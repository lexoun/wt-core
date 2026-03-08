<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$categorytitle = "Vlastní spotřeba";
$pagetitle = "Nová vlastní spotřeba";

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "add") {

    $mysqli->query("INSERT INTO consumption (shipping_id, billing_id, vat, order_date, order_site, order_tracking_number, client_id, customer_email, customer_phone, order_status, customer_note, order_shipping_method, payment_method, delivery_price, location_id) VALUES ('" . $shipping_id . "', '" . $billing_id . "', '" . $_POST['vat'] . "',now(),'" . $_POST['site'] . "','" . $_POST['order_tracking_number'] . "', '" . $_POST['client_id'] . "', '" . $_POST['billing_email'] . "', '" . $_POST['billing_phone'] . "', '" . $_POST['order_status'] . "', '" . $_POST['customer_note'] . "', '" . $_POST['delivery'] . "', '" . $_POST['payment'] . "', '" . $delivery_price['price'] . "', '" . $_POST['location'] . "')") or die($mysqli->error);

	$id = $mysqli->insert_id;

	$overallcena = 0;
	$overall_purchase = 0;
	
    include CONTROLLERS . "/product-stock-controller.php";

    if (isset($_POST['product_sku'])) {

        $post = array_filter($_POST['product_sku']);
        if (!empty($post)) {

            foreach ($post as $post_index => $posterino) {

                if (!empty($_POST['product_quantity'][$post_index])) {

                    $bridge = 'consumption_products_bridge';
                    $id_identify = 'order_id';
                    $quantity = $_POST['product_quantity'][$post_index];

					$product_discount = $_POST['product_discount'][$post_index];
                    $pricerino = $_POST['product_price'][$post_index];
                    $product_original_price = $_POST['product_original_price'][$post_index];
                    $location = $_POST['location'];
                    $order_client = $_POST['usah'];

                    $new_vat = $_POST['vat'];
                    $old_vat = $_POST['vat'];

                    $type = 'order';

                    $total_quantity = $quantity;

                    // VYSKLADNĚNÍ A PŘIPOJENÍ K TÉTO OBJEDNÁVCE
                    include_once CONTROLLERS . "/product-stock-update.php";

                    if ($reserve < $quantity) {

                        $quantity = $quantity - $reserve;

                        include CONTROLLERS . "/product-delivery-update.php";

                    }

                }

            }

        }

    }

    Header("Location:https://www.wellnesstrade.cz/admin/pages/accessories/historie-spotreby?success=add");
    exit;

}

$cliquery = $mysqli->query('SELECT user_name FROM demands') or die($mysqli->error);

include VIEW . '/default/header.php';

?>

<script type="text/javascript">
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
 var cloneCount = 0;
$('#duplicatevirivka').click(function() {
 cloneCount = cloneCount + 1;
      $('#virdup').clone().attr('id', 'virdup'+ cloneCount).insertAfter('[id^=virdup]:last');
      $('#virdup'+ cloneCount).find('#virivkadup').attr('name', 'zbozickovirivka'+ cloneCount);
      $('#virdup'+ cloneCount).find('#field-2').attr('name', 'cenickavirivka'+ cloneCount);

});

 var cloneCount2 = 0;
$('#duplicatesauna').click(function() {
 cloneCount2 = cloneCount2 + 1;
      $('#saundup').clone().attr('id', 'saundup'+ cloneCount2).insertAfter('[id^=saundup]:last');
      $('#saundup'+ cloneCount2).find('#saunadup').attr('name', 'zbozickosauna'+ cloneCount2);
      $('#saundup'+ cloneCount2).find('#field-2').attr('name', 'cenickasauna'+ cloneCount2);

});

});


</script>

<?php

$cliquery = $mysqli->query('SELECT user_name FROM demands WHERE status = 5') or die($mysqli->error);
?>

<script type="text/javascript">
jQuery(document).ready(function($)
{

 $('.rad1').on('switch-change', function () {

 if($('#eh').prop('checked')){

 	$('#enable_custom_hidden').show("slow");

   }else if(!$('#nah').prop('checked')){


 	$('#enable_custom_hidden').hide("slow");
 	$('#enable_custom').prop('checked', false);

 	$('.rad2').bootstrapSwitch('setState', false);

 	$('#custom_text').hide("slow");

 }

});



 $('.rad2').on('switch-change', function () {

 if($('#enable_custom').prop('checked')){

 	$('#custom_text').show("slow");

   }else if(!$('#enable_custom').prop('checked')){


 	$('#custom_text').hide("slow");
 }

});


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
 var cloneCount = 0;
$('#duplicatevirivka').click(function() {
 cloneCount = cloneCount + 1;
      $('#virdup').clone().attr('id', 'virdup'+ cloneCount).insertAfter('[id^=virdup]:last');
      $('#virdup'+ cloneCount).find('#virivkadup').attr('name', 'zbozickovirivka'+ cloneCount);
      $('#virdup'+ cloneCount).find('#field-2').attr('name', 'cenickavirivka'+ cloneCount);

});

 var cloneCount2 = 0;
$('#duplicatesauna').click(function() {
 cloneCount2 = cloneCount2 + 1;
      $('#saundup').clone().attr('id', 'saundup'+ cloneCount2).insertAfter('[id^=saundup]:last');
      $('#saundup'+ cloneCount2).find('#saunadup').attr('name', 'zbozickosauna'+ cloneCount2);
      $('#saundup'+ cloneCount2).find('#field-2').attr('name', 'cenickasauna'+ cloneCount2);

});




 $('.radiodegreeswitch').on('switch-change', function () {

 if($('.radiodegree').prop('checked')){

 	$('.shipping').show("slow");

   }else if(!$('.radiodegree').prop('checked')){


 	$('.shipping').hide("slow");
 }

});




});
</script>

<style>

.has-warning .selectboxit-container .selectboxit { border-color: #ffd78a !important;}

.page-body .selectboxit-container .selectboxit-options { margin-top: 40px !important; width: 100% !important;}
.page-body .selectboxit-container .selectboxit { height: 40px;width: 100% !important;}
.page-body .selectboxit-container .selectboxit .selectboxit-text { line-height: 40px; }
.page-body .selectboxit-container .selectboxit .selectboxit-arrow-container { height: 40px;}
.page-body .selectboxit-container .selectboxit .selectboxit-arrow-container:after { line-height: 40px;}

.nicescroll-rails > div:hover {
  background: rgb(53, 174, 255) !important;
}

#custom-scroller { width: 500px; }
.col-2, .col-8, .col-3, .col-4, .col-6 {
  display: inline-block;
  padding: 5px 2%;
  vertical-align: top;
}

.item {
  margin-right: 10px;
}

.col-2 { width: 18%; }
.col-8 { width: 76%; }
.col-3 { width: 26%; }
.col-4 { width: 36%; }
.col-6 { width: 60%; }
.select2-drop img { width: 100%; margin: 2%; }

.bigdrop.select2-container .select2-results {max-height: 300px;}
.bigdrop .select2-results {max-height: 300px;}

</style>


<form id="orderform" role="form" method="post" class="form-horizontal form-groups-bordered validate" action="vlastni-spotreba?action=add" enctype="multipart/form-data">

	<div class="row">

		<div class="col-md-6">


<div class="panel panel-primary" data-collapsed="0">

				<div class="panel-heading">
					<div class="panel-title">
						<strong style="font-weight: 600;">---</strong>
					</div>

				</div>

						<div class="panel-body">





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






<script type="text/javascript">
jQuery(document).ready(function($)
{



$('#selectbox-o').select2({
    minimumInputLength: 2,
    ajax: {
      url: "/admin/data/autosuggest-products",
      dataType: 'json',
      data: function (term, page) {
        return {
          q: term
        };
      },
      results: function (data, page) {
        return { results: data };
      }
    },

    formatResult: format,
    formatSelection: format,
    escapeMarkup: function(m) { return m; }


  });


  function format(data) {
    if (!data.id) return data.text; // optgroup

        return "<img src='https://www.wellnesstrade.cz/data/stores/images/mini/" + data.seourl + ".jpg' height='20'/>" + data.text;

  }


$('#selectbox-o').on("change", function(e) {

	var data = $('#selectbox-o').select2('data');

//$("#empty-holder").load("/admin/controllers/modals/products?sku="+vlue);

	$('#specification_copy').clone(true).insertBefore("#duplicate_specification").attr('id', 'copied').addClass('has-success').show();

	$('#copied #copy_this_first').attr('name', 'product_name[]').attr('value', data.pure_text);

	$('#copied #copy_this_third').attr('name', 'product_sku[]').attr('value', data.id);

	$('#copied #copy_id').attr('name', 'product_id[]').attr('value', data.product_id);

	$('#copied #copy_variation_id').attr('name', 'product_variation_id[]').attr('value', data.variation_id);

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


		<!-- Product Name Select Box -->
		<div class="form-group">
		   <div class="col-sm-12">
		     <input id="selectbox-o" class="input-xlarge" name="optionvalue" type="hidden" data-placeholder="Vyberte produkt.." />
		   </div>
		</div>

		<hr>

		<div class="form-group">
	<label class="col-sm-1 control-label">Položky</label>

	<div class="col-sm-10" style="float:left; padding: 0;">


	<div id="specification_copy" class="specification" style="display: none; float:left; width: 100%;">

		<div class="col-sm-6" style="margin-bottom: 8px; padding: 0;">

			<input type="text" class="form-control" id="copy_this_first" name="copythis" value="" placeholder="Název produktu">

			<input type="text" class="form-control" id="copy_id" name="copythis" value="" style="display: none;">

			<input type="text" class="form-control" id="copy_variation_id" name="copythis" value="" style="display: none;">

			<input type="text" class="form-control" id="copy_this_third" name="copythis" value="" placeholder="SKU produktu" style="display: none;">

		</div>

		<div class="col-sm-1" style="padding: 0 0px 0 8px;">
			<input type="text" class="form-control text-center" id="copy_this_second" name="copythis" value="" placeholder="Počet">
		</div>

		<div class="col-sm-1" style="padding: 0 0px 0 11px;">
			<button type="button" class="remove_specification btn btn-red" style="float:left; padding: 6px 10px; cursor: pointer;"> <i class="entypo-trash"></i> </button>
		 </div>
	</div>


  <div id="empty-holder"></div>

		<button type="button" id="duplicate_specification" style="display: none;" class="btn btn-default btn-icon icon-left">
      </button>
  </div>
  </div>


 <hr>

  	<div class="form-group">
	<label class="col-sm-3 control-label">Pobočka k vypořádání</label>

	<div class="col-sm-9" style="float:left;">


			<?php

if ($location_id == '0') {$desired_location = 7;} else { $desired_location = $location_id;}

$locations_query = $mysqli->query("SELECT * FROM shops_locations l LEFT JOIN products_stocks s ON s.location_id = l.id AND s.product_id = '" . $product['id'] . "' GROUP BY l.id ORDER BY type ASC");

while ($location = mysqli_fetch_array($locations_query)) {

    ?>
				<div class="radio" style="width: 25%; float: left;">
					<label>
						<input type="radio" <?php if ($location_id == "") { ?>name="location"<?php } ?> value="<?= $location['id'] ?>" <?php if (($location['eshop_default'] && empty($location_id)) || $location['id'] == $desired_location) {
        echo 'checked';}if ($location_id != "") {echo ' disabled';}?>><?= $location['name'] ?>
					</label>
				</div>
			<?php } ?>

			<?php if ($location_id != "") { ?><input type="text" name="location" value="<?= $desired_location ?>" style="display: none;"><?php } ?>

		</div>
	</div>





						</div>
					</div>


					</div>


	</div>

	<center>
	<div class="form-group default-padding button-demo">
		<button type="submit" style="margin-bottom: 24px; margin-right: 26px; font-size: 20px; padding: 20px 50px 20px 140px; margin-top: 20px;" data-style="zoom-in" class="ladda-button btn btn-success btn-icon icon-left btn-lg"><i class="entypo-plus" style="line-height: 48px;font-size: 40px; padding: 10px 20px;"></i> <span class="ladda-label">Vyskladnit do vlastní spotřeby</span></button>
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

