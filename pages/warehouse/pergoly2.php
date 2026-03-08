<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";

include INCLUDES . "/googlelogin.php";
include INCLUDES . "/functions.php";

if (isset($_REQUEST['od'])) {$od = $_REQUEST['od'];}
if (isset($_REQUEST['q'])) {$search = $_REQUEST['q'];}

if (isset($search) && $search != "") {

    $pagetitle = 'Hledaný výraz "' . $search . '"';

    $bread1 = "Pergoly sklad";
    $abread1 = "pergoly";

} else {

    $pagetitle = "Pergoly sklad";

}

if (isset($_REQUEST['type'])) {$type = $_REQUEST['type'];}
if (isset($_REQUEST['customer'])) {$customer = $_REQUEST['customer'];}
if (isset($_REQUEST['category'])) {$category = $_REQUEST['category'];}
if (isset($_REQUEST['brand'])) { $brand = $_REQUEST['brand'];}

if (isset($_REQUEST['location'])) {$location = $_REQUEST['location'];}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "remove") {

    $mysqli->query('DELETE FROM warehouse WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);
    $mysqli->query('DELETE FROM warehouse_specs_bridge WHERE client_id="' . $_REQUEST['id'] . '"') or die($mysqli->error);

    $selectreserve = $mysqli->query("SELECT product_id, variation_id, reserved FROM warehouse_products_bridge WHERE warehouse_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

    while ($reserve = mysqli_fetch_array($selectreserve)) {

        $instock = $reserve['reserved'];

        if (isset($reserve['variation_id']) && $reserve['variation_id'] == 0) {

            $search1 = $mysqli->query("SELECT s.id, s.reserved, s.quantity FROM demands_sauna_specs s, demands d WHERE s.product_id = '$id' and s.demand_id = d.id and d.status < 5 order by d.id desc");
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

            $warehouse_search = $mysqli->query("SELECT b.id, b.reserved, b.quantity FROM warehouse_products_bridge b, warehouse w WHERE b.product_id = '" . $reserve['product_id'] . "' and b.warehouse_id = w.id and b.warehouse_id != '" . $_REQUEST['id'] . "' and w.status <> 4 order by w.id desc") or die($mysqli->error);
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

                $search1 = $mysqli->query("SELECT b.id, b.reserved, b.quantity FROM orders_products_bridge b, orders o WHERE b.variation_id = '" . $reserve['variation_id'] . "' and b.order_id != '" . $_REQUEST['id'] . "' and b.order_id = o.id and o.order_status <> 4 and b.reserved < b.quantity order by o.id desc");

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

    $mysqli->query('DELETE FROM warehouse_products_bridge WHERE warehouse_id="' . $_REQUEST['id'] . '"') or die($mysqli->error);

    header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/pergoly?id=' . $_REQUEST['id'] . '&success=remove');
    exit;
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "demandnull") {

    $mysqli->query('UPDATE warehouse SET demand_id = "0" WHERE id = "' . $_REQUEST['id'] . '"') or die($mysqli->error);

    $_REQUEST['id'] = $_REQUEST['redirect'];
    saveCalendarEvent($_REQUEST['redirect'], 'realization');

    header('location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $_REQUEST['redirect'] . '&changedemand=success');
    exit;
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "demandchange") {

    $mysqli->query('UPDATE warehouse SET demand_id = "' . $_POST['demand'] . '" WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);

    if ($_POST['demand'] != '' && $_POST['demand'] != '0') {

        $_REQUEST['id'] = $_POST['demand'];
        saveCalendarEvent($_POST['demand'], 'realization');

    }

    $find_container = $mysqli->query("SELECT id FROM containers_products WHERE warehouse_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

    if (mysqli_num_rows($find_container) > 0) {

        $container = mysqli_fetch_array($find_container);

        $update_demand = $mysqli->query("UPDATE containers_products SET demand_id = '" . $_POST['demand'] . "' WHERE id = '" . $container['id'] . "'") or die($mysqli->error);

    }

    header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/pergoly?id=' . $_REQUEST['id'] . '&changedemand=success');
    exit;
}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "add") {

    $displaysuccess = true;
    $successhlaska = "Pergola byla úspěšně přidána.";

}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "remove") {

    $displaysuccess = true;
    $successhlaska = "Pergola byla úspěšně odstraněna.";

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

$query = '';
$currentpage = 'pergoly';
$queryBuilder = array();
$sqlQueryBuilder = array();

if (isset($customer)) {

    $queryBuilder['customer'] = $customer;

    if ($customer == '0') {

        $value = "w.demand_id = '0' AND w.reserved_showroom = 0 ";

    } elseif ($customer == '1') {

        $value = "w.demand_id > '0' AND w.reserved_showroom = 0 ";

    } elseif ($customer == '3') {

        $value = "w.reserved_showroom <> 0 ";

    }

    $sqlQueryBuilder[] .= $value;

}

if (isset($type) && $type != '') {

    $queryBuilder['type'] = $type;
    $sqlQueryBuilder[] .= "w.status = '" . $type . "' ";

} elseif (!isset($type) && !isset($location)) {

    $sqlQueryBuilder[] .= '(status < 3 OR status > 4) ';

}

if (isset($category) && $category != '') {

    $queryBuilder['category'] = $category;
    $sqlQueryBuilder[] .= "w.product = '" . $category . "' ";

}

if (isset($brand) && $brand != '') {

    $queryBuilder['brand'] = $brand;
    $sqlQueryBuilder[] .= "p.brand = '" . $brand . "' ";

}

if (isset($location) && $location != '') {

    $queryBuilder['location'] = $location;
    $sqlQueryBuilder[] .= "(w.location_id = '" . $location . "' OR w.reserved_showroom = '" . $location ."') ";
    $sqlQueryBuilder[] .= 'status < 4 ';

}

foreach ($sqlQueryBuilder as $single) {
    $query .= ' AND ' . $single;
}

if (!empty($queryBuilder)) {

    $currentpage .= '?' . http_build_query($queryBuilder);

}


/* hide all reklamace from all other tabs */
if(!empty($type) && $type != 7){
    $query .= ' AND w.status != 7';
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

    $hottubs_query = $mysqli->query("SELECT *, w.id as id, DATE_FORMAT(w.loadingdate, '%d. %m. %Y') as dateformated FROM warehouse w, warehouse_products p WHERE w.product = p.connect_name AND w.customer = 4 AND (w.serial_number LIKE '%$search%') order by w.id desc") or die($mysqli->error);

} else {

    $hottubs_max_query = $mysqli->query("SELECT w.id as id FROM warehouse w, warehouse_products p WHERE w.product = p.connect_name AND w.customer = 4 $query") or die($mysqli->error);
    $max = mysqli_num_rows($hottubs_max_query);

    if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {$od = 1;}

    $s_lol = $od - 1;
    $s_pocet = $s_lol * $perpage;
    $pocet_prispevku = $max;

    $hottubs_query = $mysqli->query("SELECT *, w.id as id, DATE_FORMAT(w.loadingdate, '%d. %m. %Y') as dateformated FROM warehouse w, warehouse_products p WHERE w.product = p.connect_name AND w.customer = 4 $query order by w.id desc limit " . $s_pocet . "," . $perpage) or die($mysqli->error);

}



function button($data)
{

    global $mysqli;
    global $currentpage;
    global $pageSymbol;
    global $query;

    $param_name = $data['param_name'];

    $this_query = preg_replace('/AND ' . $param_name . ' . .' . $data['param'] . './', '$2', $query);

    if ($data['request_name'] == 'type') {

        $this_query = preg_replace('/ AND .status < 3 OR status > 4. /', '', $this_query);

    }

    if ($data['request_name'] == 'location') {

        $this_query = preg_replace('/ AND .status < 3 OR status > 4. /', ' AND status < 4 ', $this_query);
        $this_query = preg_replace('/ AND .w.location_id = ... OR w.reserved_showroom = ..... /', '', $this_query);

    }

    $thisPage = preg_replace('/&?' . $data['request_name'] . '=[^&]*/', '', $currentpage);

    if ($thisPage == 'pergoly') { $pageSymbol = '?'; } elseif ($thisPage != 'pergoly?') { $pageSymbol = '&'; }

    if (isset($data['param']) && $data['param'] != '' && $data['param'] == $data['current_param'] && isset($data['current_param'])) {$button = 'btn-primary';} else { $button = 'btn-white';}

    $max_query = $mysqli->query("SELECT w.id as id FROM warehouse w, warehouse_products p WHERE w.product = p.connect_name AND w.customer = 4 AND $param_name = '" . $data['current_param'] . "' $this_query") or die($mysqli->error);
    $max = mysqli_num_rows($max_query);

    if($data['request_name'] == 'type' && $data['current_param'] == 7){ $max = 'unknown'; }

    if ($param_name != 'w.product' || $max != 0) {

        $button = '<a href="' . $thisPage . $pageSymbol . $data['url'] . '" style="padding: 5px 11px !important;" class="btn ' . $button . '">
						' . $data['name'] . ' (' . $max . ')</a>';

        return $button;

    }
}

if (isset($search) && $search != "") { ?>

            <div class="row">
                <div class="col-md-8 col-sm-7">
                    <h2>Na hledaný výraz <i><u>"<?= $search ?>"</u></i> odpovídají tyto pergoly:</h2>
                </div>

                <div class="col-md-4 col-sm-5">

                    <form method="get" role="form">

                        <div class="form-group">
                            <div
                                style="margin-bottom: 12px; width: 260px; float:left; margin-left: 10px;margin-right: 4px;">
                                <input id="cheart" value="<?= $search ?>" type="text" name="q"
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

    $servismaxquery = $mysqli->query('SELECT COUNT(*) AS NumberOfOrders FROM warehouse WHERE customer = 4 AND status <> 4') or die($mysqli->error);
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


                    <a href="pridat-pergolu" style=" margin-right: 14px;"
                        class="btn btn-default btn-icon icon-left btn-lg">
                        <i class="entypo-plus"></i>
                        Přidat pergolu
                    </a>

                </div>
                <?php } ?>

            </div>

            <div class="col-md-12 well"
                style="border-color: #ebebeb; background-color: #fbfbfb; padding: 6px; margin-bottom: 12px;">
                <div class="row">
                    <div class="col-md-9" style="text-align: left;">


                        <div class="btn-group">

                            <a href="<?php $thisPage = preg_replace('/&?brand=[^&]*/', '', $currentpage);
                            echo $thisPage; ?>" style="padding: 5px 11px !important;"
                               class="btn <?php if (!isset($brand)) {echo 'btn-primary';} else {echo 'btn-white';} ?>">
                                Vše</a><?php

                            $button['param'] = '';
                            if (isset($brand)) {$button['param'] = $brand;}
                            $button['param_name'] = 'p.brand';
                            $button['request_name'] = 'brand';

                            $brands_query = $mysqli->query("SELECT brand FROM warehouse_products WHERE active = 'yes' AND brand != '' AND customer = 4 GROUP BY brand");
                            while ($singleBrand = mysqli_fetch_array($brands_query)) {

                                $button['url'] = 'brand=' . $singleBrand['brand'];
                                $button['name'] = ucfirst($singleBrand['brand']);
                                $button['current_param'] = $singleBrand['brand'];

                                echo button($button);

                            }

                            ?>
                        </div>

                        <hr>



                        <div class="btn-group">

                            <a href="<?php $thisPage = preg_replace('/&?category=[^&]*/', '', $currentpage);
    echo $thisPage; ?>" style="padding: 5px 11px !important;"
                                class="btn <?php if (!isset($category)) {echo 'btn-primary';} else {echo 'btn-white';} ?>">
                                Vše</a><?php

    $button['param'] = '';
    if (isset($category)) {$button['param'] = $category;}
    $button['param_name'] = 'w.product';
    $button['request_name'] = 'category';

    $hottubs_products_query = $mysqli->query("SELECT * FROM warehouse_products WHERE active = 'yes' AND customer = 4 ORDER BY rank");

    while ($hottub_products = mysqli_fetch_array($hottubs_products_query)) {

        $button['url'] = 'category=' . $hottub_products['connect_name'];
        $button['name'] = ucfirst($hottub_products['fullname']);
        $button['current_param'] = $hottub_products['connect_name'];

        echo button($button);

    }

    ?>
                        </div>
                    </div>


                    <div class="col-sm-3" style=" text-align: right; float: right;">

                        <div class="btn-group">
                            <a href="<?php $thisPage = preg_replace('/&?customer=[^&]*/', '', $currentpage);
    echo $thisPage; ?>" style="padding: 5px 11px !important;"
                                class="btn <?php if (!isset($customer)) {echo 'btn-primary';} else {echo 'btn-white';} ?>">
                                Vše</a><?php

    $this_query = preg_replace("/AND w.demand_id > '0'/", '$2', $query);

    $thisPage = preg_replace('/&?customer=[^&]*/', '', $currentpage);
    if ($thisPage == 'viripergolyvky') {$pageSymbol = '?';} elseif ($thisPage != 'pergoly?') {$pageSymbol = '&';}

    $max_query = $mysqli->query("SELECT w.id as id FROM warehouse w, warehouse_products p WHERE w.product = p.connect_name AND w.customer = 4 AND demand_id = 0 AND w.reserved_showroom = 0 AND w.status != 7 $this_query") or die($mysqli->error);
    $max = mysqli_num_rows($max_query);



    ?>

                            <a href="<?= $thisPage . $pageSymbol . 'customer=0' ?>"
                                style="padding: 5px 11px !important;"
                                class="btn <?php if (isset($customer) && $customer == 0) {echo 'btn-primary';} else {echo 'btn-white';} ?>">
                                Volné (<?= $max ?>)
                                </label></a>


                            <?php


//    $this_query = preg_replace("/AND w.reserved_showroom <> '0'/", '$2', $query);

    $max_query = $mysqli->query("SELECT w.id as id FROM warehouse w, warehouse_products p WHERE w.product = p.connect_name AND w.customer = 4 AND w.reserved_showroom <> '0' $this_query") or die($mysqli->error);
    $max = mysqli_num_rows($max_query);

    ?>

                            <a href="<?= $thisPage . $pageSymbol . 'customer=3' ?>"
                                style="padding: 5px 11px !important;"
                                class="btn <?php if (isset($customer) && $customer == 3) {echo 'btn-primary';} else {echo 'btn-white';} ?>">
                                Showroom (<?= $max ?>)
                                </label></a>


                            <?php

    $this_query = preg_replace("/AND w.demand_id = '0'/", '$2', $query);

    $max_query = $mysqli->query("SELECT w.id as id FROM warehouse w, warehouse_products p WHERE w.product = p.connect_name AND w.customer = 4 AND demand_id > 0 AND w.reserved_showroom = 0 $this_query") or die($mysqli->error);
    $max = mysqli_num_rows($max_query);

    ?>

                            <a href="<?= $thisPage . $pageSymbol . 'customer=4' ?>"
                                style="padding: 5px 11px !important;"
                                class="btn <?php if (isset($customer) && $customer == 4) {echo 'btn-primary';} else {echo 'btn-white';} ?>">
                                Prodané (<?= $max ?>)
                                </label></a>
                        </div>
                    </div>


                </div>
                <hr>

                <!-- Pager for search results -->
                <div class="row">
                    <div class="col-md-12">

                        <div class="btn-group" style="text-align: left;">

                            <div class="btn-group">
                                <a href="<?php $thisPage = preg_replace('/&?type=[^&]*/', '', $currentpage);
    echo $thisPage; ?>" style="padding: 5px 11px !important;"
                                    class="btn <?php if (!isset($type)) {echo 'btn-primary';} else {echo 'btn-white';} ?>">
                                    Vše</a><?php

    $allStatus = array(0 => 'Ve výrobě', 1 => 'Na cestě', 2 => 'Na skladě', 3 => 'Na showroomu', 6 => 'Uskladněno', 7 => 'Reklamace', 4 => 'Expedované');

    $button['param'] = '';
    if (isset($type)) {$button['param'] = $type;}
    $button['param_name'] = 'w.status';
    $button['request_name'] = 'type';

    foreach ($allStatus as $singleStatus => $value) {

        $button['url'] = 'type=' . $singleStatus;
        $button['name'] = $value;
        $button['current_param'] = $singleStatus;

        echo button($button);

    } ?>
                            </div>

                        </div>

                        <div style="float: right">

                            <span
                                style=" border-right: 1px solid #cccccc; padding-left: 9px; margin-right: 12px;"></span>

                            <div class="btn-group">

                                <a href="<?php $thisPage = preg_replace('/&?location=[^&]*/', '', $currentpage);
    echo $thisPage; ?>" style="padding: 5px 11px !important;"
                                    class="btn <?php if (!isset($location)) {echo 'btn-primary';} else {echo 'btn-white';} ?>">
                                    Vše</a><?php

    $location_query = $mysqli->query("SELECT * FROM shops_locations ORDER BY type") or die($mysqli->error);
    while ($single_location = mysqli_fetch_array($location_query)) {

        $button['param'] = '';
        if (isset($location)) {$button['param'] = $location;}
        $button['param_name'] = 'w.location_id';
        $button['request_name'] = 'location';

        $button['url'] = 'location=' . $single_location['id'];
        $button['name'] = $single_location['name'];
        $button['current_param'] = $single_location['id'];

        echo button($button);

    } ?>
                            </div>
                        </div>
                    </div>

                </div><!-- Footer -->
            </div>
            <?php }


if(!empty($_REQUEST['special'])){

    // 3 = microsilk
    // 8 = inclear
    $spec_id = $_REQUEST['special'];

    $pocet_prispevku = 0;

    $hottubs_query = $mysqli->query("SELECT *, w.id as id, DATE_FORMAT(w.loadingdate, '%d. %m. %Y') as dateformated FROM warehouse w, warehouse_products p, warehouse_specs_bridge b WHERE w.product = p.connect_name AND w.customer = 4 AND b.client_id = w.id AND b.specs_id = '".$spec_id."' AND b.value = 'Ano' AND w.status != 4 GROUP BY w.id order by w.id desc") or die($mysqli->error);

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
                    <a href="zobrazit-pergolu?id=<?= $hottub['id'] ?>"><?= $name ?> | <?= $hottub['brand'] . ' ' . ucfirst($hottub['fullname']) ?></a>
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

                        <form role="form" method="post" name="myform" action="pergoly?action=demandchange&id=<?= $hottub['id'] ?>">

                            <div class="col-sm-6" style="width: 260px;">
                                <?php
                                $demandsq = $mysqli->query("SELECT user_name, id FROM demands WHERE customer = 4 AND product = '" . $hottub['product'] . "' and status <> 5 and status <> 6") or die($mysqli->error);

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

                        <a href="zobrazit-pergolu?id=<?= $hottub['id'] ?>" class="btn btn-default btn-sm btn-icon icon-left">
                            <i class="entypo-search"></i>
                            Zobrazit
                        </a>
                        <span style=" border-right: 1px solid #cccccc; margin: 0 10px 0 5px;"></span>
                        <a href="upravit-pergolu?id=<?= $hottub['id'] ?>" class="btn btn-primary btn-sm btn-icon icon-left">
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

                    <div class="show-specs btn btn-white btn-sm" style="cursor: pointer;"><i class="entypo-search"></i> zobrazit specifikace u pergoly</div>
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
                            <?php if (isset($hottub['customer']) && $hottub['customer'] == 4) {
                                $oldrank = '';
                                $i = 0;
                                $specsquery = $mysqli->query('SELECT * FROM specs WHERE product = 4 AND supplier = 1 order by rank asc') or die($mysqli->error);
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
                                            ?><i style="color: #d42020;font-size: 16px; margin-left: 1px;margin-top: -3px;position: absolute;" class="entypo-attention" data-toggle="tooltip" data-placement="top" title="" data-original-title="Specifikace u pergoly neodpovídá zvolené specifikaci u poptávky."></i><?php } ?>
                                    </td>

                                    <?php

                                    if ($i % 4 == 0) {echo '</tr><tr>';}

                                    $oldrank = $specs['bg_colour'];
                                }}?>
                        </table>

                    <?php } else { ?>

                        <table style="width: 100%; float: left; margin-top: 10px;">
                            <?php if (isset($hottub['customer']) && $hottub['customer'] == 4) {
                                $oldrank = '';
                                $i = 0;
                                $specsquery = $mysqli->query('SELECT * FROM specs WHERE product = 4 AND supplier = 1 order by rank asc') or die($mysqli->error);
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
                                neodpovídá žádná pergola.</a></span>
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
$total_time = round(($finish - $start), 4);

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

