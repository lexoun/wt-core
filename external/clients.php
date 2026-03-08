<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";

$documents = $mysqli->query("SELECT i.* FROM demands d, documents_contracts i WHERE d.id = i.client_id AND d.secretstring = '".$_REQUEST['secretstring']."'")or die($mysqli->error);

while($document = mysqli_fetch_assoc($documents)){

    echo '<a class="woocommerce-Button button" href="https://www.wellnesstrade.cz/data/clients/documents/'.$_REQUEST['secretstring'].'/'.$document['seoslug'].'.'.$document['extension'].'" target="_blank" style="margin-bottom: 4px;">'.$document['name'].'</a><br>';

}


// get invoices
$invoices = $mysqli->query("SELECT i.id FROM demands d, demands_advance_invoices i WHERE d.id = i.demand_id AND d.secretstring = '".$_REQUEST['secretstring']."'")or die($mysqli->error);

while($invoice = mysqli_fetch_assoc($invoices)){

    if(!file_exists($_SERVER['DOCUMENT_ROOT'] . '/data/clients/documents/' . $_REQUEST['secretstring'] . '/Zalohova_faktura_' . $invoice['id'] . '.pdf')){

        copy($_SERVER['DOCUMENT_ROOT'] . '/admin/data/invoices/demands/Zalohova_faktura_' . $invoice['id'] . '.pdf', $_SERVER['DOCUMENT_ROOT'] . '/data/clients/documents/' . $_REQUEST['secretstring'] . '/Zalohova_faktura_' . $invoice['id'] . '.pdf');

    }

    echo '<a class="woocommerce-Button button" href="https://www.wellnesstrade.cz/data/clients/documents/' . $_REQUEST['secretstring'] . '/Zalohova_faktura_' . $invoice['id'] . '.pdf" target="_blank" style="margin-bottom: 4px;">'.basename($invoice['id']).'.pdf</a><br>';

}

?>