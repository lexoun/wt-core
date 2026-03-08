<?php
ignore_user_abort(true);

$start = microtime(true);

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";

require LIBRARIES . '/wootest/vendor/autoload.php';
use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;

require LIBRARIES . '/woocommerce-api/lib/woocommerce-api.php';

if ($_REQUEST['id'] != "" && isset($_REQUEST['id'])) {

    $shops_query = $mysqli->query("SELECT *, p.id as id FROM shops s, products_sites p WHERE p.product_id = '" . $_REQUEST['id'] . "' AND p.site = s.slug") or die($mysqli->error);
    while ($shop = mysqli_fetch_array($shops_query)) {

        if ($shop['site_id'] != 0) {

            echo $shop['name'] . ' ' . $shop['site_id'] . '<br><br>';

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

            $product_query = $mysqli->query("SELECT *, SUM(s.instock) as total_instock FROM products p LEFT JOIN products_stocks s ON s.product_id = p.id WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

            $product = mysqli_fetch_array($product_query);

            $variations_array = array();

            $options = array();

            $instock = $product['total_instock'];

            if (isset($product['type']) && $product['type'] == 'variable') {

                $instock_vari_query = $mysqli->query("SELECT SUM(instock) as total_instock FROM products_stocks WHERE product_id = '" . $_REQUEST['id'] . "' AND variation_id != 0") or die($mysqli->error);
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

            $deli = ["key" => "ceske_sluzby_dodaci_doba",
                "value" => $delivery,
            ];

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

            $data_woo['sku'] = $product['ean'];

            try {

                $updateproduct = $woocommerce_connection->put('products/' . $shop['site_id'], $data_woo);

            } catch (HttpClientException $e) {

                //print_r($e->getResponse());

            }

            echo 'PRODUKT AKTUALIZOVAN.<br><br>';

            //print_r($data_woo);

            //print_r($updateproduct);

            /* ----- UPDATE VARIANT ----- */

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

                $variations_query = $mysqli->query("SELECT * FROM products_variations WHERE product_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

                $par_var_name = "";
                $update_variations = array();

                while ($variation = mysqli_fetch_array($variations_query)) {

                    $price_query = $mysqli->query("SELECT site_id, price FROM products_variations_sites WHERE site = '" . $shop['slug'] . "' AND variation_id = '" . $variation['id'] . "'");
                    $price = mysqli_fetch_array($price_query);

                    if ($price['site_id'] != 0) {

                        $instock_query = $mysqli->query("SELECT SUM(instock) as total_instock FROM products_stocks WHERE product_id = '" . $_REQUEST['id'] . "' AND variation_id = '" . $variation['id'] . "'") or die($mysqli->error);
                        $instock = mysqli_fetch_array($instock_query);

                        $data = array(
                            "id" => $price['site_id'],
                            "manage_stock" => true,
                            "stock_quantity" => $instock['total_instock'],
                            "in_stock" => true,
                            "backorders" => "notify",
                            "purchaseable" => true,
                            "backorders_allowed" => true,

                        );

                        array_push($update_variations, $data);

                        //print_r($current_variation);

                    }

                    array_push($variation_eans, $variation['ean']);

                    array_push($actual_vari_ids, $price['site_id']);

                    $act_id = $price['site_id'];

                    $actual_arrays[$act_id] = $variation['ean'];

                }

                echo '<br>----<br>';
                echo 'VARIANTY NA SHOPU<br>';
                print_r($get_arrays);
                echo '<br>----<br>';

                echo '<br>----<br>';
                echo 'VARIANTY SPRAVNE<br>';
                print_r($actual_arrays);
                echo '<br>----<br>';

                foreach ($all_vari as $vari) {

                    if (!in_array($vari['id'], $actual_vari_ids)) {

                        array_push($remove_ids, $vari['id']);

                    }

                }

                echo '<br>----<br>';
                echo 'VARIANTY KE SMAZANI<br>';

                print_r($remove_ids);

                echo '<br>----<br>';
                echo '<br>-- KONEC SHOPU --<br>';
                echo '<br>----<br><br><br><br><br><br><br><br><br>';

                $data = [

                    'update' => $update_variations,

                    'delete' => $remove_ids,

                ];

                $woocommerce_connection->post('products/' . $shop['site_id'] . '/variations/batch', $data);

            }

        }

    }

}
$time_elapsed_secs = microtime(true) - $start;
echo $time_elapsed_secs;
exit;
echo finito;
