<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$categorytitle = "Příslušenství";
$pagetitle = "Kategorie příslušenství";

if (isset($_REQUEST['customer'])) {$customer = $_REQUEST['customer'];}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "remove") {

    $mysqli->query('DELETE FROM products_cats WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);
    $displaysuccess = true;
    $successhlaska = "Kategorie příslušenství byla úspěšně smazána.";
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "remove_spamall") {

    $mysqli->query('DELETE FROM shops_categories WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);

    $mysqli->query("DELETE FROM products_sites_categories WHERE category= '" . $_REQUEST['id'] . "' AND site = 'spamall'") or die($mysqli->error);
    $displaysuccess = true;
    $successhlaska = "Kategorie příslušenství byla úspěšně smazána.";
}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "add") {
    $displaysuccess = true;
    $successhlaska = "Kategorie příslušenství byla úspěšně přidání.";

}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "edit") {
    $displaysuccess = true;
    $successhlaska = "Kategorie příslušenství byla úspěšně upravena.";

}

include VIEW . '/default/header.php';
?>

<div class="row">
	<div class="col-md-9 col-sm-7">
		<h2><?= $pagetitle ?></h2>
	</div>

	<div class="col-md-3 col-sm-5" style="text-align: right;float:right;">


				<a href="pridat-kategorii-prislusenstvi" style=" margin-right: 24px;" class="btn btn-default btn-icon icon-left btn-lg">
					<i class="entypo-plus"></i>
					Přidat kategorii
				</a>

	</div>
</div>
<!-- Pager for search results --><div class="row" style="margin-bottom: 24px; margin-top: 20px;">
	<div class="col-md-12">
		<div class="btn-group" style="text-align: left;">

						<a href="kategorie-prislusenstvi"><label class="btn btn-white btn-lg <?php if (!isset($customer)) {echo 'active';}?>">
							Vše
						</label></a>
						<a href="?customer=1"><label class="btn btn-white btn-lg <?php if (isset($customer) && $customer == 1) {echo 'active';}?>">
							Vířivky
						</label></a>

						<a href="?customer=0"><label class="btn btn-white btn-lg <?php if (isset($customer) && $customer == 0 && isset($customer)) {echo 'active';}?>">
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
			</th><th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Student Name: activate to sort column ascending" style="width: 220px;">Název</th>


			<th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Curriculum / Occupation: activate to sort column ascending" style="width: 140px; text-align:center;">Druh kategorie</th>

			<th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Curriculum / Occupation: activate to sort column ascending" style="width: 140px; text-align:center;">Sleva kategorie</th>

			<th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Curriculum / Occupation: activate to sort column ascending" style="width: 220px;">SEO adresa</th>

			<th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 350px; text-align:right;">Akce</th></tr>
	</thead>


<tbody role="alert" aria-live="polite" aria-relevant="all">
<?php
if (isset($customer)) {

    $pcategquery = $mysqli->query('SELECT * FROM products_cats WHERE customer = "' . $customer . '"') or die($mysqli->error);

} else {

    $pcategquery = $mysqli->query('SELECT * FROM products_cats') or die($mysqli->error);
}

while ($pcateg = mysqli_fetch_array($pcategquery)) {
    pcateg($pcateg);
}

?>
</tbody></table></div>

<h2>Spamall</h2>
<div id="table-2_wrapper" class="dataTables_wrapper form-inline" role="grid"><table class="table table-bordered table-striped datatable dataTable" id="table-2" aria-describedby="table-2_info">
	<thead>
		<tr role="row"><th class="sorting_disabled" role="columnheader" rowspan="1" colspan="1" aria-label="



			" style="width: 29px;">
				<div class="checkbox checkbox-replace neon-cb-replacement">
					<label class="cb-wrapper"><input type="checkbox" id="chk-1"><div class="checked"></div></label>
				</div>
			</th><th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Student Name: activate to sort column ascending" style="width: 220px;">Název</th>


			<th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Curriculum / Occupation: activate to sort column ascending" style="width: 220px;">SEO adresa</th>

			<th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Curriculum / Occupation: activate to sort column ascending" style="width: 140px; text-align:center;">Sleva kategorie</th>


			<th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 350px; text-align:right;">Akce</th></tr>
	</thead>



<tbody role="alert" aria-live="polite" aria-relevant="all">
<?php

$pcategquery = $mysqli->query('SELECT * FROM shops_categories WHERE parent_id = 0 AND shop_id = 3') or die($mysqli->error);

while ($pcateg = mysqli_fetch_array($pcategquery)) { ?>
<tr class="even">
			<td class=" sorting_1">
				<div class="checkbox checkbox-replace neon-cb-replacement">
					<label class="cb-wrapper"><input type="checkbox" id="chk-1"><div class="checked"></div></label>
				</div>
			</td>
			<td class=" "><?= $pcateg['name'] ?></td>
			<td class=" " style=" text-align:center;"><strong><?= $pcateg['slug'] ?></strong></td>
			<td class=" " style=" text-align:center;"><?php if (isset($pcateg['customer']) && $pcateg['customer'] == 0) {echo 'Sauny';} else {echo 'Vířivky';}?></td>
			<td class=" " style="text-align: right;">

				<a href="upravit-kategorii-prislusenstvi?id=<?= $pcateg['id'] ?>" class="btn btn-primary btn-sm btn-icon icon-left">
					<i class="entypo-pencil"></i>
					Upravit
				</a>
				<a href="kategorie-prislusenstvi?action=remove_spamall&id=<?= $pcateg['id'] ?>" class="btn btn-danger btn-sm btn-icon icon-left">
					<i class="entypo-cancel"></i>
					Smazat
				</a>
			</td>
		</tr>
		<?php $subparents_query = $mysqli->query("SELECT * FROM shops_categories WHERE parent_id = '" . $pcateg['id'] . "' AND shop_id = 3");
    while ($subparents = mysqli_fetch_array($subparents_query)) { ?>
 										<tr class="even">
			<td class=" sorting_1">
				<div class="checkbox checkbox-replace neon-cb-replacement">
					<label class="cb-wrapper"><input type="checkbox" id="chk-1"><div class="checked"></div></label>
				</div>
			</td>
			<td class=" "> --- <?= $subparents['name'] ?></td>
			<td class=" " style=" text-align:center;"><strong><?= $subparents['slug'] ?></strong></td>
			<td class=" " style=" text-align:center;"><?php if (isset($subparents['customer']) && $subparents['customer'] == 0) {echo 'Sauny';} else {echo 'Vířivky';}?></td>
			<td class=" " style="text-align: right;">

				<a href="upravit-kategorii-prislusenstvi?id=<?= $subparents['id'] ?>" class="btn btn-primary btn-sm btn-icon icon-left">
					<i class="entypo-pencil"></i>
					Upravit
				</a>
				<a href="kategorie-prislusenstvi?action=remove_spamall&id=<?= $subparents['id'] ?>" class="btn btn-danger btn-sm btn-icon icon-left">
					<i class="entypo-cancel"></i>
					Smazat
				</a>
			</td>
		</tr>

		<?php }
}

?>
</tbody></table></div>



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



