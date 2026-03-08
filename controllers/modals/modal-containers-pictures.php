<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configModal.php";

if (isset($_REQUEST['hottub_id'])) {

    $result = glob($_SERVER['DOCUMENT_ROOT'] . "/admin/data/containers/" . $_REQUEST['link'] . "/" . $_REQUEST['id'] . "/" . $_REQUEST['hottub_id'] . "/small_*.*");

    if (!empty($result)) {

        ?>

	 	<div class="well col-sm-12 lightgallery">

		 	<?php

        foreach ($result as $res) {

            $str = basename($res);

            $image = substr($str, 6);

            ?>

		 		<a data-src="https://www.wellnesstrade.cz/admin/data/containers/<?= $_REQUEST['link'] . "/".$_REQUEST['id'] ?>/<?= $_REQUEST['hottub_id'] ?>/<?= $image ?>" style="margin: 4px 9px;" class="full" rel="hottub-pictures">
		 			<img src="https://www.wellnesstrade.cz/admin/data/containers/<?= $_REQUEST['link'] . "/".$_REQUEST['id'] ?>/<?= $_REQUEST['hottub_id'] ?>/<?= basename($res) ?>" width="120">
		 		</a>



		 	<?php } ?>

		 </div>

	 		<?php }

} elseif (isset($_REQUEST['type'])) {

    $result = glob($_SERVER['DOCUMENT_ROOT'] . "/admin/data/containers/" . $_REQUEST['link'] . "/".$_REQUEST['id'] . "/" . $_REQUEST['type'] . "/small_*.*");

    if (!empty($result)) {

        ?>

	 	<div class="well col-sm-12 lightgallery">

	 	<?php

        foreach ($result as $res) {

            $str = basename($res);

            $image = substr($str, 6);

            ?><a data-src="https://www.wellnesstrade.cz/admin/data/containers/<?= $_REQUEST['link'] . "/".$_REQUEST['id'] ?>/<?= $_REQUEST['type'] ?>/<?= $image ?>" style="margin: 4px 9px;" class="full" rel="/<?= $_REQUEST['type'] ?>"><img src="https://www.wellnesstrade.cz/admin/data/containers/<?= $_REQUEST['link'] . "/".$_REQUEST['id'] ?>/<?= $_REQUEST['type'] ?>/<?= basename($res) ?>" width="120"></a><?php } ?>

	 </div>

<?php }
}
?>
<script type="text/javascript">
    $(document).ready(function() {

        $('.lightgallery').lightGallery({
            selector: 'a.full'
        });

    });
</script>
