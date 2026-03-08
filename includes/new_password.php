<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
$clientquery = $mysqli->query('SELECT * FROM demands WHERE email="' . $_REQUEST['email'] . '"') or die($mysqli->error);
$client = mysqli_fetch_assoc($clientquery);

$mail = new PHPMailer\PHPMailer\PHPMailer(true);

//$mail->SMTPDebug = 3;                               // Enable verbose debug output
$mail->CharSet = 'UTF-8';
$mail->isSMTP();
$mail->Host = 'mail.webglobe.cz'; // Specify main and backup SMTP servers
$mail->SMTPAuth = true; // Enable SMTP authentication
$mail->Username = 'admin@wellnesstrade.cz'; // SMTP username
$mail->Password = 'RD4ufcLv'; // SMTP password
$mail->SMTPSecure = 'ssl'; // Enable TLS encryption, `ssl` also accepted
$mail->Port = 465; // TCP port to connect to

$mail->From = 'admin@wellnesstrade.cz';
$mail->FromName = 'WellnessTrade.cz';
$mail->addAddress($client['email'], $client['name'] . $client['surname']); // Add a recipient

$mail->isHTML(true); // Set email   format to HTML

$mail->Subject = 'WellnessTrade.cz - Přihlašovací údaje';
$mail->Body = 'Vážený kliente,<br><br>v klientském rozhraní WellnessTrade.cz Vám byl založen účet s přihlašovacími údaji: <br><br> Email: <b>' . $client['email'] . '</b><br> Heslo: <b>' . $client['secretstring'] . '</b>.<br><br>S pozdravem a přáním pěkného dne<br><br>WellnessTrade.cz';

if (!$mail->send()) {
    echo 'Message could not be sent.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {

    $clientquery = $mysqli->query('UPDATE clients SET active="1" WHERE id = "' . $client['id'] . '"') or die($mysqli->error);

    header('Location: https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $client['id'] . '?email=done');
    exit;
}
