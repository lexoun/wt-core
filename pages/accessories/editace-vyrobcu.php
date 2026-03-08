<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$categorytitle = "Příslušenství";
$pagetitle = "Výrobci";

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "reset" && $_REQUEST['id'] != 0 && isset($_REQUEST['id'])) {

    $getProducts = $mysqli->query("SELECT id FROM products WHERE manufacturer = '".$_REQUEST['id']."'")or die($mysqli->error);

    while($product = mysqli_fetch_assoc($getProducts)){

        $mysqli->query("INSERT IGNORE INTO cron_jobs (product_id) VALUES ('".$product['id']."')")or die($mysqli->error);

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

<div id="table-2_wrapper" class="dataTables_wrapper form-inline" role="grid"><table class="table table-bordered table-striped datatable dataTable" id="table-2" aria-describedby="table-2_info">
	<thead>
		<tr role="row">
			<th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" style="width: 220px;">Název</th>
			<th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" style="width: 220px;">E-mail</th>
			<th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" style="width: 220px;">Doba dodání</th>
			<th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" style="width: 220px;">Počet produktů</th>
			<?php
$shops_query = $mysqli->query("SELECT * FROM shops") or die($mysqli->error);
while ($shop = mysqli_fetch_array($shops_query)) {
    ?>
			<th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" style="width: 220px;"><?= $shop['name'] ?> synchronizace</th>
			<?php } ?>
			<th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" style="width: 200px;">Akce</th>
		</tr>
	</thead>


<tbody role="alert" aria-live="polite" aria-relevant="all">
<?php

$select_query = $mysqli->query("SELECT m.id, m.delivery_time, m.manufacturer, m.email FROM products_manufacturers m")or die($mysqli->error);

while ($select = mysqli_fetch_assoc($select_query)) {

    $allCount = $mysqli->query("SELECT id FROM products WHERE manufacturer = '".$select['id']."'");

    $sqlquery = urlencode('manufacturer = '.$select['id']);

    //$insert_query = $mysqli->query("INSERT INTO products_manufacturers (manufacturer, delivery_time) VALUES ('".$select['value']."', '7')");

    ?>
<tr>
<td><?= $select['manufacturer'] ?></td>
<td><?= $select['email'] ?></td>
<td><?= $select['delivery_time'] ?></td>
<td><?= mysqli_num_rows($allCount) ?></td>
<?php
    $shops_query = $mysqli->query("SELECT * FROM shops") or die($mysqli->error);
    while ($shop = mysqli_fetch_array($shops_query)) {

        $get_total = $mysqli->query("SELECT p.id FROM products_sites sites, products p WHERE sites.site = '" . $shop['slug'] . "' AND p.id = sites.product_id AND p.manufacturer = '" . $select['id'] . "' GROUP BY p.id") or die($mysqli->error);

        $select_synchronize = $mysqli->query("SELECT p.id FROM products_sites sites, products p WHERE sites.site = '" . $shop['slug'] . "'  AND p.id = sites.product_id AND p.manufacturer = '" . $select['id'] . "' AND sites.delivery_time = '" . $select['delivery_time'] . "' GROUP BY p.id") or die($mysqli->error);

        ?>
<td><?= mysqli_num_rows($select_synchronize) . ' / ' . mysqli_num_rows($get_total) ?></td>
<?php } ?>
<td>

    <a href="editace-prislusenstvi?sqlquery=<?= $sqlquery ?>" class="btn btn-info btn-sm" style="margin-bottom: 6px;" target="_blank">
        <i class="entypo-search"></i>
    </a>

    <a href="upravit-vyrobce?id=<?= $select['id'] ?>" class="btn btn-primary btn-sm " style="margin-bottom: 6px;">
					<i class="entypo-pencil"></i>
				</a>


    <a href="editace-vyrobcu?id=<?= $select['id'] ?>&action=reset" class="btn btn-default btn-sm " style="margin-bottom: 6px;" data-toggle="tooltip" data-placement="top" title="" data-original-title="Zařadit všechny položky k aktumatické aktualizaci v e-shopech">
					<i class="entypo-ccw"></i>
				</a></td>
</tr>
<?php } ?>
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




