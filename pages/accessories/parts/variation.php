<?php

if(!empty($variation)){

$sale_price = truevalue($variation['sale_price']);
$weight = truevalue($variation['weight']);
$length = truevalue($variation['length']);
$width = truevalue($variation['width']);
$height = truevalue($variation['height']);

$data['price'] = truevalue($variation['price']);

$purchase_price = truevalue($variation['purchase_price']);
$wholesale_price = truevalue($variation['wholesale_price']);

}

?>

<div class="panel panel-primary variation" data-collapsed="0" style="border-color: #dedede; width: 49.5%; float: left; margin: 0 0.25% 14px;">

    <div class="panel-heading">
        <div class="panel-title">
            <strong style="font-weight: 600;">Varianta<i class="remove_variation entypo-trash" style="margin-top: 8px;     width: 6%; cursor: pointer;"></i></strong>
        </div>

    </div>

    <div class="panel-body">

        <div class="col-sm-12" style="padding: 0;">



            <div class="col-sm-9" style="padding: 0;">
                <?php

                if(!empty($variation)){

                $variations_values_query = $mysqli->query('SELECT * FROM products_variations_values WHERE variation_id="' . $variation['id'] . '"') or die($mysqli->error);

                if (mysqli_num_rows($variations_values_query) > 0) {
                    $first = 0;
                    while ($variation_value = mysqli_fetch_array($variations_values_query)) {
                        ?>

                        <div id="fin_vari_coppied">
                            <div class="col-sm-6 form-label-group" style="margin-bottom: 8px; padding: 0;">
                                <input type="text" class="form-control" id="variation_name" name="item[<?= $i ?>][variation_name][]" value="<?= $variation_value['name'] ?>">                                <?php if ($first == 0) { ?><label>Název specifikace*</label><?php } ?>

                            </div>
                            <div class="col-sm-5 form-label-group" style="margin-bottom: 8px; padding: 0 0px 0 8px;">
                                <input type="text" class="form-control" id="variation_value" name="item[<?= $i ?>][variation_value][]" value="<?= $variation_value['value'] ?>">
                                <?php if ($first == 0) { ?><label>Hodnota varianty*</label><?php } ?>

                            </div>
                            <div class="col-sm-1" style="padding: 0;">
                                <i class="remove_specifi_vari entypo-trash" style="float: left; padding: 7px 8px; cursor: pointer;"></i>
                            </div>
                        </div>

                        <?php

                        $first = 1;
                    }
                }else { ?>

                <div id="fin_vari_coppied">
                    <div class="col-sm-6 form-label-group" style="margin-bottom: 8px; padding: 0;">
                        <input type="text" class="form-control" id="variation_name" name="item[<?= $i ?>][variation_name][]" value="">
                        <label>Název specifikace*</label>

                    </div>
                    <div class="col-sm-5 form-label-group" style="margin-bottom: 8px; padding: 0 0px 0 8px;">
                        <input type="text" class="form-control" id="variation_value" name="item[<?= $i ?>][variation_value][]" value="">
                        <label>Hodnota varianty*</label>
                    </div>
                    <div class="col-sm-1" style="padding: 0;">
                        <i class="remove_specifi_vari entypo-trash" style="float: left; padding: 7px 8px; cursor: pointer;"></i>
                    </div>
                </div>

                <?php } }else { ?>

                    <div id="fin_vari_coppied">
                        <div class="col-sm-6 form-label-group" style="margin-bottom: 8px; padding: 0;">
                            <input type="text" class="form-control" id="variation_name" name="item[<?= $i ?>][variation_name][]" value="">
                            <label>Název specifikace*</label>
                        </div>
                        <div class="col-sm-5 form-label-group" style="margin-bottom: 8px; padding: 0 0px 0 8px;">
                            <input type="text" class="form-control" id="variation_value" name="item[<?= $i ?>][variation_value][]" value="">
                            <label>Hodnota varianty*</label>

                        </div>
                        <div class="col-sm-1" style="padding: 0;">
                            <i class="remove_specifi_vari entypo-trash" style="float: left; padding: 7px 8px; cursor: pointer;"></i>
                        </div>
                    </div>

                <?php } ?>
                <button type="button" id="duplicate_specifi_vari" style="float: left;width: 100%;" class="duplicate_specifi_vari btn btn-default btn-icon icon-left">
                    Přidat další specifikaci
                    <i class="entypo-plus"></i>
                </button>

            </div>


            <div class="col-sm-3" style="padding: 0;">

                <?php


                $path = PRODUCT_IMAGE_PATH . '/thumbnail/' . $product['seourl'] . '_variation_' . $variation['id'] . '.jpg';
                if (file_exists($path)) {

                    $imagePath = '/data/stores/images/thumbnail/' . $product['seourl'] . '_variation_' . $variation['id'] . '.jpg';
                    ?>
                    <div class="fileinput fileinput-exists" data-provides="fileinput" data-name="variation_picture[<?= $i ?>]" style="text-align: center; width: 100%;">
                        <div class="fileinput-new thumbnail" style="width: 100px; height: 100px;" data-trigger="fileinput">
                            <img src="/data/assets/no-image-7.jpg" alt="..." width="100">
                        </div>
                        <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 180px; max-height: 180px">
                            <img src="<?= $imagePath ?>" width="80">
                        </div>
                        <div>
									<span class="btn btn-info btn-file" style="padding: 4px 8px; font-size: 11px; margin-top: 5px;">
										<span class="fileinput-new">Vybrat obrázek</span>
										<span class="fileinput-exists">Změnit</span>
										<input type="file" accept="image/*">
									</span>
                            <a href="#" class="btn btn-danger fileinput-exists" data-dismiss="fileinput" style="padding: 4px 8px; font-size: 11px; margin-top: 5px;">Odstranit</a>
                        </div>
                    </div>

                <?php } else { ?>

                    <div class="fileinput fileinput-new" data-provides="fileinput" style="text-align: center; width: 100%;">
                        <div class="fileinput-new thumbnail" style="width: 100px; height: 100px;" data-trigger="fileinput">
                            <img src="/data/assets/no-image-7.jpg" alt="..." width="100">
                        </div>
                        <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 100px; max-height: 100px"></div>
                        <div>
									<span class="btn btn-info btn-file" style="padding: 4px 8px; font-size: 11px; margin-top: 5px;">
										<span class="fileinput-new">Vybrat obrázek</span>
										<span class="fileinput-exists">Změnit</span>
										<input type="file" name="variation_picture[<?= $i ?>]" accept="image/*">
									</span>
                            <a href="#" class="btn btn-danger fileinput-exists" data-dismiss="fileinput" style="padding: 4px 8px; font-size: 11px; margin-top: 5px;">Odstranit</a>
                        </div>
                    </div>

                <?php } ?>

            </div>


            <input type="text" class="form-control" id="copy_this_hidus_cislus" name="copythis" value="<?= $i ?>" style="display: none;">
            <input type="text" class="form-control" id="variation_id" name="item[<?= $i ?>][variation_id]" value="<?php isset_echo($variation['id']); ?>" style="display: none;">


            <hr>

            <div class="form-group" style="margin-top: 24px; margin-bottom: 6px;">
                <div class="col-sm-9" style="padding-left: 2px;">
                    <label for="item-<?= $i ?>-instock" class="radio-new">
                        <input id="item-<?= $i ?>-instock" type="radio" name="item[<?= $i ?>][availability]" value="0" <?php if (isset($variation['availability']) && $variation['availability'] == 0) {echo 'checked';}?>>
                        <label for="item-<?= $i ?>-instock" >Skladem</label>
                    </label>
                    <label for="item-<?= $i ?>-on-order" class="radio-new">
                        <input id="item-<?= $i ?>-on-order" type="radio" name="item[<?= $i ?>][availability]" value="2" <?php if (isset($variation['availability']) && $variation['availability'] == 2) {echo 'checked';}?>>
                        <label for="item-<?= $i ?>-on-order">Na objednávku</label>
                    </label>
                    <label for="item-<?= $i ?>-hidden" class="radio-new">
                        <input id="item-<?= $i ?>-hidden" type="radio" name="item[<?= $i ?>][availability]" value="3" <?php if (isset($variation['availability']) && $variation['availability'] == 3) {echo 'checked';}?>>
                        <label for="item-<?= $i ?>-hidden">Skryto</label>

                    </label>
                    <label for="item-<?= $i ?>-unavailable" class="radio-new">
                        <input id="item-<?= $i ?>-unavailable" type="radio" name="item[<?= $i ?>][availability]" value="4" <?php if (isset($variation['availability']) && $variation['availability'] == 4) {echo 'checked';}?>>
                        <label for="item-<?= $i ?>-unavailable">Nedostupné</label>

                    </label>
                </div>

            </div>

            <hr>

            <div class="col-sm-3 tooltip-primary form-label-group" data-toggle="tooltip" data-placement="top" title="" data-original-title="SKU" style="margin-bottom: 8px; padding: 0 !important;">
                <input type="text" class="form-control sku" id="variation_sku" name="item[<?= $i ?>][variation_sku]" value="<?php isset_echo($variation['sku']); ?>">
                <label>Kód SKU*</label>
            </div>

            <div class="col-sm-3 form-label-group" style="float: left;">
                <input type="text" class="form-control ean" id="variation_ean" name="item[<?= $i ?>][variation_ean]" value="<?php isset_echo($variation['ean']); ?>" placeholder="EAN kód*">
                <label>EAN kód*</label>
            </div>

            <div class="col-sm-3 form-label-group" style="margin-bottom: 8px;  padding: 0 0 0 10px;">
                <input type="text" data-mask="decimal" class="form-control" id="variation_price" name="item[<?= $i ?>][variation_price]" value="<?php isset_echo($data['price']); ?>">
                <label>Cena</label>

            </div>

            <div class="col-sm-12" style="float: left;"><hr></div>

            <?php
            $locations_query = $mysqli->query("SELECT * FROM shops_locations l LEFT JOIN products_stocks s ON s.location_id = l.id AND s.product_id = '" . $product['id'] . "' AND s.variation_id = '" . $variation['id'] . "' ORDER BY type ASC");

            $reserved_quantity = 0;
            while ($location = mysqli_fetch_array($locations_query)) {
                ?>
                <div class="col-sm-3 form-label-group <?php if ($location['instock'] != "") { ?> tooltip-primary" data-toggle="tooltip" data-placement="top" title="" data-original-title="Skladem<?php } ?>" style="margin-bottom: 0;  padding: 0 0px 0 10px;">
                    <input type="text" data-mask="decimal" class="form-control" id="fff" name="item[<?= $i ?>][current_stock_<?= $location['id'] ?>]" value="<?= $location['instock'] ?>" style="display: none;">
                    <label><?= $location['name'] ?></label>

                    <input type="text" data-mask="decimal" class="form-control" id="variation_stock" name="item[<?= $i ?>][new_stock_<?= $location['id'] ?>]" value="<?= $location['instock'] ?>">

                </div>
            <?php } ?>


            <hr>

            <div class="form-group form-label-group" style="float: left; padding-left: 16px !important;">

                <?php

                mysqli_data_seek($locations_query, 0);

                while ($location = mysqli_fetch_array($locations_query)) {


                    ?>
                    <div class="col-lg-3 col-sm-3 has-metric">
                        <input id="min_stock-<?= $location['id'] ?>" type="number" class="form-control" name="item[<?= $i ?>][min_stock_location_<?= $location['id'] ?>]" value="<?= $location['min_stock'] ?>" placeholder="Minimální Σ <?= $location['name'] ?>">
                        <label for="min_stock-<?= $location['id'] ?>">Min. Σ ~ <?= $location['name'] ?></label>
                        <span class="input-group-addon">Ks</span>
                    </div>

                <?php } ?>

            </div>

            <hr>

            <div class="col-sm-3 form-label-group <?php if ($purchase_price != "") { ?> tooltip-primary" data-toggle="tooltip" data-placement="top" title="" data-original-title="Nákupní cena<?php } ?>" style="margin-bottom: 8px; padding: 0 0px 0 10px;">
                <input type="text" data-mask="decimal" class="form-control" id="variation_purchase_price" name="item[<?= $i ?>][variation_purchase_price]" value="<?= $purchase_price ?>">
                <label>Nákupní cena</label>
            </div>

            <div class="col-sm-3 form-label-group <?php if ($wholesale_price != "") { ?> tooltip-primary" data-toggle="tooltip" data-placement="top" title="" data-original-title="Velkoobchodní cena<?php } ?>" style="margin-bottom: 8px; padding: 0 0px 0 10px;">
                <input type="text" data-mask="decimal" class="form-control" id="variation_wholesale_price" name="item[<?= $i ?>][variation_wholesale_price]" value="<?= $wholesale_price ?>">
                <label>Velkoobchodní</label>
            </div>

            <div class="col-sm-3 form-label-group <?php if ($sale_price != "") { ?> tooltip-primary" data-toggle="tooltip" data-placement="top" title="" data-original-title="Zlevněná cena<?php } ?>" style="margin-bottom: 8px; padding: 0 0px 0 10px;">

                <input type="text" data-mask="decimal" class="form-control" id="variation_sale_price" name="item[<?= $i ?>][variation_sale_price]" value="<?= $sale_price ?>">
                <label>Zlevněná cena</label>
            </div>


            <hr>


            <div class="col-sm-3 form-label-group" style="padding-left: 0; padding-right: 8px;">
                <input type="text" class="form-control" style="float:left;" id="variation_weight" name="item[<?= $i ?>][variation_weight]" value="<?= $weight ?>">
                <label>Váha (kg)</label>
            </div>

            <div class="col-sm-3 form-label-group" style="padding: 0 8px;">
                <input type="text" class="form-control" style="float:left;" id="variation_length" name="item[<?= $i ?>][variation_length]" value="<?= $length ?>">
                <label>Délka/Hloubka (cm)</label>
            </div>
            <div class="col-sm-3 form-label-group" style="padding: 0 8px;">
                <input type="text" class="form-control" style="float:left;" id="variation_width" name="item[<?= $i ?>][variation_width]" value="<?= $width ?>">
                <label>Šířka (cm)</label>
            </div>
            <div class="col-sm-3 form-label-group" style="padding-right: 0; padding-left: 8px;">
                <input type="text" class="form-control" style="float:left;" id="variation_height" name="item[<?= $i ?>][variation_height]" value="<?= $height ?>">
                <label>Výška (cm)</label>
            </div>

        </div>

        <hr>


        <div class="col-sm-12 form-label-group" style="margin-bottom: 8px; padding: 0; float: left;">
            <input type="text" class="form-control" id="variation_description" name="item[<?= $i ?>][variation_description]" value="<?php isset_echo($variation['description']); ?>">
            <label>Doplňující popisek varianty</label>
        </div>

    </div>

</div>