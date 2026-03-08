<?php


include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";

if (isset($_REQUEST['type']) && $_REQUEST['type'] == 'closure') {

    $closures_query = $mysqli->query("SELECT closure FROM shops_locations_closures WHERE shop_slug = '" . $_REQUEST['shop'] . "' AND location_slug = '" . $_REQUEST['location'] . "'") or die($mysqli->error);
    $closure = mysqli_fetch_array($closures_query);

    if (isset($closure['closure']) && $closure['closure'] != "") {
        echo '<span class="tooltip">' . $closure['closure'] . '</span>';
    }

} elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == 'announcement') {

    $announcement_query = $mysqli->query("SELECT announcement FROM shops WHERE slug = '" . $_REQUEST['shop'] . "'") or die($mysqli->error);
    $announcement = mysqli_fetch_array($announcement_query);

    if (isset($announcement['announcement']) && $announcement['announcement'] != "") { ?>
        <div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout">
            <ul class="woocommerce-error">
                <li><strong><?= $announcement['announcement'] ?></strong></li>
            </ul>
        </div>
        <?php
    }

}