<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";

$id = $_REQUEST['id'];

$container_query = $mysqli->query("SELECT id, container_name FROM containers WHERE id = '$id'");
$container = mysqli_fetch_array($container_query);

$text = 'Přiřadit kódy';

$remove_button = 'Přiřadit kódy a vytvořit vířivky';

$title = 'Přiřadit kódy k vířivkám v kontejneru #' . $container['id'];

?>

	<div class="modal-dialog" style="width: 800px;">
	<form role="form" method="post" action="?action=add_codes&id=<?= $id ?>" enctype="multipart/form-data">
	<div class="modal-content">
			<div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

				<h4 class="modal-title"><?= $title ?></h4> </div>

			<div class="modal-body" style="padding: 20px 35px 20px 35px; text-align: center;">

			<div class="form-group" style="float: left; width: 100%; border-bottom: 1px solid #ebebeb;">
				<label class="col-sm-4 control-label">Sklad</label>
				<div class="col-sm-7">
						<?php
$warehouse_query = $mysqli->query("SELECT * FROM shops_locations WHERE type = 'warehouse'") or die($mysqli->error);
while ($warehouse = mysqli_fetch_array($warehouse_query)) {
    ?>
						<div class="radio" style="margin-left: 30px; margin-top: 0; float: left;">
							<label>
								<input type="radio" name="location_id" value="<?= $warehouse['id'] ?>" <?php if ($warehouse['id'] == 1) {echo 'checked';}?>><?= $warehouse['name'] ?>
							</label>
						</div>
						<?php } ?>
				</div>

			</div>

		 <?php $container_products = $mysqli->query("SELECT *, DATE_FORMAT(date_created, '%d. %m. %Y') as dateformated FROM containers_products WHERE container_id = '$id' ORDER BY id ASC") or die($mysqli->error);
while ($cont_product = mysqli_fetch_array($container_products)) { ?>

					<div class="form-group">
            <label for="field-2" class="col-sm-4 control-label" style="margin-top: 6px; text-align: right; color: #222"><strong>#<?= $cont_product['id'] ?> - <?= ucfirst($cont_product['product']) ?></strong></label>

            <div class="col-sm-4" style="margin-bottom: 6px;">



				<input type="text" name="value-<?= $cont_product['id'] ?>" class="form-control" placeholder="Sériové číslo"

                value="<?php if(!empty($container['container_name'])){ echo $container['container_name'].'-'; }?>">


              </div>
              <div class="col-sm-4" style="margin-bottom: 6px;">


				<input type="number" name="price-<?= $cont_product['id'] ?>" class="form-control" placeholder="Pořizovací cena">


              </div>
          </div>
            <?php } ?>

			</div>
			<div style="clear:both;"></div>

<div class="modal-footer" style="text-align:left;"> <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>

	<div style="float: right;"><button type="submit" class="btn btn-primary btn-icon icon-left"><?= $remove_button ?>
					<i class="fa fa-barcode"></i></button></div>

	</div>
