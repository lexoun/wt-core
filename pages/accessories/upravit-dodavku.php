<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$id = $_REQUEST['id'];

$data_query = $mysqli->query('SELECT * FROM products_supply WHERE id="' . $id . '"') or die($mysqli->error);

if (mysqli_num_rows($data_query) > 0) {

    $data = mysqli_fetch_assoc($data_query);

    $pagetitle = 'Upravit dodávku';

    if (isset($_REQUEST['action']) && $_REQUEST['action'] == "edit") {

        $update = $mysqli->query("UPDATE products_supply SET date = '" . $_POST['date'] . "', container_id = '" . $_POST['container_id'] . "', admin_note = '" . $_POST['admin_note'] . "', manufacturer = '" . $_POST['manufacturer'] . "', location_id = '" . $_POST['location'] . "' WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

        if (isset($_POST['product_sku'])) {

            $post_products = $_POST['product_sku'];

        } else {

            $post_products = array();

        }

        $find_simple_product = $mysqli->query("SELECT b.product_id, b.variation_id, b.reserved, p.code  FROM products p, products_supply_bridge b WHERE p.id = b.product_id AND b.supply_id = '" . $_REQUEST['id'] . "' order by p.id desc") or die($mysqli->error);

        $find_variable_product = $mysqli->query("SELECT b.product_id, b.variation_id, b.reserved, v.sku FROM products_variations v, products_supply_bridge b WHERE v.id = b.variation_id AND b.supply_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

        $array1 = array();
        while ($row = mysqli_fetch_assoc($find_simple_product)) {
            $array1[] = $row['code'];
        }
        while ($row = mysqli_fetch_assoc($find_variable_product)) {
            $array1[] = $row['sku'];
        }

        $array2 = array_filter($post_products);

        $dups_old = array();
        foreach (array_count_values($array2) as $val => $c) {
            if ($c > 1) {$dups_old[] = $val;}
        }

        $dups_new = array();
        foreach (array_count_values($array1) as $val => $c) {
            if ($c > 1) {$dups_new[] = $val;}
        }

        $check_duplicants = array_diff((array)$dups_new, (array)$dups_old);

        $removed_products = array_diff((array)$array1, (array)$array2); // odebírané produkty

        $removed_products = array_merge((array)$removed_products, (array)$check_duplicants);

        foreach ($removed_products as $removed) {

            $find_simple = $mysqli->query("SELECT b.id, b.product_id, b.reserved FROM products p, products_supply_bridge b WHERE p.code = '$removed' AND b.variation_id = 0 AND p.id = b.product_id AND b.supply_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);
            if (mysqli_num_rows($find_simple) != 0) {

                $reserve = mysqli_fetch_assoc($find_simple);

                $mysqli->query("DELETE FROM products_supply_bridge WHERE id = '" . $reserve['id'] . "'");

                $orders_query = $mysqli->query("SELECT * FROM supply_types_bridge WHERE product_id = '" . $reserve['product_id'] . "' AND supply_id = '" . $id . "'") or die($mysqli->error);
                while ($order = mysqli_fetch_array($orders_query)) {

                    $quantity = $order['quantity'];

                    $mysqli->query("UPDATE orders_products_bridge SET delivered = delivered - $quantity WHERE aggregate_id = '" . $order['id'] . "' AND product_id = '" . $order['product_id'] . "'  AND aggregate_type = 'order'") or die($mysqli->error);

                }

                $mysqli->query("DELETE FROM supply_types_bridge WHERE product_id = '" . $reserve['product_id'] . "' AND supply_id = '" . $id . "'");

                $quantity = $reserve['reserved'];

            } else {

                $find_var = $mysqli->query("SELECT b.id, b.product_id, b.variation_id, b.reserved FROM products_variations v, products_supply_bridge b WHERE v.sku = '$removed' AND v.id = b.variation_id AND b.variation_id != 0 AND b.supply_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

                if (mysqli_num_rows($find_var) != 0) {
                    $reserve = mysqli_fetch_assoc($find_var);

                    $mysqli->query("DELETE FROM products_supply_bridge WHERE id = '" . $reserve['id'] . "'");

                    $orders_query = $mysqli->query("SELECT * FROM supply_types_bridge WHERE variation_id = '" . $reserve['variation_id'] . "' AND supply_id = '" . $id . "'") or die($mysqli->error);
                    while ($order = mysqli_fetch_array($orders_query)) {

                        $quantity = $order['quantity'];

                        $mysqli->query("UPDATE orders_products_bridge SET delivered = delivered - $quantity WHERE aggregate_id = '" . $order['id'] . "' AND variation_id = '" . $order['variation_id'] . "'  AND aggregate_type = 'order'") or die($mysqli->error);

                    }

                    $mysqli->query("DELETE FROM supply_types_bridge WHERE variation_id = '" . $reserve['variation_id'] . "' AND supply_id = '" . $id . "'");

                    $quantity = $reserve['reserved'];

                }

            }

        }

        $added_products = array_diff((array)$array2, (array)$array1); // přidávané produkty
        $stable_products = array_intersect((array)$array1, (array)$array2);

        if (isset($_POST['product_sku'])) {

            $post = array_filter($_POST['product_sku']);
            if (!empty($post)) {

                foreach ($post as $post_index => $posterino) {

                    if (in_array($posterino, $added_products)) {

                        if (!empty($_POST['product_quantity'][$post_index])) {

                            $quantity = $_POST['product_quantity'][$post_index];
                            $purchase_price = $_POST['product_price'][$post_index];

                            // SIMPLE PRODUCT
                            $searchquery = $mysqli->query("SELECT id FROM products WHERE code = '$posterino'") or die($mysqli->error);

                            if (mysqli_num_rows($searchquery) > 0) {

                                $search = mysqli_fetch_array($searchquery);
                                $search['vid'] = 0;

                                // VARIABLE PRODUCT
                            } else {

                                $searchquery = $mysqli->query("SELECT p.id as id, v.id as vid FROM products p, products_variations v WHERE v.product_id = p.id AND v.sku = '$posterino'") or die($mysqli->error);

                                if (mysqli_num_rows($searchquery) > 0) {

                                    $search = mysqli_fetch_array($searchquery);

                                }

                            }

                            $reserved = 0;
                            $remaining_quantity = $quantity;

                            // CHECK ORDERS FOR SUPPLY DEMAND

                            $order_query = $mysqli->query("SELECT ob.id, ob.reserved, ob.quantity, o.id as order_id 
                                FROM orders_products_bridge ob, orders o 
                                WHERE o.id = ob.aggregate_id AND o.order_status < 3 AND (ob.reserved + ob.delivered) <> ob.quantity AND ob.product_id = '" . $search['id'] . "' AND ob.variation_id = '" . $search['vid'] . "' AND ob.aggregate_type = 'order'") or die($mysqli->error);

                            while ($order = mysqli_fetch_array($order_query)) {

                                $difference = $order['quantity'] - $order['reserved'] - $order['delivered'];

                                if ($difference > $remaining_quantity || $difference == $remaining_quantity) {

                                    $update = $mysqli->query("UPDATE orders_products_bridge SET delivered = delivered + $remaining_quantity WHERE id = '" . $order['id'] . "'") or die("$mysqli->error");

                                    $added = $remaining_quantity;
                                    $remaining_quantity = 0;

                                } else {

                                    $update = $mysqli->query("UPDATE orders_products_bridge SET delivered = delivered + $rozdil WHERE id = '" . $order['id'] . "'") or die($mysqli->error);

                                    $added = $difference;
                                    $remaining_quantity = $remaining_quantity - $difference;

                                }

                                $reserved = $reserved + $added;

                                $mysqli->query("INSERT INTO supply_types_bridge (order_id, product_id, variation_id, supply_id, quantity, type) VALUES ('" . $order['id'] . "', '" . $search['id'] . "', '" . $search['vid'] . "', '" . $id . "', '" . $added . "', 'order')") or die($mysqli->error);

                            }

                            $insert = $mysqli->query("INSERT INTO products_supply_bridge (supply_id, product_id, variation_id, quantity, reserved, purchase_price) VALUES ('" . $id . "', '" . $search['id'] . "', '" . $search['vid'] . "', '" . $quantity . "', '" . $reserved . "', '" . $purchase_price . "')") or die($mysqli->error);

                        }

                    } elseif (in_array($posterino, $stable_products)) {

                        $quantity = $_POST['product_quantity'][$post_index];

                        $purchase_price = $_POST['product_price'][$post_index];

                        // SIMPLE PRODUCT
                        $searchquery = $mysqli->query("SELECT p.id, b.quantity, b.id as bridge_id, b.reserved FROM products p, products_supply_bridge b WHERE p.code = '$posterino' AND b.product_id = p.id AND b.supply_id = '" . $data['id'] . "'") or die($mysqli->error);

                        if (mysqli_num_rows($searchquery) > 0) {

                            $search = mysqli_fetch_array($searchquery);
                            $search['vid'] = 0;

                            // VARIABLE PRODUCT
                        } else {

                            $searchquery = $mysqli->query("SELECT p.id as id, v.id as vid, b.quantity, b.id as bridge_id, b.reserved FROM products p, products_variations v, products_supply_bridge b WHERE v.product_id = p.id AND v.sku = '$posterino' AND b.variation_id = v.id AND b.supply_id = '" . $data['id'] . "'") or die($mysqli->error);

                            if (mysqli_num_rows($searchquery) > 0) {

                                $search = mysqli_fetch_array($searchquery);

                            }

                        }

                        if (isset($pricerino) && $pricerino != "") {

                            $price_product = $pricerino;

                        } else {

                            $price_product = $search['price'];

                        }

                        if (isset($order['vat']) && $order['vat'] == 21 && $_POST['vat'] != 21) {

                            $price_product = $price_product / 121 * $final_vat;

                        } else {

                            $price_product = $price_product;

                        }

                        /* ROVNÁ SE, POUZE ZMĚNA CENY  */

                        if ($quantity == $search['quantity']) {

                            //echo 'množství stejné';

                            //$mysqli->query("UPDATE products_supply_bridge SET price = '$price_product' WHERE id = '".$get_simple['id']."'");

                            /* NOVÉ MNOŽSTVÍ JE MENŠÍ NEŽ PŮVODNÍ MNOŽSTVÍ +++ REZERVOVANÉ MNOŽSTVÍ JE VĚTŠÍ NEŽ NOVÉ MNOŽSTVÍ  */

                        } elseif ($quantity < $search['quantity'] && $quantity < $search['reserved']) {

                            $remaining = $search['reserved'] - $quantity;

                            $orders_query = $mysqli->query("SELECT *, o.id as order_id, b.id as sbridge_id FROM orders o, supply_types_bridge b WHERE b.type_id = o.id AND b.product_id = '" . $search['id'] . "' AND b.variation_id = '" . $search['vid'] . "' AND b.supply_id = '" . $id . "' and o.order_status < 3") or die($mysqli->error);

                            while ($order = mysqli_fetch_array($orders_query)) {

                                if ($order['type'] == 'order') {

                                    $bridge = 'orders_products_bridge';
                                    $id_identify = 'order_id';

                                } elseif ($order['type'] == 'service') {

                                    $bridge = 'services_products_bridge';
                                    $id_identify = 'aggregate_id';

                                }

                                if ($order['quantity'] <= $remaining) {

                                    $mysqli->query("DELETE FROM supply_types_bridge WHERE id = '" . $order['sbridge_id'] . "'") or die($mysqli->error);

                                    $mysqli->query("UPDATE $bridge SET delivered = 0 WHERE $id_identify = '" . $order['type_id'] . "'") or die($mysqli->error);

                                    $remaining = $remaining - $order['quantity'];

                                } else {

                                    $mysqli->query("UPDATE supply_types_bridge SET quantity = quantity - $remaining WHERE id = '" . $order['sbridge_id'] . "'") or die($mysqli->error);

                                    $mysqli->query("UPDATE $bridge SET delivered = delivered - $remaining WHERE $id_identify = '" . $order['type_id'] . "'") or die($mysqli->error);

                                    $remaining = 0;

                                }

                                if ($remaining = 0) {break;}

                            }

                            $mysqli->query("UPDATE products_supply_bridge SET quantity = '$quantity', reserved = '$quantity' WHERE id = '" . $search['bridge_id'] . "'") or die($mysqli->error);

                            /* NOVÉ VĚTŠÍ NEŽ PŮVODNÍ +++ REZERVOVANÉ MENŠÍ NEŽ NOVÉ  */

                        } elseif ($quantity > $search['quantity'] && $quantity > $search['reserved']) {

                            $free_quantity = $quantity - $search['quantity'];

                            $reserved = $search['reserved'];

                            $orders_query = $mysqli->query("SELECT o.id as order_id, b.id, b.reserved, b.quantity, b.delivered FROM orders_products_bridge b, orders o WHERE b.product_id = '" . $search['id'] . "' AND b.variation_id = '" . $search['vid'] . "' AND b.aggregate_id = o.id and o.order_status < 3 and b.reserved + b.delivered < b.quantity AND b.location_id = '" . $data['location_id'] . "' order by o.id asc") or die($mysqli->error);
                            while ($order = mysqli_fetch_array($orders_query)) {

                                $rozdil = $order['quantity'] - $order['reserved'] - $order['delivered'];

                                if ($rozdil > $free_quantity || $rozdil == $free_quantity) {

                                    $update = $mysqli->query("UPDATE orders_products_bridge SET delivered = delivered + $free_quantity WHERE id = '" . $order['id'] . "'") or die($mysqli->error);

                                    $add = $free_quantity;
                                    $free_quantity = 0;

                                } else {

                                    $update = $mysqli->query("UPDATE orders_products_bridge SET delivered = delivered + $rozdil WHERE id = '" . $order['id'] . "'") or die($mysqli->error);

                                    $add = $rozdil;
                                    $free_quantity = $free_quantity - $rozdil;

                                }

                                $reserved = $reserved + $add;

                                $lookup_orders = $mysqli->query("SELECT * FROM supply_types_bridge WHERE type_id = '" . $order['id'] . "' AND product_id = '" . $search['id'] . "' AND variation_id = '" . $search['vid'] . "' AND supply_id = '" . $data['id'] . "' AND type = 'order'") or die($mysqli->error);

                                if (mysqli_num_rows($lookup_orders) > 0) {

                                    $mysqli->query("UPDATE supply_types_bridge SET quantity = quantity + $add WHERE type_id = '" . $order['id'] . "' AND product_id = '" . $search['id'] . "' AND variation_id = '" . $search['vid'] . "' AND supply_id = '" . $data['id'] . "' AND type = 'order'") or die($mysqli->error);

                                } else {

                                    $mysqli->query("INSERT INTO supply_types_bridge (type_id, product_id, variation_id, supply_id, quantity, type) VALUES ('" . $order['id'] . "', '" . $search['id'] . "', '" . $search['vid'] . "', '" . $data['id'] . "', '" . $add . "', 'order')") or die($mysqli->error);

                                }

                            }

                            $mysqli->query("UPDATE products_supply_bridge SET quantity = '$quantity', reserved = '$reserved' WHERE product_id = '" . $search['id'] . "' AND variation_id = '" . $search['vid'] . "' AND supply_id = '" . $data['id'] . "' ") or die($mysqli->error);

                        } else {

                            $mysqli->query("UPDATE products_supply_bridge SET quantity = '" . $quantity . "' WHERE id = '" . $search['bridge_id'] . "'");

                        }

                    }

                }

            }

        }

        Header("Location:https://www.wellnesstrade.cz/admin/pages/accessories/zobrazit-dodavku?id=" . $id . "&success=edit");
        exit;

    }

    $cliquery = $mysqli->query('SELECT user_name FROM demands') or die($mysqli->error);

    $shops_query = $mysqli->query("SELECT * FROM shops") or die($mysqli->error);


    include VIEW . '/default/header.php';
    ?>


<style>

.has-warning .selectboxit-container .selectboxit { border-color: #ffd78a !important;}

.page-body .selectboxit-container .selectboxit-options { margin-top: 40px !important; width: 100% !important;}
.page-body .selectboxit-container .selectboxit { height: 40px;width: 100% !important;}
.page-body .selectboxit-container .selectboxit .selectboxit-text { line-height: 40px; }
.page-body .selectboxit-container .selectboxit .selectboxit-arrow-container { height: 40px;}
.page-body .selectboxit-container .selectboxit .selectboxit-arrow-container:after { line-height: 40px;}

.nicescroll-rails > div:hover {
  background: rgb(53, 174, 255) !important;
}

#custom-scroller { width: 500px; }
.col-2, .col-8, .col-3, .col-4, .col-6 {
  display: inline-block;
  padding: 5px 2%;
  vertical-align: top;
}

.item {
  margin-right: 10px;
}

.col-2 { width: 18%; }
.col-8 { width: 76%; }
.col-3 { width: 26%; }
.col-4 { width: 36%; }
.col-6 { width: 60%; }
.select2-drop img { width: 100%; margin: 2%; }

.bigdrop.select2-container .select2-results {max-height: 300px;}
.bigdrop .select2-results {max-height: 300px;}

</style>

<form role="form" id="order_form" method="post" class="form-horizontal form-groups-bordered validate" action="upravit-dodavku?id=<?= $id ?>&action=edit" enctype="multipart/form-data">

	<div class="row">

			<div class="col-md-6">

<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						<strong style="font-weight: 600;">Informace prodejce</strong>
					</div>

				</div>

		<div class="form-group"><br>
								<div class="col-sm-12"><div class="col-sm-12">
							<textarea name="admin_note" class="form-control autogrow" id="field-7"><?= $data['admin_note'] ?></textarea>
						</div>		</div>
						</div>

</div>


			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						<strong style="font-weight: 600;">Základní údaje</strong>
					</div>

				</div>



				<div class="panel-body">

					<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Datum doručení</label>
						<div class="col-sm-6">
							<div class="date">
              					<input type="text" class="form-control datepicker" name="date" data-format="yyyy-mm-dd" placeholder="Datum" value="<?= $data['date'] ?>">
          			  		</div>
						</div>
					</div>


					<div class="form-group">
						<?php $manufactures_query = $mysqli->query("SELECT * FROM products_manufacturers") or die($mysqli->error);?>

						<label for="manufacturer" class="col-sm-3 control-label">Výrobce</label>


						<div class="col-sm-5">
							<select name="manufacturer" class="form-control">


								<option value="">Vyberte výrobce</option>

								<?php while ($manufacturer = mysqli_fetch_array($manufactures_query)) { ?>
									<option value="<?= $manufacturer['id'] ?>" <?php if ($manufacturer['id'] == $data['manufacturer']) {echo 'selected';}?>><?= $manufacturer['manufacturer'] ?></option>
								<?php } ?>

							</select>
						</div>


					</div>


						</div>

				</div>

		</div>


			<div class="col-md-6">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						<strong style="font-weight: 600;">Položky</strong>
					</div>

				</div>

						<div class="panel-body">

							<?php shop_accessories('products_supply_bridge', 'supply_id', $data['id'], $data['location_id']);?>

						</div>
					</div>

					</div>


	</div>

	<center>
	<div class="form-group default-padding button-demo">
		<button type="submit" style="margin-bottom: 24px; margin-right: 26px; font-size: 20px; padding: 20px 50px 20px 140px; margin-top: 20px;" data-color="red" data-style="zoom-in" class="ladda-button btn btn-primary btn-icon icon-left btn-lg"><i class="entypo-pencil" style="line-height: 48px;font-size: 40px; padding: 10px 20px;"></i> <span class="ladda-label">Upravit dodávku</span></button>
	</div></center>

</form>

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

	<script>

        $(document).ready(function(){

            $("#order_form").on("submit", function(){
              var form = $( "#order_form" );
                         var l = Ladda.create( document.querySelector( '#order_form .button-demo button' ) );
                if(form.valid()){

                  l.start();
                }
               });


         });


    </script>

<?php include VIEW . '/default/footer.php'; ?>


<?php

} else {

    include INCLUDES . "/404.php";

}?>
