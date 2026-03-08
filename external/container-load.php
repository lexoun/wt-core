<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/config/configPublic.php';

$container_products = $mysqli->query("SELECT 
       p.*, DATE_FORMAT(p.date_created, '%d. %m. %Y') as dateformated, d.user_name, DATE_FORMAT(d.realization, '%d. %m. %Y') as realizationformated, d.id as demand_id, d.product as demand_product, w.serial_number as warehouse_number, w.demand_id as has_demand, creator.user_name as creator_user_name, editor.user_name as editor_user_name, wp.supplier_name, wp.brand, wp.code
    FROM containers_products p 
        LEFT JOIN demands d ON d.id = p.demand_id 
        LEFT JOIN warehouse w ON w.id = p.warehouse_id 
        LEFT JOIN warehouse_products wp ON wp.connect_name = p.product 
        LEFT JOIN demands creator ON creator.id = p.creator_id 
        LEFT JOIN demands editor ON editor.id = p.editor_id 
    WHERE p.container_id = '" . $_REQUEST['id'] . "' 
    ORDER BY RIGHT(w.serial_number, 3) ASC") or die($mysqli->error);


$salt = "oijahsfdapsf80efdjnsdjp";

if($_COOKIE['external_pass'] == md5($salt.'XMkbClYqQl')) {

    $brand = 'IQue';
    $link_secret = 'IQU_bgewKD';

}elseif($_COOKIE['external_pass'] == md5($salt.'alEnDaNDLe')){

    $brand = 'Lovia';
    $link_secret = 'LOV_qJcUBZ';

}elseif($_COOKIE['external_pass'] == md5($salt.'aeSDIoewfh')){

    $brand = 'Quantum';
    $link_secret = 'QUA_jEjsaI';

}elseif($_COOKIE['external_pass'] == md5($salt.'AEJaoewiaA')){

    $brand = 'Pergola';
    $link_secret = 'PER_JoifaE';

}elseif($_COOKIE['external_pass'] == md5($salt.'kEonFEIAJp')){

        $brand = 'Espoo Smart';
        $link_secret = 'ESP_fjFSoe';

}elseif($_COOKIE['external_pass'] == md5($salt.'DAIemPOOEN')){

    $brand = 'Espoo Deluxe';
    $link_secret = 'ESS_kSpaeKL';

}

?>

<hr />

<?php

$i = 0;
$files = array('Proforma faktura', 'Shipping Advice', 'Arrival Notice', 'Sea WayBill', 'Invoice', 'Packing list', 'Customs', 'Fotografie nákladu');

foreach ($files as $file) {

    if ($file == 'Fotografie nákladu') { ?>
        <div class="file well col-sm-3" style="min-height: 134px; width: 11.7%; float: left; margin: 0px 5px 0 5px; padding: 4px; height: 134px;">


            <div class="profile-stories" style="margin-bottom: 10px;">

                <h4 style="margin: 6px 0px 11px; text-align: center; font-size: 12px;">Photos of Cargo</h4>

                <article class="story" style="margin: 0 0 0 0; min-height: 20px;margin-top: 34px">

                    <center>
                        <?php $result = glob($_SERVER['DOCUMENT_ROOT'] . '/admin/data/containers/' . $link_secret .'/'. $_REQUEST['id'] . '/cargo/small_*.*');
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

        <div id="<?= $_REQUEST['id'].'-'.$i ?>" class="file well col-sm-3" style=" min-height: 134px; width: 11.7%; float: left; margin: 0px 5px 0 5px; padding: 4px">

            <div class="holder">

                <?php $result = glob($_SERVER['DOCUMENT_ROOT'] . '/admin/data/containers/' . $link_secret .'/' . $_REQUEST['id'] . '/' . $file . '.*');

                if (!empty($result)) {

                foreach ($result as $res) { ?>

                    <div class="profile-stories">

                        <article class="story" style="margin: 0; min-height: 89px;">

                            <div style="display: block;">
                    <span id="demandus" style=" width: 100%;">
                        <h4 style="margin: 6px 0px 6px; text-align: center; font-size: 12px;">
                            <?php if($file == 'Proforma faktura'){ echo 'Proforma Invoice'; }else{ echo $file; }?>
                        </h4>
                        <h5 style="margin: 2px 0px 11px; text-align: center; font-size: 11px;">
                            <?= date('d. m. Y H:i', filemtime($res)) ?>
                        </h5>
                        <?php

                        $ext = pathinfo($res, PATHINFO_EXTENSION);

                        if (in_array($ext, $image_extensions)) { ?>
                            <center><a
                                    href="https://www.wellnesstrade.cz/admin/data/containers/<?= $link_secret .'/'.$_REQUEST['id'] ?>/<?= basename($res) ?>"
                                    target="_blank"><img
                                        src="https://www.wellnesstrade.cz/admin/data/containers/<?= $link_secret .'/'.$_REQUEST['id'] ?>/<?= basename($res) ?>"
                                        width="auto" style="max-width: 100%;" class="img-rounded"></a></center>
                        <?php } else { ?>
                            <center>
                            <a href="https://docs.google.com/viewerng/viewer?url=https://www.wellnesstrade.cz/admin/data/containers/<?= $link_secret .'/'.$_REQUEST['id'] ?>/<?= basename($res) ?>"
                               target="_blank">
                                <i class="fa fa-file" style="font-size: 50px;"></i>
                            </a>
                        </center>

                        <?php } ?>
                        <div class="text-center" style="margin: 12px 0 8px;">

                            <a href="https://www.wellnesstrade.cz/admin/data/containers/<?= $link_secret .'/'.$_REQUEST['id'] ?>/<?= basename($res) ?>"
                               class="btn btn-blue" style="padding-right: 14px;" download>
                                <i class="entypo-down"></i>
                            </a>
                            <a class="remove-file btn btn-red" style="padding-right: 14px; margin-left: 4px;" data-name="<?= basename($res) ?>" data-id="<?= $i ?>" data-container="<?= $_REQUEST['id'] ?>">
                                <i class="entypo-trash"></i>
                            </a>

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

                        <h4 style="margin: 6px 0px 11px; text-align: center; font-size: 12px;">
                            <?php if($file == 'Proforma faktura'){ echo 'Proforma Invoice'; }else{ echo $file; }?>
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

} ?>
<div style="clear: both;"></div>
<hr />
<div class="row info-list">
    <?php

    $i = 0;

    while ($cont_product = mysqli_fetch_array($container_products)) {
        $i++;

        ?>
        <div id="<?= $cont_product['id'] ?>" class="well col-sm-3" style="width: 24.2%; float: left; margin: 20px 0.4% 0; padding: 4px">
            <div style="float: left; width: 100%;">
                <a class="member-img" style="width: 50px; margin: 8px 12px 8px 8px;background-color: #ececec;border-radius: 5px;border: 1px solid #d5d5d5;">
                    <img src="https://www.wellnesstrade.cz/admin/data/images/customer/<?= $cont_product['product'] ?>.png"
                         width="50px" class="img-rounded" alt="<?= $cont_product['dateformated'] ?>" />
                </a>
                <h5 style="font-size: 15px;line-height: 23px;margin-top: 10px;">

                    <?php if ($cont_product['warehouse_id'] != 0) {

                        echo '#' . $cont_product['id'] . ': #' . $cont_product['warehouse_number'] . ' ' . ucfirst($cont_product['supplier_name']);

                    } else {

                        echo '#' . $cont_product['id'] . ' ' . ucfirst($cont_product['supplier_name']);

                    } ?>

                    <?php if(!empty($cont_product['code'])){ ?>
                        <br><small><?= $cont_product['code'] ?></small>
                    <?php } ?>
                </h5>

                <p style="margin-bottom: 0;">
                    <?php if (isset($cont_product['has_demand']) && $cont_product['has_demand'] != 0) {

                        echo '<strong style="color: #d53e29;"><i class="fas fa-arrow-right"></i> Sold</strong>';

                    } elseif (isset($cont_product['demand_id']) && $cont_product['demand_id'] != 0) {

                        echo '<strong style="color: #d53e29;"><i class="fas fa-arrow-right"></i> Sold</strong>';

                    }else{

                        echo '<strong style="color: #00a651;"><i class="fas fa-arrow-right"></i> Free</strong>';

                    }

                    ?>
                </p>
                <hr style="float:left; width: 100%;">
            </div>


            <?php

            $result = glob($_SERVER['DOCUMENT_ROOT'] . '/admin/data/containers/' . $link_secret .'/'.$cont_product['container_id'] . '/' . $cont_product['id'] . '/small_*.*');

            ?>
            <a data-container="<?= $cont_product['container_id'] ?>"
               data-hottub="<?= $cont_product['id'] ?>"
               data-link="<?= $link_secret ?>"
               class="toggle-picture-upload-modal btn <?php if (!empty($result)) { ?>btn-green<?php } else { ?>btn-primary<?php } ?> btn-sm btn-icon icon-left"  style="max-width: 30%; height: 34px; line-height: 24px; padding-left: 29px;">
                <i class="entypo-plus" style="line-height: 24px;padding: 5px 4px;"></i>
                Foto
            </a>


            <div id="pdf-<?= $cont_product['id'] ?>" class="pdf-holder" style="width: 70%; float: right;">
                <div class="pdf-inner-holder">
                    <?php

                    $result = glob($_SERVER['DOCUMENT_ROOT'] . '/admin/data/containers/' . $link_secret .'/'.$cont_product['container_id'] . '/' . $cont_product['id'] . '/*.pdf');
                    if (!empty($result)) {

                        foreach ($result as $res) {

                            $str = basename($res);

                            ?>
                            <div style="float: right;">

                                <a href="https://docs.google.com/viewerng/viewer?url=https://www.wellnesstrade.cz/admin/data/containers/<?= $link_secret .'/'. $_REQUEST['id'] ?>/<?= $cont_product['id'] ?>/<?= $str ?>"
                                   target="_blank" class="btn btn-primary" style="padding: 6px 17px;">
                                    <i class="fa fa-file"></i>
                                </a>

                                <a href="https://www.wellnesstrade.cz/admin/data/containers/<?= $link_secret .'/'.$_REQUEST['id'] ?>/<?= $cont_product['id'] ?>/<?= $str ?>"
                                   class="btn btn-blue" style="padding-right: 14px; margin-left: 4px;" download>
                                    <i class="entypo-down"></i>
                                </a>
                                <a class="remove_file_hottub btn btn-red" style="padding-right: 14px; margin-left: 4px;" data-id="<?= $cont_product['id'] ?>" data-name="<?= $str ?>" data-container="<?= $_REQUEST['id'] ?>">
                                    <i class="entypo-trash"></i>
                                </a>
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
            <hr>

            <div style="clear:both;"></div>

            <table class="table table-bordered table-hover" style="width: 100%; float: left; margin-top: 10px; font-size: 12px;letter-spacing: -0.022em;">
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

                $specsquery = $mysqli->query("SELECT s.bg_colour, s.name_en, p.value, sp.option_en FROM specs s, containers_products_specs_bridge p LEFT JOIN specs_params sp ON sp.option = p.value WHERE s.product = 1 AND s.supplier = 1 AND p.specs_id = s.id AND p.client_id = '" . $cont_product['id'] . "' GROUP BY s.id order by s.rank asc") or die($mysqli->error);
                while ($specs = mysqli_fetch_array($specsquery)) {

                    ?>
                    <tr>
                        <td
                            style="vertical-align: middle; width: 40%; background-color: <?= $specs['bg_colour'] ?>; color: #000; padding: 4px 5px 2px; border-bottom: 1px solid #fff;border-right: 1px solid #fff;">
                            <strong>
                                <?= $specs['name_en'] ?></strong></td>
                        <td
                            style="vertical-align: middle; width: auto; background-color: <?= $specs['bg_colour'] ?>;  color: #000; padding: 4px 5px 2px; border-bottom: 1px solid #fff; text-align: center;">
                            <?php if(!empty($specs['option_en'])) { echo $specs['option_en'];

                            } elseif($specs['value'] == 'Ano') {

                                echo 'Yes';

                            } elseif($specs['value'] == 'Ne') {

                                echo 'No';

                            }  else {

                                echo '-';

                            }  ?>
                        </td>
                    </tr>
                <?php } ?>

            </table>
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
            "&type=cargo&link=" + link);

        $("#picture-upload-modal").modal('show');


    });
</script>

