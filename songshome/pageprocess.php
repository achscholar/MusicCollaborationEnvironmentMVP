<!DOCTYPE html>
<html>

<head>
<title>Music Collab. Environ. - Add Song</title>
<link rel ="stylesheet" type="text/css" href="/songshome/errorPage.css" >
<link rel="icon" href="/songshome/imageNotFound.jpg" type="image/ico">
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

    <h1>&nbsp;&nbsp;Oops! An Error Has Occured</h1>

</body>

</html>

<?php

// Returns wheter user is trying to edit an existing song
function editSong() {
    if( isset($_POST['editCheckboxesSong'])  && isset($_POST['editCheckboxesSongKeys']) ) {
        return true;
    }
    else {
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // isset($_POST['submitBtn']) 
    // isset($_POST["addSongGroupIdSubmit"]) 
    // isset($_POST['addSongGroupKeySubmit']) 
    // isset($_POST["numberInput"]) 
    // isset($_POST["songTitle"]) 
    // isset($_POST["lyricsInput"]) 
    // isset($_FILES['songImages']) 
    // isset($_POST["linkAccessOnly"]) 
    // isset($_POST["imagesSrcOrder"])

    // isset($_POST["audioLabelInput"]) 
    // isset($_POST["playerToggleValue"])     
    // isset($_FILES["audioFiles"])
    // isset($_POST["audioLabelOrder"]);
    // isset($_POST["audioSrcOrder"]);

    // isset($_POST["youtubeToggleValue"]) 
    // isset($_POST["linkLabelInput"]) 
    // isset($_POST["linkInput"]) 

    //Checks if all the required information was received.
    if( isset($_POST["addSongGroupIdSubmit"]) && isset($_POST['addSongGroupKeySubmit']) && isset($_POST["numberInput"]) && isset($_POST["songTitle"]) && isset($_POST["lyricsInput"]) && isset($_FILES['songImages']) && isset($_POST["audioLabelInput"]) && isset($_POST["playerToggleValue"]) && isset($_POST["linkLabelInput"]) && isset($_POST["linkInput"]) && isset($_POST["youtubeToggleValue"]) && isset($_POST["linkAccessOnly"]) && isset($_FILES["audioFiles"]) && isset($_POST["imagesSrcOrder"]) && isset($_POST["audioLabelOrder"]) && isset($_POST["audioSrcOrder"])) {

        $groupIdSubmit = $_POST["addSongGroupIdSubmit"];
        $groupKeySubmit = $_POST["addSongGroupKeySubmit"];

        require('./includes/db.php');

        // Security check
        require("userDetailsChangedCheck.php");
        require("retrieveUserAccessLevel.php");
        if (isset($_POST["addSongGroupIdSubmit"])) {
            $checkAccessGroupId = $_POST["addSongGroupIdSubmit"];
        }
        else {
            $checkAccessGroupId = "NULL";
        }
        $access_level = checkAccesstoResource($checkAccessGroupId, $db); // Retrieve proper access level for current user.
        if (!isset($_SESSION['member_id']) || $access_level < 5) { // Limit music releases to group leaders.
            print('Error: You do not have access to this resource.');
            die();
        }

        $accessOk = 0;

        //GROUP KEY CHECK
        require("checkKey.php");
        
        $checkKeyQuery = checkKey("groups", $groupIdSubmit, "groupKey",  $groupKeySubmit, $db);
        if($checkKeyQuery){
            $accessOk = 1;
        } 

        if (editSong() && $accessOk == 1) {
            $editSongId = $_POST["editCheckboxesSong"];
            $editSongKey = $_POST["editCheckboxesSongKeys"];

            $checkSongKeyQuery = checkKey("songs", $editSongId, "songKey",  $editSongKey, $db);
            // Makes sure the song exists and it is located in the target group
            if($checkSongKeyQuery && $checkKeyQuery['id'] == $checkSongKeyQuery['groupId']){
                $accessOk = 1;
            }
            else {
                $accessOk = 0;
            }
        }
       

        if ($accessOk) {

            $addSongOk = 1;
            require("functions.php");

            $targetGroupId = $checkKeyQuery['id'];
            $targetGroupKey = $checkKeyQuery['groupKey'];

            //RETURN CODE
            $returnLink = "&nbsp;<a href=\"/songshome/group/$targetGroupId/$targetGroupKey\">Return</a>";
            $returnCode = "Location: "."./view/$editSongId/$editSongKey";

            require_once './htmlpurifier/library/HTMLPurifier.auto.php';       
            $config = HTMLPurifier_Config::createDefault();
            $purifier = new HTMLPurifier($config);
         
            $songTitle = $_POST["songTitle"];
            $songNumber = $_POST["numberInput"];
            $songLyrics = $_POST["lyricsInput"];
            
           
            $songTitle = $purifier->purify($songTitle);
            $songNumber = $purifier->purify($songNumber);
            $songLyrics = $purifier->purify($songLyrics);
   
            require("bannedChars.php");
            $songTitleCheck = strpos_multi($bannedArray, $songTitle);
            $songNumberCheck = strpos_multi($bannedArray, $songNumber);

            if ($songTitleCheck == "0" || $songNumberCheck == "0") {
                $addSongOk = 0;
            }

            if (strlen($songTitle) > 400 || strlen($songTitle) == 0 || strlen($songNumber) > 12 || strlen($songLyrics) > 100000 ){
                $addSongOk = 0;                
            }

            //LIMIT # of Songs in group to 500
            $numberOfSongs = $db->query("SELECT `id` FROM `songs` WHERE `groupId` = '$targetGroupId'")->fetchAll(); 
            if (sizeof($numberOfSongs) > 500){
                print(" Song limit reached for this group. Please add songs to another group.");
                $addSongOk = 0;
            }
        
            //Begin adding song if provided song values are valid.
            if ($addSongOk){

                $linkAccessOnly = $_POST["linkAccessOnly"];

                if ($linkAccessOnly != 0) {
                    $linkAccessValue = 1;
                }
                else {
                    $linkAccessValue = 0;
                }


                if (editSong() && $accessOk == 1) {
                    // Updates the current song title, number, lyrics and linkAccessOnly

                    $targetSongId = $checkSongKeyQuery['id'];

                    $stmt = $db->prepare("UPDATE `songs` SET `title` = :Title,  `songNumber` = :songnumber, `lyrics` = :Lyrics, `created` = CURRENT_TIMESTAMP, `linkAccessOnly` = :linkaccessonly WHERE `id` = :targetid");
                    $data = [                                    
                        'Title' => $songTitle,                           
                        'songnumber' => $songNumber,
                        'Lyrics' => $songLyrics, 
                        'linkaccessonly' => $linkAccessValue, 
                        'targetid' => $targetSongId,                   
                    ];
                    $stmt->execute($data);
   
                   
                }
                else {
                    // Inserts a new song with the supplied song title, number, lyrics and linkAccessOnly
                    $songKey = getRandKey(12);
                    $stmt = $db->prepare("INSERT INTO `songs` (`groupId`, `title`, `songNumber`, `lyrics`, `songKey`, `created`, `linkAccessOnly`) VALUES (:targetgroupid, :title, :songnumber, :lyrics, :songkey, CURRENT_TIMESTAMP, $linkAccessValue)");
                    $data = [
                        'targetgroupid' => $targetGroupId,
                        'title' => $songTitle,
                        'songnumber' => $songNumber,
                        'lyrics' => $songLyrics,
                        'songkey' => $songKey,
                    ];
                    $stmt->execute($data);
    
                    $targetSongId = $db->lastInsertId();

                    $returnCode = "Location: "."./view/$targetSongId/$songKey";
                }
               

                // Check if song added successfully.
                $stmt = $db->prepare('SELECT * FROM songs WHERE id = :lastinsertid');
                $stmt->bindParam('lastinsertid', $targetSongId);
                $stmt->execute();
                $songQuery = $stmt->fetch();

                if ($songQuery){

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

                   $uploadFilesSizeMB = bytes_to_megabytes(getUploadFileSizeMB($_FILES));

                   //150MB Post Filesize Limit
                   if ($uploadFilesSizeMB <= 150) {

                        //SITEWIDE DATA LIMIT HERE ALSO IN editgroupprocess creategroupprocess and pageprocess
                        // require("checkGroupSiteDataUsage.php");
                        // if ( checkGroupSiteDataUsage($targetGroupId, $uploadFilesSizeMB, $db) ) {

                            // var_dump($uploadFilesSizeMB);
                            // $_POST["imagesSrcOrder"]
                            //IMAGE UPLOAD
                            if(isset($_FILES['songImages'])){

                                print("<h2 style=\"margin:0\"><u>Image Upload Information</u></h2>");

                                $imageIndex = 0;
                                $imageUploadCount = 0;

                                $updatedImages[] = array();

                                foreach ($_FILES['songImages']['error'] as $songImageError) {
                                
                                    if ($songImageError == 0) {

                                        $uploadOk = 0;

                                        $fileName = $_FILES["songImages"]["name"][$imageIndex];
                                        $tmpFileName = $_FILES["songImages"]["tmp_name"][$imageIndex];

                                        echo "<br>&nbsp;&nbsp;Image #".($imageIndex+1).": ".$fileName;

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
                                            echo " Sorry, your file was not uploaded.";
                                            print($returnLink);
                                        // if everything is ok, try to upload file
                                        } 

                                        else 
                                        {  
                                            
                                            $imageUploadCount++;

                                            $highestPicturesSortQuery = $db->query("SELECT `pictureSort` FROM `pictures` ORDER BY `pictureSort` DESC LIMIT 1")->fetch();
                                        
                                            if ($highestPicturesSortQuery != false) {
                                                $highestPicturesSortValue = $highestPicturesSortQuery['pictureSort'] + 1;
                                            }
                                            else {
                                                $highestPicturesSortValue = 1;
                                            }

                                            $target_dir = "./pictures/"; 
                                            $pictureKey = getRandKey(12);

                                            $stmt = $db->prepare("INSERT INTO `pictures` (`songId`, `pictureKey`, `pictureSort`, `created`) VALUES (:songid, :picturekey, :picturesort, CURRENT_TIMESTAMP)");
                                            $data = [
                                                'songid' => $targetSongId,
                                                'picturekey' => $pictureKey,
                                                'picturesort' => $highestPicturesSortValue,
                                            ];
                                            $stmt->execute($data);

                                            $lastInsertIdPicture = $db->lastInsertId();

                                            $extension =  pathinfo($fileName, PATHINFO_EXTENSION);
                                            $target_file = $target_dir . "image_". $lastInsertIdPicture . "_" . $pictureKey .".". $extension;
                                            $fullPictureName = "image_". $lastInsertIdPicture . "_" . $pictureKey .".". $extension;                  

                                            if (move_uploaded_file($tmpFileName, $target_file)) {
                                                                    
                                                echo " The file ". basename($fileName). " has been received. ";
                                                                    
                                                $thumbnail_target_file = $target_dir . "thumbnail_image_". $lastInsertIdPicture . "_" . $pictureKey .".". $extension;
                                                // copy($target_file, $thumbnail_target_file);

                                                $uploadOk = createImages($target_file, $thumbnail_target_file, 640, $imageFileType, $uploadOk, false);

                                                if ($uploadOk == 1){

                                                    $mbPictureSize = bytes_to_megabytes(filesize($target_file)) + bytes_to_megabytes(filesize($thumbnail_target_file));

                                                    $stmt = $db->prepare("UPDATE `pictures` SET `pictureFileName` = :picturefilename, `mbSize` = :mbsize WHERE `pictureId` = :lastinsertidpicture");
                                                    $data = [                                    
                                                        'picturefilename' => $fullPictureName,                           
                                                        'mbsize' => $mbPictureSize, 
                                                        'lastinsertidpicture' => $lastInsertIdPicture,                              
                                                    ];
                                                    $stmt->execute($data);

                                                    // Checks if the picture data was successfully added to the database.
                                                    $stmt = $db->prepare("SELECT pictureId, pictureFileName FROM `pictures` WHERE `pictureId` = :lastinsertidpicture");
                                                    $stmt->bindParam('lastinsertidpicture', $lastInsertIdPicture);
                                                    $stmt->execute();
                                                    $checkDatabaseForPicture = $stmt->fetchAll();

                                                    if (sizeof($checkDatabaseForPicture) != 0){      
                                                        $updatedImages[] = $checkDatabaseForPicture[0]["pictureFileName"]; // USED LATER FOR MANAGING SORTING AND DELETING IMAGES.
                                                        $_POST["imagesSrcOrder"][$imageIndex] = $checkDatabaseForPicture[0]["pictureFileName"]; // USED LATER FOR MANAGING SORTING AND DELETING IMAGES.
                                                        print(" The image was successfully uploaded! ");
                                                        print($returnLink);
                                                    } 
                                                    else {
                                                        echo " Sorry, there was an error while adding your picture to the database.";
                                                        print($returnLink);
                                                    }
                                                }
                    
                                            
                                            }
                                            else{
                                                echo " Sorry, there was an error while uploading your picture.";
                                            }

                                            //LIMIT # OF IMAGES TO 80
                                            if ($imageUploadCount >= 80) {
                                                break;
                                            }
                                        }

                                        // echo "&nbsp;&nbsp;Image #".($imageIndex+1).": ".$phpFileUploadErrors[$_FILES["songImages"]["error"][$imageIndex]].".";
                                        // $a = $_FILES["songImages"]["tmp_name"][$imageIndex];
                                        // displayVar($a);
                                    }
                                    else
                                    {
                                        
                                        echo "<br>&nbsp;&nbsp;Image #".($imageIndex+1).": An error occured when uploading the image. ".$phpFileUploadErrors[$_FILES["songImages"]["error"][$imageIndex]].".";                           
                                        
                                    }

                                    $imageIndex++;
                                }

                                // Fix sorting order of images and delete specified images.

                                // Begin by retrieving the current sort order.
                                $stmt = $db->prepare('SELECT pictureId, pictureFileName, pictureSort FROM pictures WHERE songId = :lastinsertid ORDER BY pictureSort ASC');
                                $stmt->bindParam('lastinsertid', $targetSongId);
                                $stmt->execute();
                                $picturesDetailsQuery = $stmt->fetchAll();

                                // var_dump($_POST["imagesSrcOrder"]);
                                // Update the sort order of images not meant to be deleted.
                                $i = 0;
                                
                                foreach ($_POST["imagesSrcOrder"] as $imageOrderByFilenames) { 

                                    $imageFilename = $purifier->purify(substrAfter($imageOrderByFilenames, "/"));

                                    if ($imageFilename != "imageNotFound.jpg") { // Update sorting only on Images not being deleted.

                                        $stmt = $db->prepare("UPDATE `pictures` SET `pictureSort` = :newpicturesort WHERE `pictureFileName` = :targetpicturefilename");
                                        $data = [                                    
                                            'newpicturesort' => $i,                           
                                            'targetpicturefilename' => $imageFilename,                              
                                        ];
                                        $stmt->execute($data);

                                        $updatedImages[] = $imageFilename; 
                                        $i++;
                                    }   
                                                                  
                                }

                                // Remove unsorted images/images that are meant to be deleted from server.
                                // displayVar($_POST["imagesSrcOrder"]);
                                foreach ($picturesDetailsQuery as $possibleImageToDelete) {
                               
                                    if (!in_array($possibleImageToDelete["pictureFileName"], $updatedImages)) {
                                        $imageFileToDelete = $possibleImageToDelete["pictureFileName"];
                                        
                                        if ($imageFileToDelete != "" && file_exists("./pictures/".$imageFileToDelete)) {
                                            // Delete image file from server.
                                            if(unlink("./pictures/".$imageFileToDelete)) {
                                                // Delete image filename entry from database.
                                                $stmt = $db->prepare("DELETE FROM `pictures` WHERE `pictureFileName` = :todeletefilename");
                                                $data = [                                                          
                                                    'todeletefilename' => $imageFileToDelete,                              
                                                ];
                                                $stmt->execute($data);

                                            }  
                                            else {
                                                print("\nError: Image file to be deleted was not removed from the server!");
                                            } 

                                            // Delete image thumbnail from server.
                                            if($imageFileToDelete != "" && !unlink("./pictures/thumbnail_".$imageFileToDelete)) {
                                                print("\nError: Image file thumbnail to be deleted was not removed from the server!");
                                            }
                                        }
                                        else {
                                            print("\nError: Image file to be deleted does not exist!");
                                        }

                                    }
                                    
                                }

                            }
                            else
                            {
                                print(' Image submission error.');
                            }

                            //AUDIO UPLOAD
                            if(isset($_POST["audioLabelInput"]) && isset($_POST["playerToggleValue"]) && isset($_FILES["audioFiles"])){
                                print("<br><h2 style=\"margin:0\"><u>Audio Upload Information</u></h2>");

                                $audioIndex = 0;
                                $audioUploadCount = 0;

                                $updatedAudio[] = array();

                                foreach ($_FILES['audioFiles']['error'] as $songAudioError) {   
                                    if ($songAudioError == 0) {

                                        $uploadOk = 0;

                                        $fileName = $_FILES["audioFiles"]["name"][$audioIndex];
                                        $tmpFileName = $_FILES["audioFiles"]["tmp_name"][$audioIndex];

                                        echo "<br>&nbsp;&nbsp;Audio #".($audioIndex+1).": ".$fileName;

                                        $audioFileType = mime_content_type($tmpFileName);

                                        // var_dump($audioFileType);
                                        //Javascript & PHP Determine .m4a as different mime types, hence the addition of "video/mp4" here.

                                        if (substr($audioFileType, 0, 6) == "audio/" || $audioFileType == "video/mp4"){
                                            if ($audioFileType != "audio/webm" && $audioFileType != "audio/ogg"){
                                                print(" File is an audio - " . $audioFileType . ".");
                                                $uploadOk = 1;
                                            }
                                            else {
                                                print(" Audio filetype unsupported.");
                                                $uploadOk = 0;
                                            }
                                            
                                        }     
                                        else{
                                            print(" File is not an audio.");
                                            $uploadOk = 0;
                                        }  

                                        //HTML Purifier
                                        $audioLabel = $_POST["audioLabelInput"][$audioIndex];

                                        $fileNamePurify = $purifier->purify($fileName);
                                        $tmpFileNamePurify = $purifier->purify($tmpFileName);
                                        $audioLabelPurify = $purifier->purify($audioLabel);
                                        if ($fileName != $fileNamePurify || $tmpFileName != $tmpFileNamePurify || $audioLabel != $audioLabelPurify) {
                                            $uploadOk = 0;
                                        }           
                                    
                                        // Check if file already exists
                                        // if (file_exists($target_file)) {
                                        //     echo "Sorry, file already exists.";
                                        //     $uploadOk = 0;
                                        // }
                                    
                                        // Check file size
                                        if (filesize($tmpFileName) > megabytes_to_bytes(2000)) {
                                            echo " Sorry, your file is too large.";
                                            $uploadOk = 0;
                                        }
                                    
                                        // Check if $uploadOk is set to 0 by an error
                                        if ($uploadOk == 0) {
                                            echo " Sorry, your file was not uploaded.";
                                            print($returnLink);
                                            // if everything is ok, try to upload file
                                        } 

                                        else 
                                        {  
                                            
                                            $audioUploadCount++;

                                            $highestAudiosSortQuery = $db->query("SELECT `musicSort` FROM `music` ORDER BY `musicSort` DESC LIMIT 1")->fetch();
                                        
                                            if ($highestAudiosSortQuery != false) {
                                                $highestAudiosSortValue = $highestAudiosSortQuery['musicSort'] + 1;
                                            }
                                            else {
                                                $highestAudiosSortValue = 1;
                                            }

                                            $includeInGroupPlayer = $_POST["playerToggleValue"][$audioIndex];
                                            if ($includeInGroupPlayer == 1) {
                                                $includeInGroupPlayerValue = 1;
                                            }
                                            else {
                                                $includeInGroupPlayerValue = 0;
                                            }
                                        

                                            $target_dir = "./music/"; 
                                            $audioKey = getRandKey(12);

                                            $stmt = $db->prepare("INSERT INTO `music` (`songId`, `musicKey`, `musicSort`, `created`, `inlcludeInGroupPlayer`) VALUES (:songid, :musickey, :musicsort, CURRENT_TIMESTAMP, :inlcludeingroupplayer)");
                                            $data = [
                                                'songid' => $targetSongId,                                   
                                                'musickey' => $audioKey,                               
                                                'musicsort' => $highestAudiosSortValue,
                                                'inlcludeingroupplayer' => $includeInGroupPlayerValue,
                                            ];
                                            $stmt->execute($data);

                                            $lastInsertIdAudio = $db->lastInsertId();

                                            $extension =  pathinfo($fileName, PATHINFO_EXTENSION);
                                            $target_file = $target_dir . "audio_". $lastInsertIdAudio . "_" . $audioKey .".". $extension;
                                            $fullAudioName = "audio_". $lastInsertIdAudio . "_" . $audioKey .".". $extension;                  

                                            if (move_uploaded_file($tmpFileName, $target_file)) {
                                                                    
                                                echo " The file ". basename($fileName). " has been received. ";
                                                    
                                                $mbAudioSize = bytes_to_megabytes(filesize($target_file));

                                                
                                                $stmt = $db->prepare("UPDATE `music` SET `musicName` = :musicname,  `musicFileName` = :musicfilename, `mbSize` = :mbsize WHERE `musicId` = :lastinsertidaudio");
                                                $data = [                                    
                                                    'musicname' => $audioLabelPurify,                           
                                                    'musicfilename' => $fullAudioName,
                                                    'mbsize' => $mbAudioSize,     
                                                    'lastinsertidaudio' => $lastInsertIdAudio,                              
                                                ];
                                                $stmt->execute($data);

                                            

                                                // Checks if the audio data was successfully added to the database.
                                                $stmt = $db->prepare("SELECT musicId, musicFileName FROM `music` WHERE `musicId` = :lastinsertidaudio");
                                                $stmt->bindParam('lastinsertidaudio', $lastInsertIdAudio);
                                                $stmt->execute();
                                                $checkDatabaseForAudio = $stmt->fetchAll();

                                                if (sizeof($checkDatabaseForAudio) != 0){     
                                                    $updatedAudio[] = $checkDatabaseForAudio[0]["musicFileName"]; // USED LATER FOR MANAGING SORTING AND DELETING AUDIO.
                                                    $_POST["audioSrcOrder"][$audioIndex] = $checkDatabaseForAudio[0]["musicFileName"]; // USED LATER FOR MANAGING SORTING AND DELETING AUDIO.

                                                    print(" The audio was successfully uploaded! ");
                                                    print($returnLink);
                                                } 
                                                else {
                                                    echo " Sorry, there was an error while adding your audio to the database.";
                                                    print($returnLink);
                                                }
                                                
                    
                                            
                                            }
                                            else{
                                                echo " Sorry, there was an error while uploading your audio.";
                                            }

                                            //LIMIT # OF AUDIOS TO 60
                                            if ($audioUploadCount >= 60) {
                                                break;
                                            }
                                        }


                                    }                  
                                    else
                                    {
                                        
                                        echo "<br>&nbsp;&nbsp;Audio #".($audioIndex+1).": An error occured when uploading the audio. ".$phpFileUploadErrors[$_FILES["audioFiles"]["error"][$audioIndex]].".";                           
                                        
                                    }

                                    $audioIndex++;
                                }

                                // Fix sorting order of audio and delete specified audio files.

                                // Begin by retrieving the current sort order.
                                $stmt = $db->prepare('SELECT musicId, musicName, musicFileName, musicSort FROM music WHERE songId = :lastinsertid ORDER BY musicSort ASC');
                                $stmt->bindParam('lastinsertid', $targetSongId);
                                $stmt->execute();
                                $musicDetailsQuery = $stmt->fetchAll();

                                // var_dump($_POST["imagesSrcOrder"]);
                                // Update the sort order of audio not meant to be deleted.
                                $i = 0;
                                $x = 0;
                                foreach ($_POST["audioSrcOrder"] as $musicOrderByFilenames) { 

                                    $musicFilename = $purifier->purify(substrAfter($musicOrderByFilenames, "/"));
                                    $audioLabel = $_POST["audioLabelOrder"][$x];

                                    if ($musicFilename != "defaultMP3.mp3") { // Update sorting only on audio files not being deleted.

                                        $audioLabelPurified = $purifier->purify($audioLabel);

                                        $stmt = $db->prepare("UPDATE `music` SET `musicSort` = :newmusicsort, `musicName` = :newmusiclabel WHERE `musicFileName` = :targetmusicfilename");
                                        $data = [                                    
                                            'newmusicsort' => $i,                           
                                            'targetmusicfilename' => $musicFilename, 
                                            'newmusiclabel' => $audioLabelPurified                           
                                        ];
                                        $stmt->execute($data);
                                                // print("\nUPDATED: ".$musicFilename.", LABEL: ".$audioLabelPurified.", SORT #: ".$i);
                                        $updatedAudio[] = $musicFilename; 
                                        $i++;
                                    }  
                                    
                                    $x++;
                                                                  
                                }

                                // Remove unsorted audio that are meant to be deleted from server.
                                // displayVar($_POST["audioSrcOrder"]);
                                foreach ($musicDetailsQuery as $possibleAudioToDelete) {
                               
                                    if (!in_array($possibleAudioToDelete["musicFileName"], $updatedAudio)) {
                                        $audioFileToDelete = $possibleAudioToDelete["musicFileName"];
                                        
                                        if ($audioFileToDelete != "" && file_exists("./music/".$audioFileToDelete)) {
                                            // Delete audio file from server.
                                            if(unlink("./music/".$audioFileToDelete)) {
                                                // Delete audio filename entry from database.
                                                
                                                $stmt = $db->prepare("DELETE FROM `music` WHERE `musicFileName` = :todeletefilename");
                                                $data = [                                                          
                                                    'todeletefilename' => $audioFileToDelete,                              
                                                ];
                                                $stmt->execute($data);

                                            }  
                                            else {
                                                print("\nError: Audio file to be deleted was not removed from the server!");
                                            } 

                                        }
                                        else {
                                            print("\nError: Audio file to be deleted does not exist!");
                                        }

                                    }
                                    
                                }
                                
                            }
                            else
                            {
                                print(' Audio submission error.');
                            }

                            //LINK UPLOAD
                            if( isset($_POST["youtubeToggleValue"]) && isset($_POST["linkLabelInput"])  && isset($_POST["linkInput"]) ){

                                // Delete old link data.
                                $stmt = $db->prepare("DELETE FROM `links` WHERE `songId` = :lastinsertid");
                                $stmt->bindParam('lastinsertid', $targetSongId);
                                $stmt->execute();

                                print("<h2 style=\"margin:0\"><u>Link Upload Information</u></h2>");

                                $linkIndex = 0;
                                $linkUploadCount = 0;

                                foreach ($_POST['youtubeToggleValue'] as $linkInputType) {              


                                    if ($_POST["linkInput"][$linkIndex] == "") {
                                        echo "<br>&nbsp;&nbsp;Link #".($linkIndex+1).": Link data is empty."; 
                                        break;
                                    }

                                    $highestLinksSortQuery = $db->query("SELECT `linkSort` FROM `links` ORDER BY `linkSort` DESC LIMIT 1")->fetch();
                                        
                                    if ($highestLinksSortQuery != false) {
                                        $highestLinksSortValue = $highestLinksSortQuery['linkSort'] + 1;
                                    }
                                    else {
                                        $highestLinksSortValue = 1;
                                    }

                                    $targetURL = $_POST["linkInput"][$linkIndex];
                                    $targetLabelURL = $_POST["linkLabelInput"][$linkIndex];
                                    $targetLabelURL = $purifier->purify($targetLabelURL);

                                    $linkType = 0;

                                    // If url is a youtube link, parse url.
                                    if ($linkInputType == 2 || $linkInputType == 1) {
                                        //Creates youtubeLinkVariables array of variables from url.                                                                          
                                        parse_str( parse_url( $targetURL, PHP_URL_QUERY ), $youtubeLinkVariables );
                                    }

                                    $youtubeLinkFailed = 0;

                                    // Youtube Playlist
                                    if ($linkInputType == 2) {
                                        
                                        //Checks if a playlist variable/index in the URL is present
                                        if(array_key_exists ("list", $youtubeLinkVariables)){

                                            // Remove malicious code
                                            $cleanUpload = $purifier->purify($youtubeLinkVariables['list']);
                                            $linkType = 2;

                                            $stmt = $db->prepare("INSERT INTO `links` (`songId`, `linkType`, `linkData`, `linkName`, `linkSort`, `created`) VALUES (:songid, :linktype, :linkdata, :linkname, :linksort, CURRENT_TIMESTAMP)");
                                            $data = [
                                                    'songid' => $targetSongId,                                   
                                                    'linktype' => $linkType,                               
                                                    'linkdata' => $cleanUpload,
                                                    'linkname' => $targetLabelURL,
                                                    'linksort' => $highestLinksSortValue,
                                            ];
                                            $stmt->execute($data);

                                            $linkUploadCount++;
                                         
                                        }
                                        
                                        
                                    }
                                    // Single Youtube Video
                                    else if ($linkInputType == 1) {

                                        //Checks if a video variable/index in the URL is present
                                        if(array_key_exists ("v", $youtubeLinkVariables)){

                                            // Remove malicious code
                                            $cleanUpload = $purifier->purify($youtubeLinkVariables['v']);
                                            $linkType = 1;

                                            $stmt = $db->prepare("INSERT INTO `links` (`songId`, `linkType`, `linkData`, `linkName`, `linkSort`, `created`) VALUES (:songid, :linktype, :linkdata, :linkname, :linksort, CURRENT_TIMESTAMP)");
                                            $data = [
                                                    'songid' => $targetSongId,                                   
                                                    'linktype' => $linkType,                               
                                                    'linkdata' => $cleanUpload,
                                                    'linkname' => $targetLabelURL,
                                                    'linksort' => $highestLinksSortValue,
                                            ];
                                            $stmt->execute($data);

                                            $linkUploadCount++;
                                       
                                        }
                                        // Check if "youtu.be/" is present
                                        else if (strpos($targetURL, 'youtu.be/') !== false) {
                                           
                                            // Trim beginning of URL until "youtu.be/" is encountered.
                                            $trimmedTargetURL = strstr($targetURL, 'youtu.be/');
                                            // Remove "youtu.be/" from URL
                                            $trimmedTargetURL = ltrim($trimmedTargetURL,"youtu.be/");
                                            // Remove any forward slashes
                                            $trimmedTargetURL = str_replace('/','',$trimmedTargetURL);

                                            // Remove malicious code
                                            $cleanUpload = $purifier->purify($trimmedTargetURL);
                                            $linkType = 1;

                                            $stmt = $db->prepare("INSERT INTO `links` (`songId`, `linkType`, `linkData`, `linkName`, `linkSort`, `created`) VALUES (:songid, :linktype, :linkdata, :linkname, :linksort, CURRENT_TIMESTAMP)");
                                            $data = [
                                                    'songid' => $targetSongId,                                   
                                                    'linktype' => $linkType,                               
                                                    'linkdata' => $cleanUpload,
                                                    'linkname' => $targetLabelURL,
                                                    'linksort' => $highestLinksSortValue,
                                            ];
                                            $stmt->execute($data);
                                          
                                            $linkUploadCount++;

                                        }
                                    
                                    }
                                    // Regular URL
                                    else {
                                       // Remove malicious code
                                       $cleanUpload = $purifier->purify($targetURL);
                                       $linkType = 0;

                                       $stmt = $db->prepare("INSERT INTO `links` (`songId`, `linkType`, `linkData`, `linkName`, `linkSort`, `created`) VALUES (:songid, :linktype, :linkdata, :linkname, :linksort, CURRENT_TIMESTAMP)");
                                       $data = [
                                            'songid' => $targetSongId,                                   
                                            'linktype' => $linkType,                               
                                            'linkdata' => $cleanUpload,
                                            'linkname' => $targetLabelURL,
                                            'linksort' => $highestLinksSortValue,
                                        ];
                                        $stmt->execute($data);

                                        $linkUploadCount++;
                                    }

                                    echo "<br>&nbsp;&nbsp;Link #".($linkIndex+1).": Link has been uploaded."; 

            
                                    //LIMIT # OF LINKS TO 500
                                    if ($linkUploadCount >= 500) {
                                        break;
                                    }

                                    $linkIndex++;
                                }
                                
                            }
                            else
                            {
                                print(' Link submission error.');
                            }

                        // }
                        // else
                        // {
                        //     print(' Site Data Limit Error!');
                        // }
                    
                        header($returnCode);
                    
                    }
                    else{
                        print(' Error uploading files. The 150MB upload limit has been reached.');
                    }
                }
                else 
                {
                    print(' Song could not be added. Database Error.');
                }
                

            }
            else 
            {
                print(' Song could not be added. Please try again later.');
            }

        }
        else {
            require("404.php");
            // echo "ERROR 404: Database Error.";
        }
    }
    else {
        echo " POST Error: The Required Information Was Not Received.";
    }

}
else {
    // header("Location: "."./groups.php");
}


?>
