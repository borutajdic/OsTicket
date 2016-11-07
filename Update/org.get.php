<?php
require('staff.inc.php');
csrf_token();
if (!isset($_SERVER['HTTP_REFERER'])){
	die("Direktni dostop ni mogoč!");
}
$q = intval($_GET['q']);
$od = $_GET['od'];
$do = $_GET['do'];
$sql1 = "SELECT t.ticket_id AS ticket, o.id AS id,o.name AS name, s.id AS sla_id, s.name AS sla_name, s.notes AS sla_notes, t.number AS number
		FROM ost_organization o
		LEFT JOIN ost_user u ON (u.org_id = o.id)
		LEFT JOIN ost_ticket t ON (t.user_id = u.id)
		LEFT JOIN ost_sla s ON (t.sla_id = s.id)
		WHERE o.id = $q AND (t.closed BETWEEN '$od' AND '$do')
		ORDER BY t.number
		";
	
$result1 = db_query($sql1);

echo '<style>
	@media print {
		.break {page-break-after: always;}
	}
	</style>';
echo '<div style="overflow-x:auto; overflow-y:auto;">';
$date = date_format($od,'Y-m');
while($orgs = db_fetch_array($result1)){
	echo '<div style="border:black solid 1px; background-color:#dddddd; padding:5px">
			<b>Obračun ur za obdobje od '.$od.' do '.$do.'</b>
			<b style="float:right;">3tav d.o.o.</b>
			<br>
			<br>
			<b>'.$orgs['id'].'-'.$orgs['name'].'</b>
			</div>
			<div style="border:black solid 1px; padding:10px">
			<b>'.$orgs['sla_id'].' - '.$orgs['sla_name'].' - '.$orgs['sla_notes'].'</b>
			</div>';
			
	echo '<table class="break" style="width:100%;border:black solid 1px;  border-collapse: collapse;">
			<tr>
			<td style="text-align: left; font-size:15px; font-weight: bold; caption-side: bottom;  white-space: nowrap; border-bottom:black solid 1px;">Zahtevek - '.$orgs['number'].'</td>
			<td style="border-bottom:black solid 1px;"> </td>
			<td style="border-bottom:black solid 1px;"> </td>
			</tr>';
	$ticket =  $orgs['ticket'];
	$sql = "SELECT date_format(tt.created,'%d-%m-%Y') AS created, c.subject AS subject, tt.body AS vsebina, tt.poster, tt.staff_id
			FROM ost_ticket t
			LEFT JOIN ost_user u ON (t.user_id = u.id)
			LEFT JOIN ost_ticket__cdata c ON (t.ticket_id = c.ticket_id)
			LEFT JOIN ost_ticket_thread tt ON (t.ticket_id = tt.ticket_id)
			WHERE (t.closed BETWEEN '$od' AND '$do') AND t.ticket_id = $ticket AND (tt.thread_type = 'M' OR tt.thread_type = 'R')
			";
	if(!$result = db_query($sql)) die("Neki je narobe 1");
	while($row = db_fetch_array($result)){
		if($row['staff_id'] == 0) echo '<tr style="background-color:#dddddd">';
		else echo '<tr>';
		echo '<td style=" white-space: nowrap; padding-left: 15px;">'.$row['created'].' '.$row['poster'].'</td>
				<td style="padding-left: 15px;"><strong>'.$row['subject'].': </strong>'.$row['vsebina'].' </td>
				<td> </td>
				</tr>
				<tr style ="height: 15px"><td colspan="3"></td></tr>';
	}
	$sql = "SELECT date_format(t.closed,'%d-%m-%Y') AS closed, tt.time_spent AS time, tt.opis AS opis, d.opis AS delo, CONCAT_WS(' ',s.firstname,s.lastname) as name, date_format(tt.created, '%d-%m-%Y') AS created
			FROM ost_ticket t
			LEFT JOIN ost_ticket_time tt ON (t.ticket_id = tt.ticket_id)
			LEFT JOIN ost_vrsta_dela d ON (d.id = tt.vrsta_dela_id)
			LEFT JOIN ost_staff s ON (tt.staff_id = s.staff_id)
			WHERE (t.closed BETWEEN '$od' AND '$do') AND t.ticket_id = $ticket
			ORDER BY created
			";
	if(!$result = db_query($sql)) die("Neki je narobe");
	while($row = db_fetch_array($result)){
		echo '<tr>
			<td style="padding-left: 15px; border-top:black solid 1px;">'.$row['created'].' '.$row['name'].'</td>
			<td style="padding-left: 15px; border-top:black solid 1px;">'.$row['opis'].'</td>
			<td style="white-space: nowrap; border-top:black solid 1px;text-align:right">'.$row['delo'].'   <span style="font-weight:bold">'.$row['time'].'</span></td>
			</tr>
			';
	}
	$sql = "SELECT sum(tt.time_spent) AS time
			FROM ost_ticket t
			LEFT JOIN ost_ticket_time tt ON (t.ticket_id = tt.ticket_id)
			WHERE (t.closed BETWEEN '$od' AND '$do') AND t.ticket_id = $ticket
			GROUP BY t.ticket_id,t.created
			";
	if(!$result = db_query($sql)) die("Neki je narobe");
	while($row = db_fetch_array($result)){
		echo '<tr>
				<td> </td>
				<td> </td>
				<td style="white-space: nowrap; border-top:black solid 1px;text-align:right;">Skupaj v zahtevku '.$ticket.'  <span style="font-weight:bold">'.$row['time'].'</span></td>
				</tr>';
	}
	echo '</table>';
	echo '<br>';
}
$sql = "SELECT sum(tt.time_spent) AS time, d.opis
			FROM ost_ticket t
			LEFT JOIN ost_ticket_time tt ON (t.ticket_id = tt.ticket_id)
			LEFT JOIN ost_vrsta_dela d ON (tt.vrsta_dela_id = d.id)
			LEFT JOIN ost_user u ON (t.user_id = u.id)
			LEFT JOIN ost_organization o ON (u.org_id = o.id)
			WHERE o.id = $q AND (t.closed BETWEEN '$od' AND '$do')
			GROUP BY tt.vrsta_dela_id
			";
	if(!$result = db_query($sql)) die("Neki je narobe");
	
	echo '<table style="border:solid 1px; width:100%;">';
	while($row = db_fetch_array($result)){
		echo '<tr style="text-align:right"><td>'.$row['opis'].'   <span style="font-weight:bold">'.$row['time'].'</span></td></tr>';
	}
	echo '</table>';
echo '</div>';
?>