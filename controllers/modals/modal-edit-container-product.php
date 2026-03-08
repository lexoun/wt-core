<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";
$start = microtime(true);

$id = $_REQUEST['id'];

$cpars_query = $mysqli->prepare('SELECT * FROM containers_products_specs_bridge WHERE specs_id = ? and client_id = ?');
$spec_id = null;
$product_id = null;
$cpars_query->bind_param('ii', $spec_id, $product_id);

$params_query = $mysqli->prepare('SELECT *, p.id as id FROM specs_params p, warehouse_products_types_specs w WHERE p.spec_id = ? AND w.spec_param_id = p.id AND w.type_id = ? GROUP by p.id');
$spec_id = null;
$type_id = null;
$params_query->bind_param('ii', $spec_id, $type_id);

$container_query = $mysqli->query("SELECT p.demand_id, p.product, w.serial_number, w.purchase_price, d.user_name, w.demand_id as has_demand, p.container_id, cont.brand FROM containers_products p LEFT JOIN containers cont ON cont.id = p.container_id LEFT JOIN warehouse w ON w.id = p.warehouse_id LEFT JOIN demands d ON d.id = p.demand_id WHERE p.id = '$id'") or die($mysqli->error);
$container = mysqli_fetch_array($container_query);

$remove_button = 'Upravit';

$title = 'Upravit položku #' . $id;


// todo brand Lovia + Swim SPA in one
if($container['brand'] == 'Swim SPA'){

    $demandsq = $mysqli->query("SELECT d.user_name, d.id FROM demands d, warehouse_products w WHERE (d.customer = 1 OR d.customer = 3) AND d.product = w.connect_name AND (w.brand = 'Swim SPA' OR w.brand = 'Lovia') AND d.status != 6 AND d.status != 5") or die($mysqli->error);

    $virivky_query_new = $mysqli->query("SELECT * FROM warehouse_products WHERE customer = 1 AND (brand = 'Swim SPA' OR brand = 'Lovia') ORDER BY brand");

}elseif($container['brand'] == 'Pergola'){

    $demandsq = $mysqli->query("SELECT d.user_name, d.id FROM demands d, warehouse_products w WHERE d.customer = 4 AND d.product = w.connect_name AND w.brand = 'Pergola' AND d.status != 6 AND d.status != 5") or die($mysqli->error);

    $virivky_query_new = $mysqli->query("SELECT * FROM warehouse_products WHERE customer = 4 AND brand = 'Pergola' ORDER BY brand");

}elseif($container['brand'] == 'Espoo Deluxe'){

    $demandsq = $mysqli->query("SELECT d.user_name, d.id FROM demands d, warehouse_products w WHERE d.customer = 0 AND d.product = w.connect_name AND w.brand = 'Espoo Deluxe' AND d.status != 6 AND d.status != 5") or die($mysqli->error);

    $virivky_query_new = $mysqli->query("SELECT * FROM warehouse_products WHERE customer = 0 AND brand = 'Espoo Deluxe' ORDER BY brand");

}elseif($container['brand'] == 'Espoo Smart'){

    $demandsq = $mysqli->query("SELECT d.user_name, d.id FROM demands d, warehouse_products w WHERE d.customer = 0 AND d.product = w.connect_name AND w.brand = 'Espoo Smart' AND d.status != 6 AND d.status != 5") or die($mysqli->error);

    $virivky_query_new = $mysqli->query("SELECT * FROM warehouse_products WHERE customer = 0 AND brand = 'Espoo Smart' ORDER BY brand");

}else{

    $demandsq = $mysqli->query("SELECT d.user_name, d.id FROM demands d, warehouse_products w WHERE (d.customer = 1 OR d.customer = 3) AND d.product = w.connect_name AND w.brand = '" . $container['brand'] . "' AND d.status != 6 AND d.status != 5") or die($mysqli->error);

    $virivky_query_new = $mysqli->query("SELECT * FROM warehouse_products WHERE customer = 1 AND brand = '".$container['brand']."' ORDER BY brand");

}

?>

<div class="modal-dialog" style="width: 1300px;">


	<div class="modal-content">
			<div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

				<h4 class="modal-title"><?= $title ?></h4> </div>

			<div class="modal-body" style="padding: 15px; text-align: left;">

                <div id="loader" style="display: none; text-align: center; min-height: 500px;"><img src="https://www.wellnesstrade.cz/admin/assets/images/loader_backinout.gif" width="80%" style="margin: 0 auto;"></div>
<form id="edit_form" role="form" method="post" action="https://www.wellnesstrade.cz/admin/pages/warehouse/editace-kontejneru?action=edit_container_product&id=<?= $id ?>" class="form-horizontal form-groups-bordered validate" enctype="multipart/form-data">


	<div class="form-group">
		<div class="col-sm-6">
            <label class="col-sm-5 control-label">Sériové číslo</label>
            <div class="col-sm-7">
                <input type="text" name="serial_number" class="form-control" value="<?= $container['serial_number'] ?>">
            </div>
        </div>
        <?php /*
        <div class="col-sm-6">
            <label class="col-sm-6 control-label">Pořizovací cena</label>
            <div class="col-sm-6">
                <input type="text" name="purchase_price" class="form-control" value="<?= $container['purchase_price'] ?>">
            </div>
        </div>

        */?>
    </div>

<script type="text/javascript">
						jQuery(document).ready(function($)
						{




                            $('.paid_input').change(function() {

						        $(this).closest('.form-group').find('textarea').toggle();

                            });



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

                                $("#current_provedeni").load("/admin/controllers/modals/load-new.php?id="+id+"&connect_name="+selected);

                            });



							$('.provedeni').on('change', function() {

								var id = this.id;
								var selected = this.value;

							   	$('.params_virivky_'+id).hide( "slow");
								$('.params_'+selected+'_'+id).show( "slow");

							});


                            $("#save_data").click(function(e) {

                                $("#edit_form > *").hide();
                                $("#loader").show();

                                event.preventDefault();
                                e.preventDefault();

                                var url = "https://www.wellnesstrade.cz/admin/pages/warehouse/editace-kontejneru?action=edit_container_product&id=<?= $id ?>"; // the script where you handle the form input.

                                $.ajax({
                                    type: "POST",
                                    url: url,
                                    data: $("#edit_form").serialize(), // serializes the form's elements.
                                    success: function(data) {

                                        // console.log(data);

                                        $("#<?= $id ?>").load("/admin/controllers/modals/container?id=<?= $container['container_id'] ?> #<?= $id ?> > *");

                                        $("#loader").fadeOut(400, function() {

                                            $(this).html('<img src="https://www.wellnesstrade.cz/admin/assets/images/tick-confirmed.gif" width="60%" style="margin: 40px auto;">').fadeIn(400);

                                        });

                                    }
                                });

                                return false; // avoid to execute the actual submit of the form.
                            });

						});
					</script>


<div class="form-group">
	<div class="col-sm-12">

        <select name="demand" class="select2" data-allow-clear="true" data-placeholder="Vyberte poptávku...">
            <option value="0">--- žádná poptávka</option>

            <?php if (isset($container['has_demand']) && $container['has_demand'] != 0) {
                $get_user = $mysqli->query("SELECT user_name, DATE_FORMAT(realization, '%d. %m. %Y') as realizationformated FROM demands WHERE id = '".$container['has_demand']."'")or die($mysqli->error);
                $user = mysqli_fetch_assoc($get_user);
            ?>
                    <option value="<?= $container['has_demand'] ?>" selected><?= $user['user_name'] ?></option>
                <?php
            } elseif (isset($container['demand_id']) && $container['demand_id'] != 0) {
                ?>
                    <option value="<?= $container['demand_id'] ?>" selected><?= $container['user_name'] ?></option>
                <?php
            }
                while ($dem = mysqli_fetch_array($demandsq)) {

    $find = $mysqli->query("SELECT id FROM containers_products WHERE demand_id = '" . $dem['id'] . "'");
    if (mysqli_num_rows($find) != 1) {

        ?>
            <option value="<?= $dem['id'] ?>"><?= $dem['user_name'] ?></option><?php }}?>
        </select>
    </div>
</div>

    <hr style="clear: both;">

    <div class="form-group">
    <div class="col-sm-6">
						<label class="col-sm-3 control-label" style="width: 20%; color: #555; font-weight: 500;">Položka</label>

						<div class="col-sm-9" style="width: 35%;">

							<select class="form-control" name="virivkatype" id="virivkatype" <?php if(!empty($container['has_demand'])){ echo 'disabled'; } ?>>
								<?php

$get_provedeni = $mysqli->query("SELECT t.id FROM containers_products_specs_bridge c, warehouse_products_types t, warehouse_products p WHERE c.client_id = '" . $id . "' AND c.specs_id = 5 AND t.warehouse_product_id = p.id AND p.connect_name = '" . $container['product'] . "' AND t.name = c.value") or die($mysqli->error);
$provedeni = mysqli_fetch_array($get_provedeni);

while ($virivka = mysqli_fetch_array($virivky_query_new)) { ?>
								<option value="<?= $virivka['connect_name'] ?>" <?php if (isset($container['product']) && $container['product'] == $virivka['connect_name']) {echo 'selected';}?>><?php if ($virivka['brand'] != "") {echo $virivka['brand'] . ' ' . ucfirst($virivka['fullname']);} else {echo ucfirst($virivka['fullname']);}?></option><?php } ?>
							</select>

						</div>
					</div>
    <div class="col-sm-6">

					<script type="text/javascript">
						jQuery(document).ready(function($)
						{


							/*
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

							$('.provedeni').on('change', function() {


							var id = this.id;
							var selected = this.value;

						   	$('.params_virivky_'+id).hide( "slow");
							$('.params_'+selected+'_'+id).show( "slow");


						});*/






						});
					</script>


	<?php
    mysqli_data_seek($virivky_query_new, 0);
while ($virivka = mysqli_fetch_array($virivky_query_new)) {

    ?>
		<div class="virivky_typy virivka_<?= $virivka['connect_name'] ?>" <?php if ($container['product'] != $virivka['connect_name']) { ?>style="display: none;"<?php } ?>>

            <div class="form-group">
						<label class="col-sm-3 control-label" style="width: 20%; color: #555; font-weight: 500;">Provedení</label>

						<div class="col-sm-6" style="width: 35%;">

							<select class="form-control provedeni_<?= $virivka['connect_name'] ?> provedeni" id="<?= $virivka['connect_name'] ?>" name="provedeni_<?= $virivka['connect_name'] ?>">
		<?php

    $param_type_query = $mysqli->query("SELECT * FROM containers_products_specs_bridge WHERE specs_id = '5' and client_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);
    $param_type = mysqli_fetch_array($param_type_query);

    $selected = false;
    $options = '';

    $virivky_typy = $mysqli->query("SELECT * FROM warehouse_products_types WHERE warehouse_product_id = '" . $virivka['id'] . "'") or die($mysqli->error);
    while ($typ = mysqli_fetch_array($virivky_typy)) {

        $selected_echo = "";

        if (isset($param_type['value']) && $param_type['value'] == $typ['name'] && $container['product'] == $virivka['connect_name']) {$selected = true;
            $selected_echo = 'selected';
            $selected_id = $virivka['id'];}

        $options = $options . '<option value="' . $typ['seo_url'] . '" ' . $selected_echo . '>' . $typ['name'] . '</option>';

    }?>
			<option value="" <?php if ($selected != true) {echo 'selected';}?>>Žádná vybraná možnost</option>
		<?php

    echo $options;

    ?>
			</select>
						</div>
			</div>
		</div>
<?php }
?>
    </div>
    </div>
    <hr style="clear: both;">

    <?php

$virivky_typy = $mysqli->query("SELECT * FROM warehouse_products_types WHERE warehouse_product_id = '" . $selected_id . "'") or die($mysqli->error);
?>
<div id="current_provedeni">
    <?php while ($typ = mysqli_fetch_array($virivky_typy)) { ?>

			<div class="params_virivky params_virivky_<?= $container['product'] ?> params_<?= $typ['seo_url'] ?>_<?= $container['product'] ?>" <?php if ($param_type['value'] != $typ['name'] || $container['product'] != $container['product']) { ?>style="display: none;"<?php } ?>>
                <div class="col-sm-6" style="border-right: 1px solid #eee;">

                <?php

    $specs_query = $mysqli->query("SELECT *, s.id as id, c.value FROM specs s, warehouse_products_types_specs w, containers_products_specs_bridge c WHERE c.specs_id = s.id AND c.client_id = '" . $_REQUEST['id'] . "' AND w.spec_id = s.id AND w.type_id = '" . $typ['id'] . "' AND s.supplier = 1 GROUP BY s.id ORDER BY s.rank asc") or die($mysqli->error);

    $total_specs = mysqli_num_rows($specs_query);

    $i = -1;
    while ($specs = mysqli_fetch_assoc($specs_query)) {


        $i++;
        if($i == round(($total_specs / 2))){ echo '</div><div class="col-sm-6">'; }


        $spec_id = $specs['id'];
        $product_id = $id;

        $cpars_query->execute() or die($mysqli->error);
        $result = $cpars_query->get_result();
        $cpars = $result->fetch_array();

        // VALUE U POPTÁVKY K DANÉ SPECIFIKACI

        if (isset($specs['type']) && $specs['type'] == 1) {

            $spec_id = $specs['id'];
            $type_id = $typ['id'];

            $params_query->execute() or die($mysqli->error);
            $result = $params_query->get_result();

            ?><div class="form-group" style="border-bottom: 1px solid #f1f1f1; padding-bottom: 10px; margin-bottom: 10px; min-height: 42px;">
							<label class="col-sm-3 control-label" style="width: 24%; color: #555; font-weight: 500;"><?= $specs['name'] ?></label>
							<div class="col-sm-6" style="width: 46%;">
								<select name="<?= $container['product'] ?>_<?= $typ['seo_url'] ?>_<?= $specs['seoslug'] ?>" class="form-control">
									<?php
            $selected = false;
            $options = '';

            while ($params = $result->fetch_array()) {

                $selected_echo = "";

                // když uložené provedení u poptávky == právě řešené provedení
                if (isset($provedeni['id']) && $provedeni['id'] == $params['type_id']) {

                    if (isset($cpars['value']) && $cpars['value'] == $params['option'] && $param_type['value'] == $typ['name'] && $container['product'] == $container['product']) {

                        $selected_echo = 'selected';
                        $selected = true;

                    }

                } elseif ($provedeni['id'] != $params['type_id']) {

                    if (isset($params['choosed']) && $params['choosed'] == 1 && $cpars['value'] != "unknown") {

                        $selected_echo = 'selected';
                        $selected = true;

                    }

                }

                $options = $options . '<option value="' . $params['option'] . '" ' . $selected_echo . '>' . $params['option'] . '</option>';

            }?>
									<option value="" <?php if ($selected != true) {echo 'selected';}?>>Žádná vybraná možnost</option>
									<?= $options ?>
								</select>
							</div>
<?php

        } elseif($specs['type'] == 0)  {

            $paramsquery = $mysqli->query("SELECT * FROM warehouse_products_types_specs WHERE spec_id = '" . $specs['id'] . "' AND type_id = '" . $typ['id'] . "' order by spec_param_id desc") or die($mysqli->error);

            ?><div class="form-group" style="border-bottom: 1px solid #f1f1f1; padding-bottom: 10px; margin-bottom: 10px; min-height: 42px; line-height: 19px;">
						<label class="col-sm-3 control-label" style="width: 24%; color: #555; font-weight: 500;"><?= $specs['name'] ?></label>
						<div class="col-sm-6" style="width: 46%;"><?php

            $selected = false;
            while ($params = mysqli_fetch_array($paramsquery)) {

                if (isset($params['spec_param_id']) && $params['spec_param_id'] == 1) {$value = 'Ano';} else { $value = 'Ne';}

                ?><div class="radio" style="width: 80px; float: left;text-align: left;">
								<label>
									<input class="generate_radio" id="price_<?= $specs['seoslug'] ?>" type="radio" name="<?= $container['product'] ?>_<?= $typ['seo_url'] ?>_<?= $specs['seoslug'] ?>" value="<?= $value ?>" <?php if (($cpars['value'] == $value && $param_type['value'] == $typ['name']) || ($params['choosed'] == 1 && $cpars['value'] != "unknown" && !$selected)) {$selected = true;
                    echo 'checked';}?> style="height: 14px;"><?= $value ?>
								</label>
							</div><?php

            }?>
						</div>

					<?php } elseif($specs['type'] == 2) {

            ?><div class="form-group" style="border-bottom: 1px solid #f1f1f1; padding-bottom: 10px; margin-bottom: 10px; min-height: 42px; line-height: 19px;">
						<label class="col-sm-3 control-label" style="width: 24%; color: #555; font-weight: 500;"><?= $specs['name'] ?></label>
						<div class="col-sm-6" style="width: 46%;"><?php

                    $selected = false;
                        ?>

                            <div class="radio" style="width: 140px; float: left; text-align: left;">
                                <label>
                                    <input 
                                            type="text"
                                            name="<?= $container['product'] ?>_<?= $typ['seo_url'] ?>_<?= $specs['seoslug'] ?>"
                                            class="form-control generate_text" id="price_<?= $specs['seoslug'] ?>" value="<?php echo $specs['value'] ?>" style="width: 100%;">
                                </label>
                            </div>
                                   
						</div>

          
           <?php }


        ?>

        <div class="col-sm-6" style="width: 15%; padding-left: 0; display: none;">

            <input class="form-control" name="<?= $container['product'] ?>_<?= $typ['seo_url'] ?>_<?= $specs['seoslug'] ?>_price" type="text" placeholder="Cena">

        </div>
        <div class="col-sm-3" style="width: 15%; padding: 0;">
            <input id="<?= $container['product'] ?>_<?= $typ['seo_url'] ?>_<?= $specs['seoslug'] ?>_paid" class="paid_input" name="<?= $container['product'] ?>_<?= $typ['seo_url'] ?>_<?= $specs['seoslug'] ?>_paid" value="1" type="checkbox" style="height: 24px; float: left;" <?php if($specs['paid'] == 1){ echo 'checked'; }?>>
            <label for="<?= $container['product'] ?>_<?= $typ['seo_url'] ?>_<?= $specs['seoslug'] ?>_paid" style="line-height: 30px !important; padding-left: 10Px;">zaplaceno</label>
        </div>

    <textarea name="<?= $container['product'] ?>_<?= $typ['seo_url'] ?>_<?= $specs['seoslug'] ?>_paid_text" style="margin: 8px 5%; width: 90%; float: left;

    <?php if($specs['paid'] == 0){ ?>display: none;<?php } ?>" class="col-sm-12 form-control <?= $container['product'] ?>_<?= $typ['seo_url'] ?>_<?= $specs['seoslug'] ?>_paid_text" placeholder="Dodatečný popisek k zaplacení... např. způsob doručení."><?= $specs['paid_text'] ?></textarea>
                </div>


        <?php
    }?>
			</div>
            </div>

		<?php } ?>

</div>


	<!-- konec specifikace konec specifikace konec specifikace -->

<div style="clear:both;"></div>
<div class="modal-footer" style="text-align:left;"> <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>
    <div style="float: right;">
<!--	<button type="submit" class="btn btn-primary btn-icon icon-left">--><?//echo $remove_button; ?>
<!--					<i class="entypo-pencil"></i></button>-->

       <button id="save_data" class="btn btn-success btn-icon icon-left">
					<i class="entypo-pencil"></i> Upravit</button>
					</div>

	</div>
</form>

			</div>


    <link rel="stylesheet" href="/admin/assets/js/select2/select2-bootstrap.css">
    <link rel="stylesheet" href="/admin/assets/js/select2/select2.css">
    <!-- Bottom Scripts -->
    <!--	<script src="/admin/assets/js/gsap/TweenMax.min.js"></script>-->
    <!--	<script src="/admin/assets/js/gsap/main-gsap.js"></script>-->
    <script src="/admin/assets/js/jquery-ui/js/jquery-ui-1.10.3.minimal.min.js"></script>
    <script src="/admin/assets/js/bootstrap.js"></script>
    <!--	<script src="/admin/assets/js/joinable.js"></script>-->
    <!--	<script src="/admin/assets/js/resizeable.js"></script>-->
    <!--	<script src="/admin/assets/js/neon-api.js"></script>-->
    <script src="/admin/assets/js/select2/select2.min.js"></script>
    <!--	<script src="/admin/assets/js/neon-chat.js"></script>-->
    <script src="/admin/assets/js/neon-custom.js"></script>



	<?php
$time_elapsed_secs = microtime(true) - $start;
//echo '<br>' . $time_elapsed_secs;

?>