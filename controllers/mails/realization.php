<?php
        echo 'test';

$demand_query = $mysqli->query('SELECT * FROM demands WHERE id = "' . $getclient['id'] . '"') or die($mysqli->error);
$demand = mysqli_fetch_assoc($demand_query);

$address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $demand['shipping_id'] . '" WHERE b.id = "' . $demand['billing_id'] . '"') or die($mysqli->error);
$address = mysqli_fetch_assoc($address_query);

if(isset($address['billing_email']) && $address['billing_email'] != ''){

if (isset($demand['customer']) && $demand['customer'] == 1) {

    $titlus = "realizace vířivky";

} else {

    $titlus = 'realizace sauny';

}

if ($_POST['details'] != "") {

    $additional = '<strong>' . $_POST['details'] . '</strong>';

} else {

    $additional = "žádné";

}


if (isset($demand['confirmed']) && $demand['confirmed'] == '1') {
    $status = 'Potvrzená';
} elseif($demand['confirmed'] == '2') {
    $status = 'V řešení';
} else{
    $status = 'Plánovaná';
}



    $start = $demand['realization'].' '.$demand['realizationtime'];

    if(!empty($demand['realtodate']) && !empty($demand['realtotime']) && $demand['realtodate'] != '0000-00-00' && $demand['realtodate'] != $demand['realization']){
        
        $end = ' - '.$demand['realtodate'].' '.$demand['realtotime'];

    } else {

        $end = '';

    }

// MAILING PŘICHÁZÍ TE

$mail = new PHPMailer\PHPMailer\PHPMailer(true);

//$mail->SMTPDebug = 3;                               // Enable verbose debug output
$mail->CharSet = 'UTF-8';
$mail->isSMTP();

if (isset($client['customer']) && $client['customer'] == 0 || ($client['customer'] == 3 && $_SESSION['customah'] == 0)) {

    $product = 'sauny';

    $mail->Host = 'mail.webglobe.cz'; // Specify main and backup SMTP servers
    $mail->SMTPAuth = true; // Enable SMTP authentication
    $mail->Username = 'servis@saunahouse.cz'; // SMTP username
    $mail->Password = '6tRg3GvDRa7AzUK'; // SMTP password
    $mail->SMTPSecure = 'ssl'; // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 465; // TCP port to connect to

    $mail->From = 'servis@saunahouse.cz';
    $mail->FromName = 'Saunahouse.cz';

    $mail->DKIM_domain = 'saunahouse.cz';
    $mail->DKIM_private = 'https://www.saunahouse.cz/wp-content/keys/saunahouse-private.key';
    $mail->DKIM_selector = 'phpmailer';
    $mail->DKIM_passphrase = '';
    $mail->DKIM_identity = 'servis@saunahouse.cz';

    $maincolor = '#950026';
    $logo = '<img src="https://www.saunahouse.cz/wp-content/uploads/2016/06/saunahouse-logo.png" alt="Saunahouse.cz" style="border: none; display: inline; font-size: 14px; font-weight: bold; height: auto; line-height: 100%; outline: none; text-decoration: none; text-transform: capitalize;">';

    $email_text = 'servis@saunahouse.cz';
    $domain = 'spahouse.cz';

} elseif (isset($client['customer']) && $client['customer'] == 1 || ($client['customer'] == 3 && $_SESSION['customah'] == 1)) {

    $product = 'vířivky';

    $mail->Host = 'mail.webglobe.cz'; // Specify main and backup SMTP servers
    $mail->SMTPAuth = true; // Enable SMTP authentication
    $mail->Username = 'servis@spahouse.cz'; // SMTP username
    $mail->Password = '6tRg3GvDRa7AzUK'; // SMTP password
    $mail->SMTPSecure = 'ssl'; // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 465; // TCP port to connect to

    $mail->From = 'servis@spahouse.cz';
    $mail->FromName = 'Spahouse.cz';

    $mail->DKIM_domain = 'spahouse.cz';
    $mail->DKIM_private = 'https://www.spahouse.cz/wp-content/newkeys/spahouse-private.key';
    $mail->DKIM_selector = 'phpmailer';
    $mail->DKIM_passphrase = '';
    $mail->DKIM_identity = 'servis@spahouse.cz';

    $maincolor = '#009ee0';
    $logo = '<img src="https://www.spahouse.cz/wp-content/uploads/2015/12/LOGOSPAHOUSE.png" alt="Spahouse.cz" style="border: none; display: inline; font-size: 14px; font-weight: bold; height: auto; line-height: 100%; outline: none; text-decoration: none; text-transform: capitalize;">';

    $email_text = 'servis@spahouse.cz';
    $domain = 'spahouse.cz';

}

/*$mail->addAddress($address['billing_email']); // Add a recipient*/
$mail->addBCC('becher.filip@gmail.com'); // Add a recipient

$mail->isHTML(true); // Set email   format to HTML


$mail->Subject = 'Realizace - ' . $product;

$title = 'Realizace ' . $product;

$opening_text = '<p style="margin: 0 0 16px;">Dobrý den,</p><p style="margin: 0 0 16px;">naplánovali jsme pro Vás realizaci.</p>';

$content = '<table width="500" cellspacing="1" cellpadding="1" border="0" style="float: left; margin: 18px 0 27px; border: 1px solid #dcdcdc; padding: 23px 30px 22px; line-height: 25px; background: #fbfbfb;">
    <tbody>
    <tr><td>Stav realizace:</td> <td><b>' . $status . '</b> </td></tr>
    <tr><td>Datum:</td> <td><b>' . $start . $end . '</b></td></tr>
    <tr><td>Informace:</td> <td>' . $additional . '</td></tr>
    </tbody>
    </table>

    <p style="clear: both;"></p>';




//$firstTechnicianQuery = $mysqli->query("SELECT d.* FROM mails_recievers r, demands d WHERE r.type_id = '" . $id . "' AND r.admin_id = d.id AND (d.role = 'technician' OR d.role = 'senior_technician') AND d.active = 1 AND r.type = 'service' AND r.reciever_type = 'performer' LIMIT 1") or die($mysqli->error);
$firstTechnicianQuery = $mysqli->query("SELECT d.* FROM mails_recievers r, demands d WHERE r.type_id = '" . $id . "' AND r.admin_id = d.id AND d.active = 1 AND r.type = 'service' AND r.reciever_type = 'performer' LIMIT 1") or die($mysqli->error);

$firstTechnician = mysqli_fetch_assoc($firstTechnicianQuery);

if(!empty($firstTechnician)){

   $technician_data = ' <div style="text-align:center; margin: 20px 0 14px; width: 33%; margin-left: 16.33%; float: left;">
                      <p style="margin: 8px 0 4px; font-size: 16px; font-weight: bold; ">Servnisní technik</p>
            <p style="margin: 6px 0 2px; font-size: 16px; font-weight: bold; font-style: italic; ">'.$firstTechnician['user_name'].'</p>
  <p style="margin: 18px 0 4px; font-size: 15px; color: #666">+420 '.number_format($firstTechnician['phone'], 0, ',', ' ').'</p>
          </div>';

}


    // Servisní technik Tomáš Kurfürst +420 776 556 994 Servisní technik Pavel Müller +420 773 005 544 Servisní technik Martin Hanuš +420 771 156 622 Servisní technik Michal Dobeš +420 777 624 187 Servisní technik Juraj Rybnikár +420 776 241 309 Servisní technik Jakub Čepil +420 774 777 052

if (isset($alternate_text) && $alternate_text != '') {

    $opening_text = '<p style="margin: 0 0 16px;">' . $alternate_text . '</p>';

}

$body = '<div id="wrapper" dir="ltr" style="background-color: #f5f5f5; margin: 0; padding: 60px 0 70px 0; -webkit-text-size-adjust: none !important; width: 100%;">
               <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tbody><tr>
<td align="center" valign="top">
                              <div id="template_header_image">
                                   <p style="margin-top: 0; margin-bottom: 3em;">' . $logo . '</p>                          </div>
                              <table border="0" cellpadding="0" cellspacing="0" width="780" id="template_container" style="box-shadow: 0 1px 4px rgba(0,0,0,0.1) !important; background-color: #fdfdfd; border: 1px solid #dcdcdc; border-radius: 3px !important;">
<tbody><tr>
<td align="center" valign="top">
                                             <!-- Header -->
                                             <table border="0" cellpadding="0" cellspacing="0" width="780" id="template_header" style="background-color: ' . $maincolor . '; border-radius: 3px 3px 0 0 !important; color: #ffffff; border-bottom: 0; font-weight: bold; line-height: 100%; vertical-align: middle; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif;"><tbody><tr>
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
' . $opening_text . '
' . $content . '



      <div style="padding: 32px 40px 40px; text-align: center;">
        <h4 style="font-size: 18px; font-weight: bold; text-transform: uppercase; color: #009feb; border-bottom: 1px solid #ecebeb; padding-bottom: 30px; margin-bottom: 20px;">Důležité kontakty:</h4>    

<div style="display: inline-block; width: 100%;">

   '.$technician_data.'
          
                    <div style="text-align:center; margin: 20px 0 14px; width: 33%; float: left;">
                      <p style="margin: 8px 0 4px; font-size: 16px; font-weight: bold; ">Změna termínu</p>
            <p style="margin: 6px 0 2px; font-size: 16px; font-weight: bold; font-style: italic; ">Jan Pazderský</p>
  <p style="margin: 18px 0 4px; font-size: 15px; color: #666">+420 776 55 37 22</p>
  <p style="margin: 4px 0 4px; font-size: 15px; color: #009feb; font-weight: 600;">pazdersky@'.$domain.'</p>
          </div>
                    
        

        </div>
        
        
        
        <div style="padding: 15px 22px 16px; text-align: center; color: #555; font-size: 16px; line-height: 28px; font-weight: bold; margin: 40px 0 30px; font-style: italic;">
       Děkujeme,<br>
         že jste si vybrali právě nás a naše '.$product.'<br>
  <span style="font-weight: normal;">Váš Tým '.ucfirst($domain).'</span>
       </div>
      <div style="border-top: 1px solid #ecebeb; border-bottom: 1px solid #ecebeb; padding: 14px 22px 14px; text-align: center; color: #FFF;">
        <a href="https://www.'.$domain.'/" style="color: #009feb; font-size: 14px; text-decoration: none; font-weight: bold;">www.'.$domain.'</a></div>

                                                                           </div>
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
                                   <a href="tel:+420774141596" value="+420774141596" target="_blank" style="color:#333333;font-size:22px;text-decoration:none">+420 774 141 596</a></strong><br>
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

//$mail->AddBCC($email_text);


$mail->isHTML(true); // Set email   format to HTML

$mail->Body = $body;
$mail->AltBody = strip_tags($body);

if (!$mail->send()) {
    echo 'Message could not be sent.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
}

}