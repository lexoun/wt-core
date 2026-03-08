<?php
$performers = '';

foreach ($performersArray as $performer => $value) {

    $selectquery = $mysqli->query("SELECT user_name FROM demands WHERE id = '$value'");
    $select = mysqli_fetch_array($selectquery);

    if ($performers != "") { $performers .= ', ' . $select['user_name'];; } else { $performers = $select['user_name'];; }

}

$observers = '';

foreach ($observersArray as $performer => $value) {

    $selectquery = $mysqli->query("SELECT user_name FROM demands WHERE id = '$value'");
    $select = mysqli_fetch_array($selectquery);

    if ($observers != "") { $observers .= ', ' . $select['user_name']; } else { $observers = $select['user_name'];; }

}


if (isset($_POST['demandus']) && $_POST['demandus'] != '') {

    $requestquery = $mysqli->query("SELECT user_name FROM demands WHERE id = '" . $_POST['demandus'] . "'") or die("bNeexistuje");
    $request = mysqli_fetch_array($requestquery);

    $demand = '<p style="margin: 0 0 8px;">Přiřazeno k poptávce <a href="https://www.wellnesstrade.cz/admin/pages/demands/zobrazit-poptavku?id='.$_POST['demandus'].'" target="_blank"><strong>'.$request['user_name'].'</strong></a>.</p>';

} else {

    $demand = '<p style="margin: 0 0 8px;">Není přiřazeno k poptávce ani klientovi.</p>';

}

if ($_POST['text'] != "") {

    $event_description = $_POST['text'];

} else {

    $event_description = 'žádný popis';

}

$attendees[] = '';

foreach ($mailRecievers as $reciever => $value) {

    $selectquery = $mysqli->query("SELECT user_name, dimension, email FROM demands WHERE id = '$value'");
    $select = mysqli_fetch_array($selectquery);

    $attendee = new Google_Service_Calendar_EventAttendee();
    $attendee->setDisplayName($select['user_name']);
    $attendee->setEmail($select['dimension']);
    $attendee->setResponseStatus('accepted');

    if (isset($client['id']) && $client['id'] == $value) {
        $attendee->setOrganizer(true);
    }

    $attendees[] = $attendee;
    unset($attendee);

    $subject = 'Nový úkol - ' . $_POST['title'];

    $reciever_mail = $select['email'];
    $reciever_username = $select['user_name'];
    $title = 'Nový úkol - ' . $_POST['title'];

    $opening_text = '<p style="margin: 0 0 16px;">V administračním rozhraní WellnessTrade.cz byl vytvořen nový úkol, ke kterému jsi byl přiřazen:</p>

          <table cellspacing="1" cellpadding="1" border="0" style="margin: 20px 0; border: 1px solid #dcdcdc; padding: 23px 30px 22px; line-height: 26px; background: #fbfbfb;">
          <tbody>

            <tr>
              <td style="width: 120px;">Název úkolu:</td>
              <td><strong>' . $_POST['title'] . '</strong></td>
            </tr>

            <tr>
              <td>Datum splnění:</td>
              <td><strong>' . $startus . '</strong></td>
            </tr>

             <tr>
              <td style="padding-top: 5px;">Popis úkolu:</td>
              <td style="font-size: 13px; line-height: 20px; padding-top: 8px;">' . $event_description . '</td>
            </tr>

          </tbody>
          </table>

          ' . $demand . '

          <p style="margin: 0 0 8px;">Proveditelé: <strong>' . $performers . '</strong></p>
          <br>
          <p style="margin: 0 0 8px;">Informovaní: <strong>' . $observers . '</strong></p>
          <br>
          <p style="margin: 0 0 2px;">Úkol zadal: ' . $client['user_name'] . '</p>
          <p style="margin: 0 0 2px;">Datum vytvoření: ' . $now . '</p>';

    include CONTROLLERS . "/admin_mails_templates.php";

}