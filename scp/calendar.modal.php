<?php
require('staff.inc.php');
csrf_token();
?>
<style>

.mytable {
    font-family: arial, sans-serif;
    border-collapse: collapse;
    width: 100%;
}
.mytd, .myth {
    border: 1px solid #000000;
    text-align: left;
    padding: 8px;
}


.mytr:nth-child(even) {
    background-color: #dddddd;
}

</style>
<?php
$date = $_GET['date'];

	$string='';
	$events = array();
	$sql = "SELECT CONCAT_WS(' ',s.firstname,s.lastname) as Zaposleni, a.name, aa.opis, aa.cas_od
			FROM ost_staff s, ost_agent_aktivnost aa, ost_aktivnosti a
			WHERE s.staff_id = aa.staff_id and a.id=aa.aktivnost_id and '$date' BETWEEN aa.aktivnost_od AND aa.aktivnost_do";
	$result = db_query($sql) or die('Ne morem pridobiti rezultata!');
	$string.='<table class="mytable">';
	$string.='<th class="myth">Zaposleni</th><th class="myth">Vrsta aktivnosti</th><th class="myth">Opis</th>';
	while($row = db_fetch_array($result)) {
		$string.='<tr class="mytr">';
		$string.='<td class="mytd">'.$row['Zaposleni'].'</td><td class="mytd">'.$row['name'].' '.$row['cas_od'].'</td><td class="mytd">'.$row['opis'].'</td>';
		$string.='</tr>';
	}
	$string.='</table>';
	echo $string;
?>

	