<?php

include_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";
//include INCLUDES . "/googlelogin.php";
include_once INCLUDES . "/functions.php";

$gclient = new Google_Client();
$gclient->setAuthConfigFile($_SERVER['DOCUMENT_ROOT'] . '/admin/config/client_secret.json');
$gclient->setRedirectUri('https://' . $_SERVER['HTTP_HOST'] . '/admin/');

$refreshToken = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/admin/config/tokens/token-2126.txt"); // load previously saved token
$gclient->refreshToken($refreshToken);
$tokens = $gclient->getAccessToken();

$gclient->setAccessToken($tokens);

$service = new Google_Service_Calendar($gclient);
$calendarId = 'kcie94kfi9absq10j3d8uijis8@group.calendar.google.com';

$calendarListEntry = $service->calendarList->get($calendarId);

$start_filter = date("c", strtotime(date("Y-m-d. H:i:s")));
$end_filter = date("c", strtotime(date('Y-m-d H:i:s', strtotime(date("Y-m-d H:i:s", time()) . " + 365 day"))));

$eventOptions = array("orderBy" => "startTime",
    "singleEvents" => true,
    "timeMin" => $start_filter,
    "timeMax" => $end_filter);

$eventList = $service->events->listEvents($calendarId, $eventOptions);

$idsArray = array();
$i = 0;
while (true) {
    foreach ($eventList->getItems() as $event) {

        $i++;

        if ($event->start->dateTime != '') { $startDate = $event->start->dateTime;} else { $startDate = $event->start->date;}
        if ($event->end->dateTime != '') { $endDate = $event->end->dateTime;} else { $endDate = $event->end->date;}


        $lookup_query1 = $mysqli->query("SELECT * FROM services WHERE gcalendar = '".$event->id."' AND state != 'canceled'")or die($mysqli->error);
        $lookup_query2 = $mysqli->query("SELECT * FROM demands WHERE gcalendar = '".$event->id."'")or die($mysqli->error);
        $lookup_query3 = $mysqli->query("SELECT * FROM tasks WHERE gcalendar = '".$event->id."'")or die($mysqli->error);
        $lookup_query4 = $mysqli->query("SELECT * FROM dashboard_texts WHERE gcalendar = '".$event->id."'")or die($mysqli->error);
        $lookup_query5 = $mysqli->query("SELECT * FROM demands_mails_history WHERE gcalendar = '".$event->id."'")or die($mysqli->error);
        $lookup_query6 = $mysqli->query("SELECT * FROM demands_double_realization WHERE gcalendar = '".$event->id."'")or die($mysqli->error);
//
//
        if(mysqli_num_rows($lookup_query1) > 0){ $state = '..... true_ '; }
        elseif(mysqli_num_rows($lookup_query2) > 0){ $state = '..... true_ '; }
        elseif(mysqli_num_rows($lookup_query3) > 0){ $state = '..... true_ '; }
        elseif(mysqli_num_rows($lookup_query4) > 0){ $state = '..... true_ '; }
        elseif(mysqli_num_rows($lookup_query5) > 0){ $state = '..... true_ '; }
        elseif(mysqli_num_rows($lookup_query6) > 0){ $state = '..... true_ '; }
        else{

            calendarDelete($event->id); $state = 'xx. falsee___ ';

        }
//        echo $state.$i . ': ' . $startDate . ' - ' . $endDate . ' ... ' . $event->getSummary() . ' ... '.$event->id.'<br>';




        array_push($idsArray, $event->id);


    }


    $pageToken = $eventList->getNextPageToken();
    if ($pageToken) {
        $optParams = array('pageToken' => $pageToken);
        $eventList = $service->events->listEvents($calendarId, $optParams);
    } else {
        break;
    }
}


$lookup_query1 = $mysqli->query("SELECT * FROM services WHERE date >= now() AND state != 'canceled'")or die($mysqli->error);
while($lookup = mysqli_fetch_assoc($lookup_query1)){

    $id = $lookup['id'];

    if(in_array($lookup['gcalendar'], $idsArray) && !empty($lookup['gcalendar'])){

        // todo check if data is fine?
//        echo $lookup['id'].': SERVIS in array<br>';

    }else{
//        gcalendar is empty
//        echo $lookup['id'].': SERVIS empty gcalendar or not in array<br>';
        require(CONTROLLERS."/calendars/calendar-service.php");
    }

}

$lookup_query2 = $mysqli->query("SELECT * FROM demands WHERE realization >= now()")or die($mysqli->error);
while($lookup = mysqli_fetch_assoc($lookup_query2)){

    $id = $lookup['id'];

    if(in_array($lookup['gcalendar'], $idsArray) && !empty($lookup['gcalendar'])){

        // todo check if data is fine?
//            echo $lookup['id'].': REAL in array<br>';

    }else{
//        gcalendar is empty
//        echo $lookup['id'].': REAL empty gcalendar or not in array<br>';
        require(CONTROLLERS."/calendars/calendar-realization.php");
    }

}


$lookup_query3 = $mysqli->query("SELECT * FROM tasks WHERE due >= now()")or die($mysqli->error);
while($lookup = mysqli_fetch_assoc($lookup_query3)){

    $id = $lookup['id'];

    if(in_array($lookup['gcalendar'], $idsArray) && !empty($lookup['gcalendar'])){

        // todo check if data is fine?
//        echo $lookup['id'].': TASK in array<br>';

    }else{
//        gcalendar is empty
//        echo $lookup['id'].': TASK empty gcalendar or not in array<br>';
        require(CONTROLLERS."/calendars/calendar-task.php");
    }

}


$lookup_query4 = $mysqli->query("SELECT * FROM dashboard_texts WHERE date >= now()")or die($mysqli->error);
while($lookup = mysqli_fetch_assoc($lookup_query4)){

    $id = $lookup['id'];

    if(in_array($lookup['gcalendar'], $idsArray)){

        // todo check if data is fine?
//        echo $lookup['id'].': EVENT in array<br>';

    }else{
//        gcalendar is empty
//        echo $lookup['id'].': EVENT empty gcalendar or not in array<br>';
        require(CONTROLLERS."/calendars/calendar-event.php");
    }

}


$lookup_query5 = $mysqli->query("SELECT * FROM demands_mails_history WHERE date_time >= now()")or die($mysqli->error);
while($lookup = mysqli_fetch_assoc($lookup_query5)){

    $id = $lookup['id'];

    if(in_array($lookup['gcalendar'], $idsArray) && !empty($lookup['gcalendar'])){

        // todo check if data is fine?
//        echo $lookup['id'].': FOLLOW in array<br>';

    }else{
//        gcalendar is empty
//        echo $lookup['id'].': FOLLOW empty gcalendar or not in array<br>';
        require(CONTROLLERS."/calendars/calendar-follow-up.php");
    }

}



$lookup_query6 = $mysqli->query("SELECT * FROM demands_double_realization WHERE startdate >= now()")or die($mysqli->error);
while($lookup = mysqli_fetch_assoc($lookup_query6)){

    $id = $lookup['id'];

    if(in_array($lookup['gcalendar'], $idsArray) && !empty($lookup['gcalendar'])){

        // todo check if data is fine?
//        echo $lookup['id'].': REAL DOUBLE in array<br>';

    }else{
//        gcalendar is empty
//        echo $lookup['id'].': REAL DOUBLE empty gcalendar or not in array<br>';
        require(CONTROLLERS."/calendars/calendar-realization.php");
    }

}


$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$total_time = round(($finish - $start), 4);

echo 'PHP '.PHP_VERSION.' | Page generated in ' . $total_time . ' seconds.';