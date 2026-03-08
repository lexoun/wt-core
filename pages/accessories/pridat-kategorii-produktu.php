<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$clientnamequery = $mysqli->query('SELECT * FROM demands where id = "' . $servis['clientid'] . '"') or die($mysqli->error);
$clientname = mysqli_fetch_array($clientnamequery);

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "add") {

    if ($_POST['name'] != "") {

        $seoslug = odkazy($_POST['name']);

        $insert = "INSERT INTO products_cats (name, seoslug, customer, status, spec) VALUES ('" . $_POST['name'] . "', '$seoslug', '" . $_POST['customer'] . "', '" . $_POST['shower'] . "', '" . $_POST['isspec'] . "')";

        $insertresults = $mysqli->query($insert) or die($mysqli->error);

        Header("Location:https://www.wellnesstrade.cz/admin/pages/accessories/kategorie-prislusenstvi?success=add");
        exit;
    }}

$categorytitle = "Příslušenství";
$pagetitle = "Přidání kategorie příslušenství";

    include VIEW . '/default/header.php';



$categoryquery = $mysqli->query('SELECT * FROM products_cats where id = "' . $_REQUEST['id'] . '"') or die($mysqli->error);

$category = mysqli_fetch_assoc($categoryquery);

?>
<form role="form" method="post" class="form-horizontal form-groups-bordered validate" action="pridat-kategorii-prislusenstvi?action=add" enctype="multipart/form-data">

	<div class="row">

		<div class="col-md-12">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Přidání kategorie příslušenství
					</div>

				</div>

						<div class="panel-body">

					<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Název</label>

						<div class="col-sm-5">
							<input type="text" class="form-control" id="field-1" name="name" value="">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Druh produktu</label>

						<div class="col-sm-5">
							<div class="radio" style="width: 100px; margin-left: 10px;float: left;">
								<label>
									<input type="radio" name="customer" id="optionsRadios1" value="1" checked>Vířivka
								</label>
							</div>
							<div class="radio" style="width: 180px; margin-left: 10px;float: left;">
								<label>
									<input type="radio" name="customer" id="optionsRadios2" value="0">Sauna
								</label>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-3 control-label">Zobrazovat pro klienty</label>

						<div class="col-sm-5">
							<div class="radio" style="width: 100px; margin-left: 10px;float: left;">
								<label>
									<input type="radio" name="shower" id="optionsRadios1" value="0" checked>Ano
								</label>
							</div>
							<div class="radio" style="width: 180px; margin-left: 10px;float: left;">
								<label>
									<input type="radio" name="shower" id="optionsRadios2" value="1">Ne
								</label>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-3 control-label">Je specifikací? (pouze pro sauny)</label>

						<div class="col-sm-5">
							<div class="radio" style="width: 100px; margin-left: 10px;float: left;">
								<label>
									<input type="radio" name="isspec" id="optionsRadios1" value="1">Ano
								</label>
							</div>
							<div class="radio" style="width: 180px; margin-left: 10px;float: left;">
								<label>
									<input type="radio" name="isspec" id="optionsRadios2" value="0" checked>Ne
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
  <a href="kategorie-prislusenstvi"><button type="button" class="btn btn-primary">Zpět</button></a>
		<button type="submit" class="btn btn-success">Přidat kategorii</button>
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

