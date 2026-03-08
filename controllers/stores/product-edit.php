<?php

include_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";

function shopConnection($shop) {

    // todo try cache
    return new Automattic\WooCommerce\Client(
        $shop['url'],
        $shop['secret_key'],
        $shop['secret_code'],
        [
            'wp_api' => true,
            'version' => 'wc/v3',
            'query_string_auth' => true,

        ]
    );

}

function removeImage($shop, $imageId){
    $curl = curl_init();
    curl_setopt_array($curl, array
    (
        CURLOPT_URL => $shop.'/wp-json/wp/v2/media/'.$imageId.'?force=1',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'DELETE',
        CURLOPT_HTTPHEADER => array(
            'Authorization: Basic bGV4b3VuOmYzbDF4bTR4'
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    //print_r($response);
}



function variationImage($all_vari, $product_seourl, $variation_id, $shop_url, $key) {

    // get variation image
    foreach ($all_vari as $vari) {
        if($vari->id == $key){
            $variationImage = $vari->image;
        }
    }

    // delete image
    if (!empty($variationImage->id) && strpos($variationImage->name, 'variation') !== false) {
        removeImage($shop_url, $variationImage->id);
    }

    // new image
    if (file_exists($_SERVER['DOCUMENT_ROOT'] .
        '/data/stores/images/thumbnail/' . $product_seourl . '_variation_'.$variation_id.'.jpg')) {

        return array(
            'src' => 'https://www.wellnesstrade.cz/data/stores/images/big/' . $product_seourl . '_variation_'.$variation_id.'.jpg',
            'position' => 0,
        );

    }

}


function variationStatus($availability): string
{

    if ($availability === 3) {
        return 'private';
    }

     return 'publish';

}

function variationDelivery($product, $variation){

    // set delivery date for "on order"
    if($variation['availability'] == 2){ return 999; }
    if($variation['availability'] == 4){ return 998; }

    // individual delivery time
    if (!empty($product['delivery_time'])) {

        return $product['delivery_time'];

    }

    // manufacturer delivery
    if (!empty($product['manufacturer_delivery'])) {

        return $product['manufacturer_delivery'];

    }

    return 14;


}

function updateProduct($updatedProduct){

    global $mysqli;

    if (!empty($updatedProduct['aggregate_id'])) {


        $shops_query = $mysqli->query("SELECT 
                *, p.id as id 
            FROM shops s, products_sites p 
            WHERE p.product_id = '" . $updatedProduct['aggregate_id'] . "' AND p.site = s.slug AND s.slug = '".$updatedProduct['shop']."'
        ") or die($mysqli->error);
        $shop = mysqli_fetch_array($shops_query);

        $echoes = $shop['name'] . ' ' . $shop['site_id'] . '<br><br>';


        // connect to targeted shop
        $woocommerce_connection = shopConnection($shop);


        $product_query = $mysqli->query("SELECT *, SUM(s.instock) as total_instock FROM products p LEFT JOIN products_stocks s ON s.product_id = p.id WHERE id = '" . $updatedProduct['aggregate_id'] . "'") or die($mysqli->error);
        $product = mysqli_fetch_array($product_query);

        $images = array();

        // if some of the product image has changed
        $changeImages = json_decode($updatedProduct['special']);

        if($changeImages->imageChange == 1){


            if(!empty($shop['site_id'])){

            // get all images
            $wooProduct = $woocommerce_connection->get('products/' . $shop['site_id']);

                // delete all images
                if (!empty($wooProduct->images)) {

                    foreach ($wooProduct->images as $key => $image) {

                        // delete single image
                        if (!empty($image->id)) {

                            removeImage($shop['url'], $image->id);

                        }
                    }
                }
            }

            // upload main image
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/thumbnail/' . $product['seourl'] . '.jpg')) {

                $main_image = array(
                    'src' => 'https://www.wellnesstrade.cz/data/stores/images/big/' . $product['seourl'] . '.jpg',
                    'position' => 0,
                );

                array_push($images, $main_image);

            }


            // upload all other images
            $files = glob($_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/thumbnail/' . $product['seourl'] . '_{,[1-9]}[0-9].jpg', GLOB_BRACE);
            if (!empty($files)) {

                $i = 0;
                foreach ($files as $file) {

                    if (file_exists($file)) {

                        $i++;
                        $printfile = basename($file) . PHP_EOL;

                        $next_image = array(
                            'src' => 'https://www.wellnesstrade.cz/data/stores/images/big/' . $printfile,
                            'position' => $i,
                        );

                        array_push($images, $next_image);

                    }
                }
            }
        }

        $attributes = array();
        $attribute_text = array();

        $specifications_query = $mysqli->query("SELECT name, value FROM products_specifications WHERE product_id = '" . $updatedProduct['aggregate_id'] . "'") or die($mysqli->error);

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

        $variations_array = array();
        $parent_attributes = array();

        $options = array();

        $instock = $product['total_instock'];



        if (isset($product['type']) && $product['type'] == 'variable') {

            $instock_vari_query = $mysqli->query("SELECT SUM(instock) as total_instock FROM products_stocks WHERE product_id = '" . $updatedProduct['aggregate_id'] . "'") or die($mysqli->error);
            $instock_vari = mysqli_fetch_array($instock_vari_query);

            $instock = $instock_vari['total_instock'];

            $variations_query = $mysqli->query("SELECT id FROM products_variations WHERE product_id = '" . $updatedProduct['aggregate_id'] . "'") or die($mysqli->error);

            $par_var_name = "";
            while ($variation = mysqli_fetch_array($variations_query)) {

                $var_attributes = array();

                $var_values_query = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $variation['id'] . "'") or die($mysqli->error);
                while ($var_value = mysqli_fetch_array($var_values_query)) {

                    $current_attribute = array(

                        'name' => $var_value['name'],
                        'option' => $var_value['value'],

                    );

                    $par_var_name = $var_value['name'];

                    array_push($var_attributes, $current_attribute);
                    array_push($options, $var_value['value']);

                }

            }

            $parent_attributes_options = array(

                'name' => $par_var_name,
                'position' => '0',
                'visible' => false,
                'variation' => true,
                'options' => $options,
            );

            array_push($parent_attributes, $parent_attributes_options);

        }

        $this_category_array = array();
        $category_query = $mysqli->query("SELECT category FROM products_sites_categories WHERE product_id = '" . $updatedProduct['aggregate_id'] . "' AND site = '" . $shop['slug'] . "'") or die($mysqli->error);

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


        if ($shop['sale_price'] != 0 && $shop['sale_price'] != "") {

            $sale_price = $shop['sale_price'];

        } else {

            $sale_price = "";

        }

        if ($product['pdf'] != '') {

            $description = $product['description'] . '

                  <a href="https://www.wellnesstrade.cz/data/stores/' . $product['pdf'] . '" class="single_pdf_button button alt" target="_blank">Zobrazit prospekt (PDF)</a>';

        } else {

            $description = $product['description'];

        }

        $data_woo = [

            'name' => $product['productname'],
            'slug' => $product['seourl'],

            'permalink' => $shop['url'] . '/produkt/' . $product['seourl'] . '/',
            'type' => $product['type'],

            'description' => $description,
            'short_description' => $product['short_description'],
            'price' => $product['price'],
            'regular_price' => $product['price'],

            'sale_price' => $sale_price,

            'manage_stock' => true,
            'stock_quantity' => $instock,
            'in_stock' => true,
            'backorders' => 'notify',
            'backorders_allowed' => true,
            'backordered' => false,

            'weight' => $product['weight'],
            'dimensions' => [

                'length' => $product['length'],
                'width' => $product['width'],
                'height' => $product['height'],

            ],

            'attributes' => $parent_attributes,
            'categories' => $this_category_array,

        ];

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

        if (isset($product['availability']) && $product['availability'] == 3) {

            $data_woo['catalog_visibility'] = 'hidden';
            $data_woo['in_stock'] = 'false';
            $data_woo['backorders_allowed'] = 'false';

        } else {
            $data_woo['catalog_visibility'] = 'visible';

        }

        // add images only of something changes
        if (!empty($images)) {
            $data_woo['images'] = $images;
        }

        $data_woo['attributes'] = array_merge((array)$data_woo['attributes'], (array)$attributes);

        $data_woo['meta_data'] = array_merge((array)$data_meta, (array)$attribute_text);

        $data_woo['sku'] = $product['ean'];


        //print_r($data_woo);
        if (isset($shop['site_id']) && $shop['site_id'] == 0) {

            try {

                echo 'woo post';

                $newproduct = $woocommerce_connection->post('products', $data_woo);

                if (isset($newproduct) && isset($newproduct->id) && $newproduct->id != 0) {

                    $update_id = $mysqli->query("UPDATE products_sites SET site_id = '" . $newproduct->id . "' WHERE id = '" . $shop['id'] . "' AND product_id = '" . $updatedProduct['aggregate_id'] . "'");

                    // todo if nově přidán, tak přidat i obrázek, i pokud change image je nula
                    $echoes = $echoes . 'PRODUKT NOVĚ PŘIDÁN.<br><br>';

                }

            } catch (Automattic\WooCommerce\HttpClient\HttpClientException $e) {

                // print_r($e->getResponse());

                if ($e->getCode() == 400) {

                    $response = $e->getResponse();

                    $responsebody = $response->getBody();

                    $responsenewbody = json_decode($responsebody, true);

                    // print_r($responsenewbody);

                    $product_id = $responsenewbody['data']['resource_id'];

                    // echo $product_id;

                    echo 'woo update';

                    $woocommerce_connection->put('products/' . $product_id, $data_woo);

                    $update_id = $mysqli->query("UPDATE products_sites SET site_id = '$product_id' WHERE id = '" . $shop['id'] . "'");

                    $echoes = $echoes . 'PRODUKT BYL NA WEBU ALE NEMĚL PŘIŘAZENÉ PRODUCT_ID. PŘIŘAZENO PRODUCT ID A AKTUALIZOVÁN PRODUKT.<br><br>';

                }
            }

        } else {

            print_r($data_woo);

            try {

                $updateproduct = $woocommerce_connection->put('products/' . $shop['site_id'], $data_woo);

                if (isset($updateproduct)) {

                    $echoes .= 'PRODUKT AKTUALIZOVAN.<br><br>';

                }

            } catch (Automattic\WooCommerce\HttpClient\HttpClientException $e) {

                //print_r($e->getResponse());

                if ($e->getCode() == 400) {

                    echo 'woo post 2';

                    $newproduct = $woocommerce_connection->post('products', $data_woo);


                    if (isset($newproduct) && isset($newproduct->id) && $newproduct->id != 0) {

                        $update_id = $mysqli->query("UPDATE products_sites SET site_id = '" . $newproduct->id . "' WHERE id = '" . $shop['id'] . "' AND product_id = '" . $updatedProduct['aggregate_id'] . "'");

                    }

                    $echoes = $echoes . 'PRODUKT MĚL SITE ID ALE NEBYL NA WEBU. NOVĚ PŘIDÁN.<br><br>';

                }

            }


           print_r($updateproduct);

        }

        //print_r($data_woo);


        /* ----- UPDATE VARIANT ----- */
        if (isset($product['type']) && $product['type'] == 'variable') {

            $variations_array = array();
            $update_array = array();
            $delete_array = array();

            $parent_attributes = array();

            $options = array();
            $variation_eans = array();

            // get variants
            $all_vari = $woocommerce_connection->get('products/' . $shop['site_id'] . '/variations');

            $remove_ids = array();
            $actual_vari_ids = array();
            $get_arrays = array();
            $actual_arrays = array();

            foreach ($all_vari as $vari) {

                $varid = $vari->id;
                $get_arrays[$varid] = $vari->sku;

            }

            $update_variations = array();

            $variations_query = $mysqli->query("SELECT * FROM products_variations WHERE product_id = '" . $updatedProduct['aggregate_id'] . "'") or die($mysqli->error);

            $par_var_name = "";
            while ($variation = mysqli_fetch_array($variations_query)) {

                $instock_query = $mysqli->query("SELECT SUM(instock) as total_instock FROM products_stocks WHERE product_id = '" . $updatedProduct['aggregate_id'] . "' AND variation_id = '" . $variation['id'] . "'") or die($mysqli->error);
                $instock = mysqli_fetch_array($instock_query);

                $site_id_query = $mysqli->query("SELECT site_id FROM products_variations_sites WHERE site = '" . $shop['slug'] . "' AND variation_id = '" . $variation['id'] . "'");
                $site_id = mysqli_fetch_array($site_id_query);

                $key = array_search($variation['ean'], $get_arrays);

                if ($key != $site_id['site_id']) {

                    $echoes .= '<br><br><br><br>EAN JE V SHOPU, NESEDI ID V DB - ' . $key . '<br><br><br><br>';

                    $update = $mysqli->query("UPDATE products_variations_sites SET site_id = '$key' WHERE  site = '" . $shop['slug'] . "' AND variation_id = '" . $variation['id'] . "'");

                    $site_id['site_id'] = $key;

                }


                $variation_eans[] = $variation['ean'];
                $actual_vari_ids[] = $site_id['site_id'];

                $act_id = $site_id['site_id'];

                $actual_arrays[$act_id] = $variation['ean'];

                echo $variation['id'] . 'eee';



                /* NEMÁ!! PŘIŘAZENÉ SITE ID */
                if (isset($site_id['site_id']) && $site_id['site_id'] == 0) {

                    $var_attributes = array();

                    $var_values_query = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $variation['id'] . "'") or die($mysqli->error);
                    while ($var_value = mysqli_fetch_array($var_values_query)) {

                        $current_attribute = array(

                            'name' => $var_value['name'],
                            'option' => $var_value['value'],

                        );

                        $par_var_name = $var_value['name'];

                        $var_attributes[] = $current_attribute;
                        $options[] = $var_value['value'];

                    }


                    if ($variation['sale_price'] != 0 && $variation['sale_price'] != "") {

                        $sale_price = $variation['sale_price'];

                    } else {

                        $sale_price = "";

                    }


                    $final_stock = $instock['total_instock'];

                    $variationDelivery = variationDelivery($product, $variation);

                    if($variationDelivery === 998){ $final_stock = 0; }


                    $current_variation = array(

                        'sku' => $variation['ean'],

                        'price' => $variation['price'],
                        'regular_price' => $variation['price'],

                        'sale_price' => $sale_price,

                        'manage_stock' => true,
                        'stock_quantity' => $final_stock,
                        'in_stock' => true,
                        'backorders' => 'notify',
                        'purchaseable' => true,
                        'backorders_allowed' => true,
                        'weight' => $variation['weight'],
                        'dimensions' => array(
                            'length' => $variation['length'],
                            'width' => $variation['width'],
                            'height' => $variation['height'],
                            'unit' => 'cm',
                        ),

                        'attributes' => $var_attributes,
                        'status' => variationStatus($variation['availability']),

                    );

                    $deli = [
                        'key' => 'ceske_sluzby_dodaci_doba',
                        'value' => $variationDelivery,
                    ];


                    $data_meta = [$deli];
                    $current_variation['meta_data'] = $data_meta;

                    // if some of the other images is changed
                    if($changeImages->variationImages == 1) {

                        $var_image = variationImage($all_vari, $product['seourl'], $variation['id'], $shop['url'], $site_id['site_id']);

                        if (!empty($var_image)) {
                            $current_variation['image'] = $var_image;
                        }

                    }


                    $variations_array[] = $current_variation;

                    // print_r($current_variation);

                    try {

                        echo 'vari update';
                        $updateproduct = $woocommerce_connection->post('products/' . $shop['site_id'] . '/variations/', $current_variation);

                        //print_r($updateproduct);

                        if (isset($updateproduct) && isset($updateproduct['id']) && $updateproduct['id'] != 0) {

                            $update_id = $mysqli->query("UPDATE products_variations_sites SET site_id = '" . $updateproduct['id'] . "' WHERE site = '" . $shop['slug'] . "' AND variation_id = '" . $variation['id'] . "'");

                            $echoes .= 'VARIANTA NOVE PRIDANA.<br>';

                        }


                    } catch (Automattic\WooCommerce\HttpClient\HttpClientException $e) {

                        if ($e->getCode() == 400) {

                            $response = $e->getResponse();

                            $responsebody = $response->getBody();

                            $responsenewbody = json_decode($responsebody, true);

                            //print_r($responsenewbody);

                            $product_id = $responsenewbody['data']['resource_id'];

                            //echo $product_id;

                            echo 'vari update 2';
                            $woocommerce_connection->put('products/' . $shop['site_id'] . '/variations/' . $product_id, $current_variation);

                            $update_id = $mysqli->query("UPDATE products_variations_sites SET site_id = '$product_id' WHERE site = '" . $shop['slug'] . "' AND variation_id = '" . $variation['id'] . "'");

                            $echoes = $echoes . 'VARIANTA BYLA NA WEBU ALE NEMĚL PŘIŘAZENÉ PRODUCT_ID. PŘIŘAZENO PRODUCT ID A AKTUALIZOVÁN PRODUKT.<br>';

                        }

                    }

                /* MÁ!!! PŘIŘAZENÉ SITE ID */
                } else {

                    $var_attributes = [];

                    $var_values_query = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $variation['id'] . "'") or die($mysqli->error);
                    while ($var_value = mysqli_fetch_array($var_values_query)) {

                        $current_attribute = array(

                            'name' => $var_value['name'],
                            'option' => $var_value['value'],

                        );

                        $par_var_name = $var_value['name'];

                        $var_attributes[] = $current_attribute;
                        $options[] = $var_value['value'];

                    }


                    if ($variation['sale_price'] != 0 && $variation['sale_price'] != "") {

                        $sale_price = $variation['sale_price'];

                    } else {

                        $sale_price = "";

                    }

                    $final_stock = $instock['total_instock'];

                    $variationDelivery = variationDelivery($product, $variation);

                    if($variationDelivery === 998){ $final_stock = 0; }

                    $current_variation = array(

                        "id" => $site_id['site_id'],
                        "sku" => $variation['ean'],

                        "price" => $variation['price'],
                        "regular_price" => $variation['price'],
                        "sale_price" => $sale_price,
                        "manage_stock" => true,
                        "stock_quantity" => $final_stock,
                        "in_stock" => true,
                        "backorders" => "notify",
                        "backorders_allowed" => true,
                        "weight" => $variation['weight'],
                        "dimensions" => array(
                            "length" => $variation['length'],
                            "width" => $variation['width'],
                            "height" => $variation['height'],
                            "unit" => "cm",
                        ),

                        "attributes" => $var_attributes,
                        'status' => variationStatus($variation['availability']),

                    );

                    $deli = [
                        'key' => 'ceske_sluzby_dodaci_doba',
                        'value' => $variationDelivery,
                    ];

                    $data_meta = [$deli];
                    $current_variation['meta_data'] = $data_meta;


                    // if some of the other images is changed aa
                    if($changeImages->variationImages == 1) {

                        $var_image = variationImage($all_vari, $product['seourl'], $variation['id'], $shop['url'], $site_id['site_id']);

                        if (!empty($var_image)) {
                            $current_variation['image'] = $var_image;
                        }

                    }

                    $update_variations[] = $current_variation;

                    print_r($current_variation);

                    $echoes .= 'UPDATED CLASSIC <BR>';

                }

            }

            foreach ($all_vari as $vari) {

                if (!in_array($vari->id, $actual_vari_ids)) {

                    $remove_ids[] = $vari->id;

                }

            }

            $data = [
                'update' => $update_variations,
                'delete' => $remove_ids,
            ];

            echo 'vari batch';

            try{

                $result = $woocommerce_connection->post('products/' . $shop['site_id'] . '/variations/batch', $data);

            }  catch (Automattic\WooCommerce\HttpClient\HttpClientException $e) {

                // todo TO BE DONE
                $error = $e->getResponse();

            }

            print_r($result);

        }

        echo $echoes;
        if(isset($get_arrays)){

            /*
            echo '<br>----<br>VARIANTY NA SHOPU<br>';
            print_r($get_arrays);
            echo '<br>----<br><br>----<br>VARIANTY SPRAVNE<br>';
            print_r($actual_arrays);
            echo '<br>----<br><br>----<br>VARIANTY KE SMAZANI<br>';
            print_r($remove_ids);
            */

        }
        echo '<br>----<br><br>-- KONEC SHOPU --<br><br>----<br><br><br><br><br><br><br><br><br>';

        return 'success';
    }

}

function deleteProduct($deleted_product) {

    $response = '';

    global $mysqli;

    $shops_query = $mysqli->query("SELECT * FROM shops WHERE slug = '".$deleted_product['shop']."'") or die($mysqli->error);
    $shop = mysqli_fetch_assoc($shops_query);


    // connect to targeted shop
    $woocommerce_connection = shopConnection($shop);

    try{

        $woocommerce_connection->delete('products/' . $deleted_product['shop_id'], ['force' => true]);

    }  catch (Automattic\WooCommerce\HttpClient\HttpClientException $e) {

        $response .= $e->getResponse();

    }

    return $response;
}


function quantityProduct($quantityProduct) {

    $response = '';

    global $mysqli;

    $shops_query = $mysqli->query("SELECT 
                *, p.id as id 
            FROM shops s, products_sites p 
            WHERE p.product_id = '" . $quantityProduct['aggregate_id'] . "' AND p.site = s.slug AND s.slug = '".$quantityProduct['shop']."'
        ") or die($mysqli->error);
    $shop = mysqli_fetch_array($shops_query);

    $product_query = $mysqli->query("SELECT *, SUM(s.instock) as total_instock 
        FROM products p 
        LEFT JOIN products_stocks s ON s.product_id = p.id 
        WHERE id = '" . $quantityProduct['aggregate_id'] . "'") or die($mysqli->error);
    $product = mysqli_fetch_array($product_query);


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

    $deli = ['key' => 'ceske_sluzby_dodaci_doba',
        'value' => $delivery,
    ];

    $data_meta = [$deli];

    $data_woo = [
        'manage_stock' => true,
        'stock_quantity' => $instock,
        'in_stock' => true,
        'backorders' => 'notify',
        'backorders_allowed' => true,
        'backordered' => false,
    ];

    $data_woo['meta_data'] = array($data_meta);


    if ($product['availability'] != 3 && $product['availability'] != 4) {

        $data_woo['catalog_visibility'] = 'visible';

    } elseif (isset($product['availability']) && ($product['availability'] == 3 || $product['availability'] == 4)) {

        $data_woo['catalog_visibility'] = 'hidden';
        $data_woo['in_stock'] = 'false';
        $data_woo['backorders_allowed'] = 'false';

    }

    // connect to targeted shop
    $woocommerce_connection = shopConnection($shop);


    // update main product
    try {

        $woocommerce_connection->put('products/' . $shop['site_id'], $data_woo);

    } catch (Automattic\WooCommerce\HttpClient\HttpClientException $e) {

        $response .= $e->getResponse();

    }


    // if variations update batch


    if($product['type'] === 'variable'){

        $variations_query = $mysqli->query("SELECT *, v.id as id FROM products_variations v 
        LEFT JOIN products_variations_sites s ON s.product_id = v.product_id AND s.variation_id = v.id 
        WHERE v.product_id = '" . $quantityProduct['aggregate_id'] . "' AND s.site = '".$quantityProduct['shop']."'") or die($mysqli->error);

        $update_variations = array();
        while ($variation = mysqli_fetch_array($variations_query)) {

            $instock_query = $mysqli->query("SELECT SUM(instock) as total_instock FROM products_stocks 
                WHERE product_id = '" . $variation['product_id'] . "' AND variation_id = '" . $variation['id'] . "'") or die($mysqli->error);
            $instock = mysqli_fetch_array($instock_query);

            $current_variation = array(
                "id" => $variation['site_id'],
                "sku" => $variation['ean'],

                "manage_stock" => true,
                "stock_quantity" => $instock['total_instock'],
                "in_stock" => true,
                "backorders" => "notify",
                "purchaseable" => true,
                "backorders_allowed" => true,
            );

            $update_variations[] = $current_variation;

        }

        $data = [
            'update' => $update_variations,
        ];


        try {

            $woocommerce_connection->post('products/' . $shop['site_id'] . '/variations/batch', $data);

        } catch (Automattic\WooCommerce\HttpClient\HttpClientException $e) {

            $response .= $e->getResponse();

        }

    }
    return 'quantity successful'.$response;
}
