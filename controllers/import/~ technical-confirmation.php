<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";
include $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/functions.php";


// todo napojit na databázi
function setProduct($subject){

    if (strpos($subject, 'Eden Compact') !== false) {
        $product = 'eden-compact';
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
    }else{
        return;
    }

    return $type;
}


$additional_info = '';

if(!empty($_REQUEST['demand_id'])){


    $demand_query = $mysqli->query("SELECT shipping_id, billing_id, id, email, product, DATE_FORMAT(realization, '%M') as realization_month,  DATE_FORMAT(realization, '%Y') as realization_year, realization FROM demands WHERE id = '".$_REQUEST['demand_id']."'")or die($mysqli->error);
    $demand = mysqli_fetch_assoc($demand_query);

    $demand_address_query = $mysqli->query("SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = '" . $demand['shipping_id'] . "' WHERE b.id = '" . $demand['billing_id'] . "'") or die($mysqli->error);
    $address = mysqli_fetch_assoc($demand_address_query);

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

if(isset($demand['realization']) && $demand['realization'] != '0000-00-00'){

    $realization_date = datumCesky($demand['realization_month']).' '.$demand['realization_year'];

}else{

    $realization_date = 'nestanoven';

}


if(!file_exists($_SERVER['DOCUMENT_ROOT'] . '/admin/data/demands/documents/' . $title . ' - Stavební příprava.pdf')){

    echo $title;
    die('error - zadna stavebni pripava');
}

$path = $_SERVER['DOCUMENT_ROOT'] . '/admin/data/demands/documents/' . $title . ' - Stavební příprava.pdf';

$maincolor = '#39a2e5';
$email_text = 'obchod@spahouse.cz';
$subject = 'Kontrola stavební připravenosti ' . $title;

$opening_text = '<p>Dobrý den,</p>

<p>zasíláme Vám informace ohledně stavební přípravy a předpokládaný měsíc instalace.</p>

<p>Pro bezproblémový chod instalace vířivky Vás žádáme o včasné zaslání informací:</p>

<ul><li><strong style="color: blue">Předpokládaný měsíc realizace:</strong> <strong style="color: green">'. $realization_date.'</strong></li>
<li><strong style="color: blue">Potvrzení instalační adresy:</strong> <strong style="color: green">'. return_address($address).'</strong></li></ul>

<p><strong style="color: blue">Před zahájením realizace Vás požádáme o zaslání následných informací:</strong></p>

<ul><li><strong>Fotografie průchodnosti</strong> cesty vířivky (terénní nerovnosti, průchody) od vjezdu až po její umístění (vzdálenost, místo připravené pro vířivku)
<br>(Fotografie, pokud možno zasílejte až po dokončení všech stavební prací spojený s vířivkou v klasickém formátu ve formě emailové přílohy, nebo přes aplikaci WhatsApp na číslo vašeho prodejce)</li></ul>

<ul><li><strong>Fotografie jističe</strong> (3 fázový jistič C16A), chrániče (30 mA) a kabelu (CYKY 5x2,5) z důvodu posouzení dostatečné délky (5m)pro zapojení vířivky </li></ul>

<ul><li><strong>Počet osob </strong>potřebných pro manipulaci s vířivkou: (Capri, Tahiti, Fiji) 3 <strong>osoby + náš techni</strong><strong>k, (Vířivky 2x2m a větší) 5 osob </strong><strong>+ náš technik</strong>
<br>(náš technik není robot, tak nám ho prosím šetřete a pokud máte možnost zkuste sehnat jednoho člověka navíc - děkujeme)</li></ul>

<ul><li>Pomůžeme Vám případně zařídit <strong>jeřáb</strong></li></ul>

<p><strong style="color: red">DŮLEŽITÉ 1: </strong>V případě neobdržení výše uvedených informací, si vyhrazujeme právo na změnu termínu.</p>

<p><strong style="color: red">DŮLEŽITÉ 2: </strong>Informace o přípravě prostoru pro vířivku najdete v přiložené stavební přípravě.</p>

<p>Děkujeme za Vaší reakci, po obdržení podkladů Vás budeme nejpozději do týdne kontaktovat ohledně dalších kroků. Nejdříve musíme vaše informace zpracovat a zjistit zdali je vše v pořádku.</p>

<p>Děkujeme za trpělivost a v případě jakýchkoliv dotazů či nejasností, nás kdykoliv kontaktujte.</p>

 <p style="margin: 0 0 16px;">Přejeme Vám hezký den</p>
 <p style="margin: 0 0 16px;">Tým Spahouse</p>';

$body = '<div id="wrapper" dir="ltr" style="background-color: #f5f5f5; margin: 0; padding: 70px 0 70px 0; -webkit-text-size-adjust: none !important; width: 100%;">
                    <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tbody><tr>
        <td align="center" valign="top">
                                <div id="template_header_image">
                                    <p style="margin-top: 0; margin-bottom: 3em;"><img src="https://www.spahouse.cz/wp-content/uploads/2018/04/spahouse-logo-4k.png" alt="Spahouse.cz" height="64" style="height: 64px; border: none; display: inline; font-size: 14px; font-weight: bold; line-height: 100%; outline: none; text-decoration: none; text-transform: capitalize;"></p>                        </div>
                                <table border="0" cellpadding="0" cellspacing="0" width="780" id="template_container" style="box-shadow: 0 1px 4px rgba(0,0,0,0.1) !important; background-color: #fdfdfd; border: 1px solid #dcdcdc; border-radius: 3px !important;">
        <tbody><tr>
        <td align="center" valign="top">
                                            <!-- Header -->
                                            <table border="0" cellpadding="0" cellspacing="0" width="780" id="template_header" style="background-color: #39a2e5; border-radius: 3px 3px 0 0 !important; color: #ffffff; border-bottom: 0; font-weight: bold; line-height: 100%; vertical-align: middle; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif;"><tbody><tr>
        <td id="header_wrapper" style="padding: 36px 48px; display: block;">
        <h1 style="color: #ffffff; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif; font-size: 24px; font-weight: 300; line-height: 150%; margin: 0; text-align: left; text-shadow: 0 1px 0 #aa3351; -webkit-font-smoothing: antialiased;">Kontrola stavební připravenosti ' . $title . '</h1>
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
$mail->Username = 'obchod@spahouse.cz'; // SMTP username
$mail->Password = 'pjRuQ6sQ'; // SMTP password
$mail->SMTPSecure = 'ssl'; // Enable TLS encryption, `ssl` also accepted
$mail->Port = 465; // TCP port to connect to

$mail->From = 'obchod@spahouse.cz';
$mail->FromName = 'Spahouse.cz';

$mail->DKIM_domain = 'spahouse.cz';
$mail->DKIM_private = 'https://www.spahouse.cz/wp-content/newkeys/spahouse-private.key';
$mail->DKIM_selector = 'phpmailer';
$mail->DKIM_passphrase = '';
$mail->DKIM_identity = 'obchod@spahouse.cz';

$mail->isHTML(true); // Set email   format to HTML

$mail->Subject = $subject;
$mail->Body = $body;

if (!$mail->send()) {
    echo 'Message could not be sent.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
}


if(!empty($_REQUEST['demand_id'])){

    header('location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $demand['id'] . '&success=send_preparation');

}else{

    $product = setProduct($title);
    $type = hottubType($title);

    $mysqli->query("INSERT INTO demands_preparations (product, type, email, phone, message, datetime) VALUES (
                                    '".$product."',
                                    '".$type."',
                                    '".$email."',
                                    '".$phone."',
                                    '".$message."',
                                    CURRENT_TIMESTAMP()
                                    )")or die($mysqli->error);

}