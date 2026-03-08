<?php

if($_REQUEST['action'] === 'remove') {

    $path = pathinfo($_POST['src']);
    $name = $path['basename'];
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/data/stores/description_images/' . $name;
    echo $fullPath;

    if(file_exists($fullPath)){

        unlink($fullPath);

        echo 'image deleted';

    }else{


        echo 'image not found';

    }
    exit;
}


if ($_FILES['file']['name']) {

    if (!$_FILES['file']['error']) {

        $productDir = $_SERVER['DOCUMENT_ROOT'] . '/data/stores/description_images/'.$_REQUEST['product_id'];

        /*
        if (!is_dir($productDir) && !mkdir($concurrentDirectory = $productDir, 0777) && !is_dir($concurrentDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }*/

        if (!mkdir($productDir, 0755) && !is_dir($productDir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $productDir));
        }

        $name = md5(rand(100, 200));
        $ext = explode('.', $_FILES['file']['name']);
        $filename = $name . '.' . $ext[1];
        $destination = $productDir . '/' . $filename;
        $location = $_FILES["file"]["tmp_name"];
        move_uploaded_file($location, $destination);

        echo 'https://www.wellnesstrade.cz/data/stores/description_images/' . $_REQUEST['product_id'] . '/' . $filename;

    }
    else
    {
        echo  $message = 'Ooops!  Your upload triggered the following error:  '.$_FILES['file']['error'];
    }
}
