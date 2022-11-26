<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST["collaborationId"]) && isset($_POST["collaborationKey"]) && isset($_POST["closeRequestId"])) {
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

                     // Retrieve the target request.
                    $stmt = $db->prepare("SELECT * FROM `collaboration_requests` WHERE `id` = :requestid");
                    $stmt->bindParam('requestid',  $_POST["closeRequestId"]);
                    $stmt->execute();
                    $targetRequestQuery = $stmt->fetch();

                        if ($userInCollaborationQuery && $targetRequestQuery && ($access_level >= 5 || $targetRequestQuery["member_id"] == $_SESSION['member_id'])) { // User is in collaboration and is a Group Leader or submission creator.
                            $stmt = $db->prepare("UPDATE `collaboration_requests` SET `open` = :requestopen WHERE `id` = :requestid AND `collaboration_id` = :collaborationid");
                            $data = [         
                                'requestopen' => 0,                                                  
                                'requestid' =>  $_POST["closeRequestId"],  
                                'collaborationid' => $collaborationQuery['id'],   
                            ];
                            $stmt->execute($data);

                            // Remove unverified public submissions.
                            try {                             
                                $stmt = $db->prepare("DELETE FROM `collaboration_request_files` WHERE `collaboration_id` = :collaborationid AND `collaboration_requests_id` = :collaborationrequestsid AND `verified` = :submissionverified AND `member_id` IS NULL");
                                $data = [
                                    'collaborationid' => $collaborationQuery["id"],
                                    'collaborationrequestsid' => $_POST["closeRequestId"],
                                    'submissionverified' => 0,
                                ];
                                $stmt->execute($data);                                               
                            } catch (Exception $e) {
                                echo '<br>Caught exception: ',  $e->getMessage(), "\n";
                            }

                            header("Location: "."./collaboration/".$collaborationQuery["id"]."/".$collaborationQuery["collaboration_key"]);
                        }
                        else {
                            echo "Error: You cannot close this submission.";
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