<?php
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;


// Automatický delete při práci s administrací
if(!empty($_POST['data'])){

    $passedArray = unserialize($_POST['data']);

    $shop = $passedArray['shop'];
    $product = $passedArray['product'];

    require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";

    $mysqli->query("INSERT INTO shops_update_log (state, product_id, shop_slug, site_id, action) VALUES ('failed', '".$shop['product_id']."', '".$shop['slug']."', '".$shop['site_id']."', 'delete')")or die($mysqli->error);

    $logId = $mysqli->insert_id;

    require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/controllers/stores/woo_base.php";

    // has site ID
    if(isset($shop['site_id'])){

        $productID = $shop['site_id'];
        // doesnt have site ID, check for SKU
    }else{

        $productID = wc_get_product_id_by_sku($product['ean']);
    }

    if( isset($product['type']) && $product['type'] === 'variable' ){

        $pr = new WC_Product_Variable($productID);

    }else{

        $pr = new WC_Product_Simple($productID); // "simple" By default

    }

// Solo jednoduchý delete
}elseif(!empty($_REQUEST['id']) && $_REQUEST['action'] == 'product' && !empty($_REQUEST['shop'])){

    require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";

    $shops_query = $mysqli->query("SELECT * FROM shops WHERE slug = '".$_REQUEST['shop']."'") or die($mysqli->error);
    $shop = mysqli_fetch_assoc($shops_query);

    $mysqli->query("INSERT INTO shops_update_log (state, product_id, shop_slug, site_id, action) VALUES ('failed', '0', '".$shop['slug']."', '".$_REQUEST['id']."', 'delete')")or die($mysqli->error);

    $logId = $mysqli->insert_id;

    require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/controllers/stores/woo_base.php";

    try {

        // variable funguje jak na produkty s variantami, tak na simple produkty
        $pr = new WC_Product_Variable($_REQUEST['id']);

    } catch (Exception $pr) {

        echo 'Caught exception: ',  $pr->getMessage(), "\n";
        exit;
    }


}elseif(!empty($_REQUEST['id']) && $_REQUEST['action'] == 'variation' && !empty($_REQUEST['shop'])){

    require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";

    $shops_query = $mysqli->query("SELECT * FROM shops WHERE slug = '".$_REQUEST['shop']."'") or die($mysqli->error);
    $shop = mysqli_fetch_assoc($shops_query);

    require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/controllers/stores/woo_base.php";

    try {

        $removeVari = new WC_Product_Variation($_REQUEST['id']);

    } catch (Exception $pr) {

        echo 'Caught exception: ',  $pr->getMessage(), "\n";
        exit;
    }

    // obrázky u variant
    if ($removeVari->get_image_id('') != 0) {

        $mainImage = get_post($removeVari->get_image_id(''));
        $mainImageMetaData = wp_get_attachment_metadata($mainImage->ID, true);

        $storePath = BASE_PATH . '/wp-content/uploads/' . $mainImageMetaData['file'];
        if (file_exists($storePath)) {

            $deleted_item = wp_delete_attachment($mainImage->ID, true);

        }

    }
    $removeVari->delete(true);

    echo 'Successfully deleted variation';
    exit;

}



if($pr->exists()){

    // hlavní obrázek
    if ($pr->get_image_id() != 0) {

        $mainImage = get_post($pr->get_image_id());
        $mainImageMetaData = wp_get_attachment_metadata($mainImage->ID, true);

        $storePath = BASE_PATH . '/wp-content/uploads/' . $mainImageMetaData['file'];
        if (file_exists($storePath)) {

            $deleted_item = wp_delete_attachment($mainImage->ID, true);

        }

    }

    // ostatní obrázky
    foreach($pr->get_gallery_image_ids() as $singleImage){

        $imageData = wp_get_attachment_metadata($singleImage, true);

        $storePath = BASE_PATH . '/wp-content/uploads/' . $imageData['file'];
        if (file_exists($storePath)) {

            $deleted_item = wp_delete_attachment($singleImage, true);

        }

    }


    // varianty
    foreach ( $pr->get_children() as $variID ) {

        $removeVari = new WC_Product_Variation($variID);

        // obrázky u variant
        if ($removeVari->get_image_id('') != 0) {

            $mainImage = get_post($removeVari->get_image_id(''));
            $mainImageMetaData = wp_get_attachment_metadata($mainImage->ID, true);

            $storePath = BASE_PATH . '/wp-content/uploads/' . $mainImageMetaData['file'];
            if (file_exists($storePath)) {

                $deleted_item = wp_delete_attachment($mainImage->ID, true);

            }

        }
        $removeVari->delete(true);

    }
    $pr->delete(true);

}else{

    echo '<pre>Already deleted</pre>';

}

$mysqli->query("UPDATE shops_update_log SET state = 'success' WHERE id = '".$logId."'")or die($mysqli->error);


$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$total_time = round($finish - $start, 4);

echo '<pre>Page generated in ' . $total_time . ' seconds. - '.$shop['slug'].'</pre>';