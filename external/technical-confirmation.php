<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";
include INCLUDES . "/functions.php";

if(!empty($_REQUEST['cc'])){

    $demand_query = $mysqli->query("SELECT secretstring, user_name, id, DATE_FORMAT(realization, '%M') as realization_month,  DATE_FORMAT(realization, '%Y') as realization_year, realization, address_confirmed, realization_confirmed, billing_id, shipping_id FROM demands WHERE secretstring = '".$_REQUEST['cc']."'")or die($mysqli->error);

    if(mysqli_num_rows($demand_query) > 0){

        if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'confirm_realization'){

            $mysqli->query("UPDATE demands SET realization_confirmed = 1 WHERE secretstring = '".$_REQUEST['cc']."'")or die($mysqli->error);

            header('location: https://www.wellnesstrade.cz/admin/external/technical-confirmation?cc='.$_REQUEST['cc'].'&success=confirm_realization');
            exit;
        }

        if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'confirm_address'){

            $mysqli->query("UPDATE demands SET address_confirmed = 1 WHERE secretstring = '".$_REQUEST['cc']."'")or die($mysqli->error);

            header('location: https://www.wellnesstrade.cz/admin/external/technical-confirmation?cc='.$_REQUEST['cc'].'&success=confirm_address');
            exit;
        }


        $demand = mysqli_fetch_assoc($demand_query);

        $demand_address_query = $mysqli->query("SELECT * 
            FROM addresses_billing b 
                LEFT JOIN addresses_shipping s ON s.id = '" . $demand['shipping_id'] . "' 
                WHERE b.id = '" . $demand['billing_id'] . "'") or die($mysqli->error);
        $address = mysqli_fetch_assoc($demand_address_query);

        if(isset($demand['realization']) && $demand['realization'] != '0000-00-00'){

            $realization_date = datumCesky($demand['realization_month']).' '.$demand['realization_year'];

        }else{

            $realization_date = 'nestanoven';

        }


        $pagetitle = $demand['user_name']." - Technická připravenost";

        include INCLUDES . "/head.php";

        ?>
        <body class="page-body white" style="background-color: #e6e6e6; height: auto;">

        <div class="page-container"  style="width: 94%; margin: 20px auto 0; height: auto; ">

            <div class="main-content" style="padding: 20px 10px;">

                <div class="col-md-4">

                    <h4>Fotografie chrániče (30 mA)</h4>
                    <small>&nbsp;</small>
                    <form action="/admin/controllers/uploads/upload-file-technical?cc=<?= $_REQUEST['cc'] ?>&type=chranic" class="dropzone-previews dropzone" id="chranic" style="min-height: 230px; height: 230px; overflow: hidden;">
                        <div class="fallback">
                            <input name="file" type="file" multiple />
                        </div>
                    </form>

                    <?php

                    $files = array_map('basename', glob($_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/chranic/' . $demand['secretstring'] . '/*.{'.extList($image_extensions).'}', GLOB_BRACE));
                    ?>
                    <div id="load-chranic" class="notes-list" <?php if (empty($files)) { ?>style="padding:
                                        23px 0 22px;" <?php } ?>>

                        <ul class="list-of-notes lightgallery">
                            <?php

                            if (!empty($files)) {
                                foreach ($files as $file) {

                                    // skip thumbs
                                    if(substr( $file, 0, 6 ) === "small_"){ continue; }

                                    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/chranic/' . $demand['secretstring'] . '/small_' . $file)) {

                                        $full_image = '/data/clients/pictures/chranic/' . $demand['secretstring'] . '/' . $file;
                                        $small_image = '/data/clients/pictures/chranic/' . $demand['secretstring'] . '/small_' . $file;

                                    } else {

                                        $full_image = '/data/clients/pictures/chranic/' . $demand['secretstring'] . '/' . $file;
                                        $small_image = $full_image;

                                    } ?>


                                    <div class="single-picture" style="width: 19%; margin: 10px 0.5% 10px 0; display: inline-block;border: 1px solid #dfdfdf;border-radius: 4px;">
                                        <a data-src="<?= $full_image ?>" href="<?= $full_image ?>" class="full" rel="chranic">
                                            <img src="<?= $small_image ?>" width="100%" class="img-rounded">
                                        </a>
                                    </div>


                                    <?php
                                }
                            } else { ?>
                                <ul class="cbp_tmtimeline">
                                    <li style=" width: 100%;">

                                        <div class="cbp_tmicon">
                                            <i class="entypo-block"
                                               style="line-height: 42px !important;"></i>
                                        </div>

                                        <div class="cbp_tmlabel empty" style="margin-top: -29px; margin-bottom: 0px;padding-top: 9px;">
                                            <span class="text-danger" style="font-weight: bold; margin-left: -12px;font-size: 15px;">Zatím nejsou nahrány žádné fotografie.</span>
                                        </div>
                                    </li>
                                </ul>
                            <?php } ?>
                            <div class="clear"></div>
                        </ul>

                    </div>
                    <hr>

                </div>

                <div class="col-md-4">
                    <h4>Fotografie jističe (3 fázový jistič C16A)</h4>
                    <small>&nbsp;</small>
                    <form action="/admin/controllers/uploads/upload-file-technical?cc=<?= $_REQUEST['cc'] ?>&type=jistic" class="dropzone-previews dropzone" id="jistic" style="min-height: 230px; height: 230px; overflow: hidden;">
                        <div class="fallback">
                            <input name="file" type="file" multiple />
                        </div>
                    </form>
                    <?php

                    $files = array_map('basename', glob($_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/jistic/' . $demand['secretstring'] . '/*.{'.extList($image_extensions).'}', GLOB_BRACE));
                    ?>
                    <div id="load-jistic" class="notes-list" <?php if (empty($files)) { ?>style="padding:
                                        23px 0 22px;" <?php } ?>>

                        <ul class="list-of-notes lightgallery">
                            <?php

                            if (!empty($files)) {
                                foreach ($files as $file) {

                                    // skip thumbs
                                    if(substr( $file, 0, 6 ) === "small_"){ continue; }

                                    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/jistic/' . $demand['secretstring'] . '/small_' . $file)) {

                                        $full_image = '/data/clients/pictures/jistic/' . $demand['secretstring'] . '/' . $file;
                                        $small_image = '/data/clients/pictures/jistic/' . $demand['secretstring'] . '/small_' . $file;

                                    } else {

                                        $full_image = '/data/clients/pictures/jistic/' . $demand['secretstring'] . '/' . $file;
                                        $small_image = $full_image;

                                    } ?>


                                    <div class="single-picture" style="width: 19%; margin: 10px 0.5% 10px 0; display: inline-block;border: 1px solid #dfdfdf;border-radius: 4px;">
                                        <a data-src="<?= $full_image ?>" href="<?= $full_image ?>" class="full" rel="jistic">
                                            <img src="<?= $small_image ?>" width="100%" class="img-rounded">
                                        </a>
                                    </div>


                                    <?php
                                }
                            } else { ?>
                                <ul class="cbp_tmtimeline">
                                    <li style=" width: 100%;">

                                        <div class="cbp_tmicon">
                                            <i class="entypo-block"
                                               style="line-height: 42px !important;"></i>
                                        </div>

                                        <div class="cbp_tmlabel empty" style="margin-top: -29px; margin-bottom: 0px;padding-top: 9px;">
                                            <span class="text-danger" style="font-weight: bold; margin-left: -12px;font-size: 15px;">Zatím nejsou nahrány žádné fotografie.</span>
                                        </div>
                                    </li>
                                </ul>
                            <?php } ?>
                            <div class="clear"></div>
                        </ul>

                    </div>
                    <hr>

                </div>

                <div class="col-md-4">
                    <h4>Fotografie kabelu (CYKY 5x2,5)</h4>
                    <small>z důvodu posouzení dostatečné délky (5m) pro zapojení vířivky</small>
                    <form action="/admin/controllers/uploads/upload-file-technical?cc=<?= $_REQUEST['cc'] ?>&type=kabel" class="dropzone-previews dropzone" id="kabel" style="min-height: 230px; height: 230px; overflow: hidden;">
                        <div class="fallback">
                            <input name="file" type="file" multiple />
                        </div>
                    </form>
                    <?php

                    $files = array_map('basename', glob($_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/kabel/' . $demand['secretstring'] . '/*.{'.extList($image_extensions).'}', GLOB_BRACE));
                    ?>
                    <div id="load-kabel" class="notes-list" <?php if (empty($files)) { ?>style="padding:
                                        23px 0 22px;" <?php } ?>>

                        <ul class="list-of-notes lightgallery">
                            <?php

                            if (!empty($files)) {
                                foreach ($files as $file) {

                                    // skip thumbs
                                    if(substr( $file, 0, 6 ) === "small_"){ continue; }

                                    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/kabel/' . $demand['secretstring'] . '/small_' . $file)) {

                                        $full_image = '/data/clients/pictures/kabel/' . $demand['secretstring'] . '/' . $file;
                                        $small_image = '/data/clients/pictures/kabel/' . $demand['secretstring'] . '/small_' . $file;

                                    } else {

                                        $full_image = '/data/clients/pictures/kabel/' . $demand['secretstring'] . '/' . $file;
                                        $small_image = $full_image;

                                    } ?>


                                    <div class="single-picture" style="width: 19%; margin: 10px 0.5% 10px 0; display: inline-block;border: 1px solid #dfdfdf;border-radius: 4px;">
                                        <a data-src="<?= $full_image ?>" href="<?= $full_image ?>" class="full" rel="kabel">
                                            <img src="<?= $small_image ?>" width="100%" class="img-rounded">
                                        </a>
                                    </div>


                                    <?php
                                }
                            } else { ?>
                                <ul class="cbp_tmtimeline">
                                    <li style=" width: 100%;">

                                        <div class="cbp_tmicon">
                                            <i class="entypo-block"
                                               style="line-height: 42px !important;"></i>
                                        </div>

                                        <div class="cbp_tmlabel empty" style="margin-top: -29px; margin-bottom: 0px;padding-top: 9px;">
                                            <span class="text-danger" style="font-weight: bold; margin-left: -12px;font-size: 15px;">Zatím nejsou nahrány žádné fotografie.</span>
                                        </div>
                                    </li>
                                </ul>
                            <?php } ?>
                            <div class="clear"></div>
                        </ul>

                    </div>
                    <hr>
                </div>

                <div class="col-md-4">
                    <h4>Fotografie průchodnosti cesty vířivky</h4>
                    <small>(terénní nerovnosti, průchody) od vjezdu až po její umístění (vzdálenost, místo připravené pro vířivku)</small>
                    <form action="/admin/controllers/uploads/upload-file-technical?cc=<?= $_REQUEST['cc'] ?>&type=pruchodnost" class="dropzone-previews dropzone" id="pruchodnost" style="min-height: 230px; height: 230px; overflow: hidden;">
                        <div class="fallback">
                            <input name="file" type="file" multiple />
                        </div>
                    </form>
                    <?php

                    $files = array_map('basename', glob($_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/pruchodnost/' . $demand['secretstring'] . '/*.{'.extList($image_extensions).'}', GLOB_BRACE));
                    ?>
                    <div id="load-pruchodnost" class="notes-list" <?php if (empty($files)) { ?>style="padding:
                                        23px 0 22px;" <?php } ?>>

                        <ul class="list-of-notes lightgallery">
                            <?php

                            if (!empty($files)) {
                                foreach ($files as $file) {

                                    // skip thumbs
                                    if(substr( $file, 0, 6 ) === "small_"){ continue; }

                                    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/pruchodnost/' . $demand['secretstring'] . '/small_' . $file)) {

                                        $full_image = '/data/clients/pictures/pruchodnost/' . $demand['secretstring'] . '/' . $file;
                                        $small_image = '/data/clients/pictures/pruchodnost/' . $demand['secretstring'] . '/small_' . $file;

                                    } else {

                                        $full_image = '/data/clients/pictures/pruchodnost/' . $demand['secretstring'] . '/' . $file;
                                        $small_image = $full_image;

                                    } ?>


                                    <div class="single-picture" style="width: 19%; margin: 10px 0.5% 10px 0; display: inline-block;border: 1px solid #dfdfdf;border-radius: 4px;">
                                        <a data-src="<?= $full_image ?>" href="<?= $full_image ?>" class="full" rel="pruchodnost">
                                            <img src="<?= $small_image ?>" width="100%" class="img-rounded">
                                        </a>
                                    </div>


                                    <?php
                                }
                            } else { ?>
                                <ul class="cbp_tmtimeline">
                                    <li style=" width: 100%;">

                                        <div class="cbp_tmicon">
                                            <i class="entypo-block"
                                               style="line-height: 42px !important;"></i>
                                        </div>

                                        <div class="cbp_tmlabel empty" style="margin-top: -29px; margin-bottom: 0px;padding-top: 9px;">
                                            <span class="text-danger" style="font-weight: bold; margin-left: -12px;font-size: 15px;">Zatím nejsou nahrány žádné fotografie.</span>
                                        </div>
                                    </li>
                                </ul>
                            <?php } ?>
                            <div class="clear"></div>
                        </ul>

                    </div>
                    <hr>

                </div>

                <div class="col-md-4">
                    <h4>Fotografie umístění</h4>
                    <small>&nbsp;</small>
                    <form action="/admin/controllers/uploads/upload-file-technical?cc=<?= $_REQUEST['cc'] ?>&type=umisteni" class="dropzone-previews dropzone" id="umisteni" style="min-height: 230px; height: 230px; overflow: hidden;">
                        <div class="fallback">
                            <input name="file" type="file" multiple />
                        </div>
                    </form>
                    <?php

                    $files = array_map('basename', glob($_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/eni" /' . $demand['secretstring'] . '/*.{'.extList($image_extensions).'}', GLOB_BRACE));
                    ?>
                    <div id="load-umisteni" class="notes-list" <?php if (empty($files)) { ?>style="padding:
                                        23px 0 22px;" <?php } ?>>

                        <ul class="list-of-notes lightgallery">
                            <?php

                            if (!empty($files)) {
                                foreach ($files as $file) {

                                    // skip thumbs
                                    if(substr( $file, 0, 6 ) === "small_"){ continue; }

                                    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/umisteni/' . $demand['secretstring'] . '/small_' . $file)) {

                                        $full_image = '/data/clients/pictures/umisteni/' . $demand['secretstring'] . '/' . $file;
                                        $small_image = '/data/clients/pictures/umisteni/' . $demand['secretstring'] . '/small_' . $file;

                                    } else {

                                        $full_image = '/data/clients/pictures/umisteni/' . $demand['secretstring'] . '/' . $file;
                                        $small_image = $full_image;

                                    } ?>


                                    <div class="single-picture" style="width: 19%; margin: 10px 0.5% 10px 0; display: inline-block;border: 1px solid #dfdfdf;border-radius: 4px;">
                                        <a data-src="<?= $full_image ?>" href="<?= $full_image ?>" class="full" rel="umisteni">
                                            <img src="<?= $small_image ?>" width="100%" class="img-rounded">
                                        </a>
                                    </div>


                                    <?php
                                }
                            } else { ?>
                                <ul class="cbp_tmtimeline">
                                    <li style=" width: 100%;">

                                        <div class="cbp_tmicon">
                                            <i class="entypo-block"
                                               style="line-height: 42px !important;"></i>
                                        </div>

                                        <div class="cbp_tmlabel empty" style="margin-top: -29px; margin-bottom: 0px;padding-top: 9px;">
                                            <span class="text-danger" style="font-weight: bold; margin-left: -12px;font-size: 15px;">Zatím nejsou nahrány žádné fotografie.</span>
                                        </div>
                                    </li>
                                </ul>
                            <?php } ?>
                            <div class="clear"></div>
                        </ul>

                    </div>
                    <hr>

                </div>
                <div class="col-md-4">
                    <h3>Potvrzení potřebných údajů</h3>
                    <hr>

                    <span style="font-size: 14px; color: #373e4a;">Předpokládaný měsíc realizace:</span> <br>
                    <h4 style="font-size: 18px"><span class="<?= $demand['realization_confirmed'] ? 'text-success' : 'text-danger' ?>"><?= $realization_date ?> - <?= $demand['realization_confirmed'] ? 'termín potvrzen' : 'termín nepotvrzen' ?></span></h4>
                    <?php if($realization_date != 'nestanoven' && $demand['realization_confirmed'] != 1){ ?>
                    <a class="btn btn-success" href="./technical-confirmation?action=confirm_realization&cc=<?= $_REQUEST['cc'] ?>">Potvrdit datum realizace</a>
                    <?php } ?>
                    <br>
                    <hr>
                    <br>
                    <span style="font-size: 14px; color: #373e4a;">Instalační adresa:</span> <br>
                    <h4 style="font-size: 18px"><span class="<?= $demand['address_confirmed'] ? 'text-success' : 'text-danger' ?>"><?= return_address($address) ?> - <?= $demand['address_confirmed'] ? 'adresa potvrzena' : 'adresa nepotvrzena' ?></span></h4>

                    <?php if($realization_date != 'adresa nezadána' && $demand['address_confirmed'] != 1){ ?>
                    <a class="btn btn-success" href="./technical-confirmation?action=confirm_address&cc=<?= $_REQUEST['cc'] ?>">Potvrdit instalační adresu</a>
                    <?php } ?>
                    <hr>

                </div>
                <div class="clear"></div>


                <?php
                $time = microtime();
                $time = explode(' ', $time);
                $time = $time[1] + $time[0];
                $finish = $time;
                $total_time = round(($finish - $start), 4);

                ?>
                <footer class="main">
                    Wellness Trade, s.r.o. &copy; <?= date("Y") ?> <span style=" float:right;"><?= 'Page generated in ' . $total_time . ' seconds.' ?></span>
                </footer>
            </div>


        </div>
        <script src="<?= $home ?>/admin/assets/js/jquery.validate.min.js"></script>

        <link rel="stylesheet" href="<?= $home ?>/admin/assets/js/vertical-timeline/css/component.css">
        <script src="<?= $home ?>/admin/assets/js/gsap/main-gsap.js"></script>
        <script src="<?= $home ?>/admin/assets/js/jquery-ui/js/jquery-ui-1.10.3.minimal.min.js"></script>
        <script src="<?= $home ?>/admin/assets/js/bootstrap.js"></script>

        <!--    <script src="--><?//echo $home; ?><!--/admin/assets/js/neon-custom.js"></script>-->
        <script src="<?= $home ?>/admin/assets/js/neon-demo.js"></script>


        <link rel="stylesheet" href="<?= $home ?>/admin/assets/js/dropzone/dropzone.css">
        <script src="<?= $home ?>/admin/assets/js/dropzone/dropzone.js"></script>

        <script type="text/javascript">
            $(document).ready(function() {

                $('.lightgallery').lightGallery({
                    selector: 'a.full'
                });

                Dropzone.autoDiscover = false;

                const myDropzone = new Dropzone('form#chranic', {
                    acceptedFiles: 'image/*',
                });

                const myDropzone = new Dropzone('form#jistic', {
                    acceptedFiles: 'image/*',
                });

                const myDropzone = new Dropzone('form#kabel', {
                    acceptedFiles: 'image/*',
                });

                const myDropzone = new Dropzone('form#pruchodnost', {
                    acceptedFiles: 'image/*',
                });

                const myDropzone = new Dropzone('form#umisteni', {
                    acceptedFiles: 'image/*',
                });


                /*

                myDropzone.on("complete", function (file) {

                    $("#hottub_pictures").load(location.href + " #hottub_pictures");

                });

                 */

            });
        </script>

        </body>
        </html>
        <?php

    }else{ echo 'Špatný tajný klientský kód!'; }

}else{ echo 'Špatný tajný klientský kód!'; }


?>