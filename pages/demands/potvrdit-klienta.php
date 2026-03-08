<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
$clientquery = $mysqli->query('SELECT * FROM demands WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);
$client = mysqli_fetch_assoc($clientquery);

if (isset($client['customer']) && $client['customer'] == 1 || $client['customer'] == 3) {

    $contact_mail = 'eshop@spahouse.cz';
    $web_address = 'Spahouse.cz';

} elseif (isset($client['customer']) && $client['customer'] == 0) {

    $contact_mail = 'eshop@saunahouse.cz';
    $web_address = 'Saunahouse.cz';

}

$title = 'Vítejte v klientském rozhraní';

$subject = $web_address . ' - Přihlašovací údaje';

$opening_text = '<p style="margin: 0 0 16px;">Dobrý den,</p><p style="margin: 0 0 16px;">právě jste obdrželi přihlašovací údaje do Vašeho klientského rozhraní.</p>

<table width="340" cellspacing="1" cellpadding="1" border="0" style="float: left; margin: 18px 0 27px; border: 1px solid #dcdcdc; padding: 23px 30px 22px; line-height: 25px; background: #fbfbfb;">
<tbody>
<tr>
  <td style="width: 50px;">Email:</td>
  <td><strong><a href="mailto:' . $client['email'] . '" target="_blank" style="color: #737373;text-decoration: none;">' . $client['email'] . '</a></strong></td>
</tr>

<tr>
  <td>Heslo:</td>
  <td><strong>' . $client['secretstring'] . '</strong></td>
</tr>
</tbody>
</table>

<a href="https://www.wellnesstrade.cz/klient?mail_login=' . $client['email'] . '&mail_password=' . $client['secretstring'] . '" style="float: left; color:#ffffff;text-decoration:none;margin-top: 40px;margin-left: 30px;" target="_blank" data-saferedirecturl="https://www.google.com/url?hl=cs&amp;q=https://www.wellnesstrade.cz/klient?mail_login=' . $client['email'] . '&mail_password=' . $client['secretstring'] . '&amp;source=gmail&amp;ust=1480282230364000&amp;usg=AFQjCNF98L3CiqlClwJcocu24oyfUphIiw">
							<table border="0" align="center" width="270" cellpadding="0" cellspacing="0" bgcolor="2b303a" style="background-color:#2b303a;border-radius:50px" class="m_-4809656733968689948cta-button m_-4809656733968689948main_color">

								<tbody><tr><td height="17" style="font-size:17px;line-height:17px">&nbsp;</td></tr>

								<tr>

	                				<td align="center" style="color:#ffffff;font-size:18px;" class="m_-4809656733968689948cta-text">


		                    			<div style="line-height:24px;padding-top:2px;font-size:18px">
			                    			Vstoupit do rozhraní
		                    			</div>
		                    		</td>

	                			</tr>

								<tr><td height="17" style="font-size:17px;line-height:17px">&nbsp;</td></tr>

							</tbody></table></a>

<p style="clear: both;"></p>

<p style="margin: 16px 0 16px;">Přihlásit se můžete na adrese: <a href="https://eshop.spahouse.cz/" target="_blank">https://eshop.spahouse.cz/</a></p>

<p style="margin: 0 0 16px;">Heslo si následně můžete změnit po kliknutí na "Změnit údaje" v horní navigaci.</p>

<p style="margin: 0 0 16px;">S pozdravem a přáním hezkého dne</p>

<p style="margin: 0 0 16px;">WellnessTrade.cz</p>';

$body = '<div id="wrapper" dir="ltr" style="background-color: #f5f5f5; margin: 0; padding: 60px 0 70px 0; -webkit-text-size-adjust: none !important; width: 100%;">
			<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tbody><tr>
<td align="center" valign="top">
						<div id="template_header_image">
							<p style="margin-top: 0; margin-bottom: 3em;">
							<img src="http://www.spahouse.cz/wp-content/uploads/2015/12/LOGOSPAHOUSE.png" alt="Spahouse.cz" style="margin-right: 40px; margin-bottom: 3px; border: none; display: inline; font-size: 14px; font-weight: bold; height: auto; line-height: 100%; outline: none; text-decoration: none; text-transform: capitalize;">
							<img src="https://www.saunahouse.cz/wp-content/uploads/2016/06/saunahouse-logo.png" alt="Saunahouse.cz" style="border: none; display: inline; font-size: 14px; font-weight: bold; height: auto; line-height: 100%; outline: none; text-decoration: none; text-transform: capitalize;">

							</p>						</div>
						<table border="0" cellpadding="0" cellspacing="0" width="780" id="template_container" style="box-shadow: 0 1px 4px rgba(0,0,0,0.1) !important; background-color: #fdfdfd; border: 1px solid #dcdcdc; border-radius: 3px !important;">
<tbody><tr>
<td align="center" valign="top">
									<!-- Header -->
									<table border="0" cellpadding="0" cellspacing="0" width="780" id="template_header" style="background-color: #2b303a; border-radius: 3px 3px 0 0 !important; color: #ffffff; border-bottom: 0; font-weight: bold; line-height: 100%; vertical-align: middle; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif;"><tbody><tr>
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
							<strong><a style="color:#333333;font-size:22px;text-decoration:none" href="mailto:' . $contact_mail . '" target="_blank">' . $contact_mail . '</a></strong><br>
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
$mail->Host = 'mail.webglobe.cz'; // Specify main and backup SMTP servers
$mail->SMTPAuth = true; // Enable SMTP authentication
$mail->Username = 'admin@wellnesstrade.cz'; // SMTP username
$mail->Password = 'RD4ufcLv'; // SMTP password
$mail->SMTPSecure = 'ssl'; // Enable TLS encryption, `ssl` also accepted
$mail->Port = 465; // TCP port to connect to

$mail->From = 'admin@wellnesstrade.cz';
$mail->FromName = 'WellnessTrade.cz';
$mail->addAddress($client['email'], $client['user_name']);

$mail->DKIM_domain = 'wellnesstrade.cz';
$mail->DKIM_private = $_SERVER['DOCUMENT_ROOT'] . '/admin/config/keys/private.key';
$mail->DKIM_selector = 'phpmailer';
$mail->DKIM_passphrase = '';
$mail->DKIM_identity = 'admin@wellnesstrade.cz';

$mail->isHTML(true); // Set email   format to HTML

$mail->Subject = $subject;
$mail->Body = $body;

if (!$mail->send()) {

    echo 'Message could not be sent.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;

} else {

    $clientquery = $mysqli->query('UPDATE demands SET active = 1 WHERE id = "' . $client['id'] . '"') or die($mysqli->error);

    header('Location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $client['id'] . '&success=change_status&has_mail=true');
    exit;
}
