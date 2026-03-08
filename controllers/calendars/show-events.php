<?php
if ($_GET['showType'] == 'events' || $_GET['showType'] == 'all') {
    include $_SERVER['DOCUMENT_ROOT'] . '/admin/config/configPublic.php';

    function acronymRt($words)
    {

        $acronym = '';

        foreach (explode(' ', $words) as $word) {
            $acronym .= mb_substr($word, 0, 1, 'utf-8');
        }

        return $acronym;

    }


    function date_start_end($event){

        if ($event['enddate'] != '0000-00-00' && $event['time'] == '00:00:00' && $event['endtime'] == '00:00:00') {
            $current['start'] = $event['date'];
            $current['end'] = $event['enddate'] . 'T24:00:00';
        } elseif ($event['enddate'] != '0000-00-00' && $event['time'] != '00:00:00' && $event['endtime'] != '00:00:00') {
            $current['start'] = $event['date'] . 'T' . $event['time'];
            $current['end'] = $event['enddate'] . 'T' . $event['endtime'];
        } elseif ($event['enddate'] != '0000-00-00' && $event['endtime'] != '00:00:00') {
            $current['start'] = $event['date'];
            $current['end'] = $event['enddate'] . 'T' . $event['endtime'];
        } elseif ($event['enddate'] != '0000-00-00' && $event['time'] != '00:00:00') {
            $current['start'] = $event['date'] . 'T' . $event['time'];
            $current['end'] = $event['enddate'] . 'T24:00:00';
        } elseif ($event['enddate'] != '0000-00-00') {
            $current['start'] = $event['date'];
            $current['end'] = $event['enddate'] . 'T24:00:00';
        } elseif ($event['time'] != '00:00:00') {
            $current['start'] = $event['date'] . 'T' . $event['time'];
        } else {
            $current['start'] = $event['date'];
        }

        return $current;
    }

    if (isset($_GET['show'])) { $show = $_GET['show']; } else { $show = 'all'; }

    if (isset($_GET['recieverType']) && $_GET['recieverType'] != 'all') {  $recieverType = " AND reciever_type = '".$_GET['recieverType']."' "; }else{
        $recieverType = '';
    }


    if ($show == 'all') {


        if($_GET['recieverType'] == 'all'){

            $eventsQuery = $mysqli->query("SELECT t.time, t.enddate, t.endtime, t.date, t.title, t.id, t.popis, t.freq, t.rec_interval, t.count FROM dashboard_texts t WHERE t.date > DATE_SUB(NOW(), INTERVAL 12 MONTH)") or die($mysqli->error);

        }else{

            $eventsQuery = $mysqli->query("SELECT t.time, t.enddate, t.endtime, t.date, t.title, t.id, t.popis, t.freq, t.rec_interval, t.count FROM dashboard_texts t, mails_recievers r WHERE t.date > DATE_SUB(NOW(), INTERVAL 12 MONTH) AND r.type_id = t.id AND r.type = 'event' $recieverType GROUP BY t.id") or die($mysqli->error);

        }

    } elseif ($show == 'technicians') {

        $eventsQuery = $mysqli->query("SELECT t.time, t.enddate, t.endtime, t.date, t.title, t.id, t.popis, t.freq, t.rec_interval, t.count FROM dashboard_texts t, mails_recievers r, demands d WHERE r.admin_id = d.id AND d.role = 'technician' AND r.type_id = t.id AND r.type = 'event' $recieverType AND t.admin_id != r.admin_id AND t.date > DATE_SUB(NOW(), INTERVAL 12 MONTH) GROUP BY t.id") or die($mysqli->error);

    } else {

        $eventsQuery = $mysqli->query("SELECT t.time, t.enddate, t.endtime, t.date, t.title, t.id, t.popis, t.freq, t.rec_interval, t.count FROM dashboard_texts t, mails_recievers r WHERE t.admin_id = '$show' $recieverType AND r.type_id = t.id AND r.type = 'event' AND t.date > DATE_SUB(NOW(), INTERVAL 12 MONTH) GROUP BY t.id") or die($mysqli->error);

      $eventsQuery2 = $mysqli->query("SELECT t.time, t.enddate, t.endtime, t.date, t.title, t.id, t.popis, t.freq, t.rec_interval, t.count FROM dashboard_texts t, mails_recievers r WHERE r.admin_id = '$show' AND r.type_id = t.id AND r.type = 'event' AND t.admin_id != r.admin_id $recieverType AND t.date > DATE_SUB(NOW(), INTERVAL 12 MONTH) GROUP BY t.id") or die($mysqli->error);

    }

      while ($event = mysqli_fetch_assoc($eventsQuery)) {

          $allTargets = 'Proveditelé: '.recieversShort('event', $event['id'], 'performer').'<hr>Informovaní: '.recieversShort('event', $event['id'], 'observer');

          $current = array(
              'title' => $event['title'],
              'url' => '/admin/pages/tasks/zobrazit-udalost?id=' . $event['id'],
              'className' => 'event',
              'description' => $event['title'] . '<hr>' . $allTargets . '<hr>'.$event['popis'],
          );

          $techniciansQuery = $mysqli->query("SELECT d.id FROM mails_recievers r, demands d WHERE r.type_id = '" . $event['id'] . "' AND r.admin_id = d.id AND d.role = 'technician' $recieverType AND r.type = 'event' AND d.active = 1") or die($mysqli->error);

          if (mysqli_num_rows($techniciansQuery) > 0 && mysqli_num_rows($techniciansQuery) < 2) {

              $technician = mysqli_fetch_assoc($techniciansQuery);
              $current['resourceId'] = $technician['id'];

          } elseif (mysqli_num_rows($techniciansQuery) > 0) {

              $resourceIdArray = array();
              while ($technician = mysqli_fetch_assoc($techniciansQuery)) {
                  array_push($resourceIdArray, $technician['id']);
              }
              $current['resourceIds'] = $resourceIdArray;

          }

          $date = date_start_end($event);

          $current['start'] = $date['start'];
          if(!empty($date['end'])){ $current['end'] = $date['end']; }

          $rows[] = $current;


          if(!empty($event['freq']) && !empty($event['count'])) {

              $total_repeat = $event['count'] * $event['rec_interval'];

              $i = 0;
              for ($i; $i < $total_repeat; $i++) {

                  if ($i % $event['rec_interval'] != 0 || $i == 0) { continue; }

                  if($event['freq'] == 'DAILY'){

                      $thisDate['date'] = date('Y-m-d', strtotime("+".$i." days", strtotime($event['date'])));
                      $thisDate['enddate'] = date('Y-m-d', strtotime("+".$i." days", strtotime($event['enddate'])));

                  }elseif($event['freq'] == 'WEEKLY'){

                      $thisDate['date'] = date('Y-m-d', strtotime("+".$i." weeks", strtotime($event['date'])));
                      $thisDate['enddate'] = date('Y-m-d', strtotime("+".$i." weeks", strtotime($event['enddate'])));

                  }elseif($event['freq'] == 'MONTHLY'){

                      $thisDate['date'] = date('Y-m-d', strtotime("+".$i." months", strtotime($event['date'])));
                      $thisDate['enddate'] = date('Y-m-d', strtotime("+".$i." months", strtotime($event['enddate'])));

                  }

                  $thisDate['time'] = $event['time'];
                  $thisDate['endtime'] = $event['endtime'];

                  $date = date_start_end($thisDate);

                  $current['start'] = $date['start'];
                  if(!empty($date['end'])){ $current['end'] = $date['end']; }

                  $rows[] = $current;

              }

          }


      }
    if ($show != 'all' && $show != 'technicians') {
        
        while ($event = mysqli_fetch_assoc($eventsQuery2)) {


            $allTargets = 'Proveditelé: '.recieversShort('event', $event['id'], 'performer').'<hr>Informovaní: '.recieversShort('event', $event['id'], 'observer');

            $current = array(
                'title' => $event['title'],
                'url' => '/admin/pages/tasks/zobrazit-udalost?id=' . $event['id'],
                'className' => 'event',
                'description' => $event['title'] . '<hr>' . $allTargets . '<hr>'.$event['popis'],
            );


            $date = date_start_end($event);

            $current['start'] = $date['start'];
            if(!empty($date['end'])){ $current['end'] = $date['end']; }

            $rows[] = $current;


            if(!empty($event['freq']) && !empty($event['count'])) {

                $total_repeat = $event['count'] * $event['rec_interval'];

                $i = 0;
                for ($i; $i < $total_repeat; $i++) {

                    if ($i % $event['rec_interval'] != 0 || $i == 0) { continue; }

                    if($event['freq'] == 'DAILY'){

                        $thisDate['date'] = date('Y-m-d', strtotime("+".$i." days", strtotime($event['date'])));
                        $thisDate['enddate'] = date('Y-m-d', strtotime("+".$i." days", strtotime($event['enddate'])));

                    }elseif($event['freq'] == 'WEEKLY'){

                        $thisDate['date'] = date('Y-m-d', strtotime("+".$i." weeks", strtotime($event['date'])));
                        $thisDate['enddate'] = date('Y-m-d', strtotime("+".$i." weeks", strtotime($event['enddate'])));

                    }elseif($event['freq'] == 'MONTHLY'){

                        $thisDate['date'] = date('Y-m-d', strtotime("+".$i." months", strtotime($event['date'])));
                        $thisDate['enddate'] = date('Y-m-d', strtotime("+".$i." months", strtotime($event['enddate'])));

                    }

                    $thisDate['time'] = $event['time'];
                    $thisDate['endtime'] = $event['endtime'];

                    $date = date_start_end($thisDate);

                    $current['start'] = $date['start'];
                    if(!empty($date['end'])){ $current['end'] = $date['end']; }

                    $rows[] = $current;

                }

            }

        }



    }
    if (!empty($rows)) {
        echo json_encode($rows);
    } else {
        echo json_encode(array());
    }
}else{
    echo json_encode(array());
}
