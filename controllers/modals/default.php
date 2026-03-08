<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";

$id = $_REQUEST['id'];

if (isset($_REQUEST['type']) && $_REQUEST['type'] == "createUser") {

    $dataQuery = $mysqli->query("SELECT id, user_name, customer, email FROM demands WHERE id = '$id'");
    $data = mysqli_fetch_assoc($dataQuery);

    if($data['customer'] == 0){ $eshop = 'Saunahouse'; }else{ $eshop = 'Spahouse'; }

    $modal['title'] = 'Vytvoření klienta -  ' . $data['user_name'];
    $modal['text'] = 'Právě vytváříte klientský profil pro klienta <strong>' . $data['user_name'] . '</strong> v obchodě <strong>'.$eshop.'.cz</strong>.<br><br>Klientovi bude zaslán informační email s přihlašovacími údajemi na adresu: <strong>' . $data['email'] . '</strong>.';

    $modal['button'] = 'Vytvořit klienta';
    $modal['buttonColor'] = 'btn-green';
    $modal['link'] = '?action=createUser&id=' . $data['id'];


} elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == "resetUserPassword") {

    $dataQuery = $mysqli->query("SELECT id, user_name, customer, email FROM demands WHERE id = '$id'");
    $data = mysqli_fetch_assoc($dataQuery);

    if($data['customer'] == 0){ $eshop = 'Saunahouse'; }else{ $eshop = 'Spahouse'; }

    $modal['title'] = 'Resetování hesla -  ' . $data['user_name'];
    $modal['text'] = 'Právě se chystáte resetovat heslo pro klienta <strong>' . $data['user_name'] . '</strong> v obchodě <strong>'.$eshop.'.cz</strong>.<br><br>Klientovi bude zaslán informační email s resetovanými přihlašovacími údajemi na adresu: <strong>' . $data['email'] . '</strong>';

    $modal['button'] = 'Resetovat heslo';
    $modal['buttonColor'] = 'btn-info';
    $modal['link'] = '?action=resetUserPassword&id=' . $data['id'];

} elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == "finishClient") {

    $dataQuery = $mysqli->query("SELECT id, user_name, customer, email FROM demands WHERE id = '$id'");
    $data = mysqli_fetch_assoc($dataQuery);

    if($data['customer'] == 0){ $eshop = 'Saunahouse'; }else{ $eshop = 'Spahouse'; }

    if(empty($_REQUEST['status'])){ $_REQUEST['status'] = ''; }

    $modal['title'] = 'Ukočení klienta -  ' . $data['user_name'];
    $modal['text'] = 'Byl zvolen jeden ze stavů <i>Nedokončená, Dokončená nebo Hotová</i>. <br><br>Se změnou na tento stav se na skladě <strong>vyskladní vířivka</strong> přidělená zákazníkovi a zároveň se klientovi vytvoří profil v obchodě <strong>'.$eshop.'.cz</strong>.<br><br>Klientovi bude zaslán informační email s přihlašovacími údajemi na adresu: <strong>' . $data['email'] . '</strong>.';

    $modal['button'] = 'Ukočení klienta';
    $modal['buttonColor'] = 'btn-green';
    $modal['link'] = '?action=createUser&id=' . $data['id'].'&status='.$_REQUEST['status'];

}


?>
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

            <h4 class="modal-title"><?= $modal['title'] ?></h4> </div>

        <div class="modal-body" style="padding: 36px 35px 20px 35px; text-align: center;">

            <?= $modal['text'] ?>

        </div>

        <div class="modal-footer" style="text-align:left;"> <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>

            <div style="float: right;"><a href="<?= $modal['link'] ?>"><button type="submit" class="btn <?= $modal['buttonColor'] ?> "><?= $modal['button'] ?></button></a></div>

        </div>
    </div>
</div>
