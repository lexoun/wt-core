<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

if (isset($_REQUEST['admin_id'])) { $admin_id = $_REQUEST['admin_id'];}
if (isset($_REQUEST['year'])) { $year = $_REQUEST['year'];}
if (isset($_REQUEST['month'])) { $month = $_REQUEST['month'];}

$pagetitle = "Statistika prodejců";

include VIEW . '/default/header.php';

/*
zaplacené zálohy
nezaplacené doplatky
kompletně zaplacené kontejnery?
 */


$kurz_url = "https://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_devizoveho_trhu/denni_kurz.txt";
$kurz_data =  file_get_contents($kurz_url);
$output = explode("\n", $kurz_data);

unset($output[0]); // odstranění prvního řádku - datum
unset($output[count($output)]); // odstranění posledního řádku - nic neobsahuje
unset($output[1]); // odstranění druhého řádku - legenda pro CSV

$kurz = array("CZK" => 1);
foreach($output as $radek){
    $mena = explode("|", $radek);
    $kurz[trim($mena[3])] = str_replace(",",".",trim($mena[4]));
}

// exchange_rate


// prodejní cena vířivky (+ komplet fakturace)

$query = '';

if(isset($admin_id)){
    $query .= ' AND d.admin_id = '.$admin_id;
}

if(isset($year)){
    $query .= ' AND YEAR(inv.payment_date) = '.$year;
}

if(isset($month)){
    $query .= ' AND MONTH(inv.payment_date) = '.$month;
}


$soldHottubsQuery = $mysqli->query("SELECT 
       d.*, w.*, g.*, cont.*, c.*, d.id as id, d.status as status, w.purchase_price as warehouse_purchase_price, c.purchase_price as container_purchase_price, admin.user_name as admin_name, inv.payment_date, cont.delivery_price as delivery_price
FROM demands d
    LEFT JOIN demands_generate_hottub g ON d.id = g.id 
    LEFT JOIN warehouse w ON w.demand_id = d.id
    LEFT JOIN containers_products c ON c.demand_id = d.id
    LEFT JOIN containers cont ON cont.id = c.container_id
    LEFT JOIN demands admin ON admin.id = d.admin_id
    LEFT JOIN demands_advance_invoices inv ON inv.demand_id = d.id
WHERE g.price_hottub != 0 $query GROUP BY d.id ORDER BY d.id DESC")or die($mysqli->error);

$allHottubs = [];

$total = [
    'sale_price' => 0,
    'purchase_price' => 0,
    'profit' => 0,
];

while($soldHottub = mysqli_fetch_assoc($soldHottubsQuery)){

    $containerCountQuery = $mysqli->query("SELECT COUNT(*) as total FROM containers_products WHERE container_id = '" . $soldHottub['container_id'] . "' ORDER BY id desc") or die($mysqli->error);

    $containerCount = mysqli_fetch_assoc($containerCountQuery);

    $hottubPurchasePrice = 0;


/*    if(!empty($soldHottub['container_purchase_price'])){

        //$hottubPurchasePrice += $soldHottub['container_purchase_price'];

    }else*/

    if(!empty($soldHottub['warehouse_purchase_price'])){

        if(!empty($soldHottub['first_exchange_rate'])){

            $first_exchange_rate = $soldHottub['first_exchange_rate'];

        }else{

            $first_exchange_rate = $kurz['USD'];

        }

        if(!empty($soldHottub['second_exchange_rate'])){

            $second_exchange_rate = $soldHottub['second_exchange_rate'];

        }else{

            $second_exchange_rate = $kurz['USD'];

        }


        $hottubPurchasePrice += $soldHottub['warehouse_purchase_price'] / 100 * 30 * $first_exchange_rate;
        $hottubPurchasePrice += $soldHottub['warehouse_purchase_price'] / 100 * 70 * $second_exchange_rate;


    }

    $soldHottub['delivery_share'] = ($soldHottub['delivery_price'] / $containerCount['total']) * $second_exchange_rate;

    $hottubPurchasePrice += $soldHottub['delivery_share'];

    // todo vlastní náklady na přidané věci (microsilk, ekozone atd.)

    $soldHottub['sale_price'] = hottubSalePrice($soldHottub['id']);
    $soldHottub['purchase_price'] = $hottubPurchasePrice;

    if(!is_nan($soldHottub['purchase_price'])){
        $soldHottub['profit'] = $soldHottub['sale_price'] - $soldHottub['purchase_price'];
    }else{
        $soldHottub['profit'] = $soldHottub['sale_price'];

    }


    $allHottubs[] = $soldHottub;

    $total['sale_price'] += $soldHottub['sale_price'];

    if(!is_nan($soldHottub['purchase_price'])){
        $total['purchase_price'] += $soldHottub['purchase_price'];
    }

    $total['profit'] += $soldHottub['profit'];

}

// nejdříve: náklad na vířivku
// pořizovací cena vířivky


// k vířivce připočítaný náklad na dopravu (rovnoměrně rozdělené v rámci kontejneru)


// výpočet

// filtrace na základě 1. prodejce, 2. pobočky, 3. per měsíc

// další statistika: kolik prodal: plánované návštěvy x neplánované návštěvy x ani jedno

// + kumulativní statistika za měsíc

?>

<div class="row">
    <div class="col-md-8 col-sm-7">
        <h2><?= $pagetitle ?></h2>
    </div>

</div>
<div class="col-md-12 well" style="border-color: #ebebeb; margin-top: 8px;background-color: #fbfbfb;">

    <div class="col-sm-6">
        <div class="btn-group" style="text-align: left; float: right;">
    <?php

    function filtrationLink(array $removed = [], bool $hasPrev = false): string
    {

        global $_REQUEST;

        $link = '';
        foreach($_REQUEST as $key => $request){

            if(in_array($key, $removed, true)){ continue; }

            if(empty($link) && !$hasPrev){

                $link .= '?'.$key.'='.$request;

            }else{

                $link .= '&'.$key.'='.$request;

            }

        }

        return $link;

    }
?>
        <a href="statistika-prodejcu<?= filtrationLink(['admin_id']) ?>">
            <label style="margin-bottom: 6px;" class="btn btn-sm <?php if (!isset($admin_id)) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                Vše
            </label>
        </a>
<?php
    $adminsQuery = $mysqli->query('SELECT id, user_name as name FROM demands WHERE (role = "salesman" OR role = "salesman-technician") AND active = 1 ORDER BY id')or die($mysqli->error);

    while($admin = mysqli_fetch_assoc($adminsQuery)){ ?>
        <a href="?admin_id=<?= $admin['id'].filtrationLink(['admin_id'],true) ?>">
            <label style="margin-bottom: 6px;" class="btn btn-sm <?php if (isset($admin['id']) && $admin['id'] == $admin_id) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>"><?= $admin['name'] ?></label></a>
    <?php }?>

        </div>
    </div>


    <div class="col-sm-6">
        <div class="btn-group" style="text-align: left; float: right;">
            <a href="statistika-prodejcu<?= filtrationLink(['year', 'month']) ?>">
                <label class="btn btn-sm <?php if (!isset($year)) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                    Vše
                </label>
            </a>
    <?php
        $range = range('2014', date('Y'));
        foreach($range as $yearLoop){ ?>
            <a href="?year=<?php echo $yearLoop.filtrationLink(['year'],true)?>">
                <label class="btn btn-sm <?php if (isset($year) && $year == $yearLoop) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                    <?= $yearLoop ?>
                </label>
            </a>
        <?php } ?>
        </div>

        <?php if(!empty($year)){ ?>
        <div class="btn-group" style="text-align: left; float: right; margin-top: 10px;">
            <a href="statistika-prodejcu<?= filtrationLink(['month']) ?>">
                <label class="btn btn-sm <?php if (!isset($month)) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                    Vše
                </label>
            </a>
            <?php
            for($i=1;$i<13;$i++){ ?>
                <a href="?month=<?php echo $i.filtrationLink(['month'],true)?>"><label class="btn btn-sm <?php if (isset($month) && $month == $i) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                        <?= $i ?>
                    </label>
                </a>
            <?php } ?>
        </div>
        <?php } ?>
    </div>

    <div class="clear"></div>
</div>

<div class="alert alert-info">V případě, že není u kontejneru zaevidovaná záloha a doplatek (tzn. není znám kurz), použije se pro výpočet AKTUÁLNÍ kurz dle ČNB (<?= $kurz["USD"] ?> CZK/USD)</div>


<div class="member-entry">
    <div style="width: 20%; float: left; text-align: center;"><h3 style="margin-top: 8.5px;"><span style=" font-size: 17px; color: #737881;">Příjmy:</span><br> <?= thousand_seperator($total['sale_price']) ?> Kč</h3>
    </div>
    <div style="width: 20%; float: left; text-align: center;"><h3 style="margin-top: 8.5px;"><span style=" font-size: 17px; color: #737881;">Náklady:</span><br> <?= thousand_seperator($total['purchase_price']) ?> Kč</h3></div>
    <div style="width: 20%; float: left; text-align: center;"><h3 style="margin-top: 8.5px;"><span style=" font-size: 17px; color: #737881;">Zisk:</span><br> <?= thousand_seperator($total['profit']) ?> Kč <br> <small>(počet prodaných: <?= mysqli_num_rows($soldHottubsQuery) ?>)</small></h3></div>
</div>


<table class="table table-bordered table-striped datatable dataTable" id="table-2" aria-describedby="table-2_info">
    <thead>

    <tr>
        <td>Poptávka</td>
        <td>Stav</td>
        <td>Datum přijaté zálohy</td>
        <td>Příjem</td>
        <td>Náklad<br><small>včetně dopravy</small></td>
        <td>Doprava</td>
        <td>Zisk</td>
    </tr>

    </thead>

    <tbody>
    <?php
    foreach($allHottubs as $data){
        ?>
        <tr>
            <td>
                <a href="/admin/pages/demands/zobrazit-poptavku?id=<?= $data['id'] ?>">
                    <?= $data['id'] ?>
                    <?= $data['user_name'] ?>
                </a>
            </td>
            <td>
                <?php
                foreach($demand_statuses as $status){
                    if($status['id'] === $data['status']){
                        echo $status['name'];
                    }
                }
                ?>
            </td>
            <td>
                <?= $data['payment_date'] ?>
            </td>
            <td>
                <?php if($data['sale_price'] !== '0.00'){ ?>
                    <strong class="text-success"><?= thousand_seperator($data['sale_price']) ?> Kč</strong>
                <?php }else{ ?>
                    <i class="entypo-block"></i>
                <?php } ?>
            </td>
            <td>
                <?php

                if(!empty($data['purchase_price']) && !is_nan($data['purchase_price'])){ ?>
                    <strong class="text-danger"><?= thousand_seperator($data['purchase_price']) ?> Kč</strong>
                <?php }else{ ?>
                    <i class="entypo-block"></i>
                <?php } ?>
            </td>
            <td>
                <?php

                if(!empty($data['delivery_share']) && !is_nan($data['delivery_share'])){ ?>
                    <strong class="text-warning"><?= thousand_seperator($data['delivery_share']) ?> Kč</strong>
                <?php }else{ ?>
                    <i class="entypo-block"></i>
                <?php } ?>
            </td>
            <td>
                <?php if(!empty($data['profit']) && !is_nan($data['profit'])){ ?>
                    <strong class="text-info"><?= thousand_seperator($data['profit']) ?> Kč</strong>
                <?php }else{ ?>
                    <i class="entypo-block"></i>
                <?php } ?>
            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>



<?php include VIEW . '/default/footer.php'; ?>



