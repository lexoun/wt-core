<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/config/configPublic.php';

$code = $mysqli->real_escape_string($_REQUEST['code']);
$mysqli->query("UPDATE demands SET newsletter = 1 WHERE secretstring = '".$code."'")or die($mysqli->error);

echo 'Úspěšně došlo k odhlášení odebírání e-mailů.';