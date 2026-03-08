<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

if(isset($_REQUEST['category'])){ $category = $_REQUEST['category'];}

$pagetitle = "Statistika servisů";

$clientquery = $mysqli->query('SELECT * FROM demands WHERE email="' . $_COOKIE['cookie_email'] . '"') or die($mysqli->error);
$client = mysqli_fetch_assoc($clientquery);

include VIEW . '/default/header.php';

$query = "";
$currentpage = "statistika-servisu";
$allow_sites = "";

if (isset($category) && $category != "") {

    $query = ' AND category = "' . $category . '"';

}

$string_start = '';
$string_end = '';
$start_date = date('Y-m-d');
$end_date = date('Y-m-d');

if (isset($_REQUEST['start_date']) && $_REQUEST['start_date'] != "") {

    $start_date = $_REQUEST['start_date'];

    $query = $query . ' AND date >= "' . $_REQUEST['start_date'] . '"';

    $string_start = '&start_date=' . $_REQUEST['start_date'];

}

if (isset($_REQUEST['end_date']) && $_REQUEST['end_date'] != "") {

    $end_date = $_REQUEST['end_date'];

    $query = $query . ' AND date <= "' . $_REQUEST['end_date'] . '"';

    $string_end = '&end_date=' . $_REQUEST['end_date'];

}

?>


<div class="row">
    <div class="col-md-8 col-sm-7">
        <h2><?= $pagetitle ?></h2>
    </div>

</div>
<!-- Pager for search results --><div class="col-md-12 well" style="border-color: #ebebeb; margin-top: 8px;background-color: #fbfbfb;">


    <div class="btn-group col-sm-8" style="text-align: left; padding: 0;">
        <a href="?category=<?php echo $string_start;
        echo $string_end; ?>"><label class="btn btn-lg <?php if (!isset($category) || $category == "") { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                Vše
            </label></a>
        <?php
        $catq = $mysqli->query('SELECT * FROM services_categories WHERE customer = 1 ORDER BY title') or die($mysqli->error);
        while ($cat = mysqli_fetch_array($catq)) { ?>
            <a href="?category=<?= $cat['seoslug'].$string_start.$string_end ?>">
                <label class="btn btn-lg <?php if (isset($category) && $category == $cat['seoslug']) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                    <?= $cat['title'] ?>
                </label>
            </a>
        <?php } ?>
    </div>

    <form role="form" method="get" class="form-horizontal form-groups-bordered validate" action="statistika-servisu" enctype="multipart/form-data" novalidate="novalidate">

        <div class="form-group col-sm-4" style=" padding: 0; margin: 0;">
            <input name="category" style="display: none;" value="<?php if(isset($category)){ echo $category; }?>">

            <input id="datum1pridatsem" type="text" class="form-control datepicker" name="start_date" data-format="yyyy-mm-dd" placeholder="Počáteční datum" style="height: 41px; width: 140px; margin-right: 10px; float: left;" value="<?= $start_date ?>">

            <input id="datum1pridatsem" type="text" class="form-control datepicker" name="end_date" data-format="yyyy-mm-dd" placeholder="Konečné datum" style="height: 41px; width: 140px; margin-right: 10px; float: left;" value="<?= $end_date ?>">

            <button type="submit" style="padding: 10px 18px 10px 50px; height: 36px;" class="btn btn-blue btn-icon icon-left">
                Načíst
                <i class="fa fa-download" style="     padding: 10px 12px;"></i>
            </button>
        </div>

    </form>


    <div class="clear"></div>
</div><!-- Footer -->

<?php

$services_numbers = 0;
$services_total = 0;
$services_work = 0;
$services_purchase = 0;
$services_delivery = 0;
$services_material = 0;

function devat($value, $vat){
    return round((int)$value / (100 + (int)$vat) * 100);
}

$services_query = $mysqli->query("SELECT * FROM services WHERE state <> 'canceled'$query");
while ($services = mysqli_fetch_array($services_query)) {

    $this_work = 0;
    $items_query = $mysqli->query("SELECT * FROM services_items WHERE service_id = '".$services['id']."'")or die($mysqli->error);
    while($item = mysqli_fetch_assoc($items_query)){
        $services_work += devat($item['price'], $services['vat']);
        $this_work += devat($item['price'], $services['vat']);
    }

    $services_delivery += devat($services['delivery_price'], $services['vat']);

    $this_service_material = devat($services['price'], $services['vat']) - devat($services['delivery_price'], $services['vat']) - $this_work;

    if($services['service_purchase'] == 0 && $this_service_material > 0){
        $services_material += $this_service_material - devat($services['service_purchase'], $services['vat']);
    }else {
        $services_material += $this_service_material - round($this_service_material / 100 * 70);
    }

    $services_total += devat($services['price'], $services['vat']);

}

$services_purchase = round($services_total / 100 * 70);

?>
<div class="member-entry">
    <div style="width: 28%; float: left;"><h3 style="margin-top: 8.5px;"><span style=" font-size: 17px; color: #737881;">Zisk práce:</span> <?= number_format($services_work, 0, ',', ' ') ?> Kč</h3></div>
    <div style="width: 22%; float: left;"><h3 style="margin-top: 8.5px;"><span style=" font-size: 17px; color: #737881;">Zisk doprava:</span> <?= number_format($services_delivery, 0, ',', ' ') ?> Kč</h3></div>
    <div style="width: 22%; float: left;"><h3 style="margin-top: 8.5px;"><span style=" font-size: 17px; color: #737881;">Zisk materiál:</span> <?= number_format($services_material, 0, ',', ' ') ?> Kč</h3></div>
</div>

<div class="member-entry">
    <?php

    $data = [];
    $chart_query = $mysqli->query("SELECT year(date) as year, month(date) as month, sum(price) as price, sum(delivery_price) as delivery_price, sum(service_purchase) as service_purchase, month(date) as month, year(date) as year FROM services WHERE state <> 'canceled'$query GROUP BY year(date), month(date) ORDER BY year(date) desc");
    while ($chart = mysqli_fetch_array($chart_query)) {
        if($chart['year'] != 0 && $chart['price'] != 0) {


            $this_month_delivery = 0;
            $this_month_material = 0;
            $this_month_work = 0;
            $this_month_purchase = 0;
            $services_total = 0;

            $services_query = $mysqli->query("SELECT * FROM services WHERE state <> 'canceled' AND year(date) = '" . $chart['year'] . "' AND month(date) = '" . $chart['month'] . "' $query");
            while ($services = mysqli_fetch_array($services_query)) {

                $this_work = 0;
                $items_query = $mysqli->query("SELECT * FROM services_items WHERE service_id = '" . $services['id'] . "'") or die($mysqli->error);
                while ($item = mysqli_fetch_assoc($items_query)) {
                    $this_month_work += devat($item['price'], $services['vat']);
                    $this_work += devat($item['price'], $services['vat']);
                }

                $this_month_delivery += devat($services['delivery_price'], $services['vat']);
                $this_service_material = devat($services['price'], $services['vat']) - devat($services['delivery_price'], $services['vat']) - $this_work;

                if ($services['service_purchase'] == 0 && $this_service_material > 0) {

                    $this_month_material += $this_service_material - devat($services['service_purchase'], $services['vat']);
                    $this_month_purchase += devat($services['service_purchase'], $services['vat']);

                } else {

                    $this_month_material += $this_service_material - round($this_service_material / 100 * 70);
                    $this_month_purchase += round($this_service_material / 100 * 70);

                }

                $services_total += devat($services['price'], $services['vat']);
                $services_numbers++;

            }
            $data[$chart['year'] . '-' . $chart['month']] = [
                'income' => str_replace(",", ".", $services_total),
                'purchase' => str_replace(",", ".", $this_month_purchase),
                'total' => str_replace(",", ".",$services_total - $this_month_purchase),
            ];

            /*a: <?= $chart['price'] ?>, b: <?= $minus ?>, c: <?php echo $total; ? */
        }
    }

    ?>
    <div id="chart10" style="height: 300px"></div>
    <script>


        $(document).ready(function()
        {

            Morris.Area({
                element: 'chart10',
                data: [

                    <?php foreach($data as $key => $chart){ ?>
                        { d: '<?= $key; ?>', a: <?= $chart['income']; ?>, b: <?= $chart['purchase']; ?>, c: <?= $chart['total']; ?>},
                    <?php
                    }

                    ?>
                ],
                xkey: 'd',
                behaveLikeLine: true,
                postUnits: ' Kč',
                ykeys: ['a','b','c'],
                labels: ['Celkem','Náklady' , 'Čistý zisk']
            });
        });

    </script>



</div>

<!-- Pager for search results --><div class="row">
    <div class="col-md-12">
        <center>

            <h1 style="margin-bottom: 50px;">Počet servisů: <?= $services_numbers ?><br><small>ceny bez DPH</small><br></h1>
            <h5 >U příslušneství (materiálu) bez nákupní ceny se počítá defaultní marže na 30%</h5>
        </center>
    </div>
</div><!-- Footer -->

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



