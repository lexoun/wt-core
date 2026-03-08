<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/functions.php";

if (isset($_REQUEST['customer'])) {$customer = $_REQUEST['customer'];}
if (isset($_REQUEST['category'])) {$category = $_REQUEST['category'];}
//$site = $_REQUEST['site'];
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
	<div class="col-md-8 col-sm-7">
		<h2>EAN Duplicita</h2>
	</div>
</div>


<?php

$productsquery = $mysqli->query("SELECT ean FROM products WHERE ean != '' group by ean having count(*) >= 2") or die($mysqli->error);
if (mysqli_num_rows($productsquery) > 0) {
    ?>

<table class="table table-bordered table-striped datatable dataTable">
	<thead>
		<tr>
			<th class="text-center">EAN kód</th>
		</tr>
	</thead>

	<tbody role="alert" aria-live="polite" aria-relevant="all">

<?php
    while ($products = mysqli_fetch_assoc($productsquery)) { ?>


<tr class="even">


				<td class="text-center">

					<?php if ($products['ean'] != '' && $products['ean'] != ' ' && $products['ean'] != '0') {echo $products['ean'];} else {echo '<strong style="color:#d42020;">chybí!</strong>';}?>

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



<div class="row">
	<div class="col-md-8 col-sm-7">
		<h2>EAN Duplicita VARIANTY</h2>
	</div>
</div>


<?php

$productsquery = $mysqli->query("SELECT ean FROM products_variations WHERE ean != '' group by ean having count(*) >= 2") or die($mysqli->error);
if (mysqli_num_rows($productsquery) > 0) {
    ?>

<table class="table table-bordered table-striped datatable dataTable">
	<thead>
		<tr>
			<th class="text-center">EAN kód</th>
		</tr>
	</thead>

	<tbody role="alert" aria-live="polite" aria-relevant="all">

<?php
    while ($products = mysqli_fetch_assoc($productsquery)) { ?>


<tr class="even">


				<td class="text-center">

					<?php if ($products['ean'] != '' && $products['ean'] != ' ' && $products['ean'] != '0') {echo $products['ean'];} else {echo '<strong style="color:#d42020;">chybí!</strong>';}?>

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








<div class="row">
	<div class="col-md-8 col-sm-7">
		<h2>EAN Duplicita PRODUKT s VARIANTOU</h2>
	</div>
</div>


<?php

$productsquery = $mysqli->query("SELECT p.ean, v.ean FROM products p, products_variations v WHERE p.ean = v.ean AND v.ean != ''") or die($mysqli->error);
if (mysqli_num_rows($productsquery) > 0) {
    ?>

<table class="table table-bordered table-striped datatable dataTable">
	<thead>
		<tr>
			<th class="text-center">EAN kód</th>
		</tr>
	</thead>

	<tbody role="alert" aria-live="polite" aria-relevant="all">

<?php
    while ($products = mysqli_fetch_assoc($productsquery)) { ?>


<tr class="even">


				<td class="text-center">

					<?php if ($products['ean'] != '' && $products['ean'] != ' ' && $products['ean'] != '0') {echo $products['ean'];} else {echo '<strong style="color:#d42020;">chybí!</strong>';}?>

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



<div class="row">
	<div class="col-md-8 col-sm-7">
		<h2>SKU Duplicita</h2>
	</div>
</div>


<?php

$productsquery = $mysqli->query("SELECT code, id FROM products WHERE code != '' group by code having count(*) >= 2") or die($mysqli->error);
if (mysqli_num_rows($productsquery) > 0) {
    ?>

<table class="table table-bordered table-striped datatable dataTable">
	<thead>
		<tr>
			<th class="text-center">SKU kód</th>
		</tr>
	</thead>

	<tbody role="alert" aria-live="polite" aria-relevant="all">

<?php
    while ($products = mysqli_fetch_assoc($productsquery)) { ?>


<tr class="even">


				<td class="text-center">

					<?php if ($products['code'] != '' && $products['code'] != ' ' && $products['code'] != '0') {echo $products['code'].' - <a href="../accessories/zobrazit-prislusenstvi?id='.$products['id'].'">'.$products['id'].'</a>';
					} else {echo '<strong style="color:#d42020;">chybí!</strong>';}?>

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



<div class="row">
	<div class="col-md-8 col-sm-7">
		<h2>SKU Duplicita VARIANTY</h2>
	</div>
</div>


<?php

$productsquery = $mysqli->query("SELECT sku, product_id FROM products_variations WHERE sku != '' group by sku having count(*) >= 2") or die($mysqli->error);
if (mysqli_num_rows($productsquery) > 0) {
    ?>

<table class="table table-bordered table-striped datatable dataTable">
	<thead>
		<tr>
			<th class="text-center">SKU kód</th>
		</tr>
	</thead>

	<tbody role="alert" aria-live="polite" aria-relevant="all">

<?php
    while ($products = mysqli_fetch_assoc($productsquery)) { ?>


<tr class="even">


				<td class="text-center">

					<?php if ($products['sku'] != '' && $products['sku'] != ' ' && $products['sku'] != '0') {

					    echo $products['sku'].' - <a href="../accessories/zobrazit-prislusenstvi?id='.$products['product_id'].'">'.$products['product_id'].'</a>';} else {echo '<strong style="color:#d42020;">chybí!</strong>';}?>

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








<div class="row">
	<div class="col-md-8 col-sm-7">
		<h2>SKU Duplicita PRODUKT s VARIANTOU</h2>
	</div>
</div>


<?php

$productsquery = $mysqli->query("SELECT p.code, v.sku, p.id, v.id as vid, v.product_id FROM products p, products_variations v WHERE p.code = v.sku AND v.sku != ''") or die($mysqli->error);
if (mysqli_num_rows($productsquery) > 0) {
    ?>

<table class="table table-bordered table-striped datatable dataTable">
	<thead>
		<tr>
			<th class="text-center">SKU kód</th>
		</tr>
	</thead>

	<tbody role="alert" aria-live="polite" aria-relevant="all">

<?php
    while ($products = mysqli_fetch_assoc($productsquery)) { ?>


<tr class="even">


				<td class="text-center">

					<?php if ($products['code'] != '' && $products['code'] != ' ' && $products['code'] != '0') {

					    echo $products['code'].' - '.$products['id'] .' x '.$products['product_id'] .' ('.$products['vid'].')';

					} else {echo '<strong style="color:#d42020;">chybí!</strong>';}?>

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

