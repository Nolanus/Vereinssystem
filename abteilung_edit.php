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
$title = "Abteilung bearbeiten";

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
        <?php
        if (isset($_GET['id'])){
            $sql = "SELECT `abteilungen`.*, `mitglieder`.`vorname`, `mitglieder`.`nachname` FROM `abteilungen`
                        LEFT JOIN `mitglieder` ON `abteilungen`.`abteilungsleiter` = `mitglieder`.`mitglieder_id`
                        WHERE `abteilungs_id` = ".intval($_GET['id'])." AND `abteilungen`.`status` = '1' ";
            $abteilungs_result = mysql_query($sql);
            //echo mysql_error();
            if (mysql_num_rows($abteilungs_result) == 1){
                $abteilung = mysql_fetch_assoc($abteilungs_result);
                if ($abteilung['abteilungsleiter'] == $user['mitglieder_id'] || $user['rights'] >= 4){
                ?>
            		<div class="boxsystem33">
            		  <div class="leftbox">
                          <h2>Abteilung bearbeiten</h2>
                          <p>Bearbeiten Sie eine Abteilung der Vereins. Um eine Abteilung zu löschen, wählen Sie das Abfalleimer-Symbol.</p>
                          <?php
                          if (isset($_GET['save'])){
                            if ($_GET['save'] == "success"){
                              echo "<div class=\"message success\"><p>Die Änderungen wurden erfolgreich gespeichert.</p></div>";
                            }elseif($_GET['save'] == "fail"){
                              if ($_GET['why'] == "data"){
                                echo "<div class=\"message error\"><p>Die Änderungen wurden aufgrund ungültiger Eingaben nicht übernommen.</p><ul>";
                                $fehlerarray = json_decode(base64_decode($_GET['errors']),true);
                                foreach ($fehlerarray as &$fehler) {
                                  echo "<li>$fehler</li>\n";
                                }
                                echo "</ul></div>";
                              }elseif($_GET['why'] == 1062){
                                // MySQL Error für doppelten Tabelleneintrag
                                echo "<div class=\"message error\"><p>Es ist bereits eine Abteilung mit diesen Daten vorhanden. Bitte verwenden Sie diesen und löschen gegebenenfalls den überflüssigen Eintrag.</p></div>";
                              }else{
                                // Vermutlich ein Fehler bei der Kommunikation mit der MySQL Datenbank. $_GET['why'] enthält die MySQL-Fehlernummer
                                echo "<div class=\"message error\"><p>Beim Versuch, die Änderungen zu speichern, ist leider ein Fehler aufgetreten. Bitte versuchen Sie es erneut oder wenden sich an den Systemadministrator.</p></div>";
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
                          }
                          ?>
                          <form id="editform" class="formular" method="post" action="abteilung_process.php" accept-charset="utf-8">
                              <p>
                                  <label for="name">Name</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['name'])){
                                    echo htmlspecialchars($prefill['name']);
                                  }else{
                                    echo htmlspecialchars($abteilung['name']);
                                  }?>" name="name" id="name" title="Name" class="large" />
                              </p>
                              <p>
                                  <label for="beschreibung">Beschreibung</label>
                                  <textarea name="beschreibung" id="beschreibung" class="xlarge" rows="5" cols="20"><?php
                                  if (isset($prefill['beschreibung'])){
                                    echo htmlspecialchars($prefill['beschreibung']);
                                  }else{
                                    echo htmlspecialchars($abteilung['beschreibung']);
                                  }
                                  ?></textarea>
                              </p>
                              <p>
                                  <label for="homepage">Homepage</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['homepage'])){
                                    echo htmlspecialchars($prefill['homepage']);
                                  }else{
                                    echo htmlspecialchars($abteilung['homepage']);
                                  }?>" name="homepage" id="homepage" title="Homepage" class="large" />
                              </p>
                              <p>
                                  <label for="abteilungsleiter">Abteilungsleiter</label>
                                  <?php
                                  if((!isset($prefill['abteilungsleiter']) && $abteilung['abteilungsleiter'] > 0) || (isset($prefill['abteilungsleiter']) && !empty($prefill['abteilungsleiter'])) || (isset($prefill['abteilungsleiterdrop']) && !empty($prefill['abteilungsleiterdrop'])  && $prefill['abteilungsleiterdrop'] != "new")){
                                    // Es ist ein Abteilungsleiter festgelegt bzw. über Prefill einer übergeben worden (entweder per Textbox oder bei Selectbox)
                                    if ((!isset($prefill['abteilungsleiter'])) || (isset($prefill['abteilungsleiterdrop']) && !empty($prefill['abteilungsleiterdrop']) && $prefill['abteilungsleiterdrop'] != "new")){
                                        // Entweder kein Prefill vorhanden oder das Prefill übergibt bereits eine mitglieder_id für den Abteilungsleiter (mittels abteilungsleiterdrop)
                                        if (isset($prefill['abteilungsleiter']) && intval($prefill['abteilungsleiterdrop']) > 0){
                                          $abteilungsleiterid = $prefill['abteilungsleiterdrop'];
                                        }else{
                                          $abteilungsleiterid = $abteilung['abteilungsleiter'];
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
                                    echo $abteilungsleiter_sql;
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
                                              if ((!isset($prefill['abteilungsleiterdrop']) && $abteilungsleiter['mitglieder_id'] == $abteilung['abteilungsleiter']) || (isset($prefill['abteilungsleiterdrop']) && intval($prefill['abteilungsleiterdrop']) == $abteilungsleiter['mitglieder_id'])){
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
                                  <?php
                                  $nachkomma = $abteilung['aktivenumlage'] % 100;
                                  $vorkomma = ($abteilung['aktivenumlage'] - $nachkomma)/100;
                                  ?>
                                  <input type="text" value="<?php
                                  if (isset($prefill['aktumleuro'])){
                                    echo ($prefill['aktumleuro']);
                                  }else{
                                    echo ($vorkomma);
                                  }?>" name="aktumleuro" id="aktumleuro" title="Euro" class="xxsmall" /> .
                                  <input type="text" value="<?php
                                  if (isset($prefill['aktumlcent'])){
                                    echo ($prefill['aktumlcent']);
                                  }else{
                                    echo str_pad($nachkomma, 2 ,'0', STR_PAD_LEFT);
                                  }?>" name="aktumlcent" title="Cent" id="aktumlcent" class="xxsmall" maxlength="2" /> €
                              </p>
                              <p>
                                  <input type="hidden" name="abteilungs_id" value="<?php echo $abteilung['abteilungs_id'];?>" />
                                  <input type="submit" name="saveabteilung" id="saveabteilung" value="Speichern" title="Speichern" class="button medium" />
                              </p>
                          </form>
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
                    <h2>Abteilung bearbeiten</h2>
                    <div class="message error"><p>Es wurde kein Abteilung mit dieser ID gefunden!<br /><a href="abteilungen.php" title="Übersicht aller Abteilungen anzeigen">Zur Abteilungsverwaltung</a></p></div>

        <?php     }
        }else{ ?>
            <h2>Abteilung bearbeiten</h2>
            <div class="message error"><p>Es wurde keine Abteilungs-ID übergeben!<br /><a href="abteilungen.php" title="Übersicht aller Abteilungen anzeigen">Zur Abteilungsverwaltung</a></p></div>
  <?php } ?>
    		<div class="clearit">&nbsp;</div>
        </div>
<?php
// HTML-Fußbereich einbinden
require_once("inc/footer.inc.php");
?>