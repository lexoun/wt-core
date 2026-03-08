<?php
use Salamek\Zasilkovna as Zasilkovna;

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$id = $_REQUEST['id'];

$orderquery = $mysqli->query('SELECT * FROM orders WHERE id="' . $id . '"') or die($mysqli->error);

if (mysqli_num_rows($orderquery) > 0) {

    $order = mysqli_fetch_assoc($orderquery);

    $forceRegenerate = false;
    $hasInvoice = false;
    $denyEdit = false;

    $invoice_id = 0;
    $invoice_query = $mysqli->query("SELECT id, date, export_id FROM orders_invoices WHERE order_id = '$id' AND type = 'order' AND status != 'odd' order by id desc");
    if (mysqli_num_rows($invoice_query) > 0) {

        while ($invoice = mysqli_fetch_array($invoice_query)) {

            $hasInvoice = true;

            $correct_query = $mysqli->query("SELECT id FROM orders_invoices WHERE invoice_id = '" . $invoice['id'] . "' order by id desc LIMIT 1");

            if (date("Y-m", strtotime($invoice['date'])) == date("Y-m") && mysqli_num_rows($correct_query) == 0) {

                $invoice_id = $invoice['id'];
                $forceRegenerate = true;

            }

            if($invoice['export_id'] != 0){

                $forceRegenerate = false;
                $denyEdit = true;

            }
        }
    }

    if ($client['email'] == 'becher@saunahouse.cz') {
        $hasInvoice = false;
        $forceRegenerate = false;
    }

    $address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $order['shipping_id'] . '" WHERE b.id = "' . $order['billing_id'] . '"') or die($mysqli->error);
    $address = mysqli_fetch_assoc($address_query);

    $pagetitle = 'Upravit objednávku';

//    if (isset($order['order_status']) && $order['order_status'] == 0) {
//        $bread1 = "Nezpracované objednávky";
//        $abread1 = "nezpracovane-objednavky";
//    } elseif (isset($order['order_status']) && $order['order_status'] == 1) {
//        $bread1 = "Přijaté objednávky";
//        $abread1 = "prijate-objednavky";
//    } elseif (isset($order['order_status']) && $order['order_status'] == 2) {
//        $bread1 = "Připravené objednávky";
//        $abread1 = "pripravene-objednavky";
//    } elseif (isset($order['order_status']) && $order['order_status'] == 3) {
//        $bread1 = "Vyexpedované objednávky";
//        $abread1 = "vyexpedovane-objednavky";
//    } else {
//        $bread1 = "Stornované objednávky";
//        $abread1 = "stornovane-objednavky";
//	}

    $bread1 = "Editace objednávek";
    $abread1 = "editacek-objednavek";

    if (isset($order['user_id'])) {

        $getclientquery = $mysqli->query('SELECT user_name FROM demands WHERE id="' . $order['user_id'] . '"') or die($mysqli->error);
        $getclient = mysqli_fetch_assoc($getclientquery);

        if (mysqli_num_rows($getclientquery) > 0) {
            $isclient = 1;
        }

    }


    if (isset($_POST['username']) && $_POST['username'] != "") {

        $name = $_POST['username'];
        $parts = explode(" ", $name);
        $lastname = array_pop($parts);
        $firstname = implode(" ", $parts);
        $selectedquery = $mysqli->query('SELECT * FROM demands WHERE user_name = "' . $_POST['username'] . '"') or die($mysqli->error);

        if (mysqli_num_rows($selectedquery) == 0) {

            $newuser = 1;

            $susernm = $firstname . " " . $lastname;

        } else {
            $selected = mysqli_fetch_assoc($selectedquery);
            $susernm = $selected['user_name'];

        }
    }

    if (isset($_REQUEST['action']) && $_REQUEST['action'] == "edit") {

        $final_vat = 100 + $_POST['vat'];

        $billing_zipcode = preg_replace('/\s+/', '', $_POST['billing_zipcode']);
        $billing_phone = preg_replace('/\s+/', '', $_POST['billing_phone']);
        $billing_email = preg_replace('/\s+/', '', $_POST['billing_email']);

		$billing_id = 0;
        if ($order['billing_id'] != '0') {

            $mysqli->query("UPDATE addresses_billing SET 
                 billing_company = '" . $_POST['billing_company'] . "', 
                 billing_name = '" . $_POST['billing_name'] . "', 
                 billing_surname = '" . $_POST['billing_surname'] . "', 
                 billing_street = '" . $_POST['billing_street'] . "', 
                 billing_city = '" . $_POST['billing_city'] . "', 
                 billing_zipcode = '" . $billing_zipcode . "', 
                 billing_country = '" . $_POST['billing_country'] . "', 
                 billing_ico = '" . $_POST['billing_ico'] . "', 
                 billing_dic = '" . $_POST['billing_dic'] . "', 
                 billing_email = '" . $billing_email . "', 
                 billing_phone = '" . $billing_phone . "' 
             WHERE id = '" . $order['billing_id'] . "'") or die($mysqli->error);

             $billing_id = $order['billing_id'];

        } else {

            $mysqli->query("INSERT INTO addresses_billing (
                    billing_company, 
                    billing_ico, 
                    billing_dic, 
                    billing_name, 
                    billing_surname, 
                    billing_street, 
                    billing_city, 
                    billing_zipcode, 
                    billing_country, 
                    billing_phone, 
                    billing_email
                ) VALUES (
                    '" . $_POST[' billing_company '] . "', 
                    '" . $_POST[' billing_ico '] . "', 
                    '" . $_POST[' billing_dic '] . "', 
                    '" . $_POST[' billing_name '] . "', 
                    '" . $_POST[' billing_surname '] . "', 
                    '" . $_POST[' billing_street '] . "', 
                    '" . $_POST[' billing_city '] . "', 
                    '" . $billing_zipcode . "', 
                    '" . $_POST[' billing_country '] . "', 
                    '" . $billing_phone . "', 
                    '" . $billing_email . "'
                  )") or die($mysqli->error);
            $billing_id = $mysqli->insert_id;

        }

        $hasShipping = false;
		$shipping_id = 0;
        if (($_POST['shipping_company'] != '' || $_POST['shipping_name'] != '' || $_POST['shipping_surname'] != '' || $_POST['shipping_street'] != '' || $_POST['shipping_city'] != '') && isset($_POST['different_shipping']) && $_POST['different_shipping'] == 'yes') {

            $hasShipping = true;

            if ($order['shipping_id'] != '0') {

                $mysqli->query("UPDATE 
                      addresses_shipping 
                    SET 
                      shipping_company = '" . $_POST['shipping_company'] . "', 
                      shipping_name = '" . $_POST['shipping_name'] . "', 
                      shipping_surname = '" . $_POST['shipping_surname'] . "', 
                      shipping_street = '" . $_POST['shipping_street'] . "', 
                      shipping_city = '" . $_POST['shipping_city'] . "', 
                      shipping_zipcode = '" . $_POST['shipping_zipcode'] . "', 
                      shipping_country = '" . $_POST['shipping_country'] . "' 
                    WHERE 
                      id = '" . $order['shipping_id'] . "'") or die($mysqli->error);

                $shipping_id = $order['shipping_id'];

            } else {

                $insert_shipping = $mysqli->query("INSERT INTO addresses_shipping (
                      shipping_company, shipping_name, 
                      shipping_surname, shipping_street, 
                      shipping_city, shipping_zipcode, 
                      shipping_country
                    ) 
                    VALUES 
                      (
                        '" . $_POST['shipping_company'] . "', 
                        '" . $_POST['shipping_name'] . "', 
                        '" . $_POST['shipping_surname'] . "', 
                        '" . $_POST['shipping_street'] . "', 
                        '" . $_POST['shipping_city'] . "', 
                        '" . $_POST['shipping_zipcode'] . "', 
                        '" . $_POST['shipping_country'] . "'
                      )") or die($mysqli->error);

                $shipping_id = $mysqli->insert_id;

            }

        } elseif (isset($_POST['different_shipping']) && ($_POST['different_shipping'] != 'yes' && $order['shipping_id'] != 0)) {

            $mysqli->query("DELETE FROM addresses_shipping WHERE id = '" . $order['shipping_id'] . "'") or die($mysqli->error);
            $shipping_id = 0;
        }


        // only if has invoice
        if($hasInvoice && !$forceRegenerate) {

            $selected_country = 'CZ';
            if($hasShipping || empty($_POST['billing_country'])){

                $selected_country = $_POST['shipping_country'];

            }elseif(!empty($_POST['billing_country'])){

                $selected_country = $_POST['billing_country'];

            }

            if($selected_country != 'CZ' && $selected_country != 'SK'){ $selected_country = 'EU'; }

            $delivery = $_POST['delivery_'.$selected_country];

            $get_delivery_price = $mysqli->query("SELECT price, transporter_company, link_name FROM shops_delivery_methods WHERE link_name = '" . $delivery . "'");
            $delivery_price = mysqli_fetch_array($get_delivery_price);

            if ($_POST['delivery_special_price'] != "") { $delivery_price['price'] = $_POST['delivery_special_price']; }

            $consignment_id = $order['consignment_id'];
            if($delivery_price['transporter_company'] == 'Uloženka'){

                $shipping_location = $_POST['shipping_location_ul'];

            }elseif($delivery_price['transporter_company'] == 'Balík na poštu'){

                $shipping_location = $_POST['shipping_location_cp'];

            }elseif($delivery_price['transporter_company'] == 'Zásilkovna'){

                $shipping_location = $_POST['shipping_location_zasilkovna'];
                $shipping_location_id = $_POST['shipping_location_id_zasilkovna'];

                if($shipping_location != $order['shipping_location'] && !empty($order['consignment_id'])){
                    $consignment_id = 0;
                }

            }else{

                $shipping_location = '';
                $shipping_location_id = 0;

            }


            $mysqli->query("UPDATE orders SET 
                  shipping_location = '".$shipping_location."',
                  shipping_location_id = '".$shipping_location_id."',
                  billing_id = '" . $billing_id . "', 
                  order_shipping_method = '" . $delivery . "',
                  shipping_id = '" . $shipping_id . "', 
                  customer_email = '" . $_POST['billing_email'] . "', 
                  customer_phone = '" . $_POST['billing_phone'] . "', 
                  order_status = '" . $_POST['order_status'] . "', 
                  customer_note = '" . $mysqli->real_escape_string($_POST['customer_note']) . "', 
                  admin_note = '" . $mysqli->real_escape_string($_POST['admin_note']) . "', 
                  weight = '" . $_POST['weight'] . "' 
              WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);


        }else{


            $selected_country = 'CZ';
            if($hasShipping || empty($_POST['billing_country'])){

                $selected_country = $_POST['shipping_country'];

            }elseif(!empty($_POST['billing_country'])){

                $selected_country = $_POST['billing_country'];

            }

            if($selected_country != 'CZ' && $selected_country != 'SK'){ $selected_country = 'EU'; }

            $delivery = $_POST['delivery_'.$selected_country];

            $get_delivery_price = $mysqli->query("SELECT price, transporter_company, link_name FROM shops_delivery_methods WHERE link_name = '" . $delivery . "'");
            $delivery_price = mysqli_fetch_array($get_delivery_price);

            if ($_POST['delivery_special_price'] != "") { $delivery_price['price'] = $_POST['delivery_special_price']; }

            $consignment_id = $order['consignment_id'];
            if($delivery_price['transporter_company'] == 'Uloženka'){

                $shipping_location = $_POST['shipping_location_ul'];

            }elseif($delivery_price['transporter_company'] == 'Balík na poštu'){

                $shipping_location = $_POST['shipping_location_cp'];

            }elseif($delivery_price['transporter_company'] == 'Zásilkovna'){

                $shipping_location = $_POST['shipping_location_zasilkovna'];
                $shipping_location_id = $_POST['shipping_location_id_zasilkovna'];

                if($shipping_location != $order['shipping_location'] && !empty($order['consignment_id'])){
                    $consignment_id = 0;
                }

            }else{

                $shipping_location = '';
                $shipping_location_id = 0;

            }

            $currency = $_POST['currency'];
            $exchange_rate = $_POST[$currency.'_rate'];

            $mysqli->query("UPDATE orders SET 
                order_currency = '".$currency."',
                shipping_location = '".$shipping_location."',
                shipping_location_id = '".$shipping_location_id."',
                exchange_rate = '".$exchange_rate."',
                consignment_id = '".$consignment_id."',
                billing_id = '" . $billing_id . "', 
                shipping_id = '" . $shipping_id . "', 
                order_tracking_number = '" . $_POST['order_tracking_number'] . "', 
                customer_email = '" . $_POST['billing_email'] . "', 
                customer_phone = '" . $_POST['billing_phone'] . "', 
                order_status = '" . $_POST['order_status'] . "', 
                vat = '" . $_POST['vat'] . "', 
                customer_note = '" . $_POST['customer_note'] . "', 
                admin_note = '" . $_POST['admin_note'] . "', 
                order_shipping_method = '" . $delivery . "', 
                payment_method = '" . $_POST['payment'] . "', 
                delivery_price = '" . $delivery_price['price'] . "', 
                location_id = '" . $_POST['location'] . "', 
                weight = '" . $_POST['weight'] . "'
            WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

            if (isset($_POST['product_sku'])) {

                $post_products = $_POST['product_sku'];

            } else {

                $post_products = array();

            }

            $find_simple_product = $mysqli->query("SELECT b.product_id, b.variation_id, b.reserved, p.code  FROM products p, orders_products_bridge b WHERE p.id = b.product_id AND b.aggregate_id = '" . $_REQUEST['id'] . "' AND b.aggregate_type = 'order' order by p.id desc") or die($mysqli->error);

            $find_variable_product = $mysqli->query("SELECT b.product_id, b.variation_id, b.reserved, v.sku FROM products_variations v, orders_products_bridge b WHERE v.id = b.variation_id AND b.aggregate_id = '" . $_REQUEST['id'] . "' AND b.aggregate_type = 'order'") or die($mysqli->error);

            $array1 = array();
            while ($row = mysqli_fetch_assoc($find_simple_product)) {
                $array1[] = $row['code'];
            }
            while ($row = mysqli_fetch_assoc($find_variable_product)) {
                $array1[] = $row['sku'];
            }

            $array2 = array_filter($post_products);

            $dups_old = array();
            foreach (array_count_values($array2) as $val => $c) {
                if ($c > 1) {$dups_old[] = $val;}
            }

            $dups_new = array();
            foreach (array_count_values($array1) as $val => $c) {
                if ($c > 1) {$dups_new[] = $val;}
            }

            $check_duplicants = array_diff((array)$dups_new, (array)$dups_old);
            $removed_products = array_diff((array)$array1, (array)$array2); // odebírané produkty
            $removed_products = array_merge((array)$removed_products, (array)$check_duplicants);

            include CONTROLLERS . "/product-stock-controller.php";

            foreach ($removed_products as $removed) {

                $product = array();

                $product_query = $mysqli->query("SELECT 
                        b.id, b.product_id, b.variation_id, b.reserved 
                    FROM products p, orders_products_bridge b 
                    WHERE p.code = '$removed' AND b.variation_id = 0 AND p.id = b.product_id AND b.aggregate_id = '" . $id . "' AND b.aggregate_type = 'order'
                UNION 
                    SELECT b.id, b.product_id, b.variation_id, b.reserved 
                    FROM products_variations v, orders_products_bridge b 
                    WHERE v.sku = '$removed' AND v.id = b.variation_id AND b.variation_id != 0 AND b.aggregate_id = '" . $id . "'
                ") or die($mysqli->error);

                if (mysqli_num_rows($product_query) != 0) {

                    $product = mysqli_fetch_assoc($product_query);

                    $mysqli->query("DELETE FROM orders_products_bridge WHERE id = '" . $product['id'] . "'");

                    product_update($product['product_id'], $product['variation_id'], $order['location_id'], $product['reserved'], $client['id'], 'order_change', $id);

                }

            }

            $added_products = array_diff((array)$array2, (array)$array1); // přidávané produkty

            $stable_products = array_intersect((array)$array1, (array)$array2);

            $overall_purchase = 0;
            $overallcena = 0;

            if (isset($post_products)) {

                $post = array_filter($post_products);

                if (!empty($post)) {

                    foreach ($post as $post_index => $posterino) {

                        $product = array();

                        $stock_allocation['posterino'] = $posterino;
                        $stock_allocation['id'] = $id;

                        $stock_allocation['bridge'] = 'orders_products_bridge';
                        $stock_allocation['id_identify'] = 'order_id';
                        $stock_allocation['quantity'] = $_POST['product_quantity'][$post_index];
                        $stock_allocation['location'] = $_POST['location'];
                        $stock_allocation['type'] = 'order';
                        $stock_allocation['quantity'] = $_POST['product_quantity'][$post_index];
                        $stock_allocation['total_quantity'] = $_POST['product_quantity'][$post_index];

                        $stock_allocation['price'] = product_price(
                            $_POST['product_price'][$post_index],
                            $_POST['product_original_price'][$post_index],
                            $_POST['vat'],
                            $order['vat'],
                            $_POST['product_discount'][$post_index]
                        );


                        $quantity = $_POST['product_quantity'][$post_index];

                        // add new products to order
                        if (in_array($posterino, $added_products)) {

                            if (!empty($_POST['product_quantity'][$post_index])) {

                                //$total_quantity = $quantity;

                                // VYSKLADNĚNÍ A PŘIPOJENÍ K TÉTO OBJEDNÁVCE
                                include_once CONTROLLERS . "/product-stock-update.php";
                                $response = stock_allocate($stock_allocation);

                                if ($response['reserve'] < $stock_allocation['quantity']) {

                                    $quantity = $stock_allocation['quantity'] - $response['reserve'];
                                    include CONTROLLERS . "/product-delivery-update.php";

                                }

                            }


                        // update products already in order
                        } elseif (in_array($posterino, $stable_products)) {

                            $product_query = $mysqli->query("
                            
                            SELECT p.price, p.productname, b.product_id, b.variation_id, p.delivery_time, b.reserved, b.quantity, b.delivered, b.id, cat.discount, p.purchase_price, p.ean FROM products p, orders_products_bridge b, products_cats cat, products_sites_categories minicat WHERE minicat.category = cat.seoslug AND p.code = '$posterino' AND p.id = b.product_id AND b.aggregate_id = '" . $_REQUEST['id'] . "' AND b.aggregate_type = 'order' GROUP BY p.id 
                            
                            UNION 
                            
                            SELECT 
                            v.price, p.productname, b.product_id, b.variation_id, p.delivery_time, b.reserved, b.quantity, b.delivered, b.id, cat.discount, v.purchase_price, v.ean 
                            FROM products p, orders_products_bridge b, products_variations v, products_cats cat, products_sites_categories minicat WHERE minicat.category = cat.seoslug AND v.product_id = p.id AND v.sku = '$posterino' AND v.id = b.variation_id AND b.aggregate_id = '" . $_REQUEST['id'] . "' AND b.aggregate_type = 'order' GROUP BY v.id
                            
                            ") or die($mysqli->error);

                            // product exists
                            if (mysqli_num_rows($product_query) != 0) {

                                $product = mysqli_fetch_assoc($product_query);

                                /* todo different branch for stock settlement
                                if($order['location_id'] != $_POST['location']){

                                    // return reserved quantity to prev location
                                    product_update($product['product_id'], $product['variation_id'], $order['location_id'], $product['reserved'], $client['id'], 'order_change', $id);

                                    // alocate quantity from new location
                                    if (!empty($quantity)) {

                                        echo '!empty quantity<br>';

                                        $total_quantity = $quantity;

                                        // VYSKLADNĚNÍ A PŘIPOJENÍ K TÉTO OBJEDNÁVCE
                                        include_once CONTROLLERS . "/product-stock-update.php";

                                        if ($reserve < $quantity) {

                                            $quantity = $quantity - $reserve;
                                            include CONTROLLERS . "/product-delivery-update.php";

                                        }

                                    }

                                }

                                */


                                $quantity = $_POST['product_quantity'][$post_index];

                                /* quantity is same, just price change  */
                                if ($quantity == $product['quantity']) {

                                    $mysqli->query("UPDATE orders_products_bridge 
                                        SET price = '".$stock_allocation['price']['price']."', 
                                            discount = '" . $stock_allocation['price']['discount'] . "', 
                                            discount_net = '" . $stock_allocation['price']['discount_net'] . "' 
                                        WHERE id = '" . $product['id'] . "'")or die($mysqli->error);


                                /* NOVÉ MNOŽSTVÍ JE MENŠÍ NEŽ PŮVODNÍ MNOŽSTVÍ +++ REZERVOVANÉ MNOŽSTVÍ JE VĚTŠÍ NEŽ NOVÉ MNOŽSTVÍ  */
                                } elseif ($quantity < $product['quantity'] && ($quantity < ($product['reserved'] + $product['delivered']))) {


                                    // množství přidané k jiným objednávkám
                                    $reduced_quantity = ($product['reserved'] + $product['delivered']) - $quantity;

                                    // monžství delivered
                                    if ($reduced_quantity > $product['delivered']) {

                                        $delivered_quantity = $product['delivered'];

                                    } else {

                                        $delivered_quantity = $reduced_quantity;

                                    }

                                    if ($delivered_quantity > 0) {

                                        // DODÁVKY PŘIDÁNÍ K JINÝM OBJEDNÁVKÁM
                                        product_delivered_update($product['product_id'], $product['variation_id'], $delivered_quantity, 'order', $id);

                                    }

                                    $reserved_quantity = $reduced_quantity - $delivered_quantity;

                                    if ($reserved_quantity > 0) {

                                        // NASKLADNĚNÍ A PŘIDÁNÍ K JINÝM OBJEDNÁVKÁM
                                        product_update($product['product_id'], $product['variation_id'], $_POST['location'], $reserved_quantity, $client['id'], 'order_change', $id);

                                    }

                                    $final_reserved = $product['reserved'] - $reserved_quantity;

                                    $mysqli->query("UPDATE orders_products_bridge 
                                        SET quantity = '$quantity', 
                                            reserved = '" . $final_reserved . "', 
                                            delivered = delivered - $delivered_quantity, 
                                            price = '" . $stock_allocation['price']['price'] . "', 
                                            discount = '" . $stock_allocation['price']['discount'] . "', 
                                            discount_net = '".$stock_allocation['price']['discount_net']."' 
                                        WHERE id = '" . $product['id'] . "'");

                                    /* NOVÉ VĚTŠÍ NEŽ PŮVODNÍ +++ REZERVOVANÉ MENŠÍ NEŽ NOVÉ  */

                                } elseif ($quantity > $product['quantity'] && ($quantity > ($product['reserved'] + $product['delivered']))) {

                                    $total_quantity = $quantity;

                                    // množství připojené k této objednávce
                                    $quantity -= ($product['reserved'] + $product['delivered']);

                                    // VYSKLADNĚNÍ A PŘIPOJENÍ K TÉTO OBJEDNÁVCE
                                    include_once CONTROLLERS . "/product-stock-update.php";
                                    $response = stock_allocate($stock_allocation);

                                    if ($response['reserve'] < $quantity) {

                                        $quantity -=  $response['reserve'];

                                        include CONTROLLERS . "/product-delivery-update.php";

                                    }

                                    $mysqli->query("UPDATE orders_products_bridge SET quantity = '" . $total_quantity . "' WHERE id = '" . $product['id'] . "'");

                                } else {

                                    $mysqli->query("UPDATE orders_products_bridge SET quantity = '" . $quantity . "' WHERE id = '" . $product['id'] . "'");

                                }


                            }

                        }

                    }

                }

            }


            /* ZJIŠTĚNÍ CENY TOTAL SQL */

            // multiple same products = sum of products and later rounding
            // (15.63 - 3.126) + (15.63 - 3.126) =>  25.008 => rounding => 25.01!
            // todo tabulka orders, products a products_variations asi není potřeba

            /*
            $get_price = $mysqli->query("SELECT 
                SUM(total) as total, SUM(purchase_price) as purchase_price, SUM(discount_net) as discount_net 
            FROM (
                SELECT 
                   sum(b.quantity * b.price) as total, sum(b.purchase_price * b.quantity) as purchase_price, round(sum(b.discount_net * b.quantity), 2) as discount_net 
                    FROM products p, orders_products_bridge b, orders o 
                    WHERE o.id = b.order_id AND p.id = b.product_id AND o.id = '" . $id . "' AND p.type = 'simple' 
                UNION ALL SELECT 
                    sum(b.quantity * b.price) as total, sum(b.purchase_price * b.quantity) as purchase_price, round(sum(b.discount_net * b.quantity), 2) as discount_net 
                    FROM products_variations v, orders_products_bridge b, orders o 
                    WHERE o.id = b.order_id AND v.id = b.variation_id AND o.id = '" . $id . "' AND b.variation_id != 0
                ) as products") or die($mysqli->error);

            $price_data = mysqli_fetch_array($get_price);
            */


            // new simplified getPrice
            /*
            $getPriceNew = $mysqli->query("SELECT 
                SUM(price) as total, SUM(purchase_price) as purchase_price, SUM(discountRounded) as discount_net 
            FROM ( 
                SELECT (purchase_price * quantity) as purchase_price, round(discount_net * quantity, 2) as discountRounded, (price * quantity) as price
                FROM orders_products_bridge 
                WHERE order_id = '".$id."'
            ) as products")or die($mysqli->error);

            $price_data = mysqli_fetch_array($getPriceNew);*/

            // new simplified getPrice
            $getPriceNew = $mysqli->query("SELECT
                SUM(total) as total, SUM(purchase_price) as purchase_price, SUM(discountRounded) as discount_net
            FROM (
                SELECT round(((price - discount_net) * quantity), 2) as total,(purchase_price * quantity) as purchase_price, round(discount_net * quantity, 2) as discountRounded
                FROM orders_products_bridge
                WHERE aggregate_id = '".$id."' AND aggregate_type = 'order'
            ) as products")or die($mysqli->error);

            $price_data = mysqli_fetch_array($getPriceNew);


            $overallcena = $price_data['total'] + $delivery_price['price'];
            $overall_purchase = $price_data['purchase_price'];

            $coeficient = vat_coeficient($_POST['vat']);
            $price = get_price($overallcena, $coeficient);


            // if rounding
            $price['rounded'] = 0;
            if($_POST['payment'] == 'cash' || $_POST['payment'] == 'cod'){

                $price['single'] = round($price['single']);
                $price['rounded'] = number_format($price['single'] - $overallcena, 2, '.', '');

            }



            $mysqli->query("UPDATE orders SET total_vat = '".$price['vat']."', total_rounded = '".$price['rounded']."', total_without_vat = '".$price['without_vat']."', total = '".$price['single']."', order_purchase = '$overall_purchase', discount_net = '".$price_data['discount_net']."' WHERE id = '" . $_REQUEST['id'] . "'");



            // another warehouse selected
            if($order['location_id'] != $_POST['location']){

                $products_bridge_query = $mysqli->query("SELECT b.*, p.code, v.sku FROM orders_products_bridge b LEFT JOIN products p ON p.id = b.product_id LEFT JOIN products_variations v ON v.product_id = b.product_id AND v.id = b.variation_id WHERE aggregate_id = '".$order['id']."' AND b.aggregate_type = 'order'")or die($mysqli->error);

                while($product = mysqli_fetch_assoc($products_bridge_query)){
                    // $bridge_loop

                    if(!empty($product['sku'])){
                        $posterino = $product['sku'];
                    }else{
                        $posterino = $product['code'];
                    }

                    $stock_allocation['posterino'] = $posterino;
                    $stock_allocation['id'] = $id;

                    $stock_allocation['bridge'] = 'orders_products_bridge';
                    $stock_allocation['id_identify'] = 'order_id';
                    $stock_allocation['quantity'] = $product['quantity'];
                    $stock_allocation['location'] = $_POST['location'];
                    $stock_allocation['type'] = 'order';
                    $stock_allocation['quantity'] = $product['quantity'];
                    $stock_allocation['total_quantity'] = $product['quantity'];

                    $stock_allocation['price'] = product_price(
                        $product['price'],
                        $product['original_price'],
                        $_POST['vat'],
                        $order['vat'],
                        $product['discount']
                    );


                    // stock up all products into previous warehouse
                    if ($product['reserved'] > 0) {

                        // NASKLADNĚNÍ A PŘIDÁNÍ K JINÝM OBJEDNÁVKÁM
                        product_update($product['product_id'], $product['variation_id'], $product['location_id'], $product['reserved'], $client['id'], 'order_change', $product['id']);

                    }

                    // set for all products in bridge the new warehouse
                    $mysqli->query("UPDATE orders_products_bridge SET location_id = '".$stock_allocation['location']."', reserved = 0 WHERE id = '".$product['id']."'")or die($mysqli->error);

                    // try to assign available stock quantities from new warehouse
                    $total_quantity = $product['quantity'];

                    // VYSKLADNĚNÍ A PŘIPOJENÍ K TÉTO OBJEDNÁVCE
                    include_once CONTROLLERS . "/product-stock-update.php";
                    $response = stock_allocate($stock_allocation);

                    if ($response['reserve'] < $quantity) {

                        $quantity -= $reserve;

                        include CONTROLLERS . "/product-delivery-update.php";

                    }
                }
            }
        }


            if (isset($_POST['order_status']) && $_POST['order_status'] == 4 && $order['order_status'] != 4) {

                $topupquery = $mysqli->query("SELECT b.product_id, b.reserved, p.customer, b.variation_id FROM orders_products_bridge b, products p WHERE p.id = b.product_id AND b.aggregate_id = '" . $_REQUEST['id'] . "' AND b.aggregate_type = 'order'") or die($mysqli->error);

                while ($topup = mysqli_fetch_array($topupquery)) {

                    $quantity = $topup['reserved'];

                    if ($quantity > 0) {

                        product_update($topup['product_id'], $topup['variation_id'], $_POST['location'], $quantity, $client['id'], 'order_cancel', $id);

                    }

                    $update = $mysqli->query("UPDATE orders_products_bridge SET reserved = '0' WHERE product_id = '" . $topup['product_id'] . "' AND aggregate_id = '" . $_REQUEST['id'] . "' AND aggregate_type = 'order'");
                }

            } elseif ($_POST['order_status'] < 4 && $order['order_status'] == 4) {

                $topupquery = $mysqli->query("SELECT variation_id 
                    FROM orders_products_bridge WHERE aggregate_id = '" . $_REQUEST['id'] . "' AND aggregate_type = 'order'") or die($mysqli->error);
                while ($topup = mysqli_fetch_array($topupquery)) {

                    if (isset($topup['variation_id']) && $topup['variation_id'] == 0) {

                        $topupquery = $mysqli->query("SELECT b.product_id, b.reserved, b.quantity, p.instock, p.ean FROM orders_products_bridge b, products p WHERE p.id = b.product_id AND b.aggregate_id = '" . $_REQUEST['id'] . "' AND b.aggregate_type = 'order'") or die($mysqli->error);

                        while ($topup = mysqli_fetch_array($topupquery)) {

                            if ($topup['quantity'] > $topup['instock']) {

                                $reserve = $topup['instock'];

                            } else {

                                $reserve = $topup['quantity'];

                            }

                            $mysqli->query("UPDATE orders_products_bridge SET reserved = '$reserve' WHERE product_id = '" . $topup['product_id'] . "' AND aggregate_id = '" . $_REQUEST['id'] . "' AND aggregate_type = 'order'");
                            $mysqli->query("UPDATE products SET instock = instock - $reserve WHERE id = '" . $topup['product_id'] . "'");

                        }

                    } else {

                        $topupquery = $mysqli->query("SELECT b.product_id, v.id, b.reserved, b.quantity, v.stock, v.ean FROM orders_products_bridge b, products_variations v WHERE v.id = b.variation_id AND b.aggregate_id = '" . $_REQUEST['id'] . "' AND b.aggregate_type = 'order'") or die($mysqli->error);

                        while ($search = mysqli_fetch_array($topupquery)) {

                            if ($search['quantity'] > $search['stock']) {

                                $reserve = $search['stock'];

                            } else {

                                $reserve = $search['quantity'];

                            }

                            $mysqli->query("UPDATE orders_products_bridge SET reserved = '$reserve' WHERE variation_id = '" . $search['id'] . "' AND aggregate_id = '" . $_REQUEST['id'] . "' AND aggregate_type = 'order'");
                            $mysqli->query("UPDATE products_variations SET stock = stock - $reserve WHERE id = '" . $search['id'] . "' AND product_id = '" . $search['product_id'] . "'");

                        }

                    }

                }

            }




        if (isset($_POST['order_status']) && $_POST['order_status'] == 0) {
            $link = 'nezpracovane';
        } elseif (isset($_POST['order_status']) && $_POST['order_status'] == 1) {
            $link = 'prijate';
        } elseif (isset($_POST['order_status']) && $_POST['order_status'] == 2) {
            $link = 'pripravene';
        } elseif (isset($_POST['order_status']) && $_POST['order_status'] == 3) {
            $link = 'vyexpedovane';
        } else { $link = 'stornovane';}

        $reserved_query = $mysqli->query("SELECT quantity, reserved 
            FROM products p, orders_products_bridge o 
            WHERE p.id = o.product_id AND o.aggregate_id = '$id' AND o.aggregate_type = 'order'");

        $total_reserved = 0;
        $total_missing = 0;

        while ($reserv = mysqli_fetch_array($reserved_query)) {

            $total_reserved = $total_reserved + $reserv['reserved'];

            $total_missing = $total_missing + ($reserv['quantity'] - $reserv['reserved']);

        }

        /* --- API ORDER STATUS UPDATE */
        if ($_POST['order_status'] != $order['order_status'] && $order['order_site'] != 'wellnesstrade' && $order['id'] != 0) {

            $url = 'https://www.wellnesstrade.cz/admin/controllers/stores/status-change';

            $content = '?order_id=' . $order['id'] . '&status=' . $_POST['order_status'] . '&delivery_type=' . $order['payment_method'] . '&site=' . $order['order_site'];

            $parts = parse_url($url);
            $fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 30);
            $out = "GET " . $parts['path'] . $content . " HTTP/1.1\r\n";
            $out .= "Host: " . $parts['host'] . "\r\n";
            $out .= "Content-Length: 0" . "\r\n";
            $out .= "Connection: Close\r\n\r\n";

            fwrite($fp, $out);
            fclose($fp);

        }

        /* --- API ORDER STATUS UPDATE */



        $hasMail = '';
        if (isset($_POST['send_mail']) && $_POST['send_mail'] == 'yes') {

            if (isset($_POST['enable_custom']) && $_POST['enable_custom'] == 'yes') {

                $alternate_text = $_POST['custom_text'];

            }

            include INCLUDES . "/order_status_emails.php";
            $hasMail = '&has_mail=true';

        }


        if($forceRegenerate){

            $redirect = 'https://www.wellnesstrade.cz/admin/pages/orders/zobrazit-objednavku?id=' . $id;
            include CONTROLLERS . "/generators/order_invoice_regenerate.php";

        }

        Header("Location:https://www.wellnesstrade.cz/admin/pages/orders/zobrazit-objednavku?id=" . $id . "&success=edit&missing=" . $total_missing . "&reserved=" . $total_reserved.$hasMail);
        exit;
    }

    $cliquery = $mysqli->query('SELECT user_name FROM demands') or die($mysqli->error);

    $shops_query = $mysqli->query("SELECT * FROM shops") or die($mysqli->error);



    $kurz_url = "http://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_devizoveho_trhu/denni_kurz.txt";
    $kurz_data =  file_get_contents($kurz_url);
    $output = explode("\n", $kurz_data);

    unset($output[0]); // odstranění prvního řádku - datum
    unset($output[count($output)]); // odstranění posledního řádku - nic neobsahuje
    unset($output[1]); // odstranění druhého řádku - legenda pro CSV

    $kurz = array("CZK" => 1);
    foreach($output as $radek){
        $mena = explode("|", $radek);
        $kurz[trim($mena[3])] = str_replace(",",".",trim($mena[4]));
    }


    include VIEW . '/default/header.php';
?>

<script type="text/javascript">
jQuery(document).ready(function($)
{




     $('.rad1').on('switch-change', function () {

     if($('#eh').prop('checked')){

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

<style>

.has-warning .selectboxit-container .selectboxit { border-color: #ffd78a !important;}

.page-body .selectboxit-container .selectboxit-options { margin-top: 40px !important; width: 100% !important;}
.page-body .selectboxit-container .selectboxit { height: 40px;width: 100% !important;}
.page-body .selectboxit-container .selectboxit .selectboxit-text { line-height: 40px; }
.page-body .selectboxit-container .selectboxit .selectboxit-arrow-container { height: 40px;}
.page-body .selectboxit-container .selectboxit .selectboxit-arrow-container:after { line-height: 40px;}

.nicescroll-rails > div:hover {
  background: rgb(53, 174, 255) !important;
}

#custom-scroller { width: 500px; }
.col-2, .col-8, .col-3, .col-4, .col-6 {
  display: inline-block;
  padding: 5px 2%;
  vertical-align: top;
}

.item {
  margin-right: 10px;
}

.col-2 { width: 18%; }
.col-8 { width: 76%; }
.col-3 { width: 26%; }
.col-4 { width: 36%; }
.col-6 { width: 60%; }
.select2-drop img { width: 100%; margin: 2%; }

.bigdrop.select2-container .select2-results {max-height: 300px;}
.bigdrop .select2-results {max-height: 300px;}

</style>

    <script type="text/javascript">

        $(document).ready(function () {

            $('.currency').change(function() {

                let original_currency = '<?= $order['order_currency'] ?>';
                let rate = $(this).data("value");
                let value = '';
                let currency = $(this).val();

                if(currency != 'CZK'){
                    $('.calculator').show('slow');
                }else{
                    $('.calculator').hide('slow');
                }

                $('.final_currency').attr("placeholder", $(this).val()).attr("data-rate", rate);
                $('.final_currency_shortcut').html($(this).val());
                $('.final_currency, .original_currency').val('');

                $('.price-control:visible').each(function(){

                    if($(this).data("default") != undefined) {

                        value = $(this).data("default");

                    }else if($(this).val() != undefined) {

                        value = $(this).val();

                    }

                    if(value != null && value != undefined && value != ''){

                        let exchange = (value / rate).toFixed(2);
                        $(this).val(exchange);

                    }

                });

            });


            var methods = {
            <?php $delivery_methods_query = $mysqli->query("SELECT * FROM shops_delivery_methods");

            while($method = mysqli_fetch_assoc($delivery_methods_query)){


                echo $method['link_name'].': '.$method['price'].',
                ';


            }

            ?>

            };

            $('.delivery_select').change(function(){

                let selected = $(this).val();

                let delivery = methods[$(this).val()];
                let rate = $('.currency:checked').data("value");

                let exchange = (delivery / rate).toFixed(2);

                $('#delivery_special_price').val(exchange);


                if(selected.includes('ulozenka')){

                    $('.ceska_posta').hide('slow');
                    $('.zasilkovna').hide('slow');
                    $('.ulozenka').show('slow');

                }else if(selected.includes('balik_na_postu')){

                    $('.ulozenka').hide('slow');
                    $('.zasilkovna').hide('slow');
                    $('.ceska_posta').show('slow');

                }else if(selected.includes('zasilkovna')){

                    $('.ulozenka').hide('slow');
                    $('.ceska_posta').hide('slow');
                    $('.zasilkovna').show('slow');

                }else{

                    $('.ulozenka').hide('slow');
                    $('.zasilkovna').hide('slow');
                    $('.ceska_posta').hide('slow');

                }


            });


            $(".billing_country").change(function() {

                var country = $(this).val();

                if(country != 'CZ' && country != 'SK'){
                    country = 'EU';
                }

                if($('.shipping_country_group').is(":hidden") || $('.shipping_country').val() == ''){

                    // alert(country);

                    $('.delivery').hide();
                    $('.delivery_'+country).show();

                    $('.delivery_label').html(country);

                }

            });



            $(".shipping_country").change(function() {

                var country = $(this).val();

                if(country == ''){

                    country = $(".billing_country").val();

                }

                if(country != 'CZ' && country != 'SK'){
                    country = 'EU';
                }


                    $('.delivery').hide();
                    $('.delivery_'+country).show();

                    $('.delivery_label').html(country);

            });


        });
    </script>

<?php if($forceRegenerate){ ?>
    <div class="alert alert-warning"><strong>Upozornění!</strong> Upravujete objednávku, u které již byla vystavena faktura. Veškeré úpravy se AUTOMATICKY přegenerují do vystavené faktury.</div>
 <?php } ?>

<?php if($forceRegenerate && $order['payment_method'] == 'cash'){ ?>
    <div class="alert alert-info"><strong>Upozornění!</strong> Objednávka byla fakturována s platbou <strong>Hotově</strong> a částky z toho důvodu nelze pozměnit (již posláno do EET).</div>
<?php } ?>

<?php if (!$forceRegenerate && $hasInvoice) { ?>
        <div class="alert alert-danger"><strong>Upozornění!</strong> Upravujete objednávku s již vyexportovanou fakturou. Faktura již byla importována do účetnictví a není tedy možné měnit jakékoliv její náležitosti.</div>
<?php } ?>

<form role="form" id="order_form" method="post" class="form-horizontal form-groups-bordered validate" action="upravit-objednavku?id=<?= $id ?>&action=edit" enctype="multipart/form-data">

	<div class="row">
    <div class="col-md-5">
    <div class="panel panel-primary" data-collapsed="0">

        <div class="panel-heading">
            <div class="panel-title" style="width: 100%;">
                <strong style="font-weight: 600;">Interní informace</strong>
                <?php if (isset($order['order_site']) && $order['order_site'] != 'wellnesstrade') { ?>
                    <img src="https://www.wellnesstrade.cz/admin/assets/images/<?= $order['order_site'] ?>-shop.png" width="80px" style="float: right;"/>
                <?php } else { ?>
                    <img src="https://www.wellnesstrade.cz/wp-content/uploads/2015/03/logoblack.png" style="padding-top: 7px; padding-bottom: 5px;" width="80px" style="float: right;"/>
                <?php } ?>
            </div>
        </div>

        <div class="form-group">
            <br>
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <textarea name="admin_note" class="form-control autogrow" id="field-7"><?= $order['admin_note'] ?></textarea>
                </div>
            </div>
        </div>

    </div>

    <div class="panel panel-primary" data-collapsed="0">

        <div class="panel-heading">
            <div class="panel-title">
                <strong style="font-weight: 600;">Změna stavu</strong>
            </div>
        </div>

        <div class="panel-body">

            <div class="form-group" style="margin-top: 10px; margin-bottom: 24px;">
                <label class="col-sm-3 control-label" for="order_status" style="padding-top: 14px;">Stav objednávky</label>
                <div class="col-sm-6">
                    <select id="order_status" name="order_status" class="selectboxit">
                        <option value="0" <?php if (isset($order['order_status']) && $order['order_status'] == 0) {echo 'selected';}?>>Nezpracovaná</option>
                        <option value="1" <?php if (isset($order['order_status']) && $order['order_status'] == 1) {echo 'selected';}?>>V řešení</option>
                        <option value="2" <?php if (isset($order['order_status']) && $order['order_status'] == 2) {echo 'selected';}?>>Připravená</option>
                        <option value="3" <?php if (isset($order['order_status']) && $order['order_status'] == 3) {echo 'selected';}?>>Vyexpedovaná</option>
                        <option value="4" <?php if (isset($order['order_status']) && $order['order_status'] == 4) {echo 'selected';}?>>Stornovaná</option>
                    </select>
                </div>
            </div>


            <div class="form-group" style="margin-bottom: 24px;">
                <label class="col-sm-6 control-label" for="eh" style="padding-top: 7px; text-align: left;">Informovat zákazníka o změně stavu</label>
                <div class="col-sm-6">
                    <div class="radiodegreeswitch rad1 make-switch switch-small" style="float: left; margin-right:11px; margin-top: 2px;" data-on-label="<i class='entypo-mail'></i>" data-off-label="<i class='entypo-cancel'></i>">
                        <input class="radiodegree" name="send_mail" id="eh" value="yes" type="checkbox"/>
                    </div>
                </div>
            </div>


            <div class="form-group" id="enable_custom_hidden" style="display: none;">
                <label class="col-sm-4 control-label" for="enable_custom" style="padding-top: 7px;">Vlastní úvodní text emailu</label>
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

    <div <?php if(!$forceRegenerate && $hasInvoice){ echo 'style="pointer-events: none; opacity: 0.5;"'; } ?>>
        <div class="panel panel-primary" data-collapsed="0">
            <div class="panel-heading">
                <div class="panel-title">
                    <strong style="font-weight: 600;">Fakturační údaje</strong>
                </div>
            </div>

            <div class="panel-body">

                <?php billing_address($address); ?>

                <div class="form-group">
                    <label for="field-7" class="col-sm-3 control-label">Doplňující informace</label>
                    <div class="col-sm-9">
                        <textarea name="customer_note" class="form-control autogrow" id="field-7"><?= $order['customer_note'] ?></textarea>
                    </div>
                </div>

            </div>
        </div>

        <?php shipping_address($address); ?>
    </div>

</div>

        <div class="col-md-7">

			<div class="panel panel-primary" data-collapsed="0" <?php if(($forceRegenerate && $order['payment_method'] == 'cash') || ($hasInvoice && !$forceRegenerate)){ echo 'style="pointer-events: none; opacity: 0.5;"'; } ?>>

				<div class="panel-heading">
					<div class="panel-title">
						<strong style="font-weight: 600;">Položky</strong>
					</div>
				</div>
                <div class="panel-body">

                    <?php shop_accessories('orders_products_bridge', 'order_id', $order['id'], $order['location_id']);?>

                    <script type="text/javascript">
                        $(document).ready(function () {

                            $('.original_currency').on('input', function (e) {

                                let rate = $('.final_currency').data('rate');
                                let exchanged = $(this).val() / rate;

                                $('.final_currency').val(exchanged.toFixed(2));

                            });

                        });
                    </script>
                    <div class="form-group calculator" <?php if($order['order_currency'] == 'CZK'){ ?>style="display: none;"<?php } ?>>
                        <hr>
                        <label for="field-2" class="col-sm-3 control-label">Kalkulátor měn</label>
                        <div class="form-label-group">
                            <div class="col-sm-3 has-metric">
                                <input type="text" class="form-control text-center original_currency" name="original_currency" value="" placeholder="CZK" style="padding: 0; height: 38px;">
                                <span class="input-group-addon">Kč</span>
                            </div>
                            <div class="col-sm-1">
                                <i class="fas fa-exchange-alt" style="padding: 10px 14px; font-size: 16px; color: #0d7eff"></i>
                            </div>
                            <div class="col-sm-3 has-metric">
                                <input type="text" class="form-control text-center final_currency" name="final_currency" value="" placeholder="<?php if($order['order_currency'] != 'CZK'){ echo $order['order_currency']; } ?>" data-rate="<?= $order['exchange_rate'] ?>" style="padding: 0; height: 38px;">
                                <span class="input-group-addon final_currency_shortcut"><?php if($order['order_currency'] != 'CZK'){ echo $order['order_currency']; } ?></span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="panel panel-primary" data-collapsed="0" <?php if(($forceRegenerate && $order['payment_method'] == 'cash') || ($hasInvoice && !$forceRegenerate)){ echo 'style="pointer-events: none; opacity: 0.5;"'; } ?>>

				<div class="panel-heading">
					<div class="panel-title">
						<strong style="font-weight: 600;">Platební podmínky</strong>
					</div>
				</div>

                <div class="panel-body">

                    <div class="form-group">
                        <label for="field-2" class="col-sm-3 control-label">Měna</label>
                        <div class="col-sm-9">

                            <div class="radio" style="float: left;">
                                <label>
                                    <input class="currency" type="radio" id="currency_czk" name="currency"
                                           data-value="<?php if($order['order_currency'] == 'CZK'){
                                               echo '1';
                                           }elseif($order['order_currency'] == 'EUR'){
                                               echo number_format((float) 1/$kurz["EUR"], 3, '.', ''); }elseif($order['order_currency'] == 'EUR'){
                                               echo number_format((float) 1/$kurz["USD"], 3, '.', ''); } ?>"
                                           data-ext="Kč" value="CZK" <?php if(!isset($order['order_currency']) || $order['order_currency'] == 'CZK'){ echo 'checked'; }?>>CZK
                                </label>
                                <input style="display: none;" name="CZK_rate" value="<?= $kurz["CZK"] ?>">
                            </div>
                            <div class="radio" style="float: left; margin-left: 30px;">
                                <label>
                                    <input class="currency" type="radio" id="currency_eur" name="currency" data-value="<?= $kurz["EUR"] ?>" data-ext="€" value="EUR" <?php if(isset($order['order_currency']) && $order['order_currency'] == 'EUR'){ echo 'checked'; }?>>EUR
                                </label>
                                <input style="display: none;" name="EUR_rate" value="<?= $kurz["EUR"] ?>">

                            </div>
                            <div class="radio" style="float: left; margin-left: 30px;">
                                <label>
                                    <input class="currency" type="radio" id="currency_usd" name="currency" data-value="<?= $kurz["USD"] ?>" data-ext="$" value="USD" <?php if(isset($order['order_currency']) && $order['order_currency'] == 'USD'){ echo 'checked'; }?>>USD
                                </label>
                                <input style="display: none;" name="USD_rate" value="<?= $kurz["USD"] ?>">
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label for="field-2" class="col-sm-3 control-label">Aktuální kurz dle ČNB</label>
                        <div class="col-sm-8">
                            <h5>
                                <strong><?= $kurz["EUR"] ?></strong> CZK/EUR&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <strong><?= $kurz["USD"] ?></strong> CZK/USD</h5>
                        </div>
                    </div>
                    <hr>

                <?php

                    $delivery_country = 'CZ';
                    if(empty($address['shipping_country'])){

                        $delivery_country = $address['billing_country'];

                    }elseif(!empty($address['shipping_country'])){

                        $delivery_country = $address['shipping_country'];

                    }

                    if($delivery_country != 'CZ' && $delivery_country != 'SK'){ $delivery_country = 'EU'; }

                    ?>

            <div class="form-group">
                <label class="col-sm-3 control-label">DPH %</label>
                <div class="col-sm-9">
                    <div class="radio" style="width: 100px; float: left;">
                        <label>
                            <input type="radio" name="vat" value="21" <?php if (isset($order['vat']) && $order['vat'] == 21) {echo 'checked';}?>>21%
                        </label>
                    </div>
                    <div class="radio" style="width: 100px;float: left;">
                        <label>
                            <input type="radio" name="vat" value="15" <?php if (isset($order['vat']) && $order['vat'] == 15) {echo 'checked';}?>>15%
                        </label>
                    </div>
                    <div class="radio" style="width: 100px;float: left;">
                        <label>
                            <input type="radio" name="vat" value="12" <?php if (isset($order['vat']) && $order['vat'] == 12) {echo 'checked';}?>>12%
                        </label>
                    </div>
                    <div class="radio" style="width: 100px;float: left;">
                        <label>
                            <input type="radio" name="vat" value="10" <?php if (isset($order['vat']) && $order['vat'] == 10) {echo 'checked';}?>>10%
                        </label>
                    </div>
                    <div class="radio" style="width: 100px;float: left;">
                        <label>
                            <input type="radio" name="vat" value="0" <?php if (isset($order['vat']) && $order['vat'] == 0) {echo 'checked';}?>>0%

                        </label>
                    </div>
                </div>
            </div>
            <hr>
            <div class="form-group" <?php if($forceRegenerate || $hasInvoice){ echo 'style="pointer-events: none; opacity: 0.5;"'; } ?>">
                <?php $payment_methods_query = $mysqli->query("SELECT * FROM shops_payment_methods ORDER BY name"); ?>
                <label class="col-sm-4 control-label" style="text-align: center;">Způsob úhrady</label>
                <div class="col-sm-8">
                    <select id="payment" name="payment" class="selectboxit">
                        <?php while ($payment_method = mysqli_fetch_array($payment_methods_query)) { ?><option value="<?= $payment_method['link_name'] ?>" <?php if (isset($order['payment_method']) && $order['payment_method'] == $payment_method['link_name']) {echo 'selected';}?>><?= ucfirst($payment_method['pay_text']) ?></option><?php } ?>
                    </select>
                </div>
            </div>

        </div>

			</div>

    <div class="panel panel-primary" data-collapsed="0">

        <div class="panel-heading">
            <div class="panel-title">
                <strong style="font-weight: 600;">Doručovací údaje - pro zvolenou zemi (<span class="delivery_label"><?= $delivery_country ?></span>)</strong>
            </div>

        </div>

        <div class="panel-body">

            <div class="form-group">

                <div class="col-sm-6" style="padding: 0;">
                    <div class="col-sm-12">
                        <div class="delivery delivery_CZ" <?php if($delivery_country != 'CZ'){ echo 'style="display: none;"'; } ?>>
                            <select id="delivery_cz" name="delivery_CZ" class="selectboxit delivery_select">
                                <?php
                                $delivery_methods_query = $mysqli->query("SELECT * FROM shops_delivery_methods WHERE country = 'CZ' OR shop_method_id = 'local_pickup' ORDER BY CASE WHEN shop_method_id like 'local_pickup' THEN 0
        WHEN shop_method_id like 'ceske_sluzby%' THEN 1
        WHEN shop_method_id like 'flat_rate' THEN 2
        WHEN shop_method_id like 'free_shipping' THEN 3
                ELSE 4 END, name");

                                while ($delivery_method = mysqli_fetch_array($delivery_methods_query)) {

                                    ?><option value="<?= $delivery_method['link_name'] ?>" <?php if (isset($order['order_shipping_method']) && $order['order_shipping_method'] == $delivery_method['link_name']) {echo 'selected';
                                        $current_delivery = $delivery_method['price'];}?>><?= $delivery_method['shop_title'] ?></option><?php

                                }

                                ?>
                            </select>
                        </div>

                        <div class="delivery delivery_SK" <?php if($delivery_country != 'SK'){ echo 'style="display: none;"'; } ?>>

                            <select id="delivery_sk" name="delivery_SK" class="selectboxit delivery_select">
                                <?php

                                $delivery_methods_query = $mysqli->query("SELECT * FROM shops_delivery_methods WHERE country = 'SK' OR shop_method_id = 'local_pickup' ORDER BY CASE WHEN shop_method_id like 'local_pickup' THEN 0
        WHEN shop_method_id like 'ceske_sluzby%' THEN 1
        WHEN shop_method_id like 'flat_rate' THEN 2
        WHEN shop_method_id like 'free_shipping' THEN 3
                ELSE 4 END, name")or die($mysqli->error);

                                while ($delivery_method = mysqli_fetch_array($delivery_methods_query)) {

                                    ?><option value="<?= $delivery_method['link_name'] ?>" <?php if (isset($order['order_shipping_method']) && $order['order_shipping_method'] == $delivery_method['link_name']) {echo 'selected';
                                        $current_delivery = $delivery_method['price'];}?>><?= $delivery_method['shop_title'] ?></option><?php

                                }

                                ?>
                            </select>
                        </div>

                        <div class="delivery delivery_EU" <?php if($delivery_country != 'EU'){ echo 'style="display: none;"'; } ?>>

                            <select id="delivery_eu" name="delivery_EU" class="selectboxit delivery_select">
                                <?php

                                $delivery_methods_query = $mysqli->query("SELECT * FROM shops_delivery_methods WHERE country = 'EU' OR shop_method_id = 'local_pickup' ORDER BY CASE WHEN shop_method_id like 'local_pickup' THEN 0
        WHEN shop_method_id like 'ceske_sluzby%' THEN 1
        WHEN shop_method_id like 'flat_rate' THEN 2
        WHEN shop_method_id like 'free_shipping' THEN 3
                ELSE 4 END, name");

                                while ($delivery_method = mysqli_fetch_array($delivery_methods_query)) {

                                    ?><option value="<?= $delivery_method['link_name'] ?>" <?php if (isset($order['order_shipping_method']) && $order['order_shipping_method'] == $delivery_method['link_name']) {echo 'selected';
                                        $current_delivery = $delivery_method['price'];}?>><?= $delivery_method['shop_title'] ?></option><?php

                                }

                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6" <?php if(($forceRegenerate && $order['payment_method'] == 'cash') || ($hasInvoice && !$forceRegenerate)){ echo 'style="pointer-events: none; opacity: 0.5;"'; } ?>>
                        <label class="col-sm-4 control-label" for="delivery_special_price" style="padding-top: 14px; padding-right: 0;padding-left: 10px;">Cena doručení</label>
                        <div class="col-sm-8">
                            <input type="number"  style="height: 40px;" class="form-control price-control" id="delivery_special_price" name="delivery_special_price" value="<?= $order['delivery_price'] ?>" data-default="<?= $order['delivery_price'] ?>">
                        </div>
                </div>
            </div>


            <?php

            /*
            if($order['order_site'] == 'spamall'){

                $shopId = 12424;
                $apiKey = 'DtDerPdgSe4pAZmBGfbA0Iq5S';

            }elseif($order['order_site'] == 'spahouse'){

                $shopId = 14685;
                $apiKey = 'UbxsFKiKSOSRb6026X4LX2r4E';

// WT admin + saunahouse
            }else{

                $shopId = 12880;
                $apiKey = 'ndH3bBAjtQUNuAYihHf11maIK';

            }

            $endpoint = \UlozenkaLib\APIv3\Enum\Endpoint::PRODUCTION;

            $api = new \UlozenkaLib\APIv3\Api($endpoint);

            $transportServiceId = \UlozenkaLib\APIv3\Enum\TransportService::ULOZENKA;

            // get the destination branches for transport service Ulozenka with respect to settings of the shop with id $shopId
            $getTransportServiceBranchesResponse = $api->getTransportServiceBranches($transportServiceId, $shopId, true);

            // process the response
            $branches = array();
            if ($getTransportServiceBranchesResponse->isSuccess()) {

                foreach ($getTransportServiceBranchesResponse->getDestinationBranches() as $branch) {


                    $branches[] = array( "id" => $branch->getId(), "name" =>$branch->getName());
//                        echo $branch->getId() . ' ' . $branch->getName() . PHP_EOL;
                }
            } else {
                $errors = $getTransportServiceBranchesResponse->getErrors();
                foreach ($errors as $error) {
                    echo $error->getCode() . ' ' . $error->getDescription() . PHP_EOL;
                }
            }


            function sortByOrder($a, $b) {
                return $a['name'] > $b['name'];
            }

            usort($branches, 'sortByOrder');

            ?>

            <div class="form-group ulozenka" style="margin-top: 10px; margin-bottom: 24px; <?php if (strpos($order['order_shipping_method'], 'ulozenka') === false) { echo 'display: none;'; } ?>">
                <label class="col-sm-3 control-label" for="shipping_location_ul" style="padding-top: 14px;">Uloženka pobočky</label>
                <div class="col-sm-8">
                    <select id="shipping_location_ul" name="shipping_location_ul" class="selectboxit">
                        <option value="">Vyberte výdejní místo</option>
                        <?php
                        // process the response
                        foreach ($branches as $branch) {

                            ?>
                            <option value="<?= $branch['name'] ?>" <?php if (isset($order['shipping_location']) && $order['shipping_location'] == $branch['name']) { echo 'selected'; } ?>><?= $branch['name'] ?>
                            </option>
                            <?php

                        }
                        ?>
                    </select>
                </div>
            </div>



            <?php

            $transportServiceId = \UlozenkaLib\APIv3\Enum\TransportService::CPOST_BALIK_NA_POSTU;

            // get the destination branches for transport service Ulozenka with respect to settings of the shop with id $shopId
            $getTransportServiceBranchesResponse = $api->getTransportServiceBranches($transportServiceId, $shopId, true);

            // process the response
            $branches = array();
            if ($getTransportServiceBranchesResponse->isSuccess()) {

                foreach ($getTransportServiceBranchesResponse->getDestinationBranches() as $branch) {

                    $branches[] = array( "id" => $branch->getId(), "name" =>$branch->getName());
//                        echo $branch->getId() . ' ' . $branch->getName() . PHP_EOL;
                }
            } else {
                $errors = $getTransportServiceBranchesResponse->getErrors();
                foreach ($errors as $error) {
                    echo $error->getCode() . ' ' . $error->getDescription() . PHP_EOL;
                }
            }

            usort($branches, 'sortByOrder');

            ?>


            <div class="form-group ceska_posta" style="margin-top: 10px; margin-bottom: 24px; <?php if (strpos($order['order_shipping_method'], 'balik_na_postu') === false) { echo 'display: none;'; } ?>">
                <label class="col-sm-3 control-label" for="shipping_location_cp" style="padding-top: 14px;">Balík na poštu</label>
                <div class="col-sm-8">
                    <select id="shipping_location_cp" name="shipping_location_cp" class="selectboxit">
                        <option value="">Vyberte výdejní místo</option>
                        <?php
                        // process the response
                        foreach ($branches as $branch) {

                            ?>
                            <option value="<?= $branch['name'] ?>" <?php if (isset($order['shipping_location']) && $order['shipping_location'] == $branch['name']) { echo 'selected'; } ?>><?= $branch['name'] ?>
                            </option>
                            <?php

                        }
                        ?>
                    </select>
                </div>
            </div>



            <?php

            $apiKey = 'd977ce48de5a390f';
            $apiPassword = 'd977ce48de5a390f08a4e7ad52af5181';

            $api = new Zasilkovna\ApiRest($apiPassword, $apiKey);

            $branch = new Zasilkovna\Branch($apiKey, new Zasilkovna\Model\BranchStorageSqLite());
            //$branch->getBranchList();
                        */


            ?>
            <script>
                var packetaApiKey = 'd977ce48de5a390f';
                /*
                    This function will receive either a pickup point object, or null if the user
                    did not select anything, e.g. if they used the close icon in top-right corner
                    of the widget, or if they pressed the escape key.
                */
                function showSelectedPickupPoint(point)
                {
                    var spanElement = document.getElementById('packeta-point-info');
                    var idElement = document.getElementById('packeta-point-id');
                    var nameStreetElement = document.getElementById('packeta-point-nameStreet');
                    if(point) {
                        var recursiveToString = function(o) {
                            return Object.keys(o).map(
                                function(k) {
                                    if(o[k] === null) {
                                        return k + " = null";
                                    }

                                    return k + " = " + (typeof(o[k]) == "object"
                                            ? "<ul><li>" + recursiveToString(o[k]) + "</li></ul>"
                                            : o[k].toString().replace(/&/g, '&amp;').replace(/</g, '&lt;')
                                    );
                                }
                            ).join("</li><li>");
                        };

                        spanElement.innerText =  point.nameStreet;

                        // spanElement.innerText =
                        //     "Address: " + point.name + "\n" + point.zip + " " + point.city + "\n\n"
                        //     + "All available fields:\n";

                        // spanElement.innerHTML +=
                        //     "<strong>" + recursiveToString(point) + "</strong>";

                        idElement.value = point.id;
                        nameStreetElement.value = point.nameStreet;
                    }
                    else {
                        spanElement.innerText = "";
                        idElement.value = "";
                        nameStreetElement.value = "";

                    }
                };
            </script>

            <div class="form-group zasilkovna" style="margin-top: 10px; margin-bottom: 24px; <?php if (strpos($order['order_shipping_method'], 'zasilkovna') === false) { echo 'display: none;'; } ?>">
                <label class="col-sm-3 control-label" style="padding-top: 14px;"> <input type="button" onclick="Packeta.Widget.pick(packetaApiKey, showSelectedPickupPoint)" value="Výběr pobočky..." class="btn btn-info btn-md"></label>
                <div class="col-sm-8">

                    <p style="padding: 20px 0 0;">Vybraná pobočka:
                        <input type="hidden" name="shipping_location_id_zasilkovna" id="packeta-point-id" <?php
                        if (isset($order['shipping_location_id']) && ($order['order_shipping_method'] == 'zasilkovna_cr' || $order['order_shipping_method'] == 'zasilkovna_sk')) { echo 'value="'.$order['shipping_location'].'"'; }
                        ?>>
                        <input type="hidden" name="shipping_location_zasilkovna" id="packeta-point-nameStreet" <?php
                        if (isset($order['shipping_location']) && ($order['order_shipping_method'] == 'zasilkovna_cr' || $order['order_shipping_method'] == 'zasilkovna_sk')) { echo 'value="'.$order['shipping_location'].'"'; }
                        ?>>
                        <span id="packeta-point-info" style="font-weight: bold;"><?php
                            if (isset($order['shipping_location']) && ($order['order_shipping_method'] == 'zasilkovna_cr' || $order['order_shipping_method'] == 'zasilkovna_sk')) { echo $order['shipping_location']; }else{ echo 'žádná'; }
                            ?>
                        </span>
                    </p>

                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label" for="tracking_code" style="padding-top: 14px;">Sledovací číslo</label>
                <div class="col-sm-4">
                        <input type="text" style="height: 40px;" name="order_tracking_number" class="form-control" id="tracking_code" placeholder="Sledovací číslo" value="<?= $order['order_tracking_number'] ?>">
                </div>

                <label class="col-sm-2 control-label" for="weight" style="padding-top: 14px;">Váha</label>
                <div class="col-sm-4">
                        <input type="number" style="height: 40px;" name="weight" class="form-control" id="weight" placeholder="Váha" value="<?= $order['weight'] ?>">
                </div>
            </div>
        </div>
    </div>
</div>


	</div>

	<center>
	<div class="form-group default-padding button-demo">
		<button type="submit" style="margin-bottom: 24px; margin-right: 26px; font-size: 20px; padding: 20px 50px 20px 140px; margin-top: 20px;" data-color="red" data-style="zoom-in" class="ladda-button btn btn-primary btn-icon icon-left btn-lg"><i class="entypo-pencil" style="line-height: 48px;font-size: 40px; padding: 10px 20px;"></i> <span class="ladda-label">Upravit objednávku</span></button>
	</div></center>

</form>

<footer class="main">
	&copy; <?= date("Y") ?> <span style=" float:right;"><?php
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round(($finish - $start), 4);

    echo 'PHP '.PHP_VERSION.' | Page generated in ' . $total_time . ' seconds.';?></span>
</footer>
    </div>
</div>

	<script>

        $(document).ready(function(){

            $("#order_form").on("submit", function(){
              var form = $( "#order_form" );
                         var l = Ladda.create( document.querySelector( '#order_form .button-demo button' ) );
                if(form.valid()){

                  l.start();
                }
               });


         });


    </script>
    <script src="https://widget.packeta.com/v6/www/js/library.js"></script>

<?php include VIEW . '/default/footer.php'; ?>



<?php

} else {

    include INCLUDES . "/404.php";

} ?>
