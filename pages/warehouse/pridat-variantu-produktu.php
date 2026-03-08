<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$pagetitle = "Přidat produkt";

$bread1 = "Editace produktů";
$abread1 = "editace-produktu";

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "add") {

    $seo_url = odkazy($_POST['name']);

    $insert = $mysqli->query("INSERT INTO warehouse_products_types (warehouse_product_id, name, seo_url) VALUES ('" . $_REQUEST['id'] . "','" . $_POST['name'] . "','$seo_url')") or die($mysqli->error);

    Header("Location:https://www.wellnesstrade.cz/admin/pages/warehouse/editace-produktu?success=add");
    exit;
}

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

<form role="form" method="post" name="myform" class="form-horizontal form-groups-bordered validate" action="pridat-variantu-produktu?action=add&id=<?= $_REQUEST['id'] ?>" enctype="multipart/form-data">
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
						<label for="field-2" class="col-sm-3 control-label">Název varianty</label>

						<div class="col-sm-5">
							<input type="text" class="form-control" id="field-2" name="name" value="">
						</div>
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

<?php include VIEW . '/default/footer.php'; ?>