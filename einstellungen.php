<?php
// Datenbankverbindung & Kodierung einbinden
require_once("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once("inc/checkuser.inc.php");

// Rechte Check
if ($user['rights'] < 5){
  // Rechtelevel geringer als 5 = Kein Zugang
  if (isset($_SERVER["HTTP_REFERER"])){
    $referer = "?before=".base64_encode($_SERVER["HTTP_REFERER"]);
  }else{
    $referer = "";
  }
  header("Location: norights.php$referer");
  exit();
}

// Titel festlegen
$title = "Systemeinstellungen bearbeiten";

// JavaScript einfügen
$appendjs = "$(\"#cakeworkcount, #annualwork\").keydown(function(){
    $(\"#workcountwarning\").slideDown();
});";

// HTML-Kopf einbinden
require_once("inc/head.inc.php");
?>
        <div id="content">
        <?php
                ?>
            		<div class="boxsystem33">
            		  <div class="leftbox">
                          <h2>Systemeinstellungen bearbeiten</h2>
                          <p>Über dieses Formular können Sie die Systemeinstellungen bearbeiten.</p>
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
                          // Daten der Vereinsanschrift auslesen
                          $sql = "SELECT * FROM `orte` WHERE `orte`.`orts_id` = '".$settings['vereinsanschrift']."'";
                          $ort_result = mysql_query($sql);
                          $ort = mysql_fetch_assoc($ort_result);
                          ?>
                          <form id="editform" class="formular" method="post" action="einstellungen_process.php" accept-charset="utf-8">
                              <p>
                                  <label for="vereinsname">Vereinsname</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['vereinsname'])){
                                    echo htmlspecialchars($prefill['vereinsname']);
                                  }else{
                                    echo htmlspecialchars($settings['vereinsname']);
                                  }?>" name="vereinsname" id="vereinsname" class="medium" />
                              </p>
                              <p>
                                  <label for="vereinsname_lang">Offizieller Vereinsname</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['vereinsname_lang'])){
                                    echo htmlspecialchars($prefill['vereinsname_lang']);
                                  }else{
                                    echo htmlspecialchars($settings['vereinsname_lang']);
                                  }?>" name="vereinsname_lang" id="vereinsname_lang" class="xlarge" />
                              </p>
                              <p>
                                  <label for="beitrag_kindereuro">Monatl. Beitrag Kind</label>
                                  <?php
                                  $nachkomma = $settings['beitrag_kinder'] % 100;
                                  $vorkomma = ($settings['beitrag_kinder'] - $nachkomma)/100;
                                  ?>
                                  <input type="text" value="<?php
                                  if (isset($prefill['beitrag_kindereuro'])){
                                    echo ($prefill['beitrag_kindereuro']);
                                  }else{
                                    echo ($vorkomma);
                                  }?>" name="beitrag_kindereuro" id="beitrag_kindereuro" title="Euro" class="xxsmall" /> .
                                  <input type="text" value="<?php
                                  if (isset($prefill['beitrag_kindercent'])){
                                    echo ($prefill['beitrag_kindercent']);
                                  }else{
                                    echo str_pad($nachkomma, 2 ,'0', STR_PAD_LEFT);
                                  }?>" name="beitrag_kindercent" title="Cent" id="beitrag_kindercent" class="xxsmall" maxlength="2" /> €
                              </p>
                              <p>
                                  <label for="beitrag_erwachsenereuro">Monatl. Beitrag Erwachsener</label>
                                  <?php
                                  $nachkomma = $settings['beitrag_erwachsener'] % 100;
                                  $vorkomma = ($settings['beitrag_erwachsener'] - $nachkomma)/100;
                                  ?>
                                  <input type="text" value="<?php
                                  if (isset($prefill['beitrag_erwachsenereuro'])){
                                    echo ($prefill['beitrag_erwachsenereuro']);
                                  }else{
                                    echo ($vorkomma);
                                  }?>" name="beitrag_erwachsenereuro" id="beitrag_erwachsenereuro" title="Euro" class="xxsmall" /> .
                                  <input type="text" value="<?php
                                  if (isset($prefill['beitrag_erwachsenercent'])){
                                    echo ($prefill['beitrag_erwachsenercent']);
                                  }else{
                                    echo str_pad($nachkomma, 2 ,'0', STR_PAD_LEFT);
                                  }?>" name="beitrag_erwachsenercent" title="Cent" id="beitrag_erwachsenercent" class="xxsmall" maxlength="2" /> €
                              </p>
                              <p>
                                  <label for="webmastermail">E-Mail Webmaster</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['webmastermail'])){
                                    echo htmlspecialchars($prefill['webmastermail']);
                                  }else{
                                    echo htmlspecialchars($settings['webmastermail']);
                                  }?>" name="webmastermail" title="E-Mail Adresse des Webmasters" id="webmastermail" class="medium" />
                              </p>
                              <p>
                                  <label for="leitermail">E-Mail Leiter</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['leitermail'])){
                                    echo htmlspecialchars($prefill['leitermail']);
                                  }else{
                                    echo htmlspecialchars($settings['leitermail']);
                                  }?>" name="leitermail" title="E-Mail Adresse des Vereinsleiters" id="leitermail" class="medium" />
                              </p>
                              <p>
                                  <label for="usecaptchalogin">Captcha beim Login</label>
                                  <select name="usecaptchalogin" size="1" class="medium" id="usecaptchalogin">
                                    <?php
                                    echo "<option value=\"1\"";
                                    if ((isset($prefill['usecaptchalogin']) && $prefill['usecaptchalogin'] == 1)|| (!isset($prefill['usecaptchalogin']) && $settings['usecaptchalogin'] == 1)){
                                      echo " selected=\"selected\" ";
                                    }
                                    echo ">Ja</option>";
                                    echo "<option value=\"0\"";
                                    if ((isset($prefill['usecaptchalogin']) && $prefill['usecaptchalogin'] == 0) || (!isset($prefill['usecaptchalogin']) && $settings['usecaptchalogin'] == 0)){
                                      echo " selected=\"selected\" ";
                                    }
                                    echo ">Nein</option>";
                                    ?>
                                  </select>
                              </p>
                              <h2>Anschrift Geschäftsstelle</h2>
                              <p>
                                  <label for="strasse">Straße</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['strasse'])){
                                    echo htmlspecialchars($prefill['strasse']);
                                  }else{
                                    echo htmlspecialchars($ort['strasse']);
                                  }?>" name="strasse" id="strasse" title="Straße" class="medium" />
                                  <input type="text" value="<?php
                                  if (isset($prefill['hausnummer'])){
                                    echo htmlspecialchars($prefill['hausnummer']);
                                  }else{
                                    echo htmlspecialchars($ort['hausnummer']);
                                  }?>" title="Hausnummer" name="hausnummer" id="hausnummer" class="xsmall" />
                              </p>
                              <p>
                                  <label for="ort">Ort</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['plz'])){
                                    echo ($prefill['plz']);
                                  }else{
                                    echo ($ort['plz']);
                                  }?>" name="plz" id="plz" title="PLZ" class="xsmall" maxlength="5" />
                                  <input type="text" value="<?php
                                  if (isset($prefill['ort'])){
                                    echo htmlspecialchars($prefill['ort']);
                                  }else{
                                    echo htmlspecialchars($ort['ort']);
                                  }?>" name="ort" title="Ortsname" id="ort" class="medium" />
                              </p>
                              <p>
                                  <label for="telnr">Telefonnummer</label>
                                  <?php
                                  // Gespeicherte Telefonnummer zerlegen, um die Teile in den entsprechenden Input-Feldern anzeigen zu können
                                  if (!empty($ort['telefon'])){
                                    list($vorwahl,$telnr) = explode("/",$ort['telefon']);
                                  }
                                  ?>
                                  <input type="text" value="<?php
                                  if (isset($prefill['telvorwahl'])){
                                    echo ($prefill['telvorwahl']);
                                  }elseif(isset($vorwahl)){
                                    echo ($vorwahl);
                                  }?>" name="telvorwahl" id="telvorwahl" title="Vorwahl" class="xsmall" maxlength="5" /> /
                                  <input type="text" value="<?php
                                  if (isset($prefill['telnr'])){
                                    echo ($prefill['telnr']);
                                  }elseif(isset($telnr)){
                                    echo ($telnr);
                                  }?>" name="telnr" title="Telefonnummer" id="telnr" class="medium" />
                              </p>
                              <h2>Mitgliedertätigkeiten</h2>
                              <div id="workcountwarning" class="message warning nodisplay"><p>Beachten Sie, dass eine Änderung an diesen Werten auch vergangene Berechnungen beeinflusst!</p></div>
                              <p>
                                  <label for="annualwork">Anzahl jährliche Dienste</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['annualwork'])){
                                    echo htmlspecialchars($prefill['annualwork']);
                                  }else{
                                    echo intval($settings['annualwork']);
                                  }?>" name="annualwork" title="Jährlich zu leistende Dienste" id="annualwork" class="small" />
                              </p>
                              <p>
                                  <label for="cakeworkcount">1 Standdienst entspricht</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['cakeworkcount'])){
                                    echo htmlspecialchars($prefill['cakeworkcount']);
                                  }else{
                                    echo intval($settings['cakeworkcount']);
                                  }?>" name="cakeworkcount" title="Jährlich zu leistende Dienste" id="cakeworkcount" class="small" /> Kuchen
                              </p>
                              <p>
                                  <input type="submit" name="savesettings" id="savesettings" value="Speichern" title="Speichern" class="button medium" />
                              </p>
                          </form>
                      </div>
            		  <div class="rightbox">
                       <h2>Systemstatus</h2>
                       <p>Letzter Cronjob-Durchlauf:<br /><?php echo strftime("%d. %B %Y, %H:%M",$settings['lastcronrun']);?></p>
                       <p>Letzte Cronjob-Ausgabe:<br /><i><?php echo ($settings['lastcronoutput']);?></i></p>
                       <p>Letzte Diensteabrechnung:<br /><i><?php echo strftime("%d. %B %Y, %H:%M",$settings['last_diensteabrechnung']);?></i></p>
            		  </div>
            		</div>
    		<div class="clearit">&nbsp;</div>
        </div>
<?php
// HTML-Fußbereich einbinden
require_once("inc/footer.inc.php");
?>