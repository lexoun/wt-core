<?php
use Salamek\Zasilkovna as Zasilkovna;

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";
include_once INCLUDES . "/accessories-functions.php";

$id = $_REQUEST['id'];

//print_r($orders);

$orderquery = $mysqli->query('SELECT *, DATE_FORMAT(order_date, "%d. %M %Y") as dateformated, DATE_FORMAT(order_date, "%H:%i:%s") as hoursmins FROM orders WHERE id="' . $id . '"') or die($mysqli->error);


/*

addresses_billing
addresses_shipping
orders_products_bridge

 */

if (mysqli_num_rows($orderquery) > 0) {

    $order = mysqli_fetch_array($orderquery);

    $address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $order['shipping_id'] . '" WHERE b.id = "' . $order['billing_id'] . '"') or die($mysqli->error);
    $address = mysqli_fetch_assoc($address_query);


    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'separate_order') {

        $old_order_query = $mysqli->query('SELECT * FROM orders WHERE id="' . $id . '"') or die($mysqli->error);
        $old_order = mysqli_fetch_array($old_order_query);

        $address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $old_order['shipping_id'] . '" WHERE b.id = "' . $old_order['billing_id'] . '"') or die($mysqli->error);
        $address = mysqli_fetch_assoc($address_query);

        $insert_billing = $mysqli->query("INSERT INTO addresses_billing (billing_company, billing_ico, billing_dic, billing_degree, billing_name, billing_surname, billing_street, billing_city, billing_zipcode, billing_country, billing_phone, billing_email) VALUES ('" . $address['billing_company'] . "', '" . $address['billing_ico'] . "', '" . $address['billing_dic'] . "', '" . $address['billing_degree'] . "', '" . $address['billing_name'] . "', '" . $address['billing_surname'] . "', '" . $address['billing_street'] . "', '" . $address['billing_city'] . "', '" . $address['billing_zipode'] . "', '" . $address['billing_country'] . "', '" . $address['billing_phone'] . "', '" . $address['billing_email'] . "')") or die($mysqli->error);

        $billing_id = $mysqli->insert_id;

        if ($old_order['shipping_id'] != 0) {

            $insert_shipping = $mysqli->query("INSERT INTO addresses_shipping (shipping_company, shipping_ico, shipping_dic, shipping_degree, shipping_name, shipping_surname, shipping_street, shipping_city, shipping_zipcode, shipping_country) VALUES ('" . $address['shipping_company'] . "', '" . $address['shipping_ico'] . "', '" . $address['shipping_dic'] . "', '" . $address['shipping_degree'] . "', '" . $address['shipping_name'] . "', '" . $address['shipping_surname'] . "', '" . $address['shipping_street'] . "', '" . $address['shipping_city'] . "', '" . $address['shipping_zipcode'] . "', '" . $address['shipping_country'] . "')") or die($mysqli->error);

            $shipping_id = $mysqli->insert_id;

        }

        $insert = $mysqli->query("INSERT INTO orders (billing_id, shipping_id, vat, order_date, order_site, order_tracking_number, client_id, customer_email, customer_phone, order_status, customer_note, order_shipping_method, payment_method, delivery_price, location_id)
	VALUES ('" . $billing_id . "', '" . $shipping_id . "', '" . $old_order['vat'] . "', now(), '" . $old_order['order_site'] . "', '" . $old_order['order_tracking_number'] . "', '" . $old_order['client_id'] . "', '" . $old_order['customer_email'] . "', '" . $old_order['customer_phone'] . "', '" . $old_order['order_status'] . "', '" . $old_order['customer_note'] . "', '" . $old_order['order_shipping_method'] . "', '" . $old_order['payment_method'] . "', '" . $old_order['delivery_price'] . "', '" . $old_order['location_id'] . "')") or die($mysqli->error);

        $old_id = $mysqli->insert_id;
        $new_id = '10'.$old_id;

        $currency = currency($old_order['order_currency']);

        $mysqli->query("UPDATE orders SET id = '".$new_id."', reference_number = '".$new_id."' WHERE old_id = '".$old_id."'")or die($mysqli->error);

        $overallcena = 0;
        $overall_purchase = 0;

        $overallcena_old = 0;
        $overall_purchase_old = 0;

        $orders_products_bridge = $mysqli->query("SELECT * FROM orders_products_bridge WHERE aggregate_id = '" . $old_order['id'] . "' AND aggregate_type = 'order'");

        while ($bridge = mysqli_fetch_array($orders_products_bridge)) {

            if ($bridge['variation_id'] != 0) {

                $product_query = $mysqli->query("SELECT *, s.id as ajdee FROM products p, products_variations s WHERE p.id = '" . $bridge['product_id'] . "' AND p.id = s.product_id AND s.id = '" . $bridge['variation_id'] . "'");
                $product = mysqli_fetch_array($product_query);

                $sku = $product['sku'];

            } else {

                $product_query = $mysqli->query("SELECT * FROM products p, products_sites s WHERE p.id = '" . $bridge['product_id'] . "' AND p.id = s.product_id");

                $product = mysqli_fetch_array($product_query);

                $price = number_format($product['price'], 0, ',', ' ') . $currency['sign'];

                $sku = $product['code'];
            }

            if ($_POST[$sku] == 'yes') {

                $update_products_bridge = $mysqli->query("UPDATE orders_products_bridge SET aggregate_id = '$new_id' WHERE id = '" . $bridge['id'] . "' AND aggregate_type = 'order'");

                $overallcena = $overallcena + ($bridge['quantity'] * $bridge['price']);

                $overall_purchase = $overall_purchase + $bridge['purchase_price'];

            } else {

                $overallcena_old = $overallcena_old + ($bridge['quantity'] * $bridge['price']);

                $overall_purchase_old = $overall_purchase_old + $bridge['purchase_price'];

            }

        }

        $coeficient = vat_coeficient($old_order['vat']);


        $overallcena = $overallcena + $old_order['delivery_price'];
        $price = get_price($overallcena, $coeficient);

// old to delete
//        $vat = number_format($overallcena - $overallcena / $coeficient, 2, '.', '');
//        $total_without_vat = number_format($overallcena - $vat, 2, '.', '');
//        $order_price = $overallcena;
// old to delete

        $mysqli->query("UPDATE orders SET total_vat = '".$price['vat']."', total_without_vat = '".$price['without_vat']."', total = '".$price['single']."', order_purchase = '$overall_purchase' WHERE id = '$new_id'");




        $overallcena_old = $overallcena_old + $old_order['delivery_price'];
        $price = get_price($overallcena_old, $coeficient);

// old to delete
//        $vat_old = number_format($overallcena_old * $coeficient, 2, '.', '');
//        $total_without_vat_old = number_format($overallcena_old - $vat_old, 2, '.', '');
//        $order_price_old = $overallcena_old;
// old to delete

        $update = $mysqli->query("UPDATE orders SET total_vat = '".$price['vat']."', total_without_vat = '".$price['without_vat']."', total = '".$price['single']."', order_purchase = '$overall_purchase_old' WHERE id = '$id'");

        Header("Location:https://www.wellnesstrade.cz/admin/pages/orders/zobrazit-objednavku?id=" . $id . "&success=separate_order");

    }

    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'duplicate') {

        $old_order_query = $mysqli->query('SELECT * FROM orders WHERE id="' . $id . '"') or die($mysqli->error);
        $old_order = mysqli_fetch_array($old_order_query);

        $address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $old_order['shipping_id'] . '" WHERE b.id = "' . $old_order['billing_id'] . '"') or die($mysqli->error);


        $mysqli->query("INSERT INTO addresses_billing (billing_company, billing_ico, billing_dic, billing_degree, billing_name, billing_surname, billing_street, billing_city, billing_zipcode, billing_country, billing_phone, billing_email) VALUES ('" . $address['billing_company'] . "', '" . $address['billing_ico'] . "', '" . $address['billing_dic'] . "', '" . $address['billing_degree'] . "', '" . $address['billing_name'] . "', '" . $address['billing_surname'] . "', '" . $address['billing_street'] . "', '" . $address['billing_city'] . "', '" . $address['billing_zipcode'] . "', '" . $address['billing_country'] . "', '" . $address['billing_phone'] . "', '" . $address['billing_email'] . "')") or die($mysqli->error);

        $billing_id = $mysqli->insert_id;
		$shipping_id = 0;
        if ($old_order['shipping_id'] != 0) {

            $mysqli->query("INSERT INTO addresses_shipping (shipping_company, shipping_ico, shipping_dic, shipping_degree, shipping_name, shipping_surname, shipping_street, shipping_city, shipping_zipcode, shipping_country) VALUES ('" . $address['shipping_company'] . "', '" . $address['shipping_ico'] . "', '" . $address['shipping_dic'] . "', '" . $address['shipping_degree'] . "', '" . $address['shipping_name'] . "', '" . $address['shipping_surname'] . "', '" . $address['shipping_street'] . "', '" . $address['shipping_city'] . "', '" . $address['shipping_zipcode'] . "', '" . $address['shipping_country'] . "')") or die($mysqli->error);

            $shipping_id = $mysqli->insert_id;

        }

        $mysqli->query("INSERT INTO orders (order_purchase, total_rounded, total, total_without_vat, total_vat, billing_id, shipping_id, vat, order_date, order_site, order_tracking_number, client_id, customer_email, customer_phone, order_status, customer_note, order_shipping_method, payment_method, delivery_price, location_id)
	VALUES ('" . $old_order['order_purchase'] . "', '" . $old_order['total_rounded'] . "', '" . $old_order['total'] . "', '" . $old_order['total_without_vat'] . "', '" . $old_order['total_vat'] . "', '" . $billing_id . "', '" . $shipping_id . "', '" . $old_order['vat'] . "', now(), '" . $old_order['order_site'] . "', '" . $old_order['order_tracking_number'] . "', '" . $old_order['client_id'] . "', '" . $old_order['customer_email'] . "', '" . $old_order['customer_phone'] . "', '" . $old_order['order_status'] . "', '" . $old_order['customer_note'] . "', '" . $old_order['order_shipping_method'] . "', '" . $old_order['payment_method'] . "', '" . $old_order['delivery_price'] . "', '" . $old_order['location_id'] . "')") or die($mysqli->error);

        $old_id = $mysqli->insert_id;
        $id = '10'.$old_id;



        $mysqli->query("UPDATE orders SET id = '".$id."', reference_number = '".$id."' WHERE old_id = '".$old_id."'")or die($mysqli->error);


		$overallcena = 0;
		$overall_purchase = 0;

        $orders_products_bridge = $mysqli->query("SELECT * FROM orders_products_bridge WHERE aggregate_id = '" . $old_order['id'] . "' AND aggregate_type = 'order'");

        while ($products_bridge = mysqli_fetch_assoc($orders_products_bridge)) {

            if ($old_order['order_status'] != 4) {

                if ($products_bridge['variation_id'] != 0) {

                    $product_query = $mysqli->query("SELECT *, s.id as ajdee FROM products p, products_variations s WHERE p.id = '" . $products_bridge['product_id'] . "' AND p.id = s.product_id AND s.id = '" . $products_bridge['variation_id'] . "'")or die($mysqli->error);
                    $product = mysqli_fetch_assoc($product_query);
                    $sku = $product['sku'];

                } else {

                    $product_query = $mysqli->query("SELECT * FROM products p LEFT JOIN products_sites s ON p.id = s.product_id WHERE p.id = '" . $products_bridge['product_id'] . "'")or die($mysqli->error);

                    $product = mysqli_fetch_assoc($product_query);
                    $sku = $product['code'];

                }

                // todo??
                $order_client = $products_bridge['client_id'];

                $stock_allocation['posterino'] = $sku;
                $stock_allocation['id'] = $id;
                $stock_allocation['bridge'] = 'orders_products_bridge';
                $stock_allocation['id_identify'] = 'order_id';
                $stock_allocation['quantity'] = $products_bridge['quantity'];
                $stock_allocation['location'] = $products_bridge['location_id'];
                $stock_allocation['type'] = 'order';
                $stock_allocation['quantity'] = $products_bridge['quantity'];
                $stock_allocation['total_quantity'] = $products_bridge['quantity'];

                $stock_allocation['price'] = product_price(
                    $products_bridge['price'],
                    $products_bridge['original_price'],
                    $old_order['vat'],
                    $old_order['vat'],
                    $products_bridge['discount']
                );


                // přiřezení k nové objednávce
                include_once CONTROLLERS . "/product-stock-update.php";
                $response = stock_allocate($stock_allocation);

            } else {

                $mysqli->query("INSERT INTO orders_products_bridge (
                                    aggregate_id, 
                                    aggregate_type, 
                                    product_id, 
                                    product_name, 
                                    variation_id, 
                                    variation_values, 
                                    quantity, 
                                    client_id, 
                                    reserved, 
                                    price, 
                                    purchase_price, 
                                    location_id, 
                                    discount) VALUES (
                                  '$id', 
                                  'order', 
                                  '" . $products_bridge['product_id'] . "', 
                                  '" . $products_bridge['product_name'] . "', 
                                  '" . $products_bridge['variation_id'] . "', 
                                  '" . $products_bridge['variation_values'] . "', 
                                  '" . $products_bridge['quantity'] . "', 
                                  '" . $products_bridge['client_id'] . "', 
                                  '0', 
                                  '" . $products_bridge['price'] . "', 
                                  '" . $products_bridge['purchase_price'] . "', 
                                  '" . $products_bridge['location_id'] . "', 
                                  '" . $products_bridge['discount'] . "')") or die($mysqli->error);

            }

        }

        Header("Location:https://www.wellnesstrade.cz/admin/pages/orders/zobrazit-objednavku?id=" . $id . "&success=duplicate");

    }



    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'change_status') {

        $update = $mysqli->query("UPDATE orders SET order_status = '" . $_POST['status'] . "', order_tracking_number = '" . $_POST['order_tracking_number'] . "' WHERE id = '$id'");

        // NOVÝ STAV OBJEDNÁVKY = STORNO, PŮVODNÍ STAV = JAKÝKOLIV

        include CONTROLLERS . "/product-stock-controller.php";

        if (isset($_POST['status']) && $_POST['status'] == 4 && $order['order_status'] != 4) {

            $topupquery = $mysqli->query("SELECT b.product_id, b.reserved, p.customer, b.variation_id, b.location_id 
                FROM orders_products_bridge b, products p 
                WHERE p.id = b.product_id AND b.aggregate_id = '" . $_REQUEST['id'] . "' AND b.aggregate_type = 'order'") or die($mysqli->error);
            while ($topup = mysqli_fetch_array($topupquery)) {

                $quantity = $topup['reserved'];

                if ($quantity > 0) {

                    product_update($topup['product_id'], $topup['variation_id'], $topup['location_id'], $quantity, $client['id'], 'order_cancel', $id);

                }

                $update = $mysqli->query("UPDATE orders_products_bridge SET reserved = '0' WHERE product_id = '" . $topup['product_id'] . "' AND aggregate_id = '" . $_REQUEST['id'] . "' AND aggregate_type = 'order'");

            }

            // NOVÝ STAV OBJEDNÁVKY = JAKÝKOLIV, PŮVODNÍ STAV = STORNO

        } elseif ($_POST['status'] < 4 && $order['order_status'] == 4) {

            $search_query = $mysqli->query("SELECT b.product_id, b.variation_id, b.quantity, b.location_id, s.instock 
                FROM orders_products_bridge b, products_stocks s 
                WHERE s.product_id = b.product_id AND s.variation_id = b.variation_id AND s.location_id = b.location_id AND b.aggregate_id = '" . $_REQUEST['id'] . "' AND b.aggregate_type = 'order'") or die($mysqli->error);

            while ($search = mysqli_fetch_array($search_query)) {

                if ($search['quantity'] > $search['instock']) {

                    $reserve = $search['instock'];

                } else {

                    $reserve = $search['quantity'];

                }

                $mysqli->query("UPDATE orders_products_bridge SET reserved = '$reserve' WHERE product_id = '" . $search['product_id'] . "' AND variation_id = '" . $search['variation_id'] . "' AND aggregate_id = '" . $_REQUEST['id'] . "' AND aggregate_type = 'order'") or die($mysqli->error);
                $mysqli->query("UPDATE products_stocks SET instock = instock - $reserve WHERE product_id = '" . $search['product_id'] . "' AND variation_id = '" . $search['variation_id'] . "' AND location_id IN (SELECT id as location_id FROM shops_locations WHERE id = '" . $search['location_id'] . "')") or die($mysqli->error);

                api_product_update($search['product_id']);

            }

        }

        /* --- API ORDER STATUS UPDATE */
        if ($_POST['status'] != $order['order_status'] && $order['order_site'] != 'wellnesstrade' && $order['id'] != 0 || $_POST['status'] == 4) {


            // todo

            $url = 'https://www.wellnesstrade.cz/admin/controllers/stores/status-change';

            $content = '?order_id=' . $order['id'] . '&status=' . $_POST['status'] . '&delivery_type=' . $order['payment_method'] . '&site=' . $order['order_site'];

//            echo $content;
            /*
            $parts = parse_url($url);
            $fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 30);
            $out = "GET " . $parts['path'] . $content . " HTTP/1.1\r\n";
            $out .= "Host: " . $parts['host'] . "\r\n";
            $out .= "Content-Length: 0" . "\r\n";
            $out .= "Connection: Close\r\n\r\n";

            fwrite($fp, $out);
            fclose($fp);
            */
            // todo

        }

        /* --- API ORDER STATUS UPDATE */

        if (isset($_POST['send_mail']) && $_POST['send_mail'] == 'yes') {

            if (isset($_POST['enable_custom']) && $_POST['enable_custom'] == 'yes') {

                $alternate_text = $_POST['custom_text'];

            }

            include INCLUDES . "/order_status_emails.php";


            Header("Location:https://www.wellnesstrade.cz/admin/pages/orders/zobrazit-objednavku?id=" . $id . "&success=change_status&has_mail=true");
            exit;

        }

        if (isset($_REQUEST['link'])) {

            Header('Location:https://www.wellnesstrade.cz/admin/pages/orders/editace-objednavek?state=' . $_REQUEST['link'] . '?success=change_status');

        }else{

            Header("Location:https://www.wellnesstrade.cz/admin/pages/orders/zobrazit-objednavku?id=" . $id . "&success=change_status&has_mail=false");

        }
        exit;

    }

    $pagetitle = 'Objednávka ' . $order['id'];

    if (isset($order['order_status']) && $order['order_status'] == 0) {
        $bread1 = "Nezpracované objednávky";
        $abread1 = "editace-objednavek?state=0";
    } elseif (isset($order['order_status']) && $order['order_status'] == 1) {
        $bread1 = "Přijaté objednávky";
        $abread1 = "editace-objednavek?state=1";
    } elseif (isset($order['order_status']) && $order['order_status'] == 2) {
        $bread1 = "Připravené objednávky";
        $abread1 = "editace-objednavek?state=2";

    } elseif (isset($order['order_status']) && $order['order_status'] == 3) {
        $bread1 = "Vyexpedované objednávky";
        $abread1 = "editace-objednavek?state=3";

    } else {
        $bread1 = "Stornované objednávky";
        $abread1 = "editace-objednavek?state=4";

    }

    
    $currency = currency($order['order_currency']);

    include VIEW . '/default/header.php';
    ?>

<script type="text/javascript">
jQuery(document).ready(function($)
{

$('.radio').click(function() {
   if($("input:radio[class='saunaradio']").is(":checked")) {


	$('.virivkens').hide( "slow");
	$('.saunkens').show( "slow");
   }
     if($("input:radio[class='virivkaradio']").is(":checked")) {


   	$('.saunkens').hide( "slow");
$('.virivkens').show( "slow");
   }
});
 var cloneCount = 0;
$('#duplicatevirivka').click(function() {
 cloneCount = cloneCount + 1;
      $('#virdup').clone().attr('id', 'virdup'+ cloneCount).insertAfter('[id^=virdup]:last');
      $('#virdup'+ cloneCount).find('#virivkadup').attr('name', 'zbozickovirivka'+ cloneCount);
      $('#virdup'+ cloneCount).find('#field-2').attr('name', 'cenickavirivka'+ cloneCount);

});

 var cloneCount2 = 0;
$('#duplicatesauna').click(function() {
 cloneCount2 = cloneCount2 + 1;
      $('#saundup').clone().attr('id', 'saundup'+ cloneCount2).insertAfter('[id^=saundup]:last');
      $('#saundup'+ cloneCount2).find('#saunadup').attr('name', 'zbozickosauna'+ cloneCount2);
      $('#saundup'+ cloneCount2).find('#field-2').attr('name', 'cenickasauna'+ cloneCount2);

});

});


</script>

	<div class="panel panel-primary" data-collapsed="0">



						<div class="panel-body">
<div class="invoice">

	<div class="row">

        <div class="col-sm-12 invoice-left">


            <div class="col-sm-3" style="padding: 0;">


                <h3><span style="font-size: 18px;">OBJEDNÁVKA Č.</span> #<?= $order['id'] ?>
                <br>
                <span style=" font-weight: 400;font-size: 15px; color: #666;"><?= $order['dateformated'] . ', ' . $order['hoursmins'] ?></span></h3>
            </div>

            <div class="col-sm-6">
                <ol class="breadcrumb bc-2" style="margin-top: 16px; margin-bottom: 0; float:left;">
                    <li <?php if (isset($order['order_status']) && $order['order_status'] == 0) {echo 'class="active"';}?>> <?php if (isset($order['order_status']) && $order['order_status'] == 0) {echo '<strong>Nezpracovaná</strong>';} else { ?>Nezpracovaná<?php } ?></li>
                    <li <?php if (isset($order['order_status']) && $order['order_status'] == 1) {echo 'class="active"';}?>> <?php if (isset($order['order_status']) && $order['order_status'] == 1) {echo '<strong>V řešení</strong>';} else { ?>V řešení<?php } ?></li>
                    <li <?php if (isset($order['order_status']) && $order['order_status'] == 2) {echo 'class="active"';}?>> <?php if (isset($order['order_status']) && $order['order_status'] == 2) {echo '<strong>Připravená</strong>';} else { ?>Připravená<?php } ?></li>
                    <li <?php if (isset($order['order_status']) && $order['order_status'] == 3) {echo 'class="active"';}?>> <?php if (isset($order['order_status']) && $order['order_status'] == 3) {echo '<strong>Vyexpedovaná</strong>';} else { ?>Vyexpedovaná<?php } ?></li>
                    <li <?php if (isset($order['order_status']) && $order['order_status'] == 4) {echo 'class="active"';}?>> <?php if (isset($order['order_status']) && $order['order_status'] == 4) {echo '<strong>Stornovaná</strong>';} else { ?>Stornovaná<?php } ?></li>
                </ol>

            </div>

            <div class="col-sm-3 invoice-right" style="padding-top: 14px; padding-right: 34px;">

                    <a href="#">
                    <?php if (isset($order['order_site']) && $order['order_site'] == 'wellnesstrade') { ?>
                    <img src="https://www.wellnesstrade.cz/wp-content/uploads/2015/03/logoblack.png" style="padding-top: 21px;" width="160"/>
                    <?php } else { ?>
                    <img src="https://www.wellnesstrade.cz/admin/assets/images/<?= $order['order_site'] ?>-shop.png" width="160"/>
                    <?php } ?>
                </a>
            </div>

        </div>

	</div>

    <hr class="margin" style="margin: 0 0 20px;" />

    <div class="col-sm-12" style="padding: 0; margin-bottom: 34px; float: left;">

        <div class="col-sm-7 invoice-left" style="width: 28%; padding: 0;">

            <div class="col-sm-12" style="padding: 0; padding-right: 16px;">


                <div class="col-sm-12 alert alert-default" style="background-color: #f7f7f7;padding-bottom: 0;padding: 10px 0; font-size: 12px;">

                        <table class="table table-stripped table-hover" style="width: 100%;  text-align: left; ">
                            <thead>
                            <tr>
                                <th>
                                    <h4>Informace o zákazníkovi</h4>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if ($order['client_id'] != 0) {

                                $client_address_query = $mysqli->query('SELECT c.id, b.billing_name, b.billing_surname, b.billing_company, s.shipping_name, s.shipping_surname, s.shipping_company FROM demands c LEFT JOIN addresses_billing b ON b.id = c.billing_id LEFT JOIN addresses_shipping s ON s.id = c.shipping_id WHERE c.id = "' . $order['client_id'] . '"') or die($mysqli->error);
                                $client_address = mysqli_fetch_assoc($client_address_query);

                                $name = user_name($client_address);
                                ?>

                                <br />

                                <tr>
                                    <td><strong style="font-weight: 500 !important;"><a href="/admin/pages/demands/zobrazit-poptavku?id=<?= $client_address['id'] ?>">Klient: <u><?= $name ?></u></a></strong></td>
                                </tr>

                                <?php

                            }

                            ?>

                            <tr>
                                <td><strong style="font-weight: 500 !important;"><?php if ($order['customer_phone'] != "") {$phone = str_replace(' ', '', $order['customer_phone']);
                                            $billing_phone = substr($phone, -9);
                                            echo number_format($billing_phone, 0, ',', ' ');}?></strong></td>
                            </tr>
                            <tr>
                                <td><strong style="font-weight: 500 !important;"><a href="mailto:<?= $order['customer_email'] ?>"><?= $order['customer_email'] ?></a></strong></td>
                            </tr>

                            </tbody>
                        </table>


                </div>
                <div class="alert alert-info">

                    <?php if ($order['admin_note'] != "") {echo '<strong>Informace prodejce:</strong> ' . $order['admin_note'];}else{ echo 'žádné'; } ?>

                </div>

                <div class="alert alert-warning">

                    <?php if ($order['customer_note'] != "") {echo '<strong>Informace od zákazníka:</strong> ' . $order['customer_note'];}else{ echo 'žádné';}?>

                </div>

            </div>

        </div>

        <div class="col-sm-8 alert alert-default" style="background-color: #f7f7f7;padding-bottom: 0;width: 43%;margin-right: 0.5%; padding: 10px 0; font-size: 12px;">

            <div class="col-sm-6 invoice-left" style="border-right: 1px solid #dedede; margin-bottom: 10px;">

                <table class="table table-stripped table-hover" style="width: 100%; float: left; text-align: left;">
                    <thead>
                    <tr>
                        <th colspan="2">
                            <h4>Fakturační údaje</h4>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($address['billing_company'] != "") { ?>
                        <tr>
                            <td>Firma</td>
                            <td><strong style="font-weight: 500 !important;"><?php  echo $address['billing_company']; ?></strong></td>
                        </tr>
                    <?php  }
                    if ($address['billing_ico'] != "") { ?>
                        <tr>
                            <td>IČO</td>
                            <td><strong style="font-weight: 500 !important;"><?php if ($address['billing_ico'] != "") { echo $address['billing_ico']; } ?></strong></td>
                        </tr>
                    <?php  }
                    if ($address['billing_dic'] != "") { ?>
                        <tr>
                            <td>DIČ</td>
                            <td><strong style="font-weight: 500 !important;"><?php if ($address['billing_dic'] != "") { echo $address['billing_dic'];} ?></strong></td>
                        </tr>
                    <?php  }
                    if ($address['billing_name'] != '' || $address['billing_surname'] != '') { ?>
                        <tr>
                            <td>Jméno a příjmení</td>
                            <td><strong style="font-weight: 500 !important;"><?= $address['billing_name'] . ' ' . $address['billing_surname'] ?></strong></td>
                        </tr>
                    <?php  }
                    if ($address['billing_street'] != "") { ?>
                        <tr>
                            <td>Ulice</td>
                            <td><strong style="font-weight: 500 !important;"><?= $address['billing_street'] ?></strong></td>
                        </tr>
                    <?php  }
                    if ($address['billing_city'] != "") { ?>
                        <tr>
                            <td>Město a PSČ</td>
                            <td><strong style="font-weight: 500 !important;"><?= '' . $address['billing_city'] ?>&nbsp;&nbsp;&nbsp;&nbsp;<?= $address['billing_zipcode'] ?></strong></td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td>Země</td>
                        <td><strong style="font-weight: 500 !important;"><?php if (isset($address['billing_country']) && $address['billing_country'] == 'CZ') {echo 'Česká republika';} elseif (isset($address['billing_country']) && $address['billing_country'] == 'SK') {echo 'Slovensko';} elseif (isset($address['billing_country']) && $address['billing_country'] == 'PL') {echo 'Polsko';} elseif (isset($address['billing_country']) && $address['billing_country'] == 'AT') {echo 'Rakousko';} else {echo $address['billing_country'];} ?></strong></td>
                    </tr>

                    </tbody>
                </table>

            </div>

                <div class="col-sm-6 invoice-left">

                    <?php if ($order['shipping_id'] != 0){ ?>
                        <table class="table table-stripped table-hover" style="width: 100%; float: left; text-align: left;">
                            <thead>
                            <tr>
                                <th colspan="2">
                                    <h4>Doručovací údaje</h4>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if ($address['shipping_company'] != "") { ?>
                                <tr>
                                    <td>Firma</td>
                                    <td><strong style="font-weight: 500 !important;"><?php  echo $address['shipping_company']; ?></strong></td>
                                </tr>
                            <?php  }
                            if ($address['shipping_ico'] != "") { ?>
                                <tr>
                                    <td>IČO</td>
                                    <td><strong style="font-weight: 500 !important;"><?php if ($address['shipping_ico'] != "") { echo $address['shipping_ico']; } ?></strong></td>
                                </tr>
                            <?php  }
                            if ($address['shipping_dic'] != "") { ?>
                                <tr>
                                    <td>DIČ</td>
                                    <td><strong style="font-weight: 500 !important;"><?php if ($address['shipping_dic'] != "") { echo $address['shipping_dic'];} ?></strong></td>
                                </tr>
                            <?php  }
                            if ($address['shipping_name'] != '' || $address['shipping_surname'] != '') { ?>
                                <tr>
                                    <td>Jméno a příjmení</td>
                                    <td><strong style="font-weight: 500 !important;"><?= $address['shipping_name'] . ' ' . $address['shipping_surname'] ?></strong></td>
                                </tr>
                            <?php  }
                            if ($address['shipping_street'] != "") { ?>
                                <tr>
                                    <td>Ulice</td>
                                    <td><strong style="font-weight: 500 !important;"><?= $address['shipping_street'] ?></strong></td>
                                </tr>
                            <?php  }
                            if ($address['shipping_city'] != "") { ?>
                                <tr>
                                    <td>Město a PSČ</td>
                                    <td><strong style="font-weight: 500 !important;"><?= '' . $address['shipping_city'] ?>&nbsp;&nbsp;&nbsp;&nbsp;<?= $address['shipping_zipcode'] ?></strong></td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <td>Země</td>
                                <td><strong style="font-weight: 500 !important;"><?php if (isset($address['shipping_country']) && $address['shipping_country'] == 'CZ') {echo 'Česká republika';} elseif (isset($address['shipping_country']) && $address['shipping_country'] == 'SK') {echo 'Slovensko';} elseif (isset($address['shipping_country']) && $address['shipping_country'] == 'PL') {echo 'Polsko';} elseif (isset($address['shipping_country']) && $address['shipping_country'] == 'AT') {echo 'Rakousko';} else {echo $address['shipping_country'];} ?></strong></td>
                            </tr>

                            </tbody>
                        </table>
                    <?php } ?>

                </div>



            <div style="clear: both;"></div>

        </div>

        <?php

    $payment_query = $mysqli->query("SELECT name FROM shops_payment_methods WHERE link_name = '" . $order['payment_method'] . "'") or die($mysqli->error);
    $payment = mysqli_fetch_array($payment_query);

    $delivery_query = $mysqli->query("SELECT m.id, m.name, m.transporter_company, l.address, l.opening_hours FROM shops_delivery_methods m LEFT JOIN shops_locations l ON l.id = m.location_id WHERE m.link_name = '" . $order['order_shipping_method'] . "'") or die($mysqli->error);
    $delivery = mysqli_fetch_assoc($delivery_query);

    ?>
        <div class="col-sm-6 alert alert-default" style="background-color: #f7f7f7;padding-bottom: 10px;width: 28%; padding: 10px 4px;">

            <div class="col-md-12 invoice-left" style="padding: 0;">


                <table class="table table-stripped table-hover" style="width: 100%; float: right; text-align: left; font-size: 13px;">
                    <thead>
                    <tr>
                        <th colspan="2">
                            <h4>Platba a doprava</h4>
                        </th>
                    </tr>
                    </thead>
                    <tbody> <tr>
                        <td>Druh doručení:</td>
                        <td><strong><?= $delivery['name'] ?></strong></td>
                    </tr>
                    <?php if ($order['shipping_location'] != "") { ?>
                        <tr>
                            <td>Pobočka pro doručení:</td>
                            <td><strong><?= $order['shipping_location'] ?></strong></td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td>Číslo pro sledování zásilky:</td>
                        <td><?php if ($order['order_tracking_number'] != "") { ?><strong><?= $order['order_tracking_number'] ?></strong><?php } else { ?>žádný<?php } ?></td>
                    </tr>
                    <tr>
                        <td>Váha:</td>
                        <td><?php if ($order['weight'] != "") { ?><strong><?= $order['weight'] ?></strong><?php } else { ?>0<?php } ?></td>
                    </tr>
                    <tr>
                        <td>Způsob úhrady:</td>
                        <td><strong><?= $payment['name'] ?></strong></td>
                    </tr>
                    <tr>
                        <td>Variabilní symbol:</td>
                        <td><?php if (isset($order['payment_method']) && $order['payment_method'] == 'bacs') { ?><strong><?= $order['id'] ?></strong><?php } else { ?>žádný<?php } ?></td>
                    </tr>
                    </tbody>
                </table>

                <div class="col-md-12" style="display: inline-block; text-align: center; margin-bottom: 16px; margin-top: -10px;">
                    <?php if($delivery['transporter_company'] == 'Uloženka'){

                            if(empty($order['consignment_id'])) {
                                ?>
                                <a href="/admin/controllers/stores/consignments/ulozenka?action=create_consignment&id=<?= $order['id'] ?>"
                                   class="btn btn-blue btn-icon icon-left hidden-print">
                                    Uložit zásilku
                                    <i class="fas fa-truck-loading" style="font-size: 10px;"></i>
                                </a>
<!--                                <a href="javascript:;" onclick="jQuery('#ulozenka_package').modal('show');"-->
<!--                                   class="btn btn-blue btn-icon icon-left hidden-print">-->
<!--                                    Uložit zásilku-->
<!--                                    <i class="fas fa-truck-loading" style="font-size: 10px;"></i>-->
<!--                                </a>-->
                                <?php
                            }else{

                                if(file_exists($_SERVER['DOCUMENT_ROOT'] . '/admin/data/consignments/'.$order['consignment_id'].'.pdf')){

                                    $hrefUrl = '/admin/data/consignments/'.$order['consignment_id'].'.pdf';

                                }else{

                                    $hrefUrl = '/admin/controllers/stores/consignments/ulozenka?action=create_label&id='.$order['id'];

                                }
                                ?>
                                <a href="<?= $hrefUrl ?>" class="btn btn-orange btn-icon icon-left hidden-print" target="_blank">
                                    Tisknout šťítek
                                    <i class="entypo-print"></i>
                                </a>

                                <a href="/admin/controllers/stores/consignments/ulozenka?action=storno&id=<?= $order['id'] ?>"
                                   class="btn btn-primary btn-icon icon-left hidden-print">
                                    Stornovat zásilku
                                    <i class="entypo-trash"></i>
                                </a>

<!--                                <a href="javascript:;" onclick="jQuery('#ulozenka_package').modal('show');"-->
<!--                                   class="btn btn-primary btn-icon icon-left hidden-print">-->
<!--                                    Stornovat zásilku-->
<!--                                    <i class="entypo-trash"></i>-->
<!--                                </a>-->

                               <?php

                            }

                        }elseif($delivery['transporter_company'] == 'Zásilkovna'){

                            if(empty($order['consignment_id'])) {
                                ?>
                                <a href="/admin/controllers/stores/consignments/zasilkovna?action=create_consignment&id=<?= $order['id'] ?>"
                                   class="btn btn-blue btn-icon icon-left hidden-print">
                                    Uložit zásilku
                                    <i class="fas fa-truck-loading" style="font-size: 10px;"></i>
                                </a>
                                <!--                                <a href="javascript:;" onclick="jQuery('#ulozenka_package').modal('show');"-->
                                <!--                                   class="btn btn-blue btn-icon icon-left hidden-print">-->
                                <!--                                    Uložit zásilku-->
                                <!--                                    <i class="fas fa-truck-loading" style="font-size: 10px;"></i>-->
                                <!--                                </a>-->
                                <?php
                            }else{

                                if(file_exists($_SERVER['DOCUMENT_ROOT'] . '/admin/data/consignments/'.$order['consignment_id'].'.pdf')){

                                    $hrefUrl = '/admin/data/consignments/'.$order['consignment_id'].'.pdf';

                                }else{

                                    echo 'při generování štítku došlo k problému. kontaktovat admina';
//                                    $hrefUrl = '/admin/controllers/stores/consignments/ulozenka?action=create_label&id='.$order['id'];

                                }
                                ?>
                                <a href="<?= $hrefUrl ?>" class="btn btn-orange btn-icon icon-left" target="_blank">
                                    Tisknout šťítek
                                    <i class="entypo-print"></i>
                                </a>
                                <a href="https://tracking.packeta.com/cs/?id=<?= $order['consignment_id'] ?>" class="btn btn-info btn-icon icon-left" target="_blank">
                                    Stav zásilky
                                    <i class="entypo-info-circled"></i>
                                </a>
<!--todo storno? jde? nejde asi?-->
<!--                                <a href="/admin/controllers/stores/consignments/ulozenka?action=storno&id=--><?// echo $order['id']; ?><!--"-->
<!--                                   class="btn btn-primary btn-icon icon-left hidden-print">-->
<!--                                    Stornovat zásilku-->
<!--                                    <i class="entypo-trash"></i>-->
<!--                                </a>-->

                                <!--                                <a href="javascript:;" onclick="jQuery('#ulozenka_package').modal('show');"-->
                                <!--                                   class="btn btn-primary btn-icon icon-left hidden-print">-->
                                <!--                                    Stornovat zásilku-->
                                <!--                                    <i class="entypo-trash"></i>-->
                                <!--                                </a>-->

                                <?php

                            }

                    } ?>

                </div>



                <?php

                $check_invoice = $mysqli->query("SELECT id FROM orders_invoices WHERE order_id = '".$order['id']."' AND type = 'active'")or die($mysqli->error);

                if(mysqli_num_rows($check_invoice) > 0){
                    $invoice = mysqli_fetch_assoc($check_invoice);

                }else{
                    $invoice['id'] = 99999;
                }

                $order_date = date("Y-m-d", strtotime($order['order_date']));

                $payment_info = '-';
                $color = 'color: #373e4a;';
                // if bankwire
                if($order['payment_method'] == 'bacs'){

                    $bank_sum_query = $mysqli->query("SELECT SUM(value) as total FROM bank_transactions WHERE account = 'order' AND (vs = '".$order['id']."' OR manual_assign = '".$order['id']."' OR vs = '".$invoice['id']."') AND date >= '".$order_date."'")or die($mysqli->error);
                    $bank_sum = mysqli_fetch_assoc($bank_sum_query);

                    if (isset($bank_sum['total']) && $bank_sum['total'] != '0') {

                        if (isset($order['paid_value']) && $bank_sum['total'] == $order['total']) {

                            $payment_info = '<i class="entypo-check"></i> zaplaceno';
                            $color = 'color: #00a651';

                        } else {

                            $payment_info = '<i class="entypo-block"></i> problém: '.thousand_seperator($bank_sum['total'] - $order['total']).$currency['sign'];;
                            $color = 'color: #d42020;';

                        }

                    }else{

                        $payment_info = '<i class="entypo-back-in-time"></i> čeká na platbu';
                        $color = 'color: #ff9600;';

                    }


                }elseif($order['payment_method'] == 'agmobindercardall' || $order['payment_method'] == 'agmobinderbank'){

                    // check comgate
                    $comgate_query = $mysqli->query("SELECT * FROM transactions_comgate WHERE id = '".$order['transaction_id']."'")or die($mysqli->error);

                    if(mysqli_num_rows($comgate_query) > 0){
                        $comgate = mysqli_fetch_assoc($comgate_query);


                        if ($comgate['status'] == 'PAID' && $comgate['value'] == $order['total']) {

                            $payment_info = '<i class="entypo-check"></i> comgate: zaplaceno';
                            $color = 'color: #00a651';

                        } elseif ($comgate['status'] == 'PAID' && $comgate['value'] != $order['total']) {

                            $payment_info = '<i class="entypo-block"></i>comgate: problém: '. thousand_seperator($comgate['value'] - $order['total']).$currency['sign'];
                            $color = 'color: #d42020;';

                        } elseif ($comgate['status'] == 'PENDING') {

                            $payment_info = '<i class="entypo-back-in-time"></i>comgate: čeká na platbu';
                            $color = 'color: #ff9600;';

                        } elseif ($comgate['status'] == 'CANCELLED') {

                            $payment_info = '<i class="entypo-trash"></i>comgate: stornovaná';
                            $color = 'color: #000;';

                        }


                    }else{

                        $payment_info = '-';
                        $color = 'color: #373e4a;';

                    }

                }else{

                    $payment_info = '-';
                    $color = 'color: #373e4a;';

                }

                ?>


                <table class="table table-stripped table-hover" style="width: 100%; float: right; text-align: left; font-size: 13px;">
                    <thead>
                    <tr>
                        <th colspan="2">
                            <h4>Cena</h4>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Celkem bez dph:</td>
                            <td><strong><?= thousand_seperator($order['total_without_vat']).$currency['sign'] ?></strong></td>
                        </tr>
                        <tr>
                            <td>DPH <?= $order['vat'] ?>%:</td>
                            <td><strong><?= thousand_seperator($order['total_vat']).$currency['sign'] ?></strong></td>
                        </tr>
                        <?php
                        if($order['total_rounded'] != '0.00'){ ?>
                        <tr>
                            <td>Zaokrouhleno: </td>
                            <td><strong><?= thousand_seperator($order['total_rounded']).$currency['sign'] ?></strong></td>
                        </tr>
                        <?php } ?>
                        <tr>
                            <td><strong>Cena celkem:</strong></td>
                            <td>
                                <h4 style="font-size: 18px; margin-bottom: 0; font-family: inherit;  font-weight: normal;<?= $color ?> "><strong style="<?= $color ?>; font-weight: 600; "><?= thousand_seperator($order['total']) ?></strong> <?= $currency['sign'] ?></h4></td>
                        </tr>                        <tr>
                            <td>Poznámka k platbě:</td>
                            <td> <span style="font-size: 13px;  font-weight: normal; <?= $color ?>"><?= $payment_info ?></span>
                              </td>
                        </tr>

                    </tbody>
                </table>


            </div>

        </div>

    </div>



	<div class="margin"></div>

	<table class="table table-bordered table-hover">
		<thead>
			<tr>
				<th style="background-color: #f9f9f9 !important; color: #222;" class="text-center">#</th>
				<th style="background-color: #f9f9f9 !important; color: #222;" width="30%">Položka</th>
				<th style="background-color: #f9f9f9 !important; color: #222;" width="90px" class="text-center">Počet</th>
				<th style="background-color: #f9f9f9 !important; color: #222;" width="130px" class="text-center">Rezervováno</th>
				<th style="background-color: #f9f9f9 !important; color: #222;" width="90px" class="text-center">Na cestě</th>
				<th style="background-color: #f9f9f9 !important; color: #222;" width="90px" class="text-center">Chybí</th>
				<th style="background-color: #f9f9f9 !important; color: #222;" class="text-center">Původní cena</th>
				<th style="background-color: #f9f9f9 !important; color: #222;" class="text-center">Cena za mj.</th>
                <th style="background-color: #f9f9f9 !important; color: #222;" class="text-center">Sleva</th>
                <th style="background-color: #f9f9f9 !important; color: #222;" class="text-right">Cena celkem</th>
			</tr>
		</thead>

		<tbody>
			<?php

    $bridge_query = $mysqli->query("SELECT * FROM orders_products_bridge 
        WHERE aggregate_id = '$id' AND aggregate_type = 'order'");

    $price_with_dph = 0;
    $i = 0;

    $has_discount = false;
    $total_discount = 0;
    while ($bridge = mysqli_fetch_array($bridge_query)) {

        $i++;

        $products_query = $mysqli->query("SELECT *, id as ajdee FROM products WHERE id = '" . $bridge['product_id'] . "'");

        if (mysqli_num_rows($products_query) == 1) {

            $product = mysqli_fetch_array($products_query);

            ?>


			<tr>
				<td class="text-center" style="vertical-align: middle;"><?= $i ?></td>
				<td><a href="/admin/pages/accessories/zobrazit-prislusenstvi?id=<?= $product['ajdee'] ?>" target="_blank">
<?php

            if ($bridge['variation_id'] != 0) {

                $variation_sku_query = $mysqli->query("SELECT id, sku, main_warehouse FROM products_variations WHERE id = '" . $bridge['variation_id'] . "'");
                $variation_sku = mysqli_fetch_array($variation_sku_query);

                $main_warehouse = $variation_sku['main_warehouse'];

                $path = PRODUCT_IMAGE_PATH.'/thumbnail/' . $product['seourl'] . '_variation_'.$variation_sku['id'].'.jpg';
                $path_product = PRODUCT_IMAGE_PATH.'/thumbnail/' . $product['seourl'] . '.jpg';

                if(file_exists($path)){
                    $imagePath = '/data/stores/images/thumbnail/'.$product['seourl'].'_variation_'.$variation_sku['id'].'.jpg';
                }elseif(file_exists($path_product)){
                    $imagePath = '/data/stores/images/thumbnail/'.$product['seourl'].'.jpg';
                }else{
                    $imagePath = '/data/assets/no-image-7.jpg';
                }

            } else {

                $main_warehouse = $product['main_warehouse'];

                $path = PRODUCT_IMAGE_PATH.'/thumbnail/' . $product['seourl'] . '.jpg';
                if(file_exists($path)){
                    $imagePath = '/data/stores/images/thumbnail/'.$product['seourl'].'.jpg';
                }else{
                    $imagePath = '/data/assets/no-image-7.jpg';
                }

            }

                if (isset($bridge['quantity']) && isset($bridge['reserved']) && ($bridge['quantity'] - $bridge['reserved']) > 0) {
                    $border = 'border: 1px dashed #ff0000';
                } else {
                    $border = 'border: 1px solid #ebebeb';
                }

                echo '<img src="'.$imagePath.'" width="40" height="45.55" style="float: left; margin-right: 12px; '.$border.' ">';

                if(!empty($bridge['discount'])){

                    $has_discount = true;

                    $total_discount += round(($bridge['price'] / 100 * ($bridge['discount'])) * $bridge['quantity'], 2, PHP_ROUND_HALF_DOWN);

                }

        ?>



					<strong style="<?php if (isset($bridge['variation_id']) && $bridge['variation_id'] == 0) { ?>padding-top: 5px; display: block;<?php } ?>font-weight: 500;"><?= $product['productname'] ?> - <small class="tooltip-primary" data-toggle="tooltip" data-placement="top" title="" data-original-title="SKU"><?php

            if ($bridge['variation_id'] != 0) {

                echo $variation_sku['sku'];

            } else {

                echo $product['code'];
            }

            ?></small></strong></a>

					<?php if ($bridge['variation_id'] != 0) {

                echo '<span style="font-size: 12px; font-weight: 300;">';

                $variation_query = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $bridge['variation_id'] . "'");

                while ($variation = mysqli_fetch_array($variation_query)) {
                    echo '<br>';
                    echo $variation['name'] . ': ' . $variation['value'];

                }

                echo '</span>';

            }
            ?></td>
				<td class="text-center" style="vertical-align: middle;"><?= $bridge['quantity'] ?></td>
				<td class="text-center" style="vertical-align: middle;"><strong class="text-success"><?= $bridge['reserved'] ?></strong></td>
				<td class="text-center" style="vertical-align: middle;"><strong class="text-info"><?= $bridge['delivered'] ?></strong></td>
				<td class="text-center" style="vertical-align: middle;"><?php

            if (($bridge['quantity'] - $bridge['reserved'] - $bridge['delivered']) > 0) { ?>

<strong class="text-danger">-<?= $bridge['quantity'] - $bridge['reserved'] - $bridge['delivered'] ?></strong>

				<?php } else { ?>0<?php } ?></td>
				<td class="text-center" style="vertical-align: middle;"><?php

                    if($order['order_currency'] === 'CZK'){
                        echo number_format($bridge['original_price'], 2, ',', ' ');
                    }else{
                        echo number_format($bridge['original_price'] / $order['exchange_rate'], 2, ',', ' ');
                    }

                    echo $currency['sign']; ?></td>
				<td class="text-center" style="vertical-align: middle;"><?= number_format($bridge['price'], 2, ',', ' ').$currency['sign'] ?></td>
                <td class="text-center" style="vertical-align: middle;"><?php

                    if(!empty($bridge['discount'])) {

                        echo $bridge['discount']; ?> %
                        <br>
                        <small><?= $bridge['discount_net'] * $bridge['quantity'].$currency['sign'];?></small>
                    <?php }else{ echo '-'; }
                    ?></td>
                <td class="text-right" style="vertical-align: middle;"><strong><?= number_format($bridge['price'] * $bridge['quantity'], 2, ',', ' ').$currency['sign'] ?></strong></td>
			</tr>
<?php
        } else { ?>


<tr>
				<td class="text-center" style="vertical-align: middle;"><?= $i ?></td>
				<td><strong>Neznámý produkt</strong> <?= $bridge['product_name'] ?> - <small><?= $bridge['variation_values'] ?></small></td>
				<td class="text-center" style="vertical-align: middle;"><?= $bridge['quantity'] ?></td>
				<td class="text-center" style="vertical-align: middle;"><strong class="text-success"><?= $bridge['reserved'] ?></strong></td>
				<td class="text-center" style="vertical-align: middle;"><?php

            if (($bridge['quantity'] - $bridge['reserved']) > 0) { ?>

<strong class="text-danger">-<?= $bridge['quantity'] - $bridge['reserved'] ?></strong>

				<?php } else { ?>0<?php } ?></td>
    <td class="text-center" style="vertical-align: middle;">-</td>
				<td class="text-center" style="vertical-align: middle;"><?= number_format($bridge['original_price'], 0, ',', ' ').$currency['sign'] ?></td>


				<td class="text-center" style="vertical-align: middle;"><?= number_format($bridge['price'], 2, ',', ' ').$currency['sign'] ?></td>
    <td class="text-center" style="vertical-align: middle;"><?= $order['discount'] ?> %</td>


    <td class="text-right" style="vertical-align: middle;"><strong><?= number_format($bridge['price'] * $bridge['quantity'], 2, ',', ' ').$currency['sign'] ?></strong></td>
			</tr>



<?php

        }

        $price_with_dph = $price_with_dph + ($bridge['price'] * $bridge['quantity']);
    }


            $i++

    ?>


            <tr>
                <td height="63" class="text-center" style="vertical-align: middle;"><?= $i ?></td>
                <td height="63" style="vertical-align: middle;"><strong style="font-weight: 500;"><i class="fas fa-truck-loading" style="width: 40px;height: 46px;line-height: 45px;text-align: center;border: 1px solid #ebebeb;background-color: #fdfdfd;margin-right: 14px;"></i>Doprava</strong></td>
                <td class="text-center" style="vertical-align: middle;">1</td>
                <td class="text-center" style="vertical-align: middle;"><strong class="text-success">-</strong></td>
                <td class="text-center" style="vertical-align: middle;">-</td>
                <td class="text-center" style="vertical-align: middle;">-</td>
                <td class="text-center" style="vertical-align: middle;"><?= thousand_seperator($order['delivery_price']).$currency['sign'] ?></td>
                <td class="text-center" style="vertical-align: middle;"><?= thousand_seperator($order['delivery_price']).$currency['sign'] ?></td>
                <td class="text-center" style="vertical-align: middle;">-</td>
                <td class="text-right" style="vertical-align: middle;"><strong><?= thousand_seperator($order['delivery_price']).$currency['sign'] ?></strong></td>
            </tr>




        <?php

        if($has_discount) {

            $i++;

            ?>

            <tr>
                <td height="63" class="text-center" style="vertical-align: middle;"><?= $i ?></td>
                <td height="63" style="vertical-align: middle;"><strong style="font-weight: 500;"><i class="fas fa-percent" style="width: 40px;height: 46px;line-height: 45px;text-align: center;border: 1px solid #ebebeb;background-color: #fdfdfd;margin-right: 14px;"></i>Sleva</strong></td>
                <td class="text-center" style="vertical-align: middle;">1</td>
                <td class="text-center" style="vertical-align: middle;"><strong class="text-success">-</strong></td>
                <td class="text-center" style="vertical-align: middle;">-</td>
                <td class="text-center" style="vertical-align: middle;">-</td>
                <td class="text-center" style="vertical-align: middle;">-<?= thousand_seperator($total_discount).$currency['sign'] ?></td>
                <td class="text-center" style="vertical-align: middle;">-<?= thousand_seperator($total_discount).$currency['sign'] ?></td>
                <td class="text-center" style="vertical-align: middle;">-</td>
                <td class="text-right" style="vertical-align: middle;"><strong>-<?= thousand_seperator($total_discount).$currency['sign'] ?></strong></td>
            </tr>

            <?php
        }



        ?>


		</tbody>
	</table>


	<div class="row">

				<hr />


            <div class="invoice-left col-sm-6">

                <?php

                if (isset($order['payment_method']) && $order['payment_method'] == 'cash' || $order['payment_method'] == 'agmobindercardall') {

                    $eet = 'yes';

                } else {

                    $eet = 'no';

                }

                $has_invoice = false;
                $allowRegenerate = false;

                $invoice_query = $mysqli->query("SELECT * FROM orders_invoices WHERE order_id = '$id' AND type = 'order' AND status != 'odd' order by id desc");

                if (mysqli_num_rows($invoice_query) == 0) {

                    ?>
                    <a href="javascript:;" onclick="jQuery('#recapitulate').modal('show');" class="btn btn-success btn-icon icon-left hidden-print">
                        Vystavit fakturu
                        <i class="entypo-doc-text"></i>
                    </a>
                    <?php

                }else{

                    $has_invoice = true;

                while ($invoice = mysqli_fetch_array($invoice_query)) {

                    if (isset($invoice['status']) && $invoice['status'] == 'active') {


                        // check if there is a "correcting invoice"
                        $correct_query = $mysqli->query("SELECT * FROM orders_invoices WHERE invoice_id = '" . $invoice['id'] . "' order by id desc LIMIT 1");

                        if(date("Y-m", strtotime($invoice['date'])) == date("Y-m")
                            && mysqli_num_rows($correct_query) == 0){

                            // if not, allow regenerate
                            $allowRegenerate = true;

                        }

                        if($invoice['export_id'] != 0){

                            $allowRegenerate = false;

                        }

                ?>
                    <div style="background-color: #f3f3f3; padding: 20px 20px 24px 20px; float: left;  margin-bottom: 26px;">
                        <h4 style="margin-bottom: 16px; margin-top: 0;">Faktura <u><?= $invoice['id'] ?></u>:</h4>
                        <a href="https://www.wellnesstrade.cz/admin/data/invoices/orders/<?= $invoice['id'] ?>.pdf?t=<?= $currentDate->getTimestamp() ?>" target="_blank" class="btn btn-white btn-icon icon-left hidden-print">
                            Zobrazit fakturu
                            <i class="entypo-search"></i>
                        </a>


                        &nbsp;


                        <?php
                            if($allowRegenerate
                                || $client['email'] == 'becher@saunahouse.cz'
                            ){

                            ?>
                            <a href="/admin/controllers/generators/order_invoice_regenerate?invoice_id=<?= $invoice['id'] ?>" class="btn btn-danger btn-icon icon-left" style="float: right;">Přegenerovat
                                <i class="entypo-record"></i></a>
                        <?php }else{

                                ?>
                                <a class="btn btn-danger btn-icon icon-left" style="float: right;" disabled>Nelze přegenerovat
                                    <i class="entypo-record"></i></a>
                        <?php }
                            ?>
                        &nbsp;

                        <a href="javascript: w=window.open('https://www.wellnesstrade.cz/admin/data/invoices/orders/<?= $invoice['id'] ?>.pdf?t=<?= $currentDate->getTimestamp() ?>'); w.print(); " class="btn btn-primary btn-icon icon-left hidden-print">
                            Tisknout fakturu
                            <i class="entypo-print"></i>
                        </a>

                        &nbsp;

    <!--                    <a href="#" class="btn btn-success btn-icon icon-left hidden-print">-->
    <!--                        Odeslat fakturu-->
    <!--                        <i class="entypo-mail"></i>-->
    <!--                    </a>-->

                        <?php


                        if (mysqli_num_rows($correct_query) > 0) {

                        $correct = mysqli_fetch_array($correct_query);

                            ?>
                            <hr>

                            <h4 style="margin-bottom: 16px; margin-top: 0;">Opravný daňový doklad <u><?= $correct['id'] ?></u> k faktuře <u><?= $correct['invoice_id'] ?></u>:</h4>
                            <a href="https://www.wellnesstrade.cz/admin/data/invoices/orders/<?= $correct['id'] ?>.pdf?t=<?= $currentDate->getTimestamp() ?>" target="_blank" class="btn btn-white btn-icon icon-left hidden-print">
                                Zobrazit doklad
                                <i class="entypo-search"></i>
                            </a>

                            &nbsp;

                            <a href="javascript: w=window.open('https://www.wellnesstrade.cz/admin/data/invoices/orders/<?= $correct['id'] ?>.pdf?t=<?= $currentDate->getTimestamp() ?>'); w.print(); " class="btn btn-primary btn-icon icon-left hidden-print">
                                Tisknout doklad
                                <i class="entypo-print"></i>
                            </a>

                            &nbsp;

        <!--                    <a href="#" class="btn btn-success btn-icon icon-left hidden-print">-->
        <!--                        Odeslat doklad-->
        <!--                        <i class="entypo-mail"></i>-->
        <!--                    </a>-->

                        </div>

                    <?php

                    } else {

                    ?>
                        <hr>
                        <h4 style="margin-bottom: 16px; margin-top: 0;">Opravný daňový doklad:</h4>

                        <a href="/admin/controllers/generators/order_invoice?id=<?= $order['id'] ?>&odd=1&eet=<?= $eet ?>&type=order" class="btn btn-red btn-icon icon-left hidden-print">
                            Vystavit ODD pro fakturu
                            <i class="entypo-cancel-circled"></i>
                        </a>

                    </div>

                    <?php
                    }

                }

            }
            }

            ?>

        </div>

			<div class="invoice-right col-sm-6">

			<a href="javascript:;" onclick="jQuery('#change_status_modal').modal('show');" class="btn btn-blue btn-icon icon-left hidden-print">
					Změnit stav
					<i class="entypo-bookmarks"></i>
				</a>
				&nbsp;

				<a href="javascript:;" onclick="jQuery('#separate_order').modal('show');" class="btn btn-orange btn-icon icon-left hidden-print">
					Rozdělit
					<i class="entypo-resize-full"></i>
				</a>
				&nbsp;


				<a href="./zobrazit-objednavku?id=<?= $order['id'] ?>&action=duplicate" target="_blank" class="btn btn-default btn-icon icon-left hidden-print">
					Duplikovat
					<i class="entypo-book"></i>
				</a>

				&nbsp;

                <?php if(!$has_invoice || ($has_invoice && !$allowRegenerate)){ ?>
                    <a href="./upravit-objednavku?id=<?= $order['id'] ?>" class="btn btn-default btn-icon icon-left hidden-print">
                        Upravit
                        <i class="entypo-pencil"></i>
                    </a>
                <?php }elseif($allowRegenerate){ ?>
                    <a href="javascript:;" onclick="jQuery('#order_edit_modal').modal('show');" class="btn btn-default btn-icon icon-left hidden-print">
                        Upravit
                        <i class="entypo-pencil"></i>
                    </a>
                <?php } ?>

			</div>

		</div>

	</div>

</div




    <?php if(!empty($order['import_log'])){ ?>
    <div class="panel-group" id="accordion-test">
        <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <a data-toggle="collapse" data-parent="#accordion-test" href="#collapseThree" class="collapsed" aria-expanded="false">
                        Import log
                    </a>
                    </h4>
                </div>
                <div id="collapseThree" class="panel-collapse collapse" aria-expanded="false"> <div class="panel-body">
                <pre><?php
                            $import_log = json_decode($order['import_log'], true);
                            print_r($import_log);
                            ?></pre>
                </div>
            </div>
        </div>
    </div>
    <?php }

    $invoice_query = $mysqli->query("SELECT receipt, id FROM orders_invoices WHERE order_id = '$id' AND type = 'order' order by id desc");
    while ($invoice = mysqli_fetch_array($invoice_query)) {

        if(!empty($invoice['receipt'])){ ?>
            <div class="panel-group" id="accordion-test">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion-test" href="#collapse-<?= $invoice['id'] ?>" class="collapsed" aria-expanded="false">
                                EET receipt log
                            </a>
                        </h4>
                    </div>
                    <div id="collapse-<?= $invoice['id'] ?>" class="panel-collapse collapse" aria-expanded="false"> <div class="panel-body">
                            <pre><?php
                                $import_log = json_decode($invoice['receipt'], true);
                                print_r($import_log);
                                ?></pre>
                        </div>
                    </div>
                </div>
            </div>
        <?php }

    }
    ?>
<footer class="main">


	&copy; <?= date("Y") ?> <span style=" float:right;"><?php
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round(($finish - $start), 4);

    echo 'PHP '.PHP_VERSION.' | Page generated in ' . $total_time . ' seconds.';?></span>

</footer>	</div>


	</div>

<style>

.page-body .selectboxit-container .selectboxit-options { margin-top: 40px !important; width: 100% !important;}
.page-body .selectboxit-container .selectboxit { height: 40px;width: 100% !important;}
.page-body .selectboxit-container .selectboxit .selectboxit-text { line-height: 40px; }
.page-body .selectboxit-container .selectboxit .selectboxit-arrow-container { height: 40px;}
.page-body .selectboxit-container .selectboxit .selectboxit-arrow-container:after { line-height: 40px;}
</style>






<div class="modal fade" id="order_edit_modal" aria-hidden="true" style="display: none; margin-top: 8%;">
  <div class="modal-dialog" style="width: 800px;">
    <div class="modal-content">

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title">Upravení objednávky #<?= $order['id'] ?></h4>
      </div>

      <div class="modal-body">

      	<div class="well" style="margin-bottom: 0;">
         <p style="font-size: 16px; line-height: 26px; padding: 20px 0 10px; color: #0F0F0F;  text-align: center;">~ Objednávka má vystavenou fakturu!</p>
        <p style="font-size: 14px; line-height: 26px; padding: 20px 0 10px; color: #0F0F0F; padding: 20px;border-radius: 5px;border: 1px solid #D70505; font-weight: bold;">Všechny provedené změny se při uložení objednávky AUTOMATICKY přegenerují do faktury!</p>
     </div>

      </div>
      <div class="modal-footer" style="text-align:left;">
        <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>
        <a href="./upravit-objednavku?id=<?= $order['id'] ?>" style="float:right;">
          <button type="submit" class="btn btn-default btn-icon icon-left">Upravit objednávku <i class="entypo-pencil"></i></button>
        </a>
      </div>

    </div>
  </div>
</div>




<script type="text/javascript">
jQuery(document).ready(function($)
{

 $('.rad1').on('switch-change', function () {

 if($('#nah').prop('checked')){

 	$('#enable_custom_hidden').show("slow");

   }else if(!$('#nah').prop('checked')){


 	$('#enable_custom_hidden').hide("slow");
 	$('#enable_custom').prop('checked', false);

 	$('.rad2').bootstrapSwitch('setState', false);

 	$('#custom_text').hide("slow");

 }

});



 $('.rad2').on('switch-change', function () {

 if($('#enable_custom').prop('checked')){

 	$('#custom_text').show("slow");

   }else if(!$('#enable_custom').prop('checked')){


 	$('#custom_text').hide("slow");
 }

});


});
</script>

<div class="modal fade" id="change_status_modal" aria-hidden="true" style="display: none;margin-top: 3%;">

	<div class="modal-dialog" style="padding-top: 8%;">

		<form role="form" method="post" action="zobrazit-objednavku?action=change_status&id=<?= $id ?>" enctype="multipart/form-data">
		<div class="modal-content">
			<div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

				<h4 class="modal-title">Změna stavu objednávky #<?= $order['id'] ?></h4> </div>

			<div class="modal-body">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Nový stav objednávky
					</div>

				</div>

						<div class="panel-body">


				<div class="form-group">
						<label class="col-sm-2 control-label"></label>
						<div class="col-sm-8">
							<select name="status" class="selectboxit">
								<option value="0" <?php if (isset($order['order_status']) && $order['order_status'] == 0) {echo 'selected';}?>>Nezpracovaná</option>
								<option value="1" <?php if (isset($order['order_status']) && $order['order_status'] == 1) {echo 'selected';}?>>V řešení</option>
								<option value="2" <?php if (isset($order['order_status']) && $order['order_status'] == 2) {echo 'selected';}?>>Připravená</option>
								<option value="3" <?php if (isset($order['order_status']) && $order['order_status'] == 3) {echo 'selected';}?>>Vyexpedovaná</option>
								<option value="4" <?php if (isset($order['order_status']) && $order['order_status'] == 4) {echo 'selected';}?>>Stornovaná</option>
							</select>
						</div>
					</div>
				</div>
				</div>

				<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Nastavení mailového upozornění
					</div>

				</div>

						<div class="panel-body form-horizontal">


				<div class="form-group">
						<label class="col-sm-6 control-label" for="nah" style="padding-top: 7px;">Informovat zákazníka o změně stavu</label>
						<div class="col-sm-6">
							<div class="radiodegreeswitch rad1 make-switch switch-small" style="float: left; margin-right:11px; margin-top: 2px;" data-on-label="<i class='entypo-mail'></i>" data-off-label="<i class='entypo-cancel'></i>">
										<input class="radiodegree" name="send_mail" id="nah" value="yes" type="checkbox"/>
									</div>

						</div>
					</div>

				<div class="form-group" id="enable_custom_hidden" style="display: none;">
						<label class="col-sm-6 control-label" for="enable_custom" style="padding-top: 7px;">Vlastní úvodní text emailu</label>
						<div class="col-sm-6">
							<div class="radiodegreeswitch rad2 make-switch switch-small" style="float: left; margin-right:11px; margin-top: 2px;" data-on-label="<i class='entypo-pencil'></i>" data-off-label="<i class='entypo-cancel'></i>">
										<input class="radiodegree" name="enable_custom" id="enable_custom" value="yes" type="checkbox"/>
									</div>

						</div>
					</div>

					<div class="form-group" id="custom_text" style="display: none;">
						<label class="col-sm-3 control-label" for="ee" style="padding-top: 7px;">Úvodní text emailu</label>
						<div class="col-sm-9">

							<textarea name="custom_text" class="form-control autogrow" id="field-7" style="height: 140px;"></textarea>

						</div>
					</div>

				</div>
				</div>

				<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Číslo pro sledování zásilky
					</div>

				</div>

						<div class="panel-body">


				<div class="form-group">
						<label class="col-sm-2 control-label" for="nah"></label>
						<div class="col-sm-8">
								<input type="text" style="height: 40px;" name="order_tracking_number" class="form-control" id="field-1" placeholder="Sledovací číslo" value="<?= $order['order_tracking_number'] ?>">
						</div>
					</div>
				</div>
				</div>

			</div>
<div class="modal-footer" style="text-align:left;"> <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>

	<a href="#" style="float:right;"><button type="submit" class="btn btn-blue btn-icon icon-left">Změnit stav
					<i class="entypo-bookmarks"></i></button></a>
	</div>
	</div>
        </form>

    </div>
</div>



    <div class="modal fade" id="recapitulate" aria-hidden="true" style="display: none;margin-top: 3%;">

        <div class="modal-dialog" style="padding-top: 4%; width: 800px;">

                <div class="modal-content">
                    <div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

                        <h4 class="modal-title">Rekapitulace objednávky #<?= $order['id'] ?></h4> </div>

                    <div class="modal-body">



                                <div class="form-group">

                                    <table class="table table-bordered table-hover">
                                        <thead>
                                        <tr>
<!--                                            <th style="background-color: #f9f9f9 !important; color: #222;" class="text-center">#</th>-->
                                            <th style="background-color: #f9f9f9 !important; color: #222;" >Položka</th>
                                            <th style="background-color: #f9f9f9 !important; color: #222;" class="text-center">Počet</th>
                                            <th style="background-color: #f9f9f9 !important; color: #222;" class="text-center">Cena</th>
                                            <th style="background-color: #f9f9f9 !important; color: #222;" class="text-center">Sleva</th>
                                            <th style="background-color: #f9f9f9 !important; color: #222;" class="text-right">Cena celkem</th>
                                        </tr>
                                        </thead>

                                        <tbody>
                                        <?php

                                        $bridge_query = $mysqli->query("SELECT * FROM orders_products_bridge WHERE aggregate_id = '$id' AND aggregate_type = 'order'");

                                        $price_with_dph = 0;
                                        $i = 0;

                                        $has_discount = false;
                                        $total_discount = 0;
                                        while ($bridge = mysqli_fetch_array($bridge_query)) {

                                            $i++;

                                            $products_query = $mysqli->query("SELECT *, id as ajdee FROM products WHERE id = '" . $bridge['product_id'] . "'");

                                            if (mysqli_num_rows($products_query) == 1) {

                                                $product = mysqli_fetch_array($products_query);

                                                ?>


                                                <tr>
                                                    <td><a href="/admin/pages/accessories/zobrazit-prislusenstvi?id=<?= $product['ajdee'] ?>" target="_blank">
                                                            <?php

                                                            if ($bridge['variation_id'] != 0) {

                                                                $variation_sku_query = $mysqli->query("SELECT id, sku, main_warehouse FROM products_variations WHERE id = '" . $bridge['variation_id'] . "'");
                                                                $variation_sku = mysqli_fetch_array($variation_sku_query);

                                                                $main_warehouse = $variation_sku['main_warehouse'];

                                                                $path = PRODUCT_IMAGE_PATH.'/small/' . $product['seourl'] . '_variation_'.$variation_sku['id'].'.jpg';
                                                                $path_product = PRODUCT_IMAGE_PATH.'/small/' . $product['seourl'] . '.jpg';

                                                                if(file_exists($path)){
                                                                    $imagePath = '/data/stores/images/small/'.$product['seourl'].'_variation_'.$variation_sku['id'].'.jpg';
                                                                }elseif(file_exists($path_product)){
                                                                    $imagePath = '/data/stores/images/small/'.$product['seourl'].'.jpg';
                                                                }else{
                                                                    $imagePath = '/data/assets/no-image-7.jpg';
                                                                }

                                                            } else {

                                                                $main_warehouse = $product['main_warehouse'];

                                                                $path = PRODUCT_IMAGE_PATH.'/small/' . $product['seourl'] . '.jpg';
                                                                if(file_exists($path)){
                                                                    $imagePath = '/data/stores/images/small/'.$product['seourl'].'.jpg';
                                                                }else{
                                                                    $imagePath = '/data/assets/no-image-7.jpg';
                                                                }

                                                            }

                                                                $border = 'border: 1px solid #ebebeb';

                                                            echo '<img src="'.$imagePath.'" width="30" style="float: left; margin-right: 12px; '.$border.' ">';

                                                            if(!empty($bridge['discount'])){

                                                                $has_discount = true;

                                                                $total_discount += round(($bridge['price'] / 100 * ($bridge['discount'])) * $bridge['quantity'], 2, PHP_ROUND_HALF_DOWN);

                                                            }

                                                            ?>



                                                            <strong style="<?php if (isset($bridge['variation_id']) && $bridge['variation_id'] == 0) { ?>padding-top: 9px; float:left;<?php } ?>font-weight: 500;"><?= $product['productname'] ?></strong></a>

                                                        <?php if ($bridge['variation_id'] != 0) {

                                                            echo '<span style="font-size: 12px; font-weight: 300;">';

                                                            $variation_query = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $bridge['variation_id'] . "'");

                                                            while ($variation = mysqli_fetch_array($variation_query)) {
                                                                echo '<br>';
                                                                echo $variation['name'] . ': ' . $variation['value'];

                                                            }

                                                            echo '</span>';

                                                        }
                                                        ?></td>
                                                    <td class="text-center" style="vertical-align: middle;"><?= $bridge['quantity'] ?></td>

                                                    <td class="text-center" style="vertical-align: middle;"><?= number_format($bridge['original_price'], 0, ',', ' ').$currency['sign'] ?></td>

                                                    <td class="text-center" style="vertical-align: middle;"><?php

                                                        if(!empty($bridge['discount'])) {

                                                            echo $bridge['discount']; ?> % = <?php

                                                            echo $bridge['discount_net'] * $bridge['quantity'].$currency['sign'];

                                                        }else{ echo '-'; }
                                                        ?></td>
                                                    <td class="text-right" style="vertical-align: middle;"><strong><?= number_format($bridge['price'] * $bridge['quantity'], 2, ',', ' ').$currency['sign'] ?></strong></td>
                                                </tr>
                                                <?php
                                            } else { ?>


                                                <tr>
                                                    <td><strong>Neznámý produkt</strong> <?= $bridge['product_name'] ?> - <small><?= $bridge['variation_values'] ?></small></td>
                                                    <td class="text-center" style="vertical-align: middle;"><?= $bridge['quantity'] ?></td>
                                                    <td class="text-center" style="vertical-align: middle;"><strong class="text-success"><?= $bridge['reserved'] ?></strong></td>
                                                    <td class="text-center" style="vertical-align: middle;"><?php

                                                        if (($bridge['quantity'] - $bridge['reserved']) > 0) { ?>

                                                            <strong class="text-danger">-<?= $bridge['quantity'] - $bridge['reserved'] ?></strong>

                                                        <?php } else { ?>0<?php } ?></td>
                                                    <td class="text-center" style="vertical-align: middle;"><?= number_format($bridge['original_price'], 0, ',', ' ').$currency['sign'] ?></td>


                                                    <td class="text-center" style="vertical-align: middle;"><?= number_format($bridge['price'], 2, ',', ' ').$currency['sign'] ?></td>
                                                    <td class="text-center" style="vertical-align: middle;"><?= $order['discount'] ?> %</td>


                                                    <td class="text-right" style="vertical-align: middle;"><strong><?= number_format($bridge['price'] * $bridge['quantity'], 2, ',', ' ').$currency['sign'] ?></strong></td>
                                                </tr>



                                                <?php

                                            }

                                            $price_with_dph = $price_with_dph + ($bridge['price'] * $bridge['quantity']);
                                        }


                                        $i++

                                        ?>


                                        <tr>
                                            <td height="20" style="vertical-align: middle;"><strong style="font-weight: 500;"><i class="fas fa-truck-loading" style="width: 30px;height: 34px;line-height: 33px;text-align: center;border: 1px solid #ebebeb;background-color: #fdfdfd;margin-right: 14px;"></i>Doprava</strong></td>
                                            <td class="text-center" style="vertical-align: middle;">1</td>
                                            <td class="text-center" style="vertical-align: middle;"><?= thousand_seperator($order['delivery_price']).$currency['sign'] ?></td>
                                            <td class="text-center" style="vertical-align: middle;">-</td>
                                            <td class="text-right" style="vertical-align: middle;"><strong><?= thousand_seperator($order['delivery_price']).$currency['sign'] ?></strong></td>
                                        </tr>




                                        <?php

                                        if($has_discount) {

                                            $i++;

                                            ?>

                                            <tr>
                                                <td height="20" style="vertical-align: middle;"><strong style="font-weight: 500;"><i class="fas fa-percent" style="width: 30px;height: 34px;line-height: 33px;text-align: center;border: 1px solid #ebebeb;background-color: #fdfdfd;margin-right: 14px;"></i>Sleva</strong></td>
                                                <td class="text-center" style="vertical-align: middle;">1</td>
                                                <td class="text-center" style="vertical-align: middle;">-<?= thousand_seperator($total_discount).$currency['sign'] ?></td>
                                                <td class="text-center" style="vertical-align: middle;">-</td>
                                                <td class="text-right" style="vertical-align: middle;"><strong>-<?= thousand_seperator($total_discount).$currency['sign'] ?></strong></td>
                                            </tr>

                                            <?php
                                        }



                                        ?>


                                        </tbody>
                                    </table>

                                    <table class="table table-stripped table-hover" style="width: 50%; float: right; text-align: left; font-size: 13px;margin-bottom: -40px;">
                                        <tbody>
                                        <tr>
                                            <td style="padding: 12px 8px;">Druh doručení:</td>
                                            <td style="padding: 12px 8px;"><strong><?= $delivery['name'] ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 12px 8px;">Způsob úhrady:</td>
                                            <td style="padding: 12px 8px;"><strong><?= $payment['name'] ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 12px 8px;"><strong>Cena celkem:</strong></td>
                                            <td style="padding: 12px 8px;">
                                                <span style="font-size: 18px; margin-bottom: 0; font-family: inherit;  font-weight: normal; "><strong style=" font-weight: 600; "><?= thousand_seperator($order['total']) ?></strong> <?= $currency['sign'] ?></span></td>
                                        </tr>
                                        </tbody>
                                    </table>

                                    <div style="clear: both;"></div>



                        </div>

                    </div>
                    <div class="modal-footer" style="text-align:left;"> <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>

                       <a href="/admin/controllers/generators/order_invoice?id=<?= $order['id'] ?>&eet=<?= $eet ?>&type=order" class="btn btn-success btn-icon icon-left" style="float: right;">Vystavit fakturu
                                <i class="entypo-doc-text"></i></a>

        </div>
    </div>
</div>
</div>





<div class="modal fade" id="separate_order" aria-hidden="true" style="display: none;margin-top: 3%;">

	<div class="modal-dialog" style="padding-top: 8%;">

		<form role="form" method="post" action="zobrazit-objednavku?id=<?= $order['id'] ?>&action=separate_order" enctype="multipart/form-data">
		<div class="modal-content">
			<div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

				<h4 class="modal-title">Rozdělení objednávky #<?= $order['id'] ?></h4> </div>

			<div class="modal-body">

				<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Položky přenesené do nové objednávky
					</div>

				</div>

						<div class="panel-body">

							<div class="form-group">
						<?php

    $orders_products_bridge = $mysqli->query("SELECT * FROM orders_products_bridge WHERE aggregate_id = '" . $order['id'] . "' AND aggregate_type = 'order'");

    while ($bridge = mysqli_fetch_array($orders_products_bridge)) {

        if ($bridge['variation_id'] != 0) {

            $product_query = $mysqli->query("SELECT *, s.id as ajdee, s.price as price FROM products p, products_variations s WHERE p.id = '" . $bridge['product_id'] . "' AND p.id = s.product_id AND s.id = '" . $bridge['variation_id'] . "'");
            $product = mysqli_fetch_array($product_query);

            $select = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $product['ajdee'] . "'") or die($mysqli->error);
            $desc = "";
            while ($var = mysqli_fetch_array($select)) {

                $desc = $desc . $var['name'] . ': ' . $var['value'] . ' ';

            }

            $price = number_format($product['price'], 0, ',', ' ') . $currency['sign'];

            $product_title = $product['sku'] . ' – ' . $product['productname'] . ' – ' . $desc . ' – ' . $price;

            $sku = $product['sku'];

        } else {

            $product_query = $mysqli->query("SELECT * FROM products p, products_sites s WHERE p.id = '" . $bridge['product_id'] . "' AND p.id = s.product_id");

            $product = mysqli_fetch_array($product_query);

            $price = number_format($product['price'], 0, ',', ' ') . $currency['sign'];

            $product_title = $product['code'] . ' – ' . $product['productname'] . ' – ' . $price;

            $sku = $product['code'];
        }

        ?>



						<div class="specification" style="float: left; width: 100%;">


							<div class="col-sm-1" style="padding: 0 0px 0 11px;">
							<input class="form-control" name="<?= $sku ?>" id="<?= $sku ?>" value="yes" type="checkbox"/>
						</div>

							<div class="col-sm-8" style="margin-bottom: 8px; padding: 0;">


							<input type="text" class="form-control" id="specification_name" name="product_name[]" value="<?= $product_title ?>" placeholder="Název produktu" disabled>

							<input type="text" class="form-control" id="copy_this_third" name="product_sku[]" value="<?= $sku ?>" placeholder="SKU produktu" style="display: none;" disabled>

							</div>
							<div class="col-sm-1" style="padding: 0 0px 0 8px;">
								<input type="text" class="form-control text-center" id="specification_value" name="product_quantity[]" value="<?= $bridge['quantity'] ?>" placeholder="Počet" disabled>
							</div>

							<div class="col-sm-2" style="padding: 0 0px 0 8px;">
								<input type="text" class="form-control text-center" id="specification_value" name="product_price[]" value="<?= $bridge['price'] ?>" placeholder="Počet" disabled>
							</div>



						</div>

						<?php

    }?>
					</div>



				</div>
				</div>

			</div>
<div class="modal-footer" style="text-align:left;"> <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>

	<a href="#" style="float:right;"><button type="submit" class="btn btn-orange btn-icon icon-left">Rozdělit
					<i class="entypo-resize-full"></i></button></a>
	</div>
	</div>
        </form>

    </div>
</div>

<?php include VIEW . '/default/footer.php'; ?>



<?php

} else {

    include INCLUDES . "/404.php";

}?>


