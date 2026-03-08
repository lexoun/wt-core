<?php

if(empty($_REQUEST['shop'])){ die('need shop'); }

require_once $_SERVER['DOCUMENT_ROOT'].'/admin/vendor/autoload.php';

include_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/admin/controllers/stores/product-edit.php";

$shops_query = $mysqli->query("SELECT * FROM shops WHERE slug = '" . $_REQUEST['shop'] . "'") or die($mysqli->error);
$shop = mysqli_fetch_assoc($shops_query);


// connect to targeted shop
$woocommerce = shopConnection($shop);


$page = 1;

$getProducts = $woocommerce->get('products', array('per_page' => 100, 'type' => 'simple', 'page' => $page));

foreach($getProducts as $singleProduct){

    echo $singleProduct->sku;

}



/* todo tbd
$getProducts = $mysqli->query("SELECT site_id FROM products p, products_sites s WHERE s.site = '" . $shop['slug'] . "' AND s.product_id = p.id") or die($mysqli->error);

echo 'Main products: (celkem ' . mysqli_num_rows($getProducts) . ')<br><br>';

$ok = 0;
$ko = 0;
$missing = '';
$adminProducts = array();

while ($product = mysqli_fetch_assoc($getProducts)) {

    array_push($adminProducts, $product['site_id']);

    if (in_array($product['site_id'], $shopProducts)) {

        $ok++;
//        $missing .= 'má být (ok) - <a href="'.$shop['url'].'/?page_id='.$product['site_id'].'" target="_blank">'.$product['site_id'].'</a><br>';

    } else {

        $ko++;
        $missing .= '!!!!! NEMÁ BÝT - <a href="'.$shop['url'].'/?page_id='.$product['site_id'].'" target="_blank">'.$product['site_id'].'</a><br>';

    }

}