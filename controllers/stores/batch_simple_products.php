<?php
ignore_user_abort(true);

$start = microtime(true);

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";
include LIBRARIES . '/wootest/vendor/autoload.php';
use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;

$print = '';

$shops_query = $mysqli->query("SELECT url, secret_key, secret_code, slug FROM shops") or die($mysqli->error);
while ($shop = mysqli_fetch_array($shops_query)) {

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

    $data = "";

    $i = 0;

    $importer_query = $mysqli->query("SELECT p.id as product_id, p.availability, s.id, s.delivery_time as product_delivery, s.site_id, SUM(stock.instock) as instock FROM products p, products_sites s, products_stocks stock, cron_jobs j WHERE s.site_id <> 0 AND s.product_id = p.id AND s.site = '" . $shop['slug'] . "' AND p.id = stock.product_id AND p.type = 'simple' AND j.product_id = p.id AND j.type = 'batch_simple' AND j.shop_slug = '" . $shop['slug'] . "' GROUP BY p.id LIMIT 60") or die($mysqli->error);

    if (mysqli_num_rows($importer_query) > 0) {

        $all_products = array();
        while ($product = mysqli_fetch_array($importer_query)) {

            $i++;

            $instock = $product['instock'];

            if ($instock > 0) {

                $delivery = 0;

            } else {

                $delivery = $product['product_delivery'];

            }

            $print = $print . '#' . $i . '-' . $shop['slug'] . ' - Sys ID: ' . $product['product_id'] . ' --- Shop ID: ' . $product['site_id'] . ' --- Delivery: ' . $delivery . ' --- Instock: ' . $instock . '<br>';

            $this_array = [

                "id" => $product['site_id'],
                "manage_stock" => true,
                "stock_quantity" => $instock,
                "backorders" => "notify",
                "backordered" => false,

            ];

            if ($product['availability'] != 3) {

                $this_array['catalog_visibility'] = 'visible';
                $this_array['in_stock'] = true;
                $this_array['backorders_allowed'] = true;

            } elseif (isset($product['availability']) && $product['availability'] == 3) {

                $this_array['catalog_visibility'] = 'hidden';
                $this_array['in_stock'] = false;
                $this_array['backorders_allowed'] = false;

            }

            $deli = ["key" => "ceske_sluzby_dodaci_doba",
                "value" => $delivery,
            ];

            $this_array['meta_data'] = [$deli];

            array_push($all_products, $this_array);

            $mysqli->query("DELETE FROM cron_jobs WHERE product_id = '" . $product['product_id'] . "' AND type = 'batch_simple' AND shop_slug = '" . $shop['slug'] . "'") or die($mysqli->error);

        }

        $data = ['update' => $all_products];

        try {

            $woocommerce_connection->post('products/batch', $data);

        } catch (HttpClientException $e) {

            print_r($e->getMessage());

        }

    }

}
$time_elapsed_secs = microtime(true) - $start;
echo $print . '<br><br>' . $i . '<br>' . $time_elapsed_secs;
exit;
