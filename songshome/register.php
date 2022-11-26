<?php

//https://www.w3schools.com/php/php_form_url_email.asp
// $str = "hцfsssssшшггшщщщщ";
// //$pattern = "^[{\p{L}}{0-9}]";

// if (preg_match('/^[{\p{L}}{0-9}\s]+$/u', $str)) {
//     echo 'Username is valid';
// } else {
//     echo 'Username is NOT valid';
// }


//LIMIT password to 70 bytes when a pepper is added
//LIMIT username to 50 characters no hash
//LIMIT email to 254 bytes no hash
//LIMIT Display Name to alphanumberic using regex and clean using XSS library
//Address will not be stored. Sign up code will be automatically emailed.

//CHECK FOR POST
// if ($_SERVER['REQUEST_METHOD'] == 'POST') 

//CHECK IF VARIABLES RECEIVED
// if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['displayname']) && isset($_POST['useremail']))

$user_name = 'johnny26';
$pass_word = '89ejf9sa';
$display_name = 'John';
$user_email = 'johnny7834@gmail.com';
$register_to_group_id = $_GET['abcd'];
$register_to_group_key = '10001';

$ok_to_register = 0;

if(isset($register_to_group_id)){
    alert();
}


if (strlen($user_name) <=50 && strlen($pass_word) <=55 && strlen($display_name) <=50 && strlen($user_email) <=254){
    // SEE https://regexr.com/ for regex expression that is used below
    // for username and email verification

    //EMAIL CHECK
    if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
       
    }
}

?>