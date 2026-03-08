<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";

include INCLUDES . "/googlelogin.php";
include INCLUDES . "/functions.php";

if (isset($_REQUEST['od'])) {$od = $_REQUEST['od'];}

$pagetitle = "Garanční prohlídky";

if (isset($_REQUEST['year'])) { $year = $_REQUEST['year']; }

$link_year = '';
$query = '';
if(!empty($year)){

    $query .= ' AND YEAR(realization) = '.$year;
    $link_year .= '&year='.$year;

}


function warranty_inspection($id){

    global $mysqli;

    $check_query = $mysqli->query("
            SELECT s.date
                FROM demands d, services s
                WHERE
                    s.clientid = d.id AND
                    s.category = 'garancni-prohlidka' AND
                    d.id = '".$id."'
            ORDER BY s.date DESC
            LIMIT 1
            ")or die($mysqli->error);

    if(mysqli_num_rows($check_query) > 0){

        $check = mysqli_fetch_assoc($check_query);


        if(date("Y-m-d", strtotime($check['date'])) > date("Y-m-d")){

            $status = '<span class="text-info">prohlídka je naplánována na: <strong>'.date_formatted($check['date']).'</strong></span>';

        }else{

            $status = '<span class="text-warning">prohlídka proběhla: <strong>'.date_formatted($check['date']).'</strong></span>';

        }


    }else{

        $status = '<span class="text-danger">prohlídka nikdy neproběhla</span>';

    }

    return $status;

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
</div>

<div class="col-md-12 well"
     style="border-color: #ebebeb; background-color: #fbfbfb; padding: 6px; margin-bottom: 12px;">
    <div class="row">
        <div class="col-md-12" style="text-align: left;">

            <div class="btn-group">

                <a href="./garancni-prohlidky" style="padding: 5px 11px !important;" class="btn btn-md <?php if (!isset($year)) {echo 'btn-primary';} else {echo 'btn-white';} ?>">Vše</a>

                <?php
                $range = range('2013', date('Y'));
                foreach($range as $yearLoop){ ?>
                    <a href="?year=<?= $yearLoop ?>">
                        <label class="btn btn-sm <?php if (!empty($year) && $year == $yearLoop) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                            <?= $yearLoop ?>
                        </label>
                    </a>
                <?php } ?>
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

$demands_max_query = $mysqli->query("
SELECT 
       d.*, d.id as demand_id, ship.* 
FROM demands d
    LEFT JOIN addresses_shipping ship ON ship.id = d.shipping_id 
    LEFT JOIN addresses_billing bill ON bill.id = d.billing_id 
WHERE d.id NOT IN(
        SELECT d.id
    FROM demands d, services s
    WHERE
        s.clientid = d.id AND
        s.category = 'garancni-prohlidka' AND
        s.date BETWEEN DATE_SUB(NOW(), INTERVAL 365 DAY) AND NOW() AND
        d.realization <= DATE_SUB(NOW(), INTERVAL 365 DAY)
    )
    AND customer = 1 AND status = 5
    AND realization <= DATE_SUB(NOW(), INTERVAL 365 DAY)
    $query
GROUP BY d.id
") or die($mysqli->error);
$max = mysqli_num_rows($demands_max_query);



$all_demands_query = $mysqli->query("
 SELECT 
       d.*, d.id as demand_id, ship.* 
FROM demands d
    LEFT JOIN addresses_shipping ship ON ship.id = d.shipping_id 
    LEFT JOIN addresses_billing bill ON bill.id = d.billing_id 
WHERE d.id NOT IN(
        SELECT d.id
    FROM demands d, services s
    WHERE
        s.clientid = d.id AND
        s.category = 'garancni-prohlidka' AND
        s.date BETWEEN DATE_SUB(NOW(), INTERVAL 365 DAY) AND NOW() 
    )
    AND customer = 1 AND status = 5
    AND realization <= DATE_SUB(NOW(), INTERVAL 365 DAY)
    $query
GROUP BY d.id ORDER BY d.realization DESC limit " . $s_pocet . "," . $perpage) or die($mysqli->error);


$pocet_prispevku = $max;



$initialData = [];
$clients = [];
while ($allclients = mysqli_fetch_array($all_demands_query)) {

    $address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $allclients['shipping_id'] . '" WHERE b.id = "' . $allclients['billing_id'] . '"') or die($mysqli->error);
    $address = mysqli_fetch_assoc($address_query);

    $location = calendar_location($address);

    $realLocation = $mysqli->real_escape_string($location);
    $realUsername = $mysqli->real_escape_string($allclients['user_name']);
    $realProdukt = $mysqli->real_escape_string(returnpn($allclients['customer'], $allclients['product']));

    $addClient = [
        'demand_id' => $allclients['demand_id'],
        'user_name' => $allclients['user_name'],
        'phone' => phone_prefix($allclients['phone_prefix']).$allclients['phone'],
        'realization_date' => date_formatted($allclients['realization']),
        'warranty_inspection' => warranty_inspection($allclients['demand_id']),
        'address' => $realLocation,


    ];

    $clients[] = $addClient;

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

        $initialData[] = $currentData;

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
            <th style="vertical-align: middle;text-align: center;">Telefonní číslo</th>
            <th style="vertical-align: middle;text-align: center;">Datum realizace</th>
            <th style="vertical-align: middle;text-align: center;">Poslední garanční prohlídka</th>
            <th style="vertical-align: middle;text-align: center;">Adresa</th>
            <th style="vertical-align: middle;text-align: center;">Akce</th>
        </tr>
        </thead>
        <tbody>
        <?php


        foreach ($clients as $demand) {

            ?>
            <tr>
                <td><a href="../demands/zobrazit-poptavku?id=<?= $demand['demand_id'] ?>" target="_blank"><?= $demand['user_name'] ?></a></td>

                <td style="text-align: center;"><?= $demand['phone'] ?></td>

                <td style="text-align: center;"><?= $demand['realization_date'] ?></td>

                <td style="text-align: center;"><?= $demand['warranty_inspection'] ?></td>

                <td><?= $demand['address'] ?></td>

                <td>

                    <a href="/admin/pages/services/pridat-servis?service=new&client=<?= $demand['demand_id'] ?>&category=garancni-prohlidka"
                       class="btn btn-success btn-icon icon-left btn-sm" target="_blank">
                        <i class="entypo-check"></i>
                        Naplánovat prohlídku
                    </a>

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

