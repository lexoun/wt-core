<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";

$follow_up_query = $mysqli->query("SELECT *, DATE_FORMAT(date_time, '%Y-%m-%d') as dateformated, DATE_FORMAT(date_time, '%H:%i') as hoursmins FROM demands_mails_history WHERE id = '" . $_REQUEST['id'] . "'");
$follow_up = mysqli_fetch_array($follow_up_query);

?>

<script type="text/javascript">

    jQuery(document).ready(function($)
    {

        $('.categoryButton2').click(function() {

            var type = $(this).data("type");

            $(".type").val(type);

            $(".categoryButton2").removeClass("active");
            $(this).addClass("active");

        });


        $('.radio2').click(function() {
            if($("input:radio[class='choosedateradio']").is(":checked")) {

                $('.customdate').show( "slow");
            }else{

                $('.customdate').hide( "slow");

            }


        });


    });

</script>



<div class="modal-dialog" style="width: 800px;">

    <form role="form" method="post" action="/admin/pages/demands/zobrazit-poptavku?action=follow-up-edit&follow_up_id=<?= $follow_up['id'] ?>&id=<?= $follow_up['demand_id'] ?>" enctype="multipart/form-data">
        <div class="modal-content">
            <div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

                <h4 class="modal-title">Změna stavu FollowUpu #<?= $follow_up['id'] ?></h4> </div>

            <div class="modal-body">

                <div class="col-sm-3" style="cursor:pointer; width: 16.66%; padding: 0 5px;">
                    <div class="tile-stats tile-gray spsle categoryButton2 <?php if($follow_up['type'] == 'Návštěva - plánovaná'){ echo 'active'; } ?>" data-type="Návštěva - plánovaná" style="border: 1px solid #DDDDDD;  padding: 10px 20px; background: #FFFFFF;">
                        <div class="icon" style="top: 10px !important;right: 10px;"><i style="font-size: 20px;" class="entypo-users"></i></div>
                        <div class="num"></div> <h5>Návštěva - plánovaná</h5> <p></p>
                    </div>
                </div>

                <div class="col-sm-3" style="cursor:pointer; width: 16.66%; padding: 0 5px;">
                    <div class="tile-stats tile-gray spsle categoryButton2 <?php if($follow_up['type'] == 'Návštěva - neplánovaná'){ echo 'active'; } ?>" data-type="Návštěva - neplánovaná" style="border: 1px solid #DDDDDD;  padding: 10px 20px; background: #FFFFFF;">
                        <div class="icon" style="top: 10px !important;right: 10px;"><i style="font-size: 20px;" class="entypo-users"></i></div>
                        <div class="num"></div> <h5>Návštěva - neplánovaná</h5> <p></p>
                    </div>
                </div>

                <div class="col-sm-3" style="cursor:pointer; width: 16.66%; padding: 0 5px;">
                    <div class="tile-stats tile-gray spsle categoryButton2 <?php if($follow_up['type'] == 'Zkouška vířivky'){ echo 'active'; } ?>" data-type="Zkouška vířivky" style="border: 1px solid #DDDDDD;  padding: 10px 20px; background: #FFFFFF;">
                        <div class="icon" style="top: 10px !important;right: 10px;"><i style="font-size: 20px;" class="fa fa-spinner"></i></div>
                        <div class="num"></div> <h5>Zkouška vířivky</h5> <p></p>
                    </div>
                </div>

                <div class="col-sm-3" style="cursor:pointer; width: 16.66%; padding: 0 5px;">
                    <div class="tile-stats tile-gray spsle categoryButton2 <?php if($follow_up['type'] == 'Telefonát'){ echo 'active'; } ?>" data-type="Telefonát" style="border: 1px solid #DDDDDD; padding: 10px 20px; background: #FFFFFF;">
                        <div class="icon" style="top: 10px !important;"><i style="font-size: 20px;" class="fa fa-phone"></i></div>
                        <div class="num"></div> <h5>Telefonát</h5> <p></p>
                    </div>
                </div>

                <div class="col-sm-3" style="cursor:pointer; width: 16.66%; padding: 0 5px;">
                    <div class="tile-stats tile-gray spsle categoryButton2 <?php if($follow_up['type'] == 'Mailing'){ echo 'active'; } ?>" data-type="Mailing" style="border: 1px solid #DDDDDD;  padding: 10px 20px; background: #FFFFFF;">
                        <div class="icon" style="top: 10px !important;right: 10px;"><i style="font-size: 20px;" class="fa fa-envelope"></i></div>
                        <div class="num"></div> <h5>Mailing</h5> <p></p>
                    </div>
                </div>
                <div class="col-sm-3" style="cursor:pointer; width: 16.66%; padding: 0 5px;">
                    <div class="tile-stats tile-gray spsle categoryButton2 <?php if($follow_up['type'] == 'Nabídka'){ echo 'active'; } ?>" data-type="Nabídka" style="border: 1px solid #DDDDDD;  padding: 10px 20px; background: #FFFFFF;">
                        <div class="icon" style="top: 10px !important;right: 10px;"><i style="font-size: 20px;" class="entypo-doc-text"></i></div>
                        <div class="num"></div> <h5>Nabídka</h5> <p></p>
                    </div>
                </div>

                <div style="clear:both;"></div>

                    <input class="type" type="text" name="type" value="<?= $follow_up['type'] ?>" style="display: none;">

                    <div class="row">

                                <div class="form-group">

                                    <div class="col-sm-12 allmailus mailuszaklad">
                                        <textarea class="form-control changusnamus nameruszaklad" data-stylesheet-url="<?= $home ?>/admin/assets/css/wysihtml5-color.css" name="text" id="sample_wysiwyg" style="height: 100px;" placeholder="Popis Follow Upu"><?= $follow_up['text'] ?></textarea>
                                    </div>

                                    <hr>


                                    <div class="col-sm-12">

                                        <div class="well col-sm-6 admins_well" style="margin: 0 5px 18px -5px; padding: 16px 0 10px 20px;">
                                            <h4 style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">Proveditelé</h4>

                                            <?php $admins_query = $mysqli->query("SELECT id, user_name FROM demands WHERE role != 'client' AND role != 'technician' AND active = 1");
                                            while ($admins = mysqli_fetch_array($admins_query)) {

                                                $find_query = $mysqli->query("SELECT admin_id FROM mails_recievers WHERE type_id = '" . $follow_up['id'] . "' AND admin_id = '" . $admins['id'] . "' AND type = 'follow_up' AND reciever_type = 'performer'") or die($mysqli->error);

                                            ?>
                                                <div class="col-sm-4" style="padding: 0">
                                                    <input id="admin-<?= $admins['id'] ?>-performer-<?= $follow_up['id'] ?>" name="performer[]" value="<?= $admins['id'] ?>" type="checkbox" <?php if (mysqli_num_rows($find_query) > 0) { echo 'checked'; } ?>>
                                                    <label for="admin-<?= $admins['id'] ?>-performer-<?= $follow_up['id'] ?>" style="padding-left: 4px; cursor: pointer; <?php if(mysqli_num_rows($find_query) > 0){ echo 'color: green !important;'; }?>"><?= $admins['user_name'] ?></label>
                                                </div>
                                            <?php } ?>
                                        </div>

                                        <div class="well col-sm-6 admins_well" style="margin: 0 -5px 18px 5px; padding: 16px 0 10px 20px;">
                                            <h4 style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">Informovaní</h4>

                                            <?php
                                            $admins_query = $mysqli->query("SELECT id, user_name FROM demands WHERE role != 'client' AND role != 'technician'  AND active = 1");
                                            while ($admins = mysqli_fetch_array($admins_query)) {

                                                $find_query = $mysqli->query("SELECT admin_id FROM mails_recievers WHERE type_id = '" . $follow_up['id'] . "' AND admin_id = '" . $admins['id'] . "' AND type = 'follow_up' AND reciever_type = 'observer'") or die($mysqli->error);

                                                ?>
                                                <div class="col-sm-4" style="padding: 0">
                                                    <input id="admin-<?= $admins['id'] ?>-observer-<?= $follow_up['id'] ?>" name="observer[]" value="<?= $admins['id'] ?>" type="checkbox" <?php if (mysqli_num_rows($find_query) > 0) { echo 'checked'; } ?>>
                                                    <label for="admin-<?= $admins['id'] ?>-observer-<?= $follow_up['id'] ?>" style="padding-left: 4px; cursor: pointer; <?php if(mysqli_num_rows($find_query) > 0){ echo 'color: green !important;'; }?>"><?= $admins['user_name'] ?></label>
                                                </div>
                                            <?php } ?>
                                        </div>

                                    </div>

                                    <div style="clear:both"></div>
                                    <hr>

                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Notifikace</label>
                                        <div class="col-sm-10">
                                            <div class="radio radio2" style="width: 100px; min-height: 17px; margin-top: 6px; margin-bottom: 6px; float: left;">
                                                <label>
                                                    <input type="radio" name="notificationdate" value="0">Dnes
                                                </label>
                                            </div>
                                            <div class="radio radio2" style="width: 100px; min-height: 17px; margin-top: 6px; margin-bottom: 6px; float: left;">
                                                <label>
                                                    <input type="radio" name="notificationdate" value="1" checked>Zítra
                                                </label>
                                            </div>
                                            <div class="radio radio2" style="width: 100px; min-height: 17px; margin-top: 6px; margin-bottom: 6px; float: left;">
                                                <label>
                                                    <input type="radio" name="notificationdate" value="2">Za 2 dny
                                                </label>
                                            </div>
                                            <div class="radio radio2" style="width: 100px; min-height: 17px; margin-top: 6px; margin-bottom: 6px; float: left;">
                                                <label>
                                                    <input type="radio" name="notificationdate" value="3">Za 3 dny
                                                </label>
                                            </div>
                                            <div class="radio radio2" style="width: 100px; min-height: 17px; margin-top: 6px; margin-bottom: 6px; float: left;">
                                                <label>
                                                    <input type="radio" name="notificationdate" value="7">Za týden
                                                </label>
                                            </div>
                                            <div class="radio radio2" style="width: 100px; min-height: 17px; margin-top: 6px; margin-bottom: 6px; float: left;">
                                                <label>
                                                    <input type="radio" name="notificationdate" value="14">Za 2 týdny
                                                </label>
                                            </div>

                                            <div class="radio radio2" style="width: 100px; min-height: 17px; margin-top: 20px; margin-bottom: 6px; float: left;">
                                                <label>
                                                    <input type="radio" name="notificationdate" value="choose" class="choosedateradio" checked>Vyberu datum
                                                </label>
                                            </div>

                                            <input class="customdate form-control datepicker" type="text" name="chooseDate" data-format="yyyy-mm-dd" placeholder="Datum" style="width: 100px; margin-top: 14px;margin-left: 10px;  margin-bottom: 6px;float: left" value="<?= $follow_up['dateformated'] ?>">

                                            <input class="form-control timepicker" type="text" name="chooseTime" data-show-seconds="false" data-default-time="00-00"
                                                   data-show-meridian="false" data-minute-step="5" placeholder="Čas" style="width: 100px; margin-left: 10px; margin-top: 14px; margin-bottom: 6px; float: left;"  value="<?= $follow_up['hoursmins'] ?>">


                                        </div>




                                    </div>
                                </div>




                    </div>

            </div>
            <div class="modal-footer" style="text-align:left;"> <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>

                <a href="#" style="float:right;"><button type="submit" class="btn btn-primary btn-icon icon-left">Upravit
                        <i class="entypo-pencil"></i></button></a>
    </form>
</div>


<link rel="stylesheet" href="https://www.wellnesstrade.cz/admin/assets/js/selectboxit/jquery.selectBoxIt.css">
<script src="https://www.wellnesstrade.cz/admin/assets/js/bootstrap-switch.min.js" id="script-resource-8"></script>
<script src="https://www.wellnesstrade.cz/admin/assets/js/selectboxit/jquery.selectBoxIt.min.js"></script>
<script src="https://www.wellnesstrade.cz/admin/assets/js/neon-custom.js"></script>
<script src="https://www.wellnesstrade.cz/admin/assets/js/bootstrap-datepicker.min.js"></script>
<script src="https://www.wellnesstrade.cz/admin/assets/js/bootstrap-timepicker.min.js"></script>
<!--<script src="--><?// echo $home; ?><!--/admin/assets/js/jquery-ui/js/jquery-ui-1.10.3.minimal.min.js"></script>-->