<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$mailquery = $mysqli->query('SELECT * FROM demands_mails_templates where id = "' . $_REQUEST['id'] . '"') or die($mysqli->error);

if (mysqli_num_rows($mailquery) > 0) {

    $mail = mysqli_fetch_assoc($mailquery);

    if (isset($_REQUEST['action']) && $_REQUEST['action'] == "edit") {

        if ($_POST['title'] != "") {

            $insert = $mysqli->query("UPDATE demands_mails_templates SET title = '" . $_POST['title'] . "', type = '" . $_POST['type'] . "', customer = '" . $_POST['customer'] . "', text = '" . $_POST['text'] . "' WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

            Header("Location:https://www.wellnesstrade.cz/admin/pages/demands/mailove-sablony?success=edit");
            exit;

        }
    }

    $pagetitle = "Upravit šablonu";
    $spesl = " - Poptávky";

    $bread1 = "Poptávky";
    $bread2 = "Maily";

    $bread3 = "Mailové šablony";
    $abread3 = "mailove-sablony";

    $virivky = array("Silver", "Gold", "Diamond", "Platinum");

    $sauny = array("tiny", "dice", "cavalir", "home", "cube", "charm", "charisma", "exclusive", "lora", "mona", "deluxe", "grand");

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

<form role="form" method="post" class="form-horizontal form-groups-bordered validate" action="upravit-mail?id=<?= $_REQUEST['id'] ?>&action=edit" enctype="multipart/form-data">

	<div class="row">

		<div class="col-md-12">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Úprava šablonu
					</div>

				</div>

						<div class="panel-body">


					<div class="form-group">
						<label class="col-sm-3 control-label">Šablona pro</label>
						<div class="col-sm-6">
							<div class="radio" style="width: 100px; float: left;">
								<label>
									<input type="radio" name="customer" value="1" <?php if (isset($mail['customer']) && $mail['customer'] == 1) {echo 'checked';}?>>Vířivka
								</label>
							</div>
							<div class="radio" style="width: 100px;float: left;">
								<label>
									<input type="radio" name="customer" value="0" <?php if (isset($mail['customer']) && $mail['customer'] == 0) {echo 'checked';}?>>Sauna
								</label>
							</div>
							<div class="radio" style="width: 140px;float: left;">
								<label>
									<input type="radio" name="customer" value="3" <?php if (isset($mail['customer']) && $mail['customer'] == 3) {echo 'checked';}?>>Sauna + Vířivka
								</label>
							</div>
							<div class="radio" style="width: 140px;float: left;">
								<label>
									<input type="radio" name="customer" value="9" <?php if (isset($mail['customer']) && $mail['customer'] == 9) {echo 'checked';}?>>Textové (na úpravu)
								</label>
							</div>

						</div>
					</div>



					<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Předmět</label>

						<div class="col-sm-5">
							<input type="text" class="form-control" id="field-1" name="title" value="<?= $mail['title'] ?>">
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-3 control-label">Kategorie</label>

						<div class="col-sm-5">

							<select class="form-control" name="type">
								<option value="zakladni" <?php if (isset($mail['type']) && $mail['type'] == "zakladni") {echo 'selected';}?>>Základní</option>
								<option value="microsilk" <?php if (isset($mail['type']) && $mail['type'] == "microsilk") {echo 'selected';}?>>Microsilk</option>
								<option value="wipod" <?php if (isset($mail['type']) && $mail['type'] == "wipod") {echo 'selected';}?>>WiPod</option>
							</select>

						</div>
					</div>



      					<div class="form-group">
						<label for="field-1" class="col-sm-2 control-label">Obsah šablony</label>

						<div class="col-sm-9">
							<textarea class="form-control" name="text" style="height: 380px;"><?= $mail['text'] ?></textarea>
						</div>
					</div>


				</div>

			</div>

		</div>
	</div>



			<center>
	<div class="form-group default-padding" style="margin-left: -20px;">
  <a href="mailove-sablony"><button type="button" class="btn btn-primary">Zpět</button></a>
		<button type="submit" class="btn btn-success">Upravit šablonu</button>
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
