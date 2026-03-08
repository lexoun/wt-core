<?php

define('PRODUCT_IMAGE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/data/stores/images');

// all product images sizes / jpg extension
$productImageSizes = array('mini', 'small', 'single', 'catalog', 'thumbnail', 'big');


// all allowed image extensions
$image_extensions = array('png', 'jpg', 'JPG', 'jpeg', 'JPEG', 'gif', 'HEIC', 'jfif', 'webp');


function extList($image_extensions){

    $extList = '';
    foreach($image_extensions as $extension){ $extList .= $extList ? ','.$extension : $extension; }

    return $extList;

}


define('CALENDAR_ID', 'kcie94kfi9absq10j3d8uijis8@group.calendar.google.com');

$currentDate = new DateTime();

// all demands states
$demand_statuses = array(
    0 => array(
        'id' => '1',
        'name' => 'Nezpracovaná'
    ),
    1 => array(
        'id' => '3',
        'name' => 'V řešení'
    ),
    2 => array(
        'id' => '2',
        'name' => 'Zhotovené nabídky'
    ),
    3 => array(
        'id' => '7',
        'name' => 'Odložené'
    ),
    4 => array(
        'id' => '6',
        'name' => 'Stornované'
    ),
    5 => array(
        'id' => '12',
        'name' => 'Prodané'
    ),
    6 => array(
        'id' => '14',
        'name' => 'Neobjednaná vířivka'
    ),
    7 => array(
        'id' => '15',
        'name' => 'Nová realizace'
    ),
    8 => array(
        'id' => '4',
        'name' => 'Realizace'
    ),
    9 => array(
        'id' => '8',
        'name' => 'Nedokončená'
    ),
    10 => array(
        'id' => '13',
        'name' => 'Dokončená'
    ),
    11 => array(
        'id' => '5',
        'name' => 'Hotová'
    ),
);





// phone prefixes
$phone_prefixes = array(
    0 => array(
        'id' => 'CZE',
        'name' => '+420'
    ),
    1 => array(
        'id' => 'SVK',
        'name' => '+421'
    ),
    2 => array(
        'id' => 'DEU',
        'name' => '+49'
    ),
    3 => array(
        'id' => 'AUT',
        'name' => '+43'
    )
);








// all demands states
//$accessories_suppliers = array(
//    0 => array(
//        'id' => '1',
//        'name' => 'IQue',
//        'mail' => 'becher.filip@gmail.com'
//    ),
//    1 => array(
//        'id' => '2',
//        'name' => 'Spa Plus',
//        'mail' => 'becher.filip@gmail.com'
//    )
//);
