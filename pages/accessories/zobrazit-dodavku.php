<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$id = $_REQUEST['id'];

$supply_query = $mysqli->query('SELECT *, DATE_FORMAT(date, "%d. %M %Y") as recieved_date, DATE_FORMAT(ordered_date, "%d. %M %Y") as ordered_date FROM products_supply WHERE id = "' . $id . '"') or die($mysqli->error);

if (mysqli_num_rows($supply_query) > 0) {

    $supply = mysqli_fetch_array($supply_query);

    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'change_status') {

        $update = $mysqli->query("UPDATE products_supply SET status = '" . $_POST['status'] . "' WHERE id = '" . $id . "'");

        Header('Location:https://www.wellnesstrade.cz/admin/pages/accessories/zobrazit-dodavku?id=' . $id . '&success=change_status');
        exit;
    }

/*
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'change_status'){

$update = $mysqli->query("UPDATE products_supply SET status = '".$_POST['status']."' WHERE id = '".$id."'");

Header('Location:https://www.wellnesstrade.cz/admin/pages/accessories/zobrazit-dodavku?id='.$id.'&success=change_status');
exit;
}

 */

    $accessories_suppliers = $mysqli->query("SELECT * FROM products_manufacturers WHERE type = 'supplier' AND id = '".$supply['supplier']."' ORDER BY manufacturer")or die($mysqli->error);
    $supplier = mysqli_fetch_assoc($accessories_suppliers);


    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'send_email') {

        $data_query = $mysqli->query('SELECT *, DATE_FORMAT(date, "%d. %M %Y") as recieved_date, DATE_FORMAT(ordered_date, "%d. %M %Y") as ordered_date FROM products_supply WHERE id = "' . $id . '"') or die($mysqli->error);
        $data = mysqli_fetch_array($data_query);



        $bridge_query = $mysqli->query("SELECT * FROM products_supply_bridge WHERE supply_id = '$id'");

        $i = 0;
        $all_products = '';

        while ($bridge = mysqli_fetch_array($bridge_query)) {
            $i++;

            $products_query = $mysqli->query("SELECT *, id as ajdee FROM products WHERE id = '" . $bridge['product_id'] . "'");
            $product = mysqli_fetch_array($products_query);

            if ($bridge['variation_id'] != 0) {

                $code = $variation_sku['sku'];

            } else {

                $code = $product['code'];

            }

            $variation_name = '';

            if ($bridge['variation_id'] != 0) {

                $variation_name .= '<span style="font-size: 12px; font-weight: 300;">';

                $variation_query = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $bridge['variation_id'] . "'");

                while ($variation = mysqli_fetch_array($variation_query)) {

                    $variation_name .= $variation['name'] . ': ' . $variation['value'];

                }

                $variation_name .= '</span>';

            }

            $product = '<tr>
				<td><strong>' . $code . ' - ' . $product['productname'] . '</strong>' . $variation_name . '</td>
				<td style="text-align: center;">' . $bridge['quantity'] . '</td>
			</tr>';

            $all_products .= $product;

        }

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
        $mail->addAddress($supplier['email']); // Add a recipient

        $mail->isHTML(true); // Set email   format to HTML

        $mail->Subject = 'Supply order - #' . $data['id'];
        $mail->Body = $_POST['mail_text'] . '<br><br>

	  <table>
		<thead>
			<tr>
				<th style="text-align: center;">Item</th>
				<th>Quantity</th>
			</tr>
		</thead>
		<tbody>
		' . $all_products . '
		</tbody>
		</table>

	  <br>
	  <br>' . $_POST['mail_ending'];

        if (!$mail->send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        }

        $update = $mysqli->query("UPDATE products_supply SET status = '1', ordered_date = CURRENT_TIMESTAMP(), mail_send = 1, mail_text = '" . $mail->Body . "' WHERE id = '" . $id . "'");

        Header('Location:https://www.wellnesstrade.cz/admin/pages/accessories/zobrazit-dodavku?id=' . $id . '&success=change_status');
        exit;
    }

    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'date_recieved') {

        $update = $mysqli->query("UPDATE products_supply SET status = '2', date = '" . $_POST['date'] . "' WHERE id = '" . $id . "'");

        Header('Location:https://www.wellnesstrade.cz/admin/pages/accessories/zobrazit-dodavku?id=' . $id . '&success=change_status');
        exit;
    }

    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'supply_recieved') {

        $supply_query = $mysqli->query('SELECT status FROM products_supply WHERE id = "' . $id . '"') or die($mysqli->error);

        $supply = mysqli_fetch_array($supply_query);

        if ($supply['status'] != '3') {

            $update = $mysqli->query("UPDATE products_supply SET status = '3' WHERE id = '" . $id . "'");

            $bridge_query = $mysqli->query("SELECT b.*, s.location_id FROM products_supply_bridge b, products_supply s WHERE b.supply_id = s.id AND b.supply_id = '$id'");

            while ($bridge = mysqli_fetch_array($bridge_query)) {

                $types_bridge_query = $mysqli->query("SELECT * FROM supply_types_bridge WHERE product_id = '" . $bridge['product_id'] . "' AND variation_id = '" . $bridge['variation_id'] . "' AND supply_id = '" . $bridge['supply_id'] . "'") or die($mysqli->error);

                while ($type = mysqli_fetch_array($types_bridge_query)) {

                    $delivered = $type['quantity'];

                    if ($type['type'] == 'order') {

                        $bridge = 'orders_products_bridge';
                        $id_identify = 'order_id';

                    } elseif ($type['type'] == 'service') {

                        $bridge = 'services_products_bridge';
                        $id_identify = 'aggregate_id';

                    }

                    $mysqli->query("UPDATE $bridge SET reserved = reserved + $delivered, delivered = delivered - $delivered WHERE  product_id = '" . $type['product_id'] . "' AND variation_id = '" . $type['variation_id'] . "' AND $id_identify = '" . $type['type_id'] . "'") or die($mysqli->error);

                }

                $quantity = $bridge['quantity'] - $bridge['reserved'];

                $mysqli->query("UPDATE products_stocks SET instock = instock + $quantity WHERE product_id = '" . $bridge['product_id'] . "' AND variation_id = '" . $bridge['variation_id'] . "' AND location_id = '" . $bridge['location_id'] . "'") or die($mysqli->error);

            }

        }

        Header('Location:https://www.wellnesstrade.cz/admin/pages/accessories/zobrazit-dodavku?id=' . $id . '&success=change_status');
        exit;
    }

    $pagetitle = 'Dodávka ' . $supply['id'];

    include VIEW . '/default/header.php';

    ?>

<script type="text/javascript">
jQuery(document).ready(function($)
{

$('.radio').click(function() {
   if($("input:radio[class='saunaradio']").is(":checked")) {


	$('.virivkens').hide( "slow");
	$('.saunkens').show( "slow");
   }
     if($("input:radio[class='virivkaradio']").is(":checked")) {


   	$('.saunkens').hide( "slow");
$('.virivkens').show( "slow");
   }
});
 var cloneCount = 0;
$('#duplicatevirivka').click(function() {
 cloneCount = cloneCount + 1;
      $('#virdup').clone().attr('id', 'virdup'+ cloneCount).insertAfter('[id^=virdup]:last');
      $('#virdup'+ cloneCount).find('#virivkadup').attr('name', 'zbozickovirivka'+ cloneCount);
      $('#virdup'+ cloneCount).find('#field-2').attr('name', 'cenickavirivka'+ cloneCount);

});

 var cloneCount2 = 0;
$('#duplicatesauna').click(function() {
 cloneCount2 = cloneCount2 + 1;
      $('#saundup').clone().attr('id', 'saundup'+ cloneCount2).insertAfter('[id^=saundup]:last');
      $('#saundup'+ cloneCount2).find('#saunadup').attr('name', 'zbozickosauna'+ cloneCount2);
      $('#saundup'+ cloneCount2).find('#field-2').attr('name', 'cenickasauna'+ cloneCount2);

});

});


</script>

	<div class="panel panel-primary" data-collapsed="0">



						<div class="panel-body">
<div class="invoice">

	<div class="row">

		<div class="col-sm-12" style="padding: 0;">

		<div class="col-sm-12 invoice-left">

			<h3 style="float: left; margin-right: 20px;"><span style="font-size: 18px;">DODÁVKA Č.</span> #<?= $supply['id'] ?></h3>

			<ol class="breadcrumb bc-2" style="margin-bottom: 0; float:left;">
					<li <?php if (isset($supply['status']) && $supply['status'] == 0) {echo 'class="active"';}?>> <?php if (isset($supply['status']) && $supply['status'] == 0) {echo '<strong>Neobjednaná</strong>';} else { ?>Neobjednaná<?php } ?></li>
					<li <?php if (isset($supply['status']) && $supply['status'] == 1) {echo 'class="active"';}?>> <?php if (isset($supply['status']) && $supply['status'] == 1) {echo '<strong>Objednaná</strong>';} else { ?>Objednaná<?php } ?></li>
					<li <?php if (isset($supply['status']) && $supply['status'] == 2) {echo 'class="active"';}?>> <?php if (isset($supply['status']) && $supply['status'] == 2) {echo '<strong>Na cestě</strong>';} else { ?>Na cestě<?php } ?></li>
					<li <?php if (isset($supply['status']) && $supply['status'] == 3) {echo 'class="active"';}?>> <?php if (isset($supply['status']) && $supply['status'] == 3) {echo '<strong>Přijatá</strong>';} else { ?>Přijatá<?php } ?></li>
				</ol>

		<br />
		<hr class="margin" style="margin-bottom: 20px;"/>

		</div>

	</div>

	</div>

	<div class="row">


		<div class="col-sm-6" style="padding: 0;">

		<div class="col-sm-12 invoice-left">


		<?php if ($supply['admin_note'] != "") {echo '<div class="alert alert-info"><strong>Informace prodejce:</strong> ' . $supply['admin_note'] . '</div>';}?>
			<h4>Informace o dodavateli</h4>
			<strong ><?= $supplier['manufacturer'] ?></strong>
			<br />
			<a href="mailto:<?= $supplier['email'] ?>"><?= $supplier['email'] ?></a>

		</div>

	</div>

		<div class="col-md-6 invoice-right">
			<h4>Informace o dodávce</h4>
			<p style="font-size: 14px; margin-bottom: 2px;">Datum objednání: <strong><?= $supply['ordered_date'] ?></strong></p>
			<p style="font-size: 14px; margin-bottom: 6px;">Datum doručení: <strong><?= $supply['recieved_date'] ?></strong></p>
		</div>

	</div>

	<div class="margin"></div>

	<table class="table table-bordered">
		<thead>
			<tr>
				<th class="text-center">#</th>
				<th width="45%">Položka</th>
				<th width="90px" class="text-center">Počet</th>
				<th width="90px" class="text-center">Rezerováno</th>
				<th width="90px" class="text-center">Nákupní cena</th>
			</tr>
		</thead>

		<tbody>
			<?php

    $bridge_query = $mysqli->query("SELECT * FROM products_supply_bridge WHERE supply_id = '$id'");

    $price_with_dph = 0;
    $i = 0;

    while ($bridge = mysqli_fetch_array($bridge_query)) {

        $i++;

        ?>


			<tr>
				<td class="text-center" style="vertical-align: middle;" width="100px"><?= $i ?></td>
				<td><?php get_product_list($bridge);?></td>
				<td class="text-center" style="vertical-align: middle;"><?= $bridge['quantity'] ?></td>
				<td class="text-center" style="vertical-align: middle;"><?= $bridge['reserved'] ?></td>
				<td class="text-center" style="vertical-align: middle;"><?= $bridge['purchase_price'] ?> Kč</td>
			</tr>
<?php

        $price_with_dph = $price_with_dph + ($bridge['purchase_price'] * $bridge['quantity']);
    }

    ?>

		</tbody>
	</table>

	<div class="margin"></div>

	<div class="row">


			<div class="clear"></div>
				<hr />
		<div class="col-sm-12">
			<div class="invoice-left col-sm-6" style="padding: 0;">


				<?php

    if ($supply['mail_send'] == 1) { ?>

			<h4 style="color: #00a651;">E-mail odeslán!</h4>
			<p style="font-size: 14px; margin-bottom: 2px;">Datum odeslání: <strong><?= $supply['ordered_date'] ?></strong></p>
			<p style="font-size: 14px; margin-bottom: 6px;">Text e-mailu: <br>
				<div style="background-color: #f0f0f1; padding: 10px; float: left;"><?= $supply['mail_text'] ?></div></p>

		<?php

    }

    ?>


			</div>


			<div class="invoice-right col-sm-6" style="padding: 0;">

			<a href="javascript:;" onclick="jQuery('#send_email').modal('show');" class="btn btn-orange btn-icon icon-left hidden-print" <?php if ($supply['status'] > 0) {echo 'disabled';}?>>
				Poslat e-mail [Objednaná]
				<i class="entypo-bookmarks"></i>
			</a>

			&nbsp;


			<a href="javascript:;" onclick="jQuery('#date_recieved').modal('show');" class="btn btn-info btn-icon icon-left hidden-print" <?php if ($supply['status'] > 1) {echo 'disabled';}?>>
				Nastavit datum doručení [Na cestě]
				<i class="entypo-bookmarks"></i>
			</a>

			&nbsp;

			<a href="javascript:;" onclick="jQuery('#supply_recieved').modal('show');" class="btn btn-success btn-icon icon-left hidden-print" <?php if ($supply['status'] > 2) {echo 'disabled';}?>>
				Naskladnit zboží [Přijatá]
				<i class="entypo-bookmarks"></i>
			</a>

			&nbsp;

			<a href="./upravit-dodavku?id=<?= $supply['id'] ?>" class="btn btn-default btn-icon icon-left hidden-print" <?php if ($supply['status'] > 2) {echo 'disabled';}?>>
				Upravit
				<i class="entypo-pencil"></i>
			</a>

			</div>

		</div>

	</div>

				<?php

    $orders_query = $mysqli->query("SELECT type_id, type FROM supply_types_bridge WHERE supply_id = '" . $supply['id'] . "' GROUP BY type") or die($mysqli->error);
    while ($order = mysqli_fetch_array($orders_query)) {

        ?>

<div class="col-sm-6">
	<div class="panel panel-primary" data-collapsed="0">

	<div class="panel-heading">
					<div class="panel-title">
						<strong style="font-weight: 600"><?php if ($order['type'] == 'order') {echo '<a href="/admin/pages/orders/zobrazit-objednavku?id=' . $order['type_id'] . '" target="_blank">Objednávka';}?> #<?= $order['type_id'] ?></a></strong>
					</div>

				</div>

			<div class="panel-body">




							<table class="table table-bordered">
		<thead>
			<tr>
				<th width="90%">Položka</th>
				<th width="90px" class="text-center">Rezerováno</th>
			</tr>
		</thead>

		<tbody>

								<?php

        $bridge_query = $mysqli->query("SELECT * FROM supply_types_bridge WHERE supply_id = '" . $supply['id'] . "' AND type_id = '" . $order['type_id'] . "' AND type = 'order'");
        while ($bridge = mysqli_fetch_array($bridge_query)) {

            ?>

				<tr>
					<td><small><?php get_product_list($bridge);?></small></td>
					<td class="text-center"><?= $bridge['quantity'] ?></td>
				</tr>


			<?php }?>

				</tbody>

			</table>





			</div>

	</div>

</div>

<?php }?>

</div></div>

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

<div class="modal fade" id="date_recieved" aria-hidden="true" style="display: none;margin-top: 3%;">

	<div class="modal-dialog" style="padding-top: 8%;">

		<form role="form" method="post" action="zobrazit-dodavku?action=date_recieved&id=<?= $id ?>" enctype="multipart/form-data">
		<div class="modal-content">
			<div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

				<h4 class="modal-title">Zadání data doručení #<?= $supply['id'] ?></h4> </div>

			<div class="modal-body">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Datum doručení
					</div>

				</div>

						<div class="panel-body">


				<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Datum doručení</label>
						<div class="col-sm-6">
							<div class="date">
              					<input type="text" class="form-control datepicker" name="date" data-format="yyyy-mm-dd" placeholder="Datum" value="<?php if ($supply['date'] == '0000-00-00') {echo date('Y-m-d');} else {echo $supply['date'];}?>">
          			  		</div>
						</div>
					</div>

				</div>
				</div>

			</div>
	<div class="modal-footer" style="text-align:left;"> <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>

	<a href="#" style="float:right;"><button type="submit" class="btn btn-blue btn-icon icon-left">Nastavit datum
					<i class="entypo-bookmarks"></i></button></a>
	</form>
	</div>
</div>

</div>
</div>








<div class="modal fade" id="send_email" aria-hidden="true" style="display: none;margin-top: 3%;">

	<div class="modal-dialog" style="padding-top: 8%;">

		<form role="form" method="post" action="zobrazit-dodavku?action=send_email&id=<?= $id ?>" enctype="multipart/form-data">
		<div class="modal-content">
			<div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

				<h4 class="modal-title">Objednání dodávky #<?= $supply['id'] ?></h4> </div>

			<div class="modal-body">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Úvodní text mailu
					</div>
				</div>

				<div class="panel-body">
					<div class="form-group">
							<div class="col-sm-12">
								<div class="date">
	              					<textarea name="mail_text" class="form-control autogrow" id="field-7" style="height: 100px;"></textarea>
	          			  		</div>
							</div>
					</div>
				</div>


				<div class="panel-heading">
					<div class="panel-title">
						Závěrečný text mailu
					</div>
				</div>

				<div class="panel-body">
					<div class="form-group">
							<div class="col-sm-12">
								<div class="date">
	              					<textarea name="mail_ending" class="form-control autogrow" id="field-7" style="height: 100px;"></textarea>
	          			  		</div>
							</div>
					</div>
				</div>



			</div>
			</div>
	<div class="modal-footer" style="text-align:left;"> <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>

	<a href="#" style="float:right;"><button type="submit" class="btn btn-blue btn-icon icon-left">Odeslat
					<i class="entypo-bookmarks"></i></button></a>
	</form>
	</div>
</div>

</div>
</div>





<div class="modal fade" id="supply_recieved" aria-hidden="true" style="display: none;margin-top: 3%;">

	<div class="modal-dialog" style="padding-top: 8%;">

		<form role="form" method="post" action="zobrazit-dodavku?action=supply_recieved&id=<?= $id ?>" enctype="multipart/form-data">
		<div class="modal-content">
			<div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

				<h4 class="modal-title">Naskladnění zboží z dodávky #<?= $supply['id'] ?></h4> </div>

			<div class="modal-body">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Opravdu chcete naskladnit zboží z dodávky?
					</div>

				</div>

						<div class="panel-body">




				</div>
				</div>

			</div>
	<div class="modal-footer" style="text-align:left;"> <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>

	<a href="#" style="float:right;"><button type="submit" class="btn btn-blue btn-icon icon-left">Změnit stav
					<i class="entypo-bookmarks"></i></button></a>
	</form>
	</div>
</div>



</div>
</div>



</div>
</div>


<?php include VIEW . '/default/footer.php'; ?>


<?php

} else {

    include INCLUDES . "/404.php";

}?>


