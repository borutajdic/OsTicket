<?php
if(!defined('OSTSCPINC') || !$thisstaff) die('Access Denied');
$staff_ID = $thisstaff -> getId()
?>
<form action="tickets.php?a=aktivnost" method="post" enctype="multipart/form-data">
 <?php csrf_token(); ?>
 <h2><?php echo __('Dodaj aktivnost');?></h2>
 <table style="width:100%" border="0" cellspacing="0" cellpadding="3">
			<tr>
				<td>
					<label for="set_aktivnost"><b><?php echo __('Vrsta aktivnosti: ');?></b></label>
					<select name="aktiv">
						<?php
						$dbquery="SELECT name,id FROM ost_aktivnosti";
						$dbresult=db_query($dbquery) or die ("Ne morem pridobiti podatkov: ".mysql_error());
						while ($dbrow=db_fetch_array($dbresult)) {
						$dbTitle=$dbrow['name'];
						$dbCode=$dbrow['id'];
							echo "<option value=$dbCode>
								$dbTitle
							</option>";
						}	
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td>
					<b>Aktivnost od </b><input type="date" name="aktivnost_od"><input type="time" name="cas_od" value="00:00">
				</td>
				<td>
					<b>Aktivnost do </b><input type="date" name="aktivnost_do"><input type="time" name="cas_do" value="00:00">
				</td>
			</tr>
			<tr>
				<td>
					<b>Kratek opis: </b><textarea rows="4" cols="50" style="resize: none;" name="opis"></textarea>
				</td>
			</tr>
</table>
<p style="padding-left:165px;">
    <input type="submit" name="aktivno" value="<?php echo __('Objavi aktivnost');?>">
</p>
<?php

if(isset($_POST['aktivno'])){
	$aktivnost = $_POST['aktiv'];
	if($_POST['aktivnost_od'] && $_POST['aktivnost_do']){
		$sql = 'INSERT INTO ost_agent_aktivnost(staff_id, aktivnost_id, aktivnost_od, aktivnost_do,created ,cas_od, cas_do,opis)'
				.' VALUES (' .db_input($thisstaff -> getId()). ',' .db_input($aktivnost). ',' .db_input($_POST['aktivnost_od']). ',' .db_input($_POST['aktivnost_do']). ',NOW(),' .db_input($_POST['cas_od']). ',' .db_input($_POST['cas_do']). ',' .db_input($_POST['opis']). ')';
		db_query($sql);
	}
	else if(!$_POST['aktivnost_od'] && $_POST['aktivnost_do']){
		echo "Manjka začetni datum";
	}
	else if(!$_POST['aktivnost_do'] && $_POST['aktivnost_od']){
		echo "Manjka končni datum";
	}
	else{
		echo "Manjkata začetni in končni datum";
	}
}
echo '<br>';

if(isset($_POST['deleteButton'])){
	if(isset($_POST['akt'])){
		foreach($_POST['akt'] as $a){
			$sql = 'DELETE FROM ost_agent_aktivnost WHERE '.$a.' = id';
			db_query($sql) or die("Ni bilo mogoče izbrisati");
		}
	}
}

if(isset($_POST['prihod'])){
	$sql = "UPDATE ost_staff SET isAtWork = 1 WHERE staff_id = $staff_ID";
	db_query($sql) or die ("Ne morem posodobiti podatkov: ".mysql_error());
	$sql = 'INSERT INTO ost_agent_aktivnost(staff_id, aktivnost_id, aktivnost_od, aktivnost_do,created ,cas_od, cas_do)'
				.' VALUES (' .db_input($thisstaff -> getId()). ',10,NOW(),NOW(),NOW(),NOW(),NOW())';
	db_query($sql) or die("Ni bilo mogoče izvesti");
}

if(isset($_POST['odhod'])){
	$sql = "UPDATE ost_staff SET isAtWork = 0 WHERE staff_id = $staff_ID";
	db_query($sql) or die ("Ne morem posodobiti podatkov: ".mysql_error());
	
	$sql = 'INSERT INTO ost_agent_aktivnost(staff_id, aktivnost_id, aktivnost_od, aktivnost_do,created ,cas_od, cas_do)'
				.' VALUES (' .db_input($thisstaff -> getId()). ',9,NOW(),NOW(),NOW(),NOW(),NOW())';
	db_query($sql) or die("Ni bilo mogoče izvesti");
}
?>
<p>
	<?php
	$dbquery="SELECT isAtWork FROM ost_staff WHERE staff_id = $staff_ID";
	$dbresult=db_query($dbquery) or die ("Ne morem pridobiti podatkov: ".mysql_error());
	$isWorking;
	while ($dbrow=db_fetch_array($dbresult)) {
		$isWorking = $dbrow['isAtWork'];
	}	
	if($isWorking == 0){?>
		<input type="submit" name="prihod" value="<?php echo __('Potrdi prihod');?>" href="tickets.php?a=aktivnost">
	<?php
	}
	else{?>
		<input type="submit" name="odhod" value="<?php echo __('Potrdi odhod');?>" href="tickets.php?a=aktivnost">
	<?php
	}
	?>
</p>
<br>
<?php 
$sql1 = "SELECT a.name, DATE_FORMAT(aa.aktivnost_od,'%d-%m-%Y') AS od, DATE_FORMAT(aa.aktivnost_do,'%d-%m-%Y') AS do, aa.id, aa.cas_od, aa.cas_do, aa.opis,aa.aktivnost_id
			FROM ost_aktivnosti a, ost_agent_aktivnost aa
			WHERE a.id = aa.aktivnost_id and aa.staff_id = $staff_ID
			ORDER BY aa.created DESC";
$sqlresult = db_query($sql1) or die ("Ne morem pridobiti podatkov");
while($sqlrow=db_fetch_array($sqlresult)){
	$name = $sqlrow['name'];
	$aktivnost_od = $sqlrow['od'];
	$aktivnost_do = $sqlrow['do'];
	$cas_od = $sqlrow['cas_od'];
	$cas_do = $sqlrow['cas_do'];
	$opis = $sqlrow['opis'];
	
	if($sqlrow['aktivnost_id'] != 10 && $sqlrow['aktivnost_id'] != 9)echo '<input type="checkbox" name="akt[]" value='.$sqlrow['id'].'>'.$name.': od '.$aktivnost_od.' ' .$cas_od. ' do '.$aktivnost_do. ' ' .$cas_do. ': ' .$opis. '<br>';
}
?>
	<p  style="padding-left:165px;">
        <input type="submit" name="deleteButton" value="<?php echo __('Izbrisi');?>">
    </p>
</form>
<?php
include(STAFFINC_DIR.'footer.inc.php');
?>