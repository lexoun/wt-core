<?php


// /mnt/data/accounts/w/wellnesstrade/data/www/wellnesstrade.cz/admin/controllers/crons/webhook-shops.php

// /mnt/data/accounts/w/wellnesstrade/data/www/wellnesstrade.cz/admin/controllers/crons/webhook-shops.php

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

$mysqli->query("UPDATE webhooks_shops SET special = 'pepega' WHERE id = 193") or die($mysqli->error);

// /wellnesstrade.cz/admin/controllers/crons/webhook-shops.php

exit;
/*
if(empty($_REQUEST['secretcode']) || $_REQUEST['secretcode'] !== "lYspnYd2mYTJm6") {
    die('wrong access code');
}
*/

include_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";
include_once CONTROLLERS . "/stores/product-edit.php";

$query_data = $mysqli->query("SELECT * 
    FROM webhooks_shops 
    WHERE CAST(finished as DATE) = '0000-00-00' AND started < DATE_SUB( NOW(), INTERVAL 30 MINUTE ) AND retries < 4 
    ORDER BY id DESC 
    LIMIT 1
    ")or die($mysqli->error);

while($data = mysqli_fetch_assoc($query_data)) {

    $mysqli->query("UPDATE webhooks_shops SET started = CURRENT_TIMESTAMP(), retries = retries + 1 WHERE id = '".$data['id']."'") or die($mysqli->error);

    $id = $data['event_id'];


    /* array of categories: [

        product create
        product update - special [with images/without images]
        product delete

        variation create
        variation update - special [with images/without images]
        variation delete

    ] */

    $result = false;

    try {

        if($data['type'] === 'update'){

            $result = updateProduct($data);

        }elseif($data['type'] === 'delete'){

            $result = deleteProduct($data);

        }elseif($data['type'] === 'quantity'){

            $result = quantityProduct($data);

        }

    }catch(Exception $e) {

        echo 'Message: ' .$e->getMessage();

        $errMessage = $mysqli->real_escape_string($e->getMessage());
        $mysqli->query("UPDATE webhooks_shops SET result = '".$errMessage."' WHERE id = '".$data['id']."'") or die($mysqli->error);

    }

    if($result){
        $mysqli->query("UPDATE webhooks_shops SET finished = CURRENT_TIMESTAMP(), result = 'SUCCESS' WHERE id = '".$data['id']."'")or die($mysqli->error);
        echo 'success<br>';
    }

}

exit;
