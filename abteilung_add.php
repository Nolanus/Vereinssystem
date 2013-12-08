<?php
// Datenbankverbindung & Kodierung einbinden
require_once("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once("inc/checkuser.inc.php");

// Rechte Check
if ($user['rights'] < 4){
  // Rechtelevel geringer als 4 = Kein Zugang
  if (isset($_SERVER["HTTP_REFERER"])){
    $referer = "?before=".base64_encode($_SERVER["HTTP_REFERER"]);
  }else{
    $referer = "";
  }
  header("Location: norights.php$referer");
  exit();
}

// Titel festlegen
$title = "Abteilung hinzufügen";

// JavaScript einfügen
$appendjs = "$(\"#abteilungsleiterdrop\").change(function(){
  if ($(this).val() == \"new\"){
      $(\"#abteilungsleiterdrop\").fadeOut(function(){
        $(\"#abteilungsleiter\").fadeIn().select();
      });
  }
});";

// HTML-Kopf einbinden
require_once("inc/head.inc.php");
?>
        <div id="content">
            		<div class="boxsystem33">
            		  <div class="leftbox">
                          <h2>Abteilung hinzufügen</h2>
                          <p>Über dieses Formular kann eine neue Abteilung in der Datenbank erstellt werden.</p>
                          <?php
                          if (isset($_GET['created']) && $_GET['created'] == "fail"){
                            if ($_GET['why'] == "data"){
                                echo "<div class=\"message error\"><p>Die Änderungen wurden aufgrund ungültiger Eingaben nicht übernommen.</p><ul>";
                                $fehlerarray = json_decode(base64_decode($_GET['errors']),true);
                                foreach ($fehlerarray as &$fehler) {
                                  echo "<li>$fehler</li>\n";
                                }
                                echo "</ul></div>";
                            }else{
                              echo "<div class=\"message error\"><p>Beim Versuch, die neuen Abteilung anzulegen, ist leider ein Fehler aufgetreten. Bitte versuchen Sie es erneut oder wenden sich an den Systemadministrator.</p></div>";
                            }
                            if (empty($_GET['data']) == false){
                              // Vorgaben für die Felder wurden übergeben; diese Werte in das Array $prefill schreiben
                              $prefill = json_decode(base64_decode($_GET['data']), true);
                              // Folgendes wendet stripslashes auf jedes Element des Arrays an
                              foreach ($prefill as $key=>$value) {
                                  $prefill[$key] = stripslashes($value);
                              }
                            }
                          }
                          ?>
                          <form id="addform" class="formular" method="post" action="abteilung_process.php" accept-charset="utf-8">
                              <p>
                                  <label for="name">Name</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['name'])){
                                    echo htmlspecialchars($prefill['name']);
                                  }?>" name="name" id="name" title="Name" class="large" />
                              </p>
                              <p>
                                  <label for="beschreibung">Beschreibung</label>
                                  <textarea name="beschreibung" id="beschreibung" class="xlarge" rows="5" cols="20"><?php
                                  if (isset($prefill['beschreibung'])){
                                    echo htmlspecialchars($prefill['beschreibung']);
                                  }?></textarea>
                              </p>
                              <p>
                                  <label for="homepage">Homepage</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['homepage'])){
                                    echo htmlspecialchars($prefill['homepage']);
                                  }else{
                                    echo "http://";
                                  }?>" name="homepage" id="homepage" title="Homepage" class="large" />
                              </p>
                              <p>
                                  <label for="abteilungsleiter">Abteilungsleiter</label>
                                  <?php
                                  if((isset($prefill['abteilungsleiter']) && !empty($prefill['abteilungsleiter'])) || (isset($prefill['abteilungsleiterdrop']) && !empty($prefill['abteilungsleiterdrop'])  && $prefill['abteilungsleiterdrop'] != "new")){
                                    // Es ist ein Abteilungsleiter festgelegt bzw. über Prefill einer übergeben worden (entweder per Textbox oder bei Selectbox)
                                    if ((!isset($prefill['abteilungsleiter'])) || (isset($prefill['abteilungsleiterdrop']) && !empty($prefill['abteilungsleiterdrop']) && $prefill['abteilungsleiterdrop'] != "new")){
                                        // Entweder kein Prefill vorhanden oder das Prefill übergibt bereits eine mitglieder_id für den Abteilungsleiter (mittels abteilungsleiterdrop)
                                        if (intval($prefill['abteilungsleiterdrop']) > 0){
                                          $abteilungsleiterid = $prefill['abteilungsleiterdrop'];
                                        }
                                        // Wir haben also nur eine ID. WIr lesen den Namen des Mitglieds aus, um mithilfe des Namens den Suchstring zusammenbauen zu können
                                        $pre_abteilungsleiter = mysql_query("SELECT `vorname`,`nachname` FROM `mitglieder` WHERE `mitglieder_id` = ".intval($abteilungsleiterid)."");
                                        $pre_abteilungsleiter_data = mysql_fetch_assoc($pre_abteilungsleiter);
                                        $abteilungsleiter_sql = "SELECT `vorname`,`nachname` FROM `mitglieder` WHERE (`nachname` LIKE '%".$pre_abteilungsleiter_data['vorname']."%' OR `vorname` LIKE '%".$pre_abteilungsleiter_data['vorname']."%') AND (`nachname` LIKE '%".$pre_abteilungsleiter_data['nachname']."%' OR `vorname` LIKE '%".$pre_abteilungsleiter_data['nachname']."%')";
                                    }elseif(isset($prefill['abteilungsleiter']) && is_string($prefill['abteilungsleiter'])){
                                        // Das Prefill übergibt den Namen des Abteilungsleiters, diesen an Leerzeichen trennen und alle Personen suchen, die übereinstimmungen im Vor- und/oder Nachnamen haben
                                        $namensteile = explode(" ",$prefill['abteilungsleiter']);
                                        $suchteile = array(); // Suchteile Array erstellen, welches alle Suchkriterien (Textteile des Namens) enthält
                                        foreach ($namensteile as &$suchteil) {
                                          $suchteile[] = "(`nachname` LIKE '%".$suchteil."%' OR `vorname` LIKE '%".$suchteil."%')";
                                        }
                                        $suchteile[] = "`status` = 1"; // Nur aktive Mitglieder verwenden
                                        $abteilungsleiter_sql = "SELECT `vorname`,`nachname` FROM `mitglieder` WHERE ".implode(" AND ",$suchteile);
                                    }
                                    // Ruft den Namen der Abteilungsleiter und die Anzahl ab, wie oft diese Nameskombination vorkommt

                                    $abteilungsleiter_count = mysql_query($abteilungsleiter_sql);
                                    if (mysql_num_rows($abteilungsleiter_count) == 0){
                                        // Es gibt genau keinen Nutzer mit dem Namen = Textbox leer anzeigen
                                        $abteilungsleiter_data = mysql_fetch_assoc($abteilungsleiter_count);
                                        echo "<input type=\"text\" value=\"\" name=\"abteilungsleiter\" id=\"abteilungsleiter\" title=\"Abteilungsleiter\" class=\"large\" />";
                                    }elseif (mysql_num_rows($abteilungsleiter_count) == 1){
                                        // Es gibt genau einen Nutzer mit dem Namen = Textbox mit dem anzeigen
                                        $abteilungsleiter_data = mysql_fetch_assoc($abteilungsleiter_count);
                                        echo "<input type=\"text\" value=\"".htmlspecialchars($abteilungsleiter_data['vorname']." ".$abteilungsleiter_data['nachname'])."\" name=\"abteilungsleiter\" id=\"abteilungsleiter\" title=\"Abteilungsleiter\" class=\"large\" />";
                                    }else{
                                        // Es gibt mehrere Nutzer mit identischem Namen = Selectbox mit weiteren Informationen zu diesen Nutzern anzeigen
                                        // Um eventuelle gleiche Kombinationen von Vorname-Nachname nicht mehrfach zu durchlaufen, gruppieren wir unsere Anfrage
                                        $moegl_abteilungsleiternamen = mysql_query($abteilungsleiter_sql." GROUP BY `vorname`,`nachname`");
                                        // Alle möglichen Nutzer auslesen
                                        echo "<select name=\"abteilungsleiterdrop\" size=\"1\" id=\"abteilungsleiterdrop\" class=\"xlarge\">";
                                        while ($moegl_abteilungsleitername = mysql_fetch_row($moegl_abteilungsleiternamen)){
                                          // Alle Möglichen Kombinationen von Vor- und Nachnamen, die mit der Eingabe übereinstimmen, durchlaufen
                                          $abteilungsleiter_mit_diesem_namen = mysql_query("SELECT * FROM `mitglieder` LEFT JOIN `orte` ON `orte`.`orts_id` = `mitglieder`.`anschrift` WHERE `vorname` = '".$moegl_abteilungsleitername['0']."' AND `nachname` = '".$moegl_abteilungsleitername['1']."' AND `mitglieder`.`status` = 1");
                                          while ($abteilungsleiter = mysql_fetch_assoc($abteilungsleiter_mit_diesem_namen)){
                                              echo "<option value=\"".$abteilungsleiter['mitglieder_id']."\"";
                                              if ((isset($prefill['abteilungsleiterdrop']) && intval($prefill['abteilungsleiterdrop']) == $abteilungsleiter['mitglieder_id'])){
                                                // Ist der aktuelle Schleifendurchlauf der bisherige Abteilungsleiter, diesen Eintrag vorselektieren
                                                echo " selected=\"selected\" ";
                                              }
                                              echo ">".htmlspecialchars($abteilungsleiter['vorname']." ".$abteilungsleiter['nachname'])." (".strftime("%d. %B %Y",strtotime($abteilungsleiter['geburtstag'])).", ".$abteilungsleiter['plz']." ".$abteilungsleiter['ort'].")</option>\n";
                                          }
                                        }
                                        echo "<option value=\"new\">Andere Person</option>
                                        </select>
                                        <input type=\"text\" value=\"\" name=\"abteilungsleiter\" id=\"abteilungsleiter\" title=\"Abteilungsleiter\" class=\"large nodisplay\" />";
                                        // Das Textfeld noch einfügen, aber nicht anzeigen. Es wird eingeblendet, falls der Eintrag "Andere Person" in der Selectbox gewählt wird
                                    }
                                  }else{
                                    // Es ist kein Abteilungsleiter festgelegt = Leeres Feld anzeigen
                                    echo "<input type=\"text\" value=\"\" name=\"abteilungsleiter\" id=\"abteilungsleiter\" title=\"Abteilungsleiter\" class=\"large\" />";
                                  }?>
                              </p>
                              <p>
                                  <label for="aktumleuro">Monatl. Aktivenumlage</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['aktumleuro'])){
                                    echo ($prefill['aktumleuro']);
                                  }else{
                                    echo "0";
                                  }?>" name="aktumleuro" id="aktumleuro" title="Euro" class="xxsmall" /> .
                                  <input type="text" value="<?php
                                  if (isset($prefill['aktumlcent'])){
                                    echo ($prefill['aktumlcent']);
                                  }else{
                                    echo "00";
                                  }?>" name="aktumlcent" title="Cent" id="aktumlcent" class="xxsmall" maxlength="2" /> €
                              </p>
                              <p>
                                  <input type="submit" name="addabteilung" id="addabteilung" value="Hinzufügen" title="Hinzufügen" class="button medium" />
                              </p>
                          </form>
                      </div>
            		  <div class="rightbox">
                      <h2>Aktionen</h2>
                      <ul class="nolistimg">
                        <li><img src="images/list.png" alt="" title="Zur Übersicht" /> <a href="abteilungen.php" title="Zurück zur Übersicht aller Abteilungen">Abteilungsverwaltung</a></li>
                      </ul>
            		  </div>
            		</div>

    		<div class="clearit">&nbsp;</div>
        </div>
<?php
// HTML-Fußbereich einbinden
require_once("inc/footer.inc.php");
?>