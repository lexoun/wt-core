<?php
include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/functions.php";

$pagetitle = "Mailing";
$spesl = " - Klienti";

$bread1 = "Mailing";

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "send_mails") {

    $errors = array_filter($_POST['myselect']);
    if (!empty($errors)) {

        $is_mail = "false";
        $category = $errors;
        $nummero = 0;
        $text = $mysqli->real_escape_string($_POST['text']);

        foreach ($category as $categories => $value) {

            //$selectquery = $mysqli->query("SELECT user_name, email, secretstring, customer FROM demands WHERE id = '1'");

            $select = "";

            $selectquery = $mysqli->query("SELECT user_name, email, secretstring, customer, product FROM demands WHERE id = '$value'");

            if (mysqli_num_rows($selectquery) == 1) {

                $select = mysqli_fetch_array($selectquery);




                $product = returnpn($select['customer'], $select['product']);

                $password = mb_substr($select['secretstring'], 0, 5);

                $search = array("{name}", "{surname}", "{email}", "{secretstring}", "{product}", "{password}");
                $replace = array($select['name'], $select['surname'], $select['email'], $select['secretstring'], $product, $password);

                $text_replaced = str_replace($search, $replace, $_POST['text']);

                /* $insert = $mysqli->query("INSERT INTO mailing_history (target_id, customer, title, text, datetime, admin_id) VALUES ('$value', '".$_POST['customer']."','".$_POST['title']."','$text',current_timestamp(),'".$client['id']."')"); */

                if (isset($_POST['notificationdate']) && $_POST['notificationdate'] == "choose") {

                    $notificationdate = $_POST['notificationchooseddate'];

                } elseif (isset($_POST['notificationdate']) && $_POST['notificationdate'] == "none") {

                    $notificationdate = "0000-00-00";

                } else {

                    $notificationdate = Date('Y-m-d', strtotime("+" . $_POST['notificationdate'] . " days"));

                }

                //$insert2 = $mysqli->query("UPDATE demands SET notification = '$notificationdate' WHERE id = '$value'");

                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                //$mail->SMTPDebug = 3;                               // Enable verbose debug output
                $mail->CharSet = 'UTF-8';
                $mail->isSMTP();

                if (isset($select['customer']) && $select['customer'] == '0') {

                    $mail->Host = 'mail.webglobe.cz'; // Specify main and backup SMTP servers
                    $mail->SMTPAuth = true; // Enable SMTP authentication
                    $mail->Username = 'eshop@saunahouse.cz'; // SMTP username
                    $mail->Password = '9HE4fL3n'; // SMTP password
                    $mail->SMTPSecure = 'ssl'; // Enable TLS encryption, `ssl` also accepted
                    $mail->Port = 465; // TCP port to connect to

                    $mail->From = 'eshop@saunahouse.cz';
                    $mail->FromName = 'Saunahouse.cz';

                    $mail->DKIM_domain = 'saunahouse.cz';
                    $mail->DKIM_private = 'https://www.saunahouse.cz/wp-content/keys/saunahouse-private.key';
                    $mail->DKIM_selector = 'phpmailer';
                    $mail->DKIM_passphrase = '';
                    $mail->DKIM_identity = 'eshop@saunahouse.cz';

                } elseif (isset($select['customer']) && $select['customer'] == '1') {

                    $mail->Host = 'mail.webglobe.cz'; // Specify main and backup SMTP servers
                    $mail->SMTPAuth = true; // Enable SMTP authentication
                    $mail->Username = 'eshop@spahouse.cz'; // SMTP username
                    $mail->Password = '9HE4fL3n'; // SMTP password
                    $mail->SMTPSecure = 'ssl'; // Enable TLS encryption, `ssl` also accepted
                    $mail->Port = 465; // TCP port to connect to

                    $mail->From = 'eshop@spahouse.cz';
                    $mail->FromName = 'Spahouse.cz';

                    $mail->DKIM_domain = 'spahouse.cz';
                    $mail->DKIM_private = 'https://www.spahouse.cz/wp-content/newkeys/spahouse-private.key';
                    $mail->DKIM_selector = 'phpmailer';
                    $mail->DKIM_passphrase = '';
                    $mail->DKIM_identity = 'eshop@spahouse.cz';

                }

                $mail->addAddress($select['email'], $select['user_name']); // Add a recipient

                $mail->isHTML(true); // Set email   format to HTML

                $mail->Subject = $_POST['title'];
                $mail->Body = $text_replaced;

                if (!$mail->send()) {
                    echo 'Message could not be sent.';
                    echo 'Mailer Error: ' . $mail->ErrorInfo;
                }

                $nummero = $nummero + 1;

                $is_mail = "true";

            }

        }
    }

    header('location: https://www.wellnesstrade.cz/admin/pages/mailing/create?success=event_add&mail=' . $is_mail . '&count=' . $nummero);
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


$('#select_all').click(function() {
    $('#myselect option').prop('selected', true);

	$('.ms-selectable .ms-elem-selectable').hide();
	$('.ms-selection .ms-elem-selection').show();

});


$('#remove_all').click(function() {
    $('#myselect option').prop('selected', false);

	$('.ms-selection .ms-elem-selection').hide();
	$('.ms-selectable .ms-elem-selectable').show();

});


});

</script>

<form id="myformus" role="form" method="post" name="myform" class="form-horizontal form-groups-bordered validate" enctype='multipart/form-data' action="create?action=filtration">
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
                            <label class="col-sm-3 control-label">Stav poptávky</label>

                            <div class="col-sm-5">

                                <select class="form-control" name="status">
                                        <option value="1" <?php if (isset($_POST['status']) && $_POST['status'] == 1) {
                                            echo 'selected';
                                        } ?>>Nezpracovaná poptávka</option>
                                        <option value="2" <?php if (isset($_POST['status']) && $_POST['status'] == 2) {
                                            echo 'selected';
                                        } ?>>Zhotovená nabídka</option>
                                        <option value="3" <?php if (isset($_POST['status']) && $_POST['status'] == 3) {
                                            echo 'selected';
                                        } ?>>V řešení</option>
                                        <option value="12" <?php if (isset($_POST['status']) && $_POST['status'] == 12) {
                                            echo 'selected';
                                        } ?>>Prodaná</option>
                                        <option value="14" <?php if (isset($_POST['status']) && $_POST['status'] == 14) {
                                            echo 'selected';
                                        } ?>>Neobjednaná vířivka</option>
                                        <option value="4" <?php if (isset($_POST['status']) && $_POST['status'] == 4) {
                                            echo 'selected';
                                        } ?>>Realizace</option>
                                        <option value="8" <?php if (isset($_POST['status']) && $_POST['status'] == 8) {
                                            echo 'selected';
                                        } ?>>Nedokončená</option>
                                        <option value="5" <?php if (isset($_POST['status']) && $_POST['status'] == 5) {
                                            echo 'selected';
                                        } ?>>Hotová</option>
                                        <option value="6" <?php if (isset($_POST['status']) && $_POST['status'] == 6) {
                                            echo 'selected';
                                        } ?>>Stornovaná</option>
                                        <option value="7" <?php if (isset($_POST['status']) && $_POST['status'] == 7) {
                                            echo 'selected';
                                        } ?>>Odložená</option>
                                </select>

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

<form id="myformus" role="form" method="post" name="myform" class="form-horizontal form-groups-bordered validate" enctype='multipart/form-data' action="create?action=send_mails">
<input type="hidden" name="length" value="14">
	<div class="row">

		<div class="col-md-12">

					<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Výběr poptávek
					</div>

				</div>


						<div class="panel-body">


									<div class="form-group">
						<label class="col-sm-3 control-label"></label>

						<div class="col-sm-8">
							<select id="myselect" multiple="multiple" name="myselect[]" class="form-control multi-select" style="width: 800px;">
								<?php

    if (isset($_POST['optionsRadios']) && $_POST['optionsRadios'] == 1) {

        $product = $_POST['virivkatype'];

    } elseif (isset($_POST['optionsRadios']) && $_POST['optionsRadios'] == 0) {

        $product = $_POST['saunatype'];

    } elseif (isset($_POST['optionsRadios']) && $_POST['optionsRadios'] == 3) {

    }

    if ($product == "all") {

        $clients_query = $mysqli->query("SELECT id, user_name FROM demands WHERE customer = '" . $_POST['optionsRadios'] . "' AND newsletter = 0 AND status = '".$_POST['status']."' AND id > 23 AND email != ''") or die("1Neexistuje");

    } else {

        $clients_query = $mysqli->query("SELECT id, user_name FROM demands WHERE customer = '" . $_POST['optionsRadios'] . "' AND product = '$product' AND newsletter = 0 AND status = '".$_POST['status']."' AND id > 23 AND email != ''") or die("1Neexistuje");

    }
    $i = 0;
    while ($client = mysqli_fetch_array($clients_query)) {

        $approve = 1;
        $i++;

        if (isset($approve) && $approve == 1) { ?>

									<option value="<?= $client['id'] ?>"><?= $client['user_name'] ?></option>

								<?php
        }

    }
    ?>

							</select>
							<span id="select_all">Vybrat všechny</span>
							<span id="remove_all">Odstranit všechny</span>

							Celkem: <?= $i ?>
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
					<label for="field-1" class="col-sm-2 control-label">HTML mailu</label>

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


