<?php
if (isset($_REQUEST['action']) && $_REQUEST['action'] == "remove") {

    $selectq = $mysqli->query("SELECT order_status FROM orders WHERE id = '" . $_REQUEST['id'] . "'");
    $select = mysqli_fetch_array($selectq);
    if ($select['status'] < 3) {

        $topupquery = $mysqli->query("SELECT b.product_id, b.reserved, p.customer 
            FROM orders_products_bridge b, products p 
            WHERE p.id = b.product_id AND b.aggregate_id = '" . $_REQUEST['id'] . "' AND b.aggregate_type = 'order'") or die($mysqli->error);
        while ($topup = mysqli_fetch_array($topupquery)) {

            $instock = $topup['reserved'];
            if (isset($product['customer']) && $product['customer'] == 0) {
                $search1 = $mysqli->query("SELECT s.id, s.reserved, s.quantity FROM demands_sauna_specs s, demands d WHERE s.product_id = '" . $_REQUEST['id'] . "' and s.demand_id = d.id and d.status < 5 order by d.id asc");
                while ($search = mysqli_fetch_array($search1)) {

                    $rozdil = $search['quantity'] - $search['reserved'];

                    if ($rozdil >= $instock) {

                        $update = $mysqli->query("UPDATE demands_sauna_specs SET reserved = reserved + $instock WHERE id = '" . $search['id'] . "'");

                        $instock = 0;

                    } else {

                        $update = $mysqli->query("UPDATE demands_sauna_specs SET reserved = reserved + $rozdil WHERE id = '" . $search['id'] . "'");

                        $instock = $instock - $rozdil;

                    }

                }}
            $updateres = $instock;
            if ($updateres > 0) {

                $search1 = $mysqli->query("SELECT b.id, b.reserved, b.quantity FROM orders_products_bridge b, orders o WHERE b.product_id = '" . $topup['product_id'] . "' AND b.order_id != '" . $_REQUEST['id'] . "' and b.order_id = o.id and o.order_status < 4 and b.reserved < b.quantity order by o.id desc");
                if (mysqli_num_rows($search1) > 0) {
                    while ($search = mysqli_fetch_array($search1)) {

                        $rozdil = $search['quantity'] - $search['reserved'];

                        if ($rozdil > $updateres || $rozdil == $updateres) {

                            $update = $mysqli->query("UPDATE orders_products_bridge SET reserved = reserved + $updateres WHERE id = '" . $search['id'] . "'");

                            $updateres = 0;

                        } else {

                            $update = $mysqli->query("UPDATE orders_products_bridge SET reserved = reserved + $rozdil WHERE id = '" . $search['id'] . "'");

                            $updateres = $updateres - $rozdil;

                        }

                    }
                }

            }

            $update = $mysqli->query("UPDATE products SET instock = instock + $updateres WHERE id = '" . $topup['product_id'] . "'");

        }

    }

    $mysqli->query("DELETE FROM orders WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);
    $mysqli->query("DELETE FROM orders_products_bridge WHERE aggregate_id = '" . $_REQUEST['id'] . "' AND aggregate_type = 'order'") or die($mysqli->error);
    $displaysuccess = true;
    $successhlaska = "Objednávka byla úspěšně smazána.";

    if (isset($select['order_status']) && $select['order_status'] == 0) {
        $link = 'nezpracovane';
    } elseif (isset($select['order_status']) && $select['order_status'] == 1) {
        $link = 'prijate';
    } elseif (isset($select['order_status']) && $select['order_status'] == 2) {
        $link = 'pripravene';
    } elseif (isset($select['order_status']) && $select['order_status'] == 3) {
        $link = 'vyexpedovane';
    } else { $link = 'stornovane';}

    header('location: https://www.wellnesstrade.cz/admin/' . $link . '-objednavky?success=remove');
    exit;
}
