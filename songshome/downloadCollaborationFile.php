<?php

if(isset($_REQUEST["fileId"]) && isset($_REQUEST["fileKey"]) && isset($_GET['collaboration_id']) && isset($_GET['collaboration_key'])){
    require('./includes/db.php');

    require("userDetailsChangedCheck.php");
    require("retrieveUserAccessLevel.php");
    if (isset($_SESSION['member_id'])) {
        $access_level = checkAccesstoResource($_SESSION['parent_group_id'], $db); // Retrieve proper access level for current user.
    }
    

     // Get parameters
     $targetPostId = urldecode($_REQUEST["fileId"]);
     $fileKey = urldecode($_REQUEST["fileKey"]);

    // Check if the collaboarion post exists.
    $stmt = $db->prepare("SELECT * FROM `collaboration_posts` WHERE `id` = :fileid AND `file_key` = :filekey");
    $stmt->bindParam('fileid', $targetPostId);
    $stmt->bindParam('filekey', $fileKey);
    $stmt->execute();
    $musicQuery = $stmt->fetch();

    if ($access_level >= 1) {
        
        if(sizeof($musicQuery) != 0 && isset($_SESSION['member_id'])) {

            // Check if user is part of the collaboration.
            $stmt = $db->prepare("SELECT * FROM `collaboration_participants` WHERE `member_id` = :memberid AND `collaboration_id` = :collaborationid AND `collaboration_key` = :collaborationkey");
            $stmt->bindParam('memberid',  $_SESSION['member_id']);
            $stmt->bindParam('collaborationid',  $_GET['collaboration_id']);
            $stmt->bindParam('collaborationkey',  $_GET['collaboration_key']);
            $stmt->execute();
            $collagorationParticipantQuery = $stmt->fetch();

            if ($collagorationParticipantQuery || $access_level >= 5) { // Limit file download to group leaders or participants of the collaboration.
            
                    $musicName = $musicQuery['filename'];
                    $filepath = "./collaborationPostFiles/" . $musicName;
                
                    // Process download
                    if($musicName != "" && file_exists($filepath)) {
                        header('Content-Description: File Transfer');
                        header('Content-Type: application/octet-stream');
                        header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate');
                        header('Pragma: public');
                        header('Content-Length: ' . filesize($filepath));
                        flush(); // Flush system output buffer
                        readfile($filepath);
                        exit;
                    }
                
            }
        }
        else{
            echo "File does not exist.";
        }
            
    }
    else if (isset($_GET['request_id'])) { // Visitor downloading a file.

        // Retrieve target collaboration file request.
        $stmt = $db->prepare("SELECT * FROM collaboration_requests WHERE `id` = :requestid AND `collaboration_id` = :collaborationid AND `target_post_id` = :targetpostid AND `request_for` = 'PUBLIC' AND `open` = 1");
        $stmt->bindParam('requestid', $_GET['request_id']);
        $stmt->bindParam('collaborationid', $_GET['collaboration_id']); 
        $stmt->bindParam('targetpostid',  $targetPostId);
        $stmt->execute();
        $targetRequestQuery = $stmt->fetch();

        if(sizeof($musicQuery) != 0 && $targetRequestQuery) {   
   
            $musicName = $musicQuery['filename'];
            $filepath = "./collaborationPostFiles/" . $musicName;
        
            // Process download
            if($musicName != "" && file_exists($filepath)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($filepath));
                flush(); // Flush system output buffer
                readfile($filepath);
                exit;
            }
                       
        }
    }
}
?>