<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

if (isset($_REQUEST['customer'])) {$customer = $_REQUEST['customer'];}
if (isset($_REQUEST['category'])) {$category = $_REQUEST['category'];}
$site = $_REQUEST['site'];
if (isset($_REQUEST['od'])) {$od = $_REQUEST['od'];}
if (isset($_REQUEST['q'])) {$search = $_REQUEST['q'];}

if (isset($search) && $search != "") {

    $pagetitle = 'Hledaný výraz "' . $search . '"';

    $bread1 = "Náhradní díly";
    $abread1 = "nahradni-dily";

} else {

    $pagetitle = "Náhradní díly";

}

$clientquery = $mysqli->query('SELECT * FROM demands WHERE email="' . $_COOKIE['cookie_email'] . '"') or die($mysqli->error);
$client = mysqli_fetch_assoc($clientquery);





if (isset($_REQUEST['action']) && $_REQUEST['action'] == "remove") {


    $unlinkquery = $mysqli->query('SELECT seourl, ean FROM products WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);
    $unlink = mysqli_fetch_assoc($unlinkquery);

    // added
    foreach($productImageSizes as $imageSize){

        $path = PRODUCT_IMAGE_PATH.'/'.$imageSize.'/'.$unlink['seourl'].'.jpg';
        if(file_exists($path)){ unlink($path); };

        unset($path);
    }

    $images = glob($_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/small/' . $unlink['seourl'] . '_{,[1-9]}{,[1-9]}[0-9].jpg', GLOB_BRACE);
    if (!empty($images)) {

        foreach ($images as $image) {

            $imageName = basename($image);

            foreach($productImageSizes as $imageSize){

                $path = PRODUCT_IMAGE_PATH.'/'.$imageSize.'/'.$imageName;
                if(file_exists($path)){ unlink($path); };
                unset($path);

            }

        }

    }

    $images = glob($_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/small/' . $unlink['seourl'] . '_variation_{,[1-9]}{,[1-9]}[0-9].jpg', GLOB_BRACE);
    if (!empty($images)) {

        foreach ($images as $image) {

            $imageName = basename($image);

            foreach($productImageSizes as $imageSize){

                $path = PRODUCT_IMAGE_PATH.'/'.$imageSize.'/'.$imageName;
                if(file_exists($path)){ unlink($path); };
                unset($path);

            }

        }

    }
    // added

    $mysqli->query('DELETE FROM products WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);
    $mysqli->query('DELETE FROM products_categories WHERE productid="' . $_REQUEST['id'] . '"') or die($mysqli->error);

    $select_variations = $mysqli->query("SELECT id FROM products_variations WHERE product_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);
    while ($variation = mysqli_fetch_array($select_variations)) {

        $mysqli->query('DELETE FROM products_variations_values WHERE variation_id="' . $variation['id'] . '"') or die($mysqli->error);

    }

    $mysqli->query('DELETE FROM products_variations WHERE product_id="' . $_REQUEST['id'] . '"') or die($mysqli->error);
    $mysqli->query("DELETE FROM products_specifications WHERE product_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);
    $mysqli->query("DELETE FROM products_sites_categories WHERE product_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);
    $mysqli->query("DELETE FROM products_sites WHERE product_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);


    // todo remove from eshop webhook
    //api_product_remove($_REQUEST['id'], '');


    if (isset($_REQUEST['link'])) {

        header('location: https://' . $_SERVER['SERVER_NAME'] . '/admin/pages/warehouse/nahradni-dily?od=' . $_REQUEST['link'] . '&success=remove');
        exit;

    } else {

        header('location: https://' . $_SERVER['SERVER_NAME'] . '/admin/pages/warehouse/nahradni-dily?success=remove');
        exit;
    }

}


include VIEW . '/default/header.php';


$query = "";
$currentpage = "nahradni-dily";
$allow_sites = "";

if (isset($customer) && $customer == 0 || $customer == 3 || $customer == 1) {

    if ($query == "") {

        $query = 'AND p.customer = ' . $customer;

    } else {

        $query = $query . ' AND p.customer = ' . $customer;

    }

    if ($currentpage == "nahradni-dily") {

        $currentpage = $currentpage . '?customer=' . $customer;

    } else {

        $currentpage = $currentpage . '&customer=' . $customer;

    }

}

if (isset($category)) {

    $allow_sites = ',products_sites_categories s';

    if ($query == "") {

        $query = 'AND s.product_id = p.id AND s.category = "' . $category . '"';

    } else {

        $query = $query . ' AND s.product_id = p.id AND s.category = "' . $category . '"';

    }

    if ($currentpage == "nahradni-dily") {

        $currentpage = $currentpage . '?category=' . $category;

    } else {

        $currentpage = $currentpage . '&category=' . $category;

    }

}

if (isset($site)) {

    $allow_sites = ',products_sites_categories s';

    if ($query == "") {

        $query = 'AND s.product_id = p.id AND s.site = "' . $site . '"';

    } else {

        $query = $query . ' AND s.product_id = p.id AND s.site = "' . $site . '"';

    }

    if ($currentpage == "nahradni-dily") {

        $currentpage = $currentpage . '?site=' . $site;

    } else {

        $currentpage = $currentpage . '&site=' . $site;

    }

}

?>


<?php
$perpage = 25;
if (isset($search) && $search != "") {

    $productsquery = $mysqli->query("SELECT * FROM products WHERE productname like '%$search%' GROUP BY productname
 ORDER BY CASE WHEN productname like '$search %' THEN 0
               WHEN productname like '_$search %' THEN 1
               WHEN productname like '$search%' THEN 2
               WHEN productname like '% $search' THEN 3
               WHEN productname like '% $search %' THEN 4
               WHEN productname like '%$search' THEN 5
               WHEN productname like '%$search%' THEN 6
               ELSE 7 END, productname") or die("bNeexistuje");

} else {

    if ($site == "nosite") {

        $products_max_query = $mysqli->query("SELECT COUNT(*) AS NumberOfOrders FROM products p WHERE p.spare_part = 1 AND p.id NOT IN (SELECT s.product_id FROM products p, products_sites_categories s WHERE s.product_id = p.id)") or die($mysqli->error);
        $products_max = mysqli_fetch_array($products_max_query);

        $max = $products_max['NumberOfOrders'];

        if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {$od = 1;}

        $s_lol = $od - 1;
        $s_pocet = $s_lol * $perpage;
        $pocet_prispevku = $max;
        $productsquery = $mysqli->query("SELECT * FROM products p WHERE p.spare_part = 1 AND p.id NOT IN (SELECT s.product_id FROM products p, products_sites_categories s WHERE s.product_id = p.id) order by productname asc limit " . $s_pocet . "," . $perpage) or die($mysqli->error);

    } else {

        $products_max_query = $mysqli->query("SELECT COUNT(*) AS NumberOfOrders FROM products p $allow_sites WHERE p.spare_part = 1 $query") or die($mysqli->error);
        $products_max = mysqli_fetch_array($products_max_query);

        $max = $products_max['NumberOfOrders'];

        if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {$od = 1;}

        $s_lol = $od - 1;
        $s_pocet = $s_lol * $perpage;
        $pocet_prispevku = $max;

        $productsquery = $mysqli->query("SELECT *, p.id as id FROM products p $allow_sites WHERE p.spare_part = 1 $query order by productname asc limit " . $s_pocet . "," . $perpage) or die($mysqli->error);

    }

}

if (isset($search) && $search != "") { ?>

<div class="row">
	<div class="col-md-8 col-sm-8">
		<h2>Na hledaný výraz <i><u>"<?= $search ?>"</u></i> odpovídá toto příslušenství:</h2>
	</div>

	<div class="col-md-4 col-sm-4">

		<form method="get" role="form">

			<div class="form-group">
			<div style="margin-bottom: 12px; width: 260px; float:left; margin-left: 10px;margin-right: 4px;"><input id="cheart" value="<?= $search ?>" type="text" name="q" class="form-control" placeholder="Hledání..." /></div>

				<button style="width: 50px; float:left;" type="submit" class="btn btn-default"><i style=" position: relative; right: 0; top: 0;" class="entypo-search"></i></button>
			</div>

		</form>

	</div>
</div>


	<?php } else { ?>
<div class="row">
	<div class="col-md-4 col-sm-4">
		<h2><?= $pagetitle ?></h2>
	</div>

	<div class="col-md-4">
		<center><ul class="pagination pagination-sm">
			<?php
    include VIEW . "/default/pagination.php";?>
		</ul>
	</center>
	</div>

	<div class="col-md-4 col-sm-5">

		<form method="get" role="form">

			<div class="form-group">
			<div style="margin-bottom: 12px; width: 260px; float:left; margin-left: 10px;margin-right: 4px;"><input id="cheart" type="text" name="q" class="form-control" placeholder="Hledání..." /></div>

				<button style="width: 50px; float:left;" type="submit" class="btn btn-default"><i style=" position: relative; right: 0; top: 0;" class="entypo-search"></i></button>
			</div>

		</form>

	</div>
</div>
<!-- Pager for search results --><div class="col-md-12 well" style="border-color: #ebebeb; background-color: #fbfbfb;">

		<div>
		<div class="btn-group" style="text-align: left;">

						<a href="nahradni-dily"><label class="btn btn-lg <?php if (!isset($site)) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
							Vše
						</label></a>

						<?php $shops_query = $mysqli->query("SELECT * FROM shops");
    while ($shop = mysqli_fetch_array($shops_query)) { ?>
						<a href="?site=<?= $shop['slug'] ?>"><label class="btn btn-lg <?php if ($site == $shop['slug']) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
							<?= $shop['name'] ?>
						</label></a>
						<?php } ?>

						<a href="?site=nosite"><label class="btn btn-lg <?php if ($site == "nosite") { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
							Nepřiřazené
						</label></a>



					</div>
				</div>



 <?php if ($site != "wellnesstrade" && isset($site)) { ?>
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
			<a href="nahradni-dily?site=<?= $site ?>&category=<?= $parent_categories['id'] ?>"><label style="margin-bottom: 8px; border-bottom: 2px solid #d2d2d2;" class="btn <?php if ($category == $parent_categories['id']) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>"><strong><?= $parent_categories['name'] ?></strong></label></a>
			<br>
			 <?php

            $subparents_query = $mysqli->query("SELECT id, name FROM shops_categories WHERE parent_id = '" . $parent_categories['id'] . "' AND shop_id = '" . $parent_categories['shop_id'] . "'");
            if (mysqli_num_rows($subparents_query) > 0) {
                while ($subparents = mysqli_fetch_array($subparents_query)) { ?>
 				<a href="nahradni-dily?site=<?= $site ?>&category=<?= $subparents['id'] ?>"><label style="margin-bottom: 8px;"class="btn <?php if ($category == $subparents['id']) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>"><?= $subparents['name'] ?></label></a>
			<?php

                    $sub_subparents_query = $mysqli->query("SELECT id, name FROM shops_categories WHERE parent_id = '" . $subparents['id'] . "' AND shop_id = '" . $parent_categories['shop_id'] . "'");
                    if (mysqli_num_rows($sub_subparents_query) > 0) { ?>
			&nbsp;&nbsp;&nbsp;<i class="fa fa-long-arrow-right"></i>&nbsp;&nbsp;&nbsp;
			<?php
                        while ($sub_subparents = mysqli_fetch_array($sub_subparents_query)) {

                            ?>
 				<a href="nahradni-dily?site=<?= $site ?>&category=<?= $sub_subparents['id'] ?>"><label style="margin-bottom: 8px;"class="btn <?php if ($category == $sub_subparents['id']) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>"><?= $sub_subparents['name'] ?></label></a>
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




 <?php if ($site == "wellnesstrade") { ?>
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
 <a href="nahradni-dily?site=wellnesstrade&category=<?= $categories['seoslug'] ?>"><label class="btn <?php if ($category == $categories['seoslug']) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>"><?= $categories['name'] ?></label></a>
			<?php } ?>
		</div>
 <hr style="border-top: 1px solid #ebebeb;">
<div class="btn-group" style="text-align: left;">
			<h4 style="float: left; height: 50px; line-height: 50px; margin-right: 18px;">Sauny</h4> <?php
        $categoriesquery = $mysqli->query('SELECT * FROM products_cats WHERE customer = 0') or die($mysqli->error);
        while ($categories = mysqli_fetch_array($categoriesquery)) { ?>
 <a href="nahradni-dily?site=wellnesstrade&category=<?= $categories['seoslug'] ?>"><label style="margin-bottom: 8px;" class="btn <?php if ($category == $categories['seoslug']) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>"><?= $categories['name'] ?></label></a>


		<?php } ?>	</div>
	</div>
<?php } ?>








</div><!-- Footer -->
<?php }

if (mysqli_num_rows($productsquery) > 0) {
    ?>
<div class="member-entry" style="margin-bottom: 0px; margin-top: 0; padding: 8px 15px 7px;" >




					<span id="content-<?= $targetproduct['seourl'] ?>">
					<div style="width: 80px; text-align: center; float: left;">
						<h6>Obrázek</h6>
					</div>
				<div class="member-details" style="width: 92%; text-align: center; ">
				<div class="col-sm-4">
					<h6>Název produktu & Kategorie v obchodech</h6>
				</div>

				<div class="col-sm-2" style=" width: 17.66666667%; text-align: right;    padding-right: 58px; max-width: 200px;">
					<h6>Sklad</h6>
				</div>

				<div class="col-sm-3" style=" width: 28%; text-align: center; ">

					<h6>Cena v obchodech</h6>

				</div>
				<div class="col-sm-1" style="width: 120px; text-align: center; float:right;">

					<h6>Akce</h6>

				</div>
					</div>
					</span>

		</div>
<?php
    while ($products = mysqli_fetch_assoc($productsquery)) {
        products($products);
    }} else { ?>
<ul class="cbp_tmtimeline" style=" margin-left: 25px;">
  <li style="margin-top: 80px;">

		<div class="cbp_tmicon">
			<i class="entypo-block" style="line-height: 42px !important;"></i>
		</div>

		<div class="cbp_tmlabel empty" style="padding-top: 9px;">
			<span><a style="font-weight: bold; margin-left: -12px;font-size: 17px;">Bohužel tomuto filtru neodpovídá žádný klient.</a></span>
		</div>
	</li>
  </ul>
<?php
}

?>




<!-- Pager for search results --><div class="row">
	<div class="col-md-12">
		<center><ul class="pagination pagination-sm">
			<?php
include VIEW . "/default/pagination.php";?>
		</ul>

		<h1 style="margin-bottom: 50px;">Celkem: <?= $max ?></h1>
	</center>
	</div>
</div><!-- Footer -->
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
<?php include VIEW . '/default/footer.php'; ?>