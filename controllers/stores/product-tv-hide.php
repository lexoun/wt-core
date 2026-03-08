<?php
ignore_user_abort(true);


require LIBRARIES . '/wootest/vendor/autoload.php';
use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;

require LIBRARIES . '/woocommerce-api/lib/woocommerce-api.php';

if ($_REQUEST['id'] != "" && isset($_REQUEST['id'])) {

    include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";

    $shops_query = $mysqli->query("SELECT *, p.id as id FROM shops s, products_sites p WHERE p.product_id = '" . $_REQUEST['id'] . "' AND p.site = s.slug") or die($mysqli->error);
    while ($shop = mysqli_fetch_array($shops_query)) {

        echo $shop['name'] . '<br><br>';

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

        $product_query = $mysqli->query("SELECT * FROM products WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

        $product = mysqli_fetch_array($product_query);

        $update = $mysqli->query("UPDATE products SET checked = '0' WHERE id = '" . $product['id'] . "'") or die($mysqli->error);

        $data_woo = [];

        if ($product['availability'] != 3) {

            $data_woo['catalog_visibility'] = 'visible';
            $data_woo['in_stock'] = 'true';
            $data_woo['backorders_allowed'] = "true";

        } elseif (isset($product['availability']) && $product['availability'] == 3) {

            $data_woo['catalog_visibility'] = 'hidden';
            $data_woo['in_stock'] = 'false';
            $data_woo['backorders_allowed'] = "false";

        }

        try {

            $updateproduct = $woocommerce_connection->put('products/' . $shop['site_id'], $data_woo);

        } catch (HttpClientException $e) {

            //print_r($e->getResponse());

        }

        echo 'PRODUKT AKTUALIZOVAN.<br><br>';

        //print_r($data_woo);

        //print_r($updateproduct);

    }

}
