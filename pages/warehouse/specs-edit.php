<?php



include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";

include INCLUDES . "/googlelogin.php";
include INCLUDES . "/functions.php";

$id = $_REQUEST['id'];

$spec_query = $mysqli->query('SELECT * FROM specs WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);

$spec = mysqli_fetch_assoc($spec_query);

$pagetitle = "Upravit specifikaci";

$bread1 = "Specifikace";
$abread1 = "specifikace";

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "edit") {

    $brands = !empty($_POST['brands']) ? json_encode($_POST['brands']) : '';
    $mysqli->query("UPDATE specs SET brand = '".$brands."' WHERE id = '".$id."'")or die($mysqli->error);

    $mysqli->query("UPDATE specs_params SET active = 0 WHERE spec_id = '".$spec['id']."'")or die($mysqli->error);
    foreach($_POST['params'] as $key => $value){
        $mysqli->query("UPDATE specs_params SET active = '".$value."' WHERE id = '".$key."'")or die($mysqli->error);
    }

    Header("Location:https://www.wellnesstrade.cz/admin/pages/warehouse/specifikace?success=true");
    exit;
}

include VIEW . '/default/header.php';

    ?>

    <h1><?= $spec['name'] ?></h1>
    <form role="form" method="post" name="myform" class="form-horizontal form-groups-bordered validate" action="specs-edit?id=<?= $_REQUEST['id'] ?>&action=edit">

        <div class="row">
            <div class="col-md-12">
            <div class="panel panel-primary">
                <div class="panel-body">
                    <div class="col-md-8 col-sm-offset-1">

                        <div class="form-group">
                            <div class="col-sm-3">Aktivní pro značky</div>
                            <?php

                            $specBrands = json_decode($spec['brand']);

                            $brands = $mysqli->query("SELECT brand FROM warehouse_products WHERE brand != '' AND customer = '".$spec['product']."' AND active = 'yes' GROUP BY brand")or die($mysqli->error);

                            while($brand = mysqli_fetch_assoc($brands)){ ?>
                                <div class="col-sm-2">
                                    <label>
                                        <input type="checkbox" id="<?= $brand['brand'] ?>" name="brands[]" value="<?= $brand['brand'] ?>" <?= (!empty($specBrands) && in_array($brand['brand'], $specBrands)) ? 'checked' : '' ?>>
                                        <?= $brand['brand'] ?>
                                    </label>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-3">Parametry - aktivace</div>
                            <div class="col-sm-8">
                                <?php

                                $params = $mysqli->query("SELECT * FROM specs_params WHERE spec_id = '".$spec['id']."' ORDER BY option")or die($mysqli->error);
                                while($param = mysqli_fetch_assoc($params)){ ?>
                                    <div class="col-sm-6">
                                        <label>
                                            <input type="checkbox" name="params[<?= $param['id'] ?>]" value="1" <?= $param['active'] ? 'checked' : '' ?>>
                                            <?= $param['option'] ?>
                                        </label>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                <center>
                    <div class="form-group default-padding" style="margin-left: -100px;">
                        <button type="submit" class="btn btn-success"><?= $pagetitle ?></button>
                    </div>
                </center>
            </div>

        </div>
    </form>
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

    <script>

        $(document).ready(function(){

            $("#order_form").on("submit", function(){
                var form = $( "#order_form" );
                var l = Ladda.create( document.querySelector( '#order_form .button-demo button' ) );
                if(form.valid()){

                    l.start();
                }
            });


        });


    </script>

    <?php include VIEW . '/default/footer.php'; ?>
