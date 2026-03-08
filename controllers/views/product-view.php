<?php

$warehouse = mysqli_fetch_array($warehouseQuery);

if (isset($warehouse['customer']) && $warehouse['customer'] == 0) {

    $sauna = 'yes';
    $saunaid = $warehouse['id'];

    if ($warehouse['serial_number'] != "") {$name = $warehouse['serial_number'];} else { $name = '#' . $warehouse['id'];}

    $name_title = $warehouse['brand'] . ' ' . ucfirst($warehouse['fullname']);
    ?>

    <div class="member-entry" style="margin: 0;">

        <a class="member-img" style="width: 6%;">
            <img src="https://www.wellnesstrade.cz/admin/data/images/customer/<?= $warehouse['product'] ?>.png" width="90px" class="img-rounded" />
            <i class="entypo-forward"></i>
        </a>
        <div class="member-details">
            <h4 style="float: left;">

                <?php
                if ($warehouse['demand_id'] != 0) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-red btn-sm">Prodaná</button>';}

                if (isset($warehouse['status']) && $warehouse['status'] == 0) {

                    echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-orange btn-sm">Ve výrobě</button><button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-primary btn-sm">očekávané naskladnění <strong><u>' . $warehouse['dateformated'] . '</u></strong></button>';

                } elseif (isset($warehouse['status']) && $warehouse['status'] == 1) {

                    echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-blue btn-sm">Na cestě</button><button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-primary btn-sm">očekávané naskladnění <strong><u>' . $warehouse['dateformated'] . '</u></strong></button>';

                } elseif (isset($warehouse['status']) && $warehouse['status'] == 2) {

                    echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-green btn-sm">Na skladě</button>';

                } elseif (isset($warehouse['status']) && $warehouse['status'] == 3) {

                    echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-brown btn-sm">Na showroomu</button>';

                } elseif (isset($warehouse['status']) && $warehouse['status'] == 4) {

                    echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-purple btn-sm">Expedovaná</button>';

                } elseif (isset($warehouse['status']) && $warehouse['status'] == 6) {

                    echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-info btn-sm">Uskladněná</button>';

                } elseif (isset($warehouse['status']) && $warehouse['status'] == 7) {

                    echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-info btn-sm">Reklamace</button>';

                }

                if (isset($warehouse['status']) && $warehouse['status'] != 4) {

                    $location_query = $mysqli->query("SELECT name FROM shops_locations WHERE id = '" . $warehouse['location_id'] . "'") or die($mysqli->error);
                    $location = mysqli_fetch_array($location_query);

                    echo '<button style="margin-right: 4px; margin-top: -3px; background-color: #338fd8; border-color: #338fd8;" type="button" class="btn btn-brown btn-sm">' . $location['name'] . '</button>';

                }

                ?> #<?= $warehouse['id'] ?> | <?= $warehouse['code'] ?>: <?= $warehouse['brand'] . ' ' . ucfirst($warehouse['fullname']) ?>
            </h4>
            <?php if ($access_edit) { ?><div style="float:right;margin-top: 0px;padding-right: 0px;">
                <a href="/admin/pages/warehouse/upravit-saunu?id=<?= $warehouse['id'] ?>&redirect=<?= $getclient['id'] ?>" class="btn btn-primary btn-sm btn-icon icon-left">
                    <i class="entypo-pencil"></i>
                    Upravit
                </a>
                <a href="/admin/pages/warehouse/sauny?action=demandnull&id=<?= $warehouse['id'] ?>&redirect=<?= $getclient['id'] ?>" class="btn btn-danger btn-sm btn-icon icon-left">
                    <i class="entypo-cancel"></i>
                    Odebrat od poptávky
                </a>
                </div><?php } ?>
            <div class="clear"></div>
            <!-- Details with Icons -->		<div class="row info-list">
                <div class="col-sm-4" style="margin-top: 8px;">

                    <i class="entypo-right-open-mini"></i>
                    Výrobní číslo: <strong><?= $warehouse['serial_number'] ?></strong>
                </div>
                <div class="col-sm-4" style="margin-top: 8px;">

                    <i class="entypo-right-open-mini"></i>
                    Sauna <strong><?php if (isset($warehouse['ram']) && $warehouse['ram'] == 1) {echo 's rámem';} else {echo 'bez rámu';}?></strong>
                </div>
                <br><br>

                <?php if (isset($warehouse['customer']) && $warehouse['customer'] == 0 && $warehouse['demand_id'] != 0) {
                    $specsquery = $mysqli->query("SELECT s.reserved, s.quantity, p.productname, p.category FROM demands_sauna_specs s, products p WHERE s.demand_id = '".$getclient['id']."' and p.id = s.product_id") or die($mysqli->error);
                    if (mysqli_num_rows($specsquery) > 0) {
                        while ($specs = mysqli_fetch_array($specsquery)) {

                            $catq = $mysqli->query("SELECT name FROM products_cats WHERE seoslug = '" . $specs['category'] . "'") or die($mysqli->error);
                            $cat = mysqli_fetch_assoc($catq);

                            ?>

                            <div class="col-sm-4" style="margin-bottom: 6px; line-height: 26px;">

                                <i class="entypo-right-open-mini"></i>
                                <?= $cat['name'] ?>: <strong style=" margin-right: 8px;"><?= $specs['productname'] ?></strong><?php if ($specs['reserved'] >= 1) {echo '<small style="margin-right: -3px;">' . $specs['reserved'] . 'x</small><i data-toggle="tooltip" data-placement="top" title="" data-original-title="Rezervováno" style="color: #00a651; font-size: 18px;" class="entypo-check"></i>';}if ($specs['quantity'] > $specs['reserved']) {$rozdil = $specs['quantity'] - $specs['reserved'];
                                    echo '<small style="margin-right: -1px;">' . $rozdil . 'x</small><i style="color: #d42020;font-size: 16px;" class="entypo-cancel-circled" data-toggle="tooltip" data-placement="top" title="" data-original-title="Chybí"></i>';}?>
                            </div>



                        <?php }} else { ?>
                        <div class="col-sm-12" style="margin-bottom: 6px; line-height: 26px;">

                            <i class="entypo-right-open-mini"></i>Sauna je přiřazená k poptávce u které zatím nejsou přidělené žádné specifikace.

                        </div>

                    <?php }} else { ?>
                    <div class="col-sm-12" style="margin-bottom: 6px; line-height: 26px;">

                        <i class="entypo-right-open-mini"></i>Sauna je zatím volná, a proto k ní nejsou přidělené žádné specifikace.

                    </div>
                <?php }if (isset($warehouse['description']) && $warehouse['description'] != "") { ?>
                    <div class="col-sm-12" style="margin-bottom: 6px; line-height: 26px;">

                        <i class="entypo-right-open-mini"></i><?= $warehouse['description'] ?>

                    </div>
                <?php } ?>
                <?php if (isset($access_edit) && $access_edit) { ?>
                    <hr style="float: left;width: 100%;margin-top: 10px; margin-bottom: 3px;">
                    <div class="col-sm-3" style="margin-top: 8px;">

                        <i class="entypo-right-open-mini"></i>
                        Prodejní cena: <strong><?= number_format($warehouse['sale_price'], 0, ',', ' ') ?>,- Kč</strong>
                    </div>
                    <div class="col-sm-3" style="margin-top: 8px;">

                        <i class="entypo-right-open-mini"></i>
                        Nákupní cena: <strong><?= number_format($warehouse['purchase_price'], 0, ',', ' ') ?>,- Kč</strong>
                    </div>
                <?php } ?>
            </div>
        </div>

    </div>


    <?php

} else {

    $virivka = 'yes';
    $virivkaid = $warehouse['id'];

    if ($warehouse['serial_number'] != "") {$name = $warehouse['serial_number'];} else { $name = '#' . $warehouse['id'];}

    $name_title = $warehouse['brand'] . ' ' . ucfirst($warehouse['fullname']);
    ?>

    <div class="member-entry" style="margin: 0;">
        <a class="member-img" style="width: 6%;">
            <img src="https://www.wellnesstrade.cz/admin/data/images/customer/<?= $warehouse['product'] ?>.png" width="90px" class="img-rounded" />
            <i class="entypo-forward"></i>
        </a>

        <div class="member-details" style="width: 94%;">
            <h4 style="float: left;">

                <?php
                if ($warehouse['demand_id'] != 0) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-red btn-sm">Prodaná</button>';}
                if (isset($warehouse['status']) && $warehouse['status'] == 0) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-orange btn-sm">Ve výrobě</button>';} elseif (isset($warehouse['status']) && $warehouse['status'] == 1) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-blue btn-sm">Na cestě</button><button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-primary btn-sm">očekávané naskladnění <strong><u>' . $warehouse['dateformated'] . '</u></strong></button>';} elseif (isset($warehouse['status']) && $warehouse['status'] == 2) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-green btn-sm">Na skladě</button>';} elseif (isset($warehouse['status']) && $warehouse['status'] == 3) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-brown btn-sm">Na showroomu</button>';} elseif (isset($warehouse['status']) && $warehouse['status'] == 4) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-purple btn-sm">Expedovaná</button>';
                } elseif (isset($warehouse['status']) && $warehouse['status'] == 6) {
                    echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-info btn-sm">Uskladněná</button>';
                } elseif (isset($warehouse['status']) && $warehouse['status'] == 7) {
                    echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-info btn-sm">Reklamace</button>';
                }
                if (isset($warehouse['status']) && $warehouse['status'] != 4) {

                    $location_query = $mysqli->query("SELECT name FROM shops_locations WHERE id = '" . $warehouse['location_id'] . "'") or die($mysqli->error);
                    $location = mysqli_fetch_array($location_query);

                    echo '<button style="margin-right: 4px; margin-top: -3px; background-color: #338fd8; border-color: #338fd8;" type="button" class="btn btn-brown btn-sm">' . $location['name'] . '</button>';

                }
                ?><a href="/admin/pages/warehouse/zobrazit-virivku?id=<?= $warehouse['id'] ?>"> <?= $name ?> | <?= $warehouse['brand'] . ' ' . ucfirst($warehouse['fullname']) ?></a>
            </h4>

            <?php if ($access_edit) { ?>	<div style="float:right;margin-top: 0px;padding-right: 0px;">
                <a href="/admin/pages/warehouse/zobrazit-virivku?id=<?= $warehouse['id'] ?>" class="btn btn-default btn-sm btn-icon icon-left">
                    <i class="entypo-search"></i>
                    Zobrazit
                </a>
                <a href="/admin/pages/warehouse/upravit-virivku?id=<?= $warehouse['id'] ?>&redirect=<?= $getclient['id'] ?>" class="btn btn-primary btn-sm btn-icon icon-left">
                    <i class="entypo-pencil"></i>
                    Upravit
                </a>
                <a href="/admin/pages/warehouse/virivky?action=demandnull&id=<?= $warehouse['id'] ?>&redirect=<?= $getclient['id'] ?>" class="btn btn-danger btn-sm btn-icon icon-left">
                    <i class="entypo-cancel"></i>
                    Odebrat od poptávky
                </a>
                </div><?php } ?>
            <div class="clear"></div>
        </div>

        <div class="member-details" style="width: 100%;">


            <?php if ($warehouse['demand_id'] != 0) {

            $now = date("Y-m-d", strtotime("now"));

            if($warehouse['loadingdate'] != '0000-00-00'){

                $dateadd = date("Y-m-d", strtotime($warehouse['loadingdate']));

                $delivery_date = date("d. m. y", strtotime($warehouse['loadingdate']));

                $date1 = new DateTime($dateadd);
                $date2 = new DateTime($now);
                $interval = $date1->diff($date2);
                $nummero = $interval->days;

                ?>
                <div class="col-sm-12" style="padding-bottom: 10px; border-bottom: 1px solid #eee; color: #000; margin-bottom: 4px;">Termín doručení je <strong><?= $delivery_date ?> (<?= $nummero ?> dnů)</strong>.</div>
                <?php


            }elseif ($warehouse['created_date'] != '0000-00-00' && $warehouse['status'] == 0) {

                $dateadd = date("Y-m-d", strtotime("+77 days", strtotime($warehouse['created_date'])));
                $dateadd2 = date("Y-m-d", strtotime("+42 days", strtotime($warehouse['created_date'])));
                $estimated = date("d. m. y", strtotime("+77 days", strtotime($warehouse['created_date'])));
                $correction = date("d. m. y", strtotime("+42 days", strtotime($warehouse['created_date'])));

                $date1 = new DateTime($dateadd);
                $date2 = new DateTime($now);
                $interval = $date1->diff($date2);
                $nummero = $interval->days;

                $date3 = new DateTime($dateadd2);
                $interval2 = $date3->diff($date2);
                $nummero2 = $interval2->days;

                ?>
                <div class="col-sm-4" style="padding-bottom: 10px; border-bottom: 1px solid #eee; color: #000; margin-bottom: 4px;">Orientační termín doručení je <strong><?= $estimated ?> (<?= $nummero ?> dnů)</strong>. <br>Orientační termín bude upřesněn do <strong><?= $nummero2 ?> dnů</strong>.</div>

            <?php }
            ?>
            <div class="row info-list" style="margin: 10px 0 0;">

                <?php if (isset($warehouse['customer']) && $warehouse['customer'] == 1) {



                $get_provedeni = $mysqli->query("SELECT value FROM warehouse_specs_bridge WHERE client_id = '" . $warehouse['id'] . "' AND specs_id = 5") or die($mysqli->error);
                $provedeni = mysqli_fetch_array($get_provedeni);

                $get_ids = $mysqli->query("SELECT w.id as id, w.name as name FROM warehouse_products_types w, warehouse_products p WHERE w.warehouse_product_id = p.id AND p.fullname = '" . $warehouse['fullname'] . "' AND w.name = '" . $provedeni['value'] . "'") or die($mysqli->error);

                if (mysqli_num_rows($get_ids) > 0) {

                $get_id = mysqli_fetch_array($get_ids);

                $specsquery = $mysqli->query("SELECT s.id, s.name, w.value as warehouse_value, d.value as demand_value, s.demand_category, s.technical FROM specs s INNER JOIN warehouse_products_types_specs wh ON wh.spec_id = s.id AND wh.type_id = '" . $get_id['id'] . "' AND s.warehouse_spec = 1 LEFT JOIN warehouse_specs_bridge w ON w.specs_id = s.id AND w.client_id = '" . $warehouse['id'] . "' LEFT JOIN demands_specs_bridge d ON d.specs_id = s.id AND d.client_id = '".$getclient['id']."' GROUP BY s.id order by s.demand_category asc, s.name asc") or die($mysqli->error);

                $category_warehouse_done = '';
                while ($specs = mysqli_fetch_array($specsquery)) {

                if (isset($specs['demand_category']) && $specs['demand_category'] == 1 && $category_warehouse_done != $specs['demand_category']) {

                ?>
                <div class="col-sm-3" style="padding: 0 10px 0; border-right: 1px dashed #cccccc;">
                    <h4 style="font-size: 14px; margin-bottom: 13px; margin-left: 0; text-align: center; margin-top: 0; border-bottom: 1px solid #dedede; padding-bottom: 13px;">Obecné hlavní</h4>
                    <div class="col-sm-12" style="margin-bottom: 6px; padding: 0;">
                        <i class="entypo-right-open-mini"></i>
                        Typ vířivky: <strong><?php
                            echo $warehouse['product']; ?></strong>

                    </div>
                    <div class="col-sm-12" style="margin-bottom: 6px; padding: 0;">
                        <i class="entypo-right-open-mini"></i>
                        Provedení: <strong><?= $provedeni['value'] ?></strong>
                    </div>
                    <?php

                    $category_warehouse_done = $specs['demand_category'];
                    } elseif (isset($specs['demand_category']) && $specs['demand_category'] == 2 && $category_warehouse_done != $specs['demand_category']) {

                    ?>
                </div>
            <div class="col-sm-3" style="padding: 0 10px 0;"">
                <h4 style="font-size: 14px; margin-bottom: 13px; margin-left: 0; text-align: center; margin-top: 0; border-bottom: 1px solid #dedede; padding-bottom: 13px;">Příplatková výbava</h4>
            <?php

            $category_warehouse_done = $specs['demand_category'];
            } elseif (isset($specs['demand_category']) && $specs['demand_category'] == 3 && $category_warehouse_done != $specs['demand_category']) {
            ?>
            </div>
        <div class="col-sm-6" style="padding: 0 10px 0; border-left: 1px dashed #cccccc;">
        <h4 style="font-size: 14px; margin-bottom: 13px; margin-left: 0; text-align: center; margin-top: 0; border-bottom: 1px solid #dedede; padding-bottom: 13px;">Technické detaily</h4>
        <?php
        $category_warehouse_done = $specs['demand_category'];
        }

        ?>

        <div <?php if ($category_warehouse_done == 3) { ?>class="col-sm-6"<?php } else { ?>class="col-sm-12"<?php } ?> style="margin-bottom: 6px; padding: 0;">

            <i class="entypo-right-open-mini"></i>
            <?php if ($specs['id'] != 7) {echo $specs['name'];?>: <?php } ?><strong><?= $specs['warehouse_value'] ?></strong>
        </div>


        <?php } ?>
        </div>

        <?php

        }

        }

        /* if($access_edit){ ?>
        <hr style="float: left;width: 100%;margin-top: 18px; margin-bottom: 3px;">
        <div class="col-sm-3" style="margin-top: 8px;">

        <i class="entypo-right-open-mini"></i>
        Prodejní cena: <strong><?= number_format($warehouse['sale_price'], 0, ',', ' ') ?>,- Kč</strong>
        </div>
        <div class="col-sm-3" style="margin-top: 8px;">

        <i class="entypo-right-open-mini"></i>
        Nákupní cena: <strong><?= number_format($warehouse['purchase_price'], 0, ',', ' ') ?>,- Kč</strong>
        </div>
        <div class="col-sm-3" style="margin-top: 8px;">

        <i class="entypo-right-open-mini"></i>
        Reálně inkasováno: <strong><?= number_format($warehouse['real_price'], 0, ',', ' ') ?>,- Kč</strong>
        </div>    <?php }*/?>

            <?php if ($warehouse['description'] != "") { ?>

                <hr style="float: left;width: 100%;margin-top: 18px; margin-bottom: 3px;">
                <div class="col-sm-8" style="margin-top: 8px;">
                    <i class="entypo-info"></i>
                    <?= $warehouse['description'] ?>
                </div>

            <?php } ?>

        </div>
        <?php
        } else { ?>
            adajjsjd
            <div class="row info-list">
                <?php if (isset($warehouse['customer']) && $warehouse['customer'] == 1) {

                    $specsquery = $mysqli->query('SELECT id, name FROM specs WHERE product = 1 order by type desc, id asc') or die($mysqli->error);
                    while ($specs = mysqli_fetch_array($specsquery)) {
                        $paramsquery = $mysqli->query('SELECT value FROM warehouse_specs_bridge WHERE specs_id = "' . $specs['id'] . '" AND client_id = "' . $warehouse['id'] . '"') or die($mysqli->error);
                        $params = mysqli_fetch_array($paramsquery);

                        ?>

                        <div class="col-sm-3" style="margin-top: 8px;">

                            <i class="entypo-right-open-mini"></i>
                            <?php if ($specs['id'] != 7) {echo $specs['name'];?>: <?php } ?><strong><?= $params['value'] ?></strong>
                        </div>



                        <?php

                    }

                }?>


            </div>

            <?php if ($access_edit) { ?>
                <hr style="float: left;width: 100%;margin-top: 18px; margin-bottom: 3px;">
                <div class="col-sm-3" style="margin-top: 8px;">

                    <i class="entypo-right-open-mini"></i>
                    Prodejní cena: <strong><?= number_format($warehouse['sale_price'], 0, ',', ' ') ?>,- Kč</strong>
                </div>
                <div class="col-sm-3" style="margin-top: 8px;">

                    <i class="entypo-right-open-mini"></i>
                    Nákupní cena: <strong><?= number_format($warehouse['purchase_price'], 0, ',', ' ') ?>,- Kč</strong>
                </div>
                <div class="col-sm-3" style="margin-top: 8px;">

                    <i class="entypo-right-open-mini"></i>
                    Reálně inkasováno: <strong><?= number_format($warehouse['real_price'], 0, ',', ' ') ?>,- Kč</strong>
                </div>

            <?php } ?>

        <?php } ?>
    </div>
    </div>
    <?php

}?>