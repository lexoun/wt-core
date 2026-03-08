<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";

$id = $_REQUEST['id'];
$od = $_REQUEST['od'];

$product_query = $mysqli->query("SELECT id, type, productname FROM products WHERE id = '" . $_REQUEST['id'] . "'");

$product = mysqli_fetch_array($product_query);

if (isset($product['type']) && $product['type'] == 'simple') {

    ?>


<script type="text/javascript">


jQuery(document).ready(function($)
{





	$('.location_original').click(function() {

		var location_id = this.id;

		$(".location_original .tile-stats").removeClass('tile-primary');
		$(".location_original .tile-stats").addClass('tile-gray');

		$(this).find(".tile-stats").removeClass('tile-gray');
		$(this).find(".tile-stats").addClass('tile-primary');

		$("#original_location").val(location_id);

	});

	$('.location_final').click(function() {

		var location_id = this.id;

		$(".location_final .tile-stats").removeClass('tile-primary');
		$(".location_final .tile-stats").addClass('tile-gray');

		$(this).find(".tile-stats").removeClass('tile-gray');
		$(this).find(".tile-stats").addClass('tile-primary');

		$("#final_location").val(location_id);

	});



});



</script>


	<div class="modal-dialog" style="width: 700px;">
		<form role="form" method="post" action="zobrazit-prislusenstvi?action=stock_transfer&id=<?= $id ?>&link=<?= $od ?>" enctype="multipart/form-data">
		<div class="modal-content">
			<div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

				<h4 class="modal-title">Přeskladnění produktu <?= $product['productname'] ?></h4> </div>

			<div class="modal-body" style="padding: 20px 12px;">


<div class="col-sm-12" style="display: inline-block;">
	<h4>Původní sklad</h4>
</div>
<div class="col-sm-12" style="display: inline-block;">
<?php

    $locations_query = $mysqli->query("SELECT * FROM shops_locations l LEFT JOIN products_stocks s ON s.location_id = l.id AND s.product_id = '" . $product['id'] . "' ORDER BY type ASC");

    $i = 0;
    while ($location = mysqli_fetch_array($locations_query)) {

        if ($i == 0 && $location['instock'] > 0) {$original_location = $location['id'];
            $i++;
            $tyle = 'tile-primary';} else { $tyle = 'tile-gray';}

        ?>

	<div id="<?= $location['id'] ?>" class="<?php if ($location['instock'] > 0) { ?>location_original <?php } ?>col-sm-3" style="<?php if ($location['instock'] > 0) { ?>cursor:pointer;<?php } else { ?>cursor: not-allowed; opacity: 0.5;<?php } ?> padding-left: 0px; padding-right: 6px; margin-bottom: 16px;">
					<div class="tile-stats <?= $tyle ?>" style="padding: 10px;">

						<div class="num"></div> <h3 style="font-size: 12px; margin: 4px 0; text-align: center"><?= $location['name'] . ' (' . $location['instock'] . ' ks)' ?></h3> <p></p>
					</div>
				</div>

<?php } ?>
</div>

<div class="col-sm-12" style="display: inline-block;">


						<div class="input-spinner" style="text-align:center; float: left;padding: 0 36.7% 10px;">
							<center>
							<p style="margin-bottom: 6px; font-size: 11px;">množství</p>
								<button type="button" class="btn btn-default btn-lg">-</button>
								<input type="text" class="form-control size-1 input-lg" value="1" name="quantity"/>
								<button type="button" class="btn btn-default btn-lg">+</button>
							</center>
							</div>

</div>

<div class="col-sm-12" style="display: inline-block;">
	<h4>Cílový sklad</h4>
</div>
<div class="col-sm-12" style="display: inline-block;">
<?php
    $i = 0;
    mysqli_data_seek($locations_query, 0);
    while ($location = mysqli_fetch_array($locations_query)) {

        if ($i == 0) {$final_location = $location['id'];
            $i++;
            $tyle = 'tile-primary';} else { $tyle = 'tile-gray';}

        ?>

	<div id="<?= $location['id'] ?>" class="location_final col-sm-3" style="cursor:pointer; padding-left: 0px; padding-right: 6px; ">
					<div class="tile-stats <?= $tyle ?>" style="padding: 10px;">

						<div class="num"></div> <h3 style="font-size: 12px; margin: 4px 0; text-align: center"><?= $location['name'] . ' (' . $location['instock'] . ' ks)' ?></h3> <p></p>
					</div>
				</div>

<?php } ?>
</div>

				<input type="text" id="original_location" name="original_location" style="display: none;" value="<?= $original_location ?>">
				<input type="text" id="final_location" name="final_location" style="display: none;" value="<?= $final_location ?>">

<div style="clear: both"></div>
			</div>
<div class="modal-footer" style="text-align:left;"> <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>

	<a href="#" style="float:right;"><button type="submit" class="btn btn-blue btn-icon icon-left">Přeskladnit
					<i class="entypo-box"></i></button></a>
	</form>
	</div>

	<?php } elseif (isset($product['type']) && $product['type'] == 'variable') {

    ?>


	<div class="modal-dialog" style="width: 700px;">
		<div class="modal-content">
			<div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

				<h4 class="modal-title"><small>Přeskladnění produktu</small> <?= $product['productname'] ?></h4> </div>

			<div class="modal-body" style="padding: 20px 12px;">


			<?php

    $stock_query = $mysqli->query("SELECT * FROM products_variations WHERE product_id = '" . $product['id'] . "'") or die($mysqli->error);

    while ($variation_modal = mysqli_fetch_array($stock_query)) {

        $value_query = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $variation_modal['id'] . "'") or die($mysqli->error);
        $name = "";
        while ($value = mysqli_fetch_array($value_query)) {

            $name = $value['name'] . ': ' . $value['value'] . ' ' . $name;

        }

        ?>

		<script type="text/javascript">


jQuery(document).ready(function($)
{




$('#click_variation_content-<?= $variation_modal['id'] ?>').click(function() {


	$(".clicks").hide('slow');
	$(".variation").hide('slow');

   setTimeout(function () {
	$("#variation_content-<?= $variation_modal['id'] ?>").show('slow');
                 }, 700);




});



});


</script>


<div id="click_variation_content-<?= $variation_modal['id'] ?>" class="clicks col-sm-6" style="cursor:pointer; padding-left: 3px; padding-right: 3px; margin-bottom: 10px;">
					<div class="tile-stats tile-gray">
						<div class="num"></div> <h3 style="font-size: 15px;"><?= $name ?></h3> <p></p>
					</div>
				</div>





<div id="variation_content-<?= $variation_modal['id'] ?>" class="variation" style="display: none;">
<form role="form" method="post" action="zobrazit-prislusenstvi?action=stock_transfer&id=<?= $id ?>&variation_id=<?= $variation_modal['id'] ?>&link=<?= $od ?>" enctype="multipart/form-data">


<script type="text/javascript">


jQuery(document).ready(function($)
{



	$('.location_original').click(function() {

		var location_id = this.id;

		$(".location_original .tile-stats").removeClass('tile-primary');
		$(".location_original .tile-stats").addClass('tile-gray');

		$(this).find(".tile-stats").removeClass('tile-gray');
		$(this).find(".tile-stats").addClass('tile-primary');

		$("#original_location_<?= $variation_modal['id'] ?>").val(location_id);

	});

	$('.location_final').click(function() {

		var location_id = this.id;

		$(".location_final .tile-stats").removeClass('tile-primary');
		$(".location_final .tile-stats").addClass('tile-gray');

		$(this).find(".tile-stats").removeClass('tile-gray');
		$(this).find(".tile-stats").addClass('tile-primary');

		$("#final_location_<?= $variation_modal['id'] ?>").val(location_id);

	});


});


</script>



<div class="col-sm-12" style="display: inline-block;">
	<h4>Původní sklad</h4>
</div>
<div class="col-sm-12" style="display: inline-block;">
<?php

        $locations_query = $mysqli->query("SELECT * FROM shops_locations l LEFT JOIN products_stocks s ON s.location_id = l.id AND s.product_id = '" . $product['id'] . "' AND s.variation_id = '" . $variation_modal['id'] . "' ORDER BY type ASC");

        $i = 0;
        while ($location = mysqli_fetch_array($locations_query)) {

            if ($i == 0 && $location['instock'] > 0) {$original_location = $location['id'];
                $i++;
                $tyle = 'tile-primary';} else { $tyle = 'tile-gray';}

            ?>

	<div id="<?= $location['id'] ?>" class="<?php if ($location['instock'] > 0) { ?>location_original <?php } ?>col-sm-4" style="<?php if ($location['instock'] > 0) { ?>cursor:pointer;<?php } else { ?>cursor: not-allowed; opacity: 0.5;<?php } ?> padding-left: 0px; padding-right: 6px; margin-bottom: 16px;">
					<div class="tile-stats <?= $tyle ?>">

						<div class="num"></div> <h3 style="font-size: 15px; margin: 4px 0;"><?= $location['name'] . ' (' . $location['instock'] . ' ks)' ?></h3> <p></p>
					</div>
				</div>

<?php } ?>
</div>

<div class="col-sm-12" style="display: inline-block;">


						<div class="input-spinner" style="text-align:center; float: left;padding: 0 36.7% 10px;">
							<center>
							<p style="margin-bottom: 6px; font-size: 11px;">množství</p>
								<button type="button" class="btn btn-default btn-lg">-</button>
								<input type="text" class="form-control size-1 input-lg" value="1" name="quantity"/>
								<button type="button" class="btn btn-default btn-lg">+</button>
							</center>
							</div>

</div>

<div class="col-sm-12" style="display: inline-block;">
	<h4>Cílový sklad</h4>
</div>
<div class="col-sm-12" style="display: inline-block;">
<?php
        $i = 0;
        mysqli_data_seek($locations_query, 0);
        while ($location = mysqli_fetch_array($locations_query)) {

            if ($i == 0) {$final_location = $location['id'];
                $i++;
                $tyle = 'tile-primary';} else { $tyle = 'tile-gray';}

            ?>

	<div id="<?= $location['id'] ?>" class="location_final col-sm-4" style="cursor:pointer; padding-left: 0px; padding-right: 6px; ">
					<div class="tile-stats <?= $tyle ?>">

						<div class="num"></div> <h3 style="font-size: 15px; margin: 4px 0;"><?= $location['name'] . ' (' . $location['instock'] . ' ks)' ?></h3> <p></p>
					</div>
				</div>

<?php } ?>
</div>


				<input type="text" id="original_location_<?= $variation_modal['id'] ?>" name="original_location" style="display: none;" value="<?= $original_location ?>">
				<input type="text" id="final_location_<?= $variation_modal['id'] ?>" name="final_location" style="display: none;" value="<?= $final_location ?>">

<div style="clear: both"></div>










<div class="modal-footer" style="text-align:left;"> <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>

	<a href="#" style="float:right;"><button type="submit" class="btn btn-blue btn-icon icon-left">Přeskladnit
					<i class="entypo-box"></i></button></a>
	</form> </div>
		</div>

<?php
    }?>
<div class="clear"></div>
	</div>

</div> </div>

<?php

}?>

	<script src="https://www.wellnesstrade.cz/admin/assets/js/neon-custom.js"></script>
	<script src="https://www.wellnesstrade.cz/admin/assets/js/bootstrap.js"></script>

