<?php

include $_SERVER['DOCUMENT_ROOT'] . '/admin/config/config.php';

include INCLUDES . '/googlelogin.php';
include INCLUDES . '/functions.php';

use Granam\CzechVocative\CzechName;

$redirect_url = urlencode('pages/demands/zobrazit-poptavku?' . $_SERVER['QUERY_STRING']);

$id = $_REQUEST['id'];

/*
$demand = $em->find('Demand', $id);

$orders = $demand->getOrder();
*/

//print_r($orders);

/*
foreach($orders as $singleOrder){
    echo $singleOrder->getId().'<br>';
}
*/


$getclientquery = $mysqli->query('SELECT *, d.customer as customer, d.id as id, DATE_FORMAT(d.date, "%d. %m. %Y") as dateformated, DATE_FORMAT(d.realization, "%d. %m. %Y") as realizationformated, DATE_FORMAT(d.realtodate, "%d. %m. %Y") as realtodateformat, d.active as activated 
    FROM demands d 
    LEFT JOIN warehouse_products p ON p.connect_name = d.product
    LEFT JOIN shops_locations l ON l.id = d.showroom
WHERE d.id="' . $id . '"') or die($mysqli->error);

if (mysqli_num_rows($getclientquery) > 0) {
    $getclient = mysqli_fetch_assoc($getclientquery);

    $billing_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $getclient['shipping_id'] . '" WHERE b.id = "' . $getclient['billing_id'] . '"') or die($mysqli->error);
    $billing = mysqli_fetch_assoc($billing_query);

    $title = $getclient['user_name'];

    if (isset($getclient['customer']) && $getclient['customer'] == 3 && $getclient['secondproduct'] != 'custom') {
        $second_product_query = $mysqli->query("SELECT brand, fullname FROM warehouse_products WHERE connect_name = '" . $getclient['secondproduct'] . "'") or die($mysqli->error);
        $second_product = mysqli_fetch_array($second_product_query);
    }

    include $_SERVER['DOCUMENT_ROOT'] . '/admin/pages/demands/parts/requests.php';

    $spesl = ' - Poptávka';

    $pagetitle = $title;

    $admin_link = '';
    if($client['role'] ==  "salesman" || $client['role'] == "salesman-technician"){

        $admin_link = '&admin_id='.$client['id'];

    }

    $bread1 = 'Editace poptávek';
    $abread1 = 'editace-poptavek?status=1'.$admin_link;

    $abread2 = 'editace-poptavek?status='.$getclient['status'].$admin_link;

    if($getclient['status'] == 1){

        $bread2 = 'Nezpracované';

    }elseif($getclient['status'] == 2){

        $bread2 = 'Zhotovené nabídky';

    }elseif($getclient['status'] == 3){

        $bread2 = 'V řešení';

    }elseif($getclient['status'] == 12){

        $bread2 = 'Prodané';

    }elseif($getclient['status'] == 4){

        $bread2 = 'Realizace';

    }elseif($getclient['status'] == 8){

        $bread2 = 'Nedokončené';

    }elseif($getclient['status'] == 5){

        $bread2 = 'Hotové';

    }elseif($getclient['status'] == 7){

        $bread2 = 'Odložené';

    }elseif($getclient['status'] == 6){

        $bread2 = 'Stornované';

    }elseif($getclient['status'] == 15){

        $bread2 = 'Nová realizace';

    }elseif($getclient['status'] == 14){

        $bread2 = 'Neobjednané vířivky';

    }elseif($getclient['status'] == 13){

        $bread2 = 'Dokončené';

    }

    $locations_query = $mysqli->query("SELECT * FROM shops_locations WHERE type = 'branch'") or die($mysqli->error);
    while ($location = mysqli_fetch_array($locations_query)) {
        $locationsArray[] = $location;
    }


    $findContainer = $mysqli->query("SELECT p.id, p.container_id, w.serial_number, c.container_name, w.description, c.id_brand FROM containers_products p LEFT JOIN warehouse w ON w.id = p.warehouse_id LEFT JOIN containers c ON c.id = p.container_id WHERE p.demand_id = '$id'") or die($mysqli->error);

    $warehouseQuery = $mysqli->query("SELECT *, w.id as id, DATE_FORMAT(w.loadingdate, '%d. %m. %Y') as dateformated FROM warehouse w, warehouse_products p WHERE w.product = p.connect_name AND w.demand_id = '".$getclient['id']."'") or die($mysqli->error);


    if (isset($getclient['status']) && $getclient['status'] > 3) {
        require_once CONTROLLERS . '/lati-longi.php';
    }

    include VIEW . '/default/header.php';
    ?>


<script type="text/javascript">


jQuery(document).ready(function($) {

    $('#zmrdsample').wysihtml5({
        "font-styles": true, //Font styling, e.g. h1, h2, etc. Default true
        "emphasis": true, //Italics, bold, etc. Default true
        "lists": true, //(Un)ordered lists, e.g. Bullets, Numbers. Default true
        "html": false, //Button which allows you to edit the generated HTML. Default false
        "link": false, //Button to insert a link. Default true
        "image": true, //Button to insert an image. Default true,
        "color": false, //Button to change color of font
        "blockquote": true, //Blockquote
        "style": { "remove": 1 }

    });


    $('#technical_zmrdsample').wysihtml5({
        "font-styles": true, //Font styling, e.g. h1, h2, etc. Default true
        "emphasis": true, //Italics, bold, etc. Default true
        "lists": true, //(Un)ordered lists, e.g. Bullets, Numbers. Default true
        "html": false, //Button which allows you to edit the generated HTML. Default false
        "link": false, //Button to insert a link. Default true
        "image": true, //Button to insert an image. Default true,
        "color": false, //Button to change color of font
        "blockquote": true, //Blockquote
        "style": { "remove": 1 }
    });




    // $('#zmrdsample').hide();
    // $('#technical_edit_description').hide();


    $("#newstatus").submit(function (e) {

        var status = $(this).find('.status').val();
        var shop_id = <?= $getclient['woocommerce_id'] ?>

        if((status == 5 || status == 13 || status == 8) && shop_id == 0){

                $('#default-modal').removeData('bs.modal');
                e.preventDefault();

                $("#default-modal").modal({

                    remote: '/admin/controllers/modals/default.php?id=<?= $getclient['id'] ?>&type=finishClient&status='+status,
                });

            return false;

        }

    });


    $('#selectbox-o').select2({
        minimumInputLength: 2,
        ajax: {
            url: "/admin/data/autosuggest-custom.php",
            dataType: 'json',
            data: function(term, page) {
                return {
                    q: term,
                    site: 'wellnesstrade'
                };
            },
            results: function(data, page) {
                return {
                    results: data
                };
            }
        }
    });


    $('#selectbox-o').on("change", function(e) {


        var vlue = $("#selectbox-o").select2("val");

        var nema = $(".select2-chosen").text();

        $('#specification_copy').clone(true).insertBefore("#duplicate_specification").attr('id',
            'copied').addClass('has-success').show();

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


    // REALIZACE VÍŘIVKY ZAČÁTEK //

    $("#sendrealization").click(function() {

        var url = "/admin/controllers/info_save"; // the script where you handle the form input.

        $.ajax({
            type: "POST",
            url: url,
            data: $("#realizationdate").serialize(), // serializes the form's elements.
            success: function(data) {
                $("#wtf").html($.datepicker.formatDate('dd. mm. yy', new Date(data)));
                $("#wtf2").html($.datepicker.formatDate('dd. mm. yy', new Date(data)));

                $("#wtfinvis").html(data);
                $("#realizationdate").hide("clip", "slow");
                $("#realizationtext").show("clip", "slow");
            }
        });

        return false; // avoid to execute the actual submit of the form.
    });


    $('#realizationtext').click(function() {
        $("#realizationtext").hide("clip", "slow");
        setTimeout(function() {
            $("#realizationdate").show("slow");
        }, 540);
    });


    $('#cancelrealization').click(function() {

        var datanew = $("#wtfinvis").html();
        $('#realzmrd').val(datanew);
        $("#realizationdate").hide("slow");
        setTimeout(function() {
            $("#realizationtext").show("clip", "slow");
        }, 540);
    });

    // KONEC REALIZACE VÍŘIVKY //



    // REALIZACE SAUNY - POUZE POKUD JE POPTÁVKA PRO SAUNU A VÍŘIVKU //


    $("#sendrealizationsauna").click(function() {

        var url = "/admin/controllers/info_save"; // the script where you handle the form input.

        $.ajax({
            type: "POST",
            url: url,
            data: $("#realizationdatesauna").serialize(), // serializes the form's elements.
            success: function(data) {
                $("#wtfsauna").html($.datepicker.formatDate('dd. mm. yy', new Date(
                    data)));
                $("#wtf2sauna").html($.datepicker.formatDate('dd. mm. yy', new Date(
                    data)));

                $("#wtfinvissauna").html(data);
                $("#realizationdatesauna").hide("clip", "slow");
                $("#realizationtextsauna").show("clip", "slow");
            }
        });

        return false; // avoid to execute the actual submit of the form.
    });


    $('#realizationtextsauna').click(function() {
        $("#realizationtextsauna").hide("clip", "slow");
        setTimeout(function() {
            $("#realizationdatesauna").show("slow");
        }, 540);
    });


    $('#cancelrealizationsauna').click(function() {

        var datanew = $("#wtfinvissauna").html();
        $('#realzmrdsauna').val(datanew);
        $("#realizationdatesauna").hide("slow");
        setTimeout(function() {
            $("#realizationtextsauna").show("clip", "slow");
        }, 540);
    });

    // KONEC REALIZACE SAUNY //

    $("#sendform").click(function() {

        var url = "/admin/controllers/info_save"; // the script where you handle the form input.

        $.ajax({
            type: "POST",
            url: url,
            data: $("#editdescription").serialize(), // serializes the form's elements.
            success: function(data) {
                $(".descriptiontext").html(data);
                $("#editdescription").hide("slow");
                $(".descriptiontext").show("slow");
            }
        });

        return false; // avoid to execute the actual submit of the form.
    });





    $("#send_technical").click(function() {

        var url =
            "/admin/controllers/technical_info_save"; // the script where you handle the form input.

        $.ajax({
            type: "POST",
            url: url,
            data: $("#technical_edit_description")
                .serialize(), // serializes the form's elements.
            success: function(data) {
                $(".technical_description_text").html(data);
                $("#technical_edit_description").hide("slow");
                $(".technical_description_text").show("slow");
            }
        });

        return false; // avoid to execute the actual submit of the form.
    });


    $('#changestatus').click(function() {
        $("#changestatus").hide("clip", "slow");
        setTimeout(function() {
            $("#newstatus").show("slow");
        }, 540);

    });


    $('#cancelchangestatus').click(function() {

        $("#newstatus").hide("slow");
        setTimeout(function() {
            $("#changestatus").show("clip", "slow");
        }, 540);
    });

    $('#addspecification').click(function() {
        $("#addspecification").hide("clip", "slow");
        setTimeout(function() {
            $("#specificationform").show("slow");
        }, 540);

    });


    $('#cancelspecification').click(function() {

        $("#specificationform").hide("slow");
        setTimeout(function() {
            $("#addspecification").show("clip", "slow");
        }, 540);
    });





    $('#addtask').click(function() {
        $("#addtask").hide("slow");
        $("#taskform").show("slow");
    });

    $('#canceladdtask').click(function() {

        $("#taskform").hide("slow");
        $("#addtask").show("slow");
    });

    $('.descriptiontext').click(function() {
        $(".descriptiontext").hide("slow");
        $("#editdescription").show("slow");

    });

    $('#canceledit').click(function() {

        // var datanew = $(".descriptiontext").html();

        // $('#zmrdsample').val(datanew);

        $("#editdescription").hide("slow");
        $(".descriptiontext").show("slow");

    });



    $('#show_products').click(function() {


        $("#products").toggle("slow");

    });



    $('.technical_description_text').click(function() {
        $(".technical_description_text").hide("slow");
        $("#technical_edit_description").show("slow");

    });

    $('#technical_cancel_edit').click(function() {

        // var datanew = $(".technical_description_text").html();

        // $('#technical_zmrdsample').val(datanew);

        $("#technical_edit_description").hide("slow");
        $(".technical_description_text").show("slow");

    });



    $('#add_contract').click(function() {
        $("#add_contract").hide("slow");
        setTimeout(function() {
            $("#contract").show("slow");
        }, 540);
    });



    $('#cancel_contract').click(function() {
        $("#contract").hide("slow");
        setTimeout(function() {
            $("#add_contract").show("slow");
        }, 540);
    });




    $('#adress').click(function() {
        if ($("#sample-checkin").is(":visible")) {
            $("#sample-checkin").hide("slow");
        } else {
            $("#sample-checkin").show("slow");
            setTimeout(function() {

                showmap();
            }, 540);

        }
    });

    $('.showprodukticek').click(function() {
        if ($("#produkticek").is(":visible")) {
            $("#produkticek").hide("slow");
        } else {
            $("#produkticek").show("slow");
        }
    });

    $('#duplicatereciever').click(function() {

        $('#reciever').clone().insertAfter("#reciever");

    });



});
</script>

            <?php
if ($access_edit) {
        ?>
    <div class="col-sm-3" style="width: 25%; text-align: center; left: 280px; float: left; margin-top: -50px;">
        <?php
		if($getclient['rating'] == 0){

			$rating = '-';

		}else{
			$rating = '';
			for($getclient['rating']; $getclient['rating'] > 0; $getclient['rating']--){

				$rating .= '<img src="/admin/assets/images/star_2.png" style="margin-right: 4px;">';

			}
        }
        echo $rating;

    ?>
    </div>


            <div class="col-sm-5" style="width: 50%; float: right; margin-top: -53px;">

                <div class="profile-buttons" style="float: right; margin-right: 4px;">



                    <a href="javascript:;" onclick="jQuery('#demand_sms').modal('show');" class="btn btn-default"
                       style="margin-left: 11px; margin-right: 8px;" data-toggle="tooltip" data-placement="top"
                       title="" data-original-title="Odeslat nabídkovou SMS">
                        <i class="fas fa-sms"></i>
                    </a>

                    <span style=" border-right: 1px solid #cccccc;"></span>

                    <?php

        $get_brand = $mysqli->query("SELECT brand FROM warehouse_products WHERE connect_name = '".$getclient['product']."'")or die($mysqli->error);
        $brand = mysqli_fetch_assoc($get_brand);

        if (isset($getclient['status']) && ($getclient['status'] != 5 && $getclient['status'] != 8 && $getclient['status'] != 13)) {
            if (!isset($findContainer) || mysqli_num_rows($findContainer) != 1) {
                ?>
                    <a href="javascript:;" onclick="jQuery('#container_modal').modal('show');" class="btn btn-default"
                        style="margin-left: 11px; margin-right: 8px;" data-toggle="tooltip" data-placement="top"
                        title="" data-original-title="Přidat do kontejneru">
                        <i class="fas fa-dolly-flatbed"></i>
                    </a>
            <?php
            } elseif (isset($findContainer)) {

                $found_product = mysqli_fetch_array($findContainer);

                ?>
                    <a href="/admin/pages/warehouse/editace-kontejneru?brand=<?= $brand['brand'] ?>" class="btn btn-green" style="margin-left: 11px; margin-right: 8px;"
                        data-toggle="tooltip" data-placement="top" title=""
                        data-original-title="Položka #<?= $found_product['id'] ?> v kontejneru #<?= $found_product['id_brand'] ?>">
                        <i class="fas fa-dolly-flatbed"></i>
                    </a>
                    <?php
            } ?>
                    <span style=" border-right: 1px solid #cccccc;"></span>
                    <?php
        }

            if (($getclient['status'] == 5 || $getclient['status'] == 13 || $getclient['status'] == 8) && $getclient['woocommerce_id'] == 0) {
                ?>
                    <a data-id="<?= $getclient['id'] ?>" style="margin-right: 8px;" data-type="createUser" class="toggle-default-modal btn btn-default" data-toggle="tooltip" data-placement="top"
                        title="" data-original-title="Vytvořit uživatele">
                        <i class="entypo-user"></i>
                    </a>
                <span style=" border-right: 1px solid #cccccc;"></span>
                    <?php
} elseif (($getclient['status'] == 5 || $getclient['status'] == 13 || $getclient['status'] == 8) && $getclient['woocommerce_id'] != 0) { ?>
                    <a data-id="<?= $getclient['id'] ?>" style="margin-right: 8px;" data-type="resetUserPassword" class="toggle-default-modal btn btn-success" data-toggle="tooltip" data-placement="top"
                        title="" data-original-title="Uživatel byl již vytvořen pod ID <?= $getclient['woocommerce_id'] ?>.
                        Kliknutím můžete znovu poslat úvodní přihlašovací údaje.">
                        <i class="entypo-user"></i>
                    </a>
                <span style=" border-right: 1px solid #cccccc;"></span>

                <?php

            }
         ?>


                    <a href="/admin/pages/services/pridat-servis?service=new&client=<?= $getclient['id'] ?>"
                        style="margin-left: 11px;" class="btn btn-default" data-toggle="tooltip" data-placement="top"
                        title="" data-original-title="Přidat servis">
                        <i class="entypo-tools"></i>
                    </a>

                    <a href="/admin/pages/orders/vytvorit-objednavku?order=new&client=<?= $getclient['id'] ?>" class="btn btn-default" data-toggle="tooltip" data-placement="top"
                       title="" data-original-title="Vytvořit objednávku">
                        <i class="entypo-basket"></i>
                    </a>

                    <a href="/admin/pages/documents/pridat-smlouvu?id=<?= $getclient['id'] ?>"
                        style="margin-right: 8px;" class="btn btn-default" data-toggle="tooltip" data-placement="top"
                        title="" data-original-title="Přidat dokument">
                        <i class="entypo-book"></i>
                    </a>

                    <span style=" border-right: 1px solid #cccccc;"></span>
                    <a href="./upravit-poptavku?id=<?= $getclient['id'] ?>&type=<?= $getclient['status'] ?>"
                        class="btn btn-default" style="margin-left: 11px; margin-right: 8px;" data-toggle="tooltip"
                        data-placement="top" title="" data-original-title="Upravit poptávku">
                        <i class="entypo-pencil"></i>
                    </a>



                    <?php if (isset($getclient['status']) && ($getclient['status'] != 5 && $getclient['status'] != 4 && $getclient['status'] != 8 && $getclient['status'] != 13)) { ?>

                    <span style=" border-right: 1px solid #cccccc;"></span>

                    <?php if (isset($getclient['status']) && $getclient['status'] == 6) { ?>
                    <a href="./zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=restore" class="btn btn-red"
                        style="margin-left: 11px;" data-toggle="tooltip" data-placement="top" title=""
                        data-original-title="Zrušit stornování poptávky">
                        <i class="entypo-cancel-circled"></i>
                    </a>
                    <?php } else { ?>
                    <a href="./zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=cancel"
                        class="btn btn-default" style="margin-left: 11px;" data-toggle="tooltip" data-placement="top"
                        title="" data-original-title="Stornovat poptávku">
                        <i class="entypo-cancel-circled"></i>
                    </a>
                    <?php } ?>
                    <a data-type="demand" data-id="<?= $getclient['id'] ?>" class="btn btn-default toggle-modal-remove"
                        data-toggle="tooltip" data-placement="top" title="" data-original-title="Odstranit poptávku">
                        <i class="entypo-trash"></i>
                    </a>
                    <?php } ?>


                </div>
            </div>
            <?php
} ?>

            <div id="produkticek" style=" margin-bottom: 66px; display: none;">

                <?php

    $demand_id = $getclient['id'];
    $virivkaid = 0;
    $saunaid = 0;

    if (mysqli_num_rows($warehouseQuery) > 0) {

        include $_SERVER['DOCUMENT_ROOT'] . '/admin/controllers/views/product-view.php';

    } elseif (isset($findContainer) && mysqli_num_rows($findContainer) == 1) {

        include $_SERVER['DOCUMENT_ROOT'] . '/admin/controllers/views/product-view-containers.php';

    } ?>

                <div style="text-align: center; margin-top: 20px;">
                <?php
                if(isset($warehouseQuery) && mysqli_num_rows($warehouseQuery) == 1){

                    mysqli_data_seek($warehouseQuery, 0);
                    $found_product = mysqli_fetch_array($warehouseQuery);

                    ?>
                    <a href="../warehouse/upravit-virivku?id=<?= $found_product['id'] ?>>&redirect=<?= $getclient['id'] ?>>" style="margin-bottom: 6px"
                       class="btn btn-primary btn-lg btn-icon icon-left" title="Upravit">
                        <i class="entypo-pencil"></i> Upravit hodnoty na skladě
                    </a>
                <?php } ?>

                <?php
                if(isset($findContainer) && mysqli_num_rows($findContainer) == 1){

                    mysqli_data_seek($findContainer, 0);
                    $found_product = mysqli_fetch_array($findContainer);

                    ?>
                    <a data-id="<?= $found_product['id'] ?>" style="margin-bottom: 6px"
                       class="toggle-modal-edit btn btn-orange btn-lg btn-icon icon-left" title="Upravit">
                        <i class="entypo-pencil"></i> Upravit hodnoty v kontejneru
                    </a>
                <?php } ?>
                </div>
            </div>


            <div id="sample-checkin" class="map-checkin"
                style=" background: #f0f0f0; height: 400px !important; max-height: 400px !important; overflow: hidden; width: 100%; display: none; margin-bottom: 40px;">
            </div>

            <div class="profile-env" style="float: left; width: 100%;">

                <header class="row"
                    style="<?php if (isset($getclient['customer']) && $getclient['customer'] == 3) { ?>min-height: 172px;<?php } else { ?>min-height: 115px;<?php } ?> margin-top: 0;">


                    <?php if ($getclient['customer'] != 3) { ?>
                    <div class="col-sm-2" style="width: 10%;">
                        <a class="profile-picture">
                            <img src="https://www.wellnesstrade.cz/admin/data/images/customer/<?= $getclient['product'] ?>.png"
                                class="img-responsive img-circle" />
                        </a></div>
                    <?php } else { ?>
                    <div class="col-sm-6" style="width: 70%;margin-top: -24px;">
                        <div class="col-sm-2" style="width: 180px;padding-right: 0px">
                            <a class="profile-picture">
                                <img src="https://www.wellnesstrade.cz/admin/data/images/customer/<?= $getclient['product'] ?>.png"
                                    class="img-responsive img-circle"
                                    style="width: 47%; margin-right: 10px; float:left;" />
                            </a>
                            <a class="profile-picture">
                                <img src="https://www.wellnesstrade.cz/admin/data/images/customer/<?= $getclient['secondproduct'] ?>.png"
                                    class="img-responsive img-circle" style="width: 46%; float:right;" />
                            </a></div>

                        <div class="col-sm-5" style="width: 40%; padding-right: 0;">
                            <ul class="profile-info-sections">
                                <li style="padding-right: 26px;">
                                    <div class="profile-name">
                                        <strong>
                                            <a style="  font-weight: 500;"><?php if ($billing['billing_degree'] != '') {
        echo $billing['billing_degree'] . ' ';
    }
        echo $pagetitle; ?></a>
                                            <!-- User statuses available classes "is-online", "is-offline", "is-idle", "is-busy" -->
                                        </strong>
                                        <?php
if (mysqli_num_rows($warehouseQuery) > 0) { ?>


                                        <?php if ($getclient['customer'] != 3) { ?><span class="showprodukticek"
                                            style="color: #00a651; text-decoration: underline;"><a
                                                style="color: #00a651; cursor: pointer;"><i style="font-size: 12px;"
                                                    class="entypo-check"></i><?php echo $name . ' ';
            echo $name_title; ?></a></span>
                                        <?php } else { ?>

                                        <span class="showprodukticek"
                                            style="cursor: pointer;"><?php if ($virivka == 'yes') { ?><a
                                                style="color: #00a651; text-decoration: underline;"><i
                                                    style="font-size: 12px;" class="entypo-check"></i><?php echo $name . ' ' . $name_title . '</a>';
        } else {

            echo $getclient['brand'] . ' ' . ucfirst($getclient['fullname']);

        } ?> a <?php if (isset($getclient['secondproduct']) && $getclient['secondproduct'] == 'custom') {
            echo 'Sauna na míru';
        } elseif ($sauna == 'yes') {
            echo '<a style="color: #00a651; text-decoration: underline;"><i style="font-size: 12px;" class="entypo-check"></i>#' . $saunaid . ' ' . $second_product['brand'] . ' ' . ucfirst($second_product['fullname']) . '</a>';
        } else {
            echo $second_product['brand'] . ' ' . ucfirst($second_product['fullname']);
        } ?></span>

                                        <?php
}
        } else { ?>
                                        <?php if ($getclient['customer'] != 3) { ?><span><?php if (isset($getclient['product']) && $getclient['product'] == 'custom') { ?>Sauna
                                            na míru<?php } else {
            echo $getclient['brand'] . ' ' . ucfirst($getclient['fullname']);
        } ?> </span><?php } else { ?>
                                        <span><?= $getclient['brand'] . ' ' . ucfirst($getclient['fullname']) ?>
                                            a <?php if (isset($getclient['secondproduct']) && $getclient['secondproduct'] == 'custom') {
            echo 'Sauna na míru';
        } else {
            echo $second_product['brand'] . ' ' . ucfirst($second_product['fullname']);
        } ?></span>
                                        <?php } ?>
                                        <?php } ?>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <?php } ?>

                    <div class="col-sm-5"
                        style="    <?php if ($getclient['customer'] != 3) { ?>width: 88%;<?php } else { ?>width: 100%;<?php } ?> padding-right: 0;">

                        <ul class="profile-info-sections">
                            <?php if ($getclient['customer'] != 3) { ?>
                            <li style="padding-right: 26px;">
                                <div class="profile-name">
                                    <?php

                                    $parts = explode(" ", $getclient['user_name']);
                                    $firstname = array_shift($parts);
                                    $lastname = array_pop($parts);

                                    $female = false;
                                    if(!empty($lastname)){

                                        $vocativ = new CzechName();

                                        $genderFirst = $vocativ->isMale($firstname);
                                        $genderSecond = $vocativ->isMale($lastname);

                                        if($genderFirst && $genderSecond){

                                            $preName = 'pane';

                                        }elseif($genderSecond){

                                            $preName = 'pane';

                                        }else{

                                           $preName = 'paní';
                                           $female = true;

                                        }

                                        echo $preName.' '.$vocativ->vocative($lastname);

                                    }

                                    ?>
                                    <strong>
                                        <a style="  font-weight: 500;"><?php if ($billing['billing_degree'] != '') {
        echo $billing['billing_degree'] . ' ';
    }
        echo $pagetitle; ?></a>
                                          </strong>
                                    <?php
if (mysqli_num_rows($warehouseQuery) > 0) { ?>


                                    <?php if ($getclient['customer'] != 3) { ?><span class="showprodukticek"
                                        style="color: #00a651;"><a
                                            style="color: #00a651; cursor: pointer;">
             <?php

             if($warehouse['reserved'] == 1){ echo '<span style="color: #cc2423; text-decoration: none; margin: 4px 0 2px; font-size: 12px;">Rezervace do '.date('d. m. Y', strtotime($warehouse['reserved_date'])).'</span>'; } ?>
            <i style="font-size: 12px;"
                                                class="entypo-check"></i><?php echo $name . ' ';
            echo $name_title; ?></a></span>
                                    <?php } else { ?>

                                    <span class="showprodukticek"
                                        style="cursor: pointer;"><?php if ($virivka == 'yes') { ?><a
                                            style="color: #00a651; text-decoration: underline;"><i
                                                style="font-size: 12px;" class="entypo-check"></i><?php echo $name . ' ' . $name_title . '</a>';} else {
            echo $getclient['brand'] . ' ' . ucfirst($getclient['fullname']);
        } ?> a <?php if (isset($getclient['secondproduct']) && $getclient['secondproduct'] == 'custom') {
            echo 'Sauna na míru';
        } elseif ($sauna == 'yes') {
            echo '<a style="color: #00a651; text-decoration: underline;"><i style="font-size: 12px;" class="entypo-check"></i>#' . $saunaid . ' ' . $second_product['brand'] . ' ' . ucfirst($second_product['fullname']) . '</a>';
        } else {
            echo $second_product['brand'] . ' ' . ucfirst($second_product['fullname']);
        } ?></span>

                                    <?php
}
        } elseif (isset($findContainer) && mysqli_num_rows($findContainer) > 0) {

            if (isset($found_product)) {
                if (isset($getclient['customer']) && $getclient['customer'] != 3) { ?>
                                    <span class="showprodukticek"
                                        style="color: orange; cursor: pointer;"><?php if (isset($getclient['product']) && $getclient['product'] == 'custom') { ?>Sauna
                                        na míru<?php } else {
                    echo $getclient['brand'] . ' ' . ucfirst($getclient['fullname']);
                } ?> - <?php
if (isset($found_product['serial_number']) && $found_product['serial_number'] != '') {
                    echo $found_product['serial_number'];
                } else { ?>kontejner <?php if (isset($found_product['container_name']) && $found_product['container_name'] == '') {
                    echo $found_product['id_brand'];
                } else {
                    echo $found_product['container_name'];
                } ?>, vířivka <?php echo $found_product['id'];} ?></span>

                                    <?php } else { ?>

                                    <span class="showprodukticek"
                                        style="cursor: pointer;"><?= $getclient['brand'] . ' ' . ucfirst($getclient['fullname']) ?>
                                        a <?php if (isset($getclient['secondproduct']) && $getclient['secondproduct'] == 'custom') {
                    echo 'Sauna na míru';
                } else {
                    echo $second_product['brand'] . ' ' . ucfirst($second_product['fullname']);
                } ?> - <?php
if (isset($found_product['serial_number']) && $found_product['serial_number'] != '') {
                    echo $found_product['serial_number'];
                } else { ?>kontejner <?php if (isset($found_product['container_name']) && $found_product['container_name'] == '') {
                    echo $found_product['container_id'];
                } else {
                    echo $found_product['container_name'];
                } ?>, vířivka <?php echo $found_product['id'];} ?></span>
                                    <?php }
            }
        } else { ?>
                                    <?php if (isset($getclient['customer']) && $getclient['customer'] != 3) { ?><span
                                        class="showprodukticek"
                                        style="cursor: pointer;"><?php if (isset($getclient['product']) && $getclient['product'] == 'custom') { ?>Sauna
                                        na míru<?php } else {
            echo $getclient['brand'] . ' ' . ucfirst($getclient['fullname']);
        } ?> </span><?php } else { ?>
                                    <span class="showprodukticek"
                                        style="cursor: pointer;"><?= $getclient['brand'] . ' ' . ucfirst($getclient['fullname']) ?>
                                        a <?php if (isset($getclient['secondproduct']) && $getclient['secondproduct'] == 'custom') {
            echo 'Sauna na míru';
        } else {
            echo $second_product['brand'] . ' ' . ucfirst($second_product['fullname']);
        } ?></span>
                                    <?php } ?>
                                    <?php } ?>
                                </div>
                            </li>
                            <?php } ?>

                            <li style="padding: 0 26px;">
                                <div class="profile-stat">
                                    <h3 style="margin-left: -7px;"><i style="margin-right: 4px;"
                                            class="entypo-briefcase"></i><?php
    if(!empty($getclient['name'])) {
        echo 'Showroom <strong>'.$getclient['name'].'</strong>';
    } else {
        echo 'Neznámý showroom';
    } ?>
                                    </h3>
                                    <span><?php if ($getclient['admin_id'] != 0) {
        $findadminquery = $mysqli->query("SELECT user_name FROM demands WHERE id = '" . $getclient['admin_id'] . "'");
        $findadmin = mysqli_fetch_array($findadminquery);

        echo '<strong>' . $findadmin['user_name'] . '</strong> se stará o poptávku.';
    } else {
        echo 'O poptávku se nikdo nestará.';
    } ?></span>
                                </div>
                            </li>


                            <li id="adress" style="cursor: pointer;padding: 0 26px;">
                                <div class="profile-stat">
                                    <h3 style="margin-left: -7px;"><i class="entypo-location"></i>Adresa</h3>
                                    <span><?php address($billing); ?></span>
                                </div>
                            </li>

                            <li style="cursor: pointer; padding: 0 0 0 26px; max-height: 58px;">
                                <div class="profile-stat">
                                    <a href="javascript:;" onclick="jQuery('#new-realization-modal').modal('show');">
                                        <h3 style="margin-left: -1px; margin-bottom: 4px;"><i
                                                style="font-size: 16px; margin-right: 5px;"
                                                class="fa fa-check"></i>Realizace <span style="color: #0072bb"><?php

                                            if ($getclient['area'] == 'prague') {
                                                echo 'PR';
                                            } elseif($getclient['area'] == 'brno') {
                                                echo 'BR';
                                            }else{
                                                echo '<i class="entypo-block"></i>';
                                            }
                                            ?></span>
                                            <?php if (isset($getclient['customer']) && $getclient['customer'] == 3) { ?>Vířivky<?php } ?>
                                        </h3>

                                        <?php if (isset($getclient['realization']) && $getclient['realization'] != '0000-00-00') {
        if (isset($getclient['confirmed']) && $getclient['confirmed'] == 1) {
            echo "<span style='color: #00a651;'>potvrzená <span id='wtf' style='color: #00a651; font-weight: 500;'>" . $getclient['realizationformated'] . '</span>';
            if ($getclient['realtodate'] != '0000-00-00') {
                echo " až <span id='wtf2' style='color: #00a651; font-weight: 500;'>" . $getclient['realtodateformat'] . '</span></span>';
            }
        } elseif (isset($getclient['confirmed']) && $getclient['confirmed'] == 2) {
            echo "<span style='color: #FF9933;'>v řešení <span id='wtf' style='color: #FF9933; font-weight: 500;'>" . $getclient['realizationformated'] . '</span>';
            if ($getclient['realtodate'] != '0000-00-00') {
                echo " až <span id='wtf2' style='color: #FF9933; font-weight: 500;'>" . $getclient['realtodateformat'] . '</span></span>';
            }
        } else {
            echo "<span style='color: #21d1e1;'>plánována <span id='wtf' style='color: #21d1e1; font-weight: 500;'>" . $getclient['realizationformated'] . '</span>';
            if ($getclient['realtodate'] != '0000-00-00') {
                echo " až <span id='wtf2' style='color: #21d1e1; font-weight: 500;'>" . $getclient['realtodateformat'] . '</span></span>';
            }
        }
    } else {
        echo '<span>Den realizace nebyl stanoven.</span>';
    } ?>
                                        <span id='wtfinvis'
                                            style="display:none;"><?= $getclient['realization'] ?></span>
                                    </a>


                                </div>
                            </li>

                            <?php

    if (isset($getclient['customer']) && $getclient['customer'] == 0) {
        $deadline_query = $mysqli->query("SELECT DATE_FORMAT(deadline_date, '%d. %m. %Y') as deadline_date FROM demands_generate_hottub WHERE id = '" . $_REQUEST['id'] . "'");
        $deadline = mysqli_fetch_array($deadline_query);
    } elseif (isset($getclient['customer']) && $getclient['customer'] == 1) {
        $deadline_query = $mysqli->query("SELECT DATE_FORMAT(deadline_date, '%d. %m. %Y') as deadline_date FROM demands_generate_hottub WHERE id = '" . $_REQUEST['id'] . "'");
        $deadline = mysqli_fetch_array($deadline_query);
    }

    if (isset($deadline['deadline_date'])) {
        ?>


                            <li style="cursor: pointer; padding: 0 0 0 16px; max-height: 58px; margin-left: 10px;">
                                <div class="profile-stat">
                                    <a href="javascript:;" onclick="jQuery('#new-realization-modal').modal('show');">
                                        <h3 style="margin-left: -1px; margin-bottom: 4px;"><i
                                                style="font-size: 16px; margin-right: 5px;"
                                                class="entypo-cancel-circled"></i>Deadline</h3>

                                        <span style='color: #000;'><i class="entypo-right-open-mini"></i> <span
                                                style='font-weight: 500;'><?= $deadline['deadline_date'] ?></span>

                                    </a>


                                </div>
                            </li>

                            <?php
} ?>

                            <?php if (isset($getclient['customer']) && $getclient['customer'] == 3) {
        $gatedate = $mysqli->query("SELECT *, DATE_FORMAT(startdate, '%d. %m. %Y') as startformated, DATE_FORMAT(enddate, '%d. %m. %Y') as endformated FROM demands_double_realization WHERE demand_id = '" . $getclient['id'] . "'");
        $saunadate = mysqli_fetch_array($gatedate); ?>

                            <li style="cursor: pointer;     margin-left: 25px; padding: 0 0 0 26px; max-height: 58px;">
                                <div class="profile-stat">



                                    <a href="javascript:;"
                                        onclick="jQuery('#new-realization-modal-sauna').modal('show');">
                                        <h3 style="margin-left: -1px; margin-bottom: 4px;"><i
                                                style="font-size: 16px; margin-right: 5px;"
                                                class="fa fa-check"></i>Realizace Sauny</h3>

                                        <?php if (isset($saunadate['startdate']) && $saunadate['startdate'] != '0000-00-00') {
            if (isset($saunadate['confirmed']) && $saunadate['confirmed'] == 1) {
                echo "<span style='color: #00a651;'>potvrzená <span id='wtf' style='color: #00a651; font-weight: 500;'>" . $saunadate['startformated'] . '</span>';
                if ($saunadate['enddate'] != '0000-00-00') {
                    echo " až <span id='wtf2' style='color: #00a651; font-weight: 500;'>" . $saunadate['endformated'] . '</span></span>';
                }
            } elseif (isset($saunadate['confirmed']) && $saunadate['confirmed'] == 2) {
                echo "<span style='color: #FF9933;'>v řešení <span id='wtf' style='color: #FF9933; font-weight: 500;'>" . $saunadate['startformated'] . '</span>';
                if ($saunadate['enddate'] != '0000-00-00') {
                    echo " až <span id='wtf2' style='color: #FF9933; font-weight: 500;'>" . $saunadate['endformated'] . '</span></span>';
                }
            } else {
                echo "<span style='color: #21d1e1;'>plánována <span id='wtf' style='color: #21d1e1; font-weight: 500;'>" . $saunadate['startformated'] . '</span>';
                if ($saunadate['enddate'] != '0000-00-00') {
                    echo " až <span id='wtf2' style='color: #21d1e1; font-weight: 500;'>" . $saunadate['endformated'] . '</span></span>';
                }
            }
        } else {
            echo '<span>Den realizace nebyl stanoven.</span>';
        } ?>
                                        <span id='wtfinvissauna' style="display:none;"><?php if (isset($saunadate['startdate']) && $saunadate['startdate'] != '0000-00-00') {
            echo $saunadate['startdate'];
        } ?></span>
                                    </a>
                                </div>
                            </li>
                            <?php
} ?>

                        </ul>

                    </div>



                </header>

                <section class="profile-info-tabs">

                    <div class="row">
                        <div class="col-sm-2"
                            style="text-align:center;width: 14%; padding-top: 10px; padding-right: 6px; height: 50px;">
                            <span <?php if ($access_edit) { ?>id="changestatus" <?php } ?> style="width: 100px;cursor: pointer;font-size: 13px; font-weight: 500;
                                color:#404a5b;"><i class="entypo-flag" style="padding-right: 2px;"></i><?php

                            foreach($demand_statuses as $status){ if($status['id'] == $getclient['status']){ echo $status['name']; } }  ?></span>
                            <?php if ($access_edit) { ?>
                            <form id="newstatus" style="display: none;" role="form" method="post"
                                action="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=changestatus"
                                enctype="multipart/form-data">
                                <select class="form-control status" name="status" style="width: 60%; float: left;">

                                    <?php
                                    foreach($demand_statuses as $status){
                                        ?>
                                        <option value="<?= $status['id'] ?>" <?php if($status['id'] == $getclient['status']){ echo 'selected'; }  ?>><?= $status['name'] ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                                <button type="submit" style="float: left;margin-left: 5px;margin-top:5px;"
                                    class="btn btn-green"> <i class="entypo-pencil"></i> </button>
                                <a id="cancelchangestatus" style="float: left;margin-left: 4px;margin-top:5px;"><button
                                        type="button" class="btn btn-white"> <i class="entypo-cancel"></i> </button></a>
                            </form><?php } ?>

                        </div>
                        <div class="col-sm-10" style="  width: 85.33333333%;">

                            <div class="col-sm-3" style="min-width: 232px;">
                                <ul class="user-details">
                                    <li>
                                        <i class="entypo-mail" style="margin-right: 5px;"></i>
                                        <?php if ($billing['billing_email'] != '') { ?><strong><a
                                                href="mailto:<?= $billing['billing_email'] ?>"><?= $billing['billing_email'] ?></a></strong><?php } else {
        echo 'žádný email';
    } ?>

                                    </li>
                                </ul>
                            </div>
                            <div class="col-sm-3">
                                <ul class="user-details">
                                    <li>

                                        <i class="entypo-phone"></i>
                                        <?php if ($billing['billing_phone'] != '' && $billing['billing_phone'] != 0) {
        echo phone_prefix($billing['billing_phone_prefix']); ?> <strong><?= number_format($billing['billing_phone'], 0, ',', ' ') ?></strong><?php
} else {
        echo 'žádný telefon';
    } ?>

                                    </li>

                                </ul>
                            </div>
                            <div class="col-sm-3">
                                <ul class="user-details">
                                    <li>


                                        <i class="fa fa-tachometer" style="margin-right: 5px;"></i>
                                        <?php if ($getclient['distance'] != '' && $getclient['distance'] != '0') { ?>Vzdálenost
                                            <strong><?= $getclient['distance'] ?> km</strong><?php } elseif(!empty($getclient['area'])){
   if (isset($billing['shipping_street']) && $billing['shipping_street'] != '' && isset($billing['shipping_city']) && $billing['shipping_city'] != '' && isset($billing['shipping_zipcode']) && $billing['shipping_zipcode'] != 0) {

                                            $location_address = $billing['shipping_city'] . ' ' . $billing['shipping_zipcode'] . ' ' . $billing['shipping_street'] . ' ' . $billing['shipping_country'];

                                        } else {

                                            $location_address = $billing['billing_city'] . ' ' . $billing['billing_zipcode'] . ' ' . $billing['billing_street'] . ' ' . $billing['billing_country'];

                                        }

                                        ?>

                                        <script type="text/javascript"
                                                src="//maps.google.com/maps/api/js?key=AIzaSyDRermPdr7opDFLqmrcOuK5L4zC2_U8XGk&sensor=false">
                                        </script>
                                        <script type="text/javascript">

                                            // var address = encodeURIComponent($("#location").val());
                                            var address = '<?= $location_address ?>>';
                                            var API_KEY = 'AIzaSyDWsYJWdJpuS_SgJ_0bpi0uOOGAGPBWsgk';

                                            $.ajax({
                                                type: "GET",
                                                url: "//maps.googleapis.com/maps/api/geocode/json?address=" + address + "&sensor=false&key=" + API_KEY,
                                                dataType: "json",
                                                success: function(data){

                                                    let lati = data.results[0].geometry.location.lat;
                                                    let longi = data.results[0].geometry.location.lng;

                                                    var directionsDisplay;
                                                    var directionsService = new google.maps.DirectionsService();
                                                    var map;

                                                    <?php if($getclient['area'] == 'prague'){ ?>
                                                    var start = '50.010540, 14.469440';
                                                    <?php }elseif($getclient['area'] == 'brno'){ ?>
                                                    var start = '49.020630, 17.128210';
                                                    <?php } ?>

                                                    var end = lati+','+longi;
                                                    var request = {
                                                        origin: start,
                                                        destination: end,
                                                        travelMode: google.maps.TravelMode.DRIVING
                                                    };
                                                    directionsService.route(request, function(response, status) {

                                                        let distance = response.routes[0].legs[0].distance.value / 1000;
                                                        let km = distance.toFixed(0);
                                                        $("#distance").html(km);

                                                    });
                                                }
                                            });
                                        </script>
                                        <?php
                                            echo 'vypočt. vzdálenost: <strong id="distance"></strong> km';
                                        }else{
                                            echo 'vzdálenost nedostupná';
                                        }?>

                                    </li>
                                </ul>
                            </div>
                            <div class="col-sm-3">
                                <ul class="user-details">
                                    <li>

                                        <i class="entypo-calendar"></i>
                                        Dne <strong><?= $getclient['dateformated'] ?></strong>
                                        <?php if ($getclient['creator'] > 0) {
        $findadminquery = $mysqli->query("SELECT user_name FROM demands WHERE id = '" . $getclient['creator'] . "'");
        $findadmin = mysqli_fetch_array($findadminquery);

        echo 'přidal <strong>' . $findadmin['user_name'].'</strong>';
    } ?>

                                    </li>
                                </ul>
                            </div>


                            <hr style="width: 100%; border-top: 1px solid #ffffff; margin-top: 34px;">

                        </div>


                        <div class="" <?php if (isset($getclient['customer']) && $getclient['customer'] == 3) { ?>style="width:
                            4%;height: 20%;margin-left: 200px;margin-left: 130px;/* float: right; */float: left;"
                            <?php } else { ?>style="display: none;" <?php } ?>>

                            <?php if (isset($getclient['customer']) && $getclient['customer'] == 3) { ?>
                            <img src="https://www.wellnesstrade.cz/admin/data/images/customer/<?= $getclient['product'] ?>.png"
                                class="img-responsive img-circle"
                                style="width:42px; float:right;    margin-right: -17px;" /><?php } ?>

                        </div>


                        <div class="col-sm-12" style="margin-top: 10px; float: left;">
                            <?php

        $searchquery = $mysqli->query("SELECT *, w.id as id, DATE_FORMAT(w.loadingdate, '%d. %m. %Y') as dateformated FROM warehouse w, warehouse_products p WHERE w.product = p.connect_name AND w.demand_id = '$demand_id'") or die($mysqli->error);
        if (mysqli_num_rows($searchquery) > 0) {

            while ($warehouse = mysqli_fetch_array($searchquery)) {

                // if is hottub
                if (isset($warehouse['customer']) && $warehouse['customer'] == 1) {

                    $get_provedeni = $mysqli->query("SELECT value FROM warehouse_specs_bridge WHERE client_id = '" . $warehouse['id'] . "' AND specs_id = 5") or die($mysqli->error);
                    $provedeni = mysqli_fetch_array($get_provedeni);

                    $get_ids = $mysqli->query("SELECT w.id as id, w.name as name 
                        FROM warehouse_products_types w, warehouse_products p 
                            WHERE w.warehouse_product_id = p.id AND p.fullname = '" . $warehouse['fullname'] . "' AND w.name = '" . $provedeni['value'] . "'") or die($mysqli->error);
                    $get_id = mysqli_fetch_array($get_ids);

                    // get all technical specs
                    $specsquery = $mysqli->query("SELECT 
                
               s.id, s.name, s.demand_category, s.technical 
                
                FROM specs s 
                INNER JOIN warehouse_products_types_specs wh ON wh.spec_id = s.id AND wh.type_id = '" . $get_id['id'] . "' AND s.technical = 1 AND s.warehouse_spec = 1 
                 GROUP BY s.id order by s.demand_category asc, s.name asc") or die($mysqli->error);


                    $necessary_changes = '';
                    while ($specs = mysqli_fetch_array($specsquery)) {

                        // get demand value
                        $demandSpecQuery = $mysqli->query("SELECT * FROM demands_specs_bridge WHERE specs_id = '" . $specs['id'] . "' AND client_id = '" . $demand_id . "'") or die($mysqli->error);
                        $demandSpec = mysqli_fetch_assoc($demandSpecQuery);

                        // get warehouse value
                        $warehouseSpecQuery = $mysqli->query("SELECT * FROM warehouse_specs_bridge WHERE specs_id = '" . $specs['id'] . "' AND client_id = '" . $warehouse['id'] . "'") or die($mysqli->error);
                        $warehouseSpec = mysqli_fetch_assoc($warehouseSpecQuery);


                        // check difference between warehouse and demand
                        if (((!isset($demandSpec['value']) || !isset($warehouseSpec)) || $demandSpec['value'] != $warehouseSpec['value'])
                        && !((!isset($demandSpec['value']) || !isset($warehouseSpec)) || $demandSpec['value'] == 'Ne' && $warehouseSpec['value'] == '')
                        && !($demandSpec['value'] == '' && $warehouseSpec['value'] == 'Ne')
                            ) {


                            // get product status
                            $product_status = '';
                            $get_products = $mysqli->query("SELECT *, d.type as stock_type, d.id as dem_id FROM demands_products d, products p, products_stocks s WHERE d.type = '" . $getclient['product'] . "' AND d.spec_id = '" . $specs['id'] . "' AND d.product_id = p.id AND s.product_id = d.product_id AND s.variation_id = d.variation_id AND s.location_id = '2'") or die($mysqli->error);

                            if (mysqli_num_rows($get_products) > 0) {
                                while ($product = mysqli_fetch_array($get_products)) {
                                    $single_query = $mysqli->query("SELECT b.*, l.name FROM demands_products_bridge b LEFT JOIN shops_locations l ON b.location_id = l.id WHERE b.demand_id = '" . $getclient['id'] . "' AND b.spec_id = '" . $specs['id'] . "' AND b.product_id = '" . $product['product_id'] . "' AND b.variation_id = '" . $product['variation_id'] . "'") or die($mysqli->error);
                                    $single = mysqli_fetch_array($single_query);

                                    if (!empty($single)) {

                                        if ($single['type'] == 'warehouse') {

                                            $product_status = '<a href="/admin/pages/accessories/zobrazit-prislusenstvi?id=' . $product['product_id'] . '"
                                               target="_blank" style="color: #00a651; font-weight: bold;">- Rezervováno v
                                                ' . $single['name'] . '</a>';

                                        } elseif ($single['type'] == 'missing') {

                                            $product_status = '<a href="/admin/pages/accessories/zobrazit-prislusenstvi?id=' . $product['product_id'] . '"
                                               target="_blank" style="color: #d42020; font-weight: bold;">- Chybějící v
                                                ' . $single['name'] . '</a>';

                                        } elseif ($single['type'] == 'supply') {
                                            $supply_query = $mysqli->query("SELECT *, DATE_FORMAT(date, '%d. %M %Y') as recieved_date FROM products_supply WHERE id = '" . $single['type_id'] . "'") or die($mysqli->error);
                                            $supply = mysqli_fetch_array($supply_query);

                                            $product_status = '<a href="/admin/pages/accessories/zobrazit-dodavku?id=' . $supply['id'] . '"
                                               target="_blank" style="color: #ff5722; font-weight: bold;">- Dodávka
                                                #' . $supply['id'] . '
                                                - doručení
                                                ' . $supply['recieved_date'] . '</a>';

                                        } elseif ($single['type'] == 'hottub') {
                                            $hottub_query = $mysqli->query("SELECT w.*, l.name FROM warehouse w LEFT JOIN shops_locations l ON l.id = w.location_id WHERE w.id = '" . $single['type_id'] . "'") or die($mysqli->error);
                                            $hottub = mysqli_fetch_array($hottub_query);

                                            $product_status = '<a href="/admin/pages/warehouse/zobrazit-virivku?id=' . $hottub['id'] . '" target="_blank" style="color: #ff5722; font-weight: bold;">- Vířivka
                                                #' . $hottub['serial_number'] . ' - ' . $hottub['name'] . '</a>';

                                        }

                                    }

                                }
                            }


                            // check info regarding product
                            $paid = '';
                            if ($warehouseSpec['paid']) {

                                $paid_text = $warehouseSpec['paid_text'] ? $warehouseSpec['paid_text'] : 'bez dodatečných informací';

                                $paid = '<i class="fas fa-asterisk" style="margin-left: 20px; line-height: 15px; color: #d42020;"  data-toggle="tooltip" data-placement="top" data-original-title="' . $paid_text . '"></i>';

                            }


                            // add to other changes
                            $necessary_changes .= '
                            <div class="col-sm-12" style="margin-bottom: 6px; padding: 0; color: #000000; padding-left: 22px;text-indent: -15px;">
                                <i class="entypo-right-open-mini" style="margin-right: -7px;"></i>
                                <strong>' . $specs['name'] . '</strong>:
                                změnit na
                                <strong>' . mb_strtoupper($demandSpec['value']) . '</strong> ' . $paid . '
                                ' . $product_status . '
                            </div>';

                        }

                    }


                    ?>
                    <div class="col-sm-4" style="padding: 0 10px 0; ">
                        <h4 style="font-size: 14px; margin-bottom: 13px; text-align: center; margin-top: 0; margin-left: 0; border-bottom: 1px solid #dedede; padding-bottom: 13px;">
                            <?php if (!empty($necessary_changes)) { ?><i style="color: #d42020;"
                                                                         class="entypo-attention" data-toggle="tooltip"
                                                                         data-placement="top" title=""
                                                                         data-original-title="Specifikace u vířivky neodpovídá zvolené specifikaci u poptávky."></i><?php } else { ?>
                                <i
                                    style=" color: #00a651;" class="entypo-check"></i><?php } ?> Nutné změny <a
                                href="/admin/pages/warehouse/zobrazit-virivku?id=<?= $warehouse['id'] ?>"
                                target="_blank" style="font-size: 11px;"> - zobrazit skladovou položku</a></h4>
                        <?php
                        if (!empty($necessary_changes)) {

                            echo $necessary_changes;

                        } else {

                            echo '<div style="margin-bottom: 6px; padding: 0;"><i class="entypo-right-open-mini"></i>žádné změny</div>';

                        }
                        ?>
                    </div>


                    <?php


                    // old to delete

                    /*

                    if($client['id'] == 2126){
                    $specsquery = $mysqli->query("SELECT 
                    
                    w.paid, w.paid_text, s.id, s.name, w.value as warehouse_value, d.value as demand_value, s.demand_category, s.technical 
                    
                    FROM specs s 
                    INNER JOIN warehouse_products_types_specs wh ON wh.spec_id = s.id AND wh.type_id = '" . $get_id['id'] . "' AND s.technical = 1 AND s.warehouse_spec = 1 
                    LEFT JOIN demands_specs_bridge d ON d.specs_id = s.id AND d.client_id = '$demand_id' 
                    LEFT JOIN warehouse_specs_bridge w ON w.specs_id = s.id AND w.client_id = '" . $warehouse['id'] . "' 
                    
                    WHERE (d.value != w.value OR NOT EXISTS(SELECT * FROM warehouse_specs_bridge w WHERE w.specs_id = s.id AND w.client_id = '" . $warehouse['id'] . "')) GROUP BY s.id order by s.demand_category asc, s.name asc") or die($mysqli->error);


                    ?>
                    <div class="col-sm-4" style="padding: 0 10px 0; ">
                        <h4
                            style="font-size: 14px; margin-bottom: 13px; text-align: center; margin-top: 0; margin-left: 0; border-bottom: 1px solid #dedede; padding-bottom: 13px;">
                            <?php if (mysqli_num_rows($specsquery) > 0) { ?><i style="color: #d42020;"
                                                                               class="entypo-attention"
                                                                               data-toggle="tooltip"
                                                                               data-placement="top" title=""
                                                                               data-original-title="Specifikace u vířivky neodpovídá zvolené specifikaci u poptávky."></i><?php } else { ?>
                                <i
                                    style=" color: #00a651;" class="entypo-check"></i><?php } ?> Nutné změny <a
                                href="/admin/pages/warehouse/zobrazit-virivku?id=<?= $warehouse['id'] ?>"
                                target="_blank" style="font-size: 11px;"> - zobrazit skladovou položku</a></h4>
                        <?php
                        $technical = false;
                        if (mysqli_num_rows($specsquery) > 0) {
                            while ($specs = mysqli_fetch_array($specsquery)) {

                                print_r($specs);

                                if (isset($specs['technical']) && $specs['technical'] == 1 && !$technical) {
                                    $technical = true; ?>


                                    <?php

                                    // $category_warehouse_done = $specs['demand_category'];
                                } ?>
                                <div class="col-sm-12"
                                     style="margin-bottom: 6px; padding: 0; color: #000000; padding-left: 22px;text-indent: -15px;">

                                    <i class="entypo-right-open-mini" style="margin-right: -7px;"></i>
                                    <strong><?= $specs['name'] ?></strong>:
                                    změnit na
                                    <strong><?= mb_strtoupper($specs['demand_value']) ?></strong> <?php
                                    if ($specs['paid']) { ?><i class="fas fa-asterisk"
                                                               style="margin-left: 20px; line-height: 15px; color: #d42020;"
                                                               data-toggle="tooltip" data-placement="top"
                                                               data-original-title="<?php

                                                               if (!empty($specs['paid_text'])) {
                                                                   echo $specs['paid_text'];
                                                               } else {
                                                                   echo 'bez dodatečných informací';
                                                               }

                                                               ?>"></i><?php } ?>

                                    <?php
                                    $get_products = $mysqli->query("SELECT *, d.type as stock_type, d.id as dem_id FROM demands_products d, products p, products_stocks s WHERE d.type = '" . $getclient['product'] . "' AND d.spec_id = '" . $specs['id'] . "' AND d.product_id = p.id AND s.product_id = d.product_id AND s.variation_id = d.variation_id AND s.location_id = '2'") or die($mysqli->error);

                                    if (mysqli_num_rows($get_products) > 0) {
                                        while ($product = mysqli_fetch_array($get_products)) {
                                            $single_query = $mysqli->query("SELECT b.*, l.name FROM demands_products_bridge b LEFT JOIN shops_locations l ON b.location_id = l.id WHERE b.demand_id = '" . $getclient['id'] . "' AND b.spec_id = '" . $specs['id'] . "' AND b.product_id = '" . $product['product_id'] . "' AND b.variation_id = '" . $product['variation_id'] . "'") or die($mysqli->error);
                                            $single = mysqli_fetch_array($single_query);

                                            if (!empty($single)) {

                                                if ($single['type'] == 'warehouse') { ?>

                                                    <a href="/admin/pages/accessories/zobrazit-prislusenstvi?id=<?= $product['product_id'] ?>"
                                                       target="_blank" style="color: #00a651; font-weight: bold;">-
                                                        Rezervováno v
                                                        <?= $single['name'] ?></a>

                                                <?php } elseif ($single['type'] == 'missing') { ?>

                                                    <a href="/admin/pages/accessories/zobrazit-prislusenstvi?id=<?= $product['product_id'] ?>"
                                                       target="_blank" style="color: #d42020; font-weight: bold;">-
                                                        Chybějící v
                                                        <?= $single['name'] ?></a>

                                                <?php } elseif ($single['type'] == 'supply') {
                                                    $supply_query = $mysqli->query("SELECT *, DATE_FORMAT(date, '%d. %M %Y') as recieved_date FROM products_supply WHERE id = '" . $single['type_id'] . "'") or die($mysqli->error);
                                                    $supply = mysqli_fetch_array($supply_query); ?>

                                                    <a href="/admin/pages/accessories/zobrazit-dodavku?id=<?= $supply['id'] ?>"
                                                       target="_blank" style="color: #ff5722; font-weight: bold;">-
                                                        Dodávka
                                                        #<?= $supply['id'] ?>
                                                        - doručení
                                                        <?= $supply['recieved_date'] ?></a>

                                                <?php } elseif ($single['type'] == 'hottub') {
                                                    $hottub_query = $mysqli->query("SELECT w.*, l.name FROM warehouse w LEFT JOIN shops_locations l ON l.id = w.location_id WHERE w.id = '" . $single['type_id'] . "'") or die($mysqli->error);
                                                    $hottub = mysqli_fetch_array($hottub_query); ?>

                                                    <a href="/admin/pages/warehouse/zobrazit-virivku?id=<?= $hottub['id'] ?>"
                                                       target="_blank" style="color: #ff5722; font-weight: bold;">-
                                                        Vířivka
                                                        #<?= $hottub['serial_number'] ?>
                                                        - <?= $hottub['name'] ?></a>

                                                <?php }

                                            }

                                        }
                                    } ?>
                                </div>
                                <?php
                            }
                        } else { ?>

                            <div style="margin-bottom: 6px; padding: 0;">

                                <i class="entypo-right-open-mini"></i>
                                žádné změny
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                }

                    */
                    // old to delete


}
            }
        } else { ?>


                            <div class="col-sm-4" style="padding: 0 10px 0; ">
                                <h4
                                    style="font-size: 14px; margin-bottom: 13px; text-align: center; margin-top: 0; margin-left: 0; border-bottom: 1px solid #dedede; padding-bottom: 13px;">
                                    <i class="entypo-right-open"></i> Nemá skladovou vířivku</h4>

                                <div style="margin-bottom: 6px; padding: 0;">

                                    <i class="entypo-right-open-mini"></i>
                                    poptávka ještě nemá přiřazenou skladovou položku
                                </div>

                            </div>
                            <?php
}

        $get_provedeni = $mysqli->query("SELECT value FROM demands_specs_bridge WHERE client_id = '" . $getclient['id'] . "' AND specs_id = 5") or die($mysqli->error);

        $provedeni = mysqli_fetch_array($get_provedeni);
        if (isset($provedeni['value']) && $provedeni['value'] != '') {
            $get_ids = $mysqli->query("SELECT w.id as id, w.name as name FROM warehouse_products_types w, warehouse_products p WHERE w.warehouse_product_id = p.id AND p.connect_name = '" . $getclient['product'] . "' AND w.name = '" . $provedeni['value'] . "'") or die($mysqli->error);
            $get_id = mysqli_fetch_array($get_ids);


            $specs_query = $mysqli->query("SELECT *, s.id as id FROM specs s, warehouse_products_types_specs w WHERE w.spec_id = s.id AND w.type_id = '" . $get_id['id'] . "' AND s.demand_category < 3 AND s.is_demand = 1 GROUP BY s.id ORDER BY s.demand_category ASC, s.name asc") or die($mysqli->error);


            while ($specs = mysqli_fetch_array($specs_query)) {
                if (isset($specs['demand_category']) && $specs['demand_category'] == 1 && (!isset($category_done) || $category_done != $specs['demand_category'])) {
                    ?>
                            <div class="col-sm-4" style="padding: 0 10px 0; border-left: 1px dashed #cccccc;">
                                <h4
                                    style="font-size: 14px; margin-bottom: 13px; text-align: center; margin-top: 0; border-bottom: 1px solid #dedede; padding-bottom: 13px;">
                                    Obecné hlavní</h4>
                                <div class="col-sm-12" style="margin-bottom: 6px; padding: 0;">
                                    <i class="entypo-right-open-mini"></i>
                                    Typ vířivky:
                                    <strong><?= returnpn($getclient['customer'], $getclient['product']) ?></strong>

                                </div>
                                <div class="col-sm-12" style="margin-bottom: 6px; padding: 0;">
                                    <i class="entypo-right-open-mini"></i>
                                    Provedení: <strong><?= $provedeni['value'] ?></strong>
                                </div>
                                <?php
$category_done = $specs['demand_category'];
                } elseif (isset($specs['demand_category']) && $specs['demand_category'] == 2 && (!isset($category_done) || $category_done != $specs['demand_category'])) {
                    ?>
                            </div>
                            <div class="col-sm-4" style="padding: 0 10px 0; border-left: 1px dashed #cccccc;">
                                <h4
                                    style="font-size: 14px; margin-bottom: 13px; text-align: center; margin-top: 0; border-bottom: 1px solid #dedede; padding-bottom: 13px;">
                                    Příplatková výbava</h4>
                                <?php
$category_done = $specs['demand_category'];
                } elseif (isset($specs['demand_category']) && $specs['demand_category'] == 3 && (!isset($category_done) || $category_done != $specs['demand_category'])) {
                    ?>
                            </div>
                            <div class="col-sm-6" style="padding: 0 10px 0; border-left: 1px dashed #cccccc;">
                                <h4
                                    style="font-size: 14px; margin-bottom: 13px; text-align: center; margin-top: 0; border-bottom: 1px solid #dedede; padding-bottom: 13px;">
                                    Specifikace provedení</h4>
                                <?php
$category_done = $specs['demand_category'];
                }

                $paramsquery = $mysqli->query('SELECT value FROM demands_specs_bridge WHERE specs_id = "' . $specs['id'] . '" AND client_id = "' . $getclient['id'] . '"') or die($mysqli->error);
                $params = mysqli_fetch_array($paramsquery);

                if (mysqli_num_rows($searchquery) > 0) {
                    $specsdemquery = $mysqli->query('SELECT value FROM warehouse_specs_bridge WHERE specs_id = "' . $specs['id'] . '" AND client_id = "' . $virivkaid . '"') or die($mysqli->error);
                    $demandsspecs = mysqli_fetch_array($specsdemquery);
                } else {
                    $specsdemquery = $mysqli->query('SELECT value FROM containers_products_specs_bridge WHERE specs_id = "' . $specs['id'] . '" AND client_id = "' . $virivkaid . '"') or die($mysqli->error);
                    $demandsspecs = mysqli_fetch_array($specsdemquery);
                }

                if ((!empty($params['value']) && $params['value'] != 'Ne' && $params['value'] != 'IQue Ozonátor' ) && ($params['value'] != '2,25 kW' && $params['value'] != '1,85 kW' && $params['value'] != '3 kW' && $params['value'] != '1,5 kW' && $params['value'] != '2x 1,5 kW')) {

                    if($specs['id'] == 16 && $params['value'] == '2 speed 2,25 kW' && $provedeni['value'] != 'Gold'){
                        continue;
                    }

                    ?>
                                <div <?php if (isset($category_done) && $category_done == 3) { ?>class="col-sm-6"
                                    <?php } else { ?>class="col-sm-12" <?php } ?>
                                    style="margin-bottom: 6px; padding: 0;">
                                    <i class="entypo-right-open-mini"></i>
                                    <?php if (isset($specs['id']) && $specs['id'] == 7) { ?><strong><?= $params['value'] ?></strong><?php } else {
                        echo $specs['name']; ?>: <strong><?= $params['value'] ?></strong>
                                    <?php
} ?>
                                </div>
                                <?php
}
            }


            ?><hr style="border-color: #dfdfdf;"><?php




            $accessories_query = $mysqli->query("SELECT * FROM demands_accessories_bridge WHERE aggregate_id = '" . $id . "'") or die($mysqli->error);
            while ($accessory = mysqli_fetch_array($accessories_query)) {

                $subtitle = $accessory['variation_values'] ?: '';
                $name = $accessory['product_name'].$subtitle;

                ?>
                <div <?php if (isset($category_done) && $category_done == 3) { ?>class="col-sm-6"
                     <?php } else { ?>class="col-sm-12" <?php } ?>
                     style="margin-bottom: 6px; padding: 0;">
                    <i class="entypo-right-open-mini"></i>
                    <a href="../accessories/zobrazit-prislusenstvi?id=<?= $accessory['product_id'] ?>" target="_blank"><strong><?= $name ?></strong></a>
                </div>
                <?php

            }

            ?>

                            </div>


                            <?php

            $searchquery = $mysqli->query("SELECT *, w.id as id, DATE_FORMAT(w.loadingdate, '%d. %m. %Y') as dateformated FROM warehouse w, warehouse_products p WHERE w.product = p.connect_name AND w.demand_id = '$demand_id'") or die($mysqli->error);
            if (mysqli_num_rows($searchquery) > 0) {
                while ($warehouse = mysqli_fetch_array($searchquery)) {
                    if (isset($warehouse['customer']) && $warehouse['customer'] == 1) {
                        $get_provedeni = $mysqli->query("SELECT value FROM warehouse_specs_bridge WHERE client_id = '" . $warehouse['id'] . "' AND specs_id = 5") or die($mysqli->error);
                        $provedeni = mysqli_fetch_array($get_provedeni);

                        $get_ids = $mysqli->query("SELECT w.id as id, w.name as name FROM warehouse_products_types w, warehouse_products p WHERE w.warehouse_product_id = p.id AND p.fullname = '" . $warehouse['fullname'] . "' AND w.name = '" . $provedeni['value'] . "'") or die($mysqli->error);
                        $get_id = mysqli_fetch_array($get_ids);

                        $get_specs = $mysqli->query("SELECT s.id, s.name, w.value as warehouse_value, d.value as demand_value, s.demand_category, s.technical FROM specs s INNER JOIN warehouse_products_types_specs wh ON wh.spec_id = s.id AND wh.type_id = '" . $get_id['id'] . "' AND s.technical = 1 AND s.warehouse_spec = 1 LEFT JOIN warehouse_specs_bridge w ON w.specs_id = s.id AND w.client_id = '" . $warehouse['id'] . "' LEFT JOIN demands_specs_bridge d ON d.specs_id = s.id AND d.client_id = '$demand_id' WHERE d.value != w.value GROUP BY s.id order by s.demand_category asc, s.name asc") or die($mysqli->error);

                        if (mysqli_num_rows($get_specs) > 0) {
                            ?>
                            <div style="clear: both;"></div>




                            <?php
}
                    }
                }
            } ?>



                            <?php
} else { ?>
                            <div class="col-sm-12" style="margin-bottom: 6px; padding: 0;">
                                <i class="entypo-right-open-mini"></i>
                                Poptávka nemá zvolené žádné provedení.
                            </div>

                            <?php } ?>

                        </div>


                        <div class="clear"></div>


                        <hr style=" width: 100%; border-top: 1px solid #ffffff; margin-top: 22px; margin-bottom: 3px;">


                        <div class="col-sm-12" style="padding: 0;">
                            <div class="profile-env" style=" margin-bottom: 0;">
                                <section class="profile-feed" style=" margin-bottom: 0;">
                                    <div class="profile-stories" style=" margin-bottom: 0;">


                                        <article
                                            class="story <?php if (isset($found_product) && $found_product['description'] != '') { ?>col-sm-4<?php } else { ?>col-sm-6<?php } ?>"
                                            style="padding: 0; margin: 16px 0 !important;">

                                            <aside class="user-thumb"
                                                style="width: 20%; text-align: center; border-right: 1px solid #cccccc; padding-right: 12px; font-size: 11px;">
                                                <i class="entypo-info"
                                                    style="font-size: 30px; width: 100%;float: left;margin-bottom: 4px;"></i>
                                                Informace prodejců</aside>
                                            <div class="story-content" style="margin-top: 10px; width: 78%;">
                                                <div class="story-main-content">
                                                    <div <?php if ($access_edit) { ?>class="descriptiontext" <?php } ?>
                                                        <?php if (isset($getclient['description']) && $getclient['description'] == '') { ?>style="
                                                        cursor: pointer;" <?php } ?>><?php if (!empty($getclient['description'])) {
        echo $getclient['description'];
    } else {
        echo '<strong>Pro přidání popisku klikněte zde.</strong>';
    } ?>
                                                    </div>
                                                    <?php if ($access_edit) { ?>
                                                    <form id="editdescription" role="form" method="post"
                                                        enctype="multipart/form-data" style="display: none;">
                                                        <textarea class="form-control" name="description"
                                                            style="height: 100px;"
                                                            id="zmrdsample"><?= $getclient['description'] ?></textarea>
                                                        <input type="text" name="id"
                                                            value="<?= $getclient['id'] ?>"
                                                            style="display:none;">
                                                        <input type="text" name="type" value="text"
                                                            style="display:none;">
                                                        <div class="row" style="margin-top: 16px;">
                                                            <div class="col-md-8 col-sm-5"
                                                                style="text-align: left;float:left;">


                                                                <a id="sendform"
                                                                    style="cursor:pointer; margin-right: 4px;"
                                                                    class="btn btn-green btn-icon icon-left btn-lg">
                                                                    <i class="entypo-pencil"></i>
                                                                    Upravit popisek
                                                                </a>
                                                                <a id="canceledit"><button type="button"
                                                                        class="btn btn-white btn-lg"> <i
                                                                            class="entypo-cancel"></i> </button></a>

                                                            </div>
                                                        </div>
                                                    </form><?php } ?>
                                                </div>
                                            </div>
                                        </article>


                                        <article
                                            class="story <?php if (isset($found_product['description']) && $found_product['description'] != '') { ?>col-sm-4<?php } else { ?>col-sm-6<?php } ?>"
                                            style="padding: 0; margin: 16px 0 !important;">
                                            <aside class="user-thumb"
                                                style="width: 20%;text-align: center;border-right: 1px solid #cccccc;padding-right: 20px;padding-right: 12px; font-size: 11px;">
                                                <i class="fa fa-wrench"
                                                    style="font-size: 30px; width: 100%;float: left;margin-bottom: 4px;"></i>
                                                Informace techniků

                                                <strong style="float: left; margin-top: 8px;">
                                                    <?php

                                                    if($getclient['customer'] == '0'){ $type = 'realization_sauna'; }else{ $type = 'realization_hottub'; }

                                                    $technicians_query = $mysqli->query("SELECT c.user_name FROM demands c, mails_recievers t WHERE c.id = t.admin_id AND t.type_id = '$id' AND t.type = '".$type."' AND t.reciever_type = 'performer'") or die($mysqli->error);
    $i = 0;
    while ($technician = mysqli_fetch_array($technicians_query)) {
        if ($i == 0) {
            echo $technician['user_name'];
        } else {
            echo ', ' . $technician['user_name'];
        }
        $i++;
    } ?></strong>
                                            </aside>
                                            <div class="story-content" style="margin-top: 10px; width: 78%;">
                                                <div class="story-main-content">
                                                    <div <?php if ($access_edit) { ?>class="technical_description_text"
                                                        <?php } ?>
                                                        <?php if (isset($getclient['technical_description']) && $getclient['technical_description'] == '') { ?>style="
                                                        cursor: pointer;" <?php } ?>><?php if ($getclient['technical_description'] != '') {
        echo $getclient['technical_description'];
    } else {
        echo '<strong>Pro přidání popisku klikněte zde.</strong>';
    } ?>
                                                    </div>
                                                    <?php if ($access_edit) { ?>
                                                    <form id="technical_edit_description" role="form" method="post"
                                                        enctype="multipart/form-data" style="display: none">
                                                        <textarea class="form-control" name="technical_description"
                                                            style="height: 100px;"
                                                            id="technical_zmrdsample"><?= $getclient['technical_description'] ?></textarea>
                                                        <input type="text" name="id"
                                                            value="<?= $getclient['id'] ?>"
                                                            style="display:none;">
                                                        <input type="text" name="type" value="text"
                                                            style="display:none;">
                                                        <div class="row" style="margin-top: 16px;">
                                                            <div class="col-md-8 col-sm-5"
                                                                style="text-align: left;float:left;">


                                                                <a id="send_technical"
                                                                    style="cursor:pointer; margin-right: 4px;"
                                                                    class="btn btn-green btn-icon icon-left btn-lg">
                                                                    <i class="entypo-pencil"></i>
                                                                    Upravit popisek
                                                                </a>
                                                                <a id="technical_cancel_edit"><button type="button"
                                                                        class="btn btn-white btn-lg"> <i
                                                                            class="entypo-cancel"></i> </button></a>

                                                            </div>
                                                        </div>
                                                    </form><?php } ?>
                                                </div>
                                            </div>

                                        </article>


                                        <?php if (isset($found_product) && $found_product['description'] != '') { ?>

                                        <article class="story col-sm-4" style="margin: 16px 0  10px !important;">
                                            <aside class="user-thumb"
                                                style="padding: 0; width: 20%;text-align: center;border-right: 1px solid #cccccc;padding-right: 12px; font-size: 11px;">
                                                <i class="fa fa-truck"
                                                    style="font-size: 30px; width: 100%;float: left;margin-bottom: 4px;"></i>
                                                Informace ve skladu </aside>
                                            <div class="story-content" style="margin-top: 10px; width: 78%;">
                                                <div class="story-main-content">
                                                    <div <?php if (isset($found_product['description']) && $found_product['description'] == '') { ?>style="
                                                        cursor: pointer;" <?php } ?>><?php if ($found_product['description'] != '') {
        echo $found_product['description'];
    } else {
        echo '<strong>Pro přidání popisku klikněte zde.</strong>';
    } ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </article>

                                        <?php } ?>

                                    </div>
                                </section>
                            </div>


                        </div>

                        <?php

    $contacts_query = $mysqli->query("SELECT * FROM demands_contacts WHERE demand_id = '$id'") or die($mysqli->error);

    if (mysqli_num_rows($contacts_query) > 0) {
        ?>

                        <div class="clear"></div>
                        <hr style=" width: 100%; border-top: 1px solid #ffffff; margin-top: 14px; margin-bottom: 14px;">


                        <div class="clear"></div>
                        <div class="" style="width: 8%; float:left;    height: 1px;">


                        </div>


                        <div class="col-sm-10" style="  width: 85.33333333%;margin: 12px 0 2px;">


                            <?php

        $contact_number = 0;

        while ($contact = mysqli_fetch_array($contacts_query)) {
            ?>



                            <div class="col-md-3">

                                <ul class="user-details"
                                    style="padding: 0 22px; border: 1px solid #dedede; border-radius: 5px; background-color: #f9f9f9;">
                                    <li>
                                        <h3 style="margin-top: 12px;"><?= $contact['name'] ?>
                                        </h3>
                                    </li>
                                    <li>
                                        <i class="entypo-user" style="margin-right: 5px;"></i>
                                        <?php if (!empty($contact['role'])) { ?><strong><?= $contact['role'] ?></strong><?php } else {
                echo 'žádná role';
            } ?>
                                    </li>
                                    <li>
                                        <i class="entypo-phone" style="margin-right: 5px;"></i>
                                        <?php if (isset($contact['phone']) && $contact['phone'] != '') { ?>+420
                                        <strong><?= number_format($contact['phone'], 0, ',', ' ') ?></strong><?php } else {
                echo 'žádný telefon';
            } ?>
                                    </li>
                                    <li>
                                        <i class="entypo-mail" style="margin-right: 5px;"></i>
                                        <?php if ($contact['email'] != '') { ?><strong><a
                                                href="mailto:<?= $contact['email'] ?>"><?= $contact['email'] ?></a></strong><?php } else {
                echo 'žádný email';
            } ?>
                                    </li>


                                </ul>

                            </div>




                            <?php
} ?>

                        </div>

                        <?php
} ?>




                        <?php

    if ($getclient['status'] == 5 || $getclient['status'] == 8 || $getclient['status'] == 13) {
        ?>


                        <script>
                        jQuery(document).ready(function($) {


                            $('.nav-tabs li').click(function() {

                                var id = this.id;

                                $('.nav-tabs li').removeClass('active');
                                $(this).addClass('active');

                                $('.tab').hide('slow');
                                $('#' + id + '-tab').show('slow');


                            });



                        });
                        </script>


    <?php

    if($getclient['status'] == 5 || $getclient['status'] == 8 || $getclient['status'] == 12 || $getclient['status'] == 4 || $getclient['status'] == 13){
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

        $missingDocuments = 0;
        foreach ($types as $key => $value) {

            $missingDocuments++;

            $documentsQuery = $mysqli->query('SELECT * FROM documents_contracts WHERE client_id="' . $_REQUEST['id'] . '" AND type = "' . $value . '" order by id desc') or die($mysqli->error);

            $documentsCount = mysqli_num_rows($documentsQuery);

            if($documentsCount > 0){ $missingDocuments -= $documentsCount; }

            if ($value == 'invoice') {
                $check_data = $mysqli->query("SELECT invoices_number FROM demands_generate WHERE id = '$id'");
                if (mysqli_num_rows($check_data) > 0) {
                    $check = mysqli_fetch_array($check_data);
                    $inv = $check['invoices_number'];

                    $missingDocuments += $inv;

                }
            }
        }
    }



?>
                        <div class="col-sm-12" style="padding-left: 54px;">
                            <div class="col-sm-10">
                                <!-- tabs for the profile links -->
                                <ul class="nav nav-tabs">
                                    <li id="hide" data-target="hide-tab" class="active"><a style="cursor: pointer; padding: 10px 20px;"><i class="entypo-eye"></i></a></li>
                                    <li id="service" data-target="service-tab"><a style="cursor: pointer;"><i class="entypo-tools"></i> Servis</a> </li>
                                    <?php if ($access_edit) { ?>
                                    <li id="orders"><a style="cursor: pointer;"><i class="entypo-basket"></i>
                                            Objednávky</a></li>
                                    <li id="documents"><a style="cursor: pointer;"><i class="entypo-book"></i>
                                            Dokumenty <span class="badge badge-secondary" style="margin-left: 4px;"><?= $missingDocuments ?></span></a></li>
                                    <li id="generate-data"><a style="cursor: pointer;"><i class="entypo-rocket"></i>
                                            Generování</a></li>
                                    <?php } ?>
                                </ul>

                            </div>

                        </div>

                        <?php
} ?>


                        <div class="clear"></div>

                </section>





                <?php


            include $_SERVER['DOCUMENT_ROOT'] . '/admin/pages/demands/parts/tabs.php';

                if ($access_edit) { ?>

                <?php if ($getclient['status'] != 12 && $getclient['status'] != 4 && $getclient['status'] != 8 && $getclient['status'] != 5 && $getclient['status'] != 13) {

                    include $_SERVER['DOCUMENT_ROOT'] . '/admin/pages/demands/parts/follow-ups.php';

                 }

        if ($getclient['status'] && ($getclient['status'] == 15 || $getclient['status'] == 12 || $getclient['status'] == 4 || $getclient['status'] == 8)) { ?>

                <hr>
                <h2 style="text-align: center;text-align: center;margin: 23px 0 22px;">Management prodané poptávky</h2>
                <hr>
                <section class="profile-feed sold-management"
                    style="    width: 25%;    padding: 0; float: left;  z-index: 0;border-right: 1px solid #EEEEEE;">



                    <h3 style="text-align: center;">Smlouva</h3>
                    <form id="rootwizard" method="post" action="" class="form-horizontal form-wizard"
                        style="margin-top: 60px;">

                        <div class="steps-progress">
                            <div class="progress-indicator"></div>
                        </div>

                        <ul>
                            <li <?php if (isset($getclient['contract']) && $getclient['contract'] == 0) {
            echo 'class="completed red"';
        } ?>>
                                <a
                                    href="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=edit_sold&type=contract&value=0"><span>0</span>Nevystavená
                                    smlouva</a>
                            </li>
                            <li <?php if (isset($getclient['contract']) && $getclient['contract'] == 1) {
            echo 'class="completed yellow"';
        } ?>>
                                <a
                                    href="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=edit_sold&type=contract&value=1"><span>1</span>Vystavená
                                    smlouva</a>
                            </li>
                            <li <?php if (isset($getclient['contract']) && $getclient['contract'] == 2) {
            echo 'class="completed orange"';
        } ?>>
                                <a
                                    href="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=edit_sold&type=contract&value=2"><span>2</span>Podepsaná
                                    smlouva</a>
                            </li>
                            <li <?php if (isset($getclient['contract']) && $getclient['contract'] == 3) {
            echo 'class="completed green"';
        } ?>>
                                <a
                                    href="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=edit_sold&type=contract&value=3"><span>3</span>Zaplacená</a>
                            </li>

                        </ul>

                    </form>

                </section>

                <section class="profile-feed sold-management"
                    style="width: 30%; padding: 0; float: left;  z-index: 0;border-right: 1px solid #EEEEEE;">


                    <h3 style="text-align: center;">Stavební příprava</h3>
                    <form id="rootwizard" method="post" action="" class="form-horizontal form-wizard"
                        style="margin-top: 60px;">

                        <div class="steps-progress">
                            <div class="progress-indicator"></div>
                        </div>

                        <ul>
                            <li <?php if (isset($getclient['technical']) && $getclient['technical'] == 0) {
            echo 'class="completed red"';
        } ?>>
                                <a
                                    href="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=edit_sold&type=technical&value=0"><span>0</span>K zavolání</a>
                            </li>
                            <li <?php if (isset($getclient['technical']) && $getclient['technical'] == 1) {
            echo 'class="completed yellow"';
        } ?>>
                                <a
                                    href="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=edit_sold&type=technical&value=1"><span>1</span>Odeslaný
                                    email</a>
                            </li>
                            <li <?php if (isset($getclient['technical']) && $getclient['technical'] == 2) {
            echo 'class="completed orange"';
        } ?>>
                                <a
                                    href="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=edit_sold&type=technical&value=2"><span>2</span>V
                                    řešení</a>
                            </li>
                            <li <?php if (isset($getclient['technical']) && $getclient['technical'] == 3) {
            echo 'class="completed green"';
        } ?>>
                                <a
                                    href="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=edit_sold&type=technical&value=3"><span>3</span>Komplet</a>
                            </li>

                        </ul>

                    </form>

                </section>


            <section class="profile-feed sold-management"
                     style=" width: 19%;    padding: 0; float: left;  z-index: 0; border-right: 1px solid #EEEEEE;">

                <h3 style="text-align: center;">Realizace</h3>

                <?php

                if (isset($getclient['realization']) && $getclient['realization'] != '0000-00-00') {
                ?>
                <form id="rootwizard" method="post" action="" class="form-horizontal form-wizard"
                      style="margin-top: 60px;">

                    <div class="steps-progress">
                        <div class="progress-indicator"></div>
                    </div>

                    <ul>
                        <li <?php

                        if (isset($getclient['confirmed']) && $getclient['confirmed'] == 0) {
                            echo 'class="completed teal"';
                        } ?>>
                            <a  href="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=edit_sold&type=realization&value=0">
                                <span>0</span>
                                Plánovaná
                            </a>
                        </li>
                        <li <?php if(isset($getclient['confirmed']) && $getclient['confirmed'] == 1) {
                            echo 'class="completed green"';
                        } ?>>
                            <a href="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=edit_sold&type=realization&value=1">
                                <span>1</span>
                                Potvrzená
                            </a>
                        </li>

                    </ul>

                </form>


                <?php }else{ ?>

                <div class="alert alert-info" style="margin: 40px 20px; text-align: center;">realizace zatím není naplánovaná</div>

                <?php } ?>
            </section>

                <section class="profile-feed sold-management"
                    style="    width: 26%;    padding: 0; float: left;  z-index: 0;">

                    <h3 style="text-align: center;">Nedokončené</h3>
                    <form id="rootwizard" method="post" action="" class="form-horizontal form-wizard"
                        style="margin-top: 60px;">

                        <div class="steps-progress">
                            <div class="progress-indicator"></div>
                        </div>

                        <ul>
                            <li <?php if (isset($getclient['unfinished']) && $getclient['unfinished'] == 0) {
            echo 'class="completed red"';
        } ?>>
                                <a
                                    href="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=edit_sold&type=unfinished&value=0"><span>0</span>Neřešeno</a>
                            </li>
                            <li <?php if (isset($getclient['unfinished']) && $getclient['unfinished'] == 1) {
            echo 'class="completed orange"';
        } ?>>
                                <a
                                    href="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=edit_sold&type=unfinished&value=1"><span>1</span>V
                                    řešení</a>
                            </li>
                            <li <?php if (isset($getclient['unfinished']) && $getclient['unfinished'] == 2) {
            echo 'class="completed green"';
        } ?>>
                                <a
                                    href="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=edit_sold&type=unfinished&value=2"><span>2</span>Připravená</a>
                            </li>

                        </ul>

                    </form>

                </section>


                <?php } ?>

                <div class="clear"></div>

                <hr style="margin: 12px;">
                <?php } ?>



                <section class="profile-feed"
                    style="    width: 50%;    padding: 0% 2% 0 1%;float: left;  z-index: 0;border-right: 1px solid #EEEEEE;">
                    <h2>Poznámky</h2>
                    <hr>


                    <!-- profile stories -->
                    <div class="profile-stories" style="margin-top: 30px;">

                        <?php

    $check_starred = $mysqli->query('SELECT * FROM demands_timeline WHERE client_id = "' . $getclient['id'] . '" AND star = "1"') or die($mysqli->error);

    $demandstextquery = $mysqli->query('SELECT *, DATE_FORMAT(datetime, "%d. %m. %Y") as dateformated, DATE_FORMAT(datetime, "%H:%i") as hoursmins FROM demands_timeline WHERE client_id="' . $getclient['id'] . '" ORDER BY id desc') or die($mysqli->error);
    if (mysqli_num_rows($demandstextquery) > 0) {
        while ($demandstext = mysqli_fetch_assoc($demandstextquery)) {
            $adminquery = $mysqli->query('SELECT user_name, avatar, id FROM demands WHERE id="' . $demandstext['admin_id'] . '"') or die($mysqli->error);
            $admin = mysqli_fetch_assoc($adminquery); ?>


                        <article id="story-<?= $demandstext['id'] ?>"
                            class="story  <?php if (isset($demandstext['star']) && $demandstext['star'] == 1) { ?>starred<?php } ?>"
                            style="margin: 0; margin-bottom: 10px; padding: 8px 30px; <?php if (isset($demandstext['star']) && $demandstext['star'] == 1) {
                echo 'font-weight: bold; background-color: #ebf1f6;  border-radius: 3px; padding-top: 30px; padding-bottom: 12px;';
            } ?>">

                            <aside class="user-thumb">
                                <a href="#">
                                    <img src="/admin/assets/avatars/<?= $admin['id'] ?>.jpg" alt=""
                                        width="44" class="img-circle" />
                                </a>
                            </aside>

                            <div class="story-content" style="width: 88%;">

                                <!-- story header -->
                                <header>

                                    <div class="publisher">
                                        <a href="#"><?= $admin['user_name'] ?></a>
                                        <em><?= $demandstext['dateformated'] . ' v ' . $demandstext['hoursmins'] ?></em>
                                    </div>



                                    <?php if (isset($access_edit) && $access_edit) { ?>
                                    <div class="story-type" style="margin-left: 10px;margin-right: 6px;">
                                        <span style=" border-right: 1px solid #cccccc; margin-right: 8px;"></span>
                                        <a data-id="<?= $demandstext['id'] ?>" class="remove-note"
                                            style="color:#949494; cursor: pointer;"><i class="entypo-trash"></i></a>

                                    </div><?php } ?>

                                    <div data-id="<?= $demandstext['id'] ?>" class="kill-note story-type"
                                        style="margin-left: 10px;cursor: pointer;">
                                        <i class="fa fa-strikethrough"></i>
                                    </div>

                                    <div data-id="<?= $demandstext['id'] ?>" class="bold-note story-type"
                                        style="margin-left: 10px;cursor: pointer;">
                                        <i class="fa fa-bold"></i>
                                    </div>

                                    <div data-id="<?= $demandstext['id'] ?>" class="star-note star story-type" style="cursor: pointer; <?php if (mysqli_num_rows($check_starred) > 0 && $demandstext['star'] != 1) {
                echo 'display: none;';
            } ?>">
                                        <i class="fa fa-star"></i>
                                    </div>

                                </header>

                                <div class="story-main-content">
                                    <p id="killed-<?= $demandstext['id'] ?>" style="<?php if (isset($demandstext['line']) && $demandstext['line'] == 1) {
                echo 'text-decoration: line-through;';
            }
            if (isset($demandstext['bold']) && $demandstext['bold'] == 1) {
                echo 'font-weight: bold;';
            }
            if (isset($demandstext['star']) && $demandstext['star'] == 1) {
                echo 'font-weight: bold; color: #303641;';
            } ?>"><?= $demandstext['text'] ?>
                                    </p>
                                </div>


                                <!-- separator -->
                                <hr style="margin-top: 19px; margin-bottom: 0px;" />

                            </div>

                        </article>
                        <?php
}
    } else { ?>

                        <article id="story" class="story" style="margin: 0; margin-bottom: 21px;">


                            <div class="story-content" style="width: 100%; text-align:center;">
                                <!-- story header -->
                                <header>

                                    <div>
                                        <em>U poptávky zatím nejsou žádné poznámky.</em>
                                    </div>



                                </header>
                                <!-- separator -->
                                <hr style="margin-top: 24px; margin-bottom: 0px;" />



                            </div>

                        </article>

                        <?php } ?>

                        <!--<div class="text-center">
        <a href="#" class="btn btn-default btn-icon icon-left" style="adding-right: 14px;">
          <i class="fa fa-angle-double-right" style="padding: 6px 11px;"></i>
          Zobrazit všechny
        </a>
      </div>-->

                    </div>

                    <!-- profile post form -->
                    <form class="profile-post-form" method="post" enctype="multipart/form-data"
                        action="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=add">

                        <textarea class="form-control autogrow" name="text"
                            placeholder="Stalo se něco nového?"></textarea>

                        <div class="form-options">

                            <div class="post-type" style="display: none;">

                                <a href="#" class="tooltip-primary" data-toggle="tooltip" data-placement="bottom"
                                    title="" data-original-title="Nahrát obrázek">
                                    <i class="entypo-camera"></i>
                                </a>

                                <a href="#" class="tooltip-primary" data-toggle="tooltip" data-placement="bottom"
                                    title="" data-original-title="Přidat soubor">
                                    <i class="entypo-attach"></i>
                                </a>

                                <a href="#" class="tooltip-primary" data-toggle="tooltip" data-placement="bottom"
                                    title="" data-original-title="Adresa">
                                    <i class="entypo-location"></i>
                                </a>
                            </div>

                            <div class="post-submit">
                                <button type="submit" class="btn btn-primary">Přidat poznámku</button>
                            </div>

                        </div>
                    </form>

                </section>


                <script type="text/javascript">
                            $(document).ready(function() {

                            $('.remove-note').click(function(e) {

                                e.preventDefault();

                                var id = $(this).data("id");

                                $("#story-"+id)
                                    .hide("slow");

                                $.get(
                                    "/admin/controllers/demands-timeline?type=remove&id="+id
                                );

                            });

                            $('.kill-note').click(function(e) {

                                e.preventDefault();
                                var id = $(this).data("id");

                                if ($("#killed-"+id).css('text-decoration') == 'line-through solid rgb(148, 148, 148)') {

                                    $("#killed-"+id)
                                        .css(
                                            "text-decoration", "none");

                                    $.get(
                                        "/admin/controllers/demands-timeline?type=line&id="+id+"&turn=off"
                                    );

                                } else {

                                    $("#killed-"+id)
                                        .css(
                                            "text-decoration", "line-through");

                                    $.get(
                                        "/admin/controllers/demands-timeline?type=line&id="+id+"&turn=on"
                                    );

                                }
                            });

                            $('.bold-note').click(function(e) {

                                e.preventDefault();

                                var id = $(this).data("id");


                                if ($("#killed-"+id).css('font-weight') == '700' && $("#story-"+id).css("font-weight") != '700') {


                                    $("#killed-"+id)
                                        .css("font-weight",
                                            "normal");

                                    $.get(
                                        "/admin/controllers/demands-timeline?type=bold&id="+id+"&turn=off"
                                    );


                                } else {

                                    $("#killed-"+id).css("font-weight", "bold");

                                    $.get(
                                        "/admin/controllers/demands-timeline?type=bold&id="+id+"&turn=on"
                                    );

                                }

                            });

                            $('.star-note').click(function(e) {


                                e.preventDefault();
                                var id = $(this).data("id");

                                if ($("#story-"+id)
                                    .hasClass('starred')) {

                                    $(".star").show();

                                    $("#story-"+id)
                                        .removeClass(
                                            'starred')
                                        .css({
                                            "font-weight" : "normal",
                                            "border-radius" : "0px"
                                             }).animate({
                                            "background-color": "#fff",
                                            "padding": "0",
                                            "paddingBottom": "0"
                                        });

                                    $("#killed-"+id)
                                        .css("font-weight",
                                            "normal").animate({
                                            "color": "#949494"
                                        });

                                    $.get(
                                        "/admin/controllers/demands-timeline?type=star&id="+id+"&turn=off"
                                    );

                                } else {

                                    $(".star").hide();

                                    $("#story-"+id)
                                        .find(".star").show();

                                    $("#story-"+id)
                                        .animate({
                                            "background-color": "#ebf1f6",
                                            "padding": "30",
                                            "paddingBottom": "12"
                                        }).addClass('starred')
                                        .css({
                                            "font-weight": "bold",
                                            "border-radius": "3px"
                                        });

                                    $("#killed-"+id)
                                        .css("font-weight",
                                            "bold")
                                        .animate({
                                            "color": "#303641"
                                        });

                                    $.get(
                                        "/admin/controllers/demands-timeline?type=star&id="+id+"&turn=on"
                                    );

                                }
                            });
                        });

                        </script>


                <?php
$cliquery = $mysqli->query('SELECT id, user_name FROM demands WHERE role != "client" AND active = 1') or die($mysqli->error); ?>
                <section class="profile-feed" style="    width: 50%;    padding: 0% 1% 0 2%;float: left;  z-index: 0;">
                    <h2>Úkoly</h2>
                    <hr>



                    <div class="panel-group" id="accordion-test">

                        <?php

    $demandstasksquery = $mysqli->query('SELECT *, DATE_FORMAT(date, "%d. %m. %Y") as dateformated, DATE_FORMAT(due, "%d. %m. %Y") as dueformated FROM tasks WHERE demand_id = "' . $getclient['id'] . '" ORDER BY id desc') or die($mysqli->error);
    if (mysqli_num_rows($demandstasksquery) > 0) {
        while ($demandstasks = mysqli_fetch_assoc($demandstasksquery)) {
            task($demandstasks, $client['avatar'], $access_edit, 'pages/demands/zobrazit-poptavku&redirectid=' . $getclient['id']);
        }
    } else { ?>


                        <div class="panel panel-default" style="margin-bottom: 13px;  ">
                            <div class="panel-heading">
                                <h4 class="panel-title"
                                    style="height: 100px; line-height: 80px; padding-bottom: 0px; text-align:center;">
                                    U poptávky zatím nejsou žádné zadané úkoly.
                                </h4>

                            </div>

                        </div>



                        <?php } ?>
                    </div>

                    <center><button id="addtask" type="button" class="btn btn-primary"
                            style="height: 71px; width: 300px;margin-bottom: 14px;  font-size: 17px;">Přidat
                            úkol</button></center>
                    <form id="taskform" role="form" method="post" enctype='multipart/form-data' autocomplete="off"
                        action="/admin/controllers/task-controller?id=<?= $getclient['id'] ?>&task=add&redirect=pages/demands/zobrazit-poptavku&redirectid=<?= $getclient['id'] ?>"
                        style="display: none; ">


                        <input type="text" style="display: none;" name="choosed_who" value="demand">
                        <input type="text" style="display: none;" name="demandus" value="<?= $getclient['id'] ?>">
                        <input type="text" style="width: 49%; float: left; margin: 0 10px 10px 0;" name="title" placeholder="Krátký název úkolu" class="form-control" id="field-1" required>
                        <input type="text" style="width: 37%; float: left; margin: 0 0 10px 0;" name="datum" class="form-control datepicker" data-format="yyyy-mm-dd" placeholder="Datum provedení" required>
                        <input type="text" style="width: 12%" class="form-control timepicker" name="time"
                            data-template="dropdown" data-show-seconds="false" data-default-time="00-00"
                            data-show-meridian="false" data-minute-step="5" placeholder="Čas" />

                        <div class="form-group well admins_well"
                             style="background-color: #FFFFFF; padding: 12px 0px 7px; float: left; width: 100%; ">
                            <h4 style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">Proveditelé</h4>

                            <?php

                            $adminsquery = $mysqli->query("SELECT id, user_name FROM demands WHERE role != 'client' AND active = 1 AND active = 1");

                            while ($admins = mysqli_fetch_array($adminsquery)) { ?>

                                <div class="col-sm-3">

                                    <input id="admin-<?= $admins['id'] ?>-event-performer" name="performer[]"
                                           value="<?= $admins['id'] ?>" type="checkbox">
                                    <label for="admin-<?= $admins['id'] ?>-event-performer"
                                           style="padding-left: 4px; cursor: pointer;"><?= $admins['user_name'] ?></label>

                                </div>

                            <?php } ?>


                        </div>
                        <div class="form-group well admins_well"
                             style="background-color: #FFFFFF; padding: 12px 0px 7px; float: left; width: 100%;">

                            <h4
                                style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">
                                Informovaní</h4>

                            <?php mysqli_data_seek( $adminsquery, 0 );

                            while($admins = mysqli_fetch_array($adminsquery)){ ?>

                                <div class="col-sm-3">

                                    <input id="admin-<?= $admins['id'] ?>-event-observer" name="observer[]"
                                           value="<?= $admins['id'] ?>" type="checkbox" <?php if($client['id'] == $admins['id']){ echo 'checked';}?>>
                                    <label for="admin-<?= $admins['id'] ?>-event-observer"
                                           style="padding-left: 4px; cursor: pointer;">
                                        <?= $admins['user_name'] ?></label>

                                </div>

                            <?php } ?>

                        </div>

                        <textarea class="form-control autogrow" name="text" placeholder="Popis zadaného úkolu..."
                            style="overflow: hidden; margin-bottom: 8px;word-wrap: break-word; resize: horizontal; height: 80px;"></textarea>
                        <button type="submit" class="btn btn-primary"
                            style="width: 82%; height: 71px; margin-bottom: 14px;  font-size: 17px;">Přidat
                            úkol</button>
                        <button type="button" id="canceladdtask" class="btn btn-default"
                            style="width: 17%; height: 71px; margin-bottom: 14px;  font-size: 17px;"><i
                                class="entypo-cancel"></i></button>
                    </form>


                    <!--<div class="text-center">
        <a href="#" class="btn btn-default btn-icon icon-left" style="adding-right: 14px;">
          <i class="fa fa-angle-double-right" style="padding: 6px 11px;"></i>
          Zobrazit všechny
        </a>
      </div> -->
                </section>
                <div class="clear"></div>
                <hr />
                <?php if ($access_edit) { ?>

                <div class="col-sm-12">
                    <h2 style="text-align: center; text-align: center; margin-top: 36px; margin-bottom: 30px;">Soubory k poptávce</h2>
                    <hr>

                    <?php
        $result = glob($_SERVER['DOCUMENT_ROOT'] . '/data/clients/uploads/' . $getclient['secretstring'] . '/*');
        $i = 0;

        uasort($result, function($a,$b) { return filemtime($a) < filemtime($b); });
        foreach ($result as $res) {
            $filename = pathinfo($res, PATHINFO_FILENAME); ?>
                    <section id="servistab" class="profile-feed well"
                        style="width: 24%; margin-right: 1%; float:left; margin-bottom: 30px;">

                        <div class="profile-stories">

                            <article class="story" style="margin: 0;">
                                <div style="display: block;">
                                    <span style=" width: 100%;">
                                        <h3
                                            style="margin: 2px 0px 0px; text-align: center; min-height: 26px; line-height: 17px; font-size: 13px; overflow: hidden; text-overflow: ellipsis;">
                                            <?= basename($res) ?>
                                        </h3>
                                        <h4 style="font-style: italic; margin: 6px 0px 10px; text-align: center; font-size: 12px;">
                                            <?= date('d. m. Y H:i', filemtime($res)) ?>
                                        </h4>
                                        <?php
$i = $i + 1;
            $ext = pathinfo($res, PATHINFO_EXTENSION);

            if (in_array($ext, $image_extensions)) { ?>
                                        <center><a
                                                href="https://www.wellnesstrade.cz/data/clients/uploads/<?= $getclient['secretstring'] ?>/<?= basename($res) ?>"
                                                target="_blank"><img
                                                    src="https://www.wellnesstrade.cz/data/clients/uploads/<?= $getclient['secretstring'] ?>/<?= basename($res) ?>"
                                                    style="width: 100%;" width="100%" class="img-rounded"></a></center>
                                        <?php } else { ?>
                                        <center>
                                            <a href="https://docs.google.com/viewerng/viewer?url=https://www.wellnesstrade.cz/data/clients/uploads/<?= $getclient['secretstring'] ?>/<?= basename($res) ?>"
                                                target="_blank">
                                                <i class="fa fa-file" style="font-size: 140px;"></i>
                                            </a>
                                        </center>

                                        <?php } ?>
                                        <div class="text-center" style="margin-top: 20px;">
                                            <a id="hidedemand-<?= $filename ?>"
                                                class="btn btn-primary btn-icon icon-left"
                                                style="margin-right: 10px; display: none;">
                                                <i class="entypo-cancel"></i>
                                                Skrýt nabídku
                                            </a>
                                            <a href="https://www.wellnesstrade.cz/data/clients/uploads/<?= $getclient['secretstring'] ?>/<?= basename($res) ?>"
                                                class="btn btn-blue btn-icon icon-left" style="padding-right: 14px;"
                                                download>
                                                <i class="entypo-down"></i>
                                                Stáhnout
                                            </a>
                                            <a href="zobrazit-poptavku?id=<?= $getclient['id'] ?>&upload=remove&name=<?= urlencode(basename($res)) ?>"
                                                class="btn btn-red btn-icon icon-left"
                                                style="padding-right: 14px; margin-left: 4px;">
                                                <i class="entypo-trash"></i>
                                                Smazat
                                            </a>
                                        </div>
                                    </span>
                                </div>

                            </article>

                        </div>

                    </section>

                    <?php
}
        ?>

                    <div style="clear: both;"></div>
                    <div class="profile-stories" style="margin-bottom: 50px;">

                        <article class="story" style="margin: 0 0 50px 0; min-height: 89px; text-align: center;">
                            <form style="text-align: center;" role="form" method="post"
                                action="zobrazit-poptavku?id=<?= $getclient['id'] ?>&action=upload_file"
                                enctype="multipart/form-data">

                                    <input type="file" style="width: 260px; padding-top: 6px; display: inline-block;" class="form-control" name="file"
                                        id="field-file" placeholder="Placeholder">
                                    <button type="submit" class="btn btn-green btn-icon icon-left"> <i class="entypo-plus"></i> Nahrát soubor </button>
                            </form>



                        </article>

                    </div>


                </div>

                <?php

    } ?>




                <div class="clear"></div>

                <div class="profile-env">

                    <section id="demand_pictures" class="profile-info-tabs">


                        <div class="row">
                            <div class="notes-env">


                                <div class="col-sm-6">

                                    <div class="notes-header">
                                        <div class="col-md-6">
                                            <h2 style="margin-left: 20px; margin-top: 8px;">Obrázky technické</h2>
                                        </div>
                                        <div class="col-md-6" style="text-align: right;float:right;">

                                            <a data-id="technical"
                                                class="toggle-picture-upload-modal btn btn-primary btn-icon icon-left btn-lg"
                                                style="margin-top: 3px; ">
                                                <i class="entypo-plus"></i>
                                                Přidat obrázky
                                            </a>

                                        </div>

                                    </div>


                                    <?php

    $files = array_map('basename', glob($_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/technical/' . $getclient['secretstring'] . '/[!-small_]*.{'.extList($image_extensions).'}', GLOB_BRACE));

    ?>
                                    <div id="load-technical" class="notes-list" <?php if (empty($files)) { ?>style="padding:
                                        23px 0 22px;" <?php } ?>>

                                        <ul class="list-of-notes lightgallery" style="padding: 0 20px;">
                                            <?php
if (!empty($files)) {
        foreach ($files as $file) {


            $originalFileName = substr($file, 4);

            // skip thumbs not needed
            //if(substr( $file, 0, 6 ) === "small_"){ continue; }

            $full_image = '/data/clients/pictures/technical/' . $getclient['secretstring'] . '/big_' . $originalFileName;

            if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/technical/' . $getclient['secretstring'] . '/small_' . $originalFileName)) {

                $small_image = '/data/clients/pictures/technical/' . $getclient['secretstring'] . '/small_' . $originalFileName;

            } else {

                $small_image = $full_image;

            } ?>

            <div class="single-picture" style="width: 19%; margin: 10px 0.5% 10px 0; display: inline-block;border: 1px solid #dfdfdf;border-radius: 4px;">
                <a class="remove-picture-technical btn btn-sm btn-danger" style="position: absolute; border: 1px solid #FFF; border-radius: 3px;" data-picture="<?= basename($originalFileName) ?>" data-toggle="tooltip" data-placement="top" title="" data-original-title="Odstranit obrázek">
                    <i class="entypo-trash"></i>
                </a>
                <a data-src="<?= $full_image ?>" href="<?= $full_image ?>" class="full" rel="technical">
                    <img src="<?= $small_image ?>" width="100%" class="img-rounded">
                </a>
            </div>

                                            <?php
}
    } else { ?>
                                            <ul class="cbp_tmtimeline">
                                                <li style=" width: 100%;">

                                                    <div class="cbp_tmicon" style="margin-left: -1px;">
                                                        <i class="entypo-block"
                                                            style="line-height: 42px !important;"></i>
                                                    </div>

                                                    <div class="cbp_tmlabel empty"
                                                        style="margin-top: -29px; margin-bottom: 0px;padding-top: 9px;">
                                                        <span
                                                            style="font-weight: bold; margin-left: -12px;font-size: 17px;">U
                                                            poptávky ještě nejsou žádné obrázky.</span>
                                                    </div>
                                                </li>
                                            </ul>
                                            <?php } ?>
                                            <div class="clear"></div>
                                        </ul>

                                    </div>
                                </div>

                                <div class="col-sm-6">

                                    <div class="notes-header">
                                        <div class="col-md-6">
                                            <h2 style="margin-left: 20px; margin-top: 8px;">Obrázky realizace</h2>
                                        </div>
                                        <div class="col-md-6" style="text-align: right;float:right;">



                                            <a data-id="realization"
                                                class="toggle-picture-upload-modal btn btn-primary btn-icon icon-left btn-lg"
                                                style="margin-top: 3px; ">
                                                <i class="entypo-plus"></i>
                                                Přidat obrázky
                                            </a>

                                        </div>

                                    </div>

                                    <script>
                                    $(document).ready(function() {

                                        $('.remove-picture').click(function() {

                                            $(this).parent(".single-picture").fadeOut();

                                            var id = $(this).data("id");
                                            var picture = $(this).data("picture");

                                            $.get("./zobrazit-poptavku?id=<?= $id ?>&action=remove_picture_realization&picture=" +
                                                picture);

                                        });


                                        $('.remove-picture-technical').click(function() {

                                            $(this).parent(".single-picture").fadeOut();

                                            var id = $(this).data("id");
                                            var picture = $(this).data("picture");

                                            $.get("./zobrazit-poptavku?id=<?= $id ?>&action=remove_picture_technical&picture=" +
                                                picture);

                                        });

                                    });
                                    </script>

                                    <?php

    $files = array_map('basename', glob($_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/realization/' . $getclient['secretstring'] . '/[!-small_]*.{'.extList($image_extensions).'}', GLOB_BRACE));
    ?>
                                    <div id="load-realization" class="notes-list" <?php if (empty($files)) { ?>style="padding:
                                        23px 0 22px;" <?php } ?>>

                                        <ul class="list-of-notes lightgallery">
                                            <?php

    if (!empty($files)) {
        foreach ($files as $file) {

            $originalFileName = substr($file, 4);

            // skip thumbs
            //if(substr( $file, 0, 6 ) === "small_"){ continue; }

            $full_image = '/data/clients/pictures/realization/' . $getclient['secretstring'] . '/big_' . $originalFileName;
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/realization/' . $getclient['secretstring'] . '/small_' . $originalFileName)) {

                $small_image = '/data/clients/pictures/realization/' . $getclient['secretstring'] . '/small_' . $originalFileName;

            } else {

                $small_image = $full_image;

            } ?>


            <div class="single-picture" style="width: 19%; margin: 10px 0.5% 10px 0; display: inline-block;border: 1px solid #dfdfdf;border-radius: 4px;">
                <a class="remove-picture btn btn-sm btn-danger" style="position: absolute; border: 1px solid #FFF; border-radius: 3px;" data-picture="<?= basename($originalFileName) ?>" data-toggle="tooltip" data-placement="top" title="" data-original-title="Odstranit obrázek">
                    <i class="entypo-trash"></i>
                </a>
                <a data-src="<?= $full_image ?>" href="<?= $full_image ?>" class="full" rel="realization">
                    <img src="<?= $small_image ?>" width="100%" class="img-rounded">
                </a>
            </div>


                                            <?php
}
    } else { ?>
                                            <ul class="cbp_tmtimeline">
                                                <li style=" width: 100%;">

                                                    <div class="cbp_tmicon" style="margin-left: -1px;">
                                                        <i class="entypo-block"
                                                            style="line-height: 42px !important;"></i>
                                                    </div>

                                                    <div class="cbp_tmlabel empty"
                                                        style="margin-top: -29px; margin-bottom: 0px;padding-top: 9px;">
                                                        <span
                                                            style="font-weight: bold; margin-left: -12px;font-size: 17px;">U
                                                            poptávky ještě nejsou žádné obrázky.</span>
                                                    </div>
                                                </li>
                                            </ul>
                                            <?php } ?>
                                            <div class="clear"></div>
                                        </ul>

                                    </div>
                                </div>



                            </div><!-- Footer -->
                        </div>
                    </section>

                    <?php

                    $mails_query = $mysqli->query("SELECT * FROM mails_archive WHERE reciever_id = '".$getclient['id']."'")or die($mysqli->error);

                    if(mysqli_num_rows($mails_query) > 0){
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
                                            <?php while($mail = mysqli_fetch_assoc($mails_query)){ ?>
                                                <tr>
                                                    <td><?= $mail['datetime'] ?></td>
                                                    <td><?= $mail['subject'] ?></td>
                                                    <td><?= $mail['admin_id'] ?></td>
                                                    <td><a class="toggle-mail-modal btn btn-primary btn-sm btn-icon icon-left" data-id="<?= $mail['id'] ?>">
                                                            <i class="entypo-search"></i>
                                                            Zobrazit mail
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                            </tbody>
                                        </table>


                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php }
                    ?>
                </div>
                <?php if (isset($getclient['latitude']) && $getclient['latitude'] != '' && $getclient['longitude'] != '') { ?>
                <script type="text/javascript"
                    src="//maps.google.com/maps/api/js?key=AIzaSyDRermPdr7opDFLqmrcOuK5L4zC2_U8XGk&sensor=false">
                </script>
                <script type="text/javascript">
                function showmap() {

                    var directionsDisplay;
                    var directionsService = new google.maps.DirectionsService();
                    var map;
                    calcRoute();

                    function initialize() {
                        directionsDisplay = new google.maps.DirectionsRenderer();
                        var chicago = new google.maps.LatLng( <?php  echo $getclient['latitude'].', '.$getclient['longitude']; ?> );
                        var mapOptions = {
                            zoom: 11,
                            center: chicago
                        };
                        map = new google.maps.Map(document.getElementById('sample-checkin'), mapOptions);
                        directionsDisplay.setMap(map);
                    }

                    function calcRoute() {
                        var start = '50.096500, 14.402800';
                        var end = '<?= $getclient['latitude'] . ', ' . $getclient['longitude'] ?>';
                        var request = {
                            origin: start,
                            destination: end,
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
                </script><!-- Footer -->
                <?php }



                 ?>


                <footer class="main">


                    &copy; <?= date('Y') ?> <span style=" float:right;"><?php
$time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round(($finish - $start), 4);

    echo 'PHP '.PHP_VERSION.' | Page generated in ' . $total_time . ' seconds.'; ?></span>

                </footer>
            </div>

        </div>



    <?php

    include $_SERVER['DOCUMENT_ROOT'] . '/admin/pages/demands/parts/modals.php';



 ?>

        <script type="text/javascript">
            $(document).ready(function() {

                $('.lightgallery').lightGallery({
                    selector: 'a.full'
                });

            });
        </script>

<?php include VIEW . '/default/footer.php'; ?>



<?php


} else {
    include INCLUDES . '/404.php';
}