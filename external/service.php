<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";

if(!empty($_REQUEST['serv'])){

    $service_query = $mysqli->query("SELECT * FROM services WHERE md5(id) = '".$_REQUEST['serv']."'")or die($mysqli->error);

    if(mysqli_num_rows($service_query) > 0){

        $service = mysqli_fetch_assoc($service_query);

        $pagetitle = $service['id']." - Fotografie servisu";

        include INCLUDES . "/head.php";

        ?>
    <body class="page-body white" style="background-color: #e6e6e6; height: auto;">

        <div class="page-container"  style="width: 94%; margin: 20px auto 0; height: auto; ">

            <div class="main-content" style="padding: 5px;">

                <hr>
                <div class="row col-sm-12">

                    <h3 style="margin-bottom: 16px; margin-top: 0;">Fotografie & videa k servisu <?= $service['id'] ?>:</h3>

                    <div class="col-sm-6 well">
                        <div id="service_pictures" class="lightgallery">

                            <?php

                            $files = array_map('basename', glob($_SERVER['DOCUMENT_ROOT'] . '/admin/data/images/services/' . $service['id'] . '/*.{'.extList($image_extensions).'}', GLOB_BRACE));

                            if (!empty($files)) {
                                foreach ($files as $file) {

                                    // skip thumbs
                                    if(substr( $file, 0, 6 ) === "small_"){ continue; }

                                    if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/admin/data/images/services/" . $service['id'] . "/small_" . $file)) {

                                        $full_image = "/admin/data/images/services/" . $service['id'] . "/" . $file;
                                        $small_image = "/admin/data/images/services/" . $service['id'] . "/small_" . $file;

                                    } else {

                                        $full_image = "/admin/data/images/services/" . $service['id'] . "/" . $file;
                                        $small_image = $full_image;

                                    }
                                    ?>
                                    <div class="single-picture" style="width: 19%; margin: 10px 0.5% 10px 0; display: inline-block;border: 1px solid #dfdfdf;border-radius: 4px;">
                                        <a class="remove-picture btn btn-sm btn-danger" style="position: absolute; border: 1px solid #FFF; border-radius: 3px;" data-picture="<?= basename($file) ?>" data-toggle="tooltip" data-placement="top" title="" data-original-title="Odstranit obrázek">
                                            <i class="entypo-trash"></i>
                                        </a>
                                        <a class="full" data-src="<?= $full_image ?>" rel="realization">
                                            <img src="<?= $small_image ?>" width="100%" class="img-rounded">
                                        </a>
                                    </div>

                                    <?php

                                }

                            }else{

                                echo 'žádné fotografie';
                            }

                            ?>
                        </div>

                        <hr>
                        <div id="service_videos">

                            <?php

                            $files = array_map('basename', glob($_SERVER['DOCUMENT_ROOT'] . '/admin/data/images/services/' . $service['id'] . '/*.{mp4,mkv,avi}', GLOB_BRACE));

                            if (!empty($files)) {
                                foreach ($files as $file) {

                                    $full_image = "/admin/data/images/services/" . $service['id'] . "/" . $file;

                                    ?>
                                    <div class="single-picture" style="width: 19%; margin: 10px 0.5% 10px 0; display: inline-block;border: 1px solid #dfdfdf;border-radius: 4px;">
                                        <a class="remove-picture btn btn-sm btn-danger" style="position: absolute; border: 1px solid #FFF; border-radius: 3px;" data-picture="<?= basename($file) ?>" data-toggle="tooltip" data-placement="top" title="" data-original-title="Odstranit video">
                                            <i class="entypo-trash"></i>
                                        </a>
                                        <a href="<?= $full_image ?>" rel="realization" target="_blank">
                                            <i class="entypo-video" style="font-size: 80px;"></i>
                                        </a>
                                    </div>

                                    <?php

                                }

                            }else{

                                echo 'žádné videa';
                            }

                            ?>
                        </div>

                    </div>
                    <div class="col-sm-6">

                        <form action="/admin/controllers/uploads/upload-file-service?id=<?= $service['id'] ?>" class="dropzone-previews dropzone" id="drop-this" style="min-height: 230px; height: 230px;">
                            <div class="fallback">
                                <input name="file" type="file" multiple />
                            </div>
                        </form>

                    </div>

                    
                </div>

                <?php 

                $demand_query = $mysqli->query("SELECT secretstring, user_name, id FROM demands WHERE id = '".$service['clientid']."'")or die($mysqli->error);

                ?>

                <div class="row col-sm-12">

                    
                        <hr>
            

                    <?php if(mysqli_num_rows($demand_query) > 0){ $demand = mysqli_fetch_assoc($demand_query); ?>

                        <h3 style="margin-bottom: 16px; margin-top: 0;">Fotografie k poptávce: <?= $demand['user_name'] ?></h3>


                    <div class="col-sm-6 well">
                        
                    <?php

            $files = array_map('basename', glob($_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/technical/' . $demand['secretstring'] . '/*.{'.extList($image_extensions).'}', GLOB_BRACE));

            ?>

                <h3>Fotografie technické</h3>
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
                        <div class="alert alert-warning">
                                U poptávky není žádná technická fotografie.
                            </div>
                    <?php } ?>
                </ul>

            </div>
                    </div>
            <div class="col-sm-6 well">
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
                            <div class="alert alert-warning">
                                U poptávky není žádná fotografie z realizace.
                            </div>
                        <?php } ?>
                        <div class="clear"></div>
                    </ul>

                </div>

            </div>


                    <?php }else{ ?>

                        <div class="col-sm-12">
                            <div class="alert alert-warning">
                                <strong>Upozornění!</strong> K servisu není přiřazena poptávka, nelze tedy zobrazit fotografie.
                            </div>
                        </div>

                    <?php } ?>  

                    
                </div>

                <?php
                $time = microtime();
                $time = explode(' ', $time);
                $time = $time[1] + $time[0];
                $finish = $time;
                $total_time = round(($finish - $start), 4);

                ?>
                <footer class="main">
                    Wellness Trade, s.r.o. &copy; <?= date("Y") ?> <span style=" float:right;"><?= 'Page generated in ' . $total_time . ' seconds.' ?></span>
                </footer>	</div>
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