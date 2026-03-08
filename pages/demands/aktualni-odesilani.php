<?php
include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$pagetitle = "Aktuální odesílání";
$spesl = " - Poptávky";

$bread1 = "Poptávky";
$bread2 = "Maily";

if (isset($_REQUEST['customer'])) {$customer = $_REQUEST['customer'];}
if (isset($_REQUEST['category'])) {$category = $_REQUEST['category'];}

if (isset($_REQUEST['od'])) {$od = $_REQUEST['od'];}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "remove") {

    $mysqli->query('DELETE FROM demands_mails_templates WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);

    $displaysuccess = true;
    $successhlaska = "Šablona byla úspěšně smazána.";
}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "add") {
    $displaysuccess = true;
    $successhlaska = "Šablona byla úspěšně přidána.";

}

if (isset($_REQUEST['success']) && $_REQUEST['success'] == "edit") {
    $displaysuccess = true;
    $successhlaska = "Šablona byla úspěšně upravena.";

}

$virivkyquery = $mysqli->query("SELECT * FROM warehouse_products WHERE customer = 1 ORDER BY brand");

$saunyquery = $mysqli->query("SELECT * FROM warehouse_products WHERE customer = 0 ORDER BY code");

include VIEW . '/default/header.php';
?>

<div class="row">
	<div class="col-md-9 col-sm-7">
		<h2><?= $pagetitle ?></h2>
	</div>

	<div class="col-md-3 col-sm-5" style="text-align: right;float:right;">




	</div>
</div>
<!-- Pager for search results --><div class="row" style="margin-bottom: 24px; margin-top: 20px;">
	<div class="col-md-12">
		<div class="btn-group" style="text-align: left;">

						<a href="aktualni-odesilani"><label class="btn btn-white btn-lg <?php if (!isset($customer)) {echo 'active';}?>">
							Vše
						</label></a>
						<a href="?customer=1"><label class="btn btn-white btn-lg <?php if ($customer == 1) {echo 'active';}?>">
							Vířivky
						</label></a>

						<a href="?customer=0"><label class="btn btn-white btn-lg <?php if ($customer == 0 && isset($customer)) {echo 'active';}?>">
							Sauny
						</label></a>


					</div>
		</div>

</div><!-- Footer -->
<div id="table-2_wrapper" class="dataTables_wrapper form-inline" role="grid">



<?php

if (isset($customer) && isset($category)) {

    $currentpage = 'editace-manualu?customer=' . $customer . '&category=' . $category;

    $servismaxquery = $mysqli->query("SELECT COUNT(*) AS NumberOfOrders FROM demands WHERE product = '$category' AND notification != '0000-00-00' order by id desc") or die($mysqli->error);
    $servismaxquery = mysqli_fetch_array($servismaxquery);
    $max = $servismaxquery['NumberOfOrders'];
    if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {$od = 1;}
    $perpage = 12;

    $s_lol = $od - 1;
    $s_pocet = $s_lol * $perpage;
    $pocet_prispevku = $max;
    $demandsquery = $mysqli->query("SELECT * FROM demands WHERE product = '$category' AND notification != '0000-00-00' order by id desc limit " . $s_pocet . "," . $perpage) or die($mysqli->error);

} elseif (isset($customer)) {

    $currentpage = 'editace-manualu?customer=' . $customer;

    $servismaxquery = $mysqli->query("SELECT COUNT(*) AS NumberOfOrders FROM demands WHERE customer = '$customer' AND notification != '0000-00-00'") or die($mysqli->error);
    $servismaxquery = mysqli_fetch_array($servismaxquery);
    $max = $servismaxquery['NumberOfOrders'];
    if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {$od = 1;}
    $perpage = 12;

    $s_lol = $od - 1;
    $s_pocet = $s_lol * $perpage;
    $pocet_prispevku = $max;
    $demandsquery = $mysqli->query("SELECT * FROM demands WHERE customer = '$customer' AND notification != '0000-00-00' order by id desc limit " . $s_pocet . "," . $perpage) or die($mysqli->error);

} elseif (isset($category)) {

    $currentpage = 'editace-manualu?category=' . $category;

    $servismaxquery = $mysqli->query("SELECT COUNT(*) AS NumberOfOrders FROM demands WHERE product = '$category' AND notification != '0000-00-00'") or die($mysqli->error);
    $servismaxquery = mysqli_fetch_array($servismaxquery);
    $max = $servismaxquery['NumberOfOrders'];
    if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {$od = 1;}
    $perpage = 12;

    $s_lol = $od - 1;
    $s_pocet = $s_lol * $perpage;
    $pocet_prispevku = $max;
    $demandsquery = $mysqli->query("SELECT * FROM demands WHERE product = '$category' AND notification != '0000-00-00' order by id desc limit " . $s_pocet . "," . $perpage) or die($mysqli->error);

} else {

    $currentpage = 'editace-manualu';

    $servismaxquery = $mysqli->query("SELECT COUNT(*) AS NumberOfOrders FROM demands WHERE notification != '0000-00-00' order by id desc") or die($mysqli->error);
    $servismaxquery = mysqli_fetch_array($servismaxquery);
    $max = $servismaxquery['NumberOfOrders'];
    if ((isset($od) && $od == "") or (isset($od) && $od < 1) || !isset($od)) {$od = 1;}
    $perpage = 12;

    $s_lol = $od - 1;
    $s_pocet = $s_lol * $perpage;
    $pocet_prispevku = $max;
    $demandsquery = $mysqli->query("SELECT * FROM demands WHERE notification != '0000-00-00' order by id desc limit " . $s_pocet . "," . $perpage) or die($mysqli->error);

}

if (mysqli_num_rows($demandsquery) > 0) {
    ?>

        <table class="table table-bordered table-striped datatable dataTable" id="table-2" aria-describedby="table-2_info">
        <thead>
          <tr role="row">
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 140px; text-align: center;">Poptávka</th>
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Curriculum / Occupation: activate to sort column ascending" style="width: 100px; text-align: center;">Příští odeslání</th>
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Curriculum / Occupation: activate to sort column ascending" style="width: 100px; text-align: center;">Poslední odeslání</th>
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Average Grade: activate to sort column ascending" style="width: 80px; text-align: center;">Druh</th>
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Average Grade: activate to sort column ascending" style="width: 100px; text-align: center;">Stav poptávky</th>
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 100px; text-align: center;">Správce</th>
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 151px; text-align: center;">Akce</th></tr>
        </thead>


          <tbody role="alert" aria-live="polite" aria-relevant="all">
          <?php
    while ($demand = mysqli_fetch_array($demandsquery)) {

        $adminquery = $mysqli->query("SELECT user_name FROM demands WHERE id = '" . $demand['admin_id'] . "'")or die($mysqli->error);
        $admin = mysqli_fetch_array($adminquery);

        $mailsquery = $mysqli->query("SELECT *, DATE_FORMAT(datetime, '%d. %m. %Y') as dateformated, DATE_FORMAT(datetime, '%T') as hoursmins FROM demands_mails_history WHERE demand_id = '" . $demand['id'] . "'") or die($mysqli->error);
        $mail = mysqli_fetch_array($mailsquery);
        ?>


            <script type="text/javascript">

              jQuery(document).ready(function($)
              {

                $('.showhistory-<?= $demand['id'] ?>').click(function() {

                  $(".showhistory-<?= $demand['id'] ?>").hide("slow");
                  $(".hidehistory-<?= $demand['id'] ?>").show("slow");
                  $(".history-<?= $demand['id'] ?>").show("slow");

                });

                $('.hidehistory-<?= $demand['id'] ?>').click(function() {

                  $(".hidehistory-<?= $demand['id'] ?>").hide("slow");
                  $(".history-<?= $demand['id'] ?>").hide("slow");
                  $(".showhistory-<?= $demand['id'] ?>").show("slow");

                });

              });

            </script>



          <tr class="odd" >
            <td class=" " style="background-color: #ffffff !important;text-align: center;"><a href="zobrazit-poptavku?id=<?= $demand['id'] ?>" target="_blank" style="color: #949494;"><?= $demand['user_name'] ?></a></td>
            <td class=" " style="background-color: #ffffff !important;text-align: center;"><?php if (isset($demand['dateformated']) && $demand['dateformated'] == "00. 00. 0000") {echo "žádné";} else {echo $demand['dateformated'];}?></td>
            <td class=" " style="background-color: #ffffff !important;"><center><?= $mail['dateformated'] ?></center></td>
            <td class=" " style="background-color: #ffffff !important;"><center> <?php if (isset($demand['customer']) && $demand['customer'] == 0) {echo "sauna";} elseif (isset($demand['customer']) && $demand['customer'] == 1) {echo "vířivka";} elseif (isset($demand['customer']) && $demand['customer'] == 3) {echo "sauna a vířivka";} elseif (isset($demand['customer']) && $demand['customer'] == 9) {echo "textovej";}?></center></td>
            <td class=" " style="background-color: #ffffff !important; height: 43px !important;text-align: center;"><?php if (isset($demand['status']) && $demand['status'] == 1) {echo 'nezpracovaná';} elseif (isset($demand['status']) && $demand['status'] == 2) {echo 'zhotovená nabídka';} elseif (isset($demand['status']) && $demand['status'] == 3) {echo 'v řešení';} elseif (isset($demand['status']) && $demand['status'] == 4) {echo 'realizace';} elseif (isset($demand['status']) && $demand['status'] == 7) {echo 'odložená';} elseif (isset($demand['status']) && $demand['status'] == 5) {echo 'hotová';} else {echo 'stornovaná';}?></td>
            <td class=" " style="background-color: #ffffff !important;"><center><?= $admin['user_name'] ?></center></td>
            <td class=" " style="text-align: center;background-color: #ffffff !important;">

              <a href="solo-mail?id=<?= $demand['id'] ?>" class="btn btn-primary btn-sm btn-icon icon-left">
                <i class="entypo-pencil"></i>
                Aktualizovat
              </a>
              <a class="showhistory-<?= $demand['id'] ?> btn btn-primary btn-sm btn-icon icon-left">
                <i class="entypo-search"></i>
                Zobrazit historii
              </a>
              <a class="hidehistory-<?= $demand['id'] ?> btn btn-primary btn-sm btn-icon icon-left" style="display: none;">
                <i class="entypo-cancel"></i>
                Skrýt historii
              </a>
            </td>
          </tr>
          <tr class="history-<?= $demand['id'] ?>" style="display: none;">
            <td colspan="7" style="background-color: #f8f8f8 !important;">
          <div class="well well-lg" style="background-color: #FFFFFF;">
             <div id="table-2_wrapper" class="dataTables_wrapper form-inline" role="grid" style="margin-bottom: 30px;">

<?php

        $mailsquery = $mysqli->query("SELECT *, DATE_FORMAT(datetime, '%d. %m. %Y') as dateformated, DATE_FORMAT(datetime, '%T') as hoursmins FROM demands_mails_history WHERE demand_id = '" . $demand['id'] . "' ORDER BY id desc");
        if (mysqli_num_rows($mailsquery) > 0) {
            ?>

        <table class="table table-bordered table-striped datatable dataTable" id="table-2" aria-describedby="table-2_info">
        <thead>
          <tr role="row">
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Average Grade: activate to sort column ascending" style="width: 200px;">Předmět</th>
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Curriculum / Occupation: activate to sort column ascending" style="width: 90px; text-align: center;">Datum</th>
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Curriculum / Occupation: activate to sort column ascending" style="width: 90px; text-align: center;">Čas</th>
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 100px; text-align: center;">Odeslal</th>
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Actions: activate to sort column ascending" style="width: 80px; text-align: center;">Akce</th></tr>
        </thead>


          <tbody role="alert" aria-live="polite" aria-relevant="all">
          <?php
            while ($mail = mysqli_fetch_array($mailsquery)) {
                $adminquery = $mysqli->query("SELECT user_name FROM demands WHERE id = '" . $mail['admin_id'] . "'");
                $admin = mysqli_fetch_array($adminquery);

                if (isset($mail['customer']) && $mail['customer'] == 10) { ?>


            <script type="text/javascript">

              jQuery(document).ready(function($)
              {

                $('.showmail-<?= $mail['id'] ?>').click(function() {

                  $(".showmail-<?= $mail['id'] ?>").hide("slow");
                  $(".hidemail-<?= $mail['id'] ?>").show("slow");
                  $(".mail-<?= $mail['id'] ?>").show("slow");

                });

                $('.hidemail-<?= $mail['id'] ?>').click(function() {

                  $(".hidemail-<?= $mail['id'] ?>").hide("slow");
                  $(".mail-<?= $mail['id'] ?>").hide("slow");
                  $(".showmail-<?= $mail['id'] ?>").show("slow");

                });

              });

            </script>



          <tr class="odd" >
            <td class=" " style="background-color: #ffffff !important; height: 43px !important;"><i class="fa fa-phone" style="font-size: 15px; padding: 0 5px 0 5px;"></i> <?= $mail['title'] ?></td>
            <td class=" " style="background-color: #ffffff !important;"><center><?= $mail['dateformated'] ?></center></td>
            <td class=" " style="background-color: #ffffff !important;"><center><?= $mail['hoursmins'] ?></center></td>
            <td class=" " style="background-color: #ffffff !important;"><center><?= $admin['user_name'] ?></center></td>
            <td class=" " style="text-align: center;background-color: #ffffff !important;">
              <a class="showmail-<?= $mail['id'] ?> btn btn-primary btn-sm btn-icon icon-left">
                <i class="entypo-search"></i>
                Zobrazit telefonát
              </a>
              <a class="hidemail-<?= $mail['id'] ?> btn btn-primary btn-sm btn-icon icon-left" style="display: none;">
                <i class="entypo-cancel"></i>
                Skrýt telefonát
              </a>
            </td>
          </tr>
          <tr class="mail-<?= $mail['id'] ?>" style="display: none;">
            <td colspan="5" style="background-color: #f8f8f8 !important;">
          <div class="well well-lg" style="background-color: #FFFFFF;">
            <?= $mail['text'] ?>
            </div>
            <center><a style="margin-bottom: 12px; margin-right: 26px; font-size: 20px; padding: 30px 100px 30px 150px;" class="hidemail-<?= $mail['id'] ?> btn btn-primary btn-icon icon-left btn-lg">
          <i class="entypo-cancel" style="line-height: 60px;font-size: 40px; padding: 10px 20px;"></i>
          Skrýt telefonát
        </a></center>
          </td>
             </tr>
             </tr>


            <?php
                } elseif (isset($mail['customer']) && $mail['customer'] == 11) { ?>


            <script type="text/javascript">

              jQuery(document).ready(function($)
              {

                $('.showmail-<?= $mail['id'] ?>').click(function() {

                  $(".showmail-<?= $mail['id'] ?>").hide("slow");
                  $(".hidemail-<?= $mail['id'] ?>").show("slow");
                  $(".mail-<?= $mail['id'] ?>").show("slow");

                });

                $('.hidemail-<?= $mail['id'] ?>').click(function() {

                  $(".hidemail-<?= $mail['id'] ?>").hide("slow");
                  $(".mail-<?= $mail['id'] ?>").hide("slow");
                  $(".showmail-<?= $mail['id'] ?>").show("slow");

                });

              });

            </script>



          <tr class="odd" >
            <td class=" " style="background-color: #ffffff !important; height: 43px !important;"><i class="fa fa-mail-forward" style="font-size: 14px; padding: 0 6px 0 2px;"></i> <?= $mail['title'] ?></td>
            <td class=" " style="background-color: #ffffff !important;"><center><?= $mail['dateformated'] ?></center></td>
            <td class=" " style="background-color: #ffffff !important;"><center><?= $mail['hoursmins'] ?></center></td>
            <td class=" " style="background-color: #ffffff !important;"><center><?= $admin['user_name'] ?></center></td>
            <td class=" " style="text-align: center;background-color: #ffffff !important;">
              <a class="showmail-<?= $mail['id'] ?> btn btn-primary btn-sm btn-icon icon-left">
                <i class="entypo-search"></i>
                Zobrazit důvod
              </a>
              <a class="hidemail-<?= $mail['id'] ?> btn btn-primary btn-sm btn-icon icon-left" style="display: none;">
                <i class="entypo-cancel"></i>
                Skrýt důvod
              </a>
            </td>
          </tr>
          <tr class="mail-<?= $mail['id'] ?>" style="display: none;">
            <td colspan="5" style="background-color: #f8f8f8 !important;">
          <div class="well well-lg" style="background-color: #FFFFFF;">
            <?= $mail['text'] ?>
            </div>
            <center><a style="margin-bottom: 12px; margin-right: 26px; font-size: 20px; padding: 30px 100px 30px 150px;" class="hidemail-<?= $mail['id'] ?> btn btn-primary btn-icon icon-left btn-lg">
          <i class="entypo-cancel" style="line-height: 60px;font-size: 40px; padding: 10px 20px;"></i>
          Skrýt důvod
        </a></center>
          </td>
             </tr>
             </tr>


            <?php
                } else {
                    ?>


            <script type="text/javascript">

              jQuery(document).ready(function($)
              {

                $('.showmail-<?= $mail['id'] ?>').click(function() {

                  $(".showmail-<?= $mail['id'] ?>").hide("slow");
                  $(".hidemail-<?= $mail['id'] ?>").show("slow");
                  $(".mail-<?= $mail['id'] ?>").show("slow");

                });

                $('.hidemail-<?= $mail['id'] ?>').click(function() {

                  $(".hidemail-<?= $mail['id'] ?>").hide("slow");
                  $(".mail-<?= $mail['id'] ?>").hide("slow");
                  $(".showmail-<?= $mail['id'] ?>").show("slow");

                });

              });

            </script>



          <tr class="odd" >
            <td class=" " style="background-color: #ffffff !important; height: 43px !important;"><i class="fa fa-envelope" style="font-size: 14px; padding: 0 6px 0 2px;"></i> <?= $mail['title'] ?></td>
            <td class=" " style="background-color: #ffffff !important;"><center><?= $mail['dateformated'] ?></center></td>
            <td class=" " style="background-color: #ffffff !important;"><center><?= $mail['hoursmins'] ?></center></td>
            <td class=" " style="background-color: #ffffff !important;"><center><?= $admin['user_name'] ?></center></td>
            <td class=" " style="text-align: center;background-color: #ffffff !important;">
              <a class="showmail-<?= $mail['id'] ?> btn btn-primary btn-sm btn-icon icon-left">
                <i class="entypo-search"></i>
                Zobrazit mail
              </a>
              <a class="hidemail-<?= $mail['id'] ?> btn btn-primary btn-sm btn-icon icon-left" style="display: none;">
                <i class="entypo-cancel"></i>
                Skrýt mail
              </a>
            </td>
          </tr>
          <tr class="mail-<?= $mail['id'] ?>" style="display: none;">
            <td colspan="5" style="background-color: #f8f8f8 !important;">
          <div class="well well-lg" style="background-color: #FFFFFF;">
            <?= $mail['text'] ?>
            </div>
            <center><a style="margin-bottom: 12px; margin-right: 26px; font-size: 20px; padding: 30px 100px 30px 150px;" class="hidemail-<?= $mail['id'] ?> btn btn-primary btn-icon icon-left btn-lg">
          <i class="entypo-cancel" style="line-height: 60px;font-size: 40px; padding: 10px 20px;"></i>
          Skrýt mail
        </a></center>
          </td>
             </tr>


         <?php }
            }
            ?>
          </tbody>

        </table>
<?php } else { ?>


<div class="well well-lg" style="margin-bottom: 0;">

<h3 style="text-align: center;">Zatím nebyly záslány žádné emaily.</h3>

</div>


<?php } ?>
        </div>
            </div>
            <center><a style="margin-bottom: 12px; margin-right: 26px; font-size: 20px; padding: 30px 100px 30px 150px;" class="hidehistory-<?= $demand['id'] ?> btn btn-primary btn-icon icon-left btn-lg">
          <i class="entypo-cancel" style="line-height: 60px;font-size: 40px; padding: 10px 20px;"></i>
          Skrýt historii
        </a></center>
          </td>
             </tr>


         <?php }
    ?>
          </tbody>

        </table>
<?php } else { ?>


<div class="well well-lg" style="margin-bottom: 0;">

<h3 style="text-align: center;">Filtraci neodpovídají žádné poptávky.</h3>

</div>


<?php } ?>

<!-- Pager for search results --><div class="row">
	<div class="col-md-12">
		<center><ul class="pagination pagination-sm">
			<?php
include VIEW . "/default/pagination.php";?>
		</ul></center>
	</div>
</div><!-- Footer -->
</div>



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



<?php include VIEW . '/default/footer.php'; ?>