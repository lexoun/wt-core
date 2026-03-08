<?php
ignore_user_abort(true);

$start = microtime(true);

include_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";

include_once LIBRARIES . '/wootest/vendor/autoload.php';
use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;

$shops_query = $mysqli->query("SELECT *, p.id as id FROM shops s, products_sites p WHERE p.product_id = '" . $_REQUEST['id'] . "' AND p.site = s.slug") or die($mysqli->error);
while ($shop = mysqli_fetch_array($shops_query)) {

    $echoes = '';
    $echoes = $echoes . $shop['name'] . ' ' . $shop['site_id'] . '<br><br>';

    $woocommerce_connection = "";
    $woocommerce_connection = new Client(
        $shop['url'],
        $shop['secret_key'],
        $shop['secret_code'],
        [
            'wp_api' => true,
            'version' => 'wc/v2',
            'query_string_auth' => true,

        ]
    );

    $product_query = $mysqli->query("SELECT p.delivery_time, p.type, SUM(s.instock) as total_instock FROM products p LEFT JOIN products_stocks s ON s.product_id = p.id WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

    $product = mysqli_fetch_array($product_query);

    $instock = $product['total_instock'];

    if (isset($product['type']) && $product['type'] == 'variable') {

        $instock_vari_query = $mysqli->query("SELECT SUM(instock) as total_instock FROM products_stocks WHERE product_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);
        $instock_vari = mysqli_fetch_array($instock_vari_query);

        $instock = $instock_vari['total_instock'];

    }

    $delivery_query = $mysqli->query("SELECT m.delivery_time FROM products_manufacturers m, products_specifications s WHERE s.value = m.manufacturer AND s.name = 'Výrobce' AND s.product_id = '" . $_REQUEST['id'] . "'");

    $delivery = mysqli_fetch_array($delivery_query);

    if ($instock > 0) {

        $delivery = 0;

    } else {

        if (isset($delivery['delivery_time']) && $delivery['delivery_time'] != "") {

            $delivery = $delivery['delivery_time'];

        } else {

            $delivery = $product['delivery_time'];

        }

    }

    $data_woo = [
        "manage_stock" => true,
        "stock_quantity" => $instock,
        "in_stock" => true,
        "backorders" => "notify",
        "backorders_allowed" => true,
        "backordered" => false,
    ];

    $deli = ["key" => "ceske_sluzby_dodaci_doba", "value" => $delivery];

    if ($shop['url'] != "https://www.spahouse.cz") {

        $data_woo['meta_data'] = [$cross, $ups, $deli];

    }

    if ($product['availability'] != 3) {

        $data_woo['catalog_visibility'] = 'visible';

    } elseif (isset($product['availability']) && $product['availability'] == 3) {

        $data_woo['catalog_visibility'] = 'hidden';
        $data_woo['in_stock'] = 'false';
        $data_woo['backorders_allowed'] = "false";

    }

    if (isset($shop['site_id']) && $shop['site_id'] == 0) {

        //print_r($data_woo);

        try {

            $newproduct = $woocommerce_connection->post('products', $data_woo);

            if (isset($newproduct) && isset($newproduct['id']) && $newproduct['id'] != 0) {

                $update_id = $mysqli->query("UPDATE products_sites SET site_id = '" . $newproduct['id'] . "' WHERE id = '" . $shop['id'] . "' AND product_id = '" . $_REQUEST['id'] . "'");

                $echoes = $echoes . 'PRODUKT NOVĚ PŘIDÁN.<br><br>';

            }

        } catch (HttpClientException $e) {

            //print_r($e->getResponse());

            if ($e->getCode() == 400) {

                $response = $e->getResponse();

                $responsebody = $response->getBody();

                $responsenewbody = json_decode($responsebody, true);

                //print_r($responsenewbody);

                $product_id = $responsenewbody['data'][resource_id];

                //echo $product_id;

                $updateproduct = $woocommerce_connection->put('products/' . $product_id, $data_woo);

                $update_id = $mysqli->query("UPDATE products_sites SET site_id = '$product_id' WHERE id = '" . $shop['id'] . "'");

                $echoes = $echoes . 'PRODUKT BYL NA WEBU ALE NEMĚL PŘIŘAZENÉ PRODUCT_ID. PŘIŘAZENO PRODUCT ID A AKTUALIZOVÁN PRODUKT.<br><br>';

            }
        }

    } else {

        try {

            $updateproduct = $woocommerce_connection->put('products/' . $shop['site_id'], $data_woo);

        } catch (HttpClientException $e) {

            //print_r($e->getResponse());

        }

        $echoes = $echoes . 'PRODUKT AKTUALIZOVAN.<br><br>';

        //print_r($data_woo);

        //print_r($updateproduct);

    }

    if (isset($product['type']) && $product['type'] == 'variable') {

        $variations_array = array();
        $update_array = array();
        $delete_array = array();

        $parent_attributes = array();

        $options = array();

        $variation_eans = array();

        $all_vari = $woocommerce_connection->get('products/' . $shop['site_id'] . '/variations');

        $remove_ids = array();

        $actual_vari_ids = array();

        $get_arrays = array();

        $actual_arrays = array();

        foreach ($all_vari as $vari) {

            $varid = $vari['id'];

            $get_arrays[$varid] = $vari['sku'];

        }

        $update_variations = array();
        $variations_query = $mysqli->query("SELECT * FROM products_variations WHERE product_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

        $par_var_name = "";
        while ($variation = mysqli_fetch_array($variations_query)) {

            $instock_query = $mysqli->query("SELECT SUM(instock) as total_instock FROM products_stocks WHERE product_id = '" . $_REQUEST['id'] . "' AND variation_id = '" . $variation['id'] . "'") or die($mysqli->error);
            $instock = mysqli_fetch_array($instock_query);

            $price_query = $mysqli->query("SELECT site_id, price FROM products_variations_sites WHERE site = '" . $shop['slug'] . "' AND variation_id = '" . $variation['id'] . "'");
            $price = mysqli_fetch_array($price_query);

            $key = "lolek";

            $key = array_search($variation['ean'], $get_arrays);

            if ($key != $price['site_id']) {

                $echoes = $echoes . '<br><br><br><br>EAN JE V SHOPU, NESEDI ID V DB - ' . $key . '<br><br><br><br>';

                $update = $mysqli->query("UPDATE products_variations_sites SET site_id = '$key' WHERE  site = '" . $shop['slug'] . "' AND variation_id = '" . $variation['id'] . "'");

                $price['site_id'] = $key;

            }

            array_push($variation_eans, $variation['ean']);

            array_push($actual_vari_ids, $price['site_id']);

            $act_id = $price['site_id'];

            $actual_arrays[$act_id] = $variation['ean'];

            /* NEMÁ!! PŘIŘAZENÉ SITE ID */

            if (isset($price['site_id']) && $price['site_id'] == 0) {

                $current_variation = array(
                    "manage_stock" => true,
                    "stock_quantity" => $instock['total_instock'],
                    "in_stock" => true,
                    "backorders" => "notify",
                    "purchaseable" => true,
                    "backorders_allowed" => true,
                );

                array_push($variations_array, $current_variation);

                // print_r($current_variation);

                try {

                    $updateproduct = $woocommerce_connection->post('products/' . $shop['site_id'] . '/variations/', $current_variation);

                    //print_r($updateproduct);

                    if (isset($updateproduct) && isset($updateproduct['id']) && $updateproduct['id'] != 0) {

                        $update_id = $mysqli->query("UPDATE products_variations_sites SET site_id = '" . $updateproduct['id'] . "' WHERE site = '" . $shop['slug'] . "' AND variation_id = '" . $variation['id'] . "'");

                        $echoes = $echoes . 'VARIANTA NOVE PRIDANA.<br>';

                    }

                } catch (HttpClientException $e) {

                    if ($e->getCode() == 400) {

                        $response = $e->getResponse();

                        $responsebody = $response->getBody();

                        $responsenewbody = json_decode($responsebody, true);

                        //print_r($responsenewbody);

                        $product_id = $responsenewbody['data'][resource_id];

                        //echo $product_id;

                        $updateproduct = $woocommerce_connection->put('products/' . $shop['site_id'] . '/variations/' . $product_id, $current_variation);

                        $update_id = $mysqli->query("UPDATE products_variations_sites SET site_id = '$product_id' WHERE site = '" . $shop['slug'] . "' AND variation_id = '" . $variation['id'] . "'");

                        $echoes = $echoes . 'VARIANTA BYLA NA WEBU ALE NEMĚL PŘIŘAZENÉ PRODUCT_ID. PŘIŘAZENO PRODUCT ID A AKTUALIZOVÁN PRODUKT.<br>';

                    }

                }

                /* MÁ!!! PŘIŘAZENÉ SITE ID */

            } else {

                $current_variation = array(

                    "id" => $price['site_id'],
                    "manage_stock" => true,
                    "stock_quantity" => $instock['total_instock'],
                    "in_stock" => true,
                    "backorders" => "notify",
                    "purchaseable" => true,
                    "backorders_allowed" => true,

                );

                array_push($update_variations, $current_variation);

                $echoes = $echoes . 'UPDATED CLASSIC <BR>';

            }

        }

        foreach ($all_vari as $vari) {

            if (!in_array($vari['id'], $actual_vari_ids)) {

                array_push($remove_ids, $vari['id']);

            }

        }

        $data = [

            'update' => $update_variations,
            'delete' => $remove_ids,

        ];

        $woocommerce_connection->post('products/' . $shop['site_id'] . '/variations/batch', $data);

    }

    echo $echoes . '<br>----<br>VARIANTY NA SHOPU<br>';
    print_r($get_arrays);
    echo '<br>----<br><br>----<br>VARIANTY SPRAVNE<br>';
    print_r($actual_arrays);
    echo '<br>----<br><br>----<br>VARIANTY KE SMAZANI<br>';
    print_r($remove_ids);
    echo '<br>----<br><br>-- KONEC SHOPU --<br><br>----<br><br><br><br><br><br><br><br><br>';

}
$time_elapsed_secs = microtime(true) - $start;
echo $time_elapsed_secs;
