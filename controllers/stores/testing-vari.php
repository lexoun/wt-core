<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";

include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/vendor/autoload.php';

use Automattic\WooCommerce\Client;

$woocommerce = new Client(
    'https://www.spahouse.cz',
    'ck_90b925bd87a1efdd63bb39becc3f8d64f863b71f',
    'cs_2b07f5c929da7852ffee34112d814b0c76c0127a',
    [
        'wp_api' => true,
        'version' => 'wc/v3',
        'query_string_auth' => true,

    ]
);

$data = [
    'meta_data' => [
        [
            'key' => '_url',
            'value' => 'testorrvací',
        ],
    ],
];

$result = $woocommerce->put('customers/281', $data);

print_r($result);
