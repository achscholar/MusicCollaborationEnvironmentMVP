<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST["collaborationId"]) && isset($_POST["collaborationKey"])) {
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

                    // Check if user is the creator of the collaboration or is a group leader.
                    if (($userInCollaborationQuery && $collaborationQuery["created_by_member_id"] == $_SESSION['member_id']) || $access_level >= 5 ) {
                        // Delete all collaboration files.

                        // Retrieve collaboration request file submissions.
                        $stmt = $db->prepare("SELECT * FROM `collaboration_request_files` WHERE `collaboration_id` = :collaborationid");
                        $stmt->bindParam('collaborationid',  $collaborationQuery["id"]);
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

                        // Retrieve collaboration post files.
                        $stmt = $db->prepare("SELECT * FROM `collaboration_posts` WHERE `collaboration_id` = :collaborationid");
                        $stmt->bindParam('collaborationid',  $collaborationQuery["id"]);
                        $stmt->execute();
                        $allPostsQuery = $stmt->fetchAll();

                        foreach ($allPostsQuery as $post) {
                            if($post["filename"] != "" && file_exists("./collaborationPostFiles/" . $post["filename"])) {
                                if(!unlink("./collaborationPostFiles/" . $post["filename"])) { // Remove each post file.
                                    $allDeleted = 0; // File deletion failed.
                                }  
                            }
                        }

                        if  ($allDeleted) { // If all files were deleted, remove collaboration from the database.
                            try {                             
                                $stmt = $db->prepare("DELETE FROM `collaborations` WHERE `id` = :collaborationid AND `collaboration_key` = :collaborationkey");
                                $stmt->bindParam('collaborationid',  $collaborationQuery["id"]);
                                $stmt->bindParam('collaborationkey',  $collaborationQuery["collaboration_key"]);
                                $stmt->execute();

                                header("Location: "."./viewcollaborations/".$songQuery["id"]."/".$songQuery["songKey"]);
                            } catch (Exception $e) {
                                echo 'Could not delete the collaboration, error: ',  $e->getMessage(), "\n";
                            }
                        }

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