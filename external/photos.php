<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";

if(!empty($_REQUEST['cc'])){

    $demand_query = $mysqli->query("SELECT secretstring, user_name, id FROM demands WHERE secretstring = '".$_REQUEST['cc']."'")or die($mysqli->error);

    if(mysqli_num_rows($demand_query) > 0){

        $demand = mysqli_fetch_assoc($demand_query);

        $pagetitle = $demand['user_name']." - Technické fotografie";

        include INCLUDES . "/head.php";

    ?>
<body class="page-body white" style="background-color: #e6e6e6; height: auto;">

    <div class="page-container"  style="width: 94%; margin: 20px auto 0; height: auto; ">

        <div class="main-content" style="padding: 5px;">

            <?php

            $files = array_map('basename', glob($_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/technical/' . $demand['secretstring'] . '/*.{'.extList($image_extensions).'}', GLOB_BRACE));

            ?>
            <div id="load-technical" class="notes-list" <?php if (empty($files)) { ?>style="padding:
                                        23px 0 22px;" <?php } ?>>

                <ul class="list-of-notes lightgallery" style="padding: 0;">
                    <?php
                    if (!empty($files)) {
                        foreach ($files as $file) {



                            // skip thumbs
                            if(substr( $file, 0, 6 ) === "small_"){ continue; }

                            if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/technical/' . $demand['secretstring'] . '/small_' . $file)) {

                                $full_image = '/data/clients/pictures/technical/' . $demand['secretstring'] . '/' . $file;
                                $small_image = '/data/clients/pictures/technical/' . $demand['secretstring'] . '/small_' . $file;

                            } else {

                                $full_image = '/data/clients/pictures/technical/' . $demand['secretstring'] . '/' . $file;
                                $small_image = $full_image;

                            } ?>

                            <div class="single-picture col-xs-12 col-sm-6 col-md-4 col-lg-2" style="display: inline-block; padding: 10px;">
                                <a data-src="<?= $full_image ?>" href="<?= $full_image ?>" class="full" rel="technical" style="display: block; border: 1px solid #dfdfdf;border-radius: 4px;">
                                    <img src="<?= $small_image ?>" width="100%" class="img-rounded">
                                </a>
                            </div>

                            <?php
                        }
                    } else { ?>
                        <ul class="cbp_tmtimeline">
                            <li style=" width: 100%;">

                                <div class="cbp_tmicon" style="margin-left: -1px;">
                                    <i class="entypo-block"
                                       style="line-height: 42px !important;"></i>
                                </div>

                                <div class="cbp_tmlabel empty" style="margin-top: -29px; margin-bottom: 0px;padding-top: 9px;">
                                            <span style="font-weight: bold; margin-left: -12px;font-size: 17px;">U poptávky ještě nejsou žádné obrázky.</span>
                                </div>
                            </li>
                        </ul>
                    <?php } ?>
                    <div class="clear"></div>
                </ul>

                <hr>

                <div class="col-sm-12">
                    <?php if(file_exists($_SERVER['DOCUMENT_ROOT'].'/admin/data/demands/protocols/Predavaci_protokol_v_'.$demand['id'].'.pdf')){ ?>
                    <a href="/admin/data/demands/protocols/Predavaci_protokol_v_<?= $demand['id'] ?>.pdf?t=<?= $currentDate->getTimestamp() ?>" class="btn btn-lg btn-info btn-icon icon-left" target="_blank">
                        <i class="entypo-doc-text"></i>
                        Předávací protokol
                    </a>
                    <?php } else { ?>

                        <p class="text-warning">Předávací protokol nebyl vystaven.</p>

                    <?php } ?>
                </div>
            </div>
            <hr>
            <div class="col-sm-6">
                <h3>Nahrání fotografií realizace</h3>
                <form action="/admin/controllers/uploads/upload-file-poptavka?id=<?= $demand['id'] ?>&type=realization" class="dropzone-previews dropzone" id="drop-this" style="min-height: 230px; height: 230px;">
                    <div class="fallback">
                        <input name="file" type="file" multiple />
                    </div>
                </form>
            </div>
            <div class="col-sm-6">
                <h3>Fotografie realizace</h3>


                <?php

                $files = array_map('basename', glob($_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/realization/' . $demand['secretstring'] . '/*.{'.extList($image_extensions).'}', GLOB_BRACE));
                ?>
                <div id="load-realization" class="notes-list" <?php if (empty($files)) { ?>style="padding:
                                        23px 0 22px;" <?php } ?>>

                    <ul class="list-of-notes lightgallery">
                        <?php

                        if (!empty($files)) {
                            foreach ($files as $file) {

                                // skip thumbs
                                if(substr( $file, 0, 6 ) === "small_"){ continue; }

                                if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/realization/' . $demand['secretstring'] . '/small_' . $file)) {

                                    $full_image = '/data/clients/pictures/realization/' . $demand['secretstring'] . '/' . $file;
                                    $small_image = '/data/clients/pictures/realization/' . $demand['secretstring'] . '/small_' . $file;

                                } else {

                                    $full_image = '/data/clients/pictures/realization/' . $demand['secretstring'] . '/' . $file;
                                    $small_image = $full_image;

                                } ?>


                                <div class="single-picture" style="width: 19%; margin: 10px 0.5% 10px 0; display: inline-block;border: 1px solid #dfdfdf;border-radius: 4px;">
                                    <a data-src="<?= $full_image ?>" href="<?= $full_image ?>" class="full" rel="realization">
                                        <img src="<?= $small_image ?>" width="100%" class="img-rounded">
                                    </a>
                                </div>


                                <?php
                            }
                        } else { ?>
                            <ul class="cbp_tmtimeline">
                                <li style=" width: 100%;">

                                    <div class="cbp_tmicon" style="margin-left: -1px;">
                                        <i class="entypo-block"
                                           style="line-height: 42px !important;"></i>
                                    </div>

                                    <div class="cbp_tmlabel empty" style="margin-top: -29px; margin-bottom: 0px;padding-top: 9px;">
                                                        <span style="font-weight: bold; margin-left: -12px;font-size: 17px;">U poptávky ještě nejsou žádné obrázky.</span>
                                    </div>
                                </li>
                            </ul>
                        <?php } ?>
                        <div class="clear"></div>
                    </ul>

                </div>

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


            const myDropzone = new Dropzone('form#drop-this', {

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

} ?>