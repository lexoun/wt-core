<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

if(isset($_REQUEST['site'])){ $site = $_REQUEST['site'];}

$pagetitle = "Statistika objednávek";

$clientquery = $mysqli->query('SELECT * FROM demands WHERE email="' . $_COOKIE['cookie_email'] . '"') or die($mysqli->error);
$client = mysqli_fetch_assoc($clientquery);

include VIEW . '/default/header.php';

$query = "";
$currentpage = "statistika-objednavek";
$allow_sites = "";

if (isset($site) && $site != "") {

    $query = ' AND order_site = "' . $site . '"';

}

$string_start = '';
$string_end = '';
$start_date = date('Y-m-d');
$end_date = date('Y-m-d');

if (isset($_REQUEST['start_date']) && $_REQUEST['start_date'] != "") {

	$start_date = $_REQUEST['start_date'];

    $query = $query . ' AND order_date >= "' . $_REQUEST['start_date'] . '"';

    $string_start = '&start_date=' . $_REQUEST['start_date'];

}

if (isset($_REQUEST['end_date']) && $_REQUEST['end_date'] != "") {

	$end_date = $_REQUEST['end_date'];

    $query = $query . ' AND order_date <= "' . $_REQUEST['end_date'] . '"';

    $string_end = '&end_date=' . $_REQUEST['end_date'];

}

?>


<div class="row">
	<div class="col-md-8 col-sm-7">
		<h2><?= $pagetitle ?></h2>
	</div>

</div>
<!-- Pager for search results --><div class="col-md-12 well" style="border-color: #ebebeb; margin-top: 8px;background-color: #fbfbfb;">


	<div class="btn-group col-sm-7" style="text-align: left; padding: 0;">


				<a href="?site=<?php echo $string_start;
echo $string_end; ?>"><label class="btn btn-lg <?php if (!isset($site) || $site == "") { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
											Vše
										</label></a>
						<a href="?site=wellnesstrade<?php echo $string_start;
echo $string_end; ?>"><label class="btn btn-lg <?php if (isset($site) && $site == "wellnesstrade") { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
							Klientské rozhraní
						</label></a>
						<a href="?site=saunahouse<?php echo $string_start;
echo $string_end; ?>"><label class="btn btn-lg <?php if (isset($site) && $site == "saunahouse") { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
							Saunahouse
						</label></a>

						<a href="?site=spamall<?php echo $string_start;
echo $string_end; ?>"><label class="btn btn-lg <?php if (isset($site) && $site == "spamall") { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
							Spamall
						</label></a>





					</div>

<form role="form" method="get" class="form-horizontal form-groups-bordered validate" action="statistika-objednavek" enctype="multipart/form-data" novalidate="novalidate">

	<div class="form-group col-sm-5" style=" padding: 0; margin: 0;">
							<input name="site" style="display: none;" value="<?php if(isset($site)){ echo $site; }?>">

							<input id="datum1pridatsem" type="text" class="form-control datepicker" name="start_date" data-format="yyyy-mm-dd" placeholder="Počáteční datum" style="height: 41px; width: 140px; margin-right: 10px; float: left;" value="<?= $start_date ?>">

							<input id="datum1pridatsem" type="text" class="form-control datepicker" name="end_date" data-format="yyyy-mm-dd" placeholder="Konečné datum" style="height: 41px; width: 140px; margin-right: 10px; float: left;" value="<?= $end_date ?>">

<button type="submit" style="padding: 10px 18px 10px 50px; height: 36px;" class="btn btn-blue btn-icon icon-left">
								Načíst
								<i class="fa fa-download" style="     padding: 10px 12px;"></i>
									</button>
						    </div>

						</form>


<div class="clear"></div>
</div><!-- Footer -->

<?php

$orders_numbers = 0;
$orders_total = 0;
$orders_purchase = 0;
$orders_delivery = 0;

function devat($value, $vat){
    return round((int)$value / (100 + (int)$vat) * 100);
}

$orders_query = $mysqli->query("SELECT * FROM orders WHERE order_status = '3'$query");
while ($orders = mysqli_fetch_array($orders_query)) {

    $orders_delivery += devat($orders['delivery_price'], $orders['vat']);
    $orders_total += devat($orders['total'], $orders['vat']) - devat($orders['delivery_price'], $orders['vat']);

    $orders_numbers++;
}

$orders_purchase = round($orders_total / 100 * 70);

?>

<div class="member-entry">

    <div style="width: 28%; float: left;"><h3 style="margin-top: 8.5px;"><span style=" font-size: 17px; color: #737881;">Nákupní cena:</span> <?= number_format($orders_purchase, 0, ',', ' ') ?> Kč</h3></div>
    <div style="width: 28%; float: left;"><h3 style="margin-top: 8.5px;"><span style=" font-size: 17px; color: #737881;">Prodejní cena:</span> <?= number_format($orders_total, 0, ',', ' ') ?> Kč</h3></div>
    <div style="width: 22%; float: left;"><h3 style="margin-top: 8.5px;"><span style=" font-size: 17px; color: #737881;">Zisk:</span> <?= number_format($orders_total - $orders_purchase, 0, ',', ' ') ?> Kč</h3></div>
    <div style="width: 22%; float: left;"><h3 style="margin-top: 8.5px;"><span style=" font-size: 17px; color: #737881;">Doprava:</span> <?= number_format($orders_delivery, 0, ',', ' ') ?> Kč</h3></div>

</div>

<?php

if (!isset($site) || $site == "") { ?>

<div class="member-entry">


<?php

    $sites_query = $mysqli->query("SELECT sum(total) as total, sum(delivery_price) as delivery_price, sum(order_purchase) as order_purchase, order_site FROM orders WHERE total < 20000 AND order_status = '3'$query AND order_site = 'wellnesstrade'") or die($mysqli->error);

    $sites = mysqli_fetch_array($sites_query);

    $total_without_delivery = $sites['total'] - $sites['delivery_price'];

    $orders_purchase = round($total_without_delivery / 100 * 70);

    $total = $total_without_delivery - $orders_purchase;
    ?>

<div style="width: 25%; float: left;"><h3 style="margin-top: 8.5px;"><span style=" font-size: 17px; color: #737881;">Wellnesstrade:</span> <?= number_format($total, 0, ',', ' ') ?> Kč</h3></div>

<?php

    $sites_query = $mysqli->query("SELECT sum(total) as total, sum(delivery_price) as delivery_price, sum(order_purchase) as order_purchase, order_site FROM orders WHERE order_status = '3'$query AND order_site = 'saunahouse'") or die($mysqli->error);

    $sites = mysqli_fetch_array($sites_query);

    $total_without_delivery = $sites['total'] - $sites['delivery_price'];

    $orders_purchase = round($total_without_delivery / 100 * 70);

    $total = $total_without_delivery - $orders_purchase;
    ?>

<div style="width: 25%; float: left;"><h3 style="margin-top: 8.5px;"><span style=" font-size: 17px; color: #737881;">Saunahouse:</span> <?= number_format($total, 0, ',', ' ') ?> Kč</h3></div>


<?php

    $sites_query = $mysqli->query("SELECT sum(total) as total, sum(delivery_price) as delivery_price, sum(order_purchase) as order_purchase, order_site FROM orders WHERE order_status = '3'$query AND order_site = 'spahouse'") or die($mysqli->error);

    $sites = mysqli_fetch_array($sites_query);

    $total_without_delivery = $sites['total'] - $sites['delivery_price'];

    $orders_purchase = round($total_without_delivery / 100 * 70);

    $total = $total_without_delivery - $orders_purchase;
    ?>

<div style="width: 25%; float: left;"><h3 style="margin-top: 8.5px;"><span style=" font-size: 17px; color: #737881;">Spahouse:</span> <?= number_format($total, 0, ',', ' ') ?> Kč</h3></div>


<?php

    $sites_query = $mysqli->query("SELECT sum(total) as total, sum(delivery_price) as delivery_price, sum(order_purchase) as order_purchase, order_site FROM orders WHERE total < 20000 AND order_status = '3'$query AND order_site = 'spamall'") or die($mysqli->error);

    $sites = mysqli_fetch_array($sites_query);

    $total_without_delivery = $sites['total'] - $sites['delivery_price'];

    $orders_purchase = round($total_without_delivery / 100 * 70);

    $total = $total_without_delivery - $orders_purchase;
    ?>

<div style="width: 25%; float: left;"><h3 style="margin-top: 8.5px;"><span style=" font-size: 17px; color: #737881;">Spamall:</span> <?= number_format($total, 0, ',', ' ') ?> Kč</h3></div>



</div>

<?php } ?>


<div class="member-entry">
<?php

$chart_query = $mysqli->query("SELECT year(order_date) as year, month(order_date) as month, sum(total) as total, sum(delivery_price) as delivery_price, sum(order_purchase) as order_purchase, month(order_date) as month, year(order_date) as year FROM orders WHERE total < 20000 AND order_status = '3'$query GROUP BY year(order_date), month(order_date) ORDER BY year(order_date) desc");

?>


<div id="chart10" style="height: 300px"></div>
	<script>


	$(document).ready(function()
	{


	// Area Chart
			Morris.Area({
				element: 'chart10',
				data: [

				<?php

while ($chart = mysqli_fetch_array($chart_query)) {
    $total_without_delivery = $chart['total'] - $chart['delivery_price'];

    // PODLE PURCHASE
    //$total = $total_without_delivery - $chart['order_purchase'];

    $minus = round($chart['total'] / 100 * 70);
    // 30% purchase price z total value
    $total = $total_without_delivery - $minus;
    ?>
{ d: '<?= $chart['year'] . "-" . $chart['month'] ?>', a: <?= str_replace(",", ".", $chart['total']) ?>, b: <?= str_replace(",", ".", $minus) ?>, c: <?= str_replace(",", ".", $total) ?>},
<?php
}

?>
				],
				xkey: 'd',
				behaveLikeLine: true,
				postUnits: ' Kč',
				ykeys: ['a','b','c'],
				labels: ['Celkem','Náklady' , 'Čistý zisk']
			});
});

	</script>



</div>

<!-- Pager for search results --><div class="row">
	<div class="col-md-12">
		<center>

		<h1 style="margin-bottom: 50px;">Počet objednávek: <?= $orders_numbers ?><br><small>ceny bez DPH</small></h1>
            <h5 >U příslušneství (materiálu) bez nákupní ceny se počítá defaultní marže na 30%</h5>
	</center>
	</div>
</div><!-- Footer -->

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



