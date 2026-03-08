<?php

use FilipSedivy\EET\Certificate;
use FilipSedivy\EET\Dispatcher;
use FilipSedivy\EET\Receipt;
use FilipSedivy\EET\Utils\UUID;

$certificate = new Certificate(__DIR__ . '/1245995017.p12', 'Wellnesstrade2510?');
$dispatcher = new Dispatcher($certificate);
$dispatcher->setProductionService();

if(!empty($repeat) && $repeat){

    $receipt = json_decode($invoice['receipt']);

    $receipt->uuid_zpravy = UUID::v4();
    $receipt->prvni_zaslani = false;
    $receipt->bkp = $invoice['bkp'];
    $receipt->pkp = $invoice['pkp'];

    $dispatcher->send($receipt);

    $fik = $dispatcher->getFik();

    $mysqli->query("UPDATE orders_invoices SET fik = '".$fik."' WHERE id = '".$invoice['id']."'")or die($mysqli->error);

}else {

    $receipt = new Receipt;
    $receipt->uuid_zpravy = UUID::v4();
    $receipt->id_provoz = '11';
    $receipt->id_pokl = 'AWT';
    $receipt->dic_popl = 'CZ29154871';

    $receipt->porad_cis = $send_id;
    $receipt->dat_trzby = new DateTime;

    $receipt->celk_trzba = (float)$total['single'];
    $receipt->zakl_nepodl_dph = (float)$total['rounded'];
    $receipt->zakl_dan1 = (float)$total['without_vat'];
    $receipt->dan1 = (float)$total['vat'];

    try {

        $dispatcher->send($receipt);

        $fik = $dispatcher->getFik();
        $pkp = $dispatcher->getPkp();
        $bkp = $dispatcher->getBkp();

    } catch (FilipSedivy\EET\Exceptions\EET\ClientException $exception) {

        $pkp = $dispatcher->getPkp();
        $bkp = $dispatcher->getBkp();


    } catch (FilipSedivy\EET\Exceptions\EET\ErrorException $exception) {

//    echo '(' . $exception->getCode() . ') ' . $exception->getMessage();
    } catch (FilipSedivy\EET\Exceptions\Receipt\ConstraintViolationException $violationException) {

//    echo implode('<br>', $violationException->getErrors());
    }


}