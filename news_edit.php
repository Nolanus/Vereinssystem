<?php
// Datenbankverbindung & Kodierung einbinden
require_once("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once("inc/checkuser.inc.php");

// Rechte Check
if ($user['rights'] < 2){
  // Rechtelevel geringer als 2 = Kein Zugang
  if (isset($_SERVER["HTTP_REFERER"])){
    $referer = "?before=".base64_encode($_SERVER["HTTP_REFERER"]);
  }else{
    $referer = "";
  }
  header("Location: norights.php$referer");
  exit();
}

// Titel festlegen
$title = "Nachricht / Meldung bearbeiten";

// JavaScript einfügen
$appendjs = "";

// HTML-Kopf einbinden
require_once("inc/head.inc.php");
?>
        <div id="content">
        <?php
        if (isset($_GET['id'])){
            $sql = "SELECT * FROM `news` WHERE `news_id` = ".intval($_GET['id'])." AND `status` = '1' ";
            $news_result = mysql_query($sql);
            //echo mysql_error();
            if (mysql_num_rows($news_result) == 1){
                $nachricht = mysql_fetch_assoc($news_result);
                // Hat der Nutzer die erforderlichen Rechte?
                if ($user['rights'] >= 4 || $nachricht['author'] == $user['mitglieder_id']){
                ?>
        		<div class="boxsystem33">
        		  <div class="leftbox">
                      <h2>Nachricht / Meldung bearbeiten</h2>
                      <p>Bearbeiten Sie die Eigenschaften einer Nachricht bzw. Meldung.</p>
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
                      <form id="editform" class="formular" method="post" action="news_process.php" accept-charset="utf-8">
                          <p>
                              <label for="title">Titel</label>
                              <input type="text" value="<?php
                                  if (isset($prefill['title'])){
                                    echo htmlspecialchars($prefill['title']);
                                  }else{
                                    echo htmlspecialchars($nachricht['title']);
                                  }?>" name="title" title="Titel" id="title" class="xlarge" />
                          </p>
                          <p>
                              <label for="inhalt">Inhalt</label>
                                  <textarea name="content" id="inhalt" class="xlarge" rows="15" cols="20"><?php
                                  if (isset($prefill['content'])){
                                    echo $prefill['content'];
                                  }else{
                                    echo $nachricht['content'];
                                  }?></textarea>
                          </p>
                          <p>
                              <label for="minright">Sichtbar für</label>
                              <select name="minright" size="1" class="medium" id="minright">
                                <option value="1"<?php if ((isset($prefill['minright']) && $prefill['minright'] == 1)|| (!isset($prefill['minright']) && $nachricht['minright'] == 1)){echo " selected=\"selected\"";}?>>Normale Nutzer + höher</option>
                                <option value="2"<?php if ((isset($prefill['minright']) && $prefill['minright'] == 2)|| (!isset($prefill['minright']) && $nachricht['minright'] == 2)){echo " selected=\"selected\"";}?>>Moderatoren + höher</option>
                                <option value="3"<?php if ((isset($prefill['minright']) && $prefill['minright'] == 3)|| (!isset($prefill['minright']) && $nachricht['minright'] == 3)){echo " selected=\"selected\"";}?>>Abteilungsleiter + höher</option>
                                <option value="4"<?php if ((isset($prefill['minright']) && $prefill['minright'] == 4)|| (!isset($prefill['minright']) && $nachricht['minright'] == 4)){echo " selected=\"selected\"";}?>>Vereinsvorstände + höher</option>
                                <option value="5"<?php if ((isset($prefill['minright']) && $prefill['minright'] == 5)|| (!isset($prefill['minright']) && $nachricht['minright'] == 5)){echo " selected=\"selected\"";}?>>Administratoren</option>
                              </select>
                          </p>
                          <?php
                          if ($user['rights'] >= 5){
                            // Administratoren die Möglichkeit geben, eine Nachricht ohne konsequenz auf "lastchange" zu bearbeiten
                          ?>
                          <p>
                              <label for="silentchange">Stille Änderung</label>
                              <select name="silentchange" size="1" class="medium" id="silentchange">
                                <option value="0"<?php if ((isset($prefill['silentchange']) && $prefill['silentchange'] == 0)){echo " selected=\"selected\"";}?>>Nein</option>
                                <option value="1"<?php if ((isset($prefill['silentchange']) && $prefill['silentchange'] == 1)){echo " selected=\"selected\"";}?>>Ja</option>
                              </select>
                          </p>
                          <?php
                          }
                          ?>
                          <p>
                              <input type="hidden" name="news_id" value="<?php echo $nachricht['news_id'];?>" />
                              <input type="submit" name="savenews" id="savenews" value="Speichern" title="Speichern" class="button medium" />
                          </p>
                      </form>
                  </div>
        		  <div class="rightbox">
                    <?php include("inc/action_leiste_news.inc.php"); ?>
        		  </div>
        		</div>
            <?php
                }else{
                  echo "<div class=\"message error\"><p>Für diese Aktion haben Sie nicht die erforderlichen Rechte. Bitte beachten Sie, dass Sie nur Ihre eigenen Nachrichten bearbeiten können.<br />
                  Wenden Sie sich an den Systemadministrator, wenn Sie der Meinung sind, diese Funktion zu benötigen.<br /><a href=\"news.php\" title=\"Übersicht aller Nachrichten anzeigen\">Zur Nachrichtenübersicht</a></p></div>";
                }
            }else{ ?>
                    <h2>Nachricht / Meldung bearbeiten</h2>
                    <div class="message error"><p>Es wurde keine Nachricht mit dieser ID gefunden!<br /><a href="news.php" title="Übersicht aller Nachrichten anzeigen">Zur Nachrichtenübersicht</a></p></div>

        <?php     }
        }else{ ?>
        <h2>Nachricht / Meldung bearbeiten</h2>
        <div class="message error"><p>Es wurde keine Nachrichten-ID übergeben!<br /><a href="news.php" title="Übersicht aller Orte anzeigen">Zur Nachrichtenübersicht</a></p></div>

            <?php } ?>
    		<div class="clearit">&nbsp;</div>
        </div>
<?php
// HTML-Fußbereich einbinden
require_once("inc/footer.inc.php");
?>