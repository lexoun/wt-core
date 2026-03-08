<?php


include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";

require LIBRARIES . '/woocommerce-api/lib/woocommerce-api.php';

$options = array(
    'ssl_verify' => false,
);

$consumer_key = 'ck_c2fca1469ac8e59ff45d137193b25d90baa33434';
$consumer_secret = 'cs_7d21834f8052167ffef7fc03c8695033d62a4c31';

try {

    $woocommerce = new WC_API_Client('https://www.spamall.cz', $consumer_key, $consumer_secret, $options);

} catch (WC_API_Client_Exception $e) {

    echo $e->getMessage() . PHP_EOL;
    echo $e->getCode() . PHP_EOL;

    if ($e instanceof WC_API_Client_HTTP_Exception) {

        print_r($e->get_request());
        print_r($e->get_response());
    }
}

$options = array(
    'ssl_verify' => false,
);

$consumer_key = 'ck_387cb75a11f5d35eb5b5b0654bc71d77c7933e6f';
$consumer_secret = 'cs_9438787e0de8ec7cced2caada938b7441430d43d';

try {

    $woocommerce_saunahouse = new WC_API_Client('https://beta.saunahouse.cz', $consumer_key, $consumer_secret, $options);

} catch (WC_API_Client_Exception $d) {

    echo $d->getMessage() . PHP_EOL;
    echo $d->getCode() . PHP_EOL;

    if ($d instanceof WC_API_Client_HTTP_Exception) {

        print_r($d->get_request());
        print_r($d->get_response());
    }
}

$number = 0;

$number_sauna = 0;

/*

$product_query = $mysqli->query("SELECT p.ean, s.id as id FROM products p, products_sites s WHERE s.product_id = p.id AND s.site = 'spamall' AND s.site_id = 0 order by rand()")or die($mysqli->error);

while($product = mysqli_fetch_array($product_query)){

$find_product = "";
$new_filter = "";
$spamall_id = "";

$new_filter = array(
'filter' => array(
'sku' => $product['ean']
)
);

$find_product = $woocommerce->products->get(null, $new_filter );

$spamall_id = $find_product->products[0]->id;

if($spamall_id != ""){

$insert_query = $mysqli->query("UPDATE products_sites SET site_id = '$spamall_id' WHERE id = '".$product['id']."'");

}

$number++;
echo $spamall_id.'<br>';
if($number > 40){
exit;

}

}
 */

$product_query = $mysqli->query("SELECT p.ean, s.id as id FROM products p, products_sites s WHERE s.product_id = p.id AND s.site = 'saunahouse' AND s.site_id = 0") or die($mysqli->error);

while ($product = mysqli_fetch_array($product_query)) {

    $find_product = "";
    $new_filter = "";
    $spamall_id = "";

    $new_filter = array(
        'filter' => array(
            'sku' => $product['ean'],
        ),
    );

    $find_product = $woocommerce_saunahouse->products->get(null, $new_filter);

    $spamall_id = $find_product->products[0]->id;

    if ($spamall_id != "") {

        $insert_query = $mysqli->query("UPDATE products_sites SET site_id = '$spamall_id' WHERE s.id = '" . $product['id'] . "'");

    }

    $number_sauna++;
    echo $spamall_id . '<br>';
    if ($number_sauna > 20) {
        exit;

    }

}

echo $number . ' SPAM<br><br>';
echo $number_sauna . ' SAUNA<br><br>';
