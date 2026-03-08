<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

if (isset($_REQUEST['customer'])) {$customer = $_REQUEST['customer'];}
if (isset($_REQUEST['category'])) {$category = $_REQUEST['category'];}
if (isset($_REQUEST['state'])) { $state = $_REQUEST['state'];}

if (isset($_REQUEST['od'])) {$od = $_REQUEST['od'];}
if (isset($_REQUEST['q'])) {$search = $_REQUEST['q'];}

if (isset($search) && $search != "") {

    $pagetitle = 'Hledaný výraz "' . $search . '"';

    $bread1 = $currentPage['name'];
    $abread1 = $currentPage['seo_url'];

} else {

    $pagetitle = $currentPage['name'];

}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "change_state") {

    $mysqli->query("UPDATE demands_preparations SET state = '".$_REQUEST['state']."' WHERE id =  '". $_REQUEST['id'] . "'") or die($mysqli->error);

    header('location: https://www.wellnesstrade.cz/admin/pages/demands/editace-priprav?success=change_state');
    exit;
}


    include VIEW . '/default/header.php';

    if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {$od = 1;}
    $perpage = 60;
    $s_lol = $od - 1;
    $s_pocet = $s_lol * $perpage;


//    $link_year = '';
    $query = '';
    if(isset($state)){

        $query .= ' WHERE state = '.$state;
//        $link_year .= '&year='.$state;

    }

    if (isset($search) && $search != "") {

        $parts = explode(" ", $search);
        $last = array_pop($parts);
        $first = implode(" ", $parts);

        if ($first == "") {
            $first = 0;
        }
        if ($last == "") {
            $last = 0;
        }

        $pocet_prispevku = 0;

        // todo select

    } else {

        $maxQuery = $mysqli->query('SELECT COUNT(*) AS NumberOfOrders FROM demands_preparations $query') or die($mysqli->error);
        $max = mysqli_fetch_array($maxQuery);

        $pocet_prispevku = $max['NumberOfOrders'];

        $dataQuery = $mysqli->query("SELECT * FROM demands_preparations $query ORDER BY id DESC LIMIT " . $s_pocet . ', ' . $perpage) or die($mysqli->error);

    }?>
    <div class="row">
        <div class="col-md-3 col-sm-3">
            <h2><?php if(empty($search)) {

                    echo $pagetitle;

                }else{ echo 'Hledanému výrazu <i><u>"'.$search.'"</u></i> odpovídájí tyto výsledky:'; } ?></h2>
        </div>
        <div class="col-md-2">
            <center><ul class="pagination pagination-sm">
                    <?php
                    include VIEW . "/default/pagination.php";?>
                </ul>

            </center>
        </div>
        <div class="col-md-7" style="text-align: right;  margin: 17px 0;">

<!--            <form method="get" role="form" style="float: right;">-->
<!---->
<!--                <div class="form-group">-->
<!--                    <div style="margin-bottom: 12px; width: 260px; float:left; margin-left: 10px;margin-right: 4px;"><input id="cheart" value="--><?// if(!empty($search)) { echo $search; } ?><!--" type="text" name="q" class="form-control" placeholder="Hledání..." /></div>-->
<!---->
<!--                    <button style="width: 50px; float:left;" type="submit" class="btn btn-default"><i style=" position: relative; right: 0; top: 0;" class="entypo-search"></i></button>-->
<!--                </div>-->
<!---->
<!--            </form>-->
<!---->

            <a href="./editace-priprav" class="btn btn-md <?= !isset($state) ? 'btn-primary' : 'btn-default' ?>" style="margin-right: 6px;">Vše</a>
            <a href="?state=0" class="btn btn-md <?= isset($state) && $state == 0 ? 'btn-primary' : 'btn-default' ?>" style="margin-right: 6px;">Nezpracované</a>
            <a href="?state=1" class="btn btn-md <?= isset($state) && $state == 1 ? 'btn-primary' : 'btn-default' ?>">Zpracované</a>
        </div>
    </div>



    <?php
    if (mysqli_num_rows($dataQuery) > 0) { ?>

        <table class="table table-bordered table-striped datatable dataTable">
            <thead>
            <tr>
                <th width="" class="text-center">ID</th>
                <th width="" class="text-center">Datum</th>
                <th width="" class="text-center">Status</th>
                <th width="" class="text-center">E-mail</th>
                <th width="" class="text-center">Telefon</th>
                <th width="" class="text-center">Typ</th>
                <th width="" class="text-center">Produkt</th>
                <th width="" class="text-center">Provedení</th>
                <th width="" class="text-center">Zpráva</th>
                <th width="160px" class="text-center">Akce</th>
            </tr>
            </thead>

            <tbody role="alert" aria-live="polite" aria-relevant="all">
            <?php
            while ($data = mysqli_fetch_array($dataQuery)) {

                $datetime = date('d. m. Y H:i:s', strtotime($data['datetime']));

                ?>
                <tr class="even">
                    <td class="text-center">
                        <?= $data['id']; ?>
                    </td>

                    <td class="text-center">
                        <?= $datetime; ?>
                    </td>

                    <td class="text-center">
                        <?php if(!$data['state']){ ?>
                            <span class="circle-color red"></span> nezpracovaná
                        <?php }else{ ?>
                            <span class="circle-color green"></span> zpracovaná
                        <?php } ?>
                    </td>

                    <td class="text-center">
                        <strong><?= $data['email'] ?></strong>
                    </td>

                    <td class="text-center">
                        <strong><?= $data['phone'] ?></strong>
                    </td>


                    <td class="text-center">
                        <?= $data['customer'] == 1 ? 'Vířivka' : 'Sauna' ?>
                    </td>

                    <td class="text-center">
                        <?= ucfirst($data['product']) ?>
                    </td>

                    <td class="text-center">
                        <?= $data['type'] ?>
                    </td>

                    <td class="text-center">
                        <i><?= $data['message'] ?></i>
                    </td>

                    <td style="text-align: center;">
                        <?php
                        if(isset($data['state']) && $data['state']){ ?>
                            <a href="?action=change_state&id=<?= $data['id'] ?>&state=0" class="btn btn-danger">
                                <i class="entypo-back"></i>
                            </a>
                        <?php }else{ ?>
                            <a href="?action=change_state&id=<?= $data['id'] ?>&state=1" class="btn btn-success">
                                <i class="entypo-check"></i>
                            </a>
                        <?php } ?>
                    </td>

                </tr>
                <?php

            }?>

            </tbody>

        </table>

    <?php } else { ?>


        <ul class="cbp_tmtimeline" style="margin-left: 25px;  margin-top: 50px;">
            <li style="margin-top: 80px;">

                <div class="cbp_tmicon">
                    <i class="entypo-block" style="line-height: 42px !important;"></i>
                </div>

                <div class="cbp_tmlabel empty" style="padding-top: 9px;">
                    <span><a style="font-weight: bold; margin-left: -12px;font-size: 17px;">Bohužel tomuto filtru neodpovídá žádný výsledek.</a></span>
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
                </ul></center>
        </div></div>

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


<style>

    .page-body .selectboxit-container .selectboxit-options { margin-top: 40px !important; width: 100% !important;}
    .page-body .selectboxit-container .selectboxit { height: 40px;width: 100% !important;}
    .page-body .selectboxit-container .selectboxit .selectboxit-text { line-height: 40px; }
    .page-body .selectboxit-container .selectboxit .selectboxit-arrow-container { height: 40px;}
    .page-body .selectboxit-container .selectboxit .selectboxit-arrow-container:after { line-height: 40px;}
</style>


<script type="text/javascript">
    $(document).ready(function(){
        $(".toggle-modal-remove").click(function(e){

            $('#remove-modal').removeData('bs.modal');
            e.preventDefault();


            var type = $(this).data("type");

            var id = $(this).data("id");

            $("#remove-modal").modal({

                remote: 'controllers/modals/modal-remove.php?id='+id+'&type='+type,
            });
        });
    });
</script>

<div class="modal fade" id="remove-modal" aria-hidden="true" style="display: none; margin-top: 160px;">

</div>

<script type="text/javascript">
    $(document).ready(function(){
        $(".toggle-modal-change-status").click(function(e){

            $('#change-status-modal').removeData('bs.modal');
            e.preventDefault();


            var id = $(this).data("id");

            $("#change-status-modal").modal({

                remote: 'controllers/modals/modal-change-status-data.php?id='+id,
            });
        });
    });
</script>

<div class="modal fade" id="change-status-modal" aria-hidden="true" style="display: none; margin-top: 3%;">

</div>



<?php include VIEW . '/default/footer.php'; ?>

