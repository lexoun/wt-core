<ol class="breadcrumb bc-3">
    <li>
        <a href="/admin/"><i class="entypo-home"></i>Přehled</a>
    </li>
    <?php if (!empty($abread1)) { ?>
        <li>
            <a href="./<?= $abread1 ?>"><?= $bread1 ?></a>
        </li>
    <?php } elseif (!empty($bread1)) { ?>
        <li><?= $bread1 ?></li>
    <?php }
    if (!empty($abread2)) { ?>
        <li>
            <a href="./<?= $abread2 ?>"><?= $bread2 ?></a>
        </li>
    <?php } elseif (!empty($bread2)) { ?>
        <li><?= $bread2 ?></li>
    <?php }
    if (!empty($abread3)) { ?>
        <li>
            <a href="./<?= $abread3 ?>"><?= $bread3 ?></a>
        </li>
    <?php } elseif (!empty($bread3)) { ?>
        <li><?= $bread3 ?></li>
    <?php } ?>
    <li class="active">
        <a href="<?= $_SERVER['REQUEST_URI'] ?>">
            <strong><?= $pagetitle ?></strong>
        </a>
    </li>
</ol>