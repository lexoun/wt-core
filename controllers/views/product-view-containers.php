<?php
$search_container_query = $mysqli->query("SELECT *, w.id as id, DATE_FORMAT(w.date_created, '%d. %m. %Y') as dateformated FROM containers_products w, warehouse_products p WHERE w.product = p.connect_name AND w.demand_id = '" . $getclient['id'] . "'") or die($mysqli->error);
if (mysqli_num_rows($search_container_query) > 0) {

    while ($warehouse = mysqli_fetch_array($search_container_query)) {
        if (isset($warehouse['customer']) && $warehouse['customer'] == 0) {

            $sauna = 'yes';
            $saunaid = $warehouse['id'];

            if ($warehouse['serial_number'] != "") {$name = $warehouse['serial_number'];} else { $name = '#' . $warehouse['id'];}
            ?>

            <div class="member-entry" style="margin: 0;">

                <a class="member-img" style="width: 6%;">
                    <img src="https://www.wellnesstrade.cz/admin/data/images/customer/? echo $warehouse['product'];?>.png" width="90px" class="img-rounded" />
                    <i class="entypo-forward"></i>
                </a>
                <div class="member-details">
                    <h4 style="float: left;">

                        <?php
                        if ($warehouse['demand_id'] != 0) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-red btn-sm">Rezervovaná</button>';}
                        if (isset($warehouse['status']) && $warehouse['status'] == 0) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-orange btn-sm">Zadána do výroby</button>';} elseif (isset($warehouse['status']) && $warehouse['status'] == 1) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-blue btn-sm">Na cestě</button><button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-primary btn-sm">očekávané naskladnění <strong><u>' . $warehouse['dateformated'] . '</u></strong></button>';} elseif (isset($warehouse['status']) && $warehouse['status'] == 2) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-green btn-sm">Na skladě</button>';} elseif (isset($warehouse['status']) && $warehouse['status'] == 4) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-purple btn-sm">Expedovaná</button>';} elseif (isset($warehouse['status']) && $warehouse['status'] == 3) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-brown btn-sm">Na showroomu</button>';} elseif (isset($warehouse['status']) && $warehouse['status'] == 5) {echo '<button style="margin-right: 4px; margin-top: -3px;" type="button" class="btn btn-brown btn-sm">Na showroomu - Brno</button>';}?> #<?= $warehouse['id'] ?> | <?= $warehouse['code'] ?>: <?= $warehouse['brand'] . ' ' . ucfirst($warehouse['fullname']) ?>
                    </h4>
                    <?php if ($access_edit) { ?><div style="float:right;margin-top: 0px;padding-right: 0px;">
                        <a href="../admin/upravit-saunu?id=<?= $warehouse['id'] ?>&redirect=<?= $getclient['id'] ?>" class="btn btn-primary btn-sm btn-icon icon-left">
                            <i class="entypo-pencil"></i>
                            Upravit
                        </a>
                        <a href="../admin/sauny?action=demandnull&id=<?= $warehouse['id'] ?>&redirect=<?= $getclient['id'] ?>" class="btn btn-danger btn-sm btn-icon icon-left">
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
                            $specsquery = $mysqli->query("SELECT s.reserved, s.quantity, p.productname, p.category FROM demands_sauna_specs s, products p WHERE s.demand_id = '" . $getclient['id'] . "' and p.id = s.product_id") or die($mysqli->error);
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
                        <?php if ($access_edit) { ?>
                            <hr style="float: left;width: 100%;margin-top: 10px; margin-bottom: 3px;">
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
                    </div>
                </div>

            </div>


        <?php } else {
            $virivka = 'yes';
            $virivkaid = $warehouse['id'];

            if (!empty($warehouse['serial_number'])) {$name = $warehouse['serial_number'];} else { $name = '#' . $warehouse['id'];}
            ?>

            <div class="member-entry" style="margin: 0;">
                <a class="member-img" style="width: 6%;">
                    <img src="https://www.wellnesstrade.cz/admin/data/images/customer/<?= $warehouse['product'] ?>.png" width="90px" class="img-rounded" />
                    <i class="entypo-forward"></i>
                </a>

                <div class="member-details">
                    <h4 style="float: left;">
                        <?= $name ?> | <?= $warehouse['brand'] . ' ' . ucfirst($warehouse['fullname']) ?>
                    </h4>


                    <div class="clear"></div>
                    <?php if ($warehouse['demand_id'] != 0) { ?>
                        <!-- Details with Icons -->		<div class="row info-list">

                            <?php if (isset($warehouse['customer']) && $warehouse['customer'] == 1) {
                                $specsquery = $mysqli->query("SELECT s.id, s.name, w.value as warehouse_value, d.value as demand_value FROM specs s LEFT JOIN containers_products_specs_bridge w ON w.specs_id = s.id AND w.client_id = '" . $warehouse['id'] . "' LEFT JOIN demands_specs_bridge d ON d.specs_id = s.id AND d.client_id = '" . $getclient['id'] . "' WHERE s.product = 1 AND s.supplier = 1 order by type desc, id asc") or die($mysqli->error);

                                while ($specs = mysqli_fetch_array($specsquery)) {

                                    ?>

                                    <div class="col-sm-3" style="margin-top: 8px;">

                                        <i class="entypo-right-open-mini"></i>
                                        <?php if ($specs['id'] != 7) {echo $specs['name'];?>: <?php } ?><strong><?= $specs['warehouse_value'] ?></strong>
                                        <?php if ($specs['demand_value'] != $specs['warehouse_value']) { ?><i style="color: #d42020;font-size: 16px; margin-left: 1px;margin-top: -3px;position: absolute;" class="entypo-attention" data-toggle="tooltip" data-placement="top" title="" data-original-title="Specifikace u vířivky neodpovídá zvolené specifikaci u poptávky."></i><?php } ?>
                                    </div>


                                <?php }}?>
                        </div>
                    <?php } else { ?>
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



                                <?php }}?>
                        </div>
                    <?php } ?>
                </div></div>
            <?php

        }}
}

?>