<?php

include_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";
include_once INCLUDES . "/googlelogin.php";
include_once INCLUDES . "/functions.php";

$dataQuery = $mysqli->query('SELECT s.*, DATE_FORMAT(s.date, "%d. %M %Y") as dateformated, DATE_FORMAT(estimatedtime, "%H:%i:%s") as hoursmins, c.title FROM services s LEFT JOIN services_categories c ON c.seoslug = s.category WHERE s.id="' . $id . '"') or die($mysqli->error);

$data = mysqli_fetch_array($dataQuery);

$data['eventType'] = 'service';

$location = '';
$productSpecification = '';

$address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $data['shipping_id'] . '" WHERE b.id = "' . $data['billing_id'] . '"') or die($mysqli->error);
$address = mysqli_fetch_assoc($address_query);

$location = calendar_location($address);


if($data['customertype'] == 0){ $data['title'] = 'servis sauny'; }

if ($data['clientid'] != 0) {

    $demand_query = $mysqli->query("SELECT * FROM demands WHERE id = '" . $data['clientid'] . "'") or die("bNeexistuje");
    $demand = mysqli_fetch_array($demand_query);

    $productSpecification = calendarProductSpecs($demand);

    $data['title'] = 'Servis #'.$data['id'].' - ' . $demand['user_name'] . ' - ' . $data['title'];

}else{

    $user_name = getAddressName($address);

    $data['title'] = 'Servis #'.$data['id'].' - ' . $user_name . ' - ' . $data['title'];

}

if (isset($data['state']) && $data['state'] == 'new') {
    $status = 'NOVÝ';
}elseif(isset($data['state']) && $data['state'] == 'waiting') {
    $status = 'ČEKÁ NA DÍKY';
}elseif($data['state'] == 'unconfirmed') {
    $status = 'NEPOTVRZENÝ';
}elseif($data['state'] == 'confirmed') {
    $status = 'POTVRZENÝ';
}elseif($data['state'] == 'executed') {
    $status = 'PROVEDENÝ';
}elseif($data['state'] == 'unfinished') {
    $status = 'NEDOKONČENÝ';
}elseif($data['state'] == 'warranty') {
    $status = 'REKLAMACE';
}elseif($data['state'] == 'finished') {
    $status = 'HOTOVÝ';
}elseif($data['state'] == 'canceled') {
    $status = 'STORNOVANÝ';
}


$event = new Google_Service_Calendar_Event(array(
    'summary' => $data['title'],
    'location' => $location,
    'colorId' => '3',
    'reminders' => array(
        'useDefault' => false,
//        'overrides' => array(
//            array('method' => 'email', 'minutes' => 24 * 60),
//        ),
    ),
));


$event->setDescription('🛠️ Proveditelé: ' . getRecievers($data['eventType'], $id, 'performer') . '
👁️ Informovaní: ' . getRecievers($data['eventType'], $id, 'observer') . '
 
» DŮLEŽITÉ INFORMACE:

Stav servisu: '.$status.'
 
⚠️️ ' . $data['technical_details'] . '

📞 Kontakt: '.phone_prefix($address['billing_phone_prefix']).' ' . $address['billing_phone'] . '

📷 Nahrání fotek: https://wellnesstrade.cz/p?serv='.md5($data['id']).'

' . $productSpecification);


// Start and End
$start = calendar_startDate($data['date'], $data['estimatedtime']);
$end = calendar_endDate($data['date'], $data['estimatedtime']);

$event->setStart($start);
$event->setEnd($end);

// Attendees
$attendees = getAttendees($data['id'], $data['eventType']);
$event->attendees = $attendees;

// Saving via API
$eventId = calendarSave($data, $event);

$mysqli->query("UPDATE services SET gcalendar = '$eventId' WHERE id = '" . $data['id'] . "'") or die($mysqli->error);