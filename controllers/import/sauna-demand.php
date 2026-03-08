<?php
include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";

$name = $mysqli->real_escape_string($_POST['your-name']);
$email = $mysqli->real_escape_string($_POST['your-email']);
$phone = $mysqli->real_escape_string($_POST['your-phone']);
$location = $mysqli->real_escape_string($_POST['menu-10']);

$message = $mysqli->real_escape_string($_POST['your-message']);
$subject = $mysqli->real_escape_string($_POST['subject-demand']);


// Mail
$target_shop = 'saunahouse';

require_once MODEL . 'mailsModel.php';


// todo automize via DB
if($location == 'Praha - Braník'){

    $loc['name'] = 'Praha';
    $loc['phone'] = '776 55 66 33';
    $loc['seller'] = 'Michael Bäumel';
    $loc['image'] = 'mail-michael.jpg';
    $loc['address'] = 'Vrbova 32, Praha 4, 147 00';
    $loc['map_url'] = 'https://goo.gl/maps/dEFEW5244Az';
    $loc['map_image'] = 'mail-praha.jpg';
    $loc['opening_hours'] = '<table style="width: 240px; margin: 20px auto 0; text-align: center; padding-top: 20px; border-top: 1px solid #ecebeb;">
<tbody>
<tr>
<td>Po–Čt</td>
<td>10:00–12:00, 13:00–18:00</td>
</tr>
<tr>
<tr>
<td>Pá</td>
<td>10:00–12:00, 13:00–17:00</td>
</tr>
<tr>
<td>So</td>
<td>10:00–12:00, 13:00–15:00</td>
</tr>
<tr>
<td>Ne</td>
<td>zavřeno</td>
</tr>
</tbody>
</table>';

    $showroom = 2;
    // petr
//    $admin_id = 2127;

    // michal
    $admin_id = 557;

}elseif($location == 'Brno'){

    $loc['name'] = 'Brno';
    $loc['phone'] = '773 00 55 88';
    $loc['seller'] = 'Vít Berger';
    $loc['image'] = 'mail-vitek.jpg';
    $loc['address'] = 'Heršpická 1035/11f, Brno, 639 00';
    $loc['map_url'] = 'https://maps.app.goo.gl/Sg4KTU9rdnAwHM5v6';
    $loc['map_image'] = 'mail-brno.jpg';

    $loc['opening_hours'] = '<table style="width: 240px; margin: 20px auto 0; text-align: center; padding-top: 20px; border-top: 1px solid #ecebeb;">
<tbody>
<tr>
<td>Po–Pá</td>
<td>10:00–12:00, 13:00–18:00</td>
</tr>
<tr>
<td>So–Ne</td>
<td>zavřeno&nbsp;/ po telefonické domluvě</td>
</tr>
</tbody>
</table>';

    $showroom = 3;
    $admin_id = 2190;

}elseif($location == 'Hradec Králové'){

    $loc['name'] = 'Hradec Králové';
    $loc['phone'] = '774 44 33 14';
    $loc['seller'] = 'Petr Hloušek';
    $loc['image'] = 'hlousek.jpg';
    $loc['address'] = 'Náměstí 5. května 888, Hradec Králové, 500 02';
    $loc['map_url'] = 'https://goo.gl/maps/pGmpmCkn5WKUwTLv5';
    $loc['map_image'] = 'map-hradec.jpg';
    $loc['opening_hours'] = '<table style="width: 300px; margin: 20px auto 0; text-align: center; padding-top: 20px; border-top: 1px solid #ecebeb;">
<tbody>
<tr>
<td>Po–Čt</td>
<td>9:30–12:00, 13:00–17:30</td>
</tr>
<tr>
<td>Pá</td>
<td>9:30–12:00, 13:00–17:00</td>
</tr>
<tr>
<td>So–Ne</td>
<td>zavřeno&nbsp;/ po telefonické domluvě</td>
</tr>
</tbody>
</table>';

    $showroom = 7;
    $area = 'prague';

    $admin_id = 8704;

}else{
    exit;
}



function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


function setProduct($subject){

    global $mysqli;

    $product = 'sauna';

    $get_all_products = $mysqli->query("SELECT * FROM warehouse_products WHERE customer = 0 ")or die($mysqli->error);

    while($products = mysqli_fetch_assoc($get_all_products)){

        if (strpos($subject, $products['brand'].' '.$products['fullname']) !== false) {

            $product = $products['connect_name'];
            break;

        }

    }

    return $product;
}



$product = setProduct($subject);

$secretstring = generateRandomString(14);

$parts = explode(" ", $name);
$lastname = array_pop($parts);
$firstname = implode(" ", $parts);

// todo phone prefix

$insert_billing = $mysqli->query("INSERT INTO addresses_billing (billing_phone, billing_email, billing_name, billing_surname) VALUES ('" . $phone . "', '" . $email . "', '" . $firstname . "', '" . $lastname . "')") or die($mysqli->error);
$billing_id = $mysqli->insert_id;

$mysqli->query("INSERT INTO demands (billing_id, user_name, admin_id, showroom, description, customer, date, product, email, phone, status, secretstring)
VALUES ('" . $billing_id . "', '" . $name . "', '" . $admin_id . "', '" . $showroom . "','" . $message . "', '0',  CURRENT_TIMESTAMP(), '$product', '" . $email . "', '" . $phone . "', '1','" . $secretstring . "')") or die($mysqli->error);

$id = $mysqli->insert_id;

$mail = connectMail($target_shop);

$body = '<div id="wrapper" dir="ltr" style="background-color: #f5f5f5; margin: 0; padding: 40px 0 40px 0; -webkit-text-size-adjust: none !important; width: 100%;">
			<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tbody><tr>
<td align="center" valign="top">
						<div id="template_header_image">
							<p style="margin-top: 0; margin-bottom: 30px;"><img src="https://www.saunahouse.cz/wp-content/uploads/2018/10/saunahouse-logo-4k.png" alt="Saunahouse.cz" style="height: 64px;" height="64"></p>						</div>
						<table border="0" cellpadding="0" cellspacing="0" width="780" id="template_container" style="box-shadow: 0 1px 4px rgba(0,0,0,0.1) !important; background-color: #fdfdfd; border: 1px solid #dcdcdc; border-radius: 3px !important;">
<tbody>
<tr>
<td align="center" valign="top">
<table border="0" cellpadding="0" cellspacing="0" width="780" id="template_body"><tbody><tr>
<td valign="top" id="body_content" style="background-color: #fdfdfd;">
<!-- Content -->
<table border="0" cellpadding="20" cellspacing="0" width="100%"><tbody><tr>
<td valign="top" style="padding: 48px;">															<div id="body_content_inner" style="color: #333; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif; font-size: 14px; line-height: 25px; text-align: left;">
<p>Dobrý den,</p>
<p>vážíme si Vaší poptávky a zájmu o naše služby. Poptávku nyní zpracováváme a budeme Vás kontaktovat jakmile to bude možné.</p> 
<p>Děkujeme <br><br>Tým Saunahouse.cz</p>											
</div>
</td>
</tr>
</tbody></table>
</td>
  <tr>
    <td style="font-family: helvetica, roboto">
  <img src="https://www.wellnesstrade.cz/files/showroom-mail.jpg" style="width: 100%;">
      <div style="padding: 32px 40px 40px;">
        <p>Vaši poptávku vyřizuje</p>
        <div style="text-align:center; margin: 20px 20px 14px; font-style: italic;">
       <img src="https://www.wellnesstrade.cz/files/'.$loc['image'].'" style="width: 140px;">
          <p style="margin: 8px 0 4px; font-size: 18px; font-weight: bold;">'.$loc['seller'].'</p>
<p style="margin: 8px 0 4px; color: #666">Vedoucí pobočky '.$loc['name'].'</p>
<p style="margin: 8px 0 4px; color: #666">Tel.: +420 '.$loc['phone'].'</p>
        </div>
       '.$loc['opening_hours'].'
       </div>
      <div style="background-color: #eee; padding: 24px 22px 22px; text-align: center; color: #333; font-weight: bold; font-size: 14px;">
        Neváhejte se přijít podívat do našeho showroomu ('.$loc['name'].'):</div>
      <a href="'.$loc['map_url'].'"><img src="https://www.wellnesstrade.cz/files/'.$loc['map_image'].'" style="width: 100%;"></a>
<div style="background-color: #FFF; padding: 15px 22px 16px; text-align: center; color: #555; font-size: 14px; font-style: italic;">
        '.$loc['address'].'</div>
      <div style="background-color: #333; padding: 23px 22px 22px; text-align: center; color: #FFF; font-weight: bold;">
        <a href="https://www.saunahouse.cz/" style="color: #FFF;">www.saunahouse.cz</a></div>
  
  </td>
  </tr>
</tr></tbody></table>
</td>
</tr>
</tbody></table>
</td>
</tr></tbody></table>
</div>';

$mail->addAddress($email);
//$mail->addBCC('becher.filip@gmail.com', 'FB');

$mail->isHTML(true); // Set email   format to HTML

$mail->Subject = 'Poptávka přijata';
$mail->Body = $body;
$mail->AltBody = strip_tags($body);

send_mail($mail, $id);
