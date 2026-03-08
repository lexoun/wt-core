<?php



include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";

include INCLUDES . "/googlelogin.php";
include INCLUDES . "/functions.php";

$categorytitle = "Poptávky";
$pagetitle = "Upravit poptávku";

$bread1 = "Editace poptávek";
$abread1 = "editace-poptavek";

$id = $_REQUEST['id'];

$getclientquery = $mysqli->query('SELECT * FROM demands WHERE id="' . $id . '"') or die($mysqli->error);

if (mysqli_num_rows($getclientquery) > 0) {

    $getclient = mysqli_fetch_assoc($getclientquery);

    $billing_query = $mysqli->query('SELECT * FROM addresses_billing WHERE id = "' . $getclient['billing_id'] . '"') or die($mysqli->error);
    $billing = mysqli_fetch_assoc($billing_query);

    $shipping_query = $mysqli->query('SELECT * FROM addresses_shipping WHERE id = "' . $getclient['shipping_id'] . '"') or die($mysqli->error);
    $shipping = mysqli_fetch_assoc($shipping_query);

    if ($billing['billing_company'] != '') {

        $bread2 = $billing['billing_company'];

    } else {

        $bread2 = $billing['billing_name'] . ' ' . $billing['billing_surname'];

    }

    $abread2 = "zobrazit-poptavku?id=" . $getclient['id'];

    if (isset($_REQUEST['action']) && $_REQUEST['action'] == "removefile") {

        $result = glob($_SERVER['DOCUMENT_ROOT'] . "/admin/data/files/demands/" . $getclient['secretstring'] . ".*");
        foreach ($result as $res) {

            unlink($res);

        }
        Header("Location:https://www.wellnesstrade.cz/admin/pages/demands/upravit-poptavku?id=" . $getclient['id']);
        exit;
    }

    if (isset($_REQUEST['action']) && $_REQUEST['action'] == "edit") {

        if (($_POST['billing_email'] != "" || $_POST['billing_phone'] != "") && $_POST['optionsRadios'] != "") {

            $billing_zipcode = preg_replace('/\s+/', '', $_POST['billing_zipcode']);
            $billing_phone = preg_replace('/\s+/', '', $_POST['billing_phone']);
            $billing_email = preg_replace('/\s+/', '', $_POST['billing_email']);

            $shipping_zipcode = preg_replace('/\s+/', '', $_POST['shipping_zipcode']);
            $shipping_phone = preg_replace('/\s+/', '', $_POST['shipping_phone']);
            $shipping_email = preg_replace('/\s+/', '', $_POST['shipping_email']);

            if ($_POST['shipping_name'] != '' || $_POST['shipping_surname'] != '') {

                $user_name = $_POST['shipping_name'] . ' ' . $_POST['shipping_surname'];

            } elseif ($_POST['billing_name'] != '' || $_POST['billing_surname'] != '') {

                $user_name = $_POST['billing_name'] . ' ' . $_POST['billing_surname'];

            } elseif ($_POST['billing_company'] != '') {

                $user_name = $_POST['billing_company'];

            } else {

                $user_name = $_POST['shipping_company'];

            }

            $getcustomer = $_POST['optionsRadios'];

            if ($getcustomer == "virivka") {

                $customer = "1";
                $product = $_POST['virivkatype'];
                $product2 = "";

            } elseif ($getcustomer == "sauna") {

                $customer = "O";
                $product = $_POST['saunatype'];
                $product2 = "";

            } elseif ($getcustomer == "both") {

                $customer = "3";
                $product = $_POST['virivkatype'];

                $product2 = $_POST['saunatype'];

            } elseif ($getcustomer == "pergola") {

                $customer = "4";
                $product = $_POST['pergolatype'];
                $product2 = "";

            }
			
			$shipping_degree = '';
			$billing_degree = '';


            if (isset($_POST['radio_shipping_degree'])) {

                $shipping_degree = $_POST['shipping_degree'];

            }

            if (isset($_POST['radio_billing_degree'])) {

                $billing_degree = $_POST['billing_degree'];

            }

			$billing_id = 0;
            if ($getclient['billing_id'] != '0') {

                $mysqli->query("UPDATE addresses_billing SET billing_company = '" . $_POST['billing_company'] . "', billing_degree = '" . $billing_degree . "', billing_name = '" . $_POST['billing_name'] . "', billing_surname = '" . $_POST['billing_surname'] . "', billing_street = '" . $_POST['billing_street'] . "', billing_city = '" . $_POST['billing_city'] . "', billing_zipcode = '" . $billing_zipcode . "', billing_country = '" . $_POST['billing_country'] . "',  billing_ico = '" . $_POST['billing_ico'] . "', billing_dic = '" . $_POST['billing_dic'] . "', billing_email = '" . $billing_email . "', billing_phone = '" . $billing_phone . "', billing_phone_prefix = '" . $_POST['billing_phone_prefix'] . "' WHERE id = '" . $getclient['billing_id'] . "'") or die($mysqli->error);

                $billing_id = $getclient['billing_id'];

            } else {

                $insert_billing = $mysqli->query("INSERT INTO addresses_billing (billing_company, billing_ico, billing_dic, billing_degree, billing_name, billing_surname, billing_street, billing_city, billing_zipcode, billing_country, billing_phone, billing_email, billing_phone_prefix) VALUES ('" . $_POST['billing_company'] . "', '" . $_POST['billing_ico'] . "', '" . $_POST['billing_dic'] . "', '" . $billing_degree . "', '" . $_POST['billing_name'] . "', '" . $_POST['billing_surname'] . "', '" . $_POST['billing_street'] . "', '" . $_POST['billing_city'] . "', '" . $billing_zipcode . "', '" . $_POST['billing_country'] . "', '" . $billing_phone . "', '" . $billing_email . "', '" . $_POST['billing_phone_prefix'] . "')") or die($mysqli->error);

                $billing_id = $mysqli->insert_id;

            }

			$shipping_id = 0;
            if ($_POST['shipping_company'] != '' || $_POST['shipping_name'] != '' || $_POST['shipping_surname'] != '' || $_POST['shipping_street'] != '' || $_POST['shipping_city'] != '' || $shipping_zipcode != '' || $_POST['shipping_ico'] != '' || $_POST['shipping_dic'] != '') {

                if (isset($getclient['shipping_id']) && $getclient['shipping_id'] != 0) {

                    $mysqli->query("UPDATE addresses_shipping SET shipping_company = '" . $_POST['shipping_company'] . "', shipping_degree = '" . $shipping_degree . "', shipping_name = '" . $_POST['shipping_name'] . "', shipping_surname = '" . $_POST['shipping_surname'] . "', shipping_street = '" . $_POST['shipping_street'] . "', shipping_city = '" . $_POST['shipping_city'] . "', shipping_zipcode = '" . $shipping_zipcode . "', shipping_country = '" . $_POST['shipping_country'] . "',  shipping_ico = '" . $_POST['shipping_ico'] . "', shipping_dic = '" . $_POST['shipping_dic'] . "', shipping_email = '" . $shipping_email . "', shipping_phone = '" . $shipping_phone . "', shipping_phone_prefix = '" . $_POST['shipping_phone_prefix'] . "' WHERE id = '" . $getclient['shipping_id'] . "'") or die($mysqli->error);

                    $shipping_id = $getclient['shipping_id'];

                } else {

                    $mysqli->query("INSERT INTO addresses_shipping (shipping_company, shipping_ico, shipping_dic, shipping_degree, shipping_name, shipping_surname, shipping_street, shipping_city, shipping_zipcode, shipping_country, shipping_phone, shipping_email, shipping_phone_prefix) VALUES ('" . $_POST['shipping_company'] . "', '" . $_POST['shipping_ico'] . "', '" . $_POST['shipping_dic'] . "', '" . $shipping_degree . "', '" . $_POST['shipping_name'] . "', '" . $_POST['shipping_surname'] . "', '" . $_POST['shipping_street'] . "', '" . $_POST['shipping_city'] . "', '" . $shipping_zipcode . "', '" . $_POST['shipping_country'] . "', '" . $_POST['shipping_phone'] . "', '" . $_POST['shipping_email'] . "', '" . $_POST['shipping_phone_prefix'] . "')") or die($mysqli->error);

                    $shipping_id = $mysqli->insert_id;

                }

            }

            $update = $mysqli->query("UPDATE demands 
                SET user_name = '" . $user_name . "', 
                    billing_id = '" . $billing_id . "', 
                    shipping_id = '" . $shipping_id . "', 
                    showroom = '" . $_POST['showroom'] . "', 
                    admin_id = '" . $_POST['admin_id'] . "', 
                    description = '" . $mysqli->real_escape_string($_POST['description']) . "', 
                    email = '" . $billing_email . "', 
                    customer = '$customer', 
                    product = '$product', 
                    secondproduct = '$product2', 
                    phone = '$billing_phone', 
                    phone_prefix = '".$_POST['billing_phone_prefix']."', 
                    distance = '" . $_POST['distance'] . "', 
                    rating = '" . $_POST['rating'] . "' 
                WHERE id = '$id'") or die($mysqli->error);

            $id = $_REQUEST['id'];

            if ($getcustomer == "virivka" || $getcustomer == "both") {

/*

$specsquery = $mysqli->query('SELECT * FROM specs WHERE product = 1')or die($mysqli->error);
while($specs = mysqli_fetch_array($specsquery)){

$specsslug = $specs['seoslug'];
$insrt = $_POST[$specsslug];
$spcid = $specs['id'];

$find_query = $mysqli->query("SELECT id FROM demands_specs_bridge WHERE client_id = '".$_REQUEST['id']."' AND specs_id = '$spcid'")or die($mysqli->error);
if(mysqli_num_rows($find_query) > 0){

$find = mysqli_fetch_array($find_query);
$insert_specs = $mysqli->query("UPDATE demands_specs_bridge SET value = '$insrt' WHERE id = '".$find['id']."'")or die($mysqli->error);

}else{

$insert_specs = $mysqli->query("INSERT INTO demands_specs_bridge (value, client_id, specs_id) VALUES ('$insrt','".$_REQUEST['id']."','$spcid')")or die($mysqli->error);

}

}
 */

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

                    $find_query = $mysqli->query("SELECT id FROM demands_specs_bridge WHERE client_id = '" . $_REQUEST['id'] . "' AND specs_id = '" . $specs['id'] . "'") or die($mysqli->error);
                    if (mysqli_num_rows($find_query) > 0) {

                        $find = mysqli_fetch_array($find_query);
                        $insert_specs = $mysqli->query("UPDATE demands_specs_bridge SET value = '$spec_value' WHERE id = '" . $find['id'] . "'") or die($mysqli->error);

                    } else {

                        $insert_specs = $mysqli->query("INSERT INTO demands_specs_bridge (value, client_id, specs_id) VALUES ('$spec_value','" . $_REQUEST['id'] . "','" . $specs['id'] . "')") or die($mysqli->error);

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

            }


            // todo unify for pergola, sauna and hottub

            if ($getcustomer == "pergola") {

                /*

                $specsquery = $mysqli->query('SELECT * FROM specs WHERE product = 1')or die($mysqli->error);
                while($specs = mysqli_fetch_array($specsquery)){

                $specsslug = $specs['seoslug'];
                $insrt = $_POST[$specsslug];
                $spcid = $specs['id'];

                $find_query = $mysqli->query("SELECT id FROM demands_specs_bridge WHERE client_id = '".$_REQUEST['id']."' AND specs_id = '$spcid'")or die($mysqli->error);
                if(mysqli_num_rows($find_query) > 0){

                $find = mysqli_fetch_array($find_query);
                $insert_specs = $mysqli->query("UPDATE demands_specs_bridge SET value = '$insrt' WHERE id = '".$find['id']."'")or die($mysqli->error);

                }else{

                $insert_specs = $mysqli->query("INSERT INTO demands_specs_bridge (value, client_id, specs_id) VALUES ('$insrt','".$_REQUEST['id']."','$spcid')")or die($mysqli->error);

                }

                }
                 */

                $choosed_pergola = $_POST['pergolatype'];

                $choosed_type = $_POST['provedeni_' . $choosed_pergola];

                $get_ids = $mysqli->query("SELECT w.id as id, w.name as name FROM warehouse_products_types w, warehouse_products p WHERE w.warehouse_product_id = p.id AND p.connect_name = '$choosed_pergola' AND w.seo_url = '$choosed_type'") or die($mysqli->error);
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

                    $spec_value = $_POST[$choosed_pergola . '_' . $choosed_type . '_' . $seoslug];

                    $find_query = $mysqli->query("SELECT id FROM demands_specs_bridge WHERE client_id = '" . $_REQUEST['id'] . "' AND specs_id = '" . $specs['id'] . "'") or die($mysqli->error);
                    if (mysqli_num_rows($find_query) > 0) {

                        $find = mysqli_fetch_array($find_query);
                        $insert_specs = $mysqli->query("UPDATE demands_specs_bridge SET value = '$spec_value' WHERE id = '" . $find['id'] . "'") or die($mysqli->error);

                    } else {

                        $insert_specs = $mysqli->query("INSERT INTO demands_specs_bridge (value, client_id, specs_id) VALUES ('$spec_value','" . $_REQUEST['id'] . "','" . $specs['id'] . "')") or die($mysqli->error);

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

            }



            if ($getcustomer == "sauna") {

                /*

                $specsquery = $mysqli->query('SELECT * FROM specs WHERE product = 1')or die($mysqli->error);
                while($specs = mysqli_fetch_array($specsquery)){

                $specsslug = $specs['seoslug'];
                $insrt = $_POST[$specsslug];
                $spcid = $specs['id'];

                $find_query = $mysqli->query("SELECT id FROM demands_specs_bridge WHERE client_id = '".$_REQUEST['id']."' AND specs_id = '$spcid'")or die($mysqli->error);
                if(mysqli_num_rows($find_query) > 0){

                $find = mysqli_fetch_array($find_query);
                $insert_specs = $mysqli->query("UPDATE demands_specs_bridge SET value = '$insrt' WHERE id = '".$find['id']."'")or die($mysqli->error);

                }else{

                $insert_specs = $mysqli->query("INSERT INTO demands_specs_bridge (value, client_id, specs_id) VALUES ('$insrt','".$_REQUEST['id']."','$spcid')")or die($mysqli->error);

                }

                }
                 */

                $choosed_sauna = $_POST['saunatype'];

                $choosed_type = $_POST['provedeni_' . $choosed_sauna];

                $get_ids = $mysqli->query("SELECT w.id as id, w.name as name FROM warehouse_products_types w, warehouse_products p WHERE w.warehouse_product_id = p.id AND p.connect_name = '$choosed_sauna' AND w.seo_url = '$choosed_type'") or die($mysqli->error);
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

                    $spec_value = $_POST[$choosed_sauna . '_' . $choosed_type . '_' . $seoslug];

                    $find_query = $mysqli->query("SELECT id FROM demands_specs_bridge WHERE client_id = '" . $_REQUEST['id'] . "' AND specs_id = '" . $specs['id'] . "'") or die($mysqli->error);
                    if (mysqli_num_rows($find_query) > 0) {

                        $find = mysqli_fetch_array($find_query);
                        $insert_specs = $mysqli->query("UPDATE demands_specs_bridge SET value = '$spec_value' WHERE id = '" . $find['id'] . "'") or die($mysqli->error);

                    } else {

                        $insert_specs = $mysqli->query("INSERT INTO demands_specs_bridge (value, client_id, specs_id) VALUES ('$spec_value','" . $_REQUEST['id'] . "','" . $specs['id'] . "')") or die($mysqli->error);

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

            }



            $remove = $mysqli->query("DELETE FROM demands_contacts WHERE demand_id = '$id'") or die($mysqli->error);

            $post = array_filter($_POST['contact_name']);

            foreach ($post as $post_index => $posterino) {

                $contact_name = $posterino;
                $contact_role = $_POST['contact_role'][$post_index];
                $contact_phone = $_POST['contact_phone'][$post_index];
                $contact_email = $_POST['contact_email'][$post_index];

                $insert = $mysqli->query("INSERT INTO demands_contacts (demand_id, name, role, phone, email) VALUES ('$id', '$contact_name', '$contact_role', '$contact_phone', '$contact_email')") or die($mysqli->error);

            }

            saveCalendarEvent($id, 'realization');

            Header("Location:https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=" . $getclient['id'] . "&success=edit");
            exit;
        } else {

            $displayerror = true;
            $errorhlaska = "Klient NEBYL úspěšně přidán.";

        }}

    $saunyquery = $mysqli->query("SELECT * FROM warehouse_products WHERE customer = 0 ORDER BY code asc, fullname asc");

    $virivkyquery = $mysqli->query("SELECT * FROM warehouse_products WHERE customer = 1 ORDER BY brand");

    $pergolyAll = $mysqli->query("SELECT * FROM warehouse_products WHERE customer = 4 ORDER BY code asc, fullname asc");



    include VIEW . '/default/header.php';


    ?>

<script type="text/javascript">

function randomPassword(length) {
    var chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOP1234567890";
    var pass = "";
    for (var x = 0; x < length; x++) {
        var i = Math.floor(Math.random() * chars.length);
        pass += chars.charAt(i);
    }
    return pass;
}

function generate() {
    myform.secretstring.value = randomPassword(myform.length.value);
}

jQuery(document).ready(function($)
{

$('.radio').click(function() {

   if($("input:radio[class='saunaradio']").is(":checked")) {
        $('.tajtl').hide( "slow");
        $('.virivkens').hide( "slow");
       $('.pergoly').hide( "slow");
       $('.saunkens').show( "slow");
   }

    if($("input:radio[class='virivkaradio']").is(":checked")) {
        $('.tajtl').hide( "slow");
        $('.saunkens').hide( "slow");
        $('.pergoly').hide( "slow");
        $('.virivkens').show( "slow");

    }

    if($("input:radio[class='bothradio']").is(":checked")) {
        $('.pergoly').hide( "slow");
        $('.tajtl').show( "slow");
        $('.saunkens').show( "slow");
        $('.virivkens').show( "slow");
   }

    if($("input:radio[class='pergolaradio']").is(":checked")) {
        $('.tajtl').hide( "slow");
        $('.saunkens').hide( "slow");
        $('.virivkens').hide( "slow");
        $('.pergoly').show( "slow");
    }

});


 $('.radio_billing_degree_switch').on('switch-change', function () {

 if($('.radio_billing_degree').prop('checked')){

 	$('.billing_degree').show("slow");
 	$('.billing_degree').focus();

   }else if(!$('.radio_billing_degree').prop('checked')){


 	$('.billing_degree').hide("slow");
 }

});


 $('.radio_shipping_degree_switch').on('switch-change', function () {

 if($('.radio_shipping_degree').prop('checked')){

 	$('.shipping_degree').show("slow");
 	$('.shipping_degree').focus();

   }else if(!$('.radio_shipping_degree').prop('checked')){


 	$('.shipping_degree').hide("slow");
 }

});

});


</script>

        <script type="text/javascript">

            toastr.options.positionClass = 'toast-top-full-width';
            toastr.options.timeOut = 7000;
            toastr.options.extendedTimeOut = 1000;
            toastr.options.closeButton = true;
            toastr.options.showEasing = 'swing';
            toastr.options.hideEasing = 'linear';
            toastr.options.showMethod = 'fadeIn';
            toastr.options.hideMethod = 'fadeOut';
            toastr.options.progressBar = true;

            $(document).on('submit', '#demand_form', function(event) {

                if($("input[name='billing_email']").val() == '' && $("input[name='billing_phone']").val() == ''){

                    $("input[name='billing_email'], input[name='billing_phone']").closest('.form-group').removeClass('has-success').addClass('has-error');

                    toastr.error('Musí být zadáno telefonní číslo nebo e-mail.');
                    event.preventDefault();


                }else{

                    $("input[name='billing_email'], input[name='billing_phone']").closest('.form-group').removeClass('has-error').addClass('has-success');

                }

                var hasContent = false;
                $(".shipping-required").each(function() {

                    if ($(this).val() != '') {
                        hasContent = true;
                    }

                });

                var isEmpty = false;
                if(hasContent){

                    $(".shipping-required").each(function() {

                        if ($(this).val() == '') {
                            isEmpty = true;

                            $(this).closest('.form-group').removeClass('has-success').addClass('has-error');

                        }else{

                            $(this).closest('.form-group').removeClass('has-error').addClass('has-success');


                        }

                    })

                }

                if(isEmpty){
                    toastr.error('Chybí některé z položek doručovací adresy.');
                    event.preventDefault();
                }


            });
        </script>



<form role="form" method="post" name="myform" id="demand_form" class="form-horizontal form-groups-bordered validate" enctype="multipart/form-data" action="upravit-poptavku?action=edit&id=<?= $getclient['id'] ?>">
<input type="hidden" name="length" value="14">

	<div class="row">

		<div class="col-md-12">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Upravit poptávku
					</div>

				</div>

						<div class="panel-body">


						<div class="form-group">
							<div class="col-sm-6">
								<textarea class="form-control autogrow" id="field-ta" name="description" placeholder="Informace prodejce" style="padding: 20px 18px;"><?= $getclient['description'] ?></textarea>
							</div>
							<label for="field-1" class="col-sm-2 control-label">Rating zákazníka</label>

							<div class="col-sm-4">
								<div style="margin: 6px 0 4px;">
									<input id="rating_0" name="rating" value="0" type="radio" <?php if ($getclient['rating'] == 0) {echo 'checked';}?> style="cursor: pointer;"/>
									<label for="rating_0" style="padding-left: 6px; cursor: pointer;">-</label>
								</div>

								<div style="margin-bottom: 2px;">
									<input id="rating_1" name="rating" value="1" type="radio" <?php if ($getclient['rating'] == 1) {echo 'checked';}?> style="cursor: pointer;"/>
									<label for="rating_1" style="padding-left: 6px; cursor: pointer;">
										<img src="/admin/assets/images/star_2.png" style="padding-bottom: 4px;">
									</label>
								</div>

								<div style="margin-bottom: 2px;">
									<input id="rating_2" name="rating" value="2" type="radio" <?php if ($getclient['rating'] == 2) {echo 'checked';}?> style="cursor: pointer;"/>
									<label for="rating_2" style="padding-left: 6px; cursor: pointer;">
										<img src="/admin/assets/images/star_2.png" style="padding-bottom: 4px;">
										<img src="/admin/assets/images/star_2.png" style="padding-bottom: 4px;">
									</label>
								</div>

								<div style="margin-bottom: 2px;">
									<input id="rating_3" name="rating" value="3" type="radio" <?php if ($getclient['rating'] == 3) {echo 'checked';}?> style="cursor: pointer;"/>
									<label for="rating_3" style="padding-left: 6px; cursor: pointer;">
										<img src="/admin/assets/images/star_2.png" style="padding-bottom: 4px;">
										<img src="/admin/assets/images/star_2.png" style="padding-bottom: 4px;">
										<img src="/admin/assets/images/star_2.png" style="padding-bottom: 4px;">
									</label>
								</div>

								<div style="margin-bottom: 2px;">
									<input id="rating_4" name="rating" value="4" type="radio" <?php if ($getclient['rating'] == 4) {echo 'checked';}?> style="cursor: pointer;"/>
									<label for="rating_4" style="padding-left: 6px; cursor: pointer;">
										<img src="/admin/assets/images/star_2.png" style="padding-bottom: 4px;">
										<img src="/admin/assets/images/star_2.png" style="padding-bottom: 4px;">
										<img src="/admin/assets/images/star_2.png" style="padding-bottom: 4px;">
										<img src="/admin/assets/images/star_2.png" style="padding-bottom: 4px;">
									</label>
								</div>

								<div style="margin-bottom: 2px;">
									<input id="rating_5" name="rating" value="5" type="radio" <?php if ($getclient['rating'] == 5) {echo 'checked';}?> style="cursor: pointer;"/>
									<label for="rating_5" style="padding-left: 6px; cursor: pointer;">
										<img src="/admin/assets/images/star_2.png" style="padding-bottom: 4px;">
										<img src="/admin/assets/images/star_2.png" style="padding-bottom: 4px;">
										<img src="/admin/assets/images/star_2.png" style="padding-bottom: 4px;">
										<img src="/admin/assets/images/star_2.png" style="padding-bottom: 4px;">
										<img src="/admin/assets/images/star_2.png" style="padding-bottom: 4px;">
									</label>
								</div>

							</div>
						</div>

						<hr>

								<div class="col-md-6" style="padding-left: 0;">

							<div class="panel panel-primary" data-collapsed="0">

								<div class="panel-heading">
									<div class="panel-title">
										Fakturační údaje
									</div>

								</div>

										<div class="panel-body">


                                            <div class="form-group">
                                                <label for="field-2" class="col-sm-3 control-label">E-mail</label>

                                                <div class="col-sm-6">
                                                    <input type="text" name="billing_email" class="form-control" id="field-2" value="<?= $billing['billing_email'] ?>">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="field-2" class="col-sm-3 control-label">Telefon</label>

                                                <div class="col-sm-6">
                                                    <select type="select" name="billing_phone_prefix" class="form-control" style="width: 30%; float: left;">
                                                        <?php
                                                        foreach($phone_prefixes as $prefix){ ?>
                                                            <option value="<?= $prefix['id'] ?>" <?php if($billing['billing_phone_prefix'] == $prefix['id']){ echo 'selected'; }?>><?= $prefix['name'] ?></option>
                                                        <?php } ?>
                                                    </select>
                                                    <input type="text" name="billing_phone" class="form-control" id="field-2" value="<?= $billing['billing_phone'] ?>"  style="width: 70%; float: left;">
                                                </div>
                                            </div>


                                            <hr>
                <div class="form-group">
                    <label for="billing_ico" class="col-sm-2 control-label">IČO</label>

                    <div class="col-sm-4" style="padding: 0;">
                        <input type="text" name="billing_ico" class="form-control ico" id="billing_ico" value="<?php if ($billing['billing_ico'] != 0) {echo $billing['billing_ico'];}?>" style="float: left; width: 75%;">
                        <a class="ares-load btn-md btn btn-primary" style="float: right; width: 20%; padding: 6px;"><i class="entypo-download"></i></a>
                    </div>


                    <label class="col-sm-1 control-label">DIČ</label>
                    <div class="col-sm-3">
                        <input type="text" name="billing_dic" class="form-control" id="field-2" value="<?= $billing['billing_dic'] ?>">
                    </div>
                </div>

			<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Firma</label>

						<div class="col-sm-8">
							<input type="text" name="billing_company" class="form-control" value="<?= $billing['billing_company'] ?>">
						</div>
					</div>


					<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Titul</label>

						<div class="col-sm-5">
							<div class="radio_billing_degree_switch make-switch switch-small" style="float: left; margin-right:20px; margin-top: 3px;" data-on-label="<i class='entypo-check'></i>" data-off-label="<i class='entypo-cancel'></i>">
								<input class="radio_billing_degree" name="radio_billing_degree" value="nah" type="checkbox" <?php if ($billing['billing_degree'] != "") {echo 'checked';}?>/>
							</div>
							<input class="billing_degree form-control" type="text" name="billing_degree" style="<?php if (isset($billing['billing_degree']) && $billing['billing_degree'] == "") { ?>display: none;<?php } ?> width: 33%; float:left;" value="<?= $billing['billing_degree'] ?>">
						</div>
					</div>

					<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Jméno</label>

						<div class="col-sm-6">
							<input type="text" name="billing_name" class="form-control" id="field-1" value="<?= $billing['billing_name'] ?>">
						</div>
					</div>
					<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Příjmení</label>

						<div class="col-sm-6">
							<input type="text" name="billing_surname" class="form-control" id="field-1" value="<?= $billing['billing_surname'] ?>">
						</div>
					</div>

					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Ulice</label>

						<div class="col-sm-6">
							<input type="text" name="billing_street" class="form-control" id="field-2" value="<?= $billing['billing_street'] ?>">
						</div>
					</div>
					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Město</label>

						<div class="col-sm-6">
							<input type="text" name="billing_city" class="form-control" id="field-2" value="<?= $billing['billing_city'] ?>">
						</div>
					</div>
						<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">PSČ</label>

						<div class="col-sm-2">
							<input type="text" name="billing_zipcode" class="form-control" id="field-2" value="<?php if ($billing['billing_zipcode'] != 0) {echo $billing['billing_zipcode'];}?>">
						</div>
						 <label class="col-sm-1 control-label">Země</label>
						  <div class="col-sm-4"> <select id="optionus" name="billing_country" class="form-control"> <option value="czech" <?php if (isset($billing['billing_country']) && $billing['billing_country'] == 'czech') {echo 'selected';}?>>Česká republika</option> <option value="slovakia" <?php if (isset($billing['billing_country']) && $billing['billing_country'] == 'slovakia') {echo 'selected';}?>>Slovensko</option> <option value="austria" <?php if (isset($billing['billing_country']) && $billing['billing_country'] == 'austria') {echo 'selected';}?>>Rakousko</option> <option value="germany" <?php if (isset($billing['billing_country']) && $billing['billing_country'] == 'germany') {echo 'selected';}?>>Německo</option></select> </div>
					</div>

				<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Vzdálenost</label>

						<div class="col-sm-6">
							<input type="text" name="distance" style="width: 180px; float: left;" class="form-control" id="field-2" value="<?= $getclient['distance'] ?>">&nbsp;
						</div>


					</div>

<div class="form-group" style="display: none;">
						<label for="field-2" class="col-sm-3 control-label">Heslo</label>

						<div class="col-sm-6">
							<input type="text" style="width: 180px; float: left;" class="form-control" id="field-2" value="<?= $getclient['secretstring'] ?>" disabled>&nbsp;
						</div>


					</div>

				</div>

				</div>
				</div>

					<div class="col-md-6">

							<div class="panel panel-primary" data-collapsed="0">

								<div class="panel-heading">
									<div class="panel-title">
										Jiné doručovací údaje
									</div>

								</div>

										<div class="panel-body">



                                            <div class="form-group">
                                                <label for="field-2" class="col-sm-3 control-label">E-mail</label>

                                                <div class="col-sm-6">
                                                    <input type="text" name="shipping_email" class="form-control" id="field-2" value="<?= !empty($shipping['shipping_email']) ? $shipping['shipping_email'] : '' ?>">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="field-2" class="col-sm-3 control-label">Telefon</label>

                                                <div class="col-sm-6">
                                                        <select type="select" name="shipping_phone_prefix" class="form-control" style="width: 30%; float: left;">
                                                            <?php
                                                            foreach($phone_prefixes as $prefix){ ?>
                                                                <option value="<?= $prefix['id'] ?>" <?php if($shipping['shipping_phone_prefix'] == $prefix['id']){ echo 'selected'; }?>><?= $prefix['name'] ?></option>
                                                            <?php } ?>
                                                        </select>
                                                        <input type="text" name="shipping_phone" class="form-control" id="field-2" value="<?= !empty($shipping['shipping_phone']) ? $shipping['shipping_phone'] : '' ?>" style="width: 70%; float: left;">
                                                </div>
                                            </div>


                                            <hr>

                                            <div class="form-group">
                        <label for="field-2" class="col-sm-3 control-label">IČO</label>

                        <div class="col-sm-3" style="padding: 0;">
                            <input type="text" name="shipping_ico" class="form-control ico" id="field-2" value="<?php echo !empty($shipping['shipping_ico']) ? $shipping['shipping_ico'] : '' ?>">
                        </div>



                        <label class="col-sm-2 control-label">DIČ</label>
                        <div class="col-sm-3">
                            <input type="text" name="shipping_dic" class="form-control" id="field-2" value="<?= !empty($shipping['shipping_dic']) ? $shipping['shipping_dic'] : '' ?>">
                        </div>
                    </div>

					<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Firma</label>

						<div class="col-sm-8">
							<input type="text" name="shipping_company" class="form-control" value="<?php echo !empty($shipping['shipping_company']) ? $shipping['shipping_company'] : '' ?>">
						</div>
					</div>


					<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Titul</label>

						<div class="col-sm-5">
							<div class="radio_shipping_degree_switch make-switch switch-small" style="float: left; margin-right:20px; margin-top: 3px;" data-on-label="<i class='entypo-check'></i>" data-off-label="<i class='entypo-cancel'></i>">
								<input class="radio_shipping_degree" name="radio_shipping_degree" value="nah" type="checkbox" <?php if (!empty($shipping['shipping_degree'])) {echo 'checked';}?>/>
							</div>
							<input class="shipping_degree form-control" type="text" name="shipping_degree" style="<?php if (isset($shipping['shipping_degree']) && $shipping['shipping_degree'] == "") { ?>display: none;<?php } ?> width: 33%; float:left;" value="<?= !empty($shipping['shipping_degree']) ? $shipping['shipping_degree'] : '' ?>">
						</div>
					</div>

					<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Jméno</label>

						<div class="col-sm-8">
							<input type="text" name="shipping_name" class="form-control" value="<?= !empty($shipping['shipping_name']) ? $shipping['shipping_name'] : '' ?>">
						</div>
					</div>
					<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Příjmení</label>

						<div class="col-sm-8">
							<input type="text" name="shipping_surname" class="form-control" value="<?= !empty($shipping['shipping_surname']) ? $shipping['shipping_surname'] : '' ?>">
						</div>
					</div>


					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Ulice *</label>

						<div class="col-sm-8">
							<input  type="text" name="shipping_street" class="form-control shipping-required" value="<?= !empty($shipping['shipping_street']) ? $shipping['shipping_street'] : '' ?>">
						</div>
					</div>
					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Město *</label>

						<div class="col-sm-8">
							<input  type="text" name="shipping_city" class="form-control shipping-required" value="<?= !empty($shipping['shipping_city']) ? $shipping['shipping_city'] : '' ?>">
						</div>
					</div>
					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">PSČ *</label>

						<div class="col-sm-3">
							<input type="text" name="shipping_zipcode" class="form-control shipping-required" id="field-2" value="<?php if (!empty($shipping['shipping_zipcode'])) {echo $shipping['shipping_zipcode'];}?>">
						</div>
						 <label class="col-sm-1 control-label">Země</label>
						  <div class="col-sm-4"> <select id="optionus" name="shipping_country" class="form-control">
						  	<option value="czech" <?php if (isset($shipping['shipping_country']) && $shipping['shipping_country'] == 'czech') {echo 'selected';}?>>Česká republika</option>
						  	<option value="slovakia" <?php if (isset($shipping['shipping_country']) && $shipping['shipping_country'] == 'slovakia') {echo 'selected';}?>>Slovensko</option>
						  	<option value="austria" <?php if (isset($shipping['shipping_country']) && $shipping['shipping_country'] == 'austria') {echo 'selected';}?>>Rakousko</option></select>  </div>
					</div>






											</div>
						</div>
					</div>

<div style="clear:both;"></div>

					<hr>
						<div class="form-group">
						<label class="col-sm-3 control-label">Druh</label>
						<div class="col-sm-8">
							<div class="radio" style="width: 100px; float: left;">
								<label>
									<input type="radio" name="optionsRadios" value="virivka" class="virivkaradio" <?php if (isset($getclient['customer']) && $getclient['customer'] == "1") {echo 'checked';}?>>Vířivka
								</label>
							</div>
							<div class="radio" style="width: 100px;float: left;">
								<label>
									<input type="radio" name="optionsRadios" value="sauna" class="saunaradio" <?php if (isset($getclient['customer']) && $getclient['customer'] == "0") {echo 'checked';}?>>Sauna
								</label>
							</div>
							<div class="radio" style="width: 140px;float: left;">
								<label>
									<input type="radio" name="optionsRadios" value="both" class="bothradio" <?php if (isset($getclient['customer']) && $getclient['customer'] == "3") {echo 'checked';}?>>Vířivka + Sauna
								</label>
							</div>
                            <div class="radio" style="width: 120px;float: left;">
                                <label>
                                    <input type="radio" name="optionsRadios" value="pergola" class="pergolaradio" <?php if (isset($getclient['customer']) && $getclient['customer'] == "4") {echo 'checked';}?>>Pergola
                                </label>
                            </div>

						</div>
					</div>




					<div class="tajtl" <?php if ($getclient['customer'] != 3 || $getclient['customer'] != 4) { ?>style="display: none;"<?php } ?>>	<hr style="margin-top: 10px; margin-bottom: 5px;"><div class="form-group" style="margin-bottom: 10px;">
				<label class="col-sm-3 control-label"><h4>Vířivka</h4></label></div>
				<hr style="margin-top: 5px;">
	</div>
	<div class="virivkens" <?php if (isset($getclient['customer']) && ($getclient['customer'] == "0" || $getclient['customer'] == "4")) { ?>style="display: none;"<?php } ?>>

<?php

    specs_demand($getclient, '1');
    specs_demand($getclient, '2');

    ?>

	</div>



		<div class="tajtl" <?php if ($getclient['customer'] != 3 || $getclient['customer'] != 4) { ?>style="display: none;"<?php } ?>>	<hr style="margin-top: 20px; margin-bottom: 5px;"><div class="form-group" style="margin-bottom: 10px;">
				<label class="col-sm-3 control-label"><h4>Sauna</h4></label></div>
				<hr style="margin-top: 5px;">
	</div>
	<div class="saunkens" <?php if (isset($getclient['customer']) && ($getclient['customer'] == "1" || $getclient['customer'] == "4")) { ?>style="display: none;"<?php } ?>>
		<?php if (isset($getclient['customer']) && $getclient['customer'] == 3) { ?>
					<div class="form-group">
						<label class="col-sm-3 control-label">Sauny</label>

						<div class="col-sm-5">

							<select class="form-control" name="saunatype">
								<?php while ($sauna = mysqli_fetch_array($saunyquery)) { ?>
								<option value="<?= $sauna['connect_name'] ?>" <?php if (isset($getclient['secondproduct']) && $getclient['secondproduct'] == $sauna['connect_name']) {echo 'selected';}?>><?php if ($sauna['code'] != "") {echo $sauna['code'];?> - <?php }if ($sauna['brand'] != "") {echo $sauna['brand'] . ' ' . ucfirst($sauna['fullname']);} else {echo ucfirst($sauna['fullname']);}?></option><?php } ?>
							</select>

						</div>
					</div>
					<?php } ?>

            <?php

            specs_sauna($getclient, '1');
            specs_sauna($getclient, '2');

            ?>
            </div>


        <div class="pergoly"
             <?php if (isset($getclient['customer']) && $getclient['customer'] != "4") { ?>style="display: none;"<?php } ?>>
            <?php

            specs_pergola($getclient, '1');
            specs_pergola($getclient, '2');

            ?>

        </div>

				</div>

			</div>

		</div>
	</div>
	<div class="row">

		<div class="col-md-12">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Správa poptávky
					</div>

				</div>


				<div class="panel-body">

					<div class="form-group">

						<label class="col-sm-3 control-label">Showroom</label>

						<div class="col-sm-5">

							<select class="form-control" name="showroom">
                                <option value="0" <?php if (isset($getclient['showroom']) && $getclient['showroom'] == '0') {echo 'selected';}?>>Neznámý showroom</option>
                                <?php
                                $showrooms_query = $mysqli->query("SELECT * FROM shops_locations WHERE type = 'branch'")or die($mysqli->error);
                                while($showroom = mysqli_fetch_assoc($showrooms_query)){
                                ?>
								<option value="<?= $showroom['id'] ?>>" <?php if (isset($getclient['showroom']) && $getclient['showroom'] == $showroom['id']) {echo 'selected';}?>><?= $showroom['name'] ?></option>
								<?php } ?>
							</select>

						</div>

					</div>
					<div class="form-group">
						<?php $admins_query = $mysqli->query("SELECT id, user_name FROM demands WHERE (role = 'salesman' OR role = 'salesman-technician') AND active = 1");?>
						<label class="col-sm-3 control-label">O poptávku se stará</label>

						<div class="col-sm-5">

							<select class="form-control" name="admin_id">
								<option value="0" <?php if (isset($getclient['admin_id']) && $getclient['admin_id'] == 0) {echo 'selected';}?>>Nikdo nepřiřazen</option>
								<?php while ($admin = mysqli_fetch_array($admins_query)) { ?>
								<option value="<?= $admin['id'] ?>" <?php if (isset($getclient['admin_id']) && $getclient['admin_id'] == $admin['id']) {echo 'selected';}?>><?= $admin['user_name'] ?></option>
								<?php } ?>
							</select>

						</div>

					</div>

				</div>

			</div>

		</div>
</div>


<div class="row">

		<div class="col-md-12">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Kontakty k poptávce
					</div>

				</div>


				<div class="panel-body">

		<?php $contacts_query = $mysqli->query("SELECT * FROM demands_contacts WHERE demand_id = '$id'") or die($mysqli->error);

    $contact_number = 0;

    while ($contact = mysqli_fetch_array($contacts_query)) {

        $contact_number++;
        ?>


					<div class="col-md-3">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Kontakt #<?= $contact_number ?>
					</div>

				</div>


				<div class="panel-body">

					<div class="form-group">


						<div class="col-sm-12">

							<label class="col-sm-3 control-label">Jméno</label>

							<div class="col-sm-9">

							<input type="text" name="contact_name[]" class="form-control" id="field-2" value="<?= $contact['name'] ?>">

							</div>

						</div>

					</div>

					<div class="form-group">


						<div class="col-sm-12">

							<label class="col-sm-3 control-label">Role</label>

							<div class="col-sm-9">


								<select class="form-control" name="contact_role[]">

									<option value="">žádná</option>
									<option value="investor" <?php if (isset($contact['role']) && $contact['role'] == 'investor') {echo 'selected';}?>>investor</option>
									<option value="prebirajici" <?php if (isset($contact['role']) && $contact['role'] == 'prebirajici') {echo 'selected';}?>>přebírající</option>
									<option value="architekt" <?php if (isset($contact['role']) && $contact['role'] == 'architekt') {echo 'selected';}?>>architekt</option>
									<option value="stavbyvedoucí" <?php if (isset($contact['role']) && $contact['role'] == 'stavbyvedoucí') {echo 'selected';}?>>stavbyvedoucí</option>
									<option value="designer" <?php if (isset($contact['role']) && $contact['role'] == 'designer') {echo 'selected';}?>>designer</option>
									<option value="developer" <?php if (isset($contact['role']) && $contact['role'] == 'developer') {echo 'selected';}?>>developer</option>
									<option value="elektrikář" <?php if (isset($contact['role']) && $contact['role'] == 'elektrikář') {echo 'selected';}?>>elektrikář</option>
									<option value="manžel/manželka" <?php if (isset($contact['role']) && $contact['role'] == 'manžel/manželka') {echo 'selected';}?>>manžel/manželka</option>
								</select>

							</div>

						</div>

					</div>

					<div class="form-group">


						<div class="col-sm-12">

							<label class="col-sm-3 control-label">Telefon</label>

							<div class="col-sm-9">

							<input type="text" name="contact_phone[]" class="form-control" id="field-2" value="<?= $contact['phone'] ?>">

							</div>

						</div>

					</div>

					<div class="form-group">


						<div class="col-sm-12">

							<label class="col-sm-3 control-label">E-mail</label>

							<div class="col-sm-9">

							<input type="text" name="contact_email[]" class="form-control" id="field-2" value="<?= $contact['email'] ?>">

							</div>

						</div>

					</div>

				</div>

			</div>

				</div>

			<?php }

    if ($contact_number != 4) {

        $contact_number++;

        for ($num = $contact_number; $num < 5; $num++) {

            ?>



	<div class="col-md-3">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						Kontakt #<?= $num ?>
					</div>

				</div>


				<div class="panel-body">

					<div class="form-group">


						<div class="col-sm-12">

							<label class="col-sm-3 control-label">Jméno</label>

							<div class="col-sm-9">

							<input type="text" name="contact_name[]" class="form-control" id="field-2">

							</div>

						</div>

					</div>

					<div class="form-group">


						<div class="col-sm-12">

							<label class="col-sm-3 control-label">Role</label>

							<div class="col-sm-9">

								<select class="form-control" name="contact_role[]">

									<option value="">žádná</option>
									<option value="investor">investor</option>
									<option value="architekt">architekt</option>
									<option value="stavbyvedoucí">stavbyvedoucí</option>
									<option value="designer">designer</option>
									<option value="developer">developer</option>
									<option value="elektrikář">elektrikář</option>

								</select>

							</div>

						</div>

					</div>

					<div class="form-group">


						<div class="col-sm-12">

							<label class="col-sm-3 control-label">Telefon</label>

							<div class="col-sm-9">

							<input type="text" name="contact_phone[]" class="form-control" id="field-2">

							</div>

						</div>

					</div>

					<div class="form-group">


						<div class="col-sm-12">

							<label class="col-sm-3 control-label">E-mail</label>

							<div class="col-sm-9">

							<input type="text" name="contact_email[]" class="form-control" id="field-2">

							</div>

						</div>

					</div>

				</div>

			</div>

				</div>



				<?php } ?>





    <?php } ?>
                </div>

            </div>
        </div>
</div>

	<center>
	<div class="form-group default-padding">
		<a href="./zobrazit-poptavku?id=<?= $_REQUEST['id'] ?>" style="margin-bottom: 24px; margin-right: 16px; margin-top: 20px; display: inline-block;"><button type="button" class="btn btn-default btn-lg" style="font-size: 20px; padding: 20px 40px 20px 40px; ">Zpět</button></a>
		<span class="button-demo"><button type="submit" style="margin-bottom: 24px; margin-right: 26px; font-size: 20px; padding: 20px 50px 20px 140px; margin-top: 20px;" data-color="red" data-style="zoom-in" class="ladda-button btn btn-primary btn-icon icon-left btn-lg"><i class="entypo-pencil" style="line-height: 48px;font-size: 40px; padding: 10px 20px;"></i> <span class="ladda-label">Upravit poptávku</span></button></span>
	</div></center>

</form>
<?php include VIEW . '/default/footer.php'; ?>



<?php


} else {

    include INCLUDES . "/404.php";

}?>


