<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$id = $_REQUEST['id'];

$hottubquery = $mysqli->query('SELECT * FROM warehouse_products WHERE id="' . $id . '"') or die($mysqli->error);
$hottub = mysqli_fetch_assoc($hottubquery);

$pagetitle = "Upravit produkt";

$bread1 = "Editace produktů";
$abread1 = "editace-produktu";

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "edit") {

    $name = odkazy($_POST['connect_name']);

    $insert = $mysqli->query("UPDATE warehouse_products SET fullname = '" . $_POST['fullname'] . "', brand = '" . $_POST['brand'] . "', connect_name = '$name', code = '" . $_POST['code'] . "', customer = '" . $_POST['optionsRadios'] . "', active = '" . $_POST['status'] . "' WHERE id = '$id'");

    $path = $_SERVER['DOCUMENT_ROOT'] . "/admin/data/images/customer/" . $hottub['connect_name'] . ".png";

    if (file_exists($path) && $name != $hottub['connect_name']) {

        rename($_SERVER['DOCUMENT_ROOT'] . "/admin/data/images/customer/" . $hottub['connect_name'] . ".png", $_SERVER['DOCUMENT_ROOT'] . "/admin/data/images/customer/" . $name . ".png");

    } elseif (!file_exists($path) && $_FILES['picture']['size'] != 0 && $_FILES['picture']['error'] == 0) {

        $im = new imagick($_FILES['picture']['tmp_name']);
        $im->scaleImage(200, 200, true);
        $im->writeImage($_SERVER['DOCUMENT_ROOT'] . '/admin/data/images/customer/' . $name .'.png');

    }

    $types_query = $mysqli->query("SELECT * FROM warehouse_products_types WHERE warehouse_product_id = '$id'") or die($mysqli->error);

    while ($type = mysqli_fetch_array($types_query)) {


        $path = $_SERVER['DOCUMENT_ROOT'] . "/admin/data/images/customer/" . $hottub['connect_name'] . "-". $type['name'] .".png";
        if (file_exists($path) && $name != $hottub['connect_name']) {

            rename($_SERVER['DOCUMENT_ROOT'] . "/admin/data/images/customer/" . $hottub['connect_name'] . "-". $type['name'] .".png", $_SERVER['DOCUMENT_ROOT'] . "/admin/data/images/customer/" . $name . "-".$type['name'].".png");

        } elseif (!file_exists($path) && $_FILES['picture_'.$type['id']]['size'] != 0 && $_FILES['picture_'.$type['id']]['error'] == 0) {
            $im = new imagick($_FILES['picture_'.$type['id']]['tmp_name']);
            $im->scaleImage(200, 200, true);
            $im->writeImage($_SERVER['DOCUMENT_ROOT'] . '/admin/data/images/customer/' . $name .'-'.$type['name'].'.png');
        }

        $remove_params = $mysqli->query("DELETE FROM warehouse_products_types_specs WHERE type_id = '" . $type['id'] . "'") or die($mysqli->error);

        $type_name = $type['seo_url'];

        $type_price = $_POST[$type_name . '_price'];

        $update_type = $mysqli->query("UPDATE warehouse_products_types SET price = '$type_price' WHERE id = '" . $type['id'] . "'") or die($mysqli->error);

        //echo '<br><br>'.$type_name.'<br><br>';

        $specs_query = $mysqli->query("SELECT * FROM specs") or die($mysqli->error);

        while ($spec = mysqli_fetch_array($specs_query)) {

            $spec_id = $spec['id'];

            if (isset($_POST[$type_name . '_spec_' . $spec_id]) && $_POST[$type_name . '_spec_' . $spec_id] == 'yes') {

                //echo '<strong>'.$spec['name'].'</strong><br>';

                if (isset($spec['type']) && $spec['type'] == 0) {

                    if (isset($_POST[$type_name . '_spec_' . $spec_id . '_param_yes']) && $_POST[$type_name . '_spec_' . $spec_id . '_param_yes'] == 'yes') {

                        //echo ' - ANO!!! -> '.$_POST[$type_name.'_price_spec_'.$spec_id.'_yes'].'<br>';

                        $param_price = $_POST[$type_name . '_price_spec_' . $spec_id . '_yes'];

                        $param_default = 0;

                        if ($_POST[$type_name . '_spec_default_' . $spec_id] == 'yes') {

                            //echo ' ------ BINGO ^^^ DEFAULT<br>';

                            $param_default = 1;

                        }

                        // INSERT INSERT INSERT INSERT INSERT INSERT INSERT

                        $remove_params = $mysqli->query("INSERT INTO warehouse_products_types_specs (type_id, spec_id, spec_param_id, price, choosed) VALUES ('" . $type['id'] . "', '" . $spec['id'] . "', '1', '$param_price', '$param_default')") or die($mysqli->error);

                    }

                    if (isset($_POST[$type_name . '_spec_' . $spec_id . '_param_no']) && $_POST[$type_name . '_spec_' . $spec_id . '_param_no'] == 'yes') {

                        //echo ' - NE!!! -> '.$_POST[$type_name.'_price_spec_'.$spec_id.'_no'].'<br>';

                        $param_price = $_POST[$type_name . '_price_spec_' . $spec_id . '_no'];

                        $param_default = 0;

                        if ($_POST[$type_name . '_spec_default_' . $spec_id] == 'no') {

                            //echo ' ------ BINGO ^^^ DEFAULT<br>';

                            $param_default = 1;

                        }

                        // INSERT INSERT INSERT INSERT INSERT INSERT INSERT

                        $remove_params = $mysqli->query("INSERT INTO warehouse_products_types_specs (type_id, spec_id, spec_param_id, price, choosed) VALUES ('" . $type['id'] . "', '" . $spec['id'] . "', '0', '$param_price', '$param_default')") or die($mysqli->error);

                    }

                } else {

                    $params_query = $mysqli->query("SELECT * FROM specs_params WHERE spec_id = '" . $spec['id'] . "'") or die($mysqli->error);
                    while ($param = mysqli_fetch_array($params_query)) {

						$param_id = $param['id'];

                        if (isset($_POST[$type_name . '_param_' . $param_id]) && $_POST[$type_name . '_param_' . $param_id] == 'yes') {

                            //echo ' - '.$param['option'].' -> '.$_POST[$type_name.'_price_'.$param_id].'<br>';

                            $param_price = $_POST[$type_name . '_price_' . $param_id];

                            $param_default = 0;

                            if ($_POST[$type_name . '_spec_default_' . $spec_id] == $param_id) {

                                //echo ' ------ BINGO ^^^ DEFAULT<br>';

                                $param_default = 1;

                            }

                            // INSERT INSERT INSERT INSERT INSERT INSERT INSERT

                            $remove_params = $mysqli->query("INSERT INTO warehouse_products_types_specs (type_id, spec_id, spec_param_id, price, choosed) VALUES ('" . $type['id'] . "', '" . $spec['id'] . "', '" . $param['id'] . "', '$param_price', '$param_default')") or die($mysqli->error);

                        }

                    }

                }

                //echo '<br>';

            }
        }
    }

//echo '<br><br>lol';

    Header("Location:https://www.wellnesstrade.cz/admin/pages/warehouse/editace-produktu?success=add");
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "removefile") {

    unlink("../../data/images/customer/" . $_REQUEST['file_name'] . ".png");

    Header("Location:https://www.wellnesstrade.cz/admin/pages/warehouse/upravit-produkt?id=" . $hottub['id']);
}

$virivkyquery = $mysqli->query("SELECT * FROM warehouse_products WHERE active = 'yes' AND customer = 1 ORDER BY brand");

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

<style>


    input[type="checkbox"]:checked + label {
        color: black;
        font-weight: bold;
    }

    input[type="checkbox"] + label { line-height: 22px !important; }

    .option-line {
        border-bottom: 1px solid #efefef;
        padding: 0px 2px !important;
        margin: 0 16px !important;
        }

    .option-line:hover { background-color: #f5f5f5;}

</style>

<form role="form" method="post" name="myform" class="form-horizontal form-groups-bordered validate" action="upravit-produkt?action=edit&id=<?= $id ?>" enctype="multipart/form-data">
<input type="hidden" name="length" value="14">
	<div class="row">

		<div class="col-md-12">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						<?= $pagetitle ?>
					</div>

				</div>

						<div class="panel-body">

				<div class="form-group">
						<label class="col-sm-3 control-label">Druh</label>
						<div class="col-sm-5">
							<div class="radio" style="width: 100px; float: left;">
								<label>
									<input type="radio" name="optionsRadios" value="1" class="virivkaradio" <?php if (isset($hottub['customer']) && $hottub['customer'] == 1) {echo 'checked';}?>>Vířivka
								</label>
							</div>
							<div class="radio" style="width: 100px;float: left;">
								<label>
									<input type="radio" name="optionsRadios" value="0" class="saunaradio" <?php if (isset($hottub['customer']) && $hottub['customer'] == 0) {echo 'checked';}?>>Sauna
								</label>
							</div>
                            <div class="radio" style="width: 100px;float: left;">
                                <label>
                                    <input type="radio" name="optionsRadios" value="4" class="pergolaradio" <?php if (isset($hottub['customer']) && $hottub['customer'] == 4) {echo 'checked';}?>>Pergola
                                </label>
                            </div>

						</div>
					</div>

					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Název produktu</label>

						<div class="col-sm-5">
							<input type="text" class="form-control" id="field-2" name="fullname" value="<?= $hottub['fullname'] ?>">
						</div>
					</div>

					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Propojovací jméno</label>

						<div class="col-sm-5">
							<input type="text" class="form-control" id="field-2" name="connect_name" value="<?= $hottub['connect_name'] ?>" readonly>
						</div>
					</div>
					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Značka produktu</label>

						<div class="col-sm-5">
							<input type="text" class="form-control" id="field-2" name="brand" value="<?= $hottub['brand'] ?>">
						</div>
					</div>
						<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Kód produktu</label>

						<div class="col-sm-5">
							<input type="text" class="form-control" id="field-2" name="code" value="<?= $hottub['code'] ?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Stav</label>
						<div class="col-sm-5">
							<div class="radio" style="width: 100px; float: left;">
								<label>
									<input type="radio" name="status" value="yes" <?php if (isset($hottub['active']) && $hottub['active'] == 'yes') {echo 'checked';}?>>Aktivní
								</label>
							</div>
							<div class="radio" style="width: 100px;float: left;">
								<label>
									<input type="radio" name="status" value="no" <?php if (isset($hottub['active']) && $hottub['active'] == 'no') {echo 'checked';}?>>Neaktivní
								</label>
							</div>

						</div>
					</div>
					<div class="form-group" style="margin-bottom: 18px;">
						<label for="field-1" class="col-sm-3 control-label">Obrázek (pouze png)</label>
						<div class="col-sm-5">
						<?php
                            $path = "../../data/images/customer/" . $hottub['connect_name'] . ".png";
                            if (file_exists($path)) { ?>
                                <img src="../../data/images/customer/<?= $hottub['connect_name'] ?>.png" width="60">

                                    <a href="upravit-produkt?id=<?= $hottub['id'] ?>&action=removefile&file_name=<?= $hottub['connect_name'] ?>" class="btn btn-danger btn-sm btn-icon icon-left">
                                    <i class="entypo-cancel"></i>
                                    Smazat
                                </a>
                            <?php } else { ?>
                                    <input type="file" class="form-control" name="picture" id="field-file" placeholder="Placeholder">

                            <?php } ?>
						</div>
					</div>

					<hr>


					<?php

$types_query = $mysqli->query("SELECT * FROM warehouse_products_types WHERE warehouse_product_id = '$id' ORDER BY name DESC") or die($mysqli->error);

while ($type = mysqli_fetch_array($types_query)) { ?>

					<div class="col-sm-12 well" style="float: left; width: 49%; margin: 0.5%;">
<!--                        <h2 class="col-sm-6" style="width: 200px;position: fixed;transform: rotate(-90deg);left: 173px;height: 30px;top: 40px;">--><?//echo ucfirst($type['name']); ?><!--</h2>-->
						<div class="col-sm-12" style="float: left;">
						<h2 class="col-sm-3" style="margin: 2px 0 14px;"><?= ucfirst($type['name']) ?></h2>
						<div class="col-sm-3">
							<input type="text" class="form-control" style="width: 50%; float: right;" id="<?= $type['seo_url'] ?>_price" name="<?= $type['seo_url'] ?>_price" value="<?= $type['price'] ?>">
							<label for="<?= $type['seo_url'] ?>_price" class="control-label" style="float: right; margin-right: 14px;"> Cena:</label>
							</div>

                        <label for="field-1" class="col-sm-2 control-label">Obrázek .png</label>
                        <div class="col-sm-3">
                            <?php
                            $path = "../../data/images/customer/" . $hottub['connect_name'] . "-" . $type['name'] . ".png";
                            if (file_exists($path)) { ?>
                                <img src="../../data/images/customer/<?= $hottub['connect_name'] ?>-<?= $type['name'] ?>.png" width="60">
                                <a href="upravit-produkt?id=<?= $hottub['id'] ?>&action=removefile&file_name=<?= $hottub['connect_name'] ?>-<?= $type['name'] ?>" class="btn btn-danger btn-sm btn-icon icon-left">
                                    <i class="entypo-cancel"></i>
                                    Smazat
                                </a>
                            <?php } else { ?>
                                <input type="file" class="form-control" name="picture_<?= $type['id'] ?>" id="field-file" placeholder="Placeholder">

                            <?php } ?>
                        </div>
                        </div>

                        <?php

    $specs_query = $mysqli->query("SELECT *, s.id as id FROM specs s LEFT JOIN warehouse_products_types_specs w ON w.spec_id = s.id AND w.type_id = '" . $type['id'] . "' AND s.id != 5 GROUP BY s.id") or die($mysqli->error);

    while ($spec = mysqli_fetch_array($specs_query)) {

        if(empty($spec['brand']) || !in_array($hottub['brand'], json_decode($spec['brand']))){ continue; }
        ?>
							<div class="well col-sm-3" style="width: 100%; padding: 6px 0px 16px; background-color: #FFF;">

						<div class="form-group option-line">
							<div class="col-sm-11">
								<input class="checkbox form-control" name="<?= $type['seo_url'] ?>_spec_<?= $spec['id'] ?>" id="<?= $type['seo_url'] ?>_spec_<?= $spec['id'] ?>" value="yes" type="checkbox" style="cursor: pointer;width: 14px;float: left;margin-right: 9px; margin-top: 9px;"  <?php if ($spec['spec_id'] != "") {echo 'checked';}?>/>
								<label for="<?= $type['seo_url'] ?>_spec_<?= $spec['id'] ?>" class="control-label" style="cursor: pointer; text-align: left;"><h4><?= $spec['name'] ?></h4></label>
							</div>

							<div class="col-sm-1">
								 <input class="radio form-control" name="<?= $type['seo_url'] ?>_spec_default_<?= $spec['id'] ?>" value="yes" type="radio" style="cursor: pointer; width: 18px;" <?php if (isset($spec['spec_id']) && $spec['spec_id'] == "") {} else {echo 'checked';}?>/>
							</div>
						</div>



							<?php

        if (isset($spec['type']) && $spec['type'] == 0) {

            $select_specific = $mysqli->query("SELECT * FROM warehouse_products_types_specs WHERE spec_id = '" . $spec['id'] . "' AND type_id = '" . $type['id'] . "' AND spec_param_id = 1") or die($mysqli->error);
            $specific = mysqli_fetch_array($select_specific);

            ?>


							<div class="form-group option-line">

								<div class="col-sm-9">
									<input class="checkbox form-control" id="<?= $type['seo_url'] ?>_spec_<?= $spec['id'] ?>_param_yes" name="<?= $type['seo_url'] ?>_spec_<?= $spec['id'] ?>_param_yes" value="yes" type="checkbox" style="cursor: pointer;width: 14px;float: left;margin-right: 9px;" <?php if ($specific['spec_id'] != "") {echo 'checked';}?>/>

									<label for="<?= $type['seo_url'] ?>_spec_<?= $spec['id'] ?>_param_yes" class="control-label" style=" cursor: pointer; max-width: 86%; text-align: left;">Ano</label>

								</div>
								<div class="col-sm-2" style="padding-left: 0;">
								<input type="text" class="form-control" id="field-2" name="<?= $type['seo_url'] ?>_price_spec_<?= $spec['id'] ?>_yes" value="<?= $specific['price'] ?>">
							</div>
							<div class="col-sm-1">
								<input class="radio form-control" name="<?= $type['seo_url'] ?>_spec_default_<?= $spec['id'] ?>" value="yes" type="radio" style="cursor: pointer; width: 18px;" <?php if (isset($specific['choosed']) && $specific['choosed'] == 1) {echo 'checked';}?>/>
							</div>
							</div>



							<?php

            $select_specific2 = $mysqli->query("SELECT * FROM warehouse_products_types_specs WHERE spec_id = '" . $spec['id'] . "' AND type_id = '" . $type['id'] . "' AND spec_param_id = 0") or die($mysqli->error);
            $specific2 = mysqli_fetch_array($select_specific2);

            ?>

							<div class="form-group option-line">


							<div class="col-sm-9">
								<input class="checkbox form-control" id="<?= $type['seo_url'] ?>_spec_<?= $spec['id'] ?>_param_no" name="<?= $type['seo_url'] ?>_spec_<?= $spec['id'] ?>_param_no" value="yes" type="checkbox" style="cursor: pointer;width: 14px;float: left;margin-right: 9px;" <?php if ($specific2['spec_id'] != "") {echo 'checked';}?>/>

								<label for="<?= $type['seo_url'] ?>_spec_<?= $spec['id'] ?>_param_no" class="control-label" style=" cursor: pointer; max-width: 86%; text-align: left;">Ne</label>

							</div>
							<div class="col-sm-2" style="padding-left: 0;">
							<input type="text" class="form-control" id="field-2" name="<?= $type['seo_url'] ?>_price_spec_<?= $spec['id'] ?>_no" value="<?= $specific2['price'] ?>">
						</div>
						<div class="col-sm-1">
							<input class="radio form-control" name="<?= $type['seo_url'] ?>_spec_default_<?= $spec['id'] ?>" value="no" type="radio" style="cursor: pointer; width: 18px;" <?php if (isset($specific2['choosed']) && $specific2['choosed'] == 1) {echo 'checked';}?>/>
						</div>
						</div>



							<?php } else {

            $params_query = $mysqli->query("SELECT *, s.id as id FROM specs_params s LEFT JOIN warehouse_products_types_specs w ON w.spec_param_id = s.id AND w.type_id = '" . $type['id'] . "' WHERE s.spec_id = '" . $spec['id'] . "' AND s.active != 0 ORDER BY s.option ASC") or die($mysqli->error);
            while ($param = mysqli_fetch_array($params_query)) {

                ?>
                <div class="form-group option-line">
                    <div class="col-sm-9">
                        <input class="checkbox form-control" id="<?= $type['seo_url'] ?>_param_<?= $param['id'] ?>" name="<?= $type['seo_url'] ?>_param_<?= $param['id'] ?>" value="yes" type="checkbox" style="cursor: pointer;width: 14px;float: left;margin-right: 9px;" <?php if ($param['spec_id'] != "") {echo 'checked';}?>/>

                        <label for="<?= $type['seo_url'] ?>_param_<?= $param['id'] ?>" class="control-label" style=" cursor: pointer; max-width: 86%; text-align: left;"><?= $param['option'] ?></label>
                    </div>
                    <div class="col-sm-2" style="padding-left: 0;">
                    <input type="text" class="form-control" id="field-2" name="<?= $type['seo_url'] ?>_price_<?= $param['id'] ?>" value="<?= $param['price'] ?>">
                </div>
                    <div class="col-sm-1">
                        <input class="radio form-control" name="<?= $type['seo_url'] ?>_spec_default_<?= $spec['id'] ?>" value="<?= $param['id'] ?>" type="radio" style="cursor: pointer; width: 18px;" <?php if (isset($param['choosed']) && $param['choosed'] == 1) {echo 'checked';}?>/>
                    </div>
                </div>
            <?php
            }

        }

        echo '</div>';

    }

    echo '</div>';

}
?>
					<hr>
		</div>
	</div>
    <center>
	    <div class="form-group default-padding" style="margin-left: -100px;">
            <a href="./virivky?id=<?= $_REQUEST['id'] ?>"><button type="button" class="btn btn-primary">Zpět</button></a>
            <button type="submit" class="btn btn-success"><?= $pagetitle ?></button>
	    </div>
    </center>
</form>
<footer class="main">

	&copy; <?= date("Y") ?> <span style=" float:right;"><?php
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$total_time = round(($finish - $start), 4);

echo 'PHP '.PHP_VERSION.' | Page generated in ' . $total_time . ' seconds.';?></span>

</footer>
</div>
	</div>

<?php include VIEW . '/default/footer.php'; ?>


