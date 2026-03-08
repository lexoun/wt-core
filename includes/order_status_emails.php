<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";

$order_title = '';
$maincolor = '';
$logo = '';
$title = '';
$show_bank = '';
$email_text = '';

$mail = new PHPMailer\PHPMailer\PHPMailer(true);
//$mail->SMTPDebug = 3;                               // Enable verbose debug output
$mail->CharSet = 'UTF-8';
$mail->isSMTP();

// todo přidat LEFT JOINS.... namísto direct joins

$orderquery = $mysqli->query('SELECT *, o.id as id, DATE_FORMAT(order_date, "%d. %M %Y") as dateformated, DATE_FORMAT(order_date, "%H:%i:%s") as hoursmins FROM (orders o, shops_payment_methods p, shops_delivery_methods d) LEFT JOIN shops_locations l ON l.id = d.location_id WHERE o.id="' . $id . '" AND o.order_shipping_method = d.link_name AND o.payment_method = p.link_name') or die($mysqli->error);
$order = mysqli_fetch_assoc($orderquery);

$currency = currency_eshop($order['order_currency']);
$order_site = $order['order_site'];
$order_status = $order['order_status'];

// todo function to set everything either for local pickup or for else at ONE place
if($order['shop_method_id'] == 'local_pickup'){

    $localPickupAddress = json_decode($order['address']);
    $localPickupOpening = json_decode($order['opening_hours']);

    $opening_hours = '';
    foreach($localPickupOpening as $key => $value){

        $opening_hours .=
            '<tr>
  <td>'.$key.':</td>
  <td>'.$value.'</td>
</tr>';

    }

}

if ($order_site == 'saunahouse') {

    $mail->Host = 'mail.webglobe.cz'; // Specify main and backup SMTP servers
    $mail->SMTPAuth = true; // Enable SMTP authentication
    $mail->Username = 'eshop@saunahouse.cz'; // SMTP username
    $mail->Password = '9HE4fL3n'; // SMTP password
    $mail->SMTPSecure = 'ssl'; // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 465; // TCP port to connect to

    $mail->From = 'eshop@saunahouse.cz';
    $mail->FromName = 'Saunahouse.cz';

    $mail->DKIM_domain = 'saunahouse.cz';
    $mail->DKIM_private = 'https://www.saunahouse.cz/wp-content/keys/saunahouse-private.key';
    $mail->DKIM_selector = 'phpmailer';
    $mail->DKIM_passphrase = '';
    $mail->DKIM_identity = 'eshop@saunahouse.cz';

    $maincolor = '#950026';
    $logo = '<img src="https://www.saunahouse.cz/wp-content/uploads/2016/06/saunahouse-logo.png" alt="Saunahouse.cz" style="border: none; display: inline; font-size: 14px; font-weight: bold; height: auto; line-height: 100%; outline: none; text-decoration: none; text-transform: capitalize;">';

    $email_text = 'eshop@saunahouse.cz';

} elseif ($order_site == 'spahouse') {

    $mail->Host = 'mail.webglobe.cz'; // Specify main and backup SMTP servers
    $mail->SMTPAuth = true; // Enable SMTP authentication
    $mail->Username = 'eshop@spahouse.cz'; // SMTP username
    $mail->Password = '9HE4fL3n'; // SMTP password
    $mail->SMTPSecure = 'ssl'; // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 465; // TCP port to connect to

    $mail->From = 'eshop@spahouse.cz';
    $mail->FromName = 'Spahouse.cz';

    $mail->DKIM_domain = 'spahouse.cz';
    $mail->DKIM_private = 'https://www.spahouse.cz/wp-content/newkeys/spahouse-private.key';
    $mail->DKIM_selector = 'phpmailer';
    $mail->DKIM_passphrase = '';
    $mail->DKIM_identity = 'eshop@spahouse.cz';

    $maincolor = '#39a2e5';
    $logo = '<img src="https://www.spahouse.cz/wp-content/uploads/2018/04/spahouse-logo-4k.png" alt="Spahouse.cz" style="border: none; display: inline; font-size: 14px; font-weight: bold; height: auto; line-height: 100%; outline: none; text-decoration: none; text-transform: capitalize;">';

    $email_text = 'eshop@spahouse.cz';

} elseif ($order_site == 'spamall') {

    $mail->Host = 'mail.webglobe.cz'; // Specify main and backup SMTP servers
    $mail->SMTPAuth = true; // Enable SMTP authentication
    $mail->Username = 'info@spamall.cz'; // SMTP username
    $mail->Password = 'Wellnesstrade2510!'; // SMTP password
    $mail->SMTPSecure = 'ssl'; // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 465; // TCP port to connect to

    $mail->From = 'info@spamall.cz';
    $mail->FromName = 'Spamall.cz';

    $mail->DKIM_domain = 'spamall.cz';
    $mail->DKIM_private = 'https://www.spamall.cz/wp-content/keys/spamall-private.key';
    $mail->DKIM_selector = 'phpmailer';
    $mail->DKIM_passphrase = '';
    $mail->DKIM_identity = 'info@spamall.cz';

    $maincolor = '#0787ea';
    $logo = '<img src="https://www.spamall.cz/wp-content/uploads/2017/01/logo-sm-1.png" alt="Spamall.cz" style="border: none; display: inline; font-size: 14px; font-weight: bold; height: auto; line-height: 100%; outline: none; text-decoration: none; text-transform: capitalize;">';
    $email_text = 'info@spamall.cz';

} elseif ($order_site == 'wellnesstrade') {

    $mail->Host = 'mail.webglobe.cz'; // Specify main and backup SMTP servers
    $mail->SMTPAuth = true; // Enable SMTP authentication
    $mail->Username = 'admin@wellnesstrade.cz'; // SMTP username
    $mail->Password = 'RD4ufcLv'; // SMTP password
    $mail->SMTPSecure = 'ssl'; // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 465; // TCP port to connect to

    $mail->From = 'admin@wellnesstrade.cz';
    $mail->FromName = 'WellnessTrade.cz';

    $mail->DKIM_domain = 'wellnesstrade.cz';
    $mail->DKIM_private = $_SERVER['DOCUMENT_ROOT'] . '/admin/config/keys/private.key';
    $mail->DKIM_selector = 'phpmailer';
    $mail->DKIM_passphrase = '';
    $mail->DKIM_identity = 'admin@wellnesstrade.cz';

    $maincolor = '#2b303a';
    $logo = '<img src="https://www.wellnesstrade.cz/wp-content/uploads/2015/03/logoblack.png" alt="Wellnesstrade.cz" style="border: none; display: inline; font-size: 14px; font-weight: bold; height: auto; line-height: 100%; outline: none; text-decoration: none; text-transform: capitalize;">';

    $clientquery = $mysqli->query('SELECT customer FROM demands WHERE id="' . $order['client_id'] . '"') or die($mysqli->error);
    $client = mysqli_fetch_assoc($clientquery);

    if (isset($client['customer']) && $client['customer'] == 1 || $client['customer'] == 3) {

        $email_text = 'eshop@spahouse.cz';

    } elseif (isset($client['customer']) && $client['customer'] == 0) {

        $email_text = 'eshop@saunahouse.cz';

    }else{

        $email_text = 'info@wellnesstrade.cz';

    }

}

$products_query = $mysqli->query("SELECT *, p.id as ajdee, o.price as price 
    FROM products p, orders_products_bridge o 
    WHERE p.id = o.product_id AND o.aggregate_id = '" . $id . "' AND o.aggregate_type = 'order'");

$price_with_dph = 0;
$table_products = '';
$total_discount = 0;

while ($product = mysqli_fetch_array($products_query)) {


    $has_discount = false;
    $current_discount = '-';
    if(!empty($product['discount'])){

        $has_discount = true;

        $current_discount = $product['discount'].' %';

        $total_discount += (number_format($product['price'] / 100 * ($product['discount']), 2, '.', '')) * $product['quantity'];

//        Todo calculate by discount_net? Why? What for?
//        $total_discount += $price['discount_net'] / 100 * ($product['discount']), 2, '.', '')) * $product['quantity'];

    }



    $variation_text = '';
    if ($product['variation_id'] != 0) {

        $variation_query = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $product['variation_id'] . "'");

        while ($variation = mysqli_fetch_array($variation_query)) {
            $variation_text = $variation_text . $variation['name'] . ': ' . $variation['value'] . '<br>';

        }

    }

    $table_products .= '<tr>
				<td style="text-align:left;vertical-align:middle;border:1px solid #eee;word-wrap:break-word;color:#737373;padding:12px">' . $product['productname'] . '<br><small>' . $variation_text . '</small>
				</td>
							<td style="text-align:center;vertical-align:middle;border:1px solid #eee;color:#737373;padding:12px">' . $product['quantity'] . '</td>
							<td style="text-align:center;vertical-align:middle;border:1px solid #eee;color:#737373;padding:12px"><span>' . thousand_seperator($product['price'] * $product['quantity']) . ' <span>'.$currency['sign'].'</span></span></td>
						</tr>';

    $price_with_dph = $price_with_dph + ($product['price'] * $product['quantity']);
}




if($has_discount) {

//    $discount = get_price($total_discount, $coeficient);

    $table_products .= '<tr>
				<td style="text-align:left;vertical-align:middle;border:1px solid #eee;word-wrap:break-word;color:#737373;padding:12px">Sleva
				</td>
							<td style="text-align:center;vertical-align:middle;border:1px solid #eee;color:#737373;padding:12px">1</td>
							<td style="text-align:center;vertical-align:middle;border:1px solid #eee;color:#737373;padding:12px"><span>-'. thousand_seperator($total_discount).' <span>'.$currency['sign'].'</span></span></td>
						</tr>';

}

$midprice = $order['total'] - $order['delivery_price'];

$totalprice = $order['total'];

$address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $order['shipping_id'] . '" WHERE b.id = "' . $order['billing_id'] . '"') or die($mysqli->error);
$address = mysqli_fetch_assoc($address_query);

$phone = str_replace(' ', '', $address['billing_phone']);
$billing_phone = substr($phone, -9);

if ($order['shipping_id'] != 0) {

    $shipping_name = $address['shipping_name'];
    $shipping_surname = $address['shipping_surname'];
    $shipping_street = $address['shipping_street'];
    $shipping_zipcode = $address['shipping_zipcode'];
    $shipping_city = $address['shipping_city'];

    if (isset($address['shipping_country']) && $address['shipping_country'] == 'CZ') {$shipping_country = 'Česká republika';} elseif (isset($address['shipping_country']) && $address['shipping_country'] == 'SK') {$shipping_country = 'Slovensko';} elseif (isset($address['shipping_country']) && $address['shipping_country'] == 'PL') {$shipping_country = 'Polsko';} elseif (isset($address['shipping_country']) && $address['shipping_country'] == 'AT') {$shipping_country = 'Rakousko';} else { $shipping_country = $address['shipping_country'];}

} else {

    $shipping_name = $address['billing_name'];
    $shipping_surname = $address['billing_surname'];
    $shipping_street = $address['billing_street'];
    $shipping_zipcode = $address['billing_zipcode'];
    $shipping_city = $address['billing_city'];

    if (isset($address['billing_country']) && $address['billing_country'] == 'CZ') {$shipping_country = 'Česká republika';} elseif (isset($address['billing_country']) && $address['billing_country'] == 'SK') {$shipping_country = 'Slovensko';} elseif (isset($address['billing_country']) && $address['billing_country'] == 'PL') {$shipping_country = 'Polsko';} elseif (isset($address['billing_country']) && $address['billing_country'] == 'AT') {$shipping_country = 'Rakousko';} else { $shipping_country = $address['billing_country'];}

}

if (isset($address['billing_country']) && $address['billing_country'] == 'CZ') {$billing_country = 'Česká republika';} elseif (isset($address['billing_country']) && $address['billing_country'] == 'SK') {$billing_country = 'Slovensko';} elseif (isset($address['billing_country']) && $address['billing_country'] == 'PL') {$billing_country = 'Polsko';} elseif (isset($address['billing_country']) && $address['billing_country'] == 'AT') {$billing_country = 'Rakousko';} else { $billing_country = $address['billing_country'];}

$customer_note = '';
if ($order['customer_note'] != "") {

    $customer_note = '<p><strong style="color:' . $maincolor . '">Doplňující informace:</strong> ' . $order['customer_note'] . '</p>';
}

$customer_text = '<h2 style="color: ' . $maincolor . ';display:block;font-size:18px;font-weight:bold;line-height:130%;margin:24px 0 8px;text-align:left">Informace o zákazníkovi</h2>
<ul>
<li>
<strong style="color:' . $maincolor . '">E-mail:</strong> <span style="color:#2b303a;"><a href="mailto:' . $order['customer_email'] . '" target="_blank">' . $order['customer_email'] . '</a></span>
</li>
            <li>
<strong style="color:' . $maincolor . '">Tel:</strong> <span style="color:#2b303a;">' . $billing_phone . '</span>
</li>
    </ul>

    ' . $customer_note . '


    <table cellspacing="0" cellpadding="0" style="width:100%;vertical-align:top" border="0"><tbody><tr>
<td valign="top" width="50%">
			<h3 style="color: ' . $maincolor . ';display:block;font-size:16px;font-weight:bold;line-height:130%;margin:16px 0 8px;text-align:left">Doručovací adresa</h3>

			<p style="color:#505050;font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif;margin:0 0 16px">' . $shipping_name . ' ' . $shipping_surname . '<br>' . $shipping_street . '<br>' . $shipping_zipcode . '&nbsp; &nbsp; &nbsp;' . $shipping_city . '<br>
			' . $shipping_country . '</p>
		</td>
					<td valign="top" width="50%">
				<h3 style="color: ' . $maincolor . ';display:block;font-size:16px;font-weight:bold;line-height:130%;margin:16px 0 8px;text-align:left">Fakturační adresa</h3>

				<p style="color:#505050;font-family:&quot;Helvetica Neue&quot;,Helvetica,Roboto,Arial,sans-serif;margin:0 0 16px">' . $address['billing_company'] . ' ' . $address['billing_name'] . ' ' . $address['billing_surname'] . '<br>' . $address['billing_street'] . '<br>' . $address['billing_zipcode'] . '&nbsp; &nbsp; &nbsp;' . $address['billing_city'] . '<br>
				' . $billing_country . '</p><span class="HOEnZb"><font color="#888888">
			</font></span></td></tr></tbody></table>

';

$pay_text = $order['pay_text'];

if ($order['shop_method_id'] == 'local_pickup') {

    $delivery_text = $order['delivery_text'];

} else {

    $delivery_text = $order['delivery_text'] . ' - ' . $order['delivery_price'] .$currency['sign'].'';

}

if (isset($order['payment_method']) && $order['payment_method'] == 'bacs') {

    $payment_text = '<p style="margin: 0 0 14px;">' . $order['delivery_text'] . ' a platba předem na bankovní účet:</p><table width="300" cellspacing="1" cellpadding="1" border="0">
		<tbody>
		<tr>
		  <td><strong style="color: ' . $maincolor . '">Bankovní účet:</strong></td>
		  <td style="text-align:right">' . $currency['bank_account']. '</td>
		<tr>
		  <td><strong style="color: ' . $maincolor . '">Variabilní symbol:</strong></td>
		  <td style="text-align:right">' . $order['id'] . '</td>
		</tr>
		<tr>
		  <td><strong style="color: ' . $maincolor . '">Částka:</strong></td>
		  <td style="text-align:right"><strong style="color: ' . $maincolor . ';">' . thousand_seperator($totalprice) . ' '.$currency['sign'].'</strong></td>
		</tr>
		</tbody>
		</table>';

} else {

    $payment_text = '' . $order['delivery_text'] . ' a platba ' . $order['pay_text'] . '.';

}

if ($order_status == '0') {

    $title = 'Děkujeme Vám za objednávku';

    $opening_text = '<p style="margin: 0 0 16px;">rádi bychom Vás informovali, že jsme úspěšně přijali Vaši objednávku. Ohledně stavu objednávky Vás budeme dále informovat. Detaily Vaší objednávky jsou zobrazeny níže pro Váš přehled:</p>';

    $show_bank = $payment_text;

    $order_title = 'Objednávka č. ' . $order['id'];

    $subject = 'Potvrzení objednávky ' . $order['id'];

} elseif ($order_status == '1') {

    $title = 'Objednávka přijata';

    $opening_text = '<p style="margin: 0 0 16px;">Vážený zákazníku,</p><p style="margin: 0 0 16px;">rádi bychom Vás informovali, že Vaše objednávka právě prochází kontrolou a přípravou, nebo čeká na naskladnění objednaného zboží. Detaily Vaší objednávky jsou zobrazeny níže pro Váš přehled:</p>';

    $show_bank = $payment_text;

    $order_title = 'Objednávka č. ' . $order['id'];

    $subject = 'Přijetí objednávky ' . $order['id'];

} elseif ($order_status == '2') {

    if ($order['shop_method_id'] == 'local_pickup') {

        $title = 'Objednávka připravena k vyzvednutí';

        $opening_text = '<p style="margin: 0 0 16px;">Vážený zákazníku,</p><p style="margin: 0 0 16px;">objednávka je již připravena k vyzvednutí na naší pobočce '.$order['full_name'].'. Detaily Vaší objednávky jsou zobrazeny níže pro Váš přehled:</p>';

        $show_bank = '<table width="300" cellspacing="1" cellpadding="1" border="0">
<tbody>
<tr>
  <td><strong style="color: ' . $maincolor . '">'.$localPickupAddress->street.'</strong></td>
</tr>
<tr>
  <td><strong style="color: ' . $maincolor . '">'.$localPickupAddress->city.', '.$localPickupAddress->zipcode.'</strong></td>
</tr>

</tbody>
</table>
<table width="300" cellspacing="1" cellpadding="1" border="0" style="    margin-top: 10px;">
<tbody>
'.$opening_hours.'
</tbody>
</table>';

        $order_title = 'Objednávka č. ' . $order['id'];

        $subject = 'Objednávka ' . $order['id'] . ' je připravena k vyzvednutí';

    } else {

        $title = 'Objednávka připravena k odeslání';

        $opening_text = '<p style="margin: 0 0 16px;">Vážený zákazníku,</p><p style="margin: 0 0 16px;">objednávka je již připravena k odeslání. Detaily Vaší objednávky jsou zobrazeny níže pro Váš přehled:</p>';

        if (isset($order['payment_method']) && $order['payment_method'] == 'bacs' && $order['shop_method_id'] !== 'local_pickup') {

            $show_bank = '<p style="margin: 0 0 14px;"><strong>Objednávka nebude odeslána, dokud nebudeme evidovat Vaší platbu na bankovním účtě.</strong></p>' . $payment_text;

        } else {

            $show_bank = $payment_text;
        }

        $order_title = 'Objednávka č. ' . $order['id'];

        $subject = 'Objednávka ' . $order['id'] . ' je připravena k odeslání';

    }

} elseif ($order_status == '3') {

    if ($order['shop_method_id'] !== 'local_pickup') {

        $title = 'Objednávka převzata';

        $opening_text = '<p style="margin: 0 0 16px;">Vážený zákazníku,</p><p style="margin: 0 0 16px;">rádi bychom Vás informovali, že jsme právě předali Vaší objednávku.</p> <p style="margin: 0 0 16px;">Detaily Vaší objednávky jsou zobrazeny níže pro Váš přehled:</p>';

        $show_bank = '';

        $order_title = 'Objednávka č. ' . $order['id'];

        $subject = 'Převzetí objednávky ' . $order['id'];

    } else {

        $title = 'Objednávka odeslána';

        $opening_text = '<p style="margin: 0 0 16px;">Vážený zákazníku,</p><p style="margin: 0 0 16px;">rádi bychom Vás informovali, že jsme právě odeslali Vaší objednávku. <br>Pro sledování objednávky můžete použít sledovací číslo <strong>' . $order['order_tracking_number'] . '</strong> na webu PPL nebo kliknout na následující odkaz: <a href="https://www.ppl.cz/main2.aspx?cls=Package&idSearch=18AJVVN" target="_blank">sledovat zásilku</a>.</p> <p style="margin: 0 0 16px;">Detaily Vaší objednávky jsou zobrazeny níže pro Váš přehled:</p>';

        $show_bank = '';

        $order_title = 'Objednávka č. ' . $order['id'];

        $subject = 'Odeslání objednávky ' . $order['id'];

    }

} elseif ($order_status == '4') {

    $title = 'Objednávka stornována';

    $opening_text = '<p style="margin: 0 0 16px;">Rádi bychom Vás informovali, že Vaše objednávka byla stornována.</p>';

    $show_bank = '';

    $order_title = 'Objednávka č. ' . $order['id'];

    $subject = 'Stornování objednávky ' . $order['id'];

}

if (isset($alternate_text) && $alternate_text != "") {

    $opening_text = '<p style="margin: 0 0 16px;">' . $alternate_text . '</p>';

}

$body = '<div id="wrapper" dir="ltr" style="background-color: #f5f5f5; margin: 0; padding: 70px 0 70px 0; -webkit-text-size-adjust: none !important; width: 100%;">
			<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tbody><tr>
<td align="center" valign="top">
						<div id="template_header_image">
							<p style="margin-top: 0; margin-bottom: 3em;">' . $logo . '</p>						</div>
						<table border="0" cellpadding="0" cellspacing="0" width="780" id="template_container" style="box-shadow: 0 1px 4px rgba(0,0,0,0.1) !important; background-color: #fdfdfd; border: 1px solid #dcdcdc; border-radius: 3px !important;">
<tbody><tr>
<td align="center" valign="top">
									<!-- Header -->
									<table border="0" cellpadding="0" cellspacing="0" width="780" id="template_header" style="background-color: ' . $maincolor . '; border-radius: 3px 3px 0 0 !important; color: #ffffff; border-bottom: 0; font-weight: bold; line-height: 100%; vertical-align: middle; font-family: Helvetica Neue, Helvetica, Roboto, Arial, sans-serif;"><tbody><tr>
<td id="header_wrapper" style="padding: 36px 48px; display: block;">
<h1 style="color: #ffffff; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif; font-size: 30px; font-weight: 300; line-height: 150%; margin: 0; text-align: left; text-shadow: 0 1px 0 #aa3351; -webkit-font-smoothing: antialiased;">' . $title . '</h1>
											</td>
										</tr></tbody></table>
<!-- End Header -->
</td>
							</tr>
<tr>
<td align="center" valign="top">
									<!-- Body -->
									<table border="0" cellpadding="0" cellspacing="0" width="780" id="template_body"><tbody><tr>
<td valign="top" id="body_content" style="background-color: #fdfdfd;">
												<!-- Content -->
												<table border="0" cellpadding="20" cellspacing="0" width="100%"><tbody><tr>
<td valign="top" style="padding: 48px;">
															<div id="body_content_inner" style="color: #737373; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif; font-size: 14px; line-height: 150%; text-align: left;">
' . $opening_text . '' . $show_bank . '
<h2 style="color: ' . $maincolor . '; display: block; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif; font-size: 18px; font-weight: bold; line-height: 130%; margin: 24px 0 10px; text-align: left;">' . $order_title . '</h2>

<table cellspacing="0" cellpadding="6" style="width:100%;color:#737373;border:1px solid #e4e4e4" border="1">
<thead><tr>
<th scope="col" style="text-align:left;color:#737373;border:1px solid #e4e4e4;padding:12px;">Produkt</th>
			<th scope="col" style="text-align:center;color:#737373;border:1px solid #e4e4e4;padding:12px; width: 20%;">Množství</th>
			<th scope="col" style="text-align:center;color:#737373;border:1px solid #e4e4e4;padding:12px">Cena</th>
		</tr></thead>
<tbody>
' . $table_products . '
</tbody>
<tfoot>
<tr>
<th scope="row" colspan="1" style="text-align:left;border-top-width:4px;color:#737373;border:1px solid #e4e4e4;padding:12px">Mezisoučet:</th>
						<td colspan="2" style="text-align:center;    padding: 12px; border-top-width:4px;color:#737373;border:1px solid #e4e4e4;"><span>' . thousand_seperator($midprice) . ' <span>'.$currency['sign'].'</span></span></td>
					</tr>
<tr>
<th scope="row" colspan="1" style="text-align:left;color:#737373;border:1px solid #e4e4e4;padding:12px">Doručení:</th>
						<td colspan="2" style="text-align:center;color:#737373;border:1px solid #e4e4e4;padding:12px">' . $delivery_text . '</td>
					</tr>
<tr>
<th scope="row" colspan="1" style="text-align:left;color:#737373;border:1px solid #e4e4e4;padding:12px">Platební metoda:</th>
						<td colspan="2" style="text-align:center;color:#737373;border:1px solid #e4e4e4;padding:12px">' . ucfirst($pay_text) . '</td>
					</tr>
<tr>
<th scope="row" colspan="1" style="text-align:left;color:#737373;border:1px solid #e4e4e4;padding:12px">Cena celkem:</th>
						<td colspan="2" style="text-align:center; padding: 12px; font-size: 18px; font-weight: 500; color:#222;border:1px solid #e4e4e4;"><span>' . thousand_seperator($totalprice) . '  <span>'.$currency['sign'].'</span></span></td>
					</tr>
</tfoot>
</table>

' . $customer_text . '
															</div>
														</td>
													</tr></tbody></table>
<!-- End Content -->
</td>
										</tr></tbody></table>
<!-- End Body -->
</td>
							</tr>
<tr>
<td align="center" valign="top">
									<!-- Footer -->
									<table border="0" cellpadding="10" cellspacing="0" width="780" id="template_footer"><tbody><tr>
<td valign="top" style="padding: 0; -webkit-border-radius: 6px;">
												<table border="0" cellpadding="10" cellspacing="0" width="100%"><tbody><tr>
<td colspan="2" valign="middle" id="credit" style="padding: 0 48px 18px 48px; -webkit-border-radius: 6px; border: 0; color: ' . $maincolor . '; font-family: Arial; font-size: 12px; line-height: 125%; text-align: center;">
															<p>ceny jsou uvedeny včetně DPH</p>
														</td>
													</tr></tbody></table>
</td>
										</tr></tbody></table>


<!-- End Footer -->
</td>
							</tr>
</tbody></table>

<table cellpadding="0" cellspacing="0" style="font-size:11px;color:#999999; margin-top: 40px;">
					<tbody><tr>
						<td valign="top" width="58" style="padding-top: 1px;">
							<img src="https://www.wellnesstrade.cz/admin/assets/images/1472614678_phone.png" alt="phone" class="CToWUd">
						</td>
						<td>
							<strong>
							<a href="tel:%2B420%20222%20562%20009" value="+420222562009" target="_blank" style="font-family: Helvetica Neue, Helvetica, Roboto, Arial, sans-serif; color:#333333;font-size:22px;text-decoration:none">+420 777 624 350</a></strong><br>
							Pracovní dny od 10:00 do 18:00 hodin
						</td>
						<td width="60"></td>
						<td valign="top" width="58" style="padding-top: 1px;">
							<img src="https://www.wellnesstrade.cz/admin/assets/images/1472614684_mail.png" alt="mail" class="CToWUd">
						</td>
						<td>
							<strong><a style="font-family: Helvetica Neue, Helvetica, Roboto, Arial, sans-serif; color:#333333;font-size:22px;text-decoration:none" href="mailto:' . $email_text . '" target="_blank">' . $email_text . '</a></strong><br>
							Na email se snažíme odpovídat okamžitě
						</td>
					</tr>
				</tbody></table>


</td>
				</tr></tbody></table>
</div>';

$mail->addAddress($order['customer_email']);

if (isset($order['order_status']) && $order['order_status'] == 0) {
    $mail->AddBCC($email_text);
}


//$mail->AddBCC('becher.filip@gmail.com');


$mail->isHTML(true); // Set email   format to HTML

$mail->Subject = $subject;
$mail->Body = $body;
$mail->AltBody = strip_tags($body);

if (!$mail->send()) {
    echo 'Message could not be sent.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
}
