<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

if (isset($_REQUEST['od'])) {$od = $_REQUEST['od'];}
if (isset($_REQUEST['q'])) {$search = $_REQUEST['q'];}

$pagetitle = "Add show";

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'site') {

    $insert_query = $mysqli->query("INSERT INTO administration_sites (name, special, link_url, category_id) VALUES ('" . $_POST['name'] . "', '" . $_POST['special'] . "', '" . $_POST['link_url'] . "', '" . $_POST['category_id'] . "')");
    $id = $mysqli->insert_id;

    Header("Location:https://www.wellnesstrade.cz/admin/pages/system/add_show?succes=edit&site=" . $id);
    exit;

}

include VIEW . '/default/header.php';

?>

<form id="orderform" role="form" method="post" class="form-horizontal form-groups-bordered validate" action="add_show?action=add" enctype="multipart/form-data">

<div class="row">

		<div class="col-md-12">


<div class="panel panel-primary" data-collapsed="0">



						<div class="panel-body">




					<div class="form-group">
						<div class="col-sm-4">
										<input name="seo_url"  class="form-control" type="text">
						</div>
					</div>


					<div class="form-group col-sm-4" style="padding-top: 0;">

						<div class="col-sm-12">cat

							<select class="form-control" name="category_id">
								<option value="0">choose</option>
								<?php
$sites_query = $mysqli->query("SELECT * FROM administration_categories");
while ($site = mysqli_fetch_array($sites_query)) {

    ?>
								<option value="<?= $site['id'] ?>"><?= $site['name'] ?></option>
								<?php } ?>
							</select>

						</div>
					</div>



			<div class="form-group col-sm-4" style="padding-top: 0;">

						<div class="col-sm-12">site

							<select class="form-control" name="site_id">
								<option value="0">choose</option>
								<?php
$sites_query = $mysqli->query("SELECT * FROM administration_sites");
while ($site = mysqli_fetch_array($sites_query)) {

    ?>
								<option value="<?= $site['id'] ?>"><?= $site['name'] ?></option>
								<?php } ?>
							</select>

						</div>
					</div>





					<div class="form-group col-sm-4" style="padding-top: 0;">

						<div class="col-sm-12">main

							<select class="form-control" name="main_id">
								<option value="0">choose</option>
								<?php
$sites_query = $mysqli->query("SELECT * FROM administration_sites");
while ($site = mysqli_fetch_array($sites_query)) {

    ?>
								<option value="<?= $site['id'] ?>"><?= $site['name'] ?></option>
								<?php } ?>
							</select>

						</div>
					</div>



</div>

</div>

	<center>
	<div class="form-group default-padding button-demo">
		<button type="submit" style="margin-bottom: 24px; margin-right: 26px; font-size: 20px; padding: 20px 50px 20px 140px; margin-top: 20px;" data-style="zoom-in" class="ladda-button btn btn-success btn-icon icon-left btn-lg"><i class="entypo-plus" style="line-height: 48px;font-size: 40px; padding: 10px 20px;"></i> <span class="ladda-label">Uložit</span></button>
	</div></center>

</form>




<hr>



<form id="orderform" role="form" method="post" class="form-horizontal form-groups-bordered validate" action="add_show?action=site" enctype="multipart/form-data">

<div class="row">

		<div class="col-md-12">


<div class="panel panel-primary" data-collapsed="0">



						<div class="panel-body">




					<div class="form-group">
						<div class="col-sm-4">
										<input name="name"  class="form-control" type="text">name
						</div>
					</div>

					<div class="form-group">
						<div class="col-sm-4">
										<input name="link_url"  class="form-control" type="text">link
						</div>
					</div>


					<div class="form-group">
						<div class="col-sm-4">
										<input name="special"  class="form-control" type="text">speci
						</div>
					</div>






					<div class="form-group col-sm-4" style="padding-top: 0;">

						<div class="col-sm-12">cat

							<select class="form-control" name="category_id">
								<option value="0">choose</option>
								<?php
$sites_query = $mysqli->query("SELECT * FROM administration_categories");
while ($site = mysqli_fetch_array($sites_query)) {

    ?>
								<option value="<?= $site['id'] ?>"><?= $site['name'] ?></option>
								<?php } ?>
							</select>

						</div>
					</div>




</div>

</div>

	<center>
	<div class="form-group default-padding button-demo">
		<button type="submit" style="margin-bottom: 24px; margin-right: 26px; font-size: 20px; padding: 20px 50px 20px 140px; margin-top: 20px;" data-style="zoom-in" class="ladda-button btn btn-success btn-icon icon-left btn-lg"><i class="entypo-plus" style="line-height: 48px;font-size: 40px; padding: 10px 20px;"></i> <span class="ladda-label">Uložit</span></button>
	</div></center>

</form>






<!-- Pager for search results --><div class="row">
	<div class="col-md-12">
		<center><ul class="pagination pagination-sm">
			<?php
include VIEW . "/default/pagination.php";?>
		</ul>

		<h1 style="margin-bottom: 50px;">Celkem: <?= $max ?></h1>
	</center>
	</div>
</div><!-- Footer -->

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



