<?php
// Datenbankverbindung & Kodierung einbinden
require_once("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once("inc/checkuser.inc.php");

// Titel festlegen
$title = "Mitgliedsprofil bearbeiten";

// JavaScript einfügen
$appendjs = "$(\"#strasse,#hausnummer,#plz,#ort,#telnr,#telvorwahl\").keydown(function(){
    $(\"#locationchangep\").slideDown();
});
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
        <?php
        if (isset($_GET['id'])){
            $sql = "SELECT * FROM `mitglieder`
                LEFT JOIN `orte` ON `mitglieder`.`anschrift` = `orte`.`orts_id`
                WHERE `mitglieder`.`mitglieder_id` = ".intval($_GET['id'])." AND `mitglieder`.`status` = '1' ";
            $member_result = mysql_query($sql);
            echo mysql_error();
            if (mysql_num_rows($member_result) == 1){
                $member = mysql_fetch_assoc($member_result);

                if ($member['parent2'] == $user['mitglieder_id'] && time()-strtotime($member['geburtstag']) < 568024668){
                    $isfather = true;
                }else{
                    $isfather = false;
                }
                if ($member['parent1'] == $user['mitglieder_id'] && time()-strtotime($member['geburtstag']) < 568024668){
                    $ismother = true;
                }else{
                    $ismother = false;
                }
                if ($user['rights'] >= 4 || $member['mitglieder_id'] == $user['mitglieder_id'] || $isfather || $ismother){
                ?>
            		<div class="boxsystem33">
            		  <div class="leftbox">
                          <h2>Mitgliedsprofil bearbeiten</h2>
                          <p>Bearbeiten Sie die Eigenschaft und Mitgliedschaft eines Vereinsmitglieds.</p>
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
                                echo "<div class=\"message error\"><p>Es ist bereits ein Mitglied mit diesen Daten vorhanden. Bitte verwenden Sie dieses und löschen gegebenenfalls den überflüssigen Eintrag.</p></div>";
                              }else{
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
                          <form id="editform" class="formular" method="post" action="member_process.php" accept-charset="utf-8">
                              <h3>Persönliches</h3>
                              <p>
                                  <label for="vorname">Vorname</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['vorname'])){
                                    echo htmlspecialchars($prefill['vorname']);
                                  }else{
                                    echo htmlspecialchars($member['vorname']);
                                  }?>" name="vorname" id="vorname" class="medium" />
                              </p>
                              <p>
                                  <label for="nachname">Nachname</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['nachname'])){
                                    echo htmlspecialchars($prefill['nachname']);
                                  }else{
                                    echo htmlspecialchars($member['nachname']);
                                  }?>" name="nachname" id="nachname" class="medium" />
                              </p>
                              <p>
                                  <label for="geschlecht">Geschlecht</label>
                                  <select name="geschlecht" size="1" class="medium" id="geschlecht">
                                    <?php
                                    echo "<option value=\"0\">Bitte wählen</option>
                                    <option value=\"1\"";
                                    if (($member['geschlecht'] == 1 && !isset($prefill['geschlecht']) || (isset($prefill['geschlecht']) && $prefill['geschlecht'] == 1))){
                                      echo " selected=\"selected\" ";
                                    }
                                    echo ">männlich</option>";
                                    echo "<option value=\"2\"";
                                    if (($member['geschlecht'] == 2 && !isset($prefill['geschlecht']) || (isset($prefill['geschlecht']) && $prefill['geschlecht'] == 2))){
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
                                    $bdaytimestamp = strtotime($member['geburtstag']);
                                    $aktuellertag = intval(strftime("%d",$bdaytimestamp));
                                    // Mittels einer For-Schleife von 1 bis 31 ein Auswahlfeld für den Tag erstellen
                                    for ($i=1; $i<=31; $i++)  {
                                      echo "<option value=\"$i\"";
                                      if (($i == $aktuellertag && !isset($prefill['geburtstagday'])) || isset($prefill['geburtstagday']) && $prefill['geburtstagday'] == $i){
                                        // Ist der aktuelle Schleifendurchlauf der bisherige Tag des Geburtstags, diesen Eintrag vorselektieren
                                        echo " selected=\"selected\" ";
                                      }
                                      echo ">$i</option>\n";
                                    }
                                    ?>
                                  </select> .
                                  <select name="geburtstagmonth" size="1" class="small" id="geburtstagmonth">
                                    <?php
                                    $aktuellermonat = intval(strftime("%m",$bdaytimestamp));
                                    // Mittels einer For-Schleife von 1 bis 12 ein Auswahlfeld für den Monat erstellen
                                    for ($i=1; $i<=12; $i++)  {
                                      echo "<option value=\"$i\"";
                                      if (($i == $aktuellermonat && !isset($prefill['geburtstagmonth'])) || isset($prefill['geburtstagmonth']) && $prefill['geburtstagmonth'] == $i){
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
                                  }else{
                                    echo strftime("%Y",$bdaytimestamp);
                                  }?>" name="geburtstagyear" id="geburtstagyear" maxlength="4" class="xsmall" />
                              </p>
                              <?php
                              if ($user['rights'] >= 4){
                                // Nur Rechtelevel 4 oder höher können Familienbeziehungen bearbeiten
                              ?>
                              <p>
                                  <label for="mutter">Mutter</label>
                                  <?php
                                  if((!isset($prefill['mutter']) && $member['parent1'] > 0) || (isset($prefill['mutter']) && !empty($prefill['mutter'])) || (isset($prefill['mutterdrop']) && !empty($prefill['mutterdrop']))){
                                    // Es ist eine Mutter festgelegt bzw. über Prefill einer übergeben worden (entweder per Textbox oder bei Selectbox)
                                    if ((!isset($prefill['mutter'])) || (isset($prefill['mutterdrop']) && !empty($prefill['mutterdrop'])  && $prefill['mutterdrop'] != "new")){
                                        // Entweder kein Prefill vorhanden oder das Prefill übergibt bereits eine mitglieder_id für die Mutter (mittels mutter)
                                        if (isset($prefill['mutterdrop']) && intval($prefill['mutterdrop']) > 0){
                                          $mutterid = $prefill['mutterdrop'];
                                        }else{
                                          $mutterid = $member['parent1'];
                                        }
                                        // Wir haben also nur eine ID. WIr lesen den Namen des Mitglieds aus, um mithilfe des Namens den Suchstring zusammenbauen zu können
                                        $pre_mutter = mysql_query("SELECT `vorname`,`nachname` FROM `mitglieder` WHERE `mitglieder_id` = ".intval($mutterid)."");
                                        $pre_mutter_data = mysql_fetch_assoc($pre_mutter);
                                        $mutter_sql = "SELECT `vorname`,`nachname` FROM `mitglieder` WHERE (`nachname` LIKE '%".$pre_mutter_data['vorname']."%' OR `vorname` LIKE '%".$pre_mutter_data['vorname']."%') AND (`nachname` LIKE '%".$pre_mutter_data['nachname']."%' OR `vorname` LIKE '%".$pre_mutter_data['nachname']."%')";
                                    }elseif(isset($prefill['mutter']) && is_string($prefill['mutter'])){
                                        // Das Prefill übergibt den Namen der Mutter, diesen an Leerzeichen trennen und alle Personen suchen, die übereinstimmungen im Vor- und/oder Nachnamen haben
                                        $namensteile = explode(" ",$prefill['mutter']);
                                        $suchteile = array(); // Suchteile Array erstellen, welches alle Suchkriterien (Textteile des Namens) enthält
                                        foreach ($namensteile as &$suchteil) {
                                          $suchteile[] = "(`nachname` LIKE '%".$suchteil."%' OR `vorname` LIKE '%".$suchteil."%')";
                                        }
                                        $suchteile[] = "`status` = 1"; // Nur aktive Mitglieder verwenden
                                        $mutter_sql = "SELECT `vorname`,`nachname` FROM `mitglieder` WHERE ".implode(" AND ",$suchteile);
                                    }
                                    // Ruft den Namen der Mutter und die Anzahl ab, wie oft diese Nameskombination vorkommt
                                    //echo $mutter_sql;
                                    $mutter_count = mysql_query($mutter_sql);
                                    if (mysql_num_rows($mutter_count) == 0){
                                        // Es gibt genau keinen Nutzer mit dem Namen = Textbox leer anzeigen
                                        $mutter_data = mysql_fetch_assoc($mutter_count);
                                        echo "<input type=\"text\" value=\"\" name=\"mutter\" id=\"mutter\" title=\"Mutter\" class=\"large\" />";
                                    }elseif (mysql_num_rows($mutter_count) == 1){
                                        // Es gibt genau einen Nutzer mit dem Namen = Textbox mit dem anzeigen
                                        $mutter_data = mysql_fetch_assoc($mutter_count);
                                        echo "<input type=\"text\" value=\"".htmlspecialchars($mutter_data['vorname']." ".$mutter_data['nachname'])."\" name=\"mutter\" id=\"mutter\" title=\"Mutter\" class=\"large\" />";
                                    }else{
                                        // Es gibt mehrere Nutzer mit identischem Namen = Selectbox mit weiteren Informationen zu diesen Nutzern anzeigen
                                        // Um eventuelle gleiche Kombinationen von Vorname-Nachname nicht mehrfach zu durchlaufen, gruppieren wir unsere Anfrage nochmal
                                        $moegl_muetternamen = mysql_query($mutter_sql." GROUP BY `vorname`,`nachname`");
                                        // Alle möglichen Nutzer auslesen
                                        echo "<select name=\"mutterdrop\" size=\"1\" id=\"mutterdrop\" class=\"xlarge\">";
                                        while ($moegl_muttername = mysql_fetch_row($moegl_muetternamen)){
                                          // Alle Möglichen Kombinationen von Vor- und Nachnamen, die mit der Eingabe übereinstimmen, durchlaufen
                                          $muetter_mit_diesem_namen = mysql_query("SELECT * FROM `mitglieder` LEFT JOIN `orte` ON `orte`.`orts_id` = `mitglieder`.`anschrift` WHERE `vorname` = '".$moegl_muttername['0']."' AND `nachname` = '".$moegl_muttername['1']."' AND `mitglieder`.`status` = 1");
                                          while ($mutter = mysql_fetch_assoc($muetter_mit_diesem_namen)){
                                              echo "<option value=\"".$mutter['mitglieder_id']."\"";
                                              if ((!isset($prefill['mutterdrop']) && $mutter['mitglieder_id'] == $member['parent1']) || (isset($prefill['mutterdrop']) && intval($prefill['mutterdrop']) == $mutter['mitglieder_id'])){
                                                // Ist der aktuelle Schleifendurchlauf die bisherige Mutter, diesen Eintrag vorselektieren
                                                echo " selected=\"selected\" ";
                                              }
                                              echo ">".htmlspecialchars($mutter['vorname']." ".$mutter['nachname'])." (".strftime("%d. %B %Y",strtotime($mutter['geburtstag'])).", ".$mutter['plz']." ".$mutter['ort'].")</option>\n";
                                          }
                                        }
                                        echo "<option value=\"new\">Andere Person</option>
                                        </select>
                                        <input type=\"text\" value=\"\" name=\"mutter\" id=\"mutter\" title=\"Mutter\" class=\"large nodisplay\" />";
                                        // Das Textfeld noch einfügen, aber nicht anzeigen. Es wird eingeblendet, falls der Eintrag "Andere Person" in der Selectbox gewählt wird
                                    }
                                  }else{
                                    // Es ist kein Mutter festgelegt = Leeres Feld anzeigen
                                    echo "<input type=\"text\" value=\"\" name=\"mutter\" id=\"mutter\" title=\"Mutter\" class=\"large\" />";
                                  }?>
                              </p>
                              <p>
                                  <label for="vater">Vater</label>
                                  <?php
                                  if((!isset($prefill['vater']) && $member['parent2'] > 0) || (isset($prefill['vater']) && !empty($prefill['vater'])) || (isset($prefill['vaterdrop']) && !empty($prefill['vaterdrop']))){
                                    // Es ist ein Vater festgelegt bzw. über Prefill einer übergeben worden (entweder per Textbox oder bei Selectbox)
                                    if ((!isset($prefill['vater'])) || (isset($prefill['vaterdrop']) && !empty($prefill['vaterdrop'])  && $prefill['vaterdrop'] != "new")){
                                        // Entweder kein Prefill vorhanden oder das Prefill übergibt bereits eine mitglieder_id für die Vater (mittels vaterdrop)
                                        if (isset($prefill['vaterdrop']) && intval($prefill['vaterdrop']) > 0){
                                          $vaterid = $prefill['vaterdrop'];
                                        }else{
                                          $vaterid = $member['parent2'];
                                        }
                                        // Wir haben also nur eine ID. WIr lesen den Namen des Mitglieds aus, um mithilfe des Namens den Suchstring zusammenbauen zu können
                                        $pre_vater = mysql_query("SELECT `vorname`,`nachname` FROM `mitglieder` WHERE `mitglieder_id` = ".intval($vaterid)."");
                                        $pre_vater_data = mysql_fetch_assoc($pre_vater);
                                        $vater_sql = "SELECT `vorname`,`nachname` FROM `mitglieder` WHERE (`nachname` LIKE '%".$pre_vater_data['vorname']."%' OR `vorname` LIKE '%".$pre_vater_data['vorname']."%') AND (`nachname` LIKE '%".$pre_vater_data['nachname']."%' OR `vorname` LIKE '%".$pre_vater_data['nachname']."%')";
                                    }elseif(isset($prefill['vater']) && is_string($prefill['vater'])){
                                        // Das Prefill übergibt den Namen des Vaters, diesen an Leerzeichen trennen und alle Personen suchen, die übereinstimmungen im Vor- und/oder Nachnamen haben
                                        $namensteile = explode(" ",$prefill['vater']);
                                        $suchteile = array(); // Suchteile Array erstellen, welches alle Suchkriterien (Textteile des Namens) enthält
                                        foreach ($namensteile as &$suchteil) {
                                          $suchteile[] = "(`nachname` LIKE '%".$suchteil."%' OR `vorname` LIKE '%".$suchteil."%')";
                                        }
                                        $suchteile[] = "`status` = 1"; // Nur aktive Mitglieder verwenden
                                        $vater_sql = "SELECT `vorname`,`nachname` FROM `mitglieder` WHERE ".implode(" AND ",$suchteile);
                                    }
                                    // Ruft den Namen der Vater und die Anzahl ab, wie oft diese Nameskombination vorkommt
                                    //echo $vater_sql;
                                    $vater_count = mysql_query($vater_sql);
                                    if (mysql_num_rows($vater_count) == 0){
                                        // Es gibt genau keinen Nutzer mit dem Namen = Textbox leer anzeigen
                                        $vater_data = mysql_fetch_assoc($vater_count);
                                        echo "<input type=\"text\" value=\"\" name=\"vater\" id=\"vater\" title=\"Vater\" class=\"large\" />";
                                    }elseif (mysql_num_rows($vater_count) == 1){
                                        // Es gibt genau einen Nutzer mit dem Namen = Textbox mit dem anzeigen
                                        $vater_data = mysql_fetch_assoc($vater_count);
                                        echo "<input type=\"text\" value=\"".htmlspecialchars($vater_data['vorname']." ".$vater_data['nachname'])."\" name=\"vater\" id=\"vater\" title=\"Vater\" class=\"large\" />";
                                    }else{
                                        // Es gibt mehrere Nutzer mit identischem Namen = Selectbox mit weiteren Informationen zu diesen Nutzern anzeigen
                                        // Um eventuelle gleiche Kombinationen von Vorname-Nachname nicht mehrfach zu durchlaufen, gruppieren wir unsere Anfrage
                                        $moegl_vaeternamen = mysql_query($vater_sql." GROUP BY `vorname`,`nachname`");
                                        // Alle möglichen Nutzer auslesen
                                        echo "<select name=\"vaterdrop\" size=\"1\" id=\"vaterdrop\" class=\"xlarge\">";
                                        while ($moegl_vatername = mysql_fetch_row($moegl_vaeternamen)){
                                          // Alle Möglichen Kombinationen von Vor- und Nachnamen, die mit der Eingabe übereinstimmen, durchlaufen
                                          $vaeter_mit_diesem_namen = mysql_query("SELECT * FROM `mitglieder` LEFT JOIN `orte` ON `orte`.`orts_id` = `mitglieder`.`anschrift` WHERE `vorname` = '".$moegl_vatername['0']."' AND `nachname` = '".$moegl_vatername['1']."' AND `mitglieder`.`status` = 1");
                                          while ($vater = mysql_fetch_assoc($vaeter_mit_diesem_namen)){
                                              echo "<option value=\"".$vater['mitglieder_id']."\"";
                                              if ((!isset($prefill['vaterdrop']) && $vater['mitglieder_id'] == $member['parent2']) || (isset($prefill['vaterdrop']) && intval($prefill['vaterdrop']) == $vater['mitglieder_id'])){
                                                // Ist der aktuelle Schleifendurchlauf der bisherige Vater, diesen Eintrag vorselektieren
                                                echo " selected=\"selected\" ";
                                              }
                                              echo ">".htmlspecialchars($vater['vorname']." ".$vater['nachname'])." (".strftime("%d. %B %Y",strtotime($vater['geburtstag'])).", ".$vater['plz']." ".$vater['ort'].")</option>\n";
                                          }
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
                              <?php
                              } //Ende Rechtelevel 4 oder höher only
                              ?>
                              <h3>Kontaktdaten</h3>
                              <p>
                                  <label for="strasse">Straße</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['strasse'])){
                                    echo htmlspecialchars($prefill['strasse']);
                                  }else{
                                    echo htmlspecialchars($member['strasse']);
                                  }?>" name="strasse" id="strasse" title="Straße" class="medium" />
                                  <input type="text" value="<?php
                                  if (isset($prefill['hausnummer'])){
                                    echo htmlspecialchars($prefill['hausnummer']);
                                  }else{
                                    echo htmlspecialchars($member['hausnummer']);
                                  }?>" title="Hausnummer" name="hausnummer" id="hausnummer" class="xsmall" />
                              </p>
                              <p>
                                  <label for="ort">Ort</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['plz'])){
                                    echo htmlspecialchars($prefill['plz']);
                                  }else{
                                    echo htmlspecialchars($member['plz']);
                                  }?>" name="plz" id="plz" title="PLZ" class="xsmall" maxlength="5" />
                                  <input type="text" value="<?php
                                  if (isset($prefill['ort'])){
                                    echo htmlspecialchars($prefill['ort']);
                                  }else{
                                    echo htmlspecialchars($member['ort']);
                                  }?>" name="ort" title="Ortsname" id="ort" class="medium" />
                              </p>
                              <p>
                                  <label for="telnr">Telefonnummer</label>
                                  <?php
                                  // Gespeicherte Telefonnummer zerlegen, um die Teile in den entsprechenden Input-Feldern anzeigen zu können
                                  if ($member['telefon'] != ""){
                                    list($vorwahl,$telnr) = explode("/",$member['telefon']);
                                  }
                                  ?>
                                  <input type="text" value="<?php
                                  if (isset($prefill['telvorwahl'])){
                                    echo ($prefill['telvorwahl']);
                                  }elseif(isset($vorwahl)){
                                    echo $vorwahl;
                                  }?>" name="telvorwahl" id="telvorwahl" title="Vorwahl" class="xsmall" maxlength="5" /> /
                                  <input type="text" value="<?php
                                  if (isset($prefill['telnr'])){
                                    echo ($prefill['telnr']);
                                  }elseif(isset($telnr)){
                                    echo $telnr;
                                  }?>" name="telnr" title="Telefonnumer" id="telnr" class="medium" />
                              </p>
                              <p id="locationchangep" class="nodisplay">
                                  <label for="locationchange">Ortsänderung gilt</label>
                                  <select name="locationchange" size="1" class="large" id="locationchange">
                                        <option value="0"<?php if (isset($prefill['locationchange']) && $prefill['locationchange'] == 0){echo " selected=\"selected\"";}?>>... nur für dieses Mitglied</option>
                                        <?php if ($user['rights'] >= 4){ ?>
                                            <option value="1"<?php if (isset($prefill['locationchange']) && $prefill['locationchange'] == 1){echo " selected=\"selected\"";}?>>... für dieses Mitglied und die Eltern</option>
                                        <?php } ?>
                                        <option value="2"<?php if (isset($prefill['locationchange']) && $prefill['locationchange'] == 2){echo " selected=\"selected\"";}?>>... für dieses Mitglied und die Kinder</option>
                                        <?php if ($user['rights'] >= 4){ ?>
                                            <option value="2"<?php if (isset($prefill['locationchange']) && $prefill['locationchange'] == 3){echo " selected=\"selected\"";}?>>... für dieses Mitglied, die Eltern und Kinder</option>
                                            <option value="3"<?php if (isset($prefill['locationchange']) && $prefill['locationchange'] == 4){echo " selected=\"selected\"";}?>>... für dieses Mitglied und alle Mitglieder, die ebenfalls dort wohnten</option>
                                        <?php } ?>
                                  </select>
                              </p>
                              <p>
                                  <label for="handynr">Handynummer</label>
                                  <?php
                                  // Gespeicherte Handynummer zerlegen, um die Teile in den entsprechenden Input-Feldern anzeigen zu können
                                  if ($member['handy'] != ""){
                                    list($netznr,$handynr) = explode("/",$member['handy']);
                                  }
                                  ?>
                                  <input type="text" value="<?php
                                  if (isset($prefill['hdyvorwahl'])){
                                    echo htmlspecialchars($prefill['hdyvorwahl']);
                                  }elseif(isset($netznr)){
                                    echo $netznr;
                                  }?>" name="hdyvorwahl" id="hdyvorwahl" class="xsmall" /> /
                                  <input type="text" value="<?php
                                  if (isset($prefill['handynr'])){
                                    echo htmlspecialchars($prefill['handynr']);
                                  }elseif(isset($handynr)){
                                    echo $handynr;
                                  }?>" name="handynr" id="handynr" class="medium" />
                              </p>
                              <p>
                                  <label for="email">E-Mail</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['email'])){
                                    echo htmlspecialchars($prefill['email']);
                                  }else{
                                    echo htmlspecialchars($member['email']);
                                  }?>" name="email" id="email" class="large" />
                              </p>
                              <h3>Mitgliedschaft</h3>
                              <?php
                              if ($user['rights'] >= 4){
                                // Nur Rechtelevel 4 oder höher können Familienbeziehungen bearbeiten
                              ?>
                              <p>
                                  <label for="mitgliedschaft">Mitgliedschaft</label>
                                  <select name="mitgliedschaft" size="1" class="medium" id="mitgliedschaft">
                                    <?php
                                    echo "<option value=\"0\"";
                                    if ((isset($prefill['mitgliedschaft']) && $prefill['mitgliedschaft'] == 0) || (!isset($prefill['mitgliedschaft']) && $member['mitgliedschaft'] == 0)){
                                      echo " selected=\"selected\" ";
                                    }
                                    echo ">Normal / Aktiv</option>";
                                    echo "<option value=\"1\"";
                                    if ((isset($prefill['mitgliedschaft']) && $prefill['mitgliedschaft'] == 1) || (!isset($prefill['mitgliedschaft']) && $member['mitgliedschaft'] == 1)){
                                      echo " selected=\"selected\" ";
                                    }
                                    echo ">Unterstützend / Passiv</option>";
                                    echo "<option value=\"2\"";
                                    if ((isset($prefill['mitgliedschaft']) && $prefill['mitgliedschaft'] == 2) || (!isset($prefill['mitgliedschaft']) && $member['mitgliedschaft'] == 2)){
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
                                    $beitritttimestamp = strtotime($member['beitritt']);
                                    $aktuellertag = intval(strftime("%d",$beitritttimestamp));
                                    // Mittels einer For-Schleife von 1 bis 31 ein Auswahlfeld für den Tag erstellen
                                    for ($i=1; $i<=31; $i++)  {
                                      echo "<option value=\"$i\"";
                                      if (($i == $aktuellertag && !isset($prefill['beitrittday'])) || (isset($prefill['beitrittday']) && $prefill['beitrittday'] == $i)){
                                        // Ist der aktuelle Schleifendurchlauf der bisherige Tag des Geburtstags, diesen Eintrag vorselektieren
                                        echo " selected=\"selected\" ";
                                      }
                                      echo ">$i</option>\n";
                                    }
                                    ?>
                                  </select> .
                                  <select name="beitrittmonth" size="1" class="small" id="beitrittmonth">
                                    <?php
                                    $aktuellermonat = intval(strftime("%m",$beitritttimestamp));
                                    // Mittels einer For-Schleife von 1 bis 12 ein Auswahlfeld für den Monat erstellen
                                    for ($i=1; $i<=12; $i++)  {
                                      echo "<option value=\"$i\"";
                                      if (($i == $aktuellermonat && !isset($prefill['beitrittmonth'])) || (isset($prefill['beitrittmonth']) && $prefill['beitrittmonth'] == $i)){
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
                                    echo strftime("%Y",$beitritttimestamp);
                                  }?>" name="beitrittyear" id="beitrittyear" maxlength="4" class="xsmall" />
                              </p>
                              <?php
                              } //Ende Rechtelevel 4 oder höher only
                              ?>
                              <p>
                                  <label for="abrechnung">Abrechnung</label>
                                  <select name="abrechnung" size="1" class="medium" id="abrechnung">
                                    <?php
                                    echo "<option value=\"0\"";
                                    if ((isset($prefill['abrechnung']) && $prefill['abrechnung'] == 0) || (!isset($prefill['abrechnung']) && $member['abrechnung'] == 0)){
                                      echo " selected=\"selected\" ";
                                    }
                                    echo ">Bankeinzug</option>";
                                    echo "<option value=\"1\"";
                                    if ((isset($prefill['abrechnung']) && $prefill['abrechnung'] == 1) || (!isset($prefill['abrechnung']) && $member['abrechnung'] == 1)){
                                      echo " selected=\"selected\" ";
                                    }
                                    echo ">Überweisung</option>";
                                    ?>
                                  </select>
                              </p>
                              <div id="kontoverb"<?php if ((isset($prefill['abrechnung']) && $prefill['abrechnung'] == 1) || ((!isset($prefill['abrechnung']) && $member['abrechnung'] == 1))){echo " class=\"nodisplay\"";}?>>
                              <p>
                                  <label for="kontoinhaber">Kontoinhaber</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['kontoinhaber'])){
                                    echo htmlspecialchars($prefill['kontoinhaber']);
                                  }else{
                                    echo htmlspecialchars($member['kontoinhaber']);
                                  }?>" name="kontoinhaber" id="kontoinhaber" title="Name des Kontoinhabers" class="medium" />
                              </p>
                              <p>
                                  <label for="kontonummer">Kontonummer</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['kontonummer'])){
                                    echo htmlspecialchars($prefill['kontonummer']);
                                  }else{
                                    echo htmlspecialchars($member['kontonummer']);
                                  }?>" name="kontonummer" id="kontonummer" title="Kontonummer" class="medium" />
                              </p>
                              <p>
                                  <label for="blz">Bankverbindung</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['blz'])){
                                    echo htmlspecialchars($prefill['blz']);
                                  }else{
                                    echo htmlspecialchars($member['blz']);
                                  }?>" name="blz" id="blz" title="BLZ" class="xsmall" maxlength="12" />
                                  <input type="text" value="<?php
                                  if (isset($prefill['bankname'])){
                                    echo htmlspecialchars($prefill['bankname']);
                                  }else{
                                    echo htmlspecialchars($member['bankname']);
                                  }?>" name="bankname" id="bankname" title="Bankname" class="medium" />
                              </p>
                              </div>
                               <!-- <td class="firstcolumn"></td>
                                <td><?php if ($member['mitgliedschaft'] == 0){
                                  echo "Einzelmitgliedschaft";
                                }else{
                                  echo "<a href=\"fammitglied_show.php?id=".intval($member['mitgliedschaft'])."\" title=\"Informationen zu dieser Familienmitgliedschaft anzeigen\">Familienmitgliedschaft</a>";
                                }?>
                                </td>

                                <td class="firstcolumn">Bankverbindung</td>
                                <td><?php if ($member['abrechnung'] == 0){
                                  echo $member['kontoinhaber']."<br />".$member['kontonummer']."<br />BLZ: ".$member['blz'];
                                }else{
                                  echo "Nicht relevant";
                                }?>-->
                              <p>
                                  <input type="hidden" name="mitglieder_id" value="<?php echo $member['mitglieder_id'];?>" />
                                  <input type="hidden" name="orts_id" value="<?php echo $member['anschrift'];?>" />
                                  <input type="submit" name="savemember" value="Speichern" title="Speichern" class="button medium" />
                              </p>
                          </form>
                      </div>
            		  <div class="rightbox">
                      <?php
                        include("inc/action_leiste_members.inc.php");
                      ?>
                      </div>
            		</div>
                <?php
                }else{
                  echo "<div class=\"message error\"><p>Für diese Aktion haben Sie nicht die erforderlichen Rechte. Bitte beachten Sie, dass Sie Ihre Kinder nur bis zu dessen Alter von 18 Jahren bearbeiten können.<br />
                  Wenden Sie sich an den Systemadministrator, wenn Sie der Meinung sind, diese Funktion zu benötigen.<br /><a href=\"members.php\" title=\"Übersicht aller Mitglieder anzeigen\">Zur Mitgliederverwaltung</a></p></div>";
                }
                 }else{ ?>
                    <h2>Mitglied bearbeiten</h2>
                    <div class="message error"><p>Es wurde kein Mitglied mit dieser ID gefunden!<br /><a href="members.php" title="Übersicht aller Mitglieder anzeigen">Zur Mitgliederverwaltung</a></p></div>

        <?php     }
        }else{ ?>
        <h2>Mitglied bearbeiten</h2>
        <div class="message error"><p>Es wurde keine Mitglieder-ID übergeben!<br /><a href="members.php" title="Übersicht aller Mitglieder anzeigen">Zur Mitgliederverwaltung</a></p></div>

            <?php } ?>
    		<div class="clearit">&nbsp;</div>
        </div>
<?php
// HTML-Fußbereich einbinden
require_once("inc/footer.inc.php");
?>