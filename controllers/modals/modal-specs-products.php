<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";

$order_query = $mysqli->query("SELECT id, order_status, order_tracking_number FROM orders WHERE id = '" . $_REQUEST['id'] . "'");

$order = mysqli_fetch_array($order_query);

?>


<script type="text/javascript">
jQuery(document).ready(function($)
{

 $('.rad1').on('switch-change', function () {

 if($('#nah').prop('checked')){

 	$('#enable_custom_hidden').show("slow");

   }else if(!$('#nah').prop('checked')){


 	$('#enable_custom_hidden').hide("slow");
 	$('#enable_custom').prop('checked', false);

 	$('.rad2').bootstrapSwitch('setState', false);

 	$('#custom_text').hide("slow");

 }

});



 $('.rad2').on('switch-change', function () {

 if($('#enable_custom').prop('checked')){

 	$('#custom_text').show("slow");

   }else if(!$('#enable_custom').prop('checked')){


 	$('#custom_text').hide("slow");
 }

});


});
</script>
	<div class="modal-dialog">

		<form role="form" method="post" action="zobrazit-objednavku?action=change_status&id=<?= $order['id'] ?>&link=<?= $order['order_status'] ?>" enctype="multipart/form-data">
		<div class="modal-content">
			<div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

				<h4 class="modal-title">Změna stavu objednávky #<?= $order['id'] ?></h4> </div>

			<div class="modal-body">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Nový stav objednávky
					</div>

				</div>

						<div class="panel-body">


				<div class="form-group">
						<label class="col-sm-2 control-label"></label>
						<div class="col-sm-8">
							<select name="status" class="selectboxit">
								<option value="0" <?php if (isset($order['order_status']) && $order['order_status'] == 0) {echo 'selected';}?>>Nezpracovaná</option>
								<option value="1" <?php if (isset($order['order_status']) && $order['order_status'] == 1) {echo 'selected';}?>>V řešení</option>
								<option value="2" <?php if (isset($order['order_status']) && $order['order_status'] == 2) {echo 'selected';}?>>Připravená</option>
								<option value="3" <?php if (isset($order['order_status']) && $order['order_status'] == 3) {echo 'selected';}?>>Vyexpedovaná</option>
								<option value="4" <?php if (isset($order['order_status']) && $order['order_status'] == 4) {echo 'selected';}?>>Stornovaná</option>
							</select>
						</div>
					</div>
				</div>
				</div>

				<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Nastavení mailového upozornění
					</div>

				</div>

						<div class="panel-body form-horizontal">


				<div class="form-group">
						<label class="col-sm-6 control-label" for="nah" style="padding-top: 7px;">Informovat zákazníka o změně stavu</label>
						<div class="col-sm-6">
							<div class="radiodegreeswitch rad1 make-switch switch-small" style="float: left; margin-right:11px; margin-top: 2px;" data-on-label="<i class='entypo-mail'></i>" data-off-label="<i class='entypo-cancel'></i>">
										<input class="radiodegree" name="send_mail" id="nah" value="yes" type="checkbox"/>
									</div>

						</div>
					</div>

				<div class="form-group" id="enable_custom_hidden" style="display: none;">
						<label class="col-sm-6 control-label" for="enable_custom" style="padding-top: 7px;">Vlastní úvodní text emailu</label>
						<div class="col-sm-6">
							<div class="radiodegreeswitch rad2 make-switch switch-small" style="float: left; margin-right:11px; margin-top: 2px;" data-on-label="<i class='entypo-pencil'></i>" data-off-label="<i class='entypo-cancel'></i>">
										<input class="radiodegree" name="enable_custom" id="enable_custom" value="yes" type="checkbox"/>
									</div>

						</div>
					</div>

					<div class="form-group" id="custom_text" style="display: none;">
						<label class="col-sm-3 control-label" for="ee" style="padding-top: 7px;">Úvodní text emailu</label>
						<div class="col-sm-9">

							<textarea name="custom_text" class="form-control autogrow" id="field-7" style="height: 140px;"></textarea>

						</div>
					</div>

				</div>
				</div>

				<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Číslo pro sledování zásilky
					</div>

				</div>

						<div class="panel-body">


				<div class="form-group">
						<label class="col-sm-2 control-label" for="nah"></label>
						<div class="col-sm-8">
								<input type="text" style="height: 40px;" name="order_tracking_number" class="form-control" id="field-1" placeholder="Sledovací číslo" value="<?= $order['order_tracking_number'] ?>">
						</div>
					</div>
				</div>
				</div>

			</div>
<div class="modal-footer" style="text-align:left;"> <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>

	<a href="#" style="float:right;"><button type="submit" class="btn btn-blue btn-icon icon-left">Změnit stav
					<i class="entypo-bookmarks"></i></button></a>
	</form>
	</div>


	<link rel="stylesheet" href="https://www.wellnesstrade.cz/admin/assets/js/selectboxit/jquery.selectBoxIt.css">

	<!-- Bottom Scripts -->

	<script src="https://www.wellnesstrade.cz/admin/assets/js/bootstrap-switch.min.js" id="script-resource-8"></script>

	<script src="https://www.wellnesstrade.cz/admin/assets/js/selectboxit/jquery.selectBoxIt.min.js"></script>

	<script src="https://www.wellnesstrade.cz/admin/assets/js/neon-custom.js"></script>
