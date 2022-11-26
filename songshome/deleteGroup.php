<!DOCTYPE html>
<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if(isset($_POST['deleteFormButton']) && isset($_POST['allCheckboxes']) && isset($_POST['allCheckboxesKeys']) ) 
  {
    require('./includes/db.php');
    require("functions.php");

    // Security check
    require("userDetailsChangedCheck.php");
    require("retrieveUserAccessLevel.php");
    
    $toDeleteGroupIds = $_POST['allCheckboxes'];
    $toDeleteGroupKeys = $_POST['allCheckboxesKeys'];

      if (isset($_SESSION['member_id'])) {
        $access_level = checkAccesstoResource($_SESSION['parent_group_id'], $db); 

        if ($access_level >= 5) {

          $index = 0;
          $allGroupsToDeleteIdList = array();

          $redirectToId = "";

          foreach ($toDeleteGroupIds as $toDeleteId) {
              $toDeleteKey = $toDeleteGroupKeys[$index];
              
              $stmt = $db->prepare("SELECT * FROM `groups` WHERE `id` = :requestedgroup AND `groupKey` = :requestedgroupkey");
              $stmt->bindParam('requestedgroup', $toDeleteId);
              $stmt->bindParam('requestedgroupkey', $toDeleteKey);
              $stmt->execute();
              $groupLookup = $stmt->fetch();

              if ($groupLookup) {
                // Security Check

                // End group deletion early if a foreign group is detected.
                if (isset($groupLookup["relationalGroupId"])) {
                  $checkAccessGroupId = $groupLookup["relationalGroupId"];
                }
                else {
                    $checkAccessGroupId = "NULL";
                }
                $access_level = checkAccesstoResource($checkAccessGroupId, $db); // Retrieve proper access level for current user.
                if (!isset($_SESSION['member_id']) || $access_level < 5) { 
                    print('Error: You do not have access to this resource.');
                    die();
                }

                $redirectIds = explode( ',', $groupLookup['locationIndex']);
                $redirectToId = $redirectIds[count($redirectIds)-2];

                $locationIndexConcat = $groupLookup['locationIndex'].$groupLookup['id'].",%";
                $stmt = $db->prepare("SELECT `id`, `groupPicture` FROM `groups` WHERE `locationIndex` LIKE :subgrouplocationindex OR id = :topdeletegroupid");
                $stmt->bindParam('subgrouplocationindex', $locationIndexConcat);
                $stmt->bindParam('topdeletegroupid', $groupLookup['id']);
                $stmt->execute();
                $deleteGroupsQuery = $stmt->fetchAll();

                if ($deleteGroupsQuery != NULL) {
                  $deleteGroupIds = array_column($deleteGroupsQuery, "id");
                  
                  foreach ($deleteGroupsQuery as $deleteGroup) { // Delete group images for returned groups.
                    deleteFile("./groupPictures/thumbnail_",  $deleteGroup["groupPicture"]);
                  }
                  
                }

                // Create a list of subgroups to delete.
                if ($deleteGroupIds != NULL) { $allGroupsToDeleteIdList = array_merge($allGroupsToDeleteIdList, $deleteGroupIds); }
              }
              else
              {
                print("ERROR: Group does not exist.");
              }
              $index++;
          }

          $allGroupIdsDelimited = implode(",", $allGroupsToDeleteIdList);

          if (isset($allGroupIdsDelimited) && $allGroupIdsDelimited != "") {
            $allSongsToDeleteQuery = $db->query("SELECT `id` FROM `songs` WHERE `groupId` IN ($allGroupIdsDelimited)")->fetchAll();
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
          if (isset($allGroupIdsDelimited) && $allGroupIdsDelimited != "") {
            $db->query("DELETE FROM `groups` WHERE `id` IN ($allGroupIdsDelimited)");
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