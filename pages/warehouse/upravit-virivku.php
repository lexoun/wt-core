<?php



include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";

include INCLUDES . "/googlelogin.php";
include INCLUDES . "/functions.php";

$id = $_REQUEST['id'];

$hottubquery = $mysqli->query('SELECT *, w.id as id FROM warehouse w, warehouse_products p WHERE w.product = p.connect_name AND w.id="' . $_REQUEST['id'] . '"') or die($mysqli->error);

if (mysqli_num_rows($hottubquery) > 0) {

    $hottub = mysqli_fetch_assoc($hottubquery);

    if ($hottub['demand_id'] != 0) {

        $demandquery = $mysqli->query("SELECT * FROM demands WHERE id = '" . $hottub['demand_id'] . "'");
        $demand = mysqli_fetch_array($demandquery);

    }

    $pagetitle = "Upravit vířivku";

    $bread1 = "Vířivky";
    $abread1 = "virivky";

    $bread2 = '#' . $hottub['id'] . ' ' . $hottub['brand'] . ' ' . ucfirst($hottub['fullname']);
    $abread2 = "virivky?id=" . $hottub['id'];

    if (isset($_REQUEST['action']) && $_REQUEST['action'] == "edit") {

        $reserved_mail = 0;

        if($_POST['reserved'] == 1){

            $reserved_date = $_POST['reserved_date'];

            if($_POST['reserved_mail'] == 1 && $hottub['reserved_mail'] == 0){

                $reserved_mail = $client['id'];

            }elseif($hottub['reserved_mail'] != 0){


                $reserved_mail = $hottub['reserved_mail'];

            }else{


                $reserved_mail = 0;

            }

        }else{

            $reserved_date = '0000-00-00';

        }

        $mysqli->query("UPDATE warehouse SET 
        reserved_showroom = '".$_POST['reserved_showroom']."',
        reserved = '".$_POST['reserved']."',
        reserved_date = '".$reserved_date."',
        reserved_mail = '".$reserved_mail."',
        change_description = '" . $_POST['change_description'] . "', change_executor = '" . $_POST['change_executor'] . "', description = '" . $_POST['description'] . "', loadingdate = '" . $_POST['loadingdate'] . "', status = '" . $_POST['status'] . "', demand_id = '" . $_POST['demand'] . "', serial_number = '" . $_POST['serialnumber'] . "', location_id = '" . $_POST['location_id'] . "' WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

        if ($_POST['location_id'] != $hottub['location_id']) {

            $mysqli->query("UPDATE warehouse_products_bridge SET location_id = '" . $_POST['location_id'] . "' WHERE warehouse_id = '" . $hottub['id'] . "'") or die($mysqli->error);

        }

        include CONTROLLERS . "/product-stock-controller.php";


        /// START SPECS START SPECS START SPECS

        $choosed_hottub = $_POST['virivkatype'];

        $choosed_type = $_POST['provedeni_' . $choosed_hottub];

        $get_ids = $mysqli->query("SELECT w.id as id, w.name as name, p.id as product_id FROM warehouse_products_types w, warehouse_products p WHERE w.warehouse_product_id = p.id AND p.connect_name = '$choosed_hottub' AND w.seo_url = '$choosed_type'") or die($mysqli->error);
        $get_id = mysqli_fetch_array($get_ids);



        ///provedení

        $find_query = $mysqli->query("SELECT id FROM warehouse_specs_bridge WHERE client_id = '" . $_REQUEST['id'] . "' AND specs_id = '5'") or die($mysqli->error);
        if (mysqli_num_rows($find_query) > 0) {

            $find = mysqli_fetch_array($find_query);
            $insert_specs = $mysqli->query("UPDATE warehouse_specs_bridge SET value = '" . $get_id['name'] . "' WHERE id = '" . $find['id'] . "'") or die($mysqli->error);

        } else {

            $insert_specs = $mysqli->query("INSERT INTO warehouse_specs_bridge (value, client_id, specs_id) VALUES ('" . $get_id['name'] . "','" . $_REQUEST['id'] . "','5')") or die($mysqli->error);

        }




        ///provedení

        $specs_query = $mysqli->query("SELECT *, s.id as id FROM specs s, warehouse_products_types_specs w WHERE w.spec_id = s.id AND w.type_id = '" . $get_id['id'] . "' GROUP BY s.id") or die($mysqli->error);

        while ($specs = mysqli_fetch_array($specs_query)) {

            $seoslug = $specs['seoslug'];

			if(isset($_POST[$choosed_hottub . '_' . $choosed_type . '_' . $seoslug])){

            $spec_value = $_POST[$choosed_hottub . '_' . $choosed_type . '_' . $seoslug];

            $find_query = $mysqli->query("SELECT * FROM warehouse_specs_bridge WHERE client_id = '" . $_REQUEST['id'] . "' AND specs_id = '" . $specs['id'] . "'") or die($mysqli->error);

            if (mysqli_num_rows($find_query) == 1) {


                $old_param_query = $mysqli->query("SELECT w.id, w.value, p.id as param_id FROM warehouse_specs_bridge w, specs_params p WHERE w.value = p.option AND w.client_id = '" . $_REQUEST['id'] . "' AND w.specs_id = '" . $specs['id'] . "'") or die($mysqli->error);

                $old_param = mysqli_fetch_array($old_param_query);

                $mysqli->query("UPDATE warehouse_specs_bridge SET value = '$spec_value' WHERE client_id = '" . $_REQUEST['id'] . "' AND specs_id = '" . $specs['id'] . "'") or die($mysqli->error);

                if ($old_param['value'] != $spec_value) {

                            $mysqli->query("DELETE FROM warehouse_products_bridge WHERE spec_id = '" . $specs['id'] . "' AND warehouse_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);


                }



                // getting param id

                if (isset($specs['type']) && $specs['type'] == 1) {

                    $paramsquery = $mysqli->query("SELECT *, p.id as id FROM specs_params p, warehouse_products_types_specs w  WHERE p.spec_id = '" . $specs['id'] . "' AND w.spec_param_id = p.id AND w.type_id = '" . $get_id['id'] . "' AND p.option = '".$spec_value."' GROUP by p.id") or die($mysqli->error);

                    $param = mysqli_fetch_array($paramsquery);

                    $param_id = $param['id'];

                } else {

                    if ($spec_value == 'Ano') { $param_id = 1; } else { $param_id = 0;}

                }


                // product

                $products_check = $mysqli->query("SELECT * FROM demands_products WHERE spec_id = '" . $specs['id'] . "' AND param_id = '" . $param_id . "' AND type = '" . $choosed_hottub . "'") or die($mysqli->error);

                if (mysqli_num_rows($products_check) > 0) {

                    $product = mysqli_fetch_array($products_check);


                        $find_query = $mysqli->query("SELECT * FROM warehouse_products_bridge WHERE spec_id = '" . $specs['id'] . "' AND warehouse_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);
                        if (mysqli_num_rows($find_query) == 0) {

                            $mysqli->query("INSERT INTO warehouse_products_bridge (warehouse_id, spec_id, product_id, variation_id, quantity, reserved, location_id) VALUES ('" . $_REQUEST['id'] . "', '" . $specs['id'] . "', '" . $product['product_id'] . "', '" . $product['variation_id'] . "', '1', '1', '" . $_POST['location_id'] . "')") or die($mysqli->error);

                        }else{
                            $mysqli->query("UPDATE warehouse_products_bridge SET product_id = '" . $product['product_id'] . "', variation_id = '" . $product['variation_id'] . "', quantity = '1', reserved = '1', location_id = '" . $_POST['location_id'] . "' WHERE spec_id = '" . $specs['id'] . "' AND warehouse_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

                        }


                }
//
                // product

            } else {

                if (mysqli_num_rows($find_query) > 1) {

                    $mysqli->query("DELETE FROM warehouse_specs_bridge WHERE client_id = '" . $_REQUEST['id'] . "' AND specs_id = '" . $specs['id'] . "'") or die($mysqli->error);

                }

                $insert_specs = $mysqli->query("INSERT INTO warehouse_specs_bridge (value, client_id, specs_id) VALUES ('$spec_value','" . $_REQUEST['id'] . "','" . $specs['id'] . "')") or die($mysqli->error);

                $products_check = $mysqli->query("SELECT * FROM demands_products WHERE spec_id = '" . $specs['id'] . "' AND param_id = '" . $find['param_id'] . "' AND type = '" . $choosed_hottub . "'") or die($mysqli->error);

                if (mysqli_num_rows($products_check) > 0) {

                    while ($product = mysqli_fetch_array($products_check)) {

                        $mysqli->query("INSERT INTO warehouse_products_bridge (warehouse_id, spec_id, product_id, variation_id, quantity, reserved, location_id) VALUES ('" . $hottub['id'] . "', '" . $specs['id'] . "', '" . $product['product_id'] . "', '" . $product['variation_id'] . "', '1', '1', '" . $_POST['location_id'] . "')") or die($mysqli->error);

                    }
                }

			}
		}

        }

        /// END SPECS END SPECS END SPECS END SPECS

        if ($hottub['demand_id'] != $_POST['demand'] && $hottub['demand_id'] != 0) {

            $_REQUEST['id'] = $hottub['demand_id'];

            saveCalendarEvent($hottub['demand_id'], 'realization');

        }

        if ($_POST['demand'] != '' && $_POST['demand'] != '0') {

            $_REQUEST['id'] = $_POST['demand'];

            saveCalendarEvent($_POST['demand'], 'realization');

        }

        $_REQUEST['id'] = $hottub['id'];
        $id = $hottub['id'];

        $find_container = $mysqli->query("SELECT id FROM containers_products WHERE warehouse_id = '$id'") or die($mysqli->error);

        if (mysqli_num_rows($find_container) > 0) {
            $hottub = mysqli_fetch_array($find_container);

            $update_demand = $mysqli->query("UPDATE containers_products SET demand_id = '" . $_POST['demand'] . "' WHERE id = '" . $hottub['id'] . "'") or die($mysqli->error);

        }

        if (isset($_REQUEST['redirect']) && $_REQUEST['redirect'] != "") {

            Header("Location:https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=" . $_REQUEST['redirect'] . "&success=edit");

        } else {

            Header("Location:https://www.wellnesstrade.cz/admin/pages/warehouse/zobrazit-virivku?id=" . $_REQUEST['id'] . "&success=edit");

        }
        exit;
    }

    $cpars_query = $mysqli->prepare('SELECT * FROM warehouse_specs_bridge WHERE specs_id = ? and client_id = ?');
    $spec_id = null;
    $product_id = null;
    $cpars_query->bind_param('ii', $spec_id, $product_id);

    $params_query = $mysqli->prepare('SELECT *, p.id as id FROM specs_params p, warehouse_products_types_specs w WHERE p.spec_id = ? AND w.spec_param_id = p.id AND w.type_id = ? GROUP by p.id');
    $spec_id = null;
    $type_id = null;
    $params_query->bind_param('ii', $spec_id, $type_id);

    $virivkyquery = $mysqli->query("SELECT * FROM warehouse_products WHERE active = 'yes' AND customer = 1 ORDER BY brand");

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
    myform.password.value = randomPassword(myform.length.value);
}

jQuery(document).ready(function($)
{

$('.radio').click(function() {
   if($("input:radio[class='saunaradio']").is(":checked")) {


	$('.virivkens').hide( "slow");
	$('.saunkens').show( "slow");
   }
     if($("input:radio[class='virivkaradio']").is(":checked")) {


   	$('.saunkens').hide( "slow");
$('.virivkens').show( "slow");
   }
});
});


</script>


<script type="text/javascript">
	jQuery(document).ready(function($)
	{

	$('#virivkatype').on('change', function() {
		var id = '<?= $id ?>'
		var selected = this.value;



		$('.virivky_typy').hide( "slow");
		   	$('.params_virivky').hide( "slow");
			$('.virivka_'+selected).show( "slow");
			var selected_type = $('.virivka_'+selected+' .provedeni_'+selected).val();
			if(selected_type != ""){
			$('.params_'+selected_type+'_'+selected).show( "slow");
			}

			$("#current_provedeni").load("load-new.php?id="+id+"&connect_name="+selected);

		});



		$('.provedeni').on('change', function() {


			var id = this.id;
			var selected = this.value;

		   	$('.params_virivky_'+id).hide( "slow");
			$('.params_'+selected+'_'+id).show( "slow");


		});






	});
</script>

<form role="form" method="post" name="myform" class="form-horizontal form-groups-bordered validate" action="upravit-virivku?id=<?= $_REQUEST['id'] ?>&action=edit<?php if (isset($_REQUEST['redirect']) && $_REQUEST['redirect'] != "") {echo '&redirect=' . $_REQUEST['redirect'];}?>">
<input type="hidden" name="length" value="14">
	<div class="row">

		<div class="col-md-6">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						<?= $pagetitle ?>
					</div>

				</div>

						<div class="panel-body">

	<div class="form-group">
						<label class="col-sm-3 control-label">K poptávce</label>

						<div class="col-sm-8">
						<?php

	$demandsq = $mysqli->query("SELECT user_name, id FROM demands WHERE (customer = 1 OR customer = 3) 
	AND status <> 5 and status <> 6") or die($mysqli->error);
	

    ?>
						<select name="demand" class="select2" data-allow-clear="true" data-placeholder="Vyberte poptávku...">
								<option value="0">žádná poptávka</option>
<!--								<optgroup label="--><?//echo strtoupper($hottub['product']); ?><!-- poptávky">-->
								<?php     if ($hottub['demand_id'] != 0) {

$selectrer = $mysqli->query("SELECT user_name, id FROM demands WHERE id = '" . $hottub['demand_id'] . "'") or die($mysqli->error);
$sel = mysqli_fetch_array($selectrer);
?>
								<option value="<?= $sel['id'] ?>" selected><?= $sel['user_name'] ?></option>
								<?php
}else{
?><option value="" selected></option>
									<?php } ?>
									<?php
                                    while ($dem = mysqli_fetch_array($demandsq)) {

        $find = $mysqli->query("SELECT id FROM warehouse WHERE demand_id = '" . $dem['id'] . "' AND product = '" . $hottub['product'] . "'");

        if (mysqli_num_rows($find) != 1) { ?><option value="<?= $dem['id'] ?>"><?= $dem['user_name'] ?></option><?php }}?>
								</optgroup>
						</select>

						</div>
					</div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">Rezervace pro poptávku</label>

                                <div class="col-sm-8">

                                    <div class="radio" style="margin-left: 10px; float: left;">
                                        <label>
                                            <input type="radio" name="reserved" value="1" <?php if (isset($hottub['reserved']) && $hottub['reserved'] == 1) {echo 'checked';}?>>Ano
                                        </label>
                                    </div>
                                        <div class="radio" style="margin-left: 31px;float: left;">
                                            <label>
                                                <input type="radio" name="reserved" value="0" <?php if (isset($hottub['reserved']) && $hottub['reserved'] == 0) {echo 'checked';}?>>Ne
                                            </label>
                                        </div>

                                </div>
                            </div>


                            <div class="form-group">
                                <label for="field-2" class="col-sm-3 control-label">Konec rezervace</label>

                                <div class="col-sm-8">
                                    <input type="date" class="form-control" id="field-2" name="reserved_date" value="<?php if($hottub['reserved_date'] != '0000-00-00'){ echo $hottub['reserved_date']; }else{ echo date('Y-m-d', strtotime('+7 days')); } ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">Informovat o konci<br></label>

                                <div class="col-sm-3">

                                    <div class="radio" style="margin-left: 10px;float: left;">
                                        <label>
                                            <input type="radio" name="reserved_mail" value="1" <?php if (!isset($hottub['reserved_mail']) || $hottub['reserved_mail'] != 0) {echo 'checked';}?>>Ano
                                        </label>
                                    </div>
                                    <div class="radio" style="margin-left: 31px; float: left;">
                                        <label>
                                            <input type="radio" name="reserved_mail" value="0" <?php if (isset($hottub['reserved_mail']) && $hottub['reserved_mail'] == 0) {echo 'checked';}?>>Ne
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <small>(mailové upozornění v den konce rezervace)</small>
                                </div>
                            </div>


                            <hr/>


                            <div class="form-group" style="margin-top: 18px; margin-bottom: 21px;">
                                <label class="col-sm-3 control-label">Rezervace na showroom</label>
                                <div class="col-sm-9">
                                    <div class="radio col-sm-3" style="float: left;">
                                        <label>
                                            <input type="radio" name="reserved_showroom" value="0" <?php if (empty($hottub['reserved_showroom'])) {echo 'checked';}?>>Ne
                                        </label>
                                    </div>
                                    <?php
                                    $location_query = $mysqli->query("SELECT * FROM shops_locations WHERE type = 'branch'") or die($mysqli->error);
                                    while ($location = mysqli_fetch_array($location_query)) {
                                   ?>
                                    <div class="radio col-sm-3" style="margin-left: 0px; float: left;">
                                        <label>
                                            <input type="radio" name="reserved_showroom" value="<?= $location['id'] ?>>" <?php if (!empty($hottub['reserved_showroom']) && $hottub['reserved_showroom'] == $location['id']) {echo 'checked';}?>><?= $location['name'] ?>
                                        </label>
                                    </div>
                                    <?php } ?>

                                </div>
                            </div>

                            <hr/>

					<div class="form-group" style="margin-top: 18px; margin-bottom: 21px;">
						<label class="col-sm-2 control-label">Stav</label>
						<div class="col-sm-10">
							<div class="radio col-sm-3" style="float: left;">
								<label>
									<input type="radio" name="status" value="0" <?php if (isset($hottub['status']) && $hottub['status'] == 0) {echo 'checked';}?>>Ve výrobě
								</label>
							</div>
							<div class="radio col-sm-3" style="float: left;">
								<label>
									<input type="radio" name="status" value="1" <?php if (isset($hottub['status']) && $hottub['status'] == 1) {echo 'checked';}?>>Na cestě
								</label>
							</div>
							<div class="radio col-sm-3" style="float: left;">
								<label>
									<input type="radio" name="status" value="2" <?php if (isset($hottub['status']) && $hottub['status'] == 2) {echo 'checked';}?>>Na skladě
								</label>
							</div>
							<div class="radio col-sm-3" style="float: left;">
								<label>
									<input type="radio" name="status" value="3" <?php if (isset($hottub['status']) && $hottub['status'] == 3) {echo 'checked';}?>>Na showroomu
								</label>
							</div>
							<div class="radio col-sm-3" style="float: left;">
								<label>
									<input type="radio" name="status" value="6" <?php if (isset($hottub['status']) && $hottub['status'] == 6) {echo 'checked';}?>>Uskladněno
								</label>
							</div>
							<div class="radio col-sm-3" style="float: left;">
								<label>
									<input type="radio" name="status" value="7" <?php if (isset($hottub['status']) && $hottub['status'] == 7) {echo 'checked';}?>>Reklamace
								</label>
							</div>

							<div class="radio col-sm-3" style="float: left;">
								<label>
									<input type="radio" name="status" value="4" <?php if (isset($hottub['status']) && $hottub['status'] == 4) {echo 'checked';}?>>Expedovaná
								</label>
							</div>
						</div>
					</div>
                    <hr/>

                    <div class="form-group">
						<label for="field-2" class="col-sm-2 control-label">Fyzické umístění</label>
						<div class="col-sm-10">
							<?php
    $warehouse_query = $mysqli->query("SELECT * FROM shops_locations") or die($mysqli->error);
    while ($warehouse = mysqli_fetch_array($warehouse_query)) {

        ?>
							<div class="radio col-sm-3" style="margin-left: 0px; float: left;">
								<label>
									<input type="radio" name="location_id" value="<?= $warehouse['id'] ?>" <?php if (isset($hottub['location_id']) && $hottub['location_id'] == $warehouse['id']) {echo 'checked';}?>><?= $warehouse['name'] ?>
								</label>
							</div>
							<?php } ?>
						</div>
					</div>

                            <hr/>

					<div class="form-group">
                        <label for="field-2" class="col-sm-3 control-label">Očekávané naskladnění<br><small>(pouze pro stav na cestě)</small></label>

						<div class="col-sm-8">
							<input type="date" class="form-control" id="field-2" name="loadingdate" value="<?= $hottub['loadingdate'] ?>">
						</div>
					</div>
					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Výrobní číslo</label>

						<div class="col-sm-8">
							<input type="text" class="form-control" id="field-2" name="serialnumber" value="<?= $hottub['serial_number'] ?>">
						</div>
					</div>
					<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Dodatečný popisek</label>

						<div class="col-sm-8">
							<textarea class="form-control autogrow" id="field-ta" name="description" style="overflow: hidden; word-wrap: break-word; resize: horizontal; height: 48px;"><?= $hottub['description'] ?></textarea>
						</div>
					</div>

                    <hr/>

                    <div class="form-group">
                        <label for="field-1" class="col-sm-3 control-label">Popis poslední provedené změny</label>

                        <div class="col-sm-8">
                            <textarea class="form-control autogrow" id="field-ta" name="change_description" style="overflow: hidden; word-wrap: break-word; resize: horizontal; height: 48px;"><?= $hottub['change_description'] ?></textarea>
                        </div>
                    </div>


                    <div class="form-group">
                        <?php $admins_query = $mysqli->query("SELECT id, user_name FROM demands WHERE (role = 'technician' OR role = 'salesman-technician') AND active = 1");?>
                        <label class="col-sm-3 control-label">Změnu provedl</label>

                        <div class="col-sm-5">

                            <select class="form-control" name="change_executor">
                                <option value="0" <?php if (isset($hottub['change_executor']) && $hottub['change_executor'] == 0) {echo 'selected';}?>>Nikdo</option>
                                <?php while ($admin = mysqli_fetch_array($admins_query)) { ?>
                                    <option value="<?= $admin['id'] ?>" <?php if (isset($hottub['change_executor']) && $hottub['change_executor'] == $admin['id']) {echo 'selected';}?>><?= $admin['user_name'] ?></option>
                                <?php } ?>
                            </select>

                        </div>

                    </div>






				</div>

			</div>

		</div>







		<div class="col-md-6">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						<strong style="font-weight: 600;">Specifikace</strong>
					</div>

				</div>

						<div class="panel-body">




					<label class="col-sm-3 control-label">Vířivky</label>

						<div class="col-sm-9">

							<select class="form-control" id="virivkatype" disabled>
								<?php

    $get_provedeni = $mysqli->query("SELECT t.id FROM warehouse_specs_bridge c, warehouse_products_types t, warehouse_products p WHERE c.client_id = '" . $id . "' AND c.specs_id = 5 AND t.warehouse_product_id = p.id AND p.connect_name = '" . $hottub['product'] . "' AND t.name = c.value") or die($mysqli->error);
    $provedeni = mysqli_fetch_array($get_provedeni);

    $virivky_query_new = $mysqli->query("SELECT id, connect_name, fullname, brand FROM warehouse_products WHERE customer = 1 ORDER BY brand");
    while ($virivka = mysqli_fetch_array($virivky_query_new)) { ?>
								<option value="<?= $virivka['connect_name'] ?>" <?php if (isset($hottub['product']) && $hottub['product'] == $virivka['connect_name']) {echo 'selected';}?>><?php if ($virivka['brand'] != "") {echo $virivka['brand'] . ' ' . ucfirst($virivka['fullname']);} else {echo ucfirst($virivka['fullname']);}?></option><?php } ?>
							</select>

                            <input name="virivkatype" value="<?= $hottub['product'] ?>" style="display: none;">

						</div>
					</div>

					<div class="form-group">
					<script type="text/javascript">
						jQuery(document).ready(function($)
						{


							/*
							$('#virivkatype').on('change', function() {
								var selected = this.value;
							   	$('.virivky_typy').hide( "slow");
							   	$('.params_virivky').hide( "slow");
								$('.virivka_'+selected).show( "slow");
								var selected_type = $('.virivka_'+selected+' .provedeni_'+selected).val();
								if(selected_type != ""){
								$('.params_'+selected_type+'_'+selected).show( "slow");
								}
								});

							$('.provedeni').on('change', function() {


							var id = this.id;
							var selected = this.value;

						   	$('.params_virivky_'+id).hide( "slow");
							$('.params_'+selected+'_'+id).show( "slow");


						});*/






						});
					</script>

	<?php mysqli_data_seek($virivky_query_new, 0);
    while ($virivka = mysqli_fetch_array($virivky_query_new)) {

        ?>
		<div class="virivky_typy virivka_<?= $virivka['connect_name'] ?>" <?php if ($hottub['product'] != $virivka['connect_name']) { ?>style="display: none;"<?php } ?>>
			<div class="form-group">
						<label class="col-sm-4 control-label">Provedení</label>

						<div class="col-sm-7">

							<select class="form-control provedeni_<?= $virivka['connect_name'] ?> provedeni" id="<?= $virivka['connect_name'] ?>" name="provedeni_<?= $virivka['connect_name'] ?>">
		<?php

        $param_type_query = $mysqli->query("SELECT * FROM warehouse_specs_bridge WHERE specs_id = '5' and client_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);
        $param_type = mysqli_fetch_array($param_type_query);

        $selected = false;
        $options = '';

        $virivky_typy = $mysqli->query("SELECT * FROM warehouse_products_types WHERE warehouse_product_id = '" . $virivka['id'] . "'") or die($mysqli->error);
        while ($typ = mysqli_fetch_array($virivky_typy)) {

            $selected_echo = "";

            if (isset($param_type['value']) && $param_type['value'] == $typ['name'] && $hottub['product'] == $virivka['connect_name']) {$selected = true;
                $selected_echo = 'selected';
                $selected_id = $virivka['id'];}

            $options = $options . '<option value="' . $typ['seo_url'] . '" ' . $selected_echo . '>' . $typ['name'] . '</option>';

        }

        ?>
			<option value="" <?php if ($selected != true) {echo 'selected';}?>>Žádná vybraná možnost</option>
		<?php

        echo $options;

        ?>
			</select>
						</div>
			</div>
		</div>
<?php }
    $virivky_typy = $mysqli->query("SELECT * FROM warehouse_products_types WHERE warehouse_product_id = '" . $selected_id . "'") or die($mysqli->error);
    ?>
<div id="current_provedeni">
<?php while ($typ = mysqli_fetch_array($virivky_typy)) { ?>

			<div class="params_virivky params_virivky_<?= $hottub['product'] ?> params_<?= $typ['seo_url'] ?>_<?= $hottub['product'] ?>" <?php if ($param_type['value'] != $typ['name'] || $hottub['product'] != $hottub['product']) { ?>style="display: none;"<?php } ?>>
				<?php

        $specs_query = $mysqli->query("SELECT *, s.id as id, c.value FROM (specs s, warehouse_products_types_specs w) LEFT JOIN warehouse_specs_bridge c ON  c.specs_id = s.id AND c.client_id = '" . $_REQUEST['id'] . "' WHERE w.spec_id = s.id AND w.type_id = '" . $typ['id'] . "' AND s.warehouse_spec = 1 GROUP BY s.id ORDER BY s.rank asc") or die($mysqli->error);

        while ($specs = mysqli_fetch_array($specs_query)) {

            $spec_id = $specs['id'];
            $product_id = $id;

            $cpars_query->execute() or die($mysqli->error);
            $result = $cpars_query->get_result();
            $cpars = $result->fetch_array();

            // VALUE U POPTÁVKY K DANÉ SPECIFIKACI

            if (isset($specs['type']) && $specs['type'] == 1) {

                $spec_id = $specs['id'];
                $type_id = $typ['id'];

                $params_query->execute() or die($mysqli->error);
                $result = $params_query->get_result();

                ?><div class="form-group">
							<label class="col-sm-4 control-label"><?= $specs['name'] ?></label>
							<div class="col-sm-7">
								<select name="<?= $hottub['product'] ?>_<?= $typ['seo_url'] ?>_<?= $specs['seoslug'] ?>" class="form-control">
									<?php
                $selected = false;
                $options = '';

                while ($params = $result->fetch_array()) {

                    $selected_echo = "";

                    // když uložené provedení u poptávky == právě řešené provedení
                    if (isset($provedeni['id']) && $provedeni['id'] == $params['type_id']) {

                        if (isset($cpars['value']) && $cpars['value'] == $params['option'] && $param_type['value'] == $typ['name'] && $hottub['product'] == $hottub['product']) {

                            $selected_echo = 'selected';
                            $selected = true;

                        }

                    } elseif ($provedeni['id'] != $params['type_id']) {

                        if (isset($params['choosed']) && $params['choosed'] == 1 && $cpars['value'] != "unknown") {

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
						</div><?php

            } else {

                $paramsquery = $mysqli->query("SELECT * FROM warehouse_products_types_specs WHERE spec_id = '" . $specs['id'] . "' AND type_id = '" . $typ['id'] . "' order by spec_param_id desc") or die($mysqli->error);

                ?><div class="form-group">
						<label class="col-sm-4 control-label"><?= $specs['name'] ?></label>
						<div class="col-sm-7"><?php

                $selected = false;
                while ($params = mysqli_fetch_array($paramsquery)) {

                    if (isset($params['spec_param_id']) && $params['spec_param_id'] == 1) {$value = 'Ano';} else { $value = 'Ne';}

                    ?><div class="radio" style="width: 100px; float: left;text-align: left;">
								<label>
									<input class="generate_radio" id="price_<?= $specs['seoslug'] ?>" type="radio" name="<?= $hottub['product'] ?>_<?= $typ['seo_url'] ?>_<?= $specs['seoslug'] ?>" value="<?= $value ?>" <?php if (($cpars['value'] == $value && $param_type['value'] == $typ['name']) || ($params['choosed'] == 1 && $cpars['value'] != "unknown" && !$selected)) {$selected = true;
                        echo 'checked';}?> style=" height: 20px;"><?= $value ?>
								</label>
							</div><?php

                }?>
						</div>
					</div>
					<?php }
        }?>
			</div>

		<?php } ?>

</div>



						</div>
					</div>

				</div>



	</div>



			<center>
	<div class="form-group default-padding" style="margin-left: -100px;">

  <a href="./virivky?id=<?= $_REQUEST['id'] ?>"><button type="button" class="btn btn-primary">Zpět</button></a>
		<button type="submit" class="btn btn-success"><?= $pagetitle ?></button>
	</div></center>

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

	<script>

        $(document).ready(function(){

            $("#order_form").on("submit", function(){
              var form = $( "#order_form" );
                         var l = Ladda.create( document.querySelector( '#order_form .button-demo button' ) );
                if(form.valid()){

                  l.start();
                }
               });


         });


    </script>

<?php include VIEW . '/default/footer.php'; ?>



<?php

} else {

    include INCLUDES . "/404.php";

}?>
