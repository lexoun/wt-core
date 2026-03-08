<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";

$id = $_REQUEST['id'];

$dataQuery = $mysqli->query("SELECT subject, content FROM mails_archive WHERE id = '$id'");
$data = mysqli_fetch_assoc($dataQuery);

?>


<div class="modal-dialog" style="width: 1000px;">
    <div class="modal-content">
        <div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

            <h4 class="modal-title"><?= $data['subject'] ?></h4> </div>

        <div class="modal-body" style="padding: 0;">

            <?= $data['content'] ?>

        </div>

        <div class="modal-footer" style="text-align:left;">
            <div style="float: right;"><button type="submit" class="btn btn-default" data-dismiss="modal">Zavřít</button></div>

        </div>
    </div>
</div>
