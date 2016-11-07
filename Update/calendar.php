

<?php
require('staff.inc.php');
$nav->setTabActive('dashboard');
require(STAFFINC_DIR.'header.inc.php');


csrf_token();
?>

<link rel="stylesheet" href="css/calendar.css" type="text/css">

<style>
/* The Modal (background) */
.modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1; /* Sit on top */
    padding-top: 80px; /* Location of the box */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgb(0,0,0); /* Fallback color */
    background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
}

/* Modal Content */
.content {
    position: relative;
    background-color: #fefefe;
    margin: auto;
    padding: 0;
    border: 1px solid #888;
    width: 80%;
    box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19);
    -webkit-animation-name: animatetop;
    -webkit-animation-duration: 0.4s;
    animation-name: animatetop;
    animation-duration: 0.4s
}
.modal-header {
    padding: 23px;
    background-color: #dddddd;
    color: white;
}

/* Add Animation */
@-webkit-keyframes animatetop {
    from {top:-300px; opacity:0}
    to {top:0; opacity:1}
}

@keyframes animatetop {
    from {top:-300px; opacity:0}
    to {top:0; opacity:1}
}

/* The Close Button */
.close {
    color: black;
    float: right;
    font-size: 20px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: #FF0000;
    text-decoration: none;
    cursor: pointer;
}
</style>
<script>
function modal(id){
	var modal = document.getElementById('myModal');
	var btn = document.getElementById(id);
	var span = document.getElementsByClassName("close")[0];
	btn.onclick = function() {
		modal.style.display = "block";
		var xhttp;
		if (window.XMLHttpRequest){
			xhttp = new XMLHttpRequest();
		}
		else{
			xhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
		xhttp.onreadystatechange = function() {
			if (xhttp.readyState == 4 && xhttp.status == 200) {
			  document.getElementById("show_data").innerHTML = xhttp.responseText;
			}
		};
		xhttp.open("GET", "calendar.modal.php?date="+id, true);
		xhttp.send();
	}

	span.onclick = function() {
		modal.style.display = "none";
	}

	window.onclick = function(event) {
		if (event.target == modal) {
			modal.style.display = "none";
		}
	}
	
	
}

function getData() {
	var xhttp;
	if (window.XMLHttpRequest){
		xmlhttp = new XMLHttpRequest();
	}
	else{
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xhttp.onreadystatechange = function() {
		if (xhttp.readyState == 4 && xhttp.status == 200) {
		  document.getElementById("show_data").innerHTML = xhttp.responseText;
		}
	};
	xhttp.open("GET", "calendar.modal.php?date="+document.getElementByName("test").getAttribute('id'), true);
	xhttp.send();
}
</script>
<?php
/* draws a calendar */

function draw_calendar($month,$year){
	/*Naredi koledar*/
	$calendar = '<table cellpadding="0" cellspacing="0" class="calendar">';
	
	$headings = array('Nedelja','Ponedeljek','Torek','Sreda','Cetrtek','Petek','Sobota');
	$calendar.= '<tr class="calendar-row"><td class="calendar-day-head">'.implode('</td><td class="calendar-day-head">',$headings).'</td></tr>';

	$running_day = date('w',mktime(0,0,0,$month,1,$year));
	$days_in_month = date('t',mktime(0,0,0,$month,1,$year));
	$days_in_this_week = 1;
	$day_counter = 0;
	$dates_array = array();

	$calendar.= '<tr class="calendar-row">';

	for($x = 0; $x < $running_day; $x++):
		$calendar.= '<td class="calendar-day-np">&nbsp;</td>';
		$days_in_this_week++;
	endfor;

	for($list_day = 1; $list_day <= $days_in_month; $list_day++):
		if($list_day < 10) {
            $list_day = str_pad($list_day, 2, '0', STR_PAD_LEFT);
        }
		$month = str_pad($month, 2, '0', STR_PAD_LEFT);
		$event_day = $year.'-'.$month.'-'.$list_day;

		$calendar.= '<td class="calendar-day"><div name="test" style="height:100px; width:120px; overflow: auto; white-space:nowrap" id=' .$event_day. ' onclick="modal(this.id);">';
		$calendar.= '<div>'.$list_day.'</div>';	
		$query = "SELECT CONCAT_WS(' ',s.firstname,s.lastname) as Zaposleni, a.name, aa.aktivnost_id
			FROM ost_staff s, ost_agent_aktivnost aa, ost_aktivnosti a
			WHERE s.staff_id = aa.staff_id and a.id=aa.aktivnost_id and '$event_day' BETWEEN aa.aktivnost_od AND aa.aktivnost_do AND aa.aktivnost_id > 1 AND aa.aktivnost_id != 9 AND aa.aktivnost_id != 10";
			$result = db_query($query) or die('Ne morem pridobiti rezultata!');
			while($row = db_fetch_array($result)) {
				$words = explode(' ',$row['Zaposleni']);
				$calendar .= '<div>'.$words[0][0].'. '.$words[1][0].'. : '.$row['name'].'</div>';
			}
		$calendar.= '</div></td>';
		if($running_day == 6):
			$calendar.= '</tr>';
			if(($day_counter+1) != $days_in_month):
				$calendar.= '<tr class="calendar-row">';
			endif;
			$running_day = -1;
			$days_in_this_week = 0;
		endif;
		$days_in_this_week++; $running_day++; $day_counter++;
	endfor;

	if($days_in_this_week < 8):
		for($x = 1; $x <= (8 - $days_in_this_week); $x++):
			$calendar.= '<td class="calendar-day-np">&nbsp;</td>';
		endfor;
	endif;

	$calendar.= '</tr>';

	$calendar.= '</table>';

	$calendar = str_replace('</td>','</td>'."\n",$calendar);
	$calendar = str_replace('</tr>','</tr>'."\n",$calendar);

	return $calendar;
}

$month = (int) ($_GET['month'] ? $_GET['month'] : date('m'));
$year = (int)  ($_GET['year'] ? $_GET['year'] : date('Y'));

$select_month_control = '<select name="month" id="month">';
for($x = 1; $x <= 12; $x++) {
	$select_month_control.= '<option value="'.$x.'"'.($x != $month ? '' : ' selected="selected"').'>'.date('F',mktime(0,0,0,$x,1,$year)).'</option>';
}
$select_month_control.= '</select>';

$year_range = 7;
$select_year_control = '<select name="year" id="year">';
for($x = ($year-floor($year_range/2)); $x <= ($year+floor($year_range/2)); $x++) {
	$select_year_control.= '<option value="'.$x.'"'.($x != $year ? '' : ' selected="selected"').'>'.$x.'</option>';
}
$select_year_control.= '</select>';

$next_month_link = '<a href="?month='.($month != 12 ? $month + 1 : 1).'&year='.($month != 12 ? $year : $year + 1).'" class="control">Naslednji mesec &gt;&gt;</a>';

$previous_month_link = '<a href="?month='.($month != 1 ? $month - 1 : 12).'&year='.($month != 1 ? $year : $year - 1).'" class="control">&lt;&lt; 	Prejsni mesec</a>';

$controls = '<form method="get">'.$select_month_control.$select_year_control.'&nbsp;<input type="submit" name="submit" value="Pojdi" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$previous_month_link.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$next_month_link.' </form>';

echo '<h2>Aktivnosti delavcev</h2><br><br>';
echo '<h2 style="float:left; padding-right:30px;">'.date('F',mktime(0,0,0,$month,1,$year)).' '.$year.'</h2>';
echo '<div style="float:left;">'.$controls.'</div>';
echo '<div style="clear:both;"></div>';
echo '<br />';
echo draw_calendar($month,$year);
echo '<br /><br />';
?>
<!--Modal-->
<div id="myModal" class="modal">
	<!----Modal content-->
	<div class="content">
		<div class="modal-header">
			<span class="close">X</span>
		</div>
		<div id = "show_data"></div>
	</div>
</div>