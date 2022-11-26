<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST["collaborationId"]) && isset($_POST["collaborationKey"]) && isset($_POST["addMembersIds"])) {
        require('./includes/db.php');
        require("functions.php");

        if ($_POST["addMembersIds"] == "NOONE") { // Misclick redirect back.
            header("Location: "."./collaboration/".$_POST["collaborationId"]."/".$_POST["collaborationKey"]);
        }

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

                        if ($userInCollaborationQuery || $access_level >= 5) { // User is in collaboration or is a Group Leader

                            if ($access_level >= 5 && ($_POST["addMembersIds"] == "ONLYMEMBERS" || $_POST["addMembersIds"] == "ONLYGROUPLEADERS")) { // Adding mutiple users to collaboration.
                                $addLevel = 0;
                                if ($_POST["addMembersIds"] == "ONLYMEMBERS") {
                                    $addLevel = 1;
                                }
                                else if ($_POST["addMembersIds"] == "ONLYGROUPLEADERS") {
                                    $addLevel = 5;
                                }

                                if ($addLevel != 0) { // Adding all Members or all Group Leaders
                                    // Retrieve all users to add to the collaboration.
                                    $stmt = $db->prepare("SELECT `member_id` FROM `members` WHERE `parent_group_id` = :membergroupid AND `access_level` = :addlevel");
                                    $stmt->bindParam('membergroupid', $_SESSION['parent_group_id']);
                                    $stmt->bindParam('addlevel', $addLevel);
                                    $stmt->execute();
                                    $membersToAddQuery = $stmt->fetchAll();

                                    foreach($membersToAddQuery as $targetMember) {
                                        try {                             
                                            $stmt = $db->prepare("INSERT INTO collaboration_participants (songId, collaboration_id, member_id, collaboration_key) VALUES (:songid, :collaborationid, :memberid, :collaborationkey)");
                                            $data = [
                                                'songid' => $songQuery["id"],                                   
                                                'collaborationid' => $collaborationQuery["id"],                               
                                                'memberid' => $targetMember['member_id'],
                                                'collaborationkey' => $collaborationQuery["collaboration_key"],
                                            ];
                                            $stmt->execute($data);
                                        } catch (Exception $e) {
                                            echo 'Caught exception: ',  $e->getMessage(), "\n";
                                        }
                                    }

                                    header("Location: "."./collaboration/".$collaborationQuery["id"]."/".$collaborationQuery["collaboration_key"]);
                                }
                            }
                           else if ($_POST["addMembersIds"] != "ONLYMEMBERS" && $_POST["addMembersIds"] != "ONLYGROUPLEADERS") { // Adding a single member.

                                // Retrieve details of user who is being invited comparing to logged in user.
                                $stmt = $db->prepare("SELECT `parent_group_id`, `member_id`, `access_level` FROM `members` WHERE `member_id` = :memberid AND `parent_group_id` = :parentgroupid");
                                $stmt->bindParam('memberid', $_POST["addMembersIds"]);
                                $stmt->bindParam('parentgroupid', $_SESSION['parent_group_id']);
                                $stmt->execute();
                                $addMemberQuery = $stmt->fetch();

                                if ($addMemberQuery) { // Users have same parent groups, add target user to collaboration.
                                    try {                             
                                        $stmt = $db->prepare("INSERT INTO collaboration_participants (songId, collaboration_id, member_id, collaboration_key) VALUES (:songid, :collaborationid, :memberid, :collaborationkey)");
                                        $data = [
                                            'songid' => $songQuery["id"],                                   
                                            'collaborationid' => $collaborationQuery["id"],                               
                                            'memberid' => $addMemberQuery['member_id'],
                                            'collaborationkey' => $collaborationQuery["collaboration_key"],
                                        ];
                                        $stmt->execute($data);
                                    } catch (Exception $e) {
                                        echo '<br>Caught exception: ',  $e->getMessage(), "\n";
                                    }

                                    header("Location: "."./collaboration/".$collaborationQuery["id"]."/".$collaborationQuery["collaboration_key"]);
                                }
                                else {
                                    echo "Error: Invalid User or you cannot add this user.";
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