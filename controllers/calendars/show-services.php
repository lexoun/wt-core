<?php
if ($_GET['showType'] == 'services' || $_GET['showType'] == 'all') {
include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";

if(isset($_GET['show'])){ $show = $_GET['show']; }else{ $show = 'all'; }

if (isset($_GET['recieverType']) && $_GET['recieverType'] != 'all') {  $recieverType = " AND reciever_type = '".$_GET['recieverType']."' "; }else{
    $recieverType = '';
}

function user_name($name)
{

    if (isset($name['shipping_name']) && ($name['shipping_name'] != '' || $name['shipping_surname'] != '')) {

        return $name['shipping_name'] . ' ' . $name['shipping_surname'];

    } elseif ($name['billing_name'] && ($name['billing_name'] != '' || $name['billing_surname'] != '')) {

        return $name['billing_name'] . ' ' . $name['billing_surname'];

    } else {

        return $name['billing_company'];

    }

}

function acronymRt($words)
{

    $acronym = '';

    foreach (explode(' ', $words) as $word) {
        $acronym .= mb_substr($word, 0, 1, 'utf-8');
    }

    return $acronym;

}

if ($show == 'all') {


    if($_GET['recieverType'] == 'all'){

        $servisquery = $mysqli->query("SELECT s.state, s.estimatedtime, s.date, s.id, b.billing_name, b.billing_surname, b.billing_company, ship.shipping_name, ship.shipping_surname, ship.shipping_company, s.technical_details FROM services s LEFT JOIN addresses_billing b ON b.id = s.billing_id LEFT JOIN addresses_shipping ship ON ship.id = s.shipping_id WHERE s.state != 'canceled' AND s.date > DATE_SUB(NOW(), INTERVAL 12 MONTH) GROUP BY s.id") or die($mysqli->error);


    }else{

        $servisquery = $mysqli->query("SELECT s.state, s.estimatedtime, s.date, s.id, b.billing_name, b.billing_surname, b.billing_company, ship.shipping_name, ship.shipping_surname, ship.shipping_company, s.technical_details FROM mails_recievers r, services s LEFT JOIN addresses_billing b ON b.id = s.billing_id LEFT JOIN addresses_shipping ship ON ship.id = s.shipping_id WHERE s.state != 'canceled' AND s.date > DATE_SUB(NOW(), INTERVAL 12 MONTH) AND r.type_id = s.id AND r.type = 'service' $recieverType GROUP BY s.id") or die($mysqli->error);


    }

} elseif($show == 'technicians') {
  
    $servisquery = $mysqli->query("SELECT s.state, s.estimatedtime, s.date, b.billing_name, b.billing_surname, b.billing_company, ship.shipping_name, ship.shipping_surname, ship.shipping_company, s.id, s.technical_details FROM mails_recievers t, demands d, services s LEFT JOIN addresses_billing b ON b.id = s.billing_id LEFT JOIN addresses_shipping ship ON ship.id = s.shipping_id WHERE s.state != 'canceled' AND s.id = t.type_id AND t.type = 'service' AND t.admin_id = d.id AND d.role = 'technician' AND s.date > DATE_SUB(NOW(), INTERVAL 12 MONTH) $recieverType GROUP BY s.id") or die($mysqli->error);

} else {

    $servisquery = $mysqli->query("SELECT s.state, s.estimatedtime, s.date, b.billing_name, b.billing_surname, b.billing_company, ship.shipping_name, ship.shipping_surname, ship.shipping_company, s.id, s.technical_details FROM mails_recievers t, services s LEFT JOIN addresses_billing b ON b.id = s.billing_id LEFT JOIN addresses_shipping ship ON ship.id = s.shipping_id WHERE s.state != 'canceled' AND s.id = t.type_id AND t.type = 'service' AND t.admin_id = '$show' AND s.date > DATE_SUB(NOW(), INTERVAL 12 MONTH) $recieverType") or die($mysqli->error);

}

while ($row = mysqli_fetch_assoc($servisquery)) {

    $title = user_name($row);

    $allTargets = 'Proveditelé: '.recieversShort('service', $row['id'], 'performer').'<hr>Informovaní: '.recieversShort('service', $row['id'], 'observer');


    if (isset($row['state']) && $row['state'] == 'new') {
        $status = 'NOVÝ';
    }elseif(isset($row['state']) && $row['state'] == 'waiting') {
        $status = 'ČEKÁ NA DÍKY';
    }elseif($row['state'] == 'unconfirmed') {
        $status = 'NEPOTVRZENÝ';
    }elseif($row['state'] == 'confirmed') {
        $status = 'POTVRZENÝ';
    }elseif($row['state'] == 'executed') {
        $status = 'PROVEDENÝ';
    }elseif($row['state'] == 'unfinished') {
        $status = 'NEDOKONČENÝ';
    }elseif($row['state'] == 'warranty') {
        $status = 'REKLAMACE';
    }elseif($row['state'] == 'finished') {
        $status = 'HOTOVÝ';
    }elseif($row['state'] == 'canceled') {
        $status = 'STORNOVANÝ';
    }

    $current = array(
        'title' => 'Servis - ' . $title,
        'url' => '/admin/pages/services/zobrazit-servis?id=' . $row['id'],
        'className' => 'service',
        'description' => 'Servis - '. $title . '<hr>'.$status.'<hr>' . $allTargets . '<hr>'.$row['technical_details'],
    );

    $techniciansQuery = $mysqli->query("SELECT d.id FROM mails_recievers r, demands d WHERE r.type_id = '" . $row['id'] . "' AND r.admin_id = d.id AND d.role = 'technician' AND r.type = 'service' AND d.active = 1 $recieverType") or die($mysqli->error);

    if (mysqli_num_rows($techniciansQuery) > 0 && mysqli_num_rows($techniciansQuery) < 2) {
        $technician = mysqli_fetch_assoc($techniciansQuery);
        $current['resourceId'] = $technician['id'];
    } elseif (mysqli_num_rows($techniciansQuery) > 0) {
        $resourceIdArray = array();
        while ($technician = mysqli_fetch_assoc($techniciansQuery)) {

            array_push($resourceIdArray, $technician['id']);

        }
        $current['resourceIds'] = $resourceIdArray;
    }

    if ($row['date'] != "0000-00-00" && $row['estimatedtime'] != "00:00:00") {
            $current['start'] = $row['date'] . 'T' . $row['estimatedtime'];
    } else {
            $current['start'] = $row['date'];

    }

    $rows[] = $current;

}
if (!empty($rows)) {
    echo json_encode($rows);
}else{
    echo json_encode(array());
}
}else{
    echo json_encode(array());
}