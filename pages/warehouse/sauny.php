<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";

include INCLUDES . "/googlelogin.php";
include INCLUDES . "/functions.php";

if (isset($_REQUEST['od'])) {$od = $_REQUEST['od'];}

if (isset($_REQUEST['q'])) {$search = $_REQUEST['q'];}

if (isset($search) && $search != "") {

    $pagetitle = 'Hledaný výraz "' . $search . '"';

    $bread1 = "sauny sklad";
    $abread1 = "sauny";

} else {

    $pagetitle = "Sauny sklad";

}

if (isset($_REQUEST['type'])) {$type = $_REQUEST['type'];}
if (isset($_REQUEST['customer'])) {$customer = $_REQUEST['customer'];}
if (isset($_REQUEST['category'])) {$category = $_REQUEST['category'];}
if (isset($_REQUEST['location'])) {$location = $_REQUEST['location'];}
if (isset($_REQUEST['brand'])) { $brand = $_REQUEST['brand'];}


if (isset($_REQUEST['action']) && $_REQUEST['action'] == "remove") {

    $mysqli->query('DELETE FROM warehouse WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);
    $mysqli->query('DELETE FROM warehouse_specs_bridge WHERE client_id="' . $_REQUEST['id'] . '"') or die($mysqli->error);

    header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/sauny?id=' . $_REQUEST['id'] . '&success=remove');
    exit;
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "demandnull") {

    $mysqli->query('UPDATE warehouse SET demand_id = "0" WHERE id = "' . $_REQUEST['id'] . '"') or die($mysqli->error);

    $_REQUEST['id'] = $_REQUEST['redirect'];

    saveCalendarEvent($_REQUEST['id'], 'realization');

    header('location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $_REQUEST['redirect'] . '&changedemand=success');
    exit;
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "demandchange") {

    $mysqli->query('UPDATE warehouse SET demand_id = "' . $_POST['demand'] . '" WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);

    if ($_POST['demand'] != '' && $_POST['demand'] != '0') {

        $_REQUEST['id'] = $_POST['demand'];

        saveCalendarEvent($_REQUEST['id'], 'realization');

    }

    header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/sauny?id=' . $_REQUEST['id'] . '&changedemand=success');
    exit;
}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "add") {
    $displaysuccess = true;
    $successhlaska = "Vířivka byla úspěšně přidána.";
}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "remove") {
    $displaysuccess = true;
    $successhlaska = "Vířivka byla úspěšně odstraněna.";
}

$saunyquery = $mysqli->query("SELECT * FROM warehouse_products WHERE active = 'yes' AND customer = 0 ORDER BY brand, fullname");

include VIEW . '/default/header.php';


// QUERY BUILDER

$query = '';
$currentpage = 'sauny';

if (isset($customer) && $customer != '') {

    if ($currentpage == 'sauny') {

        $currentpage .= '?customer=' . $customer;

    } else {

        $currentpage .= '&customer=' . $customer;

    }

    if ($customer == '0') {

        $value = 'w.demand_id = 0';

    } elseif ($customer == '1') {

        $value = 'w.demand_id > 0';

    }

    if ($query == "") {

        $query = 'AND ' . $value;

    } else {

        $query = $query . ' AND ' . $value;

    }

}

if (isset($type) && $type != '') {

    if ($currentpage == 'sauny') {

        $currentpage .= '?type=' . $type;

    } else {

        $currentpage .= '&type=' . $type;

    }

    if ($query == "") {

        $query = 'AND w.status = ' . $type;

    } else {

        $query = $query . ' AND w.status = ' . $type;

    }

} elseif (!isset($type) && !isset($location)) {

    $query = $query . ' AND (status < 3 OR status > 4) ';

}

if (isset($category) && $category != '') {

    if ($currentpage == 'sauny') {

        $currentpage .= '?category=' . $category;

    } else {

        $currentpage .= '&category=' . $category;

    }

    if ($query == "") {

        $query = 'AND w.product = "' . $category . '"';

    } else {

        $query = $query . ' AND w.product = "' . $category . '"';

    }

}

if (isset($brand) && $brand != '') {

    $queryBuilder['brand'] = $brand;
    $sqlQueryBuilder[] .= "p.brand = '" . $brand . "' ";

}

if (isset($location) && $location != '') {

    if ($currentpage == 'sauny') {

        $currentpage .= '?location=' . $location;

    } else {

        $currentpage .= '&location=' . $location;

    }

    if ($query == "") {

        $query = 'AND w.location_id = ' . $location;

    } else {

        $query = $query . ' AND w.location_id = ' . $location;

    }

}

if ($currentpage == 'sauny') {$pageSymbol = '?';} else { $pageSymbol = '&';}
// END QUERY BUILDER



$perpage = 20;
if (isset($search) && $search != "") {

    $parts = explode(" ", $search);
    $last = array_pop($parts);
    $first = implode(" ", $parts);

    if ($first == "") {
        $first = 0;
    }
    if ($last == "") {
        $last = 0;
    }

    $saunas_query = $mysqli->query("SELECT *, w.id as id, DATE_FORMAT(w.loadingdate, '%d. %m. %Y') as dateformated FROM warehouse w, warehouse_products p WHERE w.product = p.connect_name AND w.customer = 0 AND (w.serial_number LIKE '%$search%') order by w.id desc") or die($mysqli->error);

} else {

    $hottubs_max_query = $mysqli->query("SELECT w.id as id FROM warehouse w, warehouse_products p WHERE w.product = p.connect_name AND w.customer = 0 $query") or die($mysqli->error);
    $max = mysqli_num_rows($hottubs_max_query);

    if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {$od = 1;}

    $s_lol = $od - 1;
    $s_pocet = $s_lol * $perpage;
    $pocet_prispevku = $max;

    $saunas_query = $mysqli->query("SELECT *, w.id as id, DATE_FORMAT(w.loadingdate, '%d. %m. %Y') as dateformated FROM warehouse w, warehouse_products p WHERE w.product = p.connect_name AND w.customer = 0 $query order by w.id desc limit " . $s_pocet . "," . $perpage) or die($mysqli->error);

}

function button($data)
{

    global $mysqli;
    global $currentpage;
    global $pageSymbol;
    global $query;

    $param_name = $data['param_name'];
    $this_query = preg_replace('/AND ' . $param_name . ' = .' . $data['param'] . './', '$2', $query);

    if ($data['request_name'] == 'type' || $data['request_name'] == 'location') {

        $this_query = preg_replace('/ AND .status < 3 OR status > 4. /', '', $this_query);

    }

    $thisPage = preg_replace('/&?' . $data['request_name'] . '=[^&]*/', '', $currentpage);

    if ($thisPage == 'sauny') {$pageSymbol = '?';} elseif ($thisPage != 'sauny?') {$pageSymbol = '&';}

    if (isset($data['param']) && $data['param'] == $data['current_param']) {$button = 'btn-primary';} else { $button = 'btn-white';}

    $max_query = $mysqli->query("SELECT w.id as id FROM warehouse w, warehouse_products p WHERE w.product = p.connect_name AND w.customer = 0 AND $param_name = '" . $data['current_param'] . "' $this_query") or die($mysqli->error);
    $max = mysqli_num_rows($max_query);

    if ($param_name != 'w.product' || $max != 0) {

        $button = '<a href="' . $thisPage . $pageSymbol . $data['url'] . '" style="padding: 5px 11px !important;" class="btn ' . $button . '">
						' . $data['name'] . ' (' . $max . ')</a>';

        return $button;
    }
}

if (isset($search) && $search != "") { ?>

<div class="row">
	<div class="col-md-8 col-sm-7">
		<h2>Na hledaný výraz <i><u>"<?= $search ?>"</u></i> odpovídají tyto poptávky:</h2>
	</div>

	<div class="col-md-4 col-sm-5">

		<form method="get" role="form">

			<div class="form-group">
			<div style="margin-bottom: 12px; width: 260px; float:left; margin-left: 10px;margin-right: 4px;"><input id="cheart" value="<?= $search ?>" type="text" name="q" class="form-control typeahead" data-remote="data/autosuggest-demands.php?q=%QUERY" data-template="<div class='thumb-entry'><span class='image'><img src='{{img}}' width=45 height=45 /></span><span class='text' style='width: 75%;'><strong style='overflow: hidden;text-overflow: ellipsis;white-space: nowrap;'>{{value}}</strong><em>{{desc}}</em></span></div>" placeholder="Hledání..." /></div>

				<button style="width: 50px; float:left;" type="submit" class="btn btn-default"><i style=" position: relative; right: 0; top: 0;" class="entypo-search"></i></button>
			</div>

		</form>

	</div>
</div>


	<?php } else {

    $servismaxquery = $mysqli->query('SELECT COUNT(*) AS NumberOfOrders FROM warehouse WHERE customer = 0 AND status <> 4') or die($mysqli->error);
    $servismaxquery = mysqli_fetch_array($servismaxquery);
    $max = $servismaxquery['NumberOfOrders'];

    ?>









<div class="row" style="margin-bottom: 16px;">
	<div class="col-md-2 col-sm-2">
		<h2 style="float: left"><?= $pagetitle ?> (<?= $max ?>)</h2>
	</div>

    <div class="col-md-2">
        <center>
            <ul class="pagination pagination-sm">
                <?php include VIEW . "/default/pagination.php";?>
            </ul>
        </center>
    </div>

	<div class="col-md-4 col-sm-4">

		<form method="get" role="form">

			<div class="form-group">
			<div style="margin-bottom: 12px; width: 260px; float:left; margin-left: 10px;margin-right: 4px;"><input id="cheart" type="text" name="q" class="form-control typeahead" placeholder="Hledání..." /></div>

				<button style="width: 50px; float:left;" type="submit" class="btn btn-default"><i style=" position: relative; right: 0; top: 0;" class="entypo-search"></i></button>
			</div>

		</form>

	</div>


	<?php if ($access_edit) { ?>
	<div class="col-md-2 col-sm-2" style="text-align: right;float:right;">


				<a href="pridat-saunu" style=" margin-right: 14px;" class="btn btn-default btn-icon icon-left btn-lg">
					<i class="entypo-plus"></i>
					Přidat saunu
				</a>

	</div>
	<?php } ?>

</div>


    <script type="text/javascript">
        $(document).ready(function() {
            $(".show-specs").click(function() {

                $(this).parent().find('.hidden-specs').toggle('slow');

            });
        });
    </script>

<div class="col-md-12 well" style="border-color: #ebebeb; background-color: #fbfbfb; padding: 6px; margin-bottom: 12px;">
	<div class="row">
		<div class="col-md-9" style="text-align: left;">

            <div class="btn-group">

                <a href="<?php $thisPage = preg_replace('/&?brand=[^&]*/', '', $currentpage);
                echo $thisPage; ?>" style="padding: 5px 11px !important;"
                   class="btn <?php if (!isset($brand)) {echo 'btn-primary';} else {echo 'btn-white';} ?>">
                    Vše</a><?php

                $button['param'] = '';
                if (isset($brand)) {$button['param'] = $brand;}
                $button['param_name'] = 'p.brand';
                $button['request_name'] = 'brand';

                $brands_query = $mysqli->query("SELECT brand FROM warehouse_products WHERE active = 'yes' AND brand != '' AND customer = 0 GROUP BY brand");
                while ($singleBrand = mysqli_fetch_array($brands_query)) {

                    $button['url'] = 'brand=' . $singleBrand['brand'];
                    $button['name'] = ucfirst($singleBrand['brand']);
                    $button['current_param'] = $singleBrand['brand'];

                    echo button($button);

                }

                ?>
            </div>
            <span style=" border-right: 1px solid #cccccc; padding-left: 9px; margin-right: 12px;"></span>

		<div class="btn-group">

 						<a href="<?php $thisPage = preg_replace('/&?category=[^&]*/', '', $currentpage);
    echo $thisPage;?>"  style="padding: 5px 11px !important;" class="btn <?php if (!isset($category)) {echo 'btn-primary';} else {echo 'btn-white';}?>">
							Vše</a><?php


    $button['param'] = '';
    if(isset($category)){ $button['param'] = $category; }
    $button['param_name'] = 'w.product';
    $button['request_name'] = 'category';

    $hottubs_query = $mysqli->query("SELECT * FROM warehouse_products WHERE active = 'yes' AND customer = 0 ORDER BY brand");

    while ($hottub = mysqli_fetch_array($hottubs_query)) {

        $button['url'] = 'category=' . $hottub['connect_name'];
        $button['name'] = ucfirst($hottub['fullname']);
        $button['current_param'] = $hottub['connect_name'];

        echo button($button);

    }

    ?>
		</div>
	</div>


	<div class="col-sm-3" style=" text-align: right; float: right;">

	<div class="btn-group">
						<a href="<?php $thisPage = preg_replace('/&?customer=[^&]*/', '', $currentpage);
    echo $thisPage;?>"  style="padding: 5px 11px !important;" class="btn <?php if (!isset($customer)) {echo 'btn-primary';} else {echo 'btn-white';}?>">
							Vše</a><?php

    $this_query = preg_replace('/AND demand_id > 0/', '$2', $query);

    $thisPage = preg_replace('/&?customer=[^&]*/', '', $currentpage);
    if ($thisPage == 'virivky') {$pageSymbol = '?';} elseif ($thisPage != 'virivky?') {$pageSymbol = '&';}

    $max_query = $mysqli->query("SELECT w.id as id FROM warehouse w, warehouse_products p WHERE w.product = p.connect_name AND w.customer = 0 AND demand_id = 0 AND w.status != 7 $query") or die($mysqli->error);
    $max = mysqli_num_rows($max_query);

    ?>

						<a href="<?= $thisPage . $pageSymbol . 'customer=0' ?>" style="padding: 5px 11px !important;" class="btn <?php if (isset($customer) && $customer == 0) {echo 'btn-primary';} else {echo 'btn-white';}?>">
							Volné (<?= $max ?>)
						</label></a>


						<?php

    $this_query = preg_replace('/AND w.demand_id = 0/', '$2', $query);

    $max_query = $mysqli->query("SELECT w.id as id FROM warehouse w, warehouse_products p WHERE w.product = p.connect_name AND w.customer = 0 AND demand_id > 0 $this_query") or die($mysqli->error);
    $max = mysqli_num_rows($max_query);

    ?>

						<a href="<?= $thisPage . $pageSymbol . 'customer=1' ?>" style="padding: 5px 11px !important;" class="btn <?php if (isset($customer) && $customer == 1) {echo 'btn-primary';} else {echo 'btn-white';}?>">
							Prodané (<?= $max ?>)
						</label></a>
						</div>
	</div>


</div>
<hr>

<!-- Pager for search results --><div class="row">
	<div class="col-md-12">

		<div class="btn-group" style="text-align: left;">

						<div class="btn-group">
						<a href="<?php $thisPage = preg_replace('/&?type=[^&]*/', '', $currentpage);
    echo $thisPage;?>"  style="padding: 5px 11px !important;" class="btn <?php if (!isset($type)) {echo 'btn-primary';} else {echo 'btn-white';}?>">
							Vše</a><?php

    $allStatus = array(0 => 'Ve výrobě', 1 => 'Na cestě', 2 => 'Na skladě', 3 => 'Na showroomu', 6 => 'Uskladněno', 7 => 'Reklamace', 4 => 'Expedované');

    $button['param'] = '';
    if(isset($type)){ $button['param'] = $type; }
    $button['param_name'] = 'w.status';
    $button['request_name'] = 'type';

    foreach ($allStatus as $singleStatus => $value) {

        $button['url'] = 'type=' . $singleStatus;
        $button['name'] = $value;
        $button['current_param'] = $singleStatus;

        echo button($button);

    }?>
						</div>

					</div>

					<div style="float: right">

							<span style=" border-right: 1px solid #cccccc; padding-left: 9px; margin-right: 12px;"></span>

							<div class="btn-group">

							<a href="<?php $thisPage = preg_replace('/&?location=[^&]*/', '', $currentpage);
    echo $thisPage;?>"  style="padding: 5px 11px !important;" class="btn <?php if (!isset($location)) {echo 'btn-primary';} else {echo 'btn-white';}?>">
							Vše</a><?php

    $location_query = $mysqli->query("SELECT * FROM shops_locations ORDER BY type") or die($mysqli->error);
    while ($single_location = mysqli_fetch_array($location_query)) {


        $button['param'] = '';
        if(isset($location)){ $button['param'] = $location; }
        $button['param_name'] = 'w.location_id';
        $button['request_name'] = 'location';

        $button['url'] = 'location=' . $single_location['id'];
        $button['name'] = $single_location['name'];
        $button['current_param'] = $single_location['id'];

        echo button($button);

    }?>
					</div>
					</div>
		</div>

</div><!-- Footer -->
</div>
<?php }

if (mysqli_num_rows($saunas_query) > 0) {

    while ($sauna = mysqli_fetch_assoc($saunas_query)) {

        sauny($sauna, $access_edit);

    }

} else { ?>
<ul class="cbp_tmtimeline" style=" margin-left: 25px;">
  <li style="margin-top: 80px;">

		<div class="cbp_tmicon">
			<i class="entypo-block" style="line-height: 42px !important;"></i>
		</div>

		<div class="cbp_tmlabel empty" style="padding-top: 9px;">
			<span><a style="font-weight: bold; margin-left: -12px;font-size: 17px;">Bohužel tomuto filtru neodpovídá žádná sauna.</a></span>
		</div>
	</li>
  </ul>
<?php } ?>




<!-- Pager for search results --><div class="row">
	<div class="col-md-12">
		<center><ul class="pagination pagination-sm">
			<?php
include VIEW . "/default/pagination.php";?>
		</ul></center>
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



	</div>



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

<?php include VIEW . '/default/footer.php'; ?>

