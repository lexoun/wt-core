<?php

if (!isset($_REQUEST['html'])) {$_REQUEST['html'] = '';}

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/functions.php";

$id = $_REQUEST['client_id'];

$getclientquery = $mysqli->query('SELECT *, DATE_FORMAT(date, "%d. %m. %Y") as dateformated, DATE_FORMAT(realization, "%d. %m. %Y") as realizationformated, DATE_FORMAT(realtodate, "%d. %m. %Y") as realtodateformat FROM demands WHERE id="' . $id . '"') or die($mysqli->error);
$getclient = mysqli_fetch_assoc($getclientquery);

$advance_invoice_query = $mysqli->query("SELECT *, DATE_FORMAT(date, '%d. %m. %Y') as dateformated FROM demands_advance_invoices WHERE id = '" . $_REQUEST['advance_invoice_id'] . "'");
$advance_invoice = mysqli_fetch_array($advance_invoice_query);

$date = new DateTime();

$insert_date = date_format($date, "Y-m-d H:i:s");

$invoice_datetime = date_format($date, "d. m. Y H:i:s");

$invoice_date = date_format($date, "d. m. Y");

$proof_invoice_query = $mysqli->query("SELECT id, id_prefix FROM demands_advance_invoices_proof WHERE advance_invoice = '" . $_REQUEST['advance_invoice_id'] . "'");

if (mysqli_num_rows($proof_invoice_query) == 1) {

    $proof_invoice = mysqli_fetch_array($proof_invoice_query);

    $invoice_id_infix = $proof_invoice['id'];

    $year_prefix = $proof_invoice['id_prefix'];

} else {

    $year_prefix = date("y");

    $insert_query = $mysqli->query("INSERT INTO demands_advance_invoices_proof (id_prefix, demand_id, advance_invoice, date, date_payment, price_without_vat, total_vat, rounded, total_price, type) VALUES ('$year_prefix', '$id','" . $advance_invoice['id'] . "', '$insert_date', '$insert_date', '" . $advance_invoice['price_without_vat'] . "', '" . $advance_invoice['total_vat'] . "', '" . $advance_invoice['rounded'] . "', '" . $advance_invoice['total_price'] . "', '" . $_REQUEST['type'] . "')") or die($mysqli->error);

    $invoice_id_infix = $mysqli->insert_id;

    $invoice_id_infix = str_pad($invoice_id_infix, 5, "0", STR_PAD_LEFT);

}

$invoice_id = $year_prefix . 'IN' . $invoice_id_infix;

$data_query = $mysqli->query("SELECT * FROM demands_generate WHERE id = '$id'");
$data = mysqli_fetch_array($data_query);

$date_payment = date('d. m. Y', strtotime($_POST['date_payment']));

$first_deposit = $data['deposit'];
$second_deposit = $data['deposit_second'];
$third_deposit = $data['deposit_third'];
$fourth_deposit = $data['deposit_fourth'];

if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

    $vat_real = 0;

} else {

    $vat_real = $data['price_vat'];

}

$totalvat = 100 + $vat_real;

if (isset($advance_invoice['payment_method']) && $advance_invoice['payment_method'] == 'bankwire') {

    $pay_method = 'Převodem';

} elseif (isset($advance_invoice['payment_method']) && $advance_invoice['payment_method'] == 'cash') {

    $pay_method = 'Hotově';

}

if (isset($data['billing_company']) && $data['billing_company'] != "" && isset($data['billing_ico']) && $data['billing_ico'] != "") {

    $name = $data['billing_name'] . ' ' . $data['billing_surname'];

    if ($data['billing_degree'] != "") {

        $name = $data['billing_degree'] . ' ' . $name;

    }

    if ($data['billing_company'] != "") {

        $order_odberatel_name = $data['billing_company'];

    } else {

        $order_odberatel_name = $name;

    }

    $order_address = $data['billing_street'];
    $order_postcode = $data['billing_zipcode'];
    $order_city = $data['billing_city'];

    if ($data['billing_ico'] != "" && $data['billing_dic'] != "") {

        $odberatel_dic_info = '<table style="margin-top: 15px; font-size: 13px; float: left;">
				<tr>
				<td style="width: 60px;">IČ</td>
				<td>' . $data['billing_ico'] . '</td>
				</tr>
				<tr>
				<td>DIČ</td>
				<td>' . $data['billing_dic'] . '</td>
				</tr>
				</table>';

    } elseif ($data['billing_ico'] != "") {

        $odberatel_dic_info = '<table style="margin-top: 15px; font-size: 13px; float: left;">
				<tr>
				<td style="width: 60px;">IČ</td>
				<td>' . $data['billing_ico'] . '</td>
				</tr>
				</table>';

    }

} else {

    $name = $data['billing_name'] . ' ' . $data['billing_surname'];

    if ($data['billing_degree'] != "") {

        $name = $data['billing_degree'] . ' ' . $name;

    }

    if ($data['billing_company'] != "") {

        $order_odberatel_name = $data['billing_company'];

    } else {

        $order_odberatel_name = $name;

    }

    $order_address = $data['billing_street'];
    $order_postcode = $data['billing_zipcode'];
    $order_city = $data['billing_city'];

    if ($data['billing_ico'] != "" && $data['billing_dic'] != "") {

        $odberatel_dic_info = '<table style="margin-top: 15px; font-size: 13px; float: left;">
				<tr>
				<td style="width: 60px;">IČ</td>
				<td>' . $data['billing_ico'] . '</td>
				</tr>
				<tr>
				<td>DIČ</td>
				<td>' . $data['billing_dic'] . '</td>
				</tr>
				</table>';

    } elseif ($data['billing_ico'] != "") {

        $odberatel_dic_info = '<table style="margin-top: 15px; font-size: 13px; float: left;">
				<tr>
				<td style="width: 60px;">IČ</td>
				<td>' . $data['billing_ico'] . '</td>
				</tr>
				</table>';

    }

}

$td_padding = 'padding: 12px 15px;';
$h1_top = '';
$dodavatel_margin = '';
$odberatel_margin = '';

$site_logo = '<img src="../../assets/images/wellnesstrade-shop.png" style="margin: 60px 0 20px; float: left;">';

$products_price = 0;

if (isset($getclient['customer']) && $getclient['customer'] == 1 || $getclient['customer'] == 3) {

    $paramsquery = $mysqli->query('SELECT value FROM demands_specs_bridge WHERE specs_id = "5" AND client_id = "' . $getclient['id'] . '"') or die($mysqli->error);
    $params = mysqli_fetch_array($paramsquery);

    $provedeni = $params['value'];

    $data_hottub_query = $mysqli->query("SELECT * FROM demands_generate_hottub WHERE id = '$id'");
    $data_hottub = mysqli_fetch_array($data_hottub_query);

    if (isset($getclient['customer']) && $getclient['customer'] == 3) {

        $half_price_sauna = $advance_invoice['price_without_vat'] / 2;

    } else {

        $half_price_sauna = $advance_invoice['price_without_vat'];

    }

    $i = 2;

    $specifikace = "";

    if ($data_hottub['price_microsilk'] != "") {if ($specifikace != "") {$specifikace = $specifikace . ', MicroSilk';} else { $specifikace = 'MicroSilk';}}

    if ($data_hottub['price_wipod'] != "") {if ($specifikace != "") {$specifikace = $specifikace . ', WiPod';} else { $specifikace = 'WiPod';}}

    if ($data_hottub['price_inclear'] != "") {if ($specifikace != "") {$specifikace = $specifikace . ', InClear';} else { $specifikace = 'InClear';}}

    if ($data_hottub['price_covermate'] != "") {if ($specifikace != "") {$specifikace = $specifikace . ', CoverMate I';} else { $specifikace = 'CoverMate I';}}

    if ($data_hottub['price_covermate_3'] != "") {if ($specifikace != "") {$specifikace = $specifikace . ', CoverMate III';} else { $specifikace = 'CoverMate III';}}

    if ($data_hottub['price_wifi'] != "") {if ($specifikace != "") {$specifikace = $specifikace . ', WiFi';} else { $specifikace = 'WiFi';}}

    $delivery_montage = "";

    if ($data_sauna['price_delivery'] > 0) {

        $delivery_montage = '<br>doprava';

    }

    if ($data_sauna['price_montage'] > 0) {

        if ($delivery_montage != "") {

            $delivery_montage = $delivery_montage . ', montáž';

        } else {

            $delivery_montage = '<br>montáž';

        }

    }

    $hottub = '<tr>
		<td class="first" width="60px" style="text-align: right; padding: 0 10px 0 0; color: #85becd;">#1</td>
		<td class="first">Uhrazená záloha - ' . returnpn($getclient['customer'], $getclient['product']) . ' ' . $provedeni . '<br><small>' . $specifikace . $delivery_montage . '</small></td>
		<td>' . number_format($half_price_sauna, 2, ',', ' ') . ' Kč</td>
		<td style="padding-right: 28px;">' . $vat_real . '%</td>
		<td style="text-align: right; padding-right: 40px;">' . number_format($half_price_sauna / 100 * $totalvat, 2, ',', ' ') . ' Kč</td>
	</tr>';

}

if (isset($getclient['customer']) && $getclient['customer'] == 0 || $getclient['customer'] == 3) {

    $data_sauna_query = $mysqli->query("SELECT * FROM demands_generate_sauna WHERE id = '$id'");
    $data_sauna = mysqli_fetch_array($data_sauna_query);

    if (isset($getclient['customer']) && $getclient['customer'] == 3) {

        $half_price_sauna = $advance_invoice['price_without_vat'] / 2;

    } else {

        $half_price_sauna = $advance_invoice['price_without_vat'];

    }

    if (isset($getclient['customer']) && $getclient['customer'] == 3) {$number = 2;
        $product = $getclient['secondproduct'];} else { $number = 1;
        $product = $getclient['product'];}

    $delivery_montage = "";

    if ($data_sauna['price_delivery'] > 0) {

        $delivery_montage = '<br>doprava';

    }

    if ($data_sauna['price_montage'] > 0) {

        if ($delivery_montage != "") {

            $delivery_montage = $delivery_montage . ', montáž';

        } else {

            $delivery_montage = '<br>montáž';

        }

    }

    if ($data_sauna['type'] != "") {

        $insert_type = ' - typ ' . $data_sauna['type'];

    }

    $sauna = '<tr>
		<td class="first" width="60px" style="text-align: right; padding: 0 10px 0 0; color: #85becd;">#' . $number . '</td>
		<td class="first">Uhrazená záloha - ' . returnpn($getclient['customer'], $product) . $insert_type . '</td>
		<td>' . number_format($half_price_sauna, 2, ',', ' ') . ' Kč</td>
		<td style="padding-right: 28px;">' . $vat_real . '%</td>
		<td style="text-align: right; padding-right: 40px;">' . number_format($half_price_sauna / 100 * $totalvat, 2, ',', ' ') . ' Kč</td>
	</tr>';

}

/*

echo 'CELKEM ZAOKROUHLENO NAHORU - '.number_format($products_price/100*$totalvat, 0, '', '');

echo '<br><br><br><br>PRVNÍ PLATBA - '.number_format($first_products/100*$totalvat, 0, '', '');

echo '<br><br>DRUHÁ PLATBA - '.number_format(ceil($second_products/100*$totalvat), 0, '', '');
 */

if (($getclient['payment_method'] == 'cash' || $getclient['payment_method'] == 'agmobindercardall') && $_REQUEST['eet'] == 'yes') {
/*

$send_id = $invoice_id;

$send_total = $getclient['total'];

$send_without_vat = $total_without_vat;

$send_vat = $rounded_vat;

include(LIBRARIES."/eet/eet.php");

$eet_print = '<div style="margin-left: 52px;">
<ul style="font-size: 11px;">
<li>FIK: '.$fik.'</li>
<li>BKP: '.$bkp.'</li>
<li>Vystaveno v čase: '.$invoice_datetime.'</li>
<li>Označení provozovny: 11</li>
<li>Označení pokladního zařízení: AWT</li>
<li>Režim tržby: běžný</li>
</ul>
</div>';

//$update_invoice_fik = $mysqli->query("UPDATE orders_invoices SET fik = '$fik', bkp = '$bkp', pkp = '$pkp' WHERE id = '$invoice_id'")or die($mysqli->error);
 */

}

$mpdf = new Mpdf\Mpdf();

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
	font-size: 13px;
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

table.products td { text-align: center;border-bottom: 1px dotted #bedfe7; ' . $td_padding . '}


table.products td.first { text-align: left;}

table.products tr.head td { font-size: 11px; color: #2b2e39;}



table.total {  color: #000; font-size: 13px;}

table.total td { text-align: center;border-bottom: 1px dotted #dcdcdc; padding: 13px 15px 12px;}


table.total td.first { padding-left: 25px; text-align: left;}




</style>
<body>

<div style="width: 100%; padding-bottom: 10px">

<div style="width: 100%; padding-left: 73px;">
' . $site_logo . '
<h1 style="float: right; padding-top: 56px; text-align: right; margin-right: 99px; font-size: 24px;"><span style="color: #666;">Daňový doklad</span> ' . $invoice_id . '</h1>
</div>


<div style="' . $dodavatel_margin . ' width: 281px; margin-left: 75px; float: left;">

<div style="margin-left: 0px;">
<p style="font-size: 14px; color: #666;">Dodavatel</p>
<p style="font-size: 19px; margin-bottom: 10px; font-family: Roboto-Medium, Helvetica;">Wellness Trade, s. r. o.</p>
<p style="font-size: 11px; line-height: 14px;margin-bottom: 0; padding-bottom: 0;">Vrbova 1277/32</p>
<p style="font-size: 11px;; margin-top: 0; padding-top: 0;">147 00 Praha</p>

<table style="margin-top: 15px; font-size: 13px; float: left;">
<tr>
<td style="width: 60px;">IČ</td>
<td>29154871</td>
</tr>
<tr>
<td>DIČ</td>
<td>CZ29154871</td>
</tr>
</table>
</div>

</div>


<div style="width: 60px; margin-right: 6px; padding-top: 50px;text-align: center;float: left;">

<img src="../../assets/images/arrow-right.jpg" style="max-width: 100%;">

</div>



<div style="' . $odberatel_margin . ' width: 33%; padding-right: 20px; float: right;">

<div>
<p style="font-size: 14px; color: #666;">Odběratel</p>
<p style="font-size: 19px; margin-bottom: 10px; font-family: Roboto-Medium, Helvetica;">' . $order_odberatel_name . '</p>
<p style="font-size: 15px; line-height: 14px;margin-bottom: 0; padding-bottom: 0;">' . $order_address . '</p>
<p style="font-size: 15px;; margin-top: 0; padding-top: 0;">' . $order_postcode . ' ' . $order_city . '</p>

' . $odberatel_dic_info . '

</div>

</div>

<div style="clear: both;"></div>

</div>

<hr style="color: #e7e7ef; margin-left: 75px; margin-right: 75px; width: 644px;">


<div style="padding: 5px 0 5px 75px;float: left; width: 44%;">
<table class="bank-table" style="float: left; color: #000;">

<tr>
<td class="first"><strong>Bankovní účet</strong></td>
<td style="padding-left: 0;"><strong>2000364217/2010</strong></td>
</tr>
<tr>
<td class="first"><strong>Variabilní symbol</strong></td>
<td style="padding-left: 0;"><strong>' . $invoice_id . '</strong></td>
</tr>
<tr>
<td class="first">Způsob platby</td>
<td style="padding-left: 0;">' . $pay_method . '</td>
</tr>
</table>
</div>


<div style="padding: 5px 0 0 20px;float: left; width: 44%;">
<table class="bank-table" style="float: left; color: #000;">

<tr>
<td class="first">Datum vystavení</td>
<td style="padding-left: 0;">' . $invoice_date . '</td>
</tr>
<tr>
<td class="first"><strong>Datum přijetí úplaty</strong></td>
<td style="padding-left: 0;"><strong>' . $date_payment . '</strong></td>
</tr>

</table>
</div>


<hr style="color: #e7e7ef; margin-left: 75px; margin-right: 75px; width: 644px;">

<h1 style="' . $h1_top . ' margin-left: 74px; font-family: Roboto-Light, Helvetica; margin-bottom: 14px; font-size: 15px;">Daňový doklad k přijaté záloze na základě úhrady zálohové faktury č. <strong style="font-family: Roboto-Medium, Helvetica; font-size: 16px;">' . $advance_invoice['id'] . '</strong> ze dne ' . $advance_invoice['dateformated'] . '</h1>
<div style="width: 100%;  background-color: #e2f3f7; color: #232c31;">
<div style=" width: 40px; margin-left: 100px; float: left; margin-bottom: -300px;">
<img src="../../assets/images/zobracek.png">
</div>

<div style="clear: both;"></div>

<div style="width: 100%; margin: 3px 0 20px; ">
<table class="products" style="width: 100%;">
<tr class="head">
<td class="first" width="60px" style="border: 0;text-align: right; padding: 0 10px 0 0; color: #85becd;"></td>
<td class="first" style="border: 0; background-color: transparent;" width="310px"></td>
<td>Cena za kus bez dph</td>
<td style="padding-right: 20px;">DPH</td>
<td style="padding-right: 40px;">Cena vč. DPH</td>
</tr>

' . $hottub . $sauna . '

</table>
</div>
</div>

' . $break . '

<div style="width: 400px; float: left; padding-top: 26px;">
' . $eet_print . '
<div style="width: 240px;margin-left: 140px;">
<img src="../../assets/images/wtrazitko.jpg">

</div>
</div>

<div style="width: 360px; float: right; background-color: #e7e7ef; height: auto;">
<div style="clear: both;"></div>

<table class="total" style="width: 100%;">
<tr>
<td class="first">Celkem bez DPH </td>
<td style="text-align: right; padding-right: 40px;">' . number_format($advance_invoice['price_without_vat'], 2, ',', ' ') . ' Kč</td>
</tr>
<tr>
<td class="first">DPH ' . $vat_real . '%</td>
<td style="text-align: right; padding-right: 40px;">' . number_format($advance_invoice['total_vat'], 2, ',', ' ') . ' Kč</td>
</tr>
<tr>
<td class="first">Zaokrouhleno</td>
<td style="text-align: right; padding-right: 40px;">' . number_format($advance_invoice['rounded'], 2, ',', ' ') . ' Kč</td>
</tr>
<tr>
<td class="first" style="font-size: 18px; padding-bottom: 18px; border-bottom: 1px solid #ececec; font-family: Roboto-Medium, Helvetica;">Cena celkem</td>
<td style="text-align: right; padding-right: 40px; border-bottom: 1px solid #ececec; font-size: 18px; font-family: Roboto-Medium, Helvetica;">' . number_format($advance_invoice['total_price'], 0, '', ' ') . ',- Kč</td>
</tr>

</table>

</div>



 <div style="position: absolute; left: 80px; right: 10px; bottom: 70px; width: 640px;">
   	<div style="width: 100%;padding: 10px 0px; font-size: 12px;">

   		<h2>' . $dont_pay . '</h2>
   		<p style="font-size: 12px; margin-top: 24px;">Městský soud v Praze, oddíl C, vložka 203387</p>

   	</div>
    </div>

    <div style="position: absolute; left: 80px; right: 10px; bottom: 20px; width: 640px;">
   	<table style="width: 100%;padding: 10px 0px; border-top: 2px solid #3a3f4d; font-size: 12px;">
   		<tr>
   		<td width="180px" style="padding: 10px 0;">Vystavil/a: ' . $client['user_name'] . '</td>
   		<td width="180px" style="padding: 10px 0;">ucetni@wellnesstrade.cz</td>
   		<td width="180px" style="padding: 10px 0;">+420 774 141 596</td>

   		<td style="padding: 10px 0; text-align: right;"><a href="https://www.wellnesstrade.cz/" style="color: #FFF; float: left;text-decoration: none;"><img src="../../assets/images/wlogofooter.png"></a></td>
   		</tr>

   	</table>
    </div>
';

//$update_invoice_price_deposit = $mysqli->query("UPDATE demands_advance_invoices SET amount = '$total_final_price', deposit = '$depos' WHERE id = '$invoice_id'")or die($mysqli->error);

//==============================================================
if (isset($_REQUEST['html']) && $_REQUEST['html']) {echo $html;exit;}
//==============================================================

$mpdf->WriteHTML($html);

$mpdf->Output($_SERVER['DOCUMENT_ROOT'] . '/admin/data/invoices/demands_tax/Danovy_doklad_' . $invoice_id . '.pdf', 'F');

Header("Location:https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=" . $id . "&success=generate_invoice");
exit;
