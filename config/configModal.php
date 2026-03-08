<?php
$mysqli = new mysqli("localhost", "wellnesstrade", "Wellnesstrade2510", "wellnesstrade");
if ($mysqli->connect_errno) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}


mysqli_set_charset($mysqli, "utf8");
$mysqli->query("SET NAMES 'utf8'");
SetLocale(LC_ALL, "cs_CZ.utf8");
$czquery = $mysqli->query("SET lc_time_names = 'cs_CZ'");

//$showLog = false;
//if (isset($_COOKIE['cookie_email']) && $_COOKIE['cookie_email'] == 'becher@saunahouse.cz') {
//    ini_set('display_errors', 1);
//    ini_set('display_startup_errors', 1);
//    error_reporting(E_ALL);
//
//    $showLog = false;
//}


?>