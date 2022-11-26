
<?php
print("<p>Sign in page will be implemented here.</p>");

if (isset($_POST['signin_memberid'])) {

require("signout.php"); // sign out previous user
// Force SSL
// if($_SERVER["HTTPS"] != "on") {
//     die('Must login via HTTPS');
//   }
  
  // Load the current sessionID
  session_start();

  
  // Validate the login information, being sure to escape the input
//   ...
//   if (! $valid) {
//     die('Invalid login');
//   }
  
  // Start the new session ID to prevent session fixation
  session_regenerate_id();
  
  // Log them in
  
  require('includes/db.php');

  $stmt = $db->prepare("SELECT * FROM `members` WHERE `member_id` = :memberid");
  $stmt->bindParam('memberid', $_POST['signin_memberid']);
  $stmt->execute();
  $groupQuery = $stmt->fetch();

  $_SESSION['member_id'] = $groupQuery['member_id'];
  $_SESSION['access_level'] = $groupQuery['access_level'];
  $_SESSION['parent_group_id'] = $groupQuery['parent_group_id'];
  $_SESSION['updated_timestamp'] = $groupQuery['updated'];
  $_SESSION['firstname'] = $groupQuery['firstname'];
  $_SESSION['lastname'] = $groupQuery['lastname'];
  $_SESSION['user_email'] = $groupQuery['user_email'];

  // Retrieve parent group key (for redirects)
  // $stmt = $db->prepare("SELECT * FROM `groups` WHERE `id` = :parentgroupid");
  // $stmt->bindParam('parentgroupid', $groupQuery['parent_group_id']);
  // $stmt->execute();
  // $parentKeyQuery = $stmt->fetch();
  // session_write_close();
  // if ($parentKeyQuery) { // Redirect to user parent group if it exists.
  //   header("Location: ./group/".$groupQuery['parent_group_id']."/".$parentKeyQuery["groupKey"]);
  // }
  session_write_close();

  require("redirectCheck.php"); // Redirects to specified location if set.

}

?>