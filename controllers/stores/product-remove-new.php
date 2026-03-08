<?php
ignore_user_abort(true);


require LIBRARIES . '/wootest/vendor/autoload.php';
use Automattic\WooCommerce\Client;

require LIBRARIES . '/woocommerce-api/lib/woocommerce-api.php';

if ($_REQUEST['site_id'] != "" && isset($_REQUEST['site_id'])) {

    include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";

    $shop_query = $mysqli->query("SELECT * FROM shops WHERE slug = '" . $_REQUEST['site'] . "'");
    $shop = mysqli_fetch_assoc($shop_query);

    if (isset($shop['api']) && $shop['api'] == 'new') {

        $woocommerce = new Client(
            $shop['url'],
            $shop['secret_key'],
            $shop['secret_code'],
            [
                'wp_api' => true,
                'version' => 'wc/v2',
                'query_string_auth' => true,

            ]
        );

        $woocommerce->delete('products/' . $_REQUEST['site_id'], ['force' => true]);

    } elseif (isset($shop['api']) && $shop['api'] == 'old') {

        $woocommerce_connection = "";
        $woocommerce_connection = new WC_API_Client(
            $shop['url'],
            $shop['secret_key'],
            $shop['secret_code'],
            [
                'ssl_verify' => false,

            ]
        );

        $woocommerce_connection->products->delete($_REQUEST['site_id'], true);

    }

}
