<?php

if (isset($_REQUEST['secretcode']) && $_REQUEST['secretcode'] == "lYspnYd2mYTJm6") {

    include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";

    $id = date('Y-m', strtotime("-1 months"));

    if (date('Y-m') == date('Y-01')) {

        $year = date('Y', strtotime("-1 year"));

    } else {

        $year = date('Y');
    }

    $invoices_query = $mysqli->query("SELECT *, i.id as id, DATE_FORMAT(i.date, '%Y-%m-%d') as date, DATE_FORMAT(i.date, '%Y-%m-%dT%H:%i:%s') as datetime FROM orders_invoices i WHERE YEAR(i.date) = '$year' AND MONTH(i.date) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH) ORDER BY i.id") or die($mysqli->error);

    $outputName = $_SERVER['DOCUMENT_ROOT'] . '/admin/data/export/invoices_orders/invoices_' . $id . '.pdf';

    $pdf = new \Clegginabox\PDFMerger\PDFMerger;

    while($invoice = mysqli_fetch_assoc($invoices_query)){

        $pdf->addPDF($_SERVER['DOCUMENT_ROOT'] . '/admin/data/invoices/orders/' . $invoice['id'] . '.pdf', 'all');

    }

    $pdf->merge('browser', $outputName, 'P');

}