<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";


// old to delete remove
//$get_discounts = $mysqli->query("SELECT b.order_id, b.product_id, b.variation_id, b.original_price, b.discount, i.id FROM orders_products_bridge b, orders_invoices i WHERE b.discount != 0 AND i.order_id = b.order_id")or die($mysqli->error);
//
//while($discount = mysqli_fetch_assoc($get_discounts)){
//
////    print_r($discount);
//
//    $mysqli->query("UPDATE orders_invoices_products_bridge SET original_price = '".$discount['original_price']."', discount = '".$discount['discount']."'
//
//    WHERE invoice_id = '".$discount['id']."'
//    AND product_id = '".$discount['product_id']."'
//    AND variation_id = '".$discount['variation_id']."'
//
//    ")or die($mysqli->error);
//
//}
//
//
//$get_discounts = $mysqli->query("SELECT b.service_id, b.product_id, b.variation_id, b.original_price, b.price, b.discount, i.id FROM services_products_bridge b, orders_invoices i WHERE i.order_id = b.service_id")or die($mysqli->error);
//
//while($discount = mysqli_fetch_assoc($get_discounts)){
//
////    print_r($discount);
//
//    $mysqli->query("UPDATE orders_invoices_products_bridge SET original_price = '".$discount['original_price']."', discount = '".$discount['discount']."', price = '".$discount['price']."'
//
//    WHERE invoice_id = '".$discount['id']."'
//    AND product_id = '".$discount['product_id']."'
//    AND variation_id = '".$discount['variation_id']."'
//
//    ")or die($mysqli->error);
//
//}
//
//
//exit;
//
//$get_discounts = $mysqli->query("SELECT b.order_id, b.product_id, b.variation_id, b.original_price, b.discount, i.id FROM orders_products_bridge b, orders_invoices i WHERE  i.order_id = b.order_id")or die($mysqli->error);
//
//while($discount = mysqli_fetch_assoc($get_discounts)){
//
////    print_r($discount);
//
//    $mysqli->query("UPDATE orders_invoices_products_bridge SET original_price = '".$discount['original_price']."'
//
//    WHERE invoice_id = '".$discount['id']."'
//    AND product_id = '".$discount['product_id']."'
//    AND variation_id = '".$discount['variation_id']."'
//
//    ")or die($mysqli->error);
//
//}
//
//
//exit;
// old to delete remove


if (isset($_REQUEST['customer'])) {$customer = $_REQUEST['customer'];}
if (isset($_REQUEST['category'])) {$category = $_REQUEST['category'];}
if (isset($_REQUEST['od'])) {$od = $_REQUEST['od'];}
if (isset($_REQUEST['q'])) {$search = $_REQUEST['q'];}

if (isset($search) && $search != "") {

    $pagetitle = 'Hledaný výraz "' . $search . '"';

    $bread1 = $currentPage['name'];
    $abread1 = $currentPage['seo_url'];

} else {

    $pagetitle = $currentPage['name'];

}


if (isset($_REQUEST['action']) && $_REQUEST['action'] == "connect_invoice") {

    $mysqli->query('UPDATE bank_transactions SET manual_assign = "' . $_POST['invoice_id'] . '" WHERE ident = "' . $_REQUEST['id'] . '"') or die($mysqli->error);

    header('location: https://www.wellnesstrade.cz/admin/pages/invoices/vystavene-faktury?show=payments');
    exit;
}



if (isset($_REQUEST['action']) && $_REQUEST['action'] == "connect_invoice_comgate") {

    $mysqli->query('UPDATE transactions_comgate SET target_id = "' . $_POST['target_id'] . '", target_type = "' . $_REQUEST['target_type'] . '" WHERE ident = "' . $_REQUEST['id'] . '"') or die($mysqli->error);

    header('location: https://www.wellnesstrade.cz/admin/pages/invoices/vystavene-faktury?show=payments');
    exit;
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "invoice_disconnect") {

    $mysqli->query('UPDATE bank_transactions SET manual_assign = "0" WHERE ident = "' . $_REQUEST['id'] . '"') or die($mysqli->error);

    header('location: https://www.wellnesstrade.cz/admin/pages/invoices/vystavene-faktury?show=payments');
    exit;
}

include VIEW . '/default/header.php';


$branches = array();
$locations_query = $mysqli->query("SELECT * FROM shops_locations WHERE type = 'branch'")or die($mysqli->error);
while($singleLoc = mysqli_fetch_assoc($locations_query)){

    $branches[$singleLoc['id']] = $singleLoc['name'];

}


if(empty($_REQUEST['show']) || ($_REQUEST['show'] != 'payments' && $_REQUEST['show'] != 'comgate')){

    if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {$od = 1;}
    $perpage = 60;
    $s_lol = $od - 1;
    $s_pocet = $s_lol * $perpage;

    if (isset($search) && $search != "") {

        $parts = explode(" ", $search);
        $last = array_pop($parts);
        $first = implode(" ", $parts);

        if ($first == "") {
            $first = 0;
        }
        if ($last == "") {
            $last = 0;
        }

        $pocet_prispevku = 0;

        // (i.id = '$search') OR (o.id = '$search') OR

        $invoices_query = $mysqli->query("SELECT * FROM (
    SELECT i.currency, i.status, i.total_price, o.paid, o.paid_value, i.id, i.type, i.order_id as target_id, DATE_FORMAT(i.date, '%d. %m. %Y') as dateformated, i.date, i.fik, s.shipping_name, s.shipping_surname, b.billing_name, b.billing_surname, b.billing_company, o.payment_method, i.invoice_id, o.location_id, o.transaction_id FROM orders_invoices i, orders o 
        LEFT JOIN addresses_billing b ON b.id = o.billing_id 
        LEFT JOIN addresses_shipping s ON s.id = o.shipping_id 
    WHERE 
          i.order_id = o.id AND 
          i.type = 'order' AND 
          ((i.id LIKE '%$search%') OR 
          (s.shipping_surname like '%$search%' OR s.shipping_surname like '%$search%') OR 
          (s.shipping_name like '%$search%') OR 
          (s.shipping_company like '%$search%') OR 
          (b.billing_company like '%$search%'))
    GROUP BY s.id 

    UNION 
    
    SELECT i.currency, i.status, i.total_price, o.paid, o.paid_value, i.id, i.type, i.order_id as target_id, DATE_FORMAT(i.date, '%d. %m. %Y') as dateformated, i.date, i.fik, s.shipping_name, s.shipping_surname, b.billing_name, b.billing_surname, b.billing_company, o.payment_method, i.invoice_id, o.location_id, o.transaction_id FROM orders_invoices i, services o LEFT JOIN addresses_billing b ON b.id = o.billing_id  
        LEFT JOIN addresses_shipping s ON s.id = o.shipping_id 
    WHERE i.order_id = o.id AND i.type = 'service'  AND
          ((i.id LIKE '%$search%') OR 
          (s.shipping_surname like '%$search%' OR s.shipping_surname like '%$search%') OR 
          (s.shipping_name like '%$search%') OR (s.shipping_company like '%$search%') OR 
          (b.billing_company like '%$search%'))
           GROUP BY b.id) 
    
    AS invoices order by invoices.id") or die($mysqli->error);

    } else {

        $ordersmaxquery = $mysqli->query('SELECT COUNT(*) AS NumberOfOrders FROM orders_invoices WHERE status = "active"') or die($mysqli->error);
        $ordersmax = mysqli_fetch_array($ordersmaxquery);
        $max = $ordersmax['NumberOfOrders'];

        $pocet_prispevku = $max;


        $getIds = $mysqli->query('SELECT MIN(id) AS min, MAX(id) AS max 
        FROM (SELECT id FROM orders_invoices ORDER BY id DESC LIMIT ' . $s_pocet . ', ' . $perpage .') as invoices')or die($mysqli->error);

        $ids = mysqli_fetch_assoc($getIds);


        $invoices_query = $mysqli->query("SELECT * FROM (

        SELECT i.currency, i.status, i.total_price, o.paid, o.paid_value, i.id, i.type, i.order_id as target_id, DATE_FORMAT(i.date, '%d. %m. %Y') as dateformated, i.date, i.fik, s.shipping_name, s.shipping_surname, b.billing_name, b.billing_surname, b.billing_company, o.payment_method, i.invoice_id, o.payment_date, o.location_id, o.transaction_id FROM orders_invoices i, orders o LEFT JOIN addresses_billing b ON b.id = o.billing_id LEFT JOIN addresses_shipping s ON s.id = o.shipping_id WHERE i.type = 'order' AND i.id >= '".$ids['min']."' AND i.id <= '".$ids['max']."' AND i.order_id = o.id

        UNION

        SELECT i.currency, i.status, i.total_price, o.paid, o.paid_value, i.id, i.type, i.order_id as target_id, DATE_FORMAT(i.date, '%d. %m. %Y') as dateformated, i.date, i.fik, s.shipping_name, s.shipping_surname, b.billing_name, b.billing_surname, b.billing_company, o.payment_method, i.invoice_id, o.payment_date, o.location_id, o.transaction_id FROM orders_invoices i, services o LEFT JOIN addresses_billing b ON b.id = o.billing_id  LEFT JOIN addresses_shipping s ON s.id = o.shipping_id WHERE i.type = 'service' AND i.id >= '".$ids['min']."' AND i.id <= '".$ids['max']."' AND i.order_id = o.id

        ) AS invoices order by invoices.id desc") or die($mysqli->error);



    }?>
<div class="row">
	<div class="col-md-3 col-sm-3">
		<h2><?php if(empty($search)) {

                echo $pagetitle;

            }else{ echo 'Hledanému výrazu <i><u>"'.$search.'"</u></i> odpovídájí tyto výsledky:'; } ?></h2>
	</div>
    <div class="col-md-2">
        <center><ul class="pagination pagination-sm">
                <?php
                include VIEW . "/default/pagination.php";?>
            </ul>

        </center>
    </div>
	<div class="col-md-7" style="text-align: right;  margin: 17px 0;">

		<form method="get" role="form" style="float: right;">

			<div class="form-group">
			<div style="margin-bottom: 12px; width: 260px; float:left; margin-left: 10px;margin-right: 4px;"><input id="cheart" value="<?php if(!empty($search)) { echo $search; } ?>" type="text" name="q" class="form-control" placeholder="Hledání..." /></div>

				<button style="width: 50px; float:left;" type="submit" class="btn btn-default"><i style=" position: relative; right: 0; top: 0;" class="entypo-search"></i></button>
			</div>

		</form>

        <a href="?show=payments" class="btn btn-md btn-primary" style="margin-right: 6px;">Zobrazit výpis plateb na účtu</a>
        <a href="?show=comgate" class="btn btn-md btn-info" style="margin-right: 6px;">Zobrazit Comgate platby</a>
        <a href="../../controllers/export/orders-invoices-print?secretcode=lYspnYd2mYTJm6" class="btn btn-md btn-orange" target="_blank" style="margin-right: 10px;">Tisknout min. měsíc</a>

    </div>
</div>



<?php
if (mysqli_num_rows($invoices_query) > 0) { ?>

<table class="table table-bordered table-striped datatable dataTable">
	<thead>
		<tr>
			<th width="" class="text-center">ID</th>
            <th width="" class="text-center">VS / Odběratel</th>
            <th width="" class="text-center">Druh</th>
			<th width="" class="text-center">Částka</th>
			<th width="" class="text-center">Celkem zaplaceno</th>
			<th width="" class="text-center">Přijaté platby</th>
			<th width="160px" class="text-center">Akce</th>
		</tr>
	</thead>

	<tbody role="alert" aria-live="polite" aria-relevant="all">
<?php
    while ($invoice = mysqli_fetch_array($invoices_query)) {

        $currency = currency($invoice['currency']);

        $interval_start = date('Y-m-d', strtotime($invoice['date'].' - 60 days'));
        $interval_end = date('Y-m-d', strtotime($invoice['date'].' + 60 days'));

        ?>


<tr class="even">
	<td class="text-center"><strong><a href="https://www.wellnesstrade.cz/admin/data/invoices/orders/<?= $invoice['id'] ?>.pdf?t=<?= $currentDate->getTimestamp() ?>" target="_blank"><?= $invoice['id'] ?></a></strong><br><small><?= $invoice['dateformated'] ?></small>
       </td>


    <td class="text-center">

        <?php if (isset($invoice['type']) && $invoice['type'] == 'order') { ?>

            <a href="../orders/zobrazit-objednavku?id=<?= $invoice['target_id'] ?>"><?php echo $invoice['target_id'];if ($invoice['fik'] != "") {echo ' <small>EET</small>';} ?></a>

        <?php } elseif (isset($invoice['type']) && $invoice['type'] == 'service') { ?>

            <a href="../services/zobrazit-servis?id=<?= $invoice['target_id'] ?>"><?php echo $invoice['target_id'];if ($invoice['fik'] != "") {echo ' <small>EET</small>';} ?></a>

        <?php } ?>
        <br><?= user_name($invoice) ?>

    </td>
  <td class="text-center">


    <?php if (isset($invoice['type']) && $invoice['type'] == 'order') { ?>

      <i class="entypo-bag"></i> objednávka

    <?php } elseif (isset($invoice['type']) && $invoice['type'] == 'service') { ?>

      <i class="entypo-tools"></i> servis

    <?php } ?>
      <br>
      <?php if (isset($invoice['status']) && $invoice['status'] == 'storno') { ?>

          <i class="entypo-cancel"></i>stornovaná faktura

      <?php } elseif (isset($invoice['status']) && $invoice['status'] == 'active') { ?>

          vystavená faktura

      <?php } elseif (isset($invoice['status']) && $invoice['status'] == 'odd') { ?>

          opravný doklad k <strong><a href="https://www.wellnesstrade.cz/admin/data/invoices/orders/<?= $invoice['invoice_id'] ?>.pdf?t=<?= $currentDate->getTimestamp() ?>" target="_blank"><?= $invoice['invoice_id'] ?></a></strong>

      <?php } ?>
  </td>



	<td class="text-center">

		<span><strong><?= number_format($invoice['total_price'], 2, ',', ' ') ?></strong> <?= $currency['sign'] ?><br><?php


        // todo globals payment
        $payment_query = $mysqli->query("SELECT name FROM shops_payment_methods WHERE link_name = '" . $invoice['payment_method'] . "'") or die($mysqli->error);
        $payment = mysqli_fetch_array($payment_query);

        // todo globals payment

        if (isset($invoice['payment_method']) && $invoice['payment_method'] == 'cash') {

            $selectedBranch = 'převzetí při realizaci';
            if(!empty($branches[$invoice['location_id']])){

                $selectedBranch = $branches[$invoice['location_id']];

            }

            $payment_method = 'hotově - <strong>'.$selectedBranch.'</strong>';

		} else {

            $payment_method = $payment['name'];

        }

		echo $payment_method;
		?></span>

<!--		--><?//
//
//        $total = 0;
//
//        $bridge_query = $mysqli->query("SELECT * FROM orders_products_bridge WHERE target_id = '" . $invoice['target_id'] . "'") or die($mysqli->error);
//
//        while ($bridge = mysqli_fetch_array($bridge_query)) {
//
//            $total = $total + ($bridge['price'] * $bridge['quantity']);
//
//        }
//
//        $delivery_query = $mysqli->query("SELECT delivery_price FROM orders WHERE id = '" . $invoice['target_id'] . "'") or die($mysqli->error);
//
//        $delivery = mysqli_fetch_array($delivery_query);
//
//        ?>
<!---->
<!--		<br><small>--><?//echo ($total + $delivery['delivery_price']); ?><!--</small>-->



	</td>

    <?php
    if (isset($invoice['status']) && $invoice['status'] != 'odd') {

    ?>

	<td class="text-center">
            <?php

            if($invoice['paid'] != 0){

                echo payment_status($invoice);

            }else{

                $payment = check_payment($invoice, 'order');

                echo '<span style=" font-size: 13px; '.$payment['color'].'">'.$payment['info'].'</span>';
            }

             ?>
    </td>

    <td class="text-center"><span><?php


            $bank_query = $mysqli->query("SELECT account, vs, ident, value, DATE_FORMAT(date, '%d. %m. %Y') as dateformated FROM bank_transactions WHERE (vs = '".$invoice['id']."' OR manual_assign = '".$invoice['id']."' OR vs = '".$invoice['target_id']."') 
                AND date BETWEEN '".$interval_start."' AND '".$interval_end."' ")or die($mysqli->error);


                $allPayments = '';
                if(mysqli_num_rows($bank_query) > 0){

                    while($bank = mysqli_fetch_assoc($bank_query)) {

                        $vs_id = '';
                        $show_marks = '';
                        if($bank['vs'] == $invoice['id'] && $invoice['target_id'] != $invoice['id']){

                            $vs_id = 'Zaplaceno pod číslem faktury!  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                            $show_marks = '<strong style="color: #03a9f4;">!!!</strong> ';

                        }

                        if($bank['account'] == 'demand'){

                            $vs_id = 'Zaplaceno na poptávkový účet!  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                            $show_marks = '<strong style="color: #ff9600;">!!!</strong> ';

                        }


                        $allPayments .= '<div class="well tooltip-primary" style="padding: 4px 4px; margin-bottom: 4px;" data-toggle="tooltip" data-placement="top" title="" data-original-title="'.$vs_id.'ID: '.$bank['ident'].'"> '.$show_marks.number_format($bank['value'], 2, ',', ' ').'  '.$currency['sign'].' - <strong style="color: #00a651">' . $bank['dateformated'] . '</strong></div>';

                    }

                } elseif(!empty($invoice['payment_date']) && $invoice['payment_date'] != '0000-00-00' && $invoice['payment_date'] != '0000-00-00 00:00:00') {

                    $allPayments .= '<strong style="color: #00a651">' . datetime_formatted($invoice['payment_date']) . '</strong>';

                } elseif(isset($invoice['payment_method']) && $invoice['payment_method'] == 'cash') {

                    $allPayments .= '-';

                } else {

                    $allPayments .= '<span style="color: #d42020;">žádná přijatá platba</span>';

                }


                echo $allPayments;




            ?></span></td>

    <?php }else{ ?>

        <td class="text-center">-</td>
        <td class="text-center">-</td>


    <?php } ?>


	<td style="text-align: center;">

				<a href="https://www.wellnesstrade.cz/admin/data/invoices/orders/<?= $invoice['id'] ?>.pdf?t=<?= $currentDate->getTimestamp() ?>" target="_blank" class="btn btn-default   hidden-print">
					<i class="entypo-search"></i>
				</a>

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







<!-- Pager for search results --><div class="row">
	<div class="col-md-12">
		<center><ul class="pagination pagination-sm">
			<?php

include VIEW . "/default/pagination.php";?>
		</ul></center>
	</div></div>


<?php }elseif($_REQUEST['show'] == 'payments'){



    if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {$od = 1;}

    $perpage = 70;
    $s_lol = $od - 1;
    $s_pocet = $s_lol * $perpage;
    $ordersmaxquery = $mysqli->query("SELECT COUNT(*) AS NumberOfOrders FROM bank_transactions WHERE account = 'order'") or die($mysqli->error);

    $ordersmax = mysqli_fetch_array($ordersmaxquery);
    $max = $ordersmax['NumberOfOrders'];

    $pocet_prispevku = $max;

    $bank_query = $mysqli->query("SELECT t.*, DATE_FORMAT(t.date, '%d. %m. %Y') as dateformated FROM bank_transactions t WHERE t.account = 'order' order by t.date desc limit " . $s_pocet . "," . $perpage)or die($mysqli->error);
    ?>

    <div class="row" style="margin-bottom: 20px">
        <div class="col-md-4 col-sm-4">
            <h2>Historie přijatých plateb <small>(objednávky)</small></h2>
        </div>

        <div class="col-md-4">
            <center>
                <ul class="pagination pagination-sm">
                    <?php
                    include VIEW . "/default/pagination.php";?>
                </ul>
            </center>
        </div>

        <div class="col-md-4 col-sm-4" style="text-align: right; float: right;">
            <a href="./vystavene-faktury" class="btn btn-md btn-primary" style="margin-left: 10px;">Zpět na faktury</a>
        </div>
    </div>

    <table class="table table-bordered table-striped datatable dataTable">
        <thead>
        <tr>
            <th width="" class="text-center">ID</th>
            <th width="" class="text-center">Variabilní symbol</th>
            <th width="" class="text-center">Částka</th>
            <th width="" class="text-center">Měna</th>
            <th width="" class="text-center">Datum a čas</th>
            <th width="" class="text-center">Typ</th>
            <th width="300px" class="text-center">Popis</th>
            <th width="300px" class="text-center">Popis interní</th>
            <th width="" class="text-center">Akce</th>
        </tr>
        </thead>

        <tbody role="alert" aria-live="polite" aria-relevant="all">
        <?php

        $invoices_query = $mysqli->query("SELECT o.id, s.*, b.* FROM orders o LEFT JOIN addresses_billing b ON b.id = o.billing_id LEFT JOIN addresses_shipping s ON s.id = o.shipping_id WHERE o.order_status < 3 ORDER BY o.id desc") or die($mysqli->error);

        while ($bank = mysqli_fetch_array($bank_query)) {

            ?>

            <tr class="even">
                <td class="text-center"><?= $bank['ident'] ?></td>
                <td class="text-center"><?php

                    $order_invoice = $mysqli->query("SELECT id FROM orders_invoices WHERE id = '".$bank['vs']."' OR order_id = '".$bank['vs']."' ")or die($mysqli->error);
                    $demand_invoice = $mysqli->query("SELECT id FROM demands_advance_invoices WHERE id = '".$bank['vs']."' ")or die($mysqli->error);

                    if(!empty($bank['vs'])){

                        if(mysqli_num_rows($order_invoice) > 0 || mysqli_num_rows($demand_invoice) > 0 || !empty($bank['manual_assign'])){
                            echo '<span class="text-success">'.$bank['vs'].'</span>';
                        }else{
                            echo '<span class="text-danger">'.$bank['vs'].'</span>';
                        }

                    }else{
                        echo '-';
                    }

                    ?></td>
                <td class="text-center"><?= thousand_seperator($bank['value']) ?></td>
                <td class="text-center"><?= $bank['currency'] ?></td>
                <td class="text-center"><?= date_formatted($bank['date']) ?></td>
                <td class="text-center"><?= $bank['typ'] ?></td>
                <td class="text-center"><?= $bank['description'] ?></td>
                <td class="text-center"><?= $bank['description_inside'] ?></td>
                <td class="text-center">
                    <?php
                    if(mysqli_num_rows($order_invoice) == 0 && mysqli_num_rows($demand_invoice) == 0 && empty($bank['manual_assign'])){

                        ?>
                        <a class="btn btn-green connect_invoice">
                            <i class="entypo-check"></i> Připojit k faktuře
                        </a>



                        <div class="show_connect" style="display: none;">
                            <form enctype='multipart/form-data' autocomplete="off" action="vystavene-faktury?id=<?= $bank['ident'] ?>&action=connect_invoice" method="post" role="form">
                                <div class="form-group">
                                    <?php
                                    mysqli_data_seek($invoices_query, 0);
                                    ?>
                                    <select id="invoice_id" name="invoice_id" class="select2" data-allow-clear="true" data-placeholder="Vyberte objednávku..."  style="width: 100% !important; margin: 10px 0 10px 0;">
                                        <option></option>
                                        <?php while ($invoice = mysqli_fetch_array($invoices_query)) { ?><option value="<?= $invoice['id'] ?>>"><?= $invoice['id'].' - '.user_name($invoice) ?></option><?php } ?>
                                    </select>
                                    <button style="width: 100%; float:left;" type="submit" class="btn btn-green"><i style=" position: relative; right: 0; top: 0;" class="entypo-plus"></i></button>
                                </div>

                            </form>

                        </div>

                    <?php }elseif(!empty($bank['manual_assign'])){

                        ?>
                        <a style="width: 100%; float:left; cursor: inherit; margin-bottom: 4px;" class="btn btn-primary">manuálně připojena <br><strong>ZF <?= $bank['manual_assign'] ?></strong></a>
                        <a href="vystavene-faktury?id=<?= $bank['ident'] ?>&action=invoice_disconnect" style="width: 100%; float:left;" class="btn btn-danger"><i style=" position: relative; right: 0; top: 0;" class="entypo-trash"></i> Odpojit</a>
                        <?php
                    } ?>
                </td>

            </tr>

        <?php } ?>

        </tbody>

    </table>




    <div class="row">
        <div class="col-md-12">
            <center><ul class="pagination pagination-sm">
                    <?php
                    include VIEW . "/default/pagination.php";?>
                </ul>
            </center>
        </div>

    </div>

<?php }elseif($_REQUEST['show'] == 'comgate'){

    if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {$od = 1;}

    $perpage = 70;
    $s_lol = $od - 1;
    $s_pocet = $s_lol * $perpage;
    $ordersmaxquery = $mysqli->query("SELECT COUNT(*) AS NumberOfOrders FROM transactions_comgate") or die($mysqli->error);

    $ordersmax = mysqli_fetch_array($ordersmaxquery);
    $max = $ordersmax['NumberOfOrders'];

    $pocet_prispevku = $max;

    $bank_query = $mysqli->query("SELECT t.*, DATE_FORMAT(t.datetime, '%d. %m. %Y %H:%i:%s') as dateformated, i.id as order_id FROM transactions_comgate t LEFT JOIN orders i ON i.reference_number = t.reference_number AND t.reference_number != 0 order by t.datetime desc limit " . $s_pocet . "," . $perpage)or die($mysqli->error);

    ?>
    <div class="row" style="margin-bottom: 20px">
        <div class="col-md-4 col-sm-4">
            <h2>Comgate platby</h2>
        </div>
        <div class="col-md-4 col-sm-4">
            <center>
                <ul class="pagination pagination-sm">
                    <?php include VIEW . "/default/pagination.php"; ?>
                </ul>
            </center>
        </div>
        <div class="col-md-4 col-sm-4" style="text-align: right; float: right;">
            <a href="./vystavene-faktury" class="btn btn-md btn-primary" style="margin-left: 10px;">Zpět na faktury</a>
        </div>
    </div>

    <table class="table table-bordered table-striped datatable dataTable">
        <thead>
        <tr>
            <th width="" class="text-center">Comgate ID</th>
            <th width="" class="text-center">ID propojení</th>
            <th width="" class="text-center">Obchod</th>
            <th width="" class="text-center">Status</th>
            <th width="" class="text-center">Datum a čas</th>
            <th width="" class="text-center">Částka</th>
            <th width="" class="text-center">Variabilní symbol</th>
            <th width="" class="text-center">E-mail</th>
<!--            <th width="" class="text-center">Telefon</th>-->
            <th width="500px" class="text-center">Popis</th>
            <th width="" class="text-center">Akce</th>
        </tr>
        </thead>

        <tbody role="alert" aria-live="polite" aria-relevant="all">
        <?php

        $invoices_query = $mysqli->query("SELECT o.id, s.*, b.* FROM orders o LEFT JOIN addresses_billing b ON b.id = o.billing_id LEFT JOIN addresses_shipping s ON s.id = o.shipping_id WHERE o.order_status < 3 ORDER BY o.id desc") or die($mysqli->error);


        $demands_invoices = $mysqli->query("SELECT i.id, d.user_name FROM demands_advance_invoices i, demands d WHERE i.demand_id = d.id AND d.status != 5 AND d.status != 6 GROUP BY i.id") or die($mysqli->error);

        while ($bank = mysqli_fetch_array($bank_query)) {

            ?>

            <tr class="even">
                <td class="text-center"><?= $bank['id'] ?></td>
                <td class="text-center"><?php if(!empty($bank['target_id'])){ echo '<strong class="text-success">'.$bank['target_id'].'</strong>'; }else{ echo '-'; } ?></td>
                <td class="text-center"><?= $bank['merchant'] ?></td>
                <td class="text-center"><btn class="btn btn-md <?php
                    if($bank['status'] == 'PAID'){
                        echo 'btn-success';
                    }elseif($bank['status'] == 'PENDING'){
                        echo 'btn-orange';
                    }elseif($bank['status'] == 'CANCELLED'){
                        echo 'btn-black';
                    }
                    ?>" ><?php

                    echo $bank['status'];

                    ?></btn></td>
                <td class="text-center"><?= $bank['dateformated'] ?></td>
                <td class="text-center"><?= thousand_seperator($bank['value']).' '.$bank['currency'] ?></td>
                <td class="text-center"><?php if(!empty($bank['reference_number'])){ echo $bank['reference_number']; }else{ echo '-'; }  ?></td>
                <td class="text-center"><?php if(!empty($bank['client_mail'])){ echo $bank['client_mail'];  }else{ echo '-'; } ?></td>
<!--                <td class="text-center">--><?// echo $bank['client_phone']; ?><!--</td>-->
                <td class="text-center" style="font-size: 10px;"><?= $bank['product_description'] ?></td>
                <td class="text-center">

                <?php if(empty($bank['target_id'])){ ?>

                <a class="btn btn-green connect_invoice">
                    <i class="entypo-check"></i> K faktuře
                </a>
                    <hr style="margin: 4px 0;">

                <a class="btn btn-green connect_demand">
                    <i class="entypo-check"></i> K zálohovce
                </a>

                <div class="show_connect_demand" style="display: none;">
                    <form enctype='multipart/form-data' autocomplete="off" action="vystavene-faktury?id=<?= $bank['id'] ?>&action=connect_invoice_comgate&target_type=demand" method="post" role="form">
                        <div class="form-group">
                            <?php
                            mysqli_data_seek($demands_invoices, 0);
                            ?>
                            <select id="invoice_id" name="target_id" class="select2" data-allow-clear="true" data-placeholder="Vyberte objednávku..."  style="width: 100% !important; margin: 10px 0 10px 0;">
                                <option></option>
                                <?php while ($invoice = mysqli_fetch_array($demands_invoices)) { ?><option value="<?= $invoice['id'] ?>>"><?= $invoice['id'].' - '.$invoice['user_name'] ?></option><?php } ?>
                            </select>
                            <button style="width: 100%; float:left;" type="submit" class="btn btn-green"><i style=" position: relative; right: 0; top: 0;" class="entypo-plus"></i></button>
                        </div>
                    </form>

                </div>

                <div class="show_connect" style="display: none;">
                    <form enctype='multipart/form-data' autocomplete="off" action="vystavene-faktury?id=<?= $bank['id'] ?>&action=connect_invoice_comgate&target_type=order_service" method="post" role="form">
                        <div class="form-group">
                            <?php
                            mysqli_data_seek($invoices_query, 0);
                            ?>
                            <select id="invoice_id" name="target_id" class="select2" data-allow-clear="true" data-placeholder="Vyberte objednávku..."  style="width: 100% !important; margin: 10px 0 10px 0;">
                                <option></option>
                                <?php while ($invoice = mysqli_fetch_array($invoices_query)) { ?><option value="<?= $invoice['id'] ?>>"><?= $invoice['id'].' - '.user_name($invoice) ?></option><?php } ?>
                            </select>
                            <button style="width: 100%; float:left;" type="submit" class="btn btn-green"><i style=" position: relative; right: 0; top: 0;" class="entypo-plus"></i></button>
                        </div>
                    </form>

                </div>



                <?php } ?>

                </td>

            </tr>

        <?php } ?>

        </tbody>

    </table>

    <div class="row">
        <div class="col-md-12">
            <center><ul class="pagination pagination-sm">
                    <?php
                    include VIEW . "/default/pagination.php";?>
                </ul>
            </center>
        </div>

    </div>

        <?php } ?>


<script type="text/javascript">

    jQuery(document).ready(function($)
    {

        $('.connect_invoice').click(function() {

            $(this).hide();
            $(this).next('.show_connect').show("slow");

        });


        $('.connect_demand').click(function() {

            $(this).hide();
            $(this).next('.show_connect_demand').show("slow");

        });


    });

</script>

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

            remote: 'controllers/modals/modal-remove.php?id='+id+'&type='+type,
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

            remote: 'controllers/modals/modal-change-status-data.php?id='+id,
        });
    });
});
</script>


<div class="modal fade" id="change-status-modal" aria-hidden="true" style="display: none; margin-top: 3%;">

</div>



    <?php include VIEW . '/default/footer.php'; ?>

