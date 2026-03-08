<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";
include_once INCLUDES . "/accessories-functions.php";

$categorytitle = "Příslušenství";
$pagetitle = "Naskladnění příslušenství";

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "add") {

    if (isset($_POST['product_sku'])) {

        $post = array_filter($_POST['product_sku']);
        if (!empty($post)) {

            foreach ($post as $post_index => $posterino) {

                if (!empty($_POST['product_quantity'][$post_index])) {

                    $quantity = $_POST['product_quantity'][$post_index];
                    $purchase_price = $_POST['product_price'][$post_index];

                    // SIMPLE PRODUCT
                    $searchquery = $mysqli->query("SELECT id FROM products WHERE code = '$posterino'") or die($mysqli->error);

                    if (mysqli_num_rows($searchquery) > 0) {

                        $search = mysqli_fetch_array($searchquery);

                        $search['vid'] = '0';

                        // VARIABLE PRODUCT
                    } else {

                        $searchquery = $mysqli->query("SELECT p.id as id, v.id as vid FROM products p, products_variations v WHERE v.product_id = p.id AND v.sku = '$posterino'") or die($mysqli->error);

                        if (mysqli_num_rows($searchquery) > 0) {

                            $search = mysqli_fetch_array($searchquery);

                        }

                    }

                }

            }

        }

    }

    Header("Location:https://www.wellnesstrade.cz/admin/pages/accessories/editace-prislusenstvi");
    exit;

}

include VIEW . '/default/header.php';


?>

<style>

    .has-warning .selectboxit-container .selectboxit { border-color: #ffd78a !important;}

    .page-body .selectboxit-container .selectboxit-options { margin-top: 40px !important; width: 100% !important;}
    .page-body .selectboxit-container .selectboxit { height: 40px;width: 100% !important;}
    .page-body .selectboxit-container .selectboxit .selectboxit-text { line-height: 40px; }
    .page-body .selectboxit-container .selectboxit .selectboxit-arrow-container { height: 40px;}
    .page-body .selectboxit-container .selectboxit .selectboxit-arrow-container:after { line-height: 40px;}

    .nicescroll-rails > div:hover {
        background: rgb(53, 174, 255) !important;
    }

    #custom-scroller { width: 500px; }
    .col-2, .col-8, .col-3, .col-4, .col-6 {
        display: inline-block;
        padding: 5px 2%;
        vertical-align: top;
    }

    .item {
        margin-right: 10px;
    }

    .col-2 { width: 18%; }
    .col-8 { width: 76%; }
    .col-3 { width: 26%; }
    .col-4 { width: 36%; }
    .col-6 { width: 60%; }
    .select2-drop img { width: 100%; margin: 2%; }

    .bigdrop.select2-container .select2-results {max-height: 300px;}
    .bigdrop .select2-results {max-height: 300px;}

</style>

<form id="supply_form" role="form" method="post" class="form-horizontal form-groups-bordered validate" action="naskladneni?action=add" enctype="multipart/form-data">

        <div class="row">

            <div class="col-md-6">
                <div class="panel panel-primary" data-collapsed="0">

                    <div class="panel-heading">
                        <div class="panel-title">
                            <strong style="font-weight: 600;">Informace prodejce</strong>
                        </div>

                    </div>

                    <div class="form-group"><br>
                        <div class="col-sm-12"><div class="col-sm-12">
                                <textarea name="admin_note" class="form-control autogrow" id="field-7"></textarea>
                            </div>		</div>
                    </div>

                </div>


                <div class="panel panel-primary" data-collapsed="0">

                    <div class="panel-heading">
                        <div class="panel-title">
                            <strong style="font-weight: 600;">Základní údaje</strong>
                        </div>

                    </div>

                    <div class="panel-body">
                        <div class="form-group">
                            <label for="field-1" class="col-sm-3 control-label">Datum doručení</label>
                            <div class="col-sm-6">
                                <div class="date">
                                    <input type="text" class="form-control datepicker" name="date" data-format="yyyy-mm-dd" placeholder="Datum" value="">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>


            <div class="col-md-6">

                <div class="panel panel-primary" data-collapsed="0">

                    <div class="panel-heading">
                        <div class="panel-title">
                            <strong style="font-weight: 600;">Položky momentálně vybraného výrobce</strong>
                        </div>

                    </div>

                    <div class="panel-body">

                        <script type="text/javascript">
                            jQuery(document).ready(function($)
                            {

                                $('#selectbox-o').select2({
                                    minimumInputLength: 2,
                                    ajax: {
                                        url: "/admin/data/autosuggest-products-stock",
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





                                $('#selectbox-o').on("change", function(e) {



                                    var data = $('#selectbox-o').select2('data');


//$("#empty-holder").load("/admin/controllers/modals/products?sku="+vlue);


                                    $('#specification_copy').clone(true).insertBefore("#duplicate_specification").attr('id', 'copied').addClass('has-success').show();

                                    $('#copied #copy_this_first').attr('name', 'product_name[]').attr('value', data.pure_text);

                                    $('#copied #copy_this_third').attr('name', 'product_sku[]').attr('value', data.id);

                                    $('#copied #copy_this_second').attr('name', 'product_quantity[]').attr('value', '1');

                                    $('#copied').attr('id', 'copifinish');

                                    $("#selectbox-o").select2("val", "");

                                    setTimeout(function(){
                                        $('#copifinish').attr('id', 'hasfinish').removeClass('has-success');}, 2000);


                                });


                                $('.remove_specification').click(function() {
                                    $(this).closest('.specification').remove();
                                    event.preventDefault();
                                });

                            });
                        </script>

                        <!-- Product Name Select Box -->
                        <div class="form-group">
                            <div class="col-sm-12">
                                <input id="selectbox-o" class="input-xlarge" name="optionvalue" type="hidden" data-placeholder="Vyberte produkt.." />
                            </div>
                        </div>

                        <hr>

                            <div class="form-group">

                                <div class="col-sm-12" style="float:left; padding: 0;">


                                    <div id="specification_copy" class="specification" style="display: none; float:left; width: 100%;">

                                        <div class="col-sm-6" style="margin-bottom: 8px; padding: 0;">

                                            <input type="text" class="form-control" id="copy_this_first" name="copythis" value="" placeholder="Název produktu">

                                            <input type="text" class="form-control" id="copy_this_third" name="copythis" value="" placeholder="SKU produktu" style="display: none;">

                                        </div>

                                        <div class="col-sm-1" style="padding: 0 0px 0 8px;">

                                            <input type="text" class="form-control text-center" id="copy_this_second" name="copythis" value="" placeholder="Počet">

                                        </div>


                                        <div class="col-sm-1" style="padding: 0 0px 0 11px;">
                                            <button type="button" class="remove_specification btn btn-red" style="float:left; padding: 6px 10px; cursor: pointer;"> <i class="entypo-trash"></i> </button>
                                        </div>
                                    </div>


                                    <div id="empty-holder"></div>


                                    <button type="button" id="duplicate_specification" style="display: none;" class="btn btn-default btn-icon icon-left">
                                    </button>
                                </div>
                            </div>


                        <hr>

                            <div class="form-group">
                                <label class="col-sm-3 control-label">Pobočka k vypořádání</label>

                                <div class="col-sm-9" style="float:left;">
                                    <?php

                                    if (empty($location_id) || $location_id == '0') {$desired_location = 7;} else { $desired_location = $location_id;}

                                    $locations_query = $mysqli->query("SELECT * FROM shops_locations ORDER BY type ASC");

                                    $i = 0;
                                    while ($location = mysqli_fetch_array($locations_query)) {

                                        ?>
                                        <div class="radio" style="width: 33%; float: left;">
                                            <label>
                                                <input type="radio" <?php if (empty($location_id)) { ?>name="location"<?php } ?> value="<?= $location['id'] ?>" <?php if (($i == 0 && empty($location_id)) || $location['id'] == $desired_location) {$i++;
                                                    echo 'checked';}if (!empty($location_id)) {echo ' disabled';}?>><?= $location['name'] ?>
                                            </label>
                                        </div>
                                    <?php } ?>

                                    <?php if (!empty($location_id)) { ?><input type="text" name="location" value="<?= $desired_location ?>" style="display: none;"><?php } ?>

                                </div>
                            </div>


                    </div>
                </div>

            </div>

        </div>

        <center>
            <div class="form-group default-padding button-demo">
                <button type="submit" style="margin-bottom: 24px; margin-right: 26px; font-size: 20px; padding: 20px 50px 20px 140px; margin-top: 20px;" data-style="zoom-in" class="ladda-button btn btn-success btn-icon icon-left btn-lg"><i class="entypo-plus" style="line-height: 48px;font-size: 40px; padding: 10px 20px;"></i> <span class="ladda-label">Naskladnit zboží</span></button>
            </div>
        </center>

    </form>

<footer class="main">

    &copy; <?= date("Y") ?> <span style=" float:right;"><?php
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $finish = $time;
        $total_time = round(($finish - $start), 4);

        echo 'PHP '.PHP_VERSION.' | Page generated in ' . $total_time . ' seconds.';?></span>

</footer>
</div>

</div>

<?php include VIEW . '/default/footer.php'; ?>

