<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$pagetitle = "Přidat produkt";

$bread1 = "Editace produktů";
$abread1 = "editace-produktu";

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "add") {

    $name = odkazy($_POST['connect_name']);

    $insert = $mysqli->query("INSERT INTO warehouse_products (fullname, connect_name, code, customer, active, brand) VALUES ('" . $_POST['fullname'] . "','$name', '" . $_POST['code'] . "', '" . $_POST['optionsRadios'] . "', '" . $_POST['status'] . "', '" . $_POST['brand'] . "')") or die($mysqli->error);

    $id = $mysqli->insert_id;

    if ($_FILES['picture']['size'] != 0 && $_FILES['picture']['error'] == 0) {

        $im = new imagick($_FILES['picture']['tmp_name']);
        $im->scaleImage(200, 200, true);
        $im->writeImage($_SERVER['DOCUMENT_ROOT'] . '/admin/data/images/customer/' . $name .'.png');

//        $im->setImageBackgroundColor('white');
//
//        $w = $im->getImageWidth();
//        $h = $im->getImageHeight();
//        $im->extentImage(400, 400, ($w - 400) / 2, ($h - 400) / 2);


//        $targetFile = $_SERVER['DOCUMENT_ROOT'] . '/admin/data/images/customer/' . $name .'.png';
//        move_uploaded_file($tempFile, $targetFile); //6

    }

    Header("Location:https://www.wellnesstrade.cz/admin/pages/warehouse/editace-produktu?success=add");
    exit;
}

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

<form role="form" method="post" name="myform" class="form-horizontal form-groups-bordered validate" action="pridat-produkt?action=add" enctype="multipart/form-data">
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
						<label class="col-sm-3 control-label">Druh</label>
						<div class="col-sm-5">
							<div class="radio" style="width: 100px; float: left;">
								<label>
									<input type="radio" name="optionsRadios" value="1" class="virivkaradio" checked>Vířivka
								</label>
							</div>
							<div class="radio" style="width: 100px;float: left;">
								<label>
									<input type="radio" name="optionsRadios" value="0" class="saunaradio">Sauna
								</label>
							</div>

						</div>
					</div>

					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Název produktu</label>

						<div class="col-sm-5">
							<input type="text" class="form-control" id="field-2" name="fullname" value="">
						</div>
					</div>
					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Propojovací jméno</label>

						<div class="col-sm-5">
							<input type="text" class="form-control" id="field-2" name="connect_name" value="">
						</div>
					</div>
					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Značka produktu</label>

						<div class="col-sm-5">
                            <select name="brand" class="selectboxit">
                                <option value="">Žádná</option>
                                <option value="IQue">IQue</option>
                                <option value="Lovia">Lovia</option>
                                <option value="Quantum">Quantum</option>
                                <option value="SH Spas">SH Spas</option>
                                <option value="Swim SPA">Swim SPA</option>
                                <option value="Domo">Domo</option>
                                <option value="Espoo Deluxe">Espoo Deluxe</option>
                                <option value="Espoo Smart">Espoo Smart</option>
                            </select>
						</div>
					</div>
						<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Kód produktu</label>

						<div class="col-sm-5">
							<input type="text" class="form-control" id="field-2" name="code" value="">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Stav</label>
						<div class="col-sm-5">
							<div class="radio" style="width: 100px; float: left;">
								<label>
									<input type="radio" name="status" value="yes" checked>Aktivní
								</label>
							</div>
							<div class="radio" style="width: 100px;float: left;">
								<label>
									<input type="radio" name="status" value="no">Neaktivní
								</label>
							</div>

						</div>
					</div>
					    <div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Obrázek (pouze png)</label>

						<div class="col-sm-5">
							<input type="file" class="form-control" name="picture" id="field-file" placeholder="Placeholder">
						</div>
					</div>
					<hr>


				</div>

			</div>

		</div>
	</div>



			<center>
	<div class="form-group default-padding" style="margin-left: -100px;">
  
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

