<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";

$ds = DIRECTORY_SEPARATOR; //1

$storeFolder = $_SERVER['DOCUMENT_ROOT'] . '/admin/data/images/services/' . $_REQUEST['id']; //2

if (!empty($_FILES)) {

    if (!file_exists($storeFolder)) {
        mkdir($storeFolder, 0777, true);
    }

    $tempFile = $_FILES['file']['tmp_name']; //3

    $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

    if($ext != 'mp4' && $ext != 'avi' && $ext != 'mkv'){

        $im = new imagick($tempFile);
        /* create the thumbnail */

        //$im->cropThumbnailImage( 400, 400 );
        $im->scaleImage(133, 133, true);

        $im->setImageBackgroundColor('white');

        $w = $im->getImageWidth();
        $h = $im->getImageHeight();
        $im->extentImage(133, 133, ($w - 133) / 2, ($h - 133) / 2);

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


    }else{

        $targetFile = $storeFolder . '/' . $_FILES['file']['name'];
        move_uploaded_file($tempFile, $targetFile);

    }





}
