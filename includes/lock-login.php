<?php
if ($_SESSION['loggedin'] != 1) {
    if (isset($_COOKIE["cookie_email"])) {
        include "includes/lockscreen.php";
        exit;
    } else {
        include "includes/login.php";
        exit;
    }
}

