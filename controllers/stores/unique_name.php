<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";


function odkazy($title)
{

    static $convertTable = array(

        'á' => 'a', 'Á' => 'A', 'ä' => 'a', 'Ä' => 'A', 'è' => 'c',

        'È' => 'C', 'ï' => 'd', 'Ï' => 'D', 'é' => 'e', 'É' => 'E',

        'ì' => 'e', 'Ì' => 'E', 'ě' => 'e', 'Ě' => 'E', 'í' => 'i',

        'Í' => 'I', 'i' => 'i', 'I' => 'I', '¾' => 'l', '¼' => 'L',

        'å' => 'l', 'Å' => 'L', 'ň' => 'n', 'Ň' => 'N', 'ñ' => 'n',

        'Ñ' => 'N', 'ó' => 'o', 'Ó' => 'O', 'ö' => 'o', 'Ö' => 'O',

        'ř' => 'r', 'Ř' => 'R', 'à' => 'r', 'À' => 'R', 'š' => 's',

        'Š' => 'S', 'Č' => 'C', 'č' => 'c', 'œ' => 's', 'Œ' => 'S', '' => 't', '' => 'T',

        'ú' => 'u', 'ů' => 'u', 'Ú' => 'U', 'ù' => 'u', 'Ù' => 'U', 'ü' => 'u',

        'Ü' => 'U', 'ý' => 'y', 'Ý' => 'Y', 'y' => 'y', 'Y' => 'Y',

        'ž' => 'z', 'Ž' => 'Z', 'Ÿ' => 'z', '' => 'Z', '´' => '',

    );

    $title = strtolower(strtr($title, $convertTable));

    $title = preg_replace('/[^a-zA-Z0-9]+/u', '-', $title);

    $title = str_replace('--', '-', $title);

    $title = trim($title, '-');

    return $title;

}


$check = odkazy($_GET['check']);

$find_duplicate = $mysqli->query("SELECT id, seourl as simple_value FROM products WHERE id != '".$_GET['id']."' AND seourl = '".$check."' LIMIT 1")or die($mysqli->error);


if(mysqli_num_rows($find_duplicate) > 0){

//    $product = mysqli_fetch_assoc($find_duplicate);

    $response = array();
    $response['state'] = "failure";
//    $response['value'] = $product['simple_value'];

    echo json_encode($response);


}else{

    $response = array();
    $response['state'] = "success";

    echo json_encode($response);

}