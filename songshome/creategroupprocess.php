<!DOCTYPE html>
<html>

<head>
<title>Music Collab. Environ. - Edit Group</title>
<link rel ="stylesheet" type="text/css" href="/songshome/errorPage.css" >
<link rel="icon" href="/songshome/imageNotFound.jpg" type="image/ico">
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

    <h2>&nbsp;&nbsp;Oops! An Error Has Occured</h2>

</body>

</html>
<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if(isset($_POST['submit']) && isset($_POST["groupTitle"]) && isset($_POST["linkAccessOnly"])) {

        require('./includes/db.php');

        // Security check
        require("userDetailsChangedCheck.php");
        require("retrieveUserAccessLevel.php");
        if (isset($_POST["relationalGroupIdSubmit"])) {
            $checkAccessGroupId = $_POST["relationalGroupIdSubmit"];
        }
        else {
            $checkAccessGroupId = "NULL";
        }
        $access_level = checkAccesstoResource($checkAccessGroupId, $db); // Retrieve proper access level for current user.
        if (!isset($_SESSION['member_id']) || $access_level < 5) { // Limit group creation to group leaders.
            print('Error: You do not have access to this resource.');
            die();
        }

        
        $accessOk = 0;

        //GROUP KEY CHECK
        if(isset($_POST["relationalGroupIdSubmit"]) && isset($_POST["relationalGroupKeySubmit"]) ) {
            $relationalGroupIdSubmit = $_POST["relationalGroupIdSubmit"];
            $relationalGroupKeySubmit = $_POST["relationalGroupKeySubmit"];

            require("checkKey.php");

            $checkKeyQuery = checkKey("groups", $relationalGroupIdSubmit, "groupKey",  $relationalGroupKeySubmit, $db);
            if($checkKeyQuery){
                $accessOk = 1;
            } 

            if($checkKeyQuery){
                //LIMIT # of groups in a group to 1000
                $targetGroupId = $checkKeyQuery['id'];
                $numberOfGroups = $db->query("SELECT `id` FROM `groups` WHERE `relationalGroupId` = '$targetGroupId'")->fetchAll(); 
                if (sizeof($numberOfGroups) >= 1000){
                    print(" Group limit reached for this group. Please add groups to another group. ");
                    $accessOk = 0;
                }
            }
        }
        else {
            $accessOk = 1;
        }


        

        if ($accessOk) {
            require("functions.php");    
            
            $groupTitle = $_POST["groupTitle"];
           
            require("bannedChars.php");

            //HTML Purifier also used to purify picture filename
            require_once './htmlpurifier/library/HTMLPurifier.auto.php';       
            $config = HTMLPurifier_Config::createDefault();
            $purifier = new HTMLPurifier($config);
            $groupTitle = $purifier->purify($groupTitle);
            

            $titleCheck = strpos_multi($bannedArray, $groupTitle);
            if ($titleCheck != "0" && $groupTitle != "" && !(strlen($groupTitle) > 210)){
                // After string is checked for invalid characters, Group creation begins here.

                if(isset($_POST["relationalGroupIdSubmit"]) && isset($_POST["relationalGroupKeySubmit"]) ) {
                    $relationalGroupId = $checkKeyQuery['id'];

                    //PDO Query Statement
                    $stmt = $db->prepare('SELECT groupName FROM groups WHERE groupName = :grouptitle AND relationalGroupId = :relationalgroupid');
                    $stmt->bindParam('grouptitle', $groupTitle);
                    $stmt->bindParam('relationalgroupid', $relationalGroupId);
                    $stmt->execute();
                    $groupQuery = $stmt->fetch();

                }
                else
                {

                    //PDO Query Statement
                    $stmt = $db->prepare("SELECT groupName FROM groups WHERE groupName = :grouptitle AND relationalGroupId IS NULL");
                    $stmt->bindParam('grouptitle', $groupTitle);
                    $stmt->execute();
                    $groupQuery = $stmt->fetch();
            
                }
                
                if ($groupQuery == false){

                    $linkAccessOnly = $_POST["linkAccessOnly"][0];
                    
                    if ($linkAccessOnly != 0) {
                        $linkAccessOnly = 1;
                    }
                    else {
                        $linkAccessOnly = 0;
                    }

                    if(isset($_POST["relationalGroupIdSubmit"]) && isset($_POST["relationalGroupKeySubmit"]) ) {

                        $groupKey = getRandKey(12);
                        $stmt = $db->prepare("INSERT INTO `groups` (`id`, `groupName`, `created`, `groupPicture`, `groupKey`, `relationalGroupId`, `linkAccessOnly`) VALUES (NULL, :grouptitle, CURRENT_TIMESTAMP, '', :groupkey, :relationalgroupid, :linkaccessonly)");
                        $data = [
                            'grouptitle' => $groupTitle,
                            'groupkey' => $groupKey,
                            'relationalgroupid' => $relationalGroupId,
                            'linkaccessonly' => $linkAccessOnly,
                        ];
                        $stmt->execute($data);
                        $groupQuery = $stmt->fetch();


                    //PDO Query Statement
                        $stmt = $db->prepare('SELECT * FROM groups WHERE groupName = :grouptitle AND relationalGroupId = :relationalgroupid');
                        $stmt->bindParam('grouptitle', $groupTitle);
                        $stmt->bindParam('relationalgroupid', $relationalGroupId);
                        $stmt->execute();
                        $groupQuery = $stmt->fetch();
                        $id = $groupQuery['id'];
                        
                    }
                    else
                    {
                        // $db->query("INSERT INTO `groups` (`id`, `groupName`, `created`, `groupPicture`) VALUES (NULL, '$groupTitle', CURRENT_TIMESTAMP, '')");
                        $groupKey = getRandKey(12);
                        $stmt = $db->prepare("INSERT INTO `groups` (`id`, `groupName`, `created`, `groupPicture`, `dataLimitGB`, `groupKey`, `relationalGroupId`, `linkAccessOnly`) VALUES (NULL, :grouptitle, CURRENT_TIMESTAMP, '', 15, :groupkey, NULL, :linkaccessonly)");
                        $data = [
                            'grouptitle' => $groupTitle,
                            'groupkey' => $groupKey,
                            'linkaccessonly' => $linkAccessOnly,
                        ];
                        $stmt->execute($data);
                        $groupQuery = $stmt->fetch();


                        //PDO Query Statement
                        $stmt = $db->prepare('SELECT * FROM groups WHERE groupName = :grouptitle AND relationalGroupId IS NULL');
                        $stmt->bindParam('grouptitle', $groupTitle);
                        $stmt->execute();
                        $groupQuery = $stmt->fetch();
                        $id = $groupQuery['id'];
                        
                        
                    }

                    if($groupQuery != false){

                        //Creates a location index for the group to help display the location of the group in group.php
                        $stmt = $db->prepare('SELECT * FROM groups WHERE id = :relationalgroupid');
                        $stmt->bindParam('relationalgroupid', $groupQuery['relationalGroupId']);
                        $stmt->execute();
                        $locationQuery = $stmt->fetch();

                        if($locationQuery){
                            
                            do {
            
                                $locationId[] = $locationQuery["id"];
                                $testGroupId = $locationQuery["relationalGroupId"];
                                $stmt = $db->prepare('SELECT * FROM groups WHERE id = :testgroupid');
                                $stmt->bindParam('testgroupid', $testGroupId);
                                $stmt->execute();
                                $locationQuery = $stmt->fetch();
        
                            } while ($testGroupId !== null);
                         
                            $locationIdFlip = array_reverse($locationId);
                            $locationIndex = implode(",", $locationIdFlip);
                           
                            $db->query("UPDATE `groups` SET `locationIndex` = '$locationIndex,' WHERE `groups`.`id` = '$id'");
                        }

                        //Uploads the submitted image.
                        if (!is_array($_FILES["groupImage"]["tmp_name"])) {
                            if(isset($_FILES["groupImage"]["tmp_name"]) && $_FILES["groupImage"]["error"] === 0) {            

                                //SITEWIDE DATA LIMIT HERE ALSO IN editgroupprocess creategroupprocess and pageprocess
                                $uploadFilesSizeMB = bytes_to_megabytes($_FILES["groupImage"]["size"]);
            
                                // require("checkGroupSiteDataUsage.php");
                                // if ( checkGroupSiteDataUsage($id, $uploadFilesSizeMB, $db) ) {       

                                    $fileName = $_FILES["groupImage"]["name"];
                                    $tmpFileName = $_FILES["groupImage"]["tmp_name"];

                                    $uploadOk = 1;
                                    $imageFileType = mime_content_type($tmpFileName);
                                    
                                    if (substr($imageFileType, 0, 6) == "image/"){
                                        print(" File is an image - " . $imageFileType . ".");
                                        $uploadOk = 1;
                                    } else {
                                        print(" File is not an image.");
                                        $uploadOk = 0;
                                    }

                                    // //HTML Purifier
                                    $fileNamePurify = $purifier->purify($fileName);
                                    $tmpFileNamePurify = $purifier->purify($tmpFileName);
                                    if ($fileName != $fileNamePurify || $tmpFileName != $tmpFileNamePurify) {
                                        $uploadOk = 0;
                                    }
                                
                                    // // Check if file already exists
                                    // if (file_exists($target_file)) {
                                    //     echo "Sorry, file already exists.";
                                    //     $uploadOk = 0;
                                    // }
                                
                                    // Check file size
                                    if (filesize($tmpFileName) > megabytes_to_bytes(6)) {
                                        echo " Sorry, your file is too large.";
                                        $uploadOk = 0;
                                    }
                                
                                    // Check if $uploadOk is set to 0 by an error
                                    if ($uploadOk == 0) {
                                        echo " Your file was not uploaded.";
                                    // if everything is ok, try to upload file
                                    } 
                                    else {   

                                        $target_dir = "./groupPictures/";
                                        $pictureKey = getRandKey(12);
                                        $extension =  pathinfo($fileName, PATHINFO_EXTENSION);
                                        $target_file = $target_dir . "mainImage_". $id . "_" . $pictureKey . ".". $extension;
                                        $fullPictureName = "mainImage_". $id . "_" . $pictureKey . ".". $extension;

                                            if (move_uploaded_file($tmpFileName, $target_file)) {
                                                
                                                echo " The file ". basename($fileName). " has been received. ";
                                                                    
                                                $thumbnail_target_file = $target_dir . "thumbnail_mainImage_". $id . "_" . $pictureKey . ".". $extension;
                                                // copy($target_file, $thumbnail_target_file);

                                                $uploadOk = createImages($target_file, $thumbnail_target_file, 640, $imageFileType, $uploadOk, true);

                                                if ($uploadOk == 1){

                                                    // $mbPictureSize = bytes_to_megabytes(filesize($target_file)) + bytes_to_megabytes(filesize($thumbnail_target_file));
                                                    $mbPictureSize = bytes_to_megabytes(filesize($thumbnail_target_file));

                                                    $stmt = $db->prepare("UPDATE `groups` SET `groupPicture` = :fullpicturename, `groupPictureMBSize` = :grouppicturembsize WHERE `id` = :id");
                                                    $data = [
                                                        'id' => $id,
                                                        'fullpicturename' => $fullPictureName,
                                                        'grouppicturembsize' => $mbPictureSize,
                                                    ];
                                                    $stmt->execute($data);

                                                    $stmt = $db->prepare("SELECT 'id' FROM `groups` WHERE `groupPicture` = :fullpicturename");
                                                    $stmt->bindParam('fullpicturename', $fullPictureName);
                                                    $stmt->execute();
                                                    $checkDatabaseForPicture = $stmt->fetchAll();

                                                    if (sizeof($checkDatabaseForPicture) != 0){                               
                                                        print(" The image was successfully uploaded! ");
                                                    } 
                                                    else {
                                                        echo " Sorry, there was an error while adding your picture to the database.";
                                                    }
                                                }
                                                
                                                if(isset($_POST["relationalGroupIdSubmit"]) && isset($_POST["relationalGroupKeySubmit"]) ) {
                                
                                                    header("Location: "."./group/".$_POST["relationalGroupIdSubmit"]."/".$_POST["relationalGroupKeySubmit"]);
                                                }
                                                else
                                                {
                                                    //header("Location: "."./groups.php");
                                                }

                                        
                                        }
                                        else{
                                            echo " Sorry, there was an error while uploading your picture.";
                                        }
                                    }

                                // }
                                // else
                                // {
                                //     print(' Site Data Limit Error!');
                                // }
                            
                            }
                            else
                            {
                                $phpFileUploadErrors = array(
                                    0 => 'There is no error, the file uploaded with success',
                                    1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                                    2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
                                    3 => 'The uploaded file was only partially uploaded',
                                    4 => 'No file was uploaded',
                                    6 => 'Missing a temporary folder',
                                    7 => 'Failed to write file to disk.',
                                    8 => 'A PHP extension stopped the file upload.',
                                );
                                
                                echo "<br> An error occured when uploading the image. ".$phpFileUploadErrors[$_FILES["groupImage"]["error"]].".";

                                    if ($_FILES["groupImage"]["error"] === 4) {
                                        
                                        if(isset($_POST["relationalGroupIdSubmit"]) && isset($_POST["relationalGroupKeySubmit"]) ) {
                                        
                                            header("Location: "."./group/".$_POST["relationalGroupIdSubmit"]."/".$_POST["relationalGroupKeySubmit"]);
                                        }
                                        else
                                        {
                                            //header("Location: "."./groups.php");
                                        }
                                    }
                                    
                                
                            }
                        }
                        else {
                            print('The image was not submitted correctly.');
                        }
                        

                    }
                    else{
                        print('The group could not be created.');
                    }
                    
                }
                else
                {
                    print('This group already exists.');
                }

            }
            else 
            {
            print('You have entered a group name that is too long, empty or contains an invalid character.');
            }
        }

        else
        {
        echo "POST Error: Database Error.";
        }
        
    }

    else
    {
    echo "POST Error: The Required Information Was Not Received.";
    }

}
else{
    //header("Location: "."./groups.php");
}

?>
