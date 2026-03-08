<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";

$service_query = $mysqli->query("SELECT id, state FROM services WHERE id = '" . $_REQUEST['id'] . "'");
$service = mysqli_fetch_array($service_query);

if(isset($_REQUEST['redirect_url'])){ $redirect_url = urlencode($_REQUEST['redirect_url']); }else{ $redirect_url = ''; }

?>


<!--<script type="text/javascript">-->
<!--jQuery(document).ready(function($)-->
<!--{-->
<!---->
<!-- $('.rad1').on('switch-change', function () {-->
<!---->
<!-- if($('#nah').prop('checked')){-->
<!---->
<!-- 	$('#enable_custom_hidden').show("slow");-->
<!---->
<!--   }else if(!$('#nah').prop('checked')){-->
<!---->
<!---->
<!-- 	$('#enable_custom_hidden').hide("slow");-->
<!-- 	$('#enable_custom').prop('checked', false);-->
<!---->
<!-- 	$('.rad2').bootstrapSwitch('setState', false);-->
<!---->
<!-- 	$('#custom_text').hide("slow");-->
<!---->
<!-- }-->
<!---->
<!--});-->
<!---->
<!---->
<!-- $('.rad2').on('switch-change', function () {-->
<!---->
<!-- if($('#enable_custom').prop('checked')){-->
<!---->
<!-- 	$('#custom_text').show("slow");-->
<!---->
<!--   }else if(!$('#enable_custom').prop('checked')){-->
<!---->
<!---->
<!-- 	$('#custom_text').hide("slow");-->
<!-- }-->
<!---->
<!--});-->
<!---->
<!---->
<!--});-->
<!--</script>-->
	<div class="modal-dialog">

		<form role="form" method="post" action="/admin/pages/services/zobrazit-servis?action=change_state&id=<?= $service['id'] ?>&redirect_url=<?= $redirect_url ?>&link=<?= $service['state'] ?>" enctype="multipart/form-data">
		<div class="modal-content">
			<div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

				<h4 class="modal-title">Změna stavu servisu #<?= $service['id'] ?></h4> </div>

			<div class="modal-body">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Nový stav servisu
					</div>

				</div>

						<div class="panel-body">


				<div class="form-group">
						<label class="col-sm-2 control-label"></label>
						<div class="col-sm-8">
							<select id="state" name="state" class="selectboxit">
								<option value="new" <?php if (isset($service['state']) && $service['state'] == 'new') {echo 'selected';}?>>Nový</option>
								<option value="waiting" <?php if (isset($service['state']) && $service['state'] == 'waiting') {echo 'selected';}?>>Čeká na díly</option>
								<option value="unconfirmed" <?php if (isset($service['state']) && $service['state'] == 'unconfirmed') {echo 'selected';}?>>Nepotvrzený</option>
								<option value="confirmed" <?php if (isset($service['state']) && $service['state'] == 'confirmed') {echo 'selected';}?>>Potvrzený</option>
								<option value="executed" <?php if (isset($service['state']) && $service['state'] == 'executed') {echo 'selected';}?>>Provedený</option>
                                <option value="unfinished" <?php if (isset($service['state']) && $service['state'] == 'unfinished') {echo 'selected';}?>>Nedokončený</option>
                                <option value="problematic" <?php if (isset($service['state']) && $service['state'] == 'problematic') {echo 'selected';}?>>Problémové</option>
                                <option value="warranty" <?php if (isset($service['state']) && $service['state'] == 'warranty') {echo 'selected';}?>>Reklamace</option>
								<option value="finished" <?php if (isset($service['state']) && $service['state'] == 'finished') {echo 'selected';}?>>Hotový</option>
								<option value="canceled" <?php if (isset($service['state']) && $service['state'] == 'canceled') {echo 'selected';}?>>Stornovaný</option>
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


			</div>
<div class="modal-footer" style="text-align:left;"> <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>

	<a href="#" style="float:right;"><button type="submit" class="btn btn-blue btn-icon icon-left">Změnit stav
					<i class="entypo-bookmarks"></i></button></a>
	</form>
	</div>


	<link rel="stylesheet" href="https://www.wellnesstrade.cz/admin/assets/js/selectboxit/jquery.selectBoxIt.css">
	<script src="https://www.wellnesstrade.cz/admin/assets/js/bootstrap-switch.min.js" id="script-resource-8"></script>
	<script src="https://www.wellnesstrade.cz/admin/assets/js/selectboxit/jquery.selectBoxIt.min.js"></script>
	<script src="https://www.wellnesstrade.cz/admin/assets/js/neon-custom.js"></script>
<!--<script src="--><?// echo $home; ?><!--/admin/assets/js/jquery-ui/js/jquery-ui-1.10.3.minimal.min.js"></script>-->