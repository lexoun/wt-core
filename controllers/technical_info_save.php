<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";

include INCLUDES . "/googlelogin.php";
include INCLUDES . "/functions.php";

$mysqli->query('UPDATE demands SET technical_description = "' . $mysqli->real_escape_string($_POST['technical_description']) . '" WHERE id="' . $_POST['id'] . '"') or die($mysqli->error);

saveCalendarEvent($_POST['id'], 'realization');

echo $_POST['technical_description'];
