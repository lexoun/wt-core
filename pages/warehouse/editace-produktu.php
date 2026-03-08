<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

if (isset($_REQUEST['od'])) {$od = $_REQUEST['od'];}

if (isset($_REQUEST['customer'])) {$customer = $_REQUEST['customer'];}

$pagetitle = "Editace produktů";

$bread1 = "Sklad";

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "add") {
    $displaysuccess = true;
    $successhlaska = "Produkt byl úspěšně přidán.";
}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "remove") {
    $displaysuccess = true;
    $successhlaska = "Produkt byl úspěšně odstraněn.";
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "remove") {

    $unlinkquery = $mysqli->query('SELECT connect_name FROM warehouse_products WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);
    $unlink = mysqli_fetch_assoc($unlinkquery);

    $result = glob($_SERVER['DOCUMENT_ROOT'] . "/admin/data/images/customer/" . $unlink['connect_name'] . ".*");
    foreach ($result as $res) {
        unlink($res);
    }

    $result = glob($_SERVER['DOCUMENT_ROOT'] . "/admin/data/images/customer/" . $unlink['connect_name'] . ".*");
    foreach ($result as $res) {
        unlink($res);
    }

    $mysqli->query('DELETE FROM warehouse_products WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);

    Header("Location:https://www.wellnesstrade.cz/admin/pages/warehouse/editace-produktu?success=remove");
    exit;
}



if (isset($_REQUEST['action']) && $_REQUEST['action'] == "remove_type") {

    $unlinkquery = $mysqli->query('SELECT customer, connect_name FROM warehouse_products WHERE id="' . $_REQUEST['product_id'] . '"') or die($mysqli->error);
    $unlink = mysqli_fetch_assoc($unlinkquery);

    $select_variations = $mysqli->query("SELECT name FROM warehouse_products_types WHERE warehouse_product_id = '" . $_REQUEST['product_id'] . "' AND id = '".$_REQUEST['type_id']."'");
    $type = mysqli_fetch_array($select_variations);


    $file_url = '/admin/data/demands/documents/'.returnpn($unlink['customer'], $unlink['connect_name']).' '.$type['name'].' - Stavební příprava.pdf';
    if(file_exists($_SERVER['DOCUMENT_ROOT'] . $file_url)){ unlink($_SERVER['DOCUMENT_ROOT'] . $file_url); }


    $image_url = '/admin/data/images/customer/'.returnpn($unlink['customer'], $unlink['connect_name']).' '.$type['name'].'.png';
    if(file_exists($_SERVER['DOCUMENT_ROOT'] . $image_url)){ unlink($_SERVER['DOCUMENT_ROOT'] . $image_url); }

    $mysqli->query('DELETE FROM warehouse_products_types WHERE id = "' . $_REQUEST['type_id'] . '"') or die($mysqli->error);
    $mysqli->query('DELETE FROM warehouse_products_types_specs WHERE type_id = "' . $_REQUEST['type_id'] . '"') or die($mysqli->error);

    Header("Location:https://www.wellnesstrade.cz/admin/pages/warehouse/editace-produktu?success=remove_type");
    exit;
}


if (isset($_REQUEST['action']) && $_REQUEST['action'] == "preparation") {


    $product_query = $mysqli->query('SELECT connect_name, customer FROM warehouse_products WHERE id="' . $_REQUEST['product_id'] . '"') or die($mysqli->error);
    $product = mysqli_fetch_assoc($product_query);

    if(!empty($_REQUEST['type_id'])) {

        $select_variations = $mysqli->query("SELECT name FROM warehouse_products_types WHERE warehouse_product_id = '" . $_REQUEST['product_id'] . "' AND id = '" . $_REQUEST['type_id'] . "'");
        $type = mysqli_fetch_array($select_variations);

        $path = $_SERVER['DOCUMENT_ROOT'] . '/admin/data/demands/documents/'.returnpn($product['customer'], $product['connect_name']).' '.$type['name'].' - Stavební příprava.pdf';


    }else{

        $path = $_SERVER['DOCUMENT_ROOT'] . '/admin/data/demands/documents/'.returnpn($product['customer'], $product['connect_name']).' - Stavební příprava.pdf';

    }

    move_uploaded_file($_FILES['file']['tmp_name'], $path);

    Header("Location:https://www.wellnesstrade.cz/admin/pages/warehouse/editace-produktu?success=remove");
    exit;
}


if (isset($_REQUEST['action']) && $_REQUEST['action'] == "preparation_remove") {


    $product_query = $mysqli->query('SELECT connect_name, customer FROM warehouse_products WHERE id="' . $_REQUEST['product_id'] . '"') or die($mysqli->error);
    $product = mysqli_fetch_assoc($product_query);

    $select_variations = $mysqli->query("SELECT name FROM warehouse_products_types WHERE warehouse_product_id = '" . $_REQUEST['product_id'] . "' AND id = '".$_REQUEST['type_id']."'");
    $type = mysqli_fetch_array($select_variations);


    $file_url = '/admin/data/demands/documents/'.returnpn($product['customer'], $product['connect_name']).' '.$type['name'].' - Stavební příprava.pdf';

    if(file_exists($_SERVER['DOCUMENT_ROOT'] . $file_url)){ unlink($_SERVER['DOCUMENT_ROOT'] . $file_url); }

    Header("Location:https://www.wellnesstrade.cz/admin/pages/warehouse/editace-produktu?success=remove");
    exit;
}



$saunyquery = $mysqli->query("SELECT * FROM warehouse_products WHERE active = 'yes' AND customer = 0 ORDER BY code");


include VIEW . '/default/header.php';


?>

<div class="row" style="margin-bottom: 16px;">
	<div class="col-md-10 col-sm-7">
		<h2 style="float: left"><?= $pagetitle ?></h2>
	</div>
	<div class="col-md-2 col-sm-5" style="text-align: right;float:right;">


				<a href="pridat-produkt" style=" margin-right: 16px;" class="btn btn-default btn-icon icon-left btn-lg">
					<i class="entypo-plus"></i>
					Přidat produkt
				</a>

	</div>
</div>


<?php

$products_query = $mysqli->query('SELECT * FROM warehouse_products ORDER BY customer desc, brand asc, rank asc, code asc') or die($mysqli->error);

while ($product = mysqli_fetch_array($products_query)) {


    $demandsquery = $mysqli->query("SELECT count(*) as max FROM demands WHERE product = '" . $product['connect_name'] . "' AND status <> 5 AND status <> 6");
    $demands = mysqli_fetch_array($demandsquery);

    $realizationquery = $mysqli->query("SELECT count(*) as max FROM demands WHERE product = '" . $product['connect_name'] . "' AND status = 4");
    $realization = mysqli_fetch_array($realizationquery);

    $clientsquery = $mysqli->query("SELECT count(*) as max FROM demands WHERE product = '" . $product['connect_name'] . "' AND status = 5");
    $clients = mysqli_fetch_array($clientsquery);

    $warehousequery = $mysqli->query("SELECT status, demand_id FROM warehouse WHERE product = '" . $product['connect_name'] . "'");

    $produce = 0;
    $shipped = 0;
    $stock = 0;
    $showroom = 0;
    $showroom_brno = 0;
    $demand = 0;
    while ($warehouse = mysqli_fetch_array($warehousequery)) {

        if ($warehouse['demand_id'] != 0) {

            $demand = $demand + 1;

        }

        if (isset($warehouse['status']) && $warehouse['status'] == 0) {

            $produce = $produce + 1;

        } elseif (isset($warehouse['status']) && $warehouse['status'] == 1) {

            $shipped = $shipped + 1;

        } elseif (isset($warehouse['status']) && $warehouse['status'] == 2) {

            $stock = $stock + 1;

        } elseif (isset($warehouse['status']) && $warehouse['status'] == 3) {

            $showroom = $showroom + 1;

        } elseif (isset($warehouse['status']) && $warehouse['status'] == 5) {

            $showroom_brno = $showroom_brno + 1;

        }

    }

    ?>


    <div class="member-entry" style="margin-bottom: 0px; padding: 10px 10px 0;">

        <a class="member-img" style="width: 6%;">
            <img src="https://www.wellnesstrade.cz/admin/data/images/customer/<?= $product['connect_name'] ?>.png" width="90px" class="img-rounded" />
        </a>

        <div class="member-details" style="width: 92.9% !important;">
            <h4 style="float: left;">
                <?php if (isset($product['customer']) && $product['customer'] == 0) {echo $product['code'] . ': ';}?><?= $product['brand'] . ' ' . ucfirst($product['fullname']) ?>
                <small style="font-size: 65%;">	<i class="entypo-right-open-mini"></i>
                    <?php if (isset($product['active']) && $product['active'] == 'no') { ?><span style=" color: #d42020;"><?php } else { ?><span style=" color: #04a500;"><?php } ?>
                            <?php if (isset($product['customer']) && $product['customer'] == 0) { ?>
                                Sauna je nyní <strong><?php if (isset($product['active']) && $product['active'] == 'yes') { ?>aktivní<?php } else { ?>neaktivní<?php } ?></strong> a proto se <strong><?php if (isset($product['active']) && $product['active'] == 'yes') { ?>zobrazuje<?php } else { ?>nezobrazuje<?php } ?></strong> v sekci sklad.
                            <?php } elseif (isset($product['customer']) && $product['customer'] == 1) { ?>
                                Vířivka je nyní <strong><?php if (isset($product['active']) && $product['active'] == 'yes') { ?>aktivní<?php } else { ?>neaktivní<?php } ?></strong> a proto se <strong><?php if (isset($product['active']) && $product['active'] == 'yes') { ?>zobrazuje<?php } else { ?>nezobrazuje<?php } ?></strong> v sekci sklad.
                            <?php } elseif (isset($product['customer']) && $product['customer'] == 1) { ?>
                                Pergola je nyní <strong><?php if (isset($product['active']) && $product['active'] == 'yes') { ?>aktivní<?php } else { ?>neaktivní<?php } ?></strong> a proto se <strong><?php if (isset($product['active']) && $product['active'] == 'yes') { ?>zobrazuje<?php } else { ?>nezobrazuje<?php } ?></strong> v sekci sklad.
                            <?php } ?>
							</span></small>
            </h4>
            <div style="float:right;margin-top: 0px;padding-right: 0px;">

                <a href="pridat-variantu-produktu?id=<?= $product['id'] ?>" class="btn btn-green btn-sm btn-icon icon-left">
                    <i class="entypo-plus"></i>
                    Přidat variantu
                </a>
                <a href="upravit-produkt?id=<?= $product['id'] ?>" class="btn btn-primary btn-sm btn-icon icon-left">
                    <i class="entypo-pencil"></i>
                    Upravit
                </a>
                <a href="?action=remove&id=<?= $product['id'] ?>" class="btn btn-danger btn-sm btn-icon icon-left">
                    <i class="entypo-cancel"></i>
                    Smazat
                </a>
            </div>
            <div class="clear"></div>
            <!-- Details with Icons -->

            <div class="row info-list">


                <div class="col-sm-4" style="margin-top: 8px;">
                    <i class="entypo-right-open-mini"></i>
                   <?php if ($demands['max'] < 1) {
                       ?>Momentálně není <strong>žádná</strong> nevyřešená poptávka.<?php
                   } elseif (isset($demands['max']) && $demands['max'] == 1) {
                       ?>Momentálně je <strong><?= $demands['max'] ?></strong> nevyřešená poptávka.<?php
                   } elseif ($demands['max'] < 5) { ?>Momentálně jsou <strong><?= $demands['max'] ?></strong> nevyřešené poptávky.<?php
                   } else { ?>Momentálně je <strong><?= $demands['max'] ?></strong> nevyřešených poptávek.<?php
                   }?>
                </div>

                <div class="col-sm-4" style="margin-top: 8px;">
                    <i class="entypo-right-open-mini"></i>
                    Právě se <?php if ($realization['max'] < 1) {
                        ?>nerealizuje <strong>žádná</strong> poptávka.<?php
                    } elseif (isset($realization['max']) && $realization['max'] == 1) {
                        ?>realizuje <strong><?= $realization['max'] ?></strong> poptávka.<?php
                    } elseif ($realization['max'] < 5) {
                        ?>realizují <strong><?= $realization['max'] ?></strong> poptávky.<?php
                    } else {
                        ?>realizuje <strong><?= $realization['max'] ?></strong> poptávek.<?php
                    }?>
                </div>

                <div class="col-sm-4" style="margin-top: 8px;">
                    <i class="entypo-right-open-mini"></i>
                    <?php
                        if ($clients['max'] < 1) {
                            ?>Zatím nezakoupil <strong>žádný</strong> klient.<?php
                        } elseif (isset($clients['max']) && $clients['max'] == 1) {
                            ?>Již zakoupil <strong><?= $clients['max'] ?></strong> klient.<?php
                        } elseif ($clients['max'] < 5) {
                            ?>Již zakoupili <strong><?= $clients['max'] ?></strong> klienti.<?php
                        } else {
                            ?>Již zakoupilo <strong><?= $clients['max'] ?></strong> klientů.<?php } ?>
                </div>
                <br><br>
                <div style="margin-top: 20px; float:right;">
                    <a href="./virivky?category=<?= $product['connect_name'] ?>&customer=1"><button style="margin-right: 4px; margin-left: 16px;margin-top: -3px;height: 28px; line-height: 10px;" type="button" class="btn btn-default btn-sm"><span style="font-size: 17px;"><?= $demand ?></span> rezervováno</button></a>
                    <a href="./virivky?type=0&category=<?= $product['connect_name'] ?>"><button style="margin-right: 4px; margin-top: -3px; height: 28px; line-height: 10px;" type="button" class="btn btn-default btn-sm"><span style="font-size: 17px;"><?= $produce ?></span> zadáno do výroby</button></a>
                    <a href="./virivky?type=1&category=<?= $product['connect_name'] ?>"><button style="margin-right: 4px; margin-top: -3px;height: 28px; line-height: 10px;" type="button" class="btn btn-default btn-sm"><span style="font-size: 17px;"><?= $shipped ?></span> na cestě</button></a>
                    <a href="./virivky?type=2&category=<?= $product['connect_name'] ?>"><button style="margin-right: 4px; margin-top: -3px;height: 28px; line-height: 10px;" type="button" class="btn btn-default btn-sm"><span style="font-size: 17px;"><?= $stock ?></span> na skladě</button></a>
                    <a href="./virivky?type=3&category=<?= $product['connect_name'] ?>"><button style="margin-right: 4px; margin-top: -3px;height: 28px; line-height: 10px;" type="button" class="btn btn-default btn-sm"><span style="font-size: 17px;"><?= $showroom ?></span> na showroomu</button></a>
                </div>

            </div>

<!--            <br>-->
            <div class="row">
                <hr>

            <?php
                $select_variations = $mysqli->query("SELECT id, name FROM warehouse_products_types WHERE warehouse_product_id = '" . $product['id'] . "' ORDER BY name DESC");
                while ($type = mysqli_fetch_array($select_variations)) {


                    if(file_exists($_SERVER['DOCUMENT_ROOT'].'/admin/data/images/customer/'.$product['connect_name'].'-'.$type['name'].'.png')){

                        $image_path = '/admin/data/images/customer/'.$product['connect_name'].'-'.$type['name'].'.png';

                    }else{

                        $image_path = 'https://www.wellnesstrade.cz/data/assets/no-image-7.jpg';

                    }


                    ?>
                        <div class="col-sm-6">
                        <div class="well" style="background-color: #FCFCFC; padding: 6px 4px 4px;">
                            <img src="<?= $image_path ?>" height="40" style="float: left;">
                            <h4 style="float: left; margin-right: 20px; font-size: 15px; width: 120px;"><?= $type['name'] ?></h4>
                            <?php

                            $file_url = '/admin/data/demands/documents/' . returnpn($product['customer'], $product['connect_name']) . ' ' . $type['name'] . ' - Stavební příprava.pdf';

                            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $file_url)) {

                                ?>
                        <span style="width: 100px; float: left;">
                <a href="<?= $file_url ?>?t=<?= $currentDate->getTimestamp() ?>" target="_blank"
                   class="btn btn-info btn-md">
                    <i class="entypo-doc-text-inv" style="font-size: 14px;"></i>
                </a>
                <a href="./editace-produktu?action=preparation_remove&product_id=<?= $product['id'] ?>&type_id=<?= $type['id'] ?>"
                   class="btn btn-danger btn-md">
                    <i class="entypo-trash" style="font-size: 14px;"></i>
                </a>
                        </span>
                    <?php } else { ?>
                <span style="width: 100px; float: left;">
                    <a style=" float: left;" class="btn btn-default btn-md">
                        <i class="entypo-cancel-circled" style="font-size: 14px;"></i>

                    </a>
                </span>
                        <form role="form" method="post"
                              action="./editace-produktu?action=preparation&product_id=<?= $product['id'] ?>&type_id=<?= $type['id'] ?>"
                              enctype="multipart/form-data" style="display: inline;">
                            <input type="file" style="width: 100px; padding-top: 6px; display: inline-block;"
                                   class="form-control" name="file"
                                   id="field-file" placeholder="Placeholder">
                            <button type="submit" class="btn btn-green btn-icon icon-left btn-md"><i
                                        class="entypo-plus"></i> Nahrát
                            </button>
                        </form>
                            <?php }

                            if($client['email'] == 'becher@saunahouse.cz'){
                            ?>
                            <a href="./editace-produktu?action=remove_type&product_id=<?= $product['id'] ?>&type_id=<?= $type['id'] ?>"
                               class="btn btn-black btn-md">
                                <i class="entypo-trash" style="font-size: 14px;"></i>
                            </a>

                            <?php } ?>

                            <div style="clear: both"></div>
                        </div>
                        </div>

                    <?php


            }?>
            </div>

        </div>

    </div>



    <?php



}

?>


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


<script src="<?= $home ?>/admin/assets/js/jquery.validate.min.js"></script>

<?php include VIEW . '/default/footer.php'; ?>
