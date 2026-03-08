<?php
include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";

$fetch = file_get_contents("php://input");
$body = $mysqli->real_escape_string($fetch);

$order = json_decode($fetch, true);
$site = $_REQUEST['site'];

//$order, $site, $body
// $order = array(), $body = json encoded
include $_SERVER['DOCUMENT_ROOT'] . "/admin/controllers/stores/new-order-hook.php";
