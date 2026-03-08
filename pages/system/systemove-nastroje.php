<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$pagetitle = "Systémové nástroje";




if(!empty($_REQUEST['action']) && $_REQUEST['action'] == 'vop'){

    $path = $_SERVER['DOCUMENT_ROOT'] . '/admin/data/demands/documents/Všeobecné obchodní podmínky společnosti Wellness Trade_v.pdf';
    move_uploaded_file($_FILES['file']['tmp_name'], $path);

    header('location: https://www.wellnesstrade.cz/admin/pages/system/systemove-nastroje');
    exit;
}

if(!empty($_REQUEST['action']) && $_REQUEST['action'] == 'vop_swim'){

    $path = $_SERVER['DOCUMENT_ROOT'] . '/admin/data/demands/documents/Všeobecné obchodní podmínky společnosti Wellness Trade_swim.pdf';
    move_uploaded_file($_FILES['file']['tmp_name'], $path);

    header('location: https://www.wellnesstrade.cz/admin/pages/system/systemove-nastroje');
    exit;
}




include VIEW . '/default/header.php';


?>



<div class="row">


	<div class="col-sm-12">

	<div class="col-md-12 col-sm-12" style="margin-bottom: 20px;">
		<h2>E-shopy</h2>
	</div>




	<div class="col-sm-3">
		<a href="/admin/controllers/stores/batch_simple_products?site=spahouse" style="cursor: pointer;" target="_blank">
			<div class="tile-stats tile-gray">
				<div class="icon"><i class=""></i></div>
				<div class="num"></div>
				<h3>SPAHOUSE SIMPLE</h3>

				<p>nově vyexportuje všechno zboží pro všechny eshopy</p>
			</div>
		</a>
	</div>

	<div class="col-sm-3">
		<a href="/admin/controllers/stores/batch_simple_products?site=saunahouse" style="cursor: pointer;" target="_blank">
			<div class="tile-stats tile-gray">
				<div class="icon"><i class=""></i></div>
				<div class="num"></div>
				<h3>SAUNAHOUSE SIMPLE</h3>

				<p>nově vyexportuje všechno zboží pro všechny eshopy</p>
			</div>
		</a>
	</div>

	<div class="col-sm-3">
		<a href="/admin/controllers/stores/batch_simple_products?site=spamall" style="cursor: pointer;" target="_blank">
			<div class="tile-stats tile-gray">
				<div class="icon"><i class=""></i></div>
				<div class="num"></div>
				<h3>SPAMALL SIMPLE</h3>

				<p>nově vyexportuje všechno zboží pro všechny eshopy</p>
			</div>
		</a>
	</div>

	<div style="clear: both;"></div>
	<hr>

<div class="col-sm-3">
		<a href="/admin/controllers/stores/batch_variable_products?site=spahouse" style="cursor: pointer;" target="_blank">
			<div class="tile-stats tile-gray">
				<div class="icon"><i class=""></i></div>
				<div class="num"></div>
				<h3>SPAHOUSE VARI</h3>

				<p>nově vyexportuje všechno zboží pro všechny eshopy</p>
			</div>
		</a>
	</div>

	<div class="col-sm-3">
		<a href="/admin/controllers/stores/batch_variable_products?site=saunahouse" style="cursor: pointer;" target="_blank">
			<div class="tile-stats tile-gray">
				<div class="icon"><i class=""></i></div>
				<div class="num"></div>
				<h3>SAUNAHOUSE VARI</h3>

				<p>nově vyexportuje všechno zboží pro všechny eshopy</p>
			</div>
		</a>
	</div>

	<div class="col-sm-3">
		<a href="/admin/controllers/stores/batch_variable_products?site=spamall" style="cursor: pointer;" target="_blank">
			<div class="tile-stats tile-gray">
				<div class="icon"><i class=""></i></div>
				<div class="num"></div>
				<h3>SPAMALL VARI</h3>

				<p>nově vyexportuje všechno zboží pro všechny eshopy</p>
			</div>
		</a>
	</div>


    </div>
</div>
<hr>
<div class="row">

    <div class="col-sm-12">
        <div class="col-md-12 col-sm-12" style="margin-bottom: 20px;">
            <h2>VOP - vířivky</h2>
        </div>

        <a href="<?= $home ?>/admin/data/demands/documents/Všeobecné obchodní podmínky společnosti Wellness Trade_v.pdf?t=<?= $currentDate->getTimestamp() ?>"
           target="_blank"
           style="margin-bottom: 12px; margin-right: 4px; font-size: 13px; padding: 12px 16px 12px 46px; float: left;"
           class="btn btn-primary btn-icon icon-left btn-lg">
            <i class="entypo-down"
               style="line-height: 24px;font-size: 14px; padding: 10px 8px;"></i>
            Obchodní podmínky
        </a>
        <form  role="form" method="post"
              action="systemove-nastroje?action=vop"
              enctype="multipart/form-data">

            <input type="file" style="width: 260px; padding-top: 6px; display: inline-block;" class="form-control" name="file"
                   id="field-file" placeholder="Placeholder">
            <button type="submit" class="btn btn-green btn-icon icon-left"> <i class="entypo-plus"></i> Nahrát soubor </button>
        </form>
    </div>
</div>

<hr>

<div class="row" style="margin-top: 20px;">


    <div class="col-sm-12">
        <div class="col-md-12 col-sm-12" style="margin-bottom: 20px;">
            <h2>VOP - swim</h2>
        </div>

        <a href="<?= $home ?>/admin/data/demands/documents/Všeobecné obchodní podmínky společnosti Wellness Trade_swim.pdf?t=<?= $currentDate->getTimestamp() ?>"
           target="_blank"
           style="margin-bottom: 12px; margin-right: 4px; font-size: 13px; padding: 12px 16px 12px 46px; float: left;"
           class="btn btn-primary btn-icon icon-left btn-lg">
            <i class="entypo-down"
               style="line-height: 24px;font-size: 14px; padding: 10px 8px;"></i>
            Obchodní podmínky
        </a>
        <form  role="form" method="post"
               action="systemove-nastroje?action=vop_swim"
               enctype="multipart/form-data">

            <input type="file" style="width: 260px; padding-top: 6px; display: inline-block;" class="form-control" name="file"
                   id="field-file" placeholder="Placeholder">
            <button type="submit" class="btn btn-green btn-icon icon-left"> <i class="entypo-plus"></i> Nahrát soubor </button>
        </form>
    </div>
		</div>

	</div>





</div>
</div>




<?php include VIEW . '/default/footer.php'; ?>


