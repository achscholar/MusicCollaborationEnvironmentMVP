<!DOCTYPE html>
<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if(isset($_POST['deleteSongFormButton']) && isset($_POST['allCheckboxesSong']) && isset($_POST['allCheckboxesSongKeys'])) {

    require('./includes/db.php');
    require("functions.php");

    // Security check
    require("userDetailsChangedCheck.php");
    require("retrieveUserAccessLevel.php");
    
    $toDeleteSongIds = $_POST['allCheckboxesSong'];
    $toDeleteSongKeys = $_POST['allCheckboxesSongKeys'];

      if (isset($_SESSION['member_id'])) {
        $access_level = checkAccesstoResource($_SESSION['parent_group_id'], $db); 

        if ($access_level >= 5) {
          $index = 0;
          
          $redirectToId = "";

          foreach ($toDeleteSongIds as $toDeleteId) {
              $toDeleteKey = $toDeleteSongKeys[$index];
              
              $stmt = $db->prepare("SELECT * FROM `songs` WHERE `id` = :requestedsong AND `songKey` = :requestedsongkey");
              $stmt->bindParam('requestedsong', $toDeleteId);
              $stmt->bindParam('requestedsongkey', $toDeleteKey);
              $stmt->execute();
              $songLookup = $stmt->fetch();

              if ($songLookup) {
 
                // End release deletion early if a foreign group is detected.
                $access_level = checkAccesstoResource($songLookup["groupId"], $db); // Retrieve proper access level for current user.
                if (!isset($_SESSION['member_id']) || $access_level < 5) { 
                    print('Error: You do not have access to this resource.');
                    die();
                }

                $redirectToId = $songLookup["groupId"];

              }
              else
              {
                print("ERROR: Release does not exist.");
              }
              $index++;
          }

          $allSongIdsDelimitedUnclean = implode(",", $toDeleteSongIds);

          if (isset($allSongIdsDelimitedUnclean) && $allSongIdsDelimitedUnclean != "") {
            $allSongsToDeleteQuery = $db->query("SELECT `id` FROM `songs` WHERE `id` IN ($allSongIdsDelimitedUnclean)")->fetchAll();
            $deleteSongIds = array_column($allSongsToDeleteQuery, "id");
            $allSongIdsDelimited = implode(",", $deleteSongIds);
          }

          if (isset($allSongIdsDelimited) && $allSongIdsDelimited != "") {
            // Delete song pictures.
            $allSongPicturesToDeleteQuery = $db->query("SELECT `pictureFileName` FROM `pictures` WHERE `songId` IN ($allSongIdsDelimited)")->fetchAll();
            foreach($allSongPicturesToDeleteQuery as $songPictureFilename) {
              deleteFile("./pictures/", $songPictureFilename["pictureFileName"]);
              deleteFile("./pictures/thumbnail_", $songPictureFilename["pictureFileName"]);
            }

            // Delete song files.
            $allSongFilesToDeleteQuery = $db->query("SELECT `musicFileName` FROM `music` WHERE `songId` IN ($allSongIdsDelimited)")->fetchAll();
            foreach($allSongFilesToDeleteQuery as $songFileFilename) {
              deleteFile("./music/", $songFileFilename["musicFileName"]);
            }

            // Retrieve list of collaborations to delete.
            $allCollaborationsToDeleteQuery = $db->query("SELECT `id` FROM `collaborations` WHERE `songId` IN ($allSongIdsDelimited)")->fetchAll();
            $deleteCollaborationIds = array_column($allCollaborationsToDeleteQuery, "id");
            $allCollaborationIdsDelimited = implode(",", $deleteCollaborationIds);
          }

          if (isset($allCollaborationIdsDelimited) && $allCollaborationIdsDelimited != "") {
            // Delete collaboration posts files.
            $allCollaborationPostsToDeleteQuery = $db->query("SELECT `filename` FROM `collaboration_posts` WHERE `collaboration_id` IN ($allCollaborationIdsDelimited)")->fetchAll();
            foreach ($allCollaborationPostsToDeleteQuery as $postFileFilename) {
              deleteFile("./collaborationPostFiles/", $postFileFilename["filename"]);
            }

            // Delete collaboration requests files.
            $allCollaborationRequestsToDeleteQuery = $db->query("SELECT `filename` FROM `collaboration_request_files` WHERE `collaboration_id` IN ($allCollaborationIdsDelimited)")->fetchAll();
            foreach ($allCollaborationRequestsToDeleteQuery as $requestFileFilename) {
              deleteFile("./collaborationRequestFiles/", $requestFileFilename["filename"]);
            }
          }

          // Delete all target groups from the database, cascading/deleting the rows for the rest of the affected tables.
          if (isset($allSongIdsDelimited) && $allSongIdsDelimited != "") {
            $db->query("DELETE FROM `songs` WHERE `id` IN ($allSongIdsDelimited)");
          }

          if ($redirectToId) {
            $stmt = $db->prepare("SELECT * FROM `groups` WHERE `id` = :requestedgroup");
            $stmt->bindParam('requestedgroup', $redirectToId);
            $stmt->execute();
            $redirectGroupQuery = $stmt->fetch();

            header("Location: "."./group/".$redirectGroupQuery["id"]."/".$redirectGroupQuery["groupKey"]);
          }

        }
        else
        {
          echo "ERROR: You do not have access to this resource.";
        }

      }
      else
      {
        echo "ERROR: You do not have access to this resource.";
      }

  }
  else
  {
    echo "POST Error: The Required Information Was Not Received.";
  }
}
else{
  header("Location: "."./groups.php");
}

?>