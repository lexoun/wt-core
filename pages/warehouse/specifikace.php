<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$categorytitle = "Klienti";
$pagetitle = "Specifikace";

if (isset($_REQUEST['customer'])) {$customer = $_REQUEST['customer'];}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_choice') {

    $insert_query = $mysqli->query("INSERT INTO specs_params (spec_id, option, option_en) VALUES ('" . $_REQUEST['id'] . "','" . $_POST['choice'] . "','" . $_POST['option_en'] . "')") or die($mysqli->error);

    Header("Location:https://www.wellnesstrade.cz/admin/pages/warehouse/specifikace?success=add_choice");
    exit;

}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_specification') {

    if ($_POST['name'] != "") {

        $seoslug = odkazy($_POST['name']);

        $insert_query = $mysqli->query("INSERT INTO specs (name, name_en, seoslug, type, product, brand, supplier) VALUES ('" . $_POST['name'] . "','" . $_POST['name_en'] . "','$seoslug','" . $_POST['type'] . "','1','IQue','" . $_POST['supplier'] . "')") or die($mysqli->error);

    }

    Header("Location:https://www.wellnesstrade.cz/admin/pages/warehouse/specifikace?success=add_specification");
    exit;

}

include VIEW . '/default/header.php';

?>

<div class="row">
	<div class="col-md-9 col-sm-8">
		<h2><?= $pagetitle ?></h2>
	</div>
    <div class="col-md-2 col-sm-3">
        <a ref="javascript:;" onclick="jQuery('#add_specification').modal('show');" style=" margin-right: 14px;" class="btn btn-default btn-icon icon-left btn-lg">
            <i class="entypo-plus"></i>
            Přidat specifikaci
        </a>
	</div>
</div>

<div id="table-2_wrapper" style="margin-top: 30px;" class="dataTables_wrapper form-inline" role="grid"><table class="table table-bordered table-striped datatable dataTable" id="table-2" aria-describedby="table-2_info">
	<thead>
		<tr role="row"><th style="width: 140px;">Název</th>
			<th  style="width: 120px;">SEO adresa</th>
			<th style="width: 100px; text-align: center;">Typ zobrazení</th>

			<th  style="width: 80px; text-align: center;">Značky</th>

			<th style="width: 500px;">Parametry (<span class="text-success">aktivní</span> x <span class="text-danger">neaktivní</span>)</th>

			<th style="width: 340px; text-align: center;" >Akce</th></tr>
	</thead>


<tbody role="alert" aria-live="polite" aria-relevant="all">
<?php

$specsselect = $mysqli->query('SELECT * FROM specs ORDER BY name') or die($mysqli->error);
while ($spec = mysqli_fetch_array($specsselect)) {

    ?>
<tr class="even">
			<td class=" "><strong><?= $spec['name'] ?></strong><br><small style="color: #ed4749;"><?= $spec['name_en'] ?></small></td>
			<td class=" "><?= $spec['seoslug'] ?></td>
			<td class="" style="padding-left: 12px;"><?php if (isset($spec['type']) && $spec['type'] == 0) {echo 'radio';} elseif (isset($spec['type']) && $spec['type'] == 1) {echo 'dropdown';} else {echo 'input';}?></td>
			<td class="text-center"><?php
                if(!empty($spec['brand'])) {
                    $comma = '';
                    foreach (json_decode($spec['brand']) as $brand) {
                        echo $comma . $brand;
                        $comma = ', ';
                    }
                }
                ?></td>
			<td class=" " <?php if ($spec['type'] != 1) {echo 'style="padding-left: 16px; font-style: italic;"';}?>><?php if (isset($spec['type']) && $spec['type'] == 0) {echo 'Ano/ne';} elseif (isset($spec['type']) && $spec['type'] == 2) {echo 'Textová hodnota';} else {echo '<p style="line-height: 22px; margin-bottom: 0;">';

            $inactive = false;
            $optionsselect = $mysqli->query("SELECT * FROM specs_params WHERE spec_id = '" . $spec['id'] . "' ORDER BY active DESC") or die($mysqli->error);
			    while ($options = mysqli_fetch_assoc($optionsselect)) {

			        if($options['active'] == 1){ ?>
                        <span class="text-success">
                            <?= $options['option'] ?> &nbsp;&#8226;&nbsp;
                        </span>
                    <?php }else{
			            if(!$inactive){ echo '<hr>'; $inactive = true; }
			            ?>
                        <span class="text-danger">
                            <?= $options['option'] ?> &nbsp;&#8226;&nbsp;
                        </span>
                    <?php }

			    }
        echo '</p>';}?></td>
			<td class="text-center" style="text-align: right;">
                <?php if ($spec['type'] != 0) { ?>
                    <a href="javascript:;" onclick="jQuery('#add_choice_<?= $spec['id'] ?>').modal('show');"  class="btn btn-green btn-sm btn-icon icon-left">
                        <i class="entypo-plus"></i>
                        Přidat možnost
                    </a>
                <?php } ?>
                <a href="/admin/pages/warehouse/specs-edit?id=<?= $spec['id'] ?>" class="btn btn-default btn-sm btn-icon icon-left">
                    <i class="entypo-pencil"></i>
                    Upravit
                </a>
                <a href="/admin/pages/warehouse/specifikace-propojene-prislusenstvi?id=<?= $spec['id'] ?>" class="btn btn-primary btn-sm btn-icon icon-left">
                  <i class="entypo-plus"></i>
                  Příslušenství
                </a>
			</td>
		</tr>
<?php } ?>
</tbody></table></div>


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


	<?php

$specs_choice_query = $mysqli->query('SELECT * FROM specs WHERE type = 1') or die($mysqli->error);
while ($specs_choice = mysqli_fetch_array($specs_choice_query)) {

    ?>


  <div class="modal fade" id="add_choice_<?= $specs_choice['id'] ?>" aria-hidden="true" style="display: none;  top: 8%;">
        <div class="modal-dialog">
          <div class="modal-content">
          <form role="form" role="form" method="post"  class="form-horizontal form-groups-bordered validate" action="specifikace?action=add_choice&id=<?= $specs_choice['id'] ?>" enctype="multipart/form-data">

            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 class="modal-title">Nová možnost k specifikaci <strong>#<?= $specs_choice['id'] ?></strong></h4>
            </div>

            <div class="modal-body">

             <p>Přidat novou možnost volby pro specifikaci <strong><?= $specs_choice['name'] ?></strong></p> <br>


           <div class="form-group">
            <label for="field-2" class="col-sm-4 control-label"><strong>Hodnota specifikace</strong> (standardní znaky)</label>

            <div class="col-sm-5">
              <div class="date">
              <input type="text" class="form-control" name="choice">

            </div></div>
          </div>



           <div class="form-group">
            <label for="field-2" class="col-sm-4 control-label"><strong>Hodnota anglicky</strong> (standardní znaky)</label>

            <div class="col-sm-5">
              <div class="date">
              <input type="text" class="form-control" name="option_en">

            </div></div>
          </div>

            </div>


       <div class="modal-footer" style="text-align:left;">
            <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>
              <button type="submit" class="btn btn-green btn-icon icon-left" style="float: right;">Přidat <i class="entypo-pencil"></i></button>
          </div>

      </form>

          </div>
        </div>
      </div>

<?php } ?>



  <div class="modal fade" id="add_specification" aria-hidden="true" style="display: none;  top: 8%;">
        <div class="modal-dialog">
          <div class="modal-content">
          <form role="form" role="form" method="post"  class="form-horizontal form-groups-bordered validate" action="specifikace?action=add_specification" enctype="multipart/form-data">

            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 class="modal-title">Přidání nové specifikace</h4>
            </div>

            <div class="modal-body">

             <p>Nová specifikace k vířivkám IQue (k ničemu jinému zatím přidávat nejde). Volby následně přidáte manuálně u specifikace.</p> <br>


           <div class="form-group">
            <label for="field-2" class="col-sm-4 control-label"><strong>Název specifikace</strong></label>

            <div class="col-sm-5">
              <div class="date">
              <input type="text" class="form-control" name="name">

            </div></div>
           </div>

           <div class="form-group">
            <label for="field-2" class="col-sm-4 control-label"><strong>English name</strong></label>

            <div class="col-sm-5">
              <div class="date">
              <input type="text" class="form-control" name="name_en">

            </div></div>
          </div>


           <div class="form-group">
            <label for="field-2" class="col-sm-4 control-label"><strong>Typ specifikace</strong></label>

           <div class="col-sm-8">

              <div class="radio" style="float: left;">
                <label>
                  <input type="radio" name="type" value="0" checked>Ano/Ne
                </label>
              </div>
              <div class="radio" style="float: left; margin-left: 20px;">
                <label>
                  <input type="radio" name="type" value="1">Dropdown možnosti
                </label>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="field-2" class="col-sm-4 control-label"><strong>Pro dodavatele</strong></label>

           <div class="col-sm-8">

              <div class="radio" style="float: left;">
                <label>
                  <input type="radio" name="supplier" value="1" checked>Ano
                </label>
              </div>
              <div class="radio" style="float: left; margin-left: 20px;">
                <label>
                  <input type="radio" name="supplier" value="0">Ne
                </label>
              </div>
            </div>
          </div>

            </div>


       <div class="modal-footer" style="text-align:left;">
            <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>
              <button type="submit" class="btn btn-green btn-icon icon-left" style="float: right;">Přidat <i class="entypo-pencil"></i></button>
          </div>

      </form>

          </div>
        </div>
      </div>


<script type="text/javascript">
$(document).ready(function(){
    $(".toggle-modal-products").click(function(e){

      $('#products-modal').removeData('bs.modal');
       e.preventDefault();


       var id = $(this).data("id");

        $("#products-modal").modal({

            remote: '/admin/controllers/modals/modal-specs-products.php?id='+id,
        });
    });
});
</script>


<div class="modal fade" id="products-modal" aria-hidden="true" style="display: none; margin-top: 8%;">


</div>

<?php include VIEW . '/default/footer.php'; ?>

