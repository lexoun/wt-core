<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";

if (isset($_REQUEST['type']) && $_REQUEST['type'] == "remove") {

    $mysqli->query('DELETE FROM demands_timeline WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);

} elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == "line" && $_REQUEST['turn'] == "on") {

    $mysqli->query('UPDATE demands_timeline SET line = "1" WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);

} elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == "line" && $_REQUEST['turn'] == "off") {

    $mysqli->query('UPDATE demands_timeline SET line = "0" WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);

} elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == "bold" && $_REQUEST['turn'] == "on") {

    $mysqli->query('UPDATE demands_timeline SET bold = "1" WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);

} elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == "bold" && $_REQUEST['turn'] == "off") {

    $mysqli->query('UPDATE demands_timeline SET bold = "0" WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);

} elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == "star" && $_REQUEST['turn'] == "on") {

    $mysqli->query('UPDATE demands_timeline SET star = "1" WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);

    $mysqli->query('UPDATE demands_timeline SET bold = "1" WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);

} elseif (isset($_REQUEST['type']) && $_REQUEST['type'] == "star" && $_REQUEST['turn'] == "off") {

    $mysqli->query('UPDATE demands_timeline SET star = "0" WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);

    $mysqli->query('UPDATE demands_timeline SET bold = "0" WHERE id="' . $_REQUEST['id'] . '"') or die($mysqli->error);

}
