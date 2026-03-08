<?php


if(!empty($_REQUEST['action']) && $_REQUEST['action'] == 'smsmessage') {

    if (!empty($_POST['message']) && strlen($billing['billing_phone']) == 9) {

        $phone = phone_prefix($billing['billing_phone_prefix']).$billing['billing_phone'];
        $message = remove_diacritics($_POST['message']);


        /*
        $msg = new \jakubenglicky\SmsManager\Message\Message();

        $msg->setTo([$phone]);
        $msg->setBody($message);

        $smsClient = new \jakubenglicky\SmsManager\Http\Client('beef7e2706a4510897ece8550f4edd51bea3f527');

        try {

            $smsClient->send($msg);

        } catch (Exception $e) {
            echo 'Caught aaexception: ', $e->getMessage(), "\n";
        }
        */

        if($id == '4674'){

            $url = "https://http-api.smsmanager.cz/Send?apikey=5fa3d36ec4d8f4292289a6c695838c934ef98864&number=".$phone."&message=".urlencode($message);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_POST, false);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE);

            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);

            print_r($response);
            print_r($err);

        }else{

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

        header('location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $id . '&sms=success');
        exit;

    }else{

        header('location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $id . '&sms=missing');
        exit;
    }

}



// old to delete
//if (isset($_REQUEST['action']) && $_REQUEST['action'] == "paymentRecieved") {
//
//    $invoice_query = $mysqli->query("SELECT total_price, currency FROM demands_advance_invoices i WHERE id = '".$_REQUEST['invoice_id']."'") or die($mysqli->error);
//    $invoice = mysqli_fetch_assoc($invoice_query);
//
//    $currency = currency($invoice['currency']);
//
//    // Mail
//    $target_shop = 'spahouse';
//    require_once MODEL . 'mailsModel.php';
//
//    $mail = connectMail($target_shop);
//
//    $body = '<div id="wrapper" dir="ltr" style="background-color: #f5f5f5; margin: 0; padding: 40px 0 40px 0; -webkit-text-size-adjust: none !important; width: 100%;">
//			<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tbody><tr>
//<td align="center" valign="top">
//						<div id="template_header_image">
//							<p style="margin-top: 0; margin-bottom: 30px;"><img src="https://www.wellnesstrade.cz/admin/assets/images/spahouse-shop.png" alt="Spahouse.cz" style="height: 64px;" height="64"></p>						</div>
//						<table border="0" cellpadding="0" cellspacing="0" width="780" id="template_container" style="box-shadow: 0 1px 4px rgba(0,0,0,0.1) !important; background-color: #fdfdfd; border: 1px solid #dcdcdc; border-radius: 3px !important;">
//<tbody>
//<tr>
//<td align="center" valign="top">
//<table border="0" cellpadding="0" cellspacing="0" width="780" id="template_body"><tbody><tr>
//<td valign="top" id="body_content" style="background-color: #fdfdfd;">
//<!-- Content -->
//<table border="0" cellpadding="20" cellspacing="0" width="100%"><tbody><tr>
//<td valign="top" style="padding: 30px 48px 20px 48px;text-align: center;">		<img src="https://www.wellnesstrade.cz/data/assets/icon1.jpg" width="86">													<div id="body_content_inner" style="color: #333; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif; font-size: 14px; line-height: 25px; text-align: left; margin-top: 14px;">
//
//<p>Dobrý den,</p>
//  <p>rádi bychom Vám potvrdili přijetí platby ve výši <strong>'.thousand_seperator($invoice['total_price']).$currency['sign'].'</strong> a dovolujeme si Vám zaslat užitečné kontakty při řešení realizace vířivé vany.</p> </div>
//</td>
//</tr>
//</tbody></table>
//</td>
//  <tr>
//    <td style="font-family: helvetica, roboto; border-top: 1px solid #dcdcdc;">
//      <div style="padding: 32px 40px 40px; text-align: center;">
//        <div style="border-radius: 50%; margin: 0 auto; width: 37px; height: 40px; background: #f4f3f3; text-align: center; line-height: 40px; color: #009feb; font-weight: bold; font-size: 17px; padding-left: 3px;border: 1px solid #dcdcdc; ">1.</div>
//        <h4 style="font-size: 18px; font-weight: bold; text-transform: uppercase; color: #009feb; border-bottom: 1px solid #ecebeb; padding-bottom: 30px; margin-bottom: 20px;">Kdo s Vámi bude řešit vše potřebné?</h4>
//
//        <div style="display: inline-block; width: 100%;">
//          <div style="text-align:center; margin: 20px 0 14px; width: 33%; float: left;">
//                      <p style="margin: 8px 0 4px; font-size: 16px; font-weight: bold; ">Administrativa</p>
//  <p style="margin: 4px 0 32px; line-height: 18px; font-size: 14px; color: #666; font-style: italic; ">Zákaznický servis, <br>objednávky příslušenství</p>
//            <div style="border-right: 1px solid #ecebeb; width:100%; margin-bottom: 10px; float: left;"><img src="https://www.wellnesstrade.cz/data/assets/jitka.jpg" width="130"></div>
//            <img src="https://www.wellnesstrade.cz/data/assets/flag_czech.png" width="34"><img src="https://www.wellnesstrade.cz/data/assets/flag_english.png" width="34">
//            <p style="margin: 6px 0 2px; font-size: 16px; font-weight: bold; font-style: italic; ">Jitka Valůšková</p>
//  <p style="margin: 2px 0 10px; font-size: 14px; color: #666; font-style: italic; ">Vedoucí administrativy</p>
//  <p style="margin: 18px 0 4px; font-size: 15px; color: #666">+420 777 20 28 79</p>
//  <p style="margin: 4px 0 4px; font-size: 15px; color: #009feb; font-weight: 600;">valuskova@spahouse.cz</p>
//          </div>
//                    <div style="text-align:center; margin: 20px 0 14px; width: 33%; float: left;">
//                      <p style="margin: 8px 0 4px; font-size: 16px; font-weight: bold; ">Termíny realizací</p>
//  <p style="margin: 4px 0 32px; line-height: 18px; font-size: 14px; color: #666; font-style: italic; ">Realizace, logistika<br> a koordinace</p>
//            <div style="border-right: 1px solid #ecebeb; width:100%; margin-bottom: 10px; float: left;"><img src="https://www.wellnesstrade.cz/data/assets/jan.jpg" width="130"></div>
//            <img src="https://www.wellnesstrade.cz/data/assets/flag_czech.png" width="34"><img src="https://www.wellnesstrade.cz/data/assets/flag_english.png" width="34">
//            <p style="margin: 6px 0 2px; font-size: 16px; font-weight: bold; font-style: italic; ">Jan Pazderský</p>
//  <p style="margin: 2px 0 10px; font-size: 14px; color: #666; font-style: italic; ">Koordinátor</p>
//  <p style="margin: 18px 0 4px; font-size: 15px; color: #666">+420 776 55 37 22</p>
//  <p style="margin: 4px 0 4px; font-size: 15px; color: #009feb; font-weight: 600;">pazdersky@spahouse.cz</p>
//          </div>
//                    <div style="text-align:center; margin: 20px 0 14px; width: 33%; float: left;">
//                      <p style="margin: 8px 0 4px; font-size: 16px; font-weight: bold; ">Technické dotazy</p>
//  <p style="margin: 4px 0 32px; line-height: 18px; font-size: 14px; color: #666; font-style: italic; ">Technické informace, <br>instalace</p>
//            <div style=" width:100%; margin-bottom: 10px; float: left;"><img src="https://www.wellnesstrade.cz/data/assets/ladislav.jpg" width="130"></div>
//            <img src="https://www.wellnesstrade.cz/data/assets/flag_czech.png" width="34"><img src="https://www.wellnesstrade.cz/data/assets/flag_english.png" width="34"><img src="https://www.wellnesstrade.cz/data/assets/flag_deutsch.png" width="34">
//            <p style="margin: 6px 0 2px; font-size: 16px; font-weight: bold; font-style: italic; ">Ladislav Šefl</p>
//  <p style="margin: 2px 0 10px; font-size: 14px; color: #666; font-style: italic; ">Vedoucí technického oddělení</p>
//  <p style="margin: 18px 0 4px; font-size: 15px; color: #666">+420 777 90 40 90</p>
//  <p style="margin: 4px 0 4px; font-size: 15px; color: #009feb; font-weight: 600;">sefl@spahouse.cz</p>
//          </div>
//
//        </div>
//
//      <div style="border: 1px solid #dcdcdc; background-color: #eee; margin-top: 50px;padding: 24px 18px 30px; text-align: center; color: #333; font-weight: bold; font-size: 13.5px; line-height: 24 px;">
//        <div style="border-radius: 50%; margin: 0 auto; width: 37px; height: 40px; background: #FFF; text-align: center; line-height: 40px; color: #009feb; font-weight: bold; margin-bottom: 18px; font-size: 17px; padding-left: 3px;border: 1px solid #dcdcdc; ">2.</div>
//        V případě, že nebudete mít potřebu nás kontaktovat, <span style="color: #009feb;">ozveme se Vám přibližně 3 týdny před realizací</span>, abychom Vás požádali o zaslání fotografií stavební přípravy, aby mohlo dojít k naplánování přesného termínu realizace.</div>
//
//<div style="padding: 15px 22px 16px; text-align: center; color: #555; font-size: 16px; line-height: 28px; font-weight: bold; margin: 40px 0 30px; font-style: italic;">
//       Děkujeme,<br>
//         že jste si vybrali právě nás a naše vířivky<br>
//  <span style="font-weight: normal;">Váš Tým Spahouse</span>
//       </div>
//      <div style="border-top: 1px solid #ecebeb; border-bottom: 1px solid #ecebeb; padding: 14px 22px 14px; text-align: center; color: #FFF;">
//        <a href="https://www.spahouse.cz/" style="color: #009feb; font-size: 14px; text-decoration: none; font-weight: bold;">www.spahouse.cz</a></div>
//
//  </td>
//  </tr>
//</tr></tbody></table>
//</td>
//</tr>
//</tbody></table>
//</td>
//</tr></tbody></table>
//</div>';
//
////    $mail->addAddress($email);
//    $mail->addAddress('becher.filip@gmail.com');
////    $mail->addBCC('becher.filip@gmail.com', 'FB');
//
//    $mail->isHTML(true); // Set email   format to HTML
//
//    $mail->Subject = 'Potvrzení přijetí platby a kontaktní údaje';
//    $mail->Body = $body;
//    $mail->AltBody = strip_tags($body);
//
//    if (!$mail->send()) {
//        echo 'Message could not be sent.';
//        echo 'Mailer Error: ' . $mail->ErrorInfo;
//    }
//
//
//
//exit;
//
//}
// old to delete


if (isset($_REQUEST['action']) && $_REQUEST['action'] == "changestatus") {

    $mysqli->query("UPDATE demands SET status = '" . $_POST['status'] . "' WHERE id = '" . $_REQUEST['id'] . "'");

    if ($_REQUEST['status'] == 15 && $getclient['status'] != 15) {

        // EMAIL
        $subject = 'Nová realizace - '.$getclient['user_name'];
        $title = $subject;

        $opening_text = '<p style="margin: 0 0 16px;">V administraci došlo k přesunutí poptávky <a href="https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id='.$getclient['id'].'" target="_blank">'.$getclient['user_name'].'</a> do kategorie <strong>"Nová realizace"</strong>.</p>
        
        <p style="margin: 0 0 16px;">Přesun provedl: '.$client['user_name'].'</p>

    <p style="clear: both;"></p>';

        require_once CONTROLLERS . '/admin_mails_templates.php';

        $admins_query = $mysqli->query("SELECT email, user_name FROM demands WHERE (role = 'salesman-technician') AND active = 1");
        while ($admins = mysqli_fetch_array($admins_query)){

            $mail->addAddress($admins['email'], $admins['user_name']);

        }

        if(!$mail->send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        }

    }

    if($_REQUEST['status'] == 12 || $_REQUEST['status'] == 15 || $_REQUEST['status'] == 4)  {

        $mysqli->query("UPDATE warehouse SET reserved = 0, reserved_date = '0000-00-00', reserved_mail = 0 WHERE demand_id = '".$_REQUEST['id']."'")or die($mysqli->error);

    }

    header('location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $_REQUEST['id'] . '&success=changestatus');
    exit;

}



if (isset($_REQUEST['action']) && $_REQUEST['action'] == "createUser") {


    if(!empty($_REQUEST['status'])){

        $mysqli->query("UPDATE demands SET status = '" . $_REQUEST['status'] . "' WHERE id = '" . $_REQUEST['id'] . "'");
        $mysqli->query("UPDATE warehouse SET status = 4 WHERE demand_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

    }

    /*

    // select clients shop
    if (isset($getclient['customer']) && $getclient['customer'] == 1 || $getclient['customer'] == 3) {

        $target_shop = 'spahouse';
        $contact_mail = 'eshop@spahouse.cz';
        $web_address = 'Spahouse.cz';

    } elseif (isset($getclient['customer']) && $getclient['customer'] == 0) {

        $target_shop = 'saunahouse';
        $contact_mail = 'eshop@saunahouse.cz';
        $web_address = 'Saunahouse.cz';

    }

    // get shop info
    $shops_query = $mysqli->query("SELECT * FROM shops WHERE slug = '".$target_shop."'") or die($mysqli->error);
    $shop = mysqli_fetch_assoc($shops_query);

    require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/controllers/stores/woo_customer.php";
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    require_once MODEL . 'mailsModel.php';

    $mail = connectMail($target_shop);

    $password = mb_substr($getclient['secretstring'], 0, 5);

    if($getclient['woocommerce_id'] != 0){

        $user = get_user_by('id', $getclient['woocommerce_id']);

    }else{

        $user = get_user_by('email', $getclient['email']);

    }

    if(!empty($user)) {

        wp_update_user([
            'ID' => $user->ID,
            'role' => 'client'
        ]);

        // assignment of store id to customer
        $mysqli->query("UPDATE demands SET woocommerce_id = '".$user->ID."' WHERE id = '" . $getclient['id'] . "'") or die($mysqli->error);

    }else{

        $user = wp_create_user( $getclient['email'], $password, $getclient['email'] );

        // get clients addresses
        $address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $getclient['shipping_id'] . '" WHERE b.id = "' . $getclient['billing_id'] . '"') or die($mysqli->error);
        $address = mysqli_fetch_assoc($address_query);


        if (isset($address['shipping_name']) && ($address['shipping_name'] != '' || $address['shipping_surname'] != '')) {

            $first_name = $address['shipping_name'];
            $last_name = $address['shipping_surname'];

        } elseif ($address['billing_name'] && ($address['billing_name'] != '' || $address['billing_surname'] != '')) {

            $first_name = $address['billing_name'];
            $last_name = $address['billing_surname'];

        } else {

            $first_name = $address['billing_company'];

        }

    //
    //    if ( is_wp_error($user) ){
    //        echo $user->get_error_message();
    //        echo $user->get_error_code();
    //    }

        //        function add_user_meta( $user_id, $meta_key, $meta_value, $unique = false )
//        add_user_meta($user, )


        wp_update_user([
            'ID' => $user,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'role' => 'client'
        ]);

        wp_set_password( $password, $user );

    //    echo $password;


        if ($address['billing_company'] != "" || $address['billing_ico'] != 0 || $address['billing_name'] != "" || $address['billing_street'] != "") {

            if (isset($address['billing_country']) && $address['billing_country'] == 'czech') {$billing_country = "CZ";} elseif (isset($address['billing_country']) && $address['billing_country'] == 'slovakia') {$billing_country = "SK";} elseif (isset($address['billing_country']) && $address['billing_country'] == 'austria') {$billing_country = "AT";}

            update_user_meta( $user, "billing_first_name", $address['billing_name']);
            update_user_meta( $user, "billing_last_name", $address['billing_surname']);
            update_user_meta( $user, "billing_company", $address['billing_company'] );
            update_user_meta( $user, "billing_email", $address['billing_email'] );
            update_user_meta( $user, "billing_address_1", $address['billing_street']);
            update_user_meta( $user, "billing_city", $address['billing_city']);
            update_user_meta( $user, "billing_postcode", $address['billing_zipcode'] );
            update_user_meta( $user, "billing_country", 'CZ');
            update_user_meta( $user, "billing_phone", $address['billing_phone'] );

        }

        if (isset($getclient['country']) && $getclient['country'] == 'czech') {$country = "CZ";} elseif (isset($getclient['country']) && $getclient['country'] == 'slovakia') {$country = "SK";} elseif (isset($getclient['country']) && $getclient['country'] == 'austria') {$country = "AT";}


        if ($address['shipping_company'] != "" || $address['shipping_ico'] != 0 || $address['shipping_name'] != "" || $address['shipping_street'] != "") {

            if (isset($address['shipping_country']) && $address['shipping_country'] == 'czech') {$shipping_country = "CZ";} elseif (isset($address['shipping_country']) && $address['shipping_country'] == 'slovakia') {$shipping_country = "SK";} elseif (isset($address['shipping_country']) && $address['shipping_country'] == 'austria') {$shipping_country = "AT";}

            update_user_meta( $user, "shipping_first_name", $address['shipping_name'] );
            update_user_meta( $user, "shipping_last_name", $address['shipping_surname']);
            update_user_meta( $user, "shipping_company", $address['shipping_company'] );
            update_user_meta( $user, "shipping_address_1", $address['shipping_street']);
            update_user_meta( $user, "shipping_city", $address['shipping_city']);
            update_user_meta( $user, "shipping_postcode", $address['shipping_zipcode'] );
            update_user_meta( $user, "shipping_country", 'CZ');

        }else{

            update_user_meta( $user, "shipping_first_name", $address['billing_name'] );
            update_user_meta( $user, "shipping_last_name", $address['billing_surname']);
            update_user_meta( $user, "shipping_company", $address['billing_company'] );
            update_user_meta( $user, "shipping_address_1", $address['billing_street']);
            update_user_meta( $user, "shipping_city", $address['billing_city']);
            update_user_meta( $user, "shipping_postcode", $address['billing_zipcode'] );
            update_user_meta( $user, "shipping_country", 'CZ');


        }

        update_user_meta( $user, "secretstring", $getclient['secretstring']);


        // assignment of store id to customer
        $mysqli->query("UPDATE demands SET woocommerce_id = '".$user."' WHERE id = '" . $getclient['id'] . "'") or die($mysqli->error);



        // todo
        //
        //    $metas = array(
        //        'nickname'   => $userFirstName,
        //        'first_name' => $userFirstName,
        //        'last_name'  => $userLastName ,
        //        'city'       => $userCityID ,
        //        'gender'     => $userGenderID
        //    );
        //
        //    foreach($metas as $key => $value) {
        //        update_user_meta( $user_id, $key, $value );
        //    }
        //  todo end

    */


        /*  OLD TO CHANGE

        $invoices_query = $mysqli->query("SELECT * FROM demands_advance_invoices WHERE demand_id = '" . $_REQUEST['id'] . "'");
        while ($invoice = mysqli_fetch_array($invoices_query)) {
            copy($_SERVER['DOCUMENT_ROOT'] . '/admin/data/invoices/demands/Zalohova_faktura_' . $invoice['id'] . '.pdf', $_SERVER['DOCUMENT_ROOT'] . '/admin/data/files/contracts/' . $getclient['secretstring'] . '/Zalohova_faktura_' . $invoice['id'] . '.pdf');

            $name = 'Zálohová faktura #' . $invoice['status'];
            $slug = 'Zalohova_faktura_' . $invoice['id'];

            $insert_query = $mysqli->query("INSERT INTO documents_contracts (client_id, name, seoslug, icon, extension) VALUES ('$id', '$name', '$slug', 'fa fa-file', 'pdf')");
        }

        $update = $mysqli->query("UPDATE demands SET active = '1', password = '" . $password . "' WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

        if (isset($getclient['customer']) && $getclient['customer'] == 1) {
            $data_hottub_query = $mysqli->query("SELECT * FROM demands_generate_hottub WHERE id = '" . $_REQUEST['id'] . "'");
            $data_hottub = mysqli_fetch_array($data_hottub_query);

            $sale_price = $data_hottub['price_hottub'] + $data_hottub['price_termo'] + $data_hottub['price_stairs'] + $data_hottub['price_microsilk'] + $data_hottub['price_spa_caddy'] + $data_hottub['price_wipod'] + $data_hottub['price_inclear'] + $data_hottub['price_covermate'] + $data_hottub['price_covermate_3'] + $data_hottub['price_wifi'] - $data_hottub['discount'];

            $delivery_price = $data_hottub['price_delivery'];

            $montage_price = $data_hottub['price_montage'];
        }

        $updatewarehouse = $mysqli->query("UPDATE warehouse SET status = 4, sale_price = '$sale_price', delivery_price = '$delivery_price', montage_price = '$montage_price' WHERE demand_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);


        OLD TO CHANGE */

        // EMAIL

    /*
        $title = ucfirst($target_shop).'.cz E-shop - Přihlašovací údaje';

        $subject = $web_address . ' - Přihlašovací údaje';

        $opening_text = '<p style="margin: 0 0 16px;">Vážená klientko, vážený kliente,</p><p style="margin: 0 0 16px;">právě jste obdrželi přihlašovací údaje pro klientské přihlášení do internetového obchodu '.ucfirst($target_shop).'.cz v rámci kterého máte na veškerý nabízený sortiment <strong>slevu 10 %</strong>.</p>
    <p style="margin: 32px 0 0; text-align: center;">Vaše přihlašovací údaje:</p>
    
    <table width="100%" cellspacing="1" cellpadding="1" border="0" style="float: left; margin: 10px 0 27px; font-size:15px; border: 1px solid #dcdcdc; padding: 23px 30px 22px; line-height: 25px; background: #fbfbfb;">
    <tbody>
    <tr>
    <td align="center" style="width: 50%;">Přihlašovací jméno:<br>
    <strong>' . $getclient['email'] . '</strong></td>
    
    <td align="center" style="width: 50%">Heslo:<br>
    <strong>' . $password . '</strong></td>
    </tr>
    </tbody>
    </table>
    
    <p style="clear: both;"></p>
    
    <p style="margin: 16px 0 16px;">Přihlásit se můžete na adrese: <a href="https://eshop.'.$target_shop.'.cz/muj-ucet/" target="_blank">https://eshop.'.$target_shop.'.cz/muj-ucet/</a></p>
    
    <p style="margin: 0 0 16px;">Heslo a ostatní údaje si můžete změnit po kliknutí na záložku "Detaily účtu".</p>
    
    
    <p style="margin: 0 0 16px;">V případě dotazů nás neváhejte kontaktovat. Rádi Vám s výběrem pomůžeme.</p>
    
    <p style="margin: 0 0 16px; line-height: 29px;">Za tým '.ucfirst($target_shop).'.cz<br>
    <strong style="font-size: 18px;">Jitka Valůšková</strong></p>';



        $body = '<div id="wrapper" dir="ltr" style="background-color: #f5f5f5; margin: 0; padding: 40px 0 40px 0; -webkit-text-size-adjust: none !important; width: 100%;">
                <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tbody><tr>
    <td align="center" valign="top">
                            <div id="template_header_image">
                                <p style="margin-top: 0; margin-bottom: 30px;">' . $mail->logo . '</p>						</div>
                            <table border="0" cellpadding="0" cellspacing="0" width="780" id="template_container" style="box-shadow: 0 1px 4px rgba(0,0,0,0.1) !important; background-color: #fdfdfd; border: 1px solid #dcdcdc; border-radius: 3px !important;">
    <tbody><tr>
    <td align="center" valign="top">
                                        <!-- Header -->
                                        <table border="0" cellpadding="0" cellspacing="0" width="780" id="template_header" style="background-color: ' . $mail->main_color . '; border-radius: 3px 3px 0 0 !important; color: #ffffff; border-bottom: 0; font-weight: bold; line-height: 100%; vertical-align: middle; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif;"><tbody><tr>
    <td id="header_wrapper" style="padding: 36px 48px; display: block;">
    <h1 style="color: #ffffff; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif; font-size: 30px; font-weight: 300; line-height: 150%; margin: 0; text-align: left; text-shadow: 0 1px 0 rgba(0,0,0,0.4); -webkit-font-smoothing: antialiased;">' . $title . '</h1>
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
                                                                <div id="body_content_inner" style="color: #333; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif; font-size: 14px; line-height: 25px; text-align: left;">
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
    </tbody></table>
    
    <table cellpadding="0" cellspacing="0" style="font-family: Helvetica Neue, Helvetica, Roboto, Arial, sans-serif; font-size:11px;color:#999999; line-height: 22px;margin-top: 40px;">
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
                                <strong><a style="color:#333333;font-size:22px;text-decoration:none" href="mailto:' . $mail->email_text . '" target="_blank">' . $mail->email_text . '</a></strong><br>
                                Na email se snažíme odpovídat okamžitě
                            </td>
                        </tr>
                    </tbody></table>
    
    
    </td>
                    </tr></tbody></table>
    </div>';



        $mail->addAddress($getclient['email'], $getclient['user_name']);
//        $mail->addBCC('becher.filip@gmail.com', 'FB');


        $mail->isHTML(true); // Set email   format to HTML

        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);


        if (!$mail->send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        }

    //    print_r($mail->Body);
    //    exit;

    }

    */


    // EMAIL END
    header('location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $id . '&createUser=success');
    exit;



}






if (isset($_REQUEST['action']) && $_REQUEST['action'] == "resetUserPassword") {

    // select clients shop
    if (isset($getclient['customer']) && $getclient['customer'] == 1 || $getclient['customer'] == 3) {

        $target_shop = 'spahouse';
        $contact_mail = 'eshop@spahouse.cz';
        $web_address = 'Spahouse.cz';

    } elseif (isset($getclient['customer']) && $getclient['customer'] == 0) {

        $target_shop = 'saunahouse';
        $contact_mail = 'eshop@saunahouse.cz';
        $web_address = 'Saunahouse.cz';

    }

    // get shop info
    $shops_query = $mysqli->query("SELECT * FROM shops WHERE slug = '".$target_shop."'") or die($mysqli->error);
    $shop = mysqli_fetch_assoc($shops_query);

    require_once $_SERVER['DOCUMENT_ROOT'] . "/admin/controllers/stores/woo_customer.php";

    require_once MODEL . 'mailsModel.php';

    $mail = connectMail($target_shop);

    $password = mb_substr($getclient['secretstring'], 0, 5);

    if($getclient['woocommerce_id'] != 0){

        $user = get_user_by('id', $getclient['woocommerce_id']);

    }

    if(!$user = $user->ID){

        $user = get_user_by('login', $getclient['email']);
        if (!$user = $user->ID) $user = wp_create_user( $getclient['email'], $password, $getclient['email'] );

    }

     wp_set_password( $password, $user );


    // EMAIL
    $title = ucfirst($target_shop).'.cz E-shop - Přihlašovací údaje';

    $subject = $web_address . ' - Přihlašovací údaje';

    $opening_text = '<p style="margin: 0 0 16px;">Vážená klientko, vážený kliente,</p><p style="margin: 0 0 16px;">právě jste obdrželi přihlašovací údaje pro klientské přihlášení do internetového obchodu '.ucfirst($target_shop).'.cz v rámci kterého máte na veškerý nabízený sortiment <strong>slevu 10 %</strong>.</p>
<p style="margin: 32px 0 0; text-align: center;">Vaše přihlašovací údaje:</p>

<table width="100%" cellspacing="1" cellpadding="1" border="0" style="float: left; margin: 10px 0 27px; font-size:15px; border: 1px solid #dcdcdc; padding: 23px 30px 22px; line-height: 25px; background: #fbfbfb;">
<tbody>
<tr>
<td align="center" style="width: 50%;">Přihlašovací jméno:<br>
<strong>' . $getclient['email'] . '</strong></td>

<td align="center" style="width: 50%">Heslo:<br>
<strong>' . $password . '</strong></td>
</tr>
</tbody>
</table>

<p style="clear: both;"></p>

<p style="margin: 16px 0 16px;">Přihlásit se můžete na adrese: <a href="https://eshop.'.$target_shop.'.cz/muj-ucet/" target="_blank">https://eshop.'.$target_shop.'.cz/muj-ucet/</a></p>

<p style="margin: 0 0 16px;">Heslo a ostatní údaje si můžete změnit po kliknutí na záložku "Detaily účtu".</p>


<p style="margin: 0 0 16px;">V případě dotazů nás neváhejte kontaktovat. Rádi Vám s výběrem pomůžeme.</p>

<p style="margin: 0 0 16px; line-height: 29px;">Za tým '.ucfirst($target_shop).'.cz<br>
<strong style="font-size: 18px;">Jitka Valůšková</strong></p>';



    $body = '<div id="wrapper" dir="ltr" style="background-color: #f5f5f5; margin: 0; padding: 40px 0 40px 0; -webkit-text-size-adjust: none !important; width: 100%;">
			<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tbody><tr>
<td align="center" valign="top">
						<div id="template_header_image">
							<p style="margin-top: 0; margin-bottom: 30px;">' . $mail->logo . '</p>						</div>
						<table border="0" cellpadding="0" cellspacing="0" width="780" id="template_container" style="box-shadow: 0 1px 4px rgba(0,0,0,0.1) !important; background-color: #fdfdfd; border: 1px solid #dcdcdc; border-radius: 3px !important;">
<tbody><tr>
<td align="center" valign="top">
									<!-- Header -->
									<table border="0" cellpadding="0" cellspacing="0" width="780" id="template_header" style="background-color: ' . $mail->main_color . '; border-radius: 3px 3px 0 0 !important; color: #ffffff; border-bottom: 0; font-weight: bold; line-height: 100%; vertical-align: middle; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif;"><tbody><tr>
<td id="header_wrapper" style="padding: 36px 48px; display: block;">
<h1 style="color: #ffffff; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif; font-size: 30px; font-weight: 300; line-height: 150%; margin: 0; text-align: left; text-shadow: 0 1px 0 rgba(0,0,0,0.4); -webkit-font-smoothing: antialiased;">' . $title . '</h1>
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
															<div id="body_content_inner" style="color: #333; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif; font-size: 14px; line-height: 25px; text-align: left;">
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
</tbody></table>

<table cellpadding="0" cellspacing="0" style="font-family: Helvetica Neue, Helvetica, Roboto, Arial, sans-serif; font-size:11px;color:#999999; line-height: 22px;margin-top: 40px;">
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
							<strong><a style="color:#333333;font-size:22px;text-decoration:none" href="mailto:' . $mail->email_text . '" target="_blank">' . $mail->email_text . '</a></strong><br>
							Na email se snažíme odpovídat okamžitě
						</td>
					</tr>
				</tbody></table>
                </td>
            </tr></tbody></table>
</div>';



    $mail->addAddress($getclient['email'], $getclient['user_name']);
    //$mail->addBCC('becher.filip@gmail.com', 'FB');

    $mail->isHTML(true); // Set email   format to HTML

    $mail->Subject = $subject;
    $mail->Body = $body;
    $mail->AltBody = strip_tags($body);

    if (!$mail->send()) {
        echo 'Message could not be sent.';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    }

    header('location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $id . '&resetPassword=success');
    exit;
}





if (isset($_REQUEST['action']) && $_REQUEST['action'] == "upload_file") {

    $realName = iconv('UTF-8', 'ASCII//TRANSLIT', pathinfo($_FILES['file']['name'], PATHINFO_FILENAME));
    $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

    $oldmask = umask(0);
    if (!is_dir($_SERVER['DOCUMENT_ROOT'] . '/data/clients/uploads/' . $getclient['secretstring'])) {
        mkdir($_SERVER['DOCUMENT_ROOT'] . '/data/clients/uploads/' . $getclient['secretstring'], 0777);
        umask($oldmask);
    }

    $path = $_SERVER['DOCUMENT_ROOT'] . "/data/clients/uploads/" . $getclient['secretstring'] . "/" . $realName.'.'.$extension;
    move_uploaded_file($_FILES['file']['tmp_name'], $path);

    header('location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $id . '&offer=success');
    exit;
}




if (isset($_REQUEST['action']) && $_REQUEST['action'] == "add_document" && isset($_REQUEST['type'])) {

    $name = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
    $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $seoSlug = odkazy($name);

    $oldmask = umask(0);
    if (!is_dir($_SERVER['DOCUMENT_ROOT'] . '/data/clients/documents/' . $getclient['secretstring'])) {
        mkdir($_SERVER['DOCUMENT_ROOT'] . '/data/clients/documents/' . $getclient['secretstring'], 0777);
        umask($oldmask);
    }

    $path = $_SERVER['DOCUMENT_ROOT'] . "/data/clients/documents/" . $getclient['secretstring'] . "/" . $seoSlug . "." . $extension;

    move_uploaded_file($_FILES['file']['tmp_name'], $path);

    $mysqli->query("INSERT INTO documents_contracts (client_id, name, seoslug, type, extension) VALUES ('" . $getclient['id'] . "', '" . $_FILES['file']['name'] . "', '" . $seoSlug . "', '" . $_REQUEST['type'] . "', '" . $extension . "')") or die("4Neexistuje");

    // $mysqli->query('UPDATE demands SET contract = 2 WHERE id = "' . $_REQUEST['id'] . '"') or die("4Neexistuje");

    header('location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $id . '&contract=success');
    exit;
}



if (isset($_REQUEST['action']) && $_REQUEST['action'] == "remove_document" && isset($_REQUEST['document_id'])) {
    $documentQuery = $mysqli->query("SELECT * FROM documents_contracts WHERE id = '" . $_REQUEST['document_id'] . "'") or die($mysqli->error);

    $document = mysqli_fetch_array($documentQuery);

    $path = $_SERVER['DOCUMENT_ROOT'] . "/data/clients/documents/" . $getclient['secretstring'] . "/" . $document['seoslug'] . "." . $document['extension'];

    if (file_exists($path)) {
        unlink($path);
    }

    $mysqli->query("DELETE FROM documents_contracts WHERE id = '" . $_REQUEST['document_id'] . "'") or die($mysqli->error);

    header('location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $_REQUEST['id'] . '&success=remove');
    exit;
}




if (isset($_REQUEST['action']) && $_REQUEST['action'] == "copy_invoices" && isset($_REQUEST['type'])) {

// OLD TO DELETE

//    $invoices_query = $mysqli->query("SELECT * FROM demands_advance_invoices WHERE demand_id = '" . $_REQUEST['id'] . "'");
//    while ($invoice = mysqli_fetch_array($invoices_query)) {
//        copy($_SERVER['DOCUMENT_ROOT'] . '/admin/data/invoices/demands/Zalohova_faktura_' . $invoice['id'] . '.pdf', $_SERVER['DOCUMENT_ROOT'] . '/admin/data/files/contracts/' . $getclient['secretstring'] . '/Zalohova_faktura_' . $invoice['id'] . '.pdf');
//
//        $name = 'Zálohová faktura #' . $invoice['status'];
//        $slug = 'Zalohova_faktura_' . $invoice['id'];
//
//        $insert_query = $mysqli->query("INSERT INTO documents_contracts (client_id, name, seoslug, icon, extension) VALUES ('$id', '$name', '$slug', 'fa fa-file', 'pdf')");
//    }
//
//
//    $name = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
//    $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
//    $seoSlug = odkazy($name);
//
//    $oldmask = umask(0);
//    if (!is_dir($_SERVER['DOCUMENT_ROOT'] . '/data/clients/documents/' . $getclient['secretstring'])) {
//        mkdir($_SERVER['DOCUMENT_ROOT'] . '/data/clients/documents/' . $getclient['secretstring'], 0777);
//        umask($oldmask);
//    }
//
//    $path = $_SERVER['DOCUMENT_ROOT'] . "/data/clients/documents/" . $getclient['secretstring'] . "/" . $seoSlug . "." . $extension;
//
//    move_uploaded_file($_FILES['file']['tmp_name'], $path);
//
//    $mysqli->query("INSERT INTO documents_contracts (client_id, name, seoslug, type, extension) VALUES ('" . $getclient['id'] . "', '" . $_FILES['file']['name'] . "', '" . $seoSlug . "', '" . $_REQUEST['type'] . "', '" . $extension . "')") or die("4Neexistuje");

// OLD TO DELETE

    header('location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $id . '&contract=success');
    exit;
}




if (isset($_REQUEST['action']) && $_REQUEST['action'] == "edit_sold") {

    if ($_REQUEST['type'] == 'technical') {

        $mysqli->query("UPDATE demands SET technical = '" . $_REQUEST['value'] . "' WHERE id = '" . $_REQUEST['id'] . "'");

    } elseif ($_REQUEST['type'] == 'contract') {

        $mysqli->query("UPDATE demands SET contract = '" . $_REQUEST['value'] . "' WHERE id = '" . $_REQUEST['id'] . "'");

    } elseif ($_REQUEST['type'] == 'unfinished') {

        $mysqli->query("UPDATE demands SET unfinished = '" . $_REQUEST['value'] . "' WHERE id = '" . $_REQUEST['id'] . "'");

    } elseif ($_REQUEST['type'] == 'realization') {

        $mysqli->query("UPDATE demands SET confirmed = '" . $_REQUEST['value'] . "' WHERE id = '" . $_REQUEST['id'] . "'");
        saveCalendarEvent($_REQUEST['id'], 'realization');

    }

    header('location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $id . '&edit_sold=success');
    exit;
}



if (isset($_REQUEST['action']) && $_REQUEST['action'] == "changeoffer") {

    $result = glob($_SERVER['DOCUMENT_ROOT'] . "/admin/data/files/demands/" . $getclient['secretstring'] . "/" . $getclient['secretstring'] . ".*");
    foreach ($result as $res) {
        unlink($res);
    }

    $path_parts = pathinfo($_FILES["zmrdus"]["name"]);
    $extension = $path_parts['extension'];

    $seoslug = $getclient['secretstring'] . '.' . $extension;

    $path = $_SERVER['DOCUMENT_ROOT'] . "/admin/data/files/demands/" . $getclient['secretstring'] . "/" . $seoslug;
    move_uploaded_file($_FILES['zmrdus']['tmp_name'], $path);

    header('location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $id . '&offer=success');
    exit;
}



if (isset($_REQUEST['action']) && $_REQUEST['action'] == "remove_picture_technical") {

    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/technical/' . $getclient['secretstring'] . '/big_' . $_REQUEST['picture'])) {

        unlink($_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/technical/' . $getclient['secretstring'] . '/big_' . $_REQUEST['picture']);
    }

    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/technical/' . $getclient['secretstring'] . '/small_' . $_REQUEST['picture'])) {

        unlink($_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/technical/' . $getclient['secretstring'] . '/small_' . $_REQUEST['picture']);

    }

    header('location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $id . '&remove=success');
    exit;
}



if (isset($_REQUEST['action']) && $_REQUEST['action'] == "remove_picture_realization") {

    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/realization/' . $getclient['secretstring'] . '/big_' . $_REQUEST['picture'])) {

        unlink($_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/realization/' . $getclient['secretstring'] . '/big_' . $_REQUEST['picture']);

    }

    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/realization/' . $getclient['secretstring'] . '/small_' . $_REQUEST['picture'])) {

        unlink($_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/realization/' . $getclient['secretstring'] . '/small_' . $_REQUEST['picture']);

    }

    header('location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $id . '&remove=success');
    exit;
}



if (isset($_REQUEST['action']) && $_REQUEST['action'] == "remove") {


exit;

    $suserquery = $mysqli->query('SELECT secretstring, customer FROM demands WHERE id="' . $_REQUEST['id'] . '"') or die("1Neexistuje");
    $suser = mysqli_fetch_assoc($suserquery);

    if (isset($suser['customer']) && $suser['customer'] == 0) {

        $tostockquery = $mysqli->query("SELECT s.product_id, s.reserved FROM demands_sauna_specs s, demands d WHERE s.demand_id = d.id AND d.status < 5 AND s.demand_id = '" . $_REQUEST['id'] . "'");

        while ($tostock = mysqli_fetch_array($tostockquery)) {

            if ($tostock['reserved'] > 0) {

                $instock = $tostock['reserved'];

                $search1 = $mysqli->query("SELECT s.id, s.reserved, s.quantity FROM demands_sauna_specs s, demands d WHERE s.product_id = '" . $tostock['product_id'] . "' and s.demand_id = d.id and d.status < 5 order by d.id desc");

                while ($search = mysqli_fetch_array($search1)) {

                    $rozdil = $search['quantity'] - $search['reserved'];

                    if ($rozdil >= $instock) {

                        $mysqli->query("UPDATE demands_sauna_specs SET reserved = reserved + $instock WHERE id = '" . $search['id'] . "'");
                        $instock = 0;

                    } else {

                        $mysqli->query("UPDATE demands_sauna_specs SET reserved = reserved + $rozdil WHERE id = '" . $search['id'] . "'");
                        $instock = $instock - $rozdil;

                    }

                }

                $updateres = $instock;

                if ($updateres > 0) {

                    $search1 = $mysqli->query("SELECT b.id, b.reserved, b.quantity FROM orders_products_bridge b, orders o WHERE b.product_id = '" . $tostock['product_id'] . "' and b.order_id = o.id and o.status < 3 and b.reserved < b.quantity order by o.id desc");

                    while ($search = mysqli_fetch_array($search1)) {

                        $rozdil = $search['quantity'] - $search['reserved'];

                        if ($rozdil > $updateres || $rozdil == $updateres) {

                            $mysqli->query("UPDATE orders_products_bridge SET reserved = reserved + $updateres WHERE id = '" . $search['id'] . "'");
                            $updateres = 0;

                        } else {

                            $mysqli->query("UPDATE orders_products_bridge SET reserved = reserved + $rozdil WHERE id = '" . $search['id'] . "'");
                            $updateres = $updateres - $rozdil;

                        }
                    }
                }

                $mysqli->query("UPDATE products SET instock = instock + $updateres WHERE id = '" . $tostock['product_id'] . "'");
            }
        }
    }

    if (isset($_REQUEST['action']) && $_REQUEST['action'] == "products") {
        include_once CONTROLLERS . "/product-stock-controller.php";

        $get_specs = $mysqli->query("SELECT * FROM demands_specs_bridge b, specs s WHERE b.client_id = '" . $getclient['id'] . "' AND b.specs_id = s.id") or die($mysqli->error);
        while ($get_spec = mysqli_fetch_array($get_specs)) {
            $get_products = $mysqli->query("SELECT *, d.type as stock_type, d.id as dem_id FROM demands_products d, products p WHERE d.type = '" . $getclient['product'] . "' AND d.spec_id = '" . $get_spec['specs_id'] . "' AND d.product_id = p.id") or die($mysqli->error);

            while ($product = mysqli_fetch_array($get_products)) {
                $pid = $product['dem_id'];
                $value = $_POST[$pid];

                // check if exists

                $check_db = $mysqli->query("SELECT * FROM demands_products_bridge WHERE demand_id = '" . $getclient['id'] . "' AND spec_id = '" . $get_spec['specs_id'] . "' AND product_id = '" . $product['product_id'] . "' AND variation_id = '" . $product['variation_id'] . "'") or die($mysqli->error);

                if (mysqli_num_rows($check_db) > 0) {
                    $check = mysqli_fetch_array($check_db);

                    // was warehouse, is not now

                    if ($check['type'] == 'warehouse' && substr($value, 0, 9) != "warehouse") {

                        // NASKLADNĚNÍ A PŘIDÁNÍ K JINÝM OBJEDNÁVKÁM
                        product_update($product['product_id'], $product['variation_id'], $check['location_id'], '1', '0', 'demand_removed', $id);
                    } elseif ($check['type'] != 'warehouse' && substr($value, 0, 9) == "warehouse") {

                        // was not warehouse, is now

                        $warehouse_id = substr($value, 10);

                        $instock_query = $mysqli->query("SELECT instock FROM products_stocks WHERE product_id = '" . $product['product_id'] . "' AND variation_id = '" . $product['variation_id'] . "' AND location_id = '" . $warehouse_id . "'");

                        $instock = mysqli_fetch_array($instock_query);

                        if ($instock['instock'] > 0) {

                            $mysqli->query("UPDATE products_stocks SET instock = instock - 1 WHERE product_id = '" . $product['product_id'] . "' AND variation_id = '" . $product['variation_id'] . "' AND location_id = '" . $warehouse_id . "'") or die($mysqli->error);

                            $mysqli->query("UPDATE demands_products_bridge SET type = 'warehouse', type_id = '0', location_id = '" . $warehouse_id . "' WHERE demand_id = '" . $getclient['id'] . "' AND spec_id = '" . $get_spec['specs_id'] . "' AND product_id = '" . $product['product_id'] . "' AND variation_id = '" . $product['variation_id'] . "'") or die($mysqli->error);

                            api_product_update($product['product_id']);

                        } else {

                            $mysqli->query("UPDATE demands_products_bridge SET type = 'missing', type_id = '0', location_id = '" . $warehouse_id . "' WHERE demand_id = '" . $getclient['id'] . "' AND spec_id = '" . $get_spec['specs_id'] . "' AND product_id = '" . $product['product_id'] . "' AND variation_id = '" . $product['variation_id'] . "'") or die($mysqli->error);

                        }
                    } elseif ($check['type'] == 'warehouse' && substr($value, 0, 9) == "warehouse" && $check['type_id'] != substr($value, 10)) {

                        // NASKLADNĚNÍ A PŘIDÁNÍ K JINÝM OBJEDNÁVKÁM
                        product_update($product['product_id'], $product['variation_id'], $check['location_id'], '1', '0', 'demand_removed', $id);

                        $warehouse_id = substr($value, 10);

                        $instock_query = $mysqli->query("SELECT instock FROM products_stocks WHERE product_id = '" . $product['product_id'] . "' AND variation_id = '" . $product['variation_id'] . "' AND location_id = '" . $warehouse_id . "'");

                        $instock = mysqli_fetch_array($instock_query);

                        if ($instock['instock'] > 0) {

                            $mysqli->query("UPDATE products_stocks SET instock = instock - 1 WHERE product_id = '" . $product['product_id'] . "' AND variation_id = '" . $product['variation_id'] . "' AND location_id = '" . $warehouse_id . "'") or die($mysqli->error);

                            $mysqli->query("UPDATE demands_products_bridge SET type = 'warehouse', type_id = '0', location_id = '" . $warehouse_id . "' WHERE demand_id = '" . $getclient['id'] . "' AND spec_id = '" . $get_spec['specs_id'] . "' AND product_id = '" . $product['product_id'] . "' AND variation_id = '" . $product['variation_id'] . "'") or die($mysqli->error);

                            api_product_update($product['product_id']);

                        } else {

                            $mysqli->query("UPDATE demands_products_bridge SET type = 'missing', type_id = '0', location_id = '" . $warehouse_id . "' WHERE demand_id = '" . $getclient['id'] . "' AND spec_id = '" . $get_spec['specs_id'] . "' AND product_id = '" . $product['product_id'] . "' AND variation_id = '" . $product['variation_id'] . "'") or die($mysqli->error);

                        }
                    }

                    // was warehouse, is not now

                    if ($check['type'] == 'supply' && substr($value, 0, 6) != "supply") {

                        // DODÁVKY PŘIDÁNÍ K JINÝM OBJEDNÁVKÁM
                        product_delivered_single($product['product_id'], $product['variation_id'], '1', $check['type_id']);
                    } elseif ($check['type'] != 'supply' && substr($value, 0, 6) == "supply") {

                        // was not warehouse, is now

                        $supply_id = substr($value, 7);

                        $mysqli->query("UPDATE products_supply_bridge SET reserved = reserved + 1 WHERE product_id = '" . $product['product_id'] . "' AND variation_id = '" . $product['variation_id'] . "' AND supply_id = '" . $supply_id . "'") or die($mysqli->error);

                        $mysqli->query("UPDATE demands_products_bridge SET type = 'supply', type_id = '" . $supply_id . "', location_id = '0' WHERE demand_id = '" . $getclient['id'] . "' AND spec_id = '" . $get_spec['specs_id'] . "' AND product_id = '" . $product['product_id'] . "' AND variation_id = '" . $product['variation_id'] . "'") or die($mysqli->error);
                    } elseif ($check['type'] == 'supply' && substr($value, 0, 6) == "supply" && $check['type_id'] == substr($value, 7)) {

                        // DODÁVKY PŘIDÁNÍ K JINÝM OBJEDNÁVKÁM
                        product_delivered_single($product['product_id'], $product['variation_id'], '1', $check['type_id']);

                        $supply_id = substr($value, 7);

                        $mysqli->query("UPDATE products_supply_bridge SET reserved = reserved + 1 WHERE product_id = '" . $product['product_id'] . "' AND variation_id = '" . $product['variation_id'] . "' AND supply_id = '" . $supply_id . "'") or die($mysqli->error);

                        $mysqli->query("UPDATE demands_products_bridge SET type = 'supply', type_id = '" . $supply_id . "', location_id = '0' WHERE demand_id = '" . $getclient['id'] . "' AND spec_id = '" . $get_spec['specs_id'] . "' AND product_id = '" . $product['product_id'] . "' AND variation_id = '" . $product['variation_id'] . "'") or die($mysqli->error);
                    }

                    // is now hottub, was not before

                    if (substr($value, 0, 12) == "ware-product") {
                        $hottub_id = substr($value, 13);

                        $mysqli->query("UPDATE demands_products_bridge SET type = 'hottub', type_id = '" . $hottub_id . "' WHERE demand_id = '" . $getclient['id'] . "' AND spec_id = '" . $get_spec['specs_id'] . "' AND product_id = '" . $product['product_id'] . "' AND variation_id = '" . $product['variation_id'] . "'") or die($mysqli->error);
                    }
                } else {

                    // if doesnt exists

                    $mysqli->query("INSERT INTO demands_products_bridge (demand_id, spec_id, product_id, variation_id, type, type_id, quantity) VALUES ('" . $getclient['id'] . "', '" . $get_spec['specs_id'] . "', '" . $product['product_id'] . "', '" . $product['variation_id'] . "', '" . $type . "', '" . $value . "', '1')") or die($mysqli->error);

                    if (substr($value, 0, 9) == "warehouse") {

                        // is firstly warehouse

                        $warehouse_id = substr($value, 10);

                        $instock_query = $mysqli->query("SELECT instock FROM products_stocks WHERE product_id = '" . $product['product_id'] . "' AND variation_id = '" . $product['variation_id'] . "' AND location_id = '" . $warehouse_id . "'");

                        $instock = mysqli_fetch_array($instock_query);

                        if ($instock['instock'] > 0) {
                            $mysqli->query("UPDATE products_stocks SET instock = instock - 1 WHERE product_id = '" . $product['product_id'] . "' AND variation_id = '" . $product['variation_id'] . "' AND location_id = '" . $warehouse_id . "'") or die($mysqli->error);

                            $mysqli->query("UPDATE demands_products_bridge SET type = 'warehouse', type_id = '0', location_id = '" . $warehouse_id . "' WHERE demand_id = '" . $getclient['id'] . "' AND spec_id = '" . $get_spec['specs_id'] . "' AND product_id = '" . $product['product_id'] . "' AND variation_id = '" . $product['variation_id'] . "'") or die($mysqli->error);

                            api_product_update($product['product_id']);
                        } else {
                            $mysqli->query("UPDATE demands_products_bridge SET type = 'missing', type_id = '0', location_id = '" . $warehouse_id . "' WHERE demand_id = '" . $getclient['id'] . "' AND spec_id = '" . $get_spec['specs_id'] . "' AND product_id = '" . $product['product_id'] . "' AND variation_id = '" . $product['variation_id'] . "'") or die($mysqli->error);
                        }
                    } elseif (substr($value, 0, 6) == "supply") {

                        // is firstly supply

                        $supply_id = substr($value, 7);

                        $mysqli->query("UPDATE products_supply_bridge SET reserved = reserved + 1 WHERE product_id = '" . $product['product_id'] . "' AND variation_id = '" . $product['variation_id'] . "' AND supply_id = '" . $supply_id . "'") or die($mysqli->error);
                    } elseif (substr($value, 0, 12) == "ware-product") {

                        // is firstly another hottub
                        $hottub_id = substr($value, 13);

                        $mysqli->query("UPDATE demands_products_bridge SET type = 'hottub', type_id = '" . $hottub_id . "' WHERE demand_id = '" . $getclient['id'] . "' AND spec_id = '" . $get_spec['specs_id'] . "' AND product_id = '" . $product['product_id'] . "' AND variation_id = '" . $product['variation_id'] . "'") or die($mysqli->error);
                    }
                }
            }
        }

        if ($_REQUEST['redirect_id'] != '') {
            header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/zobrazit-virivku?id=' . $_REQUEST['redirect_id'] . '&contract=success');
        } else {
            header('location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $id . '&contract=success');
        }
    }

    $mysqli->query('DELETE FROM demands_sauna_specs WHERE demand_id="' . $_REQUEST['id'] . '"') or die("12Neexistuje");

    $mysqli->query('DELETE FROM demands WHERE id="' . $_REQUEST['id'] . '"') or die("4Neexistuje");
    $mysqli->query('DELETE FROM demands_timeline WHERE client_id="' . $_REQUEST['id'] . '"') or die("7Neexistuje");
    $mysqli->query('DELETE FROM demands_specs_bridge WHERE client_id="' . $_REQUEST['id'] . '"') or die("12Neexistuje");

    $selecttask = $mysqli->query("SELECT id FROM tasks WHERE demand_id = '" . $_REQUEST['id'] . "'");
    while ($taskid = mysqli_fetch_assoc($selecttask)) {

        $mysqli->query('DELETE FROM task_comments WHERE task_id="' . $taskid['id'] . '"') or die("4Neexistuje");
        $mysqli->query('DELETE FROM task_targets WHERE task_id="' . $taskid['id'] . '"') or die("4Neexistuje");

    }

    $mysqli->query('DELETE FROM demands_mails_history WHERE demand_id="' . $_REQUEST['id'] . '"') or die("4Neexistuje");

    $mysqli->query('DELETE FROM tasks WHERE demand_id="' . $_REQUEST['id'] . '"') or die("4Neexistuje");

    header('location: https://www.wellnesstrade.cz/admin/pages/demands/editace-poptavek?remove=success');
    exit;



}

// old to delete
//if (isset($_REQUEST['action']) && $_REQUEST['action'] == "add_to_container") {
//
////    if (isset($_POST['container']) && $_POST['container'] == 'new') {
////
////        $container_insert = $mysqli->query("INSERT INTO containers (customer, size, creator_id, editor_id, date_created) VALUES ('" . $getclient['customer'] . "', '14', '" . $client['id'] . "', '" . $client['id'] . "', CURRENT_TIMESTAMP())") or die($mysqli->error);
////
////        $container_id = $mysqli->insert_id;
////        $_POST['container'] = $container_id;
////    }
//
////    $container_products_insert = $mysqli->query("INSERT INTO containers_products (container_id, demand_id, customer, creator_id, editor_id, product, date_created)
////VALUES ('" . $_POST['container'] . "', '$id', '" . $getclient['customer'] . "','" . $client['id'] . "','" . $client['id'] . "', '" . $getclient['product'] . "', CURRENT_TIMESTAMP())") or die($mysqli->error);
////
////    $container_product_id = $mysqli->insert_id;
////
////    $specsquery = $mysqli->query("SELECT specs_id, value FROM demands_specs_bridge WHERE client_id = '$id'") or die($mysqli->error);
////    while ($specs = mysqli_fetch_array($specsquery)) {
////
////        if($specs['specs_id'] == '36' && $specs['value'] != 'IQue Ozonátor'){ $specs['value'] = ''; }
////
////        $insert_specs = $mysqli->query("INSERT INTO containers_products_specs_bridge (client_id, specs_id, value)
////VALUES ('$container_product_id', '" . $specs['specs_id'] . "', '" . $specs['value'] . "')") or die($mysqli->error);
////
////    }
////
////    header('location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $id . '&edit=success');
////    exit;
//}
// old to delete

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "payment_save") {

    $invoice_check = $mysqli->query("SELECT * FROM demands_advance_invoices WHERE id = '".$_REQUEST['advance_invoice_id']."'")or die($mysqli->error);
    $invoice = mysqli_fetch_assoc($invoice_check);

    if(!empty($_POST['date_payment'])){

        $mysqli->query("UPDATE demands_advance_invoices SET paid = 3, paid_value = total_price, payment_date = '" . $_POST['date_payment'] . "' WHERE id = '" . $_REQUEST['advance_invoice_id'] . "'") or die($mysqli->error);


    }elseif(!empty($_POST['problem_solved'])) {

        $mysqli->query('UPDATE demands_advance_invoices SET paid = 3, paid_value = total_price, additional_text = "' . $_POST['problem_solved'] . '" WHERE id="' . $_REQUEST['advance_invoice_id'] . '"') or die($mysqli->error);

    }else{

        $mysqli->query('UPDATE demands_advance_invoices SET paid = 3, paid_value = total_price, additional_text = "manuálně potvrzeno" WHERE id="' . $_REQUEST['advance_invoice_id'] . '"') or die($mysqli->error);

    }

    $mysqli->query('UPDATE demands SET contract = 3 WHERE id = "' . $_REQUEST['id'] . '"') or die($mysqli->error);


    if($_POST['paymentConfirmation'] == 1) {

        // get total price
        $price_query = $mysqli->query("SELECT i.id, i.total_price, i.currency, i.status, d.email, d.phone, d.product, d.customer FROM demands_advance_invoices i LEFT JOIN demands d ON d.id = i.demand_id WHERE i.id = '" . $_REQUEST['advance_invoice_id'] . "'") or die($mysqli->error);

        if(mysqli_num_rows($price_query) > 0){

            $price = mysqli_fetch_assoc($price_query);

            $total_price = $price['total_price'];
            $currency = currency($price['currency']);
            $email = $price['email'];

            if($price['status'] == 1){

                include($_SERVER['DOCUMENT_ROOT'] . '/admin/controllers/mails/client/firstPayment.php');

            }else{

                include($_SERVER['DOCUMENT_ROOT'] . '/admin/controllers/mails/client/recievedPayment.php');

            }

        }

    }


    if($invoice['payment_method'] == 'cash'){

        $balance_query = $mysqli->query("SELECT balance FROM cashier WHERE location_id = '".$_POST['location']."' ORDER BY id DESC limit 1")or die($mysqli->error);
        $balance = mysqli_fetch_assoc($balance_query);

        $description = 'Zálohová faktura ' . $invoice['id'];
        $next_balance = $balance['balance'] + $invoice['total_price'];

//        if($odd){
//            $income = 0; $outcome = $total['single'];
//        }else{
        $income = $invoice['total_price']; $outcome = 0;
//        }

        $mysqli->query("INSERT INTO cashier (
             date, 
             invoice_id, 
             var_sym, 
             description, 
             income, 
             outcome, 
             balance, 
             location_id,
             admin_id, 
             aggregate_type
         ) VALUES (
            CURRENT_TIMESTAMP(),
            '".$invoice['id']."',
            '".$invoice['id']."',
            '".$description."',
            '".$income."',
            '".$outcome."',
            '".$next_balance."',
            '".$_POST['location']."',
            '".$client['id']."',
            'demand'
        )")or die($mysqli->error);

    }

    header('location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $id . '&payment_save=success');
    exit;
}



if (isset($_REQUEST['realization']) && $_REQUEST['realization'] == "new") {

    if (isset($getclient['customer']) && $getclient['customer'] == 3 && $_POST['customer'] == 1) {

        $type = 'realization_hottub';

        $clientsquery = $mysqli->query("UPDATE demands SET confirmed = '" . $_POST['confirmed'] . "', realization = '" . $_POST['realizationdate'] . "', realizationtime = '" . $_POST['realizationtime'] . "', realtodate = '" . $_POST['realtodate'] . "', realtotime = '" . $_POST['realtotime'] . "', area = '" . $_POST['area'] . "'  WHERE id = '" . $getclient['id'] . "'") or die("bNeexistuje");

    } elseif (isset($getclient['customer']) && $getclient['customer'] == 3 && $_POST['customer'] == 0) {

        $type = 'realization_sauna';

        $secondRealQuery = $mysqli->query("SELECT * FROM demands_double_realization WHERE demand_id = '" . $getclient['id'] . "'");

        $gcalendars = mysqli_fetch_array($secondRealQuery);
        $gcalendar = $gcalendars['gcalendar'];

        if (mysqli_num_rows($secondRealQuery) > 0) {

            $update = $mysqli->query("UPDATE demands_double_realization SET confirmed = '" . $_POST['confirmed'] . "', startdate = '" . $_POST['realizationdate'] . "', starttime = '" . $_POST['realizationtime'] . "', enddate = '" . $_POST['realtodate'] . "', endtime = '" . $_POST['realtotime'] . "', area = '" . $_POST['area'] . "' WHERE demand_id = '" . $getclient['id'] . "'");

        } else {

            $insert = $mysqli->query("INSERT INTO demands_double_realization (confirmed, startdate, starttime, enddate, endtime, demand_id, area) VALUES ('" . $_POST['confirmed'] . "','" . $_POST['realizationdate'] . "', '" . $_POST['realizationtime'] . "', '" . $_POST['realtodate'] . "', '" . $_POST['realtotime'] . "', '" . $getclient['id'] . "', '" . $_POST['area'] . "')");

        }

    } else {

        if($_POST['customer'] == '0'){ $type = 'realization_sauna'; }else{ $type = 'realization_hottub'; }

        $mysqli->query("UPDATE demands SET confirmed = '" . $_POST['confirmed'] . "', realization = '" . $_POST['realizationdate'] . "', realizationtime = '" . $_POST['realizationtime'] . "', realtodate = '" . $_POST['realtodate'] . "', realtotime = '" . $_POST['realtotime'] . "', area = '" . $_POST['area'] . "'  WHERE id = '" . $getclient['id'] . "'") or die("bNeexistuje");

    }


    $mysqli->query("DELETE FROM mails_recievers WHERE type_id = '" . $getclient['id'] . "' AND type = '".$type."'") or die($mysqli->error);

    if(!empty(($_POST['performer']))){ $performersArray = array_filter($_POST['performer']); }else{ $performersArray[] = ''; }
    if(!empty(($_POST['observer']))){ $observersArray = array_filter($_POST['observer']); }else{ $observersArray[] = ''; }

    if (!empty($performersArray) || !empty($observersArray)) {

        recievers($performersArray, $observersArray, $type, $id);

    }

    saveCalendarEvent($id, 'realization');

    if (isset($_POST['send_email']) && $_POST['send_email'] == 'yes') {

        include CONTROLLERS . '/mails/realization.php';

    }

    if (isset($_REQUEST['redirect'])) {

        header('location: https://www.wellnesstrade.cz/admin/pages/demands/editace-poptavek?type=' . $_REQUEST['redirect'] . '&success=realadd');

    } else {

        header('location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $_REQUEST['id'] . '&success=realadd');

    }
    exit;
}

if (isset($_REQUEST['text']) && $_REQUEST['text'] == "remove") {

    $mysqli->query('DELETE FROM demands_timeline WHERE id="' . $_REQUEST['textid'] . '"') or die($mysqli->error);

    header('location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $_REQUEST['id'] . '&success=text_remove');
    exit;
}

if (isset($_REQUEST['upload']) && $_REQUEST['upload'] == "remove") {

    $path = $_SERVER['DOCUMENT_ROOT'] . "/data/clients/uploads/" . $getclient['secretstring'] . "/" . $_REQUEST['name'];

    if (file_exists($path)) {
        unlink($path);
    }

    header('location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $_REQUEST['id'] . '&success=remove');
    exit;
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "cancel") {

    $mysqli->query('UPDATE demands SET status = "6" WHERE id="' . $_REQUEST['id'] . '"') or die("4Neexistuje");

    header('location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $_REQUEST['id'] . '&success=cancel');
    exit;
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "restore") {

    $mysqli->query('UPDATE demands SET status = "1" WHERE id="' . $_REQUEST['id'] . '"') or die("4Neexistuje");

    header('location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $_REQUEST['id'] . '&success=restore');
    exit;
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "add") {

    if ($_POST['text'] != "") {

        $mysqli->query("INSERT INTO demands_timeline (client_id, admin_id, datetime, text) VALUES ('" . $getclient['id'] . "', '" . $client['id'] . "', CURRENT_TIMESTAMP(),'" . $_POST['text'] . "')") or die("4Neexistuje");

        header('location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $_REQUEST['id'] . '&success=text_add');
        exit;
    }

}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "addnote") {

    $username = $client['user_name'];

    $mysqli->query("INSERT INTO demands_notes (client_id, username, text) values ('" . $mysqli->real_escape_string($id) . "','" . $mysqli->real_escape_string($username) . "','" . $mysqli->real_escape_string($_POST['text']) . "')") or die($mysqli->error);

    header('location: https://' . $_SERVER['SERVER_NAME'] . '/admin/pages/demands/zobrazit-poptavku?id=' . $_REQUEST['id']);
    exit;
}
if (isset($_REQUEST['action']) && $_REQUEST['action'] == "removeservis") {

    $mysqli->query("DELETE FROM orders WHERE id = '" . $_REQUEST['servisid'] . "'") or die($mysqli->error);
    $mysqli->query("DELETE FROM orders_products_bridge WHERE aggregate_id = '" . $_REQUEST['servisid'] . "'  AND aggregate_type = 'order'") or die($mysqli->error);

    $displaysuccess = true;
    $successhlaska = "Objednávka byla úspěšně smazána.";
}


if (isset($_REQUEST['action']) && $_REQUEST['action'] == "follow-up-state") {

    $mysqli->query('UPDATE demands_mails_history SET state = "'.$_REQUEST['state'].'" WHERE id="' . $_REQUEST['follow_up_id'] . '"') or die($mysqli->error);

    $displaysuccess = true;
    $successhlaska = "Follow Up proveden.";

    if (!empty($_REQUEST['redirect_url'])) {

        $redirect_url = urldecode($_REQUEST['redirect_url']);

        header('location: https://www.wellnesstrade.cz/admin/' . $redirect_url);
        exit;

    }else{

        header('location: https://' . $_SERVER['SERVER_NAME'] . '/admin/pages/demands/zobrazit-poptavku?id=' . $_REQUEST['id']);
        exit;

    }

}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "follow-up-remove") {

    $dataQuery = $mysqli->query('SELECT gcalendar FROM demands_mails_history WHERE id = "' . $_REQUEST['follow_up_id'] . '"') or die($mysqli->error);
    $data = mysqli_fetch_assoc($dataQuery);

    $mysqli->query('DELETE FROM demands_mails_history WHERE id="' . $_REQUEST['follow_up_id'] . '"') or die($mysqli->error);

    calendarDelete($data['gcalendar']);

    $displaysuccess = true;
    $successhlaska = "Follow Up zničen.";

    header('location: https://' . $_SERVER['SERVER_NAME'] . '/admin/pages/demands/zobrazit-poptavku?id=' . $_REQUEST['id']);
    exit;
}


if (isset($_REQUEST['action']) && $_REQUEST['action'] == "follow-up") {

    $text = $mysqli->real_escape_string($_POST['text']);

    if (isset($_POST['notificationdate']) && $_POST['notificationdate'] == "choose") {

        $notificationdate = $_POST['chooseDate'];

    }else{

        $notificationdate = Date('Y-m-d', strtotime("+" . $_POST['notificationdate'] . " days"));

    }

    if(!empty($_POST['chooseTime'])){

        $finalDate = $notificationdate.' '.$_POST['chooseTime'];

    }else{

        $finalDate = $notificationdate.' 00:00:00';

    }

    $mysqli->query("INSERT INTO demands_mails_history (demand_id, type, text, datetime, admin_id, date_time) VALUES ('" . $_REQUEST['id'] . "', '" . $_POST['type'] . "','$text', current_timestamp(),'" . $client['id'] . "', '".$finalDate."')")or die($mysqli->error);

    $id = $mysqli->insert_id;

    if(!empty(($_POST['performer']))){ $performersArray = array_filter($_POST['performer']); }else{ $performersArray[] = ''; }
    if(!empty(($_POST['observer']))){ $observersArray = array_filter($_POST['observer']); }else{ $observersArray[] = ''; }

    if (!empty($performersArray) || !empty($observersArray)) {

        recievers($performersArray, $observersArray, 'follow_up', $id);

    }

    saveCalendarEvent($id, 'follow-up');

    header('location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $_REQUEST['id']);
    exit;
}


if (isset($_REQUEST['action']) && $_REQUEST['action'] == "follow-up-edit") {

    $text = $mysqli->real_escape_string($_POST['text']);

    if (isset($_POST['notificationdate']) && $_POST['notificationdate'] == "choose") {

        $notificationdate = $_POST['chooseDate'];

    }else{

        $notificationdate = Date('Y-m-d', strtotime("+" . $_POST['notificationdate'] . " days"));

    }

    if(!empty($_POST['chooseTime'])){

        $finalDate = $notificationdate.' '.$_POST['chooseTime'];

    }else{

        $finalDate = $notificationdate.' 00:00:00';

    }


    $mysqli->query("UPDATE demands_mails_history SET type = '".$_POST['type']."', text = '$text', admin_id = '" . $client['id'] . "', date_time = '".$finalDate."' WHERE id = '".$_REQUEST['follow_up_id']."'")or die($mysqli->error);
    
    $mysqli->query("DELETE FROM mails_recievers WHERE type_id = '" . $_REQUEST['follow_up_id'] . "' AND type = 'follow_up'") or die($mysqli->error);

    if(!empty(($_POST['performer']))){ $performersArray = array_filter($_POST['performer']); }else{ $performersArray[] = ''; }
    if(!empty(($_POST['observer']))){ $observersArray = array_filter($_POST['observer']); }else{ $observersArray[] = ''; }

    if (!empty($performersArray) || !empty($observersArray)) {

        recievers($performersArray, $observersArray, 'follow_up', $_REQUEST['follow_up_id']);

    }

    $id = $_REQUEST['follow_up_id'];
    saveCalendarEvent($id, 'follow-up');

    header('location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $_REQUEST['id']);
    exit;
}





if (isset($_REQUEST['success']) && $_REQUEST['success'] == "edit") {
    $displaysuccess = true;
    $successhlaska = "Poptávka byla úspěšně upravena.";
}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "cancel") {
    $displaysuccess = true;
    $successhlaska = "Poptávka byla úspěšně stornována.";
}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "restore") {
    $displaysuccess = true;
    $successhlaska = "Poptávka byla úspěšně obnovena.";
}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "specification") {
    $displaysuccess = true;
    $successhlaska = "Specifikace k poptávce byla úspěšně přidána.";
}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "comment_remove") {
    $displaysuccess = true;
    $successhlaska = "Komentář u úkolu byl úspěšně smazán.";
}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "comment_add") {
    $displaysuccess = true;
    $successhlaska = "Komentář k úkolu byl úspěšně přidán.";
}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "task_remove") {
    $displaysuccess = true;
    $successhlaska = "Úkol k poptávce byl úspěšně smazán.";
}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "task_add") {
    $displaysuccess = true;
    $successhlaska = "Úkol k poptávce byl úspěšně přidán.";
}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "task_edit") {
    $displaysuccess = true;
    $successhlaska = "Úkol k poptávce byl úspěšně upraven.";
}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "text_add") {
    $displaysuccess = true;
    $successhlaska = "Příspěvek k poptávce byl úspěšně přidán.";
}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "text_remove") {
    $displaysuccess = true;
    $successhlaska = "Příspěvek k poptávce byl úspěšně smazán.";
}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "task_change") {
    $displaysuccess = true;
    $successhlaska = "Status úkolu byl úspěšně upraven.";
}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "changestatus") {
    $displaysuccess = true;
    $successhlaska = "Status poptávky byl úspěšně upraven.";
}