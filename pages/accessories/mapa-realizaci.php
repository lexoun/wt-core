<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

if (isset($_REQUEST['customer'])) {$customer = $_REQUEST['customer'];}

$id = $_REQUEST['id'];
$getclientquery = $mysqli->query('SELECT *, DATE_FORMAT(date, "%d. %m. %Y") as dateformated, DATE_FORMAT(realization, "%d. %m. %Y") as realizationformated FROM demands WHERE id="1"') or die($mysqli->error);
$getclient = mysqli_fetch_assoc($getclientquery);

$pagetitle = "Mapa realizací";

$query = "";
$currentpage = "statistika-prodeju";
$allow_sites = "";

if (isset($customer) && $customer != "" && $customer != "all") {

    if ($customer == 1) {

        $query = ' AND (customer = 1 OR customer = 3)';

    } elseif ($customer == 0) {

        $query = ' AND customer = 0';

    }

}

if ($_REQUEST['start_date'] != "") {

    $query = $query . ' AND realization >= "' . $_REQUEST['start_date'] . '"';

    $string_start = '&start_date=' . $_REQUEST['start_date'];

}

if ($_REQUEST['end_date'] != "") {

    $query = $query . ' AND realization <= "' . $_REQUEST['end_date'] . '"';

    $string_end = '&end_date=' . $_REQUEST['end_date'];

}

$allclientquery = $mysqli->query("SELECT *, DATE_FORMAT(realization, '%d. %m. %Y') as realization FROM demands WHERE status = '4' $query order by id asc") or die($mysqli->error);

if (isset($customer) && $customer == 0) {

    $query_double = ' AND customer = 3';

    $query_date = '';

    if (isset($_REQUEST['start_date']) || isset($_REQUEST['end_date'])) {

        if ($_REQUEST['start_date'] != "") {

            $query_date = $query_date . ' AND startdate >= "' . $_REQUEST['start_date'] . '"';

        }

        if ($_REQUEST['end_date'] != "") {

            $query_date = $query_date . ' AND startdate <= "' . $_REQUEST['end_date'] . '"';

        }

        $second_product_query = $mysqli->query("SELECT DATE_FORMAT(startdate, '%d. %m. %Y') as startdate FROM demands_double_realization WHERE startdate != '0000-00-00' $query_date") or die($mysqli->error);

    } else {

        $second_product_query = $mysqli->query("SELECT * FROM demands WHERE status = '4' $query_double order by id asc") or die($mysqli->error);

    }

}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "edit") {
    $displaysuccess = true;
    $successhlaska = "Klient byl úspěšně upraven.";
}

include VIEW . '/default/header.php';

?>

<script type="text/javascript">
jQuery(document).ready(function($)
{

$('#servis').click(function() {
 $( "#servis" ).addClass( "active" );
 $( "#accessories" ).removeClass( "active" );
 $( "#documents" ).removeClass( "active" );

 $( "#servistab" ).show( "slow" );
 $( "#accessoriestab" ).hide( "slow" );
 $( "#documentstab" ).hide( "slow" );
});
$('#accessories').click(function() {
 $( "#servis" ).removeClass( "active" );
 $( "#accessories" ).addClass( "active" );
 $( "#documents" ).removeClass( "active" );

 $( "#servistab" ).hide( "slow" );
 $( "#accessoriestab" ).show( "slow" );
 $( "#documentstab" ).hide( "slow" );
});
$('#documents').click(function() {
 $( "#servis" ).removeClass( "active" );
 $( "#accessories" ).removeClass( "active" );
 $( "#documents" ).addClass( "active" );

 $( "#servistab" ).hide( "slow" );
 $( "#accessoriestab" ).hide( "slow" );
 $( "#documentstab" ).show( "slow" );
});


$('#addli').click(function() {
if($("#addstrong").is(":visible")){
 $( "#addstrong" ).hide( "slow" );
 $( "#addtext" ).show( "slow" );
$("#adda").animate({'padding': "10px"
  });
$("#addli").animate({width: "300px"
  });
}
});

setTimeout(function(){

showmap();
    }, 540);


});


</script>

<script type="text/javascript" src="//maps.google.com/maps/api/js?sensor=false&key=AIzaSyDRermPdr7opDFLqmrcOuK5L4zC2_U8XGk&sensor=false"></script>
   <script>
      if (document.location.search.indexOf('compiled') !== -1) {
        script += '_compiled';
      }
      script += '.js"><' + '/script>';
      document.write(script);
    </script>

<div class="row">
  <div class="col-md-8 col-sm-7">
    <h2><?= $pagetitle ?></h2>
  </div>

</div>


<!-- Pager for search results --><div class="col-md-12 well" style="border-color: #ebebeb; margin-top: 8px;background-color: #fbfbfb;">


  <div class="btn-group col-sm-7" style="text-align: left; padding: 0;">

            <a href="?customer=all<?php echo $string_start;
echo $string_end; ?>"><label class="btn btn-lg <?php if ($customer == "all" || $customer == "") { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
              Vše
            </label></a>
            <a href="?customer=1<?php echo $string_start;
echo $string_end; ?>"><label class="btn btn-lg <?php if ($customer == "1") { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
              Vířivky
            </label></a>

            <a href="?customer=0<?php echo $string_start;
echo $string_end; ?>"><label class="btn btn-lg <?php if ($customer == "0") { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
              Sauny
            </label></a>





          </div>

<form role="form" method="get" class="form-horizontal form-groups-bordered validate" action="mapa-realizaci" enctype="multipart/form-data" novalidate="novalidate">

  <div class="form-group col-sm-5" style=" padding: 0; margin: 0;">
              <input name="customer" style="display: none;" value="<?= $customer ?>">

              <input id="datum1pridatsem" type="text" class="form-control datepicker" name="start_date" data-format="yyyy-mm-dd" placeholder="Počáteční datum" style="height: 41px; width: 140px; margin-right: 10px; float: left;" value="<?= $_REQUEST['start_date'] ?>">

              <input id="datum1pridatsem" type="text" class="form-control datepicker" name="end_date" data-format="yyyy-mm-dd" placeholder="Konečné datum" style="height: 41px; width: 140px; margin-right: 10px; float: left;" value="<?= $_REQUEST['end_date'] ?>">

<button type="submit" style="padding: 10px 18px 10px 50px; height: 36px;" class="btn btn-blue btn-icon icon-left">
                Načíst
                <i class="fa fa-download" style="     padding: 10px 12px;"></i>
                  </button>
                </div>

            </form>


<div class="clear"></div>
</div><!-- Footer -->



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

 var macDoList = [

     <?php
$n = 0;
while ($allclients = mysqli_fetch_array($allclientquery)) {

    if ($allclients['latitude'] != "" && $allclients['longitude'] != "") {
        echo '{lat:' . $allclients['latitude'] . ',lng:' . $allclients['longitude'] . ',realization: "' . $allclients['realization'] . '",description: "' . $allclients['name'] . ' ' . $allclients['surname'] . '", ajdee: "' . $allclients['id'] . '", produktus: "' . returnpn($allclients['customer'], $allclients['product']) . '", adresour: "' . $allclients['city'] . ', ' . $allclients['street'] . '"},';
        $n = $n + 1;
    }
}

if (isset($second_product_query)) {
    while ($allclients = mysqli_fetch_array($second_product_query)) {

        $realdate = $allclients['startdate'];

        if (isset($_REQUEST['start_date']) || isset($_REQUEST['end_date'])) {

            $second_date_query = $mysqli->query("SELECT * FROM demands WHERE status = '4' AND id = '" . $allclients['demand_id'] . "' order by id asc") or die($mysqli->error);

            $allclients = mysqli_fetch_array($second_date_query);

            if ($allclients['status'] != 4) {

                continue;

            }

        } else {

            $second_date_query = $mysqli->query("SELECT DATE_FORMAT(startdate, '%d. %m. %Y') as startdate FROM demands_double_realization WHERE demand_id = '" . $allclients['id'] . "'") or die($mysqli->error);

            if (mysqli_num_rows($second_date_query) == 1) {
                $get_date = mysqli_fetch_array($second_date_query);

                $realdate = $get_date['startdate'];

            } else {

                $realdate = '00. 00. 0000';

            }

        }

        if ($allclients['latitude'] != "" && $allclients['longitude'] != "") {
            echo '{lat:' . $allclients['latitude'] . ',lng:' . $allclients['longitude'] . ',realization: "' . $realdate . '",description: "' . $allclients['name'] . ' ' . $allclients['surname'] . '", ajdee: "' . $allclients['id'] . '", produktus: "' . returnpn($allclients['customer'], $allclients['secondproduct']) . '", adresour: "' . $allclients['city'] . ', ' . $allclients['street'] . '"},';
            $n = $n + 1;
        }
    }

}
?>];

      var infowindow = new google.maps.InfoWindow()
    for(var i=0;i<<?= $n ?>	;i++){
        console.log(macDoList[i].lat)
        var descriptor = macDoList[i].description;
        var realization = macDoList[i].realization;
        var idus = macDoList[i].ajdee;
        var adrus = macDoList[i].adresour;
         var produkter = macDoList[i].produktus;
        var latLng = new google.maps.LatLng(
                            macDoList[i].lat,
                            macDoList[i].lng);



        var content = '<a href="./zobrazit-poptavku?id='+idus+'" target="_blank">'+descriptor+' &raquo;</a><br><strong>'+realization+'</strong><br>'+
        '<i>'+adrus+'</i><br><strong>~ '+produkter+'</strong>';







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

    var markerCluster = new MarkerClusterer(map, markers);

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

</footer> </div>



  </div>

<?php include VIEW . '/default/footer.php'; ?>

