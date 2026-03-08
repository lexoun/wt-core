<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";

$id = $_REQUEST['id'];

$container_query = $mysqli->query("SELECT delivery_price, container_name, container_supplier, date_lead, date_loading, container_number, brand FROM containers WHERE id = '$id'");
$container = mysqli_fetch_array($container_query);

$text = 'Nastavte poslední známé datum doručení. Datum se propíše do kalendáře jako vyskladnění kontejneru a zároveň ke každé vířivce.';

$remove_button = 'Uložit';

$title = 'Cena dopravy a ID kontejneru #' . $id;

?>



	<div class="modal-dialog">
	<form role="form" method="post" action="?action=delivery_price&id=<?= $id ?>" enctype="multipart/form-data">
	<div class="modal-content">
			<div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

				<h4 class="modal-title"><?= $title ?></h4> </div>

			<div class="modal-body" style="padding: 36px 35px 20px 35px; text-align: center;">

          <div class="form-group">
            <label for="field-2" class="col-sm-4 control-label"><strong>ID kontejneru</strong></label>

            <div class="col-sm-6">

     		   <input type="text" name="container_name" class="form-control" <?php if ($container['container_name'] != "") { ?>value="<?= $container['container_name'] ?>"<?php } ?>>

            </div>
          </div>
                <br><br>
                <div class="form-group">
                    <label for="field-2" class="col-sm-4 control-label"><strong>Cena doručení</strong></label>
                    <div class="col-sm-6">
                        <input type="text" name="delivery_price" class="form-control" <?php if ($container['delivery_price'] != 0) { ?>value="<?= $container['delivery_price'] ?>"<?php } ?>>
                    </div>
                </div>
                <br><br>
                <div class="form-group">
                    <label for="field-2" class="col-sm-4 control-label"><strong>Container Number</strong></label>
                    <div class="col-sm-6">
                        <input type="text" name="container_number" class="form-control" placeholder="Container Number" value="<?php if (!empty($container['container_number'])){ echo $container['container_number']; } ?>" style=" z-index: 10;">
                    </div>
                </div>
                <br><br>
                <?php if($container['brand'] == 'Espoo'){?>
                <div class="form-group">
                    <label for="field-2" class="col-sm-4 control-label"><strong>Container Supplier</strong></label>
                    <div class="col-sm-6">
                            <select name="container_supplier" class="form-control">
                                <option value="" <?php if (isset($container['container_supplier']) && $container['container_supplier'] == "") {echo 'selected';}?>>nezvolen</option>
                                <option value="Deluxe" <?php if (isset($container['container_supplier']) && $container['container_supplier'] == "Deluxe") {echo 'selected';}?>>Deluxe</option>
                                <option value="Smart" <?php if (isset($container['container_supplier']) && $container['container_supplier'] == "Smart") {echo 'selected';}?>>Smart</option>
                            </select>
                    </div>
                </div>
                <br><br>
                <?php } ?>
                <div class="form-group">
                    <label for="field-2" class="col-sm-4 control-label"><strong>Factory Lead Time</strong></label>
                    <div class="col-sm-6">
                        <input type="text" style="width: 100%; z-index: 10; float: left; margin: 0 0 10px 0;" name="date_lead" class="form-control datepicker" data-format="yyyy-mm-dd" placeholder="Factory Lead Time" <?php if ($container['date_lead'] != '0000-00-00') { ?>value="<?= $container['date_lead'] ?>"<?php } ?>>
                    </div>
                </div>
                <br><br>
                <div class="form-group">
                    <label for="field-2" class="col-sm-4 control-label"><strong>Loading Date</strong></label>
                    <div class="col-sm-6">
                        <input type="text" style="width: 100%; z-index: 10; float: left; margin: 0 0 10px 0;" name="date_loading" class="form-control datepicker" data-format="yyyy-mm-dd" placeholder="Loading Date" <?php if ($container['date_loading'] != '0000-00-00') { ?>value="<?= $container['date_loading'] ?>"<?php } ?>>
                    </div>
                </div>
          <div style="clear: both;"></div>

			</div>

<div class="modal-footer" style="text-align:left;"> <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>

	<div style="float: right;"><button type="submit" class="btn btn-primary btn-icon icon-left"><?= $remove_button ?>
					<i class="entypo-pencil"></i></button></div>

	</div>


<script src="<?= $home ?>/admin/assets/js/gsap/TweenMax.min.js"></script>
<script src="<?= $home ?>/admin/assets/js/gsap/main-gsap.js"></script>
<script src="<?= $home ?>/admin/assets/js/jquery-ui/js/jquery-ui-1.10.3.minimal.min.js"></script>
<script src="<?= $home ?>/admin/assets/js/bootstrap.js"></script>
<script src="<?= $home ?>/admin/assets/js/joinable.js"></script>
<script src="<?= $home ?>/admin/assets/js/resizeable.js"></script>
<script src="<?= $home ?>/admin/assets/js/neon-api.js"></script>
<script src="<?= $home ?>/admin/assets/js/neon-custom.js"></script>
<script src="<?= $home ?>/admin/assets/js/bootstrap-datepicker.js"></script>
<script src="<?= $home ?>/admin/assets/js/bootstrap-timepicker.min.js"></script>
<script src="<?= $home ?>/admin/assets/js/bootstrap-colorpicker.min.js"></script>
<script src="<?= $home ?>/admin/assets/js/daterangepicker/moment.min.js"></script>
<script src="<?= $home ?>/admin/assets/js/daterangepicker/daterangepicker.js"></script>
