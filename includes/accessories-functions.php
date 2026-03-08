<?php

function getProductSites($product_id) {

    global $mysqli;

    $shopsArray = array();
    $shops_query = $mysqli->query("SELECT site FROM products_sites WHERE product_id = '" . $product_id . "'") or die($mysqli->error);

    while ($shop = mysqli_fetch_assoc($shops_query)) {

        array_push($shopsArray, $shop);

    }

    return $shopsArray;
}



function saveProductEvent($id, $category, $type, $shop = '', $shop_id = 0, $specialEncoded = ''){

    global $mysqli;
    global $client;

    $check_duplicity = $mysqli->query("SELECT 
        id, 
        type,
        special 
    FROM webhooks_shops 
    WHERE CAST(finished as DATE) = '0000-00-00' 
        AND retries < 4 
        AND aggregate_id = '".$id."'
        AND category = '".$category."'
        AND type = '".$type."'
        AND shop = '".$shop."'
        ")or die($mysqli->error);

    // todo dont create quantity if update exists

    if(mysqli_num_rows($check_duplicity) == 0){

        $mysqli->query("INSERT INTO 
            webhooks_shops (aggregate_id, category, type, shop, shop_id, created, special) 
            VALUES ('".$id."', '".$category."', '".$type."', '".$shop."', '".$shop_id."', CURRENT_TIMESTAMP(), '".$specialEncoded."')
            ")or die($mysqli->error);

    }else{

        $finded_result = mysqli_fetch_assoc($check_duplicity);

        if($finded_result['type'] != 'update'){ return; }

        $newSpecialDecoded = json_decode($specialEncoded);
        $oldSpecialDecoded = json_decode($finded_result['special']);

        $createSpecial = array();
        $createSpecial['imageChange'] = 0;
        $createSpecial['variationImages'] = 0;
        // todo maybe left empty db cell if is not changed?

        if($newSpecialDecoded->imageChange == 1 || $oldSpecialDecoded->imageChange == 1) {

            $createSpecial['imageChange'] = 1;

        }

        if($newSpecialDecoded->variationImages == 1 || $oldSpecialDecoded->variationImages == 1){

            $createSpecial['variationImages'] = 1;

        }

        $special = json_encode($createSpecial);

        // update query if special is different
        if($special != $finded_result['special']){

            $mysqli->query("UPDATE webhooks_shops SET special = '".$special."' WHERE id = '".$finded_result['id']."'")or die($mysqli->error);

        }

    }

}


function updateShopProduct($db_table, $product_id, $variation_id, $specialEncoded = ''){

    global $mysqli;

    $shops_query = $mysqli->query("SELECT * FROM shops ORDER BY name");

    while ($shop = mysqli_fetch_array($shops_query)) {

        $slug = $shop['slug'];
        $postname = $slug . '_box';

        if (isset($_POST[$postname]) && $_POST[$postname] == '1') {

            $current_price = 0;
            $sale_price = 0;
            if(isset($_POST[$slug . '_price'])){ $current_price = $_POST[$slug . '_price']; }
            if(isset($_POST[$slug . '_sale_price'])){ $sale_price = $_POST[$slug . '_sale_price']; }

            $shop_lookout_query = $mysqli->query("SELECT * FROM $db_table 
                WHERE product_id = '".$product_id."' AND variation_id = '".$variation_id."' AND site = '$slug'");

            if (mysqli_num_rows($shop_lookout_query) == 0) {

                $mysqli->query("INSERT INTO $db_table (product_id, variation_id, site, sale_price) VALUES ('$product_id', '$variation_id', '$slug', '$sale_price')") or die($mysqli->error);

            } else {

                $mysqli->query("UPDATE $db_table 
                    SET sale_price = '$sale_price' 
                    WHERE product_id = '$product_id' AND variation_id = '$variation_id' AND site = '$slug'") or die($mysqli->error);

            }


            // site categories
            $mysqli->query("DELETE FROM products_sites_categories WHERE product_id = '" . $product_id . "' AND site = '$slug'");

            if (isset($_POST[$slug . '_reciever'])) {

                $errors = array_filter($_POST[$slug . '_reciever']);

                if (!empty($errors)) {

                    $categories = $errors;

                    foreach ($categories as $category) {

                        $mysqli->query("INSERT INTO products_sites_categories (product_id, site, category) VALUES ('$product_id', '$slug', '$category')") or die($mysqli->error);

                    }

                }
            }

            if($variation_id === 0){

                // update product on eshop webhook
                saveProductEvent($product_id, 'product', 'update', $slug, 0, $specialEncoded);

            }

        } else {

            // delete product from shop if has site_id
            $shop_lookout_query = $mysqli->query("SELECT site_id FROM $db_table 
                    WHERE product_id = '$product_id' AND variation_id = '".$variation_id."' AND site = '$slug'");

            if(mysqli_num_rows($shop_lookout_query) > 0){

                $shop_lookout = mysqli_fetch_array($shop_lookout_query);

                if (!empty($shop_lookout['site_id'])) {

                    if($variation_id === 0) {

                        // todo test product delete for removed images
                        // remove from eshop webhook
                        saveProductEvent($product_id, 'product', 'delete', $slug, $shop_lookout['site_id']);

                    }

                    // todo remove from eshop webhook
                    //api_product_remove($product_id, $slug);

                }

                $mysqli->query("DELETE FROM $db_table WHERE product_id = '" . $product_id . "' and variation_id = '".$variation_id."' AND site = '$slug'");
                $mysqli->query("DELETE FROM products_sites_categories WHERE product_id = '" . $product_id . "' AND site = '$slug'");

            }
        }
    }
}


function updateVariationValues($variation_id, $data){

    global $mysqli;

    $variation_values = array();
    foreach ($data['variation_name'] as $index => $variation_name) {

        $variation_value = $mysqli->real_escape_string($data['variation_value'][$index]);

        $mysqli->query("DELETE FROM products_variations_values WHERE variation_id = '$variation_id'");
        $mysqli->query("INSERT INTO products_variations_values (variation_id, name, value)
			  	VALUES ('$variation_id', '$variation_name', '$variation_value')") or die($mysqli->error);

    }

}




function productWarehouseStock($product_id, $variation_id = 0, $variation = ''){

    global $mysqli;
    global $client;

    $locations_query = $mysqli->query("SELECT l.id as id, s.*
        FROM shops_locations l 
        LEFT JOIN products_stocks s ON s.location_id = l.id AND s.product_id = '" . $product_id . "' AND s.variation_id = '".$variation_id."'
        GROUP BY l.id");

    $reserved_quantity = 0;
    while ($location = mysqli_fetch_assoc($locations_query)) {

        $location_id = $location['id'];

        if($variation_id !== 0){

            $new_stock = preg_replace('/\s+/', '', $variation['new_stock_' . $location_id]);
            $current_stock = $variation['current_stock_' . $location_id];
            $min_stock = preg_replace('/\s+/', '', $variation['min_stock_location_' . $location_id]);

            $stock_diff = $new_stock - $current_stock;

            if (isset($current_stock) && ($new_stock == "" || $stock_diff == 0)) {
                $quantity = 0;
            } else {
                $quantity = $stock_diff;
            }


        }else{

            $instock = 0;

            $location_id = $location['id'];
            $instock = $_POST['location_' . $location_id];
            $min_stock = $_POST['min_stock_location_' . $location_id];

            $quantity = $instock - $location['instock'];

        }

        product_update($product_id, $variation_id, $location['id'], $quantity, $client['id'], 'to_stock', 0);

        $mysqli->query("UPDATE products_stocks 
            SET min_stock = '".$min_stock."' 
            WHERE product_id = '".$product_id."' AND 
                  variation_id = '".$variation_id."' AND 
                  location_id = '".$location['id']."'
            ") or die($mysqli->error);

    }
}




function productImages($seoslug){

    global $productImageSizes;

    $refreshImages = 0;

    // main picture removed, empty main
    if (isset($_POST['picture']) && $_POST['picture'] == '') {

        foreach($productImageSizes as $imageSize){

            $path = PRODUCT_IMAGE_PATH.'/'.$imageSize.'/'.$seoslug.'.jpg';
            if(file_exists($path)){ unlink($path); };
            unset($path);
        }

        $refreshImages['imageChange'] = 1;

    }

    if (isset($_FILES['picture']) && $_FILES['picture']['size'] != 0 && $_FILES['picture']['error'] == 0) {

        foreach($productImageSizes as $imageSize){

            $path = PRODUCT_IMAGE_PATH.'/'.$imageSize.'/'.$seoslug.'.jpg';
            if(file_exists($path)){ unlink($path); };
            unset($path);
        }

        $path = $_FILES["picture"]["tmp_name"];
        $filename = $seoslug;

        store_image_resize($path, $filename);

        $refreshImages = 1;
    }

    if (isset($_FILES['otherpics']) && $_FILES['otherpics'] != '') {

        if (any_uploaded('otherpics')) {

            $file_ary = reArrayFiles($_FILES['otherpics']);
            $i = 0;
            foreach ($file_ary as $file) {

                $i++;

                $path = $file["tmp_name"];
                $filename = $seoslug . '_' . $i;

                store_image_resize($path, $filename);

                $refreshImages = 1;

            }

        }
    }

    return $refreshImages;

}


function variationImage($variation_id, $post_index, $seoslug) {

    global $productImageSizes;

    $webhookSpecial = array();
    $webhookSpecial['newVariationImage'] = 0;

    // removed variation picture
    if (isset($_POST['variation_picture'][$post_index]) && $_POST['variation_picture'][$post_index] == '') {

        foreach($productImageSizes as $imageSize){

            $path = PRODUCT_IMAGE_PATH.'/'.$imageSize.'/'.$seoslug.'_variation_'.$variation_id.'.jpg';
            if(file_exists($path)){  unlink($path); };
            unset($path);

        }

        $webhookSpecial['newVariationImage'] = 1;

    }

    // new variation picture
    if (isset($_FILES['variation_picture']['name'][$post_index]) && ($_FILES['variation_picture']['size'][$post_index] != 0 && $_FILES['variation_picture']['error'][$post_index] == 0)) {

        foreach($productImageSizes as $imageSize){

            $path = PRODUCT_IMAGE_PATH.'/'.$imageSize.'/'.$seoslug.'_variation_'.$variation_id.'.jpg';
            if(file_exists($path)){  unlink($path); };
            unset($path);

        }

        $path = $_FILES["variation_picture"]["tmp_name"][$post_index];
        $filename = $seoslug . '_variation_' . $variation_id;

        store_image_resize($path, $filename);

        $webhookSpecial['newVariationImage'] = 1;

    }

    return $webhookSpecial;

}


function any_uploaded($name)
{
    foreach ($_FILES[$name]['error'] as $ferror) {
        if ($ferror != UPLOAD_ERR_NO_FILE) {
            return true;
        }
    }
    return false;
}


function reArrayFiles(&$file_post)
{

    $file_ary = array();
    $file_count = count($file_post['name']);
    $file_keys = array_keys($file_post);

    for ($i = 0; $i < $file_count; $i++) {
        foreach ($file_keys as $key) {
            $file_ary[$i][$key] = $file_post[$key][$i];
        }
    }

    return $file_ary;
}




function productSpecs($id){

    global $mysqli;

    $mysqli->query("DELETE FROM products_specifications WHERE product_id = '" . $id . "'");

    // specifications
    if (isset($_POST['specification_value'])) { $specs_values = array_filter($_POST['specification_value']); }
    if (isset($_POST['specification_name'])) { $specs_name = array_filter($_POST['specification_name']); }

    $specifications = array();

    if (!empty($specs_name) && !empty($specs_values)) {

        foreach ($specs_name as $index => $specification) {

            if (!empty($specification) && !empty($specs_values[$index])) {

                $upperName = ucfirst($specification);
                $upperValue = ucfirst($specs_values[$index]);

                $mysqli->query("INSERT INTO products_specifications (product_id, name, value) VALUES ('" . $id . "','$upperName','$upperValue')") or die($mysqli->error);

                $specifications[$upperName] = $upperValue;

            }
        }
    }

    return json_encode($specifications);
}




function createProductStock($data){


}