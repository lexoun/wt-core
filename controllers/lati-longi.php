<?php

include_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";

if (!isset($_REQUEST['type']) || $_REQUEST['type'] != 'batch') {


    $demands_query = $mysqli->query("SELECT latitude, id, shipping_id, billing_id FROM demands WHERE status > '3' AND id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

    $demand = mysqli_fetch_array($demands_query);

    $address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $demand['shipping_id'] . '" WHERE b.id = "' . $demand['billing_id'] . '"') or die($mysqli->error);
    $address = mysqli_fetch_assoc($address_query);

    if (isset($address['shipping_street']) && $address['shipping_street'] != '' && isset($address['shipping_city']) && $address['shipping_city'] != '' && isset($address['shipping_zipcode']) && $address['shipping_zipcode'] != 0) {

        $combinedAddress = $address['shipping_city'] . ' ' . $address['shipping_zipcode'] . ' ' . $address['shipping_street'] . ' ' . $address['shipping_country'];

    } else {

        $combinedAddress = $address['billing_city'] . ' ' . $address['billing_zipcode'] . ' ' . $address['billing_street'] . ' ' . $address['billing_country'];

    }

    if(!empty($combinedAddress) && trim($combinedAddress) !== ''){

        $prepAddr = str_replace(' ', '+', $combinedAddress);

        $geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . $prepAddr . '&sensor=false&key=AIzaSyDWsYJWdJpuS_SgJ_0bpi0uOOGAGPBWsgk');
        $output = json_decode($geocode);

        if (isset($output) && $output->status != 'ZERO_RESULTS') {

            $latitude = $output->results[0]->geometry->location->lat;
            $longitude = $output->results[0]->geometry->location->lng;
            $lat = str_replace(',', '.', $latitude);
            $long = str_replace(',', '.', $longitude);

            if (($demand['latitude'] == '0.000000' && isset($latitude) && $latitude != "") || (isset($lat) && $demand['latitude'] != $lat  && $lat != "")) {

                $mysqli->query("UPDATE demands SET latitude = '$lat', longitude = '$long' WHERE id = '" . $demand['id'] . "'") or die($mysqli->error);

                $getclient['latitude'] = $lat;
                $getclient['longitude'] = $long;

            }

        }

    }


} else {



    $number = 0;

    $demands_query = $mysqli->query("SELECT latitude, id, shipping_id, billing_id FROM demands WHERE status > '3' AND latitude = '0.000000' ORDER BY RAND() LIMIT 20") or die($mysqli->error);

    while ($demand = mysqli_fetch_array($demands_query)) {

            $address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $demand['shipping_id'] . '" WHERE b.id = "' . $demand['billing_id'] . '"') or die($mysqli->error);
            $address = mysqli_fetch_assoc($address_query);

            if (isset($address['shipping_street']) && $address['shipping_street'] != '' && isset($address['shipping_city']) && $address['shipping_city'] != '' && isset($address['shipping_zipcode']) && $address['shipping_zipcode'] != 0) {

                $combinedAddress = $address['shipping_city'] . ' ' . $address['shipping_zipcode'] . ' ' . $address['shipping_street'] . ' ' . $address['shipping_country'];

            } else {

                $combinedAddress = $address['billing_city'] . ' ' . $address['billing_zipcode'] . ' ' . $address['billing_street'] . ' ' . $address['billing_country'];

            }

            if(!empty($combinedAddress) && trim($combinedAddress) !== ''){

                $prepAddr = str_replace(' ', '+', $combinedAddress);
                $geocode = file_get_contents('https://maps.google.com/maps/api/geocode/json?address=' . $prepAddr . '&sensor=false&key=AIzaSyDWsYJWdJpuS_SgJ_0bpi0uOOGAGPBWsgk');
                $output = json_decode($geocode);

                if(isset($output) && $output->status != 'ZERO_RESULTS'){

                    $latitude = $output->results[0]->geometry->location->lat;
                    $longitude = $output->results[0]->geometry->location->lng;
                    $lat = str_replace(',', '.', $latitude);
                    $long = str_replace(',', '.', $longitude);

                    if (($demand['latitude'] == '0.000000' and $latitude != "") || ($demand['latitude'] != $lat && isset($lat) && $lat != "")) {

                        $mysqli->query("UPDATE demands SET latitude = '$lat', longitude = '$long' WHERE id = '" . $demand['id'] . "'") or die($mysqli->error);

                    }

                }
                $number++;
            }

            echo $combinedAddress.' - '.$demand['latitude'].' - '.$lat;
            echo $demand['id'] . '<br>';
    }

    echo $number;


}
