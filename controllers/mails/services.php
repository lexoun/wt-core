<?php

$service_query = $mysqli->query('SELECT * FROM services WHERE id="' . $id . '"') or die($mysqli->error);
$service = mysqli_fetch_assoc($service_query);

$address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $service['shipping_id'] . '" WHERE b.id = "' . $service['billing_id'] . '"') or die($mysqli->error);
$address = mysqli_fetch_assoc($address_query);

if(isset($address['billing_email']) && $address['billing_email'] != ''){

if (isset($service['customertype']) && $service['customertype'] == 1) {

    $titlus = "servis vířivky";


} else {

    $titlus = 'servis sauny';

}

if ($service['details'] != "") {

    $additional = '<strong>' . $service['details'] . '</strong>';

} else {

    $additional = "žádné";

}

$date = date("d. m. Y", strtotime($service['date']));
$time = date("H:i", strtotime($service['estimatedtime']));

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

}

$mail->addAddress($address['billing_email']); // Add a recipient
$mail->addBCC('sefl@spahouse.cz'); // Add a recipient
$mail->addBCC('kontrola@spahouse.cz'); // Add a recipient

$mail->isHTML(true); // Set email   format to HTML



/*

if ($service['state'] == 'new') {

    $mail->Subject = 'Naplánovaný servis - ' . $product;

    $title = 'Naplánovaný servis ' . $product;

    $opening_text = '<p style="margin: 0 0 16px;">Dobrý den,</p><p style="margin: 0 0 16px;">naplánovali jsme pro Vás nový servis vířivky.</p>

     <p style="margin: 0 0 16px; color: #1974c1; font-weight: bold;">Ohledně jeho potvrzení Vás budeme kontaktovat.</p>';

    $content = '<table width="500" cellspacing="1" cellpadding="1" border="0" style="float: left; margin: 18px 0 27px; border: 1px solid #dcdcdc; padding: 23px 30px 22px; line-height: 25px; background: #fbfbfb;">
     <tbody>
     <tr><td>Druh servisu:</td> <td><b>' . $titlus . '</b> </td></tr>
     <tr><td>Datum:</td> <td><b>' . $date . '</b> - <b>' . $time . '</b></td></tr>
     <tr><td>Informace:</td> <td>' . $additional . '</td></tr>
     </tbody>
     </table>

     <p style="clear: both;"></p>';

} elseif ($service['state'] == 'confirmed') {

    $mail->Subject = 'Potvrzení servisu - ' . $product;

    $title = 'Potvrzení servisu ' . $product;

    $opening_text = '<p style="margin: 0 0 16px;">Dobrý den,</p><p style="margin: 0 0 16px; color: #19c119; font-weight: bold;">termín Vašeho servisu byl potvrzen.</p>

     <p style="margin: 0 0 16px;">V případě dotazů či změny termínu nás neváhejte kontaktovat.</p>';

    $content = '<table width="500" cellspacing="1" cellpadding="1" border="0" style="float: left; margin: 18px 0 27px; border: 1px solid #dcdcdc; padding: 23px 30px 22px; line-height: 25px; background: #fbfbfb;">
     <tbody>
     <tr><td>Druh servisu:</td> <td><b>' . $titlus . '</b> </td></tr>
     <tr><td>Datum:</td> <td><b>' . $date . '</b> - <b>' . $time . '</b></td></tr>
     <tr><td>Informace:</td> <td>' . $additional . '</td></tr>
     </tbody>
     </table>

     <p style="clear: both;"></p>';

} elseif ($service['state'] == 'canceled') {

    $mail->Subject = 'Zrušení servisu - ' . $product;

    $title = 'Zrušení servisu ' . $product;

    $opening_text = '<p style="margin: 0 0 16px;">Dobrý den,</p><p style="margin: 0 0 16px; color: #cc2424; font-weight: bold;">Váš servis byl zrušen.</p>

     <p style="margin: 0 0 16px;">V případě dotazů nebo žádosti o nový termín servisu nás neváhejte kontaktovat.</p>';

    $content = '<table width="500" cellspacing="1" cellpadding="1" border="0" style="float: left; margin: 18px 0 27px; border: 1px solid #dcdcdc; padding: 23px 30px 22px; line-height: 25px; background: #fbfbfb;">
     <tbody>
     <tr><td>Druh servisu:</td> <td><b>' . $titlus . '</b> </td></tr>
     <tr><td>Datum:</td> <td><b>' . $date . '</b> - <b>' . $time . '</b></td></tr>
     <tr><td>Informace:</td> <td>' . $additional . '</td></tr>
     </tbody>
     </table>

     <p style="clear: both;"></p>';

} else {

    $mail->Subject = 'Změna servisu - ' . $product;

    $title = 'Změna servisu ' . $product;

    $opening_text = '<p style="margin: 0 0 16px;">Dobrý den,</p><p style="margin: 0 0 16px;">údaje Vašeho servisu byli změněny.</p>

     <p style="margin: 0 0 16px; color: #d99b14; font-weight: bold;">Zkontrolujte níže uvedené údaje a v případě dotazů či změny termínu nás neváhejte kontaktovat.</p>';

    $content = '<table width="500" cellspacing="1" cellpadding="1" border="0" style="float: left; margin: 18px 0 27px; border: 1px solid #dcdcdc; padding: 23px 30px 22px; line-height: 25px; background: #fbfbfb;">
     <tbody>
     <tr><td>Druh servisu:</td> <td><b>' . $titlus . '</b> </td></tr>
     <tr><td>Datum:</td> <td><b>' . $date . '</b> - <b>' . $time . '</b></td></tr>
     <tr><td>Informace:</td> <td>' . $additional . '</td></tr>
     </tbody>
     </table>

     <p style="clear: both;"></p>';

}

*/



//$firstTechnicianQuery = $mysqli->query("SELECT d.* FROM mails_recievers r, demands d WHERE r.type_id = '" . $id . "' AND r.admin_id = d.id AND (d.role = 'technician' OR d.role = 'senior_technician') AND d.active = 1 AND r.type = 'service' AND r.reciever_type = 'performer' LIMIT 1") or die($mysqli->error);
$firstTechnicianQuery = $mysqli->query("SELECT d.* FROM mails_recievers r, demands d WHERE r.type_id = '" . $id . "' AND r.admin_id = d.id AND d.active = 1 AND r.type = 'service' AND r.reciever_type = 'performer' LIMIT 1") or die($mysqli->error);

$firstTechnician = mysqli_fetch_assoc($firstTechnicianQuery);

if(!empty($firstTechnician)){

   $technician_data = ' <div style="text-align:center; margin: 20px 0 14px; width: 33%; margin-left: 16.33%; float: left;">
                      <p style="margin: 8px 0 4px; font-size: 16px; font-weight: bold; ">Servnisní technik</p>
            <p style="margin: 6px 0 2px; font-size: 16px; font-weight: bold; font-style: italic; ">'.$firstTechnician['user_name'].'</p>
  <p style="margin: 18px 0 4px; font-size: 15px; color: #666">+420 '.number_format($firstTechnician['phone'], 0, ',', ' ').'</p>
          </div>';

    $technician_data_short = '+420 ' . number_format($firstTechnician['phone'], 0, ',', ' ').' ('.$firstTechnician['user_name'].')';

}


    // Servisní technik Tomáš Kurfürst +420 776 556 994 Servisní technik Pavel Müller +420 773 005 544 Servisní technik Martin Hanuš +420 771 156 622 Servisní technik Michal Dobeš +420 777 624 187 Servisní technik Juraj Rybnikár +420 776 241 309 Servisní technik Jakub Čepil +420 774 777 052

if (isset($alternate_text) && $alternate_text != '') {

    $opening_text = '<p style="margin: 0 0 16px;">' . $alternate_text . '</p>';

}


if($_POST['email_type'] == 'received'){ 

    $mail->Subject = 'Spahouse.cz – Přijetí servisního požadavku';

    $title = 'Přijetí servisního požadavku';

    $opening_text = '';

$content = '<p style="margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;line-height:115%;font-size:16px;font-family:"Aptos",sans-serif;>Dobrý den,<br><br>
potvrzujeme přijetí servisního požadavku. Naše servisní oddělení jej nyní zaevidovalo a bude Vás informovat o dalším postupu a předpokládaném termínu vyřízení, jakmile to bude možné.</p>
Děkujeme za trpělivost a spolupráci.<br><br>
<p style="margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;line-height:115%;font-size:16px;font-family:"Aptos",sans-serif;"><strong>S přáním hezkého dne,</strong></p>

<table style="border: none;width:100%;">
    <tbody>
        <tr>
            <td colspan="2" style="width:20.12%;padding: 0 .75pt .75pt .75pt;height:102.05pt;">
                <p style="margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;line-height:115%;font-size:16px;font-family:"Aptos",sans-serif;"><strong><img width="140" height="140" src="https://www.spahouse.cz/wp-content/uploads/2024/04/pazdersky.jpg" alt="image"></strong></p>
            </td>
            <td colspan="2" style="width:71.96%;padding:.75pt .75pt .75pt .75pt;height:102.05pt;">
                <p style="margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;line-height:115%;font-size:16px;font-family:"Aptos",sans-serif;"><strong>JAN PAZDERSKÝ</strong></p>
                <p style="margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;line-height:115%;font-size:16px;font-family:"Aptos",sans-serif;"><strong>Service manager<br>&nbsp;&nbsp;<br> <em>Tel.:&nbsp;+ 420 776 55 37 22</em><br> <em>Email.:&nbsp;</em></strong><a href="mailto:halda@spahouse.cz"><strong><em>pazdersky@spahouse.cz</em></strong></a><strong>&nbsp;&nbsp;</strong></p>
            </td>
            <td style="width:.66%;padding:0cm 0cm 0cm 0cm;height:102.05pt;">
                <p style="margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;line-height:115%;font-size:16px;font-family:"Aptos",sans-serif;"><strong>&nbsp;</strong></p>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="width:26.12%;padding:.75pt .75pt .75pt .75pt;text-align: center;">
               <strong>&nbsp; &nbsp;</strong><strong><a href="https://www.facebook.com/spahouse.saunahouse/"><img width="29" height="29" src="https://www.wellnesstrade.cz/files/servis-fb.png" alt="signature_1238761170"></a>&nbsp;&nbsp;<a href="https://www.instagram.com/spahouse_cz/"><img width="29" height="29" src="https://www.wellnesstrade.cz/files/servis-insta.png" alt="signature_1238761170"></a></strong>
            </td>
            <td colspan="2" style="width:71.96%;padding:.75pt .75pt .75pt .75pt;">
               <strong>SKLAD PRAHA<br>&nbsp;Dobronick&aacute;, Praha 4 - Krč</strong>
            </td>
            <td style="width:.66%;border:none;border-bottom:solid #2196F3 1.0pt;padding:0cm 0cm 0cm 0cm;">
                <p style="margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;line-height:115%;font-size:16px;font-family:"Aptos",sans-serif;"><strong>&nbsp;</strong></p>
            </td>
        </tr>
        <tr>
            <td style="width:.66%;padding:0cm 0cm 0cm 0cm;height:50.9pt;">
                <p style="margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;line-height:115%;font-size:16px;font-family:"Aptos",sans-serif;"><strong>&nbsp;</strong></p>
            </td>
            <td colspan="2" style="width:25.5%;border:none;border-top:solid #2196F3 1.0pt;padding:.75pt .75pt .75pt .75pt;height:50.9pt;">
                <p style="margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;line-height:115%;font-size:16px;font-family:"Aptos",sans-serif;"><strong><img width="100" height="29" src="https://www.spahouse.cz/wp-content/uploads/2015/12/LOGOSPAHOUSE.png" alt="signature_1300322749"></strong></p>
            </td>
            <td colspan="2" style="width:72.58%;border:none;border-top:solid #2196F3 1.0pt;padding:.75pt .75pt .75pt .75pt;height:50.9pt;">
                <p style="margin-top:0cm;margin-right:0cm;margin-bottom:8.0pt;margin-left:0cm;line-height:115%;font-size:16px;font-family:"Aptos",sans-serif;"><strong><em>&nbsp;&nbsp;</em></strong><a href="http://spahouse.cz/"><strong><em>spahouse.cz</em></strong></a><strong><em>&nbsp;&bull;&nbsp;</em></strong><a href="http://saunahouse.cz/"><strong><em>saunahouse.cz</em></strong></a><strong><em>&nbsp;&bull;&nbsp;</em></strong><a href="http://virivka.cz/"><strong><em>virivka.cz</em></strong></a><strong><em>&nbsp;&bull;&nbsp;</em></strong><a href="http://iquespa.cz/"><strong><em>iquespa.cz</em></strong></a></p>
            </td>
        </tr>
        <tr>
            <td style="border:none;"><br></td>
            <td style="border:none;"><br></td>
            <td style="border:none;"><br></td>
            <td style="border:none;"><br></td>
            <td style="border:none;"><br></td>
        </tr>
    </tbody>
</table>';

}elseif($_POST['email_type'] == 'planned'){ 


    $information = ''; 
    if(!empty($additional)){
        $information = '<tr><td>Informace:</td> <td>' . $additional . '</td></tr>';
    }

    $technician_contact = '';
    if(!empty($technician_data_short)){
        $technician_contact = '<tr><td>Kontakt na technika:</td> <td><b>' . $technician_data_short . '</b> </td></tr>';
    }

    $mail->Subject = 'Spahouse.cz - Naplánovaný servisní požadavek';

    $title = 'Naplánovaný servisní požadavek';
    

    $opening_text = '<p style="margin: 0 0 16px;">Dobrý den,</p><p style="margin: 0 0 16px; font-weight: bold;">zasíláme Vám potvrzení servisního termínu. Prosíme, e-mail si přečte až do konce.</p>';

    $content = '<table width="100%" cellspacing="1" cellpadding="1" border="0" style="float: left; margin: 18px 0 27px; border: 1px solid #dcdcdc; padding: 23px 30px 22px; line-height: 25px; background: #fbfbfb;">
     <tbody>
     <tr><td>Termín servisu:</td> <td><b>' . $date . '</b></td></tr>
     <tr><td>Orientační čas příjezdu:</td> <td><b>' . $time . '</b></td></tr>
     <tr><td>Trvání servisu:</td> <td><b>90–120 minut</b> </td></tr>
     ' . $information . '
     ' . $technician_contact . '
     </tbody>
     </table>
<br><br>
     <p><strong>Pokyny pro zákazníka</strong></p>
<ul>
<li><strong>Vířivka musí být zpřístupněna</strong> &ndash; (zpřístupnění všech stran vířivky).</li>
<li><strong>Zapuštěná vířivka</strong> &ndash; terasová prkna musí být předem rozebrána</li>
<li>V případě, že vířivka není zpřístupněna ze všech stran, je nutná asistence na manipulaci s vířivkou</li>
<li>Zákazník musí být přítomen na protokolární předání servisu</li>
</ul>
<p><strong>Důležité informace</strong></p>
<ul>
<li>V případě, neuznání reklamace je servis hrazen dle servisního ceníku: 1200 Kč vč. dph / započatá hodina a 18 kč/km.</li>
<li>V případě, že vířivka není zpřístupněna a servis si vyžaduje vícepráce (rozebrání terasy atd.) je tento úkon účtován hodinovou sazbou 1200 Kč vč. DPH.</li>
<li>V případě, že není možné vykonat servis z důvodu překážky na straně zákazníka, bude účtován planý výjezd</li>
</ul>

     <p style="clear: both;"></p>
     <div style="padding: 12px 40px 40px; text-align: center;">
        
        <div style="padding: 15px 22px 16px; text-align: center; color: #555; font-size: 16px; line-height: 28px; font-weight: bold; margin: 40px 0 30px; font-style: italic;">
       Děkujeme,<br>
         že jste si vybrali právě nás a naše vířivky<br>
  <span style="font-weight: normal;">Váš Tým Spahouse</span>
       </div>
      <div style="border-top: 1px solid #ecebeb; border-bottom: 1px solid #ecebeb; padding: 14px 22px 14px; text-align: center; color: #FFF;">
        <a href="https://www.spahouse.cz/" style="color: #009feb; font-size: 14px; text-decoration: none; font-weight: bold;">www.spahouse.cz</a></div>

                                                                           </div>';


}elseif($_POST['email_type'] == 'guarantee_check'){

    $information = ''; 
    if(!empty($additional)){
        $information = '<tr><td>Informace:</td> <td>' . $additional . '</td></tr>';
    }

    $technician_contact = '';
    if(!empty($technician_data_short)){
        $technician_contact = '<tr><td>Kontakt na technika:</td> <td><b>' . $technician_data_short . '</b> </td></tr>';
    }

    $mail->Subject = 'Spahouse.cz - Garanční prohlídka';

    $title = 'Garanční prohlídka';
    

    $opening_text = '<p style="margin: 0 0 16px;">Dobrý den,</p><p style="margin: 0 0 16px; font-weight: bold;">zasíláme Vám potvrzení servisního termínu garanční prohlídky. Prosíme, email si přečte až do konce</p>';

    $content = '<table width="100%" cellspacing="1" cellpadding="1" border="0" style="float: left; margin: 18px 0 27px; border: 1px solid #dcdcdc; padding: 23px 30px 22px; line-height: 25px; background: #fbfbfb;">
     <tbody>
     <tr><td>Termín servisu:</td> <td><b>' . $date . '</b></td></tr>
     <tr><td>Orientační čas příjezdu:</td> <td><b>' . $time . '</b></td></tr>
     <tr><td>Typ servisu:</td> <td><b>Garanční prohlídka</b></td></tr>
     <tr><td>Trvání servisu:</td> <td><b>90–120 minut</b> </td></tr>
     <tr><td>Cena servisu:</td> <td><b>4 990 Kč vč. DPH</b> </td></tr>
     ' . $information . '
     ' . $technician_contact . '
     </tbody>
     </table>
<br><br>
     <p><strong>Garanční prohlídka obsahuje</strong></p>
<ul>
<li>Diagnostika řídícího systému</li>
<li>Kontrolu cirkulačního systému</li>
<li>Kontrola funkčnosti ohřevu a topné spirály</li>
<li>Kontrola těsnosti masážních čerpadel</li>
<li>Kontrola vzduchového čerpadla</li>
<li>Stav potrubí (vodní kámen, řasy a jiné znečištění)</li>
<li>Kontrola filtračního systému</li>
<li>Kontrola LED osvětlení</li>
<li>Kontrola ovládacích prvků</li>
<li>Kontrola funkčnosti trysek</li>
<li>Kontrola MicroSilku (je-li součástí výbavy)</li>
</ul>

<p><strong>Garanční prohlídka obsahuje</strong></p>
<ul>
<li>Výměnu vody</li>
<li>Čištění vířivky</li>
</ul>

<p><strong>Pokyny pro zákazníka</strong></p>
<ul>
<li>Vířivka musí být zpřístupněna (zpřístupnění všech stran vířivky).  </li>
<li>Zapuštěná vířivka – terasová prkna musí být předem rozebrána</li>
<li>V případě, že vířivka není zpřístupněna ze všech stran, je nutná asistence na manipulaci s vířivkou</li>
<li>Zákazník musí být přítomen na protokolární předání servisu</li>
</ul>

<p><strong>Důležité informace</strong></p>
<ul>
<li>V případě, neuznání reklamace je servis hrazen dle servisního ceníku.</li>
<li>V případě, že vířivka není zpřístupněna a servis si vyžaduje vícepráce (rozebrání terasy atd.) je tento úkon účtován hodinovou sazbou 1200 Kč vč. DPH.</li>
<li>V případě, že není možné vykonat servis z důvodu překážky na straně zákazníka, bude účtován planý výjezd</li>
</ul>

     <p style="clear: both;"></p>
     <div style="padding: 12px 40px 40px; text-align: center;">
        
        <div style="padding: 15px 22px 16px; text-align: center; color: #555; font-size: 16px; line-height: 28px; font-weight: bold; margin: 40px 0 30px; font-style: italic;">
       Děkujeme,<br>
         že jste si vybrali právě nás a naše vířivky<br>
  <span style="font-weight: normal;">Váš Tým Spahouse</span>
       </div>
      <div style="border-top: 1px solid #ecebeb; border-bottom: 1px solid #ecebeb; padding: 14px 22px 14px; text-align: center; color: #FFF;">
        <a href="https://www.spahouse.cz/" style="color: #009feb; font-size: 14px; text-decoration: none; font-weight: bold;">www.spahouse.cz</a></div>

                                                                           </div>';

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

      
                                                                           </div>
                                                                      </td>
                                                                 </tr></tbody></table>
<!-- End Content -->
</td>
                                                  </tr></tbody></table>
<!-- End Body -->
</td>
                                   </tr>
</tbody></table>

</td>
                    </tr></tbody></table>
</div>';



/*
OLDBODY

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
  <p style="margin: 4px 0 4px; font-size: 15px; color: #009feb; font-weight: 600;">pazdersky@spahouse.cz</p>
          </div>
                    
        

        </div>
        
        
        
        <div style="padding: 15px 22px 16px; text-align: center; color: #555; font-size: 16px; line-height: 28px; font-weight: bold; margin: 40px 0 30px; font-style: italic;">
       Děkujeme,<br>
         že jste si vybrali právě nás a naše vířivky<br>
  <span style="font-weight: normal;">Váš Tým Spahouse</span>
       </div>
      <div style="border-top: 1px solid #ecebeb; border-bottom: 1px solid #ecebeb; padding: 14px 22px 14px; text-align: center; color: #FFF;">
        <a href="https://www.spahouse.cz/" style="color: #009feb; font-size: 14px; text-decoration: none; font-weight: bold;">www.spahouse.cz</a></div>

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
*/


//$mail->AddBCC($email_text);

$mail->isHTML(true); // Set email   format to HTML

$mail->Body = $body;
$mail->AltBody = strip_tags($body);

if (!$mail->send()) {
    echo 'Message could not be sent.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
}

}