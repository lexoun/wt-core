<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";

include INCLUDES . "/googlelogin.php";
include INCLUDES . "/functions.php";

$id = $_REQUEST['id'];

$hottubquery = $mysqli->query('SELECT *, w.id as id, DATE_FORMAT(w.loadingdate, "%d. %m. %Y") as dateformated FROM warehouse w, warehouse_products p WHERE w.product = p.connect_name AND w.id="' . $_REQUEST['id'] . '"') or die($mysqli->error);

if (mysqli_num_rows($hottubquery) > 0) {

    $hottub = mysqli_fetch_assoc($hottubquery);


    if (isset($_REQUEST['action']) && $_REQUEST['action'] == "remove_picture") {

        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/admin/data/images/hottubs/' . $hottub['id'] . '/' . $_REQUEST['picture'])) {

            unlink($_SERVER['DOCUMENT_ROOT'] . '/admin/data/images/hottubs/' . $hottub['id'] . '/' . $_REQUEST['picture']);

        }

        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/admin/data/images/hottubs/' . $hottub['id'] . '/small_' . $_REQUEST['picture'])) {

            unlink($_SERVER['DOCUMENT_ROOT'] . '/admin/data/images/hottubs/' . $hottub['id'] . '/small_' . $_REQUEST['picture']);

        }

        header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/zobrazit-pergolu?id=' . $id . '&remove=success');
        exit;
    }



    if ($hottub['demand_id'] != 0) {

        $demandquery = $mysqli->query("SELECT * FROM demands WHERE id = '" . $hottub['demand_id'] . "'");
        $demand = mysqli_fetch_array($demandquery);

    }

    $pagetitle = "Zobrazit pergolu";

    $bread1 = "Pergoly";
    $abread1 = "pergoly";

    $bread2 = '#' . $hottub['id'] . ' ' . $hottub['brand'] . ' ' . ucfirst($hottub['fullname']);
    $abread2 = "pergoly?id=" . $hottub['id'];

    // todo - variable in config databse
    $link_secret = 'PER_JoifaE';

    if (isset($_REQUEST['action']) && $_REQUEST['action'] == "remove_change") {

        include_once CONTROLLERS . "/product-stock-controller.php";

        $change_query = $mysqli->query("SELECT * FROM demands_products_bridge WHERE warehouse_id = '" . $_REQUEST['requester_id'] . "' AND spec_id = '" . $_REQUEST['spec_id'] . "'") or die($mysqli->error);
        $change = mysqli_fetch_array($change_query);

        if ($change['product_id'] != 0) {

            if ($change['type'] == 'warehouse') {

                // NASKLADNĚNÍ A PŘIDÁNÍ K JINÝM OBJEDNÁVKÁM
                product_update($change['product_id'], $change['variation_id'], $change['location_id'], '1', '0', 'demand_removed', $_REQUEST['requester_id']);

            } elseif ($change['type'] == 'supply') {

                // DODÁVKY PŘIDÁNÍ K JINÝM OBJEDNÁVKÁM
                product_delivered_single($change['product_id'], $change['variation_id'], '1', $change['type_id']);

            }

        }

        $mysqli->query("DELETE FROM demands_products_bridge WHERE warehouse_id = '" . $_REQUEST['requester_id'] . "' AND spec_id = '" . $_REQUEST['spec_id'] . "'") or die($mysqli->error);

        header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/zobrazit-pergolu?id=' . $_REQUEST['id']);

    }




    if (isset($_REQUEST['action']) && $_REQUEST['action'] == "done") {

        include_once CONTROLLERS . "/product-stock-controller.php";

        $change_query = $mysqli->query("SELECT * FROM demands_products_bridge WHERE warehouse_id = '" . $_REQUEST['requester_id'] . "' AND spec_id = '" . $_REQUEST['spec_id'] . "'") or die($mysqli->error);
        $change = mysqli_fetch_array($change_query);

        if ($change['type'] == 'warehouse') {

            $check_query = $mysqli->query("SELECT * FROM warehouse_products_bridge WHERE warehouse_id = '" . $_REQUEST['requester_id'] . "' AND spec_id = '" . $_REQUEST['spec_id'] . "'") or die($mysqli->error);

            if (mysqli_num_rows($check_query) > 0) {

                $check = mysqli_fetch_array($check_query);

                // product updated

                $mysqli->query("UPDATE warehouse_products_bridge SET product_id = '" . $change['product_id'] . "', variation_id = '" . $change['variation_id'] . "', reserved = '1', location_id = '" . $change['location_id'] . "' WHERE id = '" . $check['id'] . "'") or die($mysqli->error);

                // old product added back to warehouse
                product_update($check['product_id'], $check['variation_id'], $check['location_id'], '1', '0', 'demand_spec_changed', $_REQUEST['requester_id']);

            } else {

                // product added

                $mysqli->query("INSERT INTO warehouse_products_bridge (warehouse_id, spec_id, product_id, variation_id, quantity, reserved, location_id) VALUES ('" . $_REQUEST['requester_id'] . "', '" . $_REQUEST['spec_id'] . "', '" . $change['product_id'] . "', '" . $change['variation_id'] . "', '1', '1', '" . $change['location_id'] . "')") or die($mysqli->error);

            }

        } elseif ($change['type'] == 'supply') {

        } elseif ($change['type'] == 'basic') {

            $check_query = $mysqli->query("SELECT * FROM warehouse_products_bridge WHERE warehouse_id = '" . $_REQUEST['requester_id'] . "' AND spec_id = '" . $_REQUEST['spec_id'] . "'") or die($mysqli->error);

            if (mysqli_num_rows($check_query) > 0) {

                $check = mysqli_fetch_array($check_query);

                // old product added back to warehouse
                product_update($check['product_id'], $check['variation_id'], $check['location_id'], '1', '0', 'demand_spec_changed', $_REQUEST['requester_id']);

                $mysqli->query("DELETE FROM warehouse_products_bridge WHERE warehouse_id = '" . $_REQUEST['requester_id'] . "' AND spec_id = '" . $_REQUEST['spec_id'] . "'") or die($mysqli->error);

            }

        } elseif ($change['type'] == 'hottub') {

            $selected_query = $mysqli->query("SELECT * FROM warehouse_products_bridge WHERE warehouse_id = '" . $change['type_id'] . "' AND spec_id = '" . $change['spec_id'] . "'") or die($mysqli->error);
            $selected = mysqli_fetch_array($selected_query);

            $check_query = $mysqli->query("SELECT * FROM warehouse_products_bridge WHERE warehouse_id = '" . $_REQUEST['requester_id'] . "' AND spec_id = '" . $_REQUEST['spec_id'] . "'") or die($mysqli->error);

            // check if current hottub has product

            if (mysqli_num_rows($check_query) > 0) {

                $check = mysqli_fetch_array($check_query);

                // add current product to another hottub

                $mysqli->query("UPDATE warehouse_products_bridge SET warehouse_id = '" . $change['type_id'] . "', location_id = '" . $selected['location_id'] . "' WHERE id = '" . $check['id'] . "'") or die($mysqli->error);

            }

            $spec_query = $mysqli->query("SELECT value FROM warehouse_specs_bridge b WHERE client_id = '" . $_REQUEST['requester_id'] . "' AND specs_id = '" . $_REQUEST['spec_id'] . "'") or die($mysqli->error);
            $spec = mysqli_fetch_array($spec_query);

            // update another hottub's specification

            $mysqli->query("UPDATE warehouse_specs_bridge SET value = '" . $spec['value'] . "' WHERE client_id = '" . $change['type_id'] . "' AND specs_id = '" . $change['spec_id'] . "'") or die($mysqli->error);

            // add product from another hottub to this one

            $mysqli->query("UPDATE warehouse_products_bridge SET warehouse_id = '" . $_REQUEST['requester_id'] . "', location_id = '" . $change['location_id'] . "' WHERE id = '" . $selected['id'] . "'") or die($mysqli->error);

        }

        // specification updated

        if ($change['param_id'] == '1') {$param = 'Ano';} elseif ($change['param_id'] == '0') {$param = 'Ne';} else {

            $param_value_query = $mysqli->query("SELECT option FROM specs_params WHERE id = '" . $change['param_id'] . "'") or die($mysqli->error);
            $param_value = mysqli_fetch_array($param_value_query);

            $param = $param_value['option'];

        }

        $mysqli->query("UPDATE warehouse_specs_bridge SET value = '" . $param . "' WHERE client_id = '" . $_REQUEST['requester_id'] . "' AND specs_id = '" . $_REQUEST['spec_id'] . "'") or die($mysqli->error);

        $mysqli->query("DELETE FROM demands_products_bridge WHERE warehouse_id = '" . $_REQUEST['requester_id'] . "' AND spec_id = '" . $_REQUEST['spec_id'] . "'") or die($mysqli->error);

        header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/zobrazit-pergolu?id=' . $_REQUEST['id']);

    }





    if (isset($_REQUEST['action']) && $_REQUEST['action'] == "products") {

        include_once CONTROLLERS . "/product-stock-controller.php";

        $connected_product = $_REQUEST['d_product_id'];

        $value = $_POST['value'];

        $product_query = $mysqli->query("SELECT * FROM demands_products WHERE id = '" . $connected_product . "'") or die($mysqli->error);
        $product = mysqli_fetch_array($product_query);



        $mysqli->query("INSERT INTO demands_products_bridge (warehouse_id, spec_id, param_id, product_id, variation_id, quantity, type) VALUES ('" . $hottub['id'] . "', '" . $_POST['spec_id'] . "', '" . $_POST['param_id'] . "', '" . $product['product_id'] . "', '" . $product['variation_id'] . "', '1', 'basic')") or die($mysqli->error);



        // přiřazení produktu ze skladu
        if (substr($value, 0, 9) == "warehouse") {

            $warehouse_id = substr($value, 10);

            $instock_query = $mysqli->query("SELECT instock FROM products_stocks WHERE product_id = '" . $product['product_id'] . "' AND variation_id = '" . $product['variation_id'] . "' AND location_id = '" . $warehouse_id . "'");

            $instock = mysqli_fetch_array($instock_query);

            if ($instock['instock'] > 0) {

                $mysqli->query("UPDATE products_stocks SET instock = instock - 1 WHERE product_id = '" . $product['product_id'] . "' AND variation_id = '" . $product['variation_id'] . "' AND location_id = '" . $warehouse_id . "'") or die($mysqli->error);

                $mysqli->query("UPDATE demands_products_bridge SET type = 'warehouse', type_id = '0', location_id = '" . $warehouse_id . "' WHERE warehouse_id = '" . $hottub['id'] . "' AND spec_id = '" . $product['spec_id'] . "' AND product_id = '" . $product['product_id'] . "' AND variation_id = '" . $product['variation_id'] . "'") or die($mysqli->error);

                api_product_update($product['product_id']);





            } else {

                $mysqli->query("UPDATE demands_products_bridge SET type = 'missing', type_id = '0', location_id = '" . $warehouse_id . "' WHERE warehouse_id = '" . $hottub['id'] . "' AND spec_id = '" . $product['spec_id'] . "' AND product_id = '" . $product['product_id'] . "' AND variation_id = '" . $product['variation_id'] . "'") or die($mysqli->error);

            }


        // přiřazení produktu z dodávky
        } elseif (substr($value, 0, 6) == "supply") {

            $supply_id = substr($value, 7);

            $mysqli->query("UPDATE products_supply_bridge SET reserved = reserved + 1 WHERE product_id = '" . $product['product_id'] . "' AND variation_id = '" . $product['variation_id'] . "' AND supply_id = '" . $supply_id . "'") or die($mysqli->error);

            $mysqli->query("UPDATE demands_products_bridge SET type = 'supply', type_id = '" . $supply_id . "' WHERE warehouse_id = '" . $hottub['id'] . "' AND spec_id = '" . $product['spec_id'] . "' AND product_id = '" . $product['product_id'] . "' AND variation_id = '" . $product['variation_id'] . "'") or die($mysqli->error);



        // přiřazení produktu od jiné pergoly
        } elseif (substr($value, 0, 12) == "ware-product") {

            $hottub_id = substr($value, 13);

            $mysqli->query("UPDATE demands_products_bridge SET type = 'hottub', type_id = '" . $hottub_id . "' WHERE warehouse_id = '" . $hottub['id'] . "' AND spec_id = '" . $product['spec_id'] . "' AND product_id = '" . $product['product_id'] . "' AND variation_id = '" . $product['variation_id'] . "'") or die($mysqli->error);

        }



        header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/zobrazit-pergolu?id=' . $hottub['id']);

    }




    $virivkyquery = $mysqli->query("SELECT * FROM warehouse_products WHERE active = 'yes' AND customer = 4 ORDER BY brand");


    include VIEW . '/default/header.php';

    ?>

<script type="text/javascript">

function randomPassword(length) {
    var chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOP1234567890";
    var pass = "";
    for (var x = 0; x < length; x++) {
        var i = Math.floor(Math.random() * chars.length);
        pass += chars.charAt(i);
    }
    return pass;
}

function generate() {
    myform.password.value = randomPassword(myform.length.value);
}

jQuery(document).ready(function($)
{

$('.radio').click(function() {
   if($("input:radio[class='saunaradio']").is(":checked")) {


	$('.virivkens').hide( "slow");
	$('.saunkens').show( "slow");
   }
     if($("input:radio[class='virivkaradio']").is(":checked")) {


   	$('.saunkens').hide( "slow");
$('.virivkens').show( "slow");
   }
});
});


</script>


<script type="text/javascript">
	jQuery(document).ready(function($)
	{

	$('#virivkatype').on('change', function() {
		var id = '<?= $id ?>'
		var selected = this.value;



		$('.virivky_typy').hide( "slow");
		   	$('.params_virivky').hide( "slow");
			$('.virivka_'+selected).show( "slow");
			var selected_type = $('.virivka_'+selected+' .provedeni_'+selected).val();
			if(selected_type != ""){
			$('.params_'+selected_type+'_'+selected).show( "slow");
			}

			$("#current_provedeni").load("load-new.php?id="+id+"&connect_name="+selected);

		});



		$('.provedeni').on('change', function() {


			var id = this.id;
			var selected = this.value;

		   	$('.params_virivky_'+id).hide( "slow");
			$('.params_'+selected+'_'+id).show( "slow");


		});



		$('#show_products').click(function() {

			$( "#products" ).toggle( "slow" );

		});


	});
</script>


	<?php

    if ($hottub['demand_id'] != 0) {

        $demandquery = $mysqli->query("SELECT id, user_name FROM demands WHERE id = '" . $hottub['demand_id'] . "'");
        $demand = mysqli_fetch_array($demandquery);

    }

    if ($hottub['serial_number'] != "") {$name = $hottub['serial_number'];} else { $name = '#' . $hottub['id'];}
    ?>
<div class="col-sm-12" style="padding: 0;">

<div class="col-sm-6" style="padding: 0;">

	<div class="member-entry" style="margin-bottom: 0px;" >
	<?php if (isset($hottub['demand_id']) && $hottub['demand_id'] == 0) { ?><script type="text/javascript">
		jQuery(document).ready(function($)
		{

		$('#priradit-<?= $hottub['id'] ?>').click(function() {

				$('#priradit-<?= $hottub['id'] ?>').hide( "slow");
				$('#prirazeni-<?= $hottub['id'] ?>').show( "slow");

		});

		$('#cancel-<?= $hottub['id'] ?>').click(function() {


				$('#prirazeni-<?= $hottub['id'] ?>').hide( "slow");
				$('#priradit-<?= $hottub['id'] ?>').show( "slow");

		});
		});
</script>
<?php } ?>



	<div class="member-details" style="width: 100% !important;">
		<a class="member-img" style="width: 10%;">
		<img src="https://www.wellnesstrade.cz/admin/data/images/customer/<?= $hottub['product'] ?>.png" width="90px" class="img-rounded" />
		<i class="entypo-forward"></i>
	</a>
    <div style="width: 90%;float: left;">
		<h4 style="float: left; margin-left: 0;">
<?php

    if ($hottub['demand_id'] != 0 && $hottub['status'] != 4) {

        echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-red btn-sm">Prodaná</button>';

    }

    if (isset($hottub['status']) && $hottub['status'] == 0) {

        echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-orange btn-sm">Ve výrobě</button>';

    } elseif (isset($hottub['status']) && $hottub['status'] == 1) {

        echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-blue btn-sm">Na cestě</button><button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-primary btn-sm">očekávané naskladnění <strong><u>' . $hottub['dateformated'] . '</u></strong></button>';

    } elseif (isset($hottub['status']) && $hottub['status'] == 2) {

        echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-green btn-sm">Na skladě</button>';

    } elseif (isset($hottub['status']) && $hottub['status'] == 3) {

        echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-brown btn-sm">Na showroomu</button>';

    } elseif (isset($hottub['status']) && $hottub['status'] == 4) {

        echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-purple btn-sm">Expedovaná</button>';

    } elseif (isset($hottub['status']) && $hottub['status'] == 6) {

        echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-info btn-sm">Uskladněná</button>';

    } elseif (isset($hottub['status']) && $hottub['status'] == 7) {

        echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-info btn-sm">Reklamace</button>';

    }

    if (isset($hottub['status']) && $hottub['status'] != 4) {

        $location_query = $mysqli->query("SELECT name FROM shops_locations WHERE id = '" . $hottub['location_id'] . "'") or die($mysqli->error);
        $location = mysqli_fetch_array($location_query);

        echo '<button style="margin-right: 4px; margin-top: -3px; background-color: #338fd8; border-color: #338fd8;" type="button" class="btn btn-brown btn-sm">' . $location['name'] . '</button>';

    }
    ?> <?= $name ?> | <?= $hottub['brand'] . ' ' . ucfirst($hottub['fullname']) ?></h4>
        <?php


        if ($access_edit) { ?>
            <div style="float:right;margin-top: 0px;">
                <a href="upravit-pergolu?id=<?= $hottub['id'] ?>" class="btn btn-primary btn-sm btn-icon icon-left">
                    <i class="entypo-pencil"></i>
                    Upravit
                </a>
                <a data-id="<?= $hottub['id'] ?>" data-type="hottub" class="toggle-modal-remove btn btn-danger btn-sm btn-icon icon-left">
                    <i class="entypo-cancel"></i>
                    Smazat
                </a>
            </div>
        <?php } ?>
    </div>

        <?php

    if ($hottub['demand_id'] != 0) {

		    ?>
        <h5 style="font-size: 15px; margin-top: 2px; float: left;"><i class="entypo-users"></i> <a href="/admin/pages/demands/zobrazit-poptavku?id=<?= $demand['id'] ?>"  class="text-success"><?php if($hottub['reserved'] == 1){ echo '<span style="color: #cc2423; text-decoration: underline;">Rezervace do <strong>'.date('d. m. Y', strtotime($hottub['reserved_date'])).'</span>'; }?> » <?= $demand['user_name'] ?></a></h5>
		<?php } elseif($hottub['reserved_showroom'] != 0){

        $location_query = $mysqli->query("SELECT * FROM shops_locations WHERE id = '".$hottub['reserved_showroom']."'") or die($mysqli->error);
        $location = mysqli_fetch_array($location_query);
        ?>


		     <h5 style="font-size: 15px; margin-top: 2px; float: left;">
                 <i class="entypo-archive"></i>
                <span style="color: #0077b1;">Rezervace na showroom » <?= $location['name'] ?></span></h5>

		<?php }else{ ?>

        <h5 id="priradit-<?= $hottub['id'] ?>" style="font-size: 15px; margin-top: 2px; float: left;"><i class="entypo-cancel"></i> <a class="text-warning">Volná</a></h5>

					<div id="prirazeni-<?= $hottub['id'] ?>" class="form-group" style="display:none;width: 360px;float:left;">

							<form role="form" method="post" name="myform" action="pergoly?action=demandchange&id=<?= $hottub['id'] ?>">

					<div class="col-sm-6" style="width: 260px;">
						<?php
        $demandsq = $mysqli->query("SELECT user_name, id FROM demands WHERE customer = 4 AND product = '" . $hottub['product'] . "' and status <> 5 and status <> 6") or die($mysqli->error);

        ?>
						<select name="demand" class="select2" data-allow-clear="true" data-placeholder="Vyberte poptávku...">
								<option></option>
								<optgroup label="<?= strtoupper($hottub['product']) ?> poptávky">
									<?php while ($dem = mysqli_fetch_array($demandsq)) {
            $find = $mysqli->query("SELECT id FROM warehouse WHERE demand_id = '" . $dem['id'] . "' AND product = '" . $hottub['product'] . "'");
            if (mysqli_num_rows($find) != 1) { ?><option value="<?= $dem['id'] ?>"><?= $dem['user_name'] ?></option><?php }}?>
								</optgroup>
						</select>

						</div>
						<button style="float: left;margin-left: -9px;    height: 42px;" type="submit" class="btn btn-green"> <i class="entypo-pencil"></i> </button>
						<a id="cancel-<?= $hottub['id'] ?>" style="float: left;margin-left: 4px;    "><button type="button" class="btn btn-white" style="height: 42px;"> <i class="entypo-cancel"></i> </button></a>
					</form>
					</div>

					<?php } ?>



<div class="clear"></div>


        <div class="row info-list" style="margin: 0 -10px 0 -5px;">
            <?php


            $now = date("Y-m-d", strtotime("now"));


            if($hottub['loadingdate'] != '0000-00-00'){

                $dateadd = date("Y-m-d", strtotime($hottub['loadingdate']));

                $delivery_date = date("d. m. y", strtotime($hottub['loadingdate']));

                $date1 = new DateTime($dateadd);
                $date2 = new DateTime($now);
                $interval = $date1->diff($date2);
                $nummero = $interval->days;

                ?>
                <div class="col-sm-12" style="padding-bottom: 10px; border-bottom: 1px solid #eee; color: #000; margin-bottom: 4px;">Termín doručení je <strong><?= $delivery_date ?> (<?= $nummero ?> dnů)</strong>.</div>
                <?php


            }elseif ($hottub['created_date'] != '0000-00-00' && $hottub['status'] == 0) {

                $dateadd = date("Y-m-d", strtotime("+77 days", strtotime($hottub['created_date'])));

                $dateadd2 = date("Y-m-d", strtotime("+42 days", strtotime($hottub['created_date'])));

                $estimated = date("d. m. y", strtotime("+77 days", strtotime($hottub['created_date'])));

                $correction = date("d. m. y", strtotime("+42 days", strtotime($hottub['created_date'])));

                $date1 = new DateTime($dateadd);
                $date2 = new DateTime($now);
                $interval = $date1->diff($date2);
                $nummero = $interval->days;

                $date3 = new DateTime($dateadd2);
                $interval2 = $date3->diff($date2);
                $nummero2 = $interval2->days;

                ?>
                <div class="col-sm-4" style="padding-bottom: 10px; border-bottom: 1px solid #eee; color: #000; margin-bottom: 4px;">Orientační termín doručení je <strong><?= $estimated ?> (<?= $nummero ?> dnů)</strong>. <br>Orientační termín bude upřesněn do <strong><?= $nummero2 ?> dnů</strong>.</div>

            <?php } ?>




            <?php if ($hottub['description'] != "") { ?>

                <div class="alert alert-info" style="margin-right: 20px; margin-bottom: 0;"><i class="entypo-info"></i> <?= $hottub['description'] ?></div>

            <?php } ?>

            <?php if ($hottub['change_description'] != "") {

                $admins_query = $mysqli->query("SELECT user_name FROM demands WHERE id = '".$hottub['change_executor']."'");
                $admin = mysqli_fetch_array($admins_query);

                ?>

                <hr style="float: left;width: 100%;margin-top: 18px; margin-bottom: 3px;">
                <div class="col-sm-12" style="margin-top: 8px; display: inline-block; padding: 0;">
                    <div class="alert alert-info">  <i class="entypo-tools"></i> Změnu provedl: <strong><?= $admin['user_name'] ?></strong> ~ <?= $hottub['change_description'] ?>
                    </div>
                </div>

            <?php } ?>

            <?php

        $oldrank = '';
    if ($hottub['demand_id'] != 0) { 
        
        ?>
		<!-- Details with Icons -->

	<table style="width: 100%; float: left; margin-top: 10px;">
        <?php

        
    $provedeni_query = $mysqli->query("SELECT 
       s.bg_colour, s.name, p.value, p.paid, p.paid_text 
    FROM 
         specs s, warehouse_specs_bridge p 
    WHERE 
          s.id = '5'
      AND p.specs_id = s.id 
      AND p.client_id = '" . $hottub['id'] . "' 
  ORDER BY 
    s.rank ASC") or die($mysqli->error);
    while ($specs = mysqli_fetch_array($provedeni_query)) {
        ?>
        <tr>
                <td
                    style="vertical-align: middle;width: 44%; background-color: <?= $specs['bg_colour'] ?>; color: #000; padding: 4px 5px 2px; border-bottom: 1px solid #fff;border-right: 1px solid #fff;">
                    <strong>
                        <?= $specs['name'] ?></strong></td>
                <td
                    style="vertical-align: middle;width: auto; background-color: <?= $specs['bg_colour'] ?>;  color: #000; padding: 4px 5px 2px; border-bottom: 1px solid #fff; text-align: center;">
                    <?php
                    if($specs['paid']){ ?><i class="fas fa-asterisk" style="float: left; line-height: 15px; color: #d42020;"
                                             data-toggle="tooltip" data-placement="top" data-original-title="<?php

                        if(!empty($specs['paid_text'])){
                        echo $specs['paid_text'];
                        }else{ echo 'bez dodatečných informací'; }

                        ?>"></i><?php }

                    if ($specs['value'] != '') {echo $specs['value'];} else {echo '-';}

                    ?>


                </td>
            </tr>
    <?php }

        if (isset($hottub['customer']) && $hottub['customer'] == 4) {
        
        
            $i = 0;
            $specsquery = $mysqli->query('SELECT * FROM specs WHERE product = 4 AND warehouse_spec = 1 order by rank asc') or die($mysqli->error);
            while ($specs = mysqli_fetch_assoc($specsquery)) {

                $paramsquery = $mysqli->query('SELECT value FROM warehouse_specs_bridge WHERE specs_id = "' . $specs['id'] . '" AND client_id = "' . $hottub['id'] . '"') or die($mysqli->error);
                $params = mysqli_fetch_assoc($paramsquery);

                $specsdemquery = $mysqli->query('SELECT value FROM demands_specs_bridge WHERE specs_id = "' . $specs['id'] . '" AND client_id = "' . $demand['id'] . '"') or die($mysqli->error);
                $demandsspecs = mysqli_fetch_assoc($specsdemquery);

                $i++;

                if (isset($specs['rank']) && $specs['rank'] == 0) { $specs['bg_colour'] = '#ace6ce'; }
                $newrank = $specs['bg_colour'];

                if (isset($oldrank) && isset($newrank) && $newrank != $oldrank) {
                    echo '</table><table style="width: 100%; float: left; margin-top: 10px;">';
                    $i = 1;
                }

                if ($i == 1) { echo '<tr>'; }
                ?>

         <td style="background-color: <?= $specs['bg_colour'] ?>; color: #000; width: 10%; padding: 3px 5px; border-bottom: 1px solid #fff;border-right: 1px solid #fff;"><strong><?= $specs['name'] ?></strong></td>
         <td style="background-color: <?= $specs['bg_colour'] ?>;  color: #000; width: 14%; padding: 3px 5px; border-bottom: 1px solid #fff; border-right: 4px solid #fff;text-align: center;"><?php if (!empty($params['value'])) { echo $params['value']; } else { echo '-'; } ?> <?php

             if (!empty($demandsspecs['value']) && !empty($params['value']) && $demandsspecs['value'] != $params['value'] && $specs['is_demand'] == 1) { ?><i style="color: #d42020;font-size: 16px; margin-left: 1px;margin-top: -3px;position: absolute;" class="entypo-attention" data-toggle="tooltip" data-placement="top" title="" data-original-title="Specifikace u pergoly neodpovídá zvolené specifikaci u poptávky."></i><?php } ?></td>

    <?php

                if ($i % 2 == 0) {echo '</tr><tr>';}

                $oldrank = $specs['bg_colour'];
            }
        }?>
</table>

<?php } else { ?>

		<table style="width: 100%; float: left; margin-top: 10px;">
		<?php if (isset($hottub['customer']) && $hottub['customer'] == 4) {

 $provedeni_query = $mysqli->query("SELECT 
       s.bg_colour, s.name, p.value, p.paid, p.paid_text 
    FROM 
         specs s, warehouse_specs_bridge p 
    WHERE 
          s.id = '5'
      AND s.supplier = 1 
      AND p.specs_id = s.id 
      AND p.client_id = '" . $hottub['id'] . "' 
  ORDER BY 
    s.rank ASC") or die($mysqli->error);
    while ($specs = mysqli_fetch_array($provedeni_query)) {
        ?>
        <tr>
                <td
                    style="vertical-align: middle;width: 44%; background-color: <?= $specs['bg_colour'] ?>; color: #000; padding: 4px 5px 2px; border-bottom: 1px solid #fff;border-right: 1px solid #fff;">
                    <strong>
                        <?= $specs['name'] ?></strong></td>
                <td
                    style="vertical-align: middle;width: auto; background-color: <?= $specs['bg_colour'] ?>;  color: #000; padding: 4px 5px 2px; border-bottom: 1px solid #fff; text-align: center;">
                    <?php
                    if($specs['paid']){ ?><i class="fas fa-asterisk" style="float: left; line-height: 15px; color: #d42020;"
                                             data-toggle="tooltip" data-placement="top" data-original-title="<?php

                        if(!empty($specs['paid_text'])){
                        echo $specs['paid_text'];
                        }else{ echo 'bez dodatečných informací'; }

                        ?>"></i><?php }

                    if ($specs['value'] != '') {echo $specs['value'];} else {echo '-';}

                    ?>


                </td>
            </tr>
    <?php }
    
        $i = 0;
        $specsquery = $mysqli->query('SELECT * FROM specs WHERE product = 4 AND warehouse_spec = 1 order by rank asc') or die($mysqli->error);

        while ($specs = mysqli_fetch_array($specsquery)) {
            $paramsquery = $mysqli->query('SELECT value FROM warehouse_specs_bridge WHERE specs_id = "' . $specs['id'] . '" AND client_id = "' . $hottub['id'] . '"') or die($mysqli->error);
            $params = mysqli_fetch_array($paramsquery);

            $i++;

            if (isset($specs['rank']) && $specs['rank'] == 0) {$specs['bg_colour'] = '#ace6ce';}
            $newrank = $specs['bg_colour'];

            if ($newrank != $oldrank) {echo '</table><table style="width: 100%; float: left; margin-top: 10px;">';
                $i = 1;}

            if ($i == 1) {echo '<tr>';}
            ?>

<td style="background-color: <?= $specs['bg_colour'] ?>; color: #000; width: 25%; padding: 3px 5px; border-bottom: 1px solid #fff;border-right: 1px solid #fff;"><strong><?= $specs['name'] ?></strong></td>
     <td style="background-color: <?= $specs['bg_colour'] ?>;  color: #000; width: 25%; padding: 3px 5px; border-bottom: 1px solid #fff; border-right: 4px solid #fff;text-align: center;"><?php if (!empty($params['value'])) { echo $params['value']; } else { echo '-'; }?></td>

<?php

            if ($i % 2 == 0) {echo '</tr><tr>';}

            $oldrank = $specs['bg_colour'];
        }}?>
</table>

<?php } ?>

		<?/*
    if($access_edit){ ?>
    <hr style="float: left;width: 100%;margin-top: 18px; margin-bottom: 3px;">
    <div class="col-sm-3" style="margin-top: 8px; padding: 0; width: 20%;">
    <i class="entypo-right-open-mini"></i>
    Nákupní cena: <strong><?= number_format($hottub['purchase_price'], 0, ',', ' ') ?>,- Kč</strong>
    </div>
    <div class="col-sm-3" style="margin-top: 8px; padding: 0; width: 20%;">
    <i class="entypo-right-open-mini"></i>
    Prodejní cena: <strong><?= number_format($hottub['sale_price'], 0, ',', ' ') ?>,- Kč</strong>
    </div>
    <div class="col-sm-3" style="margin-top: 8px; padding: 0; width: 20%;">
    <i class="entypo-right-open-mini"></i>
    Doprava: <strong><?= number_format($hottub['delivery_price'], 0, ',', ' ') ?>,- Kč</strong>
    </div>
    <div class="col-sm-3" style="margin-top: 8px; padding: 0; width: 20%;">
    <i class="entypo-right-open-mini"></i>
    Montáž: <strong><?= number_format($hottub['montage_price'], 0, ',', ' ') ?>,- Kč</strong>
    </div>
    <div class="col-sm-3" style="margin-top: 8px; padding: 0; width: 20%;">
    <i class="entypo-right-open-mini"></i>
    Zisk: <strong><?= number_format($hottub['sale_price']+$hottub['delivery_price']+$hottub['montage_price']-$hottub['purchase_price'], 0, ',', ' ') ?>,- Kč</strong>
    </div><?php }*/?>



		</div>




	</div>

</div>
<br>

        <div class="panel panel-primary" data-collapsed="0">

            <div class="panel-heading">
                <div class="panel-title">
                    Aktuálně nainstalované příslušenství
                </div>
            </div>
            <div class="panel-body">
                <?php

                $orders_products_bridge = $mysqli->query("SELECT * FROM warehouse_products_bridge WHERE warehouse_id = '" . $hottub['id'] . "'");

                if (mysqli_num_rows($orders_products_bridge) > 0) { ?>

                <table class="table table-bordered table-hover ">
                    <tbody>

                        <?php

                        while ($bridge = mysqli_fetch_array($orders_products_bridge)) {

                            ?>
                            <tr>
                                <td>
                                <a href="/admin/pages/accessories/zobrazit-prislusenstvi?id=<?= $bridge['product_id'] ?>" target="_blank">

                                    <?php
                                    if ($bridge['variation_id'] != 0) {


                                        $product_query = $mysqli->query("SELECT *, s.id as ajdee FROM products p, products_variations s WHERE p.id = '" . $bridge['product_id'] . "' AND p.id = s.product_id AND s.id = '" . $bridge['variation_id'] . "'");
                                        $product = mysqli_fetch_array($product_query);

                                        $select = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $product['ajdee'] . "'") or die($mysqli->error);
                                        $desc = "";
                                        while ($var = mysqli_fetch_array($select)) {

                                            $desc = $desc . $var['value'] . ' ';

                                        }

                                        $product_title = $product['productname'] . ' – ' . $desc;
                                        ?>

                                        <span style="font-size: 11.5px; float: left;"><i class="entypo-check" style="color: #00a651;"></i> <?= $product_title ?></span>

                                        <?php

                                    } else {



                                        $product_query = $mysqli->query("SELECT * FROM products WHERE id = '" . $bridge['product_id'] . "'") or die($mysqli->error);
                                        $product = mysqli_fetch_array($product_query);

                                        $product_title = $product['productname'];

                                        ?>

                                        <span style=" font-size: 11.5px; float: left;"><i class="entypo-check" style="color: #00a651;"></i> <?= $product_title ?></span>
                                        <?php

                                    }?>
                                </a>
                                </td>
                            </tr>

                            <?php

                        }

                        ?>
                    </tbody>
                </table>
                    <?php

                }

                ?>
            </div>
        </div>
        <div class="panel panel-primary" data-collapsed="0">

            <div class="panel-heading">
                <div class="panel-title">
                    Obrázky k pergole
                </div>
            </div>
            <div class="panel-body">

            <div class="notes-header">

            </div>

                <div id="hottub_pictures" class="lightgallery">

                    <?php
                    $files = array_map('basename', glob($_SERVER['DOCUMENT_ROOT'] . '/admin/data/images/hottubs/' . $hottub['id'] . '/*.{'.extList($image_extensions).',pdf,PDF}', GLOB_BRACE));

                    if (!empty($files)) {
                        foreach ($files as $file) {

                            // skip thumbs
                            if(substr( $file, 0, 6 ) === "small_"){ continue; }

                            if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/admin/data/images/hottubs/" . $hottub['id'] . "/small_" . $file)) {

                                $full_image = "/admin/data/images/hottubs/" . $hottub['id'] . "/" . $file;
                                $small_image = "/admin/data/images/hottubs/" . $hottub['id'] . "/small_" . $file;

                            } else {

                                $full_image = "/admin/data/images/hottubs/" . $hottub['id'] . "/" . $file;
                                $small_image = "/admin/assets/images/pdf.png";

                            }
                            ?>
                            <div class="single-picture" style="width: 16%; margin-right: 3%; display: inline-block;">
                                <a class="remove-picture btn btn-default" style="position: relative; margin-bottom: -30%; margin-left: 90%;" data-picture="<?= basename($file) ?>" data-toggle="tooltip" data-placement="top" title="" data-original-title="Odstranit obrázek">
                                    <i class="entypo-trash"></i>
                                </a>
                                <a class="full" data-src="<?= $full_image ?>" rel="hottub-pictures">
                                    <img src="<?= $small_image ?>" width="100%" style="margin: 20px;" class="img-rounded">
                                </a>
                            </div>

                            <?php

                        }

                    }

                    ?>
                </div>

                <form action="/admin/controllers/uploads/upload-file-hottub?id=<?= $_REQUEST['id'] ?>" class="dropzone-previews dropzone" id="drop-this" style="min-height: 230px; height: 230px;">
                    <div class="fallback">
                        <input name="file" type="file" multiple />
                    </div>
                </form>

            </div>
        </div>
        <div class="panel panel-primary" data-collapsed="0">

            <div class="panel-heading">
                <div class="panel-title">
                    Soubory z kontejneru
                </div>
            </div>
            <div class="panel-body">

                <?php
                $container_products = $mysqli->query("SELECT * FROM containers_products WHERE warehouse_id = '" . $_REQUEST['id'] . "' ORDER BY id desc") or die($mysqli->error);

                if(mysqli_num_rows($container_products) == 0){
                    
                    echo '<div class="alert alert-info">K této pergole nejsou přiřazeny žádné kontejnery.</div>';
                
                }else{

                    $cont_product = mysqli_fetch_array($container_products);

                    $result = glob($_SERVER['DOCUMENT_ROOT'] . '/admin/data/containers/' . $link_secret. '/' . $cont_product['container_id'] . '/' . $cont_product['id'] . '/*.pdf');
                    if (!empty($result)) {

                        foreach ($result as $res) {

                            $str = basename($res);

                            ?>
                            <div style="text-align: right; width: 100%;">
                                <strong style="margin-right: 20px; text-align: left;"><?= $str ?></strong>
                                <a href="https://docs.google.com/viewerng/viewer?url=https://www.wellnesstrade.cz/admin/data/containers/<?= $link_secret. '/'.$cont_product['container_id'] ?>/<?= $cont_product['id'] ?>/<?= $str ?>"
                                target="_blank" class="btn btn-primary" style="padding: 6px 17px;">
                                    <i class="fa fa-file"></i>
                                </a>
                                <a href="https://www.wellnesstrade.cz/admin/data/containers/<?= $link_secret. '/'.$cont_product['container_id'] ?>/<?= $cont_product['id'] ?>/<?= $str ?>"
                                class="btn btn-blue" style="padding-right: 14px; margin-left: 4px;" download>
                                    <i class="entypo-down"></i>
                                </a>
                            </div>
                            <?php

                        }

                    }
                 ?>
                <hr>
                <?php




                $result = glob($_SERVER['DOCUMENT_ROOT'] . '/admin/data/containers/' . $link_secret. '/' . $cont_product['container_id'] . '/' . $cont_product['id'] . '/small_*.*');

                if (!empty($result)) {


                ?>

                <div class="well col-sm-12 lightgallery">

                    <?php

                    foreach ($result as $res) {

                        $str = basename($res);

                        $image = substr($str, 6);

                        ?>

                        <a class="full" data-src="https://www.wellnesstrade.cz/admin/data/containers/<?= $link_secret. '/'.$cont_product['container_id'] ?>/<?= $cont_product['id'] ?>/<?= $image ?>" style="margin: 4px 9px;" rel="warehouse-pictures">
                            <img src="https://www.wellnesstrade.cz/admin/data/containers/<?= $link_secret. '/'.$cont_product['container_id'] ?>/<?= $cont_product['id'] ?>/<?= basename($res) ?>" width="20%">
                        </a>



                    <?php } ?>

                </div>

                <?php }
                }
                ?>




            </div>
        </div>

</div>


<?php

    $get_provedeni = $mysqli->query("SELECT value FROM warehouse_specs_bridge WHERE client_id = '" . $hottub['id'] . "' AND specs_id = 5") or die($mysqli->error);
    $provedeni = mysqli_fetch_array($get_provedeni);

    $get_ids = $mysqli->query("SELECT w.id as id, w.name as name FROM warehouse_products_types w, warehouse_products p WHERE w.warehouse_product_id = p.id AND p.fullname = '" . $hottub['fullname'] . "' AND w.name = '" . $provedeni['value'] . "'") or die($mysqli->error);
    $get_id = mysqli_fetch_array($get_ids);

    $specsquery = $mysqli->query("SELECT w.paid, w.paid_text, s.id, s.name, w.value as warehouse_value, d.value as demand_value, s.demand_category, s.technical 
    FROM specs s INNER JOIN warehouse_products_types_specs wh ON wh.spec_id = s.id AND wh.type_id = '" . $get_id['id'] . "' AND s.technical = 1 AND s.warehouse_spec = 1 
    LEFT JOIN warehouse_specs_bridge w ON w.specs_id = s.id AND w.client_id = '" . $hottub['id'] . "' 
    LEFT JOIN demands_specs_bridge d ON d.specs_id = s.id AND d.client_id = '" . $hottub['demand_id'] . "' AND d.client_id != 0 WHERE d.value != w.value 
    GROUP BY s.id order by s.demand_category asc, s.name asc") or die($mysqli->error);

    ?>
<div class="col-sm-6">
	<div class="panel panel-primary" data-collapsed="0">

								<div class="panel-heading">
									<div class="panel-title">
										<?php if (mysqli_num_rows($specsquery) > 0) { ?><i style="color: #d42020;" class="entypo-attention" data-toggle="tooltip" data-placement="top" title="" data-original-title="Specifikace u pergoly neodpovídá zvolené specifikaci u poptávky."></i><?php } else { ?><i style=" color: #00a651;" class="entypo-check"></i><?php } ?> Nutné změny pro poptávku
									</div>
								</div>

										<div class="panel-body">

<?php

    $technical = false;
    if (mysqli_num_rows($specsquery) > 0) {

        while ($specs = mysqli_fetch_array($specsquery)) {

            if (isset($specs['technical']) && $specs['technical'] == 1 && !$technical) {

                $technical = true;
                ?>


<?php

                // $category_warehouse_done = $specs['demand_category'];

            }

            ?>
<div class="col-sm-12" style="margin-bottom: 6px; padding: 0; color: #000000; padding-left: 22px;text-indent: -15px;">

          <i class="entypo-right-open-mini" style="margin-right: -7px;"></i>
          <strong><?= $specs['name'] ?></strong>: změnit na <strong><?= mb_strtoupper($specs['demand_value']) ?></strong>
    <?php if($specs['paid']){ ?><i class="fas fa-asterisk" style="line-height: 15px; margin-left: 40px; color: #d42020;"></i><small style="font-size: 11px;"><?php
        if(!empty($specs['paid_text'])){
            echo $specs['paid_text'];
        }else{ echo 'bez dodatečných informací'; }

        ?></small><?php } ?>
    </div>
<?php
        }

    } else { ?>

        <div style="margin-bottom: 6px; padding: 0;">

              <i class="entypo-cancel"></i>
              žádné změny
        </div>
<?php
    }

    ?>
 </div>
</div>


<?php

    function current_changes($changes)
    {

        global $mysqli;
        global $hottub;

        if ($changes['warehouse_id'] == $hottub['id']) {

            if ($changes['param_id'] != 0 && $changes['param_id'] != 1) {

                $param_query = $mysqli->query("SELECT * FROM specs_params WHERE id = '" . $changes['param_id'] . "'") or die($mysqli->error);
                $get_param = mysqli_fetch_array($param_query);

                $param = $get_param['option'];

            } else {

                if ($changes['param_id'] == 1) {$param = 'Ano';} else { $param = 'Ne';}

            }

        } else {

            $param_query = $mysqli->query("SELECT * FROM warehouse_specs_bridge WHERE client_id = '" . $changes['warehouse_id'] . "' AND specs_id = '" . $changes['spec_id'] . "'") or die($mysqli->error);
            $get_param = mysqli_fetch_array($param_query);

            $param = $get_param['value'];

        }

        ?>
		<div class="col-sm-12" style="margin-bottom: 6px; padding: 0; color: #000000; padding-left: 22px; float: left;">

              <i class="entypo-right-open-mini" style="margin-right: -7px;"></i>
              <strong><?= $changes['name'] ?></strong>: změnit na <strong><?= mb_strtoupper($param) ?></strong>

              <?php
		$task_content = '';
        $task_content .= $changes['name'] . ': změnit na ' . mb_strtoupper($param);

        $single_query = $mysqli->query("SELECT b.*, l.name FROM demands_products_bridge b LEFT JOIN shops_locations l ON b.location_id = l.id WHERE b.warehouse_id = '" . $changes['warehouse_id'] . "' AND b.spec_id = '" . $changes['specification_id'] . "' AND b.product_id = '" . $changes['product_id'] . "' AND b.variation_id = '" . $changes['variation_id'] . "'") or die($mysqli->error);
        $single = mysqli_fetch_array($single_query);

        if ($single['type'] == 'warehouse') { ?>

                  <a href="/admin/pages/accessories/zobrazit-prislusenstvi?id=<?= $changes['product_id'] ?>" target="_blank" style="color: #00a651; font-weight: bold;">- Rezervováno v <?= $single['name'] ?></a>

                <?php

            $task_content .= ' - Rezervováno v ' . $single['name'];

        } elseif ($single['type'] == 'missing') { ?>

                  <a href="/admin/pages/accessories/zobrazit-prislusenstvi?id=<?= $changes['product_id'] ?>" target="_blank" style="color: #d42020; font-weight: bold;">- Chybějící v <?= $single['name'] ?></a>

                <?php

            $task_content .= ' - Chybějící v ' . $single['name'];

        } elseif ($single['type'] == 'supply') {

            $supply_query = $mysqli->query("SELECT *, DATE_FORMAT(date, '%d. %M %Y') as recieved_date FROM products_supply WHERE id = '" . $single['type_id'] . "'") or die($mysqli->error);
            $supply = mysqli_fetch_array($supply_query);

            ?>

            <a href="/admin/pages/accessories/zobrazit-dodavku?id=<?= $supply['id'] ?>" target="_blank" style="color: #ff5722; font-weight: bold;">- Dodávka #<?= $supply['id'] ?> - doručení <?= $supply['recieved_date'] ?></a>

          <?php

            $task_content .= ' - Dodávka #' . $supply['id'] . ' - doručení ' . $supply['recieved_date'];

        } elseif ($single['type'] == 'hottub') {

            $warehouse_hottub_query = $mysqli->query("SELECT w.*, l.name FROM warehouse w LEFT JOIN shops_locations l ON l.id = w.location_id WHERE w.id = '" . $single['type_id'] . "'") or die($mysqli->error);
            $warehouse_hottub = mysqli_fetch_array($warehouse_hottub_query);

            ?>

            <a href="/admin/pages/warehouse/zobrazit-pergolu?id=<?= $warehouse_hottub['id'] ?>" target="_blank" style="color: #ff5722; font-weight: bold;">- Pergola #<?= $warehouse_hottub['serial_number'] ?> - <?= $warehouse_hottub['name'] ?></a>

          <?php

            $task_content .= ' - Pergola #' . $warehouse_hottub['serial_number'] . ' - ' . $warehouse_hottub['name'];

        }?>

             <div style="float: right;">
   			   <a href="zobrazit-pergolu?id=<?= $hottub['id'] ?>&requester_id=<?= $changes['warehouse_id'] ?>&action=done&spec_id=<?= $changes['specification_id'] ?>" class="btn btn-green"  style="margin-right: 8px;" <?php if ($changes['type'] == 'missing') {echo 'disabled';}?>>
 			 		<i class="entypo-check"></i>
				</a>
				<span style=" border-right: 1px solid #cccccc;"></span>
		       <a href="zobrazit-pergolu?id=<?= $hottub['id'] ?>&requester_id=<?= $changes['warehouse_id'] ?>&action=remove_change&spec_id=<?= $changes['specification_id'] ?>" class="btn btn-default" style="margin-left: 11px;">
		          <i class="entypo-trash"></i>
		       </a>
		   </div>


          </div>

       <?php

        $task_content .= '
';

        return $task_content;

    }

    ?>


<div class="panel panel-primary" data-collapsed="0">

	<div class="panel-heading">
		<div class="panel-title">
			Budoucí plánované změny u pergoly
		</div>

	</div>

	<div class="panel-body">

		<?php

    $task_content = ''; 
    $changes_query = $mysqli->query("SELECT *, b.spec_id as specification_id FROM (specs s, demands_products_bridge b) LEFT JOIN demands_products p ON p.spec_id = s.id AND p.product_id = b.product_id AND p.variation_id = b.variation_id WHERE b.warehouse_id = '" . $hottub['id'] . "' AND b.spec_id = s.id GROUP BY s.id") or die($mysqli->error);

    while ($changes = mysqli_fetch_array($changes_query)) {

        $task_content .= current_changes($changes);

    }

    $changes_query = $mysqli->query("SELECT *, b.spec_id as specification_id FROM (specs s, demands_products_bridge b) LEFT JOIN demands_products p ON p.spec_id = s.id AND p.product_id = b.product_id AND p.variation_id = b.variation_id WHERE b.type_id = '" . $hottub['id'] . "' AND b.spec_id = s.id GROUP BY s.id") or die($mysqli->error);

    while ($changes = mysqli_fetch_array($changes_query)) {

        $task_content .= current_changes($changes);

    }

    if(empty($task_content)){

        echo '<div style="margin-bottom: 6px; padding: 0;">

              <i class="entypo-cancel"></i>
              žádné změny
        </div>';

    }

    ?>

	</div>


</div>


<?php

    function specification_availabilities($hottub, $specs, $params)
    {

        global $mysqli;

        if (isset($params['id']) && $params['id'] == 1) {$param_value = 'Ano';} elseif ($params['id'] == 0) {$param_value = 'Ne';} else { $param_value = $params['option'];}

        ?>

					<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-default btn-md options" data-id="<?= $specs['id'] ?>" data-param="<?= $params['id'] ?>"><?= $param_value ?></button>

      				<div class="availabilities_<?= $specs['id'] ?>_<?= $params['id'] ?>" style="display: none;">



				<?php

        $get_products = $mysqli->query("SELECT *, d.type as stock_type, d.id as dem_id FROM demands_products d, products p WHERE d.type = '" . $hottub['product'] . "' AND d.spec_id = '" . $specs['id'] . "' AND d.product_id = p.id AND d.param_id = '" . $params['id'] . "'") or die($mysqli->error);

        ?>

				      <div class="col-sm-6">
				      <h4><?= $specs['name'] ?></h4>



				 <?php


        $product = mysqli_fetch_array($get_products);

        if(!empty($product)){

        ?>

			      	 <form role="form" method="post" name="myform" id="demand_form" class="form-horizontal form-groups-bordered" enctype="multipart/form-data" action="zobrazit-pergolu?id=<?= $hottub['id'] ?>&action=products&d_product_id=<?= $product['dem_id'] ?>">

			      			<input type="text" style="display: none;" name="spec_id" value="<?= $specs['id'] ?>">
			      			<input type="text" style="display: none;" name="param_id" value="<?= $params['id'] ?>">


				      <h5><a href="/admin/pages/accessories/zobrazit-prislusenstvi?id=<?= $product['product_id'] ?>" target="_blank"><?= $product['productname'] ?></a></h5>



				 <?php
        if (mysqli_num_rows($get_products) > 0) {

            $supply_query = $mysqli->query("SELECT *, DATE_FORMAT(s.date, '%d. %M %Y') as recieved_date FROM products_supply_bridge b, products_supply s WHERE b.product_id = '" . $product['product_id'] . "' AND b.variation_id = '" . $product['variation_id'] . "' AND b.supply_id = s.id AND s.status < 3") or die($mysqli->error);

            $check_db = $mysqli->query("SELECT * FROM demands_products_bridge WHERE demand_id = '" . $hottub['demand_id'] . "' AND spec_id = '" . $specs['id'] . "' AND product_id = '" . $product['product_id'] . "' AND variation_id = '" . $product['variation_id'] . "'") or die($mysqli->error);

            $check = mysqli_fetch_array($check_db);

            ?>

				    <select name="value" class="form-control">

				       <?php

            $warehouse_query = $mysqli->query("SELECT * FROM shops_locations l, products_stocks s WHERE s.product_id = '" . $product['product_id'] . "' AND s.variation_id = '" . $product['variation_id'] . "' AND s.location_id = l.id AND l.type = 'warehouse'") or die($mysqli->error);
            while ($warehouse = mysqli_fetch_array($warehouse_query)) {

                ?>
				       <option value="warehouse-<?= $warehouse['id'] ?>" <?php

                       if (!empty($check) && ($check['type'] == 'warehouse' || $check['type'] == 'missing') && $check['location_id'] == $warehouse['id']) {
                           echo 'selected';} ?>><?php

                           if (!empty($check) && $check['type'] == 'warehouse' && $check['location_id'] == $warehouse['id']) {

                               ?>Rezervováno v <?php echo $warehouse['name'];

                           } elseif (!empty($check) && $check['type'] == 'missing' && $check['location_id'] == $warehouse['id']) {

                               ?>Chybějící v <?= $warehouse['name'] ?><?php

                           } else {

                               ?>Připojit z <?= $warehouse['name'] ?> - <?= $warehouse['instock'] ?> ks<?php

                           }?></option>
				     <?php

            }
            ?>
				       <option>---</option>
				       <?php

            while ($supply = mysqli_fetch_array($supply_query)) {

                ?>

				     <option value="supply-<?= $supply['id'] ?>" <?php if (!empty($check) && $check['type'] == 'supply' && $check['type_id'] == $supply['id']) {echo 'selected';} elseif (!empty($check) && $supply['quantity'] == $supply['reserved']) {echo 'disabled';}?>>Dodávka #<?= $supply['id'] ?> - doručení <?= $supply['recieved_date'] ?></option>


				    <?php
            }

            ?>
				       <option>---</option>
				       <?php

            $warehouse_products_query = $mysqli->query("SELECT w.*, l.name FROM warehouse_specs_bridge s, warehouse w LEFT JOIN shops_locations l ON l.id = w.location_id WHERE w.id = s.client_id AND s.specs_id = '" . $specs['id'] . "' AND s.value = '" . $param_value . "' AND w.status != 4") or die($mysqli->error);

            while ($warehouse_product = mysqli_fetch_array($warehouse_products_query)) {

                ?>

				     <option value="ware-product-<?= $warehouse_product['id'] ?>">Skladová pergola #<?= $warehouse_product['serial_number'] ?> -  <?= $warehouse_product['name'] ?></option>


				    <?php
            }
            ?>

				     </select>


				    <?php

        }?>
				<button type="submit" style="float: left;margin-left: 5px;margin-top:5px;" class="btn btn-green"> <i class="entypo-pencil"></i> Nastavit změnu </button>
				</form>



				<?php } ?>
					</div>


			</div>
					<?php

    }

    ?>




<div class="panel panel-primary" data-collapsed="0">

	<div class="panel-heading">
		<div class="panel-title">
			Naplánování fyzické úpravy pergoly
		</div>

	</div>

	<div class="panel-body">



<script type="text/javascript">
	jQuery(document).ready(function($)
	{

    $('.remove-picture').click(function() {

        $(this).parent(".single-picture").fadeOut();

        var id = $(this).data("id");
        var picture = $(this).data("picture");

        $.get("./zobrazit-pergolu?id=<?= $id ?>&action=remove_picture&picture="+picture);

    });

    $(".specs").click(function(e){

		 var id = $(this).data("id");

	 	 $('.specs').hide("slow");

	 	 $('.values_'+id).show("slow");

	});


	$(".options").click(function(e){

		 var id = $(this).data("id");
		 var param = $(this).data("param");

	 	 $('.options').hide("slow");

	 	 $('.availabilities_'+id+'_'+param).show("slow");

	});
});
</script>







<?php

    $get_provedeni = $mysqli->query("SELECT * FROM warehouse_specs_bridge WHERE client_id = '" . $hottub['id'] . "' AND specs_id = 5") or die($mysqli->error);
    $provedeni = mysqli_fetch_array($get_provedeni);

    $get_ids = $mysqli->query("SELECT w.* FROM warehouse_products_types w, warehouse_products p WHERE w.warehouse_product_id = p.id AND p.fullname = '" . $hottub['fullname'] . "' AND w.name = '" . $provedeni['value'] . "'") or die($mysqli->error);
    $typ = mysqli_fetch_array($get_ids);

    $specs_query = $mysqli->query("SELECT s.* FROM (specs s, demands_products d) INNER JOIN warehouse_products_types_specs wh ON wh.spec_id = s.id AND wh.type_id = '" . $typ['id'] . "' AND s.technical = 1 AND s.warehouse_spec = 1 LEFT JOIN warehouse_specs_bridge w ON w.specs_id = s.id AND w.client_id = '" . $hottub['id'] . "' WHERE d.type = '" . $hottub['product'] . "' AND d.spec_id = s.id GROUP BY s.id order by s.demand_category asc, s.name asc") or die($mysqli->error);

    while ($specs = mysqli_fetch_array($specs_query)) {

        $check_for_change = $mysqli->query("SELECT * FROM demands_products_bridge WHERE (warehouse_id = '" . $hottub['id'] . "' OR type_id = '" . $hottub['id'] . "') AND spec_id = '" . $specs['id'] . "'") or die($mysqli->error);

        if (mysqli_num_rows($check_for_change) != 0) {continue;}

        ?>
<button style="margin-right: 4px; margin-bottom: 10px; width: 18.9%;" type="button" class="btn btn-info btn-md specs" data-id="<?= $specs['id'] ?>"><?= $specs['name'] ?></button>
<?php

        $param_spec_query = $mysqli->query("SELECT * FROM warehouse_specs_bridge WHERE specs_id = '" . $specs['id'] . "' and client_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);
        $cpars = mysqli_fetch_array($param_spec_query);

        // VALUE U POPTÁVKY K DANÉ SPECIFIKACI

        if (isset($specs['type']) && $specs['type'] == 1) {

            $paramsquery = $mysqli->query("SELECT *, p.id as id FROM specs_params p, warehouse_products_types_specs w  WHERE p.spec_id = '" . $specs['id'] . "' AND w.spec_param_id = p.id AND w.type_id = '" . $typ['id'] . "' AND p.option != '" . $cpars['value'] . "' GROUP by p.id") or die($mysqli->error);

        } else {

            if ($cpars['value'] == 'Ano') {$param_value = '1';} else { $param_value = '0';}

            $paramsquery = $mysqli->query("SELECT *, spec_param_id as id FROM warehouse_products_types_specs WHERE spec_id = '" . $specs['id'] . "' AND type_id = '" . $typ['id'] . "' AND spec_param_id != '" . $param_value . "' order by spec_param_id desc") or die($mysqli->error);

        }

        // VŠECHNY VALUES U PROVEDENÍ PERGOLY

        ?>
					 		<div class="values_<?= $specs['id'] ?>" style="display: none;">
						<?php

        while ($params = mysqli_fetch_assoc($paramsquery)) {

            specification_availabilities($hottub, $specs, $params);

        }?>


							</div>


						<?php

    }

    ?>

	</div>


</div>




	<div class="panel panel-primary" data-collapsed="0">

			<div class="panel-heading">
				<div class="panel-title">
					Úkoly pro provedení úprav
				</div>

			</div>

			<div class="panel-body">
<?php

    $task_query = $mysqli->query("SELECT *, DATE_FORMAT(date, '%d. %m. %Y') as dateformated, DATE_FORMAT(due, '%d. %m. %Y') as dueformated FROM tasks WHERE warehouse_id = '" . $hottub['id'] . "' AND status != 3") or die($mysqli->error);

    if (mysqli_num_rows($task_query) > 0) {



        while($task = mysqli_fetch_array($task_query)){

        task($task, $client['avatar'], $access_edit, 'pages/warehouse/zobrazit-pergolu?id=' . $hottub['id']);

        }

    } else { ?>
        <div style="margin-bottom: 6px; padding: 0;">

            <i class="entypo-cancel"></i>
            žádná zadaná úprava
        </div>

    <?php

       }?>

			</div>
	</div>


	<div class="panel panel-primary" data-collapsed="0">

			<div class="panel-heading">
				<div class="panel-title">
					Přidat úkol pro provedení úprav
				</div>

			</div>

			<div class="panel-body">
<?php

if(!empty($task_content)){

        $demquery = $mysqli->query('SELECT id, user_name FROM demands') or die($mysqli->error);
        ?>
<div class="well" style="width: 100%; float:inherit; margin: 0 auto 50px;">
		<form autocomplete="off" id="taskform" role="form" method="post" enctype='multipart/form-data' action="/admin/controllers/task-controller?task=add&redirect=pages/warehouse/zobrazit-pergolu&redirectid=<?= $hottub['id'] ?>">

		<div class="form-group" style="float:left; width: 100%;">
		<input type="text" style="width: 64%; float: left; margin-right: 2%; margin-bottom: 8px;" name="title" placeholder="Název úkolu" class="form-control" id="field-1" value="Úprava pergoly <?= $name ?>" required>

      <input id="datum3" type="text" style="width: 22%; float: left; margin-bottom: 6px;" name="datum" class="form-control datepicker" data-format="yyyy-mm-dd" placeholder="Datum provedení" required>
      <input type="text" style="width: 12%" class="form-control timepicker" name="time" data-template="dropdown" data-show-seconds="false" data-default-time="00-00" data-show-meridian="false" data-minute-step="5" placeholder="Čas"/>
				</div>

	<input type="text" style="display: none;" name="warehouse_id" value="<?= $hottub['id'] ?>">
	<input type="text" style="display: none;" name="demandus" value="<?= $hottub['demand_id'] ?>">

            <div class="form-group well admins_well"
                 style="background-color: #FFFFFF; padding: 12px 0px 7px; float: left; width: 100%; ">

                <h4
                        style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">
                    Proveditelé</h4>

                <?php

                $adminsquery = $mysqli->query("SELECT id, user_name FROM demands WHERE role != 'client' AND active = 1 AND active = 1");

                while ($admins = mysqli_fetch_array($adminsquery)) { ?>

                    <div class="col-sm-3">

                        <input id="admin-<?= $admins['id'] ?>-event-performer" name="performer[]"
                               value="<?= $admins['id'] ?>" type="checkbox">
                        <label for="admin-<?= $admins['id'] ?>-event-performer"
                               style="padding-left: 4px; cursor: pointer;"><?= $admins['user_name'] ?></label>

                    </div>

                <?php } ?>


            </div>
            <div class="form-group well admins_well"
                 style="background-color: #FFFFFF; padding: 12px 0px 7px; float: left; width: 100%;">

                <h4
                        style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">
                    Informovaní</h4>

                <?php mysqli_data_seek( $adminsquery, 0 );

                while($admins = mysqli_fetch_array($adminsquery)){ ?>

                    <div class="col-sm-3">

                        <input id="admin-<?= $admins['id'] ?>-event-observer" name="observer[]"
                               value="<?= $admins['id'] ?>" type="checkbox" <?php if($client['id'] == $admins['id']){ echo 'checked';}?>>
                        <label for="admin-<?= $admins['id'] ?>-event-observer"
                               style="padding-left: 4px; cursor: pointer;">
                            <?= $admins['user_name'] ?></label>

                    </div>

                <?php } ?>

            </div>


 <div class="form-group">
			<textarea class="form-control autogrow" name="text" placeholder="Zadání úkolu." style="overflow: hidden; margin-bottom: 8px; float: left; margin-top: 8px; word-wrap: break-word; resize: horizontal; height: 80px;"><?= $task_content ?></textarea>
		</div>
			<span class="button-demo"><button type="submit" data-type="zoom-in" class="ladda-button btn btn-primary" style="width: 100%; margin-top: 12px; height: 71px; margin-bottom: 0;  font-size: 17px;">Naplánovat změnu</button></span>
		</form>
</div>

<?php }else{

    echo '<div style="margin-bottom: 6px; padding: 0;">

            <i class="entypo-cancel"></i>
            žádná plánovaná úprava
        </div>';
} ?>

			</div>
	</div>




    <div class="panel panel-primary" data-collapsed="0">

			<div class="panel-heading">
				<div class="panel-title">
					Historie zadaných úprav
				</div>

			</div>

			<div class="panel-body">
<?php

    $task_query = $mysqli->query("SELECT *, DATE_FORMAT(date, '%d. %m. %Y') as dateformated, DATE_FORMAT(due, '%d. %m. %Y') as dueformated FROM tasks WHERE warehouse_id = '" . $hottub['id'] . "'  AND status != 0 && status != 1") or die($mysqli->error);

    if(mysqli_num_rows($task_query) > 0) {

        while ($task = mysqli_fetch_array($task_query)) {

            task($task, $client['avatar'], $access_edit, 'pages/warehouse/zobrazit-pergolu?id=' . $hottub['id']);

        }

    }else{

        echo  '<div style="margin-bottom: 6px; padding: 0;">

            <i class="entypo-cancel"></i>
            žádná historická úprava
        </div>';


    }
        ?>

            </div>
    </div>

</div>

<div style="clear: both;"></div>


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

        <script type="text/javascript">
            $(document).ready(function() {

                $('.lightgallery').lightGallery({
                    selector: 'a.full'
                });

                Dropzone.autoDiscover = false;


                const myDropzone = new Dropzone('form#drop-this', {

                    acceptedFiles: 'image/*,application/pdf',


                });

                myDropzone.on("complete", function (file) {

                    $("#hottub_pictures").load(location.href + " #hottub_pictures");

                });

            });
        </script>

    <?php include VIEW . '/default/footer.php'; ?>



<?php

} else {

    include INCLUDES . "/404.php";

}?>
