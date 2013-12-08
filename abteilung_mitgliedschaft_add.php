<?php
// Datenbankverbindung & Kodierung einbinden
require_once("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once("inc/checkuser.inc.php");

// Rechte Check
if ($user['rights'] < 3){
  // Rechtelevel geringer als 3 = Kein Zugang
  if (isset($_SERVER["HTTP_REFERER"])){
    $referer = "?before=".base64_encode($_SERVER["HTTP_REFERER"]);
  }else{
    $referer = "";
  }
  header("Location: norights.php$referer");
  exit();
}

// Titel festlegen
$title = "Abteilungsmitgliedschaft hinzufügen";

// JavaScript einfügen
$appendjs = "$(\"#mitgliedsnamedrop\").change(function(){
  if ($(this).val() == \"new\"){
      $(\"#mitgliedsnamedrop\").fadeOut(function(){
        $(\"#mitgliedsname\").fadeIn().select();
      });
  }
});";

// HTML-Kopf einbinden
require_once("inc/head.inc.php");
?>
        <div id="content">
 <?php
        if (isset($_GET['id'])){
            $sql = "SELECT `abteilungen`.* FROM `abteilungen` WHERE `abteilungs_id` = ".intval($_GET['id'])." AND `abteilungen`.`status` = '1' ";
            $abteilungs_result = mysql_query($sql);
            //echo mysql_error();
            if (mysql_num_rows($abteilungs_result) == 1){
                $abteilung = mysql_fetch_assoc($abteilungs_result);
                if ($abteilung['abteilungsleiter'] == $user['mitglieder_id'] || $user['rights'] >= 4){
                ?>
            		<div class="boxsystem33">
            		  <div class="leftbox">
                          <h2>Abteilungsmitgliedschaft hinzufügen</h2>
                          <p>Über dieses Formular können Sie neue Mitglieder zur Abteilung <i><?php echo htmlspecialchars($abteilung['name']); ?></i> zuordnen. Geben Sie dazu entweder die Mitgliedsnummer oder den Namen des Mitglieds ein.</p>
                          <?php
                          if (isset($_GET['add'])){
                            if ($_GET['add'] == "fail"){
                              if ($_GET['why'] == "data"){
                                  echo "<div class=\"message error\"><p>Die Änderungen wurden aufgrund ungültiger Eingaben nicht übernommen.</p><ul>";
                                  $fehlerarray = json_decode(base64_decode($_GET['errors']),true);
                                  foreach ($fehlerarray as &$fehler) {
                                    echo "<li>$fehler</li>\n";
                                  }
                                  echo "</ul></div>";
                              }elseif($_GET['why'] == 1062){
                                // MySQL Error für doppelten Tabelleneintrag
                                echo "<div class=\"message error\"><p>Dieses Mitglied ist bereits der Abteilung ".htmlspecialchars($abteilung['name'])." zugeordnet.</p></div>";
                              }else{
                                echo "<div class=\"message error\"><p>Beim Versuch, die neuen Zugehörigkeit anzulegen, ist leider ein Fehler aufgetreten. Bitte versuchen Sie es erneut oder wenden sich an den Systemadministrator.</p></div>";
                              }
                              if (empty($_GET['data']) == false){
                                // Vorgaben für die Felder wurden übergeben; diese Werte in das Array $prefill schreiben
                                $prefill = json_decode(base64_decode($_GET['data']), true);
                                // Folgendes wendet stripslashes auf jedes Element des Arrays an
                                foreach ($prefill as $key=>$value) {
                                    $prefill[$key] = stripslashes($value);
                                }
                              }
                            }elseif($_GET['add'] == "success"){
                              echo "<div class=\"message success\"><p>Die Zugehörigkeit wurde erfolgreich eingerichtet.</p></div>";
                            }
                          }
                          ?>
                          <form id="addform" class="formular" method="post" action="abteilung_process.php" accept-charset="utf-8">
                              <p>
                                  <label for="mitgliedsnummer">Mitgliedsnummer</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['mitgliedsnummer'])){
                                    echo htmlspecialchars($prefill['mitgliedsnummer']);
                                  }?>" name="mitgliedsnummer" id="mitgliedsnummer" title="Mitgliedsnummer" class="large" />
                              </p>
                              <p>
                                  <label for="mitgliedsname">Mitgliedsname</label>
                                  <?php
                                  if((isset($prefill['mitgliedsname']) && !empty($prefill['mitgliedsname'])) || (isset($prefill['mitgliedsnamedrop']) && !empty($prefill['mitgliedsnamedrop'])  && $prefill['mitgliedsnamedrop'] != "new")){
                                    // Es ist ein Mitgliedsname festgelegt bzw. über Prefill einer übergeben worden (entweder per Textbox oder bei Selectbox)
                                    if ((!isset($prefill['mitgliedsname'])) || (isset($prefill['mitgliedsnamedrop']) && !empty($prefill['mitgliedsnamedrop']) && $prefill['mitgliedsnamedrop'] != "new")){
                                        // Entweder kein Prefill vorhanden oder das Prefill übergibt bereits eine mitglieder_id für den Mitgliedsname (mittels mitgliedsnamedrop)
                                        if (intval($prefill['mitgliedsnamedrop']) > 0){
                                          $mitgliedsid = $prefill['mitgliedsnamedrop'];
                                        }
                                        // Wir haben also nur eine ID. WIr lesen den Namen des Mitglieds aus, um mithilfe des Namens den Suchstring zusammenbauen zu können
                                        $pre_mitglied = mysql_query("SELECT `vorname`,`nachname` FROM `mitglieder` WHERE `mitglieder_id` = ".intval($mitgliedsid)."");
                                        $pre_mitglied_data = mysql_fetch_assoc($pre_mitglied);
                                        $mitglieds_sql = "SELECT `vorname`,`nachname` FROM `mitglieder` WHERE (`nachname` LIKE '%".$pre_mitglied_data['vorname']."%' OR `vorname` LIKE '%".$pre_mitglied_data['vorname']."%') AND (`nachname` LIKE '%".$pre_mitglied_data['nachname']."%' OR `vorname` LIKE '%".$pre_mitglied_data['nachname']."%')";
                                    }elseif(isset($prefill['mitgliedsname']) && is_string($prefill['mitgliedsname'])){
                                        // Das Prefill übergibt den Namen des Abteilungsleiters, diesen an Leerzeichen trennen und alle Personen suchen, die übereinstimmungen im Vor- und/oder Nachnamen haben
                                        $namensteile = explode(" ",$prefill['mitgliedsname']);
                                        $suchteile = array(); // Suchteile Array erstellen, welches alle Suchkriterien (Textteile des Namens) enthält
                                        foreach ($namensteile as &$suchteil) {
                                          $suchteile[] = "(`nachname` LIKE '%".$suchteil."%' OR `vorname` LIKE '%".$suchteil."%')";
                                        }
                                        $suchteile[] = "`status` = 1"; // Nur aktive Mitglieder verwenden
                                        $mitglieds_sql = "SELECT `vorname`,`nachname` FROM `mitglieder` WHERE ".implode(" AND ",$suchteile);
                                    }
                                    // Ruft den Namen der Mitgliedsname und die Anzahl ab, wie oft diese Nameskombination vorkommt

                                    $mitglied_count = mysql_query($mitglieds_sql);
                                    if (mysql_num_rows($mitglied_count) == 0){
                                        // Es gibt genau keinen Nutzer mit dem Namen = Textbox leer anzeigen
                                        $mitglieds_data = mysql_fetch_assoc($mitglied_count);
                                        echo "<input type=\"text\" value=\"\" name=\"mitgliedsname\" id=\"mitgliedsname\" title=\"Mitgliedsname\" class=\"large\" />";
                                    }elseif (mysql_num_rows($mitglied_count) == 1){
                                        // Es gibt genau einen Nutzer mit dem Namen = Textbox mit dem anzeigen
                                        $mitglieds_data = mysql_fetch_assoc($mitglied_count);
                                        echo "<input type=\"text\" value=\"".htmlspecialchars($mitglieds_data['vorname']." ".$mitglieds_data['nachname'])."\" name=\"mitgliedsname\" id=\"mitgliedsname\" title=\"Mitgliedsname\" class=\"large\" />";
                                    }else{
                                        // Es gibt mehrere Nutzer mit identischem Namen = Selectbox mit weiteren Informationen zu diesen Nutzern anzeigen
                                        // Um eventuelle gleiche Kombinationen von Vorname-Nachname nicht mehrfach zu durchlaufen, gruppieren wir unsere Anfrage
                                        $moegl_mitglieder = mysql_query($mitglieds_sql." GROUP BY `vorname`,`nachname`");
                                        // Alle möglichen Nutzer auslesen
                                        echo "<select name=\"mitgliedsnamedrop\" size=\"1\" id=\"mitgliedsnamedrop\" class=\"xlarge\">";
                                        while ($moegl_mitglied = mysql_fetch_row($moegl_mitglieder)){
                                          // Alle Möglichen Kombinationen von Vor- und Nachnamen, die mit der Eingabe übereinstimmen, durchlaufen
                                          $mitglieder_mit_diesem_namen = mysql_query("SELECT * FROM `mitglieder` LEFT JOIN `orte` ON `orte`.`orts_id` = `mitglieder`.`anschrift` WHERE `vorname` = '".$moegl_mitglied['0']."' AND `nachname` = '".$moegl_mitglied['1']."' AND `mitglieder`.`status` = 1");
                                          while ($mitglied = mysql_fetch_assoc($mitglieder_mit_diesem_namen)){
                                              echo "<option value=\"".$mitglied['mitglieder_id']."\"";
                                              if ((isset($prefill['mitgliedsnamedrop']) && intval($prefill['mitgliedsnamedrop']) == $mitglied['mitglieder_id'])){
                                                // Ist der aktuelle Schleifendurchlauf der bisherige Mitgliedsname, diesen Eintrag vorselektieren
                                                echo " selected=\"selected\" ";
                                              }
                                              echo ">".htmlspecialchars($mitglied['vorname']." ".$mitglied['nachname'])." (".strftime("%d. %B %Y",strtotime($mitglied['geburtstag'])).", ".$mitglied['plz']." ".$mitglied['ort'].")</option>\n";
                                          }
                                        }
                                        echo "<option value=\"new\">Andere Person</option>
                                        </select>
                                        <input type=\"text\" value=\"\" name=\"mitgliedsname\" id=\"mitgliedsname\" title=\"Mitgliedsname\" class=\"large nodisplay\" />";
                                        // Das Textfeld noch einfügen, aber nicht anzeigen. Es wird eingeblendet, falls der Eintrag "Andere Person" in der Selectbox gewählt wird
                                    }
                                  }else{
                                    // Es ist kein Mitgliedsname festgelegt = Leeres Feld anzeigen
                                    echo "<input type=\"text\" value=\"\" name=\"mitgliedsname\" id=\"mitgliedsname\" title=\"Mitgliedsname\" class=\"large\" />";
                                  }?>
                              </p>
                              <p>
                                  <label for="type">Art der Zugehörigkeit</label>
                                  <select name="type" id="type" size="1">
                                  <option value="1"<?php if (isset($prefill['type']) && $prefill['type'] == 1){echo " selected=\"selected\"";}?>>Aktive Zugehörigkeit</option>
                                  <option value="0"<?php if (isset($prefill['type']) && $prefill['type'] == 0){echo " selected=\"selected\"";}?>>Passive Zugehörigkeit</option>
                                  </select>
                              </p>
                              <p>
                                  <label for="afteradd">Im Anschluss</label>
                                  <select name="afteradd" id="afteradd" size="1">
                                  <option value="2"<?php if (isset($prefill['afteradd']) && $prefill['afteradd'] == 2){echo " selected=\"selected\"";}?>>Zurück zur Übersicht</option>
                                  <option value="1"<?php if (isset($prefill['afteradd']) && $prefill['afteradd'] == 1 || isset($_GET['return'])){echo " selected=\"selected\"";}?>>weitere Abteilungsmitgliedschaft hinzufügen</option>
                                  <option value="3"<?php if (isset($prefill['afteradd']) && $prefill['afteradd'] == 3){echo " selected=\"selected\"";}?>>Detailseite anzeigen</option>
                                  </select>
                              </p>
                              <p>
                                  <input type="hidden" name="abteilungs_id" value="<?php echo $abteilung['abteilungs_id'];?>" />
                                  <input type="submit" name="addabteilungsmember" id="addabteilungsmember" value="Hinzufügen" title="Hinzufügen" class="button medium" />
                              </p>
                          </form>
                          <p><img src="images/arrow_left.png" alt="" class="imageinline" /> <a href="abteilung_mitglieder.php?id=<?php echo $abteilung['abteilungs_id'];?>" title="Zurück zur Abteilungsmitgliedschaftsverwaltung">Zurück zur Übersicht</a></p>
                      </div>
            		  <div class="rightbox">
                        <?php include("inc/action_leiste_abteilungen.inc.php"); ?>
            		  </div>
            		</div>
            <?php }else{ // Ende Rechte-Check If-Teil
                      echo "<div class=\"message error\"><p>Für diese Aktion haben Sie nicht die erforderlichen Rechte. Bitte beachten Sie, dass Sie als Abteilungsleiter nur Ihre eigene Abteilung verwalten können.
                      Wenden Sie sich an den Systemadministrator, wenn Sie der Meinung sind, diese Funktion zu benötigen.<br /><a href=\"abteilungen.php\" title=\"Übersicht aller Abteilungen anzeigen\">Zur Abteilungsverwaltung</a></p></div>";
                    }
                }else{ ?>
                    <h2>Abteilungsmitglieder verwalten</h2>
                    <div class="message error"><p>Es wurde kein Abteilung mit dieser ID gefunden!<br /><a href="abteilungen.php" title="Übersicht aller Abteilungen anzeigen">Zur Abteilungsverwaltung</a></p></div>

        <?php  }
        }else{ ?>
            <h2>Abteilungsmitglieder verwalten</h2>
            <div class="message error"><p>Es wurde keine Abteilungs-ID übergeben!<br /><a href="abteilungen.php" title="Übersicht aller Abteilungen anzeigen">Zur Abteilungsverwaltung</a></p></div>
  <?php } ?>
    		<div class="clearit">&nbsp;</div>
        </div>
<?php
// HTML-Fußbereich einbinden
require_once("inc/footer.inc.php");
?>