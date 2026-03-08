<?php php

if (session_status() === PHP_SESSION_NONE){ session_start(); };

setlocale(LC_TIME, "cs_CZ");

require_once 'config/config.php';
//require_once 'config/parameters.php';
require_once 'config/router.php';
require_once 'config/functions.php';

//my_connect();

//include_once 'new_controller/base.php';

if (file_exists('new_controller/'.$main.'Controller.php')) {
    include_once 'new_controller/'.$main.'Controller.php';
}


include_once 'new_view/'.$main.'/'.$subsidiary.'.php';
