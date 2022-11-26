<?php

if(isset($_REQUEST["fileId"]) && isset($_REQUEST["fileKey"])){
    require('./includes/db.php');

    require("userDetailsChangedCheck.php");
    if ($access_level >= 1) {
          
        // Get parameters
        $fileId = urldecode($_REQUEST["fileId"]);
        $fileKey = urldecode($_REQUEST["fileKey"]);
        $stmt = $db->prepare("SELECT * FROM `music` WHERE `musicId` = :fileid AND `musicKey` = :filekey");
        $stmt->bindParam('fileid', $fileId);
        $stmt->bindParam('filekey', $fileKey);
        $stmt->execute();
        $musicQuery = $stmt->fetch();
        if(sizeof($musicQuery) != 0) {
            require("retrieveUserAccessLevel.php");
            $stmt = $db->prepare("SELECT * FROM `songs` WHERE `id` = :targetsongid");
            $stmt->bindParam('targetsongid', $musicQuery["songId"]);
            $stmt->execute();
            $targetSongQuery = $stmt->fetch();

            $access_level = checkAccesstoResource($targetSongQuery["groupId"], $db); // Retrieve proper access level for current user.
            if (isset($_SESSION['member_id']) && $access_level >= 5) { // Limit file download to group leaders.
            
                    $musicName = $musicQuery['musicFileName'];
                    $filepath = "./music/" . $musicName;
                
                    // Process download
                    if(file_exists($filepath)) {
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
}
?>