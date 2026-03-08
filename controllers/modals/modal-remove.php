<?php

include_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";

$id = $_REQUEST['id'];

$od = $_REQUEST['od'];

if (isset($_REQUEST['type']) && $_REQUEST['type'] == "order") {

    $order_query = $mysqli->query("SELECT id FROM orders WHERE id = '$id'");

    $order = mysqli_fetch_array($order_query);

    $link = '?action=remove&id=' . $order['id'] . '&link=' . $od;

    $text = $order['id'];

    $remove_button = 'Smazat objednávku';

    $title = 'Smazání objednávky č. ' . $order['id'];

} elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == "product") {

    $product_query = $mysqli->query("SELECT id, productname FROM products WHERE id = '$id'");

    $product = mysqli_fetch_array($product_query);

    $link = '?action=remove&id=' . $product['id'] . '&link=' . $od;

    $text = $product['productname'];

    $remove_button = 'Smazat produkt';

    $title = 'Smazání produktu ' . $product['productname'];

} elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == "hottub") {

    $hottub_query = $mysqli->query("SELECT w.id, p.brand, p.fullname FROM warehouse w, warehouse_products p WHERE p.connect_name = w.product AND w.id = '$id'");

    $hottub = mysqli_fetch_array($hottub_query);

    $link = '?action=remove&id=' . $hottub['id'];

    $text = 'Opravdu chcete navždy smazat vířivku <strong>#' . $hottub['id'] . ' | ' . $sauna['brand'] . ' ' . $sauna['fullname'] . '</strong>?';

    $remove_button = 'Smazat vířivku';

    $title = 'Smazání vířivky #' . $hottub['id'] . ' | ' . $sauna['brand'] . ' ' . $sauna['fullname'];

} elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == "sauna") {

    $sauna_query = $mysqli->query("SELECT w.id, p.code, p.brand, p.fullname FROM warehouse w, warehouse_products p WHERE p.connect_name = w.product AND w.id = '$id'");

    $sauna = mysqli_fetch_array($sauna_query);

    $link = '?action=remove&id=' . $sauna['id'];

    $text = 'Opravdu chcete navždy smazat saunu <strong>#' . $sauna['id'] . ' | ' . $sauna['code'] . ' - ' . $sauna['brand'] . ' ' . $sauna['fullname'] . '</strong>?';

    $remove_button = 'Smazat saunu';

    $title = 'Smazání sauny #' . $sauna['id'] . ' | ' . $sauna['code'] . ' - ' . $sauna['brand'] . ' ' . $sauna['fullname'];

} elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == "service") {

    $service_query = $mysqli->query("SELECT id FROM services WHERE id = '$id'");

    $service = mysqli_fetch_array($service_query);

    $link = '?action=remove&id=' . $service['id'] . '&send_mail=no" style="margin-right: 8px"><button type="submit" class="btn btn-green btn-icon icon-left">Smazat a neposílat mail
					<i class="entypo-trash"></i></button></a><a href="?action=remove&id=' . $service['id'] . '&send_mail=yes';

    $text = 'Opravdu chcete navždy smazat servis číslo <strong>' . $service['id'] . '</strong>?';

    $remove_button = 'Smazat a poslat mail';

    $title = 'Smazání servisu ' . $service['id'];

} elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == "container") {

    $container_query = $mysqli->query("SELECT id FROM containers WHERE id = '$id'");

    $container = mysqli_fetch_array($container_query);

    $link = '?action=remove&id=' . $container['id'];

    $text = 'Opravdu chcete navždy smazat kontejner číslo <strong>#' . $container['id'] . '</strong>? Budou zároveň odstraněny i všechny přidělené položky.';

    $remove_button = 'Smazat';

    $title = 'Smazání kontejneru #' . $container['id'];

} elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == "container_product") {

    $container_product_query = $mysqli->query("SELECT id FROM containers_products WHERE id = '$id'");

    $container_product = mysqli_fetch_array($container_product_query);

    $link = '?action=remove_product&id=' . $container_product['id'];

    $text = 'Opravdu chcete navždy smazat přiřazenou položku číslo <strong>#' . $container_product['id'] . '</strong>?';

    $remove_button = 'Smazat';

    $title = 'Smazání kontejneru #' . $container_product['id'];

} elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == "demand") {

    $demand_query = $mysqli->query("SELECT id FROM demands WHERE id = '$id'");
    $demand = mysqli_fetch_array($demand_query);

    $link = '?action=remove&id=' . $demand['id'];

    $text = 'Opravdu chcete navždy smazat poptávku číslo <strong>#' . $demand['id'] . '</strong>?';

    $remove_button = 'Smazat';

    $title = 'Smazání poptávky #' . $demand['id'];

}

?>
<div class="modal-dialog">
	<div class="modal-content">
			<div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title"><?= $title ?></h4> </div>
			<div class="modal-body" style="padding: 36px 35px 20px 35px; text-align: center;">
					<?= $text ?>
			</div>

    <div class="modal-footer" style="text-align:left;">
        <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>

        <div style="float: right;"><a href="<?= $link ?>"><button type="submit" class="btn btn-red btn-icon icon-left"><?= $remove_button ?>
                        <i class="entypo-trash"></i></button></a></div>

        </div>
    </div>
</div>
