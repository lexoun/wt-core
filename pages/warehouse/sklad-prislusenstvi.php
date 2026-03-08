<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$id = $_REQUEST['id'];

$location_query = $mysqli->query("SELECT * FROM shops_locations WHERE id = '" . $id . "'") or die($mysqli->error);
$location = mysqli_fetch_array($location_query);

$pagetitle = $location['name'];

include VIEW . '/default/header.php';

?>

<div class="panel-body">
	<div class="invoice">

		<div class="row">

		<div class="col-sm-6">
			<h3 style="margin-bottom: 16px; margin-top: 2px; float: left;"><?= $location['name'] ?></h3>
		</div>

		<div class="col-sm-6" style="float: right; text-align: right;">

			<a href="/admin/controllers/generators/warehouse-accessories?id=<?= $id ?>" style="padding: 10px 18px 10px 50px; height: 36px; float: right; margin-left: 14px;" class="btn btn-blue btn-icon icon-left">Stáhnout <i class="fa fa-download" style="     padding: 10px 12px;"></i></a>
			<div class=" btn-group" style="float: right;">

				<?php

$location_query = $mysqli->query("SELECT * FROM shops_locations");
while ($loc = mysqli_fetch_array($location_query)) { ?>

						<a class="btn btn-white <?php if ($loc['id'] == $id) {echo 'active';}?>" href="/admin/pages/warehouse/sklad-prislusenstvi?id=<?= $loc['id'] ?>" style="padding: 5px 11px !important;"><?= $loc['name'] ?></a>

					<?php } ?>
				</div>



		</div>

		<table class="table table-bordered">
			<thead>
				<tr>
					<th>Položka</th>
					<th class="text-center">Počet skladem</th>
					<th class="text-center">Nákupní cena mj.</th>
					<th class="text-center">Nákupní cena celkem</th>
				</tr>
			</thead>
			<tbody>

				<?php

$total = 0;
$instock = 0;

$data_query = $mysqli->query("SELECT p.id as product_id, s.instock as instock, 0 as variation_id, p.purchase_price FROM products_stocks s, products p WHERE s.location_id = '" . $location['id'] . "' AND s.product_id = p.id AND s.instock > 0 and p.type = 'simple' UNION ALL SELECT p.id as product_id, s.instock as instock, v.id as variation_id, v.purchase_price FROM products_stocks s, products p, products_variations v WHERE s.location_id = '" . $location['id'] . "' AND s.product_id = p.id AND s.variation_id = v.id AND v.product_id = p.id AND s.instock > 0 and p.type = 'variable'") or die($mysqli->error);
while ($data = mysqli_fetch_assoc($data_query)) {

    ?>

	<tr>
				<td><?php get_product_list($data);?></td>
				<td class="text-center" style="vertical-align: middle;"><?= $data['instock'] ?></td>
				<td style="vertical-align: middle; text-align: right;"><?= number_format($data['purchase_price'], 0, ',', ' ') . ' Kč' ?></td>
				<td style="vertical-align: middle; text-align: right;"><?= number_format($data['instock'] * $data['purchase_price'], 0, ',', ' ') . ' Kč' ?></td>


			<?php

    $instock = $instock + $data['instock'];
    $total = $total + ($data['instock'] * $data['purchase_price']);

}

?>


			</tbody>
		</table>

		</div>

	</div>


	<h3 style="margin-bottom: 16px;">Počet kusů: <u><?= $instock ?> ks</u> / Celková hodnota: <u><?= number_format($total, 0, ',', ' ') . ' Kč' ?></u></h3>

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

<style>

.page-body .selectboxit-container .selectboxit-options { margin-top: 40px !important; width: 100% !important;}
.page-body .selectboxit-container .selectboxit { height: 40px;width: 100% !important;}
.page-body .selectboxit-container .selectboxit .selectboxit-text { line-height: 40px; }
.page-body .selectboxit-container .selectboxit .selectboxit-arrow-container { height: 40px;}
.page-body .selectboxit-container .selectboxit .selectboxit-arrow-container:after { line-height: 40px;}
</style>



</div>
</div>



</div>
</div>


<?php include VIEW . '/default/footer.php'; ?>

