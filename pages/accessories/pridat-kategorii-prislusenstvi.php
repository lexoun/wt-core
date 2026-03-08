<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$clientnamequery = $mysqli->query('SELECT * FROM demands where id = "' . $servis['clientid'] . '"') or die($mysqli->error);
$clientname = mysqli_fetch_array($clientnamequery);

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "add" && $_REQUEST['site'] == "wellnesstrade") {

    if ($_POST['name'] != "") {

        $seoslug = odkazy($_POST['name']);

        $insert = "INSERT INTO products_cats (name, seoslug, customer, status, spec, discount) VALUES ('" . $_POST['name'] . "', '$seoslug', '" . $_POST['customer'] . "', '" . $_POST['shower'] . "', '" . $_POST['isspec'] . "', '" . $_POST['discount'] . "')";

        $insertresults = $mysqli->query($insert) or die($mysqli->error);

        Header("Location:https://www.wellnesstrade.cz/admin/pages/accessories/kategorie-prislusenstvi?success=add");
        exit;
    }}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "add" && $_REQUEST['site'] == "spamall") {

    if ($_POST['name'] != "") {

        $insert = "INSERT INTO shops_categories (shop_id, name, slug, parent_id) VALUES ('3','" . $_POST['name'] . "', '" . $_POST['slug'] . "','" . $_POST['parent'] . "')";

        $insertresults = $mysqli->query($insert) or die($mysqli->error);

        Header("Location:https://www.wellnesstrade.cz/admin/pages/accessories/kategorie-prislusenstvi?success=add");
        exit;
    }}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "add" && $_REQUEST['site'] == "spahouse") {

    if ($_POST['name'] != "") {

        $insert = "INSERT INTO shops_categories (id, shop_id, name, slug, parent_id) VALUES ('" . $_POST['id'] . "','4','" . $_POST['name'] . "', '" . $_POST['slug'] . "','" . $_POST['parent'] . "')";

        $insertresults = $mysqli->query($insert) or die($mysqli->error);

        Header("Location:https://www.wellnesstrade.cz/admin/pages/accessories/kategorie-prislusenstvi?success=add");
        exit;
    }}

$categorytitle = "Příslušenství";
$pagetitle = "Přidání kategorie příslušenství";

include VIEW . '/default/header.php';
?>


<script type="text/javascript">


jQuery(document).ready(function($)
{


$('#saunahouse').click(function() {

	$('#choose_type').hide( "slow");
	$('.choosed_saunah').show( "slow");

});

$('#spamall').click(function() {

	$('#choose_type').hide( "slow");
	$('.choosed_spam').show( "slow");

});

$('#spahouse').click(function() {

	$('#choose_type').hide( "slow");
	$('.choosed_spahouse').show( "slow");

});


$('#wellnesstrade').click(function() {

	$('#choose_type').hide( "slow");
	$('.choosed_wt').show( "slow");

});


});


</script>

<div class="col-md-12" id="choose_type" style=" padding-right: 100px;">
      <div class="well" style="display:block; margin: 50px auto 40px; width: 900px;">
        <h2 class="specialborderbottom" style="margin-bottom: 20px;padding-bottom: 18px;text-align:center;">Vyberte obchod</h2>

        <div id="saunahouse" class="col-sm-4" style="cursor:pointer;">
          <div class="tile-stats tile-gray spsle" style="border: 1px solid #DDDDDD;    background: #FFFFFF;">
            <div class="icon" style="top: 36px !important;"><i style="font-size: 60px;" class="entypo-home"></i></div>
            <div class="num"></div> <h3>Saunahouse</h3> <p></p>
          </div>
        </div>

        <div id="spamall" class="col-sm-4" style="cursor:pointer;">
          <div class="tile-stats tile-gray spsle" style="border: 1px solid #DDDDDD;    background: #FFFFFF;">
            <div class="icon" style="top: 36px !important;"><i style="font-size: 60px;" class="entypo-home"></i></div>
            <div class="num"></div> <h3>Spamall</h3> <p></p>
          </div>
        </div>

         <div id="spahouse" class="col-sm-4" style="cursor:pointer;">
          <div class="tile-stats tile-gray spsle" style="border: 1px solid #DDDDDD;    background: #FFFFFF;">
            <div class="icon" style="top: 36px !important;"><i style="font-size: 60px;" class="entypo-home"></i></div>
            <div class="num"></div> <h3>Spahouse</h3> <p></p>
          </div>
        </div>

        <div id="wellnesstrade" class="col-sm-4" style="cursor:pointer;">
          <div class="tile-stats tile-gray spsle" style="border: 1px solid #DDDDDD;    background: #FFFFFF;">
            <div class="icon" style="top: 36px !important;"><i style="font-size: 60px;" class="entypo-home"></i></div>
            <div class="num"></div> <h3>Wellnesstrade</h3> <p></p>
          </div>
        </div>


         <div style="clear:both;"></div>
</div>
</div>


  <div class="choosed_wt col-sm-8 col-sm-offset-2"  style="display: none;">
<form role="form" method="post" class="form-horizontal form-groups-bordered validate" action="pridat-kategorii-prislusenstvi?action=add&site=wellnesstrade" enctype="multipart/form-data">

	<div class="row">

		<div class="col-md-12">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Přidání kategorie příslušenství
					</div>

				</div>

						<div class="panel-body">

					<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Název</label>

						<div class="col-sm-5">
							<input type="text" class="form-control" id="field-1" name="name" value="">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Druh produktu</label>

						<div class="col-sm-5">
							<div class="radio" style="width: 100px; margin-left: 10px;float: left;">
								<label>
									<input type="radio" name="customer" id="optionsRadios1" value="1" checked>Vířivka
								</label>
							</div>
							<div class="radio" style="width: 180px; margin-left: 10px;float: left;">
								<label>
									<input type="radio" name="customer" id="optionsRadios2" value="0">Sauna
								</label>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-3 control-label">Zobrazovat pro klienty</label>

						<div class="col-sm-5">
							<div class="radio" style="width: 100px; margin-left: 10px;float: left;">
								<label>
									<input type="radio" name="shower" id="optionsRadios1" value="0" checked>Ano
								</label>
							</div>
							<div class="radio" style="width: 180px; margin-left: 10px;float: left;">
								<label>
									<input type="radio" name="shower" id="optionsRadios2" value="1">Ne
								</label>
							</div>
						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-3 control-label">Je specifikací? (pouze pro sauny)</label>

						<div class="col-sm-5">
							<div class="radio" style="width: 100px; margin-left: 10px;float: left;">
								<label>
									<input type="radio" name="isspec" id="optionsRadios1" value="1">Ano
								</label>
							</div>
							<div class="radio" style="width: 180px; margin-left: 10px;float: left;">
								<label>
									<input type="radio" name="isspec" id="optionsRadios2" value="0" checked>Ne
								</label>
							</div>
						</div>
					</div>


					<div class="form-group">
						<label for="discount" class="col-sm-3 control-label">Sleva na celou kategorii</label>

						<div class="col-sm-5">
							<input type="text" class="form-control" style="float:left; width: 20%;" id="discount" name="discount" value="">
							<span class="input-group-addon" style="float:left; padding: 9px 25px 8px 9px;">%</span>
						</div>
					</div>


				</div>

			</div>

		</div>
	</div>



			<center>
	<div class="form-group default-padding" style="margin-left: -20px;">
  <a href="kategorie-prislusenstvi"><button type="button" class="btn btn-primary">Zpět</button></a>
		<button type="submit" class="btn btn-success">Přidat kategorii</button>
	</div></center>

</form>

</div>






<div class="choosed_spam col-sm-8 col-sm-offset-2"  style="display: none;">
<form role="form" method="post" class="form-horizontal form-groups-bordered validate" action="pridat-kategorii-prislusenstvi?action=add&site=spamall" enctype="multipart/form-data">

	<div class="row">

		<div class="col-md-12">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Přidání kategorie příslušenství
					</div>

				</div>

						<div class="panel-body">

					<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Název</label>

						<div class="col-sm-5">
							<input type="text" class="form-control" id="field-1" name="name" value="">
						</div>
					</div>

						<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Slug</label>

						<div class="col-sm-5">
							<input type="text" class="form-control" id="field-1" name="slug" value="">
						</div>
					</div>

					<?php $parent_categories_query = $mysqli->query("SELECT id, name FROM shops_categories WHERE parent_id = 0 AND shop_id = 3");?>

					<div class="form-group">
						<label for="discount" class="col-sm-3 control-label">Hlavní kategorie</label>

						<div class="col-sm-5">
								<select style="width: 88%; float:left; display: block;" name="parent" class="form-control">
								<option value="">Vyberte kategorii</option>
								<?php while ($parent_categories = mysqli_fetch_array($parent_categories_query)) { ?>
								<option value="<?= $parent_categories['id'] ?>"><?= $parent_categories['name'] ?></option>
									<?php $subparents_query = $mysqli->query("SELECT id, name FROM shops_categories WHERE parent_id = '" . $parent_categories['id'] . "' AND shop_id = 3");
    while ($subparents = mysqli_fetch_array($subparents_query)) { ?>
 										<option value="<?= $subparents['id'] ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $subparents['name'] ?></option>
								<?php }}?>
							</select>

						</div>
					</div>


				</div>

			</div>

		</div>
	</div>



			<center>
	<div class="form-group default-padding" style="margin-left: -20px;">
  <a href="kategorie-prislusenstvi"><button type="button" class="btn btn-primary">Zpět</button></a>
		<button type="submit" class="btn btn-success">Přidat kategorii</button>
	</div></center>

</form>

</div>




<div class="choosed_spahouse col-sm-8 col-sm-offset-2"  style="display: none;">
<form role="form" method="post" class="form-horizontal form-groups-bordered validate" action="pridat-kategorii-prislusenstvi?action=add&site=spahouse" enctype="multipart/form-data">

	<div class="row">

		<div class="col-md-12">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Přidání kategorie příslušenství
					</div>

				</div>

						<div class="panel-body">
		<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">ID</label>

						<div class="col-sm-5">
							<input type="text" class="form-control" id="field-1" name="id" value="">
						</div>
					</div>
					<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Název</label>

						<div class="col-sm-5">
							<input type="text" class="form-control" id="field-1" name="name" value="">
						</div>
					</div>

						<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Slug</label>

						<div class="col-sm-5">
							<input type="text" class="form-control" id="field-1" name="slug" value="">
						</div>
					</div>

					<?php $parent_categories_query = $mysqli->query("SELECT id, name FROM shops_categories WHERE parent_id = 0 AND shop_id = 4");?>

					<div class="form-group">
						<label for="discount" class="col-sm-3 control-label">Hlavní kategorie</label>

						<div class="col-sm-5">
								<select style="width: 88%; float:left; display: block;" name="parent" class="form-control">
								<option value="">Vyberte kategorii</option>
								<?php while ($parent_categories = mysqli_fetch_array($parent_categories_query)) { ?>
								<option value="<?= $parent_categories['id'] ?>"><?= $parent_categories['name'] ?></option>
									<?php $subparents_query = $mysqli->query("SELECT id, name FROM shops_categories WHERE parent_id = '" . $parent_categories['id'] . "' AND shop_id = 4");
    while ($subparents = mysqli_fetch_array($subparents_query)) { ?>
 										<option value="<?= $subparents['id'] ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $subparents['name'] ?></option>
								<?php }}?>
							</select>

						</div>
					</div>


				</div>

			</div>

		</div>
	</div>



			<center>
	<div class="form-group default-padding" style="margin-left: -20px;">
  <a href="kategorie-prislusenstvi"><button type="button" class="btn btn-primary">Zpět</button></a>
		<button type="submit" class="btn btn-success">Přidat kategorii</button>
	</div></center>

</form>

</div>

<div style="clear: both"></div>





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


