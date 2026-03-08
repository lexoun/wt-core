<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";

$id = $_REQUEST['id'];

$getclientquery = $mysqli->query("SELECT secretstring FROM demands WHERE id = '" . $id . "'") or die($mysqli->error);
$getclient = mysqli_fetch_assoc($getclientquery);


$types = ['chranic', 'jistic', 'kabel', 'pruchodnost', 'umisteni'];

foreach($types as $type){

    if (isset($_REQUEST['type']) && $_REQUEST['type'] == $type) {

        $storeFolder = $_SERVER['DOCUMENT_ROOT'] . '/data/clients/pictures/'.$type.'/' . $getclient['secretstring'];

        break;
    }

}

if (!empty($_FILES)) {

    if (!file_exists($storeFolder)) {
        mkdir($storeFolder, 0777, true);
    }

    $tempFile = $_FILES['file']['tmp_name']; //3


    // Create thumbnail (small) image
    $im = new imagick($tempFile);

    $im->scaleImage(133, 133, true);

    $im->setImageBackgroundColor('white');

    $w = $im->getImageWidth();
    $h = $im->getImageHeight();
    $im->extentImage(133, 133, ($w - 133) / 2, ($h - 133) / 2);

    $im->setImageCompressionQuality(40);

    $im->writeImage($storeFolder . '/small_' . $_FILES['file']['name']);



    // Create compressed full 1600x1600 image

    $im = new imagick($tempFile);

    $im->setImageCompressionQuality(40);

    $size = $im->getImageGeometry();
    $maxWidth = 1600;
    $maxHeight = 1600;


    // ----------
    // |        |
    // ----------
    if($size['width'] >= $size['height']){
        if($size['width'] > $maxWidth){
            $im->resizeImage($maxWidth, 0, Imagick::FILTER_LANCZOS, 1);
        }
    }


    // ------
    // |    |
    // |    |
    // |    |
    // |    |
    // ------
    else{
        if($size['height'] > $maxHeight){
            $im->resizeImage(0, $maxHeight, Imagick::FILTER_LANCZOS, 1);
        }
    }

    $im->writeImage($storeFolder . '/big_' . $_FILES['file']['name']);

}
