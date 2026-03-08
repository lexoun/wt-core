<?php
if ($_GET['showType'] == 'follow-ups' || $_GET['showType'] == 'all') {

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";

if(isset($_GET['show'])){ $show = $_GET['show']; }

if (isset($_GET['recieverType']) && $_GET['recieverType'] != 'all') {  $recieverType = " AND reciever_type = '".$_GET['recieverType']."' "; }else{
    $recieverType = '';
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

        $dataQuery = $mysqli->query("SELECT f.type, user_name, d.id, f.date_time, f.admin_id, f.text, f.id as fid, f.state FROM demands_mails_history f LEFT JOIN demands d ON d.id = f.demand_id WHERE f.date_time > DATE_SUB(NOW(), INTERVAL 12 MONTH)") or die($mysqli->error);

    }else{

        $dataQuery = $mysqli->query("SELECT f.type, user_name, d.id, f.date_time, f.admin_id, f.text, f.id as fid, f.state FROM mails_recievers r, demands_mails_history f LEFT JOIN demands d ON d.id = f.demand_id WHERE f.date_time > DATE_SUB(NOW(), INTERVAL 12 MONTH) AND r.type_id = f.id AND r.type = 'follow_up' $recieverType") or die($mysqli->error);


    }

} else {

    $dataQuery = $mysqli->query("SELECT f.type, d.user_name, d.id, f.date_time, f.admin_id, f.text, f.id as fid, f.state FROM mails_recievers r, demands_mails_history f LEFT JOIN demands d ON d.id = f.demand_id WHERE r.admin_id = '$show' AND f.date_time > DATE_SUB(NOW(), INTERVAL 12 MONTH) AND r.type_id = f.id AND r.type = 'follow_up' $recieverType") or die($mysqli->error);

}




while ($data = mysqli_fetch_array($dataQuery)) {

    $allTargets = 'Proveditelé: '.recieversShort('follow_up', $data['fid'], 'performer').'<hr>Informovaní: '.recieversShort('follow_up', $data['fid'], 'observer');

    $newDate = new DateTime($data['date_time']);

    if ($newDate->format('H:i:s') != "00:00:00") {

        $date = $newDate->format('Y-m-d') . 'T' . $newDate->format('H:i:s');

    } else {
        
        $date = $newDate->format('Y-m-d');
    }

    $className = $data['state'] != 'ongoing' ? ' strike' : '';
    $checkmark = $data['state'] != 'ongoing' ? '✔ ' : '';



    $rows[] = array(
        'title' => $checkmark.$data['type'].' - '.$data['user_name'],
        'start' => $date,
        'url' => '/admin/pages/demands/zobrazit-poptavku?id=' . $data['id'],
        'className' => 'follow-up'.$className,
        'description' => 'Follow Up - '.$data['user_name'] . ' - ' . $data['type'].'<hr>' . $allTargets . '<hr>'.$data['text'],
    );

}

if (!empty($rows)) {
    echo json_encode($rows);
}else{
    echo json_encode(array());
}
}