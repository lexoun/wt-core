<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";

$product_query = $mysqli->query("SELECT * FROM products WHERE code = '" . $_REQUEST['sku'] . "'");

if (mysqli_num_rows($product_query) > 0) {

    $product = mysqli_fetch_array($product_query);

    $price = number_format($product['price'], 0, ',', ' ') . ' Kč';

    $product_title = $product['code'] . ' – ' . $product['productname'] . ' – ' . $price;

    $sku = $product['code'];

} else {

    $product_query = $mysqli->query("SELECT *, s.id as ajdee, s.price as price FROM products p, products_variations s WHEREAND p.id = s.product_id AND s.sku = '" . $_REQUEST['sku'] . "'");
    $product = mysqli_fetch_array($product_query);

    $select = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $product['ajdee'] . "'") or die($mysqli->error);
    $desc = "";

    $desc = "";
    while ($var = mysqli_fetch_array($select)) {

        $desc = $desc . $var['name'] . ': ' . $var['value'] . ' ';

    }

    $price = number_format($product['price'], 0, ',', ' ') . ' Kč';

    $product_title = $product['sku'] . ' – ' . $product['productname'] . ' – ' . $desc . ' – ' . $price;

    $sku = $product['sku'];

}

?>

		<div class="specification" style="float: left; width: 100%;">
			<div class="col-sm-7" style="margin-bottom: 8px; padding: 0;">

			<input type="text" class="form-control" id="specification_name" name="product_name[]" value="<?= $product_title ?>" placeholder="Název produktu">

			<input type="text" class="form-control" id="copy_this_third" name="product_sku[]" value="<?= $sku ?>" placeholder="SKU produktu" style="display: none;">

			</div>
			<div class="col-sm-1" style="padding: 0 0px 0 8px;">
				<input type="text" class="form-control text-center" id="specification_value" name="product_quantity[]" value="1" placeholder="Počet">
			</div>

			<div class="col-sm-2" style="padding: 0 0px 0 8px;">
				<input type="text" class="form-control text-center" id="specification_value" name="product_price[]" value="<?= $bridge['price'] ?>" placeholder="Počet">
			</div>


      <div class="col-sm-2" style="padding: 0 0px 0 8px;">
        <input type="text" class="form-control text-center" id="specification_value" name="product_original_price[]" value="<?= $product['price'] ?>" placeholder="Počet">
      </div>

			<div class="col-sm-1" style="padding: 0 0px 0 11px;">
			<button type="button" class="remove_specification btn btn-red" style="float:left; padding: 6px 10px; cursor: pointer;"> <i class="entypo-trash"></i> </button>
			 </div>
		</div>

