<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

if (isset($_REQUEST['customer'])) {$customer = $_REQUEST['customer'];}
if (isset($_REQUEST['state'])) {$state = $_REQUEST['state'];}

$pagetitle = "Realizace vířivek";



include VIEW . '/default/header.php';

?>

<div class="row">
  <div class="col-md-8 col-sm-7">
    <h2><?= $pagetitle ?></h2>
  </div>


    <div class="col-md-4">
        <div class="btn-group" style="text-align: right; float: right;">

            <a href="realizace-virivek"><label class="btn btn-md <?php if (!isset($state)) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                    Vše
                </label></a>

            <a href="?state=0"><label class="btn btn-md <?php if (isset($state) && $state == "0") { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                    Plánované
                </label></a>
            <a href="?state=2"><label class="btn btn-md <?php if (isset($state) && $state == "2") { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                    V řešení
                </label></a>
            <a href="?state=1"><label class="btn btn-md <?php if (isset($state) && $state == "1") { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                    Potvrzené
                </label></a>



        </div>

    </div>

</div>

<div id="table-2_wrapper" class="dataTables_wrapper form-inline" role="grid"><table class="table table-bordered table-striped datatable dataTable" id="table-2" aria-describedby="table-2_info">
  <thead>
    <tr role="row">

      <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" style="width: 220px; text-align: center;">Zákazník</th>
      <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" style="width: 220px; text-align: center;">Adresa</th>
<!--      <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" style="width: 240px; text-align: center;">Telefon</th>-->
      <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" style="width: 220px; text-align: center;">Typ vířivky</th>
      <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" style="width: 140px; text-align: center;">Číslo</th>
      <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" style="width: 240px; text-align: center;">Poznámka u vířivky</th>
        <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" style="width: 240px; text-align: center;">Termín realizace</th>

        <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" style="width: 240px; text-align: center;">Termín dodání</th>
        <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" style="width: 800px; text-align: center;">Poznámky</th>
      <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" style="width: 100px; text-align: center;">Akce</th>

  </thead>



<tbody role="alert" aria-live="polite" aria-relevant="all">
<?php
$confirmed = '';
if(isset($state)){

    $confirmed = 'AND d.confirmed = '.$state;

}

$realization_query = $mysqli->query("SELECT *, d.customer as customer, d.id as id, DATE_FORMAT(d.date, '%d. %m. %Y') as dateformated, DATE_FORMAT(d.realization, '%d. %m. %Y') as realizationformated FROM demands d, warehouse_products p, demands_specs_bridge b WHERE b.specs_id = 5 AND b.client_id = d.id AND d.status < 7 AND p.connect_name = d.product AND d.status = 4 AND d.customer = 1 $confirmed ORDER BY d.realization ASC") or die($mysqli->error);

while ($realization = mysqli_fetch_array($realization_query)) {

    $hottub_query = $mysqli->query("SELECT *, DATE_FORMAT(loadingdate, '%d. %m. %Y') as loadingformated FROM warehouse WHERE demand_id = '" . $realization['id'] . "'") or die($mysqli->error);
    $hottub = mysqli_fetch_array($hottub_query);

    $billing_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $realization['shipping_id'] . '" WHERE b.id = "' . $realization['billing_id'] . '"') or die($mysqli->error);
    $billing = mysqli_fetch_assoc($billing_query);

    ?>
<tr class="even">

      <td class=" " style="text-align: center; color: #373e4a;"><a href="../demands/zobrazit-poptavku?id=<?= $realization['id'] ?>"><strong><?php
    echo $realization['user_name'];

?></strong><hr style="margin: 4px 0;"><?php
              if (isset($realization['phone']) && $realization['phone'] != "" && $realization['phone'] != 0) {

                  echo number_format($realization['phone'], 0, ',', ' ');

              } else {
                  echo "";
              }
              ?></a></td>
      <td class=" " style="text-align: center; color: #373e4a;"><?php
          address($billing);
          ?></td>
<!--      <td class=" " style="text-align: center; color: #373e4a;">--><?//
//
//          if (isset($realization['phone']) && $realization['phone'] != "" && $realization['phone'] != 0) {
//
//              echo number_format($realization['phone'], 0, ',', ' ');
//
//          } else {echo "";}?><!--</td>-->
      <td class=" " style="text-align: center; color: #373e4a;"><?= ucfirst($realization['fullname']) . ' ' . $realization['value'] ?></td>
      <td class=" " style="text-align: center; color: #373e4a;"><strong><?= $hottub['serial_number'] ?></strong></td>
      <td class=" " style="font-size: 11px;"><?= $hottub['description'] ?></td>



    <td class=" " style="text-align: center; color: #373e4a;"><?php

        if (!empty($realization['realization']) && $realization['realization'] != '0000-00-00') {

            if((isset($realization['realization']) && $realization['realization'] != '0000-00-00') && (isset($hottub['loadingdate']) && $hottub['loadingdate'] != '0000-00-00') && strtotime($hottub['loadingdate']) > strtotime($realization['realization'])){

                echo '<strong class="text-danger"><i class="entypo-cancel-circled"></i> '.$realization['realizationformated'].'</strong>';

            }elseif((!isset($realization['realization']) || $realization['realization'] == '0000-00-00') || (!isset($hottub['loadingdate']) || $hottub['loadingdate'] == '0000-00-00')){

                echo '<strong class="text-info"><i class="entypo-info"></i> '.$realization['realizationformated'].'</strong>';

          }else{

                echo '<strong class="text-success"><i class="fa fa-check"></i> '.$realization['realizationformated'].'</strong>';

            }


        }else{

            echo '<strong class="text-info"><i class="entypo-attention"></i> není</strong>';

        } ?>

    <hr style="margin: 4px 0;">
        <?php if($realization['confirmed'] == 2){ ?>
        <span style="color: #FF9933;">
            v řešení
        </span>
        <?php }elseif($realization['realization'] != '0000-00-00' && $realization['confirmed'] == 0){ ?>
        <span style="color: #21d1e1;">
            plánovaná
        </span>
        <?php }elseif($realization['confirmed'] == 1){ ?>
        <span style="color: #00a651;">
            potvrzená
        </span>
        <?php } ?>
    </td>


    <td class=" " style="text-align: center; color: #373e4a;">
        <?php

        if (!empty($hottub['loadingdate']) && $hottub['loadingdate'] != '0000-00-00') {

            if((isset($realization['realization']) && $realization['realization'] != '0000-00-00') && (isset($hottub['loadingdate']) && $hottub['loadingdate'] != '0000-00-00') && strtotime($hottub['loadingdate']) > strtotime($realization['realization'])){

                echo '<strong class="text-danger"><i class="entypo-cancel-circled"></i> '.$hottub['loadingformated'].'</strong>';

            }elseif((!isset($realization['realization']) || $realization['realization'] == '0000-00-00') || (!isset($hottub['loadingdate']) || $hottub['loadingdate'] == '0000-00-00')){

                echo '<strong class="text-info"><i class="entypo-info"></i> '.$hottub['loadingformated'].'</strong>';

            }else{

                echo '<strong class="text-success"><i class="fa fa-check"></i> '.$hottub['loadingformated'].'</strong>';

            }


        }else{

            echo '<strong class="text-info"><i class="entypo-attention"></i> není</strong>';

        } ?>
        <hr style="margin: 4px 0;">
<br>
    </td>

    <td class=" " style="font-size: 11px;"><?= $realization['description'] ?></td>



      <td class=" " style="text-align: right;">

        <a href="../demands/zobrazit-poptavku?id=<?= $realization['id'] ?>" class="btn btn-primary btn-sm btn-icon icon-left">
          <i class="entypo-search"></i>
          Poptávka
        </a>
        <?php if (isset($hottub['id'])) { ?><br>
         <a href="../accessories/zobrazit-virivku?id=<?= $hottub['id'] ?>" class="btn btn-primary btn-sm btn-icon icon-left" style="margin-top: 4px;">
          <i class="entypo-search"></i>
          Vířivka
        </a>
        <?php } ?>
      </td>
    </tr>

    <?php
}

?>
</tbody></table></div>




<footer class="main">


  &copy; <?= date("Y") ?> <span style=" float:right;"><?php
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$total_time = round(($finish - $start), 4);

echo 'PHP '.PHP_VERSION.' | Page generated in ' . $total_time . ' seconds.';?></span>

</footer> </div>



  </div>

<?php include VIEW . '/default/footer.php'; ?>

