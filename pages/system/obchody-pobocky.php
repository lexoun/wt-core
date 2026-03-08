<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

if (isset($_REQUEST['od'])) {
    $od = $_REQUEST['od'];
}
if (isset($_REQUEST['q'])) {
    $search = $_REQUEST['q'];
}

$pagetitle = "Nastavení upozornění + otevíracích dob";

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit') {

    $closures_query = $mysqli->query("SELECT * FROM shops_locations_closures") or die($mysqli->error);
    while ($closure = mysqli_fetch_array($closures_query)) {

        $shop = $closure['shop_slug'];
        $location = $closure['location_slug'];

        $new_closure = $_POST[$shop . '_' . $location];

        $mysqli->query("UPDATE shops_locations_closures SET closure = '$new_closure' WHERE shop_slug = '" . $closure['shop_slug'] . "' AND location_slug = '" . $closure['location_slug'] . "'") or die($mysqli->error);

    }

    $announcement_query = $mysqli->query("SELECT * FROM shops WHERE id != '3'") or die($mysqli->error);
    while ($announcement = mysqli_fetch_array($announcement_query)) {

        $shop = $announcement['slug'];
        $new_closure = $_POST[$shop];

        $mysqli->query("UPDATE shops SET announcement = '$new_closure' WHERE slug = '" . $announcement['slug'] . "'") or die($mysqli->error);

    }

    $query = $mysqli->query("SELECT * FROM shops_locations WHERE type = 'branch'") or die($mysqli->error);
    while ($branch = mysqli_fetch_array($query)) {

        $shop = $branch['slug'];
        $salesman_hottub = $_POST[$shop . '_salesman_hottub'];
        $salesman_sauna = $_POST[$shop . '_salesman_sauna'];

        $mysqli->query("UPDATE shops_locations SET salesman_hottub = '$salesman_hottub', salesman_sauna = '$salesman_sauna' WHERE id = '" . $branch['id'] . "'") or die($mysqli->error);

    }

    $url = "https://www.wellnesstrade.cz/admin/controllers/cache/cache-flush?site=spahouse";

    $handler = curl_init($url);
    curl_setopt($handler, CURLOPT_RETURNTRANSFER,1);
    $data = curl_exec($handler);
    curl_close($handler);


    $url = "https://www.wellnesstrade.cz/admin/controllers/cache/cache-flush?site=saunahouse";

    $handler = curl_init($url);
    curl_setopt($handler, CURLOPT_RETURNTRANSFER,1);
    $data = curl_exec($handler);
    curl_close($handler);

    Header("Location:https://www.wellnesstrade.cz/admin/pages/system/obchody-pobocky?success=edit");
    exit;

}

include VIEW . '/default/header.php';

?>

<script type="text/javascript">

    $(document).ready(function() {

        $("button[type='submit']").click(function (e) {

            $(".alert").show();

        });

    });

</script>


<div class="alert alert-info" style="display: none;">
    <img src="https://www.wellnesstrade.cz/admin/assets/images/loader-trans.gif" height="20" style="float: left;"> <span style="line-height: 22px; margin-left: 7px">Právě dochází k promazávání CACHE na stránkách. Vyčkejte prosím!</span>
</div>

<form id="orderform" role="form" method="post" class="form-horizontal form-groups-bordered validate" action="obchody-pobocky?action=edit" enctype="multipart/form-data">

<div class="row">

		<div class="col-md-12">


<div class="panel panel-primary" data-collapsed="0">


						<div class="panel-body">

							<h4>Speciální otevírací doba</h4><hr>

                            <div class="alert alert-default"">
                            <span style="line-height: 22px; margin-left: 7px"><strong>Vzor upozornění:</strong> Bohužel máme zavřeno 1.-30.12., ale můžete nás stále kontaktovat na tel.: 608 33 44 90, nebo ostatních showroomech. Děkujeme za pochopení.</span>
                        </div>
							<?php

$closures_query = $mysqli->query("SELECT * FROM shops_locations_closures c, shops_locations l WHERE l.slug = c.location_slug ORDER BY l.id, c.location_slug") or die($mysqli->error);
while ($closure = mysqli_fetch_array($closures_query)) {

    ?>
					<div class="form-group">

						<label for="field-1" class="col-sm-3 control-label"><?= ucfirst($closure['shop_slug']) ?> <?= ucfirst($closure['name']) ?></label>
						<div class="col-sm-9">
										<input name="<?= $closure['shop_slug'] ?>_<?= $closure['location_slug'] ?>"  class="form-control" type="text" value="<?= $closure['closure'] ?>">
						</div>
					</div>

					<?php } ?>
<br>
<h4>Speciální upozornění v eshopech</h4>
<hr>

	<?php

$announcement_query = $mysqli->query("SELECT * FROM shops WHERE id != '3'") or die($mysqli->error);

while ($announcement = mysqli_fetch_array($announcement_query)) {

    ?>


					<div class="form-group">

						<label for="field-1" class="col-sm-3 control-label"><?= ucfirst($announcement['slug']) ?> e-shop</label>
						<div class="col-sm-4">
										<input name="<?= $announcement['slug'] ?>"  class="form-control" type="text" value="<?= $announcement['announcement'] ?>">
						</div>
					</div>

					<?php } ?>


                            <h4>Nastavení defaultních prodejců</h4><hr>

<!--                            <div class="form-group">-->
<!--                                <label for="field-1" class="col-sm-1 control-label"></label>-->
<!--                                <div class="col-sm-1" style="text-align: center;">-->
<!--                                    Vířivky-->
<!--                                </div>-->
<!--                                <div class="col-sm-1" style="text-align: center;">-->
<!--                                    Sauny-->
<!--                                </div>-->
<!--                            </div>-->


                            <div class="col-sm-1">
                                <div class="form-group">
                                    <label for="field-1" class="col-sm-12 control-label"></label>
                                </div>
                                <div class="form-group">
                                    <label for="field-1" class="col-sm-12 control-label" style="padding-top: 14px;">Vířivky</label>
                                </div>
                                <div class="form-group">
                                    <label for="field-1" class="col-sm-12 control-label" style="padding-top: 14px;">Sauny</label>
                                </div>
                            </div>
                            <?php

                            $admins_query = $mysqli->query("SELECT * FROM demands WHERE role = 'salesman' OR role = 'salesman-technician'")or die($mysqli->error);

                            $query = $mysqli->query("SELECT * FROM shops_locations WHERE type = 'branch'") or die($mysqli->error);
                            while ($branch = mysqli_fetch_array($query)) {

                                ?>

                                <div class="col-sm-2" style="width: 12%;">
                                    <div class="form-group">

                                    <label for="field-1" class="col-sm-12 control-label" style="text-align: center;"><?= ucfirst($branch['name']) ?></label>
                                    </div>

                                    <div class="form-group">
                                        <?php mysqli_data_seek($admins_query, 0); ?>
                                        <div class="col-sm-12">
                                            <select name="<?= $branch['slug'] ?>_salesman_hottub" class="form-control">
                                                <option value="0">žádný prodejce</option>
                                            <?php while($admin = mysqli_fetch_assoc($admins_query)){ ?>
                                                <option value="<?= $admin['id']; ?>" <?= ($branch['salesman_hottub'] == $admin['id']) ? 'selected' : '' ?>><?= $admin['user_name']; ?></option>
                                            <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <?php mysqli_data_seek($admins_query, 0); ?>
                                        <div class="col-sm-12">
                                            <select name="<?= $branch['slug'] ?>_salesman_sauna" class="form-control">
                                                <option value="0">žádný prodejce</option>
                                                <?php while($admin = mysqli_fetch_assoc($admins_query)){ ?>
                                                    <option value="<?= $admin['id']; ?>" <?= ($branch['salesman_sauna'] == $admin['id']) ? 'selected' : '' ?>><?= $admin['user_name']; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                            </div>
                            <?php } ?>
                            <br>


</div>

</div>

	<center>
	<div class="form-group default-padding button-demo">
		<button type="submit" style="margin-bottom: 24px; margin-right: 26px; font-size: 20px; padding: 20px 50px 20px 140px; margin-top: 20px;" data-style="zoom-in" class="ladda-button btn btn-success btn-icon icon-left btn-lg"><i class="entypo-plus" style="line-height: 48px;font-size: 40px; padding: 10px 20px;"></i> <span class="ladda-label">Uložit</span></button>
	</div></center>

</form>

<!-- Footer -->

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




