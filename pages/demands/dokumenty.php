<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

if (isset($_REQUEST['od'])) {$od = $_REQUEST['od'];}

if (isset($_REQUEST['customer'])) {$customer = $_REQUEST['customer'];}

$pagetitle = "Prodejní dokumenty";

$bread1 = "Poptávky";

include VIEW . '/default/header.php';

?>

<div class="row" style="margin-bottom: 16px;">
    <div class="col-md-8 col-sm-7">
        <h2 style="float: left"><?= $pagetitle ?></h2>
    </div>
    <div class="col-md-4 col-sm-5" style="text-align: right;float:right;">
        <a href="<?= $home ?>/admin/data/demands/documents/Všeobecné obchodní podmínky společnosti Wellness Trade_v.pdf?t=<?= $currentDate->getTimestamp() ?>"
           target="_blank"
           style="margin-bottom: 12px; margin-right: 4px; font-size: 14px; padding: 16px 34px 16px 80px; "
           class="btn btn-primary btn-icon icon-left btn-lg">
            <i class="entypo-doc-text-inv"
               style="line-height: 24px;font-size: 14px; padding: 12px 16px;"></i>
            Obchodní podmínky
        </a>
        <a href="<?= $home ?>/admin/data/demands/documents/Všeobecné obchodní podmínky společnosti Wellness Trade_swim.pdf?t=<?= $currentDate->getTimestamp() ?>"
           target="_blank"
           style="margin-bottom: 12px; margin-right: 4px; font-size: 14px; padding: 16px 34px 16px 80px; "
           class="btn btn-primary btn-icon icon-left btn-lg">
            <i class="entypo-doc-text-inv"
               style="line-height: 24px;font-size: 14px; padding: 12px 16px;"></i>
            Obchodní podmínky SWIM
        </a>
    </div>
</div>

<style>
    .row.special { margin:0;padding: 10px 0 0; border-bottom: 1px solid #eaeaea; }
    .row.special:hover { background-color: #F9F9F9;}
</style>
<?php

$warehousequery = $mysqli->query('SELECT * FROM warehouse_products WHERE customer = 1 AND brand != "" ORDER BY brand asc, rank asc, code asc') or die($mysqli->error);

while ($product = mysqli_fetch_array($warehousequery)) {

    ?>
    <div class="row special">

        <div class="col-sm-4 col-md-2">
            <h3 style="float: left; font-size: 16px; line-height: 18px;"><?= $product['brand'].' '.$product['fullname'] ?></h3>
        </div>
    <?php

    $select_variations = $mysqli->query("SELECT id, name FROM warehouse_products_types WHERE warehouse_product_id = '" . $product['id'] . "' ORDER BY name DESC");
    while ($type = mysqli_fetch_array($select_variations)) { ?>
        <div class="col-sm-2" style="width: 14%;">
                <?php

                $file_url = '/admin/data/demands/documents/' . returnpn($product['customer'], $product['connect_name']) . ' ' . $type['name'] . ' - Stavební příprava.pdf';

                if (file_exists($_SERVER['DOCUMENT_ROOT'] . $file_url)) {

                    ?>

                <a href="<?= $file_url ?>?t=<?= $currentDate->getTimestamp() ?>" target="_blank">
                     <div class="tile-stats tile-green" style="padding: 14px 20px;">
                         <div class="icon"><i class="entypo-doc-text-inv"></i></div>
                        <h3 style="font-size: 15px; line-height: 20px; margin-top: 0;"><?= $type['name'] ?></h3>
                     </div>
                </a>

                <?php } else { ?>
                    <div class="tile-stats tile-white" style="padding: 14px 20px;">
                        <div class="icon"><i class="entypo-cancel-circled"></i></div>
                        <h3 style="font-size: 15px; line-height: 20px; margin-top: 0;"><?= $type['name'] ?> - chybí</h3>
                    </div>
                <?php } ?>

        </div>
        <?php }?>
    </div>
<?php } ?>
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
<script src="<?= $home ?>/admin/assets/js/jquery.validate.min.js"></script>
<?php include VIEW . '/default/footer.php'; ?>