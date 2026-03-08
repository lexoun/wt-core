<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$pagetitle = "Vydané úkoly";

$bread1 = "Přehled úkolů";
$abread1 = "prehled-ukolu";

if (isset($_REQUEST['od'])) {$od = $_REQUEST['od'];}

$status = $_REQUEST['status'];

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
	<div class="col-md-2 col-sm-2">
		<h2 style="margin-top: 12px;"><?= $pagetitle ?></h2>
	</div>
	<?php
$cliquery = $mysqli->query('SELECT id, user_name FROM demands WHERE role != "client" AND active = 1') or die($mysqli->error);
$demquery = $mysqli->query('SELECT id, user_name FROM demands') or die($mysqli->error);
?>
<div class="col-md-5 col-sm-5" style="float:right;padding-left:0">
		<button id="addtask" type="button" class="btn btn-default btn-block" style="height: 71px; margin-bottom: 14px;  font-size: 17px;">Přidat úkol</button>

<div id="taskform" class="well" style="display: none; width: 700px; float:inherit; margin: 0 auto 50px;">
    <h2 class="specialborderbottom" style="margin-bottom: 20px;">Nový úkol</h2>
		<form class="validate" id="taskform" role="form" method="post" enctype='multipart/form-data' action="/admin/controllers/task-controller?task=add&redirect=pages/tasks/vydane-ukoly">
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
<div style="padding-left: 5px; margin-bottom: 10px;   float: left; margin-top: -63px;">
		<div style="font-size: 12px;float:left;margin-top: 29px;">

						<a href="./vydane-ukoly"><span style="<?php if (!isset($status)) {echo 'font-weight: bold;';}?>margin-right: 10px; cursor: pointer;"><i class="entypo-flag" style="padding-right: 2px;"></i>Všechny úkoly</span></a>

						<a href="./vydane-ukoly?status=0"><span style="<?php if (isset($status) && $status == 0) {echo 'font-weight: bold;';}?>margin-right: 12px; color: #727272;cursor: pointer;"><i class="entypo-flag" style="padding-right: 2px;"></i>Úkol čeká</span></a>

						<a href="./vydane-ukoly?status=1"><span style="<?php if (isset($status) && $status == 1) {echo 'font-weight: bold;';}?>margin-right: 12px; color: #d42020;cursor: pointer;"><i class="entypo-flag" style="padding-right: 2px;"></i>Úkol odmítnut</span></a>

						<a href="./vydane-ukoly?status=2"><span style="<?php if (isset($status) && $status == 2) {echo 'font-weight: bold;';}?>margin-right: 12px; color: #0072bc;cursor: pointer;"><i class="entypo-flag" style="padding-right: 2px;"></i>Úkol v řešení</span></a>

						<a href="./vydane-ukoly?status=3"><span style="<?php if (isset($status) && $status == 3) {echo 'font-weight: bold;';}?>margin-right: 12px; color: #04a500;cursor: pointer;"><i class="entypo-flag" style="padding-right: 2px;"></i>Úkol splněn</span></a>

		</div>

	</div>

<div class="profile-env">
<!-- Pager for search results --><div class="row">


	<section class="profile-feed" style="width: 100%;float: left;padding-left:15px;padding-right: 15px;">

	<div class="panel-group" id="vydane-ukoly">

		<?php
if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {$od = 1;}
$perpage = 6;
$s_lol = $od - 1;
$s_pocet = $s_lol * $perpage;

if (isset($status)) {

    $currentpage = 'vydane-ukoly?status=' . $status;

    $tasks_max_query = $mysqli->query("SELECT COUNT(*) AS MaxNumber FROM tasks WHERE request_id = '" . $client['id'] . "' AND status = '$status'") or die($mysqli->error);
    $tasks_max = mysqli_fetch_array($tasks_max_query);
    $pocet_prispevku = $tasks_max['MaxNumber'];
    $demandstasksquery = $mysqli->query('SELECT *, DATE_FORMAT(date, "%d. %m. %Y") as dateformated FROM tasks WHERE request_id = "' . $client['id'] . '" AND status =  "' . $status . '" ORDER BY id desc limit ' . $s_pocet . ',' . $perpage) or die($mysqli->error);
} else {

    $currentpage = 'vydane-ukoly';

    $tasks_max_query = $mysqli->query("SELECT COUNT(*) AS MaxNumber FROM tasks WHERE request_id = '" . $client['id'] . "'") or die($mysqli->error);
    $tasks_max = mysqli_fetch_array($tasks_max_query);
    $pocet_prispevku = $tasks_max['MaxNumber'];
    $demandstasksquery = $mysqli->query('SELECT *, DATE_FORMAT(date, "%d. %m. %Y") as dateformated FROM tasks WHERE request_id = "' . $client['id'] . '" ORDER BY id desc limit ' . $s_pocet . ',' . $perpage) or die($mysqli->error);
}

while ($demandstasks = mysqli_fetch_array($demandstasksquery)) {

    $check = $mysqli->query('SELECT * FROM mails_recievers WHERE admin_id = "' . $client['id'] . '" AND type_id = "' . $demandstasks['id'] . '" AND type = "task"') or die($mysqli->error);
    if (mysqli_num_rows($check) < 1) {

        task($demandstasks, $client['avatar'], $access_edit, 'pages/tasks/vydane-ukoly');

    }
}?>

		</div>
	</section>






</div><!-- Footer -->
<!-- Pager for search results --><div class="row">
	<div class="col-md-12">
		<center><ul class="pagination pagination-sm">
			<?php
include VIEW . "/default/pagination.php";?>
		</ul></center>
	</div>
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

