<?php
// =============================================================================
// INICIALIZACE
// =============================================================================

include $_SERVER['DOCUMENT_ROOT'] . '/admin/config/config.php';
include INCLUDES . '/googlelogin.php';
include INCLUDES . '/functions.php';

$categorytitle = 'Servis';
$pagetitle      = 'Přidat servis';

// =============================================================================
// POMOCNÉ FUNKCE
// =============================================================================

/**
 * Vrátí sanitizovanou hodnotu z $_POST, nebo výchozí hodnotu.
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
 * Vloží fakturační adresu a vrátí nové ID.
 */
function insertBillingAddress(mysqli $db): int
{
    $db->query("INSERT INTO addresses_billing (
        billing_company, billing_ico, billing_dic,
        billing_name, billing_surname, billing_street,
        billing_city, billing_zipcode, billing_country,
        billing_phone, billing_email, billing_details
    ) VALUES (
        '" . post('billing_company') . "',
        '" . post('billing_ico') . "',
        '" . post('billing_dic') . "',
        '" . post('billing_name') . "',
        '" . post('billing_surname') . "',
        '" . post('billing_street') . "',
        '" . post('billing_city') . "',
        '" . postTrim('billing_zipcode') . "',
        '" . post('billing_country') . "',
        '" . postTrim('billing_phone') . "',
        '" . postTrim('billing_email') . "',
        '" . $db->real_escape_string(post('customer_note')) . "'
    )") or die($db->error);

    return (int) $db->insert_id;
}

/**
 * Vloží doručovací adresu, pokud jsou vyplněna klíčová pole. Vrátí ID nebo 0.
 */
function insertShippingAddress(mysqli $db): int
{
    $requiredFields = ['shipping_company', 'shipping_name', 'shipping_surname',
                       'shipping_street', 'shipping_city', 'shipping_zipcode'];

    $hasData = false;
    foreach ($requiredFields as $field) {
        if (post($field) !== '') {
            $hasData = true;
            break;
        }
    }

    if (!$hasData) {
        return 0;
    }

    $db->query("INSERT INTO addresses_shipping (
        shipping_company, shipping_name, shipping_surname,
        shipping_street, shipping_city, shipping_zipcode,
        shipping_country, shipping_details
    ) VALUES (
        '" . post('shipping_company') . "',
        '" . post('shipping_name') . "',
        '" . post('shipping_surname') . "',
        '" . post('shipping_street') . "',
        '" . post('shipping_city') . "',
        '" . post('shipping_zipcode') . "',
        '" . post('shipping_country') . "',
        '" . $db->real_escape_string(post('customer_note')) . "'
    )") or die($db->error);

    return (int) $db->insert_id;
}

/**
 * Vloží hlavní záznam servisu a vrátí nové ID.
 */
function insertService(mysqli $db, int $billingId, int $shippingId, string $category): int
{
    $db->query("INSERT INTO services (
        creator_id, currency, category, payment_method,
        delivery_price, vat, shipping_id, billing_id,
        shipping_details, clientid, estimatedtime, customertype,
        state, date, date_added, details, technical_details,
        internal_details, location_id
    ) VALUES (
        '" . post('creator_id') . "',
        'CZK',
        '" . $category . "',
        '" . post('payment') . "',
        '" . post('delivery_price') . "',
        '" . post('vat') . "',
        '$shippingId',
        '$billingId',
        '" . post('customer_note') . "',
        '" . post('client') . "',
        '" . post('estimatedtime') . "',
        '" . post('customer') . "',
        '" . post('state') . "',
        '" . post('date') . "',
        '" . post('date_added') . "',
        '" . $db->real_escape_string(post('details')) . "',
        '" . $db->real_escape_string(post('technical_details')) . "',
        '" . $db->real_escape_string(post('internal_details')) . "',
        '" . post('location') . "'
    )") or die($db->error);

    return (int) $db->insert_id;
}

/**
 * Vloží položky servisu a vrátí jejich celkovou cenu.
 */
function insertServiceItems(mysqli $db, int $serviceId): float
{
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
                    VALUES ('$serviceId', '$name', '$price')")
            or die($db->error);
    }

    return $total;
}

/**
 * Alokuje produkty ze skladu navázané na servis.
 */
function allocateProducts(mysqli $db, int $serviceId): array
{
    $totalPurchase = 0.0;
    $totalPrice    = 0.0;

    if (empty($_POST['product_sku'])) {
        return [$totalPurchase, $totalPrice];
    }

    $skus = array_filter($_POST['product_sku']);
    if (empty($skus)) {
        return [$totalPurchase, $totalPrice];
    }

    foreach ($skus as $index => $sku) {
        if (empty($_POST['product_quantity'][$index])) {
            continue;
        }

        $stockAllocation = [
            'posterino'   => $sku,
            'id'          => $serviceId,
            'bridge'      => 'services_products_bridge',
            'id_identify' => 'aggregate_id',
            'quantity'    => $_POST['product_quantity'][$index],
            'location'    => post('location'),
            'type'        => 'service',
            'total_quantity' => $_POST['product_quantity'][$index],
            'price'       => product_price(
                $_POST['product_price'][$index],
                $_POST['product_original_price'][$index],
                post('vat'),
                post('vat'),
                $_POST['product_discount'][$index]
            ),
        ];

        include CONTROLLERS . '/product-stock-update.php';
        stock_allocate($stockAllocation);
    }

    return [$totalPurchase, $totalPrice];
}

/**
 * Vypočítá a uloží celkovou cenu servisu.
 */
function updateServiceTotal(mysqli $db, int $serviceId, float $serviceItemsPrice): void
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
        reference_number   = '{$serviceId}000',
        total_vat          = '{$price['vat']}',
        total_rounded      = '{$price['rounded']}',
        total_without_vat  = '{$price['without_vat']}',
        price              = '{$price['single']}',
        discount_net       = '{$priceData['discount_net']}',
        service_purchase   = '$purchaseTotal'
        WHERE id = '$serviceId'");
}

// =============================================================================
// ZPRACOVÁNÍ POST – přidání servisu
// =============================================================================

$serviceClient = $_POST['client'] ?? $_REQUEST['client'] ?? null;

if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'add') {

    $category = (post('customer') == 1) ? post('category_hottub') : post('category_sauna');

    $billingId  = insertBillingAddress($mysqli);
    $shippingId = insertShippingAddress($mysqli);
    $serviceId  = insertService($mysqli, $billingId, $shippingId, $category);

    $serviceItemsPrice = insertServiceItems($mysqli, $serviceId);

    // Proveditelé a informovaní
    $performers = !empty($_POST['performer']) ? array_filter($_POST['performer']) : [''];
    $observers  = !empty($_POST['observer']) ? array_filter($_POST['observer']) : [''];

    if (!empty($performers) || !empty($observers)) {
        recievers($performers, $observers, 'service', $serviceId);
    }

    // Kalendářní událost
    $_REQUEST['id'] = $serviceId;
    if (!empty(post('date'))) {
        saveCalendarEvent($serviceId, 'service');
        // TODO: odeslání mailu
    }

    // Produkty ze skladu
    allocateProducts($mysqli, $serviceId);

    // Celková cena
    updateServiceTotal($mysqli, $serviceId, $serviceItemsPrice);

    // Odeslání mailu
    $hasMail = '';
    $id = $serviceId;
    if (post('send_mail') === 'yes') {
        include CONTROLLERS . '/mails/services.php';
        $hasMail = '&has_mail=true';

    }

    // Přesměrování
    if (isset($_REQUEST['client'])) {
        header('Location:https://www.wellnesstrade.cz/admin/pages/services/zobrazit-servis?id=' . $serviceId . '&success=edit');
    } else {
        header('Location:https://www.wellnesstrade.cz/admin/pages/services/planovane-servisy?success=edit');
    }
    exit;
}

// =============================================================================
// NAČTENÍ DAT PRO FORMULÁŘ
// =============================================================================

$address  = '';
$selected = [];
$susernm  = '';
$newuser  = 0;

if (!empty($serviceClient)) {
    $selectedQuery = $mysqli->query('SELECT * FROM demands WHERE id = "' . $serviceClient . '"') or die($mysqli->error);

    if (mysqli_num_rows($selectedQuery) === 0) {
        $newuser = 1;
        $susernm = $firstname . ' ' . $lastname;
    } else {
        $selected = mysqli_fetch_assoc($selectedQuery);
        $susernm  = $selected['user_name'];

        $addressQuery = $mysqli->query('
            SELECT * FROM addresses_billing b
            LEFT JOIN addresses_shipping s ON s.id = "' . $selected['shipping_id'] . '"
            WHERE b.id = "' . $selected['billing_id'] . '"
        ') or die($mysqli->error);

        $address = mysqli_fetch_assoc($addressQuery);
    }
}

// Kurzy ČNB
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

// =============================================================================
// VIEW
// =============================================================================

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

    // Přepínání kategorií
    $('.customer-select').click(function () {
        if ($("input:radio[value='0']").is(':checked')) {
            $('.hottub-categories').hide('slow');
            $('.sauna-categories').show('slow');
        } else if ($("input:radio[value='1']").is(':checked')) {
            $('.sauna-categories').hide('slow');
            $('.hottub-categories').show('slow');
        }
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

<!-- =====================================================================
     FORMULÁŘ 1 – Výběr klienta
     ===================================================================== -->
<form role="form" method="post"
      class="form-horizontal form-groups-bordered validate"
      action="pridat-servis?service=new"
      enctype="multipart/form-data">

    <input type="hidden" name="length" value="14">

    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-primary" data-collapsed="0">

                <div class="panel-heading">
                    <div class="panel-title">
                        <strong style="font-weight:600;">
                            Klient
                            <img src="https://www.wellnesstrade.cz/wp-content/uploads/2015/03/logoblack.png"
                                 style="height:12px; margin-top:-2px;">
                        </strong>
                    </div>
                </div>

                <div class="panel-body">
                    <div class="form-group <?= (isset($_REQUEST['client']) && $_REQUEST['client'] === 'not_found') ? 'validate-has-error' : '' ?>"
                         style="margin-top:20px;">

                        <?php
                        $clientsArray = [];
                        $clientsQuery = $mysqli->query('SELECT id, user_name, customer, product FROM demands WHERE status != 6')
                            or die($mysqli->error);

                        while ($singleClient = mysqli_fetch_assoc($clientsQuery)) {
                            $clientsArray[] = [
                                'id'   => $singleClient['id'],
                                'text' => $singleClient['user_name'] . ' - ' . returnpn($singleClient['customer'], $singleClient['product']),
                            ];
                        }
                        ?>

                        <script type="text/javascript">
                        $(document).ready(function () {
                            var sampleArray = <?= json_encode($clientsArray) ?>;
                            $("#e10").select2({
                                data: sampleArray,
                                placeholder: "<?php
                                    if (!empty($selected['user_name'])) {
                                        echo $selected['user_name'] . ' - ' . returnpn($selected['customer'], $selected['product']);
                                    } else {
                                        echo 'Výběr klienta';
                                    }
                                ?>"
                            });
                        });
                        </script>

                        <div class="col-sm-12">
                            <div class="col-sm-8">
                                <input type="hidden" name="client" id="e10"/>
                            </div>
                            <div class="col-sm-4" style="padding-left:0;">
                                <button type="submit"
                                        style="padding:10px 18px 10px 50px; width:100%; height:42px;"
                                        class="btn btn-blue btn-icon icon-left">
                                    Načíst údaje
                                    <i class="fa fa-download" style="padding:14px 12px;"></i>
                                </button>
                                <?php if (!empty($selected['user_name'])) { ?>
                                    <a href="../demands/zobrazit-poptavku?id=<?= $selected['id'] ?>"
                                       target="_blank"
                                       class="btn btn-primary btn-icon icon-left"
                                       style="float:right; margin-top:4px;">
                                        <i class="entypo-user"></i> Zobrazit poptávku
                                    </a>
                                <?php } ?>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</form>

<!-- =====================================================================
     FORMULÁŘ 2 – Přidání servisu
     ===================================================================== -->
<form role="form" method="post" autocomplete="off"
      class="form-horizontal form-groups-bordered validate"
      action="pridat-servis?action=add"
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

                    <input type="text" style="display:none;" name="client"
                           value="<?= isset($selected['id']) ? $selected['id'] : '' ?>">

                    <!-- Proveditelé -->
                    <div class="form-group well col-sm-12 admins_well"
                         style="margin:0 auto 18px; padding:16px 0 10px 20px;">
                        <h4 style="text-align:center; margin-top:0; border-bottom:1px solid #e2e2e5; padding-bottom:10px;">
                            Proveditelé
                        </h4>
                        <?php
                        $admins_query = $mysqli->query("SELECT id, user_name, role FROM demands WHERE (role = 'technician' OR role = 'salesman-technician' OR role = 'admin') AND active = 1");
                        while ($admins = mysqli_fetch_array($admins_query)) { ?>
                            <div class="col-sm-4 col-md-5 col-lg-3" style="padding:0">
                                <input id="admin-<?= $admins['id'] ?>-performer"
                                       name="performer[]" value="<?= $admins['id'] ?>" type="checkbox">
                                <label for="admin-<?= $admins['id'] ?>-performer"
                                       style="padding-left:4px; cursor:pointer;"><?= $admins['user_name'] ?></label>
                            </div>
                        <?php } ?>
                    </div>

                    <!-- Informovaní -->
                    <div class="form-group well col-sm-12 admins_well"
                         style="margin:0 auto; padding:16px 0 10px 20px;">
                        <h4 style="text-align:center; margin-top:0; border-bottom:1px solid #e2e2e5; padding-bottom:10px;">
                            Informovaní
                        </h4>
                        <?php
                        $adminsQuery = $mysqli->query("SELECT id, user_name, role FROM demands WHERE role != 'client' AND active = 1");
                        while ($admins = mysqli_fetch_array($adminsQuery)) { ?>
                            <div class="col-sm-4 col-md-5 col-lg-3" style="padding:0">
                                <input id="admin-<?= $admins['id'] ?>-observer"
                                       name="observer[]" value="<?= $admins['id'] ?>" type="checkbox"
                                       <?php if ((!empty($client['id']) && $client['id'] == $admins['id']) || $admins['role'] === 'salesman-technician') { echo 'checked'; } ?>>
                                <label for="admin-<?= $admins['id'] ?>-observer"
                                       style="padding-left:4px; cursor:pointer;"><?= $admins['user_name'] ?></label>
                            </div>
                        <?php } ?>
                    </div>

                    <hr style="clear:both; float:left; width:100%;">

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
                                        <?= (!empty($client['id']) && $admin['id'] == $client['id']) ? 'selected' : '' ?>>
                                        <?= $admin['user_name'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <!-- Stav + Přidáno -->
                    <div class="form-group" style="margin-top:10px; margin-bottom:24px;">
                        <hr>
                        <label class="col-sm-2 control-label" for="state" style="padding-top:14px;"><strong>Stav</strong></label>
                        <div class="col-sm-4" style="padding-right:0;">
                            <select id="state" name="state" class="selectboxit">
                                <option value="new" selected>Nový</option>
                                <option value="waiting">Čeká na díly</option>
                                <option value="unconfirmed">Nepotvrzený</option>
                                <option value="confirmed">Potvrzený</option>
                                <option value="executed">Provedený</option>
                                <option value="unfinished">Nedokončený</option>
                                <option value="invoiced">Fakturované</option>
                                <option value="problematic">Problémové</option>
                                <option value="warranty">Reklamace</option>
                                <option value="finished">Hotový</option>
                                <option value="canceled">Stornovaný</option>
                            </select>
                        </div>
                        <label for="field-3" class="col-sm-2 control-label"><strong>Přidáno</strong></label>
                        <div class="col-sm-3" style="padding-right:0;">
                            <input id="datum5" type="text" class="form-control datepicker"
                                   name="date_added" data-format="yyyy-mm-dd"
                                   data-validate="required" data-message-required="Musíte zadat datum."
                                   value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>

                    <!-- Datum + čas -->
                    <div class="form-group">
                        <label for="field-2" class="col-sm-3 control-label"><strong>Datum</strong></label>
                        <div class="col-sm-3" style="padding-right:0;">
                            <input id="datum5" type="text" class="form-control datepicker"
                                   name="date" data-format="yyyy-mm-dd">
                        </div>
                        <label for="field-2" class="col-sm-2 control-label">Přibl. čas</label>
                        <div class="col-sm-3" style="padding-right:0;">
                            <input type="text" class="form-control timepicker" name="estimatedtime"
                                   data-template="dropdown" data-show-seconds="false"
                                   data-default-time="11:25 AM" data-show-meridian="false"
                                   data-minute-step="5" placeholder="Přibližný čas...">
                        </div>
                    </div>

                    <!-- Informace pro techniky -->
                    <div class="form-group">
                        <label for="field-ta" class="col-sm-3 control-label" style="padding-right:0;">Informace pro techniky</label>
                        <div class="col-sm-8" style="padding-right:0;">
                            <textarea class="form-control" name="technical_details" id="field-ta" rows="4"></textarea>
                        </div>
                    </div>

                    <!-- Interní informace -->
                    <div class="form-group">
                        <label for="field-ta" class="col-sm-3 control-label" style="padding-right:0;">Interní informace</label>
                        <div class="col-sm-8" style="padding-right:0;">
                            <textarea class="form-control" name="internal_details" id="field-ta" rows="4"></textarea>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Panel: Fakturační údaje -->
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        <strong style="font-weight:600;">Fakturační údaje</strong>
                    </div>
                </div>
                <div class="panel-body">
                    <?php billing_address($address); ?>
                    <div class="form-group">
                        <label for="field-7" class="col-sm-3 control-label">Doplňující informace</label>
                        <div class="col-sm-6">
                            <textarea name="customer_note" class="form-control autogrow" id="field-7"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <?php shipping_address($address); ?>

        </div><!-- /col-md-6 left -->

        <!-- PRAVÝ SLOUPEC -->
        <div class="col-md-6" style="margin-top:-167px;">

            <!-- Panel: Popis servisu -->
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title"><strong style="font-weight:600;">Popis servisu</strong></div>
                </div>
                <div class="panel-body">

                    <!-- Typ -->
                    <div class="form-group">
                        <label for="field-2" class="col-sm-3 control-label"><h4>Volba typu</h4></label>
                        <div class="col-sm-9">
                            <div class="radio col-sm-3">
                                <label>
                                    <input type="radio" name="customer" class="customer-select" value="1"
                                        <?php if ((isset($_REQUEST['customer']) && $_REQUEST['customer'] == 1)
                                            || (isset($selected['customer']) && in_array($selected['customer'], [1, 3]))) {
                                            echo 'checked';
                                        } ?> required>Vířivka
                                </label>
                            </div>
                            <div class="radio col-sm-3">
                                <label>
                                    <input type="radio" name="customer" class="customer-select" value="0"
                                        <?php if ((isset($_REQUEST['customer']) && $_REQUEST['customer'] == 0)
                                            || (isset($selected['customer']) && $selected['customer'] == 0)) {
                                            echo 'checked';
                                        } ?> required>Sauna
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Kategorie – vířivka -->
                    <div class="form-group hottub-categories"
                         <?php if (!isset($selected['customer']) || $selected['customer'] == 0) { echo 'style="display:none;"'; } ?>>
                        <label for="field-2" class="col-sm-2 control-label"><h4>Kategorie</h4></label>
                        <div class="col-sm-10">
                            <select name="category_hottub" class="form-control" required>
                                <option value="">Žádná zvolená kategorie</option>
                                <?php
                                $catq = $mysqli->query('SELECT * FROM services_categories WHERE customer = 1 ORDER BY title') or die($mysqli->error);
                                while ($cat = mysqli_fetch_array($catq)) { ?>
                                    <option value="<?= $cat['seoslug'] ?>"
                                        <?= (isset($_REQUEST['category']) && $_REQUEST['category'] === $cat['seoslug']) ? 'selected' : '' ?>>
                                        <?= $cat['title'] ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <!-- Kategorie – sauna -->
                    <div class="form-group sauna-categories"
                         <?php if (!isset($selected['customer']) || $selected['customer'] != 0) { echo 'style="display:none;"'; } ?>>
                        <label for="field-2" class="col-sm-2 control-label"><h4>Kategorie</h4></label>
                        <div class="col-sm-10">
                            <select name="category_sauna" class="form-control" required>
                                <option value="">Žádná zvolená kategorie</option>
                                <?php
                                $catq = $mysqli->query('SELECT * FROM services_categories WHERE customer = 0 ORDER BY title') or die($mysqli->error);
                                while ($cat = mysqli_fetch_array($catq)) { ?>
                                    <option value="<?= $cat['seoslug'] ?>"><?= $cat['title'] ?></option>
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
                            <textarea class="form-control" name="details" id="field-ta" rows="4"></textarea>
                        </div>
                    </div>

                    <!-- Odeslat mail -->
                    <div class="form-group">
                        <label for="field-ta" class="col-sm-4 control-label"><strong>Odeslat mail klientovi</strong></label>
                        <div class="col-sm-8">
                            <div class="radio col-sm-3"><label><input type="radio" name="send_mail" value="yes">Ano</label></div>
                            <div class="radio col-sm-3"><label><input type="radio" name="send_mail" value="no" checked>Ne</label></div>
                        </div>
                    </div>

                    <!-- Typ e-mailu -->
                    <div class="form-group">
                        <label for="field-2" class="col-sm-2 control-label"><h4>Typ e-mailu</h4></label>
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
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title"><strong style="font-weight:600;">Příslušenství k servisu</strong></div>
                </div>
                <div class="panel-body">

                    <?php shop_accessories('', '', '', ''); ?>

                    <script type="text/javascript">
                    $(document).ready(function () {
                        $('.original_currency').on('input', function () {
                            const rate = $('.final_currency').data('rate');
                            $('.final_currency').val(($(this).val() / rate).toFixed(2));
                        });
                    });
                    </script>

                    <!-- Kalkulátor měn -->
                    <div class="form-group calculator" style="display:none;">
                        <hr>
                        <label for="field-2" class="col-sm-3 control-label">Kalkulátor měn</label>
                        <div class="form-label-group">
                            <div class="col-sm-3 has-metric">
                                <input type="text" class="form-control text-center original_currency"
                                       name="original_currency" value="" placeholder="CZK"
                                       style="padding:0; height:38px;">
                                <span class="input-group-addon">Kč</span>
                            </div>
                            <div class="col-sm-1">
                                <i class="fas fa-exchange-alt" style="padding:10px 14px; font-size:16px; color:#0d7eff"></i>
                            </div>
                            <div class="col-sm-3 has-metric">
                                <input type="text" class="form-control text-center final_currency"
                                       name="final_currency" value="" placeholder="" data-rate=""
                                       style="padding:0; height:38px;">
                                <span class="input-group-addon final_currency_shortcut"></span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Panel: Cena a platební podmínky -->
            <div class="panel panel-primary" data-collapsed="0">
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
                            <input type="number" name="service_item_price[]" class="form-control" value="" placeholder="Cena položky">
                        </div>
                        <div class="col-sm-1">
                            <button type="button" class="btn btn-danger remove-item"><i class="entypo-trash"></i></button>
                        </div>
                    </div>

                    <!-- Výchozí položka -->
                    <div class="form-group item">
                        <div class="col-sm-8">
                            <input type="text" name="service_item_name[]" class="form-control" value="" placeholder="Položka servisu">
                        </div>
                        <div class="col-sm-3">
                            <input type="number" name="service_item_price[]" class="form-control" value="" placeholder="Cena položky">
                        </div>
                        <div class="col-sm-1">
                            <button type="button" class="btn btn-danger remove-item"><i class="entypo-trash"></i></button>
                        </div>
                    </div>

                    <button type="button" id="duplicate-item"
                            style="float:left; width:100%;"
                            class="btn btn-default btn-icon icon-left">
                        Přidat další specifikaci <i class="entypo-plus"></i>
                    </button>

                    <div style="clear:both"></div>
                    <hr>

                    <!-- Cena dopravy -->
                    <div class="form-group">
                        <label for="field-6" class="col-sm-5 control-label">Cena dopravy</label>
                        <div class="col-sm-3">
                            <input type="number" name="delivery_price" class="form-control" value="">
                        </div>
                    </div>

                    <!-- Měna -->
                    <div class="form-group">
                        <label for="field-2" class="col-sm-3 control-label">Měna</label>
                        <div class="col-sm-9">
                            <div class="radio" style="float:left;">
                                <label>
                                    <input class="currency" type="radio" id="currency_czk" name="currency"
                                           data-value="1" data-ext="Kč" value="CZK"
                                           <?= (!isset($order['order_currency']) || $order['order_currency'] === 'CZK') ? 'checked' : '' ?>>CZK
                                </label>
                                <input style="display:none;" name="CZK_rate" value="<?= $kurz['CZK'] ?>">
                            </div>
                            <div class="radio" style="float:left; margin-left:30px;">
                                <label>
                                    <input class="currency" type="radio" id="currency_eur" name="currency"
                                           data-value="<?= $kurz['EUR'] ?>" data-ext="€" value="EUR"
                                           <?= (isset($order['order_currency']) && $order['order_currency'] === 'EUR') ? 'checked' : '' ?>>EUR
                                </label>
                                <input style="display:none;" name="EUR_rate" value="<?= $kurz['EUR'] ?>">
                            </div>
                            <div class="radio" style="float:left; margin-left:30px;">
                                <label>
                                    <input class="currency" type="radio" id="currency_usd" name="currency"
                                           data-value="<?= $kurz['USD'] ?>" data-ext="$" value="USD"
                                           <?= (isset($order['order_currency']) && $order['order_currency'] === 'USD') ? 'checked' : '' ?>>USD
                                </label>
                                <input style="display:none;" name="USD_rate" value="<?= $kurz['USD'] ?>">
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Aktuální kurz -->
                    <div class="form-group">
                        <label for="field-2" class="col-sm-3 control-label">Aktuální kurz dle ČNB</label>
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
                                               <?= ($vatRate === 21) ? 'checked' : '' ?>><?= $vatRate ?>%
                                    </label>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <hr>

                    <!-- Způsob úhrady -->
                    <?php $payment_methods_query = $mysqli->query('SELECT * FROM shops_payment_methods ORDER BY name'); ?>
                    <div class="form-group">
                        <div class="col-sm-12">
                            <label class="col-sm-4 control-label">Způsob úhrady</label>
                            <div class="col-sm-6">
                                <select id="payment" name="payment" class="selectboxit">
                                    <?php while ($pm = mysqli_fetch_array($payment_methods_query)) { ?>
                                        <option value="<?= $pm['link_name'] ?>"><?= ucfirst($pm['pay_text']) ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div><!-- /col-md-6 right -->

    </div><!-- /row -->

    <center>
        <div class="form-group default-padding">
            <?php if (isset($_REQUEST['id'])) { ?>
                <a href="zobrazit-klienta?id=<?= $_REQUEST['id'] ?>">
                    <button type="button" class="btn btn-primary">Zpět</button>
                </a>
            <?php } ?>
            <button type="submit" data-type="zoom-in" class="ladda-button btn btn-green button-demo"
                    style="width:400px; margin:0 auto; height:71px; margin-bottom:0; font-size:17px;">
                Přidat servis
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
        var form = $(this);
        var l = Ladda.create(document.querySelector('#orderform .button-demo button'));
        if (form.valid()) {
            l.start();
        }
    });
});
</script>

<?php include VIEW . '/default/footer.php'; ?>