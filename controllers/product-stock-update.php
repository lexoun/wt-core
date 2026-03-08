<?php

include_once CONTROLLERS . "/product-stock-controller.php";

/*
 * Example data:
 *
 * $stock_allocation['posterino'] = $posterino;
 * $stock_allocation['bridge'] = 'orders_products_bridge';
 * $stock_allocation['id_identify'] = 'order_id';
 * $stock_allocation['quantity'] = $_POST['product_quantity'][$post_index];
 * $stock_allocation['product_discount'] = $_POST['product_discount'][$post_index];
 * $stock_allocation['pricerino'] = $_POST['product_price'][$post_index];
 * $stock_allocation['original_price'] = $_POST['product_original_price'][$post_index];
 * $stock_allocation['location'] = $_POST['location'];
 * $stock_allocation['new_vat'] = $_POST['vat'];
 * $stock_allocation['old_vat'] = $_POST['vat'];
 * $stock_allocation['type'] = 'order';
 * $stock_allocation['quantity'] = $quantity;
 * $stock_allocation['total_quantity'] = $quantity;
 */
function stock_allocate($stock_allocation)
{

    global $mysqli;

    $bridge = $stock_allocation['bridge'];

    $variation_values = '';

    $searchquery = $mysqli->query("SELECT
       stock.instock, p.price, p.productname, p.delivery_time, p.id as id, p.purchase_price, p.ean 
        FROM products p 
        LEFT JOIN products_stocks stock ON stock.product_id = p.id AND stock.location_id = '" . $stock_allocation['location'] . "'
        WHERE p.code = '" . $stock_allocation['posterino'] . "'") or die($mysqli->error);

    if (mysqli_num_rows($searchquery) > 0) {

        $search = mysqli_fetch_array($searchquery);
        $search['vid'] = '0';

    } else {

        $searchquery = $mysqli->query("SELECT 
            stock.instock, v.price, p.productname, p.delivery_time, p.id as id, v.id as vid, v.purchase_price, v.ean 
        FROM (products p, products_variations v) 
        LEFT JOIN products_stocks stock ON stock.product_id = p.id 
                                               AND stock.location_id = '" . $stock_allocation['location'] . "' 
                                               AND stock.variation_id = v.id 
        WHERE v.product_id = p.id AND v.sku = '" . $stock_allocation['posterino'] . "'") or die($mysqli->error);
        $search = mysqli_fetch_array($searchquery);

        $variation_query = $mysqli->query("SELECT name, value FROM products_variations_values 
            WHERE variation_id = '" . $search['vid'] . "'");
        while ($variation = mysqli_fetch_array($variation_query)) {

            if (empty($variation_values)) {
                $variation_values = $variation['name'] . ': ' . $variation['value'];
            } else {
                $variation_values .= ', ' . $variation['name'] . ': ' . $variation['value'];
            }
        }

        $variation_values = $mysqli->real_escape_string($variation_values);

    }

    if ($stock_allocation['quantity'] > $search['instock']) {

        $reserve = $search['instock'];

    } else {

        $reserve = $stock_allocation['quantity'];

    }

    $mysqli->query("UPDATE products_stocks SET instock = instock - $reserve 
        WHERE product_id = '" . $search['id'] . "' AND variation_id = '" . $search['vid'] . "' 
            AND location_id IN (
                SELECT id as location_id 
                FROM shops_locations WHERE id = '" . $stock_allocation['location'] . "'
                )") or die($mysqli->error);


    if ($reserve != 0) {

        // insert into log
        $mysqli->query("INSERT INTO history_products (
                              product_id, 
                              variation_id, 
                              datetime, 
                              type, 
                              value, 
                              final_stock, 
                              admin_id, 
                              location_id) VALUES (
                              '" . $search['id'] . "', 
                              '" . $search['vid'] . "', 
                              now(), 
                              'product_edit', 
                              '$reserve', 
                              '" . $stock_allocation['quantity'] . "', 
                              'system', 
                              '" . $stock_allocation['location'] . "')") or die($mysqli->error);

    }

    api_product_update($stock_allocation['id']);


    $check_bridge = $mysqli->query("SELECT id FROM $bridge WHERE product_id = '" . $search['id'] . "' AND variation_id = '" . $search['vid'] . "' AND aggregate_id = '" . $stock_allocation['id'] . "' AND aggregate_type = '".$stock_allocation['type']."'") or die($mysqli->error);


    $product_name = $mysqli->real_escape_string($search['productname']);


    if (mysqli_num_rows($check_bridge) != 0) {

        $mysqli->query("UPDATE $bridge 
            SET 
                reserved = reserved + $reserve, 
                quantity = '" . $stock_allocation['quantity'] . "', 
                price = '" . $stock_allocation['price']['price'] . "', 
                discount_net = '" . $stock_allocation['price']['discount_net'] . "', 
                original_price = '" . $stock_allocation['price']['original_price'] . "', 
                location_id = '" . $stock_allocation['location'] . "', 
                product_name = '" . $product_name . "', 
                variation_values = '" . $variation_values . "', 
                discount = '" . $stock_allocation['price']['discount'] . "' 
          WHERE product_id = '" . $search['id'] . "' AND variation_id = '" . $search['vid'] . "' AND aggregate_id = '" . $stock_allocation['id'] . "' AND aggregate_type = '".$stock_allocation['type']."'
          ") or die($mysqli->error);

    } else {

        $mysqli->query("INSERT INTO $bridge (
                   aggregate_id,
                   aggregate_type,
                   product_id, 
                   variation_id, 
                   product_name, 
                   variation_values, 
                   quantity, 
                   reserved, 
                   price, 
                   discount_net, 
                   original_price, 
                   purchase_price, 
                   location_id, 
                   discount
               ) VALUES (
                 '" . $stock_allocation['id'] . "', 
                 '" . $stock_allocation['type'] . "', 
                 '" . $search['id'] . "', 
                 '" . $search['vid'] . "', 
                 '" . $product_name . "',
                 '" . $variation_values . "', 
                 '" . $stock_allocation['quantity'] . "', 
                 '" . $reserve . "', 
                 '" . $stock_allocation['price']['price'] . "', 
                 '" . $stock_allocation['price']['discount_net'] . "', 
                 '" . $stock_allocation['price']['original_price'] . "', 
                 '" . $search['purchase_price'] . "', 
                 '" . $stock_allocation['location'] . "', 
                 '" . $stock_allocation['price']['discount'] . "'
                 )") or die($mysqli->error);

    }


    $response = [];
    $response['total_quantity'] = $stock_allocation['total_quantity'];
    if (!isset($stock_allocation['total_quantity']) || $stock_allocation['total_quantity'] == '') {
        $response['total_quantity'] = $stock_allocation['quantity'];
    }

    $response['reserve'] = $reserve;

    return $response;

}