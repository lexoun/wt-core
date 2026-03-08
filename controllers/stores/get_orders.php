<?php

if (!empty($_REQUEST['secretcode']) && $_REQUEST['secretcode'] == "lYspnYd2mYTJm6") {

    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $start = $time;

    if (empty($_REQUEST['shop'])) {
        exit;
    }

    require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";

    $shops_query = $mysqli->query("SELECT * FROM shops WHERE slug = '" . $_REQUEST['shop'] . "'") or die($mysqli->error);
    $shop = mysqli_fetch_assoc($shops_query);

    require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/controllers/stores/woo_base.php";
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);



    $orders_key = array();



    $getOrders = $mysqli->query("SELECT id, order_key FROM orders WHERE order_site = '" . $shop['slug'] . "'") or die($mysqli->error);

    $ok = 0;
    $ko = 0;
    $missing = '';
    $ordersArray = array();

    while ($order = mysqli_fetch_assoc($getOrders)) {

        array_push($ordersArray, $order['order_key']);

    }


    // limit on last week or so...
    $ordersQuery = wc_get_orders(array(
        'limit' => 10,
        'orderby' => 'date',
        'order' => 'DESC',
    ));

    foreach($ordersQuery as $order){

        if (in_array($order->get_order_key(), $ordersArray)) {

            $ok++;
            $missing .= 'je (ok) - '.$order->get_order_key().'<br>';

        } else {

            $ko++;
            $missing .= '!!!!! NENÍ  ' . $order->get_order_key() . '<br>';

            $order_data = $order->get_data(); // The Order data

            $order_id = $order_data['id'];

            $order_date_created = $order_data['date_created']->date('Y-m-d H:i:s');

            date_default_timezone_set('Europe/Prague');


            $datetime1 = new DateTime($order_date_created);

            $since_start = $datetime1->diff(new DateTime(date('Y-m-d H:i:s')));



            $minutes = $since_start->days * 24 * 60;
            $minutes += $since_start->h * 60;
            $minutes += $since_start->i;

            if($minutes > 90){



                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                //$mail->SMTPDebug = 3;                               // Enable verbose debug output
                $mail->CharSet = 'UTF-8';
                $mail->isSMTP();


                $mail->Host = 'mail.webglobe.cz'; // Specify main and backup SMTP servers
                $mail->SMTPAuth = true; // Enable SMTP authentication
                $mail->Username = 'admin@wellnesstrade.cz'; // SMTP username
                $mail->Password = 'RD4ufcLv'; // SMTP password
                $mail->SMTPSecure = 'ssl'; // Enable TLS encryption, `ssl` also accepted
                $mail->Port = 465; // TCP port to connect to

                $mail->From = 'admin@wellnesstrade.cz';
                $mail->FromName = 'WellnessTrade.cz';

                $mail->DKIM_domain = 'wellnesstrade.cz';
                $mail->DKIM_private = $_SERVER['DOCUMENT_ROOT'] . '/admin/config/keys/private.key';
                $mail->DKIM_selector = 'phpmailer';
                $mail->DKIM_passphrase = '';
                $mail->DKIM_identity = 'admin@wellnesstrade.cz';

                echo 'velké špatné';

                $mail->addAddress('becher.filip@gmail.com');


                $mail->isHTML(true); // Set email   format to HTML

                $mail->Subject = 'Problém s objednávkou IDProblém s objednávkou ID:'.$order_id.' E-shop:'.$shop['slug'];
                $mail->Body = '';
                $mail->AltBody = strip_tags($body);

                if (!$mail->send()) {
                    echo 'Message could not be sent.';
                    echo 'Mailer Error: ' . $mail->ErrorInfo;
                }



            }

            /*
            $order_parent_id = $order_data['parent_id'];
            $order_status = $order_data['status'];
            $order_currency = $order_data['currency'];
            $order_version = $order_data['version'];
            $order_payment_method = $order_data['payment_method'];
            $order_payment_method_title = $order_data['payment_method_title'];
            $order_payment_method = $order_data['payment_method'];
            $order_payment_method = $order_data['payment_method'];

            ## Creation and modified WC_DateTime Object date string ##

            // Using a formated date ( with php date() function as method)
            $order_date_created = $order_data['date_created']->date('Y-m-d H:i:s');
            $order_date_modified = $order_data['date_modified']->date('Y-m-d H:i:s');

            // Using a timestamp ( with php getTimestamp() function as method)
            $order_timestamp_created = $order_data['date_created']->getTimestamp();
            $order_timestamp_modified = $order_data['date_modified']->getTimestamp();

            $order_discount_total = $order_data['discount_total'];
            $order_discount_tax = $order_data['discount_tax'];
            $order_shipping_total = $order_data['shipping_total'];
            $order_shipping_tax = $order_data['shipping_tax'];
            $order_total = $order_data['cart_tax'];
            $order_total_tax = $order_data['total_tax'];
            $order_customer_id = $order_data['customer_id']; // ... and so on

            ## BILLING INFORMATION:

            $order_billing_first_name = $order_data['billing']['first_name'];
            $order_billing_last_name = $order_data['billing']['last_name'];
            $order_billing_company = $order_data['billing']['company'];
            $order_billing_address_1 = $order_data['billing']['address_1'];
            $order_billing_address_2 = $order_data['billing']['address_2'];
            $order_billing_city = $order_data['billing']['city'];
            $order_billing_state = $order_data['billing']['state'];
            $order_billing_postcode = $order_data['billing']['postcode'];
            $order_billing_country = $order_data['billing']['country'];
            $order_billing_email = $order_data['billing']['email'];
            $order_billing_phone = $order_data['billing']['phone'];

            ## SHIPPING INFORMATION:

            $order_shipping_first_name = $order_data['shipping']['first_name'];
            $order_shipping_last_name = $order_data['shipping']['last_name'];
            $order_shipping_company = $order_data['shipping']['company'];
            $order_shipping_address_1 = $order_data['shipping']['address_1'];
            $order_shipping_address_2 = $order_data['shipping']['address_2'];
            $order_shipping_city = $order_data['shipping']['city'];
            $order_shipping_state = $order_data['shipping']['state'];
            $order_shipping_postcode = $order_data['shipping']['postcode'];
            $order_shipping_country = $order_data['shipping']['country'];


            // Iterating through each WC_Order_Item_Product objects
            foreach ($order->get_items() as $item_key => $item){

                ## Using WC_Order_Item methods ##

                // Item ID is directly accessible from the $item_key in the foreach loop or
                $item_id = $item->get_id();

                ## Using WC_Order_Item_Product methods ##

                $product = $item->get_product(); // Get the WC_Product object

                $product_id = $item->get_product_id(); // the Product id
                $variation_id = $item->get_variation_id(); // the Variation id

                $item_type = $item->get_type(); // Type of the order item ("line_item")

                $item_name = $item->get_name(); // Name of the product
                $quantity = $item->get_quantity();
                $tax_class = $item->get_tax_class();
                $line_subtotal = $item->get_subtotal(); // Line subtotal (non discounted)
                $line_subtotal_tax = $item->get_subtotal_tax(); // Line subtotal tax (non discounted)
                $line_total = $item->get_total(); // Line total (discounted)
                $line_total_tax = $item->get_total_tax(); // Line total tax (discounted)

                ## Access Order Items data properties (in an array of values) ##
                $item_data = $item->get_data();

                $product_name = $item_data['name'];
                $product_id = $item_data['product_id'];
                $variation_id = $item_data['variation_id'];
                $quantity = $item_data['quantity'];
                $tax_class = $item_data['tax_class'];
                $line_subtotal = $item_data['subtotal'];
                $line_subtotal_tax = $item_data['subtotal_tax'];
                $line_total = $item_data['total'];
                $line_total_tax = $item_data['total_tax'];

                // Get data from The WC_product object using methods (examples)
                $product = $item->get_product(); // Get the WC_Product object

                $product_type = $product->get_type();
                $product_sku = $product->get_sku();
                $product_price = $product->get_price();
                $stock_quantity = $product->get_stock_quantity();

            }*/

        }

    }

    echo $missing;

    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round($finish - $start, 4);

    echo '<pre>Page generated in ' . $total_time . ' seconds.</pre>';

}



