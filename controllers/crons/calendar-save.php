<?php

if(empty($_REQUEST['secretcode']) || $_REQUEST['secretcode'] !== "lYspnYd2mYTJm6") {
    die('wrong access code');
}

include_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";
include_once INCLUDES . "/googlelogin.php";
include_once INCLUDES . "/functions.php";
include_once CONTROLLERS . "/calendars/calendar-functions.php";


$query_data = $mysqli->query("SELECT * FROM webhooks_calendar WHERE CAST(finished as DATE) = '0000-00-00' AND started < DATE_SUB( NOW(), INTERVAL 30 MINUTE ) AND retries < 4 ORDER BY id DESC LIMIT 8")or die($mysqli->error);

while($data = mysqli_fetch_assoc($query_data)) {

    $mysqli->query("UPDATE webhooks_calendar SET started = CURRENT_TIMESTAMP(), retries = retries + 1 WHERE id = '".$data['id']."'") or die($mysqli->error);

    $id = $data['event_id'];

    /* array of categories: [event, follow-up, realization, service, task] */

    $result = false;

    try {

        $gcalendar = calendarConnect($data['editor_id']);


        /* zjištění přístupu ke kalendářům
        $calendarList = $gcalendar->calendarList->listCalendarList();

        while(true) {
            foreach ($calendarList->getItems() as $calendarListEntry) {
                echo $calendarListEntry->getSummary();
            }
            $pageToken = $calendarList->getNextPageToken();
            if ($pageToken) {
                $optParams = array('pageToken' => $pageToken);
                $calendarList = $gcalendar->calendarList->listCalendarList($optParams);
            } else {
                break;
            }
        }
        */

        if ($data['category'] === 'event') {

            $result = saveEvent($id);

        } elseif ($data['category'] === 'follow-up') {

            $result = saveFollowUp($id);

        } elseif ($data['category'] === 'realization') {

            $result = saveRealization($id);

        } elseif ($data['category'] === 'service') {

            $result = saveService($id);

        } elseif ($data['category'] === 'task') {

            $result = saveTask($id);

        }

    }catch(Exception $e) {
         echo 'Message: ' .$e->getMessage();

         $errMessage = $mysqli->real_escape_string($e->getMessage());
         $mysqli->query("UPDATE webhooks_calendar SET result = '".$errMessage."' WHERE id = '".$data['id']."'") or die($mysqli->error);

    }

    if($result){
        $mysqli->query("UPDATE webhooks_calendar SET finished = CURRENT_TIMESTAMP(), result = 'SUCCESS' WHERE id = '".$data['id']."'")or die($mysqli->error);
        echo 'success<br>';
    }

}

exit;
