<!DOCTYPE html>
<html lang="en">
<head>
<?php include('./includes/defaultheadtags.html');?>
<title>Music Collab. Environ. - View My Collaborations</title>

<link rel="stylesheet" href="/songshome/musicPlayer/dist/css/green-audio-player.css">
<script src="/songshome/musicPlayer/dist/js/green-audio-player.js"></script>

</head>
<body>
    <?php
    
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    
        if(isset($_GET['collaboration_id']) && isset($_GET['collaboration_key'])) {

            require('./includes/db.php');
            require("functions.php");
            require('./includes/serverTimeoffset.php');

            // Security Component
            require("userDetailsChangedCheck.php"); //START SESSION
            require("retrieveUserAccessLevel.php");

            
            $databaseTimestamp = $db->query("SELECT CURRENT_TIMESTAMP;")->fetch();

            $collaborationIdSubmit = $_GET["collaboration_id"];
            $collaborationKeySubmit = $_GET["collaboration_key"];

            //COLLABORATION KEY CHECK
            $stmt = $db->prepare("SELECT * FROM `collaborations` WHERE `id` = :collaborationid AND `collaboration_key` = :collaborationkey");
            $stmt->bindParam('collaborationid',  $collaborationIdSubmit);
            $stmt->bindParam('collaborationkey',  $collaborationKeySubmit);
            $stmt->execute();
            $collaborationQuery = $stmt->fetch();

            if ($collaborationQuery) {

                // Retrieve Parent Group Id
                $stmt = $db->prepare("SELECT * FROM `songs` WHERE `id` = :songid");
                $stmt->bindParam('songid',  $collaborationQuery["songId"]);
                $stmt->execute();
                $songQuery = $stmt->fetch();

                $access_level = checkAccesstoResource($songQuery["groupId"], $db); // Retrieve proper access level for current user.

                if (isset($_SESSION['member_id']) && $access_level >= 1) { // User is from the parent group.

                    //MEMBER PARTICIPATION CHECK
                    $stmt = $db->prepare("SELECT * FROM `collaboration_participants` WHERE `member_id` = :memberid AND `collaboration_id` = :collaborationid AND `collaboration_key` = :collaborationkey");
                    $stmt->bindParam('memberid',  $_SESSION['member_id']);
                    $stmt->bindParam('collaborationid',  $collaborationIdSubmit);
                    $stmt->bindParam('collaborationkey',  $collaborationKeySubmit);
                    $stmt->execute();
                    $collagorationParticipantQuery = $stmt->fetch();

                    if (!($access_level >= 5) && !$collagorationParticipantQuery) { // Member is not part of this collaboration, treat them as a visitor.
                        $access_level = 0;
                    }
                    
                }

                // Navigation menu & logo setup.
                $parentGroupNavId = retrieveParentGroupId($songQuery["groupId"], $db);
                $parentGroupNavKey = retrieveParentGroupKey($songQuery["groupId"], $db);
                $logoBarLink = "/songshome/group/".$parentGroupNavId."/".$parentGroupNavKey;
                $navigationLinks[] = "<a class=\"navLinks\"  href=\"/songshome/group/".$parentGroupNavId."/".$parentGroupNavKey."\">Home</a>";
                $navigationLinks[] = "<a class=\"navLinks\"  href=\"/songshome/signin.php\">Sign In</a>";
                require("navmenustart.php");

                include('includes/scrollbuttons.php');

                if ($access_level >= 1) { // User is from the parent group and is part of the collaboration.
                        

                        include('temp_signinbutton.php'); // REMOVE LATER

                        print("
                            <br>
                            <div class=\"responsiveContainer centerContainer\">
                                <p class=\"boldNunito\"><span class=\"linkRedirect\"><a href=\"/songshome/viewcollaborations/".$songQuery["id"]."/".$songQuery["songKey"]."\">".$songQuery["title"]." Release Collaboration</a></span></p>
                            </div>
                        ");

                         // Check if user is in the target collaboration, used to display UI to upload close submissions, close submissions and request changes.
                         $stmt = $db->prepare("SELECT * FROM `collaboration_participants` WHERE `songId` = :songid AND `collaboration_id` = :collaborationid AND `member_id` = :memberid");
                         $stmt->bindParam('songid',  $collaborationQuery["songId"]);
                         $stmt->bindParam('collaborationid',  $collaborationQuery["id"]);
                         $stmt->bindParam('memberid',  $_SESSION['member_id']);
                         $stmt->execute();
                         $userInCollaborationQuery = $stmt->fetch();

                        // Get name of collaboration creator.
                        $stmt = $db->prepare("SELECT * FROM `members` WHERE `member_id` = :memberid");
                        $stmt->bindParam('memberid',  $collaborationQuery["created_by_member_id"]);
                        $stmt->execute();
                        $creatorQuery = $stmt->fetch();

                        $createdByMemberFullname = "";
                        if ($creatorQuery) {
                            $createdByMemberFullname = $creatorQuery["firstname"]." ".$creatorQuery["lastname"];
                        }

                        // Retrieve name of all collaborators and their details.
                        $stmt = $db->prepare("SELECT firstname, lastname, user_email, collaboration_participants.member_id FROM collaboration_participants JOIN members ON collaboration_participants.member_id = members.member_id WHERE collaboration_participants.collaboration_id = :collaborationid ORDER BY joined_on DESC");
                        $stmt->bindParam('collaborationid',  $collaborationQuery["id"]);
                        $stmt->execute();
                        $collaborationMembersQuery = $stmt->fetchAll();

                        // Retrieve name of all users and their details.
                        $stmt = $db->prepare("SELECT firstname, lastname, user_email, member_id, access_level FROM members WHERE parent_group_id = :parentgroupid ORDER BY firstname");
                        $stmt->bindParam('parentgroupid',  $_SESSION['parent_group_id']);
                        $stmt->execute();
                        $allMembersQuery = $stmt->fetchAll();

                        if ($creatorQuery["member_id"] == $_SESSION['member_id'] || $access_level >= 5) { // Invited users list for Group Leader or Member that created the collaboration.
                            print("<div class=\"centerContainer\" style=\"border: calc(7px + .5vw) solid black;width: 90%;margin-top:10px;border-radius: 30px;\">");
                            if (($userInCollaborationQuery && $collaborationQuery["created_by_member_id"] == $_SESSION['member_id']) || $access_level >= 5 ) { // User is the collaboration creator or a Group Leader, show collaboration delete button.
                                print("<div style=\"text-align:right;\">
                                            <label onclick=\"if(confirm('Are you sure you would like to delete this collaboration?')){this.style.display = 'none'; document.getElementById('removeCollaborationForm').submit();}\" class=\"button\" style=\"background-color: red;max-width: 60%;display: inline-block;z-index:1;font-size:calc(17px + .2vw);margin-top:5px; cursor: pointer;margin-left:auto;margin-right:auto;\">
                                                <span style=\"z-index:1;\">Delete Collaboration</span>
                                            </label>
                                            <form style=\"display:inline-block;margin-top:10px;\" id=\"removeCollaborationForm\" target=\"_self\" action=\"/songshome/removecollaboration.php\" method=\"post\">
                                                <input type=\"hidden\" name=\"collaborationId\" value=\"".$collaborationQuery["id"]."\">
                                                <input type=\"hidden\" name=\"collaborationKey\" value=\"".$collaborationQuery["collaboration_key"]."\">
                                            </form>
                                        </div>");
                            }

                            print("<h2 class=\"titleNoBorderArial\">Invited Collaborators (Click Name to Remove): ");
                            print("<br><form onsubmit=\"return confirm('Are you sure you would like to remove ".$collaborationMembersQuery[0]["firstname"]." ".$collaborationMembersQuery[0]["lastname"]." from the collaboration?');\" target=\"_self\" action=\"/songshome/removecollaborationparticipant.php\" method=\"post\">
                                            <input type=\"hidden\" name=\"collaborationId\" value=\"".$collaborationQuery["id"]."\">
                                            <input type=\"hidden\" name=\"collaborationKey\" value=\"".$collaborationQuery["collaboration_key"]."\">
                                            <input type=\"hidden\" name=\"removeMemberId\" value=\"".$collaborationMembersQuery[0]["member_id"]."\">
                                            <button class=\"nobutton hoverunderline\">".$collaborationMembersQuery[0]["firstname"]." ".$collaborationMembersQuery[0]["lastname"]." (".$collaborationMembersQuery[0]["user_email"].")</button>
                                    </form>");
                            print("<span id=\"dots\">...</span><span id=\"more\" style=\"display: none;\">");
                            $i = 0;
                            foreach($collaborationMembersQuery as $collaborationMember) {
                                if ($i != 0) {
                                    print("<form onsubmit=\"return confirm('Are you sure you would like to remove ".$collaborationMember["firstname"]." ".$collaborationMember["lastname"]." from the collaboration?');\" target=\"_self\" action=\"/songshome/removecollaborationparticipant.php\" method=\"post\">
                                                <input type=\"hidden\" name=\"collaborationId\" value=\"".$collaborationQuery["id"]."\">
                                                <input type=\"hidden\" name=\"collaborationKey\" value=\"".$collaborationQuery["collaboration_key"]."\">
                                                <input type=\"hidden\" name=\"removeMemberId\" value=\"".$collaborationMember["member_id"]."\">
                                                <button class=\"nobutton hoverunderline\">".$collaborationMember["firstname"]." ".$collaborationMember["lastname"]." (".$collaborationMember["user_email"].")</button>
                                            </form>");
                                }
                                $i++;
                            }
                            print("
                            </span>
                            <button class=\"button\" onclick=\"showAll()\" id=\"showAllButton\">Show All</button>
                            </h2>");
                            if($access_level >= 5) { 
                               print("<p class=\"arial\">Note: Since you are a Group Leader you can invite yourself to this collaboration to make any changes.</p>");
                            }
                            else {
                               print("<p class=\"arial\">Note: A Group Leader can view this collaboration.</p>");
                            }
                       
                            print("
                            <form id=\"addCollaborationParticipantsForm\" target=\"_self\" action=\"/songshome/addcollaborationparticipants.php\" method=\"post\">
                            <label class=\"button\" style=\"max-width: 80%;display: inline-block;z-index:1;font-size:calc(17px + .2vw);margin-bottom:10px; cursor: pointer;\">

                            <input type=\"hidden\" name=\"collaborationId\" value=\"".$collaborationQuery["id"]."\">
                            <input type=\"hidden\" name=\"collaborationKey\" value=\"".$collaborationQuery["collaboration_key"]."\">

                            
                            <span onclick=\"if(document.getElementById('selctAddMembers').value == 'NOONE') { return false;} this.style.display = 'none'; document.getElementById('addCollaborationParticipantsForm').submit();\" style=\"z-index:1;\">Invite to Collaboration </span>
                            <select  id=\"selctAddMembers\" name=\"addMembersIds\" class=\"selectMenu\" style=\"-webkit-appearance: none;
                            -moz-appearance: none;z-index:2;min-width:calc(150px + 4vw);border: 1px solid #fff;\">
                                <option value=\"NOONE\">-- Click Here --</option>");
                                if ($access_level >= 5) { // Restrict adding multiple users to Group Leaders.
                                    print(" 
                                    <option value=\"ONLYMEMBERS\">All Regular Members</option>
                                    <option value=\"ONLYGROUPLEADERS\">All Group Leaders</option>
                                    <option value=\"NOONE\">----------------</option>");
                                }

                                foreach($allMembersQuery as $user) {
                     
                                    if ($user["access_level"] == 5) {
                                        print("<option value=\"".$user["member_id"]."\">".$user["firstname"]." ".$user["lastname"]." [Group Leader] (".$user["user_email"].")</option>");
                                    }
                                    else {
                                        print("<option value=\"".$user["member_id"]."\">".$user["firstname"]." ".$user["lastname"]." (".$user["user_email"].")</option>");
                                    }
                                    
                                }
                            print("    
                            </select>
                            </label>
                            </form>

                            </div>");

                        }
                        else { // Invited users list for Regular Members.
                            print("<div class=\"centerContainer\" style=\"border: calc(7px + .5vw) solid black;width: 90%;margin-top:10px;border-radius: 30px;\">
                                <h2 class=\"titleNoBorderArial\">Invited Collaborators: ");
                            if ($_SESSION["member_id"] != $collaborationMembersQuery[0]["member_id"]) {
                                print("<br>".$collaborationMembersQuery[0]["firstname"]." ".$collaborationMembersQuery[0]["lastname"]." (".$collaborationMembersQuery[0]["user_email"].")");
                            }
                            else {
                                print("
                                <form style=\"display:inline-block;\" onsubmit=\"return confirm('Are you sure you would like to remove ".$collaborationMembersQuery[0]["firstname"]." ".$collaborationMembersQuery[0]["lastname"]." from the collaboration?');\" target=\"_self\" action=\"/songshome/removecollaborationparticipant.php\" method=\"post\">
                                    <input type=\"hidden\" name=\"collaborationId\" value=\"".$collaborationQuery["id"]."\">
                                    <input type=\"hidden\" name=\"collaborationKey\" value=\"".$collaborationQuery["collaboration_key"]."\">
                                    <input type=\"hidden\" name=\"removeMemberId\" value=\"".$collaborationMembersQuery[0]["member_id"]."\">
                                    <button class=\"nobutton hoverunderline\" style=\"text-align:left;\">(Click to Remove) ".$collaborationMembersQuery[0]["firstname"]." ".$collaborationMembersQuery[0]["lastname"]." (".$collaborationMembersQuery[0]["user_email"].")</button>
                                </form>");    
                            }

                            print("<span id=\"dots\">...</span><span id=\"more\" style=\"display: none;\">");
                            $i = 0;
                            foreach($collaborationMembersQuery as $collaborationMember) {
                                if ($i != 0) {
                                    if ($_SESSION["member_id"] != $collaborationMember["member_id"]) {
                                        print("<br>".$collaborationMember["firstname"]." ".$collaborationMember["lastname"]." (".$collaborationMember["user_email"].")");
                                    }
                                    else {
                                    
                                        print("
                                        <form style=\"display:inline-block;\" onsubmit=\"return confirm('Are you sure you would like to remove ".$collaborationMember["firstname"]." ".$collaborationMember["lastname"]." from the collaboration?');\" target=\"_self\" action=\"/songshome/removecollaborationparticipant.php\" method=\"post\">
                                            <input type=\"hidden\" name=\"collaborationId\" value=\"".$collaborationQuery["id"]."\">
                                            <input type=\"hidden\" name=\"collaborationKey\" value=\"".$collaborationQuery["collaboration_key"]."\">
                                            <input type=\"hidden\" name=\"removeMemberId\" value=\"".$collaborationMember["member_id"]."\">
                                            <button class=\"nobutton hoverunderline\" style=\"text-align:left;\">Click to Remove: ".$collaborationMember["firstname"]." ".$collaborationMember["lastname"]." (".$collaborationMember["user_email"].")</button>
                                        </form>");    
                                        
                                    }
                                }
                                $i++;
                            }
                            print("
                            </span>
                            <button class=\"button\" onclick=\"showAll()\" id=\"showAllButton\">Show All</button>
                            </h2>");
                        
                            print("<p class=\"arial\">Note: Group Leaders can view this collaboration.</p>");
                            
                            print("
                            <form id=\"addCollaborationParticipantsForm\" target=\"_self\" action=\"/songshome/addcollaborationparticipants.php\" method=\"post\">
                            <label class=\"button\" style=\"max-width: 80%;display: inline-block;z-index:1;font-size:calc(17px + .2vw);margin-bottom:10px; cursor: pointer;\">

                            <input type=\"hidden\" name=\"collaborationId\" value=\"".$collaborationQuery["id"]."\">
                            <input type=\"hidden\" name=\"collaborationKey\" value=\"".$collaborationQuery["collaboration_key"]."\">

                            <span onclick=\"if(document.getElementById('selctAddMembers').value == 'NOONE') { return false;} this.style.display = 'none'; document.getElementById('addCollaborationParticipantsForm').submit();\" style=\"z-index:1;\">Invite to Collaboration </span>
                            <select  id=\"selctAddMembers\" name=\"addMembersIds\" class=\"selectMenu\" style=\"-webkit-appearance: none;
                            -moz-appearance: none;z-index:2;min-width:calc(150px + 4vw);border: 1px solid #fff;\">
                                <option value=\"NOONE\">-- Click Here --</option>");
                               
                                foreach($allMembersQuery as $user) {
                                   
                                    if ($user["access_level"] == 5) {
                                        print("<option value=\"".$user["member_id"]."\">".$user["firstname"]." ".$user["lastname"]." [Group Leader] (".$user["user_email"].")</option>");
                                    }
                                    else {
                                        print("<option value=\"".$user["member_id"]."\">".$user["firstname"]." ".$user["lastname"]." (".$user["user_email"].")</option>");
                                    }
                                    
                                }
                            print("    
                            </select>
                            </label>
                            </form>

                            </div>");
                        }

                        print("<h2 class=\"title\" style=\"border-bottom:calc(2px + .1vw) solid black;overflow: hidden;\"><p style=\"text-align:left;\">".$collaborationQuery["collaboration_title"]." Collaboration<span style=\"float:right;\">Created by ".$createdByMemberFullname."</span></p></h2>");

                        // Retrieve all collaboration posts.
                        // $stmt = $db->prepare("SELECT id, collaboration_title, collaboration_key,  firstname, lastname FROM collaborations LEFT JOIN members ON collaborations.created_by_member_id = members.member_id WHERE songId = :songid ORDER BY collaboration_title");
                        $stmt = $db->prepare("SELECT collaboration_posts.id, collaboration_posts.collaboration_id, collaboration_posts.member_id, collaboration_posts.content, collaboration_posts.uploaded, collaboration_posts.file_label, collaboration_posts.file_key, collaboration_posts.filename, collaboration_posts.link, members.firstname, members.lastname, members.user_email FROM collaboration_posts LEFT JOIN members ON collaboration_posts.member_id = members.member_id  WHERE `collaboration_id` = :collaborationid ORDER BY uploaded ASC");
                        $stmt->bindParam('collaborationid',  $collaborationQuery['id']);
                        $stmt->execute();
                        $allPostsQuery = $stmt->fetchAll();

                         // Retrieve all collaboration file requests.
                        $stmt = $db->prepare("SELECT collaboration_requests.id, collaboration_requests.collaboration_id, collaboration_requests.member_id, collaboration_requests.content, collaboration_requests.uploaded, collaboration_requests.request_for, collaboration_requests.open, collaboration_requests.target_post_id, collaboration_requests.target_filename, collaboration_requests.target_filelabel, collaboration_requests.target_filekey,  collaboration_requests.target_link,  members.firstname, members.lastname, members.user_email FROM collaboration_requests LEFT JOIN members ON collaboration_requests.member_id = members.member_id WHERE `collaboration_id` = :collaborationid ORDER BY uploaded ASC");
                        $stmt->bindParam('collaborationid',  $collaborationQuery['id']);
                        $stmt->execute();
                        $allRequestsQuery = $stmt->fetchAll();   

                        // Combine posts and requests.
                        $combinedPostRequests = array_merge(
                            $allPostsQuery,
                            $allRequestsQuery
                        );

                        // Sort combined posts and requests by date.
                        twod_array_sort($combinedPostRequests, 'uploaded', SORT_ASC);
                        $musicPlayerArray = array(); // Holds id's of audio files for the music player.



                        // Display collaboration content.
                        $l = 0; // For music files and music player.
                        $i = 0; // Tracks loop iterartions.
                        foreach($combinedPostRequests as $collaborationActivity) {
                           
                            if (isset($collaborationActivity["filename"]) && isset($collaborationActivity["link"]) && $collaborationActivity["filename"] == NULL  && $collaborationActivity["link"] == NULL) { // User posted feedback.
                                print("<h2 class=\"postsText\" style=\"width: 80%;margin-top:calc(15px + 2vw);border-radius: 30px;position:relative; z-index: 1;\">");
                                if ($userInCollaborationQuery && ($collaborationQuery["created_by_member_id"] == $_SESSION['member_id'] || $_SESSION['member_id'] == $collaborationActivity["member_id"] || $access_level >= 5)) {
                                    print("
                                    <form id=\"removeCollaborationPostForm$i\" target=\"_self\" action=\"/songshome/removecollaborationpost.php\" method=\"post\">
                                        <input type=\"hidden\" name=\"collaborationId\" value=\"".$collaborationQuery["id"]."\">
                                        <input type=\"hidden\" name=\"collaborationKey\" value=\"".$collaborationQuery["collaboration_key"]."\">
                                        <input type=\"hidden\" name=\"removePostId\" value=\"".$collaborationActivity["id"]."\">
                                        <label onclick=\"if (confirm('Are you sure you would like to remove this post?')) {document.getElementById('removeCollaborationPostForm$i').submit(); }\"class=\"removeContentButton\" style=\"top:calc(-15px + -.3vw);right:calc(-15px + -.3vw); z-index: 2;\"><span>X</span></label>
                                    </form>
                                    ");
                                }
                                print("\"".$collaborationActivity["content"]."\"");
                                print("<br><br>
                                    <div style=\"text-align:right;\"><span style=\"display:block;font-size: calc(10px + .85vw);\">-".$collaborationActivity["firstname"]." ".$collaborationActivity["lastname"].", Contact: ".$collaborationActivity["user_email"].",<br>Posted: ".date('m/d/Y h:ia', strtotime($collaborationActivity["uploaded"]))." ".$timezone."</span></div>");
                                print("</h2>");
                            }
                            else if (isset($collaborationActivity["filename"]) && isset($collaborationActivity["link"])) { // User posted a file or link to a file.

                                print("<h2 class=\"postsText\" style=\"width: 80%;margin-top:calc(15px + 2vw);border-radius: 30px;position:relative; z-index: 1;border: calc(.5px + .1vw) solid rgb(79, 189, 73);\">");
                                if ($userInCollaborationQuery && ($collaborationQuery["created_by_member_id"] == $_SESSION['member_id'] || $_SESSION['member_id'] == $collaborationActivity["member_id"] || $access_level >= 5)) {
                                    print("
                                    <form id=\"removeCollaborationPostForm$i\" target=\"_self\" action=\"/songshome/removecollaborationpost.php\" method=\"post\">
                                        <input type=\"hidden\" name=\"collaborationId\" value=\"".$collaborationQuery["id"]."\">
                                        <input type=\"hidden\" name=\"collaborationKey\" value=\"".$collaborationQuery["collaboration_key"]."\">
                                        <input type=\"hidden\" name=\"removePostId\" value=\"".$collaborationActivity["id"]."\">
                                        <label onclick=\"if (confirm('Are you sure you would like to remove this post?')) {document.getElementById('removeCollaborationPostForm$i').submit(); }\" class=\"removeContentButton\" style=\"top:calc(-15px + -.3vw);right:calc(-15px + -.3vw); z-index: 2;background-color: rgb(79, 189, 73);\"><span>X</span></label>
                                    </form>
                                    ");
                                }

                                if ($collaborationActivity["filename"] != NULL) { // User posted a file.
                                    print(" <div class=\"musicPlayerWrap\" style=\"width:80%;font-size: calc(10px + .85vw);text-align:center;padding-bottom:3px;\">
                                                <p class=\"musicPlayerTitle\">".$collaborationActivity["file_label"]." - </p><a onclick=\"alert('Before opening the file consider scanning it if you are not sure that it is safe.');\" class=\"musicPlayerTitleDownload\" href=\"/songshome/downloadCollaborationFile.php?fileId=".$collaborationActivity["id"]."&fileKey=".$collaborationActivity["file_key"]."&collaboration_id=".$collaborationQuery["id"]."&collaboration_key=".$collaborationQuery["collaboration_key"]."\">Download</a>
                                                <label onclick=\"location.href='https://www.virustotal.com/gui/home/upload'\" style=\"display:inline-block;margin-left: 10px;margin-top: 10px;\" class=\"button\">Scan File</label>
                                            </div>");
                                    
                                    if(file_exists("./collaborationPostFiles/" . $collaborationActivity["filename"])) {
                                        
                                        $contentType = mime_content_type("./collaborationPostFiles/".$collaborationActivity['filename']);

                                        if ($contentType == "video/mp4" || $contentType == "audio/mpeg" || $contentType == "audio/ogg" || $contentType == "audio/wav" || $contentType == "audio/x-wav" ) { // File is an .mp3 file, embed in music player.
                                            print("
                                                    <div class=\"musicPlayerWrap\" style=\"width:80%;\">
                                                        <div id=\"musicWrapClickDetect\" class=\"musicPlayer$l\" style=\"width:100%; box-sizing: border-box;\">
                                                        
                                                            <audio crossorigin>
                                                                <source src=\"/songshome/collaborationPostFiles/".$collaborationActivity["filename"]."\" type=\"$contentType\">                                
                                                            </audio>    
                                                        </div>                                     
                                                    </div>
                                                    <br>
                        
                                            ");
                                            $musicPlayerArray[] = "musicPlayer".$l;
                                            $l++;
                                        }
                                        
                                    }
                                }

                              
                                if ($collaborationActivity["link"] != NULL) { // User posted a link to a file.
                                    print("<div class=\"arial\" style=\"width: 80%;\">Link: <a onclick=\"if (!confirm('Visit this link? Consider scanning it beforehand.')) { event.preventDefault();  }\" class=\"link\" href=\"".$collaborationActivity["link"]."\">".$collaborationActivity["link"]."</a><label onclick=\"location.href='https://www.virustotal.com/gui/home/url'\" style=\"display:inline-block;margin-left: 10px;margin-top: 10px;\" class=\"button\">Scan Link</label></div><br>");
                                }

                                print("\"".$collaborationActivity["content"]."\"");
                                print("<div style=\"text-align:right;\"><span style=\"display:block;font-size: calc(10px + .85vw);\">-".$collaborationActivity["firstname"]." ".$collaborationActivity["lastname"].", Contact: ".$collaborationActivity["user_email"].",<br>Posted: ".date('m/d/Y h:ia', strtotime($collaborationActivity["uploaded"]))." ".$timezone."</span></div>");
                                if ($userInCollaborationQuery) {
                                    print("<br><br>
                                        <p class=\"postsTextDivider arial\" style=\"border-bottom:calc(0.5px + 0.1vw) solid rgb(79, 189, 73);color:rgb(79, 189, 73);\"><span style=\"padding-left: 20px;\">Request a Change to This File</span></p>
                                        <form id=\"requestChangeForm$i\" target=\"_self\" style=\"text-align:center;\" action=\"/songshome/requestchange.php\" method=\"post\">
                                            <textarea id=\"requestMessage$i\" placeholder=\"Request Message\" name=\"requestMessage\" class=\"songTitleUpload\" maxlength=\"10000\" style=\"margin-left:auto;margin-right:auto;max-width:80%;min-width:80%;border-radius:8px;font-size:calc(17px + .5vw);text-align:left;padding-left:20px;margin-bottom: 20px;padding-top: 20px;padding-bottom: 20px;border: calc(0.5px + 0.1vw) solid rgb(79, 189, 73);\" form=\"requestChangeForm$i\"></textarea>
                                            <label class=\"button\" style=\"background-color: rgb(79, 189, 73);max-width: 60%;display: inline-table;z-index:1;font-size:calc(17px + .2vw);margin-bottom:10px; cursor: pointer;margin-left:auto;margin-right:auto;\">

                                                <input type=\"hidden\" name=\"collaborationId\" value=\"".$collaborationQuery["id"]."\">
                                                <input type=\"hidden\" name=\"collaborationKey\" value=\"".$collaborationQuery["collaboration_key"]."\">

                                                <input type=\"hidden\" name=\"targetPostId\" value=\"".$collaborationActivity["id"]."\">

                                                <span onclick=\"if(document.getElementById('selectRequestMember$i').value == 'NOONE') { return false;} if(checkIfEmpty('requestMessage$i', 'The request message cannot be empty.')) {this.style.display = 'none';document.getElementById('requestChangeForm$i').submit();}\" style=\"z-index:1;\">Request Change from: </span>
                                                <select  id=\"selectRequestMember$i\" name=\"requestMemberId\" class=\"selectMenu\" style=\"-webkit-appearance: none;
                                                -moz-appearance: none;z-index:2;min-width:calc(150px + 4vw);border: 1px solid #fff;\">
                                                    <option value=\"NOONE\">-- Click Here --</option>
                                                    <option value=\"COLLABORATIONPARTICIPANTS\">All Collaboration Participants</option>");
                                                    if ($access_level >= 5) {
                                                        print("<option value=\"PUBLIC\">COMMUNITY/PUBLIC (Share Link to Collaboration)</option>");
                                                    }
                                                    print("
                                                    <option value=\"NOONE\">----------------</option>
                                                    ");
                                                    foreach($collaborationMembersQuery as $user) {
                                                        if ($user["access_level"]  == 5) {
                                                            print("<option value=\"".$user["member_id"]."\">".$user["firstname"]." ".$user["lastname"]." [Group Leader] (".$user["user_email"].")</option>");
                                                        }
                                                        else {
                                                            print("<option value=\"".$user["member_id"]."\">".$user["firstname"]." ".$user["lastname"]." (".$user["user_email"].")</option>");
                                                        }
                                                    }
                                                print("
                                                </select>
                                            </label>
                                        </form>

                                        ");
                                }
                                print("</h2>");

                            }
                            else if (isset($collaborationActivity["target_filename"]) && isset($collaborationActivity["target_link"])) { // User posted a requested change to a file.
                                $requestedMemberIds = array();
                                if ($collaborationActivity["request_for"] != "PUBLIC") { // Format the requested user id list.
                                    $requestedMemberIds = explode(",", $collaborationActivity["request_for"]);
                                }

                                print("<h2 class=\"postsText\" style=\"width: 80%;margin-top:calc(15px + 2vw);border-radius: 30px;position:relative; z-index: 1;border: calc(.5px + .1vw) solid red;\">");
                                if ($userInCollaborationQuery && ($collaborationQuery["created_by_member_id"] == $_SESSION['member_id'] || $_SESSION['member_id'] == $collaborationActivity["member_id"] || in_array($_SESSION["member_id"], $requestedMemberIds)  || $access_level >= 5)) {
                                    print("
                                    <form id=\"removeCollaborationRequestForm$i\" target=\"_self\" action=\"/songshome/removecollaborationrequest.php\" method=\"post\">
                                        <input type=\"hidden\" name=\"collaborationId\" value=\"".$collaborationQuery["id"]."\">
                                        <input type=\"hidden\" name=\"collaborationKey\" value=\"".$collaborationQuery["collaboration_key"]."\">
                                        <input type=\"hidden\" name=\"removeRequestId\" value=\"".$collaborationActivity["id"]."\">
                                        <label onclick=\"if (confirm('Are you sure you would like to remove this request?')) {document.getElementById('removeCollaborationRequestForm$i').submit(); }\" class=\"removeContentButton\" style=\"top:calc(-15px + -.3vw);right:calc(-15px + -.3vw); z-index: 2;background-color: red;\"><span>X</span></label>
                                    </form>
                                    ");
                                 }
                                
                                // Retrieve the names of users the request was made for.
                                if ($collaborationActivity['request_for'] != "PUBLIC") { // Request was made to a registered user.
                                    $requestFor = $collaborationActivity['request_for'];
                                    $requestForQuery = $db->query("SELECT `firstname`, `lastname` FROM `members` WHERE `member_id` IN ($requestFor)")->fetchAll();

                                    print("<p class=\"arial\" style=\"padding-bottom:10px;\">Request for:<span style=\"color:red;\">");
                                    $z = 0;
                                    foreach($requestForQuery as $requestForUser) {
                                        if ($z != 0) { print(","); }
                                        print(" ".$requestForUser['firstname']." ".$requestForUser['lastname']."");
                                        $z++;
                                    }
                                    print("</span></p>");
                                }
                                else { // Request was made to the public.
                                    print("<p class=\"arial\" style=\"padding-bottom:10px;\">Request for: <span style=\"color:red;\">Community/Public (Share Link to Collaboration)</span></p>");
                                }

                                print("\"".$collaborationActivity["content"]."\"");
                                print("<div style=\"text-align:right;\"><br><span style=\"display:block;font-size: calc(10px + .85vw);\">Requested by: ".$collaborationActivity["firstname"]." ".$collaborationActivity["lastname"].", Contact: ".$collaborationActivity["user_email"].",<br>Posted: ".date('m/d/Y h:ia', strtotime($collaborationActivity["uploaded"]))." ".$timezone."</span></div>");
                            

                                // Retrieve target post for this request change.
                                $stmt = $db->prepare("SELECT collaboration_posts.id, collaboration_posts.collaboration_id, collaboration_posts.member_id, collaboration_posts.content, collaboration_posts.uploaded, collaboration_posts.file_label, collaboration_posts.file_key, collaboration_posts.filename, collaboration_posts.link, members.firstname, members.lastname, members.user_email FROM collaboration_posts LEFT JOIN members ON collaboration_posts.member_id = members.member_id   WHERE `id` = :targetpostid AND `collaboration_id` = :collaborationid ORDER BY uploaded ASC");
                                $stmt->bindParam('targetpostid',  $collaborationActivity["target_post_id"]);
                                $stmt->bindParam('collaborationid',  $collaborationQuery['id']);
                                $stmt->execute();
                                $targetPostQuery = $stmt->fetch();   

                                if ($targetPostQuery) { // Target post has not been deleted, retrieve details.
                                    print("<p class=\"postsTextDivider arial\"><span style=\"padding-left: 20px;\"></span></p>");
                                    print("<p class=\"arial\" style=\"text-align:center;padding-bottom: 10px;\">".$collaborationActivity["firstname"]." ".$collaborationActivity["lastname"]." Requested a Change To:</p>");

                                    if ($targetPostQuery["filename"] != NULL) { // Target post contained a file.
                                        print(" <div class=\"musicPlayerWrap\" style=\"width:80%;font-size: calc(10px + .85vw);text-align:center;padding-bottom:3px;\">
                                                    <p class=\"musicPlayerTitle\">".$targetPostQuery["file_label"]." - </p><a onclick=\"alert('Before opening the file consider scanning it if you are not sure that it is safe.');\" class=\"musicPlayerTitleDownload\" href=\"/songshome/downloadCollaborationFile.php?fileId=".$targetPostQuery["id"]."&fileKey=".$targetPostQuery["file_key"]."&collaboration_id=".$collaborationQuery["id"]."&collaboration_key=".$collaborationQuery["collaboration_key"]."\">Download</a>
                                                    <label onclick=\"location.href='https://www.virustotal.com/gui/home/upload'\" style=\"display:inline-block;margin-left: 10px;margin-top: 10px;\" class=\"button\">Scan File</label>
                                                </div>");
                                        
                                        if(file_exists("./collaborationPostFiles/" . $targetPostQuery["filename"])) {
                                            
                                            $contentType = mime_content_type("./collaborationPostFiles/".$targetPostQuery['filename']);
    
                                            if ($contentType == "video/mp4" || $contentType == "audio/mpeg" || $contentType == "audio/ogg" || $contentType == "audio/wav" || $contentType == "audio/x-wav" ) { // File is an .mp3 file, embed in music player.
                                                print("
                                                        <div class=\"musicPlayerWrap\" style=\"width:80%;\">
                                                            <div id=\"musicWrapClickDetect\" class=\"musicPlayer$l\" style=\"width:100%; box-sizing: border-box;\">
                                                            
                                                                <audio crossorigin>
                                                                    <source src=\"/songshome/collaborationPostFiles/".$targetPostQuery["filename"]."\" type=\"$contentType\">                                
                                                                </audio>    
                                                            </div>                                     
                                                        </div>
                                                        <br>
                            
                                                ");
                                                $musicPlayerArray[] = "musicPlayer".$l;
                                                $l++;
                                            }
                                            
                                        }
                                    }
    
                                  
                                    if ($targetPostQuery["link"] != NULL) { // Target post contained a link.
                                        print("<div class=\"arial\" style=\"width: 80%;\">Link: <a onclick=\"if (!confirm('Visit this link? Consider scanning it beforehand.')) { event.preventDefault();  }\" class=\"link\" href=\"".$targetPostQuery["link"]."\">".$targetPostQuery["link"]."</a><label onclick=\"location.href='https://www.virustotal.com/gui/home/url'\" style=\"display:inline-block;margin-left: 10px;margin-top: 10px;\" class=\"button\">Scan Link</label></div><br>");
                                    }

                                    print("<div style=\"text-align:right;\"><span style=\"display:block;font-size: calc(10px + .85vw);\">File By: ".$targetPostQuery["firstname"]." ".$targetPostQuery["lastname"].", Contact: ".$targetPostQuery["user_email"].",<br>Posted: ".date('m/d/Y h:ia', strtotime($targetPostQuery["uploaded"]))." ".$timezone."</span></div>");
                                }

                                // Retrieve all request submissions for this request.
                                $stmt = $db->prepare("SELECT collaboration_request_files.id, collaboration_request_files.collaboration_id, collaboration_request_files.collaboration_requests_id, collaboration_request_files.member_id, collaboration_request_files.filename, collaboration_request_files.file_key, collaboration_request_files.link, collaboration_request_files.uploaded, collaboration_request_files.visitor_firstname, collaboration_request_files.visitor_lastname, collaboration_request_files.visitor_email, collaboration_request_files.verified, members.firstname, members.lastname, members.user_email FROM collaboration_request_files LEFT JOIN members ON collaboration_request_files.member_id = members.member_id   WHERE `collaboration_requests_id` = :collaborationrequestsid AND `collaboration_id` = :collaborationid AND `verified` = 1 ORDER BY uploaded ASC");
                                $stmt->bindParam('collaborationrequestsid',  $collaborationActivity["id"]);
                                $stmt->bindParam('collaborationid',  $collaborationQuery['id']);
                                $stmt->execute();
                                $targetRequestFileQuery = $stmt->fetchAll();  

                                $b = 0;
                                foreach($targetRequestFileQuery as $requestFileSubmission) { // Display all user file submissions for this request.

                                    

                                    if ($b != 0) { // Do not print title.
                                        print("<p class=\"postsTextDivider arial\" style=\"border-bottom:calc(0.5px + 0.1vw) solid red;color:red;margin-top:0;margin-bottom:0;\"><span style=\"padding-left: calc(9px + 1.8vw);\"></span></p>");
                                    }
                                    else { // Print title.
                                        print("<p class=\"postsTextDivider arial\" style=\"border-bottom:calc(0.5px + 0.1vw) solid red;color:red;margin-top:0;margin-bottom:0;\"><span style=\"padding-left: calc(9px + 1.8vw);\">File Submissions From Requested Participants</span></p>");
                                    }

                                    print("
                                    <div style=\"text-align:left;\">
                                    <form style=\"display:inline-block;margin-top:10px;\" id=\"removeRequestSubmissionForm$i$b\" target=\"_self\" action=\"/songshome/removecollaborationrequestsubmission.php\" method=\"post\">
                                        <input type=\"hidden\" name=\"collaborationId\" value=\"".$collaborationQuery["id"]."\">
                                        <input type=\"hidden\" name=\"collaborationKey\" value=\"".$collaborationQuery["collaboration_key"]."\">
                                        <input type=\"hidden\" name=\"removeRequestId\" value=\"".$requestFileSubmission["collaboration_requests_id"]."\">
                                        <input type=\"hidden\" name=\"removeRequestSubmissionId\" value=\"".$requestFileSubmission["id"]."\">
                                        <label onclick=\"if (confirm('Are you sure you would like to remove this file submission?')) {document.getElementById('removeRequestSubmissionForm$i$b').submit(); }\" class=\"removeContentButton\" style=\"position:relative;display:inline-block; z-index: 2;background-color: red;\"><span>X</span></label>
                                    </form>
                                    </div>
                                    ");
                                    
                                    if ($requestFileSubmission["filename"] != NULL) { // User posted a file.
                                        print("<div style=\"text-align:left;\"> <div class=\"musicPlayerWrap\" style=\"font-size: calc(10px + .85vw);text-align:center;padding-bottom:3px;display:inline-block;\">
                                                    <a onclick=\"alert('Before opening the file consider scanning it if you are not sure that it is safe.');\" class=\"musicPlayerTitleDownload\" href=\"/songshome/downloadCollaborationRequestFile.php?fileId=".$requestFileSubmission["id"]."&fileKey=".$requestFileSubmission["file_key"]."&collaboration_id=".$collaborationQuery["id"]."&collaboration_key=".$collaborationQuery["collaboration_key"]."\">Submitted File - Download</a>
                                                    <label onclick=\"location.href='https://www.virustotal.com/gui/home/upload'\" style=\"display:inline-block;margin-left: 10px;margin-top: 10px;\" class=\"button\">Scan File</label>
                                                </div>");
                                        
                                        if(file_exists("./collaborationRequestFiles/" . $requestFileSubmission["filename"])) {
                                            
                                            $contentType = mime_content_type("./collaborationRequestFiles/".$requestFileSubmission['filename']);
    
                                            if ($contentType == "video/mp4" || $contentType == "audio/mpeg" || $contentType == "audio/ogg" || $contentType == "audio/wav" || $contentType == "audio/x-wav" ) { // File is an .mp3 file, embed in music player.
                                                print("
                                                        <div class=\"musicPlayerWrap\" style=\"width:90%;display:inline-block;\">
                                                            <div id=\"musicWrapClickDetect\" class=\"musicPlayer$l\" style=\"width:100%; box-sizing: border-box;\">
                                                            
                                                                <audio crossorigin>
                                                                    <source src=\"/songshome/collaborationRequestFiles/".$requestFileSubmission["filename"]."\" type=\"$contentType\">                                
                                                                </audio>    
                                                            </div>                                     
                                                        </div>
                                                        <br>
                                                        </div>
                                                ");
                                                $musicPlayerArray[] = "musicPlayer".$l;
                                                $l++;
                                            }
                                            
                                        }
                                    }
    
                                
                                    if ($requestFileSubmission["link"] != NULL) { // User posted a link to a file.
                                        print("<div class=\"arial\" style=\"display:inline-block;margin-top:10px;margin-bottom:10px;color:red;\">Link: <a onclick=\"if (!confirm('Visit this link? Consider scanning it beforehand.')) { event.preventDefault();  }\" class=\"link\" href=\"".$requestFileSubmission["link"]."\">".$requestFileSubmission["link"]."</a><label onclick=\"location.href='https://www.virustotal.com/gui/home/url'\" style=\"display:inline-block;margin-left: 10px;margin-top: 10px;\" class=\"button\">Scan Link</label></div>");
                                    }

                                    if ($requestFileSubmission["member_id"] != NULL) { // Retreive the name of the person that submitted the file.
                                        print("<div style=\"text-align:left;\"><span style=\"display:block;font-size: calc(10px + .85vw);\">Submission By: ".$requestFileSubmission["firstname"]." ".$requestFileSubmission["lastname"].", Contact: ".$requestFileSubmission["user_email"].",<br>Posted: ".date('m/d/Y h:ia', strtotime($requestFileSubmission["uploaded"]))." ".$timezone."</span></div>");
                                    }
                                    else { // Public submission.
                                        print("<div style=\"text-align:left;\"><span style=\"display:block;font-size: calc(10px + .85vw);\">(COMMUNITY/PUBLIC) Submission By: ".$requestFileSubmission["visitor_firstname"]." ".$requestFileSubmission["visitor_lastname"].", Contact: ".$requestFileSubmission["visitor_email"].",<br>Posted: ".date('m/d/Y h:ia', strtotime($requestFileSubmission["uploaded"]))." ".$timezone."</span></div>");
                                    }
                                    

                                    $b++;

                                    if ($b == sizeof($targetRequestFileQuery)) {
                                        print("<p class=\"postsTextDivider arial\" style=\"line-height: 0;border-bottom:calc(0.5px + 0.1vw) solid red;color:red;margin-top:0;margin-bottom:0;\"><span style=\"padding-left: calc(9px + 1.8vw);\"></span></p>");
                                    }
                                }

                                if ($collaborationActivity["open"] == 1) {  // Collaboration request is open, allow submissions.
                                  
                                    if ($userInCollaborationQuery && ($access_level >= 5 || $collaborationActivity["member_id"] == $_SESSION['member_id'])) {
                                        print("<p class=\"postsTextDivider arial\" style=\"border-bottom:calc(0.5px + 0.1vw) solid red;color: red;margin-top:0;margin-bottom:0;\"><br><span style=\"padding-left: calc(9px + 1.8vw);\">Close This Request/Submissions</span></p>");
                                        print("<div style=\"text-align:center;\">
                                                    <label onclick=\"if(confirm('Are you sure you would like to close this request?')){this.style.display = 'none';document.getElementById('closeRequestsForm$i').submit();}\" class=\"button\" style=\"background-color: red;max-width: 60%;display: inline-table;z-index:1;font-size:calc(17px + .2vw);margin-top:20px;margin-bottom:10px; cursor: pointer;margin-left:auto;margin-right:auto;\">
                                                        <span style=\"z-index:1;\">Close Request/Submissions</span>
                                                    </label>
                                                    <form style=\"display:inline-block;margin-top:10px;\" id=\"closeRequestsForm$i\" target=\"_self\" action=\"/songshome/closerequestsubmissions.php\" method=\"post\">
                                                        <input type=\"hidden\" name=\"collaborationId\" value=\"".$collaborationQuery["id"]."\">
                                                        <input type=\"hidden\" name=\"collaborationKey\" value=\"".$collaborationQuery["collaboration_key"]."\">
                                                        <input type=\"hidden\" name=\"closeRequestId\" value=\"".$collaborationActivity["id"]."\">
                                                    </form>
                                                </div>");
                                    }
                                   
                                    if ($userInCollaborationQuery && ($collaborationActivity["request_for"] == "PUBLIC" || in_array($userInCollaborationQuery["member_id"], $requestedMemberIds) || $access_level >= 5)) { 
                                        // Allow file submissions to this request from the requested users.
                                        print("<p class=\"postsTextDivider arial\" style=\"border-bottom:calc(0.5px + 0.1vw) solid rgb(79, 189, 73);color: rgb(79, 189, 73);margin-top:0;margin-bottom:0;\"><br><span style=\"padding-left: calc(9px + 1.8vw);\">Upload Your Submission</span></p>");

                                        print("  
                                            <input form=\"requestSubmissionForm$i\" placeholder=\"Link to File (If uploaded somewhere else.)\" name=\"requestLinkInput\"  id=\"list1_requestLinkInput$i\" class=\"audioLabelUpload\" maxlength=\"1000\" style=\"font-size:calc(5px + 2vw);padding: calc(5px + .1vw) calc(5px + .1vw);margin-left:auto;margin-right:auto;text-align:center;display:block;max-width:80%;width:70%;\" type=\"text\"> 
                                            <div style=\"text-align:center;\">   
                                                <p class=\"arial\" style=\"font-size: calc(15px + 1vw);\">OR</p>
                                                <label class=\"normalText\" id=\"list1_audioFileName$i\" style=\"font-size: calc(10px + .8vw);display:inline-block;\">Selected Filename (Under 10MB)</label>
                                                <button style=\"display:inline-block;position:relative;margin-left:auto;\" onclick=\"document.getElementById('list1_itemInputList$i').click();\" class=\"button\">Select File</button>
                                                
                                                <form id=\"requestSubmissionForm$i\" enctype=\"multipart/form-data\" autocomplete=\"off\"  target=\"_self\" action=\"/songshome/addrequestsubmission.php\" method=\"post\">   
                                                    <input type=\"hidden\" name=\"collaborationId\" value=\"".$collaborationQuery["id"]."\">
                                                    <input type=\"hidden\" name=\"collaborationKey\" value=\"".$collaborationQuery["collaboration_key"]."\">
                                                    <input type=\"hidden\" name=\"collaborationRequestId\" value=\"".$collaborationActivity["id"]."\">
                                                    <input form=\"requestSubmissionForm$i\" style=\"display:none;\" id=\"list1_itemInputList$i\" name=\"audioFiles\"  onchange=\"if((this.files[0].size / 1048576) > 11) {alert('File cannot be over 10MB. Upload the file to online storage and post the link instead.');return;} if(this.files[0].type == 'application/vnd.microsoft.portable-executable' || this.files[0].type == 'application/x-msdownload' ){alert('Filetype not supported.');return;}  document.getElementById('list1_audioFileName$i').textContent = this.files[0].name;\" type=\"file\">
                                                </form>
                                                <label onclick=\"if(document.getElementById('list1_itemInputList$i').files.length != 0 || checkIfEmptyNoAlert('list1_requestLinkInput$i')) {this.style.display = 'none'; document.getElementById('requestSubmissionForm$i').submit();} else {alert('Please provide a link to a file or select a file to upload.');}\" class=\"button\" style=\"margin-left:auto;margin-right:auto;background-color: rgb(79, 189, 73);max-width: 60%;display: inline-table;z-index:1;font-size:calc(17px + .2vw);margin-top:20px;margin-bottom:10px; cursor: pointer;margin-left:auto;margin-right:auto;\">
                                                    <span style=\"z-index:1;\">Upload Submission</span>
                                                </label>
                                            </div>
                                        ");
                                    }
                                }
                                else {
                                    print("<p class=\"postsTextDivider arial\" style=\"border-bottom:calc(0.5px + 0.1vw) solid black;color: black;margin-top:0;margin-bottom:0;\"><br><span style=\"padding-left: calc(9px + 1.8vw);\">Request Closed</span></p>");
                                }


                                print("</h2>");

                            }

                            $i++;
                        }

                        if ($userInCollaborationQuery) {
                            print("<h2 class=\"title arial\" style=\"width:80%;\">Create a New Post</h2>");
                            print("
                            
                                <textarea form=\"createNewPostForm\" id=\"newPostText\" placeholder=\"Post Message\" name=\"newPostText\" class=\"songTitleUpload\" maxlength=\"100000\" style=\"display:block;margin-left:auto;margin-right:auto;max-width:80%;min-width:80%;border-radius:8px;font-size:calc(17px + .5vw);text-align:left;padding-left:20px;margin-bottom: 20px;padding-top: 20px;padding-bottom: 200px;\"></textarea>
                                <input form=\"createNewPostForm\" placeholder=\"Link to File (If uploaded somewhere else.)\" name=\"createNewPostLinkInput\" id=\"newPostLinkInput\" class=\"audioLabelUpload\" maxlength=\"1000\" style=\"font-size:calc(5px + 2vw);padding: calc(5px + .1vw) calc(5px + .1vw);margin-left:auto;margin-right:auto;text-align:center;display:block;width:70%;\" type=\"text\">  

                                <div style=\"text-align:center;\">   
                                    <p class=\"arial\" style=\"font-size: calc(15px + 1.3vw);\">OR</p>
                                    <input form=\"createNewPostForm\" placeholder=\"File Label\" name=\"newPostAudioFileLabel\" class=\"audioLabelUpload\" maxlength=\"200\" style=\"font-size:calc(5px + 2vw);margin-left:auto;margin-right:auto;text-align:center;display:block;width:40%;\" type=\"text\">
                                    <label class=\"normalText\" id=\"newPostAudioFilename\" style=\"font-size: calc(15px + .8vw);display:inline-block;\">Selected Filename (Under 10MB)</label>
                                    <button style=\"display:inline-block;position:relative;margin-left:auto;\" onclick=\"document.getElementById('newPostAudioFile').click();\" class=\"button\">Select File</button>
                                    
                                    <form id=\"createNewPostForm\" enctype=\"multipart/form-data\" autocomplete=\"off\"  target=\"_self\" action=\"/songshome/createnewpost.php\" method=\"post\"> 
                                        <input type=\"hidden\" name=\"collaborationId\" value=\"".$collaborationQuery["id"]."\">
                                        <input type=\"hidden\" name=\"collaborationKey\" value=\"".$collaborationQuery["collaboration_key"]."\">
                                        <input style=\"display:none;\" id=\"newPostAudioFile\" name=\"newPostAudioFile\"  onchange=\"if((this.files[0].size / 1048576) > 11) {alert('File cannot be over 10MB. Upload the file to online storage and post the link instead.');return;} if(this.files[0].type == 'application/vnd.microsoft.portable-executable' || this.files[0].type == 'application/x-msdownload' ){alert('Filetype not supported.');return;}  document.getElementById('newPostAudioFilename').textContent = this.files[0].name;\" type=\"file\">
                                    </form>
                                    <label onclick=\"if (document.getElementById('newPostAudioFile').files.length > 0 && checkIfEmptyNoAlert('newPostLinkInput')) { if(!confirm('A single link or a single file can be posted. Would you like to upload just the link?')) {return false;} } if(checkIfEmpty('newPostText', 'The post message cannot be empty.')) {this.style.display = 'none';document.getElementById('createNewPostForm').submit(); } \" class=\"button\" style=\"margin-left:auto;margin-right:auto;max-width: 60%;display: inline-table;z-index:1;font-size:calc(22px + .4vw);margin-top:20px;margin-bottom:10px; cursor: pointer;margin-left:auto;margin-right:auto;\">
                                        <span style=\"z-index:1;\">Upload New Post</span>
                                    </label>
                                </div>
                            ");
                        }

                        print("<div id=\"endPage\" style=\"display:block;width:100%;padding-top:100px;\"></div>");
                        
                      
                }
                else { // This is a visitor, only show collaboration requests made to the public.

                    include('temp_signinbutton.php'); // REMOVE LATER

                    print("<h2 class=\"title arial\"><p style=\"text-align:left;\">".$collaborationQuery["collaboration_title"]." Collaboration</p></h2>");

                    // Retrieve all collaboration file requests.
                    $stmt = $db->prepare("SELECT collaboration_requests.id, collaboration_requests.collaboration_id, collaboration_requests.member_id, collaboration_requests.content, collaboration_requests.uploaded, collaboration_requests.request_for, collaboration_requests.open, collaboration_requests.target_post_id, collaboration_requests.target_filename, collaboration_requests.target_filelabel, collaboration_requests.target_filekey,  collaboration_requests.target_link,  members.firstname, members.lastname, members.user_email FROM collaboration_requests LEFT JOIN members ON collaboration_requests.member_id = members.member_id WHERE `collaboration_id` = :collaborationid AND `request_for` = 'PUBLIC' AND `open` = 1 ORDER BY uploaded ASC");
                    $stmt->bindParam('collaborationid',  $collaborationQuery['id']);
                    $stmt->execute();
                    $allRequestsQuery = $stmt->fetchAll();   

                    $l = 0; // For music files and music player.
                    $i = 0; // Tracks loop iterartions.
                    foreach($allRequestsQuery as $request) {

                        print("<h2 class=\"postsText\" style=\"width: 80%;margin-top:calc(15px + 2vw);border-radius: 30px;position:relative; z-index: 1;border: calc(.5px + .1vw) solid black;\">");

                        print("<p class=\"arial\" style=\"padding-bottom:10px;\">Request for Our <span style=\"color:black;\">Community</span></p>");
                        print("\"".$request["content"]."\"");
                        print("<div style=\"text-align:right;\"><br><span style=\"display:block;font-size: calc(10px + .85vw);\">Requested by: ".$request["firstname"]." ".$request["lastname"]."<br>Posted: ".date('m/d/Y h:ia', strtotime($request["uploaded"]))." ".$timezone."</span></div>");
                    

                        // Retrieve target post for this request change.
                        $stmt = $db->prepare("SELECT collaboration_posts.id, collaboration_posts.collaboration_id, collaboration_posts.member_id, collaboration_posts.content, collaboration_posts.uploaded, collaboration_posts.file_label, collaboration_posts.file_key, collaboration_posts.filename, collaboration_posts.link, members.firstname, members.lastname FROM collaboration_posts LEFT JOIN members ON collaboration_posts.member_id = members.member_id   WHERE `id` = :targetpostid AND `collaboration_id` = :collaborationid ORDER BY uploaded ASC");
                        $stmt->bindParam('targetpostid',  $request["target_post_id"]);
                        $stmt->bindParam('collaborationid',  $collaborationQuery['id']);
                        $stmt->execute();
                        $targetPostQuery = $stmt->fetch();   

                        if ($targetPostQuery) { // Target post has not been deleted, retrieve details.
                            print("<p class=\"postsTextDivider arial\"><span style=\"padding-left: 20px;\"></span></p>");
                            print("<p class=\"arial\" style=\"text-align:center;padding-bottom: 10px;\">".$request["firstname"]." ".$request["lastname"]." Requested a Change To:</p>");

                            if ($targetPostQuery["filename"] != NULL) { // Target post contained a file.
                                print(" <div class=\"musicPlayerWrap\" style=\"width:80%;font-size: calc(10px + .85vw);text-align:center;padding-bottom:3px;\">
                                            <p class=\"musicPlayerTitle\">".$targetPostQuery["file_label"]." - </p><a onclick=\"alert('Before opening the file consider scanning it if you are not sure that it is safe.');\" class=\"musicPlayerTitleDownload\" href=\"/songshome/downloadCollaborationFile.php?fileId=".$targetPostQuery["id"]."&fileKey=".$targetPostQuery["file_key"]."&collaboration_id=".$collaborationQuery["id"]."&collaboration_key=".$collaborationQuery["collaboration_key"]."&request_id=".$request["id"]."\">Download</a>
                                            <label onclick=\"location.href='https://www.virustotal.com/gui/home/upload'\" style=\"display:inline-block;margin-left: 10px;margin-top: 10px;\" class=\"button\">Scan File</label>
                                        </div>");
                                
                                if(file_exists("./collaborationPostFiles/" . $targetPostQuery["filename"])) {
                                    
                                    $contentType = mime_content_type("./collaborationPostFiles/".$targetPostQuery['filename']);

                                    if ($contentType == "video/mp4" || $contentType == "audio/mpeg" || $contentType == "audio/ogg" || $contentType == "audio/wav" || $contentType == "audio/x-wav" ) { // File is an .mp3 file, embed in music player.
                                        print("
                                                <div class=\"musicPlayerWrap\" style=\"width:80%;\">
                                                    <div id=\"musicWrapClickDetect\" class=\"musicPlayer$l\" style=\"width:100%; box-sizing: border-box;\">
                                                    
                                                        <audio crossorigin>
                                                            <source src=\"/songshome/collaborationPostFiles/".$targetPostQuery["filename"]."\" type=\"$contentType\">                                
                                                        </audio>    
                                                    </div>                                     
                                                </div>
                                                <br>
                    
                                        ");
                                        $musicPlayerArray[] = "musicPlayer".$l;
                                        $l++;
                                    }
                                    
                                }
                            }

                          
                            if ($targetPostQuery["link"] != NULL) { // Target post contained a link.
                                print("<div class=\"arial\" style=\"width: 80%;\">Link: <a onclick=\"if (!confirm('Visit this link? Consider scanning it beforehand.')) { event.preventDefault();  }\" class=\"link\" href=\"".$targetPostQuery["link"]."\">".$targetPostQuery["link"]."</a><label onclick=\"location.href='https://www.virustotal.com/gui/home/url'\" style=\"display:inline-block;margin-left: 10px;margin-top: 10px;\" class=\"button\">Scan Link</label></div><br>");
                            }

                        }

                        if ($request["open"] == 1) {  // Collaboration request is open, allow submissions.               
                            // Allow file submissions to this request from the requested users.
                            print("<p class=\"postsTextDivider arial\" style=\"border-bottom:calc(0.5px + 0.1vw) solid rgb(79, 189, 73);color: rgb(79, 189, 73);margin-top:0;margin-bottom:0;\"><br><span style=\"padding-left: calc(9px + 1.8vw);\">Upload Your Submission</span></p>");

                            print("  
                                <div style=\"text-align:center;\">
                                    <input form=\"requestSubmissionForm$i\" placeholder=\"Firstname\" name=\"requestFirstname\"  id=\"list1_requestFirstname$i\" class=\"audioLabelUpload\" maxlength=\"20\" style=\"font-size:calc(5px + 2vw);padding: calc(5px + .1vw) calc(5px + .1vw);margin-left:auto;margin-right:auto;text-align:center;display:inline-block;width:34.8%;\" type=\"text\">
                                    <input form=\"requestSubmissionForm$i\" placeholder=\"Lastname\" name=\"requestLastname\"  id=\"list1_requestLastname$i\" class=\"audioLabelUpload\" maxlength=\"30\" style=\"font-size:calc(5px + 2vw);padding: calc(5px + .1vw) calc(5px + .1vw);margin-left:auto;margin-right:auto;text-align:center;display:inline-block;width:34.8%;\" type=\"text\">
                                </div>
                                <input form=\"requestSubmissionForm$i\" placeholder=\"Email\" name=\"requestEmail\"  id=\"list1_requestEmail$i\" class=\"audioLabelUpload\" maxlength=\"70\" style=\"font-size:calc(5px + 2vw);padding: calc(5px + .1vw) calc(5px + .1vw);margin-left:auto;margin-right:auto;text-align:center;display:block;width:70%;\" type=\"text\"> 
                                <div style=\"text-align:center;\">                      
                                    <form id=\"requestSubmissionForm$i\" enctype=\"multipart/form-data\" autocomplete=\"off\"  target=\"_self\" action=\"/songshome/receiverequestsubmissionlink.php\" method=\"post\">   
                                        <input type=\"hidden\" name=\"collaborationId\" value=\"".$collaborationQuery["id"]."\">
                                        <input type=\"hidden\" name=\"collaborationKey\" value=\"".$collaborationQuery["collaboration_key"]."\">
                                        <input type=\"hidden\" name=\"collaborationRequestId\" value=\"".$request["id"]."\">
                                    </form>
                                    <label onclick=\"if(checkIfEmptyNoAlert('list1_requestFirstname$i') && checkIfEmptyNoAlert('list1_requestLastname$i') && checkIfEmptyNoAlert('list1_requestEmail$i')) {} else {alert('Please provide a firstname, lastname, and email to receive a submission link.'); return false;} if (checkChars('list1_requestFirstname$i') || checkChars('list1_requestLastname$i') || checkChars('list1_requestEmail$i')) { alert(' Characters: \\\ \' < > are not allowed.'); return false;} else if ( checkEmail('list1_requestEmail$i') ) {alert('The email entered is not valid.'); return false;} else {this.style.display = 'none'; document.getElementById('requestSubmissionForm$i').submit();}\" class=\"button\" style=\"margin-left:auto;margin-right:auto;background-color: rgb(79, 189, 73);max-width: 60%;display: inline-table;z-index:1;font-size:calc(17px + .2vw);margin-top:20px;margin-bottom:10px; cursor: pointer;margin-left:auto;margin-right:auto;\">
                                        <span style=\"z-index:1;\">Receive Upload Link</span>
                                    </label>
                                </div>
                            ");    

                        }
                        else {
                            print("<p class=\"postsTextDivider arial\" style=\"border-bottom:calc(0.5px + 0.1vw) solid black;color: black;margin-top:0;margin-bottom:0;\"><br><span style=\"padding-left: calc(9px + 1.8vw);\">Request Closed</span></p>");
                        }


                        print("</h2>");

                        $i++;
                    } 

                    print("<div id=\"endPage\" style=\"display:block;width:100%;padding-top:100px;\"></div>");

                }

                require("navmenuend.php");
                
            }
            else {
                echo "<br>Error: Collaboration does not exist.";
            }
           
        }
    }
    ?>
</body>

<script>

function checkEmail(id) 
{
    var x = document.getElementById(id).value;
    if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(x)) {
        return false;
    }
    return true;
}

function checkIfEmpty(id, text) {
    var x = document.getElementById(id).value;

    if (x.length == "") {
        alert(text);
        return false;
    } 

    return true;
}

function checkIfEmptyNoAlert(id) {
    var x = document.getElementById(id).value;

    if (x.length == "") {
        return false;
    } 

    return true;
}


function checkChars(id) {
    var chr = document.getElementById(id).value;

    if (chr.includes('\\') || chr.includes('\'') || chr.includes('<') || chr.includes('>')){
       
        return true;
    }       
    return false;
}

function showAll() {
  var dots = document.getElementById("dots");
  var moreText = document.getElementById("more");
  var btnText = document.getElementById("showAllButton");

  if (dots.style.display === "none") {
    dots.style.display = "inline";
    btnText.innerHTML = "Show All"; 
    moreText.style.display = "none";
  } else {
    dots.style.display = "none";
    btnText.innerHTML = "Hide"; 
    moreText.style.display = "inline";
  }
}


var musicPlayerArray =<?php echo json_encode($musicPlayerArray); ?>;
document.addEventListener('DOMContentLoaded', function() {
    for(var i=0; i<musicPlayerArray.length; i++){    
        new GreenAudioPlayer('.' + musicPlayerArray[i], { stopOthersOnPlay: true });           
    }        
});

</script>


</html>