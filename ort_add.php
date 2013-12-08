<?php
// Datenbankverbindung & Kodierung einbinden
require_once("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once("inc/checkuser.inc.php");

// Rechte Check
if ($user['rights'] < 4){
  // Rechtelevel geringer als 4 = Kein Zugang
  header('Location: norights.php?before='.base64_encode($_SERVER["HTTP_REFERER"]));
  exit();
}

// Titel festlegen
$title = "Ort / Anschrift hinzufügen";

// JavaScript einfügen
$appendjs = "$(\"#typ\").change(function(){
if ($(this).val() == 2){
    $(\"#namep\").slideDown();
}else{
    $(\"#namep\").slideUp();
}
});";

// HTML-Kopf einbinden
require_once("inc/head.inc.php");
?>
        <div id="content">
        		<div class="boxsystem33">
        		  <div class="leftbox">
                      <h2>Ort / Anschrift hinzufügen</h2>
                      <p>Über dieses Formular kann eine neue Anschrift bzw. ein neuer Ort in der Datenbank erstellt werden.</p>
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
                              echo "<div class=\"message error\"><p>Es ist bereits ein Ort mit diesen Daten vorhanden.</p></div>";
                            }else{
                              echo "<div class=\"message error\"><p>Beim Versuch, den neuen Ort anzulegen, ist leider ein Fehler aufgetreten. Bitte versuchen Sie es erneut oder wenden sich an den Systemadministrator.</p></div>";
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
                      <form id="addform" class="formular" method="post" action="ort_process.php" accept-charset="utf-8">
                          <p id="namep"<?php
                              if ((isset($prefill['typ']) && $prefill['typ'] == 1) || !isset($prefill['typ'])){
                                echo " class=\"nodisplay\"";
                              }
                              ?>>
                              <label for="name">Bezeichnung / Name</label>
                              <input type="text" value="<?php if (isset($prefill['name'])){echo htmlspecialchars($prefill['name']);}?>" name="name" title="Name/Bezeichnung" id="name" class="medium" />
                          </p>
                          <p>
                              <label for="strasse">Straße</label>
                              <input type="text" value="<?php if (isset($prefill['strasse'])){echo htmlspecialchars($prefill['strasse']);}?>" name="strasse" id="strasse" title="Straße" class="medium" /> <input type="text" value="<?php if (isset($prefill['hausnummer'])){echo htmlspecialchars($prefill['hausnummer']);}?>" name="hausnummer" title="Hausnummer" id="hausnummer" class="xsmall" />
                          </p>
                          <p>
                              <label for="ort">Ort</label>
                              <input type="text" value="<?php if (isset($prefill['plz'])){echo htmlspecialchars($prefill['plz']);}?>" name="plz" id="plz" title="PLZ" class="xsmall" maxlength="5" /> <input type="text" value="<?php if (isset($prefill['ort'])){echo htmlspecialchars($prefill['ort']);}?>" name="ort" id="ort" title="Ortsname" class="medium" />
                          </p>

                          <p>
                              <label for="telnr">Telefonnummer</label>
                              <input type="text" value="<?php if (isset($prefill['telvorwahl'])){echo htmlspecialchars($prefill['telvorwahl']);}?>" name="telvorwahl" id="telvorwahl" title="Vorwahl" class="xsmall" maxlength="5" /> / <input type="text" title="Telefonnummer" value="<?php if (isset($prefill['telnr'])){echo htmlspecialchars($prefill['telnr']);}?>" name="telnr" id="telnr" class="medium" />
                          </p>
                          <p>
                              <label for="typ">Art des Ortes</label>
                              <select name="typ" size="1" class="medium" id="typ">
                                <option value="1"<?php if (isset($prefill['typ']) && $prefill['typ'] == "1"){echo " selected=\"selected\"";}?>>Wohnort</option>
                                <option value="2"<?php if (isset($prefill['typ']) && $prefill['typ'] == "2"){echo " selected=\"selected\"";}?>>Veranstaltungsort</option>
                              </select>
                          </p>
                          <p>
                              <input type="submit" name="addort" id="addort" value="Hinzufügen" title="Hinzufügen" class="button medium" />
                          </p>
                      </form>
                  </div>
        		  <div class="rightbox">
                      <h2>Aktionen</h2>
                      <ul class="nolistimg">
                        <li><img src="images/list.png" alt="" title="Zur Übersicht" /> <a href="orte.php" title="Zurück zur Übersicht aller Orte">Ortsverwaltung</a></li>
                      </ul>
        		  </div>
        		</div>
    		<div class="clearit">&nbsp;</div>
        </div>
<?php
// HTML-Fußbereich einbinden
require_once("inc/footer.inc.php");
?>