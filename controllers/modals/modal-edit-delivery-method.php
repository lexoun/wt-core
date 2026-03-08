<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";

$method_query = $mysqli->query("SELECT * FROM shops_delivery_methods WHERE id = '" . $_REQUEST['id'] . "'");
$method = mysqli_fetch_array($method_query);

?>
<div class="modal-dialog">

    <form role="form" method="post" action="zpusoby-dopravy?action=edit&id=<?= $method['id'] ?>" enctype="multipart/form-data">
        <div class="modal-content">
            <div class="modal-header"> <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>

                <h4 class="modal-title">Změna stavu metody #<?= $method['id'] ?></h4> </div>

            <div class="modal-body">

                <div class="panel panel-primary" data-collapsed="0">

                    <div class="panel-heading">
                        <div class="panel-title">
                            Cena
                        </div>

                    </div>

                    <div class="panel-body">
                        <div class="form-group">
                            <label class="col-sm-2 control-label" for="nah"></label>
                            <div class="col-sm-8">
                                <input type="text" style="height: 40px;" name="price" class="form-control" id="field-1" placeholder="Cena" value="<?= $method['price'] ?>">
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer" style="text-align:left;"> <button type="button" class="btn btn-default" data-dismiss="modal">Zrušit</button>

                <a href="#" style="float:right;"><button type="submit" class="btn btn-blue btn-icon icon-left">Upravit
                        <i class="entypo-bookmarks"></i></button></a>
    </form>
</div>

<!-- Bottom Scripts -->
<script src="https://www.wellnesstrade.cz/admin/assets/js/neon-custom.js"></script>
