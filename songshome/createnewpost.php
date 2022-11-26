<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST["collaborationId"]) && isset($_POST["collaborationKey"]) && isset($_POST["newPostText"]) && isset($_POST["newPostAudioFileLabel"]) && isset($_POST["createNewPostLinkInput"]) && isset($_FILES["newPostAudioFile"]) ) {
        require('./includes/db.php');
        require("functions.php");

        // Security Component
        require("userDetailsChangedCheck.php"); //START SESSION
        require("retrieveUserAccessLevel.php");

        //SONG KEY CHECK
        $collaborationIdSubmit = $_POST["collaborationId"];
        $collaborationKeySumbit = $_POST["collaborationKey"];

        // Retrieve collaboration details.
        $stmt = $db->prepare("SELECT * FROM `collaborations` WHERE `id` = :collaborationid AND `collaboration_key` = :collaborationkey");
        $stmt->bindParam('collaborationid',  $collaborationIdSubmit);
        $stmt->bindParam('collaborationkey',  $collaborationKeySumbit);
        $stmt->execute();
        $collaborationQuery = $stmt->fetch();
        
        if ($collaborationQuery) {

            // Retrieve collaboration song release.
            $stmt = $db->prepare("SELECT * FROM `songs` WHERE `id` = :songid");
            $stmt->bindParam('songid',  $collaborationQuery["songId"]);
            $stmt->execute();
            $songQuery = $stmt->fetch();

            if ($songQuery) {
                $access_level = checkAccesstoResource($songQuery["groupId"], $db); // Retrieve proper access level for current user.

                if (isset($_SESSION['member_id']) && $access_level >= 1) { // User is from the parent group.

                    // Check if user is in the target collaboration.
                    $stmt = $db->prepare("SELECT * FROM `collaboration_participants` WHERE `songId` = :songid AND `collaboration_id` = :collaborationid AND `member_id` = :memberid");
                    $stmt->bindParam('songid',  $collaborationQuery["songId"]);
                    $stmt->bindParam('collaborationid',  $collaborationQuery["id"]);
                    $stmt->bindParam('memberid',  $_SESSION['member_id']);
                    $stmt->execute();
                    $userInCollaborationQuery = $stmt->fetch();

                        if ($userInCollaborationQuery) { // User is in collaboration.

                            // Retrieve # of collaboration posts.
                            $stmt = $db->prepare("SELECT `id` FROM `collaboration_posts` WHERE `collaboration_id` = :collaborationid");
                            $stmt->bindParam('collaborationid',  $collaborationQuery["id"]);
                            $stmt->execute();
                            $allCollaborationPostsQuery = $stmt->fetchAll();


                            if (sizeof($allCollaborationPostsQuery) <= 1000) { // Limit # of collaboration posts to 1000.

                                if ($_POST["newPostText"] != "") { // Check if the post text was received.

                                    // Purify user input against HTML Injection
                                    require_once './htmlpurifier/library/HTMLPurifier.auto.php';   
                                    $config = HTMLPurifier_Config::createDefault();
                                    $purifier = new HTMLPurifier($config);
                                    $purifiedPostText = $purifier->purify($_POST["newPostText"]);

                                    if (strlen($purifiedPostText) <= 10000 && strlen($purifiedPostText) != 0) { // Ensure proper length of post text before upload.
         
                                        if ($_POST["createNewPostLinkInput"] == "" && $_FILES["newPostAudioFile"]["error"] != 0 ) { // No link or file was submitted.
                                                $stmt = $db->prepare("INSERT INTO `collaboration_posts` (`collaboration_id`, `member_id`, `content`, `uploaded`) VALUES (:collaborationid, :memberid, :postcontent, CURRENT_TIMESTAMP)");
                                                $data = [
                                                    'collaborationid' => $collaborationQuery['id'],
                                                    'memberid' => $_SESSION['member_id'],
                                                    'postcontent' => $purifiedPostText,
                                                ];
                                                $stmt->execute($data);

                                                header("Location: "."./collaboration/".$userInCollaborationQuery["collaboration_id"]."/".$userInCollaborationQuery["collaboration_key"]);
                                        }
                                        else if ($_POST["createNewPostLinkInput"] != "") { // User uploaded a link to a file.

                                            $purifiedPostLink = $purifier->purify($_POST["createNewPostLinkInput"]);
                                            
                                            if (strlen($purifiedPostLink) <= 5000 && strlen($purifiedPostLink) != 0) { // Ensure proper length of link before upload.
                                                $stmt = $db->prepare("INSERT INTO `collaboration_posts` (`collaboration_id`, `member_id`, `content`, `uploaded`, `link`) VALUES (:collaborationid, :memberid, :postcontent, CURRENT_TIMESTAMP, :postlink)");
                                                $data = [
                                                    'collaborationid' => $collaborationQuery['id'],
                                                    'memberid' => $_SESSION['member_id'],
                                                    'postcontent' => $purifiedPostText,
                                                    'postlink' => $purifiedPostLink,
                                                ];
                                                $stmt->execute($data);

                                                header("Location: "."./collaboration/".$userInCollaborationQuery["collaboration_id"]."/".$userInCollaborationQuery["collaboration_key"]);
                                            }
                                            else {
                                                echo "Error: Link is too long.";
                                            }

                                        }
                                        else if ($_FILES["newPostAudioFile"]["error"] == 0) { // User uploaded a file.

                                            $uploadOk = 0;

                                            $fileName = $_FILES["newPostAudioFile"]["name"];
                                            $tmpFileName = $_FILES["newPostAudioFile"]["tmp_name"];

                                            echo "<br>&nbsp;&nbsp;Audio: ".$fileName;

                                            $audioFileType = mime_content_type($tmpFileName);

                                            // Reject invalid filetypes.
                                            if (substr($audioFileType, 0,  13) != "application/x"){
                                                    $uploadOk = 1;          
                                            }     
                                            else{
                                                print(" Error: Invalid Filetype.");
                                                $uploadOk = 0;
                                            }  

                                            //HTML Purifier
                                            $audioLabel = $_POST["newPostAudioFileLabel"];

                                            $fileNamePurify = $purifier->purify($fileName);
                                            $tmpFileNamePurify = $purifier->purify($tmpFileName);
                                            $audioLabelPurify = $purifier->purify($audioLabel);
                                            if ($fileName != $fileNamePurify || $tmpFileName != $tmpFileNamePurify || $audioLabel != $audioLabelPurify) {
                                                $uploadOk = 0;
                                            }           
                                        
                                            // Check file size
                                            if (filesize($tmpFileName) > megabytes_to_bytes(11)) {
                                                echo " Sorry, your file is too large.";
                                                $uploadOk = 0;
                                            }
                                        
                                            // Check if $uploadOk is set to 0 by an error.
                                            if ($uploadOk == 0) {
                                                echo " Sorry, your file was not uploaded.";
                                            } 
                                            else 
                                            {  
                                        
                                                $target_dir = "./collaborationPostFiles/"; 
                                                $audioKey = getRandKey(12);

                                                $stmt = $db->prepare("INSERT INTO `collaboration_posts` (`collaboration_id`, `member_id`, `content`, `uploaded`) VALUES (:collaborationid, :memberid, :postcontent, CURRENT_TIMESTAMP)");
                                                $data = [
                                                    'collaborationid' => $collaborationQuery['id'],
                                                    'memberid' => $_SESSION['member_id'],
                                                    'postcontent' => $purifiedPostText,
                                                ];
                                                $stmt->execute($data);

                                                $lastInsertIdAudio = $db->lastInsertId();

                                                $extension =  pathinfo($fileName, PATHINFO_EXTENSION);
                                                $target_file = $target_dir . "postfile_". $lastInsertIdAudio . "_" . $audioKey .".". $extension;
                                                $fullAudioName = "postfile_". $lastInsertIdAudio . "_" . $audioKey .".". $extension;                  

                                                if (move_uploaded_file($tmpFileName, $target_file)) {
                                                                        
                                                    echo " The file ". basename($fileName). " has been received. ";
                                                        
                                                    $mbAudioSize = bytes_to_megabytes(filesize($target_file));

                                                    $purifiedPostFileLabel = $purifier->purify($_POST["createNewPostLinkInput"]);
    
                                                    if (strlen($purifiedPostFileLabel) <= 200) {
                                                    
                                                        $stmt = $db->prepare("UPDATE `collaboration_posts` SET `file_label` = :filelabel,  `file_key` = :filekey, `filename` = :postfilename, `mbSize` = :mbsize WHERE `id` = :lastinsertidaudio AND `collaboration_id` = :collaborationid");
                                                        $data = [                                    
                                                            'filelabel' => $audioLabelPurify,                           
                                                            'filekey' => $audioKey,
                                                            'postfilename' => $fullAudioName,
                                                            'mbsize' => $mbAudioSize,     
                                                            'lastinsertidaudio' => $lastInsertIdAudio,   
                                                            'collaborationid' => $collaborationQuery['id'],        
                                                        ];
                                                        $stmt->execute($data);
                                                        
                                                        header("Location: "."./collaboration/".$userInCollaborationQuery["collaboration_id"]."/".$userInCollaborationQuery["collaboration_key"]);
                                                    }
                                                    else{
                                                        echo " Sorry, you file label was too long.";
                                                    }
                                                
                                                }
                                                else{
                                                    echo " Sorry, there was an error while uploading your audio.";
                                                }

                                            }


                                        }                  
                                        else
                                        {     
                                            echo "<br>An error occured when uploading the audio. ".$_FILES["newPostAudioFile"]["error"].".";                           
                                            
                                        }

                                    }
                                    else {
                                        echo "Error: Post text is too long.";
                                    }
                                }
                                else {
                                    echo "Error: Post text not received.";
                                }
                            }
                            else {
                                echo "Error: The allowed number of posts per collaboration has been exceeded.";
                            }
                        }
                        else {
                            echo "Note: You are not part of this collaboration.";
                        }

                }
                else {
                    echo "Error: You do not have access to this resource.";
                }
            }

        }
        else {
            echo "Error: Target collaboration does not exist.";
        }
    }
    else {
        echo " POST Error: The Required Information Was Not Received.";
    }
}
?>