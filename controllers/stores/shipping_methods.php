<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/vendor/autoload.php';

use Automattic\WooCommerce\Client;

//
//$o_query = $mysqli->query("SELECT id, order_shipping_method FROM orders ORDER BY order_shipping_method")or die($mysqli->error);
//
//while($order = mysqli_fetch_assoc($o_query)){
//
//    $method_query = $mysqli->query("SELECT * FROM shops_delivery_methods WHERE link_name = '".$order['order_shipping_method']."'")or die($mysqli->error);
//    $method = mysqli_fetch_assoc($method_query);
//
//
//    if(empty($method['id'])){ $method['id'] = '3'; }
//
//    echo $order['order_shipping_method'].' '.$method['id'].'<br>';
//
//    $mysqli->query("UPDATE orders SET order_delivery_type = '".$method['id']."' WHERE id = '".$order['id']."'")or die($mysqli->error);
//
//}
//
//
//
//exit;



if(!empty($_REQUEST['shop']) && !empty($_REQUEST['action']) && $_REQUEST['action'] == 'edit'){


    $shops_query = $mysqli->query("SELECT * FROM shops WHERE slug = '".$_REQUEST['shop']."'") or die($mysqli->error);
    $shop = mysqli_fetch_assoc($shops_query);

    require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/controllers/stores/woo_base.php";


    $delivery_method_query = $mysqli->query("SELECT * FROM shops_delivery_methods WHERE id = '".$_REQUEST['id']."'")or die($mysqli->error);
    $delivery_method = mysqli_fetch_assoc($delivery_method_query);


    // Get instance_id for selected e-shop

    $shippingMethods = json_decode($shop['shipping_methods'], true);

    $finalData = $shippingMethods[$delivery_method['country']];

    foreach($finalData as $single) {

        if($single['id'] == $delivery_method['id']){

            $method_values = $single;
            break;
        }

    }





    // First get the $shipping_method




    // todo Get instance_id for each $shop and while loop


//    $zone2 = WC_Shipping_Zones::get_zone_by( 'instance_id', $method_values['instance_id'] );

    $shipping_method = WC_Shipping_Zones::get_shipping_method($method_values['instance_id']);



    $shipping_method->set_cost( 159 );

    $shipping_method->save();


    print_r($shipping_method);


}




if(!empty($_REQUEST['shop'])){

    /* Shops Start */
    $finalZones = array();
    $shops_query = $mysqli->query("SELECT * FROM shops WHERE slug = '".$_REQUEST['shop']."'") or die($mysqli->error);
    $shop = mysqli_fetch_assoc($shops_query);

    require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/controllers/stores/woo_base.php";

    $zone = new WC_Shipping_Zone(0);
    $otherZone[$zone->get_zone_id()]  = $zone->get_data();
    $otherZone[$zone->get_zone_id()]['formatted_zone_location'] = $zone->get_shipping_methods();
    $otherZone[$zone->get_zone_id()]['shipping_methods'] = $zone->get_shipping_methods();

    $otherZone[$zone->get_zone_id()]['zone_locations'][0]->code = 'EU';

    $zones = array_merge(WC_Shipping_Zones::get_zones(), $otherZone);
    foreach ($zones as $each_zone) {


        $location_code = $each_zone['zone_locations'][0]->code;

        $finalZones[$location_code] = array();

        $shipping_methods = $each_zone['shipping_methods'];
        foreach($shipping_methods as $singleMethod){

            $currentMethod = $mysqli->query("SELECT id FROM shops_delivery_methods WHERE shop_title = '".$singleMethod->title."' AND shop_method_id = '".$singleMethod->id."' AND country = '".$location_code."'") or die($mysqli->error);

            if(mysqli_num_rows($currentMethod) > 0){

                $method = mysqli_fetch_assoc($currentMethod);

                $finalZones[$location_code][] = [
                    'method_id'    => $singleMethod->id,
                    'instance_id' => $singleMethod->instance_id,
                    'title'     => $singleMethod->title,
                    'id'     => $method['id'],
                ];


            }

        }

    }


    $czechServices = ['ceske_sluzby_ulozenka', 'ceske_sluzby_dpd_parcelshop', 'ceske_sluzby_cp', 'ceske_sluzby_zasilkovna'];

    foreach($czechServices as $singleMethod){

        $currentMethod = $mysqli->query("SELECT id, shop_title FROM shops_delivery_methods WHERE shop_method_id = '".$singleMethod."' AND country = 'CZ'") or die($mysqli->error);
        if(mysqli_num_rows($currentMethod) > 0){

            $method = mysqli_fetch_assoc($currentMethod);

            $finalZones['CZ'][] = [
                'method_id'    => $singleMethod,
                'instance_id' => 0,
                'title'     => $method['shop_title'],
                'id'     => $method['id'],
            ];
        }


        $currentMethod = $mysqli->query("SELECT id, shop_title FROM shops_delivery_methods WHERE shop_method_id = '".$singleMethod."' AND country = 'SK'") or die($mysqli->error);
        if(mysqli_num_rows($currentMethod) > 0){

            $method = mysqli_fetch_assoc($currentMethod);

            $finalZones['SK'][] = [
                'method_id'    => $singleMethod,
                'instance_id' => 0,
                'title'     => $method['shop_title'],
                'id'     => $method['id'],
            ];
        }

    }



    print_r($finalZones);

    $encoded = json_encode($finalZones, JSON_UNESCAPED_UNICODE);

    //print_r($encoded);

    //$mysqli->query("UPDATE shops SET shipping_methods = '".$encoded."' WHERE id = '".$shop['id']."'") or die($mysqli->error);

}



if(!empty($_REQUEST['store_new'])){

    $shops_query = $mysqli->query("SELECT * FROM shops WHERE slug = '".$_REQUEST['store_new']."'") or die($mysqli->error);
    $shop = mysqli_fetch_assoc($shops_query);

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


    //echo 'lel';
    //print_r($woocommerce->get('shipping_methods'));

    //exit;
    foreach($woocommerce->get('shipping/zones') as $zone) {

        if($zone->id == 0){ $currentZone = 'EU'; }elseif($zone->id == 1){ $currentZone = 'CZ'; }elseif($zone->id == 2){ $currentZone = 'SK'; }

        foreach ($woocommerce->get('shipping/zones/' . $zone->id . '/methods') as $method) {

            //print_r($method);

            if(empty($method->title)){ $methodTitle = $method->method_title; }else{ $methodTitle = $method->title; }

            $currentMethod = $mysqli->query("SELECT id FROM shops_delivery_methods WHERE shop_title = '".$methodTitle."' AND shop_method_id = '".$method->method_id."' AND country = '".$currentZone."'") or die($mysqli->error);

            if(mysqli_num_rows($currentMethod) > 0) {

                $dbMethod = mysqli_fetch_assoc($currentMethod);

                $finalZones[$currentZone][] = [
                    'method_id' => $method->method_id,
                    'instance_id' => $method->instance_id,
                    'title' => $methodTitle,
                    'id' => $dbMethod['id'],
                ];

            }

        }

    }


    $czechServices = ['ceske_sluzby_ulozenka', 'ceske_sluzby_dpd_parcelshop', 'ceske_sluzby_cp'];

    foreach($czechServices as $singleMethod){

        $currentMethod = $mysqli->query("SELECT id, shop_title FROM shops_delivery_methods WHERE shop_method_id = '".$singleMethod."' AND country = 'CZ'") or die($mysqli->error);
        if(mysqli_num_rows($currentMethod) > 0){

            $method = mysqli_fetch_assoc($currentMethod);

            $finalZones['CZ'][] = [
                'method_id'    => $singleMethod,
                'instance_id' => 0,
                'title'     => $method['shop_title'],
                'id'     => $method['id'],
            ];
        }


        $currentMethod = $mysqli->query("SELECT id, shop_title FROM shops_delivery_methods WHERE shop_method_id = '".$singleMethod."' AND country = 'SK'") or die($mysqli->error);
        if(mysqli_num_rows($currentMethod) > 0){

            $method = mysqli_fetch_assoc($currentMethod);

            $finalZones['SK'][] = [
                'method_id'    => $singleMethod,
                'instance_id' => 0,
                'title'     => $method['shop_title'],
                'id'     => $method['id'],
            ];
        }

    }

    print_r($finalZones);

    $encoded = json_encode($finalZones, JSON_UNESCAPED_UNICODE);

    //print_r($encoded);

    $mysqli->query("UPDATE shops SET shipping_methods = '".$encoded."' WHERE id = '".$shop['id']."'") or die($mysqli->error);

}
