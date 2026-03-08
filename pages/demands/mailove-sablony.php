<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$pagetitle = "Mailové šablony";
$spesl = " - Poptávky";

$bread1 = "Poptávky";
$bread2 = "Maily";

if (isset($_REQUEST['customer'])) {$customer = $_REQUEST['customer'];}
if (isset($_REQUEST['category'])) {$category = $_REQUEST['category'];}

if (isset($_REQUEST['od'])) {$od = $_REQUEST['od'];}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "remove") {

    $mysqli->query('DELETE FROM demands_mails_templates WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);

    $displaysuccess = true;
    $successhlaska = "Šablona byla úspěšně smazána.";
}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "add") {
    $displaysuccess = true;
    $successhlaska = "Šablona byla úspěšně přidána.";

}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "edit") {
    $displaysuccess = true;
    $successhlaska = "Šablona byla úspěšně upravena.";

}

$virivkyquery = $mysqli->query("SELECT * FROM warehouse_products WHERE customer = 1 ORDER BY brand");

$saunyquery = $mysqli->query("SELECT * FROM warehouse_products WHERE customer = 0 ORDER BY code");

include VIEW . '/default/header.php';
?>

<div class="row">
	<div class="col-md-9 col-sm-7">
		<h2><?= $pagetitle ?></h2>
	</div>

	<div class="col-md-3 col-sm-5" style="text-align: right;float:right;">


				<a href="pridat-mail" style=" margin-right: 24px;" class="btn btn-default btn-icon icon-left btn-lg">
					<i class="entypo-plus"></i>
					Přidat šablonu
				</a>

	</div>
</div>
<!-- Pager for search results --><div class="row" style="margin-bottom: 24px; margin-top: 20px;">
	<div class="col-md-12">
		<div class="btn-group" style="text-align: left;">

						<a href="mailove-sablony"><label class="btn btn-white btn-lg <?php if (!isset($customer)) {echo 'active';}?>">
							Vše
						</label></a>
						<a href="?customer=1"><label class="btn btn-white btn-lg <?php if ($customer == 1) {echo 'active';}?>">
							Vířivky
						</label></a>

						<a href="?customer=0"><label class="btn btn-white btn-lg <?php if ($customer == 0 && isset($customer)) {echo 'active';}?>">
							Sauny
						</label></a>


					</div>
		</div>

</div><!-- Footer -->
<div id="table-2_wrapper" class="dataTables_wrapper form-inline" role="grid"><table class="table table-bordered table-striped datatable dataTable" id="table-2" aria-describedby="table-2_info">
	<thead>
		<tr role="row"><th class="sorting_disabled" role="columnheader" rowspan="1" colspan="1" aria-label="



			" style="width: 29px;">
				<div class="checkbox checkbox-replace neon-cb-replacement">
					<label class="cb-wrapper"><input type="checkbox" id="chk-1"><div class="checked"></div></label>
				</div>
			</th><th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Student Name: activate to sort column ascending">Předmět</th>
			<th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Curriculum / Occupation: activate to sort column ascending">Šablona pro</th>
			<th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Curriculum / Occupation: activate to sort column ascending">Kategorie</th>
			<th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 200px;">Akce</th></tr>
	</thead>


<tbody role="alert" aria-live="polite" aria-relevant="all">
<?php
if (isset($customer) && isset($category)) {

    $currentpage = 'editace-manualu?customer=' . $customer . '&category=' . $category;

    $servismaxquery = $mysqli->query("SELECT COUNT(*) AS NumberOfOrders FROM demands_mails_templates WHERE product = '$category' order by id desc") or die($mysqli->error);
    $servismaxquery = mysqli_fetch_array($servismaxquery);
    $max = $servismaxquery['NumberOfOrders'];
    if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {$od = 1;}
    $perpage = 12;

    $s_lol = $od - 1;
    $s_pocet = $s_lol * $perpage;
    $pocet_prispevku = $max;
    $manualsquery = $mysqli->query("SELECT * FROM demands_mails_templates WHERE product = '$category' order by id desc limit " . $s_pocet . "," . $perpage) or die($mysqli->error);

} elseif (isset($customer)) {

    $currentpage = 'editace-manualu?customer=' . $customer;

    $servismaxquery = $mysqli->query("SELECT COUNT(*) AS NumberOfOrders FROM demands_mails_templates WHERE customer = '$customer'") or die($mysqli->error);
    $servismaxquery = mysqli_fetch_array($servismaxquery);
    $max = $servismaxquery['NumberOfOrders'];
    if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {$od = 1;}
    $perpage = 12;

    $s_lol = $od - 1;
    $s_pocet = $s_lol * $perpage;
    $pocet_prispevku = $max;
    $manualsquery = $mysqli->query("SELECT * FROM demands_mails_templates WHERE customer = '$customer' order by id desc limit " . $s_pocet . "," . $perpage) or die($mysqli->error);

} elseif (isset($category)) {

    $currentpage = 'editace-manualu?category=' . $category;

    $servismaxquery = $mysqli->query("SELECT COUNT(*) AS NumberOfOrders FROM demands_mails_templates WHERE product = '$category'") or die($mysqli->error);
    $servismaxquery = mysqli_fetch_array($servismaxquery);
    $max = $servismaxquery['NumberOfOrders'];
    if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {$od = 1;}
    $perpage = 12;

    $s_lol = $od - 1;
    $s_pocet = $s_lol * $perpage;
    $pocet_prispevku = $max;
    $manualsquery = $mysqli->query("SELECT * FROM demands_mails_templates WHERE product = '$category' order by id desc limit " . $s_pocet . "," . $perpage) or die($mysqli->error);

} else {

    $currentpage = 'editace-manualu';

    $servismaxquery = $mysqli->query("SELECT COUNT(*) AS NumberOfOrders FROM demands_mails_templates order by id desc") or die($mysqli->error);
    $servismaxquery = mysqli_fetch_array($servismaxquery);
    $max = $servismaxquery['NumberOfOrders'];
    if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {$od = 1;}
    $perpage = 12;

    $s_lol = $od - 1;
    $s_pocet = $s_lol * $perpage;
    $pocet_prispevku = $max;
    $manualsquery = $mysqli->query("SELECT * FROM demands_mails_templates order by id desc limit " . $s_pocet . "," . $perpage) or die($mysqli->error);

}

while ($manuals = mysqli_fetch_array($manualsquery)) {
    mails($manuals);
}

?>
</tbody></table>

<!-- Pager for search results --><div class="row">
	<div class="col-md-12">
		<center><ul class="pagination pagination-sm">
			<?php
include VIEW . "/default/pagination.php";?>
		</ul></center>
	</div>
</div><!-- Footer -->
</div>



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