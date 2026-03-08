<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";

$id = $_REQUEST['id'];

$container_query = $mysqli->query("SELECT * FROM containers WHERE id = '$id'");
$container = mysqli_fetch_array($container_query);

$text = 'Zaplacení doplatku';

$remove_button = 'Zaplacení doplatku';

$title = 'Zaplacení doplatku kontejneru #' . $container['id'];


$kurz_url = "http://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_devizoveho_trhu/denni_kurz.txt";
$kurz_data =  file_get_contents($kurz_url);
$output = explode("\n", $kurz_data);

unset($output[0]); // odstranění prvního řádku - datum
unset($output[count($output)]); // odstranění posledního řádku - nic neobsahuje
unset($output[1]); // odstranění druhého řádku - legenda pro CSV

$kurz = array("CZK" => 1);
foreach($output as $radek){
    $mena = explode("|", $radek);
    $kurz[trim($mena[3])] = str_replace(",",".",trim($mena[4]));
}


?>

<script type="text/javascript">

    jQuery(document).ready(function($)
    {
        $('.price-control, .exchange_rate').change(function() {
            calc_second_price();
        });
    });

</script>

<div class="modal-dialog" style="width: 800px;">
    <form role="form" method="post" action="?action=second_payment&id=<?= $id ?>" enctype="multipart/form-data">
        <div class="modal-content">
            <div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

                <h4 class="modal-title"><?= $title ?></h4> </div>

            <div class="modal-body" style="padding: 20px 35px 20px 35px; text-align: center;">

                <div class="form-group"><label for="field-2" class="col-sm-3 control-label">Aktuální kurz dle ČNB</label>
                    <div class="col-sm-3">
                        <h5>
                            <strong><?= $kurz["USD"] ?></strong> CZK/USD</h5>

                    </div>
                    <label for="field-2" class="col-sm-3 control-label"><strong>Kurz platby</strong></label>
                        <div class="col-sm-3">
                            <input class="exchange_rate form-control" type="text" value="<?= $kurz["USD"] ?>" name="exchange_rate">
                        </div>
                </div>


                <div style="clear:both;"></div>
                <hr>

                <?php

                $container_products = $mysqli->query("SELECT *, DATE_FORMAT(date_created, '%d. %m. %Y') as dateformated FROM containers_products WHERE container_id = '$id' ORDER BY id ASC") or die($mysqli->error);
                while ($cont_product = mysqli_fetch_array($container_products)) {

                    ?>

                    <div class="form-group">
                        <label for="field-2" class="col-sm-4 control-label" style="margin-top: 6px; text-align: right; color: #222"><strong>#<?= $cont_product['id'] ?> - <?= ucfirst($cont_product['product']) ?></strong></label>
                        <div class="col-sm-6" style="margin-bottom: 6px;">

                            <input type="number" name="price-<?= $cont_product['id'] ?>" class="form-control price-control" placeholder="Pořizovací cena" value="<?php
                            if(!empty($cont_product['purchase_price'])){ echo $cont_product['purchase_price']; }
                            ?>" readonly>

                        </div>
                    </div>
                <?php }

                ?>

                <hr>
                <div style="clear:both;"></div>

                <hr>

                <div class="form-group">
                    <label for="field-2" class="col-sm-4 control-label" style="margin-top: 6px; text-align: right; color: #222"><strong>Cena za náhradní díly</strong></label>
                    <div class="col-sm-6" style="margin-bottom: 6px;">

                        <input type="number" name="spare_parts" class="form-control price-control" placeholder="Částka">


                    </div>
                </div>

                <div style="clear:both;"></div>
                <hr>



                <div class="form-group">

                    <div class="col-md-12">
                        <h5>Zaplacená záloha 30%: $<span><?= number_format($container['first_payment'], 2, '.', ',') ?></span></h5>
                        <h4>Přepočtená záloha 30%: <span><?= thousand_seperator($container['first_payment'] * $container['first_exchange_rate']) ?></span> Kč</h4>
                        <small style="float: none;">(již zaplacená záloha)</small>
                        <input id="deposit" type="number" style="display: none" value="<?= $container['first_payment'] ?>" >
                    </div>
                    <hr>

                    <div class="col-md-6">

                        <h5>Celková cena: <span id="new_total_usd">0</span></h5>
                        <h4>Celková přepočtená cena: <span id="new_total_czk">0</span></h4>
                        <small style="float: none;">(celková cena pro výpočet doplatku)</small>

                    </div>

                    <div class="col-md-6">

                        <h5>Konečná platba: <span id="final_payment_usd">0</span></h5>
                        <h4>Konečná přepočtená platba: <span id="final_payment_czk">0</span></h4>
                        <small style="float: none;">(celková cena - zaplacená záloha)</small>

                    </div>




                    <div style="clear:both;"></div>
                    <hr>




                </div>

            </div>
            <div style="clear:both;"></div>

            <div class="modal-footer" style="text-align:left;"> <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>

                <div style="float: right;"><button type="submit" class="btn btn-primary btn-icon icon-left"><?= $remove_button ?>
                        <i class="fa fa-barcode"></i></button></div>

            </div>

        </div>
    </form>
</div>
