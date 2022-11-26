<?php



//>=10 Sitewide admin
//>=5 Group Leaders
//>=1 Members
require('includes/db.php');
session_start();

if (isset($_SESSION) && isset($_SESSION['member_id'])){
   

    $member_id = $_SESSION['member_id'];
    $parent_group = $_SESSION['parent_group_id'];
    $access_level = $_SESSION['access_level'];
    $updated_timestamp = $_SESSION['updated_timestamp'];

    // Retrieve current user settings.
    $stmt = $db->prepare("SELECT * FROM `members` WHERE `member_id` = :memberid AND `parent_group_id` = :parentgroupid AND `access_level` = :accesslevel AND `updated` = :updatedtimestamp");
    $stmt->bindParam('memberid', $member_id);
    $stmt->bindParam('parentgroupid', $parent_group);
    $stmt->bindParam('accesslevel', $access_level);
    $stmt->bindParam('updatedtimestamp', $updated_timestamp);
    $stmt->execute();
    $groupQuery = $stmt->fetch();

    // Initiates sign out if member settings have changed while member is signed in.
    if (!$groupQuery) {
        $access_level = 0;

        require("signout.php");
    }


}
else
{
    $access_level = 0;
}


?>