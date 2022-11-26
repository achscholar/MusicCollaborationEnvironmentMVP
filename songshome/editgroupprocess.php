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

    if(isset($_POST['submit']) && isset($_POST['editThisGroup']) && isset($_POST['editThisGroupKey']) && isset($_POST["editGroupTitle"]) && isset($_POST['moveGroup']) && isset($_POST["linkAccessOnly"]) ) {

        
        require('./includes/db.php');

        // Security check
        require("userDetailsChangedCheck.php");
        require("retrieveUserAccessLevel.php");
        if (isset($_POST["editThisGroup"])) {
            $checkAccessGroupId = $_POST["editThisGroup"];
        }
        else {
            $checkAccessGroupId = "NULL";
        }
        $access_level = checkAccesstoResource($checkAccessGroupId, $db); // Retrieve proper access level for current user.
        if (!isset($_SESSION['member_id']) || $access_level < 5) { // Limit group editing to group leaders.
            print('Error: You do not have access to this resource.');
            die();
        }

        $accessOk = 0;

        $editThisGroupId = $_POST['editThisGroup'];  
        $editThisGroupKey = $_POST['editThisGroupKey'];

        //Format for moveGroup is: subgroupid|subgroupkey. So we check for a |.
        if(strpos($_POST['moveGroup'], '|') !== FALSE){
            $moveHere = $_POST['moveGroup'];
            $moveHere = explode("|", $moveHere);
            $moveHereId = $moveHere[0];
            $moveHereKey = $moveHere[1];
        }
        else
        {
            $moveHereId = "Error";
            $moveHereKey = "Error";
        }
        
        // print($moveHereId);
        // print($moveHereKey);
        
        
        // GROUP & MOVEGROUP KEY CHECK
        require("checkKey.php");
        $editThisGroupKeyQuery = checkKey("groups", $editThisGroupId, "groupKey",  $editThisGroupKey, $db);
        $moveHereGroupKeyQuery = checkKey("groups", $moveHereId, "groupKey", $moveHereKey, $db);

        if($editThisGroupKeyQuery && $moveHereGroupKeyQuery){
            $accessOk = 1;
        }


        if ($accessOk) {

            require("functions.php");

            $groupTitle = $_POST["editGroupTitle"];

            //HTML Purifier also used to purify picture filename
            require('./htmlpurifier/library/HTMLPurifier.auto.php');       
            $config = HTMLPurifier_Config::createDefault();
            $purifier = new HTMLPurifier($config);
            $groupTitle = $purifier->purify($groupTitle);


            $moveOk = 1;
            // Checks if the target and subgroup has a NULL relationalgroupid.
            // This makes sure the target group is not moved inside someone elses main group.
            $stmt = $db->prepare("SELECT * FROM `groups` WHERE `id` = :editthisgroupid"); 
            $stmt->bindParam('editthisgroupid', $editThisGroupId);
            $stmt->execute();
            $targetGroupNullCheck = $stmt->fetch();  

            $stmt = $db->prepare("SELECT * FROM `groups` WHERE `id` = :movehereid"); 
            $stmt->bindParam('movehereid', $moveHereId);
            $stmt->execute();
            $subGroupNullCheck = $stmt->fetch(); 
    
            if($targetGroupNullCheck && $subGroupNullCheck){
                if($targetGroupNullCheck['relationalGroupId'] === NULL && $subGroupNullCheck['relationalGroupId'] === NULL){
                    $moveOk = 0;
                }
                
            }

         
            //LIMIT moving target group to a group which has 1500 or more groups
            $moveHereGroupId = $moveHereGroupKeyQuery['id'];
            $numberOfGroups = $db->query("SELECT `id` FROM `groups` WHERE `relationalGroupId` = '$moveHereGroupId'")->fetchAll(); 
            if (sizeof($numberOfGroups) >= 1000){
                print(" Group limit reached for this group. Please add groups to another group. ");
                $moveOk = 0;
            }


            if ($moveOk){

                //Starts moving the requested group to the new location.
                $stmt = $db->prepare("SELECT * FROM `groups` WHERE `id` = :editthisgroupid"); 
                $stmt->bindParam('editthisgroupid', $editThisGroupId);
                $stmt->execute();
                $groupQueryTestId = $stmt->fetchAll();     

                $stmt = $db->prepare("SELECT * FROM `groups` WHERE `id` = :movehereid"); 
                $stmt->bindParam('movehereid', $moveHereId);
                $stmt->execute();
                $moveHereQueryTestId = $stmt->fetchAll();    
            
                // If the target and requested groups exists then run this code
                if (sizeof($groupQueryTestId) == "1" && sizeof($moveHereQueryTestId) == "1"){

                    $groupName = $groupQueryTestId[0]['groupName']; 
                    $groupQueryId = $groupQueryTestId[0]['id'];
                    $relationalGroupId = $groupQueryTestId[0]['relationalGroupId']; 

                    // Retrieve key of relational group.
                    $stmt = $db->prepare("SELECT * FROM `groups` WHERE `id` = :relationalgroupid"); 
                    $stmt->bindParam('relationalgroupid', $relationalGroupId);
                    $stmt->execute();
                    $relationalGroupIdQuery = $stmt->fetch(); 
                    $relationalGroupKey =  $relationalGroupIdQuery['groupKey']; 

                    $moveHereGroupQueryId = $moveHereQueryTestId[0]['id'];
                    $moveHereRelationalGroupId = $moveHereQueryTestId[0]['relationalGroupId'];

                    if ($groupQueryId != $moveHereGroupQueryId){
                        
                        //Makes sure that the user isn't trying to move the requested group to a group inside of itself (This would result in no way to access it).
                        $okToMove = 1;
                        
                        $stmt = $db->prepare('SELECT * FROM groups WHERE id = :relationalgroupid');
                        $stmt->bindParam('relationalgroupid', $moveHereGroupQueryId);
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

                            if (in_array($groupQueryId, $locationId)){
                                $okToMove = 0;
                                
                            }
                         
                            $locationIdFlip = array_reverse($locationId);
                            $locationIndex = implode(",", $locationIdFlip);                 
   
                        }

                       
                        
                        
                        if ($okToMove == 1){
                            

                            $groupNameQuery = $db->query("SELECT 'groupName' FROM `groups` WHERE `groupName` = '$groupName' AND `relationalGroupId` = '$moveHereGroupQueryId'")->fetchAll();

                            if (sizeof($groupNameQuery) == 0){

                                if(isset($locationIndex)){
                                    $db->query("UPDATE `groups` SET `relationalGroupId` = '$moveHereGroupQueryId', `locationIndex` = '$locationIndex,' WHERE `groups`.`id` = '$groupQueryId'");
                                }
                                else {
                                    $db->query("UPDATE `groups` SET `relationalGroupId` = '$moveHereGroupQueryId', `locationIndex` = NULL WHERE `groups`.`id` = '$groupQueryId'");
                                }
                                    

                            }
                            else{
                                
                                print("The target group already has a group with the same name.<br>");
                            }
                        }
                        else{
                            print ("Moving the requested group to the target location is not allowed. ");
                        }
                            
                    }
                    
                }

            }
            else 
            {
                print("Group was not be moved. ");
            }

            // After the requested group is moved its information is updated again.
            $stmt = $db->prepare("SELECT * FROM `groups` WHERE `id` = :editthisgroupid"); 
            $stmt->bindParam('editthisgroupid', $editThisGroupId);
            $stmt->execute();
            $groupQueryTestId = $stmt->fetchAll();      

            if (sizeof($groupQueryTestId) == "1"){

                // Extracts the required information from the groupQueryTestId. A lot of code depends on these variables.
                $groupQueryId = $groupQueryTestId[0]['id'];
                $relationalGroupId = $groupQueryTestId[0]['relationalGroupId'];

                // Retrieve key of relational group.
                $stmt = $db->prepare("SELECT * FROM `groups` WHERE `id` = :relationalgroupid"); 
                $stmt->bindParam('relationalgroupid', $relationalGroupId);
                $stmt->execute();
                $relationalGroupIdQuery = $stmt->fetch(); 
                $relationalGroupKey =  $relationalGroupIdQuery['groupKey']; 

                // Provides the return code to the previous page in case there is an error with the process.
                if($relationalGroupId == NULL){
                    // $returnCode = "&nbsp;<a href=\"/songshome/groups.php\">Return</a>";
                }
                else
                {
                    $returnCode = "&nbsp;<a href=\"/songshome/group.php?groupSubmit=$relationalGroupId&groupKeySubmit=$relationalGroupKey\">Return</a>";
                }

                // Updates the linkAccessOnly status of the group.
                $linkAccessOnly = $_POST["linkAccessOnly"][0];
                if ($linkAccessOnly != 0) {
                    $linkAccessOnly = 1;
                }
                else {
                    $linkAccessOnly = 0;
                }

                $stmt = $db->prepare("UPDATE `groups` SET `linkAccessOnly` = :linkaccessonly WHERE `groups`.`id` = :groupqueryid");
                $data = [
                    'groupqueryid' => $groupQueryId,
                    'linkaccessonly' => $linkAccessOnly,
                ];
                $stmt->execute($data);
                

                // Checks the title and ID that was submitted by the user for invalid characters.
                require("bannedChars.php");
                $titleCheck = strpos_multi($bannedArray, $groupTitle);
                $editGroupTitleCheck = strpos_multi($bannedArray, $editThisGroupId);

                // Confirms that the previous check found no invalid characters in the submitted ID and title. It also makes sure that the name and ID are not empty, as well
                // as checking that the submitted group name is the correct amount of characters and the ID is no more that the specified length.
                if ($titleCheck != "0" && $editGroupTitleCheck != "0" && $groupTitle != "" && $editThisGroupId != "" && !(strlen($groupTitle) > 210) && !(strlen($editThisGroupId) > 15)){
                // After strings are checked, Group editing begins here.

                    // Checks if the requested group for editing is a main group or one of the subgroups.
                    if(isset($relationalGroupId)) {

                        //Checks that the new group name submitted by the user matches or does not match the name of the group that the user wants to edit.
                        
                        $nameQuery = $db->query("SELECT * FROM `groups` WHERE `id` = '$groupQueryId' AND `relationalGroupId` = '$relationalGroupId'")->fetchAll();  
                        $editThisGroup = $nameQuery[0]['groupName']; 

                        $stmt = $db->prepare("SELECT `id` FROM `groups` WHERE `groupName` = :grouptitle AND `relationalGroupId` = '$relationalGroupId'");
                        $stmt->bindParam('grouptitle', $groupTitle);
                        $stmt->execute(); 
                        $allGroupsQuery = $stmt->fetchAll();  
                        
                    }

                    else {

                        //Checks that the new group name submitted by the user matches or does not match the name of the group that the user wants to edit.

                        $nameQuery = $db->query("SELECT * FROM groups WHERE id = '$groupQueryId' AND relationalGroupId IS NULL")->fetchAll();    
                        $editThisGroup = $nameQuery[0]['groupName']; 

                        $stmt = $db->prepare("SELECT id FROM groups WHERE groupName = :grouptitle AND relationalGroupId IS NULL");
                        $stmt->bindParam('grouptitle', $groupTitle);
                        $stmt->execute(); 
                        $allGroupsQuery = $stmt->fetchAll();  
                        
                    } 

                    $uploadOk = 1;

                    // If the submitted group has the same name as the submitted title then this code will be executed.
                    if ($editThisGroup == $groupTitle){
                        $uploadOk = 1;
                    }

                    //If the submitted title is not being used by another group this code will be executed.
                    elseif(sizeof($allGroupsQuery) == "0") {

                        // Updates the name of the requested group with the submitted group name.
                        $stmt = $db->prepare("UPDATE `groups` SET `groupName` = :grouptitle WHERE `groups`.`id` = :groupqueryid");
                        $data = [
                            'grouptitle' => $groupTitle,
                            'groupqueryid' => $groupQueryId,
                        ];
                        $stmt->execute($data);

                        // Checks to see if the requested group was successfully renamed.
                        $updateQueryTestName = $db->query("SELECT `groupName` FROM `groups` WHERE `id` = '$groupQueryId'")->fetchAll();       
                        $newGroupName = $updateQueryTestName[0]['groupName']; 

                        // If the requested group was successfully renamed then this code will be run.
                        if ($newGroupName == $groupTitle){
                            print("The group has been renamed.");
                            print($returnCode);

                            $uploadOk = 1;
                            
                        }
                        else
                        {
                            $uploadOk = 0;
                            print("The group could not be successfully renamed.");
                            print($returnCode);
                        }

                    }
                    else
                    {
                        $uploadOk = 0;
                        print('The name entered is being used by another group.');
                        print($returnCode);
                    }

                    //Image Upload Code
                    if($uploadOk == 1){

                        //PLACE DATA LIMIT HERE editgroupprocess creategroupprocess and pageprocess

                        if (!is_array($_FILES["groupImage"]["tmp_name"])) {
                            if (isset($_FILES["groupImage"]["tmp_name"]) && $_FILES["groupImage"]["error"] === 0) {

                                //SITEWIDE DATA LIMIT HERE ALSO IN editgroupprocess creategroupprocess and pageprocess
                                $uploadFilesSizeMB = bytes_to_megabytes($_FILES["groupImage"]["size"]);
            
                                // require("checkGroupSiteDataUsage.php");
                                // if ( checkGroupSiteDataUsage($editThisGroupKeyQuery["id"], $uploadFilesSizeMB, $db) ) {    

                                    $fileName = $_FILES["groupImage"]["name"];
                                    $tmpFileName = $_FILES["groupImage"]["tmp_name"];

                                    print(" ".$fileName." ");
                            
                                    $imageFileType = mime_content_type($tmpFileName);
                                    
                                    if (substr($imageFileType, 0, 6) == "image/"){
                                        print(" File is an image - " . $imageFileType . ".");
                                        $uploadOk = 1;
                                    } else {
                                        print(" File is not an image.");
                                        $uploadOk = 0;
                                    }

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
                                        echo " Sorry, your file was not uploaded.";
                                        print($returnCode);
                                    // if everything is ok, try to upload file
                                    } 
                                    else 
                                    {          
                                        $target_dir = "./groupPictures/"; 
                                        $pictureKey = getRandKey(12);

                                        $extension =  pathinfo($fileName, PATHINFO_EXTENSION);
                                        $target_file = $target_dir . "mainImage_". $groupQueryId . "_" . $pictureKey .".". $extension;
                                        $fullPictureName = "mainImage_". $groupQueryId . "_" . $pictureKey .".". $extension;                  
                                        
                                        //Delete existing file
                                        $groupPicture = $db->query("SELECT `groupPicture` FROM `groups` WHERE `id` = '$groupQueryId'")->fetch();
 
                                        if (deleteFile("./groupPictures/thumbnail_", $groupPicture["groupPicture"])) {
                                            $db->query("UPDATE `groups` SET `groupPicture` = '' WHERE `groups`.`id` = '$groupQueryId'");
                                        }

                                        if (move_uploaded_file($tmpFileName, $target_file)) {
                                                                
                                            echo " The file ". basename($fileName). " has been received. ";
                                                                
                                            $thumbnail_target_file = $target_dir . "thumbnail_mainImage_". $groupQueryId . "_" . $pictureKey .".". $extension;
                                            // copy($target_file, $thumbnail_target_file);

                                            $uploadOk = createImages($target_file, $thumbnail_target_file, 640, $imageFileType, $uploadOk, true);

                                            if ($uploadOk == 1){

                                                // $mbPictureSize = bytes_to_megabytes(filesize($target_file)) + bytes_to_megabytes(filesize($thumbnail_target_file));
                                                $mbPictureSize = bytes_to_megabytes(filesize($thumbnail_target_file));

                                                // Adds filename to database and then checks if it was successfully added.
                                                $stmt = $db->prepare("UPDATE `groups` SET `groupPicture` = :fullpicturename, `groupPictureMBSize` = :grouppicturembsize WHERE `groups`.`id` = :groupqueryid");
                                                $data = [
                                                    'fullpicturename' => $fullPictureName,
                                                    'groupqueryid' => $groupQueryId,
                                                    'grouppicturembsize' => $mbPictureSize,
                                                ];
                                                $stmt->execute($data);

                                                // Checks if the filename was successfully added to the database.
                                                $stmt = $db->prepare("SELECT 'id' FROM `groups` WHERE `groupPicture` = :fullpicturename");
                                                $stmt->bindParam('fullpicturename', $fullPictureName);
                                                $stmt->execute();
                                                $checkDatabaseForPicture = $stmt->fetchAll();

                                                if (sizeof($checkDatabaseForPicture) != 0){                               
                                                    print(" The image was successfully uploaded! ");
                                                    print($returnCode);
                                                } 
                                                else {
                                                    echo " Sorry, there was an error while adding your picture to the database.";
                                                    print($returnCode);
                                                }
                                            }
                                            
                                            //Redirect
                                            if($relationalGroupId != NULL) {
                                                header("Location: "."./group.php?groupSubmit=$relationalGroupId&groupKeySubmit=$relationalGroupKey");
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
                                    0 => ' There is no error, the file uploaded with success',
                                    1 => ' The uploaded file exceeds the upload_max_filesize directive in php.ini',
                                    2 => ' The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
                                    3 => ' The uploaded file was only partially uploaded',
                                    4 => ' No file was uploaded',
                                    6 => ' Missing a temporary folder',
                                    7 => ' Failed to write file to disk.',
                                    8 => ' A PHP extension stopped the file upload.',
                                );
                                echo "<br> An error occured when uploading the image. ".$phpFileUploadErrors[$_FILES["groupImage"]["error"]].".";
                                print($returnCode);

                                //Redirect
                                if($relationalGroupId != NULL) {
                                    header("Location: "."./group.php?groupSubmit=$relationalGroupId&groupKeySubmit=$relationalGroupKey");
                                }
                                else
                                {
                                    //header("Location: "."./groups.php");
                                }
                                
                                
                            }
                        }
                        else {
                            print('The image was not submitted correctly.');                     
                            print($returnCode);
                        }


                    }
                    
                }
                else 
                {
                    print('You have entered a group name that is too long, empty or contains an invalid character.');                     
                    print($returnCode);
                }

            }
            else
            {
                print('The target group does not exist.');
                print($returnCode);
            }

        }
        else
        {
            print('ERROR 404: Database Error.');
        }
    }
    else
    {
        echo "POST Error: The Required Information Was Not Received.";

    } 
}
else
{
    echo "POST Error.";
}

?>







