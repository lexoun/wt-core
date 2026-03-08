<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$categorytitle = "Příslušenství";
$pagetitle = "Nová dodávka";

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "add") {

    $insert = $mysqli->query("INSERT INTO products_supply (date, container_id, admin_note, supplier, location_id) VALUES ('" . $_POST['date'] . "', '" . $_POST['container_id'] . "', '" . $_POST['admin_note'] . "', '" . $_REQUEST['supplier'] . "', '" . $_POST['location'] . "')") or die($mysqli->error);

    $id = $mysqli->insert_id;

    if (isset($_POST['product_sku'])) {

        $post = array_filter($_POST['product_sku']);
        if (!empty($post)) {

            foreach ($post as $post_index => $posterino) {

                if (!empty($_POST['product_quantity'][$post_index])) {

                    $quantity = $_POST['product_quantity'][$post_index];
                    $purchase_price = $_POST['product_price'][$post_index];

                    // SIMPLE PRODUCT
                    $searchquery = $mysqli->query("SELECT id FROM products WHERE code = '$posterino'") or die($mysqli->error);

                    if (mysqli_num_rows($searchquery) > 0) {

                        $search = mysqli_fetch_array($searchquery);

                        $search['vid'] = '0';

                        // VARIABLE PRODUCT
                    } else {

                        $searchquery = $mysqli->query("SELECT p.id as id, v.id as vid FROM products p, products_variations v WHERE v.product_id = p.id AND v.sku = '$posterino'") or die($mysqli->error);

                        if (mysqli_num_rows($searchquery) > 0) {

                            $search = mysqli_fetch_array($searchquery);

                        }

                    }

                    $insert = $mysqli->query("INSERT INTO products_supply_bridge (supply_id, product_id, variation_id, quantity, purchase_price) VALUES ('" . $id . "', '" . $search['id'] . "', '" . $search['vid'] . "', '" . $quantity . "', '" . $purchase_price . "')") or die($mysqli->error);

                    $bridge_id = $mysqli->insert_id;

                    // check if is required somewhere

                    $reserved = 0;

                    // ORDER START --- ORDER START --- ORDER START --- ORDER START --- ORDER START

                    $orders_query = $mysqli->query("SELECT b.id, b.aggregate_id, b.reserved, b.delivered, b.quantity 
                        FROM orders_products_bridge b, orders o 
                        WHERE b.product_id = '" . $search['id'] . "' and b.variation_id = '" . $search['vid'] . "' and b.aggregate_id = o.id and o.order_status < 3 and (b.reserved + b.delivered) < b.quantity AND b.aggregate_type = 'order' 
                        order by o.id asc") or die($mysqli->error);
                    while ($order = mysqli_fetch_array($orders_query)) {

                        $rozdil = $order['quantity'] - ($order['reserved'] + $order['delivered']);

                        if ($rozdil > $quantity || $rozdil == $quantity) {

                            $add = $quantity;
                            $quantity = 0;

                        } else {

                            $add = $rozdil;
                            $quantity = $quantity - $rozdil;

                        }

                        $mysqli->query("UPDATE orders_products_bridge SET delivered = delivered + $add WHERE id = '" . $order['id'] . "'") or die($mysqli->error);

                        $lookup_orders = $mysqli->query("SELECT * FROM supply_types_bridge WHERE type_id = '" . $order['id'] . "' AND product_id = '" . $search['id'] . "' AND variation_id = '" . $search['vid'] . "' AND supply_id = '" . $id . "' AND type = 'order'") or die($mysqli->error);

                        if (mysqli_num_rows($lookup_orders) > 0) {

                            $mysqli->query("UPDATE supply_types_bridge SET quantity = quantity + $add WHERE type_id = '" . $order['id'] . "' AND product_id = '" . $search['id'] . "' AND variation_id = '" . $search['vid'] . "' AND supply_id = '" . $id . "' AND type = 'order'") or die($mysqli->error);

                        } else {

                            $mysqli->query("INSERT INTO supply_types_bridge (type_id, product_id, variation_id, supply_id, quantity, type) VALUES ('" . $order['id'] . "', '" . $search['id'] . "', '" . $search['vid'] . "', '" . $id . "', '" . $add . "', 'order')") or die($mysqli->error);

                        }

                        $reserved = $reserved + $add;

                        if ($quantity == 0) {break;}

                    }

                    // ORDER END --- ORDER END --- ORDER END --- ORDER END --- ORDER END

                    // SERVICE START --- SERVICE START --- SERVICE START --- SERVICE START --- SERVICE START

                    $services_query = $mysqli->query("SELECT b.id, b.service_id, b.reserved, b.delivered, b.quantity FROM services_products_bridge b, services o WHERE b.product_id = '" . $search['id'] . "' and b.variation_id = '" . $search['vid'] . "' and b.service_id = o.id and o.state != 'finished' AND o.state != 'canceled' and (b.reserved + b.delivered) < b.quantity order by o.id asc") or die($mysqli->error);
                    while ($service = mysqli_fetch_array($services_query)) {

                        $rozdil = $service['quantity'] - ($service['reserved'] + $service['delivered']);

                        if ($rozdil > $quantity || $rozdil == $quantity) {

                            $add = $quantity;
                            $quantity = 0;

                        } else {

                            $add = $rozdil;
                            $quantity = $quantity - $rozdil;

                        }

                        $mysqli->query("UPDATE services_products_bridge SET delivered = delivered + $add WHERE id = '" . $service['id'] . "'") or die($mysqli->error);

                        $lookup_services = $mysqli->query("SELECT * FROM supply_types_bridge WHERE type_id = '" . $service['service_id'] . "' AND product_id = '" . $search['id'] . "' AND variation_id = '" . $search['vid'] . "' AND supply_id = '" . $id . "' AND type = 'service'") or die($mysqli->error);

                        if (mysqli_num_rows($lookup_services) > 0) {

                            $mysqli->query("UPDATE supply_types_bridge SET quantity = quantity + $add WHERE type_id = '" . $service['service_id'] . "' AND product_id = '" . $search['id'] . "' AND variation_id = '" . $search['vid'] . "' AND supply_id = '" . $id . "' AND type = 'service'") or die($mysqli->error);

                        } else {

                            $mysqli->query("INSERT INTO supply_types_bridge (type_id, product_id, variation_id, supply_id, quantity, type) VALUES ('" . $service['service_id'] . "', '" . $search['id'] . "', '" . $search['vid'] . "', '" . $id . "', '" . $add . "', 'service')") or die($mysqli->error);

                        }

                        $reserved = $reserved + $add;

                        if ($quantity == 0) {break;}

                    }

                    // SERVICE END --- SERVICE END --- SERVICE END --- SERVICE END --- SERVICE END

                    $mysqli->query("UPDATE products_supply_bridge SET reserved = '" . $reserved . "' WHERE id = '" . $bridge_id . "'") or die($mysqli->error);

                }

            }

        }

    }

    Header("Location:https://www.wellnesstrade.cz/admin/pages/accessories/zobrazit-dodavku?id=" . $id . "&success=edit&missing=" . $total_missing . "&reserved=" . $total_reserved);
    exit;

}

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



<?php

if (!isset($_REQUEST['supplier']) || $_REQUEST['supplier'] == '') {

    ?>

	<div class="row">

		<div class="col-md-12">
<div class="panel panel-primary" data-collapsed="0">

<div class="panel-heading">
					<div class="panel-title">
						<strong style="font-weight: 600;">Zvolte dodavatele</strong>
					</div>

				</div>

				<div class="panel-body">

	<?php

    $accessories_suppliers = $mysqli->query("SELECT * FROM products_manufacturers WHERE type = 'supplier' ORDER BY manufacturer")or die($mysqli->error);
                while($supplier = mysqli_fetch_assoc($accessories_suppliers)) {


        ?>

	<a href="nova-dodavka?supplier=<?= $supplier['id'] ?>" class="btn btn-md btn-default" style="margin: 5px 3px; width: 219px; padding: 12px; font-weight: bold;"><?= $supplier['manufacturer'] ?></a>

<?php

    }?>
</div>
</div>
</div>
</div>
<?php

} else {

    ?>


<form id="supply_form" role="form" method="post" class="form-horizontal form-groups-bordered validate" action="nova-dodavka?action=add&supplier=<?= $_REQUEST['supplier'] ?>" enctype="multipart/form-data">

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
							<textarea name="admin_note" class="form-control autogrow" id="field-7"></textarea>
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

						<label for="supplier" class="col-sm-3 control-label">Dodavatel</label>


						<div class="col-sm-5">
							<select name="supplier" class="form-control" disabled>


								<option value="">Vyberte dodavatele</option>

                                <?php
                                $supplier_query = $mysqli->query("SELECT * FROM products_manufacturers WHERE type = 'supplier'") or die($mysqli->error);

                                while($supplier = mysqli_fetch_assoc($supplier_query)) {

                                    ?>
                                    <option value="<?= $supplier['id'] ?>"<?php if (!empty($_REQUEST['supplier']) && $_REQUEST['supplier'] == $supplier['id']) {echo 'selected';}?>><?= $supplier['manufacturer'] ?></option>
                                <?php } ?>


							</select>
						</div>
					</div>

					<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Datum doručení</label>
						<div class="col-sm-6">
							<div class="date">
              					<input type="text" class="form-control datepicker" name="date" data-format="yyyy-mm-dd" placeholder="Datum" value="">
          			  		</div>
						</div>
					</div>


						</div>

				</div>



		</div>


			<div class="col-md-6">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						<strong style="font-weight: 600;">Položky momentálně vybraného výrobce</strong>
					</div>

				</div>

						<div class="panel-body">

							<?php

    if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'batch') {

        ?>



<script type="text/javascript">
jQuery(document).ready(function($)
{



$('#selectbox-o').select2({
    minimumInputLength: 2,
    ajax: {
      url: "/admin/data/autosuggest-products",
      dataType: 'json',
      data: function (term, page) {
        return {
          q: term,
            supplier: <?= $_REQUEST['supplier'] ?>
        };
      },
      results: function (data, page) {
        return { results: data };
      }
    },

    formatResult: format,
    formatSelection: format,
    escapeMarkup: function(m) { return m; }

});

  function format(data) {
    if (!data.id) return data.text; // optgroup

        return "<img src='https://www.wellnesstrade.cz/stores/data/images/mini/" + data.seourl + ".jpg' height='20'/>" + data.text;

  }





$('#selectbox-o').on("change", function(e) {



	var data = $('#selectbox-o').select2('data');


//$("#empty-holder").load("/admin/controllers/modals/products?sku="+vlue);


	$('#specification_copy').clone(true).insertBefore("#duplicate_specification").attr('id', 'copied').addClass('has-success').show();

	$('#copied #copy_this_first').attr('name', 'product_name[]').attr('value', data.pure_text);

	$('#copied #copy_this_third').attr('name', 'product_sku[]').attr('value', data.id);

	$('#copied #copy_this_second').attr('name', 'product_quantity[]').attr('value', '1');

	$('#copied').attr('id', 'copifinish');

	$("#selectbox-o").select2("val", "");

	setTimeout(function(){
      $('#copifinish').attr('id', 'hasfinish').removeClass('has-success');}, 2000);


});


$('.remove_specification').click(function() {
   $(this).closest('.specification').remove();
   event.preventDefault();
});

});
</script>


		<!-- Product Name Select Box -->
		<div class="form-group">
		   <div class="col-sm-12">
		     <input id="selectbox-o" class="input-xlarge" name="optionvalue" type="hidden" data-placeholder="Vyberte produkt.." />
		   </div>
		</div>

		<hr>

		<div class="form-group">

	<div class="col-sm-12" style="float:left;">


	<div id="specification_copy" class="specification" style="display: none; float:left; width: 100%;">

		<div class="col-sm-6" style="margin-bottom: 8px; padding: 0;">

			<input type="text" class="form-control" id="copy_this_first" name="copythis" value="" placeholder="Název produktu">

			<input type="text" class="form-control" id="copy_this_third" name="copythis" value="" placeholder="SKU produktu" style="display: none;">

		</div>

		<div class="col-sm-1" style="padding: 0 0px 0 8px;">

			<input type="text" class="form-control text-center" id="copy_this_second" name="copythis" value="" placeholder="Počet">

		</div>

		<div class="col-sm-2" style="padding: 0 0px 0 8px;">

			<input type="text" class="form-control text-center" id="copy_this_price" name="copythis" value="" placeholder="Aktuální cena">

		</div>

    <div class="col-sm-2" style="padding: 0 0px 0 8px;">

      <input type="text" class="form-control text-center" id="copy_this_original_price" name="copythis" value="" placeholder="Původní cena">

    </div>


		<div class="col-sm-1" style="padding: 0 0px 0 11px;">
			<button type="button" class="remove_specification btn btn-red" style="float:left; padding: 6px 10px; cursor: pointer;"> <i class="entypo-trash"></i> </button>
		 </div>
	</div>


  <div id="empty-holder"></div>



	<?php

        $products_bridge = $mysqli->query("SELECT p.*, p.id as product_id, ob.sum_quantity, ob.sum_reserved FROM products AS p, 
    (SELECT ob.product_id, SUM(ob.quantity) as sum_quantity, SUM(ob.reserved) as sum_reserved FROM orders_products_bridge ob, orders o WHERE o.id = ob.aggregate_id AND o.order_status < 3 AND ob.reserved <> ob.quantity AND ob.aggregate_type = 'order' GROUP BY ob.product_id 
    
    UNION 
    
    SELECT ob.product_id, SUM(ob.quantity) as sum_quantity, SUM(ob.reserved) as sum_reserved FROM services_products_bridge ob, services o WHERE o.id = ob.aggregate_id AND ob.reserved <> ob.quantity AND o.state != 'finished' AND o.state != 'canceled' GROUP BY ob.product_id
    
    UNION 
    
    SELECT s.product_id, s.instock as sum_quantity, s.instock as sum_reserved FROM products p, products_stocks s WHERE s.instock < s.min_stock AND s.product_id = p.id GROUP BY p.id
    
    ) AS ob, products_suppliers AS s
    
    WHERE p.id = ob.product_id AND s.supplier = '" . $_REQUEST['supplier'] . "' AND s.product_id = p.id AND p.type = 'simple' GROUP BY p.id") or die($mysqli->error);

        while ($bridge = mysqli_fetch_array($products_bridge)) {



            $calculate_missing = 0;


            $location_query = $mysqli->query("SELECT s.instock, s.min_stock, l.id FROM shops_locations l, products_stocks s WHERE s.product_id = '" . $bridge['product_id'] . "' AND s.location_id = l.id") or die($mysqli->error);
            while ($location = mysqli_fetch_array($location_query)) {



                $missing_query = $mysqli->query("SELECT p.id as product_id, ob.sum_quantity, ob.sum_reserved, ob.location_id FROM products AS p, 
            (SELECT ob.product_id, SUM(ob.quantity) as sum_quantity, SUM(ob.reserved) as sum_reserved, ob.location_id FROM orders_products_bridge ob, orders o WHERE o.id = ob.aggregate_id AND o.order_status < 3 AND ob.reserved <> ob.quantity AND ob.location_id = '" . $location['id'] . "' AND ob.aggregate_type = 'order' GROUP BY ob.product_id 
            
            UNION 
            
            SELECT ob.product_id, SUM(ob.quantity) as sum_quantity, SUM(ob.reserved) as sum_reserved, ob.location_id FROM services_products_bridge ob, services o WHERE o.id = ob.aggregate_id AND ob.reserved <> ob.quantity AND o.state != 'finished' AND o.state != 'canceled' AND ob.location_id = '" . $location['id'] . "' GROUP BY ob.product_id
            ) AS ob, products_suppliers AS s
            
            WHERE p.id = ob.product_id AND s.supplier = '" . $_REQUEST['supplier'] . "' AND p.id = '" . $bridge['product_id'] . "' AND s.product_id = p.id AND p.type = 'simple' GROUP BY p.id") or die($mysqli->error);


                while ($total_missing = mysqli_fetch_assoc($missing_query)) {

                    $calculate_missing += $total_missing['sum_quantity'] - $total_missing['sum_reserved'];

                }


                if ($location['min_stock'] > 0) {

                    $calculate_missing += $location['min_stock'] - $location['instock'];

                }

            }


        $product_query = $mysqli->query("SELECT * FROM products p LEFT JOIN products_sites s ON s.product_id = s.id WHERE p.id = '" . $bridge['product_id'] . "'");

            $product = mysqli_fetch_array($product_query);

            $price = number_format($product['price'], 0, ',', ' ') . ' Kč';

            $product_title = $product['code'] . ' – ' . $product['productname'] . ' – ' . $price;

            $sku = $product['code'];

            ?>

		<div class="specification" style="float: left; width: 100%;">
			<div class="col-sm-7" style="margin-bottom: 8px; padding: 0;">

			<input type="text" class="form-control" id="specification_name" name="product_name[]" value="<?= $product_title ?>" placeholder="Název produktu">

			<input type="text" class="form-control" id="copy_this_third" name="product_sku[]" value="<?= $sku ?>" placeholder="SKU produktu" style="display: none;">

			</div>
			<div class="col-sm-1" style="padding: 0 0px 0 8px;">
				<input type="text" class="form-control text-center" id="specification_value" name="product_quantity[]" value="<?= $calculate_missing ?>" placeholder="Počet">
			</div>

			<div class="col-sm-2" style="padding: 0 0px 0 8px;">
				<input type="text" class="form-control text-center" id="specification_value" name="product_price[]" value="<?= $bridge['purchase_price'] ?>" placeholder="Nákupní cena">
			</div>

			<div class="col-sm-1" style="padding: 0 0px 0 11px;">
			<button type="button" class="remove_specification btn btn-red" style="float:left; padding: 6px 10px; cursor: pointer;"> <i class="entypo-trash"></i> </button>
			 </div>
		</div>

		<?php

        }

        $products_bridge = $mysqli->query("SELECT p.*, p.id as product_id, ob.variation_id, ob.sum_quantity, ob.sum_reserved FROM products AS p,
    (SELECT ob.product_id, ob.variation_id, SUM(ob.quantity) as sum_quantity, SUM(ob.reserved) as sum_reserved FROM orders_products_bridge ob, orders o WHERE o.id = ob.aggregate_id AND o.order_status < 3 AND ob.reserved <> ob.quantity AND ob.aggregate_type = 'order' GROUP BY ob.variation_id 
    
    UNION 
    
    SELECT ob.product_id, ob.variation_id, SUM(ob.quantity) as sum_quantity, SUM(ob.reserved) as sum_reserved FROM services_products_bridge ob, services o WHERE o.id = ob.aggregate_id AND ob.reserved <> ob.quantity AND o.state != 'finished' AND o.state != 'canceled' GROUP BY ob.variation_id
    
        UNION 
    
    SELECT s.product_id, s.variation_id, s.instock as sum_quantity, s.instock as sum_reserved FROM products p, products_variations v, products_stocks s WHERE s.instock < s.min_stock AND s.product_id = p.id AND s.variation_id = v.id AND v.product_id = p.id GROUP BY v.id
    
    ) AS ob, products_suppliers AS s, products_variations AS v
    
    WHERE v.id = ob.variation_id AND v.product_id = p.id AND p.id = ob.product_id AND s.supplier = '" . $_REQUEST['supplier'] . "' AND s.product_id = p.id AND p.type = 'variable' GROUP BY v.id") or die($mysqli->error);

        while ($bridge = mysqli_fetch_array($products_bridge)) {


            $calculate_missing = 0;


            $location_query = $mysqli->query("SELECT s.instock, s.min_stock, l.id FROM shops_locations l, products_stocks s WHERE s.product_id = '" . $bridge['product_id'] . "' AND s.variation_id = '" . $bridge['variation_id'] . "'AND s.location_id = l.id") or die($mysqli->error);
            while ($location = mysqli_fetch_array($location_query)) {


                $missing_query = $mysqli->query("SELECT p.id as product_id, ob.variation_id, ob.sum_quantity, ob.sum_reserved, ob.location_id FROM products AS p, 
            (SELECT ob.product_id, ob.variation_id, SUM(ob.quantity) as sum_quantity, SUM(ob.reserved) as sum_reserved, ob.location_id FROM orders_products_bridge ob, orders o WHERE o.id = ob.aggregate_id AND o.order_status < 3 AND ob.reserved <> ob.quantity AND ob.location_id = '" . $location['id'] . "' AND ob.aggregate_type = 'order' GROUP BY ob.variation_id 
            
            UNION 
            
            SELECT ob.product_id, ob.variation_id, SUM(ob.quantity) as sum_quantity, SUM(ob.reserved) as sum_reserved, ob.location_id FROM services_products_bridge ob, services o WHERE o.id = ob.aggregate_id AND ob.reserved <> ob.quantity AND o.state != 'finished' AND o.state != 'canceled' AND ob.location_id = '" . $location['id'] . "' GROUP BY ob.variation_id
            ) AS ob, products_suppliers AS s, products_variations AS v
            
            WHERE v.id = ob.variation_id AND v.product_id = p.id AND p.id = ob.product_id AND s.supplier = '" . $_REQUEST['supplier'] . "' AND p.id = '" . $bridge['product_id'] . "' AND s.product_id = p.id AND p.type = 'variable' GROUP BY v.id") or die($mysqli->error);


                while ($total_missing = mysqli_fetch_assoc($missing_query)) {

                    $calculate_missing += $total_missing['sum_quantity'] - $total_missing['sum_reserved'];

                }


                if ($location['min_stock'] > 0) {

                    $calculate_missing += $location['min_stock'] - $location['instock'];

                }

            }


            $quantity = $bridge['sum_quantity'] - $bridge['sum_reserved'];

            if ($bridge['variation_id'] != 0) {

                $product_query = $mysqli->query("SELECT *, s.id as ajdee, s.price as price, s.purchase_price as purchase_price FROM products p, products_variations s WHERE p.id = '" . $bridge['product_id'] . "' AND p.id = s.product_id AND s.id = '" . $bridge['variation_id'] . "'");
                $product = mysqli_fetch_array($product_query);

                $select = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $product['ajdee'] . "'") or die($mysqli->error);
                $desc = "";
                while ($var = mysqli_fetch_array($select)) {

                    $desc = $desc . $var['name'] . ': ' . $var['value'] . ' ';

                }

                $price = number_format($product['price'], 0, ',', ' ') . ' Kč';

                $product_title = $product['sku'] . ' – ' . $product['productname'] . ' – ' . $desc . ' – ' . $price;

                $sku = $product['sku'];

            } else {

                $product_query = $mysqli->query("SELECT * FROM products p LEFT JOIN products_sites s ON s.product_id = s.id WHERE p.id = '" . $bridge['product_id'] . "'");

                $product = mysqli_fetch_array($product_query);

                $price = number_format($product['price'], 0, ',', ' ') . ' Kč';

                $product_title = $product['code'] . ' – ' . $product['productname'] . ' – ' . $price;

                $sku = $product['code'];

            }

            ?>

		<div class="specification" style="float: left; width: 100%;">
			<div class="col-sm-7" style="margin-bottom: 8px; padding: 0;">

			<input type="text" class="form-control" id="specification_name" name="product_name[]" value="<?= $product_title ?>" placeholder="Název produktu">

			<input type="text" class="form-control" id="copy_this_third" name="product_sku[]" value="<?= $sku ?>" placeholder="SKU produktu" style="display: none;">

			</div>
			<div class="col-sm-1" style="padding: 0 0px 0 8px;">
				<input type="text" class="form-control text-center" id="specification_value" name="product_quantity[]" value="<?= $calculate_missing ?>" placeholder="Počet">
			</div>

			<div class="col-sm-2" style="padding: 0 0px 0 8px;">
				<input type="text" class="form-control text-center" id="specification_value" name="product_price[]" value="<?= $bridge['purchase_price'] ?>" placeholder="Nákupní cena">
			</div>


			<div class="col-sm-1" style="padding: 0 0px 0 11px;">
			<button type="button" class="remove_specification btn btn-red" style="float:left; padding: 6px 10px; cursor: pointer;"> <i class="entypo-trash"></i> </button>
			 </div>
		</div>

		<?php

        }

        ?>



		<button type="button" id="duplicate_specification" style="display: none;" class="btn btn-default btn-icon icon-left">
      </button>
  </div>
  </div>


 <hr>

  	<div class="form-group">
	<label class="col-sm-3 control-label">Pobočka k vypořádání</label>

	<div class="col-sm-9" style="float:left;">


			<?php

        if (empty($location_id) || $location_id == '0') {$desired_location = 7;} else { $desired_location = $location_id;}

        $locations_query = $mysqli->query("SELECT * FROM shops_locations l LEFT JOIN products_stocks s ON s.location_id = l.id AND s.product_id = '" . $product['id'] . "' GROUP BY l.id ORDER BY type ASC");

        while ($location = mysqli_fetch_array($locations_query)) {

            ?>
				<div class="radio" style="width: 25%; float: left;">
					<label>
						<input type="radio" <?php if (empty($location_id)) { ?>name="location"<?php } ?> value="<?= $location['id'] ?>" <?php if (($location['eshop_default'] && empty($location_id)) || $location['id'] == $desired_location) {

                echo 'checked';}if (!empty($location_id)) {echo ' disabled';}?>><?= $location['name'] ?>
					</label>
				</div>
			<?php } ?>

			<?php if (!empty($location_id)) { ?><input type="text" name="location" value="<?= $desired_location ?>" style="display: none;"><?php } ?>

		</div>
	</div>



		<?php

    } else {

        ?>


<script type="text/javascript">
jQuery(document).ready(function($)
{



$('#selectbox-o').select2({
    minimumInputLength: 2,
    ajax: {
      url: "/admin/data/autosuggest-products",
      dataType: 'json',
      data: function (term, page) {
        return {
          q: term,
          supplier: <?= $_REQUEST['supplier'] ?>
        };
      },
      results: function (data, page) {
        return { results: data };
      }
    },

    formatResult: format,
    formatSelection: format,
    escapeMarkup: function(m) { return m; }

});

  function format(data) {
    if (!data.id) return data.text; // optgroup

        return "<img src='https://www.wellnesstrade.cz/data/stores/images/mini/" + data.seourl + ".jpg' height='20'/>" + data.text;

  }





$('#selectbox-o').on("change", function(e) {



	var data = $('#selectbox-o').select2('data');


//$("#empty-holder").load("/admin/controllers/modals/products?sku="+vlue);


	$('#specification_copy').clone(true).insertBefore("#duplicate_specification").attr('id', 'copied').addClass('has-success').show();

	$('#copied #copy_this_first').attr('name', 'product_name[]').attr('value', data.pure_text);

	$('#copied #copy_this_third').attr('name', 'product_sku[]').attr('value', data.id);

	$('#copied #copy_this_second').attr('name', 'product_quantity[]').attr('value', '1');

	$('#copied').attr('id', 'copifinish');

	$("#selectbox-o").select2("val", "");

	setTimeout(function(){
      $('#copifinish').attr('id', 'hasfinish').removeClass('has-success');}, 2000);


});


$('.remove_specification').click(function() {
   $(this).closest('.specification').remove();
   event.preventDefault();
});

});
</script>


		<!-- Product Name Select Box -->
		<div class="form-group">
		   <div class="col-sm-12">
		     <input id="selectbox-o" class="input-xlarge" name="optionvalue" type="hidden" data-placeholder="Vyberte produkt.." />
		   </div>
		</div>

		<hr>

		<div class="form-group">

	<div class="col-sm-12" style="float:left; padding: 0;">


	<div id="specification_copy" class="specification" style="display: none; float:left; width: 100%;">

		<div class="col-sm-6" style="margin-bottom: 8px; padding: 0;">

			<input type="text" class="form-control" id="copy_this_first" name="copythis" value="" placeholder="Název produktu">

			<input type="text" class="form-control" id="copy_this_third" name="copythis" value="" placeholder="SKU produktu" style="display: none;">

		</div>

		<div class="col-sm-1" style="padding: 0 0px 0 8px;">

			<input type="text" class="form-control text-center" id="copy_this_second" name="copythis" value="" placeholder="Počet">

		</div>


		<div class="col-sm-1" style="padding: 0 0px 0 11px;">
			<button type="button" class="remove_specification btn btn-red" style="float:left; padding: 6px 10px; cursor: pointer;"> <i class="entypo-trash"></i> </button>
		 </div>
	</div>


  <div id="empty-holder"></div>


		<button type="button" id="duplicate_specification" style="display: none;" class="btn btn-default btn-icon icon-left">
      </button>
  </div>
  </div>


 <hr>

        <div class="form-group">
            <label class="col-sm-3 control-label">Pobočka k vypořádání</label>

            <div class="col-sm-9" style="float:left;">


                <?php

                if (empty($location_id) || $location_id == '0') {$desired_location = 7;} else { $desired_location = $location_id;}

                $locations_query = $mysqli->query("SELECT * FROM shops_locations ORDER BY type ASC");

                $i = 0;
                while ($location = mysqli_fetch_array($locations_query)) {

                    ?>
                    <div class="radio" style="width: 33%; float: left;">
                        <label>
                            <input type="radio" <?php if (empty($location_id)) { ?>name="location"<?php } ?> value="<?= $location['id'] ?>" <?php if (($i == 0 && empty($location_id)) || $location['id'] == $desired_location) {$i++;
                                echo 'checked';}if (!empty($location_id)) {echo ' disabled';}?>><?= $location['name'] ?>
                        </label>
                    </div>
                <?php } ?>

                <?php if (!empty($location_id)) { ?><input type="text" name="location" value="<?= $desired_location ?>" style="display: none;"><?php } ?>

            </div>
        </div>







									<?php

    }

    ?>

						</div>
					</div>

					</div>


	</div>

	<center>
	<div class="form-group default-padding button-demo">
		<button type="submit" style="margin-bottom: 24px; margin-right: 26px; font-size: 20px; padding: 20px 50px 20px 140px; margin-top: 20px;" data-style="zoom-in" class="ladda-button btn btn-success btn-icon icon-left btn-lg"><i class="entypo-plus" style="line-height: 48px;font-size: 40px; padding: 10px 20px;"></i> <span class="ladda-label">Vytvořit dodávku</span></button>
	</div></center>

</form>

<?php }?>



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

