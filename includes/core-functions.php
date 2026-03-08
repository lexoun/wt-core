<?php

// old to delete

function old_store_image_resize($path, $filename){

    /*  PRODUCT IMAGE RESOLUTIONS

     * 600x675 - CATALOG
     * 2000X2000 - BIG
     * 600x600  - single
     * 300x300 - THUMBNAIL
     * 120X120 - SMALL
     * 10X10 - MINI
     *
     */

    $blackColor = new ImagickPixel('rgb(0, 0, 0)');
    $whiteColor = new ImagickPixel('rgb(255, 255, 255)');
//    $wierdColor = new ImagickPixel('rgb(255, 0, 255)');
    $greyColor  = new ImagickPixel('rgb(249,249,249)');

    // Transfer of original image into new folder and tramforming to SRGB JPG
    $im = new Imagick($path);

    if ($im->getImageColorspace() == Imagick::COLORSPACE_CMYK) {
        $im->transformimagecolorspace(Imagick::COLORSPACE_SRGB);
    }

    $im->setImageBackgroundColor($whiteColor);
    $im = $im->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
    $im->setImageFormat('jpg');
    $im->writeImage($_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/original_uncolorized/'.$filename.'.jpg');

    // Creation of gray image
    list($w, $h) = array_values($im->getImageGeometry());

    if($w > 1400 || $h > 1400){
        if($w > $h){ $w = 1400; $h = 0; }else{ $w = 0; $h = 1400; }
        $im->scaleImage($w, $h);
    }

    list($w, $h) = array_values($im->getImageGeometry());

    $colorA = $im->getImagePixelColor(0, 0)->getColor();
    $colorB = $im->getImagePixelColor($w - 1, 0)->getColor();
    $colorC = $im->getImagePixelColor(0, $h - 1)->getColor();
    $colorD = $im->getImagePixelColor($w - 1, $h - 1)->getColor();

    $resultA = false; $resultB = false; $resultC = false; $resultD = false;

    if(($colorA['r'] >= '240' && $colorA['g'] >= '240' && $colorA['b'] >= '240') || ($colorA['r'] <= '15' && $colorA['g'] <= '15' && $colorA['b'] <= '15')){
        $resultA = true;
    }
    if(($colorB['r'] >= '240' && $colorB['g'] >= '240' && $colorB['b'] >= '240') || ($colorB['r'] <= '15' && $colorB['g'] <= '15' && $colorB['b'] <= '15')){
        $resultB = true;
    }
    if(($colorC['r'] >= '240' && $colorC['g'] >= '240' && $colorC['b'] >= '240') || ($colorC['r'] <= '15' && $colorC['g'] <= '15' && $colorC['b'] <= '15') ){
        $resultC = true;
    }
    if(($colorD['r'] >= '240' && $colorD['g'] >= '240' && $colorD['b'] >= '240') || ($colorD['r'] <= '15' && $colorD['g'] <= '15' && $colorD['b'] <= '15') ){
        $resultD = true;
    }

    // Pixels in every corner of image is  similiar either to white or black
    if($resultA && $resultB && $resultC && $resultD){

        $im->floodfillPaintImage($whiteColor, 3500, $blackColor, 0,      0,      false);
//        $im->floodfillPaintImage($whiteColor, 3500, $blackColor, $w - 1, 0,      false);
//        $im->floodfillPaintImage($whiteColor, 3500, $blackColor, 0,      $h - 1, false);
        $im->floodfillPaintImage($whiteColor, 3500, $blackColor, $w - 1, $h - 1, false);

//        $im->floodfillPaintImage($wierdColor, 2000, $whiteColor, 0,      0,      false);
////        $im->floodfillPaintImage($wierdColor, 3500, $whiteColor, $w - 1, 0,      false);
////        $im->floodfillPaintImage($wierdColor, 3500, $whiteColor, 0,      $h - 1, false);
//        $im->floodfillPaintImage($wierdColor, 2000, $whiteColor, $w - 1, $h - 1, false);
//
        $im->floodfillPaintImage($greyColor, 2000, $whiteColor, 0,      0,      false);
//        $im->floodfillPaintImage($greyColor, 10, $wierdColor, $w - 1, 0,      false);
//        $im->floodfillPaintImage($greyColor, 10, $wierdColor, 0,      $h - 1, false);
        $im->floodfillPaintImage($greyColor, 2000, $whiteColor, $w - 1, $h - 1, false);
//
//        $im->floodfillPaintImage($greyColor, 3500, $whiteColor, 0,      0,      false);
////        $im->floodfillPaintImage($greyColor, 10, $wierdColor, $w - 1, 0,      false);
////        $im->floodfillPaintImage($greyColor, 10, $wierdColor, 0,      $h - 1, false);
//        $im->floodfillPaintImage($greyColor, 3500, $whiteColor, $w - 1, $h - 1, false);

        $im->setImageBackgroundColor($greyColor);

        if($w > $h){
            $diff = abs($w-$h);
            $im->extentImage($w,$w, 0, -($diff/2));
        }elseif($h > $w){
            $diff = abs($h - $w);
            $im->extentImage($h, $h, -($diff/2), 0);
        }else{
            $im->extentImage($w, $h,($h-$w)/2,($h-$w)/2);
        }

    // Crop full-width or full-height image
    }else{
        if($w > $h){
            $im->cropThumbnailImage($h, $h);
        }else{
            $im->cropThumbnailImage($w, $w);
        }
    }

    // BIG SIZE - max 1400x1400
    $im->writeImage($_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/big/'.$filename.'.jpg');
    $finalImage = $_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/big/'.$filename.'.jpg';

    // 600x600
    $im->scaleImage(600,600,true);
    $im->writeImage($_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/single/'.$filename.'.jpg');

    $wnew = $im->getImageWidth();
    if($wnew < 600){ $im->destroy(); $im = new Imagick($finalImage); }

    // THUMBNAILS SIZE - 300x300
    $im->scaleImage(300,300);
    $im->writeImage($_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/thumbnail/'.$filename.'.jpg');

    // SMALL SIZE - 100x100
    $im->scaleImage(100,100);
    $im->writeImage($_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/small/'.$filename.'.jpg');

    // MINI - 20x20
    $im->scaleImage(20,20);
    $im->writeImage($_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/mini/'.$filename.'.jpg');
    $im->destroy();

    // CATALOG VIEW - 600x675
    $im = new Imagick($finalImage);
    $im->scaleImage(600,675,true);
    $im->writeImage($_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/catalog/'.$filename.'.jpg');
    $im->destroy();

}

// old to delete





function recieversShort($type, $id, $reciever_type) {

    global $mysqli;

    $recievers = '';

    $targetsQuery = $mysqli->query("SELECT c.user_name FROM mails_recievers t, demands c WHERE t.type_id = '" . $id . "' AND t.admin_id = c.id AND t.type = '".$type."' AND t.reciever_type = '".$reciever_type."'") or die($mysqli->error);

    while ($target = mysqli_fetch_assoc($targetsQuery)) {
        if (!empty($recievers)) { $recievers .= ' & ' . acronymRt($target['user_name']); } else { $recievers .= acronymRt($target['user_name']); }
    }

    return $recievers;

}



function phone_prefix($loc)
{

    if ($loc == 'CZE') {
        return '+420';
    } elseif ($loc == 'SVK') {
        return '+421';
    } elseif ($loc == 'AUT') {
        return '+43';
    } elseif ($loc == 'DEU') {
        return '+49';
    }

}



// Prices - invoices generation and orders

function vat_coeficient($vat){

    if (isset($vat) && $vat == '21') {

        $coeficient = '1.21';

    } elseif (isset($vat) && $vat == '15') {

        $coeficient = '1.15';

    } elseif (isset($vat) && $vat == '12') {

        $coeficient = '1.12';

    } elseif (isset($vat) && $vat == '10') {

        $coeficient = '1.1';

    } elseif (isset($vat) && $vat == '0') {

        $coeficient = '1';

    }

    return $coeficient;
}


function get_price($price, $coeficient) {

    $data = array();

    $data['single'] = number_format($price, 2,'.','');
    $data['vat'] = number_format($price - ($price / $coeficient), 2,'.','');
    $data['without_vat'] = number_format($price - $data['vat'], 2,'.','');

    return $data;
}


function thousand_seperator($value){

    // todo... když je 10.117 ... tak se to zaokrouhlí na 10.12, ale je potřeba, aby to bylo 10.11...
   return number_format($value, 2, ',', ' ');

}


function currency($currency_code){

    // Default CZ
    $currency['sign'] = ' Kč';
    $currency['code'] = 'CZK';
    $currency['bank_account'] = '2000364217/2010';
    $currency['iban'] = 'CZ8320100000002000364217';

    if($currency_code == 'EUR'){

        $currency['sign'] = ' €';
        $currency['code'] = 'EUR';
        $currency['bank_account'] = '2300737564/2010';
        $currency['iban'] = 'CZ8620100000002300737564';

    }elseif($currency_code == 'USD'){

        $currency['sign'] = ' $';
        $currency['code'] = 'USD';
        $currency['bank_account'] = '2400364240/2010';
        $currency['iban'] = 'CZ2620100000002400364240';

    }

    return $currency;
}


function currency_eshop($currency_code){

    // Default CZ
    $currency['sign'] = ' Kč';
    $currency['bank_account'] = '2700610079/2010';
    $currency['iban'] = 'CZ2120100000002700610079';

    if($currency_code == 'EUR'){

        $currency['sign'] = ' €';
        $currency['bank_account'] = '2300737564/2010';
        $currency['iban'] = 'CZ8620100000002300737564';

    }elseif($currency_code == 'USD'){

        $currency['sign'] = ' $';
        $currency['bank_account'] = '2400364240/2010';
        $currency['iban'] = 'CZ2620100000002400364240';

    }

    return $currency;
}

function store_image_resize($path, $filename){

    /*  PRODUCT IMAGE RESOLUTIONS
     *
     *  - original uncolorized        unchanged
     *  - big                         1200x1200
     *  - single - shop_single          700×800
     *  - catalog - shop_catalog        420×480
     *  - thumbnail - shop_thumbnail    210×240
     *  - mini:                           21×24
     *
     */

    $blackColor = new ImagickPixel('rgb(0, 0, 0)');
    $whiteColor = new ImagickPixel('rgb(255, 255, 255)');
    $greyColor  = new ImagickPixel('rgb(249,249,249)');

    // Transfer of original image into new folder and tramforming to SRGB JPG
    $im = new Imagick($path);

    if ($im->getImageColorspace() == Imagick::COLORSPACE_CMYK) {
        $im->transformimagecolorspace(Imagick::COLORSPACE_SRGB);
    }

    $im->setImageBackgroundColor($whiteColor);
    $im = $im->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
    $im->setImageFormat('jpg');
    $im->writeImage($_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/original_uncolorized/'.$filename.'.jpg');
    $finalImage = $_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/original_uncolorized/'.$filename.'.jpg';

    // Creation of gray image
    list($w, $h) = array_values($im->getImageGeometry());

    if($w > 1200 || $h > 1200){
        if($w > $h){ $w = 1200; $h = 0; }else{ $w = 0; $h = 1200; }
        $im->scaleImage($w, $h);
    }


    if($w < 500 || $h < 500){
        if($w > $h){ $w = 500; $h = 0; }else{ $w = 0; $h = 500; }
        $im->scaleImage($w, $h);
    }

    list($w, $h) = array_values($im->getImageGeometry());

    $colorA = $im->getImagePixelColor(0, 0)->getColor();
    $colorB = $im->getImagePixelColor($w - 1, 0)->getColor();
    $colorC = $im->getImagePixelColor(0, $h - 1)->getColor();
    $colorD = $im->getImagePixelColor($w - 1, $h - 1)->getColor();

    $resultA = false; $resultB = false; $resultC = false; $resultD = false;

    if(($colorA['r'] >= '240' && $colorA['g'] >= '240' && $colorA['b'] >= '240') || ($colorA['r'] <= '15' && $colorA['g'] <= '15' && $colorA['b'] <= '15')){
        $resultA = true;
    }
    if(($colorB['r'] >= '240' && $colorB['g'] >= '240' && $colorB['b'] >= '240') || ($colorB['r'] <= '15' && $colorB['g'] <= '15' && $colorB['b'] <= '15')){
        $resultB = true;
    }
    if(($colorC['r'] >= '240' && $colorC['g'] >= '240' && $colorC['b'] >= '240') || ($colorC['r'] <= '15' && $colorC['g'] <= '15' && $colorC['b'] <= '15') ){
        $resultC = true;
    }
    if(($colorD['r'] >= '240' && $colorD['g'] >= '240' && $colorD['b'] >= '240') || ($colorD['r'] <= '15' && $colorD['g'] <= '15' && $colorD['b'] <= '15') ){
        $resultD = true;
    }

    // Pixels in every corner of image is  similiar either to white or black
    if($resultA && $resultB && $resultC && $resultD){

        $im->floodfillPaintImage($whiteColor, 3500, $blackColor, 0,      0,      false);
        $im->floodfillPaintImage($whiteColor, 3500, $blackColor, $w - 1, $h - 1, false);

        $im->floodfillPaintImage($greyColor, 2000, $whiteColor, 0,      0,      false);
        $im->floodfillPaintImage($greyColor, 2000, $whiteColor, $w - 1, $h - 1, false);

        $im->setImageBackgroundColor($greyColor);

        if($w > $h){

            $diff = abs($w-$h);
            $im->extentImage($w,$w, 0, -($diff/2));

        }elseif($h > $w){

            $diff = abs($h - $w);
            $im->extentImage($h, $h, -($diff/2), 0);

        }else{

            $im->extentImage($w, $h,($h-$w)/2,($h-$w)/2);

        }

        // BIG SIZE - max 1200x1200
        $im->writeImage($_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/big/'.$filename.'.jpg');
        $finalImage = $_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/big/'.$filename.'.jpg';

        list($w, $h) = array_values($im->getImageGeometry());

        $ratioOriginal = 800/1200;

        if ($w/$h > $ratioOriginal) {

            $w = $h * $ratioOriginal;

        } else {

            $h = $w / $ratioOriginal;

        }

        // SINGLE - shop_single: 700×800
        $im->scaleImage(700,800,true);
        $im->setImageBackgroundColor($greyColor);

        $w = $im->getImageWidth();
        $h = $im->getImageHeight();
        $im->extentImage(700,800,($w-700)/2,($h-800)/2);


    // Crop full-width or full-height image
    }else{

        if($w > $h){
            $im->cropThumbnailImage($h, $h);
        }else{
            $im->cropThumbnailImage($w, $w);
        }

        // BIG SIZE - max 1200x1200
        $im->writeImage($_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/big/'.$filename.'.jpg');

        // SINGLE - shop_single: 700×800
        $im->destroy();
        $im = new Imagick($finalImage);

        $im->cropThumbnailImage(700, 800);
        $finalImage = $_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/single/'.$filename.'.jpg';

    }

    // SINGLE - shop_single: 700×800
    $im->writeImage($_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/single/'.$filename.'.jpg');

    // CATALOG - shop_catalog: 420×480
    $im->scaleImage(420,480,true);
    $im->writeImage($_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/catalog/'.$filename.'.jpg');

    // THUMBNAIL - shop_thumbnail: 210×240
    $im->scaleImage(210,240,true);
    $im->writeImage($_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/thumbnail/'.$filename.'.jpg');

    // THUMBNAIL - shop_thumbnail: 105×120
    $im->scaleImage(105,120,true);
    $im->writeImage($_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/small/'.$filename.'.jpg');

    // MINI - 21×24
    $im->scaleImage(21,24,true);
    $im->writeImage($_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/mini/'.$filename.'.jpg');
    $im->destroy();

}

// only quantity update
function api_product_update($product_id)
{
    foreach(getProductSites($product_id) as $site){

        saveProductEvent($product_id, 'product', 'quantity', $site['site']);

    }
}


function api_product_remove($id, $store)
{
    global $mysqli;

    // todo remove from eshop webhook

}



function getAddressName($address){

    if ($address['shipping_name'] != '' || $address['shipping_surname'] != '') {

        $user_name = $address['shipping_name'] . ' ' . $address['shipping_surname'];

    } elseif ($address['billing_name'] != '' || $address['billing_surname'] != '') {

        $user_name = $address['billing_name'] . ' ' . $address['billing_surname'];

    } elseif ($address['billing_company'] != '') {

        $user_name = $address['billing_company'];

    } else {

        $user_name = $address['shipping_company'];

    }

    return $user_name;

}



function date_formatted($date){

    return date("d. m. Y", strtotime($date));

}

function datetime_formatted($date){

    return date("d. m. Y H:i", strtotime($date));

}



function payment_status($data){

    $currency = currency($data['currency']);

    if(empty($data['paid'])){

        $status = '<span style="color: #d42020; font-size: 13px;"><i class="entypo-cc-nc"></i> nezaplaceno</span>';

    }elseif($data['paid'] == 1){

        $status = '<span style="color: #00a651; font-size: 13px;"><i class="entypo-check"></i> zaplaceno</span>';

    }elseif($data['paid'] == 2){

        $status = '<span style="color: #d42020; font-size: 13px;"><i class="entypo-block"></i> <strong>problém: '.thousand_seperator($data['paid_value'] - $data['total_price']).$currency['sign'].'</strong></strong>';

    }elseif($data['paid'] == 3){

        $status = '<span style="color: #00a651; font-size: 13px;"><i class="entypo-check"></i> zaplaceno</span>';

    }

    return $status;

}


function check_payment($data, $type = 'order'){

    global $mysqli;

    // $data contains
    //
    // order/demand: target_id, location_id
    // both: payment_date, currency, date, payment_method, total_price


        $currency = currency($data['currency']);

        $interval_start = date('Y-m-d', strtotime($data['date'].' - 60 days'));
        $interval_end = date('Y-m-d', strtotime($data['date'].' + 60 days'));


        $payment['paid'] = false;
        if (isset($data['payment_method']) && $data['payment_method'] == 'cash') {

            $cashier_query = $mysqli->query("SELECT id, date FROM cashier WHERE location_id = '".$data['location_id']."' AND invoice_id = '".$data['id']."' AND var_sym = '".$data['target_id']."'")or die($mysqli->error);

            $cashier = mysqli_fetch_assoc($cashier_query);

            if (mysqli_num_rows($cashier_query) > 0) {

                $payment['paid'] = true;

                $payment['date'] = $cashier['date'];

                $payment['info'] = '<i class="entypo-check"></i> zaplaceno</span> ~ <span style="color: #ff9600"><i class="fa fa-money"></i> hotově</span>';
                $payment['color'] = 'color: #00a651';

            } elseif(!empty($data['payment_date']) && $data['payment_date'] != '0000-00-00' && $data['payment_date'] != '0000-00-00 00:00:00') {

                $payment['paid'] = true;

                $payment['date'] = $data['payment_date'];

                $payment['info'] = '<i class="entypo-check"></i> zaplaceno</span> ~ <span style="color: #ff9600"><i class="fa fa-money"></i> hotově</span>';
                $payment['color'] = 'color: #00a651';

            }else{

                $payment['info'] = '<i class="entypo-cc-nc"></i> nezaplaceno</span> ~ <span style="color: #ff9600"><i class="fa fa-money"></i> hotově</span>';
                $payment['color'] = 'color: #d42020';

            }

        // bank
        } elseif($data['payment_method'] == 'bacs' || $data['payment_method'] == 'cod' || $data['payment_method'] == 'bankwire'){

            $interval = '';
            if($type == 'order'){
                $interval = "(date BETWEEN '".$interval_start."' AND '".$interval_end."') AND ";
            }

            // // old removed $type
//            $bank_sum_query = $mysqli->query("SELECT SUM(value) as total FROM bank_transactions WHERE account = '".$type."' AND $interval (vs = '".$data['id']."' OR manual_assign = '".$data['id']."' OR vs = '".$data['target_id']."')")or die($mysqli->error);

            $bank_sum_query = $mysqli->query("SELECT SUM(value) as total FROM bank_transactions WHERE $interval (vs = '".$data['id']."' OR manual_assign = '".$data['id']."' OR vs = '".$data['target_id']."')")or die($mysqli->error);

            $bank_sum = mysqli_fetch_assoc($bank_sum_query);

            if (isset($bank_sum['total']) && $bank_sum['total'] != '0') {

                if ($bank_sum['total'] == $data['total_price'] || $data['paid'] == 3) {

                    $payment['date'] = date('Y-m-d');

                    $payment['paid'] = true;

                    $payment['info'] = '<i class="entypo-check"></i> zaplaceno';
                    $payment['color'] = 'color: #00a651';

                } else {

                    $payment['date'] = date('Y-m-d');

                    $payment['info'] = '<i class="entypo-block"></i> <strong>problém: '.thousand_seperator($bank_sum['total'] - $data['total_price']).$currency['sign'].'</strong>';
                    $payment['color'] = 'color: #d42020;';

                    if($type == 'demand' && empty($data['paid'])){

                        $mysqli->query("UPDATE demands_advance_invoices SET paid = 2, paid_value = '".$bank_sum['total']."', payment_date = '".$payment['date']."' WHERE id = '".$data['id']."'")or die($mysqli->error);

                    }elseif($type == 'order' && empty($data['paid'])){

                       if(isset($data['type']) && $data['type'] == 'order'){ $dbName = 'orders'; }elseif(isset($data['type']) && $data['type'] == 'service'){ $dbName = 'services'; }

                       if(!empty($dbName)){

                            $mysqli->query("UPDATE $dbName SET paid = 2, paid_value = '".$bank_sum['total']."', payment_date = '".$payment['date']."' WHERE id = '".$data['target_id']."'")or die($mysqli->error);

                       }

                    }

                }

            } elseif(!empty($data['payment_date']) && $data['payment_date'] != '0000-00-00' && $data['payment_date'] != '0000-00-00 00:00:00') {

                $payment['date'] = $data['payment_date'];

                $payment['paid'] = true;

                $payment['info'] = '<i class="entypo-check"></i> manuálně zaplaceno';
                $payment['color'] = 'color: #006dcc;';

            }else{

                $payment['info'] = '<i class="entypo-cc-nc"></i> nezaplaceno';
                $payment['color'] = 'color: #d42020;';

            }

        }elseif($data['payment_method'] == 'agmobindercardall' || $data['payment_method'] == 'agmobinderbank' || $data['payment_method'] == 'card'){

            // check comgate
// old removed $type
//            $comgate_query = $mysqli->query("SELECT * FROM transactions_comgate WHERE target_id = '".$data['target_id']."' AND target_type = '".$type."'")or die($mysqli->error);
            $comgate_query = $mysqli->query("SELECT * FROM transactions_comgate WHERE target_id = '".$data['target_id']."'")or die($mysqli->error);

            if(mysqli_num_rows($comgate_query) > 0){
                $comgate = mysqli_fetch_assoc($comgate_query);


                if ($comgate['status'] == 'PAID' && $comgate['value'] == $data['total_price']) {

                    $payment['date'] = $comgate['datetime'];

                    $payment['paid'] = true;

                    $payment['info'] = '<i class="entypo-check"></i> comgate: zaplaceno';
                    $payment['color'] = 'color: #00a651';

                } elseif ($comgate['status'] == 'PAID' && $comgate['value'] != $data['total_price']) {

                    $payment['info'] = '<i class="entypo-block"></i>comgate: <strong>problém: '. thousand_seperator($comgate['value'] - $data['total_price']).$currency['sign'].'</strong>';
                    $payment['color'] = 'color: #d42020;';

                } elseif ($comgate['status'] == 'PENDING') {

                    $payment['info'] = '<i class="entypo-back-in-time"></i>comgate: čeká na platbu';
                    $payment['color'] = 'color: #ff9600;';

                } elseif ($comgate['status'] == 'CANCELLED') {

                    $payment['info'] = '<i class="entypo-trash"></i>comgate: stornovaná';
                    $payment['color'] = 'color: #000;';

                }else{

                    $payment['info'] = '<i class="entypo-cc-nc"></i> nezaplaceno';
                    $payment['color'] = 'color: #d42020;';

                }

            }else{

                $payment['info'] = '<i class="entypo-cc-nc"></i> nezaplaceno';
                $payment['color'] = 'color: #d42020;';

            }

        }else{

            $payment['info'] = '<i class="entypo-cc-nc"></i> nezaplaceno';
            $payment['color'] = 'color: #d42020;';

        }


        if($payment['paid']){

            if($type == 'demand' && $data['paid'] != 3){

                $mysqli->query("UPDATE demands_advance_invoices SET paid = 1, paid_value = total_price, payment_date = '".$payment['date']."' WHERE id = '".$data['id']."'")or die($mysqli->error);

                $mysqli->query('UPDATE demands SET contract = 3 WHERE id = "' . $data['demand_id'] . '"') or die($mysqli->error);

            }elseif($type == 'order' && $data['paid'] != 3){

               if(isset($data['type']) && $data['type'] == 'order'){

                   $mysqli->query("UPDATE orders SET paid = 1, paid_value = total, payment_date = '".$payment['date']."' WHERE id = '".$data['target_id']."'")or die($mysqli->error);

               }elseif(isset($data['type']) && $data['type'] == 'service'){

                   $mysqli->query("UPDATE services SET paid = 1, paid_value = price, payment_date = '".$payment['date']."' WHERE id = '".$data['target_id']."'")or die($mysqli->error);

                }
            }
        }

    return $payment;

}

function send_mail($mail, $reciever_id = 0){

    global $client;
    global $mysqli;

    if (!$mail->send()) {

        echo 'Message could not be sent.';
        echo 'Mailer Error: ' . $mail->ErrorInfo;

    }else{

       $content = $mysqli->real_escape_string($mail->Body);
       $subject = $mysqli->real_escape_string($mail->Subject);

       if(empty($client)){ $client['id'] = 0; }

       $mysqli->query("INSERT INTO mails_archive (reciever_id, subject, content, datetime, admin_id) VALUES ('".$reciever_id."', '".$subject."',  '".$content."', CURRENT_TIMESTAMP() , '".$client['id']."')")or die($mysqli->error);

    }

}



function strong_echo($data){

    if(!empty($data) && $data != '0.00'){

        echo '<strong>'.$data.'</strong>';

    }else{

        echo '-';

    }

}


function isset_echo($data){

    if(!empty($data)){ echo $data; }

}

function billing_address($address = ''){

 ?>
<div class="form-group">
    <label for="field-2" class="col-sm-2 control-label">IČO</label>

    <div class="col-sm-4">
        <input type="text" name="billing_ico" class="form-control" id="billing_ico" value="<?php if(isset($address['billing_ico']) && $address['billing_ico'] !== 0 && !empty($address['billing_ico'])){ echo $address['billing_ico']; }?>" style="float: left; width: 75%;">
        <a class="ares-load btn-md btn btn-primary" style="float: right; width: 20%; padding: 6px;"><i class="entypo-download"></i></a>
    </div>

    <label class="col-sm-1 control-label">DIČ</label>
    <div class="col-sm-4">
        <input type="text" name="billing_dic" class="form-control" id="field-2" value="<?= $address ? $address['billing_dic'] : '' ?>">
    </div>
</div>

<div class="form-group">
    <label for="field-1" class="col-sm-3 control-label">Firma</label>
    <div class="col-sm-8">
        <input type="text" name="billing_company" class="form-control" value="<?= $address ? $address['billing_company'] : '' ?>">
    </div>
</div>

<div class="form-group">
    <label for="field-1" class="col-sm-3 control-label">Jméno</label>	<div class="col-sm-6">
        <input type="text" name="billing_name" class="form-control" id="field-1" placeholder="Jméno" value="<?= $address ? $address['billing_name'] : '' ?>">
    </div>
</div>

<div class="form-group">
    <label for="field-1" class="col-sm-3 control-label">Příjmení</label>	<div class="col-sm-6">
        <input type="text" name="billing_surname" class="form-control" id="field-1" placeholder="Příjmení" value="<?= $address ? $address['billing_surname'] : '' ?>">
    </div>
</div>

<div class="form-group">
    <label for="field-2" class="col-sm-3 control-label">Telefonní číslo</label>	<div class="col-sm-6">
        <input type="text" name="billing_phone" class="form-control" id="field-2" placeholder="Telefonní číslo" value="<?= $address ? $address['billing_phone'] : '' ?>">
    </div>
</div>

<div class="form-group">
    <label for="field-2" class="col-sm-3 control-label">Email</label>

    <div class="col-sm-6">
        <input type="text" name="billing_email" class="form-control" id="field-2" placeholder="Email" value="<?= $address ? $address['billing_email'] : '' ?>">
    </div>
</div>


<div class="form-group">
    <label for="field-3" class="col-sm-3 control-label">Ulice</label>
    <div class="col-sm-6">
        <input type="text" name="billing_street" class="form-control" id="field-3" placeholder="Ulice" value="<?= $address ? $address['billing_street'] : '' ?>">
    </div>
</div>

<div class="form-group">
    <label for="field-6" class="col-sm-3 control-label">Město</label>
    <div class="col-sm-6">
        <input type="text" name="billing_city" class="form-control" id="field-4" placeholder="Město" value="<?= $address ? $address['billing_city'] : '' ?>">
    </div>
</div>

<div class="form-group">
    <label for="field-6" class="col-sm-3 control-label">PSČ</label>
    <div class="col-sm-6">
        <input type="text" name="billing_zipcode" class="form-control" id="field-5" placeholder="PSČ" value="<?= $address ? $address['billing_zipcode'] : '' ?>">
    </div>
</div>


<div class="form-group">
    <label class="col-sm-3 control-label">Země</label>
    <div class="col-sm-6">
        <select name="billing_country" class="selectboxit billing_country">
            <option value="CZ" <?php if (isset($address['billing_country']) && $address['billing_country'] == 'CZ') {echo 'selected';}?>>Česká republika</option>
            <option value="SK" <?php if (isset($address['billing_country']) && $address['billing_country'] == 'SK') {echo 'selected';}?>>Slovensko</option>
            <option value="DE" <?php if (isset($address['billing_country']) && $address['billing_country'] == 'DE') {echo 'selected';}?>>Německo</option>
            <option value="AT" <?php if (isset($address['billing_country']) && $address['billing_country'] == 'AT') {echo 'selected';}?>>Rakousko</option>
            <option value="PL" <?php if (isset($address['billing_country']) && $address['billing_country'] == 'PL') {echo 'selected';}?>>Polsko</option>
            <option value="UK" <?php if (isset($address['billing_country']) && $address['billing_country'] == 'UK') {echo 'selected';}?>>Velká Británie</option>
        </select>

    </div>
</div>
<?php

}





function shipping_address($address = ''){

    if (!empty($address['shipping_name']) || !empty($address['shipping_company']) || !empty($address['shipping_street']) || !empty($address['shipping_city'])) {

        $is_different = true;

    } else {

        $is_different = false;

    }

    ?>

    <script type="text/javascript">
        jQuery(document).ready(function($){
            $('.different_shipping_address').on('switch-change', function () {

                if($('.different_shipping').prop('checked')){

                    $('.shipping').show("slow");

                }else if(!$('.different_shipping').prop('checked')){

                    $('.shipping').hide("slow");

                }

            });
        });
    </script>

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-8">
            <div class="different_shipping_address make-switch switch-small" style="float: left; margin-right:11px; margin-top: 2px;" data-on-label="<i class='entypo-check'></i>" data-off-label="<i class='entypo-cancel'></i>">
                <input class="radiodegree different_shipping" name="different_shipping" id="nah" value="yes" type="checkbox" <?php if ($is_different) {echo 'checked';}?>/>
            </div>
            <label for="nah" style="line-height: 26px; margin-left: 4px; cursor: pointer; font-style: italic; font-size: 14px; ">odlišné doručovací údaje</label>
        </div>
    </div>


<div class="panel panel-primary shipping" <?php if (!$is_different) { ?>style="display: none;"<?php } ?> data-collapsed="0">

    <div class="panel-heading">
        <div class="panel-title">
            <strong style="font-weight: 600;">Doručovací údaje</strong>
        </div>
    </div>

    <div class="panel-body">

        <div class="form-group">
            <label for="field-1" class="col-sm-3 control-label">Firma</label>

            <div class="col-sm-8">
                <input type="text" name="shipping_company" class="form-control" value="<?= $address ? $address['shipping_company'] : '' ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="field-1" class="col-sm-3 control-label">Jméno</label>
            <div class="col-sm-6">
                <input type="text" name="shipping_name" class="form-control" id="field-1" placeholder="Jméno" value="<?= $address ? $address['shipping_name'] : '' ?>">
            </div>

        </div>
        <div class="form-group">
            <label for="field-1" class="col-sm-3 control-label">Příjmení</label>	<div class="col-sm-6">
                <input type="text" name="shipping_surname" class="form-control" id="field-1" placeholder="Příjmení" value="<?= $address ? $address['shipping_surname'] : '' ?>">
            </div>

        </div>

        <div class="form-group">
            <label for="field-3" class="col-sm-3 control-label">Ulice</label>
            <div class="col-sm-6">
                <input type="text" name="shipping_street" class="form-control" id="field-3" placeholder="Ulice" value="<?= $address ? $address['shipping_street'] : '' ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="field-6" class="col-sm-3 control-label">Město</label>
            <div class="col-sm-6">
                <input type="text" name="shipping_city" class="form-control" id="field-4" placeholder="Město" value="<?= $address ? $address['shipping_city'] : '' ?>">
            </div>

        </div>

        <div class="form-group">
            <label for="field-6" class="col-sm-3 control-label">PSČ</label>
            <div class="col-sm-6">
                <input type="text" name="shipping_zipcode" class="form-control" id="field-5" placeholder="PSČ" value="<?= $address ? $address['shipping_zipcode'] : '' ?>">
            </div>
        </div>

        <div class="form-group shipping_country_group">
            <label class="col-sm-3 control-label">Země</label>
            <div class="col-sm-6">
                <select name="shipping_country" class="selectboxit shipping_country">
                    <option value="CZ" <?php if (isset($address['shipping_country']) && $address['shipping_country'] == 'CZ') {echo 'selected';}?>>Česká republika</option>
                    <option value="SK" <?php if (isset($address['shipping_country']) && $address['shipping_country'] == 'SK') {echo 'selected';}?>>Slovensko</option>
                    <option value="DE" <?php if (isset($address['shipping_country']) && $address['shipping_country'] == 'DE') {echo 'selected';}?>>Německo</option>
                    <option value="AT" <?php if (isset($address['shipping_country']) && $address['shipping_country'] == 'AT') {echo 'selected';}?>>Rakousko</option>
                    <option value="PL" <?php if (isset($address['shipping_country']) && $address['shipping_country'] == 'PL') {echo 'selected';}?>>Polsko</option>
                </select>
            </div>
        </div>
    </div>
</div>
<?php

}

function getCategoryProducts($category){

    global $mysqli;

    $resultArray = $mysqli->query("SELECT connect_name FROM warehouse_products WHERE category = '".$category."' ORDER BY brand");

    $products = [];
    while($result = mysqli_fetch_assoc($resultArray)){
        array_push($products, $result['connect_name']);
    }

    return $products;

}


function hottubSalePrice($demand_id){

    global $mysqli;

    $data_hottub_query = $mysqli->query("SELECT * FROM demands_generate_hottub WHERE id = '$demand_id'");
    $data_hottub = mysqli_fetch_array($data_hottub_query);

    $total_price = $data_hottub['price_hottub'];

    $specs_demand = $mysqli->query("SELECT *, s.id as id FROM specs s, demands_specs_bridge d WHERE d.specs_id = s.id AND d.value != '' AND d.value != 'Ne' AND d.client_id = '" . $demand_id . "' AND s.generate = 1 ORDER BY s.demand_order") or die($mysqli->error);

    while ($spec = mysqli_fetch_array($specs_demand)) {
        $total_price += $spec['price'];
    }

    $accessories_query = $mysqli->query("SELECT * FROM demands_accessories_bridge WHERE aggregate_id = '" . $demand_id . "'") or die($mysqli->error);
    while ($accessory = mysqli_fetch_array($accessories_query)) {
        $total_price += $accessory['price'] * $accessory['quantity'];
    }


    if($data_hottub['chemie_type'] == 0){ $data_hottub['price_chemie'] = 0; }

    $total_price += $data_hottub['price_delivery'] + $data_hottub['price_montage'] + $data_hottub['price_chemie'] - $data_hottub['discount'];

    return $total_price;

}