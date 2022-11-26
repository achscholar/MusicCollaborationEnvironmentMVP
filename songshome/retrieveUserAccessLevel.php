<?php


//>=10 Sitewide admin
//>=5 Group Leaders
//>=1 Members
require('includes/db.php');

function checkAccesstoResource($anyGroupId, &$db)
{   
    if (isset($_SESSION['member_id'])){
        $member_id = $_SESSION['member_id'];
        $parent_group = $_SESSION['parent_group_id'];
        $access_level = $_SESSION['access_level'];


        //Retrieves the details for the requested group.
        $stmt = $db->prepare("SELECT * FROM `groups` WHERE `id` = :anygroupid");
        $stmt->bindParam('anygroupid', $anyGroupId);
        $stmt->execute();
        $groupQuery = $stmt->fetch();

        // var_dump($groupQuery);
        if (!$groupQuery) {
            $access_level = 0;

            if ($_SESSION['access_level'] >= 10 ) { // ALLOWS ACCESS FOR SITEWIDE ADMINS TO ALL RESOURCES.
                $access_level = $_SESSION['access_level'];
            }
        }
        else { // Find the parent group id of the requested group and compare it to the signed in user.
            if ($groupQuery["locationIndex"] == NULL) { // This is the parent group!
                if ($parent_group != $groupQuery["id"]) {
                    $access_level = 0; // LIMIT ACCESS TO RESOURCE, THE USER IS ACCESSING A FOREIGN GROUP
                }
            }
            else  { // This is the subgroup, retrieve the parent group.
                $locationIdList = explode(",", $groupQuery['locationIndex']);
                if ($parent_group != $locationIdList[0]) {
                    $access_level = 0; // LIMIT ACCESS TO RESOURCE, THE USER IS ACCESSING A FOREIGN GROUP
                }
            }

            if ($_SESSION['access_level'] >= 10 ) { // ALLOWS ACCESS FOR SITEWIDE ADMINS TO ALL RESOURCES.
                $access_level = $_SESSION['access_level'];
            }
        }


    }
    else
    {
        $access_level = 0;
    }

    return $access_level;
}

function retrieveParentGroupId($anyGroupId, &$db)
{   
    //Retrieves the details for the requested group.
    $stmt = $db->prepare("SELECT * FROM `groups` WHERE `id` = :anygroupid");
    $stmt->bindParam('anygroupid', $anyGroupId);
    $stmt->execute();
    $groupQuery = $stmt->fetch();

    // var_dump($groupQuery);
    if (!$groupQuery) {
        return NULL;
    }
    else { // Find the parent group id of the requested group.
        if ($groupQuery["locationIndex"] == NULL) { // This is the parent group!
            return  $groupQuery["id"];
        }
        else  { // This is the subgroup, retrieve the parent group.
            $locationIdList = explode(",", $groupQuery['locationIndex']);
            return $locationIdList[0];
        }
    }

    return NULL;
}

function retrieveParentGroupKey($anyGroupId, &$db)
{   
    //Retrieves the details for the requested group.
    $stmt = $db->prepare("SELECT * FROM `groups` WHERE `id` = :anygroupid");
    $stmt->bindParam('anygroupid', $anyGroupId);
    $stmt->execute();
    $groupQuery = $stmt->fetch();

    // var_dump($groupQuery);
    if (!$groupQuery) {
        return NULL;
    }
    else { // Find the parent group id of the requested group.
        if ($groupQuery["locationIndex"] == NULL) { // This is the parent group!
            return  $groupQuery["groupKey"];
        }
        else  { // This is the subgroup, retrieve the parent group.
            $locationIdList = explode(",", $groupQuery['locationIndex']);
            // Retrieve the key of the parent group.
            $stmt = $db->prepare("SELECT * FROM `groups` WHERE `id` = :parentgroupid");
            $stmt->bindParam('parentgroupid', $locationIdList[0]);
            $stmt->execute();
            $parentGroupQuery = $stmt->fetch();
            
            return $parentGroupQuery["groupKey"];
        }
    }

    return NULL;
}

?>