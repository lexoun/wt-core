<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";

$id = $_REQUEST['id'];
$od = $_REQUEST['od'];

if(!empty($_REQUEST['var_id'])){
$var_id = $_REQUEST['var_id'];
}else{ $var_id = 0; }

$product_query = $mysqli->query("SELECT id, type, productname FROM products WHERE id = '" . $_REQUEST['id'] . "'");

$product = mysqli_fetch_array($product_query);

if (isset($product['type']) && $product['type'] == 'simple') {

    ?>

<script type="text/javascript">


jQuery(document).ready(function($)
{

    $('.location').click(function() {

        var location_id = this.id;

        $(".location .tile-stats").removeClass('tile-primary');
        $(".location .tile-stats").addClass('tile-gray');

        $(this).find(".tile-stats").removeClass('tile-gray');
        $(this).find(".tile-stats").addClass('tile-primary');

        $("#choosed_stock").val(location_id);

        $(".fast_add1").attr('href', 'zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=1&stock='+location_id+'&link=<?= $od ?>');
        $(".fast_add2").attr('href', 'zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=2&stock='+location_id+'&link=<?= $od ?>');
        $(".fast_add3").attr('href', 'zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=3&stock='+location_id+'&link=<?= $od ?>');
        $(".fast_add4").attr('href', 'zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=4&stock='+location_id+'&link=<?= $od ?>');
        $(".fast_add5").attr('href', 'zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=5&stock='+location_id+'&link=<?= $od ?>');
        $(".fast_add10").attr('href', 'zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=10&stock='+location_id+'&link=<?= $od ?>');
        $(".fast_add15").attr('href', 'zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=15&stock='+location_id+'&link=<?= $od ?>');
        $(".fast_add20").attr('href', 'zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=20&stock='+location_id+'&link=<?= $od ?>');

    });

});


</script>


	<div class="modal-dialog" style="width: 800px">
		<form role="form" method="post" action="zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&link=<?= $od ?>" enctype="multipart/form-data">
		<div class="modal-content">
			<div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

				<h4 class="modal-title">Naskladnění produktu <?= $product['productname'] ?></h4> </div>

			<div class="modal-body" style="padding: 20px 12px;">

				<?php

    $locations_query = $mysqli->query("SELECT * FROM shops_locations l LEFT JOIN products_stocks s ON s.location_id = l.id AND s.product_id = '" . $product['id'] . "' ORDER BY type ASC");

    $i = 0;
    while ($location = mysqli_fetch_array($locations_query)) {

        if ($i == 0) {$first_location = $location['id'];
            $i++;
            $tyle = 'tile-primary';} else { $tyle = 'tile-gray';}

        ?>

	<div id="<?= $location['id'] ?>" class="location col-sm-3" style="cursor:pointer; padding-left: 0px; padding-right: 6px; margin-bottom: 16px;">
					<div class="tile-stats <?= $tyle ?>" style="padding: 10px;">
						<div class="num"></div> <h3 style="font-size: 12px; margin: 4px 0; text-align: center;"><?= $location['name'] . ' (' . $location['instock'] . ' ks)' ?></h3> <p></p>
					</div>
				</div>

<?php } ?>


				<input type="text" id="choosed_stock" name="choosed_stock" style="display: none;" value="<?= $first_location ?>">

				<a href="zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=1&stock=<?= $first_location ?>&link=<?= $od ?>" class="fast_add1 btn btn-default btn-lg" style="padding: 10px 15.71px;">1 ks</a>

				<a href="zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=2&stock=<?= $first_location ?>&link=<?= $od ?>" class="fast_add2 btn btn-default btn-lg" style="padding: 10px 15.71px;">2 ks</a>

				<a href="zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=3&stock=<?= $first_location ?>&link=<?= $od ?>" class="fast_add3 btn btn-default btn-lg" style="padding: 10px 15.71px;">3 ks</a>

				<a href="zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=4&stock=<?= $first_location ?>&link=<?= $od ?>" class="fast_add4 btn btn-default btn-lg" style="padding: 10px 15.71px;">4 ks</a>

				<a href="zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=5&stock=<?= $first_location ?>&link=<?= $od ?>" class="fast_add5 btn btn-default btn-lg" style="padding: 10px 15.71px;">5 ks</a>

				<a href="zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=10&stock=<?= $first_location ?>&link=<?= $od ?>" class="fast_add10 btn btn-default btn-lg" style="padding: 10px 15.71px;">10 ks</a>

				<a href="zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=15&stock=<?= $first_location ?>&link=<?= $od ?>" class="fast_add15 btn btn-default btn-lg" style="padding: 10px 15.71px;">15 ks</a>

				<a href="zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=20&stock=<?= $first_location ?>&link=<?= $od ?>" class="fast_add20 btn btn-default btn-lg" style="padding: 10px 15.71px;">20 ks</a>




						<div class="input-spinner" style="text-align:center; padding: 26px 36.7% 10px;">
								<center><p style="margin-bottom: 16px;">nebo zvolte</p>
								<button type="button" class="btn btn-default btn-lg">-</button>
								<input type="text" class="form-control size-1 input-lg" value="1" name="quant"/>
								<button type="button" class="btn btn-default btn-lg">+</button>
							</center>
							</div>
			</div>
<div class="modal-footer" style="text-align:left;"> <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>

	<a href="#" style="float:right;"><button type="submit" class="btn btn-blue btn-icon icon-left">Přidat na sklad
					<i class="entypo-box"></i></button></a>
	</form>
	</div>

	<?php } elseif (isset($product['type']) && $product['type'] == 'variable') {



    ?>


	<div class="modal-dialog" style="width: 800px">
		<div class="modal-content">
			<div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

				<h4 class="modal-title"><small>Naskladnění produktu</small> <?= $product['productname'] ?></h4> </div>

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

                <?php if(empty($var_id)){ ?>

<div id="click_variation_content-<?= $variation_modal['id'] ?>" class="clicks col-sm-6" style="cursor:pointer; padding-left: 3px; padding-right: 3px; margin-bottom: 10px;">
					<div class="tile-stats tile-gray">
						<div class="num"></div> <h3 style="font-size: 15px;"><?= $name ?></h3> <p></p>
					</div>
				</div>


<?php } ?>


<div id="variation_content-<?= $variation_modal['id'] ?>" class="variation" <?php if(empty($var_id) || $var_id != $variation_modal['id']){ ?>style="display: none;"<?php } ?>>
<form role="form" method="post" action="zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&variation_id=<?= $variation_modal['id'] ?>&link=<?= $od ?>" enctype="multipart/form-data">


<script type="text/javascript">


jQuery(document).ready(function($)
{


$('.location').click(function() {

	var location_id = this.id;

	$(".location .tile-stats").removeClass('tile-primary');
	$(".location .tile-stats").addClass('tile-gray');

	$(this).find(".tile-stats").removeClass('tile-gray');
	$(this).find(".tile-stats").addClass('tile-primary');

	$("#choosed_stock_<?= $variation_modal['id'] ?>").val(location_id);

	$(".fast_add1_<?= $variation_modal['id'] ?>").attr('href', 'zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=1&variation_id=<?= $variation_modal['id'] ?>&stock='+location_id+'&link=<?= $od ?>');
	$(".fast_add2_<?= $variation_modal['id'] ?>").attr('href', 'zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=2&variation_id=<?= $variation_modal['id'] ?>&stock='+location_id+'&link=<?= $od ?>');
	$(".fast_add3_<?= $variation_modal['id'] ?>").attr('href', 'zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=3&variation_id=<?= $variation_modal['id'] ?>&stock='+location_id+'&link=<?= $od ?>');
	$(".fast_add4_<?= $variation_modal['id'] ?>").attr('href', 'zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=4&variation_id=<?= $variation_modal['id'] ?>&stock='+location_id+'&link=<?= $od ?>');
	$(".fast_add5_<?= $variation_modal['id'] ?>").attr('href', 'zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=5&variation_id=<?= $variation_modal['id'] ?>&stock='+location_id+'&link=<?= $od ?>');
	$(".fast_add10_<?= $variation_modal['id'] ?>").attr('href', 'zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=10&variation_id=<?= $variation_modal['id'] ?>&stock='+location_id+'&link=<?= $od ?>');
	$(".fast_add15_<?= $variation_modal['id'] ?>").attr('href', 'zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=15&variation_id=<?= $variation_modal['id'] ?>&stock='+location_id+'&link=<?= $od ?>');
	$(".fast_add20_<?= $variation_modal['id'] ?>").attr('href', 'zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=20&variation_id=<?= $variation_modal['id'] ?>&stock='+location_id+'&link=<?= $od ?>');



});



});


</script>

<?php

        $locations_query = $mysqli->query("SELECT * FROM shops_locations l LEFT JOIN products_stocks s ON s.location_id = l.id AND s.product_id = '" . $product['id'] . "' AND s.variation_id = '" . $variation_modal['id'] . "' ORDER BY type ASC");

        $i = 0;
        while ($location = mysqli_fetch_array($locations_query)) {

            if ($i == 0) {$first_location = $location['id'];
                $i++;
                $tyle = 'tile-primary';} else { $tyle = 'tile-gray';}

            ?>

	<div id="<?= $location['id'] ?>" class="location col-sm-3" style="cursor:pointer; padding-left: 0px; padding-right: 6px; margin-bottom: 16px;">
					<div class="tile-stats <?= $tyle ?>">
						<div class="num"></div> <h3 style="font-size: 15px; margin: 4px 0;"><?= $location['name'] . ' (' . $location['instock'] . ' ks)' ?></h3> <p></p>
					</div>
				</div>

<?php } ?>

				<input type="text" id="choosed_stock_<?= $variation_modal['id'] ?>" name="choosed_stock" style="display: none;" value="<?= $first_location ?>">



				<a href="zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=1&variation_id=<?= $variation_modal['id'] ?>&stock=<?= $first_location ?>&link=<?= $od ?>" class="fast_add1_<?= $variation_modal['id'] ?> btn btn-default btn-lg" style="padding: 10px 15.71px;">1 ks</a>

				<a href="zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=2&variation_id=<?= $variation_modal['id'] ?>&stock=<?= $first_location ?>&link=<?= $od ?>" class="fast_add2_<?= $variation_modal['id'] ?> btn btn-default btn-lg" style="padding: 10px 15.71px;">2 ks</a>

				<a href="zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=3&variation_id=<?= $variation_modal['id'] ?>&stock=<?= $first_location ?>&link=<?= $od ?>" class="fast_add3_<?= $variation_modal['id'] ?> btn btn-default btn-lg" style="padding: 10px 15.71px;">3 ks</a>

				<a href="zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=4&variation_id=<?= $variation_modal['id'] ?>&stock=<?= $first_location ?>&link=<?= $od ?>" class="fast_add4_<?= $variation_modal['id'] ?> btn btn-default btn-lg" style="padding: 10px 15.71px;">4 ks</a>

				<a href="zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=5&variation_id=<?= $variation_modal['id'] ?>&stock=<?= $first_location ?>&link=<?= $od ?>" class="fast_add5_<?= $variation_modal['id'] ?> btn btn-default btn-lg" style="padding: 10px 15.71px;">5 ks</a>

				<a href="zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=10&variation_id=<?= $variation_modal['id'] ?>&stock=<?= $first_location ?>&link=<?= $od ?>" class="fast_add10_<?= $variation_modal['id'] ?> btn btn-default btn-lg" style="padding: 10px 15.71px;">10 ks</a>

				<a href="zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=15&variation_id=<?= $variation_modal['id'] ?>&stock=<?= $first_location ?>&link=<?= $od ?>" class="fast_add15_<?= $variation_modal['id'] ?> btn btn-default btn-lg" style="padding: 10px 15.71px;">15 ks</a>

				<a href="zobrazit-prislusenstvi?action=tostock&id=<?= $id ?>&quant=20&variation_id=<?= $variation_modal['id'] ?>&stock=<?= $first_location ?>&link=<?= $od ?>" class="fast_add20_<?= $variation_modal['id'] ?> btn btn-default btn-lg" style="padding: 10px 15.71px;">20 ks</a>



						<div class="input-spinner" style="text-align:center; padding: 26px 36.7% 10px;">
								<center><p style="margin-bottom: 16px;">nebo zvolte</p>
								<button type="button" class="btn btn-default btn-lg">-</button>
								<input type="text" class="form-control size-1 input-lg" value="1" name="quant"/>
								<button type="button" class="btn btn-default btn-lg">+</button>
							</center>
							</div>


							<div class="modal-footer" style="text-align:left;"> <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>

	<a href="#" style="float:right;"><button type="submit" class="btn btn-blue btn-icon icon-left">Přidat na sklad
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

