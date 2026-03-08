<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";

include INCLUDES . "/googlelogin.php";
include INCLUDES . "/functions.php";

$pagetitle = "Přidat vířivku";

$bread1 = "Vířivky";
$abread1 = "virivky";

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "add") {

    $insert = $mysqli->query("INSERT INTO warehouse (location_id, loadingdate, description, product, status, demand_id, customer, serial_number, purchase_price, sale_price) VALUES ('" . $_POST['location_id'] . "', '" . $_POST['loadingdate'] . "','" . $_POST['description'] . "','" . $_POST['virivkatype'] . "','" . $_POST['status'] . "','" . $_POST['demand'] . "','1','" . $_POST['serialnumber'] . "','" . $_POST['purchase_price'] . "','" . $_POST['sale_price'] . "')") or die($mysqli->error);
    $id = $mysqli->insert_id;

    $specsquery = $mysqli->query('SELECT * FROM specs WHERE product = 1 AND supplier = 1') or die($mysqli->error);

    $specnaz = "";
    while ($specs = mysqli_fetch_array($specsquery)) {
        $specsslug = $specs['seoslug'];
        $insrt = $_POST[$specsslug];
        $spcid = $specs['id'];
        $insert_specs = "INSERT INTO warehouse_specs_bridge (value, specs_id, client_id) VALUES ('$insrt', '$spcid', '$id')";
        $insert_specs_result = $mysqli->query($insert_specs) or die($mysqli->error);

        $parametter = $specs['name'] . ': ' . $insrt;

        if ($specnaz != "") {

            $specnaz = $specnaz . ', ' . $parametter;

        } else {

            $specnaz = $parametter;

        }

    }

    if ($_POST['demand'] != "" && $_POST['demand'] != 0) {

        $_REQUEST['id'] = $_POST['demand'];

        saveCalendarEvent($_POST['demand'], 'realization');

    }

    if (isset($_POST['product_sku'])) {

        $post = array_filter($_POST['product_sku']);
        if (!empty($post)) {

            foreach ($post as $post_index => $posterino) {

                if (!empty($_POST['product_quantity'][$post_index])) {

                    $quantity = $_POST['product_quantity'][$post_index];

                    $searchquery = $mysqli->query("SELECT instock, id FROM products p WHERE code = '$posterino'") or die($mysqli->error);

                    if (mysqli_num_rows($searchquery) > 0) {

                        $search = mysqli_fetch_array($searchquery);

                        if ($quantity > $search['instock']) {

                            $reserve = $search['instock'];
                            $update = $mysqli->query("UPDATE products SET instock = instock - $reserve WHERE id = '" . $search['id'] . "'");

                        } else {

                            $reserve = $quantity;
                            $update = $mysqli->query("UPDATE products SET instock = instock - $reserve WHERE id = '" . $search['id'] . "'");

                        }

                        $insert = $mysqli->query("INSERT INTO warehouse_products_bridge (warehouse_id, product_id, quantity, reserved) VALUES ('$id', '" . $search['id'] . "', '$quantity', '$reserve')");

                    } else {

                        $searchquery = $mysqli->query("SELECT v.stock, p.productname, p.id as id, v.id as vid FROM products p, products_variations v WHERE v.sku = '$posterino' AND p.id = v.product_id") or die($mysqli->error);

                        if (mysqli_num_rows($searchquery) > 0) {

                            $search = mysqli_fetch_array($searchquery);

                            if ($quantity > $search['stock']) {

                                $reserve = $search['stock'];
                                $update = $mysqli->query("UPDATE products_variations SET stock = stock - $reserve WHERE id = '" . $search['vid'] . "'");

                            } else {

                                $reserve = $quantity;
                                $update = $mysqli->query("UPDATE products_variations SET stock = stock - $reserve WHERE id = '" . $search['vid'] . "'");

                            }

                            $insert = $mysqli->query("INSERT INTO warehouse_products_bridge (warehouse_id, product_id, variation_id, quantity, reserved) VALUES ('$id', '" . $search['id'] . "', '" . $search['vid'] . "', '$quantity', '$reserve')");

                        }

                    }

                }

            }

        }

    }

    Header("Location:https://www.wellnesstrade.cz/admin/pages/warehouse/zobrazit-virivku?id=" . $id . "&success=edit");
    exit;
}

include VIEW . '/default/header.php';

?>

<script type="text/javascript">

function randomPassword(length) {
    var chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOP1234567890";
    var pass = "";
    for (var x = 0; x < length; x++) {
        var i = Math.floor(Math.random() * chars.length);
        pass += chars.charAt(i);
    }
    return pass;
}

function generate() {
    myform.password.value = randomPassword(myform.length.value);
}

jQuery(document).ready(function($)
{

$('.radio').click(function() {
   if($("input:radio[class='saunaradio']").is(":checked")) {


	$('.virivkens').hide( "slow");
	$('.saunkens').show( "slow");
   }
     if($("input:radio[class='virivkaradio']").is(":checked")) {


   	$('.saunkens').hide( "slow");
$('.virivkens').show( "slow");
   }
});
});


</script>


<script>

jQuery(document).ready(function($)
{


   $('#selectbox-o').select2({
    minimumInputLength: 2,
    ajax: {
      url: "/admin/data/autosuggest-products.php",
      dataType: 'json',
      data: function (term, page) {
        return {
          q: term,
          site: 'wellnesstrade'
        };
      },
      results: function (data, page) {
        return { results: data };
      }
    }
  });




$('#selectbox-o').on("change", function(e) {


	var vlue = $("#selectbox-o").select2("val");

	var nema = $("#s2id_selectbox-o .select2-chosen").text();

	$('#specification_copy').clone(true).insertBefore("#duplicate_specification").attr('id', 'copied').addClass('has-success').show();

	$('#copied #copy_this_first').attr('name', 'product_name[]').attr('value', nema);

	$('#copied #copy_this_third').attr('name', 'product_sku[]').attr('value', vlue);
	$('#copied #copy_this_second').attr('name', 'product_quantity[]').attr('value', '1');

	$('#copied').attr('id', 'copifinish');

	$("#selectbox-o").select2("val", "");


	setTimeout(function(){
      $('#copifinish').attr('id', 'hasfinish').removeClass('has-success');}, 2000);


});


$('.remove_specification').click(function() {
   $(this).closest('.specification').remove();
   event.preventDefault();
});


  });

</script>


<form role="form" method="post" name="myform" class="form-horizontal form-groups-bordered validate" action="pridat-virivku?action=add">
<input type="hidden" name="length" value="14">
	<div class="row">

		<div class="col-md-6">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						<?= $pagetitle ?>
					</div>

				</div>

						<div class="panel-body">

	<div class="form-group">

	<label class="col-sm-3 control-label" style="padding-top: 28px;">Značka</label>
	<div class="col-sm-9">
<?php

$brands_query = $mysqli->query("SELECT DISTINCT brand FROM warehouse_products WHERE active = 'yes' AND customer = 1 AND brand != ''");

$i = 0;
while ($brand = mysqli_fetch_array($brands_query)) {

    $i++;

    $brand_stripped = preg_replace('/\s+/', '_', $brand['brand']);

    if ($i == 3) {$i = 1;}
    ?>

<script type="text/javascript">


jQuery(document).ready(function($)
{

	$('#click_brand_<?= $brand_stripped ?>').click(function() {

		$(".brand-clicks .tile-stats").removeClass('tile-primary');
		$(".brand-clicks .tile-stats").addClass('tile-gray	');

		$("#click_brand_<?= $brand_stripped ?> .tile-stats").addClass('tile-primary');

		$(".brand-clicks .tile-primary").removeClass('tile-gray	');

		$(".brand-groups").hide('slow');
		$(".brand-groups .setname").attr('name', '');


		$("#show_brand_<?= $brand_stripped ?>").show('slow');
		$("#show_brand_<?= $brand_stripped ?> .setname").attr('name', 'virivkatype');


	});

});

</script>




		<div id="click_brand_<?= $brand_stripped ?>" class="brand-clicks col-sm-4" style="cursor:pointer; padding-left: 0; padding-right: 5px;">
					<div class="tile-stats tile-gray">
						<div class="icon" style="top: 20px !important;"><i></i></div>
						<div class="num"></div> <h3><?= $brand['brand'] ?></h3> <p></p>
					</div>
				</div>







<?php } ?>


			</div>

			</div>

<?php

mysqli_data_seek($brands_query, 0);

while ($brand = mysqli_fetch_array($brands_query)) {

    $brand_stripped = preg_replace('/\s+/', '_', $brand['brand']);

    ?>
	<div id="show_brand_<?= $brand_stripped ?>" class="form-group brand-groups" style="display: none;">
						<label class="col-sm-3 control-label">Typ vířivky</label>

						<div class="col-sm-9">

							<select class="form-control setname" name="">
								<option value="">Vyberte vířivku</option>
								<?php

    $virivkyquery = $mysqli->query("SELECT * FROM warehouse_products WHERE active = 'yes' AND customer = 1 AND brand = '" . $brand['brand'] . "' ORDER BY brand");
    while ($virivka = mysqli_fetch_array($virivkyquery)) { ?>
								<option value="<?= $virivka['connect_name'] ?>"><?= $virivka['brand'] . ' ' . ucfirst($virivka['fullname']) ?></option><?php } ?>
							</select>

						</div>
					</div>

<?php } ?>
				<div class="form-group">
						<label class="col-sm-3 control-label">K poptávce</label>

						<div class="col-sm-9">
						<?php

$demandsq = $mysqli->query("SELECT user_name, id FROM demands WHERE customer = 1") or die($mysqli->error);

?>
						<select name="demand" class="select2" data-allow-clear="true" data-placeholder="Vyberte poptávku...">
								<option></option>
								<optgroup label="VÍŘÍVKY poptávky">
									<?php while ($dem = mysqli_fetch_array($demandsq)) {
    $find = $mysqli->query("SELECT id FROM warehouse WHERE demand_id = '" . $dem['id'] . "'");
    if (mysqli_num_rows($find) != 1) { ?><option value="<?= $dem['id'] ?>"><?= $dem['user_name'] ?></option><?php }}?>
								</optgroup>
						</select>

						</div>
					</div>

					<div class="form-group" style="margin-top: 18px; margin-bottom: 21px;">
						<label class="col-sm-3 control-label">Stav</label>
						<div class="col-sm-9">
							<div class="radio" style="margin-left: 10px;float: left;">
								<label>
									<input type="radio" name="status" value="0" checked>Ve výrobě
								</label>
							</div>
							<div class="radio" style="margin-left: 31px; float: left;">
								<label>
									<input type="radio" name="status" value="1" >Na cestě
								</label>
							</div>
							<div class="radio" style="margin-left: 31px; float: left;">
								<label>
									<input type="radio" name="status" value="2">Na skladě
								</label>
							</div>
							<div class="radio" style="margin-left: 30px; float: left;">
								<label>
									<input type="radio" name="status" value="3">Na showroomu
								</label>
							</div>
						</div>
					</div>


					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Fyzické umístění</label>
						<div class="col-sm-8">
							<?php
$warehouse_query = $mysqli->query("SELECT * FROM shops_locations") or die($mysqli->error);
while ($warehouse = mysqli_fetch_array($warehouse_query)) {

    ?>
							<div class="radio" style="margin-left: 30px; float: left;">
								<label>
									<input type="radio" name="location_id" value="<?= $warehouse['id'] ?>"  <?php if ($warehouse['id'] == 1) {echo 'checked';}?>><?= $warehouse['name'] ?>
								</label>
							</div>
							<?php } ?>
						</div>
					</div>


						<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Očekávané naskladnění</label>

						<div class="col-sm-9">
							<input type="date" class="form-control" id="field-2" name="loadingdate" value="">
						</div>
					</div>
					<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Výrobní číslo</label>

						<div class="col-sm-9">
							<input type="text" class="form-control" id="field-2" name="serialnumber" value="">
						</div>
					</div>
						<div class="form-group">
						<label for="field-2" class="col-sm-3 control-label">Prodejní cena</label>

						<div class="col-sm-9">
							<input type="text" class="form-control" id="field-2" style="float:left; width: 90%;" name="sale_price" value="">
							<span class="input-group-addon" style="float:left; padding: 9px 25px 8px 9px;">Kč</span>
						</div>
					</div>
					<div class="form-group" >
						<label for="field-2" class="col-sm-3 control-label">Nákupní cena</label>

						<div class="col-sm-3" style="padding-right: 0">
							<input type="text" class="form-control" style="float:left; width: 60%;" id="field-2" name="purchase_price" value="">
							<span class="input-group-addon" style="float:left; padding: 9px 25px 8px 9px;">Kč</span>
						</div>
						<label for="field-2" class="col-sm-3 control-label">Reálně inkasováno</label>

						<div class="col-sm-3" style="padding-left: 0">
							<input type="text" class="form-control" style="float:left; width: 60%;" id="field-2" name="real_price" value="">
							<span class="input-group-addon" style="float:left; padding: 9px 25px 8px 9px;">Kč</span>
						</div>
					</div>

					<div class="form-group">
						<label for="field-1" class="col-sm-3 control-label">Dodatečný popisek</label>

						<div class="col-sm-9">
							<textarea class="form-control autogrow" id="field-ta" name="description" style="overflow: hidden; word-wrap: break-word; resize: horizontal; height: 48px;"></textarea>
						</div>
					</div>
					<hr>

<?php
$specsquery = $mysqli->query('SELECT * FROM specs WHERE product = 1 AND supplier = 1 order by type desc, id asc') or die($mysqli->error);
while ($specs = mysqli_fetch_assoc($specsquery)) {

	$cparsquery = $mysqli->query('SELECT * FROM warehouse_specs_bridge WHERE specs_id = "' . $specs['id'] . '"') or die($mysqli->error);
	$cpars = mysqli_fetch_array($cparsquery);
	
    if (isset($specs['type']) && $specs['type'] == 1) {

        $paramsquery = $mysqli->query('SELECT * FROM specs_params WHERE spec_id = "' . $specs['id'] . '" order by id asc') or die($mysqli->error);

        ?>
<div class="form-group">
						<label class="col-sm-3 control-label"><?= $specs['name'] ?></label>

						<div class="col-sm-9">

							<select name="<?= $specs['seoslug'] ?>" class="form-control">
								<?php
        $selected = false;
        while ($params = mysqli_fetch_assoc($paramsquery)) {

            ?>

								<option value="<?= $params['option'] ?>" <?php if (isset($cpars['value']) && $cpars['value'] == $params['option']) {echo 'selected';
                $selected = true;}?>><?= $params['option'] ?></option>
								<?php } ?>
								<option value="" <?php if ($selected != true) {echo 'selected';}?>>Žádná vybraná možnost</option>
							</select>

						</div>
					</div>
<?php
    } else {
        ?>
	<div class="form-group">
						<label class="col-sm-3 control-label"><?= $specs['name'] ?></label>
						<div class="col-sm-9">
							<div class="radio" style="width: 100px; float: left;">
								<label>
									<input type="radio" name="<?= $specs['seoslug'] ?>" value="Ano">Ano
								</label>
							</div>
							<div class="radio" style="width: 100px;float: left;">
								<label>
									<input type="radio" name="<?= $specs['seoslug'] ?>" value="Ne" checked>Ne
								</label>
							</div>

						</div>
					</div>

<?php

    }}
?>


				</div>

			</div>

		</div>

		<div class="col-md-6">

			<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						<strong style="font-weight: 600;">Položky</strong>
					</div>

				</div>

						<div class="panel-body">


							<!-- Product Name Select Box -->
							<div class="form-group">
							   <label class="col-sm-2 control-label">Přidat položku</label>
							   <div class="col-sm-9" style="padding: 0; width: 64.8%;">
							     <input id="selectbox-o" class="input-xlarge" name="optionvalue" type="hidden" data-placeholder="Vyberte produkt.." />
							   </div>
							</div>
							<hr>


							<div class="form-group">
						<label class="col-sm-2 control-label">Položky</label>

						<div class="col-sm-10" style="float:left; padding: 0;">



						<div id="specification_copy" class="specification" style="display: none; float:left; width: 100%;">

							<div class="col-sm-6" style="margin-bottom: 8px; padding: 0;">

								<input type="text" class="form-control" id="copy_this_first" name="copythis" value="" placeholder="Název produktu">

								<input type="text" class="form-control" id="copy_this_third" name="copythis" value="" placeholder="SKU produktu" style="display: none;">

							</div>

							<div class="col-sm-2" style="padding: 0 0px 0 8px;">

								<input type="text" class="form-control text-center" id="copy_this_second" name="copythis" value="" placeholder="Počet">

							</div>


							<div class="col-sm-2" style="padding: 0 0px 0 11px;">
								<button type="button" class="remove_specification btn btn-red" style="float:left; padding: 6px 10px; cursor: pointer;"> <i class="entypo-trash"></i> </button>
							 </div>
						</div>



							<button type="button" id="duplicate_specification" style="display: none;" class="btn btn-default btn-icon icon-left">
				          </button>
				      </div>
				      </div>
						</div>
					</div>

				</div>
	</div>



			<center>
	<div class="form-group default-padding" style="margin-left: -100px;">

  <a href="./virivky?id=<?= $_REQUEST['id'] ?>"><button type="button" class="btn btn-primary">Zpět</button></a>
		<button type="submit" class="btn btn-success"><?= $pagetitle ?></button>
	</div></center>

</form>
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


     <script>

        $(document).ready(function(){

            $("#orderform").on("submit", function(){
              var form = $( "#orderform" );
                         var l = Ladda.create( document.querySelector( '#orderform .button-demo button' ) );
                if(form.valid()){

                  l.start();
                }
               });


         });


    </script>

<?php include VIEW . '/default/footer.php'; ?>


