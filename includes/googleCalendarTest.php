<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/admin/vendor/autoload.php';

$client = new Google_Client();
$client->setSubject('kcie94kfi9absq10j3d8uijis8@group.calendar.google.com');
$client->setAuthConfig($_SERVER['DOCUMENT_ROOT'] . '/admin/config/client_secret.json');
$client->setApplicationName("Google Calendar API");
$client->setAccessType('offline');
$client->setScopes([\Google_Service_Calendar::CALENDAR, \Google_Service_Calendar::CALENDAR_EVENTS]);
$client->setPrompt('select_account consent');


print_r($client);


$eventList = $service->events->listEvents(CALENDAR_ID, $eventOptions);



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
