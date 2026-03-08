<?php

//include_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

require_once $_SERVER['DOCUMENT_ROOT'].'/admin/vendor/autoload.php';

use Google\Client;
use Google\Service\Calendar;

/**
 * Returns an authorized API client.
 * @return Client the authorized client object
 */
function getClient()
{
    $client = new Client();
    $client->setApplicationName('Google Calendar API PHP Quickstart');
    $client->setScopes('https://www.googleapis.com/auth/calendar.events');
    $client->setAuthConfig($_SERVER['DOCUMENT_ROOT'] . '/admin/config/client_secret.json');
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');

    // Load previously authorized token from a file, if it exists.
    // The file token.json stores the user's access and refresh tokens, and is
    // created automatically when the authorization flow completes for the first
    // time.

    //todo change on .json
    $tokenPath = $_SERVER['DOCUMENT_ROOT'] . '/admin/config/tokens/gcal-token.json';
    if (file_exists($tokenPath)) {
        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);
    }

    // If there is no previous token or it's expired.
    if ($client->isAccessTokenExpired()) {
        // Refresh the token if possible, else fetch a new one.
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            $client->setAccessToken($accessToken);

            // Check to see if there was an error.
            if (array_key_exists('error', $accessToken)) {
                throw new Exception(join(', ', $accessToken));
            }
        }
        // Save the token to a file.
        if (!file_exists(dirname($tokenPath))) {
            mkdir(dirname($tokenPath), 0700, true);
        }
        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    }
    return $client;
}


// Get the API client and construct the service object.
$client = getClient();
$service = new Calendar($client);

// Print the next 10 events on the user's calendar.
try{

    $calendarId = 'primary';
    $optParams = array(
        'maxResults' => 10,
        'orderBy' => 'startTime',
        'singleEvents' => true,
        'timeMin' => date('c'),
    );
    $results = $service->events->listEvents($calendarId, $optParams);
    $events = $results->getItems();

    if (empty($events)) {
        print "No upcoming events found.\n";
    } else {
        print "Upcoming events:\n";
        foreach ($events as $event) {
            $start = $event->start->dateTime;
            if (empty($start)) {
                $start = $event->start->date;
            }
            printf("%s (%s)\n", $event->getSummary(), $start);
        }
    }
}
catch(Exception $e) {
    // TODO(developer) - handle error appropriately
    echo 'Message: ' .$e->getMessage();
}



/*
if(empty($client['id'])){
    $clientId = 2126;
}else{
    $clientId = $client['id'];
}

$clientId = 557;

//echo $clientId.'<br>';

$useragent = $_SERVER['HTTP_USER_AGENT'];

if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4))) {

} else {

    $gclient = new Google_Client();
    $gclient->setAccessType('offline');
    $gclient->setApprovalPrompt('force');
    $gclient->setApplicationName("Wellness Trade");
    $gclient->setAuthConfigFile($_SERVER['DOCUMENT_ROOT'] . '/admin/config/client_secret.json');
    $gclient->setRedirectUri('https://' . $_SERVER['HTTP_HOST'] . '/admin/controllers/oauth-controller.php');
    $gclient->addScope("https://www.googleapis.com/auth/calendar");


    if(!isset($client) || $client['id'] == 2126){
        //print_r($gclient);
        //echo "keke";
    }

    //1//0990zsPiPwC1-CgYIARAAGAkSNwF-L9Ir1vuoxSNCNqFb762oZcM7cLKpaOCiJhtGFA25meyXWVlD8MWBxfgmKjx_Y_Vu4iXD5IQ
    //1//096ndI02omYC_CgYIARAAGAkSNwF-L9IrUty0fUzkpAElLTgjtn6NtR8NvxhjK-ft6OWoBsQJqisqprTEY_DgGhq0beW7g8fd2Xs


    if (isset($_COOKIE['cookie_google_token']) && $_COOKIE['cookie_google_token']) {

        if ($gclient->isAccessTokenExpired()) {

            $refreshToken = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/admin/config/tokens/token-" . $clientId . ".txt");              // load previously saved token

            echo $refreshToken;
            try {

                $gclient->refreshToken($refreshToken);

            } catch (Exception $e) {

                $redirect_uri = 'https://' . $_SERVER['HTTP_HOST'] . '/admin/controllers/oauth-controller.php';
                header('Location: ' . $redirect_uri);
                exit;

            }

            $tokens = $gclient->getAccessToken();
            $gclient->setAccessToken($tokens);

            $_SESSION['token'] = $gclient->getAccessToken();
            // setcookie("cookie_google_token", $_SESSION['token']['access_token'], time() + 60 * 60 * 24 * 30, "/"); to
            // to remove
            $token = json_decode($_SESSION['token']['access_token']);
            $refresher = 'ready';

        }

    } else {

        $refreshToken = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/admin/config/tokens/token-" . $clientId . ".txt"); // load previously saved token

        //echo 'bu-composer';
        try {

            $gclient->refreshToken($refreshToken);

        } catch (Exception $e) {

            $redirect_uri = 'https://' . $_SERVER['HTTP_HOST'] . '/admin/controllers/oauth-controller.php';
            header('Location: ' . $redirect_uri);
            exit;

        }

        $tokens = $gclient->getAccessToken();

        try {

            $gclient->setAccessToken($tokens);

        } catch (Exception $e) {

            $redirect_uri = 'https://' . $_SERVER['HTTP_HOST'] . '/admin/controllers/oauth-controller.php';
            header('Location: ' . $redirect_uri);
            exit;

        }

        $gclient->setAccessToken($tokens);

        $_SESSION['token'] = $gclient->getAccessToken();
        // setcookie("cookie_google_token", $_SESSION['token']['access_token'], time() + 60 * 60 * 24 * 30, "/");
        // to remove
        $token = json_decode($_SESSION['token']['access_token']);

    }

    if (isset($_REQUEST['logout'])) {
        unset($_SESSION['token']);
        $gclient->revokeToken();
    }

}
