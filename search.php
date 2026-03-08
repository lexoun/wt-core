<?php



include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$categorytitle = "Vyhledávání";
$pagetitle = "Vyhledávání";
if (isset($_REQUEST['od'])) {$od = $_REQUEST['od'];}

$search = $_REQUEST['query'];

if (isset($_REQUEST['customer'])) {$customer = $_REQUEST['customer'];}
if (isset($_REQUEST['category'])) {$category = $_REQUEST['category'];}

$virivky = array("capri", "dreamline", "eden", "tahiti", "tonga", "trinidad");

$sauny = array("tiny", "cavalir", "home", "cube", "charm", "charisma", "exclusive", "lora", "mona", "deluxe", "grand");

$parts = explode(" ", $search);
$last = array_pop($parts);
$first = implode(" ", $parts);

if ($first == "") {
    $first = 0;
}
if ($last == "") {
    $last = 0;
}

$check_phone = preg_replace('/\s+/', '', $search);

$demandsquery = $mysqli->query("SELECT 
               d.*, p.*, w.*, ship.*, bill.*, 
               d.status as demand_status, d.product as product, w.id as warehouse_id, 
               d.customer as customer, w.serial_number, w.status, w.demand_id, d.id as id, 
               DATE_FORMAT(i.date, '%d. %m. %Y') as invoice_date, 
               DATE_FORMAT(i.payment_date, '%d. %m. %Y') as payment_date, 
               DATE_FORMAT(w.loadingdate, '%d. %m. %Y') as loading_date, 
               DATE_FORMAT(g.deadline_date, '%d. %m. %Y') as duerino, 
               DATE_FORMAT(d.date, '%d. %m. %Y') as dateformated, 
               DATE_FORMAT(d.realization, '%d. %m. %Y') as realizationformated 
            FROM demands d 
                LEFT JOIN demands_advance_invoices i ON i.demand_id = d.id AND i.status = 1 
                LEFT JOIN demands_generate_hottub g ON g.id = d.id 
                LEFT JOIN warehouse_products p ON p.connect_name = d.product 
                LEFT JOIN warehouse w ON w.demand_id = d.id 
                LEFT JOIN addresses_billing bill ON bill.id = d.billing_id 
                LEFT JOIN addresses_shipping ship ON ship.id = d.shipping_id 
            WHERE (d.phone like '%$search%' OR d.email like '%$search%') 
               OR (d.phone like '%$check_phone%') 
               OR (d.user_name like '%$search%' OR d.user_name like '%$last%' OR d.user_name like '%$first%')
               OR (ship.shipping_street like '%$search%' OR ship.shipping_city like '%$search%')
               OR (bill.billing_street like '%$search%' OR bill.billing_city like '%$search%')
            GROUP BY d.secretstring, d.user_name, d.id
            ORDER BY CASE 
              WHEN d.user_name like '$first $last' THEN 1
              WHEN d.user_name like '$first $last%' THEN 2
              WHEN d.user_name like '%$first $last' THEN 3
              WHEN d.user_name like '$last' THEN 4
			  WHEN d.user_name like '$last%' THEN 5
			  WHEN d.user_name like '%$last%' THEN 6
			  WHEN d.user_name like '$first' THEN 7
              WHEN d.user_name like '$last' THEN 8
              ELSE 9 END, d.user_name
	 LIMIT 200") or die($mysqli->error);

//echo mysqli_num_rows($demandsquery);

$ordersquery = $mysqli->query("SELECT *, o.id as id, p.name as pay_method, d.name as ship_method, DATE_FORMAT(order_date, '%d. %m. %Y') as dateformated, DATE_FORMAT(order_date, '%H:%i:%s') as hoursmins 
    FROM orders o 
        LEFT JOIN shops_payment_methods p ON o.payment_method = p.link_name 
        LEFT JOIN shops_delivery_methods d ON o.order_shipping_method = d.link_name 
        LEFT JOIN addresses_billing bill ON bill.id = o.billing_id 
        LEFT JOIN addresses_shipping ship ON ship.id = o.shipping_id 
    WHERE (ship.shipping_surname like '%$search%' OR ship.shipping_surname like '%$search%') 
       OR (ship.shipping_name like '%$search%') OR o.customer_email like '%$search%'")
    or die($mysqli->error);

include VIEW . '/default/header.php';

?>

<section class="search-results-env">

	<div class="row">
		<div class="col-md-12">


			<!-- Search categories tabs -->			<ul class="nav nav-tabs right-aligned">
				<li class="tab-title pull-left">
					<div class="search-string">Celkem nalezeno <?echo (mysqli_num_rows($demandsquery) + mysqli_num_rows($ordersquery)); ?> položek na hledaný výraz: <strong>&ldquo;<?= $search ?>&rdquo;</strong></div>
				</li>

				<li class="active" style="font-size: 18px;">
					<a href="#demands">
						Poptávky
						<span class="disabled-text">(<?echo mysqli_num_rows($demandsquery); ?>)</span>
					</a>
				</li>
				<li>
					<a href="#orders" style="font-size: 18px;">
						Objednávky
						<span class="disabled-text">(<?echo mysqli_num_rows($ordersquery); ?>)</span>
					</a>
				</li>
			</ul>

			<form method="get" action="../admin/search" class="search-bar" enctype="application/x-www-form-urlencoded" style="margin-bottom: 20px;">

				<div class="input-group">
					<input type="text" class="form-control input-lg" name="query" placeholder="Zadejte hledaný výraz..." value="<?= $search ?>">

					<div class="input-group-btn">
						<button type="submit" class="btn btn-lg btn-primary btn-icon">
							Hledat
							<i class="entypo-search"></i>
						</button>
					</div>
				</div>

			</form>


			<!-- Search search form -->			<div class="search-results-panes">

				<div class="search-results-pane active" id="demands">



					<div class="row">
						<div class="col-md-8 col-sm-7">
							<h4><strong>Poptávky</strong> odpovídající výrazu <strong><i>"<?= $search ?>"</i></strong></h4>
						</div>
					</div>


						<?php

$parts = explode(" ", $search);
$last = array_pop($parts);
$first = implode(" ", $parts);

if ($first == "") {
    $first = 0;
}
if ($last == "") {
    $last = 0;
}

if (mysqli_num_rows($demandsquery) > 0) {
    while ($clients = mysqli_fetch_assoc($demandsquery)) {
        $type = '';
        demands($clients, $type);

    }} else {?>
					<ul class="cbp_tmtimeline" style=" margin-left: 25px;">
					  <li style="margin-top: 50px;">

							<div class="cbp_tmicon">
								<i class="entypo-block" style="line-height: 42px !important;"></i>
							</div>

							<div class="cbp_tmlabel empty" style="padding-top: 9px; margin-bottom: 50px;">
								<span><a style=" margin-left: -12px;font-size: 17px;">Bohužel tomuto filtru neodpovídá žádná poptávka.</a></span>
							</div>
						</li>
					  </ul>
					<?php
}?>


				</div>

				<div class="search-results-pane" id="orders">


					<div class="row">
						<div class="col-md-8 col-sm-7">
							<h4><strong>Objednávky</strong> odpovídající výrazu <strong><i>"<?= $search ?>"</i></strong></h4>
						</div>
					</div>


					<?php

if (mysqli_num_rows($ordersquery) > 0) {?>



					<table class="table table-bordered table-striped datatable dataTable">
						<thead> <tr> <th width="200px">Objednávka</th> <th width="120px" class="text-center">Stav</th> <th width="120px" class="text-center">Obchod</th>  <th style=" width: 300px;">Zakoupeno</th> <th>Doručení</th> <th>Datum</th> <th>Cena celkem</th> <th width="220px">Akce</th></tr> </thead>

						<tbody role="alert" aria-live="polite" aria-relevant="all">


						<?php
    while ($orders = mysqli_fetch_array($ordersquery)) {?>

					    <?php ordersnew($orders, $client['secretstring'], 1);
        ?>
					<?php
    }?>


						 </tbody>

					  </table>

					<?php } else {?>
					<ul class="cbp_tmtimeline" style=" margin-left: 25px;">
					  <li style="margin-top: 50px;">

							<div class="cbp_tmicon">
								<i class="entypo-block" style="line-height: 42px !important;"></i>
							</div>

							<div class="cbp_tmlabel empty" style="padding-top: 9px; margin-bottom: 50px;">
								<span><a style=" margin-left: -12px;font-size: 17px;">Bohužel tomuto filtru neodpovídá žádná objednávka.</a></span>
							</div>
						</li>
					</ul>
					<?php } ?>


				</div>

			</div>

		</div>
	</div>

</section>







	<div class="row">


		</div>




<footer class="main">


	&copy; <?php echo date("Y"); ?> <span style=" float:right;"><?php
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$total_time = round(($finish - $start), 4);

echo 'PHP '.PHP_VERSION.' | Page generated in ' . $total_time . ' seconds.';?></span>

</footer>	</div>



	</div>

<?php include VIEW . '/default/footer.php'; ?>
</body>
</html>