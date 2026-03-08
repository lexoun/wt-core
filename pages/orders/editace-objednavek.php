<?php
include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

if (isset($_REQUEST['state'])) {$state = $_REQUEST['state'];}
if (isset($_REQUEST['payment_method'])) { $payment_method = $_REQUEST['payment_method']; }
if (isset($_REQUEST['shipping_method'])) { $shipping_method = $_REQUEST['shipping_method']; }
if (isset($_REQUEST['site'])) { $site = $_REQUEST['site']; }

if (isset($_REQUEST['od'])) {$od = $_REQUEST['od'];}
if (isset($_REQUEST['search'])) { $search = $_REQUEST['search']; }

if (!empty($search)) {

    $pagetitle = 'Editace objednávek - hledaný výraz <i>"' . $search . '"</i>';

    $bread1 = "Editace objednávek";
    $abread1 = "editace-objednavek";

} else {

    $pagetitle = "Editace objednávek";

}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "changestatus") {

    $update = $mysqli->query("UPDATE orders SET status = '" . $_POST['status'] . "' WHERE id = '" . $_REQUEST['id'] . "'");
    echo lol;

    header('location: https://www.wellnesstrade.cz/admin/pages/orders/nezpracovane-objednavky?id=' . $_REQUEST['id'] . '&success=changestatus');
    exit;
}

include INCLUDES . "/remove-orders.php";

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "done") {

    $updatequery = $mysqli->query('UPDATE orders SET status = "1" WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);
    $displaysuccess = true;
    $successhlaska = "Objednávka byla úspěšna vyřízena.";
}

$cliquery = $mysqli->query('SELECT user_name FROM demands') or die($mysqli->error);


// QUERY BUILDER
$query = '';
$currentpage = 'editace-objednavek';
$queryBuilder = array();
$sqlQueryBuilder = array();



if (isset($site)){

    $queryBuilder['site'] = $site;
    $sqlQueryBuilder[] .= "order_site = '".$site."'";

}

if (!empty($state)  || (isset($state) && $state == '0')){

    $queryBuilder['state'] = $state;
    $sqlQueryBuilder[] .= "order_status = '".$state."'";

}

if (!empty($payment_method)){

    $queryBuilder['payment_method'] = $payment_method;
    $sqlQueryBuilder[] .= "payment_method = '".$payment_method."'";

}

if (!empty($shipping_method)){

    $queryBuilder['shipping_method'] = $shipping_method;
    $sqlQueryBuilder[] .= "order_shipping_method = d.link_name AND d.transporter_company = '".$shipping_method."'";

}


$i = 0;
foreach($sqlQueryBuilder as $single){

    if($i > 0){ $sign = ' AND '; }else{ $sign = ' WHERE'; $i++; }

    $query .= $sign.' '.$single;

}


if(!empty($queryBuilder)){

    $currentpage .= '?'.http_build_query($queryBuilder);

}



// END QUERY BUILDER

function button($data)
{

    global $currentpage;

    $thisPage = preg_replace('/&?' . $data['request_name'] . '=[^&]*/', '', $currentpage);

    $pageSymbol = '';
    if ($thisPage == 'editace-objednavek') {$pageSymbol = '?';} elseif ($thisPage != 'editace-objednavek?') {$pageSymbol = '&';}

    if ((!empty($data['param']) || (isset($data['param']) && $data['param'] == '0')) && $data['param'] == $data['current_param']) {$button = 'btn-primary';} else { $button = 'btn-white';}

    $button = '<a href="' . $thisPage . $pageSymbol . $data['url'] . '" style="padding: 5px 11px !important;" class="btn ' . $button . '">' . $data['name'] . '</a>';

    return $button;

}


include VIEW . '/default/header.php';






if (!empty($search)) {

    $parts = explode(" ", $search);
    $last = array_pop($parts);
    $first = implode(" ", $parts);

    if ($first == "") {
        $first = 0;
    }
    if ($last == "") {
        $last = 0;
    }

    $ordersQuery = $mysqli->query("
        SELECT 
           *, o.id as id, p.name as pay_method, d.name as ship_method, 
           DATE_FORMAT(order_date, '%d. %m. %Y') as dateformated, 
           DATE_FORMAT(order_date, '%H:%i:%s') as hoursmins 
        FROM orders o 
            LEFT JOIN shops_payment_methods p ON o.payment_method = p.link_name 
            LEFT JOIN shops_delivery_methods d ON o.order_shipping_method = d.link_name 
            LEFT JOIN addresses_billing bill ON bill.id = o.billing_id 
            LEFT JOIN addresses_shipping ship ON ship.id = o.shipping_id 
         WHERE (bill.billing_surname like '$search') 
            OR (bill.billing_name like '%$search%' OR bill.billing_surname like '%$search%') 
            OR (bill.billing_name like '%$search%' OR bill.billing_surname like '%$search%') 
            OR (bill.billing_name like '%$search%') 
            OR (ship.shipping_surname like '$search') 
            OR (ship.shipping_name like '%$search%' OR ship.shipping_surname like '%$search%') 
            OR (ship.shipping_name like '%$search%' OR ship.shipping_surname like '%$search%') 
            OR (ship.shipping_name like '%$search%') 
            OR o.customer_email like '%$search%'
            OR o.id like '%$search%'
        ORDER BY o.id DESC
    ") or die($mysqli->error);


} else {

    $ordersMaxQuery = $mysqli->query("SELECT o.id FROM orders o 
                    LEFT JOIN shops_payment_methods p ON o.payment_method = p.link_name 
                    LEFT JOIN shops_delivery_methods d ON o.order_shipping_method = d.link_name
                    $query") or die($mysqli->error);
    $max = mysqli_num_rows($ordersMaxQuery);


    if (empty($od)) {
        $od = 1;
    }

    $perpage = 30;

    $s_pocet = ($od - 1) * $perpage;
    $pocet_prispevku = $max;

    $ordersQuery = $mysqli->query("SELECT *, o.id as id, p.name as pay_method, d.name as ship_method, DATE_FORMAT(order_date, '%d. %m. %Y') as dateformated, DATE_FORMAT(order_date, '%H:%i:%s') as hoursmins
                    FROM orders o 
                        LEFT JOIN shops_payment_methods p ON o.payment_method = p.link_name 
                        LEFT JOIN shops_delivery_methods d ON o.order_shipping_method = d.link_name
                    $query
                    ORDER BY order_date DESC 
                    LIMIT " . $s_pocet . ',' . $perpage) or die($mysqli->error);

}

?>

        <div class="row">
            <div class="col-md-4">
                <h2><?= $pagetitle ?></h2>
            </div>

            <div class="col-md-4">
                <center>
                    <ul class="pagination pagination-sm">
                        <?php
                        include VIEW . "/default/pagination.php";?>
                    </ul>
                </center>
            </div>

            <div class="col-md-4">

                <form method="get" role="form" style="float: right; margin: 17px 0;">

                    <div class="form-group">
                        <div style="margin-bottom: 12px; width: 260px; float:left; margin-left: 10px;margin-right: 4px;"><input id="cheart" type="text" name="search" class="form-control typeahead" data-remote="data/autosuggest-clients.php?q=%QUERY" data-template="<div class='thumb-entry'><span class='image'><img src='{{img}}' width=45 height=45 /></span><span class='text' style='width: 75%;'><strong style='overflow: hidden;text-overflow: ellipsis;white-space: nowrap;'>{{value}}</strong><em>{{desc}}</em></span></div>" placeholder="Hledání..." /></div>

                        <button style="width: 50px; float:left;" type="submit" class="btn btn-default"><i style=" position: relative; right: 0; top: 0;" class="entypo-search"></i></button>
                    </div>

                </form>

            </div>
        </div>



        <div class="col-md-12 well" style="border-color: #ebebeb; background-color: #fbfbfb; padding: 6px; margin-bottom: 12px;">
            <div class="row">
                <div class="col-md-4" style="text-align: left;">

                    <div class="btn-group">
                        <a href="<?php $thisPage = preg_replace('/&?site=[^&]*/', '', $currentpage);
                        echo $thisPage;?>"  style="padding: 5px 11px !important;" class="btn <?php if (!isset($site)) {echo 'btn-primary';} else {echo 'btn-white';}?>">
                            Vše</a><?php

                        $allStatus = array('wellnesstrade' => 'WellnessTrade', 'spahouse' => 'Spahouse', 'saunahouse' => 'Saunahouse',  'spamall' => 'Spamall', );

                        // $button['param'] = '';
                        if(isset($site)){ $button['param'] = $site; }
                        $button['request_name'] = 'site';

                        foreach ($allStatus as $singleStatus => $value) {

                            $button['url'] = 'site=' . $singleStatus;
                            $button['name'] = $value;
                            $button['current_param'] = $singleStatus;

                            echo button($button);

                        }?>
                    </div>
                </div>


                <div class="col-sm-8" style="text-align: right; float: right;">

                    <div class="btn-group">
                        <a href="<?php $thisPage = preg_replace('/&?shipping_method=[^&]*/', '', $currentpage);
                        echo $thisPage;?>"  style="padding: 5px 11px !important;" class="btn <?php if (!isset($shipping_method)) {echo 'btn-primary';} else {echo 'btn-white';}?>">
                            Vše</a><?php

                        $button['param'] = '';
                        if(isset($shipping_method)){ $button['param'] = $shipping_method; }
                        $button['request_name'] = 'shipping_method';

                        $catq = $mysqli->query('SELECT * FROM shops_delivery_methods GROUP BY transporter_company ORDER BY name') or die($mysqli->error);
                        while ($cat = mysqli_fetch_assoc($catq)) {
                            $button['url'] = 'shipping_method=' . $cat['transporter_company'];
                            $button['name'] = $cat['transporter_company'];
                            $button['current_param'] = $cat['transporter_company'];

                            echo button($button);

                        }?>
                    </div>


                </div>
            </div>



                <hr>
            <div class="row">

                <div class="col-sm-6" style="text-align: left; float: left;">

                    <div class="btn-group">

                        <a href="<?php $thisPage = preg_replace('/&?state=[^&]*/', '', $currentpage);
                        echo $thisPage;?>"  style="padding: 5px 11px !important;" class="btn <?php if (!isset($state)) {echo 'btn-primary';} else {echo 'btn-white';}?>">
                            Vše</a><?php

                        $allStatus = array(0 => '<span class="circle-color red"></span> Nezpracovaná', 1 => '<span class="circle-color orange"></span> V řešení', 2 => '<span class="circle-color blue"></span> Připravená', 3 => '<span class="circle-color green"></span> Vyexpedovaná', 4 => '<span class="circle-color black"></span> Stornovaná');

                        $button['param'] = '';
                        if(isset($state)){ $button['param'] = $state; }
                        $button['request_name'] = 'state';

                        foreach ($allStatus as $singleStatus => $value) {

                            $button['url'] = 'state=' . $singleStatus;
                            $button['name'] = $value;
                            $button['current_param'] = $singleStatus;

                            echo button($button);

                        }?>
                    </div>

                </div>


                <div class="col-sm-6" style=" text-align: right; float: right;">

                    <div class="btn-group">

                        <a href="<?php $thisPage = preg_replace('/&?payment_method=[^&]*/', '', $currentpage);
                        echo $thisPage;?>"  style="padding: 5px 11px !important;" class="btn <?php if (!isset($payment_method)) {echo 'btn-primary';} else {echo 'btn-white';}?>">
                            Vše</a><?php

                        $button['param'] = '';
                        if(isset($payment_method)){ $button['param'] = $payment_method; }
                        $button['request_name'] = 'payment_method';

                        $catq = $mysqli->query('SELECT * FROM shops_payment_methods ORDER BY name') or die($mysqli->error);
                        while ($cat = mysqli_fetch_assoc($catq)) {
                            $button['url'] = 'payment_method=' . $cat['link_name'];
                            $button['name'] = $cat['name'];
                            $button['current_param'] = $cat['link_name'];

                            echo button($button);

                        }?>
                    </div>

                </div>

            </div>

        </div>



        <div id="table-2_wrapper" class="dataTables_wrapper form-inline" role="grid">
            <table class="table table-bordered table-striped datatable dataTable" id="table-2" aria-describedby="table-2_info">
                <thead>
                    <tr>
                        <th width="200px">Objednávka</th>
                        <th width="120px" class="text-center">Stav & Obchod</th>
                        <th style="width: 414px;">Zakoupeno</th>
                        <th>Doručení</th>
                        <th class="text-center">Datum</th>
                        <th class="text-center">Cena celkem</th>
                        <th width="40px" class="text-center">Akce</th>
                    </tr>
                </thead>


                <tbody role="alert" aria-live="polite" aria-relevant="all">
                <?php

                while ($order = mysqli_fetch_assoc($ordersQuery)) {

                    ordersnew($order, $client['secretstring'], 0);;

                }

                $testnum = $s_pocet + 1 + $perpage;

                if ($od == 1) {$tonum = $s_pocet + $perpage;} elseif ($testnum > $pocet_prispevku) {$tonum = $pocet_prispevku;} else { $tonum = $s_pocet + 1 + $perpage;}
                ?>


                </tbody></table>

            </div>

        <div class="row">
            <div class="col-md-12">
                <center><ul class="pagination pagination-sm">
                        <?php $currentpage = "nezpracovane-objednavky";
                        include VIEW . "/default/pagination.php";?>
                    </ul>

                    <h2 style="margin-bottom: 30px;">Celkem: <?= $max ?></h2>
                </center>
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

