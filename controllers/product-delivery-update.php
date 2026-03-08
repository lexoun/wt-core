<?php
$searchquery = $mysqli->query("SELECT s.id as supply_id, supply.id as sbridge_id, supply.quantity, supply.reserved, p.price, p.productname, p.delivery_time, p.id as id, cat.discount, p.purchase_price, p.ean FROM products p, products_cats cat, products_sites_categories minicat, products_supply_bridge supply, products_supply s WHERE supply.product_id = p.id AND supply.reserved <> supply.quantity AND s.location_id = '2' AND supply.supply_id = s.id AND s.status < 3 AND minicat.category = cat.seoslug AND p.code = '$posterino'") or die($mysqli->error);

if (mysqli_num_rows($searchquery) > 0) {

    $search = mysqli_fetch_array($searchquery);
    $search['vid'] = '0';

} else {

    $searchquery = $mysqli->query("SELECT s.id as supply_id, supply.id as sbridge_id, supply.quantity, supply.reserved, v.price, p.productname, p.delivery_time, p.id as id, v.id as vid, cat.discount, v.purchase_price, v.ean FROM products p, products_variations v, products_cats cat, products_sites_categories minicat, products_supply_bridge supply, products_supply s WHERE supply.product_id = p.id AND s.location_id = '2' AND supply.variation_id = v.id AND minicat.category = cat.seoslug AND minicat.product_id = p.id AND v.product_id = p.id AND supply.supply_id = s.id AND s.status < 3 AND v.sku = '$posterino'") or die($mysqli->error);

    $search = mysqli_fetch_array($searchquery);

}

if (!empty($search) && $search['supply_id'] != '') {

    $remaining = $search['quantity'] - $search['reserved'];

    if ($quantity > $remaining) {

        $reserve = $remaining;

    } else {

        $reserve = $quantity;

    }

    $mysqli->query("UPDATE products_supply_bridge SET reserved = reserved + $reserve WHERE id = '" . $search['sbridge_id'] . "'") or die($mysqli->error);

    $mysqli->query("UPDATE $bridge SET delivered = delivered + $reserve WHERE product_id = '" . $search['id'] . "' AND variation_id = '" . $search['vid'] . "' AND $id_identify = '" . $id . "'") or die($mysqli->error);

    if (!isset($total_quantity) || $total_quantity == '') {$total_quantity = $quantity;}

    // update / insert .... supply_types_bridge....

    $check_bridge = $mysqli->query("SELECT id FROM supply_types_bridge WHERE product_id = '" . $search['id'] . "' AND variation_id = '" . $search['vid'] . "' AND type_id = '" . $id . "' AND type = '" . $type . "'") or die($mysqli->error);

    if (mysqli_num_rows($check_bridge) != 0) {

        $mysqli->query("UPDATE supply_types_bridge SET quantity = quantity + $reserve WHERE product_id = '" . $search['id'] . "' AND variation_id = '" . $search['vid'] . "' AND type_id = '" . $id . "' AND type = '" . $type . "'") or die($mysqli->error);

    } else {

        $mysqli->query("INSERT INTO supply_types_bridge (type_id, product_id, variation_id, supply_id, quantity, type) VALUES ('$id', '" . $search['id'] . "', '" . $search['vid'] . "', '" . $search['supply_id'] . "', '$reserve', '" . $type . "')") or die($mysqli->error);
    }

}
