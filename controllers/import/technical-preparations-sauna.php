<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$showLog = true;
include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";


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

function hottubType($subject){

    if (strpos($subject, 'Platinum') !== false) {
        $type = 'Platinum';
    }elseif (strpos($subject, 'Diamond') !== false) {
        $type = 'Diamond';
    }elseif (strpos($subject, 'Gold') !== false) {
        $type = 'Gold';
    }elseif (strpos($subject, 'Silver') !== false) {
        $type = 'Silver';
    }else{
        return;
    }

    return $type;
}


$additional_info = '';

if(!empty($_REQUEST['demand_id'])){

    $demand_query = $mysqli->query("SELECT id, email, product FROM demands WHERE id = '".$_REQUEST['demand_id']."'")or die($mysqli->error);
    $demand = mysqli_fetch_assoc($demand_query);

    $product_query = $mysqli->query("SELECT brand, fullname FROM warehouse_products WHERE connect_name = '".$demand['product']."'")or die($mysqli->error);
    $product = mysqli_fetch_assoc($product_query);

    $provedeni_query = $mysqli->query("SELECT value FROM demands_specs_bridge WHERE specs_id = 5 AND client_id = '".$demand['id']."'")or die($mysqli->error);
    $provedeni = mysqli_fetch_assoc($provedeni_query);

    $email = $demand['email'];

    if ($provedeni['value'] === 'Special version') { $variation = 'Gold'; }else{ $variation = $provedeni['value']; }

    if(!empty($provedeni['value'])){

        $title = $product['brand'].' '.$product['fullname'].' '.$variation;

    }else{

        $title = $product['brand'].' '.$product['fullname'];

    }

}elseif(!empty($_POST)){

    $title = $mysqli->real_escape_string($_POST['title']);
    $email = $mysqli->real_escape_string($_POST['email']);
    $phone = $mysqli->real_escape_string($_POST['phone']);
    $message = $mysqli->real_escape_string($_POST['message']);
    
    $product = setProduct($title);

    if (strpos($title, 'IQue') !== false) {

        $additional_info = '<p style="margin: 0 0 16px;">Mezi nejdůležitější aspekty stavební přípravy pro vířivku patří:</p>
<ul>
<li>Rovná, zpevněná plocha bez spádu</li>
<li>Průchodnost na místo uložení vířivky (lze nést na „stojáka“)</li>
<li>Přívod CYKY 5 x 2,5mm, jistič C16A + chránič</li>
</ul>';

    }else{

        $additional_info = '';

    }

}

if(file_exists($_SERVER['DOCUMENT_ROOT'] . '/admin/data/demands/documents/Saunahouse - stavebni priprava 2025.pdf')){

    /*die('error - zadna stavebni pripava');*/

    $path = $_SERVER['DOCUMENT_ROOT'] . '/admin/data/demands/documents/Saunahouse - stavebni priprava 2025.pdf';

    $maincolor = '#950026';
    $email_text = 'obchod@saunahouse.cz';
    $subject = 'Stavební příprava pro typové sauny 2025';

    $opening_text = '<p style="margin: 0 0 16px;">Dobrý den,</p>
    
    <p style="margin: 0 0 16px;">v příloze zasíláme požadovanou kompletní stavební přípravu pro typové sauny 2025.</p>
    
    '.$additional_info.'
    
     <p style="margin: 0 0 16px;">V případě dalších technických dotazů, prosím kontaktujte kolegu, technika, pana Fajta, +420 777 624 475.</p>
     <p style="margin: 0 0 16px;">Pokud budete mít zájem o zpracování nezávazné cenové nabídky na konkrétní model, neváhejte nás taktéž kontaktovat.</p>
     <p style="margin: 0 0 16px;">Přejeme Vám hezký den</p>
     <p style="margin: 0 0 16px;">Tým Saunahosue</p>';

    $body = '<div id="wrapper" dir="ltr" style="background-color: #f5f5f5; margin: 0; padding: 70px 0 70px 0; -webkit-text-size-adjust: none !important; width: 100%;">
                        <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tbody><tr>
            <td align="center" valign="top">
                                    <div id="template_header_image">
                                        <p style="margin-top: 0; margin-bottom: 3em;"><img src="https://www.saunahouse.cz/wp-content/uploads/2016/06/saunahouse-logo.png" alt="Saunahouse.cz" height="64" style="height: 64px; border: none; display: inline; font-size: 14px; font-weight: bold; line-height: 100%; outline: none; text-decoration: none; text-transform: capitalize;"></p>                        </div>
                                    <table border="0" cellpadding="0" cellspacing="0" width="780" id="template_container" style="box-shadow: 0 1px 4px rgba(0,0,0,0.1) !important; background-color: #fdfdfd; border: 1px solid #dcdcdc; border-radius: 3px !important;">
            <tbody><tr>
            <td align="center" valign="top">
                                                <!-- Header -->
                                                <table border="0" cellpadding="0" cellspacing="0" width="780" id="template_header" style="background-color: #950026; border-radius: 3px 3px 0 0 !important; color: #ffffff; border-bottom: 0; font-weight: bold; line-height: 100%; vertical-align: middle; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif;"><tbody><tr>
            <td id="header_wrapper" style="padding: 36px 48px; display: block;">
            <h1 style="color: #ffffff; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif; font-size: 30px; font-weight: 300; line-height: 150%; margin: 0; text-align: left; text-shadow: 0 1px 0 #aa3351; -webkit-font-smoothing: antialiased;">Stavební příprava ' . $title . '</h1>
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
            ' . $opening_text . '
    
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
                                <a href="tel:%2B420774141596" value="+420774141596" target="_blank" style="color:#333333;font-size:22px;text-decoration:none">+420 774 141 596</a></strong><br>
                                Pracovní dny od 10:00 do 18:00 hodin
                            </td>
                            <td width="60"></td>
                            <td valign="top" width="58" style="padding-top: 1px;">
                                <img src="https://www.wellnesstrade.cz/admin/assets/images/1472614684_mail.png" alt="mail" class="CToWUd">
                            </td>
                            <td>
                                <strong><a style="color:#333333;font-size:22px;text-decoration:none" href="mailto:' . $email_text . '" target="_blank">' . $email_text . '</a></strong><br>
                                Na email se snažíme odpovídat okamžitě
                            </td>
                        </tr>
                    </tbody></table>
            </td>
                            </tr></tbody></table>
            </div>';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    //$mail->SMTPDebug = 3;                               // Enable verbose debug output
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();

    $mail->addAttachment($path);

    $mail->addAddress($email);

    $mail->Host = 'mail.webglobe.cz'; // Specify main and backup SMTP servers
    $mail->SMTPAuth = true; // Enable SMTP authentication
    $mail->SMTPSecure = 'ssl'; // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 465; // TCP port to connect to

    $mail->Username = 'info@saunahouse.cz';
    $mail->Password = 'Q9ccpQEq';

    $mail->From = 'info@saunahouse.cz';
    $mail->FromName = 'Saunahouse.cz';

    $mail->DKIM_domain = 'saunahouse.cz';
    $mail->DKIM_private = 'https://www.saunahouse.cz/wp-content/keys/saunahouse-private.key';
    $mail->DKIM_selector = 'phpmailer';
    $mail->DKIM_passphrase = '1484787613';
    $mail->DKIM_identity = 'info@saunahouse.cz';

    $mail->isHTML(true); // Set email   format to HTML

    $mail->Subject = $subject;
    $mail->Body = $body;

    if (!$mail->send()) {
        echo 'Message could not be sent.';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    }

}



if(!empty($_REQUEST['demand_id'])){

    header('location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $demand['id'] . '&success=send_preparation');

}else{

    $product = setProduct($title);
    $type = hottubType($title);

    $mysqli->query("INSERT INTO demands_preparations (customer, product, type, email, phone, message, datetime) VALUES (
                                    '0',
                                    '".$product."',
                                    '".$type."',
                                    '".$email."',
                                    '".$phone."',
                                    '".$message.$title."',
                                    CURRENT_TIMESTAMP()
                                    )")or die($mysqli->error);

}