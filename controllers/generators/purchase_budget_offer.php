<?php

if (!isset($_REQUEST['html'])) {$_REQUEST['html'] = '';}

include_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include_once $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/functions.php";

$id = $_REQUEST['id'];

$getclientquery = $mysqli->query('SELECT *, DATE_FORMAT(date, "%d. %m. %Y") as dateformated, DATE_FORMAT(realization, "%d. %m. %Y") as realizationformated, DATE_FORMAT(realtodate, "%d. %m. %Y") as realtodateformat FROM demands WHERE id="' . $id . '"') or die($mysqli->error);
$getclient = mysqli_fetch_assoc($getclientquery);

if (isset($getclient['customer']) && ($getclient['customer'] == 0 || $getclient['customer'] == 1) || ($getclient['customer'] == 3 && $_REQUEST['type'] == '1') || ($getclient['customer'] == 4)) {

    $product = $getclient['product'];

} elseif (isset($getclient['customer']) && $getclient['customer'] == 3 && $_REQUEST['type'] == '0'){

    $product = $getclient['secondproduct'];

}

$brand_query = $mysqli->query("SELECT brand FROM warehouse_products WHERE connect_name = '$product'") or die($mysqli->error);
$brand = mysqli_fetch_assoc($brand_query);

if (isset($_REQUEST['type']) && $_REQUEST['type'] == '0') {

    $site_logo = '<img src="../../assets/images/saunahouse-shop.png" width="170" style="margin: 38px 0 8px; ">';

} elseif (isset($_REQUEST['type']) && ($_REQUEST['type'] == '1' || $_REQUEST['type'] == '4')) {

    $site_logo = '<img src="../../assets/images/spahouse-shop.png" width="140" style="margin: 26px 0 0px; ">';

}

$data_query = $mysqli->query("SELECT * FROM demands_generate WHERE id = '$id'");
$data = mysqli_fetch_array($data_query);

$currency = currency($data['currency']);

$billing_query = $mysqli->query('SELECT * FROM addresses_billing WHERE id = "' . $getclient['billing_id'] . '"') or die($mysqli->error);
$billing = mysqli_fetch_assoc($billing_query);

if ((!empty($billing['billing_company'])) || (!empty($billing['billing_ico']))) {

    $name = $billing['billing_company'] ?: $billing['billing_name'] . ' ' . $billing['billing_surname'];

    if ($billing['billing_degree'] != "") { $name = $billing['billing_degree'] . ' ' . $name; }

    $address = 'se sídlem <strong>' . $billing['billing_street'] . ', ' . $billing['billing_zipcode'] . ', ' . $billing['billing_city'] . ', IČ: ' . $billing['billing_ico'] . ', DIČ: ' . $billing['billing_dic'] . '</strong>';

} else {

    $name = $billing['billing_name'] . ' ' . $billing['billing_surname'];

    if ($billing['billing_degree'] != "") { $name = $billing['billing_degree'] . ' ' . $name; }

    $address = 'bytem <strong>' . $billing['billing_street'] . ', ' . $billing['billing_zipcode'] . ', ' . $billing['billing_city'] . '</strong>';

}

if (isset($_REQUEST['type']) && $_REQUEST['type'] == '1') {

    $paramsquery = $mysqli->query('SELECT value FROM demands_specs_bridge WHERE specs_id = "5" AND client_id = "' . $getclient['id'] . '"') or die($mysqli->error);
    $params = mysqli_fetch_array($paramsquery);

    $provedeni = $params['value'];

    if($provedeni == 'Special version'){ $provedeni = 'Gold'; }

    $paramsquery = $mysqli->query('SELECT value FROM demands_specs_bridge WHERE specs_id = "2" AND client_id = "' . $getclient['id'] . '"') or die($mysqli->error);
    $params = mysqli_fetch_array($paramsquery);

    $oplasteni = $params['value'];

    if (strpos($oplasteni, ' DIAMOND/PLATINUM') !== false) {

        $oplasteni = str_replace(" DIAMOND/PLATINUM", "", $oplasteni);

    } elseif (strpos($oplasteni, ' GOLD/SILVER') !== false) {

        $oplasteni = str_replace(" GOLD/SILVER", "", $oplasteni);

    }

    $paramsquery = $mysqli->query('SELECT value FROM demands_specs_bridge WHERE specs_id = "1" AND client_id = "' . $getclient['id'] . '"') or die($mysqli->error);
    $params = mysqli_fetch_array($paramsquery);

    // todo nešikovné, může se zjistil dole v loopu, ale je to "GENERATE"? možná ne
    $akryl = $params['value'];



    $data_hottub_query = $mysqli->query("SELECT * FROM demands_generate_hottub WHERE id = '$id'");
    $data_hottub = mysqli_fetch_array($data_hottub_query);


    $vat = 100 + $data['price_vat'];
    $total_price = $data_hottub['price_hottub'];

    $specs_demand = $mysqli->query("SELECT *, s.id as id FROM specs s, demands_specs_bridge d WHERE d.specs_id = s.id AND d.value != '' AND d.value != 'Ne' AND d.client_id = '" . $getclient['id'] . "' AND s.generate = 1 ORDER BY s.demand_order") or die($mysqli->error);

    $i = 4;
    $specifications = "";
    while ($spec = mysqli_fetch_array($specs_demand)) {

        if(!$spec['is_generated'] ){

            $total_price += $spec['price'];

        }else{

            $text = $spec['generate_text'] ?: '-';

            // vyjímka schůdky
            if ($spec['generate_text'] == "Schůdky") { $text = $spec['value']; }

            // vyjímka termokryt
            if (isset($spec['name']) && $spec['name'] == 'Termokryt') {
                if ($brand['brand'] == 'IQue') {
                    $spec['name'] = 'Termokryt IQue';
                } else {
                    $spec['name'] = 'Termokryt';
                }
                $text = $spec['value'];
            }

            // vyjímka držák na kryt
            if (isset($spec['name']) && $spec['name'] == 'Držák na kryt') { $text = $spec['value']; }

            // vyjímka swimspa
            if ($spec['name'] == 'Termokryt' && $brand['brand'] == 'Swim SPA') { $text = 'Termický kryt SwimSpa - '.$spec['value']; }
            if ($spec['name'] == 'Zimní Izolace ' && $brand['brand'] == 'Swim SPA') { $text = 'Reflexní izolace opláštění a podlahy SwimSpa'; }
            if ($spec['name'] == 'Trysky' && $brand['brand'] == 'Swim SPA') { $text = $spec['value']; }

            if($spec['name'] == 'Ozonátor'){  $text = $spec['value']; }

            $specifications .= '<tr>
            <td>' . $spec['name'] . '</td>
            <td>' . $text . '</td>
            <td class="price">' . number_format($spec['price'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH</td>
            </tr>';

            $total_price += $spec['price'];

            $i++;

        }

    }

    $accessories_query = $mysqli->query("SELECT * FROM demands_accessories_bridge WHERE aggregate_id = '" . $getclient['id'] . "'") or die($mysqli->error);
    while ($accessory = mysqli_fetch_array($accessories_query)) {

        $title = $accessory['product_name'];

        // todo insert directly into database or fetch form products table
        if (strpos($accessory['product_name'], 'CoverMate III') !== false) {

            $subtitle = 'Hydraulický držák termického krytu k vířivce';

        }elseif (strpos($accessory['product_name'], 'CoverMate II') !== false) {

            $subtitle = 'Držák termického krytu k vířivce';

        }elseif (strpos($accessory['product_name'], 'CoverMate I') !== false) {

            $subtitle = 'Držák termického krytu k vířivce';

        }else{

            $subtitle = $accessory['variation_values'] ?: '-';

        }

        $specifications .= '<tr>
        <td>' . $title . '</td>
        <td>' . $subtitle . '</td>
        <td class="price">' . number_format($accessory['price'] * $accessory['quantity'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH</td>
        </tr>';

        $total_price += $accessory['price'] * $accessory['quantity'];

        $i++;
    }

    $dphprice = $total_price / 100 * $vat;

    if($data_hottub['chemie_type'] == 0){ $data_hottub['price_chemie'] = 0; }

    $totalpriceddd = $total_price + $data_hottub['price_delivery'] + $data_hottub['price_montage'] + $data_hottub['price_chemie'] - $data_hottub['discount'];

    $dphpricedelivery = $totalpriceddd / 100 * $vat;


    $first_deposit = $data['deposit'];
    $second_deposit = 100 - $data['deposit'];

    if (isset($data['deposit_type']) && $data['deposit_type'] == 'percentage') {

        $deposit_value = $data['deposit'] . '%';

    } elseif (isset($data['deposit_type']) && $data['deposit_type'] == 'money') {

        $count_deposit = number_format($data['deposit'] / 100 * $vat, 2, ',', ' ');

        $deposit_value = $count_deposit . ' '.$currency['sign'];

    }

    $reverse_charge_text = '';
    if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

        $first_price_calculate = $totalpriceddd / 100 * $first_deposit;
        $second_price_calculate = $totalpriceddd / 100 * $second_deposit;

        $first_price = number_format($first_price_calculate, 2, ',', ' ') . ',- '.$currency['sign'].' bez DPH';
        $second_price = number_format($second_price_calculate, 2, ',', ' ') . ',- '.$currency['sign'].' bez DPH';

        $reverse_charge_text = '<p>jedná se o přenesení daňové povinnosti podle § 92a zákona o dani z přidané hodnoty, kdy výši daně je povinen doplnit a přiznat plátce, pro kterého je plnění uskutečněno.</p>';

        $reverse_charge_final_price = number_format($totalpriceddd, 0, ',', ' ') . ' '.$currency['sign'].' bez DPH';

        $final_price = '<tr>
<td colspan="2">Cena vířivky vč. příslušenství a dopravy</td>
<td colspan="2">' . $reverse_charge_final_price . '</td>
</tr>';

    } else {

        $first_price_calculate = $dphpricedelivery / 100 * $first_deposit;
        $second_price_calculate = $dphpricedelivery / 100 * $second_deposit;

        $first_price = number_format($first_price_calculate, 2, ',', ' ') . ',- '.$currency['sign'].' s DPH';
        $second_price = number_format($second_price_calculate, 2, ',', ' ') . ',- '.$currency['sign'].' s DPH';

        $reverse_charge_dph = '<tr>
<td style="background-color: #d5edf2;">Cena předmětu koupě s ' . $data['price_vat'] . '% DPH</td>
<td style="background-color: #d5edf2;">-</td>
<td style="background-color: #d5edf2;">' . number_format($dphprice, 2, ',', ' ') . '  '.$currency['sign'].' s '. $data['price_vat'] . '% DPH</td>
</tr>';

//        echo $dphpricedelivery;
        $reverse_charge_final_price = '<div style="font-size: 13px; margin-bottom: 10px;">'.number_format($dphpricedelivery, 2, ',', ' ') . ' '.$currency['sign'].' s '. $data['price_vat'] . '% DPH</div>';

        $final_without_vat =  '<div style="margin-top: 10px; font-size: 10px;">'.number_format($totalpriceddd, 2, ',', ' ') . ' '.$currency['sign'].' bez DPH</div>';

        $final_price = '<tr>
<td colspan="2" rowspan="2">Cena vířivky vč. příslušenství a dopravy</td>
<td colspan="2">' . $final_without_vat . '</td>
</tr>
<tr style="border-top: 0 !important;">
<td colspan="2">' . $reverse_charge_final_price . '</td>
</tr>';

    }

    $discount = '';
    if (isset($data_hottub['discount']) && $data_hottub['discount'] != "" && $data_hottub['discount'] != 0) {

        $discount = '<tr>
<td style="background-color: #d5edf2;"><strong>Sleva</strong></td>
<td style="background-color: #d5edf2;">-</td>
<td class="price" style="background-color: #d5edf2;">' . number_format($data_hottub['discount'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH</td>
</tr>
<tr>
<td style="background-color: #d5edf2;">Cena za vířivku po slevě</td>
<td style="background-color: #d5edf2;">-</td>
<td class="price" style="background-color: #d5edf2;">' . number_format($data_hottub['price_hottub'] - $data_hottub['discount'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH</td>
</tr>';

        $i++;
        $i++;
    }

    if (isset($data_hottub['chemie_type']) && $data_hottub['chemie_type'] == 1) {

        $start_chemie = 'pH+, pH-, oxy, aktivátor, testery';

    } elseif (isset($data_hottub['chemie_type']) && $data_hottub['chemie_type'] == 2) {

        $start_chemie = 'pH+, pH-, testery, chlor';

    } elseif (isset($data_hottub['chemie_type']) && $data_hottub['chemie_type'] == 3) {

        $start_chemie = 'pH+, pH-, testery, BromiCharge';

    } elseif (isset($data_hottub['chemie_type']) && $data_hottub['chemie_type'] == 4) {

        $start_chemie = 'Spa Clear, ph+, pH-, testery';

    } elseif (isset($data_hottub['chemie_type']) && $data_hottub['chemie_type'] == 5) {

        $start_chemie = 'Polynode, ph+, pH-, testery';

    } elseif (isset($data_hottub['chemie_type']) && $data_hottub['chemie_type'] == 6) {

        $start_chemie = 'Brom, ph+, pH-, testery';

    }

    $insert_chemie = '';
    if(!empty($start_chemie)){

        $insert_chemie = '<tr>
<td>Startovací chemie</td>
<td>' . $start_chemie . '</td>
<td class="price">' . number_format($data_hottub['price_chemie'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH</td>
</tr>';

    }


    $other_arrangements = $data['other_arrangements'] ? '<p style="padding-bottom: 2px;">11. ' . $data['other_arrangements'] . '</p>' : '';

    if (isset($data_hottub['delivery_address']) && $data_hottub['delivery_address'] == "" || $data_hottub['delivery_address'] == ', , 0') {

        $delivery_address = "";

    } else {

        $delivery_address = 'Místo dodávky: <strong><u>' . $data_hottub['delivery_address'] . '</u></strong>';

    }

    function warrantySign($warranty){
        if($warranty === '1'){
            return $warranty.' rok';
        }elseif($warranty > '4'){
            return $warranty.' let';
        }else{
            return $warranty.' roky';
        }
    }

    if ($brand['brand'] == 'IQue' || $brand['brand'] == 'Swim SPA') {

        if($brand['brand'] == 'IQue'){ $warranty_type = ' IQue Warranty Exclusive'; }else{ $warranty_type = ''; }

        $warranty = 'Prodávající poskytuje prodloužené záruční podmínky'.$warranty_type.': '.warrantySign($data_hottub['warranty_third']).' na akrylátovou skořepinu, '.warrantySign($data_hottub['warranty_fifth']).' na konstrukci vířivky, '.warrantySign($data_hottub['warranty_second']).'  na (řídící systém, ovládací panel), '.warrantySign($data_hottub['warranty_first']).' na (hydromasážní pumpy, cirkulační pumpa, vodoinstalace, opláštění, trysky a ovládací prvky), '.warrantySign($data_hottub['warranty_fourth']).' na (všechny ostatní komponenty). V případě komerčního provozu je komplexní záruka na vše uvedené 2 roky. Záruční podmínky jsou dále upraveny ve VOP.';

        $hottub_specs = $akryl . ', ' . $oplasteni . ' barva opláštění';

    } elseif ($brand['brand'] == 'SH Spas') {

        $warranty = 'Prodávající poskytuje prodloužené záruční podmínky SH SPAS Warranty: '.warrantySign($data_hottub['warranty_first']).' na (trysky, vodoinstalace – potrubí, ovládací prvky, opláštění, čerpadla a konstrukci), '.warrantySign($data_hottub['warranty_second']).' na (ovládací systém), '.warrantySign($data_hottub['warranty_third']).' na akrylátovou skořepinu, '.warrantySign($data_hottub['warranty_fourth']).' na (příplatkovou výbavu a doplňky). Záruční podmínky jsou dále upraveny ve VOP.';

        $hottub_specs = $akryl . ', Carbon Grey barva opláštění';

    }


    if(file_exists($_SERVER['DOCUMENT_ROOT'] .'/admin/data/images/customer/' . $getclient['product'] . '-'.$provedeni.'.png')){

        $file_url = '../../../admin/data/images/customer/' . $getclient['product'] . '-'.$provedeni.'.png';

    }else{

        $file_url = '../../../admin/data/images/customer/' . $getclient['product'] . '.png';

    }


$contact_info = '';
if(!empty($name) && !empty($address) && !empty($billing['billing_email'])){

    $contact_info = '<p style="padding-bottom: 6px; font-size: 16px;"><strong>' . $name . '</strong></p>
    <p>' . $address . '</p>
    <p style="padding-bottom: 8px;">E-mail: <strong>' . $billing['billing_email'] . '</strong>, Tel.: <strong>' . number_format($billing['billing_phone'], 0, ',', ' ') . '</strong></p>';

}


    $mpdf = new Mpdf\Mpdf([
            'disableJavaScript' => true,
            'mode' => 'utf-8',
            'compression' => 0, // disable object stream compression
        ]);

        $mpdf->SetTitle('Zálohová faktura ' . $invoice_id);
        $mpdf->SetAuthor('Wellness Trade, s. r. o.');
        $mpdf->SetCreator('Wellness Trade, s. r. o.');
        $mpdf->SetSubject('Zálohová faktura ' . $invoice_id);
        $mpdf->SetKeywords('zaloho faktura, invoice');

        $mpdf->useActiveForms = false;
        $mpdf->useSubstitutions = false;

//==============================================================

    $html = '
<style>
@page{
    sheet-size: 210mm 297mm;
    margin: 0;
    background-color: #FFFFFF;
}

body, div, p {
	font-family: "Roboto", "Helvetica";
	font-size: 10px;
	color: #000000;
}

strong { font-family: Roboto-Medium, Helvetica; font-weight: 500;}

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
	image-rendering: -moz-crisp-edges;		/* Firefox */
	image-rendering: -o-crisp-edges;		/* Opera */
	image-rendering: -webkit-optimize-contrast;/* Webkit (non-standard naming) */
	image-rendering: crisp-edges;
	-ms-interpolation-mode: nearest-neighbor;	/* IE (non-standard property) */
}


table {
    border-collapse: collapse;
}

table, th, td {
    border: 0;
}
table.bank-table td { padding: 3px 0; width: 150px;}


table.products {  color: #000; font-size: 13px;}

table.products td { text-align: center;border-bottom: 1px dotted #bedfe7; }


table.products td.first { text-align: left;}

table.products tr.head td { font-size: 11px; color: #2b2e39;}

table td.price { text-align: right; padding-right: 15px;} 

table.total {  color: #000; font-size: 13px;}

table.total td { text-align: center;border-bottom: 1px dotted #dcdcdc; padding: 13px 15px 12px;}





table.tablus tr { background-color: #e2f3f7; border: 1px solid #d4eef4;}

table.total td.first { padding-left: 25px; text-align: left;}

table.tablus thead tr {background-color: #cbe6ed; border-bottom: 1px solid #e9f6f9;}
table.tablus thead td {border-right: 1px solid #e9f6f9;}
table.tablus td { font-size: 9.5px; padding: 6px 15px 5px; text-align: center; border-right: 1px solid #d4eef4;}

table.tablus tfoot tr { background-color: #e2f3f7; border-top: 5px solid #c7e3ea;}

table.tablus tfoot td {font-weight: bold; font-family: Roboto-Light, Helvetica; font-size: 13px; padding: 14px 15px 14px;}




table.tablus2 tr { border: 1px solid #d4eef4;}

table.tablus2 td{ padding: 13px 15px 12px; text-align: center; border-right: 1px solid #d4eef4;}


</style>
<body>

<div style="width: 100%; margin-bottom: 40px; text-align:center;">
' . $site_logo . '
</div>

<div style="padding: 5px 60px 5px 60px;float: left; width: 100%;">

'.$contact_info.'

<h1 style=" font-family: Roboto-Light, Helvetica; margin-bottom: 20px; margin-top: 40px; font-size: 22px; text-align: center;">Cenová nabídka</h1>
<table class="tablus" style="margin: 10px 0; width: 100%;">
<thead>
<tr>
<td>Název</td>
<td>Specifikace</td>
<td>Cena</td>
<td>Fotografie</td>
</tr>
</thead>
<tbody>
<tr>
<td>' . returnpn($getclient['customer'], $getclient['product']) . ' - ' . $provedeni . '</td>
<td>' . $hottub_specs . '</td>
<td class="price">' . number_format($data_hottub['price_hottub'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH</td>
<td rowspan="' . $i . '"><img src="'.$file_url.'" width="115"></td>
</tr>

' . $discount . '

' . $specifications . '

' . $insert_chemie . '

<tr>
<td>Montáž</td>
<td>1 technik</td>
<td class="price">' . number_format($data_hottub['price_montage'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH</td>
</tr>

<tr>
<td>Doprava</td>
<td>-</td>
<td class="price">' . number_format($data_hottub['price_delivery'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH</td>
</tr>


</tbody>
<tfoot>
'.$final_price.'
</tfoot>
</table>

</div>
<div style="clear: both;"></div>

';
//==============================================================
    if (isset($_REQUEST['html']) && $_REQUEST['html']) {echo $html;exit;}
//==============================================================

    $mpdf->WriteHTML($html);

    $mpdf->Output($_SERVER['DOCUMENT_ROOT'] . '/admin/data/demands/budget_offers/Cenova_nabidka_' . $id . '.pdf', 'F');



} elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == '0') {

    if (isset($getclient['customer']) && $getclient['customer'] == 0) {$product = $getclient['product'];} elseif (isset($getclient['customer']) && $getclient['customer'] == 3) {$product = $getclient['secondproduct'];}

    $data_sauna_query = $mysqli->query("SELECT * FROM demands_generate_sauna WHERE id = '$id'");
    $data_sauna = mysqli_fetch_array($data_sauna_query);

    $vat = 100 + $data['price_vat'];

    $totalprice = $data_sauna['price_sauna'];

    $totalpriceddd = $totalprice + $data_sauna['price_delivery'] + $data_sauna['price_montage'] - $data_sauna['discount'];

    $dphprice = $totalprice / 100 * $vat;

    $dphpricedelivery = $totalpriceddd / 100 * $vat;

    $first_deposit = $data['deposit'];
    $second_deposit = 100 - $data['deposit'];

    if (isset($data['deposit_type']) && $data['deposit_type'] == 'percentage') {

        $deposit_value = $data['deposit'] . '%';

    } elseif (isset($data['deposit_type']) && $data['deposit_type'] == 'money') {

        $deposit_value = $data['deposit'] . ' '.$currency['sign'].'';

    }

    $reverse_charge_text = '';
    if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

        $first_price_calculate = $totalpriceddd / 100 * $first_deposit;
        $second_price_calculate = $totalpriceddd / 100 * $second_deposit;

        $first_price = number_format($first_price_calculate, 2, ',', ' ') . ',- '.$currency['sign'].' bez DPH';
        $second_price = number_format($second_price_calculate, 2, ',', ' ') . ',- '.$currency['sign'].' bez DPH';

        $reverse_charge_text = '<p>jedná se o přenesení daňové povinnosti podle § 92a zákona o dani z přidané hodnoty, kdy výši daně je povinen doplnit a přiznat plátce, pro kterého je plnění uskutečněno.</p>';

        $reverse_charge_final_price = number_format($totalpriceddd, 0, ',', ' ') . ' '.$currency['sign'].' bez DPH';

        $final_price = '<tr>
<td colspan="2">Cena sauny vč. příslušenství a dopravy</td>
<td colspan="2">' . $reverse_charge_final_price . '</td>
</tr>';

    } else {

        $first_price_calculate = $dphpricedelivery / 100 * $first_deposit;
        $second_price_calculate = $dphpricedelivery / 100 * $second_deposit;

        $first_price = number_format($first_price_calculate, 2, ',', ' ') . ',- '.$currency['sign'].' s DPH';
        $second_price = number_format($second_price_calculate, 2, ',', ' ') . ',- '.$currency['sign'].' s DPH';

        $reverse_charge_dph = '<tr>
<td style="background-color: #d5edf2;">Cena předmětu koupě s ' . $data['price_vat'] . '% DPH</td>
<td style="background-color: #d5edf2;">-</td>
<td style="background-color: #d5edf2;">' . number_format($dphprice, 2, ',', ' ') . '  '.$currency['sign'].' s '. $data['price_vat'] . '% DPH</td>
</tr>';

//        echo $dphpricedelivery;
        $reverse_charge_final_price = '<div style="font-size: 13px; margin-bottom: 10px;">'.number_format($dphpricedelivery, 2, ',', ' ') . ' '.$currency['sign'].' s '. $data['price_vat'] . '% DPH</div>';

        $final_without_vat =  '<div style="margin-top: 10px; font-size: 10px;">'.number_format($totalpriceddd, 2, ',', ' ') . ' '.$currency['sign'].' bez DPH</div>';

        $final_price = '<tr>
<td colspan="2" rowspan="2">Cena sauny vč. příslušenství a dopravy</td>
<td colspan="2">' . $final_without_vat . '</td>
</tr>
<tr style="border-top: 0 !important;">
<td colspan="2">' . $reverse_charge_final_price . '</td>
</tr>';

    }

    $i = 5;
    $discount = '';
    if (isset($data_sauna['discount']) && $data_sauna['discount'] != "" && $data_sauna['discount'] != 0) {

        $discount = '<tr>
<td style="background-color: #d5edf2;"><strong>Sleva</strong></td>
<td style="background-color: #d5edf2;">-</td>
<td class="price" style="background-color: #d5edf2;">' . number_format($data_sauna['discount'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH</td>
</tr>
<tr>
<td style="background-color: #d5edf2;">Cena za saunu po slevě</td>
<td style="background-color: #d5edf2;">-</td>
<td class="price" style="background-color: #d5edf2;">' . number_format($data_sauna['price_hottub'] - $data_sauna['discount'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH</td>
</tr>';

        $i++;
    }

    $other_arrangements = "";
    if ($data['other_arrangements'] != "") {

        $other_arrangements = '<p style="padding-bottom: 2px;">11. ' . $data['other_arrangements'] . '</p>';
    }

    $sauna_specifications = "<tr>
<td>" . returnpn($getclient['customer'], $product) . "</td>
<td>" . $data_sauna['dimension'] . " mm</td>
</tr>";

    if (isset($data_sauna['material']) && $data_sauna['material'] == 'red-cedar') {$material = 'Červený cedr';} elseif (isset($data_sauna['material']) && $data_sauna['material'] == 'abachi') {$material = 'Abachi';} elseif (isset($data_sauna['material']) && $data_sauna['material'] == 'african-ayous') {$material = 'Africká vrba';}

    $sauna_specifications .= "<tr>
<td>Materiál</td>
<td>" . $material . "</td>
</tr>";

    $sauna_specifications .= "<tr>
<td>Kamenná stěna</td>
<td>" . ucfirst($data_sauna['stone_wall']) . "</td>
</tr>";

    $sauna_specifications .= "<tr>
<td>Lavice</td>
<td>Horní " . $data_sauna['bottom_bench'] . " cm - Spodní " . $data_sauna['bottom_bench'] . " cm</td>
</tr>";

    if (isset($data_sauna['glass_wall']) && $data_sauna['glass_wall'] == 1) {

        $sauna_specifications .= "<tr>
	<td>Stěna</td>
	<td>Kalené sklo o síle 10mm - čiré sklo</td>
	</tr>";

    }

    if (isset($data_sauna['glass_doors']) && $data_sauna['glass_doors'] == 1) {

        $sauna_specifications .= "<tr>
	<td>Dveře</td>
	<td>Kalené sklo o síle 8mm - čiré sklo</td>
	</tr>";

    }

    if (isset($data_sauna['handrail']) && $data_sauna['handrail'] == 'stainless') {$handrail = 'Nerezové madlo (vnitřní část z červeného cedru)';} elseif (isset($data_sauna['handrail']) && $data_sauna['handrail'] == 'wooden') {$handrail = 'Dřevěné';} elseif (isset($data_sauna['handrail']) && $data_sauna['handrail'] == 'glass') {$handrail = 'Skleněné';}

    $sauna_specifications .= "<tr>
<td>Madlo</td>
<td>" . $handrail . "</td>
</tr>";

    if ($data_sauna['stove'] != "") {

        $sauna_specifications .= "<tr>
	<td>Kamna</td>
	<td>" . $data_sauna['stove'] . "</td>
	</tr>";

    }

    if (isset($data_sauna['controlpanel']) && $data_sauna['controlpanel'] == 1) {

        $sauna_specifications .= "<tr>
	<td>Řídící jednotka</td>
	<td>Espoo Touchscreen</td>
	</tr>";

    }

    if (isset($data_sauna['remote']) && $data_sauna['remote'] == 1) {

        $sauna_specifications .= "<tr>
	<td>Dálkové ovládání</td>
	<td>ANO</td>
	</tr>";

    }

    if (isset($data_sauna['rgb_sky']) && $data_sauna['rgb_sky'] == 1) {

        $sauna_specifications .= "<tr>
	<td>RGB hvězdná obloha</td>
	<td>ANO</td>
	</tr>";

    }

    if (isset($data_sauna['rgb_backrest']) && $data_sauna['rgb_backrest'] == 1) {

        $sauna_specifications .= "<tr>
	<td>RGB osvětlení za opěrkami</td>
	<td>ANO</td>
	</tr>";

    }

    if (isset($data_sauna['light']) && $data_sauna['light'] == 1) {

        $sauna_specifications .= "<tr>
	<td>Rohové světlo se stínítkem</td>
	<td>ANO</td>
	</tr>";

    }

    if (isset($data_sauna['audio']) && $data_sauna['audio'] == 1) {

        $sauna_specifications .= "<tr>
	<td>Audio systém (FM, MP3)</td>
	<td>ANO</td>
	</tr>";

    }

    if (isset($data_sauna['loudspeaker']) && $data_sauna['loudspeaker'] == 1) {

        $sauna_specifications .= "<tr>
	<td>2x voděodolný reproduktor</td>
	<td>ANO</td>
	</tr>";

    }

    if (isset($data_sauna['accessories']) && $data_sauna['accessories'] == '1') {$accessories = 'Vědro, naběračka, přesýpací hodiny a ručičkový teploměr';} elseif (isset($data_sauna['accessories']) && $data_sauna['accessories'] == '2') {$accessories = 'Vědro, naběračka, přesýpací hodiny a teploměr s vlhkoměrem';} elseif (isset($data_sauna['accessories']) && $data_sauna['accessories'] == '3') {$accessories = 'třetí možnost, ještě nevíme';}

    $sauna_specifications .= "<tr>
<td>Doplňky</td>
<td>" . $accessories . "</td>
</tr>";

    $mpdf = new Mpdf\Mpdf([
            'disableJavaScript' => true,
            'mode' => 'utf-8',
            'compression' => 0, // disable object stream compression
        ]);

        $mpdf->SetTitle('Zálohová faktura ' . $invoice_id);
        $mpdf->SetAuthor('Wellness Trade, s. r. o.');
        $mpdf->SetCreator('Wellness Trade, s. r. o.');
        $mpdf->SetSubject('Zálohová faktura ' . $invoice_id);
        $mpdf->SetKeywords('zaloho faktura, invoice');

        $mpdf->useActiveForms = false;
        $mpdf->useSubstitutions = false;

//==============================================================

    $html = '
<style>
@page{
    sheet-size: 210mm 297mm;
    margin: 0;
    background-color: #FFFFFF;
}

body, div, p {
	font-family: "Roboto", Helvetica;
	font-size: 10px;
	color: #000000;
}

strong { font-family: Roboto-Medium, Helvetica; font-weight: 500;}

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
        image-rendering: -moz-crisp-edges;		/* Firefox */
	image-rendering: -o-crisp-edges;		/* Opera */
	image-rendering: -webkit-optimize-contrast;/* Webkit (non-standard naming) */
	image-rendering: crisp-edges;
	-ms-interpolation-mode: nearest-neighbor;	/* IE (non-standard property) */
}


table {
    border-collapse: collapse;
}

table, th, td {
    border: 0;
}
table.bank-table td { padding: 3px 0; width: 150px;}


table.products {  color: #000; font-size: 13px;}

table.products td { text-align: center;border-bottom: 1px dotted #bedfe7; }


table.products td.first { text-align: left;}

table.products tr.head td { font-size: 11px; color: #2b2e39;}



table.total {  color: #000; font-size: 13px;}

table.total td { text-align: center;border-bottom: 1px dotted #dcdcdc; padding: 13px 15px 12px;}





table.tablus tr { background-color: #e2f3f7; border: 1px solid #d4eef4;}

table.total td.first { padding-left: 25px; text-align: left;}

table.tablus thead tr {background-color: #cbe6ed; border-bottom: 1px solid #e9f6f9;}
table.tablus thead td {border-right: 1px solid #e9f6f9;}
table.tablus td{ padding: 11px 15px 10px; text-align: center; border-right: 1px solid #d4eef4;}

table.tablus tfoot tr { background-color: #e2f3f7; border-top: 5px solid #c7e3ea;}

table.tablus tfoot td {font-weight: bold; font-family: Roboto-Light, Helvetica; font-size: 13px; padding: 15px 15px 15px;}




table.tablus2 tr { border: 1px solid #d4eef4;}

table.tablus2 td{ padding: 13px 15px 12px; text-align: center; border-right: 1px solid #d4eef4;}


</style>
<body>

<div style="width: 100%; margin-bottom: 18px; text-align:center;">
' . $site_logo . '
</div>

<div style="padding: 5px 60px 5px 60px;float: left; width: 100%;">


<p style="padding-bottom: 6px; font-size: 16px;">1. <strong>Wellness Trade, s.r.o.</strong></p>
<p style="padding-bottom: 8px;">se sídlem Vrbova 1277/32, 147 00 Praha, IČ: 29154871, DIČ: CZ29154871</p>
<p>společnost zapsaná v obchodním rejstříku vedeném Městským soudem v Praze oddíl C, vložka 203387</p>
<p>zastoupena jednatelem Michaelem Bäumelem. E-mail: baumel@saunahouse.cz. Bankovní spojení: 2000364217/2010</p>
<p style="padding-bottom: 16px;">(dále jen "<strong>prodávající</strong>") na straně jedné a</p>

<p style="padding-bottom: 6px; font-size: 16px;">2. <strong>' . $name . '</strong></p>
<p>' . $address . '</p>
<p style="padding-bottom: 8px;">E-mail: <strong>' . $billing['billing_email'] . '</strong>, Tel.: <strong>' . number_format($billing['billing_phone'], 0, ',', ' ') . '</strong></p>
<p>(dále jen "<strong>kupující</strong>" nebo "<strong>zákazník</strong>“) na straně druhé</p>
<p>podle ustanovení § 2586 nového občanského zákoníku v platném znění uzavřeli tuto</p>
<h1 style=" font-family: Roboto-Light, Helvetica; margin-bottom: 8px; font-size: 24px; text-align: center;">Smlouvu o dílo<br><small style="text-align: center; font-size: 12px;">(dále jen "smlouva")</small></h1>
<h2 style=" font-family: Roboto-Light, Helvetica; margin-bottom: 7px; font-size: 18px; float: left;">čl. I Úvodní ustanovení</h2>
<p style="padding-bottom: 2px;">Prodávající realizuje kupujícímu saunu <strong>' . returnpn($getclient['customer'], $product) . '</strong>.  Její podrobná specifikace je přílohou č. 1 k této smlouvě (dále také jen „sauna“ nebo „předmět koupě“).</p>
<h2 style=" font-family: Roboto-Light, Helvetica; margin-bottom: 7px; font-size: 18px; float: left;">čl. II Předmět smlouvy a kupní cena</h2>
<table class="tablus" style="margin: 10px 0; width: 100%;">
<thead>
<tr>
<td>Název</td>
<td>Specifikace</td>
<td>Cena</td>
<td>Fotografie</td>
</tr>
</thead>
<tbody>
<tr>
<td>' . returnpn($getclient['customer'], $product) . '</td>
<td>' . $data_sauna['dimension'] . ' mm</td>
<td>' . number_format($data_sauna['price_sauna'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH</td>
<td rowspan="' . $i . '"><img src="../../../admin/data/images/customer/' . $product . '.png"></td>
</tr>

<tr>
<td style="background-color: #d5edf2;">Cena za saunu vč. příslušenství</td>
<td style="background-color: #d5edf2;">-</td>
<td style="background-color: #d5edf2;">' . number_format($totalprice, 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH</td>
</tr>

' . $reverse_charge_dph . '


<tr>
<td>Montáž</td>
<td>2 technici</td>
<td>' . number_format($data_sauna['price_montage'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH</td>
</tr>

<tr>
<td>Doprava</td>
<td>-</td>
<td>' . number_format($data_sauna['price_delivery'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH</td>
</tr>

' . $discount . '

</tbody>
<tfoot>
' . $final_price . '
</tfoot>
</table>
' . $reverse_charge_text . '

<pagebreak />
<div style="width: 100%; margin-bottom: 24px; text-align:center;">
' . $site_logo . '
</div>
<p style="padding-bottom: 2px;">1. Prodávající zhotovuje kupujícímu saunu schopnou provozu dle platných právních předpisů, za kterých kupující saunu kupuje a zavazuje se zaplatit prodávajícímu kupní cenu sjednanou v čl. II. této smlouvy.</p>

<p style="padding-bottom: 2px;">2. Záloha ve výši <strong>' . $deposit_value . '</strong> je splatná <strong>předem</strong> na základě vystavené zálohové faktury. Doplatek kupní ceny (kupní cena po odečtení již
uhrazené zálohy) je splatný hotově při předání nebo převodem na účet.</p>



<h2 style=" font-family: Roboto-Light, Helvetica; margin-bottom: 7px; font-size: 18px; float: left; margin-top: 20px;">čl. III Lhůta a termín plnění</h2>

<p style="padding-bottom: 2px;">1. Termín realizace je stanoven na datové rozmezí: <strong>' . $data_sauna['delivery_time'] . '</strong></p>
<p style="padding-bottom: 2px;">2. Přesný termín realizace je stanoven nejpozději týden před zahájením realizace.</p>
<p style="padding-bottom: 2px;">3. Doba plnění: 1 pracovní den (8-12 hodin denně).
<br>K případné úpravě termínu realizace může dojít po vzájemné dohodě obou smluvních stran a to písemně. Prodávající si vyhrazuje právo upravit termín realizace při nepříznivých podnebných podmínkách, jak jsou sněhové kalamity apod. a při zásahu vyšší moci tj.: zásahy státu, provozní, dopravní a energetické poruchy, stávky, výluky či další objektivní překážky na straně prodávajícího jsou důvodem k odkladu plnění smluvních povinností na straně prodávajícího po dobu a v rozsahu účinnosti zmíněných událostí. Totéž platí, i když uvedené události nastali u subdodavatelů, či smluvních dopravců prodávajícího. </p>
<p style="padding-bottom: 2px;">4. Místo realizace: <strong><u>' . $data_sauna['delivery_address'] . '</u></strong></p>



<h2 style=" font-family: Roboto-Light, Helvetica; margin-bottom: 7px; font-size: 18px; float: left; margin-top: 20px;">čl. IV Práva a povinnosti kupujícího</h2>

<p style="padding-bottom: 2px;">1. Kupující je povinen zpřístupnit místo plnění po dobu sjednanou v článku III. této smlouvy.</p>
<p style="padding-bottom: 2px;">2. Kupující zajistí stavební přípravu dle pokynů prodávajícího. Stavební příprava tvoří přílohu č.2 této smlouvy. </p>
<p style="padding-bottom: 2px;">3. V případě, že kupující zamlčel některé skutečnosti, které vyplynou až při samotné realizaci a budou s nimi spojené více práce, výrazně převyšující rozsah domluvených prací, řídí se dodatečné práce ceníkem 400,- '.$currency['sign'].'/hod bez DPH.</p>
<p style="padding-bottom: 2px;">4. Kupující je povinen převzít předmět dodávky také v případě, kdy předmět dodávky má vady či nedodělky, které nebrání užívání předmětu dodávky. V takovém případě bude do předávacího protokolu zapsána poznámka, na jejímž základě budete probíhat reklamační řízení. V takovém případě bude provedeno zádržné 20% z celkové částky bez DPH do předání díla bez závad a nedodělků.</p>

<h2 style=" font-family: Roboto-Light, Helvetica; margin-bottom: 7px; font-size: 18px; float: left; margin-top: 20px;">čl. V Práva a povinnosti prodávajícího</h2>

<p style="padding-bottom: 2px;">1. Prodávající je povinen dodat předmět koupě dle specifikace, kterou tvoří příloha č. 1 této smlouvy.</p>
<p style="padding-bottom: 2px;">2. Prodávající seznámí kupujícího s průběhem realizace a nutnou stavební připraveností.</p>
<p style="padding-bottom: 2px;">3. Prodávající protokolárně předá předmět koupě kupujícímu a zaškolí jej do provozu.</p>


<h2 style=" font-family: Roboto-Light, Helvetica; margin-bottom: 7px; font-size: 18px; float: left; margin-top: 20px;">čl. VI Další práva a povinnosti stran</h2>

<p style="padding-bottom: 2px;">1. Vlastnictví předmětu koupě odpovídající v době uzavření této smlouvy částce <strong>' . $reverse_charge_final_price . '</strong> přechází na kupujícího až úplným zaplacením kupní ceny a podepsání předávacího protokolu.</p>
<p style="padding-bottom: 2px;">2. Obsah smlouvy je předmětem obchodního tajemství, strany se zavazují, že její obsah nesdělí bez předchozího písemného souhlasu druhé strany jakékoli třetí osobě. Při porušení tohoto ujednání si smluvní strany odpovídají za škodu, která by tím vznikla.</p>



<h2 style=" font-family: Roboto-Light, Helvetica; margin-bottom: 7px; font-size: 18px; float: left; margin-top: 20px;">čl. VII Záruka</h2>

<p style="padding-bottom: 2px;">1. Záruční doba začíná běžet ode dne předání zboží kupujícímu a podepsání předávacího protokolu.</p>
<p style="padding-bottom: 2px;">2. Záruka konstrukce sauny, dřevěného a spojovacího materiálu: 2 roky.</p>
<p style="padding-bottom: 2px;">3. Záruka elektronického zařízení (kamna, řídící jednotka, světla): 2 roky.</p>
<p style="padding-bottom: 2px;">4. Záruku nelze uplatnit na vady vzniklé nesprávným používáním, které je uvedeno v přiloženém manuálu.</p>
<p style="padding-bottom: 2px;">5. Vadu krytou zárukou musí kupující vytknout prodávajícímu bez zbytečného odkladu poté, kdy měl možnost předmět dodávky prohlédnout a vadu zjistit, nejpozději však v reklamační lhůtě určené délkou záruční doby.</p>


<pagebreak />
<div style="width: 100%; margin-bottom: 24px; text-align:center;">
' . $site_logo . '
</div>

<h2 style=" font-family: Roboto-Light, Helvetica; margin-bottom: 7px; font-size: 18px; float: left; margin-top: 20px;">čl. VIII Odstoupení od smlouvy</h2>

<p style="padding-bottom: 2px;">1. Kupující je oprávněn odstoupit od smlouvy před zahájením realizace a to nejpozději 48 hodin před sjednaným datem započetím realizace. V takovém případě je povinen uhradit manipulační poplatek ve výši 50% z kupní ceny stanovené v odstavci II. této smlouvy.</p>
<p style="padding-bottom: 2px;">2. Kupující je oprávněn odstoupit od smlouvy v případě, že prodávající nebyl schopen dodržet termín realizace či jinak porušil smluvní ujednání.</p>



<h2 style=" font-family: Roboto-Light, Helvetica; margin-bottom: 7px; font-size: 18px; float: left; margin-top: 20px;">čl. IX Závěrečná ujednání</h2>

<p style="padding-bottom: 8px;">Není-li touto smlouvou stanoveno jinak, řídí se práva a povinnosti smluvních stran jí založené příslušnými ustanoveními občanského zákoníku.</p>


<p style="padding-bottom: 2px;">1. Změny a doplňky této smlouvy lze provést pouze po dohodě obou smluvních stran formou písemného číslovaného dodatku.</p>
<p style="padding-bottom: 2px;">2. Tato smlouva je vyhotovena ve 2 stejnopisech, každé s platností originálu, přičemž každá smluvní strana obdrží po jednom vyhotovení.</p>
<p style="padding-bottom: 2px;">3. Tato smlouva nabývá platnosti a účinnosti podpisem poslední ze smluvních stran.</p>
<p style="padding-bottom: 2px;">4. Účastníci této smlouvy shodně prohlašují, že si tuto smlouvu před jejím podpisem řádně přečetli, že byla uzavřena po vzájemném projednání, podle jejich pravé a svobodné vůle. Obě smluvní strany potvrzují správnost a autentičnost této smlouvy svým vlastnoručním podpisem.</p>


<p style="padding-bottom: 2px; padding-top: 20px;"><strong>Přílohy:</strong></p>
<p style="padding-bottom: 2px;">1) Specifikace předmětu koupě</p>
<p style="padding-bottom: 2px;">2) Stavební příprava a technické podklady</p>
<p style="padding-bottom: 2px;">3) Všeobecné obchodní podmínky společnosti Wellness Trade, s. r. o.</p>

<table class="tablus2"  style="margin: 20px 0 0; width: 100%;">
<tbody>
<tr>
<td style="width: 50%;"><strong>Prodávající:</strong> Wellness Trade, s.r.o. - Michael Bäumel</td>
<td style="width: 50%;"><strong>Kupující:</strong> ' . $name . '</td>
</tr>
<tr>
<td style="width: 50%;">V Praze dne ' . date("d. m. Y") . '</td>
<td style="width: 50%;">V …………………………………… dne ……………………………</td>
</tr>
<tr>
<td style="width: 50%; padding: 0;"><img src="../../assets/images/wtrazitko.jpg"></td>
<td style="width: 50%;"></td>
</tr>
</table>

<pagebreak />
<div style="width: 100%; margin-bottom: 24px; text-align:center;">
' . $site_logo . '
</div>

<h2 style=" font-family: Roboto-Light, Helvetica; margin-bottom: 7px; font-size: 18px; float: left;">Příloha č.1</h2>

<p style="padding-bottom: 12px;">Předmět koupě zahrnuje saunovou kabinu <strong>' . returnpn($getclient['customer'], $product) . '</strong> (<strong>' . $data_sauna['dimension'] . '</strong> mm) s následující specifikací:</p>


<table class="tablus" style="margin: 10px auto; width: 86%;">
<thead>
<tr>
<td>Název</td>
<td>Specifikace</td>
</tr>
</thead>
<tbody>
' . $sauna_specifications . '
</tbody>
</table>

</div>
<div style="clear: both;"></div>

';
//==============================================================
    if (isset($_REQUEST['html']) && $_REQUEST['html']) {echo $html;exit;}
//==============================================================

    $mpdf->WriteHTML($html);

    $mpdf->Output($_SERVER['DOCUMENT_ROOT'] . '/admin/data/demands/contracts/Smlouva_o_dilo_' . $id . '.pdf', 'F');

}elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == '4') {

    $paramsquery = $mysqli->query('SELECT value FROM demands_specs_bridge WHERE specs_id = "5" AND client_id = "' . $getclient['id'] . '"') or die($mysqli->error);
    $params = mysqli_fetch_array($paramsquery);

    $provedeni = $params['value'];


    $data_pergola_query = $mysqli->query("SELECT * FROM demands_generate_pergola WHERE id = '$id'");
    $data_pergola = mysqli_fetch_array($data_pergola_query);


    $vat = 100 + $data['price_vat'];
    $total_price = $data_pergola['price_pergola'];

    $specs_demand = $mysqli->query("SELECT *, s.id as id FROM specs s, demands_specs_bridge d WHERE d.specs_id = s.id AND d.value != '' AND d.value != 'Ne' AND d.client_id = '" . $getclient['id'] . "' AND s.generate = 1 ORDER BY s.demand_order") or die($mysqli->error);

    $i = 4;
    $specifications = "";

    $pergola_specs = '-';
    while ($spec = mysqli_fetch_array($specs_demand)) {

        // if provedeni is individual
        if($provedeni == 'Individuální' && $spec['id'] == 96){
            $pergola_specs = $spec['value'];
        }
        
        if(!$spec['is_generated'] ){

            $total_price += $spec['price'];

        }else{

            $text = $spec['generate_text'] ?: '-';
            
            // screeny
            if($spec['id'] == 79 || $spec['id'] == 80 || $spec['id'] == 81 || $spec['id'] == 82 || $spec['id'] == 83 || $spec['id'] == 84) { $text = $spec['value']; }

            $specifications .= '<tr>
        <td>' . $spec['name'] . '</td>
        <td>' . $text . '</td>
        <td class="price">' . number_format($spec['price'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH</td>
        </tr>';

            $total_price += $spec['price'];

            $i++;

        }

    }

    $accessories_query = $mysqli->query("SELECT * FROM demands_accessories_bridge WHERE aggregate_id = '" . $getclient['id'] . "'") or die($mysqli->error);
    while ($accessory = mysqli_fetch_array($accessories_query)) {

        $title = $accessory['product_name'];
        $subtitle = $accessory['variation_values'] ?: '-';

        $specifications .= '<tr>
        <td>' . $title . '</td>
        <td>' . $subtitle . '</td>
        <td class="price">' . number_format($accessory['price'] * $accessory['quantity'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH</td>
        </tr>';

        $total_price += $accessory['price'] * $accessory['quantity'];

        $i++;
    }

    $dphprice = $total_price / 100 * $vat;

    $totalpriceddd = $total_price + $data_pergola['price_delivery'] + $data_pergola['price_montage'] - $data_pergola['discount'];

    $dphpricedelivery = $totalpriceddd / 100 * $vat;


    $first_deposit = $data['deposit'];
    $second_deposit = 100 - $data['deposit'];

    if (isset($data['deposit_type']) && $data['deposit_type'] == 'percentage') {

        $deposit_value = $data['deposit'] . '%';

    } elseif (isset($data['deposit_type']) && $data['deposit_type'] == 'money') {

        $count_deposit = number_format($data['deposit'] / 100 * $vat, 2, ',', ' ');

        $deposit_value = $count_deposit . ' '.$currency['sign'];

    }

    $reverse_charge_text = '';
    if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

        $first_price_calculate = $totalpriceddd / 100 * $first_deposit;
        $second_price_calculate = $totalpriceddd / 100 * $second_deposit;

        $first_price = number_format($first_price_calculate, 2, ',', ' ') . ',- '.$currency['sign'].' bez DPH';
        $second_price = number_format($second_price_calculate, 2, ',', ' ') . ',- '.$currency['sign'].' bez DPH';

        $reverse_charge_text = '<p>jedná se o přenesení daňové povinnosti podle § 92a zákona o dani z přidané hodnoty, kdy výši daně je povinen doplnit a přiznat plátce, pro kterého je plnění uskutečněno.</p>';

        $reverse_charge_final_price = number_format($totalpriceddd, 0, ',', ' ') . ' '.$currency['sign'].' bez DPH';

        $final_price = '<tr>
<td colspan="2">Cena pergoly vč. příslušenství a dopravy</td>
<td colspan="2">' . $reverse_charge_final_price . '</td>
</tr>';

    } else {

        $first_price_calculate = $dphpricedelivery / 100 * $first_deposit;
        $second_price_calculate = $dphpricedelivery / 100 * $second_deposit;

        $first_price = number_format($first_price_calculate, 2, ',', ' ') . ',- '.$currency['sign'].' s DPH';
        $second_price = number_format($second_price_calculate, 2, ',', ' ') . ',- '.$currency['sign'].' s DPH';

        $reverse_charge_dph = '<tr>
<td style="background-color: #d5edf2;">Cena předmětu koupě s ' . $data['price_vat'] . '% DPH</td>
<td style="background-color: #d5edf2;">-</td>
<td style="background-color: #d5edf2;">' . number_format($dphprice, 2, ',', ' ') . '  '.$currency['sign'].' s '. $data['price_vat'] . '% DPH</td>
</tr>';

//        echo $dphpricedelivery;
        $reverse_charge_final_price = '<div style="font-size: 13px; margin-bottom: 10px;">'.number_format($dphpricedelivery, 2, ',', ' ') . ' '.$currency['sign'].' s '. $data['price_vat'] . '% DPH</div>';

        $final_without_vat =  '<div style="margin-top: 10px; font-size: 10px;">'.number_format($totalpriceddd, 2, ',', ' ') . ' '.$currency['sign'].' bez DPH</div>';

        $final_price = '<tr>
<td colspan="2" rowspan="2">Cena pergoly vč. příslušenství a dopravy</td>
<td colspan="2">' . $final_without_vat . '</td>
</tr>
<tr style="border-top: 0 !important;">
<td colspan="2">' . $reverse_charge_final_price . '</td>
</tr>';

    }

    $discount = '';
    if (isset($data_pergola['discount']) && $data_pergola['discount'] != "" && $data_pergola['discount'] != 0) {

        $discount = '<tr>
<td style="background-color: #d5edf2;"><strong>Sleva</strong></td>
<td style="background-color: #d5edf2;">-</td>
<td class="price" style="background-color: #d5edf2;">' . number_format($data_pergola['discount'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH</td>
</tr>
<tr>
<td style="background-color: #d5edf2;">Cena za pergolu po slevě</td>
<td style="background-color: #d5edf2;">-</td>
<td class="price" style="background-color: #d5edf2;">' . number_format($data_pergola['price_pergola'] - $data_pergola['discount'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH</td>
</tr>';

        $i++;
        $i++;
    }


    $other_arrangements = $data['other_arrangements'] ? '<p style="padding-bottom: 2px;">11. ' . $data['other_arrangements'] . '</p>' : '';

    if (isset($data_pergola['delivery_address']) && $data_pergola['delivery_address'] == "" || $data_pergola['delivery_address'] == ', , 0') {

        $delivery_address = "";

    } else {

        $delivery_address = 'Místo dodávky: <strong><u>' . $data_pergola['delivery_address'] . '</u></strong>';

    }

    function warrantySign($warranty){
        if($warranty === '1'){
            return $warranty.' rok';
        }elseif($warranty > '4'){
            return $warranty.' let';
        }else{
            return $warranty.' roky';
        }
    }


        $warranty_type = '';

        $warranty = 'Prodávající poskytuje záruční podmínky'.$warranty_type.': '.warrantySign($data_pergola['warranty_first']).' na pergolu. Záruční podmínky jsou dále upraveny ve VOP.';

    if(file_exists($_SERVER['DOCUMENT_ROOT'] .'/admin/data/images/customer/' . $getclient['product'] . '-'.$provedeni.'.png')){

        $file_url = '../../../admin/data/images/customer/' . $getclient['product'] . '-'.$provedeni.'.png';

    }else{

        $file_url = '../../../admin/data/images/customer/' . $getclient['product'] . '.png';

    }


    $contact_info = '';
    if(!empty($name) && !empty($address) && !empty($billing['billing_email'])){

        $contact_info = '<p style="padding-bottom: 6px; font-size: 16px;"><strong>' . $name . '</strong></p>
    <p>' . $address . '</p>
    <p style="padding-bottom: 8px;">E-mail: <strong>' . $billing['billing_email'] . '</strong>, Tel.: <strong>' . number_format($billing['billing_phone'], 0, ',', ' ') . '</strong></p>';

    }


    $mpdf = new Mpdf\Mpdf([
            'disableJavaScript' => true,
            'mode' => 'utf-8',
            'compression' => 0, // disable object stream compression
        ]);

        $mpdf->SetTitle('Zálohová faktura ' . $invoice_id);
        $mpdf->SetAuthor('Wellness Trade, s. r. o.');
        $mpdf->SetCreator('Wellness Trade, s. r. o.');
        $mpdf->SetSubject('Zálohová faktura ' . $invoice_id);
        $mpdf->SetKeywords('zaloho faktura, invoice');

        $mpdf->useActiveForms = false;
        $mpdf->useSubstitutions = false;

//==============================================================

    $html = '
<style>
@page{
    sheet-size: 210mm 297mm;
    margin: 0;
    background-color: #FFFFFF;
}

body, div, p {
	font-family: "Roboto", "Helvetica";
	font-size: 10px;
	color: #000000;
}

strong { font-family: Roboto-Medium, Helvetica; font-weight: 500;}

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
	image-rendering: -moz-crisp-edges;		/* Firefox */
	image-rendering: -o-crisp-edges;		/* Opera */
	image-rendering: -webkit-optimize-contrast;/* Webkit (non-standard naming) */
	image-rendering: crisp-edges;
	-ms-interpolation-mode: nearest-neighbor;	/* IE (non-standard property) */
}


table {
    border-collapse: collapse;
}

table, th, td {
    border: 0;
}
table.bank-table td { padding: 3px 0; width: 150px;}


table.products {  color: #000; font-size: 13px;}

table.products td { text-align: center;border-bottom: 1px dotted #bedfe7; }


table.products td.first { text-align: left;}

table.products tr.head td { font-size: 11px; color: #2b2e39;}

table td.price { text-align: right; padding-right: 15px;} 

table.total {  color: #000; font-size: 13px;}

table.total td { text-align: center;border-bottom: 1px dotted #dcdcdc; padding: 13px 15px 12px;}





table.tablus tr { background-color: #e2f3f7; border: 1px solid #d4eef4;}

table.total td.first { padding-left: 25px; text-align: left;}

table.tablus thead tr {background-color: #cbe6ed; border-bottom: 1px solid #e9f6f9;}
table.tablus thead td {border-right: 1px solid #e9f6f9;}
table.tablus td { font-size: 9.5px; padding: 6px 15px 5px; text-align: center; border-right: 1px solid #d4eef4;}

table.tablus tfoot tr { background-color: #e2f3f7; border-top: 5px solid #c7e3ea;}

table.tablus tfoot td {font-weight: bold; font-family: Roboto-Light, Helvetica; font-size: 13px; padding: 14px 15px 14px;}




table.tablus2 tr { border: 1px solid #d4eef4;}

table.tablus2 td{ padding: 13px 15px 12px; text-align: center; border-right: 1px solid #d4eef4;}


</style>
<body>

<div style="width: 100%; margin-bottom: 40px; text-align:center;">
' . $site_logo . '
</div>

<div style="padding: 5px 60px 5px 60px;float: left; width: 100%;">

'.$contact_info.'

<h1 style=" font-family: Roboto-Light, Helvetica; margin-bottom: 20px; margin-top: 40px; font-size: 22px; text-align: center;">Cenová nabídka</h1>
<table class="tablus" style="margin: 10px 0; width: 100%;">
<thead>
<tr>
<td>Název</td>
<td>Specifikace</td>
<td>Cena</td>
<td>Fotografie</td>
</tr>
</thead>
<tbody>
<tr>
<td>' . returnpn($getclient['customer'], $getclient['product']) . ' - ' . $provedeni . '</td>
<td>' . $pergola_specs . '</td>
<td class="price">' . number_format($data_pergola['price_pergola'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH</td>
<td rowspan="' . $i . '"><img src="'.$file_url.'" width="115"></td>
</tr>

' . $discount . '

' . $specifications . '

<tr>
<td>Montáž</td>
<td>1 technik</td>
<td class="price">' . number_format($data_pergola['price_montage'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH</td>
</tr>

<tr>
<td>Doprava</td>
<td>-</td>
<td class="price">' . number_format($data_pergola['price_delivery'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH</td>
</tr>


</tbody>
<tfoot>
'.$final_price.'
</tfoot>
</table>

</div>
<div style="clear: both;"></div>

';
//==============================================================
    if (isset($_REQUEST['html']) && $_REQUEST['html']) {echo $html;exit;}
//==============================================================

    $mpdf->WriteHTML($html);

    $mpdf->Output($_SERVER['DOCUMENT_ROOT'] . '/admin/data/demands/budget_offers/Cenova_nabidka_' . $id . '.pdf', 'F');



}

$update_demand = $mysqli->query("UPDATE demands SET date_contract = now(), contract = 1 WHERE id = '$id'") or die($mysqli->error);

Header("Location:https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=" . $id . "&success=generate_contract");
exit;