<?php
// Datenbankverbindung & Kodierung einbinden
require_once ("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once ("inc/checkuser.inc.php");

// Titel festlegen
$title = "Veranstaltungen und Dienste";

//Javascript einbinden für Autorefresh nach Änderung der Filtereinstellung
$appendjs = '$("form select").change(function(){
$("#searchform input:submit").click();;
});';

// HTML-Kopf einbinden
require_once ("inc/head.inc.php");
?>
<div id="content">
	<h2>Veranstaltungs- und Diensteverwaltung</h2>
	<form id="searchform" class="formular" accept-charset="utf-8" method="get" action="veranstaltungen.php" enctype="">
		<p class="actionline">
			<span class="actionlinesearchform"> <img src="images/show.png" alt="" title="Veranstaltungen mit Diensten"  />
				<select name="detail" class="medium">
					<option value="all">Alle Veranstaltungen</option>
					<option <?php
					//Filter für Veranstaltungen
					//Einträge vorselektieren bei übetragenen GET Werten
					if (isset($_GET['detail']) and $_GET['detail'] == "eigene") { echo "selected=\"selected\"";
					}
					?> value="eigene">Eigene Veranstaltungen</option>
					<option <?php
					if (isset($_GET['detail']) and $_GET['detail'] == "dienste") { echo "selected=\"selected\"";
					}
					?> value="dienste">Mit eingetragenen Diensten</option>
				</select> <img src="images/clock.png" alt="" title="Veranstaltungen mit Diensten"  />
				<select name="time" title="Veranstaltungszeit wählen" class="medium">
					<option value="all">Kommende und Vergangene</option>
					<option <?php
					//Filter für Veranstaltungen
					//Einträge vorselektieren bei übetragenen GET Werten
					if (isset($_GET['time']) and $_GET['time'] == "neu") { echo "selected=\"selected\"";
					}
					?> value="neu">Nur Kommende</option>
					<option <?php
					if (isset($_GET['time']) and $_GET['time'] == "alt") { echo "selected=\"selected\"";
					}
					?> value="alt">Nur Vergangene</option>
				</select> <img src="images/magnifier.png" alt="" title="Suchen"  />
				<input type="text" name="for" value="<?php
				//Falls bereits eine Suche gestartet wurde, übergebenen Wert ins Suchfeld eintragen
				if (isset($_GET['for'])) {
					$search4url = "&amp;for=".urlencode($_GET['for']);
					echo htmlspecialchars($_GET['for']);
				 }else{
	               // Haben wir auf keiner bestimmten Seite, ist der String leer und hat somit keine Auswirkungen auf die URL
	               $search4url = "";
	             }
				?>" class="actionlinesearch medium" title="Nach Veranstaltungsname suchen" />
				<input type="submit" value="Suchen" class="actionlinesearch" />
			</span>
			<?php
			 if (isset($_GET['page']) && intval($_GET['page']) >= 1){
               // Sind wir auf einer bestimmten Seite Suche? Dann muss die Seitenzahl auch bei URLs übergeben werden, daher bauen wir einen entsprechenden String, der diese enthält
               $page4url = "&amp;page=".intval($_GET['page']);
             }else{
               // Haben wir auf keiner bestimmten Seite, ist der String leer und hat somit keine Auswirkungen auf die URL
               $page4url = "";
             }
			if ($user["rights"] == 5) {
				//Hinzufügen Button nur anzeigen wenn der User Adminrechte hat
				echo '<span><img src="images/add.png" alt="" title="Hinzufügen/Erstellen"  /> <a href="veranstaltung_add.php" title="Neue Veranstaltung erstellen" >Neue Veranstaltung</a></span>';
			}
			?>
		</p>
	</form>
	<div class="clearit">
		&nbsp;
	</div>
	<?php
	// Meldungen, die per GET übergeben wurden anzeigen
	if (isset($_GET['removal'])) {
		if ($_GET['removal'] == "success") {
			echo "<p class=\"message success\">Die Veranstaltung wurde erfolgreich aus dem System entfernt.</p>";
		} else {
			echo "<p class=\"message error\">Beim Entfernen der Veranstaltung ist ein Fehler aufgetreten.</p>";
		}
	} elseif (isset($_GET['deletion'])) {
		if ($_GET['deletion'] == "success") {
			echo "<p class=\"message success\">Die Veranstaltung wurde erfolgreich aus der Datenbank gelöscht.</p>";
		} else {
			echo "<p class=\"message error\">Beim Löschen der Veranstaltung aus der Datenbank ist ein Fehler aufgetreten.</p>";
		}
	}

	if (!isset($page)) {
		$page = 1;
	}
	//Aktuellen Timestamp in Variable speichern
	$now = time();

	//SQL Abfrage für alle Veranstaltungen und zugehörige orts und Mitgliederdaten
	$sql = "SELECT * FROM `veranstaltungen` LEFT JOIN `orte` ON `veranstaltungen`.`ort` = `orte`.`orts_id` LEFT JOIN `mitglieder` ON `veranstaltungen`.`ansprechpartner` = `mitglieder`.`mitglieder_id`";

	if($user['rights'] == 5){
		$sql .= " WHERE (`veranstaltungen`.`status` = 1 OR `veranstaltungen`.`status` = 2)";
	}else{
		$sql .= " WHERE `veranstaltungen`.`status` = 1";
	}
	//Suchoptionen
	//Suche nach Veranstaltungsnamen
	if (!empty($_GET['for'])) {
		$sql .= " WHERE `veranstaltungsname` LIKE '%" . mysql_real_escape_string(str_replace(" ", "%", $_GET['for'])) . "%'";
	}
	//Filter für Veranstaltungszeit
	if (isset($_GET['time']) and $_GET['time'] != "all") {

		$sql .= " AND ";

		//Nur vergangene oder nur bevorstehende Veranstaltungen anzeigen
		if ($_GET['time'] == "neu") {
			$sql .= "`startzeit` > " . $now;
			$time4url = "&amp;time="."neu";
		}else{
			$sql .= "`startzeit` < " . $now;
			$time4url = "&amp;time="."alt";
		}
	}else{
		$time4url = "";
	}
	//Filter für eigene Veranstaltungen und eingetragene Dienste
	if (isset($_GET['detail']) and $_GET['detail'] != "all") {
		$detail4url = "&amp;detail=".$_GET['detail'];	
		
		//Anfragesyntax muss unterscheiden ob es schon eine Bedingung gibt
		$sql .= " AND ";
		//Suchender User ist Veranstalter
		if ($_GET['detail'] == "eigene") {
			$sql .= " `ansprechpartner` = '" . $user['mitglieder_id'] . "'";
		}
		if ($_GET['detail'] == "dienste") {
			//Suchender User hat Dienste
			//Abfrage aller Events wo der aktuelle User Dienste hat
			$sql_dienste = "SELECT DISTINCT `event` FROM `dienste` WHERE `person` = '" . $user['mitglieder_id'] . "'";
			$veranstaltungen_dienste = mysql_query($sql_dienste);

			$i = 0;
			// SQL Array mit diesen Diensten füllen
			while ($veranstaltung_dienst = mysql_fetch_assoc($veranstaltungen_dienste)) {
				if ($i == 0) {
					$veranstaltungen_dienste_liste = "('" . $veranstaltung_dienst['event'] . "'";
				} else {
					$veranstaltungen_dienste_liste .= ", '" . $veranstaltung_dienst['event'] . "'";
				}
				$i++;
			}
			//Wenn es mindestens eine Veranstaltung mit eingetragenem Dienst gibt
			//Nur diese Veranstaltungen finden
			//Anfrage vervollständigen
			if ($i != 0) {
				$veranstaltungen_dienste_liste .= ")";
			}
			if(!empty($veranstaltungen_dienste_liste)){
				$sql .= " `veranstaltungs_id` IN " . $veranstaltungen_dienste_liste;
			}
		}
	}else{
		$detail4url = "";
	}

	//Datenbankergebniss nach Veranstaltungsstartzeit ändern um später sortieren zu können
	$sql .= " ORDER BY `startzeit` DESC";
	//Veranstaltungen abfragen
	$veranstaltungen = mysql_query($sql);

	// Gesamtanzahl aller Orte ermitteln
	if($veranstaltungen != FALSE){
		$anzahl = mysql_num_rows($veranstaltungen);
	}else{
		$anzahl = 0;
	}
	// Festlegen, wie viele Einträge pro Seite angezeigt werden sollen
	$proseite = 10;
	
	if (!isset($_GET['page'])) {
		// Keine Seite übermittelt, dann erste Seite anzeigen
		$page = 1;
	} elseif ($anzahl > $proseite) {
		// Seite übermittelt und mehrere Seiten werden auch benötigt
		$page = intval($_GET['page']);
	}
	// Ergebnismenge limitieren, evtl. entsprechend der Seitenzahl bereits nur hintere Ergebnisse ausgeben
	$sql .= " LIMIT " . (abs($page - 1) * $proseite) . ",$proseite";
	$veranstaltungen = mysql_query($sql);

	//Wenn es einen SQL Fehler gibt oder keine Veranstaltung gefunden wurde das ausgeben
	if ($veranstaltungen == FALSE or mysql_num_rows($veranstaltungen) == 0) {
		echo "<table><tr><td colspan=\"5\" class=\"tcenter\">Es wurden keine Veranstaltungen gefunden!</td></tr></table>";
	} else {
		//Ansonsten Liste mit Veranstaltungen generieren
		//Zähler für ausgegebene Veranstaltungen auf 0 setzen
		$i = 0;
		//Variable die angibt ob die aktuelle Veranstaltung bevorstehend oder bereits vergangen ist
		$new = TRUE;
		//Tabelle generieren
		while ($veranstaltung = mysql_fetch_assoc($veranstaltungen)) {

			//Datumsanzeige unterscheidet zwischen einzel- und mehrtägigen Veranstaltungen
			if (date("d.n.Y", $veranstaltung["startzeit"]) == date("d.n.Y", $veranstaltung["endzeit"])) {
				$termin = "Am " . date("d.n.Y", $veranstaltung["startzeit"]) . "<br />Von " . date("H:i", $veranstaltung["startzeit"]) . " bis " . date("H:i", $veranstaltung["endzeit"]);
			} else {
				$termin = date("d.n.Y", $veranstaltung["startzeit"]) . ", " . date("H:i", $veranstaltung["startzeit"]) . " bis <br />" . date("d.n.Y", $veranstaltung["endzeit"]) . ", " . date("H:i", $veranstaltung["endzeit"]);
			}

			//Fallunterscheidung für Tabellenüberschriften
			//Erste Veranstaltung ist vergangen
			if ($i == 0 and $now > $veranstaltung['startzeit']) {
				//Variable anpassen
				$new = FALSE;
				//Tabellenkopf ausgeben
				echo '<h3>Vergangene Veranstaltungen</h3>              
                                     <table><colgroup>
                    <col width="22%" />
                    <col width="22%" />
                    <col width="22%" />
                    <col width="22%" />
                  </colgroup> <thead>
                   <tr>
                        <th>Veranstaltungsname</th>
                        <th>Veranstaltungsort</th>
                        <th>Ansprechpartner</th>
                        <th>Termin</th>
                        <th>Aktionen</th>
                    </tr>
                  </thead>
                  <tbody>';
			}
			//Erste Veranstaltung ist bevorstehend
			if ($i == 0 and $now < $veranstaltung['startzeit']) {
				//Tabellenkopf ausgeben
				echo '<h3>Bevorstehende Veranstaltungen</h3>
							                  <table><colgroup>
                    <col width="22%" />
                    <col width="22%" />
                    <col width="22%" />
                    <col width="22%" />
                  </colgroup> <thead>                  
                    <tr>
                        <th>Veranstaltungsname</th>
                        <th>Veranstaltungsort</th>
                        <th>Ansprechpartner</th>
                        <th>Termin</th>
                        <th>Aktionen</th>
                    </tr>
                  </thead>
                  <tbody>';
			}
			//Letzte Veranstaltung war bevorstehend, jetzt vergangen
			if ($i != 0 and $new == TRUE and $now > $veranstaltung['startzeit']) {
				//Variable anpassen
				$new = FALSE;
				//Tabellenkopf ausgeben
				echo '</tbody></table><br /><h3>Vergangene Veranstaltungen</h3>
                  <table><colgroup>
                    <col width="22%" />
                    <col width="22%" />
                    <col width="22%" />
                    <col width="22%" />
                  </colgroup> <thead>               
                    <tr>
                        <th>Veranstaltungsname</th>
                        <th>Veranstaltungsort</th>
                        <th>Ansprechpartner</th>
                        <th>Termin</th>
                        <th>Aktionen</th>
                    </tr>
                  </thead>
                  <tbody>';
			}
			//Letzte Veranstaltung war vergangen, jetzt bevorstehend
			if ($i != 0 and $new == FALSE and $now < $veranstaltung['startzeit']) {
				//Variable anpassen
				$new = TRUE;
				//Tabellenkopf ausgeben
				echo '</tbody></table><br /><h3>Bevorstehende Veranstaltungen</h3>   
              <colgroup>
                    <col width="22%" />
                    <col width="22%" />
                    <col width="22%" />
                    <col width="22%" />
                  </colgroup><table><thead>               
                    <tr>
                        <th>Veranstaltungsname</th>
                        <th>Veranstaltungsort</th>
                        <th>Ansprechpartner</th>
                        <th>Termin</th>
                        <th>Aktionen</th>
                    </tr>
                  </thead>
                  <tbody>';
			}

			//Tabellenzeile für Veranstaltung ausgeben
			echo "<tr>
									<td><b>" . $veranstaltung["veranstaltungsname"] . "</b></td>
									<td>" . $veranstaltung["name"] . "</td>
									<td>" . $veranstaltung["vorname"] . " " . $veranstaltung["nachname"] . "</td>
									<td>" . $termin . "</td>";

			//Ausgabe des Detail und Bearbeiten icons
			echo "<td class=\"actionrow\"><a href=\"veranstaltung_show.php?id=" . $veranstaltung['veranstaltungs_id'] . "\"><img src=\"images/show.png\" alt=\"\" title=\"Anzeigen\" /></a>
			<a href=\"dienste_show.php?id=" . $veranstaltung['veranstaltungs_id'] . "\"><img src=\"images/history.png\" alt=\"\" title=\"Dienstdetails ansehen\" /></a>";

			//Bearbeiten Icon nur anzeigen wenn User Admin oder Veranstalter
			if ($user["rights"] == 5 or $user["mitglieder_id"] == $veranstaltung["ansprechpartner"]) {
				echo "<a href=\"veranstaltung_edit.php?id=" . $veranstaltung['veranstaltungs_id'] . "\"><img src=\"images/edit.png\" alt=\"\" title=\"Bearbeiten\" /></a>";
			}
			//Zeile schließen
			echo "</td></tr>";
			//Zähler inkrementieren
			$i++;
		}

	}
	//Tabelle schließen
	?>
	</tbody>
	</table>
	<?php
	if ($anzahl > $proseite) {
      // Gibt es mehr Einträge, als auf einer Seite anzeigt werden können, also muss eine Seitennavigation angezeigt werden
      echo "<div class=\"pagenavi\">
      <p class=\"tcenter\">
      <a href=\"veranstaltungen.php?page=".($page-1)."{$detail4url}{$search4url}{$time4url}\" title=\"Seite zurück\" class=\"naviicon";
      if ($page == 1){
        // Auf der ersten Seite "zurück" Pfeil verstecken
        echo " hidden";
      }
      echo "\" ><img src=\"images/arrow_left.png\" alt=\"&laquo;\" /></a> \n";
      for ($i = 1; ($i-1)*$proseite <= $anzahl; $i++)  {
        echo "<a href=\"veranstaltungen.php?page={$i}{$detail4url}{$search4url}{$time4url}\"";
        if ($i == $page){
          echo " class=\"currentpage\" title=\"Aktuelle Seite $i\" ";
        }else{
          echo " title=\"Seite $i anzeigen\" ";
        }
        echo ">$i</a> \n";
      }
      echo "
      <a href=\"veranstaltungen.php?page=".($page+1)."{$detail4url}{$search4url}{$time4url}\" title=\"Seite nach vorne\" class=\"naviicon";
      if ($page*$proseite >= $anzahl){
        // Auf der letzten Seite "vorwärts" Pfeil verstecken
        echo " hidden";
      }
      echo "\"><img src=\"images/arrow_right.png\" alt=\"&raquo;\" /></a> \n
      </ul>
      </div>";
	}
	?>
	<div class="clearit">&nbsp;</div>
	</div>

	<?php
	// HTML-Fußbereich einbinden
	require_once ("inc/footer.inc.php");
	?>
