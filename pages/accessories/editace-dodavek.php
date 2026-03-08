<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$pagetitle = "Dodávky příslušenství";


include VIEW . '/default/header.php';


?>

<div class="row">
	<div class="col-md-8 col-sm-7">
		<h2><?= $pagetitle ?></h2>
	</div>

	<div class="col-md-4 col-sm-5">

		<a href="pridat-dodavku" style=" margin-right: 24px; float: right;" class="btn btn-default btn-icon icon-left btn-lg">
					<i class="entypo-plus"></i>
					Přidat dodávku
				</a>

	</div>
</div>
<br>

	<?php
$supply_max_query = $mysqli->query('SELECT COUNT(*) AS NumberOfOrders FROM products_supply') or die($mysqli->error);
$supply_max = mysqli_fetch_array($supply_max_query);
$max = $supply_max['NumberOfOrders'];
if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {$od = 1;}

$perpage = 20;
$s_lol = $od - 1;
$s_pocet = $s_lol * $perpage;
$pocet_prispevku = $max;
?>

	   <?php

$supply_query = $mysqli->query("SELECT *, DATE_FORMAT(date, '%d. %m. %Y') as recieved_date FROM products_supply ORDER BY id DESC limit " . $s_pocet . "," . $perpage) or die($mysqli->error);

?>


<?php
if (mysqli_num_rows($supply_query) > 0) { ?>

<table class="table table-bordered table-striped datatable dataTable">
	<thead> <tr> <th width="200px">Dodávka</th>
		<th style="width: 600px;">Objednáno</th> <th>Stav</th><th>Datum dodání</th><th>Pobočka</th> <th class="text-center">Cena</th> <th width="220px" class="text-center">Akce</th></tr> </thead>

	<tbody role="alert" aria-live="polite" aria-relevant="all">
<?php
    while ($supply = mysqli_fetch_array($supply_query)) {

        supply($supply, $client['secretstring'], 0);

    }?>

       </tbody>

  </table>

	 <?php } else { ?>


<ul class="cbp_tmtimeline" style="margin-left: 25px;  margin-top: 50px;">
  <li style="margin-top: 80px;">

		<div class="cbp_tmicon">
			<i class="entypo-block" style="line-height: 42px !important;"></i>
		</div>

		<div class="cbp_tmlabel empty" style="padding-top: 9px;">
			<span><a style="font-weight: bold; margin-left: -12px;font-size: 17px;">Bohužel tomuto filtru neodpovídá žádná objednávka.</a></span>
		</div>
	</li>
</ul>
<?php } ?>







<!-- Pager for search results --><div class="row">
	<div class="col-md-12">
		<center><ul class="pagination pagination-sm">
			<?php $currentpage = "nezpracovane-objednavky";
include VIEW . "/default/pagination.php";?>
		</ul>

		<h1 style="margin-bottom: 50px;">Celkem: <?= $max ?></h1>

	</center>
	</div></div>
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


<style>

.page-body .selectboxit-container .selectboxit-options { margin-top: 40px !important; width: 100% !important;}
.page-body .selectboxit-container .selectboxit { height: 40px;width: 100% !important;}
.page-body .selectboxit-container .selectboxit .selectboxit-text { line-height: 40px; }
.page-body .selectboxit-container .selectboxit .selectboxit-arrow-container { height: 40px;}
.page-body .selectboxit-container .selectboxit .selectboxit-arrow-container:after { line-height: 40px;}
</style>




<script type="text/javascript">
$(document).ready(function(){
    $(".toggle-modal-remove").click(function(e){

			$('#remove-modal').removeData('bs.modal');
    	 e.preventDefault();


    	 var type = $(this).data("type");

    	 var id = $(this).data("id");

        $("#remove-modal").modal({

            remote: '/admin/controllers/modals/modal-remove.php?id='+id+'&type='+type,
        });
    });
});
</script>


<div class="modal fade" id="remove-modal" aria-hidden="true" style="display: none; margin-top: 160px;">

</div>




<script type="text/javascript">
$(document).ready(function(){
    $(".toggle-modal-change-status").click(function(e){

			$('#change-status-modal').removeData('bs.modal');
    	 e.preventDefault();


    	 var id = $(this).data("id");

        $("#change-status-modal").modal({

            remote: '/admin/controllers/modals/modal-change-status-data.php?id='+id,
        });
    });
});
</script>


<div class="modal fade" id="change-status-modal" aria-hidden="true" style="display: none; margin-top: 3%;">

</div>



    <?php include VIEW . '/default/footer.php'; ?>



