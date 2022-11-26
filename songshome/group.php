<!DOCTYPE html>
<html>

<head>
<?php include('./includes/defaultheadtags.html');

?>
<title>Music Collab. Environ.</title>
</head>
<body>
    <?php
    require("userDetailsChangedCheck.php");
    require("retrieveUserAccessLevel.php");

    if(isset($_GET['groupSubmit']) && isset($_GET['groupKeySubmit'])) {
        
        require('./includes/db.php');

        $requestedGroup = $_GET['groupSubmit'];
        $requestedGroupKey = $_GET['groupKeySubmit'];
   
        //GROUP KEY CHECK
        $stmt = $db->prepare("SELECT * FROM `groups` WHERE `id` = :requestedgroup AND `groupKey` = :requestedgroupkey");
        $stmt->bindParam('requestedgroup', $requestedGroup);
        $stmt->bindParam('requestedgroupkey', $requestedGroupKey);
        $stmt->execute();
        $groupLookup = $stmt->fetchAll();

        if (sizeof($groupLookup) != 0){
            

            // Navigation menu & logo setup.
            $parentGroupNavId = retrieveParentGroupId($groupLookup[0]['id'], $db);
            $parentGroupNavKey = retrieveParentGroupKey($groupLookup[0]['id'], $db);
            $logoBarLink = "/songshome/group/".$parentGroupNavId."/".$parentGroupNavKey;
            $navigationLinks[] = "<a class=\"navLinks\"  href=\"/songshome/group/".$parentGroupNavId."/".$parentGroupNavKey."\">Home</a>";
            $navigationLinks[] = "<a class=\"navLinks\"  href=\"/songshome/signin.php\">Sign In</a>";
            require("navmenustart.php");

            require("functions.php");
            include('./includes/scrollbuttons.php');

            include('temp_signinbutton.php'); // REMOVE LATER

            $targetGroupId = $groupLookup[0]['id'];
            $targetGroupKey = $groupLookup[0]['groupKey'];

            $access_level = checkAccesstoResource($targetGroupId, $db); // Retrieve proper access level for current user.

            // Hide groups for visitors if link only access is set.
            if (isset($_SESSION['member_id']) && $access_level >= 1) {
                $stmt = $db->prepare("SELECT * FROM `groups` WHERE `relationalGroupId` = :targetgroupid");
            }
            else
            {
                $stmt = $db->prepare("SELECT * FROM `groups` WHERE `relationalGroupId` = :targetgroupid AND `linkAccessOnly` = 0");
            }
            $stmt->bindParam('targetgroupid', $targetGroupId);
            $stmt->execute();
            $groupsQuery = $stmt->fetchAll();

            //Navigation Bar Code
            if ($groupLookup[0]['locationIndex'] != "") {
                
                $groupLookup[0]['locationIndex'] = substr($groupLookup[0]['locationIndex'], 0, -1);
                // $targetGroupIndexQuery = "SELECT FROM `table` WHERE `ID` IN (".implode(',',$array).")";
                $targetGroupIndex = $groupLookup[0]['locationIndex'].",".$targetGroupId;
            }
            else {
                $targetGroupIndex = $targetGroupId;
            }

            $stmt = $db->prepare("SELECT `id`,`groupName`,`groupKey` FROM `groups` WHERE `id` IN ($targetGroupIndex) ORDER BY FIELD (id, $targetGroupIndex)");
            // $stmt = $db->prepare("SELECT * FROM `groups` WHERE `id` IN (:targetgroupindex)");
            $stmt->execute();
            $targetGroupIndexQuery = $stmt->fetchAll();

            $count = 0;
            $directories = array();
            $songDirectories = array();

            

            print("<br>
                   <h2 class=\"title\">");
            

            //This code displays where the requested group is.
            $z = 0;
            foreach($targetGroupIndexQuery as $targetGroupValue) {
                $location = $targetGroupValue['id'];
                $name = $targetGroupValue['groupName'];
                $key = $targetGroupValue['groupKey'];
        
                $groupNameCutTop = $name;
                if (mb_strlen($name) > 71){
                    $groupNameCutTop = substr($name, 0, 70) . '...';
                }
                
                if (mb_strlen($name) > 20){
                        $name = substr($name, 0, 20).'...';       
                }
            
                if ($z == 0){               
                    print("<span class=\"linkRedirect\"><a href=\"/songshome/group/$location/$key\">".$groupNameCutTop."</a></span>");
                }
                else{
                    print(" > <span class=\"linkRedirect\"><a href=\"/songshome/group/$location/$key\">".$name."</a></span>");
                }
                // displayVar($targetGroupValue);
                $z = 1;
            }

            // print(" Group ID: $targetGroupId</h2>");
            print(" Groups");

            if (isset($_SESSION['member_id']) && $access_level >= 5) {
                print("<span>
                            <form id=\"editThisGroupForm\" style=\"display:inline-block;\" enctype=\"multipart/form-data\" action=\"/songshome/editgroup.php\" method=\"post\">     
                                <input form=\"editThisGroupForm\" type=\"hidden\" value=\"".$groupLookup[0]["id"]."\" accept=\"text\" name=\"editCheckboxes[]\">
                                <input form=\"editThisGroupForm\" type=\"hidden\" value=\"".$groupLookup[0]["groupKey"]."\" accept=\"text\" name=\"editCheckboxesKeys[]\"> 
                                <input form=\"editThisGroupForm\" name=\"editbutton\" type=\"hidden\" value=\"Edit This Group\">
                                <input form=\"editThisGroupForm\" style=\"background-color: black; margin-top:10px; margin-left:10px;margin-bottom:10px;\" class=\"button\" value=\"Edit Group\" type=\"submit\">
                            </form>
                        </span>");
            }

            print("</h2>");

            if (sizeof($groupsQuery) != 0){
                print("
                <div class=\"grid\" style=\"grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); grid-template-rows: repeat(auto-fit, minmax(150px, 1fr));\">");
                


                
                foreach ($groupsQuery as $group)
                {
                  
                    $groupParent = $groupsQuery[$count]['relationalGroupId'];
                    $directories[] = $count;
                    $group = $groupsQuery[$count]['id'];
                    $groupKey = $groupsQuery[$count]['groupKey'];
                    $groupName = $groupsQuery[$count]['groupName'];
                    $pictureName = $groupsQuery[$count]['groupPicture'];

                    if ($pictureName != "") {
                        $pictureSrc = "/songshome/groupPictures/thumbnail_$pictureName";            
                    }
                    else {
                        $pictureSrc = "/songshome/imageGroupDefault.jpg";
                    }

                    print("
                    
                    <div style=\"border: none;text-align: center; vertical-align: top;\">
                        <div style=\"margin-left:auto; margin-right:auto; max-width:150px;\">
                        <form id=\"deleteForm\" onsubmit=\"validateCheckboxesGroups();\" target=\"_self\" action=\"/songshome/deleteGroup.php\" method=\"post\">                 
                            <div style=\"position:relative;\">
                                <label id=\"label$count\" style=\"display:none;\" class=\"container\">
                                    <input onclick=\"onToggle('$count');\" form=\"deleteForm\" id=\"$count\" value=\"$group\" name=\"allCheckboxes[]\" type=\"checkbox\"><span class=\"checkmark\"></span>
                                </label>
                                <input form=\"deleteForm\" style=\"display:none\" id=\"deleteKeys$count\" value=\"$groupKey\" name=\"allCheckboxesKeys[]\" type=\"checkbox\">
                            </div>
                        </form>
                        
                       
                        <img class=\"groupBorderSmall\" style=\"cursor:pointer;\" onclick=\"location.href='/songshome/group/$group/$groupKey'\" src=\"$pictureSrc\" alt=\"$groupName\" onerror=\"this.src='/songshome/imageGroupDefault.jpg';\"/>                          
                        <a class=\"labels\" onclick=\"location.href='/songshome/group/$group/$groupKey'\" style=\"cursor: pointer;text-decoration: none; padding-top:calc(8px + .35vw); margin-bottom:calc(30px + .3vw); display: block; margin-left: auto;margin-right: auto;max-width:15ch;\">$groupName</a>

                        

                            <input form=\"editForm\" style=\"display:none\" id=\"edit$count\" value=\"$group\" name=\"editCheckboxes[]\" type=\"checkbox\">
                            <input form=\"editForm\" style=\"display:none\" id=\"editKeys$count\" value=\"$groupKey\" name=\"editCheckboxesKeys[]\" type=\"checkbox\">

                        </div>  
                            
                            
                        </div>
                        
                        ");

                $count++;
        
                }

                print("</div>");
            }

            print(" 

            <table width=\"100%\">
            
            <tr>

            <td valign=\"center\" width=\"33.33%\" style=\"vertical-align: top;word-wrap: break-word;margin-left:auto;margin-right:auto;text-align:center;\">  

                <div class=\"popup\" style=\"margin-left: auto; margin-right: auto;\" >
                        <span class=\"popuptext\" style=\"\" id=\"emptyGroup\">No Groups Were Selected</span>
                <input name=\"deleteFormButton\" form=\"deleteForm\" style=\"white-space: normal;display:none;background-color:red;\" id=\"deleteButtonGroups\" class=\"button\" type=\"submit\" value=\"Delete Groups\">
                </div>
            </td>

            <td valign=\"center\" width=\"33.33%\" style=\"vertical-align: top;margin-left:auto;margin-right:auto;text-align:center;\">
                <div style=\"text-align:center;\">");
                if (isset($_SESSION['member_id']) && $access_level >= 5) {
                    print("
                        <button style=\"margin-bottom:10px;\" class=\"button\" onclick=\"hideShowCheckboxesGroups(); hideShow('deleteButtonGroups'); hideShow('editbutton');\">Edit Groups</button>&nbsp;

                        <button  onclick=\"document.getElementById('addGroupForm').submit();\" id=\"createButton\" class=\"button\">Add Group</button>");
                }

                print("
                <form style=\"display: inline;\" id=\"addGroupForm\" target=\"_self\" action=\"/songshome/creategroup.php\" method=\"post\">
                    <input type=\"hidden\" value=\"$targetGroupId\" name=\"relationalGroupIdSubmit\"/>
                    <input type=\"hidden\" value=\"$targetGroupKey\" name=\"relationalGroupKeySubmit\"/>   
                </form>
                </div>
            </td>

            <form id=\"editForm\" onsubmit=\"validateEditCheckboxesGroups();\" target=\"_self\" action=\"/songshome/editgroup.php\" method=\"post\"></form>

            <td valign=\"center\" width=\"33.33%\"style=\"vertical-align: top;margin-left:auto;margin-right:auto;text-align:center;\">
                <div class=\"popup\" style=\"margin-left: auto; margin-right: auto;\" >
                        <span class=\"popuptext\" style=\"\" id=\"editGroup\">Please Select One Group</span>
                <input form=\"editForm\" style=\"white-space: normal;display:none;background-color:#4fbd49;\" name=\"editbutton\" id=\"editbutton\" class=\"button\" type=\"submit\" value=\"Edit Group Details\">
                </div>
            </td>
            
            </tr>
            </table>
            ");
            
            //This code displays the songs in the requested group.
            $groupNameGlobal =  $groupLookup[0]['groupName'];

            // Hide songs for visitors if link only access is set.
            if (isset($_SESSION['member_id']) && $access_level >= 1) {
                $stmt = $db->prepare("SELECT * FROM `songs` WHERE `groupId` = :targetgroupid ORDER BY `id` ASC");
            }
            else
            {
                $stmt = $db->prepare("SELECT * FROM `songs` WHERE `groupId` = :targetgroupid AND `linkAccessOnly` = 0 ORDER BY `id` ASC");
            }
            $stmt->bindParam('targetgroupid', $targetGroupId);
            $stmt->execute();
            $songArray = $stmt->fetchAll();

            // $songArray = $db->query("SELECT * FROM `songs` WHERE `groupId` = '$targetGroupId'")->fetchAll();
            
            print("
            <h2 class=\"title\">$groupNameGlobal Releases</h2><tr>
            <div class=\"grid\">
            ");

            $b = 0;
            foreach ($songArray as $songLoop){

                $songNumber = $songArray[$b]['songNumber'];
                $songId = $songArray[$b]['id'];
                $songKey = $songArray[$b]['songKey'];
                $song = $songArray[$b]['title'];
                $songDirectories[] = $count;

                $songPictureArray = $db->query("SELECT `pictureFileName` FROM `pictures` WHERE `songId` = '$songId' ORDER BY `pictureSort` ASC LIMIT 1")->fetchAll(); 
                
                if (sizeof($songPictureArray) != 0) {      
                    $songPictureName = $songPictureArray[0]['pictureFileName'];           
                    if ($songPictureName != "") {
                        $songPictureSrc = "/songshome/pictures/thumbnail_$songPictureName";            
                    }
                    else {
                        $songPictureSrc = "/songshome/imageNotFound.jpg";
                    }
                }
                else {
                    $songPictureName = "imageNotFound.jpg";
                    $songPictureSrc = "/songshome/imageNotFound.jpg";
                }

                print("
                
                <div class=\"item\" style=\"text-align: center; vertical-align: top; margin-left:auto; margin-right:auto; max-width:300px;\">
             
                        <form id=\"deleteSongForm\" onsubmit=\"validateCheckboxes();\" target=\"_self\" action=\"/songshome/deleteSong.php\" method=\"post\">

                            <div style=\"position:relative;\">
                                <label id=\"label$count\" style=\"display:none;\" class=\"container\">
                                    <input onclick=\"onToggle('$count');\" form=\"deleteSongForm\" id=\"$count\" value=\"$songId\" name=\"allCheckboxesSong[]\" type=\"checkbox\"><span class=\"checkmark\"></span>
                                </label>
                                <input form=\"deleteSongForm\" style=\"display:none\" id=\"deleteKeys$count\" value=\"$songKey\" name=\"allCheckboxesSongKeys[]\" type=\"checkbox\">
                            </div>
                        </form>
                        
                        <img class=\"songBorder\" style=\"cursor:pointer;\" onclick=\"location.href='/songshome/view/$songId/$songKey'\" src=\"$songPictureSrc\" alt=\"$song\" onerror=\"this.src='/songshome/imageNotFound.jpg';\"/>                          
                        <a class=\"labels\" onclick=\"location.href='/songshome/view/$songId/$songKey'\" style=\"cursor: pointer;text-decoration: none; padding-top:calc(8px + .35vw); margin-bottom:calc(30px + .3vw); display: block; margin-left: auto;margin-right: auto;max-width:25ch;\">");

                        if ($songNumber != NULL){
                            print("$songNumber - $song");
                        }
                        else{
                            print("$song");
                        }
                        
                    print("
                        </a>
            
                        <input form=\"editSongForm\" style=\"display:none\" id=\"edit$count\" value=\"$songId\" name=\"editCheckboxesSong[]\" type=\"checkbox\">
                        <input form=\"editSongForm\" style=\"display:none\" id=\"editKeys$count\" value=\"$songKey\" name=\"editCheckboxesSongKeys[]\" type=\"checkbox\">
                                    
                </div>
                
                ");
                $count++;
                $b++;
            }


            print(" 
            </div>
            <table width=\"100%\">
    
            <tr>

            <td valign=\"center\" width=\"33.33%\" style=\"vertical-align: top;word-wrap: break-word;margin-left:auto;margin-right:auto;text-align:center;\">  

                <div class=\"popup\" style=\"margin-left: auto; margin-right: auto;\" >
                        <span class=\"popuptext\" style=\"\" id=\"emptySong\">No Releases Were Selected</span>
                <input name=\"deleteSongFormButton\" form=\"deleteSongForm\" style=\"white-space: normal;display:none;background-color:red;\" id=\"deletebutton\" class=\"button\" type=\"submit\" value=\"Delete Releases\">
                </div>
            </td>

            <td valign=\"center\" width=\"33.33%\" style=\"vertical-align: top;margin-left:auto;margin-right:auto;text-align:center;\">
     

                <div style=\"text-align:center;\">");

                    if (isset($_SESSION['member_id']) && $access_level >= 5) {
                        print("
                        <button style=\"margin-bottom:10px;\" class=\"button\" onclick=\"hideShowCheckboxes(); hideShow('deletebutton'); hideShow('editSongButton');\">Edit Releases</button>&nbsp;

                        <button  onclick=\"document.getElementById('addSongForm').submit();\" id=\"createSongButton\" class=\"button\">Add Release</button>");
                    }
                    print("
                    <form style=\"display: inline;\" id=\"addSongForm\" target=\"_self\" action=\"/songshome/pagedetails.php\" method=\"post\">
                        <input type=\"hidden\" value=\"$targetGroupId\" name=\"addSongGroupIdSubmit\"/>
                        <input type=\"hidden\" value=\"$targetGroupKey\" name=\"addSongGroupKeySubmit\"/>      
                    </form>

                </div>
            </td>

            <form id=\"editSongForm\" onsubmit=\"validateEditCheckboxes();\" target=\"_self\" action=\"/songshome/pagedetails.php\" method=\"post\">
                <input type=\"hidden\" value=\"$targetGroupId\" name=\"addSongGroupIdSubmit\"/>
                <input type=\"hidden\" value=\"$targetGroupKey\" name=\"addSongGroupKeySubmit\"/>      
            </form>

            
            <td valign=\"center\" width=\"33.33%\"style=\"vertical-align: top;margin-left:auto;margin-right:auto;text-align:center;\">
                <div class=\"popup\" style=\"margin-left: auto; margin-right: auto;\" >
                        <span class=\"popuptext\" style=\"\" id=\"editSong\">Please Select One Release</span>
                <input form=\"editSongForm\" style=\"white-space: normal;display:none;background-color:#4fbd49;\" name=\"editSongButton\" id=\"editSongButton\" class=\"button\" type=\"submit\" value=\"Edit Release Details\">
                </div>
            </td>
            
            </tr>
            </table>

            
            <br><br><br>
            ");

            require("navmenuend.php");

            
        }
        else
        {
            include("404.php");
            echo "The requested group does not exist.";
            // header("Location: "."./groups.php");
        }
      
    }
    else
    {
        echo "ERROR: Request method was incorrect. ";
        // header("Location: "."./groups.php");
    }

    
?>

</body>

<script>

function onToggle(idd){
    var x = document.getElementById(idd);
    if (x.checked == true) {
      document.getElementById("edit"+ idd).checked = true;
      document.getElementById("editKeys"+ idd).checked = true;
      document.getElementById("deleteKeys"+ idd).checked = true;
    } else {
      document.getElementById("edit"+ idd).checked = false;    
      document.getElementById("editKeys"+ idd).checked = false;
      document.getElementById("deleteKeys"+ idd).checked = false
    }
}

function hideShow(idd)
{
    var y = document.getElementById(idd);
    if (y.style.display === "none") {
            y.style.display = "inline";
        } else {
        y.style.display = "none";
        }
}


// Groups Code

function hideShowCheckboxesGroups() {

    var jArray = <?php echo json_encode($directories); ?>;
    console.log(jArray);
    for(var i=0; i<jArray.length; i++){        
        var x = document.getElementById("label" + i);
        var z = document.getElementById(i);
        var y = document.getElementById("edit" + i);
        var l = document.getElementById("editKeys" + i);
        var p = document.getElementById("deleteKeys" + i);

        if (x.style.display === "none") {
            x.style.display = "block";
        }
        else 
        {
            z.checked = false;
            y.checked = false;
            l.checked = false;
            p.checked = false;
            x.style.display = "none";
        }
    }

}


function validateCheckboxesGroups() {
    var jArray = <?php echo json_encode($directories); ?>;
    
    
    
    if(checkmarkDirectoryGroups().length === 0)
    { 
    showPopup('emptyGroup');
    event.preventDefault();
    return false;
    }


    return true;
}

function validateEditCheckboxesGroups() {
    var jArray = <?php echo json_encode($directories); ?>;
    
    
    
    if(checkmarkDirectoryGroups().length === 1)
    { 
        return true;
    
    }
    showPopup('editGroup');
    event.preventDefault();
    return false;

    
}

function checkmarkDirectoryGroups() {
    var jArray = <?php echo json_encode($directories); ?>;
    var deletearray = [];

    for(var i=0; i<jArray.length; i++)
    {

    var x = document.getElementById(i);

    if (x.checked)
    {
        deletearray.push(i);
    }

    }
    return deletearray;
}








// Songs Code

function hideShowCheckboxes() {

    var jArray = <?php echo json_encode($songDirectories); ?>;

    for(var i=0; i<jArray.length; i++){
        var array = jArray[i].toString();
       
        var x = document.getElementById("label" + array);
        var z = document.getElementById(array);
        var y = document.getElementById("edit" + array);
        var l = document.getElementById("editKeys" + i);
        var p = document.getElementById("deleteKeys" + i);
       
        if (x.style.display === "none") {
            x.style.display = "block";
        } else {
        z.checked = false;
        y.checked = false;
        l.checked = false;
        p.checked = false;
        x.style.display = "none";
        }
    }

}


function validateCheckboxes() {
    var jArray = <?php echo json_encode($songDirectories); ?>;
    
    
    
    if(checkmarkDirectory().length === 0)
    { 
    showPopup('emptySong');
    event.preventDefault();
    return false;
    }


    return true;
}

function validateEditCheckboxes() {
    var jArray = <?php echo json_encode($songDirectories); ?>;
    
    
    
    if(checkmarkDirectory().length === 1)
    { 
        return true;
    
    }
    showPopup('editSong');
    event.preventDefault();
    return false;

    
}

function checkmarkDirectory() {
    var jArray = <?php echo json_encode($songDirectories); ?>;
    var deletearray = [];

    for(var i=0; i<jArray.length; i++)
    {

    var x = document.getElementById(jArray[i]);

    if (x.checked)
    {
        deletearray.push(i);
    }

    }
    return deletearray;
}

</script>



</html>