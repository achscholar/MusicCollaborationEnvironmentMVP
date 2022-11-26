<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST["collaborationId"]) && isset($_POST["collaborationKey"]) && isset($_POST["removeRequestId"])) {
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

                    // Retrieve the target post.
                    $stmt = $db->prepare("SELECT * FROM `collaboration_requests` WHERE `collaboration_id` = :collaborationid AND `id` = :requestid");
                    $stmt->bindParam('collaborationid',  $collaborationQuery["id"]);
                    $stmt->bindParam('requestid',  $_POST["removeRequestId"]);
                    $stmt->execute();
                    $targetRequestQuery = $stmt->fetch();

                    $requestedMemberIds= array();
                    if ($targetRequestQuery["request_for"] != "" && $targetRequestQuery["request_for"] != "PUBLIC") {
                        $requestedMemberIds = explode(",", $targetRequestQuery["request_for"]);
                    }

                    // DO NOT CHANGE, unless you ensure both members have the same parent groups. Check if user is the creator of the collaboration or is a group leader or the user whos trying to remove their own post.
                    if ($userInCollaborationQuery && ($collaborationQuery["created_by_member_id"] == $_SESSION['member_id'] || $_SESSION['member_id'] == $targetRequestQuery["member_id"] || in_array($_SESSION["member_id"], $requestedMemberIds)  || $access_level >= 5)) {

                        // Retrieve collaboration request file submissions.
                        $stmt = $db->prepare("SELECT * FROM `collaboration_request_files` WHERE `collaboration_id` = :collaborationid AND `collaboration_requests_id` = :collaborationrequestsid");
                        $stmt->bindParam('collaborationid',  $collaborationQuery["id"]);
                        $stmt->bindParam('collaborationrequestsid', $targetRequestQuery ["id"]);
                        $stmt->execute();
                        $allRequestSubmissionsQuery = $stmt->fetchAll();

                        $allDeleted = 1;
                        foreach ($allRequestSubmissionsQuery as $requestSubmisssion) {
                            if($requestSubmisssion["filename"] != "" && file_exists("./collaborationRequestFiles/" . $requestSubmisssion["filename"])) {
                                if(!unlink("./collaborationRequestFiles/" . $requestSubmisssion["filename"])) { // Remove each request submission file.
                                    $allDeleted = 0; // File deletion failed.
                                }  
                            }
                        }

                        if  ($allDeleted) { // If all files were deleted, remove request from the database.
                            try {                             
                                $stmt = $db->prepare("DELETE FROM `collaboration_requests` WHERE `collaboration_id` = :collaborationid AND `id` = :requestid");
                                $stmt->bindParam('collaborationid',  $collaborationQuery["id"]);
                                $stmt->bindParam('requestid',  $targetRequestQuery["id"]);
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