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
$title = "Ort / Anschrift bearbeiten";

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
        <?php
        if (isset($_GET['id'])){
            $sql = "SELECT * FROM `orte` WHERE `orts_id` = ".intval($_GET['id'])." AND `status` = '1' ";
            $ort_result = mysql_query($sql);
            //echo mysql_error();
            if (mysql_num_rows($ort_result) == 1){
                $ort = mysql_fetch_assoc($ort_result);
                ?>
            		<div class="boxsystem33">
            		  <div class="leftbox">
                          <h2>Ort / Anschrift bearbeiten</h2>
                          <p>Bearbeiten Sie die Eigenschaften eines Ortes. Beachten Sie, dass diese Änderungen jedes Mitglied und jede Veranstaltung betreffen, die an diesem Ort stattfindet bzw. die an diesem Ort wohnen.</p>
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
                                echo "<div class=\"message error\"><p>Es ist bereits ein Ort mit diesen Daten vorhanden. Bitte verwenden Sie diesen und löschen gegebenenfalls den überflüssigen Eintrag.</p></div>";
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
                          <form id="editform" class="formular" method="post" action="ort_process.php" accept-charset="utf-8">
                              <p id="namep"<?php if ((isset($prefill['typ']) && $prefill['typ'] == 1)|| (!isset($prefill['typ']) && $ort['typ'] == 1)){
                                echo " class=\"nodisplay\"";
                              }
                              ?>>
                                  <label for="name">Bezeichnung / Name</label>
                                  <input type="text" value="<?php
                                  if (isset($prefill['name'])){
                                    echo htmlspecialchars($prefill['name']);
                                  }else{
                                    echo htmlspecialchars($ort['name']);
                                  }?>" name="name" id="name" class="medium" />
                              </p>
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
                              <p>
                                  <label for="typ">Art des Ortes</label>
                                  <select name="typ" size="1" class="medium" id="typ">
                                    <?php
                                    echo "<option value=\"1\"";
                                    if ((isset($prefill['typ']) && $prefill['typ'] == 1)|| (!isset($prefill['typ']) && $ort['typ'] == 1)){
                                      echo " selected=\"selected\" ";
                                    }
                                    echo ">Wohnort</option>";
                                    echo "<option value=\"2\"";
                                    if ((isset($prefill['typ']) && $prefill['typ'] == 2) || (!isset($prefill['typ']) && $ort['typ'] == 2)){
                                      echo " selected=\"selected\" ";
                                    }
                                    echo ">Veranstaltungsort</option>";
                                    ?>
                                  </select>
                              </p>
                              <p>
                                  <input type="hidden" name="orts_id" value="<?php echo $ort['orts_id'];?>" />
                                  <input type="submit" name="saveort" id="saveort" value="Speichern" title="Speichern" class="button medium" />
                              </p>
                          </form>
                      </div>
            		  <div class="rightbox">
                        <?php include("inc/action_leiste_orte.inc.php"); ?>
            		  </div>
            		</div>
            <?php }else{ ?>
                    <h2>Ort bearbeiten</h2>
                    <div class="message error"><p>Es wurde kein Ort mit dieser ID gefunden!<br /><a href="orte.php" title="Übersicht aller Orte anzeigen">Zur Orts- und Anschriftsverwaltung</a></p></div>

        <?php     }
        }else{ ?>
        <h2>Ort bearbeiten</h2>
        <div class="message error"><p>Es wurde keine Orts-ID übergeben!<br /><a href="orte.php" title="Übersicht aller Orte anzeigen">Zur Orts- und Anschriftsverwaltung</a></p></div>

            <?php } ?>
    		<div class="clearit">&nbsp;</div>
        </div>
<?php
// HTML-Fußbereich einbinden
require_once("inc/footer.inc.php");
?>