<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/* Shops Start */
$finalZones = array();
$shops_query = $mysqli->query("SELECT * FROM shops WHERE slug = '".$_REQUEST['shop']."'") or die($mysqli->error);
$shop = mysqli_fetch_assoc($shops_query);

require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/controllers/stores/woo_customer.php";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$blogusers = get_users( 'role=client' );
// Array of WP_User objects.

echo '<pre>';
foreach ( $blogusers as $user) {

    $client_lookup_query = $mysqli->query("SELECT id, woocommerce_id FROM demands WHERE email = '" . $user->user_email . "'") or die($mysqli->error);
    if (mysqli_num_rows($client_lookup_query) == 1) {

        $client_lookup = mysqli_fetch_array($client_lookup_query);

        if($client_lookup['woocommerce_id'] != $user->ID){

        $client_id = $client_lookup['id'];

//        $mysqli->query("UPDATE demands SET woocommerce_id = '".$user->ID."' WHERE id = '".$client_id."'")or die($mysqli->error);

            echo '
    <span style="width: 80px; margin-left: 40px; float: left;">'.$client_lookup['woocommerce_id'].' x '.$user->ID.'</span>'.$user->user_email.'<br>';

        }else{

            wp_update_user( array ('ID' => $user->ID, 'role' => 'client') ) ;

            echo '
    <span style="width: 80px; margin-left: 40px; float: left;">'.$client_lookup['woocommerce_id'].' x '.$user->ID.'</span>'.$user->user_email.'<br>';

        }

    }else{


        echo '
        <span style="width: 50px; margin-left: 40px; float: left;">____ x '.$user->ID.'</span>'.$user->user_email.'<br>';
    }

}
echo '</pre>';