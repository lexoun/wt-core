<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";

$id = $_REQUEST['id'];

$container_query = $mysqli->query("SELECT date_due, date_correction FROM containers WHERE id = '$id'");
$container = mysqli_fetch_array($container_query);

$title = 'Převzetí kontejneru -  ' . $id;
$text = 'Právě se chystáte převzít kontejner číslo <strong>' . $id . '</strong>.<br><br>Dojde k hromadnému naskladnění vířivek na skladě. <strong>Tento proces je nevratný.</strong>';

$remove_button = 'Převzít kontejner';

?>



<div class="modal-dialog" style="width: 960px;">
    <form role="form" method="post" action="?action=recieve&id=<?= $id ?>" enctype="multipart/form-data">
        <div class="modal-content">
            <div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

                <h4 class="modal-title"><?= $title ?></h4> </div>

            <div class="modal-body" style="padding: 28px 0px 20px; ">

                <div class="alert alert-danger" style="margin: 0 50px;"><p style="text-align: center; font-size: 13px; font-style: italic;"><?= $text ?></p>
                </div>

                <hr>

                <div class="form-group" style="display: inline-block; width: 100%;">
                    <label for="field-2" class="col-sm-2 control-label" style="text-align: right; line-height: 20px;"><strong>Datum převzetí</strong></label>
                    <div class="col-sm-4">

                        <input type="text" style="width: 100%; float: left; margin: 0 0 10px 0;" name="date_received" class="form-control datepicker" data-format="yyyy-mm-dd" placeholder="Datum převzetí" <?php if ($container['date_due'] != '0000-00-00') { ?>value="<?= $container['date_due'] ?>"<?php } ?>>

                    </div>
                </div>

                </div>

            <div style="clear: both;"></div>

            <div class="modal-footer" style="text-align:left;"> <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>

                <div style="float: right;"><button type="submit" class="btn btn-success btn-icon icon-left"><?= $remove_button ?>
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
