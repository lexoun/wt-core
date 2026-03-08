<?php
use Salamek\Zasilkovna as Zasilkovna;

include_once INCLUDES . "/accessories-functions.php";


// check if not in db
$check_query = $mysqli->query("SELECT id FROM orders WHERE id = '" . $order['number'] . "' OR order_key = '" . $order['order_key'] . "'") or die($mysqli->error);

if(mysqli_num_rows($check_query)){ die('objednávka již byla naimportována.'); }

if (!empty($order) && !empty($order['order_key']) && !empty($site)) {

    $delivery_price = $order['shipping_total'];

    $billing = $order['billing'];
    $shipping = $order['shipping'];

    $shipping_location = '';
    foreach ($order['shipping_lines'] as $shipping_line) {

        $shipping['method_id'] = $shipping_line['method_id'];
        $shipping['instance_id'] = $shipping_line['instance_id'];
        $shipping['title'] = $shipping_line['method_title'];

        if($shipping_line['method_id'] === 'ceske_sluzby_ulozenka'){

            $key = array_search('ulozenka_pobocka', array_column($shipping_line['meta_data'], 'key'));
            $shipping_location = $shipping_line['meta_data'][$key]['value'];

        }elseif($shipping_line['method_id'] === 'ceske_sluzby_zasilkovna'){

            $key = array_search('ceske_sluzby_zasilkovna_pobocka_nazev', array_column($shipping_line['meta_data'], 'key'));
            $shipping_location = $shipping_line['meta_data'][$key]['value'];

            $apiKey = 'd977ce48de5a390f';
            $apiPassword = 'd977ce48de5a390f08a4e7ad52af5181';

            $api = new Zasilkovna\ApiRest($apiPassword, $apiKey);
            $branch = new Zasilkovna\Branch($apiKey, new Zasilkovna\Model\BranchStorageSqLite());

            $shipping_location_id = 0;
            // process the response
            foreach ($branch->getBranchList() as $singleBranch) {
                if($singleBranch['nameStreet'] == $shipping_location){
                    $shipping_location_id = $singleBranch['id'];
                    break;
                }
            }
        }
    }

    $shops_query = $mysqli->query("SELECT shipping_methods FROM shops WHERE slug = '".$site."'") or die($mysqli->error);
    $shop = mysqli_fetch_array($shops_query);

    $shippingMethods = json_decode($shop['shipping_methods'], true);

    $finalData = $shippingMethods[$billing['country']];

    foreach($finalData as $single) {
        if($single['method_id'] == $shipping['method_id'] && $single['instance_id'] == $shipping['instance_id']){

            $getShippingMethod = $mysqli->query("SELECT * FROM shops_delivery_methods WHERE id = '".$single['id']."'");
            $shippingMethod = mysqli_fetch_assoc($getShippingMethod);
            break;
        }
    }

    if (substr($order['payment_method'], 0, 14) === "agmobinderbank") {

        $method_id = "agmobinderbank";

    } elseif ($order['payment_method'] == 'cod' && $shippingMethod['shop_method_id'] == 'local_pickup') {

        $method_id = 'cash';

    } else {

        $method_id = $order['payment_method'];

    }

    $phone = str_replace(' ', '', $billing['phone']);
    $billing_phone = substr($phone, -9);

    $client_id = 0;
    if(isset($site) && $site == 'saunahouse'){ $customer_type = '0'; }elseif(isset($site) && $site == 'spahouse') { $customer_type = '1'; }

    if (isset($customer_type) && $order['customer_id'] != 0 && isset($order['customer_id'])) {

        $client_lookup_query = $mysqli->query("SELECT id FROM demands WHERE woocommerce_id = '" . $order['customer_id'] . "' AND woocommerce_id != 0 AND woocommerce_id != 999999999 AND customer = '$customer_type'") or die($mysqli->error);
        if (mysqli_num_rows($client_lookup_query) == 1) {

            $client_lookup = mysqli_fetch_array($client_lookup_query);
            $client_id = $client_lookup['id'];

        }
    }

    $coeficient = vat_coeficient('21');

    $overallcena = $order['total'];

    $price = get_price($overallcena, $coeficient);

    $ico_key = array_search('_billing_ic', array_column($order['meta_data'], 'key'));
    $dic_key = array_search('_billing_dic', array_column($order['meta_data'], 'key'));

    $ico = $ico_key ? $order['meta_data'][$ico_key]['value'] : '';
    $dic = $dic_key ? $order['meta_data'][$dic_key]['value'] : '';

    if ((isset($link['location_id']) && $link['location_id'] == 0) || !isset($link['location_id']) || $link['location_id'] == '') {

        $link['location_id'] = 7;

    }

    if(!empty($billing['address_2'])){ $billing['address_1'] .= ' '.$billing['address_2']; }

    // billing address
    $mysqli->query("INSERT INTO addresses_billing
        (billing_company, 
         billing_ico, 
         billing_dic, 
         billing_name, 
         billing_surname, 
         billing_street, 
         billing_city, 
         billing_zipcode, 
         billing_country, 
         billing_phone, 
         billing_email) 
        VALUES 
       ('" . $billing['company'] . "', 
       '" . $ico . "', 
       '" . $dic . "', 
       '" . $billing['first_name'] . "', 
       '" . $billing['last_name'] . "', 
       '" . $billing['address_1'] . "', 
       '" . $billing['city'] . "', 
       '" . $billing['postcode'] . "', 
       '" . $billing['country'] . "', 
       '" . $billing_phone . "', 
       '" . $billing['email'] . "')") or die($mysqli->error);

    $billing_id = $mysqli->insert_id;

    $shipping_id = 0;
    if ($shipping['first_name'] != $billing['first_name'] ||
        $shipping['last_name'] != $billing['last_name'] ||
        $shipping['company'] != $billing['company'] ||
        $shipping['address_1'] != $billing['address_1'] ||
        $shipping['city'] != $billing['city']) {

        if(!empty($shipping['address_2'])){ $shipping['address_1'] .= ' '.$shipping['address_2']; }

        // shipping address
        $mysqli->query("INSERT INTO addresses_shipping (
                                shipping_company, 
                                shipping_name, 
                                shipping_surname, 
                                shipping_street, 
                                shipping_city, 
                                shipping_zipcode, 
                                shipping_country) 
                        VALUES ('" . $shipping['company'] . "', 
                                '" . $shipping['first_name'] . "', 
                                '" . $shipping['last_name'] . "', 
                                '" . $shipping['address_1'] . "', 
                                '" . $shipping['city'] . "', 
                                '" . $shipping['postcode'] . "', 
                                '" . $shipping['country'] . "'
                                )") or die($mysqli->error);

        $shipping_id = $mysqli->insert_id;

    }


    if ($order['customer_id'] != 0) { $discount = '10'; }else{ $discount = '0'; }

    // todo check if is client

    if($method_id == 'agmobindercardall' || $method_id == 'agmobinderbank'){
        $reference_number = $order['id'];
    }else{
        $reference_number = $order['number'];
    }


    /* TODO ověřit zaokrouhlování na straně e-shopu - jak se importuje? */

    // if rounding
    $price['rounded'] = 0;
    if($order['payment_method'] == 'cod'){

        $price['single'] = round($price['single']);
        $price['rounded'] = number_format($price['single'] - $overallcena, 2, '.', '');

    }


    // order insert
    $mysqli->query("INSERT INTO orders (
                    id, 
                    reference_number, 
                    billing_id, 
                    shipping_id, 
                    client_id, 
                    total_without_vat, 
                    total_vat, 
                    vat, 
                    payment_method, 
                    order_shipping_method, 
                    order_site, 
                    order_key, 
                    order_date, 
                    delivery_price, 
                    order_status, 
                    order_currency, 
                    total, 
                    customer_id, 
                    customer_note, 
                    customer_email, 
                    customer_phone, 
                    shipping_location, 
                    shipping_location_id, 
                    location_id, 
                    discount, 
                    import_log, 
                    weight, 
                    total_rounded
         ) VALUES ('" . $order['number'] . "', 
           '" . $reference_number . "', 
           '$billing_id', 
           '$shipping_id', 
           '$client_id','".$price['without_vat']."','".$price['vat']."','21','$method_id', 
           '".$shippingMethod['link_name']."',
           '" . $site . "', 
           '" . $order['order_key'] . "', 
           now(), 
           '$delivery_price', 
           '0', 
           '" . $order['currency'] . "', 
           '" . $price['single'] . "', 
           '" . $order['customer_id'] . "', 
           '" . $order['customer_note'] . "', 
           '" . $billing['email'] . "', 
           '$billing_phone', 
           '" . $shipping_location . "', 
           '" . $shipping_location_id . "', 
           '" . $link['location_id'] . "', 
           '" . $discount . "', 
           '" . $body . "', 
           '4.00', 
           '" . $price['rounded'] . "'
       )") or die($mysqli->error);


    $id = $order['number'];

    foreach ($order['line_items'] as $item) {

        $product = '';
        $variation_values = '';
        $product_name = '';

        if ($item['variation_id'] == 0) {

            $product_lookup = $mysqli->query("SELECT 
                    p.id, productname, purchase_price, s.instock, p.code as sku, p.price 
                FROM products p 
                    LEFT JOIN products_stocks s ON s.product_id = p.id AND s.location_id = '" . $link['location_id'] . "' 
                WHERE p.ean = '" . $item['sku'] . "'") or die($mysqli->error);

            if (mysqli_num_rows($product_lookup) > 0) {

                $product = mysqli_fetch_assoc($product_lookup);

                // set variation to zero for future use
                $product['variation_id'] = 0;

            }

        } else {

            $product_lookup = $mysqli->query("SELECT 
                p.id as id, v.id as variation_id, s.instock, p.productname, v.purchase_price, v.sku as sku, v.price 
            FROM (products_variations v, products p) 
                LEFT JOIN products_stocks s ON s.product_id = p.id AND s.variation_id = v.id AND s.location_id = '" . $link['location_id'] . "' 
            WHERE v.ean = '" . $item['sku'] . "' AND v.product_id = p.id GROUP BY s.location_id") or die($mysqli->error);

            if (mysqli_num_rows($product_lookup) > 0) {

                $product = mysqli_fetch_assoc($product_lookup);

                $variation_query = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $product['variation_id'] . "'");
                while ($variation = mysqli_fetch_assoc($variation_query)) {

                    if ($variation_values == "") { $variation_values = $variation['name'] . ': ' . $variation['value'];
                    } else { $variation_values .= ', ' . $variation['name'] . ': ' . $variation['value']; }

                }

            }

        }


        if (!empty($product['id'])){

            // remove reserved from stock
            $quantity = $item['quantity'];

            if ($quantity > $product['instock']) {

                $reserve = $product['instock'];
                $instock = 0;

            } else {

                $reserve = $quantity;
                $instock = $product['instock'] - $quantity;

            }

            $mysqli->query("UPDATE products_stocks SET instock = instock - $reserve WHERE product_id = '" . $product['id'] . "' AND variation_id = '" . $product['variation_id'] . "' and location_id = '" . $link['location_id'] . "'") or die($mysqli->error);


            $calculate_discount = 0;
            $discount_net = 0;

            if (!empty($product['price']) && !empty($item['price']) && $item['price'] != $product['price'] && $order['currency'] == 'CZK') {

                $calculate_discount = 100 - (($item['price'] / $product['price']) * 100);
                $discount_net = number_format($product['price'] - $item['price'], 2, '.', '');

            }

            // log into product stock history
            $mysqli->query("INSERT INTO history_products (product_id, variation_id, datetime, type, target_id, value, final_stock) VALUES ('" . $product['id'] . "', '" . $product['variation_id'] . "', now(), 'order_reserve', '$id', '$reserve', '$instock')") or die($mysqli->error);

        }else{

            // pro případ, že bude položka smazaná ale na webu zůstala (nemělo by se stát)
            //  nastavit hodnoty pro $product z posladných $item
            $product['id'] = 0;
            $product['productname'] = $item['name'];
            $product['variation_id'] = 0;
            $variation_values = '';
            $product['price'] = $item['price'];
            $reserve = 0;
            $product['purchase_price'] = $product['price'] * 0.8;
            $calculate_discount = 0;
            $discount_net = 0;

        }

        $product_name = $mysqli->real_escape_string($product['productname']);
        $variation_values = $mysqli->real_escape_string($variation_values);

        // bridge add
        $mysqli->query("INSERT INTO orders_products_bridge (
                    aggregate_id,
                    aggregate_type,
                    product_id, 
                    product_site, 
                    product_name, 
                    variation_id, 
                    variation_values, 
                    quantity, 
                    price, 
                    original_price, 
                    reserved, 
                    purchase_price, 
                    location_id,
                    discount,
                    discount_net) 
                    VALUES (
                    '$id', 
                    'order', 
                    '" . $product['id'] . "', 
                    '" . $site . "',
                    '" . $product_name . "', 
                    '" . $product['variation_id'] . "', 
                    '" . $variation_values . "', 
                    '" . $item['quantity'] . "', 
                    '" . $product['price'] . "', 
                    '" . $product['price'] . "', 
                    '" . $reserve . "', 
                    '" . $product['purchase_price'] . "', 
                    '" . $link['location_id'] . "',
                    '" . $calculate_discount . "',
                    '" . $discount_net . "')") or die($mysqli->error);


        $new_filter = array(
            'filter' => array(
                'sku' => $item['sku'],
            ),
        );

        $data = [

            "managing_stock" => true,
            "stock_quantity" => $instock,

        ];


        // plan only stock sync on shops
        foreach(getProductSites($product['id']) as $productSite){

            if($productSite['site'] === $site){ continue; }
            saveProductEvent($product['id'], 'product', 'quantity', $productSite['site']);

        }

    // products loop end
    }

    include $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/order_status_emails.php";


    // POST to zásilkovna
    include $_SERVER['DOCUMENT_ROOT'] . "/admin/controllers/stores/consignments/zasilkovna.php";
    createPackage($id);


}