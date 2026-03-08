<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$categorytitle = "Úkoly";
$pagetitle = "Přehled úkolů";
if (isset($_REQUEST['od'])) {$od = $_REQUEST['od'];}

if (isset($_REQUEST['customer'])) {$customer = $_REQUEST['customer'];}
if (isset($_REQUEST['category'])) {$category = $_REQUEST['category'];}
if (isset($_REQUEST['type'])) {$type = $_REQUEST['type'];}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "comment_remove") {
    $displaysuccess = true;
    $successhlaska = "Komentář u úkolu byl úspěšně smazán.";
}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "comment_add") {
    $displaysuccess = true;
    $successhlaska = "Komentář k úkolu byl úspěšně přidán.";
}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "task_remove") {
    $displaysuccess = true;
    $successhlaska = "Úkol byl úspěšně smazán.";
}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "task_add") {
    $displaysuccess = true;
    $successhlaska = "Úkol byl úspěšně přidán.";
}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "task_edit") {
    $displaysuccess = true;
    $successhlaska = "Úkol byl úspěšně upraven.";
}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "task_change") {
    $displaysuccess = true;
    $successhlaska = "Status úkolu byl úspěšně upraven.";
}

$virivky = array("capri", "dreamline", "eden", "tahiti", "tonga", "trinidad");

$sauny = array("tiny", "cavalir", "home", "cube", "charm", "charisma", "exclusive", "lora", "mona", "deluxe", "grand");

include VIEW . '/default/header.php';

?>

<script type="text/javascript">

jQuery(document).ready(function($)
{
	$('#addtask').click(function() {
 $( "#addtask" ).hide( "slow" );
 $( "#taskform" ).show( "slow" );
});

$('#canceladdtask').click(function() {

 $( "#taskform" ).hide( "slow" );
 $( "#addtask" ).show( "slow" );
});

$('.descriptiontext').click(function() {
 $( ".descriptiontext" ).hide( "slow" );
 $( "#editdescription" ).show( "slow" );

});


$('#duplicatereciev').click(function() {

$('#reciev').clone(true).insertBefore("#duplicatereciev").attr('id', 'kappa3').show();
$('#kappa3 #trojka').attr('name', 'reciever[]');

});


$('.removeshit').click(function() {
   $(this).closest('.hovnus').remove();
   event.preventDefault();
});


$('.choosio').click(function() {
   if($("input:radio[id='not_choosed']").is(":checked")) {


  $('#demands_who').hide( "slow");
  $('#clients_who').hide( "slow");
   }

     if($("input:radio[id='choosed_demand']").is(":checked")) {

    $('#clients_who').hide( "slow");
    $('#demands_who').show( "slow");

   }

    if($("input:radio[id='choosed_client']").is(":checked")) {


    $('#demands_who').hide( "slow");
$('#clients_who').show( "slow");
   }

});


});
</script>

<div class="row">
	<div class="col-md-3 col-sm-3">
		<h2><?= $pagetitle ?></h2>
	</div>

	<?php
$cliquery = $mysqli->query('SELECT id, user_name FROM demands WHERE role != "client" AND active = 1') or die($mysqli->error);
$demquery = $mysqli->query('SELECT id, user_name FROM demands') or die($mysqli->error);
?>
<div class="col-md-4 col-sm-4" style="padding-left:0; float: right;">
		<button id="addtask" type="button" class="btn btn-default btn-block" style="height: 71px; margin-bottom: 14px;  font-size: 17px;">Přidat úkol</button>

<div id="taskform" class="well" style="display: none; width: 700px; float:inherit; margin: 0 auto 50px; margin-right: 30% !important;">
    <h2 class="specialborderbottom" style="margin-bottom: 20px;">Nový úkol</h2>
		<form class="validate" id="taskform" role="form" method="post" enctype='multipart/form-data' action="/admin/controllers/task-controller?task=add&redirect=pages/tasks/prehled-ukolu">
		<div class="form-group" style="float:left; width: 100%;">
		<input type="text" style="width: 58%; float: left; margin-right: 2%; margin-bottom: 8px;" name="title" placeholder="Název úkolu" class="form-control" id="field-1" value="" data-validate="required" data-message-required="Musíte zadat název úkolu.">

      <input id="datum3" type="text" style="width: 28%; float: left; margin-bottom: 6px;" name="datum" class="form-control datepicker" data-format="yyyy-mm-dd" placeholder="Datum provedení" data-validate="required" data-message-required="Musíte zvolit datum úkolu.">

      <input type="text" style="width: 12%" class="form-control timepicker" name="time" data-template="dropdown" data-show-seconds="false" data-default-time="00-00" data-show-meridian="false" data-minute-step="5" placeholder="Čas"/>
				</div>
       <div class="form-group" style="float: left; width: 100%; margin-bottom: 0;">
         <?php $adminsquery = $mysqli->query("SELECT id, user_name FROM demands WHERE role != 'client' AND active = 1");
?>
           <div class="hovnus" id="reciev" style="margin-right: 2%; margin-bottom: 12px; width: 49%; float:left; display: none;">
            <select id="trojka" style="width: 88%; float:left;" class="form-control" name="copythis"  data-validate="required" data-message-required="Musíte vybrat příjemce.">
                <option value="">Vyberte adresáta</option>
                <?php while ($admins = mysqli_fetch_array($adminsquery)) {echo '<option value="' . $admins['id'] . '">' . $admins['user_name'] . '</option>';}?>
              </select>
              <i class="removeshit entypo-trash" style="float:left; margin-top: 6px; margin-left: 10px; cursor: pointer;"></i>
            </div>
              <?php mysqli_data_seek($adminsquery, 0);
?>
           <div id="kappa3" class="hovnus" style="margin-right: 2%; margin-bottom: 12px; width: 49%; float:left;"> <select style="width: 88%; float:left;" class="form-control" name="reciever[]"  data-validate="required" data-message-required="Musíte vybrat příjemce.">
                <option value="">Vyberte adresáta</option>
                <?php while ($admins = mysqli_fetch_array($adminsquery)) {echo '<option value="' . $admins['id'] . '">' . $admins['user_name'] . '</option>';}?>
              </select>
              <i class="removeshit entypo-trash" style="float:left; margin-top: 8px; margin-left: 10px; cursor: pointer;"></i>
            </div>
              <button type="button" id="duplicatereciev" style="float: left;margin-bottom: 12px;  width: 49%;" class="btn btn-default btn-icon icon-left btn-addmore">
            Přiřadit dalšího adresáta
            <i class="entypo-plus"></i>
          </button>
          </div>
          <div class="col-sm-6 choosio" style="padding: 18px 4px 14px;">

<div class="col-sm-4" style="padding: 4px;">
    <input id="not_choosed" name="choosed_who" value="none" type="radio" checked>
    <label for="not_choosed" style="padding-left: 4px;">Nepřiřazovat</label>
 </div>
<div class="col-sm-4" style="padding: 4px;">
    <input id="choosed_demand" name="choosed_who" value="demand" type="radio">
    <label for="choosed_demand" style="padding-left: 4px;">K poptávce</label>
  </div>
<div class="col-sm-4" style="padding: 4px;">
    <input id="choosed_client" name="choosed_who" value="client" type="radio">
    <label for="choosed_client" style="padding-left: 4px;">Ke klientovi</label>
 </div>

          </div>
  <div id="demands_who" class="form-group specialformus col-sm-6" style="float: left; display: none;">
      <?php
$demandsq = $mysqli->query("SELECT user_name, id FROM demands WHERE status < 6 AND (customer = 0 OR customer = 3 OR customer = 1)") or die($mysqli->error);

?>
            <select id="choosepoptavka" name="demandus" class="select2" data-allow-clear="true" data-placeholder="Přiřadit úkol k poptávce..."  style="width: 100% !important; margin: 10px 0 10px 0;">
                <option></option>
                  <?php while ($dem = mysqli_fetch_array($demandsq)) { ?><option value="<?= $dem['id'] ?>>"><?= $dem['user_name'] ?></option><?php } ?>
            </select>
							</div>



<div id="clients_who" class="form-group specialformus col-sm-6" style="float: left; display: none;">
      <?php
$demandsq = $mysqli->query("SELECT user_name, id FROM demands WHERE role = 'client'") or die($mysqli->error);

?>
            <select id="choosepoptavka" name="clientus" class="select2" data-allow-clear="true" data-placeholder="Přiřadit úkol ke klientovi..."  style="width: 100% !important; margin: 10px 0 10px 0;">
                <option></option>
                  <?php while ($dem = mysqli_fetch_array($demandsq)) { ?><option value="<?= $dem['id'] ?>>"><?= $dem['user_name'] ?></option><?php } ?>
            </select>
              </div>


			<textarea class="form-control autogrow" name="text" placeholder="Zadání úkolu." style="overflow: hidden; margin-bottom: 8px;word-wrap: break-word; resize: horizontal; height: 80px;"></textarea>
			<button type="submit" class="btn btn-primary" style="width: 82%; height: 71px; margin-bottom: 14px;  font-size: 17px;">Přidat úkol</button>
			<button type="button" id="canceladdtask" class="btn btn-white" style="width: 17%; height: 71px; margin-bottom: 14px;  font-size: 17px;"><i class="entypo-cancel"></i></button>
		</form>
</div>
</div>

</div>



<div class="col-md-12 well" style="border-color: #ebebeb; background-color: #fbfbfb; margin-top: 10px;">
<div class="row">
  <div class="col-md-5">
    <div class="btn-group" style="text-align: left;">
            <?php $mark = "?";?>
            <a href="prehled-ukolu"><label class="btn btn-lg <?php if (!isset($type)) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
              Vše
            </label></a>

            <a href="?type=demand"><label class="btn btn-lg <?php if (isset($type) && $type == 'demand') { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
              Poptávky
            </label></a>
            <a href="?type=client"><label class="btn btn-lg <?php if (isset($type) && $type == 'client') { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
              Klienti
            </label></a>
            <a href="?type=general"><label class="btn btn-lg <?php if (isset($type) && $type == 'general') { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
              Obecné
            </label></a>


          </div>





</div>



</div>
</div><!-- Footer -->

<?php
$query = '';
if (isset($type)) {

    if ($type == 'demand') {

        $query = 'AND demand_id != 0';

    } elseif ($type == 'client') {

        $query = 'AND client_id != 0';

    } elseif ($type == 'general') {

        $query = 'AND demand_id = 0 AND client_id = 0';

    }

}

?>



<div class="row" style="margin-top: 6px;">
	<div class="col-md-6 col-sm-6" style="padding-right: 0; margin-bottom: 10px;">
			<a href="./prijate-ukoly"><h3 style="width: 22%;float:left;">Přijaté úkoly</h3></a>

	</div>

	<div class="col-md-6 col-sm-6" style="padding-left: 5px; margin-bottom: 10px;">

			<a href="./vydane-ukoly"><h3 style="width: 24%;float:left;">Vydané úkoly</h3></a>


	</div>
</div>

<div class="profile-env">
<!-- Pager for search results --><div class="row">


	<section class="profile-feed" style="width: 49%;float: left;padding-left:15px;padding-right: 15px;">

	<div class="panel-group" id="prijate-ukoly">

		<?php

$demandstasksquery = $mysqli->query("SELECT *, DATE_FORMAT(date, '%d. %m. %Y') as dateformated, DATE_FORMAT(due, '%d. %m. %Y') as dueformated FROM mails_recievers tgts, tasks tsks WHERE tgts.admin_id = '" . $client['id'] . "' AND tsks.id = tgts.type_id AND tgts.type = 'task' AND tsks.due >= CURDATE() AND tsks.status != 3 $query ORDER BY tsks.id DESC") or die($mysqli->error);

while ($demandstasks = mysqli_fetch_array($demandstasksquery)) {
    task($demandstasks, $client['avatar'], $access_edit, 'pages/tasks/prehled-ukolu');
}
if (mysqli_num_rows($demandstasksquery) == 0) {
    ?>

       <div class="panel panel-default" style="margin-bottom: 13px;  ">
        <div class="panel-heading">


          <p style="padding: 40px; margin: 0; "><i class="entypo-check" style="font-size: 14px;"></i>  Všechny úkoly máš splněné.</p>

        </div>
  </div>
<?php } ?>

		</div>
		<div class="text-center">
				<a href="./prijate-ukoly" class="btn btn-default btn-icon icon-left" style="padding-right: 14px;">
					<i class="fa fa-angle-double-right" style="padding: 6px 11px;"></i>
					Historie přijatých úkolů
				</a>
			</div>
	</section>



	<section class="profile-feed" style="width: 49%;float: left;padding-left:15px;padding-right: 15px;">
	<div class="panel-group" id="vydane-ukoly">

		<?php
$count = 0;
$demandstasksquery = $mysqli->query("SELECT *, DATE_FORMAT(date, '%d. %m. %Y') as dateformated, DATE_FORMAT(due, '%d. %m. %Y') as dueformated FROM tasks WHERE request_id = '" . $client['id'] . "' AND status != 3 AND due >= CURDATE() $query ORDER BY id desc") or die($mysqli->error);
while ($demandstasks = mysqli_fetch_assoc($demandstasksquery)) {

    $check = $mysqli->query('SELECT * FROM mails_recievers WHERE admin_id = "' . $client['id'] . '" AND type_id = "' . $demandstasks['id'] . '" AND type = "task"') or die($mysqli->error);
    if (mysqli_num_rows($check) < 1) {

        task($demandstasks, $client['avatar'], '', '');

        $count = $count++;
    }
}

if ($count == 0) {
    ?>

       <div class="panel panel-default" style="margin-bottom: 13px;  ">
        <div class="panel-heading">


          <p style="padding: 40px; margin: 0; "><i class="entypo-check" style="font-size: 14px;"></i> Všechny tebou zadané úkoly jsou splněné.</p>

        </div>
  </div>
<?php } ?>



		</div>
		<div class="text-center">
				<a href="./vydane-ukoly" class="btn btn-default btn-icon icon-left" style="padding-right: 14px;">
					<i class="fa fa-angle-double-right" style="padding: 6px 11px;"></i>
					Historie vydaných úkolů
				</a>
			</div>
	</section>


</div><!-- Footer -->
</div>





<footer class="main">


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

