<?php

if (!isset($_REQUEST['html'])) { $_REQUEST['html'] = ''; }

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";

use Rikudou\CzQrPayment\QrPayment;
use Rikudou\CzQrPayment\Options\QrPaymentOptions;
use Rikudou\Iban\Iban\CzechIbanAdapter;

include $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/functions.php";

$id = $_REQUEST['id'];

$sauna = '';
$hottub = '';
$pergola = '';

// NEW YEARS CHECK
$last_date_query = $mysqli->query("SELECT DATE_FORMAT(date, '%y') as year FROM demands_advance_invoices ORDER BY id DESC LIMIT 1");
$last_date = mysqli_fetch_array($last_date_query);

$this_year = date('y');

if ($last_date['year'] != $this_year) {

    $new_line = $this_year . '0800001';
    $mysqli->query("ALTER TABLE demands_advance_invoices AUTO_INCREMENT = $new_line") or die($mysqli->error);

}

/*

$find_query = $mysqli->query("SELECT id, DATE_FORMAT(date, '%d. %m. %Y') as dateformated, date FROM orders_invoices WHERE order_id = '$id' AND status = 'active' order by id desc");

if(mysqli_num_rows($find_query) > 0){

while($find = mysqli_fetch_array($find_query)){

$find_odd_query = $mysqli->query("SELECT id FROM orders_invoices WHERE order_id = '$id' AND invoice_id = '".$find['id']."' AND status = 'odd' order by id desc");

if(mysqli_num_rows($find_odd_query) == 0){

echo "K objednavce existuje vystavena faktura, ke ktere neni vystaveni opravny danovy doklad. Pro vytvoreni nove nejdrive vystavte opravny danovy doklad ke stavajici fakture.";
exit;

}

}

}
 */

$getclientquery = $mysqli->query('SELECT *, DATE_FORMAT(date, "%d. %m. %Y") as dateformated, DATE_FORMAT(realization, "%d. %m. %Y") as realizationformated, DATE_FORMAT(realtodate, "%d. %m. %Y") as realtodateformat FROM demands WHERE id="' . $id . '"') or die($mysqli->error);
$getclient = mysqli_fetch_assoc($getclientquery);

$advance_invoice_query = $mysqli->query("SELECT * FROM demands_advance_invoices WHERE demand_id = '$id' AND status = '" . $_REQUEST['invoice'] . "'");

if (mysqli_num_rows($advance_invoice_query) == 1) {

    $advance_invoice = mysqli_fetch_array($advance_invoice_query);

    $invoice_id = $advance_invoice['id'];

    $date = new DateTime($advance_invoice['date']);

    $insert_date = date_format($date, "Y-m-d H:i:s");
    $invoice_datetime = date_format($date, "d. m. Y H:i:s");
    $invoice_date = date_format($date, "d. m. Y");

} else {

    $date = new DateTime();

    $insert_date = date_format($date, "Y-m-d H:i:s");
    $invoice_datetime = date_format($date, "d. m. Y H:i:s");
    $invoice_date = date_format($date, "d. m. Y");

    $mysqli->query("INSERT INTO demands_advance_invoices (demand_id, status, date) VALUES ('$id', '" . $_REQUEST['invoice'] . "', '".$insert_date."' 
)")or die($mysqli->error);

    $invoice_id = $mysqli->insert_id;

}

$data_query = $mysqli->query("SELECT * FROM demands_generate WHERE id = '$id'");
$data = mysqli_fetch_array($data_query);

$billing_query = $mysqli->query('SELECT * FROM addresses_billing WHERE id = "' . $getclient['billing_id'] . '"') or die($mysqli->error);
$billing = mysqli_fetch_assoc($billing_query);

$found_address = check_address_invoice($billing);

if ($found_address != '') {

    $address_id = $found_address;

} else {

    $insert_address = $mysqli->query("INSERT INTO addresses_invoices (billing_company, billing_ico, billing_dic, billing_degree, billing_name, billing_surname, billing_street, billing_city, billing_zipcode, billing_country, billing_phone, billing_email) VALUES ('" . $billing['billing_company'] . "', '" . $billing['billing_ico'] . "', '" . $billing['billing_dic'] . "', '" . $billing['billing_degree'] . "', '" . $billing['billing_name'] . "', '" . $billing['billing_surname'] . "', '" . $billing['billing_street'] . "', '" . $billing['billing_city'] . "', '" . $billing['billing_zipcode'] . "', '" . $billing['billing_country'] . "', '" . $billing['billing_phone'] . "', '" . $billing['billing_email'] . "')") or die($mysqli->error);
    $address_id = $mysqli->insert_id;

}

$dont_pay = '';
if (isset($_REQUEST['payment_method']) && $_REQUEST['payment_method'] == 'cash') {

//    $date_due = $invoice_date;
    $date_due = date('d. m. Y', strtotime($_POST['date_due']));
    $dont_pay = 'Fakturu již neplaťte';

} else {

    $date_due = date('d. m. Y', strtotime($_POST['date_due']));
    $dont_pay = 'Fakturu prosím uhraďte v plné výši včetně haléřové položky.';

}

$first_deposit = $data['deposit'];
$second_deposit = $data['deposit_second'];
$third_deposit = $data['deposit_third'];
$fourth_deposit = $data['deposit_fourth'];

$reverse_charge_text = '';
if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {

    $vat_real = 0;

    $reverse_charge_text = '<p style="font-size: 11px;">Jedná se o přenesení daňové povinnosti podle § 92a zákona o dani z přidané hodnoty, kdy výši daně je povinen doplnit a přiznat plátce, pro kterého je plnění uskutečněno.</p>';
    $with_vat_price = 'Cena bez DPH';

} else {

    if($data['price_vat'] == 15){ $data['price_vat'] = 12; };

    $vat_real = $data['price_vat'];
    $with_vat_price = 'Cena vč. DPH';

}

$totalvat = 100 + $vat_real;

if (isset($_REQUEST['payment_method']) && $_REQUEST['payment_method'] == 'bankwire') {

    $pay_method = 'Převodem';

} elseif (isset($_REQUEST['payment_method']) && $_REQUEST['payment_method'] == 'cash') {

    $pay_method = 'Hotově';

} elseif (isset($_REQUEST['payment_method']) && $_REQUEST['payment_method'] == 'card') {

    $pay_method = 'Platební kartou';

}


$name = $billing['billing_name'] . ' ' . $billing['billing_surname'];

if (!empty($billing['billing_degree'])) { $name = $billing['billing_degree'] . ' ' . $name; }

$order_odberatel_name = $billing['billing_company'] ?: $name;

$order_address = $billing['billing_street'];
$order_postcode = $billing['billing_zipcode'];
$order_city = $billing['billing_city'];

$odberatel_dic_info = '';
if ($billing['billing_ico'] != "" && $billing['billing_dic'] != "") {

    $odberatel_dic_info = '<table style="margin-top: 15px; font-size: 13px; float: left;">
            <tr>
            <td style="width: 60px;">IČ</td>
            <td>' . $billing['billing_ico'] . '</td>
            </tr>
            <tr>
            <td>DIČ</td>
            <td>' . $billing['billing_dic'] . '</td>
            </tr>
            </table>';

} elseif ($billing['billing_ico'] != "") {

    $odberatel_dic_info = '<table style="margin-top: 15px; font-size: 13px; float: left;">
            <tr>
            <td style="width: 60px;">IČ</td>
            <td>' . $billing['billing_ico'] . '</td>
            </tr>
            </table>';

}


$td_padding = 'padding: 12px 15px;';
$h1_top = '';
$dodavatel_margin = '';
$odberatel_margin = '';

$site_logo = '<img src="../../assets/images/wellnesstrade-shop.png" style="margin: 60px 0 20px; float: left;">';

$currency = currency($data['currency']);

if (isset($_REQUEST['invoice']) && $_REQUEST['invoice'] == '1') {

    $depos = $first_deposit; 

} elseif (isset($_REQUEST['invoice']) && $_REQUEST['invoice'] == '2') {

    $depos = $second_deposit;

} elseif (isset($_REQUEST['invoice']) && $_REQUEST['invoice'] == '3') {
    
    $depos = $third_deposit;

} elseif (isset($_REQUEST['invoice']) && $_REQUEST['invoice'] == '4') {
    
    $depos = $fourth_deposit;

}




// defaults for both products


// hottub price calculation
$data_hottub = array();
$hasHottub = false;
if (isset($getclient['customer']) && $getclient['customer'] == 1 || $getclient['customer'] == 3) {

    $hasHottub = true;

    $paramsquery = $mysqli->query('SELECT value FROM demands_specs_bridge WHERE specs_id = "5" AND client_id = "' . $getclient['id'] . '"') or die($mysqli->error);
    $params = mysqli_fetch_array($paramsquery);

    $data_hottub['provedeni'] = $params['value'];

    if ($data_hottub['provedeni'] == 'Special version') { $data_hottub['provedeni'] = 'Gold'; }

    $generate_hottub_query = $mysqli->query("SELECT * FROM demands_generate_hottub WHERE id = '$id'");
    $generate_hottub = mysqli_fetch_array($generate_hottub_query);

    $data_hottub['price'] = $generate_hottub['price_hottub'] + $generate_hottub['price_delivery'] + $generate_hottub['price_montage'] + $generate_hottub['price_chemie'] - $generate_hottub['discount'];

    $specs_demand = $mysqli->query("SELECT *, s.id as id FROM specs s, demands_specs_bridge d WHERE d.specs_id = s.id AND d.value != '' AND d.value != 'Ne' AND d.client_id = '" . $getclient['id'] . "' AND s.generate = 1 ORDER BY s.demand_order") or die($mysqli->error);

    $data_hottub['specifikace'] = "";
    while ($spec = mysqli_fetch_assoc($specs_demand)) {

        if(!$spec['is_generated'] ){

            $data_hottub['price'] += $spec['price'];

        }else{

            if ($spec['id'] == 16) {

                if ($spec['value'] == '2 speed 2,25 kW' && $data_hottub['provedeni'] == 'Gold') {

                    $data_hottub['specifikace'] .= ', dvourychlostní pumpa';

                }

            } else {

                if ($spec['value'] == 'Ekozone') { $spec['name'] = $spec['value'];
                } elseif ($spec['name'] == 'Ozonátor') { $spec['name'] = ''; }

                $data_hottub['specifikace'] .= $data_hottub['specifikace'] ? ', ' . $spec['name'] : $spec['name'];

            }

            $data_hottub['price'] += $spec['price'];

        }

    }


    $accessories_query = $mysqli->query("SELECT * FROM demands_accessories_bridge WHERE aggregate_id = '" . $id . "'") or die($mysqli->error);
    while ($accessory = mysqli_fetch_array($accessories_query)) {

        $subtitle = $accessory['variation_values'] ?: '';
        $name = $accessory['product_name'].$subtitle;

        $data_hottub['specifikace'] .= $data_hottub['specifikace'] ? ', ' . $name : $name;

        $data_hottub['price'] += $accessory['price'] * $accessory['quantity'];

    }


    $data_hottub['delivery_montage'] = "";

    if ($generate_hottub['price_delivery'] > 0) {

        $data_hottub['delivery_montage'] = '<br>doprava';

    }

    if ($generate_hottub['price_montage'] > 0) {

        $data_hottub['delivery_montage'] .= $data_hottub['delivery_montage'] ? ', montáž' : '<br>montáž';

    }

    if ($generate_hottub['price_chemie'] > 0) {

        $data_hottub['delivery_montage'] .= $data_hottub['delivery_montage'] ? ', chemie' : '<br>chemie';

    }


    if ($_POST['special_name'] != "") {

        $data_hottub['name'] = $_POST['special_name'];

    } else {

        $data_hottub['name'] = returnpn($getclient['customer'], $getclient['product']) . ' ' . $data_hottub['provedeni'] . '<br><small>' . $data_hottub['specifikace'] . $data_hottub['delivery_montage'] . '</small>';

    }

}



// sauna price calculation
$data_sauna = array();
$hasSauna = false;
if (isset($getclient['customer']) && $getclient['customer'] == 0 || $getclient['customer'] == 3) {

    $hasSauna = true;

    $generate_sauna_query = $mysqli->query("SELECT * FROM demands_generate_sauna WHERE id = '$id'");
    $generate_sauna = mysqli_fetch_array($generate_sauna_query);

    $data_sauna['price'] = $generate_sauna['price_sauna'] + $generate_sauna['price_delivery'] + $generate_sauna['price_montage'] - $generate_sauna['discount'];

    if (isset($getclient['customer']) && $getclient['customer'] == 3) {
        $data_sauna['index'] = 2;
        $data_sauna['product'] = $getclient['secondproduct'];
    } else {
        $data_sauna['index'] = 1;
        $data_sauna['product'] = $getclient['product'];
    }

    $data_sauna['delivery_montage'] = "";

    if ($generate_sauna['price_delivery'] > 0) {

        $data_sauna['delivery_montage'] = '<br>doprava';

    }

    if ($generate_sauna['price_montage'] > 0) {

        $data_sauna['delivery_montage'] .= $data_sauna['delivery_montage'] ? ', montáž' : '<br>montáž';

    }

    $data_sauna['insert_type'] = $generate_sauna['type'] ? ' - typ ' . $generate_sauna['type'] : '';

    if (!empty($_POST['special_name'])) {

        $data_sauna['name'] = $_POST['special_name'];

    } else {

        $data_sauna['name'] = returnpn($getclient['customer'], $data_sauna['product']) . $data_sauna['insert_type'];

    }

}


// pergola calculation
$data_pergola = array();
$hasPergola = false;
if (isset($getclient['customer']) && $getclient['customer'] == 4) {

    $hasPergola = true;

    $paramsquery = $mysqli->query('SELECT value FROM demands_specs_bridge WHERE specs_id = "5" AND client_id = "' . $getclient['id'] . '"') or die($mysqli->error);
    $params = mysqli_fetch_array($paramsquery);

    $data_pergola['provedeni'] = $params['value'];


    $generate_pergola_query = $mysqli->query("SELECT * FROM demands_generate_pergola WHERE id = '$id'");
    $generate_pergola = mysqli_fetch_assoc($generate_pergola_query);

    $data_pergola['price'] = $generate_pergola['price_pergola'] + $generate_pergola['price_delivery'] + $generate_pergola['price_montage'] - $generate_pergola['discount'];

    $specs_demand = $mysqli->query("SELECT *, s.id as id FROM specs s, demands_specs_bridge d WHERE d.specs_id = s.id AND d.value != '' AND d.value != 'Ne' AND d.client_id = '" . $getclient['id'] . "' AND s.generate = 1 ORDER BY s.demand_order") or die($mysqli->error);

    $data_pergola['specifikace'] = "";
    while ($spec = mysqli_fetch_assoc($specs_demand)) {

        if(!$spec['is_generated'] ){

            $data_pergola['price'] += $spec['price'];

        }

    }

    $data_pergola['delivery_montage'] = "";

    if ($generate_pergola['price_delivery'] > 0) {

        $data_pergola['delivery_montage'] = '<br>doprava';

    }

    if ($generate_pergola['price_montage'] > 0) {

        $data_pergola['delivery_montage'] .= $data_pergola['delivery_montage'] ? ', montáž' : '<br>montáž';

    }

    if ($_POST['special_name'] != "") {

        $data_pergola['name'] = $_POST['special_name'];

    } else {

        $data_pergola['name'] = returnpn($getclient['customer'], $getclient['product']) . ' ' . $data_pergola['provedeni'] . '<br><small>' . $data_pergola['specifikace'] . $data_pergola['delivery_montage'] . '</small>';

    }

}




// payments set by percentage
$both_product_price = 0;
if (isset($data['deposit_type']) && $data['deposit_type'] == 'percentage') {

    if ($hasHottub) {

        $both_product_price += $data_hottub['price'];
        $final_price_hottub = $data_hottub['price'] / 100 * $depos;

    }

    if ($hasSauna) {

        $both_product_price += $data_sauna['price'];
        $final_price_sauna = $data_sauna['price'] / 100 * $depos;

    }

    if ($hasPergola) {

        $both_product_price += $data_pergola['price'];
        $final_price_pergola = $data_pergola['price'] / 100 * $depos;

    }

    if ($_REQUEST['invoice'] != $data['invoices_number']) {

        $both_product_price = $both_product_price / 100 * $depos;

    } elseif (isset($_REQUEST['invoice']) && $_REQUEST['invoice'] == $data['invoices_number']) {

        $both_product_price = $both_product_price / 100 * $depos;

    }


//payments set by values
} elseif (isset($data['deposit_type']) && $data['deposit_type'] == 'money') {

    // sauna + vířivka
    if ($getclient['customer'] == 3) {

        $final_price_hottub = $depos / 2;
        $final_price_sauna = $depos / 2;

    // single vířivka nebo sauna
    } else {

        $final_price_hottub = $depos;
        $final_price_sauna = $depos;
        $final_price_pergola = $depos;

    }

    $both_product_price = $depos;

}


if($hasHottub){

    $hottub = '<tr>
            <td class="first" width="60px" style="text-align: right; padding: 0 10px 0 0; color: #85becd;">#1</td>
            <td class="first">' . $data_hottub['name'] . '</td>
            <td>' . number_format($final_price_hottub, 2, ',', ' ') . $currency['sign'] .'</td>
            <td style="padding-right: 28px;">' . $vat_real . '%</td>
            <td style="text-align: right; padding-right: 40px;">' . number_format($final_price_hottub / 100 * $totalvat, 2, ',', ' ') . $currency['sign'] .'</td>
        </tr>';

}

if($hasSauna) {

    $sauna = '<tr>
		<td class="first" width="60px" style="text-align: right; padding: 0 10px 0 0; color: #85becd;">#' . $data_sauna['index'] . '</td>
		<td class="first">' . $data_sauna['name'] . '</td>
		<td>' . number_format($final_price_sauna, 2, ',', ' ') . $currency['sign'] . '</td>
		<td style="padding-right: 28px;">' . $vat_real . '%</td>
		<td style="text-align: right; padding-right: 40px;">' . number_format($final_price_sauna / 100 * $totalvat, 2, ',', ' ') . $currency['sign'] . '</td>
	</tr>';

}


if($hasPergola){

    $pergola = '<tr>
            <td class="first" width="60px" style="text-align: right; padding: 0 10px 0 0; color: #85becd;">#1</td>
            <td class="first">' . $data_pergola['name'] . '</td>
            <td>' . number_format($final_price_pergola, 2, ',', ' ') . $currency['sign'] .'</td>
            <td style="padding-right: 28px;">' . $vat_real . '%</td>
            <td style="text-align: right; padding-right: 40px;">' . number_format($final_price_pergola / 100 * $totalvat, 2, ',', ' ') . $currency['sign'] .'</td>
        </tr>';

}

    $total_with_vat = $both_product_price / 100 * $totalvat;
    $total_vat = $both_product_price / 100 * $vat_real;
    $total_without_vat = $both_product_price;
    $total_database = number_format($both_product_price / 100 * $totalvat, 2, '.', '');


$total = array();

$total['without_vat'] = number_format($total_without_vat, 2, '.', '');;
$total['vat'] = $total_vat;
$total['rounded'] = 0;
$total['total_price'] = $total_with_vat;

$is_rounded = '';
if($_REQUEST['payment_method'] == 'cash'){

    $total['total_price'] = round($total['total_price']);
    $total['rounded'] = number_format($total['total_price'] - $total_with_vat, 2, '.', '');

    $is_rounded = '<tr>
<td class="first">Zaokrouhleno </td>
<td style="text-align: right; padding-right: 40px;">' . thousand_seperator($total['rounded']) . $currency['sign'].'</td>
</tr>';

}



$eet_print = '';
if ((isset($getclient['payment_method']) && ($getclient['payment_method'] == 'cash' || $getclient['payment_method'] == 'agmobindercardall')) && (isset($_REQUEST['eet']) && $_REQUEST['eet'] == 'yes')) {


// EET
//
//$send_id = $invoice_id;
//
//$send_total = $getclient['total'];
//
//$send_without_vat = $total_without_vat;
//
//$send_vat = $rounded_vat;
//
//include(LIBRARIES."/eet/eet.php");
//
//$eet_print = '<div style="margin-left: 52px;">
//<ul style="font-size: 11px;">
//<li>FIK: '.$fik.'</li>
//<li>BKP: '.$bkp.'</li>
//<li>Vystaveno v čase: '.$invoice_datetime.'</li>
//<li>Označení provozovny: 11</li>
//<li>Označení pokladního zařízení: AWT</li>
//<li>Režim tržby: běžný</li>
//</ul>
//</div>';
//
////$update_invoice_fik = $mysqli->query("UPDATE orders_invoices SET fik = '$fik', bkp = '$bkp', pkp = '$pkp' WHERE id = '$invoice_id'")or die($mysqli->error);
//

}
// nebo přímo IBAN
$qrWrapper = new QrPayment(
    new CzechIbanAdapter('2000364217', '2010'),  // účet / kód banky
    // případně: new \Rikudou\Iban\Iban\IBAN('CZ8320100000002000364217')
    [
        QrPaymentOptions::AMOUNT         => $total['total_price'],
        QrPaymentOptions::CURRENCY       => $currency['code'],
        QrPaymentOptions::VARIABLE_SYMBOL => $invoice_id,
        QrPaymentOptions::DUE_DATE       => new \DateTimeImmutable(date('Y/m/d', strtotime($_POST['date_due']))),
        //QrPaymentOptions::MESSAGE        => 'Zalohova faktura '.$invoice_id,   // volitelné
        QrPaymentOptions::INSTANT_PAYMENT => true,   // volitelné – okamžitá platba
    ]
);


$qrSvg = $qrWrapper->getQrCode();

$dataUri = $qrSvg->getDataUri();


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
<h1 style="float: right; padding-top: 56px; text-align: right; margin-right: 99px; font-size: 24px;"><span style="color: #666;">Zálohová faktura</span> ' . $invoice_id . '</h1>
</div>


<div style="' . $dodavatel_margin . ' width: 281px; margin-left: 75px; float: left;">

<div style="margin-left: 0px;">
<p style="font-size: 14px; color: #666;">Dodavatel</p>
<p style="font-size: 19px; margin-bottom: 10px; font-family: Roboto-Medium, Helvetica;">Wellness Trade, s. r. o.</p>
<p style="font-size: 15px; line-height: 14px;margin-bottom: 0; padding-bottom: 0;">Vrbova 1277/32</p>
<p style="font-size: 15px;; margin-top: 0; padding-top: 0;">147 00 Praha</p>

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
<td style="padding-left: 0;"><strong>'.$currency['bank_account'].'</strong></td>
</tr>
<tr>
<td class="first font-size: 11px;">IBAN</td>
<td style="padding-left: 0; font-size: 11px;">'.$currency['iban'].'</td>
</tr>
<tr>
<td class="first" font-size: 11px;>BIC</td>
<td style="padding-left: 0; font-size: 11px;">CEKOCZPP</td>
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
<td class="first"><strong>Datum splatnosti</strong></td>
<td style="padding-left: 0;"><strong>' . $date_due . '</strong></td>
</tr><tr>
<td class="first">Datum UDP </td>
<td style="padding-left: 0;">' . $invoice_date . '</td>
</tr>

</table>
</div>


<hr style="color: #e7e7ef; margin-left: 75px; margin-right: 75px; width: 644px;">

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
<td style="padding-right: 40px;">' . $with_vat_price . '</td>
</tr>

' . $hottub . $sauna . $pergola . '

</table>
</div>
</div>

<div style="width: 360px; float: left; padding-top: 26px;">
' . $eet_print . '
<div style="width: 240px;margin-left: 140px;">
<img src="../../assets/images/wtrazitko.jpg">

</div>
</div>

<div style="width: 400px; float: right; background-color: #e7e7ef; height: auto;">
<div style="clear: both;"></div>

<table class="total" style="width: 100%;">
<tr>
<td class="first">Celkem bez DPH </td>
<td style="text-align: right; padding-right: 40px;">' . thousand_seperator($total['without_vat']) . $currency['sign'].'</td>
</tr>
<tr>
<td class="first">DPH ' . $vat_real . '%</td>
<td style="text-align: right; padding-right: 40px;">' . thousand_seperator($total['vat']) . $currency['sign'].'</td>
</tr>
'.$is_rounded.'
<tr>
<td class="first" style="font-size: 12px; padding-bottom: 12px; border-bottom: 0px solid #ececec; font-family: Roboto-Medium, Helvetica;">Cena celkem</td>
<td style="text-align: right; padding-right: 40px; border-bottom: 0px solid #ececec;  padding-bottom: 10px; font-size: 15px; font-family: Roboto-Medium, Helvetica;">' . thousand_seperator($total['total_price']) . $currency['sign'].'</td>
</tr>

</table>
</div>
<div style="width: 400px; float: right;">
<h2 style="font-size: 14px;">' . $dont_pay . '</h2>
<div style="float: right; width: 180px; text-align: center; margin: 50px; 0 20px 20px; padding: 10px; border: 1px solid #ddd; background: #f9f9f9;">
    <strong>Zaplaťte QR kódem</strong><br>
    <img src="' . htmlspecialchars($dataUri) . '" width="100" height="100" alt="QR platba" />
    <br>
    <small style="color: #555; font-size: 10px;">
        Naskenujte v mobilní aplikaci banky<br>
        Částka: ' . number_format($total['total_price'], 2, ',', ' ') . ' Kč | VS: ' . $invoice_id . '
    </small>
</div>
</div>




 <div style="position: absolute; left: 80px; right: 10px; bottom: 70px; width: 640px;">
   	<div style="width: 100%;padding: 10px 0px; font-size: 12px;">
   		' . $reverse_charge_text . '
   		<p style="font-size: 12px; margin-top: 24px;">Městský soud v Praze, oddíl C, vložka 203387</p>

   	</div>
    </div>

    <div style="position: absolute; left: 80px; right: 10px; bottom: 20px; width: 640px;">
   	<table style="width: 100%;padding: 10px 0px; border-top: 2px solid #3a3f4d; font-size: 12px;">
   		<tr>
   		<td width="180px" style="padding: 10px 0;">Vystavil/a: ' . $client['user_name'] . '</td>
   		<td width="180px" style="padding: 10px 0;">ucetni@wellnesstrade.cz</td>
   		<td width="180px" style="padding: 10px 0;">+420 774 141 596</td>

   		<td style="padding: 10px 0; text-align: right;"><img src="../../assets/images/wlogofooter.png"></td>
   		</tr>

   	</table>
    </div>
';



$mysqli->query("UPDATE demands_advance_invoices 
    SET address_id = '" . $address_id . "',
        special_name = '" . $_POST['special_name'] . "', 
        due_date = '" . $_POST['date_due'] . "', 
        price_without_vat = '".$total['without_vat']."', 
        total_vat = '".$total['vat']."', 
        total_price = '".$total['total_price']."', 
        deposit = '$depos', 
        deposit_type = '" . $data['deposit_type'] . "', 
        payment_method = '" . $_REQUEST['payment_method'] . "',
        currency = '" . $data['currency'] . "',
        rounded = '".$total['rounded']."',
        exchange_rate = '" . $data['exchange_rate'] . "'
    WHERE id = '$invoice_id'") or die($mysqli->error);


// paid...
if($advance_invoice['total_price'] != $total['total_price'] && $advance_invoice['paid'] != 0){

    $mysqli->query("UPDATE demands_advance_invoices SET paid = '0' WHERE id = '$invoice_id'")or die($mysqli->error);

}

//==============================================================
if (isset($_REQUEST['html']) && $_REQUEST['html']) {echo $html; exit;}
//==============================================================

$mpdf->WriteHTML($html);

$mpdf->Output($_SERVER['DOCUMENT_ROOT'] . '/admin/data/invoices/demands/Zalohova_faktura_' . $invoice_id . '.pdf', 'F');

Header("Location:https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=" . $id . "&success=generate_invoice");
exit;
