<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";

include INCLUDES . "/functions.php";

$id = $_REQUEST['id'];

$getclientquery = $mysqli->query('SELECT *, d.customer as customer, d.id as id, DATE_FORMAT(d.date, "%d. %m. %Y") as dateformated, DATE_FORMAT(d.realization, "%d. %m. %Y") as realizationformated, DATE_FORMAT(d.realtodate, "%d. %m. %Y") as realtodateformat FROM demands d, warehouse_products p WHERE p.connect_name = d.product AND d.id="' . $id . '"') or die($mysqli->error);
$getclient = mysqli_fetch_assoc($getclientquery);

$address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $getclient['shipping_id'] . '" WHERE b.id = "' . $getclient['billing_id'] . '"') or die($mysqli->error);
$address = mysqli_fetch_assoc($address_query);

$spesl = " - " . $address['billing_name'] . " " . $address['billing_surname'];

$pagetitle = 'Údaje pro generování';

$bread1 = "Editace poptávek";
$abread1 = "editace-poptavek";

$bread2 = $address['billing_name'] . " " . $address['billing_surname'];
$abread2 = "zobrazit-poptavku?id=" . $id;






if($client['id'] == 212622222){
    $allDemandsQuery = $mysqli->query("SELECT id, showroom FROM demands WHERE status != 6") or die($mysqli->error);
    while ($allDemands = mysqli_fetch_assoc($allDemandsQuery)) {

        $accessories = $mysqli->query("SELECT id, product_id from demands_accessories_bridge WHERE aggregate_id = '".$allDemands['id']."' ")or die($mysqli->error);
        while($access = mysqli_fetch_assoc($accessories)){

            $find_accessories = $mysqli->query("SELECT id, product_id from demands_accessories_bridge WHERE aggregate_id = '".$allDemands['id']."' AND product_id = '".$access['product_id']."'")or die($mysqli->error);

            if(mysqli_num_rows($accessories) > 1){
                echo $allDemands['id'].'<br>';

                $limit = mysqli_num_rows($find_accessories) - 1;

                $mysqli->query("DELETE FROM demands_accessories_bridge WHERE aggregate_id = '".$allDemands['id']."' AND product_id = '".$access['product_id']."' LIMIT $limit")or die($mysqli->error);

            }


        }


    }

}
$i = 0;
if ($client['id'] == 21262222) {

    $allDemandsQuery = $mysqli->query("SELECT id, showroom FROM demands WHERE status != 6") or die($mysqli->error);


    function exchange_specs($allDemands, $product_id, $singleSpec)
    {

        global $i;
        global $mysqli;

        $find_query = $mysqli->query("SELECT id FROM demands_accessories_bridge WHERE aggregate_id = '" . $allDemands['id'] . "' AND product_id = '" . $product_id . "'") or die($mysqli->error);

        if (mysqli_num_rows($find_query) == 0) {


            $product_query = $mysqli->query("SELECT * FROM products WHERE id = '" . $product_id . "'") or die($mysqli->error);
            $product = mysqli_fetch_assoc($product_query);

            $mysqli->query("INSERT INTO demands_accessories_bridge 
                                                (aggregate_id, product_id, product_name, purchase_price, price, original_price, quantity, reserved, location_id) VALUES
                                                ('" . $allDemands['id'] . "', '" . $product['id'] . "', '" . $product['productname'] . "', '" . $product['purchase_price'] . "', '" . $singleSpec['price'] . "', '" . $product['price'] . "', 1, 1, '" . $allDemands['showroom'] . "')") or die($mysqli->error);

            $i++;
            echo $i;
        }

    }


    while ($allDemands = mysqli_fetch_assoc($allDemandsQuery)) {

        $allSpecsQuery = $mysqli->query("SELECT * FROM demands_specs_bridge WHERE client_id = '" . $allDemands['id'] . "' AND value != ''") or die($mysqli->error);

        while ($singleSpec = mysqli_fetch_assoc($allSpecsQuery)) {

//                                      33 Automatický dávkovač - více variant 4125
            if ($singleSpec['specs_id'] == 33 && $singleSpec['value'] == 'Ano') {

                $product_id = 4125;
                exchange_specs($allDemands, $product_id, $singleSpec);

            }

//                                      34 Automatický dávkovač pH 4124
            if ($singleSpec['specs_id'] == 34 && $singleSpec['value'] == 'Ano') {

                $product_id = 4124;
                exchange_specs($allDemands, $product_id, $singleSpec);

            }

//                                      39 FIBALON 4705
            if ($singleSpec['specs_id'] == 39 && $singleSpec['value'] == 'Ano') {

                $product_id = 4705;
                exchange_specs($allDemands, $product_id, $singleSpec);

            }

//                                      41 Filter Cleaner 4667
            if ($singleSpec['specs_id'] == 41 && $singleSpec['value'] == 'Ano') {

                $product_id = 4667;
                exchange_specs($allDemands, $product_id, $singleSpec);

            }

//                                      6 Písková filtrace 4126
            if ($singleSpec['specs_id'] == 6 && $singleSpec['value'] == 'Ano') {

                $product_id = 4126;
                exchange_specs($allDemands, $product_id, $singleSpec);

            }

//                                      31 Spa Caddy 2357
            if ($singleSpec['specs_id'] == 31 && $singleSpec['value'] == 'Ano') {

                $product_id = 2357;
                exchange_specs($allDemands, $product_id, $singleSpec);

            }

//                                      37 Spa Sponge 243
            if ($singleSpec['specs_id'] == 37 && $singleSpec['value'] == 'Ano') {

                $product_id = 243;
                exchange_specs($allDemands, $product_id, $singleSpec);

            }

//                                      38 Water Wand 241
            if ($singleSpec['specs_id'] == 38 && $singleSpec['value'] == 'Ano') {

                $product_id = 241;
                exchange_specs($allDemands, $product_id, $singleSpec);

            }

//                                      42 TowelBar 2367
            if ($singleSpec['specs_id'] == 42 && $singleSpec['value'] == 'Ano') {

                $product_id = 2367;
                exchange_specs($allDemands, $product_id, $singleSpec);

            }


//                                      30 Držák na kryt - více variant
            if ($singleSpec['specs_id'] == 30) {


//                                    CoverMate I 234
//                                    CoverMate II understyle 4134
//                                    CoverMate III 235
//                                    CoverMate Vanish 3632
//                                    CoverMate Eco 2463
//                                    CoverCatch 3634
//                                    CoverMate Vanish XL 4335

                $product_id = '';
                $product_name = '';
                $original_price = '';
                $purchase_price = '';

                if ($singleSpec['value'] == 'CoverMate I') {

                    $product_id = 234;
                    $product_name = 'CoverMate';
                    $original_price = 9560;
                    $purchase_price = 4079;

                }

                if ($singleSpec['value'] == 'CoverMate II understyle') {

                    $product_id = 4134;
                    $product_name = 'CoverMate II UNDERSTYLE';
                    $original_price = 10770;
                    $purchase_price = 5089;

                }

                if ($singleSpec['value'] == 'CoverMate III') {

                    $product_id = 235;
                    $product_name = 'CoverMate III';
                    $original_price = 16820;
                    $purchase_price = 5260;

                }

                if ($singleSpec['value'] == 'CoverMate Vanish') {

                    $product_id = 3632;
                    $product_name = 'CoverMate Vanish';
                    $original_price = 22870;
                    $purchase_price = 7576;

                }

                if ($singleSpec['value'] == 'CoverMate Eco') {

                    $product_id = 2463;
                    $product_name = 'CoverMate I EKO';
                    $original_price = 5940;
                    $purchase_price = 3430;

                }

                if ($singleSpec['value'] == 'CoverCatch') {

                    $product_id = 3634;
                    $product_name = 'CoverCatch';
                    $original_price = 2500;
                    $purchase_price = 1486;

                }

                if ($singleSpec['value'] == 'CoverMate Vanish XL') {

                    $product_id = 4335;
                    $product_name = 'CoverMate Vanish XL';
                    $original_price = 24080;
                    $purchase_price = 6140;

                }

                if (!empty($product_id)) {

                       $find_query = $mysqli->query("SELECT id FROM demands_accessories_bridge WHERE aggregate_id = '" . $allDemands['id'] . "' AND product_id = '" . $product_id . "'") or die($mysqli->error);

                    if (mysqli_num_rows($find_query) == 0) {

                        $mysqli->query("INSERT INTO demands_accessories_bridge 
                                                            (aggregate_id, product_id, product_name, purchase_price, price, original_price, quantity, reserved, location_id) VALUES
                                                            ('" . $allDemands['id'] . "', '" . $product_id . "', '" . $product_name . "', '" . $purchase_price . "', '" . $singleSpec['price'] . "',  '" . $original_price . "', 1, 1, '" . $allDemands['showroom'] . "')") or die($mysqli->error);

                    }

                }


            }


//                                      40 Madlo Safe-T-Rail - více variant
            if ($singleSpec['specs_id'] == 40 && $singleSpec['value'] == 'Ano') {

                echo $allDemands['id'].' safe-t-rail<br>';


                $find_query = $mysqli->query("SELECT id FROM demands_accessories_bridge WHERE aggregate_id = '" . $allDemands['id'] . "' AND product_id = '240' AND variation_id = '294'") or die($mysqli->error);

                if (mysqli_num_rows($find_query) == 0) {

                $mysqli->query("INSERT INTO demands_accessories_bridge 
                                                (aggregate_id, product_id, variation_id, product_name, purchase_price, price, original_price, quantity, reserved, location_id) VALUES
                                                ('" . $allDemands['id'] . "', 240, 294,'Madlo Safe-T-Rail - Provedení: Černá', '3680', '" . $singleSpec['price'] . "', '5960', 1, 1, '" . $allDemands['showroom'] . "')") or die($mysqli->error);

                }



            }


//                                      32 Madlo Safe-T-Rail II - více variant
            if ($singleSpec['specs_id'] == 32 && $singleSpec['value'] == 'Ano') {


                $find_query = $mysqli->query("SELECT id FROM demands_accessories_bridge WHERE aggregate_id = '" . $allDemands['id'] . "' AND product_id = '2362' AND variation_id = '301'") or die($mysqli->error);

                if (mysqli_num_rows($find_query) == 0) {

                    $mysqli->query("INSERT INTO demands_accessories_bridge 
                                                (aggregate_id, product_id, variation_id, product_name, purchase_price, price, original_price, quantity, reserved, location_id) VALUES
                                                ('" . $allDemands['id'] . "', '2362', '301', 'Madlo Safe-T-Rail II - Provedení: Černá ', '3680', '" . $singleSpec['price'] . "', '5960', 1, 1, '" . $allDemands['showroom'] . "')") or die($mysqli->error);

                }

            }


//                                30 Držák na kryt - více variant
//
//                                    CoverMate I 234
//                                    CoverMate II understyle 4134
//                                    CoverMate III 235
//                                    CoverMate Vanish 3632
//                                    CoverMate Eco 2463
//                                    CoverCatch 3634
//                                    CoverMate Vanish XL 4335
//
//
//                                40 Madlo Safe-T-Rail - více variant
//                                    240 var 294
//
//
//                                32 Madlo Safe-T-Rail II - více variant
//                                    2362 var 301


        }


    }


    /*

    33 Automatický dávkovač - více variant 4125
    34 Automatický dávkovač pH 4124


    30 Držák na kryt - více variant

        CoverMate I 234
        CoverMate II understyle 4134
        CoverMate III 235
        CoverMate Vanish 3632
        CoverMate Eco 2463
        CoverCatch 3634
        CoverMate Vanish XL 4335


    39 FIBALON 4705
    41 Filter Cleaner 4667

    40 Madlo Safe-T-Rail - více variant
        240 var 294


    32 Madlo Safe-T-Rail II - více variant
        2362 var 301


    6 Písková filtrace 4126
    31 Spa Caddy 2357
    37 Spa Sponge 243
    38 Water Wand 241
    42 TowelBar 2367
     */

}


if (isset($_REQUEST['action']) && $_REQUEST['action'] == "save_data") {

    if (isset($_POST['deposit_type']) && $_POST['deposit_type'] == 'percentage') {

        $deposit = $_POST['percentage_deposit'];
        $deposit_second = $_POST['percentage_deposit_second'];
        $deposit_third = $_POST['percentage_deposit_third'];
        $deposit_fourth = $_POST['percentage_deposit_fourth'];

    } else {

        $deposit = $_POST['money_deposit'];
        $deposit_second = $_POST['money_deposit_second'];
        $deposit_third = $_POST['money_deposit_third'];
        $deposit_fourth = $_POST['money_deposit_fourth'];

    }


    $currency = $_POST['currency'];

    $exchange_rate = $_POST[$currency.'_rate'];

    $check_data = $mysqli->query("SELECT * FROM demands_generate WHERE id = '$id'");
    if (mysqli_num_rows($check_data) == 0) {

        if (isset($_POST['price_vat']) && $_POST['price_vat'] == "") {$_POST['price_vat'] = null;}

        $mysqli->query("INSERT INTO demands_generate (id,
                              affidavit,
        currency,
        exchange_rate,
		deposit_type,
		deposit,
		deposit_second,
		deposit_third,
		deposit_fourth,
		invoices_number,
		reverse_charge, other_arrangements, other_purchase_protocol, price_vat) 
		VALUES ('$id',
		        '".$_POST['affidavit']."',
		                                                                                
		'".$_POST['currency']."',
		'".$exchange_rate."',
		'" . $_POST['deposit_type'] . "',
		'$deposit',
		'$deposit_second',
		'$deposit_third',
		'$deposit_fourth',
		'" . $_POST['invoices_number'] . "',
		'" . $_POST['reverse_charge'] . "','" . $_POST['other_arrangements'] . "' ,'" . $_POST['other_purchase_protocol'] . "',
			 " . ($_POST['price_vat'] == null ? "NULL" : "'" . $_POST['price_vat'] . "'") . ")") or die($mysqli->error);

    } else {

        $mysqli->query("UPDATE demands_generate 
    SET
        currency = '".$_POST['currency']."',
        affidavit = '".$_POST['affidavit']."',
        exchange_rate = '".$exchange_rate."',
		deposit_type = '" . $_POST['deposit_type'] . "',
		deposit = '$deposit',
		deposit_second = '$deposit_second',
		deposit_third = '$deposit_third',
		deposit_fourth = '$deposit_fourth',
		invoices_number = '" . $_POST['invoices_number'] . "',
		reverse_charge = '" . $_POST['reverse_charge'] . "', other_arrangements = '" . $_POST['other_arrangements'] . "', other_purchase_protocol = '" . $_POST['other_purchase_protocol'] . "',
		price_vat = " . ($_POST['price_vat'] == null ? "NULL" : "'" . $_POST['price_vat'] . "'") . " WHERE id = '$id'") or die($mysqli->error);

    }

    $billing_zipcode = preg_replace('/\s+/', '', $_POST['billing_zipcode']);
    $billing_phone = preg_replace('/\s+/', '', $_POST['billing_phone']);
    $billing_email = preg_replace('/\s+/', '', $_POST['billing_email']);

    $mysqli->query("UPDATE addresses_billing SET billing_company = '" . $_POST['billing_company'] . "', billing_name = '" . $_POST['billing_name'] . "', billing_degree = '" . $_POST['billing_degree'] . "', billing_surname = '" . $_POST['billing_surname'] . "', billing_street = '" . $_POST['billing_street'] . "', billing_city = '" . $_POST['billing_city'] . "', billing_zipcode = '" . $billing_zipcode . "', billing_country = '" . $_POST['billing_country'] . "',  billing_ico = '" . $_POST['billing_ico'] . "', billing_dic = '" . $_POST['billing_dic'] . "', billing_email = '" . $billing_email . "', billing_phone = '" . $billing_phone . "' WHERE id = '" . $getclient['billing_id'] . "'") or die($mysqli->error);

    if (isset($getclient['customer']) && $getclient['customer'] == 1 || $getclient['customer'] == 3) {

        $choosed_hottub = $_POST['virivkatype'];

        $choosed_type = $_POST['provedeni_' . $choosed_hottub];

        $get_ids = $mysqli->query("SELECT w.id as id, w.name as name FROM warehouse_products_types w, warehouse_products p WHERE w.warehouse_product_id = p.id AND p.connect_name = '$choosed_hottub' AND w.seo_url = '$choosed_type'") or die($mysqli->error);
        $get_id = mysqli_fetch_array($get_ids);

        ///provedení

        $find_query = $mysqli->query("SELECT id FROM demands_specs_bridge WHERE client_id = '" . $_REQUEST['id'] . "' AND specs_id = '5'") or die($mysqli->error);
        if (mysqli_num_rows($find_query) > 0) {

            $find = mysqli_fetch_array($find_query);
            $insert_specs = $mysqli->query("UPDATE demands_specs_bridge SET value = '" . $get_id['name'] . "' WHERE id = '" . $find['id'] . "'") or die($mysqli->error);

        } else {

            $insert_specs = $mysqli->query("INSERT INTO demands_specs_bridge (value, client_id, specs_id) VALUES ('" . $get_id['name'] . "','" . $_REQUEST['id'] . "','5')") or die($mysqli->error);

        }

        ///provedení

        // DEMAND SPECS

        $specs_query = $mysqli->query("SELECT *, s.id as id FROM specs s, warehouse_products_types_specs w WHERE w.spec_id = s.id AND w.type_id = '" . $get_id['id'] . "' AND s.is_demand = 1 GROUP BY s.id") or die($mysqli->error);

        while ($specs = mysqli_fetch_array($specs_query)) {

            $seoslug = $specs['seoslug'];

            $spec_value = $_POST[$choosed_hottub . '_' . $choosed_type . '_' . $seoslug];

            $spec_price = null;
            if (($spec_value != "Ne" && $spec_value != "") && $specs['generate'] == 1) {

                $spec_price = $_POST['price_' . $seoslug];

                if ($spec_price == "") {$spec_price = null;}

            } else {

                $_POST['price_' . $seoslug] = null;
                $spec_price = null;

            }

            if(!empty($_POST['is_generated_' . $seoslug])){

                $is_generated = 1;

            }else{

                $is_generated = 0;

            }

            $find_query = $mysqli->query("SELECT id FROM demands_specs_bridge WHERE client_id = '" . $_REQUEST['id'] . "' AND specs_id = '" . $specs['id'] . "'") or die($mysqli->error);
            if (mysqli_num_rows($find_query) > 0) {

                $find = mysqli_fetch_array($find_query);
                $mysqli->query("UPDATE demands_specs_bridge SET value = '$spec_value', is_generated = '$is_generated', price = " . ($spec_price == null ? "NULL" : "'$spec_price'") . " WHERE id = '" . $find['id'] . "'") or die($mysqli->error);

            } else {

                $mysqli->query("INSERT INTO demands_specs_bridge (value, client_id, specs_id, price, is_generated) VALUES ('$spec_value','" . $_REQUEST['id'] . "','" . $specs['id'] . "', " . ($spec_price == null ? "NULL" : "'$spec_price'") . ", '$is_generated')") or die($mysqli->error);

            }

        }

        // END DEMAND SPECS

        // NOT DEMAND SPECS

        $specs_query = $mysqli->query("SELECT *, s.id as id FROM specs s, warehouse_products_types_specs w WHERE w.spec_id = s.id AND w.type_id = '" . $get_id['id'] . "' AND s.is_demand = 0 GROUP BY s.id") or die($mysqli->error);

        while ($specs = mysqli_fetch_array($specs_query)) {

            if (isset($specs['type']) && $specs['type'] == 1) {

                $paramsquery = $mysqli->query("SELECT *, p.id as id FROM specs_params p, warehouse_products_types_specs w  WHERE p.spec_id = '" . $specs['id'] . "' AND w.spec_param_id = p.id AND w.type_id = '" . $get_id['id'] . "' AND w.choosed = 1 GROUP by p.id") or die($mysqli->error);

                $param = mysqli_fetch_array($paramsquery);

                $value = $param['option'];

            } else {

                $paramsquery = $mysqli->query("SELECT * FROM warehouse_products_types_specs WHERE spec_id = '" . $specs['id'] . "' AND type_id = '" . $get_id['id'] . "' AND choosed = 1 order by spec_param_id desc") or die($mysqli->error);

                $param = mysqli_fetch_array($paramsquery);

                if (isset($param['spec_param_id']) && $param['spec_param_id'] == 1) {$value = 'Ano';} else { $value = 'Ne';}

            }

            $find_query = $mysqli->query("SELECT id FROM demands_specs_bridge WHERE client_id = '" . $_REQUEST['id'] . "' AND specs_id = '" . $specs['id'] . "'") or die($mysqli->error);
            if (mysqli_num_rows($find_query) > 0) {

                $find = mysqli_fetch_array($find_query);
                $insert_specs = $mysqli->query("UPDATE demands_specs_bridge SET value = '$value' WHERE id = '" . $find['id'] . "'") or die($mysqli->error);

            } else {

                $insert_specs = $mysqli->query("INSERT INTO demands_specs_bridge (value, client_id, specs_id) VALUES ('$value','" . $_REQUEST['id'] . "','" . $specs['id'] . "')") or die($mysqli->error);

            }

        }

        // END NOT DEMAND SPECS

        // SPECS THAT SHOULD NOT BE IN DEMAND

        $specs_query = $mysqli->query("SELECT *, b.id as demandSpecId FROM specs s, demands_specs_bridge b WHERE 
                    b.specs_id = s.id AND b.client_id = '".$_REQUEST['id']."' AND s.id != 5 AND                                
                    s.id NOT IN (SELECT s.id as id FROM specs s, warehouse_products_types_specs w WHERE w.spec_id = s.id AND w.type_id = '" . $get_id['id'] . "' GROUP BY s.id) GROUP BY s.id") or die($mysqli->error);

        while ($specs = mysqli_fetch_assoc($specs_query)) {

            $mysqli->query("DELETE FROM demands_specs_bridge WHERE id = '".$specs['demandSpecId']."'")or die($mysqli->error);

        }

        // END SPECS THAT SHOULD NOT BE IN DEMAND




        $check_data_hottub = $mysqli->query("SELECT * FROM demands_generate_hottub WHERE id = '$id'");

        if (isset($_POST['price_hottub']) && $_POST['price_hottub'] == "") {$_POST['price_hottub'] = 0;}
        if (isset($_POST['price_montage']) && $_POST['price_montage'] == "") {$_POST['price_montage'] = 0;}
        if (isset($_POST['price_delivery']) && $_POST['price_delivery'] == "") {$_POST['price_delivery'] = 0;}
        if (isset($_POST['price_chemie']) && $_POST['price_chemie'] == "") {$_POST['price_chemie'] = 0;}
        if (isset($_POST['discount']) && $_POST['discount'] == "") {$_POST['discount'] = 0;}

        if (mysqli_num_rows($check_data_hottub) == 0) {

            $mysqli->query("INSERT INTO demands_generate_hottub (id,
        warranty_first,
		warranty_second,
		warranty_third,
		warranty_fourth,
		warranty_fifth,
		delivery_time,
		delivery_address,
		deadline_date,
		planned_date,
		price_hottub,
		chemie_type,
		price_montage,
		price_delivery,
		price_chemie,
		discount) VALUES ('$id',
        '" . $_POST['warranty_first'] . "',
		'" . $_POST['warranty_second'] . "',
		'" . $_POST['warranty_third'] . "',
		'" . $_POST['warranty_fourth'] . "',
		'" . $_POST['warranty_fifth'] . "',
		'" . $_POST['delivery_time'] . "',
		'" . $_POST['delivery_address'] . "',
		'" . $_POST['deadline_date_hottub'] . "',
		'" . $_POST['planned_date_hottub'] . "',
		 " . (!isset($_POST['price_hottub']) || $_POST['price_hottub'] == null ? "NULL" : "'" . $_POST['price_hottub'] . "'") . ",
		 '" . $_POST['chemie_type'] . "',
		 " . $_POST['price_montage'] . ",
		 " . $_POST['price_delivery'] . ",
		 " . (!isset($_POST['price_chemie']) || $_POST['price_chemie'] == null ? "NULL" : "'" . $_POST['price_chemie'] . "'") . ",
		 " . (!isset($_POST['discount']) || $_POST['discount'] == null ? "NULL" : "'" . $_POST['discount'] . "'") . ")") or die($mysqli->error);

        } else {

            $mysqli->query("UPDATE demands_generate_hottub SET
        warranty_first = '" . $_POST['warranty_first'] . "',
		warranty_second = '" . $_POST['warranty_second'] . "',
		warranty_third = '" . $_POST['warranty_third'] . "',
		warranty_fourth = '" . $_POST['warranty_fourth'] . "',
		warranty_fifth = '" . $_POST['warranty_fifth'] . "',
		delivery_time = '" . $_POST['delivery_time'] . "',
		delivery_address = '" . $_POST['delivery_address'] . "',
		deadline_date = '" . $_POST['deadline_date_hottub'] . "',
		planned_date = '" . $_POST['planned_date_hottub'] . "',
		price_hottub = " . (!isset($_POST['price_hottub']) || $_POST['price_hottub'] == null ? "NULL" : "'" . $_POST['price_hottub'] . "'") . ",
		chemie_type = '" . $_POST['chemie_type'] . "',
		price_montage = " . $_POST['price_montage'] . ",
		price_delivery = " . $_POST['price_delivery'] . ",
		price_chemie = " . (!isset($_POST['price_chemie']) || $_POST['price_chemie'] == null ? "NULL" : "'" . $_POST['price_chemie'] . "'") . ",
		discount = " . (!isset($_POST['discount']) || $_POST['discount'] == null ? "NULL" : "'" . $_POST['discount'] . "'") . " WHERE id = '$id'") or die($mysqli->error);

        }

        $address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $getclient['shipping_id'] . '" WHERE b.id = "' . $getclient['billing_id'] . '"') or die($mysqli->error);
        $address = mysqli_fetch_assoc($address_query);

        if ($address['shipping_name'] != '' || $address['shipping_surname'] != '') {

            $user_name = $address['shipping_name'] . ' ' . $address['shipping_surname'];

        } elseif ($address['billing_name'] != '' || $address['billing_surname'] != '') {

            $user_name = $address['billing_name'] . ' ' . $address['billing_surname'];

        } elseif ($address['billing_company'] != '') {

            $user_name = $address['billing_company'];

        } else {

            $user_name = $address['shipping_company'];

        }

        $_POST['confirmed'] = 0;
        $_POST['realizationdate'] = $_POST['planned_date_hottub'];
        $_POST['customer'] = 1;

        $mysqli->query("UPDATE demands SET user_name = '" . $user_name . "',  area = '" . $_POST['area'] . "', realization = '" . $_POST['planned_date_hottub'] . "' WHERE id = '" . $getclient['id'] . "'") or die($mysqli->error);


        $mysqli->query("DELETE FROM mails_recievers WHERE type_id = '" . $getclient['id'] . "' AND type = 'realization_hottub'") or die($mysqli->error);

        if(!empty(($_POST['performer']))){ $performersArray = array_filter($_POST['performer']); }else{ $performersArray[] = ''; }
        if(!empty(($_POST['observer']))){ $observersArray = array_filter($_POST['observer']); }else{ $observersArray[] = ''; }

        if (!empty($performersArray) || !empty($observersArray)) {

            recievers($performersArray, $observersArray, 'realization_hottub', $getclient['id']);

        }

        // todo sklad na kterém je vířivka
        /* old to delete?
            $location = $getclient['showroom'];

            if(empty($getclient['showroom'])){  $location = 1; }
        */

        if(empty($_POST['area'])){ $location = 1; }else{

            $getWarehouesId = $mysqli->query("SELECT id FROM shops_locations WHERE area_name = '".$_POST['area']."'")or die($mysqli->error);
            $warehouseId = mysqli_fetch_assoc($getWarehouesId);

            $location = $warehouseId['id'];

        }

        // products
        if (isset($_POST['product_sku'])) {

            $post_products = $_POST['product_sku'];

        } else {

            $post_products = array();

        }

        $find_simple_product = $mysqli->query("SELECT b.product_id, b.variation_id, b.reserved, p.code  FROM products p, demands_accessories_bridge b WHERE p.id = b.product_id AND b.aggregate_id = '" . $_REQUEST['id'] . "' order by p.id desc") or die($mysqli->error);

        $find_variable_product = $mysqli->query("SELECT b.product_id, b.variation_id, b.reserved, v.sku FROM products_variations v, demands_accessories_bridge b WHERE v.id = b.variation_id AND b.aggregate_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

        $array1 = array();
        while ($row = mysqli_fetch_assoc($find_simple_product)) {
            $array1[] = $row['code'];
        }
        while ($row = mysqli_fetch_assoc($find_variable_product)) {
            $array1[] = $row['sku'];
        }

        $array2 = array_filter($post_products);

        $dups_old = array();
        foreach (array_count_values($array2) as $val => $c) {
            if ($c > 1) {$dups_old[] = $val;}
        }

        $dups_new = array();
        foreach (array_count_values($array1) as $val => $c) {
            if ($c > 1) {$dups_new[] = $val;}
        }

        $check_duplicants = array_diff((array)$dups_new, (array)$dups_old);
        $removed_products = array_diff((array)$array1, (array)$array2); // odebírané produkty
        $removed_products = array_merge((array)$removed_products, (array)$check_duplicants);

        include CONTROLLERS . "/product-stock-controller.php";

        foreach ($removed_products as $removed) {

            $product = array();

            $product_query = $mysqli->query("SELECT b.id, b.product_id, b.variation_id, b.reserved FROM products p, demands_accessories_bridge b WHERE p.code = '$removed' AND b.variation_id = 0 AND p.id = b.product_id AND b.aggregate_id = '" . $id . "' UNION SELECT b.id, b.product_id, b.variation_id, b.reserved FROM products_variations v, demands_accessories_bridge b WHERE v.sku = '$removed' AND v.id = b.variation_id AND b.variation_id != 0 AND b.aggregate_id = '" . $id . "'") or die($mysqli->error);

            if (mysqli_num_rows($product_query) != 0) {

                $product = mysqli_fetch_assoc($product_query);

                $mysqli->query("DELETE FROM demands_accessories_bridge WHERE id = '" . $product['id'] . "'");

                product_update($product['product_id'], $product['variation_id'], $location, $product['reserved'], $client['id'], 'order_change', $id);

            }

        }

        $added_products = array_diff((array)$array2, (array)$array1); // přidávané produkty


        $stable_products = array_intersect((array)$array1, (array)$array2);

        $overall_purchase = 0;
        $overallcena = 0;

        if (isset($post_products)) {

            $post = array_filter($post_products);

            if (!empty($post)) {

                foreach ($post as $post_index => $posterino) {

                    $product = array();

                    $check_data = $mysqli->query("SELECT price_vat FROM demands_generate WHERE id = '$id'");

                    if(mysqli_num_rows($check_data) > 0){
                        $data = mysqli_fetch_array($check_data);
                        $old_vat = $data['price_vat'];
                    }else{
                        $old_vat = $_POST['price_vat'];
                    }


                    $stock_allocation['posterino'] = $posterino;
                    $stock_allocation['id'] = $id;
                    $stock_allocation['bridge'] = 'demands_accessories_bridge';
                    $stock_allocation['id_identify'] = 'aggregate_id';
                    $stock_allocation['quantity'] = $_POST['product_quantity'][$post_index];
                    $stock_allocation['location'] = $location;
                    $stock_allocation['type'] = 'demand';
                    $stock_allocation['quantity'] = $_POST['product_quantity'][$post_index];
                    $stock_allocation['total_quantity'] = $_POST['product_quantity'][$post_index];

                    $stock_allocation['price'] = product_price(
                        $_POST['product_price'][$post_index],
                        $_POST['product_original_price'][$post_index],
                        $_POST['price_vat'],
                        $old_vat,
                        0
                    );

                    $quantity = $_POST['product_quantity'][$post_index];

                    if (in_array($posterino, $added_products)) {

                        $total_quantity = $quantity;

                        // VYSKLADNĚNÍ A PŘIPOJENÍ K TÉTO OBJEDNÁVCE
                        include_once CONTROLLERS . "/product-stock-update.php";
                        $response = stock_allocate($stock_allocation);

                        if ($response['reserve'] < $quantity) {

                            $quantity -= $response['reserve'];

                            include CONTROLLERS . "/product-delivery-update.php";

                        }



                    } elseif (in_array($posterino, $stable_products)) {


                        $product_query = $mysqli->query("
                        
                        SELECT p.price, p.productname, b.product_id, b.variation_id, p.delivery_time, b.reserved, b.quantity, b.delivered, b.id, cat.discount, p.purchase_price, p.ean FROM products p, demands_accessories_bridge b, products_cats cat, products_sites_categories minicat WHERE minicat.category = cat.seoslug AND p.code = '$posterino' AND p.id = b.product_id AND b.aggregate_id = '" . $_REQUEST['id'] . "' GROUP BY p.id 
                        
                        UNION 
                        
                        SELECT 
                        v.price, p.productname, b.product_id, b.variation_id, p.delivery_time, b.reserved, b.quantity, b.delivered, b.id, cat.discount, v.purchase_price, v.ean 
                        FROM products p, demands_accessories_bridge b, products_variations v, products_cats cat, products_sites_categories minicat 
                            WHERE minicat.category = cat.seoslug AND v.product_id = p.id AND v.sku = '$posterino' AND v.id = b.variation_id AND b.aggregate_id = '" . $_REQUEST['id'] . "' GROUP BY v.id
                        
                        ") or die($mysqli->error);

                        if (mysqli_num_rows($product_query) != 0) {

                            $product = mysqli_fetch_assoc($product_query);

                            /* ROVNÁ SE, POUZE ZMĚNA CENY  */

                            if ($quantity == $product['quantity']) {

                                $mysqli->query("UPDATE demands_accessories_bridge 
                                    SET price = '".$stock_allocation['price']['price']."', 
                                        discount = '". $stock_allocation['price']['discount'] ."', 
                                        discount_net = '" . $stock_allocation['price']['discount_net'] . "' 
                                    WHERE id = '" . $product['id'] . "'")or die($mysqli->error);

                                /* NOVÉ MNOŽSTVÍ JE MENŠÍ NEŽ PŮVODNÍ MNOŽSTVÍ +++ REZERVOVANÉ MNOŽSTVÍ JE VĚTŠÍ NEŽ NOVÉ MNOŽSTVÍ  */

                            } elseif ($quantity < $product['quantity'] && ($quantity < ($product['reserved'] + $product['delivered']))) {

                                // množství přidané k jiným objednávkám
                                $reduced_quantity = ($product['reserved'] + $product['delivered']) - $quantity;

                                // monžství delivered
                                if ($reduced_quantity > $product['delivered']) {

                                    $delivered_quantity = $product['delivered'];

                                } else {

                                    $delivered_quantity = $reduced_quantity;

                                }

                                if ($delivered_quantity > 0) {

                                    // DODÁVKY PŘIDÁNÍ K JINÝM OBJEDNÁVKÁM
                                    product_delivered_update($product['product_id'], $product['variation_id'], $delivered_quantity, 'order', $id);

                                }

                                $reserved_quantity = $reduced_quantity - $delivered_quantity;

                                if ($reserved_quantity > 0) {

                                    // NASKLADNĚNÍ A PŘIDÁNÍ K JINÝM OBJEDNÁVKÁM
                                    product_update($product['product_id'], $product['variation_id'], $location, $reserved_quantity, $client['id'], 'order_change', $id);

                                }

                                $final_reserved = $product['reserved'] - $reserved_quantity;

                                $mysqli->query("UPDATE demands_accessories_bridge SET quantity = '$quantity', reserved = '" . $final_reserved . "', delivered = delivered - $delivered_quantity, price = '".$price_product['price']."', discount = '$product_discount', discount_net = '".$price_product['discount_net']."' WHERE id = '" . $product['id'] . "'");

                                /* NOVÉ VĚTŠÍ NEŽ PŮVODNÍ +++ REZERVOVANÉ MENŠÍ NEŽ NOVÉ  */

                            } elseif ($quantity > $product['quantity'] && ($quantity > ($product['reserved'] + $product['delivered']))) {

                                $total_quantity = $quantity;

                                // množství připojené k této objednávce
                                $quantity = $quantity - ($product['reserved'] + $product['delivered']);

                                // VYSKLADNĚNÍ A PŘIPOJENÍ K TÉTO OBJEDNÁVCE
                                include_once CONTROLLERS . "/product-stock-update.php";
                                $response = stock_allocate($stock_allocation);

                                if ($response['reserve'] < $quantity) {

                                    $quantity = $quantity - $response['reserve'];

                                    include CONTROLLERS . "/product-delivery-update.php";

                                }

                                $mysqli->query("UPDATE demands_accessories_bridge SET quantity = '" . $total_quantity . "' WHERE id = '" . $product['id'] . "'");

                            } else {

                                $mysqli->query("UPDATE demands_accessories_bridge SET quantity = '" . $quantity . "' WHERE id = '" . $product['id'] . "'");

                            }
                        }
                    }
                }
            }
        }
    }

    if (isset($getclient['customer']) && $getclient['customer'] == 0 || $getclient['customer'] == 3) {


        $choosed_product = $_POST['saunatype'];
        $choosed_type = $_POST['provedeni_' . $choosed_product];

        $get_ids = $mysqli->query("SELECT w.id as id, w.name as name FROM warehouse_products_types w, warehouse_products p WHERE w.warehouse_product_id = p.id AND p.connect_name = '$choosed_product' AND w.seo_url = '$choosed_type'") or die($mysqli->error);
        $get_id = mysqli_fetch_array($get_ids);

        ///provedení

        $find_query = $mysqli->query("SELECT id FROM demands_specs_bridge WHERE client_id = '" . $_REQUEST['id'] . "' AND specs_id = '5'") or die($mysqli->error);
        if (mysqli_num_rows($find_query) > 0) {

            $find = mysqli_fetch_array($find_query);
            $insert_specs = $mysqli->query("UPDATE demands_specs_bridge SET value = '" . $get_id['name'] . "' WHERE id = '" . $find['id'] . "'") or die($mysqli->error);

        } else {

            $insert_specs = $mysqli->query("INSERT INTO demands_specs_bridge (value, client_id, specs_id) VALUES ('" . $get_id['name'] . "','" . $_REQUEST['id'] . "','5')") or die($mysqli->error);

        }

        ///provedení

        // DEMAND SPECS

        $specs_query = $mysqli->query("SELECT *, s.id as id FROM specs s, warehouse_products_types_specs w WHERE w.spec_id = s.id AND w.type_id = '" . $get_id['id'] . "' AND s.is_demand = 1 GROUP BY s.id") or die($mysqli->error);

        while ($specs = mysqli_fetch_array($specs_query)) {

            $seoslug = $specs['seoslug'];

            $spec_value = $_POST[$choosed_product . '_' . $choosed_type . '_' . $seoslug];

            $spec_price = null;
            if (($spec_value != "Ne" && $spec_value != "") && $specs['generate'] == 1) {

                $spec_price = $_POST['price_' . $seoslug];

                if ($spec_price == "") {$spec_price = null;}

            } else {

                $_POST['price_' . $seoslug] = null;
                $spec_price = null;

            }

            if(!empty($_POST['is_generated_' . $seoslug])){

                $is_generated = 1;

            }else{

                $is_generated = 0;

            }

            $find_query = $mysqli->query("SELECT id FROM demands_specs_bridge WHERE client_id = '" . $_REQUEST['id'] . "' AND specs_id = '" . $specs['id'] . "'") or die($mysqli->error);
            if (mysqli_num_rows($find_query) > 0) {

                $find = mysqli_fetch_array($find_query);
                $mysqli->query("UPDATE demands_specs_bridge SET value = '$spec_value', is_generated = '$is_generated', price = " . ($spec_price == null ? "NULL" : "'$spec_price'") . " WHERE id = '" . $find['id'] . "'") or die($mysqli->error);

            } else {

                $mysqli->query("INSERT INTO demands_specs_bridge (value, client_id, specs_id, price, is_generated) VALUES ('$spec_value','" . $_REQUEST['id'] . "','" . $specs['id'] . "', " . ($spec_price == null ? "NULL" : "'$spec_price'") . ", '$is_generated')") or die($mysqli->error);

            }

        }

        // END DEMAND SPECS

        // NOT DEMAND SPECS

        $specs_query = $mysqli->query("SELECT *, s.id as id FROM specs s, warehouse_products_types_specs w WHERE w.spec_id = s.id AND w.type_id = '" . $get_id['id'] . "' AND s.is_demand = 0 GROUP BY s.id") or die($mysqli->error);

        while ($specs = mysqli_fetch_array($specs_query)) {

            if (isset($specs['type']) && $specs['type'] == 1) {

                $paramsquery = $mysqli->query("SELECT *, p.id as id FROM specs_params p, warehouse_products_types_specs w  WHERE p.spec_id = '" . $specs['id'] . "' AND w.spec_param_id = p.id AND w.type_id = '" . $get_id['id'] . "' AND w.choosed = 1 GROUP by p.id") or die($mysqli->error);

                $param = mysqli_fetch_array($paramsquery);

                $value = $param['option'];

            } else {

                $paramsquery = $mysqli->query("SELECT * FROM warehouse_products_types_specs WHERE spec_id = '" . $specs['id'] . "' AND type_id = '" . $get_id['id'] . "' AND choosed = 1 order by spec_param_id desc") or die($mysqli->error);

                $param = mysqli_fetch_array($paramsquery);

                if (isset($param['spec_param_id']) && $param['spec_param_id'] == 1) {$value = 'Ano';} else { $value = 'Ne';}

            }

            $find_query = $mysqli->query("SELECT id FROM demands_specs_bridge WHERE client_id = '" . $_REQUEST['id'] . "' AND specs_id = '" . $specs['id'] . "'") or die($mysqli->error);
            if (mysqli_num_rows($find_query) > 0) {

                $find = mysqli_fetch_array($find_query);
                $insert_specs = $mysqli->query("UPDATE demands_specs_bridge SET value = '$value' WHERE id = '" . $find['id'] . "'") or die($mysqli->error);

            } else {

                $insert_specs = $mysqli->query("INSERT INTO demands_specs_bridge (value, client_id, specs_id) VALUES ('$value','" . $_REQUEST['id'] . "','" . $specs['id'] . "')") or die($mysqli->error);

            }

        }

        // END NOT DEMAND SPECS

        // SPECS THAT SHOULD NOT BE IN DEMAND

        $specs_query = $mysqli->query("SELECT *, b.id as demandSpecId FROM specs s, demands_specs_bridge b WHERE 
                    b.specs_id = s.id AND b.client_id = '".$_REQUEST['id']."' AND s.id != 5 AND                                
                    s.id NOT IN (SELECT s.id as id FROM specs s, warehouse_products_types_specs w WHERE w.spec_id = s.id AND w.type_id = '" . $get_id['id'] . "' GROUP BY s.id) GROUP BY s.id") or die($mysqli->error);

        while ($specs = mysqli_fetch_assoc($specs_query)) {

            $mysqli->query("DELETE FROM demands_specs_bridge WHERE id = '".$specs['demandSpecId']."'")or die($mysqli->error);

        }

        // END SPECS THAT SHOULD NOT BE IN DEMAND


		if(empty($_POST['bottom_bench'])){ $_POST['bottom_bench'] = null; }
		if(empty($_POST['top_bench'])){ $_POST['top_bench'] = null; }
		if(empty($_POST['material'])){ $_POST['material'] = null; }
		if(empty($_POST['type'])){ $_POST['type'] = null; }
		if(empty($_POST['stone_wall'])){ $_POST['stone_wall'] = null; }
		if(empty($_POST['handrail'])){ $_POST['handrail'] = null; }
		if(empty($_POST['stove'])){ $_POST['stove'] = null; }
		if(empty($_POST['rgb_sky'])){ $_POST['rgb_sky'] = null; }
		if(empty($_POST['rgb_backrest'])){ $_POST['rgb_backrest'] = null; }
		if(empty($_POST['light'])){ $_POST['light'] = null; }
		if(empty($_POST['controlpanel'])){ $_POST['controlpanel'] = null; }
		if(empty($_POST['remote'])){ $_POST['remote'] = null; }
		if(empty($_POST['audio'])){ $_POST['audio'] = null; }
		if(empty($_POST['loudspeaker'])){ $_POST['loudspeaker'] = null; }
		if(empty($_POST['glass_wall'])){ $_POST['glass_wall'] = null; }
		if(empty($_POST['glass_doors'])){ $_POST['glass_doors'] = null; }
		if(empty($_POST['accessories'])){ $_POST['accessories'] = null; }

        $check_data_sauna = $mysqli->query("SELECT * FROM demands_generate_sauna WHERE id = '$id'");
        if (mysqli_num_rows($check_data_sauna) == 0) {

            if (isset($_POST['price_sauna']) && $_POST['price_sauna'] == "") {$_POST['price_sauna'] = 0;}
            if (isset($_POST['price_montage_sauna']) && $_POST['price_montage_sauna'] == "") {$_POST['price_montage_sauna'] = 0;}
            if (isset($_POST['price_delivery_sauna']) && $_POST['price_delivery_sauna'] == "") {$_POST['price_delivery_sauna'] = 0;}
            if (isset($_POST['discount_sauna']) && $_POST['discount_sauna'] == "") {$_POST['discount_sauna'] = 0;}

            $mysqli->query("INSERT INTO demands_generate_sauna (id,
		delivery_time,
		delivery_address,
		deadline_date,
		planned_date,
		price_sauna,
		price_montage,
		price_delivery,
		discount, dimension,
		top_bench,
		bottom_bench,
		material,
		type,
		stone_wall,
		handrail,
		stove,
		rgb_sky,
		rgb_backrest,
		light,
		controlpanel,
		remote,
		audio,
		loudspeaker,
		glass_wall,
		glass_doors,
		accessories) VALUES ('$id',
		'" . $_POST['delivery_time_sauna'] . "',
		'" . $_POST['delivery_address_sauna'] . "',
		'" . $_POST['deadline_date_sauna'] . "',
		'" . $_POST['planned_date_sauna'] . "',
		 " . (!isset($_POST['price_sauna']) || $_POST['price_sauna'] == null ? "NULL" : "'" . $_POST['price_sauna'] . "'") . ",
		 " . (!isset($_POST['price_montage_sauna']) || $_POST['price_montage_sauna'] == null ? "NULL" : "'" . $_POST['price_montage_sauna'] . "'") . ",
		 " . (!isset($_POST['price_delivery_sauna']) || $_POST['price_delivery_sauna'] == null ? "NULL" : "'" . $_POST['price_delivery_sauna'] . "'") . ",
		 " . (!isset($_POST['discount_sauna']) || $_POST['discount_sauna'] == null ? "NULL" : "'" . $_POST['discount_sauna'] . "'") . ",
		'" . $_POST['dimension'] . "',
		'" . $_POST['bottom_bench'] . "',
		'" . $_POST['top_bench'] . "',
		'" . $_POST['material'] . "',
		'" . $_POST['type'] . "',
		'" . $_POST['stone_wall'] . "',
		'" . $_POST['handrail'] . "',
		'" . $_POST['stove'] . "',
		'" . $_POST['rgb_sky'] . "',
		'" . $_POST['rgb_backrest'] . "',
		'" . $_POST['light'] . "',
		'" . $_POST['controlpanel'] . "',
		'" . $_POST['remote'] . "',
		'" . $_POST['audio'] . "',
		'" . $_POST['loudspeaker'] . "',
		'" . $_POST['glass_wall'] . "',
		'" . $_POST['glass_doors'] . "',
		'" . $_POST['accessories'] . "')") or die($mysqli->error);

        } else {

            $mysqli->query("UPDATE demands_generate_sauna SET
		delivery_time = '" . $_POST['delivery_time_sauna'] . "',
		delivery_address = '" . $_POST['delivery_address_sauna'] . "',
		deadline_date = '" . $_POST['deadline_date_sauna'] . "',
		planned_date = '" . $_POST['planned_date_sauna'] . "',
		price_sauna = " . (!isset($_POST['price_sauna']) || $_POST['price_sauna'] == null ? "NULL" : "'" . $_POST['price_sauna'] . "'") . ",
		price_montage = " . (!isset($_POST['price_montage_sauna']) || $_POST['price_montage_sauna'] == null ? "NULL" : "'" . $_POST['price_montage_sauna'] . "'") . ",
		price_delivery = " . (!isset($_POST['price_delivery_sauna']) || $_POST['price_delivery_sauna'] == null ? "NULL" : "'" . $_POST['price_delivery_sauna'] . "'") . ",
		discount = " . (!isset($_POST['discount_sauna']) || $_POST['discount_sauna'] == null ? "NULL" : "'" . $_POST['discount_sauna'] . "'") . ",
		dimension = '" . $_POST['dimension'] . "',
		top_bench = '" . $_POST['top_bench'] . "',
		bottom_bench = '" . $_POST['bottom_bench'] . "',
		material = '" . $_POST['material'] . "',
		stone_wall = '" . $_POST['stone_wall'] . "',
		handrail = '" . $_POST['handrail'] . "',
		type = '" . $_POST['type'] . "',
		stove = '" . $_POST['stove'] . "',
		rgb_sky = '" . $_POST['rgb_sky'] . "',
		rgb_backrest = '" . $_POST['rgb_backrest'] . "',
		light = '" . $_POST['light'] . "',
		controlpanel = '" . $_POST['controlpanel'] . "',
		remote = '" . $_POST['remote'] . "',
		audio = '" . $_POST['audio'] . "',
		loudspeaker = '" . $_POST['loudspeaker'] . "',
		glass_wall = '" . $_POST['glass_wall'] . "',
		glass_doors = '" . $_POST['glass_doors'] . "',
		accessories = '" . $_POST['accessories'] . "' WHERE id = '$id'") or die($mysqli->error);

        }

        $_POST['confirmed'] = 0;
        $_POST['realizationdate'] = $_POST['planned_date_sauna'];
        $_POST['customer'] = 0;

        $address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $getclient['shipping_id'] . '" WHERE b.id = "' . $getclient['billing_id'] . '"') or die($mysqli->error);
        $address = mysqli_fetch_assoc($address_query);

        if ($address['shipping_name'] != '' || $address['shipping_surname'] != '') {

            $user_name = $address['shipping_name'] . ' ' . $address['shipping_surname'];

        } elseif ($address['billing_name'] != '' || $address['billing_surname'] != '') {

            $user_name = $address['billing_name'] . ' ' . $address['billing_surname'];

        } elseif ($address['billing_company'] != '') {

            $user_name = $address['billing_company'];

        } else {

            $user_name = $address['shipping_company'];

        }

        if (isset($getclient['customer']) && $getclient['customer'] == 0) {

            $update = $mysqli->query("UPDATE demands SET user_name = '" . $user_name . "', realization = '" . $_POST['planned_date_sauna'] . "' WHERE id = '" . $getclient['id'] . "'") or die($mysqli->error);

        } elseif (isset($getclient['customer']) && $getclient['customer'] == 3) {

            $gatedate = $mysqli->query("SELECT id, gcalendar FROM demands_double_realization WHERE demand_id = '" . $getclient['id'] . "'");
            $saunadate = mysqli_fetch_array($gatedate);
            $gcalendar = $saunadate['gcalendar'];

            if (mysqli_num_rows($gatedate) == 1) {

                $update = $mysqli->query("UPDATE demands_double_realization SET startdate = '" . $_POST['planned_date_sauna'] . "' WHERE demand_id = '" . $getclient['id'] . "'");

            } else {

                $insert = $mysqli->query("INSERT INTO demands_double_realization (startdate, demand_id) VALUES ('" . $_POST['planned_date_sauna'] . "', '" . $getclient['id'] . "')");

            }

        }



        $mysqli->query("DELETE FROM mails_recievers WHERE type_id = '" . $getclient['id'] . "' AND type = 'realization_sauna'") or die($mysqli->error);

        if(!empty(($_POST['performer']))){ $performersArray = array_filter($_POST['performer']); }else{ $performersArray[] = ''; }
        if(!empty(($_POST['observer']))){ $observersArray = array_filter($_POST['observer']); }else{ $observersArray[] = ''; }

        if (!empty($performersArray) || !empty($observersArray)) {

            recievers($performersArray, $observersArray, 'realization_sauna', $getclient['id']);

        }


    }




    if (isset($getclient['customer']) && $getclient['customer'] == 4) {

        $choosed_product = $_POST['pergolatype'];
        $choosed_type = $_POST['provedeni_' . $choosed_product];

        $get_ids = $mysqli->query("SELECT w.id as id, w.name as name FROM warehouse_products_types w, warehouse_products p WHERE w.warehouse_product_id = p.id AND p.connect_name = '$choosed_product' AND w.seo_url = '$choosed_type'") or die($mysqli->error);
        $get_id = mysqli_fetch_array($get_ids);

        ///provedení

        $find_query = $mysqli->query("SELECT id FROM demands_specs_bridge WHERE client_id = '" . $_REQUEST['id'] . "' AND specs_id = '5'") or die($mysqli->error);
        if (mysqli_num_rows($find_query) > 0) {

            $find = mysqli_fetch_array($find_query);
            $insert_specs = $mysqli->query("UPDATE demands_specs_bridge SET value = '" . $get_id['name'] . "' WHERE id = '" . $find['id'] . "'") or die($mysqli->error);

        } else {

            $insert_specs = $mysqli->query("INSERT INTO demands_specs_bridge (value, client_id, specs_id) VALUES ('" . $get_id['name'] . "','" . $_REQUEST['id'] . "','5')") or die($mysqli->error);

        }

        ///provedení

        // DEMAND SPECS

        $specs_query = $mysqli->query("SELECT *, s.id as id FROM specs s, warehouse_products_types_specs w WHERE w.spec_id = s.id AND w.type_id = '" . $get_id['id'] . "' AND s.is_demand = 1 GROUP BY s.id") or die($mysqli->error);

        while ($specs = mysqli_fetch_array($specs_query)) {

            $seoslug = $specs['seoslug'];

            $spec_value = $_POST[$choosed_product . '_' . $choosed_type . '_' . $seoslug];

            $spec_price = null;
            if (($spec_value != "Ne" && $spec_value != "") && $specs['generate'] == 1) {

                $spec_price = $_POST['price_' . $seoslug];

                if ($spec_price == "") {$spec_price = null;}

            } else {

                $_POST['price_' . $seoslug] = null;
                $spec_price = null;

            }

            if(!empty($_POST['is_generated_' . $seoslug])){

                $is_generated = 1;

            }else{

                $is_generated = 0;

            }

            $find_query = $mysqli->query("SELECT id FROM demands_specs_bridge WHERE client_id = '" . $_REQUEST['id'] . "' AND specs_id = '" . $specs['id'] . "'") or die($mysqli->error);
            if (mysqli_num_rows($find_query) > 0) {

                $find = mysqli_fetch_array($find_query);
                $mysqli->query("UPDATE demands_specs_bridge SET value = '$spec_value', is_generated = '$is_generated', price = " . ($spec_price == null ? "NULL" : "'$spec_price'") . " WHERE id = '" . $find['id'] . "'") or die($mysqli->error);

            } else {

                $mysqli->query("INSERT INTO demands_specs_bridge (value, client_id, specs_id, price, is_generated) VALUES ('$spec_value','" . $_REQUEST['id'] . "','" . $specs['id'] . "', " . ($spec_price == null ? "NULL" : "'$spec_price'") . ", '$is_generated')") or die($mysqli->error);

            }

        }

        // END DEMAND SPECS

        // NOT DEMAND SPECS

        $specs_query = $mysqli->query("SELECT *, s.id as id FROM specs s, warehouse_products_types_specs w WHERE w.spec_id = s.id AND w.type_id = '" . $get_id['id'] . "' AND s.is_demand = 0 GROUP BY s.id") or die($mysqli->error);

        while ($specs = mysqli_fetch_array($specs_query)) {

            if (isset($specs['type']) && $specs['type'] == 1) {

                $paramsquery = $mysqli->query("SELECT *, p.id as id FROM specs_params p, warehouse_products_types_specs w  WHERE p.spec_id = '" . $specs['id'] . "' AND w.spec_param_id = p.id AND w.type_id = '" . $get_id['id'] . "' AND w.choosed = 1 GROUP by p.id") or die($mysqli->error);

                $param = mysqli_fetch_array($paramsquery);

                $value = $param['option'];

            } else {

                $paramsquery = $mysqli->query("SELECT * FROM warehouse_products_types_specs WHERE spec_id = '" . $specs['id'] . "' AND type_id = '" . $get_id['id'] . "' AND choosed = 1 order by spec_param_id desc") or die($mysqli->error);

                $param = mysqli_fetch_array($paramsquery);

                if (isset($param['spec_param_id']) && $param['spec_param_id'] == 1) {$value = 'Ano';} else { $value = 'Ne';}

            }

            $find_query = $mysqli->query("SELECT id FROM demands_specs_bridge WHERE client_id = '" . $_REQUEST['id'] . "' AND specs_id = '" . $specs['id'] . "'") or die($mysqli->error);
            if (mysqli_num_rows($find_query) > 0) {

                $find = mysqli_fetch_array($find_query);
                $insert_specs = $mysqli->query("UPDATE demands_specs_bridge SET value = '$value' WHERE id = '" . $find['id'] . "'") or die($mysqli->error);

            } else {

                $insert_specs = $mysqli->query("INSERT INTO demands_specs_bridge (value, client_id, specs_id) VALUES ('$value','" . $_REQUEST['id'] . "','" . $specs['id'] . "')") or die($mysqli->error);

            }

        }

        // END NOT DEMAND SPECS

        // SPECS THAT SHOULD NOT BE IN DEMAND

        $specs_query = $mysqli->query("SELECT *, b.id as demandSpecId FROM specs s, demands_specs_bridge b WHERE 
                    b.specs_id = s.id AND b.client_id = '".$_REQUEST['id']."' AND s.id != 5 AND                                
                    s.id NOT IN (SELECT s.id as id FROM specs s, warehouse_products_types_specs w WHERE w.spec_id = s.id AND w.type_id = '" . $get_id['id'] . "' GROUP BY s.id) GROUP BY s.id") or die($mysqli->error);

        while ($specs = mysqli_fetch_assoc($specs_query)) {

            $mysqli->query("DELETE FROM demands_specs_bridge WHERE id = '".$specs['demandSpecId']."'")or die($mysqli->error);

        }

        // END SPECS THAT SHOULD NOT BE IN DEMAND


        // todo all above special classes/functions

        $check_data_pergola = $mysqli->query("SELECT * FROM demands_generate_pergola WHERE id = '$id'");

        if (isset($_POST['price_pergola']) && $_POST['price_pergola'] == "") {$_POST['price_pergola'] = 0;}
        if (isset($_POST['price_montage']) && $_POST['price_montage'] == "") {$_POST['price_montage'] = 0;}
        if (isset($_POST['price_delivery']) && $_POST['price_delivery'] == "") {$_POST['price_delivery'] = 0;}
        if (isset($_POST['discount']) && $_POST['discount'] == "") {$_POST['discount'] = 0;}

        if (mysqli_num_rows($check_data_pergola) == 0) {

            $mysqli->query("INSERT INTO demands_generate_pergola (id,
        warranty_first,
		warranty_second,
		warranty_third,
		warranty_fourth,
		warranty_fifth,
		delivery_time,
		delivery_address,
		deadline_date,
		planned_date,
		price_pergola,
		price_montage,
		price_delivery,
		discount) VALUES ('$id',
        '" . $_POST['warranty_first'] . "',
		'" . $_POST['warranty_second'] . "',
		'" . $_POST['warranty_third'] . "',
		'" . $_POST['warranty_fourth'] . "',
		'" . $_POST['warranty_fifth'] . "',
		'" . $_POST['delivery_time'] . "',
		'" . $_POST['delivery_address'] . "',
		'" . $_POST['deadline_date_pergola'] . "',
		'" . $_POST['planned_date_pergola'] . "',
		 " . (!isset($_POST['price_pergola']) || $_POST['price_pergola'] == null ? "NULL" : "'" . $_POST['price_pergola'] . "'") . ",
		 " . $_POST['price_montage'] . ",
		 " . (!isset($_POST['price_delivery_pergola']) || $_POST['price_delivery_pergola'] == null ? "NULL" : "'" . $_POST['price_delivery_pergola'] . "'") . ",
		 " . (!isset($_POST['discount']) || $_POST['discount'] == null ? "NULL" : "'" . $_POST['discount'] . "'") . ")") or die($mysqli->error);

        } else {

            $mysqli->query("UPDATE demands_generate_pergola SET
        warranty_first = '" . $_POST['warranty_first'] . "',
		warranty_second = '" . $_POST['warranty_second'] . "',
		warranty_third = '" . $_POST['warranty_third'] . "',
		warranty_fourth = '" . $_POST['warranty_fourth'] . "',
		warranty_fifth = '" . $_POST['warranty_fifth'] . "',
		delivery_time = '" . $_POST['delivery_time'] . "',
		delivery_address = '" . $_POST['delivery_address'] . "',
		deadline_date = '" . $_POST['deadline_date_pergola'] . "',
		planned_date = '" . $_POST['planned_date_pergola'] . "',
		price_pergola = " . (!isset($_POST['price_pergola']) || $_POST['price_pergola'] == null ? "NULL" : "'" . $_POST['price_pergola'] . "'") . ",
		price_montage = " . $_POST['price_montage'] . ",
		price_delivery = " . (!isset($_POST['price_delivery_pergola']) || $_POST['price_delivery_pergola'] == null ? "NULL" : "'" . $_POST['price_delivery_pergola'] . "'") . ",
		discount = " . (!isset($_POST['discount']) || $_POST['discount'] == null ? "NULL" : "'" . $_POST['discount'] . "'") . " WHERE id = '$id'") or die($mysqli->error);


        }

        $address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $getclient['shipping_id'] . '" WHERE b.id = "' . $getclient['billing_id'] . '"') or die($mysqli->error);
        $address = mysqli_fetch_assoc($address_query);

        if ($address['shipping_name'] != '' || $address['shipping_surname'] != '') {

            $user_name = $address['shipping_name'] . ' ' . $address['shipping_surname'];

        } elseif ($address['billing_name'] != '' || $address['billing_surname'] != '') {

            $user_name = $address['billing_name'] . ' ' . $address['billing_surname'];

        } elseif ($address['billing_company'] != '') {

            $user_name = $address['billing_company'];

        } else {

            $user_name = $address['shipping_company'];

        }

        $_POST['confirmed'] = 0;
        $_POST['realizationdate'] = $_POST['planned_date_pergola'];
        $_POST['customer'] = 4;

        $mysqli->query("UPDATE demands SET user_name = '" . $user_name . "',  area = '" . $_POST['area'] . "', realization = '" . $_POST['planned_date_pergola'] . "' WHERE id = '" . $getclient['id'] . "'") or die($mysqli->error);


        $mysqli->query("DELETE FROM mails_recievers WHERE type_id = '" . $getclient['id'] . "' AND type = 'realization_pergola'") or die($mysqli->error);

        if(!empty(($_POST['performer']))){ $performersArray = array_filter($_POST['performer']); }else{ $performersArray[] = ''; }
        if(!empty(($_POST['observer']))){ $observersArray = array_filter($_POST['observer']); }else{ $observersArray[] = ''; }

        if (!empty($performersArray) || !empty($observersArray)) {

            recievers($performersArray, $observersArray, 'realization_pergola', $getclient['id']);

        }

        // todo sklad na kterém je vířivka
        /* old to delete?
            $location = $getclient['showroom'];

            if(empty($getclient['showroom'])){  $location = 1; }
        */

        if(empty($_POST['area'])){ $location = 1; }else{

            $getWarehouesId = $mysqli->query("SELECT id FROM shops_locations WHERE area_name = '".$_POST['area']."'")or die($mysqli->error);
            $warehouseId = mysqli_fetch_assoc($getWarehouesId);

            $location = $warehouseId['id'];

        }

    }

    saveCalendarEvent($getclient['id'], 'realization');

//    demand

    Header("Location:https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=" . $id . "&success=update_data");
    exit;

}



$kurz_url = "http://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_devizoveho_trhu/denni_kurz.txt";
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


include VIEW . '/default/header.php';

?>

<style>
.form-control[disabled] {
    background-color: #eeeeee !important;
}

/*.select2 { visibility: visible !important; }*/
</style>

<div class="row" style="    margin-bottom: 20px;">
	<div class="col-md-8 col-sm-7">
		<h2><?= $address['billing_name'] . " " . $address['billing_surname'] ?> - <?= $pagetitle ?></h2>
	</div>
</div>


<?php

/* ------------- ULOŽENÍ PARAMETRŮ ------------- */

$check_data = $mysqli->query("SELECT * FROM demands_generate WHERE id = '$id'");
$data = mysqli_fetch_array($check_data);

// Načteme storno stav jednotlivých faktur pro tuto poptávku
// klíč = pořadí faktury (status), hodnota = pole s id a storno
$invoices_storno = [];
$inv_storno_query = $mysqli->query("SELECT id, status, storno FROM demands_advance_invoices WHERE demand_id = '$id' ORDER BY status ASC") or die($mysqli->error);
while ($inv_row = mysqli_fetch_assoc($inv_storno_query)) {
    $invoices_storno[$inv_row['status']] = $inv_row;
}

?>

<form role="form" method="post" id="demand_form" name="myform" class="form-horizontal form-groups-bordered validate" enctype="multipart/form-data" action="https://www.wellnesstrade.cz/admin/pages/demands/udaje-pro-generovani?id=<?= $id ?>&action=save_data">
<input type="hidden" name="length" value="14">

    <div class="row">

	<div class="col-md-12" style="padding: 0">

		<div class="col-md-6">

							<div class="panel panel-primary" data-collapsed="0">

								<div class="panel-heading">
									<div class="panel-title">
										Fakturační údaje
									</div>

								</div>

										<div class="panel-body">

                    <div class="form-group">
                        <label for="billing_ico" class="col-sm-2 control-label">IČO</label>

                        <div class="col-sm-4">
                            <input type="text" id="billing_ico" name="billing_ico" class="form-control" value="<?= $address['billing_ico'] ?>" style="float: left; width: 75%;">
                            <a class="ares-load btn-md btn btn-primary" style="float: right; width: 20%; padding: 6px;"><i class="entypo-download"></i></a>
                        </div>

                        <label for="billing_dic" class="col-sm-1 control-label">DIČ</label>
                        <div class="col-sm-3">
                            <input type="text" id="billing_dic" name="billing_dic" class="form-control" value="<?= $address['billing_dic'] ?>">
                        </div>
                    </div>

					<div class="form-group">
						<label for="billing_company" class="col-sm-3 control-label">Firma</label>

						<div class="col-sm-8">
							<input type="text" id="billing_company" name="billing_company" class="form-control" value="<?= $address['billing_company'] ?>">
						</div>
					</div>

					<div class="form-group">
						<label for="billing_degree" class="col-sm-3 control-label">Titul</label>

						<div class="col-sm-8">
							<input type="text" id="billing_degree" name="billing_degree" class="form-control" value="<?= $address['billing_degree'] ?>">
						</div>
					</div>

					<div class="form-group">
						<label for="billing_name" class="col-sm-3 control-label">Jméno</label>

						<div class="col-sm-8">
							<input type="text" id="billing_name" name="billing_name" class="form-control" value="<?= $address['billing_name'] ?>">
						</div>
					</div>
					<div class="form-group">
						<label for="billing_surname" class="col-sm-3 control-label">Příjmení</label>

						<div class="col-sm-8">
							<input type="text" id="billing_surname" name="billing_surname" class="form-control" value="<?= $address['billing_surname'] ?>">
						</div>
					</div>


					<div class="form-group">
						<label for="billing_street" class="col-sm-3 control-label">Ulice</label>

						<div class="col-sm-8">
							<input  type="text" id="billing_street" name="billing_street" class="form-control" value="<?= $address['billing_street'] ?>">
						</div>
					</div>
					<div class="form-group">
						<label for="billing_city" class="col-sm-3 control-label">Město</label>

						<div class="col-sm-8">
							<input  type="text" id="billing_city" name="billing_city" class="form-control" value="<?= $address['billing_city'] ?>">
						</div>
					</div>
					<div class="form-group">
						<label for="billing_zipcode" class="col-sm-3 control-label">PSČ</label>

						<div class="col-sm-3">
							<input type="text" id="billing_zipcode" name="billing_zipcode" class="form-control" value="<?= $address['billing_zipcode'] ?>">
						</div>
						 <label for="billing_country" class="col-sm-1 control-label">Země</label>
						  <div class="col-sm-4"> <select id="billing_country" name="billing_country" class="form-control">
						  	<option value="czech" <?php if (isset($address['billing_country']) && $address['billing_country'] == 'czech') {echo 'selected';}?>>Česká republika</option>
						  	<option value="slovakia" <?php if (isset($address['billing_country']) && $address['billing_country'] == 'slovakia') {echo 'selected';}?>>Slovensko</option>
						  	<option value="austria" <?php if (isset($address['billing_country']) && $address['billing_country'] == 'slovakia') {echo 'selected';}?>>Rakousko</option>
						    <option value="germany" <?php if (isset($address['billing_country']) && $address['billing_country'] == 'germany') {echo 'selected';}?>>Německo</option></select>  </div>
					</div>

					<hr>

					<div class="form-group">
						<label for="phone" class="col-sm-3 control-label">Telefon</label>

						<div class="col-sm-8">
							<input  type="text" id="phone" name="billing_phone" class="form-control" value="<?php if (isset($address['billing_phone']) && $address['billing_phone'] != "") {echo $address['billing_phone'];} else {echo $address['billing_phone'];}?>">
						</div>
					</div>
					<div class="form-group">
						<label for="email" class="col-sm-3 control-label">Email</label>

						<div class="col-sm-8">
							<input  type="text" id="email" name="billing_email" class="form-control" value="<?php if (isset($address['billing_email']) && $address['billing_email'] != "") {echo $address['billing_email'];} else {echo $address['billing_email'];}?>">
						</div>
					</div>



							</div>
						</div>

					</div>
					<div class="col-md-6">


						<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Finanční údaje
					</div>

				</div>

                <div class="panel-body">

                <script type="text/javascript">

					jQuery(document).ready(function($) {


                        $('#deposit_type_percentage').click(function () {


                            $('#money_values').hide("slow");
                            $('#percentage_values').show("slow");

                        });


                        $('#deposit_type_money').click(function () {


                            $('#percentage_values').hide("slow");
                            $('#money_values').show("slow");

                        });


                        $('#one_invoice').click(function () {


                            $('#percentage_deposit_second').hide("slow");
                            $('#percentage_deposit_third').hide("slow");
                            $('#percentage_deposit_fourth').hide("slow");


                            $('#money_deposit_second').hide("slow");
                            $('#money_deposit_third').hide("slow");
                            $('#money_deposit_fourth').hide("slow");

                        });

                        $('#two_invoices').click(function () {

                            $('#percentage_deposit_second').show("slow");

                            $('#percentage_deposit_third').hide("slow");
                            $('#percentage_deposit_fourth').hide("slow");


                            $('#money_deposit_second').show("slow");

                            $('#money_deposit_third').hide("slow");
                            $('#money_deposit_fourth').hide("slow");

                        });

                        $('#three_invoices').click(function () {

                            $('#percentage_deposit_second').show("slow");
                            $('#percentage_deposit_third').show("slow");

                            $('#percentage_deposit_fourth').hide("slow");


                            $('#money_deposit_second').show("slow");
                            $('#money_deposit_third').show("slow");

                            $('#money_deposit_fourth').hide("slow");

                        });

                        $('#four_invoices').click(function () {

                            $('#percentage_deposit_second').show("slow");
                            $('#percentage_deposit_third').show("slow");
                            $('#percentage_deposit_fourth').show("slow");

                            $('#money_deposit_second').show("slow");
                            $('#money_deposit_third').show("slow");
                            $('#money_deposit_fourth').show("slow");

                        });


                        function calc_price() {

                            var sum = 0; // Recompute total sum per change.

                            $('.price-control:visible').each(function () {
                                var x = $(this).val(); // Get the number and make sure it exists.
                                sum += parseFloat(x || 0);
                            });

                            $('.price-control-accessory:visible').each(function () {
                                var quantity = $(this).parent().parent().find('.quantity-control').val();
                                var x = $(this).val() * quantity;
                                sum += parseFloat(x || 0);
                            });

                            sum -= $('.discount').val();
                            console.log(sum);

                            $('#finalprice').html(sum.toLocaleString());

                        }

                        calc_price();


                        $('.currency').change(function () {

                            // console.log($('.currency:checked').val());

                            var rate = $(this).data("value");
                            var sum = 0;

                            $('.price-control:visible').each(function () {

                                var value = $(this).data("default");
                                var exchange = (value / rate).toFixed(0);
                                // console.log(exchange);

                                $(this).val(exchange);
                                sum += parseFloat(exchange);


                            });

                            var value = $('.discount').data("default");
                            var exchange = (value / rate).toFixed(0);

                            $('.discount').val(exchange);
                            sum -= $('.discount').val();

                            $('#finalprice').html(sum.toLocaleString());
                            $('.currency_ext').html($(this).data("ext"));

                        });

                        $('.discount, .price-control, .price-control-accessory, .quantity-control').change(function () {
                            calc_price();
                        });

                        $('.generate_radio').on('click', function () {

                                var selected = this.value;
                                var id = this.id;

                                if (selected == 'Ano') {

                                    var rate = $('.currency:checked').data("value");
                                    var value = $("." + id).find('.price-control').data("default");
                                    var exchange = (value / rate).toFixed(0);
                                    $("." + id).find('.price-control').val(exchange)

                                    $("." + id).show("slow", function () {


                                        calc_price();
                                    });

                                    $("." + id).find("input").prop('disabled', false);

                                } else {

                                    $("." + id).hide("slow", function () {
                                        calc_price();
                                    });
                                    $("." + id).find("input").prop('disabled', true);

                                }

                            }
                        );

                        $('.generate_select').on('change', function () {

                            var selected = this.value;
                            var id = this.id;

                            if (selected != '') {

                                var rate = $('.currency:checked').data("value");
                                var value = $("." + id).find('.price-control').data("default");
                                var exchange = (value / rate).toFixed(0);


                                $("." + id).find('.price-control').val(exchange)

                                $("." + id).show("slow", function () {
                                    calc_price();
                                });
                                $("." + id).find("input").prop('disabled', false);

                            } else {

                                $("." + id).hide("slow", function () {
                                    calc_price();
                                });
                                $("." + id).find("input").prop('disabled', true);

                            }


                        });
                        
                        $('.generate_text').on('input', function () {

                            var selected = this.value;
                            var id = this.id;

                            if (selected != '') {

                                var rate = $('.currency:checked').data("value");
                                var value = $("." + id).find('.price-control').data("default");
                                var exchange = (value / rate).toFixed(0);


                                $("." + id).find('.price-control').val(exchange)

                                $("." + id).show("slow", function () {
                                    calc_price();
                                });
                                $("." + id).find("input").prop('disabled', false);

                            } else {

                                $("." + id).hide("slow", function () {
                                    calc_price();
                                });
                                $("." + id).find("input").prop('disabled', true);

                            }


                        });


                        $('#selectbox-o').select2({
                            minimumInputLength: 2,
                            ajax: {
                                url: "/admin/data/autosuggest-products",
                                dataType: 'json',
                                data: function (term, page) {
                                    return {
                                        q: term
                                    };
                                },
                                results: function (data, page) {
                                    return { results: data };
                                }
                            },

                            formatResult: format,
                            formatSelection: format,
                            escapeMarkup: function(m) { return m; }


                        });


                        function format(data) {
                            if (!data.id) return data.text; // optgroup

                            return "<img src='https://www.wellnesstrade.cz/data/stores/images/mini/" + data.seourl + ".jpg' height='20'/>" + data.text;

                        }




                        $('.quick-search').on('change', function () {

                            var selected = this.value;

                            // console.log(selected);
                            var $select = $('#selectbox-o');
                            // console.log($select);

                            var productType = $(".quick-search option:selected").data("type");

                            $select.select2('open');

                            // Get the search box within the dropdown or the selection
                            // Dropdown = single, Selection = multiple
                            var $search = $('.select2-input');
                            // This is undocumented and may change in the future

                            $search.val(selected);
                            $search.trigger('input');


                            if(productType == 'simple'){
                                setTimeout(function() { $('.select2-results').find('li').first().trigger("mouseup"); }, 500);
                            }

                            $(this).val('');

                        });





                        $('#selectbox-o').on("change", function(e) {

                            var data = $('#selectbox-o').select2('data');

                            $('#specification_copy').clone(true).insertBefore("#duplicate").attr('id', 'copied').css('border', '1px dashed #57D941').css('border-radius', '3px').fadeIn(600);

                            var price_without_vat = Math.floor(data.original_price / 1.21);

                            $('#copied .productName').html(data.pure_text);

                            $('#copied #copy_this_third').attr('name', 'product_sku[]').attr('value', data.id);
                            $('#copied #copy_this_quantity').attr('name', 'product_quantity[]').attr('value', '1');
                            $('#copied #copy_this_price').attr('name', 'product_price[]').attr('value', price_without_vat);
                            $('#copied #copy_this_original_price').attr('name', 'product_original_price[]').attr('value', price_without_vat);


                            $('#copied').attr('id', 'copifinish');

                            $("#selectbox-o").select2("val", "");

                            calc_price();

                            setTimeout(function(){
                                $('#copifinish').attr('id', 'hasfinish').css('border', "0").css('border-bottom', "1px solid #eeeeee").css('border-radius', "0"); }, 2500);

                        });


                        $('.remove_specification').click(function() {


                            event.preventDefault();
                            $(this).closest('.specification').css('border', '1px dashed #D90041').css('border-radius', '3px');

                            $.when($(this).closest('.specification').delay(100).fadeOut(600)).done(function() {
                                $(this).closest('.specification').remove();
                                calc_price();
                            });


                        });

                    });


					</script>

                            <div class="form-group">
                                <label for="field-2" class="col-sm-3 control-label">Měna</label>

                                <div class="col-sm-9">

                                    <div class="radio" style="float: left;">
                                        <label>
                                            <input class="currency" type="radio" id="currency_czk" name="currency" data-value="1" data-ext="Kč" value="CZK" <?php if(!isset($data['currency']) || $data['currency'] == 'CZK'){ echo 'checked'; }?>>CZK
                                        </label>
                                        <input style="display: none;" name="CZK_rate" value="<?= $kurz["CZK"] ?>">

                                    </div>
                                    <div class="radio" style="float: left; margin-left: 30px;">
                                        <label>
                                            <input class="currency" type="radio" id="currency_eur" name="currency" data-value="<?= $kurz["EUR"] ?>" data-ext="€" value="EUR" <?php if(isset($data['currency']) && $data['currency'] == 'EUR'){ echo 'checked'; }?>>EUR
                                        </label>
                                        <input style="display: none;" name="EUR_rate" value="<?= $kurz["EUR"] ?>">

                                    </div>
                                    <div class="radio" style="float: left; margin-left: 30px;">
                                        <label>
                                            <input class="currency" type="radio" id="currency_usd" name="currency" data-value="<?= $kurz["USD"] ?>" data-ext="$" value="USD" <?php if(isset($data['currency']) && $data['currency'] == 'USD'){ echo 'checked'; }?>>USD
                                        </label>
                                            <input style="display: none;" name="USD_rate" value="<?= $kurz["USD"] ?>">
                                    </div>
                                </div>


                            </div>



                            <hr>

                        <div class="form-group"><label for="field-2" class="col-sm-3 control-label">Aktuální kurz dle ČNB</label>
                            <div class="col-sm-8">
                                <h5>
                                <strong><?= $kurz["EUR"] ?></strong> CZK/EUR&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <strong><?= $kurz["USD"] ?></strong> CZK/USD</h5>
                            </div>
                            </div>
                            <hr>


					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Výpočet záloh</label>

						<div class="col-sm-8">

							<div class="radio" style="float: left;">
								<label>
									<input type="radio" id="deposit_type_percentage" name="deposit_type" value="percentage" <?php if ((!isset($data['deposit_type']) && $data['deposit_type'] == "") || $data['deposit_type'] == 'percentage') {echo 'checked';}
;?>>Procenta
								</label>
							</div>
							<div class="radio" style="float: left; margin-left: 30px;">
								<label>
									<input type="radio" id="deposit_type_money" name="deposit_type" value="money" <?php if (isset($data['deposit_type']) && $data['deposit_type'] == 'money') {echo 'checked';}?>>Částka
								</label>
							</div>
						</div>

					</div>



					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Počet faktur</label>

						<div class="col-sm-8">

							<div class="radio" style="float: left;">
								<label>
									<input type="radio" id="one_invoice" name="invoices_number" value="1" <?php if (isset($data['invoices_number']) && $data['invoices_number'] == '1') {echo 'checked';}
;?>>1
								</label>
							</div>
							<div class="radio" style="float: left; margin-left: 30px;">
								<label>
									<input type="radio" id="two_invoices" name="invoices_number" value="2" <?php if ((!isset($data['invoices_number']) && $data['invoices_number'] == "") || $data['invoices_number'] == '2') {echo 'checked';}?>>2
								</label>
							</div>
							<div class="radio" style="float: left; margin-left: 30px;">
								<label>
									<input type="radio" id="three_invoices" name="invoices_number" value="3" <?php if (isset($data['invoices_number']) && $data['invoices_number'] == '3') {echo 'checked';}?>>3
								</label>
							</div>
							<div class="radio" style="float: left; margin-left: 30px;">
								<label>
									<input type="radio" id="four_invoices" name="invoices_number" value="4" <?php if (isset($data['invoices_number']) && $data['invoices_number'] == '4') {echo 'checked';}?>>4
								</label>
							</div>
						</div>

					</div>


				<div id="percentage_values" <?php if (isset($data['deposit_type']) && $data['deposit_type'] == 'money') { ?>style="display: none;"<?php } ?>>

					<div class="form-group">

						<label for="field-2" class="col-sm-3 control-label">1. záloha v %</label>

						<div class="col-sm-3">
							<?php if (!empty($invoices_storno[1]) && $invoices_storno[1]['storno'] == 1): ?>
								<div style="position: relative;">
									<input type="text" name="percentage_deposit" class="form-control" value="<?php if (isset($data['deposit']) && $data['deposit'] != "" && $data['deposit_type'] == 'percentage') {echo $data['deposit'];} else {echo '50';}?>" disabled style="background-color: #fff0f0; color: #999; text-decoration: line-through;">
									<span style="position:absolute; right:8px; top:7px; color:#d9534f; font-size:11px; font-weight:bold;">STORNO</span>
								</div>
								<small style="color:#d9534f;"><i class="entypo-cancel"></i> Faktura č. <?= $invoices_storno[1]['id'] ?> byla stornována</small>
							<?php else: ?>
								<input type="text" name="percentage_deposit" class="form-control" value="<?php if (isset($data['deposit']) && $data['deposit'] != "" && $data['deposit_type'] == 'percentage') {echo $data['deposit'];} else {echo '50';}?>">
								<?php if (!empty($invoices_storno[1])): ?><small style="color:#5cb85c;"><i class="entypo-check"></i> Faktura č. <?= $invoices_storno[1]['id'] ?> aktivní</small><?php endif; ?>
							<?php endif; ?>
						</div>


						<div id="percentage_deposit_second" <?php if (isset($data['invoices_number']) && $data['invoices_number'] < '2') { ?>style="display: none;"<?php } ?>>
							<label for="field-2" class="col-sm-3 control-label">2. záloha v %</label>

							<div class="col-sm-3">
								<?php if (!empty($invoices_storno[2]) && $invoices_storno[2]['storno'] == 1): ?>
									<div style="position: relative;">
										<input type="text" name="percentage_deposit_second" class="form-control" value="<?php if (isset($data['deposit_second']) && $data['deposit_second'] != "" && $data['deposit_type'] == 'percentage') {echo $data['deposit_second'];} else {echo '50';}?>" disabled style="background-color: #fff0f0; color: #999; text-decoration: line-through;">
										<span style="position:absolute; right:8px; top:7px; color:#d9534f; font-size:11px; font-weight:bold;">STORNO</span>
									</div>
									<small style="color:#d9534f;"><i class="entypo-cancel"></i> Faktura č. <?= $invoices_storno[2]['id'] ?> byla stornována</small>
								<?php else: ?>
									<input type="text" name="percentage_deposit_second" class="form-control" value="<?php if (isset($data['deposit_second']) && $data['deposit_second'] != "" && $data['deposit_type'] == 'percentage') {echo $data['deposit_second'];} else {echo '50';}?>">
									<?php if (!empty($invoices_storno[2])): ?><small style="color:#5cb85c;"><i class="entypo-check"></i> Faktura č. <?= $invoices_storno[2]['id'] ?> aktivní</small><?php endif; ?>
								<?php endif; ?>
							</div>
						</div>

					</div>
					<div class="form-group">

						<div id="percentage_deposit_third" <?php if ((!isset($data['invoices_number']) && $data['invoices_number'] == "") || $data['invoices_number'] < '3') { ?>style="display: none;"<?php } ?>>
							<label for="field-2" class="col-sm-3 control-label">3. záloha v %</label>

							<div class="col-sm-3">
								<?php if (!empty($invoices_storno[3]) && $invoices_storno[3]['storno'] == 1): ?>
									<div style="position: relative;">
										<input type="text" name="percentage_deposit_third" class="form-control" value="<?php if (isset($data['deposit_third']) && $data['deposit_third'] != "" && $data['deposit_type'] == 'percentage') {echo $data['deposit_third'];} else {echo '50';}?>" disabled style="background-color: #fff0f0; color: #999; text-decoration: line-through;">
										<span style="position:absolute; right:8px; top:7px; color:#d9534f; font-size:11px; font-weight:bold;">STORNO</span>
									</div>
									<small style="color:#d9534f;"><i class="entypo-cancel"></i> Faktura č. <?= $invoices_storno[3]['id'] ?> byla stornována</small>
								<?php else: ?>
									<input type="text" name="percentage_deposit_third" class="form-control" value="<?php if (isset($data['deposit_third']) && $data['deposit_third'] != "" && $data['deposit_type'] == 'percentage') {echo $data['deposit_third'];} else {echo '50';}?>">
									<?php if (!empty($invoices_storno[3])): ?><small style="color:#5cb85c;"><i class="entypo-check"></i> Faktura č. <?= $invoices_storno[3]['id'] ?> aktivní</small><?php endif; ?>
								<?php endif; ?>
							</div>
						</div>

						<div id="percentage_deposit_fourth" <?php if ($data['invoices_number'] < '4') { ?>style="display: none;"<?php } ?>>
							<label for="field-2" class="col-sm-3 control-label">4. záloha v %</label>

							<div class="col-sm-3">
								<?php if (!empty($invoices_storno[4]) && $invoices_storno[4]['storno'] == 1): ?>
									<div style="position: relative;">
										<input type="text" name="percentage_deposit_fourth" class="form-control" value="<?php if (isset($data['deposit_fourth']) && $data['deposit_fourth'] != "" && $data['deposit_type'] == 'percentage') {echo $data['deposit_fourth'];} else {echo '50';}?>" disabled style="background-color: #fff0f0; color: #999; text-decoration: line-through;">
										<span style="position:absolute; right:8px; top:7px; color:#d9534f; font-size:11px; font-weight:bold;">STORNO</span>
									</div>
									<small style="color:#d9534f;"><i class="entypo-cancel"></i> Faktura č. <?= $invoices_storno[4]['id'] ?> byla stornována</small>
								<?php else: ?>
									<input type="text" name="percentage_deposit_fourth" class="form-control" value="<?php if (isset($data['deposit_fourth']) && $data['deposit_fourth'] != "" && $data['deposit_type'] == 'percentage') {echo $data['deposit_fourth'];} else {echo '50';}?>">
									<?php if (!empty($invoices_storno[4])): ?><small style="color:#5cb85c;"><i class="entypo-check"></i> Faktura č. <?= $invoices_storno[4]['id'] ?> aktivní</small><?php endif; ?>
								<?php endif; ?>
							</div>
						</div>


					</div>

				</div>

				<div id="money_values" <?php if ((!isset($data['deposit_type']) && $data['deposit_type'] == "") || $data['deposit_type'] == 'percentage') { ?> style="display: none;"<?php } ?>>

					<div class="form-group">

						<label for="field-2" class="col-sm-3 control-label">1. záloha v Kč bez DPH</label>

						<div class="col-sm-3">
							<?php if (!empty($invoices_storno[1]) && $invoices_storno[1]['storno'] == 1): ?>
								<div style="position: relative;">
									<input type="text" name="money_deposit" class="form-control" value="<?php if (isset($data['deposit']) && $data['deposit'] != "" && $data['deposit_type'] == 'money') {echo $data['deposit'];} else {echo '50';}?>" disabled style="background-color: #fff0f0; color: #999; text-decoration: line-through;">
									<span style="position:absolute; right:8px; top:7px; color:#d9534f; font-size:11px; font-weight:bold;">STORNO</span>
								</div>
								<small style="color:#d9534f;"><i class="entypo-cancel"></i> Faktura č. <?= $invoices_storno[1]['id'] ?> byla stornována</small>
							<?php else: ?>
								<input type="text" name="money_deposit" class="form-control" value="<?php if (isset($data['deposit']) && $data['deposit'] != "" && $data['deposit_type'] == 'money') {echo $data['deposit'];} else {echo '50';}?>">
								<?php if (!empty($invoices_storno[1])): ?><small style="color:#5cb85c;"><i class="entypo-check"></i> Faktura č. <?= $invoices_storno[1]['id'] ?> aktivní</small><?php endif; ?>
							<?php endif; ?>
						</div>


						<div id="money_deposit_second" <?php if (isset($data['invoices_number']) && $data['invoices_number'] < '2') { ?>style="display: none;"<?php } ?>>
							<label for="field-2" class="col-sm-3 control-label">2. záloha v Kč bez DPH</label>

							<div class="col-sm-3">
								<?php if (!empty($invoices_storno[2]) && $invoices_storno[2]['storno'] == 1): ?>
									<div style="position: relative;">
										<input type="text" name="money_deposit_second" class="form-control" value="<?php if (isset($data['deposit_second']) && $data['deposit_second'] != "" && $data['deposit_type'] == 'money') {echo $data['deposit_second'];} else {echo '50';}?>" disabled style="background-color: #fff0f0; color: #999; text-decoration: line-through;">
										<span style="position:absolute; right:8px; top:7px; color:#d9534f; font-size:11px; font-weight:bold;">STORNO</span>
									</div>
									<small style="color:#d9534f;"><i class="entypo-cancel"></i> Faktura č. <?= $invoices_storno[2]['id'] ?> byla stornována</small>
								<?php else: ?>
									<input type="text" name="money_deposit_second" class="form-control" value="<?php if (isset($data['deposit_second']) && $data['deposit_second'] != "" && $data['deposit_type'] == 'money') {echo $data['deposit_second'];} else {echo '50';}?>">
									<?php if (!empty($invoices_storno[2])): ?><small style="color:#5cb85c;"><i class="entypo-check"></i> Faktura č. <?= $invoices_storno[2]['id'] ?> aktivní</small><?php endif; ?>
								<?php endif; ?>
							</div>
						</div>

					</div>
					<div class="form-group">

						<div id="money_deposit_third" <?php if ((!isset($data['invoices_number']) && $data['invoices_number'] == "") || $data['invoices_number'] < '3') { ?>style="display: none;"<?php } ?>>
							<label for="field-2" class="col-sm-3 control-label">3. záloha v Kč bez DPH</label>

							<div class="col-sm-3">
								<?php if (!empty($invoices_storno[3]) && $invoices_storno[3]['storno'] == 1): ?>
									<div style="position: relative;">
										<input type="text" name="money_deposit_third" class="form-control" value="<?php if (isset($data['deposit_third']) && $data['deposit_third'] != "" && $data['deposit_type'] == 'money') {echo $data['deposit_third'];} else {echo '50';}?>" disabled style="background-color: #fff0f0; color: #999; text-decoration: line-through;">
										<span style="position:absolute; right:8px; top:7px; color:#d9534f; font-size:11px; font-weight:bold;">STORNO</span>
									</div>
									<small style="color:#d9534f;"><i class="entypo-cancel"></i> Faktura č. <?= $invoices_storno[3]['id'] ?> byla stornována</small>
								<?php else: ?>
									<input type="text" name="money_deposit_third" class="form-control" value="<?php if (isset($data['deposit_third']) && $data['deposit_third'] != "" && $data['deposit_type'] == 'money') {echo $data['deposit_third'];} else {echo '50';}?>">
									<?php if (!empty($invoices_storno[3])): ?><small style="color:#5cb85c;"><i class="entypo-check"></i> Faktura č. <?= $invoices_storno[3]['id'] ?> aktivní</small><?php endif; ?>
								<?php endif; ?>
							</div>
						</div>

						<div id="money_deposit_fourth" <?php if ($data['invoices_number'] < '4') { ?>style="display: none;"<?php } ?>>
							<label for="field-2" class="col-sm-3 control-label">4. záloha v Kč bez DPH</label>

							<div class="col-sm-3">
								<?php if (!empty($invoices_storno[4]) && $invoices_storno[4]['storno'] == 1): ?>
									<div style="position: relative;">
										<input type="text" name="money_deposit_fourth" class="form-control" value="<?php if (isset($data['deposit_fourth']) && $data['deposit_fourth'] != "" && $data['deposit_type'] == 'money') {echo $data['deposit_fourth'];} else {echo '50';}?>" disabled style="background-color: #fff0f0; color: #999; text-decoration: line-through;">
										<span style="position:absolute; right:8px; top:7px; color:#d9534f; font-size:11px; font-weight:bold;">STORNO</span>
									</div>
									<small style="color:#d9534f;"><i class="entypo-cancel"></i> Faktura č. <?= $invoices_storno[4]['id'] ?> byla stornována</small>
								<?php else: ?>
									<input type="text" name="money_deposit_fourth" class="form-control" value="<?php if (isset($data['deposit_fourth']) && $data['deposit_fourth'] != "" && $data['deposit_type'] == 'money') {echo $data['deposit_fourth'];} else {echo '50';}?>">
									<?php if (!empty($invoices_storno[4])): ?><small style="color:#5cb85c;"><i class="entypo-check"></i> Faktura č. <?= $invoices_storno[4]['id'] ?> aktivní</small><?php endif; ?>
								<?php endif; ?>
							</div>
						</div>


					</div>

				</div>



						<hr>

					<div class="form-group">
						<label for="field-2" class="col-sm-1 control-label">DPH</label>

											<div class="col-sm-2">
												<input  type="text" name="price_vat" class="form-control" value="<?php if (isset($data['price_vat']) && $data['price_vat'] != "" && $data['price_vat'] != '15') {echo $data['price_vat'];} else {echo '12';}?>">
											</div>

						<label for="field-2" class="col-sm-4 control-label">Přenesená daňová povinnost</label>
						<div class="col-sm-3">
							<div class="radio" style="width: 70px; float: left;">
								<label>
									<input type="radio" name="reverse_charge" value="Ano" <?php if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {echo 'checked';}?>>Ano
								</label>
							</div>
							<div class="radio" style="width: 40px;float: left;">
								<label>
									<input type="radio" name="reverse_charge" value="Ne" <?php if (!isset($data['reverse_charge']) || $data['reverse_charge'] == "") {echo 'checked';} else {if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ne') {echo 'checked';}}?>>Ne
								</label>
							</div>

						</div>

                       <?php /*
                        <label for="field-2" class="col-sm-2 control-label">Odstranit DPH značení</label>

                        <div class="col-sm-3">
                            <div class="radio" style="width: 70px; float: left;">
                                <label>
                                    <input type="radio" name="remove_info" value="Ne" <?php if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ne') {echo 'checked';}?>>Ne
                                </label>
                            </div>
                            <div class="radio" style="width: 40px;float: left;">
                                <label>
                                    <input type="radio" name="remove_info" value="Ano" <?php if (!isset($data['reverse_charge']) || $data['reverse_charge'] == "") {echo 'checked';} else {if (isset($data['reverse_charge']) && $data['reverse_charge'] == 'Ano') {echo 'checked';}}?>>Ano
                                </label>
                            </div>
                        </div>

                        */?>

                    </div>

                    <div class="form-group">

                        <select class="form-control" name="affidavit" style="margin-left: 10px;">
                            <option value="0"
                                <?php if (isset($data['affidavit']) && $data['affidavit'] == 0) {echo 'selected';}?>>
                                ZAŘAZENÍ ZAKÁZKY dle zákona
                            </option>
                            <option value="1"
                                <?php if (isset($data['affidavit']) && $data['affidavit'] == 1) {echo 'selected';}?>>
                                a, rodinný dům o ploše do 350m2, nebo byt o ploše do 120m2 12% § 48 odst. 5 ZDPH
                            </option>
                            <option value="2"
                                <?php if (isset($data['affidavit']) && $data['affidavit'] == 2) {echo 'selected';}?>>
                                b, nezisková organizace do 350m2 (nad 350m2 přiděleno DIČ např.SVJ) 12% § 49 ZDPH
                            </option>
                            <option value="3"
                                <?php if (isset($data['affidavit']) && $data['affidavit'] == 3) {echo 'selected';}?>>
                                c, fyzická osoba v pronájmu 12% § 49 ZDPH
                            </option>
                            <option value="4"
                                <?php if (isset($data['affidavit']) && $data['affidavit'] == 4) {echo 'selected';}?>>
                                d, právnická osoba, která není plátcem DPH – nemá přidělen DIČ 21%
                            </option>
                            <option value="5"
                                <?php if (isset($data['affidavit']) && $data['affidavit'] == 5) {echo 'selected';}?>>
                                e, fyzická osoba (OSVČ), která není plátcem DPH – nemá přidělen DIČ 21%
                            </option>
                            <option value="6"
                                <?php if (isset($data['affidavit']) && $data['affidavit'] == 6) {echo 'selected';}?>>
                                f, právnická osoba – součást stavby (součást bytové výstavby), plátce DPH-přiděleno DIČ 0% režim PDP (CZ-CPA 43.22.11)
                            </option>
                            <option value="7"
                                <?php if (isset($data['affidavit']) && $data['affidavit'] == 7) {echo 'selected';}?>>
                                g, fyzická osoba – součást stavby, plátce DPH-přiděleno DIČ 0% režim PDP (CZ-CPA 43.22.11)
                            </option>
                            <option value="8"
                                <?php if (isset($data['affidavit']) && $data['affidavit'] == 8) {echo 'selected';}?>>
                                h, firma-prodej do EU, vždy bez instalace - přiděleno DIČ (VAT) 0% režim reverse charge
                            </option>
                            <option value="9"
                                <?php if (isset($data['affidavit']) && $data['affidavit'] == 9) {echo 'selected';}?>>
                                ch, fyzická osoba-prodej do EU, vždy bez instalace - bez DIČ 21%
                            </option>
                            <option value="10"
                                <?php if (isset($data['affidavit']) && $data['affidavit'] == 10) {echo 'selected';}?>>
                                i, právnická i fyzická osoba-prodej do 3.země (mimo ČR a EU) 21%
                            </option>
                            <option value="11"
                                <?php if (isset($data['affidavit']) && $data['affidavit'] == 11) {echo 'selected';}?>>
                                j, ostatní 21% DPH
                            </option>
                        </select>


                    </div>


</div>

		</div>


		<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Doplňující ujednání
					</div>

				</div>

						<div class="panel-body">


					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Kupní smlouva <br>(11. bod)</label>

						<div class="col-sm-8">
							<textarea name="other_arrangements" class="form-control"><?= $data['other_arrangements'] ?></textarea>
						</div>
					</div>

					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Předávací protokol <br>(8. bod)</label>

						<div class="col-sm-8">
							<textarea name="other_purchase_protocol" class="form-control"><?= $data['other_purchase_protocol'] ?></textarea>
						</div>
					</div>

                            <?php if (isset($getclient['customer']) && $getclient['customer'] == 1 || $getclient['customer'] == 3) {

    $data_hottub_query = $mysqli->query("SELECT * FROM demands_generate_hottub WHERE id = '$id'");
    $data_hottub = mysqli_fetch_array($data_hottub_query);

    ?>


                    <h4>Záruky</h4>
                    <div class="form-group">
                        <label for="field-2" class="col-sm-3 control-label">Hydromasážní pumpy, cirkulační pumpa, vodoinstalace, opláštění, trysky a  ovládací prvky</label>

                        <div class="col-sm-3">
                            <input  type="number" name="warranty_first" class="form-control" value="<?php if (!empty($data_hottub['warranty_first'])){ echo $data_hottub['warranty_first'];} else { echo '5';}?>">
                        </div>
                        <label for="field-2" class="col-sm-3 control-label">Řídící systém, ovládací panel</label>

                        <div class="col-sm-3">
                            <input  type="number" name="warranty_second" class="form-control" value="<?php if (!empty($data_hottub['warranty_second'])){ echo $data_hottub['warranty_second'];} else { echo '5';}?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="field-2" class="col-sm-3 control-label">Akrylátová skořepina</label>

                        <div class="col-sm-3">
                            <input  type="number" name="warranty_third" class="form-control" value="<?php if (!empty($data_hottub['warranty_third'])){ echo $data_hottub['warranty_third'];} else { echo '10';}?>">
                        </div>
                        <label for="field-2" class="col-sm-3 control-label">Na všechny ostatní komponenty</label>

                        <div class="col-sm-3">
                            <input  type="number" name="warranty_fourth" class="form-control" value="<?php if (!empty($data_hottub['warranty_fourth'])){ echo $data_hottub['warranty_fourth'];} else { echo '2';}?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="field-2" class="col-sm-3 control-label">Konstrukce vířivky</label>

                        <div class="col-sm-3">
                            <input  type="number" name="warranty_fifth" class="form-control" value="<?php if (!empty($data_hottub['warranty_fifth'])){ echo $data_hottub['warranty_fifth'];} else { echo '10';}?>">
                        </div>
                    </div>


                            <?php } ?>

                    <?php if (isset($getclient['customer']) && $getclient['customer'] == 4) {

                        $data_pergola_query = $mysqli->query("SELECT * FROM demands_generate_pergola WHERE id = '$id'");
                        $data_pergola = mysqli_fetch_array($data_pergola_query);

                        ?>

                                <h4>Záruky</h4>
                                <div class="form-group">
                                    <label for="field-2" class="col-sm-3 control-label">Na pergolu</label>

                                    <div class="col-sm-3">
                                        <input  type="number" name="warranty_first" class="form-control" value="<?php if (!empty($data_pergola['warranty_first'])){ echo $data_pergola['warranty_first'];} else { echo '2';}?>">
                                    </div>
                                  <?php /*  ?>
                                    <label for="field-2" class="col-sm-3 control-label">Druhá záruka</label>

                                    <div class="col-sm-3">
                                        <input  type="number" name="warranty_second" class="form-control" value="<?php if (!empty($data_pergola['warranty_second'])){ echo $data_pergola['warranty_second'];} else { echo '7';}?>">
                                    </div>
                                    <?php */ ?>
                                </div>

                                <?php /* ?>

                                <div class="form-group">
                                    <label for="field-2" class="col-sm-3 control-label">Třetí záruka</label>

                                    <div class="col-sm-3">
                                        <input  type="number" name="warranty_third" class="form-control" value="<?php if (!empty($data_pergola['warranty_third'])){ echo $data_pergola['warranty_third'];} else { echo '10';}?>">
                                    </div>
                                    <label for="field-2" class="col-sm-3 control-label">Čtvrtá záruka</label>

                                    <div class="col-sm-3">
                                        <input  type="number" name="warranty_fourth" class="form-control" value="<?php if (!empty($data_pergola['warranty_fourth'])){ echo $data_pergola['warranty_fourth'];} else { echo '2';}?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="field-2" class="col-sm-3 control-label">Pátá záruka</label>

                                    <div class="col-sm-3">
                                        <input  type="number" name="warranty_fifth" class="form-control" value="<?php if (!empty($data_pergola['warranty_fifth'])){ echo $data_pergola['warranty_fifth'];} else { echo '10';}?>">
                                    </div>
                                </div>
                                <?php */ ?>


                            <?php } ?>


				</div>
			</div>



</div>
</div>



					</div>



<?php if (isset($getclient['customer']) && $getclient['customer'] == 1 || $getclient['customer'] == 3) {

    ?>




					<div class="row">

					<div class="col-md-12" style="padding: 0">

			<div class="col-md-8">



				<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Produktové informace
					</div>

				</div>

						<div class="panel-body">

									<div class="form-group">
											<label for="field-2" class="col-sm-3 control-label">Dodací doba</label>

											<div class="col-sm-2">
												<input  type="text" name="delivery_time" class="form-control" value="<?php if (isset($data_hottub['delivery_time']) && $data_hottub['delivery_time'] != "") {echo $data_hottub['delivery_time'];} else {echo '6 - 7 týdnů';}?>">
											</div>

											<label for="field-2" class="col-sm-2 control-label">Dodací adresa</label>

											<div class="col-sm-4">
												<input  type="text" name="delivery_address" class="form-control" value="<?php if (isset($data_hottub['delivery_address']) && $data_hottub['delivery_address'] != "") {echo $data_hottub['delivery_address'];} else {

        if (isset($address['shipping_street']) && $address['shipping_street'] != '') {

            echo $address['shipping_street'] . ', ' . $address['shipping_city'] . ', ' . $address['shipping_zipcode'];

        } else {

            echo $address['billing_street'] . ', ' . $address['billing_city'] . ', ' . $address['billing_zipcode'];

        }

    }?>">
											</div>
										</div>

										<hr>



				<?php

    if ($getclient['realization'] != "0000-00-00") {

        $date_planned = $getclient['realization'];

    } else {

        if (isset($data_hottub['planned_date']) && $data_hottub['planned_date'] != "" && $data_hottub['planned_date'] != "0000-00-00") {

            $date_planned = $data_hottub['planned_date'];

        } else { $date_planned = date('Y-m-d');}

    }

    ?>


                            <div class="form-group">
                                <div class="col-sm-4">
                                    <div class="well" style="padding: 6px 8px 10px 8px; width: 100%; margin: 10px 0 0; float: left;">

                                        <div class="radio" style="float: left; margin-left: 10px;">
                                            <label>
                                                <input type="radio" name="area" value="prague" <?php
                                                if ((isset($getclient['area']) && $getclient['area'] == 'prague') || (!empty($getclient['showroom']) && $getclient['showroom'] == 2 && $getclient['area'] == 'unknown')) {
                                                    echo 'checked';
                                                } ?>>Praha
                                            </label>
                                        </div>
                                        <div class="radio" style="float: left;margin-left: 18px;">
                                            <label>
                                                <input type="radio" name="area" value="brno" <?php if((isset($getclient['area']) && $getclient['area'] == 'brno') || (!empty($getclient['showroom']) && $getclient['showroom'] == 3 && $getclient['area'] == 'unknown')) {
                                                    echo 'checked';
                                                } ?>>Brno
                                            </label>
                                        </div>
                                    </div>

                                </div>

                            <label for="field-2" class="col-sm-2 control-label">Plánovaná realizace</label>

						<div class="col-sm-2">
							<input id="datum5" type="text" class="form-control datepicker" name="planned_date_hottub" data-format="yyyy-mm-dd" placeholder="Datum provedení..." value="<?= $date_planned ?>" data-validate="required" data-message-required="Musíte zadat datum.">
						</div>


						<label for="field-2" class="col-sm-2 control-label">Deadline realizace</label>

						<div class="col-sm-2">
							<input id="datum5" type="text" class="form-control datepicker" name="deadline_date_hottub" data-format="yyyy-mm-dd" placeholder="Datum provedení..." value="<?php if (isset($data_hottub['deadline_date']) && $data_hottub['deadline_date'] != "" && $data_hottub['deadline_date'] != "0000-00-00") {echo $data_hottub['deadline_date'];}?>" data-validate="required" data-message-required="Musíte zadat datum.">
						</div>

					</div>

                            <div class="col-md-12" style="padding: 0;">

                                <?php

                                $adminsquery = $mysqli->query("SELECT id, user_name, role FROM demands WHERE role != 'client' AND active = 1");

                               ?>
                                    <div class="well admins_well" style="padding: 12px 0px 7px; width: 49.5%; margin-right: 0.5%;  margin-bottom: 0; float: left;">
                                        <h4 style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">
                                            Proveditelé</h4>
                                        <?php

                                        if($getclient['customer'] == '0'){

                                            $type = 'realization_sauna';

                                        }elseif($getclient['customer'] == '1'){

                                            $type = 'realization_hottub';

                                        }elseif($getclient['customer'] == '4'){

                                            $type = 'realization_pergola';

                                        }
                                        $adminsquery = $mysqli->query("SELECT id, user_name FROM demands WHERE role != 'client' AND active = 1");
                                        while ($admins = mysqli_fetch_array($adminsquery)) {

                                            $find_query = $mysqli->query("SELECT admin_id FROM mails_recievers WHERE type_id = '" . $getclient['id'] . "' AND admin_id = '" . $admins['id'] . "' AND type = '".$type."' AND reciever_type = 'performer'") or die($mysqli->error);

                                            ?><div class="col-sm-4" style="padding: 0 6px 0 12px;">

                                            <input id="real-admin-<?= $admins['id'] ?>-performer" name="performer[]" value="<?= $admins['id'] ?>" type="checkbox" <?php if (mysqli_num_rows($find_query) > 0) { echo 'checked'; } ?>>
                                            <label for="real-admin-<?= $admins['id'] ?>-performer" style="padding-left: 4px; cursor: pointer;"><?= $admins['user_name'] ?></label>
                                            </div>
                                        <?php } ?>
                                    </div>


                                    <div class="well admins_well" style="padding: 12px 0px 7px; width: 49.5%; margin-left: 0.5%; margin-bottom: 0; float: left;">
                                        <h4 style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">
                                            Informovaní</h4>
                                        <?php



                                        $adminsquery = $mysqli->query("SELECT id, user_name, role FROM demands WHERE role != 'client' AND active = 1");
                                        while ($admins = mysqli_fetch_array($adminsquery)) {

                                            $find_query = $mysqli->query("SELECT admin_id FROM mails_recievers WHERE type_id = '" . $getclient['id'] . "' AND admin_id = '" . $admins['id'] . "' AND type = '".$type."' AND reciever_type = 'observer'") or die($mysqli->error);

                                            ?><div class="col-sm-4" style="padding: 0 6px 0 12px;">

                                            <input id="real-admin-<?= $admins['id'] ?>-observer" name="observer[]" value="<?= $admins['id'] ?>" type="checkbox" <?php if (mysqli_num_rows($find_query) > 0 || $admins['role'] == 'salesman-technician') { echo 'checked'; } ?>>
                                            <label for="real-admin-<?= $admins['id'] ?>-observer" style="padding-left: 4px; cursor: pointer; <?php if(!empty($client['id']) && $client['id'] == $admins['id']){ echo 'color: green !important;'; }?>"><?= $admins['user_name'] ?></label>
                                            </div>
                                        <?php } ?>
                                    </div>
                            </div>
					</div>

							</div>

		<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Změna specifikací u poptávky
					</div>

				</div>

						<div class="panel-body">




<?php

    if (isset($getclient['customer']) && $getclient['customer'] == 1 || $getclient['customer'] == 3) {

        specs_demand($getclient, '1');
        specs_demand($getclient, '2');

    ?>


				</div>
			</div>

		</div>

		<?php

    $get_provedeni = $mysqli->query("SELECT t.id FROM demands_specs_bridge d, warehouse_products_types t, warehouse_products p WHERE d.client_id = '" . $getclient['id'] . "' AND d.specs_id = 5 AND t.warehouse_product_id = p.id AND p.connect_name = '" . $getclient['product'] . "' AND t.name = d.value") or die($mysqli->error);
    $provedeni = mysqli_fetch_array($get_provedeni);


//    old to delete
//    $price_query = $mysqli->query("SELECT * FROM warehouse_versions WHERE product = '" . $getclient['product'] . "' AND version = '" . $provedeni['id'] . "'") or die($mysqli->error);
//    $price = mysqli_fetch_array($price_query);
//    old to delete


//    if(isset($data['currency']) && $data['currency'] == 'CZK'){ $multiply = 1; }elseif(isset($data['currency']) && $data['currency'] == 'EUR'){ $multiply = $kurz['EUR']; }elseif(isset($data['currency']) && $data['currency'] == 'USD') { $multiply = $kurz['USD']; }
    }
    ?>

		<div class="col-md-4">


			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
                        Cena a vybavení <small>(uvádějte ve zvolené měně)</small>
					</div>

				</div>

						<div class="panel-body">
					<div class="form-group">
						<label for="field-2" class="col-sm-5 control-label">Cena vířivky</label>

						<div class="col-sm-7">
							<input  type="text" name="price_hottub" class="form-control price-control" data-default="<?php if (isset($data_hottub['price_hottub']) && $data_hottub['price_hottub'] != "") {echo round($data_hottub['price_hottub'] * $data['exchange_rate']);} else { echo '0';}?>" value="<?php if (isset($data_hottub['price_hottub']) && $data_hottub['price_hottub'] != "") {echo $data_hottub['price_hottub'];} else { echo '0'; }?>">
						</div>
					</div>


					<div class="form-group">
						<label for="field-2" class="col-sm-5 control-label">Sleva</label>

						<div class="col-sm-7">
							<input  type="text" name="discount" class="form-control discount" data-default='<?= $data_hottub['discount'] * $data['exchange_rate'] ?>' value="<?= !empty($data_hottub['discount']) ? $data_hottub['discount'] : '' ?>">
						</div>

					</div>
					<hr>

                            <div class="form-group">
                                <label for="field-2" class="col-sm-5 control-label"></label>
                                <div class="col-sm-5">
                                </div>
                                <div class="col-sm-2">
                                  Generovat?
                                </div>
                            </div>

<?php

    // selling price is taken from specs db table
    $generate_specs_query = $mysqli->query("SELECT * FROM specs s LEFT JOIN demands_specs_bridge d ON d.specs_id = s.id AND d.client_id = '" . $getclient['id'] . "' WHERE s.generate = 1 ORDER BY s.demand_order ASC") or die($mysqli->error);
    while ($generate_spec = mysqli_fetch_array($generate_specs_query)) {

        ?>
                        <div class="price_<?= $generate_spec['seoslug'] ?> form-group" <?php if (isset($generate_spec['value']) && $generate_spec['value'] == 'Ne' || !isset($generate_spec['value']) || $generate_spec['value'] == "") { ?>style="display: none;"<?php }?>>
                            <label for="field-2" class="col-sm-5 control-label"><?= $generate_spec['name'] ?></label>

                            <div class="col-sm-5">
                                <input type="text" name="price_<?= $generate_spec['seoslug'] ?>" class="form-control price-control" data-default="<?php if (isset($generate_spec['price']) && $generate_spec['price'] != "") {echo round($generate_spec['price'] * $data['exchange_rate']);} else {echo $generate_spec['generate_price'];}?>" value="<?php if (isset($generate_spec['price']) && $generate_spec['price'] != "") {echo $generate_spec['price'];} else {echo $generate_spec['generate_price'];}?>" <?php if (isset($generate_spec['value']) && $generate_spec['value'] == 'Ne' || !isset($generate_spec['value']) || $generate_spec['value'] == "") { ?>disabled<?php }?>>
                            </div>
                            <div class="col-sm-2">
                               <input class="form-control" name="is_generated_<?= $generate_spec['seoslug'] ?>" value="1" type="checkbox" <?php if (isset($generate_spec['is_generated']) && $generate_spec['is_generated']) { echo 'checked'; } ?> style="width: auto;">
                            </div>
                        </div>
<?php } ?>
					<div class="form-group">
						<label for="field-2" class="col-sm-4 control-label">Chemie</label>

						<div class="col-sm-8">
							<select class="form-control" name="chemie_type">
									<option value="1" <?php if (isset($data_hottub['chemie_type']) && $data_hottub['chemie_type'] == '1') { echo 'selected'; }?>>1. pH+, pH-, oxy, aktivátor, testery</option>
									<option value="2" <?php if (isset($data_hottub['chemie_type']) && $data_hottub['chemie_type'] == '2') { echo 'selected'; }?>>2. pH+, pH-, testery, chlor</option>
									<option value="3" <?php if (isset($data_hottub['chemie_type']) && $data_hottub['chemie_type'] == '3') { echo 'selected'; }?>>3. pH+, pH-, testery, BromiCharge</option>
									<option value="4" <?php if (isset($data_hottub['chemie_type']) && $data_hottub['chemie_type'] == '4') { echo 'selected'; } else { echo 'selected';  } ?>>4. Spa Clear, ph+, pH-, testery</option>
									<option value="5" <?php if (isset($data_hottub['chemie_type']) && $data_hottub['chemie_type'] == '5') { echo 'selected'; }?>>5. Polynode, ph+, pH-, testery</option>
									<option value="6" <?php if (isset($data_hottub['chemie_type']) && $data_hottub['chemie_type'] == '6') { echo 'selected'; }?>>6. Brom, ph+, pH-, testery</option>
                                <option value="0" <?php if (isset($data_hottub['chemie_type']) && $data_hottub['chemie_type'] == '0') { echo 'selected'; }?>>žádná chemie</option>

								</select>

                            <input  type="text" name="price_chemie" class="form-control price-control" data-default="<?php if (isset($data_hottub['price_chemie']) && $data_hottub['price_chemie'] != "") {echo round($data_hottub['price_chemie'] * $data['exchange_rate']);} else {echo '990';}?>" value="<?php if (isset($data_hottub['price_chemie']) && $data_hottub['price_chemie'] != "") {echo $data_hottub['price_chemie'];} else {echo '990';}?>">
						</div>
					</div>

                            <hr>

                            <div class="">

                                <select class="form-control quick-search">

                                    <option value="">Rychlý výběr příslušenství</option>

                                    <option value="Automatický dávkovač" data-type="variable">Automatický dávkovač - více variant</option>
                                    <option value="CoverMate" data-type="variable">Držák na kryt - více variant</option>
                                    <option value="FIBALON" data-type="simple">FIBALON</option>
                                    <option value="Filter Cleaner" data-type="simple">Filter Cleaner</option>
                                    <option value="Madlo Safe-T-Rail" data-type="variable">Madlo Safe-T-Rail - více variant</option>
                                    <option value="Madlo Safe-T-Rail II" data-type="variable">Madlo Safe-T-Rail II - více variant</option>
                                    <option value="Filtrační zařízení WM300" data-type="simple">Písková filtrace</option>
                                    <option value="Spa Caddy" data-type="simple">Spa Caddy</option>
                                    <option value="Spa Sponge" data-type="simple">Spa Sponge</option>
                                    <option value="TowelBar" data-type="simple">TowelBar</option>
                                    <option value="Water Wand" data-type="simple">Water Wand</option>
                                </select>

                            </div>

                            <hr style="margin-bottom: 0;">
    <div class="form-group">
        <div class="col-sm-12" >
            <input id="selectbox-o" class="input-xlarge" name="optionvalue" type="hidden" data-placeholder="Vyberte produkt.." />
        </div>
    </div>


    <hr style="margin-bottom: 0;">

    <div class="form-group">

        <div class="col-sm-12" style="float:left;">


            <div id="specification_copy" class="specification" style="display: none; float:left; width: 100%; margin: 0px 0; padding: 10px 0; border-bottom: 1px solid #eeeeee;">

                <div class="col-sm-6" style="padding: 0;">

                    <span class="productName" style="height: 42px; display: table-cell; vertical-align: middle;"></span>

                    <input type="text" class="form-control" id="copy_this_third" name="copythis" value="" placeholder="SKU produktu" style="display: none;">

                </div>

                <div class="col-sm-2">
                    <input type="text" class="form-control text-center quantity-control" id="copy_this_quantity" name="copy_this_quantity" value="1" placeholder="Množství" style="padding: 0; height: 42px;">
                </div>
                <div class="col-sm-3" style="padding: 0 0px 0 8px;">

                    <input type="text" class="form-control text-center price-control" id="copy_this_price" name="copythis" value="" placeholder="Cena" style="padding: 0; height: 42px;">

                    <input type="text" class="form-control text-center price-control" id="copy_this_original_price" name="copythis" value="" placeholder="Původní cena" style="padding: 0; height: 42px; display: none;">

                </div>

                <div class="col-sm-1" style="padding: 0 0px 0 11px;">
                    <button type="button" class="remove_specification btn btn-red" style="float:left; padding: 6px 10px; cursor: pointer; height: 42px;"> <i class="entypo-trash"></i> </button>
                </div>
            </div>


            <?php

                $products_bridge = $mysqli->query("SELECT * FROM demands_accessories_bridge WHERE aggregate_id = '".$getclient['id']."'")or die($mysqli->error);

                while ($bridge = mysqli_fetch_assoc($products_bridge)) {

                    if ($bridge['variation_id'] != 0) {

                        $product_query = $mysqli->query("SELECT *, s.id as ajdee, s.price as price FROM products p, products_variations s WHERE p.id = '" . $bridge['product_id'] . "' AND p.id = s.product_id AND s.id = '" . $bridge['variation_id'] . "'");
                        $product = mysqli_fetch_assoc($product_query);

                        $select = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $product['ajdee'] . "'") or die($mysqli->error);
                        $desc = "";
                        while ($var = mysqli_fetch_assoc($select)) {

                            $desc = $desc . $var['name'] . ': ' . $var['value'] . ' ';

                        }

                        $price = number_format($product['price'], 0, ',', ' ') . ' Kč';

                        $product_title = $product['productname'] . ' – ' . $desc;

                        $sku = $product['sku'];

                    } else {

                        $product_query = $mysqli->query("SELECT * FROM products p WHERE id = '" . $bridge['product_id'] . "'");

                        $product = mysqli_fetch_assoc($product_query);

                        $price = number_format($product['price'], 0, ',', ' ') . ' Kč';

                        $product_title = $product['productname'];

                        $sku = $product['code'];

                    }

                    ?>
                    <div class="specification" style="float: left; width: 100%; margin: 0px 0; padding: 10px 0; border-bottom: 1px solid #eeeeee;">

                        <div class="col-sm-6" style="padding: 0;">

                            <span class="productName" style="height: 42px; display: table-cell; vertical-align: middle;"><?= $product_title ?></span>

                            <input type="text" class="form-control" id="copy_this_third" name="product_sku[]" value="<?= $sku ?>" placeholder="SKU produktu" style="display: none;">

                        </div>
                        <div class="col-sm-2">
                            <input type="text" class="form-control text-center quantity-control" name="product_quantity[]" value="<?= $bridge['quantity']; ?>" data-default="<?= $bridge['quantity']; ?>" placeholder="Množství" style="padding: 0; height: 42px;">
                        </div>

                        <div class="col-sm-3" style="padding: 0 0px 0 8px;">
                            <input type="text" class="form-control text-center price-control" name="product_price[]" value="<?= $bridge['price'] ?>" data-default="<?= $bridge['price'] ?>" placeholder="Cena" style="padding: 0; height: 42px;">
                            <input type="text" class="form-control text-center price-control" name="product_original_price[]" value="<?= $bridge['original_price'] ?>" data-default="<?= $bridge['original_price'] ?>" placeholder="Původní cena" style="padding: 0; height: 42px; display: none;">
                        </div>

                        <div class="col-sm-1" style="padding: 0 0px 0 11px;">
                            <button type="button" class="remove_specification btn btn-red" style="float:left; padding: 6px 10px; cursor: pointer;  height: 42px;"> <i class="entypo-trash"></i> </button>
                        </div>
                    </div>

                    <?php

                } ?>


            <span id="duplicate" style="display: none;"></span>

        </div>
    </div>


					<hr>

					<div class="form-group">
						<label for="field-2" class="col-sm-5 control-label">Montáž</label>

						<div class="col-sm-7">
							<input  type="text" name="price_montage" class="form-control price-control" data-default="<?php
                            if (isset($data_hottub['price_montage']) && $data_hottub['price_montage'] != "") {
                                echo round($data_hottub['price_montage'] * $data['exchange_rate']);
                            } else {
                                echo '5000';
                            }?>" value="<?php if (isset($data_hottub['price_montage']) && $data_hottub['price_montage'] != "") {echo $data_hottub['price_montage'];} else {echo '5000';}?>">
						</div>
					</div>
					<div class="form-group">
						<label for="field-2" class="col-sm-5 control-label">Doprava</label>

						<div class="col-sm-7">
							<input  type="text" name="price_delivery" class="form-control price-control" data-default="<?php if (isset($data_hottub['price_delivery']) && $data_hottub['price_delivery'] != "") {echo round($data_hottub['price_delivery'] * $data['exchange_rate']);} else {echo '2990';}?>" value="<?php if (isset($data_hottub['price_delivery']) && $data_hottub['price_delivery'] != "") {echo $data_hottub['price_delivery'];} else {echo '2990';}?>">

						</div>
					</div>

                            <hr>
                            <div class="form-group">
                            <label for="field-2" class="col-sm-5 control-label">Celková částka<br><small>bez DPH</small></label>
                                <h4><span id="finalprice">0</span> <span class="currency_ext"><?php if(!isset($data['currency']) || $data['currency'] == 'CZK'){ echo 'Kč'; }elseif($data['currency'] == 'EUR'){ echo '€'; }elseif($data['currency'] == 'USD'){ echo '$'; }?></span></h4>
                            </div>
</div>

		</div>

	</div>



				</div>

			</div>

			<?php    }


if (isset($getclient['customer']) && $getclient['customer'] == 4) {

    ?>


    <div class="row">

        <div class="col-md-12" style="padding: 0">

            <div class="col-md-8">



                <div class="panel panel-primary" data-collapsed="0">

                    <div class="panel-heading">
                        <div class="panel-title">
                            Produktové informace
                        </div>

                    </div>

                    <div class="panel-body">

                        <div class="form-group">
                            <label for="field-2" class="col-sm-3 control-label">Dodací doba</label>

                            <div class="col-sm-2">
                                <input  type="text" name="delivery_time" class="form-control" value="<?php if (isset($data_pergola['delivery_time']) && $data_pergola['delivery_time'] != "") {echo $data_pergola['delivery_time'];} else {echo '6 - 7 týdnů';}?>">
                            </div>

                            <label for="field-2" class="col-sm-2 control-label">Dodací adresa</label>

                            <div class="col-sm-4">
                                <input  type="text" name="delivery_address" class="form-control" value="<?php if (isset($data_pergola['delivery_address']) && $data_pergola['delivery_address'] != "") {echo $data_pergola['delivery_address'];} else {

                                    if (isset($address['shipping_street']) && $address['shipping_street'] != '') {

                                        echo $address['shipping_street'] . ', ' . $address['shipping_city'] . ', ' . $address['shipping_zipcode'];

                                    } else {

                                        echo $address['billing_street'] . ', ' . $address['billing_city'] . ', ' . $address['billing_zipcode'];

                                    }

                                }?>">
                            </div>
                        </div>

                        <hr>



                        <?php

                        if ($getclient['realization'] != "0000-00-00") {

                            $date_planned = $getclient['realization'];

                        } else {

                            if (isset($data_pergola['planned_date']) && $data_pergola['planned_date'] != "" && $data_pergola['planned_date'] != "0000-00-00") {

                                $date_planned = $data_pergola['planned_date'];

                            } else { $date_planned = date('Y-m-d');}

                        }

                        ?>


                        <div class="form-group">
                            <div class="col-sm-4">
                                <div class="well" style="padding: 6px 8px 10px 8px; width: 100%; margin: 10px 0 0; float: left;">

                                    <div class="radio" style="float: left; margin-left: 10px;">
                                        <label>
                                            <input type="radio" name="area" value="prague" <?php
                                            if ((isset($getclient['area']) && $getclient['area'] == 'prague') || (!empty($getclient['showroom']) && $getclient['showroom'] == 2 && $getclient['area'] == 'unknown')) {
                                                echo 'checked';
                                            } ?>>Praha
                                        </label>
                                    </div>
                                    <div class="radio" style="float: left;margin-left: 18px;">
                                        <label>
                                            <input type="radio" name="area" value="brno" <?php if((isset($getclient['area']) && $getclient['area'] == 'brno') || (!empty($getclient['showroom']) && $getclient['showroom'] == 3 && $getclient['area'] == 'unknown')) {
                                                echo 'checked';
                                            } ?>>Brno
                                        </label>
                                    </div>
                                </div>

                            </div>

                            <label for="field-2" class="col-sm-2 control-label">Plánovaná realizace</label>

                            <div class="col-sm-2">
                                <input id="datum5" type="text" class="form-control datepicker" name="planned_date_pergola" data-format="yyyy-mm-dd" placeholder="Datum provedení..." value="<?= $date_planned ?>" data-validate="required" data-message-required="Musíte zadat datum.">
                            </div>


                            <label for="field-2" class="col-sm-2 control-label">Deadline realizace</label>

                            <div class="col-sm-2">
                                <input id="datum5" type="text" class="form-control datepicker" name="deadline_date_pergola" data-format="yyyy-mm-dd" placeholder="Datum provedení..." value="<?php if (isset($data_pergola['deadline_date']) && $data_pergola['deadline_date'] != "" && $data_pergola['deadline_date'] != "0000-00-00") {echo $data_pergola['deadline_date'];}?>" data-validate="required" data-message-required="Musíte zadat datum.">
                            </div>

                        </div>

                        <div class="col-md-12" style="padding: 0;">

                            <?php

                            $adminsquery = $mysqli->query("SELECT id, user_name, role FROM demands WHERE role != 'client' AND active = 1 AND active = 1");

                            ?>
                            <div class="well admins_well" style="padding: 12px 0px 7px; width: 49.5%; margin-right: 0.5%;  margin-bottom: 0; float: left;">
                                <h4 style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">
                                    Proveditelé</h4>
                                <?php

                                if($getclient['customer'] == '0'){

                                    $type = 'realization_sauna';

                                }elseif($getclient['customer'] == '1'){

                                    $type = 'realization_hottub';

                                }elseif($getclient['customer'] == '4'){

                                    $type = 'realization_pergola';

                                }

                                $adminsquery = $mysqli->query("SELECT id, user_name FROM demands WHERE role != 'client' AND active = 1");
                                while ($admins = mysqli_fetch_array($adminsquery)) {

                                    $find_query = $mysqli->query("SELECT admin_id FROM mails_recievers WHERE type_id = '" . $getclient['id'] . "' AND admin_id = '" . $admins['id'] . "' AND type = '".$type."' AND reciever_type = 'performer'") or die($mysqli->error);

                                    ?><div class="col-sm-4" style="padding: 0 6px 0 12px;">

                                    <input id="real-admin-<?= $admins['id'] ?>-performer" name="performer[]" value="<?= $admins['id'] ?>" type="checkbox" <?php if (mysqli_num_rows($find_query) > 0) { echo 'checked'; } ?>>
                                    <label for="real-admin-<?= $admins['id'] ?>-performer" style="padding-left: 4px; cursor: pointer;"><?= $admins['user_name'] ?></label>
                                    </div>
                                <?php } ?>
                            </div>


                            <div class="well admins_well" style="padding: 12px 0px 7px; width: 49.5%; margin-left: 0.5%; margin-bottom: 0; float: left;">
                                <h4 style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">
                                    Informovaní</h4>
                                <?php



                                $adminsquery = $mysqli->query("SELECT id, user_name, role FROM demands WHERE role != 'client' AND active = 1");
                                while ($admins = mysqli_fetch_array($adminsquery)) {

                                    $find_query = $mysqli->query("SELECT admin_id FROM mails_recievers WHERE type_id = '" . $getclient['id'] . "' AND admin_id = '" . $admins['id'] . "' AND type = '".$type."' AND reciever_type = 'observer'") or die($mysqli->error);

                                    ?><div class="col-sm-4" style="padding: 0 6px 0 12px;">

                                    <input id="real-admin-<?= $admins['id'] ?>-observer" name="observer[]" value="<?= $admins['id'] ?>" type="checkbox" <?php if (mysqli_num_rows($find_query) > 0 || $admins['role'] == 'salesman-technician') { echo 'checked'; } ?>>
                                    <label for="real-admin-<?= $admins['id'] ?>-observer" style="padding-left: 4px; cursor: pointer; <?php if(!empty($client['id']) && $client['id'] == $admins['id']){ echo 'color: green !important;'; }?>"><?= $admins['user_name'] ?></label>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="panel panel-primary" data-collapsed="0">

                    <div class="panel-heading">
                        <div class="panel-title">
                            Změna specifikací u poptávky
                        </div>

                    </div>

                    <div class="panel-body">

                        <?php

                        if (isset($getclient['customer']) && $getclient['customer'] == 4) {

                        specs_pergola($getclient, '1');
                        specs_pergola($getclient, '2');

                        ?>

                    </div>
                </div>

            </div>

            <?php

            $get_provedeni = $mysqli->query("SELECT t.id FROM demands_specs_bridge d, warehouse_products_types t, warehouse_products p WHERE d.client_id = '" . $getclient['id'] . "' AND d.specs_id = 5 AND t.warehouse_product_id = p.id AND p.connect_name = '" . $getclient['product'] . "' AND t.name = d.value") or die($mysqli->error);
            $provedeni = mysqli_fetch_array($get_provedeni);


            //    old to delete
            //    $price_query = $mysqli->query("SELECT * FROM warehouse_versions WHERE product = '" . $getclient['product'] . "' AND version = '" . $provedeni['id'] . "'") or die($mysqli->error);
            //    $price = mysqli_fetch_array($price_query);
            //    old to delete


            //    if(isset($data['currency']) && $data['currency'] == 'CZK'){ $multiply = 1; }elseif(isset($data['currency']) && $data['currency'] == 'EUR'){ $multiply = $kurz['EUR']; }elseif(isset($data['currency']) && $data['currency'] == 'USD') { $multiply = $kurz['USD']; }
            }
            ?>

            <div class="col-md-4">

                <div class="panel panel-primary" data-collapsed="0">

                    <div class="panel-heading">
                        <div class="panel-title">
                            Informace ceně vybavení <small>(uvádějte ve zvolené měně)</small>
                        </div>

                    </div>

                    <div class="panel-body">
                        <div class="form-group">
                            <label for="field-2" class="col-sm-5 control-label">Cena pergoly</label>

                            <div class="col-sm-7">
                                <input  type="text" name="price_pergola" class="form-control price-control" data-default="<?php if (isset($data_pergola['price_pergola']) && $data_pergola['price_pergola'] != "") {echo round($data_pergola['price_pergola'] * $data['exchange_rate']);} else { echo '0';}?>" value="<?php if (isset($data_pergola['price_pergola']) && $data_pergola['price_pergola'] != "") {echo $data_pergola['price_pergola'];} else { echo '0'; }?>">
                            </div>
                        </div>


                        <div class="form-group">
                            <label for="field-2" class="col-sm-5 control-label">Sleva</label>

                            <div class="col-sm-7">
                                <input  type="text" name="discount" class="form-control discount" data-default='<?= $data_pergola['discount'] * $data['exchange_rate'] ?>' value="<?= $data_pergola['discount'] ?>">
                            </div>

                        </div>
                        <hr>

                        <div class="form-group">
                            <label for="field-2" class="col-sm-5 control-label"></label>
                            <div class="col-sm-5">
                            </div>
                            <div class="col-sm-2">
                                Generovat?
                            </div>
                        </div>

                        <?php

                        // selling price is taken from specs db table
                        $generate_specs_query = $mysqli->query("SELECT * FROM specs s LEFT JOIN demands_specs_bridge d ON d.specs_id = s.id AND d.client_id = '" . $getclient['id'] . "' WHERE s.generate = 1 ORDER BY s.demand_order ASC") or die($mysqli->error);
                        while ($generate_spec = mysqli_fetch_array($generate_specs_query)) {

                            ?>
                            <div class="price_<?= $generate_spec['seoslug'] ?> form-group" <?php if (isset($generate_spec['value']) && $generate_spec['value'] == 'Ne' || !isset($generate_spec['value']) || $generate_spec['value'] == "") { ?>style="display: none;"<?php }?>>
                                <label for="field-2" class="col-sm-5 control-label"><?= $generate_spec['name'] ?></label>

                                <div class="col-sm-5">
                                    <input type="text" name="price_<?= $generate_spec['seoslug'] ?>" class="form-control price-control" data-default="<?php if (isset($generate_spec['price']) && $generate_spec['price'] != "") {echo round($generate_spec['price'] * $data['exchange_rate']);} else {echo $generate_spec['generate_price'];}?>" value="<?php if (isset($generate_spec['price']) && $generate_spec['price'] != "") {echo $generate_spec['price'];} else {echo $generate_spec['generate_price'];}?>" <?php if (isset($generate_spec['value']) && $generate_spec['value'] == 'Ne' || !isset($generate_spec['value']) || $generate_spec['value'] == "") { ?>disabled<?php }?>>
                                </div>
                                <div class="col-sm-2">
                                    <input class="form-control" name="is_generated_<?= $generate_spec['seoslug'] ?>" value="1" type="checkbox" <?php if (isset($generate_spec['is_generated']) && $generate_spec['is_generated']) { echo 'checked'; } ?> style="width: auto;">
                                </div>
                            </div>
                        <?php } ?>

                        <hr>


                        <div class="form-group">

                            <div class="col-sm-12" style="float:left;">


                                <div id="specification_copy" class="specification" style="display: none; float:left; width: 100%; margin: 0px 0; padding: 10px 0; border-bottom: 1px solid #eeeeee;">

                                    <div class="col-sm-6" style="padding: 0;">

                                        <span class="productName" style="height: 42px; display: table-cell; vertical-align: middle;"></span>

                                        <input type="text" class="form-control" id="copy_this_third" name="copythis" value="" placeholder="SKU produktu" style="display: none;">

                                    </div>

                                    <div class="col-sm-2">
                                        <input type="text" class="form-control text-center quantity-control" id="copy_this_quantity" name="copy_this_quantity" value="1" placeholder="Množství" style="padding: 0; height: 42px;">
                                    </div>
                                    <div class="col-sm-3" style="padding: 0 0px 0 8px;">

                                        <input type="text" class="form-control text-center price-control" id="copy_this_price" name="copythis" value="" placeholder="Cena" style="padding: 0; height: 42px;">

                                        <input type="text" class="form-control text-center price-control" id="copy_this_original_price" name="copythis" value="" placeholder="Původní cena" style="padding: 0; height: 42px; display: none;">

                                    </div>

                                    <div class="col-sm-1" style="padding: 0 0px 0 11px;">
                                        <button type="button" class="remove_specification btn btn-red" style="float:left; padding: 6px 10px; cursor: pointer; height: 42px;"> <i class="entypo-trash"></i> </button>
                                    </div>
                                </div>


                                <?php

                                $products_bridge = $mysqli->query("SELECT * FROM demands_accessories_bridge WHERE aggregate_id = '".$getclient['id']."'")or die($mysqli->error);

                                while ($bridge = mysqli_fetch_assoc($products_bridge)) {

                                    if ($bridge['variation_id'] != 0) {

                                        $product_query = $mysqli->query("SELECT *, s.id as ajdee, s.price as price FROM products p, products_variations s WHERE p.id = '" . $bridge['product_id'] . "' AND p.id = s.product_id AND s.id = '" . $bridge['variation_id'] . "'");
                                        $product = mysqli_fetch_assoc($product_query);

                                        $select = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $product['ajdee'] . "'") or die($mysqli->error);
                                        $desc = "";
                                        while ($var = mysqli_fetch_assoc($select)) {

                                            $desc = $desc . $var['name'] . ': ' . $var['value'] . ' ';

                                        }

                                        $price = number_format($product['price'], 0, ',', ' ') . ' Kč';

                                        $product_title = $product['productname'] . ' – ' . $desc;

                                        $sku = $product['sku'];

                                    } else {

                                        $product_query = $mysqli->query("SELECT * FROM products p WHERE id = '" . $bridge['product_id'] . "'");

                                        $product = mysqli_fetch_assoc($product_query);

                                        $price = number_format($product['price'], 0, ',', ' ') . ' Kč';

                                        $product_title = $product['productname'];

                                        $sku = $product['code'];

                                    }

                                    ?>
                                    <div class="specification" style="float: left; width: 100%; margin: 0px 0; padding: 10px 0; border-bottom: 1px solid #eeeeee;">

                                        <div class="col-sm-6" style="padding: 0;">

                                            <span class="productName" style="height: 42px; display: table-cell; vertical-align: middle;"><?= $product_title ?></span>

                                            <input type="text" class="form-control" id="copy_this_third" name="product_sku[]" value="<?= $sku ?>" placeholder="SKU produktu" style="display: none;">

                                        </div>
                                        <div class="col-sm-2">
                                            <input type="text" class="form-control text-center quantity-control" name="product_quantity[]" value="<?= $bridge['quantity']; ?>" data-default="<?= $bridge['quantity']; ?>" placeholder="Množství" style="padding: 0; height: 42px;">
                                        </div>

                                        <div class="col-sm-3" style="padding: 0 0px 0 8px;">
                                            <input type="text" class="form-control text-center price-control" name="product_price[]" value="<?= $bridge['price'] ?>" data-default="<?= $bridge['price'] ?>" placeholder="Cena" style="padding: 0; height: 42px;">
                                            <input type="text" class="form-control text-center price-control" name="product_original_price[]" value="<?= $bridge['original_price'] ?>" data-default="<?= $bridge['original_price'] ?>" placeholder="Původní cena" style="padding: 0; height: 42px; display: none;">
                                        </div>

                                        <div class="col-sm-1" style="padding: 0 0px 0 11px;">
                                            <button type="button" class="remove_specification btn btn-red" style="float:left; padding: 6px 10px; cursor: pointer;  height: 42px;"> <i class="entypo-trash"></i> </button>
                                        </div>
                                    </div>

                                    <?php

                                } ?>


                                <span id="duplicate" style="display: none;"></span>

                            </div>
                        </div>


                        <hr>

                        <div class="form-group">
                            <label for="field-2" class="col-sm-5 control-label">Montáž</label>

                            <div class="col-sm-7">
                                <input  type="text" name="price_montage" class="form-control price-control" data-default="<?php
                                if (isset($data_pergola['price_montage']) && $data_pergola['price_montage'] != "") {
                                    echo round($data_pergola['price_montage'] * $data['exchange_rate']);
                                } else {
                                    echo '5000';
                                }?>" value="<?php if (isset($data_pergola['price_montage']) && $data_pergola['price_montage'] != "") {echo $data_pergola['price_montage'];} else {echo '5000';}?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="field-2" class="col-sm-5 control-label">Doprava</label>

                            <div class="col-sm-7">
                                <input  type="text" name="price_delivery_pergola" class="form-control price-control" data-default="<?php if (isset($data_pergola['price_delivery']) && $data_pergola['price_delivery'] != "") {echo round($data_pergola['price_delivery'] * $data['exchange_rate']);} else {echo '2990';}?>" value="<?php if (isset($data_pergola['price_delivery']) && $data_pergola['price_delivery'] != "") {echo $data_pergola['price_delivery'];} else {echo '2990';}?>">
                            </div>
                        </div>

                        <hr>
                        <div class="form-group">
                            <label for="field-2" class="col-sm-5 control-label">Celková částka<br><small>bez DPH</small></label>
                            <h4><span id="finalprice">0</span> <span class="currency_ext"><?php if(!isset($data['currency']) || $data['currency'] == 'CZK'){ echo 'Kč'; }elseif($data['currency'] == 'EUR'){ echo '€'; }elseif($data['currency'] == 'USD'){ echo '$'; }?></span></h4>
                        </div>
                    </div>

                </div>

            </div>



        </div>

    </div>

<?php    }



if (isset($getclient['customer']) && $getclient['customer'] == 0 || $getclient['customer'] == 3) {

    $data_sauna_query = $mysqli->query("SELECT * FROM demands_generate_sauna WHERE id = '$id'");
    $data_sauna = mysqli_fetch_array($data_sauna_query);

    ?>

<div class="row">

		<div class="col-md-12">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Údaje poptávky - <?php if (isset($getclient['customer']) && $getclient['customer'] == 0) {echo $getclient['brand'] . ' ' . ucfirst($getclient['fullname']);} else {

        $find_warehouse = $mysqli->query("SELECT * FROM warehouse_products WHERE connect_name = '" . $getclient['secondproduct'] . "'");
        $warehouse_second = mysqli_fetch_array($find_warehouse);

        echo ucfirst($warehouse_second['brand']) . ' ' . ucfirst($warehouse_second['fullname']);
    }?>
					</div>

				</div>


				<div class="panel-body" style="    background-color: #fbfbfb;">

	<div class="" <?php if (isset($getclient['customer']) && $getclient['customer'] == 3) { ?>style="width: 4%;height: 20%;margin-left: 200px;margin-left: 130px;/* float: right; */float: left;"<?php } else { ?>style="width: 14%; float:left;    height: 1px;"<?php } ?>>

			<?php if (isset($getclient['customer']) && $getclient['customer'] == 3) { ?>
				<img src="https://www.wellnesstrade.cz/admin/data/images/customer/<?= $getclient['secondproduct'] ?>.png" class="img-responsive img-circle" style="width:42px; float:right;    margin-right: -17px;" /><?php } ?>

			</div>

<div class="col-sm-10" style="margin-top: 1px;">

	z poptávky

			</div>
	</div>
		</div>
			</div>
				</div>



					<?php

//    $price_query = $mysqli->query("SELECT * FROM warehouse_versions WHERE product = '" . $getclient['product'] . "' AND version = '$provedeni'") or die($mysqli->error);
//    $price = mysqli_fetch_array($price_query);

    ?>


					<div class="row">

					<div class="col-md-12" style="padding: 0">

			<div class="col-md-8">



				<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Produktové informace
					</div>

				</div>

						<div class="panel-body">

									<div class="form-group">
											<label for="field-2" class="col-sm-3 control-label">Dodací doba</label>

											<div class="col-sm-2">
												<input  type="text" name="delivery_time_sauna" class="form-control" value="<?php if (isset($data_sauna['delivery_time']) && $data_sauna['delivery_time'] != "") {echo $data_sauna['delivery_time'];} else {echo '6 - 7 týdnů';}?>">
											</div>

											<label for="field-2" class="col-sm-2 control-label">Dodací adresa</label>

											<div class="col-sm-4">
												<input  type="text" name="delivery_address_sauna" class="form-control" value="<?php if (isset($data_sauna['delivery_address']) && $data_sauna['delivery_address'] != "") {echo $data_sauna['delivery_address'];} else {

        if (isset($address['shipping_street']) && $address['shipping_street'] != '') {

            echo $address['shipping_street'] . ', ' . $address['shipping_city'] . ', ' . $address['shipping_zipcode'];

        } else {

            echo $address['billing_street'] . ', ' . $address['billing_city'] . ', ' . $address['billing_zipcode'];

        }

    }?>">
											</div>
										</div>


										<hr>



				<?php

    if (isset($getclient['customer']) && $getclient['customer'] == 0) {

        if ($getclient['realization'] != "0000-00-00") {

            $date_planned = $getclient['realization'];

        } else {

            if (isset($data_sauna['planned_date']) && $data_sauna['planned_date'] != "" && $data_sauna['planned_date'] != "0000-00-00") {

                $date_planned = $data_sauna['planned_date'];

            } else { $date_planned = date('Y-m-d');}

        }

    } elseif (isset($getclient['customer']) && $getclient['customer'] == 3) {

        $gatedate = $mysqli->query("SELECT *, DATE_FORMAT(startdate, '%d. %m. %Y') as startformated, DATE_FORMAT(enddate, '%d. %m. %Y') as endformated FROM demands_double_realization WHERE demand_id = '" . $getclient['id'] . "'");
        $saunadate = mysqli_fetch_array($gatedate);

        if ($saunadate['startdate'] != "0000-00-00") {

            $date_planned = $saunadate['startdate'];

        } else {

            if (isset($data_sauna['planned_date']) && $data_sauna['planned_date'] != "" && $data_sauna['planned_date'] != "0000-00-00") {

                $date_planned = $data_sauna['planned_date'];

            } else { $date_planned = date('Y-m-d');}

        }

    }

    ?>

                    <div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Plánovaná realizace</label>

						<div class="col-sm-3">
							<input id="datum5" type="text" class="form-control datepicker" name="planned_date_sauna" data-format="yyyy-mm-dd" placeholder="Datum provedení..." value="<?= $date_planned ?>" data-validate="required" data-message-required="Musíte zadat datum.">
						</div>

						<label for="field-2" class="col-sm-3 control-label">Deadline realizace</label>

						<div class="col-sm-3">
							<input id="datum5" type="text" class="form-control datepicker" name="deadline_date_sauna" data-format="yyyy-mm-dd" placeholder="Datum provedení..." value="<?php if (isset($data_sauna['deadline_date']) && $data_sauna['deadline_date'] != "" && $data_sauna['deadline_date'] != "0000-00-00") {echo $data_sauna['deadline_date'];}?>" data-validate="required" data-message-required="Musíte zadat datum.">
						</div>
					</div>

									<hr>


                            <div class="col-md-12" style="padding: 0;">

                                <?php

                                $adminsquery = $mysqli->query("SELECT id, user_name, role FROM demands WHERE role != 'client' AND active = 1");

                                ?>
                                <div class="well admins_well" style="padding: 12px 0px 7px; width: 49.5%; margin-right: 0.5%;  margin-bottom: 0; float: left;">
                                    <h4 style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">
                                        Proveditelé</h4>
                                    <?php

                                    if($getclient['customer'] == '0'){

                                        $type = 'realization_sauna';

                                    }elseif($getclient['customer'] == '1'){

                                        $type = 'realization_hottub';

                                    }elseif($getclient['customer'] == '4'){

                                        $type = 'realization_pergola';

                                    }
                                    $adminsquery = $mysqli->query("SELECT id, user_name FROM demands WHERE role != 'client' AND active = 1");
                                    while ($admins = mysqli_fetch_array($adminsquery)) {

                                        $find_query = $mysqli->query("SELECT admin_id FROM mails_recievers WHERE type_id = '" . $getclient['id'] . "' AND admin_id = '" . $admins['id'] . "' AND type = '".$type."' AND reciever_type = 'performer'") or die($mysqli->error);

                                        ?><div class="col-sm-4" style="padding: 0 6px 0 12px;">

                                        <input id="real-admin-<?= $admins['id'] ?>-performer" name="performer[]" value="<?= $admins['id'] ?>" type="checkbox" <?php if (mysqli_num_rows($find_query) > 0) { echo 'checked'; } ?>>
                                        <label for="real-admin-<?= $admins['id'] ?>-performer" style="padding-left: 4px; cursor: pointer;"><?= $admins['user_name'] ?></label>
                                        </div>
                                    <?php } ?>
                                </div>


                                <div class="well admins_well" style="padding: 12px 0px 7px; width: 49.5%; margin-left: 0.5%; margin-bottom: 0; float: left;">
                                    <h4 style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">
                                        Informovaní</h4>
                                    <?php



                                    $adminsquery = $mysqli->query("SELECT id, user_name, role FROM demands WHERE role != 'client' AND active = 1");
                                    while ($admins = mysqli_fetch_array($adminsquery)) {

                                        $find_query = $mysqli->query("SELECT admin_id FROM mails_recievers WHERE type_id = '" . $getclient['id'] . "' AND admin_id = '" . $admins['id'] . "' AND type = '".$type."' AND reciever_type = 'observer'") or die($mysqli->error);

                                        ?><div class="col-sm-4" style="padding: 0 6px 0 12px;">

                                        <input id="real-admin-<?= $admins['id'] ?>-observer" name="observer[]" value="<?= $admins['id'] ?>" type="checkbox" <?php if (mysqli_num_rows($find_query) > 0 || $admins['role'] == 'salesman-technician') { echo 'checked'; } ?>>
                                        <label for="real-admin-<?= $admins['id'] ?>-observer" style="padding-left: 4px; cursor: pointer; <?php if(!empty($client['id']) && $client['id'] == $admins['id']){ echo 'color: green !important;'; }?>"><?= $admins['user_name'] ?></label>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
					</div>



							</div>

		<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Specifikace u sauny
					</div>

				</div>


						<div class="panel-body">

                            <div class="form-group">
                            <?php

                            specs_sauna($getclient, '1');
                            specs_sauna($getclient, '2');

                            ?>
                            </div>

                            <hr>

                            <div class="form-group">
                                <label for="type" class="col-sm-4 control-label"><strong>Typ sauny</strong></label>

                                <div class="col-sm-5">
                                    <input  type="text" id="type" name="type" class="form-control" value="<?php if (isset($data_sauna['type']) && $data_sauna['type'] != "") {echo $data_sauna['type'];}?>">
                                </div>

                            </div>

                            <hr>

									<div class="form-group">
												<label for="dimension" class="col-sm-6 control-label">Rozměr sauny (1500 x 1050 x 1700)</label>

											<div class="col-sm-4">
												<input  type="text" id="dimension" name="dimension" class="form-control" value="<?php if (isset($data_sauna['dimension']) && $data_sauna['dimension'] != "") {echo $data_sauna['dimension'];}?>">
											</div>
										</div>

									<div class="form-group">
											<label for="bottom_bench" class="col-sm-6 control-label">Lavice dolní (číslo bez cm)</label>

											<div class="col-sm-3">
												<input  type="text" id="bottom_bench" name="bottom_bench" class="form-control" value="<?php if (isset($data_sauna['bottom_bench']) && $data_sauna['bottom_bench'] != "") {echo $data_sauna['bottom_bench'];}?>">
											</div>



									</div>

									<div class="form-group">

											<label for="top_bench" class="col-sm-6 control-label">Lavice horní (číslo bez cm)</label>

											<div class="col-sm-3">
												<input  type="text" id="top_bench" name="top_bench" class="form-control" value="<?php if (isset($data_sauna['top_bench']) && $data_sauna['top_bench'] != "") {echo $data_sauna['top_bench'];}?>">
											</div>

									</div>

									<hr>


									<div class="form-group">
											<label for="stove" class="col-sm-4 control-label">Kamna</label>

											<div class="col-sm-5">
												<input  type="text" id="stove" name="stove" class="form-control" value="<?php if (isset($data_sauna['stove']) && $data_sauna['stove'] != "") {echo $data_sauna['stove'];}?>">
											</div>

									</div>

									<hr>

									<div class="form-group">

											<label class="col-sm-4 control-label">Vybavení sauny</label>

											    <div class="col-sm-8">
								                  <input id="rgb_sky" name="rgb_sky" value="1" type="checkbox" <?php if (isset($data_sauna['rgb_sky']) && $data_sauna['rgb_sky'] == 1) {echo 'checked';}?>>
								                  <label for="rgb_sky" style="padding-left: 4px; cursor: pointer;">RGB hvězdná obloha</label>
								               </div>

								                 <div class="col-sm-8 col-sm-offset-4">
								                  <input id="rgb_backrest" name="rgb_backrest" value="1" type="checkbox" <?php if (isset($data_sauna['rgb_backrest']) && $data_sauna['rgb_backrest'] == 1) {echo 'checked';}?>>
								                  <label for="rgb_backrest" style="padding-left: 4px; cursor: pointer;">RGB osvětlení za opěrkami</label>
								               </div>

								                 <div class="col-sm-8 col-sm-offset-4">
								                  <input id="light" name="light" value="1" type="checkbox" <?php if (isset($data_sauna['light']) && $data_sauna['light'] == 1) {echo 'checked';}?>>
								                  <label for="light" style="padding-left: 4px; cursor: pointer;">rohové světlo se stínítkem</label>
								               </div>

								               <div class="col-sm-8 col-sm-offset-4">
								                  <input id="controlpanel" name="controlpanel" value="1" type="checkbox" <?php if (isset($data_sauna['controlpanel']) && $data_sauna['controlpanel'] == 1) {echo 'checked';}?>>
								                  <label for="controlpanel" style="padding-left: 4px; cursor: pointer;">Řídící jednotka Espoo Touchscreen</label>
								               </div>

								               <div class="col-sm-8 col-sm-offset-4">
								                  <input id="remote" name="remote" value="1" type="checkbox" <?php if (isset($data_sauna['remote']) && $data_sauna['remote'] == 1) {echo 'checked';}?>>
								                  <label for="remote" style="padding-left: 4px; cursor: pointer;">Dálkové ovládání</label>
								               </div>

								               <div class="col-sm-8 col-sm-offset-4">
								                  <input id="audio" name="audio" value="1" type="checkbox" <?php if (isset($data_sauna['audio']) && $data_sauna['audio'] == 1) {echo 'checked';}?>>
								                  <label for="audio" style="padding-left: 4px; cursor: pointer;">Audio systém (FM, MP3)</label>
								               </div>

								               <div class="col-sm-8 col-sm-offset-4">
								                  <input id="loudspeaker" name="loudspeaker" value="1" type="checkbox" <?php if (isset($data_sauna['loudspeaker']) && $data_sauna['loudspeaker'] == 1) {echo 'checked';}?>>
								                  <label for="loudspeaker" style="padding-left: 4px; cursor: pointer;">2x voděodolný reproduktor</label>
								               </div>

								               <div class="col-sm-8 col-sm-offset-4">
								                  <input id="glass_wall" name="glass_wall" value="1" type="checkbox" <?php if (isset($data_sauna['glass_wall']) && $data_sauna['glass_wall'] == 1) {echo 'checked';}?>>
								                  <label for="glass_wall" style="padding-left: 4px; cursor: pointer;">Stěna z kaleného skla o síle 10mm – čiré sklo</label>
								               </div>

								               <div class="col-sm-8 col-sm-offset-4">
								                  <input id="glass_doors" name="glass_doors" value="1" type="checkbox" <?php if (isset($data_sauna['glass_doors']) && $data_sauna['glass_doors'] == 1) {echo 'checked';}?>>
								                  <label for="glass_doors" style="padding-left: 4px; cursor: pointer;">Dveře z kaleného skla o síle 8mm – čiré sklo</label>
								               </div>

									</div>

									<hr>


									<div class="form-group">
											<label for="accessories" class="col-sm-4 control-label">Doplňky zdarma</label>

											<div class="col-sm-8">
												<select class="form-control" id="accessories" name="accessories">
                                                    <option value="0" <?php if (isset($data_sauna['accessories']) && $data_sauna['accessories'] == '0') {echo 'selected';}?>>žádné</option>
													<option value="1" <?php if (isset($data_sauna['accessories']) && $data_sauna['accessories'] == '1') {echo 'selected';}?>>Vědro, naběračka, přesýpací hodiny a ručičkový teploměr</option>
													<option value="2" <?php if (isset($data_sauna['accessories']) && $data_sauna['accessories'] == '2') {echo 'selected';}?>>Vědro, naběračka, přesýpací hodiny a teploměr s vlhkoměrem</option>

												</select>
											</div>

									</div>

						</div>
			</div>

		</div>
		<div class="col-md-4">


			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Informace o produktu
					</div>

				</div>

						<div class="panel-body">
					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Cena sauny</label>

						<div class="col-sm-8">
							<input  type="text" name="price_sauna" class="form-control price-control" value="<?= $data_sauna['price_sauna'] ?>">
						</div>
					</div>

                            <div class="form-group">
                                <label for="field-2" class="col-sm-5 control-label">Sleva</label>

                                <div class="col-sm-7">
                                    <input  type="text" name="discount_sauna" class="form-control discount" data-default='<?= $data_sauna['discount'] * $data['exchange_rate'] ?>' value="<?= $data_sauna['discount'] ?>">
                                </div>

                            </div>

                            <hr>

                            <div class="form-group">
                                <label for="field-2" class="col-sm-5 control-label"></label>
                                <div class="col-sm-5">
                                </div>
                                <div class="col-sm-2">
                                    Generovat?
                                </div>
                            </div>

                            <?php

                            // selling price is taken from specs db table
                            $generate_specs_query = $mysqli->query("SELECT * FROM specs s LEFT JOIN demands_specs_bridge d ON d.specs_id = s.id AND d.client_id = '" . $getclient['id'] . "' WHERE s.generate = 1 ORDER BY s.demand_order ASC") or die($mysqli->error);
                            while ($generate_spec = mysqli_fetch_array($generate_specs_query)) {

                                ?>
                                <div class="price_<?= $generate_spec['seoslug'] ?> form-group" <?php if (isset($generate_spec['value']) && $generate_spec['value'] == 'Ne' || !isset($generate_spec['value']) || $generate_spec['value'] == "") { ?>style="display: none;"<?php }?>>
                                    <label for="field-2" class="col-sm-5 control-label"><?= $generate_spec['name'] ?></label>

                                    <div class="col-sm-5">
                                        <input type="text" name="price_<?= $generate_spec['seoslug'] ?>" class="form-control price-control" data-default="<?php if (isset($generate_spec['price']) && $generate_spec['price'] != "") {echo round($generate_spec['price'] * $data['exchange_rate']);} else {echo $generate_spec['generate_price'];}?>" value="<?php if (isset($generate_spec['price']) && $generate_spec['price'] != "") {echo $generate_spec['price'];} else {echo $generate_spec['generate_price'];}?>" <?php if (isset($generate_spec['value']) && $generate_spec['value'] == 'Ne' || !isset($generate_spec['value']) || $generate_spec['value'] == "") { ?>disabled<?php }?>>
                                    </div>
                                    <div class="col-sm-2">
                                        <input class="form-control" name="is_generated_<?= $generate_spec['seoslug'] ?>" value="1" type="checkbox" <?php if (isset($generate_spec['is_generated']) && $generate_spec['is_generated']) { echo 'checked'; } ?> style="width: auto;">
                                    </div>
                                </div>
                            <?php } ?>

					<hr>

					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Montáž</label>

						<div class="col-sm-8">
							<input  type="text" name="price_montage_sauna" class="form-control price-control" value="<?php if (isset($data_sauna['price_montage']) && $data_sauna['price_montage'] != "") {echo $data_sauna['price_montage'];} else {echo '5000';}?>">
						</div>
					</div>
					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Doprava</label>

						<div class="col-sm-8">
							<input  type="text" name="price_delivery_sauna" class="form-control price-control" value="<?php if (isset($data_sauna['price_delivery']) && $data_sauna['price_delivery'] != "") {echo $data_sauna['price_delivery'];} else {echo '0';}?>">
						</div>
					</div>

                            <hr>
                            <div class="form-group">
                                <label for="field-2" class="col-sm-5 control-label">Celková částka<br><small>bez DPH</small></label>
                                <h4><span id="finalprice">0</span> <span class="currency_ext"><?php if(!isset($data['currency']) || $data['currency'] == 'CZK'){ echo 'Kč'; }elseif($data['currency'] == 'EUR'){ echo '€'; }elseif($data['currency'] == 'USD'){ echo '$'; }?></span></h4>
                            </div>
</div>

		</div>

	</div>



				</div>

			</div>



			<?php } ?>




	<center>
	<div class="form-group default-padding">
		<a href="./zobrazit-poptavku?id=<?= $_REQUEST['id'] ?>" style="margin-bottom: 24px; margin-right: 16px; margin-top: 20px; display: inline-block;"><button type="button" class="btn btn-default btn-lg" style="font-size: 20px; padding: 20px 40px 20px 40px; ">Zpět</button></a>
		<span class="button-demo">

			<button type="submit" style="margin-bottom: 24px; margin-right: 26px; font-size: 20px; padding: 20px 50px 20px 140px; margin-top: 20px;" data-style="zoom-in" class="ladda-button btn btn-success btn-icon icon-left btn-lg">
			<i class="entypo-plus" style="line-height: 48px;font-size: 40px; padding: 10px 20px;"></i>
			<span class="ladda-label">Uložit údaje</span>
		</button>
		</span>
	</div></center>

</form>

<!-- Footer -->
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
<div class="modal fade" id="modal-1" aria-hidden="true" style="display: none; margin-top: 6%;"> <div class="modal-dialog"> <div class="modal-content"> <div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button> <h4 class="modal-title">Potvrzení vytvoření klienta</h4> </div> <div class="modal-body">
Opravdu si přeje spustit operaci vytvoření klienta z poptávky? Tímto krokem vygenerujete nového klienta z podkladů v poptávce, pošlete emailovi informační email s přihlašovacíma údajema (zatím nefunguje), vyskladníte produkt ze skladu (vířivka, sauna) a případně se vyskladní zboží přidělené k sauně jako specifikace. Poptávka se též přesune do "Hotových".
</div> <div class="modal-footer" style="text-align:left;"> <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button> <a href="https://www.wellnesstrade.cz/admin/generovat-klienta?id=<?= $getclient['id'] ?>" style="float:right;"><button type="button" class="btn btn-green">Vytvořit klienta</button></a> </div> </div> </div> </div>


    <?php include VIEW . '/default/footer.php'; ?>