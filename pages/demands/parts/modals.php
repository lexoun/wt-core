<?php
    if($brand['brand'] == 'Swim SPA' || $brand['brand'] == 'Lovia') {

        $containerBrand = 'Lovia';

    }else{

        $containerBrand = $brand['brand'];

    }
?>
<script type="text/javascript">
        $(document).ready(function(){
            $(".toggle-modal-remove").click(function(e){

                $('#remove-modal').removeData('bs.modal');
                e.preventDefault();


                var type = $(this).data("type");

                var id = $(this).data("id");

                $("#remove-modal").modal({

                    remote: '/admin/controllers/modals/modal-remove.php?id='+id+'&type='+type,
                });
            });


            $(document).on('click', '.toggle-modal-edit', function(e) {

                $('#edit-container-product-modal').removeData('bs.modal');

                e.preventDefault();

                var id = $(this).data("id");

                $("#edit-container-product-modal").modal({
                    remote: '/admin/controllers/modals/modal-edit-container-product.php?id=' + id,
                });
            });
        });
    </script>


    <div class="modal fade" id="remove-modal" aria-hidden="true" style="display: none; margin-top: 160px;">
    </div>



    <div class="modal fade" id="edit-container-product-modal" aria-hidden="true" style="display: none; margin-top: 0px;">
    </div>


    <script type="text/javascript">
        $(document).ready(function(){
            $(".toggle-edit-followup").click(function(e){

                $('#followup-modal').removeData('bs.modal');
                e.preventDefault();

                var id = $(this).data("id");

                $("#followup-modal").modal({

                    remote: '/admin/controllers/modals/followup.php?id='+id,
                });
            });
        });
    </script>


    <div class="modal fade" id="followup-modal" aria-hidden="true" style="display: none; margin-top: 2%;">

    </div>



    <script type="text/javascript">
        $(document).ready(function(){
            $(".toggle-default-modal").click(function(e){

                $('#default-modal').removeData('bs.modal');
                e.preventDefault();


                var type = $(this).data("type");

                var id = $(this).data("id");

                $("#default-modal").modal({

                    remote: '/admin/controllers/modals/default.php?id='+id+'&type='+type,
                });
            });
        });
    </script>


    <div class="modal fade" id="default-modal" aria-hidden="true" style="display: none; margin-top: 160px;">

    </div>

    <script type="text/javascript">
        $(document).ready(function(){
            $(".toggle-mail-modal").click(function(e){

                $('#mail-modal').removeData('bs.modal');
                e.preventDefault();

                var id = $(this).data("id");

                $("#mail-modal").modal({

                    remote: '/admin/controllers/modals/modal-show-mail.php?id='+id,
                });
            });
        });
    </script>


    <div class="modal fade" id="mail-modal" aria-hidden="true" style="display: none; margin-top: 20px;">

    </div>


    <div class="modal fade" id="modal-1" aria-hidden="true" style="display: none; margin-top: 8%;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"> <button type="button" class="close" data-dismiss="modal"
                        aria-hidden="true">×</button>
                    <h4 class="modal-title">Potvrzení vytvoření klienta</h4>
                </div>
                <div class="modal-body">
                    Opravdu si přeje spustit operaci vytvoření klienta z poptávky? Tímto krogooglm vygenerujete
                    nového klienta z podkladů v poptávce, pošlete emailovi informační email s přihlašovacíma údajema
                    (zatím nefunguje), vyskladníte produkt ze skladu (vířivka, sauna) a případně se vyskladní zboží
                    přidělené k sauně jako specifikace. Poptávka se též přesune do "Hotových".
                </div>
                <div class="modal-footer" style="text-align:left;"> <button type="button" class="btn btn-default"
                        data-dismiss="modal">Zrušit</button> <a
                        href="./generovat-klienta?id=<?= $getclient['id'] ?>" style="float:right;"><button
                            type="button" class="btn btn-green">Vytvořit
                            klienta</button></a> </div>
            </div>
        </div>
    </div>

        <div class="modal fade" id="container_modal" aria-hidden="true" style="display: none; top: 8%;">
            <div class="modal-dialog">
                <form role="form" method="post" class="form-horizontal form-groups-bordered validate"
                      action="../warehouse/editace-kontejneru?demand_id=<?= $id ?>&action=add_demand&brand=<?= $containerBrand ?>"
                      enctype="multipart/form-data">
                <div class="modal-content">


                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            <h4 class="modal-title">Přidat produkt do kontejneru</h4>
                        </div>

                        <div class="modal-body">

                            <label for="field-2" class="col-sm-4 control-label"><strong>Zvolte kontejner</strong></label>

                            <div class="col-sm-6">
                                <select id="optionus" name="container" class="form-control">
                                    <?php



    // todo brand Lovia + Swim SPA in one$brand['brand']
    if($brand['brand'] == 'Swim SPA' || $brand['brand'] == 'Lovia') {

        $containers_query = $mysqli->query("SELECT *, DATE_FORMAT(date_created, '%d. %m. %Y') as date_formatted FROM containers WHERE (closed = '0' OR closed = '1') AND (brand = 'Swim SPA' OR brand = 'Lovia') order by id desc") or die($mysqli->error);


    }else{

        $containers_query = $mysqli->query("SELECT *, DATE_FORMAT(date_created, '%d. %m. %Y') as date_formatted FROM containers WHERE (closed = '0' OR closed = '1') AND brand = '" . $brand['brand'] . "' order by id desc") or die($mysqli->error);

    }


        while ($containers = mysqli_fetch_array($containers_query)) {
            $total_products_query = $mysqli->query("SELECT count(*) as total FROM containers_products WHERE container_id = '" . $containers['id'] . "'") or die($mysqli->error);
            $total_products = mysqli_fetch_array($total_products_query);

            if (isset($total_products['total']) && $total_products['total'] < $containers['size']) {
                ?>
                                    <option value="<?= $containers['id'] ?>">
                                        Kontejner
                                        #<?= $containers['id_brand'] ?>
                                        - <?= $containers['date_formatted'] ?>
                                        [<?= $total_products['total'] ?>/<?= $containers['size'] ?>]
                                    </option>

                                    <?php
            }
        } ?>
                                    <option value="new">Založit nový kontejner</option>
                                </select>

                            </div>
                            <br>
                        </div>

                    <div class="modal-footer" style="text-align:left;">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>
                        <button type="submit" class="btn btn-green btn-icon icon-left" style="float: right;">Přidat ke
                            kontejneru <i class="entypo-pencil"></i></button>
                    </div>
                </div>

                </form>

            </div>
        </div>


    <?php

    if (!empty($getclient['showroom'])) {
        $showroom_sms = remove_diacritics($getclient['sms_address']);
    } else {
        $showroom_sms = 'Praha - Branik, Vrbova 32, 147 00 Praha 4. Parkovani na vyhrazenem parkovisti za showroomem';
    }


    $hasVisitation = false;
    $visatation_query = $mysqli->query("SELECT *, DATE_FORMAT(date_time, '%d. %m. %Y') as dateformated, DATE_FORMAT(date_time, '%H:%i') as hoursmins FROM demands_mails_history WHERE demand_id = '$id' AND (type = 'Zkouška vířivky' OR type = 'Návštěva - plánovaná' OR type = 'Návštěva - neplánovaná') AND state != 'done' ORDER BY id desc");
    if (mysqli_num_rows($visatation_query) > 0) {
        $visitation = mysqli_fetch_assoc($visatation_query);

        $hasVisitation = true;
        $visitation_info = $visitation['dateformated'].' v čase '.$visitation['hoursmins'];

    }

    $salutation = '';
    if (!empty($preName) && !empty($lastname)) {
        $salutation = remove_diacritics($preName . ' ' . $vocativ->vocative($lastname));
    }

    if ($provedeni['value'] === 'Special version') { $variation = 'Gold'; }else{ $variation = $provedeni['value']; }
    $packet = $variation ? ' '.$variation : '';

    ?>

    <script type="text/javascript">

        function countChar(val) {
            var len = val.value.length;
            $('#charNum').text(len);
        };


        jQuery(document).ready(function($) {

            const salutation = '<?= $salutation ?>';
            const female = '<?php if($female){ echo 'a'; }?>';

            $(".content-offer").click(function () {

                $(".sms-content").html('Dobry den, '+salutation+', na email: <?= $billing['billing_email'] ?> jsme Vam zaslali nabidku na <?= $getclient['brand'] . ' ' . ucfirst($getclient['fullname']).$packet ?> dle domluvy. Pokud jste nabidku neobdrzel'+female+', podivejte se prosim do spamu ci hromadne posty. Dekujeme za Vasi reakci a v pripade jakychkoliv otazek ci nejasnosti nas nevahejte kontaktovat. Tym SPAHOUSE');

            });

            $(".content-contact").click(function () {

                $(".sms-content").html('Dobry den, '+salutation+', potvrzujeme prijeti zalohove platby za virivku. V pripade otazek na: realizaci (776 553 722), administrativu a fakturaci (777 202 879), nas kontaktujte. Tym SPAHOUSE');

            });

            $(".content-showroom").click(function () {

                $(".sms-content").html('Dobry den, '+salutation+', adresa showroomu <?= $showroom_sms ?>. Tym SPAHOUSE');

            });

            $(".content-augmented").click(function () {

                $(".sms-content").html('Dobry den, '+salutation+', vyzkousejte rozsirenou realitu u Vas doma: https://www.spahouse.cz/rozsirena-realita/. Tym SPAHOUSE');

            });

            $(".content-construction-prep").click(function () {

                $(".sms-content").html('Dobry den, '+salutation+', na email: <?= $billing['billing_email'] ?> jsme Vam zaslali informace ke kontrole stavebni pripravy. Pokud jste email neobdrzel'+female+', podivejte se prosim do spamu ci hromadne posty. Dekujeme za Vasi reakci a v pripade jakychkoliv otazek ci nejasnosti nas nevahejte kontaktovat. Tym SPAHOUSE');

            });

            $(".content-construction-prep2").click(function () {

                $(".sms-content").html('Dobry den, '+salutation+', stale jsme od Vas nedostali pozadovane informace ke stavebni pripravenosti, ktere jsme Vam posilali v e-mailu. Tyto informace potrebujeme pro naplanovani a hladky prubeh instalace Vasi virivky. V pripade nedodani kompletnich informaci za vcas, muze dojit k odlozeni dodavky virivky. Tym SPAHOUSE');

            });

            <?php if($hasVisitation){ ?>

                $(".content-visitation").click(function () {

                    $(".sms-content").html('Dobry den, '+salutation+', potvrzujeme rezervaci navstevy Showroomu v terminu <?= $visitation_info ?>. Tym SPAHOUSE');

                });

                $(".content-visitation-address").click(function () {

                    $(".sms-content").html('Dobry den, '+salutation+', potvrzujeme rezervaci navstevy Showroomu v terminu <?= $visitation_info ?>. Adresa showroomu <?= $showroom_sms ?>. Tym SPAHOUSE');

                });

            <?php } ?>

            $(".content").click(function () {

                $(".content").removeClass('btn-primary');
                $(".content").addClass('btn-white');
                $(this).addClass('btn-primary');
                $(this).removeClass('btn-white');

            });

        });

    </script>


    <div class="modal fade" id="demand_sms" aria-hidden="true"
         style="display: none;  top: 8%;">
        <div class="modal-dialog">
            <div class="modal-content">
                <form role="form" method="post" class="form-horizontal form-groups-bordered validate"
                      action="zobrazit-poptavku?id=<?= $id ?>&action=smsmessage"
                      enctype="multipart/form-data">

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title">Odeslání poptávkové SMS
                        </h4>
                    </div>

                    <div class="modal-body">

                        <h5><strong>SMS zpráva bude odeslána na číslo: <u style="color: #000;"><?= phone_prefix($billing['billing_phone_prefix']).' '.number_format((float)$billing['billing_phone'], 0, ',', ' ') ?></u></strong></h5>
                        <br>
                        <div class="form-group col-sm-12">
                            <label for="field-2" class="col-sm-12 form-label" style="padding: 20px 0 10px; color: #000; font-style: italic;">Druh zprávy:</label>
                            <br>
<style>
    .content {
        margin-bottom: 6px;
    }
</style>
                            <div class="content-offer btn btn-primary btn-md content">Nabídka</div>
                            <div class="content-contact btn btn-white btn-md content">Kontakty</div>
                            <div class="content-showroom btn btn-white btn-md content">Adresa</div>
                            <div class="content-augmented btn btn-white btn-md content">Rozšířená realita</div>
                            <div class="content-construction-prep btn btn-white btn-md content">Stavební příprava #1</div>
                            <div class="content-construction-prep2 btn btn-white btn-md content">Stavební příprava #2</div>
                           <?php if($hasVisitation){ ?>
                               <div class="content-visitation btn btn-white btn-md content">Návštěva</div>
                               <div class="content-visitation-address btn btn-white btn-md content">Návštěva + adresa</div>
                           <?php } ?>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <?php

                            $default_message = 'Dobry den, '. $salutation . ', na email: '.$billing['billing_email'].' jsme Vam zaslali nabidku na '.$getclient['brand'] . ' ' . ucfirst($getclient['fullname']).$packet.' dle domluvy. Pokud jste nabidku neobdrzeli, podivejte se prosim do spamu ci hromadne posty. Dekujeme za Vasi reakci a v pripade jakychkoliv otazek ci nejasnosti nas nevahejte kontaktovat. Tym SPAHOUSE';


                            ?>
                            <div class="col-sm-12">
                                <textarea id="field" onkeyup="countChar(this)" class="form-control autogrow sms-content" name="message" placeholder="Obsah sms zprávy..."
                              style="overflow: hidden; margin-bottom: 0px;word-wrap: break-word; resize: horizontal; height: 100px;"><?= $default_message ?></textarea>


                              <!--  todo maybe <textarea maxlength='140'></textarea> -->
                                <br>
<!--                               <div style="text-align: center;">Počet znaků: <span id="charNum">--><?// echo strlen($default_message);?><!--</span> (1 SMS = 160 zn.)</div>-->
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="text-align:left;">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>
                        <button type="submit" class="btn btn-lg btn-green btn-icon icon-left" style="float: right;">Odeslat <i
                                    class="fas fa-sms"></i></button>
                    </div>

                </form>

            </div>
        </div>
    </div>




    <?php

        $i = 0;

        if (!empty($generate_data)) {
            $inv = $generate_data['invoices_number'];

            for ($inv; $inv > 0; $inv--) {
                $i++;

                $advance_invoice_query = "";
                $advance_invoice = "";
                $advance_invoice_query = $mysqli->query("SELECT due_date, payment_method, special_name, paid FROM demands_advance_invoices WHERE demand_id = '$id' AND status = '$i'");

                if (isset($advance_invoice_query) && mysqli_num_rows($advance_invoice_query) == 1) {
                    $advance_invoice = mysqli_fetch_array($advance_invoice_query);
                } else {
                    $advance_invoice = ['payment_method' => "bankwire"];
                } ?>
    <div class="modal fade" id="invoice_modal_<?= $i ?>" aria-hidden="true" style="display: none; top: 8%;">
        <div class="modal-dialog">
            <div class="modal-content">
                <form role="form" role="form" method="post" class="form-horizontal form-groups-bordered validate"
                    action="/admin/controllers/generators/demand_advance_invoice?id=<?= $id ?>&invoice=<?= $i ?>"
                    enctype="multipart/form-data">

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title">Generovat zál. fakturu #<?= $i ?>
                        </h4>
                    </div>
                    <?php
                    $invoicePaid = false;
                    if(isset($advance_invoice['paid']) && $advance_invoice['paid'] != 0){ $invoicePaid = true; }

                    if($invoicePaid){ ?>
                        <div class="alert alert-warning"><strong>Upozornění!</strong> Právě generujete již zaplacenou fakturu. Při změně ceny bude zaplacení anulováno a automaticky bude znovu zkontrolován stav zaplacení. V případě, že byla faktura "manuálně zaplacena", je nutné platbu opět potvrdit.</div>
                    <?php } ?>
                    <div class="modal-body">
                        <p>Právě generujete fakturu pro poptávku
                            <strong><?= $getclient['user_name'] ?></strong>.
                        </p>
                        <br>
                        <div class="form-group">
                            <label for="field-2" class="col-sm-4 control-label"><strong>Datum
                                    splatnosti</strong></label>

                            <div class="col-sm-5">
                                <div class="date">
                                    <input type="text" class="form-control datepicker" name="date_due"
                                        data-format="yyyy-mm-dd" placeholder="Datum" value="<?php if (isset($advance_invoice['due_date']) && $advance_invoice['due_date'] != "0000-00-00") {
                    echo $advance_invoice['due_date'];
                } else {
                    echo date('Y-m-d');
                } ?>">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="field-2" class="col-sm-4 control-label"><strong>Způsob úhrady</strong></label>
                            <div class="col-sm-8">
                                <div class="radio" style="float: left;">
                                    <label>
                                        <input type="radio" name="payment_method" value="bankwire" <?php if ((isset($advance_invoice['payment_method']) && $advance_invoice['payment_method'] == 'bankwire') || !isset($advance_invoice['payment_method'])) {
                    echo 'checked';
                } ?>>Převodem na účet
                                    </label>
                                </div>
                                <div class="radio" style="float: left; margin-left: 20px;">
                                    <label>
                                        <input type="radio" name="payment_method" value="cash" <?php if (isset($advance_invoice['payment_method']) && $advance_invoice['payment_method'] == 'cash') {
                    echo 'checked';
                } ?>>Hotově
                                    </label>
                                </div>
                                <div class="radio" style="float: left; margin-left: 20px;">
                                    <label>
                                        <input type="radio" name="payment_method" value="card" <?php if (isset($advance_invoice['payment_method']) && $advance_invoice['payment_method'] == 'card') {
                                            echo 'checked';
                                        } ?>>Kartou
                                    </label>
                                </div>

                            </div>
                        </div>

                        <div class="form-group">
                            <label for="field-2" class="col-sm-4 control-label"><strong>Speciální název
                                    položky</strong></label>

                            <div class="col-sm-5">
                                <div class="date">
                                    <input type="text" class="form-control" name="special_name" value="<?php if (isset($advance_invoice['special_name']) && $advance_invoice['special_name'] != "") {
                    echo $advance_invoice['special_name'];
                } ?>">

                                </div>
                            </div>
                        </div>



                    </div>

                    <div class="modal-footer" style="text-align:left;">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>
                        <button type="submit" class="btn btn-green btn-icon icon-left" style="float: right;">Generovat
                            <i class="entypo-pencil"></i></button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <?php
            }
        }

        $i = 0;
        if (!empty($generate_data)) {
            $inv = $generate_data['invoices_number'];

            for ($inv; $inv > 0; $inv--) {
                $purchase_invoice = false;

                $i++;

                $get_advance_invoices = $mysqli->query("SELECT * FROM demands_advance_invoices WHERE demand_id = '$id' AND status = '$i'");

                while ($get_advance = mysqli_fetch_array($get_advance_invoices)) {
                    ?>


    <div class="modal fade" id="invoice_payment_modal_<?= $i ?>" aria-hidden="true"
        style="display: none;  top: 8%;">
        <div class="modal-dialog">
            <div class="modal-content">
                <form role="form" method="post" class="form-horizontal form-groups-bordered validate"
                    action="zobrazit-poptavku?id=<?= $id ?>&advance_invoice_id=<?= $get_advance['id'] ?>&action=payment_save"
                    enctype="multipart/form-data">

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title">Uložit platbu k zálohové faktuře číslo <?= $get_advance['id'] ?>
                        </h4>
                    </div>

                    <div class="modal-body">

                        <br>
                        <?php

                        if (isset($get_advance['payment_method']) && $get_advance['payment_method'] == 'cash') { ?>
                      <strong>Záloha by měla být placena <u>hotově</u>.</strong>
                        <?php } elseif (isset($get_advance['payment_method']) && $get_advance['payment_method'] == 'bankwire') { ?>
                       <strong>Záloha by měla být placena <u>bankovním převodem</u>.</strong><?php

                        } elseif (isset($get_advance['payment_method']) && $get_advance['payment_method'] == 'card') { ?>
                            <strong>Záloha by měla být placena <u>platební kartou</u>.</strong><?php

                        }

                        ?>
                        ~
                       <?php

                       $get_advance['target_id'] = $get_advance['id'];
                       $get_advance['location_id'] = $getclient['showroom'];

                       $payment = check_payment($get_advance, 'demand');

                       echo '<span style="  '.$payment['color'].'">'.$payment['info'].'</span>';

                        if (isset($get_advance['payment_method']) && $get_advance['payment_method'] == 'cash') {

                       ?>

                        <hr style="margin: 30px 0;">

                        <div class="form-group">
                            <label for="field-2" class="col-sm-4 control-label">
                                <strong>Datum přijetí úplaty</strong>
                            </label>

                            <div class="col-sm-5">
                                <div class="date">
                                    <input type="text" class="form-control" name="date_payment"
                                           data-format="yyyy-mm-dd" placeholder="Datum"
                                           value="<?php
                                           if (isset($get_advance['payment_date']) &&
                                           $get_advance['payment_date'] != "" &&
                                           $get_advance['payment_date'] != '0000-00-00')
                                           {
                                        echo $get_advance['payment_date'];
                                    } else {
                                        echo date('Y-m-d');
                                    } ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <style>
                            .radio .validate-has-error {
                                position: absolute !important;
                                margin-top: 24px !important;
                            }
                        </style>

                        <div class="form-group">
                            <label for="field-2" class="col-sm-12 form-label" style="margin: 0; line-height: 27px;">
                                <strong>Pobočka pro zaúčtování platby</strong>
                            </label>

                            <div class="col-sm-12">
                                <?php
                                foreach($locationsArray as $location){ ?>
                                    <div class="radio" style="float: left; margin-left: 10px">
                                        <label>
                                            <input type="radio" name="location" value="<?= $location['id'] ?>" required><?= $location['name'] ?>
                                        </label>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>

                        <?php }



                        ?>
                        <hr style="margin: 30px 0;">
                        <div class="well" style="margin: 0;">
                            <div class="form-group">
                                <label for="field-2" class="col-sm-5 form-label" style="margin: 0; line-height: 27px;">
                                    <strong>Poslat potvrzení o platbě</strong>
                                </label>

                                <div class="col-sm-7">
                                    <div class="radio" style="float: left; margin-left: 10px">
                                        <label>
                                            <input type="radio" name="paymentConfirmation" value="1">Ano
                                        </label>
                                    </div>
                                    <div class="radio" style="float: left;margin-left: 30px;">
                                        <label>
                                            <input type="radio" name="paymentConfirmation" value="0" checked>Ne
                                        </label>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                    <div class="modal-footer" style="text-align:left;">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>
                        <button type="submit" class="btn btn-lg btn-green btn-icon icon-left" style="float: right;">Uložit <i
                                class="entypo-pencil"></i></button>
                    </div>

                </form>

            </div>
        </div>
    </div>



    <div class="modal fade" id="invoice_proof_modal_<?= $i ?>" aria-hidden="true"
        style="display: none;  top: 8%;">
        <div class="modal-dialog">
            <div class="modal-content">
                <form role="form" method="post" class="form-horizontal form-groups-bordered validate"
                    action="/admin/controllers/generators/demand_invoice_eet?client_id=<?= $id ?>&advance_invoice_id=<?= $get_advance['id'] ?>&type=active"
                    enctype="multipart/form-data">

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title">Generovat daňový doklad k zálohové faktuře číslo
                            <?= $get_advance['id'] ?>
                        </h4>
                    </div>

                    <div class="modal-body">

                        <p>Právě generujete daňový doklad pro záloh. fakturu.</p> <br>
                        <?php if (isset($get_advance['payment_method']) && $get_advance['payment_method'] == 'cash') { ?>
                        <p style="color:#d42020;"><strong>Záloha byla placena <u>hotově</u>. Tržba bude při
                                vygenerování daňového dokladu odeslána do EET!</strong></p>
                        <?php } elseif (isset($get_advance['payment_method']) && $get_advance['payment_method'] == 'bankwire') { ?>
                        <p style="color:#00a651;"><strong>Záloha byla placena <u>bankovním převodem</u> a vše je v
                                pořádku.</strong></p><?php } ?><br>
                        <br>

                        <div class="form-group">
                            <label for="field-2" class="col-sm-4 control-label"><strong>Datum přijetí
                                    úplaty</strong></label>

                            <div class="col-sm-5">
                                <div class="date">
                                    <input type="text" class="form-control datepicker" name="date_payment"
                                        data-format="yyyy-mm-dd" placeholder="Datum"
                                        value="<?= date('Y-m-d') ?>">

                                </div>
                            </div>
                        </div>

                    </div>


                    <div class="modal-footer" style="text-align:left;">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>
                        <button type="submit" class="btn btn-green btn-icon icon-left" style="float: right;">Generovat
                            <i class="entypo-pencil"></i></button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <?php
                }
            }
        }
        ?>





    <script type="text/javascript">

    $(document).ready(function(){
        $('#picture-upload-modal').on('hidden.bs.modal', function() {
            
            $("#demand_pictures").load(location.href + " #demand_pictures");

            $(this).removeData('bs.modal');
            $(this).data('bs.modal', null);

        });

    });

    function initDropzones() {
        $('.dropzone').each(function() {

            event.preventDefault();

            let dropzoneControl = $(this)[0].dropzone;
            if (dropzoneControl) {
                dropzoneControl.destroy();
            }
        });
    }


    $(".toggle-picture-upload-modal").click(function(event) {

        event.preventDefault();

        var id = $(this).data("id");

        $('#picture-upload-modal').find('.modal-title').text('<?= $getclient['user_name'] ?>: Nahrání obrázků - ' + id);

        Dropzone.autoDiscover = false;
        initDropzones();

        var myDropzone = new Dropzone('form#dropzone_upload', {

            url: "/admin/controllers/uploads/upload-file-poptavka?id=<?= $id ?>&type=" +
                id,

        });

        $("#picture-upload-modal").modal('show');

    });

    </script>


    <div class="modal fade" id="picture-upload-modal" aria-hidden="true" style="display: none; top: 8%;">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 class="modal-title">Nahrání obrázků <?= $getclient['user_name'] ?>
                    </h4>
                </div>

                <div class="modal-body">
                    <form action="#" class="dropzone" id="dropzone_upload">
                        <div class="fallback">
                            <input name="file" type="file" multiple />
                        </div>
                    </form>
                </div>

                <div class="modal-footer" style="text-align:left;">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>
                    <button type="button" class="btn btn-success btn-icon icon-left" id="done-picture-upload"
                        data-dismiss="modal" style="float: right;">Hotovo <i class="entypo-check"></i></button>
                </div>

            </div>
        </div>
    </div>





    <style>
    .page-body .selectboxit-container .selectboxit-options {
        margin-top: 40px !important;
        width: 100% !important;
    }

    .page-body .selectboxit-container .selectboxit {
        height: 40px;
        width: 100% !important;
    }

    .page-body .selectboxit-container .selectboxit .selectboxit-text {
        line-height: 40px;
    }

    .page-body .selectboxit-container .selectboxit .selectboxit-arrow-container {
        height: 40px;
    }

    .page-body .selectboxit-container .selectboxit .selectboxit-arrow-container:after {
        line-height: 40px;
    }
    </style>

    <script type="text/javascript">
    $(document).ready(function() {
        $(".toggle-modal-change-state").click(function(e) {

            $('#change-state-modal').removeData('bs.modal');
            e.preventDefault();


            var id = $(this).data("id");

            $("#change-state-modal").modal({

                remote: '/admin/controllers/modals/modal-change-services.php?id=' + id,
            });
        });
    });
    </script>

    <div class="modal fade" id="change-state-modal" aria-hidden="true" style="display: none; margin-top: 3%;">

    </div>


    <div class="modal fade" id="new-realization-modal" aria-hidden="true" style="display: none; top: 4%;">
        <div class="modal-dialog" style="width: 800px;">
            <div class="modal-content">
                <form role="form" role="form" method="post" class="form-horizontal form-groups-bordered validate"
                    autocomplete="off" action="zobrazit-poptavku?id=<?= $getclient['id'] ?>&realization=new"
                    enctype="multipart/form-data">


                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title">Realizace poptávky <?= $getclient['user_name'] ?>
                        </h4>
                    </div>

                    <div class="modal-body">

                    <div class="form-group" style="margin: 0;">
                    <div class="well"
                                style="padding: 8px 8px 8px 8px; width: 100%; margin: 10px 0 0; float: left;">
                        <div class="col-sm-6">
                                <label for="field-2" class="col-sm-2 control-label">Začátek</label>

                                <div class="col-sm-10">
                                    <div class="date-and-time">
                                        <input type="text" class="form-control datepicker" name="realizationdate"
                                            data-format="yyyy-mm-dd" placeholder="Datum"
                                            <?php if (isset($getclient['realization']) && $getclient['realization'] != '0000-00-00') { ?>value="<?= $getclient['realization'] ?>"
                                            <?php } ?>>
                                        <input type="text" class="form-control timepicker" name="realizationtime"
                                            data-template="dropdown" placeholder="Čas"
                                            value="<?= $getclient['realizationtime'] ?>"
                                            data-show-seconds="false" data-default-time="" data-show-meridian="false"
                                            data-minute-step="5" />
                                    </div>
                                </div>
                            </div>
                        <div class="col-sm-6">
                                <label for="field-2" class="col-sm-2 control-label">Konec</label>

                                <div class="col-sm-10">
                                    <div class="date-and-time">
                                        <input type="text" class="form-control datepicker" name="realtodate"
                                            data-format="yyyy-mm-dd" placeholder="Datum"
                                            <?php if ($getclient['realtodate'] != '0000-00-00') { ?>value="<?= $getclient['realtodate'] ?>"
                                            <?php } ?>>
                                        <input type="text" class="form-control timepicker" name="realtotime"
                                            data-template="dropdown" placeholder="Čas"
                                            value="<?= $getclient['realtotime'] ?>" data-show-seconds="false"
                                            data-default-time="" data-show-meridian="false" data-minute-step="5" />
                                    </div>
                            </div>
                                            </div>
                        </div>
                    </div>
                            <div class="form-group" style="width: 50%; float: left; margin:0;">
                            <div class="well"
                                style="padding: 6px 8px 10px 8px; width: 100%; margin: 10px 0 0; float: left;">
                                <div class="radio" style="float: left;">
                                    <label>
                                        <input type="radio" name="confirmed" value="0" <?php if (isset($getclient['confirmed']) && $getclient['confirmed'] == '0') {
            echo 'checked';
        } ?>>Plánovaná
                                    </label>
                                </div>
                                <div class="radio" style="float: left;margin-left: 18px;">
                                    <label>
                                        <input type="radio" name="confirmed" value="2" <?php if (isset($getclient['confirmed']) && $getclient['confirmed'] == '2') {
            echo 'checked';
        } ?>>V řešení
                                    </label>
                                </div>
                                <div class="radio" style="float: left;margin-left: 18px;">
                                    <label>
                                        <input type="radio" name="confirmed" value="1" <?php if (isset($getclient['confirmed']) && $getclient['confirmed'] == '1') {
            echo 'checked';
        } ?>>Potvrzená
                                    </label>
                                </div>
    </div>
                            </div>


                            <div class="form-group" style="width: 48%; float: right; margin: 0 0 0 2%;">
                            <div class="well"
                                style="padding: 6px 8px 10px 8px; width: 100%; margin: 10px 0 0; float: left;">
                              
                                    <div class="radio" style="float: left; margin-left: 10px;">
                                        <label>
                                            <input type="radio" name="area" value="prague" <?php
                                            if ((isset($getclient['area']) && $getclient['area'] == 'prague' && $getclient['realization'] != '0000-00-00') || (!empty($getclient['showroom']) && $getclient['showroom'] == 2)) {
            echo 'checked';
        } ?>>Praha
                                        </label>
                                    </div>
                                    <div class="radio" style="float: left;margin-left: 18px;">
                                        <label>
                                            <input type="radio" name="area" value="brno" <?php if((isset($getclient['area']) && $getclient['area'] == 'brno' && $getclient['realization'] != '0000-00-00') || (!empty($getclient['showroom']) && $getclient['showroom'] == 3)) {
            echo 'checked';
        } ?>>Brno
                                        </label>
                                    </div>
    </div>

                            </div>



                            <div class="form-group" style="padding: 0; margin: 0;">
                                <div class="well admins_well" style="padding: 12px 0px 7px; width: 100%; margin: 10px 0 0; float: left;">
                                    <h4 style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">
                                        Proveditelé</h4>
                      <?php

                      if($getclient['customer'] == '0'){ $type = 'realization_sauna'; }else{ $type = 'realization_hottub'; }

        $adminsquery = $mysqli->query("SELECT id, user_name FROM demands WHERE role != 'client' AND active = 1");
        while ($admins = mysqli_fetch_array($adminsquery)) {

            $find_query = $mysqli->query("SELECT admin_id FROM mails_recievers WHERE type_id = '" . $getclient['id'] . "' AND admin_id = '" . $admins['id'] . "' AND type = '".$type."' AND reciever_type = 'performer'") or die($mysqli->error);

                                 ?><div class="col-sm-3" style="padding: 0 6px 0 12px;">

                                        <input id="real-admin-<?= $admins['id'] ?>-performer" name="performer[]" value="<?= $admins['id'] ?>" type="checkbox" <?php if (mysqli_num_rows($find_query) > 0) { echo 'checked'; } ?>>
                                        <label for="real-admin-<?= $admins['id'] ?>-performer" style="padding-left: 4px; cursor: pointer; <?php if(mysqli_num_rows($find_query) > 0){ echo 'color: green !important;'; }?>"><?= $admins['user_name'] ?></label>
                                    </div>
                                    <?php } ?>
                                </div>
                            </div>


                        <div class="form-group" style="padding: 0; margin: 0;">
                            <div class="well admins_well" style="padding: 12px 0px 7px; width: 100%; margin: 10px 0 0; float: left;">
                                <h4 style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">
                                    Informovaní</h4>
                                <?php



                                $adminsquery = $mysqli->query("SELECT id, user_name, role FROM demands WHERE role != 'client' AND active = 1");
                                while ($admins = mysqli_fetch_array($adminsquery)) {

                                    $find_query = $mysqli->query("SELECT admin_id FROM mails_recievers WHERE type_id = '" . $getclient['id'] . "' AND admin_id = '" . $admins['id'] . "' AND type = '".$type."' AND reciever_type = 'observer'") or die($mysqli->error);

                                    ?><div class="col-sm-3" style="padding: 0 6px 0 12px;">

                                    <input id="real-admin-<?= $admins['id'] ?>-observer" name="observer[]" value="<?= $admins['id'] ?>" type="checkbox" <?php if (mysqli_num_rows($find_query) > 0 || $admins['role'] == 'salesman-technician') { echo 'checked'; } ?>>
                                    <label for="real-admin-<?= $admins['id'] ?>-observer" style="padding-left: 4px; cursor: pointer; <?php if(mysqli_num_rows($find_query) > 0){ echo 'color: green !important;'; }?>"><?= $admins['user_name'] ?></label>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>



                            <input type="text" name="customer" value="<?php if (isset($getclient['customer']) && $getclient['customer'] == 3) {
            echo '1';
        } else {
            echo $getclient['customer'];
        } ?>" style="display:none;">
                            <input type="text" name="id" value="<?= $getclient['id'] ?>" style="display:none;">


                            <div class="form-group well" 
                                style="background-color: #FFFFFF; padding: 12px 0px 7px; float: left; width: 100%; margin-left: 0.5%; margin-bottom: 0;">
                                
                                <div class="col-sm-5">
                                    <label for="field-ta" class="col-sm-5 control-label">Odeslat mail klientovi</label>
                                    <div class="col-sm-7">
                                        <div class="radio col-sm-6" style="margin: 0;">
                                            <label>
                                                <input type="radio" name="send_email" id="maile" value="yes">Ano
                                            </label>
                                        </div>
                                        <div class="radio col-sm-6" style="margin: 0;">
                                            <label>
                                                <input type="radio" name="send_email" id="maile" value="no" checked>Ne
                                            </label>
                                        </div>
                                    </div>
                                 </div>

                                <div class="col-sm-7">

                                    <div class="form-group">
                                        <label for="field-ta" class="col-sm-5 control-label" style="padding-right: 0;">Informace pro zákazníka</label>

                                        <div class="col-sm-7" style="padding-right: 0;">
                                            <textarea class="form-control" name="details" id="field-ta" rows="4" ></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <div style="clear:both"></div>
                        
                    </div>
                    

                    <div class="modal-footer" style="text-align:left;">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>
                        <button type="submit" class="btn btn-green btn-icon icon-left" style="float: right;">Naplánovat
                            <i class="entypo-pencil"></i></button>
                    </div>

                </form>
            </div>
        </div>
    </div>


    <?php if (isset($getclient['customer']) && $getclient['customer'] == 3) { ?>



    <div class="modal fade" id="new-realization-modal-sauna" aria-hidden="true" style="display: none; top: 8%;">
        <div class="modal-dialog">
            <div class="modal-content">
                <form role="form" role="form" method="post" class="form-horizontal form-groups-bordered validate"
                    autocomplete="off" action="zobrazit-poptavku?id=<?= $getclient['id'] ?>&realization=new"
                    enctype="multipart/form-data">


                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title">Realizace poptávky <?= $getclient['user_name'] ?>
                            - sauna</h4>
                    </div>

                    <div class="modal-body">

                     <div class="form-group">
                     <div class="well"
                                style="padding: 12px 0px 7px; width: 100%; margin: 20px 0 0; float: left;">
                        <div class="col-sm-6">
                            
                                <label for="field-2" class="col-sm-3 control-label">Začátek</label>

                                <div class="col-sm-9">
                                    <div class="date-and-time">
                                        <input type="text" class="form-control datepicker" name="realizationdate"
                                            data-format="yyyy-mm-dd" placeholder="Datum"
                                            <?php if (isset($saunadate['startdate']) && $saunadate['startdate'] != '0000-00-00') { ?>value="<?= $saunadate['startdate'] ?>"
                                            <?php } ?>>
                                        <input type="text" class="form-control timepicker" name="realizationtime"
                                            data-template="dropdown" placeholder="Čas"
                                            value="<?= $saunadate['starttime'] ?>"
                                            data-show-seconds="false" data-default-time="" data-show-meridian="false"
                                            data-minute-step="5" />
                                    </div>
                                </div>
                        </div>
                        <div class="col-sm-6">
                                <label for="field-2" class="col-sm-3 control-label">Konec</label>

                                <div class="col-sm-9">
                                    <div class="date-and-time">
                                        <input type="text" class="form-control datepicker" name="realtodate"
                                            data-format="yyyy-mm-dd" placeholder="Datum"
                                            <?php if ($saunadate['enddate'] != '0000-00-00') { ?>value="<?= $saunadate['enddate'] ?>"
                                            <?php } ?>>
                                        <input type="text" class="form-control timepicker" name="realtotime"
                                            data-template="dropdown" placeholder="Čas"
                                            value="<?= $saunadate['endtime'] ?>" data-show-seconds="false"
                                            data-default-time="" data-show-meridian="false" data-minute-step="5" />
                                </div>
                            </div>
                        </div>
                                            </div>
                    </div>
                            <div class="form-group"  style="width: 50%; float: left;">
                            <div class="well"
                                style="padding: 12px 0px 7px; width: 100%; margin: 20px 0 0; float: left;">
                                <div class="radio" style="float: left;">
                                    <label>
                                        <input type="radio" name="confirmed" value="0" <?php if (isset($saunadate['confirmed']) && $saunadate['confirmed'] == '0') {
            echo 'checked';
        } ?>>Plánovaná
                                    </label>
                                </div>
                                <div class="radio" style="float: left;margin-left: 18px;">
                                    <label>
                                        <input type="radio" name="confirmed" value="2" <?php if (isset($saunadate['confirmed']) && $saunadate['confirmed'] == '2') {
            echo 'checked';
        } ?>>V řešení
                                    </label>
                                </div>
                                <div class="radio" style="float: left;margin-left: 18px;">
                                    <label>
                                        <input type="radio" name="confirmed" value="1" <?php if (isset($saunadate['confirmed']) && $saunadate['confirmed'] == '1') {
            echo 'checked';
        } ?>>Potvrzená
                                    </label>
                                </div>
    </div>
                            </div>

                            <div class="form-group" style="width: 50%; float: left; padding-left: 20px;">
                                <div class="well"
                                     style="padding: 6px 8px 10px 8px; width: 100%; margin: 10px 0 0; float: left;">

                                    <div class="radio" style="float: left; margin-left: 10px;">
                                        <label>
                                            <input type="radio" name="area" value="prague" <?php
                                            if ((isset($getclient['area']) && $getclient['area'] == 'prague') || (!empty($getclient['showroom']) && $getclient['showroom'] == 2 && $getclient['area'] == 'unknown')) {
                                                echo 'checked';
                                            } ?>>Praha
                                        </label>
                                    </div>
                                    <div class="radio" style="float: left;margin-left: 18px;">
                                        <label>
                                            <input type="radio" name="area" value="brno" <?php if((isset($getclient['area']) && $getclient['area'] == 'brno') || (!empty($getclient['showroom']) && $getclient['showroom'] == 3 && $getclient['area'] == 'unknown')) {
                                                echo 'checked';
                                            } ?>>Brno
                                        </label>
                                    </div>
                                </div>

                            </div>


                        <div class="form-group" style="padding: 0; margin: 0;">
                            <div class="well admins_well" style="padding: 12px 0px 7px; width: 100%; margin: 10px 0 0; float: left;">
                                <h4 style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">
                                    Proveditelé</h4>
                                <?php

                                $type = 'realization_sauna';

                                $adminsquery = $mysqli->query("SELECT id, user_name FROM demands WHERE role != 'client' AND active = 1");
                                while ($admins = mysqli_fetch_array($adminsquery)) {

                                    $find_query = $mysqli->query("SELECT admin_id FROM mails_recievers WHERE type_id = '" . $getclient['id'] . "' AND admin_id = '" . $admins['id'] . "' AND type = '".$type."' AND reciever_type = 'performer'") or die($mysqli->error);

                                    ?><div class="col-sm-3" style="padding: 0 6px 0 12px;">

                                    <input id="real-admin-<?= $admins['id'] ?>-performer" name="performer[]" value="<?= $admins['id'] ?>" type="checkbox" <?php if (mysqli_num_rows($find_query) > 0) { echo 'checked'; } ?>>
                                    <label for="real-admin-<?= $admins['id'] ?>-performer" style="padding-left: 4px; cursor: pointer; <?php if(mysqli_num_rows($find_query) > 0){ echo 'color: green !important;'; }?>"><?= $admins['user_name'] ?></label>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>


                        <div class="form-group" style="padding: 0; margin: 0;">
                            <div class="well admins_well" style="padding: 12px 0px 7px; width: 100%; margin: 10px 0 0; float: left;">
                                <h4 style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">
                                    Informovaní</h4>
                                <?php



                                $adminsquery = $mysqli->query("SELECT id, user_name, role FROM demands WHERE role != 'client' AND active = 1");
                                while ($admins = mysqli_fetch_array($adminsquery)) {

                                    $find_query = $mysqli->query("SELECT admin_id FROM mails_recievers WHERE type_id = '" . $getclient['id'] . "' AND admin_id = '" . $admins['id'] . "' AND type = '".$type."' AND reciever_type = 'observer'") or die($mysqli->error);

                                    ?><div class="col-sm-3" style="padding: 0 6px 0 12px;">

                                    <input id="real-admin-<?= $admins['id'] ?>-observer" name="observer[]" value="<?= $admins['id'] ?>" type="checkbox" <?php if (mysqli_num_rows($find_query) > 0 || $admins['role'] == 'salesman-technician') { echo 'checked'; } ?>>
                                    <label for="real-admin-<?= $admins['id'] ?>-observer" style="padding-left: 4px; cursor: pointer; <?php if(mysqli_num_rows($find_query) > 0){ echo 'color: green !important;'; }?>"><?= $admins['user_name'] ?></label>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>

                        <input type="text" name="customer" value="0" style="display:none;">
                        <input type="text" name="id" value="<?= $getclient['id'] ?>" style="display:none;">
                    </div>

                    <div class="modal-footer" style="text-align:left;">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>
                        <button type="submit" class="btn btn-green btn-icon icon-left" style="float: right;">Naplánovat
                            <i class="entypo-pencil"></i></button>
                    </div>

                </form>
            </div>
        </div>
    </div>

<?php } ?>

