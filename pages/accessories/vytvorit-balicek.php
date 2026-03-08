<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";




/*
 * todo dokončit nahrání všech příslušenství do products_stocks ke každé pobočce?
 *
 *

$locsArray = [];

$locations = $mysqli->query("SELECT * FROM shops_locations")or die($mysqli->error);
while($loc = mysqli_fetch_assoc($locations)){
    array_push($locsArray, $loc);
}


print_r($locsArray);

$allAccessories = $mysqli->query("SELECT * FROM products WHERE type = 'simple'")or die($mysqli->error);

while($accessory = mysqli_fetch_assoc($allAccessories)){

    echo $accessory['id'];

    foreach($locsArray){}


}




$allAccessories = $mysqli->query("SELECT * FROM products WHERE type = 'variable'")or die($mysqli->error);

while($accessory = mysqli_fetch_assoc($allAccessories)){

    echo $accessory['id'];

}


*/

$categorytitle = "Příslušenství";
$pagetitle = "Přidání příslušenství";

$shops_query = $mysqli->query("SELECT * FROM shops");

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "add") {

    $seoslug = odkazy($_POST['productname']);
    $product_name = $mysqli->real_escape_string($_POST['productname']);

    $type = 'bundle';

    include CONTROLLERS . "/product-stock-controller.php";

    $height = str_replace(',', '.', $_POST['height']);
    $weight = str_replace(',', '.', $_POST['weight']);
    $width = str_replace(',', '.', $_POST['width']);
    $length = str_replace(',', '.', $_POST['length']);

    $cross_selling = '';
    $up_selling = '';

    if(!empty($_POST['cross_selling'])){ $cross_selling = serialize($_POST['cross_selling']); }
    if(!empty($_POST['up_selling'])){ $up_selling = serialize($_POST['up_selling']); }

    $purchase_price = preg_replace("/[^0-9]/", "", $_POST['purchaseprice']);
    $wholesale_price = preg_replace("/[^0-9]/", "", $_POST['wholesaleprice']);
    $price = preg_replace("/[^0-9]/", "", $_POST['price']);

    $code = preg_replace('/\s+/', '', $_POST['code']);
    $ean_main = preg_replace('/\s+/', '', $_POST['ean']);

    $mysqli->query("INSERT INTO products (
        spare_part, 
        manufacturer, 
        delivery_time, 
        main_warehouse, 
        up_selling, 
        cross_selling, 
        height, 
        weight, 
        width, 
        length, 
        type, 
        productname, 
        short_description, 
        description, 
        seourl, 
        availability, 
        ean, 
        code, 
        purchase_price, 
        wholesale_price, 
        price)
	VALUES (
        '" . $_POST['spare_part'] . "', 
	    '" . $_POST['manufacturer'] . "', 
	    '" . $_POST['delivery_time'] . "', 
	    '" . $_POST['main_warehouse'] . "', 
	    '$up_selling', 
	    '$cross_selling', 
	    '$height', 
	    '$weight', 
	    '$width', 
	    '$length', 
	    '$type', 
	    '$product_name', 
	    '" . $_POST['short_description'] . "', 
	    '" . $_POST['description'] . "', 
	    '$seoslug', 
	    '" . $_POST['availability'] . "', 
	    '$ean_main', 
	    '$code', 
	    '$purchase_price', 
	    '$wholesale_price', 
	    '" . $price . "'
    )") or die($mysqli->error);

    $id = $mysqli->insert_id;


    if(!empty($_POST['supplier_first'])){

        $mysqli->query("INSERT IGNORE INTO products_suppliers (product_id, supplier) VALUES ('".$id."', '".$_POST['supplier_first']."')")or die($mysqli->error);

    }

    if(!empty($_POST['supplier_second'])){

        $mysqli->query("INSERT IGNORE INTO products_suppliers (product_id, supplier) VALUES ('".$id."', '".$_POST['supplier_second']."')")or die($mysqli->error);

    }


    $locations_query = $mysqli->query("SELECT id FROM shops_locations");
    while ($location = mysqli_fetch_array($locations_query)) {

        $instock = 0;

        $location_id = $location['id'];
        $instock = $_POST['location_' . $location_id];

        $mysqli->query("INSERT IGNORE INTO products_stocks (product_id, instock, location_id) VALUES ('$id', '$instock','" . $location['id'] . "')") or die($mysqli->error);

    }


    //URL PRO ESHOPY

    mysqli_data_seek($shops_query, 0);
    while ($shop = mysqli_fetch_array($shops_query)) {

        $slug = $shop['slug'];

        $postname = $slug . '_box';

        if ($_POST[$postname] == '1') {

            $sale_price = $_POST[$slug . '_sale_price'];

            $insert = $mysqli->query("INSERT IGNORE INTO products_sites (product_id, site, sale_price) VALUES ('$id', '$slug', '$sale_price')") or die($mysqli->error);

            if (isset($_POST[$slug . '_reciever'])) {

                $errors = array_filter($_POST[$slug . '_reciever']);

                if (!empty($errors)) {

                    $categories = $errors;

                    foreach ($categories as $category) {

                        $insert = $mysqli->query("INSERT IGNORE INTO products_sites_categories (product_id, site, category) VALUES ('$id', '$slug', '$category')") or die($mysqli->error);

                    }

                }
            }

        }
    }

    if ($_FILES['picture']['size'] != 0 && $_FILES['picture']['error'] == 0) {

        $path = $_FILES["picture"]["tmp_name"];
        $filename = $seoslug;

        store_image_resize($path, $filename);

    }

    function any_uploaded($name)
    {
        foreach ($_FILES[$name]['error'] as $ferror) {
            if ($ferror != UPLOAD_ERR_NO_FILE) {
                return true;
            }
        }
        return false;
    }

    if (any_uploaded('otherpics')) {

        function reArrayFiles(&$file_post)
        {

            $file_ary = array();
            $file_count = count($file_post['name']);
            $file_keys = array_keys($file_post);

            for ($i = 0; $i < $file_count; $i++) {
                foreach ($file_keys as $key) {
                    $file_ary[$i][$key] = $file_post[$key][$i];
                }
            }

            return $file_ary;
        }

        $file_ary = reArrayFiles($_FILES['otherpics']);
        $i = 0;
        foreach ($file_ary as $file) {

            $i++;

            $path = $file["tmp_name"];
            $filename = $seoslug . '_' . $i;

            store_image_resize($path, $filename);

        }

    }

    if (isset($_POST['specification_value'])) {

        $specs_values = array_filter($_POST['specification_value']);

    }

    if (isset($_POST['specification_name'])) {

        $specs_name = array_filter($_POST['specification_name']);

    }

    if (!empty($specs_name) && !empty($specs_values)) {

        foreach ($specs_name as $index => $specification) {

            if (!empty($specification) && !empty($specs_values[$index])) {

                $upperName = ucfirst($specification);
                $upperValue = ucfirst($specs_values[$index]);

                $insert = $mysqli->query("INSERT INTO products_specifications (product_id, name, value) VALUES ('$id','$upperName','$upperValue')") or die($mysqli->error);
            }
        }
    }


    if (!empty($_FILES['pdf']['name'])) {

        $targetfolder = $_SERVER['DOCUMENT_ROOT'] . "/data/stores/";

        $targetfolder = $targetfolder . basename($_FILES['pdf']['name']);

        if (move_uploaded_file($_FILES['pdf']['tmp_name'], $targetfolder)) {
            $pdf = basename($_FILES['pdf']['name']);

            $mysqli->query("UPDATE products SET pdf = '" . $pdf . "' WHERE id = '" . $id . "'") or die($mysqli->error);

        } else {

            echo "Problem uploading file";

        }

    }

    Header("Location:https://www.wellnesstrade.cz/admin/pages/accessories/zobrazit-prislusenstvi?id=" . $id . "&success=add");
    exit;
}

include VIEW . '/default/header.php';

?>

<script type="text/javascript">


    function randomPassword(length) {
        var chars = "ABCDEFGHIJKLMNOP1234567890";
        var pass = "";
        for (var x = 0; x < length; x++) {
            var i = Math.floor(Math.random() * chars.length);
            pass += chars.charAt(i);
        }
        return pass;
    }

    function generate() {
        myform.code.value = randomPassword(myform.lengthee.value);
    }


    jQuery(document).ready(function($)
    {

        toastr.options.positionClass = 'toast-top-full-width';
        toastr.options.timeOut = 7000;
        toastr.options.extendedTimeOut = 1000;
        toastr.options.closeButton = true;
        toastr.options.showEasing = 'swing';
        toastr.options.hideEasing = 'linear';
        toastr.options.showMethod = 'fadeIn';
        toastr.options.hideMethod = 'fadeOut';
        toastr.options.progressBar = true;

        function countInArray(array, what) {
            var count = 0;
            for (var i = 0; i < array.length; i++) {
                if (array[i] === what) {
                    count++;
                }
            }
            return count;
        }


        $("#product_form").submit(function () {


            var pass = true;

            // EAN check
            var eanArray = $('form .ean').map(function(){
                return $(this).val()
            }).get();


            var id = 0;

            $("form .ean").each(function () {

                var $this = $(this);
                var check = $(this).val();

                if(countInArray(eanArray, check) > 1){

                    $($this).closest('.form-group').addClass('has-error');
                    toastr.error('V této kartě má více položek stejný EAN kód.');


                    pass = false;
                    return false;

                }else if(check == ''){

                    $(this).closest('.form-group').addClass('has-error');
                    toastr.error('Není vyplněný EAN kód.')

                    pass = false;
                    return false;

                }else{

                    var result = $.ajax({
                        type: 'GET',
                        url: "../../controllers/stores/unique_sku",
                        async: false,
                        dataType: 'json',
                        data: { id: id, check: check, type: 'ean' },
                        done: function(results) {

                            JSON.parse(results);
                            return results;

                        },
                        fail: function( jqXHR, textStatus, errorThrown ) {
                            console.log( 'Could not get posts, server response: ' + textStatus + ': ' + errorThrown );
                        }
                    }).responseJSON; // <-- this instead of .responseText

                    if (result.state == 'failure') {

                        $($this).closest('.form-group').addClass('has-error');
                        toastr.error('Duplicitní EAN kód. - jako u <a href="./zobrazit-prislusenstvi?id=' + result.duplicate_id + '" target="_blank">produktu ID ' + result.duplicate_id + ' → zobrazit</a>')


                        pass = false;
                        return false;

                    } else {

                        $($this).closest('.form-group').removeClass('has-error');

                    }

                }

            });



            // SKU check
            var skuArray = $('form .sku').map(function(){
                return $(this).val()
            }).get();


            $("form .sku").each(function () {

                var $this = $(this);
                var check = $(this).val();

                if(countInArray(skuArray, check) > 1){

                    $($this).closest('.form-group').addClass('has-error');
                    toastr.error('V této kartě má více položek stejný SKU kód.');

                    pass = false;
                    return false;

                }else if(check == ''){

                    $(this).closest('.form-group').addClass('has-error');
                    toastr.error('Není vyplněný SKU kód');
                    pass = false;
                    return false;

                }else{

                    var result = $.ajax({
                        type: 'GET',
                        url: "../../controllers/stores/unique_sku",
                        async: false,
                        dataType: 'json',
                        data: { id: id, check: check, type: 'sku' },
                        done: function(results) {
                            JSON.parse(results);
                            return results;
                        },
                        fail: function( jqXHR, textStatus, errorThrown ) {
                            console.log( 'Could not get posts, server response: ' + textStatus + ': ' + errorThrown );
                        }
                    }).responseJSON; // <-- this instead of .responseText

                    if (result.state == 'failure') {

                        $($this).closest('.form-group').addClass('has-error');
                        toastr.error('Duplicitní SKU kód. - jako u <a href="./zobrazit-prislusenstvi?id=' + result.duplicate_id + '" target="_blank">produktu ID ' + result.duplicate_id + ' → zobrazit</a>');

                        pass = false;
                        return false;

                    } else {

                        $($this).closest('.form-group').removeClass('has-error');

                    }
                };



            });


            if(!pass){

                return false;

            }

        });




        myform.code.value = randomPassword(myform.lengthee.value);
        myform.ean.value = randomPassword(myform.lengthee.value);






        function duplicate_variation(i) {

            console.log(i);

            $('#variation_copy').clone(true).insertBefore("#duplicate_variation").attr('id', 'copied').show();

            $('#copied').find('input').each(function() {

                let id = $(this).attr('id')

                $(this).attr('name', 'item['+i+']['+id+']');

                if(id == 'availability'){

                    let value = $(this).attr('value');

                    $(this).attr('id', 'avai'+i+'-'+value);
                    $(this).parent().find('label').attr('for', 'avai'+i+'-'+value);

                }

            });

            $('#vari_specifi_buttonus').clone(true).insertBefore("#copied #vari_herus_buttonus").show();

            $('#variation_copy_image').clone(false).insertBefore("#copied #variation_copy_image_here").show();

            $('#copied #copy_this_picture_var').attr('name', 'variation_picture['+i+']');
            $('#copied #copy_this_hidus_cislus').attr('value', i);

            $('#copied #copy_this_first_var').attr('name', 'item['+i+'][variation_name][]');
            $('#copied #copy_this_second_var').attr('name', 'item['+i+'][variation_value][]');

            $('#copied #vari_herus_buttonus').attr('id', 'fin_copied');

            $('#copied').attr('id', 'fin_coppied');

        }


        var i = 0;



        $('#variantsbox').on('ifChecked', function(event){

            i++;

            duplicate_variation(i);

            $(this).parent('.icheckbox_line-grey').removeClass('icheckbox_line-grey').addClass('icheckbox_line-green');

            $('.variants').show( "slow");

            $('.hidden-ean').addClass("ean");
            $('.hidden-sku').addClass("sku");

            $('.hide_on_variation').hide( "slow");

        });



        $('#variantsbox').on('ifUnchecked', function(event){

            $(this).parent('.icheckbox_line-green').removeClass('icheckbox_line-green').addClass('icheckbox_line-grey');

            $('form .variation_copy').remove();

            $('.variants').hide( "slow");

            $('.hidden-ean').removeClass("ean");
            $('.hidden-sku').removeClass("sku");

            $('.hide_on_variation').show( "slow");

        });





        $('.remove_shop_category').click(function() {
            $(this).closest('.hovnus').remove();
            event.preventDefault();
        });



        $('#duplicate_specification').click(function() {

            $('#specification_copy').clone(true).insertBefore("#duplicate_specification").attr('id', 'copied').show();
            $('#copied #copy_this_first').attr('name', 'specification_name[]');
            $('#copied #copy_this_second').attr('name', 'specification_value[]');


        });

        $('.remove_specification').click(function() {
            $(this).closest('.specification').remove();
            event.preventDefault();
        });



        $('.duplicate_specifi_vari').click(function() {

            $('#vari_speci').clone(true).insertBefore(this).attr('id', 'vari_coppied').show();

            var bla = $(this).next('#copy_this_hidus_cislus').val();

            $('#vari_coppied #copy_this_first_vari_speci').attr('name', 'item['+bla+'][variation_name][]');
            $('#vari_coppied #copy_this_second_vari_speci').attr('name', 'item['+bla+'][variation_value][]');

            $('#vari_coppied').attr('id', 'fin_vari_coppied');


        });

        $('.remove_specifi_vari').click(function() {
            $(this).closest('#fin_vari_coppied').remove();
            event.preventDefault();
        });






        $('#duplicate_variation').click(function() {

            i++;

            duplicate_variation(i);


        });

        $('.remove_variation').click(function() {
            $(this).closest('.variation').remove();
            event.preventDefault();
        });


        $('.clickus').click(function() {
            var bar = new ProgressBar.Line(progress, {
                strokeWidth: 5,
                easing: 'easeInOut',
                duration: 1400,
                color: '#ec5956',
                trailColor: '#c1c3c6',
                trailWidth: 5,
                svgStyle: {width: '100%', height: '100%'}
            });

            bar.animate(0.8);  // Number from 0.0 to 1.0



            setTimeout(function(){

                bar.animate(1.0);  // Number from 0.0 to 1.0

            }, 1600);


        });

    });


</script>


<form role="form" id="product_form" method="post" name="myform" class="form-horizontal form-groups-bordered validate" action="pridat-prislusenstvi?action=add" enctype="multipart/form-data">

    <input type="hidden" name="lengthee" value="7">

    <div class="row">

        <div class="col-md-6">

            <div class="panel panel-primary" data-collapsed="0">

                <div class="panel-heading">
                    <div class="panel-title">
                        <strong style="font-weight: 600;">Základní údaje</strong>
                    </div>

                </div>

                <div class="panel-body">


                    <div class="form-group form-label-group" style="padding-left: 15px !important">
                        <div class="col-lg-12 col-sm-12">
                            <input type="text" class="form-control" id="productname" name="productname" value="" placeholder="Název">
                            <label for="productname">Název</label>
                        </div>
                    </div>


                    <div class="form-group form-label-group" style="padding-left: 15px !important">
                        <div class="col-lg-12 col-sm-12">
                            <textarea class="form-control" name="short_description" id="short_description" rows="2" placeholder="Krátký popis"></textarea>
                        </div>
                    </div>


                    <div class="form-group">

                        <div class="col-sm-12">
                            <textarea class="form-control summernote" rows="18" name="description" id="contents"></textarea>
                        </div>
                    </div>





                    <!--                            <hr class="hr-text" data-content="Dostupnost">-->
                    <hr>

                    <style>

                        .form-label-group { margin-top: 16px !important; margin-bottom: 6px !important; padding-left: 0 !important; padding-right: 10px !important}

                        hr{
                            float: left;width: 100%;margin: 8px 0;
                        }


                        .radio-new {
                            float: left;  border: 1px solid #ebebeb; border-radius: 4px; padding: 12px 35px 6px 30px; margin-left: 10px; text-align: center; cursor: pointer; color: #666;
                        }
                        .radio-new label { cursor: pointer;}


                        .checkbox {
                            float: left;  border: 1px solid #ebebeb; border-radius: 4px; padding: 12px 20px 6px 30px !important; margin-left: 10px; cursor: pointer; color: #666;
                        }
                        .checkbox label { cursor: pointer;}


                    </style>

                    <div class="form-group" style="margin-top: 6px; margin-bottom: 6px; float: left;">
                        <div class="col-sm-12" style="padding-left: 2px;">
                            <div style="float: left">
                                <label for="instock" class="radio-new">
                                    <input id="instock" type="radio" name="availability" value="0" checked>
                                    <label for="instock" >Skladem</label>
                                </label>
                                <label for="on-order" class="radio-new">
                                    <input id="on-order" type="radio" name="availability" value="2">
                                    <label for="on-order">Na objednávku</label>
                                </label>
                                <label for="hidden" class="radio-new">
                                    <input id="hidden" type="radio" name="availability" value="3">
                                    <label for="hidden">Skryto</label>
                                </label>
                                <label for="unavailable" class="radio-new">
                                    <input id="unavailable" type="radio" name="availability" value="4">
                                    <label for="unavailable">Nedostupné</label>
                                </label>
                            </div>

                            <div class="form-check" style="margin: 0; float: right;">
                                <label for="spare_part" class="checkbox checkbox-success">
                                    <input type="checkbox" id="spare_part" name="spare_part" value="1">
                                    <label for="spare_part">
                                        Náhradní díl
                                    </label>
                                </label>
                            </div>
                        </div>

                    </div>

                    <!--                            <hr class="hr-text" data-content="Ceny">-->
                    <hr>

                    <div class="form-group form-label-group hide_on_variation" style="margin-left: 0 !important; margin-bottom: 6px !important; float: left; width: 100%;">
                        <div class="col-lg-3 col-md-4 col-sm-4 has-metric">
                            <input type="number" class="form-control" id="price" name="price" value="" placeholder="Cena">
                            <label for="price">Cena</label>
                            <span class="input-group-addon">Kč</span>
                        </div>

                        <div class="col-lg-3 col-md-4 col-sm-4 has-metric hide_on_variation">
                            <input type="number" class="form-control" id="purchaseprice" name="purchaseprice" value="" placeholder="Nákupní cena">
                            <label for="purchaseprice">Nákupní cena</label>
                            <span class="input-group-addon">Kč</span>
                        </div>

                        <div class="col-lg-4 col-md-4 col-sm-4 has-metric hide_on_variation">
                            <input type="number" class="form-control" id="wholesaleprice" name="wholesaleprice" value="" placeholder="Velkoobchodní cena">
                            <label for="wholesaleprice">Velkoobchodní cena</label>
                            <span class="input-group-addon">Kč</span>
                        </div>

                        <hr>

                    </div>

                    <!--                        <hr class="hr-text" data-content="Značení položky">-->

                    <div class="form-group col-sm-12" style="float: left;">
                        <div class="form-group form-label-group col-sm-6 has-button" style="padding-left: 0 !important; padding-right: 30px !important;">
                            <input type="text" class="form-control sku" name="code" value="" placeholder="Kód SKU *" style="width: calc(100% - 140px); float: left;">
                            <label for="code">Kód SKU*</label>

                            <input type="button" class="btn btn-default" value="Vygenerovat" onClick="generate();" tabindex="2" style="width: 140px;">
                            <!--                           <div class="invalid-feedback">-->
                            <!--                                Please choose a username.-->
                            <!--                            </div>-->
                        </div>
                        <div class="form-group form-label-group col-sm-6" style="padding-left: 0 !important;  padding-right: 30px !important;"">
                        <input type="text" class="form-control ean" name="ean" value="" placeholder="EAN kód">
                        <label for="ean">EAN kód*</label>
                    </div>


                </div>


                <!--                    <hr class="hr-text" data-content="Informace o dodavateli">-->
                <hr>
                <div style="display: inline-block; width: 100%; margin-top: 14px;">
                    <div class="form-group form-label-group" style="float: left; width: 50%; margin: 0; padding: 0 !important;">

                        <div class="col-sm-6">

                            <?php $manufactures_query = $mysqli->query("SELECT * FROM products_manufacturers WHERE type = 'manufacturer'") or die($mysqli->error);?>

                            <label for="manufacturer" class="col-sm-3 control-label" style="margin-top: -40px;">Výrobce</label>


                            <div class="col-sm-12" style="padding-left: 2px;">
                                <select name="manufacturer" class="form-control">

                                    <option value="">Vyberte výrobce</option>

                                    <?php while ($manufacturer = mysqli_fetch_array($manufactures_query)) { ?>
                                        <option value="<?= $manufacturer['id'] ?>"><?= $manufacturer['manufacturer'] ?></option>
                                    <?php } ?>

                                </select>
                            </div>

                        </div>

                        <div class="col-sm-6">

                            <div class="col-sm-12 has-metric" style="padding: 0;">
                                <input type="number" class="form-control" id="delivery_time" name="delivery_time" value="" placeholder="Speciální čas doručení">
                                <label for="delivery_time">Čas doručení</label>
                                <span class="input-group-addon">dnů</span>
                            </div>

                            <small> 0 = čas dle výrobce nebo 14 dnů </small>

                        </div>

                    </div>


                    <?php

                    $supplier_query = $mysqli->query("SELECT * FROM products_manufacturers WHERE type = 'supplier' ORDER BY manufacturer") or die($mysqli->error);

                    ?>


                    <div class="form-group form-label-group" style="float: right; width: 50%; margin: 0; padding: 0 !important;">

                        <div class="col-sm-6" style="padding: 0;">

                            <label for="supplier_first" class="col-sm-3 control-label" style="margin-top: -40px;">Dodavatel #1</label>

                            <div class="col-sm-12" style="padding-left: 2px;">
                                <select name="supplier_first" class="form-control">

                                    <option value="">Vyberte dodavetele</option>

                                    <?php while($supplier = mysqli_fetch_assoc($supplier_query)) {

                                        ?>
                                        <option value="<?= $supplier['id'] ?>"><?= $supplier['manufacturer'] ?></option>
                                    <?php } ?>

                                </select>
                            </div>

                        </div>

                        <?php
                        mysqli_data_seek($supplier_query, 0);
                        ?>

                        <div class="col-sm-6">

                            <label for="supplier_second" class="col-sm-3 control-label" style="margin-top: -40px;">Dodavatel #2</label>

                            <div class="col-sm-12" style="padding-left: 2px;">
                                <select name="supplier_second" class="form-control">

                                    <option value="">Vyberte dodavetele</option>

                                    <?php while($supplier = mysqli_fetch_assoc($supplier_query)) {

                                        ?>
                                        <option value="<?= $supplier['id'] ?>" <?php if (!empty($d[1]) && $d[1] == $supplier['id']) {echo 'selected';}?>><?= $supplier['manufacturer'] ?></option>
                                    <?php } ?>

                                </select>
                            </div>

                        </div>

                    </div>

                </div>

                <!--                    <hr class="hr-text" data-content="Skladovost">-->



            </div>

        </div>

        <div class="panel panel-primary" data-collapsed="0">

            <div class="panel-heading">
                <div class="panel-title">
                    <strong style="font-weight: 600;">Specifikace & Technické informace</strong>
                </div>

            </div>

            <div class="panel-body">

                <div class="form-group">
                    <label class="col-sm-3 control-label">Specifikace</label>

                    <div class="col-sm-9" style="padding:0;">


                        <div class="col-sm-12" style="float:left; padding-left: 0;">
                            <div id="specification_copy" class="specification" style="display: none; float:left; width: 100%;">
                                <div class="col-sm-4" style="margin-bottom: 8px; padding: 0;">
                                    <input type="text" class="form-control" id="copy_this_first" name="copythis" value="" placeholder="Název">
                                </div>
                                <div class="col-sm-7" style="padding: 0 0px 0 8px;">
                                    <input type="text" class="form-control" id="copy_this_second" name="copythis" value="" placeholder="Hodnota">
                                </div>
                                <i class="remove_specification entypo-trash" style="float:left; margin-top: 8px; margin-left: 10px; cursor: pointer;"></i>
                            </div>

                            <div class="specification" style="float: left; width: 100%;">
                                <div class="col-sm-4" style="margin-bottom: 8px; padding: 0;">
                                    <input type="text" class="form-control" id="specification_name" name="specification_name[]" value="" placeholder="Název">
                                </div>
                                <div class="col-sm-7" style="padding: 0 0px 0 8px;">
                                    <input type="text" class="form-control" id="specification_value" name="specification_value[]" value="" placeholder="Hodnota">
                                </div>
                                <i class="remove_specification entypo-trash" style="float:left; margin-top: 8px; margin-left: 10px; cursor: pointer;"></i>
                            </div>

                            <button type="button" id="duplicate_specification" style="float: left;width: 100%;" class="btn btn-default btn-icon icon-left">
                                Přidat další specifikaci
                                <i class="entypo-plus"></i>
                            </button>
                        </div>
                    </div>


                </div>

                <hr>

                <div class="form-group form-label-group" style="padding-left: 16px !important;">
                    <div class="col-sm-3 has-metric">
                        <input type="number" class="form-control" id="weight" name="weight" value="" placeholder="Váha">
                        <label for="weight">Váha</label>
                        <span class="input-group-addon">Kg</span>
                    </div>

                    <div class="col-sm-3 has-metric">
                        <input type="number" class="form-control" id="length" name="length" value="" placeholder="Délka/Hloubka">
                        <label for="length">Délka / Hloubka</label>
                        <span class="input-group-addon">cm</span>
                    </div>

                    <div class="col-sm-3 has-metric">
                        <input type="number" class="form-control" id="width" name="width" value="" placeholder="Šířka">
                        <label for="width">Šířka</label>
                        <span class="input-group-addon">cm</span>
                    </div>

                    <div class="col-sm-3 has-metric">
                        <input type="number" class="form-control" id="height" name="height" value="" placeholder="Výška">
                        <label for="height">Výška</label>
                        <span class="input-group-addon">cm</span>
                    </div>
                </div>

            </div>

        </div>

    </div>






    <div class="col-md-6">

        <div class="panel panel-primary" data-collapsed="0">

            <div class="panel-heading">
                <div class="panel-title">
                    <strong style="font-weight: 600;">Dokumentace</strong>
                </div>

            </div>

            <div class="panel-body">



                <div class="form-group">

                    <div class="col-sm-6" style="border-right: 1px solid #ebebeb;">

                        <div class="fileinput fileinput-new" data-provides="fileinput" style="text-align: center; width: 100%;">
                            <div class="fileinput-new thumbnail" style="max-width: 80%; max-height: 300px !important;" data-trigger="fileinput">
                                <img src="/data/assets/no-image-7.jpg" alt="..." style="max-height: inherit !important;">
                            </div>
                            <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 180px; max-height: 180px"></div>
                            <div>
                                <span class="btn btn-info btn-file">
                                    <span class="fileinput-new">Vybrat obrázek</span>
                                    <span class="fileinput-exists">Změnit</span>
                                    <input type="file" name="picture" accept="image/*">
                                </span>
                                <a href="#" class="btn btn-danger fileinput-exists" data-dismiss="fileinput">Odstranit</a>
                            </div>
                        </div>

                        <hr>

                        <div class="form-group">
                            <label for="short_description" class="col-sm-3 control-label">PDF prospekt</label>
                            <div class="col-sm-9">

                                <input type="file" name="pdf">
                            </div>
                        </div>

                    </div>



                    <div class="col-sm-6">

                        <div style="margin-left: 25%; width: auto; margin-top: 44%;">
                            <input type="file" name="otherpics[]" class="form-control file2 inline btn btn-primary" multiple data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i> &nbsp;Nahrát další obrázky"/>
                        </div>

                    </div>


                </div>


            </div>

        </div>


        <div class="panel panel-primary" data-collapsed="0">

            <div class="panel-heading">
                <div class="panel-title">
                    <strong style="font-weight: 600;">Obchody</strong>
                </div>

            </div>
            <div class="panel-body">

                <?php
                // OBCHODY START

                mysqli_data_seek($shops_query, 0);
                while ($shop = mysqli_fetch_array($shops_query)) {


                    if($shop['slug'] == 'spamall'){

                        $color = '#53c9e7';

                        $color = '#0787ea';
                    }elseif($shop['slug'] == 'saunahouse'){

                        $color = '#950026';

                    }elseif($shop['slug'] == 'spahouse'){  $color = '#39a2e5';}
                    ?>


                    <style>

                        .check-<?= $shop['slug'] ?> .icheckbox_line-green.checked, .iradio_line-green.checked {
                            background: <?= $color ?> !important
                        }


                    </style>
                    <div class="col-md-4" style="padding: 0 6px;">
                        <script type="text/javascript">

                            jQuery(document).ready(function($)
                            {

                                $('#<?= $shop['slug'] ?>-box').on('ifChecked', function(event){

                                    $(this).parent('.icheckbox_line-grey').removeClass('icheckbox_line-grey').addClass('icheckbox_line-green');

                                    $('.<?= $shop['slug'] ?>').show( "slow");
                                    $(".<?= $shop['slug'] ?>_price").toggleClass('has-warning has-success');

                                });

                                $('#<?= $shop['slug'] ?>-box').on('ifUnchecked', function(event){


                                    $(this).parent('.icheckbox_line-green').removeClass('icheckbox_line-green').addClass('icheckbox_line-grey');

                                    $('.<?= $shop['slug'] ?>').hide( "slow");
                                    $(".<?= $shop['slug'] ?>_price").toggleClass('has-success has-warning');

                                });

                                $('#duplicate_<?= $shop['slug'] ?>_category').click(function() {

                                    $('#<?= $shop['slug'] ?>_category').clone(true).insertBefore("#duplicate_<?= $shop['slug'] ?>_category").attr('id', 'kappa').show();
                                    $('#kappa #jednicka').attr('name', '<?= $shop['slug'] ?>_reciever[]');

                                });

                            });
                        </script>

                        <div class="form-group check-<?= $shop['slug'] ?>">
                            <div class="col-sm-12">

                                <ul class="icheck-list">
                                    <li>
                                        <input tabindex="5" name="<?= $shop['slug'] ?>_box" value="1" type="checkbox" id="<?= $shop['slug'] ?>-box" class="icheck-16">
                                        <label for="minimal-checkbox-1-15"><?= $shop['name'] ?></label>
                                    </li>

                                </ul>

                            </div>
                        </div>


                        <div class="<?= $shop['slug'] ?>" style="display: none;">
                            <div class="form-group hide_on_variation">


                                <label for="<?= $shop['slug'] ?>_sale_price" class="col-sm-6 control-label">Zlevněná cena</label>

                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="<?= $shop['slug'] ?>_sale_price" style="float:left; width: 52.9%;" name="<?= $shop['slug'] ?>_sale_price" value="">
                                    <span class="input-group-addon" style="float:left; padding: 9px 25px 8px 9px;">Kč</span>
                                </div>


                            </div>

                            <div class="form-group">

                                <div class="col-sm-12">


                                    <?php $parent_categories_query = $mysqli->query("SELECT id, name FROM shops_categories WHERE parent_id = 0 AND shop_id = '" . $shop['id'] . "'");?>

                                    <div class="hovnus" id="<?= $shop['slug'] ?>_category" style="margin-bottom: 12px; width: 100%; float:left; display: none;">

                                        <select id="jednicka" style="width: 86%; float:left; display: block;" name="copythis" class="form-control">

                                            <option value="">Vyberte kategorii</option>

                                            <?php while ($parent_categories = mysqli_fetch_array($parent_categories_query)) { ?>
                                                <option value="<?= $parent_categories['id'] ?>"><?= $parent_categories['name'] ?></option>
                                                <?php $subparents_query = $mysqli->query("SELECT id, name FROM shops_categories WHERE parent_id = '" . $parent_categories['id'] . "' AND shop_id = '" . $shop['id'] . "'") or die($mysqli->error);
                                                while ($subparents = mysqli_fetch_array($subparents_query)) { ?>
                                                    <option value="<?= $subparents['id'] ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $subparents['name'] ?></option>
                                                    <?php
                                                    $subparents_query2 = $mysqli->query("SELECT id, name FROM shops_categories WHERE parent_id = '" . $subparents['id'] . "' AND shop_id = '" . $shop['id'] . "'") or die($mysqli->error);
                                                    while ($subparents2 = mysqli_fetch_array($subparents_query2)) { ?>
                                                        <option value="<?= $subparents2['id'] ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- <?= $subparents2['name'] ?></option>
                                                    <?php }

                                                }}?>

                                        </select>
                                        <btn class="remove_shop_category btn btn-md btn-default" style="width: 12%; float: right;">
                                            <i class=" entypo-trash" style="float:left; cursor: pointer;"></i></btn>

                                    </div>
                                    <?php mysqli_data_seek($parent_categories_query, 0);

                                    ?>
                                    <div id="kappa" class="hovnus" style="margin-bottom: 12px; width: 100%; float:left;">
                                        <select style="width: 88%; float:left; display: block;" name="<?= $shop['slug'] ?>_reciever[]" class="form-control">

                                            <option value="">Vyberte kategorii</option>

                                            <?php while ($parent_categories = mysqli_fetch_array($parent_categories_query)) { ?>
                                                <option value="<?= $parent_categories['id'] ?>"><?= $parent_categories['name'] ?></option>
                                                <?php $subparents_query = $mysqli->query("SELECT id, name FROM shops_categories WHERE parent_id = '" . $parent_categories['id'] . "' AND shop_id = '" . $shop['id'] . "'") or die($mysqli->error);
                                                while ($subparents = mysqli_fetch_array($subparents_query)) { ?>
                                                    <option value="<?= $subparents['id'] ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $subparents['name'] ?></option>
                                                    <?php
                                                    $subparents_query2 = $mysqli->query("SELECT id, name FROM shops_categories WHERE parent_id = '" . $subparents['id'] . "' AND shop_id = '" . $shop['id'] . "'") or die($mysqli->error);
                                                    while ($subparents2 = mysqli_fetch_array($subparents_query2)) { ?>
                                                        <option value="<?= $subparents2['id'] ?>">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- <?= $subparents2['name'] ?></option>
                                                    <?php }
                                                }}?>
                                        </select>
                                        <btn class="remove_shop_category btn btn-md btn-default" style="width: 12%; float: right;">
                                            <i class="entypo-trash" style="float:left; cursor: pointer;"></i></btn>
                                    </div>
                                    <button type="button" id="duplicate_<?= $shop['slug'] ?>_category" style="float: left;width: 100%;" class="btn btn-default btn-icon icon-left">
                                        Další kategorie
                                        <i class="entypo-plus"></i>
                                    </button>

                                </div>



                            </div>


                        </div>

                    </div>
                <?php }

                // OBCHODY END ?>



            </div>


        </div>

        <div class="panel panel-primary hide_on_variation" data-collapsed="0">

            <div class="panel-heading">
                <div class="panel-title">
                    <strong style="font-weight: 600;">Sklad</strong>
                </div>

            </div>
            <div class="panel-body">

                <div class="form-group form-label-group"  style="display: inline-block;width: 100%; margin: 16px 0px 6px;">

                    <?php

                    $locations_query = $mysqli->query("SELECT * FROM shops_locations ORDER BY type ASC");

                    while ($location = mysqli_fetch_array($locations_query)) {

                        ?>

                        <div class="col-lg-3 col-sm-3 has-metric">
                            <input id="instock-<?= $location['id'] ?>" type="number" class="form-control" name="location_<?= $location['id'] ?>" value="" placeholder="<?= $location['name'] ?>">
                            <label for="instock-<?= $location['id'] ?>">Σ ~ <?= $location['name'] ?></label>
                            <span class="input-group-addon">Ks</span>
                        </div>

                    <?php } ?>


                </div>

                <!--                <hr class="hr-text" data-content="Technické parametry">-->
                <hr>

                <div class="form-group form-label-group"  style="display: inline-block;width: 100%; margin: 16px 0 6px;">

                    <?php

                    mysqli_data_seek($locations_query, 0);
                    while ($location = mysqli_fetch_array($locations_query)) {

                        ?>
                        <div class="col-lg-3 col-sm-3 has-metric">
                            <input id="min_stock-<?= $location['id'] ?>" type="number" class="form-control" name="min_stock_location_<?= $location['id'] ?>" value="" placeholder="Minimální Σ <?= $location['name'] ?>">
                            <label for="min_stock-<?= $location['id'] ?>">Min. Σ ~ <?= $location['name'] ?></label>
                            <span class="input-group-addon">Ks</span>
                        </div>
                    <?php } ?>
                </div>

            </div>
        </div>

        <div class="panel panel-primary" data-collapsed="0">

            <div class="panel-heading">
                <div class="panel-title">
                    <strong style="font-weight: 600;">Související položky</strong>
                </div>

            </div>
            <div class="panel-body">

                <div class="form-group">
                    <label class="col-sm-3 control-label" for="cross_selling">Doplňkový prodej</label>

                    <div class="col-sm-9">
                        <?php $products_query = $mysqli->query("SELECT ean, productname FROM products WHERE ean != ''");

                        ?>

                        <select id="cross_selling" name="cross_selling[]" class="select2" multiple>
                            <?php while ($cross_product = mysqli_fetch_array($products_query)) { ?>
                                <option value="<?= $cross_product['ean'] ?>"><?= $cross_product['productname'] ?></option>
                            <?php } ?>
                        </select>

                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label" for="up_selling">Navyšovací prodej</label>

                    <div class="col-sm-9">
                        <?php mysqli_data_seek($products_query, 0);
                        ?>

                        <select id="up-up_selling" name="up_selling[]" class="select2" multiple>
                            <?php while ($up_product = mysqli_fetch_array($products_query)) { ?>
                                <option value="<?= $up_product['ean'] ?>"><?= $up_product['productname'] ?></option>
                            <?php } ?>
                        </select>

                    </div>
                </div>


            </div>

        </div>
    </div>

    </div>

    <center>
        <div class="form-group default-padding button-demo">
            <button type="submit" style="margin-bottom: 24px; margin-right: 26px; font-size: 20px; padding: 20px 50px 20px 140px; margin-top: 20px;" data-style="zoom-in" class="ladda-button btn btn-primary btn-icon icon-left btn-lg"><i class="entypo-plus" style="line-height: 48px;font-size: 40px; padding: 10px 20px;"></i> <span class="ladda-label">Přidat příslušenství</span></button>
        </div></center>

</form>

<footer class="main">


    &copy; <?= date("Y") ?> <span style=" float:right;"><?php
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $finish = $time;
        $total_time = round(($finish - $start), 4);

        echo 'PHP '.PHP_VERSION.' | Page generated in ' . $total_time . ' seconds.';?></span>

</footer>	</div>


</div>


<script type="text/javascript">
    jQuery(document).ready(function($)
    {
// Skins
        $('input.icheck-3').iCheck({
            checkboxClass: 'icheckbox_minimal-green',
            radioClass: 'iradio_minimal-green'
        });
        $('input.icheck-6').iCheck({
            checkboxClass: 'icheckbox_minimal-grey',
            radioClass: 'iradio_minimal-grey'
        });

        $('input.icheck-15').each(function(i, el)
        {
            var self = $(el),
                label = self.next(),
                label_text = label.text();
            label.remove();
            self.iCheck({
                checkboxClass: 'icheckbox_line-green',
                radioClass: 'iradio_line-red',
                insert: '<div class="icheck_line-icon"></div>' + label_text
            });
        });


        $('input.icheck-16').each(function(i, el)
        {
            var self = $(el),
                label = self.next(),
                label_text = label.text();
            label.remove();
            self.iCheck({
                checkboxClass: 'icheckbox_line-grey',
                radioClass: 'iradio_line-red',
                insert: '<div class="icheck_line-icon"></div>' + label_text
            });
        });
    });
</script>


<script>

    $(document).ready(function(){

        $("#product_form").on("submit", function(){
            var form = $( "#product_form" );
            var l = Ladda.create( document.querySelector( '#product_form .button-demo button' ) );
            // if(form.valid()){
            //
            //     l.start();
            // }
        });


    });


</script>


<?php include VIEW . '/default/footer.php'; ?>


