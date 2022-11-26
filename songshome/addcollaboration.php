<?php
var_dump($_POST);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST["songId"]) && isset($_POST["songKey"]) && isset($_POST["newCollaborationTitle"])) {
        require('./includes/db.php');
        require("functions.php");

        // Security Component
        require("userDetailsChangedCheck.php"); //START SESSION
        require("retrieveUserAccessLevel.php");

        //SONG KEY CHECK
        $songIdSubmit = $_POST["songId"];
        $songKeySubmit = $_POST["songKey"];

        $stmt = $db->prepare("SELECT * FROM `songs` WHERE `id` = :songid AND `songKey` = :songkeysubmit");
        $stmt->bindParam('songid',  $songIdSubmit);
        $stmt->bindParam('songkeysubmit',  $songKeySubmit);
        $stmt->execute();
        $songQuery = $stmt->fetch();
        
        if ($songQuery) {
            $access_level = checkAccesstoResource($songQuery["groupId"], $db); // Retrieve proper access level for current user.

            if (isset($_SESSION['member_id']) && $access_level >= 1 && $access_level <= 5) { // User is from the parent group.

                // Purify user input against HTML Injection
                require_once './htmlpurifier/library/HTMLPurifier.auto.php';       
                $config = HTMLPurifier_Config::createDefault();
                $purifier = new HTMLPurifier($config);
            
                $newCollaborationTitle = $_POST["newCollaborationTitle"];
                $newCollaborationTitle = $purifier->purify($newCollaborationTitle);

                $addCollaborationOk = 1;

                // Reject on banned characters in title.
                require("bannedChars.php");
                $collaborationTitleCheck = strpos_multi($bannedArray, $newCollaborationTitle);
                if ($collaborationTitleCheck == "0") {
                    $addCollaborationOk = 0;
                }
    
                // Ensure proper length of title.
                if (strlen($newCollaborationTitle) > 441 || strlen($newCollaborationTitle) == 0){
                    $addCollaborationOk = 0;                
                }

                // Check how many collaborations a user has created.
                $stmt = $db->prepare("SELECT * FROM `collaborations` WHERE `songId` = :songid AND `created_by_member_id` = :createdbymemberid");
                $stmt->bindParam('songid',  $songQuery['id']);
                $stmt->bindParam('createdbymemberid', $_SESSION['member_id']);
                $stmt->execute();
                $checkNumberOfCollaborationsQuery = $stmt->fetchAll();

                if ($checkNumberOfCollaborationsQuery) {
                    if(sizeof($checkNumberOfCollaborationsQuery) > 20 && $access_level <= 1) { // Member has over 20 collaborations, reject.
                        $addCollaborationOk = 0; 
                    }
                    else if (sizeof($checkNumberOfCollaborationsQuery) > 100 && $access_level <= 5) { // Group leader has over 100 collaborations, reject.
                        $addCollaborationOk = 0;
                    }
                }

                // Add collaboration
                if ($addCollaborationOk) {

                    $collaborationKey = getRandKey(12);

                    $stmt = $db->prepare("INSERT INTO collaborations (songId, collaboration_title, created_by_member_id, collaboration_key) VALUES (:songid, :collaborationtitle, :createdbymemberid, :collaborationkey)");
                    $data = [
                        'songid' => $songQuery["id"],                                   
                        'collaborationtitle' => $newCollaborationTitle,                               
                        'createdbymemberid' => $_SESSION['member_id'],
                        'collaborationkey' => $collaborationKey,
                    ];
                    $stmt->execute($data);

                    $collaborationId = $db->lastInsertId();

                    $stmt = $db->prepare("SELECT * FROM `collaborations` WHERE `id` = :collaborationid AND `created_by_member_id` = :memberid");
                    $stmt->bindParam('collaborationid',  $collaborationId);
                    $stmt->bindParam('memberid', $_SESSION['member_id']);
                    $stmt->execute();
                    $checkAddedCollaboarionQuery = $stmt->fetch();

                    if ($checkAddedCollaboarionQuery) { // Ensure collaboration was added.
                        $stmt = $db->prepare("SELECT * FROM `collaboration_participants` WHERE `collaboration_id` = :collaborationid AND `member_id` = :memberid");
                        $stmt->bindParam('collaborationid',  $collaborationId);
                        $stmt->bindParam('memberid', $_SESSION['member_id']);
                        $stmt->execute();
                        $checkUserCollaboarionQuery = $stmt->fetch();

                        if(!$checkUserCollaboarionQuery) { // Ensure user is not part of the collaboration, then add the user as a collaboration participant.
                            $stmt = $db->prepare("INSERT INTO collaboration_participants (songId, collaboration_id, member_id, collaboration_key) VALUES (:songid, :collaborationid, :memberid, :collaborationkey)");
                            $data = [
                                'songid' => $songQuery["id"],
                                'collaborationid' => $collaborationId,                                    
                                'memberid' => $_SESSION['member_id'],                               
                                'collaborationkey' => $collaborationKey,
                            ];
                            $stmt->execute($data);

                            // Ensure member got added to their new collaboration.
                            $stmt = $db->prepare("SELECT * FROM `collaboration_participants` WHERE `collaboration_id` = :collaborationid AND `member_id` = :memberid");
                            $stmt->bindParam('collaborationid',  $collaborationId);
                            $stmt->bindParam('memberid', $_SESSION['member_id']);
                            $stmt->execute();
                            $checkUserAddedCollaboarionQuery = $stmt->fetch();

                            if ($checkUserAddedCollaboarionQuery) {
                                header("Location: "."./collaboration/".$checkUserAddedCollaboarionQuery["collaboration_id"]."/".$checkUserAddedCollaboarionQuery["collaboration_key"]);
                            }
                            else {
                                echo "Error: Could not add you to the new collaboration.";
                            }
                        }
                    }
                }
                else {
                    echo "Error: There was an issue with your request or you have created too many collaborations.";
                }
                

            }
            else {
                echo "You cannot create a collaboration here.";
            }

        }
        else {
            echo "Error: Target music release does not exist.";
        }
    }
    else {
        echo " POST Error: The Required Information Was Not Received.";
    }
}
?>