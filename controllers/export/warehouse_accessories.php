<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";


$headers = array('ID', 'Název', 'Varianta', 'EAN', 'SKU', 'Hradčanská', 'Slavkov', 'Sklad Krč', 'Sklad Brno');

// Create file and make it writable

$file = fopen($_SERVER['DOCUMENT_ROOT'] . '/data/stores/accessories.csv', 'w');

// Add BOM to fix UTF-8 in Excel

fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

// Headers
// Set ";" as delimiter

fputcsv($file, $headers, ";");

// Rows
// Set ";" as delimiter
$rows = array();
$products_query = $mysqli->query("SELECT p.id as id, v.id as vid, p.productname, p.ean, p.type, p.code, v.ean as vean, v.sku FROM products p LEFT JOIN products_variations v ON p.id = v.product_id GROUP BY v.id, p.id")or die($mysqli->error);
while($product = mysqli_fetch_assoc($products_query)) {

    $row['id'] = $product['id'];
    $row['name'] = $product['productname'];


    $row['variation'] = '';
    if($product['type'] == 'variable'){

        $select = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $product['vid'] . "'") or die($mysqli->error);
        while ($var = mysqli_fetch_array($select)) {

            $row['variation'] .= $var['name'] . ': ' . $var['value'] . ' ';

        }

        $row['ean'] = $product['vean'];
        $row['sku'] = $product['sku'];

    }else{

        $row['ean'] = $product['ean'];
        $row['sku'] = $product['code'];

    }


    $locations_query = $mysqli->query("SELECT * FROM shops_locations l LEFT JOIN products_stocks s ON s.location_id = l.id AND s.product_id = '" . $product['id'] . "' ORDER BY type ASC");

    $i = 0;
    while ($location = mysqli_fetch_array($locations_query)) {


        $row[$location['slug']] = $location['instock'];

    }


    array_push($rows, $row);

}


foreach($rows as $row){


    // todo foreach namísto while... while udělat předtím a lépe specifikovat pro export

    fputcsv($file, $row, ";");
}

// Close file

fclose($file);

// Send file to browser for download

$dest_file = $_SERVER['DOCUMENT_ROOT'] . '/data/stores/accessories.csv';
$file_size = filesize($dest_file);

header("Content-Type: text/csv; charset=utf-8");
header("Content-disposition: attachment; filename=\"accessories.csv\"");
header("Content-Length: " . $file_size);
readfile($dest_file);
