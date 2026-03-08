<?php

include $_SERVER['DOCUMENT_ROOT'] . "/admin/config/config.php";
include INCLUDES . "/functions.php";

$id = $_REQUEST['id'];

$searchquery = $mysqli->query("SELECT b.id FROM notifications n, notifications_bridge b WHERE n.what_type = '1' AND b.notification_id = n.id AND b.admin_id = '" . $client['id'] . "' AND b.viewed = 0 AND notification = 'newcomment' AND n.what_id='$id'") or die($mysqli->error);

$task_query = $mysqli->query('SELECT *, DATE_FORMAT(date, "%d. %m. %Y") as dateformated, DATE_FORMAT(due, "%d. %m. %Y") as dueformated FROM tasks WHERE id="' . $id . '"') or die($mysqli->error);

if (mysqli_num_rows($task_query) > 0) {

    $show_task = mysqli_fetch_array($task_query);

    $search = mysqli_fetch_array($searchquery);
    $update = $mysqli->query("UPDATE notifications_bridge SET viewed = '1' WHERE id = '" . $search['id'] . "'");

    $pagetitle = 'Úkol';

    $cliquery = $mysqli->query('SELECT id, user_name FROM demands WHERE role != "client" AND active = 1') or die($mysqli->error);
    $demquery = $mysqli->query('SELECT id, user_name FROM demands') or die($mysqli->error);


    include VIEW . '/default/header.php';

    ?>

<script type="text/javascript">

jQuery(document).ready(function($)
{



$('#duplicatereciever').click(function() {

$('#recivdup').clone(true).insertBefore("#duplicatereciever").attr('id', 'reciv').show();
$('#reciv #recieverdup').attr('name', 'reciever[]');
  event.preventDefault();

});

$('.removeshit').click(function() {
   $(this).closest('.hovnus').remove();
   event.preventDefault();
});




});
</script>

<div class="profile-env">
<section class="profile-feed" style="padding-left:15px;padding-right: 15px; width: 900px; margin: 0 auto;">
<?php

    task($show_task, $client['avatar'], $access_edit, 'pages/tasks/zobrazit-ukol?id=' . $show_task['id']);
    ?>

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