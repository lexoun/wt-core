<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";

$id = $_REQUEST['id'];
//
//$container_query = $mysqli->query("SELECT container_id FROM containers_products WHERE id = '$id'");
//$container = mysqli_fetch_array($container_query);

$container_query = $mysqli->query("SELECT c.closed, c.id, p.warehouse_id, c.id_brand, c.brand FROM containers c, containers_products p WHERE c.id = p.container_id AND p.id = '".$id."'")or die($mysqli->error);
$container = mysqli_fetch_assoc($container_query);


$warehouse_query = $mysqli->query("SELECT serial_number FROM warehouse WHERE id = '".$container['warehouse_id']."'")or die($mysqli->error);
$warehouse = mysqli_fetch_assoc($warehouse_query);


$text = 'Opravdu přesunout položku číslo <strong>#' . $id . '</strong>?';

$remove_button = 'Přesunout';

$title = 'Přesunutí položky #' . $id;

?>



	<div class="modal-dialog">
	<form role="form" method="post" action="?action=transfer&id=<?= $id ?>" enctype="multipart/form-data">
	<div class="modal-content">
			<div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

				<h4 class="modal-title"><?= $title ?></h4> </div>

			<div class="modal-body" style="padding: 36px 35px 20px 35px; text-align: center;">

<!--					<p>--><?//echo $text; ?><!--</p>-->

        <div class="form-group">
            <label for="field-2" class="col-sm-3 control-label"><strong>Zvolte kontejner</strong></label>

            <div class="col-sm-9">
                <select id="optionus" name="container" class="form-control">
                    <option value="new">Založit nový kontejner</option>
                    <?php

                    if($container['closed'] > 1){

                        $containers_query = $mysqli->query("SELECT *, DATE_FORMAT(date_created, '%d. %m. %Y') as date_formatted FROM containers WHERE closed != '3' AND brand = '".$container['brand']."' AND closed > 1 order by id desc") or die($mysqli->error);

                    }else{

                        $containers_query = $mysqli->query("SELECT *, DATE_FORMAT(date_created, '%d. %m. %Y') as date_formatted FROM containers WHERE closed < '2' AND brand = '".$container['brand']."' order by id desc") or die($mysqli->error);

                    }




while ($containers = mysqli_fetch_array($containers_query)) {

    $total_products_query = $mysqli->query("SELECT count(*) as total FROM containers_products WHERE container_id = '" . $containers['id'] . "'") or die($mysqli->error);
    $total_products = mysqli_fetch_array($total_products_query);

    if ($total_products['total'] < $containers['size'] || $containers['id'] == $container['id']) {

        ?>
                   <option value="<?= $containers['id'] ?>" <?php if($containers['id'] == $container['id']){ echo 'selected'; }?>>Kontejner #<?php
                       if(!empty($containers['container_name'])) {

                           echo $containers['container_name'];

                       }else{

                           echo $containers['id_brand'];

                       }

                       ?> - <?= $containers['date_formatted'] ?> [<?= $total_products['total'] ?>/<?= $containers['size'] ?>]</option>

                   <?php }} ?>
                </select>

              </div>
          </div>

                <div style="clear:both;"></div>

                <?php if(!empty($container['closed']) && $container['closed'] > 1){ ?>
                    <hr>
                    <div class="form-group" style="float: left;width: 100%;">
                        <div class="col-sm-12">

                            <label class="col-sm-5 control-label">Sériové číslo</label>

                            <div class="col-sm-7">
                                <input type="text" name="serial_number" class="form-control" value="<?= $warehouse['serial_number'] ?>">
                            </div>

                        </div>
                    </div>
                <?php } ?>

                <div style="clear:both;"></div>


            </div>

<div class="modal-footer" style="text-align:left;"> <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>

	<div style="float: right;"><button type="submit" class="btn btn-primary btn-icon icon-left"><?= $remove_button ?>
					<i class="entypo-forward"></i></button></div>

	</div>
