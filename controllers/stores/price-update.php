<?php

if(empty($_REQUEST['shop'])){ die('empty shop'); }

include_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/vendor/autoload.php';

use Automattic\WooCommerce\Client;


// add all simple products to queue table todo

//'include' => [1, 2, 3, 4... ids]
// add all variable products to queue table¨
$insertValues = '';
$allProducts = $mysqli->query("SELECT * FROM products p, products_sites s WHERE s.product_id = p.id AND s.site = '".$_REQUEST['shop']."'")or die($mysqli->error);
while($product = mysqli_fetch_assoc($allProducts)){

    if(!empty($insertValues)){ $insertValues .= ', '; }
    $insertValues .= "('".$product['id']."', '".$product['type']."', '".$_REQUEST['shop']."')";

}

echo $insertValues;

$return = $mysqli->query("INSERT IGNORE INTO cron_products_basics (product_id, type, shop) VALUES $insertValues")or die($mysqli->error);

print_r($return);
exit;

// loop all simple products in queue via cron + loop all variable products in queue via cron --- can be same cron? i think so

$allProducts = [];
$adminProducts = $mysqli->query("SELECT p.*, s.sale_price FROM products p LEFT JOIN products_sites s ON s.site = '".$_REQUEST['shop']."' AND s.product_id = p.id  WHERE type = 'simple'")or die($mysqli->error);
while($adminProduct = mysqli_fetch_assoc($adminProducts)){
    $allProducts[] = $adminProduct;
}

$allVariations = [];
$adminVariations = $mysqli->query("SELECT * FROM products_variations")or die($mysqli->error);
while($adminVari = mysqli_fetch_assoc($adminVariations)){
    $allVariations[] = $adminVari;
}

$shops_query = $mysqli->query("SELECT * FROM shops WHERE slug = '".$_REQUEST['shop']."'") or die($mysqli->error);
while ($shop = mysqli_fetch_assoc($shops_query)) {

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

    // we don't need to get all products from web. the product ids are already in our database



    // maybe there should check of products that are not in ADMIN DB but are on the eshops

    $page = 1;
    $products = [];
    $all_products = [];
    do{
        try {
            $products = $woocommerce->get('products',array('per_page' => 100, 'type' => 'simple', 'page' => $page));
        }catch(HttpClientException $e){
            die("Can't get products: $e");
        }
        $all_products = array_merge($all_products, $products);
        $page++;
    } while (count($products) > 0);

    // need to do multiple requests
    //$siteProducts = $woocommerce->get('products', ['per_page' => 10, 'type' => 'simple', 'page' => 2]);

    exit;

    // todo the check
    $i = 0;
    foreach($all_products as $siteProduct){
        $i++;

        echo $i.': ';

        if($siteProduct->type == 'simple'){

            $key = array_search($siteProduct->sku, array_column($allProducts, 'ean'));

            if(!isset($key)){ echo '<strong>MISSING KEY</strong>'; }else{ echo $key.' ADMID:'.$allProducts[$key]['id'].' '; }

            echo ' '.$siteProduct->sku.' - '.$siteProduct->id.', price: '.$siteProduct->price.' x ADM price: ';

            if(!isset($key)){ echo '<strong>NO KEY</strong>'; }else{ echo $allProducts[$key]['price']; }

            if($allProducts[$key]['sale_price'] != 0 && $allProducts[$key]['price'] != $allProducts[$key]['sale_price']){

                echo '<strong> SPECIAL PRICE: '.$allProducts[$key]['sale_price'].' </strong>';
            }

            if($siteProduct->price != $allProducts[$key]['price']){
                echo ' - <strong style="color: red;">price is different. what the fuck!</strong>';
            }

        }elseif($siteProduct->type == 'variable'){

            echo ' '.$siteProduct->sku.' - '.$siteProduct->id.', ';

            $thisProductVariations = $woocommerce->get('products/'.$siteProduct->id.'/variations');

            foreach($thisProductVariations as $thisVari){

                echo 'VARI --- ';

                $key = array_search($thisVari->sku, array_column($allVariations, 'ean'));

                if(!isset($key)){ echo '<strong>MISSING KEY</strong>'; }else{ echo $key.' ADMID:'.$allVariations[$key]['id'].' '; }

                echo ' '.$thisVari->sku.' - '.$thisVari->id.', price: '.$thisVari->price.' x ADM price: ';

                if(!isset($key)){ echo '<strong>NO KEY</strong>'; }else{ echo $allVariations[$key]['price']; }

                if($allVariations[$key]['sale_price'] != 0 && $allVariations[$key]['price'] != $allVariations[$key]['sale_price']){

                    echo '<strong> SPECIAL PRICE: '.$allVariations[$key]['sale_price'].' </strong>';
                }

                if($thisVari->price != $allVariations[$key]['price']){
                    echo ' - <strong style="color: red;">price is different. what the fuck!</strong>';
                }

                echo '<br>____<br>';


            }

        }

        echo '<br>';

    }

}