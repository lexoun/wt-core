<?php

		$body = '<div id="wrapper" dir="ltr" style="background-color: #f5f5f5; margin: 0; padding: 70px 0 70px 0; -webkit-text-size-adjust: none !important; width: 100%;">
					<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tbody><tr>
		<td align="center" valign="top">
								<div id="template_header_image">
									<p style="margin-top: 0; margin-bottom: 3em;"><img src="https://www.wellnesstrade.cz/wp-content/uploads/2015/03/logoblack.png" alt="Saunahouse.cz" style="border: none; display: inline; font-size: 14px; font-weight: bold; height: auto; line-height: 100%; outline: none; text-decoration: none; text-transform: capitalize;"></p>						</div>
								<table border="0" cellpadding="0" cellspacing="0" width="780" id="template_container" style="box-shadow: 0 1px 4px rgba(0,0,0,0.1) !important; background-color: #fdfdfd; border: 1px solid #dcdcdc; border-radius: 3px !important;">
		<tbody><tr>
		<td align="center" valign="top">
											<!-- Header -->
											<table border="0" cellpadding="0" cellspacing="0" width="780" id="template_header" style="background-color: #2b303a; border-radius: 3px 3px 0 0 !important; color: #ffffff; border-bottom: 0; font-weight: bold; line-height: 100%; vertical-align: middle; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif;"><tbody><tr>
		<td id="header_wrapper" style="padding: 36px 48px; display: block;">
		<h1 style="color: #ffffff; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif; font-size: 30px; font-weight: 300; line-height: 150%; margin: 0; text-align: left; text-shadow: 0 1px 0 #aa3351; -webkit-font-smoothing: antialiased;">'.$title.'</h1>
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
		'.$opening_text.'

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


		</td>
						</tr></tbody></table>
		</div>';


 		$mail = new PHPMailer\PHPMailer\PHPMailer(true);

          //$mail->SMTPDebug = 3;                               // Enable verbose debug output
          $mail->CharSet = 'UTF-8';
          $mail->isSMTP();           
          $mail->Host = 'mail.webglobe.cz';  // Specify main and backup SMTP servers
          $mail->SMTPAuth = true;                               // Enable SMTP authentication
          $mail->Username = 'admin@wellnesstrade.cz';                 // SMTP username
          $mail->Password = 'RD4ufcLv';                           // SMTP password
          $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
          $mail->Port = 465;                                    // TCP port to connect to

          $mail->From = 'admin@wellnesstrade.cz';
          $mail->FromName = 'WellnessTrade.cz';

          $mail->DKIM_domain = 'wellnesstrade.cz';
          $mail->DKIM_private = $_SERVER['DOCUMENT_ROOT'].'/admin/config/keys/private.key';
          $mail->DKIM_selector = 'phpmailer';
          $mail->DKIM_passphrase = '';
          $mail->DKIM_identity = 'admin@wellnesstrade.cz';

          $mail->isHTML(true);                                  // Set email   format to HTML

          $mail->Subject = $subject;
          $mail->Body    = $body;


?>