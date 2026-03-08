<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/vendor/autoload.php';

use Automattic\WooCommerce\Client;


if(!empty($_REQUEST['id'])){


    $delivery_method_query = $mysqli->query("SELECT * FROM shops_delivery_methods WHERE id = '".$_REQUEST['id']."'")or die($mysqli->error);
    $delivery_method = mysqli_fetch_assoc($delivery_method_query);

    if($delivery_method['country'] == 'CZ'){
        $zone_id = 1;
    }elseif($delivery_method['country'] == 'SK'){
        $zone_id = 2;
    // EU
    }else{
        $zone_id = 3;
    }


    $shops_query = $mysqli->query("SELECT * FROM shops") or die($mysqli->error);
    while($shop = mysqli_fetch_assoc($shops_query)){

        $woocommerce = new Client(
            $shop['url'],
            $shop['secret_key'],
            $shop['secret_code'],
            [
                'wp_api' => true,
                'version' => 'wc/v3',
                'query_string_auth' => true,

            ]
        );


        // Get instance_id for selected e-shop
        $shippingMethods = json_decode($shop['shipping_methods'], true);

        $finalData = $shippingMethods[$delivery_method['country']];

        foreach($finalData as $single) {

            if($single['id'] == $delivery_method['id']){

                $method_values = $single;
                break;
            }

        }

        $data = [
            'settings' => [
                'cost' => $delivery_method['price']
            ]
        ];

        echo '<pre>';
//        print_r($woocommerce->get('shipping/zones'));

        print_r($woocommerce->put('shipping/zones/'.$zone_id.'/methods/'.$method_values['instance_id'], $data));

        echo '</pre>';


    }


}