<?php

if (isset($clients['customer']) && $clients['customer'] == 0) {

    $sauna = 'yes';
    $saunaid = $clients['warehouse_id'];

    if ($clients['serial_number'] != "") {$name = $clients['serial_number'];} else { $name = '#' . $clients['warehouse_id'];}
    ?>

<div class="member-entry" style="margin: 0;">

<a class="member-img" style="width: 6%;">
		<img src="https://www.wellnesstrade.cz/admin/data/images/customer/<?= $clients['product'] ?>.png" width="90px" class="img-rounded" />
		<i class="entypo-forward"></i>
	</a>
	<div class="member-details">
		<h4 style="float: left;">
			#<?= $clients['id'] ?> | <?= $clients['code'] ?>: <?= $clients['brand'] . ' ' . ucfirst($clients['fullname']) ?>
	</h4>
					<div style="float:right;margin-top: 0px;padding-right: 0px;">
				<a href="../admin/upravit-saunu?id=<?= $clients['warehouse_id'] ?>&redirect=<?= $getclient['id'] ?>" class="btn btn-primary btn-sm btn-icon icon-left">
					<i class="entypo-pencil"></i>
					Upravit
				</a>
				<a href="../admin/sauny?action=demandnull&id=<?= $clients['warehouse_id'] ?>&redirect=<?= $getclient['id'] ?>" class="btn btn-danger btn-sm btn-icon icon-left">
					<i class="entypo-cancel"></i>
					Odebrat od poptávky
				</a>
						</div>
<div class="clear"></div>
		<!-- Details with Icons -->		<div class="row info-list">
		<div class="col-sm-4" style="margin-top: 8px;">

							<i class="entypo-right-open-mini"></i>
							Výrobní číslo: <strong><?= $clients['serial_number'] ?></strong>
									</div>
									<div class="col-sm-4" style="margin-top: 8px;">

							<i class="entypo-right-open-mini"></i>
							Sauna <strong><?php if (isset($clients['ram']) && $clients['ram'] == 1) {echo 's rámem';} else {echo 'bez rámu';}?></strong>
									</div>
									<br><br>

		<?php if (isset($clients['customer']) && $clients['customer'] == 0) {
        $specsquery = $mysqli->query("SELECT s.reserved, s.quantity, p.productname, p.category FROM demands_sauna_specs s, products p WHERE s.demand_id = '" . $getclient['id'] . "' and p.id = s.product_id") or die($mysqli->error);
        if (mysqli_num_rows($specsquery) > 0) {
            while ($specs = mysqli_fetch_array($specsquery)) {

                $catq = $mysqli->query("SELECT name FROM products_cats WHERE seoslug = '" . $specs['category'] . "'") or die($mysqli->error);
                $cat = mysqli_fetch_assoc($catq);

                ?>

<div class="col-sm-4" style="margin-bottom: 6px; line-height: 26px;">

							<i class="entypo-right-open-mini"></i>
							<?= $cat['name'] ?>: <strong style=" margin-right: 8px;"><?= $specs['productname'] ?></strong><?php if ($specs['reserved'] >= 1) {echo '<small style="margin-right: -3px;">' . $specs['reserved'] . 'x</small><i data-toggle="tooltip" data-placement="top" title="" data-original-title="Rezervováno" style="color: #00a651; font-size: 18px;" class="entypo-check"></i>';}if ($specs['quantity'] > $specs['reserved']) {$rozdil = $specs['quantity'] - $specs['reserved'];
                    echo '<small style="margin-right: -1px;">' . $rozdil . 'x</small><i style="color: #d42020;font-size: 16px;" class="entypo-cancel-circled" data-toggle="tooltip" data-placement="top" title="" data-original-title="Chybí"></i>';}?>
				</div>



<?php }} else { ?>
<div class="col-sm-12" style="margin-bottom: 6px; line-height: 26px;">

							<i class="entypo-right-open-mini"></i>Sauna je přiřazená k poptávce u které zatím nejsou přidělené žádné specifikace.

				</div>

<?php }} else { ?>
<div class="col-sm-12" style="margin-bottom: 6px; line-height: 26px;">

							<i class="entypo-right-open-mini"></i>Sauna je zatím volná, a proto k ní nejsou přidělené žádné specifikace.

				</div>
<?php }if (isset($clients['description']) && $clients['description'] != "") { ?>
<div class="col-sm-12" style="margin-bottom: 6px; line-height: 26px;">

							<i class="entypo-right-open-mini"></i><?= $clients['description'] ?>

				</div>
				<?php } ?>
			<hr style="float: left;width: 100%;margin-top: 10px; margin-bottom: 3px;">
					<div class="col-sm-3" style="margin-top: 8px;">

							<i class="entypo-right-open-mini"></i>
							Prodejní cena: <strong><?= number_format($clients['sale_price'], 0, ',', ' ') ?>,- Kč</strong>
									</div>
									<div class="col-sm-3" style="margin-top: 8px;">

							<i class="entypo-right-open-mini"></i>
							Nákupní cena: <strong><?= number_format($clients['purchase_price'], 0, ',', ' ') ?>,- Kč</strong>
									</div>
									<div class="col-sm-3" style="margin-top: 8px;">
<!--
							<i class="entypo-right-open-mini"></i>
							Reálně inkasováno: <strong><echo number_format($clients['real_price'], 0, ',', ' '); ?>,- Kč</strong>
									</div> -->
		</div>
	</div>

</div>


<?php } else {
    $virivka = 'yes';
    $virivkaid = $clients['warehouse_id'];

    if ($clients['serial_number'] != "") {$name = $clients['serial_number'];} else { $name = '#' . $clients['warehouse_id'];}
    ?>

<div class="member-entry" style="margin: 0;">
	<a class="member-img" style="width: 6%;">
		<img src="https://www.wellnesstrade.cz/admin/data/images/customer/<?= $clients['product'] ?>.png" width="90px" class="img-rounded" />
		<i class="entypo-forward"></i>
	</a>

	<div class="member-details">
		<h4 style="float: left;"><?= $name ?> | <?= $clients['brand'] . ' ' . ucfirst($clients['fullname']) ?></h4>

						<div style="float:right;margin-top: 0px;padding-right: 0px;">
				<a href="../admin/upravit-virivku?id=<?= $clients['warehouse_id'] ?>&redirect=<?= $getclient['id'] ?>" class="btn btn-primary btn-sm btn-icon icon-left">
					<i class="entypo-pencil"></i>
					Upravit
				</a>
				<a href="../admin/virivky?action=demandnull&id=<?= $clients['warehouse_id'] ?>&redirect=<?= $getclient['id'] ?>" class="btn btn-danger btn-sm btn-icon icon-left">
					<i class="entypo-cancel"></i>
					Odebrat od poptávky
				</a>
						</div>
<div class="clear"></div>

		<!-- Details with Icons -->		<div class="row info-list">

		<?php if (isset($clients['customer']) && $clients['customer'] == 1) {
        $specsquery = $mysqli->query("SELECT s.id, s.name, w.value as warehouse_value, d.value as demand_value FROM specs s LEFT JOIN warehouse_specs_bridge w ON w.specs_id = s.id AND w.client_id = '" . $clients['warehouse_id'] . "' LEFT JOIN demands_specs_bridge d ON d.specs_id = s.id AND d.client_id = '" . $getclient['id'] . "' WHERE s.product = 1 AND s.supplier = 1 order by s.type desc, s.id asc") or die($mysqli->error);
        while ($specs = mysqli_fetch_array($specsquery)) {

            ?>

<div class="col-sm-3" style="margin-top: 8px;">

							<i class="entypo-right-open-mini"></i>
							<?php if ($specs['id'] != 7) {echo $specs['name'];?>: <?php } ?><strong><?= $specs['warehouse_value'] ?></strong>
							<?php if ($specs['demand_value'] != $specs['warehouse_value']) { ?><i style="color: #d42020;font-size: 16px; margin-left: 1px;margin-top: -3px;position: absolute;" class="entypo-attention" data-toggle="tooltip" data-placement="top" title="" data-original-title="Specifikace u vířivky neodpovídá zvolené specifikaci u poptávky."></i><?php } ?>
				</div>


<?php }} /* if($access_edit){ ?>
    <hr style="float: left;width: 100%;margin-top: 18px; margin-bottom: 3px;">
    <div class="col-sm-3" style="margin-top: 8px;">

    <i class="entypo-right-open-mini"></i>
    Prodejní cena: <strong><?= number_format($warehouse['sale_price'], 0, ',', ' ') ?>,- Kč</strong>
    </div>
    <div class="col-sm-3" style="margin-top: 8px;">

    <i class="entypo-right-open-mini"></i>
    Nákupní cena: <strong><?= number_format($warehouse['purchase_price'], 0, ',', ' ') ?>,- Kč</strong>
    </div>
    <div class="col-sm-3" style="margin-top: 8px;">

    <i class="entypo-right-open-mini"></i>
    Reálně inkasováno: <strong><?= number_format($warehouse['real_price'], 0, ',', ' ') ?>,- Kč</strong>
    </div>    <?php }*/?>

									<?php if (isset($warehouse['description']) && $warehouse['description'] != "") { ?>

										<hr style="float: left;width: 100%;margin-top: 18px; margin-bottom: 3px;">
					<div class="col-sm-8" style="margin-top: 8px;">
							<i class="entypo-info"></i>
						<?= $warehouse['description'] ?>
									</div>

									<?php } ?>
		</div>

	</div></div>
	<?php } ?>