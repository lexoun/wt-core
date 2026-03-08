<?php


include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";

include INCLUDES . "/googlelogin.php";
include INCLUDES . "/functions.php";

$id = $_REQUEST['id'];

$spec_query = $mysqli->query('SELECT * FROM specs WHERE id = "' . $_REQUEST['id'] . '"') or die($mysqli->error);

if (mysqli_num_rows($spec_query) > 0) {

    $spec = mysqli_fetch_assoc($spec_query);

    $pagetitle = $spec['name']." - propojené příslušenství";

    $bread1 = "Specifikace";
    $abread1 = "specifikace";

    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit') {

        $get_warehouse_products = $mysqli->query('SELECT * FROM warehouse_products WHERE customer = 1') or die($mysqli->error);

        while ($warehouse_product = mysqli_fetch_assoc($get_warehouse_products)) {

            if ($spec['type'] == '1') {

                $get_params = $mysqli->query('SELECT *, p.id as param_id FROM 
            specs_params p, 
            warehouse_products_types_specs s, 
            warehouse_products_types t 
        WHERE 
            s.spec_param_id = p.id 
        AND s.type_id = t.id 
        AND s.spec_id = "' . $_REQUEST['id'] . '" 
        AND t.warehouse_product_id = "' . $warehouse_product['id'] . '" 
        
        GROUP BY p.id') or die($mysqli->error);

                while ($param = mysqli_fetch_assoc($get_params)) {

                    $post_products = array();

                    $warehouse_id = $warehouse_product['id'];
                    $param_id = $param['param_id'];

//                echo $warehouse_id . ' - ' . $param_id;

                    if (isset($_POST['product_sku_' . $warehouse_id . '_' . $param_id])) {

                        $post_products = $_POST['product_sku_' . $warehouse_id . '_' . $param_id];

                    } else {

                        $post_products = array();

                    }

//                print_r($post_products);

                    $find_simple_product = $mysqli->query("SELECT b.product_id, b.variation_id, p.code  FROM products p, demands_products b WHERE p.id = b.product_id AND b.type = '" . $warehouse_product['connect_name'] . "' AND b.spec_id = '" . $_REQUEST['id'] . "' AND b.param_id = '" . $param['param_id'] . "' order by p.id desc") or die($mysqli->error);

                    $find_variable_product = $mysqli->query("SELECT b.product_id, b.variation_id, v.sku FROM products_variations v, demands_products b WHERE v.id = b.variation_id AND b.type = '" . $warehouse_product['connect_name'] . "' AND b.spec_id = '" . $_REQUEST['id'] . "' AND b.param_id = '" . $param['param_id'] . "'") or die($mysqli->error);

                    $array1 = array();
                    while ($row = mysqli_fetch_assoc($find_simple_product)) {
                        $array1[] = $row['code'];
                    }
                    while ($row = mysqli_fetch_assoc($find_variable_product)) {
                        $array1[] = $row['sku'];
                    }

                    $array2 = array_filter($post_products);

                    $dups_old = array();
                    foreach (array_count_values($array2) as $val => $c) {
                        if ($c > 1) {
                            $dups_old[] = $val;
                        }
                    }

                    $dups_new = array();
                    foreach (array_count_values($array1) as $val => $c) {
                        if ($c > 1) {
                            $dups_new[] = $val;
                        }
                    }

                    $check_duplicants = array_diff((array)$dups_new, (array)$dups_old);

                    $removed_products = array_diff((array)$array1, (array)$array2); // odebírané produkty

                    $removed_products = array_merge((array)$removed_products, (array)$check_duplicants);

                    foreach ($removed_products as $removed) {

                        if ($removed != '') {

                            $find_simple = $mysqli->query("SELECT p.id FROM products p WHERE p.code = '$removed'") or die($mysqli->error);
                            if (mysqli_num_rows($find_simple) != 0) {

                                $simple = mysqli_fetch_assoc($find_simple);

                                $mysqli->query("DELETE FROM demands_products WHERE product_id = '" . $simple['id'] . "' AND param_id = '" . $param_id . "' AND type = '" . $warehouse_product['connect_name'] . "' AND spec_id = '" . $_REQUEST['id'] . "'");

//                            echo 'delete simple';

                            } else {

                                $find_var = $mysqli->query("SELECT id, product_id FROM products_variations WHERE sku = '$removed'") or die($mysqli->error);

                                $var = mysqli_fetch_assoc($find_var);

                                $mysqli->query("DELETE FROM demands_products WHERE product_id = '" . $simple['id'] . "' AND variation_id = '" . $var['id'] . "' AND param_id = '" . $param_id . "' AND type = '" . $warehouse_product['connect_name'] . "' AND spec_id = '" . $_REQUEST['id'] . "'");

//                            echo 'delete variable';

                            }

                        }

                    }

                    $added_products = array_diff((array)$array2, (array)$array1); // přidávané produkty

                    $stable_products = array_intersect((array)$array1, (array)$array2);

                    if (isset($post_products)) {

                        $post = array_filter($post_products);

                        if (!empty($post)) {

                            foreach ($post as $post_index => $posterino) {

                                if (in_array($posterino, $added_products)) {


                                    $find_simple = $mysqli->query("SELECT p.id FROM products p WHERE p.code = '$posterino'") or die($mysqli->error);
                                    if (mysqli_num_rows($find_simple) != 0) {

                                        $simple = mysqli_fetch_assoc($find_simple);

                                        $mysqli->query("INSERT INTO demands_products (product_id, param_id, type, spec_id) VALUES ('" . $simple['id'] . "', '" . $param_id . "', '" . $warehouse_product['connect_name'] . "', '" . $_REQUEST['id'] . "')") or die($mysqli->error);

//                                    echo 'add new simple';

                                    } else {

                                        $find_var = $mysqli->query("SELECT id, product_id FROM products_variations WHERE sku = '$removed'") or die($mysqli->error);

                                        $var = mysqli_fetch_assoc($find_var);

                                        $mysqli->query("INSERT INTO demands_products (product_id, variation_id, param_id, type, spec_id) VALUES ('" . $var['product_id'] . "', '" . $var['id'] . "', '" . $param_id . "', '" . $warehouse_product['connect_name'] . "', '" . $_REQUEST['id'] . "')") or die($mysqli->error);

//                                    echo 'add new variable';

                                    }


                                } elseif (in_array($posterino, $stable_products)) {


//                                    echo 'edit old';


                                }

                            }

                        }

                    }

                }


            } elseif ($spec['type'] == '0') {

                $get_params = $mysqli->query('SELECT *, p.id as param_id FROM 
            specs_params p, 
            warehouse_products_types_specs s, 
            warehouse_products_types t 
        WHERE 
            s.spec_param_id = p.id 
        AND s.type_id = t.id 
        AND s.spec_id = "' . $_REQUEST['id'] . '" 
        AND t.warehouse_product_id = "' . $warehouse_product['id'] . '" 
        
        GROUP BY p.id') or die($mysqli->error);


                $param['param_id'] = 1;
                $param['option'] = 'Ano';


                $post_products = array();

                $warehouse_id = $warehouse_product['id'];
                $param_id = $param['param_id'];

//                echo $warehouse_id . ' - ' . $param_id;

                if (isset($_POST['product_sku_' . $warehouse_id . '_' . $param_id])) {

                    $post_products = $_POST['product_sku_' . $warehouse_id . '_' . $param_id];

                } else {

                    $post_products = array();

                }

//                print_r($post_products);

                $find_simple_product = $mysqli->query("SELECT b.product_id, b.variation_id, p.code  FROM products p, demands_products b WHERE p.id = b.product_id AND b.type = '" . $warehouse_product['connect_name'] . "' AND b.spec_id = '" . $_REQUEST['id'] . "' AND b.param_id = '" . $param['param_id'] . "' order by p.id desc") or die($mysqli->error);

                $find_variable_product = $mysqli->query("SELECT b.product_id, b.variation_id, v.sku FROM products_variations v, demands_products b WHERE v.id = b.variation_id AND b.type = '" . $warehouse_product['connect_name'] . "' AND b.spec_id = '" . $_REQUEST['id'] . "' AND b.param_id = '" . $param['param_id'] . "'") or die($mysqli->error);

                $array1 = array();
                while ($row = mysqli_fetch_assoc($find_simple_product)) {
                    $array1[] = $row['code'];
                }
                while ($row = mysqli_fetch_assoc($find_variable_product)) {
                    $array1[] = $row['sku'];
                }

                $array2 = array_filter($post_products);

                $dups_old = array();
                foreach (array_count_values($array2) as $val => $c) {
                    if ($c > 1) {
                        $dups_old[] = $val;
                    }
                }

                $dups_new = array();
                foreach (array_count_values($array1) as $val => $c) {
                    if ($c > 1) {
                        $dups_new[] = $val;
                    }
                }

                $check_duplicants = array_diff((array)$dups_new, (array)$dups_old);

                $removed_products = array_diff((array)$array1, (array)$array2); // odebírané produkty

                $removed_products = array_merge((array)$removed_products, (array)$check_duplicants);

                foreach ($removed_products as $removed) {

                    if ($removed != '') {

                        $find_simple = $mysqli->query("SELECT p.id FROM products p WHERE p.code = '$removed'") or die($mysqli->error);
                        if (mysqli_num_rows($find_simple) != 0) {

                            $simple = mysqli_fetch_assoc($find_simple);

                            $mysqli->query("DELETE FROM demands_products WHERE product_id = '" . $simple['id'] . "' AND param_id = '" . $param_id . "' AND type = '" . $warehouse_product['connect_name'] . "' AND spec_id = '" . $_REQUEST['id'] . "'");

//                            echo 'delete simple';

                        } else {

                            $find_var = $mysqli->query("SELECT id, product_id FROM products_variations WHERE sku = '$removed'") or die($mysqli->error);

                            $var = mysqli_fetch_assoc($find_var);

                            $mysqli->query("DELETE FROM demands_products WHERE product_id = '" . $simple['id'] . "' AND variation_id = '" . $var['id'] . "' AND param_id = '" . $param_id . "' AND type = '" . $warehouse_product['connect_name'] . "' AND spec_id = '" . $_REQUEST['id'] . "'");

//                            echo 'delete variable';

                        }

                    }

                }

                $added_products = array_diff((array)$array2, (array)$array1); // přidávané produkty

                $stable_products = array_intersect((array)$array1, (array)$array2);

                if (isset($post_products)) {

                    $post = array_filter($post_products);

                    if (!empty($post)) {

                        foreach ($post as $post_index => $posterino) {

                            if (in_array($posterino, $added_products)) {


                                $find_simple = $mysqli->query("SELECT p.id FROM products p WHERE p.code = '$posterino'") or die($mysqli->error);
                                if (mysqli_num_rows($find_simple) != 0) {

                                    $simple = mysqli_fetch_assoc($find_simple);

                                    $mysqli->query("INSERT INTO demands_products (product_id, param_id, type, spec_id) VALUES ('" . $simple['id'] . "', '" . $param_id . "', '" . $warehouse_product['connect_name'] . "', '" . $_REQUEST['id'] . "')") or die($mysqli->error);

//                                    echo 'add new simple';

                                } else {

                                    $find_var = $mysqli->query("SELECT id, product_id FROM products_variations WHERE sku = '$removed'") or die($mysqli->error);

                                    $var = mysqli_fetch_assoc($find_var);

                                    $mysqli->query("INSERT INTO demands_products (product_id, variation_id, param_id, type, spec_id) VALUES ('" . $var['product_id'] . "', '" . $var['id'] . "', '" . $param_id . "', '" . $warehouse_product['connect_name'] . "', '" . $_REQUEST['id'] . "')") or die($mysqli->error);

//                                    echo 'add new variable';

                                }


                            } elseif (in_array($posterino, $stable_products)) {


//                                echo 'edit old';


                            }

                        }

                    }

                }

            }

        }

        header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/specifikace-propojene-prislusenstvi?id='.$_REQUEST['id']);
        exit;

    }

    include VIEW . '/default/header.php';

    ?>

<script type="text/javascript">

function randomPassword(length) {
    var chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOP1234567890";
    var pass = "";
    for (var x = 0; x < length; x++) {
        var i = Math.floor(Math.random() * chars.length);
        pass += chars.charAt(i);
    }
    return pass;
}

function generate() {
    myform.password.value = randomPassword(myform.length.value);
}

jQuery(document).ready(function($)
{

$('.radio').click(function() {
   if($("input:radio[class='saunaradio']").is(":checked")) {


	$('.virivkens').hide( "slow");
	$('.saunkens').show( "slow");
   }
     if($("input:radio[class='virivkaradio']").is(":checked")) {


   	$('.saunkens').hide( "slow");
$('.virivkens').show( "slow");
   }
});
});


</script>

        <h3><?= $pagetitle ?></h3>
<form role="form" method="post" name="myform" class="form-horizontal form-groups-bordered validate" action="specifikace-propojene-prislusenstvi?id=<?= $_REQUEST['id'] ?>&action=edit">
<input type="hidden" name="length" value="14">
	<div class="row">







							<?php

    $get_warehouse_products = $mysqli->query('SELECT * FROM warehouse_products WHERE customer = 1') or die($mysqli->error);

    while ($warehouse_product = mysqli_fetch_assoc($get_warehouse_products)) {

        ?>
						<div class="col-md-12">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						<strong style="font-weight: 600;"><?= $warehouse_product['fullname'] ?></strong>
					</div>

				</div>

						<div class="panel-body">

					<?php

                    if($spec['type'] == 0){


                            $param['param_id'] = 1;
                            $param['option'] = 'Ano';

                            ?>

                            <div class="col-sm-4">
                            <h4 style="padding: 0; margin-bottom: 14px;"><?= $param['option'] ?></h4>

                            <script type="text/javascript">
                                jQuery(document).ready(function($)
                                {



                                    $('#selectbox-o_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>').select2({
                                        minimumInputLength: 2,
                                        ajax: {
                                            url: "/admin/data/autosuggest-products",
                                            dataType: 'json',
                                            data: function (term, page) {
                                                return {
                                                    q: term
                                                };
                                            },
                                            results: function (data, page) {
                                                return { results: data };
                                            }
                                        },

                                        formatResult: format,
                                        formatSelection: format,
                                        escapeMarkup: function(m) { return m; }


                                    });


                                    function format(data) {
                                        if (!data.id) return data.text; // optgroup

                                        return "<img src='https://www.wellnesstrade.cz/data/stores/images/mini/" + data.seourl + ".jpg' height='20'/>" + data.text;

                                    }



                                    $('#selectbox-o_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>').on("change", function(e) {


                                        var vlue = $("#selectbox-o_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>").select2("val");

                                        var nema = $("#s2id_selectbox-o_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?> .select2-chosen").text();

                                        $('#specification_copy_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>').clone(true).insertBefore("#duplicate_specification_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>").attr('id', 'copied_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>').addClass('has-success').show();

                                        $('#copied_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?> #copy_this_first').attr('name', 'product_name_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>[]').attr('value', nema);

                                        $('#copied_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?> #copy_this_third').attr('name', 'product_sku_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>[]').attr('value', vlue);


                                        $('#copied_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>').attr('id', 'copifinish_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>');

                                        $("#selectbox-o_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>").select2("val", "");

                                        setTimeout(function(){
                                            $('#copifinis_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>h').attr('id', 'hasfinish').removeClass('has-success');}, 2000);


                                    });


                                    $('.remove_specification_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>').click(function() {
                                        $(this).closest('.specification_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>').remove();
                                        event.preventDefault();
                                    });

                                });
                            </script>


                            <!-- Product Name Select Box -->
                            <div class="form-group">
                                <div class="col-sm-12" style="padding: 0; width: 100%; padding-right: 20px;">
                                    <input id="selectbox-o_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>" class="input-xlarge" name="optionvalue" type="hidden" data-placeholder="Vyberte produkt.." />
                                </div>
                            </div>



                            <div class="form-group">

                                <div class="col-sm-12" style="float:left; padding: 0;">


                                    <div id="specification_copy_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>" class="specification_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>" style="display: none; float:left; width: 100%;">

                                        <div class="col-sm-6" style="margin-bottom: 8px; padding: 0;">

                                            <input type="text" class="form-control" id="copy_this_first" name="copythis" value="" placeholder="Název produktu">

                                            <input type="text" class="form-control" id="copy_this_third" name="copythis" value="" placeholder="SKU produktu" style="display: none;">

                                        </div>

                                        <div class="col-sm-2" style="padding: 0 0px 0 11px;">
                                            <button type="button" class="remove_specification_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?> btn btn-red" style="float:left; padding: 6px 10px; cursor: pointer;"> <i class="entypo-trash"></i> </button>
                                        </div>
                                    </div>




                                    <?php

                                    $accessories_query = $mysqli->query('SELECT *, p.id as id, v.id as vid, p.price as price, v.price as variation_price FROM products p, demands_products b LEFT JOIN products_variations v ON v.id = b.variation_id WHERE p.id = b.product_id AND b.type = "' . $warehouse_product['connect_name'] . '" AND b.param_id = "' . $param['param_id'] . '" AND b.spec_id = "'.$spec['id'].'" GROUP BY p.id') or die($mysqli->error);

                                    while ($accessory = mysqli_fetch_assoc($accessories_query)) {

                                        if ($accessory['type'] == 'variable') {

                                            $product_name = "";
                                            $variation_query = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $accessory['vid'] . "'");
                                            while ($variation = mysqli_fetch_array($variation_query)) {

                                                $product_name = $variation['name'] . ': ' . $variation['value'];

                                                $product_title = $accessory['productname'] . ' – ' . $product_name;
                                                $sku = $accessory['sku'];

                                            }

                                        } else {

                                            $product_title = $accessory['productname'];
                                            $sku = $accessory['code'];

                                        }

                                        ?>

                                        <div class="specification_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>" style="float: left; width: 100%;">
                                            <div class="col-sm-10" style="margin-bottom: 8px; padding: 0;">

                                                <input type="text" class="form-control" id="specification_name" name="product_name_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>[]" value="<?= $product_title ?>" placeholder="Název produktu">

                                                <input type="text" class="form-control" id="copy_this_third" name="product_sku_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>[]" value="<?= $sku ?>" placeholder="SKU produktu" style="display: none;">

                                            </div>

                                            <div class="col-sm-2" style="padding: 0 0px 0 11px;">
                                                <button type="button" class="remove_specification_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?> btn btn-red" style="float:left; padding: 6px 10px; cursor: pointer;"> <i class="entypo-trash"></i> </button>
                                            </div>
                                        </div>

                                        <?php

                                        ?><br><?php

                                    }?>
                                    <button type="button" id="duplicate_specification_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>" style="display: none;" class="btn btn-default btn-icon icon-left">
                                    </button>
                                </div>
                            </div>

                            <hr>
                            </div><?php




                    }else{


        $get_params = $mysqli->query('SELECT *, p.id as param_id FROM specs_params p, warehouse_products_types_specs s, warehouse_products_types t WHERE s.spec_param_id = p.id AND s.type_id = t.id AND s.spec_id = "' . $_REQUEST['id'] . '" AND t.warehouse_product_id = "' . $warehouse_product['id'] . '" GROUP BY p.id') or die($mysqli->error);

        while ($param = mysqli_fetch_assoc($get_params)) {

            ?>

            <div class="col-sm-4">
							<h4 style="padding: 0; margin-bottom: 14px;"><?= $param['option'] ?></h4>

<script type="text/javascript">
jQuery(document).ready(function($)
{



$('#selectbox-o_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>').select2({
        minimumInputLength: 2,
        ajax: {
            url: "/admin/data/autosuggest-products",
            dataType: 'json',
            data: function (term, page) {
                return {
                    q: term
                };
            },
            results: function (data, page) {
                return { results: data };
            }
        },

        formatResult: format,
        formatSelection: format,
        escapeMarkup: function(m) { return m; }


    });


    function format(data) {
        if (!data.id) return data.text; // optgroup

        return "<img src='https://www.wellnesstrade.cz/data/stores/images/mini/" + data.seourl + ".jpg' height='20'/>" + data.text;

    }



$('#selectbox-o_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>').on("change", function(e) {


	var vlue = $("#selectbox-o_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>").select2("val");

	var nema = $("#s2id_selectbox-o_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?> .select2-chosen").text();

	$('#specification_copy_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>').clone(true).insertBefore("#duplicate_specification_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>").attr('id', 'copied_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>').addClass('has-success').show();

	$('#copied_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?> #copy_this_first').attr('name', 'product_name_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>[]').attr('value', nema);

	$('#copied_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?> #copy_this_third').attr('name', 'product_sku_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>[]').attr('value', vlue);


	$('#copied_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>').attr('id', 'copifinish_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>');

	$("#selectbox-o_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>").select2("val", "");

	setTimeout(function(){
      $('#copifinis_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>h').attr('id', 'hasfinish').removeClass('has-success');}, 2000);


});


$('.remove_specification_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>').click(function() {
   $(this).closest('.specification_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>').remove();
   event.preventDefault();
});

});
</script>


		<!-- Product Name Select Box -->
		<div class="form-group">
		   <div class="col-sm-12" style="padding: 0; width: 100%; padding-right: 20px;">
		     <input id="selectbox-o_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>" class="input-xlarge" name="optionvalue" type="hidden" data-placeholder="Vyberte produkt.." />
		   </div>
		</div>



		<div class="form-group">

	<div class="col-sm-12" style="float:left; padding: 0;">


	<div id="specification_copy_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>" class="specification_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>" style="display: none; float:left; width: 100%;">

		<div class="col-sm-6" style="margin-bottom: 8px; padding: 0;">

			<input type="text" class="form-control" id="copy_this_first" name="copythis" value="" placeholder="Název produktu">

			<input type="text" class="form-control" id="copy_this_third" name="copythis" value="" placeholder="SKU produktu" style="display: none;">

		</div>

		<div class="col-sm-2" style="padding: 0 0px 0 11px;">
			<button type="button" class="remove_specification_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?> btn btn-red" style="float:left; padding: 6px 10px; cursor: pointer;"> <i class="entypo-trash"></i> </button>
		 </div>
	</div>




<?php

            $accessories_query = $mysqli->query('SELECT *, p.id as id, v.id as vid, p.price as price, v.price as variation_price FROM products p, demands_products b LEFT JOIN products_variations v ON v.id = b.variation_id WHERE p.id = b.product_id AND b.type = "' . $warehouse_product['connect_name'] . '" AND b.param_id = "' . $param['param_id'] . '" AND b.spec_id = "'.$spec['id'].'" GROUP BY p.id') or die($mysqli->error);

            while ($accessory = mysqli_fetch_assoc($accessories_query)) {

                if ($accessory['type'] == 'variable') {

                    $product_name = "";
                    $variation_query = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $accessory['vid'] . "'");
                    while ($variation = mysqli_fetch_array($variation_query)) {

                        $product_name = $variation['name'] . ': ' . $variation['value'];

                        $product_title = $accessory['productname'] . ' – ' . $product_name;
                        $sku = $accessory['sku'];

                    }

                } else {

                    $product_title = $accessory['productname'];
                    $sku = $accessory['code'];

                }

                ?>

				<div class="specification_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>" style="float: left; width: 100%;">
					<div class="col-sm-10" style="margin-bottom: 8px; padding: 0;">

					<input type="text" class="form-control" id="specification_name" name="product_name_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>[]" value="<?= $product_title ?>" placeholder="Název produktu">

					<input type="text" class="form-control" id="copy_this_third" name="product_sku_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>[]" value="<?= $sku ?>" placeholder="SKU produktu" style="display: none;">

					</div>

					<div class="col-sm-2" style="padding: 0 0px 0 11px;">
					<button type="button" class="remove_specification_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?> btn btn-red" style="float:left; padding: 6px 10px; cursor: pointer;"> <i class="entypo-trash"></i> </button>
					 </div>
				</div>

					<?php

                ?><br><?php

            }?>
		<button type="button" id="duplicate_specification_<?= $warehouse_product['id'] ?>_<?= $param['param_id'] ?>" style="display: none;" class="btn btn-default btn-icon icon-left">
      </button>
  </div>
  </div>

		<hr>
            </div><?php

        }

                    }
        ?>
				</div>

				</div>
				</div>
			<?php

    }

    ?>





	</div>



			<center>
	<div class="form-group default-padding" style="margin-left: -100px;">

  <a href="./specifikace"><button type="button" class="btn btn-primary">Zpět</button></a>
		<button type="submit" class="btn btn-success">Uložit</button>
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

	<script>

        $(document).ready(function(){

            $("#order_form").on("submit", function(){
              var form = $( "#order_form" );
                         var l = Ladda.create( document.querySelector( '#order_form .button-demo button' ) );
                if(form.valid()){

                  l.start();
                }
               });


         });


    </script>
<?php include VIEW . '/default/footer.php'; ?>



<?php

} else {

    include INCLUDES . "/404.php";

}?>
