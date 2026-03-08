<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

if (isset($_REQUEST['type'])) {$type = $_REQUEST['type'];}
if (isset($_REQUEST['od'])) {$od = $_REQUEST['od'];}
if (isset($_REQUEST['year'])) {$year = $_REQUEST['year'];}
if (isset($_REQUEST['month'])) {$month = $_REQUEST['month'];}

if (isset($_REQUEST['q'])) {$search = $_REQUEST['q'];}

if (isset($search) && $search != "") {

    $pagetitle = 'Hledaný výraz "' . $search . '"';

    $bread1 = "Zálohové faktury";
    $abread1 = "zalohove-faktury";

} else {

    $pagetitle = "Zálohové faktury";

}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "connect_invoice") {

    $invoice_query = $mysqli->query("SELECT demand_id FROM demands_advance_invoices WHERE id = '" . $_POST['invoice_id'] . "'")or die($mysqli->error);
    $invoice = mysqli_fetch_assoc($invoice_query);

    $mysqli->query('UPDATE bank_transactions SET manual_assign = "' . $_POST['invoice_id'] . '" WHERE ident = "' . $_REQUEST['id'] . '"') or die($mysqli->error);

    $mysqli->query('UPDATE demands SET contract = 3 WHERE id = "' . $invoice['demand_id'] . '"') or die($mysqli->error);

    header('location: https://www.wellnesstrade.cz/admin/pages/invoices/zalohove-faktury?show=payments');
    exit;
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "invoice_disconnect") {

    $mysqli->query('UPDATE bank_transactions SET manual_assign = "0" WHERE ident = "' . $_REQUEST['id'] . '"') or die($mysqli->error);

    header('location: https://www.wellnesstrade.cz/admin/pages/invoices/zalohove-faktury?show=payments');
    exit;
}

$currentpage = 'zalohove-faktury';


include VIEW . '/default/header.php';


$branches = array();
$locations_query = $mysqli->query("SELECT * FROM shops_locations WHERE type = 'branch'")or die($mysqli->error);
while($singleLoc = mysqli_fetch_assoc($locations_query)){

    $branches[$singleLoc['id']] = $singleLoc['name'];

}



if($client['id'] == 2126) {

    $i = 0;
    $totalpiko = 0;
    $toBeGeneratedInvoices = $mysqli->query("SELECT * FROM demands_generate g, demands_generate_hottub h WHERE g.id = h.id") or die($mysqli– > error);
    while ($toBe = mysqli_fetch_array($toBeGeneratedInvoices)){

        $pepega = '';

        $total = $toBe['price_hottub'] + $toBe['price_delivery']+ $toBe['price_chemie'];

        $pepega = $mysqli->query("SELECT * FROM demands_advance_invoices WHERE status = '".$toBe['invoices_number']."' AND demand_id = '".$toBe['id']."'") or die($mysqli– > error);
        if(mysqli_num_rows($pepega) == 0){

            if($toBe['invoices_number'] != 1){
                $druhega = $mysqli->query("SELECT * FROM demands_advance_invoices WHERE status = '1' AND demand_id = '".$toBe['id']."'") or die($mysqli– > error);
                if(mysqli_num_rows($druhega) == 1) {



                    $druho = mysqli_fetch_array($druhega);

                    $totalpiko += $total - $druho['total_price'];

                    $i++;
                }

            }

        }

    }

    $i = 0;
}



if(empty($_REQUEST['show']) || $_REQUEST['show'] != 'payments'){


$filter = '';
$filter_d = '';


if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {
    $od = 1;
}

$perpage = 60;
$s_lol = $od - 1;
$s_pocet = $s_lol * $perpage;


if (isset($search) && $search != "") {

    $parts = explode(" ", $search);
    $last = array_pop($parts);
    $first = implode(" ", $parts);

    if ($first == "") {
        $first = 'aiufdjafdasjfd';
    }

    if ($last == "") {
        $last = 'dafksdkafdiaf';
    }

    $pocet_prispevku = 0;

    $invoices_query = $mysqli->query("SELECT *, DATE_FORMAT(i.date, '%d. %m. %Y') as dateformated, DATE_FORMAT(i.payment_date, '%d. %m. %Y') as payment_date_formated, i.id as id, i.deposit as deposit, i.status as status, d.showroom as location_id, i.payment_method as payment_method FROM demands_advance_invoices i, demands d, demands_generate g WHERE g.id = d.id AND d.id = i.demand_id AND ((d.id LIKE '$search') OR (i.id LIKE '$search') OR (d.id LIKE '%$search%') OR (i.id LIKE '%$search%') OR (d.user_name like '%$search%' OR d.user_name like '%$last%' OR d.user_name like '%$first%')) GROUP BY i.id order by i.id desc") or die($mysqli->error);


} else {


    if (isset($type)) {

        if ($type == 0) {

            $filter = "WHERE paid != 3 AND paid != 1 AND payment_date = '0000-00-00'";

            $filter_d = "AND i.paid != 3 AND i.paid != 1 AND payment_date = '0000-00-00'";

        } elseif ($type == 1) {

            $filter = "WHERE (payment_date != '0000-00-00' OR paid = 3 OR paid = 1)";

            $filter_d = "AND (i.payment_date != '0000-00-00' OR i.paid = 3 OR paid = 1)";

        }

        $currentpage = $currentpage . '?type=' . $type;

    }

    if (isset($year)) {

        if ($filter == "") {

            $filter = "WHERE year(i.date) = " . $year;

        } else {

            $filter = $filter . " AND year(i.date) = " . $year;

        }

        $filter_d = $filter_d . ' AND year(i.date) = ' . $year;

        if ($currentpage == "zalohove-faktury") {

            $currentpage = $currentpage . '?year=' . $year;

        } else {

            $currentpage = $currentpage . '&year=' . $year;

        }

    }

    if (isset($month)) {

        if ($filter == "") {

            $filter = "WHERE month(i.date) = " . $month;

        } else {

            $filter = $filter . " AND month(i.date) = " . $month;

        }

        $filter_d = $filter_d . ' AND month(i.date) = ' . $month;

        if ($currentpage == "zalohove-faktury") {

            $currentpage = $currentpage . '?month=' . $month;

        } else {

            $currentpage = $currentpage . '&month=' . $month;

        }

    }

    $ordersmaxquery = $mysqli->query('SELECT COUNT(*) AS NumberOfOrders FROM demands_advance_invoices i ' . $filter) or die($mysqli->error);

    $ordersmax = mysqli_fetch_array($ordersmaxquery);
    $max = $ordersmax['NumberOfOrders'];

    $pocet_prispevku = $max;

    $invoices_query = $mysqli->query('SELECT *, DATE_FORMAT(i.date, "%d. %m. %Y") as dateformated, DATE_FORMAT(i.payment_date, "%d. %m. %Y") as payment_date_formated, i.id as id, i.deposit as deposit, i.payment_method as payment_method, i.status as status, d.showroom as location_id FROM demands_advance_invoices i, demands d, demands_generate g WHERE g.id = d.id AND d.id = i.demand_id ' . $filter_d . ' order by i.id desc limit ' . $s_pocet . ',' . $perpage) or die($mysqli->error);

}




?>
        <div class="row">
            <div class="col-md-3 col-sm-3">
                <h2><?php if(empty($search)) {

                        echo $pagetitle;

                    }else{ echo 'Hledanému výrazu <i><u>"'.$search.'"</u></i> odpovídájí tyto výsledky:'; } ?></h2>
            </div>
            <div class="col-md-3">
                <center><ul class="pagination pagination-sm">
                        <?php
                        include VIEW . "/default/pagination.php";?>
                    </ul>

                </center>
            </div>
            <div class="col-md-6 col-sm-6" style="text-align: right; margin: 17px 0;">

                <form method="get" role="form" style="float: right;">

                    <div class="form-group">
                        <div style="width: 260px; float:left; margin-left: 10px;margin-right: 4px;"><input id="cheart" value="<?php if(!empty($search)) { echo $search; } ?>" type="text" name="q" class="form-control" placeholder="Hledání..." /></div>

                        <button style="width: 50px; float:left;" type="submit" class="btn btn-default"><i style=" position: relative; right: 0; top: 0;" class="entypo-search"></i></button>
                    </div>

                </form>

                <a href="?show=payments" class="btn btn-md btn-primary" style="margin-left: 10px;">Zobrazit kompletní historii plateb</a>

            </div>
        </div>


<div class="col-md-12 well" style="border-color: #ebebeb; background-color: #fbfbfb; margin-top: 10px;">
<div class="row">
	<div class="col-md-3">
		<div class="btn-group" style="text-align: left;">
						<?php $mark = "?";?>
						<a href="zalohove-faktury"><label class="btn btn-md <?php if (!isset($type)) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
							Vše
						</label></a>
						<a href="?type=1"><label class="btn btn-md <?php if (isset($type) && $type == 1) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
							Uhrazené
						</label></a>

						<a href="?type=0"><label class="btn btn-md <?php if (isset($type) && $type == 0) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
							Neuhrazené
						</label></a>


					</div>

				</div>

	    <div class="col-sm-4">
			<div class="btn-group" style="text-align: left; float: right;">
                <a href="zalohove-faktury<?php if (isset($month)) {echo '?month=' . $month;}?><?php if (isset($type)) {echo '?type=' . $type;}?>"><label class="btn btn-md <?php if (!isset($year)) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">Vše</label></a>

                <?php

                $current_year = date('Y');
                $range = range('2018', $current_year);
                foreach($range as $rangeYear){ ?>
                    <a href="?year=<?php echo $rangeYear; if (isset($month)) {echo '&month=' . $month;}?><?php if (isset($type)) {echo '&type=' . $type;}?>"><label class="btn btn-md <?php if (isset($year) && $year == $rangeYear) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>"><?= $rangeYear ?></label></a>
                <?php } ?>
            </div>
		</div>

		<div class="col-md-5">
                <div class="btn-group" style="text-align: left; float: right;">
                    <a href="zalohove-faktury<?php if (isset($year)) {echo '?year=' . $year;}?><?php if (isset($type)) {echo '?type=' . $type;}?>"><label class="btn btn-md <?php if (!isset($month)) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                        Vše
                    </label></a>
                    <a href="?month=1<?php if (isset($year)) {echo '&year=' . $year;}?><?php if (isset($type)) {echo '&type=' . $type;}?>"><label class="btn btn-md <?php if (isset($month) && $month == 1) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                        1
                    </label></a>
                    <a href="?month=2<?php if (isset($year)) {echo '&year=' . $year;}?><?php if (isset($type)) {echo '&type=' . $type;}?>"><label class="btn btn-md <?php if (isset($month) && $month == 2) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                        2
                    </label></a>
                    <a href="?month=3<?php if (isset($year)) {echo '&year=' . $year;}?><?php if (isset($type)) {echo '&type=' . $type;}?>"><label class="btn btn-md <?php if (isset($month) && $month == 3) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                        3
                    </label></a>
                    <a href="?month=4<?php if (isset($year)) {echo '&year=' . $year;}?><?php if (isset($type)) {echo '&type=' . $type;}?>"><label class="btn btn-md <?php if (isset($month) && $month == 4) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                        4
                    </label></a>
                    <a href="?month=5<?php if (isset($year)) {echo '&year=' . $year;}?><?php if (isset($type)) {echo '&type=' . $type;}?>"><label class="btn btn-md <?php if (isset($month) && $month == 5) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                        5
                    </label></a>
                    <a href="?month=6<?php if (isset($year)) {echo '&year=' . $year;}?><?php if (isset($type)) {echo '&type=' . $type;}?>"><label class="btn btn-md <?php if (isset($month) && $month == 6) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                        6
                    </label></a>
                    <a href="?month=7<?php if (isset($year)) {echo '&year=' . $year;}?><?php if (isset($type)) {echo '&type=' . $type;}?>"><label class="btn btn-md <?php if (isset($month) && $month == 7) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                        7
                    </label></a>
                    <a href="?month=8<?php if (isset($year)) {echo '&year=' . $year;}?><?php if (isset($type)) {echo '&type=' . $type;}?>"><label class="btn btn-md <?php if (isset($month) && $month == 8) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                        8
                    </label></a>
                    <a href="?month=9<?php if (isset($year)) {echo '&year=' . $year;}?><?php if (isset($type)) {echo '&type=' . $type;}?>"><label class="btn btn-md <?php if (isset($month) && $month == 9) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                        9
                    </label></a>
                    <a href="?month=10<?php if (isset($year)) {echo '&year=' . $year;}?><?php if (isset($type)) {echo '&type=' . $type;}?>"><label class="btn btn-md <?php if (isset($month) && $month == 10) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                        10
                    </label></a>
                    <a href="?month=11<?php if (isset($year)) {echo '&year=' . $year;}?><?php if (isset($type)) {echo '&type=' . $type;}?>"><label class="btn btn-md <?php if (isset($month) && $month == 11) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                        11
                    </label></a>
                    <a href="?month=12<?php if (isset($year)) {echo '&year=' . $year;}?><?php if (isset($type)) {echo '&type=' . $type;}?>"><label class="btn btn-md <?php if (isset($month) && $month == 12) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                        12
                    </label></a>
                </div>
            </div>
        </div>
    </div>


<?php
if (mysqli_num_rows($invoices_query) > 0) { ?>

<table class="table table-bordered table-striped datatable dataTable">
	<thead>
		<tr>
			<th width="" class="text-center">ID</th>
			<th width="" class="text-center">Druh</th>
			<th width="" class="text-center">Záloha</th>
            <th width="" class="text-center">PDP</th>
			<th width="" class="text-center">Poptávka</th>
			<th width="" class="text-center">Částka</th>
			<th width="" class="text-center">Celkem zaplaceno</th>
			<th width="" class="text-center">Přijaté platby</th>
			<th width="200" class="text-center">Informace</th>
			<th width="" class="text-center">Akce</th>
		</tr>
	</thead>

	<tbody role="alert" aria-live="polite" aria-relevant="all">
<?php
    while ($invoice = mysqli_fetch_array($invoices_query)) {

        $currency = currency($invoice['currency']);

        ?>
<tr class="even">
	<td class="text-center"><strong><a href="https://www.wellnesstrade.cz/admin/data/invoices/demands/Zalohova_faktura_<?= $invoice['id'] ?>.pdf?t=<?= $currentDate->getTimestamp() ?>" target="_blank"><?= $invoice['id'] ?></a></strong><br><small><?= $invoice['dateformated'] ?></small></td>

	<td class="text-center" <?php if (isset($invoice['status']) && $invoice['status'] == $invoice['invoices_number']) { ?>style="color: #00a651;"<?php } ?>>Faktura <strong><?= $invoice['status'] ?> z <?= $invoice['invoices_number'] ?></strong>



	</td>

	<td class="text-center"><span><?php if (isset($invoice['deposit_type']) && $invoice['deposit_type'] == 'percentage') {echo $invoice['deposit'] . '%';} else { ?>-<?php } ?></span></td>

    <td class="text-center"><span><?= $invoice['reverse_charge'] ?></span></td>
	<td class="text-center"><a href="../demands/zobrazit-poptavku?id=<?= $invoice['demand_id'] ?>"><?php if ($invoice['fik'] != "") {echo ' <small>EET</small>';} ?> <?= $invoice['user_name'] ?></a><br><?php if (isset($invoice['customer']) && $invoice['customer'] == 1) {echo 'vířivka';} else {echo 'sauna';}?></td>

	<td class="text-center"><span><strong><?= number_format($invoice['total_price'], 2, ',', ' ') ?></strong> <?= $currency['sign'] ?><br><?php if (isset($invoice['payment_method']) && $invoice['payment_method'] == 'cash') {

                $invoice['payment_method'] = 'cash';


                ?>hotově - <strong><?= $branches[$invoice['location_id']] ?></strong><?php

	} elseif (isset($invoice['payment_method']) && $invoice['payment_method'] == 'bankwire') {

                $invoice['payment_method'] = 'bacs';

	    ?>převodem<?php

	}elseif (isset($invoice['payment_method']) && $invoice['payment_method'] == 'card') {

                $invoice['payment_method'] = 'agmobindercardall';

                ?>platební kartou
	<?php } ?></span></td>

	<td class="text-center">
				<?php


                // nejdříve if paid == 0,
                // když jo tak rozjet check_payment, kterej uloží do db v případě že ok
                // když ne, tak rozjet payment_status

                $invoice['target_id'] = $invoice['id'];


                if($invoice['paid'] != 0){

                    echo payment_status($invoice);

                }else{

                    $payment = check_payment($invoice, 'demand');

                    echo '<span style="font-size: 13px; '.$payment['color'].'">'.$payment['info'].'</span>';
                }

            ?>
    </td>

	<td class="text-center"><span><?php

            $bank_query = $mysqli->query("SELECT *, DATE_FORMAT(date, '%d. %m. %Y') as dateformated FROM bank_transactions WHERE account = 'demand' AND (vs = '".$invoice['id']."' OR manual_assign = '".$invoice['id']."')")or die($mysqli->error);

            $allPayments = '';
            if(mysqli_num_rows($bank_query) > 0){

                while($bank = mysqli_fetch_assoc($bank_query)) {

                    $vs_id = '';
                    $show_marks = '';
                    if($bank['vs'] == $invoice['id'] && $invoice['target_id'] != $invoice['id']){

                        $vs_id = 'Zaplaceno pod číslem faktury!  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                        $show_marks = '<strong style="color: #03a9f4;">!!!</strong> ';
                    }

                    $allPayments .= '<div class="well tooltip-primary" style="padding: 4px 4px; margin-bottom: 4px;" data-toggle="tooltip" data-placement="top" title="" data-original-title="'.$vs_id.'ID: '.$bank['ident'].'"> '.$show_marks.number_format($bank['value'], 2, ',', ' ').'  '.$currency['sign'].' - <strong style="color: #00a651">' . $bank['dateformated'] . '</strong>               </div>
';

                }

            }elseif (!empty($invoice['payment_date']) && $invoice['payment_date'] != '0000-00-00 00:00:00' && $invoice['payment_date'] != '0000-00-00') {

                $allPayments .= '<strong style="color: #00a651">' . datetime_formatted($invoice['payment_date']) . '</strong>';

            } elseif(isset($invoice['payment_method']) && $invoice['payment_method'] == 'cash'){


                $allPayments .= '-';

            }else{
                $allPayments .= '<span style="color: #d42020;">žádná přijatá platba</span>';
            }


            echo $allPayments;


            ?></span></td>
	<td class="text-center"><span><?= $invoice['additional_text'] ?></span></td>


	<td style="text-align: center;">

				<a href="../demands/zobrazit-poptavku?id=<?= $invoice['demand_id'] ?>" target="_blank" class="btn btn-info hidden-print">
					<i class="entypo-user"></i>
				</a>
                <a href="https://www.wellnesstrade.cz/admin/data/invoices/demands/Zalohova_faktura_<?= $invoice['id'] ?>.pdf?t=<?= $currentDate->getTimestamp() ?>" target="_blank" class="btn btn-default hidden-print">
					<i class="entypo-search"></i>
				</a>

				<a href="javascript: w=window.open('https://www.wellnesstrade.cz/admin/data/invoices/demands/Zalohova_faktura_<?= $invoice['id'] ?>.pdf?t=<?= $currentDate->getTimestamp() ?>'); w.print(); " class="btn btn-primary hidden-print">
					<i class="entypo-print"></i>
				</a>


				<!--
				<a href="#" class="btn btn-success btn-icon icon-left hidden-print">
					Odeslat
					<i class="entypo-mail"></i>
				</a>
	-->

		</td>

</tr>


     <?php } ?>

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
			<?php
include VIEW . "/default/pagination.php";?>
			</ul>

            <?php

            $count_price_query = $mysqli->query("SELECT SUM(total_price) AS total_price, COUNT(id) as total_nums FROM demands_advance_invoices i WHERE paid != 1 AND paid != 3 AND payment_date = '0000-00-00'") or die($mysqli->error);
            $count_price = mysqli_fetch_array($count_price_query);

            ?>
            <h3 style="margin-bottom: 50px;">Neuhrazeno <u><?= $count_price['total_nums'] ?></u> faktur v celkové hodnotě <u><?= number_format($count_price['total_price'], 0, ',', ' ') ?></u> <?= $currency['sign'] ?></h3>
	</center>
	</div>

</div>


        <?php }else{

    if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {$od = 1;}

    $perpage = 70;
    $s_lol = $od - 1;
    $s_pocet = $s_lol * $perpage;
    $ordersmaxquery = $mysqli->query("SELECT COUNT(*) AS NumberOfOrders FROM bank_transactions WHERE account = 'demand'") or die($mysqli->error);

    $ordersmax = mysqli_fetch_array($ordersmaxquery);
    $max = $ordersmax['NumberOfOrders'];

    $pocet_prispevku = $max;

    $bank_query = $mysqli->query("SELECT t.*, DATE_FORMAT(t.date, '%d. %m. %Y') as dateformated FROM bank_transactions t WHERE t.account = 'demand' ORDER BY t.date desc limit " . $s_pocet . "," . $perpage)or die($mysqli->error); ?>

    <div class="row" style="margin-bottom: 20px">
        <div class="col-md-4 col-sm-4">
            <h2>Historie přijatých plateb <small>(poptávky)</small></h2>
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

            <a href="./zalohove-faktury" class="btn btn-md btn-primary" style="margin-left: 10px;">Zpět na zálohové faktury</a>

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
                $invoices_query = $mysqli->query("SELECT id FROM demands_advance_invoices ORDER BY id desc") or die($mysqli->error);

                while ($bank = mysqli_fetch_array($bank_query)) {

//                    print_r($bank);
                    ?>

                    <tr class="even">
                        <td class="text-center"><?= $bank['ident'] ?></td>
                        <td class="text-center"><?php


                            $order_invoice = $mysqli->query("SELECT id FROM orders_invoices WHERE id = '".$bank['vs']."' OR order_id = '".$bank['vs']."' ")or die($mysqli->error);
                            $demand_invoice = $mysqli->query("SELECT id FROM demands_advance_invoices WHERE id = '".$bank['vs']."' ")or die($mysqli->error);

                            if(!empty($bank['vs'])){

//                                if(!empty($bank['demand_invoice_id']) || !empty($bank['order_invoice_id'])){
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
//                            if(empty($bank['demand_invoice_id']) && empty($bank['order_invoice_id']) && empty($bank['manual_assign'])){
                            if(mysqli_num_rows($order_invoice) == 0 && mysqli_num_rows($demand_invoice) == 0 && empty($bank['manual_assign'])){

                                ?>
                            <a class="btn btn-green connect_invoice">
                                <i class="entypo-check"></i> Připojit k faktuře
                            </a>



                            <div class="show_connect" style="display: none;">
                                <form enctype='multipart/form-data' autocomplete="off" action="zalohove-faktury?id=<?= $bank['ident'] ?>&action=connect_invoice" method="post" role="form">
                                    <div class="form-group">
                                            <?php
                                            mysqli_data_seek($invoices_query, 0);
                                            ?>
                                            <select id="invoice_id" name="invoice_id" class="select2" data-allow-clear="true" data-placeholder="Vyberte zálohovu fakturu..."  style="width: 100% !important; margin: 10px 0 10px 0;">
                                                <option></option>
                                                <?php while ($invoice = mysqli_fetch_array($invoices_query)) { ?><option value="<?= $invoice['id'] ?>>"><?= $invoice['id'] ?></option><?php } ?>
                                            </select>
                                        <button style="width: 100%; float:left;" type="submit" class="btn btn-green"><i style=" position: relative; right: 0; top: 0;" class="entypo-plus"></i></button>
                                    </div>

                                </form>

                            </div>

                            <?php }elseif(!empty($bank['manual_assign'])){

                            ?>
                                <a style="width: 100%; float:left; cursor: inherit; margin-bottom: 4px;" class="btn btn-primary">manuálně připojena <br><strong>ZF <?= $bank['manual_assign'] ?></strong></a>
                            <a href="zalohove-faktury?id=<?= $bank['ident'] ?>&action=invoice_disconnect" style="width: 100%; float:left;" class="btn btn-danger"><i style=" position: relative; right: 0; top: 0;" class="entypo-trash"></i> Odpojit</a>
<?php
                            } ?>
                        </td>

                    </tr>

                <?php } ?>

                </tbody>

            </table>

    <script type="text/javascript">

        jQuery(document).ready(function($)
        {

            $('.connect_invoice').click(function() {

                $(this).hide();
                $(this).next('.show_connect').show("slow");

            });


        });

    </script>


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
