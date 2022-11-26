<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST["collaborationId"]) && isset($_POST["collaborationKey"]) && isset($_POST["removeRequestId"]) && isset($_POST["removeRequestSubmissionId"])) {
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
                 
                if (isset($_SESSION['member_id'])) { // User is logged in.

                    // Check if user is in the target collaboration.
                    $stmt = $db->prepare("SELECT * FROM `collaboration_participants` WHERE `songId` = :songid AND `collaboration_id` = :collaborationid AND `member_id` = :memberid");
                    $stmt->bindParam('songid',  $collaborationQuery["songId"]);
                    $stmt->bindParam('collaborationid',  $collaborationQuery["id"]);
                    $stmt->bindParam('memberid',  $_SESSION['member_id']);
                    $stmt->execute();
                    $userInCollaborationQuery = $stmt->fetch();

                    // Retrieve the target request.
                    $stmt = $db->prepare("SELECT * FROM `collaboration_requests` WHERE `collaboration_id` = :collaborationid AND `id` = :requestid");
                    $stmt->bindParam('collaborationid',  $collaborationQuery["id"]);
                    $stmt->bindParam('requestid',  $_POST["removeRequestId"]);
                    $stmt->execute();
                    $targetRequestQuery = $stmt->fetch();

                     // Retrieve the target submission for this request.
                    $stmt = $db->prepare("SELECT * FROM `collaboration_request_files` WHERE `collaboration_id` = :collaborationid AND `collaboration_requests_id` = :requestid AND `id` = :submissionid");
                    $stmt->bindParam('collaborationid',  $collaborationQuery["id"]);
                    $stmt->bindParam('requestid',  $_POST["removeRequestId"]);
                    $stmt->bindParam('submissionid',  $_POST["removeRequestSubmissionId"]);
                    $stmt->execute();
                    $targetSubmissionQuery = $stmt->fetch();

                    // DO NOT CHANGE, unless you ensure both members have the same parent groups. Check if user is the creator of the collaboration or is a group leader or the user whos trying to remove their own post.
                    if ($userInCollaborationQuery && ($collaborationQuery["created_by_member_id"] == $_SESSION['member_id'] || $_SESSION['member_id'] == $targetRequestQuery["member_id"] || $_SESSION['member_id'] == $targetSubmissionQuery["member_id"] || $access_level >= 5)) {

                        $allDeleted = 1;

                        if($targetSubmissionQuery["filename"] != "" && file_exists("./collaborationRequestFiles/" . $targetSubmissionQuery["filename"])) {
                            if(!unlink("./collaborationRequestFiles/" . $targetSubmissionQuery["filename"])) { // Remove each request submission file.
                                $allDeleted = 0; // File deletion failed.
                            }  
                        }
                        

                        if  ($allDeleted) { // If all files were deleted, remove submission from the database.
                            try {                             
                                $stmt = $db->prepare("DELETE FROM `collaboration_request_files` WHERE `collaboration_id` = :collaborationid AND `collaboration_requests_id` = :requestid AND `id` = :submissionid");
                                $stmt->bindParam('collaborationid',  $collaborationQuery["id"]);
                                $stmt->bindParam('requestid',  $targetRequestQuery["id"]);
                                $stmt->bindParam('submissionid',  $targetSubmissionQuery["id"]);
                                $stmt->execute();

                                header("Location: "."./collaboration/".$collaborationQuery["id"]."/".$collaborationQuery["collaboration_key"]);
                            } catch (Exception $e) {
                                echo 'Could not delete the post, error: ',  $e->getMessage(), "\n";
                            }
                        }
                        
                    }
                    else {
                        echo "Error: You do not have access to this resource.";
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