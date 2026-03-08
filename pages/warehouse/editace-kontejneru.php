<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
    ob_start('ob_gzhandler');
} else {
    ob_start();
}

if(!empty($_REQUEST['brand']) && $_REQUEST['brand'] === 'Swim SPA'){
    Header("Location:https://www.wellnesstrade.cz/admin/pages/warehouse/editace-kontejneru?brand=Lovia");
    exit;
}


include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/googlelogin.php";
include INCLUDES . "/functions.php";


if(!empty($_REQUEST['id'])){

    $get_container = $mysqli->query("SELECT * FROM containers WHERE id = '" . $_REQUEST['id'] . "'");
    $container = mysqli_fetch_array($get_container);

    if(!empty($container['brand'])){
        $_REQUEST['brand'] = $container['brand'];
    }

}

if (isset($_REQUEST['brand'])) { $brand = $_REQUEST['brand']; }else{ $brand = 'IQue'; }

if ($brand == 'IQue') {

    $link_secret = 'IQU_bgewKD';

} elseif ($brand == 'Lovia') {

    $link_secret = 'LOV_qJcUBZ';

} elseif ($brand == 'Quantum') {

    $link_secret = 'QUA_jEjsaI';

}elseif ($brand == 'Pergola') {

    $link_secret = 'PER_JoifaE';

}elseif ($brand == 'Espoo Smart') {

    $link_secret = 'ESP_fjFSoe';

}elseif ($brand == 'Espoo Deluxe') {

    $link_secret = 'ESS_kSpaeKL';

}

// old to delete

/*

 elseif ($brand == 'Espoo Smart') {

        $brand = 'Espoo Smart';
        $link_secret = 'ESP_fjFSoe';

    } elseif ($brand == 'Espoo Deluxe') {
    
        $brand = 'Espoo Deluxe';
        $link_secret = 'ESS_kSpaeKL';
    
    }
    
$all_warehouse = $mysqli->query("SELECT id FROM containers_products ")or die($mysqli->error);

while($all = mysqli_fetch_assoc($all_warehouse)){

    // systém id = 44

    $mysqli->query("INSERT IGNORE INTO containers_products_specs_bridge (client_id, specs_id) VALUES ('".$all['id']."', '44')")or die($mysqli->error);

}


$all_warehouse = $mysqli->query("SELECT id FROM warehouse WHERE customer = 1")or die($mysqli->error);

while($all = mysqli_fetch_assoc($all_warehouse)){

    // systém id = 44

    $mysqli->query("INSERT IGNORE INTO warehouse_specs_bridge (client_id, specs_id) VALUES ('".$all['id']."', '44')")or die($mysqli->error);

}


// old one timers....

$getAllContainerProducts = $mysqli->query("SELECT * FROM containers_products")or die($mysqli->error);

while($containerProduct = mysqli_fetch_assoc($getAllContainerProducts)){

    $mysqli->query("UPDATE warehouse SET purchase_price = '".$containerProduct['purchase_price']."', first_exchange_rate = '".$containerProduct['first_exchange_rate']."', second_exchange_rate = '".$containerProduct['second_exchange_rate']."' WHERE id = '".$containerProduct['warehouse_id']."'")or die($mysqli->error);

}


*/


// old to delete

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "arrival_date") {

    $get_container = $mysqli->query("SELECT * FROM containers WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);
    $container = mysqli_fetch_array($get_container);


    if(!empty($_POST['date_correction'])){ $correction = $_POST['date_correction']; }else{ $correction = 0; }

    $mysqli->query("UPDATE containers SET date_closed = CURRENT_TIMESTAMP(), date_due = '" . $_POST['date'] . "', date_correction = '" . $correction . "', editor_id = '" . $client['id'] . "' WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

    $container_products = $mysqli->query("SELECT * FROM containers_products WHERE container_id = '" . $_REQUEST['id'] . "' ORDER BY id desc") or die($mysqli->error);
    while ($cont_product = mysqli_fetch_array($container_products)) {

        $mysqli->query("UPDATE warehouse SET status = '" . $correction . "', loadingdate = '" . $_POST['date'] . "' WHERE id = '" . $cont_product['warehouse_id'] . "'") or die($mysqli->error);

    }

    $title = 'Naskladnění kontejneru ' . $container['container_name'];


    $select_event_query = $mysqli->query("SELECT gcalendar, id FROM dashboard_texts WHERE container_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

    if (mysqli_num_rows($select_event_query) == 0) {

        $insert_query = $mysqli->query("INSERT INTO dashboard_texts (admin_id, title, container_id, date, enddate) values ('" . $client['id'] . "','$title', '" . $_REQUEST['id'] . "','" . $_POST['date'] . "','" . $_POST['date'] . "')") or die($mysqli->error);

        $id = $mysqli->insert_id;

    } else {

        $get_event = mysqli_fetch_array($select_event_query);

        $mysqli->query("UPDATE dashboard_texts SET date = '" . $_POST['date'] . "', enddate = '" . $_POST['date'] . "', title = '" . $title . "' WHERE id = '" . $get_event['id'] . "'") or die($mysqli->error);

        $id = $get_event['id'];

    }



    $mysqli->query("DELETE FROM mails_recievers WHERE type_id = '$id' AND type = 'event'") or die($mysqli->error);

    if(!empty(($_POST['performer']))){ $performersArray = array_filter($_POST['performer']); }else{ $performersArray[] = ''; }
    if(!empty(($_POST['observer']))){ $observersArray = array_filter($_POST['observer']); }else{ $observersArray[] = ''; }

    if (!empty($performersArray)) {

        recievers($performersArray, $observersArray, 'event', $id);

    }

    saveCalendarEvent($id, 'event');

    header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/editace-kontejneru?brand='.$container['brand'].'&success=arrival_date');
    exit;

}



if (isset($_REQUEST['action']) && $_REQUEST['action'] == "delivery_price") {

    $container_query = $mysqli->query("SELECT brand FROM containers WHERE id = '" . $_REQUEST['id']. "'") or die($mysqli->error);
    $container = mysqli_fetch_array($container_query);

    $_POST['delivery_price'] = preg_replace('/\s+/', '', $_POST['delivery_price']);

    $mysqli->query("UPDATE containers SET 
                      delivery_price = '" . $_POST['delivery_price'] . "', 
                      date_loading = '" . $_POST['date_loading'] . "', 
                      date_lead = '" . $_POST['date_lead'] . "', 
                      container_name = '" . $_POST['container_name'] . "',
                      container_number = '" . $_POST['container_number'] . "', 
                      editor_id = '" . $client['id'] . "' 
                  WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

    header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/editace-kontejneru?brand='.$container['brand'].'&success=delivery_price');
    exit;

}


if (isset($_REQUEST['action']) && $_REQUEST['action'] == "recieve") {

    $container_query = $mysqli->query("SELECT brand FROM containers WHERE id = '" . $_REQUEST['id']. "'") or die($mysqli->error);
    $container = mysqli_fetch_array($container_query);

    $mysqli->query("UPDATE containers SET closed = '3', editor_id = '" . $client['id'] . "', date_received = '" . $_POST['date_received'] . "' WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

    $container_products = $mysqli->query("SELECT * FROM containers_products WHERE container_id = '" . $_REQUEST['id'] . "' ORDER BY id desc") or die($mysqli->error);
    while ($cont_product = mysqli_fetch_array($container_products)) {

        $mysqli->query("UPDATE warehouse SET status = '2' WHERE id = '" . $cont_product['warehouse_id'] . "'") or die($mysqli->error);

    }

    header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/editace-kontejneru?brand='.$container['brand'].'&success=delivery_price');
    exit;

}


if (isset($_REQUEST['action']) && $_REQUEST['action'] == "add_file") {

    $container_query = $mysqli->query("SELECT brand FROM containers WHERE id = '" . $_REQUEST['id']. "'") or die($mysqli->error);
    $container = mysqli_fetch_array($container_query);

    if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/admin/data/containers/" . $link_secret . "/" . $_REQUEST['id'])) {
        mkdir($_SERVER['DOCUMENT_ROOT'] . "/admin/data/containers/" . $link_secret . "/" . $_REQUEST['id'], 0777, true);

    }

    $filename = $_FILES["zmrdus"]["name"];

    $file_ext = substr($filename, strripos($filename, '.'));

    $path = $_SERVER['DOCUMENT_ROOT'] . "/admin/data/containers/" . $link_secret . "/" . $_REQUEST['id'] . "/" . $_REQUEST['type'] . $file_ext;
    move_uploaded_file($_FILES['zmrdus']['tmp_name'], $path);

    $update = $mysqli->query("UPDATE containers SET editor_id = '" . $client['id'] . "' WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

    header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/editace-kontejneru?brand='.$container['brand'].'&success=add_file');
    exit;

}


if (isset($_REQUEST['action']) && $_REQUEST['action'] == "add_file_hottub") {

    $container_query = $mysqli->query("SELECT brand FROM containers WHERE id = '" . $_REQUEST['id']. "'") or die($mysqli->error);
    $container = mysqli_fetch_array($container_query);

    if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/admin/data/containers/" . $link_secret . "/" . $_REQUEST['id'] . "/" . $_REQUEST['hottub_id'])) {
        mkdir($_SERVER['DOCUMENT_ROOT'] . "/admin/data/containers/" . $link_secret . "/" . $_REQUEST['id'] . "/" . $_REQUEST['hottub_id'], 0777, true);

    }

    $filename = $_FILES["fileinput"]["name"];

    $file_ext = substr($filename, strripos($filename, '.'));

    $path = $_SERVER['DOCUMENT_ROOT'] . "/admin/data/containers/" . $link_secret . "/" . $_REQUEST['id'] . "/" . $_REQUEST['hottub_id'] . "/" . $filename;
    move_uploaded_file($_FILES['fileinput']['tmp_name'], $path);

    header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/editace-kontejneru?brand='.$container['brand'].'&success=add_file');
    exit;

}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "remove_file") {

    $container_query = $mysqli->query("SELECT brand FROM containers WHERE id = '" . $_REQUEST['id']. "'") or die($mysqli->error);
    $container = mysqli_fetch_array($container_query);

    $path = $_SERVER['DOCUMENT_ROOT'] . "/admin/data/containers/" . $link_secret . "/" . $_REQUEST['id'] . "/" . $_REQUEST['name'];

    if (file_exists($path)) {

        unlink($path);

    }

    $update = $mysqli->query("UPDATE containers SET editor_id = '" . $client['id'] . "' WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

    header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/editace-kontejneru?brand='.$container['brand'].'&success=remove_file');
    exit;
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "remove_file_hottub") {

    $container_query = $mysqli->query("SELECT brand FROM containers WHERE id = '" . $_REQUEST['id']. "'") or die($mysqli->error);
    $container = mysqli_fetch_array($container_query);

    $path = $_SERVER['DOCUMENT_ROOT'] . "/admin/data/containers/" . $link_secret . "/" . $_REQUEST['id'] . "/" . $_REQUEST['hottub_id'] . "/" . $_REQUEST['name'];

    if (file_exists($path)) {

        unlink($path);

    }

    header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/editace-kontejneru?brand='.$container['brand'].'&success=remove_file');
    exit;
}



if (isset($_REQUEST['action']) && $_REQUEST['action'] == "first_payment") {

    $container_query = $mysqli->query("SELECT brand FROM containers WHERE id = '" . $_REQUEST['id']. "'") or die($mysqli->error);
    $container = mysqli_fetch_array($container_query);

    $total = 0;

    $container_products = $mysqli->query("SELECT * FROM containers_products WHERE container_id = '" . $_REQUEST['id'] . "' ORDER BY id desc") or die($mysqli->error);
    while ($cont_product = mysqli_fetch_array($container_products)) {

        $price = 0;
        $price = $_POST['price-' . $cont_product['id']];
        $total += $price;

        // todo: is this ok? maybe: purchase_price = '".$price."'
        $mysqli->query("UPDATE containers_products SET first_exchange_rate = '".$_POST['exchange_rate']."' WHERE id = '".$cont_product['id']."'")or die($mysqli->error);

        $mysqli->query("UPDATE warehouse SET first_exchange_rate = '".$_POST['exchange_rate']."' WHERE id = '".$cont_product['warehouse_id']."'")or die($mysqli->error);

    }

    $total += $_POST['spare_parts'];

    $deposit = $total * 0.3;

    $mysqli->query("UPDATE containers SET first_exchange_rate = '".$_POST['exchange_rate']."', first_payment = '".$deposit."', total_payment = '".$total."', spare_parts = '".$_POST['spare_parts']."' WHERE id = '".$_REQUEST['id']."'")or die($mysqli->error);




    header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/editace-kontejneru?brand='.$container['brand'].'&success=first_payment');
    exit;
}





if (isset($_REQUEST['action']) && $_REQUEST['action'] == "second_payment") {

    $container_query = $mysqli->query("SELECT brand FROM containers WHERE id = '" . $_REQUEST['id']. "'") or die($mysqli->error);
    $container = mysqli_fetch_array($container_query);

    $deposit_query = $mysqli->query("SELECT * FROM containers WHERE id = '".$_REQUEST['id']."'")or die($mysqli->error);
    $deposit = mysqli_fetch_assoc($deposit_query);

    $total_payment_correction = 0;

    $container_products = $mysqli->query("SELECT * FROM containers_products WHERE container_id = '" . $_REQUEST['id'] . "' ORDER BY id desc") or die($mysqli->error);
    while ($cont_product = mysqli_fetch_array($container_products)) {

            $price = 0;
            $price = $_POST['price-' . $cont_product['id']];
            $total_payment_correction += $price;

        // todo: is this ok? maybe: purchase_price = '".$price."'
        $mysqli->query("UPDATE containers_products SET first_exchange_rate = '".$_POST['exchange_rate']."' WHERE id = '".$cont_product['id']."'")or die($mysqli->error);

        $mysqli->query("UPDATE warehouse SET first_exchange_rate = '".$_POST['exchange_rate']."' WHERE id = '".$cont_product['warehouse_id']."'")or die($mysqli->error);

    }

    $total_payment_correction += $_POST['spare_parts'];
    $second_payment = $total_payment_correction - $deposit['first_payment'];

    $mysqli->query("UPDATE containers SET second_exchange_rate = '".$_POST['exchange_rate']."', second_payment = '".$second_payment."', total_payment_correction = '".$total_payment_correction."', spare_parts = '".$_POST['spare_parts']."' WHERE id = '".$_REQUEST['id']."'")or die($mysqli->error);


    header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/editace-kontejneru?brand='.$container['brand'].'&success=first_payment');
    exit;
}











if (isset($_REQUEST['action']) && $_REQUEST['action'] == "add_codes") {

    $has_serial = false;
    $container_products = $mysqli->query("SELECT *, DATE_FORMAT(date_created, '%d. %m. %Y') as dateformated FROM containers_products WHERE container_id = '" . $_REQUEST['id'] . "' ORDER BY id desc") or die($mysqli->error);
    while ($cont_product = mysqli_fetch_array($container_products)) {

        if (empty($cont_product['warehouse_id'])) {

            $serial_number = "";
            $price = "";

            $serial_number = $_POST['value-' . $cont_product['id']];
            $price = $_POST['price-' . $cont_product['id']];

            $mysqli->query("UPDATE containers_products SET purchase_price = '".$price."' WHERE id = '".$cont_product['id']."'")or die($mysqli->error);

            if ($serial_number != '') {

                $has_serial = true;

                $insert = $mysqli->query("INSERT INTO warehouse (product, status, demand_id, customer, serial_number, created_date, purchase_price, location_id) VALUES ('" . $cont_product['product'] . "','0','" . $cont_product['demand_id'] . "','".$cont_product['customer']."','$serial_number', now(), '$price', '" . $_POST['location_id'] . "')") or die($mysqli->error);
                $hottub_id = $mysqli->insert_id;

                $specsquery = $mysqli->query("SELECT specs_id, value FROM containers_products_specs_bridge WHERE client_id = '" . $cont_product['id'] . "'") or die($mysqli->error);
                while ($specs = mysqli_fetch_array($specsquery)) {

                    $insert_specs = $mysqli->query("INSERT INTO warehouse_specs_bridge (client_id, specs_id, value)
			  VALUES ('$hottub_id', '" . $specs['specs_id'] . "', '" . $specs['value'] . "')") or die($mysqli->error);

                    if($specs['specs_id'] == '5'){ $type = $specs['value']; }

                }


                // ONLY WAREHOUSE SPECS

                $choosed_hottub = $cont_product['product'];

                $get_ids = $mysqli->query("SELECT w.id as id, w.name as name FROM warehouse_products_types w, warehouse_products p WHERE w.warehouse_product_id = p.id AND p.connect_name = '$choosed_hottub' AND w.name = '".$type."'") or die($mysqli->error);
                $get_id = mysqli_fetch_array($get_ids);

                $specs_query = $mysqli->query("SELECT *, s.id as id FROM specs s, warehouse_products_types_specs w WHERE w.spec_id = s.id AND w.type_id = '" . $get_id['id'] . "' AND s.supplier != 1 AND s.warehouse_spec = 1 GROUP BY s.id") or die($mysqli->error);

                while ($specs = mysqli_fetch_array($specs_query)) {

                    $mysqli->query("INSERT INTO warehouse_specs_bridge (value, client_id, specs_id) VALUES ('". $value ."','" . $hottub_id . "','" . $specs['id'] . "')") or die($mysqli->error);

                    // getting param id

                    if (isset($specs['type']) && $specs['type'] == 1) {

                        $paramsquery = $mysqli->query("SELECT *, p.id as id FROM specs_params p, warehouse_products_types_specs w  WHERE p.spec_id = '" . $specs['id'] . "' AND w.spec_param_id = p.id AND w.type_id = '" . $get_id['id'] . "' AND p.option = '".$value."' GROUP by p.id") or die($mysqli->error);

                        $param = mysqli_fetch_array($paramsquery);

                        $param_id = $param['id'];

                    } else {

                        if ($value == 'Ano') { $param_id = 1; } else { $param_id = 0;}

                    }


                    // warehouse accessories

                    $products_check = $mysqli->query("SELECT * FROM demands_products WHERE spec_id = '" . $specs['id'] . "' AND param_id = '" . $param_id . "' AND type = '" . $choosed_hottub . "'") or die($mysqli->error);

                    // selected param is equal to desired param for accessory assignment
                    if (mysqli_num_rows($products_check) > 0) {

                        $product = mysqli_fetch_array($products_check);

                        $mysqli->query("INSERT INTO warehouse_products_bridge (warehouse_id, spec_id, product_id, variation_id, quantity, reserved, location_id) VALUES ('" . $hottub_id . "', '" . $specs['id'] . "', '" . $product['product_id'] . "', '" . $product['variation_id'] . "', '1', '1', '" . $_POST['location_id'] . "')") or die($mysqli->error);

                    }


                }

                /// END SPECS END SPECS END SPECS END SPECS


                $update = $mysqli->query("UPDATE containers_products SET warehouse_id = '$hottub_id' WHERE id = '" . $cont_product['id'] . "'") or die($mysqli->error);

            }

        }

    }


    if ($has_serial) {

        $now = date("Y-m-d", strtotime("now"));

        $container_query = $mysqli->query("SELECT size, container_name FROM containers WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);
        $container = mysqli_fetch_array($container_query);

        if (isset($container['size']) && $container['size'] == '7') {

            $estimated = date("Y-m-d", strtotime("+53 days", strtotime($now)));

            $correction = date("Y-m-d", strtotime("+28 days", strtotime($now)));

        } elseif (isset($container['size']) && $container['size'] == '14') {

            $estimated = date("Y-m-d", strtotime("+77 days", strtotime($now)));

            $correction = date("Y-m-d", strtotime("+42 days", strtotime($now)));

        }

        $update = $mysqli->query("UPDATE containers SET closed = '2', date_due = '$estimated', location_id = '" . $_POST['location_id'] . "', date_shipped = '$correction'  WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);


        $title = 'Naskladnění kontejneru ' . $container['container_name'];

        $mysqli->query("INSERT INTO dashboard_texts (admin_id, title, container_id, date, enddate) values ('" . $client['id'] . "','$title', '" . $_REQUEST['id'] . "','$estimated','$estimated')") or die($mysqli->error);

        $id = $mysqli->insert_id;

        $mysqli->query("DELETE FROM mails_recievers WHERE type_id = '$id' AND type = 'event'") or die($mysqli->error);

        $recievers_query = $mysqli->query("SELECT id, user_name, dimension, email FROM demands WHERE (role <> 'client' AND role <> 'admin') AND dimension != ''") or die($mysqli->error);
        while ($reciever = mysqli_fetch_array($recievers_query)) {
            $performersArray[] = $reciever['id'];
        }

        if (!empty($performersArray)) {

            recievers($performersArray, $observersArray, 'event', $id);

        }

        saveCalendarEvent($id, 'event');

    }

    $container_query = $mysqli->query("SELECT brand FROM containers WHERE id = '" . $_REQUEST['id']. "'") or die($mysqli->error);
    $container = mysqli_fetch_array($container_query);

    header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/editace-kontejneru?brand='.$container['brand'].'&success=add_codes');
    exit;

}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "add_container") {

    $prev_id_query = $mysqli->query("SELECT id_brand FROM containers WHERE brand = '".$_REQUEST['brand']."' ORDER BY id_brand DESC LIMIT 1")or die($mysqli->error);
    $prev_id = mysqli_fetch_assoc($prev_id_query);
    $prev_id['id_brand']++;

    $mysqli->query("INSERT INTO containers (id_brand, customer, size, creator_id, editor_id, date_created, brand) VALUES ('".$prev_id['id_brand']."', '1', '14', '" . $client['id'] . "', '" . $client['id'] . "', CURRENT_TIMESTAMP(), '".$_REQUEST['brand']."')") or die($mysqli->error);

    header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/editace-kontejneru?brand='.$brand);
    exit;

}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "split") {

    $products_count = $mysqli->query("SELECT COUNT(*) AS number FROM containers_products WHERE container_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

    $products_counter = mysqli_fetch_array($products_count);

    $first_rounded_up = round($products_counter['number'] / 2, 0, PHP_ROUND_HALF_UP);

    $second_remaining = $products_counter['number'] - $first_rounded_up;

    $container_update = $mysqli->query("UPDATE containers SET size = '7', editor_id = '" . $client['id'] . "' WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);


    $prev_id_query = $mysqli->query("SELECT id_brand FROM containers WHERE brand = '".$_REQUEST['brand']."'ORDER BY id_brand DESC LIMIT 1")or die($mysqli->error);
    $prev_id = mysqli_fetch_assoc($prev_id_query);
    $prev_id['id_brand']++;

    $container_insert = $mysqli->query("INSERT INTO containers (id_brand, customer, size, creator_id, editor_id, date_created, brand) VALUES ('".$prev_id['id_brand']."', '1', '7', '" . $client['id'] . "', '" . $client['id'] . "', CURRENT_TIMESTAMP(), '".$_REQUEST['brand']."')") or die($mysqli->error);

    $container_product_id = $mysqli->insert_id;

    $i = 0;
    $products_query = $mysqli->query("SELECT id FROM containers_products WHERE container_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

    while ($products = mysqli_fetch_array($products_query)) {

        $i++;

        if ($i <= $first_rounded_up) {

        } elseif ($i > $first_rounded_up) {

            $update_product = $mysqli->query("UPDATE containers_products SET container_id = '$container_product_id' WHERE id = '" . $products['id'] . "'") or die($mysqli->error);

        }

    }

    $container_query = $mysqli->query("SELECT brand FROM containers WHERE id = '" . $_REQUEST['id']. "'") or die($mysqli->error);
    $container = mysqli_fetch_array($container_query);

    header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/editace-kontejnerubrand='.$container['brand'].'');
    exit;

}



if (isset($_REQUEST['action']) && $_REQUEST['action'] == "merge") {

    $old_container = $_POST['container'];
    $new_container = $_REQUEST['id'];

    $update_query = $mysqli->query("UPDATE containers SET size = '14', editor_id = '" . $client['id'] . "' WHERE id = '$new_container'") or die($mysqli->error);

    $products_query = $mysqli->query("SELECT id FROM containers_products WHERE container_id = '$old_container'") or die($mysqli->error);

    while ($products = mysqli_fetch_array($products_query)) {

        $update_product = $mysqli->query("UPDATE containers_products SET container_id = '$new_container' WHERE id = '" . $products['id'] . "'") or die($mysqli->error);

    }

    $remove_query = $mysqli->query("DELETE FROM containers WHERE id = '$old_container'") or die($mysqli->error);


    $container_query = $mysqli->query("SELECT brand FROM containers WHERE id = '" . $_REQUEST['id']. "'") or die($mysqli->error);
    $container = mysqli_fetch_array($container_query);

    header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/editace-kontejnerubrand='.$container['brand'].'');
    exit;

}



if (isset($_REQUEST['action']) && $_REQUEST['action'] == "transfer") {


    $cont_query = $mysqli->query("SELECT container_id, warehouse_id FROM containers_products WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);
    $cont = mysqli_fetch_array($cont_query);



    $container_query = $mysqli->query("SELECT closed, date_correction, date_due, brand FROM containers WHERE id = '" . $cont['container_id'] . "'") or die($mysqli->error);
    $container = mysqli_fetch_array($container_query);


    $target_container_query = $mysqli->query("SELECT date_due FROM containers WHERE id = '" . $_POST['container'] . "'") or die($mysqli->error);
    $target_container = mysqli_fetch_array($target_container_query);

    if (isset($_POST['container']) && $_POST['container'] == 'new') {

        $prev_id_query = $mysqli->query("SELECT id_brand FROM containers WHERE brand = '".$container['brand']."' ORDER BY id_brand DESC LIMIT 1")or die($mysqli->error);
        $prev_id = mysqli_fetch_assoc($prev_id_query);
        $prev_id['id_brand']++;

        $mysqli->query("INSERT INTO containers (id_brand, customer, size, creator_id, editor_id, date_created, brand) VALUES ('".$prev_id['id_brand']."', '1', '14', '" . $client['id'] . "', '" . $client['id'] . "', CURRENT_TIMESTAMP(), '".$container['brand']."')") or die($mysqli->error);

        $container_id = $mysqli->insert_id;

        $_POST['container'] = $container_id;

    }

    $mysqli->query("UPDATE containers_products SET container_id = '" . $_POST['container'] . "', editor_id = '" . $client['id'] . "' WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);


    if($cont['warehouse_id'] != 0 && !empty($container['closed']) && $container['closed'] > 1){

        // product already exists, update only serial_number and date_due
        $mysqli->query("UPDATE warehouse SET serial_number = '".$_POST['serial_number']."', loadingdate = '".$target_container['date_due']."' WHERE id = '".$cont['warehouse_id']."'")or die($mysqli->error);

    }elseif(!empty($container['closed']) && $container['closed'] > 1){

        // create warehouse product

    }

    $mysqli->query("UPDATE containers SET editor_id = '" . $client['id'] . "' WHERE id = '" . $_POST['container'] . "'") or die($mysqli->error);
    $mysqli->query("UPDATE containers SET editor_id = '" . $client['id'] . "' WHERE id = '" . $cont['container_id'] . "'") or die($mysqli->error);

    header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/editace-kontejneru?brand='.$container['brand'].'&success=transfer');
    exit;

}



if (isset($_REQUEST['action']) && $_REQUEST['action'] == "duplicate") {

    $get_container_product = $mysqli->query('SELECT * FROM containers_products WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);
    $container_product = mysqli_fetch_assoc($get_container_product);

    $container_query = $mysqli->query("SELECT brand FROM containers WHERE id = '" . $container_product['container_id'] . "'") or die($mysqli->error);
    $container = mysqli_fetch_array($container_query);


    // set customer
    if($container['brand'] == 'Espoo Deluxe' || $container['brand'] == 'Espoo Smart'){

        $customer = 0;

    }elseif($container['brand'] == 'Pergola'){

        $customer = 4;

    }else{

        $customer = 1;

    }

    $container_products_insert = $mysqli->query("INSERT INTO containers_products (container_id, demand_id, customer, product, date_created, creator_id, editor_id, description)
	VALUES ('" . $container_product['container_id'] . "', '0', '" . $customer . "', '" . $container_product['product'] . "', CURRENT_TIMESTAMP(), '" . $client['id'] . "', '" . $client['id'] . "', '')") or die($mysqli->error);

    $container_product_id = $mysqli->insert_id;

    $specsquery = $mysqli->query("SELECT specs_id, value FROM containers_products_specs_bridge WHERE client_id = '" . $container_product['id'] . "'") or die($mysqli->error);
    while ($specs = mysqli_fetch_array($specsquery)) {

        $insert_specs = $mysqli->query("INSERT INTO containers_products_specs_bridge (client_id, specs_id, value)
	  VALUES ('$container_product_id', '" . $specs['specs_id'] . "', '" . $specs['value'] . "')") or die($mysqli->error);

    }

    $update_query = $mysqli->query("UPDATE containers SET editor_id = '" . $client['id'] . "' WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

    header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/editace-kontejneru?brand='.$container['brand'].'&success=duplicate');
    exit;

}




if (isset($_REQUEST['action']) && $_REQUEST['action'] == "add_demand") {

    // check if exists

    // calculate total price
    $hottub_price = 0;

    // demand from containers
    if(!empty($_POST['demand'])){

        $demand_id = $_POST['demand'];
        $container_id = $_REQUEST['id'];

    // demand from demand
    }elseif(!empty($_REQUEST['demand_id'])){

        if (isset($_POST['container']) && $_POST['container'] == 'new') {

            $prev_id_query = $mysqli->query("SELECT id_brand FROM containers WHERE brand = '".$_REQUEST['brand']."'ORDER BY id_brand DESC LIMIT 1")or die($mysqli->error);
            $prev_id = mysqli_fetch_assoc($prev_id_query);
            $prev_id['id_brand']++;

            $container_insert = $mysqli->query("INSERT INTO containers (id_brand, customer, size, creator_id, editor_id, date_created, brand) VALUES ('".$prev_id['id_brand']."', '1', '14', '" . $client['id'] . "', '" . $client['id'] . "', CURRENT_TIMESTAMP(), '".$_REQUEST['brand']."')") or die($mysqli->error);
            $container_id = $mysqli->insert_id;

        }else { 
            
            $container_id = $_POST['container']; 
        
        }

        $demand_id = $_REQUEST['demand_id'];

    }else{ die('error - demand not set'); }

    $check_query = $mysqli->query("SELECT * FROM containers_products WHERE demand_id = '".$demand_id."'")or die($mysqli->error);

    if(mysqli_num_rows($check_query) > 0){
        if(!empty($_REQUEST['demand_id'])){
            header('location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id='.$demand_id.'&success=add_to_container');
        }else{

            $container_query = $mysqli->query("SELECT brand FROM containers WHERE id = '" . $_REQUEST['id']. "'") or die($mysqli->error);
            $container = mysqli_fetch_array($container_query);

            header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/editace-kontejneru?brand='.$container['brand'].'&success=add_demand');
        }
        exit;
    }


    $getclientquery = $mysqli->query('SELECT * FROM demands WHERE id = "' . $demand_id . '"') or die($mysqli->error);
    $getclient = mysqli_fetch_assoc($getclientquery);

    $container_query = $mysqli->query("SELECT closed, date_correction, date_due, brand FROM containers WHERE id = '" . $container_id . "'") or die($mysqli->error);
    $container = mysqli_fetch_array($container_query);


    // insert into warehouse

    $warehouse_id = 0;
    if (isset($container['closed']) && $container['closed'] > 1) {

        // todo serial number + price
        $mysqli->query("INSERT INTO warehouse (product, status, demand_id, customer, serial_number, created_date, location_id, loadingdate) VALUES ('" . $getclient['product'] . "','".$container['date_correction']."','" . $demand_id . "','1','".$_POST['serial_number']."', now(), '1', '".$container['date_due']."')") or die($mysqli->error);

        $warehouse_id = $mysqli->insert_id;

    }

    // set customer
    if($container['brand'] == 'Espoo Deluxe' || $container['brand'] == 'Espoo Smart'){

        $customer = 0;

    }elseif($container['brand'] == 'Pergola'){

        $customer = 4;

    }else{

        $customer = 1;

    }


    $container_products_insert = $mysqli->query("INSERT INTO containers_products (container_id, demand_id, warehouse_id, customer, product, date_created, creator_id, editor_id, description)
	VALUES ('" . $container_id . "', '" . $demand_id . "', '".$warehouse_id."', '".$customer."', '" . $getclient['product'] . "', CURRENT_TIMESTAMP(), '" . $client['id'] . "', '" . $client['id'] . "', '')") or die($mysqli->error);

    $container_product_id = $mysqli->insert_id;

    // provedení start
    $get_provedeni = $mysqli->query("SELECT value FROM demands_specs_bridge WHERE client_id = '" . $demand_id . "' AND specs_id = '5'") or die($mysqli->error);
    $provedeni = mysqli_fetch_assoc($get_provedeni);

    $choosed_hottub = $getclient['product'];
    $choosed_type = $provedeni['value'];

    // provedení start

    // packet price included
    $get_ids = $mysqli->query("SELECT w.id as id, w.name as name, w.price FROM warehouse_products_types w, warehouse_products p WHERE w.warehouse_product_id = p.id AND p.connect_name = '$choosed_hottub' AND w.name = '$choosed_type'") or die($mysqli->error);
    $get_id = mysqli_fetch_array($get_ids);

    $hottub_price += $get_id['price'];


    // insert provedení
    $mysqli->query("INSERT INTO containers_products_specs_bridge (value, client_id, specs_id, price) VALUES ('" . $get_id['name'] . "','$container_product_id','5', '".$get_id['price']."')") or die($mysqli->error);

    if (isset($container['closed']) && $container['closed'] > 1) {

        $mysqli->query("INSERT INTO warehouse_specs_bridge (value, client_id, specs_id, price) VALUES ('" . $get_id['name'] . "','" . $warehouse_id . "','5', '".$get_id['price']."')") or die($mysqli->error);

    }

    // provedení end



    // todo check if selected value is available for selected product and specification -- tady asi není potřeba, když to jde z poptávky

    $specsquery = $mysqli->query("SELECT b.specs_id, b.value, s.type, s.id FROM demands_specs_bridge b, specs s WHERE b.client_id = '" . $demand_id . "' AND b.specs_id <> 5 AND s.id = b.specs_id") or die($mysqli->error);
    while ($specs = mysqli_fetch_array($specsquery)) {

        // spec type = 0 => bolean
        // spec_type = 1 => set

        // single spec price connected to packed and spec value
        if (isset($specs['type']) && $specs['type'] == 1 || $specs['type'] == 2) {

            $param_price_query = $mysqli->query("SELECT w.price FROM specs_params p, warehouse_products_types_specs w WHERE p.spec_id = '" . $specs['specs_id'] . "' AND w.spec_param_id = p.id AND w.type_id = '" . $get_id['id'] . "' AND p.option = '" . $specs['value'] . "'") or die($mysqli->error);

        } elseif (isset($specs['type']) && $specs['type'] == 0) {

            if ($specs['value'] == 'Ano') { $param_value = '1'; } else { $param_value = '0';}

            $param_price_query = $mysqli->query("SELECT price FROM warehouse_products_types_specs WHERE spec_id = '" . $specs['specs_id'] . "' AND type_id = '" . $get_id['id'] . "' AND spec_param_id = '" . $param_value . "'") or die($mysqli->error);

        } 

        $param_price = mysqli_fetch_assoc($param_price_query);
        $hottub_price += $param_price['price'];
        $spec_value = $specs['value'];


        // special condition for ozonator
        if($specs['specs_id'] == '36' && $specs['value'] != 'IQue Ozonátor'){ $spec_value = 'Bez ozonátoru'; }

        // insert specification from demand into container product
        $mysqli->query("INSERT INTO containers_products_specs_bridge (client_id, specs_id, value, price)
	  VALUES ('$container_product_id', '" . $specs['specs_id'] . "', '" . $spec_value . "', '" . $param_price['price'] . "')") or die($mysqli->error);

        // warehouse if closed
        if (isset($container['closed']) && $container['closed'] > 1) {

            // warehouse specs
            $mysqli->query("INSERT INTO warehouse_specs_bridge (value, client_id, specs_id, price) VALUES ('$spec_value','" . $warehouse_id . "','" . $specs['id'] . "', '" . $param_price['price'] . "')") or die($mysqli->error);

            // getting param id
            if (isset($specs['type']) && $specs['type'] == 1 || $specs['type'] == 2) {

                $paramsquery = $mysqli->query("SELECT *, p.id as id FROM specs_params p, warehouse_products_types_specs w  WHERE p.spec_id = '" . $specs['id'] . "' AND w.spec_param_id = p.id AND w.type_id = '" . $get_id['id'] . "' AND p.option = '".$spec_value."' GROUP by p.id") or die($mysqli->error);

                $param = mysqli_fetch_array($paramsquery);

                $param_id = $param['id'];

            } else {

                if ($spec_value == 'Ano') { $param_id = 1; } else { $param_id = 0;}

            }


            // warehouse accessories
            $products_check = $mysqli->query("SELECT * FROM demands_products WHERE spec_id = '" . $specs['id'] . "' AND param_id = '" . $param_id . "' AND type = '" . $choosed_hottub . "'") or die($mysqli->error);

            // selected param is equal to desired param for accessory assignment
            if (mysqli_num_rows($products_check) > 0) {

                $product = mysqli_fetch_array($products_check);

                $mysqli->query("INSERT INTO warehouse_products_bridge (warehouse_id, spec_id, product_id, variation_id, quantity, reserved, location_id) VALUES ('" . $warehouse_id . "', '" . $specs['id'] . "', '" . $product['product_id'] . "', '" . $product['variation_id'] . "', '1', '1', '1')") or die($mysqli->error);

            }

        }
        // warehouse if closed end



    }


    $mysqli->query("UPDATE warehouse SET purchase_price = '" . $hottub_price . "' WHERE id = '" . $warehouse_id . "'") or die($mysqli->error);
    $mysqli->query("UPDATE containers_products SET purchase_price = '" . $hottub_price . "' WHERE id = '" . $container_product_id . "'") or die($mysqli->error);

    $mysqli->query("UPDATE containers SET editor_id = '" . $client['id'] . "' WHERE id = '" . $container_id . "'") or die($mysqli->error);


    if(!empty($_REQUEST['demand_id'])){

        header('location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id='.$demand_id.'&success=add_to_container');

    }else{

        header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/editace-kontejneru?brand='.$container['brand'].'&success=add_demand');

    }
    exit;

}





if (isset($_REQUEST['action']) && $_REQUEST['action'] == "add_to_container") {

    $hottub_price = 0;

    $container_query = $mysqli->query("SELECT closed, date_correction, date_due, brand FROM containers WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);
    $container = mysqli_fetch_array($container_query);


    // insert into warehouse
    $warehouse_id = 0;
    
    // set customer
    if($container['brand'] == 'Espoo Deluxe' || $container['brand'] == 'Espoo Smart'){

        $customer = 0;

    }elseif($container['brand'] == 'Pergola'){

        $customer = 4;

    }else{

        $customer = 1;

    }


    if (isset($container['closed']) && $container['closed'] > 1) {


        // todo serial number + price
        $insert = $mysqli->query("INSERT INTO warehouse (product, status, demand_id, customer, serial_number, created_date, location_id, loadingdate) VALUES ('" . $_POST['virivkatype'] . "','".$container['date_correction']."','0','".$customer."','".$_POST['serial_number']."', now(), '1', '".$container['date_due']."')") or die($mysqli->error);

        $warehouse_id = $mysqli->insert_id;

    }




    $container_products_insert = $mysqli->query("INSERT INTO containers_products (container_id, demand_id, warehouse_id, customer, product, date_created, creator_id, editor_id, description)
	VALUES ('" . $_REQUEST['id'] . "', '0', '".$warehouse_id."', '".$customer."', '" . $_POST['virivkatype'] . "', CURRENT_TIMESTAMP(), '" . $client['id'] . "', '" . $client['id'] . "', '')") or die($mysqli->error);

    $container_product_id = $mysqli->insert_id;


    /// START SPECS

    // provedení start
    $choosed_hottub = $_POST['virivkatype'];

    $choosed_type = $_POST['provedeni_' . $choosed_hottub];

    $get_ids = $mysqli->query("SELECT w.id as id, w.name as name, w.price FROM warehouse_products_types w, warehouse_products p WHERE w.warehouse_product_id = p.id AND p.connect_name = '$choosed_hottub' AND w.seo_url = '$choosed_type'") or die($mysqli->error);
    $get_id = mysqli_fetch_array($get_ids);


    $insert_specs = $mysqli->query("INSERT INTO containers_products_specs_bridge (value, client_id, specs_id, price) VALUES ('" . $get_id['name'] . "','$container_product_id','5', '".$get_id['price']."')") or die($mysqli->error);

    if (isset($container['closed']) && $container['closed'] > 1) {

           $mysqli->query("INSERT INTO warehouse_specs_bridge (value, client_id, specs_id, price) VALUES ('" . $get_id['name'] . "','" . $warehouse_id . "','5', '".$get_id['price']."')") or die($mysqli->error);

    }

    $hottub_price += $get_id['price'];

    // provedení end


    // specs start

    $specs_query = $mysqli->query("SELECT *, s.id as id FROM specs s, warehouse_products_types_specs w WHERE w.spec_id = s.id AND w.type_id = '" . $get_id['id'] . "' GROUP BY s.id") or die($mysqli->error);

    while ($specs = mysqli_fetch_array($specs_query)) {

        $seoslug = $specs['seoslug'];

        $spec_value = $_POST[$choosed_hottub . '_' . $choosed_type . '_' . $seoslug];


        // spec type = 0 => bolean
        // spec_type = 1 => set

        // single spec price connected to packed and spec value
        if (isset($specs['type']) && $specs['type'] == 1) {

            $param_price_query = $mysqli->query("SELECT w.price FROM specs_params p, warehouse_products_types_specs w WHERE p.spec_id = '" . $specs['id'] . "' AND w.spec_param_id = p.id AND w.type_id = '" . $get_id['id'] . "' AND p.option = '" . $spec_value . "'") or die($mysqli->error);

        } elseif (isset($specs['type']) && $specs['type'] == 0) {

            if ($spec_value == 'Ano') { $param_value = '1'; } else { $param_value = '0';}

            $param_price_query = $mysqli->query("SELECT price FROM warehouse_products_types_specs WHERE spec_id = '" . $specs['id'] . "' AND type_id = '" . $get_id['id'] . "' AND spec_param_id = '" . $param_value . "'") or die($mysqli->error);

        }elseif (isset($specs['type']) && $specs['type'] == 2) {

            $param_price_query = $mysqli->query("SELECT w.price FROM specs_params p, warehouse_products_types_specs w WHERE p.spec_id = '" . $specs['id'] . "' AND w.spec_param_id = p.id AND w.type_id = '" . $get_id['id'] . "' AND p.option = '" . $spec_value . "'") or die($mysqli->error);

        }

        print_r($spec_value);
        echo '<br>';

        $param_price = mysqli_fetch_assoc($param_price_query);

        $hottub_price += $param_price['price'];

        $mysqli->query("INSERT INTO containers_products_specs_bridge (value, client_id, specs_id, price) VALUES ('$spec_value','$container_product_id','" . $specs['id'] . "', '" . $param_price['price'] . "')") or die($mysqli->error);

        // warehouse if closed
        if (isset($container['closed']) && $container['closed'] > 1) {

            // warehouse specs
            $mysqli->query("INSERT INTO warehouse_specs_bridge (value, client_id, specs_id, price) VALUES ('$spec_value','" . $warehouse_id . "','" . $specs['id'] . "', '" . $param_price['price'] . "')") or die($mysqli->error);


            // getting param id

            if (isset($specs['type']) && $specs['type'] == 1) {

                $paramsquery = $mysqli->query("SELECT *, p.id as id FROM specs_params p, warehouse_products_types_specs w  WHERE p.spec_id = '" . $specs['id'] . "' AND w.spec_param_id = p.id AND w.type_id = '" . $get_id['id'] . "' AND p.option = '".$spec_value."' GROUP by p.id") or die($mysqli->error);

                $param = mysqli_fetch_array($paramsquery);

                $param_id = $param['id'];

            } else {

                if ($spec_value == 'Ano') { $param_id = 1; } else { $param_id = 0;}

            }

            // warehouse accessories

            $products_check = $mysqli->query("SELECT * FROM demands_products WHERE spec_id = '" . $specs['id'] . "' AND param_id = '" . $param_id . "' AND type = '" . $choosed_hottub . "'") or die($mysqli->error);

            // selected param is equal to desired param for accessory assignment
            if (mysqli_num_rows($products_check) > 0) {

                $product = mysqli_fetch_array($products_check);

                $mysqli->query("INSERT INTO warehouse_products_bridge (warehouse_id, spec_id, product_id, variation_id, quantity, reserved, location_id) VALUES ('" . $warehouse_id . "', '" . $specs['id'] . "', '" . $product['product_id'] . "', '" . $product['variation_id'] . "', '1', '1', '1')") or die($mysqli->error);

            }

        }
        // warehouse if closed end



    }

    // specs end

    /// END SPEC

    $mysqli->query("UPDATE warehouse SET purchase_price = '" . $hottub_price . "' WHERE id = '" . $warehouse_id . "'") or die($mysqli->error);
    $mysqli->query("UPDATE containers_products SET purchase_price = '" . $hottub_price . "' WHERE id = '" . $container_product_id . "'") or die($mysqli->error);

    $mysqli->query("UPDATE containers SET editor_id = '" . $client['id'] . "' WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

    header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/editace-kontejneru?brand='.$container['brand'].'&success=add_to_container_standard');
    exit;

}



if (isset($_REQUEST['action']) && $_REQUEST['action'] == "edit_container_product") {

    $hottub_price = 0;
    $container_id = $_REQUEST['id'];

    $cont_query = $mysqli->query("SELECT container_id, warehouse_id, demand_id, product FROM containers_products WHERE id = '" . $container_id . "'") or die($mysqli->error);
    $cont = mysqli_fetch_array($cont_query);


    $mysqli->query("UPDATE containers_products SET demand_id = '" . $_POST['demand'] . "',  editor_id = '" . $client['id'] . "' WHERE id = '" . $container_id . "'") or die($mysqli->error);

    $container_query = $mysqli->query("SELECT closed FROM containers WHERE id = '" . $cont['container_id'] . "'") or die($mysqli->error);
    $container = mysqli_fetch_array($container_query);


    /// START SPECS START SPECS START SPECS

    if($cont['demand_id'] == 0 && empty($_POST['demand'])){

        $mysqli->query("UPDATE containers_products SET product = '" . $_POST['virivkatype'] . "' WHERE id = '" . $container_id . "'") or die($mysqli->error);

        $choosed_hottub = $_POST['virivkatype'];
        $choosed_type = $_POST['provedeni_' . $choosed_hottub];

    }else{

        $choosed_hottub = $cont['product'];
    }


    $choosed_type = $_POST['provedeni_' . $choosed_hottub];

    $get_ids = $mysqli->query("SELECT w.id as id, w.name as name, w.price FROM warehouse_products_types w, warehouse_products p WHERE w.warehouse_product_id = p.id AND p.connect_name = '$choosed_hottub' AND w.seo_url = '$choosed_type'") or die($mysqli->error);
    $get_id = mysqli_fetch_array($get_ids);

    $hottub_price += $get_id['price'];


    ///provedení

    $find_query = $mysqli->query("SELECT id FROM containers_products_specs_bridge WHERE client_id = '" . $container_id . "' AND specs_id = '5'") or die($mysqli->error);
    if (mysqli_num_rows($find_query) > 0) {

        $find = mysqli_fetch_array($find_query);
        $insert_specs = $mysqli->query("UPDATE containers_products_specs_bridge SET value = '" . $get_id['name'] . "', price = '".$get_id['price']."' WHERE id = '" . $find['id'] . "'") or die($mysqli->error);

    } else {

        $insert_specs = $mysqli->query("INSERT INTO containers_products_specs_bridge (value, client_id, specs_id, price) VALUES ('" . $get_id['name'] . "','" . $container_id . "','5', '".$get_id['price']."')") or die($mysqli->error);

    }

    
        print_r($spec_value);
        echo '<br>';


    if (isset($container['closed']) && $container['closed'] > 1) {

        $find_query = $mysqli->query("SELECT id FROM warehouse_specs_bridge WHERE client_id = '" . $cont['warehouse_id'] . "' AND specs_id = '5'") or die($mysqli->error);
        if (mysqli_num_rows($find_query) > 0) {

            $find = mysqli_fetch_array($find_query);
            $insert_specs = $mysqli->query("UPDATE warehouse_specs_bridge SET value = '" . $get_id['name'] . "', price = '".$get_id['price']."' WHERE id = '" . $find['id'] . "'") or die($mysqli->error);

        } else {

            $insert_specs = $mysqli->query("INSERT INTO warehouse_specs_bridge (value, client_id, specs_id, price) VALUES ('" . $get_id['name'] . "','" . $cont['warehouse_id'] . "','5', '".$get_id['price']."')") or die($mysqli->error);

        }

    }

    ///provedení



    $specs_query = $mysqli->query("SELECT *, s.id as id FROM specs s, warehouse_products_types_specs w WHERE w.spec_id = s.id AND w.type_id = '" . $get_id['id'] . "' GROUP BY s.id") or die($mysqli->error);

    while ($specs = mysqli_fetch_array($specs_query)) {

        $seoslug = $specs['seoslug'];

        if(!empty($_POST[$choosed_hottub . '_' . $choosed_type . '_' . $seoslug])){
            $spec_value = $_POST[$choosed_hottub . '_' . $choosed_type . '_' . $seoslug];
        }else{
            $spec_value = '';
        }


        // paid
        if(!empty($_POST[$choosed_hottub . '_' . $choosed_type . '_' . $seoslug.'_paid'])){

            $paid = $_POST[$choosed_hottub . '_' . $choosed_type . '_' . $seoslug.'_paid'];

            if(!empty($_POST[$choosed_hottub . '_' . $choosed_type . '_' . $seoslug.'_paid_text'])){
                $paid_text = $_POST[$choosed_hottub . '_' . $choosed_type . '_' . $seoslug.'_paid_text'];
            }else{
                $paid_text = '';
            }

        }else{

            $paid = 0;
            $paid_text = '';

        }






        // spec type = 0 => bolean
        // spec_type = 1 => set

        // single spec price connected to packed and spec value
        if (isset($specs['type']) && $specs['type'] == 1) {

            $param_price_query = $mysqli->query("SELECT w.price FROM specs_params p, warehouse_products_types_specs w WHERE p.spec_id = '" . $specs['id'] . "' AND w.spec_param_id = p.id AND w.type_id = '" . $get_id['id'] . "' AND p.option = '" . $spec_value . "'") or die($mysqli->error);

        } else {

            if ($spec_value == 'Ano') { $param_value = '1'; } else { $param_value = '0';}

            $param_price_query = $mysqli->query("SELECT price FROM warehouse_products_types_specs WHERE spec_id = '" . $specs['id'] . "' AND type_id = '" . $get_id['id'] . "' AND spec_param_id = '" . $param_value . "'") or die($mysqli->error);

        }

        $param_price = mysqli_fetch_assoc($param_price_query);

        $hottub_price += $param_price['price'];






        $find_query = $mysqli->query("SELECT id FROM containers_products_specs_bridge WHERE client_id = '" . $container_id . "' AND specs_id = '" . $specs['id'] . "'") or die($mysqli->error);
        if (mysqli_num_rows($find_query) > 0) {

            $find = mysqli_fetch_array($find_query);
            $insert_specs = $mysqli->query("UPDATE containers_products_specs_bridge SET value = '$spec_value', paid = '".$paid."', paid_text = '".$paid_text."', price = '".$param_price['price']."' WHERE id = '" . $find['id'] . "'") or die($mysqli->error);

        } else {

            $insert_specs = $mysqli->query("INSERT INTO containers_products_specs_bridge (value, client_id, specs_id, paid, paid_text, price) VALUES ('$spec_value','" . $container_id . "','" . $specs['id'] . "', '".$paid."', '".$paid_text."', '".$param_price['price']."')") or die($mysqli->error);

        }


        if (isset($container['closed']) && $container['closed'] > 1) {



            // add paid and paid text ... paid = add empty spec
            if(!empty($_POST[$choosed_hottub . '_' . $choosed_type . '_' . $seoslug.'_paid'])){ $spec_value = ''; }

            // warehouse specs

            $find_query = $mysqli->query("SELECT id FROM warehouse_specs_bridge WHERE client_id = '" . $cont['warehouse_id'] . "' AND specs_id = '" . $specs['id'] . "'") or die($mysqli->error);
            if (mysqli_num_rows($find_query) > 0) {


                $find = mysqli_fetch_array($find_query);

                $mysqli->query("UPDATE warehouse_specs_bridge SET value = '$spec_value', paid = '".$paid."', paid_text = '".$paid_text."', price = '".$param_price['price']."' WHERE id = '" . $find['id'] . "'") or die($mysqli->error);


            } else {

                $insert_specs = $mysqli->query("INSERT INTO warehouse_specs_bridge (value, client_id, specs_id, paid, paid_text, price) VALUES ('$spec_value','" . $cont['warehouse_id'] . "','" . $specs['id'] . "', '".$paid."', '".$paid_text."', '".$param_price['price']."')") or die($mysqli->error);


            }




            // getting param id

            if (isset($specs['type']) && $specs['type'] == 1) {

                $paramsquery = $mysqli->query("SELECT *, p.id as id FROM specs_params p, warehouse_products_types_specs w  WHERE p.spec_id = '" . $specs['id'] . "' AND w.spec_param_id = p.id AND w.type_id = '" . $get_id['id'] . "' AND p.option = '".$spec_value."' GROUP by p.id") or die($mysqli->error);

                $param = mysqli_fetch_array($paramsquery);

                $param_id = $param['id'];

            } else {

                if ($spec_value == 'Ano') { $param_id = 1; } else { $param_id = 0;}

            }

            // warehouse accessories

            $products_check = $mysqli->query("SELECT * FROM demands_products WHERE spec_id = '" . $specs['id'] . "' AND param_id = '" . $param_id . "' AND type = '" . $choosed_hottub . "'") or die($mysqli->error);


            // selected param is equal to desired param for accessory assignment
            if (mysqli_num_rows($products_check) > 0) {

                $product = mysqli_fetch_array($products_check);

                $check_warehouse_query = $mysqli->query("SELECT * FROM warehouse_products_bridge WHERE warehouse_id = '".$cont['warehouse_id']."' AND spec_id = '".$specs['id']."'")or die($mysqli->error);

                if(mysqli_num_rows($check_warehouse_query) > 0){

                    $mysqli->query("UPDATE warehouse_products_bridge SET product_id = '" . $product['product_id'] . "', variation_id = '" . $product['variation_id'] . "', quantity = '1', reserved = '1', location_id = '1' WHERE warehouse_id = '".$cont['warehouse_id']."' AND spec_id = '".$specs['id']."'") or die($mysqli->error);

                }else{

                    $mysqli->query("INSERT INTO warehouse_products_bridge (warehouse_id, spec_id, product_id, variation_id, quantity, reserved, location_id) VALUES ('" . $cont['warehouse_id'] . "', '" . $specs['id'] . "', '" . $product['product_id'] . "', '" . $product['variation_id'] . "', '1', '1', '1')") or die($mysqli->error);

                }


            // selected param is NOT equal to desired param for accessory assignment... checking for existing assigments
            }else{

                $mysqli->query("DELETE FROM warehouse_products_bridge WHERE warehouse_id = '".$cont['warehouse_id']."' AND spec_id = '".$specs['id']."'")or die($mysqli->error);

            }


        }


    }



//    $insert_specs = $mysqli->query("INSERT INTO containers_products_specs_bridge (value, client_id, specs_id) VALUES ('$spec_value','" . $container_id . "','" . $specs['id'] . "')") or die($mysqli->error);


    // remove specs not assigned to product/type todo into demands

    $containers_specs_query = $mysqli->query("SELECT * FROM containers_products_specs_bridge WHERE client_id = '".$container_id."' AND specs_id != 5")or die($mysqli->error);

    while($container_specs = mysqli_fetch_assoc($containers_specs_query)){


        $specs_query = $mysqli->query("SELECT *, s.id as id FROM specs s, warehouse_products_types_specs w WHERE w.spec_id = s.id AND w.type_id = '" . $get_id['id'] . "' AND s.id = '".$container_specs['specs_id']."' GROUP BY s.id") or die($mysqli->error);

        if(mysqli_num_rows($specs_query) == 0){

            $mysqli->query("DELETE FROM containers_products_specs_bridge WHERE client_id = '".$container_id."' AND specs_id = '".$container_specs['specs_id']."'")or die($mysqli->error);

                if($cont['warehouse_id'] != 0){

                    $mysqli->query("DELETE FROM warehouse_specs_bridge WHERE client_id = '".$cont['warehouse_id']."' AND specs_id = '".$container_specs['specs_id']."'")or die($mysqli->error);

                }

        }


    }



    // END SPECS END SPECS END SPECS END SPECS

    $mysqli->query("UPDATE containers_products SET purchase_price = '" . $hottub_price . "' WHERE id = '" . $container_id . "'") or die($mysqli->error);

    $mysqli->query("UPDATE warehouse SET demand_id = '" . $_POST['demand'] . "', purchase_price = '" . $hottub_price . "', serial_number = '" . $_POST['serial_number'] . "', product = '" . $choosed_hottub . "' WHERE id = '" . $cont['warehouse_id'] . "'") or die($mysqli->error);

    $mysqli->query("UPDATE containers SET editor_id = '" . $client['id'] . "' WHERE id = '" . $cont['container_id'] . "'") or die($mysqli->error);

//    header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/editace-kontejneru?success=edit_container_product');
    exit;

}



// todo všechny akce redirectnout na správnej brand
if (isset($_REQUEST['action']) && $_REQUEST['action'] == "remove") {

    $mysqli->query("DELETE FROM containers WHERE id='" . $_REQUEST['id'] . "'") or die($mysqli->error);

    $select_products = $mysqli->query("SELECT id FROM containers_products WHERE container_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

    while ($product = mysqli_fetch_array($select_products)) {

        $mysqli->query("DELETE FROM containers_products_specs_bridge WHERE client_id='" . $product['id'] . "'") or die($mysqli->error);

    }

    $mysqli->query("DELETE FROM containers_products WHERE container_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

    $container_query = $mysqli->query("SELECT brand FROM containers WHERE id = '" . $_REQUEST['id']. "'") or die($mysqli->error);
    $container = mysqli_fetch_array($container_query);

    header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/editace-kontejneru?brand='.$container['brand'].'&emove=success');
    exit;

}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "remove_product") {

    $select_products = $mysqli->query("SELECT id, container_id, warehouse_id FROM containers_products WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);
    $product = mysqli_fetch_array($select_products);

    $container_query = $mysqli->query("SELECT closed, brand FROM containers WHERE id = '" . $product['container_id'] . "'") or die($mysqli->error);
    $container = mysqli_fetch_array($container_query);

    // start remove from container
    $mysqli->query('DELETE FROM containers_products_specs_bridge WHERE client_id="' . $product['id'] . '"') or die($mysqli->error);
    $mysqli->query("DELETE FROM containers_products WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);
    // end remove from container


    // start remove from warehouse
    if (isset($container['closed']) && $container['closed'] > 1 && $product['warehouse_id'] != 0) {

        $mysqli->query("DELETE FROM warehouse WHERE id = '" . $product['warehouse_id'] . "'") or die($mysqli->error);
        $mysqli->query('DELETE FROM warehouse_specs_bridge WHERE client_id = "' . $product['warehouse_id'] . '"') or die($mysqli->error);
        $mysqli->query("DELETE FROM warehouse_products_bridge WHERE warehouse_id = '".$product['warehouse_id']."'") or die($mysqli->error);

    }
    // end remove from warehouse

    $mysqli->query("UPDATE containers SET editor_id = '" . $client['id'] . "' WHERE id = '" . $product['container_id'] . "'") or die($mysqli->error);

    header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/editace-kontejneru?brand='.$container['brand'].'&remove_product=success');
    exit;
}

if (isset($_REQUEST['od'])) { $od = $_REQUEST['od']; }

$pagetitle = "Editace kontejnerů";

include VIEW . '/default/header.php';

$currentpage = "editace-kontejneru";

$special_access_query = $mysqli->query("SELECT value FROM administration_accesses WHERE admin_id = '" . $client['id'] . "' AND site_id = 9999") or die($mysqli->error);
$special_access = mysqli_fetch_array($special_access_query);

if(empty($special_access)){
    $special_access['value'] = '';
}

if (!isset($od) || $od == 1) {
    $containers_query = $mysqli->query("SELECT *, DATE_FORMAT(date_created, '%d. %m. %Y') as dateformated, DATE_FORMAT(date_due, '%d. %m. %Y') as due_formated, DATE_FORMAT(date_received, '%d. %m. %Y') as received_formated FROM containers WHERE closed != 3 AND brand = '".$brand."' order by id desc") or die($mysqli->error);

    ?>
<div class="row">
	<div class="col-md-4">
		<h2><?= $pagetitle ?></h2>
	</div>

    <div class="col-md-4 col-sm-4">

        <div class="btn-group">

            <a href="?brand=IQue"  style="padding: 5px 11px !important;" class="btn <?php if ($brand == 'IQue') {echo 'btn-primary';} else {echo 'btn-white';}?>">IQue</a>
            <a href="?brand=Lovia"  style="padding: 5px 11px !important;" class="btn <?php if ($brand == 'Lovia') {echo 'btn-primary';} else {echo 'btn-white';}?>">Lovia</a>
            <a href="?brand=Quantum"  style="padding: 5px 11px !important;" class="btn <?php if ($brand == 'Quantum') {echo 'btn-primary';} else {echo 'btn-white';}?>">Quantum</a>
            <a href="?brand=Pergola"  style="padding: 5px 11px !important;" class="btn <?php if ($brand == 'Pergola') {echo 'btn-primary';} else {echo 'btn-white';}?>">Pergola</a>
            <a href="?brand=Espoo Smart" style="padding: 5px 11px !important;" class="btn <?php if ($brand == 'Espoo Smart') {echo 'btn-primary';} else {echo 'btn-white';}?>">Espoo Smart</a>
            <a href="?brand=Espoo Deluxe"  style="padding: 5px 11px !important;" class="btn <?php if ($brand == 'Espoo Deluxe') {echo 'btn-primary';} else {echo 'btn-white';}?>">Espoo Deluxe</a>

        </div>
    </div>

    <div class="col-md-4 col-sm-4">
		<a href="editace-kontejneru?action=add_container&brand=<?= $brand ?>" style=" margin-right: 14px; float:right;" class="btn btn-default btn-icon icon-left btn-lg">
			<i class="entypo-plus"></i>
			Přidat kontejner
		</a>

	</div>
</div>

<?php
    $containers_harmo_query = $mysqli->query("SELECT * FROM containers WHERE closed != 3 AND date_due != '0000-00-00' AND date_shipped != '0000-00-00'  AND brand = '".$brand."' order by date_due asc") or die($mysqli->error);

    if (mysqli_num_rows($containers_harmo_query) > 0) {

        ?>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<script type="text/javascript">

  google.charts.load("current", {packages:["timeline"], 'language': 'cs'});
  google.charts.setOnLoadCallback(drawChart);
  function drawChart() {
    var container = document.getElementById('example2.2');
    var chart = new google.visualization.Timeline(container);
    var dataTable = new google.visualization.DataTable();
    dataTable.addColumn({ type: 'string', id: 'Term' });
    dataTable.addColumn({ type: 'string', id: 'Name' });
  	dataTable.addColumn({ type: 'string', role: 'style' });
    dataTable.addColumn({ type: 'date', id: 'Start' });
    dataTable.addColumn({ type: 'date', id: 'End' });
    dataTable.addRows([
    	<?php
        $i = 0;

        while ($cont_harmo = mysqli_fetch_array($containers_harmo_query)) {

            if ($cont_harmo['container_name'] != "") {$name = $cont_harmo['container_name'];} else { $name = $cont_harmo['id'];}

            if(!empty($cont_harmo['date_closed']) && $cont_harmo['date_closed'] != '0000-00-00'){
                 $dateValue = strtotime($cont_harmo['date_closed']);
            }else{
                $dateValue = strtotime(date('Y-m-d'));
            }

            $yr = date("Y", $dateValue);
            $mon = date("m", $dateValue);
            $date = date("d", $dateValue);

            $dateValue_deliv = strtotime($cont_harmo['date_due']);

            $yr_deliv = date("Y", $dateValue_deliv);
            $mon_deliv = date("m", $dateValue_deliv);
            $date_deliv = date("d", $dateValue_deliv);

            if ($cont_harmo['date_correction'] != 0) {

                $color = '#0072bc';

            } else {

                $color = '#ff9600';

            }

            $i++;
            ?>
      [ '<?= $i ?>', 'Kontejner <?= $name ?> - [<?= $cont_harmo['size'] ?>]', '<?= $color ?>', new Date(<?= $yr ?>, <?= $mon ?>, <?= $date ?>), new Date(<?= $yr_deliv ?>, <?= $mon_deliv ?>, <?= $date_deliv ?>) ],<?php } ?>

      ]);


    var options = {
      timeline: { showRowLabels: false }
    };

    chart.draw(dataTable, options);
  }

</script>
<?php $height = $i * 50;?>
<div id="example2.2" style="margin: 16px 0; height: <?= $height ?>px;"></div>
<?php } ?>

    <script>


        // main files
        $(document).on('submit', '.file_form', function(event) {

            //disable the default form submission
            event.preventDefault();


            const file = $(this).find('input[type="file"]').val().trim(); // consider giving this an id too


            if(file){

                const id = $(this).data("id");
                const type = $(this).data("type");
                const container_id = $(this).data("container");

                const main = '#' + container_id + "-" + id;


                const formData = new FormData($(this)[0]);

                $(main + " .holder").hide();
                $(main).append('<div style="background-color: #FFF;"><img class="loading" src="https://www.wellnesstrade.cz/admin/assets/images/loader_backinout.gif" width="100%"><img class="done" src="https://www.wellnesstrade.cz/admin/assets/images/tick-confirmed.gif" width="100%" style="display: none;"></div>').fadeIn(400);

                const url = "?id="+container_id+"&action=add_file&type="+type;

                $.ajax({
                    type: "POST",
                    url: url,
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(data) {

                        $(main + " .loading").fadeOut(400, function() {
                            $(main + " .done").fadeIn(400);
                        });

                        setTimeout(function() {

                            $(main + " .holder").load("/admin/controllers/modals/container?id="+container_id+" #"+container_id+"-" + id + " .holder > *", function() {

                                $(main + " .done").fadeOut(400, function(){
                                    $(main + " .holder").fadeIn(400)
                                });

                            });

                        }, 2000);



                    },
                    error: function(){
                        alert("error in ajax form submission");
                    }
                });


            }else{
                alert('No file attached');
            }

            return false; // avoid to execute the actual submit of the form.
        });




        $(document).on('click', '.remove-file', function() {

            var name = $(this).data("name");
            var id = $(this).data("id");
            var container_id = $(this).data("container");

            $.get( "?id="+container_id+"&action=remove_file&name=" + name )
                .done(function( data ) {

                    $("#"+container_id+"-"+id+" .holder").fadeOut(300, function(){

                        $("#"+container_id+"-" + id + " .holder").load("/admin/controllers/modals/container?id="+container_id+" #"+container_id+"-" + id + " .holder > *", function() {

                            $(this).fadeIn(400)

                        });;

                    });


                });


        });

        // main files end



        $(document).on('submit', '.file_form_hottub', function(event) {

            //disable the default form submission
            event.preventDefault();

            const file = $(this).find('input[type="file"]').val().trim(); // consider giving this an id too

            if(file){

                const id = $(this).data("id");
                const formData = new FormData($(this)[0]);
                const container_id = $(this).data("container");

                const mainHolder = "#pdf-" + id;


                $(mainHolder + " .pdf-inner-holder").hide();

                $(mainHolder).append('<img class="loading" src="https://www.wellnesstrade.cz/admin/assets/images/loader-small.gif" height="36" style="float: right;">').fadeIn(400);

                const url = "?id="+container_id+"&action=add_file_hottub&hottub_id="+id;

                $.ajax({
                    type: "POST",
                    url: url,
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(data) {

                        setTimeout(function() {

                            $(mainHolder + " .pdf-inner-holder").load("/admin/controllers/modals/container?id="+container_id+" #pdf-"+id+" > *", function() {

                                $(mainHolder + " .loading").fadeOut(400, function(){
                                    $(mainHolder + " .pdf-inner-holder").fadeIn(400)
                                });

                            });

                        }, 2000);



                    },
                    error: function(){
                        alert("error in ajax form submission");
                    }
                });


            }else{
                alert('No file attached');
            }

            return false; // avoid to execute the actual submit of the form.
        });



        $(document).on('click', '.remove_file_hottub', function() {

            var name = $(this).data("name");
            var id = $(this).data("id");
            const container_id = $(this).data("container");

            $.get( "?id="+container_id+"&action=remove_file_hottub&hottub_id="+id+"&name=" + name )
                .done(function( data ) {

                    $("#pdf-" + id +" .pdf-inner-holder").fadeOut(300, function(){

                        $("#pdf-" + id +" .pdf-inner-holder").load("/admin/controllers/modals/container?id="+container_id+" #pdf-" + id +" .pdf-inner-holder > *", function() {

                            $(this).fadeIn(400)

                        });;

                    });


                });


        });
    </script>
<?php

    if (mysqli_num_rows($containers_query) > 0) {
        mysqli_data_seek($containers_query, 0);
        while ($containers = mysqli_fetch_assoc($containers_query)) {
            $containers['value'] = $special_access['value'];
            containers($containers);
        }} else { ?>
<ul class="cbp_tmtimeline" style=" margin-left: 25px;">
  <li style="margin-top: 80px;">

		<div class="cbp_tmicon">
			<i class="entypo-block" style="line-height: 42px !important;"></i>
		</div>

		<div class="cbp_tmlabel empty" style="padding-top: 9px;">
			<span><a style="font-weight: bold; margin-left: -12px;font-size: 17px;">Bohužel tomuto filtru neodpovídá žádný kontejner.</a></span>
		</div>
	</li>
  </ul>
<?php
    }

}

$perpage = 12;
$containers_max_query = $mysqli->query("SELECT COUNT(*) AS NumberOfOrders FROM containers WHERE closed = '3' AND brand = '".$brand."' order by id") or die($mysqli->error);
$containers_max = mysqli_fetch_array($containers_max_query);
$max = $containers_max['NumberOfOrders'];
if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {$od = 1;}

$s_lol = $od - 1;
$s_pocet = $s_lol * $perpage;
$pocet_prispevku = $max;

$containers_query = $mysqli->query("SELECT *, DATE_FORMAT(date_created, '%d. %m. %Y') as dateformated, DATE_FORMAT(date_due, '%d. %m. %Y') as due_formated, DATE_FORMAT(date_received, '%d. %m. %Y') as received_formated FROM containers WHERE closed = '3' AND brand = '".$brand."' order by id desc limit " . $s_pocet . "," . $perpage) or die($mysqli->error);
?>
<div class="row">
	<div class="col-md-4">
		<h2 style="margin-top: 20px;">Převzaté kontejnery</h2>
	</div>

		<div class="col-md-4">
		<center><ul class="pagination pagination-sm">
			<?php
include VIEW . "/default/pagination.php";?>
		</ul>

	</center>
	</div>

</div>

<?php

if (mysqli_num_rows($containers_query) > 0) {
    mysqli_data_seek($containers_query, 0);
    while ($containers = mysqli_fetch_assoc($containers_query)) {
        $containers['value'] = $special_access['value'];

        containers($containers);


    }} else { ?>
<ul class="cbp_tmtimeline" style=" margin-left: 25px;">
  <li style="margin-top: 80px;">

		<div class="cbp_tmicon">
			<i class="entypo-block" style="line-height: 42px !important;"></i>
		</div>

		<div class="cbp_tmlabel empty" style="padding-top: 9px;">
			<span><a style="font-weight: bold; margin-left: -12px;font-size: 17px;">Bohužel tomuto filtru neodpovídá žádný kontejner.</a></span>
		</div>
	</li>
  </ul>
<?php
}

?>




<!-- Pager for search results --><div class="row">
	<div class="col-md-12">
		<center><ul class="pagination pagination-sm">
			<?php
include VIEW . "/default/pagination.php";?>
		</ul>

		<h1 style="margin-bottom: 50px;">Celkem: <?= $max ?></h1>
	</center>
	</div>
</div><!-- Footer -->





<?php
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$total_time = round(($finish - $start), 4);

?>
<footer class="main">


	&copy; <?= date("Y") ?> <span style=" float:right;"><?= 'Page generated in ' . $total_time . ' seconds.' ?></span>

</footer>	</div>



	</div>


	<script type="text/javascript">
$(document).ready(function(){
    $(".toggle-modal-remove").click(function(e){

			$('#remove-modal').removeData('bs.modal');
    	 e.preventDefault();


    	 var type = $(this).data("type");

    	 var id = $(this).data("id");

        $("#remove-modal").modal({

            remote: '/admin/controllers/modals/modal-remove.php?id='+id+'&type='+type+'&od=<?= $od ?>',
        });
    });
});
</script>


<div class="modal fade" id="remove-modal" aria-hidden="true" style="display: none; margin-top: 10%;">

</div>



	<script type="text/javascript">

        function calc_price(){

            var sum = 0; // Recompute total sum per change.

            $('#first-payment-modal .price-control:visible').each(function() {
                var x = $(this).val(); // Get the number and make sure it exists.
                sum += parseFloat(x || 0);
            });

            var deposit_usd = sum * 0.3;
            $('#first-payment-modal #total_usd').html(sum.toLocaleString('en-US', {style:'currency', currency:'USD'}))
            $('#first-payment-modal #deposit_usd').html(deposit_usd.toLocaleString('en-US', {style:'currency', currency:'USD'}));

            var exchange_rate = $('.exchange_rate').val();
            var sum_czk = sum * exchange_rate;

            var deposit_czk = deposit_usd * exchange_rate;

            $('#first-payment-modal #total_czk').html(sum_czk.toLocaleString('cs-CZ', {style:'currency', currency:'CZK'}));
            $('#first-payment-modal #deposit_czk').html(deposit_czk.toLocaleString('cs-CZ', {style:'currency', currency:'CZK'}));

        }

        function calc_second_price(){

            var sum = 0; // Recompute total sum per change.
            var deposit = $('#deposit').val();

            $('#second-payment-modal .price-control:visible').each(function() {
                var x = $(this).val(); // Get the number and make sure it exists.
                sum += parseFloat(x || 0);
            });

            var final_usd = sum - deposit;

            $('#second-payment-modal #new_total_usd').html(sum.toLocaleString('en-US', {style:'currency', currency:'USD'}))
            $('#second-payment-modal #final_payment_usd').html(final_usd.toLocaleString('en-US', {style:'currency', currency:'USD'}));

            var exchange_rate = $('.exchange_rate').val();
            var sum_czk = sum * exchange_rate;

            console.log(sum);

            var final_czk = final_usd * exchange_rate;

            $('#second-payment-modal #new_total_czk').html(sum_czk.toLocaleString('cs-CZ', {style:'currency', currency:'CZK'}));
            $('#second-payment-modal #final_payment_czk').html(final_czk.toLocaleString('cs-CZ', {style:'currency', currency:'CZK'}));

        }

$(document).ready(function(){


    $(".toggle-first-payment").click(function(e){

        $('#first-payment-modal').removeData('bs.modal');
        $('#first-payment-modal .modal-body').html('');

    	 e.preventDefault();

    	 var type = $(this).data("type");

    	 var id = $(this).data("id");



        $("#first-payment-modal").modal({

                remote: '/admin/controllers/modals/container-first-payment.php?id='+id,

        });


        setTimeout(function(){
            calc_price();
        }, 1000);


    });

    $('#first-payment-modal').on('shown.bs.modal', function (e) {
        calc_price();
    });





    $(".toggle-second-payment").click(function(e){

        $('#second-payment-modal').removeData('bs.modal');
        $('#second-payment-modal .modal-body').html('');

        e.preventDefault();

        var type = $(this).data("type");

        var id = $(this).data("id");



        $("#second-payment-modal").modal({

            remote: '/admin/controllers/modals/container-second-payment.php?id='+id,

        });

        setTimeout(function(){
            calc_second_price();
        }, 1000);

    });

    $('#second-payment-modal').on('shown.bs.modal', function (e) {
        calc_second_price();
    });

});
</script>


<div class="modal fade" id="first-payment-modal" aria-hidden="true" style="display: none; margin-top: 2%;">

</div>

<div class="modal fade" id="second-payment-modal" aria-hidden="true" style="display: none; margin-top: 2%;">

</div>

<script type="text/javascript">
$(document).ready(function(){
    $(".toggle-modal-merge").click(function(e){

			$('#merge-modal').removeData('bs.modal');
    	 e.preventDefault();


    	 var id = $(this).data("id");

        $("#merge-modal").modal({

            remote: '/admin/controllers/modals/modal-merge-containers.php?id='+id,
        });
    });
});
</script>


<div class="modal fade" id="merge-modal" aria-hidden="true" style="display: none; margin-top: 8%;">


</div>


<script type="text/javascript">
    $(".toggle-modal-transfer").click(function(e){

			$('#transfer-modal').removeData('bs.modal');
    	 e.preventDefault();


    	 var id = $(this).data("id");

        $("#transfer-modal").modal({

            remote: '/admin/controllers/modals/modal-transfer-containers.php?id='+id,
        });
    });
</script>


<div class="modal fade" id="transfer-modal" aria-hidden="true" style="display: none; margin-top: 8%;">


</div>



<script type="text/javascript">
$(document).ready(function(){
    $(".toggle-modal-add").click(function(e){

			$('#add-to-container-modal').removeData('bs.modal');
    	 e.preventDefault();


    	 var id = $(this).data("id");

        $("#add-to-container-modal").modal({

            remote: '/admin/controllers/modals/modal_add_to_container.php?id='+id,
        });
    });
});
</script>


<div class="modal fade" id="add-to-container-modal" aria-hidden="true" style="display: none; margin-top: 2%;">


</div>






<div class="modal fade" id="edit-container-product-modal" aria-hidden="true" style="display: none; margin-top: 0px;">


</div>




<script type="text/javascript">
$(document).ready(function(){
    $(".toggle-add-codes").click(function(e){

			$('#add-codes-modal').removeData('bs.modal');
    	 e.preventDefault();


    	 var id = $(this).data("id");

        $("#add-codes-modal").modal({

            remote: '/admin/controllers/modals/modal-container-add-codes.php?id='+id,
        });
    });
});
</script>


<div class="modal fade" id="add-codes-modal" aria-hidden="true" style="display: none; margin-top: 2%;">

</div>



<script type="text/javascript">
$(document).ready(function(){
    $(".toggle-date-modal").click(function(e){

			$('#date-modal').removeData('bs.modal');
    	 e.preventDefault();


    	 var id = $(this).data("id");

        $("#date-modal").modal({

            remote: '/admin/controllers/modals/modal-container-date.php?id='+id,
        });
    });
});
</script>
    <div class="modal fade" id="date-modal" aria-hidden="true" style="display: none; margin-top: 2%;"></div>



<script type="text/javascript">
    $(document).ready(function(){
        $(".toggle-receive-modal").click(function(e){

            $('#receive-modal').removeData('bs.modal');
            e.preventDefault();

            var id = $(this).data("id");

            $("#receive-modal").modal({
                remote: '/admin/controllers/modals/modal-container-receive.php?id='+id,
            });
        });
    });
</script>
    <div class="modal fade" id="receive-modal" aria-hidden="true" style="display: none; margin-top: 2%;"></div>


<script type="text/javascript">
$(document).ready(function(){
    $(".toggle-delivery-modal").click(function(e){

		$('#delivery-modal').removeData('bs.modal');
    	 e.preventDefault();


    	 var id = $(this).data("id");

        $("#delivery-modal").modal({

            remote: '/admin/controllers/modals/modal-container-delivery.php?id='+id,
        });
    });
});
</script>


<div class="modal fade" id="delivery-modal" aria-hidden="true" style="display: none; margin-top: 8%;">

</div>





<script type="text/javascript">

    $(".show-container").click(function(){

      var container = $(this).data("container");

      if ( $('#container-holder-'+container).text().length == 0 ) {

            $("#container-holder-"+container).load("/admin/controllers/modals/container?id="+container);

  	  }else{

  	  		$('#container-holder-'+container).text('');

  	  };


   });



</script>




<div class="modal fade" id="picture-upload-modal" aria-hidden="true" style="display: none; top: 8%;">
  <div class="modal-dialog" style="width: 800px;">
    <div class="modal-content">

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title">Nahrání obrázků</h4>
      </div>

      <div class="modal-body">
        <form action="#" class="dropzone" id="dropzone_upload">
          <div class="fallback">
            <input name="file" type="file" multiple />
          </div>
        </form>


        <div id="pictures-result">

	 	</div>
      </div>

      <div class="modal-footer" style="text-align:left;">
        <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>
        <button type="button" class="btn btn-success btn-icon icon-left" id="done-picture-upload" data-dismiss="modal" style="float: right;">Hotovo <i class="entypo-check"></i></button>
      </div>

    </div>
  </div>
</div>



<script type="text/javascript">
    $(document).ready(function(){
        $(".toggle-default-modal").click(function(e){

            $('#default-modal').removeData('bs.modal');
            e.preventDefault();


            var type = $(this).data("type");

            var id = $(this).data("id");

            $("#default-modal").modal({

                remote: '/admin/controllers/modals/default.php?id='+id+'&type='+type,
            });
        });
    });
</script>


<div class="modal fade" id="default-modal" aria-hidden="true" style="display: none; margin-top: 160px;">

</div>



		<script type="text/javascript">
            $(document).ready(function() {

                $('.lightgallery').lightGallery({
                    selector: 'a.full'
                });

            });
    </script>
<?php include VIEW . '/default/footer.php'; ?>