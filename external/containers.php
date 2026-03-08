<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";

$salt = "oijahsfdapsf80efdjnsdjp";

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'login'){

    $password = md5($salt.$_POST['password']);
    if($password === md5($salt.'XMkbClYqQl') || $password === md5($salt.'alEnDaNDLe') || $password === md5($salt.'aeSDIoewfh')){

        setcookie("external_pass", $password, time() + 60 * 60 * 24 * 30, "/");
        Header("Location:https://www.wellnesstrade.cz/admin/external/containers");

    }else{

        Header("Location:https://www.wellnesstrade.cz/admin/external/containers?password=wrong");

    }

    exit;
}

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'logout'){

    setcookie("external_pass", '', time() + 60 * 60 * 24 * 30, "/");
    unset($_COOKIE['external_pass']);

    Header("Location:https://www.wellnesstrade.cz/admin/external/containers");
    exit;

}

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'set_info'){

    $mysqli->query("UPDATE containers SET date_loading = '" . $_POST['date_loading'] . "', date_lead = '" . $_POST['date_lead'] . "', container_number = '" . $_POST['container_number'] . "' WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

    Header("Location:https://www.wellnesstrade.cz/admin/external/containers");
    exit;
}


if(!empty($_COOKIE['external_pass']) && ($_COOKIE['external_pass'] == md5($salt.'XMkbClYqQl') || $_COOKIE['external_pass'] == md5($salt.'alEnDaNDLe') || $_COOKIE['external_pass'] == md5($salt.'aeSDIoewfh'))){

    if($_COOKIE['external_pass'] == md5($salt.'XMkbClYqQl')) {

        $brand = 'IQue';
        $link_secret = 'IQU_bgewKD';

    }elseif($_COOKIE['external_pass'] == md5($salt.'alEnDaNDLe')){

        $brand = 'Lovia';
        $link_secret = 'LOV_qJcUBZ';

    }elseif($_COOKIE['external_pass'] == md5($salt.'aeSDIoewfh')){

        $brand = 'Quantum';
        $link_secret = 'QUA_jEjsaI';

    }elseif($_COOKIE['external_pass'] == md5($salt.'AEJaoewiaA')){

        $brand = 'Pergola';
        $link_secret = 'PER_JoifaE';

    }elseif($_COOKIE['external_pass'] == md5($salt.'kEonFEIAJp')){

        $brand = 'Espoo Smart';
        $link_secret = 'ESP_fjFSoe';

    }elseif($_COOKIE['external_pass'] == md5($salt.'DAIemPOOEN')){

        $brand = 'Espoo Deluxe';
        $link_secret = 'ESS_kSpaeKL';

    }

    setcookie("external_pass", $_COOKIE['external_pass'], time() + 60 * 60 * 24 * 30, "/");

//include INCLUDES . "/googlelogin.php";
include INCLUDES . "/functions.php";


if (isset($_REQUEST['action']) && $_REQUEST['action'] == "add_file") {

    if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/admin/data/containers/" . $link_secret . "/" . $_REQUEST['id'])) {
        mkdir($_SERVER['DOCUMENT_ROOT'] . "/admin/data/containers/" . $link_secret . "/" . $_REQUEST['id'], 0777, true);

    }

    $filename = $_FILES["zmrdus"]["name"];

    $file_ext = substr($filename, strripos($filename, '.'));

    $path = $_SERVER['DOCUMENT_ROOT'] . "/admin/data/containers/" . $link_secret . "/" . $_REQUEST['id'] . "/" . $_REQUEST['type'] . $file_ext;
    move_uploaded_file($_FILES['zmrdus']['tmp_name'], $path);


    header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/editace-kontejneru?success=add_file');
    exit;

}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "add_file_hottub") {

    if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/admin/data/containers/" . $link_secret . "/" . $_REQUEST['id'] . "/" . $_REQUEST['hottub_id'])) {
        mkdir($_SERVER['DOCUMENT_ROOT'] . "/admin/data/containers/" . $link_secret . "/" . $_REQUEST['id'] . "/" . $_REQUEST['hottub_id'], 0777, true);

    }

    $filename = $_FILES["fileinput"]["name"];

    $file_ext = substr($filename, strripos($filename, '.'));

    $path = $_SERVER['DOCUMENT_ROOT'] . "/admin/data/containers/" . $link_secret . "/" . $_REQUEST['id'] . "/" . $_REQUEST['hottub_id'] . "/" . $filename;
    move_uploaded_file($_FILES['fileinput']['tmp_name'], $path);

    header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/editace-kontejneru?success=add_file');
    exit;

}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "remove_file") {

    $path = $_SERVER['DOCUMENT_ROOT'] . "/admin/data/containers/" . $link_secret . "/" . $_REQUEST['id'] . "/" . $_REQUEST['name'];

    if (file_exists($path)) {

        unlink($path);

    }


    header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/editace-kontejneru?success=remove_file');
    exit;
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "remove_file_hottub") {

    $path = $_SERVER['DOCUMENT_ROOT'] . "/admin/data/containers/" . $link_secret . "/" . $_REQUEST['id'] . "/" . $_REQUEST['hottub_id'] . "/" . $_REQUEST['name'];

    if (file_exists($path)) {

        unlink($path);

    }

    header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/editace-kontejneru?success=remove_file');
    exit;
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "add_codes") {

    $container_products = $mysqli->query("SELECT *, DATE_FORMAT(date_created, '%d. %m. %Y') as dateformated FROM containers_products WHERE container_id = '" . $_REQUEST['id'] . "' ORDER BY id desc") or die($mysqli->error);
    while ($cont_product = mysqli_fetch_array($container_products)) {

        if (empty($cont_product['warehouse_id'])) {

            $serial_number = "";
            $price = "";

            $serial_number = $_POST['value-' . $cont_product['id']];
            $price = $_POST['price-' . $cont_product['id']];

            if ($serial_number != '') {

                $has_serial = true;

                $insert = $mysqli->query("INSERT INTO warehouse (product, status, demand_id, customer, serial_number, created_date, purchase_price, location_id) VALUES ('" . $cont_product['product'] . "','0','" . $cont_product['demand_id'] . "','1','$serial_number', now(), '$price', '" . $_POST['location_id'] . "')") or die($mysqli->error);
                $hottub_id = $mysqli->insert_id;

                $specsquery = $mysqli->query("SELECT specs_id, value FROM containers_products_specs_bridge WHERE client_id = '" . $cont_product['id'] . "'") or die($mysqli->error);
                while ($specs = mysqli_fetch_array($specsquery)) {

                    $insert_specs = $mysqli->query("INSERT INTO warehouse_specs_bridge (client_id, specs_id, value)
			  VALUES ('$hottub_id', '" . $specs['specs_id'] . "', '" . $specs['value'] . "')") or die($mysqli->error);

                    if($specs['specs_id'] == '5'){ $type = $specs['value']; }

                }


                // ONLY WAREHOUSE SPECS

                $choosed_hottub = $cont_product['product'];

                $get_ids = $mysqli->query("SELECT w.id as id, w.name as name FROM warehouse_products_types w, warehouse_products p WHERE w.warehouse_product_id = p.id AND p.connect_name = '$choosed_hottub' AND w.name = '".$type."'") or die($mysqli->error);
                $get_id = mysqli_fetch_array($get_ids);

                $specs_query = $mysqli->query("SELECT *, s.id as id FROM specs s, warehouse_products_types_specs w WHERE w.spec_id = s.id AND w.type_id = '" . $get_id['id'] . "' AND s.supplier != 1 AND s.warehouse_spec = 1 GROUP BY s.id") or die($mysqli->error);

                while ($specs = mysqli_fetch_array($specs_query)) {

                    $mysqli->query("INSERT INTO warehouse_specs_bridge (value, client_id, specs_id) VALUES ('". $value ."','" . $hottub_id . "','" . $specs['id'] . "')") or die($mysqli->error);



                    // getting param id

                    if (isset($specs['type']) && $specs['type'] == 1) {

                        $paramsquery = $mysqli->query("SELECT *, p.id as id FROM specs_params p, warehouse_products_types_specs w  WHERE p.spec_id = '" . $specs['id'] . "' AND w.spec_param_id = p.id AND w.type_id = '" . $get_id['id'] . "' AND p.option = '".$value."' GROUP by p.id") or die($mysqli->error);

                        $param = mysqli_fetch_array($paramsquery);

                        $param_id = $param['id'];

                    } else {

                        if ($value == 'Ano') { $param_id = 1; } else { $param_id = 0;}

                    }


                    // warehouse accessories

                    $products_check = $mysqli->query("SELECT * FROM demands_products WHERE spec_id = '" . $specs['id'] . "' AND param_id = '" . $param_id . "' AND type = '" . $choosed_hottub . "'") or die($mysqli->error);

                    // selected param is equal to desired param for accessory assignment
                    if (mysqli_num_rows($products_check) > 0) {

                        $product = mysqli_fetch_array($products_check);

                        $mysqli->query("INSERT INTO warehouse_products_bridge (warehouse_id, spec_id, product_id, variation_id, quantity, reserved, location_id) VALUES ('" . $hottub_id . "', '" . $specs['id'] . "', '" . $product['product_id'] . "', '" . $product['variation_id'] . "', '1', '1', '" . $_POST['location_id'] . "')") or die($mysqli->error);

                    }


                }

                /// END SPECS END SPECS END SPECS END SPECS


                $update = $mysqli->query("UPDATE containers_products SET warehouse_id = '$hottub_id' WHERE id = '" . $cont_product['id'] . "'") or die($mysqli->error);

            }

        }

    }


    if ($has_serial) {

        $now = date("Y-m-d", strtotime("now"));

        $container_query = $mysqli->query("SELECT size, container_name FROM containers WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);
        $container = mysqli_fetch_array($container_query);

        if (isset($container['size']) && $container['size'] == '7') {

            $estimated = date("Y-m-d", strtotime("+53 days", strtotime($now)));

            $correction = date("Y-m-d", strtotime("+28 days", strtotime($now)));

        } elseif (isset($container['size']) && $container['size'] == '14') {

            $estimated = date("Y-m-d", strtotime("+77 days", strtotime($now)));

            $correction = date("Y-m-d", strtotime("+42 days", strtotime($now)));

        }

        $update = $mysqli->query("UPDATE containers SET closed = '2', date_due = '$estimated', location_id = '" . $_POST['location_id'] . "', date_shipped = '$correction'  WHERE id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);


        $title = 'Naskladnění kontejneru ' . $container['container_name'];

        $mysqli->query("INSERT INTO dashboard_texts (title, container_id, date, enddate) values ('$title', '" . $_REQUEST['id'] . "','$estimated','$estimated')") or die($mysqli->error);

        $id = $mysqli->insert_id;

        $mysqli->query("DELETE FROM mails_recievers WHERE type_id = '$id' AND type = 'event'") or die($mysqli->error);

        $recievers_query = $mysqli->query("SELECT id, user_name, dimension, email FROM demands WHERE (role <> 'client' AND role <> 'admin') AND dimension != ''") or die($mysqli->error);
        while ($reciever = mysqli_fetch_array($recievers_query)) {
            $performersArray[] = $reciever['id'];
        }

        if (!empty($performersArray)) {

            recievers($performersArray, $observersArray, 'event', $id);

        }

        saveCalendarEvent($id, 'event');

    }

    header('location: https://www.wellnesstrade.cz/admin/pages/warehouse/editace-kontejneru?success=add_codes');
    exit;

}

if (isset($_REQUEST['od'])) {$od = $_REQUEST['od'];}

$pagetitle = "Containers";

include INCLUDES . "/head.php";


function supplier_container($containers){

    global $mysqli;
    global $currentDate;

    $double_join = $mysqli->query("SELECT c.user_name as creator_user_name, e.user_name as editor_user_name FROM demands c LEFT JOIN demands  e ON e.id = '" . $containers['editor_id'] . "' LEFT JOIN containers_products p ON p.container_id = '" . $containers['id'] . "' WHERE c.id = '" . $containers['creator_id'] . "'");
    $double = mysqli_fetch_array($double_join);

    $container_products = $mysqli->query("SELECT p.*, DATE_FORMAT(p.date_created, '%d. %m. %Y') as dateformated, d.user_name,  d.id as demand_id, w.serial_number as warehouse_number, creator.user_name as creator_user_name, editor.user_name as editor_user_name FROM containers_products p LEFT JOIN demands d ON d.id = p.demand_id LEFT JOIN warehouse w ON w.id = p.warehouse_id LEFT JOIN demands  creator ON creator.id = p.creator_id LEFT JOIN demands editor ON editor.id = p.editor_id WHERE p.container_id = '" . $containers['id'] . "' ORDER BY id desc") or die($mysqli->error);

    $total_products = mysqli_num_rows($container_products);
    ?>
    <div class="member-entry" style="margin-bottom: 0px;" >

        <div class="member-img" style="width: 30%;">

            <h3 style="margin: 0;">Container #<?php if ($containers['container_name'] != "") {echo $containers['container_name'];} else {echo $containers['id_brand'];}?>

<?php
                if ($containers['date_due'] != '0000-00-00' && $containers['date_correction'] == 0 && $containers['closed'] == 2) {

                    ?><button class="btn btn-orange btn-sm">in production - ETA <strong><?= $containers['due_formated'] ?></strong></button><?php

                } elseif ($containers['date_due'] != '0000-00-00' && $containers['date_correction'] == 1 && $containers['closed'] == 2) {

                    ?><button class="btn btn-blue btn-sm">shipped - ETA <strong><?= $containers['due_formated'] ?></strong></button><?php

                }

                ?>
            </h3>

        </div>
        <div class="member-details" style="width: 70%;">

            <div class="col-sm-10">
                <span class=" btn btn-default btn-md">
                    <i class="entypo-popup"></i>
                    <a>Size: <strong>[<?= $total_products . '/' . $containers['size'] ?>]</strong></a>
                </span>

          <?php if(!empty($containers['container_number'])){ ?>
              <span class=" btn btn-default btn-md">
                  <i class="entypo-doc-text"></i>
                  <a>Container Number: <strong><?= $containers['container_number'] ?> </strong></a>
              </span>
        <?php }

             if($containers['date_lead'] != '0000-00-00'){ ?>
                  <span class=" btn btn-default btn-md">
                      <i class="fa fa-industry"></i>
                      <a>Factory Lead Time: <strong><?= $containers['date_lead'] ?></strong></a>
                  </span>
            <?php }

             if($containers['date_loading'] != '0000-00-00'){ ?>
                  <span class=" btn btn-default btn-md">
                      <i class="fa fa-truck"></i>
                      <a>Loading Date: <strong><?= $containers['date_loading'] ?></strong></a>
                  </span>
            <?php } ?>

            </div>
            <div class="col-sm-10" style="float:right; text-align: right;margin-top: 14px;padding-right: 0px;">
                <a class="btn btn-primary btn-sm btn-icon icon-left show-container" data-container="<?= $containers['id'] ?>">
                    <i class="entypo-search"></i>
                    View Container
                </a>

                <span style=" border-right: 1px solid #cccccc; margin: 0 11px 0 8px;"></span>


                <a data-id="<?= $containers['id'] ?>" class="toggle-info-modal btn btn-primary btn-sm btn-icon icon-left">
                    <i class="entypo-pencil"></i>
                    Add Container Number & Loading Date
                </a>

                <span style=" border-right: 1px solid #cccccc; margin: 0 11px 0 8px;"></span>

                <a href="/admin/controllers/generators/containers-table.php?id=<?= $containers['id'] ?>" target="_blank" class="btn btn-blue btn-sm btn-icon icon-left">
                    <i class="entypo-search"></i>
                    View Export
                </a>

            </div>
        </div>
        <div style="clear: both;"></div>


        <div id="container-holder-<?= $containers['id'] ?>"></div>

        <div id="container-<?= $containers['id'] ?>" <?php if ($containers['closed'] > '0') { ?>style="display: none;"<?php } ?>>

            <hr />

        </div>

    </div>
    <hr style="border-color: #e2e2e5; border-style: dashed; margin: 10px 0;">
    <?php

}
?>

    <style>

        @media only screen and (max-width: 900px) {

            .info-list .well.col-sm-3 {
                width: 49% !important;
                min-height: 860px;
            }

            .file {
                width: 18% !important;
                min-height: 160px !important;
                margin-bottom: 7px !important;
            }

        }


    </style>

    <script type="text/javascript">


        // main files
        $(document).on('submit', '.file_form', function(event) {

            //disable the default form submission
            event.preventDefault();


            const file = $(this).find('input[type="file"]').val().trim(); // consider giving this an id too


            if(file){

                const id = $(this).data("id");
                const type = $(this).data("type");
                const container_id = $(this).data("container");

                const main = '#' + container_id + "-" + id;


                const formData = new FormData($(this)[0]);

                $(main + " .holder").hide();
                $(main).append('<div style="background-color: #FFF;"><img class="loading" src="https://www.wellnesstrade.cz/admin/assets/images/loader_backinout.gif" width="100%"><img class="done" src="https://www.wellnesstrade.cz/admin/assets/images/tick-confirmed.gif" width="100%" style="display: none;"></div>').fadeIn(400);

                const url = "?id="+container_id+"&action=add_file&type="+type;

                $.ajax({
                    type: "POST",
                    url: url,
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(data) {

                        $(main + " .loading").fadeOut(400, function() {
                            $(main + " .done").fadeIn(400);
                        });

                        setTimeout(function() {

                            $(main + " .holder").load("/admin/controllers/modals/container?id="+container_id+" #"+container_id+"-" + id + " .holder > *", function() {

                                $(main + " .done").fadeOut(400, function(){
                                    $(main + " .holder").fadeIn(400)
                                });

                            });

                        }, 2000);



                    },
                    error: function(){
                        alert("error in ajax form submission");
                    }
                });


            }else{
                alert('No file attached');
            }

            return false; // avoid to execute the actual submit of the form.
        });




        $(document).on('click', '.remove-file', function() {

            var name = $(this).data("name");
            var id = $(this).data("id");
            var container_id = $(this).data("container");

            $.get( "?id="+container_id+"&action=remove_file&name=" + name )
                .done(function( data ) {

                    $("#"+container_id+"-"+id+" .holder").fadeOut(300, function(){

                        $("#"+container_id+"-" + id + " .holder").load("/admin/controllers/modals/container?id="+container_id+" #"+container_id+"-" + id + " .holder > *", function() {

                            $(this).fadeIn(400)

                        });;

                    });


                });


        });

        // main files end



        $(document).on('submit', '.file_form_hottub', function(event) {

            //disable the default form submission
            event.preventDefault();

            const file = $(this).find('input[type="file"]').val().trim(); // consider giving this an id too

            if(file){

                const id = $(this).data("id");
                const formData = new FormData($(this)[0]);
                const container_id = $(this).data("container");

                const mainHolder = "#pdf-" + id;


                $(mainHolder + " .pdf-inner-holder").hide();

                $(mainHolder).append('<img class="loading" src="https://www.wellnesstrade.cz/admin/assets/images/loader-small.gif" height="36" style="float: right;">').fadeIn(400);

                const url = "?id="+container_id+"&action=add_file_hottub&hottub_id="+id;

                $.ajax({
                    type: "POST",
                    url: url,
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(data) {

                        setTimeout(function() {

                            $(mainHolder + " .pdf-inner-holder").load("/admin/controllers/modals/container?id="+container_id+" #pdf-"+id+" > *", function() {

                                $(mainHolder + " .loading").fadeOut(400, function(){
                                    $(mainHolder + " .pdf-inner-holder").fadeIn(400)
                                });

                            });

                        }, 2000);



                    },
                    error: function(){
                        alert("error in ajax form submission");
                    }
                });


            }else{
                alert('No file attached');
            }

            return false; // avoid to execute the actual submit of the form.
        });



        $(document).on('click', '.remove_file_hottub', function() {

            var name = $(this).data("name");
            var id = $(this).data("id");
            const container_id = $(this).data("container");

            $.get( "?id="+container_id+"&action=remove_file_hottub&hottub_id="+id+"&name=" + name )
                .done(function( data ) {

                    $("#pdf-" + id +" .pdf-inner-holder").fadeOut(300, function(){

                        $("#pdf-" + id +" .pdf-inner-holder").load("/admin/controllers/modals/container?id="+container_id+" #pdf-" + id +" .pdf-inner-holder > *", function() {

                            $(this).fadeIn(400)

                        });;

                    });


                });


        });

    </script>
<body class="page-body white" style="background-color: #e6e6e6;">

<link rel="stylesheet" href="<?= $home ?>/admin/assets/js/vertical-timeline/css/component.css">
<div class="page-container"  style="width: 96%; margin: 80px auto; "><!-- add class "sidebar-collapsed" to close sidebar by default, "chat-visible" to make chat appear always -->

    <div class="main-content" style="padding: 20px;">



        <?php

        $currentpage = "containers";

        if (!isset($od) || $od == 1) {


            ?>

            <div class="row">
                <div class="col-md-4">
                    <h2 style="margin-top: 20px;">Unfinished Containers</h2>
                </div>
                <div class="col-md-8" style="text-align: right;">
                    <a href="?action=logout" class="btn btn-primary btn-md">Logout</a>
                </div>
            </div>
        <?php
            $containers_query = $mysqli->query("SELECT *, DATE_FORMAT(date_created, '%d. %m. %Y') as dateformated, DATE_FORMAT(date_due, '%d. %m. %Y') as due_formated FROM containers WHERE closed != 3 AND brand = '".$brand."' order by id desc") or die($mysqli->error);

        if (mysqli_num_rows($containers_query) > 0) {
            mysqli_data_seek($containers_query, 0);
            while ($containers = mysqli_fetch_assoc($containers_query)) {

                supplier_container($containers);

            }} else { ?>
            <ul class="cbp_tmtimeline" style=" margin-left: 25px;">
                <li style="margin-top: 80px;">

                    <div class="cbp_tmicon">
                        <i class="entypo-block" style="line-height: 42px !important;"></i>
                    </div>

                    <div class="cbp_tmlabel empty" style="padding-top: 9px;">
                        <span><a style="font-weight: bold; margin-left: -12px;font-size: 17px;">Bohužel tomuto filtru neodpovídá žádný kontejner.</a></span>
                    </div>
                </li>
            </ul>
            <?php
        }

        }

        $perpage = 12;
        $containers_max_query = $mysqli->query("SELECT COUNT(*) AS NumberOfOrders FROM containers WHERE closed = '3' AND brand = '".$brand."' order by id") or die($mysqli->error);
        $containers_max = mysqli_fetch_array($containers_max_query);
        $max = $containers_max['NumberOfOrders'];
        if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {$od = 1;}

        $s_lol = $od - 1;
        $s_pocet = $s_lol * $perpage;
        $pocet_prispevku = $max;

        $containers_query = $mysqli->query("SELECT *, DATE_FORMAT(date_created, '%d. %m. %Y') as dateformated, DATE_FORMAT(date_due, '%d. %m. %Y') as due_formated FROM containers WHERE closed = '3' AND brand = '".$brand."' order by id desc limit " . $s_pocet . "," . $perpage) or die($mysqli->error);
        ?>
        <div class="row">
            <div class="col-md-4">
                <h2 style="margin-top: 20px;">Finished Containers</h2>
            </div>

            <div class="col-md-4">
                <center><ul class="pagination pagination-sm">
                        <?php
                        include VIEW . "/default/pagination.php";?>
                    </ul>

                </center>
            </div>

        </div>

        <?php

        if (mysqli_num_rows($containers_query) > 0) {
            mysqli_data_seek($containers_query, 0);
            while ($containers = mysqli_fetch_assoc($containers_query)) {

                supplier_container($containers);

            }} else { ?>
            <ul class="cbp_tmtimeline" style=" margin-left: 25px;">
                <li style="margin-top: 80px;">

                    <div class="cbp_tmicon">
                        <i class="entypo-block" style="line-height: 42px !important;"></i>
                    </div>

                    <div class="cbp_tmlabel empty" style="padding-top: 9px;">
                        <span><a style="font-weight: bold; margin-left: -12px;font-size: 17px;">Bohužel tomuto filtru neodpovídá žádný kontejner.</a></span>
                    </div>
                </li>
            </ul>
            <?php
        }

        ?>




        <!-- Pager for search results --><div class="row">
            <div class="col-md-12">
                <center><ul class="pagination pagination-sm">
                        <?php
                        include VIEW . "/default/pagination.php";?>
                    </ul>

                    <h1 style="margin-bottom: 50px;">Total: <?= $max ?></h1>
                </center>
            </div>
        </div><!-- Footer -->





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

<script type="text/javascript">
    $(document).ready(function(){
        $(".toggle-info-modal").click(function(e){

            $('#info-modal').removeData('bs.modal');
            e.preventDefault();

            var id = $(this).data("id");

            $("#info-modal").modal({

                remote: '/admin/external/modal-container-info.php?id='+id,
            });
        });
    });
</script>

<div class="modal fade" id="info-modal" aria-hidden="true" style="display: none; margin-top: 8%;">

</div>

<script type="text/javascript">

    $(".show-container").click(function(){

        var container = $(this).data("container");

        if ( $('#container-holder-'+container).text().length == 0 ) {

            $("#container-holder-"+container).load("/admin/external/container-load?id="+container);

        }else{

            $('#container-holder-'+container).text('');

        };

    });

</script>


<div class="modal fade" id="picture-upload-modal" aria-hidden="true" style="display: none; top: 8%;">
    <div class="modal-dialog" style="width: 800px;">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">Nahrání obrázků</h4>
            </div>

            <div class="modal-body">
                <form action="#" class="dropzone" id="dropzone_upload">
                    <div class="fallback">
                        <input name="file" type="file" multiple />
                    </div>
                </form>


                <div id="pictures-result">

                </div>
            </div>

            <div class="modal-footer" style="text-align:left;">
                <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>
                <button type="button" class="btn btn-success btn-icon icon-left" id="done-picture-upload" data-dismiss="modal" style="float: right;">Hotovo <i class="entypo-check"></i></button>
            </div>

        </div>
    </div>
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


<script src="<?= $home ?>/admin/assets/js/jquery.validate.min.js"></script>

<link rel="stylesheet" href="https://www.wellnesstrade.cz/admin/assets/fonts/fa/css/all.min.css">

<link rel="stylesheet" href="<?= $home ?>/admin/assets/js/dropzone/dropzone.css">
<link rel="stylesheet" href="<?= $home ?>/admin/assets/js/select2/select2-bootstrap.css">
<link rel="stylesheet" href="<?= $home ?>/admin/assets/js/select2/select2.css">
<link rel="stylesheet" href="<?= $home ?>/admin/assets/js/selectboxit/jquery.selectBoxIt.css">
<link rel="stylesheet" href="<?= $home ?>/admin/assets/js/daterangepicker/daterangepicker-bs3.css">
<link rel="stylesheet" href="<?= $home ?>/admin/assets/js/icheck/skins/minimal/_all.css">
<link rel="stylesheet" href="<?= $home ?>/admin/assets/js/icheck/skins/square/_all.css">
<link rel="stylesheet" href="<?= $home ?>/admin/assets/js/icheck/skins/flat/_all.css">
<link rel="stylesheet" href="<?= $home ?>/admin/assets/js/icheck/skins/futurico/futurico.css">
<link rel="stylesheet" href="<?= $home ?>/admin/assets/js/icheck/skins/polaris/polaris.css">
<!-- Bottom Scripts -->
<script src="<?= $home ?>/admin/assets/js/gsap/TweenMax.min.js"></script>
<script src="<?= $home ?>/admin/assets/js/gsap/main-gsap.js"></script>
<script src="<?= $home ?>/admin/assets/js/jquery-ui/js/jquery-ui-1.10.3.minimal.min.js"></script>
<script src="<?= $home ?>/admin/assets/js/bootstrap.js"></script>
<script src="<?= $home ?>/admin/assets/js/joinable.js"></script>
<script src="<?= $home ?>/admin/assets/js/resizeable.js"></script>
<script src="<?= $home ?>/admin/assets/js/neon-api.js"></script>
<script src="<?= $home ?>/admin/assets/js/select2/select2.min.js"></script>
<script src="<?= $home ?>/admin/assets/js/typeahead.min.js"></script>
<script src="<?= $home ?>/admin/assets/js/selectboxit/jquery.selectBoxIt.min.js"></script>
<script src="<?= $home ?>/admin/assets/js/bootstrap-datepicker.js"></script>
<script src="<?= $home ?>/admin/assets/js/bootstrap-timepicker.min.js"></script>
<script src="<?= $home ?>/admin/assets/js/bootstrap-colorpicker.min.js"></script>
<script src="<?= $home ?>/admin/assets/js/daterangepicker/moment.min.js"></script>


<script src="<?= $home ?>/admin/assets/js/dropzone/dropzone.js"></script>
<script src="<?= $home ?>/admin/assets/js/daterangepicker/daterangepicker.js"></script>
<script src="<?= $home ?>/admin/assets/js/jquery.multi-select.js"></script>
<script src="<?= $home ?>/admin/assets/js/icheck/icheck.min.js"></script>
<script src="<?= $home ?>/admin/assets/js/neon-chat.js"></script>
<script src="<?= $home ?>/admin/assets/js/neon-custom.js"></script>
<script src="<?= $home ?>/admin/assets/js/neon-demo.js"></script>

<script type="text/javascript">
    $(document).ready(function() {

        $('.lightgallery').lightGallery({
            selector: 'a.full'
        });

    });
</script>

</body>
</html>


<?php } else {

    $pagetitle = "Containers - Login";

    include INCLUDES . "/head.php";


    ?>
    <body class="page-body login-page login-form-fall">


    <!-- This is needed when you send requests via Ajax --><script type="text/javascript">
        var baseurl = '';
    </script>

    <div class="login-container">

        <div class="login-header login-caret">

            <div class="login-content">

                <a href="/" class="logo">
                    <img src="<?= $home ?>/admin/assets/images/logo@2x.png" width="320" alt="" />
                </a>

                <p class="description">detail-oriented whirlpools and saunas</p>

                <!-- progress bar indicator -->
                <div class="login-progressbar-indicator">
                    <h3>43%</h3>
                    <span>loggin...</span>
                </div>
            </div>

        </div>

        <div class="login-progressbar">
            <div></div>
        </div>

        <div class="login-form">

            <div class="login-content">

                <div class="form-login-error" <?php if(isset($_REQUEST['password']) && $_REQUEST['password'] == 'wrong'){ echo 'style="display: block;"'; }?>>
                    <h3>Wrong Password!</h3>
                </div>

                <form method="post" role="form" action="?action=login" enctype="multipart/form-data">

                    <div class="form-group">

                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="entypo-key"></i>
                            </div>

                            <input type="password" class="form-control" name="password" id="password" placeholder="Password" autocomplete="off" style="background: transparent !important;"/>
                        </div>

                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block btn-login">
                            <i class="entypo-login"></i>
                            Login
                        </button>
                    </div>

                </form>


                <div class="login-bottom-links">

                </div>


            </div>

        </div>

    </div>

    <div class="footer-logos">
        <a href="https://www.spahouse.cz"><img src="<?= $home ?>/admin/assets/images/spahouse-logo-footer.png" alt="Spahouse"></a>
        <a href="https://www.saunahouse.cz"><img src="<?= $home ?>/admin/assets/images/saunahouse-footlogo.png"  style="margin-bottom: -6px;" alt="Saunahouse"></a>
    </div>

    <!-- Bottom Scripts -->
    <script src="<?= $home ?>/admin/assets/js/gsap/main-gsap.js"></script>
    <script src="<?= $home ?>/admin/assets/js/jquery-ui/js/jquery-ui-1.10.3.minimal.min.js"></script>
    <script src="<?= $home ?>/admin/assets/js/bootstrap.js"></script>
    <script src="<?= $home ?>/admin/assets/js/joinable.js"></script>
    <script src="<?= $home ?>/admin/assets/js/resizeable.js"></script>
    <script src="<?= $home ?>/admin/assets/js/neon-api.js"></script>
    <script src="<?= $home ?>/admin/assets/js/jquery.validate.min.js"></script>
    <script src="<?= $home ?>/admin/assets/js/neon-login.js"></script>
    <script src="<?= $home ?>/admin/assets/js/neon-custom.js"></script>
    <script src="<?= $home ?>/admin/assets/js/neon-demo.js"></script>

    </body>
    </html>


<?php } ?>