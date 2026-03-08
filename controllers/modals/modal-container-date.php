<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";

$id = $_REQUEST['id'];

$container_query = $mysqli->query("SELECT date_due, date_correction FROM containers WHERE id = '$id'");

$container = mysqli_fetch_array($container_query);

$text = 'Nastavte poslední známé datum doručení. Datum se propíše do kalendáře jako vyskladnění kontejneru a zároveň ke každé vířivce.';

$remove_button = 'Naplánovat doručení';

$title = 'Datum doručení kontejneru #' . $id;

?>



	<div class="modal-dialog" style="width: 960px;">
	<form role="form" method="post" action="?action=arrival_date&id=<?= $id ?>" enctype="multipart/form-data">
	<div class="modal-content">
			<div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

				<h4 class="modal-title"><?= $title ?></h4> </div>

			<div class="modal-body" style="padding: 28px 0px 20px; ">

					<p style="text-align: center; font-size: 13px; font-style: italic;"><?= $text ?></p>

                <hr>

        <div class="form-group" style="display: inline-block; width: 100%;">
            <label for="field-2" class="col-sm-2 control-label" style="text-align: right; line-height: 20px;"><strong>Datum doručení</strong></label>

            <div class="col-sm-4">

     		   <input type="text" style="width: 100%; float: left; margin: 0 0 10px 0;" name="date" class="form-control datepicker" data-format="yyyy-mm-dd" placeholder="Datum doručení" <?php if ($container['date_due'] != '0000-00-00') { ?>value="<?= $container['date_due'] ?>"<?php } ?>>

            </div>

            <label for="field-2" class="col-sm-2 control-label" style="text-align: right; line-height: 20px;"><strong>Na cestě</strong></label>

            <div class="col-sm-4">

                <input type="checkbox" name="date_correction" value="1" <?php if (isset($container['date_correction']) && $container['date_correction'] == '1') { ?>checked<?php } ?>>

            </div>
          </div>



                <div class="col-sm-12" style="display: inline-block;">
                <div class="form-group well"
                     style="background-color: #FFFFFF; padding: 12px 0px 7px; float: left; width: 49.5%;margin-right: 0.5%; margin-bottom: 0;">

                    <h4
                            style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">
                        Proveditelé</h4>

                    <?php

                    $event_id_query = $mysqli->query("SELECT gcalendar, id FROM dashboard_texts WHERE container_id = '" . $id . "'") or die($mysqli->error);
                    $event_id = mysqli_fetch_array($event_id_query);


                    $admin_query = $mysqli->query("SELECT id, user_name, role FROM demands WHERE role != 'client' AND active = 1 AND active = 1");


                    while ($admin = mysqli_fetch_array($admin_query)) {

                        $find_query = $mysqli->query("SELECT * FROM mails_recievers WHERE type_id = '" . $event_id['id'] . "' AND admin_id = '" . $admin['id'] . "' AND type = 'event' AND reciever_type = 'performer'") or die($mysqli->error);

                        ?>

                        <div class="col-sm-4">

                            <input id="admin-<?= $admin['id'] ?>-event-performer" name="performer[]"
                                   value="<?= $admin['id'] ?>" type="checkbox" <?php if (mysqli_num_rows($find_query) > 0) {echo 'checked';}?>>
                            <label for="admin-<?= $admin['id'] ?>-event-performer"
                                   style="padding-left: 4px; cursor: pointer;"><?= $admin['user_name'] ?></label>

                        </div>

                    <?php } ?>


                </div>
                <div class="form-group well admins_well"
                     style="background-color: #FFFFFF; padding: 12px 0px 7px; float: left; width: 49.5%; margin-left: 0.5%; margin-bottom: 0;">

                    <h4
                            style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">
                        Informovaní</h4>

                    <?php mysqli_data_seek( $admin_query, 0 );

                    while($admin = mysqli_fetch_array($admin_query)){

                        $find_query = $mysqli->query("SELECT * FROM mails_recievers WHERE type_id = '" . $event_id['id'] . "' AND admin_id = '" . $admin['id'] . "' AND type = 'event' AND reciever_type = 'observer'") or die($mysqli->error);

                        ?>

                        <div class="col-sm-4">

                            <input id="admin-<?= $admin['id'] ?>-event-observer" name="observer[]"
                                   value="<?= $admin['id'] ?>" type="checkbox" <?php if($client['id'] == $admin['id'] OR mysqli_num_rows($find_query) > 0){ echo 'checked';}?>>
                            <label for="admin-<?= $admin['id'] ?>-event-observer"
                                   style="padding-left: 4px; cursor: pointer;">
                                <?= $admin['user_name'] ?></label>

                        </div>

                    <?php } ?>

                </div>

                </div>



			</div>
			<div style="clear: both;"></div>

<div class="modal-footer" style="text-align:left;"> <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>

	<div style="float: right;"><button type="submit" class="btn btn-primary btn-icon icon-left"><?= $remove_button ?>
					<i class="entypo-resize-small"></i></button></div>

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
