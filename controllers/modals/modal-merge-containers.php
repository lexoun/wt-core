<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";

$id = $_REQUEST['id'];

$container_query = $mysqli->query("SELECT id FROM containers WHERE id = '$id'");

$container = mysqli_fetch_array($container_query);

$text = 'Opravdu chcete sloučit kontejner číslo <strong>#' . $container['id'] . '</strong>? Zachová se pouze tento kontejner - druhý bude smazán a položky přesunuty.';

$remove_button = 'Sloučit';

$title = 'Smazání kontejneru #' . $container['id'];

?>



	<div class="modal-dialog">
	<form role="form" method="post" action="?action=merge&id=<?= $container['id'] ?>" enctype="multipart/form-data">
	<div class="modal-content">
			<div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

				<h4 class="modal-title"><?= $title ?></h4> </div>

			<div class="modal-body" style="padding: 36px 35px 20px 35px; text-align: center;">

					<p><?= $text ?></p>


					<div class="form-group">
            <label for="field-2" class="col-sm-4 control-label"><strong>Zvolte kontejner</strong></label>

            <div class="col-sm-6">
                <select id="optionus" name="container" class="form-control">
                  <?php $containers_query = $mysqli->query("SELECT *, DATE_FORMAT(date_created, '%d. %m. %Y') as date_formatted FROM containers WHERE closed = '0' AND size = 7 AND id != '$id' order by id desc") or die($mysqli->error);
while ($containers = mysqli_fetch_array($containers_query)) {

    $total_products_query = $mysqli->query("SELECT count(*) as total FROM containers_products WHERE container_id = '" . $containers['id'] . "'") or die($mysqli->error);
    $total_products = mysqli_fetch_array($total_products_query);

    ?>
                   <option value="<?= $containers['id'] ?>">Kontejner #<?= $containers['id'] ?> - <?= $containers['date_formatted'] ?> [<?= $total_products['total'] ?>/<?= $containers['size'] ?>]</option>

                   <?php } ?>
                </select>

              </div>
          </div>

			</div>

<div class="modal-footer" style="text-align:left;"> <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>

	<div style="float: right;"><button type="submit" class="btn btn-primary btn-icon icon-left"><?= $remove_button ?>
					<i class="entypo-resize-small"></i></button></div>

	</div>
