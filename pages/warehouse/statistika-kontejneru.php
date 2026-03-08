<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

if (isset($_REQUEST['customer'])) {$customer = $_REQUEST['customer'];}

$pagetitle = "Statistika kontejnerů";

include VIEW . '/default/header.php';

/*
zaplacené zálohy
nezaplacené doplatky
kompletně zaplacené kontejnery?
 */

$countContainersQuery = $mysqli->query("SELECT 
       SUM(first_payment) as first, 
       SUM(second_payment) as second, 
       SUM(total_payment) as total FROM containers WHERE closed != '3'")or die($mysqli->error);

$countContainers = mysqli_fetch_assoc($countContainersQuery);


$countToBePaidQuery = $mysqli->query("SELECT COUNT(*) as total FROM containers WHERE closed != '3' AND first_payment != 0.00 AND second_payment = 0.00")or die($mysqli->error);

$countToBePaid = mysqli_fetch_assoc($countToBePaidQuery);


$containersArray = [];
$allContainersQuery = $mysqli->query("SELECT *, DATE_FORMAT(date_created, '%d. %m. %Y') as dateformated, DATE_FORMAT(date_due, '%d. %m. %Y') as due_formated FROM containers WHERE closed != '3' ORDER BY id DESC")or die($mysqli->error);

while($container = mysqli_fetch_assoc($allContainersQuery)){

    $containersArray[] = $container;

}

?>

<div class="row">
    <div class="col-md-8 col-sm-7">
        <h2><?= $pagetitle ?></h2>
    </div>

</div>
<?php /*
<div class="col-md-12 well" style="border-color: #ebebeb; margin-top: 8px;background-color: #fbfbfb;">


    <div class="clear"></div>
</div>
 */
 ?>

<div class="member-entry">
    <div style="width: 20%; float: left; text-align: center;"><h3 style="margin-top: 8.5px;"><span style=" font-size: 17px; color: #737881;">Zaplacené zálohy:</span><br> <?= thousand_seperator($countContainers['first']) ?> €</h3>

    </div>
    <div style="width: 20%; float: left; text-align: center;"><h3 style="margin-top: 8.5px;"><span style=" font-size: 17px; color: #737881;">Zaplacené doplatky:</span><br> <?= thousand_seperator($countContainers['second']) ?> €</h3></div>
    <div style="width: 20%; float: left; text-align: center;"><h3 style="margin-top: 8.5px;"><span style=" font-size: 17px; color: #737881;">Zbývá doplatit:</span><br> <?= thousand_seperator($countContainers['total'] - $countContainers['first'] - $countContainers['second']) ?> € <br> <small>(počet doplatků: <?= $countToBePaid['total'] ?>)</small></h3></div>
    <div style="width: 20%; float: left; text-align: center;"><h3 style="margin-top: 8.5px;"><span style=" font-size: 17px; color: #737881;">Dohromady zaplaceno:</span><br> <?= thousand_seperator($countContainers['total']) ?> €</h3></div>
</div>


<table class="table table-bordered table-striped datatable dataTable" id="table-2" aria-describedby="table-2_info">
    <thead>

        <tr>
            <td>ID</td>
            <td>Stav</td>
            <td>Záloha</td>
            <td>Zaplacený doplatek</td>
            <td>Zbývá doplatit</td>
            <td>Celkem</td>
        </tr>

    </thead>

    <tbody>
        <?php
        foreach($containersArray as $containers){
        ?>
        <tr>
            <td>
                <strong>
                    <?= !empty($containers['container_name']) ? $containers['container_name'] : $containers['id'] ?>
                </strong>
            </td>
            <td>
                <?php  if ($containers['date_due'] != '0000-00-00' && $containers['date_correction'] == 0) {
                    ?><button class="btn btn-orange btn-sm">ve výrobě - <strong><?= $containers['due_formated'] ?></strong></button><?php
                } elseif ($containers['date_due'] != '0000-00-00' && $containers['date_correction'] == 1 && $containers['closed'] != 3) {
                    ?><button class="btn btn-blue btn-sm">na cestě - <strong><?= $containers['due_formated'] ?></strong></button><?php
                } elseif($containers['date_received'] != '0000-00-00' && $containers['date_correction'] == 1 && $containers['closed'] == 3){
                    ?><button class="btn btn-success btn-sm">převzato - <strong><?= $containers['received_formated'] ?></strong></button><?php
                }
                ?>
            </td>
            <td>
                <?php if($containers['first_payment'] !== '0.00'){ ?>
                    <strong class="text-success"><?= thousand_seperator($containers['first_payment']) ?> €</strong>
                <?php }else{ echo $containers['first_payment']; } ?>
            </td>
            <td>
                <?php if($containers['second_payment'] !== '0.00'){ ?>
                    <strong class="text-success"><?= thousand_seperator($containers['first_payment']) ?> €</strong>
                <?php }else{ echo '0,00'; } ?>
            </td>
            <td>
                <?php if(($containers['total_payment'] - $containers['first_payment'] - $containers['second_payment']) != '0'){ ?>
                    <strong class="text-danger"><?= thousand_seperator($containers['total_payment'] - $containers['first_payment'] - $containers['second_payment']) ?> €</strong>
                <?php }else{ echo '0,00'; } ?>
            </td>
            <td>
                <?php if($containers['total_payment'] !== 0.00){ ?>
                    <strong class="text-success"><?= thousand_seperator($containers['first_payment']) ?> €</strong>
                <?php }else{ echo $containers['total_payment']; } ?>
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>



<?php include VIEW . '/default/footer.php'; ?>



