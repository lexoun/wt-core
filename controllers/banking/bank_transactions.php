<?php

date_default_timezone_set('Europe/Prague');

require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/config/configPublic.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/controllers/banking/fio-api.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$fio_demands = new FioApi('N9WIjfSTELueOw8JiRRSXsXVmHkgAISqiUGWG2z9fnzbl6ISSqU9nVXepcXliguF');
$fio_demands->reset(date('Y-m-d', strtotime('-30 days')));
$transactions_demands = $fio_demands->getData();
print_r($fio_demands);

foreach($transactions_demands['transakce'] as $transaction){

    if($transaction['castka'] > 0){

        $popis = $mysqli->real_escape_string((string)$transaction['popis']);
        $popis_interni = $mysqli->real_escape_string((string)$transaction['popis_interni']);

        $value = str_replace(",", ".", $transaction['castka']);

        $encode = $mysqli->real_escape_string(json_encode($transaction));

        $check_duplicity = $mysqli->query("SELECT * FROM bank_transactions WHERE account = 'demand' AND vs = '" . $transaction['vs'] . "' AND ident = '" . $transaction['ident'] . "'")or die($mysqli->error);

        if(mysqli_num_rows($check_duplicity) > 0){

            $mysqli->query("UPDATE bank_transactions SET value = '".$value."', import_log = '".$encode."' WHERE account = 'demand' AND vs = '" . $transaction['vs'] . "' AND ident = '" . $transaction['ident'] . "'")or die($mysqli->error);

        }else{

            // insert the record
            $mysqli->query("INSERT IGNORE INTO bank_transactions (account, vs, value, date, currency, description, description_inside, ident, typ, import_log)
        VALUES (
        'demand',
        '" . $transaction['vs'] . "',
        '" . $value . "',
        '" . $transaction['datum'] . "',
        '" . $transaction['mena'] . "',
        '" . $popis . "',
        '" . $popis_interni . "',
        '" . $transaction['ident'] . "',
        '" . $transaction['typ'] . "',
        '" . $encode . "')")or die($mysqli->error);

            // get total price
            $price_query = $mysqli->query("SELECT i.id, i.total_price, i.currency, i.status, d.email, d.phone, i.demand_id, d.product, d.customer FROM demands_advance_invoices i LEFT JOIN demands d ON d.id = i.demand_id WHERE i.id = '" . $transaction['vs'] . "'") or die($mysqli->error);

            if(mysqli_num_rows($price_query) > 0){

                $price = mysqli_fetch_assoc($price_query);

                // get total paid
                $bank_sum_query = $mysqli->query("SELECT SUM(value) as total FROM bank_transactions WHERE account = 'demand' AND (vs = '".$price['id']."' OR manual_assign = '".$price['id']."')")or die($mysqli->error);
                $bank_sum = mysqli_fetch_assoc($bank_sum_query);


                // označení faktury jako zaplacené
                if($bank_sum['total'] == $price['total_price'] || (($price['total_price'] - $bank_sum['total']) < 1)){

                    $mysqli->query("UPDATE demands_advance_invoices SET paid = 1 WHERE id = '" . $price['id'] . "'") or die($mysqli->error);

                }

                // send mail if equal
                $getclient['id'] = $price['demand_id'];

                $total_price = $value;
                $currency = currency($price['currency']);
                $email = $price['email'];

                // zaslání emailu po přijeté platbě nehledě na to, jestli byla faktura uhrazena v plné výši, částečně nebo přeplněná
                if($price['status'] == 1){

                    include($_SERVER['DOCUMENT_ROOT'] . '/admin/controllers/mails/client/firstPayment.php');

                }else{

                    include($_SERVER['DOCUMENT_ROOT'] . '/admin/controllers/mails/client/recievedPayment.php');

                }

            }

        }


        $demands_query = $mysqli->query("SELECT demand_id FROM demands_advance_invoices WHERE id = '".$transaction['vs']."'") or die($mysqli->error);
        while ($demand = mysqli_fetch_array($demands_query)) {

            $mysqli->query("UPDATE demands SET contract = 3 WHERE id = '" . $demand['demand_id'] . "'") or die($mysqli->error);

        }

    }


}


$fio = new FioApi('HiSSGgcpk13WWP3FbsceDcnkMSuCzolc4iEJoHkdZHRlBtPX9m0PVlQaBVaYrb8y');
$fio->reset(date('Y-m-d', strtotime('-30 days')));
$transactions = $fio->getData();

foreach($transactions['transakce'] as $transaction){

    if($transaction['castka'] > 0){

        $popis = $mysqli->real_escape_string((string)$transaction['popis']);
        $popis_interni = $mysqli->real_escape_string((string)$transaction['popis_interni']);

        $value = str_replace(",", ".", $transaction['castka']);

        $encode = $mysqli->real_escape_string(json_encode($transaction));

        $check_duplicity = $mysqli->query("SELECT * FROM bank_transactions WHERE account = 'order' AND vs = '" . $transaction['vs'] . "' AND ident = '" . $transaction['ident'] . "'")or die($mysqli->error);

        if(mysqli_num_rows($check_duplicity) > 0){

            $mysqli->query("UPDATE bank_transactions SET value = '".$value."', import_log = '".$encode."' WHERE account = 'order' AND vs = '" . $transaction['vs'] . "' AND ident = '" . $transaction['ident'] . "'")or die($mysqli->error);

        }else {

                $mysqli->query("INSERT IGNORE INTO bank_transactions (account, vs, value, date, currency, description, description_inside, ident, typ)
            VALUES (
            'order',
            '" . $transaction['vs'] . "',
            '" . $value . "',
            '" . $transaction['datum'] . "',
            '" . $transaction['mena'] . "',
            '" . $popis . "',
            '" . $popis_interni . "',
            '" . $transaction['ident'] . "',
            '" . $transaction['typ'] . "')") or die($mysqli->error);

        }


        $paid = 0;
        $orders_query = $mysqli->query("SELECT id, total FROM orders WHERE paid = 0 AND reference_number = '".$transaction['vs']."'") or die($mysqli->error);

        if(mysqli_num_rows($orders_query) > 0){

            $order = mysqli_fetch_array($orders_query);

            if($order['total'] == $value){

                $paid = 1;

            }elseif($order['total'] != $value){

                $paid = 2;

            }

            $mysqli->query("UPDATE orders SET paid = '".$paid."', paid_value = '" . $value . "', payment_date = '" . $transaction['datum'] . "' WHERE id = '" . $order['id'] . "'");


        }


        $paid = 0;
        $service_query = $mysqli->query("SELECT id, price as total FROM services WHERE paid = 0 AND reference_number = '".$transaction['vs']."'") or die($mysqli->error);

        if(mysqli_num_rows($service_query) > 0){

            $service = mysqli_fetch_array($service_query);

            if($service['total'] == $value){

                $paid = 1;

            }elseif($service['total'] != $value){

                $paid = 2;

            }

            $mysqli->query("UPDATE services SET paid = '".$paid."', paid_value = '" . $value . "', payment_date = '" . $transaction['datum'] . "' WHERE id = '" . $service['id'] . "'");


        }

    }

}