<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";

include INCLUDES . "/googlelogin.php";
include INCLUDES . "/functions.php";

$categorytitle = "Přehled";
$pagetitle = "Denní plán";

if(isset($_REQUEST['admin'])){ $id = $_REQUEST['admin']; }
if (isset($_REQUEST['type'])) {$type = $_REQUEST['type'];}

if (!isset($id) || $id == "") {

    $id = $client['id'];

}

$redirect_url = urlencode('pages/system/denni-plan?' . $_SERVER['QUERY_STRING']);

include VIEW . '/default/header.php';

$getclient_query = $mysqli->query("SELECT * FROM demands WHERE id = '$id'") or die($mysqli->error);
$getclient = mysqli_fetch_array($getclient_query);

?>


<!-- Pager for search results -->
<div class="col-md-12 well" style="border-color: #ebebeb; background-color: #fbfbfb; margin-top: 10px;padding-bottom: 11px;">
<div class="row">
	<div class="col-md-7">
		<div class="btn-group" style="text-align: left;">
						<?php $mark = "?";?>


				<a href="denni-plan?admin=all<?php if (isset($type)) {echo '&type=' . $type;}?>"><label style="padding: 5px 11px !important; margin-bottom: 8px;" class="btn btn-lg <?php if ($id == 'all') { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
					Vše
				</label></a>
				<?php
$admins_query = $mysqli->query('SELECT id, user_name FROM demands WHERE role != "client" AND active = 1 ORDER BY id') or die($mysqli->error);

while ($admin = mysqli_fetch_array($admins_query)) { ?>
				<a href="?admin=<?= $admin['id'] ?><?php if (isset($type)) {echo '&type=' . $type;}?>"><label style="padding: 5px 11px !important; margin-bottom: 8px;" class="btn btn-lg <?php if ($id == $admin['id']) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
					<?= $admin['user_name'] ?>
				</label></a>
				<?php } ?>

			</div></div>
			<div class="col-md-5">

				<div class="btn-group" style="float: right; text-align: right">
				<a href="denni-plan<?php if (isset($id)) {echo '?admin=' . $id;}?>"><label style="padding: 5px 11px !important;" class="btn btn-lg <?php if (!isset($type)) { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
							Vše
						</label></a>
						<a href="?type=tasks<?php if (isset($id)) {echo '&admin=' . $id;}?>"><label style="padding: 5px 11px !important;" class="btn btn-lg <?php if (isset($type) && $type == 'tasks') { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
							Úkoly
                        </label></a>
                        <a href="?type=follow-ups<?php if (isset($id)) {echo '&admin=' . $id;}?>"><label style="padding: 5px 11px !important;" class="btn btn-lg <?php if (isset($type) && $type == 'follow-ups') { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
							Follow Upy
						</label></a>
						<a href="?type=events<?php if (isset($id)) {echo '&admin=' . $id;}?>"><label style="padding: 5px 11px !important;" class="btn btn-lg <?php if (isset($type) && $type == 'events') { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
							Události
						</label></a>
						<a href="?type=realizations<?php if (isset($id)) {echo '&admin=' . $id;}?>"><label style="padding: 5px 11px !important;" class="btn btn-lg <?php if (isset($type) && $type == 'realizations') { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
							Realizace
						</label></a>
						<a href="?type=services<?php if (isset($id)) {echo '&admin=' . $id;}?>"><label style="padding: 5px 11px !important;" class="btn btn-lg <?php if (isset($type) && $type == 'services') { ?>btn-primary<?php } else { ?>btn-white<?php } ?>">
							Servisy
						</label></a>
						<br>
						<hr>

						<a href="#today"><label style="padding: 5px 11px !important;" class="btn btn-white">
							Dnešní věci
						</label></a>

					</div>
				</div>
			</div>
		</div>


<?php

function table_head()
{
    ?>
	<thead>
    <tr role="row">
      <th class="text-center">Typ</th>
      <th class="text-center">Kategorie</th>
      <th class="text-center">ID</th>
      <th class="text-center" style="min-width: 100px;">Datum</th>
      <th class="text-center" style="min-width: 100px;">Čas</th>
      <th class="text-center">Název & Doplňující info</th>
      <th class="text-center" style="min-width: 180px; width: 180px;">Akce</th>
    </tr>
   </thead>

	<?php

}

function array_orderby()
{
    $args = func_get_args();
    $data = array_shift($args);
    foreach ($args as $n => $field) {
        if (is_string($field)) {
            $tmp = array();
            foreach ($data as $key => $row) {
                $tmp[$key] = $row[$field];
            }

            $args[$n] = $tmp;
        }
    }
    $args[] = &$data;
    call_user_func_array('array_multisort', $args);
    return array_pop($args);
}

function daily_todo($data)
{

    ?>

	<tr class="even">
		<td style="text-align: center;"><a class="btn btn-blue btn-sm" style="width: 70px; background-color: <?= $data['color'] ?>; border-color: <?= $data['color'] ?>;">
				<?= $data['type'] ?>
			</a></td>

		<td style="text-align: center;"><a class="btn btn-primary btn-sm" style="width: 135px;"><?= $data['category'] ?></a></td>
		<td style="text-align: left;"><?= $data['id'] ?></td>
		<td style="text-align: center;"><?= $data['date'] ?></td>
		<td style="text-align: center;"><?= $data['time'] ?></td>
		<td style="color: #222;"><strong ><?= $data['name'] ?></strong><br><hr style="margin: 5px 0"><?= $data['details'] ?></td>
		<td style="text-align: center;">
			<?= $data['button_done'] ?>
			<a href="<?= $data['show_link'] ?>" class="btn btn-primary btn-sm" style="margin-bottom: 4px;">
				<i class="entypo-search"></i>
			</a>
		</td>
	</tr>

	<?php

}


$admin_realization = '';
$admin_services = '';
$admin_tasks = '';
$admin_events = '';
$admin_followups = '';


if (isset($id) && $id != 'all') {

    $admin_realization = 'realizations.reciever_id = ' . $id . ' AND';
    $admin_services = 't.admin_id = ' . $id . ' AND';
    $admin_tasks = 'tasks.admin_id = ' . $id . ' AND';
    $admin_events = 'r.admin_id = ' . $id . ' AND';
    $admin_followups = 'AND fups.admin_id = ' . $id . '';

}

$today_array = array();
$tomorrow_array = array();
$old_array = array();
$new_array = array();

$now = date("Y-m-d");
$tomorrow = date("Y-m-d", strtotime("+1 day"));

if (!isset($type) || $type == 'realizations') {

    $realizations_query = $mysqli->query("SELECT *, d.id as demand_id, DATE_FORMAT(d.realization, '%d. %m. %Y') as date, DATE_FORMAT(d.realizationtime, '%H:%i') as time, d.realization as sort FROM mails_recievers realizations, demands d WHERE realizations.admin_id = '$id' AND d.id = realizations.type_id AND d.status != 5 AND d.realization != '0000-00-00' AND realizations.reciever_type = 'performer' ORDER BY d.realization") or die($mysqli->error);

    while ($select = mysqli_fetch_array($realizations_query)) {

        if (isset($select['customer']) && $select['customer'] != 0) {
            $sign = 'V - ';
            $data['name'] = 'Realizace vířivky - ' . $select['user_name'];
        } else {
            $sign = 'S - ';
            $data['name'] = 'Realizace sauny - ' . $select['user_name'];
        }

        $data['id'] = '<a href="/admin/pages/demands/zobrazit-poptavku?id=' . $select['id'] . '" target="_blank" class="btn btn-sm btn-default" style="font-weight: bold; color: #222;">' . $sign . $select['user_name'] . '</a>';

        $data['button_done'] = '';

        $data['type'] = 'Realizace';

        foreach($demand_statuses as $status){
            if($status['id'] == $select['status']){ $data['category'] = $status['name']; }
        }

        $data['color'] = "#00a65a";

        $data['sort'] = $select['sort'];

        $data['id'] .= '<br>
        Proveditelé: <strong>'.getRecievers('realization_hottub', $select['type_id'], 'performer').'</strong><br>Informovaní: <strong>'.getRecievers('realization_hottub', $select['type_id'], 'observer').'</strong>';

        if($select['realizationtime'] != '00:00'){ $time = $select['realizationtime']; }else{ $time = '-'; };

        $data['date'] = $select['date'];
        $data['time'] = $select['realizationtime'];
        $data['details'] = $select['technical_description'];

        $data['show_link'] = '/admin/pages/demands/zobrazit-poptavku?id=' . $select['demand_id'];

        if (isset($data['sort']) && $data['sort'] == $tomorrow) {
            array_push($tomorrow_array, $data);
        } elseif ($data['sort'] > $now) {
            array_push($new_array, $data);
        } elseif ($data['sort'] < $now) {
            array_push($old_array, $data);
        } elseif (isset($data['sort']) && $data['sort'] == $now) {
            array_push($today_array, $data);
        }

    }

}

if (!isset($type) || $type == 'services') {

    $services_query = $mysqli->query("SELECT s.*, t.*, s.id as id, DATE_FORMAT(s.date, '%d. %m. %Y') as date, DATE_FORMAT(s.estimatedtime, '%H:%i') as time, s.date as sort, d.user_name as demand_name FROM (services s, mails_recievers t) LEFT JOIN demands d on d.id = s.clientid WHERE $admin_services s.id = t.type_id AND t.type = 'service' AND s.status < 3 AND s.date != '0000-00-00' GROUP BY s.id") or die($mysqli->error);

    while ($select = mysqli_fetch_array($services_query)) {

        $data['button_done'] = '';

        $data['type'] = 'Servis';
        $data['color'] = "#ab5ce9";

        if(!empty($select['clientid'])){

            $data['id'] = '<a href="/admin/pages/demands/zobrazit-poptavku?id=' . $select['clientid'] . '" target="_blank" class="btn btn-sm btn-default" style="font-weight: bold; color: #222;">' . $select['demand_name'] . '</a>';

        } elseif (isset($select['shipping_name']) && $select['shipping_name'] != '' && $select['shipping_surname'] != '') {

            $data['id'] = '<a class="btn btn-sm btn-default" style="font-weight: bold; color: #222;">' . $select['shipping_name'] . ' ' . $select['shipping_surname'] . '</a>';

        } else {

            $data['id'] = '-';

        }

        $data['button_done'] = '<a data-id="' . $select['id'] . '" class="toggle-modal-change-status btn btn-blue btn-sm btn-icon icon-left">
					<i class="entypo-bookmarks"></i>
					Změnit stav
				</a>';

        if (isset($select['status']) && $select['status'] == 0) {$category = 'Nepotvrzený';} elseif (isset($select['status']) && $select['status'] == 1) {$category = 'Potvrzený';} elseif (isset($select['status']) && $select['status'] == 2) {$category = 'Odjetý';} elseif (isset($select['status']) && $select['status'] == 3) {$category = 'Dokončený';} elseif (isset($select['status']) && $select['status'] == 9) {$category = 'Stornovaný';}

        $data['category'] = $category;


        if($select['estimatedtime'] != '00:00'){ $time = $select['estimatedtime']; }else{ $time = '-'; };


        $data['id'] .= '<br>
        Proveditelé: <strong>'.getRecievers('service', $select['type_id'], 'performer').'</strong><br>Informovaní: <strong>'.getRecievers('service', $select['type_id'], 'observer').'</strong>';


        $data['sort'] = $select['sort'];
        $data['name'] = '';
        $data['date'] = $select['date'];
        $data['time'] = $select['estimatedtime'];
        $data['details'] = $select['details'];

        $data['show_link'] = '/admin/pages/services/zobrazit-servis?id=' . $select['id'];

        if (isset($data['sort']) && $data['sort'] == $tomorrow) {
            array_push($tomorrow_array, $data);
        } elseif (isset($data['sort']) && $data['sort'] > $now) {
            array_push($new_array, $data);
        } elseif (isset($data['sort']) && $data['sort'] < $now) {
            array_push($old_array, $data);
        } elseif (isset($data['sort']) && $data['sort'] == $now) {
            array_push($today_array, $data);
        }

    }

}


if (!isset($type) || $type == 'tasks') {

    $today_tasks_query = $mysqli->query("SELECT *, t.id as id, DATE_FORMAT(t.due, '%d. %m. %Y') as date, DATE_FORMAT(t.time, '%H:%i') as time, t.due as sort, c.user_name as client_name, d.user_name as demand_name, t.status as task_status FROM mails_recievers tasks, tasks t LEFT JOIN demands c ON c.id = t.client_id LEFT JOIN demands d ON d.id = t.demand_id WHERE $admin_tasks t.id = tasks.type_id AND tasks.type = 'task' AND t.status != 3") or die($mysqli->error);

    while ($select = mysqli_fetch_array($today_tasks_query)) {

        if (isset($select['status']) && $select['status'] == 1) {$category = 'Nezpracovaná poptávka';} elseif (isset($select['status']) && $select['status'] == 2) {$category = 'Zhotovená nabídka';} elseif (isset($select['status']) && $select['status'] == 3) {$category = 'V řešení';} elseif (isset($select['status']) && $select['status'] == 4) {$category = 'Realizace';} elseif (isset($select['status']) && $select['status'] == 7) {$category = 'Odložená';} elseif (isset($select['status']) && $select['status'] == 8) {$category = 'Nedokončená';} elseif (isset($select['status']) && $select['status'] == 5) {$category = 'Hotová';} elseif (isset($select['status']) && $select['status'] == 14) {$category = 'Neobjednaná vířivka';} elseif (isset($select['status']) && $select['status'] == 15) {$category = 'Nová realizace';}elseif (isset($select['status']) && $select['status'] == 12) {$category = 'Prodaná';}elseif (isset($select['status']) && $select['status'] == 13) {$category = 'Dokončená';} else { $category = 'Stornovaná';}

        if (isset($select['demand_id']) && $select['demand_id'] != 0) {

            $data['id'] = '<a href="/admin/pages/demands/zobrazit-poptavku?id=' . $select['demand_id'] . '" target="_blank" class="btn btn-sm btn-default" style="font-weight: bold; color: #222;">' . $select['demand_name'] . '</a>';
            $data['color'] = "#e7353b";
            $data['category'] = 'Poptávka - ' . $category;

        } else {

            $data['id'] = '-';
            $data['color'] = "#e7353b";
            $data['category'] = 'Obecný úkol';

        }

        $data['type'] = 'Úkol';

        $in_work = '';

        if (isset($select['task_status']) && $select['task_status'] == 0) {

            $status = '<span style="font-weight: bold; color: #ff0007;"><i class="entypo-cancel" style="padding-right: 2px;"></i>Neřešeno</span>';

            $in_work = '<a href="/admin/controllers/task-controller?task=change&taskid=' . $select['id'] . '&status=1&redirect_url=' . $redirect_url . '" class="btn btn-blue btn-sm" style="margin-bottom: 4px;">
					<i class="entypo-tools"></i>
				</a>';

        } elseif (isset($select['task_status']) && $select['task_status'] == 1) {

            $status = '<span style=" color: #0072bc; font-weight: bold;"><i class="entypo-tools" style="padding-right: 2px;"></i>V řešení</span>';

        }

        $data['button_done'] = $in_work . ' <a href="/admin/controllers/task-controller?task=change&taskid=' . $select['id'] . '&status=3&redirect_url=' . $redirect_url . '" class="btn btn-success btn-sm" style="margin-bottom: 4px;">
					<i class="entypo-check"></i>
                </a>';
                

        if($select['time'] != '00:00'){ $time = $select['date']; }else{ $time = '-'; };

        $data['id'] .= '<br>
        Proveditelé: <strong>'.getRecievers('task', $select['type_id'], 'performer').'</strong><br>Informovaní: <strong>'.getRecievers('task', $select['type_id'], 'observer').'</strong>';

        $data['sort'] = $select['sort'];
        $data['name'] = $status . ' - ' . $select['title'];
        $data['date'] = $select['date'];
        $data['time'] = $time;
        $data['details'] = $select['text'];

        $data['show_link'] = '/admin/pages/tasks/zobrazit-ukol?id=' . $select['id'];

        if (isset($data['sort']) && $data['sort'] == $tomorrow) {
            array_push($tomorrow_array, $data);
        } elseif ($data['sort'] > $now) {
            array_push($new_array, $data);
        } elseif ($data['sort'] < $now) {
            array_push($old_array, $data);
        } elseif (isset($data['sort']) && $data['sort'] == $now) {
            array_push($today_array, $data);
        }

    }

}


if (!isset($type) || $type == 'follow-ups') {

    $today_tasks_query = $mysqli->query("SELECT *, h.id as type_id, c.status as status, c.user_name as demand_name, h.type as type, DATE_FORMAT(h.date_time, '%Y-%m-%d') as sort FROM mails_recievers fups, demands_mails_history h LEFT JOIN demands c ON c.id = h.demand_id LEFT JOIN demands d ON d.id = h.id WHERE h.state = 'ongoing' AND h.id = fups.type_id AND fups.type = 'follow_up' AND fups.reciever_type = 'performer' $admin_followups") or die($mysqli->error);

    while ($select = mysqli_fetch_array($today_tasks_query)) {

//        print_r($select);


        if (isset($select['status']) && $select['status'] == 1) {$category = 'Nezpracovaná poptávka';} elseif (isset($select['status']) && $select['status'] == 2) {$category = 'Zhotovená nabídka';} elseif (isset($select['status']) && $select['status'] == 3) {$category = 'V řešení';} elseif (isset($select['status']) && $select['status'] == 4) {$category = 'Realizace';} elseif (isset($select['status']) && $select['status'] == 7) {$category = 'Odložená';} elseif (isset($select['status']) && $select['status'] == 8) {$category = 'Nedokončená';} elseif (isset($select['status']) && $select['status'] == 5) {$category = 'Hotová';}elseif (isset($select['status']) && $select['status'] == 13) {$category = 'Dokončená';} else { $category = 'Stornovaná';}


        $data['id'] = '<a href="/admin/pages/demands/zobrazit-poptavku?id=' . $select['demand_id'] . '" target="_blank" class="btn btn-sm btn-default" style="font-weight: bold; color: #222;">' . $select['demand_name'] . '</a>';
        $data['color'] = "#e7353b";
        $data['category'] = $select['type'];

        $data['type'] = 'Follow Up';

        $in_work = '';

        $status = '<span style="font-weight: bold; color: #ff0007;"><i class="entypo-cancel" style="padding-right: 2px;"></i>Neprovedeno</span>';

            $state = '<a href="/admin/pages/demands/zobrazit-poptavku?id=' . $select['demand_id'] . '&action=follow-up-state&follow_up_id=' . $select['type_id'] . '&state=done&redirect_url=' . $redirect_url . '" class="btn btn-success btn-sm"  style="margin-bottom: 4px;">
                <i class="fa fa-check"></i>
            </a>';

        $data['button_done'] = $state;
                

        $newDate = new DateTime($select['date_time']);

        $date = $newDate->format('d. m. Y'); 
        if($newDate->format('H:i') != '00:00'){ $time = $newDate->format('H:i'); }else{ $time = '-'; };

        $data['id'] .= '<br>
        Proveditelé: </strong>'.getRecievers('follow_up', $select['type_id'], 'performer').'</strong><br>Informovaní: <strong>'.getRecievers('follow_up', $select['type_id'], 'observer').'</strong>';


        $data['sort'] = $select['sort'];
        $data['name'] = $status;
        $data['date'] = $date;
        $data['time'] = $time;
        $data['details'] = $select['text'];

        $data['show_link'] = '/admin/pages/demands/zobrazit-poptavku?id=' . $select['demand_id'];



        if (isset($data['sort']) && $data['sort'] == $tomorrow) {

            array_push($tomorrow_array, $data);

        } elseif ($data['sort'] > $now) {

            array_push($new_array, $data);

        } elseif ($data['sort'] < $now) {

            array_push($old_array, $data);

        } elseif (isset($data['sort']) && $data['sort'] == $now) {

            array_push($today_array, $data);

        }


    }

}

if (!isset($type) || $type == 'events') {

    $texts_query = $mysqli->query("SELECT *, t.id as id, DATE_FORMAT(t.date, '%d. %m. %Y') as date, DATE_FORMAT(t.time, '%H:%i') as time, t.date as sort, d.user_name FROM mails_recievers r, dashboard_texts t LEFT JOIN demands d ON t.demand_id = d.id WHERE $admin_events t.id = r.type_id and r.type = 'event' AND t.date >= CURDATE()") or die($mysqli->error);

    while ($select = mysqli_fetch_array($texts_query)) {

        if (isset($select['demand_id']) && $select['demand_id'] != 0) {

            $data['id'] = '<a href="/admin/pages/demands/zobrazit-poptavku?id=' . $select['demand_id'] . '" target="_blank" class="btn btn-sm btn-default" style="font-weight: bold; color: #222;">' . $select['user_name'] . '</a>';
            $data['color'] = "#0073b7";
            $data['category'] = 'Poptávka';

        } else {

            $data['id'] = '-';
            $data['color'] = "#0073b7";
            $data['category'] = 'Obecná událost';

        }

        $data['type'] = 'Událost';

        $data['button_done'] = '';

        if($select['time'] != '00:00'){ $time = $select['date']; }else{ $time = '-'; };

        /*$data['button_done'] = '<a href="/admin/pages/tasks/zobrazit-udalost?id='.$select['id'].'&status=1&event=change&redirect=pages/system/denni-plan" class="btn btn-success btn-sm btn-icon icon-left" style="margin-bottom: 4px;">
        <i class="entypo-check"></i>
        Proběhlo
        </a>';*/

        $data['id'] .= '<br>
        Proveditelé: <strong>'.getRecievers('event', $select['type_id'], 'performer').'</strong><br>Informovaní: <strong>'.getRecievers('event', $select['type_id'], 'observer').'</strong>';

        $data['sort'] = $select['sort'];
        $data['name'] = $select['title'];
        $data['date'] = $select['date'];
        $data['time'] = $time;
        $data['details'] = $select['popis'];

        $data['show_link'] = '/admin/pages/tasks/zobrazit-udalost?id=' . $select['id'];

        if (isset($data['sort']) && $data['sort'] == $tomorrow) {
            array_push($tomorrow_array, $data);
        } elseif ($data['sort'] > $now) {
            array_push($new_array, $data);
        } elseif ($data['sort'] < $now) {
            array_push($old_array, $data);
        } elseif (isset($data['sort']) && $data['sort'] == $now) {
            array_push($today_array, $data);
        }

    }

}

$tomorrow_array = array_orderby($tomorrow_array, 'sort', SORT_ASC, 'type', SORT_ASC);
$today_array = array_orderby($today_array, 'sort', SORT_ASC, 'type', SORT_ASC);
$old_array = array_orderby($old_array, 'sort', SORT_ASC, 'type', SORT_ASC);
$new_array = array_orderby($new_array, 'sort', SORT_ASC, 'type', SORT_ASC);

?>
<div class="row">
	<div class="col-md-9 col-sm-7">
		<h2 style="margin: 10px 0 16px;">Staré věci</h2>
	</div>
</div>
<div id="table-2_wrapper" class="dataTables_wrapper form-inline" role="grid">
	<table class="table table-bordered table-striped datatable dataTable" id="table-2" aria-describedby="table-2_info">

<?php table_head();?>

<tbody role="alert" aria-live="polite" aria-relevant="all">

<?php

foreach ($old_array as $data) {

    daily_todo($data);

}

?>
</tbody></table></div>

<hr>

<div class="row">
	<div class="col-md-9 col-sm-7">
		<h2 style="margin: 10px 0 16px;" id="today">Dnešní věci</h2>
	</div>
</div>
<div id="table-2_wrapper" class="dataTables_wrapper form-inline" role="grid">
	<table class="table table-bordered table-striped datatable dataTable" id="table-2" aria-describedby="table-2_info">
	<?php table_head();?>

<tbody role="alert" aria-live="polite" aria-relevant="all">
	<?php


foreach ($today_array as $data) {

    daily_todo($data);

}
?>
</tbody></table></div>

<hr>

<div class="row">
	<div class="col-md-9 col-sm-7">
		<h2 style="margin: 10px 0 16px;">Zítřejší věci</h2>
	</div>
</div>
<div id="table-2_wrapper" class="dataTables_wrapper form-inline" role="grid">
	<table class="table table-bordered table-striped datatable dataTable" id="table-2" aria-describedby="table-2_info">
	<?php table_head();?>

<tbody role="alert" aria-live="polite" aria-relevant="all">
	<?php

foreach ($tomorrow_array as $data) {

    daily_todo($data);

}
?>
</tbody></table></div>

<hr>

<div class="row">
	<div class="col-md-9 col-sm-7">
		<h2 style="margin: 10px 0 16px;">Nové věci</h2>
	</div>
</div>
<div id="table-2_wrapper" class="dataTables_wrapper form-inline" role="grid">
	<table class="table table-bordered table-striped datatable dataTable" id="table-2" aria-describedby="table-2_info">
	<?php table_head();?>

<tbody role="alert" aria-live="polite" aria-relevant="all">
	<?php
foreach ($new_array as $data) {

    daily_todo($data);

}
?>
</tbody></table>
</div>









<footer class="main">


	&copy; <?= date("Y") ?> <span style=" float:right;"><?php
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$total_time = round(($finish - $start), 4);

echo 'PHP '.PHP_VERSION.' | Page generated in ' . $total_time . ' seconds.';?></span>

</footer>	</div>


	</div>


	<style>

.page-body .selectboxit-container .selectboxit-options { margin-top: 40px !important; width: 100% !important;}
.page-body .selectboxit-container .selectboxit { height: 40px;width: 100% !important;}
.page-body .selectboxit-container .selectboxit .selectboxit-text { line-height: 40px; }
.page-body .selectboxit-container .selectboxit .selectboxit-arrow-container { height: 40px;}
.page-body .selectboxit-container .selectboxit .selectboxit-arrow-container:after { line-height: 40px;}
</style>

<script type="text/javascript">
$(document).ready(function(){
    $(".toggle-modal-change-status").click(function(e){

      $('#change-status-modal').removeData('bs.modal');
       e.preventDefault();


       var id = $(this).data("id");

        $("#change-status-modal").modal({

            remote: '/admin/controllers/modals/modal-change-services.php?id='+id+'&redirect_url=<?= $redirect_url ?>',
        });
    });
});
</script>


<div class="modal fade" id="change-status-modal" aria-hidden="true" style="display: none; margin-top: 3%;">

</div>


<?php include VIEW . '/default/footer.php'; ?>

