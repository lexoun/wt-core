<?php
ignore_user_abort(true);
$start = microtime(true);
// https://woocommerce.github.io/woocommerce-rest-api-docs/#product-properties

if (empty($_REQUEST['shop'])) {
    die('empty shop');
}

include_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/vendor/autoload.php';

use Automattic\WooCommerce\Client;

$shops_query = $mysqli->query("SELECT * FROM shops WHERE slug = '".$_REQUEST['shop']."'") or die($mysqli->error);
$shop = mysqli_fetch_assoc($shops_query);

$woocommerce = new Client(
    $shop['url'],
    $shop['secret_key'],
    $shop['secret_code'],
    [
        'wp_api' => true,
        'version' => 'wc/v3',
        'query_string_auth' => true,

    ]
);

$data = [

    "manage_stock" => true,
    "stock_quantity" => 10,
    "in_stock" => true,
    "backorders" => "notify",
    "backorders_allowed" => true,
    "backordered" => false,

];

$woocommerce->put('products/9019', $data);


/*

$product = $woocommerce->get('products/9019');

foreach($product->images as $image){
    echo $image->src;
}

$stableImagesPaths = [
    'https://eshop.spahouse.cz/wp-content/uploads/2022/04/T_2_front-4.jpg',
    'https://eshop.spahouse.cz/wp-content/uploads/2022/04/T_2_back-4.jpg',
    ];


$isUploaded = false;
foreach($product->images as $newImage){

        $isUploaded = false;
        foreach($stableImagesPaths as $stableImage) {

            if (md5_file($newImage->src) == md5_file($stableImage)) {

                $isUploaded = true;
                echo '<br>Already on website - ' . $stableImage . '<br><br>';

            }
        }
}

$data = [

    "title" => 'testovací',
    "slug" => 'bu-composer',

    "type" => 'simple',

    "description" => $_POST['description'],
    "short_description" => $_POST['short_description'],
    "price" => $_POST['saunahouse_price'],
    "regular_price" => $_POST['saunahouse_price'],

    "reviews_allowed" => false,
    "managing_stock" => true,
    "stock_quantity" => $instock,
    "in_stock" => true,
    "backorders" => "notify",
    "backorders_allowed" => true,
    "backordered" => false,

    "weight" => $weight,
    "dimensions" => [

        "length" => $length,
        "width" => $width,
        "height" => $height,

    ],

    'attributes' => $parent_attributes,

    'categories' => [
        $saunahouse_category_array,
    ],

    'images' =>  [
        [
            'src' => 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_front.jpg'
        ],
        [
            'src' => 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_back.jpg'
        ]
    ],
    'custom_meta' => [
        '_crosssell_ids' => $cross_array,
        '_upsell_ids' => $up_array,
        'ceske_sluzby_dodaci_doba' => $delivery,
    ],

    'variations' => $variations_array,

];

$data = [
    'name' => 'Premium Quality',
    'type' => 'simple',
    'regular_price' => '21.99',
    'description' => 'Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.',
    'short_description' => 'Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.',
    'categories' => [
        [
            'id' => 9
        ],
        [
            'id' => 14
        ]
    ],
    'images' => [
        [
            'src' => 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_front.jpg'
        ],
        [
            'src' => 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_back.jpg'
        ]
    ]
];

//print_r($woocommerce->post('products', $data));

//print_r($woocommerce->put('products/9019', $data));

echo 'ye<br><br><br><br>';
*/



$time_elapsed_secs = microtime(true) - $start;
echo $time_elapsed_secs;
