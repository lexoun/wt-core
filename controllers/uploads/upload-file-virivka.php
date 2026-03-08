<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";

$ds = DIRECTORY_SEPARATOR; //1

$storeFolder = $_SERVER['DOCUMENT_ROOT'] . '/admin/data/containers/' . $_REQUEST['link'] ."/" . $_REQUEST['id'] . "/" . $_REQUEST['hottub_id']; //2

if (!empty($_FILES)) {

    if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/admin/data/containers/" . $_REQUEST['link'] ."/" . $_REQUEST['id'])) {
        mkdir($_SERVER['DOCUMENT_ROOT'] . "/admin/data/containers/" . $_REQUEST['link'] ."/" . $_REQUEST['id'], 0777, true);

    }

    if (!file_exists($storeFolder)) {
        mkdir($storeFolder, 0777, true);

    }

    $tempFile = $_FILES['file']['tmp_name']; //3

    $im = new imagick($tempFile);
    /* create the thumbnail */

    //$im->cropThumbnailImage( 400, 400 );
    $im->scaleImage(120, 120, true);

    $im->setImageBackgroundColor('white');

    $w = $im->getImageWidth();
    $h = $im->getImageHeight();
    $im->extentImage(120, 120, ($w - 120) / 2, ($h - 120) / 2);

    $im->writeImage($_SERVER['DOCUMENT_ROOT'] . '/admin/data/containers/' . $_REQUEST['link'] ."/" . $_REQUEST['id'] . "/" . $_REQUEST['hottub_id'] . '/small_' . $_FILES['file']['name']);

    $targetFile = $_SERVER['DOCUMENT_ROOT'] . '/admin/data/containers/' . $_REQUEST['link'] ."/" . $_REQUEST['id'] . "/" . $_REQUEST['hottub_id'] . '/' . $_FILES['file']['name']; //5

    move_uploaded_file($tempFile, $targetFile); //6

}
