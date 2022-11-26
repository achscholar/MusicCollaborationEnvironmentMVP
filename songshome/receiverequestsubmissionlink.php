<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST["requestFirstname"]) && isset($_POST["requestLastname"]) && isset($_POST["requestEmail"]) && isset($_POST["collaborationId"]) && isset($_POST["collaborationKey"]) && isset($_POST["collaborationRequestId"])) {
        require('./includes/db.php');
        require("functions.php");

        // Purify user input against HTML Injection
        require_once './htmlpurifier/library/HTMLPurifier.auto.php';       
        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
    
        $visitorFirstname = $_POST["requestFirstname"];
        $visitorFirstname = $purifier->purify($visitorFirstname);

        $visitorLastname = $_POST["requestLastname"];
        $visitorLastname = $purifier->purify($visitorLastname);

        $visitorEmail = $_POST["requestEmail"];
        $visitorEmail = $purifier->purify($visitorEmail);

        $addVisitorSubmissionOk = 1;

        // Reject on banned characters in title.
        require("bannedChars.php");
        $requestFirstnameCheck = strpos_multi($bannedArray, $visitorFirstname);
        $requestLastnameCheck = strpos_multi($bannedArray, $visitorLastname);
        $requestEmailCheck = strpos_multi($bannedArray, $visitorEmail);
        if ($requestFirstnameCheck == "0" || $requestLastnameCheck == "0" || $requestEmailCheck == "0") {
            $addVisitorSubmissionOk = 0;
        }

        // Ensure proper length.
        if (strlen($visitorFirstname) > 20 || strlen($visitorFirstname) == 0){
            $addVisitorSubmissionOk = 0;                
        }
        if (strlen($visitorLastname) > 30 || strlen($visitorLastname) == 0){
            $addVisitorSubmissionOk = 0;                
        }
        if (strlen($visitorEmail) > 70 || strlen($visitorEmail) == 0){
            $addVisitorSubmissionOk = 0;                
        }

        if (!filter_var($_POST["requestEmail"], FILTER_VALIDATE_EMAIL)) {
            $addVisitorSubmissionOk = 0; 
            echo "Invalid email provided. ";
        }

        if ($addVisitorSubmissionOk) {

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

                    // Navigation menu & logo setup.
                    require("userDetailsChangedCheck.php"); //START SESSION
                    require("retrieveUserAccessLevel.php");
                    $parentGroupNavId = retrieveParentGroupId($songQuery["groupId"], $db);
                    $parentGroupNavKey = retrieveParentGroupKey($songQuery["groupId"], $db);
                    $logoBarLink = "/songshome/group/".$parentGroupNavId."/".$parentGroupNavKey;
                    $navigationLinks[] = "<a class=\"navLinks\"  href=\"/songshome/group/".$parentGroupNavId."/".$parentGroupNavKey."\">Home</a>";
                    $navigationLinks[] = "<a class=\"navLinks\"  href=\"/songshome/signin.php\">Sign In</a>";

                    // Retrieve the target request.
                    $stmt = $db->prepare("SELECT * FROM `collaboration_requests` WHERE `collaboration_id` = :collaborationid AND `id` = :requestid AND `request_for` = 'PUBLIC' AND `open` = 1");
                    $stmt->bindParam('collaborationid',  $collaborationQuery["id"]);
                    $stmt->bindParam('requestid',  $_POST["collaborationRequestId"]);
                    $stmt->execute();
                    $targetRequestQuery = $stmt->fetch();
                    
                    if ($targetRequestQuery) {
                        // Retrieve total # of collaboration request submissions. Used to ensure over 100 submissions are not submitted.
                        $stmt = $db->prepare("SELECT `id` FROM `collaboration_request_files` WHERE `collaboration_id` = :collaborationid AND `collaboration_requests_id` = :collaborationrequestsid AND `verified` = 1");
                        $stmt->bindParam('collaborationid',  $collaborationQuery["id"]);
                        $stmt->bindParam('collaborationrequestsid',  $_POST["collaborationRequestId"]);
                        $stmt->execute();
                        $allCollaborationRequestSubmissionsQuery = $stmt->fetchAll();

                        // Limit the number of allowed unverified requests to prevent a spam attack.
                        $stmt = $db->prepare("SELECT `id` FROM `collaboration_request_files` WHERE `collaboration_id` = :collaborationid AND `collaboration_requests_id` = :collaborationrequestsid AND `verified` = 0");
                        $stmt->bindParam('collaborationid',  $collaborationQuery["id"]);
                        $stmt->bindParam('collaborationrequestsid',  $_POST["collaborationRequestId"]);
                        $stmt->execute();
                        $allUnverifiedSubmissionsQuery = $stmt->fetchAll();

                        // Check if any submissions exist for the target request for this email. Used to ensure only one request per email can be submitted.
                        $stmt = $db->prepare("SELECT * FROM `collaboration_request_files` WHERE `visitor_email` = :visitoremail AND `collaboration_requests_id` = :requestid AND `collaboration_id` = :collaborationid AND `member_id` IS NULL");
                        $stmt->bindParam('visitoremail',  $_POST["requestEmail"]);
                        $stmt->bindParam('requestid',  $_POST["collaborationRequestId"]);
                        $stmt->bindParam('collaborationid',  $collaborationQuery["id"]);
                        $stmt->execute();
                        $checkIfSubmissionsExistQuery = $stmt->fetch();

                        if (sizeof($allCollaborationRequestSubmissionsQuery) <= 100 && sizeof($allUnverifiedSubmissionsQuery) <= 5000 && !$checkIfSubmissionsExistQuery) {
                            $visitorKey = getRandKey(30);
                            $stmt = $db->prepare("INSERT INTO collaboration_request_files (collaboration_id, collaboration_requests_id, uploaded, visitor_firstname, visitor_lastname, visitor_email, visitor_key, verified) VALUES (:collaborationid, :collaborationrequestsid, CURRENT_TIMESTAMP, :visitorfirstname, :visitorlastname, :visitoremail, :visitorkey, 0)");
                            $data = [
                                'collaborationid' => $collaborationQuery["id"],
                                'collaborationrequestsid' => $_POST["collaborationRequestId"],  
                                'visitorfirstname' => $visitorFirstname,      
                                'visitorlastname' => $visitorLastname,     
                                'visitoremail' => $visitorEmail,
                                'visitorkey' => $visitorKey,                         
                            ];
                            $stmt->execute($data);

                            $lastInsertId = $db->lastInsertId();

                            // SEND EMAIL CODE WILL BE HERE.

                            print("<!DOCTYPE html>
                            <html lang=\"en\">
                            <head>");
                            include('./includes/defaultheadtags.html');
                            print("
                            <title>Music Collab. Environ. - Submission Request Received. Check Your Email</title>
                            </head>
                            <body>");
                            require("navmenustart.php");
                            print("<h2 class=\"nunito\" style=\"padding-left: 20px;padding-right:20px;\">The link has been sent to the email: $visitorEmail. Please visit the link sent to your email to upload your submission. Try checking your spam folder if it was not received.</h2>");
                            print("<p class=\"arial\" style=\"padding-left: 20px;padding-right:20px;\">The email server has not been implemented. It will be implemented along with the login system. The user will receive this link to their email: <a href=\"./uploadPage/$lastInsertId/$visitorKey\">".$_SERVER['SERVER_NAME']."/uploadPage/$lastInsertId/$visitorKey</p></a>");
                            require("navmenuend.php");
                            print("
                            </body>
                            </html>
                            ");
                        }
                        else if ($checkIfSubmissionsExistQuery) { // The user is submitting a duplicate submission.

                            // Check if the submission has not already been received.
                            $stmt = $db->prepare("SELECT * FROM `collaboration_request_files` WHERE `visitor_email` = :visitoremail AND `collaboration_requests_id` = :requestid AND `collaboration_id` = :collaborationid AND `verified` = 1 AND `member_id` IS NULL");
                            $stmt->bindParam('visitoremail',  $_POST["requestEmail"]);
                            $stmt->bindParam('requestid',  $_POST["collaborationRequestId"]);
                            $stmt->bindParam('collaborationid',  $collaborationQuery["id"]);
                            $stmt->execute();
                            $checkSubmissionReceivedQuery = $stmt->fetch();

                            print("<!DOCTYPE html>
                            <html lang=\"en\">
                            <head>");
                            include('./includes/defaultheadtags.html');
                            print("
                            <title>Music Collab. Environ. - Submission Request Received</title>
                            </head>
                            <body>");
                            require("navmenustart.php");

                            if (!$checkSubmissionReceivedQuery) {

                                // Check if any emails were sent whitin the specified timeframe.
                                $stmt = $db->prepare("SELECT * FROM `collaboration_request_files` WHERE `uploaded` >= NOW() - INTERVAL 10 MINUTE AND `visitor_email` = :visitoremail AND `collaboration_requests_id` = :requestid AND `collaboration_id` = :collaborationid AND `member_id` IS NULL");
                                $stmt->bindParam('visitoremail',  $_POST["requestEmail"]);
                                $stmt->bindParam('requestid',  $_POST["collaborationRequestId"]);
                                $stmt->bindParam('collaborationid',  $collaborationQuery["id"]);
                                $stmt->execute();
                                $checkSubmissionTimeframe = $stmt->fetch();
                                
                                // User has already tried to receive a submission link, send it again.
                                
                                if (!$checkSubmissionTimeframe) { // Send another link to the users email.

                                    // Update timestamp of last sent email.
                                    $stmt = $db->prepare("UPDATE `collaboration_request_files` SET `uploaded` = CURRENT_TIMESTAMP WHERE `visitor_email` = :visitoremail AND `collaboration_requests_id` = :requestid AND `collaboration_id` = :collaborationid AND `member_id` IS NULL");
                                    $data = [    
                                        'visitoremail' => $visitorEmail,
                                        'requestid' => $_POST["collaborationRequestId"],
                                        'collaborationid' => $collaborationQuery["id"],                       
                                    ];
                                    $stmt->execute($data);

                                    // SEND EMAIL CODE WILL BE HERE.

                                    print("<h2 class=\"nunito\" style=\"padding-left: 20px;padding-right:20px;\">A link has been sent again to the email: $visitorEmail. Please visit the link sent to your email to upload your submission. Try checking your spam folder if it was not received.</h2>");
                                    print("<p class=\"arial\" style=\"padding-left: 20px;padding-right:20px;\">The email server has not been implemented. It will be implemented along with the login system. The user will receive this link to their email: <a href=\"./uploadPage/".$checkIfSubmissionsExistQuery['id']."/".$checkIfSubmissionsExistQuery['visitor_key']."\">".$_SERVER['SERVER_NAME']."/uploadPage/".$checkIfSubmissionsExistQuery['id']."/".$checkIfSubmissionsExistQuery['visitor_key']."</p></a>");
                                }
                                else { // The user is trying to send another link too soon, make them wait.
                                    print("<h2 class=\"nunito\" style=\"padding-left: 20px;padding-right:20px;\">Please wait at least 10 minutes before attempting to request another link to your email: $visitorEmail. Try checking your spam folder if it was not received.</h2>");
                                }
                                

                            }
                            else {
                                print("<h2 class=\"nunito\" style=\"padding-left: 20px;padding-right:20px;\">Your submission has already been received for this request. Thank you!</h2>");
                            }

                            require("navmenuend.php");
                            print("
                            </body>
                            </html>
                            ");
                        }

                       
                    }

    
                }

            }
            else {
                echo "Error: Target collaboration does not exist.";
            }

        }
        else {
            echo "Error: Submission Input Ivalid.";
        }

    }
    else {
        echo " POST Error: The Required Information Was Not Received.";
    }
}
?>