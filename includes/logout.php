<?php
session_start();
setcookie('cookie_pass', '', time() -3600, "/");
setcookie('cookie_role', '', time() - 3600, "/");
// Because you are checking if(isset($_SESSION['loggedin'])), use the below:
unset($_SESSION['loggedin']);
$_SESSION = array();
session_destroy();
header('location: https://'.$_SERVER['SERVER_NAME'].'/admin/');
?>