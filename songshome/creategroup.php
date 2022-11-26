<!DOCTYPE html>
<html>
<head>
<title>Music Collab. Environ. - Create Group</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel ="stylesheet" type="text/css" href="/songshome/site.css" >
<link rel="icon" href="/songshome/imageNotFound.jpg" type="image/ico">
</head>
<body>
    <!-- style="font: 400 calc(45px + 4vw)/.01px 'Arizonia'; text-align: center;" -->
        <!-- onkeydown="return event.key != 'Enter';" -->

<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

   
    if (isset($_POST["relationalGroupIdSubmit"]) && isset($_POST["relationalGroupKeySubmit"]) ) {

        // Security check
        require("userDetailsChangedCheck.php");
        require("retrieveUserAccessLevel.php");
        $access_level = checkAccesstoResource($_POST["relationalGroupIdSubmit"], $db); // Retrieve proper access level for current user.
        if (isset($_SESSION['member_id']) && $access_level >= 5) { // Limit group creation to group leaders.

            require('./includes/db.php');

            require("navmenustart.php");
            require("functions.php");
            

            $relationalGroupIdSubmit = $_POST['relationalGroupIdSubmit'];
            $relationalGroupKeySubmit = $_POST['relationalGroupKeySubmit'];
        
            //GROUP KEY CHECK
            if ($relationalGroupIdSubmit != "" && $relationalGroupKeySubmit != ""){
                $stmt = $db->prepare("SELECT * FROM `groups` WHERE `id` = :relationalgroupidsubmit AND `groupKey` = :relationalgroupkeysubmit");
                $stmt->bindParam('relationalgroupidsubmit', $relationalGroupIdSubmit);
                $stmt->bindParam('relationalgroupkeysubmit', $relationalGroupKeySubmit);
                $stmt->execute();
                $groupQuery = $stmt->fetchAll();
            }
            else if ($relationalGroupIdSubmit == "" && $relationalGroupKeySubmit == "") {
                $groupQuery[0] = array(1);
            }
            else {
                $groupQuery = [];
            }
            
        
            if (sizeof($groupQuery) == 1){

                    if(isset($_POST["relationalGroupIdSubmit"]) && $_POST["relationalGroupIdSubmit"] != "" && isset($_POST["relationalGroupKeySubmit"]) && $_POST["relationalGroupKeySubmit"] != "") { 
                        
                        require('./includes/db.php');

                        $relationalGroupId = $_POST["relationalGroupIdSubmit"];
                        $locationArray = array();

                        $stmt = $db->prepare('SELECT * FROM groups WHERE id = :relationalgroupid');
                        $stmt->bindParam('relationalgroupid', $relationalGroupId);
                        $stmt->execute();
                        $locationQuery = $stmt->fetch();

                        $relationalGroupId = $locationQuery["id"];
                        $relationalGroupKey = $locationQuery["groupKey"];
                
                        do {
                            $locationArray[] = $locationQuery["groupName"];
                            $locationId[] = $locationQuery["id"];
                            $locationKey[] = $locationQuery["groupKey"];
                            $testGroupId = $locationQuery["relationalGroupId"];
                            $stmt = $db->prepare('SELECT * FROM groups WHERE id = :testgroupid');
                            $stmt->bindParam('testgroupid', $testGroupId);
                            $stmt->execute();
                            $locationQuery = $stmt->fetch();
                        } while ($testGroupId != null);
                    }

                    
                    print("<br>
                        <h2 class=\"title\">Create a Group In ");
                    
                    if(isset($_POST["relationalGroupIdSubmit"]) && $_POST["relationalGroupIdSubmit"] != ""  && isset($_POST["relationalGroupKeySubmit"]) && $_POST["relationalGroupKeySubmit"] != "") { 
                        $size = sizeof($locationArray) - 1;
                        for ($o = $size; $o >=0 ; $o--){
                            $location = $locationId[$o];
                            $key = $locationKey[$o];
                            if ($o == $size){
                                print("<span class=\"linkRedirect\"><a href=\"/songshome/group/$location/$key\">".$locationArray[$o]."</a></span>");
                            }
                            else{
                                print(" >> "."<span class=\"linkRedirect\"><a href=\"/songshome/group/$location/$key\">".$locationArray[$o]."</a></span>");
                            }
                        }
                    }
                    else {
                        print("<span class=\"linkRedirect\"><a href=\"/songshome/groups.php\">Groups</a></span>");
                    }
                    
                    print("</h2>
                    
                    <div style=\"text-align:center; \">
                    <form enctype=\"multipart/form-data\" id=\"uploadForm\" onsubmit=\"checkIfEmpty('textInput');checkIfUploadReady();setTogglesValues();\" autocomplete=\"off\"  target=\"_self\" action=\"/songshome/creategroupprocess.php\" method=\"post\">
                    <div style=\"width:300px;margin-left:auto;margin-right:auto;\">
                        <div class=\"imagesWrapUpload\" style=\"padding-top:0;padding-bottom:0;\" onmouseover=\"activateDropArea(0, 0); checkOneRemoveButton(0, 0);\">
                            <label class=\"imgDropArea\" id=\"list0_dropArea0\" for=\"list0_itemInputList0\">
                                <label id=\"list0_removeButton0\" onclick=\"removeInput(0, 0, './imageGroupDefault.jpg'); event.preventDefault();\" class=\"removeGroupImgButton\"> 
                                    <span>X</span>                  
                                </label>
                                <img class=\"placeholderImage\" id=\"list0_uploadPreview0\" src=\"/songshome/imageGroupDefault.jpg\" alt=\"Selected Image\" /> 
                                <div class=\"popup\" style=\"margin-left: auto; margin-right: auto; width:100%;\">
                                    <span class=\"popuptext\" style=\"width: 180px;bottom:calc(30px + .4vw);left:19%;\" id=\"list0_invalidFilePopup0\">File Type Not Supported</span>
                                    <span class=\"popuptext\" style=\"width: 180px;bottom:calc(30px + .4vw);left:19%;\" id=\"list0_fileTooLargePopup0\">File Is Too Large</span>
                                </div>
                            </label>
                        </div>
                    </div>


                
                    <div class=\"popup\" style=\"margin-left: auto; margin-right: auto;\" >
                        <span class=\"popuptext\" style=\"width: 240px;margin-left: -120px;left: 50%;\" id=\"emptyGroup\">Please Enter a Group Name</span>
                        <span class=\"popuptext\" style=\"width: 150px;margin-left: -75px;left: 50%;\" id=\"charError\">Invalid Characters<br>\\ ' < ></span>
                        <span class=\"popuptext\" style=\"width: 180px;margin-left: -90px;left: 50%;\" id=\"nameLong\">Length Limit Reached</span>
                        <input placeholder=\"Group Title\" name=\"groupTitle\"  id=\"textInput\" class=\"textbox\" maxlength=\"210\" style=\"width:300px;font-size:25px;text-align:center;box-sizing:border-box;\" form=\"uploadForm\" type=\"text\">

                        <div class=\"borderContainer\" style=\"margin-top:calc(5px + .1vw);padding-top:calc(10px + .4vw);padding-bottom:calc(10px + .4vw);width:300px;box-sizing:border-box;\">
                            <p class=\"normalText\" style=\"display:block;text-align:center;padding-left:calc(14px + 1.1vw);padding-right:calc(14px + 1.1vw);font-size: calc(14px + .2vw);line-height:25px;\">Hide from list (Link Only Acccess)?
                                <label style=\"display:inline-block;\" class=\"switch\">
                                    <input id=\"linkAccessOnlyToggle\" type=\"checkbox\">        
                                    <span class=\"sliderToggle round\"></span>
                                </label>
                                <input id=\"linkAccessOnly\" type=\"hidden\" name=\"linkAccessOnly[]\" form=\"uploadForm\">
                            </p>
                            
                        </div>
                    </div>
                    
                    <br>

                    <label class=\"button\">

                        <input id=\"list0_itemInputList0\" name=\"groupImage\"  type=\"file\" accept=\"image/*\" onchange=\"if(!checkFileType(this.files, 0, 0) ){showPopup('list0_invalidFilePopup0'); document.getElementById('list0_uploadPreview0').src = './imageGroupDefault.jpg'; return;} compressImage(0, 0, 2000, './imageGroupDefault.jpg'); document.getElementById('list0_uploadPreview0').src = window.URL.createObjectURL(this.files[this.files.length - 1]); checkOneRemoveButton(0, 0);\">
                        <span>Upload Image</span>
                        
                    </label>

                    &nbsp;
                        
                        <input form=\"uploadForm\" style=\"background-color:#4fbd49; margin-top:20px;\" id=\"uploadFormSubmit\" class=\"button\" type=\"submit\" name=\"submit\" value=\"Create Group\">
                    <br><br><br>");
                    if(isset($_POST["relationalGroupIdSubmit"]) && $_POST["relationalGroupIdSubmit"] != "" && isset($_POST["relationalGroupKeySubmit"]) && $_POST["relationalGroupKeySubmit"] != "") { 
                        print("<input form=\"uploadForm\" type=\"hidden\" name=\"relationalGroupIdSubmit\" value=\"$relationalGroupId\">
                            <input form=\"uploadForm\" type=\"hidden\" name=\"relationalGroupKeySubmit\" value=\"$relationalGroupKey\">
                        ");
                    }

                    print("
                    </form>
                    </div>");

                    require("navmenuend.php");

            }
            else{
                echo "POST Error: Database Error.";
            }
    
        }
        else
        {
            echo "Error: You do not have access to this resource.";
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

</body>
<script src="/songshome/image-conversion-master/build/conversion.js"></script>
<script type="text/javascript" src="/songshome/includes/functionsScript.js"></script>
<script type="text/javascript" src="/songshome/includes/uploadScript.js"></script>
<script type="text/javascript" src="/songshome/includes/showpopupScript.js"></script>

<script>

document.getElementById('list0_removeButton0').style.visibility = "hidden";

//Makes sure group name field is not empty at submit
function checkIfEmpty(id) {
    var x = document.getElementById(id).value;
    if (x.length == "") {
    showPopup('emptyGroup');
    event.preventDefault();
    } 
    
}

// Check input function for Android phones
var input_field = document.getElementById('textInput');
var bannedChar = "\\'<>";

input_field.addEventListener('textInput', function(e) {
    // e.data will be the 1:1 input you done
    var char = e.data; // In our example = "a"
    
    // Stop processing if "a" is pressed
    if (bannedChar.indexOf(char) >= 0){
        showPopup('charError');
        e.preventDefault();
        return false;
    }       

    if(document.getElementById("textInput").value.length >= 210){
        showPopup('nameLong');
        e.preventDefault();
        return false;
    }  
    
});


// Check input function for Windows
document.getElementById("textInput").onkeypress = function(e) {
    if(document.getElementById("textInput").value.length >= 210){
        showPopup('nameLong');
        return false;
    }
    var chr = String.fromCharCode(e.which);
 
    if (bannedChar.indexOf(chr) >= 0){
        showPopup('charError');
        return false;
    }       
        
}


// Show popup if paste is too long
document.getElementById("textInput").onpaste = function(e) {
    if(document.getElementById("textInput").value.length >= 210){
        showPopup('nameLong');
        return false;
    }   
      
    
}

var togglesList = ['linkAccessOnly'];
function setTogglesValues(){

    function loopEachItem(item, whichItem){
        if (document.getElementById(item + 'Toggle').checked == true){
            document.getElementById(item).value = 1;
           
        }
        else {
            document.getElementById(item).value = 0;
        }
    }
    
    togglesList.forEach(loopEachItem);
}

</script>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script type="text/javascript">
    //Disable Enter
    // var $myForm = $("#uploadForm");
    // $myForm.submit(function(){
    //     $myForm.submit(function(){
    //         return false;
    //     });
    // });
    
</script>
</html>