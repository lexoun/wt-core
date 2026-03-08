<?php

include_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";
include_once INCLUDES . "/googlelogin.php";
include_once INCLUDES . "/functions.php";

if(isset($_REQUEST['id'])){ 

    $id = $_REQUEST['id']; 

}elseif(isset($_POST['search'])){

    $id = $_POST['search'];

}

$dataQuery = $mysqli->query('SELECT *, d.customer as customer, d.id as id, DATE_FORMAT(d.date, "%d. %m. %Y") as dateformated, DATE_FORMAT(d.realization, "%d. %m. %Y") as realizationformated, DATE_FORMAT(d.realtodate, "%d. %m. %Y") as realtodateformat FROM demands d LEFT JOIN warehouse_products p ON p.connect_name = d.product WHERE d.id="' . $id . '"') or die($mysqli->error);
$data = mysqli_fetch_assoc($dataQuery);

// Location
$address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $data['shipping_id'] . '" WHERE b.id = "' . $data['billing_id'] . '"') or die($mysqli->error);
$address = mysqli_fetch_assoc($address_query);

$location = calendar_location($address);

$eventId = '';

if ((isset($data['realization']) && $data['realization'] != "0000-00-00")) {

    $event = new Google_Service_Calendar_Event(array(
        'location' => $location,
        'creator' => array(
            'displayName' => $client['user_name'],
            'self' => true,
        ),
        'reminders' => array(
            'useDefault' => false,
//            'overrides' => array(
//                array('method' => 'email', 'minutes' => 24 * 60),
//                array('method' => 'popup', 'minutes' => 10),
//            ),
        ),
    ));


    // Specifications
    $productSpecification = calendarProductSpecs($data);

    // Title / Summary
    if($data['area'] == 'prague'){ $area = 'PRAHA:'; }else{ $area = 'BRNO:'; }
    $data['title'] = $area.' Realizace - ' . $data['user_name'];
    $event->setSummary($data['title']);

    // Set type
    if ($data['customer'] == 0) { $data['eventType'] = 'realization_sauna'; } else { $data['eventType'] = 'realization_hottub'; }

    // Attendees
    $attendees = getAttendees($data['id'], $data['eventType']);
    $event->attendees = $attendees;

    // Color
    if (isset($data['confirmed']) && $data['confirmed'] == '1') {
        $color = '10';
        $status = 'POTRVZENÁ';
    } elseif($data['confirmed'] == '2') {
        $color = '5';
        $status = 'V ŘEŠENÍ';
    } else{
        $color = '8';
        $status = 'PLÁNOVANÁ';
    }

    // Description
    $event->setDescription('🛠️ Proveditelé: ' . getRecievers($data['eventType'], $id, 'performer') . '
👁️ Informovaní: ' . getRecievers($data['eventType'], $id, 'observer') . '
 
» DŮLEŽITÉ INFORMACE:

Stav realizace: '.$status.'

⚠️️ ' . strip_tags($data['technical_description']) . '

📞 Kontakt: '.phone_prefix($address['billing_phone_prefix']).' '. $address['billing_phone'] . '

📷 Dokumenty: https://wellnesstrade.cz/p?cc='.$data['secretstring'].'

  ' . $productSpecification);


    // Start and End
    $start = calendar_startDate($data['realization'], $data['realizationtime']);
    $end = calendar_endDate($data['realization'], $data['realizationtime'], $data['realtodate'], $data['realtotime']);

    $event->setStart($start);
    $event->setEnd($end);



    $event->setColorId($color);

    // Saving via API
    $eventId = calendarSave($data, $event);

} elseif ((isset($data['realization']) && $data['realization'] == "0000-00-00") && $data['gcalendar'] != '') {

    // Removal of event
    calendarDelete($data['gcalendar']);

}

$mysqli->query("UPDATE demands SET gcalendar = '$eventId' WHERE id = '" . $data['id'] . "'") or die($mysqli->error);


/// CUSToMER 3
if (isset($data['customer']) && $data['customer'] == 3) {

    $data['eventType'] = 'realization_sauna';

    $secondRealQuery = $mysqli->query("SELECT * FROM demands_double_realization WHERE demand_id = '" . $data['id'] . "'");

    if (mysqli_num_rows($secondRealQuery) > 0) {

        $secondReal = mysqli_fetch_array($secondRealQuery);

        $data['gcalendar'] = $secondReal['gcalendar'];

        $eventId = '';
        $productSpecification = '';

        if (isset($secondReal['startdate']) && $secondReal['startdate'] != "0000-00-00") {

            $event = new Google_Service_Calendar_Event(array(
                'location' => $location,
                'creator' => array(
                    'displayName' => $client['user_name'],
                    'self' => true,
                ),
                'reminders' => array(
                    'useDefault' => false,
//                    'overrides' => array(
//                        array('method' => 'email', 'minutes' => 24 * 60),
//                        array('method' => 'popup', 'minutes' => 10),
//                    ),
                ),
            ));


            if($data['area'] == 'prague'){ $area = 'PRAHA:'; }else{ $area = 'BRNO:'; }

            $data['title'] = $area.' Realizace - ' . $data['user_name'];

            $event->setSummary($data['title']);

            // Color
            if (isset($data['confirmed']) && $data['confirmed'] == '1') {
                $color = '10';
                $status = 'POTRVZENÁ';
            } elseif($data['confirmed'] == '2') {
                $color = '5';
                $status = 'V ŘEŠENÍ';
            } else{
                $color = '8';
                $status = 'PLÁNOVANÁ';
            }

            // Specifications
            $productSpecification = calendarProductSpecs($data, true);

$event->setDescription('🛠️ Proveditelé: ' . getRecievers($data['eventType'], $id, 'performer') . '
👁️ Informovaní: ' . getRecievers($data['eventType'], $id, 'observer') . '
 
» DŮLEŽITÉ INFORMACE:

Stav realizace: '.$status.'

⚠️ ' . strip_tags($data['technical_description']) . '

📞 Kontakt: '.phone_prefix($address['billing_phone_prefix']).' ' . $address['billing_phone'] . '

📷 Dokumenty: https://wellnesstrade.cz/p?cc='.$data['secretstring'].'


        ' . $productSpecification);

            // Start and End
            $start = calendar_startDate($secondReal['startdate'], $secondReal['starttime']);
            $end = calendar_endDate($secondReal['startdate'], $secondReal['starttime'], $secondReal['enddate'], $secondReal['endtime']);

            $event->setStart($start);
            $event->setEnd($end);

            $event->setColorId($color);

            // Attendees
            $attendees = getAttendees($data['id'], $data['eventType']);
            $event->attendees = $attendees;

            // Saving via API
            $eventId = calendarSave($data, $event);

        } elseif ((isset($secondReal['startdate']) && $secondReal['startdate'] == "0000-00-00") && $secondReal['gcalendar'] != '') {

            // removal of realization
            calendarDelete($secondReal['gcalendar']);

        }

        $mysqli->query("UPDATE demands_double_realization SET gcalendar = '$eventId' WHERE demand_id = '" . $data['id'] . "'") or die($mysqli->error);

    }

}