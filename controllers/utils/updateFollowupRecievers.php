<?php

include $_SERVER['DOCUMENT_ROOT'] . '/admin/config/config.php';

$getFollowUps = $mysqli->query("SELECT * FROM demands_mails_history")or die($mysqli->error);

while($followUp = mysqli_fetch_assoc($getFollowUps)){

    echo $followUp['id'].'<br>';

    if($followUp['demand_id'] != 0){

    $getIdQuery = $mysqli->query("SELECT admin_id FROM demands WHERE id = '".$followUp['demand_id']."'");

    $getId = mysqli_fetch_assoc($getIdQuery);

    $mysqli->query("UPDATE demands_mails_history 
        SET performer_id = '".$getId['admin_id']."' 
        WHERE id = '".$followUp['id']."'");

    }

}
