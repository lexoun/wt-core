<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/functions.php";

if (isset($_REQUEST['customer'])) {$customer = $_REQUEST['customer'];}
if (isset($_REQUEST['category'])) {$category = $_REQUEST['category'];}
if (isset($_REQUEST['site'])) {$site = $_REQUEST['site'];}
if (isset($_REQUEST['od'])) {$od = $_REQUEST['od'];}
if (isset($_REQUEST['q'])) {$search = $_REQUEST['q'];}

$pagetitle = "Chyby příslušenství";

$clientquery = $mysqli->query('SELECT * FROM demands WHERE email="' . $_COOKIE['cookie_email'] . '"') or die($mysqli->error);
$client = mysqli_fetch_assoc($clientquery);


include VIEW . '/default/header.php';


$query = "";
$currentpage = "chyby-prislusenstvi";
$allow_sites = "";

?>


<?php
$perpage = 20;
?>

<div class="row">
	<div class="col-md-9 col-sm-7">

	</div>
	<div class="col-md-3 col-sm-5" style="text-align: right;float:right;">


				<a href="<?= $home ?>/admin/pages/errors/rozmer-obrazku-prislusenstvi" style=" margin-right: 24px;" class="btn btn-default btn-icon icon-left btn-lg">
					<i class="entypo-cancel"></i>
					Chyby obrázků
				</a>

	</div>
</div>




<?php

$productsquery = $mysqli->query("SELECT p.id FROM products p LEFT JOIN products_sites s ON p.id = s.product_id WHERE p.availability != 3 AND  p.delivery_time = 0 GROUP BY p.id") or die($mysqli->error);
if (mysqli_num_rows($productsquery) > 0) {

    ?>


<div class="row">
	<div class="col-md-9 col-sm-7">
		<h2>Chybějící doba doručení <small>(Cekem <?= mysqli_num_rows($productsquery) ?>)</small></h2>
	</div>


</div>



<div class="btn-group col-sm-12">
	<?php

    while ($products = mysqli_fetch_assoc($productsquery)) { ?>




				<a href="<?= $home ?>/admin/zobrazit-prislusenstvi?id=<?= $products['id'] ?>" target="_blank"><label class="btn btn-lg btn-primary" style="margin-top: 6px;">
											<?= $products['id'] ?>
										</label></a>


<?php
    }
    ?>
</div>

<?php
} else { ?>
<ul class="cbp_tmtimeline" style=" margin-left: 25px;">
  <li style="margin-top: 80px;">

		<div class="cbp_tmicon">
			<i class="entypo-block" style="line-height: 42px !important;"></i>
		</div>

		<div class="cbp_tmlabel empty" style="padding-top: 9px;">
			<span><a style="font-weight: bold; margin-left: -12px;font-size: 17px;">U příslušenství nejsou žádné chyby.</a></span>
		</div>
	</li>
  </ul>
<?php
}

?>





<hr>


<?php

$productsquery = $mysqli->query("SELECT *, p.id as id FROM products p WHERE p.type = 'simple' AND p.id AND (p.purchase_price = '0' OR p.ean = '' OR p.code = '' OR p.description = '')") or die($mysqli->error);
if (mysqli_num_rows($productsquery) > 0) {

    ?>

<div class="row">
	<div class="col-md-9 col-sm-7">
		<h2>Chyby příslušenství <small>(Cekem <?= mysqli_num_rows($productsquery) ?>)</small></h2>
	</div>
</div>



<table class="table table-bordered table-striped datatable dataTable">
	<thead>
		<tr>
			<th class="text-center">Název</th>
			<th class="text-center">EAN kód</th>
			<th class="text-center">SKU kód</th>
			<th class="text-center">Nákupní cena</th>
			<th class="text-center">Prodejní cena</th>
			<th class="text-center">Velkoobchodní cena</th>

		</tr>
	</thead>

	<tbody role="alert" aria-live="polite" aria-relevant="all">

<?php
    while ($products = mysqli_fetch_assoc($productsquery)) { ?>


<tr class="even">

				<td class="text-center"><a href="<?= $home ?>/admin/pages/accessories/upravit-prislusenstvi?id=<?= $products['id'] ?>" target="_blank" style="font-weight: bold;"><?= $products['productname'] ?> <i class="entypo-forward"></i></a></td>



				<td class="text-center">

					<?php if ($products['ean'] != '' && $products['ean'] != ' ' && $products['ean'] != '0') {

					    echo $products['ean'];



					} else {echo '<strong style="color:#d42020;">chybí!</strong>';

                        if(!empty($products['code'])){

                            $mysqli->query("UPDATE products SET ean = '".$products['code']."' WHERE id = '".$products['id']."'")or die($mysqli->error);
                        }

					}?>

				</td>

    <td class="text-center">

					<?php if ($products['code'] != '' && $products['code'] != ' ' && $products['code'] != '0') {echo $products['code'];} else {echo '<strong style="color:#d42020;">chybí!</strong>';



                        if(!empty($products['ean'])){

                            $mysqli->query("UPDATE products SET code = '".$products['ean']."' WHERE id = '".$products['id']."'")or die($mysqli->error);
                        }


					}?>

				</td>

				<td class="text-center">

					<?php if ($products['purchase_price'] != '' && $products['purchase_price'] != ' ' && $products['purchase_price'] != '0') {echo $products['purchase_price'];} else {echo '<strong style="color:#d42020;">chybí!</strong>';}?>

				</td>

				<td class="text-center">

					<?php if ($products['price'] != '' && $products['price'] != ' ' && $products['price'] != '0') {echo $products['price'];} else {echo '<strong style="color:#d42020;">chybí!</strong>';}?>

				</td>

				<td class="text-center">

					<?php if ($products['wholesale_price'] != '' && $products['wholesale_price'] != ' ' && $products['wholesale_price'] != '0') {echo $products['wholesale_price'];} else {echo '<strong style="color:#d42020;">chybí!</strong>';}?>

				</td>



			</tr>


<?php
    }?>
</tbody>
</table>

<?php
} else { ?>
<ul class="cbp_tmtimeline" style=" margin-left: 25px;">
  <li style="margin-top: 80px;">

		<div class="cbp_tmicon">
			<i class="entypo-block" style="line-height: 42px !important;"></i>
		</div>

		<div class="cbp_tmlabel empty" style="padding-top: 9px;">
			<span><a style="font-weight: bold; margin-left: -12px;font-size: 17px;">U příslušenství nejsou žádné chyby.</a></span>
		</div>
	</li>
  </ul>
<?php } ?>


<hr>
<?php

$productsquery = $mysqli->query("SELECT *, v.id as var_id, p.id as id, v.wholesale_price as wholesale_price, v.ean as ean, v.sku as sku FROM products p, products_variations v WHERE v.product_id = p.id AND p.type = 'variable' AND (v.purchase_price = '0' OR v.ean = '' OR v.sku = '' OR v.client_price = '' OR v.client_price = '0')") or die($mysqli->error);
if (mysqli_num_rows($productsquery) > 0) {

    ?>

<div class="row">
	<div class="col-md-8 col-sm-7">
		<h2>Varianty příslušenství <small>(Cekem <?= mysqli_num_rows($productsquery) ?>)</small></h2>
	</div>
</div>




<table class="table table-bordered table-striped datatable dataTable">
	<thead>
		<tr>
			<th class="text-center">Název</th>
			<th class="text-center">EAN kód</th>
			<th class="text-center">SKU kód</th>
			<th class="text-center">Nákupní cena</th>
			<th class="text-center">Prodejní cena</th>
			<th class="text-center">Velkoobchodní cena</th>

		</tr>
	</thead>

	<tbody role="alert" aria-live="polite" aria-relevant="all">

<?php
    while ($products = mysqli_fetch_assoc($productsquery)) {

        $value_query = $mysqli->query("SELECT value FROM products_variations_values WHERE variation_id = '" . $products['var_id'] . "'") or die($mysqli->error);
        $value = mysqli_fetch_array($value_query);

        ?>


<tr class="even">

				<td class="text-center"><a href="<?= $home ?>/admin/pages/accessories/upravit-prislusenstvi?id=<?= $products['id'] ?>" target="_blank" style="font-weight: bold;"><?= $products['productname'] ?> <small>- <?= $value['value'] ?></small> <i class="entypo-forward"></i></a></td>


				<td class="text-center">

					<?php if ($products['ean'] != '' && $products['ean'] != ' ' && $products['ean'] != '0') {echo $products['ean'];} else {echo '<strong style="color:#d42020;">chybí!</strong>';

                        if(!empty($products['sku'])){

                            $mysqli->query("UPDATE products_variations SET ean = '".$products['sku']."' WHERE id = '".$products['var_id']."'")or die($mysqli->error);
                        }

					}?>

				</td>

            <td class="text-center">

					<?php if ($products['sku'] != '' && $products['sku'] != ' ' && $products['sku'] != '0') {echo $products['sku'];} else {echo '<strong style="color:#d42020;">chybí!</strong>';}?>

				</td>

				<td class="text-center">

					<?php if ($products['purchase_price'] != '' && $products['purchase_price'] != ' ' && $products['purchase_price'] != '0') {echo $products['purchase_price'];} else {echo '<strong style="color:#d42020;">chybí!</strong>';}?>

				</td>

				<td class="text-center">

					<?php if ($products['client_price'] != '' && $products['client_price'] != ' ' && $products['client_price'] != '0') {echo $products['client_price'];} else {echo '<strong style="color:#d42020;">chybí!</strong>';}?>

				</td>

				<td class="text-center">

					<?php if ($products['wholesale_price'] != '' && $products['wholesale_price'] != ' ' && $products['wholesale_price'] != '0') {echo $products['wholesale_price'];} else {echo '<strong style="color:#d42020;">chybí!</strong>';}?>

				</td>



			</tr>


<?php
    }?>
</tbody>
</table>

<?php
} else { ?>
<ul class="cbp_tmtimeline" style=" margin-left: 25px;">
  <li style="margin-top: 80px;">

		<div class="cbp_tmicon">
			<i class="entypo-block" style="line-height: 42px !important;"></i>
		</div>

		<div class="cbp_tmlabel empty" style="padding-top: 9px;">
			<span><a style="font-weight: bold; margin-left: -12px;font-size: 17px;">U variant příslušenství nejsou žádné chyby.</a></span>
		</div>
	</li>
  </ul>
<?php } ?>




<hr>
<?php

$productsquery = $mysqli->query("SELECT *, p.id as id FROM products p LEFT JOIN products_sites s ON (p.id = s.product_id AND s.site != 'wellnesstrade') WHERE p.availability != 3 AND p.type = 'simple' AND p.ean LIKE 'aaa%' GROUP BY p.id") or die($mysqli->error);
if (mysqli_num_rows($productsquery) > 0) {
    ?>


<script type="text/javascript">


jQuery(document).ready(function($)
{


$('.show-missing-ean').click(function() {

	$('.missing-ean').show( "slow");

});

});


</script>



<div class="row">
	<div class="col-md-8 col-sm-7">
		<h2>Základní EAN kódy u příslušenství <small>(Cekem <?= mysqli_num_rows($productsquery) ?>)</small></h2>
	</div>
</div>


<div class="col-md-12 well" style="border-color: #ebebeb; margin-top: 8px;background-color: #fbfbfb;">

  	<div class="btn-group col-sm-12" style="text-align: center; padding: 0;">

            <a class="show-missing-ean"><label class="btn btn-lg btn-primary">
              Zobrazit příslušenství se základním EAN kódem
            </label></a>

    </div>

<div class="clear"></div>
</div>


<table class="missing-ean table table-bordered table-striped datatable dataTable" style="display: none;">
	<thead>
		<tr>
			<th class="text-center">Název</th>
			<th class="text-center">EAN kód</th>
			<th class="text-center">Nákupní cena</th>
			<th class="text-center">Prodejní cena</th>
			<th class="text-center">Velkoobchodní cena</th>

		</tr>
	</thead>

	<tbody role="alert" aria-live="polite" aria-relevant="all">

<?php
    while ($products = mysqli_fetch_assoc($productsquery)) { ?>


<tr class="even">

				<td class="text-center"><a href="<?= $home ?>/admin/upravit-prislusenstvi?id=<?= $products['id'] ?>" target="_blank" style="font-weight: bold;"><?= $products['productname'] ?> <i class="entypo-forward"></i></a></td>



				<td class="text-center">

					<?php if ($products['ean'] != '' && $products['ean'] != ' ' && $products['ean'] != '0') {echo $products['ean'];} else {echo '<strong style="color:#d42020;">chybí!</strong>';}?>

				</td>

				<td class="text-center">

					<?php if ($products['purchase_price'] != '' && $products['purchase_price'] != ' ' && $products['purchase_price'] != '0') {echo $products['purchase_price'];} else {echo '<strong style="color:#d42020;">chybí!</strong>';}?>

				</td>

				<td class="text-center">

					<?php if ($products['price'] != '' && $products['price'] != ' ' && $products['price'] != '0') {echo $products['price'];} else {echo '<strong style="color:#d42020;">chybí!</strong>';}?>

				</td>

				<td class="text-center">

					<?php if ($products['wholesale_price'] != '' && $products['wholesale_price'] != ' ' && $products['wholesale_price'] != '0') {echo $products['wholesale_price'];} else {echo '<strong style="color:#d42020;">chybí!</strong>';}?>

				</td>



			</tr>


<?php
    }?>
</tbody>
</table>

<?php
} else { ?>
<ul class="cbp_tmtimeline" style=" margin-left: 25px;">
  <li style="margin-top: 80px;">

		<div class="cbp_tmicon">
			<i class="entypo-block" style="line-height: 42px !important;"></i>
		</div>

		<div class="cbp_tmlabel empty" style="padding-top: 9px;">
			<span><a style="font-weight: bold; margin-left: -12px;font-size: 17px;">U příslušenství nejsou žádné chyby.</a></span>
		</div>
	</li>
  </ul>
<?php
}

?>

<hr>
<?php

$productsquery = $mysqli->query("SELECT *, v.id as var_id, p.id as id, v.wholesale_price as wholesale_price, v.ean as ean, v.sku as sku FROM products p, products_variations v, products_sites s WHERE p.availability != 3 AND s.site != 'wellnesstrade' AND s.product_id = p.id AND v.product_id = p.id AND p.type = 'variable' AND v.ean LIKE 'aaa%' GROUP BY p.id") or die($mysqli->error);
if (mysqli_num_rows($productsquery) > 0) {

    ?>
<script type="text/javascript">


jQuery(document).ready(function($)
{

$('.show-missing-ean-var').click(function() {

	$('.missing-ean-var').show( "slow");

});


});


</script>

<div class="row">
	<div class="col-md-8 col-sm-7">
		<h2>Základní EAN kódy u variant <small>(Cekem <?= mysqli_num_rows($productsquery) ?>)</small></h2>
	</div>
</div>


<div class="col-md-12 well" style="border-color: #ebebeb; margin-top: 8px;background-color: #fbfbfb;">

  	<div class="btn-group col-sm-12" style="text-align: center; padding: 0;">

            <a class="show-missing-ean-var"><label class="btn btn-lg btn-primary">
              Zobrazit varianty se základním EAN kódem
            </label></a>

    </div>

<div class="clear"></div>
</div>

<table class="missing-ean-var table table-bordered table-striped datatable dataTable" style="display: none;">
	<thead>
		<tr>
			<th class="text-center">Název</th>
			<th class="text-center">EAN kód</th>
			<th class="text-center">Nákupní cena</th>
			<th class="text-center">Prodejní cena</th>
			<th class="text-center">Velkoobchodní cena</th>

		</tr>
	</thead>

	<tbody role="alert" aria-live="polite" aria-relevant="all">

<?php
    while ($products = mysqli_fetch_assoc($productsquery)) {

        $value_query = $mysqli->query("SELECT value FROM products_variations_values WHERE variation_id = '" . $products['var_id'] . "'") or die($mysqli->error);
        $value = mysqli_fetch_array($value_query);

        ?>


<tr class="even">

				<td class="text-center"><a href="<?= $home ?>/admin/upravit-prislusenstvi?id=<?= $products['id'] ?>" target="_blank" style="font-weight: bold;"><?= $products['productname'] ?> <small>- <?= $value['value'] ?></small> <i class="entypo-forward"></i></a></td>

				<td class="text-center">

					<?php if ($products['ean'] != '' && $products['ean'] != ' ' && $products['ean'] != '0') {echo $products['ean'];} else {echo '<strong style="color:#d42020;">chybí!</strong>';}?>

				</td>

				<td class="text-center">

					<?php if ($products['purchase_price'] != '' && $products['purchase_price'] != ' ' && $products['purchase_price'] != '0') {echo $products['purchase_price'];} else {echo '<strong style="color:#d42020;">chybí!</strong>';}?>

				</td>

				<td class="text-center">

					<?php if ($products['client_price'] != '' && $products['client_price'] != ' ' && $products['client_price'] != '0') {echo $products['client_price'];} else {echo '<strong style="color:#d42020;">chybí!</strong>';}?>

				</td>

				<td class="text-center">

					<?php if ($products['wholesale_price'] != '' && $products['wholesale_price'] != ' ' && $products['wholesale_price'] != '0') {echo $products['wholesale_price'];} else {echo '<strong style="color:#d42020;">chybí!</strong>';}?>

				</td>



			</tr>


<?php
    }?>
</tbody>
</table>

<?php
} else { ?>
<ul class="cbp_tmtimeline" style=" margin-left: 25px;">
  <li style="margin-top: 80px;">

		<div class="cbp_tmicon">
			<i class="entypo-block" style="line-height: 42px !important;"></i>
		</div>

		<div class="cbp_tmlabel empty" style="padding-top: 9px;">
			<span><a style="font-weight: bold; margin-left: -12px;font-size: 17px;">U variant příslušenství nejsou žádné chyby.</a></span>
		</div>
	</li>
  </ul>
<?php } ?>


<footer class="main">


	&copy; <?= date("Y") ?> <span style=" float:right;"><?php
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$total_time = round(($finish - $start), 4);

echo 'PHP '.PHP_VERSION.' | Page generated in ' . $total_time . ' seconds.';?></span>

</footer>	</div>




<script type="text/javascript">
$(document).ready(function(){
    $(".toggle-modal-remove").click(function(e){

			$('#remove-modal').removeData('bs.modal');
    	 e.preventDefault();


    	 var type = $(this).data("type");

    	 var id = $(this).data("id");

        $("#remove-modal").modal({

            remote: '/admin/controllers/modals/modal-remove.php?id='+id+'&type='+type+'&od=<?= $od ?>',
        });
    });
});
</script>


<div class="modal fade" id="remove-modal" aria-hidden="true" style="display: none; margin-top: 10%;">

</div>





<script type="text/javascript">
$(document).ready(function(){
    $(".toggle-modal-stock").click(function(e){

			$('#stock-modal').removeData('bs.modal');
    	 e.preventDefault();


    	 var id = $(this).data("id");

        $("#stock-modal").modal({

            remote: '/admin/controllers/modals/modal-stock-data.php?id='+id+'&od=<?= $od ?>',
        });
    });
});
</script>


<div class="modal fade" id="stock-modal" aria-hidden="true" style="display: none; margin-top: 8%;">


</div>


	</div>

	<script src="<?= $home ?>/admin/assets/js/jquery.validate.min.js"></script>

	<link rel="stylesheet" href="<?= $home ?>/admin/assets/js/select2/select2-bootstrap.css">
	<link rel="stylesheet" href="<?= $home ?>/admin/assets/js/select2/select2.css">
	<link rel="stylesheet" href="<?= $home ?>/admin/assets/js/selectboxit/jquery.selectBoxIt.css">
	<link rel="stylesheet" href="<?= $home ?>/admin/assets/js/daterangepicker/daterangepicker-bs3.css">
	<link rel="stylesheet" href="<?= $home ?>/admin/assets/js/icheck/skins/minimal/_all.css">
	<link rel="stylesheet" href="<?= $home ?>/admin/assets/js/icheck/skins/square/_all.css">
	<link rel="stylesheet" href="<?= $home ?>/admin/assets/js/icheck/skins/flat/_all.css">
	<link rel="stylesheet" href="<?= $home ?>/admin/assets/js/icheck/skins/futurico/futurico.css">
	<link rel="stylesheet" href="<?= $home ?>/admin/assets/js/icheck/skins/polaris/polaris.css">

	<!-- Bottom Scripts -->
	<script src="<?= $home ?>/admin/assets/js/gsap/main-gsap.js"></script>
	<script src="<?= $home ?>/admin/assets/js/jquery-ui/js/jquery-ui-1.10.3.minimal.min.js"></script>
	<script src="<?= $home ?>/admin/assets/js/bootstrap.js"></script>
	<script src="<?= $home ?>/admin/assets/js/joinable.js"></script>
	<script src="<?= $home ?>/admin/assets/js/resizeable.js"></script>
	<script src="<?= $home ?>/admin/assets/js/neon-api.js"></script>
	<script src="<?= $home ?>/admin/assets/js/select2/select2.min.js"></script>
	<script src="<?= $home ?>/admin/assets/js/bootstrap-tagsinput.min.js"></script>
	<script src="<?= $home ?>/admin/assets/js/typeahead.min.js"></script>
	<script src="<?= $home ?>/admin/assets/js/selectboxit/jquery.selectBoxIt.min.js"></script>
	<script src="<?= $home ?>/admin/assets/js/bootstrap-datepicker.js"></script>
	<script src="<?= $home ?>/admin/assets/js/bootstrap-timepicker.min.js"></script>
	<script src="<?= $home ?>/admin/assets/js/bootstrap-colorpicker.min.js"></script>
	<script src="<?= $home ?>/admin/assets/js/daterangepicker/moment.min.js"></script>
	<script src="<?= $home ?>/admin/assets/js/daterangepicker/daterangepicker.js"></script>
	<script src="<?= $home ?>/admin/assets/js/jquery.multi-select.js"></script>
	<script src="<?= $home ?>/admin/assets/js/icheck/icheck.min.js"></script>
	<script src="<?= $home ?>/admin/assets/js/neon-chat.js"></script>
	<script src="<?= $home ?>/admin/assets/js/neon-custom.js"></script>
	<script src="<?= $home ?>/admin/assets/js/neon-demo.js"></script>


</body>
</html>

