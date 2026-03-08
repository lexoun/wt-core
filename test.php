<?php

if (session_status() === PHP_SESSION_NONE) {session_start();}
// /// Use Composer Autoload
require_once $_SERVER['DOCUMENT_ROOT'].'/admin/vendor/autoload.php';

$gclient = new Google_Client();
$gclient->setAccessType('offline');
$gclient->setApprovalPrompt('force');
$gclient->setApplicationName("Wellness Trade");
$gclient->setAuthConfigFile($_SERVER['DOCUMENT_ROOT'] . '/admin/config/client_secret.json');
$gclient->setRedirectUri('https://' . $_SERVER['HTTP_HOST'] . '/admin/controllers/oauth-controller.php');
$gclient->addScope("https://www.googleapis.com/auth/calendar");

echo 'lel';