<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";
include_once INCLUDES . "/googlelogin.php";
include_once INCLUDES . "/functions.php";

$data_query = $mysqli->query("SELECT t.*, DATE_FORMAT(t.date_time, '%Y-%m-%d') as date, DATE_FORMAT(t.date_time, '%H:%i:%s') as time, c.user_name FROM demands_mails_history t LEFT JOIN demands c ON c.id = t.admin_id WHERE t.id = '" . $id . "'") or die("error");
$data = mysqli_fetch_array($data_query);

$data['eventType'] = 'follow_up';

$location = '';
$productSpecification = '⚠️ ' . strip_tags($data['text']);

if ($data['demand_id'] != 0) {

    $demand_query = $mysqli->query("SELECT * FROM demands WHERE id = '" . $data['demand_id'] . "'") or die("bNeexistuje");
    $demand = mysqli_fetch_array($demand_query);

    $address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $demand['shipping_id'] . '" WHERE b.id = "' . $demand['billing_id'] . '"') or die($mysqli->error);
    $address = mysqli_fetch_assoc($address_query);

    $location = calendar_location($address);

    // Phone + Specifications
    $productSpecification .= '
📞 Kontakt: '.phone_prefix($address['billing_phone_prefix']).' ' . $address['billing_phone'] . '

'.calendarProductSpecs($demand);

$data['title'] = $demand['user_name'] . ' - Follow-up - '.$data['type'];


}

$event = new Google_Service_Calendar_Event(array(
    'summary' => $data['title'],
    'location' => $location,
    'colorId' => '5',
    'creator' => array(
        'displayName' => $data['user_name'],
        'self' => true,
    ),
    'reminders' => array(
        'useDefault' => false,
//        'overrides' => array(
//            array('method' => 'email', 'minutes' => 24 * 60),
//            array('method' => 'popup', 'minutes' => 10),
//        ),
    ),
));


// Description
$event->setDescription('🛠️ Proveditelé: ' . getRecievers($data['eventType'], $id, 'performer') . '
👁️ Informovaní: ' . getRecievers($data['eventType'], $id, 'observer') . '
 
» DŮLEŽITÉ INFORMACE:

'.$productSpecification);


$start = calendar_startDate($data['date'], $data['time']);
$end = calendar_endDate($data['date'], $data['time']);

$event->setStart($start);
$event->setEnd($end);

$attendees = getAttendees($data['id'], $data['eventType']);

$event->attendees = $attendees;


// Saving via API
$eventId = calendarSave($data, $event);

$mysqli->query("UPDATE demands_mails_history SET gcalendar = '$eventId' WHERE id = '" . $id . "'") or die($mysqli->error);