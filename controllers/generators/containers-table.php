<?php

if (!isset($_REQUEST['html'])) {$_REQUEST['html'] = '';}

include $_SERVER['DOCUMENT_ROOT'] . '/admin/config/configPublic.php';

$id = $_REQUEST['id'];

$container_query = $mysqli->query("SELECT * FROM containers WHERE id = '$id'") or die($mysqli->error);
$container = mysqli_fetch_array($container_query);

    $title = !empty($container['container_name']) ? '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color: #666;">IQue ID: </span>'.$container['container_name'] : '';

$products = '';

$container_products = $mysqli->query("SELECT p.*, DATE_FORMAT(p.date_created, '%d. %m. %Y') as dateformated, w.supplier_name, wh.serial_number as warehouse_number, w.brand, w.fullname FROM containers_products p LEFT JOIN warehouse_products w ON w.connect_name = p.product LEFT JOIN warehouse wh ON wh.id = p.warehouse_id WHERE p.container_id = '" . $_REQUEST['id'] . "' ORDER BY p.id desc") or die($mysqli->error);

while ($cont_product = mysqli_fetch_array($container_products)) {

    if ($cont_product['warehouse_id'] != 0) {

        $productID = '#' . $cont_product['id'].' '.$cont_product['warehouse_number'];

    } else {

        $productID = '#' . $cont_product['id'];

    }

    $productName = $cont_product['supplier_name'] != '' ? $cont_product['supplier_name'] : $cont_product['fullname'];

    $oneproduct = '<div class="product"><table>
            <tr><td style="background-color: #ace6ce; padding: 3px 3px 2px; font-size: 6.5px;border-right: 1px solid #fff;"><strong> '.$productID.'</strong></td>
    <td style="background-color: #ace6ce;  padding: 3px 3px 2px; font-size: 7px;text-align: center;">' . ucfirst($productName) . '</td></tr>
    ';

    $specsquery = $mysqli->query('SELECT * FROM specs WHERE product = 1 AND supplier = 1 order by rank asc') or die($mysqli->error);
    while ($specs = mysqli_fetch_assoc($specsquery)) {

        if(empty($specs['brand']) || !in_array($cont_product['brand'], json_decode($specs['brand']))){ continue; }

        $paramsquery = $mysqli->query('SELECT 
                c.value, p.option_en 
            FROM containers_products_specs_bridge c 
            LEFT JOIN specs_params p ON c.value = p.option 
            WHERE c.specs_id = "' . $specs['id'] . '" AND c.client_id = "' . $cont_product['id'] . '"
        ') or die($mysqli->error);
        $params = mysqli_fetch_assoc($paramsquery);

        if(empty($params)){ continue; }

        if (isset($specs['rank']) && $specs['rank'] == 0) {$font_size = 'font-size: 7px;';} else { $font_size = '';}

        if (isset($params['value']) && $params['value'] == 'Ano') {
            $value = 'Yes';
        } elseif (empty($params['value']) || $params['value'] === 'Ne') {
            $value = 'No';
        } else {
            $value = $params['option_en'];
        }


        if (isset($specs['id']) && $specs['id'] == 25) {$value = $value . '"';}

        $oneproduct .= '<tr><td style="background-color: ' . $specs['bg_colour'] . '; ' . $font_size . 'padding: 2.5px 3x 2px; border-right: 1px solid #fff;"><strong>' . $specs['name_en'] . '</strong></td>';
        $oneproduct .= '<td style="background-color: ' . $specs['bg_colour'] . ';  ' . $font_size . 'padding: 2.5px 3px 2px;  text-align: center;">' . $value . '</td></tr>';

    }
    //exit;

    $oneproduct .= '</table></div>';

    $products .= $oneproduct;

}

$site_logo = '<img src="../../assets/images/wellnesstrade-shop.png" style="margin: 9px 0 0; float: left;" width="140">';

$mpdf = new Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4-L', 'orientation' => 'L']);

//==============================================================

$html = '
<style>
@page{

    margin: 5px 0;
    background-color: #FFFFFF;
}

body, div, p {
    font-family: "Roboto", Helvetica;
    font-size: 6px;
    color: #000000;
}

strong { font-family: Roboto-Medium, Helvetica; font-weight: 500;}

.product {
    width: 13.6%; 
    float: left; 
    margin: 0 0.54% 10px; 
    float: left;
}

.product table {
    width: 100%; 
    float: left;
}


.product table tr td {
    border-bottom: 1px solid #fff; 
}

.gradient {
    border:0.1mm solid #220044;
    background-color: #f0f2ff;
    background-gradient: linear #c7cdde #f0f2ff 0 1 0 0.5;
    box-shadow: 0.3em 0.3em #888888;
}
h4 {
    font-weight: bold;
    margin-top: 1em;
    margin-bottom: 0.3em;
    margin-top: 0;
}
div.text {
    padding:0.8em;
    margin-bottom: 0.7em;
}
p { margin: 0.1em 0; }
p.code {
    background-color: #e5e5e5;
    margin: 1em 1cm;
    padding: 0 0.3cm;
    border:0.2mm solid #000088;
    box-shadow: 0.3em 0.3em #888888;
}
p.example, div.example {
    background-color: #eeeeee;
    margin: 0.3em 1em 1em 1em;
    padding: 0 0.3cm;
    border:0.2mm solid #444444;
}
.code {
    font-family: monospace;
    font-size: 9pt;
}
.shadowtitle {
    height: 8mm;
    background-color: #EEDDFF;
    background-gradient: linear #c7cdde #f0f2ff 0 1 0 0.5;
    padding: 0.8em;
    padding-left: 3em;
    font-family:sans;
    font-size: 26pt;
    font-weight: bold;
    border: 0.2mm solid white;
    border-radius: 0.2em;
    box-shadow: 0 0 1em 0.5em rgba(0,0,255,0.5);
    color: #AAAACC;
    text-shadow: 0.03em 0.03em #666, 0.05em 0.05em rgba(127,127,127,0.5), -0.015em -0.015em white;
}
h3 {
    margin: 3em 0 2em -15mm;
    background-color: #EEDDFF;
    background-gradient: linear #c7cdde #f0f2ff 0 1 0 0.5;
    padding: 0.5em;
    padding-left: 3em;
    width: 50%;
    font-family:sans;
    font-size: 16pt;
    font-weight: bold;
    border-left: none;
    border-radius: 0 2em 2em 0;
    box-shadow: 0 0 2em 0.5em rgba(255,0,0,1);
    text-shadow: 0.05em 0.04em rgba(127,127,127,0.5);
}
.css {
    font-family: arial;
    font-style: italic;
    color: #000088;
}
img.smooth {
    image-rendering:auto;
    image-rendering:optimizeQuality;
    -ms-interpolation-mode:bicubic;
}
img.crisp {
    image-rendering: -moz-crisp-edges;      /* Firefox */
    image-rendering: -o-crisp-edges;        /* Opera */
    image-rendering: -webkit-optimize-contrast;/* Webkit (non-standard naming) */
    image-rendering: crisp-edges;
    -ms-interpolation-mode: nearest-neighbor;   /* IE (non-standard property) */
}


table {
    border-collapse: collapse;
}

table, th, td {
    border: 0;
}
table.bank-table td { padding: 2px 0; width: 150px;}

table.products {  color: #000; font-size: 12px;}

table.products td { text-align: center;border-bottom: 1px dotted #bedfe7; }

table.products td.first { text-align: left;}

table.products tr.head td { font-size: 11px; color: #2b2e39;}

table.total {  color: #000; font-size: 13px;}
table.total td { text-align: center;border-bottom: 1px dotted #dcdcdc; padding: 12px 15px 11px;}
table.total td.first { padding-left: 20px; text-align: left;}

</style>
<body>

<div style="width: 100%; padding-bottom: 0;">

<div style="width: 100%; padding-left: 20px;">
' . $site_logo . '
<h1 style="float: right; padding-top: 1px; text-align: right; margin-right: 46px; margin-bottom: -1px; font-size: 18px;"><span style="color: #666; float: right;">Container num.:</span> #' . $container['id_brand'].$title . '</h1>

</div>

</div>

<div style="clear: both;"></div>

</div>

<hr style="color: #e7e7ef; margin-left: 75px; margin-right: 75px; margin-top: 0; width: 100%; margin-bottom: 8px;">

<div style="clear: both;"></div>

<div style="margin: 0 10px;">
' . $products . '
</div>
</div>
</body>
';
//==============================================================
if (isset($_REQUEST['html']) && $_REQUEST['html']) {echo $html;exit;}
//==============================================================
$mpdf->WriteHTML($html);


if($container['brand'] == 'IQue'){

    $pdf_link = $container['id_brand'];
    $link_secret = 'IQU_bgewKD';

}elseif($container['brand'] == 'Lovia'){

    $pdf_link = 'lovia_'.$container['id_brand'];
    $link_secret = 'LOV_qJcUBZ';

}elseif($container['brand'] == 'Quantum'){

    $pdf_link = 'qua_'.$container['id_brand'];
    $link_secret = 'QUA_jEjsaI';

}elseif($container['brand'] == 'Pergola'){

    $pdf_link = 'per_'.$container['id_brand'];
    $link_secret = 'PER_JoifaE';

}elseif($container['brand'] == 'Espoo'){

    $pdf_link = 'esp_'.$container['id_brand'];
    $link_secret = 'ESP_fjFSoe';

}

$mpdf->Output($_SERVER['DOCUMENT_ROOT'] . '/admin/data/containers/'.$link_secret.'/container_' . $pdf_link . '.pdf', 'F');

Header('Location:https://www.wellnesstrade.cz/admin/data/containers/'.$link_secret.'/container_' . $pdf_link . '.pdf?t='.$currentDate->getTimestamp());
exit;
