<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";

include INCLUDES . "/googlelogin.php";
include INCLUDES . "/functions.php";

$hottubquery = $mysqli->query('SELECT * FROM warehouse WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);
$hottub = mysqli_fetch_assoc($hottubquery);

if ($hottub['demand_id'] != 0) {

    $demandquery = $mysqli->query("SELECT user_name FROM demands WHERE id = '" . $hottub['demand_id'] . "'");
    $demand = mysqli_fetch_array($demandquery);

}

$pagetitle = "Přidat saunu";

$bread1 = "Sauny";
$abread1 = "sauny";

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "add") {

    $insert = $mysqli->query("INSERT INTO warehouse (location_id, ram, description, loadingdate, product, status, demand_id, customer, purchase_price, sale_price, serial_number) VALUES ('" . $_POST['location_id'] . "', '" . $_POST['ram'] . "','" . $_POST['description'] . "','" . $_POST['loadingdate'] . "','" . $_POST['virivkatype'] . "','" . $_POST['status'] . "','" . $_POST['demand'] . "','0','" . $_POST['purchase_price'] . "','" . $_POST['sale_price'] . "','" . $_POST['serialnumber'] . "')") or die($mysqli->error);
    $id = $mysqli->insert_id;

    $specsquery = $mysqli->query('SELECT * FROM specs WHERE product = 0 AND supplier = 1') or die($mysqli->error);

    $specnaz = "";
    while ($specs = mysqli_fetch_array($specsquery)) {
        $specsslug = $specs['seoslug'];
        $insrt = $_POST[$specsslug];
        $spcid = $specs['id'];
        $insert_specs = "INSERT INTO warehouse_specs_bridge (value, specs_id, client_id) VALUES ('$insrt', '$spcid', '$id')";
        $insert_specs_result = $mysqli->query($insert_specs) or die($mysqli->error);

        $parametter = $specs['name'] . ': ' . $insrt;

        if ($specnaz != "") {

            $specnaz = $specnaz . ', ' . $parametter;

        } else {

            $specnaz = $parametter;

        }

    }

    if ($_POST['demand'] != "" && $_POST['demand'] != 0) {

        $_REQUEST['id'] = $_POST['demand'];

        saveCalendarEvent($_POST['demand'], 'realization');

    }

    Header("Location:https://www.wellnesstrade.cz/admin/pages/warehouse/zobrazit-saunu?id=" . $id . "&success=edit");
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

<form role="form" method="post" name="myform" class="form-horizontal form-groups-bordered validate" action="pridat-saunu?action=add">
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

	<label class="col-sm-3 control-label" style="padding-top: 28px;">Značka</label>
	<div class="col-sm-5">
<?php

$brands_query = $mysqli->query("SELECT DISTINCT brand FROM warehouse_products WHERE active = 'yes' AND customer = 0 AND brand != ''");

$i = 0;
while ($brand = mysqli_fetch_array($brands_query)) {

    $i++;

    if ($i == 3) {$i = 1;}
    ?>

<script type="text/javascript">


jQuery(document).ready(function($)
{

	$('#click_brand_<?= $brand['brand'] ?>').click(function() {

		$(".brand-clicks .tile-stats").removeClass('tile-primary');
		$(".brand-clicks .tile-stats").addClass('tile-gray	');

		$("#click_brand_<?= $brand['brand'] ?> .tile-stats").addClass('tile-primary');

		$(".brand-clicks .tile-primary").removeClass('tile-gray	');

		$(".brand-groups").hide('slow');
		$(".brand-groups .setname").attr('name', '');


		$("#show_brand_<?= $brand['brand'] ?>").show('slow');
		$("#show_brand_<?= $brand['brand'] ?> .setname").attr('name', 'virivkatype');


	});

});

</script>




		<div id="click_brand_<?= $brand['brand'] ?>" class="brand-clicks col-sm-6" style="cursor:pointer; <?php if ($i == 1) { ?>padding-left: 0; padding-right: 5px;<?php } else { ?>padding-left: 5px; padding-right: 0;<?php } ?>">
					<div class="tile-stats tile-gray">
						<div class="icon" style="top: 20px !important;"><i></i></div>
						<div class="num"></div> <h3><?= $brand['brand'] ?></h3> <p></p>
					</div>
				</div>







<?php } ?>


			</div>

			</div>

<?php

mysqli_data_seek($brands_query, 0);

while ($brand = mysqli_fetch_array($brands_query)) {

    ?>
	<div id="show_brand_<?= $brand['brand'] ?>" class="form-group brand-groups" style="display: none;">
						<label class="col-sm-3 control-label">Typ sauny</label>

						<div class="col-sm-5">

							<select class="form-control setname" name="">
								<option value="">Vyberte saunu</option>
								<?php

    $sauna_query = $mysqli->query("SELECT * FROM warehouse_products WHERE active = 'yes' AND customer = 0 AND brand = '" . $brand['brand'] . "' ORDER BY brand");
    while ($sauna = mysqli_fetch_array($sauna_query)) { ?>
								<option value="<?= $sauna['connect_name'] ?>"><?php if (isset($sauna['code'])) {echo $sauna['code'];?> - <?php }
        echo $sauna['brand'] . ' ' . ucfirst($sauna['fullname']);?></option><?php } ?>
							</select>

						</div>
					</div>

<?php } ?>



				<div class="form-group">
						<label class="col-sm-3 control-label">K poptávce</label>

						<div class="col-sm-5">
						<?php

$demandsq = $mysqli->query("SELECT user_name, id FROM demands WHERE customer = 0") or die($mysqli->error);

?>
						<select name="demand" class="select2" data-allow-clear="true" data-placeholder="Vyberte poptávku...">
								<option></option>
								<optgroup label="SAUNA poptávky">
									<?php while ($dem = mysqli_fetch_array($demandsq)) {
    $find = $mysqli->query("SELECT id FROM warehouse WHERE demand_id = '" . $dem['id'] . "'");
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
									<input type="radio" name="status" value="0" checked>Ve výrobě
								</label>
							</div>
							<div class="radio" style="margin-left: 31px; float: left;">
								<label>
									<input type="radio" name="status" value="1">Na cestě
								</label>
							</div>
							<div class="radio" style="margin-left: 31px; float: left;">
								<label>
									<input type="radio" name="status" value="2">Na skladě
								</label>
							</div>
							<div class="radio" style="margin-left: 30px; float: left;">
								<label>
									<input type="radio" name="status" value="3">Na showroomu
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
									<input type="radio" name="location_id" value="<?= $warehouse['id'] ?>" <?php if ($warehouse['id'] == '1') {echo 'checked';}?>><?= $warehouse['name'] ?>
								</label>
							</div>
							<?php } ?>
						</div>
					</div>

						<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Očekávané naskladnění<br>(pouze pro stav na cestě)</label>

						<div class="col-sm-5">
							<input type="date" class="form-control" id="field-2" name="loadingdate" value="">
						</div>
					</div>
					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Unikátní číslo produktu</label>

						<div class="col-sm-5">
							<input type="text" class="form-control" id="field-2" name="serialnumber" value="">
						</div>
					</div>
	<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Prodejní cena</label>

						<div class="col-sm-5">
							<input type="text" class="form-control" id="field-2" style="float:left; width: 91.9%;" name="sale_price" value="">
							<span class="input-group-addon" style="float:left; padding: 9px 25px 8px 9px;">Kč</span>
						</div>
					</div>
					<div class="form-group" >
						<label for="field-2" class="col-sm-3 control-label">Nákupní cena</label>

						<div class="col-sm-2">
							<input type="text" class="form-control" style="float:left; width: 77.6%;" id="field-2" name="purchase_price" value="">
							<span class="input-group-addon" style="float:left; padding: 9px 25px 8px 9px;">Kč</span>
						</div>
						<label for="field-2" class="col-sm-1 control-label">Reálně inkasováno</label>

						<div class="col-sm-2">
							<input type="text" class="form-control" style="float:left; width: 77.6%;" id="field-2" name="real_price" value="">
							<span class="input-group-addon" style="float:left; padding: 9px 25px 8px 9px;">Kč</span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Rám</label>
						<div class="col-sm-5">
							<div class="radio" style="width: 100px; float: left;">
								<label>
									<input type="radio" name="ram" value="1" checked>S rámem
								</label>
							</div>
							<div class="radio" style="width: 100px;float: left;">
								<label>
									<input type="radio" name="ram" value="0">Bez rámu
								</label>
							</div>

						</div>
					</div>
					<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Dodatečný popisek</label>

						<div class="col-sm-5">
							<textarea class="form-control autogrow" id="field-ta" name="description" style="overflow: hidden; word-wrap: break-word; resize: horizontal; height: 48px;"></textarea>
						</div>
					</div>


                            <?php
                            $specsquery = $mysqli->query('SELECT * FROM specs WHERE product = 0 AND supplier = 1 order by type desc, id asc') or die($mysqli->error);
                            while ($specs = mysqli_fetch_assoc($specsquery)) {

                                $cparsquery = $mysqli->query('SELECT * FROM warehouse_specs_bridge WHERE specs_id = "' . $specs['id'] . '"') or die($mysqli->error);
                                $cpars = mysqli_fetch_array($cparsquery);

                                if (isset($specs['type']) && $specs['type'] == 1) {

                                    $paramsquery = $mysqli->query('SELECT * FROM specs_params WHERE spec_id = "' . $specs['id'] . '" order by id asc') or die($mysqli->error);

                                    ?>
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label"><?= $specs['name'] ?></label>

                                        <div class="col-sm-9">

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
                                    ?>
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label"><?= $specs['name'] ?></label>
                                        <div class="col-sm-9">
                                            <div class="radio" style="width: 100px; float: left;">
                                                <label>
                                                    <input type="radio" name="<?= $specs['seoslug'] ?>" value="Ano">Ano
                                                </label>
                                            </div>
                                            <div class="radio" style="width: 100px;float: left;">
                                                <label>
                                                    <input type="radio" name="<?= $specs['seoslug'] ?>" value="Ne" checked>Ne
                                                </label>
                                            </div>

                                        </div>
                                    </div>

                                    <?php

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