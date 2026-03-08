<?php
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;
require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";

//$showLog = true;
if(isset($showLog) && $showLog) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

if(!empty($_REQUEST['id'])){

    $selectedStore = '';

    if(!empty($_REQUEST['store_slug'])){

        $selectedStore = 'AND s.slug = "'.$_REQUEST['store_slug'].'"';

    }

    $product_query = $mysqli->query("SELECT p.*, SUM(s.instock) as total_instock, m.delivery_time as manufacturer_delivery, m.manufacturer as manufacturer_name FROM products p LEFT JOIN products_stocks s ON s.product_id = p.id LEFT JOIN products_manufacturers m ON m.id = p.manufacturer WHERE p.id = '".$_REQUEST['id']."'") or die($mysqli->error);
    $product = mysqli_fetch_assoc($product_query);

    $product['attribute_text'] = array();
    $specifications_query = $mysqli->query("SELECT name, value FROM products_specifications WHERE product_id = '" . $_REQUEST['id'] . "' group by value") or die($mysqli->error);
    while ($specification = mysqli_fetch_assoc($specifications_query)) {

        $product['attributes'][$specification['name']][] = $specification['value'];

        $product['attribute_text'] = [
            ['key' => '_specifications_attributes_title',
                'value' => 'Základní informace'], [
                'key' => '_specifications_display_attributes',
                'value' => 'yes',

            ],
        ];

    }

    $variations_query = $mysqli->query("SELECT id FROM products_variations WHERE product_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);
    while ($variation = mysqli_fetch_assoc($variations_query)) {

        $var_values_query = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $variation['id'] . "'") or die($mysqli->error);
        while ($var_value = mysqli_fetch_assoc($var_values_query)) {

            $product['variation_attributes'][$var_value['name']][] = $var_value['value'];

        }

    }

    /* Shops Start */

    $shops_query = $mysqli->query("SELECT p.id, p.product_id, s.folder, s.slug, p.site_id FROM shops s, products_sites p WHERE p.product_id = '" . $_REQUEST['id'] . "' AND p.site = s.slug AND s.active = 1 $selectedStore ORDER BY s.slug ASC") or die($mysqli->error);
    while ($shop = mysqli_fetch_assoc($shops_query)) {

        $posted = array(
            'shop' => $shop,
            'product' => $product
        );

        $params = json_encode($posted);


        if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete'){

            $url = "https://www.wellnesstrade.cz/admin/controllers/stores/new-direct-delete";

        }else{

            $url = "https://www.wellnesstrade.cz/admin/controllers/stores/new-direct-single";

        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CAINFO, "/global/applications/tools/cacert.pem");
        curl_setopt($ch, CURLOPT_CAPATH, "/global/applications/tools/");
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('data'=>serialize($posted)));

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

//        $showLog = true;
        if ($err) {
            if(isset($showLog) && $showLog){ echo "cURL Error #:" . $err; }
        } else {
            if(isset($showLog) && $showLog){ echo $response;  }
        }

    }

}else{

    if(isset($showLog) && $showLog){    echo '<pre>Empty product ID</pre>';}
    
}

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$total_time = round($finish - $start, 4);

if(isset($showLog) && $showLog){  echo '<pre>Page generated in ' . $total_time . ' seconds.</pre>'; }

if(isset($_REQUEST['redirect']) && $_REQUEST['redirect']){

    header('Location: https://www.wellnesstrade.cz/admin/pages/accessories/zobrazit-prislusenstvi?id='.$_REQUEST['id']);

}