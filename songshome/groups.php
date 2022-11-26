<!DOCTYPE html>
<html>

<head>
<?php include('./includes/defaultheadtags.html');?>
<title>Music Collab. Environ. - Groups</title>
</head>
<body>


    

    <?php
            
        require("userDetailsChangedCheck.php"); //START SESSION
        
    if (isset($_SESSION['member_id']) && $access_level >= 10){

        require("navmenustart.php");
        require("functions.php");
        require('includes/db.php');
        include('includes/scrollbuttons.php');

        
      
        include('temp_signinbutton.php'); // REMOVE LATER

        $groupsQuery = $db->query("SELECT * FROM `groups` WHERE `relationalGroupId` IS NULL")->fetchAll();
        $directories = array();
        
            print("<div style=\"text-align:center;\"><h2 class=\"title\" style=\"display:inline-block;\">Groups<button style=\"float:right; box-sizing: border-box; -moz-box-sizing: border-box; -webkit-box-sizing: border-box; margin-bottom:5px;  padding: calc(5px + .1vw) calc(7px + .3vw); font-size: calc(8px + .8vw);vertical-align:bottom;\" class=\"button\" onclick=\"hideShowCheckboxes(); hideShow('deletebutton'); hideShow('editbutton');\">List View</button></h2></div>");
            print("<div class=\"grid\">");

            $count = 0;
            foreach ($groupsQuery as $group)
            {

                    $group = $groupsQuery[$count]['groupName'];
                    $groupId = $groupsQuery[$count]['id'];
                    $groupKey = $groupsQuery[$count]['groupKey'];
                
                    $directories[] = $count;

                    //PDO Query Statement
                    $stmt = $db->prepare('SELECT groupPicture FROM groups WHERE id = :groupid');
                    $stmt->bindParam('groupid', $groupId);
                    $stmt->execute();
                    $groupPictureQuery = $stmt->fetch();
                
                    $pictureName =  $groupPictureQuery['groupPicture'];          

                    if ($pictureName != "") {
                        $pictureSrc = "/songshome/groupPictures/thumbnail_$pictureName";  
                            
                    }
                    else {
                        $pictureSrc = "/songshome/imageGroupDefault.jpg";
                    }

                    print("
                    
                    <div style=\"border: none; text-align: center; vertical-align: top;\">
                        <div style=\"margin-left:auto; margin-right:auto; max-width:300px;\">
                            <form id=\"deleteForm\" onsubmit=\"validateCheckboxes();\" target=\"_self\" action=\"/songshome/deleteGroup.php\" method=\"post\">              
                                <div style=\"position:relative;\">
                                    <label id=\"label$count\" style=\"display:none;\" class=\"container\">
                                        <input onclick=\"onToggle('$count');\" form=\"deleteForm\" id=\"$count\" value=\"$groupId\" name=\"allCheckboxes[]\" type=\"checkbox\"><span class=\"checkmark\"></span>                    
                                    </label>
                                    <input form=\"deleteForm\" style=\"display:none\" id=\"deleteKeys$count\" value=\"$groupKey\" name=\"allCheckboxesKeys[]\" type=\"checkbox\">
                                </div>
                            </form>
                            
                        
                            <img class=\"groupBorderBig\" style=\"cursor:pointer;\" onclick=\"location.href='/songshome/group/$groupId/$groupKey'\" src=\"$pictureSrc\" alt=\"$group\" onerror=\"this.src='/songshome/imageGroupDefault.jpg';\"/>                                        
                            <a class=\"labels\" onclick=\"location.href='/songshome/group/$groupId/$groupKey'\" style=\"cursor: pointer;text-decoration: none; padding-top:calc(8px + .35vw); margin-bottom:calc(30px + .3vw); display: block; margin-left: auto;margin-right: auto;max-width:25ch;\">$group</a>
                        
                        
                                <input form=\"editForm\" style=\"display:none\" id=\"edit$count\" value=\"$groupId\" name=\"editCheckboxes[]\" type=\"checkbox\">
                                <input form=\"editForm\" style=\"display:none\" id=\"editKeys$count\" value=\"$groupKey\" name=\"editCheckboxesKeys[]\" type=\"checkbox\">
                            
                        </div>        
                    </div>
                    
                    ");
                
                
            

            $count++;
            
            }

    
        print(" </div>

    <table width=\"100%\">
        
        <tr>

        <td valign=\"center\" width=\"33.33%\" style=\"vertical-align: top;word-wrap: break-word;margin-left:auto;margin-right:auto;text-align:center;\">  
            <div class=\"popup\" style=\"margin-left: auto; margin-right: auto;\" >

                <span class=\"popuptext\" style=\"\" id=\"emptyGroup\">No Groups Were Selected</span>
                <input name=\"deleteFormButton\" form=\"deleteForm\" style=\"white-space: normal;display:none;background-color:red;\" id=\"deletebutton\" class=\"button\" type=\"submit\" value=\"Delete Groups\">

            </div>
        </td>

        <td valign=\"center\" width=\"33.33%\" style=\"vertical-align: top;margin-left:auto;margin-right:auto;text-align:center;\">
            <div style=\"text-align:center;\">

                <button style=\"margin-bottom:10px;\"class=\"button\" onclick=\"hideShowCheckboxes(); hideShow('deletebutton'); hideShow('editbutton');\">Edit Groups</button>&nbsp;
                <form style=\"display: inline;\" id=\"addGroupForm\" target=\"_self\" action=\"/songshome/creategroup.php\" method=\"post\">
                    <input type=\"hidden\" value=\"\" name=\"relationalGroupIdSubmit\"/>
                    <input type=\"hidden\" value=\"\" name=\"relationalGroupKeySubmit\"/>   
                </form>
                <button onclick=\"document.getElementById('addGroupForm').submit();\" id=\"createButton\" class=\"button\">Add Group</button>
                
            </div>
        </td>

        <form id=\"editForm\" onsubmit=\"validateEditCheckboxes();\" target=\"_self\" action=\"/songshome/editgroup.php\" method=\"post\"></form>
        <td valign=\"center\" width=\"33.33%\"style=\"vertical-align: top;margin-left:auto;margin-right:auto;text-align:center;\">
            <div class=\"popup\" style=\"margin-left: auto; margin-right: auto;\" >
                <span class=\"popuptext\" style=\"\" id=\"editGroup\">Please Select One Group</span>
                <input form=\"editForm\" style=\"white-space: normal;display:none;background-color:#4fbd49;\" name=\"editbutton\" id=\"editbutton\" class=\"button\" type=\"submit\" value=\"Edit Group Details\">
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
    }
    ?>

</div>

</body>

<?php
if (isset($_SESSION['member_id']) && $access_level >= 10){
print("
    <script>


    function onToggle(idd){
        var x = document.getElementById(idd);
        if (x.checked == true) {
        document.getElementById(\"edit\"+ idd).checked = true;
        document.getElementById(\"editKeys\"+ idd).checked = true;     
        document.getElementById(\"deleteKeys\"+ idd).checked = true;
        
        } else {
        document.getElementById(\"edit\"+ idd).checked = false;
        document.getElementById(\"editKeys\"+ idd).checked = false;    
        document.getElementById(\"deleteKeys\"+ idd).checked = false;
        }
    }

    function hideShow(idd)
    {
        var y = document.getElementById(idd);
        if (y.style.display === \"none\") {
                y.style.display = \"inline\";
            } else {
            y.style.display = \"none\";
            }
    }

    function hideShowCheckboxes() {

        var jArray ="); echo json_encode($directories); 
        print("
        for(var i=0; i<jArray.length; i++){        
            var x = document.getElementById(\"label\" + i);
            var z = document.getElementById(i);
            var y = document.getElementById(\"edit\" + i);
            var l = document.getElementById(\"editKeys\" + i);
            var p = document.getElementById(\"deleteKeys\" + i);
        
        
            if (x.style.display === \"none\") {
                x.style.display = \"block\";
            } 
            else {
                z.checked = false;
                y.checked = false;
                l.checked = false;
                p.checked = false;
                x.style.display = \"none\";
            }
        }

    }


    function validateCheckboxes() {
        var jArray ="); echo json_encode($directories); 
        
        
        print("
        if(checkmarkDirectory().length === 0)
        { 
        showPopup('emptyGroup');
        event.preventDefault();
        return false;
        }


        return true;
    }

    function validateEditCheckboxes() {
        var jArray = "); echo json_encode($directories);
        
        
        print("
        if(checkmarkDirectory().length === 1)
        { 
            return true;
        
        }
        showPopup('editGroup');
        event.preventDefault();
        return false;

        
    }

    function checkmarkDirectory() {
        var jArray = "); echo json_encode($directories);

        print("
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



    </script>");
}
?>
</html>