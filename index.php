<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
};
include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/config/config.php';

if ($client['dimension'] !== '') {
    include_once INCLUDES . '/googlelogin.php';
}

include_once INCLUDES . '/functions.php';

$categorytitle = 'Přehled';
$pagetitle = 'Přehled';

$adminsquery = $mysqli->query("SELECT id, user_name, role FROM demands WHERE role != 'client' AND active = 1");

if (isset($client['id']) && (($client['id'] === '2158') || ($client['id'] === '12955')) && !isset($_REQUEST['show'])) {

    $show = 'all';

} elseif (isset($_REQUEST['show'])) {

    $show = $_REQUEST['show'];

} else {

    $show = $client['id'];

}

if (isset($_REQUEST['calendar'])) {

    $mysqli->query("UPDATE demands SET technical = '" . $_REQUEST['calendar'] . "' WHERE id = '" . $client['id'] . "'");

    $client['technical'] = $_REQUEST['calendar'];

}

if (isset($_REQUEST['realization']) && $_REQUEST['realization'] === 'add') {

    $getclientquery = $mysqli->query("SELECT * FROM demands WHERE id = '" . $_POST['search'] . "'") or die('bNeexistuje');
    $getclient = mysqli_fetch_array($getclientquery);

    if (isset($getclient['customer']) && $getclient['customer'] === '3' && $_POST['customer'] === '1') {

        $type = 'realization_hottub';

        $clientsquery = $mysqli->query("UPDATE demands SET confirmed = '" . $_POST['confirmed'] . "', realization = '" . $_POST['realizationdate'] . "', realizationtime = '" . $_POST['realizationtime'] . "', realtodate = '" . $_POST['realtodate'] . "', realtotime = '" . $_POST['realtotime'] . "', area = '" . $_POST['area'] . "' WHERE id = '" . $_POST['search'] . "'") or die('bNeexistuje');

    } elseif (isset($getclient['customer']) && $getclient['customer'] === '3' && $_POST['customer'] === '0') {

        $type = 'realization_sauna';

        $secondRealQuery = $mysqli->query("SELECT * FROM demands_double_realization WHERE demand_id = '" . $_POST['search'] . "'");

        $gcalendars = mysqli_fetch_array($secondRealQuery);
        $gcalendar = $gcalendars['gcalendar'];

        if (mysqli_num_rows($secondRealQuery) > 0) {

            $update = $mysqli->query("UPDATE demands_double_realization SET confirmed = '" . $_POST['confirmed'] . "', startdate = '" . $_POST['realizationdate'] . "', starttime = '" . $_POST['realizationtime'] . "', enddate = '" . $_POST['realtodate'] . "', endtime = '" . $_POST['realtotime'] . "', area = '" . $_POST['area'] . "'  WHERE demand_id = '" . $_POST['search'] . "'");

        } else {

            $insert = $mysqli->query("INSERT INTO demands_double_realization (confirmed,startdate, starttime, enddate, endtime, demand_id, area) VALUES ('" . $_POST['confirmed'] . "','" . $_POST['realizationdate'] . "', '" . $_POST['realizationtime'] . "', '" . $_POST['realtodate'] . "', '" . $_POST['realtotime'] . "', '" . $_POST['search'] . "', '" . $_POST['area'] . "')");

        }

    } else {

        if($_POST['customer'] == '0'){ $type = 'realization_sauna'; }else{ $type = 'realization_hottub'; }

        $clientsquery = $mysqli->query("UPDATE demands SET confirmed = '" . $_POST['confirmed'] . "', realization = '" . $_POST['realizationdate'] . "', realizationtime = '" . $_POST['realizationtime'] . "', realtodate = '" . $_POST['realtodate'] . "', realtotime = '" . $_POST['realtotime'] . "', area = '" . $_POST['area'] . "' WHERE id = '" . $_POST['search'] . "'") or die('bNeexistuje');

    }

    $mysqli->query("DELETE FROM mails_recievers WHERE type_id = '" . $getclient['id'] . "' AND type = '".$type."'") or die($mysqli->error);


    if(!empty(($_POST['performer']))){ $performersArray = array_filter($_POST['performer']); }else{ $performersArray[] = ''; }
    if(!empty(($_POST['observer']))){ $observersArray = array_filter($_POST['observer']); }else{ $observersArray[] = ''; }

    if (!empty($performersArray) || !empty($observersArray)) {

        recievers($performersArray, $observersArray, $type, $_POST['search']);

    }


    $id = $_POST['search'];
    saveCalendarEvent($id, 'realization');

    if (isset($_POST['send_email']) && $_POST['send_email'] == 'yes') {

        include CONTROLLERS . '/mails/realization.php';

    }

    header('location: https://www.wellnesstrade.cz/admin?success=realization_add');
    exit;
}

if (isset($_REQUEST['event']) && $_REQUEST['event'] === 'add') {

    if (isset($_POST['endusdate']) && $_POST['endusdate'] == '' && isset($_POST['endustime']) && $_POST['endustime'] != '') {

        $konecdate = $_POST['realizationdate'];

    } elseif (isset($_POST['endusdate']) && $_POST['endusdate'] != '' && isset($_POST['endustime']) && $_POST['endustime'] != '') {

        $konecdate = $_POST['endusdate'];

    } elseif (isset($_POST['endusdate']) && $_POST['endusdate'] != '' && isset($_POST['endustime']) && $_POST['endustime'] == '') {

        $konecdate = $_POST['endusdate'];

    }

    $mysqli->query("INSERT INTO dashboard_texts (admin_id, demand_id, title, popis, date, time, enddate, endtime, freq, count, rec_interval) values ('" . $client['id'] . "','" . $_POST['demandus'] . "','" . $_POST['title'] . "', '" . $_POST['text'] . "','" . $_POST['realizationdate'] . "','" . $_POST['realizationtime'] . "','$konecdate','" . $_POST['endustime'] . "', '" . $_POST['freq'] . "', '" . $_POST['count'] . "', '" . $_POST['rec_interval'] . "')") or die($mysqli->error);

    $id = $mysqli->insert_id;

    if(!empty(($_POST['performer']))){ $performersArray = array_filter($_POST['performer']); }else{ $performersArray[] = ''; }
    if(!empty(($_POST['observer']))){ $observersArray = array_filter($_POST['observer']); }else{ $observersArray[] = ''; }

    if (!empty($performersArray) || !empty($observersArray)) {

        recievers($performersArray, $observersArray, 'event', $id);

    }

    saveCalendarEvent($id, 'event');

    header('location: https://www.wellnesstrade.cz/admin?success=event_add');
    exit;
}


$virivky = array('capri', 'dreamline', 'eden', 'tahiti', 'tonga', 'trinidad');

$sauny = array('tiny', 'cavalir', 'home', 'cube', 'charm', 'charisma', 'exclusive', 'lora', 'mona', 'deluxe', 'grand');

if (empty($_REQUEST['show_type'])) {

    $show_type = 'all';

} else {

    $show_type = $_REQUEST['show_type'];

}

if (empty($_REQUEST['reciever_type'])) {

    $reciever_type = 'all';

} else {

    $reciever_type = $_REQUEST['reciever_type'];

}



include VIEW . '/default/header.php';

?>

<link href='https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css' rel='stylesheet'/>

<!-- <script src='<?= $home ?>/admin/assets/js/fullcalendar/core/main.css'></script> -->

<link href='<?= $home ?>/admin/assets/css/fullcalendar-new.css' rel='stylesheet'/>
<link href='<?= $home ?>/admin/assets/css/fullcalendar.print.css' rel='stylesheet' media='print'/>
<script src='<?= $home ?>/admin/assets/lib/moment.min.js'></script>
<script src='<?= $home ?>/admin/assets/lib/jquery.min.js'></script>


<?php

if ($client['technical'] == 0) {

    ?>
    <link href='<?= $home ?>/admin/assets/css/calendar1.css' rel='stylesheet'/>
    <?php

} elseif ($client['technical'] == 1) {

    ?>
    <link href='<?= $home ?>/admin/assets/css/calendar2.css' rel='stylesheet'/>
    <?php

} elseif ($client['technical'] == 2) {

    ?>
    <link href='<?= $home ?>/admin/assets/css/calendar3.css' rel='stylesheet'/>
    <?php

}

?>


<!-- <script src='<?= $home ?>/admin/assets/js/fullcalendar.min.js'></script> -->
<!-- <script src='<?= $home ?>/admin/assets/lang/cs.js'></script> -->


<link href='<?= $home ?>/admin/assets/js/fullcalendar/core/main.min.css' rel='stylesheet'/>


<link href='<?= $home ?>/admin/assets/js/fullcalendar/daygrid/main.min.css' rel='stylesheet'/>

<link href='<?= $home ?>/admin/assets/js/fullcalendar/timegrid/main.min.css' rel='stylesheet'/>


<link href='<?= $home ?>/admin/assets/js/fullcalendar/timeline/main.min.css' rel='stylesheet'/>


<link href='<?= $home ?>/admin/assets/js/fullcalendar/resource-timeline/main.min.css' rel='stylesheet'/>


<script src='<?= $home ?>/admin/assets/js/fullcalendar/core/main.js'></script>
<script src='<?= $home ?>/admin/assets/js/fullcalendar/daygrid/main.js'></script>
<script src='<?= $home ?>/admin/assets/js/fullcalendar/timegrid/main.min.js'></script>
<script src='<?= $home ?>/admin/assets/js/fullcalendar/timeline/main.min.js'></script>
<script src='<?= $home ?>/admin/assets/js/fullcalendar/google-calendar/main.js'></script>

<script src='<?= $home ?>/admin/assets/js/fullcalendar/interaction/main.js'></script>

<script src='<?= $home ?>/admin/assets/js/fullcalendar/resource-common/main.min.js'></script>

<script src='<?= $home ?>/admin/assets/js/fullcalendar/resource-daygrid/main.min.js'></script>

<script src='<?= $home ?>/admin/assets/js/fullcalendar/resource-timegrid/main.min.js'></script>
<script src='<?= $home ?>/admin/assets/js/fullcalendar/resource-timeline/main.min.js'></script>

<script src='<?= $home ?>/admin/assets/js/fullcalendar/core/locales/cs.js'></script>

<script src='https://unpkg.com/popper.js@1.16.0/dist/umd/popper.min.js'></script>
<script src='https://unpkg.com/tooltip.js@1.3.3/dist/umd/tooltip.min.js'></script>

<script src="https://apis.google.com/js/client.js?onload=handleClientLoad"></script>

<!-- <script src='<?= $home ?>/admin/assets/js/gcal.min.js'></script> -->

    <?php

    $techniciansQuery = $mysqli->query("SELECT id, user_name FROM demands WHERE role = 'technician' AND active = 1");
    while ($technicianSingle = mysqli_fetch_array($techniciansQuery)) {

        $techniciansArray[] = array(
            'id' => $technicianSingle['id'],
            'title' => $technicianSingle['user_name']
        );

    }

    $technicians = json_encode($techniciansArray);
    ?>


    <style>


        .tooltip-inner {
            background: none !important;
            color: #484848 !important;
            padding: 0 !important;
        }

        .tooltip hr {
            margin: 10px 0 10px;
        }


        .tooltip {
            position: absolute;
            z-index: 999999;
            min-width: 160px;
            /* transition: all .250s cubic-bezier(0, 0, 0.2, 1); */
            background: white;
            color: #484848;
            border: 1px solid #cecece;
            border-radius: 3px;
            box-shadow: 0 2px 1px #bcbcbc;
            z-index: 4;
            font-weight: 500;
            padding: 14px;
            text-align: center;
            opacity: 1 !important;

        }

        .tooltip[x-placement^="bottom"] {
            margin-top: 7px;
        }

        .tooltip[x-placement^="bottom"] .tooltip-arrow {
            top: -8px;
            left: 50% !important;
            transform: translate3d(-50%, 0, 0);
            border-width: 0 8px 8px 8px;
            border-color: transparent transparent white transparent;
            -webkit-filter: drop-shadow(1px 2px 1px #bcbcbc);
            filter: drop-shadow(0 -1px 0 #bcbcbc);
        }

        .tooltip[x-placement^="top"] {
            margin-bottom: 7px;
        }

        .tooltip[x-placement^="top"] .tooltip-arrow {
            bottom: -8px;
            left: 50% !important;
            transform: translate3d(-50%, 0, 0) rotate(180deg);
            border-width: 0 8px 8px 8px;
            border-color: transparent transparent white transparent;
            -webkit-filter: drop-shadow(1px 2px 1px #bcbcbc);
            filter: drop-shadow(0 -1px 0 #bcbcbc);
        }

        .fc-day-grid-event.strike { text-decoration: line-through; }
    </style>


    <script>

        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');
            var show = '<?= $show ?>';
            var showType = '<?= $show_type ?>';
            var recieverType = '<?= $reciever_type ?>';

            var calendar = new FullCalendar.Calendar(calendarEl, {


                plugins: ['dayGrid', 'timeGrid', 'googleCalendar', 'resourceDayGrid', 'resourceTimeline', 'interaction'],
                defaultView: 'dayGridMonth',

                schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
                locale: 'cs',
                timeFormat: 'H:mm',
                datesAboveResources: true,
                height: 'auto',
                contentHeight: 'auto',
                editable: false,
                eventLimit: false,

                resourceAreaWidth: '100px',
                slotWidth: '120',
                views: {
                    resourceTimeLineWT: {
                        type: 'resourceTimelineMonth',
                        buttonText: 'Technici - měsíc'
                    },
                    dayGridWT: {
                        type: 'resourceDayGrid',
                        buttonText: 'Technici - den'
                    }
                },
                header: {
                    left: 'today prev,next',
                    center: 'title',
                    right: 'timeGridWeek, dayGridMonth, resourceTimeLineWT, dayGridWT'
                },

                resourceLabelText: 'Technici',
                googleCalendarApiKey: 'AIzaSyCK8u0dLAAbDKaHiWXJrbofiTOetOYY2f8',
                resources: <?= $technicians ?>,

                eventSources: [{
                    url: '/admin/controllers/calendars/show-realizations-planned',
                    method: 'GET',
                    extraParams: {
                        show: show,
                        showType: showType,
                        recieverType: recieverType,
                    }
                }, {
                    url: '/admin/controllers/calendars/show-realizations-processed',
                    method: 'GET',
                    extraParams: {
                        show: show,
                        showType: showType,
                        recieverType: recieverType,
                    }
                }, {
                    url: '/admin/controllers/calendars/show-realizations-confirmed',
                    method: 'GET',
                    extraParams: {
                        show: show,
                        showType: showType,
                        recieverType: recieverType,
                    }
                }, {
                    url: '/admin/controllers/calendars/show-follow-ups',
                    method: 'GET',
                    extraParams: {
                        show: show,
                        showType: showType,
                        recieverType: recieverType,
                    }
                }, {
                    url: '/admin/controllers/calendars/show-events',
                    method: 'GET',
                    extraParams: {
                        show: show,
                        showType: showType,
                        recieverType: recieverType,
                    }
                }, {
                    url: '/admin/controllers/calendars/show-tasks',
                    method: 'GET',
                    extraParams: {
                        show: show,
                        showType: showType,
                        recieverType: recieverType,
                    }
                }, {
                    url: '/admin/controllers/calendars/show-services',
                    method: 'GET',
                    extraParams: {
                        show: show,
                        showType: showType,
                        recieverType: recieverType,
                    }
                }, {
                    googleCalendarId: 'cs.czech#holiday@group.v.calendar.google.com',
                    className: 'holiday'
                }

                ],

                eventRender: function (info) {
                    // console.log(info.event.extendedProps.description);
                    var tooltip = new Tooltip(info.el, {
                        title: info.event.extendedProps.description,
                        placement: 'top',
                        trigger: 'hover',
                        container: '.page-container',
                        html: true,
                    });
                    // console.log(tooltip);
                    // $('[data-toggle="tooltip"]').tooltip('hide');
                },


                dateClick: function (info) {


                    $("#add-thing, #udalost-show, #realizace-show, #ukol-show, #choosedvirivka, #choosedsauna")
                        .hide("slow");
                    $("#thingerino, #cancel-add-thing, #choosecustomer").show("slow");

                    $('#datum1, #datum1pridatsem, #datum2, #datum2pridatsem, #datum3, #datum4, #datum4pridatsem, #datum5')
                        .val(info.dateStr);
                    $('#datum1, #datum1pridatsem, #datum2, #datum2pridatsem, #datum3, #datum4, #datum4pridatsem, #datum5')
                        .datepicker('update');

                    $(".fc-day").removeAttr('style');

                    info.dayEl.style.backgroundColor = '#F5F5F5';

                },


            });


            calendar.render();

            setTimeout(function () {
                calendar.updateSize();
                $('.calendar-loader').remove();
            }, 450);

        });

    </script>


    <?php /* events: <?php if (isset($rows) && $rows != '') { echo json_encode($rows); } ?> */ ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {

            $("#datum1").change(function () {


                var value = $.trim($("#datum1pridatsem").val());
                if (value.length === 0) {
                    $('#datum1pridatsem').val(this.value);

                    $('#datum1pridatsem').datepicker('update');

                }
            });

            $("#datum2").change(function () {

                var value = $.trim($("#datum2pridatsem").val());
                if (value.length === 0) {
                    $('#datum2pridatsem').val(this.value);

                    $('#datum2pridatsem').datepicker('update');


                }
            });

            $("#datum4").change(function () {

                var value = $.trim($("#datum4pridatsem").val());
                if (value.length === 0) {
                    $('#datum4pridatsem').val(this.value);

                    $('#datum4pridatsem').datepicker('update');

                }
            });


            $('#add-thing').click(function () {
                $("#add-thing").hide("fast");
                $("#cancel-add-thing").show("slow");
                $("#thingerino").show("slow");
            });

            $('#cancel-add-thing').click(function () {

                $("#cancel-add-thing").hide("fast");
                $("#add-thing").show("slow");
                $("#thingerino").hide("slow");
                $("#udalost-show").hide("slow");
                $("#ukol-show").hide("slow");
                $("#realizace-show").hide("slow");


                $("#choosecustomer").show("slow");
                $("#choosedvirivka").hide("slow");
                $("#choosedsauna").hide("slow");

            });

            $('#udalost').click(function () {
                $("#thingerino").hide("slow");
                $("#udalost-show").show("slow");
            });

            $('#ukol').click(function () {
                $("#thingerino").hide("slow");
                $("#ukol-show").show("slow");
            });

            $('#realizace').click(function () {
                $("#thingerino").hide("slow");
                $("#realizace-show").show("slow");
            });


            $('#virivka').click(function () {
                $("#choosecustomer").hide("slow");
                $("#choosedvirivka").show("slow");
            });

            $('#sauna').click(function () {
                $("#choosecustomer").hide("slow");
                $("#choosedsauna").show("slow");
            });

        });
    </script>

        <?php if ($access_calendar) { ?>

            <div id="thingerino" class="row" style="margin-bottom: 30px; display: none;">
                <div class="col-md-12">

                    <div id="udalost" class="col-sm-4" style="cursor:pointer;">
                        <div class="tile-stats tile-blue">
                            <div class="icon" style="top: 17px;"><i style="font-size: 60px;"
                                                                    class="entypo-calendar"></i></div>
                            <div class="num"></div>
                            <h3>Událost</h3>
                            <p></p>
                        </div>
                    </div>
                    <div id="realizace" class="col-sm-4" style="cursor:pointer;">
                        <div class="tile-stats tile-green">
                            <div class="icon" style="top: 20px !important;"><i style="font-size: 60px;"
                                                                               class="fa fa-truck"></i></div>
                            <div class="num"></div>
                            <h3>Realizace</h3>
                            <p></p>
                        </div>
                    </div>
                    <div id="ukol" class="col-sm-4" style="cursor:pointer;">
                        <div style="background: #e7353b;" class="tile-stats tile-red">
                            <div class="icon" style="top: 17px;"><i style="font-size: 60px;" class="entypo-list"></i>
                            </div>
                            <div class="num"></div>
                            <h3>Úkol</h3>
                            <p></p>
                        </div>
                    </div>


                </div>
            </div>

            <script type="text/javascript">
                jQuery(document).ready(function ($) {


                    $('.customerClass').click(function () {
                        if ($("input:radio[class='saunaradio']").is(":checked")) {


                            $('.virivkens').hide("slow");
                            $('.saunkens').show("slow");
                        }
                        if ($("input:radio[class='virivkaradio']").is(":checked")) {


                            $('.saunkens').hide("slow");
                            $('.virivkens').show("slow");
                        }
                    });


                    $('.choosio').click(function () {
                        if ($("input:radio[id='not_choosed']").is(":checked")) {


                            $('#demands_who').hide("slow");
                            $('#clients_who').hide("slow");
                        }

                        if ($("input:radio[id='choosed_demand']").is(":checked")) {

                            $('#clients_who').hide("slow");
                            $('#demands_who').show("slow");

                        }

                        if ($("input:radio[id='choosed_client']").is(":checked")) {


                            $('#demands_who').hide("slow");
                            $('#clients_who').show("slow");
                        }


                    });


                    $('.radiodegreeswitch').on('switch-change', function () {

                        if ($('.radiodegree').prop('checked')) {

                            $('.degree').show("slow");
                            $('.degree').focus();

                        } else if (!$('.radiodegree').prop('checked')) {


                            $('.degree').hide("slow");
                        }

                    });


                });
            </script>


            <div id="udalost-show" class="row" style="margin-bottom: 30px; display: none;">
                <div class="well" style="width: 900px; float:inherit; margin: 0 auto 30px;">
                    <h2 class="specialborderbottom" style="margin-bottom: 20px;">Nová událost</h2>
                    <form id="udform" autocomplete="off" class="validate" role="form" method="post"
                          enctype='multipart/form-data' action="../admin/index?event=add">

                        <div class="form-group" style="float:left;  width: 48%; margin-right: 2%;">
                            <input type="text" name="title" placeholder="Nadpis" class="form-control" id="udalost-title"
                                   value="" style="  float: left;" data-validate="required" data-message-required=" ">

                        </div>
                        <div class="form-group" style="float:left; width: 24%; margin-right: 2%;">
                            <div class="date-and-time">
                                <input id="datum1" type="text" class="form-control datepicker" name="realizationdate"
                                       data-format="yyyy-mm-dd" placeholder="Datum začátku" data-validate="required"
                                       data-message-required="Musíte zadat datum události.">
                                <input type="text" class="form-control timepicker" name="realizationtime"
                                       data-template="dropdown" data-show-seconds="false" data-default-time="00-00"
                                       data-show-meridian="false" data-minute-step="5" placeholder="Čas"/>
                            </div>
                        </div>
                        <div class="form-group" style="float:left; width: 24%;">
                            <div class="date-and-time">
                                <input id="datum1pridatsem" type="text" class="form-control datepicker" name="endusdate"
                                       data-format="yyyy-mm-dd" placeholder="Datum konce">
                                <input type="text" class="form-control timepicker" name="endustime"
                                       data-template="dropdown" data-show-seconds="false" data-default-time="00-00"
                                       data-show-meridian="false" data-minute-step="5" placeholder="Čas"/>
                            </div>
                        </div>


                        <div class="form-group" style="margin: 10px 0; float: left;">


                            <div class="col-sm-2">
                                Opakování
                            </div>
                            <label class="col-sm-1"><input type="radio" name="freq" value="" checked> Žádné</label>
                            <label class="col-sm-1"><input type="radio" name="freq" value="DAILY"> Denně</label>
                            <label class="col-sm-1"><input type="radio" name="freq" value="WEEKLY"> Týdně</label>
                            <label class="col-sm-1"><input type="radio" name="freq" value="MONTHLY"> Měsíčně</label>

                            <div class="col-sm-3">
                                Interval opakování:
                            </div>
                            <div class="col-sm-1">
                                <input type="number" name="rec_interval" value="1">
                            </div>

                            <div class="col-sm-3">
                                Počet opakování:
                            </div>
                            <div class="col-sm-1">
                                <input type="number" name="count" value="0">
                            </div>



                        </div>




                        <div class="form-group well admins_well"
                             style="background-color: #FFFFFF; padding: 12px 0px 7px; float: left; width: 49.5%;margin-right: 0.5%; margin-bottom: 0;">

                            <h4
                                style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">
                                Proveditelé</h4>

                            <?php mysqli_data_seek($adminsquery, 0);

                            while ($admins = mysqli_fetch_array($adminsquery)) { ?>

                                <div class="col-sm-4">

                                    <input id="admin-<?= $admins['id'] ?>-event-performer" name="performer[]"
                                           value="<?= $admins['id'] ?>" type="checkbox">
                                    <label for="admin-<?= $admins['id'] ?>-event-performer"
                                           style="padding-left: 4px; cursor: pointer;"><?= $admins['user_name'] ?></label>

                                </div>

                            <?php } ?>


                        </div>
                        <div class="form-group well admins_well"
                             style="background-color: #FFFFFF; padding: 12px 0px 7px; float: left; width: 49.5%; margin-left: 0.5%; margin-bottom: 0;">

                            <h4
                                style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">
                                Informovaní</h4>

                            <?php mysqli_data_seek( $adminsquery, 0 );

                            while($admins = mysqli_fetch_array($adminsquery)){ ?>

                                <div class="col-sm-4">

                                    <input id="admin-<?= $admins['id'] ?>-event-observer" name="observer[]"
                                           value="<?= $admins['id'] ?>" type="checkbox" <?php if($client['id'] == $admins['id']){ echo 'checked';}?>>
                                    <label for="admin-<?= $admins['id'] ?>-event-observer"
                                           style="padding-left: 4px; cursor: pointer;">
                                        <?= $admins['user_name'] ?></label>

                                </div>

                            <?php } ?>

                        </div>

                        <?php

//                        if ($client['id'] == 2126) {
//
//                            calendar_recievers_list('');
//
//                        }

                        ?>

                        <div class="form-group specialformus" style="float: left; width: 100%; margin-bottom: 0;">
                            <?php

                            $demandsq = $mysqli->query('SELECT user_name, id, customer FROM demands') or die($mysqli->error);
                            ?>
                            <select id="choosepoptavka" name="demandus" class="select2" data-allow-clear="true"
                                    data-placeholder="Přiřadit událost k poptávce..."
                                    style="width: 100% !important; margin: 10px 0 10px 0;">
                                <option></option>
                                <?php while ($dem = mysqli_fetch_array($demandsq)) { ?>
                                    <option value="<?= $dem['id'] ?>>"><?php if ($dem['customer'] == 0) {
                                        echo 'S - ';
                                    } elseif ($dem['customer'] == 1) {
                                        echo 'V - ';
                                    } else {
                                        echo 'SV - ';
                                    }
                                    echo $dem['user_name']; ?></option><?php } ?>
                            </select>
                        </div>

                        <div class="form-group button-demo" style="float:left; width: 100%; margin-bottom: 0;">
                            <textarea class="form-control autogrow" name="text" placeholder="Popis události..."
                                      style="overflow: hidden; margin-bottom: 8px; word-wrap: break-word; resize: horizontal; height: 80px;margin-top: 12px;width: 80%;float: left;"></textarea>
                            <button style="width: 18%; float:left;height: 80px;margin-top: 12px;margin-left: 2%;"
                                    type="submit" data-style="zoom-in" class="ladda-button btn btn-primary"><i
                                        style=" position: relative; font-size: 25px; right: 0; top: 0;"
                                        class="entypo-calendar"></i></button>


                        </div>

                    </form>
                    <div style="clear:both;"></div>
                </div>
            </div>

            <div id="realizace-show" class="row" style="margin-bottom: 30px; display: none;">


                <div class="col-md-12" id="choosecustomer">
                    <div class="well" style="display:block; margin: 50px auto 40px; width: 700px;">
                        <h2 class="specialborderbottom"
                            style="margin-bottom: 20px;padding-bottom: 18px;text-align:center;">Vyberte druh realizace
                        </h2>
                        <div id="virivka" class="col-sm-6" style="cursor:pointer;">
                            <div class="tile-stats tile-gray spsle"
                                 style="border: 1px solid #DDDDDD;    background: #FFFFFF;">
                                <div class="icon" style="top: 20px !important;"><i style="font-size: 60px;"
                                                                                   class="fa fa-spinner"></i></div>
                                <div class="num"></div>
                                <h3>Vířivka</h3>
                                <p></p>
                            </div>
                        </div>


                        <div id="sauna" class="col-sm-6" style="cursor:pointer;">
                            <div class="tile-stats tile-gray spsle"
                                 style="border: 1px solid #DDDDDD;    background: #FFFFFF;">
                                <div class="icon" style="top: 20px !important;"><i style="font-size: 60px;"
                                                                                   class="fa fa-fire"></i></div>
                                <div class="num"></div>
                                <h3>Sauna</h3>
                                <p></p>
                            </div>
                        </div>
                        <div style="clear:both;"></div>
                    </div>
                </div>

                <div id="choosedvirivka" style="display: none;">
                    <div class="well" style="width: 900px; float:inherit; margin: 0 auto 30px;">
                        <h2 class="specialborderbottom" style="margin-bottom: 20px;">Nová realizace vířivky</h2>
                        <form autocomplete="off" id="realform" class="validate" role="form" method="post"
                              enctype='multipart/form-data' action="../admin/index?realization=add">

                            <div class="form-group specialformus" style="width: 50%; float:left;">
                                <?php

                                $demandsq = $mysqli->query('SELECT user_name, id FROM demands WHERE status < 5 AND (customer = 1 OR customer = 3)') or die($mysqli->error);

                                ?>
                                <select id="choosepoptavka" name="search" class="select2" data-allow-clear="true"
                                        data-placeholder="Vyberte poptávku..." style="width: 100%; float:left;"
                                        data-validate="required" data-message-required="Musíte vybrat poptávku.">
                                    <option></option>
                                    <?php while ($dem = mysqli_fetch_array($demandsq)) { ?>
                                        <option
                                        value="<?= $dem['id'] ?>"><?= $dem['user_name'] ?></option><?php } ?>
                                </select>
                                <input name="customer" value="1" style="display: none;">
                            </div>
                            <div class="form-group" style="width: 50%; float:right; padding: 0 0 0 20px;">
                                <div
                                        style="background-color: #FFFFFF; border-radius: 4px; border: 1px solid #ebebeb; display: inline-block; width: 100%;">
                                    <div class="radio" style="float: left; margin-left: 10px;">
                                        <label>
                                            <input type="radio" name="confirmed" value="0" checked>Plánovaná
                                        </label>
                                    </div>
                                    <div class="radio" style="float: left;margin-top: 10px;margin-left: 18px;">
                                        <label>
                                            <input type="radio" name="confirmed" value="2">V řešení
                                        </label>
                                    </div>
                                    <div class="radio" style="float: left;margin-top: 10px;margin-left: 18px;">
                                        <label>
                                            <input type="radio" name="confirmed" value="1">Potvrzená
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group" style="width: 50%; float: left;">
                                <div class="date-and-time" style="width: 49%; float: left; margin-right: 2%;">
                                    <input id="datum2" type="text" class="form-control datepicker"
                                           name="realizationdate" data-format="yyyy-mm-dd" placeholder="Datum začátku"
                                           style="height: 41px;" data-validate="required"
                                           data-message-required="Musíte zadat datum realizace.">
                                    <input type="text" class="form-control timepicker" name="realizationtime"
                                           data-template="dropdown" data-show-seconds="false" data-default-time="00-00"
                                           data-show-meridian="false" data-minute-step="5" placeholder="Čas"
                                           style="height: 41px;"/>
                                </div>
                                <div class="date-and-time" style="width: 49%; float: left;">
                                    <input id="datum2pridatsem" type="text" class="form-control datepicker"
                                           name="realtodate" data-format="yyyy-mm-dd" placeholder="Datum konce"
                                           style="height: 41px;">
                                    <input type="text" class="form-control timepicker" name="realtotime"
                                           data-template="dropdown" data-show-seconds="false" data-default-time="00-00"
                                           data-show-meridian="false" data-minute-step="5" placeholder="Čas"
                                           style="height: 41px;"/>
                                </div>
                            </div>
                            <div class="form-group" style="width: 50%; float: left; padding-left: 20px;">
                                <div
                                        style="background-color: #FFFFFF; border-radius: 4px; border: 1px solid #ebebeb; display: inline-block; width: 100%;">
                                    <div class="radio" style="float: left; margin-left: 10px;">
                                        <label>
                                            <input type="radio" name="area" value="prague" checked>Praha
                                        </label>
                                    </div>
                                    <div class="radio" style="float: left;margin-top: 10px;margin-left: 18px;">
                                        <label>
                                            <input type="radio" name="area" value="brno">Brno
                                        </label>
                                    </div>
                                </div>

                            </div>

                            <div class="form-group well admins_well"
                                 style="background-color: #FFFFFF; padding: 12px 0px 7px; float: left; width: 49.5%;margin-right: 0.5%; margin-bottom: 0;">

                                <h4
                                    style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">
                                    Proveditelé</h4>

                                <?php mysqli_data_seek($adminsquery, 0);

                                while ($admins = mysqli_fetch_array($adminsquery)) { ?>

                                    <div class="col-sm-4">

                                        <input id="admin-<?= $admins['id'] ?>-realization-performer" name="performer[]"
                                               value="<?= $admins['id'] ?>" type="checkbox">
                                        <label for="admin-<?= $admins['id'] ?>-realization-performer"
                                               style="padding-left: 4px; cursor: pointer;"><?= $admins['user_name'] ?></label>

                                    </div>

                                <?php } ?>


                            </div>
                            <div class="form-group well admins_well"
                                 style="background-color: #FFFFFF; padding: 12px 0px 7px; float: left; width: 49.5%; margin-left: 0.5%; margin-bottom: 0;">

                                <h4 style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">Informovaní</h4>

                                <?php mysqli_data_seek( $adminsquery, 0 );

                                while($admins = mysqli_fetch_array($adminsquery)){ ?>

                                    <div class="col-sm-4">

                                        <input id="admin-<?= $admins['id'] ?>-realization-observer" name="observer[]"
                                               value="<?= $admins['id'] ?>" type="checkbox" <?php if($client['id'] == $admins['id'] || $admins['role'] == 'salesman-technician'){ echo 'checked';}?>>
                                        <label for="admin-<?= $admins['id'] ?>-realization-observer"
                                               style="padding-left: 4px; cursor: pointer; <?php if(!empty($client['id']) && $client['id'] == $admins['id']){ echo 'color: green !important;'; }?>">
                                            <?= $admins['user_name'] ?></label>

                                    </div>

                                <?php } ?>

                            </div>

                            <div class="form-group well" 
                                style="background-color: #FFFFFF; padding: 12px 0px 7px; float: left; width: 49.5%; margin-left: 0.5%; margin-bottom: 0;">
                                <label for="field-ta" class="col-sm-6 control-label">Odeslat mail klientovi</label>
                                <div class="col-sm-6">
                                    <div class="radio col-sm-6" style="margin: 0;">
                                        <label>
                                            <input type="radio" name="send_email" id="maile" value="yes">Ano
                                        </label>
                                    </div>
                                    <div class="radio col-sm-6" style="margin: 0;">
                                        <label>
                                            <input type="radio" name="send_email" id="maile" value="no" checked>Ne
                                        </label>
                                    </div>
                                </div>

                                <br><hr>

                                <div class="form-group">
                                    <label for="field-ta" class="col-sm-3 control-label" style="padding-right: 0;">Informace pro zákazníka</label>

                                    <div class="col-sm-8" style="padding-right: 0;">
                                        <textarea class="form-control" name="details" id="field-ta" rows="4" ></textarea>
                                    </div>
                                </div>
                            </div>


                            <span class="button-demo">
                                <button style="width: 100%; float:left; height:41px;font-size: 20px;margin-top: 10px;"
                                        type="submit" data-type="zoom-in" class="ladda-button btn btn-primary"><i
                                            style=" position: relative; right: 0; top: 0;"
                                            class="entypo-tools"></i></button>
                            </span>

                        </form>
                        <div style="clear:both;"></div>
                    </div>
                </div>

                <div id="choosedsauna" style="display: none;">
                    <div class="well" style="width: 900px; float:inherit; margin: 0 auto 30px;">
                        <h2 class="specialborderbottom" style="margin-bottom: 20px;">Nová realizace sauny</h2>
                        <form autocomplete="off" id="realform2" class="validate" role="form" method="post"
                              enctype='multipart/form-data' action="../admin/index?realization=add">

                            <div class="form-group specialformus" style="width: 50%; float: left;">
                                <?php

                                $demandsq = $mysqli->query('SELECT user_name, id FROM demands WHERE status < 5 AND (customer = 0 OR customer = 3)') or die($mysqli->error);

                                ?>
                                <select id="choosepoptavka" name="search" class="select2" data-allow-clear="true"
                                        data-placeholder="Vyberte poptávku..." style="width: 100%; float:left;"
                                        data-validate="required" data-message-required="Musíte vybrat poptávku.">
                                    <option></option>
                                    <?php while ($dem = mysqli_fetch_array($demandsq)) { ?>
                                        <option
                                        value="<?= $dem['id'] ?>"><?= $dem['user_name'] ?></option><?php } ?>
                                </select>
                                <input name="customer" value="0" style="display: none;">
                            </div>
                            <div class="form-group" style="width: 50%; float:right; padding: 0 0 0 20px;">

                                <div
                                        style="background-color: #FFFFFF; border-radius: 4px; border: 1px solid #ebebeb; display: inline-block; width: 100%;">
                                    <div class="radio" style="float: left; margin-left: 10px;">
                                        <label>
                                            <input type="radio" name="confirmed" value="0" checked>Plánovaná
                                        </label>
                                    </div>
                                    <div class="radio" style="float: left;margin-top: 10px;margin-left: 18px;">
                                        <label>
                                            <input type="radio" name="confirmed" value="2">V řešení
                                        </label>
                                    </div>
                                    <div class="radio" style="float: left;margin-top: 10px;margin-left: 18px;">
                                        <label>
                                            <input type="radio" name="confirmed" value="1">Potvrzená
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group" style="width: 50%; float: left;">
                                <div class="date-and-time" style="width: 49%; float: left; margin-right: 2%;">
                                    <input id="datum4" type="text" class="form-control datepicker"
                                           name="realizationdate" data-format="yyyy-mm-dd" placeholder="Datum začátku"
                                           style="height: 41px;" data-validate="required"
                                           data-message-required="Musíte zadat datum realizace.">
                                    <input type="text" class="form-control timepicker" name="realizationtime"
                                           data-template="dropdown" data-show-seconds="false" data-default-time="00-00"
                                           data-show-meridian="false" data-minute-step="5" placeholder="Čas"
                                           style="height: 41px;"/>
                                </div>
                                <div class="date-and-time" style="width: 49%; float: left;">
                                    <input id="datum4pridatsem" type="text" class="form-control datepicker"
                                           name="realtodate" data-format="yyyy-mm-dd" placeholder="Datum konce"
                                           style="height: 41px;">
                                    <input type="text" class="form-control timepicker" name="realtotime"
                                           data-template="dropdown" data-show-seconds="false" data-default-time="00-00"
                                           data-show-meridian="false" data-minute-step="5" placeholder="Čas"
                                           style="height: 41px;"/>
                                </div>
                            </div>
                            <div class="form-group" style="width: 50%; float: left; padding-left: 20px;">
                                <div
                                        style="background-color: #FFFFFF; border-radius: 4px; border: 1px solid #ebebeb; display: inline-block; width: 100%;">
                                    <div class="radio" style="float: left; margin-left: 10px;">
                                        <label>
                                            <input type="radio" name="area" value="prague" checked>Praha
                                        </label>
                                    </div>
                                    <div class="radio" style="float: left;margin-top: 10px;margin-left: 18px;">
                                        <label>
                                            <input type="radio" name="area" value="brno">Brno
                                        </label>
                                    </div>
                                </div>

                            </div>

                            <div class="form-group well admins_well"
                                 style="background-color: #FFFFFF; padding: 12px 0px 7px; float: left; width: 49.5%;margin-right: 0.5%; margin-bottom: 0;">

                                <h4
                                    style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">
                                    Proveditelé</h4>

                                <?php mysqli_data_seek($adminsquery, 0);

                                while ($admins = mysqli_fetch_array($adminsquery)) { ?>

                                    <div class="col-sm-4">

                                        <input id="admin-<?= $admins['id'] ?>-second-realization-performer" name="performer[]"
                                               value="<?= $admins['id'] ?>" type="checkbox">
                                        <label for="admin-<?= $admins['id'] ?>-second-realization-performer"
                                               style="padding-left: 4px; cursor: pointer;"><?= $admins['user_name'] ?></label>

                                    </div>

                                <?php } ?>


                            </div>
                            <div class="form-group well admins_well"
                                 style="background-color: #FFFFFF; padding: 12px 0px 7px; float: left; width: 49.5%; margin-left: 0.5%; margin-bottom: 0;">

                                <h4
                                    style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">
                                    Informovaní</h4>

                                <?php mysqli_data_seek( $adminsquery, 0 );

                                while($admins = mysqli_fetch_array($adminsquery)){ ?>

                                    <div class="col-sm-4">

                                        <input id="admin-<?= $admins['id'] ?>-second-realization-observer" name="observer[]"
                                               value="<?= $admins['id'] ?>" type="checkbox" <?php if($client['id'] == $admins['id'] || $admins['role'] == 'salesman-technician'){ echo 'checked';}?>>
                                        <label for="admin-<?= $admins['id'] ?>-second-realization-observer"
                                               style="padding-left: 4px; cursor: pointer; <?php if(!empty($client['id']) && $client['id'] == $admins['id']){ echo 'color: green !important;'; }?>">
                                            <?= $admins['user_name'] ?></label>

                                    </div>

                                <?php } ?>

                            </div>


                            <span class="button-demo">
                                <button style="width: 100%; float:left; height:41px;font-size: 20px;margin-top: 10px;"
                                        type="submit" data-type="zoom-in" class="ladda-button btn btn-primary"><i
                                            style=" position: relative; right: 0; top: 0;"
                                            class="entypo-tools"></i></button>
                            </span>

                        </form>
                        <div style="clear:both;"></div>
                    </div>
                </div>


            </div>

            <div id="ukol-show" class="row" style="display: none;">


                <?php

                $demquery = $mysqli->query('SELECT id, user_name FROM demands') or die($mysqli->error);
                ?>
                <div class="well" style="width: 900px; float:inherit; margin: 0 auto 50px;">
                    <h2 class="specialborderbottom" style="margin-bottom: 20px;">Nový úkol</h2>
                    <form autocomplete="off" class="validate" id="taskform" role="form" method="post"
                          enctype='multipart/form-data'
                          action="/admin/controllers/task-controller?task=add&redirect=index">

                        <div class="form-group" style="float:left; width: 100%;">
                            <input type="text" style="width: 64%; float: left; margin-right: 2%; margin-bottom: 8px;"
                                   name="title" placeholder="Název úkolu" class="form-control" id="field-1" value=""
                                   data-validate="required" data-message-required="Musíte zadat název úkolu.">

                            <input id="datum3" type="text" style="width: 22%; float: left; margin-bottom: 6px;"
                                   name="datum" class="form-control datepicker" data-format="yyyy-mm-dd"
                                   placeholder="Datum provedení" data-validate="required"
                                   data-message-required="Musíte zvolit datum úkolu.">
                            <input type="text" style="width: 12%" class="form-control timepicker" name="time"
                                   data-template="dropdown" data-show-seconds="false" data-default-time="00-00"
                                   data-show-meridian="false" data-minute-step="5" placeholder="Čas"/>
                        </div>


                        <div class="form-group well admins_well"
                             style="background-color: #FFFFFF; padding: 12px 0px 7px; float: left; width: 49.5%;margin-right: 0.5%; margin-bottom: 0;">

                            <h4 style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">Proveditelé</h4>

                            <?php mysqli_data_seek($adminsquery, 0);

                            while ($admins = mysqli_fetch_array($adminsquery)) { ?>

                                <div class="col-sm-4">

                                    <input id="admin-<?= $admins['id'] ?>-task-performer" name="performer[]"
                                           value="<?= $admins['id'] ?>" type="checkbox" required>
                                    <label for="admin-<?= $admins['id'] ?>-task-performer"
                                           style="padding-left: 4px; cursor: pointer;"><?= $admins['user_name'] ?></label>

                                </div>

                            <?php } ?>


                        </div>
                        <div class="form-group well admins_well"
                            style="background-color: #FFFFFF; padding: 12px 0px 7px; float: left; width: 49.5%; margin-left: 0.5%; margin-bottom: 0;">

                            <h4 style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">Informovaní</h4>

                            <?php mysqli_data_seek( $adminsquery, 0 );

    while($admins = mysqli_fetch_array($adminsquery)){ ?>

                            <div class="col-sm-4">

                                <input id="admin-<?= $admins['id'] ?>-task-observer" name="observer[]"
                                    value="<?= $admins['id'] ?>" type="checkbox" <?php if($client['id'] == $admins['id']){ echo 'checked';}?>>
                                <label for="admin-<?= $admins['id'] ?>-task-observer"
                                    style="padding-left: 4px; cursor: pointer;">
                                    <?= $admins['user_name'] ?></label>

                            </div>

                            <?php } ?>

                        </div>



                        <div class="form-group specialformus" style="float: left; width: 100%; margin-bottom: 0;">
                            <?php

                            $demandsq = $mysqli->query('SELECT user_name, id, customer FROM demands WHERE status != 6') or die($mysqli->error);
                            ?>
                            <select id="choosepoptavka" name="demandus" class="select2" data-allow-clear="true"
                                    data-placeholder="Přiřadit událost k poptávce..."
                                    style="width: 100% !important; margin: 10px 0 10px 0;">
                                <option></option>
                                <?php while ($dem = mysqli_fetch_array($demandsq)) { ?>
                                    <option value="<?= $dem['id'] ?>>"><?php if ($dem['customer'] == 0) {
                                        echo 'S - ';
                                    } elseif ($dem['customer'] == 1) {
                                        echo 'V - ';
                                    } else {
                                        echo 'SV - ';
                                    }
                                    echo $dem['user_name']; ?></option><?php } ?>
                            </select>
                        </div>


                        <textarea class="form-control autogrow" name="text" placeholder="Zadání úkolu."
                                  style="overflow: hidden; margin-bottom: 8px;word-wrap: break-word; resize: horizontal; height: 80px;"></textarea>
                        <span class="button-demo"><button type="submit" data-type="zoom-in"
                                                          class="ladda-button btn btn-primary"
                                                          style="width: 100%; margin-top: 12px; height: 71px; margin-bottom: 0;  font-size: 17px;">Přidat
                                úkol</button></span>
                    </form>
                </div>
            </div>

            <div class="row">

                <div class="col-md-10 calendar-body">

                    <style>
                        .fc-body {
                            background-color: #FFF;
                        }
                    </style>


                    <div id="calendar" style="min-height: 500px;"><img class="calendar-loader"
                                                                       src="/admin/assets/images/loading.png"
                                                                       style="position:absolute; left: 43%; top: 34%; -webkit-animation:spin 2s linear 5; -moz-animation:spin 2s linear 5; animation:spin 2s linear 5;"/>
                    </div>


                </div>

                <style>

                    .calendar-body {  float: left; width: 89%; }

                    .calendar-buttons {
                        float:left; padding-left: 0; width: 11%;
                    }


                    @media (max-width: 1200px) {

                        .calendar-body {  float: left; width: 80%; }


                        .calendar-buttons {
                            float:left; padding-left: 0; width: 20%;
                        }

                    }

                    @media (max-width: 768px) {

                        .fc-header-toolbar { display: inline-block; }

                        .fc button {
                            margin-left: 4px !important;
                            float: left !important;
                            font-size: 10px !important;
                            padding: 5px 13px !important;
                        }

                        .fc-left { min-width: inherit !important;}
                        .fc-right { margin-top: 14px;}

                        .calendar-body {  float: left; width: 100%; }

                        .calendar-buttons {
                            float:left; padding-left: 0; width: 100%;
                        }

                    }

                </style>

                    <div class="col-sm-12 col-md-2 calendar-buttons">
                        <?php

                        mysqli_data_seek($adminsquery, 0);

                        if ($access_edit) {
                        ?>

                        <button id="add-thing" type="button" class="btn btn-success btn-icon icon-left btn-lg">
                            Přidat událost
                        </button>

                        <button id="cancel-add-thing" type="button" class="btn btn-danger btn-icon icon-left btn-lg"
                                style="display: none;">
                            Zrušit
                        </button>

                        <?php } ?>
                        <div class="btn-group" style="text-align: left;">


                            <a href="../admin/index?reciever_type=all&show=<?= $show ?>&show_type=<?= $show_type ?>"><label
                                        class="btn btn-white btn-lg <?php if (isset($reciever_type) && $reciever_type === 'all') {
                                            echo 'active';
                                        } ?>"
                                        style="width: 30%; margin-right: 1%; margin-bottom: 8px; font-size: 11px;overflow: hidden;">
                                    Vše
                                </label>

                            </a>
                            <a href="../admin/index?reciever_type=performer&show=<?= $show ?>&show_type=<?= $show_type ?>"><label
                                        class="btn btn-white btn-lg <?php if (isset($reciever_type) && $reciever_type === 'performer') {
                                            echo 'active';
                                        } ?>"
                                        style="width: 31%; margin-right: 1%; margin-bottom: 8px; font-size: 11px;overflow: hidden;">
                                    Proveditelé
                                </label>

                            </a>
                            <a href="../admin/index?reciever_type=observer&show=<?= $show ?>&show_type=<?= $show_type ?>"><label
                                        class="btn btn-white btn-lg <?php if (isset($reciever_type) && $reciever_type === 'observer') {
                                            echo 'active';
                                        } ?>"
                                        style="width: 32%; margin-bottom: 8px; font-size: 11px;overflow: hidden;">
                                    Informovaní
                                </label>

                            </a>

                            <?php if ($access_edit) { ?>

                            <a href="../admin/index?show=all&show_type=<?= $show_type ?>"><label
                                        class="btn btn-white btn-lg <?php if (isset($show) && $show === 'all') {
                                            echo 'active';
                                        } ?>"
                                        style="width: 100%; margin-bottom: 8px;">
                                    Všichni
                                </label></a>

                            <?php } ?>


                            <?php
                            if (!$access_edit) {

                                $adminsquery = $mysqli->query("SELECT id, user_name, role FROM demands WHERE (role = 'technician' OR id = '".$client['id']."') AND active = 1 AND active = 1");

                            }

                            while ($admins = mysqli_fetch_array($adminsquery)) { ?>
                                <a href="../admin/index?show=<?= $admins['id'] ?>&reciever_type=<?= $reciever_type ?>&show_type=<?= $show_type ?>"><label
                                            class="btn btn-white btn-lg <?php if (isset($show) && $show == $admins['id']) {
                                                echo 'active';
                                            } ?>"
                                            style="width: 100%; margin-bottom: 8px;">
                                        <?= $admins['user_name'] ?>
                                    </label></a>
                            <?php } ?>
                            <a href="../admin/index?show=technicians&show_type=<?= $show_type ?>"><label
                                        class="btn btn-white btn-lg btn-technicians <?php if (isset($show) && $show === 'technicians') {
                                            echo 'active';
                                        } ?>"
                                        style="width: 100%; margin-bottom: 8px;">
                                    Technici
                                </label></a>
                            <hr style="margin-top: 2px; margin-bottom: 10px;">

                            <a href="../admin/index?show_type=all&reciever_type=<?= $reciever_type ?>&show=<?= $show ?>"><label
                                        class="btn btn-white btn-lg <?php if (!isset($show_type) || $show_type === 'all') {
                                            echo 'active';
                                        } ?>"
                                        style="width: 100%; margin-bottom: 8px;">
                                    Vše
                                </label></a>

                            <a href="../admin/index?show_type=realizations&reciever_type=<?= $reciever_type ?>&show=<?= $show ?>"><label
                                        class="btn btn-white btn-lg <?php if (isset($show_type) && $show_type === 'realizations') {
                                            echo 'active';
                                        } ?>"
                                        style="width: 100%; <?php if (isset($show_type) && $show_type === 'realizations') {
                                            echo 'background-color: #303641; border-color: #000000;';
                                        } else {
                                            echo 'background-color: #00a65a; border-color: #00a65a;';
                                        } ?> color: #FFF;margin-bottom: 8px;">
                                    Potvrzené realizace
                                </label></a>


                            <a href="../admin/index?show_type=processed&reciever_type=<?= $reciever_type ?>&show=<?= $show ?>"><label
                                        class="btn btn-white btn-lg "
                                        style="width: 100%; <?php if (isset($show_type) && $show_type === 'processed') {
                                            echo 'background-color: #303641; border-color: #000000;';
                                        } else {
                                            echo 'background-color: #FF9933; border-color: #FF9933;';
                                        } ?> color: #FFF;margin-bottom: 8px;">
                                    V řešení realizace
                                </label></a>

                            <a href="../admin/index?show_type=planned&reciever_type=<?= $reciever_type ?>&show=<?= $show ?>"><label
                                        class="btn btn-white btn-lg "
                                        style="width: 100%; <?php if (isset($show_type) && $show_type === 'planned') {
                                            echo 'background-color: #303641; border-color: #000000;';
                                        } else {
                                            echo 'background-color: #21d1e1; border-color: #21d1e1;';
                                        } ?> color: #FFF;margin-bottom: 8px;">
                                    Plánované realizace
                                </label></a>

                            <a href="../admin/index?show_type=tasks&reciever_type=<?= $reciever_type ?>&show=<?= $show ?>"><label
                                        class="btn btn-white btn-lg <?php if (isset($show_type) && $show_type === 'tasks') {
                                            echo 'active';
                                        } ?>"
                                        style="width: 100%; <?php if (isset($show_type) && $show_type === 'tasks') {
                                            echo 'background-color: #303641; border-color: #000000;';
                                        } else {
                                            echo 'background-color: #e7353b; border-color: #e7353b;';
                                        } ?> color: #FFF;margin-bottom: 8px;">
                                    Úkoly
                                </label></a>

                            <a href="../admin/index?show_type=events&reciever_type=<?= $reciever_type ?>&show=<?= $show ?>"><label
                                        class="btn btn-white btn-lg <?php if (isset($show_type) && $show_type === 'events') {
                                            echo 'active';
                                        } ?>"
                                        style="width: 100%; <?php if (isset($show_type) && $show_type === 'events') {
                                            echo 'background-color: #303641; border-color: #000000;';
                                        } else {
                                            echo 'background-color: #0073b7; border-color: #0073b7;';
                                        } ?> color: #FFF;margin-bottom: 8px;">
                                    Události
                                </label></a>

                            <a href="../admin/index?show_type=services&reciever_type=<?= $reciever_type ?>&show=<?= $show ?>"><label
                                        class="btn btn-white btn-lg <?php if (isset($show_type) && $show_type === 'services') {
                                            echo 'active';
                                        } ?>"
                                        style="width: 100%; <?php if (isset($show_type) && $show_type === 'services') {
                                            echo 'background-color: #303641; border-color: #000000;';
                                        } else {
                                            echo 'background-color: #ab5ce9; border-color: #ab5ce9;';
                                        } ?> color: #FFF;margin-bottom: 8px;">
                                    Servisy
                                </label></a>

                            <a href="../admin/index?show_type=follow-ups&reciever_type=<?= $reciever_type ?>&show=<?= $show ?>"><label
                                        class="btn btn-white btn-lg <?php if (isset($show_type) && $show_type === 'follow-ups') {
                                            echo 'active';
                                        } ?>"
                                        style="width: 100%; <?php if (isset($show_type) && $show_type === 'follow-ups') {
                                            echo 'background-color: #303641; border-color: #000000;';
                                        } else {
                                            echo 'background-color: #A51218; border-color: #A51218;';
                                        } ?> color: #FFF;margin-bottom: 8px;">
                                    Follow Ups
                                </label></a>

                        </div>

                    </div>
                <?php } ?>


            </div>


        <footer class="main">


            &copy; <?= date('Y') ?> <?php

            $time = microtime();
            $time = explode(' ', $time);
            $time = $time[1] + $time[0];
            $finish = $time;
            $total_time = round($finish - $start, 4);

            echo 'PHP '.PHP_VERSION.' | Page generated in ' . $total_time . ' seconds.'; ?>
            <span style=" float:right;">

                    <div class="fc" style="float: right; ">
                        <div class="fc-button-group">
                            <a href="<?= $home ?>/admin/index?calendar=0"><button type="button"
                                                                                          class="fc-button fc-state-default fc-corner-left <?php if ($client['technical'] == 0) {
                                                                                              echo 'fc-state-active';
                                                                                          } ?>">Styl
                                    1</button></a>
                            <a href="<?= $home ?>/admin/index?calendar=1"><button type="button"
                                                                                          class="fc-button fc-state-default <?php if ($client['technical'] == 1) {
                                                                                              echo 'fc-state-active';
                                                                                          } ?>">Styl
                                    2</button></a>
                            <a href="<?= $home ?>/admin/index?calendar=2"><button type="button"
                                                                                          class="fc-button fc-state-default fc-corner-right <?php if ($client['technical'] == 2) {
                                                                                              echo 'fc-state-active';
                                                                                          } ?>">Styl
                                    3</button></a>
                        </div>
                    </div>

                </span>

        </footer>
    </div>


</div>


<script>
    $(document).ready(function () {

        $("#udform").on("submit", function () {
            var form = $("#udform");
            var l = Ladda.create(document.querySelector('#udform .button-demo button'));
            if (form.valid()) {

                l.start();
            }
        });


        $("#taskform").on("submit", function () {
            var form = $("#taskform");
            var l = Ladda.create(document.querySelector('#taskform .button-demo button'));
            if (form.valid()) {

                l.start();
            }
        });


        $("#realform").on("submit", function () {
            var form = $("#realform");
            var l = Ladda.create(document.querySelector('#realform .button-demo button'));
            if (form.valid()) {

                l.start();
            }
        });


        $("#realform2").on("submit", function () {
            var form = $("#udform");
            var l = Ladda.create(document.querySelector('#realform2 .button-demo button'));
            if (form.valid()) {

                l.start();
            }
        });


    });
</script>

<?php include VIEW . '/default/footer.php'; ?>