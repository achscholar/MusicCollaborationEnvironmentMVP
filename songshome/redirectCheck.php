<?php
// Include to redirect to set location.
if (isset($_POST["redirectpost"])) {
    header("Location: ".$_POST["redirectpost"]);
}
else if (isset($_POST["redirectget"])) {
    header("Location: ".$_POST["redirectget"]);
}

?>