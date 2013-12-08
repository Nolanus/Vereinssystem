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
$title = "Mitglied erstellen";

// JavaScript einfügen
$appendjs = "
$(\"#abrechnung\").change(function(){
  if ($(this).val() == 0){
      $(\"#kontoverb\").slideDown();
  }else{
      $(\"#kontoverb\").slideUp();
  }
});
$(\"#mutterdrop\").change(function(){
  if ($(this).val() == \"new\"){
      $(\"#mutterdrop\").fadeOut(function(){
        $(\"#mutter\").fadeIn().select();
      });
  }
});
$(\"#vaterdrop\").change(function(){
  if ($(this).val() == \"new\"){
      $(\"#vaterdrop\").fadeOut(function(){
        $(\"#vater\").fadeIn().select();
      });
  }
});";

// HTML-Kopf einbinden
require_once("inc/head.inc.php");
?>
        <div id="content">
                 <div class="boxsystem33">
          		  <div class="leftbox">
                        <h2>Mitglied erstellen</h2>
                        <p>Über dieses Formular kann ein neues Mitglied in der Datenbank erstellt werden.</p>
                          <?php
                          if (isset($_GET['created']) && $_GET['created'] == "fail"){
                            if ($_GET['why'] == "data"){
                                echo "<div class=\"message error\"><p>Die Änderungen wurden aufgrund ungültiger Eingaben nicht übernommen.</p><ul>";
                                $fehlerarray = json_decode(base64_decode($_GET['errors']),true);
                                foreach ($fehlerarray as &$fehler) {
                                  echo "<li>$fehler</li>\n";
                                }
                                echo "</ul></div>";
                            }elseif($_GET['why'] == 1062){
                              // MySQL Error für doppelten Tabelleneintrag
                              echo "<div class=\"message error\"><p>Es ist bereits ein Mitglied mit diesen Daten vorhanden.</p></div>";
                            }else{
                              echo "<div class=\"message error\"><p>Beim Versuch, das neuen Mitglied anzulegen, ist leider ein Fehler aufgetreten. Bitte versuchen Sie es erneut oder wenden sich an den Systemadministrator.</p></div>";
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
                           <form id="editform" class="formular" method="post" action="member_process.php" accept-charset="utf-8">
                              <h3>Persönliches</h3>
                              <p>
                                  <label for="vorname">Vorname</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['vorname'])){
                                    echo htmlspecialchars($prefill['vorname']);
                                  }?>" name="vorname" id="vorname" class="medium" />
                              </p>
                              <p>
                                  <label for="nachname">Nachname</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['nachname'])){
                                    echo htmlspecialchars($prefill['nachname']);
                                  }?>" name="nachname" id="nachname" class="medium" />
                              </p>
                              <p>
                                  <label for="geschlecht">Geschlecht</label>
                                  <select name="geschlecht" size="1" class="medium" id="geschlecht">
                                    <?php
                                    echo "<option value=\"0\">Bitte wählen</option>
                                    <option value=\"1\"";
                                    if (isset($prefill['geschlecht']) && $prefill['geschlecht'] == 1){
                                      echo " selected=\"selected\" ";
                                    }
                                    echo ">männlich</option>";
                                    echo "<option value=\"2\"";
                                    if (isset($prefill['geschlecht']) && $prefill['geschlecht'] == 2){
                                      echo " selected=\"selected\" ";
                                    }
                                    echo ">weiblich</option>";
                                    ?>
                                  </select>
                              </p>
                              <p>
                                  <label for="geburtstagday">Geburtstag</label>
                                  <select name="geburtstagday" size="1" class="xxsmall" id="geburtstagday">
                                    <?php
                                    // Mittels einer For-Schleife von 1 bis 31 ein Auswahlfeld für den Tag erstellen
                                    for ($i=1; $i<=31; $i++)  {
                                      echo "<option value=\"$i\"";
                                      if (isset($prefill['geburtstagday']) && $prefill['geburtstagday'] == $i){
                                        // Ist der aktuelle Schleifendurchlauf der bisherige Tag des Geburtstags, diesen Eintrag vorselektieren
                                        echo " selected=\"selected\" ";
                                      }
                                      echo ">$i</option>\n";
                                    }
                                    ?>
                                  </select> .
                                  <select name="geburtstagmonth" size="1" class="small" id="geburtstagmonth">
                                    <?php
                                    // Mittels einer For-Schleife von 1 bis 12 ein Auswahlfeld für den Monat erstellen
                                    for ($i=1; $i<=12; $i++)  {
                                      echo "<option value=\"$i\"";
                                      if (isset($prefill['geburtstagmonth']) && $prefill['geburtstagmonth'] == $i){
                                        // Ist der aktuelle Schleifendurchlauf der bisherige Monat des Geburtstags, diesen Eintrag vorselektieren
                                        echo " selected=\"selected\" ";
                                      }
                                      echo ">".strftime("%B",mktime(0,0,0,$i))."</option>\n";
                                    }
                                    ?>
                                  </select> .
                                  <input type="text" value="<?php
                                  if (isset($prefill['strasse'])){
                                    echo htmlspecialchars($prefill['geburtstagyear']);
                                  }?>" name="geburtstagyear" id="geburtstagyear" maxlength="4" class="xsmall" />
                              </p>
                              <p>
                                  <label for="mutter">Mutter</label>
                                  <?php
                                  if((isset($prefill['mutter']) && !empty($prefill['mutter'])) || (isset($prefill['mutterdrop']) && !empty($prefill['mutterdrop']))){
                                    // Es ist eine Mutter festgelegt bzw. über Prefill eine übergeben worden
                                    if ((!isset($prefill['mutter'])) || (isset($prefill['mutterdrop']) && !empty($prefill['mutterdrop']))){
                                        // Entweder kein Prefill vorhanden oder das Prefill übergibt bereits eine mitglieder_id für die Mutter
                                        $mutterid = $prefill['mutterdrop'];
                                        $mutter_sql = "SELECT `vorname`,`nachname`,count(*) as count FROM `mitglieder` WHERE concat(`nachname`,`vorname`) = (SELECT concat(`nachname`,`vorname`) FROM mitglieder WHERE `mitglieder_id` = ".intval($mutterid).")";
                                    }elseif(isset($prefill['mutter']) && is_string($prefill['mutter'])){
                                        // Das Prefill übergibt den Namen der Mutter, diesen an Leerzeichen trennen und alle Personen suchen, die übereinstimmungen im Vor- und/oder Nachnamen haben
                                        $namensteile = explode(" ",$prefill['mutter']);
                                        $suchteile = array(); // Suchteile Array erstellen, welches alle Suchkriterien (Textteile des Namens) enthält
                                        foreach ($namensteile as &$suchteil) {
                                          $suchteile[] = "(`nachname` LIKE '%".$suchteil."%' OR `vorname` LIKE '%".$suchteil."%')";
                                        }
                                        $suchteile[] = "`status` = 1"; // Nur aktive Mitglieder verwenden
                                        $mutter_sql = "SELECT `vorname`,`nachname`,count(*) as count FROM `mitglieder` WHERE ".implode(" AND ",$suchteile);
                                    }
                                    // Ruft den Namen der Mutter und die Anzahl ab, wie oft diese Nameskombination vorkommt
                                    $mutter_count = mysql_query($mutter_sql);
                                    $mutter_data = mysql_fetch_assoc($mutter_count);
                                    if ($mutter_data['count'] == 0 || $mutter_data['count'] == 1){
                                        // Es gibt genau einen oder keinen Nutzer mit dem Namen = Textbox mit dem Namen bzw. leer anzeigen
                                        echo "<input type=\"text\" value=\"";
                                        if (isset($prefill['mutter'])){
                                          echo htmlspecialchars($prefill['mutter']);
                                        }
                                        echo "\" name=\"mutter\" id=\"mutter\" title=\"Mutter\" class=\"large\" />";
                                    }else{
                                        // Es gibt mehrere Nutzer mit identischem Namen = Selectbox mit weiteren Informationen zu diesen Nutzern anzeigen
                                        // Alle möglichen Nutzer auslesen
                                        $muetterdata = mysql_query("SELECT * FROM `mitglieder` LEFT JOIN `orte` ON `orte`.`orts_id` = `mitglieder`.`anschrift` WHERE `vorname` = '".$mutter_data['vorname']."' AND `nachname` = '".$mutter_data['nachname']."' AND `mitglieder`.`status` = 1");
                                        echo "<select name=\"mutterdrop\" size=\"1\" id=\"mutterdrop\" class=\"xlarge\">";
                                        while ($mutter = mysql_fetch_assoc($muetterdata)){
                                              echo "<option value=\"".$mutter['mitglieder_id']."\"";
                                              if (isset($prefill['mutterdrop']) && intval($prefill['mutterdrop']) == $mutter['mitglieder_id']){
                                                // Ist der aktuelle Schleifendurchlauf die bisherige Mutter, diesen Eintrag vorselektieren
                                                echo " selected=\"selected\" ";
                                              }
                                              echo ">".htmlspecialchars($mutter['vorname']." ".$mutter['nachname'])." (".strftime("%d. %B %Y",$mutter['geburtstag']).", ".$mutter['plz']." ".$mutter['ort'].")</option>\n";
                                        }
                                        echo "<option value=\"new\">Andere Person</option>
                                        </select>
                                        <input type=\"text\" value=\"\" name=\"mutter\" id=\"mutter\" title=\"Mutter\" class=\"large nodisplay\" />";
                                        // Das Textfeld noch einfügen, aber nicht anzeigen. Es wird eingeblendet, falls der Eintrag "Andere Person" in der Selectbox gewählt wird
                                    }
                                  }else{
                                    // Es ist keine Mutter festgelegt = Leeres Feld anzeigen
                                    echo "<input type=\"text\" value=\"\" name=\"mutter\" id=\"mutter\" title=\"Mutter\" class=\"large\" />";
                                  }?>
                              </p>
                              <p>
                                  <label for="vater">Vater</label>
                                  <?php
                                  if((isset($prefill['vater']) && !empty($prefill['vater'])) || (isset($prefill['vaterdrop']) && !empty($prefill['vaterdrop']))){
                                    // Es ist ein Vater festgelegt bzw. über Prefill eine übergeben worden
                                    if (isset($prefill['vaterdrop']) && !empty($prefill['vaterdrop'])){
                                        // Entweder kein Prefill vorhanden oder das Prefill übergibt bereits eine mitglieder_id für die Vater
                                        if (intval($prefill['vaterdrop']) > 0){
                                          $vaterid = $prefill['vaterdrop'];
                                        }
                                        $vater_sql = "SELECT `vorname`,`nachname`,count(*) as count FROM `mitglieder` WHERE concat(`nachname`,`vorname`) = (SELECT concat(`nachname`,`vorname`) FROM mitglieder WHERE `mitglieder_id` = ".intval($vaterid).")";
                                    }elseif(isset($prefill['vater']) && is_string($prefill['vater'])){
                                        // Das Prefill übergibt den Namen des Vaters, diesen an Leerzeichen trennen und alle Personen suchen, die übereinstimmungen im Vor- und/oder Nachnamen haben
                                        $namensteile = explode(" ",$prefill['vater']);
                                        $suchteile = array(); // Suchteile Array erstellen, welches alle Suchkriterien (Textteile des Namens) enthält
                                        foreach ($namensteile as &$suchteil) {
                                          $suchteile[] = "(`nachname` LIKE '%".$suchteil."%' OR `vorname` LIKE '%".$suchteil."%')";
                                        }
                                        $suchteile[] = "`status` = 1"; // Nur aktive Mitglieder verwenden
                                        $vater_sql = "SELECT `vorname`,`nachname`,count(*) as count FROM `mitglieder` WHERE ".implode(" AND ",$suchteile);
                                    }
                                    // Ruft den Namen der Vater und die Anzahl ab, wie oft diese Nameskombination vorkommt
                                    $vater_count = mysql_query($vater_sql);
                                    $vater_data = mysql_fetch_assoc($vater_count);
                                    if ($vater_data['count'] == 0 || $vater_data['count'] == 1){
                                        // Es gibt genau einen oder keinen Nutzer mit dem Namen = Textbox mit dem Namen bzw. leer anzeigen
                                        echo "<input type=\"text\" value=\"";
                                        if (isset($prefill['vater'])){
                                          echo htmlspecialchars($prefill['vater']);
                                        }
                                        echo "\" name=\"vater\" id=\"vater\" title=\"Vater\" class=\"large\" />";
                                    }else{
                                        // Es gibt mehrere Nutzer mit identischem Namen = Selectbox mit weiteren Informationen zu diesen Nutzern anzeigen
                                        // Alle möglichen Nutzer auslesen
                                        $vaterdata = mysql_query("SELECT * FROM `mitglieder` LEFT JOIN `orte` ON `orte`.`orts_id` = `mitglieder`.`anschrift` WHERE `vorname` = '".$vater_data['vorname']."' AND `nachname` = '".$vater_data['nachname']."' AND `mitglieder`.`status` = 1");
                                        echo "<select name=\"vaterdrop\" size=\"1\" id=\"vaterdrop\" class=\"xlarge\">";
                                        while ($vater = mysql_fetch_assoc($vaterdata)){
                                              echo "<option value=\"".$vater['mitglieder_id']."\"";
                                              if ((isset($prefill['vaterdrop']) && intval($prefill['vaterdrop']) == $vater['mitglieder_id'])){
                                                // Ist der aktuelle Schleifendurchlauf der bisherige Vater, diesen Eintrag vorselektieren
                                                echo " selected=\"selected\" ";
                                              }
                                              echo ">".htmlspecialchars($vater['vorname']." ".$vater['nachname'])." (".strftime("%d. %B %Y",$vater['geburtstag']).", ".$vater['plz']." ".$vater['ort'].")</option>\n";
                                        }
                                        echo "<option value=\"new\">Andere Person</option>
                                        </select>
                                        <input type=\"text\" value=\"\" name=\"vater\" id=\"vater\" title=\"Vater\" class=\"large nodisplay\" />";
                                        // Das Textfeld noch einfügen, aber nicht anzeigen. Es wird eingeblendet, falls der Eintrag "Andere Person" in der Selectbox gewählt wird
                                    }
                                  }else{
                                    // Es ist kein Vater festgelegt = Leeres Feld anzeigen
                                    echo "<input type=\"text\" value=\"\" name=\"vater\" id=\"vater\" title=\"Vater\" class=\"large\" />";
                                  }?>
                              </p>
                              <h3>Kontaktdaten</h3>
                              <p>
                                  <label for="strasse">Straße</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['strasse'])){
                                    echo htmlspecialchars($prefill['strasse']);
                                  }?>" name="strasse" id="strasse" title="Straße" class="medium" />
                                  <input type="text" value="<?php
                                  if (isset($prefill['hausnummer'])){
                                    echo htmlspecialchars($prefill['hausnummer']);
                                  }?>" title="Hausnummer" name="hausnummer" id="hausnummer" class="xsmall" />
                              </p>
                              <p>
                                  <label for="ort">Ort</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['plz'])){
                                    echo htmlspecialchars($prefill['plz']);
                                  }?>" name="plz" id="plz" title="PLZ" class="xsmall" maxlength="5" />
                                  <input type="text" value="<?php
                                  if (isset($prefill['ort'])){
                                    echo htmlspecialchars($prefill['ort']);
                                  }?>" name="ort" title="Ortsname" id="ort" class="medium" />
                              </p>
                              <p>
                                  <label for="telnr">Telefonnummer</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['telvorwahl'])){
                                    echo ($prefill['telvorwahl']);
                                  }?>" name="telvorwahl" id="telvorwahl" title="Vorwahl" class="xsmall" maxlength="5" /> /
                                  <input type="text" value="<?php
                                  if (isset($prefill['telnr'])){
                                    echo ($prefill['telnr']);
                                  }?>" name="telnr" title="Telefonnumer" id="telnr" class="medium" />
                              </p>
                              <p>
                                  <label for="handynr">Handynummer</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['hdyvorwahl'])){
                                    echo htmlspecialchars($prefill['hdyvorwahl']);
                                  }?>" name="hdyvorwahl" id="hdyvorwahl" class="xsmall" /> /
                                  <input type="text" value="<?php
                                  if (isset($prefill['handynr'])){
                                    echo htmlspecialchars($prefill['handynr']);
                                  }?>" name="handynr" id="handynr" class="medium" />
                              </p>
                              <p>
                                  <label for="email">E-Mail</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['email'])){
                                    echo htmlspecialchars($prefill['email']);
                                  }?>" name="email" id="email" class="large" />
                              </p>
                              <h3>Mitgliedschaft</h3>
                              <p>
                                  <label for="mitgliedschaft">Mitgliedschaft</label>
                                  <select name="mitgliedschaft" size="1" class="medium" id="mitgliedschaft">
                                    <?php
                                    echo "<option value=\"0\"";
                                    if ((isset($prefill['mitgliedschaft']) && $prefill['mitgliedschaft'] == 0)){
                                      echo " selected=\"selected\" ";
                                    }
                                    echo ">Normal / Aktiv</option>";
                                    echo "<option value=\"1\"";
                                    if ((isset($prefill['mitgliedschaft']) && $prefill['mitgliedschaft'] == 1)){
                                      echo " selected=\"selected\" ";
                                    }
                                    echo ">Unterstützend / Passiv</option>";
                                    echo "<option value=\"2\"";
                                    if ((isset($prefill['mitgliedschaft']) && $prefill['mitgliedschaft'] == 2)){
                                      echo " selected=\"selected\" ";
                                    }
                                    echo ">Ruhend</option>";
                                    ?>
                                  </select>
                              </p>
                              <p>
                                  <label for="beitrittday">Beitritt</label>
                                  <select name="beitrittday" size="1" class="xxsmall" id="beitrittday">
                                    <?php
                                    // Mittels einer For-Schleife von 1 bis 31 ein Auswahlfeld für den Tag erstellen
                                    for ($i=1; $i<=31; $i++)  {
                                      echo "<option value=\"$i\"";
                                      if ((!isset($prefill['beitrittmonth']) && $i == date("d")) || (isset($prefill['beitrittday']) && $prefill['beitrittday'] == $i)){
                                        // Ist der aktuelle Schleifendurchlauf der bisherige Tag des Geburtstags, diesen Eintrag vorselektieren
                                        echo " selected=\"selected\" ";
                                      }
                                      echo ">$i</option>\n";
                                    }
                                    ?>
                                  </select> .
                                  <select name="beitrittmonth" size="1" class="small" id="beitrittmonth">
                                    <?php
                                    // Mittels einer For-Schleife von 1 bis 12 ein Auswahlfeld für den Monat erstellen
                                    for ($i=1; $i<=12; $i++)  {
                                      echo "<option value=\"$i\"";
                                      if ( (!isset($prefill['beitrittmonth']) && $i == date("m")) || (isset($prefill['beitrittmonth']) && $prefill['beitrittmonth'] == $i)){
                                        // Ist der aktuelle Schleifendurchlauf der bisherige Monat des Geburtstags, diesen Eintrag vorselektieren
                                        echo " selected=\"selected\" ";
                                      }
                                      echo ">".strftime("%B",mktime(0,0,0,$i))."</option>\n";
                                    }
                                    ?>
                                  </select> .
                                  <input type="text" value="<?php
                                  if (isset($prefill['beitrittyear'])){
                                    echo htmlspecialchars($prefill['beitrittyear']);
                                  }else{
                                    echo date("Y");
                                  }?>" name="beitrittyear" id="beitrittyear" maxlength="4" class="xsmall" />
                              </p>
                              <p>
                                  <label for="abrechnung">Abrechnung</label>
                                  <select name="abrechnung" size="1" class="medium" id="abrechnung">
                                    <?php
                                    echo "<option value=\"0\"";
                                    if ((isset($prefill['abrechnung']) && $prefill['abrechnung'] == 0)){
                                      echo " selected=\"selected\" ";
                                    }
                                    echo ">Bankeinzug</option>";
                                    echo "<option value=\"1\"";
                                    if ((isset($prefill['abrechnung']) && $prefill['abrechnung'] == 1)){
                                      echo " selected=\"selected\" ";
                                    }
                                    echo ">Überweisung</option>";
                                    ?>
                                  </select>
                              </p>
                              <div id="kontoverb"<?php if ((isset($prefill['abrechnung']) && $prefill['abrechnung'] == 1)){echo " class=\"nodisplay\"";}?>>
                              <p>
                                  <label for="kontoinhaber">Kontoinhaber</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['kontoinhaber'])){
                                    echo htmlspecialchars($prefill['kontoinhaber']);
                                  }?>" name="kontoinhaber" id="kontoinhaber" title="Name des Kontoinhabers" class="medium" />
                              </p>
                              <p>
                                  <label for="kontonummer">Kontonummer</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['kontonummer'])){
                                    echo htmlspecialchars($prefill['kontonummer']);
                                  }?>" name="kontonummer" id="kontonummer" title="Kontonummer" class="medium" />
                              </p>
                              <p>
                                  <label for="blz">Bankverbindung</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['blz'])){
                                    echo htmlspecialchars($prefill['blz']);
                                  }?>" name="blz" id="blz" title="BLZ" class="xsmall" maxlength="12" />
                                  <input type="text" value="<?php
                                  if (isset($prefill['bankname'])){
                                    echo htmlspecialchars($prefill['bankname']);
                                  }?>" name="bankname" id="bankname" title="Bankname" class="medium" />
                              </p>
                              </div>
                              <p>
                                  <label for="username">Username</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['username'])){
                                    echo htmlspecialchars($prefill['username']);
                                  }?>" name="username" id="username" title="Username" class="medium" />
                              </p>
                              <p>
                                  <input type="submit" name="addmember" value="Erstellen" title="Erstellen" class="button medium" />
                              </p>
                          </form>
                      </div>
            		  <div class="rightbox">
                        <h2>Aktionen</h2>
                          <ul class="nolistimg">
                            <li><img src="images/list.png" alt="" title="Zur Übersicht" /> <a href="members.php" title="Zurück zur Übersicht aller Mitglieder">Mitgliederverwaltung</a></li>
                          </ul>
            		  </div>
            		</div>
    		<div class="clearit">&nbsp;</div>
        </div>
<?php
// HTML-Fußbereich einbinden
require_once("inc/footer.inc.php");
?>