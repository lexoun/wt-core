<?php

$cross_array = "";
$up_array = "";

if (isset($cross_selling) && $cross_selling != "" && $cross_selling != "N;") {

    $cross_array = $cross_selling;

}

if (isset($up_selling) && $up_selling != "" && $up_selling != "N;") {

    $up_array = $up_selling;

}

if ($type == 'variable') {
    $instock = 0;
}

if ($instock > 0) {

    $delivery = 0;

} else {

    $delivery = $_POST['delivery_time'];

}

$data_spamall = [

    "title" => $_POST['productname'],
    "slug" => $seoslug,

    "permalink" => "https://www.spamall.cz/produkt/" . $seoslug . "/",
    "type" => $type,

    "description" => $_POST['description'],
    "short_description" => $_POST['short_description'],
    "price" => $_POST['spamall_price'],
    "regular_price" => $_POST['spamall_price'],

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
        $spamall_category_array,
    ],

    'images' => $images,

    'custom_meta' => [
        '_crosssell_ids' => $cross_array,
        '_upsell_ids' => $up_array,
        'ceske_sluzby_dodaci_doba' => $delivery,
    ],

    'variations' => $variations_array,

];

$data_spamall['attributes'] = array_merge((array)$data_spamall['attributes'], (array)$attributes);

$data_spamall['custom_meta'] = array_merge((array)$data_spamall['custom_meta'], (array)$attribute_text);

if ($ean_main != $product['ean']) {
    $data_spamall['sku'] = $ean_main;
} else {

    $data_spamall['sku'] = $old_ean;

}

$cross_array = "";
$up_array = "";

if (isset($cross_selling) && $cross_selling != "" && $cross_selling != "N;") {

    $cross_array = $cross_selling;

}

if (isset($up_selling) && $up_selling != "" && $up_selling != "N;") {

    $up_array = $up_selling;

}

if ($type == 'variable') {
    $instock = 0;
}

if ($instock > 0) {

    $delivery = 0;

} else {

    $delivery = $_POST['delivery_time'];

}

$data = [

    "title" => $_POST['productname'],
    "slug" => $seoslug,

    "type" => $type,

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

    'images' => $images,

    'custom_meta' => [
        '_crosssell_ids' => $cross_array,
        '_upsell_ids' => $up_array,
        'ceske_sluzby_dodaci_doba' => $delivery,
    ],

    'variations' => $variations_array,

];

$data['attributes'] = array_merge((array)$data['attributes'], (array)$attributes);

$data['custom_meta'] = array_merge((array)$data['custom_meta'], (array)$attribute_text);

if ($ean_main != $product['ean']) {
    $data['sku'] = $ean_main;
} else {

    $data['sku'] = $old_ean;

}
