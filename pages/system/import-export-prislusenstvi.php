<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$id = $_REQUEST['id'];

$pagetitle = "Import/Export příslušenství";

include VIEW . '/default/header.php';

?>



<div class="row">


	<div class="col-sm-12">

	<div class="col-md-12 col-sm-12" style="margin-bottom: 20px;">
		<h2>Produkty</h2>
	</div>

	<div class="col-sm-3">
		<a href="/admin/controllers/export/products?secretcode=lYspnYd2mYTJm6" style="cursor: pointer;" target="_blank">
			<div class="tile-stats tile-gray">
				<div class="icon"><i class=""></i></div>
				<div class="num"></div>
				<h3>Znovu vytvořit XML feed produktů</h3>

				<p>nově vyexportuje všechno zboží pro všechny eshopy</p>
			</div>
		</a>
	</div>
	<div style="clear: both;"></div>
        
	<hr>

	<div class="col-sm-3">
		<a href="https://www.saunahouse.cz/wp-cron.php?import_key=VZjsoH_-&import_id=15&action=trigger" style="cursor: pointer;" target="_blank">
			<div class="tile-stats tile-gray">
				<div class="icon"><i class=""></i></div>
				<div class="num"></div>
				<h3>Spustit import do Saunahouse.cz</h3>

				<p>do 5 se začnou importovat, celé může trvat až 30 minut</p>
			</div>
		</a>
	</div>

	<div class="col-sm-3">
		<a href="https://www.spamall.cz/wp-cron.php?import_key=AEei3VZb30&import_id=2&action=trigger" style="cursor: pointer;" target="_blank">
			<div class="tile-stats tile-gray">
				<div class="icon"><i class=""></i></div>
				<div class="num"></div>
				<h3>Spustit import do Spahouse.cz</h3>

				<p>do 5 se začnou importovat, celé může trvat až 30 minut</p>
			</div>
		</a>
	</div>


		</div>

	</div>

<hr>

<div class="row" style="margin-top: 40px;">


	<div class="col-sm-12">

	<div class="col-md-12 col-sm-12" style="margin-bottom: 20px;">
		<h2>WellnessTrade.cz</h2>
	</div>

	<div class="col-sm-3">
		<a href="https://www.saunahouse.cz/wp-cron.php?export_key=Zh3b6Iwc4Bil&export_id=1&action=trigger" style="cursor: pointer;" target="_blank">
		<div class="tile-stats tile-gray">
			<div class="icon"><i class=""></i></div>
			<div class="num"></div>
			<h3>Export XML feedu Saunahouse.cz</h3>

			<p>vytvoří XML feed objednávek z Saunahouse.cz</p>
		</div>
	</a>
	</div>

		<div class="col-sm-3">
			<a href="/admin/controllers/import/orders?secretcode=lYspnYd2mYTJm6" style="cursor: pointer;" target="_blank">
		<div class="tile-stats tile-gray">
			<div class="icon"><i class=""></i></div>
			<div class="num"></div>
			<h3>Export XML feedu Spamall.cz</h3>

			<p>vytvoří XML feed objednávek z Spamall.cz</p>
		</div>
	</a>
	</div>
	<div style="clear: both;"></div>
	<hr>
	<div class="col-sm-3">
		<a href="/admin/controllers/import/orders?secretcode=lYspnYd2mYTJm6" style="cursor: pointer;" target="_blank">
		<div class="tile-stats tile-gray">
			<div class="icon"><i class=""></i></div>
			<div class="num"></div>
			<h3>Importovat všechny objednávky</h3>

			<p>nahraje do systému dostupné XML feedy objednávek</p>
		</div>
	</a>
	</div>




		</div>

	</div>








</div>
</div>




<?php include VIEW . '/default/footer.php'; ?>

