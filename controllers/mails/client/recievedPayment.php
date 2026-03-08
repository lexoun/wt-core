<?php

if($price['customer'] == 1){

    $target_shop = 'spahouse';

}elseif($price['customer'] == 0){

    $target_shop = 'saunahouse';

}elseif($price['customer'] == 3){

    $target_shop = 'spahouse';

}


// Mail
require_once MODEL . 'mailsModel.php';

$mail = connectMail($target_shop);

$body = '<div id="wrapper" dir="ltr" style="background-color: #f5f5f5; margin: 0; padding: 40px 0 40px 0; -webkit-text-size-adjust: none !important; width: 100%;">
			<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tbody><tr>
<td align="center" valign="top">
						<div id="template_header_image">
							<p style="margin-top: 0; margin-bottom: 30px;"><img src="https://www.wellnesstrade.cz/admin/assets/images/'.$target_shop.'-shop.png" alt="'.ucfirst($target_shop).'.cz" style="height: 64px;" height="64"></p>						</div>
						<table border="0" cellpadding="0" cellspacing="0" width="780" id="template_container" style="box-shadow: 0 1px 4px rgba(0,0,0,0.1) !important; background-color: #fdfdfd; border: 1px solid #dcdcdc; border-radius: 3px !important;">
<tbody>
<tr>
<td align="center" valign="top">
<table border="0" cellpadding="0" cellspacing="0" width="780" id="template_body"><tbody><tr>
<td valign="top" id="body_content" style="background-color: #fdfdfd;">
<!-- Content -->
<table border="0" cellpadding="20" cellspacing="0" width="100%"><tbody><tr>
<td valign="top" style="padding: 30px 48px 20px 48px;text-align: center;">		<img src="https://www.wellnesstrade.cz/data/assets/icon1.jpg" width="86">													<div id="body_content_inner" style="color: #333; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif; font-size: 14px; line-height: 25px; text-align: left; margin-top: 14px;">
  
<p>Dobrý den,</p>
  <p>rádi bychom Vám potvrdili přijetí platby ve výši <strong>'.thousand_seperator($total_price).$currency['sign'].'</strong>.</p> 
  <p>Děkujeme</p>

<p>Tým '.ucfirst($target_shop).'</p>
  </div>
</td>
</tr>
</tbody></table>
</td>
  <tr>
    <td style="font-family: helvetica, roboto;">
      <div style="border-top: 1px solid #ecebeb; padding: 14px 22px 14px; text-align: center; color: #FFF;">
        <a href="https://www.'.$target_shop.'.cz/" style="color: #009feb; font-size: 14px; text-decoration: none; font-weight: bold;">www.'.$target_shop.'.cz</a></div>
  
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
//    $mail->addBCC('becher.filip@gmail.com', 'FB');

$mail->isHTML(true); // Set email   format to HTML

$mail->Subject = 'Potvrzení přijetí platby';
$mail->Body = $body;
$mail->AltBody = strip_tags($body);

send_mail($mail, $getclient['id']);

