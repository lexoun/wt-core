
<script>
    $(document).ready(function(){
        $('input.timepicker').timepicker({
            timeFormat: 'HH:mm',
            interval: 60,
            minTime: '6',
            maxTime: '11:00pm',
            defaultTime: '12',
            startTime: '6',
            dynamic: false,
            dropdown: true,
            scrollbar: true
        });
    });
</script>

<section id="servistab" class="profile-feed" style="z-index: 0 !important;">
                    <h2 style="margin-top: 36px;">Historie follow-up</h2>
                    <hr>

                    <!-- profile stories -->
                    <div class="profile-stories">

                        <article class="story" style="margin: 40px 0 30px 0; min-height: 89px;">

                            <div id="table-2_wrapper" class="dataTables_wrapper form-inline" role="grid"
                                style="margin-bottom: 30px;">

                                <?php

        $mailsquery = $mysqli->query("SELECT *, DATE_FORMAT(date_time, '%d. %m. %Y') as dateformated, DATE_FORMAT(date_time, '%H:%i') as hoursmins FROM demands_mails_history WHERE demand_id = '$id' ORDER BY id desc");
        if (mysqli_num_rows($mailsquery) > 0) {
            ?>

                                <table class="table table-bordered table-striped datatable dataTable" id="table-2"
                                    aria-describedby="table-2_info">
                                    <thead>
                                        <tr role="row">
                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2"
                                                rowspan="1" colspan="1"
                                                aria-label="Average Grade: activate to sort column ascending"
                                                style="width: 200px;">Typ</th>
                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2"
                                                rowspan="1" colspan="1"
                                                aria-label="Average Grade: activate to sort column ascending"
                                                style="width: 200px;">Popis</th>
                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2"
                                                rowspan="1" colspan="1"
                                                aria-label="Curriculum / Occupation: activate to sort column ascending"
                                                style="width: 90px; text-align: center;">Datum</th>
                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2"
                                                rowspan="1" colspan="1"
                                                aria-label="Curriculum / Occupation: activate to sort column ascending"
                                                style="width: 90px; text-align: center;">Čas</th>
                                                <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2"
                                                rowspan="1" colspan="1"
                                                aria-label="Actions: activate to sort column ascending"
                                                style="width: 100px; text-align: center;">Stav</th>
                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2"
                                                rowspan="1" colspan="1"
                                                aria-label="Actions: activate to sort column ascending"
                                                style="width: 100px; text-align: center;">Proveditelé</th>
                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2"
                                                rowspan="1" colspan="1"
                                                aria-label="Actions: activate to sort column ascending"
                                                style="width: 100px; text-align: center;">Informovaní</th>
<!--                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2"-->
<!--                                                rowspan="1" colspan="1"-->
<!--                                                aria-label="Actions: activate to sort column ascending"-->
<!--                                                style="width: 100px; text-align: center;">Zadal</th>-->
                                            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2"
                                                rowspan="1" colspan="1"
                                                aria-label="Actions: activate to sort column ascending"
                                                style="width: 100px; text-align: center;">Akce</th>
                                        </tr>
                                    </thead>


                                    <tbody role="alert" aria-live="polite" aria-relevant="all">
                                        <?php
while ($mail = mysqli_fetch_array($mailsquery)) {


            ?>


                                        <tr class="odd">
                                            <td class=" "
                                                style="background-color: #ffffff !important; height: 43px !important;">
                                                <i class="<?php
                                                if($mail['type'] == 'Nabídka'){

                                                    echo 'entypo-doc-text';

                                                }elseif($mail['type'] == 'Návštěva - plánovaná' || $mail['type'] == 'Návštěva - neplánovaná'){

                                                    echo 'entypo-users';

                                                }elseif($mail['type'] == 'Zkouška vířivky'){

                                                    echo 'fa fa-spinner';

                                                }elseif($mail['type'] == 'Telefonát'){

                                                    echo 'fa fa-phone';

                                                }elseif($mail['type'] == 'Mailing'){

                                                    echo 'fa fa-envelope';

                                                } ?>"
                                                    style="font-size: 15px; padding: 0 5px 0 5px;"></i>
                                                <?= $mail['type'] ?>
                                            </td>
                                            <td class=" "
                                                style="background-color: #ffffff !important;">
                                                <?= $mail['text'] ?>
                                            </td>
                                            <td class=" " style="background-color: #ffffff !important; text-align: center;">
                                                <?= $mail['dateformated'] ?>
                                                
                                            </td>
                                            <td class=" " style="background-color: #ffffff !important; text-align: center;">
                                                <?php if($mail['hoursmins'] != '00:00'){
                                                    echo $mail['hoursmins'];
                                                }else{ echo '-'; } ?>
                                                
                                            </td>
                                            <td class=" " style="background-color: #ffffff !important; text-align: center;">
                                                
                                             <?php if($mail['state'] == 'ongoing'){ ?>
                                                <span style="font-weight: bold; color: #ff0007;"><i class="entypo-cancel" style="padding-right: 2px;"></i>Neprovedeno</span>
                                                <?php
                                             }else{ ?>
                                             <span style="text-decoration: underline; font-weight: bold; color: #04a500;"><i class="fa fa-check" style="padding-right: 2px;"></i>Provedeno</span>
                                             
                                             <?php }
                                                ?>
                                               
                                            </td>


                                            <td class=" " style="background-color: #ffffff !important; text-align: center;">

                                                <?php
                                                $performersQuery = $mysqli->query('SELECT t.admin_id, c.user_name FROM mails_recievers t, demands c WHERE t.type_id = "' . $mail['id'] . '" AND t.admin_id = c.id AND t.type = "follow_up" AND t.reciever_type = "performer"') or die($mysqli->error);

                                                $i = 0;
                                                while ($performer = mysqli_fetch_assoc($performersQuery)) {

                                                    if ($i > 0) {  echo ' & '; }

                                                    ?><?= $performer['user_name'] ?><?php

                                                    $i = $i + 1;

                                                }

                                                ?>

                                            </td>

                                            <td class=" " style="background-color: #ffffff !important; text-align: center;">

                                                <?php
                                                $performersQuery = $mysqli->query('SELECT t.admin_id, c.user_name FROM mails_recievers t, demands c WHERE t.type_id = "' . $mail['id'] . '" AND t.admin_id = c.id AND t.type = "follow_up" AND t.reciever_type = "observer"') or die($mysqli->error);

                                                $i = 0;
                                                while ($performer = mysqli_fetch_assoc($performersQuery)) {

                                                    if ($i > 0) {  echo ' & '; }

                                                    ?><?= $performer['user_name'] ?><?php

                                                    $i = $i + 1;

                                                }

                                                ?>

                                            </td>


                                            <td class=" "
                                                style="text-align: center;background-color: #ffffff !important;">
                                                <?php if($mail['state'] == 'ongoing'){ ?>

                                                <a href="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=follow-up-state&follow_up_id=<?= $mail['id'] ?>&state=done"
                                                    class="btn btn-success btn-sm">
                                                    <i class="fa fa-check"></i>
                                                </a>
                                            <?php }else{ ?>
                                                <a href="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=follow-up-state&follow_up_id=<?= $mail['id'] ?>&state=ongoing"
                                                    class="btn btn-default btn-sm">
                                                    <i class="entypo-cancel"></i>
                                                </a>
                                            <?php } ?>

                                                <a class="toggle-edit-followup btn btn-primary btn-sm" data-id="<?= $mail['id'] ?>">
                                                    <i class="entypo-pencil"></i>
                                                </a>

                                                <a href="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=follow-up-remove&follow_up_id=<?= $mail['id'] ?>"
                                                    class="btn btn-danger btn-sm">
                                                    <i class="entypo-trash"></i>
                                                </a>
                                            </td>
                                        </tr>

           <?php } ?>
                                    </tbody>

                                </table>
                                <?php
} else { ?>


                                <div class="well well-lg" style="margin-bottom: 0;">

                                    <h3 style="text-align: center;">Zatím nebyly záslány žádné emaily.</h3>

                                </div>


                                <?php } ?>
                            </div>



<script type="text/javascript">

jQuery(document).ready(function($)
{

$('.categoryButton').click(function() {

    var type = $(this).data("type");

    $(".type").val(type);

    $(".categoryButton").removeClass("active");
    $(this).addClass("active");

    $('#followUpForm').show( "slow");

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

<style>
	.tile-stats.active {
		background-color: #3E4552 !important;
	}
	.tile-stats.active h4 {
		color: #FFFFFF;
	}
    .tile-stats.active h5 {
        color: #FFFFFF;
    }
</style>

<div class="col-md-12">
	  <div class="well" style="display:block; margin: 50px auto 40px; width: 100%;">
	  
        <h2 class="specialborderbottom" style="margin-bottom: 20px;padding-bottom: 18px;text-align:center;">Přidat Follow Up</h2>
        
        <div class="col-sm-3" style="cursor:pointer; width: 16.66%;">
          <div class="tile-stats tile-gray spsle categoryButton" data-type="Návštěva - plánovaná" style="border: 1px solid #DDDDDD;  padding: 10px 20px; background: #FFFFFF;">
            <div class="icon" style="top: 10px !important;right: 10px;"><i style="font-size: 34px;" class="entypo-users"></i></div>
            <div class="num"></div> <h4>Návštěva - plánovaná</h4> <p></p>
          </div>
        </div>

        <div class="col-sm-3" style="cursor:pointer; width: 16.66%;">
          <div class="tile-stats tile-gray spsle categoryButton" data-type="Návštěva - neplánovaná" style="border: 1px solid #DDDDDD;  padding: 10px 20px; background: #FFFFFF;">
            <div class="icon" style="top: 10px !important;right: 10px;"><i style="font-size: 34px;" class="entypo-users"></i></div>
            <div class="num"></div> <h4>Návštěva - neplánovaná</h4> <p></p>
          </div>
        </div>

        <div class="col-sm-3" style="cursor:pointer; width: 16.66%;">
          <div class="tile-stats tile-gray spsle categoryButton" data-type="Zkouška vířivky" style="border: 1px solid #DDDDDD;  padding: 10px 20px; background: #FFFFFF;">
            <div class="icon" style="top: 10px !important;right: 10px;"><i style="font-size: 34px;" class="fa fa-spinner"></i></div>
            <div class="num"></div> <h4>Zkouška vířivky</h4> <p></p>
          </div>
        </div>

        <div class="col-sm-3" style="cursor:pointer; width: 16.66%;">
          <div class="tile-stats tile-gray spsle categoryButton" data-type="Telefonát" style="border: 1px solid #DDDDDD; padding: 10px 20px; background: #FFFFFF;">
            <div class="icon" style="top: 10px !important;"><i style="font-size: 34px;" class="fa fa-phone"></i></div>
            <div class="num"></div> <h4>Telefonát</h4> <p></p>
          </div>
        </div>

        <div class="col-sm-3" style="cursor:pointer; width: 16.66%;">
          <div class="tile-stats tile-gray spsle categoryButton" data-type="Mailing" style="border: 1px solid #DDDDDD;  padding: 10px 20px; background: #FFFFFF;">
            <div class="icon" style="top: 10px !important;right: 10px;"><i style="font-size: 34px;" class="fa fa-envelope"></i></div>
            <div class="num"></div> <h4>Mailing</h4> <p></p>
          </div>
        </div>
        <div class="col-sm-3" style="cursor:pointer; width: 16.66%;">
          <div class="tile-stats tile-gray spsle categoryButton" data-type="Nabídka" style="border: 1px solid #DDDDDD;  padding: 10px 20px; background: #FFFFFF;">
            <div class="icon" style="top: 10px !important;right: 10px;"><i style="font-size: 34px;" class="entypo-doc-text"></i></div>
            <div class="num"></div> <h4>Nabídka</h4> <p></p>;
          </div>
        </div>

        <div style="clear:both;"></div>

        <form id="followUpForm" style="display: none;" role="form" method="post" name="myform" class="form-horizontal form-groups-bordered validate col-sm-12" enctype='multipart/form-data' action="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=follow-up" autocomplete="off" >
        
        <input class="type" type="text" name="type" value="" style="display: none;">

			<div class="row">

					<div class="panel panel-primary" data-collapsed="0">

			<div class="panel-body">
  
				<div class="form-group">

					<div class="col-sm-7 allmailus mailuszaklad">
				    <textarea class="form-control changusnamus nameruszaklad" data-stylesheet-url="<?= $home ?>/admin/assets/css/wysihtml5-color.css" name="text" id="sample_wysiwyg" style="height: 360px;" placeholder="Popis Follow Upu"></textarea>
					</div>


                    <div class="col-sm-5">

                        <div class="form-group well col-sm-12 admins_well" style="margin: 0 auto 18px; padding: 16px 0 10px 20px;">
                            <h4 style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">Proveditelé</h4>

                            <?php $admins_query = $mysqli->query("SELECT id, user_name FROM demands WHERE role != 'client' AND active = 1");
                            while ($admins = mysqli_fetch_array($admins_query)) { ?>
                                <div class="col-sm-3" style="padding: 0">
                                    <input id="admin-<?= $admins['id'] ?>-performer" name="performer[]" value="<?= $admins['id'] ?>" type="checkbox" <?php if($client['id'] == $admins['id']){ echo 'checked'; }?>>
                                    <label for="admin-<?= $admins['id'] ?>-performer" style="padding-left: 4px; cursor: pointer;"><?= $admins['user_name'] ?></label>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="form-group well col-sm-12 admins_well" style="margin: 0 auto 18px; padding: 16px 0 10px 20px;">
                            <h4 style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">Informovaní</h4>

                            <?php
                            $admins_query = $mysqli->query("SELECT id, user_name FROM demands WHERE role != 'client' AND active = 1");
                            while ($admins = mysqli_fetch_array($admins_query)) { ?>
                                <div class="col-sm-3" style="padding: 0">
                                    <input id="admin-<?= $admins['id'] ?>-observer" name="observer[]" value="<?= $admins['id'] ?>" type="checkbox">
                                    <label for="admin-<?= $admins['id'] ?>-observer" style="padding-left: 4px; cursor: pointer;"><?= $admins['user_name'] ?></label>
                                </div>
                            <?php } ?>
                        </div>

                    </div>

				</div>


					<div class="form-group">
						<label class="col-sm-1 control-label">Notifikace</label>
						<div class="col-sm-9">
                            <div class="radio radio2" style="width: 100px; float: left;">
                                <label>
                                    <input type="radio" name="notificationdate" value="0">Dnes
                                </label>
                            </div>
                            <div class="radio radio2" style="width: 100px; float: left;">
                                <label>
                                    <input type="radio" name="notificationdate" value="1" checked>Zítra
                                </label>
                            </div>
                            <div class="radio radio2" style="width: 100px; float: left;">
                                <label>
                                    <input type="radio" name="notificationdate" value="2">Za 2 dny
                                </label>
                            </div>
							<div class="radio radio2" style="width: 100px; float: left;">
								<label>
									<input type="radio" name="notificationdate" value="3">Za 3 dny
								</label>
							</div>
							<div class="radio radio2" style="width: 100px;float: left;">
								<label>
									<input type="radio" name="notificationdate" value="7">Za týden
								</label>
							</div>
							<div class="radio radio2" style="width: 100px;float: left;">
								<label>
									<input type="radio" name="notificationdate" value="14">Za 2 týdny
								</label>
							</div>
							<div class="radio radio2" style="width: 100px;float: left;">
								<label>
									<input type="radio" name="notificationdate" value="choose" class="choosedateradio">Vyberu datum
								</label>
								</div>

                            <input class="customdate form-control datepicker" type="text" name="chooseDate" data-format="yyyy-mm-dd" placeholder="Datum" style="display: none; width: 100px;float: left">

                                    <input class="form-control timepicker" type="text" name="chooseTime" data-show-seconds="false" data-default-time="00-00"
                                           data-show-meridian="false" data-minute-step="5" placeholder="Čas" style="width: 100px; float: left;">


						</div>
					</div>



		</div>



			</div>	</div>




					
	<div class="form-group default-padding" style="text-align: center;">
		<button type="submit" class="btn btn-lg btn-primary btn-icon icon-left" style="padding-right: 24px;">
Přidat Follow Up<i class="fa fa-check"></i> </button>
	</div>
</form>

         <div style="clear:both;"></div>
</div>
</div>



<!-- 
                            <center><a href="solo-mail?id=<?= $getclient['id'] ?>"
                                    style="margin-bottom: 12px; margin-right: 26px; font-size: 16px; padding: 14px 40px 14px 98px;"
                                    class="btn btn-primary btn-icon icon-left btn-lg">
                                    <i class="entypo-mail"
                                        style="line-height: 28px;font-size: 30px; padding: 10px 14px;"></i>
                                    Přidat follow-up
                                </a></center> -->
                        </article>

                    </div>

                </section>

