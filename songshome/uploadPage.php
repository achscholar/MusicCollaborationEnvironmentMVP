<!DOCTYPE html>
<html lang="en">
<head>
<?php include('./includes/defaultheadtags.html');?>
<title>Music Collab. Environ. - Upload Submission</title>

</head>
<body>

<?php
require('./includes/db.php');

if (isset($_GET["requests_file_id"]) && isset($_GET["visitor_key"])) {
    // Check if the submission has not already been received.
    $stmt = $db->prepare("SELECT * FROM `collaboration_request_files` WHERE `id` = :requestsfileid AND `visitor_key` = :visitorkey AND `verified` = 0 AND `member_id` IS NULL");
    $stmt->bindParam('requestsfileid',  $_GET["requests_file_id"]);
    $stmt->bindParam('visitorkey',  $_GET["visitor_key"]);
    $stmt->execute();
    $checkSubmissionReceivedQuery = $stmt->fetch();

    require("userDetailsChangedCheck.php"); //START SESSION
    require("retrieveUserAccessLevel.php");
   

    if ($checkSubmissionReceivedQuery) {

         // Retrieve the target request.
        $stmt = $db->prepare("SELECT * FROM `collaboration_requests` WHERE `collaboration_id` = :collaborationid AND `id` = :requestid AND `request_for` = 'PUBLIC' AND `open` = 1");
        $stmt->bindParam('collaborationid',  $checkSubmissionReceivedQuery["collaboration_id"]);
        $stmt->bindParam('requestid',  $checkSubmissionReceivedQuery["collaboration_requests_id"]);
        $stmt->execute();
        $targetRequestQuery = $stmt->fetch();
      
        if ($targetRequestQuery) {
            // Check the last upload attempt. Limit upload attempts to every 3 minutes.
            $stmt = $db->prepare("SELECT * FROM `collaboration_request_files` WHERE  `last_public_upload` >= NOW() - INTERVAL 3 MINUTE AND `id` = :requestsfileid AND `visitor_key` = :visitorkey AND `verified` = 0 AND `member_id` IS NULL");
            $stmt->bindParam('requestsfileid',  $_GET["requests_file_id"]);
            $stmt->bindParam('visitorkey',  $_GET["visitor_key"]);
            $stmt->execute();
            $checkSubmissionIntervalQuery = $stmt->fetch();

            if (!$checkSubmissionIntervalQuery) {

                // Retrieve collaboration key.
                $stmt = $db->prepare("SELECT * FROM `collaborations` WHERE `id` = :collaborationid");
                $stmt->bindParam('collaborationid',  $checkSubmissionReceivedQuery["collaboration_id"]);
                $stmt->execute();
                $collaborationQuery = $stmt->fetch();

                if ($collaborationQuery) {
                     // Retrieve collaboration song release.
                    $stmt = $db->prepare("SELECT * FROM `songs` WHERE `id` = :songid");
                    $stmt->bindParam('songid',  $collaborationQuery["songId"]);
                    $stmt->execute();
                    $songQuery = $stmt->fetch();

                    if ($songQuery) {

                        // Navigation menu & logo setup.
                        $parentGroupNavId = retrieveParentGroupId($songQuery["groupId"], $db);
                        $parentGroupNavKey = retrieveParentGroupKey($songQuery["groupId"], $db);
                        $logoBarLink = "/songshome/group/".$parentGroupNavId."/".$parentGroupNavKey;
                        $navigationLinks[] = "<a class=\"navLinks\"  href=\"/songshome/group/".$parentGroupNavId."/".$parentGroupNavKey."\">Home</a>";
                        $navigationLinks[] = "<a class=\"navLinks\"  href=\"/songshome/signin.php\">Sign In</a>";

                        require("navmenustart.php");

                        print("<h2 class=\"title arial\" style=\"width:80%;\">Upload Your Submission For: ".$collaborationQuery["collaboration_title"]." Collaboration</h2>");
                        print("  
                            <input form=\"requestSubmissionForm\" placeholder=\"Link to File (If uploaded somewhere else.)\" name=\"requestLinkInput\"  id=\"list1_requestLinkInput\" class=\"audioLabelUpload\" maxlength=\"1000\" style=\"font-size:calc(5px + 2vw);padding: calc(5px + .1vw) calc(5px + .1vw);margin-left:auto;margin-right:auto;text-align:center;display:block;max-width:80%;width:70%;\" type=\"text\"> 
                            <div style=\"text-align:center;\">   
                                <p class=\"arial\" style=\"font-size: calc(15px + 1vw);\">OR</p>
                                <label class=\"normalText\" id=\"list1_audioFileName\" style=\"font-size: calc(10px + .8vw);display:inline-block;\">Selected Filename (Under 10MB)</label>
                                <button style=\"display:inline-block;position:relative;margin-left:auto;\" onclick=\"document.getElementById('list1_itemInputList').click();\" class=\"button\">Select File</button>
                                
                                <form id=\"requestSubmissionForm\" enctype=\"multipart/form-data\" autocomplete=\"off\"  target=\"_self\" action=\"/songshome/addpublicrequestsubmission.php\" method=\"post\">   
                                    <input type=\"hidden\" name=\"requests_file_id\" value=\"".$_GET["requests_file_id"]."\">
                                    <input type=\"hidden\" name=\"visitor_key\" value=\"".$_GET["visitor_key"]."\">
                                    <input type=\"hidden\" name=\"collaborationId\" value=\"".$checkSubmissionReceivedQuery["collaboration_id"]."\">
                                    <input type=\"hidden\" name=\"collaborationKey\" value=\"".$collaborationQuery["collaboration_key"]."\">
                                    <input type=\"hidden\" name=\"collaborationRequestId\" value=\"".$targetRequestQuery ["id"]."\">
                                    <input form=\"requestSubmissionForm\" style=\"display:none;\" id=\"list1_itemInputList\" name=\"audioFiles\"  onchange=\"if((this.files[0].size / 1048576) > 11) {alert('File cannot be over 10MB. Upload the file to online storage and provide the link instead.');return;} if(this.files[0].type == 'application/vnd.microsoft.portable-executable' || this.files[0].type == 'application/x-msdownload' ){alert('Filetype not supported.');return;}  document.getElementById('list1_audioFileName').textContent = this.files[0].name;\" type=\"file\">
                                </form>
                                <label onclick=\"if(document.getElementById('list1_itemInputList').files.length != 0 || checkIfEmptyNoAlert('list1_requestLinkInput')) {this.style.display = 'none'; document.getElementById('requestSubmissionForm').submit();} else {alert('Please provide a link to a file or select a file to upload.');}\" class=\"button\" style=\"margin-left:auto;margin-right:auto;background-color: rgb(79, 189, 73);max-width: 60%;display: inline-table;z-index:1;font-size:calc(17px + .2vw);margin-top:20px;margin-bottom:10px; cursor: pointer;margin-left:auto;margin-right:auto;\">
                                    <span style=\"z-index:1;\">Upload Submission</span>
                                </label>
                            </div>
                        ");  
                    }
                }
                else { // Target collaboration no longer exists.
                    require("navmenustart.php");
                    print("<h2 class=\"nunito\" style=\"padding-left: 20px;padding-right:20px;\">This collaboration no longer exists.</h2>");
                }
            }
            else { // User is attempting to upload again too soon.
                require("navmenustart.php");
                print("<h2 class=\"nunito\" style=\"padding-left: 20px;padding-right:20px;\">Please wait at least 3 minutes before your last upload attempt to make another upload.</h2>");
            }
        }
        else { // This request has been closed.
            require("navmenustart.php");
            print("<h2 class=\"nunito\" style=\"padding-left: 20px;padding-right:20px;\">This request has been closed.</h2>");
        }
    }
    else { // User already uploaded submission or this submission does not exist.
        require("navmenustart.php");
        print("<h2 class=\"nunito\" style=\"padding-left: 20px;padding-right:20px;\">You have already uploaded your submission or this request has been closed. Thank you!</h2>");
    }

    require("navmenuend.php");

}
?>

</body>
<script>
function checkIfEmptyNoAlert(id) {
    var x = document.getElementById(id).value;

    if (x.length == "") {
        return false;
    } 

    return true;
}
</script>
</html>