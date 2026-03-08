<?php
include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";

echo 'kek';

$clientquery = $mysqli->query('SELECT id FROM demands WHERE email="' . $_COOKIE['cookie_email'] . '"') or die($mysqli->error);
$client = mysqli_fetch_assoc($clientquery);

$gclient = new Google_Client();
$gclient->setAccessType('offline');
$gclient->setApprovalPrompt('force');
$gclient->setApplicationName("Wellness Trade");
$gclient->setAuthConfigFile($_SERVER['DOCUMENT_ROOT'] . '/admin/config/client_secret.json');
$gclient->setRedirectUri('https://' . $_SERVER['HTTP_HOST'] . '/admin/controllers/oauth-controller.php');
$gclient->addScope("https://www.googleapis.com/auth/calendar");

if (!isset($_GET['code'])) {

    $auth_url = $gclient->createAuthUrl();
    header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
    exit;

} else {

    $gclient->authenticate($_GET['code']);
    $_SESSION['token'] = $gclient->getAccessToken();
//    setcookie("cookie_google_token", $_SESSION['token'], time() + 60 * 60 * 24 * 30, "/");
// to remove
//    print_r($_SESSION['token']);

//    print_r($gclient);
//    $token = json_decode($_SESSION['token']);
    $refresher = 'ready';

    file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/admin/config/tokens/token-" . $client['id'] . ".txt", $_SESSION['token']['refresh_token']);

    $redirect_uri = 'https://' . $_SERVER['HTTP_HOST'] . '/admin/';
    header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
    exit;
}
