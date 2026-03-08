<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$categorytitle = "Servis";
$pagetitle = "Kategorie servisů";

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "remove") {

    $mysqli->query('DELETE FROM services WHERE id = "' . $_REQUEST['id'] . '"') or die($mysqli->error);
    $mysqli->query('DELETE FROM services_categories_bridge WHERE servis_id = "' . $_REQUEST['id'] . '"') or die($mysqli->error);
    $displaysuccess = true;
    $successhlaska = "Servis nyní čeká na schválení našeho technika. Po schválení servisního termínu budete informování SMS a E-mailem.";
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "redo") {

    $updatequery = $mysqli->query('UPDATE services SET status = "1" WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);
    $displaysuccess = true;
    $successhlaska = "Servis nyní čeká na schválení našeho technika. Po schválení servisního termínu budete informování SMS a E-mailem.";
}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "edit") {
    $displaysuccess = true;
    $successhlaska = "Servis byl úspěšně upraven, juhůůů. Hláška, která půjde určitě upravit.";

}

    include VIEW . '/default/header.php';

    ?>

<div class="row">
	<div class="col-md-9 col-sm-7">
		<h2><?= $pagetitle ?></h2>
	</div>

	<div class="col-md-3 col-sm-5">

		<form method="get" role="form" class="search-form-full">

			<div class="form-group">
				<input type="text" class="form-control" name="s" id="search-input" placeholder="Hledání..." />
				<i class="entypo-search"></i>
			</div>

		</form>

	</div>
</div>

<div id="table-2_wrapper" class="dataTables_wrapper form-inline" role="grid"><table class="table table-bordered table-striped datatable dataTable" id="table-2" aria-describedby="table-2_info">
	<thead>
		<tr role="row"><th class="sorting_disabled" role="columnheader" rowspan="1" colspan="1" aria-label="



			" style="width: 29px;">
				<div class="checkbox checkbox-replace neon-cb-replacement">
					<label class="cb-wrapper"><input type="checkbox" id="chk-1"><div class="checked"></div></label>
				</div>
			</th><th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Student Name: activate to sort column ascending" style="width: 160px;">Název</th>
			<th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Average Grade: activate to sort column ascending" style="width: 300px;">Popis</th>
			<th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Curriculum / Occupation: activate to sort column ascending" style="width: 140px;">SEO adresa</th>
			<th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 100px;">Cena</th>
			<th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 100px;">Cenový tarif</th>
			<th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 350px;">Akce</th></tr>
	</thead>


<tbody role="alert" aria-live="polite" aria-relevant="all">
<?php
$scategquery = $mysqli->query('SELECT * FROM services_categories') or die($mysqli->error);
while ($scateg = mysqli_fetch_array($scategquery)) {
    scateg($scateg);
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

