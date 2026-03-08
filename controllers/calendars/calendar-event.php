<?php

include_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";
include_once INCLUDES . "/googlelogin.php";
include_once INCLUDES . "/functions.php";

$data_query = $mysqli->query("SELECT t.*, c.user_name FROM dashboard_texts t LEFT JOIN demands c ON c.id = t.admin_id WHERE t.id = '" . $id . "'") or die("error");
$data = mysqli_fetch_array($data_query);

$data['eventType'] = 'event';

$location = '';
$productSpecification = '⚠️️ ' . strip_tags($data['popis']);
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

    $data['title'] = $demand['user_name'] . ' - '.$data['title'];

}

$event = new Google_Service_Calendar_Event(array(
    'summary' => $data['title'],
    'location' => $location,
    'colorId' => '9',
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
$end = calendar_endDate($data['date'], $data['time'], $data['enddate'], $data['endtime']);

$event->setStart($start);
$event->setEnd($end);

$attendees = getAttendees($data['id'], $data['eventType']);

$event->attendees = $attendees;


// FREQ — The frequency with which the event should be repeated (such as DAILY or WEEKLY). Required.
// INTERVAL — Works together with FREQ to specify how often the event should be repeated. For example, FREQ=DAILY;INTERVAL=2 means once every two days.

// COUNT — Number of times this event should be repeated.
// OR
// UNTIL — The date or date-time until which the event should be repeated (inclusive).

// BYDAY — Days of the week on which the event should be repeated (SU, MO, TU, etc.). Other similar components include BYMONTH, BYYEARDAY, and BYHOUR

//For your case, you may create a recurring event that has an RRULE of FREQ=DAILY;UNTIL=20190229;INTERVAL=3 for every 3 days. Change FREQ to WEEKLY for every 3 weeks and UNTIL to adjust how many cycles.


if(!empty($data['freq']) && !empty($data['count'])){

    $interval = !empty($data['rec_interval']) ? ';INTERVAL='.$data['rec_interval'] : '';
    $event->setRecurrence(array('RRULE:FREQ='.$data['freq'].';COUNT='.$data['count'].$interval));

}

// Saving via API
$eventId = calendarSave($data, $event);

$mysqli->query("UPDATE dashboard_texts SET gcalendar = '$eventId' WHERE id = '" . $id . "'") or die($mysqli->error);