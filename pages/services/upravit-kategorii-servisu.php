<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$servisquery = $mysqli->query('SELECT *, DATE_FORMAT(date, "%d. %m. %Y") as dateformated FROM services WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);
$servis = mysqli_fetch_assoc($servisquery);

$clientnamequery = $mysqli->query('SELECT * FROM demands where id = "' . $servis['clientid'] . '"') or die($mysqli->error);
$clientname = mysqli_fetch_array($clientnamequery);

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "doedit") {

    if ($_POST['title'] != "" && $_POST['price'] != "") {

        $insert = "UPDATE services_categories SET title = '" . $_POST['title'] . "', descriptions = '" . $_POST['descriptions'] . "', seoslug = '" . $_POST['seoslug'] . "', price = '" . $_POST['price'] . "', type = '" . $_POST['type'] . "' WHERE id = '" . $_REQUEST['id'] . "'";

        $insertresults = $mysqli->query($insert) or die($mysqli->error);

        Header("Location:https://www.wellnesstrade.cz/admin/pages/services/kategorie-servisu?success=edit");
        exit;
    }}

$categoryquery = $mysqli->query('SELECT * FROM services_categories where id = "' . $_REQUEST['id'] . '"') or die($mysqli->error);

if (mysqli_num_rows($categoryquery) > 0) {

    $category = mysqli_fetch_assoc($categoryquery);

    $spesl = " - " . $category['title'];
    $pagetitle = "Upravit kategorii servisu";

    $bread1 = "Kategorie servisu";
    $abread1 = "kategorie-servisu";


    include VIEW . '/default/header.php';

    ?>

<form role="form" method="post" class="form-horizontal form-groups-bordered validate" action="upravit-kategorii-servisu?id=<?= $_REQUEST['id'] ?>&action=doedit" enctype="multipart/form-data">

	<div class="row">

		<div class="col-md-12">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Úprava kategorie servisu
					</div>

				</div>

						<div class="panel-body">

					<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Název</label>

						<div class="col-sm-5">
							<input type="text" class="form-control" id="field-1" name="title" value="<?= $category['title'] ?>">
						</div>
					</div>

          	<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Popisek</label>

						<div class="col-sm-5">
							<input type="text" class="form-control" id="field-2" name="descriptions" value="<?= $category['descriptions'] ?>">
						</div>
					</div>
<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Seoslug (neměnit PLS!)</label>

						<div class="col-sm-5">
							<input type="text" class="form-control" id="field-2" name="seoslug" value="<?= $category['seoslug'] ?>">
						</div>
					</div>
					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Cena</label>

						<div class="col-sm-5">
							<input type="text" class="form-control" id="field-2" name="price" value="<?= $category['price'] ?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Cenový tarif</label>

						<div class="col-sm-5">
							<div class="radio" style="width: 100px; margin-left: 10px;float: left;">
								<label>
									<input type="radio" name="type" id="optionsRadios1" value="0" <?php if (isset($category['type']) && $category['type'] == 0) {echo 'checked';}?>>Jednorázově
								</label>
							</div>
							<div class="radio" style="width: 180px; margin-left: 10px;float: left;">
								<label>
									<input type="radio" name="type" id="optionsRadios2" value="1" <?php if (isset($category['type']) && $category['type'] == 1) {echo 'checked';}?>>Cena za hodinu
								</label>
							</div>
						</div>
					</div>


				</div>

			</div>

		</div>
	</div>



			<center>
	<div class="form-group default-padding" style="margin-left: -20px;">
  <a href="kategorie-servisu"><button type="button" class="btn btn-primary">Zpět</button></a>
		<button type="submit" class="btn btn-success">Upravit kategorii</button>
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
