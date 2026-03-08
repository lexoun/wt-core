<?php

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define( 'SHORTINIT', true );
define('BASE_PATH', '/mnt/data/accounts/w/wellnesstrade/data/www/saunahouse.cz');

require_once BASE_PATH . '/wp-load.php';

require_once BASE_PATH . '/wp-includes/user.php';
require_once BASE_PATH . '/wp-includes/l10n.php';
require_once BASE_PATH . '/wp-includes/class-wp-widget.php';
require_once BASE_PATH . '/wp-includes/class-wp-embed.php';
require_once BASE_PATH . '/wp-includes/class-wp-user.php';
require_once BASE_PATH . '/wp-includes/class-wp-query.php';
require_once BASE_PATH . '/wp-includes/embed.php';
require_once BASE_PATH . '/wp-includes/rest-api.php';
require_once BASE_PATH . '/wp-includes/theme.php';
require_once BASE_PATH . '/wp-includes/post.php';
require_once BASE_PATH . '/wp-includes/general-template.php';
require_once BASE_PATH . '/wp-includes/class-wp-post.php';
require_once BASE_PATH . '/wp-includes/category-template.php';
require_once BASE_PATH . '/wp-includes/taxonomy.php';
require_once BASE_PATH . '/wp-includes/shortcodes.php';
require_once BASE_PATH . '/wp-includes/media.php';
require_once BASE_PATH . '/wp-includes/revision.php';
require_once BASE_PATH . '/wp-includes/link-template.php';
require_once BASE_PATH . '/wp-includes/comment.php';

$GLOBALS['wp_embed'] = new WP_Embed();
$GLOBALS['wp_plugin_paths'] = array();
wp_plugin_directory_constants( );

require_once BASE_PATH . '/wp-content/plugins/woocommerce/woocommerce.php';

require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";


$wooId = 97058;

$product_query = $mysqli->query("SELECT *, SUM(s.instock) as total_instock FROM products p LEFT JOIN products_stocks s ON s.product_id = p.id WHERE id = '4694'") or die($mysqli->error);

$product = mysqli_fetch_array($product_query);

    $variations_array = array();
    $parent_attributes = array();
    $options = array();
    $attributes = array();
    $attribute_text = array();

    $specifications_query = $mysqli->query("SELECT name, value FROM products_specifications WHERE product_id = '" . $product['id'] . "'") or die($mysqli->error);

    while ($specification = mysqli_fetch_array($specifications_query)) {

        $insert_attribute = array(
            'name' => $specification['name'],
            'position' => '0',
            'visible' => true,
            'variation' => false,
            'options' => [

                $specification['value'],
            ],
        );

        array_push($attributes, $insert_attribute);

        $attribute_text = [
            ['key' => '_specifications_attributes_title',
                'value' => 'Základní informace'], [
                'key' => '_specifications_display_attributes',
                'value' => 'yes',

            ],
        ];

    }



    $manufacturer_query = $mysqli->query("SELECT delivery_time, manufacturer FROM products_manufacturers WHERE id = '" . $product['manufacturer'] . "'");
        if (mysqli_num_rows($manufacturer_query) > 0) {

            $manufacturer = mysqli_fetch_array($manufacturer_query);

            $insert_attribute = array(
                'name' => 'Výrobce',
                'position' => '0',
                'visible' => true,
                'variation' => false,
                'options' => [

                    $manufacturer['manufacturer'],
                ],
            );

            array_push($attributes, $insert_attribute);

        }

    $instock = $product['total_instock'];
    if ($instock > 0) {

        $delivery = 0;

    } else {

        if (isset($manufacturer['delivery_time']) && $manufacturer['delivery_time'] != "") {

            $delivery = $manufacturer['delivery_time'];

        } else {

            $delivery = $product['delivery_time'];

        }

    }


    $this_category_array = array();
    $category_query = $mysqli->query("SELECT category FROM products_sites_categories WHERE product_id = '" . $product['id'] . "' AND site = 'saunahouse'") or die($mysqli->error);

    while ($category = mysqli_fetch_array($category_query)) {

        $this_cat = array('id' => $category['category']);

        array_push($this_category_array, $this_cat);

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
        'value' => $delivery,
    ];

    $data_meta = [$cross, $ups, $deli];
    $data_meta = array_merge((array)$data_meta, (array)$attribute_text);


    if ($product['availability'] != 3) {

        $setCatalogVisibility = 'visible';
        $setStockStatus = true;

    } elseif (isset($product['availability']) && $product['availability'] == 3) {

        $setCatalogVisibility = 'hidden';
        $setStockStatus = false;

    }

    $pr = new WC_Product($wooId);

    $pr->set_name($product['productname']);
    $pr->set_slug($product['seourl']);
    $pr->set_description($product['description']);
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

    $pr->set_category_ids($this_category_array);

    $pr->set_attributes($attributes);
    $pr->set_meta_data($data_meta);
    $pr->set_sku($product['ean']);


// CHECK IF IMAGE ON SHOP IS DIFFERENT

$newPicture = false;
if($pr->get_image_id() != 0){

    $mainImage = get_post($pr->get_image_id());
    $mainImageMetaData = wp_get_attachment_metadata($mainImage->ID, true);

    $storePath = BASE_PATH . '/wp-content/uploads/'.$mainImageMetaData['file'];
    $adminPath = $_SERVER['DOCUMENT_ROOT'] .'/data/stores/images/big/'.$product['seourl'].'.jpg';

    if((file_exists($storePath) && file_exists($adminPath)) && (md5_file($storePath) != md5_file($adminPath)))
    {
        echo 'Different Image<br>';

        $deleted_item = wp_delete_attachment($mainImage->ID, true);
        $newPicture = true;

    }
    else
    {
        echo 'Same Image<br>';
    }

}else{

    echo 'No image<br>';
    $newPicture = true;

}


if($newPicture){

    require_once( BASE_PATH . '/wp-includes/kses.php' );
    require_once( BASE_PATH . '/wp-includes/blocks.php' );
    require_once( BASE_PATH . '/wp-includes/class-wp-block-parser.php' );
    require_once( BASE_PATH . '/wp-includes/class-wp-rewrite.php' );

    $filename = $product['seourl'].'.jpg';

    $upload_dir = wp_upload_dir();
    if ( wp_mkdir_p( $upload_dir['path'] ) ) {
        $file = $upload_dir['path'] . '/' . $filename;
    }else {
        $file = $upload_dir['basedir'] . '/' . $filename;
    }

    $wp_rewrite = new WP_Rewrite();

    $attachment = array(
        'post_title' => sanitize_file_name( $filename ),
        'post_date' => date('Y-m-d H:i:s'),
        'post_author' => 1,
        'post_content' => $filename,
        'post_status' => 'inherit',
        'post_parent' => $wooId,
        'post_mime_type' => 'image/jpeg',
        'post_guid' => $upload_dir['url'].'/'.$filename,
    );

    $attachId = wp_insert_attachment( $attachment, $file, $wooId , true);

    $sizeSingle = $_SERVER['DOCUMENT_ROOT'] .'/data/stores/images/single/'.$filename;
    $sizeCatalog = $_SERVER['DOCUMENT_ROOT'] .'/data/stores/images/catalog/'.$filename;
    $sizeThumbnail = $_SERVER['DOCUMENT_ROOT'] .'/data/stores/images/thumbnail/'.$filename;
    $sizeGaleryThumbnail = $_SERVER['DOCUMENT_ROOT'] .'/data/stores/images/small/'.$filename;

    if(file_exists($sizeSingle)){ copy($sizeSingle,BASE_PATH . '/wp-content/uploads/'.$upload_dir['subdir'].'/'.$product['seourl'].'-750x750.jpg'); };
    if(file_exists($sizeCatalog)){ copy($sizeCatalog,BASE_PATH . '/wp-content/uploads/'.$upload_dir['subdir'].'/'.$product['seourl'].'-600x675.jpg'); };
    if(file_exists($sizeThumbnail)){ copy($sizeThumbnail,BASE_PATH . '/wp-content/uploads/'.$upload_dir['subdir'].'/'.$product['seourl'].'-300x300.jpg'); };
    if(file_exists($sizeGaleryThumbnail)){ copy($sizeGaleryThumbnail,BASE_PATH . '/wp-content/uploads/'.$upload_dir['subdir'].'/'.$product['seourl'].'-100x100.jpg'); };

    $attachMeta = array(
        'width' => 1000,
        'height' => 1000,
        'file' => $upload_dir['subdir'].'/'.$filename,

        'sizes' => array(
            'woocommerce_single' => array(
                'width' => 750,
                'height' => 750,
                'mime-type' => 'image/jpeg',
                'file' => $product['seourl'].'-750x750.jpg',
            ),
            'shop_single' => array(
                'width' => 750,
                'height' => 750,
                'mime-type' => 'image/jpeg',
                'file' => $product['seourl'].'-750x750.jpg',
            ),
            'shop_catalog' => array(
                'width' => 600,
                'height' => 675,
                'mime-type' => 'image/jpeg',
                'file' => $product['seourl'].'-600x675.jpg',
            ),
            'woocommerce_thumbnail' => array(
                'width' => 300,
                'height' => 300,
                'mime-type' => 'image/jpeg',
                'file' => $product['seourl'].'-300x300.jpg',
            ),
            'woocommerce_gallery_thumbnail' => array(
                'width' => 100,
                'height' => 100,
                'mime-type' => 'image/jpeg',
                'file' => $product['seourl'].'-100x100.jpg',
            ),
            'shop_thumbnail' => array(
                'width' => 100,
                'height' => 100,
                'mime-type' => 'image/jpeg',
                'file' => $product['seourl'].'-100x100.jpg',
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

    wp_update_attachment_metadata( $attachId, $attachMeta );
    set_post_thumbnail($wooId, $attachId);

    $pr->set_image_id($attachId);

}

//$pr->save();

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$total_time = round($finish - $start, 4);

echo 'PHP '.PHP_VERSION.' | Page generated in ' . $total_time . ' seconds.';