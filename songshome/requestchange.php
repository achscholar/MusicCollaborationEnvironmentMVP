<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST["collaborationId"]) && isset($_POST["collaborationKey"]) && isset($_POST["targetPostId"]) && isset($_POST["requestMemberId"]) && isset($_POST["requestMessage"])  ) { 
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

                            // Retrieve # of collaboration requests.
                            $stmt = $db->prepare("SELECT `id` FROM `collaboration_requests` WHERE `collaboration_id` = :collaborationid");
                            $stmt->bindParam('collaborationid',  $collaborationQuery["id"]);
                            $stmt->execute();
                            $allCollaborationRequestsQuery = $stmt->fetchAll();

                            if (sizeof($allCollaborationRequestsQuery) <= 1000) { // Limit # of collaboration posts to 1000.

                                if ($_POST["requestMessage"] != "") { // Check if the post message was received.

                                    // Purify user input against HTML Injection
                                    require_once './htmlpurifier/library/HTMLPurifier.auto.php';   
                                    $config = HTMLPurifier_Config::createDefault();
                                    $purifier = new HTMLPurifier($config);
                                    $purifiedRequestMessage = $purifier->purify($_POST["requestMessage"]);

                                    if (strlen($purifiedRequestMessage) <= 10000 && strlen($purifiedRequestMessage) != 0) { // Ensure proper length of request message before upload.
                                        
                                        // Retrieve target post details.
                                        $stmt = $db->prepare("SELECT * FROM `collaboration_posts` WHERE `id` = :targetpostid AND `collaboration_id` = :collaborationid");
                                        $stmt->bindParam('targetpostid',  $_POST["targetPostId"]);
                                        $stmt->bindParam('collaborationid',  $collaborationQuery["id"]);
                                        $stmt->execute();
                                        $targetPostQuery = $stmt->fetch();

                                        if ($targetPostQuery && ($targetPostQuery["filename"] != "" || $targetPostQuery["link"] != "")) { // Ensure the target post exists.
                                        
                                            $requestFor = "";
                                            if ($_POST["requestMemberId"] == "PUBLIC") {
                                                if ($access_level >= 5) {
                                                    $requestFor = "PUBLIC";
                                                }
                                                
                                            }
                                            else if ($_POST["requestMemberId"] == "COLLABORATIONPARTICIPANTS") {

                                                 // Retrieve all collaboration participants.
                                                $stmt = $db->prepare("SELECT `member_id` FROM `collaboration_participants` WHERE `songId` = :songid AND `collaboration_id` = :collaborationid AND `collaboration_key` = :collaborationkey");
                                                $stmt->bindParam('songid',  $collaborationQuery["songId"]);
                                                $stmt->bindParam('collaborationid',  $collaborationQuery["id"]);
                                                $stmt->bindParam('collaborationkey',  $_POST["collaborationKey"]);
                                                $stmt->execute();
                                                $allCollaborationParticipantsQuery = $stmt->fetchAll();

                                                if ($allCollaborationParticipantsQuery && sizeof($allCollaborationParticipantsQuery) > 0) {

                                                    $participants = array();
                                                    foreach ($allCollaborationParticipantsQuery as $participantId) {
                                                        $participants[] = $participantId['member_id'];
                                                    }
                                                    $requestFor = implode(",", $participants); // Set the request for the list of all collaboration participants.
                                                }
                                            }
                                            else {
                                                // Retrieve target participant.
                                                $stmt = $db->prepare("SELECT `member_id` FROM `collaboration_participants` WHERE `songId` = :songid AND `collaboration_id` = :collaborationid AND `collaboration_key` = :collaborationkey AND `member_id` = :memberid");
                                                $stmt->bindParam('songid',  $collaborationQuery["songId"]);
                                                $stmt->bindParam('collaborationid',  $collaborationQuery["id"]);
                                                $stmt->bindParam('collaborationkey',  $_POST["collaborationKey"]);
                                                $stmt->bindParam('memberid',  $_POST["requestMemberId"]);
                                                $stmt->execute();
                                                $participantsQuery = $stmt->fetch();
                                                if ($participantsQuery) {
                                                    $requestFor = $participantsQuery["member_id"]; // Set the request for the target member.
                                                }
                                                
                                            }

                                            if ($requestFor != ""){
                                                if ($targetPostQuery["link"] == "") { // Target post contained a file.

                                                    $stmt = $db->prepare("INSERT INTO `collaboration_requests` (`collaboration_id`, `member_id`, `content`, `uploaded`, `request_for`, `target_post_id`, `target_filename`, `target_filelabel`, `target_filekey`) VALUES (:collaborationid, :memberid, :requestcontent, CURRENT_TIMESTAMP, :requestfor, :targetpostid, :targetfilename, :targetfilelabel, :targetfilekey)");
                                                    $data = [
                                                        'collaborationid' => $collaborationQuery['id'],
                                                        'memberid' => $_SESSION['member_id'],
                                                        'requestcontent' => $purifiedRequestMessage,
                                                        'requestfor' => $requestFor,
                                                        'targetpostid' => $targetPostQuery['id'],
                                                        'targetfilename' => $targetPostQuery['filename'],
                                                        'targetfilelabel' => $targetPostQuery['file_label'],
                                                        'targetfilekey' => $targetPostQuery['file_key'],
                                                    ];
                                                    $stmt->execute($data);

                                                }
                                                else { // Target post contained a link.
                                                    $stmt = $db->prepare("INSERT INTO `collaboration_requests` (`collaboration_id`, `member_id`, `content`, `uploaded`, `request_for`, `target_post_id`, `target_link`) VALUES (:collaborationid, :memberid, :requestcontent, CURRENT_TIMESTAMP, :requestfor, :targetpostid, :targetlink)");
                                                    $data = [
                                                        'collaborationid' => $collaborationQuery['id'],
                                                        'memberid' => $_SESSION['member_id'],
                                                        'requestcontent' => $purifiedRequestMessage,
                                                        'requestfor' => $requestFor,
                                                        'targetpostid' => $targetPostQuery['id'],
                                                        'targetlink' => $targetPostQuery['link'],
                                                    ];
                                                    $stmt->execute($data);
                                                }
                                            }
                                            

                                            header("Location: "."./collaboration/".$userInCollaborationQuery["collaboration_id"]."/".$userInCollaborationQuery["collaboration_key"]);

                                        }
                                        else {
                                            echo "Error: Target post does not exist.";
                                        }
                                        

                                    }
                                    else {
                                        echo "Error: Request message is invalid.";
                                    }
                                }
                                else {
                                    echo "Error: Request message not received.";
                                }
                            }
                            else {
                                echo "Error: The allowed number of requests per collaboration has been exceeded.";
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