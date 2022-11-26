<!DOCTYPE html>
<html>
<head>
<title>Music Collab. Environ. - Edit Group</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel ="stylesheet" type="text/css" href="/songshome/site.css" >
<link rel="icon" href="/songshome/imageNotFound.jpg" type="image/ico">
</head>
<body onload="scrollDown()">
    
    <?php
  
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        if(isset($_POST['editbutton']) && isset($_POST['editCheckboxes'])  && isset($_POST['editCheckboxesKeys'])) {

             // Security check
            require("userDetailsChangedCheck.php");
            require("retrieveUserAccessLevel.php");
            $access_level = checkAccesstoResource($_POST['editCheckboxes'][0], $db); // Retrieve proper access level for current user.
            if (isset($_SESSION['member_id']) && $access_level >= 5) { 

                require("navmenustart.php");
                require("functions.php");
                require('./includes/db.php');

                if(sizeof($_POST['editCheckboxes']) == 1 && $_POST['editCheckboxes'][0] != "" && sizeof($_POST['editCheckboxesKeys']) == 1 && $_POST['editCheckboxesKeys'][0] != "") {
                
                    $groupIdSubmit = $_POST['editCheckboxes'][0];
                    $groupKeySubmit = $_POST['editCheckboxesKeys'][0];

                    
                    //GROUP KEY CHECK
                    $stmt = $db->prepare("SELECT * FROM `groups` WHERE `id` = :groupid AND `groupKey` = :groupkeysubmit");
                    $stmt->bindParam('groupid', $groupIdSubmit);
                    $stmt->bindParam('groupkeysubmit', $groupKeySubmit);
                    $stmt->execute();
                    $groupQuery = $stmt->fetchAll();
                    
                    if (sizeof($groupQuery) == 1){
                        
                        $groupName = $groupQuery[0]['groupName'];
                        $groupId = $groupQuery[0]['id'];
                        $groupKey = $groupQuery[0]['groupKey'];
                        $relationalGroupId = $groupQuery[0]['relationalGroupId'];
                        $groupPicture = $groupQuery[0]['groupPicture'];
                        $groupLinkAccessOnly = $groupQuery[0]['linkAccessOnly'];
        
                        //Creates a location tree for the requested group 
                        if(isset($relationalGroupId)) { 
                            $locationArray = array();
                    
                            $stmt = $db->prepare('SELECT * FROM groups WHERE id = :relationalgroupid');
                            $stmt->bindParam('relationalgroupid', $relationalGroupId);
                            $stmt->execute();
                            $locationQuery = $stmt->fetch();

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
                        
                        //Prints out the location tree of the group so the user can see where the requested group is located
                        if(isset($relationalGroupId)) { 
                            print("<br>
                            <h2 class=\"title\">Edit <span class=\"linkRedirect\"><a href=\"/songshome/group/$groupId/$groupKey\">".$groupName."</a></span> Group<br>Located In ");
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
                        else
                        {
                            print("<br>
                            <h2 class=\"title\">Edit ".$groupName." Group");
                        }

                        //Allows user to select where to move the group

                        print("
                            </h2>
                            <div style=\"text-align:center;margin-left:auto;margin-right:auto;\">");
                        if(isset($relationalGroupId)) { 
                            print("
                                <span style=\"text-align:center;display:inline;width:40%;\">
                                    <label class=\"labels\" style=\"font-size: 100%;\" for=\"cars\">Move to:</label>
                                </span>
                                <span style=\"text-align:left;display:inline;padding-bottom: 2px;\">
                                    <select class=\"selectMenu\" style=\"max-width:180px;\" form=\"editForm\" id=\"moveGroup\" name=\"moveGroup\">
                                    <option value=\"$groupId|$groupKey\">-----</option>
                                ");
                        
                                if (isset($relationalGroupId)){
                                    $subGroupQuery = $db->query("SELECT `groupName`, `id`, `groupKey` FROM `groups` WHERE `relationalGroupId` = '$relationalGroupId' AND `id` != '$groupId'")->fetchAll(); 
                                    
                                    for($o = 1; $o < sizeof($locationArray); $o++)
                                    {
                                        $name = $locationArray[$o];
                                        $subId = $locationId[$o];
                                        $subKey = $locationKey[$o];
                                        print("<option value=\"$subId|$subKey\">$name  &lt;&lt;--</option>");
                                        print($subKey);
                                    }

                                    foreach($subGroupQuery as $subGroup)
                                    {
                                        $name = $subGroup['groupName'];
                                        $subId = $subGroup['id'];
                                        $subKey = $subGroup['groupKey'];
                                        print("<option value=\"$subId|$subKey\">--&gt;&gt;  $name</option>");
                                        print($subKey);
                                    }
                                    
                                }
                                else{
                                    $subGroupQuery = $db->query("SELECT `groupName`, `id`, `groupKey` FROM `groups` WHERE `relationalGroupId` IS NULL AND `id` != '$groupId'")->fetchAll(); 
                                    foreach($subGroupQuery as $subGroup)
                                    {
                                        $name = $subGroup['groupName'];
                                        $subId = $subGroup['id'];
                                        $subKey = $subGroup['groupKey'];
                                        print("<option value=\"$subId|$subKey\">--&gt;&gt;  $name</option>");
                                    }
                                
                                    
                                }
                            print("</select></span>");
                        }

                            

                        print("
                            <form enctype=\"multipart/form-data\" id=\"editForm\" onsubmit=\"checkIfEmpty('textInput');checkIfUploadReady();setTogglesValues();\" autocomplete=\"off\" target=\"_self\" action=\"/songshome/editgroupprocess.php\" method=\"post\">
                            <div style=\"width:300px;margin-left:auto;margin-right:auto;padding-top:10px;\">
                                <div class=\"imagesWrapUpload\" style=\"padding-top:0;padding-bottom:0;\" onmouseover=\"activateDropArea(0, 0); checkOneRemoveButton(0, 0);\">
                                    <label class=\"imgDropArea\" style=\"display:block;\" id=\"list0_dropArea0\" for=\"list0_itemInputList0\">
                                        <label id=\"list0_removeButton0\" onclick=\"removeInput(0, 0, './imageGroupDefault.jpg'); event.preventDefault();\" class=\"removeGroupImgButton\"> 
                                            <span>X</span>                  
                                        </label>
                                        ");
                                    if ($groupPicture == ""){
                                        print("<img class=\"placeholderImage\" id=\"list0_uploadPreview0\" src=\"/songshome/imageGroupDefault.jpg\" alt=\"Selected Image\" /> ");
                                    }
                                    else{
                                        print("<img class=\"placeholderImage\" id=\"list0_uploadPreview0\"  onerror=\"/songshome/imageGroupDefault.jpg\" src=\"/songshome/groupPictures/thumbnail_$groupPicture\" alt=\"Selected Image\" /> ");
                                    }

                                    print("
                                        <div class=\"popup\" style=\"margin-left: auto; margin-right: auto; width:100%;\">
                                            <span class=\"popuptext\" style=\"width: 180px;bottom:calc(30px + .4vw);left:19%;\" id=\"list0_invalidFilePopup0\">File Type Not Supported</span>
                                            <span class=\"popuptext\" style=\"width: 180px;bottom:calc(30px + .4vw);left:19%;\" id=\"list0_fileTooLargePopup0\">File Is Too Large</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                                <div class=\"popup\" style=\"margin-left: auto; margin-right: auto;margin-top:.1em;\" >
                                    <span class=\"popuptext\" style=\"width: 240px;margin-left: -120px;left: 50%;\" id=\"emptyGroup\">Please Enter a Group Name</span>
                                    <span class=\"popuptext\" style=\"width: 150px;margin-left: -75px;left: 50%;\" id=\"charError\">Invalid Characters<br>\\ ' < ></span>
                                    <span class=\"popuptext\" style=\"width: 180px;margin-left: -90px;left: 50%;\" id=\"nameLong\">Length Limit Reached</span>
                                    <input placeholder=\"Group Title\" name=\"editGroupTitle\" id=\"textInput\" class=\"textbox\" maxlength=\"210\" style=\"font-size:25px;text-align:center;box-sizing:border-box;\" value=\"$groupName\" form=\"editForm\" type=\"text\">

                                    <div class=\"borderContainer\" style=\"margin-top:calc(5px + .1vw);padding-top:calc(10px + .4vw);padding-bottom:calc(10px + .4vw);width:300px;box-sizing:border-box;\">
                                        <p class=\"normalText\" style=\"display:block;text-align:center;padding-left:calc(14px + 1.1vw);padding-right:calc(14px + 1.1vw);font-size: calc(14px + .2vw);line-height:25px;\">Hide from list (Link Only Acccess)?
                                            <label style=\"display:inline-block;\" class=\"switch\">");

                                            if ($groupLinkAccessOnly == 0) {
                                                print("<input id=\"linkAccessOnlyToggle\" type=\"checkbox\">
                                                    <span class=\"sliderToggle round\"></span>
                                                    <input id=\"linkAccessOnly\" value=\"0\" type=\"hidden\" name=\"linkAccessOnly[]\" form=\"editForm\">");
                                            }
                                            else{
                                                print("<input id=\"linkAccessOnlyToggle\" type=\"checkbox\" checked>
                                                    <span class=\"sliderToggle round\"></span>
                                                    <input id=\"linkAccessOnly\" value=\"1\" type=\"hidden\" name=\"linkAccessOnly[]\" form=\"editForm\">");
                                            }

                                            print("        
                                            </label>
                                            
                                        </p>                             
                                    </div>

                                </div>

                                

                                <div style=\"margin-left:auto;margin-right:auto;text-align:center;display:block;margin-top:.2em;width:300px;\">
                                <label class=\"button\" style=\"max-width:50%;display:inline-block;background-color:#4fbd49;\">
                                    <input id=\"list0_itemInputList0\" name=\"groupImage\"  type=\"file\" accept=\"image/*\" onchange=\"if(!checkFileType(this.files, 0, 0) ){showPopup('list0_invalidFilePopup0'); document.getElementById('list0_uploadPreview0').src = './imageGroupDefault.jpg'; return;} compressImage(0, 0, 2000, './imageGroupDefault.jpg'); document.getElementById('list0_uploadPreview0').src = window.URL.createObjectURL(this.files[this.files.length - 1]); checkOneRemoveButton(0, 0);\">
                                    <span>Upload Image</span>
                                </label>
                                    <input form=\"editForm\" style=\"margin-top:20px;display:inline;max-width:50%;\" class=\"button\" type=\"submit\" name=\"submit\" value=\"Change Details\">
                                </div>
                                <br><br><br><br><br>
                                <input form=\"editForm\" type=\"hidden\" name=\"editThisGroup\" value=\"$groupId\">
                                <input form=\"editForm\" type=\"hidden\" name=\"editThisGroupKey\" value=\"$groupKey\">
                            </form>
                            </div>
                        ");
                        
                        require("navmenuend.php");              
                    }
                    else
                    {
                    echo "ERROR 404: Database Error.";
                    }
                }
                else
                {
                echo "POST Error: The required number of groups was not selected.";
                
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
        echo "POST Error.";
    
    }
    ?>
</body>
<script src="/songshome/image-conversion-master/build/conversion.js"></script>
<script type="text/javascript" src="/songshome/includes/functionsScript.js"></script>
<script type="text/javascript" src="/songshome/includes/uploadScript.js"></script>
<script type="text/javascript" src="/songshome/includes/showpopupScript.js"></script>

<script>

document.getElementById('list0_removeButton0').style.visibility = "hidden";

function scrollDown(){
    var $div = $("#realTimeContents");

    $("#btnScroll").on("click",function(){    
        $div.scrollTop($div[0].scrollHeight);
    });
}

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

document.getElementById("textInput").onpaste = function(e) {
    if(document.getElementById("textInput").value.length >= 210){
        showPopup('nameLong');
        return false;
    }   
    
};

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
    // Disable Enter
    // var $myForm = $("#editForm");
    // $myForm.submit(function(){
    //     $myForm.submit(function(){
    //         return false;
    //     });
    // });
</script>
</html>