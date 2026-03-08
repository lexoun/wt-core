<?php

/*
 *
 * TODO temporary disabled

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";

// checknout všechny cron zadané úlohy a updatovat produkt
$crons_query = $mysqli->query("SELECT product_id FROM cron_jobs ORDER BY product_id LIMIT 12")or die($mysqli->error);

$i = 0;
if(mysqli_num_rows($crons_query) > 0) {

    while ($cron = mysqli_fetch_assoc($crons_query)) {

        $i++;

//        echo $cron['product_id'].'<br>';
        api_product_update($cron['product_id']);
        $mysqli->query("DELETE FROM cron_jobs WHERE product_id = '".$cron['product_id']."'")or die($mysqli->error);

    }

}

echo $i;

*/