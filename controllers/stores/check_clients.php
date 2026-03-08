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

//$blogusers = get_users( 'role=client' );
$blogusers = get_users();
// Array of WP_User objects.

//print_r($blogusers);

if($_REQUEST['shop'] == 'spahouse'){ $customer = 1; }else{ $customer = 0; }


echo '
<table>
<tr>
<td>ID</td>
<td>WOO ID</td>
<td>EMAIL</td>
</tr>';

$demands_query = $mysqli->query("SELECT id, woocommerce_id, email FROM demands WHERE status = 5 AND customer = '".$customer."' AND email != '' AND woocommerce_id = 0") or die($mysqli->error);

while($demand = mysqli_fetch_assoc($demands_query)){

    echo '
    <tr>
<td><a href="../../pages/demands/zobrazit-poptavku?id='.$demand['id'].'" target="_blank">'.$demand['id'].'</a></td>
<td>'.$demand['woocommerce_id'].'</td>
<td>'.$demand['email'].'</td>
</tr>';


}
echo '</table>';
//
//
//
echo '<pre>';



echo '
<br>
WRONG WOOCOMMERCE ID
<table>
<tr>
<td>ADM ID</td>
<td>WOO ID</td>
<td>WEB  ID</td>
<td>WOO EMAIL</td>
<td>WEB EMAIL</td>
</tr>';


$demands_query = $mysqli->query("SELECT id, woocommerce_id, email FROM demands WHERE status = 5 AND customer = '".$customer."' AND email != '' AND woocommerce_id != 0") or die($mysqli->error);

while($demand = mysqli_fetch_assoc($demands_query)){
    $key = '';

    $key = array_search($demand['woocommerce_id'], array_column($blogusers, 'ID'));

    if($key != ''){

    $stuff = '';
    $stuff = json_decode(json_encode($blogusers[$key]->data, true));

        if($stuff->user_email != $demand['email']){
            echo '
                <tr>
            <td><a href="../../pages/demands/zobrazit-poptavku?id='.$demand['id'].'" target="_blank">'.$demand['id'].'</a></td>
            <td>'.$demand['woocommerce_id'].'</td>
            <td>'.$stuff->ID.'</td>
            <td>'.$demand['email'].'</td>
            <td>'.$stuff->user_email.'</td>
            </tr>';

//            $mysqli->query("UPDATE demands SET woocommerce_id = '0' WHERE id = '".$demand['id']."'")or die($mysqli->error);

        }

    }else{

            echo '
                <tr>
            <td><a href="../../pages/demands/zobrazit-poptavku?id='.$demand['id'].'" target="_blank">'.$demand['id'].'</a></td>
            <td>'.$demand['woocommerce_id'].'</td>
            <td> --- </td>
            <td>'.$demand['email'].'</td>
            <td> --- </td>
            </tr>';

//        $mysqli->query("UPDATE demands SET woocommerce_id = '0' WHERE id = '".$demand['id']."'")or die($mysqli->error);



    }


}




echo '</table>';
//
//
//
echo '<pre>';

$blogusers = get_users( 'role=client' );
foreach ( $blogusers as $user) {

    $client_lookup_query = $mysqli->query("SELECT id, woocommerce_id FROM demands WHERE email = '" . $user->user_email . "' AND customer = '".$customer."'") or die($mysqli->error);
    if (mysqli_num_rows($client_lookup_query) == 1) {

        $client_lookup = mysqli_fetch_array($client_lookup_query);

        if($client_lookup['woocommerce_id'] != $user->ID){

            $client_id = $client_lookup['id'];

//            $mysqli->query("UPDATE demands SET woocommerce_id = '".$user->ID."' WHERE id = '".$client_id."'")or die($mysqli->error);

            echo '
    <span style=" margin-left: 40px; float: left;">ERROR - mail, bugged ID'.$client_lookup['woocommerce_id'].' x '.$user->ID.'</span> '.$user->user_email.'<br>';

        }else{


            echo '<br><span style=" margin-left: 40px; float: left;">OK - m</span><br>';
//            echo '
//    <span style="width: 80px; margin-left: 40px; float: left;">'.$client_lookup['woocommerce_id'].' x '.$user->ID.'</span>'.$user->user_email.'<br>';

        }

    }else{

        $check_id_query = $mysqli->query("SELECT id, woocommerce_id, email FROM demands WHERE woocommerce_id = '" . $user->ID . "' AND customer = '".$customer."'") or die($mysqli->error);
        if (mysqli_num_rows($check_id_query) == 1) {

            $cli = mysqli_fetch_array($check_id_query);

            if($cli['email'] != $user->user_email){

                $client_id = $cli['id'];

//            $mysqli->query("UPDATE demands SET woocommerce_id = '".$user->ID."' WHERE id = '".$client_id."'")or die($mysqli->error);

                echo '
    <span style=" margin-left: 40px; float: left;">ERROR - id, bugged mail '.$cli['woocommerce_id'].' '.$cli['email'].' x '.$user->ID.'</span> '.$user->user_email.'<br>';

            }else{


                echo '<br><span style=" margin-left: 40px; float: left;">OK - id</span><br>';
//                echo '<br><span style=" margin-left: 40px; float: left;">OK - id '.$cli['woocommerce_id'].' '.$cli['email'].' x '.$user->ID.'</span> '.$user->user_email.'<br>';

            }


        }else{

        echo '
        <span style="margin-left: 40px; float: left;">ERROR - missing x '.$user->ID.'</span>'.$user->user_email.'<br>';


        }
    }

}
echo '</pre>';