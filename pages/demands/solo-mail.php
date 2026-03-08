<?php


include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";

include INCLUDES . "/functions.php";

$id = $_REQUEST['id'];
$getclientquery = $mysqli->query('SELECT *, DATE_FORMAT(date, "%d. %m. %Y") as dateformated, DATE_FORMAT(realization, "%d. %m. %Y") as realizationformated, DATE_FORMAT(realtodate, "%d. %m. %Y") as realtodateformat FROM demands WHERE id="' . $id . '"') or die($mysqli->error);
$getclient = mysqli_fetch_assoc($getclientquery);

$pagetitle = "Poslání mailu";
$spesl = " - " . $getclient['user_name'];

$bread1 = "Poptávky";
$bread2 = "Maily";

$bread3 = "Aktuální odesílání";


$saunyquery = $mysqli->query("SELECT * FROM warehouse_products WHERE customer = 0 ORDER BY code");

$virivkyquery = $mysqli->query("SELECT * FROM warehouse_products WHERE customer = 1 ORDER BY brand");


include VIEW . '/default/header.php';


?>


<script type="text/javascript">

jQuery(document).ready(function($)
{


$('#showmailing').click(function() {

 	$('#postponing').hide( "slow");
	$('#phoning').hide( "slow");

	$('#mailing-step-2').hide( "slow");
	$('#mailing-step-3').hide( "slow");
	$('#mailing-step-custom').hide( "slow");

	$('#mailing').show( "slow");

	$("#showphoning .tile-stats").removeClass("active");
	$("#showpostponing .tile-stats").removeClass("active");
	$("#showmailing .tile-stats").addClass("active");


});

$('#showphoning').click(function() {

 	$('#postponing').hide( "slow");
	$('#mailing').hide( "slow");

	$('#mailing-step-2').hide( "slow");
	$('#mailing-step-3').hide( "slow");
	$('#mailing-step-custom').hide( "slow");

	$('#phoning').show( "slow");


	$("#showmailing .tile-stats").removeClass("active");
	$("#showpostponing .tile-stats").removeClass("active");
	$("#showphoning .tile-stats").addClass("active");

});

$('#showpostponing').click(function() {

 	$('#phoning').hide( "slow");
	$('#mailing').hide( "slow");

	$('#mailing-step-2').hide( "slow");
	$('#mailing-step-3').hide( "slow");
	$('#mailing-step-custom').hide( "slow");

	$('#postponing').show( "slow");




	$("#showphoning .tile-stats").removeClass("active");
	$("#showmailing .tile-stats").removeClass("active");
	$("#showpostponing .tile-stats").addClass("active");

});


$('.radio2').click(function() {
   if($("input:radio[class='choosedateradio']").is(":checked")) {

	$('.customdate').show( "slow");
   }else{

   		$('.customdate').hide( "slow");

   }


});



$('#template').click(function() {

 	$('#mailing').hide( "slow");
	$('#mailing-step-2').show( "slow");

});

$('#custom').click(function() {

 	$('#mailing').hide( "slow");
	$('#mailing-step-custom').show( "slow");

});

});

</script>

<style>


	.tile-stats.active {

		background-color: #3E4552 !important;

	}

	.tile-stats.active h3 {

		color: #FFFFFF;

	}


</style>

<div class="col-md-12">
  <section id="servistab" class="profile-feed" style="display:block; margin: 50px auto 40px; width: 1000px;">
  <h2 style="margin-top: 20px;"><?php
      echo $getclient['user_name'];?><small>&nbsp;&nbsp;historie mailingu</small> <span style="float:right;font-size: 21px;padding-top: 6px;">Příští mailing: <u><?php if (isset($getclient['notification']) && $getclient['notification'] == "0000-00-00") {echo "neurčeno";} else {echo $getclient['notifi'];}?></u></span></h2>
  <hr>

    <!-- profile stories -->
    <div class="profile-stories">

      <article class="story" style="margin: 40px 0 30px 0; min-height: 89px;">

        <div id="table-2_wrapper" class="dataTables_wrapper form-inline" role="grid" style="margin-bottom: 30px;">

<?php

$mailsquery = $mysqli->query("SELECT *, DATE_FORMAT(datetime, '%d. %m. %Y') as dateformated, DATE_FORMAT(datetime, '%T') as hoursmins FROM demands_mails_history WHERE demand_id = '$id' ORDER BY id desc");
if (mysqli_num_rows($mailsquery) > 0) {
    ?>

        <table class="table table-bordered table-striped datatable dataTable" id="table-2" aria-describedby="table-2_info">
        <thead>
          <tr role="row">
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Average Grade: activate to sort column ascending" style="width: 200px;">Předmět</th>
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Curriculum / Occupation: activate to sort column ascending" style="width: 90px; text-align: center;">Datum</th>
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Curriculum / Occupation: activate to sort column ascending" style="width: 90px; text-align: center;">Čas</th>
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 100px; text-align: center;">Odeslal</th>
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 80px; text-align: center;">Akce</th></tr>
        </thead>


          <tbody role="alert" aria-live="polite" aria-relevant="all">
          <?php
    while ($mail = mysqli_fetch_array($mailsquery)) {
        $adminquery = $mysqli->query("SELECT user_name FROM demands WHERE id = '" . $mail['admin_id'] . "'");
        $admin = mysqli_fetch_array($adminquery);

        if (isset($mail['customer']) && $mail['customer'] == 10) { ?>


            <script type="text/javascript">

              jQuery(document).ready(function($)
              {

                $('.showmail-<?= $mail['id'] ?>').click(function() {

                  $(".showmail-<?= $mail['id'] ?>").hide("slow");
                  $(".hidemail-<?= $mail['id'] ?>").show("slow");
                  $(".mail-<?= $mail['id'] ?>").show("slow");

                });

                $('.hidemail-<?= $mail['id'] ?>').click(function() {

                  $(".hidemail-<?= $mail['id'] ?>").hide("slow");
                  $(".mail-<?= $mail['id'] ?>").hide("slow");
                  $(".showmail-<?= $mail['id'] ?>").show("slow");

                });

              });

            </script>



          <tr class="odd" >
            <td class=" " style="background-color: #ffffff !important; height: 43px !important;"><i class="fa fa-phone" style="font-size: 15px; padding: 0 5px 0 5px;"></i> <?= $mail['title'] ?></td>
            <td class=" " style="background-color: #ffffff !important;"><center><?= $mail['dateformated'] ?></center></td>
            <td class=" " style="background-color: #ffffff !important;"><center><?= $mail['hoursmins'] ?></center></td>
            <td class=" " style="background-color: #ffffff !important;"><center><?= $admin['user_name'] ?></center></td>
            <td class=" " style="text-align: center;background-color: #ffffff !important;">
              <a class="showmail-<?= $mail['id'] ?> btn btn-primary btn-sm btn-icon icon-left">
                <i class="entypo-search"></i>
                Zobrazit telefonát
              </a>
              <a class="hidemail-<?= $mail['id'] ?> btn btn-primary btn-sm btn-icon icon-left" style="display: none;">
                <i class="entypo-cancel"></i>
                Skrýt telefonát
              </a>
            </td>
          </tr>
          <tr class="mail-<?= $mail['id'] ?>" style="display: none;">
            <td colspan="5" style="background-color: #f8f8f8 !important;">
          <div class="well well-lg" style="background-color: #FFFFFF;">
            <?= $mail['text'] ?>
            </div>
            <center><a style="margin-bottom: 12px; margin-right: 26px; font-size: 20px; padding: 30px 100px 30px 150px;" class="hidemail-<?= $mail['id'] ?> btn btn-primary btn-icon icon-left btn-lg">
          <i class="entypo-cancel" style="line-height: 60px;font-size: 40px; padding: 10px 20px;"></i>
          Skrýt telefonát
        </a></center>
          </td>
             </tr>
             </tr>


            <?php
        } elseif (isset($mail['customer']) && $mail['customer'] == 11) { ?>


            <script type="text/javascript">

              jQuery(document).ready(function($)
              {

                $('.showmail-<?= $mail['id'] ?>').click(function() {

                  $(".showmail-<?= $mail['id'] ?>").hide("slow");
                  $(".hidemail-<?= $mail['id'] ?>").show("slow");
                  $(".mail-<?= $mail['id'] ?>").show("slow");

                });

                $('.hidemail-<?= $mail['id'] ?>').click(function() {

                  $(".hidemail-<?= $mail['id'] ?>").hide("slow");
                  $(".mail-<?= $mail['id'] ?>").hide("slow");
                  $(".showmail-<?= $mail['id'] ?>").show("slow");

                });

              });

            </script>



          <tr class="odd" >
            <td class=" " style="background-color: #ffffff !important; height: 43px !important;"><i class="fa fa-mail-forward" style="font-size: 14px; padding: 0 6px 0 2px;"></i> <?= $mail['title'] ?></td>
            <td class=" " style="background-color: #ffffff !important;"><center><?= $mail['dateformated'] ?></center></td>
            <td class=" " style="background-color: #ffffff !important;"><center><?= $mail['hoursmins'] ?></center></td>
            <td class=" " style="background-color: #ffffff !important;"><center><?= $admin['user_name'] ?></center></td>
            <td class=" " style="text-align: center;background-color: #ffffff !important;">
              <a class="showmail-<?= $mail['id'] ?> btn btn-primary btn-sm btn-icon icon-left">
                <i class="entypo-search"></i>
                Zobrazit důvod
              </a>
              <a class="hidemail-<?= $mail['id'] ?> btn btn-primary btn-sm btn-icon icon-left" style="display: none;">
                <i class="entypo-cancel"></i>
                Skrýt důvod
              </a>
            </td>
          </tr>
          <tr class="mail-<?= $mail['id'] ?>" style="display: none;">
            <td colspan="5" style="background-color: #f8f8f8 !important;">
          <div class="well well-lg" style="background-color: #FFFFFF;">
            <?= $mail['text'] ?>
            </div>
            <center><a style="margin-bottom: 12px; margin-right: 26px; font-size: 20px; padding: 30px 100px 30px 150px;" class="hidemail-<?= $mail['id'] ?> btn btn-primary btn-icon icon-left btn-lg">
          <i class="entypo-cancel" style="line-height: 60px;font-size: 40px; padding: 10px 20px;"></i>
          Skrýt důvod
        </a></center>
          </td>
             </tr>
             </tr>


            <?php
        } else {
            ?>


            <script type="text/javascript">

              jQuery(document).ready(function($)
              {

                $('.showmail-<?= $mail['id'] ?>').click(function() {

                  $(".showmail-<?= $mail['id'] ?>").hide("slow");
                  $(".hidemail-<?= $mail['id'] ?>").show("slow");
                  $(".mail-<?= $mail['id'] ?>").show("slow");

                });

                $('.hidemail-<?= $mail['id'] ?>').click(function() {

                  $(".hidemail-<?= $mail['id'] ?>").hide("slow");
                  $(".mail-<?= $mail['id'] ?>").hide("slow");
                  $(".showmail-<?= $mail['id'] ?>").show("slow");

                });

              });

            </script>



          <tr class="odd" >
            <td class=" " style="background-color: #ffffff !important; height: 43px !important;"><i class="fa fa-envelope" style="font-size: 14px; padding: 0 6px 0 2px;"></i> <?= $mail['title'] ?></td>
            <td class=" " style="background-color: #ffffff !important;"><center><?= $mail['dateformated'] ?></center></td>
            <td class=" " style="background-color: #ffffff !important;"><center><?= $mail['hoursmins'] ?></center></td>
            <td class=" " style="background-color: #ffffff !important;"><center><?= $admin['user_name'] ?></center></td>
            <td class=" " style="text-align: center;background-color: #ffffff !important;">
              <a class="showmail-<?= $mail['id'] ?> btn btn-primary btn-sm btn-icon icon-left">
                <i class="entypo-search"></i>
                Zobrazit mail
              </a>
              <a class="hidemail-<?= $mail['id'] ?> btn btn-primary btn-sm btn-icon icon-left" style="display: none;">
                <i class="entypo-cancel"></i>
                Skrýt mail
              </a>
            </td>
          </tr>
          <tr class="mail-<?= $mail['id'] ?>" style="display: none;">
            <td colspan="5" style="background-color: #f8f8f8 !important;">
          <div class="well well-lg" style="background-color: #FFFFFF;">
            <?= $mail['text'] ?>
            </div>
            <center><a style="margin-bottom: 12px; margin-right: 26px; font-size: 20px; padding: 30px 100px 30px 150px;" class="hidemail-<?= $mail['id'] ?> btn btn-primary btn-icon icon-left btn-lg">
          <i class="entypo-cancel" style="line-height: 60px;font-size: 40px; padding: 10px 20px;"></i>
          Skrýt mail
        </a></center>
          </td>
             </tr>


         <?php }
    }
    ?>
          </tbody>

        </table>
<?php } else { ?>


<div class="well well-lg" style="margin-bottom: 0;">

<h3 style="text-align: center;">Zatím nebyly záslány žádné emaily.</h3>

</div>


<?php } ?>
        </div>

      </article>

    </div>

</section>
</div>

<div class="clear"></div>





<div id="mailing" style="display: none;" class="col-md-12" id="choosecustomer">
      <div class="well" style="display:block; margin: 50px auto 40px; width: 800px;">
        <h2 class="specialborderbottom" style="margin-bottom: 20px;padding-bottom: 18px;text-align:center;">Vyberte druh mailingu</h2>
        <div id="template" class="col-sm-6" style="cursor:pointer;">
          <div class="tile-stats tile-gray spsle" style="border: 1px solid #DDDDDD;    background: #FFFFFF;">
            <div class="icon" style="top: 20px !important;right: 10px;"><i style="font-size: 60px;" class="fa fa-folder-open"></i></div>
            <div class="num"></div> <h3>Šablona</h3> <p></p>
          </div>
        </div>


        <div id="custom" class="col-sm-6" style="cursor:pointer;">
          <div class="tile-stats tile-gray spsle" style="border: 1px solid #DDDDDD;    background: #FFFFFF;">
            <div class="icon" style="top: 20px !important;"><i style="font-size: 60px;" class="fa fa-pencil"></i></div>
            <div class="num"></div> <h3>Vlastní</h3> <p></p>
          </div>
        </div>

         <div style="clear:both;"></div>
</div>
</div>





<div class="col-md-12">
<form id="mailing-step-2" <?php if (!isset($_POST['template']) || $_POST['template'] == "") { ?>style="display: none;"<?php } ?> role="form" method="post" name="myform" class="form-horizontal form-groups-bordered validate" enctype='multipart/form-data' action="solo-mail?id=<?= $getclient['id'] ?>">

 <div class="well" style="display:block; margin: 50px auto 40px; width: 800px;">

			<?php

if (isset($getclient['customer']) && $getclient['customer'] == 1) {

    $cliquery = $mysqli->query('SELECT * FROM demands_mails_templates WHERE customer = 1 AND type = "zakladni"') or die($mysqli->error);
    $cliquery2 = $mysqli->query('SELECT * FROM demands_mails_templates WHERE customer = 1 AND type = "microsilk"') or die($mysqli->error);
    $cliquery3 = $mysqli->query('SELECT * FROM demands_mails_templates WHERE customer = 1 AND type = "wipod"') or die($mysqli->error);

    if (isset($_POST['template'])) {

        $selecteduserquery = $mysqli->query('SELECT * FROM demands_mails_templates WHERE id="' . $_POST['template'] . '"') or die($mysqli->error);
        $suser = mysqli_fetch_assoc($selecteduserquery);

    }
    ?>
			<div class="form-group" style="text-align: center; width: 100%; position: relative; margin-left: 0; margin-right: 0; margin-bottom: 22px; margin-top: 18px;">

						<div style="width: 300px; height: 41px; margin: 0 auto; display: block;">
							<div class="input-group" style="width: 100%; float: left;">

							<select id="choosepoptavka" name="template" class="select2" data-allow-clear="true" data-placeholder="Vyberte šablonu..." style="width: 100%; float:left;" >
				               <option></option>
				               <optgroup label="Základní">
				                 <?php while ($dem = mysqli_fetch_array($cliquery)) { ?>

				                 <option value="<?= $dem['id'] ?>" <?php if (isset($_POST['template']) && $_POST['template'] == $dem['id']) {echo 'selected';}?>><?= $dem['title'] ?></option>

				                 <?php } ?>
				             </optgroup>

				             <optgroup label="Microsilk">
				                 <?php while ($dem = mysqli_fetch_array($cliquery2)) { ?>

				                 <option value="<?= $dem['id'] ?>" <?php if (isset($_POST['template']) && $_POST['template'] == $dem['id']) {echo 'selected';}?>><?= $dem['title'] ?></option>

				                 <?php } ?>
				             </optgroup>

				             <optgroup label="WiPod">

				                 <?php while ($dem = mysqli_fetch_array($cliquery3)) { ?>

				                 <option value="<?= $dem['id'] ?>" <?php if (isset($_POST['template']) && $_POST['template'] == $dem['id']) {echo 'selected';}?>><?= $dem['title'] ?></option>

				                 <?php } ?>
				             </optgroup>

				            </select>




							</div>
						</div>
					</div>
					<?php
} elseif (isset($getclient['customer']) && $getclient['customer'] == 1) {

    ?>


							<?php $cliquery = $mysqli->query('SELECT * FROM demands_mails_templates WHERE customer = 0') or die($mysqli->error);

    if (isset($_POST['template'])) {

        $selecteduserquery = $mysqli->query('SELECT * FROM demands_mails_templates WHERE id="' . $_POST['template'] . '"') or die($mysqli->error);
        $suser = mysqli_fetch_assoc($selecteduserquery);

    }
    ?>
			<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label" style="    padding-top: 13px;">Šablona</label>

						<div class="col-sm-5">
							<div class="input-group" style="width: 100%; float: left;">

							<select id="choosepoptavka" name="template" class="select2" data-allow-clear="true" data-placeholder="Vyberte šablonu..." style="width: 100%; float:left;" >
				                <option <?php if (isset($_POST['template'])) {echo 'value="' . $suser['id'] . '"';}?>><?php if (isset($_POST['template'])) {echo $suser['title'];}?></option>
				                 <?php while ($dem = mysqli_fetch_array($cliquery)) {if (isset($_POST['template']) && $_POST['template'] != $dem['id']) { ?><option value="<?= $dem['id'] ?>"><?= $dem['title'] ?></option><?php }}?>
				            </select>


							</div>
						</div>
					</div>


				<?php
}?>


						<center>
	<div class="form-group default-padding">
		<button type="submit" class="btn btn-lg btn-primary btn-icon icon-left" style="padding-right: 20px;">
Načíst šablonu<i class="entypo-download"></i> </button>
	</div></center>
</div>
</form>
</div>





<?php
if (isset($_POST['template']) && $_POST['template'] != "") {

    $templatequery = $mysqli->query("SELECT * FROM demands_mails_templates WHERE id = '" . $_POST['template'] . "'");
    $template = mysqli_fetch_assoc($templatequery);

    ?>
<div class="col-md-12">
<form id="mailing-step-3" role="form" method="post" name="myform" class="form-horizontal form-groups-bordered validate" enctype='multipart/form-data' action="solo-mail?id=<?= $getclient['id'] ?>&action=send">

 <div style="display:block; margin: 0px auto 40px; width: 1000px;">

<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Úprava mailu
					</div>

				</div>

						<div class="panel-body">

					<div class="form-group">
						<label for="field-1" class="col-sm-2 control-label">Předmět</label>

						<div class="col-sm-5">
							<input type="text" class="form-control input-lg" name="title" id="field-1" placeholder="Předmět emailu" value="<?= $template['title'] ?>">
						</div>
					</div>


					<div class="form-group">
						<label for="field-ta" class="col-sm-2 control-label">Šablona</label>

						<div class="col-sm-8">
						<div class="well well-lg" style="background-color: #FFFFFF;">
						<?= $template['text'] ?>
						</div>
						</div>
					</div>
					<textarea name="text" style="display: none;"><?= $template['text'] ?></textarea>
					<input type="text" name="customer" value="<?= $template['customer'] ?>" style="display: none;">


					<div class="form-group">
						<label class="col-sm-2 control-label">Notifikace</label>
						<div class="col-sm-7" style="width: 688px;">
							<div class="radio radio2" style="width: 100px; float: left;">
								<label>
									<input type="radio" name="notificationdate" value="none" checked>Žádná
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
								<input class="customdate form-control datepicker" type="text" name="notificationchooseddate" data-format="yyyy-mm-dd" placeholder="Datum příští notifikace" style="display: none; width: 148px;float: right">


						</div>
					</div>





		</div>



			</div>



						<center>
	<div class="form-group default-padding">
		<button type="submit" class="btn btn-lg btn-primary btn-icon icon-left" style="padding-right: 24px;">
Odeslat<i class="entypo-mail"></i> </button>
	</div></center>
</div>
</form>
</div>
<?php } ?>





<div class="col-md-12">
<form id="mailing-step-custom" style="display: none;" role="form" method="post" name="myform" class="form-horizontal form-groups-bordered validate" enctype='multipart/form-data' action="solo-mail?id=<?= $getclient['id'] ?>&action=send">

 <div style="display:block; margin: 0px auto 40px; width: 1000px;">


 			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Obsah mailu
					</div>

				</div>

						<div class="panel-body">

					<div class="form-group">
						<label for="field-1" class="col-sm-2 control-label">Předmět</label>

						<div class="col-sm-5">
							<input type="text" class="form-control input-lg" name="title" id="field-1" placeholder="Předmět emailu" value="">
						</div>
					</div>




										<div class="form-group">
						<label class="col-sm-2 control-label">Přednatavený mail</label>
						<div class="col-sm-7" >
							<script type="text/javascript">

						jQuery(document).ready(function($)
						{

							$('.mailusradiozaklad').click(function() {


							   	$('.allmailus').hide( "slow");
								$('.changusnamus').attr('name', 'totus');

								$('.mailuszaklad').show( "slow");
								$('.nameruszaklad').attr('name', 'text');

							});

						});

					</script>
						<div class="radio" style="width: 100px; float: left;">
								<label>
									<input type="radio" name="choosedmailus" value="zadny" class="mailusradiozaklad" checked>Žádný
								</label>
						</div>

					<?php $prednastavenemaily = $mysqli->query("SELECT * FROM demands_mails_templates WHERE customer = 9");
while ($mail = mysqli_fetch_array($prednastavenemaily)) { ?>
						<div class="radio" style="width: 200px; float: left;">
								<label>
									<input type="radio" name="choosedmailus" class="mailusradio<?= $mail['id'] ?>" value="<?= $mail['id'] ?>"><?= $mail['title'] ?>
								</label>
						</div>
							<?php } ?>



						</div>
					</div>

				<div class="form-group">
					<label for="field-1" class="col-sm-2 control-label">Text mailu</label>

					<div class="col-sm-9 allmailus mailuszaklad">
				    <textarea class="form-control changusnamus nameruszaklad" data-stylesheet-url="<?= $home ?>/admin/assets/css/wysihtml5-color.css" name="text" id="sample_wysiwyg" style="height: 360px;"></textarea>
					</div>
					<?php $prednastavenemaily = $mysqli->query("SELECT * FROM demands_mails_templates WHERE customer = 9");
while ($mail = mysqli_fetch_array($prednastavenemaily)) {
    ?>
					<script type="text/javascript">

						jQuery(document).ready(function($)
						{

							$('.mailusradio<?= $mail['id'] ?>').click(function() {


							   	$('.allmailus').hide( "slow");

							   	$('.changusnamus').attr('name', 'totus');

								$('.mailus<?= $mail['id'] ?>').show( "slow");
								$('.namerus<?= $mail['id'] ?>').attr('name', 'text');


							});

						});

					</script>
					<div class="col-sm-9 allmailus mailus<?= $mail['id'] ?>" style="display: none;">
					<textarea class="form-control changusnamus namerus<?= $mail['id'] ?>" data-stylesheet-url="<?= $home ?>/admin/assets/css/wysihtml5-color.css" name="totus" id="sample_wysiwyg<?= $mail['id'] ?>" style="height: 360px;"><?= $mail['text'] ?></textarea>
						</div>
					<?php } ?>

				</div>
					<input type="text" name="customer" value="9" style="display: none;">

					<div class="form-group">
						<label class="col-sm-2 control-label">Notifikace</label>
						<div class="col-sm-7" style="width: 688px;">
							<div class="radio radio2" style="width: 100px; float: left;">
								<label>
									<input type="radio" name="notificationdate" value="none" checked>Žádná
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
								<input class="customdate form-control datepicker" type="text" name="notificationchooseddate" data-format="yyyy-mm-dd" placeholder="Datum příští notifikace" style="display: none; width: 148px;float: right">


						</div>
					</div>



		</div>



			</div>


						<center>
	<div class="form-group default-padding">
		<button type="submit" class="btn btn-lg btn-primary btn-icon icon-left" style="padding-right: 24px;">
Odeslat<i class="entypo-mail"></i> </button>
	</div></center>
</div>
</form>
</div>




<div class="col-md-12">
<form id="phoning" style="display: none;" role="form" method="post" name="myform" class="form-horizontal form-groups-bordered validate" enctype='multipart/form-data' action="solo-mail?id=<?= $getclient['id'] ?>&action=send">
<input type="hidden" name="length" value="14">




			<div class="row">

		<div style="width: 1000px; margin: 0 auto; display: block;">

					<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Telefonní interakce
					</div>

				</div>

			<div class="panel-body">


        <input type="text" name="title" value="Telefonát" style="display: none;">
				<div class="form-group">
					<label for="field-1" class="col-sm-2 control-label">Obsah telefonátu</label>

					<div class="col-sm-9 allmailus mailuszaklad">
				    <textarea class="form-control changusnamus nameruszaklad" data-stylesheet-url="<?= $home ?>/admin/assets/css/wysihtml5-color.css" name="text" id="sample_wysiwyg" style="height: 360px;"></textarea>
					</div>

				</div>


					<input type="text" name="customer" value="10" style="display: none;">


					<div class="form-group">
						<label class="col-sm-2 control-label">Notifikace</label>
						<div class="col-sm-7" style="width: 688px;">
							<div class="radio radio2" style="width: 100px; float: left;">
								<label>
									<input type="radio" name="notificationdate" value="none" checked>Žádná
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
								<input class="customdate form-control datepicker" type="text" name="notificationchooseddate" data-format="yyyy-mm-dd" placeholder="Datum příští notifikace" style="display: none; width: 148px;float: right">


						</div>
					</div>



		</div>



			</div></div>	</div>




						<center>
	<div class="form-group default-padding">
		<button type="submit" class="btn btn-lg btn-primary btn-icon icon-left" style="padding-right: 24px;">
Uložit akci<i class="fa fa-check"></i> </button>
	</div></center>
</form>
</div>



<div class="col-md-12">
<form id="postponing" style="display: none;" role="form" method="post" name="myform" class="form-horizontal form-groups-bordered validate" enctype='multipart/form-data' action="solo-mail?id=<?= $getclient['id'] ?>&action=send">
<input type="hidden" name="length" value="14">




			<div class="row">

		<div style="width: 1000px; margin: 0 auto; display: block;">

					<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Odložení
					</div>

				</div>

						<div class="panel-body">

               <input type="text" name="title" value="Odložení" style="display: none;">
				<div class="form-group">
					<label for="field-1" class="col-sm-2 control-label">Důvod odložení</label>

					<div class="col-sm-9 allmailus mailuszaklad">
				    <textarea class="form-control changusnamus nameruszaklad" data-stylesheet-url="<?= $home ?>/admin/assets/css/wysihtml5-color.css" name="text" id="sample_wysiwyg" style="height: 360px;"></textarea>
					</div>

				</div>
					<input type="text" name="customer" value="11" style="display: none;">

					<div class="form-group">
						<label class="col-sm-2 control-label">Notifikace</label>
						<div class="col-sm-7" style="width: 688px;">
							<div class="radio radio2" style="width: 100px; float: left;">
								<label>
									<input type="radio" name="notificationdate" value="none" checked>Žádná
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
								<input class="customdate form-control datepicker" type="text" name="notificationchooseddate" data-format="yyyy-mm-dd" placeholder="Datum příští notifikace" style="display: none; width: 148px;float: right">


						</div>
					</div>



		</div>



			</div></div>	</div>




						<center>
	<div class="form-group default-padding">
		<button type="submit" class="btn btn-lg btn-primary btn-icon icon-left" style="padding-right: 24px;">
Uložit akci<i class="fa fa-check"></i> </button>
	</div></center>
</form>
</div>

<footer class="main" style="    margin-top: 300px;">


	&copy; <?= date("Y") ?> <span style=" float:right;"><?php
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$total_time = round(($finish - $start), 4);

echo 'PHP '.PHP_VERSION.' | Page generated in ' . $total_time . ' seconds.';?></span>

</footer>	</div>



	</div>

<?php include VIEW . '/default/footer.php'; ?>

