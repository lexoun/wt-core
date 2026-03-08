<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$pagetitle = "Kritické příslušenství";


$clientquery = $mysqli->query('SELECT * FROM demands WHERE email="' . $_COOKIE['cookie_email'] . '"') or die($mysqli->error);
$client = mysqli_fetch_assoc($clientquery);

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "remove") {


    $unlinkquery = $mysqli->query('SELECT seourl, ean FROM products WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);
    $unlink = mysqli_fetch_assoc($unlinkquery);

    // added
    foreach($productImageSizes as $imageSize){

        $path = PRODUCT_IMAGE_PATH.'/'.$imageSize.'/'.$product['seourl'].'.jpg';
        if(file_exists($path)){ unlink($path); };

        unset($path);
    }

    $images = glob($_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/small/' . $unlink['seourl'] . '_{,[1-9]}{,[1-9]}[0-9].jpg', GLOB_BRACE);
    if (!empty($images)) {

        foreach ($images as $image) {

            $imageName = basename($image);

            foreach($productImageSizes as $imageSize){

                $path = PRODUCT_IMAGE_PATH.'/'.$imageSize.'/'.$imageName;
                if(file_exists($path)){ unlink($path); };
                unset($path);

            }

        }

    }

    $images = glob($_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/small/' . $unlink['seourl'] . '_variation_{,[1-9]}{,[1-9]}[0-9].jpg', GLOB_BRACE);
    if (!empty($images)) {

        foreach ($images as $image) {

            $imageName = basename($image);

            foreach($productImageSizes as $imageSize){

                $path = PRODUCT_IMAGE_PATH.'/'.$imageSize.'/'.$imageName;
                if(file_exists($path)){ unlink($path); };
                unset($path);

            }

        }

    }
    // added


    $mysqli->query('DELETE FROM products WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);
    $mysqli->query('DELETE FROM products_categories WHERE productid="' . $_REQUEST['id'] . '"') or die($mysqli->error);

    $select_variations = $mysqli->query("SELECT id FROM products_variations WHERE product_id = '" . $_REQUEST['id'] . "'")or die($mysqli->error);
    while ($variation = mysqli_fetch_array($select_variations)) {

        $mysqli->query('DELETE FROM products_variations_values WHERE variation_id="' . $variation['id'] . '"') or die($mysqli->error);

    }

    $mysqli->query('DELETE FROM products_variations WHERE product_id="' . $_REQUEST['id'] . '"') or die($mysqli->error);
    $mysqli->query("DELETE FROM products_specifications WHERE product_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);
    $mysqli->query("DELETE FROM products_sites_categories WHERE product_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

    $mysqli->query("DELETE FROM products_sites WHERE product_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

    // todo remove from eshop webhook
    //api_product_remove($_REQUEST['id'], '');

    if (isset($_REQUEST['link'])) {

        header('location: https://' . $_SERVER['SERVER_NAME'] . '/admin/pages/accessories/editace-prislusenstvi?od=' . $_REQUEST['link'] . '&success=remove');
        exit;
    } else {

        header('location: https://' . $_SERVER['SERVER_NAME'] . '/admin/pages/accessories/editace-prislusenstvi?success=remove');
        exit;
    }

}

include VIEW . '/default/header.php';




        $perpage = 25;

        if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {$od = 1;}
        $s_lol = $od - 1;
        $s_pocet = $s_lol * $perpage;

        $products_max_query = $mysqli->query("SELECT * FROM products p, demands_products d WHERE p.id = d.product_id GROUP BY p.id") or die($mysqli->error);
        $products_max = mysqli_fetch_array($products_max_query);


        $max = mysqli_num_rows($products_max_query);


        if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {$od = 1;}

        $s_lol = $od - 1;
        $s_pocet = $s_lol * $perpage;
        $pocet_prispevku = $max;

//        $productsquery = $mysqli->query("SELECT *, p.id as id FROM products p, demands_products d WHERE p.id = d.product_id GROUP BY p.id order by p.id desc") or die($mysqli->error);


        $productsquery = $mysqli->query("SELECT p.*, p.id as id, d.variation_id as vid FROM products p, demands_products d WHERE p.id = d.product_id GROUP BY p.id, d.variation_id order by p.id desc") or die($mysqli->error);

//        echo mysqli_num_rows($productsquery);

        ?>
            <div class="row">
                <div class="col-md-4 col-sm-4">
                    <h2><?= $pagetitle ?></h2>
                </div>

                <div class="col-md-4">

                </div>

                <div class="col-md-4 col-sm-5">


                </div>
            </div>

            <!-- Footer -->
        <?php

        if (mysqli_num_rows($productsquery) > 0) {
            ?>

        <style>
            .table-hover td { vertical-align: middle !important; }
        </style>
            <table class="table table-bordered table-hover ">
                <thead>
                <tr>
                    <td width="26" class="text-center">-</td>
                    <td class="text-center">Položka</td>

                    <?php
                    $locations_query = $mysqli->query("SELECT name FROM shops_locations WHERE type = 'warehouse'")or die($mysqli->error);

                    while($location = mysqli_fetch_assoc($locations_query)){

                        ?>
                        <td class="text-center"><?= $location['name'] ?></td>

                        <?php


                    }

                    ?>
                    <td class="text-center">Na cestě</td>
                    <td class="text-center">Tento měsíc</td>
                    <td class="text-center">Příští měsíc</td>
                    <td width="140" class="text-center">Akce</td>
                </tr>
                </thead>
                <tbody>
            <?php
            $max = 0;

            $products_list = '';
            while ($product = mysqli_fetch_assoc($productsquery)) {

                $showProduct = false;

                // selecting only products used for multiple hottubs
//                $get_count_products = $mysqli->query("SELECT id FROM demands_products WHERE product_id = '".$product['id']."'") or die($mysqli->error);
//
//                if(mysqli_num_rows($get_count_products) > 0){




                    $desc = '';
                    if($product['type'] == 'variable'){

                        $desc = ' - ';

                        $select = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $product['vid'] . "'") or die($mysqli->error);
                        while ($var = mysqli_fetch_array($select)) {

                            $desc .= $var['name'] . ': ' . $var['value'] . ' ';

                        }

                    }


                    $path = $_SERVER['DOCUMENT_ROOT'] . '/data/stores/images/small/' . $product['seourl'] . '.jpg';
                    if(file_exists($path)){
                        $imagePath = '/data/stores/images/small/'.$product['seourl'].'.jpg';
                    }else{
                        $imagePath = '/data/assets/no-image-7.jpg';
                    }



                    $current_product = '';

                    $current_product .=  '<tr>
                        <td class="text-center">	<a href="./zobrazit-prislusenstvi?id='.$product['id'].'" class="member-img">
                                <img src="'.$imagePath.'" width="26" style=" border: 1px solid #ebebeb;">
            </a></td>
            <td><a href="./zobrazit-prislusenstvi?id='.$product['id'].'">'.$product['productname'].$desc.'</a></td>';

                        $locations_stock_query = $mysqli->query("SELECT s.* FROM shops_locations l, products_stocks s WHERE s.location_id = l.id AND l.type = 'warehouse' AND s.product_id = '".$product['id']."' AND s.variation_id = '".$product['vid']."'")or die($mysqli->error);

                        while($location_stock = mysqli_fetch_assoc($locations_stock_query)){


                            $stock = 0;
                            if($location_stock['instock'] > 0) {

                                $stock = '<span class="btn btn-success btn-sm">
                               '.$location_stock['instock'].'
                            </span>';

                                $showProduct = true;

                            }


                            $current_product .= ' <td class="text-center">'.$stock.'</td>';


                        }





                        $supply_query = $mysqli->query("SELECT SUM(r.quantity) as total FROM products_supply_bridge r, products_supply o WHERE o.id = r.supply_id AND r.product_id = '" . $product['id'] . "' AND r.variation_id = '" . $product['vid'] . "' AND o.status < 3") or die($mysqli->error);


                        $supply = mysqli_fetch_assoc($supply_query);

                    $supply_info = '-';

                    if($supply['total'] > 0){

                        $showProduct = true;

                        $supply_info = '<span class="btn btn-blue btn-sm">
                                           '.$supply['total'].'
                                        </span>';

                    }


                    $current_product .= ' <td class="text-center">'.$supply_info.'</td>';




                            $find_query = $mysqli->query("SELECT spec_id FROM demands_products WHERE product_id = '".$product['id']."' AND variation_id = '".$product['vid']."' LIMIT 1")or die($mysqli->error);

                            if(mysqli_num_rows($find_query) > 0){

                                $find = mysqli_fetch_assoc($find_query);


                                $get_spec = $mysqli->query("SELECT id, type FROM specs WHERE id = '".$find['spec_id']."'")or die($mysqli->error);
                                $spec = mysqli_fetch_assoc($get_spec);



                                $select_containers = $mysqli->query("SELECT id, date_due FROM containers WHERE closed = 2 AND date_due <= DATE_ADD(NOW(), INTERVAL 2 MONTH) ORDER BY date_due ASC")or die($mysqli->error);

                                $total_count = 0;
                                $total_next = 0;
                                while($container = mysqli_fetch_assoc($select_containers)){


                                    if($product['container_essential'] == 1){

                                        $get_products = $mysqli->query("SELECT id FROM containers_products WHERE container_id = '".$container['id']."'")or die($mysqli->error);

                                        $count = mysqli_num_rows($get_products);

                                    }else{


                                        $count = 0;
                                        $countNext = 0;

                                        $get_products = $mysqli->query("SELECT id, product, demand_id FROM containers_products WHERE container_id = '".$container['id']."' AND demand_id != 0")or die($mysqli->error);

                                        while($container_product = mysqli_fetch_assoc($get_products)){

                                            if($spec['type'] == 0){

                                                $get_accessory_info = $mysqli->query("SELECT param_id FROM demands_products WHERE type = '".$container_product['product']."' AND product_id = '".$product['id']."' AND variation_id = '".$product['vid']."'")or die($mysqli->error);

                                                $accessory = mysqli_fetch_assoc($get_accessory_info);

                                                if($accessory['param_id'] == 1){

                                                    $param_value = 'Ano';

                                                }else{

                                                    $param_value = 'Ne';

                                                }


                                            }elseif($spec['type'] == 1){

                                                $get_accessory_info = $mysqli->query("SELECT option FROM demands_products p, specs_params s WHERE s.id = p.param_id AND s.spec_id = p.spec_id AND p.product_id = '".$product['id']."' AND p.variation_id = '".$product['vid']."' AND p.type = '".$container_product['product']."'")or die($mysqli->error);

                                                // get desired value of spec
                                                $accessory = mysqli_fetch_assoc($get_accessory_info);
                                                $param_value = $accessory['option'];

                                            }


                                            $different_spec_query = $mysqli->query("SELECT b.value as demand_value, c.value as container_value FROM demands_specs_bridge b, containers_products_specs_bridge c WHERE c.client_id = '".$container_product['id']."' AND b.client_id = '".$container_product['demand_id']."' AND b.specs_id = '".$spec['id']."' AND c.specs_id = b.specs_id AND b.value != c.value AND b.value != '' AND b.value = '".$param_value."'")or die($mysqli->error);

                                            if(mysqli_num_rows($different_spec_query) > 0){

                                                $month = date('m', strtotime($container['date_due']));
                                                $current_month = date('m');

                                                if($month == $current_month)
                                                {

                                                    $count++;

                                                }else{

                                                    $countNext++;

                                                }

                                            }

                                        }

                                    }

                                    $total_count += $count;
                                    $total_next += $countNext;

                                    $max++;

                                }



                                $thisMonth = '-';

                                if($total_count > 0) {

                                    $showProduct = true;

                                    $thisMonth = '<span class="btn btn-danger btn-sm">' . $total_count . ' </span>';

                                }

                                $current_product .= '<td class="text-center">'.$thisMonth.'</td>';





                                $nextMonth = '-';

                                if($total_next > 0) {

                                    $showProduct = true;

                                    $nextMonth = '<span class="btn btn-danger btn-sm">' . $total_next . ' </span>';

                                }

                                $current_product .= '<td class="text-center">'.$nextMonth.'</td>';


                            }





                    $current_product .= '<td><a href="/admin/pages/accessories/zobrazit-prislusenstvi?id='.$product['id'].'" class="btn btn-default btn-sm">
                                <i class="entypo-search"></i>
                            </a>

                            <a href="/admin/pages/accessories/upravit-prislusenstvi?id='.$product['id'].'" class="btn btn-primary btn-sm">
                                <i class="entypo-pencil"></i>
                            </a>

                            <a data-id="'.$product['id'].'" class="toggle-modal-stock btn btn-blue btn-sm">
                                <i class="entypo-box"></i>
                            </a>

                    </tr>';


                            ?>



                    <?php


//                }


                if($showProduct){ $products_list .= $current_product; }



            }

            echo $products_list;

//            echo $max;

        } else { ?>
            <ul class="cbp_tmtimeline" style=" margin-left: 25px;">
                <li style="margin-top: 80px;">

                    <div class="cbp_tmicon">
                        <i class="entypo-block" style="line-height: 42px !important;"></i>
                    </div>

                    <div class="cbp_tmlabel empty" style="padding-top: 9px;">
                        <span><a style="font-weight: bold; margin-left: -12px;font-size: 17px;">Bohužel tomuto filtru neodpovídá žádný klient.</a></span>
                    </div>
                </li>
            </ul>
            <?php
        }

        ?>
            </table>

        <footer class="main">


            &copy; <?= date("Y") ?> <span style=" float:right;"><?php
                $time = microtime();
                $time = explode(' ', $time);
                $time = $time[1] + $time[0];
                $finish = $time;
                $total_time = round(($finish - $start), 4);

                echo 'PHP '.PHP_VERSION.' | Page generated in ' . $total_time . ' seconds.';?></span>

        </footer>	</div>




    <script type="text/javascript">
        $(document).ready(function(){
            $(".toggle-modal-remove").click(function(e){

                $('#remove-modal').removeData('bs.modal');
                e.preventDefault();


                var type = $(this).data("type");

                var id = $(this).data("id");

                $("#remove-modal").modal({

                    remote: '/admin/controllers/modals/modal-remove.php?id='+id+'&type='+type+'&od=<?= $od ?>',
                });
            });
        });
    </script>


    <div class="modal fade" id="remove-modal" aria-hidden="true" style="display: none; margin-top: 10%;">

    </div>





    <script type="text/javascript">
        $(document).ready(function(){
            $(".toggle-modal-stock").click(function(e){

                $('#stock-modal').removeData('bs.modal');
                e.preventDefault();


                var id = $(this).data("id");

                $("#stock-modal").modal({

                    remote: '/admin/controllers/modals/modal-stock-data.php?id='+id+'&od=<?= $od ?>',
                });
            });
        });
    </script>


    <div class="modal fade" id="stock-modal" aria-hidden="true" style="display: none; margin-top: 8%;">


    </div>


</div>

<?php include VIEW . '/default/footer.php'; ?>


