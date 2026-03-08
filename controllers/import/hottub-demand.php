<?php
include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";

$name = $mysqli->real_escape_string($_POST['your-name']);
$email = $mysqli->real_escape_string($_POST['your-email']);
$phone = $mysqli->real_escape_string($_POST['your-phone']);
$location = $mysqli->real_escape_string($_POST['menu-10']);

$message = $mysqli->real_escape_string($_POST['your-message']);
$subject = $mysqli->real_escape_string($_POST['subject-demand']);

if(!empty($_POST['menu-295'])){
    $message .= ' '.$mysqli->real_escape_string($_POST['menu-295']);
}else{
    $message .= '';
}

// Mail
$target_shop = 'spahouse';

require_once MODEL . 'mailsModel.php';

// todo constants
if($location == 'Praha - Braník'){

    $loc['name'] = 'Praha';

    // petr
//    $admin_id = 2127;
//    $loc['phone'] = '776 55 66 22';
//    $loc['seller'] = 'Petr Svoboda';
//    $loc['image'] = 'mail-svoboda.jpg';

    // michal
//    $admin_id = 557;

    // ondrejka
    $admin_id = 11567;
    $loc['phone'] = '608 66 55 32';
    $loc['seller'] = 'Stanislav Ondrejka';
    $loc['image'] = 'mail-ondrejka.jpg';

    $loc['address'] = 'Vrbova 32, Praha 4, 147 00';
    $loc['map_url'] = 'https://goo.gl/maps/dEFEW5244Az';
    $loc['map_image'] = 'mail-praha.jpg';
    $loc['opening_hours'] = '<table style="width: 300px; margin: 20px auto 0; text-align: center; padding-top: 20px; border-top: 1px solid #ecebeb;">
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
    $area = 'prague';

}elseif($location == 'Brno'){

    $loc['name'] = 'Brno';
    $loc['phone'] = '773 00 55 88';
    $loc['seller'] = 'Vít Berger';
    $loc['image'] = 'mail-berger.jpg';
    $loc['address'] = 'Heršpická 1035/11f, Brno, 639 00';
    $loc['map_url'] = 'https://maps.app.goo.gl/Sg4KTU9rdnAwHM5v6';
    $loc['map_image'] = 'mail-brno.jpg';
    $loc['opening_hours'] = '<table style="width: 300px; margin: 20px auto 0; text-align: center; padding-top: 20px; border-top: 1px solid #ecebeb;">
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
    $area = 'brno';

    $admin_id = 2190;

}elseif($location == 'České Budějovice'){

    $loc['name'] = 'České Budějovice';
    $loc['phone'] = '776 55 66 22';
    $loc['seller'] = 'Petr Svoboda';
    $loc['image'] = 'mail-svoboda.jpg';
    $loc['address'] = 'Husova tř. 1828/39, České Budějovice, 370 05';
    $loc['map_url'] = 'https://goo.gl/maps/C3nsoY3kuoosduza6';
    $loc['map_image'] = 'map-budejovice.jpg';
    $loc['opening_hours'] = '<table style="width: 300px; margin: 20px auto 0; text-align: center; padding-top: 20px; border-top: 1px solid #ecebeb;">
<tbody>
<tr>
<td>Po–Čt</td>
<td>10:00–12:00, 13:00–18:00</td>
</tr>
<tr>
<td>Pá</td>
<td>10:00–12:00, 13:00–17:00</td>
</tr>
<tr>
<td>So–Ne</td>
<td>zavřeno&nbsp;/ po telefonické domluvě</td>
</tr>
</tbody>
</table>';

    $showroom = 6;
    $area = 'prague';

    $admin_id = 2127;

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


// todo napojit na databázi nebo ne? zase to bude další DB request navíc dopíči
function setProduct($subject){

    if (strpos($subject, 'Eden Compact') !== false) {
        $product = 'eden-compact';
    }elseif (strpos($subject, 'Cyprus') !== false) {
        $product = 'cyprus';
    }elseif (strpos($subject, 'Eden') !== false) {
        $product = 'eden';
    }elseif (strpos($subject, 'Capri') !== false) {
        $product = 'capri';
    }elseif (strpos($subject, 'Dreamline II') !== false) {
        $product = 'dreamline';
    }elseif (strpos($subject, 'Tahiti') !== false) {
        $product = 'tahiti';
    }elseif (strpos($subject, 'Tonga') !== false) {
        $product = 'tonga';
    }elseif (strpos($subject, 'Trinidad') !== false) {
        $product = 'trinidad';
    }elseif (strpos($subject, 'Corsica') !== false) {
        $product = 'corsica';
    }elseif (strpos($subject, 'ÜTO') !== false) {
        $product = 'uto';
    }elseif (strpos($subject, 'Milan') !== false) {
        $product = 'milan';
    }elseif (strpos($subject, 'Geneva') !== false) {
        $product = 'geneva';
    }elseif (strpos($subject, 'Cayman') !== false) {
        $product = 'cayman';
    }elseif (strpos($subject, 'Barbados') !== false) {
        $product = 'barbados-2';
    }elseif (strpos($subject, 'Jadran') !== false) {
        $product = 'jadran';
    }elseif (strpos($subject, 'Caribbean') !== false) {
        $product = 'caribbean';
    }elseif (strpos($subject, 'Atlantic') !== false) {
        $product = 'atlantic';
    }elseif (strpos($subject, 'Infinity Pool') !== false) {
        $product = 'infinity-pool';
    }elseif (strpos($subject, 'Sardinia') !== false) {
        $product = 'sardinia';
    }elseif (strpos($subject, 'Fiji') !== false) {
        $product = 'fiji';
    }elseif (strpos($subject, 'Gamma') !== false) {
        $product = 'gamma';
    }elseif (strpos($subject, 'Canis') !== false) {
        $product = 'canis';
    }elseif (strpos($subject, 'Vega') !== false) {
        $product = 'vega';
    }elseif (strpos($subject, 'Atlas') !== false) {
        $product = 'atlas';
    }elseif (strpos($subject, 'Polaris') !== false) {
        $product = 'polaris';
    }elseif (strpos($subject, 'Moon') !== false) {
        $product = 'moon';
    }elseif (strpos($subject, 'Genesis') !== false) {
        $product = 'genesis';
    }elseif (strpos($subject, 'Spectrum') !== false) {
        $product = 'spectrum';
    }elseif (strpos($subject, 'Spectrum Plus') !== false) {
        $product = 'spectrum-plus';
    }else{
        $product = 'hottub';
    }

    return $product;
}

function hottubType($subject){

    if (strpos($subject, 'Platinum') !== false) {
        $type = 'Platinum';
    }elseif (strpos($subject, 'Diamond') !== false) {
        $type = 'Diamond';
    }elseif (strpos($subject, 'Gold') !== false) {
        $type = 'Gold';
    }elseif (strpos($subject, 'Silver') !== false) {
        $type = 'Silver';
    }elseif (strpos($subject, '3 x 3') !== false) {
        $type = '3 x 3';
    }elseif (strpos($subject, '3 x 4') !== false) {
        $type = '3 x 4';
    }elseif (strpos($subject, '4 x 5') !== false) {
        $type = '4 x 5';
    }elseif (strpos($subject, '3 x 5') !== false) {
        $type = '3 x 5';
    }elseif (strpos($subject, 'na míru') !== false) {
        $type = 'na míru';
    }else{
        return;
    }

    return $type;
}



$product = setProduct($subject);
$type = hottubType($subject);

$technical_preparation = '';
if(file_exists($_SERVER['DOCUMENT_ROOT'] . "/admin/data/demands/documents/' . $subject . ' - Stavební příprava.pdf")){

    $path = 'https://www.wellnesstrade.cz/admin/data/demands/documents/' . $subject . ' - Stavební příprava.pdf';

    $technical_preparation = '<div style="margin: 40px 0 0; border-top: 1px solid #eee; padding-top: 40px; line-height: 42px;"><img src="https://www.wellnesstrade.cz/data/assets/building-preparation.png" height="40" style="float:left; margin-left: 10px; margin-right: 20px;">K poptávané vířivce '.$subject.' si můžete stáhnout <a href="'.$path.'" style="background-color: #00bd56; padding: 12px 20px; border-radius: 5px; margin-left: 14px; color: #FFF; text-decoration: none;">Stavební přípravu</a></div>';

}

    $secretstring = generateRandomString(14);

    $parts = explode(" ", $name);
    $lastname = array_pop($parts);
    $firstname = implode(" ", $parts);

    // todo phone prefix

    $insert_billing = $mysqli->query("INSERT INTO addresses_billing (billing_phone, billing_email, billing_name, billing_surname) VALUES ('" . $phone . "', '" . $email . "', '" . $firstname . "', '" . $lastname . "')") or die($mysqli->error);
    $billing_id = $mysqli->insert_id;

    $customer = 1;
    if($product == 'spectrum' || $product == 'spectrum-plus'){
        $customer = 4;
    }

    $mysqli->query("INSERT INTO demands (billing_id, user_name, admin_id, showroom, description, customer, date, product, email, phone, status, secretstring, area)
VALUES ('" . $billing_id . "', '" . $name . "', '" . $admin_id . "', '" . $showroom . "','" . $message . "', '".$customer."',  CURRENT_TIMESTAMP(), '".$product."', '" . $email . "', '" . $phone . "', '1','" . $secretstring . "','" . $area . "')") or die($mysqli->error);

    $id = $mysqli->insert_id;


    // save hottub type
    if(!empty($type)){

        $mysqli->query("INSERT INTO demands_specs_bridge (client_id, specs_id, value) VALUES ('".$id."', '5', '".$type."')")or die($mysqli->error);

    }


$mail = connectMail($target_shop);


if(isset($_REQUEST['language']) && $_REQUEST['language'] == 'DE'){

    $body = '<div id="wrapper" dir="ltr" style="background-color: #f5f5f5; margin: 0; padding: 40px 0 40px 0; -webkit-text-size-adjust: none !important; width: 100%;">
                <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tbody><tr>
    <td align="center" valign="top">
                            <div id="template_header_image">
                                <p style="margin-top: 0; margin-bottom: 30px;"><img src="https://www.spahouse.cz/wp-content/uploads/2018/04/spahouse-logo-4k.png" alt="Spahouse.cz" style="height: 64px;" height="64"></p>						</div>
                            <table border="0" cellpadding="0" cellspacing="0" width="780" id="template_container" style="box-shadow: 0 1px 4px rgba(0,0,0,0.1) !important; background-color: #fdfdfd; border: 1px solid #dcdcdc; border-radius: 3px !important;">
    <tbody>
    <tr>
    <td align="center" valign="top">
    <table border="0" cellpadding="0" cellspacing="0" width="780" id="template_body"><tbody><tr>
    <td valign="top" id="body_content" style="background-color: #fdfdfd;">
    <!-- Content -->
    <table border="0" cellpadding="20" cellspacing="0" width="100%"><tbody><tr>
    <td valign="top" style="padding: 48px;">															<div id="body_content_inner" style="color: #333; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif; font-size: 14px; line-height: 25px; text-align: left;">
    <p>Hallo,</p>
    <p>Wir freuen uns über Ihre Anfrage und Ihr Interesse an unseren Dienstleistungen. Wir bearbeiten Ihre Anfrage gerade und werden Sie so bald wie möglich kontaktieren.</p> 
    <p>Dankeschön <br><br>Spahouse Team</p>											
    </div>
    </td>
    </tr>
    </tbody></table>
    </td>
    </tr>
    </tbody>
    </table>
    </td>
    </tr>
    </tbody></table>
    </td>
    </tr></tbody></table>
    </div>';

    $mail->Subject = 'Anfrage angenommen';

}else{

    $body = '<div id="wrapper" dir="ltr" style="background-color: #f5f5f5; margin: 0; padding: 40px 0 40px 0; -webkit-text-size-adjust: none !important; width: 100%;">
                <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tbody><tr>
    <td align="center" valign="top">
                            <div id="template_header_image">
                                <p style="margin-top: 0; margin-bottom: 30px;"><img src="https://www.spahouse.cz/wp-content/uploads/2018/04/spahouse-logo-4k.png" alt="Spahouse.cz" style="height: 64px;" height="64"></p>						</div>
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
    <p>Děkujeme <br><br>Tým Spahouse</p>											
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
           <img src="https://www.wellnesstrade.cz/files/'.$loc['image'].'" style="width: 140px; border-radius: 50%;">
              <p style="margin: 8px 0 4px; font-size: 18px; font-weight: bold;">'.$loc['seller'].'</p>
    <p style="margin: 8px 0 4px; color: #666">Vedoucí pobočky '.$loc['name'].'</p>
    <p style="margin: 8px 0 4px; color: #666">Tel.: +420 '.$loc['phone'].'</p>
            </div>
           '.$loc['opening_hours'].'
           '.$technical_preparation.'
           </div>
          <div style="background-color: #eee; padding: 24px 22px 22px; text-align: center; color: #333; font-weight: bold; font-size: 14px;">
            Neváhejte se přijít podívat do našeho showroomu ('.$loc['name'].'):</div>
          <a href="'.$loc['map_url'].'"><img src="https://www.wellnesstrade.cz/files/'.$loc['map_image'].'" style="width: 100%;"></a>
    <div style="background-color: #FFF; padding: 15px 22px 16px; text-align: center; color: #555; font-size: 14px; font-style: italic;">
            '.$loc['address'].'</div>
          <div style="background-color: #333; padding: 23px 22px 22px; text-align: center; color: #FFF; font-weight: bold;">
            <a href="https://www.spahouse.cz/" style="color: #FFF;">www.spahouse.cz</a></div>
      
      </td>
      </tr>
    </tr>
    </tbody>
    </table>
    </td>
    </tr>
    </tbody></table>
    </td>
    </tr></tbody></table>
    </div>';

    $mail->Subject = 'Poptávka přijata';

}

$mail->addAddress($email);
//$mail->addBCC('becher.filip@gmail.com', 'FB');

$mail->isHTML(true); // Set email   format to HTML

$mail->Body = $body;
$mail->AltBody = strip_tags($body);

send_mail($mail, $id);
