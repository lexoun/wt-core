<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";


$locations_query = $mysqli->query("SELECT * FROM shops_locations WHERE type = 'branch'") or die($mysqli->error);
while ($location = mysqli_fetch_array($locations_query)) {
    $locationsArray[] = $location;
}



/*
 * // todo demand invoice

    if($invoice['payment_method'] == 'cash'){

        $balance_query = $mysqli->query("SELECT balance FROM cashier WHERE location_id = '".$getclient['showroom']."' ORDER BY id DESC limit 1")or die($mysqli->error);
        $balance = mysqli_fetch_assoc($balance_query);

        $description = 'Zálohová faktura ' . $invoice['id'];
        $next_balance = $balance['balance'] + $invoice['total_price'];

//        if($odd){
//            $income = 0; $outcome = $total['single'];
//        }else{
            $income = $invoice['total_price']; $outcome = 0;
//        }

        $mysqli->query("INSERT INTO cashier (date, invoice_id, var_sym, description, income, outcome, balance, location_id) VALUES (
            CURRENT_TIMESTAMP(),
            '".$invoice['id']."',
            '".$invoice['id']."',
            '".$description."',
            '".$income."',
            '".$outcome."',
            '".$next_balance."',
            '".$getclient['showroom']."')")or die($mysqli->error);

    }

 */



/* // todo order invoice

    $balance_query = $mysqli->query("SELECT balance FROM cash_register WHERE location_id = '".$order['location_id']."' ORDER BY id DESC limit 1")or die($mysqli->error);
    $balance = mysqli_fetch_assoc($balance_query);

    $description = 'Objednávka číslo ' . $order['id'];
    $next_balance = $balance['balance'] + $total['single'];

    if($odd){
        $income = 0; $outcome = $total['single'];
    }else{
        $income = $total['single']; $outcome = 0;
    }


    $mysqli->query("INSERT INTO cash_register (date, invoice_id, var_sym, description, income, outcome, balance, location_id) VALUES (
        CURRENT_TIMESTAMP(),
        '".$invoice_id."',
        '".$id."',
        '".$description."',
        '".$income."',
        '".$outcome."',
        '".$next_balance."',
        '".$order['location_id']."')")or die($mysqli->error);


 */

    if(empty($_REQUEST['location_id'])){

        $location_id = 2;

    }else{

        $location_id = $_REQUEST['location_id'];

    }

//    $pagetitle = $currentPage['name'];
    $pagetitle = 'Pokladna';

    if(!empty($_REQUEST['action']) && $_REQUEST['action'] == 'add'){


        if(!empty($_POST['value'])) {

            // todo check if is first insert of the day

            $balance_query = $mysqli->query("SELECT balance FROM cashier WHERE location_id = '".$_REQUEST['location_id']."' ORDER BY id DESC limit 1")or die($mysqli->error);
            $balance = mysqli_fetch_assoc($balance_query);

            if($_POST['type'] == 'income'){

                $income = $_POST['value'];
                $outcome = 0;
                $next_balance = $balance['balance'] + $income;

            }elseif($_POST['type'] == 'outcome'){

                $income = 0;
                $outcome = 0 - $_POST['value'];
                $next_balance = $balance['balance'] + $outcome;
            }


            $mysqli->query("INSERT INTO cashier (
                 date, 
                 invoice_id, 
                 var_sym, 
                 description, 
                 income, 
                 outcome,
                 balance, 
                 location_id,
                 admin_id, 
                 aggregate_type
                 ) VALUES (
                CURRENT_TIMESTAMP(),
                '".$_POST['invoice_id']."',
                '".$_POST['vs']."',
                '".$_POST['description']."',
                '".$income."',
                '".$outcome."',
                '".$next_balance."',
                '".$_REQUEST['location_id']."',
                '".$_POST['admin_id']."',
                '".$_POST['aggregate_type']."'
            )")or die($mysqli->error);


        }
        header('Location: https://www.wellnesstrade.cz/admin/pages/orders/pokladna?location_id='.$_REQUEST['location_id']);
        exit;
    }




    if(!empty($_REQUEST['action']) && $_REQUEST['action'] == 'check'){

        if(!empty($_POST['amount_found'])) {

            $amount_found = (float)trim($_POST['amount_found']);

            $balance_query = $mysqli->query("SELECT balance FROM cashier WHERE location_id = '".$_REQUEST['location_id']."' ORDER BY id DESC limit 1")or die($mysqli->error);
            $balance = mysqli_fetch_assoc($balance_query);

            $amount_difference = $amount_found - $balance['balance'];

            $mysqli->query("INSERT INTO cashier_check (
                           date,
                           admin_id,
                           description,
                           amount_expected,
                           amount_found,
                           amount_difference,
                           location_id
                         ) VALUES (
                            CURRENT_TIMESTAMP(),
                            '".$client['id']."',
                            '".$_POST['description']."',
                            '".$balance['balance']."',
                            '".$_POST['amount_found']."',
                            '".$amount_difference."',
                            '".$_REQUEST['location_id']."'
                        )
                ")or die($mysqli->error);


            if($amount_difference != 0){

                $income = 0;
                $outcome = 0;

                if($amount_difference > 0){
                    $income = $amount_difference;
                }else{
                    $outcome = -$amount_difference;
                }

                $next_balance = $balance['balance'] + $amount_difference;

                $mysqli->query("INSERT INTO cashier (
                     date, 
                     invoice_id, 
                     var_sym, 
                     description, 
                     income, 
                     outcome,
                     balance, 
                     location_id,
                     admin_id, 
                     aggregate_type
                 ) VALUES (
                CURRENT_TIMESTAMP(),
                0,
                '',
                'Úprava stavu na základě kotroly pokladny',
                '".$income."',
                '".$outcome."',
                '".$next_balance."',
                '".$_REQUEST['location_id']."',
                '".$client['id']."',
                0
                )")or die($mysqli->error);


            }


        }

        header('Location: https://www.wellnesstrade.cz/admin/pages/orders/pokladna?location_id='.$_REQUEST['location_id']);
        exit;
    }


include VIEW . '/default/header.php';

?>
    <div class="row" style="margin-bottom: 20px;">
        <div class="col-md-3 col-sm-3">
            <h2><?php

                    echo $pagetitle;

               ?></h2>
        </div>

        <div class="col-md-9 col-sm-9" style="text-align: right; padding-right: 50px;">
            <div class="btn-group">
                <?php
                foreach($locationsArray as $location){ ?>
                    <a href="?location_id=<?= $location['id'] ?>" style="padding: 5px 11px !important;" class="btn <?php if($location_id == $location['id']){ echo 'btn-primary'; }else{ echo 'btn-white'; } ?>"><?= $location['name'] ?></a>
                <?php } ?>
            </div>
        </div>
    </div>


    <script type="text/javascript">

        $(document).ready(function() {

            $('.show-confirm').click(function () {
                $(this).hide(400, function(){
                    $(this).next('.confirm').show(400);
                });
            });

            $('.hide-confirm').click(function () {
                $(this).parent('.confirm').hide(400, function(){
                    $(this).prev('.show-confirm').show(400);
                });
            });

        });

    </script>

    <style>
        .specialform input, .specialform select { height: 42px !important; }
    </style>

    <div class="row col-sm-12">

        <?php

        $opening_check_query = $mysqli->query("SELECT * FROM cashier_check WHERE DATE(date) = CURDATE() AND location_id = '".$location_id."'");

        if(mysqli_num_rows($opening_check_query) !== 0){ ?>
            <div class="col-sm-12 alert alert-success">
                Dnes již byl denní výkaz vyplněn.
            </div>
            <div style="clear: both"></div>
        <?php }else{ ?>
            <div class="col-sm-12 alert alert-danger">
                Dnes ještě nebyl denní výkaz vyplněn.
            </div>
            <div style="clear: both"></div>
        <?php } ?>

        <div class="well">

            <h4 style="margin-top: -4px;margin-left: 10px;">Vyplnit denní výkaz pokladny</h4>
            <form class="form-vertical validate specialform" role="form" method="post"
                  action="pokladna?action=check&location_id=<?= $location_id ?>" >
                <div class="col-sm-2" style="padding: 0 5px;">
                    <div class=" form-group">
                        <input type="number" class="form-control" name="amount_found" placeholder="Hodnota" required/>
                    </div>
                </div>
                <div class="col-sm-6" style="padding: 0 5px;">
                    <div class=" form-group">
                        <input class="form-control" name="description" placeholder="Poznámka" />
                    </div>
                </div>

                <div class="col-sm-3" style="padding: 0 5px;">
                    <a class="show-confirm btn btn-green btn-md btn-icon icon-left">
                        <i class="entypo-plus"></i> Přidat
                    </a>
                    <div class="confirm" style="display: none;">
                        <button type="submit"
                                class="btn btn-blue btn-md btn-icon icon-left"> <i
                                    class="entypo-check"></i> Všechno je správně! </button>
                        <a class="hide-confirm btn btn-black btn-md btn-icon icon-left"> <i
                                    class="entypo-cancel"></i> Zrušit </a>
                    </div>
                </div>
            </form>
            <div style="clear: both"></div>
        </div>
        <hr>


        <div class="well">
            <h4 style="margin-top: -2px; margin-left: 10px;">Zaznamenat platbu</h4>
            <form class="form-vertical validate specialform" role="form" method="post" action="pokladna?action=add&location_id=<?= $location_id ?>">

            <div class="col-md-1" style="padding: 0 5px;">
                <div class="form-group">
                    <select class="form-control" name="type">
                        <option value="income" selected>Příjem</option>
                        <option value="outcome">Výdej</option>
                    </select>
                </div>
            </div>
            <div class="col-sm-3" style="padding: 0 5px;">
                <div class=" form-group">
                    <?php

                    $demands_invoices_query = $mysqli->query('SELECT 
                        *, DATE_FORMAT(i.date, "%d. %m. %Y") as dateformated, DATE_FORMAT(i.payment_date, "%d. %m. %Y") as payment_date_formated, i.id as id, i.deposit as deposit, i.payment_method as payment_method, i.status as status, d.showroom as location_id 
                        FROM 
                             demands_advance_invoices i, demands d, demands_generate g 
                        WHERE g.id = d.id AND d.id = i.demand_id AND (paid = 0 OR paid = 2)
                            AND i.date BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND NOW()
                        ORDER BY i.id DESC') or die($mysqli->error);



                    $orders_invoices_query = $mysqli->query("SELECT * FROM (

                        SELECT i.currency, i.status, i.total_price, o.paid, o.paid_value, i.id, i.type, i.order_id as target_id, DATE_FORMAT(i.date, '%d. %m. %Y') as dateformated, i.date, i.fik, s.shipping_name, s.shipping_surname, b.billing_name, b.billing_surname, b.billing_company, o.payment_method, i.invoice_id, o.payment_date, o.location_id, o.transaction_id FROM orders_invoices i, orders o INNER JOIN addresses_billing b ON b.id = o.billing_id INNER JOIN addresses_shipping s ON s.id = o.shipping_id WHERE i.type = 'order' AND i.order_id = o.id AND i.date BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND NOW()
                
                        UNION
                
                        SELECT i.currency, i.status, i.total_price, o.paid, o.paid_value, i.id, i.type, i.order_id as target_id, DATE_FORMAT(i.date, '%d. %m. %Y') as dateformated, i.date, i.fik, s.shipping_name, s.shipping_surname, b.billing_name, b.billing_surname, b.billing_company, o.payment_method, i.invoice_id, o.payment_date, o.location_id, o.transaction_id 
                        FROM orders_invoices i, services o 
                            INNER JOIN addresses_billing b ON b.id = o.billing_id  
                            INNER JOIN addresses_shipping s ON s.id = o.shipping_id 
                        WHERE i.type = 'service' AND i.order_id = o.id AND i.date BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND NOW()
                
                        ) AS invoices 
            ORDER BY invoices.id DESC
        ") or die($mysqli->error);

                    ?>
                    <select name="invoice_id" class="select2" data-allow-clear="true"
                            data-placeholder="Číslo faktury..."
                            style="width: 100% !important; margin: 0;">
                        <option></option>
                        <?php while ($demands_invoices = mysqli_fetch_array($demands_invoices_query)) { ?>
                            <option value="<?= $demands_invoices['id'] ?>>">
                            <?= $demands_invoices['id'].' - '.$demands_invoices['user_name']; ?>
                            </option>
                        <?php } ?>
                        <?php while ($orders_invoices = mysqli_fetch_array($orders_invoices_query)) { ?>
                            <option value="<?= $demands_invoices['id'] ?>>">
                            <?= $orders_invoices['id'].' - '.$orders_invoices['target_id'].' - '.$orders_invoices['billing_name'].' '.$orders_invoices['billing_surname']; ?>
                            </option>
                        <?php } ?>
                    </select>

                </div>
            </div>
            <div class="col-sm-2" style="padding: 0 5px;">
                <div class=" form-group">
                    <input class="form-control" name="vs" type="number" placeholder="Variabilní symbol" />
                </div>
            </div>
            <div class="col-sm-4" style="padding: 0 5px;">
                <div class=" form-group">
                    <input class="form-control" name="description" type="text" placeholder="Popis" />
                </div>
            </div>
            <div class="col-sm-2" style="padding: 0 5px;">
                <div class=" form-group">
                    <input class="form-control" name="value" type="number" placeholder="Částka" required/>
                </div>
            </div>
            <div class="col-sm-2" style="padding: 0 5px;">
                <div class=" form-group">
                    <select class="form-control" name="admin_id" required>
                        <option value="">Výběr zadavatele</option>
                        <?php
                        $admins_query = $mysqli->query("SELECT id, user_name FROM demands WHERE role != 'client' AND role != 'technician' AND active = 1 ORDER BY user_name ASC");
                        while ($admin = mysqli_fetch_array($admins_query)) {
                            ?>
                            <option value="<?= $admin['id']; ?>"><?= $admin['user_name'] ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="col-sm-2" style="padding: 0 5px;">
                <div class=" form-group">
                    <select class="form-control" name="aggregate_type" required>
                        <option>Výběr kategorie</option>
                        <option value="order">Objednávka</option>
                        <option value="demand">Poptávka</option>
                        <option value="service">Servis</option>
                        <option value="other">Ostatní</option>
                    </select>
                </div>
            </div>
            <div class="col-sm-3" style="padding: 0 5px;">
                <a class="show-confirm btn btn-green btn-md btn-icon icon-left">
                    <i class="entypo-plus"></i> Přidat
                </a>
                <div class="confirm" style="display: none;">
                    <button type="submit"
                        class="btn btn-blue btn-md btn-icon icon-left"> <i
                            class="entypo-check"></i> Všechno je správně! </button>
                    <a class="hide-confirm btn btn-black btn-md btn-icon icon-left"> <i
                                class="entypo-cancel"></i> Zrušit </a>
                </div>
            </div>

            </form>
            <div style="clear: both"></div>
        </div>



    </div>
        <div class="row col-sm-12">

    <div class="well">

    <?php

        $records_query = $mysqli->query("SELECT c.* , d.user_name
        FROM cashier c 
            INNER JOIN demands d ON d.id = c.admin_id
        WHERE MONTH(c.date) = MONTH(CURRENT_DATE) AND YEAR(c.date) = YEAR(CURRENT_DATE)  AND c.location_id = '".$location_id."' ORDER BY c.id DESC") or die($mysqli->error);

        if (mysqli_num_rows($records_query) > 0) { ?>

        <table class="table table-bordered table-striped datatable dataTable">
            <thead>
            <tr>
                <th width="" class="text-center">ID</th>
                <th width="" class="text-center">Datum</th>
                <th width="" class="text-center">Číslo dokladu</th>
                <th width="" class="text-center">Variabilní symbol</th>
                <th width="" class="text-center">Popis</th>
                <th width="" class="text-center">Příjem</th>
                <th width="" class="text-center">Výdej</th>
                <th class="text-center">Konečný stav</th>
                <th width="" class="text-center">Zadavatel</th>
            </tr>
            </thead>

            <tbody role="alert" aria-live="polite" aria-relevant="all">
            <?php
            $i = mysqli_num_rows($records_query);
            while ($record = mysqli_fetch_assoc($records_query)) {

                ?>

                <tr class="even">
                    <td class="text-center">
                        <?= $i ?>
                    </td>
                    <td class="text-center">
                        <?= $record['date'] ?>
                    </td>
                    <td class="text-center">
                        <?php

                        $hrefUrl = '';

                        $check_orders_invoice = $mysqli->query("SELECT id FROM orders_invoices WHERE id = '".$record['invoice_id']."'")or die($mysqi->error);

                        if(mysqli_num_rows($check_orders_invoice) > 0) {

                            $hrefUrl = 'orders/' . $record['invoice_id'] . '.pdf?t=' . $currentDate->getTimestamp();


                        }else{

                            $check_demands_invoice = $mysqli->query("SELECT id FROM demands_advance_invoices WHERE id = '".$record['invoice_id']."'")or die($mysqi->error);
                            if(mysqli_num_rows($check_demands_invoice) > 0) {

                                $hrefUrl = 'demands/Zalohova_faktura_' . $record['invoice_id'] . '.pdf?t=' . $currentDate->getTimestamp();

                            }

                        }

                        if(!empty($hrefUrl)){
                            ?>
                            <a href="https://www.wellnesstrade.cz/admin/data/invoices/<?= $hrefUrl ?>" target="_blank">

                                <?= $record['invoice_id'] ?></a>
                        <?php }else{

                            echo $record['invoice_id'];

                        } ?>
                    </td>
                    <td class="text-center">
                        <?= $record['var_sym'] ?>
                    </td>
                    <td class="text-center">
                        <?= $record['description'] ?>
                    </td>
                    <td class="text-center">
                        <?php if(!empty($record['income'])){

                            echo '<strong class="text-success">+'.thousand_seperator($record['income']).' Kč</strong>';

                        }else{ echo '-'; } ?>
                    </td>
                    <td class="text-center">

                        <?php
                        if(!empty($record['outcome'])){


                            echo '<strong class="text-danger">'.thousand_seperator($record['outcome']).' Kč</strong>';

                        }else{ echo '-'; } ?>
                    </td>
                    <td class="text-center">
                        <strong><?= thousand_seperator($record['balance']) ?></strong> Kč
                    </td>
                    <td class="text-center">
                        <?= $record['user_name'] ?>
                    </td>
                </tr>
                <?php

                $i--;
            }
            ?>

            </tbody>

        </table>

        <?php } else { ?>


            <ul class="cbp_tmtimeline" style="margin-left: 25px;  margin-top: 50px;">
                <li style="margin-top: 80px;">

                    <div class="cbp_tmicon">
                        <i class="entypo-block" style="line-height: 42px !important;"></i>
                    </div>

                    <div class="cbp_tmlabel empty" style="padding-top: 9px;">
                        <span><a style="font-weight: bold; margin-left: -12px;font-size: 17px;">Tento měsíc není žádná transakce.</a></span>
                    </div>
                </li>
            </ul>
            <?php
        }

    $checks_query = $mysqli->query("SELECT ch.*, DATE(ch.date) as date_formated, d.user_name 
    FROM cashier_check ch
        INNER JOIN demands d ON d.id = ch.admin_id
    WHERE MONTH(ch.date) = MONTH(CURRENT_DATE) AND YEAR(ch.date) = YEAR(CURRENT_DATE) AND ch.location_id = '".$location_id."' 
    ORDER BY ch.id DESC") or die($mysqli->error);

    if(mysqli_num_rows($checks_query) > 0){

        ?>
            <hr>
        <h4>Výkazy pokladny:</h4>
        <table class="table table-bordered table-striped datatable dataTable">
            <thead>
            <tr>
                <th width="" class="text-center">Čas</th>
                <th width="" class="text-center">Zadavatel</th>
                <th width="" class="text-center">Zjištěná částka</th>
                <th width="" class="text-center">Očekávaná částka</th>
                <th width="" class="text-center">Rozdíl</th>
                <th width="" class="text-center">Poznámka</th>
            </tr>
            </thead>

            <tbody role="alert" aria-live="polite" aria-relevant="all">
        <?php


    while ($check = mysqli_fetch_assoc($checks_query)) {

        ?>
        <tr class="even">
            <td class="text-center"><?= $check['date'] ?></td>
            <td class="text-center"><?= $check['user_name'] ?></td>
            <td class="text-center"><?= $check['amount_found'] ?></td>
            <td class="text-center"><?= $check['amount_expected'] ?></td>
            <td class="text-center"><?= $check['amount_difference'] ?></td>
            <td class="text-center"><?= $check['description'] ?></td>
        </tr>
    <?php } ?>
            </tbody>
        </table>
    <?php } ?>
    </div>


<script type="text/javascript">

    $(document).ready(function(){

        $('.showMonth').click(function(){

            var target = $(this).parent().next('.month');

            if(target.is(':visible')){

                $(this).html('Zobrazit');

            }else{

                $(this).html('Skrýt');

            }

            target.toggle();

        });

    });

</script>

<?php

    if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {$od = 1;}
    $perpage = 40;
    $s_lol = $od - 1;
    $s_pocet = $s_lol * $perpage;

    $ordersmaxquery = $mysqli->query("SELECT COUNT(*) AS NumberOfOrders FROM cashier WHERE MONTH(date) != MONTH(CURRENT_DATE) AND location_id = '".$location_id."' GROUP BY MONTH(date)") or die($mysqli->error);
    $ordersmax = mysqli_fetch_array($ordersmaxquery);
    $max = $ordersmax['NumberOfOrders'];

    $pocet_prispevku = $max;

    $months_query = $mysqli->query("SELECT date, month(date) as month, year(date) as year FROM cashier WHERE MONTH(date) != MONTH(CURRENT_DATE) AND location_id = '".$location_id."' GROUP BY MONTH(date) ORDER BY MONTH(date) DESC") or die($mysqli->error);

    while ($month = mysqli_fetch_array($months_query)) {


    ?>
        <div class="well col-md-12" style="display: inline-block; padding: 6px; margin-bottom: 4px;">



            <div class="col-sm-6">
                <h4 style="padding: 0; margin: 7.5px 0;"><i class="far fa-calendar-check"></i> &nbsp;&nbsp;<?= strftime("%B", strtotime($month['date'])) .' '.date('Y', strtotime($month['date'])) ?></h4>
            </div>


            <div class="col-sm-6" style="text-align: right;">
                <span class="showMonth btn btn-md btn-primary">Zobrazit</span>&nbsp;
                <a href="/admin/controllers/generators/cashier?location_id=<?= $location_id ?>&date=<?= $month['date'] ?>" target="_blank" class="btn btn-md btn-info">Export</a>
            </div>


            <div class="month" style="display: none;">

            <table class="month table table-bordered table-striped datatable dataTable" style="margin-top: 20px;  margin-bottom: 10px; float: left;">
                <thead>
                <tr>
                    <th width="" class="text-center">ID</th>
                    <th width="" class="text-center">Datum</th>
                    <th width="" class="text-center">Číslo dokladu</th>
                    <th width="" class="text-center">Variabilní symbol</th>
                    <th width="" class="text-center">Popis</th>
                    <th width="" class="text-center">Příjem</th>
                    <th width="" class="text-center">Výdej</th>
                    <th class="text-center">Konečný stav</th>
                    <th width="" class="text-center">Zadavatel</th>
                </tr>
                </thead>

                <tbody role="alert" aria-live="polite" aria-relevant="all">
                <?php

                $records_query = $mysqli->query("SELECT * FROM cashier WHERE MONTH(date) = '".$month['month']."' AND location_id = '".$location_id."' ORDER BY id DESC") or die($mysqli->error);

                $i = mysqli_num_rows($records_query);
                while ($record = mysqli_fetch_array($records_query)) {

                    ?>

                    <tr class="even">
                        <td class="text-center">
                            <?= $i ?>
                        </td>
                        <td class="text-center">
                            <?= $record['date'] ?>
                        </td>
                        <td class="text-center">
                            <?php

                            $hrefUrl = '';

                            $check_orders_invoice = $mysqli->query("SELECT id FROM orders_invoices WHERE id = '".$record['invoice_id']."'")or die($mysqi->error);

                            if(mysqli_num_rows($check_orders_invoice) > 0) {

                                $hrefUrl = 'orders/' . $record['invoice_id'] . '.pdf?t=' . $currentDate->getTimestamp();


                            }else{

                                $check_demands_invoice = $mysqli->query("SELECT id FROM demands_advance_invoices WHERE id = '".$record['invoice_id']."'")or die($mysqi->error);
                                if(mysqli_num_rows($check_demands_invoice) > 0) {

                                    $hrefUrl = 'demands/Zalohova_faktura_' . $record['invoice_id'] . '.pdf?t=' . $currentDate->getTimestamp();

                                }

                            }

                            if(!empty($hrefURl)){
                                ?>
                                <a href="https://www.wellnesstrade.cz/admin/data/invoices/<?= $hrefUrl ?>" target="_blank">

                                    <?= $record['invoice_id'] ?></a>
                            <?php }else{

                                echo $record['invoice_id'];

                            } ?>
                        </td>
                        <td class="text-center">
                            <?= $record['var_sym'] ?>
                        </td>
                        <td class="text-center">
                            <?= $record['description'] ?>
                        </td>
                        <td class="text-center">
                            <?php if(!empty($record['income'])){

                                echo '<strong class="text-success">+'.thousand_seperator($record['income']).' Kč</strong>';

                            }else{ echo '-'; } ?>
                        </td>
                        <td class="text-center">

                            <?php
                            if(!empty($record['outcome'])){


                                echo '<strong class="text-danger">'.thousand_seperator($record['outcome']).' Kč</strong>';

                            }else{ echo '-'; } ?>
                        </td>
                        <td class="text-center">
                            <strong><?= thousand_seperator($record['balance']) ?></strong> Kč
                        </td>
                        <td class="text-center">
                        </td>
                    </tr>
                <?php
                $i--;
                } ?>

                </tbody>

            </table>

                <br>
                <hr>

            <?php

            $checks_query = $mysqli->query("SELECT ch.*, DATE(ch.date) as date_formated, d.user_name 
    FROM cashier_check ch
        INNER JOIN demands d ON d.id = ch.admin_id
    WHERE MONTH(ch.date) = '".$month['month']."' AND YEAR(ch.date) = '".$month['year']."' AND ch.location_id = '".$location_id."' 
    ORDER BY ch.id DESC") or die($mysqli->error);

            if(mysqli_num_rows($checks_query) > 0){

                ?>
                <hr>
                <h4>Výkazy pokladny:</h4>
                <table class="table table-bordered table-striped datatable dataTable">
                    <thead>
                    <tr>
                        <th width="" class="text-center">Čas</th>
                        <th width="" class="text-center">Zadavatel</th>
                        <th width="" class="text-center">Zjištěná částka</th>
                        <th width="" class="text-center">Očekávaná částka</th>
                        <th width="" class="text-center">Rozdíl</th>
                        <th width="" class="text-center">Poznámka</th>
                    </tr>
                    </thead>

                    <tbody role="alert" aria-live="polite" aria-relevant="all">
                    <?php


                    while ($check = mysqli_fetch_assoc($checks_query)) {

                        ?>
                        <tr class="even">
                            <td class="text-center"><?= $check['date'] ?></td>
                            <td class="text-center"><?= $check['user_name'] ?></td>
                            <td class="text-center"><?= $check['amount_found'] ?></td>
                            <td class="text-center"><?= $check['amount_expected'] ?></td>
                            <td class="text-center"><?= $check['amount_difference'] ?></td>
                            <td class="text-center"><?= $check['description'] ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            <?php }

            ?>

            </div>

        </div>

    <?php }  ?>



    <div class="row">
        <div class="col-md-12">
            <center><ul class="pagination pagination-sm">
                    <?php

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

<?php include VIEW . '/default/footer.php'; ?>

