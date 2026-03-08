<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";

$id = $_REQUEST['id'];

$container_query = $mysqli->query("SELECT date_loading, date_lead, container_number FROM containers WHERE id = '$id'");

$container = mysqli_fetch_array($container_query);

//$text = 'Nastavte poslední známé datum doručení. Datum se propíše do kalendáře jako vyskladnění kontejneru a zároveň ke každé vířivce.';

$remove_button = 'Save';

$title = 'Container Number & Loading Date ~ Container #' . $id;

?>



<div class="modal-dialog" style="z-index: 9;">
    <form role="form" method="post" action="?action=set_info&id=<?= $id ?>" enctype="multipart/form-data" style=" z-index: 9;"">
        <div class="modal-content">
            <div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

                <h4 class="modal-title"><?= $title ?></h4> </div>

            <div class="modal-body" style="padding: 36px 35px 20px 35px; text-align: center;">

                <p><?= $text ?></p>


                <div class="form-group">
                    <label for="field-2" class="col-sm-4 control-label"><strong>Container Number</strong></label>
                    <div class="col-sm-6">
                        <input type="text" name="container_number" class="form-control" placeholder="Container Number" value="<?php if (!empty($container['container_number'])){ echo $container['container_number']; } ?>" style=" z-index: 10;">
                    </div>
                </div>
                <br>
                <br>

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

            </div>
            <div style="clear: both;"></div>

            <div class="modal-footer" style="text-align:left;"> <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>

                <div style="float: right;"><button type="submit" class="btn btn-primary btn-icon icon-left"><?= $remove_button ?>
                        <i class="entypo-pencil"></i></button></div>

            </div>
        </div>
    </form>
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

