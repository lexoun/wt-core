<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";

$id = $_REQUEST['id'];
$virivka['connect_name'] = $_REQUEST['connect_name'];

$cpars_query = $mysqli->prepare('SELECT * FROM containers_products_specs_bridge WHERE specs_id = ? and client_id = ?');
$spec_id = null;
$product_id = null;
$cpars_query->bind_param('ii', $spec_id, $product_id);

$params_query = $mysqli->prepare('SELECT *, p.id as id FROM specs_params p, warehouse_products_types_specs w WHERE p.spec_id = ? AND w.spec_param_id = p.id AND w.type_id = ? GROUP by p.id');
$spec_id = null;
$type_id = null;
$params_query->bind_param('ii', $spec_id, $type_id);

$get_id_query = $mysqli->query("SELECT id FROM warehouse_products WHERE connect_name = '" . $virivka['connect_name'] . "'") or die($mysqli->error);
$get_id = mysqli_fetch_array($get_id_query);

$virivky_typy = $mysqli->query("SELECT * FROM warehouse_products_types WHERE warehouse_product_id = '" . $get_id['id'] . "'") or die($mysqli->error);
while ($typ = mysqli_fetch_array($virivky_typy)) { ?>

			<div class="params_virivky params_virivky_<?= $virivka['connect_name'] ?> params_<?= $typ['seo_url'] ?>_<?= $virivka['connect_name'] ?>" <?php if (!isset($param_type['value']) || $param_type['value'] != $typ['name']) { ?>style="display: none;"<?php } ?>>
    <div class="col-sm-6">

    <?php

    $specs_query = $mysqli->query("SELECT *, s.id as id, c.value FROM specs s, warehouse_products_types_specs w, containers_products_specs_bridge c WHERE c.specs_id = s.id AND c.client_id = '" . $_REQUEST['id'] . "' AND w.spec_id = s.id AND w.type_id = '" . $typ['id'] . "' AND s.supplier = 1 GROUP BY s.id ORDER BY s.rank asc") or die($mysqli->error);

    $total_specs = mysqli_num_rows($specs_query);


    $i = -1;

    while ($specs = mysqli_fetch_array($specs_query)) {


        $i++;
        if($i == round(($total_specs / 2))){ echo '</div><div class="col-sm-6">'; }

        $spec_id = $specs['id'];
        $product_id = $id;

        $cpars_query->execute() or die($mysqli->error);
        $result = $cpars_query->get_result();
        $cpars = $result->fetch_array();

        // VALUE U POPTÁVKY K DANÉ SPECIFIKACI

        if (isset($specs['type']) && $specs['type'] == 1) {

            $spec_id = $specs['id'];
            $type_id = $typ['id'];

            $params_query->execute() or die($mysqli->error);
            $result = $params_query->get_result();

            ?><div class="form-group">
							<label class="col-sm-4 control-label"><?= $specs['name'] ?></label>
							<div class="col-sm-7">
								<select name="<?= $virivka['connect_name'] ?>_<?= $typ['seo_url'] ?>_<?= $specs['seoslug'] ?>" class="form-control">
									<?php
            $selected = false;
            $options = '';

            while ($params = $result->fetch_array()) {

                $selected_echo = "";

                // když uložené provedení u poptávky == právě řešené provedení
                if (isset($provedeni['id']) && $provedeni['id'] == $params['type_id']) {

                    if (isset($cpars['value']) && $cpars['value'] == $params['option'] && isset($param_type['value']) && $param_type['value'] == $typ['name']) {

                        $selected_echo = 'selected';
                        $selected = true;

                    }

                } elseif ($provedeni['id'] != $params['type_id']) {

                    if (isset($params['choosed']) && $params['choosed'] == 1 && $cpars['value'] != "unknown") {

                        $selected_echo = 'selected';
                        $selected = true;

                    }

                }

                $options = $options . '<option value="' . $params['option'] . '" ' . $selected_echo . '>' . $params['option'] . '</option>';

            }?>
									<option value="" <?php if ($selected != true) {echo 'selected';}?>>Žádná vybraná možnost</option>
									<?= $options ?>
								</select>
							</div>
						</div><?php

        } else {

            $paramsquery = $mysqli->query("SELECT * FROM warehouse_products_types_specs WHERE spec_id = '" . $specs['id'] . "' AND type_id = '" . $typ['id'] . "' order by spec_param_id desc") or die($mysqli->error);

            ?><div class="form-group">
						<label class="col-sm-4 control-label"><?= $specs['name'] ?></label>
						<div class="col-sm-7"><?php

            $selected = false;
            while ($params = mysqli_fetch_array($paramsquery)) {

                if (isset($params['spec_param_id']) && $params['spec_param_id'] == 1) {$value = 'Ano';} else { $value = 'Ne';}

                ?><div class="radio" style="width: 100px; float: left;text-align: left;">
								<label>
									<input class="generate_radio" id="price_<?= $specs['seoslug'] ?>" type="radio" name="<?= $virivka['connect_name'] ?>_<?= $typ['seo_url'] ?>_<?= $specs['seoslug'] ?>" value="<?= $value ?>" <?php if (($cpars['value'] == $value && isset($param_type['value']) && $param_type['value'] == $typ['name']) || ($params['choosed'] == 1 && $cpars['value'] != "unknown" && !$selected)) {$selected = true;
                    echo 'checked';}?> style=" height: 20px;"><?= $value ?>
								</label>
							</div><?php

            }?>
						</div>
					</div>
					<?php }
    }?>
			</div>
            </div>

		<?php } ?>