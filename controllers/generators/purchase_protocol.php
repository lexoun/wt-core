<?php

if (!isset($_REQUEST['html'])) {$_REQUEST['html'] = '';}

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/functions.php";

$id = $_REQUEST['id'];

$getclientquery = $mysqli->query('SELECT *, DATE_FORMAT(date, "%d. %m. %Y") as dateformated, DATE_FORMAT(realization, "%d. %m. %Y") as realizationformated, DATE_FORMAT(realtodate, "%d. %m. %Y") as realtodateformat FROM demands WHERE id="' . $id . '"') or die($mysqli->error);
$getclient = mysqli_fetch_assoc($getclientquery);

if (isset($getclient['customer']) && ($getclient['customer'] == 0 || $getclient['customer'] == 1 || $getclient['customer'] == 4)) {

    $product = $getclient['product'];

} elseif (isset($getclient['customer']) && $getclient['customer'] == 3) {

    $product = $getclient['secondproduct'];

}

$brand_query = $mysqli->query("SELECT brand FROM warehouse_products WHERE connect_name = '$product'") or die($mysqli->error);
$brand = mysqli_fetch_assoc($brand_query);

if (isset($_REQUEST['type']) && $_REQUEST['type'] == '0') {

    $site_logo = '<img src="../../assets/images/saunahouse-shop.png" width="170" style="margin: 20px 60px 0; ">';

} elseif (isset($_REQUEST['type']) && ($_REQUEST['type'] == '1' || $_REQUEST['type'] == '4')) {

    $site_logo = '<img src="../../assets/images/spahouse-shop.png" width="160" style="margin: 20px 60px 0px; ">';

}

$data_query = $mysqli->query("SELECT * FROM demands_generate WHERE id = '$id'");
$data = mysqli_fetch_array($data_query);

$currency = currency($data['currency']);

$billing_query = $mysqli->query('SELECT * FROM addresses_billing WHERE id = "' . $getclient['billing_id'] . '"') or die($mysqli->error);
$billing = mysqli_fetch_assoc($billing_query);


function check_payment_pur($adv_invoice){

    global $mysqli;

    if (isset($adv_invoice['payment_method']) && $adv_invoice['payment_method'] == 'cash') {

        if (isset($adv_invoice['paid']) && $adv_invoice['paid'] != '0') {

            $purchase_invoice = 'uhrazeno';

        } else {

            $purchase_invoice = 'hotově';

        }

    } else {

        if (isset($adv_invoice['paid']) && $adv_invoice['paid'] != '0') {

            $purchase_invoice = 'uhrazeno';

        } else {

            $purchase_invoice = 'převodem';

        }

    }

    return $purchase_invoice;

}

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

    $rows = 4;
    $specifications = "";
    while ($spec = mysqli_fetch_array($specs_demand)) {

        if($spec['id'] == 16 && $spec['value'] == '2 speed 2,25 kW' && $provedeni != 'Gold' && $product == 'tahiti'){
            continue;
        }

        if($spec['id'] == 16 && $spec['value'] != '2 speed 2,25 kW') {

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

            $rows++;

        }

    }

//echo $total_price;


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

        $rows++;
    }


    $dphprice = $total_price / 100 * $vat;


    if($data_hottub['chemie_type'] == 0){ $data_hottub['price_chemie'] = 0; }

    $totalpriceddd = $total_price + $data_hottub['price_delivery'] + $data_hottub['price_montage'] + $data_hottub['price_chemie'] - $data_hottub['discount'];

    $dphpricedelivery = $totalpriceddd / 100 * $vat;

    if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

        $reverse_charge_final_price = number_format($totalpriceddd, 0, ',', ' ') .$currency['sign'].' bez DPH';

        $final_price = '<tr>
<td colspan="2">Cena vířivky vč. příslušenství a dopravy</td>
<td colspan="2">' . $reverse_charge_final_price . '</td>
</tr>';

    } else {

        $reverse_charge_dph = '<tr>
			<td style="background-color: #d5edf2;">Cena předmětu koupě s ' . $data['price_vat'] . '% DPH</td>
			<td style="background-color: #d5edf2;">-</td>
			<td style="background-color: #d5edf2;">' . number_format($dphprice, 0, ',', ' ') . ',- '.$currency['sign'].' s '. $data['price_vat'] . '% DPH</td>
			</tr>';

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

    if (isset($data['deposit_type']) && $data['deposit_type'] == 'percentage') {

        $deposits_placement = "";

        $i = 0;
        $display_num = 0;

        $inv = $data['invoices_number'];

        for ($inv; $inv > 0; $inv--) {

            $i++;

            if ($i == 1) {

                $calculate_first = $totalpriceddd / 100 * $data['deposit'];

                $invoice_deposit = $data['deposit'];

                if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

                    $invoice_price = number_format($calculate_first, 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH';

                } else {

                    $invoice_price = number_format($calculate_first / 100 * $vat, 0, ',', ' ') . ',- '.$currency['sign'].' s DPH';

                }

            } elseif ($i == 2) {

                $calculate_second = $totalpriceddd / 100 * $data['deposit_second'];

                $invoice_deposit = $data['deposit_second'];

                if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

                    $invoice_price = number_format($calculate_second, 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH';

                } else {

                    $invoice_price = number_format($calculate_second / 100 * $vat, 0, ',', ' ') . ',- '.$currency['sign'].' s DPH';

                }

            } elseif ($i == 3) {

                $calculate_third = $totalpriceddd / 100 * $data['deposit_third'];

                $invoice_deposit = $data['deposit_third'];

                if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

                    $invoice_price = number_format($calculate_third, 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH';

                } else {

                    $invoice_price = number_format($calculate_third / 100 * $vat, 0, ',', ' ') . ',- '.$currency['sign'].' s DPH';

                }

            } elseif ($i == 4) {

                $calculate_fourth = $totalpriceddd / 100 * $data['deposit_fourth'];

                $invoice_deposit = $data['deposit_fourth'];

                if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

                    $invoice_price = number_format($calculate_fourth, 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH';

                } else {

                    $invoice_price = number_format($calculate_fourth / 100 * $vat, 0, ',', ' ') . ',- '.$currency['sign'].' s DPH';

                }

            }

            $get_advance_inv = $mysqli->query("SELECT * FROM demands_advance_invoices WHERE demand_id = '$id' AND status = '$i'");
            $adv_invoice = mysqli_fetch_array($get_advance_inv);

            // Přeskočit stornované faktury
            if (!empty($adv_invoice['storno']) && $adv_invoice['storno'] == 1) { continue; }

            $display_num++;
            $purchase_invoice = check_payment_pur($adv_invoice);

            $deposits_placement = $deposits_placement . '<tr>
								<td>Záloha ' . $invoice_deposit . '% kupní ceny</td>
								<td>' . $invoice_price . '</td>
								<td>' . $purchase_invoice . '</td>
								</tr>';

        }

    } else {

        $deposits_placement = "";

        $i = 0;
        $display_num = 0;

        $inv = $data['invoices_number'];

        for ($inv; $inv > 0; $inv--) {

            $i++;

            if ($i == 1) {

                if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

                    $invoice_price = number_format($data['deposit'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH';

                } else {

                    $invoice_price = number_format($data['deposit'] / 100 * $vat, 0, ',', ' ') . ',- '.$currency['sign'].' s DPH';

                }

            } elseif ($i == 2) {

                if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

                    $invoice_price = number_format($data['deposit_second'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH';

                } else {

                    $invoice_price = number_format($data['deposit_second'] / 100 * $vat, 0, ',', ' ') . ',- '.$currency['sign'].' s DPH';

                }

            } elseif ($i == 3) {

                if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

                    $invoice_price = number_format($data['deposit_third'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH';

                } else {

                    $invoice_price = number_format($data['deposit_third'] / 100 * $vat, 0, ',', ' ') . ',- '.$currency['sign'].' s DPH';

                }

            } elseif ($i == 4) {

                if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

                    $invoice_price = number_format($data['deposit_fourth'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH';

                } else {

                    $invoice_price = number_format($data['deposit_fourth'] / 100 * $vat, 0, ',', ' ') . ',- '.$currency['sign'].' s DPH';

                }

            }

            $get_advance_inv = $mysqli->query("SELECT * FROM demands_advance_invoices WHERE demand_id = '$id' AND status = '$i'");
            $adv_invoice = mysqli_fetch_array($get_advance_inv);

            // Přeskočit stornované faktury
            if (!empty($adv_invoice['storno']) && $adv_invoice['storno'] == 1) { continue; }

            $display_num++;
            $purchase_invoice = check_payment_pur($adv_invoice);

            $deposits_placement = $deposits_placement . '<tr>
								<td>Záloha #' . $display_num . ' kupní ceny</td>
								<td>' . $invoice_price . '</td>
								<td>' . $purchase_invoice . '</td>
								</tr>';

        }

    }
    $discount = '';
    if ($data_hottub['discount'] != "" && $data_hottub['discount'] != 0) {

        $discount = '<tr>
<td style="background-color: #d5edf2;"><strong>Sleva</strong></td>
<td style="background-color: #d5edf2;">-</td>
<td class="price" style="background-color: #d5edf2;"><strong>' . number_format($data_hottub['discount'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH</strong></td>
</tr>
<tr>
<td style="background-color: #d5edf2;">Cena za vířivku po slevě</td>
<td style="background-color: #d5edf2;">-</td>
<td class="price" style="background-color: #d5edf2;">' . number_format($data_hottub['price_hottub'] - $data_hottub['discount'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH</td>
</tr>';

        $rows++;
        $rows++;
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

    if(!empty($start_chemie)){

        $specifications .= '<tr>
<td>Startovací chemie</td>
<td>' . $start_chemie . '</td>
<td class="price">' . number_format($data_hottub['price_chemie'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH</td>
</tr>';

    }

    $other_purchase_protocol = "";
    if ($data['other_purchase_protocol'] != "") {

        $other_purchase_protocol = '<p style="padding-bottom: 2px;">8. ' . $data['other_purchase_protocol'] . '</p>';
    }

    $hottub_specs = $brand['brand'] == 'IQue' ? $akryl . ', ' . $oplasteni . ' barva opláštění' : '';


    if(file_exists($_SERVER['DOCUMENT_ROOT'] .'/admin/data/images/customer/' . $getclient['product'] . '-'.$provedeni.'.png')){

        $file_url = '../../../admin/data/images/customer/' . $getclient['product'] . '-'.$provedeni.'.png';

    }else{

        $file_url = '../../../admin/data/images/customer/' . $getclient['product'] . '.png';

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


table td.price { text-align: right; padding-right: 15px;} 



table.tablus tr { background-color: #e2f3f7; border: 1px solid #d4eef4;}

table.total td.first { padding-left: 25px; text-align: left;}

table.tablus thead tr {background-color: #cbe6ed; border-bottom: 1px solid #e9f6f9;}
table.tablus thead td {border-right: 1px solid #e9f6f9;}
table.tablus td{ padding: 6px 15px 5px; font-size: 9.5px; text-align: center; border-right: 1px solid #d4eef4;}

table.tablus tfoot tr { background-color: #e2f3f7; border-top: 5px solid #c7e3ea;}

table.tablus tfoot td {font-weight: bold; font-family: Roboto-Light, Helvetica; font-size: 13px; padding: 14px 15px 14px;}



table.tablus2 tr { border: 1px solid #d4eef4;}

table.tablus2 td{ padding: 13px 15px 12px; text-align: center; border-right: 1px solid #d4eef4;}


</style>
<body>

<div style="width: 100%; margin-bottom: 18px; float: right; position: absolute;text-align:right;">
' . $site_logo . '
</div>

<div style="margin-top: 40px; padding: 40px 60px 5px 60px;float: left; display:block; width: 100%;">


<p style="padding-bottom: 6px; font-size: 16px;">1. <strong>Wellness Trade, s.r.o.</strong></p>
<p style="padding-bottom: 8px;">se sídlem Vrbova 1277/32, 147 00 Praha, IČ: 29154871, DIČ: CZ29154871</p>
<p>společnost zapsaná v obchodním rejstříku vedeném Městským soudem v Praze oddíl C, vložka 203387</p>
<p style="padding-bottom: 16px;">E-mail: obchod@spahouse.cz. Bankovní spojení: 2000364217/2010, (dále jen "<strong>předávající</strong>") na straně jedné a</p>

<p style="padding-bottom: 6px; font-size: 16px;">2. <strong>' . $name . '</strong></p>
<p>' . $address . '</p>
<p>E-mail: <strong>' . $billing['billing_email'] . '</strong>, Tel.: <strong>' . number_format($billing['billing_phone'], 0, ',', ' ') . '</strong>, (dále jen "<strong>přebírající</strong>") na straně druhé podepisují tento</p>
<h1 style=" font-family: Roboto-Light, Helvetica; margin-bottom: 4px; font-size: 24px; text-align: center;">Předávací protokol</h1>
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
<td rowspan="' . $rows . '"><img src="'.$file_url.'" width="115"></td>
</tr>
' . $discount . '

' . $specifications . '

' . $chemie_insert . '

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

<table class="tablus" style="margin: 10px 0; width: 100%;">
<thead>
<tr>
<td>Zálohy/doplatek</td>
<td>Částka</td>
<td>Stav</td>
</tr>
</thead>
<tbody>
' . $deposits_placement . '
</tbody>
</table>

<h2 style=" font-family: Roboto-Light, Helvetica; margin-bottom: 8px; font-size: 18px; float: left; margin-top: 10px;">Prohlášení</h2>
<p style="padding-bottom: 2px;">1. Předávající předal a přebírající přebral předmět předání (' . returnpn($getclient['customer'], $getclient['product']) . ' ' . $provedeni . ').</p>
<p style="padding-bottom: 2px;">2. Přebírající prohlašuje, že mu byl předmět předání předán řádně a plně funkční.</p>
<p style="padding-bottom: 2px;">3. Přebírající prohlašuje, že byl obeznámen s funkčností předmětu předání a byl tak plně zaškolen do jeho provozu.</p>
<p style="padding-bottom: 2px;">5. Předávající převzal od přebírajícího doplatek v hotovosti / nebo v podobě bankovního potvrzení o doplatku kupní ceny dle čl. I bod 2. kupní smlouvy.</p>
<p style="padding-bottom: 2px;">6. Stavební příprava:  DODRŽENA - NEDODRŽENA</p>
<p style="padding-bottom: 2px;">7. Revizní zpráva:  DODÁNA - NEDODÁNA</p>
' . $other_purchase_protocol . '
<table class="tablus2"  style="margin: 14px 0 0; width: 100%;">
<tbody>
<tr>
<td style="width: 50%;"><strong>Předávající:</strong> za Wellness Trade, s.r.o. - ……………………………………</td>
<td style="width: 50%;"><strong>Kupující:</strong> ' . $name . '</td>
</tr>
<tr>
<td style="width: 50%;">V …………………………………… dne ……………………………</td>
<td style="width: 50%;">V …………………………………… dne ……………………………</td>
</tr>
<tr>
<td style="width: 50%; padding: 0;"><img src="../../assets/images/razitko-bez-podpisu.jpg" width="130" style="padding: 14px 30px 14px"></td>
<td style="width: 50%;"></td>
</tr>
</table>

</div>
<div style="clear: both;"></div>

';
//==============================================================
    if (isset($_REQUEST['html']) && $_REQUEST['html']) {echo $html;exit;}
//==============================================================

    $mpdf->WriteHTML($html);

    $mpdf->Output($_SERVER['DOCUMENT_ROOT'] . '/admin/data/demands/protocols/Predavaci_protokol_v_' . $id . '.pdf', 'F');

} elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == 0) {

    $rows = 5;

    $product_type = 'sauny';

    if (isset($getclient['customer']) && $getclient['customer'] == 0) {$product = $getclient['product'];} elseif (isset($getclient['customer']) && $getclient['customer'] == 3) {$product = $getclient['secondproduct'];}

    $data_sauna_query = $mysqli->query("SELECT * FROM demands_generate_sauna WHERE id = '$id'");
    $data_sauna = mysqli_fetch_array($data_sauna_query);

    $vat = 100 + $data['price_vat'];

    $totalprice = $data_sauna['price_sauna'];

    $dphprice = $totalprice / 100 * $vat;

    $totalpriceddd = $totalprice + $data_sauna['price_delivery'] + $data_sauna['price_montage'] - $data_sauna['discount'];

    $dphpricedelivery = $totalpriceddd / 100 * $vat;

    if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

        $reverse_charge_final_price = number_format($totalpriceddd, 0, ',', ' ') . ' '.$currency['sign'].' bez DPH';

    } else {

        $reverse_charge_dph = '<tr>
			<td style="background-color: #d5edf2;">Cena předmětu koupě s ' . $data['price_vat'] . '% DPH</td>
			<td style="background-color: #d5edf2;">-</td>
			<td style="background-color: #d5edf2;">' . number_format($dphprice, 0, ',', ' ') . ',- '.$currency['sign'].' s DPH</td>
			</tr>';

        $reverse_charge_final_price = number_format($dphpricedelivery, 0, ',', ' ') . ' '.$currency['sign'].' s DPH';

    }

    if (isset($data['deposit_type']) && $data['deposit_type'] == 'percentage') {

        $deposits_placement = "";

        $i = 0;
        $display_num = 0;

        $inv = $data['invoices_number'];

        for ($inv; $inv > 0; $inv--) {

            $i++;

            if ($i == 1) {

                $calculate_first = $totalpriceddd / 100 * $data['deposit'];

                $invoice_deposit = $data['deposit'];

                if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

                    $invoice_price = number_format($calculate_first, 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH';

                } else {

                    $invoice_price = number_format($calculate_first / 100 * $vat, 0, ',', ' ') . ',- '.$currency['sign'].' s DPH';

                }

            } elseif ($i == 2) {

                $calculate_second = $totalpriceddd / 100 * $data['deposit_second'];

                $invoice_deposit = $data['deposit_second'];

                if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

                    $invoice_price = number_format($calculate_second, 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH';

                } else {

                    $invoice_price = number_format($calculate_second / 100 * $vat, 0, ',', ' ') . ',- '.$currency['sign'].' s DPH';

                }

            } elseif ($i == 3) {

                $calculate_third = $totalpriceddd / 100 * $data['deposit_third'];

                $invoice_deposit = $data['deposit_third'];

                if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

                    $invoice_price = number_format($calculate_third, 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH';

                } else {

                    $invoice_price = number_format($calculate_third / 100 * $vat, 0, ',', ' ') . ',- '.$currency['sign'].' s DPH';

                }

            } elseif ($i == 4) {

                $calculate_fourth = $totalpriceddd / 100 * $data['deposit_fourth'];

                $invoice_deposit = $data['deposit_fourth'];

                if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

                    $invoice_price = number_format($calculate_fourth, 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH';

                } else {

                    $invoice_price = number_format($calculate_fourth / 100 * $vat, 0, ',', ' ') . ',- '.$currency['sign'].' s DPH';

                }

            }

            $get_advance_inv = $mysqli->query("SELECT * FROM demands_advance_invoices WHERE demand_id = '$id' AND status = '$i'");
            $adv_invoice = mysqli_fetch_array($get_advance_inv);

            // Přeskočit stornované faktury
            if (!empty($adv_invoice['storno']) && $adv_invoice['storno'] == 1) { continue; }

            $display_num++;
            $purchase_invoice = check_payment_pur($adv_invoice);

            $deposits_placement = $deposits_placement . '<tr>
								<td>Záloha ' . $invoice_deposit . '% kupní ceny</td>
								<td>' . $invoice_price . '</td>
								<td>' . $purchase_invoice . '</td>
								</tr>';

        }

    } else {

        $deposits_placement = "";

        $i = 0;
        $display_num = 0;

        $inv = $data['invoices_number'];

        for ($inv; $inv > 0; $inv--) {

            $i++;

            if ($i == 1) {

                if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

                    $invoice_price = number_format($data['deposit'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH';

                } else {

                    $invoice_price = number_format($data['deposit'] / 100 * $vat, 0, ',', ' ') . ',- '.$currency['sign'].' s DPH';

                }

            } elseif ($i == 2) {

                if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

                    $invoice_price = number_format($data['deposit_second'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH';

                } else {

                    $invoice_price = number_format($data['deposit_second'] / 100 * $vat, 0, ',', ' ') . ',- '.$currency['sign'].' s DPH';

                }

            } elseif ($i == 3) {

                if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

                    $invoice_price = number_format($data['deposit_third'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH';

                } else {

                    $invoice_price = number_format($data['deposit_third'] / 100 * $vat, 0, ',', ' ') . ',- '.$currency['sign'].' s DPH';

                }

            } elseif ($i == 4) {

                if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

                    $invoice_price = number_format($data['deposit_fourth'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH';

                } else {

                    $invoice_price = number_format($data['deposit_fourth'] / 100 * $vat, 0, ',', ' ') . ',- '.$currency['sign'].' s DPH';

                }

            }

            $get_advance_inv = $mysqli->query("SELECT * FROM demands_advance_invoices WHERE demand_id = '$id' AND status = '$i'");
            $adv_invoice = mysqli_fetch_array($get_advance_inv);

            // Přeskočit stornované faktury
            if (!empty($adv_invoice['storno']) && $adv_invoice['storno'] == 1) { continue; }

            $display_num++;
            $purchase_invoice = check_payment_pur($adv_invoice);

            $deposits_placement = $deposits_placement . '<tr>
								<td>Záloha #' . $display_num . ' kupní ceny</td>
								<td>' . $invoice_price . '</td>
								<td>' . $purchase_invoice . '</td>
								</tr>';

        }

    }
    $discount = '';
    if ($data_sauna['discount'] != "" && $data_sauna['discount'] != 0) {

        $discount = '<tr>
<td style="background-color: #d5edf2;"><strong>Sleva</strong></td>
<td style="background-color: #d5edf2;">-</td>
<td class="price" style="background-color: #d5edf2;"><strong>' . number_format($data_sauna['discount'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH</strong></td>
</tr>';

        $rows++;
    }

    if (isset($data['additional_payment']) && $data['additional_payment'] == 'bankwire') {

        $additional_payment = 'Převodem';

    } elseif (isset($data['additional_payment']) && $data['additional_payment'] == 'cash') {

        $additional_payment = 'Hotově';

    } elseif (isset($data['additional_payment']) && $data['additional_payment'] == 'card') {

        $additional_payment = 'Platební kartou';

    }

    $other_purchase_protocol = "";
    if ($data['other_purchase_protocol'] != "") {

        $other_purchase_protocol = '<p style="padding-bottom: 2px;">8. ' . $data['other_purchase_protocol'] . '</p>';
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

table.products td { text-align: center;border-bottom: 1px dotted #bedfe7;}


table.products td.first { text-align: left;}

table.products tr.head td { font-size: 11px; color: #2b2e39;}



table.total {  color: #000; font-size: 13px;}

table.total td { text-align: center;border-bottom: 1px dotted #dcdcdc; padding: 13px 15px 12px;}





table.tablus tr { background-color: #e2f3f7; border: 1px solid #d4eef4;}

table.total td.first { padding-left: 25px; text-align: left;}

table.tablus thead tr {background-color: #cbe6ed; border-bottom: 1px solid #e9f6f9;}
table.tablus thead td {border-right: 1px solid #e9f6f9;}
table.tablus td{ padding: 6px 15px 5px; font-size: 9.5px; text-align: center; border-right: 1px solid #d4eef4;}

table.tablus tfoot tr { background-color: #e2f3f7; border-top: 5px solid #c7e3ea;}

table.tablus tfoot td {font-weight: bold; font-family: Roboto-Light, Helvetica; font-size: 13px; padding: 10px 15px 10px;}


table td.price { text-align: right; padding-right: 15px;} 


table.tablus2 tr { border: 1px solid #d4eef4;}

table.tablus2 td{ padding: 13px 15px 12px; text-align: center; border-right: 1px solid #d4eef4;}


</style>
<body>

<div style="width: 100%; margin-bottom: 18px; float: right; position: absolute;text-align:right;">
' . $site_logo . '
</div>

<div style="margin-top: 40px; padding: 40px 60px 5px 60px;float: left; display:block; width: 100%;">


<p style="padding-bottom: 6px; font-size: 16px;">1. <strong>Wellness Trade, s.r.o.</strong></p>
<p style="padding-bottom: 8px;">se sídlem Vrbova 1277/32, 147 00 Praha, IČ: 29154871, DIČ: CZ29154871</p>
<p>společnost zapsaná v obchodním rejstříku vedeném Městským soudem v Praze oddíl C, vložka 203387</p>
<p style="padding-bottom: 16px;">E-mail: obchod@spahouse.cz. Bankovní spojení: 2000364217/2010, (dále jen "<strong>předávající</strong>") na straně jedné a</p>

<p style="padding-bottom: 6px; font-size: 16px;">2. <strong>' . $name . '</strong></p>
<p>' . $address . '</p>
<p>E-mail: <strong>' . $billing['billing_email'] . '</strong>, Tel.: <strong>' . number_format($billing['billing_phone'], 0, ',', ' ') . '</strong>, (dále jen "<strong>přebírající</strong>") na straně druhé podepisují tento</p>
<h1 style=" font-family: Roboto-Light, Helvetica; margin-bottom: 4px; font-size: 24px; text-align: center;">Předávací protokol</h1>
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
<td>' . $data_sauna['dimension'] . '</td>
<td class="price">' . number_format($data_sauna['price_sauna'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH</td>
<td rowspan="' . $rows . '"><img src="../../../admin/data/images/customer/' . $product . '.png"></td>
</tr>

' . $discount . '

<tr>
<td style="background-color: #d5edf2;">Cena za saunu vč. příslušenství</td>
<td style="background-color: #d5edf2;">-</td>
<td class="price" style="background-color: #d5edf2;">' . number_format($totalprice, 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH</td>
</tr>

' . $reverse_charge_dph . '


<tr>
<td>Montáž</td>
<td>2 technici</td>
<td class="price">' . number_format($data_sauna['price_montage'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH</td>
</tr>

<tr>
<td>Doprava</td>
<td>-</td>
<td class="price">' . number_format($data_sauna['price_delivery'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH</td>
</tr>



</tbody>
<tfoot>
<tr>
<td colspan="2">Cena '.$product_type.' vč. příslušenství a dopravy</td>
<td colspan="2">' . $reverse_charge_final_price . '</td>
</tr>
</tfoot>
</table>

<table class="tablus" style="margin: 10px 0; width: 100%;">
<thead>
<tr>
<td>Zálohy/doplatek</td>
<td>Částka</td>
<td>Stav</td>
</tr>
</thead>
<tbody>
' . $deposits_placement . '
</tbody>
</table>

<h2 style=" font-family: Roboto-Light, Helvetica; margin-bottom: 8px; font-size: 18px; float: left; margin-top: 10px;">Prohlášení</h2>
<p style="padding-bottom: 2px;">1. Předávající předal a přebírající přebral předmět předání (' . returnpn($getclient['customer'], $product) . ' - ' . $data_sauna['dimension'] . ' mm).</p>
<p style="padding-bottom: 2px;">2. Přebírající prohlašuje, že mu byl předmět předání předán řádně a plně funkční.</p>
<p style="padding-bottom: 2px;">3. Přebírající prohlašuje, že byl obeznámen s funkčností předmětu předání a byl tak plně zaškolen do jeho provozu.</p>
<p style="padding-bottom: 2px;">5. Předávající převzal od přebírajícího doplatek v hotovosti / nebo v podobě bankovního potvrzení o doplatku kupní ceny dle čl. I bod 2. kupní smlouvy.</p>
<p style="padding-bottom: 2px;">6. Stavební příprava:  DODRŽENA - NEDODRŽENA</p>
<p style="padding-bottom: 2px;">7. Revizní zpráva:  DODÁNA - NEDODÁNA</p>
' . $other_purchase_protocol . '
<table class="tablus2"  style="margin: 14px 0 0; width: 100%;">
<tbody>
<tr>
<td style="width: 50%;"><strong>Předávající:</strong> za Wellness Trade, s.r.o. - ……………………………………</td>
<td style="width: 50%;"><strong>Kupující:</strong> ' . $name . '</td>
</tr>
<tr>
<td style="width: 50%;">V …………………………………… dne ……………………………</td>
<td style="width: 50%;">V …………………………………… dne ……………………………</td>
</tr>
<tr>
<td style="width: 50%; padding: 0;"><img src="../../assets/images/razitko-bez-podpisu.jpg" width="130" style="padding: 14px 30px 14px"></td>
<td style="width: 50%;"></td>
</tr>
</table>

</div>
<div style="clear: both;"></div>

';
//==============================================================
    if (isset($_REQUEST['html']) && $_REQUEST['html']) {echo $html;exit;}
//==============================================================

    $mpdf->WriteHTML($html);

    $mpdf->Output($_SERVER['DOCUMENT_ROOT'] . '/admin/data/demands/protocols/Predavaci_protokol_s_' . $id . '.pdf', 'F');

}elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == '4') {

    $product_type = 'pergoly';

    $paramsquery = $mysqli->query('SELECT value FROM demands_specs_bridge WHERE specs_id = "5" AND client_id = "' . $getclient['id'] . '"') or die($mysqli->error);
    $params = mysqli_fetch_array($paramsquery);

    $provedeni = $params['value'];

    $data_pergola_query = $mysqli->query("SELECT * FROM demands_generate_pergola WHERE id = '$id'");
    $data_pergola = mysqli_fetch_array($data_pergola_query);


    $vat = 100 + $data['price_vat'];
    $total_price = $data_pergola['price_pergola'];



    $specs_demand = $mysqli->query("SELECT *, s.id as id FROM specs s, demands_specs_bridge d WHERE d.specs_id = s.id AND d.value != '' AND d.value != 'Ne' AND d.client_id = '" . $getclient['id'] . "' AND s.generate = 1 ORDER BY s.demand_order") or die($mysqli->error);

    $rows = 4;
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

            $rows++;

        }

    }

//echo $total_price;


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

        $rows++;
    }


    $dphprice = $total_price / 100 * $vat;



    $totalpriceddd = $total_price + $data_pergola['price_delivery'] + $data_pergola['price_montage'] - $data_pergola['discount'];

    $dphpricedelivery = $totalpriceddd / 100 * $vat;

    if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

        $reverse_charge_final_price = number_format($totalpriceddd, 0, ',', ' ') .$currency['sign'].' bez DPH';

        $final_price = '<tr>
<td colspan="2">Cena pergoly vč. příslušenství a dopravy</td>
<td colspan="2">' . $reverse_charge_final_price . '</td>
</tr>';

    } else {

        $reverse_charge_dph = '<tr>
			<td style="background-color: #d5edf2;">Cena předmětu koupě s ' . $data['price_vat'] . '% DPH</td>
			<td style="background-color: #d5edf2;">-</td>
			<td style="background-color: #d5edf2;">' . number_format($dphprice, 0, ',', ' ') . ',- '.$currency['sign'].' s '. $data['price_vat'] . '% DPH</td>
			</tr>';

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

    if (isset($data['deposit_type']) && $data['deposit_type'] == 'percentage') {

        $deposits_placement = "";

        $i = 0;
        $display_num = 0;

        $inv = $data['invoices_number'];

        for ($inv; $inv > 0; $inv--) {

            $i++;

            if ($i == 1) {

                $calculate_first = $totalpriceddd / 100 * $data['deposit'];

                $invoice_deposit = $data['deposit'];

                if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

                    $invoice_price = number_format($calculate_first, 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH';

                } else {

                    $invoice_price = number_format($calculate_first / 100 * $vat, 0, ',', ' ') . ',- '.$currency['sign'].' s DPH';

                }

            } elseif ($i == 2) {

                $calculate_second = $totalpriceddd / 100 * $data['deposit_second'];

                $invoice_deposit = $data['deposit_second'];

                if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

                    $invoice_price = number_format($calculate_second, 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH';

                } else {

                    $invoice_price = number_format($calculate_second / 100 * $vat, 0, ',', ' ') . ',- '.$currency['sign'].' s DPH';

                }

            } elseif ($i == 3) {

                $calculate_third = $totalpriceddd / 100 * $data['deposit_third'];

                $invoice_deposit = $data['deposit_third'];

                if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

                    $invoice_price = number_format($calculate_third, 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH';

                } else {

                    $invoice_price = number_format($calculate_third / 100 * $vat, 0, ',', ' ') . ',- '.$currency['sign'].' s DPH';

                }

            } elseif ($i == 4) {

                $calculate_fourth = $totalpriceddd / 100 * $data['deposit_fourth'];

                $invoice_deposit = $data['deposit_fourth'];

                if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

                    $invoice_price = number_format($calculate_fourth, 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH';

                } else {

                    $invoice_price = number_format($calculate_fourth / 100 * $vat, 0, ',', ' ') . ',- '.$currency['sign'].' s DPH';

                }

            }

            $get_advance_inv = $mysqli->query("SELECT * FROM demands_advance_invoices WHERE demand_id = '$id' AND status = '$i'");
            $adv_invoice = mysqli_fetch_array($get_advance_inv);

            // Přeskočit stornované faktury
            if (!empty($adv_invoice['storno']) && $adv_invoice['storno'] == 1) { continue; }

            $display_num++;
            $purchase_invoice = check_payment_pur($adv_invoice);

            $deposits_placement = $deposits_placement . '<tr>
								<td>Záloha ' . $invoice_deposit . '% kupní ceny</td>
								<td>' . $invoice_price . '</td>
								<td>' . $purchase_invoice . '</td>
								</tr>';

        }

    } else {

        $deposits_placement = "";

        $i = 0;
        $display_num = 0;

        $inv = $data['invoices_number'];

        for ($inv; $inv > 0; $inv--) {

            $i++;

            if ($i == 1) {

                if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

                    $invoice_price = number_format($data['deposit'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH';

                } else {

                    $invoice_price = number_format($data['deposit'] / 100 * $vat, 0, ',', ' ') . ',- '.$currency['sign'].' s DPH';

                }

            } elseif ($i == 2) {

                if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

                    $invoice_price = number_format($data['deposit_second'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH';

                } else {

                    $invoice_price = number_format($data['deposit_second'] / 100 * $vat, 0, ',', ' ') . ',- '.$currency['sign'].' s DPH';

                }

            } elseif ($i == 3) {

                if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

                    $invoice_price = number_format($data['deposit_third'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH';

                } else {

                    $invoice_price = number_format($data['deposit_third'] / 100 * $vat, 0, ',', ' ') . ',- '.$currency['sign'].' s DPH';

                }

            } elseif ($i == 4) {

                if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

                    $invoice_price = number_format($data['deposit_fourth'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH';

                } else {

                    $invoice_price = number_format($data['deposit_fourth'] / 100 * $vat, 0, ',', ' ') . ',- '.$currency['sign'].' s DPH';

                }

            }

            $get_advance_inv = $mysqli->query("SELECT * FROM demands_advance_invoices WHERE demand_id = '$id' AND status = '$i'");
            $adv_invoice = mysqli_fetch_array($get_advance_inv);

            // Přeskočit stornované faktury
            if (!empty($adv_invoice['storno']) && $adv_invoice['storno'] == 1) { continue; }

            $display_num++;
            $purchase_invoice = check_payment_pur($adv_invoice);

            $deposits_placement = $deposits_placement . '<tr>
								<td>Záloha #' . $display_num . ' kupní ceny</td>
								<td>' . $invoice_price . '</td>
								<td>' . $purchase_invoice . '</td>
								</tr>';

        }

    }
    $discount = '';
    if ($data_pergola['discount'] != "" && $data_pergola['discount'] != 0) {

        $discount = '<tr>
<td style="background-color: #d5edf2;"><strong>Sleva</strong></td>
<td style="background-color: #d5edf2;">-</td>
<td class="price" style="background-color: #d5edf2;"><strong>' . number_format($data_pergola['discount'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH</strong></td>
</tr>
<tr>
<td style="background-color: #d5edf2;">Cena za pergolu po slevě</td>
<td style="background-color: #d5edf2;">-</td>
<td class="price" style="background-color: #d5edf2;">' . number_format($data_pergola['price_pergola'] - $data_pergola['discount'], 0, ',', ' ') . ',- '.$currency['sign'].' bez DPH</td>
</tr>';

        $rows++;
        $rows++;
    }

    $other_purchase_protocol = "";
    if ($data['other_purchase_protocol'] != "") {

        $other_purchase_protocol = '<p style="padding-bottom: 2px;">8. ' . $data['other_purchase_protocol'] . '</p>';
    }


    if(file_exists($_SERVER['DOCUMENT_ROOT'] .'/admin/data/images/customer/' . $getclient['product'] . '-'.$provedeni.'.png')){

        $file_url = '../../../admin/data/images/customer/' . $getclient['product'] . '-'.$provedeni.'.png';

    }else{

        $file_url = '../../../admin/data/images/customer/' . $getclient['product'] . '.png';

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


table td.price { text-align: right; padding-right: 15px;} 



table.tablus tr { background-color: #e2f3f7; border: 1px solid #d4eef4;}

table.total td.first { padding-left: 25px; text-align: left;}

table.tablus thead tr {background-color: #cbe6ed; border-bottom: 1px solid #e9f6f9;}
table.tablus thead td {border-right: 1px solid #e9f6f9;}
table.tablus td{ padding: 6px 15px 5px; font-size: 9.5px; text-align: center; border-right: 1px solid #d4eef4;}

table.tablus tfoot tr { background-color: #e2f3f7; border-top: 5px solid #c7e3ea;}

table.tablus tfoot td {font-weight: bold; font-family: Roboto-Light, Helvetica; font-size: 13px; padding: 14px 15px 14px;}



table.tablus2 tr { border: 1px solid #d4eef4;}

table.tablus2 td{ padding: 13px 15px 12px; text-align: center; border-right: 1px solid #d4eef4;}


</style>
<body>

<div style="width: 100%; margin-bottom: 18px; float: right; position: absolute;text-align:right;">
' . $site_logo . '
</div>

<div style="margin-top: 40px; padding: 40px 60px 5px 60px;float: left; display:block; width: 100%;">


<p style="padding-bottom: 6px; font-size: 16px;">1. <strong>Wellness Trade, s.r.o.</strong></p>
<p style="padding-bottom: 8px;">se sídlem Vrbova 1277/32, 147 00 Praha, IČ: 29154871, DIČ: CZ29154871</p>
<p>společnost zapsaná v obchodním rejstříku vedeném Městským soudem v Praze oddíl C, vložka 203387</p>
<p style="padding-bottom: 16px;">E-mail: obchod@spahouse.cz. Bankovní spojení: 2000364217/2010, (dále jen "<strong>předávající</strong>") na straně jedné a</p>

<p style="padding-bottom: 6px; font-size: 16px;">2. <strong>' . $name . '</strong></p>
<p>' . $address . '</p>
<p>E-mail: <strong>' . $billing['billing_email'] . '</strong>, Tel.: <strong>' . number_format($billing['billing_phone'], 0, ',', ' ') . '</strong>, (dále jen "<strong>přebírající</strong>") na straně druhé podepisují tento</p>
<h1 style=" font-family: Roboto-Light, Helvetica; margin-bottom: 4px; font-size: 24px; text-align: center;">Předávací protokol</h1>
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
<td rowspan="' . $rows . '"><img src="'.$file_url.'" width="115"></td>
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

<table class="tablus" style="margin: 10px 0; width: 100%;">
<thead>
<tr>
<td>Zálohy/doplatek</td>
<td>Částka</td>
<td>Stav</td>
</tr>
</thead>
<tbody>
' . $deposits_placement . '
</tbody>
</table>

<h2 style=" font-family: Roboto-Light, Helvetica; margin-bottom: 8px; font-size: 18px; float: left; margin-top: 10px;">Prohlášení</h2>
<p style="padding-bottom: 2px;">1. Předávající předal a přebírající přebral předmět předání (' . returnpn($getclient['customer'], $getclient['product']) . ' ' . $provedeni . ').</p>
<p style="padding-bottom: 2px;">2. Přebírající prohlašuje, že mu byl předmět předání předán řádně a plně funkční.</p>
<p style="padding-bottom: 2px;">3. Přebírající prohlašuje, že byl obeznámen s funkčností předmětu předání a byl tak plně zaškolen do jeho provozu.</p>
<p style="padding-bottom: 2px;">5. Předávající převzal od přebírajícího doplatek v hotovosti / nebo v podobě bankovního potvrzení o doplatku kupní ceny dle čl. I bod 2. kupní smlouvy.</p>
<p style="padding-bottom: 2px;">6. Stavební příprava:  DODRŽENA - NEDODRŽENA</p>
<p style="padding-bottom: 2px;">7. Revizní zpráva:  DODÁNA - NEDODÁNA</p>
' . $other_purchase_protocol . '
<table class="tablus2"  style="margin: 14px 0 0; width: 100%;">
<tbody>
<tr>
<td style="width: 50%;"><strong>Předávající:</strong> za Wellness Trade, s.r.o. - ……………………………………</td>
<td style="width: 50%;"><strong>Kupující:</strong> ' . $name . '</td>
</tr>
<tr>
<td style="width: 50%;">V …………………………………… dne ……………………………</td>
<td style="width: 50%;">V …………………………………… dne ……………………………</td>
</tr>
<tr>
<td style="width: 50%; padding: 0;"><img src="../../assets/images/razitko-bez-podpisu.jpg" width="130" style="padding: 14px 30px 14px"></td>
<td style="width: 50%;"></td>
</tr>
</table>

</div>
<div style="clear: both;"></div>

';
//==============================================================
    if (isset($_REQUEST['html']) && $_REQUEST['html']) {echo $html;exit;}
//==============================================================

    $mpdf->WriteHTML($html);

    $mpdf->Output($_SERVER['DOCUMENT_ROOT'] . '/admin/data/demands/protocols/Predavaci_protokol_p_' . $id . '.pdf', 'F');

}

Header("Location:https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=" . $id . "&success=generate_contract");
exit;