<?php
if (session_status() === PHP_SESSION_NONE) {session_start();}
// /// Use Composer Autoload
require_once $_SERVER['DOCUMENT_ROOT'].'/admin/vendor/autoload.php';


// // Import Monolog namespaces
// use Monolog\Logger;
// use Monolog\Handler\StreamHandler;
// use Monolog\Handler\SwiftMailerHandler;
// use Monolog\Formatter\HtmlFormatter;

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// $handler = new \Whoops\Handler\PrettyPageHandler();
// $handler->setEditor('vscode');

// $whoops = new \Whoops\Run();
// $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler(function($exception, $inspector, $run) {
	
// 	// Create the Transport
// 	$transporter = new Swift_SmtpTransport('mail.webglobe.cz', 465, 'ssl');
// 	$transporter->setUsername('admin@wellnesstrade.cz');
// 	$transporter->setPassword('RD4ufcLv');

// 	// Create the Mailer using your created Transport
// 	$mailer = new Swift_Mailer($transporter);

// 	// Create a message
// 	$message = (new Swift_Message('A CRITICAL log was added'));
// 	$message->setFrom(['admin@wellnesstrade.cz' => 'Error Bot']);
// 	$message->setTo(['becher.filip@gmail.com' => 'Filip Becher']);
// 	$message->setContentType("text/html");
// 	$message->setBody($last_error['type'].' _ '.$last_error['message'].' _ '.$last_error['file'].' _ '.$last_error['line']);

// 	$logger = new Logger('WT-App');

// 	$logger->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'].'/admin/storage/production.log', Logger::DEBUG));

// 	$mailerHandler = new SwiftMailerHandler($mailer, $message, Logger::CRITICAL);
// 	$mailerHandler->setFormatter(new HtmlFormatter());
// 	$logger->pushHandler($mailerHandler);
// 	$logger->critical($last_error['type'].' _ '.$last_error['message'].' _ '.$last_error['file'].' _ '.$last_error['line']);

// 	return Handler::DONE;
// }));

// $whoops->register();


if(!isset($mysqli)){
if (session_status() === PHP_SESSION_NONE){ session_start(); }

$showLog = true;
if(isset($_COOKIE['cookie_email']) && $_COOKIE['cookie_email'] == 'becher@saunahouse.cz'){ ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL); }


	$mysqli = new mysqli("localhost", "wellnesstrade", "Wellnesstrade2510", "wellnesstrade");
	if ($mysqli->connect_errno) {
	    echo "Error: Unable to connect to MySQL." . PHP_EOL;
	    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
	    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
	    exit;
	}
	mysqli_set_charset($mysqli, "utf8");
	$mysqli->query("SET NAMES 'utf8'"); 
	SetLocale(LC_ALL, "cs_CZ.utf8");

    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $start = $time;


    $home = 'https://'.$_SERVER['SERVER_NAME'];


    if(isset($_COOKIE['cookie_email']) && $_COOKIE['cookie_email'] != ""){

    $clientquery = $mysqli->query('SELECT * FROM demands WHERE email="' . $_COOKIE['cookie_email'] . '"')or die($mysqli->error);
    $client = mysqli_fetch_assoc($clientquery);

    $address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $client['shipping_id'] . '" WHERE b.id = "' . $client['billing_id'] . '"')or die($mysqli->error);
    $address = mysqli_fetch_assoc($address_query);

    // ČASEM JEN V CONFIG.MAIN
    $full_name = $_SERVER['PHP_SELF'];
    $name_array = explode('/',$full_name);
    $count = count($name_array);
    $page_name = $name_array[$count-1];

    $strip_name = strstr($page_name, '.', true);

    if((isset($_REQUEST['status']) || isset($_REQUEST['state'])) && ($strip_name == 'editace-objednavek' || $strip_name == 'editace-poptavek' || $strip_name == 'editace-zajemcu' || $strip_name == 'editace-servisu')){

        if(isset($_REQUEST['realization'])){

            $strip_name = $strip_name.'?status='.$_REQUEST['status'].'&realization='.$_REQUEST['realization'];

        }elseif(isset($_REQUEST['state'])){

            $strip_name = $strip_name.'?state='.$_REQUEST['state'];

        }else{

            $strip_name = $strip_name.'?status='.$_REQUEST['status'];

        }

    }


    $getCurrentPage = $mysqli->query("SELECT site.id, site.category_id, site.main_id, a.value, site.seo_url, site.name FROM administration_sites site LEFT JOIN administration_accesses a ON ((a.site_id = site.id AND a.site_id != 0) OR (a.category_id = site.category_id AND a.site_id = 0)) AND a.admin_id = '".$client['id']."' AND a.value = '1' LEFT JOIN administration_categories c ON c.id = site.category_id WHERE site.link_url = '$strip_name'")or die($mysqli->error);

    $currentPage = mysqli_fetch_assoc($getCurrentPage);
    // ČASEM JEN V CONFIG.MAIN

    }

    define("INCLUDES", $_SERVER['DOCUMENT_ROOT'] . "/admin/includes");
    define("CONTROLLERS", $_SERVER['DOCUMENT_ROOT'] . "/admin/controllers");
    define("SCRIPTS", $_SERVER['DOCUMENT_ROOT'] . "/admin/scripts");
    define("LIBRARIES", $_SERVER['DOCUMENT_ROOT'] . "/admin/libraries");

    define("MODEL", $_SERVER['DOCUMENT_ROOT'] . "/admin/Model/");
    define("VIEW", $_SERVER['DOCUMENT_ROOT'] . "/admin/View/");
    define("CONTROLLER", $_SERVER['DOCUMENT_ROOT'] . "/admin/Controller/");


}


require_once $_SERVER['DOCUMENT_ROOT'].'/admin/includes/globals.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/admin/includes/core-functions.php';

