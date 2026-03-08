<?php
// ============================================================
//  zobrazit-poptavku.php  –  refactored
//  Veškerá funkcionalita zachována, kód reorganizován:
//    1. Includes & setup
//    2. Datová vrstva (DB dotazy, příprava proměnných)
//    3. Helper funkce
//    4. HTML výstup
// ============================================================

// ---- 1. INCLUDES & SETUP -----------------------------------

include $_SERVER['DOCUMENT_ROOT'] . '/admin/config/config.php';
include INCLUDES . '/googlelogin.php';
include INCLUDES . '/functions.php';

use Granam\CzechVocative\CzechName;

$redirect_url = urlencode('pages/demands/zobrazit-poptavku?' . $_SERVER['QUERY_STRING']);
$id = $_REQUEST['id'];

// ---- 2. DATOVÁ VRSTVA --------------------------------------

$getclientquery = $mysqli->query('
    SELECT *, d.customer AS customer, d.id AS id,
        DATE_FORMAT(d.date,        "%d. %m. %Y") AS dateformated,
        DATE_FORMAT(d.realization, "%d. %m. %Y") AS realizationformated,
        DATE_FORMAT(d.realtodate,  "%d. %m. %Y") AS realtodateformat,
        d.active AS activated
    FROM demands d
    LEFT JOIN warehouse_products p ON p.connect_name = d.product
    LEFT JOIN shops_locations l    ON l.id = d.showroom
    WHERE d.id = "' . $id . '"
') or die($mysqli->error);

if (mysqli_num_rows($getclientquery) === 0) {
    include INCLUDES . '/404.php';
    exit;
}

$getclient = mysqli_fetch_assoc($getclientquery);

// Fakturační a doručovací adresa
$billing_query = $mysqli->query('
    SELECT * FROM addresses_billing b
    LEFT JOIN addresses_shipping s ON s.id = "' . $getclient['shipping_id'] . '"
    WHERE b.id = "' . $getclient['billing_id'] . '"
') or die($mysqli->error);
$billing = mysqli_fetch_assoc($billing_query);

$title     = $getclient['user_name'];
$pagetitle = $title;
$spesl     = ' - Poptávka';

// Druhý produkt (kombinovaná poptávka vířivka+sauna)
$second_product = null;
if (isset($getclient['customer']) && $getclient['customer'] == 3 && $getclient['secondproduct'] !== 'custom') {
    $spq = $mysqli->query("SELECT brand, fullname FROM warehouse_products WHERE connect_name = '" . $getclient['secondproduct'] . "'") or die($mysqli->error);
    $second_product = mysqli_fetch_array($spq);
}

// Skladová vířivka přiřazená k poptávce
$warehouseQuery = $mysqli->query("
    SELECT *, w.id AS id, DATE_FORMAT(w.loadingdate, '%d. %m. %Y') AS dateformated
    FROM warehouse w, warehouse_products p
    WHERE w.product = p.connect_name AND w.demand_id = '" . $getclient['id'] . "'
") or die($mysqli->error);

// Kontejner přiřazený k poptávce
$findContainer = $mysqli->query("
    SELECT p.id, p.container_id, w.serial_number, c.container_name, w.description, c.id_brand
    FROM containers_products p
    LEFT JOIN warehouse w  ON w.id = p.warehouse_id
    LEFT JOIN containers c ON c.id = p.container_id
    WHERE p.demand_id = '$id'
") or die($mysqli->error);

// Pobočky
$locations_query = $mysqli->query("SELECT * FROM shops_locations WHERE type = 'branch'") or die($mysqli->error);
$locationsArray  = [];
while ($location = mysqli_fetch_array($locations_query)) {
    $locationsArray[] = $location;
}

// GPS / mapa
if (isset($getclient['status']) && $getclient['status'] > 3) {
    require_once CONTROLLERS . '/lati-longi.php';
}

// Admin odkaz pro breadcrumb
$admin_link = '';
if ($client['role'] === 'salesman' || $client['role'] === 'salesman-technician') {
    $admin_link = '&admin_id=' . $client['id'];
}

// Breadcrumb – label pro aktuální status
$statusLabels = [
    1  => 'Nezpracované',
    2  => 'Zhotovené nabídky',
    3  => 'V řešení',
    4  => 'Realizace',
    5  => 'Hotové',
    6  => 'Stornované',
    7  => 'Odložené',
    8  => 'Nedokončené',
    12 => 'Prodané',
    13 => 'Dokončené',
    14 => 'Neobjednané vířivky',
    15 => 'Nová realizace',
];

$bread1  = 'Editace poptávek';
$abread1 = 'editace-poptavek?status=1' . $admin_link;
$abread2 = 'editace-poptavek?status=' . $getclient['status'] . $admin_link;
$bread2  = $statusLabels[$getclient['status']] ?? '';

// Deadline (zákazníci typu 0 nebo 1)
$deadline = null;
if (isset($getclient['customer']) && in_array($getclient['customer'], [0, 1])) {
    $deadline_query = $mysqli->query("SELECT DATE_FORMAT(deadline_date, '%d. %m. %Y') AS deadline_date FROM demands_generate_hottub WHERE id = '" . $_REQUEST['id'] . "'");
    $deadline = mysqli_fetch_array($deadline_query);
}

// Datum realizace sauny (pouze kombinovaná poptávka)
$saunadate = null;
if (isset($getclient['customer']) && $getclient['customer'] == 3) {
    $gatedate  = $mysqli->query("SELECT *, DATE_FORMAT(startdate, '%d. %m. %Y') AS startformated, DATE_FORMAT(enddate, '%d. %m. %Y') AS endformated FROM demands_double_realization WHERE demand_id = '" . $getclient['id'] . "'");
    $saunadate = mysqli_fetch_array($gatedate);
}

// Brand
$get_brand = $mysqli->query("SELECT brand FROM warehouse_products WHERE connect_name = '" . $getclient['product'] . "'") or die($mysqli->error);
$brand     = mysqli_fetch_assoc($get_brand);

// Najít produkt v kontejneru (pro tlačítka v toolbaru)
$found_product = null;
if (isset($findContainer) && mysqli_num_rows($findContainer) > 0) {
    $found_product = mysqli_fetch_array($findContainer);
    mysqli_data_seek($findContainer, 0);
}

// Dokumenty – chybějící počet (pouze pro určité statusy)
$missingDocuments = 0;
if (in_array($getclient['status'], [5, 8, 12, 4, 13])) {
    $types = [
        'Kupní smlouva'      => 'purchase_contract',
        'Předávací protokol' => 'transfer_protocol',
        'Checklist'          => 'checklist',
        'Záloha'             => 'invoice',
        'Zúčtovací faktura'  => 'clearing_invoice',
    ];
    if ($getclient['customer'] == 0) {
        $types['Revize'] = 'revision';
    }

    foreach ($types as $key => $value) {
        $missingDocuments++;
        $documentsQuery = $mysqli->query('SELECT * FROM documents_contracts WHERE client_id="' . $_REQUEST['id'] . '" AND type = "' . $value . '" ORDER BY id DESC') or die($mysqli->error);
        $documentsCount = mysqli_num_rows($documentsQuery);
        if ($documentsCount > 0) {
            $missingDocuments -= $documentsCount;
        }
        if ($value === 'invoice') {
            $check_data = $mysqli->query("SELECT invoices_number FROM demands_generate WHERE id = '$id'");
            if (mysqli_num_rows($check_data) > 0) {
                $check = mysqli_fetch_array($check_data);
                $missingDocuments += $check['invoices_number'];
            }
        }
    }
}

// Jméno zákazníka ve 2. pádu (vokativ) + oslovení
$preName = '';
$female  = false;
if ($getclient['customer'] != 3) {
    $parts     = explode(' ', $getclient['user_name']);
    $firstname = array_shift($parts);
    $lastname  = array_pop($parts);

    if (!empty($lastname)) {
        $vocativ      = new CzechName();
        $genderFirst  = $vocativ->isMale($firstname);
        $genderSecond = $vocativ->isMale($lastname);

        if ($genderFirst || $genderSecond) {
            $preName = 'pane';
        } else {
            $preName = 'paní';
            $female  = true;
        }

        $preName .= ' ' . $vocativ->vocative($lastname);
    }
}

// Rating – HTML hvězdičky
if ($getclient['rating'] == 0) {
    $ratingHtml = '-';
} else {
    $ratingHtml = '';
    $count = $getclient['rating'];
    for ($i = 0; $i < $count; $i++) {
        $ratingHtml .= '<img src="/admin/assets/images/star_2.png" style="margin-right: 4px;">';
    }
}

// Requests (follow-ups, tasks, …)
include $_SERVER['DOCUMENT_ROOT'] . '/admin/pages/demands/parts/requests.php';


// ---- 3. HELPER FUNKCE --------------------------------------

/**
 * Vrátí HTML span s datem realizace (barva dle stavu potvrzení).
 * @param string $date        Datum ve formátu d. m. Y
 * @param string $dateToStr   Datum konce (volitelné)
 * @param int    $confirmed   0 = plánována, 1 = potvrzena, 2 = v řešení
 * @param string $idPrefix    Prefix pro id atributy (wtf / wtfsauna)
 */
function realizationDateHtml(string $date, string $dateToStr, int $confirmed, string $idPrefix = 'wtf'): string
{
    $colors = [0 => '#21d1e1', 1 => '#00a651', 2 => '#FF9933'];
    $labels = [0 => 'plánována', 1 => 'potvrzená', 2 => 'v řešení'];

    $color = $colors[$confirmed] ?? '#21d1e1';
    $label = $labels[$confirmed] ?? 'plánována';

    $html = "<span style='color:{$color};'>{$label} <span id='{$idPrefix}' style='color:{$color}; font-weight: 500;'>{$date}</span>";
    if (!empty($dateToStr)) {
        $html .= " až <span id='{$idPrefix}2' style='color:{$color}; font-weight: 500;'>{$dateToStr}</span>";
    }
    $html .= '</span>';
    return $html;
}

/**
 * Vrátí HTML label pro zobrazení produktu / kombinace produktů.
 */
function productLabel(array $getclient, ?array $second_product, bool $hasWarehouse, bool $hasContainer, ?array $found_product, int $virivkaid, int $saunaid, string $name = '', string $name_title = '', bool $virivka = false, bool $sauna = false): string
{
    $customer       = (int)($getclient['customer'] ?? 0);
    $brand          = $getclient['brand'] ?? '';
    $fullname       = ucfirst($getclient['fullname'] ?? '');
    $product        = $getclient['product'] ?? '';
    $secondproduct  = $getclient['secondproduct'] ?? '';
    $baseClass      = 'class="showprodukticek" style="cursor: pointer;"';

    // Pokud má přiřazenou skladovou vířivku
    if ($hasWarehouse) {
        if ($customer != 3) {
            $link = $virivka ? '<a style="color: #00a651; cursor: pointer;"><i style="font-size: 12px;" class="entypo-check"></i>' . $name . ' ' . $name_title . '</a>' : '';
            return '<span class="showprodukticek" style="color: #00a651;">' . $link . '</span>';
        }
        // Kombinovaná poptávka
        $part1 = $virivka ? '<a style="color: #00a651; text-decoration: underline;"><i style="font-size: 12px;" class="entypo-check"></i>' . $name . ' ' . $name_title . '</a>' : ($brand . ' ' . $fullname);
        $part2 = ($secondproduct === 'custom') ? 'Sauna na míru' : ($sauna ? '<a style="color: #00a651; text-decoration: underline;"><i style="font-size: 12px;" class="entypo-check"></i>#' . $saunaid . ' ' . ($second_product['brand'] ?? '') . ' ' . ucfirst($second_product['fullname'] ?? '') . '</a>' : (($second_product['brand'] ?? '') . ' ' . ucfirst($second_product['fullname'] ?? '')));
        return '<span ' . $baseClass . '>' . $part1 . ' a ' . $part2 . '</span>';
    }

    // Pokud je v kontejneru
    if ($hasContainer && $found_product !== null) {
        $serial  = ($found_product['serial_number'] ?? '') !== '' ? $found_product['serial_number'] : null;
        $contLabel = $serial ?? 'kontejner ' . (($found_product['container_name'] ?? '') !== '' ? $found_product['container_name'] : $found_product['id_brand']);
        $contInfo  = $serial ? $serial : $contLabel . ', vířivka ' . $found_product['id'];

        if ($customer != 3) {
            $productLabel = ($product === 'custom') ? 'Sauna na míru' : ($brand . ' ' . $fullname);
            return '<span class="showprodukticek" style="color: orange; cursor: pointer;">' . $productLabel . ' - ' . $contInfo . '</span>';
        }
        $part1 = $brand . ' ' . $fullname;
        $part2 = ($secondproduct === 'custom') ? 'Sauna na míru' : (($second_product['brand'] ?? '') . ' ' . ucfirst($second_product['fullname'] ?? ''));
        return '<span ' . $baseClass . '>' . $part1 . ' a ' . $part2 . ' - ' . $contInfo . '</span>';
    }

    // Bez přiřazené položky
    if ($customer != 3) {
        $productLabel = ($product === 'custom') ? 'Sauna na míru' : ($brand . ' ' . $fullname);
        return '<span ' . $baseClass . '>' . $productLabel . '</span>';
    }
    $part2 = ($secondproduct === 'custom') ? 'Sauna na míru' : (($second_product['brand'] ?? '') . ' ' . ucfirst($second_product['fullname'] ?? ''));
    return '<span ' . $baseClass . '>' . $brand . ' ' . $fullname . ' a ' . $part2 . '</span>';
}


// ---- 4. HTML VÝSTUP ----------------------------------------

include VIEW . '/default/header.php';
?>

<script type="text/javascript">
jQuery(document).ready(function($) {

    // --- WYSIHTML5 editory ---
    var wysiOptions = {
        "font-styles": true, "emphasis": true, "lists": true,
        "html": false, "link": false, "image": true, "color": false,
        "blockquote": true, "style": {"remove": 1}
    };
    $('#zmrdsample').wysihtml5(wysiOptions);
    $('#technical_zmrdsample').wysihtml5(wysiOptions);

    // --- Změna statusu ---
    $("#newstatus").submit(function(e) {
        var status  = $(this).find('.status').val();
        var shop_id = <?= (int)$getclient['woocommerce_id'] ?>;

        if ((status == 5 || status == 13 || status == 8) && shop_id == 0) {
            $('#default-modal').removeData('bs.modal');
            e.preventDefault();
            $("#default-modal").modal({
                remote: '/admin/controllers/modals/default.php?id=<?= $getclient['id'] ?>&type=finishClient&status=' + status
            });
            return false;
        }
    });

    // --- Select2 – přidat produkt do specifikace ---
    $('#selectbox-o').select2({
        minimumInputLength: 2,
        ajax: {
            url: "/admin/data/autosuggest-custom.php",
            dataType: 'json',
            data: function(term, page) { return {q: term, site: 'wellnesstrade'}; },
            results: function(data, page) { return {results: data}; }
        }
    });

    $('#selectbox-o').on("change", function(e) {
        var vlue = $("#selectbox-o").select2("val");
        var nema = $(".select2-chosen").text();

        $('#specification_copy').clone(true).insertBefore("#duplicate_specification")
            .attr('id', 'copied').addClass('has-success').show();

        $('#copied #copy_this_first').attr('name', 'product_name[]').attr('value', nema);
        $('#copied #copy_this_third').attr('name', 'product_sku[]').attr('value', vlue);
        $('#copied #copy_this_second').attr('name', 'product_quantity[]').attr('value', '1');
        $('#copied #copy_this_price').attr('name', 'product_price[]');
        $('#copied').attr('id', 'copifinish');
        $("#selectbox-o").select2("val", "");

        setTimeout(function() {
            $('#copifinish').attr('id', 'hasfinish').removeClass('has-success');
        }, 2000);
    });

    $('.remove_specification').click(function() {
        $(this).closest('.specification').remove();
        event.preventDefault();
    });

    // --- Realizace vířivky ---
    $("#sendrealization").click(function() {
        $.ajax({
            type: "POST", url: "/admin/controllers/info_save",
            data: $("#realizationdate").serialize(),
            success: function(data) {
                var d = $.datepicker.formatDate('dd. mm. yy', new Date(data));
                $("#wtf").html(d); $("#wtf2").html(d); $("#wtfinvis").html(data);
                $("#realizationdate").hide("clip", "slow");
                $("#realizationtext").show("clip", "slow");
            }
        });
        return false;
    });

    $('#realizationtext').click(function() {
        $("#realizationtext").hide("clip", "slow");
        setTimeout(function() { $("#realizationdate").show("slow"); }, 540);
    });

    $('#cancelrealization').click(function() {
        $('#realzmrd').val($("#wtfinvis").html());
        $("#realizationdate").hide("slow");
        setTimeout(function() { $("#realizationtext").show("clip", "slow"); }, 540);
    });

    // --- Realizace sauny ---
    $("#sendrealizationsauna").click(function() {
        $.ajax({
            type: "POST", url: "/admin/controllers/info_save",
            data: $("#realizationdatesauna").serialize(),
            success: function(data) {
                var d = $.datepicker.formatDate('dd. mm. yy', new Date(data));
                $("#wtfsauna").html(d); $("#wtf2sauna").html(d); $("#wtfinvissauna").html(data);
                $("#realizationdatesauna").hide("clip", "slow");
                $("#realizationtextsauna").show("clip", "slow");
            }
        });
        return false;
    });

    $('#realizationtextsauna').click(function() {
        $("#realizationtextsauna").hide("clip", "slow");
        setTimeout(function() { $("#realizationdatesauna").show("slow"); }, 540);
    });

    $('#cancelrealizationsauna').click(function() {
        $('#realzmrdsauna').val($("#wtfinvissauna").html());
        $("#realizationdatesauna").hide("slow");
        setTimeout(function() { $("#realizationtextsauna").show("clip", "slow"); }, 540);
    });

    // --- Popisek prodejců ---
    $("#sendform").click(function() {
        $.ajax({
            type: "POST", url: "/admin/controllers/info_save",
            data: $("#editdescription").serialize(),
            success: function(data) {
                $(".descriptiontext").html(data);
                $("#editdescription").hide("slow");
                $(".descriptiontext").show("slow");
            }
        });
        return false;
    });

    // --- Technický popisek ---
    $("#send_technical").click(function() {
        $.ajax({
            type: "POST", url: "/admin/controllers/technical_info_save",
            data: $("#technical_edit_description").serialize(),
            success: function(data) {
                $(".technical_description_text").html(data);
                $("#technical_edit_description").hide("slow");
                $(".technical_description_text").show("slow");
            }
        });
        return false;
    });

    // --- Toggle statusu ---
    $('#changestatus').click(function() {
        $("#changestatus").hide("clip", "slow");
        setTimeout(function() { $("#newstatus").show("slow"); }, 540);
    });
    $('#cancelchangestatus').click(function() {
        $("#newstatus").hide("slow");
        setTimeout(function() { $("#changestatus").show("clip", "slow"); }, 540);
    });

    // --- Editace popisku (klik na text) ---
    $('.descriptiontext').click(function() {
        $(".descriptiontext").hide("slow");
        $("#editdescription").show("slow");
    });
    $('#canceledit').click(function() {
        $("#editdescription").hide("slow");
        $(".descriptiontext").show("slow");
    });

    $('.technical_description_text').click(function() {
        $(".technical_description_text").hide("slow");
        $("#technical_edit_description").show("slow");
    });
    $('#technical_cancel_edit').click(function() {
        $("#technical_edit_description").hide("slow");
        $(".technical_description_text").show("slow");
    });

    // --- Specifikace ---
    $('#addspecification').click(function() {
        $("#addspecification").hide("clip", "slow");
        setTimeout(function() { $("#specificationform").show("slow"); }, 540);
    });
    $('#cancelspecification').click(function() {
        $("#specificationform").hide("slow");
        setTimeout(function() { $("#addspecification").show("clip", "slow"); }, 540);
    });

    // --- Úkoly ---
    $('#addtask').click(function() { $("#addtask").hide("slow"); $("#taskform").show("slow"); });
    $('#canceladdtask').click(function() { $("#taskform").hide("slow"); $("#addtask").show("slow"); });

    // --- Produkty toggle ---
    $('#show_products').click(function() { $("#products").toggle("slow"); });

    // --- Smlouva ---
    $('#add_contract').click(function() {
        $("#add_contract").hide("slow");
        setTimeout(function() { $("#contract").show("slow"); }, 540);
    });
    $('#cancel_contract').click(function() {
        $("#contract").hide("slow");
        setTimeout(function() { $("#add_contract").show("slow"); }, 540);
    });

    // --- Mapa ---
    $('#adress').click(function() {
        if ($("#sample-checkin").is(":visible")) {
            $("#sample-checkin").hide("slow");
        } else {
            $("#sample-checkin").show("slow");
            setTimeout(function() { showmap(); }, 540);
        }
    });

    // --- Produkt detail ---
    $('.showprodukticek').click(function() {
        if ($("#produkticek").is(":visible")) {
            $("#produkticek").hide("slow");
        } else {
            $("#produkticek").show("slow");
        }
    });

    // --- Duplikovat příjemce ---
    $('#duplicatereciever').click(function() {
        $('#reciever').clone().insertAfter("#reciever");
    });

    // --- Poznámky ---
    $('.remove-note').click(function(e) {
        e.preventDefault();
        var id = $(this).data("id");
        $("#story-" + id).hide("slow");
        $.get("/admin/controllers/demands-timeline?type=remove&id=" + id);
    });

    $('.kill-note').click(function(e) {
        e.preventDefault();
        var id = $(this).data("id");
        if ($("#killed-" + id).css('text-decoration') == 'line-through solid rgb(148, 148, 148)') {
            $("#killed-" + id).css("text-decoration", "none");
            $.get("/admin/controllers/demands-timeline?type=line&id=" + id + "&turn=off");
        } else {
            $("#killed-" + id).css("text-decoration", "line-through");
            $.get("/admin/controllers/demands-timeline?type=line&id=" + id + "&turn=on");
        }
    });

    $('.bold-note').click(function(e) {
        e.preventDefault();
        var id = $(this).data("id");
        if ($("#killed-" + id).css('font-weight') == '700' && $("#story-" + id).css("font-weight") != '700') {
            $("#killed-" + id).css("font-weight", "normal");
            $.get("/admin/controllers/demands-timeline?type=bold&id=" + id + "&turn=off");
        } else {
            $("#killed-" + id).css("font-weight", "bold");
            $.get("/admin/controllers/demands-timeline?type=bold&id=" + id + "&turn=on");
        }
    });

    $('.star-note').click(function(e) {
        e.preventDefault();
        var id = $(this).data("id");
        if ($("#story-" + id).hasClass('starred')) {
            $(".star").show();
            $("#story-" + id).removeClass('starred')
                .css({"font-weight": "normal", "border-radius": "0px"})
                .animate({"background-color": "#fff", "padding": "0", "paddingBottom": "0"});
            $("#killed-" + id).css("font-weight", "normal").animate({"color": "#949494"});
            $.get("/admin/controllers/demands-timeline?type=star&id=" + id + "&turn=off");
        } else {
            $(".star").hide();
            $("#story-" + id).find(".star").show();
            $("#story-" + id).animate({"background-color": "#ebf1f6", "padding": "30", "paddingBottom": "12"})
                .addClass('starred').css({"font-weight": "bold", "border-radius": "3px"});
            $("#killed-" + id).css("font-weight", "bold").animate({"color": "#303641"});
            $.get("/admin/controllers/demands-timeline?type=star&id=" + id + "&turn=on");
        }
    });

    // --- Obrázky ---
    $('.remove-picture').click(function() {
        $(this).parent(".single-picture").fadeOut();
        var picture = $(this).data("picture");
        $.get("./zobrazit-poptavku?id=<?= $id ?>&action=remove_picture_realization&picture=" + picture);
    });

    $('.remove-picture-technical').click(function() {
        $(this).parent(".single-picture").fadeOut();
        var picture = $(this).data("picture");
        $.get("./zobrazit-poptavku?id=<?= $id ?>&action=remove_picture_technical&picture=" + picture);
    });

    // --- Záložky (tabs) pro hotové poptávky ---
    $('.nav-tabs li').click(function() {
        var id = this.id;
        $('.nav-tabs li').removeClass('active');
        $(this).addClass('active');
        $('.tab').hide('slow');
        $('#' + id + '-tab').show('slow');
    });

    // --- Lightgallery ---
    $('.lightgallery').lightGallery({selector: 'a.full'});

});
</script>

<?php if ($access_edit): ?>
<!-- ===== RATING ===== -->
<div class="col-sm-3" style="width: 25%; text-align: center; left: 280px; float: left; margin-top: -50px;">
    <?= $ratingHtml ?>
</div>

<!-- ===== TOOLBAR – AKČNÍ TLAČÍTKA ===== -->
<div class="col-sm-5" style="width: 50%; float: right; margin-top: -53px;">
    <div class="profile-buttons" style="float: right; margin-right: 4px;">

        <!-- SMS -->
        <a href="javascript:;" onclick="jQuery('#demand_sms').modal('show');" class="btn btn-default"
           style="margin-left: 11px; margin-right: 8px;" data-toggle="tooltip" data-placement="top"
           data-original-title="Odeslat nabídkovou SMS">
            <i class="fas fa-sms"></i>
        </a>
        <span style="border-right: 1px solid #cccccc;"></span>

        <?php if (!in_array($getclient['status'], [5, 8, 13])): ?>
            <?php if (!isset($findContainer) || mysqli_num_rows($findContainer) != 1): ?>
                <!-- Přidat do kontejneru -->
                <a href="javascript:;" onclick="jQuery('#container_modal').modal('show');" class="btn btn-default"
                   style="margin-left: 11px; margin-right: 8px;" data-toggle="tooltip" data-placement="top"
                   data-original-title="Přidat do kontejneru">
                    <i class="fas fa-dolly-flatbed"></i>
                </a>
            <?php elseif ($found_product !== null): ?>
                <!-- Odkaz na existující kontejner -->
                <a href="/admin/pages/warehouse/editace-kontejneru?brand=<?= $brand['brand'] ?>"
                   class="btn btn-green" style="margin-left: 11px; margin-right: 8px;" data-toggle="tooltip"
                   data-placement="top"
                   data-original-title="Položka #<?= $found_product['id'] ?> v kontejneru #<?= $found_product['id_brand'] ?>">
                    <i class="fas fa-dolly-flatbed"></i>
                </a>
            <?php endif; ?>
            <span style="border-right: 1px solid #cccccc;"></span>
        <?php endif; ?>

        <?php if (in_array($getclient['status'], [5, 13, 8])): ?>
            <?php if ($getclient['woocommerce_id'] == 0): ?>
                <a data-id="<?= $getclient['id'] ?>" style="margin-right: 8px;" data-type="createUser"
                   class="toggle-default-modal btn btn-default" data-toggle="tooltip" data-placement="top"
                   data-original-title="Vytvořit uživatele">
                    <i class="entypo-user"></i>
                </a>
                <span style="border-right: 1px solid #cccccc;"></span>
            <?php else: ?>
                <a data-id="<?= $getclient['id'] ?>" style="margin-right: 8px;" data-type="resetUserPassword"
                   class="toggle-default-modal btn btn-success" data-toggle="tooltip" data-placement="top"
                   data-original-title="Uživatel byl již vytvořen pod ID <?= $getclient['woocommerce_id'] ?>. Kliknutím můžete znovu poslat úvodní přihlašovací údaje.">
                    <i class="entypo-user"></i>
                </a>
                <span style="border-right: 1px solid #cccccc;"></span>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Servis, objednávka, dokument, editace -->
        <a href="/admin/pages/services/pridat-servis?service=new&client=<?= $getclient['id'] ?>"
           style="margin-left: 11px;" class="btn btn-default" data-toggle="tooltip" data-placement="top"
           data-original-title="Přidat servis"><i class="entypo-tools"></i></a>

        <a href="/admin/pages/orders/vytvorit-objednavku?order=new&client=<?= $getclient['id'] ?>"
           class="btn btn-default" data-toggle="tooltip" data-placement="top"
           data-original-title="Vytvořit objednávku"><i class="entypo-basket"></i></a>

        <a href="/admin/pages/documents/pridat-smlouvu?id=<?= $getclient['id'] ?>"
           style="margin-right: 8px;" class="btn btn-default" data-toggle="tooltip" data-placement="top"
           data-original-title="Přidat dokument"><i class="entypo-book"></i></a>

        <span style="border-right: 1px solid #cccccc;"></span>

        <a href="./upravit-poptavku?id=<?= $getclient['id'] ?>&type=<?= $getclient['status'] ?>"
           class="btn btn-default" style="margin-left: 11px; margin-right: 8px;" data-toggle="tooltip"
           data-placement="top" data-original-title="Upravit poptávku"><i class="entypo-pencil"></i></a>

        <?php if (!in_array($getclient['status'], [5, 4, 8, 13])): ?>
            <span style="border-right: 1px solid #cccccc;"></span>
            <?php if ($getclient['status'] == 6): ?>
                <a href="./zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=restore"
                   class="btn btn-red" style="margin-left: 11px;" data-toggle="tooltip" data-placement="top"
                   data-original-title="Zrušit stornování poptávky"><i class="entypo-cancel-circled"></i></a>
            <?php else: ?>
                <a href="./zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=cancel"
                   class="btn btn-default" style="margin-left: 11px;" data-toggle="tooltip" data-placement="top"
                   data-original-title="Stornovat poptávku"><i class="entypo-cancel-circled"></i></a>
            <?php endif; ?>
            <a data-type="demand" data-id="<?= $getclient['id'] ?>" class="btn btn-default toggle-modal-remove"
               data-toggle="tooltip" data-placement="top" data-original-title="Odstranit poptávku">
                <i class="entypo-trash"></i>
            </a>
        <?php endif; ?>

    </div>
</div>
<?php endif; ?>


<!-- ===== PRODUKT DETAIL (skrytý panel) ===== -->
<div id="produkticek" style="margin-bottom: 66px; display: none;">
    <?php
    $demand_id = $getclient['id'];
    $virivkaid = 0;
    $saunaid   = 0;

    if (mysqli_num_rows($warehouseQuery) > 0) {
        include $_SERVER['DOCUMENT_ROOT'] . '/admin/controllers/views/product-view.php';
    } elseif (isset($findContainer) && mysqli_num_rows($findContainer) == 1) {
        include $_SERVER['DOCUMENT_ROOT'] . '/admin/controllers/views/product-view-containers.php';
    }
    ?>
    <div style="text-align: center; margin-top: 20px;">
        <?php
        if (isset($warehouseQuery) && mysqli_num_rows($warehouseQuery) == 1) {
            mysqli_data_seek($warehouseQuery, 0);
            $fp = mysqli_fetch_array($warehouseQuery);
        ?>
            <a href="../warehouse/upravit-virivku?id=<?= $fp['id'] ?>>&redirect=<?= $getclient['id'] ?>>"
               style="margin-bottom: 6px" class="btn btn-primary btn-lg btn-icon icon-left">
                <i class="entypo-pencil"></i> Upravit hodnoty na skladě
            </a>
        <?php }

        if (isset($findContainer) && mysqli_num_rows($findContainer) == 1) {
            mysqli_data_seek($findContainer, 0);
            $fp = mysqli_fetch_array($findContainer);
        ?>
            <a data-id="<?= $fp['id'] ?>" style="margin-bottom: 6px"
               class="toggle-modal-edit btn btn-orange btn-lg btn-icon icon-left">
                <i class="entypo-pencil"></i> Upravit hodnoty v kontejneru
            </a>
        <?php } ?>
    </div>
</div>


<!-- ===== MAPA ===== -->
<div id="sample-checkin" class="map-checkin"
     style="background: #f0f0f0; height: 400px !important; max-height: 400px !important; overflow: hidden; width: 100%; display: none; margin-bottom: 40px;">
</div>


<!-- ===== PROFIL – HEADER ===== -->
<div class="profile-env" style="float: left; width: 100%;">

    <header class="row"
            style="<?= ($getclient['customer'] == 3) ? 'min-height: 172px;' : 'min-height: 115px;' ?> margin-top: 0;">

        <?php if ($getclient['customer'] != 3): ?>
            <!-- Jeden produkt -->
            <div class="col-sm-2" style="width: 10%;">
                <a class="profile-picture">
                    <img src="https://www.wellnesstrade.cz/admin/data/images/customer/<?= $getclient['product'] ?>.png"
                         class="img-responsive img-circle" />
                </a>
            </div>

        <?php else: ?>
            <!-- Kombinovaná poptávka (vířivka + sauna) -->
            <div class="col-sm-6" style="width: 70%; margin-top: -24px;">
                <div class="col-sm-2" style="width: 180px; padding-right: 0;">
                    <a class="profile-picture">
                        <img src="https://www.wellnesstrade.cz/admin/data/images/customer/<?= $getclient['product'] ?>.png"
                             class="img-responsive img-circle" style="width: 47%; margin-right: 10px; float: left;" />
                    </a>
                    <a class="profile-picture">
                        <img src="https://www.wellnesstrade.cz/admin/data/images/customer/<?= $getclient['secondproduct'] ?>.png"
                             class="img-responsive img-circle" style="width: 46%; float: right;" />
                    </a>
                </div>

                <div class="col-sm-5" style="width: 40%; padding-right: 0;">
                    <ul class="profile-info-sections">
                        <li style="padding-right: 26px;">
                            <div class="profile-name">
                                <strong>
                                    <a style="font-weight: 500;">
                                        <?php if ($billing['billing_degree'] != '') echo $billing['billing_degree'] . ' '; ?>
                                        <?= $pagetitle ?>
                                    </a>
                                </strong>
                                <?= productLabel($getclient, $second_product, mysqli_num_rows($warehouseQuery) > 0, isset($findContainer) && mysqli_num_rows($findContainer) > 0, $found_product, $virivkaid ?? 0, $saunaid ?? 0, $name ?? '', $name_title ?? '', $virivka ?? false, $sauna ?? false) ?>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>


        <!-- Hlavní info sloupec -->
        <div class="col-sm-5"
             style="<?= ($getclient['customer'] != 3) ? 'width: 88%;' : 'width: 100%;' ?> padding-right: 0;">
            <ul class="profile-info-sections">

                <?php if ($getclient['customer'] != 3): ?>
                    <!-- Jméno + vokativ -->
                    <li style="padding-right: 26px;">
                        <div class="profile-name">
                            <?= $preName ?>
                            <strong>
                                <a style="font-weight: 500;">
                                    <?php if ($billing['billing_degree'] != '') echo $billing['billing_degree'] . ' '; ?>
                                    <?= $pagetitle ?>
                                </a>
                            </strong>
                            <?php
                            if (isset($warehouse) && mysqli_num_rows($warehouseQuery) > 0) {
                                if ($warehouse['reserved'] == 1) {
                                    echo '<span style="color: #cc2423; text-decoration: none; margin: 4px 0 2px; font-size: 12px;">Rezervace do ' . date('d. m. Y', strtotime($warehouse['reserved_date'])) . '</span>';
                                }
                            }
                            ?>
                            <?= productLabel($getclient, $second_product, mysqli_num_rows($warehouseQuery) > 0, isset($findContainer) && mysqli_num_rows($findContainer) > 0, $found_product, $virivkaid ?? 0, $saunaid ?? 0, $name ?? '', $name_title ?? '', $virivka ?? false, $sauna ?? false) ?>
                        </div>
                    </li>
                <?php endif; ?>

                <!-- Showroom -->
                <li style="padding: 0 26px;">
                    <div class="profile-stat">
                        <h3 style="margin-left: -7px;">
                            <i style="margin-right: 4px;" class="entypo-briefcase"></i>
                            <?php if (!empty($getclient['name'])) {
                                echo 'Showroom <strong>' . $getclient['name'] . '</strong>';
                            } else {
                                echo 'Neznámý showroom';
                            } ?>
                        </h3>
                        <span>
                            <?php if ($getclient['admin_id'] != 0) {
                                $fadq = $mysqli->query("SELECT user_name FROM demands WHERE id = '" . $getclient['admin_id'] . "'");
                                $fad  = mysqli_fetch_array($fadq);
                                echo '<strong>' . $fad['user_name'] . '</strong> se stará o poptávku.';
                            } else {
                                echo 'O poptávku se nikdo nestará.';
                            } ?>
                        </span>
                    </div>
                </li>

                <!-- Adresa -->
                <li id="adress" style="cursor: pointer; padding: 0 26px;">
                    <div class="profile-stat">
                        <h3 style="margin-left: -7px;"><i class="entypo-location"></i>Adresa</h3>
                        <span><?php address($billing); ?></span>
                    </div>
                </li>

                <!-- Realizace vířivky -->
                <li style="cursor: pointer; padding: 0 0 0 26px; max-height: 58px;">
                    <div class="profile-stat">
                        <a href="javascript:;" onclick="jQuery('#new-realization-modal').modal('show');">
                            <h3 style="margin-left: -1px; margin-bottom: 4px;">
                                <i style="font-size: 16px; margin-right: 5px;" class="fa fa-check"></i>
                                Realizace
                                <span style="color: #0072bb;">
                                    <?php
                                    if ($getclient['area'] === 'prague') echo 'PR';
                                    elseif ($getclient['area'] === 'brno') echo 'BR';
                                    else echo '<i class="entypo-block"></i>';
                                    ?>
                                </span>
                                <?php if ($getclient['customer'] == 3): ?>Vířivky<?php endif; ?>
                            </h3>
                            <?php if (isset($getclient['realization']) && $getclient['realization'] !== '0000-00-00') {
                                $toStr = ($getclient['realtodate'] !== '0000-00-00') ? $getclient['realtodateformat'] : '';
                                echo realizationDateHtml($getclient['realizationformated'], $toStr, (int)$getclient['confirmed'], 'wtf');
                            } else {
                                echo '<span>Den realizace nebyl stanoven.</span>';
                            } ?>
                            <span id="wtfinvis" style="display:none;"><?= $getclient['realization'] ?></span>
                        </a>
                    </div>
                </li>

                <!-- Deadline (typ 0 nebo 1) -->
                <?php if (isset($deadline['deadline_date'])): ?>
                    <li style="cursor: pointer; padding: 0 0 0 16px; max-height: 58px; margin-left: 10px;">
                        <div class="profile-stat">
                            <a href="javascript:;" onclick="jQuery('#new-realization-modal').modal('show');">
                                <h3 style="margin-left: -1px; margin-bottom: 4px;">
                                    <i style="font-size: 16px; margin-right: 5px;" class="entypo-cancel-circled"></i>
                                    Deadline
                                </h3>
                                <span style="color: #000;">
                                    <i class="entypo-right-open-mini"></i>
                                    <span style="font-weight: 500;"><?= $deadline['deadline_date'] ?></span>
                                </span>
                            </a>
                        </div>
                    </li>
                <?php endif; ?>

                <!-- Realizace sauny (pouze kombinovaná poptávka) -->
                <?php if ($getclient['customer'] == 3 && $saunadate !== null): ?>
                    <li style="cursor: pointer; margin-left: 25px; padding: 0 0 0 26px; max-height: 58px;">
                        <div class="profile-stat">
                            <a href="javascript:;" onclick="jQuery('#new-realization-modal-sauna').modal('show');">
                                <h3 style="margin-left: -1px; margin-bottom: 4px;">
                                    <i style="font-size: 16px; margin-right: 5px;" class="fa fa-check"></i>
                                    Realizace Sauny
                                </h3>
                                <?php if (isset($saunadate['startdate']) && $saunadate['startdate'] !== '0000-00-00') {
                                    $toStr = ($saunadate['enddate'] !== '0000-00-00') ? $saunadate['endformated'] : '';
                                    echo realizationDateHtml($saunadate['startformated'], $toStr, (int)$saunadate['confirmed'], 'wtfsauna');
                                } else {
                                    echo '<span>Den realizace nebyl stanoven.</span>';
                                } ?>
                                <span id="wtfinvissauna" style="display:none;">
                                    <?= (isset($saunadate['startdate']) && $saunadate['startdate'] !== '0000-00-00') ? $saunadate['startdate'] : '' ?>
                                </span>
                            </a>
                        </div>
                    </li>
                <?php endif; ?>

            </ul>
        </div>

        <!-- Ikona produktu (u kombinované poptávky vpravo) -->
        <div class="" <?php if ($getclient['customer'] == 3) { ?>style="width: 4%; height: 20%; margin-left: 130px; float: left;"<?php } else { ?>style="display: none;"<?php } ?>>
            <?php if ($getclient['customer'] == 3): ?>
                <img src="https://www.wellnesstrade.cz/admin/data/images/customer/<?= $getclient['product'] ?>.png"
                     class="img-responsive img-circle"
                     style="width: 42px; float: right; margin-right: -17px;" />
            <?php endif; ?>
        </div>

    </header>


    <!-- ===== STATUS + KONTAKTNÍ INFORMACE ===== -->
    <section class="profile-info-tabs">
        <div class="row">
            <!-- Status badge / formulář pro změnu -->
            <div class="col-sm-2" style="text-align: center; width: 14%; padding-top: 10px; padding-right: 6px; height: 50px;">
                <span <?php if ($access_edit) echo 'id="changestatus"'; ?>
                      style="width: 100px; cursor: pointer; font-size: 13px; font-weight: 500; color: #404a5b;">
                    <i class="entypo-flag" style="padding-right: 2px;"></i>
                    <?php foreach ($demand_statuses as $status) {
                        if ($status['id'] == $getclient['status']) echo $status['name'];
                    } ?>
                </span>

                <?php if ($access_edit): ?>
                    <form id="newstatus" style="display: none;" role="form" method="post"
                          action="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=changestatus"
                          enctype="multipart/form-data">
                        <select class="form-control status" name="status" style="width: 60%; float: left;">
                            <?php foreach ($demand_statuses as $status): ?>
                                <option value="<?= $status['id'] ?>"
                                    <?php if ($status['id'] == $getclient['status']) echo 'selected'; ?>>
                                    <?= $status['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" style="float: left; margin-left: 5px; margin-top: 5px;" class="btn btn-green">
                            <i class="entypo-pencil"></i>
                        </button>
                        <a id="cancelchangestatus" style="float: left; margin-left: 4px; margin-top: 5px;">
                            <button type="button" class="btn btn-white"><i class="entypo-cancel"></i></button>
                        </a>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Email, telefon, vzdálenost, datum -->
            <div class="col-sm-10" style="width: 85.33333333%;">
                <div class="col-sm-3" style="min-width: 232px;">
                    <ul class="user-details">
                        <li>
                            <i class="entypo-mail" style="margin-right: 5px;"></i>
                            <?php if ($billing['billing_email'] != ''): ?>
                                <strong><a href="mailto:<?= $billing['billing_email'] ?>"><?= $billing['billing_email'] ?></a></strong>
                            <?php else: ?>
                                žádný email
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>

                <div class="col-sm-3">
                    <ul class="user-details">
                        <li>
                            <i class="entypo-phone"></i>
                            <?php if ($billing['billing_phone'] != '' && $billing['billing_phone'] != 0):
                                echo phone_prefix($billing['billing_phone_prefix']); ?>
                                <strong><?= number_format($billing['billing_phone'], 0, ',', ' ') ?></strong>
                            <?php else: ?>
                                žádný telefon
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>

                <div class="col-sm-3">
                    <ul class="user-details">
                        <li>
                            <i class="fa fa-tachometer" style="margin-right: 5px;"></i>
                            <?php if ($getclient['distance'] != '' && $getclient['distance'] != '0'): ?>
                                Vzdálenost <strong><?= $getclient['distance'] ?> km</strong>
                            <?php elseif (!empty($getclient['area'])): ?>
                                <?php
                                $location_address = (isset($billing['shipping_street']) && $billing['shipping_street'] != '' && isset($billing['shipping_city']) && $billing['shipping_city'] != '')
                                    ? ($billing['shipping_city'] . ' ' . $billing['shipping_zipcode'] . ' ' . $billing['shipping_street'] . ' ' . $billing['shipping_country'])
                                    : ($billing['billing_city'] . ' ' . $billing['billing_zipcode'] . ' ' . $billing['billing_street'] . ' ' . $billing['billing_country']);
                                ?>
                                <script type="text/javascript" src="//maps.google.com/maps/api/js?key=AIzaSyDRermPdr7opDFLqmrcOuK5L4zC2_U8XGk&sensor=false"></script>
                                <script type="text/javascript">
                                    var address = '<?= $location_address ?>>';
                                    var API_KEY = 'AIzaSyDWsYJWdJpuS_SgJ_0bpi0uOOGAGPBWsgk';
                                    $.ajax({
                                        type: "GET",
                                        url: "//maps.googleapis.com/maps/api/geocode/json?address=" + address + "&sensor=false&key=" + API_KEY,
                                        dataType: "json",
                                        success: function(data) {
                                            var lati = data.results[0].geometry.location.lat;
                                            var longi = data.results[0].geometry.location.lng;
                                            var directionsService = new google.maps.DirectionsService();
                                            <?php if ($getclient['area'] === 'prague'): ?>
                                            var start = '50.010540, 14.469440';
                                            <?php elseif ($getclient['area'] === 'brno'): ?>
                                            var start = '49.020630, 17.128210';
                                            <?php endif; ?>
                                            var request = {origin: start, destination: lati + ',' + longi, travelMode: google.maps.TravelMode.DRIVING};
                                            directionsService.route(request, function(response, status) {
                                                var km = (response.routes[0].legs[0].distance.value / 1000).toFixed(0);
                                                $("#distance").html(km);
                                            });
                                        }
                                    });
                                </script>
                                <?= 'vypočt. vzdálenost: <strong id="distance"></strong> km' ?>
                            <?php else: ?>
                                vzdálenost nedostupná
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>

                <div class="col-sm-3">
                    <ul class="user-details">
                        <li>
                            <i class="entypo-calendar"></i>
                            Dne <strong><?= $getclient['dateformated'] ?></strong>
                            <?php if ($getclient['creator'] > 0) {
                                $fadq = $mysqli->query("SELECT user_name FROM demands WHERE id = '" . $getclient['creator'] . "'");
                                $fad  = mysqli_fetch_array($fadq);
                                echo 'přidal <strong>' . $fad['user_name'] . '</strong>';
                            } ?>
                        </li>
                    </ul>
                </div>

                <hr style="width: 100%; border-top: 1px solid #ffffff; margin-top: 34px;">
            </div>


            <!-- Specifikace produktu -->
            <div class="" <?php if ($getclient['customer'] == 3) echo 'style="width: 4%; height: 20%; margin-left: 200px; margin-left: 130px; float: left;"'; else echo 'style="display: none;"'; ?>>
                <?php if ($getclient['customer'] == 3): ?>
                    <img src="https://www.wellnesstrade.cz/admin/data/images/customer/<?= $getclient['product'] ?>.png"
                         class="img-responsive img-circle"
                         style="width: 42px; float: right; margin-right: -17px;" />
                <?php endif; ?>
            </div>

            <div class="col-sm-12" style="margin-top: 10px; float: left;">
                <?php
                $searchquery = $mysqli->query("SELECT *, w.id AS id, DATE_FORMAT(w.loadingdate, '%d. %m. %Y') AS dateformated FROM warehouse w, warehouse_products p WHERE w.product = p.connect_name AND w.demand_id = '$demand_id'") or die($mysqli->error);

                if (mysqli_num_rows($searchquery) > 0) {
                    while ($warehouse = mysqli_fetch_array($searchquery)) {
                        // Nutné změny (pouze pro typ 1 = hot tub)
                        if (isset($warehouse['customer']) && $warehouse['customer'] == 1) {
                            $get_provedeni = $mysqli->query("SELECT value FROM warehouse_specs_bridge WHERE client_id = '" . $warehouse['id'] . "' AND specs_id = 5") or die($mysqli->error);
                            $provedeni = mysqli_fetch_array($get_provedeni);

                            $get_ids = $mysqli->query("SELECT w.id AS id, w.name AS name FROM warehouse_products_types w, warehouse_products p WHERE w.warehouse_product_id = p.id AND p.fullname = '" . $warehouse['fullname'] . "' AND w.name = '" . $provedeni['value'] . "'") or die($mysqli->error);
                            $get_id  = mysqli_fetch_array($get_ids);

                            $specsquery = $mysqli->query("SELECT s.id, s.name, s.demand_category, s.technical FROM specs s INNER JOIN warehouse_products_types_specs wh ON wh.spec_id = s.id AND wh.type_id = '" . $get_id['id'] . "' AND s.technical = 1 AND s.warehouse_spec = 1 GROUP BY s.id ORDER BY s.demand_category ASC, s.name ASC") or die($mysqli->error);

                            $necessary_changes = '';
                            while ($specs = mysqli_fetch_array($specsquery)) {
                                $demandSpecQuery   = $mysqli->query("SELECT * FROM demands_specs_bridge WHERE specs_id = '" . $specs['id'] . "' AND client_id = '" . $demand_id . "'") or die($mysqli->error);
                                $demandSpec        = mysqli_fetch_assoc($demandSpecQuery);
                                $warehouseSpecQuery = $mysqli->query("SELECT * FROM warehouse_specs_bridge WHERE specs_id = '" . $specs['id'] . "' AND client_id = '" . $warehouse['id'] . "'") or die($mysqli->error);
                                $warehouseSpec      = mysqli_fetch_assoc($warehouseSpecQuery);

                                $diff = ((!isset($demandSpec['value']) || !isset($warehouseSpec)) || $demandSpec['value'] != $warehouseSpec['value'])
                                     && !((!isset($demandSpec['value']) || !isset($warehouseSpec)) || ($demandSpec['value'] == 'Ne' && $warehouseSpec['value'] == ''))
                                     && !($demandSpec['value'] == '' && $warehouseSpec['value'] == 'Ne');

                                if ($diff) {
                                    $product_status = '';
                                    $get_products = $mysqli->query("SELECT *, d.type AS stock_type, d.id AS dem_id FROM demands_products d, products p, products_stocks s WHERE d.type = '" . $getclient['product'] . "' AND d.spec_id = '" . $specs['id'] . "' AND d.product_id = p.id AND s.product_id = d.product_id AND s.variation_id = d.variation_id AND s.location_id = '2'") or die($mysqli->error);

                                    if (mysqli_num_rows($get_products) > 0) {
                                        while ($product = mysqli_fetch_array($get_products)) {
                                            $single_query = $mysqli->query("SELECT b.*, l.name FROM demands_products_bridge b LEFT JOIN shops_locations l ON b.location_id = l.id WHERE b.demand_id = '" . $getclient['id'] . "' AND b.spec_id = '" . $specs['id'] . "' AND b.product_id = '" . $product['product_id'] . "' AND b.variation_id = '" . $product['variation_id'] . "'") or die($mysqli->error);
                                            $single = mysqli_fetch_array($single_query);

                                            if (!empty($single)) {
                                                if ($single['type'] === 'warehouse') {
                                                    $product_status = '<a href="/admin/pages/accessories/zobrazit-prislusenstvi?id=' . $product['product_id'] . '" target="_blank" style="color: #00a651; font-weight: bold;">- Rezervováno v ' . $single['name'] . '</a>';
                                                } elseif ($single['type'] === 'missing') {
                                                    $product_status = '<a href="/admin/pages/accessories/zobrazit-prislusenstvi?id=' . $product['product_id'] . '" target="_blank" style="color: #d42020; font-weight: bold;">- Chybějící v ' . $single['name'] . '</a>';
                                                } elseif ($single['type'] === 'supply') {
                                                    $supply_query = $mysqli->query("SELECT *, DATE_FORMAT(date, '%d. %M %Y') AS recieved_date FROM products_supply WHERE id = '" . $single['type_id'] . "'") or die($mysqli->error);
                                                    $supply = mysqli_fetch_array($supply_query);
                                                    $product_status = '<a href="/admin/pages/accessories/zobrazit-dodavku?id=' . $supply['id'] . '" target="_blank" style="color: #ff5722; font-weight: bold;">- Dodávka #' . $supply['id'] . ' - doručení ' . $supply['recieved_date'] . '</a>';
                                                } elseif ($single['type'] === 'hottub') {
                                                    $hottub_query = $mysqli->query("SELECT w.*, l.name FROM warehouse w LEFT JOIN shops_locations l ON l.id = w.location_id WHERE w.id = '" . $single['type_id'] . "'") or die($mysqli->error);
                                                    $hottub = mysqli_fetch_array($hottub_query);
                                                    $product_status = '<a href="/admin/pages/warehouse/zobrazit-virivku?id=' . $hottub['id'] . '" target="_blank" style="color: #ff5722; font-weight: bold;">- Vířivka #' . $hottub['serial_number'] . ' - ' . $hottub['name'] . '</a>';
                                                }
                                            }
                                        }
                                    }

                                    $paid = '';
                                    if ($warehouseSpec['paid']) {
                                        $paid_text = $warehouseSpec['paid_text'] ?: 'bez dodatečných informací';
                                        $paid = '<i class="fas fa-asterisk" style="margin-left: 20px; line-height: 15px; color: #d42020;" data-toggle="tooltip" data-placement="top" data-original-title="' . $paid_text . '"></i>';
                                    }

                                    $necessary_changes .= '
                                    <div class="col-sm-12" style="margin-bottom: 6px; padding: 0; color: #000; padding-left: 22px; text-indent: -15px;">
                                        <i class="entypo-right-open-mini" style="margin-right: -7px;"></i>
                                        <strong>' . $specs['name'] . '</strong>: změnit na
                                        <strong>' . mb_strtoupper($demandSpec['value']) . '</strong> ' . $paid . ' ' . $product_status . '
                                    </div>';
                                }
                            }
                            ?>
                            <div class="col-sm-4" style="padding: 0 10px 0;">
                                <h4 style="font-size: 14px; margin-bottom: 13px; text-align: center; margin-top: 0; margin-left: 0; border-bottom: 1px solid #dedede; padding-bottom: 13px;">
                                    <?php if (!empty($necessary_changes)): ?>
                                        <i style="color: #d42020;" class="entypo-attention" data-toggle="tooltip" data-placement="top" data-original-title="Specifikace u vířivky neodpovídá zvolené specifikaci u poptávky."></i>
                                    <?php else: ?>
                                        <i style="color: #00a651;" class="entypo-check"></i>
                                    <?php endif; ?>
                                    Nutné změny
                                    <a href="/admin/pages/warehouse/zobrazit-virivku?id=<?= $warehouse['id'] ?>" target="_blank" style="font-size: 11px;"> - zobrazit skladovou položku</a>
                                </h4>
                                <?= !empty($necessary_changes) ? $necessary_changes : '<div style="margin-bottom: 6px; padding: 0;"><i class="entypo-right-open-mini"></i>žádné změny</div>' ?>
                            </div>
                            <?php
                        }
                    }
                } else {
                    ?>
                    <div class="col-sm-4" style="padding: 0 10px 0;">
                        <h4 style="font-size: 14px; margin-bottom: 13px; text-align: center; margin-top: 0; margin-left: 0; border-bottom: 1px solid #dedede; padding-bottom: 13px;">
                            <i class="entypo-right-open"></i> Nemá skladovou vířivku
                        </h4>
                        <div style="margin-bottom: 6px; padding: 0;">
                            <i class="entypo-right-open-mini"></i>
                            poptávka ještě nemá přiřazenou skladovou položku
                        </div>
                    </div>
                    <?php
                }

                // Specifikace provedení
                $get_provedeni = $mysqli->query("SELECT value FROM demands_specs_bridge WHERE client_id = '" . $getclient['id'] . "' AND specs_id = 5") or die($mysqli->error);
                $provedeni = mysqli_fetch_array($get_provedeni);

                if (isset($provedeni['value']) && $provedeni['value'] != '') {
                    $get_ids = $mysqli->query("SELECT w.id AS id, w.name AS name FROM warehouse_products_types w, warehouse_products p WHERE w.warehouse_product_id = p.id AND p.connect_name = '" . $getclient['product'] . "' AND w.name = '" . $provedeni['value'] . "'") or die($mysqli->error);
                    $get_id  = mysqli_fetch_array($get_ids);

                    $specs_query = $mysqli->query("SELECT *, s.id AS id FROM specs s, warehouse_products_types_specs w WHERE w.spec_id = s.id AND w.type_id = '" . $get_id['id'] . "' AND s.demand_category < 3 AND s.is_demand = 1 GROUP BY s.id ORDER BY s.demand_category ASC, s.name ASC") or die($mysqli->error);

                    $category_done = null;
                    while ($specs = mysqli_fetch_array($specs_query)) {
                        if ($specs['demand_category'] == 1 && $category_done !== 1) {
                            ?>
                            <div class="col-sm-4" style="padding: 0 10px 0; border-left: 1px dashed #cccccc;">
                                <h4 style="font-size: 14px; margin-bottom: 13px; text-align: center; margin-top: 0; border-bottom: 1px solid #dedede; padding-bottom: 13px;">Obecné hlavní</h4>
                                <div class="col-sm-12" style="margin-bottom: 6px; padding: 0;">
                                    <i class="entypo-right-open-mini"></i>
                                    Typ vířivky: <strong><?= returnpn($getclient['customer'], $getclient['product']) ?></strong>
                                </div>
                                <div class="col-sm-12" style="margin-bottom: 6px; padding: 0;">
                                    <i class="entypo-right-open-mini"></i>
                                    Provedení: <strong><?= $provedeni['value'] ?></strong>
                                </div>
                            <?php
                            $category_done = 1;
                        } elseif ($specs['demand_category'] == 2 && $category_done !== 2) {
                            ?>
                            </div>
                            <div class="col-sm-4" style="padding: 0 10px 0; border-left: 1px dashed #cccccc;">
                                <h4 style="font-size: 14px; margin-bottom: 13px; text-align: center; margin-top: 0; border-bottom: 1px solid #dedede; padding-bottom: 13px;">Příplatková výbava</h4>
                            <?php
                            $category_done = 2;
                        } elseif ($specs['demand_category'] == 3 && $category_done !== 3) {
                            ?>
                            </div>
                            <div class="col-sm-6" style="padding: 0 10px 0; border-left: 1px dashed #cccccc;">
                                <h4 style="font-size: 14px; margin-bottom: 13px; text-align: center; margin-top: 0; border-bottom: 1px solid #dedede; padding-bottom: 13px;">Specifikace provedení</h4>
                            <?php
                            $category_done = 3;
                        }

                        $paramsquery = $mysqli->query('SELECT value FROM demands_specs_bridge WHERE specs_id = "' . $specs['id'] . '" AND client_id = "' . $getclient['id'] . '"') or die($mysqli->error);
                        $params      = mysqli_fetch_array($paramsquery);

                        if (mysqli_num_rows($searchquery) > 0) {
                            mysqli_data_seek($searchquery, 0);
                            $specsdemquery = $mysqli->query('SELECT value FROM warehouse_specs_bridge WHERE specs_id = "' . $specs['id'] . '" AND client_id = "' . $virivkaid . '"') or die($mysqli->error);
                        } else {
                            $specsdemquery = $mysqli->query('SELECT value FROM containers_products_specs_bridge WHERE specs_id = "' . $specs['id'] . '" AND client_id = "' . $virivkaid . '"') or die($mysqli->error);
                        }
                        $demandsspecs = mysqli_fetch_array($specsdemquery);

                        if ((!empty($params['value']) && $params['value'] !== 'Ne' && $params['value'] !== 'IQue Ozonátor')
                            && !in_array($params['value'], ['2,25 kW', '1,85 kW', '3 kW', '1,5 kW', '2x 1,5 kW'])) {

                            if ($specs['id'] == 16 && $params['value'] === '2 speed 2,25 kW' && $provedeni['value'] !== 'Gold') {
                                continue;
                            }
                            ?>
                            <div <?= ($category_done == 3) ? 'class="col-sm-6"' : 'class="col-sm-12"' ?> style="margin-bottom: 6px; padding: 0;">
                                <i class="entypo-right-open-mini"></i>
                                <?php if ($specs['id'] == 7): ?>
                                    <strong><?= $params['value'] ?></strong>
                                <?php else: ?>
                                    <?= $specs['name'] ?>: <strong><?= $params['value'] ?></strong>
                                <?php endif; ?>
                            </div>
                            <?php
                        }
                    }

                    // Příslušenství
                    ?><hr style="border-color: #dfdfdf;"><?php

                    $accessories_query = $mysqli->query("SELECT * FROM demands_accessories_bridge WHERE aggregate_id = '" . $id . "'") or die($mysqli->error);
                    while ($accessory = mysqli_fetch_array($accessories_query)) {
                        $subtitle = $accessory['variation_values'] ?: '';
                        $aName    = $accessory['product_name'] . $subtitle;
                        ?>
                        <div <?= ($category_done == 3) ? 'class="col-sm-6"' : 'class="col-sm-12"' ?> style="margin-bottom: 6px; padding: 0;">
                            <i class="entypo-right-open-mini"></i>
                            <a href="../accessories/zobrazit-prislusenstvi?id=<?= $accessory['product_id'] ?>" target="_blank"><strong><?= $aName ?></strong></a>
                        </div>
                        <?php
                    }
                    ?>
                    </div>
                    <?php
                } else {
                    ?>
                    <div class="col-sm-12" style="margin-bottom: 6px; padding: 0;">
                        <i class="entypo-right-open-mini"></i>
                        Poptávka nemá zvolené žádné provedení.
                    </div>
                    <?php
                }
                ?>
            </div><!-- /col-sm-12 specifikace -->

            <div class="clear"></div>
            <hr style="width: 100%; border-top: 1px solid #ffffff; margin-top: 22px; margin-bottom: 3px;">

            <!-- Popisky (prodejci + technici + sklad) -->
            <div class="col-sm-12" style="padding: 0;">
                <div class="profile-env" style="margin-bottom: 0;">
                    <section class="profile-feed" style="margin-bottom: 0;">
                        <div class="profile-stories" style="margin-bottom: 0;">

                            <!-- Popisek prodejců -->
                            <article class="story <?= (isset($found_product) && $found_product['description'] != '') ? 'col-sm-4' : 'col-sm-6' ?>" style="padding: 0; margin: 16px 0 !important;">
                                <aside class="user-thumb" style="width: 20%; text-align: center; border-right: 1px solid #cccccc; padding-right: 12px; font-size: 11px;">
                                    <i class="entypo-info" style="font-size: 30px; width: 100%; float: left; margin-bottom: 4px;"></i>
                                    Informace prodejců
                                </aside>
                                <div class="story-content" style="margin-top: 10px; width: 78%;">
                                    <div class="story-main-content">
                                        <div <?php if ($access_edit) echo 'class="descriptiontext"'; ?>
                                             <?php if (empty($getclient['description'])) echo 'style="cursor: pointer;"'; ?>>
                                            <?= !empty($getclient['description']) ? $getclient['description'] : '<strong>Pro přidání popisku klikněte zde.</strong>' ?>
                                        </div>
                                        <?php if ($access_edit): ?>
                                            <form id="editdescription" role="form" method="post" enctype="multipart/form-data" style="display: none;">
                                                <textarea class="form-control" name="description" style="height: 100px;" id="zmrdsample"><?= $getclient['description'] ?></textarea>
                                                <input type="hidden" name="id" value="<?= $getclient['id'] ?>">
                                                <input type="hidden" name="type" value="text">
                                                <div class="row" style="margin-top: 16px;">
                                                    <div class="col-md-8 col-sm-5" style="text-align: left; float: left;">
                                                        <a id="sendform" style="cursor: pointer; margin-right: 4px;" class="btn btn-green btn-icon icon-left btn-lg">
                                                            <i class="entypo-pencil"></i> Upravit popisek
                                                        </a>
                                                        <a id="canceledit"><button type="button" class="btn btn-white btn-lg"><i class="entypo-cancel"></i></button></a>
                                                    </div>
                                                </div>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </article>

                            <!-- Popisek techniků -->
                            <article class="story <?= (isset($found_product) && $found_product['description'] != '') ? 'col-sm-4' : 'col-sm-6' ?>" style="padding: 0; margin: 16px 0 !important;">
                                <aside class="user-thumb" style="width: 20%; text-align: center; border-right: 1px solid #cccccc; padding-right: 12px; font-size: 11px;">
                                    <i class="fa fa-wrench" style="font-size: 30px; width: 100%; float: left; margin-bottom: 4px;"></i>
                                    Informace techniků
                                    <strong style="float: left; margin-top: 8px;">
                                        <?php
                                        $type_tech = ($getclient['customer'] == '0') ? 'realization_sauna' : 'realization_hottub';
                                        $technicians_query = $mysqli->query("SELECT c.user_name FROM demands c, mails_recievers t WHERE c.id = t.admin_id AND t.type_id = '$id' AND t.type = '" . $type_tech . "' AND t.reciever_type = 'performer'") or die($mysqli->error);
                                        $techNames = [];
                                        while ($tech = mysqli_fetch_array($technicians_query)) {
                                            $techNames[] = $tech['user_name'];
                                        }
                                        echo implode(', ', $techNames);
                                        ?>
                                    </strong>
                                </aside>
                                <div class="story-content" style="margin-top: 10px; width: 78%;">
                                    <div class="story-main-content">
                                        <div <?php if ($access_edit) echo 'class="technical_description_text"'; ?>
                                             <?php if (empty($getclient['technical_description'])) echo 'style="cursor: pointer;"'; ?>>
                                            <?= !empty($getclient['technical_description']) ? $getclient['technical_description'] : '<strong>Pro přidání popisku klikněte zde.</strong>' ?>
                                        </div>
                                        <?php if ($access_edit): ?>
                                            <form id="technical_edit_description" role="form" method="post" enctype="multipart/form-data" style="display: none;">
                                                <textarea class="form-control" name="technical_description" style="height: 100px;" id="technical_zmrdsample"><?= $getclient['technical_description'] ?></textarea>
                                                <input type="hidden" name="id" value="<?= $getclient['id'] ?>">
                                                <input type="hidden" name="type" value="text">
                                                <div class="row" style="margin-top: 16px;">
                                                    <div class="col-md-8 col-sm-5" style="text-align: left; float: left;">
                                                        <a id="send_technical" style="cursor: pointer; margin-right: 4px;" class="btn btn-green btn-icon icon-left btn-lg">
                                                            <i class="entypo-pencil"></i> Upravit popisek
                                                        </a>
                                                        <a id="technical_cancel_edit"><button type="button" class="btn btn-white btn-lg"><i class="entypo-cancel"></i></button></a>
                                                    </div>
                                                </div>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </article>

                            <!-- Informace ze skladu (volitelné) -->
                            <?php if (isset($found_product) && $found_product['description'] != ''): ?>
                                <article class="story col-sm-4" style="margin: 16px 0 10px !important;">
                                    <aside class="user-thumb" style="padding: 0; width: 20%; text-align: center; border-right: 1px solid #cccccc; padding-right: 12px; font-size: 11px;">
                                        <i class="fa fa-truck" style="font-size: 30px; width: 100%; float: left; margin-bottom: 4px;"></i>
                                        Informace ve skladu
                                    </aside>
                                    <div class="story-content" style="margin-top: 10px; width: 78%;">
                                        <div class="story-main-content">
                                            <div><?= $found_product['description'] ?></div>
                                        </div>
                                    </div>
                                </article>
                            <?php endif; ?>

                        </div>
                    </section>
                </div>
            </div><!-- /popisky -->

        </div><!-- /row -->
    </section><!-- /profile-info-tabs -->


    <!-- ===== KONTAKTNÍ OSOBY ===== -->
    <?php
    $contacts_query = $mysqli->query("SELECT * FROM demands_contacts WHERE demand_id = '$id'") or die($mysqli->error);
    if (mysqli_num_rows($contacts_query) > 0):
    ?>
    <div class="clear"></div>
    <hr style="width: 100%; border-top: 1px solid #ffffff; margin-top: 14px; margin-bottom: 14px;">
    <div class="clear"></div>
    <div class="" style="width: 8%; float: left; height: 1px;"></div>
    <div class="col-sm-10" style="width: 85.33333333%; margin: 12px 0 2px;">
        <?php while ($contact = mysqli_fetch_array($contacts_query)): ?>
            <div class="col-md-3">
                <ul class="user-details" style="padding: 0 22px; border: 1px solid #dedede; border-radius: 5px; background-color: #f9f9f9;">
                    <li><h3 style="margin-top: 12px;"><?= $contact['name'] ?></h3></li>
                    <li>
                        <i class="entypo-user" style="margin-right: 5px;"></i>
                        <?= !empty($contact['role']) ? '<strong>' . $contact['role'] . '</strong>' : 'žádná role' ?>
                    </li>
                    <li>
                        <i class="entypo-phone" style="margin-right: 5px;"></i>
                        <?php if (isset($contact['phone']) && $contact['phone'] != ''): ?>
                            +420 <strong><?= number_format($contact['phone'], 0, ',', ' ') ?></strong>
                        <?php else: ?>
                            žádný telefon
                        <?php endif; ?>
                    </li>
                    <li>
                        <i class="entypo-mail" style="margin-right: 5px;"></i>
                        <?= ($contact['email'] != '') ? '<strong><a href="mailto:' . $contact['email'] . '">' . $contact['email'] . '</a></strong>' : 'žádný email' ?>
                    </li>
                </ul>
            </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>


    <!-- ===== TABS (hotové poptávky) ===== -->
    <?php if (in_array($getclient['status'], [5, 8, 13])): ?>
        <?php if (in_array($getclient['status'], [5, 8, 12, 4, 13])): ?>
            <div class="col-sm-12" style="padding-left: 54px;">
                <div class="col-sm-10">
                    <ul class="nav nav-tabs">
                        <li id="hide" data-target="hide-tab" class="active">
                            <a style="cursor: pointer; padding: 10px 20px;"><i class="entypo-eye"></i></a>
                        </li>
                        <li id="service" data-target="service-tab">
                            <a style="cursor: pointer;"><i class="entypo-tools"></i> Servis</a>
                        </li>
                        <?php if ($access_edit): ?>
                            <li id="orders"><a style="cursor: pointer;"><i class="entypo-basket"></i> Objednávky</a></li>
                            <li id="documents">
                                <a style="cursor: pointer;">
                                    <i class="entypo-book"></i> Dokumenty
                                    <span class="badge badge-secondary" style="margin-left: 4px;"><?= $missingDocuments ?></span>
                                </a>
                            </li>
                            <li id="generate-data"><a style="cursor: pointer;"><i class="entypo-rocket"></i> Generování</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="clear"></div>

</section><!-- /profile-feed -->


<!-- ===== PARTS / TABS ===== -->
<?php include $_SERVER['DOCUMENT_ROOT'] . '/admin/pages/demands/parts/tabs.php'; ?>

<?php if ($access_edit): ?>

    <?php if (!in_array($getclient['status'], [12, 4, 8, 5, 13])): ?>
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/admin/pages/demands/parts/follow-ups.php'; ?>
    <?php endif; ?>


    <!-- ===== MANAGEMENT PRODANÉ POPTÁVKY ===== -->
    <?php if (in_array($getclient['status'], [15, 12, 4, 8])): ?>
        <hr>
        <h2 style="text-align: center; margin: 23px 0 22px;">Management prodané poptávky</h2>
        <hr>

        <!-- Smlouva -->
        <?php
        $contractSteps = [
            [0, 'red',    'Nevystavená smlouva'],
            [1, 'yellow', 'Vystavená smlouva'],
            [2, 'orange', 'Podepsaná smlouva'],
            [3, 'green',  'Zaplacená'],
        ];
        ?>
        <section class="profile-feed sold-management" style="width: 25%; padding: 0; float: left; z-index: 0; border-right: 1px solid #EEEEEE;">
            <h3 style="text-align: center;">Smlouva</h3>
            <form id="rootwizard" method="post" action="" class="form-horizontal form-wizard" style="margin-top: 60px;">
                <div class="steps-progress"><div class="progress-indicator"></div></div>
                <ul>
                    <?php foreach ($contractSteps as [$val, $cls, $label]): ?>
                        <li <?= ($getclient['contract'] == $val) ? 'class="completed ' . $cls . '"' : '' ?>>
                            <a href="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=edit_sold&type=contract&value=<?= $val ?>">
                                <span><?= $val ?></span><?= $label ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </form>
        </section>

        <!-- Stavební příprava -->
        <?php
        $technicalSteps = [
            [0, 'red',    'K zavolání'],
            [1, 'yellow', 'Odeslaný email'],
            [2, 'orange', 'V řešení'],
            [3, 'green',  'Komplet'],
        ];
        ?>
        <section class="profile-feed sold-management" style="width: 30%; padding: 0; float: left; z-index: 0; border-right: 1px solid #EEEEEE;">
            <h3 style="text-align: center;">Stavební příprava</h3>
            <form id="rootwizard" method="post" action="" class="form-horizontal form-wizard" style="margin-top: 60px;">
                <div class="steps-progress"><div class="progress-indicator"></div></div>
                <ul>
                    <?php foreach ($technicalSteps as [$val, $cls, $label]): ?>
                        <li <?= ($getclient['technical'] == $val) ? 'class="completed ' . $cls . '"' : '' ?>>
                            <a href="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=edit_sold&type=technical&value=<?= $val ?>">
                                <span><?= $val ?></span><?= $label ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </form>
        </section>

        <!-- Realizace -->
        <section class="profile-feed sold-management" style="width: 19%; padding: 0; float: left; z-index: 0; border-right: 1px solid #EEEEEE;">
            <h3 style="text-align: center;">Realizace</h3>
            <?php if (isset($getclient['realization']) && $getclient['realization'] !== '0000-00-00'): ?>
                <form id="rootwizard" method="post" action="" class="form-horizontal form-wizard" style="margin-top: 60px;">
                    <div class="steps-progress"><div class="progress-indicator"></div></div>
                    <ul>
                        <li <?= ($getclient['confirmed'] == 0) ? 'class="completed teal"' : '' ?>>
                            <a href="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=edit_sold&type=realization&value=0"><span>0</span>Plánovaná</a>
                        </li>
                        <li <?= ($getclient['confirmed'] == 1) ? 'class="completed green"' : '' ?>>
                            <a href="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=edit_sold&type=realization&value=1"><span>1</span>Potvrzená</a>
                        </li>
                    </ul>
                </form>
            <?php else: ?>
                <div class="alert alert-info" style="margin: 40px 20px; text-align: center;">realizace zatím není naplánovaná</div>
            <?php endif; ?>
        </section>

        <!-- Nedokončené -->
        <?php
        $unfinishedSteps = [
            [0, 'red',    'Neřešeno'],
            [1, 'orange', 'V řešení'],
            [2, 'green',  'Připravená'],
        ];
        ?>
        <section class="profile-feed sold-management" style="width: 26%; padding: 0; float: left; z-index: 0;">
            <h3 style="text-align: center;">Nedokončené</h3>
            <form id="rootwizard" method="post" action="" class="form-horizontal form-wizard" style="margin-top: 60px;">
                <div class="steps-progress"><div class="progress-indicator"></div></div>
                <ul>
                    <?php foreach ($unfinishedSteps as [$val, $cls, $label]): ?>
                        <li <?= ($getclient['unfinished'] == $val) ? 'class="completed ' . $cls . '"' : '' ?>>
                            <a href="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=edit_sold&type=unfinished&value=<?= $val ?>">
                                <span><?= $val ?></span><?= $label ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </form>
        </section>

        <div class="clear"></div>
        <hr style="margin: 12px;">
    <?php endif; ?>


    <!-- ===== POZNÁMKY ===== -->
    <section class="profile-feed" style="width: 50%; padding: 0% 2% 0 1%; float: left; z-index: 0; border-right: 1px solid #EEEEEE;">
        <h2>Poznámky</h2>
        <hr>
        <div class="profile-stories" style="margin-top: 30px;">
            <?php
            $check_starred     = $mysqli->query('SELECT * FROM demands_timeline WHERE client_id = "' . $getclient['id'] . '" AND star = "1"') or die($mysqli->error);
            $demandstextquery  = $mysqli->query('SELECT *, DATE_FORMAT(datetime, "%d. %m. %Y") AS dateformated, DATE_FORMAT(datetime, "%H:%i") AS hoursmins FROM demands_timeline WHERE client_id="' . $getclient['id'] . '" ORDER BY id DESC') or die($mysqli->error);

            if (mysqli_num_rows($demandstextquery) > 0):
                while ($demandstext = mysqli_fetch_assoc($demandstextquery)):
                    $adminquery = $mysqli->query('SELECT user_name, avatar, id FROM demands WHERE id="' . $demandstext['admin_id'] . '"') or die($mysqli->error);
                    $admin      = mysqli_fetch_assoc($adminquery);
                    $isStarred  = isset($demandstext['star']) && $demandstext['star'] == 1;
            ?>
                <article id="story-<?= $demandstext['id'] ?>"
                         class="story <?= $isStarred ? 'starred' : '' ?>"
                         style="margin: 0; margin-bottom: 10px; padding: 8px 30px; <?= $isStarred ? 'font-weight: bold; background-color: #ebf1f6; border-radius: 3px; padding-top: 30px; padding-bottom: 12px;' : '' ?>">

                    <aside class="user-thumb">
                        <a href="#"><img src="/admin/assets/avatars/<?= $admin['id'] ?>.jpg" alt="" width="44" class="img-circle" /></a>
                    </aside>

                    <div class="story-content" style="width: 88%;">
                        <header>
                            <div class="publisher">
                                <a href="#"><?= $admin['user_name'] ?></a>
                                <em><?= $demandstext['dateformated'] . ' v ' . $demandstext['hoursmins'] ?></em>
                            </div>

                            <?php if (isset($access_edit) && $access_edit): ?>
                                <div class="story-type" style="margin-left: 10px; margin-right: 6px;">
                                    <span style="border-right: 1px solid #cccccc; margin-right: 8px;"></span>
                                    <a data-id="<?= $demandstext['id'] ?>" class="remove-note" style="color: #949494; cursor: pointer;"><i class="entypo-trash"></i></a>
                                </div>
                            <?php endif; ?>

                            <div data-id="<?= $demandstext['id'] ?>" class="kill-note story-type" style="margin-left: 10px; cursor: pointer;"><i class="fa fa-strikethrough"></i></div>
                            <div data-id="<?= $demandstext['id'] ?>" class="bold-note story-type" style="margin-left: 10px; cursor: pointer;"><i class="fa fa-bold"></i></div>
                            <div data-id="<?= $demandstext['id'] ?>" class="star-note star story-type"
                                 style="cursor: pointer; <?= (mysqli_num_rows($check_starred) > 0 && !$isStarred) ? 'display: none;' : '' ?>">
                                <i class="fa fa-star"></i>
                            </div>
                        </header>

                        <div class="story-main-content">
                            <p id="killed-<?= $demandstext['id'] ?>" style="<?php
                                if (isset($demandstext['line']) && $demandstext['line'] == 1) echo 'text-decoration: line-through;';
                                if (isset($demandstext['bold']) && $demandstext['bold'] == 1) echo 'font-weight: bold;';
                                if ($isStarred) echo 'font-weight: bold; color: #303641;';
                            ?>"><?= $demandstext['text'] ?></p>
                        </div>
                        <hr style="margin-top: 19px; margin-bottom: 0;" />
                    </div>
                </article>
            <?php
                endwhile;
            else:
            ?>
                <article id="story" class="story" style="margin: 0; margin-bottom: 21px;">
                    <div class="story-content" style="width: 100%; text-align: center;">
                        <header><div><em>U poptávky zatím nejsou žádné poznámky.</em></div></header>
                        <hr style="margin-top: 24px; margin-bottom: 0;" />
                    </div>
                </article>
            <?php endif; ?>
        </div>

        <!-- Přidat poznámku -->
        <form class="profile-post-form" method="post" enctype="multipart/form-data"
              action="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=add">
            <textarea class="form-control autogrow" name="text" placeholder="Stalo se něco nového?"></textarea>
            <div class="form-options">
                <div class="post-submit">
                    <button type="submit" class="btn btn-primary">Přidat poznámku</button>
                </div>
            </div>
        </form>
    </section>


    <!-- ===== ÚKOLY ===== -->
    <?php $cliquery = $mysqli->query('SELECT id, user_name FROM demands WHERE role != "client" AND active = 1') or die($mysqli->error); ?>
    <section class="profile-feed" style="width: 50%; padding: 0% 1% 0 2%; float: left; z-index: 0;">
        <h2>Úkoly</h2>
        <hr>

        <div class="panel-group" id="accordion-test">
            <?php
            $demandstasksquery = $mysqli->query('SELECT *, DATE_FORMAT(date, "%d. %m. %Y") AS dateformated, DATE_FORMAT(due, "%d. %m. %Y") AS dueformated FROM tasks WHERE demand_id = "' . $getclient['id'] . '" ORDER BY id DESC') or die($mysqli->error);
            if (mysqli_num_rows($demandstasksquery) > 0) {
                while ($demandstasks = mysqli_fetch_assoc($demandstasksquery)) {
                    task($demandstasks, $client['avatar'], $access_edit, 'pages/demands/zobrazit-poptavku&redirectid=' . $getclient['id']);
                }
            } else {
                ?>
                <div class="panel panel-default" style="margin-bottom: 13px;">
                    <div class="panel-heading">
                        <h4 class="panel-title" style="height: 100px; line-height: 80px; padding-bottom: 0; text-align: center;">
                            U poptávky zatím nejsou žádné zadané úkoly.
                        </h4>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>

        <center><button id="addtask" type="button" class="btn btn-primary" style="height: 71px; width: 300px; margin-bottom: 14px; font-size: 17px;">Přidat úkol</button></center>

        <form id="taskform" role="form" method="post" enctype="multipart/form-data" autocomplete="off"
              action="/admin/controllers/task-controller?id=<?= $getclient['id'] ?>&task=add&redirect=pages/demands/zobrazit-poptavku&redirectid=<?= $getclient['id'] ?>"
              style="display: none;">
            <input type="hidden" name="choosed_who" value="demand">
            <input type="hidden" name="demandus" value="<?= $getclient['id'] ?>">
            <input type="text" style="width: 49%; float: left; margin: 0 10px 10px 0;" name="title" placeholder="Krátký název úkolu" class="form-control" id="field-1" required>
            <input type="text" style="width: 37%; float: left; margin: 0 0 10px 0;" name="datum" class="form-control datepicker" data-format="yyyy-mm-dd" placeholder="Datum provedení" required>
            <input type="text" style="width: 12%;" class="form-control timepicker" name="time" data-template="dropdown" data-show-seconds="false" data-default-time="00-00" data-show-meridian="false" data-minute-step="5" placeholder="Čas" />

            <div class="form-group well admins_well" style="background-color: #FFFFFF; padding: 12px 0 7px; float: left; width: 100%;">
                <h4 style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">Proveditelé</h4>
                <?php
                $adminsquery = $mysqli->query("SELECT id, user_name FROM demands WHERE role != 'client' AND active = 1");
                while ($admins = mysqli_fetch_array($adminsquery)):
                ?>
                    <div class="col-sm-3">
                        <input id="admin-<?= $admins['id'] ?>-event-performer" name="performer[]" value="<?= $admins['id'] ?>" type="checkbox">
                        <label for="admin-<?= $admins['id'] ?>-event-performer" style="padding-left: 4px; cursor: pointer;"><?= $admins['user_name'] ?></label>
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="form-group well admins_well" style="background-color: #FFFFFF; padding: 12px 0 7px; float: left; width: 100%;">
                <h4 style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">Informovaní</h4>
                <?php
                mysqli_data_seek($adminsquery, 0);
                while ($admins = mysqli_fetch_array($adminsquery)):
                ?>
                    <div class="col-sm-3">
                        <input id="admin-<?= $admins['id'] ?>-event-observer" name="observer[]" value="<?= $admins['id'] ?>" type="checkbox"
                               <?= ($client['id'] == $admins['id']) ? 'checked' : '' ?>>
                        <label for="admin-<?= $admins['id'] ?>-event-observer" style="padding-left: 4px; cursor: pointer;"><?= $admins['user_name'] ?></label>
                    </div>
                <?php endwhile; ?>
            </div>

            <textarea class="form-control autogrow" name="text" placeholder="Popis zadaného úkolu..." style="overflow: hidden; margin-bottom: 8px; word-wrap: break-word; resize: horizontal; height: 80px;"></textarea>
            <button type="submit" class="btn btn-primary" style="width: 82%; height: 71px; margin-bottom: 14px; font-size: 17px;">Přidat úkol</button>
            <button type="button" id="canceladdtask" class="btn btn-default" style="width: 17%; height: 71px; margin-bottom: 14px; font-size: 17px;"><i class="entypo-cancel"></i></button>
        </form>
    </section>

    <div class="clear"></div>
    <hr />


    <!-- ===== SOUBORY K POPTÁVCE ===== -->
    <div class="col-sm-12">
        <h2 style="text-align: center; margin-top: 36px; margin-bottom: 30px;">Soubory k poptávce</h2>
        <hr>
        <?php
        $uploadFiles = glob($_SERVER['DOCUMENT_ROOT'] . '/data/clients/uploads/' . $getclient['secretstring'] . '/*');
        uasort($uploadFiles, function($a, $b) { return filemtime($a) < filemtime($b); });

        foreach ($uploadFiles as $res):
            $filename = pathinfo($res, PATHINFO_FILENAME);
            $ext      = pathinfo($res, PATHINFO_EXTENSION);
            $isImage  = in_array($ext, $image_extensions);
        ?>
            <section id="servistab" class="profile-feed well" style="width: 24%; margin-right: 1%; float: left; margin-bottom: 30px;">
                <div class="profile-stories">
                    <article class="story" style="margin: 0;">
                        <div style="display: block;">
                            <span style="width: 100%;">
                                <h3 style="margin: 2px 0 0; text-align: center; min-height: 26px; line-height: 17px; font-size: 13px; overflow: hidden; text-overflow: ellipsis;"><?= basename($res) ?></h3>
                                <h4 style="font-style: italic; margin: 6px 0 10px; text-align: center; font-size: 12px;"><?= date('d. m. Y H:i', filemtime($res)) ?></h4>
                                <?php if ($isImage): ?>
                                    <center>
                                        <a href="https://www.wellnesstrade.cz/data/clients/uploads/<?= $getclient['secretstring'] ?>/<?= basename($res) ?>" target="_blank">
                                            <img src="https://www.wellnesstrade.cz/data/clients/uploads/<?= $getclient['secretstring'] ?>/<?= basename($res) ?>" style="width: 100%;" class="img-rounded">
                                        </a>
                                    </center>
                                <?php else: ?>
                                    <center>
                                        <a href="https://docs.google.com/viewerng/viewer?url=https://www.wellnesstrade.cz/data/clients/uploads/<?= $getclient['secretstring'] ?>/<?= basename($res) ?>" target="_blank">
                                            <i class="fa fa-file" style="font-size: 140px;"></i>
                                        </a>
                                    </center>
                                <?php endif; ?>
                                <div class="text-center" style="margin-top: 20px;">
                                    <a href="https://www.wellnesstrade.cz/data/clients/uploads/<?= $getclient['secretstring'] ?>/<?= basename($res) ?>"
                                       class="btn btn-blue btn-icon icon-left" style="padding-right: 14px;" download>
                                        <i class="entypo-down"></i> Stáhnout
                                    </a>
                                    <a href="zobrazit-poptavku?id=<?= $getclient['id'] ?>&upload=remove&name=<?= urlencode(basename($res)) ?>"
                                       class="btn btn-red btn-icon icon-left" style="padding-right: 14px; margin-left: 4px;">
                                        <i class="entypo-trash"></i> Smazat
                                    </a>
                                </div>
                            </span>
                        </div>
                    </article>
                </div>
            </section>
        <?php endforeach; ?>

        <div style="clear: both;"></div>

        <!-- Upload formulář -->
        <div class="profile-stories" style="margin-bottom: 50px;">
            <article class="story" style="margin: 0 0 50px 0; min-height: 89px; text-align: center;">
                <form style="text-align: center;" role="form" method="post"
                      action="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=upload_file"
                      enctype="multipart/form-data">
                    <input type="file" style="width: 260px; padding-top: 6px; display: inline-block;" class="form-control" name="file" id="field-file">
                    <button type="submit" class="btn btn-green btn-icon icon-left"><i class="entypo-plus"></i> Nahrát soubor</button>
                </form>
            </article>
        </div>
    </div>

<?php endif; // $access_edit ?>


<!-- ===== OBRÁZKY (technické + realizace) ===== -->
<div class="clear"></div>
<div class="profile-env">
    <section id="demand_pictures" class="profile-info-tabs">
        <div class="row">
            <div class="notes-env">

                <!-- Technické obrázky -->
                <div class="col-sm-6">
                    <div class="notes-header">
                        <div class="col-md-6"><h2 style="margin-left: 20px; margin-top: 8px;">Obrázky technické</h2></div>
                        <div class="col-md-6" style="text-align: right; float: right;">
                            <a data-id="technical" class="toggle-picture-upload-modal btn btn-primary btn-icon icon-left btn-lg" style="margin-top: 3px;">
                                <i class="entypo-plus"></i> Přidat obrázky
                            </a>
                        </div>
                    </div>

                    <?php
                    $files = array_map('basename', glob($_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/technical/' . $getclient['secretstring'] . '/[!-small_]*.{' . extList($image_extensions) . '}', GLOB_BRACE));
                    ?>
                    <div id="load-technical" class="notes-list" <?= empty($files) ? 'style="padding: 23px 0 22px;"' : '' ?>>
                        <ul class="list-of-notes lightgallery" style="padding: 0 20px;">
                            <?php if (!empty($files)):
                                foreach ($files as $file):
                                    $originalFileName = substr($file, 4);
                                    $full_image  = '/data/clients/pictures/technical/' . $getclient['secretstring'] . '/big_' . $originalFileName;
                                    $small_image = file_exists($_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/technical/' . $getclient['secretstring'] . '/small_' . $originalFileName)
                                        ? '/data/clients/pictures/technical/' . $getclient['secretstring'] . '/small_' . $originalFileName
                                        : $full_image;
                            ?>
                                <div class="single-picture" style="width: 19%; margin: 10px 0.5% 10px 0; display: inline-block; border: 1px solid #dfdfdf; border-radius: 4px;">
                                    <a class="remove-picture-technical btn btn-sm btn-danger" style="position: absolute; border: 1px solid #FFF; border-radius: 3px;"
                                       data-picture="<?= basename($originalFileName) ?>" data-toggle="tooltip" data-placement="top" data-original-title="Odstranit obrázek">
                                        <i class="entypo-trash"></i>
                                    </a>
                                    <a data-src="<?= $full_image ?>" href="<?= $full_image ?>" class="full" rel="technical">
                                        <img src="<?= $small_image ?>" width="100%" class="img-rounded">
                                    </a>
                                </div>
                            <?php endforeach;
                            else: ?>
                                <ul class="cbp_tmtimeline">
                                    <li style="width: 100%;">
                                        <div class="cbp_tmicon" style="margin-left: -1px;"><i class="entypo-block" style="line-height: 42px !important;"></i></div>
                                        <div class="cbp_tmlabel empty" style="margin-top: -29px; margin-bottom: 0; padding-top: 9px;">
                                            <span style="font-weight: bold; margin-left: -12px; font-size: 17px;">U poptávky ještě nejsou žádné obrázky.</span>
                                        </div>
                                    </li>
                                </ul>
                            <?php endif; ?>
                            <div class="clear"></div>
                        </ul>
                    </div>
                </div><!-- /technické obrázky -->


                <!-- Obrázky realizace -->
                <div class="col-sm-6">
                    <div class="notes-header">
                        <div class="col-md-6"><h2 style="margin-left: 20px; margin-top: 8px;">Obrázky realizace</h2></div>
                        <div class="col-md-6" style="text-align: right; float: right;">
                            <a data-id="realization" class="toggle-picture-upload-modal btn btn-primary btn-icon icon-left btn-lg" style="margin-top: 3px;">
                                <i class="entypo-plus"></i> Přidat obrázky
                            </a>
                        </div>
                    </div>

                    <?php
                    $files = array_map('basename', glob($_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/realization/' . $getclient['secretstring'] . '/[!-small_]*.{' . extList($image_extensions) . '}', GLOB_BRACE));
                    ?>
                    <div id="load-realization" class="notes-list" <?= empty($files) ? 'style="padding: 23px 0 22px;"' : '' ?>>
                        <ul class="list-of-notes lightgallery">
                            <?php if (!empty($files)):
                                foreach ($files as $file):
                                    $originalFileName = substr($file, 4);
                                    $full_image  = '/data/clients/pictures/realization/' . $getclient['secretstring'] . '/big_' . $originalFileName;
                                    $small_image = file_exists($_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/realization/' . $getclient['secretstring'] . '/small_' . $originalFileName)
                                        ? '/data/clients/pictures/realization/' . $getclient['secretstring'] . '/small_' . $originalFileName
                                        : $full_image;
                            ?>
                                <div class="single-picture" style="width: 19%; margin: 10px 0.5% 10px 0; display: inline-block; border: 1px solid #dfdfdf; border-radius: 4px;">
                                    <a class="remove-picture btn btn-sm btn-danger" style="position: absolute; border: 1px solid #FFF; border-radius: 3px;"
                                       data-picture="<?= basename($originalFileName) ?>" data-toggle="tooltip" data-placement="top" data-original-title="Odstranit obrázek">
                                        <i class="entypo-trash"></i>
                                    </a>
                                    <a data-src="<?= $full_image ?>" href="<?= $full_image ?>" class="full" rel="realization">
                                        <img src="<?= $small_image ?>" width="100%" class="img-rounded">
                                    </a>
                                </div>
                            <?php endforeach;
                            else: ?>
                                <ul class="cbp_tmtimeline">
                                    <li style="width: 100%;">
                                        <div class="cbp_tmicon" style="margin-left: -1px;"><i class="entypo-block" style="line-height: 42px !important;"></i></div>
                                        <div class="cbp_tmlabel empty" style="margin-top: -29px; margin-bottom: 0; padding-top: 9px;">
                                            <span style="font-weight: bold; margin-left: -12px; font-size: 17px;">U poptávky ještě nejsou žádné obrázky.</span>
                                        </div>
                                    </li>
                                </ul>
                            <?php endif; ?>
                            <div class="clear"></div>
                        </ul>
                    </div>
                </div><!-- /obrázky realizace -->

            </div><!-- /notes-env -->
        </div><!-- /row -->
    </section><!-- /demand_pictures -->
</div><!-- /profile-env -->


<!-- ===== MAILOVÁ HISTORIE ===== -->
<?php
$mails_query = $mysqli->query("SELECT * FROM mails_archive WHERE reciever_id = '" . $getclient['id'] . "'") or die($mysqli->error);
if (mysqli_num_rows($mails_query) > 0):
?>
<div class="panel-group" id="accordion-test">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <a data-toggle="collapse" data-parent="#accordion-test" href="#collapseThree" class="collapsed" aria-expanded="false">
                    Mailová historie
                </a>
            </h4>
        </div>
        <div id="collapseThree" class="panel-collapse collapse" aria-expanded="false">
            <div class="panel-body">
                <table class="table table-bordered table-striped datatable dataTable">
                    <thead>
                        <tr>
                            <th>Datum a čas</th>
                            <th>Předmět</th>
                            <th>Odesílatel</th>
                            <th>Akce</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($mail = mysqli_fetch_assoc($mails_query)): ?>
                            <tr>
                                <td><?= $mail['datetime'] ?></td>
                                <td><?= $mail['subject'] ?></td>
                                <td><?= $mail['admin_id'] ?></td>
                                <td>
                                    <a class="toggle-mail-modal btn btn-primary btn-sm btn-icon icon-left" data-id="<?= $mail['id'] ?>">
                                        <i class="entypo-search"></i> Zobrazit mail
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

</div><!-- /profile-env main -->


<!-- ===== GOOGLE MAPA ===== -->
<?php if (isset($getclient['latitude']) && $getclient['latitude'] != '' && $getclient['longitude'] != ''): ?>
<script type="text/javascript" src="//maps.google.com/maps/api/js?key=AIzaSyDRermPdr7opDFLqmrcOuK5L4zC2_U8XGk&sensor=false"></script>
<script type="text/javascript">
function showmap() {
    var directionsDisplay;
    var directionsService = new google.maps.DirectionsService();
    var map;

    function initialize() {
        directionsDisplay = new google.maps.DirectionsRenderer();
        var center = new google.maps.LatLng(<?= $getclient['latitude'] . ', ' . $getclient['longitude'] ?>);
        map = new google.maps.Map(document.getElementById('sample-checkin'), {zoom: 11, center: center});
        directionsDisplay.setMap(map);
        calcRoute();
    }

    function calcRoute() {
        var request = {
            origin: '50.096500, 14.402800',
            destination: '<?= $getclient['latitude'] . ', ' . $getclient['longitude'] ?>',
            travelMode: google.maps.TravelMode.DRIVING
        };
        directionsService.route(request, function(response, status) {
            if (status == google.maps.DirectionsStatus.OK) {
                directionsDisplay.setDirections(response);
            }
        });
    }

    initialize();
}
</script>
<?php endif; ?>


<!-- ===== FOOTER ===== -->
<footer class="main">
    &copy; <?= date('Y') ?>
    <span style="float: right;">
        <?php
        $time        = explode(' ', microtime());
        $total_time  = round(($time[1] + $time[0]) - $start, 4);
        echo 'PHP ' . PHP_VERSION . ' | Page generated in ' . $total_time . ' seconds.';
        ?>
    </span>
</footer>

<?php
include $_SERVER['DOCUMENT_ROOT'] . '/admin/pages/demands/parts/modals.php';
include VIEW . '/default/footer.php';