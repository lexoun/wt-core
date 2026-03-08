<?php

date_default_timezone_set('Europe/Prague');

function decodeParams($data) {
    $encodedParams = explode('&', $data);
    $params = array();
    foreach ($encodedParams as $encodedParam) {
        $encodedPair = explode('=', $encodedParam);
        $paramName = urlencode($encodedPair[0]);
        $paramValue = (count($encodedPair) == 2 ? urldecode($encodedPair[1]) : '');
        $params[$paramName] = $paramValue;
    }
    return $params;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/config/configPublic.php';

/* this is only payments made via CARD terminal */

if(empty($_REQUEST['date'])){
    $date = date('Y-m-d');
}else{
    $date = $_REQUEST['date'];
}

$url = 'https://payments.comgate.cz/v1.0/transferList?merchant=134117&secret=Jz7ygn2gCjPwx0wAP88G&date='.$date;
$data = file_get_contents($url);
$cleanData = json_decode($data, true);

foreach($cleanData as $singleData){

    $singleUrl = 'https://payments.comgate.cz/v1.0/singleTransfer?merchant=134117&secret=Jz7ygn2gCjPwx0wAP88G&transferId='.$singleData['transferId'];

    $getSingle = file_get_contents($singleUrl);
    $single = json_decode($getSingle, true);

    foreach($single as $sin){

        if(!empty($sin['ID ComGate'])){

            $payment_method = 'CARD_CZ_BS';

            $encoded = json_encode($sin);

            // insert record
            $mysqli->query("INSERT IGNORE INTO transactions_comgate (merchant, id, status, datetime, client_mail, product_description, value, currency, reference_number, payment_method, import_log)
            VALUES (
            'terminal',
            '" . $sin['ID ComGate'] . "',
            'PAID',
            '" . $sin['Datum zaplacení'] . "',
            '" . $sin['E-mail plátce'] . "',
            '" . $sin['Popis'] . "',
            '" . $sin['Potvrzená částka'] . "',
            '" . $sin['Měna']. "',
            '" . $sin['Variabilní symbol platby'] . "',
            '" . $payment_method . "',
            '" . $encoded . "')")or die($mysqli->error);

        }

    }

}


$shops_query = $mysqli->query("SELECT * FROM shops")or die($mysqli->error);

while($shop = mysqli_fetch_assoc($shops_query)){


    /* zíslání transactionId z WC */

    $shop_db = new mysqli($shop['db_host'], $shop['db_username'], $shop['db_password'], $shop['db_name']);
    if ($mysqli->connect_errno) {
        echo "Error: Unable to connect to MySQL." . PHP_EOL;
        echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
        echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
        exit;
    }
    mysqli_set_charset($shop_db, "utf8");
    $shop_db->query("SET NAMES 'utf8'");


    $site = $shop['slug'];

    // ID 600374
    $get_order = $mysqli->query("SELECT id, reference_number FROM orders WHERE order_site = '".$site."' AND (payment_method = 'agmobindercardall' OR payment_method = 'agmobinderbank')
    AND (order_status != 3 AND order_status != 4)
    ")or die($mysqli->error);
    while($order = mysqli_fetch_assoc($get_order)){

        //print_r($order);

        //7450
        $select_query = $shop_db->query("SELECT gwOrderNumber FROM unimodul_transactions WHERE shopOrderNumber = '".$order['id']."'")or die($shop_db->error);

        if(mysqli_num_rows($select_query) > 0){

            $select = mysqli_fetch_assoc($select_query);

            $mysqli->query("UPDATE orders SET transaction_id = '".$select['gwOrderNumber']."' WHERE id = '".$order['id']."'")or die($mysqli->error);

        }

    }



    /* připojení do comgate */
    // while orders with transaction_id
    $get_order = $mysqli->query("SELECT transaction_id, id FROM orders WHERE order_site = '".$site."' AND transaction_id != '' AND (payment_method = 'agmobindercardall' OR payment_method = 'agmobinderbank') 
    AND (order_status != 3 AND order_status != 4)
    ")or die($mysqli->error);
    while($order = mysqli_fetch_assoc($get_order)) {

        echo $order['id'];

        $params = array(
            "transaction" => array("id" => $order['transaction_id'])
        );

        $statusGet = file_get_contents('https://payments.comgate.cz/v1.0/status?merchant='.$shop['comgate_login'].'&secret='.$shop['comgate_password'].'&transId='.$order['transaction_id']);

        $status = decodeParams($statusGet);

        //print_r($status);

        if($status['code'] != 0){
            echo $status['message']. '<br>';
            continue;
        }

        //print_r($status);

        $priceFormated = substr($status['price'], 0, -2) . ',' . substr($status['price'], -2);

        $check = $mysqli->query("SELECT * FROM transactions_comgate WHERE merchant =  '".$site."' AND id = '" . $status['transId'] . "'")or die($mysqli->error);
        if(mysqli_num_rows($check) > 0){

            $checkResult = mysqli_fetch_assoc($check);

            echo 'already added<br>';

            if($checkResult['status'] == 'PAID'){ echo ' - already paid<br><br>'; continue; }


            $mysqli->query("UPDATE transactions_comgate SET 
                target_id = '".$order['id']."', 
                target_type = 'order_service', 
                status = '" . $status['status'] . "', 
                datetime = now(), 
                import_log =  '" . $statusGet . "' 
            WHERE 
                merchant =  '".$site."' AND 
                id = '" . $status['transId'] . "'
            ")or die($mysqli->error);

        }else{

            echo 'new record<br>';


            // insert record
            $mysqli->query("INSERT IGNORE INTO transactions_comgate (
                 merchant, 
                 id, 
                 status, 
                 datetime, 
                 client_mail, 
                 product_description, 
                 value, 
                 currency, 
                 reference_number, 
                 payment_method, 
                 import_log, 
                 target_id, 
                 target_type
            ) VALUES (
                '".$site."',
                '" . $status['transId'] . "',
                '" . $status['status'] . "',
                now(),
                '" . $status['email'] . "',
                '" . $status['label'] . ' ' . $status['payer_name']  . ' ' . $status['payer_acc'] . "',
                '" . $priceFormated . "',
                '" . $status['curr']. "',
                '" . $status['vs'] . "',
                '" . $status['method'] . "',
                '" . $statusGet . "',
                '" . $order['id'] . "',
                'order_service'
            )")or die($mysqli->error);


        }
    }
}

