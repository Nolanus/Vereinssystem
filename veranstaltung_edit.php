<?php
// Datenbankverbindung & Kodierung einbinden
require_once ("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once ("inc/checkuser.inc.php");

// Titel festlegen
$title = "Veranstaltung bearbeiten";

// JavaScript einfügen
$appendjs = "$(\"#ort\").change(function(){
if ($(this).val() == \"new\"){
    $(\"#neuerort\").slideDown();
}else{
    $(\"#neuerort\").slideUp();
}
});
$(\"#ansprechpartner_id\").change(function(){
if ($(this).val() == \"other\"){
    $(\"#andereruser\").slideDown();
}else{
    $(\"#andereruser\").slideUp();
}
});
$(\"#mindienste\").change(function(){
if ($(this).val() > 0){
    $(\"#dienstbeschreibungsdiv\").slideDown();
}else{
    $(\"#dienstbeschreibungsdiv\").slideUp();
}
});";

//$appendjs = '$(\"form select\").change(function(){if (!$("#ort option[value=\'new\']").length)
//$(\"#neuerort\").slideDown()});';

// HTML-Kopf einbinden
require_once ("inc/head.inc.php");
?>

<div id="content">
	<?php
if (isset($_GET['id'])){
// Falls ID übergeben wurde, Daten dieser Veranstaltung abrufen
$sql = "SELECT * FROM `veranstaltungen` LEFT JOIN `mitglieder` ON `veranstaltungen`.`ansprechpartner` = `mitglieder`.`mitglieder_id` WHERE `veranstaltungs_id` = ".intval($_GET['id']);

$veranstaltung_result = mysql_query($sql);

//echo mysql_error();
//Nur Fortfahren wenn die Abfrage genau ein Resultat hatte
if (mysql_num_rows($veranstaltung_result) == 1){
$veranstaltung = mysql_fetch_assoc($veranstaltung_result);
	?>
		<div class="boxsystem33">
		<div class="leftbox">
			<h2>Veranstaltung bearbeiten</h2>
	<?php
	if($user['rights'] >= 3 or $user['mitglieder_id'] == $veranstaltung['ansprechpartner']){
	?>
			<?php
			//Wenn Fehlerspeicher vorhanden ist
			if (isset($_GET['save'])) {
				//Falls bereits versucht wurde eine Veranstaltung zu speichern, Fehlerspeicher decodieren
				$errors = json_decode(base64_decode($_GET['errors']));
				if ($_GET['save'] == "success") {
					//Wenn die Veranstaltung erfolgreich gespeichert werden konnte
					if (is_array($errors) && count($errors) > 0) {
						//Falls Fehler übergeben wurden, die nicht kritisch waren, anzeigen
						echo "<p class=\"message warning\">Die Änderungen wurden erfolgreich gespeichert, allerdings sind folgende inhaltliche Fehler festgestellt worden, die nicht übernommen wurden:<br />" . implode("<br />", $errors) . ".</p>";
					} else {
						echo "<p class=\"message success\">Die Änderungen wurden erfolgreich gespeichert.</p>";
					}
				} elseif ($_GET['save'] == "fail") {
					//Falls Veranstaltung nicht erfolgreich gespeichert werden konnte
					echo "<p class=\"message error\">Beim Versuch, die Änderungen zu speichern, ist leider ein Fehler aufgetreten. Bitte versuchen Sie es erneut oder wenden sich an den Systemadministrator.";
					if (is_array($errors) && count($errors) > 0) {
						echo "<br />Zusätzlich sind folgende inhaltliche Fehler festgestellt worden:<br /><b>" . implode("<br />", $errors) . "</b>.";
					}elseif ($_GET['why'] == 1062) {
						// MySQL Error für doppelten Tabelleneintrag
						echo "<p class=\"message error\">Es ist bereits ein Ort mit diesen Daten vorhanden. Bitte verwenden Sie diesen und löschen gegebenenfalls den überflüssigen Eintrag.</p>";
					} else {
						// Vermutlich ein Fehler bei der Kommunikation mit der MySQL Datenbank. $_GET['why'] enthält die MySQL-Fehlernummer
						echo "<p class=\"message error\">Beim Versuch, die Änderungen zu speichern, ist leider ein Fehler aufgetreten. Bitte versuchen Sie es erneut oder wenden sich an den Systemadministrator.</p>";
					}
					echo "</p>";
				}
			}
			?>
			<form id="editform" class="formular" method="post" action="veranstaltungen_process.php" accept-charset="utf-8">
				<p>
					<label for="veranstaltungsname">Bezeichnung / Name</label>
					<input type="text" value="<?php //Per SQL ermittelte Daten in die edit felder schreiben
						echo htmlspecialchars($veranstaltung['veranstaltungsname']);
 ?>" name="veranstaltungsname" id="veranstaltungsname" class="large" />
				</p>
				<p>
					<label for="beschreibung">Beschreibung</label>
					<textarea name="beschreibung" id="beschreibung" class="large" cols="20" rows="4" ><?php echo htmlspecialchars($veranstaltung['beschreibung']); ?></textarea>
				</p>
				<p>
					<label for="ort">Veranstaltungsort</label>
					<select size="1" name="ort" id="ort" class="large">
						<option value="new">Neuer Ort</option>
						<?php
						//Alle Veranstaltungsorte abrufen und in select feld als option einfügen
						$sql = "SELECT * FROM `orte` WHERE `typ` = 2 AND `status` = 1";
						$veranstaltungsorte = mysql_query($sql);
						while ($veranstaltungsort = mysql_fetch_assoc($veranstaltungsorte)) {
							echo "<option value=\"" . $veranstaltungsort['orts_id'] . "\"";
							if ($veranstaltungsort['orts_id'] == $veranstaltung["ort"]) {
								// Ist der aktuelle Ort des Schleifendurchlaufs der Veranstaltungsort der Veranstaltung, diesen vorselektieren
								echo " selected=\"selected\" ";
							}
							echo ">" . $veranstaltungsort['name'] . " (" . $veranstaltungsort['plz'] . " " . $veranstaltungsort['ort'] . ")</option>";
						}
						?>
					</select>

				</p>
				<div id="neuerort" class="nodisplay">
					<p>
						<b>Neuer Ort</b>
					</p>
					<p>
						<label for="ort_name">Bezeichnung / Name</label>
						<input type="text" value="" name="ort_name" title="Name/Bezeichnung" id="ort_name" class="medium" />
					</p>
					<p>
						<label for="ort_strasse">Straße</label>
						<input type="text" value="" name="ort_strasse" id="ort_strasse" title="Straße" class="medium" />
						<input type="text" value="" name="ort_hausnummer" title="Hausnummer" id="ort_hausnummer" class="xsmall" />
					</p>
					<p>
						<label for="ort_ort">Ort</label>
						<input type="text" value="" name="ort_plz" id="plz" title="PLZ" class="xsmall" maxlength="5" />
						<input type="text" value="" name="ort_ort" id="ort_ort" title="Ortsname" class="medium" />
					</p>

					<p>
						<label for="ort_telvorwahl">Telefonnummer</label>
						<input type="text" value="" name="ort_telvorwahl" id="ort_telvorwahl" title="Vorwahl" class="xsmall" maxlength="5" />
						/
						<input type="text" title="Telefonnummer" value="" name="ort_telnr" id="ort_telnr" class="medium" />
					</p>
					<p class="nodisplay">
						<label for="ort_typ">Art des Ortes</label>
						<select name="ort_typ" size="1" class="medium" id="ort_typ">
							<option value="2" selected="selected">Veranstaltungsort</option>
						</select>
					</p>
					<br />
				</div>
				<p>
					<?php
					//Alle Personen mit übergebenen Namen abfragen
					$sql_personen = "SELECT * FROM `mitglieder` LEFT JOIN `orte` ON `mitglieder`.`anschrift` = `orte`.`orts_id` WHERE `vorname` = '" . $veranstaltung['vorname'] . "' AND `nachname` = '" . $veranstaltung['nachname'] . "'";
					$personen_result = mysql_query($sql_personen);

					//Falls kein doppelter Name übergeben wurde und es auch nur einen SQL Treffer gibt diesen in Textbox eintragen
					if (empty($_GET['vorname']) and mysql_num_rows($personen_result) <= 1) {
						echo "
<label for=\"ansprechpartner_vorname\">Ansprechpartner</label>
Vorname
<input type=\"text\" name=\"ansprechpartner_vorname\" id=\"ansprechpartner_vorname\" class=\"xsmall\" value=\"" . $veranstaltung['vorname'] . "\" />
- Nachname
<input type=\"text\" name=\"ansprechpartner_nachname\" id=\"ansprechpartner_nachname\" class=\"xsmall\" value=\"" . $veranstaltung['nachname'] . "\" /></p>
";
					} elseif (!empty($_GET['vorname'])) {
						//Falls es einen übergebenen Doppelnamen gibt, diese Möglichkeiten in Selectbox schreiben
						$sql_personen = "SELECT * FROM `mitglieder` LEFT JOIN `orte` ON `mitglieder`.`anschrift` = `orte`.`orts_id` WHERE `vorname` = '" . mysql_real_escape_string(filter_var($_GET['vorname'], FILTER_SANITIZE_STRING)) . "' AND `nachname` = '" . mysql_real_escape_string(filter_var($_GET['nachname'], FILTER_SANITIZE_STRING)) . "'";
						$personen_result = mysql_query($sql_personen);
						echo '<label for="ansprechpartner"><b>Ansprechpartner</b></label><select size="1" name="ansprechpartner_id" id="ansprechpartner_id" class="xlarge">';
						while ($person = mysql_fetch_assoc($personen_result)) {
							echo "<option value=\"" . $person['mitglieder_id'] . "\">" . $person['vorname'] . " " . $person['nachname'] . "  (" . $person['plz'] . " " . $person['ort'] . ")" . "</option>";
						}
						echo "</select></p>";
					} else {
						//Falls es keinen übergebenen Doppelnamen aber mehrere SQL Treffer gibt, diese in Selectbox eintragen und aktuelle Person vorselektieren
						echo '<label for="ansprechpartner_id">Ansprechpartner</label><select size="1" name="ansprechpartner_id" id="ansprechpartner_id" class="xlarge">';
						echo '<option value="other">Anderer User</option>';
						while ($person = mysql_fetch_assoc($personen_result)) {
							echo "<option value=\"" . $person['mitglieder_id'] . "\" ";
							if ($person['mitglieder_id'] == $veranstaltung['mitglieder_id']) {echo "selected=\"selected\"";
							}
							echo ">" . $person['vorname'] . " " . $person['nachname'] . "  (" . $person['plz'] . " " . $person['ort'] . ")" . "</option>";
						}
						echo "</select>";
						echo "</p><div id=\"andereruser\" class=\"nodisplay\"><p><label for=\"ansprechpartner_vorname\">&nbsp;</label>
Vorname
<input type=\"text\" name=\"ansprechpartner_vorname\" id=\"ansprechpartner_vorname\" class=\"xsmall\" value=\"" . $veranstaltung['vorname'] . "\" />
- Nachname
<input type=\"text\" name=\"ansprechpartner_nachname\" id=\"ansprechpartner_nachname\" class=\"xsmall\" value=\"" . $veranstaltung['nachname'] . "\" />
</p></div>";

					}
					?>

				<p>
					<label for="mindienste">Personen am Stand</label>
					<select size="1" name="mindienste" id="mindienste" class="small">
					<?php
					for ($i = 0; $i <= 10; $i++) {
						echo "<option value=\"$i\"";
							if ($i == $veranstaltung['mindienste']) {
								// Ist der aktuelle Schleifendurchlauf der bisherige Tag des Geburtstags, diesen Eintrag vorselektieren
								echo " selected=\"selected\" ";
							}
						echo ">$i</option>\n";
					}
					?>
					</select>
				</p>
				<div id="dienstbeschreibungsdiv" <?php if($veranstaltung['mindienste'] == 0){ ?>class="nodisplay"<?php } ?>>
				<p>
					<label for="dienstbeschreibung">Beschreibung der Dienste</label>
					<textarea name="dienstbeschreibung" id="dienstbeschreibung" class="large" rows="4" cols="20"><?php
					 echo htmlspecialchars($veranstaltung['dienstbeschreibung']);
					 ?></textarea>
				</p>
				</div>
				<p>
					<label for="minkuchen">Benötigte Kuchen</label>
					<input type="text" value="<?php //Bereits eingegebene übergebene Daten werden in die Felder geschrieben
						if (isset($veranstaltung['minkuchen'])) {echo htmlspecialchars($veranstaltung['minkuchen']);
						}
					?>" name="minkuchen" id="minkuchen" class="small" />
				</p>
				<h2>Termin</h2>
				<p>
					Bei Terminänderungen werden alle eventuell vorhandenen Dienste dieser Veranstaltung gelöscht!
				</p>
				<p>
					<label for="startzeit_tag">Start (Datum)</label>
					<select size="1" name="startzeit_tag" id="startzeit_tag" class="small">
						<?php
						$aktuellertag = intval(strftime("%d", $veranstaltung['startzeit']));
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
						$aktuellermonat = intval(strftime("%m", $veranstaltung['startzeit']));
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
					<input type="text" value="<?php echo strftime("%Y", $veranstaltung['startzeit']); ?>" name="startzeit_jahr" id="startzeit_jahr" maxlength="4" class="xsmall" />
				</p>
				<p>
					<label for="startzeit_stunde">Start (Uhrzeit)</label>
					<select size="1" name="startzeit_stunde" id="startzeit_stunde" class="small">
						<?php
						$aktuellestunde = intval(strftime("%H", $veranstaltung['startzeit']));
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
						$aktuelleminute = intval(strftime("%M", $veranstaltung['startzeit']));
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
						<label for="endzeit_tag">Ende (Datum)</label>
						<select size="1" name="endzeit_tag" id="endzeit_tag" class="small">
						<?php
						$aktuellertag = intval(strftime("%d", $veranstaltung['endzeit']));
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
						<select size="1" name="endzeit_monat" id="endzeit_monat" class="small">
						<?php
						$aktuellermonat = intval(strftime("%m", $veranstaltung['endzeit']));
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
						<input type="text" value="<?php echo strftime("%Y", $veranstaltung['endzeit']); ?>" name="endzeit_jahr" id="endzeit_jahr" maxlength="4" class="xsmall" />
						</p>
						<p>
						<label for="endzeit_stunde">Ende (Uhrzeit)</label>
						<select size="1" name="endzeit_stunde" id="endzeit_stunde" class="small">
						<?php
						$aktuellestunde = intval(strftime("%H", $veranstaltung['endzeit']));
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
						<select size="1" name="endzeit_minute" id="endzeit_minute" class="small">
						<?php
						$aktuelleminute = intval(strftime("%M", $veranstaltung['endzeit']));
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
						<input type="hidden" name="veranstaltungs_id" value="<?php echo $veranstaltung['veranstaltungs_id']; ?>" />
						<input type="submit" name="saveevent" value="Speichern" title="Speichern" class="button medium" />
						</p>
						</form>
						<?php }else{ ?>
								<div class="message error">Sie haben nicht die erforderlichen Berechtigungen um diese Veranstaltung zu bearbeiten</div>
						<?php } ?>
						</div>
			    		  <div class="rightbox">
			              <?php
						include ("inc/action_leiste_events.inc.php");
			              ?>
			    		  </div>		
			 				</div>
						<?php }else{ ?>
						<h2>Veranstaltung bearbeiten</h2>
						<p class="error">Es wurde keine Veranstaltung mit dieser ID gefunden!<br /><a href="veranstaltungen.php" title="Übersicht aller Veranstaltungen anzeigen">Zur Veranstaltungsverwaltung</a></p>
						<?php }
							}else{
						?>
						<h2>Veranstaltung bearbeiten</h2>
						<p class="error">Es wurde keine Veranstaltungs-ID übergeben!<br /><a href="veranstaltungen.php" title="Übersicht aller Veranstaltungen anzeigen">Zur Veranstaltungsverwaltung</a></p>

						<?php } ?>
						<div class="clearit">&nbsp;</div>
						</div>
						<?php
						// HTML-Fußbereich einbinden
						require_once ("inc/footer.inc.php");
						?>
