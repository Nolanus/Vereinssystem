<?php
// Datenbankverbindung & Kodierung einbinden
require_once ("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once ("inc/checkuser.inc.php");

// Titel festlegen
$title = "Veranstaltung Detailansicht";

// HTML-Kopf einbinden
require_once ("inc/head.inc.php");
?>
<div id="content">
<?php
if (isset($_GET['id'])){
	//Wenn es die ID gibt, Veranstaltungen mit zugehörigen Daten aus der DB abrufen
$sql = "SELECT * FROM `veranstaltungen` LEFT JOIN `mitglieder` ON `veranstaltungen`.`ansprechpartner` = `mitglieder`.`mitglieder_id` LEFT JOIN `orte` ON `veranstaltungen`.`ort` = `orte`.`orts_id`  WHERE `veranstaltungs_id` = ".intval($_GET['id']);
	$veranstaltung_result = mysql_query($sql);
    echo mysql_error();
	//Nur fortfahren wenn genau eine Veranstaltung gefunden wurde
    if (mysql_num_rows($veranstaltung_result) == 1){
        $veranstaltung = mysql_fetch_assoc($veranstaltung_result);
		echo "<h2>".$veranstaltung['veranstaltungsname']."</h2>";
			//Falls es austehende Meldungen gibt, diese Anzeigen
			if (isset($_GET['created'])){
			echo "<p class=\"message success\">Die Veranstaltung wurde erfolgreich gespeichert.</p>";
			}
			?>
    		<div class="boxsystem33">
    		  <div class="leftbox">
                          <table>
                          <!-- Richtwerte für die Spaltenbreite -->
                          <colgroup>
                            <col width="40%" />
                            <col width="60%" />
                          </colgroup>
                          <tbody>
                          	 <tr>
                                <td class="firstcolumn" colspan="2">Allgemeines</td></tr>
                             <tr>
                                  <td class="firstcolumn">Veranstaltungsname</td>
                                  <td><?php echo htmlspecialchars($veranstaltung['veranstaltungsname']); ?></td>
                            </tr>
                            <tr>
                                  <td class="firstcolumn">Beschreibung</td>
                                  <td><?php echo htmlspecialchars($veranstaltung['beschreibung']); ?></td>
                            </tr>
                            <tr>
                                <td class="firstcolumn">Veranstaltungsort</td>
                                <td><?php echo htmlspecialchars($veranstaltung['name']). "<br />\n<br />" . htmlspecialchars($veranstaltung['strasse'] . " " . $veranstaltung['hausnummer']) . "<br />\n" . htmlspecialchars($veranstaltung['plz'] . " " . $veranstaltung['ort'])."<br />\n" . htmlspecialchars($veranstaltung['telefon']); ?></td>
                            </tr>
                            <tr>
                                <td class="firstcolumn">Ansprechpartner</td>
                                <td><?php echo htmlspecialchars($veranstaltung['vorname']. " " .$veranstaltung['nachname']) . "<br /><br />" .htmlspecialchars($veranstaltung['email']); ?></td>
                            </tr>
                            <tr>
                                <td class="firstcolumn">Termin</td>
                                <td>
                                    <?php
                                    //Terminsyntax richtet sich nach Länge der Veranstaltung
									if (date("d.m.Y", $veranstaltung["startzeit"]) == date("d.m.Y", $veranstaltung["endzeit"])) {
										$termin = "Am " . date("d.m.Y", $veranstaltung["startzeit"]) . "<br />Von " . date("H:i", $veranstaltung["startzeit"]) . " bis " . date("H:i", $veranstaltung["endzeit"]);
									} else {
										$termin = date("d.m.Y", $veranstaltung["startzeit"]) . ", " . date("H:i", $veranstaltung["startzeit"]) . " bis <br />" . date("d.m.Y", $veranstaltung["endzeit"]) . ", " . date("H:i", $veranstaltung["endzeit"]);
									}
									echo $termin;
								?></td>
                            </tr>
                            <tr><td class="firstcolumn" colspan="2">Vorbereitungen</td></tr>
                             <tr><td class="firstcolumn">Dienste</td>
                                <td>
                                <?php
                                //Wenn geforderte Zeitdienste in der DB eingetragen sind, diese auslesen und anzeigen
                                if($veranstaltung['mindienste'] > 0){
	                                $sql_dienste = "SELECT * FROM `dienste` WHERE `dienstart` = 0 AND `event` = ".intval($_GET['id']);
									$dienste_result = mysql_query($sql_dienste);
									$diensteprozent = (($veranstaltung['endzeit']-$veranstaltung['startzeit'])/60/30)*$veranstaltung['mindienste']/100;
									if($dienste_result != FALSE){
										//Prozentanzeige der erfüllten Zeitdienste einer Veranstlatung
	   									$eingetragen = round(mysql_num_rows($dienste_result)/$diensteprozent);
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
								$sql_kuchen = "SELECT * FROM `dienste` LEFT JOIN `mitglieder` ON `dienste`.`person` = `mitglieder`.`mitglieder_id` WHERE `dienstart` > 0 AND `event` = ".intval($_GET['id'])." ORDER BY `erstellt` DESC";
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
											echo "<br />\n";
										}
										echo $kuchendienst['vorname']." ".$kuchendienst['nachname'].": ".$kuchendienst['dienstart'];
										$i++;
									}
									echo "</p>";
								}
								?>
                                </td>
                            </tr>
                          </tbody>
                          </table>
                          <p><a href="http://maps.google.com/?q=<?php echo rawurlencode($veranstaltung['strasse'] . " " . $veranstaltung['hausnummer'] . " " . $veranstaltung['plz'] . " " . $veranstaltung['ort'] . ", Germany"); ?>" target="_blank" title="Adresse bei Google Maps anzeigen"><img src="http://maps.googleapis.com/maps/api/staticmap?markers=color:red%7C<?php echo rawurlencode($veranstaltung['strasse'] . " " . $veranstaltung['hausnummer'] . "," . $veranstaltung['plz'] . " " . $veranstaltung['ort'] . ",Germany"); ?>&amp;zoom=14&amp;size=550x400&amp;sensor=false" alt="" title="" /></a></p>
                      </div>

    		  <div class="rightbox">
              <?php
			include ("inc/action_leiste_events.inc.php");
              ?>
    		  </div>
    		</div>
    <?php }else{ ?>
            <h2>Veranstaltung anzeigen</h2>
            <p class="message error">Es wurde keine Veranstaltung mit dieser ID gefunden!<br /><a href="veranstaltungen.php" title="Übersicht aller Veranstaltungen anzeigen">Zur Veranstaltungsverwaltung</a></p>

<?php     }
	}else{
 ?>
<h2>Veranstaltung anzeigen</h2>
<p class="message error">Es wurde keine Veranstaltungs-ID übergeben!<br /><a href="veranstaltungen.php" title="Übersicht aller Veranstaltungen anzeigen">Zur Veranstaltungsverwaltung</a></p>

    <?php } ?>
	<div class="clearit">&nbsp;</div>
</div>



<?php
// HTML-Fußbereich einbinden
require_once ("inc/footer.inc.php");
?>