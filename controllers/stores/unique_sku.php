<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";


$check = preg_replace('/\s+/', '', $_GET['check']);

if($_GET['type'] == 'ean'){

    $simple = "ean = '".$check."'";
    $variable = "ean = '".$check."'";

    $simple_get = 'ean';
    $variable_get = 'ean';

}else{

    $simple = "code = '".$check."'";
    $variable = "sku = '".$check."'";

    $simple_get = 'code';
    $variable_get = 'sku';

}


$find_duplicate = $mysqli->query("SELECT id, $simple_get as simple_value FROM products WHERE id != '".$_GET['id']."' AND $simple LIMIT 1")or die($mysqli->error);

$find_variations_duplicate = $mysqli->query("SELECT product_id as id, $variable_get as vari_value FROM products_variations WHERE product_id != '".$_GET['id']."' AND $variable LIMIT 1")or die($mysqli->error);

if(mysqli_num_rows($find_duplicate) > 0){

    $duplicate = mysqli_fetch_assoc($find_duplicate);

    $response = array();
    $response['state'] = "failure";
    $response['duplicate_id'] = $duplicate['id'];

    echo json_encode($response);


}elseif(mysqli_num_rows($find_variations_duplicate) > 0){

    $duplicate = mysqli_fetch_assoc($find_variations_duplicate);

    $response = array();
    $response['state'] = "failure";
    $response['duplicate_id'] = $duplicate['id'];

    echo json_encode($response);

}else{

    $response = array();
    $response['state'] = "success";

    echo json_encode($response);

}