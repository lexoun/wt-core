<?php
$count = 0;
$lookup_query = $mysqli->query("SELECT COUNT(*) as total FROM mails_recievers realizations, demands d WHERE d.realization = CURDATE() AND realizations.admin_id = '" . $client['id'] . "' AND d.id = realizations.type_id AND (realizations.type = 'realization_hottub' OR realizations.type = 'realization_sauna') AND realizations.reciever_type = 'performer' AND d.status != 5 ORDER BY d.realization") or die($mysqli->error);
$count_dummy = mysqli_fetch_array($lookup_query);

$count += $count_dummy['total'];

$lookup_query = $mysqli->query("SELECT COUNT(*) as total FROM services s, mails_recievers t WHERE s.date = CURDATE() AND t.admin_id = '" . $client['id'] . "' AND s.id = t.type_id AND t.type = 'service' AND s.status < 3") or die($mysqli->error);
$count_dummy = mysqli_fetch_array($lookup_query);

$count += $count_dummy['total'];

$lookup_query = $mysqli->query("SELECT COUNT(*) as total FROM mails_recievers tasks, tasks t LEFT JOIN demands  c ON c.id = t.client_id LEFT JOIN demands d ON d.id = t.demand_id WHERE t.due = CURDATE() AND tasks.admin_id = '" . $client['id'] . "' AND tasks.type = 'task' AND t.id = tasks.type_id AND t.status != 3") or die($mysqli->error);
$count_dummy = mysqli_fetch_array($lookup_query);

$count += $count_dummy['total'];

$lookup_query = $mysqli->query("SELECT COUNT(*) as total FROM mails_recievers r, dashboard_texts t LEFT JOIN demands d ON t.demand_id = d.id WHERE t.date = CURDATE() AND r.admin_id = '" . $client['id'] . "' AND t.id = r.type_id") or die($mysqli->error);
$count_dummy = mysqli_fetch_array($lookup_query);

$count += $count_dummy['total'];

?>



		<?php if ($access_calendar) { ?>

		<ul class="list-inline links-list pull-left" style="padding: 0; float: right !important;">


			<a href="/admin/pages/system/denni-plan" style="color: #FFF;"> <li class="btn btn-md btn-primary" style="padding: 8px 12px 8px; border: 1px solid #454a54;">
				<i class="entypo-calendar" style="font-size: 13px; padding-right: 2px;"></i>
				<span class="badge badge-secondary" style="margin-left: 5px; background-color: #ee4749; color: #FFFFFF; margin-top: 1px"><?= $count ?></span>
			</li></a>
		</ul>

	<?php }?>



