<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include_once INCLUDES . "/functions.php";


if (isset($_REQUEST['od'])) {$od = $_REQUEST['od'];}

$filters = array('realization', 'status', 'contract', 'unfinished', 'technical', 'customer', 'category', 'showroom', 'admin_id', 'year', 'area', 'subcat', 'brand');

foreach ($filters as $filter) {

    if (isset($_REQUEST[$filter])) {$$filter = $_REQUEST[$filter];}

}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "changetype") {

    $updatequery = $mysqli->query('UPDATE demands SET status = "' . $_POST['typus'] . '" WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);

    if ($_POST['status'] == 5) {

        $mysqli->query("UPDATE warehouse SET status = 4 WHERE demand_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

    }

    if (isset($_REQUEST['status']) && $_REQUEST['status'] != "") {

        header('location: https://www.wellnesstrade.cz/admin/pages/demands/editace-poptavek?status=' . $_REQUEST['status'] . '&edit=success');
        exit;

    } else {

        header('location: https://www.wellnesstrade.cz/admin/pages/demands/editace-poptavek?edit=success');
        exit;

    }

}

if (isset($status) && ($status != '4' && $status != '8' && $status != '5')) {

    $pagetitle = "Editace poptávek";

} elseif (isset($status) && $status == '5') {

    $pagetitle = "Hotové";

} else {

    $pagetitle = "Realizace";

}

if (!isset($_REQUEST['admin_id'])) {

    $admin_id = 'all';
    $_REQUEST['admin_id'] = 'all';

} else {

    $admin_id = $_REQUEST['admin_id'];

}


//function demand_filtration_route($current, $filters, $requests){
//
//	$route = '';
//	if(($key = array_search($current, $filters)) !== false) {
//	    unset($filters[$key]);
//	}
//
//	if($current == 'customer'){
//		if (($key = array_search('category', $filters)) !== false) {
//	    	unset($filters[$key]);
//		}
//	}
//
//	if($current == 'status'){
//		if (($key = array_search('realization', $filters)) !== false) {
//	    	unset($filters[$key]);
//		}
//	}
//
//
//	foreach($filters as $filter){
//
//	      if(isset($requests[$filter])){ $route .= '&'.$filter.'='.$requests[$filter];}
//
//	}
//
//	return $route;
//
//}

function demand_filtration_query($filters, $requests){

    //print_r($filters);

	$query = "";
	foreach($filters as $filter){

	      if(isset($requests[$filter]) && $filter != 'category' && $filter != 'subcat' && $filter != 'realization' && $requests[$filter] != 'all' && $filter != 'year' && $filter != 'brand'){
		
					$query .= ' AND d.'.$filter.' = "'.$requests[$filter].'"';

			}elseif(isset($requests[$filter]) && $filter == 'category' && $filter != 'realization'){

					$cat = '"'.$requests[$filter].'"';

						if(isset($$customer) && $$customer == 3){
								$query .= ' AND (d.product = '.$cat.' OR d.secondproduct = '.$cat.')';
						}else{
								$query .= ' AND d.product = '.$cat;
						}

			}elseif(isset($requests[$filter]) && $filter == 'year' && $requests[$filter] != 'all'){

                $query .= ' AND year(d.realization) = '.$requests[$filter];

            }

	      if(isset($requests[$filter]) && $filter == 'brand' && $requests[$filter] != 'all'){

              $query .= ' AND p.brand = "'.$requests[$filter].'"';

          }

	      if($filter == 'subcat' && !empty($requests[$filter])){

              if (function_exists('getCategoryProducts')) {

                  $products = getCategoryProducts($requests[$filter]);

                  $query_product_limiter = '';
                  foreach($products as $product){

                      if(empty($query_product_limiter)){
                          $query_product_limiter .= '"'.$product.'"';
                      }else{
                          $query_product_limiter .= ', "'.$product.'"';
                      }

                  }

                  $query .= ' AND d.product IN ('.$query_product_limiter.')';
              }
          }

	}

	return $query;

}

function getTotal(){

	global $mysqli;
	global $filters;
	global $_REQUEST;

	global $status;
	global $realization;

	$query = demand_filtration_query($filters, $_REQUEST);

	if($status == 4){

		if(isset($realization) && $realization == 0){

			$demands_max = $mysqli->query("SELECT d.id FROM (demands d, warehouse w, specs s)
			 
			 LEFT JOIN demands_specs_bridge b ON b.specs_id = s.id AND b.client_id = d.id
             LEFT JOIN warehouse_specs_bridge wb ON wb.specs_id = s.id AND wb.client_id = w.id
             
			 LEFT JOIN demands_advance_invoices i ON i.demand_id = d.id AND i.status = 1 LEFT JOIN demands_generate_hottub g ON g.id = d.id LEFT JOIN warehouse_products p ON p.connect_name = d.product WHERE s.technical = '1' AND d.status = '4' AND w.demand_id = d.id AND 
			
			 ((
		 
		 (wb.value != b.value) AND (EXISTS(SELECT value FROM demands_specs_bridge WHERE specs_id = s.id AND client_id = d.id) AND EXISTS(SELECT value FROM warehouse_specs_bridge WHERE specs_id = s.id AND client_id = w.id))
		 
		 ) OR (b.value != '' AND NOT EXISTS(SELECT value FROM warehouse_specs_bridge WHERE specs_id = s.id AND client_id = w.id))
		 )
        
        AND NOT (b.value = '' AND wb.value = 'Ne')
        AND NOT (b.value = 'Ne' AND wb.value = '')
       

			
			$query GROUP BY d.id")or die($mysqli->error);

		}elseif(isset($realization) && $realization == 1){

			$demands_max = $mysqli->query("SELECT d.id FROM (demands d LEFT JOIN demands_specs_bridge b ON b.client_id = d.id) LEFT JOIN warehouse w ON w.demand_id = d.id LEFT JOIN warehouse_specs_bridge wb ON b.specs_id = wb.specs_id AND wb.client_id = w.id WHERE d.status = '4' AND d.id NOT IN (
			
			SELECT d.id FROM 
			
		  (demands d, warehouse w, specs s)
           LEFT JOIN demands_specs_bridge b ON b.specs_id = s.id AND b.client_id = d.id
           LEFT JOIN warehouse_specs_bridge wb ON wb.specs_id = s.id AND wb.client_id = w.id
          WHERE
          s.technical = 1 AND
          s.warehouse_spec = 1 AND
          d.status = '4' AND
          w.demand_id = d.id AND
        

	 ((
		 
		 (wb.value != b.value) AND (EXISTS(SELECT value FROM demands_specs_bridge WHERE specs_id = s.id AND client_id = d.id) AND EXISTS(SELECT value FROM warehouse_specs_bridge WHERE specs_id = s.id AND client_id = w.id))
		 
		 ) OR (b.value != '' AND NOT EXISTS(SELECT value FROM warehouse_specs_bridge WHERE specs_id = s.id AND client_id = w.id))
		 )
        
        AND NOT (b.value = '' AND wb.value = 'Ne')
        AND NOT (b.value = 'Ne' AND wb.value = '')
			 
			 GROUP BY d.id
			
			
			
			) $query GROUP BY d.id")or die($mysqli->error);

		}else{

			$demands_max = $mysqli->query("SELECT d.id FROM demands d, warehouse_products p WHERE p.connect_name = d.product $query")or die($mysqli->error);

		}

			$total['count'] = mysqli_num_rows($demands_max);

	}else{

			$demands_max = $mysqli->query("SELECT COUNT(d.id) AS count FROM demands d, warehouse_products p WHERE p.connect_name = d.product $query")or die($mysqli->error);
			$total = mysqli_fetch_array($demands_max);

	}

	return $total['count'];

}


function getAll($s_pocet, $perpage){

	global $mysqli;
	global $filters;
	global $_REQUEST;

	$query = demand_filtration_query($filters, $_REQUEST);

	global $status;
	global $realization;


if($status == 4){

    // not ready realizations
	if(isset($realization) && $realization == 0){

		$demands_query = $mysqli->query("SELECT 
		d.*, p.*, w.*, bill.*, ship.*, i.*, d.status as demand_status,d.product as product, w.id as warehouse_id, DATE_FORMAT(i.date, '%d. %m. %Y') as invoice_date, DATE_FORMAT(i.due_date, '%d. %m. %Y') as due_date, DATE_FORMAT(i.payment_date, '%d. %m. %Y') as payment_date, DATE_FORMAT(w.loadingdate, '%d. %m. %Y') as loading_date, DATE_FORMAT(g.deadline_date, '%d. %m. %Y') as duerino, d.customer as customer, w.serial_number, w.status, w.demand_id, d.id as id, DATE_FORMAT(d.date, '%d. %m. %Y') as dateformated, DATE_FORMAT(d.realization, '%d. %m. %Y') as realizationformated,
 DATE_FORMAT(w.loadingdate, '%d. %m. %Y') as loadingformated
		 FROM (demands d, warehouse w, specs s)
             LEFT JOIN demands_specs_bridge b ON b.specs_id = s.id AND b.client_id = d.id
             LEFT JOIN warehouse_specs_bridge wb ON wb.specs_id = s.id AND wb.client_id = w.id
             LEFT JOIN demands_advance_invoices i ON i.demand_id = d.id AND i.status = 1 
             LEFT JOIN demands_generate_hottub g ON g.id = d.id 
             LEFT JOIN warehouse_products p ON p.connect_name = d.product 
             LEFT JOIN addresses_billing bill ON bill.id = d.billing_id 
             LEFT JOIN addresses_shipping ship ON ship.id = d.shipping_id 
		 WHERE s.technical = 1 
		   AND s.warehouse_spec = 1 
		   AND d.status = '4' 
		   AND w.demand_id = d.id 
		   AND (
		       (
                 (wb.value != b.value) AND (EXISTS(SELECT value FROM demands_specs_bridge WHERE specs_id = s.id AND client_id = d.id) AND EXISTS(SELECT value FROM warehouse_specs_bridge WHERE specs_id = s.id AND client_id = w.id))
                 
                 ) 
               OR (b.value != '' AND NOT EXISTS(SELECT value FROM warehouse_specs_bridge WHERE specs_id = s.id AND client_id = w.id))
		 )
        
        AND NOT (b.value = '' AND wb.value = 'Ne')
        AND NOT (b.value = 'Ne' AND wb.value = '')
		  
		  $query 

		 GROUP BY d.id ORDER BY CASE WHEN d.realization LIKE '%00%' THEN 3 ELSE 2 END, d.realization limit ".$s_pocet.",".$perpage)or die($mysqli->error);

    // ready realizations
	}elseif(isset($realization) && $realization == 1){

//		$demands_query = $mysqli->query("SELECT d.*, p.*, w.*, bill.*, ship.*, i.*, d.status as demand_status,d.product as product, w.id as warehouse_id, DATE_FORMAT(i.date, '%d. %m. %Y') as invoice_date, DATE_FORMAT(i.due_date, '%d. %m. %Y') as due_date, DATE_FORMAT(i.payment_date, '%d. %m. %Y') as payment_date, DATE_FORMAT(w.loadingdate, '%d. %m. %Y') as loading_date, DATE_FORMAT(g.deadline_date, '%d. %m. %Y') as duerino, d.customer as customer, w.serial_number, w.status, w.demand_id, d.id as id, DATE_FORMAT(d.date, '%d. %m. %Y') as dateformated, DATE_FORMAT(d.realization, '%d. %m. %Y') as realizationformated,
// DATE_FORMAT(w.loadingdate, '%d. %m. %Y') as loadingformated FROM (demands d LEFT JOIN demands_specs_bridge b ON b.client_id = d.id) LEFT JOIN demands_advance_invoices i ON i.demand_id = d.id AND i.status = 1 LEFT JOIN demands_generate_hottub g ON g.id = d.id LEFT JOIN warehouse_products p ON p.connect_name = d.product LEFT JOIN warehouse w ON w.demand_id = d.id LEFT JOIN warehouse_specs_bridge wb ON b.specs_id = wb.specs_id AND wb.client_id = w.id LEFT JOIN addresses_billing bill ON bill.id = d.billing_id LEFT JOIN addresses_shipping ship ON ship.id = d.shipping_id WHERE d.status = '4' AND d.id NOT IN (SELECT d.id FROM demands d, demands_specs_bridge b, warehouse w, warehouse_specs_bridge wb, specs s WHERE s.id = b.specs_id AND s.technical = 1 AND s.warehouse_spec = 1 AND d.status = '4' AND b.client_id = d.id AND b.specs_id = wb.specs_id AND wb.client_id = w.id AND w.demand_id = d.id AND wb.value != b.value GROUP BY d.id) $query GROUP BY d.id ORDER BY CASE WHEN d.realization LIKE '%00%' THEN 3 ELSE 2 END, d.realization limit ".$s_pocet.",".$perpage)or die($mysqli->error);


        $demands_query = $mysqli->query("SELECT d.*, p.*, w.*, bill.*, ship.*, i.*, d.status as demand_status,d.product as product, w.id as warehouse_id, DATE_FORMAT(i.date, '%d. %m. %Y') as invoice_date, DATE_FORMAT(i.due_date, '%d. %m. %Y') as due_date, DATE_FORMAT(i.payment_date, '%d. %m. %Y') as payment_date, DATE_FORMAT(w.loadingdate, '%d. %m. %Y') as loading_date, DATE_FORMAT(g.deadline_date, '%d. %m. %Y') as duerino, d.customer as customer, w.serial_number, w.status, w.demand_id, d.id as id, DATE_FORMAT(d.date, '%d. %m. %Y') as dateformated, DATE_FORMAT(d.realization, '%d. %m. %Y') as realizationformated,
 DATE_FORMAT(w.loadingdate, '%d. %m. %Y') as loadingformated FROM (demands d LEFT JOIN demands_specs_bridge b ON b.client_id = d.id) LEFT JOIN demands_advance_invoices i ON i.demand_id = d.id AND i.status = 1 LEFT JOIN demands_generate_hottub g ON g.id = d.id LEFT JOIN warehouse_products p ON p.connect_name = d.product LEFT JOIN warehouse w ON w.demand_id = d.id LEFT JOIN warehouse_specs_bridge wb ON b.specs_id = wb.specs_id AND wb.client_id = w.id LEFT JOIN addresses_billing bill ON bill.id = d.billing_id LEFT JOIN addresses_shipping ship ON ship.id = d.shipping_id WHERE d.status = '4' AND d.id NOT IN (SELECT d.id

 FROM
  (demands d, warehouse w, specs s)
   LEFT JOIN demands_specs_bridge b ON b.specs_id = s.id AND b.client_id = d.id
   LEFT JOIN warehouse_specs_bridge wb ON wb.specs_id = s.id AND wb.client_id = w.id
  WHERE
  s.technical = 1 AND
  s.warehouse_spec = 1 AND
  d.status = '4' AND
  w.demand_id = d.id AND
  
	 ((
		 
		 (wb.value != b.value) AND (EXISTS(SELECT value FROM demands_specs_bridge WHERE specs_id = s.id AND client_id = d.id) AND EXISTS(SELECT value FROM warehouse_specs_bridge WHERE specs_id = s.id AND client_id = w.id))
		 
		 ) OR (b.value != '' AND NOT EXISTS(SELECT value FROM warehouse_specs_bridge WHERE specs_id = s.id AND client_id = w.id))
		 )
        
        AND NOT (b.value = '' AND wb.value = 'Ne')
        AND NOT (b.value = 'Ne' AND wb.value = '')
  
  GROUP BY d.id)

   $query GROUP BY d.id ORDER BY CASE WHEN d.realization LIKE '%00%' THEN 3 ELSE 2 END, d.realization limit ".$s_pocet.",".$perpage)or die($mysqli->error);

    // not ready and ready realizations
	}else{

		$demands_query = $mysqli->query("SELECT d.*, p.*, w.*, bill.*, ship.*, i.*, d.status as demand_status,d.product as product, w.id as warehouse_id, DATE_FORMAT(i.date, '%d. %m. %Y') as invoice_date, DATE_FORMAT(i.due_date, '%d. %m. %Y') as due_date, DATE_FORMAT(i.payment_date, '%d. %m. %Y') as payment_date, DATE_FORMAT(w.loadingdate, '%d. %m. %Y') as loading_date, DATE_FORMAT(g.deadline_date, '%d. %m. %Y') as duerino, w.serial_number, w.status, w.demand_id, DATE_FORMAT(d.date_contract, '%d. %m. %Y') as date_contract, d.customer as customer, d.id as id, DATE_FORMAT(d.date, '%d. %m. %Y') as dateformated, DATE_FORMAT(d.realization, '%d. %m. %Y') as realizationformated,
 DATE_FORMAT(w.loadingdate, '%d. %m. %Y') as loadingformated FROM demands d LEFT JOIN demands_advance_invoices i ON i.demand_id = d.id AND i.status = 1 LEFT JOIN demands_generate_hottub g ON g.id = d.id LEFT JOIN warehouse_products p ON p.connect_name = d.product LEFT JOIN warehouse w ON w.demand_id = d.id LEFT JOIN addresses_billing bill ON bill.id = d.billing_id LEFT JOIN addresses_shipping ship ON ship.id = d.shipping_id WHERE d.status < 20 $query GROUP BY d.id ORDER BY CASE WHEN d.realization LIKE '%00%' THEN 3 ELSE 2 END, d.realization limit ".$s_pocet.",".$perpage)or die($mysqli->error);

	}

}else{


	$demands_query = $mysqli->query("SELECT d.*, p.*, w.*, bill.*, ship.*, d.status as demand_status, d.product as product, w.id as warehouse_id, DATE_FORMAT(i.date, '%d. %m. %Y') as invoice_date, DATE_FORMAT(i.payment_date, '%d. %m. %Y') as payment_date, DATE_FORMAT(w.loadingdate, '%d. %m. %Y') as loading_date, DATE_FORMAT(g.deadline_date, '%d. %m. %Y') as duerino, w.serial_number, w.status, w.demand_id, DATE_FORMAT(d.date_contract, '%d. %m. %Y') as date_contract, d.customer as customer, d.id as id, DATE_FORMAT(d.date, '%d. %m. %Y') as dateformated, DATE_FORMAT(d.realization, '%d. %m. %Y') as realizationformated,
 DATE_FORMAT(w.loadingdate, '%d. %m. %Y') as loadingformated FROM demands d LEFT JOIN demands_advance_invoices i ON i.demand_id = d.id AND i.status = 1 LEFT JOIN demands_generate_hottub g ON g.id = d.id LEFT JOIN warehouse_products p ON p.connect_name = d.product LEFT JOIN warehouse w ON w.demand_id = d.id LEFT JOIN addresses_billing bill ON bill.id = d.billing_id LEFT JOIN addresses_shipping ship ON ship.id = d.shipping_id WHERE d.status < 20 $query GROUP BY d.id order by (CASE WHEN d.realization != '0000-00-00' THEN d.realization END) DESC, CASE WHEN d.date_contract != '0000-00-00' THEN 2 ELSE 3 END, d.date_contract asc, d.id desc LIMIT ".$s_pocet.",".$perpage)or die($mysqli->error);

}



	$allDemands = mysqli_fetch_all($demands_query, MYSQLI_ASSOC);

	$demands = array();

	foreach($allDemands as $singleDemand){

	     $singleDemand['isRealization'] = $realization;

		 array_push($demands, singleDemand($singleDemand));

	}

	return $demands;

}


function singleDemand($demand){

	global $mysqli;
	global $demand_statuses;

	$singleDemand['id'] = $demand['id'];

	// USERNAME
	$singleDemand['username'] = $demand['user_name'];

	if($demand['technical'] == 4 && ($demand['demand_status'] == 4 || $demand['demand_status'] == 15)){

		$dot_colour = '#1089ff';

	}elseif($demand['technical'] == 1 && ($demand['demand_status'] == 4 || $demand['demand_status'] == 15)){

		$dot_colour = '#fe0';

	}elseif($demand['technical'] == 2 && ($demand['demand_status'] == 4 || $demand['demand_status'] == 15)){

		$dot_colour = '#ffa500';

	}elseif($demand['technical'] == 3 && ($demand['demand_status'] == 4 || $demand['demand_status'] == 15)){

		$dot_colour = '#24b729';

	}elseif($demand['technical'] == 0 && ($demand['demand_status'] == 4 || $demand['demand_status'] == 15)){

		$dot_colour = '#ff0000';

	}

	if($demand['unfinished'] == 0 && $demand['demand_status'] == 8){

		$dot_colour = '#000';

	}elseif($demand['unfinished'] == 1 && $demand['demand_status'] == 8){

		$dot_colour = '#ffa500';

	}elseif($demand['unfinished'] == 2 && $demand['demand_status'] == 8){

		$dot_colour = '#24b729';

	}

	if($demand['contract'] == 0 && ($demand['demand_status'] == 12 || $demand['demand_status'] == 14)){

		$dot_colour = '#ff0000';

	}elseif($demand['contract'] == 1 && ($demand['demand_status'] == 12 || $demand['demand_status'] == 14)){

		$dot_colour = '#fe0';

	}elseif($demand['contract'] == 2 && ($demand['demand_status'] == 12 || $demand['demand_status'] == 14)){

		$dot_colour = '#ffa500';

	}elseif($demand['contract'] == 3 && ($demand['demand_status'] == 12 || $demand['demand_status'] == 14)){

		$dot_colour = '#24b729';

	}


	if(isset($dot_colour)){

		$singleDemand['technical'] = '<span style="height: 10px; width: 10px; background-color: '.$dot_colour.'; border-radius: 50%; margin-right: 2px; display: inline-block;"></span>';

	}else{
		
		$singleDemand['technical'] = false;

	}


	// PHOTO
	if($demand['customer'] != 3){

		$singleDemand['photo'] = '<a href="/admin/pages/demands/zobrazit-poptavku?id='.$demand['id'].'" class="member-img" style="width: 5%;">
		<img src="https://www.wellnesstrade.cz/admin/data/images/customer/'.$demand['product'].'.png" width="63" class="img-rounded" /></a>';
	
	}else{

		if($demand['secondproduct'] != 'custom'){
			$second_product_query = $mysqli->query("SELECT brand, fullname FROM warehouse_products WHERE connect_name = '".$demand['secondproduct']."'")or die($mysqli->error);
			$second_product = mysqli_fetch_array($second_product_query);
		}

		$singleDemand['photo'] = '<a href="/admin/pages/demands/zobrazit-poptavku?id='.$demand['id'].'" class="member-img" style="width: 5%; margin-top: -4px;">
		<div style="width: 50%; height: 63px; overflow: hidden; float: left; text-align: left"><img style="height: 100%; width: auto; max-width: inherit; float: left;;" src="https://www.wellnesstrade.cz/admin/data/images/customer/'.$demand['product'].'.png" width="63" class="img-rounded" /></div>
		<div style="width: 50%; height: 63px; overflow: hidden; float: right; text-align: right"><img style="height: 100%; width: auto; max-width: inherit; float: left; margin-left: -100%;" src="https://www.wellnesstrade.cz/admin/data/images/customer/'.$demand['secondproduct'].'.png" width="63" class="img-rounded"/></div></a>';
	}



	if($demand['billing_email'] != ""){ $singleDemand['email'] = $demand['billing_email'];}else{ $singleDemand['email'] = "žádný email";}

	if(isset($demand['billing_phone']) && $demand['billing_phone'] != '' && $demand['billing_phone'] != 0){

	    $singleDemand['phone'] = phone_prefix($demand['billing_phone_prefix']).' '.number_format((int)$demand['billing_phone'], 0, ',', ' ');

    }else{

        $singleDemand['phone'] =  "žádný telefon";

    }


	foreach($demand_statuses as $status){ if($status['id'] == $demand['demand_status']){ $singleDemand['status'] = $status['name']; } }

    if($demand['demand_status'] == 4 || $demand['demand_status'] == 12 || $demand['demand_status'] == 8 || $demand['demand_status'] == 13 || $demand['demand_status'] == 15 || $demand['demand_status'] == 14){

		if($demand['customer'] == '0'){ $type = 'realization_sauna'; }else{ $type = 'realization_hottub'; }

        $technicians_query = $mysqli->query("SELECT c.user_name FROM demands c, mails_recievers t WHERE c.id = t.admin_id AND t.type_id = '".$demand['id']."' AND t.reciever_type = 'performer' AND t.type = '".$type."'") or die($mysqli->error);
        $i = 0;
        $allTechnicians = '';
        while ($technician = mysqli_fetch_array($technicians_query)) {
    
            if ($i == 0) { $i++; $allTechnicians .= $technician['user_name']; } else { $allTechnicians .= ', ' . $technician['user_name'];}

		}

        $area = 'neznámá lokalita: ';
		if($demand['area'] == 'prague'){ $area = 'Praha: '; }elseif($demand['area'] == 'brno'){ $area = 'Brno: '; }elseif($demand['area'] == 'plzen'){ $area = 'Plzeň: '; }
        if(empty($allTechnicians)){ $allTechnicians = 'žádný technik'; }else{ $allTechnicians = '<strong>'.$allTechnicians.'</strong>'; }

        $singleDemand['employee_info'] = $area.$allTechnicians;

    }else{

        if(isset($demand['showroom']) && $demand['showroom'] == 2){ $showroom = ' <strong>Hradčanská</strong>';}elseif(isset($demand['showroom']) && $demand['showroom'] == 3){ $showroom = ' <strong>Brno</strong>';}elseif(isset($demand['showroom']) && $demand['showroom'] == 5){ $showroom = ' <strong>Plzeň</strong>';}else{ $showroom = 'Neznámý showroom';}

        if($demand['admin_id'] != 0){

            $findadminquery = $mysqli->query("SELECT user_name FROM demands WHERE id = '".$demand['admin_id']."'");
            $findadmin = mysqli_fetch_array($findadminquery);

            $admin_id = ', <strong>'.$findadmin['user_name'].'</strong>';

        }else{ $admin_id = ', nikdo nepřiřazen';}

        $singleDemand['employee_info'] = $showroom.$admin_id.' od '.$demand['dateformated']; 

    }
    

	$find_starred_text = $mysqli->query("SELECT *, DATE_FORMAT(datetime, '%d. %m. %Y') as dateformated FROM demands_timeline WHERE client_id = '".$demand['id']."' AND star = '1'");
    $starred_text = mysqli_fetch_array($find_starred_text);

    if(!empty($starred_text)){

        $singleDemand['starred_text'] = $starred_text['text'].' <i>~ '.$starred_text['dateformated'].'</i>';

    }else{

        $singleDemand['starred_text'] = '';

    }


	if(isset($demand['demand_status']) && ($demand['demand_status'] == 1 || $demand['demand_status'] == 3 || $demand['demand_status'] == 2 || $demand['demand_status'] == 7)){

		$followUpQuery = $mysqli->query("SELECT * FROM demands_mails_history WHERE demand_id = '".$demand['id']."' AND state = 'ongoing' ORDER BY id DESC");

		if(mysqli_num_rows($followUpQuery) > 0){

            $singleDemand['realization_date'] = '';
			while($followUp = mysqli_fetch_array($followUpQuery)){

                $newDate = new DateTime($followUp['date_time']);

                if ($newDate->format('H:i:s') != "00:00:00") {

                    $date = $newDate->format('d. m. Y') . ' ' . $newDate->format('H:i');

                } else {

                    $date = $newDate->format('d. m. Y');
                }

                $singleDemand['realization_date'] .= '<i class="fa fa-envelope-o"></i> <span style="color: #A51218; font-weight: 500;">'.$followUp['type'].' - '.$date.'</span><br>';

            }

		}else{

			$singleDemand['realization_date'] = '<i class="fa fa-envelope-o"></i> <span>žádný nastavený Follow Up</span>';
			
		}

        $find = $mysqli->query("SELECT id, container_id FROM containers_products WHERE demand_id = '".$demand['id']."'");
        if(mysqli_num_rows($find) == 0 || isset($demand['serial_number'])){

            if(isset($demand['serial_number'])){ $serial_number = '<a href="../warehouse/zobrazit-virivku?id='.$demand['warehouse_id'].'" target="_blank">
		<strong style="cursor: pointer;color: #0071bc;">'.$demand['serial_number'].'</strong></a>'; }else{ $serial_number = 'žádné'; }

            $singleDemand['serial_number'] = '<i class="fa fa-truck" style="padding: 0 2px;"></i> Sériové číslo: '.$serial_number;

            if (!empty($demand['loadingdate']) && $demand['loadingdate'] != '0000-00-00') {

                if((isset($demand['realization']) && $demand['realization'] != '0000-00-00') && (isset($demand['loadingdate']) && $demand['loadingdate'] != '0000-00-00') && strtotime($demand['loadingdate']) > strtotime($demand['realization'])){

                    $singleDemand['serial_number'] .= ' ~ <strong class="text-danger" data-toggle="tooltip" data-placement="top" title="" data-original-title="Datum naskladnění"><i class="entypo-cancel-circled"></i> '.$demand['loadingformated'].'</strong>';

                }elseif((!isset($demand['realization']) || $demand['realization'] == '0000-00-00') || (!isset($demand['loadingdate']) || $demand['loadingdate'] == '0000-00-00')){

                    $singleDemand['serial_number'] .= ' ~ <strong class="text-info" data-toggle="tooltip" data-placement="top" title="" data-original-title="Datum naskladnění"><i class="entypo-info"></i> '.$demand['loadingformated'].'</strong>';

                }else{

                    $singleDemand['serial_number'] .= ' ~ <strong class="text-success" data-toggle="tooltip" data-placement="top" title="" data-original-title="Datum naskladnění"><i class="fa fa-check"></i> '.$demand['loadingformated'].'</strong>';

                }


            }else{

                $singleDemand['serial_number'] .= ' ~ <strong class="text-info" data-toggle="tooltip" data-placement="top" title="" data-original-title="Datum naskladnění"><i class="entypo-attention"></i> není</strong>';

            }


        }else{

            $finded = mysqli_fetch_array($find);

            $singleDemand['serial_number'] = '<i class="fa fa-truck" style="padding: 0 2px;"></i> Kontejner: <strong style="cursor: pointer;color: orange;">'.$finded['container_id'].'</strong>, Vířivka <strong style="cursor: pointer;color: orange;">#'.$finded['id'].' </strong>';

        }

	}else{

        if($demand['demand_status'] == 4 && isset($demand['isRealization']) && $demand['isRealization'] == 0){

            $allChanges = '';

            $searchquery = $mysqli->query("SELECT *, w.id as id, DATE_FORMAT(w.loadingdate, '%d. %m. %Y') as dateformated FROM warehouse w, warehouse_products p WHERE w.product = p.connect_name AND w.demand_id = '".$demand['id']."'") or die($mysqli->error);

        if (mysqli_num_rows($searchquery) > 0) {

            while ($warehouse = mysqli_fetch_array($searchquery)) {

                if (isset($warehouse['customer']) && $warehouse['customer'] == 1) {

                    $get_provedeni = $mysqli->query("SELECT value FROM warehouse_specs_bridge WHERE client_id = '" . $warehouse['id'] . "' AND specs_id = 5") or die($mysqli->error);

                    $provedeni = mysqli_fetch_array($get_provedeni);

                    $get_ids = $mysqli->query("SELECT w.id as id, w.name as name FROM warehouse_products_types w, warehouse_products p WHERE w.warehouse_product_id = p.id AND p.fullname = '" . $warehouse['fullname'] . "' AND w.name = '" . $provedeni['value'] . "'") or die($mysqli->error);
                    $get_id = mysqli_fetch_array($get_ids);



                    $specsquery = $mysqli->query("SELECT s.id, s.name, w.value as warehouse_value, d.value as demand_value, s.demand_category, s.technical FROM specs s INNER JOIN warehouse_products_types_specs wh ON wh.spec_id = s.id AND wh.type_id = '" . $get_id['id'] . "' AND s.technical = 1 AND s.warehouse_spec = 1 LEFT JOIN warehouse_specs_bridge w ON w.specs_id = s.id AND w.client_id = '" . $warehouse['id'] . "' LEFT JOIN demands_specs_bridge d ON d.specs_id = s.id AND d.client_id = '".$demand['id']."' WHERE 
                    
                   
                  ((
		 
		 (w.value != d.value) AND (EXISTS(SELECT value FROM demands_specs_bridge WHERE specs_id = s.id AND client_id = '".$demand['id']."') AND EXISTS(SELECT value FROM warehouse_specs_bridge WHERE specs_id = s.id AND client_id = '" . $warehouse['id'] . "'))
		 
		 ) OR (d.value != '' AND NOT EXISTS(SELECT value FROM warehouse_specs_bridge WHERE specs_id = s.id AND client_id = '" . $warehouse['id'] . "'))
		 
		 )
        
        AND NOT (d.value = '' AND w.value = 'Ne')
        AND NOT (d.value = 'Ne' AND w.value = '')
                    
                     
                     GROUP BY s.id order by s.demand_category asc, s.name asc") or die($mysqli->error);

                        $technical = false;

                            while ($specs = mysqli_fetch_array($specsquery)) {

                                if (isset($specs['technical']) && $specs['technical'] == 1 && !$technical) {
                                    $technical = true;

                                    // $category_warehouse_done = $specs['demand_category'];
                                }

                                if($allChanges !== '') { $delimiter = '&nbsp; ~ &nbsp;'; }else{ $delimiter = ''; }
                                    $allChanges .= $delimiter.'<strong>' . $specs['name'] . ' &#x2192; ' . mb_strtoupper($specs['demand_value']) . '</strong>';

                            }

                }
            }
        }

            $singleDemand['hasChanges'] = '<div class="col-md-12" style="float: left;font-size: 11px;color: #666;background-color: #f7f4f4;border: 1px solid #ebebeb;padding: 8px 10px 10px;margin-top: 4px;border-radius: 3px;"><i style="color: #d42020; font-size: 16px; padding-right: 6px;" class="entypo-attention"></i> '.$allChanges.'</div>';





			$task_query = $mysqli->query("SELECT *, DATE_FORMAT(due, '%d. %m. %Y') as dateformated, DATE_FORMAT(time, '%H:%i') as timeformated FROM tasks WHERE demand_id = '".$demand['id']."' AND warehouse_id != 0 AND status != 3") or die($mysqli->error);

			if (mysqli_num_rows($task_query) > 0) {

				while($task = mysqli_fetch_array($task_query)){

				$taskValues = 'Naplánováno - <a href="../tasks/zobrazit-ukol?id='.$task['id'].'" target="_blank" style="font-weight: bold; text-decoration: underline;">'.$task['title'].'</a>';


				$taskValues .= '&nbsp; ~ &nbsp;'.$task['dateformated'].' '.$task['timeformated'].'&nbsp; &#x2192; &nbsp;'.$task['text'].' <span style="float: right;">🛠️ Proveditelé: <strong>' . getRecievers('task', $task['id'], 'performer').'</strong></span>';


				$singleDemand['hasChanges'] .= '<div class="col-md-12" style="line-height: 22px; float: left;font-size: 11px;color: #666;background-color: #f7f4f4;border: 1px solid #ebebeb;padding: 9px 10px; margin-top: 4px;border-radius: 3px;"><i style="color: #1089ff; font-size: 16px; padding-left: 4px; padding-right: 10px;" class="fa fa-wrench"></i> '.$taskValues.' </div>';

				}

			}




        }

		if($demand['realizationformated'] != '00. 00. 0000'){

			if(isset($demand['customer']) && $demand['customer'] == 1){ $product = 'hottub';}else{ $product = 'sauna';}
			if(isset($demand['confirmed']) && $demand['confirmed'] == 1){ $color = 'color: #00a651;'; }elseif(isset($demand['confirmed']) && $demand['confirmed'] == 2) {

				$color = 'color: #FF9933;';
				
			}else{  $color = 'color: #21d1e1;'; }

			if(isset($demand['duerino']) && $demand['duerino'] != '00. 00. 0000'){ $deadline = $demand['duerino']; }else{ $deadline = 'není'; } 

			$singleDemand['realization_date'] = '<i class="entypo-tools"></i> <span style="'.$color.'">Realizace <strong>'.$demand['realizationformated'].'</strong></span> ~ <span>Deadline: <strong>'.$deadline.'</strong></span>';

		}else{ 

			if(isset($demand['demand_status']) && $demand['demand_status'] == 4) {

				$singleDemand['realization_date'] = '<i class="entypo-tools"></i> <span style="color: #d42020; font-weight: bold;">Den realizace nebyl stanoven</span>';

			}else{

				$singleDemand['realization_date'] = '<i class="entypo-tools"></i> <span>Den realizace nebyl stanoven</span>';

			}

		}

		$find = $mysqli->query("SELECT id, container_id FROM containers_products WHERE demand_id = '".$demand['id']."'");
		if(mysqli_num_rows($find) == 0 || isset($demand['serial_number'])){
  
			if(isset($demand['serial_number'])){ $serial_number = '<a href="../warehouse/zobrazit-virivku?id='.$demand['warehouse_id'].'" target="_blank">
		<strong style="cursor: pointer;color: #0071bc;">'.$demand['serial_number'].'</strong></a>'; }else{ $serial_number = 'žádné'; }
  
				$singleDemand['serial_number'] = '<i class="fa fa-truck" style="padding: 0 2px;"></i> Sériové číslo: '.$serial_number;

                if (!empty($demand['loadingdate']) && $demand['loadingdate'] != '0000-00-00') {

                    if((isset($demand['realization']) && $demand['realization'] != '0000-00-00') && (isset($demand['loadingdate']) && $demand['loadingdate'] != '0000-00-00') && strtotime($demand['loadingdate']) > strtotime($demand['realization'])){

                        $singleDemand['serial_number'] .= ' ~ <strong class="text-danger" data-toggle="tooltip" data-placement="top" title="" data-original-title="Datum naskladnění"><i class="entypo-cancel-circled"></i> '.$demand['loadingformated'].'</strong>';

                    }elseif((!isset($demand['realization']) || $demand['realization'] == '0000-00-00') || (!isset($demand['loadingdate']) || $demand['loadingdate'] == '0000-00-00')){

                        $singleDemand['serial_number'] .= ' ~ <strong class="text-info" data-toggle="tooltip" data-placement="top" title="" data-original-title="Datum naskladnění"><i class="entypo-info"></i> '.$demand['loadingformated'].'</strong>';

                    }else{

                        $singleDemand['serial_number'] .= ' ~ <strong class="text-success" data-toggle="tooltip" data-placement="top" title="" data-original-title="Datum naskladnění"><i class="fa fa-check"></i> '.$demand['loadingformated'].'</strong>';

                    }


                }else{

                    $singleDemand['serial_number'] .= ' ~ <strong class="text-info" data-toggle="tooltip" data-placement="top" title="" data-original-title="Datum naskladnění"><i class="entypo-attention"></i> není</strong>';

                }


		  }else{
  
				$finded = mysqli_fetch_array($find);
  
				$singleDemand['serial_number'] = '<i class="fa fa-truck" style="padding: 0 2px;"></i> Kontejner: <strong style="cursor: pointer;color: orange;">'.$finded['container_id'].'</strong>, Vířivka <strong style="cursor: pointer;color: orange;">#'.$finded['id'].' </strong>';
  
			 }

	}




	return $singleDemand;

}






function demandFiltration(){

	global $mysqli;

	global $status;
	global $customer;
	global $category;
    global $brand;

	$filtration = '';


		// CUSTOMER TYPE FILTRATION WITH PRODUCTS
		$current = 'customer';

		$filter_array = array(
					    0 => array(
					        'id' => '1',
					        'name' => 'Vířivky',
					        'subcat' => 'hottub'
					    ),
					    1 => array(
					        'id' => '1',
					        'name' => 'Swim SPA',
					        'subcat' => 'swimspa'
					    ),
					    2 => array(
					        'id' => '0',
					        'name' => 'Sauny',
                            'subcat' => 'sauna'
					    ),
					    3 => array(
					        'id' => '3',
					        'name' => 'Vířivky + Sauny',
                            'subcat' => ' '

					    ),
                        4 => array(
                            'id' => '4',
                            'name' => 'Pergoly',
                            'subcat' => 'pergola'

                        ),
					);

		$filtration .= '<div class="col-md-4" style="text-align: left; width: 32%;">'.filtrationButtons($current, $filter_array).'</div>';


    if($status != '5') {

        // PERSON FILTRATION
        $current = 'admin_id';

        $filter_array = $mysqli->query('SELECT id, user_name as name FROM demands WHERE (role = "salesman" OR role = "salesman-technician") AND active = 1 ORDER BY id') or die($mysqli->error);

        $filtration .= '<div class="col-md-8" style="text-align: right; width: 68%;">' . filtrationButtons($current, $filter_array) . '</div>
        <div style="clear: both"></div><hr style="margin: 10px 0;">';

    }


    // EVERYTHING EXCEPT REALIZATIONS
	if($status != '4' && $status != '5' && $status != '8' && $status != '13' && $status != '15'){


		// STATUS FILTRATION
		$current = 'status';

		$filter_array = array(
					    0 => array(
					        'id' => '1',
					        'name' => 'Nezpracované'
					    ),
					    1 => array(
					        'id' => '3',
					        'name' => 'V řešení'
					    ),
					    2 => array(
					        'id' => '2',
					        'name' => 'Zhotovené nabídky'
					    ),
						3 => array(
							'id' => '12',
							'name' => 'Prodané'
						),
						4 => array(
							'id' => '14',
							'name' => 'Neobjednané vířivky'
						),
					    5 => array(
					        'id' => '7',
					        'name' => 'Odložené'
					    ),
					    6 => array(
					        'id' => '6',
					        'name' => 'Stornované'
					    ),
					);

		$filtration .= '<div class="col-md-4" style="text-align: right; width: 100%; float: right;">'.filtrationButtons($current, $filter_array).'</div>';


		if(isset($status) && ($status == 12 || $status == 14 || $status == 15)){

					$current = 'contract';

					$filter_array = array(
					    0 => array(
					        'id' => '0',
					        'name' => '<span class="circle-color red"></span> Nevystavená smlouva'
					    ),
					    1 => array(
					        'id' => '1',
					        'name' => '<span class="circle-color yellow"></span> Vystavená smlouva'
					    ),
					    2 => array(
					        'id' => '2',
					        'name' => '<span class="circle-color orange"></span> Podepsaná smlouva'
					    ),
					    3 => array(
					        'id' => '3',
					        'name' => '<span class="circle-color green"></span> Zaplacená'
					    ),
					);

		$filtration .= '<div class="col-md-4" style="text-align: right; width: 39%; float: right; margin-top: 6px;">'.filtrationButtons($current, $filter_array).'</div>';

		}


	// UNFINISHED FILTRATION
	}elseif($status == '8'){

		// STATUS FILTRATION
				$current = 'status';

				$filter_array = array(
							    1 => array(
							        'id' => '15',
							        'name' => 'Nové realizace'
							    ),
								2 => array(
							        'id' => '4',
							        'second_id' => '0',
							        'name' => 'Nepřipravené'
							    ),
							    3 => array(
							        'id' => '4',
							        'second_id' => '1',
							        'name' => 'Připravené'
							    ),
							    4 => array(
							        'id' => '8',
							        'name' => 'Nedokončené'
							    ),
							    5 => array(
							        'id' => '13',
							        'name' => 'Dokončené'
							    ),
							);

		$filtration .= '<div class="col-md-4" style="text-align: right; width: 73%; float: right;">'.filtrationButtons($current, $filter_array).'</div>';

				$current = 'unfinished';

				$filter_array = array(
								    0 => array(
								        'id' => '0',
								        'name' => 'Neřešeno'
								    ),
								    1 => array(
								        'id' => '1',
								        'name' => 'V řešení'
								    ),
								    2 => array(
								        'id' => '2',
								        'name' => 'Připravená'
								    )
								);

		$filtration .= '<div class="col-md-4" style="text-align: right; width: 39%; float: right; margin-top: 6px;">'.filtrationButtons($current, $filter_array).'</div>';


	// REALIZATIONS FILTRATION
	}elseif($status != '5'){


		// STATUS FILTRATION
		$current = 'area';

		$filter_array = array(
			1 => array(
				'id' => 'prague',
				'name' => 'Praha'
			),
			2 => array(
				'id' => 'brno',
				'name' => 'Brno'
			),
		);

		$filtration .= '<div class="col-md-4" style="text-align: left; width: 23%; float: left;">'.filtrationButtons($current, $filter_array).'</div>';



				// STATUS FILTRATION
				$current = 'status';

				$filter_array = array(
								1 => array(
									'id' => '15',
									'name' => 'Nové realizace'
								),
								2 => array(
							        'id' => '4',
							        'second_id' => '0',
							        'name' => 'Nepřipravené'
							    ),
							    3 => array(
							        'id' => '4',
							        'second_id' => '1',
							        'name' => 'Připravené'
							    ),
							    4 => array(
							        'id' => '8',
							        'name' => 'Nedokončené'
							    ),
							    5 => array(
							        'id' => '13',
							        'name' => 'Dokončené'
							    ),
							);

		$filtration .= '<div class="col-md-4" style="text-align: right; width: 73%; float: right;">'.filtrationButtons($current, $filter_array).'</div>';


		$current = 'technical';

		$filter_array = array(
				0 => array(
					'id' => '0',
					'name' => '<span class="circle-color red"></span> K zavolání'
				),
				1 => array(
					'id' => '1',
					'name' => '<span class="circle-color yellow"></span> Odeslaný e-mail'
				),
				2 => array(
					'id' => '2',
					'name' => '<span class="circle-color orange"></span> V řešení'
				),
				3 => array(
					'id' => '3',
					'name' => '<span class="circle-color green"></span> Komplet'
				),
			);

		$filtration .= '<div class="col-md-4" style="text-align: right; width: 60%; float: right; margin-top: 6px;">'.filtrationButtons($current, $filter_array).'</div>';

	// CLIENTS FILTRATION
	}else{

		$current = 'year';

        $filter_array = [];
        $filter_array[] = [
            'id' => '0000',
            'name' => 'Bez roku'
        ];
        $current_year = date('Y');
        $range = range('2014', $current_year);
        foreach($range as $year){
            $filter_array[] = [
                'id' => $year,
                'name' => $year,
            ];
        }

		$filtration .= '<div class="col-md-4" style="text-align: right; width: 39%; float: right; margin-top: 6px;">'.filtrationButtons($current, $filter_array).'</div>';

	}

	return $filtration;

}

function filtrationButtons($current, $filter_array){


		global $filters;
		global $_REQUEST;

		global $mysqli;

		$route = demand_filtration_route($current, $filters, $_REQUEST);

		global $$current;

		global $category;

		if($current == 'status' && ($$current == '4' || $$current == '8' || $$current == '13' || $$current == '15')){

			if(!isset($$current) || $$current == '4' && !isset($_REQUEST['realization'])){ $button_type = 'btn-primary'; }else{ $button_type = 'btn-white'; }


			$filtration = '<div class="btn-group"><a href="?'.$current.'=4'.$route.'"" style="padding: 5px 11px !important;" class="btn '.$button_type.'">Vše (připravené + nepřipravené)</a>';

		}elseif($current == 'customer'){

			if(!isset($$current) || $$current == 'all'){ $button_type = 'btn-primary'; }else{ $button_type = 'btn-white'; }

            $filtered_route = $route;
            $filtered_route = str_replace("&subcat=hottub","",$filtered_route);
            $filtered_route = str_replace("&subcat=swimspa","",$filtered_route);
            $filtered_route = str_replace("&subcat=sauna","",$filtered_route);

            $filtration = '<div class="btn-group"><a href="?'.$current.'=all'.$filtered_route.'"" style="padding: 5px 11px !important;" class="btn '.$button_type.'">Vše</a>';

		}else{

			if(!isset($$current) || $$current == 'all'){ $button_type = 'btn-primary'; }else{ $button_type = 'btn-white'; }

			$filtration = '<div class="btn-group"><a href="?'.$current.'=all'.$route.'"" style="padding: 5px 11px !important;" class="btn '.$button_type.'">Vše</a>';

		}



		foreach($filter_array as $filter){ 

			if($current == 'status' && ($$current == '4' || $$current == '8' || $$current == '13' || $$current == '15') && (isset($filter['second_id']) && $filter['second_id'] != '')){

				$route_final = '?'.$current.'='.$filter['id'].'&realization='.$filter['second_id'].$route;

				if(isset($$current) && $$current == $filter['id'] && isset($_REQUEST['realization']) && $_REQUEST['realization'] == $filter['second_id']){ $button_type = 'btn-primary'; }else{ $button_type = 'btn-white'; }

            }else{

				$route_final = '?'.$current.'='.$filter['id'].$route;

				if((isset($$current) && $$current == $filter['id'])){ $button_type = 'btn-primary'; }else{ $button_type = 'btn-white'; }

				if($current == 'customer' && !empty($_REQUEST['subcat']) && $_REQUEST['subcat'] != $filter['subcat']){ $button_type = 'btn-white'; }

			}

			if(!empty($filter['subcat'])){

                $route_final .= '&subcat='.$filter['subcat'];

            }

			$count['count'] = '';
			if($current == 'status' && ($$current != '4' && $$current != '8' && $$current != '13' && $$current != '15')  && $_REQUEST['admin_id'] != 'all' && $current != 'contract'){

				$count_query = $mysqli->query("SELECT COUNT(id) as count FROM demands WHERE admin_id = '".$_REQUEST['admin_id']."' AND $current = '".$filter['id']."'")or die($mysqli->error);
				$count = mysqli_fetch_assoc($count_query);

			}elseif($current != 'admin_id' && $current == 'status' && ($$current != '4' && $$current != '8' && $$current != '13' && $$current != '15') ){

				$count_query = $mysqli->query("SELECT COUNT(id) as count FROM demands WHERE $current = '".$filter['id']."'")or die($mysqli->error);
				$count = mysqli_fetch_assoc($count_query);

			}

			$badge_css = 'margin-top: -1px; float: right; margin-right: -3px; margin-left: 7px; font-weight: bold; padding: 4px 7px 5px 6px; margin-bottom: -3px; border-radius: 5px;';

			if($current == 'status' && $filter['id'] == '1'){

				$badge_css .= 'background-color: red; color: #FFF;';

			}elseif($current == 'status' && $filter['id'] == '3'){

				$badge_css .= 'background-color: orange; color: #FFF;';

			}elseif($current == 'status' && $filter['id'] == '2'){

				$badge_css .= 'background-color: #31a900; color: #FFF;';

			}elseif($current == 'status' && $filter['id'] == '12'){

				$badge_css .= 'background-color: blue; color: #FFF;';

			}elseif($current == 'status' && $filter['id'] == '14'){

				$badge_css .= 'background-color: purple; color: #FFF;';

			}else{
				$badge_css .= 'background-color: #667186;';
			}

			$filtration .= '<a href="'.$route_final.'" style="padding: 6px 11px 4px !important; margin-bottom: 4px;" class="btn '.$button_type.'">'.$filter['name'].'<span class="badge" style="'.$badge_css.'">'.$count['count'].'</span></a>';

		}

		$filtration .= '</div>';

		// IF CUSTOMER IS SET
		if($current == 'customer' && isset($$current) && $$current != 'all'){

                if($customer == 1){

                    // BRAND FILTRATION
                    $current = 'brand';

                    $filter_array = array(
                        0 => array(
                            'id' => 'ique',
                            'name' => 'IQue',
                        ),
                        1 => array(
                            'id' => 'lovia',
                            'name' => 'Lovia',
                        ),
                        2 => array(
                            'id' => 'quantum',
                            'name' => 'Quantum',
                        )
                    );

                    $filtration .= '<div class="col-md-6" style="text-align: left; width: 100%; margin: 20px 0; padding: 0;">'.filtrationButtons($current, $filter_array).'</div>';

                }elseif($customer == 0){

                    $current = 'brand';

                    $filter_array = array(
                        0 => array(
                            'id' => 'espoo',
                            'name' => 'Espoo',
                        ),
                        1 => array(
                            'id' => 'domo',
                            'name' => 'Domo',
                        )
                    );

                    $filtration .= '<div class="col-md-6" style="text-align: left; width: 100%; margin: 20px 0; padding: 0;">'.filtrationButtons($current, $filter_array).'</div>';

		        }




				$current = 'category';

				$route = demand_filtration_route($current, $filters, $_REQUEST);

				$query = demand_filtration_query($filters, $_REQUEST);

				if(isset($category)){

					if(isset($customer) && $customer == 3){

					 $product_query = str_replace('AND (d.product = '.$category.' OR d.secondproduct = '.$category.')', '', $query);

					}else{

					 $product_query = str_replace('AND d.product = "'.$category, '"', $query);

					}

				}else{

                    $product_query = str_replace('AND d.product = '.$category, '', $query);

                }

			if((isset($customer) && $customer == 1) || (isset($customer) && $customer == 3)){

				$products_query = $mysqli->query("SELECT * FROM warehouse_products p, demands d WHERE d.product = p.connect_name AND p.customer = 1 $product_query GROUP BY p.id ORDER BY brand")or die($mysqli->error);

				$filtration .= '<hr style="border-top: 1px solid #ebebeb; margin: 8px;">';

				$filtration .= '<div class="btn-group" style="text-align: left;">';

                if(!isset($category)){ $button_type = 'btn-primary'; }else{ $button_type = 'btn-white'; }

                $filtration .= '<a href="?'.$route.'" style="padding: 5px 11px !important; margin-bottom: 4px;" class="btn '.$button_type.'">Vše</a>';
								
				$product_match = false;
				while($product = mysqli_fetch_array($products_query)){

							if(isset($category) && $category == $product['connect_name']){ $button_type = 'btn-primary'; }else{ $button_type = 'btn-white'; } 

							$filtration .= '<a href="?'.$current.'='.$product['connect_name'].$route.'" style="padding: 5px 11px !important; margin-bottom: 4px;" class="btn '.$button_type.'">'.ucfirst($product['fullname']).'</a>';

							if(isset($category) && $category == $product['connect_name']){ $product_match = true; }

				 } 

				$filtration .= '</div>';

			}


			if((isset($customer) && $customer == 0) || (isset($customer) && $customer == 3)){

					$products_query = $mysqli->query("SELECT * FROM warehouse_products p, demands d WHERE (d.product = p.connect_name || d.secondproduct = p.connect_name) AND p.customer = 0 $product_query GROUP BY p.id ORDER BY code")or die($mysqli->error);
			

					$filtration .= '<hr style="border-top: 1px solid #ebebeb; margin: 8px;">'; 
					$filtration .= '<div class="btn-group" style="text-align: left;">';

					while($product = mysqli_fetch_array($products_query)){

						if(isset($category) && $category == $virivka['connect_name']){ $button_type = 'btn-primary'; }else{ $button_type = 'btn-white'; }

						$filtration .= '<a href="?'.$current.'='.$product['connect_name'].$route.'" style="padding: 5px 11px !important; margin-bottom: 4px;" class="btn '.$button_type.'">'.ucfirst($product['fullname']).'</a>'; 

					}

				$filtration .= '</div>';

			}



		}

		return $filtration;

}

include VIEW . '/default/header.php';

$max = getTotal();

if(empty($od)){ $od = 1; }

$perpage = 40;

$s_pocet = ($od - 1) * $perpage;
$pocet_prispevku = $max; 


$demands = getAll($s_pocet, $perpage);

$filtration = demandFiltration();

include VIEW.CURR_CONT.'/filtration.php';

include VIEW.'/default/pagination.php';

include VIEW.CURR_CONT.'/list.php';

include VIEW.'/default/pagination.php';

?>
<br><br>
    <footer class="main">


        &copy; <?= date("Y") ?> <span style=" float:right;"><?php
            $time = microtime();
            $time = explode(' ', $time);
            $time = $time[1] + $time[0];
            $finish = $time;
            $total_time = round(($finish - $start), 4);

            echo 'PHP '.PHP_VERSION.' | Page generated in ' . $total_time . ' seconds.';?></span>

    </footer>
<?php

include VIEW . '/default/footer.php'; ?>