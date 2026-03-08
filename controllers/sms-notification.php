<?php
if (!empty($_REQUEST['secretcode']) && $_REQUEST['secretcode'] == "lYspnYd2mYTJm6") {

    include $_SERVER['DOCUMENT_ROOT'] . '/admin/config/configPublic.php';
    include INCLUDES . "/functions.php";

    $tommorow = Date('Y-m-d', strtotime("+1 days"));

    $follow_ups = $mysqli->query("SELECT *, DATE_FORMAT(date_time, '%d. %m.') as dateformated, DATE_FORMAT(date_time, '%H:%i') as hoursmins FROM demands_mails_history WHERE date(date_time) = '" . $tommorow . "' AND (type = 'Zkouška vířivky' OR type = 'Návštěva' OR type = 'Návštěva - plánovaná') AND state != 'done'") or die($mysqli->error);

    $i = 0;
    while ($follow_up = mysqli_fetch_assoc($follow_ups)) {

        $getclientquery = $mysqli->query('SELECT *, d.customer as customer, d.id as id, DATE_FORMAT(d.date, "%d. %m. %Y") as dateformated, DATE_FORMAT(d.realization, "%d. %m. %Y") as realizationformated, DATE_FORMAT(d.realtodate, "%d. %m. %Y") as realtodateformat, d.active as activated 
        FROM demands d 
        LEFT JOIN warehouse_products p ON p.connect_name = d.product 
        LEFT JOIN shops_locations l ON l.id = d.showroom
    WHERE d.id="' . $follow_up['demand_id'] . '"') or die($mysqli->error);
        $getclient = mysqli_fetch_assoc($getclientquery);

        $billing_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $getclient['shipping_id'] . '" WHERE b.id = "' . $getclient['billing_id'] . '"') or die($mysqli->error);
        $billing = mysqli_fetch_assoc($billing_query);

        if (strlen($billing['billing_phone']) == 9) {

            $msg = new \jakubenglicky\SmsManager\Message\Message();

            if (!empty($getclient['showroom'])) {
                $address = remove_diacritics($getclient['sms_address']);
            } else {
                exit;
            }

            $phone = phone_prefix($billing['billing_phone_prefix']) . $billing['billing_phone'];

            if($follow_up['hoursmins'] != '00:00'){
                $visitation_info = $follow_up['dateformated'] . ' v čase ' . $follow_up['hoursmins'];
            }else{
                $visitation_info = $follow_up['dateformated'];
            }

            $msg->setTo([$phone]);
            $msg->setBody('Dobry den, radi bychom Vam pripomneli termin navstevy Showroomu dne ' . $visitation_info . '. na adrese ' . $address . '. Tesime se na Vas. Tym SPAHOUSE.');

            $smsClient = new \jakubenglicky\SmsManager\Http\Client('beef7e2706a4510897ece8550f4edd51bea3f527');

            try {

                $smsClient->send($msg);

            } catch (Exception $e) {
                echo 'Caught exception: ', $e->getMessage(), "\n";
            }

        }
        $i++;
    }
    echo $i;

}





//