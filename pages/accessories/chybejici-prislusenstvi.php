<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";


// old to delete
//$products_query = $mysqli->query("SELECT p.id, m.manufacturer FROM products p, products_manufacturers m WHERE p.manufacturer != 0 AND m.id = p.manufacturer AND m.type = 'manufacturer' ORDER BY m.manufacturer")or die($mysqli->error);
//
//
//while($product = mysqli_fetch_assoc($products_query)){


//    $find_supplier =
//    echo $product['manufacturer'].'<br>';
////
//    if($product['manufacturer'] == 'Spa Plus'){
//
//        $mysqli->query("INSERT IGNORE INTO products_suppliers (product_id, supplier) VALUES ('".$product['id']."', 30)")or die($mysqli->error);
//
//    }
//    if($product['manufacturer'] == 'FS'){
//
//        $mysqli->query("INSERT IGNORE INTO products_suppliers (product_id, supplier) VALUES ('".$product['id']."', 42)")or die($mysqli->error);
//
//    }
//    if($product['manufacturer'] == 'Sentiotec'){
//
//        $mysqli->query("INSERT IGNORE INTO products_suppliers (product_id, supplier) VALUES ('".$product['id']."', 35)")or die($mysqli->error);
//
//    }
//    if($product['manufacturer'] == 'Saunainter'){
//
//        $mysqli->query("INSERT IGNORE INTO products_suppliers (product_id, supplier) VALUES ('".$product['id']."', 40)")or die($mysqli->error);
//
//    }
//    if($product['manufacturer'] == 'Techneco'){
//
//        $mysqli->query("INSERT IGNORE INTO products_suppliers (product_id, supplier) VALUES ('".$product['id']."', 31)")or die($mysqli->error);
//
//    }


//    if($product['manufacturer'] == 'Deveraux'){
//
//        $mysqli->query("INSERT IGNORE INTO products_suppliers (product_id, supplier) VALUES ('".$product['id']."', 37)")or die($mysqli->error);
//
//    }
//
//    if($product['manufacturer'] == 'Eliga'){
//
//        $mysqli->query("INSERT IGNORE INTO products_suppliers (product_id, supplier) VALUES ('".$product['id']."', 36)")or die($mysqli->error);
//        $mysqli->query("INSERT IGNORE INTO products_suppliers (product_id, supplier) VALUES ('".$product['id']."', 40)")or die($mysqli->error);
//
//
//    }
//
//    if($product['manufacturer'] == 'Ensto'){
//
//        $mysqli->query("INSERT IGNORE INTO products_suppliers (product_id, supplier) VALUES ('".$product['id']."', 41)")or die($mysqli->error);
//
//    }
//
//
//    if($product['manufacturer'] == 'IQue'){
//
//        $mysqli->query("INSERT IGNORE INTO products_suppliers (product_id, supplier) VALUES ('".$product['id']."', 29)")or die($mysqli->error);
//
//    }
//
//    if($product['manufacturer'] == 'Harvia'){
//
//        $mysqli->query("INSERT IGNORE INTO products_suppliers (product_id, supplier) VALUES ('".$product['id']."', 35)")or die($mysqli->error);
//        $mysqli->query("INSERT IGNORE INTO products_suppliers (product_id, supplier) VALUES ('".$product['id']."', 40)")or die($mysqli->error);
//
//    }
//
//    if($product['manufacturer'] == 'EOS'){
//
//        $mysqli->query("INSERT IGNORE INTO products_suppliers (product_id, supplier) VALUES ('".$product['id']."', 34)")or die($mysqli->error);
//        $mysqli->query("INSERT IGNORE INTO products_suppliers (product_id, supplier) VALUES ('".$product['id']."', 40)")or die($mysqli->error);
//
//
//    }

//}
// old to delete


$pagetitle = 'Chybějící příslušenství';

include VIEW . '/default/header.php';


?>




<?php

//ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

$data_query = $mysqli->query("SELECT m.id, m.manufacturer FROM products_manufacturers AS m, 

(SELECT s.supplier FROM products p, orders_products_bridge ob, orders o , products_suppliers s WHERE s.product_id = p.id AND o.id = ob.aggregate_id AND o.order_status < 3 AND ob.product_id = p.id AND ob.reserved <> ob.quantity AND ob.aggregate_type = 'order' GROUP BY p.id 

UNION 

SELECT s.supplier FROM products p, services_products_bridge ob, services o, products_suppliers s WHERE s.product_id = p.id AND o.id = ob.aggregate_id AND ob.product_id = p.id AND ob.reserved <> ob.quantity AND o.state != 'finished' AND o.state != 'canceled' GROUP BY p.id

UNION 

SELECT s.supplier FROM products p, products_stocks stock, products_suppliers s WHERE s.product_id = p.id AND stock.instock < stock.min_stock AND stock.product_id = p.id GROUP BY p.id

) AS p 

WHERE p.supplier = m.id AND m.type = 'supplier'") or die($mysqli->error);
while ($data = mysqli_fetch_assoc($data_query)) {

    ?>
<div class="panel-body">
	<div class="invoice">

		<div class="row">

		<h3 style="margin-bottom: 16px;"><?= $data['manufacturer'] ?>

			<a href="/admin/pages/accessories/nova-dodavka?action=batch&supplier=<?= $data['id'] ?>" class="btn btn-md btn-primary" style="float: right;">Dodávka chybějícího zboží</a>
		</h3>

		<table class="table table-bordered">
			<thead>
            <tr>
                <th rowspan="2" style="vertical-align: middle;text-align: center;">Položka</th>

                <?php

                $location_query = $mysqli->query("SELECT * FROM shops_locations ORDER BY type");
                while ($location = mysqli_fetch_array($location_query)) { ?>


                    <th class="text-center"><span class="btn btn-sm btn-default"><strong><?= $location['name'] ?></strong></span></th>



                <?php } ?>
                <th class="text-center"><span class="btn btn-sm btn-primary"><strong>Celkem</strong></span></th>

            </tr>
				<tr>
					<?php

    mysqli_data_seek($location_query, 0);
    while ($location = mysqli_fetch_array($location_query)) { ?>


        <th class="text-center"><span class="btn btn-sm btn-default" style="width: 50px;">Sklad</span> <span class="btn btn-sm btn-default" style="width: 50px;">Min.</span>
            <span class="btn btn-sm btn-default" style="width: 50px;">Chybí</span></th>

					<?php }

    ?>

                    <th class="text-center"><span class="btn btn-sm btn-default" style="width: 50px;"><strong>Sklad</strong></span>
                        <span class="btn btn-sm btn-default" style="width: 50px;"><strong>Chybí</strong></span></th>

<!--					<th>Kde chybí?</th>-->
				</tr>
			</thead>
			<tbody>

	<?php
        // todo first PHP then echo

    $product_query = $mysqli->query("SELECT p.*, p.id as product_id, ob.sum_quantity, ob.sum_reserved FROM products AS p, 
    (SELECT ob.product_id, SUM(ob.quantity) as sum_quantity, SUM(ob.reserved) as sum_reserved FROM orders_products_bridge ob, orders o WHERE o.id = ob.aggregate_id AND o.order_status < 3 AND ob.reserved <> ob.quantity AND aggregate_type = 'order' GROUP BY ob.product_id 
    
    UNION 
    
    SELECT ob.product_id, SUM(ob.quantity) as sum_quantity, SUM(ob.reserved) as sum_reserved FROM services_products_bridge ob, services o WHERE o.id = ob.aggregate_id AND ob.reserved <> ob.quantity AND o.state != 'finished' AND o.state != 'canceled' GROUP BY ob.product_id
    
    UNION 
    
    SELECT s.product_id, s.instock as sum_quantity, s.instock as sum_reserved FROM products p, products_stocks s WHERE s.instock < s.min_stock AND s.product_id = p.id GROUP BY p.id
    
    ) AS ob, products_suppliers AS s
    
    WHERE p.id = ob.product_id AND s.supplier = '" . $data['id'] . "' AND s.product_id = p.id AND p.type = 'simple' GROUP BY p.id") or die($mysqli->error);
    while ($product = mysqli_fetch_assoc($product_query)) {

        $total['instock'] = 0;
        $total['missing'] = 0;

        ?>

		<tr>
				<td><?php get_product_list($product);?></td>

				<?php



        $location_query = $mysqli->query("SELECT s.instock, s.min_stock, l.id FROM shops_locations l, products_stocks s WHERE s.product_id = '" . $product['product_id'] . "' AND s.location_id = l.id") or die($mysqli->error);
        while ($location = mysqli_fetch_array($location_query)) {

                $calculate_missing = 0;


                $missing_query = $mysqli->query("SELECT p.id as product_id, ob.sum_quantity, ob.sum_reserved, ob.location_id FROM products AS p, 
    (SELECT ob.product_id, SUM(ob.quantity) as sum_quantity, SUM(ob.reserved) as sum_reserved, ob.location_id FROM orders_products_bridge ob, orders o WHERE o.id = ob.aggregate_id AND o.order_status < 3 AND ob.reserved <> ob.quantity AND ob.location_id = '".$location['id']."' AND ob.aggregate_type = 'order' GROUP BY ob.product_id 
    
    UNION 
    
    SELECT ob.product_id, SUM(ob.quantity) as sum_quantity, SUM(ob.reserved) as sum_reserved, ob.location_id FROM services_products_bridge ob, services o WHERE o.id = ob.aggregate_id AND ob.reserved <> ob.quantity AND o.state != 'finished' AND o.state != 'canceled' AND ob.location_id = '".$location['id']."' GROUP BY ob.product_id
    ) AS ob, products_suppliers AS s
    
    WHERE p.id = ob.product_id AND s.supplier = '" . $data['id'] . "' AND p.id = '".$product['id']."' AND s.product_id = p.id AND p.type = 'simple' GROUP BY p.id") or die($mysqli->error);


            while($total_missing = mysqli_fetch_assoc($missing_query)){

                $calculate_missing += $total_missing['sum_quantity'] - $total_missing['sum_reserved'];

            }


            if ($location['min_stock'] > 0) {

                $calculate_missing += $location['min_stock'] - $location['instock'];

            }

            ?>

            <td class="text-center" style="vertical-align: middle;">
                <span class="btn btn-sm btn-default" style="color: green; width: 50px;"><strong><?php echo $location['instock'];

                $total['instock'] += $location['instock'];

                ?></strong></span>
                <span class="btn btn-sm btn-default" style="color: teal; width: 50px;"><?php strong_echo($location['min_stock']); ?></span>

                <span class="btn btn-sm btn-default" style="color: red; width: 50px;"><?php strong_echo($calculate_missing); ?></span>

            </td>
					<?php

            $total['missing'] += $calculate_missing;
        }

        ?>


            <td class="text-center" style="vertical-align: middle;">
                <span class="btn btn-sm btn-success" style="width: 50px;"><?= $total['instock'] ?></span>
                <span class="btn btn-sm btn-danger" style="width: 50px;"><?php strong_echo($total['missing']); ?></span>
            </td>


<!--				<td>-->
<!---->
<!---->
<!---->
<!--							--><?//
//
//        $orderquery = $mysqli->query("SELECT r.reserved, r.quantity, o.id FROM orders_products_bridge r, orders o WHERE o.id = r.order_id AND r.reserved <> r.quantity AND o.order_status < '3' AND r.product_id = '" . $product['product_id'] . "'") or die($mysqli->error);
//
//        while ($ordermissing = mysqli_fetch_array($orderquery)) {
//
//            $subtotal = $ordermissing['quantity'] - $ordermissing['reserved'];
//
//            ?>
<!---->
<!--									<p style="padding-left: 10px;"><a href="/admin/pages/orders/zobrazit-objednavku?id=--><?//echo $ordermissing['id']; ?><!--" target="_blank">Objednávka #--><?//echo $ordermissing['id']; ?><!--</a> - chybějících --><?//echo $subtotal; ?><!-- ks</p>-->
<!---->
<!--									--><?//
//        }
//        ?>
<!---->
<!--					<div style="clear: both;"></div>-->
<!---->
<!---->
<!--				</td>-->
		</tr>

		<?php

    }


//    $product_query = $mysqli->query('SELECT * FROM products p, products_stocks s WHERE s.instock < s.min_stock AND s.product_id = p.id and p.type = "simple"') or die($mysqli->error);
//    while ($product = mysqli_fetch_assoc($product_query)) {
//
//        $i++;
//
//        ?>
<!---->
<!--        <tr>-->
<!--            <td>--><?//get_product_list($product);?><!--</td>-->
<!--            <td class="text-center" style="vertical-align: middle;" width="100px">chybí min.</td>-->
<!---->
<!---->
<!--            --><?//
//
//
//            $location_query = $mysqli->query("SELECT s.instock, s.min_stock FROM shops_locations l, products_stocks s WHERE s.product_id = '" . $product['product_id'] . "' AND s.location_id = l.id") or die($mysqli->error);
//            while ($location = mysqli_fetch_array($location_query)) { ?>
<!---->
<!--                <td class="text-center" style="vertical-align: middle;">--><?//echo $location['instock']; ?><!-- - min. --><?// echo $location['min_stock']; ?><!--</td>-->
<!---->
<!--            --><?//}
//
//            ?>
<!---->
<!---->
<!--            <td>-->
<!---->
<!--                <div style="clear: both;"></div>-->
<!---->
<!---->
<!--            </td>-->
<!--        </tr>-->
<!---->
<!--        --><?//
//
//    }


    ?>




	<?php

    /*
     *
     *
     *   UNION

    SELECT s.product_id, s.variation_id, s.instock as sum_quantity, s.instock as sum_reserved FROM products p, products_stocks s WHERE s.instock < s.min_stock AND s.product_id = p.id GROUP BY s.variation_id

     */

    $i = 0;
    $product_query = $mysqli->query("SELECT p.*, p.id as product_id, ob.variation_id, ob.sum_quantity, ob.sum_reserved FROM products AS p,
    (SELECT ob.product_id, ob.variation_id, SUM(ob.quantity) as sum_quantity, SUM(ob.reserved) as sum_reserved FROM orders_products_bridge ob, orders o WHERE o.id = ob.aggregate_id AND o.order_status < 3 AND ob.reserved <> ob.quantity AND ob.aggregate_type = 'order' GROUP BY ob.variation_id 
    
    UNION 
    
    SELECT ob.product_id, ob.variation_id, SUM(ob.quantity) as sum_quantity, SUM(ob.reserved) as sum_reserved FROM services_products_bridge ob, services o WHERE o.id = ob.aggregate_id AND ob.reserved <> ob.quantity AND o.state != 'finished' AND o.state != 'canceled' GROUP BY ob.variation_id
    
        
    UNION 
    
    SELECT s.product_id, s.variation_id, s.instock as sum_quantity, s.instock as sum_reserved FROM products p, products_variations v, products_stocks s WHERE s.instock < s.min_stock AND s.product_id = p.id AND s.variation_id = v.id AND v.product_id = p.id GROUP BY v.id
    
    ) AS ob, products_suppliers AS s, products_variations AS v
    
    WHERE v.id = ob.variation_id AND v.product_id = p.id AND p.id = ob.product_id AND s.supplier = '" . $data['id'] . "' AND s.product_id = p.id AND p.type = 'variable' GROUP BY v.id") or die($mysqli->error);
    while ($product = mysqli_fetch_assoc($product_query)) {

        $total['instock'] = 0;
        $total['missing'] = 0;

        $i++;

        ?>

		<tr>
            <td><?php get_product_list($product);?></td>

            <?php



            $location_query = $mysqli->query("SELECT s.instock, s.min_stock, l.id FROM shops_locations l, products_stocks s WHERE s.product_id = '" . $product['product_id'] . "' AND s.variation_id = '" . $product['variation_id'] . "'AND s.location_id = l.id") or die($mysqli->error);
            while ($location = mysqli_fetch_array($location_query)) {

                $calculate_missing = 0;


                $missing_query = $mysqli->query("SELECT p.id as product_id, ob.variation_id, ob.sum_quantity, ob.sum_reserved, ob.location_id FROM products AS p, 
    (SELECT ob.product_id, ob.variation_id, SUM(ob.quantity) as sum_quantity, SUM(ob.reserved) as sum_reserved, ob.location_id FROM orders_products_bridge ob, orders o WHERE o.id = ob.aggregate_id AND o.order_status < 3 AND ob.reserved <> ob.quantity AND ob.location_id = '".$location['id']."' AND ob.aggregate_type = 'order' GROUP BY ob.variation_id 
    
    UNION 
    
    SELECT ob.product_id, ob.variation_id, SUM(ob.quantity) as sum_quantity, SUM(ob.reserved) as sum_reserved, ob.location_id FROM services_products_bridge ob, services o WHERE o.id = ob.aggregate_id AND ob.reserved <> ob.quantity AND o.state != 'finished' AND o.state != 'canceled' AND ob.location_id = '".$location['id']."' GROUP BY ob.variation_id
    ) AS ob, products_suppliers AS s, products_variations AS v
    
    WHERE v.id = ob.variation_id AND v.product_id = p.id AND p.id = ob.product_id AND s.supplier = '" . $data['id'] . "' AND p.id = '".$product['id']."' AND s.product_id = p.id AND p.type = 'variable' GROUP BY v.id") or die($mysqli->error);


                while($total_missing = mysqli_fetch_assoc($missing_query)){

                    $calculate_missing += $total_missing['sum_quantity'] - $total_missing['sum_reserved'];

                }


                if ($location['min_stock'] > 0) {

                    $calculate_missing += $location['min_stock'] - $location['instock'];

                }

                ?>

                <td class="text-center" style="vertical-align: middle;">
                <span class="btn btn-sm btn-default" style="color: green; width: 50px;"><strong><?php echo $location['instock'];

                        $total['instock'] += $location['instock'];

                        ?></strong></span>
                    <span class="btn btn-sm btn-default" style="color: teal; width: 50px;"><?php strong_echo($location['min_stock']); ?></span>

                    <span class="btn btn-sm btn-default" style="color: red; width: 50px;"><?php strong_echo($calculate_missing); ?></span>

                </td>
                <?php

                $total['missing'] += $calculate_missing;
            }

            ?>


            <td class="text-center" style="vertical-align: middle;">
                <span class="btn btn-sm btn-success" style="width: 50px;"><?= $total['instock'] ?></span>
                <span class="btn btn-sm btn-danger" style="width: 50px;"><?php strong_echo($total['missing']); ?></span>
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




