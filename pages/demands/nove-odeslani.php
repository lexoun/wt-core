<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$pagetitle = "Nové odeslání";
$spesl = " - Poptávky";

$bread1 = "Poptávky";
$bread2 = "Maily";

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "send_mails") {

    $errors = array_filter($_POST['myselect']);
    if (!empty($errors)) {

        $is_mail = "false";
        $category = $errors;
        $nummero = 0;
        $text = $mysqli->real_escape_string($_POST['text']);

        foreach ($category as $categories => $value) {

            $selectquery = $mysqli->query("SELECT user_name, email FROM demands WHERE id = '$value'");
            $select = mysqli_fetch_array($selectquery);

            $insert = $mysqli->query("INSERT INTO demands_mails_history (demand_id, customer, title, text, datetime, admin_id) VALUES ('$value', '" . $_POST['customer'] . "','" . $_POST['title'] . "','$text',current_timestamp(),'" . $client['id'] . "')");

            if (isset($_POST['notificationdate']) && $_POST['notificationdate'] == "choose") {

                $notificationdate = $_POST['notificationchooseddate'];

            } elseif (isset($_POST['notificationdate']) && $_POST['notificationdate'] == "none") {

                $notificationdate = "0000-00-00";

            } else {

                $notificationdate = Date('Y-m-d', strtotime("+" . $_POST['notificationdate'] . " days"));

            }

            $insert2 = $mysqli->query("UPDATE demands SET notification = '$notificationdate' WHERE id = '$value'");

            $mail = new PHPMailer\PHPMailer\PHPMailer(true);

            //$mail->SMTPDebug = 3;                               // Enable verbose debug output
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host = 'mail.webglobe.cz'; // Specify main and backup SMTP servers
            $mail->SMTPAuth = true; // Enable SMTP authentication
            $mail->Username = 'admin@wellnesstrade.cz'; // SMTP username
            $mail->Password = 'RD4ufcLv'; // SMTP password
            $mail->SMTPSecure = 'ssl'; // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 465; // TCP port to connect to

            $mail->From = 'admin@wellnesstrade.cz';
            $mail->FromName = 'WellnessTrade.cz';
            $mail->addAddress($select['email'], $select['user_name']); // Add a recipient

            $mail->isHTML(true); // Set email   format to HTML

            $mail->Subject = $_POST['title'];
            $mail->Body = $_POST['text'];

            if (!$mail->send()) {
                echo 'Message could not be sent.';
                echo 'Mailer Error: ' . $mail->ErrorInfo;
            }

            $nummero = $nummero + 1;

            $is_mail = "true";

        }
    }

    header('location: https://www.wellnesstrade.cz/admin/pages/demands/nove-odeslani?success=event_add&mail=' . $is_mail . '&count=' . $nummero);
    exit;
}

$saunyquery = $mysqli->query("SELECT * FROM warehouse_products WHERE customer = 0 ORDER BY code");

$virivkyquery = $mysqli->query("SELECT * FROM warehouse_products WHERE customer = 1 ORDER BY brand");

include VIEW . '/default/header.php';

?>


<script type="text/javascript">

jQuery(document).ready(function($)
{


$('.radio1').click(function() {
   if($("input:radio[class='saunaradio']").is(":checked")) {

 	$('.tajtl').hide( "slow");
	$('.virivkens').hide( "slow");
	$('.saunkens').show( "slow");
   }
     if($("input:radio[class='virivkaradio']").is(":checked")) {

	$('.tajtl').hide( "slow");
   	$('.saunkens').hide( "slow");
$('.virivkens').show( "slow");
   }

        if($("input:radio[class='bothradio']").is(":checked")) {

	$('.tajtl').show( "slow");
   	$('.saunkens').show( "slow");
	$('.virivkens').show( "slow");
   }


});


$('.radio2').click(function() {
   if($("input:radio[class='choosedateradio']").is(":checked")) {

	$('.customdate').show( "slow");
   }else{

   		$('.customdate').hide( "slow");

   }


});


});

</script>

<form id="myformus" role="form" method="post" name="myform" class="form-horizontal form-groups-bordered validate" enctype='multipart/form-data' action="nove-odeslani?action=filtration">
<input type="hidden" name="length" value="14">
	<div class="row">

		<div class="col-md-12">

					<div class="panel panel-primary" data-collapsed="0" style="background: #F5F5F5; border-color: #e3e3e3;">

				<div class="panel-heading">
					<div class="panel-title">
						Filtrace
					</div>

				</div>

						<div class="panel-body">

													<div class="form-group" style="margin-top: 15px;">
						<label class="col-sm-3 control-label" style="padding-top: 8px;">Stav poptávky</label>

						<div class="col-sm-5">

							<select name="stav_poptavky[]" class="select2" placeholder="Všechny stavy" multiple>

								<option value="1" <?php if (isset($_POST['stav_poptavky'])) {if (in_array("1", $_POST['stav_poptavky'])) {echo 'selected';}}?>>Nezpracované</option>
								<option value="2" <?php if (isset($_POST['stav_poptavky'])) {if (in_array("2", $_POST['stav_poptavky'])) {echo 'selected';}}?>>Zhotovené nabídky</option>
								<option value="3" <?php if (isset($_POST['stav_poptavky'])) {if (in_array("3", $_POST['stav_poptavky'])) {echo 'selected';}}?>>V řešení</option>
								<option value="4" <?php if (isset($_POST['stav_poptavky'])) {if (in_array("4", $_POST['stav_poptavky'])) {echo 'selected';}}?>>Realizace</option>
							</select>

						</div>
					</div>

				<div class="form-group">
						<label class="col-sm-3 control-label">Druh</label>
						<div class="col-sm-5">
							<div class="radio radio1" style="width: 100px; float: left;">
								<label>
									<input type="radio" name="optionsRadios" value="1" class="virivkaradio" <?php if (isset($_POST['optionsRadios']) && $_POST['optionsRadios'] == "1" || !isset($_POST['optionsRadios'])) {echo 'checked';}?>>Vířivka
								</label>
							</div>
							<div class="radio radio1" style="width: 100px;float: left;">
								<label>
									<input type="radio" name="optionsRadios" value="0" class="saunaradio" <?php if (isset($_POST['optionsRadios']) && $_POST['optionsRadios'] == "0") {echo 'checked';}?>>Sauna
								</label>
							</div>
							<div class="radio radio1" style="width: 200px;float: left;">
								<label>
									<input type="radio" name="optionsRadios" value="3" class="bothradio" <?php if (isset($_POST['optionsRadios']) && $_POST['optionsRadios'] == "3") {echo 'checked';}?>>Vířivka + Sauna
								</label>
							</div>

						</div>
					</div>

					<div class="virivkens" <?php if (isset($_POST['optionsRadios']) && $_POST['optionsRadios'] == "0") { ?>style="display: none;"<?php } ?>>
					<div class="form-group">
						<label class="col-sm-3 control-label">Vířivky</label>

						<div class="col-sm-5">

							<select class="form-control" name="virivkatype">
								<option value="all">Všechny vířivky</option>
								<?php while ($virivka = mysqli_fetch_array($virivkyquery)) { ?>
								<option value="<?= $virivka['connect_name'] ?>" <?php if (isset($_POST['virivkatype']) && $_POST['virivkatype'] == $virivka['connect_name']) {echo 'selected';}?>><?= ucfirst($virivka['fullname']) ?></option><?php } ?>
							</select>

						</div>
					</div>

					<div class="form-group">
											<label class="col-sm-3 control-label">Microsilk</label>
											<div class="col-sm-5">
												<div class="radio" style="width: 100px; float: left;">
													<label>
														<input type="radio" name="microsilk" value="Ano" <?php if (isset($_POST['microsilk']) && $_POST['microsilk'] == "Ano") {echo 'checked';}?>>Ano
													</label>
												</div>
												<div class="radio" style="width: 100px;float: left;">
													<label>
														<input type="radio" name="microsilk" value="Ne" <?php if (isset($_POST['microsilk']) && $_POST['microsilk'] == "Ne") {echo 'checked';}?>>Ne
													</label>
												</div>
												<div class="radio" style="width: 100px;float: left;">
													<label>
														<input type="radio" name="microsilk" value="neresit" <?php if (isset($_POST['microsilk']) && $_POST['microsilk'] == "neresit" || !isset($_POST['microsilk'])) {echo 'checked';}?>>Neřešit
													</label>
												</div>

											</div>
					</div>

					<div class="form-group">
											<label class="col-sm-3 control-label">WiPod</label>
											<div class="col-sm-5">
												<div class="radio" style="width: 100px; float: left;">
													<label>
														<input type="radio" name="wipod" value="Ano" <?php if (isset($_POST['wipod']) && $_POST['wipod'] == "Ano") {echo 'checked';}?>>Ano
													</label>
												</div>
												<div class="radio" style="width: 100px;float: left;">
													<label>
														<input type="radio" name="wipod" value="Ne" <?php if (isset($_POST['wipod']) && $_POST['wipod'] == "Ne") {echo 'checked';}?>>Ne
													</label>
												</div>
												<div class="radio" style="width: 100px;float: left;">
													<label>
														<input type="radio" name="wipod" value="neresit" <?php if (isset($_POST['wipod']) && $_POST['wipod'] == "neresit" || !isset($_POST['wipod'])) {echo 'checked';}?>>Neřešit
													</label>
												</div>

											</div>
										</div>






			<?php

$cliquery = $mysqli->query('SELECT * FROM demands_mails_templates WHERE customer = 1 AND type = "zakladni"') or die($mysqli->error);
$cliquery2 = $mysqli->query('SELECT * FROM demands_mails_templates WHERE customer = 1 AND type = "microsilk"') or die($mysqli->error);
$cliquery3 = $mysqli->query('SELECT * FROM demands_mails_templates WHERE customer = 1 AND type = "wipod"') or die($mysqli->error);

if (isset($_POST['username'])) {

    $selecteduserquery = $mysqli->query('SELECT * FROM demands_mails_templates WHERE id="' . $_POST['username'] . '"') or die($mysqli->error);
    $suser = mysqli_fetch_assoc($selecteduserquery);

}
?>
			<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label" style="    padding-top: 12px;">Šablona</label>

						<div class="col-sm-5">
							<div class="input-group" style="width: 100%; float: left;">

							<select id="choosepoptavka" name="username" class="select2" data-allow-clear="true" data-placeholder="Vyberte šablonu..." style="width: 100%; float:left;" >
				               <option></option>
				               <optgroup label="Základní">
				                 <?php while ($dem = mysqli_fetch_array($cliquery)) { ?>

				                 <option value="<?= $dem['id'] ?>" <?php if (isset($_POST['username']) && $_POST['username'] == $dem['id']) {echo 'selected';}?>><?= $dem['title'] ?></option>

				                 <?php } ?>
				             </optgroup>

				             <optgroup label="Microsilk">
				                 <?php while ($dem = mysqli_fetch_array($cliquery2)) { ?>

				                 <option value="<?= $dem['id'] ?>" <?php if (isset($_POST['username']) && $_POST['username'] == $dem['id']) {echo 'selected';}?>><?= $dem['title'] ?></option>

				                 <?php } ?>
				             </optgroup>

				             <optgroup label="WiPod">

				                 <?php while ($dem = mysqli_fetch_array($cliquery3)) { ?>

				                 <option value="<?= $dem['id'] ?>" <?php if (isset($_POST['username']) && $_POST['username'] == $dem['id']) {echo 'selected';}?>><?= $dem['title'] ?></option>

				                 <?php } ?>
				             </optgroup>

				            </select>




							</div>
						</div>
					</div>



					</div>


							<div class="saunkens" <?php if (isset($_POST['optionsRadios']) && $_POST['optionsRadios'] == "1" || !isset($_POST['optionsRadios'])) { ?>style="display: none;"<?php } ?>>
					<div class="form-group">
						<label class="col-sm-3 control-label">Sauny</label>

						<div class="col-sm-5">

							<select class="form-control" name="saunatype">
									<option value="all">Všechny sauny</option>

								<?php while ($sauna = mysqli_fetch_array($saunyquery)) { ?>
								<option value="<?= $sauna['connect_name'] ?>"><?= $sauna['code'] ?> - <?= $sauna['brand'] . ' ' . ucfirst($sauna['fullname']) ?></option><?php } ?>
							</select>

						</div>
					</div>



							<?php $cliquery = $mysqli->query('SELECT * FROM demands_mails_templates WHERE customer = 0') or die($mysqli->error);

if (isset($_POST['username'])) {

    $selecteduserquery = $mysqli->query('SELECT * FROM demands_mails_templates WHERE id="' . $_POST['username'] . '"') or die($mysqli->error);
    $suser = mysqli_fetch_assoc($selecteduserquery);

}
?>
			<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label" style="    padding-top: 13px;">Šablona</label>

						<div class="col-sm-5">
							<div class="input-group" style="width: 100%; float: left;">

							<select id="choosepoptavka" name="usernamesauna" class="select2" data-allow-clear="true" data-placeholder="Vyberte šablonu..." style="width: 100%; float:left;" >
				                <option <?php if (isset($_POST['username'])) {echo 'value="' . $suser['id'] . '"';}?>><?php if (isset($_POST['username'])) {echo $suser['title'];}?></option>
				                 <?php while ($dem = mysqli_fetch_array($cliquery)) {if (isset($_POST['username']) && $_POST['username'] != $dem['id']) { ?><option value="<?= $dem['id'] ?>"><?= $dem['title'] ?></option><?php }}?>
				            </select>


							</div>
						</div>
					</div>


					</div>


			<center>
	<div class="form-group default-padding" style="margin-top: 30px;">
		<button type="submit" class="btn btn-lg btn-blue btn-icon icon-left" style="margin-left: -11%;">
Vyfiltrovat poptávky<i class="entypo-search"></i> </button>
	</div></center>


		</div>




			</div></div>	</div>
</form>


<?php if (isset($_REQUEST['action']) && $_REQUEST['action'] == "filtration") {
    ?>

<form id="myformus" role="form" method="post" name="myform" class="form-horizontal form-groups-bordered validate" enctype='multipart/form-data' action="nove-odeslani?action=send_mails">
<input type="hidden" name="length" value="14">
	<div class="row">

		<div class="col-md-12">

					<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Výběr poptávek
					</div>

				</div>


<?php

    $conds = $_POST['stav_poptavky'];
    $query = "";
    if ($conds != "") {
        foreach ($conds as $cond) {

            if ($query == "") {

                $query = "status = " . $cond;

            } else {

                $query = $query . " OR status = " . $cond;

            }

        }
    } else {
        $query = "status < 5";
    }

    ?>
						<div class="panel-body">


									<div class="form-group">
						<label class="col-sm-3 control-label"></label>

						<div class="col-sm-8">
							<select multiple="multiple" name="myselect[]" class="form-control multi-select" style="width: 800px;">
								<?php

    if (isset($_POST['optionsRadios']) && $_POST['optionsRadios'] == 1) {

        $product = $_POST['virivkatype'];

    } elseif (isset($_POST['optionsRadios']) && $_POST['optionsRadios'] == 0) {

        $product = $_POST['saunatype'];

    } elseif (isset($_POST['optionsRadios']) && $_POST['optionsRadios'] == 3) {

    }

    if ($product == "all") {

        $demandsquery = $mysqli->query("SELECT id, user_name FROM demands WHERE customer = '" . $_POST['optionsRadios'] . "' AND ($query)") or die($mysqli->error);

    } else {

        $demandsquery = $mysqli->query("SELECT id, user_name FROM demands WHERE customer = '" . $_POST['optionsRadios'] . "' AND product = '$product' AND ($query)") or die("1Neexistuje");

    }

    while ($demand = mysqli_fetch_array($demandsquery)) {

        $approve = 0;

        if (isset($_POST['optionsRadios']) && $_POST['optionsRadios'] == 1) {

            if ($_POST['microsilk'] != "neresit" && $_POST['wipod'] != "neresit") {

                $specsquery = $mysqli->query("SELECT * FROM demands_specs_bridge WHERE client_id = '" . $demand['id'] . "' AND ((specs_id = 3 AND value = '" . $_POST['microsilk'] . "') OR (specs_id = 4 AND value = '" . $_POST['wipod'] . "'))") or die("1Neexistuje");

                if (mysqli_num_rows($specsquery) == 2) {
                    $approve = 1;
                }

            } elseif ($_POST['microsilk'] != "neresit" && $_POST['wipod'] == "neresit") {

                $specsquery = $mysqli->query("SELECT * FROM demands_specs_bridge WHERE client_id = '" . $demand['id'] . "' AND specs_id = 3 AND value = '" . $_POST['microsilk'] . "'") or die("1Neexistuje");

                if (mysqli_num_rows($specsquery) == 1) {
                    $approve = 1;
                }

            } elseif (isset($_POST['microsilk']) && $_POST['microsilk'] == "neresit" && $_POST['wipod'] != "neresit") {

                $specsquery = $mysqli->query("SELECT * FROM demands_specs_bridge WHERE client_id = '" . $demand['id'] . "' AND specs_id = 4 AND value = '" . $_POST['wipod'] . "'") or die("1Neexistuje");

                if (mysqli_num_rows($specsquery) == 1) {
                    $approve = 1;
                }

            } else {

                $approve = 1;
            }

        } else {

            $approve = 1;
        }

        if (isset($approve) && $approve == 1) { ?>

									<option value="<?= $demand['id'] ?>"><?= $demand['user_name'] ?></option>

								<?php
        }

    }
    ?>

							</select>
						</div>
					</div>



		</div>








			</div></div>	</div>



<?php
    if (isset($_POST['username']) && $_POST['username'] != "") {

        $templatequery = $mysqli->query("SELECT * FROM demands_mails_templates WHERE id = '" . $_POST['username'] . "'");
        $template = mysqli_fetch_assoc($templatequery);

        ?>

				<div class="row">

		<div class="col-md-12">

					<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Úprava mailu
					</div>

				</div>

						<div class="panel-body">

					<div class="form-group">
						<label for="field-1" class="col-sm-2 control-label">Předmět</label>

						<div class="col-sm-5">
							<input type="text" class="form-control input-lg" name="title" id="field-1" placeholder="Předmět emailu" value="<?= $template['title'] ?>">
						</div>
					</div>


					<div class="form-group">
						<label for="field-ta" class="col-sm-2 control-label">Šablona</label>

						<div class="col-sm-8">
						<div class="well well-lg" style="background-color: #FFFFFF;">
						<?= $template['text'] ?>
						</div>
						</div>
					</div>
					<textarea name="text" style="display: none;"><?= $template['text'] ?></textarea>
					<input type="text" name="customer" value="<?= $template['customer'] ?>" style="display: none;">


					<div class="form-group">
						<label class="col-sm-2 control-label">Notifikace</label>
						<div class="col-sm-7" style="width: 470px;">
							<div class="radio radio2" style="width: 100px; float: left;">
								<label>
									<input type="radio" name="notificationdate" value="none" checked>Žádná
								</label>
							</div>
							<div class="radio radio2" style="width: 100px; float: left;">
								<label>
									<input type="radio" name="notificationdate" value="3">Za 3 dny
								</label>
							</div>
							<div class="radio radio2" style="width: 100px;float: left;">
								<label>
									<input type="radio" name="notificationdate" value="7">Za týden
								</label>
							</div>
							<div class="radio radio2" style="width: 100px;float: left;">
								<label>
									<input type="radio" name="notificationdate" value="14">Za 2 týdny
								</label>
							</div>
							<div class="radio radio2" style="width: 100px;float: left;">
								<label>
									<input type="radio" name="notificationdate" value="choose" class="choosedateradio">Vyberu datum
								</label>
								</div>
								<input class="customdate form-control datepicker" type="text" name="notificationchooseddate" data-format="yyyy-mm-dd" placeholder="Datum příští notifikace" style="display: none; width: 148px; margin-top: 14px;float: right">


						</div>
					</div>





		</div>








			</div></div>	</div>

			<?php } else { ?>

			<div class="row">

		<div class="col-md-12">

					<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Obsah mailu
					</div>

				</div>

						<div class="panel-body">

					<div class="form-group">
						<label for="field-1" class="col-sm-2 control-label">Předmět</label>

						<div class="col-sm-5">
							<input type="text" class="form-control input-lg" name="title" id="field-1" placeholder="Předmět emailu" value="">
						</div>
					</div>




										<div class="form-group">
						<label class="col-sm-2 control-label">Přednatavený mail</label>
						<div class="col-sm-7" >
							<script type="text/javascript">

						jQuery(document).ready(function($)
						{

							$('.mailusradiozaklad').click(function() {


							   	$('.allmailus').hide( "slow");
								$('.changusnamus').attr('name', 'totus');

								$('.mailuszaklad').show( "slow");
								$('.nameruszaklad').attr('name', 'text');

							});

						});

					</script>
						<div class="radio" style="width: 100px; float: left;">
								<label>
									<input type="radio" name="choosedmailus" value="zadny" class="mailusradiozaklad" checked>Žádný
								</label>
						</div>

					<?php $prednastavenemaily = $mysqli->query("SELECT * FROM demands_mails_templates WHERE customer = 9");
        while ($mail = mysqli_fetch_array($prednastavenemaily)) { ?>
						<div class="radio" style="width: 200px; float: left;">
								<label>
									<input type="radio" name="choosedmailus" class="mailusradio<?= $mail['id'] ?>" value="<?= $mail['id'] ?>"><?= $mail['title'] ?>
								</label>
						</div>
							<?php } ?>



						</div>
					</div>

				<div class="form-group">
					<label for="field-1" class="col-sm-2 control-label">Text mailu</label>

					<div class="col-sm-9 allmailus mailuszaklad">
				    <textarea class="form-control changusnamus nameruszaklad" data-stylesheet-url="<?= $home ?>/admin/assets/css/wysihtml5-color.css" name="text" id="sample_wysiwyg" style="height: 360px;"></textarea>
					</div>
					<?php $prednastavenemaily = $mysqli->query("SELECT * FROM demands_mails_templates WHERE customer = 9");
        while ($mail = mysqli_fetch_array($prednastavenemaily)) {
            ?>
					<script type="text/javascript">

						jQuery(document).ready(function($)
						{

							$('.mailusradio<?= $mail['id'] ?>').click(function() {


							   	$('.allmailus').hide( "slow");

							   	$('.changusnamus').attr('name', 'totus');

								$('.mailus<?= $mail['id'] ?>').show( "slow");
								$('.namerus<?= $mail['id'] ?>').attr('name', 'text');


							});

						});

					</script>
					<div class="col-sm-9 allmailus mailus<?= $mail['id'] ?>" style="display: none;">
					<textarea class="form-control changusnamus namerus<?= $mail['id'] ?>" data-stylesheet-url="<?= $home ?>/admin/assets/css/wysihtml5-color.css" name="totus" id="sample_wysiwyg<?= $mail['id'] ?>" style="height: 360px;"><?= $mail['text'] ?></textarea>
						</div>
					<?php } ?>

				</div>
					<input type="text" name="customer" value="9" style="display: none;">

					<div class="form-group">
						<label class="col-sm-2 control-label">Notifikace</label>
						<div class="col-sm-7" style="width: 470px;">
							<div class="radio radio2" style="width: 100px; float: left;">
								<label>
									<input type="radio" name="notificationdate" value="3" checked>Za 3 dny
								</label>
							</div>
							<div class="radio radio2" style="width: 100px;float: left;">
								<label>
									<input type="radio" name="notificationdate" value="7">Za týden
								</label>
							</div>
							<div class="radio radio2" style="width: 100px;float: left;">
								<label>
									<input type="radio" name="notificationdate" value="14">Za 2 týdny
								</label>
							</div>
							<div class="radio radio2" style="width: 100px;float: left;">
								<label>
									<input type="radio" name="notificationdate" value="choose" class="choosedateradio">Vyberu datum
								</label>
								</div>
								<input class="customdate form-control datepicker" type="text" name="notificationchooseddate" data-format="yyyy-mm-dd" placeholder="Datum příští notifikace" style="display: none; width: 148px; margin-top: 14px;float: right">


						</div>
					</div>



		</div>



			</div></div>	</div>

			<?php } ?>



						<center>
	<div class="form-group default-padding">
		<button type="submit" class="btn btn-lg btn-primary btn-icon icon-left" style="margin-left: -8%;">
Odeslat maily<i class="entypo-mail"></i> </button>
	</div></center>
</form>
<?php } ?>






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
