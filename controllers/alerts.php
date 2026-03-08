<?php



if(isset($_REQUEST['success']) && $_REQUEST['success'] == "add"){

	$display_alerts = true;

		if($page_name == 'editace-prislusenstvi.php'){
		$displaysuccess = true;
		$successhlaska = "Příslušenství bylo v pořádku přidáno.";
		}

		if($page_name == 'nezpracovane-objednavky.php'){

			$alert_success = true;
			$text_success = "Objednávka byla v úspěšně přidána.";

		}


}


if(isset($_REQUEST['success']) && $_REQUEST['success'] == "edit"){

	$display_alerts = true;

		if($page_name == 'editace-prislusenstvi.php'){

			$alert_success = true;
			$text_success = "Příslušenství bylo v pořádku upraveno.";

		}

		if($page_name == 'nezpracovane-objednavky.php' || $page_name == 'prijate-objednavky.php' || $page_name == 'pripravene-objednavky.php' || $page_name == 'vyexpedovane-objednavky.php' || $page_name == 'stornovane-objednavky.php'){

			$alert_success = true;
			$text_success = "Objednávka byla v úspěšně upravena.";

		}


		if($page_name == 'zobrazit-objednavku.php'){

			$alert_success = true;
			$text_success = "Objednávka byla úspěšně upravena.";
//
//			$alert_info = true;
//			$text_info = "Pro objednávku je nyní <strong>rezervováno</strong> celkem <strong>".$_REQUEST['reserved']." položek</strong>.";

//			$alert_warning = true;
//			$text_warning = "K objednávce momentálně <strong>chybí</strong> celkem <strong>".$_REQUEST['missing']." položek</strong>.";

			if(isset($_REQUEST['has_mail']) && $_REQUEST['has_mail'] == 'true'){

				$alert_info2 = true;
				$text_info2 = "Zákazníkovi <strong>byl odeslán informační email</strong> ohledně změny stavu objednávky.";

			}else{

				$alert_default = true;
				$text_default = "Zákazníkovi <strong>nebyl odeslán informační email</strong> ohledně změny stavu objednávky.";

			}

			

		}






	
}


if(isset($_REQUEST['success']) && $_REQUEST['success'] == "remove"){

	$display_alerts = true;

		if($page_name == 'editace-prislusenstvi.php'){

			$alert_success = true;
			$text_success = "Příslušenství bylo smazáno včetně zápisů v kategoriích.";

			// $alert_info = true;
			// $text_info = "Příslušenství bylo smazáno včetně zápisů v kategoriích.";

			// $alert_warning = true;
			// $text_warning = "Příslušenství bylo smazáno včetně zápisů v kategoriích.";

			// $alert_danger = true;
			// $text_danger = "Příslušenství bylo smazáno včetně zápisů v kategoriích.";

			// $alert_default = true;
			// $text_default = "Příslušenství bylo smazáno včetně zápisů v kategoriích.";

			// $alert_minimal = true;
			// $text_minimal = "Příslušenství bylo smazáno včetně zápisů v kategoriích.";


		}


		if($page_name == 'nezpracovane-objednavky.php' || $page_name == 'prijate-objednavky.php' || $page_name == 'pripravene-objednavky.php' || $page_name == 'vyexpedovane-objednavky.php' || $page_name == 'stornovane-objednavky.php'){

			$alert_success = true;
			$text_success = "Objednávka byla v úspěšně smazána.";

		}


		if($page_name == 'naplanovane-servisy.php'){

			$alert_success = true;
			$text_success = "Servis byl úspěšně smazán.";

		}

	
}


if(isset($_REQUEST['success']) && $_REQUEST['success'] == "change_status"){

	$display_alerts = true;

		if($page_name == 'zobrazit-objednavku.php'){

			$alert_success = true;
			$text_success = "Stav objednávky byl úspěšně změněn.";

			if(isset($_REQUEST['has_mail']) && $_REQUEST['has_mail'] == 'true'){

				$alert_info = true;
				$text_info = "Zákazníkovi <strong>byl odeslán informační email</strong> ohledně změny stavu objednávky.";

			}else{

				$alert_default = true;
				$text_default = "Zákazníkovi <strong>nebyl odeslán informační email</strong> ohledně změny stavu objednávky.";

			}

			// $alert_warning = true;
			// $text_warning = "Příslušenství bylo smazáno včetně zápisů v kategoriích.";

			// $alert_danger = true;
			// $text_danger = "Příslušenství bylo smazáno včetně zápisů v kategoriích.";

			// $alert_default = true;
			// $text_default = "Příslušenství bylo smazáno včetně zápisů v kategoriích.";

			// $alert_minimal = true;
			// $text_minimal = "Příslušenství bylo smazáno včetně zápisů v kategoriích.";

		}


		if($page_name == 'nezpracovane-objednavky.php' || $page_name == 'prijate-objednavky.php' || $page_name == 'pripravene-objednavky.php' || $page_name == 'vyexpedovane-objednavky.php' || $page_name == 'stornovane-objednavky.php'){

			$alert_success = true;
			$text_success = "Stav objednávky byl úspěšně změněn.";

			if(isset($_REQUEST['has_mail']) && $_REQUEST['has_mail'] == 'true'){

				$alert_info = true;
				$text_info = "Zákazníkovi <strong>byl odeslán informační email</strong> ohledně změny stavu objednávky.";

			}else{

				$alert_default = true;
				$text_default = "Zákazníkovi <strong>nebyl odeslán informační email</strong> ohledně změny stavu objednávky.";

			}

		}


		if($page_name == 'zobrazit-klienta.php'){

			$alert_success = true;
			$text_success = "Klient byl úspěšně schválen.";

			if(isset($_REQUEST['has_mail']) && $_REQUEST['has_mail'] == 'true'){

				$alert_info = true;
				$text_info = "Klientovi <strong>byl odeslán informační email</strong> s přihlašovacími údaji.";

			}


		}

		if($page_name == 'naplanovane-servisy.php'){

			if(isset($_REQUEST['approved']) && $_REQUEST['approved'] == 'true'){

				$alert_info = true;
				$text_info = "Naplánovaný servis byl úspěšně potvrzen.";


			}elseif(isset($_REQUEST['disapproved']) && $_REQUEST['disapproved'] == 'true'){

				$alert_default = true;
				$text_default = "Potvrzení u servisu bylo úspěšně zrušeno.";

			}



		}


	
}



if(isset($_REQUEST['success']) && $_REQUEST['success'] == "to_stock"){

	$display_alerts = true;

		if($page_name == 'zobrazit-prislusenstvi.php'){

			$alert_success = true;
			$text_success = "Zboží bylo úspěšně naskladněno.";

			if($_REQUEST['reserved_quantity'] != 0){
				$alert_info = true;
				$text_info = "K <strong>objednávkám s chybějícím zbožím</strong> bylo rezerováno <strong>".$_REQUEST['reserved_quantity']." položek</strong>.";
			}				

			if($_REQUEST['to_stock_quantity'] != 0){
				$alert_warning = true;
				$text_warning = "Do <strong>skladové zásoby produktu</strong> bylo přidáno <strong>".$_REQUEST['to_stock_quantity']." položek</strong>.";
			}
			

		}


		if($page_name == 'editace-prislusenstvi.php'){

			$alert_success = true;
			$text_success = "Zboží bylo úspěšně naskladněno.";

			if($_REQUEST['reserved_quantity'] != 0){
				$alert_info = true;
				$text_info = "K <strong>objednávkám s chybějícím zbožím</strong> bylo rezerováno <strong>".$_REQUEST['reserved_quantity']." položek</strong>.";
			}

			if($_REQUEST['to_stock_quantity'] != 0){
				$alert_warning = true;
				$text_warning = "Do <strong>skladové zásoby produktu</strong> bylo přidáno <strong>".$_REQUEST['to_stock_quantity']." položek</strong>.";
			}
			

		}		

	
}







if(isset($_REQUEST['success']) && $_REQUEST['success'] == "send_preparation"){
    $display_alerts = true;
    $alert_success = true;
    $text_success = "Stavební příprava byla úspěšně zaslána.";
}






// Events START //

if(isset($_REQUEST['success']) && $_REQUEST['success']=="event_add"){
    $display_alerts = true;
    $alert_success = true;
    $text_success = "Událost byla úspěšně vytvořena.";
}

// Events END //

// Realizations START //

if(isset($_REQUEST['success']) && $_REQUEST['success']=="realization_add"){
    $display_alerts = true;
    $alert_success = true;
    $text_success = "Realizace byla úspěšně vytvořena.";
}

// Realizations END //

// Service START //

if(isset($_REQUEST['success']) && $_REQUEST['success']=="service_add"){
    $display_alerts = true;
    $alert_success = true;
    $text_success = "Servis byl úspěšně naplánován.";
}

// Service END //

// Tasks START //

if(isset($_REQUEST['success']) && $_REQUEST['success']=="task_add"){
    $display_alerts = true;
    $alert_success = true;
    $text_success = "Úkol byl úspěšně přidán.";
}

if(isset($_REQUEST['success']) && $_REQUEST['success']=="task_edit"){
    $display_alerts = true;
    $alert_success = true;
    $text_success = "Úkol byl úspěšně upraven.";
}

if(isset($_REQUEST['success']) && $_REQUEST['success']=="task_change"){
    $display_alerts = true;
    $alert_success = true;
    $text_success = "Status úkolu byl úspěšně upraven.";
}

if(isset($_REQUEST['success']) && $_REQUEST['success']=="task_remove"){
    $display_alerts = true;
    $alert_success = true;
    $text_success = "Úkol byl úspěšně smazán.";
}

// Tasks END //

// Comments START //

if(isset($_REQUEST['success']) && $_REQUEST['success']=="comment_add"){
    $display_alerts = true;
    $alert_success = true;
    $text_success = "Komentář k úkolu byl úspěšně přidán.";
}

if(isset($_REQUEST['success']) && $_REQUEST['success']=="comment_remove"){
    $display_alerts = true;
    $alert_success = true;
    $text_success = "Komentář u úkolu byl úspěšně smazán.";
}

// Comments END //

if(isset($display_alerts) && $display_alerts){
    ?>



			<div class="row">
			  	<div class="col-sm-12">
					
					<?php if(isset($alert_success) && $alert_success){ ?><div class="alert alert-success"><strong>Výborně!</strong> <?= $text_success ?></div><?php } ?>

					<?php if(isset($alert_info) && $alert_info){ ?><div class="alert alert-info"><?= $text_info ?></div><?php } ?>

					<?php if(isset($alert_warning) && $alert_warning){ ?><div class="alert alert-warning"><?= $text_warning ?></div><?php } ?>

					<?php if(isset($alert_danger) && $alert_danger){ ?><div class="alert alert-danger"><strong>Jejda!</strong> <?= $text_danger ?></div><?php } ?>

					<?php if(isset($alert_default) && $alert_default){ ?><div class="alert alert-default"><?= $text_default ?></div><?php } ?>

					<?php if(isset($alert_minimal) && $alert_minimal){ ?><div class="alert alert-minimal"><strong>No teda.</strong> <?= $text_minimal ?></div><?php } ?>

					<?php if(isset($alert_info2) && $alert_info2){ ?><div class="alert alert-info"><?= $text_info2 ?></div><?php } ?>


				</div>
			</div>



<?php }


?>