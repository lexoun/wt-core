<?php

include_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include_once INCLUDES . "/googlelogin.php";
include_once INCLUDES . "/functions.php";

if (isset($_REQUEST['task']) && $_REQUEST['task'] == "add") {

    if ($_POST['title'] != "" && $_POST['datum'] != "") {

        if(!empty($_POST['demandus'])){ $choose_dem = $_POST['demandus']; }else{ $choose_dem = 0; }
        if(!empty($_POST['warehouse_id'])){ $warehouse_id = $_POST['warehouse_id']; }else{ $warehouse_id = 0; }

        $title = $mysqli->real_escape_string($_POST['title']);
        $text = $mysqli->real_escape_string($_POST['text']);

        $mysqli->query("INSERT INTO tasks (demand_id, warehouse_id, title, text, request_id, due, time, date) values ('$choose_dem', '".$warehouse_id."', '$title','$text','" . $client['id'] . "','" . $_POST['datum'] . "', '" . $_POST['time'] . "', CURRENT_TIMESTAMP())") or die($mysqli->error);

        $id = $mysqli->insert_id;

        if(!empty(($_POST['performer']))){ $performersArray = array_filter($_POST['performer']); }else{ $performersArray[] = ''; }
        if(!empty(($_POST['observer']))){ $observersArray = array_filter($_POST['observer']); }else{ $observersArray[] = ''; }

        if (!empty($performersArray) || !empty($observersArray)) {

            recievers($performersArray, $observersArray, 'task', $id);

        }

        saveCalendarEvent($id, 'task');

        if (isset($_REQUEST['redirectid']) && $_REQUEST['redirectid'] != "") {

            header('location: https://www.wellnesstrade.cz/admin/' . $_REQUEST['redirect'] . '?id=' . $_REQUEST['redirectid'] . '&success=task_add');
            exit;
        } else {
            header('location: https://www.wellnesstrade.cz/admin/' . $_REQUEST['redirect'] . '?success=task_add');
            exit;
        }
    } else {

        if (isset($_REQUEST['redirectid']) && $_REQUEST['redirectid'] != "") {

            header('location: https://www.wellnesstrade.cz/admin/' . $_REQUEST['redirect'] . '?id=' . $_REQUEST['redirectid'] . '&error=task_add');
            exit;
        } else {
            header('location: https://www.wellnesstrade.cz/admin/' . $_REQUEST['redirect'] . '?error=task_add');
            exit;
        }

    }

}

if (isset($_REQUEST['task']) && $_REQUEST['task'] == "remove") {

    $taskQuery = $mysqli->query("SELECT gcalendar FROM tasks WHERE id = '" . $_REQUEST['taskid'] . "'");
    $task = mysqli_fetch_array($taskQuery);

    $mysqli->query('DELETE FROM tasks WHERE id="' . $_REQUEST['taskid'] . '"') or die($mysqli->error);
    $mysqli->query('DELETE FROM mails_recievers WHERE type_id = "' . $_REQUEST['taskid'] . '" AND type = "task"') or die($mysqli->error);

    calendarDelete($task['gcalendar']);

    $url = strtok($_REQUEST['redirect'], '?');

    if ($url == 'pages/tasks/zobrazit-ukol') {

        header('location: https://www.wellnesstrade.cz/admin?success=task_remove');

    } elseif ($_REQUEST['redirectid'] != "" && $_REQUEST['redirectid'] != "") {

        header('location: https://www.wellnesstrade.cz/admin/' . $_REQUEST['redirect'] . '?id=' . $_REQUEST['redirectid'] . '&success=task_remove');
        exit;
    } elseif ($_REQUEST['redirectid'] != "") {
        header('location: https://www.wellnesstrade.cz/admin/' . $_REQUEST['redirect'] . '&success=task_remove');
        exit;
    } else {
        header('location: https://www.wellnesstrade.cz/admin/' . $_REQUEST['redirect'] . '?success=task_remove');
        exit;
    }

}

if (isset($_REQUEST['task']) && $_REQUEST['task'] == "change") {

    $changequery = $mysqli->query('UPDATE tasks SET status = "' . $_REQUEST['status'] . '" WHERE id="' . $_REQUEST['taskid'] . '"') or die($mysqli->error);

    if (!empty($_REQUEST['redirect_url'])) {

        $redirect_url = urldecode($_REQUEST['redirect_url']);

        header('location: https://www.wellnesstrade.cz/admin/' . $redirect_url);
        exit;

    } elseif (!empty($_REQUEST['redirectid'])) {

        header('location: https://www.wellnesstrade.cz/admin/' . $_REQUEST['redirect'] . '?id=' . $_REQUEST['redirectid'] . '&success=task_change');
        exit;

    } else {
        header('location: https://www.wellnesstrade.cz/admin/' . $_REQUEST['redirect']);
        exit;
    }

}

if (isset($_REQUEST['task']) && $_REQUEST['task'] == "edit") {

    if ($_POST['title'] != "" && $_POST['datum'] != "") {

        $choose_dem = $_POST['demandus'];
        $choose_cli = 0;
        $id = $_REQUEST['taskid'];

        $mysqli->query("UPDATE tasks SET title = '" . $_POST['title'] . "', text = '" . $_POST['text'] . "', demand_id = '" . $choose_dem . "', due = '" . $_POST['datum'] . "' WHERE id = '" . $_REQUEST['taskid'] . "'") or die($mysqli->error);


        $mysqli->query("DELETE FROM mails_recievers WHERE type_id = '".$_REQUEST['taskid']."' AND type = 'task'") or die($mysqli->error);


        if(!empty(($_POST['performer']))){ $performersArray = array_filter($_POST['performer']); }else{ $performersArray[] = ''; }
        if(!empty(($_POST['observer']))){ $observersArray = array_filter($_POST['observer']); }else{ $observersArray[] = ''; }

        if (!empty($performersArray) || !empty($observersArray)) {

            recievers($performersArray, $observersArray, 'task', $id);

        }

        saveCalendarEvent($id, 'task');

        if (isset($_GET['redirectid']) && $_GET['redirectid'] != "") {

            header('location: https://www.wellnesstrade.cz/admin/' . $_REQUEST['redirect'] . '?id=' . $_GET['redirectid'] . '&success=task_edit');
            exit;
        } else {
            header('location: https://www.wellnesstrade.cz/admin/' . $_REQUEST['redirect'] . '?success=task_edit');
            exit;
        }
    } else {

        if (isset($_GET['redirectid']) && $_GET['redirectid'] != "") {

            header('location: https://www.wellnesstrade.cz/admin/' . $_REQUEST['redirect'] . '?id=' . $_GET['redirectid'] . '&error=task_edit');
            exit;

        } else {
            header('location: https://www.wellnesstrade.cz/admin/' . $_REQUEST['redirect'] . '?error=task_edit');
            exit;
        }

    }
}

if (isset($_REQUEST['task']) && $_REQUEST['task'] == "postpone") {

    $select_date_query = $mysqli->query("SELECT due FROM tasks WHERE id = '" . $_REQUEST['taskid'] . "'");
    $select_date = mysqli_fetch_array($select_date_query);

    $notificationdate = Date('Y-m-d', strtotime($select_date['due'] . "+" . $_REQUEST['days'] . " days"));

    $changequery = $mysqli->query('UPDATE tasks SET due = "' . $notificationdate . '" WHERE id="' . $_REQUEST['taskid'] . '"') or die($mysqli->error);

    if (isset($_REQUEST['redirectid']) && $_REQUEST['redirectid'] != "") {

        header('location: https://www.wellnesstrade.cz/admin/' . $_REQUEST['redirect'] . '?id=' . $_REQUEST['redirectid'] . '&success=task_change');
        exit;
    } else {
        header('location: https://www.wellnesstrade.cz/admin/' . $_REQUEST['redirect'] . '');
        exit;
    }

}
