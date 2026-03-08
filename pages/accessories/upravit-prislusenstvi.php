<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
;
include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";
include_once INCLUDES . "/accessories-functions.php";

$id = $_REQUEST['id'];

$shops_query = $mysqli->query("SELECT * FROM shops ORDER BY name ASC");
$productquery = $mysqli->query('SELECT * FROM products WHERE id = "' . $id . '"') or die($mysqli->error);

if (mysqli_num_rows($productquery) > 0) {

    $product = mysqli_fetch_assoc($productquery);

    include_once CONTROLLERS . "/product-stock-controller.php";

    $oldean = $product['ean'];

    $wellnesstradequery = $mysqli->query('SELECT * FROM products_sites WHERE product_id="' . $id . '" AND site = "wellnesstrade"') or die($mysqli->error);
    $wellnesstrade = mysqli_fetch_assoc($wellnesstradequery);

    function truevalue($number)
    {

        if ($number == 0) {

            return '';

        } else {

            return $number;
        }

    }

    $purchase_price = truevalue($product['purchase_price']);
    $wholesale_price = truevalue($product['wholesale_price']);
    $price = truevalue($product['price']);

    $weight = truevalue($product['weight']);
    $length = truevalue($product['length']);
    $width = truevalue($product['width']);
    $height = truevalue($product['height']);

    $spesl = " - " . $product['productname'];
    $pagetitle = "Upravit příslušenství";

    $bread1 = "Editace příslušenství";
    $abread1 = "editace-prislusenstvi";


    // edit request
    if (isset($_REQUEST['action']) && $_REQUEST['action'] == "edit") {

        $webhookSpecial = array();
        $webhookSpecial['imageChange'] = 0;
        $webhookSpecial['variationImages'] = 0;

        if ($product['type'] == 'simple') {

            // update skladu simple produktu
            productWarehouseStock($product['id'], 0, '');

        } elseif ($product['type'] == 'variable') {

            $instock = 0;
            $main_warehouse = 0;

        }

        $seoslug = odkazy($_POST['productname']);

        // rename all images because of different product name
        if ($seoslug != $product['seourl']) {

            foreach($productImageSizes as $imageSize){

                $result = glob(PRODUCT_IMAGE_PATH.'/'.$imageSize.'/' . $product['seourl'] . '*.jpg');
                foreach ($result as $res) {

                    preg_match('/'.$product['seourl'].'(.*)/', $res, $output);
                    rename($res, PRODUCT_IMAGE_PATH.'/'.$imageSize.'/'.$seoslug.$output[1]);

                }
            }
        }

        $product_name = $mysqli->real_escape_string($_POST['productname']);

        $height = str_replace(',', '.', $_POST['height']);
        $weight = str_replace(',', '.', $_POST['weight']);
        $width = str_replace(',', '.', $_POST['width']);
        $length = str_replace(',', '.', $_POST['length']);

        if(isset($_POST['cross_selling'])){ $cross_selling = serialize($_POST['cross_selling']); }else{ $cross_selling = ''; }
		if(isset($_POST['up_selling'])){ $up_selling = serialize($_POST['up_selling']); }else{ $up_selling = ''; }
		if(isset($_POST['up_selling'])){ $spare_part = $_POST['spare_part']; }else{ $spare_part = ''; }


        $ean_main = preg_replace('/\s+/', '', $_POST['ean']);
        $code = preg_replace('/\s+/', '', $_POST['code']);

        // update specifications
        $mysqli->query("DELETE FROM products_specifications WHERE product_id = '" . $product['id'] . "'");

        // specifications
        productSpecs($id);

        $mysqli->query("UPDATE products 
            SET 
                spare_part = '" . $spare_part . "', 
                manufacturer = '" . $_POST['manufacturer'] . "', 
                delivery_time = '" . $_POST['delivery_time'] . "', 
                ean = '$ean_main', 
                cross_selling = '$cross_selling', 
                up_selling = '$up_selling', 
                height = '$height', 
                weight = '$weight', 
                width = '$width', 
                length = '$length', 
                productname = '$product_name', 
                short_description = '" . $_POST['short_description'] . "', 
                description = '" . $_POST['description'] . "', 
                internal_note = '" . $_POST['internal_note'] . "', 
                seourl = '$seoslug', 
                purchase_price = '" . $_POST['purchaseprice'] . "', 
                wholesale_price = '" . $_POST['wholesaleprice'] . "', 
                price = '" . $_POST['price'] . "', 
                code = '$code', 
                availability = '" . $_POST['availability'] . "' 
            WHERE id = '$id'") or die($mysqli->error);


        // suppliers
        $mysqli->query("DELETE FROM products_suppliers WHERE product_id = '".$id."'")or die($mysqli->error);

        // todo move into product table
        if(!empty($_POST['supplier_first'])){

            $mysqli->query("INSERT IGNORE INTO products_suppliers (product_id, supplier) VALUES ('".$id."', '".$_POST['supplier_first']."')")or die($mysqli->error);

        }

        if(!empty($_POST['supplier_second'])){

            $mysqli->query("INSERT IGNORE INTO products_suppliers (product_id, supplier) VALUES ('".$id."', '".$_POST['supplier_second']."')")or die($mysqli->error);

        }


        // check product all images
        $webhookSpecial['imageChange'] = productImages($seoslug);



        // variations
        $ids = array();
        $eans = array();
        $find_variations = $mysqli->query("SELECT id, ean FROM products_variations WHERE product_id = '" . $product['id'] . "'");

        while ($variations = mysqli_fetch_array($find_variations)) {
            array_push($eans, $variations['ean']);
            array_push($ids, $variations['id']);
        }


        $old_ids = array();
        $new_ids = array();

        if ($product['type'] == 'variable' && isset($_POST['item'])) {

            $to_stockerino = 0;
            $to_reserverino = 0;

            $post = array_filter($_POST['item']);

            foreach ($post as $post_index => $variation) {


                // SPOLEČNÁ DATA PRO PŘIDÁNÍ I UPDATE

                $wholesale_price = preg_replace('/\s+/', '', $variation['variation_wholesale_price']);
                $purchase_price = preg_replace('/\s+/', '', $variation['variation_purchase_price']);
                $price = preg_replace('/\s+/', '', $variation['variation_price']);

                $sale_price = preg_replace('/\s+/', '', $variation['variation_sale_price']);

                $ean = preg_replace('/\s+/', '', $variation['variation_ean']);
                $sku = preg_replace('/\s+/', '', $variation['variation_sku']);

                /* UPDATE VARIANT */
                if (!empty($variation['variation_id']) && isset($variation['variation_id'])) {

                    array_push($old_ids, $variation['variation_id']);
                    $variation_id = $variation['variation_id'];

                    $mysqli->query("UPDATE products_variations 
                        SET price = '" . $price . "', 
                            purchase_price = '$purchase_price', 
                            wholesale_price = '$wholesale_price', 
                            id = '" . $variation['variation_id'] . "', 
                            weight = '" . $variation['variation_weight'] . "', 
                            length = '" . $variation['variation_length'] . "', 
                            width = '" . $variation['variation_width'] . "', 
                            height = '" . $variation['variation_height'] . "', 
                            product_id = '" . $product['id'] . "', 
                            sale_price = '$sale_price', 
                            description = '" . $variation['variation_description'] . "', 
                            availability = '" . $variation['availability'] . "', 
                            ean = '$ean', 
                            sku = '$sku' 
                        WHERE id = '" . $variation['variation_id'] . "'") or die($mysqli->error);

                /* PŘIDÁNÍ VARIANT */
                } else {

                    $mysqli->query("INSERT INTO products_variations 
                        (
                         price, 
                         purchase_price, 
                         wholesale_price, 
                         weight, 
                         length, 
                         width, 
                         height, 
                         product_id, 
                         sale_price, 
                         description, 
                         ean, 
                         sku
                         )
			  	    VALUES (
                        '$price', 
                        '$purchase_price', 
                        '$wholesale_price', 
                        '" . $variation['variation_weight'] . "', 
                        '" . $variation['variation_length'] . "', 
                        '" . $variation['variation_width'] . "', 
                        '" . $variation['variation_height'] . "', 
                        '" . $product['id'] . "',
                        '$sale_price',
                        '" . $variation['variation_description'] . "',
                        '$ean',
                        '$sku'
                    )") or die($mysqli->error);

                    $variation_id = $mysqli->insert_id;

                    array_push($new_ids, $variation_id);

                }

                // update variation stock for all warehouses
                productWarehouseStock($id, $variation_id, $variation);

                // variation image (delete + upload)
                $imageResult = variationImage($variation_id, $post_index, $seoslug);

                if($imageResult == 1){
                    $webhookSpecial['variationImages'] = 1;
                }

                // update variation values
                updateVariationValues($variation_id, $variation);

                // shopy varianty
                updateShopProduct('products_variations_sites', $id, $variation_id);


            }
        }

        // webhook images
        $specialEncoded = json_encode($webhookSpecial);

        // update all shops and set product update on those shops (create, update, delete)
        updateShopProduct('products_sites', $id, 0, $specialEncoded);

        /* ODSTRANĚNÍ VARIANT */
        // todo nejdřív se musí smazat z eshopu, až potom odstranit. zatím je skrýt? nebo? nastavit product_id = 0?
        $delete_vari = array_diff((array)$ids, (array)$old_ids);
        foreach ($delete_vari as $delete_id) {

            $mysqli->query("DELETE FROM products_variations WHERE id = '$delete_id'") or die($mysqli->error);
            $mysqli->query("DELETE FROM products_stocks WHERE variation_id = '$delete_id'") or die($mysqli->error);
            $mysqli->query("DELETE FROM products_variations_sites WHERE variation_id = '$delete_id'") or die($mysqli->error);
            $mysqli->query("DELETE FROM products_variations_values WHERE variation_id = '$delete_id'") or die($mysqli->error);

            foreach($productImageSizes as $imageSize){
                $path = PRODUCT_IMAGE_PATH.'/'.$imageSize.'/'.$seoslug.'_variation_'.$delete_id.'.jpg';
                if(file_exists($path)){  rename($path, PRODUCT_IMAGE_PATH.'/'.$imageSize.'/'.$seoslug.'_variation_'.$delete_id.'.jpg'); };
                unset($path);
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

        Header("Location:https://www.wellnesstrade.cz/admin/pages/accessories/zobrazit-prislusenstvi?id=" . $id);
        exit;

    }


    if (isset($_REQUEST['action']) && $_REQUEST['action'] == "remove_pdf") {

        unlink($_SERVER['DOCUMENT_ROOT'] . "/data/stores/" . $product['pdf']);

        $mysqli->query("UPDATE products SET pdf = '' WHERE id = '" . $id . "'") or die($mysqli->error);

        Header("Location:https://www.wellnesstrade.cz/admin/pages/accessories/upravit-prislusenstvi?id=" . $product['id']);
        exit;
    }

    if (isset($_REQUEST['action']) && $_REQUEST['action'] == "remove_other_files") {
        // todo lepší nahrávání dalších obrázků - bez mazání

        // added
        $images = glob($_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/small/' . $product['seourl'] . '_{,[1-9]}[0-9].jpg', GLOB_BRACE);
        if (!empty($images)) {

            foreach ($images as $image) {

                $imageName = basename($image);

                foreach($productImageSizes as $imageSize){

                    $path = PRODUCT_IMAGE_PATH.'/'.$imageSize.'/'.$imageName;
                    if(file_exists($path)){ unlink($path); };
                    unset($path);

                }

            }

        }
        // added

        Header("Location:https://www.wellnesstrade.cz/admin/pages/accessories/upravit-prislusenstvi?id=" . $product['id']);
        exit;
    }

    $saunyquery = $mysqli->query("SELECT * FROM warehouse_products WHERE customer = 0 ORDER BY code");

    $virivkyquery = $mysqli->query("SELECT * FROM warehouse_products WHERE customer = 1 ORDER BY brand");


    include VIEW . '/default/header.php';

    ?>

<?php if (isset($product['ean']) && $product['ean'] == "") {
        ?>

<script type="text/javascript">



jQuery(document).ready(function($)
{

    myform.ean.value = randomPassword(myform.lengthee.value);

});

</script>
<?php
    }
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


        $("form .ean").each(function () {

            var $this = $(this);

            var id = '<?= $product['id'] ?>';
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

                // todo nahradit za php array -> javascript array a kontrolovat přes obsah (countInArray)
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

            var id = '<?= $product['id'] ?>';
            var check = $(this).val();

            if(countInArray(skuArray, check) > 1){

                $($this).closest('.form-group').addClass('has-error');
                toastr.error('V této kartě má více položek stejný SKU kód.');

                pass = false;
                return false;

            }else if(check == ''){

                $(this).closest('.form-group').addClass('has-error');
                toastr.error('Není vyplněný SKU kód')
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
                    toastr.error('Duplicitní SKU kód. - jako u <a href="./zobrazit-prislusenstvi?id=' + result.duplicate_id + '" target="_blank">produktu ID ' + result.duplicate_id + ' → zobrazit</a>')

                    pass = false;
                    return false;

                } else {

                    $($this).closest('.form-group').removeClass('has-error');

                }
                };



            });



        // productname
        $("#productname").each(function () {

            var $this = $(this);

            var id = '<?= $product['id'] ?>';
            var check = $(this).val();


            if(check == ''){

                $(this).closest('.form-group').addClass('has-error');
                toastr.error('Není vyplněný název položky.')

                pass = false;
                return false;

            }else{

                var result = $.ajax({
                    type: 'GET',
                    url: "../../controllers/stores/unique_name",
                    async: false,
                    dataType: 'json',
                    data: { id: id, check: check, type: 'productname' },
                    done: function(results) {

                        alert(results);
                        JSON.parse(results);
                        return results;

                    },
                    fail: function( jqXHR, textStatus, errorThrown ) {
                        console.log( 'Could not get posts, server response: ' + textStatus + ': ' + errorThrown );
                    }
                }).responseJSON; // <-- this instead of .responseText

                if (result.state == 'failure') {

                    $($this).closest('.form-group').addClass('has-error');
                    toastr.error('Duplicitní název položky.')

                    pass = false;
                    return false;

                } else {

                    $($this).closest('.form-group').removeClass('has-error');

                }

            }

        });

        // alert(pass);

        if(!pass){

            return false;

        }

    });




function duplicate_variation(i) {

    console.log(i);

    $('#variation_copy').clone(true).insertBefore("#duplicate_variation").attr('id', 'copied').show();

    $('#copied').find('input').each(function() {

        let id = $(this).attr('id');

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

    <style>
        .form-label-group { margin-top: 16px !important; margin-bottom: 6px !important; padding-left: 0 !important; padding-right: 10px !important}

        hr{
            float: left;width: 100%;margin: 8px 0;
        }

    </style>

<form role="form" id="product_form" method="post" name="myform" class="form-horizontal form-groups-bordered validate" action="upravit-prislusenstvi?action=edit&id=<?= $id ?>" enctype="multipart/form-data" autocomplete="off">

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
                                    <input type="text" class="form-control" id="productname" name="productname" value="<?= $product['productname'] ?>" placeholder="Název">
                                    <label for="productname">Název</label>
                                </div>
                            </div>


                            <div class="form-group form-label-group" style="padding-left: 15px !important">
                                <div class="col-lg-12 col-sm-12">
                                    <textarea class="form-control" name="short_description" id="short_description" rows="2" placeholder="Krátký popis"><?= $product['short_description'] ?></textarea>
                                </div>
                            </div>


         			 <div class="form-group">

						<div class="col-sm-12">
							<textarea id="summernote" class="form-control summernote" rows="18" name="description" id="contents"><?= $product['description'] ?></textarea>
						</div>
					</div>
         			 <div class="form-group">

						<div class="col-sm-12">
                            <label for="internal_note">Interní poznámka</label>
							<textarea class="form-control" rows="3" name="internal_note" id="internal_note" placeholder="Interní poznámka"><?= $product['internal_note'] ?></textarea>
						</div>
					</div>


<!--                            <hr class="hr-text" data-content="Dostupnost">-->
                            <hr>

                            <style>

                                .radio-new {
                                    float: left;  border: 1px solid #ebebeb; border-radius: 4px; padding: 12px 30px 6px 25px; margin-left: 10px; text-align: center; cursor: pointer; color: #666;
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
									<input id="instock" type="radio" name="availability" value="0" <?php if (isset($product['availability']) && $product['availability'] == 0) {echo 'checked';}?>>
                                <label for="instock" >Skladem</label>
							</label>
							<label for="on-order" class="radio-new">
									<input id="on-order" type="radio" name="availability" value="2" <?php if (isset($product['availability']) && $product['availability'] == 2) {echo 'checked';}?>>
                            <label for="on-order">Na objednávku</label>
							</label>
                                <label for="hidden" class="radio-new">
									<input id="hidden" type="radio" name="availability" value="3" <?php if (isset($product['availability']) && $product['availability'] == 3) {echo 'checked';}?>>
                                    <label for="hidden">Skryto</label>
							</label>
                                <label for="unavailable" class="radio-new">
                                    <input id="unavailable" type="radio" name="availability" value="4" <?php if (isset($product['availability']) && $product['availability'] == 4) {echo 'checked';}?>>
                                    <label for="unavailable">Nedostupné</label>

                                </label>
						</div>

                        <div class="form-check" style="margin: 0; float: right;">
                            <label for="spare_part" class="checkbox checkbox-success">
                                <input type="checkbox" id="spare_part" name="spare_part" value="1" <?php if (isset($product['spare_part']) && $product['spare_part'] == 1) {echo 'checked';}?>>
                                <label for="spare_part">
                                    Náhradní díl
                                </label>
                            </label>
                        </div>
                        </div>

					</div>

<!--                            <hr class="hr-text" data-content="Ceny">-->
                            <hr>

                        <div class="form-group form-label-group hide_on_variation" style="margin-left: 0 !important; margin-bottom: 6px !important; float: left; width: 100%; <?php if (isset($product['type']) && $product['type'] == 'variable') { ?>display: none;<?php } ?>">
                            <div class="col-lg-3 col-md-4 col-sm-4 has-metric">
                                <input type="number" class="form-control" id="price" name="price" value="<?= $product['price'] ?>" placeholder="Cena">
                                <label for="price">Cena</label>
                                <span class="input-group-addon">Kč</span>
                            </div>

                        <div class="col-lg-3 col-md-4 col-sm-4 has-metric hide_on_variation" <?php if (isset($product['type']) && $product['type'] == 'variable') { ?>style="display: none;"<?php } ?>>
                            <input type="number" class="form-control" id="purchaseprice" name="purchaseprice" value="<?= $purchase_price ?>" placeholder="Nákupní cena">
                            <label for="purchaseprice">Nákupní cena</label>
                            <span class="input-group-addon">Kč</span>
                        </div>

                        <div class="col-lg-4 col-md-4 col-sm-4 has-metric hide_on_variation" <?php if (isset($product['type']) && $product['type'] == 'variable') { ?>style="display: none;"<?php } ?>>
                            <input type="number" class="form-control" id="wholesaleprice" name="wholesaleprice" value="<?= $wholesale_price ?>" placeholder="Velkoobchodní cena">
                            <label for="wholesaleprice">Velkoobchodní cena</label>
                            <span class="input-group-addon">Kč</span>
                        </div>

                            <hr>

                        </div>

<!--                        <hr class="hr-text" data-content="Značení položky">-->

                    <div class="form-group col-sm-12" style="float: left;">
                        <div class="form-group form-label-group col-sm-6 has-button" style="padding-left: 0 !important; padding-right: 30px !important;">
                            <input type="text" class="form-control sku" name="code" value="<?= $product['code'] ?>" placeholder="Kód SKU *" style="width: calc(100% - 140px); float: left;">
                            <label for="code">Kód SKU*</label>

                            <input type="button" class="btn btn-default" value="Vygenerovat" onClick="generate();" tabindex="2" style="width: 140px;">
<!--                           <div class="invalid-feedback">-->
<!--                                Please choose a username.-->
<!--                            </div>-->
                        </div>
                        <div class="form-group form-label-group col-sm-6" style="padding-left: 0 !important;  padding-right: 30px !important;"">
                            <input type="text" class="form-control ean" name="ean" value="<?= $product['ean'] ?>" placeholder="EAN kód">
                            <label for="ean">EAN kód*</label>
                        </div>


                            </div>


<!--                    <hr class="hr-text" data-content="Informace o dodavateli">-->
<hr>
                <div style="display: inline-block; width: 100%; margin-top: 14px;">
                            <div class="form-group form-label-group" style="float: left; width: 50%; margin: 0; padding: 0 !important;">

                                <div class="col-sm-6">

                                <?php $manufactures_query = $mysqli->query("SELECT * FROM products_manufacturers WHERE type = 'manufacturer' ORDER BY manufacturer") or die($mysqli->error);?>

                                <label for="manufacturer" class="col-sm-3 control-label" style="margin-top: -40px;">Výrobce</label>


                                <div class="col-sm-12" style="padding-left: 2px;">
                                    <select name="manufacturer" class="form-control">

                                        <option value="">Vyberte výrobce</option>

                                        <?php while ($manufacturer = mysqli_fetch_array($manufactures_query)) { ?>
                                            <option value="<?= $manufacturer['id'] ?>" <?php if ($product['manufacturer'] == $manufacturer['id']) {echo 'selected';}?>><?= $manufacturer['manufacturer'] ?></option>
                                        <?php } ?>

                                    </select>
                                </div>

                                </div>

                                <div class="col-sm-6">

                                        <div class="col-sm-12 has-metric" style="padding: 0;">
                                            <input type="number" class="form-control" id="delivery_time" name="delivery_time" value="<?= $product['delivery_time'] ?>" placeholder="Speciální čas doručení">
                                            <label for="delivery_time">Čas doručení</label>
                                            <span class="input-group-addon">dnů</span>
                                        </div>

                                        <small> 0 = čas dle výrobce nebo 14 dnů </small>

                                    </div>

					        </div>


                            <?php

                            $supplier_first = $mysqli->query("SELECT * FROM products_suppliers 
                                WHERE product_id = '".$product['id']."'") or die($mysqli->error);

                            while($supplier = mysqli_fetch_assoc($supplier_first)){

                                $d[] = $supplier['supplier'];
                            }

                            $supplier_query = $mysqli->query("SELECT * FROM products_manufacturers 
                                WHERE type = 'supplier' ORDER BY manufacturer") or die($mysqli->error);

                            ?>


                            <div class="form-group form-label-group" style="float: right; width: 50%; margin: 0; padding: 0 !important;">

                                <div class="col-sm-6" style="padding: 0;">

                                    <label for="supplier_first" class="col-sm-3 control-label" style="margin-top: -40px;">Dodavatel #1</label>

                                    <div class="col-sm-12" style="padding-left: 2px;">
                                        <select name="supplier_first" class="form-control">

                                            <option value="">Vyberte dodavetele</option>

                                            <?php while($supplier = mysqli_fetch_assoc($supplier_query)) {

                                                ?>
                                                <option value="<?= $supplier['id'] ?>" <?php if (!empty($d[0]) && $d[0] == $supplier['id']) {echo 'selected';}?>><?= $supplier['manufacturer'] ?></option>
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

					<?php $specs_query = $mysqli->query('SELECT name, value FROM products_specifications WHERE product_id="' . $product['id'] . '"') or die($mysqli->error);

    if (mysqli_num_rows($specs_query) > 0) {
        while ($spec = mysqli_fetch_array($specs_query)) {
            ?>
						    <div class="specification" style="float: left; width: 100%;">
								<div class="col-sm-4" style="margin-bottom: 8px; padding: 0;">
								<input type="text" class="form-control" id="specification_name" name="specification_name[]" value="<?= $spec['name'] ?>" placeholder="Název">
								</div>
								<div class="col-sm-7" style="padding: 0 0px 0 8px;">
								<input type="text" class="form-control" id="specification_value" name="specification_value[]" value="<?= $spec['value'] ?>" placeholder="Hodnota">
								</div>
								 <i class="remove_specification entypo-trash" style="float:left; margin-top: 8px; margin-left: 10px; cursor: pointer;"></i>
							</div>
						<?php }} else { ?>
							<div class="specification" style="float: left; width: 100%;">
								<div class="col-sm-4" style="margin-bottom: 8px; padding: 0;">
								<input type="text" class="form-control" id="specification_name" name="specification_name[]" value="" placeholder="Název">
								</div>
								<div class="col-sm-7" style="padding: 0 0px 0 8px;">
								<input type="text" class="form-control" id="specification_value" name="specification_value[]" value="" placeholder="Hodnota">
								</div>
								 <i class="remove_specification entypo-trash" style="float:left; margin-top: 8px; margin-left: 10px; cursor: pointer;"></i>
							</div>
						<?php } ?>


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
                            <input type="number" class="form-control" id="weight" name="weight" value="<?= $weight ?>" placeholder="Váha">
                            <label for="weight">Váha</label>
                            <span class="input-group-addon">Kg</span>
                        </div>

                        <div class="col-sm-3 has-metric">
                            <input type="number" class="form-control" id="length" name="length" value="<?= $length ?>" placeholder="Délka/Hloubka">
                            <label for="length">Délka / Hloubka</label>
                            <span class="input-group-addon">cm</span>
                        </div>

                        <div class="col-sm-3 has-metric">
                            <input type="number" class="form-control" id="width" name="width" value="<?= $width ?>" placeholder="Šířka">
                            <label for="width">Šířka</label>
                            <span class="input-group-addon">cm</span>
                        </div>

                        <div class="col-sm-3 has-metric">
                            <input type="number" class="form-control" id="height" name="height" value="<?= $height ?>" placeholder="Výška">
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


							<?php

                            $path = PRODUCT_IMAGE_PATH . '/thumbnail/' . $product['seourl'] . '.jpg';
                            if (file_exists($path)) {

                                $imagePath = '/data/stores/images/thumbnail/' . $product['seourl'] . '.jpg';

                                ?>
							 <div class="fileinput fileinput-exists" data-provides="fileinput" data-name="picture" style="text-align: center; width: 100%;">
								<div class="fileinput-new thumbnail" style="max-width: 80%; max-height: 300px !important;" data-trigger="fileinput">
									<img src="/data/assets/no-image-7.jpg" alt="..."  style="max-height: inherit !important;">
								</div>
								<div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 80%; max-height: 300px !important;">
									  <img src="<?= $imagePath ?>" style="max-height: inherit !important;">
								</div>
								<div>
									<span class="btn btn-info btn-file" style=" margin-top: 5px; margin-right: 2px;">
										<span class="fileinput-new">Vybrat obrázek</span>
										<span class="fileinput-exists">Změnit</span>
										<input type="file" accept="image/*">
									</span>
									<a href="#" class="btn btn-danger fileinput-exists" data-dismiss="fileinput" style=" margin-top: 5px;">Odstranit</a>
								</div>
							</div>

							<?php } else { ?>

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

							<?php } ?>


                            <hr>

                            <div class="form-group">
                                <label for="short_description" class="col-sm-3 control-label">PDF prospekt</label>
                                <div class="col-sm-9">

                                    <?php if ($product['pdf'] != '') { ?>
                                        <a href="https://www.wellnesstrade.cz/data/stores/<?= $product['pdf'] ?>" class="btn btn-primary btn-sm btn-icon icon-left" target="_blank">
                                            <i class="entypo-search"></i>
                                            Zobrazit
                                        </a>
                                        <a href="upravit-prislusenstvi?id=<?= $product['id'] ?>&action=remove_pdf" class="btn btn-danger btn-sm btn-icon icon-left" >
                                            <i class="entypo-cancel"></i>
                                            Smazat
                                        </a>
                                    <?php } else { ?>
                                        <input type="file" name="pdf">
                                    <?php } ?>
                                </div>
                            </div>

						</div>



						<div class="col-sm-6">

							<?php

    $files = glob($_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/thumbnail/' . $product['seourl'] . '_{,[1-9]}[0-9].jpg', GLOB_BRACE);

    if (!empty($files)) {

        foreach ($files as $file) {

                $imagePath = '/data/stores/images/thumbnail/' . basename($file);
                echo '<img src="'.$imagePath.'" width="90" style="float: left; margin-right:10px; margin-bottom: 8px;">';

        }?>
            <a href="upravit-prislusenstvi?id=<?= $product['id'] ?>&action=remove_other_files" class="btn btn-danger btn-icon icon-left" style=" margin-left: 25%; margin-top: 5px;">
                <i class="entypo-cancel"></i>
                Smazat obrázky
            </a>

							<?php } else { ?>
                        <div style="margin-left: 25%; width: auto; margin-top: 44%;">
							<input type="file" name="otherpics[]" class="form-control file2 inline btn btn-primary" multiple data-label="<i class='glyphicon glyphicon-circle-arrow-up'></i> &nbsp;Nahrát další obrázky"/>
                        </div>

							<?php } ?>

						</div>


					</div>


					</div>

					</div>


					<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						<strong style="font-weight: 600;">ObchodyObchody</strong>
					</div>

				</div>
				<div class="panel-body">

<?php
    // OBCHODY START

    mysqli_data_seek($shops_query, 0);
    while ($shop = mysqli_fetch_array($shops_query)) {

        $this_shop = "";

        $this_shop_query = $mysqli->query('SELECT * FROM products_sites WHERE product_id = "' . $id . '" AND site = "' . $shop['slug'] . '"') or die($mysqli->error);
        $this_shop = mysqli_fetch_assoc($this_shop_query);

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
						        <input tabindex="5" name="<?= $shop['slug'] ?>_box" value="1" type="checkbox" id="<?= $shop['slug'] ?>-box" <?php if (!empty($this_shop)) {echo 'checked class="icheck-15"';} else {echo 'class="icheck-16"';}?>>
						        <label for="minimal-checkbox-1-15"><?= $shop['name'] ?></label>
						    </li>

						</ul>

					</div>
				</div>


				<div class="<?= $shop['slug'] ?>" <?php if (empty($this_shop)) { ?>style="display: none;"<?php } ?>>
	  	<div class="form-group hide_on_variation" <?php if (isset($product['type']) && $product['type'] == 'variable') { ?>style="display: none;"<?php } ?>>


						<label for="<?= $shop['slug'] ?>_sale_price" class="col-sm-6 control-label">Zlevněná cena</label>

						<div class="col-sm-6">
							<input type="text" class="form-control" id="<?= $shop['slug'] ?>_sale_price" style="float:left; width: 52.9%;" name="<?= $shop['slug'] ?>_sale_price" value="<?php if(!empty($this_shop)){ echo $this_shop['sale_price']; } ?>">
							<span class="input-group-addon" style="float:left; padding: 9px 25px 8px 9px;">Kč</span>
						</div>


					</div>

          	   <div class="form-group">

          	   	<div class="col-sm-12">


 		<?php
        $parent_categories_query = $mysqli->query("SELECT id, name FROM shops_categories WHERE parent_id = 0 AND shop_id = '" . $shop['id'] . "'");?>

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

        $this_site_category_query = "";
        $this_site_category_query = $mysqli->query("SELECT * FROM products_sites_categories WHERE product_id = '$id' AND site = '" . $shop['slug'] . "'");
        if (mysqli_num_rows($this_site_category_query) > 0) {
            while ($this_category = mysqli_fetch_array($this_site_category_query)) {

                ?>
             <div id="kappa" class="hovnus" style="margin-bottom: 12px; width: 100%; float:left;">
              	<select style="width: 86%; float:left; display: block;" name="<?= $shop['slug'] ?>_reciever[]" class="form-control" autocomplete="off">

								<option value="">Vyberte kategorii</option>


								<?php while ($parent_categories = mysqli_fetch_array($parent_categories_query)) { ?>
								<option value="<?= $parent_categories['id'] ?>" <?php if (isset($this_category['category']) && $this_category['category'] == $parent_categories['id']) {echo 'selected';}?>><?= $parent_categories['name'] ?></option>
									<?php $subparents_query = $mysqli->query("SELECT id, name FROM shops_categories WHERE parent_id = '" . $parent_categories['id'] . "' AND shop_id = '" . $shop['id'] . "'") or die($mysqli->error);
                    while ($subparents = mysqli_fetch_array($subparents_query)) { ?>
 										<option value="<?= $subparents['id'] ?>" <?php if (isset($this_category['category']) && $this_category['category'] == $subparents['id']) {echo 'selected';}?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?= $subparents['name'] ?></option>
								<?php
                        $subparents_query2 = $mysqli->query("SELECT id, name FROM shops_categories WHERE parent_id = '" . $subparents['id'] . "' AND shop_id = '" . $shop['id'] . "'") or die($mysqli->error);
                        while ($subparents2 = mysqli_fetch_array($subparents_query2)) { ?>
 										<option value="<?= $subparents2['id'] ?>" <?php if (isset($this_category['category']) && $this_category['category'] == $subparents2['id']) {echo 'selected';}?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-- <?= $subparents2['name'] ?></option>
								<?php }

                    }}?>


							</select>
                 <btn class="remove_shop_category btn btn-md btn-default" style="width: 12%; float: right;">
                     <i class="entypo-trash" style="float:left; cursor: pointer;"></i></btn>
            </div>


            <?php
                mysqli_data_seek($parent_categories_query, 0);
            }
        } else { ?>
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
            <?php } ?>
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

            <div class="panel panel-primary hide_on_variation" data-collapsed="0" <?php if (isset($product['type']) && $product['type'] == 'variable') { ?>style="display: none;"<?php } ?>>

                <div class="panel-heading">
                    <div class="panel-title">
                        <strong style="font-weight: 600;">Sklad</strong>
                    </div>

                </div>
                <div class="panel-body">

            <div class="form-group form-label-group"  style="display: inline-block;width: 100%; margin: 16px 0 6px;">

                <?php

                $locations_query = $mysqli->query("SELECT * FROM shops_locations l LEFT JOIN products_stocks s ON s.location_id = l.id AND s.product_id = '" . $product['id'] . "' ORDER BY type ASC");

                while ($location = mysqli_fetch_array($locations_query)) {

                    ?>

                    <div class="col-lg-3 col-sm-3 has-metric">
                        <input id="instock-<?= $location['id'] ?>" type="number" class="form-control" name="location_<?= $location['id'] ?>" value="<?= $location['instock'] ?>" placeholder="<?= $location['name'] ?>">
                        <label for="instock-<?= $location['id'] ?>">Σ ~ <?= $location['name'] ?></label>
                        <span class="input-group-addon">Ks</span>
                    </div>

                <?php } ?>


            </div>

            <!--                <hr class="hr-text" data-content="Technické parametry">-->
            <hr>

            <div class="form-group form-label-group"  style="display: inline-block;width: 100%; margin: 16px 0px 6px;">

                <?php

                mysqli_data_seek($locations_query, 0);

                while ($location = mysqli_fetch_array($locations_query)) {


                    ?>
                    <div class="col-lg-3 col-sm-3 has-metric">
                        <input id="min_stock-<?= $location['id'] ?>" type="number" class="form-control" name="min_stock_location_<?= $location['id'] ?>" value="<?= $location['min_stock'] ?>" placeholder="Minimální Σ <?= $location['name'] ?>">
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
    $cross_selling = unserialize($product['cross_selling']);
    ?>

							<select id="cross_selling" name="cross_selling[]" class="select2" multiple>
								<?php while ($cross_product = mysqli_fetch_array($products_query)) { ?>
								<option value="<?= $cross_product['ean'] ?>" <?php if (isset($cross_selling) && $cross_selling != "") {if (in_array($cross_product['ean'], $cross_selling)) {echo 'selected';}}?>><?= $cross_product['productname'] ?></option>
								<?php } ?>
							</select>

						</div>
					</div>

					<div class="form-group">
						<label class="col-sm-3 control-label" for="up_selling">Navyšovací prodej</label>

						<div class="col-sm-9">
							<?php mysqli_data_seek($products_query, 0);
    $up_selling = unserialize($product['up_selling']);
    ?>

							<select id="up-up_selling" name="up_selling[]" class="select2" multiple>
								<?php while ($up_product = mysqli_fetch_array($products_query)) { ?>
								<option value="<?= $up_product['ean'] ?>" <?php if (isset($up_selling) && $up_selling != "") {if (in_array($up_product['ean'], $up_selling)) {echo 'selected';}}?> ><?= $up_product['productname'] ?></option>
								<?php } ?>
							</select>

						</div>
					</div>


				</div>

			</div>
			</div>


    </div>

    <div class="row">
        <div class="col-md-12">

            <!-- Variation Start -->
            <div class="panel panel-primary" data-collapsed="0">
                <div class="panel-heading">
                    <div class="panel-title">
                        <strong style="font-weight: 600;">Druh produktu</strong>
                    </div>
                </div>


                <div class="panel-body">
                    <div class="form-group">
                        <div class="col-sm-12">
                            <div class="alert alert-info">
                                <strong><?= ($product['type'] === 'simple') ? 'Jednoduchý produkt' : 'Produkt s variantami' ?></strong>
                                - momentálně není umožněno změnit typ produktu
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                
    <?php if($product['type'] == 'variable') { ?>
    

            <div class="form-group variants" <?php if (isset($product['type']) && $product['type'] == 'simple') { ?>style="display: none;"<?php } ?>>


                <div class="col-sm-12">


                    <div id="vari_speci" style="display: none;">
                        <div class="col-sm-6" style="margin-bottom: 8px; padding: 0;">
                            <input type="text" class="form-control" id="copy_this_first_vari_speci" name="copythis" value="" placeholder="Název specifikace*">
                        </div>
                        <div class="col-sm-5" style="margin-bottom: 8px; padding: 0 0px 0 8px;">
                            <input type="text" class="form-control" id="copy_this_second_vari_speci" name="copythis" value="" placeholder="Hodnota varianty*">
                        </div>
                        <div class="col-sm-1" style="padding: 0;">
                            <i class="remove_specifi_vari entypo-trash" style="float: left; padding: 7px 8px; cursor: pointer;"></i>
                        </div>
                    </div>


                    <div id="variation_copy_image" class="fileinput fileinput-new col-sm-3" data-provides="fileinput" style=" padding: 0; text-align: center; display: none;">
                        <div class="fileinput-new thumbnail" style="width: 80px; height: 80px;" data-trigger="fileinput">
                            <img src="https://www.wellnesstrade.cz/data/assets/no-image-7.jpg" width="80" alt="...">
                        </div>
                        <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 180px; max-height: 180px"></div>
                        <div>
									<span class="btn btn-white btn-file" style="padding: 4px 8px; font-size: 11px;">
										<span class="fileinput-new">Vybrat obrázek</span>
										<span class="fileinput-exists">Změnit</span>
										<input type="file" id="copy_this_picture_var" name="copythis" accept="image/*">
									</span>
                            <a href="#" class="btn btn-orange fileinput-exists" data-dismiss="fileinput">Odstranit</a>
                        </div>
                    </div>



                    <div id="vari_specifi_buttonus" class="col-sm-9" style="padding: 0; display: none;">
                        <div id="fin_vari_coppied">
                            <div class="col-sm-6 form-label-group" style="margin-bottom: 8px; padding: 0;">
                                <input type="text" class="form-control" id="copy_this_first_var" name="copythis" value="">
                                <label>Název specifikace*</label>
                            </div>
                            <div class="col-sm-5 form-label-group" style="margin-bottom: 8px; padding: 0 0px 0 8px;">
                                <input type="text" class="form-control" id="copy_this_second_var" name="copythis" value="">
                                <label>Hodnota varianty*</label>
                            </div>
                            <div class="col-sm-1" style="padding: 0;">
                                <i class="remove_specifi_vari entypo-trash" style="float: left; padding: 7px 8px; cursor: pointer;"></i>
                            </div>
                        </div>

                        <button type="button" style="float: left;width: 100%;" class="duplicate_specifi_vari btn btn-default btn-icon icon-left">
                            Přidat další specifikaci
                            <i class="entypo-plus"></i>
                        </button>

                        <input type="text" class="form-control" id="copy_this_hidus_cislus" name="copythis" value="" style="display: none;">

                    </div>



                    <?php $variations_query = $mysqli->query('SELECT * FROM products_variations WHERE product_id="' . $product['id'] . '"') or die($mysqli->error);

                    if (mysqli_num_rows($variations_query) > 0) {

                        $i = 100;
                        while ($variation = mysqli_fetch_array($variations_query)) {
                            $i++;

                            include($_SERVER['DOCUMENT_ROOT'] . '/admin/pages/accessories/parts/variation.php');

                        }

                    }?>


                    <div id="duplicate_variation" class="form-group row" style="display: inline-block; width: 100%; margin: 20px 0 0;">


                                <div class="col-sm-12" style="float:left;">

                                    <center><button type="button" style="width: 300px; padding-top: 14px; font-size: 14px; padding-bottom: 13px; margin-top: -2px;" class="btn btn-info btn-icon icon-left">
                                            Přidat další variantu
                                            <i class="entypo-plus" style="padding-top: 8px; padding-bottom: 13px; font-size: 20px;"></i>
                                        </button></center>
                                </div>


                    </div>
                </div>

            </div>
            <?php } ?>
            </div>

        </div>


			<center>
	<div class="form-group default-padding button-demo">
		<button type="submit" style="margin-bottom: 24px; margin-right: 26px; font-size: 20px; padding: 20px 50px 20px 140px; margin-top: 20px;" data-style="zoom-in" class="ladda-button btn btn-primary btn-icon icon-left btn-lg"><i class="entypo-pencil" style="line-height: 48px;font-size: 40px; padding: 10px 20px;"></i> <span class="ladda-label">Upravit příslušenství</span></button>
	</div></center>

</form>

<?php if($product['type'] == 'variable') { ?>

    <div id="variation_copy" class="panel panel-primary variation variation_copy" style="display: none; border-color: #dedede; width: 49%; margin: 10px 0.5%; float: left;" data-collapsed="0">

        <div class="panel-heading">
            <div class="panel-title">
                <strong style="font-weight: 600;">Varianta<i class="remove_variation entypo-trash" style="margin-top: 8px;     width: 6%; cursor: pointer;"></i></strong>
            </div>

        </div>

        <div class="panel-body">



            <div class="col-sm-12" style="padding: 0;" id="variation_copy_remove">

                <div id="vari_herus_buttonus"></div>


                    <div id="variation_copy_image_here"></div>

                    <div class="col-sm-12" style="float: left;"><hr></div>

                    <div class="form-group" style="margin-top: 24px; margin-bottom: 6px;">
                        <div class="col-sm-9" style="padding-left: 2px;">
                            <label class="radio-new" style="z-index: 9999; position: relative;">
                                <input id="availability" type="radio" name="copythis" value="0">
                                <label>Skladem</label>
                            </label>
                            <label class="radio-new" style="z-index: 9999; position: relative;">
                                <input id="availability" type="radio" name="copythis" value="2">
                                <label>Na objednávku</label>
                            </label>
                            <label class="radio-new" style="z-index: 9999; position: relative;">
                                <input id="availability" type="radio" name="copythis" value="3">
                                <label>Skryto</label>
                            </label>
                            <label class="radio-new" style="z-index: 9999; position: relative;">
                                <input id="availability" type="radio" name="copythis" value="4">
                                <label>Nedostupné</label>

                            </label>
                        </div>

                    </div>

                    <div class="col-sm-3 tooltip-primary form-label-group" data-toggle="tooltip" data-placement="top" title="" data-original-title="SKU" style="margin-bottom: 8px; padding: 0">
                        <input type="text" class="form-control sku" id="variation_sku" name="copythis" value="">
                        <label>Kód SKU*</label>
                    </div>

                    <div class="col-sm-3 form-label-group" style="float: left;">
                        <input type="text" class="form-control ean" id="variation_ean" name="copythis" value="">
                        <label>EAN kód</label>
                    </div>


                    <div class="col-sm-3 form-label-group" style="margin-bottom: 8px;  padding: 0 0 0 10px;">
                        <input type="text" data-mask="decimal" class="form-control" id="variation_price" name="copythis" value="">
                        <label>Cena</label>
                    </div>


                    <div class="col-sm-12" style="float: left;"><hr></div>
                    <?php
                    $locations_query = $mysqli->query("SELECT * FROM shops_locations ORDER BY type ASC");
                    while ($location = mysqli_fetch_array($locations_query)) {
                        ?>
                        <div class="col-sm-3 form-label-group" style="margin-bottom: 0; padding: 0 0 0 10px;">
                            <input type="text" data-mask="decimal" class="form-control" id="new_stock_<?= $location['id'] ?>" name="copythis" value="">
                            <label><?= $location['name'] ?></label>
                        </div>
                    <?php } ?>

                    <div class="col-sm-12" style="float: left;"><hr></div>

                <div class="form-group form-label-group" style="float: left; padding-left: 16px !important;">

                <?php

                    mysqli_data_seek($locations_query, 0);

                    while ($location = mysqli_fetch_array($locations_query)) {


                        ?>
                        <div class="col-lg-3 col-sm-3 has-metric">
                            <input id="min_stock_location_<?= $location['id'] ?>" type="number" class="form-control" name="copythis" value="" placeholder="Minimální Σ <?= $location['name'] ?>">
                            <label for="min_stock_location_<?= $location['id'] ?>">Min. Σ ~ <?= $location['name'] ?></label>
                            <span class="input-group-addon">Ks</span>
                        </div>

                    <?php } ?>

                    </div>

                    <div class="col-sm-12" style="float: left;"><hr></div>


                    <div class="col-sm-3 form-label-group" style="margin-bottom: 8px; padding: 0 0px 0 10px;">
                        <input type="text" data-mask="decimal" class="form-control" id="variation_purchase_price" name="copythis" value="">
                        <label>Nákupní cena</label>
                    </div>
                    <div class="col-sm-3 form-label-group" style="margin-bottom: 8px; padding: 0 0px 0 10px;">
                        <input type="text" data-mask="decimal" class="form-control" id="variation_wholesale_price" name="copythis" value="">
                        <label>Velkoobchodní</label>
                    </div>

                    <div class="col-sm-3 form-label-group" style="margin-bottom: 8px; padding: 0 0px 0 10px;">
                        <input type="text" data-mask="decimal" class="form-control" id="variation_sale_price" name="copythis" value="">
                        <label>Zlevněná cena</label>
                    </div>



                    <div class="col-sm-12" style="float: left;"><hr></div>


                    <div class="col-sm-3 form-label-group" style="padding-left: 0; padding-right: 8px;">
                        <input type="text" class="form-control" style="float:left;" id="variation_weight" name="copythis" value="">
                        <label>Váha (kg)</label>
                    </div>

                    <div class="col-sm-3 form-label-group" style="padding: 0 8px;">
                        <input type="text" class="form-control" style="float:left;" id="variation_length" name="copythis" value="">
                        <label>Délka/Hloubka (cm)</label>
                    </div>
                    <div class="col-sm-3 form-label-group" style="padding: 0 8px;">
                        <input type="text" class="form-control" style="float:left;" id="variation_width" name="copythis" value="">
                        <label>Šířka (cm)</label>
                    </div>
                    <div class="col-sm-3 form-label-group" style="padding-right: 0; padding-left: 8px;">
                        <input type="text" class="form-control" style="float:left;" id="variation_height" name="copythis" value="">
                        <label>Výška (cm)</label>
                    </div>

                    <div class="col-sm-12" style="float: left;"><hr></div>

                    <div class="col-sm-12 form-label-group" style="margin-bottom: 8px; padding: 0; float: left;">
                        <input type="text" class="form-control" id="variation_description" name="copythis" value="">
                        <label>Doplňující popisek varianty</label>
                    </div>



                </div>



        </div>
    </div>

<?php } ?>


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
                //   l.start();
                // }
               });


         });


    </script>

 <script type="text/javascript">
    $(function() {

      $('#summernote').summernote({
          height: 500,
          callbacks: {
              onImageUpload: function(files) {
                  for(let i=0; i < files.length; i++) {
                      $.upload(files[i]);
                  }
              },
              onMediaDelete : function(target) {
                  deleteImage(target[0].src);
              }
          },
      });


    $.upload = function (file) {
        let out = new FormData();
        out.append('file', file, file.name);

        $.ajax({
            method: 'POST',
            url: '/admin/controllers/uploads/upload-image-accessory?product_id=<?= $product['id'] ?>',
            contentType: false,
            cache: false,
            processData: false,
            data: out,
            success: function (img) {
                $('#summernote').summernote('insertImage', img);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error(textStatus + " " + errorThrown);
            }
        });
    };

    function deleteImage(src) {
        $.ajax({
            data: {
                src : src,
            },
            type: "POST",
            url: "/admin/controllers/uploads/upload-image-accessory?action=remove",
            cache: false,
            success: function(data) {
                console.log(src);
                console.log(data);
            }
        });
    }


        $('form').on('submit', function (e) {
        // e.preventDefault();
        // alert($('.summernote').summernote('code'));
      });
    });
  </script>


<?php include VIEW . '/default/footer.php'; ?>



<?php

} else {

    include INCLUDES . "/404.php";

}?>
