<?php

if($price['customer'] == 1){

    $target_shop = 'spahouse';
    $payment_product = 'virivku';
    $product = 'vířivky';

}elseif($price['customer'] == 0){

    $target_shop = 'saunahouse';
    $payment_product = 'saunu';
    $product = 'sauny';

}elseif($price['customer'] == 3){

    $target_shop = 'spahouse';
    $payment_product = 'virivku a saunu';
    $product = 'vířivky a sauny';

}


if(!empty($price['phone']) && strlen($price['phone']) == 9) {

    $warehouse_query =  $mysqli->query("SELECT brand, fullname FROM warehouse_products WHERE connect_name = '" . $price['product'] . "'") or die($mysqli->error);
    $warehouse = mysqli_fetch_array($warehouse_query);

    $phone = '+420' . $price['phone'];
    $message = 'Dobry den, potvrzujeme prijeti zalohove platby za '.$payment_product.'. V pripade otazek na: realizaci (776 553 722), administrativa a fakturace (777 202 879), nas kontaktujte. Tym '.strtoupper($target_shop);

    /*

    $msg = new \jakubenglicky\SmsManager\Message\Message();


    $msg->setTo([$phone]);
    $msg->setBody($message);

    $smsClient = new \jakubenglicky\SmsManager\Http\Cliennew \jakubenglicky\SmsManager\Http\Client('beef7e2706a4510897ece8550f4edd51bea3f527');

    try {

        $smsClient->send($msg);

    } catch (Exception $e) {
        echo 'Caught exception: ', $e->getMessage(), "\n";
    }
    */

    $url = "http://http-api.smsmanager.cz/Send?apikey=5fa3d36ec4d8f4292289a6c695838c934ef98864&number=".$phone."&message=".urlencode($message);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

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
  <p>rádi bychom Vám potvrdili přijetí platby ve výši <strong>'.thousand_seperator($total_price).$currency['sign'].'</strong> a dovolujeme si Vám zaslat užitečné kontakty při řešení realizace '.$product.'.</p> </div>
</td>
</tr>
</tbody></table>
</td>
  <tr>
    <td style="font-family: helvetica, roboto; border-top: 1px solid #dcdcdc;">
      <div style="padding: 32px 40px 40px; text-align: center;">
        <div style="border-radius: 50%; margin: 0 auto; width: 37px; height: 40px; background: #f4f3f3; text-align: center; line-height: 40px; color: #009feb; font-weight: bold; font-size: 17px; padding-left: 3px;border: 1px solid #dcdcdc; ">1.</div>
        <h4 style="font-size: 18px; font-weight: bold; text-transform: uppercase; color: #009feb; border-bottom: 1px solid #ecebeb; padding-bottom: 30px; margin-bottom: 20px;">Kdo s Vámi bude řešit vše potřebné?</h4>    
        
        <div style="display: inline-block; width: 100%;">
          <div style="text-align:center; margin: 20px 0 14px; width: 33%; float: left;">
                      <p style="margin: 8px 0 4px; font-size: 16px; font-weight: bold; ">Administrativa</p>
  <p style="margin: 4px 0 32px; line-height: 18px; font-size: 14px; color: #666; font-style: italic; ">Zákaznický servis, <br>objednávky příslušenství</p>
            <div style="border-right: 1px solid #ecebeb; width:100%; margin-bottom: 10px; float: left;"><img src="https://www.wellnesstrade.cz/data/assets/jitka.jpg" width="130"></div>
            <img src="https://www.wellnesstrade.cz/data/assets/flag_czech.png" width="34"><img src="https://www.wellnesstrade.cz/data/assets/flag_english.png" width="34">
            <p style="margin: 6px 0 2px; font-size: 16px; font-weight: bold; font-style: italic; ">Jitka Valůšková</p>
  <p style="margin: 2px 0 10px; font-size: 14px; color: #666; font-style: italic; ">Vedoucí administrativy</p>
  <p style="margin: 18px 0 4px; font-size: 15px; color: #666">+420 777 20 28 79</p>
  <p style="margin: 4px 0 4px; font-size: 15px; color: #009feb; font-weight: 600;">valuskova@'.$target_shop.'.cz</p>
          </div>
                    <div style="text-align:center; margin: 20px 0 14px; width: 33%; float: left;">
                      <p style="margin: 8px 0 4px; font-size: 16px; font-weight: bold; ">Termíny realizací</p>
  <p style="margin: 4px 0 32px; line-height: 18px; font-size: 14px; color: #666; font-style: italic; ">Realizace, logistika<br> a koordinace</p>
            <div style="border-right: 1px solid #ecebeb; width:100%; margin-bottom: 10px; float: left;"><img src="https://www.wellnesstrade.cz/data/assets/jan.jpg" width="130"></div>
            <img src="https://www.wellnesstrade.cz/data/assets/flag_czech.png" width="34"><img src="https://www.wellnesstrade.cz/data/assets/flag_english.png" width="34">
            <p style="margin: 6px 0 2px; font-size: 16px; font-weight: bold; font-style: italic; ">Jan Pazderský</p>
  <p style="margin: 2px 0 10px; font-size: 14px; color: #666; font-style: italic; ">Koordinátor</p>
  <p style="margin: 18px 0 4px; font-size: 15px; color: #666">+420 776 55 37 22</p>
  <p style="margin: 4px 0 4px; font-size: 15px; color: #009feb; font-weight: 600;">pazdersky@'.$target_shop.'.cz</p>
          </div>
                    <div style="text-align:center; margin: 20px 0 14px; width: 33%; float: left;">
                      <p style="margin: 8px 0 4px; font-size: 16px; font-weight: bold; ">Technické dotazy</p>
  <p style="margin: 4px 0 32px; line-height: 18px; font-size: 14px; color: #666; font-style: italic; ">Technické informace, <br>instalace</p>
            <div style=" width:100%; margin-bottom: 10px; float: left;"><img src="https://www.wellnesstrade.cz/data/assets/ladislav.jpg" width="130"></div>
            <img src="https://www.wellnesstrade.cz/data/assets/flag_czech.png" width="34"><img src="https://www.wellnesstrade.cz/data/assets/flag_english.png" width="34"><img src="https://www.wellnesstrade.cz/data/assets/flag_deutsch.png" width="34">
            <p style="margin: 6px 0 2px; font-size: 16px; font-weight: bold; font-style: italic; ">Ladislav Šefl</p>
  <p style="margin: 2px 0 10px; font-size: 14px; color: #666; font-style: italic; ">Vedoucí technického oddělení</p>
  <p style="margin: 18px 0 4px; font-size: 15px; color: #666">+420 777 90 40 90</p>
  <p style="margin: 4px 0 4px; font-size: 15px; color: #009feb; font-weight: 600;">sefl@'.$target_shop.'.cz</p>
          </div>

        </div>
        
      <div style="border: 1px solid #dcdcdc; background-color: #eee; margin-top: 50px;padding: 24px 18px 30px; text-align: center; color: #333; font-weight: bold; font-size: 13.5px; line-height: 24 px;">
        <div style="border-radius: 50%; margin: 0 auto; width: 37px; height: 40px; background: #FFF; text-align: center; line-height: 40px; color: #009feb; font-weight: bold; margin-bottom: 18px; font-size: 17px; padding-left: 3px;border: 1px solid #dcdcdc; ">2.</div>
        V případě, že nebudete mít potřebu nás kontaktovat, <span style="color: #009feb;">ozveme se Vám přibližně 3 týdny před realizací</span>, abychom Vás požádali o zaslání fotografií stavební přípravy, aby mohlo dojít k naplánování přesného termínu realizace.</div>
      
<div style="padding: 15px 22px 16px; text-align: center; color: #555; font-size: 16px; line-height: 28px; font-weight: bold; margin: 40px 0 30px; font-style: italic;">
       Děkujeme,<br>
         že jste si vybrali právě nás a naše '.$product.'<br>
  <span style="font-weight: normal;">Váš Tým '.ucfirst($target_shop).'</span>
       </div>
      <div style="border-top: 1px solid #ecebeb; border-bottom: 1px solid #ecebeb; padding: 14px 22px 14px; text-align: center; color: #FFF;">
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

$mail->Subject = 'Potvrzení přijetí platby a kontaktní údaje';
$mail->Body = $body;
$mail->AltBody = strip_tags($body);

send_mail($mail, $getclient['id']);