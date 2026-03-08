<?php
if (isset($_REQUEST['secretcode']) && $_REQUEST['secretcode'] == "lYspnYd2mYTJm6") {

    include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";

    function classificationVAT($reverse_charge, $price, $dic){

        // eu countries except CZ
        $euCountries = [
            'AT',
            'BE',
            'BG',
            'CY',
            'DE',
            'DK',
            'EE',
            'EL',
            'ES',
            'FI',
            'FR',
            'GB',
            'HR',
            'HU',
            'IE',
            'IT',
            'LT',
            'LU',
            'LV',
            'MT',
            'NL',
            'PL',
            'PT',
            'RO',
            'SE',
            'SI',
            'SK',
        ];

        if(isset($reverse_charge) && $reverse_charge == 'Ano') {
            // UDpdp - přenesená daňová povinnost - nejde do kontrolního hlášení
            return 'UDpdp';
        }

        if(!empty($dic)){

            if(substr($dic, 0, 2) == 'CZ'){
                // firma v ČR... vždy UD
                return 'UD';
            }elseif(in_array(substr($dic, 0, 2), $euCountries)){
                // firma v rámci EU... vždy UDdodEU
                return 'UDdodEU';
            }else{
                // firma mimo EU... vždy UD
                return 'UD';
            }

        }else{

            if($price >= 10000){
                // F0 nad 10k... vždy UDA5
                return 'UDA5';
            }else{
                // FO pod 10k... vždy UD
                return 'UD';
            }

        }
    }


    // set interval
    $currentDate = date('Y-m-d');
    $date = date('Y-m-d', strtotime("-1 week"));

    if (date('Y-m') == date('Y-01')) {

        $year = date('Y', strtotime("-1 year"));

    } else {

        $year = date('Y');
    }


    $invoices_query = $mysqli->query("SELECT 
           *, i.id as id, DATE_FORMAT(i.date, '%Y-%m-%d') as date, DATE_FORMAT(i.date, '%Y-%m-%dT%H:%i:%s') as datetime 
        FROM orders_invoices i 
            WHERE YEAR(i.date) = '$year' 
            AND YEARWEEK(i.date) = YEARWEEK(NOW() - INTERVAL 1 WEEK)
            ORDER BY i.id") or die($mysqli->error);

    $totalInvoices = mysqli_num_rows($invoices_query);

    $find_query = $mysqli->query("SELECT * FROM orders_invoices_exports WHERE date = '".$currentDate."'") or die($mysqli->error);

    if (mysqli_num_rows($find_query) > 0) {

        $mysqli->query("UPDATE orders_invoices_exports SET invoice_number = '$totalInvoices' WHERE date = '$currentDate'") or die($mysqli->error);

        $export_id_query = mysqli_fetch_assoc($find_query);
        $exportId = $export_id_query['id'];

        $firstMail = false;

    } else {

        $mysqli->query("INSERT INTO orders_invoices_exports (date, invoice_number) VALUES ('$currentDate', '$totalInvoices')") or die($mysqli->error);
        $exportId = $mysqli->insert_id;

        $firstMail = true;

    }

    $listOfPrices = '';

    if (mysqli_num_rows($invoices_query) > 0) {

        /* create a dom document with encoding utf8 */
        $domtree = new DOMDocument('1.0', 'UTF-8');

        /* create the root element of the xml tree */
        $xmlRoot = $domtree->createElementNS('xmlns:dat', 'dat:dataPack');

        /* append it to the document created */
        $xmlRoot = $domtree->appendChild($xmlRoot);

        $xmlRoot->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:dat', 'http://www.stormware.cz/schema/version_2/data.xsd');
        $xmlRoot->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:inv', 'http://www.stormware.cz/schema/version_2/invoice.xsd');
        $xmlRoot->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:typ', 'http://www.stormware.cz/schema/version_2/type.xsd');

        $xmlRoot->setAttribute('version', '2.0');
        $xmlRoot->setAttribute('id', $currentDate);
        $xmlRoot->setAttribute('ico', '29154871');
        $xmlRoot->setAttribute('application', 'Wellness Trade App');
        $xmlRoot->setAttribute('note', 'Vydane faktury za objednavky');

        $count = 0;
        while ($invoices = mysqli_fetch_array($invoices_query)) {

            $count++;

            if ($invoices['type'] == 'order') {

                $data_query = $mysqli->query("SELECT *, DATE_FORMAT(order_date, '%Y-%m-%d') as dateformated FROM orders WHERE id = '" . $invoices['order_id'] . "'") or die($mysqli->error);
                $data = mysqli_fetch_array($data_query);

                $variable_symbol = $data['id'];

            } elseif ($invoices['type'] == 'service') {

                $data_query = $mysqli->query("SELECT *, DATE_FORMAT(date, '%Y-%m-%d') as dateformated, price as total FROM services WHERE id = '" . $invoices['order_id'] . "'") or die($mysqli->error);
                $data = mysqli_fetch_array($data_query);

                $variable_symbol = $data['id'] . '000';

                $data['order_site'] = 'wellnesstrade';

            }


            //empty reverse_charge not yet implemented
            $data['reverse_charge'] = '';

            $address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $data['shipping_id'] . '" WHERE b.id = "' . $data['billing_id'] . '"') or die($mysqli->error);
            $address = mysqli_fetch_assoc($address_query);

            $total_vat = 100 + $data['vat'];

            $payment_query = $mysqli->query("SELECT name, eet, pohoda_id FROM shops_payment_methods WHERE link_name = '" . $data['payment_method'] . "'") or die($mysqli->error);
            $payment = mysqli_fetch_array($payment_query);

            $pay_method = $payment['pohoda_id'];
            $stateEET = $payment['eet'];

            $packItem = $domtree->createElement('dat:dataPackItem');
            $packItem = $xmlRoot->appendChild($packItem);

            $packItem->setAttribute('version', '2.0');
            $packItem->setAttribute('id', $invoices['id']);

            $invoice = $domtree->createElement('inv:invoice');
            $invoice = $packItem->appendChild($invoice);

            $invoice->setAttribute('version', '2.0');

            $invoiceHeader = $domtree->createElement('inv:invoiceHeader');
            $invoiceHeader = $invoice->appendChild($invoiceHeader);

            if (isset($invoices['status']) && $invoices['status'] == 'active') {$invoice_type = 'issuedInvoice';} elseif (isset($invoices['status']) && $invoices['status'] == 'odd') {$invoice_type = 'issuedCorrectiveTax';}

            $invoiceHeader->appendChild($domtree->createElement('inv:invoiceType', $invoice_type));

            $number = $domtree->createElement('inv:number');
            $number = $invoiceHeader->appendChild($number);

            $number->appendChild($domtree->createElement('typ:numberRequested', $invoices['id']));

            $invoiceHeader->appendChild($domtree->createElement('inv:symVar', $variable_symbol));
            $invoiceHeader->appendChild($domtree->createElement('inv:date', $invoices['date']));
            $invoiceHeader->appendChild($domtree->createElement('inv:dateTax', $invoices['date']));
            $invoiceHeader->appendChild($domtree->createElement('inv:dateAccounting', $invoices['date']));

            /*
            if(isset($invoices['vat']) && $invoices['vat'] == 21){

            $invoiceHeader->appendChild($domtree->createElement('inv:rateVAT','high'));

            }elseif(isset($invoices['vat']) && $invoices['vat'] == 15){

            $invoiceHeader->appendChild($domtree->createElement('inv:rateVAT','low'));

            }elseif(isset($invoices['vat']) && $invoices['vat'] == 10){

            $invoiceHeader->appendChild($domtree->createElement('inv:rateVAT','third'));

            }elseif(isset($invoices['vat']) && $invoices['vat'] == 0){

            $invoiceHeader->appendChild($domtree->createElement('inv:rateVAT','none'));

            }

             */
            if (isset($invoices['status']) && $invoices['status'] == 'active') {

                if (isset($data['payment_method']) && $data['payment_method'] == 'cash') {

                    $invoiceHeader->appendChild($domtree->createElement('inv:dateDue', $invoices['date']));

                } else {

                    $due_date = date('Y-m-d', strtotime($invoices['date'] . ' + 14 days'));

                    $invoiceHeader->appendChild($domtree->createElement('inv:dateDue', $due_date));

                }

            } elseif (isset($invoices['status']) && $invoices['status'] == 'odd') {

                $invoiceHeader->appendChild($domtree->createElement('inv:dateDue', $invoices['date']));

            }

            if (isset($invoices['status']) && $invoices['status'] == 'odd') {

                $invoiceHeader->appendChild($domtree->createElement('inv:dateApplicationVAT', $invoices['date']));

            }

            $accounting = $domtree->createElement('inv:accounting');
            $accounting = $invoiceHeader->appendChild($accounting);

            $accounting->appendChild($domtree->createElement('typ:ids', '3FV'));

            $classificationVAT = $domtree->createElement('inv:classificationVAT');
            $classificationVAT = $invoiceHeader->appendChild($classificationVAT);

            $classificationVAT->appendChild($domtree->createElement(
                'typ:ids', classificationVAT($data['reverse_charge'], $invoices['total_price'], $address['billing_dic'])
            ));


            if (isset($invoices['status']) && $invoices['status'] == 'active') {$invoice_text = 'Faktura za zboží.';} elseif (isset($invoices['status']) && $invoices['status'] == 'odd') {$invoice_text = 'Opravný daňový doklad k faktuře ' . $invoices['invoice_id'];}

            $invoiceHeader->appendChild($domtree->createElement('inv:text', $invoice_text));

            $partnerIdentity = $domtree->createElement('inv:partnerIdentity');
            $partnerIdentity = $invoiceHeader->appendChild($partnerIdentity);

            $billing_address = $domtree->createElement('typ:address');
            $billing_address = $partnerIdentity->appendChild($billing_address);

            $billing_address->appendChild($domtree->createElement('typ:company', htmlspecialchars($address['billing_company'])));
            $billing_address->appendChild($domtree->createElement('typ:name', $address['billing_name'] . ' ' . $address['billing_surname']));
            $billing_address->appendChild($domtree->createElement('typ:city', $address['billing_city']));
            $billing_address->appendChild($domtree->createElement('typ:street', $address['billing_street']));
            $billing_address->appendChild($domtree->createElement('typ:zip', $address['billing_zipcode']));
            $billing_address->appendChild($domtree->createElement('typ:ico', $address['billing_ico']));
            $billing_address->appendChild($domtree->createElement('typ:dic', $address['billing_dic']));

            $billing_address->appendChild($domtree->createElement('typ:phone', $address['billing_phone']));
            $billing_address->appendChild($domtree->createElement('typ:email', $address['billing_email']));

            if (isset($address['shipping_name']) && $address['shipping_name'] != '' && isset($address['shipping_city']) && $address['shipping_city'] != '') {

                $shipping_name = $address['shipping_name'];
                $shipping_surname = $address['shipping_surname'];
                $shipping_city = $address['shipping_city'];
                $shipping_street = $address['shipping_street'];

            } else {

                $shipping_name = $address['billing_name'];
                $shipping_surname = $address['billing_surname'];
                $shipping_city = $address['billing_city'];
                $shipping_street = $address['billing_street'];

            }

            $shipToAddress = $domtree->createElement('typ:shipToAddress');
            $shipToAddress = $partnerIdentity->appendChild($shipToAddress);

            $shipToAddress->appendChild($domtree->createElement('typ:name', $shipping_name . ' ' . $shipping_surname));
            $shipToAddress->appendChild($domtree->createElement('typ:city', $shipping_city));
            $shipToAddress->appendChild($domtree->createElement('typ:street', $shipping_street));

            $invoiceHeader->appendChild($domtree->createElement('inv:numberOrder', $invoices['order_id']));

            if(empty($data['dateformated']) || $data['dateformated'] == '0000-00-00'){
                $invoiceHeader->appendChild($domtree->createElement('inv:dateOrder', $invoices['date']));
            }else{
                $invoiceHeader->appendChild($domtree->createElement('inv:dateOrder', $data['dateformated']));
            }

            $paymentType = $domtree->createElement('inv:paymentType');
            $paymentType = $invoiceHeader->appendChild($paymentType);

            $paymentType->appendChild($domtree->createElement('typ:paymentType', $pay_method));

            $account = $domtree->createElement('inv:account');
            $account = $invoiceHeader->appendChild($account);

            $account->appendChild($domtree->createElement('typ:accountNo', '2700610079'));
            $account->appendChild($domtree->createElement('typ:bankCode', '2010'));

            $invoiceHeader->appendChild($domtree->createElement('inv:note', 'Načteno z XML'));
            $invoiceHeader->appendChild($domtree->createElement('inv:intNote', 'Tento doklad byl vytvořen importem přes XML.'));

            $centre = $domtree->createElement('inv:centre');
            $centre = $invoiceHeader->appendChild($centre);

            $centre->appendChild($domtree->createElement('typ:ids', $data['order_site']));

            $activity = $domtree->createElement('inv:activity');
            $activity = $invoiceHeader->appendChild($activity);

            $activity->appendChild($domtree->createElement('typ:ids', 'Zboží'));

            // POLOŽKY POLOŽKY POLOŽKY POLOŽKY

            $sumProducts = 0;

            $invoiceDetail = $domtree->createElement('inv:invoiceDetail');
            $invoiceDetail = $invoice->appendChild($invoiceDetail);

            $coeficient = vat_coeficient($data['vat']);

            $total_without_vat = 0;

            $products = "";

            if($invoices['type'] == 'order'){

                $products_query = $mysqli->query("SELECT *, p.id as ajdee, o.price as price FROM orders_products_bridge o LEFT JOIN products p ON p.id = o.product_id WHERE o.aggregate_id = '" . $invoices['order_id'] . "' AND o.aggregate_type = 'order'");

            }else{

                $products_query = $mysqli->query("SELECT *, p.id as ajdee, o.price as price FROM services_products_bridge o LEFT JOIN products p ON p.id = o.product_id WHERE o.aggregate_id = '" . $invoices['order_id'] . "'");

            }

            $i = 0;
            while ($product = mysqli_fetch_array($products_query)) {

                $i++;
                $oneproduct = "";
                $vari = '';

                if ($product['variation_id'] != 0) {

                    $variation_sku_query = $mysqli->query("SELECT id, sku, main_warehouse FROM products_variations WHERE id = '" . $product['variation_id'] . "'");
                    $variation_sku = mysqli_fetch_array($variation_sku_query);

                    $sku = $variation_sku['sku'];

                    $vari_vlue = '';

                    $variation_query = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $product['variation_id'] . "'");

                    while ($variation = mysqli_fetch_array($variation_query)) {

                        if ($vari_vlue == "") { $vari_vlue = $variation['name'] . ': ' . $variation['value'];

                        } else {

                            $vari_vlue = $vari_vlue . ', ' . $variation['name'] . ': ' . $variation['value'];
                        }

                    }

                    $vari = $vari_vlue;

                } else {

                    $sku = $product['code'];

                }

                $price = $product['price'];

                if (isset($invoices['status']) && $invoices['status'] == 'active') {

                    $product_without_vat = number_format($price / $total_vat * 100, 2, '.', '');
                    $total_product_price = number_format($price, 2, '.', '');

                    $total_with_vat_quantities = number_format($price * $product['quantity'], 2, '.', '');

                } elseif (isset($invoices['status']) && $invoices['status'] == 'odd') {

                    $product_without_vat = '-' . number_format($price / $total_vat * 100, 2, '.', '');
                    $total_product_price = '-' . number_format($price, 2, '.', '');

                }

                $invoiceItem = $domtree->createElement('inv:invoiceItem');
                $invoiceItem = $invoiceDetail->appendChild($invoiceItem);

                $invoiceItem->appendChild($domtree->createElement('inv:text', htmlspecialchars(mb_strimwidth($sku . ' - ' . $product['productname'], 0, 84, "..."))));

                $invoiceItem->appendChild($domtree->createElement('inv:quantity', $product['quantity']));
                $invoiceItem->appendChild($domtree->createElement('inv:unit', 'ks'));
                $invoiceItem->appendChild($domtree->createElement('inv:payVAT', 'true'));


                if(!empty($product['discount'])){

                    $invoiceItem->appendChild($domtree->createElement('inv:discountPercentage', $product['discount']));

                }

                if (isset($data['vat']) && $data['vat'] == '21') {
                    $vat = 'high';
                } elseif (isset($data['vat']) && $data['vat'] == '15') {
                    $vat = 'low';
                } elseif (isset($data['vat']) && $data['vat'] == '12') {
                    $vat = 'low';
                } elseif (isset($data['vat']) && $data['vat'] == '10') {
                    $vat = 'third';
                } elseif (isset($data['vat']) && $data['vat'] == '0') {
                    $vat = 'none';
                }

                $invoiceItem->appendChild($domtree->createElement('inv:rateVAT', $vat));

                $classificationVAT = $domtree->createElement('inv:classificationVAT');
                $classificationVAT = $invoiceItem->appendChild($classificationVAT);

                $classificationVAT->appendChild($domtree->createElement(
                    'typ:ids', classificationVAT($data['reverse_charge'], $invoices['total_price'], $address['billing_dic'])
                ));

                // todo not only homeCurrency
                if(empty($invoices['currency']) || $invoices['currency'] == 'CZK'){
                    $homeCurrency = $domtree->createElement('inv:homeCurrency');
                }else{
                    $homeCurrency = $domtree->createElement('inv:foreignCurrency');
                }

                $homeCurrency = $invoiceItem->appendChild($homeCurrency);

                $total_products = number_format($total_product_price * $product['quantity'], 2, '.', '');

                $price = get_price($total_product_price, $coeficient);

                $homeCurrency->appendChild($domtree->createElement('typ:unitPrice', $price['single']));
                $homeCurrency->appendChild($domtree->createElement('typ:price', $price['without_vat']));
                $homeCurrency->appendChild($domtree->createElement('typ:priceVAT', $price['vat']));

                if(!empty($product['discount'])){

                    $sumProducts += ($price['single'] / 100 * (100 - $product['discount'])) * $product['quantity'];

                }else{

                    $sumProducts += $price['single'] * $product['quantity'];

                }

            }

            if ($data['delivery_price'] != 0) {

                $i++;

                if (isset($invoices['status']) && $invoices['status'] == 'active') {

                    $total_delivery_price = number_format($data['delivery_price'], 2, '.', '');

                } elseif (isset($invoices['status']) && $invoices['status'] == 'odd') {

                    $total_delivery_price = '-' . number_format($data['delivery_price'], 2, '.', '');

                }

                $invoiceItem = $domtree->createElement('inv:invoiceItem');
                $invoiceItem = $invoiceDetail->appendChild($invoiceItem);

                $invoiceItem->appendChild($domtree->createElement('inv:text', 'Doprava'));
                $invoiceItem->appendChild($domtree->createElement('inv:quantity', '1'));
                $invoiceItem->appendChild($domtree->createElement('inv:unit', 'ks'));
                $invoiceItem->appendChild($domtree->createElement('inv:payVAT', 'true'));

                if (isset($data['vat']) && $data['vat'] == '21') {$vat = 'high';} elseif (isset($data['vat']) && $data['vat'] == '15') {$vat = 'low';} elseif (isset($data['vat']) && $data['vat'] == '10') {$vat = 'third';} elseif (isset($data['vat']) && $data['vat'] == '0') {$vat = 'none';}

                $invoiceItem->appendChild($domtree->createElement('inv:rateVAT', $vat));

                $classificationVAT = $domtree->createElement('inv:classificationVAT');
                $classificationVAT = $invoiceItem->appendChild($classificationVAT);

                $classificationVAT->appendChild($domtree->createElement(
                    'typ:ids', classificationVAT($data['reverse_charge'], $invoices['total_price'], $address['billing_dic'])
                ));

                // todo not only homeCurrency
                if(empty($invoices['currency']) || $invoices['currency'] == 'CZK'){
                    $homeCurrency = $domtree->createElement('inv:homeCurrency');
                }else{
                    $homeCurrency = $domtree->createElement('inv:foreignCurrency');
                }

                $homeCurrency = $invoiceItem->appendChild($homeCurrency);


                $price = get_price($total_delivery_price, $coeficient);

                $homeCurrency->appendChild($domtree->createElement('typ:unitPrice', $price['single']));
                $homeCurrency->appendChild($domtree->createElement('typ:price', $price['without_vat']));
                $homeCurrency->appendChild($domtree->createElement('typ:priceVAT', $price['vat']));

                $sumProducts += $price['single'];

            }

            if ($invoices['type'] == 'service') {

                $i++;

                $items_query = $mysqli->query("SELECT * FROM services_items WHERE service_id = '".$invoices['order_id']."'")or die($mysqli->error);
                while($item = mysqli_fetch_assoc($items_query)) {

                    if (isset($invoices['status']) && $invoices['status'] == 'active') {

                        $total_item_price = number_format($item['price'], 2, '.', '');

                    } elseif (isset($invoices['status']) && $invoices['status'] == 'odd') {

                        $total_item_price = '-' . number_format($item['price'], 2, '.', '');

                    }

                    $invoiceItem = $domtree->createElement('inv:invoiceItem');
                    $invoiceItem = $invoiceDetail->appendChild($invoiceItem);

                    if(!empty($item['name'])){ $name = $item['name']; }else{ $name = 'Provedený servis'; }

                    $invoiceItem->appendChild($domtree->createElement('inv:text', $name));

                    $invoiceItem->appendChild($domtree->createElement('inv:quantity', '1'));

                    $invoiceItem->appendChild($domtree->createElement('inv:unit', 'ks'));

                    $invoiceItem->appendChild($domtree->createElement('inv:payVAT', 'true'));

                    if (isset($data['vat']) && $data['vat'] == '21') {
                        $vat = 'high';
                    } elseif (isset($data['vat']) && $data['vat'] == '15') {
                        $vat = 'low';
                    } elseif (isset($data['vat']) && $data['vat'] == '12') {
                        $vat = 'low';
                    } elseif (isset($data['vat']) && $data['vat'] == '10') {
                        $vat = 'third';
                    } elseif (isset($data['vat']) && $data['vat'] == '0') {
                        $vat = 'none';
                    }

                    $invoiceItem->appendChild($domtree->createElement('inv:rateVAT', $vat));

                    $classificationVAT = $domtree->createElement('inv:classificationVAT');
                    $classificationVAT = $invoiceItem->appendChild($classificationVAT);

                    $classificationVAT->appendChild($domtree->createElement(
                        'typ:ids', classificationVAT($data['reverse_charge'], $invoices['total_price'], $address['billing_dic'])
                    ));

                    // todo not only homeCurrency
                    if (empty($invoices['currency']) || $invoices['currency'] == 'CZK') {
                        $homeCurrency = $domtree->createElement('inv:homeCurrency');
                    } else {
                        $homeCurrency = $domtree->createElement('inv:foreignCurrency');
                    }

                    $homeCurrency = $invoiceItem->appendChild($homeCurrency);

                    $price = get_price($total_item_price, $coeficient);

                    $homeCurrency->appendChild($domtree->createElement('typ:unitPrice', $price['single']));
                    $homeCurrency->appendChild($domtree->createElement('typ:price', $price['without_vat']));
                    $homeCurrency->appendChild($domtree->createElement('typ:priceVAT', $price['vat']));


                    $sumProducts += $price['single'];

                }

            }

            // POLOŽKY

            // InvoiceSummary
            if (isset($invoices['status']) && $invoices['status'] == 'active') {
                $total_order = $invoices['total_price'];
            } elseif (isset($invoices['status']) && $invoices['status'] == 'odd') {
                $total_order = $invoices['total_price'];
            }

            $invoiceSummary = $domtree->createElement('inv:invoiceSummary');
            $invoiceSummary = $invoice->appendChild($invoiceSummary);

            $invoiceSummary->appendChild($domtree->createElement('inv:roundingVAT', 'none'));

            if($data['payment_method'] == 'cash' || $data['payment_method'] == 'cod'){

                $invoiceSummary->appendChild($domtree->createElement('inv:roundingDocument', 'math2one'));

            }else{

                $invoiceSummary->appendChild($domtree->createElement('inv:roundingDocument', 'none'));

            }

            // todo not only homeCurrency
            if(empty($invoices['currency']) || $invoices['currency'] == 'CZK'){

                $homeCurrency = $domtree->createElement('inv:homeCurrency');
                $homeCurrency = $invoiceSummary->appendChild($homeCurrency);

            }else{

                $homeCurrency = $domtree->createElement('inv:foreignCurrency');
                $homeCurrency = $invoiceSummary->appendChild($homeCurrency);

                $typCurrency = $domtree->createElement('typ:currency');
                $typCurrency = $homeCurrency->appendChild($typCurrency);

                $typCurrency->appendChild($domtree->createElement('typ:ids', $invoices['currency']));

                $homeCurrency->appendChild($domtree->createElement('typ:rate', $invoices['exchange_rate']));
                $homeCurrency->appendChild($domtree->createElement('typ:amount', 1));

            }

            if (isset($invoices['vat']) && $invoices['vat'] == '21') {

                $homeCurrency->appendChild($domtree->createElement('typ:priceHigh', $invoices['price_without_vat']));
                $homeCurrency->appendChild($domtree->createElement('typ:priceHighVAT', $invoices['total_vat']));
                $homeCurrency->appendChild($domtree->createElement('typ:priceHighSum', $invoices['total_price']));

            } elseif (isset($invoices['vat']) && $invoices['vat'] == '15') {

                $homeCurrency->appendChild($domtree->createElement('typ:priceLow', $invoices['price_without_vat']));
                $homeCurrency->appendChild($domtree->createElement('typ:priceLowVAT', $invoices['total_vat']));
                $homeCurrency->appendChild($domtree->createElement('typ:priceLowSum', $invoices['total_price']));

            } elseif (isset($invoices['vat']) && $invoices['vat'] == '12') {

                $homeCurrency->appendChild($domtree->createElement('typ:priceLow', $invoices['price_without_vat']));
                $homeCurrency->appendChild($domtree->createElement('typ:priceLowVAT', $invoices['total_vat']));
                $homeCurrency->appendChild($domtree->createElement('typ:priceLowSum', $invoices['total_price']));

            } elseif (isset($invoices['vat']) && $invoices['vat'] == '10') {

                $homeCurrency->appendChild($domtree->createElement('typ:price3', $invoices['price_without_vat']));
                $homeCurrency->appendChild($domtree->createElement('typ:price3VAT', $invoices['total_vat']));
                $homeCurrency->appendChild($domtree->createElement('typ:price3Sum', $invoices['total_price']));

            } elseif (isset($invoices['vat']) && $invoices['vat'] == '0') {

                $homeCurrency->appendChild($domtree->createElement('typ:priceNone', $invoices['price_without_vat']));

            }


            if ($invoices['type'] == 'order') {

                $redirect = "https://www.wellnesstrade.cz/admin/pages/orders/zobrazit-objednavku?id=" . $data['id'] . "&success=generate_invoice";

            }else{

                $redirect = "https://www.wellnesstrade.cz/admin/pages/services/zobrazit-servis?id=" . $data['id'] . "&success=generate_invoice";

            }

            if((thousand_seperator($invoices['total_price']) == thousand_seperator($sumProducts))
                || (thousand_seperator($invoices['total_price']) == thousand_seperator(round($sumProducts)) && ($data['payment_method'] == 'cash' || $data['payment_method'] == 'cod'))
            ){

                $listOfPrices .= $count.' - ID: <a href="'.$redirect.'" target="_blank">'.$invoices['id'].'</a> ';

                $listOfPrices .= ' - '.$invoices['date'];

                if ($invoices['type'] == 'order') {

                    $data_currency = $data['order_currency'];

                }else{

                    $data_currency = $data['currency'];
                }

                if ($invoices['currency'] != $data_currency) {

                    $listOfPrices .= ' - <strong>! CURRENCY MISSMATCH!!!: ' . $invoices['currency'] . ' ! x ! ' . $data_currency . ' !</strong>';

                }


                if($invoices['currency'] != 'CZK'){ $listOfPrices .= ' - <strong>! '.$invoices['currency'].' !</strong>'; }

                if ($invoices['type'] == 'order') {
                    if($data['order_currency'] != 'CZK'){ $listOfPrices .= ' - DAT: <strong>! '.$data['order_currency'].' !</strong>'; }
                }else {
                    if ($data['currency'] != 'CZK') {
                        $listOfPrices .= ' - DAT: <strong>! ' . $data['currency'] . ' !</strong>';
                    }
                }

                if(!empty($address['billing_ico'])){ $listOfPrices .= ' - ICO: '.$address['billing_ico']; }
                if(!empty($address['billing_dic'])){ $listOfPrices .= ' - DIC: '.$address['billing_dic']; }
                if($address['billing_country'] != 'CZ'){ $listOfPrices .= ' - COUNTRY : '.$address['billing_country']; }


            }else{


                $listOfPrices .= $count.' - <br>ID: <a href="'.$redirect.'" target="_blank">'.$invoices['id'].'</a>
                <br><strong>! '.$invoices['currency'].' !</strong> Invoice Total: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.thousand_seperator($invoices['total_price']).' 
                <br>'.$invoices['type'].' Total: '.thousand_seperator($data['total']).'   
                <br>Generation Sum: &nbsp;'.thousand_seperator($sumProducts).'<br>'.$data['payment_method'].'<br>';

            }



            if($invoices['total_price'] >= 10000){ $listOfPrices .= ' - PRICE: '.$invoices['total_price']; }

            $listOfPrices .= ' - VAT: '.classificationVAT($data['reverse_charge'], $invoices['total_price'], $address['billing_dic']);
            $listOfPrices .= '<br>';


            $invoiceEET = $domtree->createElement('inv:EET');
            $invoiceEET = $invoice->appendChild($invoiceEET);

            if (!empty($data['pkp'])) {

                $stateEET = 'externally';

            } elseif ($pay_method == 'draft' || $pay_method == 'delivery') {

                $stateEET = 'notEnter';

            } else {

                $stateEET = 'notSend';

            }

            $invoiceEET->appendChild($domtree->createElement('typ:stateEET', $stateEET));

            if ($stateEET == 'externally') {

                $detailEET = $domtree->createElement('typ:detailEET');
                $detailEET = $invoiceEET->appendChild($detailEET);

                $detailEET->appendChild($domtree->createElement('typ:numberOfDocument', $invoices['id']));
                $detailEET->appendChild($domtree->createElement('typ:VATIdOfPayer', 'CZ29154871'));
                $detailEET->appendChild($domtree->createElement('typ:dateOfSale', $invoices['datetime'] . '+01:00'));
                $detailEET->appendChild($domtree->createElement('typ:price', $total_order));
                $detailEET->appendChild($domtree->createElement('typ:PKP', $invoices['pkp']));
                $detailEET->appendChild($domtree->createElement('typ:BKP', $invoices['bkp']));
                $detailEET->appendChild($domtree->createElement('typ:FIK', $invoices['fik']));
                $detailEET->appendChild($domtree->createElement('typ:establishment', '11'));
                $detailEET->appendChild($domtree->createElement('typ:cashDevice', 'AWT'));
                $detailEET->appendChild($domtree->createElement('typ:mode', 'current'));
                $detailEET->appendChild($domtree->createElement('typ:dateOfSend', $invoices['datetime']));
                $detailEET->appendChild($domtree->createElement('typ:dateOfAcceptance', $invoices['datetime']));

            }

            $mysqli->query("UPDATE orders_invoices SET export_id = '$exportId' WHERE id = '" . $invoices['id'] . "'") or die($mysqli->error);

        }

        /* get the xml printed */
        $domtree->save($_SERVER['DOCUMENT_ROOT'] . '/admin/data/export/invoices_orders/orders_weekly_invoices_' . $currentDate . '.xml');

    }


    echo $listOfPrices;

    if($firstMail){

        $subject = 'Balíček objednávek - ' . $currentDate;
        $title = 'Balíček objednávek - ' . $currentDate;

        $opening_text = '<p style="margin: 0 0 16px;">V administračním rozhraní WellnessTrade.cz byl automaticky vygenerován XML balíček objednávek:</p>
    
              <table cellspacing="1" cellpadding="1" border="0" style="margin: 20px 0; border: 1px solid #dcdcdc; padding: 23px 30px 22px; line-height: 26px; background: #fbfbfb;">
              <tbody>
    
                <tr>
                  <td style="width: 120px;">ID balíčku:</td>
                  <td><strong>' . $currentDate . '</strong></td>
                </tr>
    
                <tr>
                  <td style="width: 120px;">Počet faktur:</td>
                  <td><strong>' . $count . '</strong></td>
                </tr>
    
                 <tr>
                  <td>Odkaz na balíček:</td>
                  <td style="font-size: 13px; line-height: 20px;">https://www.wellnesstrade.cz/admin/data/export/invoices_orders/orders_weekly_invoices_' . $currentDate . '.xml</td>
                </tr>
                
              </tbody>
              </table>
                  https://wellnesstrade.cz/admin/controllers/export/inv-orders-weekly?secretcode=lYspnYd2mYTJm6
    
              <p style="margin: 0 0 16px;">Vyexportovaný soubor XML je připraven k nahrání do účetního programu Pohoda.</p>';

        $body = '<div id="wrapper" dir="ltr" style="background-color: #f5f5f5; margin: 0; padding: 70px 0 70px 0; -webkit-text-size-adjust: none !important; width: 100%;">
                        <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%"><tbody><tr>
            <td align="center" valign="top">
                                    <div id="template_header_image">
                                        <p style="margin-top: 0; margin-bottom: 3em;"><img src="https://www.wellnesstrade.cz/wp-content/uploads/2015/03/logoblack.png" alt="Saunahouse.cz" style="border: none; display: inline; font-size: 14px; font-weight: bold; height: auto; line-height: 100%; outline: none; text-decoration: none; text-transform: capitalize;"></p>                        </div>
                                    <table border="0" cellpadding="0" cellspacing="0" width="780" id="template_container" style="box-shadow: 0 1px 4px rgba(0,0,0,0.1) !important; background-color: #fdfdfd; border: 1px solid #dcdcdc; border-radius: 3px !important;">
            <tbody><tr>
            <td align="center" valign="top">
                                                <!-- Header -->
                                                <table border="0" cellpadding="0" cellspacing="0" width="780" id="template_header" style="background-color: #2b303a; border-radius: 3px 3px 0 0 !important; color: #ffffff; border-bottom: 0; font-weight: bold; line-height: 100%; vertical-align: middle; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif;"><tbody><tr>
            <td id="header_wrapper" style="padding: 36px 48px; display: block;">
            <h1 style="color: #ffffff; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif; font-size: 30px; font-weight: 300; line-height: 150%; margin: 0; text-align: left; text-shadow: 0 1px 0 #aa3351; -webkit-font-smoothing: antialiased;">' . $title . '</h1>
                                                        </td>
                                                    </tr></tbody></table>
            <!-- End Header -->
            </td>
                                        </tr>
            <tr>
            <td align="center" valign="top">
                                                <!-- Body -->
                                                <table border="0" cellpadding="0" cellspacing="0" width="780" id="template_body"><tbody><tr>
            <td valign="top" id="body_content" style="background-color: #fdfdfd;">
                                                            <!-- Content -->
                                                            <table border="0" cellpadding="20" cellspacing="0" width="100%"><tbody><tr>
            <td valign="top" style="padding: 48px;">
                                                                        <div id="body_content_inner" style="color: #737373; font-family: &quot;Helvetica Neue&quot;, Helvetica, Roboto, Arial, sans-serif; font-size: 14px; line-height: 150%; text-align: left;">
            ' . $opening_text . '
    
                                                                        </div>
                                                                    </td>
                                                                </tr></tbody></table>
            <!-- End Content -->
            </td>
                                                    </tr></tbody></table>
            <!-- End Body -->
            </td>
                                        </tr>
            <tr>
            <td align="center" valign="top">
    
            <!-- End Footer -->
            </td>
                                        </tr>
            </tbody></table>
    
            </td>
            </tr>
            </tbody>
            </table>
            </div>';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        //$mail->SMTPDebug = 3;                               // Enable verbose debug output
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host = 'mail.webglobe.cz'; // Specify main and backup SMTP servers
        $mail->SMTPAuth = true; // Enable SMTP authentication
        $mail->Username = 'admin@wellnesstrade.cz'; // SMTP username
        $mail->Password = 'RD4ufcLv'; // SMTP password
        $mail->SMTPSecure = 'ssl'; // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 465; // TCP port to connect to

        $mail->From = 'admin@wellnesstrade.cz';
        $mail->FromName = 'WellnessTrade.cz';
        //$mail->addAddress('ucetni@wellnesstrade.cz','Marcela Sosnová');

        $data_query = $mysqli->query("SELECT * FROM administration_settings WHERE param = 'advance_invoices_export'")or die($mysqli->error);
        $data = mysqli_fetch_assoc($data_query);

        // todo change email
        $mail->addAddress($data['value']);

        $mail->DKIM_domain = 'wellnesstrade.cz';
        $mail->DKIM_private = $_SERVER['DOCUMENT_ROOT'] . '/admin/config/keys/private.key';
        $mail->DKIM_selector = 'phpmailer';
        $mail->DKIM_passphrase = '';
        $mail->DKIM_identity = 'admin@wellnesstrade.cz';

        $mail->isHTML(true); // Set email   format to HTML

        $mail->Subject = $subject;
        $mail->Body = $body;

        if (!$mail->send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        }

    }


    echo '<a href="https://www.wellnesstrade.cz/admin/data/export/invoices_orders/orders_weekly_invoices_' . $currentDate . '.xml" target="_blank">https://www.wellnesstrade.cz/admin/data/export/invoices_orders/orders_weekly_invoices_' . $currentDate . '.xml</a>';


} else {

    echo 'Error - spatny tajny kod.';

}



echo $count;