<?php

use Salamek\Zasilkovna as Zasilkovna;

include_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";

$apiKey = 'd977ce48de5a390f';
$apiPassword = 'd977ce48de5a390f08a4e7ad52af5181';


if (!empty($_REQUEST['action']) && $_REQUEST['action'] == 'create_consignment') {

    if (empty($_REQUEST['id'])) {
        die('chybejici id');
    }
    $id = $_REQUEST['id'];

    $parcelCount = $_POST['number_packages'] ?? 1;

    createPackage($id);

    Header("Location:https://www.wellnesstrade.cz/admin/pages/orders/zobrazit-objednavku?id=" . $id . "&success=create_consignment");
    exit;
}



function setBranch($data){

    global $apiKey;

    $branch = new Zasilkovna\Branch($apiKey, new Zasilkovna\Model\BranchStorageSqLite());

    // process the response
    if (!empty($data['shipping_location_id'])) {
        $selectedBranchId = $data['shipping_location_id'];
    } else {

        foreach ($branch->getBranchList() as $singleBranch) {

            if ($singleBranch['name'] == $data['shipping_location']) {

                $selectedBranchId = $singleBranch['id'];
                break;
            }
        }
    }

    if (!empty($selectedBranchId)) {
        return $selectedBranchId;
    } else {
        die('chyba - pobocka neni dostupna - contact admin');
    }

}


function setPackageData($id): Zasilkovna\Model\PacketAttributes
{

    global $mysqli;

    $data_query = $mysqli->query("SELECT * FROM orders WHERE id = '" . $id . "'") or die($mysqli->error);
    $data = mysqli_fetch_assoc($data_query);

    // todo ověřit, jestli opravdu je Zasilkovna, když ne tak redirect + exit;

    $data_address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $data['shipping_id'] . '" WHERE b.id = "' . $data['billing_id'] . '"') or die($mysqli->error);
    $data_address = mysqli_fetch_assoc($data_address_query);


    // set weight on 4 kg
    if ($data['weight'] == 0.00) {
        $data['weight'] = 4.00;
    }


    if (!empty($data['shipping_id'])) {

        $name = $data_address['shipping_name'];
        $surname = $data_address['shipping_surname'];
        $company = $data_address['shipping_company'];

        $street = $data_address['shipping_street'];
        $city = $data_address['shipping_city'];
        $zipcode = $data_address['shipping_zipcode'];

        if ($data_address['shipping_country'] == 'CZ') {

            $country = 'CZE';

        } elseif ($data_address['shipping_country'] == 'SK') {

            $country = 'SVK';

        }

    } else {

        $name = $data_address['billing_name'];
        $surname = $data_address['billing_surname'];
        $company = $data_address['billing_company'];

        $street = $data_address['billing_street'];
        $city = $data_address['billing_city'];
        $zipcode = $data_address['billing_zipcode'];

        if ($data_address['billing_country'] == 'CZ') {

            $country = 'CZE';

        } elseif ($data_address['billing_country'] == 'SK') {

            $country = 'SVK';

        }
    }

    $selectedBranchId = setBranch($data);

    $transporterPackage = new Zasilkovna\Model\PacketAttributes(
        $data['id'], //private string $number;
        $name, //private string $name;
        $surname, //private string $surname;
        round($data['total']), //private float $value;
        setBranch($data), //private int $addressId;
        null, //private ?int $id;
        $company, //private ?string $company;
        $data['customer_email'], //private ?string $email;
        $data['customer_phone'], //private ?string $phone;
        null, //private ?string $currency;
        null, //private ?float $cod;
        $data['weight'], //private ?float $weight;
        ucfirst($data['order_site']) . '.cz', //private ?string $eshop;
        false, //private ?bool $adultContent;
        $street, //private ?string $street;
        '', //private ?string $houseNumber;
        $city, //private ?string $city;
        $zipcode //private ?string $zip;
    );

    // cash on delivery
    if ($data['payment_method'] == 'cod') {
        $transporterPackage->setCod(round($data['total']));
        $transporterPackage->setCurrency($data['order_currency']);
    }

    return $transporterPackage;

}


function generateLabel($packetId, $transporterPackage)
{

    global $apiPassword;
    global $apiKey;

    $api = new Zasilkovna\ApiRest($apiPassword, $apiKey);
    $branch = new Zasilkovna\Branch($apiKey, new Zasilkovna\Model\BranchStorageSqLite());

    if (!empty($packetId->id)) {
        $transporterPackage->setId($packetId->id);
        $packetId = $packetId->id;
    } else {
        die('missing packetId');
    }

    $label = new Zasilkovna\Label($api, $branch);

    $pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Wellness Trade');
    $pdf->SetTitle(sprintf('Zasilkovna Label ' . $packetId));
    $pdf->SetSubject(sprintf('Zasilkovna Label ' . $packetId));
    $pdf->SetKeywords('Zasilkovna');
    $pdf->SetFont('freeserif', '', 14);
    $pdf->setFontSubsetting(true);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();

    try {
        $pdf = $label->generateLabelQuarter($pdf, $transporterPackage);
        $pdf->Output($_SERVER['DOCUMENT_ROOT'] . '/admin/data/consignments/' . $packetId . '.pdf', 'F');

//        echo "https://www.wellnesstrade.cz/admin/data/consignments/".$packetId.".pdf";
    } catch (SoapFault $e) {
        die($e);
    }


}

function createPackage($id)
{

    global $apiPassword;
    global $apiKey;
    global $mysqli;

    $api = new Zasilkovna\ApiRest($apiPassword, $apiKey);

    $transporterPackage = setPackageData($id);

    try {
        $packetId = $api->createPacket($transporterPackage);
    } catch (SoapFault $e) {
        die($e);
    }


    $mysqli->query("UPDATE orders SET consignment_id = '" . $packetId->id . "' WHERE id = '" . $id . "'") or die($mysqli->error);

    generateLabel($packetId, $transporterPackage);

}


/*
 * TODO NOT WORKING

if(!empty($_REQUEST['action']) && $_REQUEST['action'] == 'regenerate_label'){

    $packet_data = $mysqli->query("SELECT consignment_id FROM orders WHERE id = '".$id."'")or die($mysqli->error);
    $packet = mysqli_fetch_assoc($packet_data);

    $transporterPackage->setId($packet['consignment_id']);

    $label = new Zasilkovna\Label($api, $branch);

    $pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Wellness Trade');
    $pdf->SetTitle(sprintf('Zasilkovna Label '.$packet['consignment_id']));
    $pdf->SetSubject(sprintf('Zasilkovna Label '.$packet['consignment_id']));
    $pdf->SetKeywords('Zasilkovna');
    $pdf->SetFont('freeserif', '', 14);
    $pdf->setFontSubsetting(true);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();

    try {
        $pdf = $label->generateLabelQuarter($pdf, $transporterPackage);
        $pdf->Output($_SERVER['DOCUMENT_ROOT'] . '/admin/data/consignments/'.$packet['consignment_id'].'.pdf', 'F');

        echo "https://www.wellnesstrade.cz/admin/data/consignments/".$packet['consignment_id'].".pdf";
    }
    catch (SoapFault $e){
        die($e);
    }

}

*/

//if(!empty($_REQUEST['action']) && $_REQUEST['action'] == 'get_consignment'){
//
//
//    // todo single info? edit? view? WHY?
//    $consignmentId = $data['consignment_id'];
////    $consignmentId = 26817340;
//
//    $trackingResponse = $api->getTracking($consignmentId);
//
//    print_r($trackingResponse);
//
//}

// todo nejde v zásilkovně?
//if(!empty($_REQUEST['action']) && $_REQUEST['action'] == 'storno'){
//
//    $consignmentId = $data['consignment_id'];
//
//    $trackingResponse = $api->getTracking($consignmentId);
//
//    $ch = curl_init('https://api.ulozenka.cz/v3/consignments/'.$consignmentId);
//
//    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
//    curl_setopt($ch, CURLOPT_HEADER, FALSE);
//
//    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
//    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//            'X-Shop:'.$shopId,
//            'X-Key:' .$apiKey)
//    );
//
//    $response = curl_exec($ch);
//    curl_close($ch);
//
//    $decoded = json_decode($response);
//
////    if($decoded->code == 400){ die('zasilka jiz byla stornovana') ;
////    }elseif($decoded->code == 201){
//
//    $mysqli->query("UPDATE orders SET consignment_id = '0', order_tracking_number = '' WHERE id = '".$id."'")or die($mysqli->error);
////    }
//
//    Header("Location:https://www.wellnesstrade.cz/admin/pages/orders/zobrazit-objednavku?id=" . $id . "&success=storno");
//    exit;
//}
