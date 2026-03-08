<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$categorytitle = "Poptávky";
$pagetitle = "Přidat poptávku";

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "add") {

    if (($_POST['billing_email'] != "" || $_POST['billing_phone'] != "") && $_POST['secretstring'] != "" && $_POST['optionsRadios'] != "") {

        $billing_zipcode = preg_replace('/\s+/', '', $_POST['billing_zipcode']);
        $billing_phone = preg_replace('/\s+/', '', $_POST['billing_phone']);
        $billing_email = preg_replace('/\s+/', '', $_POST['billing_email']);

        $shipping_zipcode = preg_replace('/\s+/', '', $_POST['shipping_zipcode']);
        $shipping_phone = preg_replace('/\s+/', '', $_POST['shipping_phone']);
        $shipping_email = preg_replace('/\s+/', '', $_POST['shipping_email']);

        if ($_POST['billing_name'] != '' || $_POST['billing_surname'] != '') {

            $user_name = $_POST['billing_name'] . ' ' . $_POST['billing_surname'];

        } elseif ($_POST['billing_name'] != '' || $_POST['billing_surname'] != '') {

            $user_name = $_POST['billing_name'] . ' ' . $_POST['billing_surname'];

        } elseif ($_POST['billing_company'] != '') {

            $user_name = $_POST['billing_company'];

        } else {

            $user_name = $_POST['shipping_company'];

        }

        $getcustomer = $_POST['optionsRadios'];
        if ($getcustomer == "virivka") {

            $customer = "1";
            $product = $_POST['virivkatype'];
            $product2 = "";

        } elseif ($getcustomer == "sauna") {

            $customer = "O";
            $product = $_POST['saunatype'];
            $product2 = "";

        } elseif ($getcustomer == "both") {

            $customer = "3";
            $product = $_POST['virivkatype'];

            $product2 = $_POST['saunatype'];

        } elseif ($getcustomer == "pergola") {

            $customer = "4";
            $product = $_POST['pergolatype'];
            $product2 = "";

        }

		$shipping_degree = '';
        if (isset($_POST['radio_shipping_degree'])) {

            $shipping_degree = $_POST['shipping_degree'];

        }

		$billing_degree = '';
        if (isset($_POST['radio_billing_degree'])) {

            $billing_degree = $_POST['billing_degree'];

        }

		$billing_id = 0;
        $insert_billing = $mysqli->query("INSERT INTO addresses_billing (billing_company, billing_ico, billing_dic, billing_degree, billing_name, billing_surname, billing_street, billing_city, billing_zipcode, billing_country, billing_phone, billing_email, billing_phone_prefix) VALUES ('" . $_POST['billing_company'] . "', '" . $_POST['billing_ico'] . "', '" . $_POST['billing_dic'] . "', '" . $billing_degree . "', '" . $_POST['billing_name'] . "', '" . $_POST['billing_surname'] . "', '" . $_POST['billing_street'] . "', '" . $_POST['billing_city'] . "', '" . $billing_zipcode . "', '" . $_POST['billing_country'] . "', '" . $billing_phone . "', '" . $billing_email . "',  '" . $_POST['billing_phone_prefix'] . "')") or die($mysqli->error);

        $billing_id = $mysqli->insert_id;

		$shipping_id = 0;
        if ($_POST['shipping_company'] != '' || $_POST['shipping_name'] != '' || $_POST['shipping_surname'] != '' || $_POST['shipping_street'] != '' || $_POST['shipping_city'] != '' || $_POST['shipping_zipcode'] != '' || $_POST['shipping_ico'] != '' || $_POST['shipping_dic'] != '') {

            $insert_shipping = $mysqli->query("INSERT INTO addresses_shipping (shipping_company, shipping_ico, shipping_dic, shipping_degree, shipping_name, shipping_surname, shipping_street, shipping_city, shipping_zipcode, shipping_country, shipping_phone, shipping_email, shipping_phone_prefix) VALUES ('" . $_POST['shipping_company'] . "', '" . $_POST['shipping_ico'] . "', '" . $_POST['shipping_dic'] . "', '" . $shipping_degree . "', '" . $_POST['shipping_name'] . "', '" . $_POST['shipping_surname'] . "', '" . $_POST['shipping_street'] . "', '" . $_POST['shipping_city'] . "', '" . $shipping_zipcode . "', '" . $_POST['shipping_country'] . "', '" . $shipping_phone . "', '" . $shipping_email . "',  '" . $_POST['shipping_phone_prefix'] . "')") or die($mysqli->error);

            $shipping_id = $mysqli->insert_id;

        }

        $mysqli->query("INSERT INTO demands (user_name, billing_id, shipping_id, creator, admin_id, showroom, description, email,  customer, date, product, secondproduct, phone, status, secretstring, distance, rating, phone_prefix)
VALUES ('" . $user_name . "', '" . $billing_id . "', '" . $shipping_id . "', '" . $client['id'] . "', '" . $_POST['admin_id'] . "','" . $_POST['showroom'] . "','" . $mysqli->real_escape_string($_POST['description']) . "', '" . $billing_email . "', '$customer', CURRENT_TIMESTAMP(),'$product','$product2','$billing_phone','1','" . $_POST['secretstring'] . "','" . $_POST['distance'] . "', '" . $_POST['rating'] . "',  '" . $_POST['billing_phone_prefix'] . "')") or die($mysqli->error);

        $id = $mysqli->insert_id;

        if ($getcustomer == "virivka" || $getcustomer == "both" || $getcustomer == 'pergola') {

            $choosed_hottub = $product;

            $choosed_type = $_POST['provedeni_' . $choosed_hottub];

            $get_ids = $mysqli->query("SELECT w.id as id, w.name as name FROM warehouse_products_types w, warehouse_products p WHERE w.warehouse_product_id = p.id AND p.connect_name = '$choosed_hottub' AND w.seo_url = '$choosed_type'") or die($mysqli->error);
            $get_id = mysqli_fetch_array($get_ids);

            ///provedení
            $mysqli->query("INSERT INTO demands_specs_bridge (value, client_id, specs_id) VALUES ('" . $get_id['name'] . "','$id','5')") or die($mysqli->error);
            ///provedení

            // DEMAND SPECS

            $specs_query = $mysqli->query("SELECT *, s.id as id FROM specs s, warehouse_products_types_specs w WHERE w.spec_id = s.id AND w.type_id = '" . $get_id['id'] . "' AND s.is_demand = 1 GROUP BY s.id") or die($mysqli->error);

            while ($specs = mysqli_fetch_array($specs_query)) {

                $seoslug = $specs['seoslug'];

                $spec_value = $_POST[$choosed_hottub . '_' . $choosed_type . '_' . $seoslug];

                $mysqli->query("INSERT INTO demands_specs_bridge (value, client_id, specs_id) VALUES ('$spec_value','$id','" . $specs['id'] . "')") or die($mysqli->error);

            }

            // END DEMAND SPECS

            // NOT DEMAND SPECS

            $specs_query = $mysqli->query("SELECT *, s.id as id FROM specs s, warehouse_products_types_specs w WHERE w.spec_id = s.id AND w.type_id = '" . $get_id['id'] . "' AND s.is_demand = 0 GROUP BY s.id") or die($mysqli->error);

            while ($specs = mysqli_fetch_array($specs_query)) {

                if (isset($specs['type']) && $specs['type'] == 1) {

                    $paramsquery = $mysqli->query("SELECT *, p.id as id FROM specs_params p, warehouse_products_types_specs w  WHERE p.spec_id = '" . $specs['id'] . "' AND w.spec_param_id = p.id AND w.type_id = '" . $get_id['id'] . "' AND w.choosed = 1 GROUP by p.id") or die($mysqli->error);

                    $param = mysqli_fetch_array($paramsquery);

                    $value = $param['option'];

                } else {

                    $paramsquery = $mysqli->query("SELECT * FROM warehouse_products_types_specs WHERE spec_id = '" . $specs['id'] . "' AND type_id = '" . $get_id['id'] . "' AND choosed = 1 order by spec_param_id desc") or die($mysqli->error);

                    $param = mysqli_fetch_array($paramsquery);

                    if (isset($param['spec_param_id']) && $param['spec_param_id'] == 1) {$value = 'Ano';} else { $value = 'Ne';}

                }

                $find_query = $mysqli->query("SELECT id FROM demands_specs_bridge WHERE client_id = '" . $id . "' AND specs_id = '" . $specs['id'] . "'") or die($mysqli->error);
                if (mysqli_num_rows($find_query) > 0) {

                    $find = mysqli_fetch_array($find_query);
                    $mysqli->query("UPDATE demands_specs_bridge SET value = '$value' WHERE id = '" . $find['id'] . "'") or die($mysqli->error);

                } else {

                    $mysqli->query("INSERT INTO demands_specs_bridge (value, client_id, specs_id) VALUES ('$value','" . $id . "','" . $specs['id'] . "')") or die($mysqli->error);

                }

            }

            // END NOT DEMAND SPECS

        }

        $post = array_filter($_POST['contact_name']);

        foreach ($post as $post_index => $posterino) {

            $contact_name = $posterino;
            $contact_role = $_POST['contact_role'][$post_index];
            $contact_phone = $_POST['contact_phone'][$post_index];
            $contact_email = $_POST['contact_email'][$post_index];

            $insert = $mysqli->query("INSERT INTO demands_contacts (demand_id, name, role, phone, email) VALUES ('$id', '$contact_name', '$contact_role', '$contact_phone', '$contact_email')") or die($mysqli->error);

        }

        header('location: https://www.wellnesstrade.cz/admin/pages/demands/editace-poptavek?success=add');
        exit;
    } else {

        $displayerror = true;
        $errorhlaska = "Klient NEBYL úspěšně přidán.";

    }}

$saunyquery = $mysqli->query("SELECT * FROM warehouse_products WHERE customer = 0 ORDER BY code asc, fullname asc");

$virivkyquery = $mysqli->query("SELECT * FROM warehouse_products WHERE customer = 1 ORDER BY brand");


include VIEW . '/default/header.php';

?>
<script type="text/javascript">

function randomPassword(length) {
    var chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOP1234567890";
    var pass = "";
    for (var x = 0; x < length; x++) {
        var i = Math.floor(Math.random() * chars.length);
        pass += chars.charAt(i);
    }
    return pass;
}

function generate() {
    myform.secretstring.value = randomPassword(14);
}

jQuery(document).ready(function($)
{

$('.radio').click(function() {

    if($("input:radio[class='saunaradio']").is(":checked")) {
        $('.tajtl').hide( "slow");
        $('.virivkens').hide( "slow");
        $('.pergoly').hide( "slow");
        $('.saunkens').show( "slow");
    }

    if($("input:radio[class='virivkaradio']").is(":checked")) {
        $('.tajtl').hide( "slow");
        $('.saunkens').hide( "slow");
        $('.pergoly').hide( "slow");
        $('.virivkens').show( "slow");

    }

    if($("input:radio[class='bothradio']").is(":checked")) {
        $('.pergoly').hide( "slow");
        $('.tajtl').show( "slow");
        $('.saunkens').show( "slow");
        $('.virivkens').show( "slow");
    }

    if($("input:radio[class='pergolaradio']").is(":checked")) {
        $('.tajtl').hide( "slow");
        $('.saunkens').hide( "slow");
        $('.virivkens').hide( "slow");
        $('.pergoly').show( "slow");
    }

});

 myform.secretstring.value = randomPassword(14);



 $('.radio_billing_degree_switch').on('switch-change', function () {

 if($('.radio_billing_degree').prop('checked')){

 	$('.billing_degree').show("slow");
 	$('.billing_degree').focus();

   }else if(!$('.radio_billing_degree').prop('checked')){


 	$('.billing_degree').hide("slow");
 }

});


 $('.radio_shipping_degree_switch').on('switch-change', function () {

 if($('.radio_shipping_degree').prop('checked')){

 	$('.shipping_degree').show("slow");
 	$('.shipping_degree').focus();

   }else if(!$('.radio_shipping_degree').prop('checked')){


 	$('.shipping_degree').hide("slow");
 }

});

});

</script>



    <script type="text/javascript">

        toastr.options.positionClass = 'toast-top-full-width';
        toastr.options.timeOut = 7000;
        toastr.options.extendedTimeOut = 1000;
        toastr.options.closeButton = true;
        toastr.options.showEasing = 'swing';
        toastr.options.hideEasing = 'linear';
        toastr.options.showMethod = 'fadeIn';
        toastr.options.hideMethod = 'fadeOut';
        toastr.options.progressBar = true;

        $(document).on('submit', '#demand_form', function(event) {

            if($("input[name='billing_email']").val() == '' && $("input[name='billing_phone']").val() == ''){

                $("input[name='billing_email'], input[name='billing_phone']").closest('.form-group').removeClass('has-success').addClass('has-error');

                toastr.error('Musí být zadáno telefonní číslo nebo e-mail.');
                event.preventDefault();


            }else{

                $("input[name='billing_email'], input[name='billing_phone']").closest('.form-group').removeClass('has-error').addClass('has-success');

            }

            var hasContent = false;
            $(".shipping-required").each(function() {

                if ($(this).val() != '') {
                    hasContent = true;
                }

            });

            var isEmpty = false;
            if(hasContent){

                $(".shipping-required").each(function() {

                    if ($(this).val() == '') {
                        isEmpty = true;

                        $(this).closest('.form-group').removeClass('has-success').addClass('has-error');

                    }else{

                        $(this).closest('.form-group').removeClass('has-error').addClass('has-success');


                    }

                })

            }

            if(isEmpty){
                toastr.error('Chybí některé z položek doručovací adresy.');
                event.preventDefault();
            }


        });
    </script>

<form role="form" method="post" id="demand_form" name="myform" class="form-horizontal form-groups-bordered needs-validation" enctype="multipart/form-data" action="pridat-poptavku?action=add" novalidate>


	<div class="row">

		<div class="col-md-12">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Přidat poptávku
					</div>

				</div>

						<div class="panel-body">

					<div class="form-group">
							<div class="col-sm-6">
								<textarea class="form-control autogrow" id="field-ta" name="description" placeholder="Informace prodejce" style="padding: 20px 18px;"></textarea>
							</div>
							<label for="field-1" class="col-sm-2 control-label">Rating zákazníka</label>

							<div class="col-sm-4">
								<div style="margin: 6px 0 4px;">
									<input id="rating_0" name="rating" value="0" type="radio" style="cursor: pointer;" checked/>
									<label for="rating_0" style="padding-left: 6px; cursor: pointer;">-</label>
								</div>

								<div style="margin-bottom: 2px;">
									<input id="rating_1" name="rating" value="1" type="radio" style="cursor: pointer;"/>
									<label for="rating_1" style="padding-left: 6px; cursor: pointer;">
										<img src="/admin/assets/images/star_2.png" style="padding-bottom: 4px;">
									</label>
								</div>

								<div style="margin-bottom: 2px;">
									<input id="rating_2" name="rating" value="2" type="radio" style="cursor: pointer;"/>
									<label for="rating_2" style="padding-left: 6px; cursor: pointer;">
										<img src="/admin/assets/images/star_2.png" style="padding-bottom: 4px;">
										<img src="/admin/assets/images/star_2.png" style="padding-bottom: 4px;">
									</label>
								</div>

								<div style="margin-bottom: 2px;">
									<input id="rating_3" name="rating" value="3" type="radio" style="cursor: pointer;"/>
									<label for="rating_3" style="padding-left: 6px; cursor: pointer;">
										<img src="/admin/assets/images/star_2.png" style="padding-bottom: 4px;">
										<img src="/admin/assets/images/star_2.png" style="padding-bottom: 4px;">
										<img src="/admin/assets/images/star_2.png" style="padding-bottom: 4px;">
									</label>
								</div>

								<div style="margin-bottom: 2px;">
									<input id="rating_4" name="rating" value="4" type="radio" style="cursor: pointer;"/>
									<label for="rating_4" style="padding-left: 6px; cursor: pointer;">
										<img src="/admin/assets/images/star_2.png" style="padding-bottom: 4px;">
										<img src="/admin/assets/images/star_2.png" style="padding-bottom: 4px;">
										<img src="/admin/assets/images/star_2.png" style="padding-bottom: 4px;">
										<img src="/admin/assets/images/star_2.png" style="padding-bottom: 4px;">
									</label>
								</div>

								<div style="margin-bottom: 2px;">
									<input id="rating_5" name="rating" value="5" type="radio" style="cursor: pointer;"/>
									<label for="rating_5" style="padding-left: 6px; cursor: pointer;">
										<img src="/admin/assets/images/star_2.png" style="padding-bottom: 4px;">
										<img src="/admin/assets/images/star_2.png" style="padding-bottom: 4px;">
										<img src="/admin/assets/images/star_2.png" style="padding-bottom: 4px;">
										<img src="/admin/assets/images/star_2.png" style="padding-bottom: 4px;">
										<img src="/admin/assets/images/star_2.png" style="padding-bottom: 4px;">
									</label>
								</div>

							</div>
						</div>


					<hr>

		<div class="col-md-6">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Fakturační údaje
					</div>

				</div>

						<div class="panel-body">


                            <div class="form-group">
                                <label for="field-2" class="col-sm-3 control-label">E-mail</label>

                                <div class="col-sm-8">
                                    <input  type="text" name="billing_email" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="field-2" class="col-sm-3 control-label">Telefon</label>

                                    <div class="col-sm-6">
                                        <select type="select" name="billing_phone_prefix" class="form-control" style="width: 30%; float: left;">
                                            <?php
                                            foreach($phone_prefixes as $prefix){ ?>
                                                <option value="<?= $prefix['id'] ?>"><?= $prefix['name'] ?></option>
                                            <?php } ?>
                                        </select>
                                        <input type="text" name="billing_phone" class="form-control contact" id="field-2" style="width: 70%; float: left;">
                                    </div>

                            </div>

                            <hr>

                            <div class="form-group">
                                <label for="billing_ico" class="col-sm-2 control-label">IČO</label>

                                <div class="col-sm-4" style="padding: 0;">
                                    <input type="text" name="billing_ico" class="form-control" id="billing_ico" value=""  style="float: left; width: 75%;">
                                    <a class="ares-load btn-md btn btn-primary" style="float: right; width: 20%; padding: 6px;"><i class="entypo-download"></i></a>
                                </div>

                                <label class="col-sm-1 control-label">DIČ</label>
                                <div class="col-sm-3">
                                    <input type="text" name="billing_dic" class="form-control" id="field-2" value="">
                                </div>
                            </div>

					<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Firma</label>

						<div class="col-sm-8">
							<input type="text" name="billing_company" class="form-control" value="">
						</div>
					</div>

					<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Titul</label>

						<div class="col-sm-5">
							<div class="radio_billing_degree_switch make-switch switch-small" style="float: left; margin-right:20px; margin-top: 3px;" data-on-label="<i class='entypo-check'></i>" data-off-label="<i class='entypo-cancel'></i>">
								<input class="radio_billing_degree" name="radio_billing_degree" value="nah" type="checkbox"/>
							</div>
							<input class="billing_degree form-control" type="text" name="billing_degree" style="display: none; width: 33%; float:left;" value="">
						</div>
					</div>

					<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Jméno</label>

						<div class="col-sm-8">
							<input type="text" name="billing_name" class="form-control">
						</div>
					</div>
					<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Příjmení</label>

						<div class="col-sm-8">
							<input type="text" name="billing_surname" class="form-control">
						</div>
					</div>


					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Ulice</label>

						<div class="col-sm-8">
							<input  type="text" name="billing_street" class="form-control" >
						</div>
					</div>
					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Město</label>

						<div class="col-sm-8">
							<input  type="text" name="billing_city" class="form-control" >
						</div>
					</div>
					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">PSČ</label>

						<div class="col-sm-3">
							<input type="text" name="billing_zipcode" class="form-control" id="field-2">
						</div>
						 <label class="col-sm-1 control-label">Země</label>
						  <div class="col-sm-4"> <select id="optionus" name="billing_country" class="form-control"> <option value="czech">Česká republika</option> <option value="slovakia">Slovensko</option> <option value="austria">Rakousko</option> <option value="germany">Německo</option></select>  </div>
					</div>




				<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Vzdálenost</label>

						<div class="col-sm-8">
							<input type="text" name="distance" style="width: 300px; float: left;" class="form-control" >&nbsp;
							<span class="input-group-addon" style="float:left; padding: 9px 25px 8px 9px;">km</span>
						</div>


					</div>





							</div>
						</div>
					</div>

				<div class="col-md-6">

							<div class="panel panel-primary" data-collapsed="0">

								<div class="panel-heading">
									<div class="panel-title">
										Jiné doručovací údaje
									</div>

								</div>

										<div class="panel-body">


                                            <div class="form-group">
                                                <label for="field-2" class="col-sm-3 control-label">E-mail</label>

                                                <div class="col-sm-8">
                                                    <input  type="text" name="shipping_email" class="form-control" required>

                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="field-2" class="col-sm-3 control-label">Telefon</label>
                                                <div class="col-sm-6">
                                                    <select type="select" name="shipping_phone_prefix" class="form-control" style="width: 30%; float: left;">
                                                        <?php
                                                        foreach($phone_prefixes as $prefix){ ?>
                                                            <option value="<?= $prefix['id'] ?>"><?= $prefix['name'] ?></option>
                                                        <?php } ?>
                                                    </select>

                                                        <input type="text" name="shipping_phone" class="form-control contact" id="field-2" style="width: 70%; float: left;">
                                                    </div>
                                            </div>
                                            <hr>

					<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Firma</label>

						<div class="col-sm-8">
							<input type="text" name="shipping_company" class="form-control">
						</div>
					</div>
					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">IČO</label>

						<div class="col-sm-3">
							<input type="text" name="shipping_ico" class="form-control" id="field-2">
						</div>

							 <label class="col-sm-2 control-label">DIČ</label>
						<div class="col-sm-3">
							<input type="text" name="shipping_dic" class="form-control" id="field-2">
						</div>
					</div>

					<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Titul</label>

						<div class="col-sm-5">
							<div class="radio_shipping_degree_switch make-switch switch-small" style="float: left; margin-right:20px; margin-top: 3px;" data-on-label="<i class='entypo-check'></i>" data-off-label="<i class='entypo-cancel'></i>">
								<input class="radio_shipping_degree" name="radio_shipping_degree" value="nah" type="checkbox"/>
							</div>
							<input class="shipping_degree form-control" type="text" name="shipping_degree" style="display: none; width: 33%; float:left;" value="">
						</div>
					</div>

						<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Jméno</label>

						<div class="col-sm-8">
							<input type="text" name="shipping_name" class="form-control">
						</div>
					</div>
					<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Příjmení</label>

						<div class="col-sm-8">
							<input type="text" name="shipping_surname" class="form-control">
						</div>
					</div>


					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Ulice *</label>

						<div class="col-sm-8">
							<input  type="text" name="shipping_street" class="form-control shipping-required" >
						</div>
					</div>
					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Město *</label>

						<div class="col-sm-8">
							<input  type="text" name="shipping_city" class="form-control shipping-required" >
						</div>
					</div>
					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">PSČ *</label>

						<div class="col-sm-3">
							<input type="text" name="shipping_zipcode" class="form-control shipping-required" id="field-2">
						</div>
						 <label class="col-sm-1 control-label">Země</label>
						  <div class="col-sm-4"> <select id="optionus" name="shipping_country" class="form-control"> <option value="czech">Česká republika</option> <option value="slovakia">Slovensko</option> <option value="austria">Rakousko</option></select>  </div>
					</div>

                                        </div>
						</div>
					</div>


<div style="clear:both;"></div>
					

<div class="form-group" style="display: none;">
						<label for="field-2" class="col-sm-3 control-label">Heslo</label>

						<div class="col-sm-5">
							<input  data-validate="required" data-message-required="Musíte vyplnit tajný kód klienta." type="text" name="secretstring" style="width: 180px; float: left;" class="form-control" >&nbsp;
							<input type="button" class="btn btn-white" value="Vygenerovat" onClick="generate();" tabindex="2">
						</div>


					</div>
					<hr>


						<div class="form-group">
						<label class="col-sm-3 control-label">Druh</label>
						<div class="col-sm-8">
							<div class="radio" style="width: 100px; float: left;">
								<label>
									<input type="radio" name="optionsRadios" value="virivka" class="virivkaradio" checked>Vířivka
								</label>
							</div>
							<div class="radio" style="width: 100px;float: left;">
								<label>
									<input type="radio" name="optionsRadios" value="sauna" class="saunaradio">Sauna
								</label>
							</div>
							<div class="radio" style="width: 140px;float: left;">
								<label>
									<input type="radio" name="optionsRadios" value="both" class="bothradio">Vířivka + Sauna
								</label>
							</div>
                            <div class="radio" style="width: 200px;float: left;">
                                <label>
                                    <input type="radio" name="optionsRadios" value="pergola" class="pergolaradio">Pergola
                                </label>
                            </div>
						</div>


						</div>

	<div class="tajtl" style="display: none;">	<hr style="margin-top: 10px; margin-bottom: 5px;"><div class="form-group" style="margin-bottom: 11px;">
				<label class="col-sm-3 control-label"><h4>Vířivka</h4></label></div>
				<hr style="margin-top: 5px;">
	</div>

	<div class="virivkens">
<?php

    specs_demand(0, '1');
    specs_demand(0, '2');

    ?>

	</div>
	<div class="tajtl" style="display: none;">	<hr style="margin-top: 20px; margin-bottom: 5px;"><div class="form-group" style="margin-bottom: 11px;">
				<label class="col-sm-3 control-label"><h4>Sauna</h4></label></div>
				<hr style="margin-top: 5px;">
	</div>

	<div class="saunkens" style="display: none;">
					<div class="form-group">
						<label class="col-sm-3 control-label">Sauny</label>

						<div class="col-sm-5">

							<select class="form-control" name="saunatype">
								<?php while ($sauna = mysqli_fetch_array($saunyquery)) { ?>
								<option value="<?= $sauna['connect_name'] ?>"><?php if ($sauna['code'] != "") {echo $sauna['code'];?> - <?php }if ($sauna['brand'] != "") {echo $sauna['brand'] . ' ' . ucfirst($sauna['fullname']);} else {echo ucfirst($sauna['fullname']);}?></option><?php } ?>
							</select>

						</div>
					</div>
	<div class="form-group">
						<label class="col-sm-3 control-label">Zboží</label>

						<div class="col-sm-5">
							<div id="virdup" class="class">


								<select style="display: none;" name="category[]">
								<option value="1">Testzmrd</option>
							</select>
					<div style="margin-bottom: 12px; width: 80%; float:left;"><input id="saundup" type="text" name="zbozicko" class="form-control typeahead" data-remote="data/autosuggest-custom.php?q=%QUERY" data-template="<div class='thumb-entry'><span class='image'><img src='{{img}}' width=40 height=40 /></span><span class='text'><strong>{{value}}</strong><em>{{desc}}</em></span></div>" placeholder="Specifikace" /></div>
					<div style="float: right; width: 19%;"><input type="text" name="nummero" class="form-control" placeholder="Počet mj."></div>
					<div style="margin-bottom: 12px; width: 80%; float:left;"><input id="saundup1" type="text" name="zbozicko1" class="form-control typeahead" data-remote="data/autosuggest-custom.php?q=%QUERY" data-template="<div class='thumb-entry'><span class='image'><img src='{{img}}' width=40 height=40 /></span><span class='text'><strong>{{value}}</strong><em>{{desc}}</em></span></div>" placeholder="Specifikace" /></div>
					<div style="float: right; width: 19%;"><input type="text" name="nummero1" class="form-control" placeholder="Počet mj."></div>
					<div style="margin-bottom: 12px; width: 80%; float:left;"><input id="saundup2" type="text" name="zbozicko2" class="form-control typeahead" data-remote="data/autosuggest-custom.php?q=%QUERY" data-template="<div class='thumb-entry'><span class='image'><img src='{{img}}' width=40 height=40 /></span><span class='text'><strong>{{value}}</strong><em>{{desc}}</em></span></div>" placeholder="Specifikace" /></div>
					<div style="float: right; width: 19%;"><input type="text" name="nummero2" class="form-control" placeholder="Počet mj."></div>
					<div style="margin-bottom: 12px; width: 80%; float:left;"><input id="saundup3" type="text" name="zbozicko3" class="form-control typeahead" data-remote="data/autosuggest-custom.php?q=%QUERY" data-template="<div class='thumb-entry'><span class='image'><img src='{{img}}' width=40 height=40 /></span><span class='text'><strong>{{value}}</strong><em>{{desc}}</em></span></div>" placeholder="Specifikace" /></div>
					<div style="float: right; width: 19%;"><input type="text" name="nummero3" class="form-control" placeholder="Počet mj."></div>
					<div style="margin-bottom: 12px; width: 80%; float:left;"><input id="saundup4" type="text" name="zbozicko4" class="form-control typeahead" data-remote="data/autosuggest-custom.php?q=%QUERY" data-template="<div class='thumb-entry'><span class='image'><img src='{{img}}' width=40 height=40 /></span><span class='text'><strong>{{value}}</strong><em>{{desc}}</em></span></div>" placeholder="Specifikace" /></div>
					<div style="float: right; width: 19%;"><input type="text" name="nummero4" class="form-control" placeholder="Počet mj."></div>
					<div style="margin-bottom: 12px; width: 80%; float:left;"><input id="saundup5" type="text" name="zbozicko5" class="form-control typeahead" data-remote="data/autosuggest-custom.php?q=%QUERY" data-template="<div class='thumb-entry'><span class='image'><img src='{{img}}' width=40 height=40 /></span><span class='text'><strong>{{value}}</strong><em>{{desc}}</em></span></div>" placeholder="Specifikace" /></div>
					<div style="float: right; width: 19%;"><input type="text" name="nummero5" class="form-control" placeholder="Počet mj."></div>
					<div style="margin-bottom: 12px; width: 80%; float:left;"><input id="saundup6" type="text" name="zbozicko6" class="form-control typeahead" data-remote="data/autosuggest-custom.php?q=%QUERY" data-template="<div class='thumb-entry'><span class='image'><img src='{{img}}' width=40 height=40 /></span><span class='text'><strong>{{value}}</strong><em>{{desc}}</em></span></div>" placeholder="Specifikace" /></div>
					<div style="float: right; width: 19%;"><input type="text" name="nummero6" class="form-control" placeholder="Počet mj."></div>
					<div style="margin-bottom: 12px; width: 80%; float:left;"><input id="saundup7" type="text" name="zbozicko7" class="form-control typeahead" data-remote="data/autosuggest-custom.php?q=%QUERY" data-template="<div class='thumb-entry'><span class='image'><img src='{{img}}' width=40 height=40 /></span><span class='text'><strong>{{value}}</strong><em>{{desc}}</em></span></div>" placeholder="Specifikace" /></div>
					<div style="float: right; width: 19%;"><input type="text" name="nummero7" class="form-control" placeholder="Počet mj."></div>
					<div style="margin-bottom: 12px; width: 80%; float:left;"><input id="saundup8" type="text" name="zbozicko8" class="form-control typeahead" data-remote="data/autosuggest-custom.php?q=%QUERY" data-template="<div class='thumb-entry'><span class='image'><img src='{{img}}' width=40 height=40 /></span><span class='text'><strong>{{value}}</strong><em>{{desc}}</em></span></div>" placeholder="Specifikace" /></div>
					<div style="float: right; width: 19%;"><input type="text" name="nummero8" class="form-control" placeholder="Počet mj."></div>






						<div class="col-sm-4">
							<input style="display: none;" type="text" class="form-control" id="field-2" name="cenicka" value="" placeholder="Počet kusů">

						</div>
						<div class="clear"></div>
					</div>

						</div>
					</div>

</div>




                            <div class="pergoly" style="display: none;">
                                <?php

                                specs_pergola(0, '1');
                                specs_pergola(0, '2');

                                ?>

                            </div>

			</div>

		</div>
	</div>



	<div class="row">

		<div class="col-md-12">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Správa poptávky
					</div>

				</div>


				<div class="panel-body">

					<div class="form-group">

						<label class="col-sm-3 control-label">Showroom</label>

						<div class="col-sm-5">

							<select class="form-control" name="showroom">
								<option value="0" selected>Neznámý showroom</option>
                                <?php
                                $showrooms_query = $mysqli->query("SELECT * FROM shops_locations WHERE type = 'branch'")or die($mysqli->error);
                                while($showroom = mysqli_fetch_assoc($showrooms_query)){
                                    ?>
                                    <option value="<?= $showroom['id'] ?>>"><?= $showroom['name'] ?></option>
                                <?php } ?>
							</select>

						</div>

					</div>
					<div class="form-group">
						<?php $admins_query = $mysqli->query("SELECT id, user_name FROM demands WHERE (role = 'salesman' OR role = 'salesman-technician') AND active = 1");?>
						<label class="col-sm-3 control-label">O poptávku se stará</label>

						<div class="col-sm-5">

							<select class="form-control" name="admin_id">
								<option value="0" selected>Nikdo nepřiřazen</option>
								<?php while ($admin = mysqli_fetch_array($admins_query)) { ?>
								<option value="<?= $admin['id'] ?>"><?= $admin['user_name'] ?></option>
								<?php } ?>
							</select>

						</div>

					</div>

				</div>

			</div>

		</div>
</div>







	<div class="row">

		<div class="col-md-12">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Kontakty k poptávce
					</div>

				</div>


				<div class="panel-body">


					<div class="col-md-3">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Kontakt #1
					</div>

				</div>


				<div class="panel-body">

					<div class="form-group">


						<div class="col-sm-12">

							<label class="col-sm-3 control-label">Jméno</label>

							<div class="col-sm-9">

							<input type="text" name="contact_name[]" class="form-control" id="field-2">

							</div>

						</div>

					</div>

					<div class="form-group">


						<div class="col-sm-12">

							<label class="col-sm-3 control-label">Role</label>

							<div class="col-sm-9">

							<select class="form-control" name="contact_role[]">

									<option value="">žádná</option>
									<option value="investor">investor</option>
									<option value="prebirajici">přebírající</option>
									<option value="architekt">architekt</option>
									<option value="stavbyvedoucí">stavbyvedoucí</option>
									<option value="designer">designer</option>
									<option value="developer">developer</option>
									<option value="elektrikář">elektrikář</option>
									<option value="manžel/manželka">manžel/manželka</option>

								</select>

							</div>

						</div>

					</div>

					<div class="form-group">


						<div class="col-sm-12">

							<label class="col-sm-3 control-label">Telefon</label>

							<div class="col-sm-9">

							<input type="text" name="contact_phone[]" class="form-control" id="field-2">

							</div>

						</div>

					</div>

					<div class="form-group">


						<div class="col-sm-12">

							<label class="col-sm-3 control-label">E-mail</label>

							<div class="col-sm-9">

							<input type="text" name="contact_email[]" class="form-control" id="field-2">

							</div>

						</div>

					</div>

				</div>

			</div>

				</div>

				<div class="col-md-3">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Kontakt #2
					</div>

				</div>


				<div class="panel-body">

					<div class="form-group">


						<div class="col-sm-12">

							<label class="col-sm-3 control-label">Název</label>

							<div class="col-sm-9">

							<input type="text" name="contact_name[]" class="form-control" id="field-2">

							</div>

						</div>

					</div>

					<div class="form-group">


						<div class="col-sm-12">

							<label class="col-sm-3 control-label">Role</label>

							<div class="col-sm-9">

							<input type="text" name="contact_role[]" class="form-control" id="field-2">

							</div>

						</div>

					</div>

					<div class="form-group">


						<div class="col-sm-12">

							<label class="col-sm-3 control-label">Telefon</label>

							<div class="col-sm-9">

							<input type="text" name="contact_phone[]" class="form-control" id="field-2">

							</div>

						</div>

					</div>

					<div class="form-group">


						<div class="col-sm-12">

							<label class="col-sm-3 control-label">E-mail</label>

							<div class="col-sm-9">

							<input type="text" name="contact_email[]" class="form-control" id="field-2">

							</div>

						</div>

					</div>

				</div>

			</div>

				</div>


				<div class="col-md-3">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Kontakt #3
					</div>

				</div>


				<div class="panel-body">

					<div class="form-group">


						<div class="col-sm-12">

							<label class="col-sm-3 control-label">Název</label>

							<div class="col-sm-9">

							<input type="text" name="contact_name[]" class="form-control" id="field-2">

							</div>

						</div>

					</div>

					<div class="form-group">


						<div class="col-sm-12">

							<label class="col-sm-3 control-label">Role</label>

							<div class="col-sm-9">

							<input type="text" name="contact_role[]" class="form-control" id="field-2">

							</div>

						</div>

					</div>

					<div class="form-group">


						<div class="col-sm-12">

							<label class="col-sm-3 control-label">Telefon</label>

							<div class="col-sm-9">

							<input type="text" name="contact_phone[]" class="form-control" id="field-2">

							</div>

						</div>

					</div>

					<div class="form-group">


						<div class="col-sm-12">

							<label class="col-sm-3 control-label">E-mail</label>

							<div class="col-sm-9">

							<input type="text" name="contact_email[]" class="form-control" id="field-2">

							</div>

						</div>

					</div>

				</div>

			</div>

				</div>


				<div class="col-md-3">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Kontakt #4
					</div>

				</div>


				<div class="panel-body">

					<div class="form-group">


						<div class="col-sm-12">

							<label class="col-sm-3 control-label">Název</label>

							<div class="col-sm-9">

							<input type="text" name="contact_name[]" class="form-control" id="field-2">

							</div>

						</div>

					</div>

					<div class="form-group">


						<div class="col-sm-12">

							<label class="col-sm-3 control-label">Role</label>

							<div class="col-sm-9">

							<input type="text" name="contact_role[]" class="form-control" id="field-2">

							</div>

						</div>

					</div>

					<div class="form-group">


						<div class="col-sm-12">

							<label class="col-sm-3 control-label">Telefon</label>

							<div class="col-sm-9">

							<input type="text" name="contact_phone[]" class="form-control" id="field-2">

							</div>

						</div>

					</div>

					<div class="form-group">


						<div class="col-sm-12">

							<label class="col-sm-3 control-label">E-mail</label>

							<div class="col-sm-9">

							<input type="text" name="contact_email[]" class="form-control" id="field-2">

							</div>

						</div>

					</div>

				</div>

			</div>

				</div>





			</div>

			</div>
		</div>
	</div>





	<center>
	<div class="form-group default-padding button-demo">
		<button type="submit" style="margin-bottom: 24px; margin-right: 26px; font-size: 20px; padding: 20px 50px 20px 140px; margin-top: 20px;" data-style="zoom-in" class="ladda-button btn btn-success btn-icon icon-left btn-lg">
			<i class="entypo-plus" style="line-height: 48px;font-size: 40px; padding: 10px 20px;"></i>
			<span class="ladda-label">Přidat poptávku</span>
		</button>
	</div>
	</center>

</form>


<footer class="main">


	&copy; <?= date("Y") ?> <span style=" float:right;"><?php
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$total_time = round(($finish - $start), 4);

echo 'PHP '.PHP_VERSION.' | Page generated in ' . $total_time . ' seconds.';?></span>

</footer>	</div>



	</div>


<?php include VIEW . '/default/footer.php'; ?>




