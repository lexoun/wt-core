<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";

require $_SERVER['DOCUMENT_ROOT'] . '/admin/vendor/autoload.php';

include INCLUDES . "/googlelogin.php";
include INCLUDES . "/functions.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if(isset($_REQUEST['export']) && $_REQUEST['export'] == 'ekozone'){

    $spreadsheet = new Spreadsheet();

    $arrayData = [['Uživatel', 'Adresa', 'Telefonní číslo', 'Vířivka', 'Datum realizace']];

    $data_query = $mysqli->query("
        SELECT 
               d.*, d.id as demand_id, ship.*, bill.* 
        FROM (demands d, demands_specs_bridge b) 
            LEFT JOIN addresses_shipping ship ON ship.id = d.shipping_id 
            LEFT JOIN addresses_billing bill ON bill.id = d.billing_id 
        WHERE 
              b.client_id = d.id
          AND b.specs_id = 36 
          AND b.value = 'Ekozone'
          AND (d.status = 5 OR d.status = 8 OR d.status = 13) 
          AND d.customer = 1 
          AND d.role = 'client' 
        GROUP BY d.id 
        ORDER BY d.realization ASC
    ") or die($mysqli->error);

    while ($data = mysqli_fetch_assoc($data_query)) {

        $address_query = $mysqli->query('SELECT * 
        FROM addresses_billing b 
            LEFT JOIN addresses_shipping s ON s.id = "' . $data['shipping_id'] . '" WHERE b.id = "' . $data['billing_id'] . '"') or die($mysqli->error);
        $address = mysqli_fetch_assoc($address_query);

        $currentData = [
            $data['user_name'],
            return_address($address),
            phone_prefix($data['phone_prefix']).$data['phone'],
            $data['product'],
            $data['realization']
        ];

        array_push($arrayData, $currentData);

    }

    $spreadsheet->getActiveSheet()->fromArray(
        $arrayData,
        null,
        'A1'
    );

    $spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
    $spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
    $spreadsheet->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
    $spreadsheet->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
    $spreadsheet->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);

    $writer = new Xlsx($spreadsheet);
    $writer->save($_SERVER['DOCUMENT_ROOT'] . '/admin/storage/warehouse/Export_ekozone-' . date('Y-m-d-H-i') . '.xlsx');

    header('location:http://www.wellnesstrade.cz/admin/storage/warehouse/Export_ekozone-' . date('Y-m-d-H-i') . '.xlsx');
exit;
}








if (isset($_REQUEST['od'])) {$od = $_REQUEST['od'];}
if (isset($_REQUEST['q'])) {$search = $_REQUEST['q'];}

$pagetitle = "Kontrola vířivek";

if (isset($_REQUEST['type'])) {$type = $_REQUEST['type'];}

if (isset($_REQUEST['category'])) {$category = $_REQUEST['category'];}
if (isset($_REQUEST['year'])) { $year = $_REQUEST['year']; }
if (isset($_REQUEST['spec'])) { $spec = $_REQUEST['spec']; }


$link_year = '';
$query = '';
if(!empty($year)){

    $query .= ' AND YEAR(d.realization) = '.$year;
    $link_year .= '&year='.$year;

}

$link_spec = '';
if(!empty($spec)){

    if($spec == '36'){

        $query .= " AND b.specs_id = 36 AND b.value = 'Ekozone'";

    }elseif($spec == '3'){

        // microsilk
        $query .= " AND b.specs_id = 3 AND b.value = 'Ano'";

    }elseif($spec == '4'){

        // audio
        $query .= " AND b.specs_id = 4 AND b.value = 'Ano'";

    }

    $link_spec = '&spec='.$spec;

}else{

    // b.specs_id = 36 AND b.value = 'Ekozone'

    // microsilk ID 3

    // audio ID 4

//    $query .= " AND ((b.specs_id = 36 AND b.value = 'Ekozone') OR (b.specs_id = 3 AND b.value = 'Ano') OR (b.specs_id = 4 AND b.value = 'Ano'))";


}




if(!empty($_REQUEST['action']) && $_REQUEST['action'] == 'inspection'){


    foreach($_POST['type'] as $key => $val){

        $mysqli->query("INSERT INTO demands_inspections SET date = CURRENT_TIMESTAMP(), demand_id = '".$_REQUEST['id']."', type = '".$key."'")or die($mysqli->error);

    }

    Header("Location:https://www.wellnesstrade.cz/admin/pages/services/kontrola?result=success".$link_spec.$link_year);
    exit;
}


if(!empty($_REQUEST['action']) && $_REQUEST['action'] == 'cancel_inspection'){

    $mysqli->query("DELETE FROM demands_inspections WHERE demand_id = '".$_REQUEST['id']."' and type = '".$_REQUEST['type']."'")or die($mysqli->error);

    Header("Location:https://www.wellnesstrade.cz/admin/pages/services/kontrola?result=success".$link_spec.$link_year);
    exit;
}




include VIEW . '/default/header.php';

?>
<script type="text/javascript">
    jQuery(document).ready(function($)
    {
        // setTimeout(function(){
            showmap();
        // }, 540);
    });
</script>
<script type="text/javascript" src="//maps.google.com/maps/api/js?key=AIzaSyDRermPdr7opDFLqmrcOuK5L4zC2_U8XGk"></script>
<!--<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>-->
<script type="text/javascript">
    $(document).ready(function() {
        $(".show-specs").click(function() {

            $(this).parent().find('.hidden-specs').toggle('slow');

        });
    });
</script>

<?php



    ?>

    <div class="row" style="margin-bottom: 16px;">
        <div class="col-md-4 col-sm-4">
            <h2 style="float: left"><?= $pagetitle ?></h2>
        </div>


        <div class="col-md-4 col-sm-4">
        </div>
        <div class="col-md-4 col-sm-4">
            <a href="./kontrola?export=ekozone"
               style="padding: 10px 18px 10px 50px; height: 36px; float: right; margin-left: 14px;"
               class="btn btn-blue btn-icon icon-left">Export Ekozone <i class="fa fa-download"></i></a>
        </div>

    </div>

    <div class="col-md-12 well"
         style="border-color: #ebebeb; background-color: #fbfbfb; padding: 6px; margin-bottom: 12px;">
        <div class="row">
            <div class="col-md-8" style="text-align: left;">

                <div class="btn-group">

                    <a href="./kontrola?<?= $link_spec ?>" style="padding: 5px 11px !important;" class="btn btn-md <?php if (!isset($year)) {echo 'btn-primary';} else {echo 'btn-white';} ?>">Vše</a>
                    <?php
                    $range = range('2013', date('Y'));
                    foreach($range as $yearLoop){ ?>
                        <a href="?year=<?= $yearLoop.$link_spec ?>">
                            <label class="btn btn-sm <?php if (!empty($year) && $year == $yearLoop) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                                <?= $yearLoop ?>
                            </label>
                        </a>
                    <?php } ?>
                </div>
            </div>



            <div class="col-md-4" style="text-align: right;">

                <div class="btn-group">
                    <a href="./kontrola?<?= $link_year ?>" style="padding: 5px 11px !important;" class="btn btn-md <?php if (!isset($spec)) {echo 'btn-primary';} else {echo 'btn-white';} ?>">Vše</a>
                    <a href="?spec=36<?= $link_year ?>" style="padding: 5px 11px !important;" class="btn btn-md <?php if (!empty($spec) && $spec == '36') {echo 'btn-primary';} else {echo 'btn-white';} ?>">Ekozone</a>
                    <a href="?spec=3<?= $link_year ?>" style="padding: 5px 11px !important;" class="btn btn-md <?php if (!empty($spec) && $spec == '3') {echo 'btn-primary';} else {echo 'btn-white';} ?>">MicroSilk</a>
                    <a href="?spec=4<?= $link_year ?>" style="padding: 5px 11px !important;" class="btn btn-md <?php if (!empty($spec) && $spec == '4') {echo 'btn-primary';} else {echo 'btn-white';} ?>">Audio</a>
                </div>
            </div>

        </div>
<!--        <hr>-->

            </div>

<?php


    $perpage = 80;

    if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {$od = 1;}

    $s_lol = $od - 1;
    $s_pocet = $s_lol * $perpage;


    $demands_max_query = $mysqli->query("SELECT 
         d.*, d.id as demand_id, ship.* 
    FROM (demands d, demands_specs_bridge b) 
        LEFT JOIN addresses_shipping ship ON ship.id = d.shipping_id 
        LEFT JOIN addresses_billing bill ON bill.id = d.billing_id 
    WHERE b.client_id = d.id $query 
        AND (d.status = 5 OR d.status = 8 OR d.status = 13) 
        AND d.customer = 1 
        AND d.role = 'client' 
        AND d.realization <= DATE_SUB(NOW(), INTERVAL 365 DAY)
    GROUP BY d.id") or die($mysqli->error);
    $max = mysqli_num_rows($demands_max_query);


    $all_demands_query = $mysqli->query("
        SELECT 
               d.*, d.id as demand_id, ship.*, bill.* 
        FROM (demands d, demands_specs_bridge b) 
            LEFT JOIN addresses_shipping ship ON ship.id = d.shipping_id 
            LEFT JOIN addresses_billing bill ON bill.id = d.billing_id 
        WHERE 
              b.client_id = d.id $query 
              AND (d.status = 5 OR d.status = 8 OR d.status = 13) 
              AND d.customer = 1 
              AND d.role = 'client' 
              AND d.realization <= DATE_SUB(NOW(), INTERVAL 365 DAY)
        GROUP BY d.id 
        ORDER BY d.realization DESC 
        LIMIT " . $s_pocet . "," . $perpage) or die($mysqli->error);


    $pocet_prispevku = $max;



    $initialData = [];
    while ($allclients = mysqli_fetch_array($demands_max_query)) {

        $address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $allclients['shipping_id'] . '" WHERE b.id = "' . $allclients['billing_id'] . '"') or die($mysqli->error);
        $address = mysqli_fetch_assoc($address_query);

        $location = calendar_location($address);

        $realLocation = $mysqli->real_escape_string($location);
        $realUsername = $mysqli->real_escape_string($allclients['user_name']);
        $realProdukt = $mysqli->real_escape_string(returnpn($allclients['customer'], $allclients['product']));



            if(($allclients['latitude'] == '1.0000000' && $allclients['longitude'] == '1.0000000') || ($allclients['latitude'] == '0.0000000' && $allclients['longitude'] == '0.0000000')){

                $_REQUEST['id'] = $allclients['demand_id'];

                require CONTROLLERS . '/lati-longi.php';

                if(!empty($lat)){
                $allclients['latitude'] = $lat;
                $allclients['longitude'] = $long;
                }

            }

        if ($allclients['latitude'] != "" && $allclients['longitude'] != "") {


            $currentData = [
                'lat' => $allclients['latitude'],
                'lng' => $allclients['longitude'],
                'description' => $realUsername,
                'id' => $allclients['demand_id'],
                'product' => $realProdukt,
                'address' => $realLocation,

            ];

            array_push($initialData, $currentData);

        }
    }


//    SELECT d.id, d.realization, d.email, d.phone, d.product, ship.*, bill.* FROM (demands d, demands_specs_bridge b) LEFT JOIN addresses_shipping ship ON ship.id = d.shipping_id LEFT JOIN addresses_billing bill ON bill.id = d.billing_id WHERE b.client_id = d.id AND b.specs_id = 36 AND b.value = 'Ekozone' AND (d.status = 5 OR d.status = 8 OR d.status = 13)



if (mysqli_num_rows($all_demands_query) > 0) {

    ?>


    <div id="map" class="map-checkin" style=" background: #f0f0f0;height: 580px; width: 100%;"></div>

    <script type="text/javascript">
        function showmap() {
            function initMap() {
                //var center = new google.maps.LatLng(37.4419, -122.1419);
                var center = new google.maps.LatLng(49.741753, 15.335080);

                var map = new google.maps.Map(document.getElementById('map'), {
                    zoom: 7,
                    center: center,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                });

                var markers = [];

                var data = <?php echo json_encode($initialData)?>;

                var infowindow = new google.maps.InfoWindow()
                for (var x in data) {
                    var description = data[x].description;
                    var id = data[x].id;
                    var address = data[x].address;
                    var product = data[x].product;
                    var latLng = new google.maps.LatLng(
                        data[x].lat,
                        data[x].lng);

                    var content = '<a href="../demands/zobrazit-poptavku?id='+id+'" target="_blank">'+description+' &raquo;</a><br>'+
                        '<i>'+address+'</i><br><strong>~ '+product+'</strong>';

                    var marker = new google.maps.Marker({
                        animation: google.maps.Animation.DROP,
                        position: latLng,
                        map: map,
                    });

                    google.maps.event.addListener(marker,'click', (function(marker,content,infowindow){

                        return function() {

                            infowindow.setContent(content);
                            infowindow.open(map,marker);
                        };
                    })(marker,content,infowindow));



                    markers.push(marker);





                }

                var markerCluster = new MarkerClusterer(map, markers,
                    {imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'});


            }
            initMap();
        }

        $()


        $(document).ready(function() {

            $('.show_confirmation').click(function() {

                $(this).next('.confirmation').show();
                $(this).hide();
            });

        });

    </script>


    <table class="table table-bordered">
        <thead>
        <tr>
            <th style="vertical-align: middle;text-align: center;">Jméno</th>
            <th style="vertical-align: middle;text-align: center;">Datum realizace</th>
            <th style="vertical-align: middle;text-align: center;">Sklíčko</th>
            <th style="vertical-align: middle;text-align: center;">Circukalční cesta</th>
            <th style="vertical-align: middle;text-align: center;">Ekozone</th>
            <th style="vertical-align: middle;text-align: center;">MicroSilk</th>
            <th style="vertical-align: middle;text-align: center;">Audio</th>
            <th style="vertical-align: middle;text-align: center;">Adresa</th>
            <th style="vertical-align: middle;text-align: center;">Akce</th>
        </tr>
        </thead>
        <tbody>
    <?php



    function inspection($id, $type){

        global $mysqli;

        $check_query = $mysqli->query("SELECT * FROM demands_inspections WHERE demand_id = '".$id."' AND type = '".$type."' ORDER BY id DESC LIMIT 1")or die($mysqli->error);

        if(mysqli_num_rows($check_query) > 0){

            $check = mysqli_fetch_assoc($check_query);
            $status = '<span class="text-success">kontrola proběhla: <strong>'.date_formatted($check['date']).'</strong></span><br><a href="?action=cancel_inspection&id='.$id.'&type='.$type.'" class="text-danger"><i class="entypo-cancel"></i> Zrušit</a>';

        }else{

            $status = '<span class="text-danger">kontrola neproběhla</span>';

        }

        return $status;

    }

    while ($demand = mysqli_fetch_assoc($all_demands_query)) {


        $address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $demand['shipping_id'] . '" WHERE b.id = "' . $demand['billing_id'] . '"') or die($mysqli->error);
        $address = mysqli_fetch_assoc($address_query);

        $location = calendar_location($address);

        ?>
        <tr>
            <td><a href="../demands/zobrazit-poptavku?id=<?= $demand['demand_id'] ?>" target="_blank"><?= $demand['user_name'] ?></a></td>

            <td style="text-align: center;"><?= date_formatted($demand['realization']) ?></td>

            <td style="text-align: center;"><?= inspection($demand['demand_id'], 'glass') ?></td>

            <td style="text-align: center;"><?= inspection($demand['demand_id'], 'circulation_path') ?></td>

            <?php

            // ekozone
        $ekozone_check = $mysqli->query("SELECT * FROM demands_specs_bridge WHERE client_id = '".$demand['demand_id']."' AND specs_id = 36")or die($mysqli->error);
        $ekozone = mysqli_fetch_assoc($ekozone_check);

        ?><td style="text-align: center;"><?php

                if($ekozone['value'] == 'Ekozone'){

                    echo inspection($demand['demand_id'], 'ekozone');
                    echo '<br>';
                    echo $ekozone['value'];

                }else{
                    echo '-';
                }
                ?></td><?php

            // microsilk
        $microsilk_check = $mysqli->query("SELECT * FROM demands_specs_bridge WHERE client_id = '".$demand['demand_id']."' AND specs_id = 3")or die($mysqli->error);
        $microsilk = mysqli_fetch_assoc($microsilk_check);

            ?><td style="text-align: center;"><?php

                if($microsilk['value'] == 'Ano'){

                    echo inspection($demand['demand_id'], 'microsilk');
                    echo '<br>';
                    echo $microsilk['value'];

                }else{
                    echo '-';
                }


                ?></td><?php

            // audio
        $audio_check = $mysqli->query("SELECT * FROM demands_specs_bridge WHERE client_id = '".$demand['demand_id']."' AND specs_id = 4")or die($mysqli->error);
        $audio = mysqli_fetch_assoc($audio_check);

            ?><td style="text-align: center;"><?php

                if($audio['value'] == 'Ano'){

                    echo inspection($demand['demand_id'], 'audio');
                    echo '<br>';
                    echo $audio['value'];

                }else{
                    echo '-';
                }

                ?></td><?php

            ?><td><?= $location ?></td>

            <td>


                    <a class="show_confirmation btn btn-primary btn-icon icon-left btn-sm">
                        <i class="entypo-check"></i>
                        Kontrola
                    </a>

                    <form action="?action=inspection&id=<?= $demand['demand_id'] ?>" method="post" class="confirmation" style="display: none" role="form" >

                        <div class="form-group">
                            <label><input type="checkbox" name="type[glass]" value="1"> Sklíčko</label>
                        </div>

                        <div class="form-group">
                            <label><input type="checkbox" name="type[circulation_path]" value="1"> Cirkulační cesta</label>
                        </div>

                        <?php  if($ekozone['value'] == 'Ekozone'){ ?>

                        <div class="form-group">
                            <label><input type="checkbox" name="type[ekozone]" value="1"> Ekozone</label>
                        </div>
                        <?php } ?>


                        <?php  if($microsilk['value'] == 'Ano'){ ?>
                        <div class="form-group">
                            <label><input type="checkbox" name="type[microsilk]" value="1"> MicroSilk</label>
                        </div>
                       <?php } ?>

                        <?php  if($audio['value'] == 'Ano'){ ?>
                        <div class="form-group">
                            <label><input type="checkbox" name="type[audio]" value="1"> Audio</label>
                        </div>
                        <?php } ?>


                        <div class="form-group">
                            <button type="submit" class="btn btn-success">Uložit</button>
                        </div>
                    </form>

                </td>

        </tr>
        <?php

    }


    ?>

    </tbody>
</table>


<?php


} else { ?>
    <ul class="cbp_tmtimeline" style=" margin-left: 25px;">
        <li style="margin-top: 80px;">

            <div class="cbp_tmicon">
                <i class="entypo-block" style="line-height: 42px !important;"></i>
            </div>

            <div class="cbp_tmlabel empty" style="padding-top: 9px;">
                        <span><a style="font-weight: bold; margin-left: -12px;font-size: 17px;">Bohužel tomuto filtru
                                neodpovídá žádná vířivka.</a></span>
            </div>
        </li>
    </ul>
<?php } ?>




<div class="row">
    <div class="col-md-12">
        <center>
            <ul class="pagination pagination-sm">
                <?php

                include VIEW . "/default/pagination.php"; ?>
            </ul>

            <h3>Celkem: <?= $max ?></h3>
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

        echo 'PHP '.PHP_VERSION.' | Page generated in ' . $total_time . ' seconds.'; ?></span>

</footer>
</div>



</div>



<script type="text/javascript">
    $(document).ready(function() {
        $(".toggle-modal-remove").click(function(e) {

            $('#remove-modal').removeData('bs.modal');
            e.preventDefault();


            var type = $(this).data("type");

            var id = $(this).data("id");

            $("#remove-modal").modal({

                remote: '/admin/controllers/modals/modal-remove.php?id=' + id + '&type=' +
                    type + '&od=<?php if (isset($od)) {echo $od;} ?>',
            });
        });
    });
</script>


<div class="modal fade" id="remove-modal" aria-hidden="true" style="display: none; margin-top: 10%;">

</div>

<?php include VIEW . '/default/footer.php'; ?>

