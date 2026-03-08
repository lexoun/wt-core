<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$clientnamequery = $mysqli->query('SELECT * FROM demands where id = "' . $servis['clientid'] . '"') or die($mysqli->error);
$clientname = mysqli_fetch_array($clientnamequery);

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "doedit") {

    $oldslugquery = $mysqli->query("SELECT seoslug FROM products_cats WHERE id = '" . $_REQUEST['id'] . "'");
    $oldslug = mysqli_fetch_array($oldslugquery);

    $seoslug = odkazy($_POST['name']);

    $insert = "UPDATE products_cats SET name = '" . $_POST['name'] . "', seoslug = '$seoslug', customer = '" . $_POST['customer'] . "', status = '" . $_POST['shower'] . "', spec = '" . $_POST['isspec'] . "', discount = '" . $_POST['discount'] . "' WHERE id = '" . $_REQUEST['id'] . "'";

    $insertresults = $mysqli->query($insert) or die($mysqli->error);

    $selector = $mysqli->query("SELECT id FROM products WHERE category = '" . $oldslug['seoslug'] . "'");
    while ($product = mysqli_fetch_array($selector)) {

        $update = $mysqli->query("UPDATE products SET category = '$seoslug' WHERE id = '" . $product['id'] . "'");

    }

    Header("Location:https://www.wellnesstrade.cz/admin/pages/accessories/kategorie-prislusenstvi?success=edit");
    exit;
}

$categoryquery = $mysqli->query('SELECT * FROM products_cats where id = "' . $_REQUEST['id'] . '"') or die($mysqli->error);

if (mysqli_num_rows($categoryquery) > 0) {

    $category = mysqli_fetch_assoc($categoryquery);

    $spesl = " - " . $category['name'];
    $pagetitle = "Upravit kategorii příslušenství";

    $bread1 = "Kategorie příslušenství";
    $abread1 = "kategorie-prislusenstvi";

    include VIEW . '/default/header.php';
    ?>

<form role="form" method="post" class="form-horizontal form-groups-bordered validate" action="upravit-kategorii-prislusenstvi?id=<?= $_REQUEST['id'] ?>&action=doedit" enctype="multipart/form-data">

	<div class="row">

		<div class="col-md-12">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Úprava kategorie příslušenství
					</div>

				</div>

						<div class="panel-body">

					<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Název</label>

						<div class="col-sm-5">
							<input type="text" class="form-control" id="field-1" name="name" value="<?= $category['name'] ?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Druh produktu</label>

						<div class="col-sm-5">
							<div class="radio" style="width: 100px; margin-left: 10px;float: left;">
								<label>
									<input type="radio" name="customer" id="optionsRadios1" value="1" <?php if (isset($category['customer']) && $category['customer'] == 1) {echo 'checked';}?>>Vířivka
								</label>
							</div>
							<div class="radio" style="width: 180px; margin-left: 10px;float: left;">
								<label>
									<input type="radio" name="customer" id="optionsRadios2" value="0" <?php if (isset($category['customer']) && $category['customer'] == 0) {echo 'checked';}?>>Sauna
								</label>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-3 control-label">Zobrazovat pro klienty</label>

						<div class="col-sm-5">
							<div class="radio" style="width: 100px; margin-left: 10px;float: left;">
								<label>
									<input type="radio" name="shower" id="optionsRadios1" value="0" <?php if (isset($category['status']) && $category['status'] == 0) {echo 'checked';}?>>Ano
								</label>
							</div>
							<div class="radio" style="width: 180px; margin-left: 10px;float: left;">
								<label>
									<input type="radio" name="shower" id="optionsRadios2" value="1" <?php if (isset($category['status']) && $category['status'] == 1) {echo 'checked';}?>>Ne
								</label>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-3 control-label">Je specifikací? (pouze pro sauny)</label>

						<div class="col-sm-5">
							<div class="radio" style="width: 100px; margin-left: 10px;float: left;">
								<label>
									<input type="radio" name="isspec" id="optionsRadios1" value="1" <?php if (isset($category['spec']) && $category['spec'] == 1) {echo 'checked';}?>>Ano
								</label>
							</div>
							<div class="radio" style="width: 180px; margin-left: 10px;float: left;">
								<label>
									<input type="radio" name="isspec" id="optionsRadios2" value="0" <?php if (isset($category['spec']) && $category['spec'] == 0) {echo 'checked';}?>>Ne
								</label>
							</div>
						</div>
					</div>


					<div class="form-group">
						<label for="discount" class="col-sm-3 control-label">Sleva na celou kategorii</label>

						<div class="col-sm-5">
							<input type="text" class="form-control" style="float:left; width: 20%;" id="discount" name="discount" value="<?= $category['discount'] ?>">
							<span class="input-group-addon" style="float:left; padding: 9px 25px 8px 9px;">%</span>
						</div>
					</div>



				</div>

			</div>

		</div>
	</div>



			<center>
	<div class="form-group default-padding" style="margin-left: -20px;">
  <a href="kategorie-prislusenstvi"><button type="button" class="btn btn-primary">Zpět</button></a>
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
