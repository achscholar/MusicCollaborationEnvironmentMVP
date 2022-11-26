<?php

    session_start();
    if(isset($_SESSION['member_id'])){
      session_regenerate_id(true);
    }
    session_unset();
    session_destroy();
    session_write_close();
    setcookie(session_name(),'',0,'/');
    
    require("redirectCheck.php");
    
?>