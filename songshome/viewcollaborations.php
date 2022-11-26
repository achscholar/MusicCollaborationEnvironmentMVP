<!DOCTYPE html>
<html lang="en">
<head>
<?php include('./includes/defaultheadtags.html');?>
<title>Music Collab. Environ. - View My Collaborations</title>
</head>
<body>
    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
       
      
        
        if(isset($_GET['songid']) && isset($_GET['songKeySubmit'])) {

            require('./includes/db.php');
            require("functions.php");
    
            // Security Component
            require("userDetailsChangedCheck.php"); //START SESSION
            require("retrieveUserAccessLevel.php");
            
            require("navmenustart.php");
            include('temp_signinbutton.php'); // REMOVE LATER

            //SONG KEY CHECK
            $songIdSubmit = $_GET["songid"];
            $songKeySubmit = $_GET["songKeySubmit"];
            $stmt = $db->prepare("SELECT * FROM `songs` WHERE `id` = :songid AND `songKey` = :songkeysubmit");
            $stmt->bindParam('songid',  $songIdSubmit);
            $stmt->bindParam('songkeysubmit',  $songKeySubmit);
            $stmt->execute();
            $songQuery = $stmt->fetch();
         
            if ($songQuery) {
                $access_level = checkAccesstoResource($songQuery["groupId"], $db); // Retrieve proper access level for current user.

                if (isset($_SESSION['member_id']) && $access_level >= 1) { // User is from the parent group.

                    print("<h2 class=\"title responsiveContainer\"><span class=\"linkRedirect\"><a href=\"/songshome/view/".$songQuery["id"]."/".$songQuery["songKey"]."\">My Collaborations for ".$songQuery["title"]."</a></span></h2>");

                    $collaborationsLeaderQuery = array();
                    $collaborationsMemberQuery = array();
                    if ($access_level >= 5) { // Retrieve all collaborations for group leaders.

                        $stmt = $db->prepare("SELECT id, collaboration_title, collaboration_key,  firstname, lastname FROM collaborations LEFT JOIN members ON collaborations.created_by_member_id = members.member_id WHERE songId = :songid ORDER BY collaboration_title");
                        $stmt->bindParam('songid',  $songQuery["id"]);
                        $stmt->execute();
                        $collaborationsLeaderQuery = $stmt->fetchAll();

                        if ($collaborationsLeaderQuery) {
                           

                            print("<div class=\"responsiveContainer listContainer\">");
                            
                            foreach($collaborationsLeaderQuery as $collaboration) {

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
                        $stmt = $db->prepare("SELECT collaboration_title, collaboration_id, collaboration_participants.collaboration_key FROM collaboration_participants JOIN collaborations ON collaboration_participants.collaboration_id = collaborations.id WHERE collaboration_participants.songId = :songid AND collaboration_participants.member_id = :memberid ORDER BY collaboration_title");
                        $stmt->bindParam('memberid',  $_SESSION['member_id']);
                        $stmt->bindParam('songid',  $songQuery["id"]);
                        $stmt->execute();
                        $collaborationsMemberQuery = $stmt->fetchAll();

                        if ($collaborationsMemberQuery) {
                            
                            $stmt = $db->prepare("SELECT `firstname`, `lastname` FROM `members` WHERE `member_id` = :memberid");
                            $stmt->bindParam('memberid',  $_SESSION['member_id']);
                            $stmt->execute();
                            $userFullnameQuery = $stmt->fetch();

                            print("<div class=\"responsiveContainer listContainer\">");
                            
                            foreach($collaborationsMemberQuery as $collaboration) {

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

                    print("
                    <div class=\"responsiveContainer listItem\" style=\"margin-top: 30px;padding-bottom: 100px;margin-left:auto;margin-right:auto;\">
                        <input type=\"hidden\" name=\"songId\" value=\"".$songQuery["id"]."\" form=\"addCollaborationForm\">
                        <input type=\"hidden\" name=\"songKey\" value=\"".$songQuery["songKey"]."\" form=\"addCollaborationForm\">
                        <input placeholder=\"New Collaboration Title\" name=\"newCollaborationTitle\"  id=\"newCollaborationTitleInput\" class=\"songTitleUpload\" maxlength=\"440\" style=\"width:70%;font-size:25px;text-align:left;padding-left:20px;\" form=\"addCollaborationForm\" type=\"text\">
                        <input style=\"background-color: #4fbd49; width:28%;white-space: normal;overflow-wrap: break-word;\" class=\"button\" id=\"newCollabSubmit\" value=\"Add New Collaboration\" form=\"addCollaborationForm\" type=\"submit\">
                            
                    </div>
                    <form id=\"addCollaborationForm\" onsubmit=\"if(checkIfEmpty('newCollaborationTitleInput', 'The collaboration title cannot be empty.')) { document.getElementById('newCollabSubmit').style.display = 'none';}\" autocomplete=\"off\" target=\"_self\" action=\"/songshome/addcollaboration.php\" method=\"post\"></form>");
                    
                    require("navmenuend.php");
                    
                }
                else {
                    echo "<br>Error: You do not have access to this resource.";
                }
            }
            else {
                echo "Error: Could not retrieve data.";
            }
           
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