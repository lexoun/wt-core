<?php
if ($_GET['showType'] == 'tasks' || $_GET['showType'] == 'all') {
include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";

if (isset($_GET['show'])) {$show = $_GET['show'];}else{ $show = 'all'; }

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

        $demandstasksquery = $mysqli->query("SELECT t.title, t.due, t.id, t.time, t.client_id, t.demand_id, d.user_name as demand_name, t.text, t.status FROM tasks t LEFT JOIN demands d ON d.id = t.demand_id WHERE t.due > DATE_SUB(NOW(), INTERVAL 12 MONTH)") or die($mysqli->error);

    }else{

        $demandstasksquery = $mysqli->query("SELECT t.title, t.due, t.id, t.time, t.client_id, t.demand_id, d.user_name as demand_name, t.text, t.status FROM mails_recievers r, tasks t LEFT JOIN demands d ON d.id = t.demand_id WHERE t.due > DATE_SUB(NOW(), INTERVAL 12 MONTH) AND r.type_id = t.id AND r.type = 'task' $recieverType GROUP BY t.id") or die($mysqli->error);

    }

} elseif ($show == 'technicians') {

    $demandstasksquery = $mysqli->query("SELECT tsks.title, tsks.due, tsks.id, tsks.time, tsks.client_id, tsks.demand_id, d.user_name as demand_name, tsks.text, tsks.status FROM mails_recievers tgts, demands de, tasks tsks LEFT JOIN demands d ON d.id = tsks.demand_id WHERE tgts.admin_id = de.id AND de.role = 'technician' AND tsks.id = tgts.type_id AND tgts.type = 'task' AND tsks.due > DATE_SUB(NOW(), INTERVAL 12 MONTH) $recieverType GROUP BY tsks.id") or die($mysqli->error);

} else {

    $demandstasksquery = $mysqli->query("SELECT tsks.title, tsks.due, tsks.id, tsks.time, tsks.client_id, tsks.demand_id, d.user_name as demand_name, tsks.text, tsks.status FROM mails_recievers tgts, tasks tsks LEFT JOIN demands d ON d.id = tsks.demand_id WHERE tgts.admin_id = '$show' AND tsks.id = tgts.type_id AND tgts.type = 'task' AND tsks.due > DATE_SUB(NOW(), INTERVAL 12 MONTH) $recieverType") or die($mysqli->error);

}

while ($rowus = mysqli_fetch_assoc($demandstasksquery)) {

    $allTargets = 'Proveditelé: '.recieversShort('task', $rowus['id'], 'performer').'<hr>Informovaní: '.recieversShort('task', $rowus['id'], 'observer');

    if ($rowus['demand_id'] != 0) {
        $title = $rowus['demand_name'] . ' - ' . $rowus['title'];
    } else {
        $title = $rowus['title'];
    }

    $className = $rowus['status'] == 3 ? ' strike' : '';
    $checkmark = $rowus['status'] == 3 ? '✔ ' : '';

    $current = array(
        'title' => $checkmark.$title,
        'url' => '/admin/pages/tasks/zobrazit-ukol?id=' . $rowus['id'],
        'className' => 'task'.$className,
        'description' => $title . '<hr>' . $allTargets . '<hr>'.$rowus['text'],
    );

    $techniciansQuery = $mysqli->query("SELECT d.id FROM mails_recievers r, demands d WHERE r.type_id = '" . $rowus['id'] . "' AND r.admin_id = d.id AND d.role = 'technician' AND r.type = 'task' AND d.active = 1 $recieverType") or die($mysqli->error);

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

    if ($rowus['time'] != "00:00:00") {

            $current['start'] = $rowus['due'] . 'T' . $rowus['time'];

    } else {

            $current['start'] = $rowus['due'];

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