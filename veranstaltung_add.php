<?php
// Datenbankverbindung & Kodierung einbinden
require_once ("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once ("inc/checkuser.inc.php");

// Titel festlegen
$title = "Neue Veranstaltung";

// JavaScript einfügen
$appendjs = "$(\"#ort\").change(function(){
if ($(this).val() == \"new\"){
    $(\"#neuerort\").slideDown();
}else{
    $(\"#neuerort\").slideUp();
}
});
$(\"#mindienste\").change(function(){
if ($(this).val() > 0){
    $(\"#dienstbeschreibung\").slideDown();
}else{
    $(\"#dienstbeschreibung\").slideUp();
}
});";
// HTML-Kopf einbinden
require_once ("inc/head.inc.php");
?>

<div id="content">
	<div class="boxsystem33">
		<div class="leftbox">

			<h2>Neue Veranstaltung</h2>
			<?php
			//Wenn der save parameter übergeben wurde, wird das Ergebniss der Speicherung angezeigt
			if (isset($_GET['save'])) {
				if ($_GET['save'] == "fail") {
					//Wenn die Speicherung nicht erfolgreich war wird die Fehlervariable decodiert und angezeigt
					if (isset($_GET['errors'])){
						$errors = json_decode(base64_decode($_GET['errors']));
					}else{
						$errors = "";
					}
					echo "<p class=\"message error\">Beim Versuch, die Veranstaltung zu speichern, sind leider Fehler aufgetreten.";
					if (is_array($errors) && count($errors) > 0) {
						//Fehlerarray anzeigen
						echo "<br />Folgende inhaltliche Fehler sind festgestellt worden:<br /><b>" . implode("<br />", $errors) . "</b>.";
						//Falls bereits eingegebene Daten vorhanden sind, werden diese decodiert
						if (isset($_GET['data'])) {
							$data = json_decode(base64_decode($_GET['data']), TRUE);
						}
					} elseif ($_GET['why'] == 1062) {
						// MySQL Error für doppelten Tabelleneintrag
						echo "<p class=\"message error\">Es ist bereits ein Ort mit diesen Daten vorhanden. Bitte verwenden Sie diesen und löschen gegebenenfalls den überflüssigen Eintrag.</p>";
					} else {
						// Vermutlich ein Fehler bei der Kommunikation mit der MySQL Datenbank. $_GET['why'] enthält die MySQL-Fehlernummer
						echo "<p class=\"message error\">Beim Versuch, die Änderungen zu speichern, ist leider ein Fehler aufgetreten. Bitte versuchen Sie es erneut oder wenden sich an den Systemadministrator.</p>";
					}
					echo "</p>";
				}
			}
			if($user['rights'] >= 3){
			?>
			<form id="saveform" class="formular" method="post" action="veranstaltungen_process.php" accept-charset="utf-8">
				<p>
					<label for="veranstaltungsname">Bezeichnung / Name</label>
					<input type="text" value="<?php //Bereits eingegebene übergebene Daten werden in die Felder geschrieben
						if (isset($data['veranstaltungsname'])) {echo htmlspecialchars($data['veranstaltungsname']);
						}
					?>" name="veranstaltungsname" id="veranstaltungsname" class="large" />
				</p>
				<p>
					<label for="beschreibung">Beschreibung</label>
					<textarea name="beschreibung" value="<?php
					if (isset($data['beschreibung'])) {echo htmlspecialchars($data['beschreibung']);
					}
					?>" id="beschreibung" class="large" rows="4" ></textarea>
				</p>
				<p>
					<label for="ort">Veranstaltungsort</label>
					<select size="1" name="ort" id="ort" lass="large">
						<?php
						//Veranstaltungsort als selectfeld
						//Erste Option new klappt ein Formular für die Erstellung eines neuen Ortes auf
						echo "<option value=\"new\"";
						//Wenn bei dem vorigen Erstellversuch dies angewählt war, wieder anwählen
						if (isset($data['ort']) and $data['ort'] == "new") {
							echo " selected=\"selected\"";
						}
						echo ">Neuer Ort</option>";
						//Per Sql alle vorhandenen Veranstaltungsorte abfragen
						$sql = "SELECT * FROM `orte` WHERE `typ` = 2 AND `status` = 1";
						$veranstaltungsorte = mysql_query($sql);
						//Zählervariable
						$first = TRUE;
						while ($veranstaltungsort = mysql_fetch_assoc($veranstaltungsorte)) {
							//Veranstaltungsorte als option generieren
							echo "<option value=\"" . $veranstaltungsort['orts_id'] . "\"";
							//Erste Veranstaltung selektieren damit nicht new vorselektiert ist
							if ($first == TRUE and (!isset($data['ort']) or $data['ort'] != "new")) {
								echo " selected=\"selected\"";
								$first = FALSE;
							}
							//Veranstaltung ausgeben
							echo ">" . $veranstaltungsort['name'] . " (" . $veranstaltungsort['plz'] . " " . $veranstaltungsort['ort'] . ")</option>";
						}
						?>
					</select>

				</p>
				<div id="neuerort" class="<?php
					if (!isset($data['ort']) or $data['ort'] != "new") {echo "nodisplay";
					}
				?>">
					<br />
					<p>
						<b>Neuer Ort</b>
					</p>
					<p>
						<label for="ort_name">Bezeichnung / Name</label>
						<input type="text" value="<?php
						if (isset($data['ort_name'])) {echo htmlspecialchars($data['ort_name']);
						}
						?>" name="ort_name" title="Name/Bezeichnung" id="ort_name" class="medium" />
					</p>
					<p>
						<label for="ort_strasse">Straße</label>
						<input type="text" value="<?php
						if (isset($data['ort_strasse'])) {echo htmlspecialchars($data['ort_strasse']);
						}
						?>" name="ort_strasse" id="ort_strasse" title="Straße" class="medium" />
						<input type="text" value="<?php
						if (isset($data['ort_hausnummer'])) {echo htmlspecialchars($data['ort_hausnummer']);
						}
						?>" name="ort_hausnummer" title="Hausnummer" id="ort_hausnummer" class="xsmall" />
					</p>
					<p>
						<label for="ort_ort">Ort</label>
						<input type="text" value="<?php
						if (isset($data['ort_plz'])) {echo htmlspecialchars($data['ort_plz']);
						}
						?>" name="ort_plz" id="ort_plz" title="PLZ" class="xsmall" maxlength="5" />
						<input type="text" value="<?php
						if (isset($data['ort_ort'])) {echo htmlspecialchars($data['ort_ort']);
						}
						?>" name="ort_ort" id="ort_ort" title="Ortsname" class="medium" />
					</p>

					<p>
						<label for="telnr">Telefonnummer</label>
						<input type="text" value="<?php
						if (isset($data['ort_telvorwahl'])) {echo htmlspecialchars($data['ort_telvorwahl']);
						}
						?>" name="ort_telvorwahl" id="ort_telvorwahl" title="Vorwahl" class="xsmall" maxlength="5" />
						<input type="text" title="Telefonnummer" value="<?php
						if (isset($data['ort_telvorwahl'])) {echo htmlspecialchars($data['ort_telvorwahl']);
						}
						?>" name="ort_telnr" id="ort_telnr" class="medium" />
					</p>
					<p class="nodisplay">
						<label for="ort_typ">Art des Ortes</label>
						<select name="ort_typ" size="1" class="medium" id="typ">
							<option value="2" selected="selected">Veranstaltungsort</option>
						</select>
					</p>
					<br />
				</div>
				<p>
					<?php
					//Falls kein Vor und Nachname bei doppelten Datenbankeinträgen übergeben wurde, einfachen Namen in Textbox schreiben
					if (empty($data['more_persons'])) {
					?>
<label for="ansprechpartner">Ansprechpartner</label>
Vorname
<input type="text" name="ansprechpartner_vorname" id="ansprechpartner_vorname" class="xsmall" value="<?php if (isset($data['ansprechpartner_vorname'])) {echo htmlspecialchars($data['ansprechpartner_vorname']);}?>">
- Nachname
<input type="text" name="ansprechpartner_nachname" id="ansprechpartner_nachname" class="xsmall" value="<?php if (isset($data['ansprechpartner_nachname'])) {echo htmlspecialchars($data['ansprechpartner_nachname']);}?>">
<?php
					} else {
						//Ansonsten alle Personen mit dem gleichen Namen suchen und mit deren Daten verknüpfen
						$sql_personen = "SELECT * FROM `mitglieder` LEFT JOIN `orte` ON `mitglieder`.`anschrift` = `orte`.`orts_id` WHERE `vorname` = '" . mysql_real_escape_string(filter_var($data['ansprechpartner_vorname'], FILTER_SANITIZE_STRING)) . "' AND `nachname` = '" . mysql_real_escape_string(filter_var($data['ansprechpartner_nachname'], FILTER_SANITIZE_STRING)) . "'";
						$personen_result = mysql_query($sql_personen);
						//Verfügbare Personen mit Wohnort in Selectbox schreiben
						echo '<label for=\"ansprechpartner\">Ansprechpartner</label><select size="1" name="ansprechpartner_id" id="ansprechpartner_id" class="xlarge">';
						while ($person = mysql_fetch_assoc($personen_result)) {
							echo "<option value=\"" . $person['mitglieder_id'] . "\">" . $person['vorname'] . " " . $person['nachname'] . "  (" . $person['plz'] . " " . $person['ort'] . ")" . "</option>";
						}
						echo "</select>";
					}
					?>
				</p>
				<p>
					<label for="Standdienst">Personen am Stand</label>
					<select size="1" name="mindienste" id="mindienste" class="small">
						<?php
						for ($i = 0; $i <= 10; $i++) {
							echo "<option value=\"$i\"";
							if (isset($data['mindienste']) and $i == $data['mindienste']) {
								// Ist der aktuelle Schleifendurchlauf der bisherige Tag des Geburtstags, diesen Eintrag vorselektieren
								echo " selected=\"selected\" ";
							}
							echo ">$i</option>\n";
						}
						?>
					</select>
				</p>
				<div id="dienstbeschreibung" <?php if(!isset($data['mindienste']) or (isset($data['mindienste']) and $data['mindienste'] == 0)){ ?>class="nodisplay"<?php } ?>>
				<p>
					<label for="dienstbeschreibung">Beschreibung der Dienste</label>
					<textarea name="beschreibung" id="beschreibung" class="large" rows="4" ><?php
					 if(isset($data['mindienste']) and $data['mindienste'] == 0 and isset($data['dienstbeschreibung'])){ echo htmlspecialchars($data['dienstbeschreibung']); }else{
					?>
1:
2:
3:
4:
5:
6:
7:
8:
9:
10:<?php
					 }
					 ?></textarea>
				</p>
				</div
				<p>
					<label for="minkuchen">Benötigte Kuchen</label>
					<input type="text" value="<?php //Bereits eingegebene übergebene Daten werden in die Felder geschrieben
						if (isset($data['minkuchen']) and is_numeric($data['minkuchen'])) {echo htmlspecialchars($data['minkuchen']);
						}else{
							echo "0";
						}
					?>" name="minkuchen" id="minkuchen" class="small" />
				</p>
				<h2>Termin</h2>
				<p>
					<label for="start (Datum)">Start (Datum)</label>
					<select size="1" name="startzeit_tag" id="startzeit_tag" class="small">
						<?php
						// Falls Startzeit übergeben wurde, diese als Referenzvariable verwenden
						if (isset($data['startzeit'])) {
							$jetzt = $data['startzeit'];
							echo $jetzt;
						} else {
							// Sonst aktuelle Zeit verwenden
							$jetzt = time();
						}
						$aktuellertag = intval(strftime("%d", $jetzt));
						// Mittels einer For-Schleife von 1 bis 31 ein Auswahlfeld für den Tag erstellen
						for ($i = 1; $i <= 31; $i++) {
							echo "<option value=\"$i\"";
							if ($i == $aktuellertag) {
								// Ist der aktuelle Schleifendurchlauf der bisherige Tag des Geburtstags, diesen Eintrag vorselektieren
								echo " selected=\"selected\" ";
							}
							echo ">$i</option>\n";
						}
						?>
					</select>
					.
					<select size="1" name="startzeit_monat" id="startzeit_monat" class="small">
						<?php
						$aktuellermonat = intval(strftime("%m", $jetzt));
						// Mittels einer For-Schleife von 1 bis 12 ein Auswahlfeld für den Monat erstellen
						for ($i = 1; $i <= 12; $i++) {
							echo "<option value=\"$i\"";
							if ($i == $aktuellermonat) {
								// Ist der aktuelle Schleifendurchlauf der bisherige Monat des Geburtstags, diesen Eintrag vorselektieren
								echo " selected=\"selected\" ";
							}
							echo ">" . strftime("%B", mktime(0, 0, 0, $i)) . "</option>\n";
						}
						?>
					</select>
					.
					<input type="text" value="<?php echo strftime("%Y", $jetzt); ?>" name="startzeit_jahr" id="startzeit_jahr" maxlength="4" class="xsmall" />
				</p>
				<p>
					<label for="start (Uhrzeit)">Start (Uhrzeit)</label>
					<select size="1" name="startzeit_stunde" id="startzeit_stunde" class="small">
						<?php
						$aktuellestunde = intval(strftime("%H", $jetzt));
						// Mittels einer For-Schleife von 1 bis 12 ein Auswahlfeld für den Monat erstellen
						for ($i = 0; $i <= 23; $i++) {
							echo "<option value=\"$i\"";
							if ($i == $aktuellestunde) {
								// Ist der aktuelle Schleifendurchlauf der bisherige Monat des Geburtstags, diesen Eintrag vorselektieren
								echo " selected=\"selected\" ";
							}
							if ($i < 10) {
								$string_i = "0" . $i;
							} else {
								$string_i = $i;
							}
							echo ">$string_i</option>\n";
						}
						?>
						</select> :
						<select size="1" name="startzeit_minute" id="startzeit_minute" class="small">
						<?php
						$aktuelleminute = intval(strftime("%M", $jetzt));
						// Mittels einer For-Schleife von 1 bis 12 ein Auswahlfeld für den Monat erstellen
						for ($i = 0; $i <= 60; $i++) {
							echo "<option value=\"$i\"";
							if ($i == $aktuelleminute) {
								// Ist der aktuelle Schleifendurchlauf der bisherige Monat des Geburtstags, diesen Eintrag vorselektieren
								echo " selected=\"selected\" ";
							}
							if ($i < 10) {
								$string_i = "0" . $i;
							} else {
								$string_i = $i;
							}
							echo ">$string_i</option>\n";
						}
						?>
						</select>
						</p>
						<p>
						<label for="ende (Datum)">Ende (Datum)</label>
						<select size="1" name="endzeit_tag" id="endzeit_tag" class="small">
						<?php
						//Analog zu Startzeit verfahren
						if (isset($data['endzeit'])) {
							$jetzt = $data['endzeit'];
						} else {
							$jetzt = time();
						}
						$aktuellertag = intval(strftime("%d", $jetzt));
						// Mittels einer For-Schleife von 1 bis 31 ein Auswahlfeld für den Tag erstellen
						for ($i = 1; $i <= 31; $i++) {
							echo "<option value=\"$i\"";
							if ($i == $aktuellertag) {
								// Ist der aktuelle Schleifendurchlauf der bisherige Tag des Geburtstags, diesen Eintrag vorselektieren
								echo " selected=\"selected\" ";
							}
							echo ">$i</option>\n";
						}
						?>
						</select> .
						<select size="1" name="endzeit_monat" id="startzeit_monat" class="small">
						<?php
						$aktuellermonat = intval(strftime("%m", $jetzt));
						// Mittels einer For-Schleife von 1 bis 12 ein Auswahlfeld für den Monat erstellen
						for ($i = 1; $i <= 12; $i++) {
							echo "<option value=\"$i\"";
							if ($i == $aktuellermonat) {
								// Ist der aktuelle Schleifendurchlauf der bisherige Monat des Geburtstags, diesen Eintrag vorselektieren
								echo " selected=\"selected\" ";
							}
							echo ">" . strftime("%B", mktime(0, 0, 0, $i)) . "</option>\n";
						}
						?>
						</select> .
						<input type="text" value="<?php echo strftime("%Y", $jetzt); ?>" name="endzeit_jahr" id="endzeit_jahr" maxlength="4" class="xsmall" />
						</p>
						<p>
						<label for="ende (Uhrzeit)">Ende (Uhrzeit)</label>
						<select size="1" name="endzeit_stunde" id="endzeit_stunde" class="small">
						<?php
						$aktuellestunde = intval(strftime("%H", $jetzt));
						// Mittels einer For-Schleife von 1 bis 12 ein Auswahlfeld für den Monat erstellen
						for ($i = 0; $i <= 23; $i++) {
							echo "<option value=\"$i\"";
							if ($i == $aktuellestunde + 1 and !isset($data['endzeit'])) {
								// Ist der aktuelle Schleifendurchlauf der bisherige Monat des Geburtstags, diesen Eintrag vorselektieren
								echo " selected=\"selected\" ";
							} elseif ($i == $aktuellestunde and isset($data['endzeit'])) {
								// Ist der aktuelle Schleifendurchlauf der bisherige Monat des Geburtstags, diesen Eintrag vorselektieren
								echo " selected=\"selected\" ";
							}
							if ($i < 10) {
								$string_i = "0" . $i;
							} else {
								$string_i = $i;
							}
							echo ">$string_i</option>\n";
						}
						?>
						</select> :
						<select size="1" name="endzeit_minute" id="endzeit_minute" class="small">
						<?php
						$aktuelleminute = intval(strftime("%M", $jetzt));
						// Mittels einer For-Schleife von 1 bis 12 ein Auswahlfeld für den Monat erstellen
						for ($i = 0; $i <= 60; $i++) {
							echo "<option value=\"$i\"";
							if ($i == $aktuelleminute) {
								// Ist der aktuelle Schleifendurchlauf der bisherige Monat des Geburtstags, diesen Eintrag vorselektieren
								echo " selected=\"selected\" ";
							}
							if ($i < 10) {
								$string_i = "0" . $i;
							} else {
								$string_i = $i;
							}
							echo ">$string_i</option>\n";
						}
						?>
						</select>
						</p>
						<p>
						<input type="submit" name="addevent" value="Speichern" title="Speichern" class="button medium" />
						</p>
						</form>
						<?php }else{?>
							<div class="message error">Sie haben nicht die erforderlichen Berechtigungen um eine neue Veranstaltung zu erstellen</div>
						<?php } ?>
						</div>
						<div class="rightbox">
						<h2>Aktionen</h2>
						<ul class="nolistimg">
						<li><img src="images/list.png" alt="" title="Zur Übersicht" /> <a href="veranstaltungen.php" title="Zurück zur Übersicht aller Veranstaltungen">Veranstaltungsverwaltung</a></li>
						</ul>
						</div>
						</div>
						<div class="clearit">&nbsp;</div>
						</div>

						<?php
						require_once ("inc/footer.inc.php");
						?>
