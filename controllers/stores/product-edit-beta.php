<?php

$shop = $posted['shop'];
$product = $posted['product'];

$mysqli->query("INSERT INTO shops_update_log (state, product_id, shop_slug, site_id, action) VALUES ('failed', '".$shop['product_id']."', '".$shop['slug']."', '".$shop['site_id']."', 'update')")or die($mysqli->error);

$logId = $mysqli->insert_id;

require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/controllers/stores/woo_base.php";

if(!empty($product['manufacturer_name'])){ $product['attributes']['Výrobce'][] = $product['manufacturer_name']; }

if(!empty($product['attributes'])){ $attributesFalse = generate_attributes_list_for_product($product['attributes'], false); }
if(!empty($product['variation_attributes'])){ $attributesTrue = generate_attributes_list_for_product($product['variation_attributes'], true); }

$attribs = array();

if(!empty($attributesFalse) && !empty($attributesTrue)){

    $attribs = array_merge($attributesFalse, $attributesTrue);

}elseif(!empty($attributesTrue)){

    $attribs = $attributesTrue;

}elseif(!empty($attributesFalse)){

    $attribs = $attributesFalse;

}

$instock = $product['total_instock'];

if ($instock > 0 && $product['availability'] != 4) {

    $delivery = 0;

} else {

    // individual delivery time
    if (!empty($product['delivery_time'])) {

        $delivery = $product['delivery_time'];

        // manufacturer delivery
    } elseif(!empty($product['manufacturer_delivery'])) {

        $delivery = $product['manufacturer_delivery'];

    }else {

        $delivery = 14;

    }

    // set delivery date for "on order"
    if($product['availability'] == 2){ $delivery = 999; }
    if($product['availability'] == 4){ $delivery = 998; $instock = 0; }

}



$this_category_array = array();
$category_query = $mysqli->query("SELECT category FROM products_sites_categories WHERE product_id = '" . $shop['product_id'] . "' AND site = '".$shop['slug']."'") or die($mysqli->error);

while ($category = mysqli_fetch_assoc($category_query)) {

    $this_category_array[] = $category['category'];

}


$cross_array = "";
$up_array = "";

if (isset($product['cross_selling']) && $product['cross_selling'] != "" && $product['cross_selling'] != "N;") {

    $cross_array = $product['cross_selling'];

}

if (isset($product['up_selling']) && $product['up_selling'] != "" && $product['up_selling'] != "N;") {

    $up_array = $product['up_selling'];

}

$cross = ['key' => '_crosssell_ids',
    'value' => $cross_array,
];

$ups = ['key' => '_upsell_ids',
    'value' => $up_array,
];

$deli = ['key' => 'ceske_sluzby_dodaci_doba',
    'value' =>  $delivery,
];


$meta_data = [$cross, $ups, $deli];
$meta_data = array_merge((array)$meta_data, (array)$product['attribute_text']);


if ($product['availability'] != 3) {

    $setCatalogVisibility = 'visible';
    $setStockStatus = true;

} elseif ($product['availability'] == 3) {

    $setCatalogVisibility = 'hidden';
    $setStockStatus = false;

}


// has site ID
if(isset($shop['site_id'])){

    $productID = $shop['site_id'];

// doesnt have site ID, check for SKU
}else{

    $productID = wc_get_product_id_by_sku($product['ean']);

}


$product_post = array(
    'post_title'  => $product['productname'],
    'post_name'   => $product['seourl'],
    'post_status' => 'publish',
    'post_parent' => 0,
    'post_type'   => 'product',
    'guid'        => $product['seourl']
);


// Creating the product
if(empty(get_post( $productID ))){

    $productID = wp_insert_post( $product_post );

// Product is created. Updating
}else{
    $product_post['ID'] = $productID;
    $productID = wp_update_post( $product_post );
}


if( isset($product['type']) && $product['type'] === 'variable' ){

    $pr = new WC_Product_Variable($productID);

}else{

    $pr = new WC_Product_Simple($productID); // "simple" By default

}



if ($product['pdf'] != '') {

    $description = $product['description'] . '

                  <a href="https://www.wellnesstrade.cz/data/stores/' . $product['pdf'] . '" class="single_pdf_button button alt" target="_blank">Zobrazit prospekt (PDF)</a>';

} else {

    $description = $product['description'];

}


$pr->set_name($product['productname']);
$pr->set_slug($product['seourl']);
$pr->set_description($description);
$pr->set_short_description($product['short_description']);
$pr->set_price($product['price']);
$pr->set_regular_price($product['price']);

$pr->set_manage_stock(true);

$pr->set_weight($product['weight']);
$pr->set_length($product['length']);
$pr->set_width($product['width']);
$pr->set_height($product['height']);

$pr->set_stock_quantity($instock);

$pr->set_backorders('notify');

$pr->set_stock_status($setStockStatus);
$pr->set_catalog_visibility($setCatalogVisibility);


//    $product->set_upsell_ids( isset( $args['upsells'] ) ? $args['upsells'] : '' );
//    $product->set_cross_sell_ids( isset( $args['cross_sells'] ) ? $args['upsells'] : '' );


$pr->set_attributes($attribs);


$pr->set_sku($product['ean']);
$pr->set_category_ids($this_category_array);

foreach($meta_data as $data => $singleMeta){

    if($pr->meta_exists($singleMeta['key'])){

        $pr->update_meta_data($singleMeta['key'], $singleMeta['value']);

    }else{

        $pr->add_meta_data($singleMeta['key'], $singleMeta['value']);

    }
}

// CHECK IF IMAGE ON SHOP IS DIFFERENT
$adminPath = $_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/catalog/' . $product['seourl'] . '.jpg';

$newPicture = false;

if ($pr->get_image_id() != 0) {

    $mainImage = get_post($pr->get_image_id());
    $mainImageMetaData = wp_get_attachment_metadata($mainImage->ID, true);

    $path = BASE_PATH . '/wp-content/uploads/' . $mainImageMetaData['file'];
    $finalPath = substr($path, 0, strrpos($path, '.'));

    $storePath = $finalPath.'-420x480.jpg';
//
//    echo $storePath;
//    echo $adminPath;

    if ((file_exists($storePath) && !file_exists($adminPath)) || (!file_exists($storePath) && !file_exists($adminPath)) || (!file_exists($path) && !file_exists($adminPath))){
        // removed image or // shop has imageID but image doesnt exist, no new image..

        echo 'Removed image';
        $deleted_item = wp_delete_attachment($mainImage->ID, true);
        $pr->set_image_id(0);
        $newPicture = false;

    }elseif((file_exists($storePath) && file_exists($adminPath)) && (md5_file($storePath) != md5_file($adminPath)) || !file_exists($storePath) || (!file_exists($path) && file_exists($adminPath))) {
        // different image

        echo 'Diff Image';
        $deleted_item = wp_delete_attachment($mainImage->ID, true);
        $pr->set_image_id(0);
        $newPicture = true;

    }else{

        echo 'Same Image';

    }

} elseif(file_exists($adminPath)) {

    // new image
    echo 'New image';
    $newPicture = true;

}


if ($newPicture && file_exists($adminPath)) {

    $attachID = copy_image($product['seourl'], $productID);

    set_post_thumbnail($productID, $attachID);
    $pr->set_image_id($attachID);

}




$stableImagesPaths = array();
$removedImageIDs = array();


$attachedImages = $pr->get_gallery_image_ids();
foreach($attachedImages as $singleImage){

    $imageData = wp_get_attachment_metadata($singleImage, true);

    $path = BASE_PATH . '/wp-content/uploads/' . $imageData['file'];
    $finalPath = substr($path, 0, strrpos($path, '.'));

    $imagePath = $finalPath.'-420x480.jpg';

    if(!file_exists($imagePath) || in_array($imagePath, $stableImagesPaths)){

        wp_delete_attachment($singleImage, true);

        foreach (array_keys($stableImagesPaths, $finalPath, true) as $key) {
            unset($stableImagesPaths[$key]);
        }

        $removedImageIDs[] = $singleImage;


    }else{

        $stableImagesPaths[$singleImage] = $imagePath;

    }

}

$newImagesPaths = array();
$images = glob($_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/catalog/' . $product['seourl'] . '_{,[1-9]}[0-9].jpg', GLOB_BRACE);

if (!empty($images)) {

    foreach ($images as $image) {

        $newImagesPaths[] = $image;

    }
}


$checkedIDs = $pr->get_gallery_image_ids();

// todo lepší nahrávání dalších obrázků - bez mazání
// porovnání rozdílů

foreach($newImagesPaths as $newImage){

    $isUploaded = false;
    foreach($stableImagesPaths as $stableImage){

        if(md5_file($stableImage) == md5_file($newImage)){

            $isUploaded = true;
//                echo 'Already on website'.$newImage.'<br><br>';

        }

    }

    if(!$isUploaded){

//            echo 'New Upload'.$newImage.'<br><br>';
        $filename = pathinfo($newImage, PATHINFO_FILENAME);
        $attachID = copy_image($filename, $productID);
        $checkedIDs[] = $attachID;
    }
}


foreach($stableImagesPaths as $key => $stableImage){

    $isMissing = true;
    foreach($newImagesPaths as $newImage){

        if(md5_file($stableImage) == md5_file($newImage)){

            $isMissing = false;
//                echo 'Is in images '.$stableImage.'<br><br>';

        }

    }

    if($isMissing){

//            echo 'To be removed '.$stableImage.$key.'<br><br>';
        wp_delete_attachment($key, true);
        $removedImageIDs[] = $key;

    }
}

$galeryImageIDs = array_diff($checkedIDs, $removedImageIDs);

$pr->set_gallery_image_ids($galeryImageIDs);

$stableVariations = array();
foreach ( $pr->get_children() as $childID ) {

    $stableVariations[] = $childID;

}

$savedID = $pr->save();
$wpdb->update( $wpdb->posts, array('guid' => $pr->get_permalink()), array('ID' => $productID) );


if ($savedID <= 0) return "Unable to create / update product!";

//Attribute Terms: These need to be set otherwise the attributes dont show on the admin backend:
foreach ($attribs as $attrib)
{
    /** @var WC_Product_Attribute $attrib */
    $tax = $attrib->get_name();
    $vals = $attrib->get_options();

    $termsToAdd = array();

    if (is_array($vals) && count($vals) > 0)
    {
        foreach ($vals as $val)
        {
            //Get or create the term if it doesnt exist:
            $term = get_attribute_term($val, $tax);

            if ($term['id']) $termsToAdd[] = $term['id'];
        }
    }

    if (count($termsToAdd) > 0)
    {
        wp_set_object_terms($savedID, $termsToAdd, $tax, true);
    }
}


$mysqli->query("UPDATE products_sites SET site_id = '".$savedID."' WHERE product_id = '".$shop['product_id']."' AND site = '".$shop['slug']."'")or die($mysqli->error);



if($product['type'] == 'variable'){

    $newVariations = array();

    $variations_query = $mysqli->query("SELECT * FROM products_variations WHERE product_id = '" . $shop['product_id'] . "'") or die($mysqli->error);

    while ($variation = mysqli_fetch_assoc($variations_query)) {

//        echo 'abc<br>';

        $instock_query = $mysqli->query("SELECT SUM(instock) as total_instock FROM products_stocks WHERE product_id = '" . $shop['product_id'] . "' AND variation_id = '" . $variation['id'] . "'") or die($mysqli->error);
        $instock = mysqli_fetch_assoc($instock_query);

        $site_id_query = $mysqli->query("SELECT site_id FROM products_variations_sites WHERE site = '" . $shop['slug'] . "' AND variation_id = '" . $variation['id'] . "'");
        $site_id = mysqli_fetch_assoc($site_id_query);


        $var_attributes = array();
        $var_values_query = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $variation['id'] . "'") or die($mysqli->error);
        while ($var_value = mysqli_fetch_assoc($var_values_query)) {

            $var_attributes[$var_value['name']] = $var_value['value'];

        }

        if ($variation['sale_price'] != 0 && $variation['sale_price'] != "") {

            $sale_price = $variation['sale_price'];

        } else {

            $sale_price = "";

        }


        // has site ID
        if(isset($site_id['site_id'])){

            $variationID = $site_id['site_id'];

            // doesnt have site ID, check for SKU
        }else{

            $variationID = wc_get_product_id_by_sku($variation['ean']);

        }


        // individual delivery time
        if (!empty($product['delivery_time'])) {

            $variation_delivery = $product['delivery_time'];

            // manufacturer delivery
        } elseif(!empty($product['manufacturer_delivery'])) {

            $variation_delivery = $product['manufacturer_delivery'];

        }else {

            $variation_delivery = 14;

        }

        $final_stock = $instock['total_instock'];

        // set delivery date for "on order"
        if($variation['availability'] == 2){ $variation_delivery = 999; }
        if($variation['availability'] == 4){ $variation_delivery = 998; $final_stock = 0; }

        if ($variation['availability'] != 3) {

            $varStatus = 'publish';

        } elseif ($variation['availability'] == 3) {

            $varStatus = 'private';

        }

        // The variation data
        $variation_data =  array(
            'original_id'    => $variation['id'],
            'attributes'     => $var_attributes,
            'sku'            => $variation['ean'],
            'regular_price'  => $variation['price'],
            "regular_price"  => $variation['price'],
            "sale_price"     => $sale_price,
            "stock_quantity" => $final_stock,
            'delivery_time'  => $variation_delivery,
            'status'         => $varStatus,
        );





        // The function to be run
        $variationID = create_product_variation( $savedID, $variationID, $variation_data );

        $mysqli->query("UPDATE products_variations_sites SET site_id = '$variationID' WHERE site = '" . $shop['slug'] . "' AND variation_id = '" . $variation['id'] . "'");

        $newVariations[] = $variationID;
    }

    // Removal of variations
    $removedVariations = array_diff($stableVariations, $newVariations);

    foreach($removedVariations as $removedSingle){

        $removeVari = new WC_Product_Variation($removedSingle);

        // get_image_id('') => getting image_id only of variation
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

}

$mysqli->query("UPDATE shops_update_log SET state = 'success', site_id = '".$savedID."' WHERE id = '".$logId."'")or die($mysqli->error);

exit;

/* Copy image from file folders to single e-shops */
function copy_image( $filename, $productID) {

    $upload_dir = wp_upload_dir();
    if (wp_mkdir_p($upload_dir['path'])) {
        $file = $upload_dir['path'] . '/' . $filename . '.jpg';
    } else {
        $file = $upload_dir['basedir'] . '/' . $filename . '.jpg';
    }

    $attachment = array(
        'post_title' => sanitize_file_name($filename . '.jpg'),
        'post_date' => date('Y-m-d H:i:s'),
        'post_author' => 1,
        'post_content' => $filename . '.jpg',
        'post_status' => 'inherit',
        'post_parent' => $productID,
        'post_mime_type' => 'image/jpeg',
        'post_guid' => $upload_dir['url'] . '/' . $filename . '.jpg',
    );

    $attachID = wp_insert_attachment($attachment, $file, $productID, true);

    $sizeBig = $_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/big/' . $filename . '.jpg';
    $sizeSingle = $_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/single/' . $filename . '.jpg';
    $sizeCatalog = $_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/catalog/' . $filename . '.jpg';
    $sizeThumbnail = $_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/thumbnail/' . $filename . '.jpg';
    $sizeGaleryThumbnail = $_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/small/' . $filename . '.jpg';

    if (file_exists($sizeBig)) {
        copy($sizeBig, BASE_PATH . '/wp-content/uploads/' . $upload_dir['subdir'] . '/' . $filename . '.jpg');
    };
    if (file_exists($sizeSingle)) {
        copy($sizeSingle, BASE_PATH . '/wp-content/uploads/' . $upload_dir['subdir'] . '/' . $filename . '-700x800.jpg');
    };
    if (file_exists($sizeCatalog)) {
        copy($sizeCatalog, BASE_PATH . '/wp-content/uploads/' . $upload_dir['subdir'] . '/' . $filename . '-420x480.jpg');
    };
    if (file_exists($sizeThumbnail)) {
        copy($sizeThumbnail, BASE_PATH . '/wp-content/uploads/' . $upload_dir['subdir'] . '/' . $filename . '-210x240.jpg');
    };
    if (file_exists($sizeGaleryThumbnail)) {
        copy($sizeGaleryThumbnail, BASE_PATH . '/wp-content/uploads/' . $upload_dir['subdir'] . '/' . $filename . '-105x120.jpg');
    };

    $sizeInfo = getimagesize($sizeBig);

    $attachMeta = array(
        'width' => $sizeInfo[0],
        'height' => $sizeInfo[1],
        'file' => $upload_dir['subdir'] . '/' . $filename . '.jpg',
        'sizes' => array(
            'woocommerce_single' => array(
                'width' => 700,
                'height' => 800,
                'mime-type' => 'image/jpeg',
                'file' => $filename . '-700x800.jpg',
            ),
            'shop_single' => array(
                'width' => 700,
                'height' => 800,
                'mime-type' => 'image/jpeg',
                'file' => $filename . '-700x800.jpg',
            ),
            'shop_catalog' => array(
                'width' => 420,
                'height' => 480,
                'mime-type' => 'image/jpeg',
                'file' => $filename . '-420x480.jpg',
            ),
            'woocommerce_thumbnail' => array(
                'width' => 210,
                'height' => 240,
                'mime-type' => 'image/jpeg',
                'file' => $filename . '-210x240.jpg',
            ),
            'woocommerce_gallery_thumbnail' => array(
                'width' => 105,
                'height' => 120,
                'mime-type' => 'image/jpeg',
                'file' => $filename . '-105x120.jpg',
            ),
            'shop_thumbnail' => array(
                'width' => 105,
                'height' => 120,
                'mime-type' => 'image/jpeg',
                'file' => $filename . '-105x120.jpg',
            ),
        ),
        'image_meta' => array(
            'aperture' => 0,
            'credit' => '',
            'camera' => '',
            'caption' => '',
            'created_timestamp' => 0,
            'copyright' => '',
            'focal_length' => 0,
            'iso' => 0,
            'shutter_speed' => 0,
            'title' => '',
            'orientation' => 0,
            'keywords' => array(),
        ),
    );

    wp_update_attachment_metadata($attachID, $attachMeta);
    return $attachID;
}


/**
 * Create a product variation for a defined variable product ID.
 *
 * @since 3.0.0
 * @param int   $product_id | Post ID of the product parent variable product.
 * @param int   $variation_id | Post ID of the product parent variable product.
 * @param array $variation_data | The data to insert in the product.
 */

function create_product_variation( $product_id, $variation_id, $variation_data ){

    // Get the Variable product object (parent)
    $product = wc_get_product($product_id);

    $variation_post = array(
        'post_title'  => $product->get_name(),
        'post_name'   => 'product-'.$product_id.'-variation',
        'post_status' => $variation_data['status'],
        'post_parent' => $product_id,
        'post_type'   => 'product_variation',
        'guid'        => $product->get_permalink()
    );


    // Creating the product variation
    if(empty(get_post( $variation_id ))){
        $variation_id = wp_insert_post( $variation_post );

        // Variation is created. Updating
    }else{
        $variation_post['ID'] = $variation_id;
        $variation_id = wp_update_post( $variation_post );
    }

    // Get an instance of the WC_Product_Variation object
    $variation = new WC_Product_Variation( $variation_id );

    //This is an array of input attributes in the form: array("Color"=>"Navy", "Size"=>"25")
    $theseAttributes = $variation_data['attributes'];

    //


    //This is the final list of attributes that we are calculating below.
    $theseAttributesCalculated = array();

    //logg("Want to add these attributes to the variation: ".print_r($theseAttributes, true));

    $existingTax = wc_get_attribute_taxonomies();

    foreach ($theseAttributes as $name => $value)
    {
        if (strlen($name) == 0 || strlen($value) == 0)
        {
            //logg("Attribute array had a blank value for product variant ".$sku.': '.print_r($theseAttributes, true));
            return "Attribute array had a blank value.";
        }

        $tax = '';
        $slug = '';

        //Look for an existing taxonomy to match this attribute's $name
        //$thistax->attribute_name = slug of the taxonomy
        //$thistax->attribute_label = name of the taxonomy

        foreach ($existingTax as $thistax)
        {
            if ($thistax->attribute_label == $name)
            {
                $slug = $thistax->attribute_name;
                $tax = wc_attribute_taxonomy_name($slug);
                break;
            }
        }

        if (empty($tax))
        {
            $slug = wc_sanitize_taxonomy_name($name);
            //Taxonomy not found, so create it...
            if (create_global_attribute($name, $slug) > 0)
            {
                $tax = wc_attribute_taxonomy_name($slug);
            }
            else
            {
                //logg("Unable to create new attribute taxonomy ".$slug." for attribute ".$name."found in variable product ".$sku);
                continue;
            }
        }


        //logg("Want to add attribute ".$name. " value ".$value. " which is term ".$term_slug." (".$termId.") to post ".$parentID);

        $term = get_attribute_term($value, $tax);


        if ($term['id'])
        {
            // Set/save the attribute data in the product variation
            $theseAttributesCalculated[$tax] = $term['slug'];
        }
        else
        {
            //logg("Warning! Unable to create / get the attribute ".$value." in Taxonomy ".$tax);
        }
    }

    //logg("Finished gathering. Results: ".print_r($theseAttributesCalculated, true));

    $variation->set_attributes($theseAttributesCalculated);

    // SKU
    if( ! empty( $variation_data['sku'] ) )
        $variation->set_sku( $variation_data['sku'] );

    // Prices
    if( empty( $variation_data['sale_price'] ) ){
        $variation->set_price( $variation_data['regular_price'] );
    } else {
        $variation->set_price( $variation_data['sale_price'] );
        $variation->set_sale_price( $variation_data['sale_price'] );
    }
    $variation->set_regular_price( $variation_data['regular_price'] );

    // Stock
//    if( ! empty($variation_data['stock_quantity']) ){
//        $variation->set_stock_quantity( $variation_data['stock_quantity'] );
//        $variation->set_manage_stock(true);
//        $variation->set_stock_status('');
//    } else {
//        $variation->set_manage_stock(false);
//    }

    $variation->set_stock_quantity( $variation_data['stock_quantity'] );

    $variation->set_manage_stock(true);
    $variation->set_stock_status(true);

    $variation->set_backorders('notify');

    if ($variation_data['stock_quantity'] > 0) {

        $delivery = 0;

    } else {

        $delivery = $variation_data['delivery_time'];

    }

    $deli = ['key' => 'ceske_sluzby_dodaci_doba',
        'value' =>  $delivery,
    ];


    $meta_data = [$deli];

    foreach($meta_data as $data => $singleMeta){

        if($variation->meta_exists($singleMeta['key'])){

            $variation->update_meta_data($singleMeta['key'], $singleMeta['value']);

        }else{

            $variation->add_meta_data($singleMeta['key'], $singleMeta['value']);

        }
    }


    $variation->set_weight(''); // weight (reseting)


    $adminPath = $_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/catalog/' . $product->get_slug() . '_variation_'.$variation_data['original_id'].'.jpg';

    // Variation image check
    $newPicture = false;
    if ($variation->get_image_id('') != 0) {

        $mainImage = get_post($variation->get_image_id(''));
        $mainImageMetaData = wp_get_attachment_metadata($mainImage->ID, true);

        $path = BASE_PATH . '/wp-content/uploads/' . $mainImageMetaData['file'];
        $finalPath = substr($path, 0, strrpos($path, '.'));

        $storePath = $finalPath.'-420x480.jpg';
//
//        echo $storePath.' - ';
//        echo $adminPath.'<br><br>';
        // New Image
        if ((file_exists($storePath) && !file_exists($adminPath)) || (!file_exists($storePath) && !file_exists($adminPath)) || (!file_exists($path) && !file_exists($adminPath))){

            $deleted_item = wp_delete_attachment($mainImage->ID, true);
            $variation->set_image_id(0);
            $newPicture = false;

            echo 'No new';

            // Different Image
        }elseif((file_exists($storePath) && file_exists($adminPath)) && (md5_file($storePath) != md5_file($adminPath)) || !file_exists($storePath) || (!file_exists($path) && file_exists($adminPath))){

            $deleted_item = wp_delete_attachment($mainImage->ID, true);
            $variation->set_image_id(0);
            $newPicture = true;

            echo 'Diff';

        }else{

            echo 'Same';
        }


    } elseif(file_exists($adminPath)) {
        // No Image - New Uploaded

        $newPicture = true;

    }


    if ($newPicture && file_exists($adminPath)) {

        $filename = pathinfo($adminPath, PATHINFO_FILENAME);

        $attachID = copy_image($filename, $variation_id);

        set_post_thumbnail($variation_id, $attachID);
        $variation->set_image_id($attachID);

    }

//    print_r($variation);

    $variationID = $variation->save(); // Save the data

    if ($variationID <= 0) return "Unable to create product variation!";

    return $variationID;
}



// global attribute
function create_global_attribute($name, $slug)
{

    $taxonomy_name = wc_attribute_taxonomy_name( $slug );

    if (taxonomy_exists($taxonomy_name))
    {
        return wc_attribute_taxonomy_id_by_name($slug);
    }

    //logg("Creating a new Taxonomy! `".$taxonomy_name."` with name/label `".$name."` and slug `".$slug.'`');

    $attribute_id = wc_create_attribute( array(
        'name'         => $name,
        'slug'         => $slug,
        'type'         => 'select',
        'order_by'     => 'menu_order',
        'has_archives' => false,
    ) );

    //Register it as a wordpress taxonomy for just this session. Later on this will be loaded from the woocommerce taxonomy table.
    register_taxonomy(
        $taxonomy_name,
        apply_filters( 'woocommerce_taxonomy_objects_' . $taxonomy_name, array( 'product' ) ),
        apply_filters( 'woocommerce_taxonomy_args_' . $taxonomy_name, array(
            'labels'       => array(
                'name' => $name,
            ),
            'hierarchical' => true,
            'show_ui'      => false,
            'query_var'    => true,
            'rewrite'      => false,
        ) )
    );

    //Clear caches
    delete_transient( 'wc_attribute_taxonomies' );

    return $attribute_id;
}



//$rawDataAttributes must be in the form of array("Color"=>array("blue", "red"), "Size"=>array(12,13,14),... etc.)
function generate_attributes_list_for_product($rawDataAttributes, $variation = false)
{
    $attributes = array();

    $pos = 0;

    foreach ($rawDataAttributes as $name => $values)
    {
        if (empty($name) || empty($values)) continue;

        if (!is_array($values)) $values = array($values);

        $attribute = new WC_Product_Attribute();
        $attribute->set_id( 0 );
        $attribute->set_position($pos);
        $attribute->set_visible( true );
        $attribute->set_variation( $variation );

        $pos++;

        //Look for existing attribute:
        $existingTaxes = wc_get_attribute_taxonomies();

        //attribute_labels is in the format: array("slug" => "label / name")
        $attribute_labels = wp_list_pluck( $existingTaxes, 'attribute_label', 'attribute_name' );
        $slug = array_search( $name, $attribute_labels, true );

        if (!$slug)
        {
            //Not found, so create it:
            $slug = wc_sanitize_taxonomy_name($name);
            $attribute_id = create_global_attribute($name, $slug);
        }
        else
        {
            //Otherwise find it's ID
            //Taxonomies are in the format: array("slug" => 12, "slug" => 14)
            $taxonomies = wp_list_pluck($existingTaxes, 'attribute_id', 'attribute_name');

            if (!isset($taxonomies[$slug]))
            {
                //logg("Could not get wc attribute ID for attribute ".$name. " (slug: ".$slug.") which should have existed!");
                continue;
            }

            $attribute_id = (int)$taxonomies[$slug];
        }

        $taxonomy_name = wc_attribute_taxonomy_name($slug);

        $attribute->set_id( $attribute_id );
        $attribute->set_name( $taxonomy_name );
        $attribute->set_options($values);

        $attributes[] = $attribute;
    }

    return $attributes;
}




function get_attribute_term($value, $taxonomy)
{
    //Look if there is already a term for this attribute?
    $term = get_term_by('name', $value, $taxonomy);

    if (!$term)
    {
        //No, create new term.
        $term = wp_insert_term($value, $taxonomy);
        if (is_wp_error($term))
        {
            //logg("Unable to create new attribute term for ".$value." in tax ".$taxonomy."! ".$term->get_error_message());
            return array('id'=>false, 'slug'=>false);
        }
        $termId = $term['term_id'];
        $term_slug = get_term($termId, $taxonomy)->slug; // Get the term slug
    }
    else
    {
        //Yes, grab it's id and slug
        $termId = $term->term_id;
        $term_slug = $term->slug;
    }

    return array('id'=>$termId, 'slug'=>$term_slug);
}