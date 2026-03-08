<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
if (isset($_POST['type']) && $_POST['type'] == "text") {
    $mysqli->query('UPDATE demands SET description = "' . $mysqli->real_escape_string($_POST['description']) . '" WHERE id="' . $_POST['id'] . '"') or die($mysqli->error);
    echo $_POST['description'];
} else {
    $mysqli->query('UPDATE demands SET realization = "' . $mysqli->real_escape_string($_POST['realizationdate']) . '" WHERE id="' . $_POST['id'] . '"') or die($mysqli->error);
    echo $_POST['realizationdate'];

}
