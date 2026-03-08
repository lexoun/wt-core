<section id="hide-tab" class="tab profile-feed"></section>

<?php

if ($getclient['status'] == 5 || $getclient['status'] == 8 || $getclient['status'] == 13) {
    ?>

            <section id="service-tab" class="tab profile-feed" style="display: none;">

                <div class="profile-stories">

                    <article class="story" style="margin: 0;">

                        <div class="row">
                            <div class="col-md-9 col-sm-7">
                                <h2 style="margin-top: 22px; margin-bottom: 26px;">Naplánované servisy</h2>
                            </div>

                        </div>

                        <?php

    $servicesQuery = $mysqli->query('SELECT *, DATE_FORMAT(date, "%d. %m. %Y") as dateformated, DATE_FORMAT(date_added, "%d. %m. %Y") as date_added  FROM services WHERE state <> "finished" AND state <> "canceled" AND state <> "warranty" AND clientid = ' . $getclient['id'] . ' ORDER BY id DESC') or die($mysqli->error);

    if (mysqli_num_rows($servicesQuery) > 0) {
        ?>

                        <div id="table-2_wrapper" class="dataTables_wrapper form-inline" role="grid"
                            style="margin-bottom: 30px;">
                            <table class="table table-bordered table-striped datatable dataTable" id="table-2"
                                aria-describedby="table-2_info">
                                <thead>
                                    <tr role="row">
                                        <th class="text-center" style="min-width: 174px;">Servis</th>
                                        <th class="text-center" style="min-width: 90px;">Přidáno</th>
                                        <th class="text-center" style="min-width: 76px;">Produkt</th>
                                        <th class="text-center" >Kategorie</th>
                                        <th class="text-center" style="min-width: 90px;">Datum</th>
                                        <th class="text-center" style="">Informace</th>
                                        <th class="text-center" style="min-width: 220px;">Akce</th>
                                    </tr>
                                </thead>


                                <tbody role="alert" aria-live="polite" aria-relevant="all">

                                    <?php

        while ($service = mysqli_fetch_array($servicesQuery)) {

            $service['showStatus'] = true;
            service($service);
        } ?>
                                </tbody>
                            </table>
                        </div>
                        <?php
} else { ?>

                        <ul class="cbp_tmtimeline">
                            <li style="margin-top: -4px;">

                                <div class="cbp_tmicon">
                                    <i class="entypo-block" style="line-height: 42px !important;"></i>
                                </div>

                                <div class="cbp_tmlabel empty" style="padding-top: 9px;">
                                    <span><a style="font-weight: bold; margin-left: -12px;font-size: 17px;">Klient
                                            nyní nemá žádný naplánovaný servis.</a></span>
                                </div>
                            </li>
                        </ul>

                        <?php } ?>




                        <div class="row" style="margin-top: 14px;">
                            <div class="col-md-9 col-sm-7">
                                <h2 style=" margin-bottom: 26px;">Historie servisů</h2>
                            </div>


                        </div>

                        <?php

    $servicesMaxQuery = $mysqli->query('SELECT id FROM services WHERE state = "finished" OR state = "canceled" OR state = "warranty" AND clientid = ' . $getclient['id'] . ' ') or die($mysqli->error);
    $max = mysqli_num_rows($servicesMaxQuery);

    if ($max > 0) {
        if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {
            $od = 1;
        }
        $perpage = 10;

        $s_lol = $od - 1;
        $s_pocet = $s_lol * $perpage;
        $pocet_prispevku = $max; ?>
                        <div id="table-2_wrapper" class="dataTables_wrapper form-inline" role="grid"
                            style="margin-bottom: 30px;">
                            <table class="table table-bordered table-striped datatable dataTable" id="table-2"
                                aria-describedby="table-2_info">
                                <thead>
                                    <tr role="row">
                                        <th class="text-center" style="min-width: 174px;">Servis</th>
                                        <th class="text-center" style="min-width: 90px;">Přidáno</th>
                                        <th class="text-center" style="min-width: 76px;">Produkt</th>
                                        <th class="text-center" >Kategorie</th>
                                        <th class="text-center" style="min-width: 90px;">Datum</th>
                                        <th class="text-center" style="">Informace</th>
                                        <th class="text-center" style="min-width: 220px;">Akce</th>
                                    </tr>
                                </thead>


                                <tbody role="alert" aria-live="polite" aria-relevant="all">
                                    <?php

        $servicesQuery = $mysqli->query('SELECT *, DATE_FORMAT(date, "%d. %m. %Y") as dateformated, DATE_FORMAT(date_added, "%d. %m. %Y") as date_added FROM services WHERE (state = "finished" OR state = "warranty") AND clientid = ' . $getclient['id'] . ' order by date desc limit ' . $s_pocet . ',' . $perpage) or die($mysqli->error);

        while ($service = mysqli_fetch_array($servicesQuery)) {
            service($service);
        }

        $testnum = $s_pocet + 1 + $perpage;
        if ($od == 1) {
            $tonum = $s_pocet + $perpage;
        } elseif ($testnum > $pocet_prispevku) {
            $tonum = $pocet_prispevku;
        } else {
            $tonum = $s_pocet + 1 + $perpage;
        } ?>
                                </tbody>
                            </table>

                            <div class="row">
                                <div class="col-xs-6 col-left">
                                    <div class="dataTables_info" id="table-2_info">Zobrazuji záznamy
                                        <strong><?= $s_pocet + 1 ?></strong>
                                        až
                                        <strong><?= $tonum ?></strong>
                                        z celkového počtu
                                        <strong><?= $max ?></strong>
                                        záznamů</div>
                                </div>

                                <div class="col-xs-6 col-right">
                                    <div class="dataTables_paginate paging_bootstrap">
                                        <ul class="pagination pagination-sm">
                                            <?php
$currentpage = "historie-servisu";
        include VIEW . "/default/pagination.php"; ?>
                                        </ul>

                                    </div>

                                </div>
                            </div>
                        </div>
                        <?php
} else { ?>
                        <ul class="cbp_tmtimeline">
                            <li style="margin-top: -4px;">

                                <div class="cbp_tmicon">
                                    <i class="entypo-block" style="line-height: 42px !important;"></i>
                                </div>

                                <div class="cbp_tmlabel empty" style="padding-top: 9px;">
                                    <span><a style="font-weight: bold; margin-left: -12px;font-size: 17px;">Klient
                                            nemá žádný provedený servis.</a></span>
                                </div>
                            </li>
                        </ul>
                        <?php } ?>
                    </article>

                </div>
            </section>


            <section id="orders-tab" class="tab profile-feed" style="display:none;">


                <!-- profile stories -->
                <div class="profile-stories">



                    <article class="story" style="margin: 0;">

                        <div class="row">
                            <div class="col-sm-10">

                                <h2 style="margin-top: 22px; margin-bottom: 26px;">Nevyřízené objednávky</h2>
                            </div>


                        </div>
                        <?php
$ordersmaxquery = $mysqli->query('SELECT COUNT(*) AS NumberOfOrders FROM orders WHERE client_id="' . $getclient['id'] . '" AND order_status < 3') or die($mysqli->error);
    $ordersmax = mysqli_fetch_array($ordersmaxquery);
    $max = $ordersmax['NumberOfOrders'];
    if ($max > 0) {
        if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {
            $od = 1;
        }
        $s_lol = $od - 1;
        $s_pocet = $s_lol * 4;
        $pocet_prispevku = $max; ?>
                        <table class="table table-bordered table-striped datatable dataTable">
                            <thead>
                                <tr>
                                    <th width="200px">Objednávka</th>
                                    <th width="120px" class="text-center">Stav</th>
                                    <th width="120px" class="text-center">Obchod</th>
                                    <th style="width: 414px;">Zakoupeno</th>
                                    <th>Doručení</th>
                                    <th class="text-center">Datum</th>
                                    <th class="text-center">Cena celkem</th>
                                    <th width="220px" class="text-center">Akce</th>
                                </tr>
                            </thead>

                            <tbody role="alert" aria-live="polite" aria-relevant="all">
                                <?php

        $ordersquery = $mysqli->query('SELECT *, o.id as id, p.name as pay_method, d.name as ship_method, DATE_FORMAT(order_date, "%d. %m. %Y") as dateformated, DATE_FORMAT(order_date, "%T") as hoursmins FROM orders o, shops_payment_methods p, shops_delivery_methods d WHERE client_id="' . $getclient['id'] . '" AND o.order_shipping_method = d.link_name AND o.payment_method = p.link_name AND order_status < 3 order by order_date desc limit ' . $s_pocet . ',4') or die($mysqli->error);
        $i = $max + 1;
        $pagipage = 'nakupni-historie';

        while ($orders = mysqli_fetch_array($ordersquery)) {
            $i = $i - 1;
            $i = str_pad($i, 3, "0", STR_PAD_LEFT);
            ordersnew($orders, $getclient['secretstring'], $getclient['id']);
        } ?>

                                <?php
include $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/pagination-4.php"; ?>

                            </tbody>

                        </table>

                        <?php
} else { ?>

                        <ul class="cbp_tmtimeline">
                            <li style="margin-top: -4px;">
                                <div class="cbp_tmicon">
                                    <i class="entypo-block" style="line-height: 42px !important;"></i>
                                </div>

                                <div class="cbp_tmlabel empty" style="padding-top: 9px;">
                                    <span><a style="font-weight: bold; margin-left: -12px;font-size: 17px;">Klient
                                            nemá žádný nevyřízený nákup.</a></span>
                                </div>
                            </li>
                        </ul>
                        <?php } ?>
                        <div class="row">
                            <div class="col-sm-10">

                                <h2 style=" margin-bottom: 26px;">Nákupní historie</h2>
                            </div>


                        </div>
                        <?php
$ordersmaxquery = $mysqli->query('SELECT COUNT(*) AS NumberOfOrders FROM orders WHERE client_id="' . $getclient['id'] . '" AND (order_status = 3 OR order_status = 4)') or die($mysqli->error);
    $ordersmax = mysqli_fetch_array($ordersmaxquery);
    $max = $ordersmax['NumberOfOrders'];

    if ($max > 0) {
        if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {
            $od = 1;
        }
        $s_lol = $od - 1;
        $s_pocet = $s_lol * 4;
        $pocet_prispevku = $max; ?>


                        <table class="table table-bordered table-striped datatable dataTable">
                            <thead>
                                <tr>
                                    <th width="200px">Objednávka</th>
                                    <th width="120px" class="text-center">Stav</th>
                                    <th style=" width:300px;">Zakoupeno</th>
                                    <th>Doručení</th>
                                    <th class="text-center">Datum</th>
                                    <th class="text-center">Cena celkem</th>
                                    <th width="200px" class="text-center">Akce</th>
                                </tr>
                            </thead>

                            <tbody role="alert" aria-live="polite" aria-relevant="all">
                                <?php

        $ordersquery = $mysqli->query('SELECT *, DATE_FORMAT(order_date, "%d. %m. %Y") as dateformated, DATE_FORMAT(order_date, "%T") as hoursmins FROM orders WHERE client_id="' . $getclient['id'] . '" AND (order_status = 3 OR order_status = 4) ORDER by order_date desc') or die($mysqli->error);
        $i = $max + 1;
        $pagipage = 'nakupni-historie';

        while ($orders = mysqli_fetch_array($ordersquery)) {
            $i = $i - 1;
            $i = str_pad($i, 3, "0", STR_PAD_LEFT);
            ordersnew($orders, $getclient['secretstring'], $getclient['id']);
        } ?>
                            </tbody>

                        </table>
                        <?php
//include $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/pagination-4.php";
    } else { ?>

                        <ul class="cbp_tmtimeline">
                            <li style="margin-top: -4px;">

                                <div class="cbp_tmicon">
                                    <i class="entypo-block" style="line-height: 42px !important;"></i>
                                </div>

                                <div class="cbp_tmlabel empty" style="padding-top: 9px;">
                                    <span><a style="font-weight: bold; margin-left: -12px;font-size: 17px;">Klient
                                            zatím neprovedl žádný nákup.</a></span>
                                </div>
                            </li>
                        </ul>
                        <?php } ?>

                    </article>

                </div>
            </section>



            <section id="documents-tab" class="tab profile-feed" style="display:none; margin-bottom: 0;">


                <div class="profile-stories">



                    <article class="story" style="margin: 0;">

                        <div class="row" style="margin-top: 20px;">

                            <?php

    $types = array(
        'Kupní smlouva' => 'purchase_contract',
        'Předávací protokol' => 'transfer_protocol',
        'Checklist' => 'checklist',
        'Záloha' => 'invoice',
        'Zúčtovací faktura' => 'clearing_invoice',
    );

    if ($getclient['customer'] == 0) {
        $types = array_merge((array)$types, array('Revize' => 'revision'));
    }

    foreach ($types as $key => $value) {
        ?>
                            <div class="col-sm-2">
                                <h4 style="text-align: center;  margin-top: 10px; margin-bottom: 0;">
                                    <?= $key ?>
                                </h4>
                                <hr>
                                <section id="servistab" class="profile-feed well"
                                    style="max-width: 100%; width: 100%; float:right; margin-bottom: 30px; padding: 5px; ">
                                    <div class="profile-stories" style="min-height: 240px;">

                                        <article class="story"
                                            style="margin: 0; min-height: 89px; text-align: center;">


                    <?php

                    if($value == 'invoice'){


                        $check_data = $mysqli->query("SELECT invoices_number FROM demands_generate WHERE id = '$id'");
                        if (mysqli_num_rows($check_data) > 0) {

                            $check = mysqli_fetch_array($check_data);
                            $inv = $check['invoices_number'];

                            $i = 1;

                            for ($inv; $inv > 0; $inv--, $i++) {
                                $getDocumentQuery = $mysqli->query('SELECT * FROM demands_advance_invoices WHERE demand_id = "' . $_REQUEST['id'] . '" AND status = "' . $i . '"') or die($mysqli->error);

                                if (mysqli_num_rows($getDocumentQuery) > 0) {
                                    $document = mysqli_fetch_assoc($getDocumentQuery);

                                    $file = $_SERVER['DOCUMENT_ROOT'] . '/admin/data/invoices/demands/Zalohova_faktura_'.$document['id'].'.pdf';?>

                                    <h3
                                        style="margin: 2px 0px 0px; text-align: center; min-height: 18px; font-size: 12px; overflow:hidden;">
                                        Zálohová faktura #<?= $i ?>
                                    </h3>
                                    <h4
                                        style="margin-top: 0; text-align: center; margin-bottom: 6px; font-size: 12px;">
                                        <?= date("d. m. Y H:i", filemtime($file)) ?>
                                    </h4>

                                    <a href="https://www.wellnesstrade.cz/admin/data/invoices/demands/Zalohova_faktura_<?= $document['id'] ?>.pdf?t=<?= $currentDate->getTimestamp() ?>"
                                       target="_blank"
                                       style="text-align: center;  padding: 10px 0; display: inline-block; width: 100%;">
                                        <i class="fa fa-file" style="font-size: 60px;"></i>
                                    </a>

                                    <div class="text-center"
                                         style="margin-top: 5px; padding-bottom: 14px; <?php if($i < $inv){ ?> margin-bottom: 14px; border-bottom: 1px solid #d5d5d8;<?php } ?>">
                                        <a href="https://www.wellnesstrade.cz/admin/data/invoices/demands/Zalohova_faktura_<?= $document['id'] ?>.pdf?t=<?= $currentDate->getTimestamp() ?>"
                                           class="btn btn-blue" download>
                                            <i class="entypo-down"></i>
                                        </a>
                                        <a href="https://www.wellnesstrade.cz/admin/data/invoices/demands/Zalohova_faktura_<?= $document['id'] ?>.pdf?t=<?= $currentDate->getTimestamp() ?>"
                                           class="btn btn-primary">
                                            <i class="entypo-search"></i>
                                        </a>
                                    </div>
                                    <?php
                                } else {
                                    ?>

                                    <p class="text-danger"
                                       style="text-align: center;padding: 0;font-size: 13px;">
                                        Chybějící <?= $key ?> #
                                        <?= $i + 1 ?>
                                    </p>


                                    <div class="profile-stories"
                                         style="margin-top: 5px; padding-bottom: 8px; margin-bottom: 8px; border-bottom: 1px solid #d5d5d8;">
                                        <article class="story" style="margin: 10px 0 0;text-align: center;">
                                            <form id="contract" style="text-align: center;" role="form"
                                                  method="post"
                                                  action="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=add_document&type=<?= $value ?>"
                                                  enctype="multipart/form-data">

                                                <input type="file" style="width: 78%; float: left;"
                                                       class="form-control" name="file" id="field-file"
                                                       placeholder="Placeholder">
                                                <button type="submit" class="btn btn-green btn-lg"
                                                        style="width: 20%; float: right; margin-left: 2%; padding: 0;">
                                                    <i class="entypo-upload"
                                                       style="line-height: 28px;font-size: 17px;"></i>
                                                </button>
                                            </form>
                                        </article>
                                    </div>
                                    <?php
                                }
                            }
                        }




                    }else{



        $documentsQuery = $mysqli->query('SELECT * FROM documents_contracts WHERE client_id="' . $_REQUEST['id'] . '" AND type = "' . $value . '" GROUP BY id, type order by id asc') or die($mysqli->error);

        if (mysqli_num_rows($documentsQuery) > 0) {

                while($document = mysqli_fetch_assoc($documentsQuery)){

                $file = $_SERVER['DOCUMENT_ROOT'] . "/data/clients/documents/" . $getclient['secretstring'] . "/" . $document['seoslug'] . "." . $document['extension']; ?>
                                            <h3
                                                style="margin: 2px 0px 0px; text-align: center; min-height: 18px; font-size: 12px; overflow:hidden;">
                                                <?= $document['name'] ?>
                                            </h3>
                                            <h4 style="margin: 2px 0px 16px; text-align: center; font-size: 12px;">
                                                <?= date("d. m. Y H:i", filemtime($_SERVER['DOCUMENT_ROOT'] . '/data/clients/documents/' . $getclient['secretstring'] . '/' . basename($file))) ?>
                                            </h4>

                                            <?php

                $filename = pathinfo($file, PATHINFO_FILENAME);
                $ext = pathinfo($file, PATHINFO_EXTENSION);

                if (in_array($ext, $image_extensions)) { ?>

                    <div class="lightgallery">
                                            <a data-src="https://www.wellnesstrade.cz/data/clients/documents/<?= $getclient['secretstring'] ?>/<?= basename($file) ?>"
                                                class="full" rel="<?php $document['type']; ?>"
                                                style="height: 136px; display: inline-block;">
                                                <img src="https://www.wellnesstrade.cz/data/clients/documents/<?= $getclient['secretstring'] ?>/<?= basename($file) ?>"
                                                    height="100%" class="img-rounded" style="max-height: 136px; border: 4px solid #F0F0F0;">
                                            </a>
                    </div>
                                            <?php } else { ?>
                                            <a href="https://docs.google.com/viewerng/viewer?url=https://www.wellnesstrade.cz/data/clients/documents/<?= $getclient['secretstring'] ?>/<?= basename($file) ?>"
                                                target="_blank"
                                                style="text-align: center;  padding-top: 20px; display: inline-block; width: 100%; height: 136px;">
                                                <i class="fa fa-file" style="font-size: 100px;"></i>
                                            </a>
                                            <?php } ?>

                                            <div class="text-center" style="margin-top: 15px; padding-bottom: 15px; margin-bottom: 15px; border-bottom: 1px solid #d5d5d8;">
                                                <a href="https://www.wellnesstrade.cz/data/clients/documents/<?= $getclient['secretstring'] . '/' . $document['seoslug'] . '.' . $document['extension'] ?>"
                                                    class="btn btn-blue" download>
                                                    <i class="entypo-down"></i>
                                                </a>
                                                <a href="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=remove_document&document_id=<?= $document['id'] ?>"
                                                    class="btn btn-red " style="margin-left: 4px;">
                                                    <i class="entypo-trash"></i>
                                                </a>
                                            </div>
                                            <?php
}
            ?>

                <div class="profile-stories" style="margin-bottom: 0px; ">
                    <article class="story"
                             style="margin: 0 0 5px; text-align: center;">
                        <form id="contract" style="text-align: center;" role="form"
                              method="post"
                              action="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=add_document&type=<?= $value ?>"
                              enctype="multipart/form-data">

                            <input type="file" style="width: 100%;" class="form-control"
                                   name="file" id="field-file"
                                   placeholder="Placeholder"><br>
                            <button type="submit"
                                    class="btn btn-green btn-icon icon-left btn-lg"
                                    style="font-size: 14px;width: 100%;padding-left: 36px;">
                                <i class="entypo-upload"
                                   style="line-height: 26px;font-size: 14px;padding: 6px 9px;"></i>
                                Nahrát
                            </button>
                        </form>
                    </article>
                </div>
            <?php
 } else { ?>

                                            <p class="text-danger"
                                                style="text-align: center;padding: 46px 0 0;font-size: 14px;height: 123px;">
                                                Chybějící <br><?= $key ?>
                                            </p>


                                            <div class="profile-stories" style="margin-bottom: 10px;">
                                                <article class="story"
                                                    style="margin: 0 0 5px;  text-align: center;">
                                                    <form id="contract" style="text-align: center;" role="form"
                                                        method="post"
                                                        action="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=add_document&type=<?= $value ?>"
                                                        enctype="multipart/form-data">

                                                        <input type="file" style="width: 100%;" class="form-control"
                                                            name="file" id="field-file"
                                                            placeholder="Placeholder"><br>
                                                        <button type="submit"
                                                            class="btn btn-green btn-icon icon-left btn-lg"
                                                            style="font-size: 14px;width: 100%;padding-left: 36px;">
                                                            <i class="entypo-upload"
                                                                style="line-height: 26px;font-size: 14px;padding: 6px 9px;"></i>
                                                            Nahrát
                                                        </button>
                                                    </form>
                                                </article>
                                            </div>


                                            <?php }


                                            }?>

                                        </article>
                                    </div>
                                </section>


                            </div>

                            <?php
} ?>

                        </div>

                    </article>

                </div>
            </section>

            <section id="generate-data-tab" class="tab profile-feed" style="display:none; margin-bottom: 50px;">

                <?php
} ?>



                <?php if ($access_edit) { ?>
                <center>
                    <?php

    $check_data = $mysqli->query("SELECT invoices_number, payment_method FROM demands_generate WHERE id = '$id'");
    if (mysqli_num_rows($check_data) == 1) {

        $generate_data = mysqli_fetch_array($check_data);

    }



    ?>

                    <div class="col-sm-6" style="padding: 0; width: 38%;">

                        <div style="width: 410px;">

                            <a href="./udaje-pro-generovani?id=<?= $id ?>"
                                style="margin-bottom: 12px; width: 100%; font-size: 13px; padding: 12px 16px 12px 46px;"
                                class="btn <?php if (isset($generate_data) && $generate_data) {
        echo 'btn-default';
    } else {
        echo 'btn-primary';
    } ?> btn-icon icon-left btn-lg">
                                <i class="entypo-doc-text"
                                    style="line-height: 24px;font-size: 14px; padding: 10px 8px;"></i>
                                Údaje pro generování
                            </a>
                            <br>

                            <?php
                            $vop = 'v';

                            if($brand['brand'] == 'IQue'){

                                $vop = 'v';

                            }elseif($brand['brand'] == 'Swim SPA'){

                                $vop = 'swim';

                            }elseif($brand['brand'] == 'Quantum'){

                                $vop = 'qua';

                            }
                            ?>

                            <a href="<?= $home ?>/admin/data/demands/documents/Všeobecné obchodní podmínky společnosti Wellness Trade_<?= $vop ?>.pdf?t=<?= $currentDate->getTimestamp() ?>"
                                target="_blank"
                                style="margin-bottom: 12px; margin-right: 4px; font-size: 12px; padding: 12px 16px 12px 46px; float: left;"
                                class="btn btn-primary btn-icon icon-left btn-lg">
                                <i class="entypo-down"
                                    style="line-height: 24px;font-size: 14px; padding: 10px 8px;"></i>
                                Obchodní podmínky
                            </a>

                            <?php


                            if(!empty($provedeni['value']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/admin/data/demands/documents/'.returnpn($getclient['customer'], $getclient['product']).' '.$provedeni['value'].' - Stavební příprava.pdf')){

                                $technical_link = $home.'/admin/data/demands/documents/'.returnpn($getclient['customer'], $getclient['product']).' '.$provedeni['value'].' - Stavební příprava.pdf?t='. $currentDate->getTimestamp();



                                ?>
                                <a href="../../controllers/import/technical-preparations?demand_id=<?= $getclient['id'] ?>"
                                   class="btn btn-blue btn-lg" style="padding: 8px 6px 7px; float:left; margin-right: 1px; border-top-right-radius: 0; border-bottom-right-radius: 0;">
                                    <i class="entypo-mail"
                                       style="font-size: 18px;"></i>
                                </a>
                                <a href="<?= $technical_link ?>"
                                   target="_blank"
                                   style="margin-bottom: 12px; font-size: 12px; padding: 12px 16px 12px 46px; float: left;  border-radius: 0;"
                                   class="btn btn-primary btn-icon icon-left btn-lg">
                                    <i class="entypo-down"
                                       style="line-height: 24px;font-size: 14px; padding: 10px 8px;"></i>
                                    Stavební příprava
                                </a>

                                <a href="../../controllers/import/technical-confirmation?demand_id=<?= $getclient['id'] ?>"
                                   class="btn btn-blue btn-lg" style="padding: 10.5px 12px 8px; border-top-left-radius: 0; border-bottom-left-radius: 0; margin-left: 1px;">
                                    <i class="fa fa-clipboard-list"
                                       style="font-size: 18px;"></i>
                                </a>
                            <?php




                            }else{ ?>
                                <a
                                   style="margin-bottom: 12px; font-size: 13px; padding: 12px 16px 12px 46px;"
                                   class="btn btn-white btn-icon icon-left btn-lg">
                                    <i class="entypo-cancel-circled"
                                       style="line-height: 24px;font-size: 14px; padding: 10px 8px;"></i>
                                    Stavební příprava - chybí
                                </a>

                            <?php } ?>


                        </div>

                    </div>

                    <div class="col-sm-6" style="padding: 0; width: 62%;">

                        <?php

    $i = 0;

    if (!empty($generate_data)) {

        $inv = $generate_data['invoices_number'];

        for ($inv; $inv > 0; $inv--) {


            $i++;

            $disableGeneration = false;
            $purchase_invoice = false;

            $get_advance_inv = $mysqli->query("SELECT *, DATE_FORMAT(payment_date, '%d. %m. %Y') as date_formated FROM demands_advance_invoices WHERE demand_id = '$id' AND status = '$i'");
            $adv_invoice = mysqli_fetch_assoc($get_advance_inv);

            $is_storno = (!empty($adv_invoice['storno']) && $adv_invoice['storno'] == 1);

            if(isset($adv_invoice) && $adv_invoice['export_id'] != 0){ $disableGeneration = true; }

            if(!empty($adv_invoice['id'])){
                $path = $_SERVER['DOCUMENT_ROOT'] . "/admin/data/invoices/demands/Zalohova_faktura_" . $adv_invoice['id'] . ".pdf";
                if (file_exists($path)) {
                    $purchase_invoice = true;
                }
            }

            ?>
            <div style="float: left; margin-right: 10px; <?php if ($is_storno) { ?>opacity: 0.45;<?php } ?>">

                <a href="javascript:"
                    onclick="jQuery('#invoice_modal_<?= $i ?>').modal('show');"
                    style="margin-bottom: 12px; width: 100%; margin-right: 4px; font-size: 13px; padding: 12px 16px 12px 16px;"
                    class="btn btn-danger btn-icon icon-left btn-lg"
                    <?php if ($is_storno || ($disableGeneration && $client['email'] != 'becher@saunahouse.cz')) { echo 'disabled'; } ?>
                >Generovat zál. fakturu #<?= $i ?></a>

                <br>

                <div style="width: 220px; float: left;">
                    <a href="/admin/data/invoices/demands/Zalohova_faktura_<?php if ($purchase_invoice) { echo $adv_invoice['id']; } ?>.pdf?t=<?= $currentDate->getTimestamp() ?>"
                        target="_blank"
                        style="font-size: 13px; padding: 12px 16px 12px 38px; border-top-right-radius: 0; border-bottom-right-radius: 0; width: 80.6%;"
                        class="btn btn-primary btn-icon icon-left btn-lg" <?php if (!$purchase_invoice) {
    echo 'disabled';
} ?>>
                        <i class="entypo-down"
                            style="line-height: 34px;font-size: 14px; padding: 4px 4px;"></i>
                        Zálohová faktura #<?= $i ?>
                    </a>
                    <a href="javascript:;"
                        onclick="jQuery('#invoice_payment_modal_<?= $i ?>').modal('show');"
                        style="font-size: 13px; padding: 8px 10px 7px; margin-left: -2.4px; border-top-left-radius: 0; border-bottom-left-radius: 0;"
                        class="btn
                        <?php if (!$purchase_invoice) { ?>btn-primary<?php } else { ?>btn-blue<?php } ?>"
                        <?php if (!$purchase_invoice || $adv_invoice['paid'] != 0) { echo 'disabled'; } ?>
                    >
                        <i class="entypo-credit-card" style="line-height: 24px;font-size: 14px;"></i>
                    </a>
<?php if(!empty($adv_invoice)){ 
                $storno_url = '/admin/pages/invoices/zalohove-faktury?id=' . $adv_invoice['id'] . '&action=storno_invoice&_redirect=' . urlencode($_SERVER['REQUEST_URI']);
            ?>
                    <div style="display: flex; width: 100%;">
                        <a href="/admin/pages/invoices/zalohove-faktury?q=<?php if ($purchase_invoice) { echo $adv_invoice['id']; }?>" target="_blank"
                            style="font-weight: bold; font-size: 12px; flex: 1; padding: 12px 6px; border-top-left-radius: 0; border-top-right-radius: 0; border-bottom-right-radius: 0; <?php if($is_storno){ echo 'opacity:0.5; text-decoration:line-through;'; } ?>"
                            class="btn btn-default btn-lg" <?php if (!$purchase_invoice) { echo 'disabled'; } ?>>
                            <?php

                            if($adv_invoice['paid'] != 0){

                                echo payment_status($adv_invoice);

                            }else{

                                $adv_invoice['target_id'] = $adv_invoice['id'];
                                $adv_invoice['location_id'] = $getclient['showroom'];

                                $payment = check_payment($adv_invoice, 'order');

                                echo '<span style="font-size: 13px; '.$payment['color'].'">'.$payment['info'].'</span>';
                            }

                            ?>
                            <i class="fa fa-external-link" style="float: right; padding-top: 3px;"></i>
                        </a>
                        <a href="<?= $storno_url ?>"
                            title="<?= $is_storno ? 'Zrušit storno' : 'Stornovat fakturu' ?>"
                            onclick="return confirm('<?= $is_storno ? 'Zrušit storno faktury č. ' . $adv_invoice['id'] . '?' : 'Stornovat fakturu č. ' . $adv_invoice['id'] . '?' ?>')"
                            style="font-size: 12px; padding: 10px 9px; border-top-left-radius: 0; border-top-right-radius: 0; border-bottom-left-radius: 0; margin-left: -1px;"
                            class="btn <?= $is_storno ? 'btn-warning' : 'btn-danger' ?> btn-lg"
                            <?php if (!$purchase_invoice) { echo 'disabled'; } ?>>
                            <i class="<?= $is_storno ? 'entypo-ccw' : 'entypo-cancel' ?>" style="font-size: 14px; line-height: 22px;"></i>
                        </a>
                    </div>
                <?php } ?>
                    </div>
                </div>
                <?php
            }
         }
    ?>


                    </div>

                    <hr style="float: left; display: block; width: 100%; margin: 12px;">


                    <div class="col-sm-6" style="padding: 0; float: left; width: 50%;">

                        <?php if($getclient['customer'] == 1 || $getclient['customer'] == 3){

                            $purchase_protocol = false;
                            $purchase_contract = false;
                            $checklist = false;

                            $path = $_SERVER['DOCUMENT_ROOT'] . "/admin/data/demands/protocols/Predavaci_protokol_v_" . $id . ".pdf";
                            if (file_exists($path)) {
                                $purchase_protocol = true;
                            }

                            $path_purchase = $_SERVER['DOCUMENT_ROOT'] . "/admin/data/demands/contracts/Kupni_smlouva_" . $id . ".pdf";
                            if (file_exists($path_purchase)) {
                                $purchase_contract = true;
                            }

                            $purch = 'Kupni_smlouva_';

                            $path_budget = $_SERVER['DOCUMENT_ROOT'] . "/admin/data/demands/budget_offers/Cenova_nabidka_" . $id . ".pdf";
                            if (file_exists($path_budget)) {
                                $purchase_budget = true;
                            }


                            $path_checklist = $_SERVER['DOCUMENT_ROOT'] . "/admin/data/demands/checklists/Checklist_hottub_" . $id . ".pdf";
                            if (file_exists($path_checklist)) {
                                $checklist = true;
                            }

                            if($getclient['customer'] == 3){ ?>

                                <h4 style="text-align: left">Vířivka</h4><?php }

                            ?>


                        <div style="float: left; margin-right: 10px;">

                            <a href="/admin/controllers/generators/purchase_budget_offer?id=<?= $id ?>&type=1" style="margin-bottom: 12px; margin-right: 4px; width: 100%; font-size: 13px; padding: 12px 16px 12px 16px;"
                               class="btn btn-orange btn-icon icon-left btn-lg" <?php if (!isset($generate_data) || !$generate_data) { echo 'disabled'; } ?>>Generovat cenovou nabídku</a>
                            <br>
                            <a <?php if (isset($purchase_budget) && $purchase_budget) { ?>
                                href="/admin/data/demands/budget_offers/Cenova_nabidka_<?= $id ?>.pdf?t=<?= $currentDate->getTimestamp() ?>"
                                target="_blank" <?php } ?> style="margin-bottom: 12px; margin-right: 4px; font-size: 13px; padding: 12px 16px
                            12px 46px;width: 100%; " class="btn btn-primary btn-icon icon-left btn-lg" <?php if (!isset($purchase_budget) || !$purchase_budget) {
                                echo 'disabled';
                            } ?>>
                                <i class="entypo-down"
                                   style="line-height: 24px;font-size: 14px; padding: 10px 8px;"></i>
                                Cenová nabídka
                            </a>

                        </div>


                        <div style="float: left; margin-right: 10px;">

                            <a href="/admin/controllers/generators/purchase_contract?id=<?= $id ?>&type=1" style="margin-bottom: 12px; margin-right: 4px; width: 100%; font-size: 13px; padding: 12px 16px 12px 16px;"
                                class="btn btn-success btn-icon icon-left btn-lg" <?php if (!isset($generate_data) || !$generate_data) { echo 'disabled'; } ?>>Generovat kupní smlouvu</a>
                            <br>
                            <a <?php if (isset($purchase_contract) && $purchase_contract) { ?>
                                href="/admin/data/demands/contracts/<?= $purch . $id ?>.pdf?t=<?= $currentDate->getTimestamp() ?>"
                                target="_blank" <?php } ?> style="margin-bottom: 12px; margin-right: 4px; font-size: 13px; padding: 12px 16px
                                12px 46px;width: 100%; " class="btn btn-primary btn-icon icon-left btn-lg" <?php if (!isset($purchase_contract) || !$purchase_contract) {
        echo 'disabled';
    } ?>>
                                <i class="entypo-down"
                                    style="line-height: 24px;font-size: 14px; padding: 10px 8px;"></i>
                                Kupní smlouva
                            </a>

                        </div>

                        <div style="float: left; margin-right: 10px;">

                            <a href="/admin/controllers/generators/purchase_protocol?id=<?= $id ?>&type=1"
                                style="margin-bottom: 12px; margin-right: 4px; font-size: 13px; padding: 12px 16px 12px 16px; width: 100%;"
                                class="btn btn-info btn-icon icon-left btn-lg" <?php if (!isset($generate_data) || !$generate_data) {
        echo 'disabled';
    } ?>>
                                Generovat předávací protokol
                            </a>
                            <br>

                            <a href="/admin/data/demands/protocols/Predavaci_protokol_v_<?= $id ?>.pdf?t=<?= $currentDate->getTimestamp() ?>"
                                target="_blank"
                                style="margin-bottom: 12px; margin-right: 4px; font-size: 13px; padding: 12px 16px 12px 46px; width: 100%;"
                                class="btn btn-primary btn-icon icon-left btn-lg" <?php if (!isset($purchase_contract) || !$purchase_protocol) {
        echo 'disabled';
    } ?>>
                                <i class="entypo-down"
                                    style="line-height: 24px;font-size: 14px; padding: 10px 8px;"></i>
                                Předávací protokol
                            </a>
                        </div>

                        <div style="float: left;">
                            <a href="/admin/controllers/generators/checklist_hottub?id=<?= $id ?>"
                                style="margin-bottom: 12px; margin-right: 4px; font-size: 13px; padding: 12px 16px 12px 16px; width: 100%;"
                                class="btn btn-default btn-icon icon-left btn-lg">
                                Generovat checklist
                            </a>
                            <br>
                            <a href="/admin/data/demands/checklists/Checklist_hottub_<?= $id ?>.pdf?t=<?= $currentDate->getTimestamp() ?>" target="_blank"
                                style="margin-bottom: 12px; margin-right: 4px; font-size: 13px; padding: 12px 16px 12px 46px; width: 100%;"
                                class="btn btn-primary btn-icon icon-left btn-lg" <?php if (!isset($checklist) || !$checklist) {
        echo 'disabled';
    } ?>>
                                <i class="entypo-down"
                                    style="line-height: 24px;font-size: 14px; padding: 10px 8px;"></i>
                                Checklist
                            </a>
                        </div>

                    <?php } ?>



                        <?php

                        if($getclient['customer'] == 0 || $getclient['customer'] == 3){

                            $purchase_protocol = false;
                            $purchase_contract = false;

                            $path = $_SERVER['DOCUMENT_ROOT'] . "/admin/data/demands/protocols/Predavaci_protokol_s_" . $id . ".pdf";
                            if (file_exists($path)) {
                                $purchase_protocol = true;
                            }


                            $path_purchase = $_SERVER['DOCUMENT_ROOT'] . "/admin/data/demands/contracts/Smlouva_o_dilo_" . $id . ".pdf";
                            if (file_exists($path_purchase)) {
                                $purchase_contract = true;
                            }

                            $purch = 'Smlouva_o_dilo_';

                            if($getclient['customer'] == 3){ echo '<div style="clear: both"></div><h4 style="text-align: left">Sauna</h4>'; } ?>

                            <div style="float: left; margin-right: 10px;">

                                <a href="/admin/controllers/generators/purchase_contract?id=<?= $id ?>&type=0"
                                   style="margin-bottom: 12px; margin-right: 4px; width: 100%; font-size: 13px; padding: 12px 16px 12px 16px;"
                                   class="btn btn-success btn-icon icon-left btn-lg" <?php if (!isset($generate_data) || !$generate_data) {
                                    echo 'disabled';
                                } ?>>
                                    Generovat smlouvu o dílo
                                </a>

                                <br>
                                <a <?php if (isset($purchase_contract) && $purchase_contract) { ?>
                                    href="/admin/data/demands/contracts/<?= $purch . $id ?>.pdf?t=<?= $currentDate->getTimestamp() ?>"
                                    target="_blank" <?php } ?> style="margin-bottom: 12px; margin-right: 4px; font-size: 13px; padding: 12px 16px
                                12px 46px;width: 100%; " class="btn btn-primary btn-icon icon-left btn-lg" <?php if (!isset($purchase_contract) || !$purchase_contract) {
                                    echo 'disabled';
                                } ?>>
                                    <i class="entypo-down"
                                       style="line-height: 24px;font-size: 14px; padding: 10px 8px;"></i>
                                    Kupní smlouva
                                </a>

                            </div>

                            <div style="float: left; margin-right: 10px;">

                                <a href="/admin/controllers/generators/purchase_protocol?id=<?= $id ?>&type=0"
                                   style="margin-bottom: 12px; margin-right: 4px; font-size: 13px; padding: 12px 16px 12px 16px; width: 100%;"
                                   class="btn btn-info btn-icon icon-left btn-lg" <?php if (!isset($generate_data) || !$generate_data) {
                                    echo 'disabled';
                                } ?>>
                                    Generovat předávací protokol
                                </a>
                                <br>

                                <a href="/admin/data/demands/protocols/Predavaci_protokol_s_<?= $id ?>.pdf?t=<?= $currentDate->getTimestamp() ?>"
                                   target="_blank"
                                   style="margin-bottom: 12px; margin-right: 4px; font-size: 13px; padding: 12px 16px 12px 46px; width: 100%;"
                                   class="btn btn-primary btn-icon icon-left btn-lg" <?php if (!isset($purchase_contract) || !$purchase_protocol) {
                                    echo 'disabled';
                                } ?>>
                                    <i class="entypo-down"
                                       style="line-height: 24px;font-size: 14px; padding: 10px 8px;"></i>
                                    Předávací protokol
                                </a>
                            </div>

                        <?php } ?>


                        <?php if($getclient['customer'] == 4){


                            $purchase_protocol = false;
                            $purchase_contract = false;
                            $checklist = false;

                            $path = $_SERVER['DOCUMENT_ROOT'] . "/admin/data/demands/protocols/Predavaci_protokol_p_" . $id . ".pdf";
                            if (file_exists($path)) {
                                $purchase_protocol = true;
                            }

                            $path_purchase = $_SERVER['DOCUMENT_ROOT'] . "/admin/data/demands/contracts/Kupni_smlouva_" . $id . ".pdf";
                            if (file_exists($path_purchase)) {
                                $purchase_contract = true;
                            }

                            $purch = 'Kupni_smlouva_';

                            $path_budget = $_SERVER['DOCUMENT_ROOT'] . "/admin/data/demands/budget_offers/Cenova_nabidka_" . $id . ".pdf";
                            if (file_exists($path_budget)) {
                                $purchase_budget = true;
                            }


                            $path_checklist = $_SERVER['DOCUMENT_ROOT'] . "/admin/data/demands/checklists/Checklist_pergola_" . $id . ".pdf";
                            if (file_exists($path_checklist)) {
                                $checklist = true;
                            }

                            ?>

                            <div style="float: left; margin-right: 10px;">

                                <a href="/admin/controllers/generators/purchase_budget_offer?id=<?= $id ?>&type=4" style="margin-bottom: 12px; margin-right: 4px; width: 100%; font-size: 13px; padding: 12px 16px 12px 16px;"
                                   class="btn btn-orange btn-icon icon-left btn-lg" <?php if (!isset($generate_data) || !$generate_data) { echo 'disabled'; } ?>>Generovat cenovou nabídku</a>
                                <br>
                                <a <?php if (isset($purchase_budget) && $purchase_budget) { ?>
                                    href="/admin/data/demands/budget_offers/Cenova_nabidka_<?= $id ?>.pdf?t=<?= $currentDate->getTimestamp() ?>"
                                    target="_blank" <?php } ?> style="margin-bottom: 12px; margin-right: 4px; font-size: 13px; padding: 12px 16px
                            12px 46px;width: 100%; " class="btn btn-primary btn-icon icon-left btn-lg" <?php if (!isset($purchase_budget) || !$purchase_budget) {
                                    echo 'disabled';
                                } ?>>
                                    <i class="entypo-down"
                                       style="line-height: 24px;font-size: 14px; padding: 10px 8px;"></i>
                                    Cenová nabídka
                                </a>

                            </div>


                            <div style="float: left; margin-right: 10px;">

                                <a href="/admin/controllers/generators/purchase_contract?id=<?= $id ?>&type=4" style="margin-bottom: 12px; margin-right: 4px; width: 100%; font-size: 13px; padding: 12px 16px 12px 16px;"
                                   class="btn btn-success btn-icon icon-left btn-lg" <?php if (!isset($generate_data) || !$generate_data) { echo 'disabled'; } ?>>Generovat kupní smlouvu</a>
                                <br>
                                <a <?php if (isset($purchase_contract) && $purchase_contract) { ?>
                                    href="/admin/data/demands/contracts/<?= $purch . $id ?>.pdf?t=<?= $currentDate->getTimestamp() ?>"
                                    target="_blank" <?php } ?> style="margin-bottom: 12px; margin-right: 4px; font-size: 13px; padding: 12px 16px
                                12px 46px;width: 100%; " class="btn btn-primary btn-icon icon-left btn-lg" <?php if (!isset($purchase_contract) || !$purchase_contract) {
                                    echo 'disabled';
                                } ?>>
                                    <i class="entypo-down"
                                       style="line-height: 24px;font-size: 14px; padding: 10px 8px;"></i>
                                    Kupní smlouva
                                </a>

                            </div>

                            <div style="float: left; margin-right: 10px;">

                                <a href="/admin/controllers/generators/purchase_protocol?id=<?= $id ?>&type=4"
                                   style="margin-bottom: 12px; margin-right: 4px; font-size: 13px; padding: 12px 16px 12px 16px; width: 100%;"
                                   class="btn btn-info btn-icon icon-left btn-lg" <?php if (!isset($generate_data) || !$generate_data) {
                                    echo 'disabled';
                                } ?>>
                                    Generovat předávací protokol
                                </a>
                                <br>

                                <a href="/admin/data/demands/protocols/Predavaci_protokol_p_<?= $id ?>.pdf?t=<?= $currentDate->getTimestamp() ?>"
                                   target="_blank"
                                   style="margin-bottom: 12px; margin-right: 4px; font-size: 13px; padding: 12px 16px 12px 46px; width: 100%;"
                                   class="btn btn-primary btn-icon icon-left btn-lg" <?php if (!isset($purchase_contract) || !$purchase_protocol) {
                                    echo 'disabled';
                                } ?>>
                                    <i class="entypo-down"
                                       style="line-height: 24px;font-size: 14px; padding: 10px 8px;"></i>
                                    Předávací protokol
                                </a>
                            </div>

                            <div style="float: left;">
                                <a href="/admin/controllers/generators/checklist_pergola?id=<?= $id ?>"
                                   style="margin-bottom: 12px; margin-right: 4px; font-size: 13px; padding: 12px 16px 12px 16px; width: 100%;"
                                   class="btn btn-default btn-icon icon-left btn-lg">
                                    Generovat checklist
                                </a>
                                <br>
                                <a href="/admin/data/demands/checklists/Checklist_pergola_<?= $id ?>.pdf?t=<?= $currentDate->getTimestamp() ?>" target="_blank"
                                   style="margin-bottom: 12px; margin-right: 4px; font-size: 13px; padding: 12px 16px 12px 46px; width: 100%;"
                                   class="btn btn-primary btn-icon icon-left btn-lg" <?php if (!isset($checklist) || !$checklist) {
                                    echo 'disabled';
                                } ?>>
                                    <i class="entypo-down"
                                       style="line-height: 24px;font-size: 14px; padding: 10px 8px;"></i>
                                    Checklist
                                </a>
                            </div>

                        <?php } ?>


                    </div>









                    <div class="col-sm-6" style="padding: 0; float: left; width: 50%;">

                        <?php
$i = 0;

    $get_advance_invoices = $mysqli->query("SELECT id FROM demands_advance_invoices WHERE demand_id = '$id' order by id desc");

    while ($get_advance = mysqli_fetch_array($get_advance_invoices)) {
        $i++; ?>

        <span style="display: inline-block;">

                        <a href="javascript:;"
                            onclick="jQuery('#invoice_proof_modal_<?= $i ?>').modal('show');"
                            style="margin-bottom: 12px; margin-right: 4px; font-size: 13px; width: 185px; padding: 12px 16px 12px 16px;"
                            class="btn btn-blue btn-icon icon-left btn-lg">
                            Vystavit daňový doklad #<?= $i ?>
                        </a>
                        <br>
        <?php

            $purchase_invoice = false;

            $get_advance_inv_proof = $mysqli->query("SELECT id, id_prefix FROM demands_advance_invoices_proof WHERE demand_id = '$id' AND advance_invoice = '$i'");
            $adv_invoice_proof = mysqli_fetch_array($get_advance_inv_proof);

            if(!empty($adv_invoice_proof)){
                $path = $_SERVER['DOCUMENT_ROOT'] . "/admin/data/invoices/demands_tax/Danovy_doklad_" . $adv_invoice_proof['id_prefix'] . "IN" . $adv_invoice_proof['id'] . ".pdf";
                if (file_exists($path)) {
                    $purchase_invoice = true;
                }
            }
            ?>

            <a href="/admin/data/invoices/demands_tax/Danovy_doklad_<?php if($purchase_invoice){ echo $adv_invoice_proof['id_prefix'] . 'IN' . $adv_invoice_proof['id']; } ?>.pdf?t=<?= $currentDate->getTimestamp() ?>"
               target="_blank"
               style="margin-bottom: 12px; width: 185px;  margin-right: 4px; font-size: 13px; padding: 12px 16px 12px 46px;"
               class="btn btn-primary btn-icon icon-left btn-lg" <?php if (!$purchase_invoice) {
                echo 'disabled';
            } ?>>
                <i class="entypo-down"
                   style="line-height: 24px;font-size: 14px; padding: 10px 8px;"></i>
                Daňový doklad #<?= $i ?>
            </a>

        </span>
        <?php } ?>

                        <br>

                    </div>






                    <div style="clear: both;"></div>



                </center>
                <?php } ?>

                <?php

if ($getclient['status'] == 5 || $getclient['status'] == 8 || $getclient['status'] == 13) {
    ?>
            </section>
            <?php
} ?>