<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/googlelogin.php";
include INCLUDES . "/functions.php";

$id = $_REQUEST['id'];

$service_query = $mysqli->query('SELECT s.*, DATE_FORMAT(s.date_added, "%d. %M %Y") as date_added, DATE_FORMAT(s.date, "%d. %M %Y") as dateformated, DATE_FORMAT(estimatedtime, "%H:%i") as hoursmins, c.title, d.user_name FROM services s LEFT JOIN services_categories c ON c.seoslug = s.category LEFT JOIN demands d ON d.id = s.creator_id WHERE s.id="' . $id . '"') or die($mysqli->error);

if (mysqli_num_rows($service_query) > 0) {

    $service = mysqli_fetch_array($service_query);

    $address_query = $mysqli->query('SELECT * FROM addresses_billing b LEFT JOIN addresses_shipping s ON s.id = "' . $service['shipping_id'] . '" WHERE b.id = "' . $service['billing_id'] . '"') or die($mysqli->error);
	$address = mysqli_fetch_assoc($address_query);

    $currency = currency($service['currency']);

    $pagetitle = 'Servis ' . $service['id'];

    $bread1 = "Editace servisů";
    $abread1 = "editace-servisu?state=".$service['state'];

    if (isset($_REQUEST['action']) && $_REQUEST['action'] == "remove_picture") {

        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/admin/data/images/services/' . $service['id'] . '/' . $_REQUEST['picture'])) {

            unlink($_SERVER['DOCUMENT_ROOT'] . '/admin/data/images/services/' . $service['id'] . '/' . $_REQUEST['picture']);

        }

        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/admin/data/images/services/' . $service['id'] . '/small_' . $_REQUEST['picture'])) {

            unlink($_SERVER['DOCUMENT_ROOT'] . '/admin/data/images/services/' . $service['id'] . '/small_' . $_REQUEST['picture']);

        }

        header('location: https://www.wellnesstrade.cz/admin/pages/services/zobrazit-servis?id=' . $id . '&remove=success');
        exit;
    }

    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'change_state') {


        $update = $mysqli->query("UPDATE services SET state = '" . $_POST['state'] . "' WHERE id = '$id'");

        include_once CONTROLLERS . "/product-stock-controller.php";

// NOVÝ STAV SERVISU = STORNO, PŮVODNÍ STAV = JAKÝKOLIV

        if (isset($_POST['state']) && $_POST['state'] == 'canceled' && $service['state'] != 'canceled') {

            $topupquery = $mysqli->query('SELECT b.product_id, b.reserved, p.customer, b.variation_id, b.location_id FROM services_products_bridge b, products p WHERE p.id = b.product_id AND b.aggregate_id="' . $_REQUEST['id'] . '"') or die($mysqli->error);
            while ($topup = mysqli_fetch_array($topupquery)) {

                $quantity = $topup['reserved'];

                if ($quantity > 0) {

                    product_update($topup['product_id'], $topup['variation_id'], $topup['location_id'], $quantity, $client['id'], 'service_cancel', $id);

                }

                $mysqli->query("UPDATE services_products_bridge SET reserved = '0' WHERE product_id = '" . $topup['product_id'] . "' AND aggregate_id = '" . $_REQUEST['id'] . "'");

            }

        // NOVÝ STAV SERVIUS = JAKÝKOLIV, PŮVODNÍ STAV = STORNO

        } elseif ($_POST['state'] != 'canceled' && $service['state'] == 'canceled') {

            $search_query = $mysqli->query("SELECT b.product_id, b.variation_id, b.quantity, b.location_id, s.instock FROM services_products_bridge b, products_stocks s WHERE s.product_id = b.product_id AND s.variation_id = b.variation_id AND s.location_id = b.location_id AND b.aggregate_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

            while ($search = mysqli_fetch_array($search_query)) {

                if ($search['quantity'] > $search['instock']) {

                    $reserve = $search['instock'];

                } else {

                    $reserve = $search['quantity'];

                }

                $mysqli->query("UPDATE services_products_bridge SET reserved = '$reserve' WHERE product_id = '" . $search['product_id'] . "' AND variation_id = '" . $search['variation_id'] . "' AND aggregate_id = '" . $_REQUEST['id'] . "'") or die($mysqli->error);

                $mysqli->query("UPDATE products_stocks SET instock = instock - $reserve WHERE product_id = '" . $search['product_id'] . "' AND variation_id = '" . $search['variation_id'] . "' AND location_id IN (SELECT id as location_id FROM shops_locations WHERE id = '" . $search['location_id'] . "')") or die($mysqli->error);

                api_product_update($search['product_id']);

            }

        }
		

		if ($service['gcalendar'] != "" && $_POST['state'] == 'canceled' && $service['state'] != 'canceled'){

            $update = $mysqli->query("UPDATE services SET gcalendar = '' WHERE id = '$id'");

            $gclient = new Google_Client();
            $gclient->setAuthConfigFile($_SERVER['DOCUMENT_ROOT'] . '/admin/config/client_secret.json');
            $gclient->setRedirectUri('https://' . $_SERVER['HTTP_HOST'] . '/admin/');

            $refreshToken = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/admin/config/tokens/token-" . $client['id'] . ".txt"); // load previously saved token
            $gclient->refreshToken($refreshToken);
            $tokens = $gclient->getAccessToken();
            $gclient->setAccessToken($tokens);

            $remove = new Google_Service_Calendar($gclient);
            $calendarId = 'kcie94kfi9absq10j3d8uijis8@group.calendar.google.com';
            $event = $remove->events->delete($calendarId, $service['gcalendar']);

        } elseif($service['gcalendar'] != "" && $service['date'] != '0000-00-00') {

            $klientus = $id;
            $customah = $service['customertype'];

            $_POST['details'] = $service['details'];
            $_POST['date'] = $service['date'];
            $_POST['estimatedtime'] = $service['estimatedtime'];

            saveCalendarEvent($id, 'service');

        }



        if (isset($_POST['send_mail']) && $_POST['send_mail'] == 'yes') {

            if (isset($_POST['enable_custom']) && $_POST['enable_custom'] == 'yes') {

                $alternate_text = $_POST['custom_text'];

            }

            include CONTROLLERS . "/mails/services.php";

        }



        if (!empty($_REQUEST['redirect_url'])) {

            $redirect_url = urldecode($_REQUEST['redirect_url']);

            Header("Location:https://www.wellnesstrade.cz/admin/" . $redirect_url);
            exit;

        } elseif (!empty($_REQUEST['link'])) {

            Header("Location:https://www.wellnesstrade.cz/admin/pages/services/editace-servisu?state=" . $_REQUEST['link'] . "&success=change_state");

        } else {

            Header("Location:https://www.wellnesstrade.cz/admin/pages/services/zobrazit-servis?id=" . $id . "&success=change_state");

        }
        exit;


    }

    include VIEW . '/default/header.php';

    ?>


	<div class="panel panel-primary" data-collapsed="0">



						<div class="panel-body">
<div class="invoice">

	<div class="row">

		<div class="col-sm-12 invoice-left">

			<div class="col-sm-3" style="padding: 0;">
			<h3 style="margin-top: 3px;margin-bottom: 14px;"><span style="font-size: 18px;">SERVIS Č.</span> #<?= $service['id'] ?> </h3>
			<p style="margin-bottom: 6px"><span style="width: 50px;display: inline-block;">Přidáno:</span> <span style="color: teal; font-weight: 400;font-size: 14px;"><?= $service['date_added'] ?></span> </p>
                <p style="margin-bottom: 10px;"><span style="width: 50px;display: inline-block;">Termín:</span>
                    <?php if(!empty($service['dateformated'])){ ?><span style="color: #da0003; font-weight: 400;font-size: 14px;"><?= $service['dateformated'] . ', ' . $service['hoursmins'] ?></span> <?php }else{ echo 'nestanoven'; } ?>

                </p>
			</div>
			<div class="col-sm-9">

				<ol class="breadcrumb bc-2" style="margin-top: 16px; margin-bottom: 0; float:left;">
                    <li <?php if (isset($service['state']) && $service['state'] == 'new') {echo 'class="active"';}?>> <?php if (isset($service['state']) && $service['state'] == 'new') {echo '<strong>Nový</strong>';} else { ?>Nový<?php } ?></li>

                    <li <?php if (isset($service['state']) && $service['state'] == 'waiting') {echo 'class="active"';}?>> <?php if (isset($service['state']) && $service['state'] == 'waiting') {echo '<strong>Čeká na díly</strong>';} else { ?>Čeká na díly<?php } ?></li>
                    
                    <li <?php if (isset($service['state']) && $service['state'] == 'unconfirmed') {echo 'class="active"';}?>> <?php if (isset($service['state']) && $service['state'] == 'unconfirmed') {echo '<strong>Nepotvrzený</strong>';} else { ?>Nepotvrzený<?php } ?></li>


                    <li <?php if (isset($service['state']) && $service['state'] == 'confirmed') {echo 'class="active"';}?>> <?php if (isset($service['state']) && $service['state'] == 'confirmed') {echo '<strong>Potvrzený</strong>';} else { ?>Potvrzený<?php } ?></li>

                    <li <?php if (isset($service['state']) && $service['state'] == 'executed') {echo 'class="active"';}?>> <?php if (isset($service['state']) && $service['state'] == 'executed') {echo '<strong>Provedený</strong>';} else { ?>Provedený<?php } ?></li>

                    <li <?php if (isset($service['state']) && $service['state'] == 'unfinished') {echo 'class="active"';}?>> <?php if (isset($service['state']) && $service['state'] == 'unfinished') {echo '<strong>Nedokončený</strong>';} else { ?>Nedokončený<?php } ?></li>

                    <li <?php if (isset($service['state']) && $service['state'] == 'warranty') {echo 'class="active"';}?>> <?php if (isset($service['state']) && $service['state'] == 'warranty') {echo '<strong>Reklamace</strong>';} else { ?>Reklamace<?php } ?></li>
				</ol>

				<ol class="breadcrumb bc-2" style="<?php if (isset($service['state']) && $service['state'] == 'finished') { ?>background-color: #00a651; border-color: #009649; color: #FFFFFF !important;<?php } ?>padding-left: 9px; margin-top: 16px; margin-left: 8px; margin-bottom: 0; float:left;">
					<i class="entypo-check"></i>
					<li <?php if (isset($service['state']) && $service['state'] == 'finished') {echo 'class="active" style="color: #FFF;"';}?>> <?php if (isset($service['state']) && $service['state'] == 'finished') {echo 'Hotový';} else { ?>Hotový<?php } ?></li>
				</ol>

				<ol class="breadcrumb bc-2" style="<?php if (isset($service['state']) && $service['state'] == 'canceled') { ?>background-color: #000; border-color: #000; color: #FFFFFF !important;<?php } ?>padding-left: 9px; margin-top: 16px; margin-left: 8px; margin-bottom: 0; float:left;">
					<i class="entypo-trash"></i>
					<li <?php if (isset($service['state']) && $service['state'] == 'canceled') {echo 'class="active" style="color: #FFF;"';}?>> <?php if (isset($service['state']) && $service['state'] == 'canceled') {echo 'Stornovaný';} else { ?>Stornovaný<?php } ?></li>
				</ol>

			</div>
		</div>
	</div>


	<hr class="margin" style="margin: 0 0 20px;" />




<div class="col-sm-12" style="padding: 0; margin-bottom: 34px; float: left;">


	<div class="col-sm-7 invoice-left" style="width: 28%; padding: 0;">

        <div class="col-sm-12" style="padding: 0; padding-right: 16px;">


            <div class="col-sm-12 alert alert-default" style="background-color: #f7f7f7;padding-bottom: 0;padding: 10px; font-size: 12px;">

                <table class="table table-stripped table-hover" style="width: 100%;  text-align: left; ">
                    <thead>
                    <tr>
                        <th colspan="2">
                            <h4>Informace o zákazníkovi</h4>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($service['clientid'] != 0) {

                        $client_address_query = $mysqli->query('SELECT c.id, b.billing_name, b.billing_surname, b.billing_company, s.shipping_name, s.shipping_surname, s.shipping_company FROM demands c LEFT JOIN addresses_billing b ON b.id = c.billing_id LEFT JOIN addresses_shipping s ON s.id = c.shipping_id WHERE c.id = "' . $service['clientid'] . '"') or die($mysqli->error);
                        $client_address = mysqli_fetch_assoc($client_address_query);

                        $name = user_name($client_address);
                        ?>

                        <br />

                        <tr>
                            <td>Klient:</td> <td><a href="/admin/pages/demands/zobrazit-poptavku?id=<?= $client_address['id'] ?>"><u><strong style="font-weight: 500 !important;"><?= $name ?></strong></u></a></strong></td>
                        </tr>

                        <?php

                    }
                 if ($address['billing_phone'] != "") { ?>
                    <tr>
                        <td>Telefonní číslo</td>
                        <td><strong style="font-weight: 500 !important;"><?= '' . $address['billing_phone'] ?></strong></td>
                    </tr>
                <?php }
                if ($address['billing_email'] != "") { ?>
                    <tr>
                        <td>E-mail</td>
                        <td><strong style="font-weight: 500 !important;"><?= '' . $address['billing_email'] ?></strong></td>
                    </tr>
                <?php } ?>

                    </tbody>
                </table>


            </div>

        </div>

			<div class="col-sm-12" style="padding: 0; padding-right: 16px;">


				<div class="alert alert-info">

				<?php if ($service['technical_details'] != "") {echo '<strong>Informace pro techniky:</strong> ' . $service['technical_details'];} else {echo 'žádné informace pro techniky';}?>

				<br><br>

				<strong>Proveditelé:</strong>
				<?php $admins_query = $mysqli->query("SELECT user_name FROM demands c, mails_recievers t WHERE t.type_id = '$id' AND t.admin_id = c.id AND t.type = 'service' AND t.reciever_type = 'performer'");

    $i = 0;
    while ($admins = mysqli_fetch_array($admins_query)) {

        $i++;
        if ($i == 1) {
            echo $admins['user_name'];
        } else {
            echo ', ' . $admins['user_name'];
        }
    }?>

                    <br><br>
    <strong>Informovaní:</strong>
    <?php $admins_query = $mysqli->query("SELECT user_name FROM demands c, mails_recievers t WHERE t.type_id = '$id' AND t.admin_id = c.id AND t.type = 'service' AND t.reciever_type = 'observer'");

    $i = 0;
    while ($admins = mysqli_fetch_array($admins_query)) {

        $i++;
        if ($i == 1) {
            echo $admins['user_name'];
        } else {
            echo ', ' . $admins['user_name'];
        }
    }?>

		            </div>

			<div class="alert alert-warning">

					<strong>Kategorie servisu:</strong> <?= $service['title'] ?><br><br>
					<strong>Zadavatel:</strong> <?= $service['user_name'] ?><br><br>

					<?php if ($service['details'] != "") {echo '<strong>Informace pro zákazníka:</strong> ' . $service['details'];} else {echo 'žádné informace pro zákazníka';}
					?><br><br>
                    <?php if ($service['internal_details'] != "") {echo '<strong>Interní informace:</strong> ' . $service['internal_details'];} else {echo 'žádné interní informace';}?>
				</div>

		            </div>



		</div>



    <div class="col-sm-8 alert alert-default" style="background-color: #f7f7f7;padding-bottom: 0;width: 48%;margin-right: 0.5%; padding: 10px 0; font-size: 12px;">

        <div class="col-sm-6 invoice-left" style="border-right: 1px solid #dedede; margin-bottom: 10px;">

            <table class="table table-stripped table-hover" style="width: 100%; float: left; text-align: left;">
                <thead>
                <tr>
                    <th colspan="2">
                        <h4>Fakturační údaje</h4>
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php if ($address['billing_company'] != "") { ?>
                    <tr>
                        <td>Firma</td>
                        <td><strong style="font-weight: 500 !important;"><?php  echo $address['billing_company']; ?></strong></td>
                    </tr>
                <?php  }
                if ($address['billing_ico'] != "") { ?>
                    <tr>
                        <td>IČO</td>
                        <td><strong style="font-weight: 500 !important;"><?php if ($address['billing_ico'] != "") { echo $address['billing_ico']; } ?></strong></td>
                    </tr>
                <?php  }
                if ($address['billing_dic'] != "") { ?>
                    <tr>
                        <td>DIČ</td>
                        <td><strong style="font-weight: 500 !important;"><?php if ($address['billing_dic'] != "") { echo $address['billing_dic'];} ?></strong></td>
                    </tr>
                <?php  }
                if ($address['billing_name'] != '' || $address['billing_surname'] != '') { ?>
                    <tr>
                        <td>Jméno a příjmení</td>
                        <td><strong style="font-weight: 500 !important;"><?= $address['billing_name'] . ' ' . $address['billing_surname'] ?></strong></td>
                    </tr>
                <?php  }
                if ($address['billing_street'] != "") { ?>
                    <tr>
                        <td>Ulice</td>
                        <td><strong style="font-weight: 500 !important;"><?= $address['billing_street'] ?></strong></td>
                    </tr>
                <?php  }
                if ($address['billing_city'] != "") { ?>
                    <tr>
                        <td>Město a PSČ</td>
                        <td><strong style="font-weight: 500 !important;"><?= '' . $address['billing_city'] ?>&nbsp;&nbsp;&nbsp;&nbsp;<?= $address['billing_zipcode'] ?></strong></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td>Země</td>
                    <td><strong style="font-weight: 500 !important;"><?php if (isset($address['billing_country']) && $address['billing_country'] == 'CZ') {echo 'Česká republika';} elseif (isset($address['billing_country']) && $address['billing_country'] == 'SK') {echo 'Slovensko';} elseif (isset($address['billing_country']) && $address['billing_country'] == 'PL') {echo 'Polsko';} elseif (isset($address['billing_country']) && $address['billing_country'] == 'AT') {echo 'Rakousko';} else {echo $address['billing_country'];} ?></strong></td>
                </tr>

                <?php if ($service['shipping_details'] != "") {echo '<div class="alert alert-info">' . $service['shipping_details'] . '</div>';}?>

                </tbody>
            </table>

        </div>

        <div class="col-sm-6 invoice-left">

            <?php if ($service['shipping_id'] != 0){ ?>
                <table class="table table-stripped table-hover" style="width: 100%; float: left; text-align: left;">
                    <thead>
                    <tr>
                        <th colspan="2">
                            <h4>Doručovací údaje</h4>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($address['shipping_company'] != "") { ?>
                        <tr>
                            <td>Firma</td>
                            <td><strong style="font-weight: 500 !important;"><?php  echo $address['shipping_company']; ?></strong></td>
                        </tr>
                    <?php  }
                    if ($address['shipping_ico'] != "") { ?>
                        <tr>
                            <td>IČO</td>
                            <td><strong style="font-weight: 500 !important;"><?php if ($address['shipping_ico'] != "") { echo $address['shipping_ico']; } ?></strong></td>
                        </tr>
                    <?php  }
                    if ($address['shipping_dic'] != "") { ?>
                        <tr>
                            <td>DIČ</td>
                            <td><strong style="font-weight: 500 !important;"><?php if ($address['shipping_dic'] != "") { echo $address['shipping_dic'];} ?></strong></td>
                        </tr>
                    <?php  }
                    if ($address['shipping_name'] != '' || $address['shipping_surname'] != '') { ?>
                        <tr>
                            <td>Jméno a příjmení</td>
                            <td><strong style="font-weight: 500 !important;"><?= $address['shipping_name'] . ' ' . $address['shipping_surname'] ?></strong></td>
                        </tr>
                    <?php  }
                    if ($address['shipping_street'] != "") { ?>
                        <tr>
                            <td>Ulice</td>
                            <td><strong style="font-weight: 500 !important;"><?= $address['shipping_street'] ?></strong></td>
                        </tr>
                    <?php  }
                    if ($address['shipping_city'] != "") { ?>
                        <tr>
                            <td>Město a PSČ</td>
                            <td><strong style="font-weight: 500 !important;"><?= '' . $address['shipping_city'] ?>&nbsp;&nbsp;&nbsp;&nbsp;<?= $address['shipping_zipcode'] ?></strong></td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td>Země</td>
                        <td><strong style="font-weight: 500 !important;"><?php if (isset($address['shipping_country']) && $address['shipping_country'] == 'CZ') {echo 'Česká republika';} elseif (isset($address['shipping_country']) && $address['shipping_country'] == 'SK') {echo 'Slovensko';} elseif (isset($address['shipping_country']) && $address['shipping_country'] == 'PL') {echo 'Polsko';} elseif (isset($address['shipping_country']) && $address['shipping_country'] == 'AT') {echo 'Rakousko';} else {echo $address['shipping_country'];} ?></strong></td>
                    </tr>

                    </tbody>
                </table>
            <?php } ?>

        </div>



        <div style="clear: both;"></div>

    </div>





    <?php

    $payment_query = $mysqli->query("SELECT name FROM shops_payment_methods WHERE link_name = '" . $service['payment_method'] . "'") or die($mysqli->error);
    $payment = mysqli_fetch_array($payment_query);

    ?>



    <div class="col-sm-6 alert alert-default" style="background-color: #f7f7f7;padding-bottom: 10px;width: 23%; padding: 10px 4px;">

        <div class="col-md-12 invoice-left" style="padding: 0;">


            <table class="table table-stripped table-hover" style="width: 100%; float: right; text-align: left; font-size: 13px;">
                <thead>
                <tr>
                    <th colspan="2">
                        <h4>Platba</h4>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Způsob úhrady:</td>
                    <td><strong><?= $payment['name'] ?></strong></td>
                </tr>
                <tr>
                    <td>Variabilní symbol:</td>
                    <td><?php if (isset($service['payment_method']) && $service['payment_method'] == 'bacs') { ?><strong><?= $service['id'] ?>000</strong><?php } else { ?>žádný<?php } ?></td>
                </tr>
                </tbody>
            </table>

            <?php

            $check_invoice = $mysqli->query("SELECT id FROM orders_invoices WHERE order_id = '".$service['id']."' AND type = 'active'")or die($mysqli->error);

            if(mysqli_num_rows($check_invoice) > 0){
                $invoice = mysqli_fetch_assoc($check_invoice);

            }else{
                $invoice = '';
            }

            if(!empty($service['order_date'])){ $service_date = date("Y-m-d", strtotime($service['order_date'])); }



            // if bankwire
            if($service['payment_method'] == 'bacs' && !empty($invoice)){

                $bank_sum_query = $mysqli->query("SELECT SUM(value) as total FROM bank_transactions WHERE account = 'order' AND (vs = '".$service['id']."' OR manual_assign = '".$service['id']."' OR vs = '".$invoice['id']."') AND date >= '".$service_date."'")or die($mysqli->error);
                $bank_sum = mysqli_fetch_assoc($bank_sum_query);

                if (isset($bank_sum['total']) && $bank_sum['total'] != '0') {

                    if (isset($service['paid_value']) && $bank_sum['total'] == $service['total']) {

                        $payment_info = '<i class="entypo-check"></i> zaplaceno';
                        $color = 'color: #00a651';

                    } else {

                        $payment_info = '<i class="entypo-block"></i> problém: '.thousand_seperator($bank_sum['total'] - $service['total']).$currency['sign'];;
                        $color = 'color: #d42020;';

                    }

                }else{

                    $payment_info = '<i class="entypo-back-in-time"></i> čeká na platbu';
                    $color = 'color: #ff9600;';

                }


            }elseif($service['payment_method'] == 'agmobindercardall' || $service['payment_method'] == 'agmobinderbank'){

                // check comgate
                $comgate_query = $mysqli->query("SELECT * FROM transactions_comgate WHERE id = '".$service['transaction_id']."'")or die($mysqli->error);

                if(mysqli_num_rows($comgate_query) > 0){
                    $comgate = mysqli_fetch_assoc($comgate_query);


                    if ($comgate['status'] == 'PAID' && $comgate['value'] == $service['total']) {

                        $payment_info = '<i class="entypo-check"></i> comgate: zaplaceno';
                        $color = 'color: #00a651';

                    } elseif ($comgate['status'] == 'PAID' && $comgate['value'] != $service['total']) {

                        $payment_info = '<i class="entypo-block"></i>comgate: problém: '. thousand_seperator($comgate['value'] - $service['total']).$currency['sign'];
                        $color = 'color: #d42020;';

                    } elseif ($comgate['status'] == 'PENDING') {

                        $payment_info = '<i class="entypo-back-in-time"></i>comgate: čeká na platbu';
                        $color = 'color: #ff9600;';

                    } elseif ($comgate['status'] == 'CANCELLED') {

                        $payment_info = '<i class="entypo-trash"></i>comgate: stornovaná';
                        $color = 'color: #000;';

                    }


                }else{

                    $payment_info = '-';
                    $color = 'color: #373e4a;';

                }

            }else{

                $payment_info = '-';
                $color = 'color: #373e4a;';

            }

            ?>


            <table class="table table-stripped table-hover" style="width: 100%; float: right; text-align: left; font-size: 13px;">
                <thead>
                <tr>
                    <th colspan="2">
                        <h4>Cena</h4>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Celkem bez dph:</td>
                    <td><strong><?= thousand_seperator($service['total_without_vat']).$currency['sign'] ?></strong></td>
                </tr>
                <tr>
                    <td>DPH <?= $service['vat'] ?>%:</td>
                    <td><strong><?= thousand_seperator($service['total_vat']).$currency['sign'] ?></strong></td>
                </tr>
                <?php if($service['total_rounded'] != '0.00'){ ?>
                    <tr>
                        <td>Zaokrouhleno: </td>
                        <td><strong><?= thousand_seperator($service['total_rounded']).$currency['sign'] ?></strong></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td><strong>Cena celkem:</strong></td>
                    <td>
                        <h4 style="font-size: 18px; margin-bottom: 0; font-family: inherit;  font-weight: normal;<?= $color ?> "><strong style="<?= $color ?>; font-weight: 600; "><?= thousand_seperator($service['price']) ?></strong> <?= $currency['sign'] ?></h4></td>
                </tr>                        <tr>
                    <td>Poznámka k platbě:</td>
                    <td> <span style="font-size: 13px;  font-weight: normal; <?= $color ?>"><?= $payment_info ?></span>
                    </td>
                </tr>

                </tbody>
            </table>


        </div>

    </div>


		</div>


	<div class="margin"></div>

	<table class="table table-bordered table-hover">
		<thead>
			<tr>
                <th style="background-color: #f9f9f9 !important; color: #222;" class="text-center">#</th>
                <th style="background-color: #f9f9f9 !important; color: #222;" width="30%">Položka</th>
                <th style="background-color: #f9f9f9 !important; color: #222;" width="90px" class="text-center">Počet</th>
                <th style="background-color: #f9f9f9 !important; color: #222;" width="130px" class="text-center">Rezervováno</th>
                <th style="background-color: #f9f9f9 !important; color: #222;" width="90px" class="text-center">Na cestě</th>
                <th style="background-color: #f9f9f9 !important; color: #222;" width="90px" class="text-center">Chybí</th>
                <th style="background-color: #f9f9f9 !important; color: #222;" class="text-center">Původní cena</th>
                <th style="background-color: #f9f9f9 !important; color: #222;" class="text-center">Cena za mj.</th>
                <th style="background-color: #f9f9f9 !important; color: #222;" class="text-center">Sleva</th>
                <th style="background-color: #f9f9f9 !important; color: #222;" class="text-right">Cena celkem</th>
			</tr>
		</thead>

		<tbody>
			<?php

    $bridge_query = $mysqli->query("SELECT * FROM services_products_bridge WHERE aggregate_id = '$id'");

    $price_with_dph = 0;
    $i = 0;


    $has_discount = false;
    $total_discount = 0;
    while ($bridge = mysqli_fetch_array($bridge_query)) {

        $i++;

        $products_query = $mysqli->query("SELECT *, id as ajdee FROM products WHERE id = '" . $bridge['product_id'] . "'");

        if (mysqli_num_rows($products_query) == 1) {

            $product = mysqli_fetch_array($products_query);

            ?>
			<tr>
				<td class="text-center" style="vertical-align: middle;"><?= $i ?></td>
				<td><a href="../accessories/zobrazit-prislusenstvi?id=<?= $product['ajdee'] ?>" target="_blank">
<?php

            if ($bridge['variation_id'] != 0) {

                $variation_sku_query = $mysqli->query("SELECT id, sku, main_warehouse FROM products_variations WHERE id = '" . $bridge['variation_id'] . "'");
                $variation_sku = mysqli_fetch_array($variation_sku_query);

                $main_warehouse = $variation_sku['main_warehouse'];

                $path = PRODUCT_IMAGE_PATH.'/small/' . $product['seourl'] . '_variation_'.$variation_sku['id'].'.jpg';
                $path_product = PRODUCT_IMAGE_PATH.'/small/' . $product['seourl'] . '.jpg';

                if(file_exists($path)){
                    $imagePath = '/data/stores/images/small/'.$product['seourl'].'_variation_'.$variation_sku['id'].'.jpg';
                }elseif(file_exists($path_product)){
                    $imagePath = '/data/stores/images/small/'.$product['seourl'].'.jpg';
                }else{
                    $imagePath = '/data/assets/no-image-7.jpg';
                }

            } else {

                $main_warehouse = $product['main_warehouse'];

                $path = PRODUCT_IMAGE_PATH.'/small/' . $product['seourl'] . '.jpg';
                if(file_exists($path)){
                    $imagePath = '/data/stores/images/small/'.$product['seourl'].'.jpg';
                }else{
                    $imagePath = '/data/assets/no-image-7.jpg';
                }

            }

            if (isset($bridge['quantity']) && isset($bridge['reserved']) && ($bridge['quantity'] - $bridge['reserved']) > 0) {
                $border = 'border: 1px dashed #ff0000';
            } else {
                $border = 'border: 1px solid #ebebeb';
            }

            echo '<img src="'.$imagePath.'" width="40" height="45.55" style="float: left; margin-right: 12px; '.$border.' ">';


            if(!empty($bridge['discount'])){

                $has_discount = true;

                $total_discount += round(($bridge['price'] / 100 * ($bridge['discount'])) * $bridge['quantity'], 2, PHP_ROUND_HALF_DOWN);

            }

?>
					<strong style="<?php if (isset($bridge['variation_id']) && $bridge['variation_id'] == 0) { ?>padding-top: 5px; display: block;<?php } ?>font-weight: 500;"><?= $product['productname'] ?> - <small class="tooltip-primary" data-toggle="tooltip" data-placement="top" title="" data-original-title="SKU"><?php

            if ($bridge['variation_id'] != 0) {

                echo $variation_sku['sku'];

            } else {

                echo $product['code'];
            }

            ?></small></strong></a>

					<?php if ($bridge['variation_id'] != 0) {

                echo '<span style="font-size: 12px; font-weight: 300;">';

                $variation_query = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $bridge['variation_id'] . "'");

                while ($variation = mysqli_fetch_array($variation_query)) {
                    echo '<br>';
                    echo $variation['name'] . ': ' . $variation['value'];

                }

                echo '</span>';

            }
            ?></td>
                <td class="text-center" style="vertical-align: middle;"><?= $bridge['quantity'] ?></td>
                <td class="text-center" style="vertical-align: middle;"><strong class="text-success"><?= $bridge['reserved'] ?></strong></td>
                <td class="text-center" style="vertical-align: middle;"><strong class="text-info"><?= $bridge['delivered'] ?></strong></td>
				<td class="text-center" style="vertical-align: middle;"><?php

            if (($bridge['quantity'] - $bridge['reserved']) > 0) { ?>

<strong class="text-danger">-<?= $bridge['quantity'] - $bridge['reserved'] ?></strong>

				<?php } else { ?>-<?php } ?></td>
                <td class="text-center" style="vertical-align: middle;"><?php

                    if($service['currency'] === 'CZK'){
                        echo number_format($bridge['original_price'], 2, ',', ' ');
                    }else{
                        echo number_format($bridge['original_price'] / $service['exchange_rate'], 2, ',', ' ');
                    }

                    echo $currency['sign']; ?></td>

                <td class="text-center" style="vertical-align: middle;"><?= number_format($bridge['price'], 2, ',', ' ').$currency['sign'] ?></td>

                <td class="text-center" style="vertical-align: middle;"><?php

                    if(!empty($bridge['discount'])) {

                        echo $bridge['discount']; ?> %
                        <br>
                        <small><?= $bridge['discount_net'] * $bridge['quantity'].$currency['sign'];?></small>
                    <?php }else{ echo '-'; }
                    ?></td>
                <td class="text-right" style="vertical-align: middle;"><strong><?= number_format($bridge['price'] * $bridge['quantity'], 2, ',', ' ').$currency['sign'] ?></strong></td>
			</tr>
<?php
        } else { ?>


<tr>
				<td class="text-center" style="vertical-align: middle;"><?= $i ?></td>
				<td><strong>Neznámý produkt</strong> <?= $bridge['product_name'] ?> - <small><?= $bridge['variation_values'] ?></small></td>
				<td class="text-center" style="vertical-align: middle;"><?= $bridge['quantity'] ?></td>
				<td class="text-center" style="vertical-align: middle;"><strong class="text-success"><?= $bridge['reserved'] ?></strong></td>
				<td class="text-center" style="vertical-align: middle;"><?php

            if (($bridge['quantity'] - $bridge['reserved']) > 0) { ?>

<strong class="text-danger">-<?= $bridge['quantity'] - $bridge['reserved'] ?></strong>

				<?php } else { ?>-<?php } ?></td>
    <td class="text-center" style="vertical-align: middle;"><?= number_format($bridge['original_price'], 0, ',', ' ').$currency['sign'] ?></td>

    <td class="text-center" style="vertical-align: middle;"><?= number_format($bridge['price'], 2, ',', ' ').$currency['sign'] ?></td>
    <td class="text-right" style="vertical-align: middle;"><strong><?= number_format($bridge['price'] * $bridge['quantity'], 2, ',', ' ').$currency['sign'] ?></strong></td>
			</tr>



<?php

        }

        $price_with_dph = $price_with_dph + ($bridge['price'] * $bridge['quantity']);
    }

    $i++;

            $items_query = $mysqli->query("SELECT * FROM services_items WHERE service_id = '".$id."'")or die($mysqli->error);
            while($item = mysqli_fetch_assoc($items_query)){
                ?>
				<tr>
					<td class="text-center" style="vertical-align: middle;"><?= $i; $i++ ?></td>
					<td height="63" style="vertical-align: middle;"><i class="fas fa-wrench" style="width: 40px;height: 46px;line-height: 45px;text-align: center;border: 1px solid #ebebeb;background-color: #fdfdfd;margin-right: 14px;"></i><?php if ($item['name'] != '') {echo '<strong style="font-weight: 500;">' . $item['name'] . '</strong>';}else{ ?>Provedení servisu<?php } ?></td>
					<td class="text-center" style="vertical-align: middle;">1</td>
                    <td class="text-center" style="vertical-align: middle;">-</td>
					<td class="text-center" style="vertical-align: middle;">-</td>
					<td class="text-center" style="vertical-align: middle;">-</td>
                    <td class="text-center" style="vertical-align: middle;"><?= number_format($item['price'], 2, ',', ' ').$currency['sign'] ?></td>
                    <td class="text-center" style="vertical-align: middle;"><?php echo number_format($item['price'], 2, ',', ' ').$currency['sign'] ?></td>
                    <td class="text-center" style="vertical-align: middle;">-</td>
                    <td class="text-right" style="vertical-align: middle;"><strong><?= number_format($item['price'], 2, ',', ' ').$currency['sign'] ?></strong></td>
				</tr>
            <?php } ?>
                <tr>
					<td class="text-center" style="vertical-align: middle;"><?= $i ?></td>
					<td height="63" style="vertical-align: middle;"><strong style="font-weight: 500;"><i class="fas fa-truck-loading" style="width: 40px;height: 46px;line-height: 45px;text-align: center;border: 1px solid #ebebeb;background-color: #fdfdfd;margin-right: 14px;"></i>Doprava</strong></td>
					<td class="text-center" style="vertical-align: middle;">1</td>
                    <td class="text-center" style="vertical-align: middle;">-</td>
					<td class="text-center" style="vertical-align: middle;">-</td>
					<td class="text-center" style="vertical-align: middle;">-</td>
                    <td class="text-center" style="vertical-align: middle;"><?= number_format($service['delivery_price'], 2, ',', ' ').$currency['sign'] ?></td>
                    <td class="text-center" style="vertical-align: middle;"><?= number_format($service['delivery_price'], 2, ',', ' ').$currency['sign'] ?></td>
                    <td class="text-center" style="vertical-align: middle;">-</td>
                    <td class="text-right" style="vertical-align: middle;"><strong><?= number_format($service['delivery_price'], 2, ',', ' ').$currency['sign'] ?></strong></td>
				</tr>


            <?php
            if($has_discount) {

                $i++;

                ?>

                <tr>
                    <td height="63" class="text-center" style="vertical-align: middle;"><?= $i ?></td>
                    <td height="63" style="vertical-align: middle;"><strong style="font-weight: 500;"><i class="fas fa-percent" style="width: 40px;height: 46px;line-height: 45px;text-align: center;border: 1px solid #ebebeb;background-color: #fdfdfd;margin-right: 14px;"></i>Sleva</strong></td>
                    <td class="text-center" style="vertical-align: middle;">1</td>
                    <td class="text-center" style="vertical-align: middle;"><strong class="text-success">-</strong></td>
                    <td class="text-center" style="vertical-align: middle;">-</td>
                    <td class="text-center" style="vertical-align: middle;">-</td>
                    <td class="text-center" style="vertical-align: middle;">-<?= thousand_seperator($total_discount).$currency['sign'] ?></td>
                    <td class="text-center" style="vertical-align: middle;">-<?= thousand_seperator($total_discount).$currency['sign'] ?></td>
                    <td class="text-center" style="vertical-align: middle;">-</td>
                    <td class="text-right" style="vertical-align: middle;"><strong>-<?= thousand_seperator($total_discount).$currency['sign'] ?></strong></td>
                </tr>

                <?php
            }
            ?>
		</tbody>
	</table>

	<div class="margin"></div>

	<div class="row">




			<div class="clear"></div>
				<hr />
		<div class="col-sm-12">
			<div class="invoice-left col-sm-8" style="padding: 0;">


				<?php

    if (isset($service['payment_method']) && $service['payment_method'] == 'cash' || $service['payment_method'] == 'agmobindercardall') {

        $eet = 'yes';

    } else { $eet = 'no';}

    $has_invoice = false;
    $allowRegenerate = false;
    $invoice_query = $mysqli->query("SELECT * FROM orders_invoices WHERE order_id = '$id' AND type = 'service' AND status != 'odd' order by id desc");
    if(mysqli_num_rows($invoice_query) == 0){

        ?>
        <h3 style="margin-bottom: 16px; margin-top: 0;">Vytvořit novou fakturu:</h3>
        <a href="javascript:;" onclick="jQuery('#recapitulate').modal('show');" class="btn btn-success btn-icon icon-left hidden-print">
            Vystavit fakturu
            <i class="entypo-doc-text"></i>
        </a>
        <?php
    } elseif (mysqli_num_rows($invoice_query) > 0) {

        $has_invoice = true;

        while ($invoice = mysqli_fetch_array($invoice_query)) {

            if (isset($invoice['status']) && $invoice['status'] == 'active') {

                $correct_query = $mysqli->query("SELECT * FROM orders_invoices WHERE invoice_id = '" . $invoice['id'] . "' order by id desc LIMIT 1");

                if(date("Y-m", strtotime($invoice['date'])) == date("Y-m") && mysqli_num_rows($correct_query) == 0){

                    $allowRegenerate = true;

                }

                if($invoice['export_id'] != 0){

                    $allowRegenerate = false;

                }

                ?>
		 <div style="background-color: #f3f3f3; padding: 20px 20px 24px 20px; float: left; width: 100%; margin-bottom: 26px;">
<h4 style="margin-bottom: 16px; margin-top: 0;">Faktura <u><?= $invoice['id'] ?></u>:</h4>
	<a href="https://www.wellnesstrade.cz/admin/data/invoices/orders/<?= $invoice['id'] ?>.pdf?t=<?= $currentDate->getTimestamp() ?>" target="_blank" class="btn btn-white btn-icon icon-left hidden-print">
					Zobrazit fakturu
					<i class="entypo-search"></i>
				</a>

				&nbsp;

				<a href="javascript: w=window.open('https://www.wellnesstrade.cz/admin/data/invoices/orders/<?= $invoice['id'] ?>.pdf?t=<?= $currentDate->getTimestamp() ?>'); w.print(); " class="btn btn-primary btn-icon icon-left hidden-print">
					Tisknout fakturu
					<i class="entypo-print"></i>
				</a>

             &nbsp;

             <?php

                 if($allowRegenerate
                     || $client['email'] == 'becher@saunahouse.cz'
                 ){

                     ?>
                     <a href="/admin/controllers/generators/order_invoice_regenerate?invoice_id=<?= $invoice['id'] ?>" class="btn btn-danger btn-icon icon-left">Přegenerovat
                         <i class="entypo-record"></i></a>
                 <?php }else{

                     ?>
                     <a class="btn btn-danger btn-icon icon-left disabled" >Nelze přegenerovat
                         <i class="entypo-record"></i></a>
                     <?php

                 }


                if (mysqli_num_rows($correct_query) > 0) {

                    $correct = mysqli_fetch_array($correct_query);

                    ?>
	<hr>

<h4 style="margin-bottom: 16px; margin-top: 0;">Opravný daňový doklad <u><?= $correct['id'] ?></u> k faktuře <u><?= $correct['invoice_id'] ?></u>:</h4>
	<a href="https://www.wellnesstrade.cz/admin/data/invoices/orders/<?= $correct['id'] ?>.pdf?t=<?= $currentDate->getTimestamp() ?>" target="_blank" class="btn btn-white btn-icon icon-left hidden-print">
					Zobrazit doklad
					<i class="entypo-search"></i>
				</a>

				&nbsp;

				<a href="javascript: w=window.open('https://www.wellnesstrade.cz/admin/data/invoices/orders/<?= $correct['id'] ?>.pdf?t=<?= $currentDate->getTimestamp() ?>'); w.print(); " class="btn btn-primary btn-icon icon-left hidden-print">
					Tisknout doklad
					<i class="entypo-print"></i>
				</a>

			</div>

<?php } else { ?>

	<hr>
<h4 style="margin-bottom: 16px; margin-top: 0;">Opravný daňový doklad:</h4>

	<a href="/admin/controllers/generators/order_invoice?id=<?= $service['id'] ?>&odd=1&eet=<?= $eet ?>&type=service" class="btn btn-red btn-icon icon-left hidden-print">
					Vystavit ODD pro fakturu
					<i class="entypo-cancel-circled"></i>
				</a>

			</div>
<?php
                }

            }

        }
    }

    ?>

</div>

			<div class="invoice-right col-sm-4" style="padding: 0;">

	<a data-id="<?= $service['id'] ?>"  class="toggle-modal-change-state btn btn-blue btn-icon icon-left hidden-print">
					Změnit stav
					<i class="entypo-bookmarks"></i>
				</a>
				&nbsp;

                <?php if(!$has_invoice || ($has_invoice && !$allowRegenerate)){ ?>
                    <a href="./upravit-servis?id=<?= $service['id'] ?>" class="btn btn-default btn-icon icon-left hidden-print">
                        Upravit
                        <i class="entypo-pencil"></i>
                    </a>
                <?php }elseif($allowRegenerate){ ?>
                    <a href="javascript:;" onclick="jQuery('#service_edit_modal').modal('show');" class="btn btn-default btn-icon icon-left hidden-print">
                        Upravit
                        <i class="entypo-pencil"></i>
                    </a>
                <?php } ?>

			</div>

		</div>




	</div>
	<hr>
	<div class="row col-sm-12">

		<h3 style="margin-bottom: 16px; margin-top: 0;">Fotografie & videa:</h3>

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

				<form action="/admin/controllers/uploads/upload-file-service?id=<?= $_REQUEST['id'] ?>" class="dropzone-previews dropzone" id="drop-this" style="min-height: 230px; height: 230px;">
					<div class="fallback">
						<input name="file" type="file" multiple />
					</div>
				</form>

			</div>
	</div>

</div>
    </div>

</div>



    <?php
    $invoice_query = $mysqli->query("SELECT receipt, id FROM orders_invoices WHERE order_id = '$id' AND type = 'service' order by id desc");
    while ($invoice = mysqli_fetch_array($invoice_query)) {

        if(!empty($invoice['receipt'])){ ?>
            <div class="panel-group" id="accordion-test">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion-test" href="#collapse-<?= $invoice['id'] ?>" class="collapsed" aria-expanded="false">
                                EET receipt log
                            </a>
                        </h4>
                    </div>
                    <div id="collapse-<?= $invoice['id'] ?>" class="panel-collapse collapse" aria-expanded="false"> <div class="panel-body">
                            <blockquote style="line-break:anywhere;"><?= $invoice['receipt'] ?></blockquote>
                        </div>
                    </div>
                </div>
            </div>
        <?php }

    }
    ?>

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






<div class="modal fade" id="service_edit_modal" aria-hidden="true" style="display: none; margin-top: 8%;">
  <div class="modal-dialog" style="width: 800px;">
    <div class="modal-content">

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h4 class="modal-title">Upravení servisu #<?= $service['id'] ?></h4>
      </div>

      <div class="modal-body">

      	<div class="well" style="margin-bottom: 0;">
            <p style="font-size: 16px; line-height: 26px; padding: 20px 0 10px; color: #0F0F0F; text-align: center;">Servis má vystavenou fakturu!</p>
            <p style="font-size: 14px; line-height: 26px; padding: 20px 0 10px; color: #0F0F0F; padding: 20px;border-radius: 5px;border: 1px solid #D70505; font-weight: bold;">Všechny provedené změny se při uložení servisu AUTOMATICKY přegenerují do faktury!</p>
        </div>

      </div>

      <div class="modal-footer" style="text-align:left;">
        <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>
        <a href="./upravit-servis?id=<?= $service['id'] ?>" style="float:right;">
          <button type="submit" class="btn btn-default btn-icon icon-left">Upravit servis <i class="entypo-pencil"></i></button>
        </a>
      </div>

    </div>
  </div>
</div>







    <div class="modal fade" id="recapitulate" aria-hidden="true" style="display: none;margin-top: 3%;">

        <div class="modal-dialog" style="padding-top: 4%; width: 800px;">

            <div class="modal-content">
                <div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

                    <h4 class="modal-title">Rekapitulace servisu #<?= $service['id'] ?></h4> </div>

                <div class="modal-body">



                    <div class="form-group">

                        <table class="table table-bordered table-hover">
                            <thead>
                            <tr>
                                <!--                                            <th style="background-color: #f9f9f9 !important; color: #222;" class="text-center">#</th>-->
                                <th style="background-color: #f9f9f9 !important; color: #222;" >Položka</th>
                                <th style="background-color: #f9f9f9 !important; color: #222;" class="text-center">Počet</th>
                                <th style="background-color: #f9f9f9 !important; color: #222;" class="text-center">Cena</th>
                                <th style="background-color: #f9f9f9 !important; color: #222;" class="text-center">Sleva</th>
                                <th style="background-color: #f9f9f9 !important; color: #222;" class="text-right">Cena celkem</th>
                            </tr>
                            </thead>

                            <tbody>
                            <?php

                            $bridge_query = $mysqli->query("SELECT * FROM services_products_bridge WHERE aggregate_id = '$id'");

                            $price_with_dph = 0;
                            $i = 0;

                            $has_discount = false;
                            $total_discount = 0;
                            while ($bridge = mysqli_fetch_array($bridge_query)) {

                                $i++;

                                $products_query = $mysqli->query("SELECT *, id as ajdee FROM products WHERE id = '" . $bridge['product_id'] . "'");

                                if (mysqli_num_rows($products_query) == 1) {

                                    $product = mysqli_fetch_array($products_query);

                                    ?>


                                    <tr>
                                        <td><a href="/admin/pages/accessories/zobrazit-prislusenstvi?id=<?= $product['ajdee'] ?>" target="_blank">
                                                <?php

                                                if ($bridge['variation_id'] != 0) {

                                                    $variation_sku_query = $mysqli->query("SELECT id, sku, main_warehouse FROM products_variations WHERE id = '" . $bridge['variation_id'] . "'");
                                                    $variation_sku = mysqli_fetch_array($variation_sku_query);

                                                    $main_warehouse = $variation_sku['main_warehouse'];

                                                    $path = PRODUCT_IMAGE_PATH.'/thumbnail/' . $product['seourl'] . '_variation_'.$variation_sku['id'].'.jpg';
                                                    $path_product = PRODUCT_IMAGE_PATH.'/thumbnail/' . $product['seourl'] . '.jpg';

                                                    if(file_exists($path)){
                                                        $imagePath = '/data/stores/images/thumbnail/'.$product['seourl'].'_variation_'.$variation_sku['id'].'.jpg';
                                                    }elseif(file_exists($path_product)){
                                                        $imagePath = '/data/stores/images/thumbnail/'.$product['seourl'].'.jpg';
                                                    }else{
                                                        $imagePath = '/data/assets/no-image-7.jpg';
                                                    }

                                                } else {

                                                    $main_warehouse = $product['main_warehouse'];

                                                    $path = PRODUCT_IMAGE_PATH.'/thumbnail/' . $product['seourl'] . '.jpg';
                                                    if(file_exists($path)){
                                                        $imagePath = '/data/stores/images/thumbnail/'.$product['seourl'].'.jpg';
                                                    }else{
                                                        $imagePath = '/data/assets/no-image-7.jpg';
                                                    }

                                                }

                                                $border = 'border: 1px solid #ebebeb';

                                                echo '<img src="'.$imagePath.'" width="30" style="float: left; margin-right: 12px; '.$border.' ">';

                                                if(!empty($bridge['discount'])){

                                                    $has_discount = true;

                                                    $total_discount += round(($bridge['price'] / 100 * ($bridge['discount'])) * $bridge['quantity'], 2, PHP_ROUND_HALF_DOWN);

                                                }

                                                ?>



                                                <strong style="<?php if (isset($bridge['variation_id']) && $bridge['variation_id'] == 0) { ?>padding-top: 9px; float:left;<?php } ?>font-weight: 500;"><?= $product['productname'] ?></strong></a>

                                            <?php if ($bridge['variation_id'] != 0) {

                                                echo '<span style="font-size: 12px; font-weight: 300;">';

                                                $variation_query = $mysqli->query("SELECT name, value FROM products_variations_values WHERE variation_id = '" . $bridge['variation_id'] . "'");

                                                while ($variation = mysqli_fetch_array($variation_query)) {
                                                    echo '<br>';
                                                    echo $variation['name'] . ': ' . $variation['value'];

                                                }

                                                echo '</span>';

                                            }
                                            ?></td>
                                        <td class="text-center" style="vertical-align: middle;"><?= $bridge['quantity'] ?></td>

                                        <td class="text-center" style="vertical-align: middle;"><?= number_format($bridge['original_price'], 0, ',', ' ').$currency['sign'] ?></td>

                                        <td class="text-center" style="vertical-align: middle;"><?php

                                            if(!empty($bridge['discount'])) {

                                                echo $bridge['discount']; ?> % = <?php

                                                echo round($bridge['discount_net'] * $bridge['quantity'], 2).$currency['sign'];

                                            }else{ echo '-'; }
                                            ?></td>
                                        <td class="text-right" style="vertical-align: middle;"><strong><?= number_format($bridge['price'] * $bridge['quantity'], 2, ',', ' ').$currency['sign'] ?></strong></td>
                                    </tr>
                                    <?php
                                } else { ?>


                                    <tr>
                                        <td><strong>Neznámý produkt</strong> <?= $bridge['product_name'] ?> - <small><?= $bridge['variation_values'] ?></small></td>
                                        <td class="text-center" style="vertical-align: middle;"><?= $bridge['quantity'] ?></td>
                                        <td class="text-center" style="vertical-align: middle;"><strong class="text-success"><?= $bridge['reserved'] ?></strong></td>
                                        <td class="text-center" style="vertical-align: middle;"><?php

                                            if (($bridge['quantity'] - $bridge['reserved']) > 0) { ?>

                                                <strong class="text-danger">-<?= $bridge['quantity'] - $bridge['reserved'] ?></strong>

                                            <?php } else { ?>0<?php } ?></td>
                                        <td class="text-center" style="vertical-align: middle;"><?= number_format($bridge['original_price'], 0, ',', ' ').$currency['sign'] ?></td>


                                        <td class="text-center" style="vertical-align: middle;"><?= number_format($bridge['price'], 2, ',', ' ').$currency['sign'] ?></td>
                                        <td class="text-center" style="vertical-align: middle;"><?= $service['discount'] ?> %</td>


                                        <td class="text-right" style="vertical-align: middle;"><strong><?= number_format($bridge['price'] * $bridge['quantity'], 2, ',', ' ').$currency['sign'] ?></strong></td>
                                    </tr>



                                    <?php

                                }

                                $price_with_dph = $price_with_dph + ($bridge['price'] * $bridge['quantity']);
                            }


                            $i++;


                            $items_query = $mysqli->query("SELECT * FROM services_items WHERE service_id = '".$id."'")or die($mysqli->error);
                            while($item = mysqli_fetch_assoc($items_query)){
                            ?>
                            <tr>
                                <td height="63" style="vertical-align: middle;"><i class="fas fa-wrench" style="width: 40px;height: 46px;line-height: 45px;text-align: center;border: 1px solid #ebebeb;background-color: #fdfdfd;margin-right: 14px;"></i><?php if ($item['name'] != '') { echo '<strong style="font-weight: 500;">' . $item['name'] . '</strong>';}else{ ?>Provedení servisu<?php } ?></td>
                                <td class="text-center" style="vertical-align: middle;">1</td>

                                <td class="text-center" style="vertical-align: middle;"><?= number_format($item['price'], 2, ',', ' ').$currency['sign'] ?></td>
                                <td class="text-center" style="vertical-align: middle;">-</td>

                                <td class="text-right" style="vertical-align: middle;"><strong><?= number_format($item['price'], 2, ',', ' ').$currency['sign'] ?></strong></td>
                            </tr>
                            <?php } ?>


                            <tr>
                                <td height="63" style="vertical-align: middle;"><strong style="font-weight: 500;"><i class="fas fa-truck-loading" style="width: 40px;height: 46px;line-height: 45px;text-align: center;border: 1px solid #ebebeb;background-color: #fdfdfd;margin-right: 14px;"></i>Doprava</strong></td>
                                <td class="text-center" style="vertical-align: middle;">1</td>
                                <td class="text-center" style="vertical-align: middle;"><?= number_format($service['delivery_price'], 2, ',', ' ').$currency['sign'] ?></td>
                                <td class="text-center" style="vertical-align: middle;">-</td>

                                <td class="text-right" style="vertical-align: middle;">
                                    <strong><?= number_format($service['delivery_price'], 2, ',', ' ').$currency['sign'] ?></strong>
                                </td>
                            </tr>



                            <?php

                            if($has_discount) {

                                $i++;

                                ?>

                                <tr>
                                    <td height="20" style="vertical-align: middle;"><strong style="font-weight: 500;"><i class="fas fa-percent" style="width: 30px;height: 34px;line-height: 33px;text-align: center;border: 1px solid #ebebeb;background-color: #fdfdfd;margin-right: 14px;"></i>Sleva</strong></td>
                                    <td class="text-center" style="vertical-align: middle;">1</td>
                                    <td class="text-center" style="vertical-align: middle;">-<?= thousand_seperator($total_discount).$currency['sign'] ?></td>
                                    <td class="text-center" style="vertical-align: middle;">-</td>
                                    <td class="text-right" style="vertical-align: middle;"><strong>-<?= thousand_seperator($total_discount).$currency['sign'] ?></strong></td>
                                </tr>

                                <?php
                            }



                            ?>


                            </tbody>
                        </table>

                        <table class="table table-stripped table-hover" style="width: 50%; float: right; text-align: left; font-size: 13px;margin-bottom: -40px;">
                            <tbody>

                            <tr>
                                <td style="padding: 12px 8px;">Způsob úhrady:</td>
                                <td style="padding: 12px 8px;"><strong><?= $payment['name'] ?></strong></td>
                            </tr>
                            <tr>
                                <td style="padding: 12px 8px;"><strong>Cena celkem:</strong></td>
                                <td style="padding: 12px 8px;">
                                    <span style="font-size: 18px; margin-bottom: 0; font-family: inherit;  font-weight: normal; "><strong style=" font-weight: 600; "><?= thousand_seperator($service['price']) ?></strong> <?= $currency['sign'] ?></span></td>
                            </tr>
                            </tbody>
                        </table>

                        <div style="clear: both;"></div>



                    </div>

                </div>
                <div class="modal-footer" style="text-align:left;"> <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>

                    <a href="/admin/controllers/generators/order_invoice?id=<?= $service['id'] ?>&eet=<?= $eet ?>&type=service" class="btn btn-success btn-icon icon-left" style="float: right;">Vystavit fakturu
                        <i class="entypo-doc-text"></i></a>

                </div>
            </div>
        </div>
    </div>





<script type="text/javascript">





$(document).ready(function(){


	$('.remove-picture').click(function() {

	    $(this).parent(".single-picture").fadeOut();

	    var id = $(this).data("id");
	    var picture = $(this).data("picture");

	    $.get("./zobrazit-servis?id=<?= $id ?>&action=remove_picture&picture="+picture);

	  });


    $(".toggle-modal-change-state").click(function(e){

      $('#change-state-modal').removeData('bs.modal');
       e.preventDefault();


       var id = $(this).data("id");

        $("#change-state-modal").modal({

            remote: '/admin/controllers/modals/modal-change-services.php?id='+id,
        });
    });





});




</script>


<div class="modal fade" id="change-state-modal" aria-hidden="true" style="display: none; margin-top: 3%;">

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

    <script type="text/javascript">
    $(document).ready(function() {

        $('.lightgallery').lightGallery({
            selector: 'a.full'
        });


        Dropzone.autoDiscover = false;

        var myDropzone = new Dropzone('form#drop-this',{

            acceptedFiles: "image/*,.mp4,.mkv,.avi"

        });


        myDropzone.on("complete", function (file) {

            $("#service_pictures").load(location.href + " #service_pictures");
            $("#service_videos").load(location.href + " #service_videos");

        });


    });
    </script>

<?php include VIEW . '/default/footer.php'; ?>


<?php

} else {

    include INCLUDES . "/404.php";

}?>


