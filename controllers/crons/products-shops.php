<?php

$start = microtime(true);
/*
if(empty($_REQUEST['secretcode']) || $_REQUEST['secretcode'] !== "lYspnYd2mYTJm6") {
    die('wrong access code');
}
*/
require_once $_SERVER['DOCUMENT_ROOT'].'/admin/vendor/autoload.php';

include_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/admin/controllers/stores/product-edit.php";

$query_data = $mysqli->query("SELECT * 
    FROM webhooks_shops 
    WHERE CAST(finished as DATE) = '0000-00-00' AND started < DATE_SUB( NOW(), INTERVAL 30 MINUTE ) AND retries < 4 
    ORDER BY id DESC 
    LIMIT 1
    ")or die($mysqli->error);

while($data = mysqli_fetch_assoc($query_data)) {

    $mysqli->query("UPDATE webhooks_shops SET started = CURRENT_TIMESTAMP(), retries = retries + 1 WHERE id = '".$data['id']."'") or die($mysqli->error);

    /* array of categories: [

        product create
        product update - special [with images/without images]
        product delete

        variation create
        variation update - special [with images/without images]
        variation delete

    ] */

    if($data['shop'] == 'wellnesstrade'){
        $mysqli->query("UPDATE webhooks_shops SET finished = CURRENT_TIMESTAMP(), result = 'SUCCESS' WHERE id = '".$data['id']."'")or die($mysqli->error);
        continue;
    }

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

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$total_time = round(($finish - $start), 4);

echo 'PHP '.PHP_VERSION.' | Page generated in ' . $total_time . ' seconds.';

exit;
