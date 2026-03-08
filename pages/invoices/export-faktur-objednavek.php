<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

if(isset($_REQUEST['od'])){ $od = $_REQUEST['od'];}

$pagetitle = "Export faktur objednávek";

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'imported') {

    $mysqli->query("UPDATE orders_invoices_exports SET imported = '" . $_REQUEST['value'] . "' WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

    header('Location: https://www.wellnesstrade.cz/admin/pages/invoices/export-faktur-objednavek?success=imported');

}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'email') {

    $mysqli->query("UPDATE administration_settings SET value = '" . $_POST['generation_email'] . "' WHERE param = 'order_invoices_export'") or die($mysqli->error);

    header('Location: https://www.wellnesstrade.cz/admin/pages/invoices/export-faktur-objednavek?success=email');
}

include VIEW . '/default/header.php';
?>

<div class="row">
	<div class="col-md-8 col-sm-7">
		<h2>XML balíčky faktur</h2>
	</div>

    <?php
    $data_query = $mysqli->query("SELECT * FROM administration_settings WHERE param = 'order_invoices_export'")or die($mysqli->error);
    $data = mysqli_fetch_assoc($data_query);
    ?>
	<div class="col-md-4 col-sm-5">
        <form method="post" role="form" action="export-faktur-objednavek?action=email">
			<div class="form-group">
               <label class="col-sm-4 control-label">E-mail pro export objednávek</label>
			<div style="margin-bottom: 12px; width: 260px; float:left; margin-left: 10px;margin-right: 4px;">
                <input type="text" name="generation_email" class="form-control typeahead" value="<?= $data['value'] ?>" />
            </div>
				<button style="width: 50px; float:left;" type="submit" class="btn btn-default"><i style=" position: relative; right: 0; top: 0;" class="entypo-pencil"></i></button>
			</div>
		</form>
	</div>
</div>

	<?php

    $ordersmaxquery = $mysqli->query('SELECT COUNT(*) AS NumberOfOrders FROM orders_invoices_exports') or die($mysqli->error);
    $ordersmax = mysqli_fetch_array($ordersmaxquery);
    $max = $ordersmax['NumberOfOrders'];
    if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {$od = 1;}

    $perpage = 30;
    $s_lol = $od - 1;
    $s_pocet = $s_lol * $perpage;
    $pocet_prispevku = $max;

    $invoices_query = $mysqli->query('SELECT *, DATE_FORMAT(date, "%Y-%m") as dateformated FROM orders_invoices_exports order by id desc limit ' . $s_pocet . ',' . $perpage) or die($mysqli->error);
?>


<?php
if (mysqli_num_rows($invoices_query) > 0) { ?>

<table class="table table-bordered table-striped datatable dataTable">
	<thead>
		<tr>
			<th width="200px" class="text-center">Číslo balíčku</th>
			<th width="140px" class="text-center">Počet faktur</th>
			<th width="415px" class="text-center">Akce</th>
		</tr>
	</thead>

	<tbody role="alert" aria-live="polite" aria-relevant="all">
<?php
    while ($invoice = mysqli_fetch_array($invoices_query)) { ?>


	<tr class="even" style="height: 46px; <?php if (isset($invoice['imported']) && $invoice['imported'] == 'ano' || $invoice['invoice_number'] == 0) {echo 'opacity: 0.4;';}?>">
	<td class="text-center" <?php if (isset($invoice['imported']) && $invoice['imported'] == 'ano' || $invoice['invoice_number'] == 0) {echo 'style="background-color: #ebebeb;"';}?>><a href="/admin/data/orders_invoices/invoices_<?= $invoice['dateformated'] ?>.xml" target="_blank"><?= $invoice['dateformated'] ?></a></td>

	<td class="text-center" <?php if (isset($invoice['imported']) && $invoice['imported'] == 'ano' || $invoice['invoice_number'] == 0) {echo 'style="background-color: #ebebeb; "';}?>><a href="#">

		<?php if ($invoice['invoice_number'] > 0) { ?><?= $invoice['invoice_number'] ?><?php } else { ?>

			žádná faktura

			<?php } ?></a></td>


	<td style="text-align: center; <?php if (isset($invoice['imported']) && $invoice['imported'] == 'ano' || $invoice['invoice_number'] == 0) {echo 'background-color: #ebebeb; ';}?>">

		<?php if ($invoice['invoice_number'] > 0) { ?>
				<a class="btn btn-default btn-icon icon-left hidden-print"
                   href="../../data/export/invoices_orders/invoices_<?= $invoice['dateformated'] ?>.xml" target="_blank">
					Zobrazit
					<i class="entypo-search"></i>
				</a>


				<a class="btn btn-primary btn-icon icon-left hidden-print"
                   download href="../../data/export/invoices_orders/invoices_<?= $invoice['dateformated'] ?>.xml">
					Stáhnout
					<i class="entypo-download"></i>
				</a>

				<?php if (isset($invoice['imported']) && $invoice['imported'] == 'ne') { ?>
				<a class="btn btn-blue btn-icon icon-left hidden-print"
                   href="./export-faktur-objednavek?id=<?= $invoice['id'] ?>&value=ano&action=imported">
					Označit za nahrané
					<i class="entypo-check"></i>
				</a>
				<?php } else { ?>

				<a class="btn btn-green btn-icon icon-left hidden-print"
                   href="./export-faktur-objednavek?id=<?= $invoice['id'] ?>&value=ne&action=imported">
					Již nahrané
					<i class="entypo-check"></i>
				</a>

				<?php } ?>

			<?php } else { ?>

			žádná faktura

			<?php } ?>




		</td>

</tr>

<?php
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

<div class="row">
	<div class="col-md-12">
		<center><ul class="pagination pagination-sm">
			<?php $currentpage = "export-faktur-objednavek";
include VIEW . "/default/pagination.php";?>
		</ul></center>
	</div>
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