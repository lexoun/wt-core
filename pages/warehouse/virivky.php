<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";

include INCLUDES . "/googlelogin.php";
include INCLUDES . "/functions.php";

// Bezpečnostní ošetření vstupů
$od = isset($_REQUEST['od']) ? (int)$_REQUEST['od'] : 1;
$search = isset($_REQUEST['q']) ? $mysqli->real_escape_string($_REQUEST['q']) : "";

// Multi-value filters — accept both type=2 and type[]=1&type[]=2
// PHP automatically parses type[]=0&type[]=1 into $_REQUEST['type'] as an array
function parseMultiParam($key) {
    global $mysqli;
    $val = $_REQUEST[$key] ?? null;
    if ($val === null || $val === '') {
        return null;
    }
    if (is_array($val)) {
        // Came as type[]=0&type[]=1
        $filtered = array_filter($val, fn($v) => $v !== '');
        if (empty($filtered)) return null;
        return array_values(array_map(fn($v) => $mysqli->real_escape_string((string)$v), $filtered));
    }
    // Came as single scalar: type=2
    return [$mysqli->real_escape_string((string)$val)];
}

$typeArr     = parseMultiParam('type');
$customerArr = parseMultiParam('customer');
$spec5Arr    = parseMultiParam('spec5');

// Legacy single-value aliases (used in older code paths)
$type     = ($typeArr !== null)     ? implode(',', $typeArr)     : null;
$customer = ($customerArr !== null) ? $customerArr[0]            : null; // customer stays single for now
$spec5    = ($spec5Arr !== null)    ? $spec5Arr[0]               : null; // will override below

$category = isset($_REQUEST['category']) ? $mysqli->real_escape_string($_REQUEST['category']) : null;
$brand = isset($_REQUEST['brand']) ? $mysqli->real_escape_string($_REQUEST['brand']) : null;
$location = isset($_REQUEST['location']) ? (int)$_REQUEST['location'] : null;

// Nové specifikace — spec5 je již zpracován přes parseMultiParam výše
if (isset($_REQUEST['spec1'])) { $spec1 = $mysqli->real_escape_string($_REQUEST['spec1']); }
if (isset($_REQUEST['spec2'])) { $spec2 = $mysqli->real_escape_string($_REQUEST['spec2']); }

if (isset($search) && $search != "") {
    $pagetitle = 'Hledaný výraz "' . htmlspecialchars($search) . '"';
    $bread1 = "Vířivky sklad";
    $abread1 = "virivky";
} else {
    $pagetitle = "Vířivky sklad";
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "remove") {
    
    $req_id = (int)$_REQUEST['id'];

    $mysqli->query('DELETE FROM warehouse WHERE id="' . $req_id . '"') or die($mysqli->error);
    $mysqli->query('DELETE FROM warehouse_specs_bridge WHERE client_id="' . $req_id . '"') or die($mysqli->error);

    $selectreserve = $mysqli->query("SELECT product_id, variation_id, reserved FROM warehouse_products_bridge WHERE warehouse_id = '" . $req_id . "'") or die($mysqli->error);

    while ($reserve = mysqli_fetch_array($selectreserve)) {

        $instock = $reserve['reserved'];

        if (isset($reserve['variation_id']) && $reserve['variation_id'] == 0) {

            $search1 = $mysqli->query("SELECT s.id, s.reserved, s.quantity FROM demands_sauna_specs s, demands d WHERE s.product_id = '" . $reserve['product_id'] . "' and s.demand_id = d.id and d.status < 5 order by d.id desc");
            while ($search = mysqli_fetch_array($search1)) {

                $rozdil = $search['quantity'] - $search['reserved'];

                if ($rozdil >= $instock) {
                    $update = $mysqli->query("UPDATE demands_sauna_specs SET reserved = reserved + $instock WHERE id = '" . $search['id'] . "'");
                    $instock = 0;
                } else {
                    $update = $mysqli->query("UPDATE demands_sauna_specs SET reserved = reserved + $rozdil WHERE id = '" . $search['id'] . "'");
                    $instock = $instock - $rozdil;
                }
            }

            $updateres = $instock;

            $warehouse_search = $mysqli->query("SELECT b.id, b.reserved, b.quantity FROM warehouse_products_bridge b, warehouse w WHERE b.product_id = '" . $reserve['product_id'] . "' and b.warehouse_id = w.id and b.warehouse_id != '" . $req_id . "' and w.status <> 4 order by w.id desc") or die($mysqli->error);
            while ($warehouse = mysqli_fetch_array($warehouse_search)) {

                $rozdil = $warehouse['quantity'] - $warehouse['reserved'];

                if ($rozdil >= $instock) {
                    $update = $mysqli->query("UPDATE warehouse_products_bridge SET reserved = reserved + $instock WHERE id = '" . $warehouse['id'] . "'");
                    $instock = 0;
                } else {
                    $update = $mysqli->query("UPDATE warehouse_products_bridge SET reserved = reserved + $rozdil WHERE id = '" . $warehouse['id'] . "'");
                    $instock = $instock - $rozdil;
                }
            }

            $updateres = $instock;

            if ($updateres > 0) {
                $search1 = $mysqli->query("SELECT b.id, b.reserved, b.quantity FROM orders_products_bridge b, orders o WHERE b.product_id = '" . $reserve['product_id'] . "' and b.order_id = o.id and o.order_status <> 4 and b.reserved < b.quantity order by o.id desc") or die($mysqli->error);

                while ($search = mysqli_fetch_array($search1)) {
                    $rozdil = $search['quantity'] - $search['reserved'];

                    if ($rozdil > $updateres || $rozdil == $updateres) {
                        $update = $mysqli->query("UPDATE orders_products_bridge SET reserved = reserved + $updateres WHERE id = '" . $search['id'] . "'");
                        $updateres = 0;
                    } else {
                        $update = $mysqli->query("UPDATE orders_products_bridge SET reserved = reserved + $rozdil WHERE id = '" . $search['id'] . "'");
                        $updateres = $updateres - $rozdil;
                    }
                }
            }
            $update = $mysqli->query("UPDATE products SET instock = instock + $updateres WHERE id = '" . $reserve['product_id'] . "'");

        } else {

            $warehouse_search = $mysqli->query("SELECT b.id, b.reserved, b.quantity FROM warehouse_products_bridge b, warehouse w WHERE b.product_id = '" . $reserve['product_id'] . "' and b.variation_id = '" . $reserve['variation_id'] . "' and b.warehouse_id = w.id and w.status <> 4 order by w.id desc") or die($mysqli->error);
            while ($warehouse = mysqli_fetch_array($warehouse_search)) {

                $rozdil = $warehouse['quantity'] - $warehouse['reserved'];

                if ($rozdil >= $instock) {
                    $update = $mysqli->query("UPDATE warehouse_products_bridge SET reserved = reserved + $instock WHERE id = '" . $warehouse['id'] . "'");
                    $instock = 0;
                } else {
                    $update = $mysqli->query("UPDATE warehouse_products_bridge SET reserved = reserved + $rozdil WHERE id = '" . $warehouse['id'] . "'");
                    $instock = $instock - $rozdil;
                }
            }

            $updateres = $instock;

            if ($updateres > 0) {
                $search1 = $mysqli->query("SELECT b.id, b.reserved, b.quantity FROM orders_products_bridge b, orders o WHERE b.variation_id = '" . $reserve['variation_id'] . "' and b.order_id != '" . $req_id . "' and b.order_id = o.id and o.order_status <> 4 and b.reserved < b.quantity order by o.id desc");

                while ($search = mysqli_fetch_array($search1)) {
                    $rozdil = $search['quantity'] - $search['reserved'];

                    if ($rozdil > $updateres || $rozdil == $updateres) {
                        $update = $mysqli->query("UPDATE orders_products_bridge SET reserved = reserved + $updateres WHERE id = '" . $search['id'] . "'");
                        $updateres = 0;
                    } else {
                        $update = $mysqli->query("UPDATE orders_products_bridge SET reserved = reserved + $rozdil WHERE id = '" . $search['id'] . "'");
                        $updateres = $updateres - $rozdil;
                    }
                }
            }
            $update = $mysqli->query("UPDATE products_variations SET stock = stock + $updateres WHERE product_id = '" . $reserve['product_id'] . "' AND id = '" . $reserve['variation_id'] . "'");
        }
    }

    $mysqli->query('DELETE FROM warehouse_products_bridge WHERE warehouse_id="' . $req_id . '"') or die($mysqli->error);

    header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/virivky?id=' . $req_id . '&success=remove');
    exit;
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "demandnull") {
    $req_id = (int)$_REQUEST['id'];
    $redirect_id = (int)$_REQUEST['redirect'];

    $mysqli->query('UPDATE warehouse SET demand_id = "0" WHERE id = "' . $req_id . '"') or die($mysqli->error);

    $_REQUEST['id'] = $redirect_id;
    saveCalendarEvent($redirect_id, 'realization');

    header('location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $redirect_id . '&changedemand=success');
    exit;
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "demandchange") {
    $req_id = (int)$_REQUEST['id'];
    $demand_id = (int)$_POST['demand'];

    $mysqli->query('UPDATE warehouse SET demand_id = "' . $demand_id . '" WHERE id="' . $req_id . '"') or die($mysqli->error);

    if ($demand_id != 0) {
        $_REQUEST['id'] = $demand_id;
        saveCalendarEvent($demand_id, 'realization');
    }

    $find_container = $mysqli->query("SELECT id FROM containers_products WHERE warehouse_id = '" . $req_id . "'") or die($mysqli->error);

    if (mysqli_num_rows($find_container) > 0) {
        $container = mysqli_fetch_array($find_container);
        $update_demand = $mysqli->query("UPDATE containers_products SET demand_id = '" . $demand_id . "' WHERE id = '" . $container['id'] . "'") or die($mysqli->error);
    }

    header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/virivky?id=' . $req_id . '&changedemand=success');
    exit;
}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "add") {
    $displaysuccess = true;
    $successhlaska = "Vířivka byla úspěšně přidána.";
}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "remove") {
    $displaysuccess = true;
    $successhlaska = "Vířivka byla úspěšně odstraněna.";
}

include VIEW . '/default/header.php';

?>

<script type="text/javascript">
    $(document).ready(function() {
        $(".show-specs").click(function() {
            $(this).parent().find('.hidden-specs').toggle('slow');
        });
    });
</script>

<?php

// QUERY BUILDER
// Stavíme tři verze query:
//   $query            — plná (customer + type + vše ostatní)
//   $query_no_customer — bez customer podmínky (pro počítání customer tlačítek)
//   $query_no_type     — bez type podmínky (pro počítání type tlačítek)

$query = '';
$currentpage = 'virivky';
$queryBuilder = array();

// Části bez customer a bez type (sdílená základna)
$sqlBase = [];          // spec, brand, category, location, ...
$sqlCustomer = '';      // jen customer podmínka
$sqlType = '';          // jen type podmínka
$sqlTypeDefault = '';   // defaultní type filtr (status < 3 OR > 4)

// --- CUSTOMER ---
if (isset($customerArr) && $customerArr !== null) {
    $queryBuilder['customer'] = $customerArr;
    $customerConditions = [];
    foreach ($customerArr as $cVal) {
        if ($cVal == '0')      $customerConditions[] = "(w.demand_id = '0' AND w.reserved_showroom = 0)";
        elseif ($cVal == '1')  $customerConditions[] = "(w.demand_id > '0' AND w.reserved_showroom = 0)";
        elseif ($cVal == '3')  $customerConditions[] = "(w.reserved_showroom <> 0)";
    }
    if (!empty($customerConditions)) {
        $sqlCustomer = ' AND (' . implode(' OR ', $customerConditions) . ')';
    }
}

// --- TYPE ---
if (isset($typeArr) && $typeArr !== null && count($typeArr) > 0) {
    $queryBuilder['type'] = $typeArr;
    $safeTypes = array_map('intval', $typeArr);
    $sqlType = ' AND w.status IN (' . implode(',', $safeTypes) . ')';
} elseif (!isset($typeArr) && !isset($location)) {
    $sqlTypeDefault = ' AND (status < 3 OR status > 4)';
}

// --- OSTATNÍ ---
if (isset($category) && $category != '') {
    $queryBuilder['category'] = $category;
    $sqlBase[] = "w.product = '" . $category . "'";
}
if (isset($brand) && $brand != '') {
    $queryBuilder['brand'] = $brand;
    $sqlBase[] = "p.brand = '" . $brand . "'";
}
if (isset($location) && $location != '') {
    $queryBuilder['location'] = $location;
    $sqlBase[] = "(w.location_id = '" . $location . "' OR w.reserved_showroom = '" . $location . "')";
    if (!isset($typeArr)) {
        $sqlBase[] = 'status < 4';
    }
}
if (isset($spec5) && $spec5 != '') {
    $queryBuilder['spec5'] = $spec5;
    $sqlBase[] = "w.id IN (SELECT client_id FROM warehouse_specs_bridge WHERE specs_id = 5 AND value = '" . $spec5 . "')";
}
if (isset($spec1) && $spec1 != '') {
    $queryBuilder['spec1'] = $spec1;
    $sqlBase[] = "w.id IN (SELECT client_id FROM warehouse_specs_bridge WHERE specs_id = 1 AND value = '" . $mysqli->real_escape_string($spec1) . "')";
}
if (isset($spec2) && $spec2 != '') {
    $queryBuilder['spec2'] = $spec2;
    $sqlBase[] = "w.id IN (SELECT client_id FROM warehouse_specs_bridge WHERE specs_id = 2 AND value = '" . $mysqli->real_escape_string($spec2) . "')";
}

$sqlBaseStr = !empty($sqlBase) ? ' AND ' . implode(' AND ', $sqlBase) : '';

// Sestavíme tři varianty query
$query             = $sqlCustomer . $sqlType . $sqlTypeDefault . $sqlBaseStr;
$query_no_customer = $sqlType    . $sqlTypeDefault . $sqlBaseStr;
$query_no_type     = $sqlCustomer . $sqlBaseStr;

if (!empty($queryBuilder)) {
    $currentpage .= '?' . http_build_query($queryBuilder);
}

/* hide all reklamace from all other tabs */
$activeTypes = $typeArr ?? [];
if (!empty($activeTypes) && !in_array('7', $activeTypes) && !in_array(7, $activeTypes)) {
    $query             .= ' AND w.status != 7';
    $query_no_customer .= ' AND w.status != 7';
}

// END QUERY BUILDER


$perpage = 40;
if (isset($search) && $search != "") {

    $parts = explode(" ", $search);
    $last = array_pop($parts);
    $first = implode(" ", $parts);

    if ($first == "") {
        $first = 0;
    }
    if ($last == "") {
        $last = 0;
    }

    $hottubs_query = $mysqli->query("SELECT *, w.id as id, DATE_FORMAT(w.loadingdate, '%d. %m. %Y') as dateformated FROM warehouse w, warehouse_products p WHERE w.product = p.connect_name AND w.customer = 1 AND (w.serial_number LIKE '%$search%') order by w.id desc") or die($mysqli->error);

} else {

    $hottubs_max_query = $mysqli->query("SELECT w.id as id FROM warehouse w, warehouse_products p WHERE w.product = p.connect_name AND w.customer = 1 $query") or die($mysqli->error);
    $max = mysqli_num_rows($hottubs_max_query);

    if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {$od = 1;}

    $s_lol = $od - 1;
    $s_pocet = $s_lol * $perpage;
    $pocet_prispevku = $max;

    $hottubs_query = $mysqli->query("SELECT *, w.id as id, DATE_FORMAT(w.loadingdate, '%d. %m. %Y') as dateformated FROM warehouse w, warehouse_products p WHERE w.product = p.connect_name AND w.customer = 1 $query order by w.id desc limit " . $s_pocet . "," . $perpage) or die($mysqli->error);

}



function button($data)
{
    global $mysqli;
    global $currentpage;
    global $pageSymbol;
    global $query;

    $param_name = $data['param_name'];

    $this_query = $query;
    if (isset($data['param']) && $data['param'] != '') {
        $safe_param = str_replace('.', '\.', $param_name);
        $this_query = preg_replace('/AND ' . $safe_param . ' = \'' . preg_quote($data['param'], '/') . '\' ?/', '', $this_query);
    }

    if ($data['request_name'] == 'type') {
        $this_query = str_replace(' AND (status < 3 OR status > 4) ', '', $this_query);
    }

    if ($data['request_name'] == 'location') {
        $this_query = str_replace(' AND (status < 3 OR status > 4) ', ' AND status < 4 ', $this_query);
        $this_query = preg_replace('/ AND \(w\.location_id = \'[0-9]+\' OR w\.reserved_showroom = \'[0-9]+\'\) ?/', '', $this_query);
    }

    $thisPage = preg_replace('/&?' . $data['request_name'] . '=[^&]*/', '', $currentpage);

    if ($thisPage == 'virivky') { $pageSymbol = '?'; } elseif ($thisPage != 'virivky?') { $pageSymbol = '&'; }

    if (isset($data['param']) && $data['param'] != '' && $data['param'] == $data['current_param'] && isset($data['current_param'])) {$button = 'btn-primary';} else { $button = 'btn-white';}

    $max_query = $mysqli->query("SELECT w.id as id FROM warehouse w, warehouse_products p WHERE w.product = p.connect_name AND w.customer = 1 AND $param_name = '" . $data['current_param'] . "' $this_query") or die($mysqli->error);
    $max = mysqli_num_rows($max_query);

    if($data['request_name'] == 'type' && $data['current_param'] == 7){ $max = 'unknown'; }

    if ($param_name != 'w.product' || $max != 0) {
        $button = '<a href="' . $thisPage . $pageSymbol . $data['url'] . '" style="padding: 5px 11px !important;" class="btn ' . $button . '">
						' . $data['name'] . ' (' . $max . ')</a>';
        return $button;
    }
}

// Nová funkce exkluzivně pro bezpečné počítání specifikací
function spec_button($data)
{
    global $mysqli;
    global $currentpage;
    global $pageSymbol;
    global $query;

    $specs_id = (int)$data['specs_id'];
    $value = $mysqli->real_escape_string($data['current_param']);

    // Ořízneme aktuální filtr pro tuto specifikaci, abychom mohli spočítat alternativy
    $this_query = preg_replace('/AND w\.id IN \(SELECT client_id FROM warehouse_specs_bridge WHERE specs_id = ' . $specs_id . ' AND value = \'.*?\'\) /', '', $query);

    $thisPage = preg_replace('/&?' . $data['request_name'] . '=[^&]*/', '', $currentpage);

    if ($thisPage == 'virivky') { $pageSymbol = '?'; } elseif ($thisPage != 'virivky?') { $pageSymbol = '&'; }

    if (isset($data['param']) && $data['param'] != '' && $data['param'] == $data['current_param']) {$button = 'btn-primary';} else { $button = 'btn-white';}

    $max_query = $mysqli->query("SELECT w.id as id FROM warehouse w, warehouse_products p WHERE w.product = p.connect_name AND w.customer = 1 $this_query AND w.id IN (SELECT client_id FROM warehouse_specs_bridge WHERE specs_id = $specs_id AND value = '$value')") or die($mysqli->error);
    $max = mysqli_num_rows($max_query);

    if ($max != 0) {
        return '<a href="' . $thisPage . $pageSymbol . $data['url'] . '" style="padding: 5px 11px !important; margin-top:4px;" class="btn ' . $button . '">' . $data['name'] . ' (' . $max . ')</a>';
    }
}

if (isset($search) && $search != "") { ?>

            <div class="row">
                <div class="col-md-8 col-sm-7">
                    <h2>Na hledaný výraz <i><u>"<?= htmlspecialchars($search) ?>"</u></i> odpovídají tyto vířivky:</h2>
                </div>

                <div class="col-md-4 col-sm-5">

                    <form method="get" role="form">

                        <div class="form-group">
                            <div
                                style="margin-bottom: 12px; width: 260px; float:left; margin-left: 10px;margin-right: 4px;">
                                <input id="cheart" value="<?= htmlspecialchars($search) ?>" type="text" name="q"
                                    class="form-control typeahead" data-remote="data/autosuggest-demands.php?q=%QUERY"
                                    data-template="<div class='thumb-entry'><span class='image'><img src='{{img}}' width=45 height=45 /></span><span class='text' style='width: 75%;'><strong style='overflow: hidden;text-overflow: ellipsis;white-space: nowrap;'>{{value}}</strong><em>{{desc}}</em></span></div>"
                                    placeholder="Hledání..." /></div>

                            <button style="width: 50px; float:left;" type="submit" class="btn btn-default"><i
                                    style=" position: relative; right: 0; top: 0;" class="entypo-search"></i></button>
                        </div>

                    </form>

                </div>
            </div>


            <?php } else {

    $servismaxquery = $mysqli->query('SELECT COUNT(*) AS NumberOfOrders FROM warehouse WHERE customer = 1 AND status <> 4') or die($mysqli->error);
    $servismaxquery = mysqli_fetch_array($servismaxquery);
    $max = $servismaxquery['NumberOfOrders'];

    ?>

            <div class="row" style="margin-bottom: 16px;">
                <div class="col-md-5">
                    <h2 style="float: left"><?= $pagetitle ?> (<?= $max ?>)</h2>

                </div>

                <div class="col-md-2">
                    <center>
                        <ul class="pagination pagination-sm">
                            <?php include VIEW . "/default/pagination.php";?>
                        </ul>
                    </center>
                </div>

                <div class="col-md-3">

                    <form method="get" role="form">

                        <div class="form-group">
                            <div
                                style="margin-bottom: 12px; width: 260px; float:left; margin-left: 10px;margin-right: 4px;">
                                <input id="cheart" type="text" name="q" class="form-control typeahead"
                                    placeholder="Hledání..." /></div>

                            <button style="width: 50px; float:left;" type="submit" class="btn btn-default"><i
                                    style=" position: relative; right: 0; top: 0;" class="entypo-search"></i></button>
                        </div>

                    </form>

                </div>


                <?php if ($access_edit) { ?>
                <div class="col-md-2 col-sm-2" style="text-align: right;float:right;">


                    <a href="pridat-virivku" style=" margin-right: 14px;"
                        class="btn btn-default btn-icon icon-left btn-lg">
                        <i class="entypo-plus"></i>
                        Přidat vířivku
                    </a>

                </div>
                <?php } ?>

            </div>

            <?php
            // ── FILTRAČNÍ PANEL v2 ────────────────────────────────────────────────

            // Počty pro customer row — používáme $query_no_customer (bez customer podmínky)
            $mq_volne   = $mysqli->query("SELECT COUNT(*) c FROM warehouse w, warehouse_products p WHERE w.product = p.connect_name AND w.customer = 1 AND w.demand_id = 0 AND w.reserved_showroom = 0 AND w.status != 7 $query_no_customer") or die($mysqli->error);
            $mq_show_c  = $mysqli->query("SELECT COUNT(*) c FROM warehouse w, warehouse_products p WHERE w.product = p.connect_name AND w.customer = 1 AND w.reserved_showroom <> '0' $query_no_customer") or die($mysqli->error);
            $mq_prodane = $mysqli->query("SELECT COUNT(*) c FROM warehouse w, warehouse_products p WHERE w.product = p.connect_name AND w.customer = 1 AND w.demand_id > 0 AND w.reserved_showroom = 0 $query_no_customer") or die($mysqli->error);
            $cnt_volne   = mysqli_fetch_assoc($mq_volne)['c'];
            $cnt_show_c  = mysqli_fetch_assoc($mq_show_c)['c'];
            $cnt_prodane = mysqli_fetch_assoc($mq_prodane)['c'];

            // Počty pro type row — používáme $query_no_type (bez type podmínky)
            $allStatusList = [0 => 'Ve výrobě', 1 => 'Na cestě', 2 => 'Na skladě', 3 => 'Na showroomu', 6 => 'Uskladněno', 7 => 'Reklamace', 4 => 'Expedované'];
            $cnt_type = [];
            foreach ($allStatusList as $stCode => $stName) {
                if ($stCode === 7) { $cnt_type[$stCode] = null; continue; }
                $r = $mysqli->query("SELECT COUNT(*) c FROM warehouse w, warehouse_products p WHERE w.product = p.connect_name AND w.customer = 1 AND w.status = $stCode $query_no_type") or die($mysqli->error);
                $cnt_type[$stCode] = mysqli_fetch_assoc($r)['c'];
            }

            // Helper: URL builder — strip a param and optionally add new value
            // Sestaví čistou URL ze $_GET, přičemž odstraní/přidá skalární parametr
            function fpUrl($currentpage, $param, $value = null) {
                $params = $_GET;
                // Odstraň param i jeho array variantu
                unset($params[$param], $params[$param . '[]']);
                if ($value !== null) {
                    $params[$param] = $value;
                }
                // Odstraň stránkování
                unset($params['od']);
                $qs = http_build_query($params);
                return 'virivky' . ($qs ? '?' . $qs : '');
            }

            // Sestaví URL pro multi-select toggle (přidá nebo odebere hodnotu z pole)
            function fpUrlMulti($currentpage, $paramWithBrackets, $value, $currentValues) {
                // $paramWithBrackets je např. 'type[]' — základní klíč bez závorek:
                $baseParam = rtrim($paramWithBrackets, '[]');

                $params = $_GET;
                // Normalizuj — $_GET může mít klíč 'type' jako array (PHP auto-parse type[]=x)
                $existing = [];
                if (isset($params[$baseParam]) && is_array($params[$baseParam])) {
                    $existing = array_map('strval', $params[$baseParam]);
                } elseif (isset($params[$baseParam]) && $params[$baseParam] !== '') {
                    $existing = [(string)$params[$baseParam]];
                }

                $val = (string)$value;
                if (in_array($val, $existing)) {
                    // Odeber hodnotu
                    $existing = array_values(array_filter($existing, fn($v) => $v !== $val));
                } else {
                    // Přidej hodnotu
                    $existing[] = $val;
                }

                unset($params[$baseParam]);
                unset($params['od']);
                if (!empty($existing)) {
                    $params[$baseParam] = $existing; // PHP http_build_query zpracuje jako array
                }

                $qs = http_build_query($params);
                return 'virivky' . ($qs ? '?' . $qs : '');
            }
            ?>

            <style>
            /* ── Filtrační panel v2 (řádkový) ── */
            .wt-fc {
                background: #fff;
                border: 1px solid #e0deda;
                border-radius: 10px;
                margin-bottom: 14px;
                overflow: hidden;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            }
            /* Active chips */
            .wt-chips {
                display: none;
                align-items: center; flex-wrap: wrap; gap: 5px;
                padding: 7px 13px;
                background: #f7f6f3;
                border-bottom: 1px solid #e0deda;
            }
            .wt-chips.on { display: flex; }
            .wt-chips-lbl { font-size: 9.5px; text-transform: uppercase; font-weight: 700; letter-spacing: .5px; color: #bbb; margin-right: 3px; }
            .wt-chip {
                display: inline-flex; align-items: center; gap: 4px;
                padding: 2px 8px; background: #1c1c1a; color: #fff;
                border-radius: 5px; font-size: 11px; font-weight: 500;
                text-decoration: none !important; cursor: pointer;
            }
            .wt-chip:hover { opacity: .78; color: #fff; }
            .wt-clr { font-size: 11px; color: #bbb; text-decoration: underline !important; cursor: pointer; margin-left: 4px; }
            .wt-clr:hover { color: #555; }

            /* Rows */
            .wt-row {
                display: flex; align-items: flex-start;
                border-bottom: 1px solid #e0deda;
                min-height: 38px;
            }
            .wt-row:last-child { border-bottom: none; }
            .wt-row.secondary { background: #fafaf8; }

            .wt-lbl {
                flex-shrink: 0; width: 112px;
                padding: 10px 13px;
                font-size: 9.5px; font-weight: 700;
                text-transform: uppercase; letter-spacing: .5px;
                color: #aaa; line-height: 1.3;
                border-right: 1px solid #e0deda;
                background: #fafaf8;
                align-self: stretch;
                display: flex;
                align-items: flex-start;
            }
            .wt-row.secondary .wt-lbl { color: #ccc; }

            .wt-tags {
                flex: 1; display: flex; flex-wrap: wrap; gap: 5px;
                padding: 7px 11px; align-items: center;
            }

            /* Tags */
            .wt-tag {
                display: inline-flex; align-items: center; gap: 3px;
                padding: 4px 9px; border-radius: 6px;
                font-size: 12px; font-weight: 400;
                background: #f0f0ec; color: #444;
                text-decoration: none !important;
                border: 1.5px solid transparent;
                transition: all .1s; white-space: nowrap; cursor: pointer;
            }
            .wt-tag:hover { background: #fff; border-color: #d0cfc9; color: #111; }
            .wt-tag.active { background: #1c1c1a; color: #fff !important; border-color: #1c1c1a; }
            .wt-tag.zero { opacity: .3; pointer-events: none; }
            .wt-row.secondary .wt-tag { font-size: 11px; padding: 3px 8px; background: #ebebе6; }
            .wt-tag .n { font-size: 10px; opacity: .5; font-variant-numeric: tabular-nums; }
            .wt-tag.active .n { opacity: .7; }

            /* Pills (customer + type rows) */
            .wt-pill {
                display: inline-flex; align-items: center; gap: 5px;
                padding: 4px 11px; border-radius: 999px;
                font-size: 12px; font-weight: 500;
                text-decoration: none !important; cursor: pointer;
                border: 1.5px solid #e0deda; background: #fff; color: #777;
                transition: all .1s; white-space: nowrap;
            }
            .wt-pill:hover { border-color: #999; color: #1c1c1a; }
            .wt-pill.active { background: #1c1c1a; color: #fff !important; border-color: #1c1c1a; }
            .wt-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
            .wt-pill .n { font-size: 10px; opacity: .65; font-variant-numeric: tabular-nums; }
            .wt-pill.active .n { opacity: .8; }

            .wt-sep { width: 1px; height: 18px; background: #e0deda; flex-shrink: 0; margin: 0 2px; }

            /* Inline model search */
            .wt-msearch { position: relative; display: inline-flex; align-items: center; }
            .wt-msearch svg { position: absolute; left: 7px; width: 12px; height: 12px; color: #ccc; }
            .wt-msearch input {
                padding: 4px 9px 4px 25px;
                border: 1.5px solid #e8e7e3; border-radius: 6px;
                font-size: 12px; font-family: inherit;
                background: #f7f7f4; color: #333; outline: none;
                width: 145px; transition: border-color .12s, width .2s;
            }
            .wt-msearch input:focus { border-color: #1c1c1a; background: #fff; width: 195px; }

            /* Multi-select indicator */
            .wt-pill.active + .wt-pill,
            .wt-pill.active ~ .wt-pill { }
            .wt-row--multi .wt-lbl::after {
                content: 'multi';
                display: block;
                font-size: 8px;
                font-weight: 600;
                color: #c0bcb5;
                letter-spacing: .3px;
                text-transform: uppercase;
                margin-top: 2px;
            }
            </style>

            <?php
            // ── Sestavíme aktivní chipsy (server-side, pro případ JS off) ──
            $wtChips = [];
            if (isset($brand))    $wtChips[] = ['lbl'=>'Řada',      'val'=>ucfirst($brand),    'url'=>fpUrl($currentpage,'brand')];
            if (isset($category)) $wtChips[] = ['lbl'=>'Model',      'val'=>ucfirst($category), 'url'=>fpUrl($currentpage,'category')];
            if (isset($spec5) && $spec5 !== null && $spec5 !== '') {
                $wtChips[] = ['lbl'=>'Provedení', 'val'=>$spec5, 'url'=>fpUrl($currentpage,'spec5')];
            }
            if (isset($spec1))    $wtChips[] = ['lbl'=>'Akryl',      'val'=>$spec1,             'url'=>fpUrl($currentpage,'spec1')];
            if (isset($spec2))    $wtChips[] = ['lbl'=>'Opláštění',  'val'=>$spec2,             'url'=>fpUrl($currentpage,'spec2')];
            if (isset($location)) {
                $lq = $mysqli->query("SELECT name FROM shops_locations WHERE id = ".(int)$location) or die($mysqli->error);
                $ln = ($lr = mysqli_fetch_assoc($lq)) ? $lr['name'] : '#'.$location;
                $wtChips[] = ['lbl'=>'Lokace', 'val'=>$ln, 'url'=>fpUrl($currentpage,'location')];
            }
            if (isset($customerArr) && $customerArr !== null) {
                $custNames = ['0'=>'Volné','1'=>'Prodané','3'=>'Na showroomu'];
                foreach ($customerArr as $cv) {
                    $wtChips[] = ['lbl'=>'Zákazník', 'val'=>$custNames[$cv]??$cv, 'url'=>fpUrlMulti($currentpage,'customer[]',$cv,$customerArr)];
                }
            }
            if (isset($typeArr) && $typeArr !== null) {
                $typeNames = [0=>'Ve výrobě',1=>'Na cestě',2=>'Na skladě',3=>'Na showroomu',4=>'Expedované',6=>'Uskladněno',7=>'Reklamace'];
                foreach ($typeArr as $tv) {
                    $wtChips[] = ['lbl'=>'Stav', 'val'=>$typeNames[(int)$tv]??$tv, 'url'=>fpUrlMulti($currentpage,'type[]',$tv,$typeArr)];
                }
            }
            if (!empty($_REQUEST['special'])) {
                $wtChips[] = ['lbl'=>'Tech', 'val'=>($_REQUEST['special']==3?'MicroSilk':'InClear'), 'url'=>'virivky'];
            }
            ?>

            <div class="wt-fc">

                <!-- Aktivní chipsy -->
                <div class="wt-chips <?= !empty($wtChips) ? 'on' : '' ?>">
                    <span class="wt-chips-lbl">Filtry</span>
                    <?php foreach ($wtChips as $ch): ?>
                    <a href="<?= htmlspecialchars($ch['url']) ?>" class="wt-chip">
                        <span style="opacity:.55;font-size:9.5px;text-transform:uppercase;letter-spacing:.3px"><?= $ch['lbl'] ?></span>
                        <?= htmlspecialchars($ch['val']) ?> &times;
                    </a>
                    <?php endforeach; ?>
                    <?php if (!empty($wtChips)): ?>
                    <a href="virivky" class="wt-clr">Smazat vše</a>
                    <?php endif; ?>
                </div>

                <!-- ROW: Model / Řada -->
                <?php
                $brandsQ = $mysqli->query("SELECT brand FROM warehouse_products WHERE active='yes' AND brand!='' AND customer=1 GROUP BY brand") or die($mysqli->error);
                $brandRows = mysqli_fetch_all($brandsQ, MYSQLI_ASSOC);

                $modelsQ = $mysqli->query("SELECT * FROM warehouse_products WHERE active='yes' AND customer=1 ORDER BY rank") or die($mysqli->error);
                $modelRows = mysqli_fetch_all($modelsQ, MYSQLI_ASSOC);
                ?>
                <div class="wt-row">
                    <div class="wt-lbl">Model /<br>Řada</div>
                    <div class="wt-tags">
                        <!-- Řady -->
                        <?php foreach ($brandRows as $b):
                            $isAct = isset($brand) && $brand == $b['brand'];
                            $href  = fpUrl($currentpage, 'brand', $isAct ? null : $b['brand']);
                            $cnt   = mysqli_fetch_assoc($mysqli->query("SELECT COUNT(*) c FROM warehouse w, warehouse_products p WHERE w.product=p.connect_name AND w.customer=1 AND p.brand='".$mysqli->real_escape_string($b['brand'])."' $query"))['c'];
                            if (!$cnt && !$isAct) continue;
                        ?>
                        <a href="<?= htmlspecialchars($href) ?>" class="wt-tag <?= $isAct?'active':'' ?>"><?= htmlspecialchars(ucfirst($b['brand'])) ?> <span class="n"><?= $cnt ?></span></a>
                        <?php endforeach; ?>

                        <span class="wt-sep"></span>

                        <!-- Model search -->
                        <div class="wt-msearch">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <input type="text" placeholder="Hledat model…" oninput="wtMS(this.value)">
                        </div>

                        <span class="wt-sep"></span>

                        <!-- Modely -->
                        <span id="wt-model-tags" style="display:contents">
                        <?php foreach ($modelRows as $m):
                            $isAct = isset($category) && $category == $m['connect_name'];
                            $href  = fpUrl($currentpage, 'category', $isAct ? null : $m['connect_name']);
                            $cnt   = mysqli_fetch_assoc($mysqli->query("SELECT COUNT(*) c FROM warehouse w, warehouse_products p WHERE w.product=p.connect_name AND w.customer=1 AND w.product='".$mysqli->real_escape_string($m['connect_name'])."' $query"))['c'];
                            if (!$cnt && !$isAct) continue;
                        ?>
                        <a href="<?= htmlspecialchars($href) ?>" class="wt-tag <?= $isAct?'active':'' ?>" data-label="<?= htmlspecialchars(strtolower($m['fullname'])) ?>"><?= htmlspecialchars(ucfirst($m['fullname'])) ?> <span class="n"><?= $cnt ?></span></a>
                        <?php endforeach; ?>
                        </span>
                    </div>
                </div>

                <!-- ROW: Zákazník (customer) — přiřazení k poptávce — MULTI-SELECT -->
                <div class="wt-row wt-row--multi">
                    <div class="wt-lbl">Zákazník</div>
                    <div class="wt-tags">
                        <?php
                        $activeCustomers = $customerArr ?? [];
                        $urlClearCustomer = preg_replace('/[?&]customer(?:%5B|\[)[^&]*/', '', $currentpage);
                        $urlClearCustomer = preg_replace('/^([^?]*)&/', '$1?', $urlClearCustomer);
                        $urlClearCustomer = rtrim($urlClearCustomer, '?');
                        ?>
                        <a href="<?= htmlspecialchars($urlClearCustomer) ?>" class="wt-pill <?= empty($activeCustomers) ? 'active' : '' ?>">Vše</a>
                        <?php
                        $custDefs = [
                            '0' => ['label'=>'Volné',        'color'=>'#16a34a', 'cnt'=>$cnt_volne],
                            '3' => ['label'=>'Na showroomu', 'color'=>'#7c3aed', 'cnt'=>$cnt_show_c],
                            '1' => ['label'=>'Prodané',       'color'=>'#dc2626', 'cnt'=>$cnt_prodane],
                        ];
                        foreach ($custDefs as $cVal => $cDef):
                            $isAct = in_array((string)$cVal, array_map('strval', $activeCustomers));
                            $href  = fpUrlMulti($currentpage, 'customer[]', $cVal, $activeCustomers);
                        ?>
                        <a href="<?= htmlspecialchars($href) ?>" class="wt-pill <?= $isAct ? 'active' : '' ?>" title="Kliknutím přidáš/odebereme filtr">
                            <span class="wt-dot" style="background:<?= $cDef['color'] ?>"></span> <?= htmlspecialchars($cDef['label']) ?> <span class="n"><?= $cDef['cnt'] ?></span>
                        </a>
                        <?php endforeach; ?>
                        <span class="wt-sep"></span>
                        <a href="virivky?special=3" class="wt-pill <?= (!empty($_REQUEST['special']) && $_REQUEST['special']==3)?'active':'' ?>">MicroSilk</a>
                        <a href="virivky?special=8" class="wt-pill <?= (!empty($_REQUEST['special']) && $_REQUEST['special']==8)?'active':'' ?>">InClear</a>
                    </div>
                </div>

                <!-- ROW: Stav vířivky (type) — fyzický stav — MULTI-SELECT -->
                <div class="wt-row wt-row--multi">
                    <div class="wt-lbl">Stav</div>
                    <div class="wt-tags">
                        <?php
                        $activeTypes = $typeArr ?? [];
                        $urlClearType = preg_replace('/[?&]type(?:%5B|\[)[^&]*/', '', $currentpage);
                        $urlClearType = preg_replace('/^([^?]*)&/', '$1?', $urlClearType);
                        $urlClearType = rtrim($urlClearType, '?');
                        ?>
                        <a href="<?= htmlspecialchars($urlClearType) ?>" class="wt-pill <?= empty($activeTypes) ? 'active' : '' ?>">Vše</a>
                        <?php
                        $typeColors = [0=>'#f59e0b',1=>'#3b82f6',2=>'#16a34a',3=>'#7c3aed',6=>'#64748b',7=>'#f97316',4=>'#94a3b8'];
                        foreach ($allStatusList as $stCode => $stName):
                            $isAct = in_array((string)$stCode, array_map('strval', $activeTypes));
                            $href  = fpUrlMulti($currentpage, 'type[]', $stCode, $activeTypes);
                            $cntSt = ($stCode === 7) ? null : $cnt_type[$stCode];
                        ?>
                        <a href="<?= htmlspecialchars($href) ?>" class="wt-pill <?= $isAct?'active':'' ?>" title="Kliknutím přidáš/odebereme filtr">
                            <span class="wt-dot" style="background:<?= $typeColors[$stCode] ?? '#aaa' ?>"></span>
                            <?= htmlspecialchars($stName) ?>
                            <?php if ($cntSt !== null): ?><span class="n"><?= $cntSt ?></span><?php endif; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- ROW: Provedení (spec5) — single select -->
                <div class="wt-row">
                    <div class="wt-lbl">Provedení</div>
                    <div class="wt-tags">
                        <?php
                        $s5q = $mysqli->query("SELECT value FROM warehouse_specs_bridge WHERE specs_id=5 AND value!='' AND value!='-' GROUP BY value ORDER BY value ASC") or die($mysqli->error);
                        while ($s5 = mysqli_fetch_assoc($s5q)):
                            $isAct = isset($spec5) && $spec5 == $s5['value'];
                            $href  = fpUrl($currentpage, 'spec5', $isAct ? null : $s5['value']);
                            $cnt   = mysqli_fetch_assoc($mysqli->query("SELECT COUNT(*) c FROM warehouse w, warehouse_products p WHERE w.product=p.connect_name AND w.customer=1 $query AND w.id IN (SELECT client_id FROM warehouse_specs_bridge WHERE specs_id=5 AND value='".$mysqli->real_escape_string($s5['value'])."')"))['c'];
                            if (!$cnt && !$isAct) continue;
                        ?>
                        <a href="<?= htmlspecialchars($href) ?>" class="wt-tag <?= $isAct?'active':'' ?>"><?= htmlspecialchars(ucfirst($s5['value'])) ?> <span class="n"><?= $cnt ?></span></a>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- ROW: Lokace -->
                <div class="wt-row">
                    <div class="wt-lbl">Lokace skladu</div>
                    <div class="wt-tags">
                        <?php
                        // Přesná kopie logiky button() pro location:
                        // odstraní (status < 3 OR status > 4), nahradí za status < 4
                        // odstraní existující location podmínku z query
                        // Query pro počítání lokací — musí odpovídat query builderu (location_id OR reserved_showroom)
                        // Odstraníme existující location podmínku a status filtr upravíme na < 4
                        $q_loc = $query;
                        $q_loc = str_replace('AND (status < 3 OR status > 4) ', 'AND status < 4 ', $q_loc);
                        $q_loc = preg_replace('/AND \(w\.location_id = \'\d+\' OR w\.reserved_showroom = \'\d+\'\) /', '', $q_loc);

                        $locQ = $mysqli->query("SELECT * FROM shops_locations ORDER BY type") or die($mysqli->error);
                        while ($sl = mysqli_fetch_assoc($locQ)):
                            $isAct = isset($location) && (int)$location === (int)$sl['id'];
                            $href  = fpUrl($currentpage, 'location', $isAct ? null : $sl['id']);
                            $loc_res = $mysqli->query("SELECT COUNT(*) c FROM warehouse w, warehouse_products p WHERE w.product=p.connect_name AND w.customer=1 AND (w.location_id='".(int)$sl['id']."' OR w.reserved_showroom='".(int)$sl['id']."') $q_loc") or die($mysqli->error);
                            $cnt = mysqli_fetch_assoc($loc_res)['c'];
                            $zero  = (!$cnt && !$isAct) ? 'zero' : '';
                        ?>
                        <a href="<?= htmlspecialchars($href) ?>" class="wt-tag <?= $isAct?'active':$zero ?>"><?= htmlspecialchars($sl['name']) ?> <span class="n"><?= $cnt ?></span></a>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- ROW: Akryl (spec1) — secondary -->
                <div class="wt-row secondary">
                    <div class="wt-lbl">Akryl</div>
                    <div class="wt-tags">
                        <?php
                        $s1q = $mysqli->query("SELECT value FROM warehouse_specs_bridge WHERE specs_id=1 AND value!='' AND value!='-' GROUP BY value ORDER BY value ASC") or die($mysqli->error);
                        while ($s1 = mysqli_fetch_assoc($s1q)):
                            $isAct = isset($spec1) && $spec1 == $s1['value'];
                            $href  = fpUrl($currentpage, 'spec1', $isAct ? null : $s1['value']);
                            $cnt   = mysqli_fetch_assoc($mysqli->query("SELECT COUNT(*) c FROM warehouse w, warehouse_products p WHERE w.product=p.connect_name AND w.customer=1 $query AND w.id IN (SELECT client_id FROM warehouse_specs_bridge WHERE specs_id=1 AND value='".$mysqli->real_escape_string($s1['value'])."')"))['c'];
                            if (!$cnt && !$isAct) continue;
                        ?>
                        <a href="<?= htmlspecialchars($href) ?>" class="wt-tag <?= $isAct?'active':'' ?>"><?= htmlspecialchars(ucfirst($s1['value'])) ?> <span class="n"><?= $cnt ?></span></a>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- ROW: Opláštění (spec2) — secondary -->
                <div class="wt-row secondary">
                    <div class="wt-lbl">Opláštění</div>
                    <div class="wt-tags">
                        <?php
                        $s2q = $mysqli->query("SELECT value FROM warehouse_specs_bridge WHERE specs_id=2 AND value!='' AND value!='-' GROUP BY value ORDER BY value ASC") or die($mysqli->error);
                        while ($s2 = mysqli_fetch_assoc($s2q)):
                            $isAct = isset($spec2) && $spec2 == $s2['value'];
                            $href  = fpUrl($currentpage, 'spec2', $isAct ? null : $s2['value']);
                            $cnt   = mysqli_fetch_assoc($mysqli->query("SELECT COUNT(*) c FROM warehouse w, warehouse_products p WHERE w.product=p.connect_name AND w.customer=1 $query AND w.id IN (SELECT client_id FROM warehouse_specs_bridge WHERE specs_id=2 AND value='".$mysqli->real_escape_string($s2['value'])."')"))['c'];
                            if (!$cnt && !$isAct) continue;
                        ?>
                        <a href="<?= htmlspecialchars($href) ?>" class="wt-tag <?= $isAct?'active':'' ?>"><?= htmlspecialchars(ucfirst($s2['value'])) ?> <span class="n"><?= $cnt ?></span></a>
                        <?php endwhile; ?>
                    </div>
                </div>

            </div><!-- /.wt-fc -->

            <script>
            function wtMS(q) {
                q = q.toLowerCase();
                document.querySelectorAll('#wt-model-tags .wt-tag').forEach(function(t) {
                    t.style.display = (t.dataset.label || '').includes(q) ? '' : 'none';
                });
            }
            </script>

            <?php }


if(!empty($_REQUEST['special'])){

    // 3 = microsilk
    // 8 = inclear
    $spec_id = (int)$_REQUEST['special'];

    $pocet_prispevku = 0;

    $hottubs_query = $mysqli->query("SELECT *, w.id as id, DATE_FORMAT(w.loadingdate, '%d. %m. %Y') as dateformated FROM warehouse w, warehouse_products p, warehouse_specs_bridge b WHERE w.product = p.connect_name AND w.customer = 1 AND b.client_id = w.id AND b.specs_id = '".$spec_id."' AND b.value = 'Ano' AND w.status != 4 GROUP BY w.id order by w.id desc") or die($mysqli->error);

}

if (mysqli_num_rows($hottubs_query) > 0) {

    while ($hottub = mysqli_fetch_assoc($hottubs_query)) {

        if ($hottub['demand_id'] != 0) {

            $demandquery = $mysqli->query("SELECT id, user_name FROM demands WHERE id = '" . $hottub['demand_id'] . "'");
            $demand = mysqli_fetch_array($demandquery);

        }

        if ($hottub['serial_number'] != "") {$name = $hottub['serial_number'];} else { $name = '#' . $hottub['id'];}
        ?>


        <div class="member-entry" style="margin-bottom: 0px;" >
            <?php if (isset($hottub['demand_id']) && $hottub['demand_id'] == 0) { ?><script type="text/javascript">
                jQuery(document).ready(function($)
                {

                    $('#priradit-<?= $hottub['id'] ?>').click(function() {

                        $('#priradit-<?= $hottub['id'] ?>').hide( "slow");
                        $('#prirazeni-<?= $hottub['id'] ?>').show( "slow");

                    });

                    $('#cancel-<?= $hottub['id'] ?>').click(function() {


                        $('#prirazeni-<?= $hottub['id'] ?>').hide( "slow");
                        $('#priradit-<?= $hottub['id'] ?>').show( "slow");

                    });
                });
            </script>
            <?php } ?>



            <div class="member-details" style="width: 100% !important;">
                <a class="member-img" style="width: 4%; overflow: hidden; height: 42px; border-bottom: 1px solid #e0e0e0; border-top: 1px solid #e0e0e0">
                    <img style=" margin-top: -12%;" src="https://www.wellnesstrade.cz/admin/data/images/customer/<?= $hottub['product'] ?>.png" width="100px" class="img-rounded" />
                </a>

                <div class="col-sm-9" style="width: 73%;">
                    <div style="min-width: 47%; float: left;">
                <h4 style="float: left; margin-left: 0;">

                    <?php
                    if ($hottub['demand_id'] != 0 && $hottub['status'] != 4) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-red btn-sm">Prodaná</button>';
                    }

                    if (isset($hottub['status']) && $hottub['status'] == 0) {

                        echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-orange btn-sm">Ve výrobě</button>';

                    } elseif (isset($hottub['status']) && $hottub['status'] == 1) {

                        echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-blue btn-sm">Na cestě</button><button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-primary btn-sm">očekávané naskladnění <strong><u>' . $hottub['dateformated'] . '</u></strong></button>';

                    } elseif (isset($hottub['status']) && $hottub['status'] == 2) {

                        echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-green btn-sm">Na skladě</button>';

                    } elseif (isset($hottub['status']) && $hottub['status'] == 3) {

                        echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-brown btn-sm">Na showroomu</button>';

                    } elseif (isset($hottub['status']) && $hottub['status'] == 4) {

                        echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-purple btn-sm">Expedovaná</button>';

                    } elseif (isset($hottub['status']) && $hottub['status'] == 6) {

                        echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-info btn-sm">Uskladněná</button>';

                    } elseif (isset($hottub['status']) && $hottub['status'] == 7) {

                        echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-info btn-sm">Reklamace</button>';

                    }

                    if (isset($hottub['status']) && $hottub['status'] != 4) {

                        $location_query = $mysqli->query("SELECT name FROM shops_locations WHERE id = '" . $hottub['location_id'] . "'") or die($mysqli->error);
                        $location = mysqli_fetch_array($location_query);

                        echo '<button style="margin-right: 4px; margin-top: -3px; background-color: #338fd8; border-color: #338fd8;" type="button" class="btn btn-brown btn-sm">' . $location['name'] . '</button>';

                    }
                    ?>
                    <a href="zobrazit-virivku?id=<?= $hottub['id'] ?>"><?= $name ?> | <?= $hottub['brand'] . ' ' . ucfirst($hottub['fullname']) ?></a>
                    <?php if ($hottub['demand_id'] != 0) { ?>
                    <a href="/admin/pages/demands/zobrazit-poptavku?id=<?= $demand['id'] ?>"><small style="margin-left: 2px; color: #000;"><?php if($hottub['reserved'] == 1){ echo '<span style="color: #cc2423; text-decoration: underline;">Rezervace do <strong>'.date('d. m. Y', strtotime($hottub['reserved_date'])).'</strong></span>'; }?> » <?= $demand['user_name'] ?></small></a>
                <?php } elseif($hottub['reserved_showroom'] != 0){

                        $location_query = $mysqli->query("SELECT * FROM shops_locations WHERE id = '".$hottub['reserved_showroom']."'") or die($mysqli->error);
                        $location = mysqli_fetch_array($location_query);
                        ?>


                        <small class="text-info" style=" margin-top: 2px; color: #0077b1;">
                          Rezervace na showroom » <?= $location['name'] ?></small>

                    <?php } else { ?>
                    <small id="priradit-<?= $hottub['id'] ?>" style="margin-left: 2px; cursor:pointer; color: #00a651;">Volná</small>

                <?php } ?>

                </h4>

                    <div id="prirazeni-<?= $hottub['id'] ?>" class="form-group" style="display:none;float:left;">

                        <form role="form" method="post" name="myform" action="virivky?action=demandchange&id=<?= $hottub['id'] ?>">

                            <div class="col-sm-6" style="width: 260px;">
                                <?php
                                $demandsq = $mysqli->query("SELECT user_name, id FROM demands WHERE (customer = 1 OR customer = 3) AND product = '" . $hottub['product'] . "' and status <> 5 and status <> 6") or die($mysqli->error);

                                ?>
                                <select name="demand" class="select2" data-allow-clear="true" data-placeholder="Vyberte poptávku...">
                                    <option></option>
                                    <optgroup label="<?= strtoupper($hottub['product']) ?> poptávky">
                                        <?php while ($dem = mysqli_fetch_array($demandsq)) {
                                            $find = $mysqli->query("SELECT id FROM warehouse WHERE demand_id = '" . $dem['id'] . "' AND product = '" . $hottub['product'] . "'");
                                            if (mysqli_num_rows($find) != 1) { ?><option value="<?= $dem['id'] ?>">»<?= $dem['user_name'] ?></option><?php }}?>
                                    </optgroup>
                                </select>

                            </div>
                            <button style="float: left;margin-left: -9px;    height: 42px;" type="submit" class="btn btn-green"> <i class="entypo-pencil"></i> </button>
                            <a id="cancel-<?= $hottub['id'] ?>" style="float: left;margin-left: 4px;    "><button type="button" class="btn btn-white" style="height: 42px;"> <i class="entypo-cancel"></i> </button></a>
                        </form>
                    </div>

                </div>

                    <div class="col-sm-4" style="color: #000; width: 200px; margin-left: 30px; margin-top: 7px">
                        <button class=" btn btn-default btn-sm">
                        <?php

                        $now = date("Y-m-d", strtotime("now"));

                        if($hottub['loadingdate'] != '0000-00-00'){

                            $dateadd = date("Y-m-d", strtotime($hottub['loadingdate']));

                            $delivery_date = date("d. m. y", strtotime($hottub['loadingdate']));

                            $date1 = new DateTime($dateadd);
                            $date2 = new DateTime($now);
                            $interval = $date1->diff($date2);
                            $nummero = $interval->days;

                            ?>
                            Termín doručení je <strong><?= $delivery_date ?> (<?= $nummero ?> dnů)</strong>.
                            <?php


                        }elseif ($hottub['created_date'] != '0000-00-00' && $hottub['status'] == 0) {

                            $dateadd = date("Y-m-d", strtotime("+77 days", strtotime($hottub['created_date'])));

                            $dateadd2 = date("Y-m-d", strtotime("+42 days", strtotime($hottub['created_date'])));

                            $estimated = date("d. m. y", strtotime("+77 days", strtotime($hottub['created_date'])));

                            $correction = date("d. m. y", strtotime("+42 days", strtotime($hottub['created_date'])));

                            $date1 = new DateTime($dateadd);
                            $date2 = new DateTime($now);
                            $interval = $date1->diff($date2);
                            $nummero = $interval->days;

                            $date3 = new DateTime($dateadd2);
                            $interval2 = $date3->diff($date2);
                            $nummero2 = $interval2->days;

                            ?>
                            Orientační termín doručení je <strong><?= $estimated ?> (<?= $nummero ?> dnů)</strong>. Orientační termín bude upřesněn do <strong><?= $nummero2 ?> dnů</strong>.

                        <?php } ?>
                        </button>
                    </div>
                    <div style="clear: both"></div>


                </div>

                <?php if ($access_edit) { ?>
                    <div class="col-sm-3" style="float:right;margin-top: 0px;padding-right: 0px; width: 23%;">

                        <a href="zobrazit-virivku?id=<?= $hottub['id'] ?>" class="btn btn-default btn-sm btn-icon icon-left">
                            <i class="entypo-search"></i>
                            Zobrazit
                        </a>
                        <span style=" border-right: 1px solid #cccccc; margin: 0 10px 0 5px;"></span>
                        <a href="upravit-virivku?id=<?= $hottub['id'] ?>" class="btn btn-primary btn-sm btn-icon icon-left">
                            <i class="entypo-pencil"></i>
                            Upravit
                        </a>
                        <span style=" border-right: 1px solid #cccccc; margin: 0 10px 0 5px;"></span>
                        <a data-id="<?= $hottub['id'] ?>" data-type="hottub" class="toggle-modal-remove btn btn-danger btn-sm btn-icon icon-left">
                            <i class="entypo-cancel"></i>
                            Odstranit
                        </a>
                    </div>
                <?php } ?>



                <div class="clear"></div>

                <div class="row info-list">


                    <?php if (!empty($hottub['description'])) { ?>

                        <hr style="float: left;width: 100%;margin-top: 10px; margin-bottom: 10px;">
                        <div class="clear"></div>
                        <div class="alert alert-info" style="margin-right: 20px; margin-bottom: 0;"><i class="entypo-info"></i> <?= $hottub['description'] ?></div>

                    <?php } ?>
                    <hr style="float: left;width: 100%;margin-top: 10px; margin-bottom: 8px;">

                    <div class="show-specs btn btn-white btn-sm" style="cursor: pointer;"><i class="entypo-search"></i> zobrazit specifikace u vířivky</div>
                    <span style=" border-right: 1px solid #cccccc;margin-top: 4px; margin: 0 10px 0 5px;"></span>


                    <?php


                    $critical_specs_query = $mysqli->query("SELECT * FROM specs s, warehouse_specs_bridge sb WHERE s.id = sb.specs_id AND sb.client_id = '".$hottub['id']."' AND (s.id = 5 OR s.id = 1 OR s.id = 2)  GROUP BY s.id ORDER BY s.rank") or die($mysqli->error);
                    while($critical_specs = mysqli_fetch_array($critical_specs_query)){

                    ?>
                    <div class="show-specs btn btn-white btn-sm" style="margin-left: 4px; padding: 5px 20px;"><?= $critical_specs['name'] ?>: <strong style="font-weight: 500;"><?= $critical_specs['value'] ?></strong></div>
                    <?php } ?>
                    <div class="hidden-specs" style="display: none;">

                    <?php if ($hottub['demand_id'] != 0) { ?>
                        <table style="width: 100%; float: left; margin-top: 10px;">
                            <?php if (isset($hottub['customer']) && $hottub['customer'] == 1) {
                                $oldrank = '';
                                $i = 0;
                                $specsquery = $mysqli->query('SELECT * FROM specs WHERE product = 1 AND supplier = 1 order by rank asc') or die($mysqli->error);
                                while ($specs = mysqli_fetch_array($specsquery)) {
                                    $paramsquery = $mysqli->query('SELECT value FROM warehouse_specs_bridge WHERE specs_id = "' . $specs['id'] . '" AND client_id = "' . $hottub['id'] . '"') or die($mysqli->error);
                                    $params = mysqli_fetch_array($paramsquery);

                                    $specsdemquery = $mysqli->query('SELECT value FROM demands_specs_bridge WHERE specs_id = "' . $specs['id'] . '" AND client_id = "' . $demand['id'] . '"') or die($mysqli->error);
                                    $demandsspecs = mysqli_fetch_array($specsdemquery);

                                    $i++;

                                    if (isset($specs['rank']) && $specs['rank'] == 0) {$specs['bg_colour'] = '#ace6ce';}
                                    $newrank = $specs['bg_colour'];

                                    if ($newrank != $oldrank) {echo '</table><table style="width: 100%; float: left; margin-top: 10px;">';
                                        $i = 1;}

                                    if ($i == 1) {echo '<tr>';}
                                    ?>

                                    <td style="background-color: <?= $specs['bg_colour'] ?>; color: #000; width: 12.5%; padding: 3px 5px; border-bottom: 1px solid #fff;border-right: 1px solid #fff;"><strong><?= $specs['name'] ?></strong></td>
                                    <td style="background-color: <?= $specs['bg_colour'] ?>;  color: #000; width: 12.5%; padding: 3px 5px; border-bottom: 1px solid #fff; border-right: 4px solid #fff;text-align: center;"><?= !empty($params['value']) ? $params['value'] : '-' ?>
                                    <?php if (isset($demandsspecs) && isset($params) && $demandsspecs['value'] != $params['value']
                                        && $specs['is_demand'] == 1 && $params['value'] != "") {
                                            ?><i style="color: #d42020;font-size: 16px; margin-left: 1px;margin-top: -3px;position: absolute;" class="entypo-attention" data-toggle="tooltip" data-placement="top" title="" data-original-title="Specifikace u vířivky neodpovídá zvolené specifikaci u poptávky."></i><?php } ?>
                                    </td>

                                    <?php

                                    if ($i % 4 == 0) {echo '</tr><tr>';}

                                    $oldrank = $specs['bg_colour'];
                                }}?>
                        </table>

                    <?php } else { ?>

                        <table style="width: 100%; float: left; margin-top: 10px;">
                            <?php if (isset($hottub['customer']) && $hottub['customer'] == 1) {
                                $oldrank = '';
                                $i = 0;
                                $specsquery = $mysqli->query('SELECT * FROM specs WHERE product = 1 AND supplier = 1 order by rank asc') or die($mysqli->error);
                                while ($specs = mysqli_fetch_array($specsquery)) {
                                    $paramsquery = $mysqli->query('SELECT value FROM warehouse_specs_bridge WHERE specs_id = "' . $specs['id'] . '" AND client_id = "' . $hottub['id'] . '"') or die($mysqli->error);
                                    $params = mysqli_fetch_array($paramsquery);

                                    $i++;

                                    if (isset($specs['rank']) && $specs['rank'] == 0) {$specs['bg_colour'] = '#ace6ce';}
                                    $newrank = $specs['bg_colour'];

                                    if ($newrank != $oldrank) {echo '</table><table style="width: 100%; float: left; margin-top: 10px;">';
                                        $i = 1;}

                                    if ($i == 1) {echo '<tr>';}
                                    ?>

                                    <td style="background-color: <?= $specs['bg_colour'] ?>; color: #000; width: 12.5%;padding: 3px 5px; border-bottom: 1px solid #fff;border-right: 1px solid #fff;"><strong><?= $specs['name'] ?></strong></td>
                                    <td style="background-color: <?= $specs['bg_colour'] ?>;  color: #000; width: 12.5%;padding: 3px 5px; border-bottom: 1px solid #fff; border-right: 4px solid #fff;text-align: center;"><?= !empty($params['value']) ? $params['value'] : '-' ?></td>

                                    <?php

                                    if ($i % 4 == 0) { echo '</tr><tr>'; }

                                    $oldrank = $specs['bg_colour'];
                                }}?>
                        </table>

                    <?php } ?>
                    <?/*
    if($access_edit){ ?>
    <hr style="float: left;width: 100%;margin-top: 18px; margin-bottom: 3px;">
    <div class="col-sm-3" style="margin-top: 8px; padding: 0; width: 20%;">
    <i class="entypo-right-open-mini"></i>
    Nákupní cena: <strong><?= number_format($hottub['purchase_price'], 0, ',', ' ') ?>,- Kč</strong>
    </div>
    <div class="col-sm-3" style="margin-top: 8px; padding: 0; width: 20%;">
    <i class="entypo-right-open-mini"></i>
    Prodejní cena: <strong><?= number_format($hottub['sale_price'], 0, ',', ' ') ?>,- Kč</strong>
    </div>
    <div class="col-sm-3" style="margin-top: 8px; padding: 0; width: 20%;">
    <i class="entypo-right-open-mini"></i>
    Doprava: <strong><?= number_format($hottub['delivery_price'], 0, ',', ' ') ?>,- Kč</strong>
    </div>
    <div class="col-sm-3" style="margin-top: 8px; padding: 0; width: 20%;">
    <i class="entypo-right-open-mini"></i>
    Montáž: <strong><?= number_format($hottub['montage_price'], 0, ',', ' ') ?>,- Kč</strong>
    </div>
    <div class="col-sm-3" style="margin-top: 8px; padding: 0; width: 20%;">
    <i class="entypo-right-open-mini"></i>
    Zisk: <strong><?= number_format($hottub['sale_price']+$hottub['delivery_price']+$hottub['montage_price']-$hottub['purchase_price'], 0, ',', ' ') ?>,- Kč</strong>
    </div><?php }*/?>

 <?php

                    $orders_products_bridge = $mysqli->query("SELECT * FROM warehouse_products_bridge WHERE warehouse_id = '" . $hottub['id'] . "'");

                    if (mysqli_num_rows($orders_products_bridge) > 0) { ?>

                        <hr style="float: left;width: 100%;margin-top: 18px; margin-bottom: 3px;">

                        <div class="col-sm-11" style="margin-top: 8px; width: 100%;">
                            <i class="entypo-tools" style="font-size: 32px; float: left; margin-left: -7%;"></i>

                            <?php

                            while ($bridge = mysqli_fetch_array($orders_products_bridge)) {

                                if ($bridge['variation_id'] != 0) {

                                    ?>

                                    <a href="./zobrazit-prislusenstvi?id=<?= $bridge['product_id'] ?>" target="_blank">
                                        <div class="tile-stats tile-white" style="padding:  10px 10px 8px; float: left; margin-right: 10px;">

                                            <?php

                                            $product_query = $mysqli->query("SELECT *, s.id as ajdee FROM products p, products_variations s WHERE p.id = '" . $bridge['product_id'] . "' AND p.id = s.product_id AND s.id = '" . $bridge['variation_id'] . "'");
                                            $product = mysqli_fetch_array($product_query);

                                            $select = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $product['ajdee'] . "'") or die($mysqli->error);
                                            $desc = "";
                                            while ($var = mysqli_fetch_array($select)) {

                                                $desc = $desc . $var['name'] . ': ' . $var['value'] . ' ';

                                            }

                                            $product_title = $product['productname'] . ' – ' . $desc;
                                            ?>

                                            <span style="color: #000000; font-size: 12px; float: left;"><?= $product_title ?></span>

                                        </div>
                                    </a>
                                    <?php

                                } else {

                                    ?>
                                    <a href="./zobrazit-prislusenstvi?id=<?= $bridge['product_id'] ?>" target="_blank">
                                        <div class="tile-stats tile-white" style="padding:  10px 10px 8px; float: left; margin-right: 10px;">
                                            <?php

                                            $product_query = $mysqli->query("SELECT * FROM products WHERE id = '" . $bridge['product_id'] . "'") or die($mysqli->error);
                                            $product = mysqli_fetch_array($product_query);

                                            $product_title = $product['productname'];

                                            ?>

                                            <span style="color: #000000; font-size: 12px; float: left;"><?= $product_title ?></span>



                                        </div>
                                    </a>
                                    <?php

                                }

                            }

                            ?>
                        </div>
                        <?php

                    }

                    ?>
                    </div>

                </div>




            </div>

        </div>



        <?php


    }

} else { ?>
            <ul class="cbp_tmtimeline" style=" margin-left: 25px;">
                <li style="margin-top: 80px;">

                    <div class="cbp_tmicon">
                        <i class="entypo-block" style="line-height: 42px !important;"></i>
                    </div>

                    <div class="cbp_tmlabel empty" style="padding-top: 9px;">
                        <span><a style="font-weight: bold; margin-left: -12px;font-size: 17px;">Bohužel tomuto filtru
                                neodpovídá žádná vířivka.</a></span>
                    </div>
                </li>
            </ul>
            <?php } ?>




            <div class="row">
                <div class="col-md-12">
                    <center>
                        <ul class="pagination pagination-sm">
                            <?php

include VIEW . "/default/pagination.php"; ?>
                        </ul>
                    </center>
                </div>
            </div>
            <footer class="main">


                &copy; <?= date("Y") ?> <span style=" float:right;"><?php

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$total_time = isset($start) ? round(($finish - $start), 4) : 0;

echo 'PHP '.PHP_VERSION.' | Page generated in ' . $total_time . ' seconds.'; ?></span>

            </footer>
        </div>



    </div>



    <script type="text/javascript">
    $(document).ready(function() {
        $(".toggle-modal-remove").click(function(e) {

            $('#remove-modal').removeData('bs.modal');
            e.preventDefault();


            var type = $(this).data("type");

            var id = $(this).data("id");

            $("#remove-modal").modal({

                remote: '/admin/controllers/modals/modal-remove.php?id=' + id + '&type=' +
                    type + '&od=<?php if (isset($od)) {echo $od;} ?>',
            });
        });
    });
    </script>


    <div class="modal fade" id="remove-modal" aria-hidden="true" style="display: none; margin-top: 10%;">

    </div>

    <?php include VIEW . '/default/footer.php'; ?>

    <!-- V praze a brně se ukazuje příliš položek (33 a 32, má být 18 a 19). -->