<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

if (isset($_REQUEST['od'])) {$od = $_REQUEST['od'];}

if (isset($_REQUEST['customer'])) {$customer = $_REQUEST['customer'];}

$pagetitle = "Editace produktů";

$bread1 = "Sklad";

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "add") {
    $displaysuccess = true;
    $successhlaska = "Produkt byl úspěšně přidán.";
}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "remove") {
    $displaysuccess = true;
    $successhlaska = "Produkt byl úspěšně odstraněn.";
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "remove") {

    $unlinkquery = $mysqli->query('SELECT name FROM warehouse_products WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);
    $unlink = mysqli_fetch_assoc($unlinkquery);

    $result = glob($_SERVER['DOCUMENT_ROOT'] . "/admin/data/images/customer/" . $unlink['name'] . ".*");
    foreach ($result as $res) {
        unlink($res);
    }

    $result = glob($_SERVER['DOCUMENT_ROOT'] . "/admin/data/images/customer/" . $unlink['name'] . ".*");
    foreach ($result as $res) {
        unlink($res);
    }

    $mysqli->query('DELETE FROM warehouse_products WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);

    Header("Location:https://www.wellnesstrade.cz/admin/pages/warehouse/editace-produktu?success=remove");
    exit;
}

$saunyquery = $mysqli->query("SELECT * FROM warehouse_products WHERE active = 'yes' AND customer = 0 ORDER BY code");

include VIEW . '/default/header.php';

?>

<div class="row" style="margin-bottom: 16px;">
	<div class="col-md-10 col-sm-7">
		<h2 style="float: left"><?= $pagetitle ?></h2>
	</div>
	<div class="col-md-2 col-sm-5" style="text-align: right;float:right;">


				<a href="pridat-produkt-sklad" style=" margin-right: 16px;" class="btn btn-default btn-icon icon-left btn-lg">
					<i class="entypo-plus"></i>
					Přidat produkt
				</a>

	</div>
</div>


<?php

$warehousequery = $mysqli->query('SELECT * FROM warehouse_products ORDER BY customer desc, code asc') or die($mysqli->error);

while ($warehouse = mysqli_fetch_array($warehousequery)) {
    warehouse_products($warehouse);
}

?>


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