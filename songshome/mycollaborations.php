<!DOCTYPE html>
<html lang="en">
<head>
<?php include('./includes/defaultheadtags.html');?>
<title>Music Collab. Environ. - View My Collaborations</title>
</head>
<body>
    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {

            require('./includes/db.php');
            require("functions.php");
    
            // Security Component
            require("userDetailsChangedCheck.php"); //START SESSION
            require("retrieveUserAccessLevel.php");
            
            require("navmenustart.php");
            include('temp_signinbutton.php'); // REMOVE LATER
         
            if (isset($_SESSION['member_id']) && isset($_SESSION['parent_group_id']) ) {
                    $access_level = checkAccesstoResource($_SESSION['parent_group_id'], $db); // Retrieve proper access level for current user.

                    // Retrieve all groups part of the same parent group.
                    $locationIndexConcat = $_SESSION['parent_group_id'].",%";
                    $stmt = $db->prepare("SELECT `id` FROM `groups` WHERE `locationIndex` LIKE :signedinmembergroupid OR id = :memberparentgroupid");
                    $stmt->bindParam('signedinmembergroupid', $locationIndexConcat);
                    $stmt->bindParam('memberparentgroupid', $_SESSION['parent_group_id']);
                    $stmt->execute();
                    $allGroupsQuery = $stmt->fetchAll();

                    $allGroupIds = array_column($allGroupsQuery, "id");
                    $allGroupIdsDelimited = implode(",", $allGroupIds);
                    
                    $allUserGroupSongs = $db->query("SELECT `id` FROM `songs` WHERE `groupId` IN ($allGroupIdsDelimited)")->fetchAll();

                    $allSongIds = array_column($allUserGroupSongs, "id");
                    $allSongIdsDelimited = implode(",", $allSongIds);

                    $collaborationsLeaderQuery = array();
                    $collaborationsMemberQuery = array();

                    print("<h2 class=\"title\" style=\"border: calc(.5px + .2vw) solid #000000; border-radius:30px;\">List of All Collaborations<h2>");
                    if ($access_level >= 5) { // Retrieve all collaborations for group leaders.

                        $collaborationsLeaderQuery = $db->query("SELECT id, songId, collaboration_title, collaboration_key,  firstname, lastname FROM collaborations LEFT JOIN members ON collaborations.created_by_member_id = members.member_id WHERE songId IN ($allSongIdsDelimited) ORDER BY songId AND collaboration_title")->fetchAll();

                        if ($collaborationsLeaderQuery) {
                             
                            $stmt = $db->prepare("SELECT * FROM `songs` WHERE `id` = :songid");
                            $stmt->bindParam('songid',  $collaborationsLeaderQuery[0]["songId"]);
                            $stmt->execute();
                            $songQuery = $stmt->fetch();
                            print("<h2 class=\"title responsiveContainer\"><span class=\"linkRedirect\"><a href=\"/songshome/view/".$songQuery["id"]."/".$songQuery["songKey"]."\">Existing Collaborations for ".$songQuery["title"]." Release</a></span></h2>");
                          
                            $prevParentSongId = $songQuery["id"];
                            print("<div class=\"responsiveContainer listContainer\">");

                            foreach($collaborationsLeaderQuery as $collaboration) {

                                if ($prevParentSongId != $collaboration['songId']) {
                                    $prevParentSongId = $collaboration['songId'];

                                    $stmt = $db->prepare("SELECT * FROM `songs` WHERE `id` = :songid");
                                    $stmt->bindParam('songid',  $collaboration['songId']);
                                    $stmt->execute();
                                    $songQuery = $stmt->fetch();

                                    print("</div>");
                                    print("<h2 class=\"title responsiveContainer\"><span class=\"linkRedirect\"><a href=\"/songshome/view/".$songQuery["id"]."/".$songQuery["songKey"]."\">Existing Collaborations for ".$songQuery["title"]." Release</a></span></h2>");
                                    print("<div class=\"responsiveContainer listContainer\">");

                                    $prevParentSongId = $collaboration['songId'];
                                }
                                print("<div style=\"width:100%; \">
                                            <div class=\"button listItem\" onclick='location.href=\"/songshome/collaboration/".$collaboration["id"]."/".$collaboration["collaboration_key"]."\";'>
                                                <p style=\"width:60%;text-align:left;\">".$collaboration["collaboration_title"]."</p>
                                                <p style\"width:40%;text-align:right;\">Created by ".$collaboration["firstname"]." ".$collaboration["lastname"]."</p>
                                            </div>
                                        </div>");
                            }

                            print("</div>");
                        }

                    }
                    else if ($access_level >= 1) { // Retrieve personal collaborations for current member.

                        $stmt = $db->prepare("SELECT collaboration_participants.songId, collaboration_title, collaboration_id, collaboration_participants.collaboration_key FROM collaboration_participants JOIN collaborations ON collaboration_participants.collaboration_id = collaborations.id WHERE collaboration_participants.member_id = :memberid ORDER BY collaborations.songId AND collaboration_title");
                        $stmt->bindParam('memberid',  $_SESSION['member_id']);
                        $stmt->execute();
                        $collaborationsMemberQuery = $stmt->fetchAll();

                        if ($collaborationsMemberQuery) {
                            
                            $stmt = $db->prepare("SELECT `firstname`, `lastname` FROM `members` WHERE `member_id` = :memberid");
                            $stmt->bindParam('memberid',  $_SESSION['member_id']);
                            $stmt->execute();
                            $userFullnameQuery = $stmt->fetch();

                            $stmt = $db->prepare("SELECT * FROM `songs` WHERE `id` = :songid");
                            $stmt->bindParam('songid',  $collaborationsMemberQuery[0]["songId"]);
                            $stmt->execute();
                            $songQuery = $stmt->fetch();
                            print("<h2 class=\"title responsiveContainer\"><span class=\"linkRedirect\"><a href=\"/songshome/view/".$songQuery["id"]."/".$songQuery["songKey"]."\">My Collaborations for ".$songQuery["title"]." Release</a></span></h2>");
                          
                            $prevParentSongId = $songQuery["id"];
                            print("<div class=\"responsiveContainer listContainer\">");
                            
                            foreach($collaborationsMemberQuery as $collaboration) {

                                if ($prevParentSongId != $collaboration['songId']) {
                                    $prevParentSongId = $collaboration['songId'];

                                    $stmt = $db->prepare("SELECT * FROM `songs` WHERE `id` = :songid");
                                    $stmt->bindParam('songid',  $collaboration['songId']);
                                    $stmt->execute();
                                    $songQuery = $stmt->fetch();

                                    print("</div>");
                                    print("<h2 class=\"title responsiveContainer\"><span class=\"linkRedirect\"><a href=\"/songshome/view/".$songQuery["id"]."/".$songQuery["songKey"]."\">My Collaborations for ".$songQuery["title"]." Release</a></span></h2>");
                                    print("<div class=\"responsiveContainer listContainer\">");

                                    $prevParentSongId = $collaboration['songId'];
                                }
                                print("<div style=\"width:100%; \">
                                             <div class=\"button listItem\" onclick='location.href=\"/songshome/collaboration/".$collaboration["collaboration_id"]."/".$collaboration["collaboration_key"]."\";'>
                                                 <p style=\"width:60%;text-align:left;\">".$collaboration["collaboration_title"]."</p>
                                                 <p style\"width:40%;text-align:right;\">".$userFullnameQuery["firstname"]." ".$userFullnameQuery["lastname"]." Is Invited</p>
                                             </div>
                                         </div>");
                            }

                            print("</div>");
                        }
                    }
                    
                    require("navmenuend.php");
           
            }
            else {
                echo "<br>Error: You do not have access to this resource.";
            }
    }
    ?>
</body>
<script>
//Makes sure group name field is not empty at submit
// Check if title is not empty on submit.
function checkIfEmpty(id, text) {
    var x = document.getElementById(id).value;
    if (x.length == "") {
        alert(text);
        event.preventDefault();
        return false;
    } 
    return true;
}


// Check input function for Android phones
var input_field = document.getElementById('newCollaborationTitleInput');
var bannedChar = "\\'<>";

input_field.addEventListener('newCollaborationTitleInput', function(e) {
    // e.data will be the 1:1 input you done
    var char = e.data; // In our example = "a"
    
    // Stop processing if "a" is pressed
    if (bannedChar.indexOf(char) >= 0){
        alert("Invalid character!");
        e.preventDefault();
        return false;
    }       

    if(document.getElementById("newCollaborationTitleInput").value.length >= 440){
        alert("The collaboration title is too long.");
        e.preventDefault();
        return false;
    }  
    
});


// Check input function for Windows
document.getElementById("newCollaborationTitleInput").onkeypress = function(e) {
    if(document.getElementById("newCollaborationTitleInput").value.length >= 440){
        alert("The collaboration title is too long.");
        return false;
    }
    var chr = String.fromCharCode(e.which);
 
    if (bannedChar.indexOf(chr) >= 0){
        alert("Invalid character!");
        return false;
        }       
        
}


// Show popup if paste is too long
document.getElementById("newCollaborationTitleInput").onpaste = function(e) {
    if(document.getElementById("newCollaborationTitleInput").value.length >= 440){
        alert("The collaboration title is too long.");
        return false;
    }   
      
    
}
</script>
</html>