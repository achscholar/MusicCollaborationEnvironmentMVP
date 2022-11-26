<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST["collaborationId"]) && isset($_POST["collaborationKey"]) && isset($_POST["removeMemberId"])) {
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

                    // Check if user is the creator of the collaboration or is a group leader or the user is trying to remove themselves.
                    if (($userInCollaborationQuery && ($collaborationQuery["created_by_member_id"] == $_SESSION['member_id'] || $_SESSION['member_id'] == $_POST["removeMemberId"]))  || $access_level >= 5 ) {

                        // Retrieve details of user who is being removed from the collaboration, ensuring both of the users have the same parent groups.
                        $stmt = $db->prepare("SELECT `parent_group_id`, `member_id`, `access_level` FROM `members` WHERE `member_id` = :memberid AND `parent_group_id` = :parentgroupid");
                        $stmt->bindParam('memberid', $_POST["removeMemberId"]);
                        $stmt->bindParam('parentgroupid', $_SESSION['parent_group_id']);
                        $stmt->execute();
                        $removeMemberQuery = $stmt->fetch();

                        if (($removeMemberQuery && $removeMemberQuery["access_level"] <= $access_level) || $access_level >= 10) { // Also ensure user access level is not less than the user being removed.

                            // Check if there is more than one collaboration participant.
                            $stmt = $db->prepare("SELECT * FROM `collaboration_participants` WHERE `songId` = :songid AND `collaboration_id` = :collaborationid");
                            $stmt->bindParam('songid',  $collaborationQuery["songId"]);
                            $stmt->bindParam('collaborationid',  $collaborationQuery["id"]);
                            $stmt->execute();
                            $participantsInCollaborationQuery = $stmt->fetchAll();

                            if($participantsInCollaborationQuery && sizeof($participantsInCollaborationQuery) > 1) { // There is more than one user left in collaboration. Allow user removal.
                                try {                             

                                    $stmt = $db->prepare("DELETE FROM `collaboration_participants` WHERE `member_id` = :memberid AND `songId` = :songid AND `collaboration_id` = :collaborationid");
                                    $data = [
                                        'memberid' => $_POST["removeMemberId"],                                   
                                        'songid' => $collaborationQuery["songId"],                               
                                        'collaborationid' => $collaborationQuery['id'],
                                    ];
                                    $stmt->execute($data);

                                   
                                    header("Location: "."./collaboration/".$collaborationQuery["id"]."/".$collaborationQuery["collaboration_key"]);
                                    
                                    
                                } catch (Exception $e) {
                                    echo '<br>Caught exception: ',  $e->getMessage(), "\n";
                                }
                            }
                            
                            header("Location: "."./collaboration/".$collaborationQuery["id"]."/".$collaborationQuery["collaboration_key"]);
                        }
                        else {
                            echo "You cannot remove this user from the collaboration.";
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