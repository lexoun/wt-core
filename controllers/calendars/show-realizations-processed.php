<?php
if ($_GET['showType'] == 'processed' || $_GET['showType'] == 'all') {
include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";

    if (isset($_GET['show'])) { $show = $_GET['show']; } else { $show = 'all'; }
    if (isset($_GET['recieverType']) && $_GET['recieverType'] != 'all') {  $recieverType = " AND reciever_type = '".$_GET['recieverType']."' "; }else{
        $recieverType = '';
    }

function userName($name)
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

            $result1 = $mysqli->query("SELECT d.id, b.billing_name, b.billing_surname, b.billing_company, s.shipping_name, s.shipping_surname, s.shipping_company, d.realization, d.realizationtime, d.realtodate, d.realtotime, d.customer, d.area, d.technical_description FROM addresses_billing b, demands d LEFT JOIN addresses_shipping s ON s.id = d.shipping_id WHERE b.id = d.billing_id AND d.realization != '0000-00-00' AND d.confirmed = '2' AND d.realization > DATE_SUB(NOW(), INTERVAL 12 MONTH)") or die($mysqli->error);

            $result2 = $mysqli->query("SELECT d.id, b.billing_surname, b.billing_surname, b.billing_company, s.shipping_name, s.shipping_surname, s.shipping_company, d.realization, d.realizationtime, d.realtodate, d.realtotime, d.customer, r.startdate, r.enddate, r.starttime, r.endtime FROM demands_double_realization r, addresses_billing b, demands d LEFT JOIN addresses_shipping s ON s.id = d.shipping_id WHERE b.id = d.billing_id AND d.customer = 3 AND d.id = r.demand_id AND r.confirmed = '2' AND r.startdate > DATE_SUB(NOW(), INTERVAL 12 MONTH)") or die($mysqli->error);

        }else{

            $result1 = $mysqli->query("SELECT d.id, b.billing_name, b.billing_surname, b.billing_company, s.shipping_name, s.shipping_surname, s.shipping_company, d.realization, d.realizationtime, d.realtodate, d.realtotime, d.customer, d.area, d.technical_description FROM mails_recievers r, addresses_billing b, demands d LEFT JOIN addresses_shipping s ON s.id = d.shipping_id WHERE b.id = d.billing_id AND d.realization != '0000-00-00' AND d.confirmed = '2' AND d.realization > DATE_SUB(NOW(), INTERVAL 12 MONTH) AND r.type_id = d.id AND (r.type = 'realization_hottub' or r.type = 'realization_sauna') $recieverType GROUP BY d.id") or die($mysqli->error);

            $result2 = $mysqli->query("SELECT d.id, b.billing_surname, b.billing_surname, b.billing_company, s.shipping_name, s.shipping_surname, s.shipping_company, d.realization, d.realizationtime, d.realtodate, d.realtotime, d.customer, r.startdate, r.enddate, r.starttime, r.endtime FROM mails_recievers r, demands_double_realization r, addresses_billing b, demands d LEFT JOIN addresses_shipping s ON s.id = d.shipping_id WHERE b.id = d.billing_id AND d.customer = 3 AND d.id = r.demand_id AND r.confirmed = '0' AND r.startdate > DATE_SUB(NOW(), INTERVAL 12 MONTH) AND r.type_id = d.id AND r.type = 'realization_sauna' $recieverType GROUP BY d.id") or die($mysqli->error);

        }

    } elseif ($show == 'technicians') {

        $result1 = $mysqli->query("SELECT d.id, b.billing_name, b.billing_surname, b.billing_company, s.shipping_name, s.shipping_surname, s.shipping_company, d.realization, d.realizationtime, d.realtodate, d.realtotime, d.customer, d.area, d.technical_description 
    FROM mails_recievers r, demands tech, addresses_billing b, demands d
    LEFT JOIN addresses_shipping s ON s.id = d.shipping_id
    WHERE r.admin_id = tech.id AND tech.role = 'technician' AND b.id = d.billing_id AND d.realization != '0000-00-00' AND d.confirmed = '2' AND d.realization > DATE_SUB(NOW(), INTERVAL 12 MONTH) AND r.type_id = d.id AND (r.type = 'realization_hottub' or r.type = 'realization_sauna') $recieverType GROUP BY d.id") or die($mysqli->error);

        $result2 = $mysqli->query("SELECT d.id, b.billing_surname, b.billing_surname, b.billing_company, s.shipping_name, s.shipping_surname, s.shipping_company, d.realization, d.realizationtime, d.realtodate, d.realtotime, d.customer, rel.startdate, rel.enddate, rel.starttime, rel.endtime 
    FROM mails_recievers r, demands tech, demands_double_realization rel, addresses_billing b, demands d
    LEFT JOIN addresses_shipping s ON s.id = d.shipping_id 
    WHERE r.admin_id = tech.id AND tech.role = 'technician' AND b.id = d.billing_id AND d.customer = 3 AND d.id = rel.demand_id AND rel.confirmed = '2' AND rel.startdate > DATE_SUB(NOW(), INTERVAL 12 MONTH) AND r.type_id = d.id AND r.type = 'realization_sauna' $recieverType GROUP BY d.id") or die($mysqli->error);

    } else {

        $result1 = $mysqli->query("SELECT d.id, b.billing_name, b.billing_surname, b.billing_company, s.shipping_name, s.shipping_surname, s.shipping_company, d.realization, d.realizationtime, d.realtodate, d.realtotime, d.customer, d.area, d.technical_description FROM mails_recievers r, addresses_billing b, demands d LEFT JOIN addresses_shipping s ON s.id = d.shipping_id WHERE b.id = d.billing_id AND d.realization != '0000-00-00' AND d.confirmed = '2' AND d.realization > DATE_SUB(NOW(), INTERVAL 12 MONTH) AND r.admin_id = '$show' AND r.type_id = d.id AND (r.type = 'realization_hottub' or r.type = 'realization_sauna') $recieverType GROUP BY d.id") or die($mysqli->error);

        $result2 = $mysqli->query("SELECT d.id, b.billing_surname, b.billing_surname, b.billing_company, s.shipping_name, s.shipping_surname, s.shipping_company, d.realization, d.realizationtime, d.realtodate, d.realtotime, d.customer, rel.startdate, rel.enddate, rel.starttime, rel.endtime FROM mails_recievers r, demands_double_realization rel, addresses_billing b, demands d LEFT JOIN addresses_shipping s ON s.id = d.shipping_id WHERE b.id = d.billing_id AND d.customer = 3 AND d.id = rel.demand_id AND rel.confirmed = '2' AND rel.startdate > DATE_SUB(NOW(), INTERVAL 12 MONTH) AND r.admin_id = '$show' AND r.type_id = d.id AND r.type = 'realization_sauna' $recieverType GROUP BY d.id") or die($mysqli->error);

    }



while ($row = mysqli_fetch_assoc($result1)) {

    if ($row['area'] == 'prague') {$area = 'PR:';} else { $area = 'BR:';}

    if (isset($row['customer']) && $row['customer'] == 1 || $row['customer'] == 3) {

        $customer = 'realization_hottub';
        $product = $area . ' V → ';

    } elseif (isset($row['customer']) && $row['customer'] == 0) {

        $customer = 'realization_sauna';
        $product = $area . ' S → ';

    }

    $title = userName($row);

    $allTargets = 'Proveditelé: '.recieversShort($customer, $row['id'], 'performer').'<hr>Informovaní: '.recieversShort($customer, $row['id'], 'observer');

    $current = array(
        'title' => $product . $title,
        'url' => '/admin/pages/demands/zobrazit-poptavku?id=' . $row['id'],
        'className' => 'realization-process',
        'description' => $product . $title . '<hr>' . $allTargets . '<hr>'.$row['technical_description'],
    );

    $techniciansQuery = $mysqli->query("SELECT d.id FROM mails_recievers r, demands d WHERE r.type_id = '" . $row['id'] . "' AND r.admin_id = d.id AND d.role = 'technician' AND d.active = 1 AND r.type = '" . $customer . "' AND r.reciever_type = 'performer'") or die($mysqli->error);

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

    if (isset($row['realizationdate']) && $row['realizationdate'] != "0000-00-00" && $row['realtodate'] != "0000-00-00" && $row['realizationtime'] == "00:00:00" && $row['realtotime'] == "00:00:00") {
        $current['start'] = $row['realization'];
        $current['end'] = $row['realtodate'] . 'T24:00:00';

    } elseif (isset($row['realizationdate']) && $row['realizationdate'] != "0000-00-00" && $row['realtodate'] != "0000-00-00" && $row['realizationtime'] != "00:00:00" && $row['realtotime'] != "00:00:00") {
        $current['start'] = $row['realization'] . 'T' . $row['realizationtime'];
        $current['end'] = $row['realtodate'] . 'T' . $row['realtotime'];

    } elseif ($row['realtodate'] != "0000-00-00" && $row['realtotime'] != "00:00:00") {
        $current['start'] = $row['realization'];
        $current['end'] = $row['realtodate'] . 'T' . $row['realtotime'];

    } elseif ($row['realtodate'] != "0000-00-00" && $row['realizationtime'] != "00:00:00") {
        $current['start'] = $row['realization'] . 'T' . $row['realizationtime'];
        $current['end'] = $row['realtodate'] . 'T24:00:00';

    } elseif ($row['realtodate'] != "0000-00-00") {

        $current['start'] = $row['realization'];
        $current['end'] = $row['realtodate'] . 'T24:00:00';

    } elseif ($row['realizationtime'] != "00:00:00") {
        $current['start'] = $row['realization'] . 'T' . $row['realizationtime'];

    } else {
        $current['start'] = $row['realization'];

    }

    $rows[] = $current;

}



while ($row = mysqli_fetch_assoc($result2)) {

    $product = 'S → ';

    $allTargets = 'Proveditelé: '.recieversShort('event', $event['id'], 'performer').'<hr>Informovaní: '.recieversShort('event', $event['id'], 'observer');

    $title = userName($row);

    $current = array(
        'title' => $product . $title,
        'url' => '/admin/pages/demands/zobrazit-poptavku?id=' . $row['id'],
        'className' => 'realization-process',
        'description' => $product . $title . '<hr>' . $allTargets . '<hr>'.$row['technical_description'],
    );

    $techniciansQuery = $mysqli->query("SELECT d.id FROM mails_recievers r, demands d WHERE r.type_id = '" . $row['id'] . "' AND r.admin_id = d.id AND d.role = 'technician' AND d.active = 1 AND r.type = 'realization_sauna' AND r.reciever_type = 'performer'") or die($mysqli->error);

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

    if ($row['startdate'] != "0000-00-00" && $row['enddate'] != "0000-00-00" && $row['starttime'] == "00:00:00" && $row['endtime'] == "00:00:00") {
        $current['start'] = $row['startdate'];
        $current['end'] = $row['enddate'] . 'T24:00:00';

    } elseif ($row['startdate'] != "0000-00-00" && $row['enddate'] != "0000-00-00" && $row['starttime'] != "00:00:00" && $row['endtime'] != "00:00:00") {
        $current['start'] = $row['startdate'] . 'T' . $row['starttime'];
        $current['end'] = $row['enddate'] . 'T' . $row['endtime'];

    } elseif ($row['enddate'] != "0000-00-00" && $row['endtime'] != "00:00:00") {
        $current['start'] = $row['startdate'];
        $current['end'] = $row['enddate'] . 'T' . $row['endtime'];

    } elseif ($row['enddate'] != "0000-00-00" && $row['starttime'] != "00:00:00") {
        $current['start'] = $row['startdate'] . 'T' . $row['starttime'];
        $current['end'] = $row['enddate'] . 'T24:00:00';

    } elseif ($row['enddate'] != "0000-00-00") {

        $current['start'] = $row['startdate'];
        $current['end'] = $row['enddate'] . 'T24:00:00';

    } elseif ($row['starttime'] != "00:00:00") {
        $current['start'] = $row['startdate'] . 'T' . $row['starttime'];

    } else {
        $current['start'] = $row['startdate'];

    }

    $rows[] = $current;

}
if (!empty($rows)) {
    echo json_encode($rows);
} else {
    echo json_encode(array());
}
}else{
    echo json_encode(array());
}