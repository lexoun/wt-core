<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

if (isset($_REQUEST['year'])) { $year = $_REQUEST['year'];}
if (isset($_REQUEST['month'])) { $month = $_REQUEST['month'];}
if (isset($_REQUEST['choosen_date'])) { $choosen_date = $_REQUEST['choosen_date'];}
if (isset($_REQUEST['day'])) { $day = $_REQUEST['day'];}
if (isset($_REQUEST['date_range'])) { $dateRange = $_REQUEST['date_range'];}

$pagetitle = "Statistika follow-upů";

include VIEW . '/default/header.php';


$query = '';

if(isset($dateRange)){

    $from = mb_substr($_REQUEST['date_range'], 0, 10);
    $to = substr($_REQUEST['date_range'],-10);

    $fromFormated = date('Y-m-d', strtotime(str_replace('.', '-', $from)));
    $toFormated = date('Y-m-d', strtotime(str_replace('.', '-', $to)));

    $query .= ' AND CAST(date_time as date) >= "'.$fromFormated.'" AND CAST(date_time as date) <= "'.$toFormated.'"';
}

if(isset($year)){
    $query .= ' AND YEAR(date_time) = '.$year;
}

if(isset($month)){
    $query .= ' AND MONTH(date_time) = '.$month;
}

if(isset($choosen_date)){
    $query .= ' AND DATE(date_time) = "'.$choosen_date.'"';
}

if(isset($day)){
    $query .= ' AND WEEKDAY(date_time) = "'.$day.'"';
}

$getAdmins = $mysqli->query("SELECT *
FROM demands
WHERE role = 'salesman' or role = 'salesman-technician'")or die($mysqli->error);


$allAdmins = [] ;

while($singleAdmin = mysqli_fetch_assoc($getAdmins)) {

    $miniAdmin = [];

    $miniAdmin['id'] = $singleAdmin['id'];
    $miniAdmin['user_name'] = $singleAdmin['user_name'];

    $followUpsQuery = $mysqli->query("SELECT d.type, COUNT(*) as total
FROM demands_mails_history d LEFT JOIN mails_recievers r ON r.type_id = d.id
WHERE r.admin_id = '" . $singleAdmin['id'] . "' AND r.reciever_type = 'performer' AND r.type = 'follow_up' $query GROUP BY d.type") or die($mysqli->error);

    while ($followUpCount = mysqli_fetch_assoc($followUpsQuery)) {

        $miniAdmin['follow_ups'][$followUpCount['type']] = $followUpCount['total'];

    }


    $allAdmins[] = $miniAdmin;


}


$days = ['Pondělí' => 0, 'Úterý' => 1, 'Středa' => 2, 'Čtvrtek' => 3, 'Pátek' => 4, 'Sobota' => 5, 'Neděle' => 6];


function filtrationLink(array $removed = [], bool $hasPrev = false): string
{

    global $_REQUEST;

    $link = '';
    foreach ($_REQUEST as $key => $request) {

        if (in_array($key, $removed, true)) {
            continue;
        }

        if (empty($link) && !$hasPrev) {

            $link .= '?' . $key . '=' . $request;

        } else {

            $link .= '&' . $key . '=' . $request;

        }

    }

    return $link;

}

?>

<div class="row">
    <div class="col-md-8 col-sm-7">
        <h2><?= $pagetitle ?></h2>
    </div>

</div>

<div class="col-md-12 well" style="border-color: #ebebeb; margin-top: 8px;background-color: #fbfbfb;">

    <div class="col-sm-6">
        <div class="btn-group" style="text-align: left; float: left;">

            <form role="form" method="get" class="form-horizontal form-groups-bordered validate" action="statistika-followupu" enctype="multipart/form-data" novalidate="novalidate">
                <input class="form-control" type="text" name="date_range" style="height: 41px; width: 180px; margin-right: 10px; float: left;" value="<?= $_REQUEST['date_range'] ?? '' ?>"/>

                <button type="submit" style="padding: 10px 18px 10px 50px; height: 36px;" class="btn btn-blue btn-icon icon-left">
                    Zvolit datum
                    <i class="fa fa-search" style="     padding: 10px 12px;"></i>
                </button>
            </form>



            <style>
                .ranges { display: none; }
            </style>
            <script>
                $(function() {
                    $('input[name="date_range"]').daterangepicker({
                        autoApply: true,
                        alwaysShowCalendars: true,
                        "locale": {
                            "format": "DD.MM.YYYY",
                            "separator": " - ",
                            "applyLabel": "Použít",
                            "cancelLabel": "Zrušit",
                            "fromLabel": "Od",
                            "toLabel": "Do",
                            "customRangeLabel": "Custom",
                            "weekLabel": "W",
                            "daysOfWeek": [
                                "Ne",
                                "Po",
                                "Út",
                                "St",
                                "Čt",
                                "Pá",
                                "So"
                            ],
                            "monthNames": [
                                "Leden",
                                "Únor",
                                "Březen",
                                "Duben",
                                "Květen",
                                "Červen",
                                "Červenec",
                                "Srpen",
                                "Září",
                                "Říjen",
                                "Listopad",
                                "Prosinec"
                            ],
                            "firstDay": 1
                        },
                    }, function(start, end, label) {
                        console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
                    });
                });
            </script>

        </div>
    </div>


    <div class="col-sm-6">
        <div class="btn-group" style="text-align: left; float: right;">
            Rok
            <a href="statistika-followupu<?= filtrationLink(['year', 'month']) ?>">
                <label class="btn btn-sm <?php if (!isset($year)) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                    Vše
                </label>
            </a>
            <?php
            $range = range('2014', date('Y'));
            foreach($range as $yearLoop){ ?>
                <a href="?year=<?php echo $yearLoop.filtrationLink(['year'],true)?>">
                    <label class="btn btn-sm <?php if (isset($year) && $year == $yearLoop) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                        <?= $yearLoop ?>
                    </label>
                </a>
            <?php } ?>
        </div>

        <?php if(!empty($year)){ ?>
            <div class="btn-group" style="text-align: left; float: right; margin-top: 10px;">
                Měsíc
                <a href="statistika-followupu<?= filtrationLink(['month']) ?>">
                    <label class="btn btn-sm <?php if (!isset($month)) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                        Vše
                    </label>
                </a>
                <?php

                    for($i=1;$i<13;$i++){ ?>
                        <a href="?month=<?php echo $i.filtrationLink(['month'],true)?>">
                            <label class="btn btn-sm <?php if (isset($month) && $month == $i) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                                <?= $i ?>
                            </label>
                        </a>
                    <?php
                        if(date("Y") == $year && date("n") == $i){ break; }
                    }
                ?>
            </div>
        <?php } ?>

        <?php if(!empty($year)){ ?>
            <div class="btn-group" style="text-align: left; float: right; margin-top: 10px;">
                Den
                <a href="statistika-followupu<?= filtrationLink(['day']) ?>">
                    <label class="btn btn-sm <?php if (!isset($day)) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                        Vše
                    </label>
                </a>
                <?php

                foreach($days as $key => $val){ ?>
                    <a href="?day=<?php echo $val.filtrationLink(['day'],true)?>">
                        <label class="btn btn-sm <?php if (isset($day) && $day == $val) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
                            <?= $key ?>
                        </label>
                    </a>
                    <?php
                }
                ?>
            </div>
        <?php } ?>
    </div>

    <div class="clear"></div>
</div>

<table class="table table-bordered table-striped datatable dataTable" id="table-2" aria-describedby="table-2_info">
    <thead>

    <tr>
        <td>Prodávající</td>
        <td>Návštěva - plánovaná</td>
        <td>Návštěva - neplánovaná</td>
        <td>Telefonát</td>
        <td>Nabídka</td>
        <td>Mailing</td>
        <td>Zkouška vířivky</td>
    </tr>

    </thead>

    <tbody>
    <?php

    foreach($allAdmins as $data){

        ?>
        <tr>
            <td>
                <a href="/admin/pages/demands/zobrazit-poptavku?id=<?= $data['id'] ?>">
                    <?= $data['user_name'] ?>
                </a>
            </td>
            <td>
                <?= !empty($data['follow_ups']['Návštěva - plánovaná']) ? $data['follow_ups']['Návštěva - plánovaná'] : '-' ?>
            </td>
            <td>
                <?= !empty($data['follow_ups']['Návštěva - neplánovaná']) ? $data['follow_ups']['Návštěva - neplánovaná'] : '-' ?>
            </td>
            <td>
                <?= !empty($data['follow_ups']['Telefonát']) ? $data['follow_ups']['Telefonát'] : '-' ?>
            </td>
            <td>
                <?= !empty($data['follow_ups']['Nabídka']) ? $data['follow_ups']['Nabídka'] : '-' ?>
            </td>
            <td>
                <?= !empty($data['follow_ups']['Mailing']) ? $data['follow_ups']['Mailing'] : '-' ?>
            </td>
            <td>
                <?= !empty($data['follow_ups']['Zkouška vířivky']) ? $data['follow_ups']['Zkouška vířivky'] : '-' ?>
            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<?php include VIEW . '/default/footer.php'; ?>



