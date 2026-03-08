<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";

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




//    $shops_query = $mysqli->query("SELECT * FROM shops WHERE slug = '" . $site . "'") or die($mysqli->error);
$shops_query = $mysqli->query("SELECT * FROM shops") or die($mysqli->error);
while($shop = mysqli_fetch_assoc($shops_query)){


//    require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/controllers/stores/woo_base.php";

    $woocommerce = new Client(
        $shop['url'],
        $shop['secret_key'],
        $shop['secret_code'],
        [
            'wp_api' => true,
            'version' => 'wc/v3'
        ]
    );


    $orders = $woocommerce->get('orders', ['per_page' => 10]);

    //    print_r($orders);

    foreach($orders as $orderObject) {

    //        $check_query = $mysqli->query("SELECT id FROM orders WHERE order_key = '" . $order->order_key . "'") or die($mysqli->error);


        $check_query = $mysqli->query("SELECT id FROM orders WHERE id = '" . $orderObject->number . "' OR order_key = '" . $orderObject->order_key . "'") or die($mysqli->error);

        if (mysqli_num_rows($check_query) == 0) {

            echo $shop['slug'].' ... '.$orderObject->number . ' - MISSING<br>';

            // todo admin info mail about missing order

            $fetch = json_encode($orderObject);
            $body = $mysqli->real_escape_string($fetch);

            $order = json_decode($fetch, true);
            $site = $shop['slug'];

            // $order = array(), $body = json encoded
            include $_SERVER['DOCUMENT_ROOT'] . "/admin/controllers/stores/new-order-hook.php";


        } elseif (mysqli_num_rows($check_query) == 1) {

            echo $shop['slug'].' ... '.$orderObject->number . ' - OK<br>';

        } elseif (mysqli_num_rows($check_query) > 1) {

            echo $shop['slug'].' ... '.$orderObject->number . ' - VÍCE JAK JEDNA<br>';

        }


    //        echo mysqli_num_rows($check_query);

    }

    //   print_r($woocommerce);
    //


}

