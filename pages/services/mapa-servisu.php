<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

if (isset($_REQUEST['state'])) { $state = $_REQUEST['state']; }
if (isset($_REQUEST['category'])) { $category = $_REQUEST['category']; }
$pagetitle = "Mapa servisů";

$query = '';
if(!empty($state)){ $query .= ' WHERE state = "'.$state.'"'; }
if(!empty($category)){

    if(!empty($state)){ $query .= ' AND '; }else{
        $query .= ' WHERE ';
    }
    $query .= ' s.category = "'.$category.'"';

}

$allclientquery = $mysqli->query("SELECT 
       s.*, d.user_name, d.latitude, d.longitude, d.customer, d.product 
    FROM services s 
        LEFT JOIN demands d ON d.id = s.clientid $query") or die($mysqli->error);

include VIEW . '/default/header.php';

$states = array('new' => 'Nový', 'waiting' => 'Čeká na díly', 'unconfirmed' => 'Nepotvrzený', 'confirmed' => 'Potvrzený', 'executed' => 'Provedený', 'unfinished' => 'Nedokončený', 'invoiced' => 'Fakturované', 'problematic' => 'Problémové', 'warranty' => 'Reklamace', 'finished' => 'Hotový', 'canceled' => 'Stornovaný');

$categories = [];
$catq = $mysqli->query('SELECT * FROM services_categories ORDER BY title') or die($mysqli->error);
while ($cat = mysqli_fetch_assoc($catq)) {

    $oneCategory = [];
    $oneCategory['name'] = $cat['title'];
    $oneCategory['slug'] = $cat['seoslug'];

    $categories[] = $oneCategory;

}

$categoryLink = '';
$stateLink = '';

if (isset($_REQUEST['category']) && $_REQUEST['category'] != "") {

    $categoryLink .= '&category=' . $_REQUEST['category'];

}

if (isset($_REQUEST['state']) && $_REQUEST['state'] != "") {

    $stateLink .= '&state=' . $_REQUEST['state'];

}


?>

<script type="text/javascript">
    jQuery(document).ready(function($)
    {
        setTimeout(function(){
            showmap();
        }, 540);
    });
</script>

<script type="text/javascript" src="//maps.google.com/maps/api/js?key=AIzaSyDRermPdr7opDFLqmrcOuK5L4zC2_U8XGk"></script>

        <div class="row">
            <div class="col-md-8 col-sm-7">
                <h2 style="  margin-top: 0px; margin-bottom: 22px;">Mapa servisů</h2>
            </div>

        </div>

    <div class="btn-group" style="text-align: left;">
        <a href="../services/mapa-servisu?<?= $categoryLink ?>">
            <label class="btn btn-sm <?php if (!isset($state)) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                Vše
            </label>
        </a>

        <?php
        foreach ($states as $singleStatus => $value) {
            ?>
            <a href="?state=<?= $singleStatus ?><?= $categoryLink ?>">
                <label class="btn btn-sm <?php if (isset($state) && $state === $singleStatus) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                    <?= $value ?>
                </label>
            </a>
        <?php
            }
        ?>
    </div>

    <hr>

    <div class="btn-group" style="text-align: left;">
        <a href="../services/mapa-servisu?<?= $stateLink ?>">
            <label class="btn btn-sm <?php if (!isset($category)) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                Vše
            </label>
        </a>

        <?php
        foreach ($categories as $singleCategory) {
            ?>
            <a href="?category=<?= $singleCategory['slug'] ?><?= $stateLink ?>">
                <label class="btn btn-sm <?php if (isset($category) && $category === $singleCategory['slug']) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                    <?= $singleCategory['name'] ?>
                </label>
            </a>
            <?php
        }
        ?>
    </div>

    <hr>


        <?php


        $initialData = [];
        while ($allclients = mysqli_fetch_array($allclientquery)) {

            $address_query = $mysqli->query("SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = '" . $allclients['shipping_id'] . "' WHERE b.id = '" . $allclients['billing_id'] . "'") or die($mysqli->error);
            $address = mysqli_fetch_assoc($address_query);

            $location = calendar_location($address);
            $realLocation = $mysqli->real_escape_string($location);

            $realUsername = $mysqli->real_escape_string($allclients['user_name']);
            $realProdukt = $mysqli->real_escape_string(returnpn($allclients['customer'], $allclients['product']));


            $state = $states[$allclients['state']] ?? null;

            if ($allclients['latitude'] != "" && $allclients['longitude'] != "") {

                $currentData = [
                    'lat' => $allclients['latitude'],
                    'lng' => $allclients['longitude'],
                    'description' => $realUsername.' &raquo; '.$state,
                    'id' => $allclients['id'],
                    'product' => $realProdukt,
                    'address' => $realLocation,

                ];

                array_push($initialData, $currentData);

            }
        }


        ?>


        <div id="map" class="map-checkin" style="background: #f0f0f0;height: 580px; width: 100%;"></div>

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

                        var content = '<a href="./zobrazit-servis?id='+id+'" target="_blank">'+description+'</a><br>'+
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


                }
                initMap();
            }
        </script><!-- Footer -->
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

