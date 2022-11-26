<!DOCTYPE html>

<link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@1,800&display=swap" rel="stylesheet">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css?family=Arizonia" rel="stylesheet">
<!-- <link href="https://allfont.net/allfont.css?fonts=annabelle" rel="stylesheet" type="text/css" /> -->
<link href="/songshome/hamburgers-master/dist/hamburgers.css" rel="stylesheet">

<div id="mySidenav" class="sidenav" >
    <a href="javascript:void(0)" class="closebtn" style="font-size:calc(25px + 1.5vw);" onclick="closeNav()">&times;</a>
    <div class="navLinkWrap">
            <?php 
           
            if (isset($_SESSION) && isset($_SESSION["parent_group_id"])) { // User is logged in, give proper navigation.
                $stmt = $db->prepare("SELECT `id`, `groupKey` FROM `groups` WHERE `id` = :parentgroupid");
                $stmt->bindParam('parentgroupid', $_SESSION["parent_group_id"]);
                $stmt->execute();
                $parentGroupQuery = $stmt->fetch();

                print("<a class=\"navLinks\"  href=\"/songshome/group/".$parentGroupQuery["id"]."/".$parentGroupQuery["groupKey"]."\">Home</a>");
                print("<a class=\"navLinks\"  href=\"/songshome/mycollaborations.php\">My Collaborations</a>");
            }
            else if (isset($navigationLinks) && sizeof($navigationLinks) > 0) { // Use the navigation provided by the containing page.
                foreach ($navigationLinks as $link) {
                    print($link);
                } 
            }
            else { // Default placeholders.
                print("
                <a class=\"navLinks\" href=\"\">Home</a>
                <a class=\"navLinks\" href=\"\">Groups</a>
                <a class=\"navLinks\" href=\"\">Upload</a>
                <a class=\"navLinks\" href=\"\">About</a>");
            }
            
            ?>
        
    </div>
</div>

<!-- Make sure to include the navmenuend file at the end of the page when using the menu. -->
<div id="navMenuDetectContent"> 

<div id="navMenuBarCont" class="navMenuBarCont" >
    <div style="text-align:center; width:100%; display:inline-block; height:100%;">
        <h1 style="text-align:center; margin:0; box-sizing:border-box;" >
            <?php 
           
            if (isset($_SESSION) && isset($_SESSION["parent_group_id"])) { // Retrieve the link using user details.
                $stmt = $db->prepare("SELECT `id`, `groupKey` FROM `groups` WHERE `id` = :parentgroupid");
                $stmt->bindParam('parentgroupid', $_SESSION["parent_group_id"]);
                $stmt->execute();
                $parentGroupQuery = $stmt->fetch();

                print("<a class=\"sitelogo\"  href=\"/songshome/group/".$parentGroupQuery["id"]."/".$parentGroupQuery["groupKey"]."\">Music Collab. Environ.</a>");
            }
            else if (isset($logoBarLink) && $logoBarLink != "") { // Use the link provided by the containing page.
                print("<a class=\"sitelogo\"  href=\"$logoBarLink\">Music Collab. Environ.</a>"); 
            }
            else {
                print("<a class=\"sitelogo\"  href=\"\">Music Collab. Environ.</a>");
            }
            
            ?>

        </h1>
    </div>
    <?php
        if (isset($_SESSION["parent_group_id"]) || isset($navigationLinks)) {
            print("
            <div class=\"hamburgerIconCont\" style=\"text-align:center; position:absolute; top:calc(10px + .7vw); right:1%;\">
            
                <button id=\"hamburgerIcon\" onmouseover=\"null\" onclick=\"openNav()\"  class=\"hamburger hamburger--spring\" type=\"button\">
                    <span class=\"hamburger-box\">
                        <span  class=\"hamburger-inner\"></span>
                    </span>
                </button>
            </div>
            ");
        }
    ?>
</div>