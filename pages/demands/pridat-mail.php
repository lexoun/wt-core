<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "add") {

    if ($_POST['title'] != "") {

        $text = $mysqli->real_escape_string($_POST['text']);

        $insert = $mysqli->query("INSERT INTO demands_mails_templates (title, type, customer, text) VALUES ('" . $_POST['title'] . "','" . $_POST['type'] . "','" . $_POST['customer'] . "','$text')") or die($mysqli->error);

        Header("Location:https://www.wellnesstrade.cz/admin/pages/demands/mailove-sablony?success=add");
        exit;

    }
}

$pagetitle = "Přidat šablonu";
$spesl = " - Poptávky";

$bread1 = "Poptávky";
$bread2 = "Maily";

$bread3 = "Mailové šablony";
$abread3 = "mailove-sablony";


include VIEW . '/default/header.php';

?>
 <script type="text/javascript">
jQuery(document).ready(function($)
{

$('.radioneh').click(function() {
   if($("input:radio[class='saunaradio']").is(":checked")) {


	$('.virivkens').hide( "slow");
	$('.saunkens').show( "slow");
   }
     if($("input:radio[class='virivkaradio']").is(":checked")) {


   	$('.saunkens').hide( "slow");
$('.virivkens').show( "slow");
   }
});

$('.radiozmrd').click(function() {
   if($("input:radio[class='provedeniradio']").is(":checked")) {


	$('.ovladens').hide( "slow");
	$('.provedens').show( "slow");
   }
     if($("input:radio[class='ovladaniradio']").is(":checked")) {


   	$('.provedens').hide( "slow");
$('.ovladens').show( "slow");
   }
});



});


</script>

<form role="form" method="post" class="form-horizontal form-groups-bordered validate" action="pridat-mail?action=add" enctype="multipart/form-data">

	<div class="row">

		<div class="col-md-12">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Přidat šablonu
					</div>

				</div>

						<div class="panel-body">


					<div class="form-group">
						<label class="col-sm-3 control-label">Šablona pro</label>
						<div class="col-sm-6">
							<div class="radio" style="width: 100px; float: left;">
								<label>
									<input type="radio" name="customer" value="1" checked>Vířivka
								</label>
							</div>
							<div class="radio" style="width: 100px;float: left;">
								<label>
									<input type="radio" name="customer" value="0">Sauna
								</label>
							</div>
							<div class="radio" style="width: 140px;float: left;">
								<label>
									<input type="radio" name="customer" value="3">Sauna + Vířivka
								</label>
							</div>
							<div class="radio" style="width: 140px;float: left;">
								<label>
									<input type="radio" name="customer" value="9">Textové (na úpravu)
								</label>
							</div>

						</div>
					</div>



					<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Předmět</label>

						<div class="col-sm-5">
							<input type="text" class="form-control" id="field-1" name="title" value="">
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-3 control-label">Kategorie</label>

						<div class="col-sm-5">

							<select class="form-control" name="type">
								<option value="zakladni" selected>Základní</option>
								<option value="microsilk">Microsilk</option>
								<option value="wipod">WiPod</option>
							</select>

						</div>
					</div>



      					<div class="form-group">
						<label for="field-1" class="col-sm-2 control-label">Obsah šablony</label>

						<div class="col-sm-9">
							<textarea class="form-control" name="text" style="height: 380px;"></textarea>
						</div>
					</div>


				</div>

			</div>

		</div>
	</div>



			<center>
	<div class="form-group default-padding" style="margin-left: -20px;">
  <a href="mailove-sablony"><button type="button" class="btn btn-primary">Zpět</button></a>
		<button type="submit" class="btn btn-success">Přidat šablonu</button>
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

