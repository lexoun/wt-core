<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$categorytitle = "Příslušenství";
$pagetitle = "Upravit výrobce";

$manufacturer_query = $mysqli->query("SELECT * FROM products_manufacturers WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

$manufacturer = mysqli_fetch_array($manufacturer_query);

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "edit") {

    $mysqli->query("UPDATE products_manufacturers SET delivery_time = '" . $_POST['delivery_time'] . "', email = '" . $_POST['email'] . "' WHERE id = '" . $_REQUEST['id'] . "'")or die($mysqli->error);



    if($manufacturer['delivery_time'] != $_POST['delivery_time']){

        $i = 0;

        $getProducts = $mysqli->query("SELECT id FROM products WHERE manufacturer = '".$manufacturer['id']."'")or die($mysqli->error);

        while($product = mysqli_fetch_assoc($getProducts)){

            $mysqli->query("INSERT IGNORE INTO cron_jobs (product_id) VALUES ('".$product['id']."')")or die($mysqli->error);

            $i++;
        }

//        echo $i;

    }

    Header("Location:https://www.wellnesstrade.cz/admin/pages/accessories/editace-vyrobcu");
    exit;

}

include VIEW . '/default/header.php';

?>

<div class="row">
	<div class="col-md-9 col-sm-7">
		<h2><?= $pagetitle ?></h2>
	</div>

	<div class="col-md-3 col-sm-5" style="text-align: right;float:right;">



	</div>
</div>




<form role="form" method="post" name="myform" class="form-horizontal form-groups-bordered validate" action="upravit-vyrobce?id=<?= $_REQUEST['id'] ?>&action=edit">
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
						<label for="field-2" class="col-sm-3 control-label">Výrobce</label>

						<div class="col-sm-8">
							<input type="text" class="form-control" id="field-2" name="manufacturer" value="<?= $manufacturer['manufacturer'] ?>" disabled>
						</div>
					</div>
					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Doba doručení</label>

						<div class="col-sm-8">
							<input type="text" class="form-control" id="field-2" name="delivery_time" value="<?= $manufacturer['delivery_time'] ?>">
						</div>
					</div>

					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">E-mail</label>

						<div class="col-sm-8">
							<input type="text" class="form-control" id="field-2" name="email" value="<?= $manufacturer['email'] ?>">
						</div>
					</div>





				</div>

			</div>

		</div>



	</div>



			<center>
	<div class="form-group default-padding" style="margin-left: -100px;">

  <a href="./editace-vyrobcu"><button type="button" class="btn btn-primary">Zpět</button></a>
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




