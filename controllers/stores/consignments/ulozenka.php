<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";


// 12424 spamall key: DtDerPdgSe4pAZmBGfbA0Iq5S
// 12880 saunahouse key: ndH3bBAjtQUNuAYihHf11maIK
// 14685 spahouse key: UbxsFKiKSOSRb6026X4LX2r4E


if(!empty($_REQUEST['action']) && $_REQUEST['action'] == 'get_list'){


    if(!empty($_REQUEST['site']) && $_REQUEST['site'] == 'spamall'){

        $shopId = 12424;
        $apiKey = 'DtDerPdgSe4pAZmBGfbA0Iq5S';

    }elseif(!empty($_REQUEST['site']) && $_REQUEST['site'] == 'saunahouse'){

        $shopId = 14685;
        $apiKey = 'UbxsFKiKSOSRb6026X4LX2r4E';

    }elseif(!empty($_REQUEST['site']) && $_REQUEST['site'] == 'spahouse'){

        $shopId = 12880;
        $apiKey = 'ndH3bBAjtQUNuAYihHf11maIK';


    }else{
        die('neurcen eshop');
        exit;
    }


    $ch = curl_init('https://api.ulozenka.cz/v3/consignments');
    curl_setopt($ch, CURLOPT_CAINFO, "/global/applications/tools/cacert.pem");
    curl_setopt($ch, CURLOPT_CAPATH, "/global/applications/tools/");
//    curl_setopt($ch, CURLOPT_URL, "https://api.ulozenka.cz/v3/consignments
//");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);

//    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'X-Shop:'.$shopId,
            'X-Key:' .$apiKey)
    );

    $response = curl_exec($ch);
    curl_close($ch);

    $decoded = json_decode($response);
    foreach($decoded->data as $res){



        // update consignment_id and tracking number

//        $order_query = $mysqli->query("SELECT id, old_id, consignment_id FROM orders WHERE id = '".$res->order_number."' OR old_id = '".$res->order_number."'")or die($mysqli->error);
//
//        if(mysqli_num_rows($order_query) == 1){
//
//            $order = mysqli_fetch_assoc($order_query);
//
//            if($order['consignment_id'] == 0){
//
//                $mysqli->query("UPDATE orders SET consignment_id = '".$res->id."', order_tracking_number = '".$res->parcel_number."' WHERE  id = '".$res->order_number."' OR old_id = '".$res->order_number."'")or die($mysqli->error);
//    //            exit;
//                echo $res->order_number.' - ANO<br>';
//
//            }
//
//        }else{
//
//            echo $res->order_number.' - NE<br>';
//
//        }



        // branch id mass
//        $mysqli->query("UPDATE orders SET shipping_location_id = '".$res->destination_branch_id."' WHERE  id = '".$res->order_number."' OR old_id = '".$res->order_number."'")or die($mysqli->error);


        print_r($res);

    }

    exit;
}



if(empty($_REQUEST['id'])){ die('chybejici id'); }


$id = $_REQUEST['id'];

$parcelCount = $_POST['number_packages'] ?? 1;

$data_query = $mysqli->query("SELECT * FROM orders WHERE id = '".$id."'")or die($mysqli->error);
$data = mysqli_fetch_assoc($data_query);


// todo ověřit, jestli opravdu je Uloženka, když ne tak redirect + exit;

$data_address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $data['shipping_id'] . '" WHERE b.id = "' . $data['billing_id'] . '"') or die($mysqli->error);
$data_address = mysqli_fetch_assoc($data_address_query);


if($data['order_site'] == 'spamall'){

    $shopId = 12424;
    $apiKey = 'DtDerPdgSe4pAZmBGfbA0Iq5S';

}elseif($data['order_site'] == 'spahouse'){

    $shopId = 14685;
    $apiKey = 'UbxsFKiKSOSRb6026X4LX2r4E';

// WT admin + saunahouse
}else{

    $shopId = 12880;
    $apiKey = 'ndH3bBAjtQUNuAYihHf11maIK';

}





$endpoint = \UlozenkaLib\APIv3\Enum\Endpoint::PRODUCTION;
$api = new \UlozenkaLib\APIv3\Api($endpoint, $shopId, $apiKey);


if(!empty($_REQUEST['action']) && $_REQUEST['action'] == 'create_consignment'){



    // před vytvořením nové zásilky zkontrolovat, jestli náhodou zásilku někdo nevytvořil manuálně - když ano, tak jí přiřadit
    $ch = curl_init('https://api.ulozenka.cz/v3/consignments');
    curl_setopt($ch, CURLOPT_CAINFO, "/global/applications/tools/cacert.pem");
    curl_setopt($ch, CURLOPT_CAPATH, "/global/applications/tools/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'X-Shop:'.$shopId,
            'X-Key:' .$apiKey)
    );

    $response = curl_exec($ch);
    curl_close($ch);

    $decoded = json_decode($response);

    foreach($decoded->data as $res){

        if($res->order_number == $id){

            $mysqli->query("UPDATE orders SET consignment_id = '".$res->id."', order_tracking_number = '".$res->parcel_number."' WHERE id = '".$id."'")or die($mysqli->error);

            Header("Location:https://www.wellnesstrade.cz/admin/pages/orders/zobrazit-objednavku?id=" . $id . "&success=match_consignment");
            exit;
        }

    }




    if(empty($data['shipping_location'])){ die('zadna zvolena vydejna!!'); }

    // + todo ověřit, jestli má zvolenou ?pobočku


    if($order['order_shipping_method'] == 'ulozenka_cr' || $order['order_shipping_method'] == 'ulozenka_sk'){

        $transportServiceId = \UlozenkaLib\APIv3\Enum\TransportService::ULOZENKA;

    }elseif($order['order_shipping_method'] == 'balik_na_postu_cr'){

        $transportServiceId = \UlozenkaLib\APIv3\Enum\TransportService::CPOST_BALIK_NA_POSTU;

    }



    // get the destination branches for transport service Ulozenka with respect to settings of the shop with id $shopId
    $getTransportServiceBranchesResponse = $api->getTransportServiceBranches($transportServiceId, $shopId, true);


    // process the response
    if ($getTransportServiceBranchesResponse->isSuccess()) {
        foreach ($getTransportServiceBranchesResponse->getDestinationBranches() as $branch) {
    //        echo $branch->getId() . ' ' . $branch->getName().'<br>';

            if($branch->getName() == $data['shipping_location']){

                $selectedBranchId = $branch->getId();
                break;
            }
        }
    } else {
        $errors = $getTransportServiceBranchesResponse->getErrors();
        foreach ($errors as $error) {
            echo $error->getCode() . ' ' . $error->getDescription() . PHP_EOL;
        }
    }

    //echo $selectedBranchId;







    // create receiver of the consignment
    $receiver = new UlozenkaLib\APIv3\Model\Consignment\Receiver();


    if(!empty($data['shipping_id'])){

        $name = $data_address['shipping_name'];
        $surname = $data_address['shipping_surname'];
        $company = $data_address['shipping_company'];

        $street = $data_address['shipping_street'];
        $city = $data_address['shipping_city'];
        $zipcode = $data_address['shipping_zipcode'];


        if($data_address['shipping_country'] == 'CZ'){

            $country = 'CZE';

        }elseif($data_address['shipping_country'] == 'SK'){

            $country = 'SVK';

        }

    }else{

        $name = $data_address['billing_name'];
        $surname = $data_address['billing_surname'];
        $company = $data_address['billing_company'];

        $street = $data_address['billing_street'];
        $city = $data_address['billing_city'];
        $zipcode = $data_address['billing_zipcode'];


        if($data_address['billing_country'] == 'CZ'){

            $country = 'CZE';

        }elseif($data_address['billing_country'] == 'SK'){

            $country = 'SVK';

        }

    }

    $receiver->setName($name);
    $receiver->setSurname($surname);
    $receiver->setCompany($company);

    $receiver->setPhone($data['customer_phone']);
    $receiver->setEmail($data['customer_email']);


    // create adress of the reciever
    $address = new UlozenkaLib\APIv3\Model\Consignment\Address($street, $city, $zipcode);
    $receiver->setAddress($address);

    // my consignment identification
    $orderNumber = $data['id'];

    // create a consignment request
    $consignmentRequest = new UlozenkaLib\APIv3\Resource\Consignments\Request\ConsignmentRequest($receiver, $orderNumber, $parcelCount, $transportServiceId);
    $consignmentRequest->setDestinationBranchId($selectedBranchId);

    $consignmentRequest->setDestinationCountry($country);
    $consignmentRequest->setVariable($data['id']);


    // dobírka
    if($data['payment_method'] == 'cod'){

        $consignmentRequest->setCashOnDelivery($data['total']);
        $consignmentRequest->setCurrency($data['order_currency']);

    }

    // todo weight?
    //$consignmentRequest->setWeight(3.21);
    //$consignmentRequest->setWeight(3.21);


    // todo pobočka pro podání ... asi nastavit defaultně tu na Hradčanské?
    //$consignmentRequest->registerBranchId();


    // pojištění?
    //$consignmentRequest->setInsurance(10000);

    // k čemu?
    //$consignmentRequest->setStatedPrice(10);


    // jiný dopravce, než uloženka, ale řešeno přes uloženku
    //$consignmentRequest->setTransportServiceId(10);


    // povolit kartu kartou? proč ne? jestli není default jako true, tak nastavit true
    //$consignmentRequest->setAllowCardPayment(10);


    // todo setNote
    //$consignmentRequest->setNote('nějakej string... jako třeba doručit do obchodu s ledničkama nevím');




    //
    // send the request and process the response
    $createConsignmentResponse = $api->createConsignment($consignmentRequest);
    if ($createConsignmentResponse->isSuccess()) {
        $consignment = $createConsignmentResponse->getConsignment();
        $insertedId = $createConsignmentResponse->getConsignment()->getId();

    } else {
        $errors = $createConsignmentResponse->getErrors();
        foreach ($errors as $error) {
            echo $error->getCode() . ' ' . $error->getDescription() . PHP_EOL;
        }
    }


//    print_r($consignment);
//    echo $insertedId;

    $mysqli->query("UPDATE orders SET consignment_id = '".$insertedId."' WHERE id = '".$id."'")or die($mysqli->error);


    Header("Location:https://www.wellnesstrade.cz/admin/pages/orders/zobrazit-objednavku?id=" . $id . "&success=create_consignment");
    exit;
}



if(!empty($_REQUEST['action']) && $_REQUEST['action'] == 'get_consignment'){


    // todo single info? edit? view? WHY?
    $consignmentId = $data['consignment_id'];
//    $consignmentId = 26817340;

    $trackingResponse = $api->getTracking($consignmentId);

    print_r($trackingResponse);

}



if(!empty($_REQUEST['action']) && $_REQUEST['action'] == 'storno'){

    $consignmentId = $data['consignment_id'];

    $trackingResponse = $api->getTracking($consignmentId);

    $ch = curl_init('https://api.ulozenka.cz/v3/consignments/'.$consignmentId);
    curl_setopt($ch, CURLOPT_CAINFO, "/global/applications/tools/cacert.pem");
    curl_setopt($ch, CURLOPT_CAPATH, "/global/applications/tools/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'X-Shop:'.$shopId,
            'X-Key:' .$apiKey)
    );

    $response = curl_exec($ch);
    curl_close($ch);

    $decoded = json_decode($response);

//    if($decoded->code == 400){ die('zasilka jiz byla stornovana') ;
//    }elseif($decoded->code == 201){

        $mysqli->query("UPDATE orders SET consignment_id = '0', order_tracking_number = '' WHERE id = '".$id."'")or die($mysqli->error);
//    }

    Header("Location:https://www.wellnesstrade.cz/admin/pages/orders/zobrazit-objednavku?id=" . $id . "&success=storno");
    exit;
}



if(!empty($_REQUEST['action']) && $_REQUEST['action'] == 'create_label'){

    // todo $_REQUEST['consignment_id'];
    $consignmentId = $data['consignment_id'];
    // array of consignment id or partner_consignment_id values
    $consignments = [$consignmentId];

    // send the request
    $labelsResponse = $api->getLabels($consignments, \UlozenkaLib\APIv3\Enum\Attributes\LabelAttr::TYPE_PDF, $firstPosition = 1, $labelsPerPage = 1, $shopId, $apiKey);


    // process the response
    if ($labelsResponse->isSuccess()) {
        $pdf = fopen ($_SERVER['DOCUMENT_ROOT'] . '/admin/data/consignments/'.$consignmentId.'.pdf','w');
        fwrite ($pdf, $labelsResponse->getLabelsString());
        fclose ($pdf);
    } else {
        $errors = $labelsResponse->getErrors();
        foreach ($errors as $error) {
            echo $error->getCode() . ' ' . $error->getDescription() . PHP_EOL;
        }
    }

    Header("Location:https://www.wellnesstrade.cz/admin/data/consignments/".$consignmentId.".pdf");
    exit;
}
