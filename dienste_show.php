<?php
// Datenbankverbindung & Kodierung einbinden
require_once ("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once ("inc/checkuser.inc.php");

// Titel festlegen
$title = "Dienste Detailansicht";

// HTML-Kopf einbinden
require_once ("inc/head.inc.php");
?>
<div id="content">
<?php
if (isset($_GET['id'])){
$sql = "SELECT * FROM `veranstaltungen` WHERE `veranstaltungs_id` = ".intval($_GET['id']);
	$veranstaltung_result = mysql_query($sql);
    echo mysql_error();
    if (mysql_num_rows($veranstaltung_result) == 1){
        $veranstaltung = mysql_fetch_assoc($veranstaltung_result);
        echo "<h1>Dienstplan von \"".htmlspecialchars($veranstaltung['veranstaltungsname'])."\"</h1>";
		$sql_dienste = "SELECT * FROM `dienste` LEFT JOIN `mitglieder` ON `dienste`.`person` = `mitglieder`.`mitglieder_id` WHERE `event` = ".intval($_GET['id']);
		$dienste_result = mysql_query($sql_dienste);
		
		function belegt($startzeit,$endzeit,$stand){
			global $dienste_result;
			if(mysql_num_rows($dienste_result) != 0){
				mysql_data_seek($dienste_result,0);
			}
			while ($dienst = mysql_fetch_assoc($dienste_result)) {
				//echo $dienst['startzeit'].$startzeit;
				//echo "<br />". $dienst['endzeit'].$endzeit;
				//echo "<br />". $dienst['stand'].$stand;
				if($dienst['startzeit'] <= $startzeit and $dienst['endzeit'] >= $endzeit and $dienst['stand'] == $stand){
					return $dienst;
				}
			}
			return FALSE;
		}

    		   if ($veranstaltung['mindienste'] >= 1){
    		     echo "<img src=\"images/show.png\" alt=\"\" class=\"imageinline\" title=\"Anzeigen\" /> <a href=\"veranstaltung_show.php?id=".$veranstaltung['veranstaltungs_id']."\" title=\"Die Veranstaltungsdetails von ".htmlspecialchars($veranstaltung['veranstaltungsname'])." anzeigen\">Veranstaltungsdetails anzeigen</a>";
    		     // Eventuelle, per GET übergebene Meldungen anzeigen
                 if (isset($_GET['action'])){
              		if($_GET['action'] == "success"){
          				echo "<div class=\"message success\"><p>Der Dienst wurde erfolgreich geändert.</p></div>";
          			}elseif($_GET['action'] == "fail"){
          				echo "<div class=\"message error\"><p>Der Dienst konnte nicht geändert werden.</p></div>";
          			}
          		 }
                 if (($veranstaltung['startzeit'] - time()) > 259200){
        		 	echo '<div class="message information"><p>Änderungen sind nur bis 72 Stunden vor Beginn der Veranstaltung, also bis zum '.date("d.m.Y \u\m H:i", $veranstaltung['startzeit'] - 259200).' Uhr, möglich!</p></div>';
    			 }elseif($veranstaltung['startzeit']  > time()){
    			    echo '<div class="message warning"><p>Änderungen sind an dieser Veranstaltung nicht mehr möglich, da der Zeitrahmen für Änderungen abgelaufen ist!</p></div>';
    			 }else{
    			   echo '<div class="message warning"><p>Änderungen sind an dieser Veranstaltung nicht mehr möglich, da sie bereits stattgefunden hat.</p></div>';
    			 }?>
    			           		<table>
    			                <tr><td class="firstcolumn" colspan="2">Vorbereitungen</td></tr>
                                 <tr><td class="firstcolumn">Dienste</td>
                                    <td>
    			              <?php
                                    //Wenn geforderte Zeitdienste in der DB eingetragen sind, diese auslesen und anzeigen
                                    if($veranstaltung['mindienste'] > 0){
    	                                $sql_dienste_stand = "SELECT * FROM `dienste` WHERE `dienstart` = 0 AND `event` = ".intval($_GET['id']);
    									$dienste_stand_result = mysql_query($sql_dienste_stand);
    									$diensteprozent = (($veranstaltung['endzeit']-$veranstaltung['startzeit'])/60/30)*$veranstaltung['mindienste']/100;
    									if($dienste_stand_result != FALSE){
    										//Prozentanzeige der erfüllten Zeitdienste einer Veranstlatung
    	   									$eingetragen = round(mysql_num_rows($dienste_stand_result)/$diensteprozent);
    	   									if($eingetragen > 100){
    	   										$eingetragen = 100;
    	   									}
    									}else{
    										$eingetragen = 0;
    									}
    									//Ausgabe
    									echo "<p>Bisher sind ca. $eingetragen% aller Dienste besetzt</p></td></tr>";
    								}
    								//Kuchen werden abgerufen auch wenn keine benötigten Kuchen eingetragen waren
    								$sql_kuchen = "SELECT * FROM `dienste` LEFT JOIN `mitglieder` ON `dienste`.`person` = `mitglieder`.`mitglieder_id` WHERE `dienstart` > 0 AND `event` = ".intval($_GET['id']);
    								$kuchen_result = mysql_query($sql_kuchen);
    								$kuchen = 0;
    								if($kuchen_result != FALSE and mysql_num_rows($kuchen_result) != 0){
    									while($kuchendienst = mysql_fetch_assoc($kuchen_result)){
    										$kuchen += $kuchendienst['dienstart'];
    									}
    								}
    								//Kuchenanzeige richtet sich nach Kuchenanzahl und ob benötigte Kuchen eingetragen wurden
    								echo '<tr><td class="firstcolumn">Kuchen</td>';
    								if($kuchen != 1){
    									echo "<td><p>Bisher sind ".$kuchen." Kuchen";
    								}else{
    									echo "<td><p>Bisher ist ".$kuchen." Kuchen";
    								}
    								if($veranstaltung['minkuchen'] > 0){
    									echo " von mindestens ".$veranstaltung['minkuchen'];
    								}
    								echo " Kuchen eingetragen</p>";
    								//Falls Kuchen eingetragen waren, eine Liste derer Ausgeben, die diese mitbringen
    								if($kuchen > 0){
    									echo "<p><b>Kuchenliste:</b></p><p>";
    									mysql_data_seek($kuchen_result,0);
    									$i = 0;
    									while($kuchendienst = mysql_fetch_assoc($kuchen_result)){
    										if($i != 0){
    											echo ", ";
    										}
    										echo $kuchendienst['vorname']." ".$kuchendienst['nachname'].": ".$kuchendienst['dienstart'];
    										$i++;
    									}
    									echo "</p>";
    								}
    								?>
                                    </td>
                                </tr>
                              </table>
    			  <h2>Kuchen</h2>
    			  <?php if($user['rights'] > 3 or ($user['rights'] == 3 and ($veranstaltung['startzeit'] - time()) > 259200) or $user['mitglieder_id'] == $veranstaltung['ansprechpartner']){
    						echo '<p><img src="images/edit.png" alt="" title="Kuchendienst" class=\"imageinline\" /> <a href="dienste_add.php?kuchen&amp;event=' . $veranstaltung['veranstaltungs_id'] . '&amp;action=add" >Jemanden für Kuchen eintragen</a></p>';
    					}else{?>
    				  <form id="kuchenform" class="formular" accept-charset="utf-8" method="get" action="dienste_process.php" enctype="">

    				  <p><label for="kuchen" style="width:auto;float:none;"><img src="images/edit.png" alt="" title="Kuchendienst" /> Ich bringe</label>
    				  <input type="text" name="kuchen" id="kuchen" value="<?php
    				  	//Zeiger im MYSQL Ergebnis wieder auf 0 setzen
    				  	//mysql_data_seek($dienste_result,0);
    					$kuchen = FALSE;
    					//Schon vorhandenen Kuchendienst suchen und eventuelle Anzahl in Input Feld schreiben
    					while ($dienst = mysql_fetch_assoc($dienste_result)) {
    						if($dienst['dienstart'] != 0 and $dienst['person'] == $user['mitglieder_id'] ){
    							echo $dienst['dienstart'];
    							$kuchen = TRUE;
    							break;
    						}
    					}
    					if(!$kuchen){
    						echo "0";
    					}
    				?>" class="xsmall" title="Kuchendienst eintragen" />
    				<label for="kuchen" style="width:auto;float:none;">Kuchen mit.</label>
    				<input type="hidden" name="event" value="<?php echo $veranstaltung['veranstaltungs_id'];?>" class="xsmall" title="Kuchendienst eintragen" />
    				<input type="hidden" name="action" value="add" class="xsmall" title="Kuchendienst eintragen" />
    				<input type="submit" value="Absenden" class="actionlinesearch" /></p>
                    </form>
    				<?php } ?>
    			  <h2>Dienste</h2>
        		  <?php

    			//Muss es einen langen Anfang oder ein langes Ende geben? Soll ein weiterer Dienst dazu genommen werden?
    			//Oder ist schon alles palletti?
    			//Startminute der Veranstaltung auslesen
    			$minute_start = date("i", $veranstaltung['startzeit']);
    			//Wenn die Veranstaltung nicht genau zu einer halben oder vollen Stunde beginnt muss der Startdienst bearbeitet werden da Dienste nur 30 Minuten lang sind
    			if ($minute_start != 30 and $minute_start != 0) {
    				//Ist die Startminute größer als 30 Minuten
    				if ($minute_start > 30) {
    					//Wenn die Startminute näher an der halben Stunde ist, wird ein ganzer 30 Minuten Dienst angefügt da
    					//Sonst der Startdienst länger als 45 Minuten wäre
    					if (($minute_start - 30) < 15) {
    						$anfang = $veranstaltung["startzeit"] - (($minute_start - 30) * 60);
    					} else {
    						//Andernfalls wird der Dienst um bis zu 15 Minuten verlängert
    						$anfang_lang = $veranstaltung["startzeit"];
    						$anfang = $veranstaltung["startzeit"] + ((90 - $minute_start) * 60);
    					}
    				} else {
    					if ($minute_start < 15) {
    						$anfang = $veranstaltung["startzeit"] - ($minute_start * 60);
    					} else {
    						$anfang_lang = $veranstaltung["startzeit"];
    						$anfang = $veranstaltung["startzeit"] + ((60 - $minute_start) * 60);
    					}
    				}
    			} else {
    				$anfang = $veranstaltung["startzeit"];
    			}
    			//Analog zu Startdienst
    			$minute_ende = date("i", $veranstaltung['endzeit']);
    			if ($minute_ende != 30 and $minute_ende != 0) {
    				if ($minute_ende > 30) {
    					if (($minute_ende - 30) < 15) {
    						$ende_lang = $veranstaltung["endzeit"];
    						$ende = $veranstaltung["endzeit"] - ($minute_ende * 60);
    					} else {
    						$ende = $veranstaltung["endzeit"] + ((60 - $minute_ende) * 60);
    					}
    				} else {
    					if (date("i", $veranstaltung["endzeit"]) < 15) {
    						$ende_lang = $veranstaltung["endzeit"];
    						$ende = $veranstaltung["endzeit"] - (($minute_ende + 30) * 60);
    					} else {
    						$ende = $veranstaltung["endzeit"] + ((30 - $minute_ende) * 60);
    					}
    				}
    			} else {
    				$ende = $veranstaltung["endzeit"];
    			}

    			if(isset($veranstaltung['dienstbeschreibung'])){
    				$dienstbeschreibung = explode("\n",$veranstaltung['dienstbeschreibung']);
    			}

    			//Wieviele Halbe Stunden gibt es?
    			$time = ($ende - $anfang) / 60 / 30;
    			echo '<table>';
    			if (isset($anfang_lang)) {
    				$starttag = $anfang_lang;

    			} else {
    				$starttag = $anfang;
    			}

    			//Starttag
    			echo '<thead><tr><th>' . date("d.m.Y", $starttag) . '</th>';
    			for ($a = 1; $a <= $veranstaltung['mindienste']; $a++) {
    				if(isset($dienstbeschreibung[$a-1]) and strlen($dienstbeschreibung[$a-1]) > 4){
    					echo "<th>".htmlspecialchars($dienstbeschreibung[$a-1])."</th>";
    				}else{
    					echo "<th>Stand $a</th>";
    				}
    			}
    			echo "</tr></thead><tbody>";

    			//Falls Anfang lang ist
    			if (isset($anfang_lang)) {
    				echo '<tr><td class="firstcolumn breit115">' . date("H:i", $anfang_lang) . " - " . date("H:i", $anfang) . '</td>';
    				for ($a = 1; $a <= $veranstaltung['mindienste']; $a++) {
    					$belegt = belegt($anfang_lang, $anfang, $a);
    					if (!$belegt) {
    						if($user['rights'] > 3 or ($user['rights'] == 3 and ($veranstaltung['startzeit'] - time()) > 86400) or $user['mitglieder_id'] == $veranstaltung['ansprechpartner']){
    							echo '<td><a href="dienste_add.php?stand=' . $a . '&amp;anfang=' . $anfang_lang . '&amp;ende=' . $anfang . '&amp;event=' . $veranstaltung['veranstaltungs_id'] . '&amp;action=add" >Eintragen</a></td>';
    						}else{
    							echo '<td><a href="dienste_process.php?stand=' . $a . '&amp;anfang=' . $anfang_lang . '&amp;ende=' . $anfang . '&amp;event=' . $veranstaltung['veranstaltungs_id'] . '&amp;action=add" >Eintragen</a></td>';
    						}
    					} else {
    						if ($belegt['mitglieder_id'] == $user['mitglieder_id'] or $user['rights'] > 3 or ($user['rights'] == 3 and ($veranstaltung['startzeit'] - time()) > 86400) or $user['mitglieder_id'] == $veranstaltung['ansprechpartner']) {
    							echo '<td>' . $belegt['vorname'] . " " . $belegt['nachname'] . ' <a href="dienste_process.php?dienst_id=' . $belegt['dienst_id'] . '&amp;event=' . $veranstaltung['veranstaltungs_id'] . '&amp;action=remove"><img src="images/cross.png" alt="Austragen" title="Austragen" class="imageinline" /></a></td>';
    						} else {
    							echo '<td class="graubg">' . $belegt['vorname'] . " " . $belegt['nachname'] . '</td>';
    						}
    					}
    				}
    				echo "</tr>";
    			}
    			//Hauptschleife
    			for ($i = 0; $i < $time; $i++) {
    				//Timestamp aktueller Dienst
    				$now = $anfang + $i * 1800;
    				$then = $now + 1800;
    				//Hat sich der Tag geändert?
    				if (date("d.m.Y", $now) != date("d.m.Y", $starttag)) {
    					//Neuen Tag setzen und ausgeben
    					$starttag = $now;
    					echo '<tr><td class="firstcolumn breit115">' . date("d.m.Y", $starttag) . '</td>';
    					for ($a = 1; $a <= $veranstaltung['mindienste']; $a++) {
    						if(isset($dienstbeschreibung[$a-1]) and strlen($dienstbeschreibung[$a-1]) > 4 and substr($dienstbeschreibung[$a-1],0,2) == ($a).":"){
    							echo "<td class=\"firstcolumn\">".htmlspecialchars($dienstbeschreibung[$a-1])."</td>";
    						}else{
    							echo "<td class=\"firstcolumn\">Stand $a</td>";
    						}
    					}
    					echo '</tr>';
    				}
    				//Dienstdauer ausgeben
    				echo '<tr><td class="firstcolumn breit115">' . date("H:i", $now) . " - " . date("H:i", $then) . '</td>';
    				//Slots für mehrere Dienste generieren
    				for ($a = 1; $a <= $veranstaltung['mindienste']; $a++) {
    					$belegt = belegt($now, $then, $a);
    					if (!$belegt) {
    						if($user['rights'] > 3 or ($user['rights'] == 3 and ($veranstaltung['startzeit'] - time()) > 86400) or $user['mitglieder_id'] == $veranstaltung['ansprechpartner']){
    							echo '<td><a href="dienste_add.php?stand=' . $a . '&amp;anfang=' . $now . '&amp;ende=' . $then . '&amp;event=' . $veranstaltung['veranstaltungs_id'] . '&amp;action=add" >Eintragen</a></td>';
    						}else{
    							echo '<td><a href="dienste_process.php?stand=' . $a . '&amp;anfang=' . $now . '&amp;ende=' . $then . '&amp;event=' . $veranstaltung['veranstaltungs_id'] . '&amp;action=add" >Eintragen</a></td>';
    						}
    					} else {
    						if ($belegt['mitglieder_id'] == $user['mitglieder_id'] or $user['rights'] > 3 or ($user['rights'] == 3 and ($veranstaltung['startzeit'] - time()) > 86400) or $user['mitglieder_id'] == $veranstaltung['ansprechpartner']) {
    							echo '<td>' . $belegt['vorname'] . " " . $belegt['nachname'] . ' <a href="dienste_process.php?dienst_id=' . $belegt['dienst_id'] . '&amp;event=' . $veranstaltung['veranstaltungs_id'] . '&amp;action=remove"><img src="images/cross.png" alt="Austragen" title="Austragen" class="imageinline" /></a></td>';
    						} else {
    							echo '<td class="graubg">' . $belegt['vorname'] . " " . $belegt['nachname'] . '</td>';
    						}
    					}
    				}
    				echo "</tr>";

    			}

    			//Falls Ende Lang ist
    			if (isset($ende_lang)) {
    				echo '<tr><td class="firstcolumn breit115">' . date("H:i", $ende) . " - " . date("H:i", $ende_lang) . '</td>';
    				for ($a = 1; $a <= $veranstaltung['mindienste']; $a++) {
    					$belegt = belegt($ende, $ende_lang, $a);
    					if (!$belegt) {
    						if($user['rights'] > 3 or ($user['rights'] == 3 and ($veranstaltung['startzeit'] - time()) > 86400) or $user['mitglieder_id'] == $veranstaltung['ansprechpartner']){
    							echo '<td><a href="dienste_add.php?stand=' . $a . '&amp;anfang=' . $ende . '&amp;ende=' . $ende_lang . '&amp;event=' . $veranstaltung['veranstaltungs_id'] . '&amp;action=add" >Eintragen</a></td>';
    						}else{
    							echo '<td><a href="dienste_process.php?stand=' . $a . '&amp;anfang=' . $ende . '&amp;ende=' . $ende_lang . '&amp;event=' . $veranstaltung['veranstaltungs_id'] . '&amp;action=add" >Eintragen</a></td>';
    						}
    					} else {
    						if ($belegt['mitglieder_id'] == $user['mitglieder_id'] or $user['rights'] > 3 or ($user['rights'] == 3 and ($veranstaltung['startzeit'] - time()) > 86400) or $user['mitglieder_id'] == $veranstaltung['ansprechpartner']) {
    							echo '<td>' . $belegt['vorname'] . " " . $belegt['nachname'] . ' <a href="dienste_process.php?dienst_id=' . $belegt['dienst_id'] . '&amp;event=' . $veranstaltung['veranstaltungs_id'] . '&amp;action=remove">><img src="images/cross.png" alt="Austragen" title="Austragen" class="imageinline" /></a></td>';
    						} else {
    							echo '<td class="graubg">' . $belegt['vorname'] . " " . $belegt['nachname'] . '</td>';
    						}
    					}
    				}
    				echo "</tr>";
    			}
    			echo "</tbody></table>";
    			?>

    <?php }else{?>
    		<h2>Dienste anzeigen</h2>
            <p class="message error">Es wurden keine vordefinierten Dienste gefunden!<br /><a href="veranstaltung_show.php?id=<?php echo $_GET['id'];?>" title="Übersicht der Veranstaltung anzeigen">Zu Veranstaltungsdetails</a></p>
			
   	<?php
   	}
	}else{
	?> 
            <h2>Dienste anzeigen</h2>
            <p class="message error">Es wurde keine Veranstaltung mit dieser ID gefunden!<br /><a href="veranstaltungen.php" title="Übersicht aller Veranstaltungen anzeigen">Zur Veranstaltungsverwaltung</a></p>

<?php 
	 }
}else{
 ?>
<h2>Dienste anzeigen</h2>
<p class="message error">Es wurde keine Veranstaltungs-ID übergeben!<br /><a href="veranstaltungen.php" title="Übersicht aller Veranstaltungen anzeigen">Zur Veranstaltungsverwaltung</a></p>

    <?php } ?>
	<div class="clearit">&nbsp;</div>
</div>



<?php
// HTML-Fußbereich einbinden
require_once ("inc/footer.inc.php");
?>