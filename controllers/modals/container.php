<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/config/configPublic.php';


/* check access...

baumel = 557
becher = 2126
svoboda = 2127
šefl = 2142
valůšková = 2158
berger = 2190
pazdersky = 3236
fajt = 11982

*/



$container_products = $mysqli->query("SELECT 
       p.*, 
       DATE_FORMAT(p.date_created, '%d. %m. %Y') as dateformated, 
       d.user_name, 
       d.area, 
       DATE_FORMAT(d.realization, '%d. %m. %Y') as realizationformated, 
       d.id as demand_id, 
       d.product as demand_product, 
       w.serial_number as warehouse_number, 
       w.demand_id as has_demand, 
       w.description, 
       creator.user_name as creator_user_name, 
       editor.user_name as editor_user_name, 
       wp.fullname, 
       w.reserved_showroom, 
       wp.brand,
       wp.code
    FROM containers_products p 
        LEFT JOIN demands d ON d.id = p.demand_id 
        LEFT JOIN warehouse w ON w.id = p.warehouse_id 
        LEFT JOIN warehouse_products wp ON wp.connect_name = p.product 
        LEFT JOIN demands creator ON creator.id = p.creator_id 
        LEFT JOIN demands editor ON editor.id = p.editor_id 
    WHERE p.container_id = '" . $_REQUEST['id'] . "' 
    ORDER BY RIGHT(w.serial_number, 3) ASC")
or die($mysqli->error);

while ($cont_product = mysqli_fetch_array($container_products)) {

    if ($cont_product['brand'] == 'IQue') {

        $brand = 'IQue';
        $link_secret = 'IQU_bgewKD';

    } elseif ($cont_product['brand'] == 'Lovia' || $cont_product['brand'] == 'Swim SPA') {

        $brand = 'Lovia';
        $link_secret = 'LOV_qJcUBZ';

    } elseif ($cont_product['brand'] == 'Quantum') {

        $brand = 'Quantum';
        $link_secret = 'QUA_jEjsaI';

    } elseif ($cont_product['brand'] == 'Pergola') {

        $brand = 'Pergola';
        $link_secret = 'PER_JoifaE';

    } elseif ($cont_product['brand'] == 'Espoo Smart') {

        $brand = 'Espoo Smart';
        $link_secret = 'ESP_fjFSoe';

    } elseif ($cont_product['brand'] == 'Espoo Deluxe') {
    
        $brand = 'Espoo Deluxe';
        $link_secret = 'ESS_kSpaeKL';
    
    }

    continue;
}

mysqli_data_seek($container_products, 0);

?>

<hr />

<?php

if(mysqli_num_rows($container_products) > 0){

    /*
         baumel = 557
         becher = 2126
         svoboda = 2127
         šefl = 2142
         valůšková = 2158
         berger = 2190
         pazdersky = 3236
         hloušek = 8704
         halda = 5510
         fajt = 11982


         Dokumenty shipping order, Advice notice - Pazdersky, Halda, Berger, Hloušek
         Dokumenty  + packing list - Pazdersky
         Dokumenty plný přehled pouze - Svoboda, Baumel, Šefl, Jitka

          */

    $documents = [
        'Proforma faktura' => [557, 2126, 2127, 2142, 2158],
        'Shipping Advice' => [11982, 3236, 5510, 2190, 8704, 11567, 557, 2126, 2127, 2142, 2158],
        'Arrival Notice' => [11982, 3236, 5510, 2190, 8704, 11567, 557, 2126, 2127, 2142, 2158],
        'Sea WayBill' => [557, 2126, 2127, 2142, 2158],
        'Invoice' => [557, 2126, 2127, 2142, 2158],
        'Packing list' => [11982, 3236, 557, 2126, 2127, 2142, 2158],
        'Customs' => [557, 2126, 2127, 2142, 2158],
        'Fotografie nákladu' => [557, 2126, 2127, 2142, 2158, 3236, 11982],
        'Faktura za dopravu' => [557, 2126, 2127, 2142, 2158],
        'Faktura za Clo-JSD' => [557, 2126, 2127, 2142, 2158],
        'Doklad Clo-JSD' => [557, 2126, 2127, 2142, 2158],
    ];

    $i = 0;
    $files = array('Proforma faktura', 'Shipping Advice', 'Arrival Notice', 'Sea WayBill', 'Invoice', 'Packing list', 'Customs', 'Fotografie nákladu', 'Faktura za dopravu', 'Faktura za Clo-JSD', 'Doklad Clo-JSD');

    foreach ($documents as $file => $ids) {

        if (!in_array($client['id'], $ids)) {
            continue;
        }

        if ($file == 'Fotografie nákladu') {

            ?>
    <div class="well col-sm-3" style="min-height: 134px; width: 8.3%; float: left; margin: 0px 5px 0 5px; padding: 4px; height: 134px;">


        <div class="profile-stories" style="margin-bottom: 10px;">

            <h4 style="margin: 6px 0px 11px; text-align: center; font-size: 11px;">Fotografie nákladu</h4>

            <article class="story" style="margin: 0 0 0 0; min-height: 20px;margin-top: 34px">

                <center>
                    <?php $result = glob($_SERVER['DOCUMENT_ROOT'] . '/admin/data/containers/' . $link_secret . '/' . $_REQUEST['id'] . '/cargo/small_*.*');
            if (!empty($result)) {
                ?>
                    <button class="btn btn-primary toggle-technical-upload-modal"
                        data-container="<?= $_REQUEST['id'] ?>"
                            data-link="<?= $link_secret ?>"> <i class="entypo-search"></i> </button>

                    <?php } ?>

                    <button type="submit" class="btn btn-green toggle-technical-upload-modal"
                        data-container="<?= $_REQUEST['id'] ?>"
                            data-link="<?= $link_secret ?>"> <i class="entypo-plus"></i> </button>

                </center>

            </article>
        </div>


    </div>




    <?php } else {


            $i++;

            ?>

    <div id="<?= $_REQUEST['id'].'-'.$i ?>" class="well col-sm-3" style=" min-height: 134px; width: 8.3%; float: left; margin: 0px 5px 0 5px; padding: 4px">

        <div class="holder">

            <?php $result = glob($_SERVER['DOCUMENT_ROOT'] . '/admin/data/containers/' . $link_secret . '/' . $_REQUEST['id'] . '/' . $file . '.*');

                if (!empty($result)) {

                    foreach ($result as $res) { ?>

            <div class="profile-stories">

                <article class="story" style="margin: 0; min-height: 89px;">

                    <div style="display: block;">
                        <span id="demandus" style=" width: 100%;">
                            <h4 style="margin: 6px 0px 6px; text-align: center; font-size: 11px;">
                                <?= $file ?>
                            </h4>
                            <h5 style="margin: 2px 0px 11px; text-align: center; font-size: 11px;">
                                <?= date('d. m. Y H:i', filemtime($res)) ?>
                            </h5>
                            <?php

                        $ext = pathinfo($res, PATHINFO_EXTENSION);

                        if (in_array($ext, $image_extensions)) { ?>
                            <center><a
                                    href="https://www.wellnesstrade.cz/admin/data/containers/<?= $link_secret . '/' . $_REQUEST['id'] ?>/<?= basename($res) ?>"
                                    target="_blank"><img
                                        src="https://www.wellnesstrade.cz/admin/data/containers/<?= $link_secret . '/' . $_REQUEST['id'] ?>/<?= basename($res) ?>"
                                        width="auto" style="max-width: 100%;" class="img-rounded"></a></center>
                            <?php } else { ?>
                            <center>
                                <a href="https://docs.google.com/viewerng/viewer?url=https://www.wellnesstrade.cz/admin/data/containers/<?php echo$link_secret . '/' . $_REQUEST['id']; ?>/<?= basename($res) ?>"
                                    target="_blank">
                                    <i class="fa fa-file" style="font-size: 50px;"></i>
                                </a>
                            </center>

                            <?php } ?>
                            <div class="text-center" style="margin: 12px 0 8px;">

                                <a href="https://www.wellnesstrade.cz/admin/data/containers/<?= $link_secret . '/' . $_REQUEST['id'] ?>/<?= basename($res) ?>"
                                    class="btn btn-blue" style="padding-right: 14px;" download>
                                    <i class="entypo-down"></i>
                                </a>
                      <?php if($client['id'] == 2127 ||
                                $client['id'] == 557 ||
                                $client['id'] == 2142 ||
                                $client['id'] == 2126
                            ){

                                ?>
                                <a class="remove-file btn btn-red" style="padding-right: 14px; margin-left: 4px;" data-name="<?= basename($res) ?>" data-id="<?= $i ?>" data-container="<?= $_REQUEST['id'] ?>">
                                    <i class="entypo-trash"></i>
                                </a>
                            <?php } ?>
                            </div>
                        </span>

                    </div>

                </article>

            </div>




            <?php }

                } else {

                    $seourl = str_replace(' ', '_', $file);

                    ?>

            <script type="text/javascript">
            jQuery(document).ready(function($) {

                $('#fileinput-<?= $seourl ?>-<?= $_REQUEST['id'] ?>').change(function() {
                    $('.span-<?= $seourl ?>-<?= $_REQUEST['id'] ?>').text($(this).val());
                });
            });
            </script>


            <div class="profile-stories" style="margin-bottom: 10px;">

                <h4 style="margin: 6px 0px 11px; text-align: center; font-size: 11px;">
                    <?= $file ?>
                </h4>

                <article class="story" style="margin: 0 0 0 0; min-height: 20px;">

                    <form class="file_form" style="text-align: center;" role="form" method="post"
                        action="editace-kontejneru?id=<?= $_REQUEST['id'] ?>&action=add_file&type=<?= $file ?>"
                        enctype="multipart/form-data" data-type="<?= $file ?>" data-id="<?= $i ?>" data-container="<?= $_REQUEST['id'] ?>">
                        <center>

                            <input type="file" style="width: 100%; height: 36px;" class="form-control" name="zmrdus"
                                id="fileinput-<?= $seourl ?>-<?= $_REQUEST['id'] ?>" placeholder="Placeholder">
                            <span class="span-<?= $seourl ?>-<?= $_REQUEST['id'] ?>"
                                style="float: left; padding: 8px 7px; width: 100%; word-wrap: break-word;"></span>
                            <br>
                            <button type="submit" class="btn btn-green"> <i class="entypo-plus"></i> </button>

                        </center>
                    </form>
                </article>

            </div>

            <?php } ?>

        </div>
    </div>

    <?php }

        ?>




    <?php

    }

} ?>
<div style="clear: both;"></div>
<hr />
<div class="row info-list">
    <?php

$i = 0;

while ($cont_product = mysqli_fetch_assoc($container_products)) {
    $i++;

    ?>
    <div id="<?= $cont_product['id'] ?>" class="well col-sm-3" style="width: 24.2%; float: left; margin: 20px 0.4% 0; padding: 4px">
        <div style="float: left; width: 100%;">
            <a class="member-img" style="width: 50px; margin: 8px 12px 8px 8px;background-color: #ececec;border-radius: 5px;border: 1px solid #d5d5d5;">
                <img src="https://www.wellnesstrade.cz/admin/data/images/customer/<?= $cont_product['product'] ?>.png"
                    width="50px" class="img-rounded" alt="<?= $cont_product['dateformated'] ?>" />
            </a>
            <h5 style="font-size: 15px;line-height: 23px;margin-top: 10px;">
                <?php

                if ($cont_product['warehouse_id'] != 0) {

                    if($cont_product['customer'] == 1){

                        $link = 'virivku';

                    }elseif($cont_product['customer'] == 0){

                        $link = 'saunu';
                        
                    }elseif($cont_product['customer'] == 4){

                        $link = 'pergolu';
                        
                    }

                    echo '#' . $cont_product['id'] . ':<a href="./zobrazit-'.$link.'?id='.$cont_product['warehouse_id'].'" target="_blank"><i class="entypo-check"></i>' . $cont_product['warehouse_number'] . ' ' . ucfirst($cont_product['fullname']) . '</a>';

                } else {

                    echo '#' . $cont_product['id'] . ' ' . ucfirst($cont_product['fullname']);

                } ?>

                <?php if(!empty($cont_product['code'])){ ?>
                    <br><small><?= $cont_product['code'] ?></small>
                <?php } ?>
            </h5>


            <?php if (!empty($cont_product['description'])) { ?>

                <div class="alert alert-info" style="margin-bottom: 0;float: left;display: inline-block;width: 100%;margin-top: 10px; font-weight: bold;"><i class="entypo-info"></i> <?= $cont_product['description'] ?></div>

            <?php } ?>


            <p style="margin-bottom: 0;">
                <?php if (isset($cont_product['has_demand']) && $cont_product['has_demand'] != 0) {

                    $get_user = $mysqli->query("SELECT area, user_name, DATE_FORMAT(realization, '%d. %m. %Y') as realizationformated, product FROM demands WHERE id = '".$cont_product['has_demand']."'")or die($mysqli->error);
                    $user = mysqli_fetch_assoc($get_user);

                    if($user['product'] != $cont_product['product']){

                        echo '<h4 class="text-danger">!!! vířivka neodpovídá poptávce !!!</h4>';

                    }

                    $area = $user['area'] == 'prague' ? $area = 'PR: ' : $area = 'BR: ';

                    ?>
                    <strong style="font-size: 13px;"><a href="/admin/pages/demands/zobrazit-poptavku?id=<?= $cont_product['has_demand'] ?>">
                            <?= $area.$user['user_name'] ?></a> [<u style="color: #d53e29"><?= $user['realizationformated'] ?></u>]</strong>
                    <?php
                } elseif (isset($cont_product['demand_id']) && $cont_product['demand_id'] != 0) {

                    if($cont_product['demand_product'] != $cont_product['product']){

                        echo '<h4 class="text-danger">!!! vířivka neodpovídá poptávce !!!</h4>';

                    }

                    $area = $cont_product['area'] == 'prague' ? $area = 'PR: ' : $area = 'BR: ';

                    ?>
                    <strong style="font-size: 13px;"><a href="/admin/pages/demands/zobrazit-poptavku?id=<?= $cont_product['demand_id'] ?>">
                            <?= $area.$cont_product['user_name'] ?></a> [<u style="color: #d53e29"><?= $cont_product['realizationformated'] ?></u>]</strong>
                    <?php

                } elseif($cont_product['reserved_showroom'] != 0){

                    $location_query = $mysqli->query("SELECT * FROM shops_locations WHERE id = '".$cont_product['reserved_showroom']."'") or die($mysqli->error);
                    $location = mysqli_fetch_array($location_query);
                    ?>


                <strong style="color: #0077b1;">Rezervace na showroom » <?= $location['name'] ?></strong>

                <?php }else{


                    echo '<strong style="color: #00a651;">Volná</strong>';


                }



                ?>
            </p>
            <hr style="float:left; width: 100%;">
        </div>
        <?php

    $result = glob($_SERVER['DOCUMENT_ROOT'] . '/admin/data/containers/' . $link_secret . '/' . $cont_product['container_id'] . '/' . $cont_product['id'] . '/small_*.*');

    ?>
        <a data-container="<?= $cont_product['container_id'] ?>"
           data-hottub="<?= $cont_product['id'] ?>"
           data-link="<?= $link_secret ?>"
            class="toggle-picture-upload-modal btn <?php if (!empty($result)) { ?>btn-green<?php } else { ?>btn-primary<?php } ?> btn-sm btn-icon icon-left"  style="max-width: 30%; height: 34px; line-height: 24px; padding-left: 29px;">
            <i class="entypo-plus" style="line-height: 24px;padding: 5px 4px;"></i>
            Foto
        </a>


        <?php if((!empty($containers['value']) && $containers['value'] == 1) 
        || ($client['id'] == '3236') 
        || ($client['id'] == '11982')
        || ($client['id'] == '2126')
        || ($client['id'] == '557')
        || ($client['id'] == '2127')
        ){ ?>

        <div id="pdf-<?= $cont_product['id'] ?>" class="pdf-holder" style="width: 70%; float: right;">
            <div class="pdf-inner-holder">
            <?php

        $result = glob($_SERVER['DOCUMENT_ROOT'] . '/admin/data/containers/' . $link_secret . '/' . $cont_product['container_id'] . '/' . $cont_product['id'] . '/*.pdf');
        if (!empty($result)) {

            foreach ($result as $res) {

                $str = basename($res);

                ?>
            <div style="float: right;">

                <a href="https://docs.google.com/viewerng/viewer?url=https://www.wellnesstrade.cz/admin/data/containers/<?= $link_secret . '/' . $_REQUEST['id'] ?>/<?= $cont_product['id'] ?>/<?= $str ?>"
                    target="_blank" class="btn btn-primary" style="padding: 6px 17px;">
                    <i class="fa fa-file"></i>
                </a>

                <a href="https://www.wellnesstrade.cz/admin/data/containers/<?= $link_secret . '/' . $_REQUEST['id'] ?>/<?= $cont_product['id'] ?>/<?= $str ?>"
                    class="btn btn-blue" style="padding-right: 14px; margin-left: 4px;" download>
                    <i class="entypo-down"></i>
                </a>
                <?php

                if($client['id'] == 2127 ||
                $client['id'] == 557 ||
                $client['id'] == 2142 ||
                $client['id'] == 2126
                ){

                ?>
                <a class="remove_file_hottub btn btn-red" style="padding-right: 14px; margin-left: 4px;"  data-container="<?= $_REQUEST['id'] ?>" data-id="<?= $cont_product['id'] ?>" data-name="<?= $str ?>">
                    <i class="entypo-trash"></i>
                </a>
                <?php } ?>
            </div>
            <?php

            }

        } else {
            ?>
            <form class="file_form_hottub"  style="text-align: center; float: right;" role="form" method="post"
                action="editace-kontejneru?id=<?= $_REQUEST['id'] ?>&hottub_id=<?= $cont_product['id'] ?>&action=add_file_hottub"
                enctype="multipart/form-data" data-id="<?= $cont_product['id'] ?>" data-container="<?= $_REQUEST['id'] ?>">

                <input type="file" style="float: left; height: 36px; margin-right: 4px; width: 70%;" class="form-control" name="fileinput"
                    id="fileinput-<?= $_REQUEST['id'] ?>-<?= $cont_product['id'] ?>" placeholder="Placeholder">

                <button type="submit" class="btn btn-primary btn-sm btn-icon icon-left" style="height: 34px; float: right; width: 28%; padding-left: 29px;"> <i class="entypo-plus" style="line-height: 24px;padding: 5px 4px;"></i>
                    PDF</button>
            </form>
            <?php } ?>
            </div>
        </div>

        <?php } ?>


        <hr>
        <div style="margin: 0; text-align: left; float: left; margin-top: 10px;">

            <a data-id="<?= $cont_product['id'] ?>" style="margin-bottom: 6px"
                class="toggle-modal-transfer btn btn-blue btn-sm" title="Přesunout">
                <i class="entypo-forward"></i>
            </a>

            <a href="editace-kontejneru?id=<?= $cont_product['id'] ?>&action=duplicate" style="margin-bottom: 6px" title="Duplikovat"
                class="btn btn-orange btn-sm">
                <i class="fa fa-copy"></i>
            </a>

            <?php


            /*
            baumel = 557
            becher = 2126
            svoboda = 2127
            šefl = 2142
            valůšková = 2158
            berger = 2190
            pazdersky = 3236
            fajt = 11982
            */

            if($client['id'] == 2127 ||
                $client['id'] == 557 ||
                $client['id'] == 2142 ||
                $client['id'] == 2190 ||
                $client['id'] == 2158 ||
                $client['id'] == 2126 ||
                $client['id'] == 11982
            ){
            ?>


            <a data-id="<?= $cont_product['id'] ?>" style="margin-bottom: 6px"
                class="toggle-modal-edit btn btn-primary btn-sm" title="Upravit">
                <i class="entypo-pencil"></i>
            </a>

            <?php } ?>

            <?php

            if($client['id'] == 2127 ||
                $client['id'] == 557 ||
                $client['id'] == 2142 ||
                $client['id'] == 2126
            ){

            ?>
            <a data-id="<?= $cont_product['id'] ?>" style="margin-bottom: 6px" data-type="container_product"
                class="toggle-modal-remove btn btn-danger btn-sm" title="Smazat">
                <i class="entypo-cancel"></i>
            </a>

            <?php } ?>
        </div>
        <div style="clear:both;"></div>

        <table style="width: 100%; float: left; margin-top: 10px;">
            <tr>
                <td
                    style="background-color: #ace6ce; padding: 4px 5px 2px; color: #000;border-bottom: 1px solid #fff;border-right: 1px solid #fff;">
                    <strong>Spa type</strong></td>
                <td
                    style="background-color: #ace6ce;  padding: 4px 5px 2px; color: #000;border-bottom: 1px solid #fff; text-align: center;">
                    <?= ucfirst($cont_product['product']) ?>
                </td>
            </tr>
            <?php

            if(!empty($cont_product['code'])){ ?>
            <tr>
                <td style="background-color: #ace6ce; padding: 4px 5px 2px; color: #000;border-bottom: 1px solid #fff;border-right: 1px solid #fff;">
                    <strong>Product Code</strong></td>
                <td
                        style="background-color: #ace6ce;  padding: 4px 5px 2px; color: #000;border-bottom: 1px solid #fff; text-align: center;">
                    <?= $cont_product['code'] ?>
                </td>
            </tr>
            <?php
            }

    $provedeni_query = $mysqli->query("SELECT 
       s.bg_colour, s.name, p.value, p.paid, p.paid_text 
    FROM 
         specs s, containers_products_specs_bridge p 
    WHERE 
          s.id = '5'
      AND s.supplier = 1 
      AND p.specs_id = s.id 
      AND p.client_id = '" . $cont_product['id'] . "' 
  ORDER BY 
    s.rank ASC") or die($mysqli->error);
    while ($specs = mysqli_fetch_array($provedeni_query)) {
        ?>
        <tr>
                <td
                    style="vertical-align: middle;width: 44%; background-color: <?= $specs['bg_colour'] ?>; color: #000; padding: 4px 5px 2px; border-bottom: 1px solid #fff;border-right: 1px solid #fff;">
                    <strong>
                        <?= $specs['name'] ?></strong></td>
                <td
                    style="vertical-align: middle;width: auto; background-color: <?= $specs['bg_colour'] ?>;  color: #000; padding: 4px 5px 2px; border-bottom: 1px solid #fff; text-align: center;">
                    <?php
                    if($specs['paid']){ ?><i class="fas fa-asterisk" style="float: left; line-height: 15px; color: #d42020;"
                                             data-toggle="tooltip" data-placement="top" data-original-title="<?php

                        if(!empty($specs['paid_text'])){
                        echo $specs['paid_text'];
                        }else{ echo 'bez dodatečných informací'; }

                        ?>"></i><?php }

                    if ($specs['value'] != '') {echo $specs['value'];} else {echo '-';}

                    ?>


                </td>
            </tr>
    <?php }


    $specsquery = $mysqli->query("SELECT 
       s.bg_colour, s.name, p.value, p.paid, p.paid_text 
    FROM 
         specs s, containers_products_specs_bridge p 
    WHERE 
          s.product = '" . $cont_product['customer'] . "'
      AND s.supplier = 1 
      AND p.specs_id = s.id 
      AND p.client_id = '" . $cont_product['id'] . "' 
      AND s.id <> 5
  ORDER BY 
    s.rank ASC") or die($mysqli->error);
    while ($specs = mysqli_fetch_array($specsquery)) {

        ?>
            <tr>
                <td
                    style="vertical-align: middle;width: 44%; background-color: <?= $specs['bg_colour'] ?>; color: #000; padding: 4px 5px 2px; border-bottom: 1px solid #fff;border-right: 1px solid #fff;">
                    <strong>
                        <?= $specs['name'] ?></strong></td>
                <td
                    style="vertical-align: middle;width: auto; background-color: <?= $specs['bg_colour'] ?>;  color: #000; padding: 4px 5px 2px; border-bottom: 1px solid #fff; text-align: center;">
                    <?php
                    if($specs['paid']){ ?><i class="fas fa-asterisk" style="float: left; line-height: 15px; color: #d42020;"
                                             data-toggle="tooltip" data-placement="top" data-original-title="<?php

                        if(!empty($specs['paid_text'])){
                        echo $specs['paid_text'];
                        }else{ echo 'bez dodatečných informací'; }

                        ?>"></i><?php }

                    if ($specs['value'] != '') {echo $specs['value'];} else {echo '-';}

                    ?>


                </td>
            </tr>
            <?php } ?>

        </table>

        <hr>

        <?php if(!empty($containers['value']) && $containers['value'] == 1){ ?>
            <span style="margin: 0; text-align: left; float: left; margin-top: 10px; padding: 10px 20px;" class="well text-danger">Cena: <strong><?= thousand_seperator($cont_product['purchase_price']) ?></strong> $</span>
        <?php } ?>
    </div>
    <?php

    if ($i % 4 == 0) { ?>
    <div style="clear: both;"></div>
    <?php }

} ?>
</div>





<script type="text/javascript">

    // $('body').on('hidden.bs.modal', '.modal', function () {
    //     $(this).find('.modal-body').empty();
    // });


// Dropzone.autoDiscover = false;

function initDropzones() {
    $('.dropzone').each(function() {

        let dropzoneControl = $(this)[0].dropzone;
        if (dropzoneControl) {
            dropzoneControl.destroy();
        }
    });
}


$(".toggle-modal-remove").click(function(e) {

    $('#remove-modal').removeData('bs.modal');
    e.preventDefault();


    var type = $(this).data("type");

    var id = $(this).data("id");

    $("#remove-modal").modal({

        remote: '/admin/controllers/modals/modal-remove.php?id=' + id + '&type=' + type,
    });
});

$(document).on('click', '.toggle-modal-edit', function(e) {

    $('#edit-container-product-modal').removeData('bs.modal');

    e.preventDefault();

    var id = $(this).data("id");

    $("#edit-container-product-modal").modal({
        remote: '/admin/controllers/modals/modal-edit-container-product.php?id=' + id,
    });
});




$(".toggle-modal-transfer").click(function(e) {

    $('#transfer-modal').removeData('bs.modal');
    e.preventDefault();


    var id = $(this).data("id");

    $("#transfer-modal").modal({

        remote: '/admin/controllers/modals/modal-transfer-containers.php?id=' + id,
    });
});

$(".toggle-picture-upload-modal").click(function() {

    event.preventDefault();

    var container = $(this).data("container");
    var hottub = $(this).data("hottub");
    var link = $(this).data("link");

    $('#picture-upload-modal').find('.modal-title').text('Kontejner ' + container +
        ': Nahrání obrázků k položce ' + hottub);

    initDropzones();

    var myDropzone = new Dropzone('form#dropzone_upload', {
        url: "/admin/controllers/uploads/upload-file-virivka?id=" + container + "&hottub_id=" + hottub + "&link=" + link,
    });

    $("#pictures-result").load("/admin/controllers/modals/modal-containers-pictures?id=" + container +
        "&hottub_id=" + hottub + "&link=" + link);

    $("#picture-upload-modal").modal('show');

});


$(".toggle-technical-upload-modal").click(function() {

    event.preventDefault();

    var container = $(this).data("container");
    var link = $(this).data("link");

    $('#picture-upload-modal').find('.modal-title').text('Kontejner ' + container +
        ': nahrání technických obrázků ');

    initDropzones();

    var myDropzone = new Dropzone('form#dropzone_upload', {

        url: "/admin/controllers/uploads/upload-file-kontejner?id=" + container + "&type=cargo&link=" + link,
    });

    $("#pictures-result").load("/admin/controllers/modals/modal-containers-pictures?id=" + container +
        "&type=cargo" + "&link=" + link);

    $("#picture-upload-modal").modal('show');

});
</script>
<script src="<?= $home ?>/admin/assets/js/jquery-ui.min.js"></script>
<script src="<?= $home ?>/admin/assets/js/bootstrap.min.js"></script>
<script src="<?= $home ?>/admin/assets/js/neon-custom.js"></script>

