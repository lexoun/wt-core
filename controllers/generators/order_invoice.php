<?php

if (!isset($_REQUEST['html'])) {$_REQUEST['html'] = '';}

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";

if(isset($_REQUEST['id'])){ $id = $_REQUEST['id']; }
if(isset($_REQUEST['type'])){ $type = $_REQUEST['type']; }
if(isset($_REQUEST['odd'])){ $odd = $_REQUEST['odd']; }else{ $odd = false; }

// NEW YEARS CHECK

$last_date_query = $mysqli->query("SELECT DATE_FORMAT(date, '%Y') as year FROM orders_invoices ORDER BY id DESC LIMIT 1");
$last_date = mysqli_fetch_array($last_date_query);

$this_year = date('Y');

$new_year = false;
if ($last_date['year'] != $this_year) {

    $new_year = true;

    $new_line = $this_year . '00001';

    $mysqli->query("ALTER TABLE orders_invoices AUTO_INCREMENT = $new_line") or die($mysqli->error);

}

$find_query = $mysqli->query("SELECT id, DATE_FORMAT(date, '%d. %m. %Y') as dateformated, date FROM orders_invoices WHERE order_id = '$id' AND type = '$type' AND status = 'active' order by id desc");

$prev_id = 0;
if (mysqli_num_rows($find_query) > 0) {

    while ($find = mysqli_fetch_array($find_query)) {

        $find_odd_query = $mysqli->query("SELECT id FROM orders_invoices WHERE order_id = '$id' AND type = '$type' AND invoice_id = '" . $find['id'] . "' AND status = 'odd' order by id desc");

        if (isset($_REQUEST['odd']) && mysqli_num_rows($find_odd_query) == 0 && $odd) {

            $odd_is_missing = 'yes';
            $prev_id = $find['id'];

        } elseif (!$odd) {

            echo 'faktura je jiz vystavena';
            exit;

        }

    }

}

if (!$odd) {

    $status = 'active';
    $odd_mark = '';
    $title = 'Faktura';
    $odd_text = '';

} else {

    $status = 'odd';
    $odd_mark = '-';
    $title = 'Opravný daňový doklad';
    $odd_text = '<p style="font-size: 12px; margin-top: 24px;">Důvod opravy: zdanitelné plnění se neuskutečnilo. Evidenční číslo původního dokladu: ' . $prev_id . '</p>';

}

if ($type == 'order') {

    if (!$odd){

        echo 'fakturace za objednavky je uz mozna pouze ze simplie!';
        exit;

    }

    $order_query = $mysqli->query("SELECT *, DATE_FORMAT(order_date, '%d. %m. %Y') as dateformated FROM orders WHERE id = '$id'");
    $order = mysqli_fetch_array($order_query);

    $products_query = $mysqli->query("SELECT *, p.id as ajdee, o.price as price 
        FROM products p, orders_products_bridge o 
        WHERE p.id = o.product_id AND o.aggregate_id = '$id' AND o.aggregate_type = 'order'");

    $total_price = $order['total'];

    $vat = $order['vat'];
    $delivery = $order['delivery_price'];

    $variable_symbol = $order['id'];

    $payment_method = $order['payment_method'];

    $currency_code = $order['order_currency'];

    $redirect = "https://www.wellnesstrade.cz/admin/pages/orders/zobrazit-objednavku?id=" . $id . "&success=generate_invoice";

} elseif ($type == 'service') {

    $order_query = $mysqli->query("SELECT *, DATE_FORMAT(date, '%d. %m. %Y') as dateformated FROM services WHERE id = '$id'");
    $order = mysqli_fetch_array($order_query);

    $products_query = $mysqli->query("SELECT *, p.id as ajdee, b.price as price, b.discount_net FROM products p, services_products_bridge b WHERE p.id = b.product_id AND b.aggregate_id = '$id'");

    $total_price = $order['price'];
    $vat = $order['vat'];
    $delivery = $order['delivery_price'];

    $variable_symbol = $order['id'] . '000';

    $payment_method = $order['payment_method'];

    $currency_code = $order['currency'];

    $order['order_site'] = 'wellnesstrade';

    $redirect = "https://www.wellnesstrade.cz/admin/pages/services/zobrazit-servis?id=" . $id . "&success=generate_invoice";

}

$address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $order['shipping_id'] . '" WHERE b.id = "' . $order['billing_id'] . '"') or die($mysqli->error);
$address = mysqli_fetch_assoc($address_query);


$currency = currency_eshop($currency_code);

$coeficient = vat_coeficient($vat);

$date = new DateTime();

$insert_date = date_format($date, "Y-m-d H:i:s");
$invoice_datetime = date_format($date, "d. m. Y H:i:s");
$invoice_date = date_format($date, "d. m. Y");


$lastID_query = $mysqli->query("SELECT id FROM orders_invoices ORDER BY id DESC LIMIT 1")or die($mysqli->error);
$lastID = mysqli_fetch_assoc($lastID_query);

if(!empty($new_line)){

    $invoice_id = $new_line;

}else{

    $invoice_id = $lastID['id'] + 1;

}


$odberatel_dic_info = "";

if ($address['billing_company'] != "") {

    $order_odberatel_name = $address['billing_company'];

} else {

    $order_odberatel_name = $address['billing_name'] . ' ' . $address['billing_surname'];

}

$order_address = $address['billing_street'];
$order_postcode = $address['billing_zipcode'];
$order_city = $address['billing_city'];

if (isset($address['billing_country']) && $address['billing_country'] == 'CZ') {
    $order_country = 'Česká republika';
} elseif (isset($address['billing_country']) && $address['billing_country'] == 'SK') {
    $order_country = 'Slovensko';
} elseif (isset($address['billing_country']) && $address['billing_country'] == 'PL') {
    $order_country = 'Polsko';
} elseif (isset($address['billing_country']) && $address['billing_country'] == 'AT') {
    $order_country = 'Rakousko';
} else {
    $order_country = $address['billing_country'];
}

if ($address['billing_ico'] != "" && $address['billing_dic'] != "") {

    $odberatel_dic_info = '<table style="margin-top: 4px; font-size: 11px; float: left;">
				<tr>
				<td style="width: 40px;">IČ</td>
				<td>' . $address['billing_ico'] . '</td>
				</tr>
				<tr>
				<td>DIČ</td>
				<td>' . $address['billing_dic'] . '</td>
				</tr>
				</table>';

} elseif ($address['billing_ico'] != "") {

    $odberatel_dic_info = '<table style="margin-top: 4px; font-size: 11px; float: left;">
				<tr>
				<td style="width: 40px;">IČ</td>
				<td>' . $address['billing_ico'] . '</td>
				</tr>
				</table>';

}

$payment_query = $mysqli->query("SELECT name, eet, link_name FROM shops_payment_methods WHERE link_name = '" . $payment_method . "'") or die($mysqli->error);
$payment = mysqli_fetch_array($payment_query);


$dont_pay = '';
$pay_method = $payment['name'];

if ($payment['link_name'] != 'bacs') {
    $dont_pay = 'Fakturu již neplaťte';
} else {
    $dont_pay = 'Fakturu prosím uhraďte včetně haléřové položky.';
}


$reverse_charge_text = '';

// if DPH = 0;
if ($vat == '0') {

    $reverse_charge_text = '<p style="font-size: 11px;">Jedná se o přenesení daňové povinnosti podle § 92a zákona o dani z přidané hodnoty, kdy výši daně je povinen doplnit a přiznat plátce, pro kterého je plnění uskutečněno.</p>';
    $with_vat_price = 'Cena bez DPH';

} else {

    $with_vat_price = 'Celkem vč. DPH';

}

if (isset($order['payment_method']) && $order['payment_method'] == 'cash' || $order['payment_method'] == 'agmobindercardall') {

    $date_due = $invoice_date;

} else {

    $date_due = date('d. m. Y', strtotime(date('Y-m-d') . ' + 14 days'));

}

$products = "";
$total_discount = 0;

$i = 0;
$has_discount = false;
while ($product = mysqli_fetch_array($products_query)) {
    $i++;

    $oneproduct = "";

    $vari = '';

    if ($product['variation_id'] != 0) {

        $variation_sku_query = $mysqli->query("SELECT id, sku, main_warehouse FROM products_variations WHERE id = '" . $product['variation_id'] . "'");
        $variation_sku = mysqli_fetch_array($variation_sku_query);

        $sku = $variation_sku['sku'];

        $vari_pre = '<span style="font-size: 10px; font-weight: 300;">';

        $vari_vlue = '';

        $variation_query = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $product['variation_id'] . "'");

        while ($variation = mysqli_fetch_array($variation_query)) {

            if ($vari_vlue == "") {$vari_vlue = $variation['name'] . ': ' . $variation['value'];

            } else {

                $vari_vlue = $vari_vlue . ', ' . $variation['name'] . ': ' . $variation['value'];
            }

        }

        $vari_after = '</span>';

        $vari = $vari_pre . $vari_vlue . $vari_after;

    } else {

        $sku = $product['code'];

    }

    // return, $price['single'], $price['without_vat'], $price['vat']

    $price = get_price($product['price'], $coeficient);

    $current_discount = '-';
    if(!empty($product['discount'])){

        $has_discount = true;

        $current_discount = $product['discount'].' %';

        $total_discount += round(($price['single'] / 100 * ($product['discount'])) * $product['quantity'], 2, PHP_ROUND_HALF_DOWN);

//        Todo calculate by discount_net? Why? What for?
//        $total_discount += $price['discount_net'] / 100 * ($product['discount']), 2, '.', '')) * $product['quantity'];

    }

    $products .= '<tr>
						<td class="first" width="60px" style="text-align: right; padding: 0 10px 0 0; color: #85becd;">#' . $i . '</td>
						<td class="first">' . $product['productname'] . ' ' . $vari . '</td>
						<td>' . $odd_mark . thousand_seperator($price['without_vat']) . $currency['sign'].'</td>
						<td>' . $product['quantity'] . '</td>
						<td>' . $current_discount . '</td>
						<td style="padding-right: 28px;">' . $vat . '%</td>
						<td style="text-align: right; padding-right: 40px;">' . $odd_mark . thousand_seperator($price['single'] * $product['quantity']) . $currency['sign'].'</td>
					</tr>';

}


if ($type == 'service') {

    $service_price = 0;

    $items_query = $mysqli->query("SELECT * FROM services_items WHERE service_id = '".$id."'")or die($mysqli->error);
    while($item = mysqli_fetch_assoc($items_query)) {

        $i++;
        $service_price += $item['price'];

        if($item['name'] != ''){ $name = $item['name']; }else{ $name = 'Provedený servis'; }
        $price = get_price($item['price'], $coeficient);

        $products .= '<tr>
                    <td class="first" width="60px" style="text-align: right; padding: 0 10px 0 0; color: #85becd;">#' . $i . '</td>
                    <td class="first">' . $name . '</td>
                    <td>' . $odd_mark . thousand_seperator($price['without_vat']) . $currency['sign'] . '</td>
                    <td>1</td>
                    <td>-</td>
                    <td style="padding-right: 28px;">' . $order['vat'] . '%</td>
                    <td style="text-align: right; padding-right: 40px;">' . $odd_mark . thousand_seperator($price['single']) . $currency['sign'] . '</td>
                </tr>';

    }

}

if ($delivery != 0) {

    $i++;

    $price = get_price($delivery, $coeficient);

    $products .= '<tr>
						<td class="first" width="60px" style="text-align: right; padding: 0 10px 0 0; color: #85becd;">#' . $i . '</td>
						<td class="first">Doprava</td>
						<td>' . $odd_mark . thousand_seperator($price['without_vat']) . $currency['sign'].'</td>
						<td>1</td>
						<td>-</td>
						<td style="padding-right: 28px;">' . $vat . '%</td>
						<td style="text-align: right; padding-right: 40px;">' . $odd_mark . thousand_seperator($price['single']) . $currency['sign'].'</td>
					</tr>';


}


/* ZJIŠTĚNÍ CENY TOTAL SQL */

if($type == 'order'){

    /* ORDER */

    $get_price = $mysqli->query("SELECT SUM(total) as total, SUM(purchase_price) as purchase_price, SUM(discount_net) as discount_net FROM (SELECT sum(b.quantity * b.price) as total, sum(b.purchase_price * b.quantity) as purchase_price, sum(b.discount_net * b.quantity) as discount_net 
        from products p, orders_products_bridge b, orders o 
        WHERE o.id = b.aggregate_id AND p.id = b.product_id AND o.id = '" . $id . "' AND p.type = 'simple' AND b.aggregate_type = 'order'
    UNION ALL 
        SELECT sum(b.quantity * b.price) as total, sum(b.purchase_price * b.quantity) as purchase_price, sum(b.discount_net * b.quantity) as discount_net 
        from products_variations v, orders_products_bridge b, orders o 
        WHERE o.id = b.aggregate_id AND v.id = b.variation_id AND o.id = '" . $id . "' AND b.variation_id != 0 AND b.aggregate_type = 'order') 
    as products") or die($mysqli->error);

    $price_data = mysqli_fetch_array($get_price);

    $sql_price = $price_data['total'] + $delivery - $price_data['discount_net'];

}else{

    /* SERVICE */

    $get_price = $mysqli->query("SELECT SUM(total) as total, SUM(purchase_price) as purchase_price, SUM(discount_net) as discount_net FROM (SELECT sum(b.quantity * b.price) as total, sum(b.purchase_price * b.quantity) as purchase_price, sum(b.discount_net * b.quantity) as discount_net from products p, services_products_bridge b, services o WHERE o.id = b.aggregate_id AND p.id = b.product_id AND o.id = '" . $id . "' AND p.type = 'simple' UNION ALL SELECT sum(b.quantity * b.price) as total, sum(b.purchase_price * b.quantity) as purchase_price, sum(b.discount_net * b.quantity) as discount_net from products_variations v, services_products_bridge b, services o WHERE o.id = b.aggregate_id AND v.id = b.variation_id AND o.id = '" . $id . "' AND b.variation_id != 0) as products") or die($mysqli->error);

    $price_data = mysqli_fetch_array($get_price);

    $sql_price = $price_data['total'] + $delivery + $service_price - $price_data['discount_net'];

}


if($has_discount) {

    $i++;

    $discount = get_price($total_discount, $coeficient);

    if($odd_mark == '-'){ $discount_mark = ''; }else{ $discount_mark = '-'; }

    $products .= '<tr>
                <td class="first" width="60px" style="text-align: right; padding: 0 10px 0 0; color: #85becd;">#' . $i . '</td>
                <td class="first">Sleva</td>
                <td>' .$discount_mark. thousand_seperator($discount['without_vat']) . $currency['sign'] . '</td>
                <td>1</td>
                <td>-</td>
                <td style="padding-right: 28px;">' . $vat . '%</td>
                <td style="text-align: right; padding-right: 40px;">' .$discount_mark. thousand_seperator($discount['single']) . $currency['sign'] . '</td>
            </tr>';

}

if ($i > 5 && $i < 11) {

    $td_padding = 'padding: 8px 13px;';
    $h1_top = 'margin-top: 0;';
    $dodavatel_margin = 'margin-top:-18px;';
    $odberatel_margin = 'margin-top:-18px;';

} elseif ($i > 10) {

    $td_padding = 'padding: 9px 13px;';
    $h1_top = '';
    $dodavatel_margin = '';
    $odberatel_margin = '';

} else {

    $td_padding = 'padding: 12px 13px;';
    $h1_top = '';
    $dodavatel_margin = '';
    $odberatel_margin = '';
}

$break = '';

if ($i > 10 && $i < 20) {

    $break = '<pagebreak />';

}

    $site_logo = '<img src="../../assets/images/' . $order['order_site'] . '-shop.png" style="margin: 30px 0 16px; float: left; max-width: 140px; max-height: 40px;" width="140" height="auto">';

/* Before EET check

    -- without_vat
    -- vat
    -- rounded
    -- total_price

*/

// Check if total_price == SQL total_price

$total = array();

if($order['total_rounded'] != '0.00'){

    $total_price_calculate = $total_price - $order['total_rounded'];
    $total = get_price($total_price_calculate, $coeficient);
    $total['single'] = $total_price;


}else{

    $total = get_price($total_price, $coeficient);

}


$total['rounded'] = $order['total_rounded'];


if($odd){

    $total['without_vat'] = -$total['without_vat'];
    $total['vat'] = -$total['vat'];
    $total['single'] = -$total['single'];
    $total['rounded'] = -$total['rounded'];

}


$is_rounded = '';
if($order['payment_method'] == 'cash' || $order['payment_method'] == 'cod'){

//    $total['single'] = round($total['single']);
//
//    if($odd){
//
//        $total['rounded'] = number_format($total['single'] + $total_price, 2, '.', '');
//        $total['rounded'] = -$total['rounded'];
//
//    }else{
//
//        $total['rounded'] = number_format($total['single'] - $total_price, 2, '.', '');
//
//    }
//
//    if(!empty($total['rounded']) && $total['rounded'] != '0.00'){
//        $is_rounded = '<tr>
//    <td class="first">Zaokrouhleno </td>
//    <td style="text-align: right; padding-right: 40px;">' . thousand_seperator($total['rounded']) . $currency['sign'].'</td>
//    </tr>';
//    }


    if(!empty($total['rounded']) && $total['rounded'] != '0.00'){
        $is_rounded = '<tr>
    <td class="first">Zaokrouhleno </td>
    <td style="text-align: right; padding-right: 40px;">' . thousand_seperator($total['rounded']) . $currency['sign'].'</td>
    </tr>';

    }

}

$eet_print = '';
if ($order['payment_method'] == 'cash' && $_REQUEST['eet'] == 'yes') {

    $send_id = $invoice_id;

    include CONTROLLERS . "/stores/eet.php";


    if(!empty($fik)){

        // FIK AND BKP
        $codes = '<li>FIK: '.$fik.'</li><li>BKP: '.$bkp.'</li>';

    }else{

        // PKP
        $fik = '';
        $codes = '<li>PKP: '.$pkp.'</li>';

    }

    $eet_print = '<div style="margin-left: 52px;">
<ul style="font-size: 11px;">
'.$codes.'
<li>Vystaveno v čase: ' . $invoice_datetime . '</li>
<li>Označení provozovny: 11</li>
<li>Označení pokladního zařízení: AWT</li>
<li>Režim tržby: běžný</li>
</ul>
</div>';

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
	font-size: 11px;
	color: #000000;
}

strong { font-family: "Roboto-Medium", Helvetica; font-weight: 500;}

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
	font-size: 8pt;
}
.shadowtitle {
	height: 8mm;
	background-color: #EEDDFF;
	background-gradient: linear #c7cdde #f0f2ff 0 1 0 0.5;
	padding: 0.8em;
	padding-left: 3em;
	font-family:sans;
	font-size: 22pt;
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
table.bank-table td { padding: 3px 0; width: 130px;}


table.products {  color: #000; font-size: 10px;}

table.products td { text-align: center;border-bottom: 1px dotted #bedfe7; ' . $td_padding . '}
table.products tr:last-child td { border-bottom: 0px !important; }


table.products td.first { text-align: left;}
table.products tr.head td { font-size: 9px; color: #2b2e39;}

table.total {  color: #000; font-size: 11px;}
table.total td { text-align: center;border-bottom: 1px dotted #dcdcdc; padding: 10px 15px 9px;}
table.total td.first { padding-left: 25px; text-align: left;}

</style>
<body>

<div style="width: 100%; padding-bottom: 10px">

<div style="width: 100%; padding-left: 60px;">
' . $site_logo . '
<h1 style="float: right; padding-top: 30px; padding-bottom: 10px; text-align: right; margin-right: 60px; font-size: 18px;"><span style="color: #666;">' . $title . '</span> ' . $invoice_id . '</h1>
</div>


<div style="' . $dodavatel_margin . ' width: 281px; margin-left: 60px; float: left;">

<div style="margin-left: 0px;">
<p style="font-size: 10px; color: #666;">Dodavatel</p>
<p style="font-size: 13px; margin-bottom: 10px; font-family: Roboto-Medium, Helvetica;">Wellness Trade, s. r. o.</p>
<p style="font-size: 11px; line-height: 14px;margin-bottom: 0; padding-bottom: 0;">Vrbova 1277/32</p>
<p style="font-size: 11px;; margin-top: 0; padding-top: 0;">147 00 Praha</p>
<p style="font-size: 11px;; margin-top: 0; padding-top: 0;">Česká republika</p>

<table style="margin-top: 4px; font-size: 11px; float: left;">
<tr>
<td style="width: 40px;">IČ</td>
<td>29154871</td>
</tr>
<tr>
<td>DIČ</td>
<td>CZ29154871</td>
</tr>
</table>
</div>

</div>


<div style="width: 40px; margin-right: 6px; padding-top: 30px;text-align: center;float: left;">

<img src="../../assets/images/arrow-right.jpg" style="max-width: 100%; opacity: 0.3;">

</div>



<div style="' . $odberatel_margin . ' width: 33%; padding-right: 20px; float: right;">

<div>
<p style="font-size: 10px; color: #666;">Odběratel</p>
<p style="font-size: 13px; margin-bottom: 10px; font-family: Roboto-Medium, Helvetica;">' . $order_odberatel_name . '</p>
<p style="font-size: 11px; line-height: 14px;margin-bottom: 0; padding-bottom: 0;">' . $order_address . '</p>
<p style="font-size: 11px; margin-top: 0; padding-top: 0;">' . $order_postcode . ' ' . $order_city . '</p>
<p style="font-size: 11px; margin-top: 0; padding-top: 0;">' . $order_country . '</p>
' . $odberatel_dic_info . '
</div>

</div>

<div style="clear: both;"></div>

</div>

<hr style="color: #e7e7ef; margin-left: 40px; margin-right: 40px; width: 680px;">


<div style="padding: 5px 0 5px 62px;float: left; width: 38%;">
<table class="bank-table" style="float: left; color: #000;">

<tr>
<td class="first"><strong>Bankovní účet</strong></td>
<td style="padding-left: 0;"><strong style="color: #CE1212;">'.$currency['bank_account'].'</strong></td>
</tr>
<tr>
<td class="first">IBAN</td>
<td style="padding-left: 0; font-size: 11px;">'.$currency['iban'].'</td>
</tr>
<tr>
<td class="first">BIC</td>
<td style="padding-left: 0; font-size: 11px;">CEKOCZPP</td>
</tr>
<tr>
<td class="first"><strong>Variabilní symbol</strong></td>
<td style="padding-left: 0;"><strong>' . $variable_symbol . '</strong></td>
</tr>
</table>
</div>
<div style="padding: 5px 0 0 20px;float: left; width: 8%;">
</div>

<div style="padding: 5px 0 0 20px;float: left; width: 38%;">
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
<tr>
<td class="first">Způsob platby </td>
<td style="padding-left: 0;">' . $pay_method . '</td>
</tr>

</table>
</div>

<div style="clear: both;"></div>

<hr style="color: #e7e7ef; margin-left: 40px; margin-right: 40px; width: 680; margin-bottom: 14px;">

<h1 style="' . $h1_top . ' margin-left: 60px; font-family: Roboto-Light, Helvetica; margin-bottom: 14px; font-size: 14px;">Objednávka číslo <strong style="font-family: Roboto-Medium, Helvetica; font-size: 14px;">' . $order['id'] . '</strong> ze dne ' . $order['dateformated'] . '</h1>
<div style="width: 100%;  background-color: #e2f3f7; color: #232c31;  border-top-right-radius: 10px; border-top-left-radius: 10px;  border-bottom-left-radius: 10px; border-bottom-left-radius: 10px;">
<div style=" width: 40px; margin-left: 80px; float: left; margin-bottom: -300px;">
<img src="../../assets/images/zobracek.png">
</div>

<div style="clear: both;"></div>

<div style="width: 100%; margin: 3px 0 20px; ">
<table class="products" style="width: 100%;">
<tr class="head">
<td class="first" width="60px" style="border: 0;text-align: right; padding: 0 10px 0 0; color: #85becd;"></td>
<td class="first" style="border: 0; background-color: transparent;" width="300px"></td>
<td>Cena/ks bez DPH</td>
<td>Počet</td>
<td>Sleva %</td>
<td style="padding-right: 24px;">Sazba DPH</td>
<td style="padding-right: 30px;">' . $with_vat_price . '</td>
</tr>
' . $products . '

</table>
</div>
</div>

' . $break . '

<div style="width: 400px; float: left; padding-top: 26px;">
' . $eet_print . '
</div>

<div style="width: 280px; float: right; background-color: #efefef; height: auto; border-bottom-right-radius: 10px; border-bottom-left-radius: 10px;">
<div style="clear: both;"></div>

<table class="total" style="width: 100%;">
<tr>
<td class="first">Celkem bez DPH </td>
<td style="text-align: right; padding-right: 40px;">' . thousand_seperator($total['without_vat']) . $currency['sign'].'</td>
</tr>
<tr>
<td class="first">DPH ' . $vat . '%</td>
<td style="text-align: right; padding-right: 40px;">' . thousand_seperator($total['vat']) . $currency['sign'].'</td>
</tr>
'.$is_rounded.'
<tr>
<td class="first" style="font-size: 12px; padding-bottom: 12px; border-bottom: 0px solid #ececec; font-family: Roboto-Medium, Helvetica;">Cena celkem</td>
<td style="text-align: right; padding-right: 40px; border-bottom: 0px solid #ececec;  padding-bottom: 10px; font-size: 13px; font-family: Roboto-Medium, Helvetica;">' . thousand_seperator($total['single']) . $currency['sign'].'</td>
</tr>

</table>

</div>
<div style="width: 100%; float: right; text-align: right;">
<h2 style="font-size: 12px; float: right; text-align: right; margin-right: 16px; margin-top 16px;">' . $dont_pay . '</h2>
</div>


 <div style="position: absolute; left: 60px; right: 10px; bottom: 50px; width: 680px;">
   	<div style="width: 100%;padding: 10px 0px; font-size: 10px;">
   		' . $reverse_charge_text . '
   		' . $odd_text . '
   		<p style="font-size: 9px; margin-top: 24px; ">Městský soud v Praze, oddíl C, vložka 203387
   		<img src="../../assets/images/wtrazitko.jpg" style="width: 160px; margin-top: -60px; marign-left: 30px; margin-right: 0px; position: absolute; float: right;"></p>
   		   	

   	</div>
    </div>

    <div style="position: absolute; left: 60px; right: 10px; bottom: 20px; width: 680px;">
   	<table style="width: 100%;padding: 10px 0px; border-top: 1px solid #3a3f4d; font-size: 9px;">
   		<tr>
   		<td width="160px" style="padding: 10px 0;">Vystavil/a: ' . $client['user_name'] . '</td>
   		<td width="120px" style="padding: 10px 0;">ucetni@wellnesstrade.cz</td>
   		<td width="120px" style="padding: 10px 0;">+420 774 141 596</td>
   		<td style="padding: 10px 0; text-align: right;"><a href="https://www.wellnesstrade.cz/" style="color: #FFF; float: left;text-decoration: none;"><img src="../../assets/images/wlogofooter.png"></a></td>
   		</tr>

   	</table>
    </div>
';
//==============================================================
if (isset($_REQUEST['html']) && $_REQUEST['html']) {echo $html;exit;}
//==============================================================

$mpdf->WriteHTML($html);





$mysqli->query("INSERT INTO orders_invoices (id, currency, exchange_rate, order_id, type, date, status, invoice_id, price_without_vat, total_vat, rounded, total_price) VALUES ('".$invoice_id."', '".$currency_code."', '".$order['exchange_rate']."', '$id', '$type', '$insert_date', '$status', '$prev_id', '".$total['without_vat']."', '".$total['vat']."', '".$total['rounded']."', '".$total['single']."')");

$invoice_id = $mysqli->insert_id;

if(!empty($eet_print)){

//    serialize($receipt)

    $db_receipt = json_encode($receipt);
    $mysqli->query("UPDATE orders_invoices SET fik = '$fik', bkp = '$bkp', pkp = '$pkp', receipt = '".$db_receipt."' WHERE id = '$invoice_id'") or die($mysqli->error);

}

//$mysqli->query("UPDATE orders_invoices SET price_without_vat = , total_vat = , rounded = , total_price =  WHERE id = '$invoice_id'") or die($mysqli->error);



if($order['payment_method'] == 'cash'){

    $balance_query = $mysqli->query("SELECT balance FROM cashier WHERE location_id = '".$order['location_id']."' ORDER BY id DESC limit 1")or die($mysqli->error);
    $balance = mysqli_fetch_assoc($balance_query);

    $description = 'Objednávka číslo ' . $order['id'];
    $next_balance = $balance['balance'] + $total['single'];

    if($odd){
        $income = 0; $outcome = $total['single'];
    }else{
        $income = $total['single']; $outcome = 0;
    }


    $mysqli->query("INSERT INTO cashier 
        (
         date, 
         invoice_id, 
         var_sym, 
         description, 
         income, 
         outcome, 
         balance, 
         location_id,
         admin_id, 
         aggregate_type
     ) VALUES (
        CURRENT_TIMESTAMP(),
        '".$invoice_id."',
        '".$id."',
        '".$description."',
        '".$income."',
        '".$outcome."',
        '".$next_balance."',
        '".$order['location_id']."',
        '".$client['id']."',
        '".$type."'
    )")or die($mysqli->error);

    // set order as paid
    if ($type == 'order') {

        $mysqli->query("UPDATE orders SET paid = 1, paid_value = total, payment_date = CURRENT_TIMESTAMP() WHERE id = '".$id."'")or die($mysqli->error);

    }elseif($type == 'service'){

        $mysqli->query("UPDATE services SET paid = 1, paid_value = price, payment_date = CURRENT_TIMESTAMP() WHERE id = '".$id."'")or die($mysqli->error);

    }

}


$mpdf->Output($_SERVER['DOCUMENT_ROOT'] . '/admin/data/invoices/orders/' . $invoice_id . '.pdf', 'F');

Header("Location:" . $redirect);
exit;