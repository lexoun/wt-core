<?php



include_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";

include_once INCLUDES . "/googlelogin.php";
include_once INCLUDES . "/functions.php";

$id = $_REQUEST['id'];

$dashboard_texts = $mysqli->query('SELECT *, DATE_FORMAT(date, "%d. %m. %Y") as dateformated, DATE_FORMAT(enddate, "%d. %m. %Y") as dateformatedend FROM dashboard_texts WHERE id="' . $id . '"') or die($mysqli->error);

if (mysqli_num_rows($dashboard_texts) > 0) {

    $dash_texts = mysqli_fetch_array($dashboard_texts);

    $pagetitle = 'Událost';

    $bread1 = "Události";
    $abread1 = "udalosti";

    if (isset($_REQUEST['action']) && $_REQUEST['action'] == "remove") {

        $mysqli->query('DELETE FROM dashboard_texts WHERE id="' . $id . '"') or die($mysqli->error);
        $mysqli->query('DELETE FROM mails_recievers WHERE type_id = "' . $id . '" AND type = "event"') or die($mysqli->error);

        calendarDelete($dash_texts['gcalendar']);

        header('location: https://www.wellnesstrade.cz/admin/index?success=task_edit');
        exit;
    }

    if (isset($_REQUEST['event']) && $_REQUEST['event'] == "change") {

        $changequery = $mysqli->query('UPDATE dashboard_texts SET status = "' . $_REQUEST['status'] . '" WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);

        if ($_REQUEST['redirect_id'] != "") {

            header('location: https://www.wellnesstrade.cz/admin/' . $_REQUEST['redirect'] . '?id=' . $_REQUEST['redirect_id'] . '&success=event_change');
            exit;

        } else {
            header('location: https://www.wellnesstrade.cz/admin/' . $_REQUEST['redirect'] . '?success=event_change');
            exit;
        }

    }

    if (isset($_REQUEST['action']) && $_REQUEST['action'] == "edit") {

        if ($_POST['title'] != "" && $_POST['date'] != "") {

            if (isset($_POST['endusdate']) && $_POST['endusdate'] == "") {

                $konecdate = $_POST['date'];

            } elseif ($_POST['endusdate'] != "") {

                $konecdate = $_POST['endusdate'];

            }

//            if (isset($_POST['time']) && $_POST['time'] == "") { $_POST['time'] = '00:00:00'; }
//            if (isset($_POST['endustime']) && $_POST['endustime'] == "") { $_POST['endustime'] = '23:59:00'; }

            $insertquery = $mysqli->query("UPDATE dashboard_texts SET title = '" . $_POST['title'] . "', popis = '" . $_POST['popis'] . "', date = '" . $_POST['date'] . "', time = '" . $_POST['time'] . "', enddate = '$konecdate', endtime = '" . $_POST['endustime'] . "' WHERE id = '$id'") or die($mysqli->error);

            $mysqli->query("DELETE FROM mails_recievers WHERE type_id = '".$id."' AND type = 'event'") or die($mysqli->error);


            if(!empty(($_POST['performer']))){ $performersArray = array_filter($_POST['performer']); }else{ $performersArray[] = ''; }
            if(!empty(($_POST['observer']))){ $observersArray = array_filter($_POST['observer']); }else{ $observersArray[] = ''; }

            if (!empty($performersArray) || !empty($observersArray)) {

                recievers($performersArray, $observersArray, 'event', $id);

            }

            saveCalendarEvent($id, 'event');

            header('location: https://www.wellnesstrade.cz/admin/pages/tasks/zobrazit-udalost?id=' . $id . '&success=task_edit');
        } else {
            header('location: https://www.wellnesstrade.cz/admin/pages/tasks/zobrazit-udalost?id=' . $id . '&error=task_edit');
        }
        exit;
    }


    include VIEW . '/default/header.php';


    ?>

<div class="profile-env" >
<section class="profile-feed" style="padding-left:15px;padding-right: 15px;  width: 900px; margin: 0 auto;">
<?php

    $requestorquery = $mysqli->query('SELECT user_name FROM demands WHERE id="' . $dash_texts['admin_id'] . '"') or die($mysqli->error);
    $requestor = mysqli_fetch_assoc($requestorquery);


    $performersQuery = $mysqli->query('SELECT t.admin_id, c.user_name FROM mails_recievers t, demands c WHERE t.type_id = "' . $dash_texts['id'] . '" AND t.admin_id = c.id AND t.type = "event" AND t.reciever_type = "performer"') or die($mysqli->error);

    $observersQuery = $mysqli->query('SELECT t.admin_id, c.user_name FROM mails_recievers t, demands c WHERE t.type_id = "' . $dash_texts['id'] . '" AND t.admin_id = c.id AND t.type = "event" AND t.reciever_type = "observer"') or die($mysqli->error);



?>
    <div class="panel panel-default" style="margin-bottom: 13px;  ">

        <div class="panel-heading" style="background-color: #fbfbfb;">

            <h4 class="panel-title" style="width: 100%; padding-top: 14px; height: 44px; border-bottom: 1px solid #ebebeb; margin-bottom: 10px;">


		<span style="float:left; font-size: 14px; font-weight: 500;"><span class="text-info"><a class="text-info" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $requestor['user_name'] ?>"><?php acronym($requestor['user_name']);?></a></span> <i class="entypo-right-open" style="margin-left: -6px;margin-right: -6px;"></i>
			<span class="text-danger">
				<?php
                $i = 0;
                while ($performer = mysqli_fetch_assoc($performersQuery)) {

                    if ($i > 0) {  echo '</span> & <span class="text-danger">'; }

                    ?><a class="text-danger"  data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $performer['user_name'] ?>"><?= acronym($performer['user_name']) ?></a><?php

                    $i = $i + 1;

                } ?></span> - <?= $dash_texts['title'] ?></span>
                        <?php if ($access_edit) { ?><div class="story-type" style="font-size: 11px;float:right;margin-right: -6px;">

                            <a data-toggle="collapse" data-parent="#prijate-ukoly" href="#edit"><i class="entypo-pencil"></i></a>
                            <span style="margin-left: 3px; margin-right: 5px; border-right: 1px solid #cccccc;"></span><a href="./zobrazit-udalost?id=<?= $dash_texts['id'] ?>&action=remove"><i class="entypo-trash" style="margin-right: 6px;"></i></a>
                            </div><?php } ?>
                    </h4>



				<p style="padding: 0px 15px 5px; margin-top: 5px;"><?php if ($dash_texts['demand_id'] != 0) {

        $demandquery = $mysqli->query('SELECT id, user_name FROM demands WHERE id="' . $dash_texts['demand_id'] . '"') or die($mysqli->error);
        $demand = mysqli_fetch_assoc($demandquery);

        echo '<a href="/admin/pages/demands/zobrazit-poptavku?id=' . $demand['id'] . '" target="_blank" style="line-height: 25px;;"><strong>Poptávka - ' . $demand['user_name'] . '</strong></a><br>';}
    echo $dash_texts['popis'];?></p>




            <h4 class="panel-title" style="width: 100%; border-top: 1px solid #ebebeb; padding-top: 14px; padding-bottom: 13px;">
                <div style="font-size: 12px;">

                        <span><i class="entypo-calendar" style="padding-right: 2px;"></i>Událost začíná <strong><?= $dash_texts['dateformated'] ?></strong></span>
                        <span style="margin-right: 14px;"><i class="entypo-clock" style="padding-right: 2px;"></i><strong><?= $dash_texts['time'] ?></strong></span>

                        <?php if ($dash_texts['enddate'] != '0000-00-00') { ?><span><i class="entypo-calendar" style="padding-right: 2px;"></i>Událost končí <strong><?= $dash_texts['dateformatedend'] ?></strong></span>
                            <span style="margin-right: 14px;"><i class="entypo-clock" style="padding-right: 2px;"></i><strong><?= $dash_texts['endtime'] ?></strong></span><?php } ?>




                        <span style="float:right;">Informovaní:
                    <?php
                    $i = 0;
                    while ($observer = mysqli_fetch_assoc($observersQuery)) {

                        if ($i > 0) {  echo ' & '; }

                        ?><a class="text-success"  data-toggle="tooltip" data-placement="top" title="" data-original-title="<?= $observer['user_name'] ?>"><?= acronym($observer['user_name']) ?></a><?php

                        $i = $i + 1;

                    } ?>



                </div>


            </h4>








				</div>

				<div id="edit" class="panel-collapse collapse">
				<form id="taskform" role="form" method="post" enctype='multipart/form-data' action="zobrazit-udalost?id=<?= $dash_texts['id'] ?>&action=edit" style="padding: 10px 0px 10px 10px;" >

							<input type="text" style="width: 48.1%; float: left; margin: 0 10px 10px 0;" name="title" value="<?= $dash_texts['title'] ?>" placeholder="Krátký název úkolu" class="form-control" id="field-1">

			<div class="date-and-time" style="width: 23.5%; float: left; margin: 0 12px 10px 0;" >
							<input type="text" class="form-control datepicker" id="realzmrd" name="date" data-format="yyyy-mm-dd" placeholder="Datum začátku" value="<?= $dash_texts['date'] ?>">
						    <input type="text" class="form-control timepicker" id="timezmrd" name="time" data-template="dropdown" placeholder="Čas" value="<?= $dash_texts['time'] ?>" data-show-seconds="false" data-default-time="" data-show-meridian="false" data-minute-step="5" />
						</div>
							<div class="date-and-time" style="width: 23.5%; float: left; margin: 0 0 10px 0;" >
							<input type="text" class="form-control datepicker" name="endusdate" data-format="yyyy-mm-dd" placeholder="Datum konce" <?php if ($dash_texts['enddate'] != '0000-00-00') { ?>value="<?= $dash_texts['enddate'] ?>"<?php } ?> >
						    <input type="text" class="form-control timepicker" name="endustime" data-template="dropdown" data-show-seconds="false" value="<?= $dash_texts['endtime'] ?>" data-default-time="10-00" data-show-meridian="false" data-minute-step="5" placeholder="Čas"/>
						</div>



                    <div class="well admins_well" style="padding: 12px 0px 7px; width: 49.5%; margin-right: 1%; float: left;">
                        <h4 style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">Proveditelé</h4>
                        <?php
                        $adminsQuery = $mysqli->query("SELECT id, user_name FROM demands WHERE role != 'client' AND active = 1 AND active = 1");

                        while ($admin = mysqli_fetch_array($adminsQuery)) {

                            $find_query = $mysqli->query("SELECT * FROM mails_recievers WHERE type_id = '" . $dash_texts['id'] . "' AND admin_id = '" . $admin['id'] . "' AND type = 'event' AND reciever_type = 'performer'") or die($mysqli->error);
                            ?>
                            <div class="col-sm-4" style="padding: 0 4px 0 10px;">
                                <input id="<?= $dash_texts['id'] ?>-admin-<?= $admin['id'] ?>-performer" name="performer[]" value="<?= $admin['id'] ?>" type="checkbox" <?php if (mysqli_num_rows($find_query) > 0) {echo 'checked';}?>>
                                <label for="<?= $dash_texts['id'] ?>-admin-<?= $admin['id'] ?>-performer" style="padding-left: 4px; cursor: pointer;"><?= $admin['user_name'] ?></label>
                            </div>
                            <?php
                        }?>
                    </div>

                    <div class="well admins_well" style="padding: 12px 0px 7px; width: 49.5%;  float: left;">
                        <h4 style="text-align: center; margin-top: 0; border-bottom: 1px solid #e2e2e5; padding-bottom: 10px;">Informovaní</h4>
                        <?php
                        $adminsQuery = $mysqli->query("SELECT id, user_name FROM demands WHERE role != 'client' AND active = 1 AND active = 1");

                        while ($admin = mysqli_fetch_array($adminsQuery)) {

                            $find_query = $mysqli->query("SELECT * FROM mails_recievers WHERE type_id = '" . $dash_texts['id'] . "' AND admin_id = '" . $admin['id'] . "' AND type = 'event' AND reciever_type = 'observer'") or die($mysqli->error);
                            ?>
                            <div class="col-sm-4" style="padding: 0 4px 0 10px;">
                                <input id="<?= $dash_texts['id'] ?>-admin-<?= $admin['id'] ?>-observer" name="observer[]" value="<?= $admin['id'] ?>" type="checkbox" <?php if (mysqli_num_rows($find_query) > 0) {echo 'checked';}?>>
                                <label for="<?= $dash_texts['id'] ?>-admin-<?= $admin['id'] ?>-observer" style="padding-left: 4px; cursor: pointer;"><?= $admin['user_name'] ?></label>
                            </div>
                            <?php
                        }?>
                    </div>



                    <textarea class="form-control autogrow" name="popis" placeholder="Stalo se něco nového?" style="width: 98%;overflow: hidden; margin-bottom: 8px;word-wrap: break-word; resize: horizontal; max-height: 100px;"><?= $dash_texts['popis'] ?></textarea>

                    <button type="submit" class="btn btn-primary" style="width: 80.5%; height: 71px; margin-bottom: 8px;  font-size: 17px;">Upravit událost</button>

                    <a data-toggle="collapse" data-parent="#prijate-ukoly" href="#edit-<?= $dash_texts['id'] ?>"><button type="button" class="btn btn-default" style="width: 17%; height: 71px; margin-bottom: 8px;  font-size: 17px;"><i class="entypo-cancel"></i></button></a>
		</form></div>
			</div>
</section></div>


<div class="clear"></div>


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


<?php include VIEW . '/default/footer.php'; ?>


<?php

} else {

    include "./includes/404.php";

}?>