<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST["collaborationId"]) && isset($_POST["collaborationKey"]) && isset($_POST["collaborationRequestId"]) && isset($_POST["requestLinkInput"]) && isset($_FILES["audioFiles"]) ) { 
        require('./includes/db.php');
        require("functions.php");

        // Security Component
        require("userDetailsChangedCheck.php"); //START SESSION
        require("retrieveUserAccessLevel.php");

        // COLLABORATION KEY CHECK
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

                            // Retrieve target request details.
                            $stmt = $db->prepare("SELECT * FROM `collaboration_requests` WHERE `id` = :collaborationrequestsid AND `collaboration_id` = :collaborationid");
                            $stmt->bindParam('collaborationrequestsid',  $_POST["collaborationRequestId"]);
                            $stmt->bindParam('collaborationid',  $collaborationQuery["id"]);
                            $stmt->execute();
                            $targetRequestQuery = $stmt->fetch();

                            $requestedMemberIds= array();
                            if ($targetRequestQuery["request_for"] != "PUBLIC") { // Format the requested user id list.
                                $requestedMemberIds = explode(",", $targetRequestQuery["request_for"]);
                            }

                            if ($targetRequestQuery["open"] == 1 && ($targetRequestQuery["request_for"] == "PUBLIC" || in_array($userInCollaborationQuery["member_id"], $requestedMemberIds) || $access_level >= 5)) { // Check if the request was made for this user.

                                // Retrieve # of collaboration request submissions.
                                $stmt = $db->prepare("SELECT `id` FROM `collaboration_request_files` WHERE `collaboration_id` = :collaborationid AND `collaboration_requests_id` = :collaborationrequestsid AND `verified` = 1");
                                $stmt->bindParam('collaborationid',  $collaborationQuery["id"]);
                                $stmt->bindParam('collaborationrequestsid',  $_POST["collaborationRequestId"]);
                                $stmt->execute();
                                $allCollaborationRequestSubmissionsQuery = $stmt->fetchAll();

                                if (sizeof($allCollaborationRequestSubmissionsQuery) <= 100) { // Limit # of collaboration request submissions to 100.

                                    // Purify user input against HTML Injection
                                    require_once './htmlpurifier/library/HTMLPurifier.auto.php';   
                                    $config = HTMLPurifier_Config::createDefault();
                                    $purifier = new HTMLPurifier($config);

                                    $verified = 1; // Distinguishes this submission from a public submission.


                                    if ($_POST["requestLinkInput"] != "") { // User uploaded a link to a file.

                                        $purifiedPostLink = $purifier->purify($_POST["requestLinkInput"]);

                                        if (strlen($purifiedPostLink) <= 1000 && strlen($purifiedPostLink) != 0) { // Ensure proper length of link before upload.
                                            $stmt = $db->prepare("INSERT INTO `collaboration_request_files` (`collaboration_id`, `collaboration_requests_id`, `member_id`, `link`, `uploaded`, `verified`) VALUES (:collaborationid, :collaborationrequestsid, :memberid, :submissionlink, CURRENT_TIMESTAMP, :verified)");
                                            $data = [
                                                'collaborationid' => $collaborationQuery['id'],
                                                'collaborationrequestsid' => $targetRequestQuery['id'],
                                                'memberid' => $_SESSION['member_id'],
                                                'submissionlink' => $purifiedPostLink,
                                                'verified' => $verified,
                                            ];
                                            $stmt->execute($data);

                                            header("Location: "."./collaboration/".$userInCollaborationQuery["collaboration_id"]."/".$userInCollaborationQuery["collaboration_key"]);
                                        }
                                        else {
                                            echo "Error: Link is invalid or too long.";
                                        }

                                    }
                                    else if ($_FILES["audioFiles"]["error"] == 0) { // User uploaded a file.

                                        $uploadOk = 0;

                                        $fileName = $_FILES["audioFiles"]["name"];
                                        $tmpFileName = $_FILES["audioFiles"]["tmp_name"];

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
                                        $fileNamePurify = $purifier->purify($fileName);
                                        $tmpFileNamePurify = $purifier->purify($tmpFileName);
                                        if ($fileName != $fileNamePurify || $tmpFileName != $tmpFileNamePurify) {
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
                                    
                                            $target_dir = "./collaborationRequestFiles/"; 
                                            $audioKey = getRandKey(12);

                                            $stmt = $db->prepare("INSERT INTO `collaboration_request_files` (`collaboration_id`, `collaboration_requests_id`, `member_id`, `uploaded`) VALUES (:collaborationid, :collaborationrequestsid, :memberid, CURRENT_TIMESTAMP)");
                                            $data = [
                                                'collaborationid' => $collaborationQuery['id'],
                                                'collaborationrequestsid' => $targetRequestQuery['id'],
                                                'memberid' => $_SESSION['member_id'],
                                            ];
                                            $stmt->execute($data);

                                            $lastInsertIdAudio = $db->lastInsertId();

                                            $extension =  pathinfo($fileName, PATHINFO_EXTENSION);
                                            $target_file = $target_dir . "postrequest_". $lastInsertIdAudio . "_" . $audioKey .".". $extension;
                                            $fullAudioName = "postrequest_". $lastInsertIdAudio . "_" . $audioKey .".". $extension;                  

                                            if (move_uploaded_file($tmpFileName, $target_file)) {
                                                                    
                                                echo " The file ". basename($fileName). " has been received. ";
                                                    
                                                $mbAudioSize = bytes_to_megabytes(filesize($target_file));
                                                
                                                $stmt = $db->prepare("UPDATE `collaboration_request_files` SET `filename` = :requestfilename,  `file_key` = :filekey, `mbSize` = :mbsize, `verified` = :requestverified WHERE `id` = :lastinsertidaudio AND `collaboration_id` = :collaborationid AND `collaboration_requests_id` = :collaborationrequestsid");
                                                $data = [         
                                                    'requestfilename' => $fullAudioName,                                                  
                                                    'filekey' => $audioKey,
                                                    'mbsize' => $mbAudioSize,     
                                                    'requestverified' => $verified,   
                                                    'lastinsertidaudio' => $lastInsertIdAudio,   
                                                    'collaborationid' => $collaborationQuery['id'],   
                                                    'collaborationrequestsid' => $targetRequestQuery['id'],     
                                                ];
                                                $stmt->execute($data);

                                                header("Location: "."./collaboration/".$userInCollaborationQuery["collaboration_id"]."/".$userInCollaborationQuery["collaboration_key"]);
                                            }
                                            else{
                                                echo " Sorry, there was an error while uploading your audio.";
                                            }

                                        }


                                    }                  
                                    else
                                    {     
                                        echo "<br>An error occured when uploading the audio. ".$_FILES["audioFiles"]["error"].".";                           
                                        
                                    }

                                }
                                else {
                                    echo "Error: The allowed number of submissions per request has been exceeded.";
                                }
                            }
                            else {
                                echo "You cannot participate in this request submission.";
                            }
                            
                        }
                        else {
                            echo "You are not part of this collaboration.";
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