<!DOCTYPE html>
<html>
    <?php if(isset($_SESSION['member_id'])) {
        print("<p class=\"labels\" style=\"margin-left: 10px; margin-top:10px; display: block;\">Welcome, ");
        print( $_SESSION['firstname']." ".$_SESSION['lastname'].", Email: ".$_SESSION['user_email']);
        print("</p>");
    }

    ?>
    <p class="labels" style="margin-left: 10px; display: inline;">Sign in as:</p>
    <!-- <form style="display:inline-block;" enctype="multipart/form-data" action="/songshome/signin.php" method="post">     
        <input type="hidden" value="1000" accept="text" name="signin_memberid">
        <input type="hidden" value="PHPCODEHERE" accept="text" name="redirectpost"> 
        <input style="background-color: black; margin-top:10px; margin-left:10px;" class="button" value="Sitewide Admin" type="submit">
    </form> -->
    <form style="display:inline-block;" enctype="multipart/form-data" action="/songshome/signin.php" method="post">     
        <input type="hidden" value="1001" accept="text" name="signin_memberid">
        <input type="hidden" value="<?php print($_SERVER['REQUEST_URI']); ?>" accept="text" name="redirectpost"> 
        <input style="background-color: black; margin-top:10px; margin-left:10px;" class="button" value="Michael (GROUP LEADER)" type="submit">
    </form>
    <form style="display:inline-block;" enctype="multipart/form-data" action="/songshome/signin.php" method="post">     
        <input type="hidden" value="1006" accept="text" name="signin_memberid">
        <input type="hidden" value="<?php print($_SERVER['REQUEST_URI']); ?>" accept="text" name="redirectpost"> 
        <input style="background-color: black; margin-top:10px; margin-left:10px;" class="button" value="Jennifer (GROUP LEADER)" type="submit">
    </form>
    <form style="display:inline-block;" enctype="multipart/form-data" action="/songshome/signin.php" method="post">     
        <input type="hidden" value="1002" accept="text" name="signin_memberid">
        <input type="hidden" value="<?php print($_SERVER['REQUEST_URI']); ?>" accept="text" name="redirectpost"> 
        <input style="background-color: black; margin-top:10px; margin-left:10px;" class="button" value="Thomas Clark (MEMBER)" type="submit">
    </form>
    <form style="display:inline-block;" enctype="multipart/form-data" action="/songshome/signin.php" method="post">     
        <input type="hidden" value="1005" accept="text" name="signin_memberid">
        <input type="hidden" value="<?php print($_SERVER['REQUEST_URI']); ?>" accept="text" name="redirectpost"> 
        <input style="background-color: black; margin-top:10px; margin-left:10px;" class="button" value="Emily Davis (MEMBER)" type="submit">
    </form>
    <form style="display:inline-block;" enctype="multipart/form-data" action="/songshome/signout.php" method="post">    
        <input type="hidden" value="<?php print($_SERVER['REQUEST_URI']); ?>" accept="text" name="redirectpost"> 
        <input style="background-color: black; margin-top:10px; margin-left:10px;" class="button" value="Signout" type="submit">
    </form>
    
   
</html>