<!DOCTYPE html>
<html>

<head>
<link rel="stylesheet" href="/songshome/musicPlayer/dist/css/green-audio-player.css">
<script src="/songshome/musicPlayer/dist/js/green-audio-player.js"></script>

<?php include('./includes/defaultheadtags.html');?>
<title>Music Collab. Environ. - Songs</title>

</head>
<!-- <body onresize="adjustElementsOnResize()"> -->
<body>
<?php

if(isset($_GET['songSubmit']) && isset($_GET['songKeySubmit'])) {
    
    require('./includes/db.php');
    
    require("userDetailsChangedCheck.php"); //START SESSION
    require("retrieveUserAccessLevel.php");
    
    $requestedSong = $_GET['songSubmit'];
    $requestedSongKey = $_GET['songKeySubmit']; 
    
    //SONG KEY CHECK
    $stmt = $db->prepare("SELECT * FROM `songs` WHERE `id` = :requestedsong AND `songKey` = :requestedsongkey");
    $stmt->bindParam('requestedsong', $requestedSong);
    $stmt->bindParam('requestedsongkey', $requestedSongKey);
    $stmt->execute();
    $songLookup = $stmt->fetch();

    if ($songLookup != false){


        // Navigation menu & logo setup.
        $parentGroupNavId = retrieveParentGroupId($songLookup['groupId'], $db);
        $parentGroupNavKey = retrieveParentGroupKey($songLookup['groupId'], $db);
        $logoBarLink = "/songshome/group/".$parentGroupNavId."/".$parentGroupNavKey;
        $navigationLinks[] = "<a class=\"navLinks\"  href=\"/songshome/group/".$parentGroupNavId."/".$parentGroupNavKey."\">Home</a>";
        $navigationLinks[] = "<a class=\"navLinks\"  href=\"/songshome/signin.php\">Sign In</a>";
        require("navmenustart.php");

        require("functions.php");
        include('./includes/scrollbuttons.php');

        include('temp_signinbutton.php'); // REMOVE LATER

        $access_level = checkAccesstoResource($songLookup["groupId"], $db); // Retrieve proper access level for current user.
        
        $musicPlayerArray = array();
        $picturesIdArray = array();

        $songId = $songLookup['id'];
        $songName = $songLookup['title'];
        $songNumber = $songLookup['songNumber'];
        $songLyrics = $songLookup['lyrics'];
        $songKey = $songLookup['songKey'];
        $songLyrics = nl2br("$songLyrics");
                        
        $groupId = $songLookup['groupId'];
        $groupQuery = $db->query("SELECT `id`, `groupName`, `groupKey` FROM `groups` WHERE `id` = '$groupId'")->fetchAll();
        if (sizeof($groupQuery) != "0"){
            $groupName = $groupQuery[0]['groupName'];
            $groupId = $groupQuery[0]['id'];
            $groupKey = $groupQuery[0]['groupKey'];
        }
        else{
            $groupName = "";
        }
               
        if (mb_strlen($groupName) > 71){
            $groupName = substr($groupName, 0, 70).'...';       
        }

        if (isset($_SESSION['member_id']) && $access_level >= 1) {
            print("        
                <div class=\"responsiveContainer\" style=\"text-align:center;margin-left:auto;margin-right:auto;margin-top:calc(13px + 1.6vw);\">
                    <input style=\"background-color: rgb(79, 189, 73); padding-left:28%;padding-right:28%;font-size:calc(13px + .25vw);\" class=\"button\" onclick=\"location.href='/songshome/viewcollaborations/$songId/$songKey';\" value=\"View My Collaborations\" type=\"submit\">
                </div>
            ");
        }

        if ($songNumber != NULL){

            print("
            <br><h2 class=\"songTitle\" style=\"padding-bottom:calc(8px + .35vw); overflow-wrap: break-word;position:relative;\"><p class=\"nunito\"><span class=\"linkRedirect\"><a href=\"/songshome/group/$groupId/$groupKey\">$groupName</a></span> Release:</p><br>$songNumber - $songName
            ");
        }
        else{
            print("
            <br><h2 class=\"songTitle\" style=\"padding-bottom:calc(8px + .35vw); overflow-wrap: break-word;position:relative;\"><p class=\"nunito\"><span class=\"linkRedirect\"><a href=\"/songshome/group/$groupId/$groupKey\">$groupName</a></span> Release:</p><br> $songName
            ");
        }

        if (isset($_SESSION['member_id']) && $access_level >= 5) {
            print("<span>
                        <form id=\"editThisSongForm\" style=\"display:inline-block;\" enctype=\"multipart/form-data\" action=\"/songshome/pagedetails.php\" method=\"post\"> 
                            <input form=\"editThisSongForm\" type=\"hidden\" value=\"$groupId\" accept=\"text\" name=\"addSongGroupIdSubmit\">
                            <input form=\"editThisSongForm\" type=\"hidden\" value=\"$groupKey\" accept=\"text\" name=\"addSongGroupKeySubmit\">    
                            <input form=\"editThisSongForm\" type=\"hidden\" value=\"$songId\" accept=\"text\" name=\"editCheckboxesSong[]\">
                            <input form=\"editThisSongForm\" type=\"hidden\" value=\"$songKey\" accept=\"text\" name=\"editCheckboxesSongKeys[]\"> 
                            <input form=\"editThisSongForm\" name=\"editSongButton\" type=\"hidden\" value=\"Edit This Group\">
                            <input form=\"editThisSongForm\" style=\"background-color: black; margin-top:10px; margin-left:10px;margin-bottom:10px;\" class=\"button\" value=\"Edit Release\" type=\"submit\">
                        </form>
                    </span>");
        }
        print("</h2>");
        
        if ($songLyrics != "") {
            print("<div class=\"lyrics\">$songLyrics</div>");
        }
       

        $picturesQuery = $db->query("SELECT * FROM `pictures` WHERE `songId` = '$songId' ORDER BY `pictureSort` ASC")->fetchAll();
        if (sizeof($picturesQuery) != "0"){

            
            print("<div id=\"myModal\" class=\"modal\">
                
                <img class=\"modal-content\" id=\"img\">
                <span class=\"close\">&times;</span>
                </div>");

            $h = 0;
            foreach ($picturesQuery as $pictures){
                $fileName = $picturesQuery[$h]['pictureFileName'];
                $picturesIdArray[] = $h;
                print("<div class=\"imagesWrap\"><img id=\"$h\"  class=\"images\" style=\"object-fit:contain;\" type=\"image\" src=\"/songshome/pictures/$fileName\" alt=\"$fileName\" onerror=\"this.src='/songshome/imageNotFound.jpg';\"/></div><br>");        
                    
                $h++;
            }
        }



        $musicQuery = $db->query("SELECT * FROM `music` WHERE `songId` = '$songId' AND `musicFileName` != '' ORDER BY `musicSort` ASC")->fetchAll();
        if (sizeof($musicQuery) != "0"){
            $l = 0;
            foreach ($musicQuery as $value){
                $musicId = $musicQuery[$l]['musicId'];
                $musicKey = $musicQuery[$l]['musicKey'];
                $musicName = $musicQuery[$l]['musicName'];
                $musicFileName = $musicQuery[$l]['musicFileName'];
                if(file_exists("./music/" . $musicFileName)) {
                    $contentType = mime_content_type("./music/$musicFileName");

                    if (isset($_SESSION['member_id']) && $access_level >= 5) {
                        print("
                                <div class=\"musicPlayerTitleWrap\">
                                    <p class=\"musicPlayerTitle\">$musicName - </p><a class=\"musicPlayerTitleDownload\" href=\"/songshome/downloadSingleSong.php?fileId=$musicId&fileKey=$musicKey\">Download</a>
                                </div>");
                    }
                    else {
                        print("
                                <div class=\"musicPlayerTitleWrap\">
                                    <p class=\"musicPlayerTitle\">$musicName</p>
                                </div>");
                    }

                    print("
                            <div class=\"musicPlayerWrap\">
                                <div id=\"musicWrapClickDetect\" class=\"musicPlayer$l\" style=\"width:100%; box-sizing: border-box;\">
                                
                                    <audio crossorigin>
                                        <source src=\"/songshome/music/$musicFileName\" type=\"$contentType\">                                
                                    </audio>    
                                </div>                                     
                            </div>
                            <br>

                    ");
                    $musicPlayerArray[] = $l;
                    $l++;
                }
            }
        }

        $linksQuery = $db->query("SELECT * FROM `links` WHERE `songId` = '$songId'")->fetchAll();
        if (sizeof($linksQuery) != "0"){
           
            $i = 0;
            foreach ($linksQuery as $value){
                $linkType = $linksQuery[$i]['linkType'];
                $linkData = $linksQuery[$i]['linkData'];
                $linkName = $linksQuery[$i]['linkName'];

                if ($linkType == 1){
                    print("
                    <div class=\"outerVideoWrapper\">
                        <h3 class=\"normalHeading\" style=\"text-align:center;width:100%;border-bottom:none;line-height:calc(5px + .5vw);\">$linkName</h3>
                        <div class=\"videoWrapper\">
                            <iframe class=\"youtubeVideo\" src=\"https://www.youtube.com/embed/$linkData\" frameborder=\"0\" allow=\"accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen=\"true\" webkitallowfullscreen=\"true\" mozallowfullscreen=\"true\">
                            </iframe>
                        </div>    
                    </div>
                    ");
                }
                else if ($linkType == 2) {
                    print("
                    <div class=\"outerVideoWrapper\">
                        <h3 class=\"normalHeading\" style=\"text-align:center;width:100%;border-bottom:none;line-height:calc(5px + .5vw);\">$linkName</h3>
                        <div class=\"videoWrapper\">
                            <iframe class=\"youtubeVideo\" src=\"https://www.youtube.com/embed/videoseries?list=$linkData\" frameborder=\"0\" allow=\"accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen=\"true\" webkitallowfullscreen=\"true\" mozallowfullscreen=\"true\">
                            </iframe>
                        </div>    
                    </div>
                    ");
                }

            $i++;
            }

            $b = 0;
            $titlePresent = 0;
            foreach ($linksQuery as $value){
                $linkType = $linksQuery[$b]['linkType'];
                $linkData = $linksQuery[$b]['linkData'];
                $linkName = $linksQuery[$b]['linkName'];

                if ($linkType == 0){
                    if ($titlePresent == false) {
                            print("<br><div class=\"linksWrap\" style=\"\">
                                        <p class=\"songLinksTitle\">See More</p><br><div class=\"linksBorder\" style=\"\"><ul>
                                    ");
                            $titlePresent = 1;
                    }

                    if ($linkData == ""){
                    }
                    else if ($linkName == "")
                    {                        
                        print("<li class=\"mainColor\"><a href=\"$linkData\">$linkData</a></li><br>");
                    }
                    else
                    {
                        print("<li class=\"mainColor\"><a href=\"$linkData\">$linkName</a></li><br>");
                    }
                }

            $b++;
            }
            print("</ul></div></div><br><br>");

        }

        // Add Placeholder
        if (sizeof($picturesQuery) == "0" && sizeof($musicQuery) == "0" && sizeof($linksQuery) == "0" && $songLyrics == ""){ 
           // print("<p style=\"font-size: calc(10px + 40vh);text-align:center;  line-height: calc(220px + 10vw); color: #000000; opacity: .2;  \">𝄞</p>");
        }
        
        require("navmenuend.php");
        // https://www.youtube.com/embed/L09a3C881n4
        // https://www.youtube.com/embed/HtjkC7pukQg

        

        
        //         $music = mime_content_type("./music/All Our Dreams.mp3");
        // var_dump("$music");

        

    }
    else
    {
        include("404.php");
        echo "The requested song does not exist.";
        //header("Location: "."./groups.php");
    }
}
else
{
    echo "ERROR: Request method was incorrect. ";
    //header("Location: "."./groups.php");
}
?>


<script>
 
    // function adjustElementsOnResize(){
    //     var toTopScrollButton = document.getElementById("scrollToTopButton");
    //     var toBottomScrollButton = document.getElementById("scrollToBottomButton");
    //     if (window.innerWidth <= 600 )
    //     {
         
    //         toTopScrollButton.style.visibility = "hidden";
    //         toBottomScrollButton.style.visibility = "hidden";
    //     } 
    //     else
    //     { 
    //         toTopScrollButton.style.visibility = "visible";
    //         toBottomScrollButton.style.visibility = "visible";
    //     }
    // }
    // adjustElementsOnResize();

    // Get the modal
    var picturesIdArray = <?php echo json_encode($picturesIdArray); ?>;

   if (picturesIdArray.length > 0) {
    var modal = document.getElementById("myModal");
    var modalImg = document.getElementById("img");
    var span = document.getElementsByClassName("close")[0];
    var active = 0;

        span.onclick = function() { 
            modal.style.display = "none";
        }
        
    var count = 0;
    for(var i=0; i<picturesIdArray.length; i++){              
        var img = document.getElementById(i);
        // var imgNext = document.getElementById(next);
        img.onclick = function(){
            

            modal.style.display = "block";
            modalImg.src = this.src;
            active = Number(this.id);
        }

    }
        
        
        modalImg.onclick = function(){
            var next = active + 1;
            
            if (picturesIdArray.length == next){
            next = 0;
            }

            modalImg.src = document.getElementById(next).src;
            
            active = next;
        }
    
    
        //Code to prevent scrolling on android when modal is open.
        // var fixed = document.getElementById('myModal');

        // fixed.addEventListener('touchmove', function(e) {
            
        //         e.preventDefault();

        // }, false);

        
 
        }


</script>

<!-- Script tag break requried. -->
<script>
    var musicPlayerArray = <?php echo json_encode($musicPlayerArray); ?>;
    document.addEventListener('DOMContentLoaded', function() {
        for(var i=0; i<musicPlayerArray.length; i++){    
            new GreenAudioPlayer('.musicPlayer' + i, { stopOthersOnPlay: true });           
        }        
    });
    
</script>

</body>



</html>