<?php

if (isset($_REQUEST['secretcode']) && $_REQUEST['secretcode'] == "lYspnYd2mYTJm6TEST") {

    include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";

    function returnpn($customer, $product)
    {
        global $mysqli;

        $brand_query = $mysqli->query("SELECT brand, fullname FROM warehouse_products WHERE connect_name = '$product'") or die($mysqli->error);
        $brand = mysqli_fetch_array($brand_query);

        return $brand['brand'] . ' ' . ucfirst($brand['fullname']);

    }

    $id = date('Y-m-d', strtotime("-2 week"));
    $date = date('Y-m-d', strtotime("-2 week"));

    if (date('Y-m') == date('Y-01')) {

        $year = date('Y', strtotime("-1 year"));

        $year = date('Y', strtotime("-1 year"));

    } else {

        $year = date('Y');
    }

    $invoices_query = $mysqli->query("SELECT *, i.id as id, o.id as demand_id, DATE_FORMAT(i.date, '%Y-%m-%d') as date, DATE_FORMAT(i.date, '%Y-%m-%dT%H:%i:%s') as datetime FROM demands_generate o, demands_advance_invoices i WHERE i.demand_id = o.id AND YEAR(i.date) = '$year' AND YEARWEEK(i.date) = YEARWEEK(NOW() - INTERVAL 2 WEEK) ORDER BY i.id") or die($mysqli->error);

    $count = 0;

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
        $xmlRoot->setAttribute('id', $id);
        $xmlRoot->setAttribute('ico', '29154871');
        $xmlRoot->setAttribute('application', 'Wellness Trade App');
        $xmlRoot->setAttribute('note', 'Zalohove faktury za virivky a sauny');

        while ($invoices = mysqli_fetch_array($invoices_query)) {

            $count++;

            $address_query = $mysqli->query("SELECT * FROM addresses_invoices WHERE id = '" . $invoices['address_id'] . "'") or die($mysqli->error);
            $address_invoice = mysqli_fetch_array($address_query);

            $payment_query = $mysqli->query("SELECT name, eet, pohoda_id FROM shops_payment_methods WHERE link_name_internal = '" . $invoices['payment_method'] . "'") or die($mysqli->error);
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

            $invoice_type = 'issuedAdvanceInvoice';

            $invoiceHeader->appendChild($domtree->createElement('inv:invoiceType', $invoice_type));

            $number = $domtree->createElement('inv:number');
            $number = $invoiceHeader->appendChild($number);

            $number->appendChild($domtree->createElement('typ:numberRequested', $invoices['id']));

            $invoiceHeader->appendChild($domtree->createElement('inv:symVar', $invoices['id']));
            $invoiceHeader->appendChild($domtree->createElement('inv:date', $invoices['date']));
            $invoiceHeader->appendChild($domtree->createElement('inv:dateTax', $invoices['date']));
            $invoiceHeader->appendChild($domtree->createElement('inv:dateAccounting', $invoices['date']));

            if (isset($invoices['payment_method']) && $invoices['payment_method'] == 'cash') {

                $invoiceHeader->appendChild($domtree->createElement('inv:dateDue', $invoices['date']));

            } else {

                $due_date = date('Y-m-d', strtotime($invoices['date'] . ' + 14 days'));

                $invoiceHeader->appendChild($domtree->createElement('inv:dateDue', $due_date));

            }

            $accounting = $domtree->createElement('inv:accounting');
            $accounting = $invoiceHeader->appendChild($accounting);

            $accounting->appendChild($domtree->createElement('typ:ids', '3FV'));

            $classificationVAT = $domtree->createElement('inv:classificationVAT');
            $classificationVAT = $invoiceHeader->appendChild($classificationVAT);

            if (isset($invoices['reverse_charge']) && $invoices['reverse_charge'] == 'Ano') {

                $classificationVAT->appendChild($domtree->createElement('typ:ids', 'UDpdp'));

            } elseif ($invoices['total_price'] >= 10000) {

                $classificationVAT->appendChild($domtree->createElement('typ:ids', 'UDA5'));

            } else {

                $classificationVAT->appendChild($domtree->createElement('typ:ids', 'UD'));

            }

            if (isset($invoices['reverse_charge']) && $invoices['reverse_charge'] == 'Ano') {

                $invoice_text = 'Fakturujeme Vám za zboží a jeho instalaci (kompletaci): (PDP)';

            } else {

                $invoice_text = 'Fakturujeme Vám za zboží a jeho instalaci (kompletaci).';

            }
            $invoiceHeader->appendChild($domtree->createElement('inv:text', $invoice_text));

            $partnerIdentity = $domtree->createElement('inv:partnerIdentity');
            $partnerIdentity = $invoiceHeader->appendChild($partnerIdentity);

            $address = $domtree->createElement('typ:address');
            $address = $partnerIdentity->appendChild($address);

            $address->appendChild($domtree->createElement('typ:company', htmlspecialchars($address_invoice['billing_company'])));
            $address->appendChild($domtree->createElement('typ:name', $address_invoice['billing_name'] . ' ' . $address_invoice['billing_surname']));
            $address->appendChild($domtree->createElement('typ:city', $address_invoice['billing_city']));
            $address->appendChild($domtree->createElement('typ:street', $address_invoice['billing_street']));
            $address->appendChild($domtree->createElement('typ:zip', $address_invoice['billing_zipcode']));
            $address->appendChild($domtree->createElement('typ:ico', $address_invoice['billing_ico']));
            $address->appendChild($domtree->createElement('typ:dic', $address_invoice['billing_dic']));

            $paymentType = $domtree->createElement('inv:paymentType');
            $paymentType = $invoiceHeader->appendChild($paymentType);

            $paymentType->appendChild($domtree->createElement('typ:paymentType', $pay_method));

            $account = $domtree->createElement('inv:account');
            $account = $invoiceHeader->appendChild($account);

            $account->appendChild($domtree->createElement('typ:accountNo', '2000364217'));
            $account->appendChild($domtree->createElement('typ:bankCode', '2010'));

            $invoiceHeader->appendChild($domtree->createElement('inv:note', 'Načteno z XML'));
            $invoiceHeader->appendChild($domtree->createElement('inv:intNote', 'Tento doklad byl vytvořen importem přes XML.'));

            $centre = $domtree->createElement('inv:centre');
            $centre = $invoiceHeader->appendChild($centre);

            $activity = $domtree->createElement('inv:activity');
            $activity = $invoiceHeader->appendChild($activity);

            $activity->appendChild($domtree->createElement('typ:ids', 'Zboží'));

            $invoiceDetail = $domtree->createElement('inv:invoiceDetail');
            $invoiceDetail = $invoice->appendChild($invoiceDetail);

            $invoiceItem = $domtree->createElement('inv:invoiceItem');
            $invoiceItem = $invoiceDetail->appendChild($invoiceItem);

            if ($invoices['special_name'] != "") {

                $name = $invoices['special_name'];

            } else {

                $demand_query = $mysqli->query("SELECT * FROM demands WHERE id = '" . $invoices['demand_id'] . "'");

                $demand = mysqli_fetch_array($demand_query);

                $name = returnpn($demand['customer'], $demand['product']);

            }

            $invoiceItem->appendChild($domtree->createElement('inv:text', $name));

            $invoiceItem->appendChild($domtree->createElement('inv:quantity', '1'));

            $invoiceItem->appendChild($domtree->createElement('inv:unit', 'ks'));

            $invoiceItem->appendChild($domtree->createElement('inv:payVAT', 'false'));

            if (isset($invoices['reverse_charge']) && $invoices['reverse_charge'] == 'Ano') {

                $vat = 'none';
                $coeficient = '0';

            } else {

                if (isset($invoices['price_vat']) && $invoices['price_vat'] == '21') {
                    $vat = 'high';
                    $coeficient = '0.21';
                } elseif (isset($invoices['price_vat']) && $invoices['price_vat'] == '15') {
                    $vat = 'low';
                    $coeficient = '0.15';
                } elseif (isset($invoices['price_vat']) && $invoices['price_vat'] == '12') {
                    $vat = 'low';
                    $coeficient = '0.12';
                } elseif (isset($invoices['price_vat']) && $invoices['price_vat'] == '10') {
                    $vat = 'third';
                    $coeficient = '0.10';
                } elseif (isset($invoices['price_vat']) && $invoices['price_vat'] == '0') {
                    $vat = 'none';
                    $coeficient = '0';
                }

            }

            $invoiceItem->appendChild($domtree->createElement('inv:rateVAT', $vat));

            $classificationVAT = $domtree->createElement('inv:classificationVAT');
            $classificationVAT = $invoiceItem->appendChild($classificationVAT);

            if (isset($invoices['reverse_charge']) && $invoices['reverse_charge'] == 'Ano') {

                $classificationVAT->appendChild($domtree->createElement('typ:ids', 'UDpdp'));

            } elseif ($invoices['total_price'] >= 10000) {

                $classificationVAT->appendChild($domtree->createElement('typ:ids', 'UDA5'));

            } else {

                $classificationVAT->appendChild($domtree->createElement('typ:ids', 'UD'));

            }

            $homeCurrency = $domtree->createElement('inv:homeCurrency');
            $homeCurrency = $invoiceItem->appendChild($homeCurrency);

            $product_vat = number_format($invoices['price_without_vat'] * $coeficient, 2, '.', '');

            $product_without_vat = number_format($invoices['price_without_vat'] - $product_vat, 2, '.', '');

            $homeCurrency->appendChild($domtree->createElement('typ:unitPrice', $invoices['price_without_vat']));

            $homeCurrency->appendChild($domtree->createElement('typ:price', $product_without_vat));
            $homeCurrency->appendChild($domtree->createElement('typ:priceVAT', $product_vat));

            if (isset($demand['customer']) && $demand['customer'] == '3') {

            }

            $invoiceSummary = $domtree->createElement('inv:invoiceSummary');
            $invoiceSummary = $invoice->appendChild($invoiceSummary);

            $invoiceSummary->appendChild($domtree->createElement('inv:roundingDocument', 'none'));

            $invoiceSummary->appendChild($domtree->createElement('inv:roundingVAT', 'none'));

            $invoiceSummary->appendChild($domtree->createElement('inv:calculateVAT', 'false'));

            $homeCurrency = $domtree->createElement('inv:homeCurrency');
            $homeCurrency = $invoiceSummary->appendChild($homeCurrency);

            if (isset($invoices['price_vat']) && $invoices['price_vat'] == '21' && $invoices['reverse_charge'] == 'Ne') {

                $homeCurrency->appendChild($domtree->createElement('typ:priceHighSum', $invoices['total_price']));

                $homeCurrency->appendChild($domtree->createElement('typ:priceHigh', $invoices['price_without_vat']));

            } elseif (isset($invoices['price_vat']) && $invoices['price_vat'] == '15' && $invoices['reverse_charge'] == 'Ne') {

                $homeCurrency->appendChild($domtree->createElement('typ:priceLowSum', $invoices['total_price']));

                $homeCurrency->appendChild($domtree->createElement('typ:priceLow', $invoices['price_without_vat']));

            } elseif (isset($invoices['price_vat']) && $invoices['price_vat'] == '12' && $invoices['reverse_charge'] == 'Ne') {

                $homeCurrency->appendChild($domtree->createElement('typ:priceLowSum', $invoices['total_price']));

                $homeCurrency->appendChild($domtree->createElement('typ:priceLow', $invoices['price_without_vat']));

            } elseif (isset($invoices['price_vat']) && $invoices['price_vat'] == '10' && $invoices['reverse_charge'] == 'Ne') {

                $homeCurrency->appendChild($domtree->createElement('typ:price3Sum', $invoices['total_price']));

                $homeCurrency->appendChild($domtree->createElement('typ:price3', $invoices['price_without_vat']));

            } elseif (isset($invoices['price_vat']) && $invoices['price_vat'] == '0') {

                $homeCurrency->appendChild($domtree->createElement('typ:priceNone', $invoices['price_without_vat']));

            }

            $invoiceEET = $domtree->createElement('inv:EET');
            $invoiceEET = $invoice->appendChild($invoiceEET);

            $invoiceEET->appendChild($domtree->createElement('typ:stateEET', 'notEnter'));

            $update_query = $mysqli->query("UPDATE demands_advance_invoices SET export_id = '$id' WHERE id = '" . $invoices['balicekid'] . "'") or die($mysqli->error);

        }

        /* get the xml printed */
        $domtree->save($_SERVER['DOCUMENT_ROOT'] . '/admin/data/demands_invoices/demands_weekly_invoices_' . $id . '.xml');

    }

//    $find_query = $mysqli->query("SELECT * FROM demands_advance_invoices_exports WHERE date = '$date'") or die($mysqli->error);
//
//    if (mysqli_num_rows($find_query) > 0) {
//
//        $insert_query = $mysqli->query("UPDATE demands_advance_invoices_exports SET invoice_number = '$count' WHERE date = '$date'") or die($mysqli->error);
//
//    } else {
//
//        $insert_query = $mysqli->query("INSERT INTO demands_advance_invoices_exports (date, invoice_number) VALUES ('$date', '$count')") or die($mysqli->error);
//
//    }



    $subject = 'Balíček zálohových faktur - ' . $id;

    $title = 'Balíček zálohových faktur - ' . $id;

    $opening_text = '<p style="margin: 0 0 16px;">V administračním rozhraní WellnessTrade.cz byl automaticky vygenerován XML balíček zálohových faktur:</p>

          <table cellspacing="1" cellpadding="1" border="0" style="margin: 20px 0; border: 1px solid #dcdcdc; padding: 23px 30px 22px; line-height: 26px; background: #fbfbfb;">
          <tbody>

            <tr>
              <td style="width: 120px;">ID balíčku:</td>
              <td><strong>' . $id . '</strong></td>
            </tr>

            <tr>
              <td style="width: 120px;">Počet faktur:</td>
              <td><strong>' . $count . '</strong></td>
            </tr>

             <tr>
              <td>Odkaz na balíček:</td>
              <td style="font-size: 13px; line-height: 20px;">https://www.wellnesstrade.cz/admin/data/demands_invoices/demands_weekly_invoices_' . $id . '.xml</td>
            </tr>

          </tbody>
          </table>
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
                        </tr></tbody></table>
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
    $mail->addAddress('becher.filip@gmail.com', 'Filip Becher');
    //$mail->addAddress('ucetni@wellnesstrade.cz','Marcela Sosnová');

    //$mail->addAddress('becher.filip@gmail.com','Filip Becher');

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

    echo '<a href="https://www.wellnesstrade.cz/admin/data/demands_invoices/demands_weekly_invoices_' . $id . '.xml" target="_blank">https://www.wellnesstrade.cz/admin/data/demands_invoices/demands_weekly_invoices_' . $id . '.xml</a>';

} else {

    echo 'Error - spatny tajny kod.';

}
