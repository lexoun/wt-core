<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

if (isset($_REQUEST['customer'])) {$customer = $_REQUEST['customer'];}

$pagetitle = "Statistika poptávek";

include VIEW . '/default/header.php';

?>


<?php

$query = "id != '' AND status < 8";
$currentpage = "statistika-prodeju";
$allow_sites = "";

if (isset($customer) && $customer != "") {

    $query = $query . ' AND customer = "' . $customer . '"';

}

$startdate = '∞';
$enddate = '∞';
$string_start = '';
$string_end = '';
$start_formated = '';
$end_formated = '';

if (isset($_REQUEST['start_date']) && $_REQUEST['start_date'] != "") {

    $query = $query . ' AND date >= "' . $_REQUEST['start_date'] . '"';

    $string_start = '&start_date=' . $_REQUEST['start_date'];

    $startdate = new DateTime($_REQUEST['start_date']);

    $start_formated = date_format($startdate, 'Y-m-d');
    $startdate = date_format($startdate, 'd. m. Y');


}

if (isset($_REQUEST['end_date']) && $_REQUEST['end_date'] != "") {

    $query = $query . ' AND date <= "' . $_REQUEST['end_date'] . '"';

    $string_end = '&end_date=' . $_REQUEST['end_date'];

    $enddate = new DateTime($_REQUEST['end_date']);

    $end_formated = date_format($enddate, 'Y-m-d');
    $enddate = date_format($enddate, 'd. m. Y');

}

?>




<div class="row">
	<div class="col-md-8 col-sm-7">
		<h2><?= $pagetitle ?></h2>
	</div>

</div>
<!-- Pager for search results --><div class="col-md-12 well" style="border-color: #ebebeb; margin-top: 8px;background-color: #fbfbfb;">


	<div class="btn-group col-sm-7" style="text-align: left; padding: 0;">


				<a href="?customer=<?php echo $string_start;
echo $string_end; ?>"><label class="btn btn-lg <?php if (!isset($customer) || $customer == "" || $customer == "9") { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
											Vše
										</label></a>


						<a href="?customer=0<?php echo $string_start;
echo $string_end; ?>"><label class="btn btn-lg <?php if (isset($customer) && $customer == "0") { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
							Sauny
						</label></a>
						<a href="?customer=1<?php echo $string_start;
echo $string_end; ?>"><label class="btn btn-lg <?php if (isset($customer) && $customer == "1") { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
							Vířivky
						</label></a>

						<a href="?customer=3<?php echo $string_start;
echo $string_end; ?>"><label class="btn btn-lg <?php if (isset($customer) && $customer == "3") { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
							Sauny + Vířivky
						</label></a>





					</div>

<form role="form" method="get" class="form-horizontal form-groups-bordered validate" action="statistika-poptavek" enctype="multipart/form-data" novalidate="novalidate">

	<div class="form-group col-sm-5" style=" padding: 0; margin: 0;">
							<input name="customer" style="display: none;" value="<?php if(isset($customer)){ echo $customer; } ?>">

							<input id="datum1pridatsem" type="text" class="form-control datepicker" name="start_date" data-format="yyyy-mm-dd" placeholder="Počáteční datum" style="height: 41px; width: 140px; margin-right: 10px; float: left;" value="<?= $start_formated ?>">

							<input id="datum1pridatsem" type="text" class="form-control datepicker" name="end_date" data-format="yyyy-mm-dd" placeholder="Konečné datum" style="height: 41px; width: 140px; margin-right: 10px; float: left;" value="<?= $end_formated ?>">

<button type="submit" style="padding: 10px 18px 10px 50px; height: 36px;" class="btn btn-blue btn-icon icon-left">
								Načíst
								<i class="fa fa-download" style="     padding: 10px 12px;"></i>
									</button>
						    </div>

						</form>


<div class="clear"></div>
</div><!-- Footer -->

	<?php

$searchquery = $mysqli->query("SELECT count(*) as celkem,
						sum(case when status = 1 then 1 else 0 end) as nezpracovane,
						sum(case when status = 2 then 1 else 0 end) as zhotovene,
						sum(case when status = 3 then 1 else 0 end) as reseni,
						sum(case when status = 4 then 1 else 0 end) as realizace,
						sum(case when status = 5 then 1 else 0 end) as hotove,
						sum(case when status = 6 then 1 else 0 end) as stornovane,
						sum(case when status = 7 then 1 else 0 end) as odlozene,
						sum(case when showroom = 2 then 1 else 0 end) as praha,
						sum(case when showroom = 3 then 1 else 0 end) as brno,
						sum(case when showroom = 5 then 1 else 0 end) as plzen,
						sum(case when showroom = 6 then 1 else 0 end) as budejovice, 
						sum(case when showroom = 7 then 1 else 0 end) as hradec 
FROM demands WHERE $query") or die($mysqli->error);

$search = mysqli_fetch_array($searchquery);
?>

<div class="row">
	<div class="col-md-12">

		<div class="panel panel-primary">
				<div class="panel-body">
		<div class="col-md-12" style="text-align:center;">
			<h3 style="margin-top: 8.5px;">
                <?= $startdate ?> - <?= $enddate ?> |
                <span class="btn btn-lg btn-white">Praha: <?= $search['praha'] ?></span>
                <span class="btn btn-lg btn-white">Brno: <?= $search['brno'] ?></span>
                <span class="btn btn-lg btn-white">České Budějovice: <?= $search['budejovice'] ?></span>
                <span class="btn btn-lg btn-white">Hradec Králové: <?= $search['hradec'] ?></span>
            </h3>

		</div>
		</div>

		</div>

	</div>
</div>



<div class="member-entry">



<div style="width: 12%; float: left;"><h3 style="margin-top: 8.5px;"><span style=" font-size: 17px; color: #737881;">Celkem:</span> <?= $search['celkem'] ?></h3></div>

<div style="width: 14%; float: left;"><h3 style="margin-top: 8.5px;"><span style=" font-size: 17px; color: #737881;">Nezpracované:</span> <?= $search['nezpracovane'] ?></h3></div>

<div style="width: 11%; float: left;"><h3 style="margin-top: 8.5px;"><span style=" font-size: 17px; color: #737881;">V řešení:</span> <?= $search['reseni'] ?></h3></div>

<div style="width: 17%; float: left;"><h3 style="margin-top: 8.5px;"><span style=" font-size: 17px; color: #737881;">Zhotovené nabídky:</span> <?= $search['zhotovene'] ?></h3></div>

<div style="width: 12%; float: left;"><h3 style="margin-top: 8.5px;"><span style=" font-size: 17px; color: #737881;">Realizace:</span> <?= $search['realizace'] ?></h3></div>

<div style="width: 11%; float: left;"><h3 style="margin-top: 8.5px;"><span style=" font-size: 17px; color: #737881;">Hotové:</span> <?= $search['hotove'] ?></h3></div>

<div style="width: 12%; float: left;"><h3 style="margin-top: 8.5px;"><span style=" font-size: 17px; color: #737881;">Stornované:</span> <?= $search['stornovane'] ?></h3></div>

<div style="width: 11%; float: left;"><h3 style="margin-top: 8.5px;"><span style=" font-size: 17px; color: #737881;">Odložené:</span> <?= $search['odlozene'] ?></h3></div>


</div>

<?php

if (isset($customer) && $customer != "") {
    ?>

<div class="member-entry">

	<?php

    $searchquery = $mysqli->query("SELECT count(*) as celkem, product, sum(case when status = 5 then 1 when status = 4 then 1 else 0 end) as hotove FROM demands WHERE $query GROUP BY product ORDER BY CASE WHEN product like 'custom' THEN 0 ELSE 1 END, product") or die($mysqli->error);

    while ($search = mysqli_fetch_array($searchquery)) {
        ?>

						<div style="<?php if (isset($search['product']) && $search['product'] == 'hottub' || $search['product'] == 'sauna') {echo 'float: right; margin-right: 40px';} else {echo 'float:left; margin-right: 120px';}?> "><h3 style="margin-top: 8.5px;"><span style=" font-size: 17px; color: #737881;"><?php if (isset($search['product']) && $search['product'] == 'hottub') {echo 'Neznámá vířivka';} elseif (isset($search['product']) && $search['product'] == 'sauna') {echo 'Neznámá sauna';} elseif (isset($search['product']) && $search['product'] == 'custom') {echo 'Sauna na míru';} else {echo ucfirst($search['product']);}?>:</span> <?= $search['celkem'] ?> <small style="color: #4da74d; font-weight: bold; font-size: 15px;">(<?= $search['hotove'] ?>)</small></h3></div>


						<?php
    }

    ?>

</div>

<?php
}

?>


<div class="member-entry">
<?php

$chart_query = $mysqli->query("SELECT count(*) as celkem, sum(case when status = 5 then 1 else 0 end) as hotove, sum(case when status = 4 then 1 else 0 end) as realizace, sum(case when status = 6 then 1 else 0 end) as stornovane, sum(case when status = 7 then 1 else 0 end) as odlozene, month(date) as month, year(date) as year FROM demands WHERE $query AND date != '0000-00-00' GROUP BY year(date), month(date) ORDER BY year(date) desc");

?>
<div id="chart12" style="height: 300px"></div>
	<script>


	$(document).ready(function()
	{


	// Area Chart
			Morris.Area({
				element: 'chart12',
				data: [

				<?php

while ($chart = mysqli_fetch_array($chart_query)) {
    ?>

{ g: '<?= $chart['year'] . "-" . $chart['month'] ?>', a: <?= $chart['celkem'] ?>, b: <?= $chart['hotove'] ?>, c: <?= $chart['realizace'] ?>, d: <?= $chart['stornovane'] ?>, e: <?= $chart['odlozene'] ?>},
<?php
}

?>
				],
				xkey: 'g',
				ykeys: ['a','b','c','d','e'],
				labels: ['Celkem','Hotové','Realizace','Stornovane','Odložené'],
			    hideHover: 'auto'
			});
});

	</script>

</div>


<div class="member-entry">
<?php

$chart_query = $mysqli->query("SELECT sum(case when status = 5 then 1 else 0 end) as hotove, sum(case when status = 4 then 1 else 0 end) as realizace, month(date) as month, year(date) as year FROM demands WHERE $query AND date != '0000-00-00' GROUP BY year(date), month(date) ORDER BY year(date) desc") or die($mysqli->error);

?>
<div id="chart11" style="height: 300px"></div>
	<script>


	$(document).ready(function()
	{


	// Area Chart
			Morris.Area({
				element: 'chart11',
				data: [

				<?php

while ($chart = mysqli_fetch_array($chart_query)) {
    ?>

{ g: '<?= $chart['year'] . "-" . $chart['month'] ?>', a: <?= $chart['hotove'] ?>, b: <?= $chart['realizace'] ?>},
<?php

}

?>
				],
				xkey: 'g',
				ykeys: ['a','b'],
				labels: ['Hotové','Realizace'],
			    hideHover: 'auto'
			});
});

	</script>

</div>


    <?php include VIEW . '/default/footer.php'; ?>



