<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";

$id = $_REQUEST['id'];

$text = 'Přidat položku do kontejneru. Vyplňte zadané specifikace k produktu.';

$remove_button = 'Přidat';

$title = 'Přidat do kontejneru #' . $id;


$container_query = $mysqli->query("SELECT closed, brand, id_brand FROM containers WHERE id = '".$id."'")or die($mysqli->error);
$container = mysqli_fetch_assoc($container_query);


// todo brand Lovia + Swim SPA in one
if($container['brand'] == 'Swim SPA'){

    $demandsq = $mysqli->query("SELECT d.user_name, d.id FROM demands d, warehouse_products w WHERE (d.customer = 1 OR d.customer = 3) AND d.product = w.connect_name AND (w.brand = 'Swim SPA' OR w.brand = 'Lovia') AND d.status != 6 AND d.status != 5") or die($mysqli->error);

    $virivky_query_new = $mysqli->query("SELECT * FROM warehouse_products WHERE customer = 1 AND (brand = 'Swim SPA' OR brand = 'Lovia') ORDER BY brand");

}elseif($container['brand'] == 'Pergola'){

    $demandsq = $mysqli->query("SELECT d.user_name, d.id FROM demands d, warehouse_products w WHERE d.customer = 4 AND d.product = w.connect_name AND w.brand = 'Pergola' AND d.status != 6 AND d.status != 5") or die($mysqli->error);

    $virivky_query_new = $mysqli->query("SELECT * FROM warehouse_products WHERE customer = 4 AND brand = 'Pergola' ORDER BY brand");

}elseif($container['brand'] == 'Espoo Smart'){

    $demandsq = $mysqli->query("SELECT d.user_name, d.id FROM demands d, warehouse_products w WHERE d.customer = 0 AND d.product = w.connect_name AND w.brand = 'Espoo Smart' AND d.status != 6 AND d.status != 5") or die($mysqli->error);

    $virivky_query_new = $mysqli->query("SELECT * FROM warehouse_products WHERE customer = 0 AND brand = 'Espoo Smart' ORDER BY brand");

}elseif($container['brand'] == 'Espoo Deluxe'){

    $demandsq = $mysqli->query("SELECT d.user_name, d.id FROM demands d, warehouse_products w WHERE d.customer = 0 AND d.product = w.connect_name AND w.brand = 'Espoo Deluxe' AND d.status != 6 AND d.status != 5") or die($mysqli->error);

    $virivky_query_new = $mysqli->query("SELECT * FROM warehouse_products WHERE customer = 0 AND brand = 'Espoo Deluxe' ORDER BY brand");

}else{

    $demandsq = $mysqli->query("SELECT d.user_name, d.id FROM demands d, warehouse_products w WHERE (d.customer = 1 OR d.customer = 3) AND d.product = w.connect_name AND w.brand = '" . $container['brand'] . "' AND d.status != 6 AND d.status != 5") or die($mysqli->error);

    $virivky_query_new = $mysqli->query("SELECT * FROM warehouse_products WHERE customer = 1 AND brand = '".$container['brand']."' ORDER BY brand");

}



?>

	<div class="modal-dialog" style="width: 800px">
	<div class="modal-content">
			<div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

				<h4 class="modal-title"><?= $title ?></h4> </div>

			<div class="modal-body" style="padding: 36px 35px 20px 35px; text-align: center;">


				<form role="form" method="post" name="myform" action="editace-kontejneru?id=<?= $id ?>&action=add_demand">

					<div class="col-sm-6" style="width: 400px;">

						<select name="demand" class="select2" data-allow-clear="true" data-placeholder="Vyberte poptávku...">
								<option></option>
									<?php while ($dem = mysqli_fetch_array($demandsq)) {
    $find = $mysqli->query("SELECT id FROM containers_products WHERE demand_id = '" . $dem['id'] . "'");
    if (mysqli_num_rows($find) != 1) {
        ?><option value="<?= $dem['id'] ?>"><?= $dem['user_name'] ?></option><?php }}?>

						</select>

						</div>

                        <?php if(!empty($container['closed']) && $container['closed'] > 1){ ?>
                            <div class="form-group" style="float: left;width: 100%;">
                                <div class="col-sm-6">

                                        <label class="col-sm-5 control-label">Sériové číslo</label>

                                        <div class="col-sm-7">
                                            <input type="text" name="serial_number" class="form-control" value="">
                                        </div>

                                    </div>
                            </div>
                        <?php } ?>

						<button style="float: left;margin-left: -9px;    height: 42px;" type="submit" class="btn btn-green"> <i class="entypo-plus"></i> Přidat z poptávky </button>
					</form><div style="clear:both;"></div>
					<hr>
					<center><strong>nebo</strong></center>
					<hr>

					<!-- začátek specifikace začátek specifikace začátek specifikace -->
<form role="form" method="post" action="?action=add_to_container&id=<?= $id ?>&brand=<?= $container['brand'] ?>" class="form-horizontal form-groups-bordered validate" enctype="multipart/form-data">



	<div class="form-group" style="float: left; width: 100%;">
						<label class="col-sm-3 control-label"><?= $container['brand'] ?></label>

						<div class="col-sm-5">

							<select class="form-control" name="virivkatype" id="virivkatype">
                                <option value="">Vyberte položku</option>
								<?php

while ($virivka = mysqli_fetch_array($virivky_query_new)) {

    ?>
								<option value="<?= $virivka['connect_name'] ?>"><?php if ($virivka['brand'] != "") {echo $virivka['brand'] . ' ' . ucfirst($virivka['fullname']);} else {echo ucfirst($virivka['fullname']);}?></option><?php } ?>
							</select>

						</div>
					</div>

					<script type="text/javascript">

						jQuery(document).ready(function($)
						{

						$('#virivkatype').on('change', function() {


							var selected = this.value;

						   	$('.virivky_typy').hide( "slow");
						   	$('.params_virivky').hide( "slow");

							$('.virivka_'+selected).show( "slow");


							var selected_type = $('.virivka_'+selected+' .provedeni_'+selected).val();

							if(selected_type != ""){

							$('.params_'+selected_type+'_'+selected).show( "slow");

							}


						});


						});

						</script>



	<?php mysqli_data_seek($virivky_query_new, 0);

while ($virivka = mysqli_fetch_array($virivky_query_new)) {

    ?>
		<div class="virivky_typy virivka_<?= $virivka['connect_name'] ?>" style="display: none;">
			<div class="form-group" style="float: left; width: 100%;">
						<label class="col-sm-3 control-label">Provedení</label>

						<div class="col-sm-5">

							<select class="form-control provedeni_<?= $virivka['connect_name'] ?>" name="provedeni_<?= $virivka['connect_name'] ?>">
		<?php

    $options = '';

    $virivky_typy = $mysqli->query("SELECT * FROM warehouse_products_types WHERE warehouse_product_id = '" . $virivka['id'] . "'") or die($mysqli->error);
    while ($typ = mysqli_fetch_array($virivky_typy)) {

        $options = $options . '<option value="' . $typ['seo_url'] . '">' . $typ['name'] . '</option>';

    }?>
			<option value="">Žádná vybraná možnost</option>

		<?php

    echo $options;

    mysqli_data_seek($virivky_typy, 0);
    ?>

			</select>

						</div>
			</div>
		</div>

					<script type="text/javascript">

						jQuery(document).ready(function($)
						{

						$('.provedeni_<?= $virivka['connect_name'] ?>').on('change', function() {

							var selected = this.value;

						   	$('.params_virivky_<?= $virivka['connect_name'] ?>').hide( "slow");
							$('.params_'+selected+'_<?= $virivka['connect_name'] ?>').show( "slow");


						});


						});

					</script>

		<?php while ($typ = mysqli_fetch_array($virivky_typy)) { ?>

			<div class="params_virivky params_virivky_<?= $virivka['connect_name'] ?> params_<?= $typ['seo_url'] ?>_<?= $virivka['connect_name'] ?>" <?php if (!isset($param_type['value']) || $param_type['value'] != $typ['name']) { ?>style="display: none;"<?php } ?>>
				<?php

        $specs_query = $mysqli->query("SELECT *, s.id as id FROM specs s, warehouse_products_types_specs w WHERE w.spec_id = s.id AND w.type_id = '" . $typ['id'] . "' AND s.supplier = 1 GROUP BY s.id") or die($mysqli->error);

        while ($specs = mysqli_fetch_array($specs_query)) {

            if (isset($specs['type']) && $specs['type'] == 1) {

                $paramsquery = $mysqli->query("SELECT *, p.id as id FROM specs_params p, warehouse_products_types_specs w  WHERE p.spec_id = '" . $specs['id'] . "' AND w.spec_param_id = p.id AND w.type_id = '" . $typ['id'] . "' GROUP by p.id") or die($mysqli->error);

                ?>

						<div class="form-group" style="float: left; width: 100%;">
							<label class="col-sm-3 control-label"><?= $specs['name'] ?></label>

							<div class="col-sm-5">

								<select name="<?= $virivka['connect_name'] ?>_<?= $typ['seo_url'] ?>_<?= $specs['seoslug'] ?>" class="form-control">
									<?php
                $selected = false;
                $options = '';

                while ($params = mysqli_fetch_assoc($paramsquery)) {

                    $selected_echo = "";
                    if (isset($params['choosed']) && $params['choosed'] == 1) {$selected_echo = 'selected';
                        $selected = true;}

                    $options = $options . '<option value="' . $params['option'] . '" ' . $selected_echo . '>' . $params['option'] . '</option>';

                }?>
									<option value="" <?php if ($selected != true) {echo 'selected';}?>>Žádná vybraná možnost</option>

									<?= $options ?>
								</select>

							</div>
						</div>


						<?php } elseif($specs['type'] == 0) {

                $paramsquery = $mysqli->query("SELECT * FROM warehouse_products_types_specs WHERE spec_id = '" . $specs['id'] . "' AND type_id = '" . $typ['id'] . "' order by spec_param_id desc") or die($mysqli->error);

                ?>

							<div class="form-group" style="float: left; width: 100%;">
						<label class="col-sm-3 control-label"><?= $specs['name'] ?></label>
						<div class="col-sm-5">


							<?php
                $selected = false;
                while ($params = mysqli_fetch_array($paramsquery)) {

                    if (isset($params['spec_param_id']) && $params['spec_param_id'] == 1) {$value = 'Ano';} else { $value = 'Ne';}

                    ?>
							<div class="radio" style="width: 100px; float: left;">
								<label>
									<input type="radio" name="<?= $virivka['connect_name'] ?>_<?= $typ['seo_url'] ?>_<?= $specs['seoslug'] ?>" value="<?= $value ?>" <?php if (isset($params['choosed']) && $params['choosed'] == 1 && !$selected) {$selected = true;
                        echo 'checked';}?>><?= $value ?>
								</label>
							</div>

							<?php } ?>

						</div>
					</div>


					<?php } elseif($specs['type'] == 2) {

                        ?>
                        <div class="form-group" style="margin-bottom: 8px;">
                            <label class="col-sm-4 control-label pergspec" style="line-height: 22px; padding-top: 0;"><?= $specs['name'] ?></label>
                            <div class="col-sm-8">
                                <?php
                                $selected = false;

                                    ?>
                                        
                                    <div class="radio" style="padding-left: 0; padding-top: 0;">
                                        <label>
                                            <input 
                                                   type="text"
                                                   name="<?= $virivka['connect_name'] ?>_<?= $typ['seo_url'] ?>_<?= $specs['seoslug'] ?>"
                                                   class="form-control generate_text" value="" style="width: 100%;">
                                        </label>
                                    </div>

                            </div>

                        </div>

                    <?php } ?>



						<?php } ?>

			</div>

		<?php } ?>

		<?php } ?>

    <?php if(!empty($container['closed']) && $container['closed'] > 1){

        ?>
        <div class="form-group" style="float: left;width: 100%;">
            <div class="col-sm-8">

                <label class="col-sm-5 control-label">Sériové číslo</label>

                <div class="col-sm-7">
                    <input type="text" name="serial_number" class="form-control" value="">
                </div>

            </div>
        </div>
        <?php
    }?>
	<!-- konec specifikace konec specifikace konec specifikace -->
         <div style="clear:both;"></div>
<div class="modal-footer" style="text-align:left;"> <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>

	<div style="float: right;"><button type="submit" class="btn btn-primary btn-icon icon-left"><?= $remove_button ?>
					<i class="entypo-plus"></i></button></div>

	</div>
</form>


			</div>

	<link rel="stylesheet" href="<?= $home ?>/admin/assets/js/select2/select2-bootstrap.css">
	<link rel="stylesheet" href="<?= $home ?>/admin/assets/js/select2/select2.css">
	<!-- Bottom Scripts -->
	  <script src="<?= $home ?>/admin/assets/js/gsap/TweenMax.min.js"></script>
	<script src="<?= $home ?>/admin/assets/js/gsap/main-gsap.js"></script>
	<script src="<?= $home ?>/admin/assets/js/jquery-ui/js/jquery-ui-1.10.3.minimal.min.js"></script>
	<script src="<?= $home ?>/admin/assets/js/bootstrap.js"></script>
	<script src="<?= $home ?>/admin/assets/js/joinable.js"></script>
	<script src="<?= $home ?>/admin/assets/js/resizeable.js"></script>
	<script src="<?= $home ?>/admin/assets/js/neon-api.js"></script>
	<script src="<?= $home ?>/admin/assets/js/select2/select2.min.js"></script>
	<script src="<?= $home ?>/admin/assets/js/neon-chat.js"></script>
	<script src="<?= $home ?>/admin/assets/js/neon-custom.js"></script>
