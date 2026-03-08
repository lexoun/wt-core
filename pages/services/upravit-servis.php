<?php
// =============================================================================
// INICIALIZACE
// =============================================================================

include $_SERVER['DOCUMENT_ROOT'] . '/admin/config/config.php';
include INCLUDES . '/googlelogin.php';
include INCLUDES . '/functions.php';

$id = $_REQUEST['id'];

$service_query = $mysqli->query('SELECT *, DATE_FORMAT(date, "%d. %m. %Y") as dateformated FROM services WHERE id="' . $id . '"') or die($mysqli->error);

if (mysqli_num_rows($service_query) === 0) {
    include INCLUDES . '/404.php';
    exit;
}

$service = mysqli_fetch_assoc($service_query);

// =============================================================================
// POMOCNÉ FUNKCE
// =============================================================================

/**
 * Vrátí hodnotu z $_POST nebo výchozí hodnotu.
 */
function post(string $key, $default = ''): string
{
    return isset($_POST[$key]) ? $_POST[$key] : $default;
}

/**
 * Odstraní bílé znaky z POST hodnoty.
 */
function postTrim(string $key): string
{
    return preg_replace('/\s+/', '', post($key));
}

/**
 * Vrátí atribut disabled/opacity style pokud jsou splněny podmínky blokování.
 */
function lockedStyle(bool $forceRegenerate, bool $hasInvoice, string $paymentMethod): string
{
    if (($forceRegenerate && $paymentMethod === 'cash') || ($hasInvoice && !$forceRegenerate)) {
        return 'style="pointer-events: none; opacity: 0.5;"';
    }
    return '';
}

/**
 * Zpracuje soubor servisního listu (nahrání nebo zachování existujícího).
 */
function handleServiceFile(array $service, array $clientname, mysqli $db): void
{
    $strpad   = str_pad($service['filenum'], 3, '0', STR_PAD_LEFT);
    $filename = 'Servisni-list-' . $strpad . '.' . $service['extension'];
    $basePath = $_SERVER['DOCUMENT_ROOT'] . '/admin/data/files/servis/' . $clientname['secretstring'] . '/';
    $path     = $basePath . $filename;

    if (file_exists($path)) {
        // Soubor existuje – není potřeba nic dělat
        return;
    }

    if ($service['filenum'] != 0) {
        // Soubor byl přejmenován nebo přesunut – znovu nahraj
        $upfile     = $_FILES['servicelist']['name'];
        $ext        = end((explode('.', $upfile)));
        $updatefile = str_pad($service['filenum'], 3, '0', STR_PAD_LEFT);
        $filenm     = 'Servisni-list-' . $updatefile . '.' . $ext;

        move_uploaded_file($_FILES['servicelist']['tmp_name'], $basePath . $filenm);
        return;
    }

    // Nový soubor – přiřadit nové číslo
    if (!empty($_FILES['servicelist']['name'])) {
        $upfile    = $_FILES['servicelist']['name'];
        $ext       = end((explode('.', $upfile)));
        $filenumq  = $db->query('SELECT filenum FROM services WHERE clientid="' . $clientname['id'] . '" ORDER BY filenum DESC LIMIT 1') or die($db->error);
        $filenumb  = mysqli_fetch_assoc($filenumq);
        $updatefl  = $filenumb['filenum'] + 1;
        $updatefile = str_pad($updatefl, 3, '0', STR_PAD_LEFT);
        $filenm    = 'Servisni-list-' . $updatefile . '.' . $ext;

        move_uploaded_file($_FILES['servicelist']['tmp_name'], $basePath . $filenm);
    }
}

/**
 * Uloží nebo aktualizuje fakturační adresu. Vrátí billing_id.
 */
function saveBillingAddress(mysqli $db, array $service): int
{
    $zipcode = postTrim('billing_zipcode');
    $phone   = postTrim('billing_phone');
    $email   = postTrim('billing_email');

    if ($service['billing_id'] != '0') {
        $db->query("UPDATE addresses_billing SET
            billing_company  = '" . post('billing_company') . "',
            billing_name     = '" . post('billing_name') . "',
            billing_surname  = '" . post('billing_surname') . "',
            billing_street   = '" . post('billing_street') . "',
            billing_city     = '" . post('billing_city') . "',
            billing_zipcode  = '$zipcode',
            billing_country  = '" . post('billing_country') . "',
            billing_ico      = '" . post('billing_ico') . "',
            billing_dic      = '" . post('billing_dic') . "',
            billing_email    = '$email',
            billing_phone    = '$phone'
            WHERE id = '" . $service['billing_id'] . "'") or die($db->error);

        return (int) $service['billing_id'];
    }

    $db->query("INSERT INTO addresses_billing (
        billing_company, billing_ico, billing_dic, billing_degree,
        billing_name, billing_surname, billing_street, billing_city,
        billing_zipcode, billing_country, billing_phone, billing_email
    ) VALUES (
        '" . post('billing_company') . "', '" . post('billing_ico') . "',
        '" . post('billing_dic') . "', '" . ($billing_degree ?? '') . "',
        '" . post('billing_name') . "', '" . post('billing_surname') . "',
        '" . post('billing_street') . "', '" . post('billing_city') . "',
        '$zipcode', '" . post('billing_country') . "', '$phone', '$email'
    )") or die($db->error);

    return (int) $db->insert_id;
}

/**
 * Uloží, aktualizuje nebo smaže doručovací adresu. Vrátí shipping_id.
 */
function saveShippingAddress(mysqli $db, array $service): int
{
    $hasShippingData = (
        post('shipping_company') !== '' || post('shipping_name') !== '' ||
        post('shipping_surname') !== '' || post('shipping_street') !== '' ||
        post('shipping_city') !== '' || post('shipping_zipcode') !== ''
    );

    $differentShipping = (isset($_POST['different_shipping']) && $_POST['different_shipping'] === 'yes');

    if ($hasShippingData && $differentShipping) {
        if ($service['shipping_id'] != '0') {
            $db->query("UPDATE addresses_shipping SET
                shipping_company  = '" . post('shipping_company') . "',
                shipping_name     = '" . post('shipping_name') . "',
                shipping_surname  = '" . post('shipping_surname') . "',
                shipping_street   = '" . post('shipping_street') . "',
                shipping_city     = '" . post('shipping_city') . "',
                shipping_zipcode  = '" . post('shipping_zipcode') . "',
                shipping_country  = '" . post('shipping_country') . "'
                WHERE id = '" . $service['shipping_id'] . "'") or die($db->error);

            return (int) $service['shipping_id'];
        }

        $db->query("INSERT INTO addresses_shipping (
            shipping_company, shipping_name, shipping_surname,
            shipping_street, shipping_city, shipping_zipcode, shipping_country
        ) VALUES (
            '" . post('shipping_company') . "', '" . post('shipping_name') . "',
            '" . post('shipping_surname') . "', '" . post('shipping_street') . "',
            '" . post('shipping_city') . "', '" . post('shipping_zipcode') . "',
            '" . post('shipping_country') . "'
        )") or die($db->error);

        return (int) $db->insert_id;
    }

    if (!$differentShipping && $service['shipping_id'] != 0) {
        $db->query("DELETE FROM addresses_shipping WHERE id = '" . $service['shipping_id'] . "'") or die($db->error);
    }

    return 0;
}

/**
 * Aktualizuje hlavní záznam servisu – verze s fakturou (omezená pole).
 */
function updateServiceWithInvoice(mysqli $db, array $service, int $billingId, int $shippingId): void
{
    $db->query("UPDATE services SET
        creator_id        = '" . post('creator_id') . "',
        category          = '" . post('category') . "',
        billing_id        = '$billingId',
        shipping_id       = '$shippingId',
        shipping_details  = '" . $db->real_escape_string(post('shipping_details')) . "',
        estimatedtime     = '" . post('estimatedtime') . "',
        state             = '" . post('state') . "',
        date              = '" . post('date') . "',
        date_added        = '" . post('date_added') . "',
        details           = '" . $db->real_escape_string(post('details')) . "',
        technical_details = '" . $db->real_escape_string(post('technical_details')) . "',
        internal_details  = '" . $db->real_escape_string(post('internal_details')) . "'
        WHERE id = '" . $_REQUEST['id'] . "'") or die($db->error);
}

/**
 * Aktualizuje hlavní záznam servisu – plná verze (bez faktury).
 */
function updateServiceFull(mysqli $db, array $service, int $billingId, int $shippingId): void
{
    $currency     = post('currency');
    $exchangeRate = post($currency . '_rate');

    $db->query("UPDATE services SET
        creator_id        = '" . post('creator_id') . "',
        currency          = '$currency',
        exchange_rate     = '$exchangeRate',
        category          = '" . post('category') . "',
        billing_id        = '$billingId',
        shipping_id       = '$shippingId',
        payment_method    = '" . post('payment') . "',
        delivery_price    = '" . post('delivery_price') . "',
        vat               = '" . post('vat') . "',
        shipping_details  = '" . $db->real_escape_string(post('shipping_details')) . "',
        estimatedtime     = '" . post('estimatedtime') . "',
        state             = '" . post('state') . "',
        date              = '" . post('date') . "',
        date_added        = '" . post('date_added') . "',
        details           = '" . $db->real_escape_string(post('details')) . "',
        technical_details = '" . $db->real_escape_string(post('technical_details')) . "',
        internal_details  = '" . $db->real_escape_string(post('internal_details')) . "',
        location_id       = '" . post('location') . "'
        WHERE id = '" . $_REQUEST['id'] . "'") or die($db->error);
}

/**
 * Smaže a znovu vloží položky servisu. Vrátí jejich celkovou cenu.
 */
function resaveServiceItems(mysqli $db, string $serviceId): float
{
    $db->query("DELETE FROM services_items WHERE service_id = '$serviceId'") or die($db->error);

    $total = 0.0;

    if (empty($_POST['service_item_name'])) {
        return $total;
    }

    foreach ($_POST['service_item_name'] as $key => $name) {
        if (empty($name)) {
            continue;
        }

        $price = (isset($_POST['service_item_price'][$key]) && $_POST['service_item_price'][$key] !== '')
            ? (float) $_POST['service_item_price'][$key]
            : 0.0;

        $total += $price;

        $db->query("INSERT INTO services_items (service_id, name, price)
                    VALUES ('$serviceId', '$name', '$price')") or die($db->error);
    }

    return $total;
}

/**
 * Zpracuje změny produktů (přidané, odebrané, beze změny).
 */
function syncProducts(mysqli $db, array $service, string $id): void
{
    $postProducts = isset($_POST['product_sku']) ? $_POST['product_sku'] : [];

    $findSimple   = $db->query("SELECT b.product_id, b.variation_id, b.reserved, p.code FROM products p, services_products_bridge b WHERE p.id = b.product_id AND b.aggregate_id = '$id' ORDER BY p.id DESC") or die($db->error);
    $findVariable = $db->query("SELECT b.product_id, b.variation_id, b.reserved, v.sku FROM products_variations v, services_products_bridge b WHERE v.id = b.variation_id AND b.aggregate_id = '$id'") or die($db->error);

    $existingSkus = [];
    while ($row = mysqli_fetch_assoc($findSimple))   { $existingSkus[] = $row['code']; }
    while ($row = mysqli_fetch_assoc($findVariable)) { $existingSkus[] = $row['sku']; }

    $filteredPost    = array_filter($postProducts);
    $removedProducts = array_diff($existingSkus, $filteredPost);
    $addedProducts   = array_diff($filteredPost, $existingSkus);
    $stableProducts  = array_intersect($existingSkus, $filteredPost);

    include_once CONTROLLERS . '/product-stock-controller.php';

    // Odebírané produkty
    foreach ($removedProducts as $removed) {
        $productQuery = $db->query("
            SELECT b.id, b.product_id, b.variation_id, b.reserved
            FROM products p, services_products_bridge b
            WHERE p.code = '$removed' AND b.variation_id = 0 AND p.id = b.product_id AND b.aggregate_id = '$id'
            UNION
            SELECT b.id, b.product_id, b.variation_id, b.reserved
            FROM products_variations v, services_products_bridge b
            WHERE v.sku = '$removed' AND v.id = b.variation_id AND b.variation_id != 0 AND b.aggregate_id = '$id'
        ") or die($db->error);

        if (mysqli_num_rows($productQuery) === 0) continue;

        $product = mysqli_fetch_assoc($productQuery);
        $db->query("DELETE FROM services_products_bridge WHERE id = '" . $product['id'] . "'");
        product_update($product['product_id'], $product['variation_id'], post('location'), $product['reserved'], $client['id'], 'service_change', $id);
    }

    // Všechny odesílané produkty (přidané i stávající)
    if (!empty($filteredPost)) {
        foreach ($filteredPost as $postIndex => $sku) {
            $quantity = $_POST['product_quantity'][$postIndex] ?? 0;

            $stockAllocation = [
                'posterino'      => $sku,
                'id'             => $id,
                'bridge'         => 'services_products_bridge',
                'id_identify'    => 'aggregate_id',
                'quantity'       => $quantity,
                'total_quantity' => $quantity,
                'location'       => post('location'),
                'type'           => 'service',
                'price'          => product_price(
                    $_POST['product_price'][$postIndex],
                    $_POST['product_original_price'][$postIndex],
                    post('vat'),
                    $service['vat'],
                    $_POST['product_discount'][$postIndex]
                ),
            ];

            if (in_array($sku, $addedProducts)) {
                if (!empty($quantity)) {
                    include_once CONTROLLERS . '/product-stock-update.php';
                    stock_allocate($stockAllocation);
                }
            } elseif (in_array($sku, $stableProducts)) {
                $productQuery = $db->query("
                    SELECT p.price, p.productname, b.product_id, b.variation_id, p.delivery_time,
                           b.reserved, b.quantity, b.delivered, b.id, cat.discount, p.purchase_price, p.ean
                    FROM products p, services_products_bridge b, products_cats cat, products_sites_categories minicat
                    WHERE minicat.category = cat.seoslug AND p.code = '$sku'
                      AND p.id = b.product_id AND b.aggregate_id = '$id'
                    GROUP BY p.id
                    UNION
                    SELECT v.price, p.productname, b.product_id, b.variation_id, p.delivery_time,
                           b.reserved, b.quantity, b.delivered, b.id, cat.discount, v.purchase_price, v.ean
                    FROM products p, services_products_bridge b, products_variations_sites s,
                         products_variations v, products_cats cat, products_sites_categories minicat
                    WHERE minicat.category = cat.seoslug AND minicat.product_id = p.id
                      AND v.product_id = p.id AND v.sku = '$sku'
                      AND v.id = b.variation_id AND b.aggregate_id = '$id'
                    GROUP BY v.id
                ") or die($db->error);

                if (mysqli_num_rows($productQuery) === 0) continue;

                $product       = mysqli_fetch_assoc($productQuery);
                $productDiscount = $_POST['product_discount'][$postIndex] ?? 0;
                $priceProduct  = product_price($stockAllocation['price']['price'], $product['price'], post('vat'), $service['vat'], $productDiscount);

                if ($quantity == $product['quantity']) {
                    // Pouze změna ceny
                    $db->query("UPDATE services_products_bridge SET
                        price        = '" . $stockAllocation['price']['price'] . "',
                        discount     = '" . $stockAllocation['price']['discount'] . "',
                        discount_net = '" . $stockAllocation['price']['discount_net'] . "'
                        WHERE id = '" . $product['id'] . "'") or die($db->error);

                } elseif ($quantity < $product['quantity'] && $quantity < ($product['reserved'] + $product['delivered'])) {
                    // Snížení množství
                    $reducedQuantity   = ($product['reserved'] + $product['delivered']) - $quantity;
                    $deliveredQuantity = min($reducedQuantity, $product['delivered']);

                    if ($deliveredQuantity > 0) {
                        product_delivered_update($product['product_id'], $product['variation_id'], $deliveredQuantity, 'service', $id);
                    }

                    $reservedQuantity = $reducedQuantity - $deliveredQuantity;
                    if ($reservedQuantity > 0) {
                        product_update($product['product_id'], $product['variation_id'], post('location'), $reservedQuantity, $client['id'], 'service_change', $id);
                    }

                    $finalReserved = $product['reserved'] - $reservedQuantity;
                    $db->query("UPDATE services_products_bridge SET
                        quantity     = '$quantity',
                        reserved     = '$finalReserved',
                        delivered    = delivered - $deliveredQuantity,
                        price        = '" . $priceProduct['price'] . "',
                        discount     = '$productDiscount',
                        discount_net = '" . $priceProduct['discount_net'] . "'
                        WHERE id = '" . $product['id'] . "'");

                } elseif ($quantity > $product['quantity'] && $quantity > ($product['reserved'] + $product['delivered'])) {
                    // Zvýšení množství
                    $totalQuantity = $quantity;
                    $quantity -= ($product['reserved'] + $product['delivered']);

                    include_once CONTROLLERS . '/product-stock-update.php';
                    $response = stock_allocate($stockAllocation);

                    if ($response['reserve'] < $quantity) {
                        $quantity -= $response['reserve'];
                        include CONTROLLERS . '/product-delivery-update.php';
                    }

                    $db->query("UPDATE services_products_bridge SET quantity = '$totalQuantity' WHERE id = '" . $product['id'] . "'");

                } else {
                    $db->query("UPDATE services_products_bridge SET quantity = '$quantity' WHERE id = '" . $product['id'] . "'");
                }
            }
        }
    }
}

/**
 * Přepočítá a uloží celkovou cenu servisu.
 */
function updateServiceTotal(mysqli $db, string $serviceId, float $serviceItemsPrice): void
{
    $result = $db->query("
        SELECT SUM(total) AS total, SUM(purchase_price) AS purchase_price, SUM(discountRounded) AS discount_net
        FROM (
            SELECT
                ROUND(((price - discount_net) * quantity), 2) AS total,
                (purchase_price * quantity) AS purchase_price,
                ROUND(discount_net * quantity, 2) AS discountRounded
            FROM services_products_bridge
            WHERE aggregate_id = '$serviceId'
        ) AS products
    ") or die($db->error);

    $priceData = mysqli_fetch_array($result);

    $deliveryPrice = (post('delivery_price') !== '') ? (float) post('delivery_price') : 0.0;
    $overallPrice  = (float) $priceData['total'] + $deliveryPrice + $serviceItemsPrice;
    $purchaseTotal = (float) $priceData['purchase_price'];

    $coeficient = vat_coeficient(post('vat'));
    $price      = get_price($overallPrice, $coeficient);

    $price['rounded'] = 0;
    if (in_array(post('payment'), ['cash', 'cod'])) {
        $price['single']  = round($price['single']);
        $price['rounded'] = number_format($price['single'] - $overallPrice, 2, '.', '');
    }

    $db->query("UPDATE services SET
        total_vat         = '" . $price['vat'] . "',
        total_rounded     = '" . $price['rounded'] . "',
        total_without_vat = '" . $price['without_vat'] . "',
        price             = '" . $price['single'] . "',
        service_purchase  = '$purchaseTotal',
        discount_net      = '" . $priceData['discount_net'] . "'
        WHERE id = '$serviceId'") or die($db->error);
}

/**
 * Přesune zásoby do nového skladu, pokud se sklad změnil.
 */
function relocateWarehouse(mysqli $db, array $service, string $id): void
{
    if ($service['location_id'] === post('location')) {
        return;
    }

    $bridgeQuery = $db->query("
        SELECT b.*, p.code, v.sku
        FROM services_products_bridge b
        LEFT JOIN products p ON p.id = b.product_id
        LEFT JOIN products_variations v ON v.product_id = b.product_id AND v.id = b.variation_id
        WHERE aggregate_id = '" . $service['id'] . "'
    ") or die($db->error);

    while ($product = mysqli_fetch_assoc($bridgeQuery)) {
        $sku = !empty($product['sku']) ? $product['sku'] : $product['code'];

        $stockAllocation = [
            'posterino'      => $sku,
            'id'             => $id,
            'bridge'         => 'services_products_bridge',
            'id_identify'    => 'aggregate_id',
            'quantity'       => $product['quantity'],
            'total_quantity' => $product['quantity'],
            'location'       => post('location'),
            'type'           => 'service',
            'price'          => product_price(
                $product['price'],
                $product['original_price'],
                post('vat'),
                $service['vat'],
                $product['discount']
            ),
        ];

        if ($product['reserved'] > 0) {
            product_update($product['product_id'], $product['variation_id'], $product['location_id'], $product['reserved'], $client['id'], 'service_change', $product['id']);
        }

        $db->query("UPDATE services_products_bridge SET location_id = '" . $stockAllocation['location'] . "', reserved = 0 WHERE id = '" . $product['id'] . "'") or die($db->error);

        include_once CONTROLLERS . '/product-stock-update.php';
        $response = stock_allocate($stockAllocation);

        if ($response['reserve'] < $product['quantity']) {
            $quantity = $product['quantity'] - $response['reserve'];
            include CONTROLLERS . '/product-delivery-update.php';
        }
    }
}

/**
 * Zpracuje změnu stavu servisu a příslušné kalendářní události.
 */
function handleCalendarAndState(mysqli $db, array $service, string $id): void
{
    $isCanceling    = ($_POST['state'] === 'canceled' && $service['state'] !== 'canceled') || empty(post('date'));
    $isReactivating = ($service['gcalendar'] === '' && (($_POST['state'] !== 'canceled' && $service['state'] === 'canceled') || !empty(post('date'))));

    if ($service['gcalendar'] !== '' && $isCanceling) {
        $db->query("UPDATE services SET gcalendar = '' WHERE id = '$id'");

        $productsQuery = $db->query('SELECT b.product_id, b.reserved, p.customer, b.variation_id, b.location_id FROM services_products_bridge b, products p WHERE p.id = b.product_id AND b.aggregate_id="' . $_REQUEST['id'] . '"') or die($db->error);

        while ($product = mysqli_fetch_array($productsQuery)) {
            if ($product['reserved'] > 0) {
                product_update($product['product_id'], $product['variation_id'], $product['location_id'], $product['reserved'], $client['id'], 'service_cancel', $id);
            }
            $db->query("UPDATE services_products_bridge SET reserved = '0' WHERE product_id = '" . $product['product_id'] . "' AND aggregate_id = '" . $_REQUEST['id'] . "'");
        }

        calendarDelete($service['gcalendar']);

    } elseif ($isReactivating) {
        saveCalendarEvent($id, 'service');

        $searchQuery = $db->query("SELECT b.product_id, b.variation_id, b.quantity, b.location_id, s.instock FROM services_products_bridge b, products_stocks s WHERE s.product_id = b.product_id AND s.variation_id = b.variation_id AND s.location_id = b.location_id AND b.aggregate_id = '" . $_REQUEST['id'] . "'") or die($db->error);

        while ($item = mysqli_fetch_array($searchQuery)) {
            $reserve = min($item['quantity'], $item['instock']);

            $db->query("UPDATE services_products_bridge SET reserved = '$reserve' WHERE product_id = '" . $item['product_id'] . "' AND variation_id = '" . $item['variation_id'] . "' AND aggregate_id = '" . $_REQUEST['id'] . "'") or die($db->error);

            $db->query("UPDATE products_stocks SET instock = instock - $reserve WHERE product_id = '" . $item['product_id'] . "' AND variation_id = '" . $item['variation_id'] . "' AND location_id IN (SELECT id FROM shops_locations WHERE id = '" . $item['location_id'] . "')") or die($db->error);

            api_product_update($item['product_id']);
        }

    } elseif (!empty(post('date'))) {
        saveCalendarEvent($id, 'service');
    }
}

// =============================================================================
// NAČTENÍ ZÁKLADNÍCH DAT
// =============================================================================

$forceRegenerate = false;
$hasInvoice      = false;
$invoice_id      = 0;

$invoice_query = $mysqli->query("SELECT id, date, export_id FROM orders_invoices WHERE order_id = '$id' AND type = 'service' AND status != 'odd' ORDER BY id DESC");

if (mysqli_num_rows($invoice_query) > 0) {
    while ($invoice = mysqli_fetch_array($invoice_query)) {
        $hasInvoice = true;

        $correct_query = $mysqli->query("SELECT id FROM orders_invoices WHERE invoice_id = '" . $invoice['id'] . "' ORDER BY id DESC LIMIT 1");

        if (date('Y-m', strtotime($invoice['date'])) === date('Y-m') && mysqli_num_rows($correct_query) === 0) {
            $invoice_id      = $invoice['id'];
            $forceRegenerate = true;
        }

        if ($invoice['export_id'] != 0) {
            $forceRegenerate = false;
            $denyEdit        = true;
        }
    }
}

// Výjimka pro konkrétní e-mail
if ($client['email'] === 'becher@saunahouse.cz') {
    $hasInvoice      = false;
    $forceRegenerate = false;
}

$address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $service['shipping_id'] . '" WHERE b.id = "' . $service['billing_id'] . '"') or die($mysqli->error);
$address       = mysqli_fetch_assoc($address_query);

$clientnamequery = $mysqli->query('SELECT * FROM demands WHERE id = "' . $service['clientid'] . '"') or die($mysqli->error);
$clientname      = mysqli_fetch_array($clientnamequery);

// =============================================================================
// AKCE: SMAZÁNÍ SOUBORU
// =============================================================================

if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'removefile') {
    $strpad   = str_pad($service['filenum'], 3, '0', STR_PAD_LEFT);
    $filename = 'Servisni-list-' . $strpad . '.' . $service['extension'];
    $path     = $_SERVER['DOCUMENT_ROOT'] . '/admin/data/files/servis/' . $clientname['secretstring'] . '/' . $filename;

    unlink($path);

    $redirectUrl = 'https://www.wellnesstrade.cz/admin/pages/services/upravit-servis?id=' . $service['id'];
    if (isset($_REQUEST['client'])) {
        $redirectUrl .= '&client=' . $_REQUEST['client'];
    }

    header('Location:' . $redirectUrl);
    exit;
}

// =============================================================================
// AKCE: ULOŽENÍ ÚPRAV SERVISU
// =============================================================================

if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'edit') {

    handleServiceFile($service, $clientname, $mysqli);

    if ($service['clientid'] != 0) {
        $_REQUEST['customer'] = $clientname['customer'];
    }

    $billingId  = saveBillingAddress($mysqli, $service);
    $shippingId = saveShippingAddress($mysqli, $service);

    if ($hasInvoice && !$forceRegenerate) {
        updateServiceWithInvoice($mysqli, $service, $billingId, $shippingId);
    } else {
        updateServiceFull($mysqli, $service, $billingId, $shippingId);

        // Proveditelé a informovaní
        $mysqli->query("DELETE FROM mails_recievers WHERE type_id = '$id' AND type = 'service'") or die($mysqli->error);

        $performers = !empty($_POST['performer']) ? array_filter($_POST['performer']) : [''];
        $observers  = !empty($_POST['observer'])  ? array_filter($_POST['observer'])  : [''];

        if (!empty($performers) || !empty($observers)) {
            recievers($performers, $observers, 'service', $id);
        }

        $serviceItemsPrice = resaveServiceItems($mysqli, $id);

        syncProducts($mysqli, $service, $id);

        updateServiceTotal($mysqli, $id, $serviceItemsPrice);

        relocateWarehouse($mysqli, $service, $id);

        include_once CONTROLLERS . '/product-stock-controller.php';

        handleCalendarAndState($mysqli, $service, $id);
    }

    // Odeslání mailu
    $hasMail = '';
    if (post('send_mail') === 'yes') {
        include CONTROLLERS . '/mails/services.php';
        $hasMail = '&has_mail=true';
    }

    // Přegenerování faktury
    if ($forceRegenerate) {
        $redirect = 'https://www.wellnesstrade.cz/admin/pages/services/zobrazit-servis?id=' . $id;
        include CONTROLLERS . '/generators/order_invoice_regenerate.php';
    }

    header('Location:https://www.wellnesstrade.cz/admin/pages/services/zobrazit-servis?id=' . $id . '&success=edit' . $hasMail);
    exit;
}

// =============================================================================
// NAČTENÍ DAT PRO VIEW
// =============================================================================

$pagetitle = 'Upravit servis';
$bread1    = 'Naplánované servisy';
$abread1   = 'naplanovane-servisy';

$kurz     = ['CZK' => 1];
$kurzData = file_get_contents('http://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_devizoveho_trhu/denni_kurz.txt');
$lines    = explode("\n", $kurzData);
unset($lines[0], $lines[1], $lines[count($lines)]);

foreach ($lines as $line) {
    $parts = explode('|', $line);
    if (isset($parts[3], $parts[4])) {
        $kurz[trim($parts[3])] = str_replace(',', '.', trim($parts[4]));
    }
}

// Adresa pro Google Maps
if (!empty($address['shipping_street']) && !empty($address['shipping_city']) && !empty($address['shipping_zipcode'])) {
    $locationAddress = $address['shipping_city'] . ' ' . $address['shipping_zipcode'] . ' ' . $address['shipping_street'] . ' ' . $address['shipping_country'];
} else {
    $locationAddress = $address['billing_city'] . ' ' . $address['billing_zipcode'] . ' ' . $address['billing_street'] . ' ' . $address['billing_country'];
}

$lockedAttr = lockedStyle($forceRegenerate, $hasInvoice, $service['payment_method']);

include VIEW . '/default/header.php';
?>

<script type="text/javascript">
jQuery(document).ready(function ($) {

    // Přepínání měny
    $('.currency').change(function () {
        const rate     = $(this).data('value');
        const currency = $(this).val();

        if (currency !== 'CZK') {
            $('.calculator').show('slow');
        } else {
            $('.calculator').hide('slow');
        }

        $('.final_currency').attr('placeholder', currency).attr('data-rate', rate);
        $('.final_currency_shortcut').html(currency);
        $('.final_currency, .original_currency').val('');

        $('.price-control:visible').each(function () {
            const value = $(this).data('default') ?? $(this).val();
            if (value != null && value !== '') {
                $(this).val((value / rate).toFixed(2));
            }
        });
    });

    $('form').validate({ ignore: '' });
});
</script>

<style>
.has-warning .selectboxit-container .selectboxit { border-color: #ffd78a !important; }
.page-body .selectboxit-container .selectboxit-options { margin-top: 40px !important; width: 100% !important; }
.page-body .selectboxit-container .selectboxit { height: 40px; width: 100% !important; }
.page-body .selectboxit-container .selectboxit .selectboxit-text { line-height: 40px; }
.page-body .selectboxit-container .selectboxit .selectboxit-arrow-container { height: 40px; }
.page-body .selectboxit-container .selectboxit .selectboxit-arrow-container:after { line-height: 40px; }
.nicescroll-rails > div:hover { background: rgb(53, 174, 255) !important; }
#custom-scroller { width: 500px; }
.col-2, .col-8, .col-3, .col-4, .col-6 { display: inline-block; padding: 5px 2%; vertical-align: top; }
.item { margin-right: 10px; }
.col-2 { width: 18%; } .col-8 { width: 76%; } .col-3 { width: 26%; }
.col-4 { width: 36%; } .col-6 { width: 60%; }
.select2-drop img { width: 100%; margin: 2%; }
.bigdrop.select2-container .select2-results, .bigdrop .select2-results { max-height: 300px; }
</style>

<script type="text/javascript" src="//maps.google.com/maps/api/js?key=AIzaSyDRermPdr7opDFLqmrcOuK5L4zC2_U8XGk&sensor=false"></script>
<script type="text/javascript">
$.ajax({
    type: 'GET',
    url: '//maps.googleapis.com/maps/api/geocode/json?address=<?= urlencode($locationAddress) ?>&sensor=false&key=AIzaSyDWsYJWdJpuS_SgJ_0bpi0uOOGAGPBWsgk',
    dataType: 'json',
    success: function (data) {
        const lati = data.results[0].geometry.location.lat;
        const longi = data.results[0].geometry.location.lng;
        const directionsService = new google.maps.DirectionsService();

        directionsService.route({
            origin:      '50.096500, 14.402800',
            destination: lati + ',' + longi,
            travelMode:  google.maps.TravelMode.DRIVING,
        }, function (response, status) {
            const km = (response.routes[0].legs[0].distance.value / 1000).toFixed(0);
            $('#distance').html(km);
        });
    }
});
</script>

<!-- Upozornění na stav faktury -->
<?php if ($forceRegenerate) { ?>
    <div class="alert alert-warning"><strong>Upozornění!</strong> Upravujete servis, u kterého již byla vystavena faktura. Veškeré úpravy se AUTOMATICKY přegenerují do vystavené faktury.</div>
<?php } ?>
<?php if ($forceRegenerate && $service['payment_method'] === 'cash') { ?>
    <div class="alert alert-info"><strong>Upozornění!</strong> Servis byl fakturován s platbou <strong>Hotově</strong> a částky z toho důvodu nelze pozměnit (již posláno do EET).</div>
<?php } ?>
<?php if (!$forceRegenerate && $hasInvoice) { ?>
    <div class="alert alert-danger"><strong>Upozornění!</strong> Upravujete servis s již vyexportovanou fakturou. Faktura již byla importována do účetnictví a není tedy možné měnit jakékoliv její náležitosti.</div>
<?php } ?>

<!-- =====================================================================
     FORMULÁŘ – Úprava servisu
     ===================================================================== -->
<form role="form" method="post" autocomplete="off"
      class="form-horizontal form-groups-bordered validate"
      action="upravit-servis?action=edit&id=<?= $id ?>"
      enctype="multipart/form-data"
      id="orderform">

    <div class="row">

        <!-- LEVÝ SLOUPEC -->
        <div class="col-md-6">

            <!-- Panel: Informace o servisu -->
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title"><strong>Informace o servisu</strong></div>
                </div>
                <div class="panel-body">

                    <!-- Proveditelé -->
                    <div class="form-group well col-sm-12 admins_well" style="margin:0 auto 18px; padding:16px 0 10px 20px;">
                        <h4 style="text-align:center; margin-top:0; border-bottom:1px solid #e2e2e5; padding-bottom:10px;">Proveditelé</h4>
                        <?php
                        $adminsQuery = $mysqli->query("SELECT id, user_name, role FROM demands WHERE (role = 'technician' OR role = 'salesman-technician' OR role = 'admin') AND active = 1");
                        while ($admin = mysqli_fetch_array($adminsQuery)) {
                            $findQuery = $mysqli->query("SELECT admin_id FROM mails_recievers WHERE type_id = '$id' AND type = 'service' AND admin_id = '" . $admin['id'] . "' AND reciever_type = 'performer'") or die($mysqli->error);
                        ?>
                            <div class="col-sm-3" style="padding:0">
                                <input id="admin-<?= $admin['id'] ?>-performer" name="performer[]" value="<?= $admin['id'] ?>" type="checkbox"
                                       <?= (mysqli_num_rows($findQuery) === 1) ? 'checked' : '' ?>>
                                <label for="admin-<?= $admin['id'] ?>-performer" style="padding-left:4px; cursor:pointer;"><?= $admin['user_name'] ?></label>
                            </div>
                        <?php } ?>
                    </div>

                    <!-- Informovaní -->
                    <div class="form-group well col-sm-12 admins_well" style="margin:0 auto 18px; padding:16px 0 10px 20px;">
                        <h4 style="text-align:center; margin-top:0; border-bottom:1px solid #e2e2e5; padding-bottom:10px;">Informovaní</h4>
                        <?php
                        $adminsQuery = $mysqli->query("SELECT id, user_name, role FROM demands WHERE role != 'client' AND active = 1");
                        while ($admin = mysqli_fetch_array($adminsQuery)) {
                            $findQuery = $mysqli->query("SELECT admin_id FROM mails_recievers WHERE type_id = '$id' AND type = 'service' AND admin_id = '" . $admin['id'] . "' AND reciever_type = 'observer'") or die($mysqli->error);
                            $isObserver = (mysqli_num_rows($findQuery) === 1);
                        ?>
                            <div class="col-sm-3" style="padding:0">
                                <input id="admin-<?= $admin['id'] ?>-observer" name="observer[]" value="<?= $admin['id'] ?>" type="checkbox"
                                       <?= ($isObserver || $admin['role'] === 'salesman-technician') ? 'checked' : '' ?>>
                                <label for="admin-<?= $admin['id'] ?>-observer"
                                       style="padding-left:4px; cursor:pointer; <?= $isObserver ? 'color:green !important;' : '' ?>">
                                    <?= $admin['user_name'] ?>
                                </label>
                            </div>
                        <?php } ?>
                    </div>

                    <hr>

                    <!-- Zadavatel -->
                    <div class="form-group" style="margin-top:10px;">
                        <label class="col-sm-3 control-label" for="creator_id" style="padding-top:14px;">Zadavatel</label>
                        <div class="col-sm-6">
                            <select id="creator_id" name="creator_id" class="selectboxit">
                                <option value="0">Žádný zadavatel</option>
                                <?php
                                mysqli_data_seek($adminsQuery, 0);
                                while ($admin = mysqli_fetch_array($adminsQuery)) { ?>
                                    <option value="<?= $admin['id'] ?>"
                                        <?= (!empty($service['creator_id']) && $admin['id'] == $service['creator_id']) ? 'selected' : '' ?>>
                                        <?= $admin['user_name'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <hr>

                    <!-- Stav + Přidáno -->
                    <div class="form-group" style="margin-top:10px; margin-bottom:24px;">
                        <label class="col-sm-2 control-label" for="state" style="padding-top:14px;"><strong>Stav</strong></label>
                        <div class="col-sm-4">
                            <select id="state" name="state" class="selectboxit">
                                <?php
                                $states = [
                                    'new'         => 'Nový',
                                    'waiting'     => 'Čeká na díly',
                                    'unconfirmed' => 'Nepotvrzený',
                                    'confirmed'   => 'Potvrzený',
                                    'executed'    => 'Provedený',
                                    'unfinished'  => 'Nedokončený',
                                    'invoiced'    => 'Fakturované',
                                    'problematic' => 'Problémové',
                                    'warranty'    => 'Reklamace',
                                    'finished'    => 'Hotový',
                                    'canceled'    => 'Stornovaný',
                                ];
                                foreach ($states as $value => $label) { ?>
                                    <option value="<?= $value ?>" <?= (isset($service['state']) && $service['state'] === $value) ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <label for="field-3" class="col-sm-2 control-label"><strong>Přidáno</strong></label>
                        <div class="col-sm-3" style="padding-right:0;">
                            <input type="text" class="form-control datepicker" name="date_added"
                                   data-format="yyyy-mm-dd" data-validate="required"
                                   data-message-required="Musíte zadat datum."
                                   value="<?= $service['date_added'] ?>">
                        </div>
                    </div>

                    <!-- Datum + čas -->
                    <div class="form-group">
                        <label for="field-2" class="col-sm-3 control-label"><strong>Datum</strong></label>
                        <div class="col-sm-3" style="padding-right:0;">
                            <input type="text" class="form-control datepicker" name="date" data-format="yyyy-mm-dd"
                                   value="<?= ($service['date'] !== '0000-00-00') ? $service['date'] : '' ?>">
                        </div>
                        <label for="field-2" class="col-sm-2 control-label">Přibl. čas</label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control timepicker" name="estimatedtime"
                                   value="<?= $service['estimatedtime'] ?>"
                                   data-template="dropdown" data-show-seconds="false"
                                   data-default-time="11:25 AM" data-show-meridian="false"
                                   data-minute-step="5" placeholder="Přibližný čas...">
                        </div>
                    </div>

                    <!-- Informace pro techniky -->
                    <div class="form-group">
                        <label for="field-ta" class="col-sm-3 control-label">Informace pro techniky</label>
                        <div class="col-sm-8">
                            <textarea class="form-control" name="technical_details" rows="4"><?= $service['technical_details'] ?></textarea>
                        </div>
                    </div>

                    <!-- Interní informace -->
                    <div class="form-group">
                        <label for="field-ta" class="col-sm-3 control-label">Interní informace</label>
                        <div class="col-sm-8">
                            <textarea class="form-control" name="internal_details" rows="4"><?= $service['internal_details'] ?></textarea>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Panel: Fakturační údaje -->
            <div class="panel panel-primary" data-collapsed="0" <?= (!$forceRegenerate && $hasInvoice) ? 'style="pointer-events:none; opacity:0.5;"' : '' ?>>
                <div class="panel-heading">
                    <div class="panel-title"><strong style="font-weight:600;">Fakturační údaje</strong></div>
                </div>
                <div class="panel-body">
                    <?php billing_address($address); ?>
                    <div class="form-group">
                        <label for="field-7" class="col-sm-3 control-label">Doplňující informace</label>
                        <div class="col-sm-6">
                            <textarea name="shipping_details" class="form-control autogrow" id="field-7"><?= $service['shipping_details'] ?></textarea>
                        </div>
                    </div>
                </div>
                <?php shipping_address($address); ?>
            </div>

        </div><!-- /col-md-6 left -->

        <!-- PRAVÝ SLOUPEC -->
        <div class="col-md-6">

            <!-- Panel: Položky servisu -->
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title"><strong style="font-weight:600;">Položky servisu</strong></div>
                </div>
                <div class="panel-body">

                    <!-- Kategorie -->
                    <?php
                    $isHottub   = (isset($service['customertype']) && $service['customertype'] == 1);
                    $catCustomer = $isHottub ? 1 : 0;
                    $catQuery   = $mysqli->query("SELECT * FROM services_categories WHERE customer = $catCustomer ORDER BY title") or die($mysqli->error);
                    ?>
                    <div class="form-group">
                        <label for="field-2" class="col-sm-2 control-label"><h4>Kategorie</h4></label>
                        <div class="col-sm-10">
                            <select name="category" class="form-control" required>
                                <option value="" <?= ($service['category'] === '') ? 'selected' : '' ?>>Žádná zvolená kategorie</option>
                                <?php while ($cat = mysqli_fetch_array($catQuery)) { ?>
                                    <option value="<?= $cat['seoslug'] ?>" <?= ($cat['seoslug'] === $service['category']) ? 'selected' : '' ?>>
                                        <?= $cat['title'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <hr>

                    <!-- Informace pro zákazníka -->
                    <div class="form-group">
                        <label for="field-ta" class="col-sm-4 control-label">
                            <strong>Informace pro zákazníka</strong><br>
                            <small>(bude zaslána v e-mailu "Naplánování servisu" a "Garanční prohlídka")</small>
                        </label>
                        <div class="col-sm-7">
                            <textarea class="form-control" name="details" rows="4"><?= $service['details'] ?></textarea>
                        </div>
                    </div>

                    <!-- Odeslat mail -->
                    <div class="form-group">
                        <label class="col-sm-4 control-label"><strong>Odeslat mail klientovi</strong></label>
                        <div class="col-sm-8">
                            <div class="radio col-sm-3"><label><input type="radio" name="send_mail" value="yes">Ano</label></div>
                            <div class="radio col-sm-3"><label><input type="radio" name="send_mail" value="no" checked>Ne</label></div>
                        </div>
                    </div>

                    <!-- Typ e-mailu -->
                    <div class="form-group">
                        <label class="col-sm-2 control-label"><h4>Typ e-mailu</h4></label>
                        <div class="col-sm-10">
                            <select name="email_type" class="form-control">
                                <option value="received">Přijetí servisu</option>
                                <option value="planned">Naplánovaný servis</option>
                                <option value="guarantee_check">Naplánovaná garanční prohlídka</option>
                            </select>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Panel: Příslušenství -->
            <div class="panel panel-primary" data-collapsed="0" <?= $lockedAttr ?>>
                <div class="panel-heading">
                    <div class="panel-title"><strong style="font-weight:600;">Příslušenství k servisu</strong></div>
                </div>
                <div class="panel-body">

                    <?php shop_accessories('services_products_bridge', 'aggregate_id', $service['id'], $service['location_id']); ?>

                    <script type="text/javascript">
                    $(document).ready(function () {
                        $('.original_currency').on('input', function () {
                            const rate = $('.final_currency').data('rate');
                            $('.final_currency').val(($(this).val() / rate).toFixed(2));
                        });
                    });
                    </script>

                    <!-- Kalkulátor měn -->
                    <div class="form-group calculator" <?= ($service['currency'] === 'CZK') ? 'style="display:none;"' : '' ?>>
                        <hr>
                        <label class="col-sm-3 control-label">Kalkulátor měn</label>
                        <div class="form-label-group">
                            <div class="col-sm-3 has-metric">
                                <input type="text" class="form-control text-center original_currency" name="original_currency" value="" placeholder="CZK" style="padding:0; height:38px;">
                                <span class="input-group-addon">Kč</span>
                            </div>
                            <div class="col-sm-1">
                                <i class="fas fa-exchange-alt" style="padding:10px 14px; font-size:16px; color:#0d7eff"></i>
                            </div>
                            <div class="col-sm-3 has-metric">
                                <input type="text" class="form-control text-center final_currency" name="final_currency" value=""
                                       placeholder="<?= ($service['currency'] !== 'CZK') ? $service['currency'] : '' ?>"
                                       data-rate="<?= $service['exchange_rate'] ?>"
                                       style="padding:0; height:38px;">
                                <span class="input-group-addon final_currency_shortcut">
                                    <?= ($service['currency'] !== 'CZK') ? $service['currency'] : '' ?>
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Panel: Cena a platební podmínky -->
            <div class="panel panel-primary" data-collapsed="0" <?= $lockedAttr ?>>
                <div class="panel-heading">
                    <div class="panel-title"><strong style="font-weight:600;">Cena a platební podmínky</strong></div>
                </div>
                <div class="panel-body">

                    <script type="text/javascript">
                    jQuery(document).ready(function ($) {
                        $('#duplicate-item').click(function () {
                            $('#copy-item').clone(true).attr('id', '').insertAfter('.item:last').show();
                        });
                        $('.remove-item').click(function () {
                            $(this).closest('.item').remove();
                        });
                    });
                    </script>

                    <!-- Šablona položky (skrytá) -->
                    <div id="copy-item" class="form-group item" style="display:none;">
                        <div class="col-sm-8">
                            <input type="text" name="service_item_name[]" class="form-control" value="" placeholder="Položka servisu">
                        </div>
                        <div class="col-sm-3">
                            <input type="number" name="service_item_price[]" class="form-control price-control" value="" placeholder="Cena položky">
                        </div>
                        <div class="col-sm-1">
                            <button type="button" class="btn btn-danger remove-item"><i class="entypo-trash"></i></button>
                        </div>
                    </div>

                    <!-- Existující položky servisu -->
                    <?php
                    $itemsQuery = $mysqli->query("SELECT * FROM services_items WHERE service_id = '$id'") or die($mysqli->error);
                    while ($item = mysqli_fetch_assoc($itemsQuery)) { ?>
                        <div class="form-group item">
                            <div class="col-sm-8">
                                <input type="text" name="service_item_name[]" class="form-control" value="<?= $item['name'] ?>" placeholder="Položka servisu">
                            </div>
                            <div class="col-sm-3">
                                <input type="number" name="service_item_price[]" class="form-control price-control"
                                       value="<?= $item['price'] ?>" data-default="<?= $item['price'] ?>" placeholder="Cena položky">
                            </div>
                            <div class="col-sm-1">
                                <button type="button" class="btn btn-danger remove-item"><i class="entypo-trash"></i></button>
                            </div>
                        </div>
                    <?php } ?>

                    <button type="button" id="duplicate-item" style="float:left; width:100%;" class="btn btn-default btn-icon icon-left">
                        Přidat další specifikaci <i class="entypo-plus"></i>
                    </button>

                    <div style="clear:both"></div>
                    <hr>

                    <!-- Cena dopravy + vzdálenost -->
                    <div class="form-group">
                        <label class="col-sm-5 control-label">Cena dopravy</label>
                        <div class="col-sm-3">
                            <input type="number" name="delivery_price" class="form-control price-control"
                                   value="<?= $service['delivery_price'] ?>" data-default="<?= $service['delivery_price'] ?>">
                        </div>
                        <div class="col-sm-4">
                            Vzdálenost: <strong id="distance" style="color:#333;">?</strong> km<br>
                            <small style="float:left;"><i id="location"><?= $locationAddress ?></i></small>
                        </div>
                    </div>

                    <hr>

                    <!-- Měna -->
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Měna</label>
                        <div class="col-sm-9">
                            <?php
                            $currencies = [
                                'CZK' => ['ext' => 'Kč',  'rate' => 1],
                                'EUR' => ['ext' => '€',   'rate' => $kurz['EUR']],
                                'USD' => ['ext' => '$',   'rate' => $kurz['USD']],
                            ];
                            foreach ($currencies as $code => $meta) {
                                $checked = (!isset($service['currency']) && $code === 'CZK') || (isset($service['currency']) && $service['currency'] === $code) ? 'checked' : '';
                            ?>
                                <div class="radio" style="float:left; <?= ($code !== 'CZK') ? 'margin-left:30px;' : '' ?>">
                                    <label>
                                        <input class="currency" type="radio" id="currency_<?= strtolower($code) ?>"
                                               name="currency" data-value="<?= $meta['rate'] ?>"
                                               data-ext="<?= $meta['ext'] ?>" value="<?= $code ?>" <?= $checked ?>><?= $code ?>
                                    </label>
                                    <input style="display:none;" name="<?= $code ?>_rate" value="<?= $meta['rate'] ?>">
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <hr>

                    <!-- Aktuální kurz -->
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Aktuální kurz dle ČNB</label>
                        <div class="col-sm-8">
                            <h5>
                                <strong><?= $kurz['EUR'] ?></strong> CZK/EUR&nbsp;&nbsp;&nbsp;&nbsp;
                                <strong><?= $kurz['USD'] ?></strong> CZK/USD
                            </h5>
                        </div>
                    </div>

                    <hr>

                    <!-- DPH -->
                    <div class="form-group">
                        <label class="col-sm-3 control-label">DPH %</label>
                        <div class="col-sm-9">
                            <?php foreach ([21, 15, 12, 10, 0] as $vatRate) { ?>
                                <div class="radio" style="width:80px; float:left;">
                                    <label>
                                        <input type="radio" name="vat" value="<?= $vatRate ?>"
                                               <?= (isset($service['vat']) && (int) $service['vat'] === $vatRate) ? 'checked' : '' ?>><?= $vatRate ?>%
                                    </label>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <hr>

                    <!-- Způsob úhrady -->
                    <?php $paymentMethodsQuery = $mysqli->query('SELECT * FROM shops_payment_methods ORDER BY name'); ?>
                    <div class="form-group" <?= $lockedAttr ?>>
                        <div class="col-sm-12">
                            <label class="col-sm-4 control-label">Způsob úhrady</label>
                            <div class="col-sm-6">
                                <select id="payment" name="payment" class="selectboxit">
                                    <?php while ($pm = mysqli_fetch_array($paymentMethodsQuery)) { ?>
                                        <option value="<?= $pm['link_name'] ?>"
                                            <?= (isset($service['payment_method']) && $service['payment_method'] === $pm['link_name']) ? 'selected' : '' ?>>
                                            <?= ucfirst($pm['pay_text']) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Panel: Informace pro klienta (servisní list) -->
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title"><strong style="font-weight:600;">Informace pro klienta</strong></div>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Servisní list</label>
                        <div class="col-sm-5">
                            <?php
                            $fileExists = false;
                            if (!empty($clientname)) {
                                $strpad   = str_pad($service['filenum'], 3, '0', STR_PAD_LEFT);
                                $filename = 'Servisni-list-' . $strpad . '.' . $service['extension'];
                                $filePath = $_SERVER['DOCUMENT_ROOT'] . '/admin/data/files/servis/' . $clientname['secretstring'] . '/' . $filename;
                                $fileExists = file_exists($filePath);
                            }

                            if ($fileExists) { ?>
                                <a href="../admin/data/files/servis/<?= $clientname['secretstring'] ?>/<?= $filename ?>" target="_blank">
                                    <button type="button" class="btn btn-primary btn-icon">Zobrazit <i class="entypo-search"></i></button>
                                </a>
                                <a href="upravit-servis?id=<?= $service['id'] ?>&action=removefile" class="btn btn-danger btn-sm btn-icon icon-left">
                                    <i class="entypo-cancel"></i> Smazat
                                </a>
                            <?php } else { ?>
                                <input type="file" class="form-control" name="servicelist" id="field-file">
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /col-md-6 right -->

    </div><!-- /row -->

    <center>
        <div class="form-group default-padding">
            <button type="submit" data-type="zoom-in" class="ladda-button btn btn-primary button-demo"
                    style="width:400px; margin:0 auto; height:71px; margin-bottom:0; font-size:17px;">
                Upravit servis
            </button>
        </div>
    </center>

</form>

<footer class="main">
    &copy; <?= date('Y') ?>
    <span style="float:right;">
        <?php
        $time   = explode(' ', microtime());
        $finish = $time[1] + $time[0];
        echo 'PHP ' . PHP_VERSION . ' | Page generated in ' . round(($finish - $start), 4) . ' seconds.';
        ?>
    </span>
</footer>

<script>
$(document).ready(function () {
    $('#orderform').on('submit', function () {
        if ($(this).valid()) {
            Ladda.create(document.querySelector('#orderform .button-demo button')).start();
        }
    });
});
</script>

<?php include VIEW . '/default/footer.php'; ?>