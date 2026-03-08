<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";

include INCLUDES . "/googlelogin.php";
include INCLUDES . "/functions.php";

$hottubquery = $mysqli->query('SELECT *, w.id as id FROM warehouse w, warehouse_products p WHERE w.product = p.connect_name AND w.id="' . $_REQUEST['id'] . '"') or die($mysqli->error);

if (mysqli_num_rows($hottubquery) > 0) {

    $hottub = mysqli_fetch_assoc($hottubquery);

    if ($hottub['demand_id'] != 0) {

        $demandquery = $mysqli->query("SELECT * FROM demands WHERE id = '" . $hottub['demand_id'] . "'");
        $demand = mysqli_fetch_array($demandquery);

    }

    $pagetitle = "Upravit saunu";

    $bread1 = "Sauny";
    $abread1 = "sauny";

    $bread2 = '#' . $hottub['id'] . ' ' . $hottub['brand'] . ' ' . ucfirst($hottub['fullname']);
    $abread2 = "sauny?id=" . $hottub['id'];

    if (isset($_REQUEST['action']) && $_REQUEST['action'] == "edit") {

        $insert = $mysqli->query("UPDATE warehouse SET ram = '" . $_POST['ram'] . "', location_id = '" . $_POST['location_id'] . "', description = '" . $_POST['description'] . "', serial_number = '" . $_POST['serialnumber'] . "', purchase_price = '" . $_POST['purchase_price'] . "', sale_price = '" . $_POST['sale_price'] . "', loadingdate = '" . $_POST['loadingdate'] . "', product = '" . $_POST['virivkatype'] . "', status = '" . $_POST['status'] . "', demand_id = '" . $_POST['demand'] . "' WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);
        $id = $mysqli->insert_id;

        $specsquery = $mysqli->query('SELECT * FROM specs') or die($mysqli->error);

        while ($specs = mysqli_fetch_array($specsquery)) {
            $specsslug = $specs['seoslug'];
            $insrt = $_POST[$specsslug];
            $spcid = $specs['id'];
            $insert_specs = "UPDATE warehouse_specs_bridge SET value = '$insrt' WHERE client_id = '" . $_REQUEST['id'] . "' AND specs_id = '$spcid'";
            $insert_specs_result = $mysqli->query($insert_specs) or die($mysqli->error);

            $parametter = $specs['name'] . ': ' . $insrt;

            if ($specnaz != "") {

                $specnaz = $specnaz . ', ' . $parametter;

            } else {

                $specnaz = $parametter;

            }
        }

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

        if ($_REQUEST['redirect'] != "") {

            Header("Location:https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=" . $_REQUEST['redirect'] . "&success=edit");

        } else {

            Header("Location:https://www.wellnesstrade.cz/admin/pages/warehouse/sauny?id=" . $_REQUEST['id'] . "&success=edit");

        }
        exit;
    }

    $saunyquery = $mysqli->query("SELECT * FROM warehouse_products WHERE active = 'yes' AND customer = 0 ORDER BY code");

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

<form role="form" method="post" name="myform" class="form-horizontal form-groups-bordered validate" action="upravit-saunu?id=<?= $_REQUEST['id'] ?>&action=edit<?php if ($_REQUEST['redirect'] != "") {echo '&redirect=' . $_REQUEST['redirect'];}?>">
<input type="hidden" name="length" value="14">
	<div class="row">

		<div class="col-md-12">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						<?= $pagetitle ?>
					</div>

				</div>

						<div class="panel-body">

	<div class="form-group">
						<label class="col-sm-3 control-label">Typ sauny</label>

						<div class="col-sm-5">

							<select class="form-control" name="virivkatype">
								<option value="">Vyberte saunu</option>
								<?php while ($sauna = mysqli_fetch_array($saunyquery)) { ?>
								<option value="<?= $sauna['connect_name'] ?>" <?php if (isset($hottub['product']) && $hottub['product'] == $sauna['connect_name']) {echo 'selected';}?>><?= $sauna['code'] ?> - <?= $sauna['brand'] . ' ' . ucfirst($sauna['fullname']) ?></option><?php } ?>
							</select>

						</div>
					</div>

	<div class="form-group">
						<label class="col-sm-3 control-label">K poptávce</label>

						<div class="col-sm-5">
						<?php

    $demandsq = $mysqli->query("SELECT user_name, id, product, secondproduct FROM demands WHERE (customer = 0 OR customer = 3) AND (product = '" . $hottub['product'] . "' OR secondproduct = '" . $hottub['product'] . "') AND status <> 5 AND status <> 6") or die($mysqli->error);
    if ($hottub['demand_id'] != 0) {
        $selectrer = $mysqli->query("SELECT user_name, id FROM demands WHERE id = '" . $hottub['demand_id'] . "'") or die($mysqli->error);
        $sel = mysqli_fetch_array($selectrer);
    }
    ?>
						<select name="demand" class="select2" data-allow-clear="true" data-placeholder="Vyberte poptávku...">
								<option></option>
								<optgroup label="<?= strtoupper($hottub['product']) ?> poptávky">
									<option value="<?= $sel['id'] ?>" selected><?= $sel['user_name'] ?></option>
									<?php while ($dem = mysqli_fetch_array($demandsq)) {
        $find = $mysqli->query("SELECT id FROM warehouse WHERE demand_id = '" . $dem['id'] . "' AND product = '" . $hottub['product'] . "'");
        if (mysqli_num_rows($find) != 1) { ?><option value="<?= $dem['id'] ?>"><?= $dem['user_name'] ?></option><?php }}?>
								</optgroup>
						</select>

						</div>
					</div>

					<div class="form-group" style="margin-top: 18px; margin-bottom: 21px;">
						<label class="col-sm-3 control-label">Stav</label>
						<div class="col-sm-5">
							<div class="radio" style="margin-left: 10px;float: left;">
								<label>
									<input type="radio" name="status" value="0" <?php if (isset($hottub['status']) && $hottub['status'] == 0) {echo 'checked';}?>>Ve výrobě
								</label>
							</div>
							<div class="radio" style="margin-left: 31px; float: left;">
								<label>
									<input type="radio" name="status" value="1" <?php if (isset($hottub['status']) && $hottub['status'] == 1) {echo 'checked';}?>>Na cestě
								</label>
							</div>
							<div class="radio" style="margin-left: 31px; float: left;">
								<label>
									<input type="radio" name="status" value="2" <?php if (isset($hottub['status']) && $hottub['status'] == 2) {echo 'checked';}?>>Na skladě
								</label>
							</div>
							<div class="radio" style="margin-left: 30px; float: left;">
								<label>
									<input type="radio" name="status" value="3" <?php if (isset($hottub['status']) && $hottub['status'] == 3) {echo 'checked';}?>>Na showroomu - Praha
								</label>
							</div>
							<div class="radio" style="margin-left: 30px; float: left;">
								<label>
									<input type="radio" name="status" value="6" <?php if (isset($hottub['status']) && $hottub['status'] == 6) {echo 'checked';}?>>Uskladněno
								</label>
							</div>
							<div class="radio" style="margin-left: 30px; float: left;">
								<label>
									<input type="radio" name="status" value="7" <?php if (isset($hottub['status']) && $hottub['status'] == 7) {echo 'checked';}?>>Reklamace
								</label>
							</div>

							<div class="radio" style="margin-left: 30px; float: left;">
								<label>
									<input type="radio" name="status" value="4" <?php if (isset($hottub['status']) && $hottub['status'] == 4) {echo 'checked';}?>>Expedována
								</label>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Fyzické umístění</label>
						<div class="col-sm-8">
							<?php
    $warehouse_query = $mysqli->query("SELECT * FROM shops_locations") or die($mysqli->error);
    while ($warehouse = mysqli_fetch_array($warehouse_query)) {

        ?>
							<div class="radio" style="margin-left: 30px; float: left;">
								<label>
									<input type="radio" name="location_id" value="<?= $warehouse['id'] ?>" <?php if (isset($hottub['location_id']) && $hottub['location_id'] == $warehouse['id']) {echo 'checked';}?>><?= $warehouse['name'] ?>
								</label>
							</div>
							<?php } ?>
						</div>
					</div>


						<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Očekávané naskladnění<br>(pouze pro stav na cestě)</label>

						<div class="col-sm-5">
							<input type="date" class="form-control" id="field-2" name="loadingdate" value="<?= $hottub['loadingdate'] ?>">
						</div>
					</div>
					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Unikátní číslo produktu</label>

						<div class="col-sm-5">
							<input type="text" class="form-control" id="field-2" name="serialnumber" value="<?= $hottub['serial_number'] ?>">
						</div>
					</div>
						<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Prodejní cena</label>

						<div class="col-sm-5">
							<input type="text" class="form-control" id="field-2" style="float:left; width: 91.9%;" name="sale_price" value="<?= $hottub['sale_price'] ?>">
							<span class="input-group-addon" style="float:left; padding: 9px 25px 8px 9px;">Kč</span>
						</div>
					</div>
					<div class="form-group" >
						<label for="field-2" class="col-sm-3 control-label">Nákupní cena</label>

						<div class="col-sm-2">
							<input type="text" class="form-control" style="float:left; width: 77.6%;" id="field-2" name="purchase_price" value="<?= $hottub['purchase_price'] ?>">
							<span class="input-group-addon" style="float:left; padding: 9px 25px 8px 9px;">Kč</span>
						</div>
						<label for="field-2" class="col-sm-1 control-label">Reálně inkasováno</label>

						<div class="col-sm-2">
							<input type="text" class="form-control" style="float:left; width: 77.6%;" id="field-2" name="real_price" value="<?= $hottub['real_price'] ?>">
							<span class="input-group-addon" style="float:left; padding: 9px 25px 8px 9px;">Kč</span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Rám</label>
						<div class="col-sm-5">
							<div class="radio" style="width: 100px; float: left;">
								<label>
									<input type="radio" name="ram" value="1" <?php if (isset($hottub['ram']) && $hottub['ram'] == 1) {echo 'checked';}?>>S rámem
								</label>
							</div>
							<div class="radio" style="width: 100px;float: left;">
								<label>
									<input type="radio" name="ram" value="0" <?php if (isset($hottub['ram']) && $hottub['ram'] == 0) {echo 'checked';}?>>Bez rámu
								</label>
							</div>

						</div>
					</div>
					<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Dodatečný popisek</label>

						<div class="col-sm-5">
							<textarea class="form-control autogrow" id="field-ta" name="description" style="overflow: hidden; word-wrap: break-word; resize: horizontal; height: 48px;"><?= $hottub['description'] ?></textarea>
						</div>
					</div>
					<hr>

<?php
    $specsquery = $mysqli->query('SELECT * FROM specs WHERE product = 0 order by type desc, id asc') or die($mysqli->error);
    while ($specs = mysqli_fetch_assoc($specsquery)) {
        $cparsquery = $mysqli->query('SELECT * FROM warehouse_specs_bridge WHERE specs_id = "' . $specs['id'] . '" and client_id = "' . $_REQUEST['id'] . '"') or die($mysqli->error);
        $cpars = mysqli_fetch_array($cparsquery);
        if (isset($specs['type']) && $specs['type'] == 1) {

            $paramsquery = $mysqli->query('SELECT * FROM specs_params WHERE spec_id = "' . $specs['id'] . '" order by id asc') or die($mysqli->error);

            ?>
<div class="form-group">
						<label class="col-sm-3 control-label"><?= $specs['name'] ?></label>

						<div class="col-sm-5">

							<select name="<?= $specs['seoslug'] ?>" class="form-control">
								<?php
            $selected = false;
            while ($params = mysqli_fetch_assoc($paramsquery)) {

                ?>

								<option value="<?= $params['option'] ?>" <?php if (isset($cpars['value']) && $cpars['value'] == $params['option']) {echo 'selected';
                    $selected = true;}?>><?= $params['option'] ?></option>
								<?php } ?>

								<option value="" <?php if ($selected != true) {echo 'selected';}?>>Žádná vybraná možnost</option>
							</select>

						</div>
					</div>
<?php
        } else {
            if (isset($specs['id']) && $specs['id'] == 6 || $specs['id'] == 10) { ?>
	<div class="form-group">
						<label class="col-sm-3 control-label"><?= $specs['name'] ?></label>
						<div class="col-sm-5">
							<div class="radio" style="width: 100px; float: left;">
								<label>
									<input type="radio" name="<?= $specs['seoslug'] ?>" value="Ano" <?php if (isset($cpars['value']) && $cpars['value'] == "Ano") {echo 'checked';}?>>Ano
								</label>
							</div>
							<div class="radio" style="width: 100px;float: left;">
								<label>
									<input type="radio" name="<?= $specs['seoslug'] ?>" value="Ne" <?php if (isset($cpars['value']) && $cpars['value'] == "Ne") {echo 'checked';}?>>Ne
								</label>
							</div>

						</div>
					</div>

<?php } else { ?>
	<div class="form-group">
						<label class="col-sm-3 control-label"><?= $specs['name'] ?></label>
						<div class="col-sm-5">
							<div class="radio" style="width: 100px; float: left;">
								<label>
									<input type="radio" name="<?= $specs['seoslug'] ?>" value="Ano" <?php if (isset($cpars['value']) && $cpars['value'] == "Ano") {echo 'checked';}?>>Ano
								</label>
							</div>
							<div class="radio" style="width: 100px;float: left;">
								<label>
									<input type="radio" name="<?= $specs['seoslug'] ?>" value="Ne" <?php if (isset($cpars['value']) && $cpars['value'] == "Ne") {echo 'checked';}?>>Ne
								</label>
							</div>

						</div>
					</div>

<?php
            }

        }}
    ?>


				</div>

			</div>

		</div>
	</div>



			<center>
	<div class="form-group default-padding" style="margin-left: -100px;">

  <a href="./sauny?id=<?= $_REQUEST['id'] ?>"><button type="button" class="btn btn-primary">Zpět</button></a>
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

<?php include VIEW . '/default/footer.php'; ?>



<?php

} else {

    include INCLUDES . "/404.php";

}?>
