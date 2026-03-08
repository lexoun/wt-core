<?php
include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";


// Připojení ke kalendáři
function calendarConnect($editor_id = 2126){

    global $client;

    $gclient = new Google_Client();
    $gclient->setSubject('kcie94kfi9absq10j3d8uijis8@group.calendar.google.com');
    $gclient->setAuthConfigFile($_SERVER['DOCUMENT_ROOT'] . '/admin/config/client_secret.json');
    $gclient->setRedirectUri('https://' . $_SERVER['HTTP_HOST'] . '/admin/');
    //$gclient->setRedirectUri('https://' . $_SERVER['HTTP_HOST'] . '/admin/controllers/oauth-controller.php');
    $gclient->setAccessType('offline');
    $gclient->setApprovalPrompt('force');
    $gclient->setApplicationName("Wellness Trade");
    $gclient->addScope("https://www.googleapis.com/auth/calendar");
    $gclient->setSubject('wellnesstradecz@gmail.com');

    $refreshToken = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/admin/config/tokens/token-".$editor_id.".txt"); // load previously saved token
    $gclient->refreshToken($refreshToken);

    $tokens = $gclient->getAccessToken();
    $gclient->setAccessToken($tokens);

    return new Google_Service_Calendar($gclient);

}


function calendarDelete($gcalendar){

    if(!empty($gcalendar)){

        $service = calendarConnect();
        $service->events->delete(CALENDAR_ID, $gcalendar);

    }

}


function recievers($performersArray, $observersArray, $type, $type_id) {

    global $mysqli;

    if(is_array($performersArray)){
        foreach ($performersArray as $performer => $value) {

            if($value != 0){

                $mysqli->query("INSERT IGNORE INTO mails_recievers (type, type_id, admin_id, reciever_type) VALUES ('".$type."', '".$type_id."', '".$value."', 'performer')")or die($mysqli->error);

            }

        }
    }

    if(is_array($observersArray)){
        foreach ($observersArray as $observer => $value) {

            if($value != 0) {

                $mysqli->query("INSERT IGNORE INTO mails_recievers (type, type_id, admin_id, reciever_type) VALUES ('" . $type . "', '" . $type_id . "', '" . $value . "', 'observer')") or die($mysqli->error);

            }

        }
    }

}



function getAttendees($type_id, $type)
{

    global $mysqli;

    $attendees[] = '';
    $attendeeQuery = $mysqli->query("SELECT admin_id FROM mails_recievers WHERE type_id = '" . $type_id . "' AND type = '".$type."'");
    while ($attendee = mysqli_fetch_array($attendeeQuery)) {

        $adminQuery = $mysqli->query("SELECT user_name, dimension, email FROM demands WHERE id = '" . $attendee['admin_id'] . "'");
        $admin = mysqli_fetch_array($adminQuery);

        if(!empty($admin['dimension'])){

            $attendee = new Google_Service_Calendar_EventAttendee();
            $attendee->setDisplayName($admin['user_name']);
            $attendee->setEmail($admin['dimension']);
            $attendee->setResponseStatus('accepted');

            $attendees[] = $attendee;
            unset($attendee);

        }

    }

    return $attendees;

}

// todo ověřit s mailsModel

function getRecievers($type, $type_id, $reciever_type) {

    global $mysqli;
    $recievers = '';

    $recieversQuery = $mysqli->query("SELECT d.user_name FROM mails_recievers r, demands d WHERE r.type = '".$type."' AND r.type_id = '".$type_id."' AND r.reciever_type = '".$reciever_type."' AND d.id = r.admin_id")or die($mysqli->error);

    while($single = mysqli_fetch_assoc($recieversQuery)){

        if ($recievers != "") { $recievers .= ', ' . $single['user_name']; } else { $recievers = $single['user_name'];; }

    }

    return $recievers;

}
// todo ověřit s mailsModel



function product_price($new_price, $original_price, $new_vat, $old_vat, $discount): array
{

    $price = [];
    $price['new_price'] = $new_price;
    $price['original_price'] = $original_price;
    $price['new_vat'] = $new_vat;
    $price['old_vat'] = $old_vat;
    $price['discount'] = $discount;


    // choose if use of new price or original price
    if (isset($new_price) && $new_price != "") {

        $price['price'] = $new_price;

    } else {

        $price['price'] = $original_price;

    }

    $final_vat = 100 + $new_vat;

    // saved price is 21% vat included, calculate for different vat value
    if (isset($old_vat) && $old_vat == 21 && $new_vat != 21) {

        $price['price'] = $price['price'] / 121 * $final_vat;

    }


    //  calculate what is the net discount value if there is any
    $price['discount_net'] = 0;
    if(!empty($discount)){

        $price['discount_net'] = number_format($price['price'] / 100 * ($discount), 4, '.', '');

    }

    return $price;

}

function generateRandomString($length = 10)
{
    return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
}

function get_product_list($bridge)
{

    global $mysqli;

    $products_query = $mysqli->query("SELECT *, id as ajdee FROM products WHERE id = '" . $bridge['product_id'] . "'");

    if (mysqli_num_rows($products_query) == 1) {

        $product = mysqli_fetch_array($products_query);
        ?>

<a href="/admin/pages/accessories/zobrazit-prislusenstvi?id=<?= $product['ajdee'] ?>" target="_blank">
<?php

        if (!empty($bridge['variation_id'])) {

            $variation_sku_query = $mysqli->query("SELECT id, sku, main_warehouse FROM products_variations WHERE id = '" . $bridge['variation_id'] . "'");
            $variation_sku = mysqli_fetch_array($variation_sku_query);

            $main_warehouse = $variation_sku['main_warehouse'];

            $path = PRODUCT_IMAGE_PATH.'/mini/' . $product['seourl'] . '_variation_'.$variation_sku['id'].'.jpg';
            $path_product = PRODUCT_IMAGE_PATH.'/mini/' . $product['seourl'] . '.jpg';

            if(file_exists($path)){
                $imagePath = '/data/stores/images/mini/'.$product['seourl'].'_variation_'.$variation_sku['id'].'.jpg';
            }elseif(file_exists($path_product)){
                $imagePath = '/data/stores/images/mini/'.$product['seourl'].'.jpg';
            }else{
                $imagePath = '/data/assets/no-image-7.jpg';
            }

        } else {

            $path = PRODUCT_IMAGE_PATH.'/mini/' . $product['seourl'] . '.jpg';
            if(file_exists($path)){
                $imagePath = '/data/stores/images/mini/'.$product['seourl'].'.jpg';
            }else{
                $imagePath = '/data/assets/no-image-7.jpg';
            }

        }

        if (isset($bridge['quantity']) && isset($bridge['reserved']) && ($bridge['quantity'] - $bridge['reserved']) > 0) {
            $border = 'border: 1px dashed #ff0000';
        } else {
            $border = 'border: 1px solid #ebebeb';
        }

        echo '<img src="'.$imagePath.'" width="24" style="float: left; margin-right:8px; '.$border.' ">';

        ?>

          <strong style="font-size: 12px; <?php if (isset($bridge['variation_id']) && $bridge['variation_id'] == 0) { ?>padding-top: 8px; float:left;<?php } ?>font-weight: 500;"><?= $product['productname'] ?> - <small class="tooltip-primary" data-toggle="tooltip" data-placement="top" title="" data-original-title="SKU"><?php

        if (!empty($bridge['variation_id'])) {

            echo $variation_sku['sku'];

        } else {

            echo $product['code'];
        }

        ?></small></strong></a>

          <?php if (!empty($bridge['variation_id'])) {

            echo '<span style="font-size: 12px; font-weight: 300;">';

            $variation_query = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $bridge['variation_id'] . "'");

            while ($variation = mysqli_fetch_array($variation_query)) {
                echo '<br>';
                echo $variation['name'] . ': ' . $variation['value'];

            }

            echo '</span>';

        }
        ?>

<?php

    } else {

        ?>
<strong>Neznámý produkt</strong> <?= $bridge['product_name'] ?> - <small><?= $bridge['variation_values'] ?></small>
<?php

    }

}


/// TODO NOT FINISHED, SHOULD BE IMPLEMENTED INSTEAD ALL SINGLE [PERFORMER] AND [OBSERVER] SELECTORS...

function calendar_recievers_list($role)
{

    global $mysqli;

    if (isset($role) && $role != '') {

        $select_query = $mysqli->query("SELECT id, user_name FROM demands WHERE role = '" . $role . "' AND active = 1 AND active = 1")or die($mysqli->error);

        $all_admins = $mysqli->query("SELECT id, user_name FROM demands WHERE role != 'client' AND active = 1 AND active = 1")or die($mysqli->error);

    } else {

        $select_query = $mysqli->query("SELECT id, user_name FROM demands WHERE role != 'client' AND active = 1 AND active = 1")or die($mysqli->error);

        $all_admins = $mysqli->query("SELECT id, user_name FROM demands WHERE role != 'client' AND active = 1 ")or die($mysqli->error);

    }

    $random_string = generateRandomString();

    ?>


<div class="form-group well admins_well" style="background-color: #FFFFFF; padding: 12px 0px 7px; float: left; width: 49.5%;margin-right: 0.5%; margin-bottom: 0;">

            <h4 style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">Proveditelé</h4>


            <?php while ($admins = mysqli_fetch_array($select_query)) { ?>

           <div class="col-sm-3">

            <input id="admin-<?= $admins['id'] ?>-<?= $random_string ?>" name="performer[]" value="<?= $admins['id'] ?>" type="checkbox">
            <label for="admin-<?= $admins['id'] ?>-<?= $random_string ?>" style="padding-left: 4px; cursor: pointer;"><?= $admins['user_name'] ?></label>

          </div>

          <?php } ?>


          </div>

           <div class="form-group well admins_well" style="background-color: #FFFFFF; padding: 12px 0px 7px; float: left; width: 49.5%; margin-left: 0.5%; margin-bottom: 0;">

            <h4 style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">Informovaní</h4>

         <?php

    while ($admins = mysqli_fetch_array($all_admins)) { ?>

           <div class="col-sm-3">

            <input id="admin-<?= $admins['id'] ?>-<?= $random_string ?>" name="observer[]" value="<?= $admins['id'] ?>" type="checkbox">
            <label for="admin-<?= $admins['id'] ?>-<?= $random_string ?>" style="padding-left: 4px; cursor: pointer;"><?= $admins['user_name'] ?></label>

          </div>

          <?php } ?>


          </div>




<?php

}

/// TODO NOT FINISHED, SHOULD BE IMPLEMENTED INSTEAD ALL SINGLE [PERFORMER] AND [OBSERVER] SELECTORS...

function demand_filtration_route($current, $filters, $requests){

	$route = '';
	if(($key = array_search($current, $filters)) !== false) {
	    unset($filters[$key]);
	}

	if($current == 'customer'){
		if (($key = array_search('category', $filters)) !== false) {
	    	unset($filters[$key]);
		}
	}

	if($current == 'status'){
		if (($key = array_search('realization', $filters)) !== false) {
	    	unset($filters[$key]);
		}
	}


	foreach($filters as $filter){

	      if(isset($requests[$filter])){ $route .= '&'.$filter.'='.$requests[$filter];}

	}

	return $route;

}

function supply_status($status)
{

    if (isset($status) && $status == 0) {

        $final_status = 'Neobjednaná';

    } elseif (isset($status) && $status == 1) {

        $final_status = 'Objednaná';

    } elseif (isset($status) && $status == 2) {

        $final_status = 'Na cestě';

    } elseif (isset($status) && $status == 3) {

        $final_status = 'Přijatá';

    }

    return $final_status;
}

function getAdminUserName($id)
{

    global $mysqli;

    $client_query = $mysqli->query("SELECT user_name FROM demands WHERE id = '" . $id . "'");
    $client = mysqli_fetch_array($client_query);

    return $client['user_name'];

}

// $type must equal 'GET' or 'POST'
function curl_request_async($url, $params, $type = 'GET')
{

    $parts = parse_url($url);
    $fp = fsockopen('ssl://' . $parts['host'], 443, $errno, $errstr, 30);

    // Data goes in the path for a GET request
    if ('GET' == $type) {
        $parts['path'] .= $params;
    }

    $out = "$type " . $parts['path'] . " HTTP/1.1\r\n";
    $out .= "Host: " . $parts['host'] . "\r\n";
    $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
    $out .= "Content-Length: " . strlen($params) . "\r\n";
    $out .= "Connection: Close\r\n\r\n";

    fwrite($fp, $out);
    fclose($fp);

}


function calendar_startDate($date, $time){

    $start = new Google_Service_Calendar_EventDateTime();

    if ($date != "0000-00-00" && $time != "00:00:00") {

        $start->setDateTime($date . 'T' . $time);
        $start->setTimeZone('Europe/Prague');

    } else {

        $start->setDate($date);

    }

    return $start;

}

function calendar_endDate($date, $time, $end_date = '0000-00-00', $end_time = '00:00:00'){

    $end = new Google_Service_Calendar_EventDateTime();

    if ($end_date != "0000-00-00" && $end_time != "00:00:00") {

        $end->setDateTime($end_date . 'T' . $end_time);
        $end->setTimeZone('Europe/Prague');

    } elseif ($end_date != "0000-00-00" && $time != "00:00:00") {

        $end->setDateTime($end_date . 'T23:59:00');
        $end->setTimeZone('Europe/Prague');

    } elseif ($end_date != "0000-00-00") {

        $finalday = date('Y-m-d', strtotime($end_date . "+1 days"));
        $end->setDate($finalday);

    } elseif ($end_time != "00:00:00" && $end_date == "0000-00-00") {

        $end->setDateTime($date . 'T' . $end_time);
        $end->setTimeZone('Europe/Prague');

    } elseif ($time != "00:00:00") {

        $timeformated = date("H:i:s", strtotime($time . "+1 hour"));

        $end->setDateTime($date . 'T' . $timeformated);
        $end->setTimeZone('Europe/Prague');

    } else {

        $dateformated = date("Y-m-d", strtotime("+1 days", strtotime($date)));

        $end->setDate($dateformated);
        $end->setTimeZone('Europe/Prague');

    }

    return $end;

}

function redirection($directory, $page, $params)
{

    header("location:https://www.wellnesstrade.cz/admin/pages/" . $directory . "/" . $page . $params);

}

function returnpn($customer, $product)
{

    global $mysqli;

    $brand_query = $mysqli->query("SELECT brand, fullname FROM warehouse_products WHERE connect_name = '$product'") or die($mysqli->error);
    $brand = mysqli_fetch_array($brand_query);

    if(!empty($brand)){

        return $brand['brand'] . ' ' . ucfirst($brand['fullname']);

    }

    return 'Neznámý produkt';

}

function address($address)
{

    if (isset($address['shipping_street']) && $address['shipping_street'] != '' && isset($address['shipping_city']) && $address['shipping_city'] != '') {

        if (isset($address['shipping_street']) && $address['shipping_street'] == "" && $address['shipping_city'] == "" && ($address['shipping_zipcode'] == "" || $address['shipping_zipcode'] == 0)) {
            echo '<span style="color: #d42020;">je stále neznámá?</span>';
        }

        if ($address['shipping_street'] != "") {
            echo $address['shipping_street']; ?>, <?php } if ($address['shipping_city'] != "") {echo $address['shipping_city'];}if ($address['shipping_zipcode'] != "" && $address['shipping_zipcode'] != 0) {echo ', ' . number_format((int)$address['shipping_zipcode'], 0, ',', ' ');}

    } else {

        if (empty($address['billing_street']) && empty($address['billing_zipcode'])) {echo '<span style="color: #d42020;">je stále neznámá?</span>';}

        if (!empty($address['billing_street'])) {echo $address['billing_street'];?>, <?php }if (!empty($address['billing_city'])) {echo $address['billing_city'];}if (!empty($address['billing_zipcode'])) {echo ', ' . number_format((int)$address['billing_zipcode'], 0, ',', ' ');}

    }

}


function return_address($address)
{

    $final_address = '';

    if (isset($address['shipping_street']) && $address['shipping_street'] != '' && isset($address['shipping_city']) && $address['shipping_city'] != '') {

        if (isset($address['shipping_street']) && $address['shipping_street'] == "" && $address['shipping_city'] == "" && ($address['shipping_zipcode'] == "" || $address['shipping_zipcode'] == 0)) {

            return 'adresa nezadána';

        }

        if ($address['shipping_street'] != "") {

            $final_address .= $address['shipping_street'].', ';

        }

        if ($address['shipping_city'] != "") {

            $final_address .=  $address['shipping_city'];

        }

        if ($address['shipping_zipcode'] != "" && $address['shipping_zipcode'] != 0) {

            $final_address .=  ', ' . number_format((int)$address['shipping_zipcode'], 0, ',', ' ');

        }

    } else {

        if (empty($address['billing_street']) && empty($address['billing_zipcode'])) {

            return 'adresa nezadána';

        }

        if (!empty($address['billing_street'])) {

            $final_address .=  $address['billing_street'].', ';

        }

        if (!empty($address['billing_city'])) {

            $final_address .=  $address['billing_city'];

        }

        if (!empty($address['billing_zipcode'])) {

            $final_address .=  ', ' . number_format((int)$address['billing_zipcode'], 0, ',', ' ');

        }

    }

    return $final_address;

}

function acronym($words)
{
    $acronym = '';
    foreach (explode(' ', $words) as $word) {
        $acronym .= mb_substr($word, 0, 1, 'utf-8');
    }

    echo $acronym;

}

function acronym_rt($words)
{

    $acronym = '';

    foreach (explode(' ', $words) as $word) {
        $acronym .= mb_substr($word, 0, 1, 'utf-8');
    }

    return $acronym;

}

function calendar_location($address)
{

    if (isset($address['shipping_street']) && $address['shipping_street'] != '' && isset($address['shipping_city']) && $address['shipping_city'] != '' && isset($address['shipping_zipcode']) && $address['shipping_zipcode'] != 0) {

        $address_street = $address['shipping_street'] ?: '';
        $address_city = $address['shipping_city'] ?: '';
        $address_zipcode = $address['shipping_zipcode'] ?: '';

    } else {

        $address_street = isset($address['billing_street']) ? $address['billing_street'] : '';
        $address_city = isset($address['billing_city']) ? $address['billing_city'] : '';
        $address_zipcode = isset($address['billing_zipcode']) ? $address['billing_zipcode'] : '';

    }

    $location = "";
    $conn = '';

    if ($address_street != "") {

        $location = $conn . $address_street;

        $conn = ', ';
    }

    if ($address_city != "") {

        $location = $location . $conn . $address_city;

        $conn = ', ';

    }

    if ($address_zipcode != "") {

        $location = $location . $conn . $address_zipcode;

    }

    return $location;

}


function shop_accessories($bridge_name, $target, $target_id, $location_id)
{

    global $mysqli;

    ?>


<script type="text/javascript">
jQuery(document).ready(function($)
{




$('#selectbox-o').select2({
    minimumInputLength: 2,
    ajax: {
      url: "/admin/data/autosuggest-products",
      dataType: 'json',
      data: function (term, page) {
        return {
          q: term
        };
      },
      results: function (data, page) {
        return { results: data };
      }
    },

    formatResult: format,
    formatSelection: format,
    escapeMarkup: function(m) { return m; }

  });

  function format(data) {
    if (!data.id) return data.text; // optgroup

        return "<img src='https://www.wellnesstrade.cz/data/stores/images/mini/" + data.seourl + ".jpg' width='20' height='20'/>" + data.text;

  }



$('#selectbox-o').on("change", function(e) {

  	var data = $('#selectbox-o').select2('data');
    let rate = $('.currency:checked').data("value");
    let currency = $('.currency:checked').val();

	$('#specification_copy').clone(true).insertBefore("#duplicate").attr('id', 'copied').css('border', '1px dashed #57D941').css('border-radius', '3px').fadeIn(600);

	$('#copied .productName').html(data.pure_text);
	$('#copied .image').attr('name', 'product_name[]').attr('src', '/data/stores/images/mini/'+data.seourl+'.jpg');
	$('#copied #copy_this_third').attr('name', 'product_sku[]').attr('value', data.id);
	$('#copied #copy_this_third').attr('name', 'product_sku[]').attr('value', data.id);
	$('#copied #copy_this_second').attr('name', 'product_quantity[]').attr('value', '1');
	$('#copied #copy_discount').attr('name', 'product_discount[]');
	$('#copied #copy_this_price').attr('name', 'product_price[]');
	$('#copied #copy_this_original_price_dummy').attr('name', 'dummy_original[]').attr('value', data.original_price).data('default', data.original_price);
  	$('#copied #copy_this_original_price').attr('name', 'product_original_price[]').attr('value', data.original_price).data('default', data.original_price);

    if(currency != 'CZK') {
        let exchange = (data.original_price / rate).toFixed(2);
        $('#copied #copy_this_price').attr('value', exchange);
    }else{
        $('#copied #copy_this_price').attr('value', data.original_price);
    }

	$('#copied').attr('id', 'copifinish');

    $("#selectbox-o").select2("val", "");

	setTimeout(function(){
	  $('#copifinish').attr('id', 'hasfinish').css('border', "0").css('border-bottom', "1px solid #eeeeee").css('border-radius', "0"); }, 2500);




});


$('.remove_specification').click(function() {
	
	event.preventDefault();
	$(this).closest('.specification').css('border', '1px dashed #D90041').css('border-radius', '3px');

	$.when($(this).closest('.specification').delay(100).fadeOut(600)).done(function() {
    	$(this).closest('.specification').remove();
	});

	

});

});
</script>


		<div class="form-group">
		   <div class="col-sm-12" >
		     <input id="selectbox-o" class="input-xlarge" name="optionvalue" type="hidden" data-placeholder="Vyberte produkt.." />
		   </div>
		</div>

		<hr style="margin-bottom: 0;">

		<div class="form-group">

	<div class="col-sm-12" style="float:left;">


	<div id="specification_copy" class="specification" style="display: none; float:left; width: 100%; margin: 0px 0; padding: 10px 0; border-bottom: 1px solid #eeeeee;">

			<div class="col-sm-1" style="padding: 0; width: 7.3333333%; margin-right: 1%;">
				<img class="image" src="#" height="40" />
			</div>

		<div class="col-sm-5" style="padding: 0;">

			<span class="productName" style="height: 42px; display: table-cell; vertical-align: middle;"></span>

			<input type="text" class="form-control" id="copy_this_third" name="copythis" value="" placeholder="SKU produktu" style="display: none;">

		</div>

		<div class="col-sm-2" style="padding: 0 0px 0 8px; width: 10%;">
					<input type="number" class="form-control spinner" id="copy_this_second" name="copythis" value="" style="width: 100%; height: 42px;"/> 
			</div>

		<div class="col-sm-2" style="padding: 0 0px 0 8px; width: 10%">
				<input type="text" class="form-control" id="copy_discount" name="copythis" placeholder="% sleva" style="width: 100%; height: 42px;"/> 
		</div>

		<div class="col-sm-1" style="padding: 0 0px 0 8px; width: 10.333333%;">

			<input type="text" class="form-control text-center price-control" id="copy_this_price" name="copythis" value="" placeholder="Cena" style="padding: 0; height: 42px;">

		</div>

    <div class="col-sm-1" style="padding: 0 0px 0 8px; width: 10.333333%;">

	  <input type="text" class="form-control text-center" id="copy_this_original_price_dummy" name="copythis" value="" placeholder="Původní cena" style="padding: 0; height: 42px; background-color: #f0f0f1 !important;" disabled>
	  
	  <input type="text" class="form-control text-center" id="copy_this_original_price" name="copythis" value="" placeholder="Původní cena" style="padding: 0; height: 42px; display: none;">

    </div>


		<div class="col-sm-1" style="padding: 0 0px 0 11px;">
			<button type="button" class="remove_specification btn btn-red" style="float:left; padding: 6px 10px; cursor: pointer; height: 42px;"> <i class="entypo-trash"></i> </button>
		 </div>
	</div>


	<?php

    if ($bridge_name != "") {

        $products_bridge = $mysqli->query("SELECT * FROM $bridge_name WHERE aggregate_id = '$target_id'");

        while ($bridge = mysqli_fetch_assoc($products_bridge)) {

            if ($bridge['variation_id'] != 0) {

                $product_query = $mysqli->query("SELECT *, s.id as ajdee, s.price as price FROM products p, products_variations s WHERE p.id = '" . $bridge['product_id'] . "' AND p.id = s.product_id AND s.id = '" . $bridge['variation_id'] . "'");
                $product = mysqli_fetch_assoc($product_query);

                if(!empty($product)) {

                    $select = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $product['ajdee'] . "'") or die($mysqli->error);
                    $desc = "";
                    while ($var = mysqli_fetch_assoc($select)) {

                        $desc = $desc . $var['name'] . ': ' . $var['value'] . ' ';

                    }

                    $price = number_format($product['price'], 0, ',', ' ') . ' Kč';
                    $product_title = $product['productname'] . ' – ' . $desc;
                    $sku = $product['sku'];

                }else{

                    $price = number_format($bridge['price'], 0, ',', ' ') . ' Kč';
                    $product_title = $bridge['product_name'] . ' – ' . $bridge['variation_values'];
                    $sku = $bridge['id'];

                }

            } else {

                $product_query = $mysqli->query("SELECT * FROM products p WHERE id = '" . $bridge['product_id'] . "'");
                $product = mysqli_fetch_assoc($product_query);

                if(!empty($product)) {

                    $price = number_format($product['price'], 0, ',', ' ') . ' Kč';
                    $product_title = $product['productname'];
                    $sku = $product['code'];

                }else{

                    $price = number_format($bridge['price'], 0, ',', ' ') . ' Kč';
                    $product_title = $bridge['product_name'];
                    $sku = $bridge['id'];

                }

			}

            ?>

		<div class="specification" style="float: left; width: 100%; margin: 0px 0; padding: 10px 0; border-bottom: 1px solid #eeeeee;">
			<div class="col-sm-1" style="padding: 0; width: 7.3333333%; margin-right: 1%; text-align: center; ">
				  <?php

            if(!empty($product['seourl']) && file_exists(PRODUCT_IMAGE_PATH.'/small/' . $product['seourl'] . '.jpg')){

                $imagePath = '/data/stores/images/small/'.$product['seourl'].'.jpg';

            }else{

                $imagePath = '/data/assets/no-image-7.jpg';

            }

            echo '<img src="'.$imagePath.'" height="40" />';

            ?>
			</div>
			<div class="col-sm-5" style="padding: 0;">

			<span class="productName" style="height: 42px; display: table-cell; vertical-align: middle;"><?= $product_title ?></span>

			<input type="text" class="form-control" id="copy_this_third" name="product_sku[]" value="<?= $sku ?>" placeholder="SKU produktu" style="display: none;">

			</div>


			<div class="col-sm-2" style="padding: 0 0px 0 8px; width: 10%;">
				<input type="number" class="form-control spinner" name="product_quantity[]" value="<?= $bridge['quantity'] ?>" style="width: 100%; height: 42px;"/> 
			</div>

			<div class="col-sm-2" style="padding: 0 0px 0 8px; width: 10%">
				<input type="text" class="form-control" name="product_discount[]" value="<?php if(!empty($bridge['discount'])){ echo $bridge['discount']; } ?>" style="width: 100%; height: 42px;" placeholder="% sleva"/> 
		</div>


			<div class="col-sm-1" style="padding: 0 0px 0 8px; width: 10.333333%;">
				<input type="text" class="form-control text-center price-control" name="product_price[]" value="<?= $bridge['price'] ?>" data-default="<?= $bridge['price'] ?>" placeholder="Cena" style="padding: 0; height: 42px;">
			</div>


      <div class="col-sm-1" style="padding: 0 0px 0 8px; width: 10.333333%;">
		<input type="text" class="form-control text-center" name="dummy_original[]" value="<?= $bridge['original_price'] ?>" data-default="<?= $bridge['original_price'] ?>" placeholder="Původní cena" style="padding: 0; height: 42px; background-color: #f0f0f1 !important;" disabled>
		<input type="text" class="form-control text-center" name="product_original_price[]" value="<?= $bridge['original_price'] ?>" data-default="<?= $bridge['original_price'] ?>" placeholder="Původní cena" style="padding: 0; height: 42px; display: none;">
      </div>

			<div class="col-sm-1" style="padding: 0 0px 0 11px;">
			<button type="button" class="remove_specification btn btn-red" style="float:left; padding: 6px 10px; cursor: pointer;  height: 42px;"> <i class="entypo-trash"></i> </button>
			 </div>
		</div>

		<?php

        }

	}?>
	
	<span id="duplicate" style="display: none;"></span>
	  
  </div>
  </div>


  	<div class="form-group">
	<label class="col-sm-3 control-label">Pobočka k vypořádání</label>

	<div class="col-sm-9" style="float:left;">

<?php

    if ($location_id == '0') { $desired_location = 7; } else { $desired_location = $location_id; }

    $locations_query = $mysqli->query("SELECT * FROM shops_locations l GROUP BY l.id ORDER BY type ASC");

    while ($location = mysqli_fetch_array($locations_query)) {
        ?>
        <div class="radio" style="width: 25%; float: left;">
            <label>
                <input type="radio" name="location" value="<?= $location['id'] ?>" <?php if(($location['eshop_default'] && empty($location_id))

                    || $location['id'] == $desired_location) {
                    echo 'checked';
                }?>><?= $location['name'] ?>
            </label>
        </div>
     <?php } ?>
		</div>
	</div>

<?php
}

function user_name($name)
{

    if(empty($name['shipping_name']) && empty($name['shipping_surname']) && empty($name['billing_name']) && empty($name['billing_surname']) && empty($name['billing_company'])){

        return '-';

    }else{

        if (isset($name['shipping_name']) && ($name['shipping_name'] != '' || $name['shipping_surname'] != '')) {

            return $name['shipping_name'] . ' ' . $name['shipping_surname'];

        } elseif ($name['billing_name'] && ($name['billing_name'] != '' || $name['billing_surname'] != '')) {

            return $name['billing_name'] . ' ' . $name['billing_surname'];

        } else {

            return $name['billing_company'];

        }

    }

}

function check_shipping($address)
{

    global $mysqli;

    $find_query = $mysqli->query("SELECT id FROM addresses_shipping WHERE shipping_name = '" . $address['shipping_name'] . "' AND shipping_surname = '" . $address['shipping_surname'] . "' AND shipping_street = '" . $address['shipping_street'] . "' AND shipping_city = '" . $address['shipping_city'] . "' AND shipping_zipcode = '" . $address['shipping_zipcode'] . "' AND shipping_country = '" . $address['shipping_country'] . "' AND shipping_phone = '" . $address['shipping_phone'] . "' AND  shipping_email = '" . $address['shipping_email'] . "'");

    if (mysqli_num_rows($find_query) > 0) {

        $result = mysqli_fetch_array($find_query);

        return $result['id'];

    }

}

function check_billing($address)
{

    global $mysqli;

    $find_query = $mysqli->query("SELECT id FROM addresses_billing WHERE billing_ico = '" . $address['billing_ico'] . "' AND billing_dic = '" . $address['billing_dic'] . "' AND billing_street = '" . $address['billing_street'] . "' AND billing_city = '" . $address['billing_city'] . "' AND billing_zipcode = '" . $address['billing_zipcode'] . "' AND billing_country = '" . $address['billing_country'] . "'");

    if (mysqli_num_rows($find_query) > 0) {

        $result = mysqli_fetch_array($find_query);

        return $result['id'];

    }

}

function check_address_invoice($address)
{

    global $mysqli;

    $find_query = $mysqli->query("SELECT id FROM addresses_invoices WHERE billing_company = '" . $address['billing_company'] . "' AND billing_ico = '" . $address['billing_ico'] . "' AND billing_dic = '" . $address['billing_dic'] . "' AND billing_street = '" . $address['billing_street'] . "' AND billing_city = '" . $address['billing_city'] . "' AND billing_zipcode = '" . $address['billing_zipcode'] . "' AND billing_country = '" . $address['billing_country'] . "' AND billing_phone = '" . $address['billing_phone'] . "' AND billing_email = '" . $address['billing_email'] . "'");

    if (mysqli_num_rows($find_query) > 0) {

        $result = mysqli_fetch_array($find_query);

        return $result['id'];

    }

}

function productcode($product)
{

    if ($product == 'tiny') {
        echo 'WS-1219';
    } elseif ($product == 'cavalir') {
        echo 'WS-1252B';
    } elseif ($product == 'home') {
        echo 'WS-1252C';
    } elseif ($product == 'cube') {
        echo 'WS-1102';
    } elseif ($product == 'charm') {
        echo 'WS-1101';
    } elseif ($product == 'exclusive') {
        echo 'WS-1211';
    } elseif ($product == 'lora') {
        echo 'WS-1232';
    } elseif ($product == 'deluxe') {
        echo 'WS-1231';
    } elseif ($product == 'grand') {
        echo 'WS-1233';
    } elseif ($product == 'dice') {
        echo 'WS-1103';
    } elseif ($product == 'charisma') {
        echo 'WS-1212';
    } elseif ($product == 'mona') {
        echo 'YH-1222';
    } elseif ($product == 'giant') {
        echo 'WS-1247';
    }

}

function productreturn($product)
{

    if ($product == 'tiny') {
        return 'WS-1219';
    } elseif ($product == 'cavalir') {
        return 'WS-1252B';
    } elseif ($product == 'home') {
        return 'WS-1252C';
    } elseif ($product == 'cube') {
        return 'WS-1102';
    } elseif ($product == 'charm') {
        return 'WS-1101';
    } elseif ($product == 'exclusive') {
        return 'WS-1211';
    } elseif ($product == 'lora') {
        return 'WS-1232';
    } elseif ($product == 'deluxe') {
        return 'WS-1231';
    } elseif ($product == 'grand') {
        return 'WS-1233';
    } elseif ($product == 'dice') {
        return 'WS-1103';
    } elseif ($product == 'charisma') {
        return 'WS-1212';
    } elseif ($product == 'mona') {
        return 'YH-1222';
    } elseif ($product == 'giant') {
        return 'WS-1247';
    }

}

// todo
function specs_demand($getclient, $type)
{

    global $mysqli;
    ?>

<div class="col-sm-6">

	<!-- začátek specifikace začátek specifikace začátek specifikace -->

	<?php


    if(!empty($getclient)) {

        $get_provedeni = $mysqli->query("SELECT t.id FROM demands_specs_bridge d, warehouse_products_types t, warehouse_products p WHERE d.client_id = '" . $getclient['id'] . "' AND d.specs_id = 5 AND t.warehouse_product_id = p.id AND p.connect_name = '" . $getclient['product'] . "' AND t.name = d.value") or die($mysqli->error);
        $provedeni = mysqli_fetch_array($get_provedeni);

    }

    $virivky_query_new = $mysqli->query("SELECT * FROM warehouse_products WHERE customer = 1 ORDER BY brand");

    if ($type == '1') {

        ?>

		<h4 style="padding-bottom: 13px;border-bottom: 1px dashed #eeeeee;text-align: center;margin-bottom: 14px;">Specifikace vířivky</h4>
	<div class="form-group">
						<label class="col-sm-4 control-label">Vířivky</label>

						<div class="col-sm-8">

							<select class="form-control" name="virivkatype" id="virivkatype">
								<?php

        while ($virivka = mysqli_fetch_array($virivky_query_new)) { ?>
								<option value="<?= $virivka['connect_name'] ?>" <?php if (isset($getclient['product']) && $getclient['product'] == $virivka['connect_name']) {echo 'selected';}?>><?php if ($virivka['brand'] != "") {echo $virivka['brand'] . ' ' . ucfirst($virivka['fullname']);} else {echo ucfirst($virivka['fullname']);}?></option><?php } ?>
							</select>

						</div>
					</div>

					<script type="text/javascript">

						jQuery(document).ready(function($)
						{

						$('#virivkatype').on('change', function() {

							var selected = this.value;

						   	$('.virivky_typy').hide( "slow");
						   	$('.params_virivky').hide( "slow");

							$('.virivka_'+selected).show( "slow");

                            $('.virivka_'+selected).find('select').prop('disabled', false);


							var selected_type = $('.virivka_'+selected+' .provedeni_'+selected).val();

							if(selected_type != ""){

							$('.params_'+selected_type+'_'+selected).show( "slow");


                            }


						});


						});

						</script>



	<?php } elseif ($type == '2') {
        ?>
		<h4 style="padding-bottom: 13px;border-bottom: 1px dashed #eeeeee;text-align: center;margin-bottom: 14px;">Technické detaily</h4>
		<?php
    }

    mysqli_data_seek($virivky_query_new, 0);

    while ($virivka = mysqli_fetch_array($virivky_query_new)) {

        if ($type == '1') {

            ?>
		<div class="virivky_typy virivka_<?= $virivka['connect_name'] ?>" <?php if (empty($getclient) || $getclient['product'] != $virivka['connect_name']) { ?>style="display: none;"<?php } ?>>
			<div class="form-group">
						<label class="col-sm-4 control-label">Provedení</label>

						<div class="col-sm-8">

							<select class="form-control provedeni_<?= $virivka['connect_name'] ?>" name="provedeni_<?= $virivka['connect_name'] ?>">
		<?php

        }

        if(!empty($getclient)){

            $param_type_query = $mysqli->query("SELECT * FROM demands_specs_bridge WHERE specs_id = '5' and client_id = '" . $getclient['id'] . "'") or die($mysqli->error);
            $param_type = mysqli_fetch_array($param_type_query);

        }

        $selected = false;
        $options = '';

        $virivky_typy = $mysqli->query("SELECT * FROM warehouse_products_types WHERE warehouse_product_id = '" . $virivka['id'] . "'") or die($mysqli->error);

        if ($type == '1') {

            while ($typ = mysqli_fetch_array($virivky_typy)) {

                $selected_echo = "";

                if (isset($param_type['value']) && $param_type['value'] == $typ['name'] && $getclient['product'] == $virivka['connect_name']) {$selected = true;
                    $selected_echo = 'selected';}

                $options = $options . '<option value="' . $typ['seo_url'] . '" ' . $selected_echo . '>' . $typ['name'] . '</option>';

            }?>
			<option value="" <?php if ($selected != true) {echo 'selected';}?>>Žádná vybraná možnost</option>

		<?php

            echo $options;

            mysqli_data_seek($virivky_typy, 0);
            ?>

			</select>

						</div>
			</div>
		</div>

					<script type="text/javascript">

						jQuery(document).ready(function($)
						{

						$('.provedeni_<?= $virivka['connect_name'] ?>').on('change', function() {

							var selected = this.value;

						   	$('.params_virivky_<?= $virivka['connect_name'] ?>').hide( "slow");

                            $('.params_'+selected+'_<?= $virivka['connect_name'] ?> select').removeAttr('disabled');
                            $('.params_'+selected+'_<?= $virivka['connect_name'] ?> input').removeAttr('disabled');

                            $('.params_'+selected+'_<?= $virivka['connect_name'] ?> ').show( "slow");

                        });


						});

					</script>

		<?php
        }

        while ($typ = mysqli_fetch_array($virivky_typy)) { ?>

			<div class="params_virivky params_virivky_<?= $virivka['connect_name'] ?> params_<?= $typ['seo_url'] ?>_<?= $virivka['connect_name'] ?>" <?php if (empty($param_type) || $param_type['value'] != $typ['name'] || $getclient['product'] != $virivka['connect_name']) { ?>style="display: none;"<?php } ?>>


				<?php

            $specs_query = $mysqli->query("SELECT *, s.id as id FROM specs s, warehouse_products_types_specs w WHERE w.spec_id = s.id AND w.type_id = '" . $typ['id'] . "' AND s.is_demand = 1 AND s.demand_category = '$type' GROUP BY s.id ORDER BY s.demand_order") or die($mysqli->error);

            while ($specs = mysqli_fetch_array($specs_query)) {

                if(!empty($getclient)){
                $param_spec_query = $mysqli->query("SELECT * FROM demands_specs_bridge WHERE specs_id = '" . $specs['id'] . "' and client_id = '" . $getclient['id'] . "'") or die($mysqli->error);
                $cpars = mysqli_fetch_array($param_spec_query);

                }
                // VALUE U POPTÁVKY K DANÉ SPECIFIKACI

                if (isset($specs['type']) && $specs['type'] == 1) {

                    $paramsquery = $mysqli->query("SELECT *, p.id as id FROM specs_params p, warehouse_products_types_specs w  WHERE p.spec_id = '" . $specs['id'] . "' AND w.spec_param_id = p.id AND w.type_id = '" . $typ['id'] . "' GROUP by p.id") or die($mysqli->error);

                    // VŠECHNY VALUES U PROVEDENÍ VÍŘIVKY

                    ?>

					 <div class="form-group" style="margin-bottom: 12px; margin-top: 10px;">
						<label class="col-sm-4 control-label" style="line-height: 31px; padding-top: 0;"><?= $specs['name'] ?></label>
						<div class="col-sm-8">

								<select <?php if (isset($specs['generate']) && $specs['generate'] == 1) { ?>class="generate_select form-control" id="price_<?= $specs['seoslug'] ?>"<?php } ?> name="<?= $virivka['connect_name'] ?>_<?= $typ['seo_url'] ?>_<?= $specs['seoslug'] ?>" class="form-control" <?php if (empty($param_type) || $param_type['value'] != $typ['name'] || $getclient['product'] != $virivka['connect_name']) { echo 'disabled'; } ?>>
									<?php
                    $selected = false;
                    $options = '';

                    while ($params = mysqli_fetch_assoc($paramsquery)) {

                        $selected_echo = "";

                        // když uložené provedení u poptávky == právě řešené provedení
                        if (isset($provedeni['id']) && $provedeni['id'] == $params['type_id']) {

                            if (isset($cpars['value']) && $cpars['value'] == $params['option'] && $param_type['value'] == $typ['name'] && $getclient['product'] == $virivka['connect_name']) {

                                $selected_echo = 'selected';
                                $selected = true;

                            }

                        } elseif (empty($provedeni) || $provedeni['id'] != $params['type_id']) {

                            if (isset($params['choosed']) && $params['choosed'] == 1 && (empty($cpars) || $cpars['value'] != "unknown")) {

                                $selected_echo = 'selected';
                                $selected = true;

                            }

                        }

                        $options = $options . '<option value="' . $params['option'] . '" ' . $selected_echo . '>' . $params['option'] . '</option>';

                    }

                    if ($selected != true && (isset($cpars['value']) && $cpars['value'] != '')) {
                        $options = $options . '<option value="' . $cpars['value'] . '" selected>' . $cpars['value'] . '</option>';
                        $selected = true;
                    }

                    ?>
									<option value="" <?php if ($selected != true) {echo 'selected';}?>>Žádná vybraná možnost</option>

									<?= $options ?>
								</select>

							</div>
						</div>


						<?php } else {

                    $paramsquery = $mysqli->query("SELECT * FROM warehouse_products_types_specs WHERE spec_id = '" . $specs['id'] . "' AND type_id = '" . $typ['id'] . "' order by spec_param_id desc") or die($mysqli->error);

                    ?>
					<div class="form-group" style="margin-bottom: 8px;">
					<label class="col-sm-4 control-label" style="line-height: 22px; padding-top: 0;"><?= $specs['name'] ?></label>
					<div class="col-sm-8">

							<?php
                    $selected = false;
                    while ($params = mysqli_fetch_array($paramsquery)) {

                        if (isset($params['spec_param_id']) && $params['spec_param_id'] == 1) { $value = 'Ano'; } else { $value = 'Ne';}
                        ?>
							<div class="radio" style="width: 64px; margin-left: 12px; float: left; padding-top: 0; height: 20px; line-height: 22px; min-height: 20px;">
								<label>
									<input <?php

                                           if (isset($specs['generate']) && $specs['generate'] == 1) {

                                               ?>class="generate_radio" id="price_<?= $specs['seoslug'] ?>"<?php

                                           }?>
                                           type="radio"
                                           name="<?= $virivka['connect_name'] ?>_<?= $typ['seo_url'] ?>_<?= $specs['seoslug'] ?>"
                                           value="<?= $value ?>"  <?php

                                        if ((!empty($cpars['value']) && $cpars['value'] == $value && $param_type['value'] == $typ['name'])
                                            || ($params['choosed'] == 1
                                            && (empty($cpars['value']) || $cpars['value'] != "unknown")
                                            && !$selected)) {

                                            $selected = true; echo 'checked'; } ?> style=" height: 20px;" <?php if (empty($param_type) || $param_type['value'] != $typ['name'] || $getclient['product'] != $virivka['connect_name']) { echo 'disabled'; } ?>><?= $value ?>
								</label>
							</div>



							<?php } ?>

						</div>

					</div>

					<?php } ?>


						<?php } ?>



		</div>


		<?php } ?>



		<?php } ?>



	<!-- konec specifikace konec specifikace konec specifikace -->


</div>
<?php

}



function specs_sauna($getclient, $type)
{

    global $mysqli;
    ?>
    <div class="col-sm-6">

        <!-- začátek specifikace začátek specifikace začátek specifikace -->

        <?php


        if(!empty($getclient)) {

            $get_provedeni = $mysqli->query("SELECT t.id FROM demands_specs_bridge d, warehouse_products_types t, warehouse_products p WHERE d.client_id = '" . $getclient['id'] . "' AND d.specs_id = 5 AND t.warehouse_product_id = p.id AND p.connect_name = '" . $getclient['product'] . "' AND t.name = d.value") or die($mysqli->error);
            $provedeni = mysqli_fetch_array($get_provedeni);

        }

        $sauny_query_new = $mysqli->query("SELECT * FROM warehouse_products WHERE customer = 0 ORDER BY brand");

        if ($type == '1') {

            ?>

            <h4 style="padding-bottom: 13px;border-bottom: 1px dashed #eeeeee;text-align: center;margin-bottom: 14px;">Specifikace sauny</h4>
            <div class="form-group">
                <label class="col-sm-4 control-label">Sauny</label>

                <div class="col-sm-8">

                    <select class="form-control" name="saunatype" id="saunatype">
                        <?php

                        while ($sauna = mysqli_fetch_array($sauny_query_new)) { ?>
                            <option value="<?= $sauna['connect_name'] ?>" <?php if (isset($getclient['product']) && $getclient['product'] == $sauna['connect_name']) {echo 'selected';}?>><?php if ($sauna['brand'] != "") {echo $sauna['brand'] . ' ' . ucfirst($sauna['fullname']);} else {echo ucfirst($sauna['fullname']);}?></option><?php } ?>
                    </select>

                </div>
            </div>

            <script type="text/javascript">

                jQuery(document).ready(function($)
                {

                    $('#saunatype').on('change', function() {


                        var selected = this.value;

                        $('.sauny_typy').hide( "slow");
                        $('.params_sauny').hide( "slow");

                        $('.sauna_'+selected).show( "slow");


                        var selected_type = $('.sauna_'+selected+' .provedeni_'+selected).val();

                        if(selected_type != ""){

                            console.log('keke');
                            $('.params_'+selected_type+'_'+selected).show( "slow");

                        }


                    });


                });

            </script>



        <?php } elseif ($type == '2') {
        ?>
            <h4 style="padding-bottom: 13px;border-bottom: 1px dashed #eeeeee;text-align: center;margin-bottom: 14px;">Technické detaily</h4>
            <?php
        }

        mysqli_data_seek($sauny_query_new, 0);

        while ($sauna = mysqli_fetch_array($sauny_query_new)) {

        if ($type == '1') {

            ?>
            <div class="sauny_typy sauna_<?= $sauna['connect_name'] ?>" <?php if (empty($getclient) || $getclient['product'] != $sauna['connect_name']) { ?>style="display: none;"<?php } ?>>
                <div class="form-group">
                    <label class="col-sm-4 control-label">Provedení</label>

                    <div class="col-sm-8">

                        <select class="form-control provedeni_<?= $sauna['connect_name'] ?>" name="provedeni_<?= $sauna['connect_name'] ?>">
                            <?php

                            }

                            if(!empty($getclient)){

                                $param_type_query = $mysqli->query("SELECT * FROM demands_specs_bridge WHERE specs_id = '5' and client_id = '" . $getclient['id'] . "'") or die($mysqli->error);
                                $param_type = mysqli_fetch_array($param_type_query);

                            }

                            $selected = false;
                            $options = '';

                            $sauny_typy = $mysqli->query("SELECT * FROM warehouse_products_types WHERE warehouse_product_id = '" . $sauna['id'] . "'") or die($mysqli->error);

                            if ($type == '1') {

                            while ($typ = mysqli_fetch_array($sauny_typy)) {

                                $selected_echo = "";

                                if (isset($param_type['value']) && $param_type['value'] == $typ['name'] && $getclient['product'] == $sauna['connect_name']) {$selected = true;
                                    $selected_echo = 'selected';}

                                $options = $options . '<option value="' . $typ['seo_url'] . '" ' . $selected_echo . '>' . $typ['name'] . '</option>';

                            }?>
                            <option value="" <?php if ($selected != true) {echo 'selected';}?>>Žádná vybraná možnost</option>

                            <?php

                            echo $options;

                            mysqli_data_seek($sauny_typy, 0);
                            ?>

                        </select>

                    </div>
                </div>
            </div>

            <script type="text/javascript">

                jQuery(document).ready(function($)
                {

                    $('.provedeni_<?= $sauna['connect_name'] ?>').on('change', function() {

                        console.log('keke');

                        var selected = this.value;

                        $('.params_sauny_<?= $sauna['connect_name'] ?>').hide( "slow");
                        $('.params_'+selected+'_<?= $sauna['connect_name'] ?>').show( "slow");


                    });


                });

            </script>

        <?php
        }

        while ($typ = mysqli_fetch_array($sauny_typy)) { ?>

            <div class="params_sauny params_sauny_<?= $sauna['connect_name'] ?> params_<?= $typ['seo_url'] ?>_<?= $sauna['connect_name'] ?>" <?php if (empty($param_type) || $param_type['value'] != $typ['name'] || $getclient['product'] != $sauna['connect_name']) { ?>style="display: none;"<?php } ?>>


                <?php

                $specs_query = $mysqli->query("SELECT *, s.id as id FROM specs s, warehouse_products_types_specs w WHERE w.spec_id = s.id AND w.type_id = '" . $typ['id'] . "' AND s.is_demand = 1 AND s.demand_category = '$type' GROUP BY s.id ORDER BY s.demand_order") or die($mysqli->error);

                while ($specs = mysqli_fetch_array($specs_query)) {

                    if(!empty($getclient)){
                        $param_spec_query = $mysqli->query("SELECT * FROM demands_specs_bridge WHERE specs_id = '" . $specs['id'] . "' and client_id = '" . $getclient['id'] . "'") or die($mysqli->error);
                        $cpars = mysqli_fetch_array($param_spec_query);

                    }
                    // VALUE U POPTÁVKY K DANÉ SPECIFIKACI

                    if (isset($specs['type']) && $specs['type'] == 1) {

                        $paramsquery = $mysqli->query("SELECT *, p.id as id FROM specs_params p, warehouse_products_types_specs w  WHERE p.spec_id = '" . $specs['id'] . "' AND w.spec_param_id = p.id AND w.type_id = '" . $typ['id'] . "' GROUP by p.id") or die($mysqli->error);

                        // VŠECHNY VALUES U PROVEDENÍ VÍŘIVKY

                        ?>

                        <div class="form-group" style="margin-bottom: 12px; margin-top: 10px;">
                            <label class="col-sm-4 control-label" style="line-height: 31px; padding-top: 0;"><?= $specs['name'] ?></label>
                            <div class="col-sm-8">

                                <select <?php if (isset($specs['generate']) && $specs['generate'] == 1) { ?>class="generate_select form-control" id="price_<?= $specs['seoslug'] ?>"<?php } ?> name="<?= $sauna['connect_name'] ?>_<?= $typ['seo_url'] ?>_<?= $specs['seoslug'] ?>" class="form-control">
                                    <?php
                                    $selected = false;
                                    $options = '';

                                    while ($params = mysqli_fetch_assoc($paramsquery)) {

                                        $selected_echo = "";

                                        // když uložené provedení u poptávky == právě řešené provedení
                                        if (isset($provedeni['id']) && $provedeni['id'] == $params['type_id']) {

                                            if (isset($cpars['value']) && $cpars['value'] == $params['option'] && $param_type['value'] == $typ['name'] && $getclient['product'] == $sauna['connect_name']) {

                                                $selected_echo = 'selected';
                                                $selected = true;

                                            }

                                        } elseif (empty($provedeni) || $provedeni['id'] != $params['type_id']) {

                                            if (isset($params['choosed']) && $params['choosed'] == 1 && (empty($cpars) || $cpars['value'] != "unknown")) {

                                                $selected_echo = 'selected';
                                                $selected = true;

                                            }

                                        }

                                        $options = $options . '<option value="' . $params['option'] . '" ' . $selected_echo . '>' . $params['option'] . '</option>';

                                    }

                                    if ($selected != true && (isset($cpars['value']) && $cpars['value'] != '')) {
                                        $options = $options . '<option value="' . $cpars['value'] . '" selected>' . $cpars['value'] . '</option>';
                                        $selected = true;
                                    }

                                    ?>
                                    <option value="" <?php if ($selected != true) {echo 'selected';}?>>Žádná vybraná možnost</option>

                                    <?= $options ?>
                                </select>

                            </div>
                        </div>


                    <?php } else {

                        $paramsquery = $mysqli->query("SELECT * FROM warehouse_products_types_specs WHERE spec_id = '" . $specs['id'] . "' AND type_id = '" . $typ['id'] . "' order by spec_param_id desc") or die($mysqli->error);

                        ?>
                        <div class="form-group" style="margin-bottom: 8px;">
                            <label class="col-sm-4 control-label" style="line-height: 22px; padding-top: 0;"><?= $specs['name'] ?></label>
                            <div class="col-sm-8">

                                <?php
                                $selected = false;
                                while ($params = mysqli_fetch_array($paramsquery)) {

                                    if (isset($params['spec_param_id']) && $params['spec_param_id'] == 1) {$value = 'Ano';} else { $value = 'Ne';}
                                    ?>
                                    <div class="radio" style="width: 64px; margin-left: 12px; float: left; padding-top: 0; height: 20px; line-height: 22px; min-height: 20px;">
                                        <label>
                                            <input <?php

                                                   if (isset($specs['generate']) && $specs['generate'] == 1) {

                                                   ?>class="generate_radio" id="price_<?= $specs['seoslug'] ?>"<?php

                                            }?>
                                                   type="radio"
                                                   name="<?= $sauna['connect_name'] ?>_<?= $typ['seo_url'] ?>_<?= $specs['seoslug'] ?>"
                                                   value="<?= $value ?>"  <?php

                                            if ((!empty($cpars['value']) && $cpars['value'] == $value && $param_type['value'] == $typ['name'])
                                                || ($params['choosed'] == 1
                                                    && (empty($cpars['value']) || $cpars['value'] != "unknown")
                                                    && !$selected)) {

                                                $selected = true; echo 'checked'; } ?> style=" height: 20px;"><?= $value ?>
                                        </label>
                                    </div>



                                <?php } ?>

                            </div>

                        </div>

                    <?php } ?>


                <?php } ?>



            </div>


        <?php } ?>



        <?php } ?>



        <!-- konec specifikace konec specifikace konec specifikace -->


    </div>
    <?php

}

function specs_pergola($getclient, $type)
{

    global $mysqli;
    ?>
    <div class="col-sm-6">

        <!-- začátek specifikace začátek specifikace začátek specifikace -->

        <?php


        if(!empty($getclient)) {

            $get_provedeni = $mysqli->query("SELECT t.id FROM demands_specs_bridge d, warehouse_products_types t, warehouse_products p WHERE d.client_id = '" . $getclient['id'] . "' AND d.specs_id = 5 AND t.warehouse_product_id = p.id AND p.connect_name = '" . $getclient['product'] . "' AND t.name = d.value") or die($mysqli->error);
            $provedeni = mysqli_fetch_array($get_provedeni);

        }

        $pergoly_query_new = $mysqli->query("SELECT * FROM warehouse_products WHERE customer = 4 ORDER BY brand");

        if ($type == '1') {

            ?>

            <h4 style="padding-bottom: 13px;border-bottom: 1px dashed #eeeeee;text-align: center;margin-bottom: 14px;">Specifikace pergoly</h4>
            <div class="form-group">
                <label class="col-sm-4 control-label">Pergoly</label>

                <div class="col-sm-8">

                    <select class="form-control" name="pergolatype" id="pergolatype">
                        <?php

                        while ($pergola = mysqli_fetch_array($pergoly_query_new)) { ?>
                            <option value="<?= $pergola['connect_name'] ?>" <?php if (isset($getclient['product']) && $getclient['product'] == $pergola['connect_name']) {echo 'selected';}?>><?php if ($pergola['brand'] != "") {echo $pergola['brand'] . ' ' . ucfirst($pergola['fullname']);} else {echo ucfirst($pergola['fullname']);}?></option><?php } ?>
                    </select>

                </div>
            </div>

            <script type="text/javascript">

                jQuery(document).ready(function($)
                {

                    $('#pergolatype').on('change', function() {


                        var selected = this.value;

                        $('.pergoly_typy').hide( "slow");
                        $('.params_pergoly').hide( "slow");

                        $('.pergola_'+selected).show( "slow");


                        var selected_type = $('.pergola_'+selected+' .provedeni_'+selected).val();

                        if(selected_type != ""){

                            $('.params_'+selected_type+'_'+selected).show( "slow");

                        }


                    });


                });

            </script>



        <?php } elseif ($type == '2') {
        ?>
            <h4 style="padding-bottom: 13px;border-bottom: 1px dashed #eeeeee;text-align: center;margin-bottom: 14px;">Technické detaily</h4>
            <?php
        }

        mysqli_data_seek($pergoly_query_new, 0);

        while ($pergola = mysqli_fetch_array($pergoly_query_new)) {

        if ($type == '1') {

            ?>
            <div class="pergoly_typy pergola_<?= $pergola['connect_name'] ?>" <?php if (empty($getclient) || $getclient['product'] != $pergola['connect_name']) { ?>style="display: none;"<?php } ?>>
                <div class="form-group">
                    <label class="col-sm-4 control-label">Provedení</label>

                    <div class="col-sm-8">

                        <select class="form-control provedeni_<?= $pergola['connect_name'] ?>" name="provedeni_<?= $pergola['connect_name'] ?>">
                            <?php

                            }

                            if(!empty($getclient)){

                                $param_type_query = $mysqli->query("SELECT * FROM demands_specs_bridge WHERE specs_id = '5' and client_id = '" . $getclient['id'] . "'") or die($mysqli->error);
                                $param_type = mysqli_fetch_array($param_type_query);

                            }

                            $selected = false;
                            $options = '';

                            $pergoly_typy = $mysqli->query("SELECT * FROM warehouse_products_types WHERE warehouse_product_id = '" . $pergola['id'] . "' ORDER BY seo_url ASC") or die($mysqli->error);

                            if ($type == '1') {

                            while ($typ = mysqli_fetch_array($pergoly_typy)) {

                                $selected_echo = "";

                                if (isset($param_type['value']) && $param_type['value'] == $typ['name'] && $getclient['product'] == $pergola['connect_name']) {$selected = true;
                                    $selected_echo = 'selected';}

                                $options = $options . '<option value="' . $typ['seo_url'] . '" ' . $selected_echo . '>' . $typ['name'] . '</option>';

                            }?>
                            <option value="" <?php if ($selected != true) {echo 'selected';}?>>Žádná vybraná možnost</option>

                            <?php

                            echo $options;

                            mysqli_data_seek($pergoly_typy, 0);
                            ?>

                        </select>

                    </div>
                </div>
            </div>

            <script type="text/javascript">

                jQuery(document).ready(function($)
                {

                    $('.provedeni_<?= $pergola['connect_name'] ?>').on('change', function() {

                        var selected = this.value;

                        $('.params_pergoly_<?= $pergola['connect_name'] ?>').hide( "slow");
                        $('.params_'+selected+'_<?= $pergola['connect_name'] ?>').show( "slow");


                    });


                });

            </script>

        <?php
        }

        while ($typ = mysqli_fetch_array($pergoly_typy)) { ?>

            <div class="params_pergoly params_pergoly_<?= $pergola['connect_name'] ?> params_<?= $typ['seo_url'] ?>_<?= $pergola['connect_name'] ?>" <?php if (empty($param_type) || $param_type['value'] != $typ['name'] || $getclient['product'] != $pergola['connect_name']) { ?>style="display: none;"<?php } ?>>


                <?php

                $specs_query = $mysqli->query("SELECT *, s.id as id FROM specs s, warehouse_products_types_specs w WHERE w.spec_id = s.id AND w.type_id = '" . $typ['id'] . "' AND s.is_demand = 1 AND s.demand_category = '$type' GROUP BY s.id ORDER BY s.demand_order") or die($mysqli->error);

                while ($specs = mysqli_fetch_array($specs_query)) {

                    if(!empty($getclient)){
                        $param_spec_query = $mysqli->query("SELECT * FROM demands_specs_bridge WHERE specs_id = '" . $specs['id'] . "' and client_id = '" . $getclient['id'] . "'") or die($mysqli->error);
                        $cpars = mysqli_fetch_array($param_spec_query);

                    }
                    // VALUE U POPTÁVKY K DANÉ SPECIFIKACI

                    if (isset($specs['type']) && $specs['type'] == 1) {

                        $paramsquery = $mysqli->query("SELECT *, p.id as id FROM specs_params p, warehouse_products_types_specs w  WHERE p.spec_id = '" . $specs['id'] . "' AND w.spec_param_id = p.id AND w.type_id = '" . $typ['id'] . "' GROUP by p.id") or die($mysqli->error);

                        // VŠECHNY VALUES U PROVEDENÍ VÍŘIVKY

                        ?>

                        <div class="form-group" style="margin-bottom: 12px; margin-top: 10px;">
                            <label class="col-sm-4 control-label" style="line-height: 31px; padding-top: 0;"><?= $specs['name'] ?></label>
                            <div class="col-sm-8">

                                <select <?php if (isset($specs['generate']) && $specs['generate'] == 1) { ?>class="generate_select form-control" id="price_<?= $specs['seoslug'] ?>"<?php } ?> name="<?= $pergola['connect_name'] ?>_<?= $typ['seo_url'] ?>_<?= $specs['seoslug'] ?>" class="form-control">
                                    <?php
                                    $selected = false;
                                    $options = '';

                                    while ($params = mysqli_fetch_assoc($paramsquery)) {

                                        $selected_echo = "";

                                        // když uložené provedení u poptávky == právě řešené provedení
                                        if (isset($provedeni['id']) && $provedeni['id'] == $params['type_id']) {

                                            if (isset($cpars['value']) && $cpars['value'] == $params['option'] && $param_type['value'] == $typ['name'] && $getclient['product'] == $pergola['connect_name']) {

                                                $selected_echo = 'selected';
                                                $selected = true;

                                            }

                                        } elseif (empty($provedeni) || $provedeni['id'] != $params['type_id']) {

                                            if (isset($params['choosed']) && $params['choosed'] == 1 && (empty($cpars) || $cpars['value'] != "unknown")) {

                                                $selected_echo = 'selected';
                                                $selected = true;

                                            }

                                        }

                                        $options = $options . '<option value="' . $params['option'] . '" ' . $selected_echo . '>' . $params['option'] . '</option>';

                                    }

                                    if ($selected != true && (isset($cpars['value']) && $cpars['value'] != '')) {
                                        $options = $options . '<option value="' . $cpars['value'] . '" selected>' . $cpars['value'] . '</option>';
                                        $selected = true;
                                    }

                                    ?>
                                    <option value="" <?php if ($selected != true) {echo 'selected';}?>>Žádná vybraná možnost</option>

                                    <?= $options ?>
                                </select>

                            </div>
                        </div>


                    <?php } elseif($specs['type'] == 0) {

                        $paramsquery = $mysqli->query("SELECT * FROM warehouse_products_types_specs WHERE spec_id = '" . $specs['id'] . "' AND type_id = '" . $typ['id'] . "' order by spec_param_id desc") or die($mysqli->error);

                        ?>
                        <div class="form-group" style="margin-bottom: 8px;">
                            <label class="col-sm-4 control-label" style="line-height: 22px; padding-top: 0;"><?= $specs['name'] ?></label>
                            <div class="col-sm-8">

                                <?php
                                $selected = false;
                                while ($params = mysqli_fetch_array($paramsquery)) {

                                    if (isset($params['spec_param_id']) && $params['spec_param_id'] == 1) {$value = 'Ano';} else { $value = 'Ne';}
                                    ?>
                                    <div class="radio" style="width: 64px; margin-left: 12px; float: left; padding-top: 0; height: 20px; line-height: 22px; min-height: 20px;">
                                        <label>
                                            <input <?php

                                                   if (isset($specs['generate']) && $specs['generate'] == 1) {

                                                   ?>class="generate_radio" id="price_<?= $specs['seoslug'] ?>"<?php

                                            }?>
                                                   type="radio"
                                                   name="<?= $pergola['connect_name'] ?>_<?= $typ['seo_url'] ?>_<?= $specs['seoslug'] ?>"
                                                   value="<?= $value ?>"  <?php

                                            if ((!empty($cpars['value']) && $cpars['value'] == $value && $param_type['value'] == $typ['name'])
                                                || ($params['choosed'] == 1
                                                    && (empty($cpars['value']) || $cpars['value'] != "unknown")
                                                    && !$selected)) {

                                                $selected = true; echo 'checked'; } ?> style=" height: 20px;"><?= $value ?>
                                        </label>
                                    </div>



                                <?php } ?>

                            </div>

                        </div>

                    <?php } elseif($specs['type'] == 2) {

                        if(!empty($getclient)){

                            $paramsquery = $mysqli->query("SELECT * FROM demands_specs_bridge WHERE client_id = '" . $getclient['id'] . "' AND specs_id = '" . $specs['id'] . "' order by id desc") or die($mysqli->error);
                            
                            $params = mysqli_fetch_array($paramsquery);

                        }

                        ?>
                        <div class="form-group" style="margin-bottom: 8px;">
                            <label class="col-sm-4 control-label pergspec" style="line-height: 22px; padding-top: 0;"><?= $specs['name'] ?></label>
                            <div class="col-sm-8">
                                <?php
                                $selected = false;

                                    ?>
                                        
                                    <div class="radio" style="padding-left: 0; padding-top: 0;">
                                        <label>
                                            <input 
                                                   type="text"
                                                   name="<?= $pergola['connect_name'] ?>_<?= $typ['seo_url'] ?>_<?= $specs['seoslug'] ?>"
                                                   class="form-control generate_text" id="price_<?= $specs['seoslug'] ?>" value="<?php if(isset($params['value'])){ echo $params['value']; } ?>" style="width: 100%;">
                                        </label>
                                    </div>

                            </div>

                        </div>

                    <?php } ?>


                <?php } ?>



            </div>


        <?php } ?>



        <?php } ?>



        <!-- konec specifikace konec specifikace konec specifikace -->


    </div>
    <?php

}

function demands($clients, $type)
{

    global $mysqli;

/*
$address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $clients['shipping_id'] . '" WHERE b.id = "' . $clients['billing_id'] . '"')or die($mysqli->error);
$address = mysqli_fetch_assoc($address_query);
 */
    $title = $clients['user_name'];

    ?>


<div class="member-entry" style="margin-bottom: 0px;" >
		<script type="text/javascript">
		jQuery(document).ready(function($)
		{

		$('#priradit-<?= $clients['id'] ?>').click(function() {

				$('#priradit-<?= $clients['id'] ?>').hide( "slow");
				$('#prirazeni-<?= $clients['id'] ?>').show( "slow");

		});

		$('#cancel-<?= $clients['id'] ?>').click(function() {


				$('#prirazeni-<?= $clients['id'] ?>').hide( "slow");
				$('#priradit-<?= $clients['id'] ?>').show( "slow");

		});


		});
</script>
<?php if ($clients['customer'] != 3) { ?>
	<a href="/admin/pages/demands/zobrazit-poptavku?id=<?= $clients['id'] ?>" class="member-img" style="width: 6%;" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?php if (isset($clients['product']) && $clients['product'] == 'custom') {echo 'sauna na míru';} else {echo $clients['brand'] . ' ' . $clients['fullname'];}?>">
		<img src="https://www.wellnesstrade.cz/admin/data/images/customer/<?= $clients['product'] ?>.png" width="90px" class="img-rounded" />
	</a>
	<?php } else {

        if ($clients['secondproduct'] != 'custom') {
            $second_product_query = $mysqli->query("SELECT brand, fullname FROM warehouse_products WHERE connect_name = '" . $clients['secondproduct'] . "'") or die($mysqli->error);
            $second_product = mysqli_fetch_array($second_product_query);
        }

        ?>
	<a href="/admin/pages/demands/zobrazit-poptavku?id=<?= $clients['id'] ?>" class="member-img" style="width: 6%; margin-top: -4px;" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $clients['brand'] . ' ' . ucfirst($clients['fullname']) ?> a <?php if (isset($clients['secondproduct']) && $clients['secondproduct'] == 'custom') {echo 'sauna na míru';} else {echo $second_product['brand'] . ' ' . ucfirst($second_product['fullname']);}?>">
		<div style="width: 50%; height: 90px; overflow: hidden; float: left; text-align: left"><img style="height: 100%; width: auto; max-width: inherit; float: left;;" src="https://www.wellnesstrade.cz/admin/data/images/customer/<?= $clients['product'] ?>.png" width="90px" class="img-rounded" /></div>
		<div style="width: 50%; height: 90px; overflow: hidden; float: right; text-align: right"><img style="height: 100%; width: auto; max-width: inherit; float: left; margin-left: -100%;" src="https://www.wellnesstrade.cz/admin/data/images/customer/<?= $clients['secondproduct'] ?>.png" width="90px" class="img-rounded"/></div>
	</a>
	<?php } ?>
	<div class="member-details">
		<div class="col-sm-12" style="padding: 0; float: left; display: block; border-bottom: 1px solid #f5f5f5; margin-left: 20px; margin-bottom: 6px;">
			<div class="col-sm-5" style="padding-left: 0;">
			<h4 style="float: left; margin-left: 0; margin-bottom: 11px;">
			<a href="/admin/pages/demands/zobrazit-poptavku?id=<?= $clients['id'] ?>"> <?php if (isset($clients['billing_degree']) && $clients['billing_degree'] != "") {echo $clients['billing_degree'] . ' ';}
    echo $title;?></a>

			<small <?php
			if($clients['demand_status'] < 5 || $clients['demand_status'] == 7) {
			    ?>id="priradit-<?= $clients['id'] ?>"<?php
			    }?>><?php

			    if (isset($clients['demand_status']) && $clients['demand_status'] == 1) {

			        echo 'Nezpracovaná poptávka';

			    } elseif (isset($clients['demand_status']) && $clients['demand_status'] == 2) {

			        echo 'Zhotovená nabídka';

			    } elseif (isset($clients['demand_status']) && $clients['demand_status'] == 3) {

			        echo 'V řešení';

			    } elseif (isset($clients['demand_status']) && $clients['demand_status'] == 12) {

			        echo 'Prodaná';

			    } elseif (isset($clients['demand_status']) && $clients['demand_status'] == 4) {

			        echo 'Realizace';

			    } elseif (isset($clients['demand_status']) && $clients['demand_status'] == 8) {

			        echo 'Nedokončená';

			    } elseif (isset($clients['demand_status']) && $clients['demand_status'] == 7) {

			        echo 'Odložená';

			    } elseif (isset($clients['demand_status']) && $clients['demand_status'] == 5) {

			        echo 'Hotová';

			    }elseif (isset($clients['demand_status']) && $clients['demand_status'] == 14) {

			        echo 'Neobjednaná vířivka';

			    }elseif (isset($clients['demand_status']) && $clients['demand_status'] == 15) {

			        echo 'Nová realizace';

			    } elseif (isset($clients['demand_status']) && $clients['demand_status'] == 6) {
			        echo 'Stornovaná';
			    }?></small>
					</h4>
				</div>
				<div class="col-sm-4" style="padding-top: 11px; padding-right: 0; text-align: right;">
				<i class="entypo-briefcase"></i>
			<?php if (isset($clients['showroom']) && $clients['showroom'] == 1) {echo 'Showroom <strong>Hradčanská</strong>';} elseif (isset($clients['showroom']) && $clients['showroom'] == 3) {echo 'Showroom <strong>Brno</strong>';} else {echo 'Neznámý showroom';}

    if (isset($clients['admin_id']) && $clients['admin_id'] != 0) {
        $findadminquery = $mysqli->query("SELECT user_name FROM demands WHERE id = '" . $clients['admin_id'] . "'");
        $findadmin = mysqli_fetch_array($findadminquery);

        echo ', <strong>' . $findadmin['user_name'] . '</strong>';

    } else {echo ', o poptávku se nikdo nestará';}
    ?>
			</div>


				<div class="col-sm-3" style="padding-top: 11px; padding-right: 0; text-align: right;">
					<i class="entypo-calendar"></i>
				Poptávka od <?= $clients['dateformated'] ?>

			</div>


			<hr>
		</div>

						<?php if (isset($clients['status']) && $clients['status'] < 5 || $clients['status'] == 7) { ?>	<div id="prirazeni-<?= $clients['id'] ?>" class="form-group" style="display:none;width: 50%;float:left;">

					<form role="form" method="post" name="myform" action="/admin/pages/demands/editace-poptavek?action=changetype&id=<?= $clients['id'] ?>&type=<?= $type ?>">
					<div class="col-sm-6">
						<select name="typus" class="form-control" >
								<option value="1" <?php if (isset($clients['status']) && $clients['status'] == 1) {echo 'selected';}?>>Nezpracované</option>
								<option value="2" <?php if (isset($clients['status']) && $clients['status'] == 2) {echo 'selected';}?>>Zhotovené nabídky</option>
								<option value="3" <?php if (isset($clients['status']) && $clients['status'] == 3) {echo 'selected';}?>>V řešení</option>
								<option value="12" <?php if (isset($clients['status']) && $clients['status'] == 12) {echo 'selected';}?>>Prodaná</option>
								<option value="4" <?php if (isset($clients['status']) && $clients['status'] == 4) {echo 'selected';}?>>Realizace</option>
								<option value="8" <?php if (isset($clients['status']) && $clients['status'] == 8) {echo 'selected';}?>>Nedokončená</option>
								<option value="5" <?php if (isset($clients['status']) && $clients['status'] == 5) {echo 'selected';}?>>Hotové</option>
								<option value="7" <?php if (isset($clients['status']) && $clients['status'] == 7) {echo 'selected';}?>>Odložená</option>
								<option value="6" <?php if (isset($clients['status']) && $clients['status'] == 6) {echo 'selected';}?>>Stornovaná</option>
						</select>
						</div>
						<button style="float: left;margin-left: -9px;" type="submit" class="btn btn-green"> <i class="entypo-pencil"></i> </button>
						<a id="cancel-<?= $clients['id'] ?>" style="float: left;margin-left: 4px;"><button type="button" class="btn btn-white"> <i class="entypo-cancel"></i> </button></a>
					</form>

					</div><?php } ?>

<div class="clear"></div>
		<!-- Details with Icons -->		<div class="row info-list">


			<div style="width: 60%; padding: 0; float: left; margin: 0;" class="info-list">

				<div class="col-sm-4" style="width: 55%; margin-top: 8px;">
					<i class="entypo-mail"></i>
					<a href="/admin/pages/demands/zobrazit-poptavku?id=<?= $clients['id'] ?>"><?php if ($clients['billing_email'] != "") {echo $clients['billing_email'];} else {echo "žádný email";}?></a>
				</div>

				<div class="col-sm-4" style="padding-left: 0; width: 45%; margin-top: 8px;">
						<i class="fa fa-file" style="padding: 0 4px;"></i> Vystavení smlouvy: <?php if (isset($clients['date_contract']) && $clients['date_contract'] != '00. 00. 0000' && $clients['date_contract'] != '0000-00-00') { ?><strong style="color: #0071bc;"><?= $clients['date_contract'] ?></strong><?php } else { ?>nevystavena<?php } ?>
				</div>



					<div class="col-sm-4" style="width: 55%; margin-top: 8px;">
						<i class="entypo-phone"></i>
						<a href="/admin/pages/demands/zobrazit-poptavku?id=<?= $clients['id'] ?>"><?php if (isset($clients['billing_phone']) && $clients['billing_phone'] != "" && $clients['billing_phone'] != 0) {

        echo phone_prefix($clients['billing_phone_prefix']).' ';
        echo number_format((int)$clients['billing_phone'], 0, ',', ' ');} else {echo "žádný telefon";}?></a>
					</div>



					<div class="col-sm-4" style="padding-left: 0;width: 45%; margin-top: 8px;">
								<i class="entypo-forward"></i>
						Vystavení ZF: <?php if (isset($clients['invoice_date']) && $clients['invoice_date'] != "" && $clients['invoice_date'] != '0000-00-00') { ?><strong style="color: #0071bc;"><?= $clients['invoice_date'] ?></strong><?php } else {echo '<strong style="color: #d42020;">nevystavena <i class="entypo-attention"></i></strong>';}?>

					</div>



						<div class="col-sm-4" style="width: 55%; margin-top: 8px;">
							<i class="entypo-location"></i>
							<a href="/admin/pages/demands/zobrazit-poptavku?id=<?= $clients['id'] ?>"><?php address($clients);?></a>
						</div>

				<?php $now = date("Y-m-d", strtotime("now"));?>



						<div class="col-sm-4" style="padding-left: 0;width: 45%; margin-top: 8px;">
									<i class="entypo-credit-card"></i>
							Splatnost ZF: <?php if (isset($clients['due_date']) && $clients['due_date'] < $now && $clients['paid_value'] != $clients['total_price']) {

        ?><strong style="color: #d42020;"><?= $clients['due_date'] ?> - po splatnosti<i class="entypo-attention"></i></strong><?php

    } elseif (isset($clients['due_date']) && $clients['due_date'] != "" && $clients['due_date'] != '00. 00. 0000' && $clients['due_date'] != '0000-00-00') {

        ?><strong style="color: #0071bc;"><?= $clients['due_date'] ?></strong><?php } else {echo '<strong style="color: #d42020;">nevystavena <i class="entypo-attention"></i></strong>';}?>

						</div>





            <div class="col-sm-4" style="width: 55%; margin-top: 8px;">

            </div>





            <div class="col-sm-4" style="padding-left: 0;width: 45%; margin-top: 8px;">
                  <i class="entypo-credit-card"></i>

              Částka: <?php if (isset($clients['payment_method']) && $clients['payment_method'] == 'cash') { ?>
        <strong style="color: #ff9600">platba hotově</strong>
        <?php } else {
        if (isset($clients['paid_value']) && $clients['paid_value'] != '0') { ?>

    <?php if (isset($clients['paid_value']) && $clients['paid_value'] == $clients['total_price']) { ?>

      <strong style="color: #00a651"><?= number_format($clients['paid_value'], 0, ',', ' ') ?> / <?= number_format($clients['total_price'], 0, ',', ' ') ?> Kč</strong>

    <?php } elseif (isset($clients['paid']) && $clients['paid'] == 3) { ?>

      <strong style="color: #00a651">problém vyřešen<br><?= number_format($clients['paid_value'], 0, ',', ' ') ?> / <?= number_format($clients['total_price'], 0, ',', ' ') ?> Kč</strong>

    <?php } else { ?>

      <strong style="color: #d42020">problém: <?= number_format($clients['paid_value'], 0, ',', ' ') ?> / <?= number_format($clients['total_price'], 0, ',', ' ') ?> Kč</strong>

    <?php } ?>

    <?php } elseif (isset($clients['payment_date']) && $clients['payment_date'] != '00. 00. 0000') { ?>
    <span style="color: #00a651">zaplaceno</span>

    <?php } elseif (isset($clients['paid_value']) && $clients['paid_value'] != '') {echo '<strong style="color: #d42020;">neproplacena</strong>';} else {

            echo '<strong style="color: #d42020;">nevystavena <i class="entypo-attention"></i></strong>';

        }}

    ?>

            </div>





			</div>

			<div style="width: 40%; padding: 0; float: left; margin:0" class="info-list">

				<?php if ($clients['customer'] != 3) { ?>





					<div class="col-sm-12" style="padding-right: 0; margin-top: 8px;">
						<i class="entypo-tools"></i>
				<?php if ($clients['realizationformated'] != '00. 00. 0000') {

			
 			if(isset($clients['confirmed']) && $clients['confirmed'] == 1) {
				 
				$color = 'color: #00a651;';
			
			} elseif(isset($clients['confirmed']) && $clients['confirmed'] == 2) {

				$color = 'color: #FF9933;';
				
			}else{

				$color = 'color: #21d1e1;';

					}

					?>
				<span style="cursor: pointer; <?= $color ?>">Realizace <strong><?= $clients['realizationformated'] ?></strong></span> ~ <span data-id="<?= $clients['id'] ?>" data-type="<?php if (isset($clients['customer']) && $clients['customer'] == 1) {echo 'hottub';} else {echo 'sauna';}?>" class="toggle-deadline-modal" style="cursor: pointer;">Deadline: <strong><?php if ($clients['duerino'] != '00. 00. 0000') { ?><?= $clients['duerino'] ?><?php } else { ?>není<?php } ?></strong></span>
					<?php

    } else {if (isset($clients['status']) && $clients['status'] == 4) { ?><span data-id="<?= $clients['id'] ?>" class="toggle-realization-modal" style="color: #d42020; cursor: pointer; font-weight: bold;">Den realizace nebyl stanoven</span><?php } else { ?><span data-id="<?= $clients['id'] ?>" class="toggle-realization-modal" style="cursor: pointer;">Den realizace nebyl stanoven</span><?php }}?>

					</div>




								<div class="col-sm-12" style="padding-right: 0; margin-top: 7px;">
									<i class="entypo-calendar"></i>
									<?php if (isset($clients['status']) && $clients['status'] == 4 && $clients['realization'] != '0000-00-00') {

        $date1 = new DateTime($clients['realization']);
        $date2 = new DateTime();
        $interval = $date1->diff($date2);
        $nummero = $interval->days;
        if ($date2 > $date1) { ?>

							<span style="color: #d42020;"><i style="color: #d42020;font-size: 16px; margin-left: 0px;margin-top: -3px;" class="entypo-cancel-circled" data-toggle="tooltip" data-placement="top" title="" data-original-title="WTF!"></i>Realizace měla proběhnout <strong>před <?php echo $nummero . ' ';if ($nummero > 1) {echo 'dny';} elseif ($nummero < 2) {echo 'dnem';} ?></strong></span>

						<?php } else {

            if (isset($clients['confirmed']) && $clients['confirmed'] == 1) { ?>

							Realizace proběhne <span style="color: #00a651; font-weight: bold;">za <?php echo $nummero . ' ';if ($nummero > 4) {echo 'dní';} elseif ($nummero > 1) {echo 'dny';} else {echo 'den';}if ($nummero < 6) { ?><i style="color: #d42020;font-size: 16px; margin-left: 1px;margin-top: -3px;position: absolute;" class="entypo-attention" data-toggle="tooltip" data-placement="top" title="" data-original-title="Realizace má být provedena za méně než 5 dní!"></i><?php } ?></span>

							<?php } else { ?>

							Realizace je naplánovaná <span style="color: #21d1e1; font-weight: bold;">za <?php echo $nummero . ' ';if ($nummero > 4) {echo 'dní';} elseif ($nummero > 1) {echo 'dny';} else {echo 'den';}if ($nummero < 6) { ?><i style="color: #d42020;font-size: 16px; margin-left: 1px;margin-top: -3px;position: absolute;" class="entypo-attention" data-toggle="tooltip" data-placement="top" title="" data-original-title="Realizace má být provedena za méně než 5 dní!"></i><?php } ?></span>

							<?php } ?>

						<?php }} else {echo 'Realizace nestanovena';}?>
								</div>





							 <?php $find = $mysqli->query("SELECT id, container_id FROM containers_products WHERE demand_id = '" . $clients['id'] . "'");
        if (mysqli_num_rows($find) == 0 || isset($clients['serial_number'])) {
            ?>

		          <div class="col-sm-12" style="padding-right: 0; margin-top: 5px;">
					<i class="fa fa-truck" style="padding: 0 2px;"></i>
					Sériové číslo: <?php if (isset($clients['serial_number'])) { ?><strong style="color: #0071bc;"><?= $clients['serial_number'] ?><i class="entypo-down-open-mini"></i></strong><?php } else { ?>žádné<?php } ?>


						<?php
            if (isset($clients['status'])) {

                if ($clients['demand_id'] != 0) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-red btn-sm">Rezervovaná</button>';}
                if (isset($clients['status']) && $clients['status'] == 0) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-orange btn-sm">Zadána do výroby</button>';} elseif (isset($clients['status']) && $clients['status'] == 1) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-blue btn-sm">Na cestě</button><button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-primary btn-sm"><strong><u>' . $clients['loading_date'] . '</u></strong></button>';} elseif (isset($clients['status']) && $clients['status'] == 2) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-green btn-sm">Na skladě</button>';} elseif (isset($clients['status']) && $clients['status'] == 3) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-brown btn-sm">Na showroomu</button>';} elseif (isset($clients['status']) && $clients['status'] == 5) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-brown btn-sm">Na showroomu - Brno</button>';}

            }

            ?>
							</div>

		          	<?php } else {
            $finded = mysqli_fetch_array($find);
            ?>

					<div class="col-sm-12" style="padding-left: 0; margin-top: 8px;">
						<i class="entypo-forward"></i>
						Kontejner: <strong style="cursor: pointer;color: orange;"><?= $finded['container_id'] ?></strong>, Vířivka <strong style="cursor: pointer;color: orange;">#<?= $finded['id'] ?> </strong>
					</div>

					<?php } ?>


							<?php } else { ?>

							<div class="well info-list" style="padding: 5px 0; margin: 0 0 6px 0;">

								<div class="col-sm-12" style="padding-right: 0;">
										<i class="entypo-tools"></i>
									<?php if ($clients['realizationformated'] != '00. 00. 0000') { ?><span data-id="<?= $clients['id'] ?>" data-type="hottub" class="toggle-realization-modal" style="color: #00a651; cursor: pointer;">Realizace <strong>vířivky <?= $clients['realizationformated'] ?></strong></span> ~ <span data-id="<?= $clients['id'] ?>" data-type="hottub" class="toggle-deadline-modal" style="cursor: pointer;">Deadline: <strong><?php if ($clients['duerino'] != '00. 00. 0000') { ?><?= $clients['duerino'] ?><?php } else { ?>není<?php } ?></strong></span>
										<?php
    } else {if (isset($clients['status']) && $clients['status'] == 4) { ?><span style="color: #d42020;">Den realizace nebyl stanoven.</span><?php } else { ?>Den realizace nebyl stanoven.<?php }}?>

								</div>


								<div class="col-sm-12" style="padding-right: 0;">
								<i class="fa fa-truck" style="padding: 0 2px;"></i>
								Sériové číslo: <?php if (isset($clients['serial_number'])) { ?><strong style="color: #0071bc;"><?= $clients['serial_number'] ?><i class="entypo-down-open-mini"></i></strong><?php } else { ?>žádné<?php } ?>


									<?php
        if (isset($clients['status'])) {

            if ($clients['demand_id'] != 0) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-red btn-sm">Rezervovaná</button>';}
            if (isset($clients['status']) && $clients['status'] == 0) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-orange btn-sm">Zadána do výroby</button>';} elseif (isset($clients['status']) && $clients['status'] == 1) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-blue btn-sm">Na cestě</button><button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-primary btn-sm"><strong><u>' . $clients['loading_date'] . '</u></strong></button>';} elseif (isset($clients['status']) && $clients['status'] == 2) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-green btn-sm">Na skladě</button>';} elseif (isset($clients['status']) && $clients['status'] == 3) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-brown btn-sm">Na showroomu</button>';} elseif (isset($clients['status']) && $clients['status'] == 5) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-brown btn-sm">Na showroomu - Brno</button>';}

        }

        ?>
								</div>

							</div>


							<div class="well info-list" style="padding: 5px 0 7px; margin: 0;">

									<?php

        $gatedate = $mysqli->query("SELECT *, DATE_FORMAT(d.startdate, '%d. %m. %Y') as startformated, DATE_FORMAT(d.enddate, '%d. %m. %Y') as endformated, DATE_FORMAT(g.deadline_date, '%d. %m. %Y') as duerino FROM demands_double_realization d LEFT JOIN demands_generate_sauna g ON g.id = d.demand_id WHERE d.demand_id = '" . $clients['id'] . "'") or die($mysqli->error);
        $saunadate = mysqli_fetch_array($gatedate);

        ?>

								<div class="col-sm-12" style="padding-right: 0;">
										<i class="entypo-tools"></i>
									<?php if ($saunadate['startformated'] != '00. 00. 0000') { ?><span data-id="<?= $clients['id'] ?>" data-type="sauna" class="toggle-realization-modal" style="color: #00a651; cursor: pointer;">Realizace <strong>sauny <?= $saunadate['startformated'] ?></strong></span> ~ <span data-id="<?= $clients['id'] ?>" data-type="sauna" class="toggle-deadline-modal" style="cursor: pointer;">Deadline: <strong><?php if ($saunadate['duerino'] != '00. 00. 0000' && $saunadate['duerino'] != '') { ?><?= $saunadate['duerino'] ?><?php } else { ?>není<?php } ?></strong></span>
										<?php
        } else {if (isset($clients['status']) && $clients['status'] == 4) { ?><span style="color: #d42020;">Den realizace nebyl stanoven.</span><?php } else { ?>Den realizace nebyl stanoven.<?php }}?>

								</div>

								<?php if (isset($clients['secondproduct']) && $clients['secondproduct'] == 'custom') { ?>


								<div class="col-sm-12" style="padding-right: 0;">
								<i class="fa fa-truck" style="padding: 0 2px;"></i>
								<strong>Sauna na míru</strong>


								</div>
								<?php
        } else { ?>


						<div class="col-sm-12" style="padding-right: 0;">
								<i class="fa fa-truck" style="padding: 0 2px;"></i>
								Sériové číslo: <?php if (isset($clients['serial_number'])) { ?><strong style="color: #0071bc;"><?= $clients['serial_number'] ?><i class="entypo-down-open-mini"></i></strong><?php } else { ?>žádné<?php } ?>


									<?php
            if (isset($clients['status'])) {

                if ($clients['demand_id'] != 0) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-red btn-sm">Rezervovaná</button>';}
                if (isset($clients['status']) && $clients['status'] == 0) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-orange btn-sm">Zadána do výroby</button>';} elseif (isset($clients['status']) && $clients['status'] == 1) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-blue btn-sm">Na cestě</button><button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-primary btn-sm"><strong><u>' . $clients['loading_date'] . '</u></strong></button>';} elseif (isset($clients['status']) && $clients['status'] == 2) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-green btn-sm">Na skladě</button>';} elseif (isset($clients['status']) && $clients['status'] == 3) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-brown btn-sm">Na showroomu</button>';} elseif (isset($clients['status']) && $clients['status'] == 5) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-brown btn-sm">Na showroomu - Brno</button>';}

            }
            ?>
								</div>


							<?php } ?>
							</div>

					<?php } ?>
			</div>






		</div>
	</div>

</div>

<?php

}

function containers($containers)
{
    global $mysqli;

    $double_join = $mysqli->query("SELECT c.user_name as creator_user_name, e.user_name as editor_user_name FROM demands c LEFT JOIN demands  e ON e.id = '" . $containers['editor_id'] . "' LEFT JOIN containers_products p ON p.container_id = '" . $containers['id'] . "' WHERE c.id = '" . $containers['creator_id'] . "'");
    $double = mysqli_fetch_array($double_join);

    $container_products = $mysqli->query("SELECT p.*, DATE_FORMAT(p.date_created, '%d. %m. %Y') as dateformated, d.user_name,  d.id as demand_id, w.serial_number as warehouse_number, creator.user_name as creator_user_name, editor.user_name as editor_user_name FROM containers_products p LEFT JOIN demands d ON d.id = p.demand_id LEFT JOIN warehouse w ON w.id = p.warehouse_id LEFT JOIN demands  creator ON creator.id = p.creator_id LEFT JOIN demands  editor ON editor.id = p.editor_id WHERE p.container_id = '" . $containers['id'] . "' ORDER BY id desc") or die($mysqli->error);

    $total_products = mysqli_num_rows($container_products);
    ?>
<div class="member-entry" style="margin-bottom: 0px;" >

	<div class="col-sm-9" style="padding: 0; margin: 0;" >

        <h3 style="margin: 0; float: left; margin-right: 20px; line-height: 34px;">Container #<?php if ($containers['container_name'] != "") {echo $containers['container_name']; } else { echo $containers['id_brand']; }?></h3>

    <?php
    if ($containers['date_due'] != '0000-00-00' && $containers['date_correction'] == 0) {
        ?><button class="btn btn-orange btn-sm">ve výrobě - <strong><?= $containers['due_formated'] ?></strong></button><?php
    } elseif ($containers['date_due'] != '0000-00-00' && $containers['date_correction'] == 1 && $containers['closed'] != 3) {
        ?><button class="btn btn-blue btn-sm">na cestě - <strong><?= $containers['due_formated'] ?></strong></button><?php
    } elseif($containers['date_received'] != '0000-00-00' && $containers['date_correction'] == 1 && $containers['closed'] == 3){
        ?><button class="btn btn-success btn-sm">převzato - <strong><?= $containers['received_formated'] ?></strong></button><?php
    }?>



        <span class=" btn btn-default btn-md">
                <i class="entypo-popup"></i>
                <a>Size: <strong>[<?= $total_products . '/' . $containers['size'] ?>]</strong></a></span>

        <?php if(!empty($containers['container_number'])){ ?><span class=" btn btn-default btn-md">
            <i class="entypo-doc-text"></i>
                <a>Number: <strong><?= $containers['container_number'] ?> </strong></a></span>

        <?php }

        if(!empty($containers['container_supplier'])){ ?><span class=" btn btn-default btn-md">
            <i class="entypo-address"></i>
                <a>Supplier: <strong><?= $containers['container_supplier'] ?> </strong></a></span>

        <?php }

        if($containers['date_lead'] != '0000-00-00'){ ?>
            <span class=" btn btn-default btn-md">
                <i class="fa fa-industry"></i>
                <a>Factory Lead Time: <strong><?= $containers['date_lead'] ?></strong></a>
            </span>
        <?php }

        if($containers['date_loading'] != '0000-00-00'){ ?>
            <span class=" btn btn-default btn-md">
                <i class="fa fa-truck"></i>
                <a>Loading Time: <strong><?= $containers['date_loading'] ?></strong></a>
            </span>
        <?php } ?>

        <hr>

<!--            <div class="well" style="margin-bottom: 0;">-->



                <?php

                if(!empty($containers['value']) && $containers['value'] == 1){

                    if($containers['first_payment'] != 0.00) {

                        ?>
                    <span class=" btn btn-white btn-md" style="width: 150px;">
                    <i class="fas fa-funnel-dollar"></i>

                    <a>Záloha: $<strong><?php  echo number_format($containers['first_payment'], 2, '.', ','); ?></strong></a></span>
                        <?php

                    }else{

                        ?>
                    <span class=" btn btn-white btn-md" style="width: 150px;">
                    <i class="fas fa-funnel-dollar"></i>
                    <a>Záloha: –</a></span>
                        <?php
                    }

                    if($containers['second_payment'] != 0.00) {
                        ?>
                        <span class=" btn btn-white btn-md" style="width: 150px;">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <a>Doplatek: $<strong><?php  echo number_format($containers['second_payment'], 2, '.', ','); ?></strong></a></span>
                        <?php
                    }else{
                        ?>
                        <span class=" btn btn-white btn-md" style="width: 150px;">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <a>Doplatek: –</a></span>
                        <?php
                    }

                    if($containers['total_payment'] != 0.00 && $containers['total_payment_correction'] == 0.00) {

                        ?>
                        <span class=" btn btn-white btn-md" style="width: 150px;">
                    <i class="fas fa-coins"></i>

                    <a>Celkem: $<strong><?php  echo number_format($containers['total_payment'], 2, '.', ','); ?></strong></a></span>
                        <?php

                    }elseif($containers['total_payment_correction'] != 0.00){ ?>
                        <span class=" btn btn-white btn-md" style="width: 150px;">
                    <i class="fas fa-coins"></i>

                    <a>Celkem: $<strong><?php  echo number_format($containers['total_payment_correction'], 2, '.', ','); ?></strong></a></span>
                    <?php }else{ ?>
                        <span class=" btn btn-white btn-md" style="width: 150px;">
                    <i class="fas fa-coins"></i>
                    <a>Celkem: –</a></span>
                    <?php }

                    if($containers['spare_parts'] != 0.00) {

                        ?>
                        <span style=" border-right: 1px solid #cccccc; margin: 0 11px 0 8px;"></span>

                        <span class=" btn btn-white btn-md">
                    <i class="fas fa-tools"></i>

                    <a>Náhradní díly: $<strong><?= number_format($containers['spare_parts'], 2, '.', ',') ?></strong></a></span>
                        <?php


                    }

                }

                ?>

<!--        </div>-->

	</div>


    <div class="col-sm-3" style="float:right; text-align: right;margin-top: 0px;padding-right: 0px; padding-left: 0;">




                <a data-id="<?= $containers['id'] ?>" class="toggle-modal-add btn btn-primary btn-sm btn-icon icon-left">
                    <i class="entypo-plus"></i>
                    Přidat položku
                </a>


               <?php

    if (isset($containers['closed']) && ($containers['closed'] == 0 || $containers['closed'] == 1)) {
        ?>



				<?php if (isset($containers['size']) && $containers['size'] == 14) { ?>
					<a href="editace-kontejneru?id=<?= $containers['id'] ?>&action=split" class="btn btn-primary btn-sm btn-icon icon-left">
						<i class="entypo-resize-full"></i>
						Rozdělit
					</a>

				<?php } else { ?>
					<a data-id="<?= $containers['id'] ?>" data-type="container" class="toggle-modal-merge btn btn-primary btn-sm btn-icon icon-left">
						<i class="entypo-resize-small"></i>
						Sloučit
					</a>
				<?php } ?>



					<span style=" border-right: 1px solid #cccccc; margin: 0 11px 0 8px;"></span>

					<a data-id="<?= $containers['id'] ?>" data-type="container" class="toggle-add-codes btn btn-success btn-sm btn-icon icon-left">
						<i class="fa fa-barcode"></i>
						Přiřadit kódy
					</a>

            <span style=" border-right: 1px solid #cccccc; margin: 0 11px 0 8px;"></span>
          <a data-id="<?= $containers['id'] ?>" data-type="container" class="toggle-modal-remove btn btn-danger btn-sm btn-icon icon-left">
            <i class="entypo-cancel"></i>
            Smazat
          </a>


        <?php } elseif (isset($containers['closed']) && $containers['closed'] == 2) { ?>

        <span style=" border-right: 1px solid #cccccc; margin: 0 11px 0 8px;"></span>

        <a data-id="<?= $containers['id'] ?>" data-type="recieveContainer" class="toggle-receive-modal btn btn-success btn-sm btn-icon icon-left">
          <i class="entypo-check"></i>
          Převzato
        </a>

        <a data-id="<?= $containers['id'] ?>" class="toggle-date-modal btn btn-primary btn-sm btn-icon icon-left">
                <i class="entypo-calendar"></i>
                Doručení
        </a>
        <?php } ?>
        <hr>
        <a class="btn btn-primary btn-sm show-container" data-container="<?= $containers['id'] ?>">
            <i class="entypo-search"></i>
        </a>
        <span style=" border-right: 1px solid #cccccc; margin: 0 11px 0 8px;"></span>


        <a data-id="<?= $containers['id'] ?>" class="toggle-delivery-modal btn btn-primary btn-sm btn-icon icon-left">
            <i class="fa fa-truck"></i>
            Doprava + ID
        </a>

        <?php if(!empty($containers['value']) && $containers['value'] == 1){ ?>

            <?php
            if($containers['first_payment'] == 0.00){
                ?>
                <a data-id="<?= $containers['id'] ?>" class="toggle-first-payment btn btn-primary btn-sm btn-icon icon-left">
                    <i class="fas fa-wallet"></i>
                    Záloha
                </a>
            <?php }elseif($containers['second_payment'] == 0.00){ ?>
                <a data-id="<?= $containers['id'] ?>" class="toggle-second-payment btn btn-blue btn-sm btn-icon icon-left">
                    <i class="fas fa-wallet"></i>
                    Doplatek
                </a>
            <?php }else{ ?>
                <a class="btn btn-green btn-sm btn-icon icon-left">
                    <i class="entypo-check"></i>
                    Zaplaceno
                </a>
            <?php } ?>

        <?php } ?>

        <span style=" border-right: 1px solid #cccccc; margin: 0 11px 0 8px;"></span>

        <a href="/admin/controllers/generators/containers-table.php?id=<?= $containers['id'] ?>" target="_blank" class="btn btn-blue btn-sm btn-icon icon-left" >
            <i class="fas fa-file-pdf" style="padding: 5px 9px;"> </i>
            Export
        </a>



	</div>



<div style="clear: both;"></div>
<div id="container-holder-<?= $containers['id'] ?>"></div>

<div id="container-<?= $containers['id'] ?>" <?php if ($containers['closed'] > '0') { ?>style="display: none;"<?php } ?>>

</div>

</div>

<hr style="border-color: #e2e2e5; border-style: dashed; margin: 10px 0;">
<?php

}

function sauny($clients, $access_edit)
{

    global $mysqli;

    if ($clients['demand_id'] != 0) {

        $demandquery = $mysqli->query("SELECT id, user_name FROM demands WHERE id = '" . $clients['demand_id'] . "'");
        $demand = mysqli_fetch_array($demandquery);

    }

    if ($clients['serial_number'] != "") {$name = $clients['serial_number'];} else { $name = '#' . $clients['id'];}

    ?>


    <div class="member-entry" style="margin-bottom: 0px;" >
        <?php if (isset($clients['demand_id']) && $clients['demand_id'] == 0) { ?><script type="text/javascript">
            jQuery(document).ready(function($)
            {

                $('#priradit-<?= $clients['id'] ?>').click(function() {

                    $('#priradit-<?= $clients['id'] ?>').hide( "slow");
                    $('#prirazeni-<?= $clients['id'] ?>').show( "slow");

                });

                $('#cancel-<?= $clients['id'] ?>').click(function() {


                    $('#prirazeni-<?= $clients['id'] ?>').hide( "slow");
                    $('#priradit-<?= $clients['id'] ?>').show( "slow");

                });
            });
        </script>
        <?php } ?>



        <div class="member-details" style="width: 100% !important;">
            <a class="member-img" style="width: 4%; overflow: hidden; height: 42px; border-bottom: 1px solid #e0e0e0; border-top: 1px solid #e0e0e0">
                <img style=" margin-top: -12%;" src="https://www.wellnesstrade.cz/admin/data/images/customer/<?= $clients['product'] ?>.png" width="100px" class="img-rounded" />
            </a>

            <div class="col-sm-9" style="width: 73%;">
                <div style="min-width: 47%; float: left;">
                    <h4 style="float: left; margin-left: 0;">

                        <?php
                        if ($clients['demand_id'] != 0 && $clients['status'] != 4) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-red btn-sm">Prodaná</button>';
                        }

                        if (isset($clients['status']) && $clients['status'] == 0) {

                            echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-orange btn-sm">Ve výrobě</button>';

                        } elseif (isset($clients['status']) && $clients['status'] == 1) {

                            echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-blue btn-sm">Na cestě</button><button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-primary btn-sm">očekávané naskladnění <strong><u>' . $clients['dateformated'] . '</u></strong></button>';

                        } elseif (isset($clients['status']) && $clients['status'] == 2) {

                            echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-green btn-sm">Na skladě</button>';

                        } elseif (isset($clients['status']) && $clients['status'] == 3) {

                            echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-brown btn-sm">Na showroomu</button>';

                        } elseif (isset($clients['status']) && $clients['status'] == 4) {

                            echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-purple btn-sm">Expedovaná</button>';

                        } elseif (isset($clients['status']) && $clients['status'] == 6) {

                            echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-info btn-sm">Uskladněná</button>';

                        } elseif (isset($clients['status']) && $clients['status'] == 7) {

                            echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-info btn-sm">Reklamace</button>';

                        }

                        if (isset($clients['status']) && $clients['status'] != 4) {

                            $location_query = $mysqli->query("SELECT name FROM shops_locations WHERE id = '" . $clients['location_id'] . "'") or die($mysqli->error);
                            $location = mysqli_fetch_array($location_query);

                            echo '<button style="margin-right: 4px; margin-top: -3px; background-color: #338fd8; border-color: #338fd8;" type="button" class="btn btn-brown btn-sm">' . $location['name'] . '</button>';

                        }
                        ?>
                        <a href="zobrazit-saunu?id=<?= $clients['id'] ?>"><?= $name ?> | <?= $clients['brand'] . ' ' . ucfirst($clients['fullname']) ?></a>
                        <?php if ($clients['demand_id'] != 0) { ?>
                            <a href="/admin/pages/demands/zobrazit-poptavku?id=<?= $demand['id'] ?>"><small style="margin-left: 2px; color: #000;"><?php if($clients['reserved'] == 1){ echo '<span style="color: #cc2423; text-decoration: underline;">Rezervace do <strong>'.date('d. m. Y', strtotime($clients['reserved_date'])).'</strong></span>'; }?> » <?= $demand['user_name'] ?></small></a>
                        <?php } elseif($clients['reserved_showroom'] != 0){

                            $location_query = $mysqli->query("SELECT * FROM shops_locations WHERE id = '".$clients['reserved_showroom']."'") or die($mysqli->error);
                            $location = mysqli_fetch_array($location_query);
                            ?>


                            <small class="text-info" style=" margin-top: 2px; color: #0077b1;">
                                Rezervace na showroom » <?= $location['name'] ?></small>

                        <?php } else { ?>
                            <small id="priradit-<?= $clients['id'] ?>" style="margin-left: 2px; cursor:pointer; color: #00a651;">Volná</small>

                        <?php } ?>

                    </h4>

                    <div id="prirazeni-<?= $clients['id'] ?>" class="form-group" style="display:none;float:left;">

                        <form role="form" method="post" name="myform" action="virivky?action=demandchange&id=<?= $clients['id'] ?>">

                            <div class="col-sm-6" style="width: 260px;">
                                <?php
                                $demandsq = $mysqli->query("SELECT user_name, id FROM demands WHERE (customer = 1 OR customer = 3) AND product = '" . $clients['product'] . "' and status <> 5 and status <> 6") or die($mysqli->error);

                                ?>
                                <select name="demand" class="select2" data-allow-clear="true" data-placeholder="Vyberte poptávku...">
                                    <option></option>
                                    <optgroup label="<?= strtoupper($clients['product']) ?> poptávky">
                                        <?php while ($dem = mysqli_fetch_array($demandsq)) {
                                            $find = $mysqli->query("SELECT id FROM warehouse WHERE demand_id = '" . $dem['id'] . "' AND product = '" . $clients['product'] . "'");
                                            if (mysqli_num_rows($find) != 1) { ?><option value="<?= $dem['id'] ?>">»<?= $dem['user_name'] ?></option><?php }}?>
                                    </optgroup>
                                </select>

                            </div>
                            <button style="float: left;margin-left: -9px;    height: 42px;" type="submit" class="btn btn-green"> <i class="entypo-pencil"></i> </button>
                            <a id="cancel-<?= $clients['id'] ?>" style="float: left;margin-left: 4px;    "><button type="button" class="btn btn-white" style="height: 42px;"> <i class="entypo-cancel"></i> </button></a>
                        </form>
                    </div>

                </div>

                <div class="col-sm-4" style="color: #000; width: 200px; margin-left: 30px; margin-top: 7px">
                    <button class=" btn btn-default btn-sm">
                        <?php

                        $now = date("Y-m-d", strtotime("now"));

                        if($clients['loadingdate'] != '0000-00-00'){

                            $dateadd = date("Y-m-d", strtotime($clients['loadingdate']));

                            $delivery_date = date("d. m. y", strtotime($clients['loadingdate']));

                            $date1 = new DateTime($dateadd);
                            $date2 = new DateTime($now);
                            $interval = $date1->diff($date2);
                            $nummero = $interval->days;

                            ?>
                            Termín doručení je <strong><?= $delivery_date ?> (<?= $nummero ?> dnů)</strong>.
                            <?php


                        }elseif ($clients['created_date'] != '0000-00-00' && $clients['status'] == 0) {

                            $dateadd = date("Y-m-d", strtotime("+77 days", strtotime($clients['created_date'])));

                            $dateadd2 = date("Y-m-d", strtotime("+42 days", strtotime($clients['created_date'])));

                            $estimated = date("d. m. y", strtotime("+77 days", strtotime($clients['created_date'])));

                            $correction = date("d. m. y", strtotime("+42 days", strtotime($clients['created_date'])));

                            $date1 = new DateTime($dateadd);
                            $date2 = new DateTime($now);
                            $interval = $date1->diff($date2);
                            $nummero = $interval->days;

                            $date3 = new DateTime($dateadd2);
                            $interval2 = $date3->diff($date2);
                            $nummero2 = $interval2->days;

                            ?>
                            Orientační termín doručení je <strong><?= $estimated ?> (<?= $nummero ?> dnů)</strong>. Orientační termín bude upřesněn do <strong><?= $nummero2 ?> dnů</strong>.

                        <?php } ?>
                    </button>
                </div>
                <div style="clear: both"></div>


            </div>

            <?php if ($access_edit) { ?>
                <div class="col-sm-3" style="float:right;margin-top: 0px;padding-right: 0px; width: 23%;">

                    <a href="zobrazit-saunu?id=<?= $clients['id'] ?>" class="btn btn-default btn-sm btn-icon icon-left">
                        <i class="entypo-search"></i>
                        Zobrazit
                    </a>
                    <span style=" border-right: 1px solid #cccccc; margin: 0 10px 0 5px;"></span>
                    <a href="upravit-saunu?id=<?= $clients['id'] ?>" class="btn btn-primary btn-sm btn-icon icon-left">
                        <i class="entypo-pencil"></i>
                        Upravit
                    </a>
                    <span style=" border-right: 1px solid #cccccc; margin: 0 10px 0 5px;"></span>
                    <a data-id="<?= $clients['id'] ?>" data-type="hottub" class="toggle-modal-remove btn btn-danger btn-sm btn-icon icon-left">
                        <i class="entypo-cancel"></i>
                        Odstranit
                    </a>
                </div>
            <?php } ?>



            <div class="clear"></div>

            <div class="row info-list">


                <?php if (!empty($clients['description'])) { ?>

                    <hr style="float: left;width: 100%;margin-top: 10px; margin-bottom: 10px;">
                    <div class="clear"></div>
                    <div class="alert alert-info" style="margin-right: 20px; margin-bottom: 0;"><i class="entypo-info"></i> <?= $clients['description'] ?></div>

                <?php } ?>
                <hr style="float: left;width: 100%;margin-top: 10px; margin-bottom: 8px;">

                <div class="show-specs btn btn-white btn-sm" style="cursor: pointer;"><i class="entypo-search"></i> zobrazit specifikace u sauny</div>
                <span style=" border-right: 1px solid #cccccc;margin-top: 4px; margin: 0 10px 0 5px;"></span>


                <?php


                $critical_specs_query = $mysqli->query("SELECT * FROM specs s, warehouse_specs_bridge sb WHERE s.id = sb.specs_id AND sb.client_id = '".$clients['id']."' AND (s.id = 5 OR s.id = 1 OR s.id = 2)  GROUP BY s.id ORDER BY s.rank") or die($mysqli->error);
                while($critical_specs = mysqli_fetch_array($critical_specs_query)){

                    ?>
                    <div class="show-specs btn btn-white btn-sm" style="margin-left: 4px; padding: 5px 20px;"><?= $critical_specs['name'] ?>: <strong style="font-weight: 500;"><?= $critical_specs['value'] ?></strong></div>
                <?php } ?>
                <div class="hidden-specs" style="display: none;">

                    <?php if ($clients['demand_id'] != 0) { ?>
                        <table style="width: 100%; float: left; margin-top: 10px;">
                            <?php if (isset($clients['customer']) && $clients['customer'] == 0) {
                                $oldrank = '';
                                $i = 0;
                                $specsquery = $mysqli->query('SELECT * FROM specs WHERE product = 0 AND supplier = 1 order by rank asc') or die($mysqli->error);
                                while ($specs = mysqli_fetch_array($specsquery)) {
                                    $paramsquery = $mysqli->query('SELECT value FROM warehouse_specs_bridge WHERE specs_id = "' . $specs['id'] . '" AND client_id = "' . $clients['id'] . '"') or die($mysqli->error);
                                    $params = mysqli_fetch_array($paramsquery);

                                    $specsdemquery = $mysqli->query('SELECT value FROM demands_specs_bridge WHERE specs_id = "' . $specs['id'] . '" AND client_id = "' . $demand['id'] . '"') or die($mysqli->error);
                                    $demandsspecs = mysqli_fetch_array($specsdemquery);

                                    $i++;

                                    if (isset($specs['rank']) && $specs['rank'] == 0) {$specs['bg_colour'] = '#ace6ce';}
                                    $newrank = $specs['bg_colour'];

                                    if ($newrank != $oldrank) {echo '</table><table style="width: 100%; float: left; margin-top: 10px;">';
                                        $i = 1;}

                                    if ($i == 1) {echo '<tr>';}
                                    ?>

                                    <td style="background-color: <?= $specs['bg_colour'] ?>; color: #000; width: 12.5%; padding: 3px 5px; border-bottom: 1px solid #fff;border-right: 1px solid #fff;"><strong><?= $specs['name'] ?></strong></td>
                                    <td style="background-color: <?= $specs['bg_colour'] ?>;  color: #000; width: 12.5%; padding: 3px 5px; border-bottom: 1px solid #fff; border-right: 4px solid #fff;text-align: center;"><?= !empty($params['value']) ? $params['value'] : '-' ?>
                                        <?php if (isset($demandsspecs) && isset($params) && $demandsspecs['value'] != $params['value']
                                            && $specs['is_demand'] == 1 && $params['value'] != "") {
                                            ?><i style="color: #d42020;font-size: 16px; margin-left: 1px;margin-top: -3px;position: absolute;" class="entypo-attention" data-toggle="tooltip" data-placement="top" title="" data-original-title="Specifikace u vířivky neodpovídá zvolené specifikaci u poptávky."></i><?php } ?>
                                    </td>

                                    <?php

                                    if ($i % 4 == 0) {echo '</tr><tr>';}

                                    $oldrank = $specs['bg_colour'];
                                }}?>
                        </table>

                    <?php } else { ?>

                        <table style="width: 100%; float: left; margin-top: 10px;">
                            <?php if (isset($clients['customer']) && $clients['customer'] == 0) {
                                $oldrank = '';
                                $i = 0;
                                $specsquery = $mysqli->query('SELECT * FROM specs WHERE product = 0 AND supplier = 1 order by rank asc') or die($mysqli->error);
                                while ($specs = mysqli_fetch_array($specsquery)) {
                                    $paramsquery = $mysqli->query('SELECT value FROM warehouse_specs_bridge WHERE specs_id = "' . $specs['id'] . '" AND client_id = "' . $clients['id'] . '"') or die($mysqli->error);
                                    $params = mysqli_fetch_array($paramsquery);

                                    $i++;

                                    if (isset($specs['rank']) && $specs['rank'] == 0) {$specs['bg_colour'] = '#ace6ce';}
                                    $newrank = $specs['bg_colour'];

                                    if ($newrank != $oldrank) {echo '</table><table style="width: 100%; float: left; margin-top: 10px;">';
                                        $i = 1;}

                                    if ($i == 1) {echo '<tr>';}
                                    ?>

                                    <td style="background-color: <?= $specs['bg_colour'] ?>; color: #000; width: 12.5%;padding: 3px 5px; border-bottom: 1px solid #fff;border-right: 1px solid #fff;"><strong><?= $specs['name'] ?></strong></td>
                                    <td style="background-color: <?= $specs['bg_colour'] ?>;  color: #000; width: 12.5%;padding: 3px 5px; border-bottom: 1px solid #fff; border-right: 4px solid #fff;text-align: center;"><?= !empty($params['value']) ? $params['value'] : '-' ?></td>

                                    <?php

                                    if ($i % 4 == 0) { echo '</tr><tr>'; }

                                    $oldrank = $specs['bg_colour'];
                                }}?>
                        </table>

                    <?php } ?>
                    <?/*
    if($access_edit){ ?>
    <hr style="float: left;width: 100%;margin-top: 18px; margin-bottom: 3px;">
    <div class="col-sm-3" style="margin-top: 8px; padding: 0; width: 20%;">
    <i class="entypo-right-open-mini"></i>
    Nákupní cena: <strong><?= number_format($clients['purchase_price'], 0, ',', ' ') ?>,- Kč</strong>
    </div>
    <div class="col-sm-3" style="margin-top: 8px; padding: 0; width: 20%;">
    <i class="entypo-right-open-mini"></i>
    Prodejní cena: <strong><?= number_format($clients['sale_price'], 0, ',', ' ') ?>,- Kč</strong>
    </div>
    <div class="col-sm-3" style="margin-top: 8px; padding: 0; width: 20%;">
    <i class="entypo-right-open-mini"></i>
    Doprava: <strong><?= number_format($clients['delivery_price'], 0, ',', ' ') ?>,- Kč</strong>
    </div>
    <div class="col-sm-3" style="margin-top: 8px; padding: 0; width: 20%;">
    <i class="entypo-right-open-mini"></i>
    Montáž: <strong><?= number_format($clients['montage_price'], 0, ',', ' ') ?>,- Kč</strong>
    </div>
    <div class="col-sm-3" style="margin-top: 8px; padding: 0; width: 20%;">
    <i class="entypo-right-open-mini"></i>
    Zisk: <strong><?= number_format($clients['sale_price']+$clients['delivery_price']+$clients['montage_price']-$clients['purchase_price'], 0, ',', ' ') ?>,- Kč</strong>
    </div><?php }*/?>

                    <?php

                    $orders_products_bridge = $mysqli->query("SELECT * FROM warehouse_products_bridge WHERE warehouse_id = '" . $clients['id'] . "'");

                    if (mysqli_num_rows($orders_products_bridge) > 0) { ?>

                        <hr style="float: left;width: 100%;margin-top: 18px; margin-bottom: 3px;">

                        <div class="col-sm-11" style="margin-top: 8px; width: 100%;">
                            <i class="entypo-tools" style="font-size: 32px; float: left; margin-left: -7%;"></i>

                            <?php

                            while ($bridge = mysqli_fetch_array($orders_products_bridge)) {

                                if ($bridge['variation_id'] != 0) {

                                    ?>

                                    <a href="./zobrazit-prislusenstvi?id=<?= $bridge['product_id'] ?>" target="_blank">
                                        <div class="tile-stats tile-white" style="padding:  10px 10px 8px; float: left; margin-right: 10px;">

                                            <?php

                                            $product_query = $mysqli->query("SELECT *, s.id as ajdee FROM products p, products_variations s WHERE p.id = '" . $bridge['product_id'] . "' AND p.id = s.product_id AND s.id = '" . $bridge['variation_id'] . "'");
                                            $product = mysqli_fetch_array($product_query);

                                            $select = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $product['ajdee'] . "'") or die($mysqli->error);
                                            $desc = "";
                                            while ($var = mysqli_fetch_array($select)) {

                                                $desc = $desc . $var['name'] . ': ' . $var['value'] . ' ';

                                            }

                                            $product_title = $product['productname'] . ' – ' . $desc;
                                            ?>

                                            <span style="color: #000000; font-size: 12px; float: left;"><?= $product_title ?></span>

                                        </div>
                                    </a>
                                    <?php

                                } else {

                                    ?>
                                    <a href="./zobrazit-prislusenstvi?id=<?= $bridge['product_id'] ?>" target="_blank">
                                        <div class="tile-stats tile-white" style="padding:  10px 10px 8px; float: left; margin-right: 10px;">
                                            <?php

                                            $product_query = $mysqli->query("SELECT * FROM products WHERE id = '" . $bridge['product_id'] . "'") or die($mysqli->error);
                                            $product = mysqli_fetch_array($product_query);

                                            $product_title = $product['productname'];

                                            ?>

                                            <span style="color: #000000; font-size: 12px; float: left;"><?= $product_title ?></span>



                                        </div>
                                    </a>
                                    <?php

                                }

                            }

                            ?>
                        </div>
                        <?php

                    }

                    ?>
                </div>

            </div>




        </div>

    </div>



<?php

}

function products($product)
{

    global $mysqli;

    $select_sites = $mysqli->query("SELECT site, price, site_id FROM products_sites WHERE product_id = '" . $product['id'] . "'");

    $select_sites_categories = $mysqli->query("SELECT site, category FROM products_sites_categories WHERE product_id = '" . $product['id'] . "'");

    ?>



<div class="member-entry" style="margin: 0;border-top: 0;box-shadow: none;border-radius: 0;padding: 10px;min-height: 94px;">




					<span id="content-<?= $product['seourl'] ?>">
					<a href="./zobrazit-prislusenstvi?id=<?= $product['id'] ?>" class="member-img" style="width: 60px;">
				<?php

        $path = $_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/small/' . $product['seourl'] . '.jpg';
        if(file_exists($path)){
            $imagePath = '/data/stores/images/small/'.$product['seourl'].'.jpg';
        }else{
            $imagePath = '/data/assets/no-image-7.jpg';
        }
        echo '<img src="'.$imagePath.'" width="60" style="float: left; border: 1px solid #ebebeb;">';


            ?>
					</a>
				<div class="member-details" style="width: 92%;">
				<div class="col-sm-4" style="<?php if (isset($product['type']) && $product['type'] == 'variable') { ?>width: 39%;<?php } else { ?>width: 39%;<?php } ?>">
					<a href="./zobrazit-prislusenstvi?id=<?= $product['id'] ?>"><h3 style="font-size: 14px;margin-top: 6px;text-overflow: ellipsis;overflow: hidden;white-space: nowrap;"><?php if (isset($product['type']) && $product['type'] == 'variable') { ?><i class="entypo-archive tooltip-primary" data-toggle="tooltip" data-placement="top" title="" data-original-title="Produkt s variantami"></i><?php } else { ?><i class="entypo-right-open tooltip-primary" data-toggle="tooltip" data-placement="top" title="" data-original-title="Jednoduchý produkt" style="margin-left: -4px; margin-right: -6px;"></i><?php } ?> <?= $product['productname'] ?> <br>

				</h3></a>


					<?php while ($category = mysqli_fetch_array($select_sites_categories)) {
        if ($category['site'] != 'wellnesstrade') {

            $shop_category_query = $mysqli->query("SELECT name FROM shops_categories WHERE id = '" . $category['category'] . "'");
            $shop_category = mysqli_fetch_array($shop_category_query);

        }
        ?>
<small class="btn btn-default" style="font-size: 11px; margin-top: -1px; margin-right: 2px; padding: 0px 4px; line-height: 15px; background-color: #ffffff; border: 1px solid #ebebeb;"><?= $category['site'] ?></small>
<?php } ?>
<span style="margin-top: 6px; margin-left: 2px; width: 100%; float: left; font-size: 11px; font-weight: 600; color: #797979;">Doba dodání: <?= $product['delivery_time'] ?> dní</span>

				</div>
								<div class="col-sm-2" style="padding:0; <?php if (isset($product['type']) && $product['type'] == 'variable') { ?>width: 18%;<?php } else { ?>width: 18%;<?php } ?>">


				<?php if (isset($product['type']) && $product['type'] == 'variable') { ?>

				  <h3 style="font-size: 12px;float: right;margin-top: 6px;">-</h3>

					<?php } else {

        $locations_query = $mysqli->query("SELECT * FROM shops_locations l LEFT JOIN products_stocks s ON s.location_id = l.id AND s.product_id = '" . $product['id'] . "' WHERE l.type = 'branch' ORDER BY type ASC");

        while ($location = mysqli_fetch_array($locations_query)) {

            $orderquery = $mysqli->query("SELECT r.reserved 
                FROM orders_products_bridge r, orders o 
                WHERE o.id = r.aggregate_id AND o.order_status < '3' AND r.product_id = '" . $product['id'] . "' 
                    AND r.location_id = '" . $location['id'] . "' AND r.aggregate_type = 'order'") or die($mysqli->error);
            $count = 0;
            while ($orderreserved = mysqli_fetch_array($orderquery)) {
                $count = $count + $orderreserved['reserved'];
            }

            $finalreserved = $count;

            $orderquery = $mysqli->query("SELECT r.reserved, r.quantity 
                FROM orders_products_bridge r, orders o 
                WHERE o.id = r.aggregate_id AND r.reserved <> r.quantity AND o.order_status < '3' AND r.product_id = '" . $product['id'] . "' AND r.location_id = '" . $location['id'] . "' AND r.aggregate_type = 'order'") or die($mysqli->error);
            $count = 0;
            while ($ordermissing = mysqli_fetch_array($orderquery)) {
                $subtotal = $ordermissing['quantity'] - $ordermissing['reserved'];
                $count = $count + $subtotal;
            }

            $finalmissing = $count;

            ?>

						<h3 style="font-size: 12px;float: right;margin-top: 6px; width:100%; text-align: right;"><?= $location['name'] ?>:
					<?php if (isset($product['availability']) && $product['availability'] == 0) { ?>

							<span style="color:#0bb668;">[<?= $location['instock'] ?> ks]</span>

						<?php } elseif (isset($product['availability']) && $product['availability'] == 1) { ?>

							<span style="color:#f56954;">Do 14 dní</span>

						<?php } elseif(isset($product['availability']) && $product['availability'] == 3) { ?>

							<span style="color:#7a92a3;">Skryto - [<?= $product['instock'] ?> ks]</span>
						<?php } else { ?>

							<span style="color:#cc2424;">Nedostupné - [<?= $product['instock'] ?> ks]</span>
						<?php }
            if ($finalreserved > 0) { ?>
							<span style="color:#000000;">[<?= $finalreserved ?> ks]</span>
							<?php }
            if ($finalmissing > 0) { ?>
							<span style="color:#d42020;">[-<?= $finalmissing ?> ks]</span>
							<?php } ?>
							</h3>

							<?php
        }

    }?>



				</div>
				<div class="col-sm-3" style=" width: 28%;">

				<?php if (isset($product['type']) && $product['type'] == 'variable') {

        $discount_query = $mysqli->query("SELECT cat.discount FROM products_cats cat, products_sites_categories minicat WHERE minicat.category = cat.seoslug AND minicat.product_id = '" . $product['id'] . "'") or die($mysqli->error);

        $discount = mysqli_fetch_assoc($discount_query);

        $get_prices_query = $mysqli->query("SELECT MIN(price) AS  'lowest' , MAX(price) AS  'highest' FROM products_variations WHERE product_id = '" . $product['id'] . "'") or die($mysqli->error);

        $prices = mysqli_fetch_array($get_prices_query);

        $lowest = $prices['lowest'];

        $highest = $prices['highest'];

        if (isset($discount['discount']) && $discount['discount'] != "" && $discount['discount'] != 0) {

            $percentage = 100 - $discount['discount'];

            $old_lowest = $lowest;

            $lowest = $lowest / 100 * $percentage;

            $old_highest = $highest;

            $highest = $highest / 100 * $percentage;

            $old_price = '<small style="text-decoration: line-through; margin-right: 90px;">' . number_format($old_lowest, 0, ',', ' ') . ' Kč  - ' . number_format($old_highest, 0, ',', ' ') . ' Kč</small><br>';

        }

        ?>

				<h5 style="font-size: 16px; float: right;width: 100%; text-align: right;margin-top: 6px;">
						<span id="price-<?= $product['seourl'] ?>"><?php if (isset($old_price)) {echo $old_price;}?><strong><?= number_format($lowest, 0, ',', ' ') ?></strong></span> Kč  - <span id="price-<?= $product['seourl'] ?>"><strong><?= number_format($highest, 0, ',', ' ') ?></strong></span> Kč
						<small class="btn btn-default" style="font-size: 11px; padding: 1px 4px; margin-top: -4px; line-height: 16px; background-color: #f5f5f6; border: 1px solid #e1e1e1;">všechny eshopy</small>
						</h5>


				<?php } else {

        $product_price = $product['price'];

        $discount_query = $mysqli->query("SELECT cat.discount FROM products_cats cat, products_sites_categories minicat WHERE minicat.category = cat.seoslug AND minicat.product_id = '" . $product['id'] . "'") or die($mysqli->error);

        $discount = mysqli_fetch_assoc($discount_query);

        if (isset($discount['discount']) && $discount['discount'] != "" && $discount['discount'] != 0) {

            $percentage = 100 - $discount['discount'];

            $old_price = '<small style="text-decoration: line-through;">' . number_format($product_price, 0, ',', ' ') . ' Kč</small> ';

            $product_price = $product_price / 100 * $percentage;

        }

        ?>

					<h4 style="float: right;width: 100%; text-align: right;margin-top: 6px;">
						<span id="price-<?= $product['seourl'] ?>"><?php if (isset($old_price)) {echo $old_price;}?><strong><?= number_format($product_price, 0, ',', ' ') ?></strong></span> Kč
						<small class="btn btn-default" style="font-size: 11px; padding: 1px 4px; margin-top: -4px; line-height: 16px; background-color: #f5f5f6; border: 1px solid #e1e1e1;">všechny eshopy</small>
						</h4>


						<?php } ?>

				</div>
				<div class="col-sm-1" style="width: 130px; float: right; padding: 0; text-align: right;">

				<a href="/admin/pages/accessories/zobrazit-prislusenstvi?id=<?= $product['id'] ?>" class="btn btn-default btn-sm">
					<i class="entypo-search"></i>
				</a>

				<a href="/admin/pages/accessories/upravit-prislusenstvi?id=<?= $product['id'] ?>" class="btn btn-primary btn-sm">
					<i class="entypo-pencil"></i>
				</a>

				<a data-id="<?= $product['id'] ?>" class="toggle-modal-stock btn btn-blue btn-sm">
					<i class="entypo-box"></i>
				</a>


				<hr style="margin: 8px 0; width: 120px;">
                <a href="/admin/controllers/stores/#?id=<?= $product['id'] ?>&redirect=true" target="_blank" class="btn btn-success btn-sm" disabled>
					<i class="entypo-upload"></i>
				</a>

                     <!--
				<a data-id="<?= $product['id'] ?>" data-type="product" class="toggle-modal-remove btn btn-danger btn-sm">
					<i class="entypo-cancel"></i>
				</a>-->

				</div>
					</div>
					</span>

		</div>



<?php

}

function service($service)
{

	global $mysqli;

    $address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $service['shipping_id'] . '" WHERE b.id = "' . $service['billing_id'] . '"') or die($mysqli->error);
    $address = mysqli_fetch_assoc($address_query);

        if (!empty($address['billing_surname'])) {

            $fullname = $address['billing_name'] . ' ' . $address['billing_surname'];

        } elseif(!empty($address['billing_company'])) {

            $fullname = $address['billing_company'];

        }else{

            $fullname = 'neznámý zákazník';

        }

        $categoryquery = $mysqli->query('SELECT title FROM services_categories WHERE seoslug = "' . $service['category'] . '"') or die($mysqli->error);

		if(!empty($address['billing_email'])){ $email = $address['billing_email']; }else{ $email = '-'; }
    ?>

<tr class="even">

			<td class=" "><a href="/admin/pages/services/zobrazit-servis?id=<?= $service['id'] ?>"><strong>#<?= $service['id'] ?></strong> od <i><?= $fullname ?></i></a><br>
            <span style="margin-top: 3px; float:left;"><a href="mailto:<?= $email ?>"><?= $email ?></a><?php if (isset($service['shipping_phone']) && $service['shipping_phone'] != "") {$phone = str_replace(' ', '', $service['shipping_phone']);
        $billing_phone = substr($phone, -9);
        echo '<br>' . number_format($billing_phone, 0, ',', ' ');}?></span></td>

    <td style="text-align: center;"><?= $service['date_added'] ?></td>


    <td style="text-align: center;"><?php

        if (isset($service['customertype']) && $service['customertype'] == 0) {echo 'sauna';} elseif (isset($service['customertype']) && $service['customertype'] == 1) {echo 'vířivka';}?></td>
            
            <td style="text-align: center;"><?php

                if(isset($service['showStatus']) && $service['showStatus']){

                    if (isset($service['state']) && $service['state'] == 'new') {
                        $status = 'NOVÝ';
                    }elseif(isset($service['state']) && $service['state'] == 'waiting') {
                        $status = 'ČEKÁ NA DÍKY';
                    }elseif($service['state'] == 'unconfirmed') {
                        $status = 'NEPOTVRZENÝ';
                    }elseif($service['state'] == 'confirmed') {
                        $status = 'POTVRZENÝ';
                    }elseif($service['state'] == 'executed') {
                        $status = 'PROVEDENÝ';
                    }elseif($service['state'] == 'unfinished') {
                        $status = 'NEDOKONČENÝ';
                    }elseif($service['state'] == 'warranty') {
                        $status = 'REKLAMACE';
                    }elseif($service['state'] == 'finished') {
                        $status = 'HOTOVÝ';
                    }elseif($service['state'] == 'canceled') {
                        $status = 'STORNOVANÝ';
                    }elseif($service['state'] == 'problematic') {
                        $status = 'PROBLÉMOVÝ';
                    }elseif($service['state'] == 'invoiced') {
                        $status = 'FAKTUROVANÝ';
                    }

                    echo '<strong>'.$status.'</strong><br>';

                }


                if (isset($service['customertype']) && $service['customertype'] == 1) {


                $category = mysqli_fetch_assoc($categoryquery);

                if(!empty($category['title'])){ echo $category['title'] . '<br>'; }

            } else {echo 'Servis sauny';}?></td>

			<td style="text-align: center;"><?php if($service['dateformated'] != '00. 00. 0000'){ echo '<strong>'.$service['dateformated'].'</strong>'; }else{ echo '-'; } ?></td>
            
            <td>
                <?php if(($service['state'] == 'finished' || $service['state'] == 'unfinished' || $service['state'] == 'executed' || $service['state'] == 'warranty' || $service['state'] == 'problematic' || $service['state'] == 'review') && !empty($service['internal_details'])){

                    echo $service['internal_details'].'<br>';

                }elseif(!empty($service['technical_details'])){

                    echo $service['technical_details'].'<br>';

                }

                echo '<div style="margin-top: 6px;">';

                $admins_query = $mysqli->query("SELECT user_name FROM demands c, mails_recievers t WHERE t.type_id = '".$service['id']."' AND t.admin_id = c.id AND t.type = 'service' AND t.reciever_type = 'performer'");

                if(mysqli_num_rows($admins_query) > 0){
                ?>
                <i class="entypo-tools"></i> Provedité: <strong><?php
                $i = 0;
                while ($admins = mysqli_fetch_assoc($admins_query)) {
                    if ($i == 0) { $i++; echo $admins['user_name']; } else {  echo ', ' . $admins['user_name']; }
                }?></strong>

                <?php } else {
                    echo '<i class="entypo-cancel"></i>žádní proveditelé';
                }

                echo '</div>'; ?>

                </td>
                    
			<td style="text-align: center;">
				<a data-id="<?= $service['id'] ?>" class="toggle-modal-change-state btn btn-blue btn-sm btn-icon icon-left">
					<i class="entypo-bookmarks"></i>
					Změnit stav
				</a>
				<a href="/admin/pages/services/zobrazit-servis?id=<?= $service['id'] ?>" class="btn btn-primary btn-sm btn-icon icon-left">
					<i class="entypo-search"></i>
					Zobrazit
				</a>
				<a data-id="<?= $service['id'] ?>" data-type="service" class="toggle-modal-remove btn btn-danger btn-sm btn-icon icon-left" style="display: none;">
					<i class="entypo-cancel"></i>
					Smazat
				</a>
            </td>
		</tr>

<?php

}

function scateg($scateg)
{

    ?>

<tr class="even">
			<td class=" sorting_1">
				<div class="checkbox checkbox-replace neon-cb-replacement">
					<label class="cb-wrapper"><input type="checkbox" id="chk-1"><div class="checked"></div></label>
				</div>
			</td>
			<td class=" "><?= $scateg['title'] ?></td>
			<td class=" "><?= $scateg['descriptions'] ?></td>
			<td class=" "><?= $scateg['seoslug'] ?></td>
			<td class=" "><?= $scateg['price'] ?></td>
			<td class=" "><?php if (isset($scateg['type']) && $scateg['type'] == "1") {echo "Kč/h";} else {echo "Jednorázový";}?></td>
			<td class=" " style="text-align: right;">

				<a href="upravit-kategorii-servisu?id=<?= $scateg['id'] ?>" class="btn btn-primary btn-sm btn-icon icon-left">
					<i class="entypo-pencil"></i>
					Upravit
				</a>
				<a href="kategorie-servisu?action=remove&id=<?= $scateg['id'] ?>" class="btn btn-danger btn-sm btn-icon icon-left">
					<i class="entypo-cancel"></i>
					Smazat
				</a>
			</td>
		</tr>



<?php

}
function pcateg($pcateg)
{

    ?>

<tr class="even">
			<td class=" sorting_1">
				<div class="checkbox checkbox-replace neon-cb-replacement">
					<label class="cb-wrapper"><input type="checkbox" id="chk-1"><div class="checked"></div></label>
				</div>
			</td>
			<td class=" "><?= $pcateg['name'] ?></td>
			<td class=" " style=" text-align:center;"><?php if (isset($pcateg['customer']) && $pcateg['customer'] == 0) {echo 'Sauny';} else {echo 'Vířivky';}?></td>
			<td class=" " style=" text-align:center;"><strong><?= $pcateg['discount'] ?>%</strong></td>
			<td class=" "><?= $pcateg['seoslug'] ?></td>
			<td class=" " style="text-align: right;">

				<a href="upravit-kategorii-prislusenstvi?id=<?= $pcateg['id'] ?>" class="btn btn-primary btn-sm btn-icon icon-left">
					<i class="entypo-pencil"></i>
					Upravit
				</a>
				<a href="kategorie-prislusenstvi?action=remove&id=<?= $pcateg['id'] ?>" class="btn btn-danger btn-sm btn-icon icon-left">
					<i class="entypo-cancel"></i>
					Smazat
				</a>
			</td>
		</tr>



<?php

}

function postscateg($pcateg)
{

    ?>

<tr class="even">
			<td class=" sorting_1">
				<div class="checkbox checkbox-replace neon-cb-replacement">
					<label class="cb-wrapper"><input type="checkbox" id="chk-1"><div class="checked"></div></label>
				</div>
			</td>
			<td class=" "><?= $pcateg['title'] ?></td>
			<td class=" "><?= $pcateg['seoslug'] ?></td>
			<td class=" "><?php if (isset($pcateg['customer']) && $pcateg['customer'] == 0) {echo 'Sauny';} else {echo 'Vířivky';}?></td>
			<td class=" " style="text-align: right;">

				<a href="upravit-kategorii-clanku?id=<?= $pcateg['id'] ?>" class="btn btn-primary btn-sm btn-icon icon-left">
					<i class="entypo-pencil"></i>
					Upravit
				</a>
				<a href="kategorie-clanku?action=remove&id=<?= $pcateg['id'] ?>" class="btn btn-danger btn-sm btn-icon icon-left">
					<i class="entypo-cancel"></i>
					Smazat
				</a>
			</td>
		</tr>



<?php

}
function manuals($manuals)
{

    ?>

<tr class="even">
			<td class=" sorting_1">
				<div class="checkbox checkbox-replace neon-cb-replacement">
					<label class="cb-wrapper"><input type="checkbox" id="chk-1"><div class="checked"></div></label>
				</div>
			</td>
			<td class=" "><?= $manuals['name'] ?></td>
			<td class=" "><?= $manuals['product'] ?></td>
			<td class=" "><?= $manuals['seoslug'] ?></td>
			<td class=" "><?= $manuals['description'] ?></td>
			<td class=" "><?= $manuals['icon'] ?></td>
			<td class=" " style="text-align: right;">

				<a href="upravit-manual?id=<?= $manuals['id'] ?>" class="btn btn-primary btn-sm btn-icon icon-left">
					<i class="entypo-pencil"></i>
					Upravit
				</a>
				<a href="editace-manualu?action=remove&id=<?= $manuals['id'] ?>" class="btn btn-danger btn-sm btn-icon icon-left">
					<i class="entypo-cancel"></i>
					Smazat
				</a>
			</td>
		</tr>
<?php
}

function mails($mails)
{

    ?>

<tr class="even">
			<td class=" sorting_1">
				<div class="checkbox checkbox-replace neon-cb-replacement">
					<label class="cb-wrapper"><input type="checkbox" id="chk-1"><div class="checked"></div></label>
				</div>
			</td>
			<td class=" "><?= $mails['title'] ?></td>
			<td class=" "><?php if (isset($mails['customer']) && $mails['customer'] == 0) {echo "sauny";} elseif (isset($mails['customer']) && $mails['customer'] == 1) {echo "vířivky";} elseif (isset($mails['customer']) && $mails['customer'] == 3) {echo "sauny a vířivky";} elseif (isset($mails['customer']) && $mails['customer'] == 9) {echo "textové (na úpravu)";}?></td>
			<td class=" "><?= $mails['type'] ?></td>
			<td class=" " style="text-align: right;">

				<a href="upravit-mail?id=<?= $mails['id'] ?>" class="btn btn-primary btn-sm btn-icon icon-left">
					<i class="entypo-pencil"></i>
					Upravit
				</a>
				<a href="mailove-sablony?action=remove&id=<?= $mails['id'] ?>" class="btn btn-danger btn-sm btn-icon icon-left">
					<i class="entypo-cancel"></i>
					Smazat
				</a>
			</td>
		</tr>
<?php
}

function custom_echo($x, $y)
{
    if (strlen($x) <= $y) {
        echo $x;
    } else {
        $y = substr($x, 0, $y) . '...';
        echo $y;
    }
}


function filename($name)
{
      $utf8 = array(
        '/[áàâãªä]/u'   =>   'a',
        '/[ÁÀÂÃÄ]/u'    =>   'A',
        '/[ÍÌÎÏ]/u'     =>   'I',
        '/[íìîï]/u'     =>   'i',
        '/[éèêë]/u'     =>   'e',
        '/[ÉÈÊË]/u'     =>   'E',
        '/[óòôõºö]/u'   =>   'o',
        '/[ÓÒÔÕÖ]/u'    =>   'O',
        '/[úùûü]/u'     =>   'u',
        '/[ÚÙÛÜ]/u'     =>   'U',
        '/ç/'           =>   'c',
        '/Ç/'           =>   'C',
        '/ñ/'           =>   'n',
        '/Ñ/'           =>   'N',
        '/–/'           =>   '-', // UTF-8 hyphen to "normal" hyphen
        '/[’‘‹›‚]/u'    =>   ' ', // Literally a single quote
        '/[“”«»„]/u'    =>   ' ', // Double quote
        '/ /'           =>   ' ', // nonbreaking space (equiv. to 0x160)
    );
    return preg_replace(array_keys($utf8), array_values($utf8), $name);
}


function odkazy($title)
{

    static $convertTable = array(

        'á' => 'a', 'Á' => 'A', 'ä' => 'a', 'Ä' => 'A', 'è' => 'c',

        'È' => 'C', 'ï' => 'd', 'Ï' => 'D', 'é' => 'e', 'É' => 'E',

        'ì' => 'e', 'Ì' => 'E', 'ě' => 'e', 'Ě' => 'E', 'í' => 'i',

        'Í' => 'I', 'i' => 'i', 'I' => 'I', '¾' => 'l', '¼' => 'L',

        'å' => 'l', 'Å' => 'L', 'ň' => 'n', 'Ň' => 'N', 'ñ' => 'n',

        'Ñ' => 'N', 'ó' => 'o', 'Ó' => 'O', 'ö' => 'o', 'Ö' => 'O',

        'ř' => 'r', 'Ř' => 'R', 'à' => 'r', 'À' => 'R', 'š' => 's',

        'Š' => 'S', 'Č' => 'C', 'č' => 'c', 'œ' => 's', 'Œ' => 'S', '' => 't', '' => 'T',

        'ú' => 'u', 'ů' => 'u', 'Ú' => 'U', 'ù' => 'u', 'Ù' => 'U', 'ü' => 'u',

        'Ü' => 'U', 'ý' => 'y', 'Ý' => 'Y', 'y' => 'y', 'Y' => 'Y',

        'ž' => 'z', 'Ž' => 'Z', 'Ÿ' => 'z', '' => 'Z', '´' => '',

    );

    $title = strtolower(strtr($title, $convertTable));

    $title = preg_replace('/[^a-zA-Z0-9]+/u', '-', $title);

    $title = str_replace('--', '-', $title);

    $title = trim($title, '-');

    return $title;

}

function remove_diacritics($text){

    $convertTable = Array(
        'ä'=>'a',
        'Ä'=>'A',
        'á'=>'a',
        'Á'=>'A',
        'à'=>'a',
        'À'=>'A',
        'ã'=>'a',
        'Ã'=>'A',
        'â'=>'a',
        'Â'=>'A',
        'č'=>'c',
        'Č'=>'C',
        'ć'=>'c',
        'Ć'=>'C',
        'ď'=>'d',
        'Ď'=>'D',
        'ě'=>'e',
        'Ě'=>'E',
        'é'=>'e',
        'É'=>'E',
        'ë'=>'e',
        'Ë'=>'E',
        'è'=>'e',
        'È'=>'E',
        'ê'=>'e',
        'Ê'=>'E',
        'í'=>'i',
        'Í'=>'I',
        'ï'=>'i',
        'Ï'=>'I',
        'ì'=>'i',
        'Ì'=>'I',
        'î'=>'i',
        'Î'=>'I',
        'ľ'=>'l',
        'Ľ'=>'L',
        'ĺ'=>'l',
        'Ĺ'=>'L',
        'ń'=>'n',
        'Ń'=>'N',
        'ň'=>'n',
        'Ň'=>'N',
        'ñ'=>'n',
        'Ñ'=>'N',
        'ó'=>'o',
        'Ó'=>'O',
        'ö'=>'o',
        'Ö'=>'O',
        'ô'=>'o',
        'Ô'=>'O',
        'ò'=>'o',
        'Ò'=>'O',
        'õ'=>'o',
        'Õ'=>'O',
        'ő'=>'o',
        'Ő'=>'O',
        'ř'=>'r',
        'Ř'=>'R',
        'ŕ'=>'r',
        'Ŕ'=>'R',
        'š'=>'s',
        'Š'=>'S',
        'ś'=>'s',
        'Ś'=>'S',
        'ť'=>'t',
        'Ť'=>'T',
        'ú'=>'u',
        'Ú'=>'U',
        'ů'=>'u',
        'Ů'=>'U',
        'ü'=>'u',
        'Ü'=>'U',
        'ù'=>'u',
        'Ù'=>'U',
        'ũ'=>'u',
        'Ũ'=>'U',
        'û'=>'u',
        'Û'=>'U',
        'ý'=>'y',
        'Ý'=>'Y',
        'ž'=>'z',
        'Ž'=>'Z',
        'ź'=>'z',
        'Ź'=>'Z'
    );

    return strtr($text, $convertTable);

}

function documentshome($documentsmanuals)
{

    if (isset($documentsmanuals['type']) && $documentsmanuals['type'] == "manual") {
        $urltype = "manualy";} else {
        $urltype = "upravit-smlouvu";
    }
    ?>


 <div class="col-sm-3">
	<a href="/admin/pages/documents/<?= $urltype ?>?id=<?= $documentsmanuals['id'] ?>" target="_blank">
		<div class="tile-title tile-gray">

			<div class="icon">
				<i class="<?= $documentsmanuals['icon'] ?>"></i>
			</div>

			<div class="title">
				<h3><?= $documentsmanuals['name'] ?></h3>
				<p><?= $documentsmanuals['description'] ?></p>
			</div>
		</div>
	</a>
</div>


<?php }

function consumption($consumption, $secretstring, $is_client)
{

    global $mysqli;
    ?>

<tr class="even">
  <td class=" "><a href="/admin/pages/accessories/zobrazit-dodavku?id=<?= $consumption['id'] ?>"><strong>#<?= $consumption['id'] ?></strong></a></td>



  <td>
    <div id="contenthidden-<?= $consumption['id'] ?>" style="height: 60px; overflow:hidden;">
      <?php

    $quantity = 0;
    $nummeroproducts = 0;
    $product_id_query = $mysqli->query('SELECT product_id, variation_id, quantity, reserved FROM consumption_products_bridge WHERE order_id = "' . $consumption['id'] . '" order by id desc') or die($mysqli->error);

    while ($product_id = mysqli_fetch_array($product_id_query)) {
        $productquery = $mysqli->query('SELECT * FROM products WHERE id="' . $product_id['product_id'] . '"') or die($mysqli->error);
        $product = mysqli_fetch_assoc($productquery);

        $variationname = "";
        if (isset($product['type']) && $product['type'] == 'variable') {
            $variation_query = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $product_id['variation_id'] . "'");

            while ($variation = mysqli_fetch_array($variation_query)) {

                if ($variationname == "") {
                    $variationname = $variation['name'] . ': ' . $variation['value'];
                } else {

                    $variationname = $variationname . ', ' . $variation['name'] . ': ' . $variation['value'];

                }

            }

            $variationname = "&nbsp; <small style='font-size: 11px'>" . $variationname . "</small>";

        }




        $path = PRODUCT_IMAGE_PATH.'/small/' . $product['seourl'] . '.jpg';
        if(file_exists($path)){
            $imagePath = '/data/stores/images/small/'.$product['seourl'].'.jpg';
        }else{
            $imagePath = '/data/assets/no-image-7.jpg';
        }

        ?>
          <a href="../accessories/zobrazit-prislusenstvi?id=<?= $product['id'] ?>" target="_blank" class="btn btn-md btn-white">
            <img data-html="true" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="" data-original-title="<?= $product_id['quantity'] ?>x <?= $product['productname'] . $variationname ?>" src="<?= $imagePath ?>" width="40"  style="border: 1px solid #ebebeb; " class="popover-primary"> <?= $product_id['quantity'] ?>x <?= $product['productname'] . $variationname ?>
          </a><br>
      <?php }?>

      </div>
    </td>

    <td class="text-center"><span><?= $consumption['recieved_date'] ?></span></td>


  <td>
    <?php

    $location_query = $mysqli->query('SELECT name FROM shops_locations WHERE id = "' . $consumption['location_id'] . '"') or die($mysqli->error);

    while ($location = mysqli_fetch_array($location_query)) {

        echo $location['name'];
    }?>

  </td>



  <td>
    <h4 style="font-size: 14px; margin-bottom: 0;text-align: right; font-family: inherit; font-weight: normal; line-height: 1.1;">-</h4>
   </td>

</tr>


<?php }

function supply($supply, $secretstring, $is_client)
{

    global $mysqli;
    ?>

<tr class="even">
	<td class=" "><a href="/admin/pages/accessories/zobrazit-dodavku?id=<?= $supply['id'] ?>"><strong>#<?= $supply['id'] ?></strong></a></td>



	<td>
		<div id="contenthidden-<?= $supply['id'] ?>" style="height: 60px; overflow:hidden;">
			<?php
    $quantity = 0;
    $nummeroproducts = 0;
    $product_id_query = $mysqli->query('SELECT product_id, variation_id, quantity, reserved FROM products_supply_bridge WHERE supply_id = "' . $supply['id'] . '" order by id desc') or die($mysqli->error);

    while ($product_id = mysqli_fetch_array($product_id_query)) {
        $productquery = $mysqli->query('SELECT * FROM products WHERE id="' . $product_id['product_id'] . '"') or die($mysqli->error);
        $product = mysqli_fetch_assoc($productquery);

        $variationname = "";
        if (isset($product['type']) && $product['type'] == 'variable') {
            $variation_query = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $product_id['variation_id'] . "'");

            while ($variation = mysqli_fetch_array($variation_query)) {

                if ($variationname == "") {
                    $variationname = $variation['name'] . ': ' . $variation['value'];
                } else {

                    $variationname = $variationname . ', ' . $variation['name'] . ': ' . $variation['value'];

                }

            }

            $variationname = "&nbsp; <small style='font-size: 11px'>" . $variationname . "</small>";

        }

        $path = PRODUCT_IMAGE_PATH.'/small/' . $product['seourl'] . '.jpg';
        if(file_exists($path)){
            $imagePath = '/data/stores/images/small/'.$product['seourl'].'.jpg';
        }else{
            $imagePath = '/data/assets/no-image-7.jpg';
        }

        ?>
		<a href="../accessories/zobrazit-prislusenstvi?id=<?= $product['id'] ?>" target="_blank">
			<img data-html="true" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="<?= $product_id['quantity'] ?>x <?= $product['productname'] . $variationname ?>" data-original-title="<?= $product_id['quantity'] ?>x <?= $product['productname'] . $variationname ?>" src="<?= $imagePath ?>" style="border: 1px solid #ebebeb;" width="40"  class="popover-primary">
		</a>
	<?php

    }?>

</div>
				</td>

  <td>
    <?php if (isset($supply['status']) && $supply['status'] == 0) {echo '<strong>Neobjednaná</strong>';} elseif (isset($supply['status']) && $supply['status'] == 1) {echo '<strong>Objednaná</strong>';} elseif (isset($supply['status']) && $supply['status'] == 2) {echo '<strong>Na cestě</strong>';} elseif (isset($supply['status']) && $supply['status'] == 3) {echo '<strong>Přijatá</strong>';}?>

  </td>

    <td class="text-center"><span><?= $supply['recieved_date'] ?></span></td>


	<td>
		<?php

    $location_query = $mysqli->query('SELECT name FROM shops_locations WHERE id = "' . $supply['location_id'] . '"') or die($mysqli->error);

    while ($location = mysqli_fetch_array($location_query)) {

        echo $location['name'];
    }?>

	</td>



	<td>
		<h4 style="font-size: 14px; margin-bottom: 0;text-align: right; font-family: inherit; font-weight: normal; line-height: 1.1;">-</h4>
   </td>

	<td style="text-align: center;">



		<a href="/admin/pages/accessories/zobrazit-dodavku?id=<?= $supply['id'] ?>" class="btn btn-primary btn-sm btn-icon icon-left">
					<i class="entypo-search"></i>
					Zobrazit
				</a></td>

</tr>


<?php }

function ordersnew($order, $secretstring, $is_client)
{

    global $mysqli;

    $address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $order['shipping_id'] . '" WHERE b.id = "' . $order['billing_id'] . '"') or die($mysqli->error);
    $address = mysqli_fetch_assoc($address_query);

    if ($order['client_id'] != 0) {

        $clientnameq = $mysqli->query('SELECT user_name, id, email FROM demands WHERE id="' . $order['client_id'] . '"') or die($mysqli->error);
        $clientname = mysqli_fetch_assoc($clientnameq);
        $fullname = $clientname['user_name'];

    } else {

        if (isset($address['billing_surname']) && $address['billing_surname'] != '') {$fullname = $address['billing_name'] . ' ' . $address['billing_surname'];} else { $fullname = $address['billing_company'];}

    }

    $email = $address['billing_email'];

    ?>

<tr class="even">
	<td class=" "><a href="/admin/pages/orders/zobrazit-objednavku?id=<?= $order['id'] ?>"><strong>#<?= $order['id'] ?></strong> od <i><?php

    if ((isset($clientname) && $clientname['user_name'] != "") || (isset($email) && $email != "")) {

        echo $fullname;

    } else {

        echo 'zákazník na prodejně';

    }

    ?></i></a><br>
		<span style="margin-top: 3px; float:left;"><a href="mailto:<?= $email ?>"><?= $email ?></a><?php if (isset($address['billing_phone']) && $address['billing_phone'] != "") {$phone = str_replace(' ', '', $address['billing_phone']);
        $billing_phone = substr($phone, -9);
        echo '<br>' . number_format((float)$billing_phone, 0, ',', ' ');}?></span></td>


		<td class="text-center">
		<?php

        if (isset($order['order_status']) && $order['order_status'] == 0) {

            echo '<span class="circle-color red"></span> <i>Nezpracovaná</i>';

        } elseif (isset($order['order_status']) && $order['order_status'] == 1) {

            echo '<span class="circle-color orange"></span> <i>V řešení</i>';

        } elseif (isset($order['order_status']) && $order['order_status'] == 2) {

            echo '<span class="circle-color blue"></span> <i>Připravená</i>';

        } elseif (isset($order['order_status']) && $order['order_status'] == 3) {

            echo '<span class="circle-color green"></span> <i>Vyexpedovaná</i>';

        } else {

            echo '<span class="circle-color black"></span> <i>Stornovaná</i>';

        }
        echo '<br>'.$order['order_site']; ?>

		</td>
	<td>
		<div id="contenthidden-<?= $order['id'] ?>" style="height: 60px; overflow:hidden;">
			<?php
    $quantity = 0;
    $finalprice = 0;
    $i = 0;
    $productidquery = $mysqli->query("SELECT product_id, variation_id, quantity, reserved 
        FROM orders_products_bridge WHERE aggregate_id = '" . $order['id'] . "' AND aggregate_type = 'order' order by id desc") or die($mysqli->error);

    while ($productid = mysqli_fetch_array($productidquery)) {

        $productquery = $mysqli->query("SELECT * FROM products WHERE id = '" . $productid['product_id'] ."'") or die($mysqli->error);
        $product = mysqli_fetch_assoc($productquery);

        $overallprice = $product['price'] * $productid['quantity'];
        $finalprice = $finalprice + $overallprice;
        $reserved = 0;

        if ($productid['reserved'] != $productid['quantity']) {

            $reserved = $productid['quantity'] - $productid['reserved'];

        }

        $variationname = "";
        if (isset($product['type']) && $product['type'] == 'variable') {

            $variation_query = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $productid['variation_id'] . "'");

            while ($variation = mysqli_fetch_array($variation_query)) {

                if ($variationname == "") {
                    $variationname = $variation['name'] . ': ' . $variation['value'];
                } else {

                    $variationname = $variationname . ', ' . $variation['name'] . ': ' . $variation['value'];

                }

            }

            $variationname = "&nbsp; <small style='font-size: 11px'>" . $variationname . "</small>";


            $path = PRODUCT_IMAGE_PATH.'/small/' . $product['seourl'] . '_variation_'.$productid['variation_id'].'.jpg';
            $path_product = PRODUCT_IMAGE_PATH.'/small/' . $product['seourl'] . '.jpg';

            if(file_exists($path)){
                $imagePath = '/data/stores/images/small/'.$product['seourl'].'_variation_'.$productid['variation_id'].'.jpg';
            }elseif(file_exists($path_product)){
                $imagePath = '/data/stores/images/small/'.$product['seourl'].'.jpg';
            }else{
                $imagePath = '/data/assets/no-image-7.jpg';
            }

        } else {

            $path = PRODUCT_IMAGE_PATH.'/small/' . $product['seourl'] . '.jpg';
            if(file_exists($path)){
                $imagePath = '/data/stores/images/small/'.$product['seourl'].'.jpg';
            }else{
                $imagePath = '/data/assets/no-image-7.jpg';
            }

        }

        if ($reserved > 0) {
            $border = 'border: 1px dashed #ff0000';
        } else {
            $border = 'border: 1px solid #ebebeb';
        }

        ?>
		<a href="../accessories/zobrazit-prislusenstvi?id=<?= $product['id'] ?>" target="_blank">
            <img
                style="<?= $border ?>"
                src="<?= $imagePath ?>"
                width="50"
                data-html="true"
                data-toggle="popover"
                data-trigger="hover"
                data-placement="top"
                data-content="<?php if ($productid['reserved'] != 0) {
                    ?><?= $productid['reserved'] ?>x rezervováno<?php }
                    if ($productid['reserved'] != 0 && isset($reserved) && $reserved != "") { ?> - <?php }
                    if (isset($reserved) && $reserved != "") { ?><?= $reserved ?>x chybí<?php } ?>"
                data-original-title="<?= $productid['quantity'] ?>x <?= $product['productname'] . $variationname ?>"
                class="popover-primary" />
		</a>
	<?php
        $quantity = $quantity + $productid['quantity'];

        $i++;
        if(mysqli_num_rows($productidquery) > 7 && $i == 6) {


            $diff = mysqli_num_rows($productidquery) - $i;

           echo '<a href="/admin/pages/orders/zobrazit-objednavku?id='.$order['id'].'" class="btn btn-white" style="cursor: pointer;padding: 3px 12px; border: 0;">
					+ '.$diff.' další
				</a>';

            break;


        }
    }
    $finalprice = $finalprice + $order['delivery_price'];?>

</div>
				</td>

	<td>

		<?php if ($address['shipping_city'] != "" || $address['billing_city'] != "") { ?>
			<a style="font-size: 13px;" href="https://maps.google.com/maps?&q=<?php address($address);?>" target="_blank"><?php address($address);?></a>
		<?php } else { ?>
			žádná adresa
		<?php } ?>
		<br>
	    <span style="float:left; margin-top: 4px;"><?php if(isset($order['ship_method'])){ echo $order['ship_method']; } ?></td>

	<td class="text-center"><span><?= $order['dateformated'] ?></span><br><span style="color: #303641;-webkit-opacity: 0.8;-moz-opacity: 0.8;opacity: 0.8;filter: alpha(opacity=80);font-size: 12px;"><?= $order['hoursmins'] ?></span></td>

	<td>
		<?php

        $currency = currency($order['order_currency']);

        $check_invoice = $mysqli->query("SELECT * FROM orders_invoices WHERE order_id = '".$order['id']."'")or die($mysqli->error);

        if(mysqli_num_rows($check_invoice) > 0){
            $invoice = mysqli_fetch_assoc($check_invoice);

//
//// todo
//            global $client;
//
//            if($client['id'] == 2126){
//
//                if ($invoice['paid'] != 0) {
//
//                    echo payment_status($invoice);
//
//                } else {
//
//                    $payment = check_payment($invoice, 'order');
//
//                    echo '<span style=" font-size: 13px; ' . $payment['color'] . '">' . $payment['info'] . '</span>';
//                }
//
//                echo 'rer';
//
//            }
// todo end

        }else{
            $invoice['id'] = 99999;
        }

        $order_date = date("Y-m-d", strtotime($order['order_date']));





        // if bankwire
        if($order['payment_method'] == 'bacs'){

            $bank_sum_query = $mysqli->query("SELECT SUM(value) as total FROM bank_transactions WHERE account = 'order' AND (vs = '".$order['id']."' OR manual_assign = '".$order['id']."' OR vs = '".$invoice['id']."') AND date >= '".$order_date."'")or die($mysqli->error);
            $bank_sum = mysqli_fetch_assoc($bank_sum_query);

            if (isset($bank_sum['total']) && $bank_sum['total'] != '0') {

                if (isset($order['paid_value']) && $bank_sum['total'] == $order['total']) {

                    $payment_info = '<i class="entypo-check"></i> zaplaceno';
                    $color = 'color: #00a651';

                } else {

                    $payment_info = '<i class="entypo-block"></i> problém: '.thousand_seperator($bank_sum['total'] - $order['total']).$currency['sign'];;
                    $color = 'color: #d42020;';

                }

            }else{

                $payment_info = '<i class="entypo-back-in-time"></i> čeká na platbu';
                $color = 'color: #ff9600;';

            }


        }elseif($order['payment_method'] == 'agmobindercardall' || $order['payment_method'] == 'agmobinderbank'){

            // check comgate
            $comgate_query = $mysqli->query("SELECT * FROM transactions_comgate WHERE id = '".$order['transaction_id']."'")or die($mysqli->error);

            if(mysqli_num_rows($comgate_query) > 0){
            $comgate = mysqli_fetch_assoc($comgate_query);


                if ($comgate['status'] == 'PAID' && $comgate['value'] == $order['total']) {

                    $payment_info = '<i class="entypo-check"></i> comgate: zaplaceno';
                    $color = 'color: #00a651';

                } elseif ($comgate['status'] == 'PAID' && $comgate['value'] != $order['total']) {

                    $payment_info = '<i class="entypo-block"></i>comgate: problém: '. thousand_seperator($comgate['value'] - $order['total']).$currency['sign'];
                    $color = 'color: #d42020;';

                } elseif ($comgate['status'] == 'PENDING') {

                    $payment_info = '<i class="entypo-back-in-time"></i>comgate: čeká na platbu';
                    $color = 'color: #ff9600;';

                } elseif ($comgate['status'] == 'CANCELLED') {

                    $payment_info = '<i class="entypo-trash"></i>comgate: stornovaná';
                    $color = 'color: #000;';

                }else{
                    $payment_info = '-';
                    $color = 'color: #373e4a;';
                }


            }else{

                $payment_info = '-';
                $color = 'color: #373e4a;';

            }

        }else{

            $payment_info = '-';
            $color = 'color: #373e4a;';

        }

        ?>
        <span style="font-size: 13px; margin-bottom: 4px; text-align: right; float: right; width: 100%;font-family: inherit; font-weight: normal; line-height: 1.1; <?= $color ?>"><?= $payment_info ?>
                    </span>
        <h4 style="font-size: 14px; margin-bottom: 0;text-align: right; font-family: inherit; font-weight: normal; line-height: 1.1; <?= $color ?>"><strong style="font-size: 14px;"><?= thousand_seperator($order['total']) ?></strong> <?= $currency['sign'] ?></h4>
        <span style="float:right; margin-top: 4px;"><?php if(isset($order['pay_method'])){ echo $order['pay_method']; } ?></span>
    </td>

	<td style="text-align: center;">
		<a data-id="<?= $order['id'] ?>" class="toggle-modal-change-status btn btn-blue btn-sm" style="margin-bottom:4px;">
					<i class="entypo-bookmarks"></i>
				</a>

		<a href="/admin/pages/orders/zobrazit-objednavku?id=<?= $order['id'] ?>" class="btn btn-primary btn-sm  ">
					<i class="entypo-search"></i>

				</a>
				<a data-id="<?= $order['id'] ?>" data-type="order" class="toggle-modal-remove btn btn-danger btn-sm btn-icon icon-left" style="display: none;">
					<i class="entypo-cancel"></i>
					Smazat
				</a></td>

</tr>


<?php }

function remove_modal($link, $ajdd, $text, $remove_button, $title)
{ ?>



<div class="modal fade" id="remove-modal-<?= $ajdd ?>" aria-hidden="true" style="display: none; margin-top: 160px;">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

				<h4 class="modal-title"><?= $title ?></h4> </div>

			<div class="modal-body">
				<?= $text ?>
			</div>
<div class="modal-footer" style="text-align:left;"> <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>

	<a href="<?= $link ?>" style="float:right;"><button type="button" class="btn btn-green"><?= $remove_button ?></button></a> </div>
</div> </div> </div>



<?php }

function task($demandstasks, $avatar, $access_edit, $redirect)
{

    global $mysqli;

    ?>

<script type="text/javascript">


jQuery(document).ready(function($)
{

$('.choosio').click(function() {
   if($("input:radio[id='not_choosed']").is(":checked")) {


  $('#demands_who').hide( "slow");
  $('#clients_who').hide( "slow");
   }

     if($("input:radio[id='choosed_demand']").is(":checked")) {

    $('#clients_who').hide( "slow");
    $('#demands_who').show( "slow");

   }

    if($("input:radio[id='choosed_client']").is(":checked")) {


    $('#demands_who').hide( "slow");
    $('#clients_who').show( "slow");
   }

});

});
</script>

<?php

    if ($demandstasks['demand_id'] != 0) {

        $demandquery = $mysqli->query('SELECT id, user_name FROM demands WHERE id="' . $demandstasks['demand_id'] . '"') or die($mysqli->error);
        $demand = mysqli_fetch_assoc($demandquery);

    } elseif ($demandstasks['client_id'] != 0) {

        $get_client_query = $mysqli->query('SELECT id, user_name FROM demands WHERE id="' . $demandstasks['client_id'] . '"') or die($mysqli->error);
        $get_client = mysqli_fetch_assoc($get_client_query);

    }

    $date1 = new DateTime('',new DateTimeZone('Europe/London'));
    $date2 = new DateTime($demandstasks['due']);

    $date1Formated = $date1->format('Y-m-d');
    $date2Formated = $date2->format('Y-m-d');

    $interval = $date1->diff($date2);

    $requestorquery = $mysqli->query('SELECT user_name FROM demands WHERE id="' . $demandstasks['request_id'] . '"') or die($mysqli->error);
    $requestor = mysqli_fetch_assoc($requestorquery);

    $performersQuery = $mysqli->query('SELECT t.admin_id, c.user_name FROM mails_recievers t, demands c WHERE t.type_id = "' . $demandstasks['id'] . '" AND t.admin_id = c.id AND t.type = "task" AND t.reciever_type = "performer"') or die($mysqli->error);

    $observersQuery = $mysqli->query('SELECT t.admin_id, c.user_name FROM mails_recievers t, demands c WHERE t.type_id = "' . $demandstasks['id'] . '" AND t.admin_id = c.id AND t.type = "task" AND t.reciever_type = "observer"') or die($mysqli->error);

    ?>



			<div class="panel panel-default" style="margin-bottom: 13px;  ">

				<div class="panel-heading" style="background-color: #fbfbfb;">

						<h4 class="panel-title" style="width: 100%; padding-top: 14px; height: 44px; border-bottom: 1px solid #ebebeb; margin-bottom: 10px;">


		<span style="float:left; font-size: 14px; font-weight: 500;"><span class="text-info"><a class="text-info" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $requestor['user_name'] ?>"><?php acronym($requestor['user_name']);?></a></span> <i class="entypo-right-open" style="margin-left: -6px;margin-right: -6px;"></i>
			<span class="text-danger">
				<?php
				$i = 0;
				while ($performer = mysqli_fetch_assoc($performersQuery)) {

				    if ($i > 0) {  echo '</span> & <span class="text-danger">'; }

				    ?><a class="text-danger"  data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $performer['user_name'] ?>"><?= acronym($performer['user_name']) ?></a><?php

				    $i = $i + 1;

				} ?></span>
				<a href="/admin/pages/tasks/zobrazit-ukol?id=<?= $demandstasks['id'] ?>"> - <?= $demandstasks['title'] ?></a></span>
				<div class="story-type" style="font-size: 11px;float:right;margin-right: -6px;">

						<a style="display: none;" data-toggle="collapse" data-parent="#prijate-ukoly" href="#flag-<?= $demandstasks['id'] ?>"><i class="entypo-flag" style="margin-right: 2px;"></i></a>
						<a style="display: none;" data-toggle="collapse" data-parent="#prijate-ukoly" href="#comments-<?= $demandstasks['id'] ?>"><i class="fa fa-comments" style="margin-right: 2px;"></i></a>


							<a data-toggle="collapse" data-parent="#prijate-ukoly" href="#edit-<?= $demandstasks['id'] ?>"><i data-toggle="tooltip" data-placement="top" title="" data-original-title="Upravit" class="entypo-pencil"></i></a>


							<span style="margin-left: 3px; margin-right: 5px; border-right: 1px solid #cccccc;"></span>

							<a href="/admin/controllers/task-controller?task=remove&taskid=<?= $demandstasks['id'] ?>&redirect=<?= $redirect ?>"><i data-toggle="tooltip" data-placement="top" title="" data-original-title="Smazat" class="entypo-trash" style="margin-right: 2px;"></i></a>
						</div>
					</h4>

					<p style="padding: 9px 24px 12px; font-size: 13px;"><?php if ($demandstasks['demand_id'] != 0) {echo '<a href="/admin/pages/demands/zobrazit-poptavku?id=' . $demand['id'] . '" target="_blank" style="margin-top: -2px; margin-bottom: -8px;display: block;"><strong>Poptávka - ' . $demand['user_name'] . '</strong></a><br>';} if ($demandstasks['text'] != "") {echo $demandstasks['text'];} else {echo "žádný popis.";}?></p>

				<h4 class="panel-title" style="width: 100%; border-top: 1px solid #ebebeb; padding-top: 14px; padding-bottom: 13px;">
					<div style="font-size: 12px;">
						<a style="display: none;" data-toggle="collapse" data-parent="#prijate-ukoly" href="#flag-<?= $demandstasks['id'] ?>"><?php if (isset($demandstasks['status']) && $demandstasks['status'] == 0) { ?>
						<span style="margin-right: 12px; color: #727272;"><i class="entypo-flag" style="padding-right: 2px;"></i>Úkol čeká</span>
						<?php } elseif (isset($demandstasks['status']) && $demandstasks['status'] == 1) { ?>
						<span style="margin-right: 12px; color: #d42020;"><i class="entypo-flag" style="padding-right: 2px;"></i>Úkol odmítnut</span>
						<?php } elseif (isset($demandstasks['status']) && $demandstasks['status'] == 2) { ?>
						<span style="margin-right: 12px; color: #0072bc;"><i class="entypo-flag" style="padding-right: 2px;"></i>Úkol v řešení</span>
						<?php } elseif (isset($demandstasks['status']) && $demandstasks['status'] == 3) { ?>
						<span style="margin-right: 12px; color: #04a500;"><i class="entypo-flag" style="padding-right: 2px;"></i>Úkol splněn</span>
						<?php } ?></a>

						<?php
						$remains = $interval->days;

    if ($date1Formated == $date2Formated) { ?>
								<strong class="text-danger">Splnit dnes!</strong>
							<?php } elseif ($date1 > $date2) { ?>
						<a data-toggle="tooltip" data-placement="top" title="" class="text-danger" data-original-title="Mělo být splněno před <?= $remains ?> <?php if ($remains == 1) { ?>dnem<?php } else { ?>dny<?php } ?>">

							<i class="entypo-calendar" style="padding-right: 2px;"></i>

							<?php if(isset($demandstasks['dueformated'])){ echo $demandstasks['dueformated'];}  if(isset($demandstasks['time']) && $demandstasks['time'] != '00:00:00') {echo ' <i class="entypo-clock" style="padding-left: 8px;"></i> ' . $demandstasks['time'];} ?>

							<?php } else {

        if ($remains == 1) {$days = ' den!';} elseif ($remains < 5) {$days = ' dny!';} else { $days = ' dní';}

        ?>

						<span data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= 'Splnit za ' . $remains . $days ?>">

							<i class="entypo-calendar" style="padding-right: 2px;"></i>

							<?php echo $demandstasks['dueformated'];
							if (isset($demandstasks['time']) && $demandstasks['time'] != '00:00:00') {
							    echo ' <i class="entypo-clock" style="padding-left: 8px;"></i> ' . $demandstasks['time'];
							}
							?>

							<?php } ?>

						</span>

						<span style=" color: #000; font-weight: bold; margin-left: 16px; margin-right: 16px;"><i class="entypo-forward" style="padding-right: 2px;"></i>Stav: <?php if ($demandstasks['status'] == 0) {echo 'Neřešeno';} elseif ($demandstasks['status'] == 1) {echo 'V řešení';} elseif ($demandstasks['status'] == 3) {echo 'Splněno';}?></span>




							Změna stavu:

						<a href="/admin/controllers/task-controller?task=change&taskid=<?= $demandstasks['id'] ?>&status=0&redirect=<?= $redirect ?>" style=""><span style="<?php if ($demandstasks['status'] == 0) {echo 'text-decoration: underline; font-weight: bold;';}?>color: #000;cursor: pointer;"><i class="entypo-cancel" style="padding-right: 2px;"></i>Neřešeno</span></a>

						<a href="/admin/controllers/task-controller?task=change&taskid=<?= $demandstasks['id'] ?>&status=1&redirect=<?= $redirect ?>" style="margin-left: 12px;"><span style="<?php if ($demandstasks['status'] == 1) {echo 'text-decoration: underline; font-weight: bold;';}?> color: #0072bc;cursor: pointer;"><i class="entypo-tools" style="padding-right: 2px;"></i>V řešení</span></a>

						<a href="/admin/controllers/task-controller?task=change&taskid=<?= $demandstasks['id'] ?>&status=3&redirect=<?= $redirect ?>" style="margin-left: 12px;"><span style="<?php if ($demandstasks['status'] == 3) {echo 'text-decoration: underline; font-weight: bold;';}?> color: #04a500;cursor: pointer;"><i class="fa fa-check" style="padding-right: 2px;"></i>Splněno</span></a>



							<span style="float:right;">

Informovaní:
<?php
				$i = 0;
				while ($observer = mysqli_fetch_assoc($observersQuery)) {

                if ($i > 0) {  echo ' & '; }

				    ?><a class="text-success"  data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $observer['user_name'] ?>"><?= acronym($observer['user_name']) ?></a><?php

				$i = $i + 1;

				} ?>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

						<span style="display: none;"><i class="entypo-calendar" style="padding-right: 2px;"></i>Od <?= $demandstasks['dateformated'] ?></span>

					</div>

                </h4>
				</div>
					<div id="flag-<?= $demandstasks['id'] ?>" class="panel-collapse collapse">
					<div class="panel-body">
						<div style="font-size: 13px;float:left;font-weight: 500;padding-top: 3px;">
						<a href="/admin/controllers/task-controller?task=change&taskid=<?= $demandstasks['id'] ?>&status=0&redirect=<?= $redirect ?>"><span style="margin-left:10px;margin-right: 28px; color: #727272;cursor: pointer;"><i class="entypo-flag" style="padding-right: 2px;"></i>Úkol čeká</span></a>

						<a href="/admin/controllers/task-controller?task=change&taskid=<?= $demandstasks['id'] ?>&status=1&redirect=<?= $redirect ?>"><span style="margin-right: 28px; color: #d42020;cursor: pointer;"><i class="entypo-flag" style="padding-right: 2px;"></i>Úkol odmítnut</span></a>

						<a href="/admin/controllers/task-controller?task=change&taskid=<?= $demandstasks['id'] ?>&status=2&redirect=<?= $redirect ?>"><span style="margin-right: 28px; color: #0072bc;cursor: pointer;"><i class="entypo-flag" style="padding-right: 2px;"></i>Úkol v řešení</span></a>

						</div>
					</div>
				</div>
				<div id="edit-<?= $demandstasks['id'] ?>" class="panel-collapse collapse">
				<form role="form" method="post" autocomplete="off" enctype='multipart/form-data' action="/admin/controllers/task-controller?task=edit&taskid=<?= $demandstasks['id'] ?>&redirect=<?= $redirect ?>" style="padding: 10px;" >

							<input type="text" style="width: 50%; float: left; margin-right: 0.5%;margin: 0 0 10px 0;" name="title" value="<?= $demandstasks['title'] ?>" placeholder="Krátký název úkolu" class="form-control" id="field-1">

			<input type="text" style="width: 49.5%; float: left; margin: 0 0 10px 0;" name="datum" value="<?= $demandstasks['due'] ?>" class="form-control datepicker" data-format="yyyy-mm-dd" placeholder="Datum provedení">



            <div class="well admins_well" style="padding: 12px 0px 7px; width: 49.5%; margin-right: 1%;  float: left;">
            <h4 style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">Proveditelé</h4>
            <?php
    $adminsQuery = $mysqli->query("SELECT id, user_name FROM demands WHERE role != 'client' AND active = 1 AND active = 1");

    while ($admin = mysqli_fetch_array($adminsQuery)) {

        $find_query = $mysqli->query("SELECT * FROM mails_recievers WHERE type_id = '" . $demandstasks['id'] . "' AND admin_id = '" . $admin['id'] . "' AND type = 'task' AND reciever_type = 'performer'") or die($mysqli->error);
        ?>
               <div class="col-sm-4" style="padding: 0 4px 0 10px;">
                  <input id="<?= $demandstasks['id'] ?>-admin-<?= $admin['id'] ?>-performer" name="performer[]" value="<?= $admin['id'] ?>" type="checkbox" <?php if (mysqli_num_rows($find_query) > 0) {echo 'checked';}?>>
                  <label for="<?= $demandstasks['id'] ?>-admin-<?= $admin['id'] ?>-performer" style="padding-left: 4px; cursor: pointer;"><?= $admin['user_name'] ?></label>
               </div>
            <?php
    }?>
          </div>


          <div class="well admins_well" style="padding: 12px 0px 7px; width: 49.5%;  float: left;">
          <h4 style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">Informovaní</h4>
            <?php
    $adminsQuery = $mysqli->query("SELECT id, user_name FROM demands WHERE role != 'client' AND active = 1 AND active = 1");

    while ($admin = mysqli_fetch_array($adminsQuery)) {

        $find_query = $mysqli->query("SELECT * FROM mails_recievers WHERE type_id = '" . $demandstasks['id'] . "' AND admin_id = '" . $admin['id'] . "' AND type = 'task' AND reciever_type = 'observer'") or die($mysqli->error);
        ?>
               <div class="col-sm-4" style="padding: 0 4px 0 10px;">
                  <input id="<?= $demandstasks['id'] ?>-admin-<?= $admin['id'] ?>-observer" name="observer[]" value="<?= $admin['id'] ?>" type="checkbox" <?php if (mysqli_num_rows($find_query) > 0) {echo 'checked';}?>>
                  <label for="<?= $demandstasks['id'] ?>-admin-<?= $admin['id'] ?>-observer" style="padding-left: 4px; cursor: pointer;"><?= $admin['user_name'] ?></label>
               </div>
            <?php
    }?>
          </div>


            <div class="form-group specialformus" style="float: left; width: 100%; margin-bottom: 0;">
          <?php

    if ($demandstasks['demand_id'] != 0) {

        $demandsq = $mysqli->query("SELECT user_name, id, customer FROM demands WHERE id = '" . $demandstasks['demand_id'] . "'") or die($mysqli->error);
        $dem = mysqli_fetch_array($demandsq);
        ?>
              <h4>Poptávka <?= $dem['user_name'] ?></h4>

          <input type="text" style="display: none;" name="demandus" value="<?= $demandstasks['demand_id'] ?>">

          <?php

    } else {

        ?>


             <?php
        $demandsq = $mysqli->query("SELECT user_name, id, customer FROM demands WHERE status != 6") or die($mysqli->error);
        ?>
            <select id="choosepoptavka" name="demandus" class="select2" data-allow-clear="true" data-placeholder="Přiřadit úkol k poptávce..."  style="width: 100% !important; margin: 10px 0 10px 0;">
                <option></option>
                  <?php while ($dem = mysqli_fetch_array($demandsq)) { ?><option value="<?= $dem['id'] ?>>"  <?php if ($dem['id'] == $demandstasks['demand_id']) {echo 'selected';}?>><?php if ($dem['customer'] == 0) {echo 'S - ';} elseif ($dem['customer'] == 1) {echo 'V - ';} else {echo 'SV - ';}
            echo $dem['user_name'];?></option><?php } ?>
            </select>


          <?php } ?>

</div>

			<textarea class="form-control" name="text" placeholder="Popis úkolu." style="width: 100%;overflow: hidden; margin-bottom: 8px;word-wrap: break-word; resize: horizontal; height: 100px;"><?= $demandstasks['text'] ?></textarea>
			<button type="submit" class="btn btn-primary" style="    width: 79.8%;height: 71px; margin-bottom: 8px;  font-size: 17px;">Upravit úkol</button>
			<a data-toggle="collapse" data-parent="#prijate-ukoly" href="#edit-<?= $demandstasks['id'] ?>"><button type="button" class="btn btn-default" style="width: 19.5%; height: 71px; margin-bottom: 8px;  font-size: 17px;"><i class="entypo-cancel"></i></button></a>
		</form></div>

			</div>
<?php

}


function saveCalendarEvent($id, $category){

    global $mysqli;
    global $client;

    $check_duplicity = $mysqli->query("SELECT id 
        FROM webhooks_calendar 
        WHERE CAST(finished as DATE) = '0000-00-00' 
            AND retries < 4 
            AND event_id = '".$id."'
            AND category = '".$category."'
            ")or die($mysqli->error);

    if(mysqli_num_rows($check_duplicity) == 0){

        $mysqli->query("INSERT INTO webhooks_calendar (event_id, category, editor_id, created) VALUES ('".$id."', '".$category."', '".$client['id']."', CURRENT_TIMESTAMP())")or die($mysqli->error);

    }else{

        $finded_result = mysqli_fetch_assoc($check_duplicity);

        $mysqli->query("UPDATE webhooks_calendar SET editor_id = '".$client['id']."' WHERE id = '".$finded_result['id']."'")or die($mysqli->error);

    }

}




function datumCesky(string $date): string
{
    $men = [
        'January', 'February', 'March', 'April', 'May',
        'June', 'July', 'August', 'September', 'October',
        'November', 'December'
    ];

    $mcz = [
        'leden', 'únor', 'březen', 'duben', 'květen',
        'červen', 'červenec', 'srpen', 'září', 'říjen',
        'listopad', 'prosinec'
    ];

    $date = str_replace($men, $mcz, $date);

    $den = [
        'Monday', 'Tuesday', 'Wednesday', 'Thursday',
        'Friday', 'Saturday', 'Sunday'
    ];

    $dcz = [
        'Pondělí', 'Úterý', 'Středa', 'Čtvrtek',
        'Pátek', 'Sobota', 'Neděle'
    ];

    return str_replace($den, $dcz, $date);
}




?>