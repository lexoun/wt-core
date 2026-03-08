<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

if (isset($_REQUEST['od'])) {$od = $_REQUEST['od'];}
if (isset($_REQUEST['q'])) {$search = $_REQUEST['q'];}

$pagetitle = "Nastavení přístupů";




if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'set_accesses') {

    $mysqli->query("DELETE FROM administration_accesses") or die($mysqli->error);

    $valuesArray = $_POST['admin'];

    foreach($valuesArray as $main_key => $value){

        foreach($value as $key => $val){

            $mysqli->query("INSERT INTO administration_accesses (admin_id, site_id, value) VALUES ('" . $main_key . "', '" . $key . "', '$val')") or die($mysqli->error);

        }

    }

    Header("Location:https://www.wellnesstrade.cz/admin/pages/system/nastaveni-pristupu?succes=edit");
    exit;

}


// todo solo techniky

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'enable_disable_admin') {

    if (isset($_REQUEST['type']) && $_REQUEST['type'] == 'disable') {

        $update_query = $mysqli->query("UPDATE demands SET active = '0' WHERE id = '" . $_REQUEST['id'] . "'");

    } elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == 'enable') {

        $update_query = $mysqli->query("UPDATE demands SET active = '1' WHERE id = '" . $_REQUEST['id'] . "'");

    }

    Header("Location:https://www.wellnesstrade.cz/admin/pages/system/nastaveni-pristupu?succes=edit");
    exit;

}

// todo solo techniky


if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_admin') {

    $salt = "oijahsfdapsf80efdjnsdjp";

    $salt .= $_POST['password'];
    $password = md5($salt);

    $user_name = $_POST['name'] . ' ' . $_POST['surname'];

    $mysqli->query("INSERT INTO demands (user_name, email, password, role, customer, product, avatar, active, secretstring) VALUES ('" . $user_name . "', '" . $_POST['email'] . "', '$password', '" . $_POST['role'] . "', '1', 'eden', 'default', '1','".$_POST['password']."')") or die($mysqli->error);
    $id = $mysqli->insert_id;

    $fp = fopen("./tokens/token-" . $id . ".txt", "wb");

    Header("Location:https://www.wellnesstrade.cz/admin/pages/system/nastaveni-pristupu?succes=edit");
    exit;

}

include VIEW . '/default/header.php';

?>

<div class="row" style="margin-bottom: 16px;">
	<div class="col-md-10 col-sm-7">
		<h2 style="float: left"><?= $pagetitle ?></h2>
	</div>
	<div class="col-md-2 col-sm-5" style="text-align: right;float:right;">


				<a ref="javascript:;" onclick="jQuery('#add_admin').modal('show');" style=" margin-right: 16px;" class="btn btn-default btn-icon icon-left btn-lg">
					<i class="entypo-plus"></i>
					Přidat člověka
				</a>

	</div>
</div>

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

jQuery(document).ready(function($){

 myform.password.value = randomPassword(myform.length.value);

});


</script>


<style>

    .checkbox-label {
        line-height: 32px; width: 100%; margin: 0; cursor: pointer; padding-top: 1px; text-align: center;
    }
    .checkbox-label input {

        cursor: pointer;
    }

</style>


<div style="background-color: #FFF; position: -webkit-sticky; /* Safari & IE */  position: sticky; top: 0px; padding-top: 20px;">
    <table class="table table-bordered table-striped">
        <thead>
            <th style="width: 330px;">Stránka</th>

            <?php

            $admins_query = $mysqli->query("SELECT c.active, c.role, c.user_name, c.id as id, c.dimension FROM demands c WHERE c.role != 'client' AND c.role != 'technician' GROUP BY c.id");
            while ($admin = mysqli_fetch_assoc($admins_query)) {

                ?><th style="width: 100px; color: #333; text-align: center; font-size: 10px;"><strong><?= $admin['user_name'] ?></strong> <br>ID: <?= $admin['id'] ?> <br> 

                <strong  data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $admin['dimension'] ?>"><i class="fa fa-envelope"></i></strong>
                </th><?php

            }
            ?>
        </thead>
    </table>
</div>


<form role="form" method="post" class="form-horizontal form-groups-bordered validate" action="nastaveni-pristupu?action=set_accesses" enctype="multipart/form-data">

    <?php

    $main_sites_query = $mysqli->query("SELECT * FROM administration_sites s WHERE special = 'main' ORDER BY rank")or die($mysqli->error);

    while ($main_site = mysqli_fetch_assoc($main_sites_query)) {

        echo '<h5>' . $main_site['name'] . '</h5>';
        echo '<table class="table table-bordered table-striped table-hover"><tbody>';

        $sites_query = $mysqli->query("SELECT * FROM administration_sites WHERE (main_id = '" . $main_site['id'] . "') OR (id = '".$main_site['id']."' AND link_url != '') ORDER BY CASE WHEN special = '' THEN 0 ELSE 1 END, rank ASC");

        while ($site = mysqli_fetch_assoc($sites_query)) {

            echo '<tr><td style="width: 200px">' . $site['name'] . '</td>';

            mysqli_data_seek($admins_query, 0);
            while ($admin = mysqli_fetch_assoc($admins_query)) {

                $find_query = $mysqli->query("SELECT value FROM administration_accesses WHERE site_id = '" . $site['id'] . "' AND admin_id = '" . $admin['id'] . "'");
                $find = mysqli_fetch_assoc($find_query);

                $checked = isset($find['value']) && $find['value'] == 1 ? 'checked' : '';

                echo '<td style="width: 100px; padding: 0;"><label class="checkbox-label"><input name="admin['.$admin['id'].']['.$site['id'].']" class="select_all_'.$admin['id'].'" value="1" type="checkbox" style="" '.$checked.'/></label></td>';

            }
            echo '</tr>';



            $sub_site_query = $mysqli->query("SELECT * FROM administration_sites WHERE main_id = '" . $site['id'] . "' ORDER BY CASE WHEN special = '' THEN 0 ELSE 1 END, rank ASC");

            while ($sub_site = mysqli_fetch_assoc($sub_site_query)) {

                echo '<tr><td style="width: 200px">' . $sub_site['name'] . '</td>';

                mysqli_data_seek($admins_query, 0);
                while ($admin = mysqli_fetch_assoc($admins_query)) {

                    $find_query = $mysqli->query("SELECT value FROM administration_accesses WHERE site_id = '" . $sub_site['id'] . "' AND admin_id = '" . $admin['id'] . "'");
                    $find = mysqli_fetch_assoc($find_query);

                    $checked = isset($find['value']) && $find['value'] == 1 ? 'checked' : '';

                    echo '<td style="width: 100px; padding: 0;"><label class="checkbox-label"><input name="admin[' . $admin['id'] . '][' . $sub_site['id'] . ']" class="select_all_' . $admin['id'] . '" value="1" type="checkbox" style="" ' . $checked . '/></label></td>';

                }
                echo '</tr>';

            }


        }
        echo '</tbody></table>';
    }


    // 9997 = calendar
    // 9998 = special_add_edit
    // 9999 = special_container

    // all demands states
    $specials = [
        ['id' => 9997, 'name' => 'Kalendář'],
        ['id' => 9998, 'name' => 'Přidávat a Upravovat'],
        ['id' => 9999, 'name' => 'Kompletní kontejner'],
        ['id' => 119, 'name' => 'Vyhledávání'],
    ];


    echo '<h5>Speciální</h5>';
    echo '<table class="table table-bordered table-striped table-hover"><tbody>';

    foreach($specials as $special){

        echo '<tr><td style="width: 200px">' . $special['name'] . '</td>';

        mysqli_data_seek($admins_query, 0);
        while ($admin = mysqli_fetch_assoc($admins_query)) {

            $find_query = $mysqli->query("SELECT value FROM administration_accesses WHERE site_id = '" . $special['id'] . "' AND admin_id = '" . $admin['id'] . "'");
            $find = mysqli_fetch_assoc($find_query);

            $checked = isset($find['value']) && $find['value'] == 1 ? 'checked' : '';

            echo '<td style="width: 100px; padding: 0;"><label class="checkbox-label"><input name="admin[' . $admin['id'] . '][' . $special['id'] . ']" class="select_all_' . $admin['id'] . '" value="1" type="checkbox" style="" ' . $checked . '/></label></td>';

        }
        echo '</tr>';
    }

    echo '</tbody></table>';

    ?>
    <center>
        <div class="form-group default-padding">
            <button type="submit" style="margin-bottom: 14px; margin-right: 26px; font-size: 18px; padding: 14px 50px 14px 80px; margin-top: 0px;" data-style="zoom-in" class="btn btn-success btn-icon icon-left btn-lg"><i class="entypo-plus" style="line-height: 26px;font-size: 20px; padding: 14px 14px;"></i> Uložit</button>
        </div>
    </center>
</form>
<br>
<hr>
<br><br>
<h4>Seznam techniků</h4>
<?php

//echo '<h5>Speciální</h5>';
echo '<table class="table table-bordered table-striped table-hover"><tbody>';

$admins_query = $mysqli->query("SELECT c.active, c.role, c.user_name, c.id as id FROM demands c WHERE c.role != 'client' and c.role = 'technician' AND c.active = 1 GROUP BY c.id");
while ($admin = mysqli_fetch_assoc($admins_query)) {

    echo '<th style="width: 100px; color: #333; text-align: center;"><strong>'.$admin['user_name'].'</strong> &nbsp; &nbsp;<a href="./nastaveni-pristupu?action=enable_disable_admin&type=disable&id='.$admin['id'].'" class="btn btn-red" data-toggle="tooltip" data-placement="top" title="" data-original-title="Deaktivovat (profil zůstane, ale nebude se zobrazovat jako možnost a přihlášení nebude možné)."><i class="entypo-cancel-circled"></i></a>
    </th>';

}




echo '</tbody></table>';


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

  <div class="modal fade" id="add_admin" aria-hidden="true" style="display: none;  top: 0;">
        <div class="modal-dialog" style="width: 1000px;">
          <div class="modal-content">
          <form role="form" role="form" method="post" name="myform" class="form-horizontal form-groups-bordered validate" action="nastaveni-pristupu?action=add_admin" enctype="multipart/form-data">
        <input type="hidden" name="length" value="14">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 class="modal-title">Přidání nové specifikace</h4>
            </div>

            <div class="modal-body">

           <div class="form-group">
                <label for="field-2" class="col-sm-1 control-label"><strong>Jméno</strong></label>

                <div class="col-sm-3">
                  <input type="text" class="form-control" name="name">

                </div>
           </div>


                <label for="field-2" class="col-sm-1 control-label"><strong>Příjmení</strong></label>

            <div class="col-sm-3">
              <input type="text" class="form-control" name="surname">

            </div>

            <label for="field-2" class="col-sm-1 control-label"><strong>Email</strong></label>

            <div class="col-sm-3">
              <input type="text" class="form-control" name="email">

            </div>

            <label for="field-2" class="col-sm-1 control-label"><strong>Heslo</strong></label>

            <div class="col-sm-3">
              <input type="text" class="form-control" name="password">
              <input type="button" class="btn btn-white" value="Vygenerovat" onClick="generate();" tabindex="2">

            </div>

            <label for="field-2" class="col-sm-1 control-label"><strong>Role</strong></label>

            <div class="col-sm-3">
            	<select name="role" class="form-control">
            		<option value="admin">Admin</option>
            		<option value="salesman">Prodejce</option>
            		<option value="technician">Technik</option>
            		<option value="assistant">Asistentka</option>
            		<option value="salesman-technician">Prodejce + Technik</option>
            	</select>

            </div>



            </div>

            <div style="clear: both"></div>


       <div class="modal-footer" style="text-align:left;">
            <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>
              <button type="submit" class="btn btn-green btn-icon icon-left" style="float: right;">Přidat <i class="entypo-pencil"></i></button>
          </div>

      </form>

          </div>
        </div>
      </div>

<?php include VIEW . '/default/footer.php'; ?>

