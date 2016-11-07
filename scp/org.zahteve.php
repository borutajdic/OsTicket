<?php
require('staff.inc.php');
$nav->setTabActive('dashboard');
require(STAFFINC_DIR.'header.inc.php');
csrf_token();
?>
<script>
function showUser() {
	var org = document.getElementById("organizacija");
	var casOd = document.getElementById("cas_od").value;
	var casDo = document.getElementById("cas_do").value;
	var str = org.options[org.selectedIndex].value;
	if(!casOd || !casDo){
		document.getElementById("write").innerHTML = "Manjka datum";
        return;
	}
    if (str == "") {
        document.getElementById("txtHint").innerHTML = "";
        return;
    } 
	
	else { 
        if (window.XMLHttpRequest) {
            xmlhttp = new XMLHttpRequest();
        } else {
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                document.getElementById("txtHint").innerHTML = xmlhttp.responseText;
            }
        };
        xmlhttp.open("GET","org.get.php?q="+str+"&od="+casOd+"&do="+casDo,true);
        xmlhttp.send();
    }
}
function print(){
	var org = document.getElementById("organizacija");
	var casOd = document.getElementById("cas_od").value;
	var casDo = document.getElementById("cas_do").value;
	var str = org.options[org.selectedIndex].value;
	var myWindow = window.open("","Tiskanje","width=1000,height=700");
	if(!casOd || !casDo){
		document.getElementById("write").innerHTML = "Manjka datum";
        return;
	}
    if (str == "") {
        return;
    } 
	
	else { 
        if (window.XMLHttpRequest) {
            xmlhttp = new XMLHttpRequest();
        } else {
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                myWindow.document.write(xmlhttp.responseText);
            }
        };
        xmlhttp.open("GET","org.get.php?q="+str+"&od="+casOd+"&do="+casDo,true);
        xmlhttp.send();
		
    }
}
</script>
<meta name="viewport" content="width=device-width, initial-scale=1">
<form action="org.zahteve.php" method="post" enctype="multipart/form-data">
	<?php csrf_token(); ?>
	<table style="width:100%" border="0" cellspacing="0" cellpadding="3">
		<tr>
			<td>
				<label for="org_ticket"><b>Ime organizacije: </b></label>
				<select name="org" id="organizacija">
					<?php
					$dbquery="SELECT id,name FROM ost_organization ORDER BY name ASC";
					$dbresult=db_query($dbquery) or die ("Ne morem pridobiti podatkov: ".mysql_error());
					echo '<option value="" >Izberi organizacijo</option>';
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
    </table>
	<b>Čas od </b><input type="date" id="cas_od"></input>
	<b> Čas do </b><input type="date" id="cas_do"></input>
</form>

<br>
<div id="txtHint"><b id="write">Izberi podjetje in obdobje</b></div>
<br>
<input type="submit" onclick="showUser()" value="Prikaži"></input>
<button id="tisk" onclick="print()" name="print_page">Stran za tiskanje</button>
<?php
include(STAFFINC_DIR.'footer.inc.php');
?>