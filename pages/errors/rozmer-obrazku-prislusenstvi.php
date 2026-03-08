<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include $_SERVER['DOCUMENT_ROOT'] . "/admin/includes/functions.php";

$pagetitle = "Chyby obrázků příslušenství";


if(!empty($_REQUEST['action']) && $_REQUEST['action'] == 'unlink'){

    $basename = $_REQUEST['imagepath'];

    foreach($productImageSizes as $imageSize){

        $path = PRODUCT_IMAGE_PATH.'/'.$imageSize.'/'.$basename;
        if(file_exists($path)){ unlink($path); };

        unset($path);
    }

    Header('location: https://www.wellnesstrade.cz/admin/pages/errors/rozmer-obrazku-prislusenstvi');
    exit;

}

$clientquery = $mysqli->query('SELECT * FROM demands WHERE email="' . $_COOKIE['cookie_email'] . '"') or die($mysqli->error);
$client = mysqli_fetch_assoc($clientquery);

include VIEW . '/default/header.php';

$query = "";
$currentpage = "chyby-prislusenstvi";
$allow_sites = "";


?>


<?php
$perpage = 20;
?>

<div class="row">
	<div class="col-md-8 col-sm-7">
		<h2>Příslušenství</h2>
	</div>

    <div class="col-md-3 col-sm-5" style="text-align: right;float:right;">


                <a href="<?= $home ?>/admin/pages/errors/chyby-prislusenstvi" style=" margin-right: 24px;" class="btn btn-default btn-icon icon-left btn-lg">
                    <i class="entypo-cancel"></i>
                    Chyby příslušenství
                </a>

    </div>
</div>


<?php
/*
$files = glob($_SERVER['DOCUMENT_ROOT'] . "/data/stores/images/thumbnail/*.*");
echo "<table border=1>";
for ($i = 0; $i < count($files); $i++) {
    $image = $files[$i];

    list($width, $height) = getimagesize($image);

    if ($width > 2000 || $height > 2000) {

        $path = pathinfo($image);
        $name = $path['filename'];

        $find_query = $mysqli->query("SELECT id FROM products WHERE seourl = '$name'");

        if (mysqli_num_rows($find_query) > 0) {

            $findid = mysqli_fetch_array($find_query);

            $ext = $path['extension'];
            list($width, $height) = getimagesize($image);
            $resolution = $width . ' x ' . $height . ' pixels';
            $path = $path['dirname'];
            $size = filesize($image);

            echo "<tr>";
            echo "<td>";
            echo '<img src="https://www.wellnesstrade.cz/data/stores/images/small/' . $name . '.' . $ext . '" alt="Random image" width="100px" height="100px" />';
            echo "</td>";
            echo "<td>";
            echo '<a href="https://www.wellnesstrade.cz/admin/pages/accessories/zobrazit-prislusenstvi?id=' . $findid['id'] . '" target="_blank">' . $name . '</a>';
            echo "</td>";
            echo "<td>";
            echo $ext;
            echo "</td>";
            echo "<td>";
            echo $resolution;
            echo "</td>";
            echo "<td>";
            echo $size . ' Byte';
            echo "</td>";
            echo "<td>";
            echo $path;
            echo "</td>";
            echo "<td>";
            echo '<a href="https://www.wellnesstrade.cz/admin/pages/errors/rozmer-obrazku-prislusenstvi?action=regenerate&imagepath=' . $name . '.' . $ext . '">--- REGENERATE ---</a>';
            echo "</td>";

        } else {

            $ext = $path['extension'];
            list($width, $height) = getimagesize($image);
            $resolution = $width . ' x ' . $height . ' pixels';
            $path = $path['dirname'];
            $size = filesize($image);

            echo "<tr>";
            echo "<td>";
            echo '<img src="https://www.wellnesstrade.cz/data/stores/images/small/' . $name . '.' . $ext . '" alt="Random image" width="100px" height="100px" />';
            echo "</td>";
            echo "<td>";
            echo $name;
            echo "</td>";
            echo "<td>";
            echo $ext;
            echo "</td>";
            echo "<td>";
            echo $resolution;
            echo "</td>";
            echo "<td>";
            echo $size . ' Byte';
            echo "</td>";
            echo "<td>";
            echo $path;
            echo "</td>";
            echo "<td>";
            echo '<a href="https://www.wellnesstrade.cz/admin/pages/errors/rozmer-obrazku-prislusenstvi?action=regenerate&imagepath=' . $name . '.' . $ext . '">--- REGENERATE ---</a>';
            echo "</td>";
            echo "<td>";
            echo '<a href="https://www.wellnesstrade.cz/admin/pages/errors/rozmer-obrazku-prislusenstvi?action=unlink&imagepath=' . $name . '.' . $ext . '">--- SMAZAT ---</a>';
            echo "</td>";

        }
    }
}
echo "</table>";


*/

?>

<?php
/*
$files = glob($_SERVER['DOCUMENT_ROOT'] . "/data/stores/images/thumbnail/*.*");
echo "<table border=1>";
for ($i = 0; $i < count($files); $i++) {
    $image = $files[$i];

    list($width, $height) = getimagesize($image);

    if ($width > 2000 || $height > 2000) {

        $path = pathinfo($image);
        $name = $path['filename'];

        $explode = explode("_", $name);

        $name = $explode[0];

        $find_query = $mysqli->query("SELECT product_id FROM products_variations WHERE id = '$name'");

        if (mysqli_num_rows($find_query) > 0) {

            $findid = mysqli_fetch_array($find_query);

            $ext = $path['extension'];
            list($width, $height) = getimagesize($image);
            $resolution = $width . ' x ' . $height . ' pixels';
            $path = $path['dirname'];
            $size = filesize($image);

            echo "<tr>";
            echo "<td>";
            echo '<img src="https://www.wellnesstrade.cz/data/stores/images/small/' . $name . '.' . $ext . '" alt="Random image" width="100px" height="100px" />';
            echo "</td>";
            echo "<td>";
            echo '<a href="https://www.wellnesstrade.cz/admin/pages/accessories/zobrazit-prislusenstvi?id=' . $findid['findid'] . '" target="_blank">' . $name . '</a>';
            echo "</td>";
            echo "<td>";
            echo $ext;
            echo "</td>";
            echo "<td>";
            echo $resolution;
            echo "</td>";
            echo "<td>";
            echo $size . ' Byte';
            echo "</td>";
            echo "<td>";
            echo $path;
            echo "</td>";
            echo "<td>";
            echo '<a href="https://www.wellnesstrade.cz/admin/pages/errors/rozmer-obrazku-prislusenstvi?action=regenerate_variations&imagepath=' . $name . '.' . $ext . '">--- REGENERATE ---</a>';
            echo "</td>";

        } else {

            $ext = $path['extension'];
            list($width, $height) = getimagesize($image);
            $resolution = $width . ' x ' . $height . ' pixels';
            $path = $path['dirname'];
            $size = filesize($image);

            echo "<tr>";
            echo "<td>";
            echo '<img src="https://www.wellnesstrade.cz/data/stores/images/small/' . $name . '.' . $ext . '" alt="Random image" width="100px" height="100px" />';
            echo "</td>";
            echo "<td>";
            echo $name;
            echo "</td>";
            echo "<td>";
            echo $ext;
            echo "</td>";
            echo "<td>";
            echo $resolution;
            echo "</td>";
            echo "<td>";
            echo $size . ' Byte';
            echo "</td>";
            echo "<td>";
            echo $path;
            echo "</td>";
            echo "<td>";
            echo '<a href="https://www.wellnesstrade.cz/admin/pages/errors/rozmer-obrazku-prislusenstvi?action=regenerate_variations&imagepath=' . $name . '.' . $ext . '">--- REGENERATE ---</a>';
            echo "</td>";
            echo "<td>";
            echo '<a href="https://www.wellnesstrade.cz/admin/pages/errors/rozmer-obrazku-prislusenstvi?action=unlink_variation&imagepath=' . $name . '.' . $ext . '">--- SMAZAT ---</a>';
            echo "</td>";

        }
    }
}
echo "</table>";

*/

/*
?>
<div id="table-2_wrapper" class="dataTables_wrapper form-inline" role="grid"><table class="table table-bordered table-striped datatable dataTable" id="table-2" aria-describedby="table-2_info">
        <thead>
        <tr role="row">
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Student Name: activate to sort column ascending" style="width: 40px;">Obrázek</th>
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Student Name: activate to sort column ascending" style="width: 220px;">Název obrázku</th>
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Student Name: activate to sort column ascending" style="width: 220px;">Přípona</th>
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Student Name: activate to sort column ascending" style="width: 220px;">Rozměr obrázku</th>
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Student Name: activate to sort column ascending" style="width: 220px;">Akce</th>

        </tr>
        </thead>


        <tbody role="alert" aria-live="polite" aria-relevant="all">
<?php

$files = glob($_SERVER['DOCUMENT_ROOT'] . "/data/stores/images/small/*.*");


for ($i = 0; $i < count($files); $i++) {


    $image = $files[$i];
    list($width, $height) = getimagesize($image);

    $path = pathinfo($image);
    $name = $path['filename'];

    $explode = explode("_", $name);

    $name_lookup = $explode[0];

    $find_query = $mysqli->query("SELECT id FROM products WHERE seourl = '$name_lookup'");

    if (mysqli_num_rows($find_query) == 0) {

        $ext = $path['extension'];
        list($width, $height) = getimagesize($image);
        $resolution = $width . ' x ' . $height . ' pixels';
        $path = $path['dirname'];
        $size = filesize($image);

        echo "<tr>";
        echo "<td>";
        echo '<img src="https://www.wellnesstrade.cz/data/stores/images/small/' . $name . '.' . $ext . '" alt="Random image" width="40px" height="40px" />';
        echo "</td>";
        echo "<td>";
        echo $name;
        echo "</td>";
        echo "<td>";
        echo $ext;
        echo "</td>";
        echo "<td>";
        echo $resolution;
        echo "</td>";
        echo "<td>";
        echo '<a href="https://www.wellnesstrade.cz/admin/pages/errors/rozmer-obrazku-prislusenstvi?action=unlink&imagepath=' . $name . '.' . $ext . '" class="toggle-modal-remove btn btn-danger btn-sm btn-icon icon-left">
                        <i class="entypo-cancel"></i>
                        Smazat
                    </a>';
        echo "</td>";

    }

}
?>
        </tbody>
    </table>
</div>


<?php */ ?>











<div class="row">
    <div class="col-md-8 col-sm-7">
        <h2>Malé obrázky u jednoduchých produktů</h2>
    </div>
</div>


<?php

$files = glob($_SERVER['DOCUMENT_ROOT'] . "/data/stores/images/big/*.*");?>

<div id="table-2_wrapper" class="dataTables_wrapper form-inline" role="grid"><table class="table table-bordered table-striped datatable dataTable" id="table-2" aria-describedby="table-2_info">
    <thead>
        <tr role="row">
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Student Name: activate to sort column ascending" style="width: 40px;">Obrázek</th>
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Student Name: activate to sort column ascending" style="width: 220px;">Název obrázku</th>
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Student Name: activate to sort column ascending" style="width: 220px;">Přípona</th>
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Student Name: activate to sort column ascending" style="width: 220px;">Rozměr obrázku</th>
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Student Name: activate to sort column ascending" style="width: 220px;">Akce</th>

        </tr>
    </thead>


<tbody role="alert" aria-live="polite" aria-relevant="all">
    <?php for ($i = 0; $i < count($files); $i++) {

    $image = $files[$i];

    list($width, $height) = getimagesize($image);

    if ($width < 470 || $height < 470) {

        $path = pathinfo($image);
        $name = $path['filename'];

        $explode = explode("_", $name);

        $name_lookup = $explode[0];

        $find_query = $mysqli->query("SELECT id FROM products WHERE seourl = '$name_lookup'");

        if (mysqli_num_rows($find_query) > 0) {

            $findid = mysqli_fetch_array($find_query);

            $ext = $path['extension'];
            list($width, $height) = getimagesize($image);
            $resolution = $width . ' x ' . $height . ' pixels';
            $path = $path['dirname'];
            $size = filesize($image);

            echo "<tr>";
            echo "<td>";
            echo '<img src="https://www.wellnesstrade.cz/data/stores/images/small/' . $name . '.' . $ext . '" alt="Random image" width="40px" height="40px" />';
            echo "</td>";
            echo "<td>";
            echo '<a href="https://www.wellnesstrade.cz/admin/pages/accessories/zobrazit-prislusenstvi?id=' . $findid['id'] . '" target="_blank">' . $name . '</a>';
            echo "</td>";
            echo "<td>";
            echo $ext;
            echo "</td>";
            echo "<td>";
            echo $resolution;
            echo "</td>";
            echo "<td>";
            echo '<a href="https://www.wellnesstrade.cz/admin/pages/accessories/zobrazit-prislusenstvi?id=' . $findid['id'] . '" target="_blank" class="btn btn-default btn-sm btn-icon icon-left">
                    <i class="entypo-search"></i>
                    Zobrazit příslušenství
                </a><br>';
            echo "</td>";

        } else {

            $ext = $path['extension'];
            list($width, $height) = getimagesize($image);
            $resolution = $width . ' x ' . $height . ' pixels';
            $path = $path['dirname'];
            $size = filesize($image);

            echo "<tr>";
            echo "<td>";
            echo '<img src="https://www.wellnesstrade.cz/data/stores/images/small/' . $name . '.' . $ext . '" alt="Random image" width="40px" height="40px" />';
            echo "</td>";
            echo "<td>";
            echo $name;
            echo "</td>";
            echo "<td>";
            echo $ext;
            echo "</td>";
            echo "<td>";
            echo $resolution;
            echo "</td>";
            echo "<td>";
            echo '<a href="https://www.wellnesstrade.cz/admin/pages/errors/rozmer-obrazku-prislusenstvi?action=unlink&imagepath=' . $name . '.' . $ext . '" class="toggle-modal-remove btn btn-danger btn-sm btn-icon icon-left">
                    <i class="entypo-cancel"></i>
                    Smazat
                </a>';
            echo "</td>";

        }
    }
}
echo "</table>";
?>

<?php
exit;
$files = glob($_SERVER['DOCUMENT_ROOT'] . "/data/stores/images/thumbnail/*.*");?>

<div class="row">
    <div class="col-md-8 col-sm-7">
        <h2>Malé obrázky u variant produktů</h2>
    </div>
</div>

<div id="table-2_wrapper" class="dataTables_wrapper form-inline" role="grid"><table class="table table-bordered table-striped datatable dataTable" id="table-2" aria-describedby="table-2_info">
    <thead>
        <tr role="row">
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Student Name: activate to sort column ascending" style="width: 40px;">Obrázek</th>
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Student Name: activate to sort column ascending" style="width: 220px;">Produkt</th>
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Student Name: activate to sort column ascending" style="width: 220px;">Přípona</th>
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Student Name: activate to sort column ascending" style="width: 220px;">Rozměr obrázku</th>
            <th class="sorting" role="columnheader" tabindex="0" aria-controls="table-2" rowspan="1" colspan="1" aria-label="Student Name: activate to sort column ascending" style="width: 220px;">Akce</th>

        </tr>
    </thead>


<tbody role="alert" aria-live="polite" aria-relevant="all">
    <?php
for ($i = 0; $i < count($files); $i++) {
    $image = $files[$i];

    list($width, $height) = getimagesize($image);

    if ($width < 470 || $height < 470) {

        $path = pathinfo($image);
        $name = $path['filename'];

        $find_query = $mysqli->query("SELECT product_id FROM products_variations WHERE id = '$name'");

        if (mysqli_num_rows($find_query) > 0) {

            $findid = mysqli_fetch_array($find_query);

            $ext = $path['extension'];
            list($width, $height) = getimagesize($image);
            $resolution = $width . ' x ' . $height . ' pixels';
            $path = $path['dirname'];
            $size = filesize($image);

            echo "<tr>";
            echo "<td>";
            echo '<img src="https://www.wellnesstrade.cz/data/stores/images/small/' . $name . '.' . $ext . '" alt="Random image" width="40px" height="40px" />';
            echo "</td>";
            echo "<td>";
            echo '<a href="https://www.wellnesstrade.cz/admin/pages/accessories/zobrazit-prislusenstvi?id=' . $findid['product_id'] . '" target="_blank">' . $name . '</a>';
            echo "</td>";
            echo "<td>";
            echo $ext;
            echo "</td>";
            echo "<td>";
            echo $resolution;
            echo "</td>";
            echo "<td>";
            echo '<a href="https://www.wellnesstrade.cz/admin/pages/accessories/zobrazit-prislusenstvi?id=' . $findid['product_id'] . '" target="_blank" class="btn btn-default btn-sm btn-icon icon-left">
                    <i class="entypo-search"></i>
                    Zobrazit příslušenství
                </a><br>';
            echo "</td>";

        } else {

            $ext = $path['extension'];
            list($width, $height) = getimagesize($image);
            $resolution = $width . ' x ' . $height . ' pixels';
            $path = $path['dirname'];
            $size = filesize($image);

            echo "<tr>";
            echo "<td>";
            echo '<img src="https://www.wellnesstrade.cz/data/stores/images/small/' . $name . '.' . $ext . '" alt="Random image" width="40px" height="40px" />';
            echo "</td>";
            echo "<td>";
            echo $name;
            echo "</td>";
            echo "<td>";
            echo $ext;
            echo "</td>";
            echo "<td>";
            echo $resolution;
            echo "</td>";
            echo "<td>";
            echo '<a href="https://www.wellnesstrade.cz/admin/pages/errors/rozmer-obrazku-prislusenstvi?action=unlink_variation&imagepath=' . $name . '.' . $ext . '" class="toggle-modal-remove btn btn-danger btn-sm btn-icon icon-left">
                    <i class="entypo-cancel"></i>
                    Smazat
                </a>';
            echo "</td>";

        }
    }
}
echo "</table>";
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

	<script src="<?= $home ?>/admin/assets/js/jquery.validate.min.js"></script>

	<link rel="stylesheet" href="<?= $home ?>/admin/assets/js/select2/select2-bootstrap.css">
	<link rel="stylesheet" href="<?= $home ?>/admin/assets/js/select2/select2.css">
	<link rel="stylesheet" href="<?= $home ?>/admin/assets/js/selectboxit/jquery.selectBoxIt.css">
	<link rel="stylesheet" href="<?= $home ?>/admin/assets/js/daterangepicker/daterangepicker-bs3.css">
	<link rel="stylesheet" href="<?= $home ?>/admin/assets/js/icheck/skins/minimal/_all.css">
	<link rel="stylesheet" href="<?= $home ?>/admin/assets/js/icheck/skins/square/_all.css">
	<link rel="stylesheet" href="<?= $home ?>/admin/assets/js/icheck/skins/flat/_all.css">
	<link rel="stylesheet" href="<?= $home ?>/admin/assets/js/icheck/skins/futurico/futurico.css">
	<link rel="stylesheet" href="<?= $home ?>/admin/assets/js/icheck/skins/polaris/polaris.css">

	<!-- Bottom Scripts -->
	<script src="<?= $home ?>/admin/assets/js/gsap/main-gsap.js"></script>
	<script src="<?= $home ?>/admin/assets/js/jquery-ui/js/jquery-ui-1.10.3.minimal.min.js"></script>
	<script src="<?= $home ?>/admin/assets/js/bootstrap.js"></script>
	<script src="<?= $home ?>/admin/assets/js/joinable.js"></script>
	<script src="<?= $home ?>/admin/assets/js/resizeable.js"></script>
	<script src="<?= $home ?>/admin/assets/js/neon-api.js"></script>
	<script src="<?= $home ?>/admin/assets/js/select2/select2.min.js"></script>
	<script src="<?= $home ?>/admin/assets/js/bootstrap-tagsinput.min.js"></script>
	<script src="<?= $home ?>/admin/assets/js/typeahead.min.js"></script>
	<script src="<?= $home ?>/admin/assets/js/selectboxit/jquery.selectBoxIt.min.js"></script>
	<script src="<?= $home ?>/admin/assets/js/bootstrap-datepicker.js"></script>
	<script src="<?= $home ?>/admin/assets/js/bootstrap-timepicker.min.js"></script>
	<script src="<?= $home ?>/admin/assets/js/bootstrap-colorpicker.min.js"></script>
	<script src="<?= $home ?>/admin/assets/js/daterangepicker/moment.min.js"></script>
	<script src="<?= $home ?>/admin/assets/js/daterangepicker/daterangepicker.js"></script>
	<script src="<?= $home ?>/admin/assets/js/jquery.multi-select.js"></script>
	<script src="<?= $home ?>/admin/assets/js/icheck/icheck.min.js"></script>
	<script src="<?= $home ?>/admin/assets/js/neon-chat.js"></script>
	<script src="<?= $home ?>/admin/assets/js/neon-custom.js"></script>
	<script src="<?= $home ?>/admin/assets/js/neon-demo.js"></script>


</body>
</html>

