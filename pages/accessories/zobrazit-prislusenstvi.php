<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";
include_once INCLUDES . "/accessories-functions.php";

/**
 * @var array $mysqli
 * @var array $client
 * @var string $start
 */


$id = $_REQUEST['id'];

$productquery = $mysqli->query("SELECT * FROM products WHERE id = '" . $id . "'") or die($mysqli->error);

if (mysqli_num_rows($productquery) > 0) {

    $product = mysqli_fetch_assoc($productquery);

    $spesl = " - příslušenství";
    $pagetitle = $product['productname'];

    $bread1 = "Editace příslušenství";
    $abread1 = "editace-prislusenstvi";

    if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'stock_transfer') {

        if ($_POST['quantity'] > 0 && $_POST['original_location'] != '' && $_POST['final_location'] != '') {

            $quantity = $_POST['quantity'];

            $original_location = $_POST['original_location'];
            $final_location = $_POST['final_location'];

            if (!isset($_REQUEST['variation_id']) || $_REQUEST['variation_id'] == '') {

                $_REQUEST['variation_id'] = 0;

                $max_quantity_query = $mysqli->query("SELECT s.instock FROM products_stocks s WHERE s.location_id = '" . $original_location . "' AND s.product_id = '" . $id . "'");
                $max_quantity = mysqli_fetch_assoc($max_quantity_query);

                if ($quantity > $max_quantity['instock']) {
                    $quantity = $max_quantity['instock'];
                }

                $mysqli->query("UPDATE products_stocks SET instock = instock - $quantity WHERE product_id = '$id' AND location_id IN (SELECT id as location_id FROM shops_locations WHERE id = '$original_location')") or die($mysqli->error);

                $mysqli->query("UPDATE products_stocks SET instock = instock + $quantity WHERE product_id = '$id' AND location_id IN (SELECT id as location_id FROM shops_locations WHERE id = '$final_location')") or die($mysqli->error);

            } else {

                $max_quantity_query = $mysqli->query("SELECT s.instock FROM products_stocks s WHERE s.location_id = '" . $original_location . "' AND s.product_id = '" . $id . "' AND s.variation_id = '" . $_REQUEST['variation_id'] . "'");
                $max_quantity = mysqli_fetch_assoc($max_quantity_query);

                if ($quantity > $max_quantity['instock']) {$quantity = $max_quantity['instock'];}

                $mysqli->query("UPDATE products_stocks SET instock = instock - $quantity WHERE product_id = '$id' AND variation_id = '" . $_REQUEST['variation_id'] . "' AND location_id IN (SELECT id as location_id FROM shops_locations WHERE id = '$original_location')") or die($mysqli->error);

                $mysqli->query("UPDATE products_stocks SET instock = instock + $quantity WHERE product_id = '$id' AND variation_id = '" . $_REQUEST['variation_id'] . "' AND location_id IN (SELECT id as location_id FROM shops_locations WHERE id = '$final_location')") or die($mysqli->error);

            }

        }

        Header("Location:https://www.wellnesstrade.cz/admin/pages/accessories/zobrazit-prislusenstvi?id=" . $product['id'] . "&success=stock_transfer");
        exit;

    }

    if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'tostock') {

        if (!isset($_REQUEST['variation_id']) || $_REQUEST['variation_id'] == '') {

            $_REQUEST['variation_id'] = 0;

        }

        include CONTROLLERS . "/product-stock-controller.php";

        if (isset($_REQUEST['stock']) && $_REQUEST['stock'] != "") {

            $quantity = $_REQUEST['quant'];

            if ($quantity != 0) {

                product_update($id, $_REQUEST['variation_id'], $_REQUEST['stock'], $quantity, $client['id'], 'to_stock', 0);
                $reserved_quantity = $_REQUEST['quant'] - $quantity;

            }

        } elseif ($_POST['quant'] != 0 || $_POST['quant'] != "") {

            $quantity = $_POST['quant'];

            if ($quantity != 0) {

                product_update($id, $_REQUEST['variation_id'], $_POST['choosed_stock'], $quantity, $client['id'], 'to_stock', 0);
                $reserved_quantity = $_REQUEST['quant'] - $quantity;

            }

        }


        if (isset($_REQUEST['link']) && $_REQUEST['link'] != "") {

            Header("Location:https://www.wellnesstrade.cz/admin/pages/accessories/editace-prislusenstvi?od=" . $_REQUEST['link'] . "&success=to_stock&to_stock_quantity=" . $quantity . "&reserved_quantity=" . $reserved_quantity);

        } else {

            Header("Location:https://www.wellnesstrade.cz/admin/pages/accessories/zobrazit-prislusenstvi?id=" . $product['id'] . "&success=to_stock&to_stock_quantity=" . $quantity . "&reserved_quantity=" . $reserved_quantity);

        }
        exit;

    }

    include VIEW . '/default/header.php';

    ?>

    <style>

        .panel i.fas { color: #21a9e1; }

        .popover {
            min-width: fit-content !important;
            max-width: 100%;
        }

    </style>

<script type="text/javascript">
    jQuery(document).ready(function ($) {

        $('.radio').click(function () {

            if ($("input:radio[class='saunaradio']").is(":checked")) {
                $('.virivkens').hide("slow");
                $('.saunkens').show("slow");
            }

            if ($("input:radio[class='virivkaradio']").is(":checked")) {
                $('.saunkens').hide("slow");
                $('.virivkens').show("slow");
            }

        });

        let cloneCount = 0;
        $('#duplicatevirivka').click(function () {

            cloneCount = cloneCount + 1;

            let $myInput = jQuery('.first-name');
            $('#virdup').clone().attr('id', 'virdup' + cloneCount).insertAfter('[id^=virdup]:last');
            $('#virdup' + cloneCount).find('#virivkadup').attr('name', 'zbozickovirivka' + cloneCount);
            $('#virdup' + cloneCount).find('#field-2').attr('name', 'cenickavirivka' + cloneCount);


        });

        let cloneCount2 = 0;
        $('#duplicatesauna').click(function () {

            cloneCount2 = cloneCount2 + 1;
            $('#saundup').clone().attr('id', 'saundup' + cloneCount2).insertAfter('[id^=saundup]:last');
            $('#saundup' + cloneCount2).find('#saunadup').attr('name', 'zbozickosauna' + cloneCount2);
            $('#saundup' + cloneCount2).find('#field-2').attr('name', 'cenickasauna' + cloneCount2);

        });

    });
</script>

<div class="col-sm-6" style="padding-left: 0;">


	<div class="panel panel-primary" data-collapsed="0">

						<div class="panel-body">
<div class="invoice">

	<div class="row">

		<div class="col-sm-12 invoice-left">


			<h3 style=" margin-bottom: 16px;"><span style="font-size: 16px;"><?= $product['productname'] ?></span> <?php if ($product['spare_part'] == 1) { ?><small style="float: right; font-weight: bold;"><i class="entypo-tools"></i> Náhradní díl</small><?php } ?> <?php
                if (isset($product['availability']) && $product['availability'] == 2) { ?>
                <small>~ na objednávku</small>
                <?php } else if (isset($product['availability']) && $product['availability'] == 3) { ?>
                 <small>~ skryto</small>
                <?php } ?></h3>

				<p style="font-size: 11px;"><?php if ($product['short_description'] != "") {echo $product['short_description'];} else {echo 'žádný krátký popisek';}?></p>

            <hr>
            <p style="font-size: 11px;"><i>Interní poznámka:</i> <?php
                    if ($product['internal_note'] != "") {

                        echo $product['internal_note'];

                    } else {

                        echo 'žádná';

                    }?>
                </p>

            <hr>
            <p><strong>EAN:</strong> <?php if ($product['ean'] != "") {echo $product['ean'];} else {echo 'žádný EAN';}?> ~ <strong>SKU:</strong> <?= $product['code'] ?></p>
            <hr>



            <div class="row" style="font-size: 12px;">

                <div class="col-sm-4"><i class="fas fa-truck"></i> <strong>Doba doručení:</strong> <?php if ($product['delivery_time'] != "" && $product['delivery_time'] != 0) {echo $product['delivery_time'] . ' dní';} else {echo 'žádná';}?></div>



            <?php

            $supplier_query = $mysqli->query("SELECT m.manufacturer FROM products_suppliers s, products_manufacturers m WHERE s.product_id = '".$product['id']."' AND s.supplier = m.id") or die($mysqli->error);

            $i = 0;
            while($supplier = mysqli_fetch_assoc($supplier_query)){

                $i++;
                ?>

                <div class="col-sm-4"><i class="fas fa-boxes"></i> Dodavatel #<?= $i ?>: <strong><?= $supplier['manufacturer'] ?></strong></div>
                <?php
            }

            ?>
            </div>

		<hr>


<div class="row">

		<div class="col-sm-12" style="font-size: 12px;  padding: 0;">

		<?php if (isset($product['type']) && $product['type'] == 'simple') { ?>
			<div class="col-sm-4"><i class="fas fa-coins"></i> Cena: <strong style="text-decoration: underline;color: #000;"><?php if ($product['price'] != "" && $product['price'] != 0) {echo $product['price'] . ' Kč';} else {echo 'žádná zadaná cena';}?></strong></div>
			<div class="col-sm-4">Nákupní cena: <strong><?= number_format($product['purchase_price'], 0, ',', ' ') ?> Kč</strong></div>
			<div class="col-sm-4">Velkoobchodní cena: <strong><?= number_format($product['wholesale_price'], 0, ',', ' ') ?> Kč</strong></div>
		<?php } ?>

            <hr style="float: left; width: 100%;">
			<div class="col-sm-3"><i class="fas fa-weight-hanging"></i> Váha: <?php strong_echo($product['weight']); ?> kg</div>
			<div class="col-sm-3"><i class="fas fa-ruler-horizontal"></i> Délka: <?php strong_echo($product['length']); ?> cm</div>
			<div class="col-sm-3"><i class="fas fa-ruler-combined"></i> Šířka: <?php strong_echo($product['width']); ?> cm</div>
			<div class="col-sm-3"><i class="fas fa-ruler-vertical"></i> Výška: <?php strong_echo($product['height']); ?> cm</div>


		</div>
		</div>

		</div>
		<hr>
		<div class="col-sm-12 invoice-left">
        <?php
        $path = PRODUCT_IMAGE_PATH.'/thumbnail/' . $product['seourl'] . '.jpg';
        if(file_exists($path)){
            $imagePath = '/data/stores/images/thumbnail/'.$product['seourl'].'.jpg';
        }else{
            $imagePath = '/data/assets/no-image-7.jpg';
        }
        echo '<img src="'.$imagePath.'" width="100" style="float: left; margin-right:8px; margin-bottom: 8px; border: 1px solid #ebebeb; border-bottom: 3px double #e2e2e5">';

    $files = array_map('basename', glob(PRODUCT_IMAGE_PATH.'/thumbnail/' . $product['seourl'] . '_{,[1-9]}[0-9].jpg', GLOB_BRACE));
    if (!empty($files)) {
        foreach ($files as $file) {

            if(file_exists($path)){
                $imagePath = '/data/stores/images/thumbnail/'.$file;
            }
            echo '<img src="'.$imagePath.'" width="100" style="float: left; margin-right:8px; margin-bottom: 8px; border: 1px solid #ebebeb;">';

        }
    }
    ?>

		</div>
	</div>



</div>


</div>


<?php if($product['type'] == 'simple'){ ?>
        <div class="col-sm-12" style="padding:0">
            <hr style="margin: 10px 0;">
            <div class="panel-heading">
                <div class="panel-title">
                    <i class="fas fa-cubes" style="font-size: 18px; margin-right: 4px;"></i> <strong style="font-weight: 600; color: #555;">Sklad</strong>
                </div>

                <div class="panel-options">
                    <a data-id="<?= $product['id'] ?>" class="toggle-modal-stock-transfer btn btn-info btn-sm btn-icon icon-left" style="color: #fff; margin-top: 6px; line-height: normal; padding: 8px 10px 8px 36px;">
                        <i class="entypo-box" style="padding: 5px 6px;"></i> Přeskladnit
                    </a>
                    &nbsp;
                    <a data-id="<?= $product['id'] ?>" class="toggle-modal-stock btn btn-primary btn-sm btn-icon icon-left" style="color: #fff; margin-top: 6px; line-height: normal; padding: 8px 10px 8px 36px;">
                        <i class="entypo-box" style="padding: 5px 6px;"></i> Naskladnit
                    </a>
                </div>
            </div>
            <table class="table table-bordered table-hover ">
                <thead>
                <tr>
                    <td class="text-center">Lokace</td>
                    <td class="text-center">Skladem</td>
                    <td class="text-center">Rezervace</td>
                    <td class="text-center">Chybí</td>
                    <td class="text-center">Na cestě</td>

                </tr>
                </thead>
                <tbody>
                <?php
                $locations_query = $mysqli->query("SELECT * FROM shops_locations l LEFT JOIN products_stocks s ON s.location_id = l.id AND s.product_id = '" . $product['id'] . "' ORDER BY type ASC");

                while ($location = mysqli_fetch_array($locations_query)) {

                    ?>
                    <tr>
                        <td><?= $location['name'] ?> <small>- min. <?= $location['min_stock'] ?></small></td>

                        <!-- skladem -->
                        <td class="text-center">
                                <span style="font-size: 12px;color:#0bb668; font-weight: bold; "><?= $location['instock'] ?> ks</span>
                       </td>

                        <!-- rezervace -->
                        <td class="text-center"> <?php

                            $orderquery = $mysqli->query("SELECT r.reserved, o.id FROM orders_products_bridge r, orders o WHERE o.id = r.aggregate_id AND o.order_status < '3' AND r.product_id = '" . $product['id'] . "' AND r.location_id = '" . $location['id'] . "' AND r.aggregate_type = 'order'") or die($mysqli->error);
                            $count = 0;
                            while ($orderreserved = mysqli_fetch_array($orderquery)) {
                                $count += $orderreserved['reserved'];
                            }

                            $service_query = $mysqli->query("SELECT r.reserved, r.quantity, o.id FROM services_products_bridge r, services o WHERE o.id = r.aggregate_id AND r.reserved <> r.quantity AND o.state != 'finished' AND o.state != 'canceled' AND r.product_id = '" . $product['id'] . "' AND r.location_id = '" . $location['id'] . "'") or die($mysqli->error);
                            while ($service_reserved = mysqli_fetch_array($service_query)) {
                                $count += $service_reserved['reserved'];
                            }

                            $demands_query = $mysqli->query("SELECT b.quantity, w.id FROM demands_products_bridge b, warehouse w WHERE w.id = b.warehouse_id AND w.status <> '4' AND b.product_id = '" . $product['id'] . "' AND b.location_id = '" . $location['id'] . "' AND type = 'warehouse'") or die($mysqli->error);
                            while ($demands_reserved = mysqli_fetch_array($demands_query)) {
                                $count += $demands_reserved['quantity'];
                            }

                            $finalreserved = $count;

                            if ($finalreserved > 0) {


                                $tooltip_title = $location['name']."&nbsp;&nbsp;<i class='fas fa-long-arrow-alt-right'></i>&nbsp;&nbsp;<strong>rezervováno</strong>";
                                $toltip_content = '';

                                if (mysqli_num_rows($orderquery) != 0) {

                                    mysqli_data_seek($orderquery, 0);

                                    while ($orderreserved = mysqli_fetch_array($orderquery)) {

                                        $toltip_content .= "<p><i class='fas fa-angle-right'></i> <a href='/admin/pages/orders/zobrazit-objednavku?id=". $orderreserved['id'] ."' target='_blank'>Objednávka #". $orderreserved['id']."</a>: <strong>".$orderreserved['reserved']." ks</strong></p>";

                                        
                                    }

                                }

                                if (mysqli_num_rows($service_query) != 0) {

                                    mysqli_data_seek($service_query, 0);

                                    while ($service_reserved = mysqli_fetch_array($service_query)) {

                                        $toltip_content .= "<p><i class='fas fa-angle-right'></i> <a href='/admin/pages/services/zobrazit-servis?id=". $service_reserved['id'] ."' target='_blank'>Servis #". $service_reserved['id']."</a>: <strong>".$service_reserved['reserved']." ks</strong></p>";

                                    }

                                }

                                if (mysqli_num_rows($demands_query) != 0) {

                                    mysqli_data_seek($demands_query, 0);

                                    while ($demands_reserved = mysqli_fetch_array($demands_query)) {

                                        $toltip_content .= "<p><i class='fas fa-angle-right'></i> <a href='/admin/pages/warehouse/zobrazit-virivku?id=". $demands_reserved['id'] ."' target='_blank'>Vířivka #". $demands_reserved['id']."</a>: <strong>".$demands_reserved['quantity']." ks</strong></p>";


                                    }

                                }
                                

                                ?>
                                <span style="font-size: 12px; color:#000000; font-weight: bold; cursor: pointer;" data-toggle="popover" data-trigger="click" data-html="true" data-placement="top" data-content="<?= $toltip_content ?>" data-original-title="<?= $tooltip_title ?>"><?= $finalreserved ?> ks <i class="fas fa-info-circle" style="margin-left: 2px"></i></span>
                                <?php

                            } else { ?>

                                <span style="font-size: 12px;color:#000000;"><i class="entypo-block"></i></span>
                            <?php } ?></td>



                        <!-- chybí -->
                        <td class="text-center"><?php
                            $orderquery = $mysqli->query("SELECT r.reserved, r.quantity, o.id FROM orders_products_bridge r, orders o WHERE o.id = r.aggregate_id AND r.reserved <> r.quantity AND o.order_status < '3' AND r.product_id = '" . $product['id'] . "' AND r.location_id = '" . $location['id'] . "'  AND r.aggregate_type = 'order'") or die($mysqli->error);

                            $count = 0;
                            while ($ordermissing = mysqli_fetch_array($orderquery)) {
                                $subtotal = $ordermissing['quantity'] - $ordermissing['reserved'];
                                $count += $subtotal;
                            }

                            $service_query = $mysqli->query("SELECT r.reserved, r.quantity, o.id FROM services_products_bridge r, services o WHERE o.id = r.aggregate_id AND r.reserved <> r.quantity AND o.state != 'finished' AND o.state != 'canceled' AND r.product_id = '" . $product['id'] . "' AND r.location_id = '" . $location['id'] . "'") or die($mysqli->error);
                            while ($service_missing = mysqli_fetch_array($service_query)) {
                                $subtotal = $service_missing['quantity'] - $service_missing['reserved'];
                                $count += $subtotal;
                            }

                            $demands_query = $mysqli->query("SELECT b.quantity, w.id FROM demands_products_bridge b, warehouse w WHERE w.id = b.warehouse_id AND w.status <> '4' AND b.product_id = '" . $product['id'] . "' AND b.location_id = '" . $location['id'] . "' AND type = 'missing'") or die($mysqli->error);
                            while ($demands_missing = mysqli_fetch_array($demands_query)) {
                                $count = $count + $demands_missing['quantity'];
                            }

                            if($location['instock'] < $location['min_stock']){

                                $count += $location['min_stock'] - $location['instock'];



                            }

                            $finalmissing = $count;

                            if ($finalmissing > 0) {

                                $tooltip_title = $location['name']."&nbsp;&nbsp;<i class='fas fa-long-arrow-alt-right'></i>&nbsp;&nbsp;<strong>chybějící</strong>";
                                $toltip_content = '';

                                if (mysqli_num_rows($orderquery) != 0) {

                                    mysqli_data_seek($orderquery, 0);

                                    while ($ordermissing = mysqli_fetch_array($orderquery)) {

                                        $subtotal = $ordermissing['quantity'] - $ordermissing['reserved'];

                                        $toltip_content .= "<p><i class='fas fa-angle-right'></i> <a href='/admin/pages/orders/zobrazit-objednavku?id=". $ordermissing['id'] ."' target='_blank'>Objednávka #". $ordermissing['id']."</a>: <strong>-".$subtotal." ks</strong></p>";

                                    }

                                }

                                if (mysqli_num_rows($service_query) != 0) {

                                    mysqli_data_seek($service_query, 0);

                                    while ($service_missing = mysqli_fetch_array($service_query)) {

                                        $subtotal = $service_missing['quantity'] - $service_missing['reserved'];

                                        $toltip_content .= "<p><i class='fas fa-angle-right'></i> <a href='/admin/pages/services/zobrazit-servis?id=". $service_missing['id'] ."' target='_blank'>Servis #". $service_missing['id']."</a>: <strong>-".$subtotal." ks</strong></p>";

                                    }

                                }

                                if (mysqli_num_rows($demands_query) != 0) {

                                    mysqli_data_seek($demands_query, 0);

                                    while ($demands_missing = mysqli_fetch_array($demands_query)) {

                                        $toltip_content .= "<p><i class='fas fa-angle-right'></i> <a href='/admin/pages/warehouse/zobrazit-virivku?id=". $demands_missing['id'] ."' target='_blank'>Vířivka #". $demands_missing['id']."</a>: <strong>-".$demands_missing['quantity']." ks</strong></p>";

                                    }

                                }

                                ?>
                                <span style="font-size: 12px; color:#d42020; font-weight: bold; cursor: pointer;" data-toggle="popover" data-trigger="click" data-html="true" data-placement="top" data-content="<?= $toltip_content ?>" data-original-title="<?= $tooltip_title ?>">-<?= $finalmissing ?> ks <i class="fas fa-info-circle" style="margin-left: 2px"></i></span>
                                <?php


                            } else { ?>

                                <span style="font-size: 12px;color:#d42020;"><i class="entypo-block"></i></span>

                            <?php } ?></td>

                        <!-- na cestě -->
                        <td class="text-center"><?php

                            $supply_query = $mysqli->query("SELECT r.quantity, o.id, DATE_FORMAT(o.date, '%d. %m. %Y') as date, o.status FROM products_supply_bridge r, products_supply o WHERE o.id = r.supply_id AND r.product_id = '" . $product['id'] . "' AND o.location_id = '" . $location['id'] . "' AND o.status < 3") or die($mysqli->error);

                            $missing = 0;
                            $count = 0;
                            while ($supply = mysqli_fetch_array($supply_query)) {

                                $subtotal = $supply['quantity'];
                                $count = $count + $subtotal;

                            }

                            if ($count == 0) { ?>

                                <span style="font-size: 12px; color:#0072bc;"><i class="entypo-block"></i></span>



                            <?php }

                            $toltip_content = '';
                            $tooltip_title = $location['name']."&nbsp;&nbsp;<i class='fas fa-long-arrow-alt-right'></i>&nbsp;&nbsp;<strong>dodávka</strong>";

                            if (mysqli_num_rows($supply_query) != 0) {

                                mysqli_data_seek($supply_query, 0);

                                $total_delivery = 0;
                                while ($supply = mysqli_fetch_array($supply_query)) {



                                    $toltip_content .= "<p><i class='fas fa-angle-right'></i> <a href='/admin/pages/accessories/zobrazit-dodavku?id=". $supply['id'] ."' target='_blank'>Dodávka #". $supply['id']."</a>: <strong>".$supply['quantity']." ks</strong>, Stav: ".supply_status($supply['status'])." [". $supply['date'] ."]</p>";



                                    $orderquery = $mysqli->query("SELECT r.quantity, o.id FROM supply_types_bridge r, orders o WHERE o.id = r.type_id AND o.order_status < '3' AND r.type - 'order' AND r.product_id = '" . $product['id'] . "' AND r.supply_id = '" . $supply['id'] . "'") or die($mysqli->error);
                                    if (mysqli_num_rows($orderquery) != 0) {

                                        mysqli_data_seek($orderquery, 0);

                                        while ($orderreserved = mysqli_fetch_array($orderquery)) { ?>

                                            <p style="padding-left: 10px;"><a href="/admin/pages/orders/zobrazit-objednavku?id=<?= $orderreserved['id'] ?>" target="_blank">Objednávka #<?= $orderreserved['id'] ?></a> - rezervovaných <?= $orderreserved['quantity'] ?> ks</p>

                                            <?php
                                        }

                                    }

                                    $total_delivery += $supply['quantity'];

                                }

                                ?>
                                <span style="font-size: 12px; color:#0072bc; font-weight: bold; cursor: pointer;" data-toggle="popover" data-trigger="click" data-html="true" data-placement="top" data-content="<?= $toltip_content ?>" data-original-title="<?= $tooltip_title ?>"><?= $total_delivery ?> ks <i class="fas fa-info-circle" style="margin-left: 2px"></i></span>
                                <?php

                            }

                            ?></td>
                    </tr>

                <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } ?>



</div>

</div>

<div class="col-sm-6" style="padding: 0;">


<div class="panel panel-primary" data-collapsed="0">

	<div class="panel-heading">
					<div class="panel-title">
						<strong style="font-weight: 600">Specifikace</strong>
					</div>

				</div>

			<div class="panel-body">




			<?php

    $specifications_query = $mysqli->query("SELECT name, value FROM products_specifications WHERE product_id = '$id'");

    if (mysqli_num_rows($specifications_query) > 0) { ?>

							<table class="table table-bordered" style="    margin: 0;">
		<thead>
			<tr>

				<th class="text-center">Název</th>
				<th class="text-center">Hodnota</th>
			</tr>
		</thead>

		<tbody>

			<?php while ($specification = mysqli_fetch_array($specifications_query)) { ?>

			<tr>
				<td class="text-center"><?= $specification['name'] ?></td>
				<td class="text-center"><?= $specification['value'] ?></td>
			</tr>

			<?php } ?>


			</tbody>
		</table>

			<?php } else { ?>

			<p style="padding: 10px; margin-bottom: 0;">Produkt nemá žádné specifikace.</p>

			<?php } ?>



			</div>

	</div>

	<div class="panel panel-primary" data-collapsed="0">


				<div class="panel-heading">
					<div class="panel-title">
						<strong style="font-weight: 600">Související</strong>
					</div>

				</div>

						<div class="panel-body">

						<p>Doplňkový prodej:
							<?php

    $cross_selling = unserialize($product['cross_selling'], ['allowed_classes' => false]);

    $i = 0;
    if (isset($cross_selling) && $cross_selling != "") {
        ?>

							<strong>

							<?php
        foreach ($cross_selling as $cross_product) {

            $select_product = $mysqli->query("SELECT id, productname FROM products WHERE ean = '$cross_product'");

            $cross_selected = mysqli_fetch_array($select_product);

            if ($i == 0) { ?>

										<a href="/admin/pages/accessories/zobrazit-prislusenstvi?id=<?= $cross_selected['id'] ?>" target="_blank"><?= $cross_selected['productname'] ?></a>

									<?php } elseif ($i > 0) { ?>- <a href="/admin/pages/accessories/zobrazit-prislusenstvi?id=<?= $cross_selected['id'] ?>" target="_blank"><?= $cross_selected['productname'] ?></a>



									<?php }

            $i++;
        }?>
						</strong>

						<?php } else { ?>
						žádné položky
						<?php } ?>
					</p>

							<p style="margin-bottom: 0;">Navyšovací prodej:
							<?php

    $up_selling = unserialize($product['up_selling'], ['allowed_classes' => false]);

    $i = 0;
    if (isset($up_selling) && $up_selling != "") { ?>
 							<strong>

							<?php foreach ($up_selling as $up_product) {

        $select_product = $mysqli->query("SELECT id, productname FROM products WHERE ean = '$up_product'");

        $up_selected = mysqli_fetch_array($select_product);

        if ($i == 0) { ?>

										<a href="./zobrazit-prislusenstvi?id=<?= $up_selected['id'] ?>" target="_blank"><?= $up_selected['productname'] ?></a>

									<?php } elseif ($i > 0) { ?>- <a href="./zobrazit-prislusenstvi?id=<?= $up_selected['id'] ?>" target="_blank"><?= $up_selected['productname'] ?></a>



									<?php }

        $i++;
    }?>
						</strong>

							<?php } else { ?>
						žádné položky
						<?php } ?>
					</p>


</div>
</div>




<div class="panel panel-primary" data-collapsed="0">


				<div class="panel-heading">
					<div class="panel-title" style="width: 100%;">
						<strong style="font-weight: 600">Obchody</strong>

						<a href="/admin/controllers/stores/#?id=<?= $id ?>&redirect=true" class="btn btn-info btn-sm" style="float: right; color: #FFF;" disabled>
							<i class="entypo-upload"></i>
							Aktualizovat
						</a>

					</div>


				</div>

						<div class="panel-body">
<div class="invoice">


	<div class="row">
		<?php
        $shops_query = $mysqli->query("SELECT * FROM shops");

        while ($shop = mysqli_fetch_array($shops_query)) {
        ?>

		<div class="col-sm-3 invoice-left well" style="padding: 3px 10px 0; margin: 0 0.5%; width: 32%; min-height: 103px; ">

			<h5 style="font-size: 13px; margin-bottom: 8px;"><?= $shop['name'] ?></h5>

			<?php

        $shop_price_query = $mysqli->query("SELECT site_id, sale_price FROM products_sites WHERE product_id = '$id' AND site = '" . $shop['slug'] . "'") or die($mysqli->error);

        if (mysqli_num_rows($shop_price_query) > 0) {

            $shop_id = mysqli_fetch_array($shop_price_query);

            $shop_category_query = $mysqli->query("SELECT s.name FROM products_sites_categories c, shops_categories s WHERE c.category = s.id AND c.product_id = '$id' AND c.site = '" . $shop['slug'] . "' AND s.shop_id = '" . $shop['id'] . "'") or die($mysqli->error);
            ?>
			<p><strong><i class="entypo-archive"></i> <?php $i = 0;while ($shop_category = mysqli_fetch_array($shop_category_query)) {if ($i == 0) {echo $shop_category['name'];} else {echo ', ' . $shop_category['name'];}
                $i++;}?></strong></p>


			<?php

            if ($shop_id['site_id'] != 0) {
                ?>

			<a href="<?= $shop['url'] ?>/?page_id=<?= $shop_id['site_id'] ?>" class="btn btn-md btn-white" target="_blank" style="width: 100%; margin-bottom: 10px;">Zobrazit</a>

			<?php

            } else {

                ?>
                <i class="entypo-flag"></i> Chyba při nahrání

		<?php } ?>


			<?php } else { ?>

			<p class="text-danger" style="margin: 24px 12px 24px 0; text-align: center;"><i class="entypo-cancel"></i>Není v obchodě.</p>

		 	<?php } ?>


		</div>
		<?php } ?>



	</div>

</div>
</div>

</div>

<?php

$find_query = $mysqli->query("SELECT spec_id FROM demands_products WHERE product_id = '".$product['id']."' LIMIT 1")or die($mysqli->error);

if(mysqli_num_rows($find_query) > 0){

    $find = mysqli_fetch_assoc($find_query);

    $get_spec = $mysqli->query("SELECT * FROM specs WHERE id = '".$find['spec_id']."'")or die($mysqli->error);
    $spec = mysqli_fetch_assoc($get_spec);

?>

    <div class="panel panel-primary" data-collapsed="0">


        <div class="panel-heading">
            <div class="panel-title" style="width: 100%;">
                <strong style="font-weight: 600">Potřebný počet pro kontejnery</strong>
            </div>


        </div>

        <div class="panel-body">
            <table class="table table-bordered table-hover ">
                <thead>
                <tr>
                    <td class="text-center">ID kontejneru</td>
                    <td class="text-center">Potřebný počet</td>
                    <td class="text-center">Datum doručení</td>
                </tr>
                </thead>
                <tbody>
            <?php



        $select_containers = $mysqli->query("SELECT * FROM containers WHERE closed = 2 ORDER BY date_due ASC")or die($mysqli->error);

                while($container = mysqli_fetch_assoc($select_containers)){



                    if($product['container_essential'] == 1){

                        $get_products = $mysqli->query("SELECT * FROM containers_products WHERE container_id = '".$container['id']."'")or die($mysqli->error);

                        $count = mysqli_num_rows($get_products);

                    }else{


                        $count = 0;
                        $tooltip_content = '';

                        $get_products = $mysqli->query("SELECT * FROM containers_products WHERE container_id = '".$container['id']."' AND demand_id != 0")or die($mysqli->error);

                        while($container_product = mysqli_fetch_assoc($get_products)){

                            if(!empty($spec) && $spec['type'] == 0){

                                $get_accessory_info = $mysqli->query("SELECT * FROM demands_products WHERE product_id = '".$product['id']."' AND type = '".$container_product['product']."'")or die($mysqli->error);

    //                        echo mysqli_num_rows($get_accessory_info);

                                $accessory = mysqli_fetch_assoc($get_accessory_info);

                                if($accessory['param_id'] == 1){

                                    $param_value = 'Ano';

                                }else{

                                    $param_value = 'Ne';

                                }

//                                print_r($accessory);

                                $get_product_specs = $mysqli->query("SELECT * FROM containers_products_specs_bridge WHERE client_id = '".$container_product['id']."' AND specs_id  = '".$spec['id']."'")or die($mysqli->error);
                                $product_spec = mysqli_fetch_assoc($get_product_specs);


                                $get_demand_specs = $mysqli->query("SELECT * FROM demands_specs_bridge WHERE client_id = '".$container_product['demand_id']."' AND specs_id  = '".$spec['id']."'")or die($mysqli->error);
                                $demand_spec = mysqli_fetch_assoc($get_demand_specs);

                                // todo if demand spec == required spec

                                if($demand_spec['value'] == $product_spec['value']){

//                                    echo 'equal';

                                }elseif(!empty($demand_spec['value']) && $demand_spec['value'] == $param_value){
//
//                                    echo 'different';
//
//                                    echo '<br><br>DEMAND_ID: '.$container_product['demand_id'];
//                                    echo '<br>CONTAINER VALUE: '.$product_spec['value'];
//                                    echo '<br>demand VALUE: '.$demand_spec['value'];

                                       $tooltip_content .= "<p><i class='fas fa-angle-right'></i> <a href='/admin/pages/warehouse/zobrazit-virivku?id=". $container_product['warehouse_id'] ."' target='_blank'>Vířivka ".$container_product['warehouse_id']."</a>: <strong>".$product_spec['value']."</strong> ☓ <a href='/admin/pages/demands/zobrazit-poptavku?id=". $container_product['demand_id'] ."' target='_blank'>Poptávka #". $container_product['demand_id']."</a>: <strong>".$demand_spec['value']."</strong></p>";


                                    $count++;

                                }


    //                            print_r($demand_spec);

//                                echo '<br><br>-----<br><br>';


                            }elseif(!empty($spec) && $spec['type'] == 1){


                                $get_accessory_info = $mysqli->query("SELECT * FROM demands_products p, specs_params s WHERE s.id = p.param_id AND s.spec_id = p.spec_id AND p.product_id = '".$product['id']."' AND p.type = '".$container_product['product']."'")or die($mysqli->error);

    //                        echo mysqli_num_rows($get_accessory_info);

    //                            echo $product['id'];

                                // get desired value of spec
                                $accessory = mysqli_fetch_assoc($get_accessory_info);

//                                print_r($accessory);


                                $get_product_specs = $mysqli->query("SELECT * FROM containers_products_specs_bridge WHERE client_id = '".$container_product['id']."' AND specs_id  = '".$spec['id']."'")or die($mysqli->error);

                                $product_spec = mysqli_fetch_assoc($get_product_specs);


                                $get_demand_specs = $mysqli->query("SELECT * FROM demands_specs_bridge WHERE client_id = '".$container_product['demand_id']."' AND specs_id  = '".$spec['id']."'")or die($mysqli->error);

                                $demand_spec = mysqli_fetch_assoc($get_demand_specs);


                                // todo if demand spec == required spec

                                if($demand_spec['value'] == $product_spec['value']){

//                                    echo 'equal';

                                }elseif(!empty($demand_spec['value']) && !empty($accessory['option']) && $demand_spec['value'] == $accessory['option']){

//                                    echo 'different';

//                                    echo '<br><br>DEMAND_ID: '.$container_product['demand_id'];
//                                    echo '<br>CONTAINER VALUE: '.$product_spec['value'];
//                                    echo '<br>demand VALUE: '.$demand_spec['value'];

                                    $tooltip_content .= "<p><i class='fas fa-angle-right'></i> <a href='/admin/pages/warehouse/zobrazit-virivku?id=". $container_product['warehouse_id'] ."' target='_blank'>Vířivka ".$container_product['warehouse_id']."</a>: <strong>".$product_spec['value']."</strong> ☓ <a href='/admin/pages/demands/zobrazit-poptavku?id=". $container_product['demand_id'] ."' target='_blank'>Poptávka #". $container_product['demand_id']."</a>: <strong>".$demand_spec['value']."</strong></p>";

                                    $count++;

                                }

//                              print_r($demand_spec);
//                              echo '<br><br>-----<br><br>';


                            }


                        }



                    }
    //                $get_specs = $mysqli->query("SELECT * FROM specs WHERE ")


                    ?>
                    <tr


                        <?php

                        if(date('m') == date("m", strtotime($container['date_due']))){

                            echo 'style="background-color: #f0ffea;"';

                        }elseif(date('m')+1 == date("m", strtotime($container['date_due']))) {

                            echo 'style="background-color: #fff2da;"';
                        }

                        ?>
                    >
                        <td class="text-center"><?= $container['container_name'] ?></td>
                        <td class="text-center"><?php if($count > 0){

                            ?><strong <?php
                                if(!empty($tooltip_content)){


                                    ?>
                                    style="cursor: pointer;" data-toggle="popover" data-trigger="click" data-html="true" data-placement="top" data-content="<?= $tooltip_content ?>" data-original-title="Položky vyžadující změny"
                                    <?php

                                }

                                ?>><?= $count ?> ks <i class="fas fa-info-circle" style="margin-left: 2px"></i></strong>



                        <?php

                        }else{

                            echo '<i class="entypo-block"></i>';

                        }?></td>
                        <td class="text-center"><?= date_formatted($container['date_due']) ?></td>
                    </>
                    <?php



                }

            ?>

                </tbody>
            </table>

        </div>

    </div>


    <?php

    }
?>


</div>

<div style="clear: both;"></div>



<?php



if (isset($product['type']) && $product['type'] == 'variable') { ?>

<div style="clear: both;"></div>

<hr style="margin: 0 0 16px;">

<?php

        $stock_query = $mysqli->query("SELECT * FROM products_variations WHERE product_id = '" . $product['id'] . "'") or die($mysqli->error);

        $i = 0;

        while ($variation = mysqli_fetch_array($stock_query)) {
            $i++;

            $value_query = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $variation['id'] . "'") or die($mysqli->error);
            $name = "";
            while ($value = mysqli_fetch_array($value_query)) {

                $name = $value['name'] . ': <strong>' . $value['value'] . '</strong>' . $name;

                $name2 = $value['name'] . ': <strong>' . $value['value'].'</strong>';
            }

            ?>

<div class="col-sm-6" style="padding-left: 0;">

	<div class="panel panel-primary" data-collapsed="0">



						<div class="panel-body" style="padding: 10px 15px 2px;">
<div class="invoice">

	<div class="row">

		<div class="col-sm-8 invoice-left">


			<h3><span style="font-size: 14px;"><?= $name ?><?php if (isset($variation['availability']) && $variation['availability'] == 2) { ?> ~ na objednávku
                    <?php } elseif (isset($variation['availability']) && $variation['availability'] == 3) { ?> ~ skryto
                    <?php }elseif (isset($variation['availability']) && $variation['availability'] == 4) { ?>
                        <span style="font-size: 12px;color:#cc2424;">~ nedostupné</span>
                        <?php } ?></span></h3>
			<h4 style=" margin-bottom: 18px; color: #595a55; font-size: 12px; font-weight: normal;"></h4>

            <p><?php if ($variation['description'] != "") {echo $variation['description'];} else {echo 'žádný popis varianty';}?></p>

                <hr>

				<p><strong>EAN:</strong> <?php if ($variation['ean'] != "") {echo $variation['ean'];} else {echo 'žádný EAN';}?> ~ <strong>SKU:</strong> <?= $variation['sku'] ?></p>

		</div>

		<div class="col-sm-4 invoice-right">

			<?php

            $path = PRODUCT_IMAGE_PATH.'/thumbnail/' . $product['seourl'] . '_variation_'.$variation['id'].'.jpg';
            if(file_exists($path)){
                $imagePath = '/data/stores/images/thumbnail/'.$product['seourl'].'_variation_'.$variation['id'].'.jpg';
            }else{
                $imagePath = '/data/assets/no-image-7.jpg';
            }
            echo '<img src="'.$imagePath.'" width="100" style="float: left; margin-right:8px; margin-bottom: 8px; border: 1px solid #ebebeb; border-bottom: 3px double #e2e2e5">';

            ?>

		</div>
	</div>
		<hr style="margin: 0 0 8px;">
	<div class="row">
		<div class="col-sm-12" style="font-size: 12px; padding: 10px 0 10px; float: left;">

            <div class="col-sm-4">
                <?php

                if (isset($variation['sale_price']) && $variation['sale_price'] != "" && $variation['sale_price'] != 0) {

                    ?>

                    <p>Původní cena: <?= number_format($variation['price'], 0, ',', ' ') ?> Kč</p>

                    <p>Zlevněná cena: <strong style="text-decoration: underline;"><?= number_format($variation['sale_price'], 0, ',', ' ') ?> Kč</strong></p>

                    <?php

                } else { ?>

                    <p>Cena: <strong style="text-decoration: underline; color: #000;"><?= number_format($variation['price'], 0, ',', ' ') ?> Kč</strong></p>

                <?php } ?>
            </div>
			<div class="col-sm-4">Nákupní: <strong><?= number_format($variation['purchase_price'], 0, ',', ' ') ?> Kč</strong></div>
			<div class="col-sm-4" style="margin-bottom: 17px;">Velkoobchodní: <strong><?= number_format($variation['wholesale_price'], 0, ',', ' ') ?> Kč</strong></div>
            <hr style="margin: 0 0 8px; float: left; width: 100%;">

			<div class="col-sm-3">Váha: <?php strong_echo($variation['weight']); ?> kg</div>
			<div class="col-sm-3">Délka: <?php strong_echo($variation['length']); ?> cm</div>
			<div class="col-sm-3">Šířka: <?php strong_echo($variation['width']); ?> cm</div>
			<div class="col-sm-3">Výška: <?php strong_echo($variation['height']); ?> cm</div>


		</div>
		</div>





</div>

</div>



        <hr>

<div class=col-sm-12" style="width: 100%; display: inline-block">
							<span class="text-right" style="float: right; margin-right: 10px;">

								<a data-id="<?= $product['id'] ?>" class="toggle-modal-stock-transfer btn btn-info btn-sm btn-icon icon-left" style="color: #fff; margin-top: -4px;">
								<i class="entypo-box" style="padding: 5px 6px;"></i> Přeskladnit
							</a>

								<a data-id="<?= $product['id'] ?>" data-varid="<?= $variation['id'] ?>" class="toggle-modal-stock btn btn-primary btn-sm btn-icon icon-left" style="margin-top: -4px;">
									<i class="entypo-box" style="padding: 5px 6px;"></i> Naskladnit
								</a>
							</span>
</div>
        <hr>

        <div class="col-sm-12">
            <table class="table table-bordered table-hover ">
                <thead>
                    <tr>
                        <td class="text-center">Lokace</td>
                        <td class="text-center">Skladem</td>
                        <td class="text-center">Rezervace</td>
                        <td class="text-center">Chybí</td>
                        <td class="text-center">Na cestě</td>
                    </tr>
                </thead>
                <tbody>
                <?php
            $locations_query = $mysqli->query("SELECT * FROM shops_locations l LEFT JOIN products_stocks s ON s.location_id = l.id AND s.product_id = '" . $product['id'] . "' AND s.variation_id = '" . $variation['id'] . "' ORDER BY type ASC");

            while ($location = mysqli_fetch_array($locations_query)) {

                ?>
                    <tr>
                        <td><?= $location['name'] ?><br><small>min. sklad: <?= $location['min_stock'] ?></small></td>

                        <!-- skladem -->
                        <td class="text-center"><?php
                            if (isset($product['availability']) && $product['availability'] == 0) { ?>
                                <span style="font-size: 12px;color:#0bb668; font-weight: bold; "><?= $location['instock'] ?> ks</span>
                            <?php } elseif (isset($product['availability']) && $product['availability'] == 1) { ?>
                                <span style="font-size: 12px;color:#f56954;">Do 14 dní</span>
                            <?php } elseif (isset($product['availability']) && $product['availability'] == 3) { ?>
                                <span style="font-size: 12px;color:#7a92a3;"><small>Skryto - </small><?= $location['instock'] ?> ks skladem</span>
                            <?php }else { ?>
                                <span style="font-size: 12px;color:#cc2424;"><small>Nedostupné - </small><?= $location['instock'] ?> ks skladem</span>
                            <?php } ?></td>

                        <!-- rezervace -->
                        <td class="text-center"> <?php

                            $orderquery = $mysqli->query("SELECT r.reserved, o.id 
                                FROM orders_products_bridge r, orders o 
                                WHERE o.id = r.aggregate_id AND o.order_status < '3' AND r.product_id = '" . $product['id'] . "' AND r.variation_id = '" . $variation['id'] . "' AND r.location_id = '" . $location['id'] . "' AND r.aggregate_type = 'order'") or die($mysqli->error);

                            $count = 0;
                            while ($orderreserved = mysqli_fetch_array($orderquery)) {
                                $count = $count + $orderreserved['reserved'];
                            }

                            $service_query = $mysqli->query("SELECT r.reserved, r.quantity, o.id FROM services_products_bridge r, services o WHERE o.id = r.aggregate_id AND r.reserved <> r.quantity AND o.state != 'finished' AND o.state != 'canceled' AND r.product_id = '" . $product['id'] . "' AND r.variation_id = '" . $variation['id'] . "' AND r.location_id = '" . $location['id'] . "'") or die($mysqli->error);
                            while ($service_reserved = mysqli_fetch_array($service_query)) {
                                $count = $count + $service_reserved['reserved'];
                            }

                            $demands_query = $mysqli->query("SELECT b.quantity, w.id FROM demands_products_bridge b, warehouse w WHERE w.id = b.warehouse_id AND w.status <> '4' AND b.product_id = '" . $product['id'] . "' AND b.variation_id = '" . $variation['id'] . "' AND b.location_id = '" . $location['id'] . "' AND type = 'warehouse'") or die($mysqli->error);
                            while ($demands_reserved = mysqli_fetch_array($demands_query)) {
                                $count = $count + $demands_reserved['quantity'];
                            }

                            $finalreserved = $count;

                            if ($finalreserved > 0) { ?>
                                <p style="font-size: 12px;color:#000000;margin-top: 6px; margin-bottom: 0px">Rezervováno: <?= $finalreserved ?> ks</p>
                                <?php

                                if (mysqli_num_rows($orderquery) != 0) {

                                    mysqli_data_seek($orderquery, 0);

                                    while ($orderreserved = mysqli_fetch_array($orderquery)) { ?>

                                        <p style="padding-left: 10px;"><a href="./zobrazit-objednavku?id=<?= $orderreserved['id'] ?>" target="_blank">Objednávka #<?= $orderreserved['id'] ?></a> - rezervovaných <?= $orderreserved['reserved'] ?> ks</p>

                                        <?php
                                    }

                                }

                                if (mysqli_num_rows($service_query) != 0) {

                                    mysqli_data_seek($service_query, 0);

                                    while ($service_reserved = mysqli_fetch_array($service_query)) { ?>

                                        <p style="padding-left: 10px;"><a href="./zobrazit-objednavku?id=<?= $service_reserved['id'] ?>" target="_blank">Servis #<?= $service_reserved['id'] ?></a> - rezervovaných <?= $service_reserved['reserved'] ?> ks</p>

                                        <?php
                                    }

                                }

                                if (mysqli_num_rows($demands_query) != 0) {

                                    mysqli_data_seek($demands_query, 0);

                                    while ($demands_reserved = mysqli_fetch_array($demands_query)) { ?>

                                        <p style="padding-left: 10px;"><a href="/admin/pages/warehouse/zobrazit-virivku?id=<?= $demands_reserved['id'] ?>" target="_blank">Vířivka #<?= $demands_reserved['id'] ?></a> - rezervovaných <?= $demands_reserved['quantity'] ?> ks</p>

                                        <?php
                                    }

                                }

                            } else { ?>

                                <span style="font-size: 12px;color:#000000;"><i class="entypo-block"></i></span>
                            <?php } ?></td>



                        <!-- chybí -->
                        <td class="text-center"><?php
                            $orderquery = $mysqli->query("SELECT r.reserved, r.quantity, o.id FROM orders_products_bridge r, orders o WHERE o.id = r.aggregate_id AND r.reserved <> r.quantity AND o.order_status < '3' AND r.product_id = '" . $product['id'] . "' AND r.variation_id = '" . $variation['id'] . "' AND r.location_id = '" . $location['id'] . "' AND r.aggregate_type = 'order'") or die($mysqli->error);
                            $count = 0;
                            while ($ordermissing = mysqli_fetch_array($orderquery)) {
                                $subtotal = $ordermissing['quantity'] - $ordermissing['reserved'];
                                $count = $count + $subtotal;
                            }

                            $service_query = $mysqli->query("SELECT r.reserved, r.quantity, o.id FROM services_products_bridge r, services o WHERE o.id = r.aggregate_id AND r.reserved <> r.quantity AND o.state != 'finished' AND o.state != 'canceled' AND r.product_id = '" . $product['id'] . "' AND r.variation_id = '" . $variation['id'] . "' AND r.location_id = '" . $location['id'] . "'") or die($mysqli->error);
                            while ($service_missing = mysqli_fetch_array($service_query)) {
                                $subtotal = $service_missing['quantity'] - $service_missing['reserved'];
                                $count = $count + $subtotal;
                            }

                            $demands_query = $mysqli->query("SELECT b.quantity, w.id FROM demands_products_bridge b, warehouse w WHERE w.id = b.warehouse_id AND w.status <> '4' AND b.product_id = '" . $product['id'] . "' AND b.variation_id = '" . $variation['id'] . "' AND b.location_id = '" . $location['id'] . "' AND type = 'missing'") or die($mysqli->error);
                            while ($demands_missing = mysqli_fetch_array($demands_query)) {
                                $count = $count + $demands_missing['quantity'];
                            }



                            $finalmissing = $count;

                            if ($finalmissing > 0) { ?>
                                <h3 style="font-size: 12px;color:#d42020; margin-top: 6px;">Chybí: -<?= $finalmissing ?> ks</h3>
                                <?php

                                if (mysqli_num_rows($orderquery) != 0) {

                                    mysqli_data_seek($orderquery, 0);

                                    while ($ordermissing = mysqli_fetch_array($orderquery)) {

                                        $subtotal = $ordermissing['quantity'] - $ordermissing['reserved'];

                                        ?>

                                        <p style="padding-left: 10px;"><a href="./zobrazit-objednavku?id=<?= $ordermissing['id'] ?>" target="_blank">Objednávka #<?= $ordermissing['id'] ?></a> - chybějících <?= $subtotal ?> ks</p>

                                        <?php
                                    }

                                }

                                if (mysqli_num_rows($service_query) != 0) {

                                    mysqli_data_seek($service_query, 0);

                                    while ($service_missing = mysqli_fetch_array($service_query)) {

                                        $subtotal = $service_missing['quantity'] - $service_missing['reserved'];

                                        ?>

                                        <p style="padding-left: 10px;"><a href="./zobrazit-objednavku?id=<?= $service_missing['id'] ?>" target="_blank">Servis #<?= $service_missing['id'] ?></a> - chybějících <?= $subtotal ?> ks</p>

                                        <?php
                                    }

                                }

                                if (mysqli_num_rows($demands_query) != 0) {

                                    mysqli_data_seek($demands_query, 0);

                                    while ($demands_missing = mysqli_fetch_array($demands_query)) {

                                        ?>

                                        <p style="padding-left: 10px;"><a href="/admin/pages/warehouse/zobrazit-virivku?id=<?= $demands_missing['id'] ?>" target="_blank">Vířivka #<?= $demands_missing['id'] ?></a> - chybějících <?= $demands_missing['quantity'] ?> ks</p>

                                        <?php
                                    }

                                }

                            } else { ?>


                                <span style="font-size: 12px;color:#d42020;"><i class="entypo-block"></i></span>

                            <?php } ?></td>

                        <!-- na cestě -->
                        <td class="text-center"><?php

                            $supply_query = $mysqli->query("SELECT r.quantity, o.id, DATE_FORMAT(o.date, '%d. %m. %Y') as date, o.status FROM products_supply_bridge r, products_supply o WHERE o.id = r.supply_id AND r.product_id = '" . $product['id'] . "' AND r.variation_id = '" . $variation['id'] . "' AND o.location_id = '" . $location['id'] . "' AND o.status < 3") or die($mysqli->error);

                            $missing = 0;
                            $count = 0;
                            while ($supply = mysqli_fetch_array($supply_query)) {

                                $subtotal = $supply['quantity'];
                                $count = $count + $subtotal;

                            }

                            if ($count == 0) { ?>

                                <span style="font-size: 12px; color:#0072bc;"><i class="entypo-block"></i></span>



                            <?php }

                            if (mysqli_num_rows($supply_query) != 0) {

                                mysqli_data_seek($supply_query, 0);

                                while ($supply = mysqli_fetch_array($supply_query)) {

                                    ?>


                                    <h3 style="font-size: 11px;color:#0072bc;margin-top: 6px;"><a href="/admin/pages/accessories/zobrazit-dodavku?id=<?= $supply['id'] ?>" target="_blank">Dodávka #<?= $supply['id'] ?></a> <br><?= $supply['quantity'] ?> ks, Stav: <?= supply_status($supply['status']) ?>. [<?= $supply['date'] ?>]</h3>

                                    <?php

                                    $orderquery = $mysqli->query("SELECT r.quantity, o.id FROM supply_types_bridge r, orders o WHERE o.id = r.type_id AND o.order_status < '3' AND r.type - 'order' AND r.product_id = '" . $product['id'] . "' AND r.variation_id = '" . $variation['id'] . "' AND r.supply_id = '" . $supply['id'] . "'") or die($mysqli->error);
                                    if (mysqli_num_rows($orderquery) != 0) {

                                        mysqli_data_seek($orderquery, 0);

                                        while ($orderreserved = mysqli_fetch_array($orderquery)) { ?>

                                            <p style="padding-left: 10px;"><a href="/admin/pages/orders/zobrazit-objednavku?id=<?= $orderreserved['id'] ?>" target="_blank">Objednávka #<?= $orderreserved['id'] ?></a> - rezervovaných <?= $orderreserved['quantity'] ?> ks</p>

                                            <?php
                                        }

                                    }

                                }

                            }

                            ?></td>
                    </tr>

                <?php } ?>
                </tbody>
            </table>
            <small><?= $variation['id'] ?></small>
        </div>


				<div style="clear: both;"></div>




</div>    </div>


            <?php
            if ($i == 3) {$i = 0;?><div style="clear: both;"></div>
					<hr style="margin: 30px 0 40px;"><?php

            }

        }?>




<?php } ?>
<div style="clear: both;"></div>

	<center>
	<div class="form-group default-padding button-demo">
		<a href="upravit-prislusenstvi?id=<?= $product['id'] ?>">
			<button style="margin-bottom: 24px; margin-right: 26px; font-size: 20px; padding: 20px 50px 20px 140px; margin-top: 20px;" data-style="zoom-in" class="ladda-button btn btn-default btn-icon icon-left btn-lg">
				<i class="entypo-pencil" style="line-height: 48px;font-size: 40px; padding: 10px 20px;"></i> <span class="ladda-label">Upravit příslušenství</span></button></a>
	</div></center>


        <div class="col-sm-12" style="padding:0;">



            <?php

            function get_type($type)
            {

                if ($type === 'update_stock') {echo 'manuální úprava skladu';
                    $sign = '';

                } elseif ($type === 'order_missing_reserve') {echo 'rezervace k již vytvořené objednávce';
                    $sign = '-';

                } elseif ($type === 'to_stock') {echo 'naskladnění položky';
                    $sign = '+';

                } elseif ($type === 'order_reserve') {echo 'rezervace k nově vytvořené objednávce';
                    $sign = '-';

                } elseif ($type === 'order_cancel') {echo 'storno objednávky';
                    $sign = '+';

                } elseif ($type === 'demand_reserve') {echo 'rezervace k poptávce';
                    $sign = '-';

                } elseif ($type === 'demand_update') {echo 'změna počtu u poptávky';
                    $sign = '-';

                } elseif ($type === 'demand_change') {echo 'odstranění od poptávky';
                    $sign = '-';

                }

            }

            function get_sign($type)
            {

                if ($type === 'update_stock') {echo '';

                } elseif ($type === 'order_missing_reserve') {echo '-';

                } elseif ($type === 'to_stock') {echo '+';

                } elseif ($type === 'order_reserve') {echo '-';

                } elseif ($type === 'order_cancel') {echo '+';

                } elseif ($type === 'demand_reserve') {echo '-';

                } elseif ($type === 'demand_missing_reserve') {echo '-';

                } elseif ($type === 'demand_update') {echo '';

                } elseif ($type === 'demand_change') {echo '+';

                }

            }

            function border_bottom($type)
            {

                if ($type === 'to_stock' || $type === 'update_stock' || $type === 'demand_change' || $type === 'order_cancel') {echo 'border-bottom: 1px solid #c3c3c3;';}
            }

            if (isset($product['type']) && $product['type'] == 'simple') { ?>
                <div class="panel panel-primary" data-collapsed="0">

                    <div class="panel-body">

                        <?php

                        $locations_query = $mysqli->query("SELECT * FROM shops_locations l LEFT JOIN products_stocks s ON s.location_id = l.id AND s.product_id = '" . $product['id'] . "' ORDER BY type ASC");

                        while ($location = mysqli_fetch_array($locations_query)) {

                            ?>

                            <div class="panel-group" id="accordion-test">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#accordion-test" href="#location-<?= $location['id'] ?>" class="collapsed" aria-expanded="false">
                                                <?= $location['name'] ?> ~ historie skladu
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="location-<?= $location['id'] ?>" class="panel-collapse collapse" aria-expanded="false"> <div class="panel-body">


                                            <?php if (isset($product['type']) && $product['type'] == 'simple') { ?>
                                                <table class="table table-bordered table-striped datatable dataTable" id="table-2" aria-describedby="table-2_info">
                                                    <thead>
                                                    <tr role="row">
                                                        <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Student Name: activate to sort column ascending" style="width: 240px;text-align:center">Datum</th>
                                                        <th role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Student Name: activate to sort column ascending" style="width: 340px;text-align:center">Typ</th>
                                                        <th role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Student Name: activate to sort column ascending" style="width: 140px; text-align:center">Změna stavu</th>
                                                        <th role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Student Name: activate to sort column ascending" style="width: 160px; text-align:center">Konečný počet</th>
                                                        <th role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Student Name: activate to sort column ascending" style="width: 240px;text-align:center">ID cíle</th>
                                                        <th role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Student Name: activate to sort column ascending" style="width: 240px;text-align:center">Admin</th>

                                                    </tr>

                                                    </thead>
                                                    <tbody>
                                                    <?php


                                                    $history_query = $mysqli->query("SELECT *, DATE_FORMAT(datetime, '%d. %m. %Y %H:%i:%s') as dateformated FROM history_products WHERE product_id = '$id' AND location_id = '".$location['id']."' order by id desc LIMIT 10") or die($mysqli->error);
                                                    while ($history = mysqli_fetch_assoc($history_query)) {

                                                    ?>
                                                    <tr>
                                                        <td style="text-align:center; <?php border_bottom($history['type']);?>"><?= $history['dateformated'] ?></td>
                                                        <td style="text-align:center; <?php border_bottom($history['type']);?>"><?php get_type($history['type']);?></td>
                                                        <td style="text-align:center; <?php border_bottom($history['type']);?>"><?php if ($history['value'] > 0) {echo get_sign($history['type']) . $history['value'];
                                                            } else {echo $history['value'];}?></td>
                                                        <td style="text-align:center; <?php border_bottom($history['type']);?>"><?= $history['final_stock'] ?></td>
                                                        <td style="text-align:center; <?php border_bottom($history['type']);?>"><?php if (isset($history['target_id']) && $history['target_id'] == 0) {echo '-';} else {echo $history['target_id'];}?></td>
                                                        <td style="text-align:center; <?php border_bottom($history['type']);?>"><?php
                                                            if ($history['admin_id'] != 0) {
                                                                $admin_query = $mysqli->query("SELECT user_name FROM demands WHERE id = '" . $history['admin_id'] . "'") or die($mysqli->error);
                                                                $admin = mysqli_fetch_assoc($admin_query);

                                                                echo $admin['user_name'];

                                                            } else {echo 'systém';}?></td>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            <?php } ?>




                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        <div style="clear: both;"></div>
                    </div>
                </div>

            <?php } ?>

        </div>







<script type="text/javascript">
$(document).ready(function(){
    $(".toggle-modal-stock").click(function(e){

		$('#stock-modal').removeData('bs.modal');
    	 e.preventDefault();

    	 var id = $(this).data("id");
    	 var var_id = $(this).data("varid");

        $("#stock-modal").modal({

            remote: '/admin/controllers/modals/modal-stock-data.php?id='+id+'&od=&var_id='+var_id,
        });
    });
});
</script>


<div class="modal fade" id="stock-modal" aria-hidden="true" style="display: none; margin-top: 8%;">


</div>




<script type="text/javascript">
$(document).ready(function(){
    $(".toggle-modal-stock-transfer").click(function(e){

		$('#stock-transfer-modal').removeData('bs.modal');
    	 e.preventDefault();


    	 var id = $(this).data("id");

        $("#stock-transfer-modal").modal({

            remote: '/admin/controllers/modals/modal-stock-transfer.php?id='+id+'&od=',
        });
    });
});
</script>


<div class="modal fade" id="stock-transfer-modal" aria-hidden="true" style="display: none; margin-top: 8%;">


</div>

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



<?php


} else {

    include INCLUDES . "/404.php";

}?>
