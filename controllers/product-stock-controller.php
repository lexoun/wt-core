<?php
include_once INCLUDES . "/accessories-functions.php";

// dodávky
function product_delivered_update($id, $var_id = 0, $initial_quantity, $type, $type_id)
{

    global $mysqli;

    // LOOP V SUPPLY
    // vezme všechny uložené produkty u dané objednávky/servisu atd.
    $supply_query = $mysqli->query("SELECT *, s.id as supply_id, sb.id as supply_bridge_id, b.quantity as bridge_quantity, sb.reserved as supply_reserved, b.id as type_id FROM products_supply s, supply_types_bridge b, products_supply_bridge sb WHERE b.product_id = '" . $id . "' AND b.variation_id = '" . $var_id . "' AND b.type_id = '" . $type_id . "' AND b.type = '" . $type . "' AND s.id = b.supply_id AND sb.supply_id = s.id ORDER BY s.id ASC") or die($mysqli->error);

    while ($supply = mysqli_fetch_array($supply_query)) {

        // $quantity = počet k přerozdělení

        if ($initial_quantity > $supply['bridge_quantity'] || $initial_quantity == $supply['bridge_quantity']) {

            $quantity = $supply['bridge_quantity'];
            $initial_quantity = 0;

            $mysqli->query("DELETE FROM supply_types_bridge WHERE id = '" . $supply['type_id'] . "'") or die($mysqli->error);

        } else {

            $quantity = $initial_quantity;
            $initial_quantity = $initial_quantity - $supply['bridge_quantity'];

            // update supply types bridge

        }


        // přidá to ty věci jen k objednávkám zatím?
        // ORDER START --- ORDER START --- ORDER START --- ORDER START --- ORDER START

        $search1 = $mysqli->query("SELECT b.id, b.aggregate_id, b.reserved, b.delivered, b.quantity FROM orders_products_bridge b, orders o WHERE b.product_id = '$id' and b.variation_id = '$var_id' and b.aggregate_id = o.id and o.order_status < 3 and (b.reserved + b.delivered) < b.quantity AND o.id != '" . $type_id . "' AND b.aggregate_type = 'order' order by o.id asc") or die($mysqli->error);
        while ($search = mysqli_fetch_array($search1)) {

            $rozdil = $search['quantity'] - ($search['reserved'] + $search['delivered']);

            if ($rozdil > $quantity || $rozdil == $quantity) {

                $add = $quantity;
                $quantity = 0;

            } else {

                $add = $rozdil;
                $quantity = $quantity - $rozdil;

            }

            $mysqli->query("UPDATE orders_products_bridge SET delivered = delivered + $add WHERE id = '" . $search['id'] . "'") or die($mysqli->error);

            $lookup_orders = $mysqli->query("SELECT * FROM supply_types_bridge WHERE type_id = '" . $search['order_id'] . "' AND product_id = '" . $id . "' AND variation_id = '" . $var_id . "' AND supply_id = '" . $supply['supply_id'] . "' AND type = 'order'") or die($mysqli->error);

            if (mysqli_num_rows($lookup_orders) > 0) {

                $mysqli->query("UPDATE supply_types_bridge SET quantity = quantity + $add WHERE type_id = '" . $search['order_id'] . "' AND product_id = '" . $id . "' AND variation_id = '" . $var_id . "' AND supply_id = '" . $supply['supply_id'] . "' AND type = 'order'") or die($mysqli->error);

            } else {

                $mysqli->query("INSERT INTO supply_types_bridge (type_id, product_id, variation_id, supply_id, quantity, type) VALUES ('" . $search['order_id'] . "', '" . $id . "', '" . $var_id . "', '" . $supply['supply_id'] . "', '" . $add . "', 'order')") or die($mysqli->error);

            }

            if ($quantity == 0) {break;}

        }

        // ORDER END --- ORDER END --- ORDER END --- ORDER END --- ORDER END


        // todo tady by měli být services a demands



        $mysqli->query("UPDATE products_supply_bridge SET reserved = reserved - $quantity WHERE id = '" . $supply['supply_bridge_id'] . "'") or die($mysqli->error);

        if ($initial_quantity == 0) {break;}

    }

}


// dodávky
function product_delivered_single($id, $var_id = 0, $initial_quantity, $supply_id)
{

    global $mysqli;

    // $quantity = počet k přerozdělení

    $quantity = $initial_quantity;

    // ORDER START --- ORDER START --- ORDER START --- ORDER START --- ORDER START

    $search1 = $mysqli->query("
    SELECT b.id, b.aggregate_id, b.reserved, b.delivered, b.quantity
        FROM orders_products_bridge b,
             orders o
        WHERE b.product_id = '$id'
          and b.variation_id = '$var_id'
          and b.aggregate_id = o.id
          and o.order_status < 3
          and (b.reserved + b.delivered) < b.quantity
          AND b.aggregate_type = 'order' 
        order by o.id asc
    ") or die($mysqli->error);
    while ($search = mysqli_fetch_array($search1)) {

        $rozdil = $search['quantity'] - ($search['reserved'] + $search['delivered']);

        if ($rozdil > $quantity || $rozdil == $quantity) {

            $add = $quantity;
            $quantity = 0;

        } else {

            $add = $rozdil;
            $quantity = $quantity - $rozdil;

        }

        $mysqli->query("UPDATE orders_products_bridge SET delivered = delivered + $add WHERE id = '" . $search['id'] . "'") or die($mysqli->error);

        $lookup_orders = $mysqli->query("SELECT * 
            FROM supply_types_bridge 
            WHERE type_id = '" . $search['order_id'] . "' AND product_id = '" . $id . "' AND variation_id = '" . $var_id . "' AND supply_id = '" . $supply_id . "' AND type = 'order'") or die($mysqli->error);

        if (mysqli_num_rows($lookup_orders) > 0) {

            $mysqli->query("UPDATE supply_types_bridge SET quantity = quantity + $add WHERE type_id = '" . $search['order_id'] . "' AND product_id = '" . $id . "' AND variation_id = '" . $var_id . "' AND supply_id = '" . $supply_id . "' AND type = 'order'") or die($mysqli->error);

        } else {

            $mysqli->query("INSERT INTO supply_types_bridge (type_id, product_id, variation_id, supply_id, quantity, type) VALUES ('" . $search['order_id'] . "', '" . $id . "', '" . $var_id . "', '" . $supply_id . "', '" . $add . "', 'order')") or die($mysqli->error);

        }

        if ($quantity == 0) {break;}

    }

    // ORDER END --- ORDER END --- ORDER END --- ORDER END --- ORDER END


    // todo tady by měli být services a demands




    $mysqli->query("UPDATE products_supply_bridge SET reserved = reserved - $quantity WHERE product_id = '" . $id . "' AND variation_id = '" . $var_id . "' AND supply_id = '" . $supply_id . "'") or die($mysqli->error);

}

function product_update($id, $var_id = 0, $location, $quantity, $admin_id, $type, $target_id)
{

    global $mysqli;

    $initial_quantity = $quantity;

    // update or create
    $exists_query = $mysqli->query("SELECT instock, location_id FROM products_stocks WHERE product_id = '$id' AND variation_id = '$var_id' AND location_id IN (SELECT id as location_id FROM shops_locations WHERE id = '$location')") or die($mysqli->error);

    if(mysqli_num_rows($exists_query) == 0){

        $mysqli->query("INSERT INTO products_stocks 
            (instock, product_id, variation_id, location_id)
            VALUES
            ('".$quantity."', '".$id."', '".$var_id."','".$location."')
        ") or die($mysqli->error);

    }else{

        $mysqli->query("UPDATE products_stocks SET instock = instock + $quantity WHERE product_id = '$id' AND variation_id = '$var_id' AND location_id IN (SELECT id as location_id FROM shops_locations WHERE id = '$location')") or die($mysqli->error);

    }


    $get_instock = $mysqli->query("SELECT instock, location_id FROM products_stocks WHERE product_id = '$id' AND variation_id = '$var_id' AND location_id IN (SELECT id as location_id FROM shops_locations WHERE id = '$location')") or die($mysqli->error);

    $instock = mysqli_fetch_array($get_instock);

    $quantity = $instock['instock'];


    // DEMAND START --- DEMAND START --- DEMAND START --- DEMAND START --- DEMAND START
    $demand_query = $mysqli->query("SELECT * FROM demands_products_bridge WHERE product_id = '" . $id . "' AND variation_id = '" . $var_id . "' AND type = 'missing' AND warehouse_id != '" . $target_id . "'") or die($mysqli->error);

    while ($demand = mysqli_fetch_array($demand_query)) {

        $rozdil = 1;

        if ($rozdil > $quantity || $rozdil == $quantity) {

            $add = $quantity;
            $quantity = 0;

        } else {

            $add = $rozdil;
            $quantity = $quantity - $rozdil;

        }

        if ($add > 0) {

            $mysqli->query("UPDATE demands_products_bridge SET type = 'warehouse' WHERE id = '" . $demand['id'] . "'") or die($mysqli->error);

        }

    }
    // DEMAND END --- DEMAND END --- DEMAND END --- DEMAND END --- DEMAND END


    // SERVICE START --- SERVICE START --- SERVICE START --- SERVICE START --- SERVICE START
    $service_query = $mysqli->query("SELECT b.id, b.aggregate_id, b.reserved, b.quantity FROM services_products_bridge b, services s WHERE b.product_id = '$id' and b.variation_id = '$var_id' and b.aggregate_id = s.id and s.status < 3 and (b.reserved + b.delivered) < b.quantity AND b.location_id = '" . $instock['location_id'] . "' AND s.id != '$target_id'  order by s.id asc") or die($mysqli->error);
    while ($service = mysqli_fetch_array($service_query)) {

        $rozdil = $service['quantity'] - ($service['reserved'] + $service['delivered']);

        if ($rozdil > $quantity || $rozdil == $quantity) {

            $add = $quantity;
            $quantity = 0;

        } else {

            $add = $rozdil;
            $quantity = $quantity - $rozdil;

        }

        $mysqli->query("UPDATE services_products_bridge SET reserved = reserved + $add WHERE id = '" . $service['id'] . "'") or die($mysqli->error);

    }
    // SERVICE END --- SERVICE END --- SERVICE END --- SERVICE END --- SERVICE END


    // ORDER START --- ORDER START --- ORDER START --- ORDER START --- ORDER START
    $search1 = $mysqli->query("SELECT b.id, b.aggregate_id, b.reserved, b.quantity 
        FROM orders_products_bridge b, orders o 
        WHERE b.product_id = '$id' and b.variation_id = '$var_id' and b.aggregate_id = o.id and o.order_status < 3 and (b.reserved + b.delivered) < b.quantity AND b.location_id = '" . $instock['location_id'] . "' AND o.id != '$target_id' AND b.aggregate_type = 'order' order by o.id") or die($mysqli->error);
    while ($search = mysqli_fetch_array($search1)) {

        $rozdil = $search['quantity'] - ($search['reserved'] + $search['delivered']);

        if ($rozdil > $quantity || $rozdil == $quantity) {

            $add = $quantity;
            $quantity = 0;

        } else {

            $add = $rozdil;
            $quantity = $quantity - $rozdil;

        }

        $mysqli->query("UPDATE orders_products_bridge 
            SET reserved = reserved + $add 
            WHERE id = '" . $search['id'] . "'") or die($mysqli->error);

    }
    // ORDER END --- ORDER END --- ORDER END --- ORDER END --- ORDER END

    $mysqli->query("UPDATE products_stocks SET instock = '$quantity' 
        WHERE product_id = '$id' AND variation_id = '$var_id' AND 
            location_id IN (SELECT id as location_id FROM shops_locations WHERE id = '$location')
    ") or die($mysqli->error);


    // todo only if quantity is changed
    // plan only stock sync on shops
    foreach(getProductSites($id) as $site){

        saveProductEvent($id, 'product', 'quantity', $site['site']);

    }


    // LOG UPDATE
    if($initial_quantity != 0) {

        // insert into log
        $mysqli->query("INSERT INTO history_products (product_id, variation_id, datetime, type, value, final_stock, admin_id, location_id) VALUES ('" . $id . "', '" . $var_id . "', now(), 'product_edit', '$initial_quantity', '$quantity', 'system', '$location')") or die($mysqli->error);

    }

}



