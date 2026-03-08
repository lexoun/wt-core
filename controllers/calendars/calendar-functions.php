<?php

function saveRealization($id){

    global $mysqli;

    $dataQuery = $mysqli->query('SELECT *, d.customer as customer, d.id as id, DATE_FORMAT(d.date, "%d. %m. %Y") as dateformated, DATE_FORMAT(d.realization, "%d. %m. %Y") as realizationformated, DATE_FORMAT(d.realtodate, "%d. %m. %Y") as realtodateformat FROM demands d LEFT JOIN warehouse_products p ON p.connect_name = d.product WHERE d.id="' . $id . '"') or die($mysqli->error);

    if(mysqli_num_rows($dataQuery) === 0){ return false; }

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
                'displayName' => 'WT Admin',
                'self' => true,
            ),
            'reminders' => array(
                'useDefault' => false,
//            'overrides' => array(
//                array('method' => 'email', 'minutes' => 24 * 60),
//                array('method' => 'popup', 'minutes' => 10),
//            ),
            ),
            'sendUpdates' => 'none',
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
                        'displayName' => 'WT Admin',
                        'self' => true,
                    ),
                    'reminders' => array(
                        'useDefault' => false,
//                    'overrides' => array(
//                        array('method' => 'email', 'minutes' => 24 * 60),
//                        array('method' => 'popup', 'minutes' => 10),
//                    ),
                    ),
                    'sendUpdates' => 'none',
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

    return true;
}

function saveEvent($id){

    global $mysqli;

    $data_query = $mysqli->query("SELECT t.*, c.user_name FROM dashboard_texts t LEFT JOIN demands c ON c.id = t.admin_id WHERE t.id = '" . $id . "'") or die("error");

    if(mysqli_num_rows($data_query) === 0){ return false; }

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
        'sendUpdates' => 'none',
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

    return true;
}

function saveService($id){

    global $mysqli;

    $dataQuery = $mysqli->query('SELECT s.*, DATE_FORMAT(s.date, "%d. %M %Y") as dateformated, DATE_FORMAT(estimatedtime, "%H:%i:%s") as hoursmins, c.title FROM services s LEFT JOIN services_categories c ON c.seoslug = s.category WHERE s.id="' . $id . '"') or die($mysqli->error);

    if(mysqli_num_rows($dataQuery) === 0){ return false; }

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
        'guestsCanModify' => false,
        'guestsCanInviteOthers' => false,
        'reminders' => array(
            'useDefault' => false,
//        'overrides' => array(
//            array('method' => 'email', 'minutes' => 24 * 60),
//        ),
        ),
        'sendUpdates' => 'none',
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

    return true;
}

function saveTask($id){

    global $mysqli;

    $data_query = $mysqli->query("SELECT t.*, c.user_name FROM tasks t LEFT JOIN demands c ON c.id = t.request_id WHERE t.id = '" . $id . "'") or die($mysqli->error);

    if(mysqli_num_rows($data_query) === 0){ return false; }

    $data = mysqli_fetch_array($data_query);

    $data['eventType'] = 'task';

    $location = '';
    $productSpecification = '⚠️️ ' . strip_tags($data['text']);

    if ($data['demand_id'] != 0) {

        $demand_query = $mysqli->query("SELECT * FROM demands WHERE id = '" . $data['demand_id'] . "'") or die($mysqli->error);
        $demand = mysqli_fetch_array($demand_query);

        $address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $demand['shipping_id'] . '" WHERE b.id = "' . $demand['billing_id'] . '"') or die($mysqli->error);
        $address = mysqli_fetch_assoc($address_query);

        $location = calendar_location($address);

        $demand['hideChanges'] = true;

        // Phone + Specifications
        $productSpecification .= '
📞 Kontakt: '.phone_prefix($address['billing_phone_prefix']).' ' . $address['billing_phone'] . '

'.calendarProductSpecs($demand).'';

        $data['title'] = $demand['user_name'] . ' - '.$data['title'];

    }

    $event = new Google_Service_Calendar_Event(array(
        'summary' => $data['title'],
        'location' => $location,
        'colorId' => '4',
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
        'sendUpdates' => 'none',
    ));


// Description
    $event->setDescription('🛠️ Proveditelé: ' . getRecievers($data['eventType'], $id, 'performer') . '
👁️ Informovaní: ' . getRecievers($data['eventType'], $id, 'observer') . '
 
» DŮLEŽITÉ INFORMACE:

'.$productSpecification);


    $start = calendar_startDate($data['due'], $data['time']);
    $end = calendar_endDate($data['due'], $data['time']);

    $event->setStart($start);
    $event->setEnd($end);

    $attendees = getAttendees($data['id'], $data['eventType']);

    $event->attendees = $attendees;

// Saving via API
    $eventId = calendarSave($data, $event);

    $mysqli->query("UPDATE tasks SET gcalendar = '$eventId' WHERE id = '" . $id . "'") or die($mysqli->error);

    return true;
}

function saveFollowUp($id){

    global $mysqli;

    $data_query = $mysqli->query("SELECT t.*, DATE_FORMAT(t.date_time, '%Y-%m-%d') as date, DATE_FORMAT(t.date_time, '%H:%i:%s') as time, c.user_name FROM demands_mails_history t LEFT JOIN demands c ON c.id = t.admin_id WHERE t.id = '" . $id . "'") or die("error");

    if(mysqli_num_rows($data_query) === 0){ return false; }

    $data = mysqli_fetch_array($data_query);

    $data['eventType'] = 'follow_up';

    $location = '';
    $productSpecification = '⚠️ ' . strip_tags($data['text']);

    if ($data['demand_id'] != 0) {

        $demand_query = $mysqli->query("SELECT * FROM demands WHERE id = '" . $data['demand_id'] . "'") or die("bNeexistuje");
        $demand = mysqli_fetch_array($demand_query);

        $address_query = $mysqli->query("SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = '" . $demand['shipping_id'] . "' WHERE b.id = '" . $demand['billing_id'] . "'") or die($mysqli->error);
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
        'sendUpdates' => 'none',
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

    return true;
}


function calendarSave($data, $event){

    global $mysqli;
    global $gcalendar;

    // Událost nemá ID z kalendáře
    if ($data['gcalendar'] == '') {

        // Kontrola existence v kalendáři
        $eventOptions = array('q' => $data['title']);
        $eventList = $gcalendar->events->listEvents(CALENDAR_ID, $eventOptions);

        foreach ($eventList->getItems() as $singleEvent) {

            if ($singleEvent->start->dateTime != '') {
                $startDate = $singleEvent->start->dateTime;
                $currentStartDate = $event->start->dateTime;
            } else {
                $startDate = $singleEvent->start->date;
                $currentStartDate = $event->start->date;
            }

            if ($singleEvent->end->dateTime != '') {
                $endDate = $singleEvent->end->dateTime;
                $currentEndDate = $event->end->dateTime;
            } else {
                $endDate = $singleEvent->end->date;
                $currentEndDate = $event->end->date;
            }

            if ($startDate == $currentStartDate && $endDate == $currentEndDate) {

                $eventId = $singleEvent->getId();

            }

        }

        // Nenalezena žádná událost, přidání nové
        if (!isset($eventId)) {

            $eventInsert = $gcalendar->events->insert(CALENDAR_ID, $event);
            $eventId = $eventInsert->id;

            $eventAction = 'create';

        }else{

            $eventUpdate = $gcalendar->events->update(CALENDAR_ID, $eventId, $event);

            $eventAction = 'update';

        }


        // Událost má ID z kalendáře
    } else {

        try {

            // Update událost iv kalendáři
            $eventUpdate = $gcalendar->events->update(CALENDAR_ID, $data['gcalendar'], $event);
            $eventId = $eventUpdate->id;

            $eventAction = 'update';

        } catch (Google_Exception $e) {

            // Error při updatu - chybějící údálost
            if ($e->getCode() == 403) {

                $eventInsert = $gcalendar->events->insert(CALENDAR_ID, $event);
                $eventId = $eventInsert->id;

                $eventAction = 'create';

            }

        }

    }

    require_once MODEL . 'mailsModel.php';
    sendMail($data['eventType'], $data['id'], $eventAction);

    return $eventId;

}


function calendarProductSpecs($data, $second = false){

    global $mysqli;

    if ($data['customer'] == 0 || $second) {

        $customer = 0;
        $product = $data['secondproduct'];

    } else {

        $customer = 1;
        $product = $data['product'];

    }

    if($data['status'] == 8 || $data['status'] == 5){ $subtitle = 'U ZÁKAZNÍKA'; }else{ $subtitle = 'NA SKLADĚ'; }

    $searchquery = $mysqli->query("SELECT * FROM warehouse WHERE demand_id = '" . $data['id'] . "' and product = '".$product."'");

    $specnaz = '';
    if (mysqli_num_rows($searchquery) == 0 && $customer == 1) {

        $specnaz = ' » VÍŘIVKA '.$subtitle.' -

 ❌ Zákazník nemá přiřazenou žádnou vířivku.
';

    } elseif (mysqli_num_rows($searchquery) == 0 && $customer == 0) {

        $specnaz = ' » SAUNA '.$subtitle.' -

 ❌ Zákazník nemá přiřazenou žádnou saunu.
';


    } else {

        $search = mysqli_fetch_array($searchquery);

        $product_name = '';
        $serial_number = '';

        if(isset($search['serial_number'])){ $serial_number = $search['serial_number']; }

        // Info from warehouse
        $warehouse_info = '';
        if (isset($search['description']) && $search['description'] != '') {

            $warehouse_info = '
    
      Informace ze skladu:
      ' . strip_tags($search['description']) . '
    
      ';
        }

        if (isset($customer) && $customer == "0" && (isset($data['secondproduct']) && $data['secondproduct'] == 'custom')) {

            $product_name = '» SAUNA NA MÍRU '.$subtitle.':
 📐 ' . $serial_number.'
';

        } elseif (isset($customer) && $customer == "0" && isset($data['secondproduct'])) {

            $product_name = '» SAUNA '.$subtitle.':
 ♨️ ' . productreturn($data['secondproduct']) . ' - ' . $serial_number.'
';

        } elseif (isset($customer) && $customer == "1" && isset($data['product'])) {

            $product_name = '» VÍŘIVKA '.$subtitle.':
 🛁 ' . returnpn($customer, $data['product']) . ' - ' . $serial_number.'
';

        }


        $specnaz = $product_name.$warehouse_info;


        if(($data['status'] == 4 || $data['status'] == 8 || $data['status'] == 7) && $customer == 1 && !isset($data['hideChanges'])){

            $specsquery = $mysqli->query("SELECT s.id, s.name, w.value as warehouse_value, d.value as demand_value FROM specs s LEFT JOIN warehouse_specs_bridge w ON (w.specs_id = s.id AND w.client_id = '" . $search['id'] . "') LEFT JOIN demands_specs_bridge d ON (d.specs_id = s.id AND d.client_id = '" . $data['id'] . "') WHERE s.product = 1 AND s.technical = 1 GROUP BY s.id order by s.type desc, s.id asc") or die($mysqli->error);

            if(mysqli_num_rows($specsquery) > 0){


                $added = false;
                while ($specs = mysqli_fetch_array($specsquery)) {

                    if (($specs['warehouse_value'] != $specs['demand_value']) && $specs['warehouse_value'] != "" && $specs['demand_value'] != "") {

                        if(!$added){
                            $specnaz .= '
 ⚠️ NUTNÉ ZMĚNY »
';
                            $added = true;
                        }

                        $specnaz .= '
          ' . $specs['name'] . ' změnit na: ' . $specs['demand_value'].'
          
          ';

                    }

                }

            }

        }


        if (isset($customer) && $customer == 1) {

            $specsquery = $mysqli->query('SELECT s.id, s.name, w.value FROM specs s, warehouse_specs_bridge w WHERE s.product = 1 AND s.service_calendar = 1 AND w.specs_id = s.id AND w.client_id = "' . $search['id'] . '" ORDER BY s.type desc, s.id asc') or die($mysqli->error);

            if (mysqli_num_rows($specsquery) > 0) {

                while ($specs = mysqli_fetch_array($specsquery)) {

                    if (!isset($specs['value']) || $specs['value'] == '') {
                        continue;
                    }

                    $specnaz .= '
• ' . $specs['name'] . ': ' . $specs['value'];

                }

            }

        }

    }

    return $specnaz;

}