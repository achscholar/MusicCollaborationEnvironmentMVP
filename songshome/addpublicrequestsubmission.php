<!DOCTYPE html>
<html lang="en">
<head>
<?php include('./includes/defaultheadtags.html');?>
<title>Music Collab. Environ. - Upload Submission</title>

</head>
<body>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    require('./includes/db.php');

    if (isset($_POST["collaborationId"]) && isset($_POST["collaborationKey"]) && isset($_POST["collaborationRequestId"]) && isset($_POST["requestLinkInput"]) && isset($_FILES["audioFiles"]) && isset($_POST["requests_file_id"]) && isset($_POST["visitor_key"])) { 
        require("functions.php");

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
                            
                // Navigation menu & logo setup.
                $parentGroupNavId = retrieveParentGroupId($songQuery["groupId"], $db);
                $parentGroupNavKey = retrieveParentGroupKey($songQuery["groupId"], $db);
                $logoBarLink = "/songshome/group/".$parentGroupNavId."/".$parentGroupNavKey;
                $navigationLinks[] = "<a class=\"navLinks\"  href=\"/songshome/group/".$parentGroupNavId."/".$parentGroupNavKey."\">Home</a>";
                $navigationLinks[] = "<a class=\"navLinks\"  href=\"/songshome/signin.php\">Sign In</a>";
                require("navmenustart.php");

                // Check if the submission has not already been received.
                $stmt = $db->prepare("SELECT * FROM `collaboration_request_files` WHERE `id` = :requestsfileid AND `visitor_key` = :visitorkey AND `verified` = 0 AND `member_id` IS NULL");
                $stmt->bindParam('requestsfileid',  $_POST["requests_file_id"]);
                $stmt->bindParam('visitorkey',  $_POST["visitor_key"]);
                $stmt->execute();
                $checkSubmissionReceivedQuery = $stmt->fetch();

                if ($checkSubmissionReceivedQuery) {

                    // Retrieve the target request.
                    $stmt = $db->prepare("SELECT * FROM `collaboration_requests` WHERE `collaboration_id` = :collaborationid AND `id` = :requestid AND `request_for` = 'PUBLIC' AND `open` = 1");
                    $stmt->bindParam('collaborationid',  $checkSubmissionReceivedQuery["collaboration_id"]);
                    $stmt->bindParam('requestid',  $checkSubmissionReceivedQuery["collaboration_requests_id"]);
                    $stmt->execute();
                    $targetRequestQuery = $stmt->fetch();
                
                    if ($targetRequestQuery) {
                        // Check the last upload attempt. Limit upload attempts to every 3 minutes.
                        $stmt = $db->prepare("SELECT * FROM `collaboration_request_files` WHERE  `last_public_upload` >= NOW() - INTERVAL 3 MINUTE AND `id` = :requestsfileid AND `visitor_key` = :visitorkey AND `verified` = 0 AND `member_id` IS NULL");
                        $stmt->bindParam('requestsfileid',  $_POST["requests_file_id"]);
                        $stmt->bindParam('visitorkey',  $_POST["visitor_key"]);
                        $stmt->execute();
                        $checkSubmissionIntervalQuery = $stmt->fetch();

                        if (!$checkSubmissionIntervalQuery) {

                            // Update last uploaded timestamp to prevent spam uploads.
                            $stmt = $db->prepare("UPDATE `collaboration_request_files` SET `last_public_upload` = CURRENT_TIMESTAMP WHERE `id` = :requestsfileid AND `visitor_key` = :visitorkey AND `verified` = 0 AND `member_id` IS NULL");
                            $data = [    
                                'requestsfileid'  =>  $_POST["requests_file_id"],
                                'visitorkey'  =>  $_POST["visitor_key"],              
                            ];
                            $stmt->execute($data);

                            // Purify user input against HTML Injection
                            require_once './htmlpurifier/library/HTMLPurifier.auto.php';   
                            $config = HTMLPurifier_Config::createDefault();
                            $purifier = new HTMLPurifier($config);

                            if ($_POST["requestLinkInput"] != "") { // User uploaded a link to a file.

                                $purifiedPostLink = $purifier->purify($_POST["requestLinkInput"]);

                                if (strlen($purifiedPostLink) <= 1000 && strlen($purifiedPostLink) != 0) { // Ensure proper length of link before upload.

                                    // Upload public submission.
                                    $stmt = $db->prepare("UPDATE `collaboration_request_files` SET `link` = :submissionlink, `last_public_upload` = CURRENT_TIMESTAMP, `verified` = :submissionverified WHERE `id` = :requestsfileid AND `visitor_key` = :visitorkey AND `verified` = 0 AND `member_id` IS NULL");
                                    $data = [    
                                        'requestsfileid'  =>  $_POST["requests_file_id"],
                                        'visitorkey'  =>  $_POST["visitor_key"],
                                        'submissionlink' => $purifiedPostLink,
                                        'submissionverified' => 1,                   
                                    ];
                                    $stmt->execute($data);

                                    print("<h2 class=\"nunito\" style=\"padding-left: 20px;padding-right:20px;\">Your submission has been received. Thank you!");
                                    print("<label onclick=\"location.href='./collaboration/".$collaborationQuery["id"]."/".$collaborationQuery["collaboration_key"]."'\" class=\"button\" style=\"display:inline-block;margin-left: 20px;\">
                                            <span style=\"z-index:1;\">Return to Collaboration</span>
                                        </label>
                                        </h2>");
                                }
                                else {
                                    echo "Error: Link is invalid or too long.";
                                }

                            }
                            else if ($_FILES["audioFiles"]["error"] == 0) { // User uploaded a file.

                                $uploadOk = 0;

                                $fileName = $_FILES["audioFiles"]["name"];
                                $tmpFileName = $_FILES["audioFiles"]["tmp_name"];

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

                                    $extension =  pathinfo($fileName, PATHINFO_EXTENSION);
                                    $target_file = $target_dir . "postrequest_".  $checkSubmissionReceivedQuery["id"] . "_" . $audioKey .".". $extension;
                                    $fullAudioName = "postrequest_".  $checkSubmissionReceivedQuery["id"] . "_" . $audioKey .".". $extension;                  

                                    if (move_uploaded_file($tmpFileName, $target_file)) {
                                                            
                                        // echo " The file ". basename($fileName). " has been received. ";
                                            
                                        $mbAudioSize = bytes_to_megabytes(filesize($target_file));

                                        // Upload public submission.
                                        $stmt = $db->prepare("UPDATE `collaboration_request_files` SET `filename` = :requestfilename, `file_key` = :filekey, `mbSize` = :mbsize, `last_public_upload` = CURRENT_TIMESTAMP, `verified` = :submissionverified WHERE `id` = :requestsfileid AND `visitor_key` = :visitorkey AND `verified` = 0 AND `member_id` IS NULL");
                                        $data = [   
                                            'requestfilename' => $fullAudioName,                                                  
                                            'filekey' => $audioKey,
                                            'mbsize' => $mbAudioSize,      
                                            'submissionverified' => 1,   
                                            'requestsfileid'  =>  $_POST["requests_file_id"],
                                            'visitorkey'  =>  $_POST["visitor_key"],                   
                                        ];
                                        $stmt->execute($data);


                                        print("<h2 class=\"nunito\" style=\"padding-left: 20px;padding-right:20px;\">Your submission has been received. Thank you!");
                                        print("<label onclick=\"location.href='./collaboration/".$collaborationQuery["id"]."/".$collaborationQuery["collaboration_key"]."'\" class=\"button\" style=\"display:inline-block;margin-left: 20px;\">
                                                <span style=\"z-index:1;\">Return to Collaboration</span>
                                            </label>
                                            </h2>");

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
                        else { // User is attempting to upload again too soon.
                            print("Please wait at least 3 minutes before your last upload attempt to make another upload.");
                        }
                    }
                    else { // This request has been closed.
                        print("This request has been closed.");
                    }
                }
                else { // User already uploaded submission or this submission does not exist.
                    print("You have already uploaded your submission or this request has been closed. Thank you!");
                }

                require("navmenuend.php");

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

</body>
</html>