<?php if ((isset($pocet_prispevku) && isset($perpage)) && ($pocet_prispevku > $perpage)) {

    if (isset($category) || isset($customer) || isset($year) || isset($type) || isset($site)) {
        $spoil = '&';
    } else { $spoil = '?';}

    $pred = $od - 1;
    $po = $od + 1;
    $po_s = $od * $perpage;
    if ($od == 1) { ?>
	<li class="prev disabled"><a href="#"><i class="entypo-left-open"></i></a></li>
<?php
    } else { ?>

<li class="prev"><a href="./<?= $currentpage . $spoil ?>od=<?= $pred ?>"><i class="entypo-left-open"></i></a></li>

<?php
    }

    $pocet = $pocet_prispevku / $perpage;

    for ($i = $od - 5; $i < $pocet; $i++) {
        if ($i > -1) {

            $o = $i + 1;
            if ($o == $od) {echo '<li class="active">';} else {echo '<li>';}
            if ($o == 1) {echo '<a href="./' . $currentpage . '"';}
            echo '<a href="./' . $currentpage . $spoil . 'od=' . $o . '">';
            echo '' . $o . '</a></li>';
            if ($i > $od + 2) {
                break;
            }
        }}

    if ($po_s < $pocet_prispevku) { ?>
	<li class="next"><a href="./<?= $currentpage . $spoil ?>od=<?= $po ?>"><i class="entypo-right-open"></i></a></li>
<?php
    } else { ?>
	<li class="next disabled"><a href="#"><i class="entypo-right-open"></i></a></li>
<?php
    }
}?>
