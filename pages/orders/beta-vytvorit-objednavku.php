<?php
use Salamek\Zasilkovna as Zasilkovna;

include $_SERVER['DOCUMENT_ROOT'] . '/admin/config/config.php';

// todo move somwhere?
include INCLUDES . '/functions.php';


/*
 * Naming Convention for Classes
 *
Verb        URI	                    Action	    Route Name
GET	        /photos	                index	    photos.index
GET	        /photos/create          create	    photos.create
POST	    /photos	                store	    photos.store
GET	        /photos/{photo}	        show	    photos.show
GET	        /photos/{photo}/edit	edit	    photos.edit
PUT/PATCH	/photos/{photo}	        update	    photos.update
DELETE	    /photos/{photo}	        destroy	    photos.destroy
 */

//$order = $em->find('Order', $id);

$categorytitle = 'Objednávky';
$pagetitle = 'Vytvořit objednávku';

/*if (isset($_REQUEST['order']) && $_REQUEST['order'] == 'new') {

    $newuser = 1;
    $sus = 1;

}*/

// define ACTION(edit, update, show, ...)

// $_REQUEST['action']






// create

// $_REQUEST['client']
if(!empty($_POST['client'])){ $orderClient = $_POST['client'];

} elseif(!empty($_REQUEST['client'])) {

    $orderClient = $_REQUEST['client'];

}

$address = '';
if (!empty($orderClient)) {

    $selectedquery = $mysqli->query('SELECT * FROM demands WHERE id = "' . $orderClient . '"') or die($mysqli->error);

    if (mysqli_num_rows($selectedquery) == 0) {

        $newuser = 1;

        $susernm = $firstname . ' ' . $lastname;

    } else {

        $selected = mysqli_fetch_assoc($selectedquery);
        $susernm = $selected['user_name'];

        $address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $selected['shipping_id'] . '" WHERE b.id = "' . $selected['billing_id'] . '"') or die($mysqli->error);
        $address = mysqli_fetch_assoc($address_query);

    }

}


// todo save somewhere instead of requests
$kurz_url = "http://www.cnb.cz/cs/financni_trhy/devizovy_trh/kurzy_devizoveho_trhu/denni_kurz.txt";
$kurz_data =  file_get_contents($kurz_url);
$output = explode("\n", $kurz_data);

unset($output[0]); // odstranění prvního řádku - datum
unset($output[count($output)]); // odstranění posledního řádku - nic neobsahuje
unset($output[1]); // odstranění druhého řádku - legenda pro CSV

$kurz = array("CZK" => 1);
foreach($output as $radek){
    $mena = explode("|", $radek);
    $kurz[trim($mena[3])] = str_replace(",",".",trim($mena[4]));
}

// get all non canceled demands
$query = $em->createQuery("SELECT d.id, d.user_name, p.brand, p.fullname FROM Demand d JOIN d.warehouse_product p WHERE d.status != 6");
$allDemands = $query->getResult();


$clientsArray = [];
foreach ($allDemands as $demand) {

    $current_array = array(
        'id' => $demand['id'],
        'text' => $demand['user_name'].' - '.$demand['brand'] . ' ' . ucfirst($demand['fullname']),
    );

    $clientsArray[] = $current_array;

}


if($pepega == true){

$clientsArray = [];
$clientsQuery = $mysqli->query('SELECT id, user_name, customer, product FROM demands WHERE status != 6') or die($mysqli->error);
while ($customer = mysqli_fetch_assoc($clientsQuery)) {

    $current_array = array(
        'id' => $customer['id'],
        'text' => $customer['user_name'].' - '.returnpn($customer['customer'], $customer['product']),
    );

    $clientsArray[] = $current_array;

}


$deliveryMethodsAll = $em->getRepository(ShopDeliveryMethod::class)->findAll();

// CZ todo merge in function
$query = $em->createQuery("SELECT s FROM ShopDeliveryMethod s 
                WHERE s.country = 'CZ' OR s.shop_method_id = 'local_pickup' 
                ORDER BY CASE 
                    WHEN s.shop_method_id like 'local_pickup' THEN 0
                    WHEN s.shop_method_id like 'ceske_sluzby%' THEN 1
                    WHEN s.shop_method_id like 'flat_rate' THEN 2
                    WHEN s.shop_method_id like 'free_shipping' THEN 3
                    ELSE 4 END, s.name");
$deliveryMethodsCZ = $query->getResult();


// SK todo merge in function
$query = $em->createQuery("SELECT s FROM ShopDeliveryMethod s 
                WHERE s.country = 'SK' OR s.shop_method_id = 'local_pickup' 
                ORDER BY CASE 
                    WHEN s.shop_method_id like 'local_pickup' THEN 0
                    WHEN s.shop_method_id like 'ceske_sluzby%' THEN 1
                    WHEN s.shop_method_id like 'flat_rate' THEN 2
                    WHEN s.shop_method_id like 'free_shipping' THEN 3
                    ELSE 4 END, s.name");
$deliveryMethodsSK = $query->getResult();


// EU todo merge in function
$query = $em->createQuery("SELECT s FROM ShopDeliveryMethod s 
                WHERE s.country = 'EU' OR s.shop_method_id = 'local_pickup' 
                ORDER BY CASE 
                    WHEN s.shop_method_id like 'local_pickup' THEN 0
                    WHEN s.shop_method_id like 'ceske_sluzby%' THEN 1
                    WHEN s.shop_method_id like 'flat_rate' THEN 2
                    WHEN s.shop_method_id like 'free_shipping' THEN 3
                    ELSE 4 END, s.name");
$deliveryMethodsEU = $query->getResult();


$shopPaymentMethods = $em->getRepository(ShopPaymentMethod::class)->findBy(array(), array('name'=>'ASC'));


// store
if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add') {


    $billing_zipcode = preg_replace('/\s+/', '', $_POST['billing_zipcode']);
    $billing_phone = preg_replace('/\s+/', '', $_POST['billing_phone']);
    $billing_email = preg_replace('/\s+/', '', $_POST['billing_email']);

    $final_vat = 100 + $_POST['vat'];

    $billing_id = 0;
    $mysqli->query("INSERT INTO 
                addresses_billing (
                   billing_company, 
                   billing_ico, 
                   billing_dic, 
                   billing_name, 
                   billing_surname, 
                   billing_street, 
                   billing_city, 
                   billing_zipcode, 
                   billing_country, 
                   billing_phone, 
                   billing_email
               ) VALUES (
                   '" . $_POST['billing_company'] . "', 
                   '" . $_POST['billing_ico'] . "', 
                   '" . $_POST['billing_dic'] . "', 
                   '" . $_POST['billing_name'] . "', 
                   '" . $_POST['billing_surname'] . "', 
                   '" . $_POST['billing_street'] . "', 
                   '" . $_POST['billing_city'] . "', 
                   '" . $billing_zipcode . "', 
                   '" . $_POST['billing_country'] . "', 
                   '" . $billing_phone . "', 
                   '" . $billing_email . "'
               )") or die($mysqli->error);

    $billing_id = $mysqli->insert_id;

    $hasShipping = false;
    $shipping_id = 0;
    if ($_POST['shipping_company'] != '' || $_POST['shipping_name'] != '' || $_POST['shipping_surname'] != '' || $_POST['shipping_street'] != '' || $_POST['shipping_city'] != '') {

        $hasShipping = true;
        $mysqli->query("INSERT INTO 
                addresses_shipping (
                    shipping_company, 
                    shipping_name, 
                    shipping_surname, 
                    shipping_street, 
                    shipping_city, 
                    shipping_zipcode, 
                    shipping_country
                ) VALUES (
                    '" . $_POST['shipping_company'] . "', 
                    '" . $_POST['shipping_name'] . "', 
                    '" . $_POST['shipping_surname'] . "', 
                    '" . $_POST['shipping_street'] . "', 
                    '" . $_POST['shipping_city'] . "', 
                    '" . $_POST['shipping_zipcode'] . "', 
                    '" . $_POST['shipping_country'] . "'
                )") or die($mysqli->error);

        $shipping_id = $mysqli->insert_id;

    }

    $selected_country = 'CZ';
    if($hasShipping || empty($_POST['billing_country'])){

        $selected_country = $_POST['shipping_country'];

    }elseif(!empty($_POST['billing_country'])){

        $selected_country = $_POST['billing_country'];

    }

    if($selected_country != 'CZ' && $selected_country != 'SK'){ $selected_country = 'EU'; }

    $delivery = $_POST['delivery_'.$selected_country];

    $get_delivery_price = $mysqli->query("SELECT price, transporter_company, link_name FROM shops_delivery_methods WHERE link_name = '" . $delivery . "'");
    $delivery_price = mysqli_fetch_array($get_delivery_price);

    if ($_POST['delivery_special_price'] != "") { $delivery_price['price'] = $_POST['delivery_special_price']; }

    if($delivery_price['transporter_company'] == 'Uloženka'){

        $shipping_location = $_POST['shipping_location_ul'];

    }elseif($delivery_price['transporter_company'] == 'Balík na poštu'){

        $shipping_location = $_POST['shipping_location_cp'];

    }elseif($delivery_price['transporter_company'] == 'Zásilkovna'){

        $shipping_location = $_POST['shipping_location_zasilkovna'];
        $shipping_location_id = $_POST['shipping_location_id_zasilkovna'];

    }else{

        $shipping_location = '';
        $shipping_location_id = 0;

    }

    $currency = $_POST['currency'];
    $exchange_rate = $_POST[$currency.'_rate'];

    $mysqli->query("INSERT INTO orders (
                    shipping_id, 
                    billing_id, 
                    vat, 
                    order_date, 
                    order_site, 
                    order_tracking_number, 
                    client_id, 
                    customer_email, 
                    customer_phone, 
                    order_status, 
                    customer_note, 
                    order_shipping_method, 
                    payment_method, 
                    delivery_price, 
                    location_id,
                    order_currency,
                    exchange_rate, 
                    admin_note, 
                    shipping_location, 
                    shipping_location_id, 
                    weight
                ) VALUES (
                    '" . $shipping_id . "', 
                    '" . $billing_id . "', 
                    '" . $_POST['vat'] . "',
                    now(),
                    '" . $_POST['site'] . "',
                    '" . $_POST['order_tracking_number'] . "', 
                    '" . $_POST['client'] . "', 
                    '" . $_POST['billing_email'] . "', 
                    '" . $_POST['billing_phone'] . "', 
                    '" . $_POST['order_status'] . "', 
                    '" . $mysqli->real_escape_string($_POST['customer_note']) . "', 
                    '" . $delivery . "', 
                    '" . $_POST['payment'] . "', 
                    '" . $delivery_price['price'] . "', 
                    '" . $_POST['location'] . "',
                    '" . $currency . "',
                    '" . $exchange_rate . "', 
                    '" . $mysqli->real_escape_string($_POST['admin_note']) . "', 
                    '" . $shipping_location . "', 
                    '" . $shipping_location_id . "', 
                    '" . $_POST['weight'] . "'
                 )") or die($mysqli->error);

    $old_id = $mysqli->insert_id;
    $id = '10'.$old_id;

    $mysqli->query("UPDATE orders SET id = '".$id."', reference_number = '".$id."' WHERE old_id = '".$old_id."'")or die($mysqli->error);

    $client_id = $_REQUEST['client'];

    $overall_purchase = 0;
    $overallcena = 0;

    include CONTROLLERS . '/product-stock-controller.php';

    if (isset($_POST['product_sku'])) {

        $post = array_filter($_POST['product_sku']);
        if (!empty($post)) {

            foreach ($post as $post_index => $posterino) {

                if (!empty($_POST['product_quantity'][$post_index])) {

                    $stock_allocation['posterino'] = $posterino;
                    $stock_allocation['id'] = $id;
                    $stock_allocation['bridge'] = 'orders_products_bridge';
                    $stock_allocation['id_identify'] = 'order_id';
                    $stock_allocation['quantity'] = $_POST['product_quantity'][$post_index];
                    $stock_allocation['location'] = $_POST['location'];
                    $stock_allocation['type'] = 'order';
                    $stock_allocation['quantity'] = $_POST['product_quantity'][$post_index];
                    $stock_allocation['total_quantity'] = $_POST['product_quantity'][$post_index];

                    $stock_allocation['price'] = product_price(
                        $_POST['product_price'][$post_index],
                        $_POST['product_original_price'][$post_index],
                        $_POST['vat'],
                        $_POST['vat'],
                        $_POST['product_discount'][$post_index]
                    );

                    $quantity = $_POST['product_quantity'][$post_index];

                    // VYSKLADNĚNÍ A PŘIPOJENÍ K TÉTO OBJEDNÁVCE
                    include_once CONTROLLERS . '/product-stock-update.php';
                    $response = stock_allocate($stock_allocation);

                    if ($response['reserve'] < $_POST['product_quantity'][$post_index]) {

                        $quantity -= $response['reserve'];
                        include CONTROLLERS . '/product-delivery-update.php';

                    }

                }

            }

        }

    }

    /* ZJIŠTĚNÍ CENY TOTAL SQL */

    // multiple same products = sum of products and later rounding
    // (15.63 - 3.126) + (15.63 - 3.126) =>  25.008 => rounding => 25.01!
    // todo tabulka orders, products a products_variations asi není potřeba
    // new simplified getPrice
    $getPriceNew = $mysqli->query("SELECT
                SUM(total) as total, SUM(purchase_price) as purchase_price, SUM(discountRounded) as discount_net
            FROM (
                SELECT round(((price - discount_net) * quantity), 2) as total,(purchase_price * quantity) as purchase_price, round(discount_net * quantity, 2) as discountRounded
                FROM orders_products_bridge
                WHERE aggregate_id = '".$id."' AND aggregate_type = 'order'
            ) as products")or die($mysqli->error);

    $price_data = mysqli_fetch_array($getPriceNew);

    $overallcena = $price_data['total'] + $delivery_price['price'];
    $overall_purchase = $price_data['purchase_price'];

    $coeficient = vat_coeficient($_POST['vat']);
    $price = get_price($overallcena, $coeficient);

    // if rounding
    $price['rounded'] = 0;
    if($_POST['payment'] == 'cash' || $_POST['payment'] == 'cod'){

        $price['single'] = round($price['single']);
        $price['rounded'] = number_format($price['single'] - $overallcena, 2, '.', '');

    }

    $mysqli->query("UPDATE orders SET total_vat = '".$price['vat']."', total_rounded = '".$price['rounded']."', total_without_vat = '".$price['without_vat']."', total = '".$price['single']."', order_purchase = '$overall_purchase', discount_net = '".$price_data['discount_net']."' WHERE id = '$id'");

    $reserved_query = $mysqli->query("SELECT quantity, reserved 
        FROM products p, orders_products_bridge o 
        WHERE p.id = o.product_id AND o.aggregate_id = '$id' AND o.aggregate_type = 'order'");

    $total_reserved = 0;
    $total_missing = 0;

    while ($reserv = mysqli_fetch_array($reserved_query)) {

        $total_reserved = $total_reserved + $reserv['reserved'];

        $total_missing = $total_missing + ($reserv['quantity'] - $reserv['reserved']);

    }

    if (isset($_POST['send_mail']) && $_POST['send_mail'] == 'yes') {

        if (isset($_POST['enable_custom']) && $_POST['enable_custom'] == 'yes') {

            $alternate_text = $_POST['custom_text'];

        }

        include INCLUDES . '/order_status_emails.php';

        Header('Location:https://www.wellnesstrade.cz/admin/pages/orders/zobrazit-objednavku?id=' . $id . '&success=edit&missing=' . $total_missing . '&reserved=' . $total_reserved . '&has_mail=true');

    } else {

        Header('Location:https://www.wellnesstrade.cz/admin/pages/orders/zobrazit-objednavku?id=' . $id . '&success=edit&missing=' . $total_missing . '&reserved=' . $total_reserved);

    }
    exit;

}


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

<script type="text/javascript">
    $(document).ready(function () {

        $('.currency').change(function() {

            let rate = $(this).data("value");
            let value = '';
            let currency = $(this).val();

            if(currency != 'CZK'){
                $('.calculator').show('slow');
            }else{
                $('.calculator').hide('slow');
            }

            $('.final_currency').attr("placeholder", $(this).val()).attr("data-rate", rate);
            $('.final_currency_shortcut').html($(this).val());
            $('.final_currency, .original_currency').val('');

            $('.price-control:visible').each(function(){

                if($(this).data("default") != undefined) {

                    value = $(this).data("default");

                } else if($(this).val() != undefined) {

                    value = $(this).val();

                }

                if(value != null && value != undefined && value != ''){

                    let exchange = (value / rate).toFixed(2);
                    $(this).val(exchange);

                }

            });

        });

        var methods = {
            <?php foreach($deliveryMethodsAll as $deliveryMethod) {
                echo $deliveryMethod->getLinkName().': '.$deliveryMethod->getPrice().', ';
            } ?>
        };

        $('.delivery_select').change(function(){

            let selected = $(this).val();

            let delivery = methods[$(this).val()];
            let rate = $('.currency:checked').data("value");

            let exchange = (delivery / rate).toFixed(2);

            $('#delivery_special_price').val(exchange);

            if(selected.includes('ulozenka')){

                $('.ceska_posta').hide('slow');
                $('.zasilkovna').hide('slow');
                $('.ulozenka').show('slow');

            }else if(selected.includes('balik_na_postu')){

                $('.ulozenka').hide('slow');
                $('.zasilkovna').hide('slow');
                $('.ceska_posta').show('slow');

            }else if(selected.includes('zasilkovna')){

                $('.ulozenka').hide('slow');
                $('.ceska_posta').hide('slow');
                $('.zasilkovna').show('slow');

            }else{

                $('.ulozenka').hide('slow');
                $('.zasilkovna').hide('slow');
                $('.ceska_posta').hide('slow');

            }

        });


        $(".billing_country").change(function() {

            var country = $(this).val();

            if(country != 'CZ' && country != 'SK'){
                country = 'EU';
            }

            if($('.shipping_country_group').is(":hidden") || $('.shipping_country').val() == ''){

                // alert(country);

                $('.delivery').hide();
                $('.delivery_'+country).show();

                $('.delivery_label').html(country);

            }

        });



        $(".shipping_country").change(function() {

            var country = $(this).val();

            if(country == ''){

                country = $(".billing_country").val();

            }

            if(country != 'CZ' && country != 'SK'){
                country = 'EU';
            }


            $('.delivery').hide();
            $('.delivery_'+country).show();

            $('.delivery_label').html(country);

        });




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

<form role="form" method="post" class="form-horizontal form-groups-bordered validate" action="vytvorit-objednavku?order=new&site=wellnesstrade" enctype="multipart/form-data">

    <input type="hidden" name="length" value="14">

    <div class="row">

        <div class="col-md-5">

            <div class="panel panel-primary" data-collapsed="0">

                <div class="panel-heading">
                    <div class="panel-title">
                        <strong style="font-weight: 600;">Klient <img src="https://www.wellnesstrade.cz/wp-content/uploads/2015/03/logoblack.png" style="height: 12px; margin-top: -2px;"></strong>
                    </div>

                </div>

                <div class="panel-body">
                    <div class="form-group <?php if (isset($_REQUEST['client']) && $_REQUEST['client'] == 'not_found') {echo 'validate-has-error';}?>" style="margin-top: 20px;">

                        <script type="text/javascript">

                            $(document).ready(function() {

                                var sampleArray = <?= json_encode($clientsArray) ?>;

                                $("#e10").select2({
                                    data: sampleArray,
                                    placeholder: "<?php if(!empty($selected['user_name'])){ echo $selected['user_name'].' - '.returnpn($selected['customer'], $selected['product']); }else{ echo 'Výběr klienta';}?>"
                                });

                            });
                        </script>
                        <div class="col-sm-12">
                            <div class="col-sm-8">
                                <input type="hidden" name="client" id="e10"/>

                            </div>
                            <div class="col-sm-4" style="padding-left: 0;">
                                <button type="submit" style="padding: 10px 18px 10px 50px; width:100%; height: 42px;" class="btn btn-blue btn-icon icon-left">
                                    Načíst údaje
                                    <i class="fa fa-download" style="padding: 14px 12px;"></i>
                                </button>
                                <?php if(!empty($selected['user_name'])){ ?>
                                    <a href="../demands/zobrazit-poptavku?id=<?= $selected['id'] ?>" target="_blank" class="btn btn-primary btn-icon icon-left" style="float: right; margin-top: 4px;">
                                        <i class="entypo-user"></i>
                                        Zobrazit poptávku
                                    </a>
                                <?php } ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>


<!--VYBRANÝ ZÁKAZNÍK Z KLIENTŮ-->

<form id="orderform" role="form" method="post" class="form-horizontal form-groups-bordered validate" action="vytvorit-objednavku?action=add" enctype="multipart/form-data">

    <div class="row">

        <div class="col-md-5">

            <input style="display: none;" type="text" name="site" value="wellnesstrade">
            <input type="text" style="display: none;" name="client" value="<?php if (isset($selected['id'])) { echo $selected['id']; }else{ echo '0'; } ?>">

            <div class="panel panel-primary" data-collapsed="0">

                <div class="panel-heading">
                    <div class="panel-title">
                        <strong style="font-weight: 600;">Interní informace</strong>
                    </div>
                </div>

                <div class="form-group">
                    <br>
                    <div class="col-sm-12">
                        <div class="col-sm-12">
                            <textarea name="admin_note" class="form-control autogrow" id="field-7"></textarea>
                        </div>
                    </div>
                </div>

            </div>


            <div class="panel panel-primary" data-collapsed="0">

                <div class="panel-heading">
                    <div class="panel-title">
                        <strong style="font-weight: 600;">Fakturační údaje</strong>
                    </div>
                </div>

                <div class="panel-body">

                    <?php billing_address($address); ?>

                    <div class="form-group">
                        <label for="field-7" class="col-sm-3 control-label">Doplňující informace</label>
                        <div class="col-sm-6">
                            <textarea name="customer_note" class="form-control autogrow" id="field-7"></textarea>
                        </div>
                    </div>

                </div>

            </div>

            <?php shipping_address($address); ?>

        </div>

        <div class="col-md-7" style="margin-top: -167px;">

            <div class="panel panel-primary" data-collapsed="0">

                <div class="panel-heading">
                    <div class="panel-title">
                        <strong style="font-weight: 600;">Položky</strong>
                    </div>

                </div>

                <div class="panel-body">
                    <?php
                    shop_accessories('', '', '', '');
                    ?>

                    <script type="text/javascript">
                        $(document).ready(function () {

                            $('.original_currency').on('input', function (e) {

                                let rate = $('.final_currency').data('rate');
                                let exchanged = $(this).val() / rate;

                                $('.final_currency').val(exchanged.toFixed(2));

                            });

                        });
                    </script>
                    <div class="form-group calculator" style="display: none;">
                        <hr>
                        <label for="field-2" class="col-sm-3 control-label">Kalkulátor měn</label>
                        <div class="form-label-group">
                            <div class="col-sm-3 has-metric">
                                <input type="text" class="form-control text-center original_currency" name="original_currency" value="" placeholder="CZK" style="padding: 0; height: 38px;">
                                <span class="input-group-addon">Kč</span>
                            </div>
                            <div class="col-sm-1">
                                <i class="fas fa-exchange-alt" style="padding: 10px 14px; font-size: 16px; color: #0d7eff"></i>
                            </div>
                            <div class="col-sm-3 has-metric">
                                <input type="text" class="form-control text-center final_currency" name="final_currency" value="" placeholder="" data-rate="" style="padding: 0; height: 38px;">
                                <span class="input-group-addon final_currency_shortcut"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-primary" data-collapsed="0">

                <div class="panel-heading">
                    <div class="panel-title">
                        <strong style="font-weight: 600;">Platební podmínky</strong>
                    </div>

                </div>


                <div class="panel-body">


                    <div class="form-group">
                        <label for="field-2" class="col-sm-3 control-label">Měna</label>

                        <div class="col-sm-9">

                            <div class="radio" style="float: left;">
                                <label>
                                    <input class="currency" type="radio" id="currency_czk" name="currency"
                                           data-value="1"
                                           data-ext="Kč" value="CZK" <?php if(!isset($order['order_currency']) || $order['order_currency'] == 'CZK'){ echo 'checked'; }?>>CZK
                                </label>
                                <input style="display: none;" name="CZK_rate" value="<?= $kurz["CZK"] ?>">

                            </div>
                            <div class="radio" style="float: left; margin-left: 30px;">
                                <label>
                                    <input class="currency" type="radio" id="currency_eur" name="currency" data-value="<?= $kurz["EUR"] ?>" data-ext="€" value="EUR" <?php if(isset($order['order_currency']) && $order['order_currency'] == 'EUR'){ echo 'checked'; }?>>EUR
                                </label>
                                <input style="display: none;" name="EUR_rate" value="<?= $kurz["EUR"] ?>">

                            </div>
                            <div class="radio" style="float: left; margin-left: 30px;">
                                <label>
                                    <input class="currency" type="radio" id="currency_usd" name="currency" data-value="<?= $kurz["USD"] ?>" data-ext="$" value="USD" <?php if(isset($order['order_currency']) && $order['order_currency'] == 'USD'){ echo 'checked'; }?>>USD
                                </label>
                                <input style="display: none;" name="USD_rate" value="<?= $kurz["USD"] ?>">
                            </div>
                        </div>


                    </div>

                    <hr>

                    <div class="form-group"><label for="field-2" class="col-sm-3 control-label">Aktuální kurz dle ČNB</label>
                        <div class="col-sm-8">
                            <h5>
                                <strong><?= $kurz["EUR"] ?></strong> CZK/EUR&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <strong><?= $kurz["USD"] ?></strong> CZK/USD</h5>
                        </div>
                    </div>

                    <hr>

                    <div class="form-group">
                        <div class="col-sm-6" style="padding: 0;">
                            <label class="col-sm-12 control-label" style="text-align: center; margin-bottom: 10px;">
                                Druh doručení ~ <span class="delivery_label">CZ</span>
                            </label>
                            <div class="col-sm-12">

                                <div class="delivery delivery_CZ">
                                    <select id="delivery_cz" name="delivery_CZ" class="selectboxit delivery_select">
                                        <?php foreach ($deliveryMethodsCZ as $deliveryMethod) { ?>
                                            <option value="<?= $deliveryMethod->getLinkName(); ?>">
                                                <?= $deliveryMethod->getShopTitle(); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="delivery delivery_SK" style="display: none;">
                                    <select id="delivery_sk" name="delivery_SK" class="selectboxit delivery_select">
                                        <?php foreach ($deliveryMethodsSK as $deliveryMethod) { ?>
                                            <option value="<?= $deliveryMethod->getLinkName(); ?>">
                                                <?= $deliveryMethod->getShopTitle(); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="delivery delivery_EU" style="display: none;">
                                    <select id="delivery_eu" name="delivery_EU" class="selectboxit delivery_select">
                                        <?php foreach ($deliveryMethodsEU as $deliveryMethod) { ?>
                                            <option value="<?= $deliveryMethod->getLinkName(); ?>">
                                                <?= $deliveryMethod->getShopTitle(); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6" style="padding: 0;">
                            <label class="col-sm-12 control-label" style="text-align: center; margin-bottom: 10px;">Způsob úhrady</label>
                            <div class="col-sm-12">
                                <select id="payment" name="payment" class="selectboxit">
                                    <?php foreach ($shopPaymentMethods as $paymentMethod) { ?>
                                        <option value="<?= $paymentMethod->getLinkName() ?>">
                                            <?= ucfirst($paymentMethod->getPayText()) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                        </div>
                    </div>

                    <?php

                    $apiKey = 'd977ce48de5a390f';
                    $apiPassword = 'd977ce48de5a390f08a4e7ad52af5181';

                    $api = new Zasilkovna\ApiRest($apiPassword, $apiKey);

                    $branch = new Zasilkovna\Branch($apiKey, new Zasilkovna\Model\BranchStorageSqLite());

                    $branch->getBranchList();

                    ?>
                    <script type="text/javascript">
                        var packetaApiKey = 'd977ce48de5a390f';
                        /*
                            This function will receive either a pickup point object, or null if the user
                            did not select anything, e.g. if they used the close icon in top-right corner
                            of the widget, or if they pressed the escape key.
                        */
                        function showSelectedPickupPoint(point)
                        {
                            var spanElement = document.getElementById('packeta-point-info');
                            var idElement = document.getElementById('packeta-point-id');
                            var nameStreetElement = document.getElementById('packeta-point-nameStreet');
                            if(point) {
                                var recursiveToString = function(o) {
                                    return Object.keys(o).map(
                                        function(k) {
                                            if(o[k] === null) {
                                                return k + " = null";
                                            }

                                            return k + " = " + (typeof(o[k]) == "object"
                                                    ? "<ul><li>" + recursiveToString(o[k]) + "</li></ul>"
                                                    : o[k].toString().replace(/&/g, '&amp;').replace(/</g, '&lt;')
                                            );
                                        }
                                    ).join("</li><li>");
                                };

                                spanElement.innerText =  point.nameStreet;

                                // spanElement.innerText =
                                //     "Address: " + point.name + "\n" + point.zip + " " + point.city + "\n\n"
                                //     + "All available fields:\n";

                                // spanElement.innerHTML +=
                                //     "<strong>" + recursiveToString(point) + "</strong>";

                                idElement.value = point.id;
                                nameStreetElement.value = point.nameStreet;
                            }
                            else {
                                spanElement.innerText = "";
                                idElement.value = "";
                                nameStreetElement.value = "";

                            }
                        };
                    </script>

                    <div class="form-group zasilkovna" style="margin-top: 10px; margin-bottom: 24px; display: none;">
                        <label class="col-sm-3 control-label" style="padding-top: 14px;"> <input type="button" onclick="Packeta.Widget.pick(packetaApiKey, showSelectedPickupPoint)" value="Výběr pobočky..." class="btn btn-info btn-md"></label>
                        <div class="col-sm-8">

                            <p style="padding: 20px 0 0;">Vybraná pobočka:
                                <input type="hidden" name="shipping_location_id_zasilkovna" id="packeta-point-id">
                                <input type="hidden" name="shipping_location_zasilkovna" id="packeta-point-nameStreet">
                                <span id="packeta-point-info" style="font-weight: bold;">žádná</span>
                            </p>

                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 10px; margin-bottom: 24px;">
                        <label class="col-sm-3 control-label" for="delivery_special_price">Cena doručení</label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control price-control" id="delivery_special_price" name="delivery_special_price" value="0">
                        </div>
                    </div>


                    <div class="form-group">
                        <label class="col-sm-3 control-label">DPH %</label>
                        <div class="col-sm-9">
                            <div class="radio" style="width: 100px; float: left;">
                                <label>
                                    <input type="radio" name="vat" value="21" checked>21%
                                </label>
                            </div>
                            <div class="radio" style="width: 100px;float: left;">
                                <label>
                                    <input type="radio" name="vat" value="15">15%
                                </label>
                            </div>
                            <div class="radio" style="width: 100px; float: left;">
                                <label>
                                    <input type="radio" name="vat" value="10">10%
                                </label>
                            </div>
                            <div class="radio" style="width: 100px;float: left;">
                                <label>
                                    <input type="radio" name="vat" value="0">0%
                                </label>
                            </div>
                        </div>
                    </div>



                </div>
            </div>

            <div class="panel panel-primary" data-collapsed="0">

                <div class="panel-heading">
                    <div class="panel-title">
                        <strong style="font-weight: 600;">Stav objednávky</strong>
                    </div>

                </div>

                <div class="panel-body">


                    <div class="form-group" style="margin-top: 10px; margin-bottom: 24px;">
                        <label class="col-sm-3 control-label" for="order_status" style="padding-top: 14px;">Stav objednávky</label>
                        <div class="col-sm-6">
                            <select id="order_status" name="order_status" class="selectboxit">
                                <option value="0">Nezpracovaná</option>
                                <option value="1">V řešení</option>
                                <option value="2">Připravená</option>
                                <option value="3">Vyexpedovaná</option>
                                <option value="4">Stornovaná</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 24px;">
                        <label class="col-sm-4 control-label" for="eh" style="padding-top: 7px;">Informovat zákazníka o změně stavu</label>
                        <div class="col-sm-6">
                            <div class="radiodegreeswitch rad1 make-switch switch-small" style="float: left; margin-right:11px; margin-top: 2px;" data-on-label="<i class='entypo-mail'></i>" data-off-label="<i class='entypo-cancel'></i>">
                                <input class="radiodegree" name="send_mail" id="eh" value="yes" type="checkbox"/>
                            </div>

                        </div>
                    </div>


                    <div class="form-group" id="enable_custom_hidden" style="display: none;">
                        <label class="col-sm-4 control-label" for="enable_custom" style="padding-top: 7px;">Vlastní úvodní text emailu</label>
                        <div class="col-sm-6">
                            <div class="radiodegreeswitch rad2 make-switch switch-small" style="float: left; margin-right:11px; margin-top: 2px;" data-on-label="<i class='entypo-pencil'></i>" data-off-label="<i class='entypo-cancel'></i>">
                                <input class="radiodegree" name="enable_custom" id="enable_custom" value="yes" type="checkbox"/>
                            </div>

                        </div>
                    </div>

                    <div class="form-group" id="custom_text" style="display: none;">
                        <label class="col-sm-3 control-label" for="ee" style="padding-top: 7px;">Úvodní text emailu</label>
                        <div class="col-sm-9">

                            <textarea name="custom_text" class="form-control autogrow" id="field-7" style="height: 140px;"></textarea>

                        </div>
                    </div>


                    <div class="form-group">
                        <label class="col-sm-2 control-label" for="tracking_code" style="padding-top: 14px;">Sledovací číslo</label>
                        <div class="col-sm-4">
                            <input type="text" style="height: 40px;" name="order_tracking_number" class="form-control" id="tracking_code" placeholder="Sledovací číslo" value="">
                        </div>
                        <label class="col-sm-2 control-label" for="weight" style="padding-top: 14px;">Váha</label>
                        <div class="col-sm-4">
                            <input type="number" style="height: 40px;" name="weight" class="form-control" id="weight" placeholder="Váha" value="0">
                        </div>
                    </div>

                </div>
            </div>
        </div>


    </div>

    <center>
        <div class="form-group default-padding button-demo">
            <button type="submit" style="margin-bottom: 24px; margin-right: 26px; font-size: 20px; padding: 20px 50px 20px 140px; margin-top: 20px;" data-style="zoom-in" class="ladda-button btn btn-success btn-icon icon-left btn-lg"><i class="entypo-plus" style="line-height: 48px;font-size: 40px; padding: 10px 20px;"></i> <span class="ladda-label">Vytvořit objednávku</span></button>
        </div></center>

</form>
<?php } ?>
<footer class="main">


    &copy; <?= date('Y') ?> <span style=" float:right;"><?php
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $finish = $time;
        $total_time = round(($finish - $start), 4);

        echo 'PHP '.PHP_VERSION.' | Page generated in ' . $total_time . ' seconds.';?></span>

</footer>	</div>


</div>

<script type="text/javascript">

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

<script src="https://widget.packeta.com/v6/www/js/library.js"></script>

<?php include VIEW . '/default/footer.php'; ?>

