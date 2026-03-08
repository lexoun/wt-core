<?php

include_once $_SERVER['DOCUMENT_ROOT'] . "/admin/config/configPublic.php";


$date = new DateTime();
$date->modify('-1 day');
$yesterday = $date->format('Y-m-d');
$today = date('Y-m-d');


// Odeslání informačního e-mailu o konci rezervací pro tento den
$hottubs_query = $mysqli->query("SELECT * FROM warehouse WHERE reserved_date = '".$today."' AND reserved_mail != 0");

while ($hottub = mysqli_fetch_array($hottubs_query)) {

    // EMAIL

    $subject = 'Konec rezervace vířivky - '.$hottub['serial_number'];
    $title = $subject;

    $opening_text = '<p style="margin: 0 0 16px;">Dnešním dnem končí rezervace vířivky na skladě s číslem <a href="https://www.wellnesstrade.cz/admin/pages/warehouse/zobrazit-virivku?id='.$hottub['id'].'" target="_blank">'.$hottub['serial_number'].'</a>.</p>

    <p style="clear: both;"></p>';

    require CONTROLLERS . '/admin_mails_templates.php';

    $admins_query = $mysqli->query("SELECT email, user_name FROM demands WHERE id = '".$hottub['reserved_mail']."'");
    while ($admins = mysqli_fetch_array($admins_query)){

        $mail->addAddress($admins['email'], $admins['user_name']);

    }

    if(!$mail->send()) {
        echo 'Message could not be sent.';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    }

}

// Ukončení rezervací u vířivek z předchozího dne
$hottubs_query = $mysqli->query("SELECT id FROM warehouse WHERE reserved_date = '".$yesterday."'");
while ($hottub = mysqli_fetch_array($hottubs_query)) {

    $mysqli->query("UPDATE warehouse SET reserved = 0, demand_id = 0, reserved_mail = 0, reserved_date = '0000-00-00' WHERE id = '".$hottub['id']."'");
    $mysqli->query("UPDATE containers_products SET demand_id = 0 WHERE warehouse_id = '".$hottub['id']."'");

}

//exit;


// Vyskladnění vířivek od odjetých realizací
$realizations_query = $mysqli->query("SELECT w.id FROM demands d, warehouse w WHERE w.demand_id = d.id AND w.status <> 4 AND (d.realization <= '".$yesterday."') AND d.confirmed = '1'");

while($realization = mysqli_fetch_assoc($realizations_query)){

    $mysqli->query("UPDATE warehouse SET status = 4 WHERE id = '" . $realization['id'] . "'") or die($mysqli->error);

}



// Přidání všech produktů pro každodenní update
// todo temporary disabled... all updated will be manual for a time

/*
$products_query = $mysqli->query("SELECT p.id FROM products p, products_sites s WHERE s.site != 'wellnesstrade' AND s.product_id = p.id GROUP BY p.id") or die($mysqli->error);

while ($product = mysqli_fetch_array($products_query)) {

        $mysqli->query("INSERT IGNORE INTO cron_jobs (product_id) VALUES ('" . $product['id'] . "')") or die($mysqli->error);

}
*/



// Poslání mailového upozornění na neobjednané vířivky
$date = Date('Y-m-d', strtotime("+98 days"));

$demands_query = $mysqli->query("SELECT d.id, d.user_name, d.admin_id, DATE_FORMAT(g.deadline_date, '%d. %m. %Y') as deadline FROM demands d, demands_generate_hottub g WHERE g.deadline_date <= '".$date."' AND d.status = 14 AND g.id = d.id")or die($mysqli->error);

while ($demand = mysqli_fetch_array($demands_query)) {

    // EMAIL
    $subject = 'Neobjednaná vířivka - ' . $demand['user_name'];
    $title = $subject;

    $opening_text = '<p style="margin: 0 0 16px;">V administraci je poptávka s neobjednanou vířivkou, která má deadline dříve jak za 98 dní!</p><p style="margin: 0 0 16px;">Jedná se o poptávku <a href="https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id=' . $demand['id'] . '" target="_blank">' . $demand['user_name'] . '</a> s deadlinem <strong>'.$demand['deadline'].'</strong>.</p>

    <p style="clear: both;"></p>';

    require CONTROLLERS . '/admin_mails_templates.php';

    $admins_query = $mysqli->query("SELECT email, user_name FROM demands WHERE id = '".$demand['admin_id']."'");
    $admin = mysqli_fetch_array($admins_query);

    $mail->addAddress($admin['email'], $admin['user_name']);

//    $mail->addBCC('becher.filip@gmail.com');

    if (!$mail->send()) {
        echo 'Message could not be sent.';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    }

}



/* OLD DELETE
// Send all invoices with no FIK to EET
$invoice_query = $mysqli->query("SELECT receipt, id, fik, bkp, pkp FROM orders_invoices WHERE date >= DATEADD(day, -1, convert(date, GETDATE()))
   AND date < convert(date, GETDATE()) AND fik = '' AND receipt != ''")or die($mysqli->error);
while ($invoice = mysqli_fetch_array($invoice_query)) {

    $repeat = true;
    include CONTROLLERS . "/stores/eet.php";

}
*/



// Check demands without warranty inspection in last 365 days

/*
$mysqli->query("
    SELECT * FROM demands
    WHERE id NOT IN(
        SELECT d.id
    FROM demands d, services s
    WHERE
        s.clientid = d.id AND
        s.category = 'revize' AND
        s.date BETWEEN DATE_SUB(NOW(), INTERVAL 365 DAY) AND NOW()
    )
    AND customer = 0 AND status = 5
")or die($mysqli->error);
*/