<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_REQUEST['id']) || $_REQUEST['id'] == '') {echo 'Neexistující ID';exit;}

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

require $_SERVER['DOCUMENT_ROOT'] . '/admin/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();

$arrayData = [['Pokožka', 'Množství', 'Nákupní cena za mj.', 'Nákupní cena celkem', 'Kód dodavatele']];

$location_query = $mysqli->query("SELECT * FROM shops_locations WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);
$location = mysqli_fetch_assoc($location_query);

$data_query = $mysqli->query("SELECT p.id as product_id, p.productname, s.instock as instock, 0 as variation_id, p.purchase_price, p.code as code FROM products_stocks s, products p WHERE s.location_id = '" . $_REQUEST['id'] . "' AND s.product_id = p.id AND s.instock > 0 and p.type = 'simple' UNION ALL SELECT p.id as product_id, p.productname, s.instock as instock, v.id as variation_id, v.purchase_price, v.sku as code FROM products_stocks s, products p, products_variations v WHERE s.location_id = '" . $_REQUEST['id'] . "' AND s.product_id = p.id AND s.variation_id = v.id AND v.product_id = p.id AND s.instock > 0 and p.type = 'variable'") or die($mysqli->error);

while ($data = mysqli_fetch_assoc($data_query)) {

    $currentData = [$data['productname'], $data['instock'], $data['purchase_price'], $data['instock'] * $data['purchase_price'], $data['code']];

    array_push($arrayData, $currentData);

}

$spreadsheet->getActiveSheet()->fromArray(
    $arrayData,
    null,
    'A1'
);

$spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
$spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$spreadsheet->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$spreadsheet->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$spreadsheet->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);

$writer = new Xlsx($spreadsheet);
$writer->save($_SERVER['DOCUMENT_ROOT'] . '/admin/storage/warehouse/Export_sklad_' . $location['slug'] . '-' . date('Y-m-d') . '.xlsx');

header('location:http://www.wellnesstrade.cz/admin/storage/warehouse/Export_sklad_' . $location['slug'] . '-' . date('Y-m-d') . '.xlsx');
