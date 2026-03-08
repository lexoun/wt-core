<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";
$start = microtime(true);

$pagetitle = "Hromadná úprava skladu";

if (isset($_REQUEST['customer'])) {$customer = $_REQUEST['customer'];}
if (isset($_REQUEST['category'])) {$category = $_REQUEST['category'];}
if (isset($_REQUEST['site'])) { $site = $_REQUEST['site'];}
if (isset($_REQUEST['od'])) {$od = $_REQUEST['od'];}
if (isset($_REQUEST['q'])) {$search = $_REQUEST['q'];}

$perpage = 100;
$query = "";
$currentpage = "batch-warehouse";
$allow_sites = "";

if (isset($customer) && ($customer == 0 || $customer == 3 || $customer == 1)) {

    $query .= $query ? 'AND ' : ' WHERE ';
    $query .= 'p.customer = ' . $customer;

    if ($currentpage == "batch-warehouse") {

        $currentpage .= '?customer=' . $customer;

    } else {

        $currentpage .= '&customer=' . $customer;

    }

}



if (isset($site)) {

    $allow_sites = ',products_sites_categories s';

    $query .= $query ? 'AND ' : ' WHERE ';
    $query .= 's.product_id = p.id AND s.site = "' . $site . '"';

    if ($currentpage == "batch-warehouse") {

        $currentpage .= '?site=' . $site;

    } else {

        $currentpage .= '&site=' . $site;

    }

}


if (isset($category)) {

    $allow_sites = ',products_sites_categories s, shops_categories c ';

    $query .= $query ? 'AND ' : ' WHERE ';
    $query .= '(s.product_id = p.id AND s.category = "' . $category . '" AND c.id = s.category) OR (s.product_id = p.id AND c.parent_id = "' . $category . '" AND c.id = s.category)';

    if ($currentpage == "batch-warehouse") {

        $currentpage .= '?category=' . $category;

    } else {

        $currentpage .= '&category=' . $category;

    }

}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "edit") {

    if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {$od = 1;}

    $s_lol = $od - 1;
    $s_pocet = $s_lol * $perpage;

    $stmt = $mysqli->prepare('UPDATE products_stocks SET instock = ? WHERE product_id = ? AND variation_id = ? AND location_id = ?');
    $instock = null;
    $product_id = null;
    $variation_id = null;
    $location_id = null;
    $stmt->bind_param('iiii', $instock, $product_id, $variation_id, $location_id);

    $i;
    $products_query = $mysqli->query("SELECT *, p.id as id FROM products p $allow_sites $query ORDER BY p.type limit " . $s_pocet . "," . $perpage) or die($mysqli->error);
    while ($product = mysqli_fetch_array($products_query)) {

        if (isset($product['type']) && $product['type'] == 'simple') {

            $locations_query = $mysqli->query("SELECT * FROM shops_locations l LEFT JOIN products_stocks s ON s.location_id = l.id AND s.product_id = '" . $product['id'] . "' GROUP BY l.id ORDER BY type ASC") or die($mysqli->error);

            while ($location = mysqli_fetch_array($locations_query)) {

                $product_id = $product['id'];
                $variation_id = 0;
                $location_id = $location['id'];

                $instock = $_POST['p_' . $product_id . '_l_' . $location_id];

                if (isset($instock) && $instock != "") {

                    $i++;
                    $stmt->execute() or die($mysqli->error);

                }

            }

        } else {

            $variations_query = $mysqli->query("SELECT * FROM products_variations WHERE product_id = " . $product['id'] . "") or die($mysqli->error);
            while ($variation = mysqli_fetch_array($variations_query)) {

                $stocks_query = $mysqli->query("SELECT *, l.name as name FROM products_stocks s, shops_locations l WHERE s.variation_id = '" . $variation['id'] . "' AND l.id = s.location_id") or die($mysqli->error);
                while ($stocks = mysqli_fetch_array($stocks_query)) {

                    $product_id = $product['id'];
                    $variation_id = $variation['id'];
                    $location_id = $stocks['location_id'];

                    $instock = $_POST['v_' . $variation_id . '_l_' . $location_id];

                    if (isset($instock) && $instock != "") {

                        $i++;
                        $stmt->execute() or die($mysqli->error);

                    }

                }

            }

        }

    }
    include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/controllers/crons/accessories-to-cron.php';

    header('location: https://' . $_SERVER['SERVER_NAME'] . '/admin/pages/accessories/' . $currentpage . '&od=' . $od);
    exit;
}

include VIEW . '/default/header.php';

?>


<style>

.product { float: left; margin: 0;padding: 6px 6px 6px;border-bottom: 1px solid #eeeeee;line-height: 31px;}

.product .product-name { width: 600px; padding: 0; }

.product .product-name strong { color: #000; }

.product .warehouse {width: 140px; padding: 0}
.product .warehouse .form-group { margin: 0; }
.product .warehouse label { margin: 0; padding-right: 0; text-align: right; font-size: 8px }
.product .warehouse .form-group div { width: 70px }

.product .variation {margin: 0;width: 100%;  padding: 6px 0 2px}
.product .variation .variation-name {width: 600px; padding:0; padding-left: 5%; }
.product .variation .variation-name i {color: #000;}


.product:hover { background-color: #F8F8F8;}
.product .variation:hover { background-color: #F0F0F0;}

</style>



<?php

if (isset($site) && $site == "nosite") {

    $products_max_query = $mysqli->query("SELECT COUNT(*) AS NumberOfOrders FROM products p WHERE p.id NOT IN (SELECT s.product_id FROM products p, products_sites_categories s WHERE s.product_id = p.id)") or die($mysqli->error);
    $products_max = mysqli_fetch_array($products_max_query);

    $max = $products_max['NumberOfOrders'];

    if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {$od = 1;}

    $s_lol = $od - 1;
    $s_pocet = $s_lol * $perpage;
    $pocet_prispevku = $max;
    $products_query = $mysqli->query("SELECT * FROM products p WHERE p.id NOT IN (SELECT s.product_id FROM products p, products_sites_categories s WHERE s.product_id = p.id) order by p.id desc limit " . $s_pocet . "," . $perpage) or die($mysqli->error);

} else {

    $products_max_query = $mysqli->query("SELECT p.id FROM products p $allow_sites $query GROUP BY p.id ") or die($mysqli->error);
    $max = mysqli_num_rows($products_max_query);

    if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {$od = 1;}

    $s_lol = $od - 1;
    $s_pocet = $s_lol * $perpage;
    $pocet_prispevku = $max;

    $products_query = $mysqli->query("SELECT *, p.id as id FROM products p $allow_sites $query GROUP BY p.id ORDER BY p.id desc limit " . $s_pocet . "," . $perpage) or die($mysqli->error);

}

?>


<div class="col-md-12 well" style="border-color: #ebebeb; background-color: #fbfbfb;">

		<div>
		<div class="btn-group" style="text-align: left;">

						<a href="batch-warehouse"><label class="btn btn-lg <?php if (!isset($site)) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
							Vše
						</label></a>

						<?php $shops_query = $mysqli->query("SELECT * FROM shops");
while ($shop = mysqli_fetch_array($shops_query)) { ?>
						<a href="?site=<?= $shop['slug'] ?>"><label class="btn btn-lg <?php if (!empty($site) && $site == $shop['slug']) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
							<?= $shop['name'] ?>
						</label></a>
						<?php } ?>

						<a href="?site=nosite"><label class="btn btn-lg <?php if (!empty($site) && $site == "nosite") { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
							Nepřiřazené
						</label></a>



					</div>
				</div>



 <?php if (!empty($site) && $site != "wellnesstrade") { ?>
 <hr style="border-top: 1px solid #ebebeb;">


<script type="text/javascript">


jQuery(document).ready(function($)
{



$('.show_categories').click(function() {


if($(".categories").is(":visible")){

 $( ".categories" ).hide( "slow" );
 $( ".show_categories" ).html( "zobrazit kategorie" );


}else{

 $( ".categories" ).show( "slow" );
 $( ".show_categories" ).html( "skrýt kategorie" );

}

});


});

</script>

 		<div class="show_categories" style="text-align: center; font-size: 15px; font-weight: bold; color: #555; cursor: pointer; text-decoration: underline;">
 			zobrazit kategorie
 		</div>

		<div class="categories" style="display: none;">

<?php $parent_categories_query = $mysqli->query("SELECT c.name as name, c.id as id, s.name as shopname, s.slug as shopslug, s.id as shop_id FROM shops_categories c, shops s WHERE s.id = c.shop_id AND c.parent_id = 0 AND s.slug = '$site'") or die($mysqli->error);

    while ($parent_categories = mysqli_fetch_array($parent_categories_query)) { ?>

<div class="btn-group" style="text-align: left; width: 100%;">
			<a href="batch-warehouse?site=<?= $site ?>&category=<?= $parent_categories['id'] ?>"><label style="margin-bottom: 8px; border-bottom: 2px solid #d2d2d2;" class="btn <?php if (!empty($category) && $category == $parent_categories['id']) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>"><strong><?= $parent_categories['name'] ?></strong></label></a>
			<br>
			 <?php

        $subparents_query = $mysqli->query("SELECT id, name FROM shops_categories WHERE parent_id = '" . $parent_categories['id'] . "' AND shop_id = '" . $parent_categories['shop_id'] . "'");
        if (mysqli_num_rows($subparents_query) > 0) {
            while ($subparents = mysqli_fetch_array($subparents_query)) {

                ?>
 				<a href="batch-warehouse?site=<?= $site ?>&category=<?= $subparents['id'] ?>"><label style="margin-bottom: 8px;"class="btn <?php if (!empty($category) && $category == $subparents['id']) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>"><?= $subparents['name'] ?></label></a>
			<?php

                $sub_subparents_query = $mysqli->query("SELECT id, name FROM shops_categories WHERE parent_id = '" . $subparents['id'] . "' AND shop_id = '" . $parent_categories['shop_id'] . "'");
                if (mysqli_num_rows($sub_subparents_query) > 0) { ?>
			&nbsp;&nbsp;&nbsp;<i class="fa fa-long-arrow-right"></i>&nbsp;&nbsp;&nbsp;
			<?php
                    while ($sub_subparents = mysqli_fetch_array($sub_subparents_query)) {

                        ?>
 				<a href="batch-warehouse?site=<?= $site ?>&category=<?= $sub_subparents['id'] ?>"><label style="margin-bottom: 8px;"class="btn <?php if (!empty($category) && $category == $sub_subparents['id']) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>"><?= $sub_subparents['name'] ?></label></a>
			<?php

                    }
                }?><br>
				 	<?php

            }?>
 				<hr style="border-top: 1px solid #ebebeb;">
			<?php } ?>
		</div>


<?php } ?>

	</div>
<?php } ?>




 <?php if (isset($site) && $site == "wellnesstrade") { ?>
 <hr style="border-top: 1px solid #ebebeb;">


<script type="text/javascript">


jQuery(document).ready(function($)
{



$('.show_categories').click(function() {


if($(".categories").is(":visible")){

 $( ".categories" ).hide( "slow" );
 $( ".show_categories" ).html( "zobrazit kategorie" );


}else{

 $( ".categories" ).show( "slow" );
 $( ".show_categories" ).html( "skrýt kategorie" );

}

});


});

</script>

 		<div class="show_categories" style="text-align: center; font-size: 15px; font-weight: bold; color: #555; cursor: pointer; text-decoration: underline;">
 			zobrazit kategorie
 		</div>

		<div class="categories" style="display: none;">

<div class="btn-group" style="text-align: left;">
			<h4 style="float: left;  margin-right: 18px;">Vířivky</h4> <?php
    $categoriesquery = $mysqli->query('SELECT * FROM products_cats WHERE customer = 1') or die($mysqli->error);
    while ($categories = mysqli_fetch_array($categoriesquery)) { ?>
 <a href="batch-warehouse?site=wellnesstrade&category=<?= $categories['seoslug'] ?>"><label class="btn <?php if ($category == $categories['seoslug']) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>"><?= $categories['name'] ?></label></a>
			<?php } ?>
		</div>
 <hr style="border-top: 1px solid #ebebeb;">
<div class="btn-group" style="text-align: left;">
			<h4 style="float: left; height: 50px; line-height: 50px; margin-right: 18px;">Sauny</h4> <?php
    $categoriesquery = $mysqli->query('SELECT * FROM products_cats WHERE customer = 0') or die($mysqli->error);
    while ($categories = mysqli_fetch_array($categoriesquery)) { ?>
 <a href="batch-warehouse?site=wellnesstrade&category=<?= $categories['seoslug'] ?>"><label style="margin-bottom: 8px;" class="btn <?php if ($category == $categories['seoslug']) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>"><?= $categories['name'] ?></label></a>


		<?php } ?>	</div>
	</div>
<?php } ?>








</div>



<div class="row">



<div class="col-md-12">
<form role="form" method="post" name="myform" class="form-horizontal form-groups-bordered" action="<?= $currentpage ?>&action=edit&od=<?= $od ?>" enctype="multipart/form-data">
<?php
$count = 0;
$count_vari = 0;
//$products_query = $mysqli->query("SELECT * FROM products ORDER BY type")or die($mysqli->error);

$total_query = $mysqli->query("SELECT (SELECT COUNT(*) FROM products WHERE type = 'simple') as total_simple,
       (SELECT COUNT(*) FROM products WHERE type = 'variable') as total_variable") or die($mysqli->error);
$total = mysqli_fetch_array($total_query);
?>

	<div class="col-md-12">
	<?php

while ($product = mysqli_fetch_array($products_query)) {

    ?>
	<div class="product col-md-12">
<?php
    if (isset($product['type']) && $product['type'] == 'simple') {

        ?>
	<div class="product-name col-sm-4">
		<a href="/admin/pages/accessories/zobrazit-prislusenstvi?id=<?= $product['id'] ?>" target="_blank"><span style="width: 110px; float: left;"><?= $product['code'] ?></span> <strong><?= $product['productname'] ?></strong></a>
	</div>
	<?php

        $locations_query = $mysqli->query("SELECT * FROM shops_locations l LEFT JOIN products_stocks s ON s.location_id = l.id AND s.product_id = '" . $product['id'] . "' GROUP BY l.id ORDER BY type ASC") or die($mysqli->error);

        while ($location = mysqli_fetch_array($locations_query)) {

            ?>
			<div class="warehouse col-sm-2">
				<div class="form-group">
					<label class="col-sm-6">
						<?= $location['name'] ?>
					</label>
					<div class="col-sm-6">
						<input type="text" class="form-control" name="p_<?= $product['id'] ?>_l_<?= $location['id'] ?>" value="<?= $location['instock'] ?>">
					</div>
				</div>
			</div>
	<?php

        }

    } else {

        ?>
	<div class="product-name col-sm-12">
		<a href="/admin/pages/accessories/zobrazit-prislusenstvi?id=<?= $product['id'] ?>" target="_blank"><span style="width: 110px; float: left;"><?= $product['code'] ?></span> <strong style="color: #000;"><?= $product['productname'] ?></strong></a>
	</div>

<?php

        $variations_query = $mysqli->query("SELECT *, v.id as id FROM products_variations v, products_variations_values val WHERE v.product_id = '" . $product['id'] . "' AND v.id = val.variation_id") or die($mysqli->error);
        while ($variation = mysqli_fetch_array($variations_query)) { ?>

	<div class="variation col-sm-5">
			<div class="variation-name col-sm-2">
                <span style="width: 60px; float: left; margin-left: 40px;"><?= $variation['sku'] ?></span><i><?= $variation['value'] ?></i>
			</div>



	<?php

            ?>

	<?php

            $stocks_query = $mysqli->query("SELECT *, l.name as name FROM products_stocks s, shops_locations l WHERE s.variation_id = '" . $variation['id'] . "' AND l.id = s.location_id") or die($mysqli->error);
            while ($stocks = mysqli_fetch_array($stocks_query)) { ?>


			<div class="warehouse col-sm-2">
				<div class="form-group">
					<label class="col-sm-6">
						<?= $stocks['name'] ?>
					</label>
					<div class="col-sm-6">
						<input type="text" class="form-control" name="v_<?= $variation['id'] ?>_l_<?= $stocks['location_id'] ?>" value="<?= $stocks['instock'] ?>">
					</div>
				</div>
			</div>



<?php

            }?>
</div>

	<?php }

    }

    ?>

	</div>

	<?php

}
?>
</div>
<center>
	<div class="form-group default-padding">
		<button type="submit" style="margin-bottom: 24px; margin-right: 26px; font-size: 20px; padding: 20px 50px 20px 140px; margin-top: 20px;" data-style="zoom-in" class="btn btn-primary btn-icon icon-left btn-lg"><i class="entypo-pencil" style="line-height: 48px;font-size: 40px; padding: 10px 20px;"></i> <span class="ladda-label">Upravit</span></button>
	</div></center>

</form>

	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<center><ul class="pagination pagination-sm">
			<?php
include VIEW . "/default/pagination.php";?>
		</ul>

		<h1 style="margin-bottom: 50px;">Celkem: <?= $max ?></h1>
	</center>
	</div>
</div>


<!-- Footer -->
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

