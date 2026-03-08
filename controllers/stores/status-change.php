<?php
ignore_user_abort(true);

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";

use Automattic\WooCommerce\Client;

//require LIBRARIES . '/wootest/vendor/autoload.php';

//require LIBRARIES . '/woocommerce-api/lib/woocommerce-api.php';

if ($_REQUEST['order_id'] != "" && isset($_REQUEST['order_id'])) {


    $status = $_REQUEST['status'];
    $delivery_type = $_REQUEST['delivery_type'];
    $site = $_REQUEST['site'];
    $order_id = $_REQUEST['order_id'];
    $meta_data = [];

    $shop_query = $mysqli->query("SELECT * FROM shops WHERE slug = '$site'");
    $shop = mysqli_fetch_assoc($shop_query);

    $woocommerce = new Client(
        $shop['url'],
        $shop['secret_key'],
        $shop['secret_code'],
        [
            'wp_api' => true,
            'version' => 'wc/v3',
//                'query_string_auth' => true,
        ]
    );


    // any, pending, processing, on-hold, completed, cancelled, refunded, failed and trash
    if ($status == 0) {

        $woo_status = "on-hold";

    } elseif ($status == 1) {

        if ($delivery_type == 'bacs') {

            $woo_status = "pending";

        } else {

            $woo_status = "processing";

        }

    } elseif ($status == 2) {

//        $woo_status = "ready";
        $woo_status = "completed";

    } elseif ($status == 3) {

//        $woo_status = "dispatched";
        $woo_status = "completed";

        $invoice_query = $mysqli->query("SELECT i.id as id, o.order_key FROM orders_invoices i, orders o WHERE i.order_id = o.id AND o.order_id = '$order_id' AND i.status = 'active'");
        if (mysqli_num_rows($invoice_query) > 0) {
            $invoice = mysqli_fetch_array($invoice_query);

            $meta_data = ["key" => "order_invoice",
                "value" => "https://www.wellnesstrade.cz/invoices/spamall/" . $invoice['order_key'] . "/" . $invoice['id'] . ".pdf",
            ];

        }

    } elseif ($status == 4) {

        $woo_status = "cancelled";
//        $woo_status = "storno";

    }

    $data = [
        "status" => $woo_status,
        "meta_data" => [$meta_data],
    ];


//    print_r($data);
//    exit;
    $woocommerce->put('orders/' . $order_id, $data);


}
