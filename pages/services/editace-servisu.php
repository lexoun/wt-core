<?php
include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/googlelogin.php";
include INCLUDES . "/functions.php";

if (isset($_REQUEST['od'])) {$od = $_REQUEST['od'];}
if (isset($_REQUEST['customer'])) {$customer = $_REQUEST['customer'];}
if (isset($_REQUEST['state'])) {$state = $_REQUEST['state'];}
if (isset($_REQUEST['category'])) {$category = $_REQUEST['category'];}

if (isset($_REQUEST['search'])) {$search = $_REQUEST['search'];}

if (!empty($search)) {

    $pagetitle = 'Editace servisů - hledaný výraz <i>"' . $search . '"</i>';

    $bread1 = "Editace servisů";
    $abread1 = "editace-servisu";

} else {

    $pagetitle = "Editace servisů";

}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "edit") {
    $displaysuccess = true;
    $successhlaska = "Servis byl úspěšně upraven, juhůůů. Hláška, která půjde určitě upravit.";

}



// QUERY BUILDER
$query = '';
$currentpage = 'editace-servisu';
$queryBuilder = array();
$sqlQueryBuilder = array();

if (isset($customer)){

    $queryBuilder['customer'] = $customer;
    $sqlQueryBuilder[] .= 'customertype = '.$customer;

}

if (!empty($state)){

    $queryBuilder['state'] = $state;
    $sqlQueryBuilder[] .= "state = '".$state."'";

}

if (!empty($category)){

    $queryBuilder['category'] = $category;
    $sqlQueryBuilder[] .= "category = '".$category."'";

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
    if ($thisPage == 'editace-servisu') {$pageSymbol = '?';} elseif ($thisPage != 'editace-servisu?') {$pageSymbol = '&';}

    if (isset($data['param']) && $data['param'] == $data['current_param']) {$button = 'btn-primary';} else { $button = 'btn-white';}

    $button = '<a href="' . $thisPage . $pageSymbol . $data['url'] . '" style="padding: 5px 11px !important;" class="btn ' . $button . '">' . $data['name'] . '</a>';

        return $button;
    
}


if(isset($state) && ($state == 'executed' || $state == 'unfinished' || $state == 'warranty' || $state == 'finished')){

    $sort = 'ORDER BY s.date DESC, s.id DESC';

}elseif(isset($state) && $state == 'canceled'){

    $sort = 'ORDER BY s.id DESC';

}else{

    $sort = 'ORDER BY s.date ASC, s.id DESC';

}


//$sort = 'ORDER BY s.date DESC, s.id DESC';


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

    $servicesQuery = $mysqli->query("
        SELECT 
           *, s.id as id, p.name as pay_method, 
           DATE_FORMAT(date, '%d. %m. %Y') as dateformated, 
           DATE_FORMAT(date, '%H:%i:%s') as hoursmins 
        FROM services s 
            LEFT JOIN shops_payment_methods p ON s.payment_method = p.link_name 
            LEFT JOIN addresses_billing bill ON bill.id = s.billing_id 
            LEFT JOIN addresses_shipping ship ON ship.id = s.shipping_id 
         WHERE (bill.billing_surname like '$search') 
            OR (bill.billing_name like '%$search%' OR bill.billing_surname like '%$search%') 
            OR (bill.billing_name like '%$search%' OR bill.billing_surname like '%$search%') 
            OR (bill.billing_name like '%$search%') 
            OR (ship.shipping_surname like '$search') 
            OR (ship.shipping_name like '%$search%' OR ship.shipping_surname like '%$search%') 
            OR (ship.shipping_name like '%$search%' OR ship.shipping_surname like '%$search%') 
            OR (ship.shipping_name like '%$search%') 
            OR (ship.shipping_email like '%$search%') 
            OR (ship.shipping_phone like '%$search%') 
            OR s.id like '%$search%'
        ORDER BY s.id DESC
    ") or die($mysqli->error);

    $max = mysqli_num_rows($servicesQuery);
    $perpage = 1000;

    $s_pocet = 0;  $od = 0; $pocet_prispevku = 0;

} else {

    $servicesMaxQuery = $mysqli->query("SELECT id FROM services $query") or die($mysqli->error);
    $max = mysqli_num_rows($servicesMaxQuery);

    if (empty($od)) { $od = 1; }

    $perpage = 24;

    $s_pocet = ($od - 1) * $perpage;
    $pocet_prispevku = $max;

    $servicesQuery = $mysqli->query("SELECT 
       s.*, s.id as id, p.name as pay_method, DATE_FORMAT(s.date, '%d. %m. %Y') as dateformated, DATE_FORMAT(s.date_added, '%d. %m. %Y') as date_added, p.name 
        FROM services s 
        LEFT JOIN shops_payment_methods p ON s.payment_method = p.link_name 
        $query 
        $sort 
        LIMIT " . $s_pocet . "," . $perpage) or die($mysqli->error);


}

include VIEW . '/default/header.php';

?>

<div class="row">
	<div class="col-md-4 col-sm-4">
		<h2><?= $pagetitle ?></h2>
	</div>

    <div class="col-md-4">
        <center><ul class="pagination pagination-sm">
                <?php
                include VIEW . "/default/pagination.php";?>
            </ul>

        </center>
    </div>
    <div class="col-md-4">

        <form method="get" role="form" style="float: right; margin: 17px 0;">

            <div class="form-group">
                <div style="margin-bottom: 12px; width: 260px; float:left; margin-left: 10px;margin-right: 4px;">
                    <input id="cheart" type="text" name="search" class="form-control typeahead" data-remote="data/autosuggest-clients.php?q=%QUERY" data-template="<div class='thumb-entry'><span class='image'><img src='{{img}}' width=45 height=45 /></span><span class='text' style='width: 75%;'><strong style='overflow: hidden;text-overflow: ellipsis;white-space: nowrap;'>{{value}}</strong><em>{{desc}}</em></span></div>" placeholder="Hledání..." />
                </div>

                <button style="width: 50px; float:left;" type="submit" class="btn btn-default"><i style=" position: relative; right: 0; top: 0;" class="entypo-search"></i></button>
            </div>

        </form>

    </div>
</div>





<div class="col-md-12 well" style="border-color: #ebebeb; background-color: #fbfbfb; padding: 6px; margin-bottom: 12px;">
	<div class="row">
		<div class="col-md-2" style="text-align: left;">

		<div class="btn-group">
        <a href="<?php $thisPage = preg_replace('/&?customer=[^&]*/', '', $currentpage);
    echo $thisPage;?>"  style="padding: 5px 11px !important;" class="btn <?php if (!isset($customer)) {echo 'btn-primary';} else {echo 'btn-white';}?>">
							Vše</a><?php

    $allStatus = array(0 => 'Sauna', 1 => 'Vířivka');

    // $button['param'] = '';
    if(isset($customer)){ $button['param'] = $customer; }
    $button['request_name'] = 'customer';

    foreach ($allStatus as $singleStatus => $value) {

        $button['url'] = 'customer=' . $singleStatus;
        $button['name'] = $value;
        $button['current_param'] = $singleStatus;

        echo button($button);

    }
    ?>
		</div>
	</div>

	<div class="col-sm-10" style="text-align: right; float: right;">

        <div class="btn-group">

        <a href="<?php $thisPage = preg_replace('/&?state=[^&]*/', '', $currentpage);
        echo $thisPage;?>"  style="padding: 5px 11px !important;" class="btn <?php if (!isset($state)) {echo 'btn-primary';} else {echo 'btn-white';}?>">
            Vše</a><?php
                                
        $allStatus = array('new' => 'Nový', 'waiting' => 'Čeká na díly', 'unconfirmed' => 'Nepotvrzený', 'confirmed' => 'Potvrzený', 'executed' => 'Provedený', 'unfinished' => 'Nedokončený', 'invoiced' => 'Fakturované', 'problematic' => 'Problémové', 'warranty' => 'Reklamace', 'finished' => 'Hotový', 'canceled' => 'Stornovaný');

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

        <hr>

    <div class="col-sm-12" style=" text-align: right; float: right;">
    
    <div class="btn-group">

        <a href="<?php $thisPage = preg_replace('/&?category=[^&]*/', '', $currentpage);
        echo $thisPage;?>"  style="padding: 5px 11px !important;" class="btn <?php if (!isset($category)) {echo 'btn-primary';} else {echo 'btn-white';}?>">
            Vše</a><?php
                
        $button['param'] = '';
        if(isset($category)){ $button['param'] = $category; }
        $button['request_name'] = 'category';

        $catq = $mysqli->query('SELECT * FROM services_categories ORDER BY title') or die($mysqli->error);
        while ($cat = mysqli_fetch_assoc($catq)) {
            $button['url'] = 'category=' . $cat['seoslug'];
            $button['name'] = $cat['title'];
            $button['current_param'] = $cat['seoslug'];

            echo button($button);

        }?>
        </div>


	</div>

</div>

</div>



<div id="table-2_wrapper" class="dataTables_wrapper form-inline" role="grid">
	<table class="table table-bordered table-striped datatable dataTable" id="table-2" aria-describedby="table-2_info">
	<thead>
    <tr role="row">
      <th class="text-center" style="min-width: 174px;">Objednávka</th>
        <th class="text-center" style="min-width: 90px;">Přidáno</th>
    <th class="text-center" style="min-width: 76px;">Produkt</th>
      <th class="text-center" >Kategorie</th>
      <th class="text-center" style="min-width: 90px;">Datum</th>
      <th class="text-center" style="">Informace</th>
      <th class="text-center" style="min-width: 220px;">Akce</th>
    </tr>
   </thead>


<tbody role="alert" aria-live="polite" aria-relevant="all">
<?php

while ($service = mysqli_fetch_assoc($servicesQuery)) {

    service($service);

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
    $(".toggle-modal-change-state").click(function(e){

      $('#change-state-modal').removeData('bs.modal');
       e.preventDefault();


       var id = $(this).data("id");

        $("#change-state-modal").modal({

            remote: '/admin/controllers/modals/modal-change-services.php?id='+id,
        });
    });
});
</script>


<div class="modal fade" id="change-state-modal" aria-hidden="true" style="display: none; margin-top: 3%;">

</div>

<?php include VIEW . '/default/footer.php'; ?>



