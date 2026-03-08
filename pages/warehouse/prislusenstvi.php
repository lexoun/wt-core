<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$pagetitle = 'Chybějící příslušenství';

include VIEW . '/default/header.php';

//ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

$data_query = $mysqli->query("SELECT m.id, m.manufacturer 
        FROM products_manufacturers AS m, (
            SELECT p.manufacturer
            FROM products p, orders_products_bridge ob, orders o 
            WHERE o.id = ob.aggregate_id AND o.order_status < 3 AND ob.product_id = p.id AND ob.reserved <> ob.quantity AND ob.aggregate_type = 'order'
            GROUP BY p.id 
        UNION SELECT p.manufacturer 
            FROM products_manufacturers m, products p, services_products_bridge ob, services o 
            WHERE o.id = ob.aggregate_id AND ob.product_id = p.id AND ob.reserved <> ob.quantity AND o.status < 3 
            GROUP BY p.id) AS p 
        WHERE p.manufacturer = m.id") or die($mysqli->error);
while ($data = mysqli_fetch_assoc($data_query)) {

    ?>
<div class="panel-body">
	<div class="invoice">

		<div class="row">

		<h3 style="margin-bottom: 16px;"><?= $data['manufacturer'] ?>

			<a href="/admin/pages/accessories/nova-dodavka?action=batch&manufacturer=<?= $data['id'] ?>" class="btn btn-md btn-primary" style="float: right;">Dodávka chybějícího zboží</a>
		</h3>

		<table class="table table-bordered">
			<thead>
				<tr>
					<th>Položka</th>
					<th width="90px" class="text-center">Chybí ks</th>
					<th width="50%">Kde chybí?</th>
				</tr>
			</thead>
			<tbody>

	<?php

    $i = 0;
    $product_query = $mysqli->query("SELECT p.*, p.id as product_id, ob.sum_quantity, ob.sum_reserved 
        FROM products AS p, (
            SELECT ob.product_id, SUM(ob.quantity) as sum_quantity, SUM(ob.reserved) as sum_reserved 
            FROM orders_products_bridge ob, orders o WHERE o.id = ob.aggregate_id AND o.order_status < 3 AND ob.reserved <> ob.quantity AND ob.aggregate_type = 'order'
            GROUP BY ob.product_id 
        UNION SELECT ob.product_id, SUM(ob.quantity) as sum_quantity, SUM(ob.reserved) as sum_reserved 
            FROM services_products_bridge ob, services o WHERE o.id = ob.aggregate_id AND ob.reserved <> ob.quantity AND o.status < 3 
            GROUP BY ob.product_id) AS ob 
        WHERE p.id = ob.product_id AND p.manufacturer = '" . $data['id'] . "' AND p.type = 'simple'") or die($mysqli->error);
    while ($product = mysqli_fetch_assoc($product_query)) {

        $i++;

        ?>

		<tr>
				<td><?php get_product_list($product);?></td>
				<td class="text-center" style="vertical-align: middle;" width="100px"><?= $product['sum_quantity'] - $product['sum_reserved'] ?></td>
				<td >



							<?php

        $orderquery = $mysqli->query("SELECT r.reserved, r.quantity, o.id 
            FROM orders_products_bridge r, orders o 
            WHERE o.id = r.aggregate_id AND r.reserved <> r.quantity AND o.order_status < '3' AND r.product_id = '" . $product['product_id'] . "' AND r.aggregate_type = 'order'") or die($mysqli->error);

        while ($ordermissing = mysqli_fetch_array($orderquery)) {

            $subtotal = $ordermissing['quantity'] - $ordermissing['reserved'];

            ?>

									<p style="padding-left: 10px;"><a href="/admin/pages/orders/zobrazit-objednavku?id=<?= $ordermissing['id'] ?>" target="_blank">Objednávka #<?= $ordermissing['id'] ?></a> - chybějících <?= $subtotal ?> ks</p>

									<?php
        }
        ?>

					<div style="clear: both;"></div>


				</td>
		</tr>

		<?php

    }?>




	<?php

    $i = 0;
    $product_query = $mysqli->query("SELECT p.*, p.id as product_id, v.id as variation_id, ob.sum_quantity, ob.sum_reserved 
        FROM products AS p, products_variations AS v, (
            SELECT ob.product_id, ob.variation_id, SUM(ob.quantity) as sum_quantity, SUM(ob.reserved) as sum_reserved 
            FROM orders_products_bridge ob, orders o WHERE o.id = ob.aggregate_id AND o.order_status < 3 AND ob.reserved <> ob.quantity AND ob.aggregate_type = 'order'
            GROUP BY ob.variation_id, ob.product_id 
        UNION SELECT ob.product_id, ob.variation_id, SUM(ob.quantity) as sum_quantity, SUM(ob.reserved) as sum_reserved 
            FROM services_products_bridge ob, services o WHERE o.id = ob.aggregate_id AND ob.reserved <> ob.quantity AND o.status < 3 
            GROUP BY ob.variation_id, ob.product_id) AS ob 
        WHERE p.id = ob.product_id AND p.id = v.product_id AND v.id = ob.variation_id AND p.manufacturer = '" . $data['id'] . "' AND p.type = 'variable'") or die($mysqli->error);
    while ($product = mysqli_fetch_assoc($product_query)) {

        $i++;

        ?>

		<tr>
				<td><?php get_product_list($product);?></td>
				<td class="text-center" style="vertical-align: middle;" width="100px"><?= $product['sum_quantity'] - $product['sum_reserved'] ?></td>
				<td >



							<?php

        $orderquery = $mysqli->query("SELECT r.reserved, r.quantity, o.id 
            FROM orders_products_bridge r, orders o 
            WHERE o.id = r.aggregate_id AND o.order_status < '3' AND r.product_id = '" . $product['product_id'] . "' AND r.variation_id = '" . $product['variation_id'] . "' AND r.aggregate_type = 'order'") or die($mysqli->error);

        while ($ordermissing = mysqli_fetch_array($orderquery)) {

            $subtotal = $ordermissing['quantity'] - $ordermissing['reserved'];

            ?>

									<p style="padding-left: 10px;"><a href="/admin/pages/orders/zobrazit-objednavku?id=<?= $ordermissing['id'] ?>" target="_blank">Objednávka #<?= $ordermissing['id'] ?></a> - chybějících <?= $subtotal ?> ks</p>

									<?php
        }
        ?>

					<div style="clear: both;"></div>



				</td>
		</tr>

		<?php

    }?>

			</tbody>
		</table>

		</div>

	</div>

	</div>

	<?php

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

