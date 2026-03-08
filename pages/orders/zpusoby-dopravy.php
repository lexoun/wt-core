<?php
include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

use Automattic\WooCommerce\Client;


if(!empty($_REQUEST['action']) && $_REQUEST['action'] == 'edit' && !empty($_REQUEST['id'])){


    // todo ikonky doručovacích služeb


    $delivery_method_query = $mysqli->query("SELECT * FROM shops_delivery_methods WHERE id = '".$_REQUEST['id']."'")or die($mysqli->error);
    $delivery_method = mysqli_fetch_assoc($delivery_method_query);

    $mysqli->query("UPDATE shops_delivery_methods SET price = '".$_POST['price']."' WHERE id = '".$_REQUEST['id']."'")or die($mysqli->error);

    if($delivery_method['country'] == 'CZ'){
        $zone_id = 1;
    }elseif($delivery_method['country'] == 'SK'){
        $zone_id = 2;
        // EU
    }else{
        $zone_id = 0;
    }


    $shops_query = $mysqli->query("SELECT * FROM shops") or die($mysqli->error);
    while($shop = mysqli_fetch_assoc($shops_query)){

        $woocommerce = new Client(
            $shop['url'],
            $shop['secret_key'],
            $shop['secret_code'],
            [
                'wp_api' => true,
                'version' => 'wc/v3',
                'query_string_auth' => true,

            ]
        );


        // Get instance_id for selected e-shop
        $shippingMethods = json_decode($shop['shipping_methods'], true);

        $finalData = $shippingMethods[$delivery_method['country']];

        foreach($finalData as $single) {

            if($single['id'] == $delivery_method['id']){

                $method_values = $single;
                break;
            }

        }

        $data = [
            'settings' => [
                'cost' => $_POST['price']
            ]
        ];


        $woocommerce->put('shipping/zones/'.$zone_id.'/methods/'.$method_values['instance_id'], $data);

    }


    header('Location: https://www.wellnesstrade.cz/admin/pages/orders/zpusoby-dopravy');
    exit;
}

$categorytitle = "Objednávky";
$pagetitle = "Způsoby dopravy";

include VIEW . '/default/header.php';
?>

<div class="row">
    <div class="col-md-9 col-sm-7">
        <h2><?= $pagetitle ?></h2>
    </div>

    <div class="col-md-3 col-sm-5" style="text-align: right;float:right;">

    </div>
</div>



<table class="table table-bordered">
    <thead>
    <tr>
        <th style="vertical-align: middle;text-align: center;">Název</th>
        <th style="vertical-align: middle;text-align: center;">Zkrácený zápis</th>
        <th style="vertical-align: middle;text-align: center;">Přepravce</th>
        <th style="vertical-align: middle;text-align: center;">Zóna</th>
        <th style="vertical-align: middle;text-align: center;">Cena</th>
        <th style="vertical-align: middle;text-align: center;">Akce</th>
    </tr>
    </thead>
    <tbody>
<?php


$delivery_method_query = $mysqli->query("SELECT * FROM shops_delivery_methods ORDER BY country")or die($mysqli->error);
while($method = mysqli_fetch_assoc($delivery_method_query)){


    // todo ikonky doručovacích služeb
    ?>
    <tr>
        <td><?= $method['name'] ?></td>
        <td><?= $method['link_name'] ?></td>
        <td><?= $method['transporter_company'] ?></td>
        <td><?= $method['country'] ?></td>
        <td><?= $method['price'] ?></td>
        <td style="text-align: center;"><a data-id="<?= $method['id'] ?>"
               class="toggle-modal-edit btn btn-blue btn-sm">
                Upravit
            </a></td>
    </tr>


    <?php

}


?>
    </tbody>
</table>



<script type="text/javascript">
    $(document).ready(function(){
        $(".toggle-modal-edit").click(function(e){

            $('#edit-modal').removeData('bs.modal');
            e.preventDefault();

            var id = $(this).data("id");

            $("#edit-modal").modal({

                remote: '/admin/controllers/modals/modal-edit-delivery-method.php?id='+id,
            });
        });
    });
</script>


<div class="modal fade" id="edit-modal" aria-hidden="true" style="display: none; margin-top: 3%;">

</div>


<!-- Footer -->
<footer class="main">


    &copy; <?= date("Y") ?> <span style=" float:right;"><?php
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $finish = $time;
        $total_time = round(($finish - $start), 4);

        echo 'PHP '.PHP_VERSION.' | Page generated in ' . $total_time . ' seconds.';?></span>

</footer>	</div>


</div>


<?php include VIEW . '/default/footer.php'; ?>



