<?php
$of = storage_path() . '/xml/export.xml';
// --------------------------------------------------------------------------

// dodavatel
$company = Company::home();
$company->org_id = '36255789'; //hack
// --------------------------------------------------------------------------

// zaciname XML
$doc = new DomDocument('1.0', 'UTF-8');
//$doc->formatOutput = true;

$root = $doc->createElementNS('xmlns:dat', 'dat:dataPack');
$root = $doc->appendChild($root);
$root->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:dat', 'http://www.stormware.cz/schema/version_2/data.xsd');
$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:inv', 'http://www.stormware.cz/schema/version_2/invoice.xsd');
$root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:typ', 'http://www.stormware.cz/schema/version_2/type.xsd');
$root->setAttribute('version', '2.0');
$root->setAttribute('id', Carbon::now()->format('YmdHis'));
$root->setAttribute('ico', $company->org_id);
$root->setAttribute('application', 'MyApp');
$root->setAttribute('note', 'Vydane faktury');
// --------------------------------------------------------------------------

// najdeme faktury
$invoices = Invoice::orderBy('id', 'DESC')
    ->skip(2)
    ->take(1)
    ->get();
// --------------------------------------------------------------------------

foreach($invoices as $invoice)
{
    $dataPackItem = $doc->createElement('dat:dataPackItem');
    $dataPackItem->setAttribute('version', '2.0');
    $dataPackItem->setAttribute('id', $invoice->invoice_number);

    $invInvoice = $doc->createElement('inv:invoice');
    $invoiceHeader = $doc->createElement('inv:invoiceHeader');

    // typ dokladu - faktura
    $invoiceType = $doc->createElement('inv:invoiceType', $invoice->type->pohoda_ref);
    $invoiceHeader->appendChild($invoiceType);
    // --------------------------------------------------------------------------

    // cislo faktury
    $invoiceNumber = $doc->createElement('inv:number');
    $invoiceNumberRequest = $doc->createElement('typ:numberRequested', $invoice->invoice_number);
    $invoiceNumberRequest->setAttribute('checkDuplicity', 'true');
    $invoiceNumber->appendChild($invoiceNumberRequest);
    // ----------------------------------------------------
    $invoiceHeader->appendChild($invoiceNumber);

    // variabilny symbol
    $vs = $doc->createElement('inv:symVar', $invoice->vs);
    $invoiceHeader->appendChild($vs);
    // --------------------------------------------------------------------------

    // original cislo f.
    $originalDocument = $doc->createElement('inv:originalDocument', $invoice->invoice_number);
    $invoiceHeader->appendChild($originalDocument);
    // --------------------------------------------------------------------------

    // datum vystavenia
    $date = $doc->createElement('inv:date', with(new Carbon($invoice->published))->format('Y-m-d'));
    $invoiceHeader->appendChild($date);
    // --------------------------------------------------------------------------

    // datum danovej povinnosti
    $date = $doc->createElement('inv:dateTax', with(new Carbon($invoice->tax_liability))->format('Y-m-d'));
    $invoiceHeader->appendChild($date);
    // --------------------------------------------------------------------------

    // datum splatnosti
    $date = $doc->createElement('inv:dateDue', with(new Carbon($invoice->due_date))->format('Y-m-d'));
    $invoiceHeader->appendChild($date);
    // --------------------------------------------------------------------------

    // clenenie
    $tmp = $doc->createElement('typ:ids', $invoice->clasification->abbr);
    $classificationVAT = $doc->createElement('inv:classificationVAT');
    $classificationVAT->appendChild($tmp);
    $invoiceHeader->appendChild($classificationVAT);
    // --------------------------------------------------------------------------

    // text nad polozkami
    $tmp = $doc->createElement('inv:text', $invoice->text);
    $invoiceHeader->appendChild($tmp);
    // --------------------------------------------------------------------------

    // klient
    $partnerIdentity = $doc->createElement('inv:partnerIdentity');
    $address = $doc->createElement('typ:address');
    $partnerIdentity->appendChild($address);

    $tmp = $doc->createElement('typ:company', $invoice->client->getName());
    $address->appendChild($tmp);

    $tmp = $doc->createElement('typ:name', $invoice->client->getFullName());
    $address->appendChild($tmp);

    $tmp = $doc->createElement('typ:street', $invoice->client->street);
    $address->appendChild($tmp);

    $tmp = $doc->createElement('typ:city', $invoice->client->city);
    $address->appendChild($tmp);

    $tmp = $doc->createElement('typ:zip', $invoice->client->zip_code);
    $address->appendChild($tmp);

    $tmp = $doc->createElement('typ:ico', $invoice->client->org_id);
    $address->appendChild($tmp);

    $tmp = $doc->createElement('typ:dic', $invoice->client->tax);
    $address->appendChild($tmp);

    $tmp = $doc->createElement('typ:icDph', $invoice->client->vat_number);
    $address->appendChild($tmp);

    $tmp = $doc->createElement('typ:ids', $invoice->client->state->title);
    $country = $doc->createElement('typ:country');
    $country->appendChild($tmp);
    $address->appendChild($country);

    $tmp = $doc->createElement('typ:phone', $invoice->client->phone);
    $address->appendChild($tmp);

    $tmp = $doc->createElement('typ:fax', $invoice->client->fax);
    $address->appendChild($tmp);

    $tmp = $doc->createElement('typ:email', $invoice->client->email);
    $address->appendChild($tmp);

    $invoiceHeader->appendChild($partnerIdentity);
    // --------------------------------------------------------------------------

    // dodacia adresa
    $shipTo = $doc->createElement('typ:shipToAddress');

    $tmp = $doc->createElement('typ:company', $invoice->clientDelivery->getName());
    $shipTo->appendChild($tmp);

    $tmp = $doc->createElement('typ:street', $invoice->clientDelivery->street);
    $shipTo->appendChild($tmp);

    $tmp = $doc->createElement('typ:city', $invoice->clientDelivery->city);
    $shipTo->appendChild($tmp);

    $tmp = $doc->createElement('typ:zip', $invoice->clientDelivery->zip_code);
    $shipTo->appendChild($tmp);

    $partnerIdentity->appendChild($shipTo);
    // --------------------------------------------------------------------------

    // konstantny symbol
    $tmp = $doc->createElement('inv:symConst', $invoice->constantSymbol->abbr);
    $invoiceHeader->appendChild($tmp);
    // --------------------------------------------------------------------------

    // interne poznamky
    $notes = NULL;
    foreach($invoice->notes as $note)
    {
        $notes .= $note->text . "; \n";
    }
    $tmp = $doc->createElement('inv:intNote', rtrim($notes, "\n"));
    $invoiceHeader->appendChild($tmp);
    // --------------------------------------------------------------------------

    // dodavatel
    $myIdentity = $doc->createElement('inv:myIdentity');
    $address = $doc->createElement('typ:address');

    $tmp = $doc->createElement('typ:company', $invoice->supplier->getName());
    $address->appendChild($tmp);

    $tmp = $doc->createElement('typ:name', $invoice->supplier->getFullName());
    $address->appendChild($tmp);

    $tmp = $doc->createElement('typ:street', $invoice->supplier->street);
    $address->appendChild($tmp);

    $tmp = $doc->createElement('typ:city', $invoice->supplier->city);
    $address->appendChild($tmp);

    $tmp = $doc->createElement('typ:zip', $invoice->supplier->zip_code);
    $address->appendChild($tmp);

    $tmp = $doc->createElement('typ:ico', $invoice->supplier->org_id);
    $address->appendChild($tmp);

    $tmp = $doc->createElement('typ:dic', $invoice->supplier->tax);
    $address->appendChild($tmp);

    $tmp = $doc->createElement('typ:icDph', $invoice->supplier->vat_number);
    $address->appendChild($tmp);

    $tmp = $doc->createElement('typ:ids', $invoice->supplier->state->title);
    $country = $doc->createElement('typ:country');
    $country->appendChild($tmp);
    $address->appendChild($country);

    $tmp = $doc->createElement('typ:phone', $invoice->supplier->phone);
    $address->appendChild($tmp);

    $tmp = $doc->createElement('typ:fax', $invoice->supplier->fax);
    $address->appendChild($tmp);

    $tmp = $doc->createElement('typ:email', $invoice->supplier->email);
    $address->appendChild($tmp);

    $myIdentity->appendChild($address);
    $invoiceHeader->appendChild($myIdentity);
    // --------------------------------------------------------------------------

    $invInvoice->appendChild($invoiceHeader);

    // polozky na fakture
    if( $invoice->items()->count())
    {
        $invoiceDetail = $doc->createElement('inv:invoiceDetail');

        foreach($invoice->items as $item)
        {
            $invoiceItem = $doc->createElement('inv:invoiceItem');

            $tmp = $doc->createElement('inv:text', $item->description);
            $invoiceItem->appendChild($tmp);

            $tmp = $doc->createElement('inv:quantity', $item->quantity);
            $invoiceItem->appendChild($tmp);

            $tmp = $doc->createElement('inv:unit', $item->unitType->unit);
            $invoiceItem->appendChild($tmp);

            $tmp = $doc->createElement('inv:payVAT', $invoice->supplier->isVatPayer() ? 'true' : 'false');
            $invoiceItem->appendChild($tmp);

            $tmp = $doc->createElement('inv:rateVAT', 'none');
            $invoiceItem->appendChild($tmp);

            $tmp = $doc->createElement('inv:discountPercentage', $item->discount);
            $invoiceItem->appendChild($tmp);

            $classificationVAT = $doc->createElement('inv:classificationVAT');
            $tmp = $doc->createElement('typ:ids', $item->clasification->abbr);
            $classificationVAT->appendChild($tmp);
            $invoiceItem->appendChild($classificationVAT);

            $accountingType = $doc->createElement('inv:accountingType');
            $tmp = $doc->createElement('typ:ids', $item->accounting->abbr);
            $accountingType->appendChild($tmp);
            $invoiceItem->appendChild($accountingType);


            $invoiceDetail->appendChild($invoiceItem);
        }

        $invInvoice->appendChild($invoiceDetail);
    }
    // --------------------------------------------------------------------------


    $dataPackItem->appendChild($invInvoice);

    $root->appendChild($dataPackItem);
}


$doc->save($of);


return Response::download($of);