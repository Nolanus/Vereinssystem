<?php
// Datenbankverbindung & Kodierung einbinden
require_once("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once("inc/checkuser.inc.php");

// Rechte Check
if ($user['rights'] < 2){
  // Rechtelevel geringer als 4 = Kein Zugang
  header('Location: norights.php?before='.base64_encode($_SERVER["HTTP_REFERER"]));
  exit();
}

// Titel festlegen
$title = "Nachricht / Meldung hinzufügen";

// JavaScript einfügen
$appendjs = "";

// HTML-Kopf einbinden
require_once("inc/head.inc.php");
?>
        <div id="content">
        		<div class="boxsystem33">
        		  <div class="leftbox">
                      <h2>Nachricht / Meldung hinzufügen</h2>
                      <p>Über dieses Formular kann eine neue Nachricht bzw. Meldung in der Datenbank erstellt werden.</p>
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
                              echo "<div class=\"message error\"><p>Es ist bereits ein Nachricht mit diesen Daten vorhanden.</p></div>";
                            }else{
                              echo "<div class=\"message error\"><p>Beim Versuch, die neue Nachricht anzulegen, ist leider ein Fehler aufgetreten. Bitte versuchen Sie es erneut oder wenden sich an den Systemadministrator.</p></div>";
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
                      <form id="addform" class="formular" method="post" action="news_process.php" accept-charset="utf-8">
                          <p>
                              <label for="title">Titel</label>
                              <input type="text" value="<?php if (isset($prefill['title'])){echo htmlspecialchars($prefill['title']);}?>" name="title" title="Titel" id="title" class="xlarge" />
                          </p>
                          <p>
                              <label for="inhalt">Inhalt</label>
                                  <textarea name="content" id="inhalt" class="xlarge" rows="15" cols="20"><?php
                                  if (isset($prefill['content'])){
                                    echo htmlspecialchars($prefill['content']);
                                  }
                                  ?></textarea>
                          </p>
                          <p>
                              <label for="minright">Sichtbar für</label>
                              <select name="minright" size="1" class="medium" id="minright">
                                <option value="1"<?php if (isset($prefill['minright']) && $prefill['minright'] == "1"){echo " selected=\"selected\"";}?>>Normale Nutzer + höher</option>
                                <option value="2"<?php if (isset($prefill['minright']) && $prefill['minright'] == "2"){echo " selected=\"selected\"";}?>>Moderatoren + höher</option>
                                <option value="3"<?php if (isset($prefill['minright']) && $prefill['minright'] == "3"){echo " selected=\"selected\"";}?>>Abteilungsleiteren + höher</option>
                                <option value="4"<?php if (isset($prefill['minright']) && $prefill['minright'] == "4"){echo " selected=\"selected\"";}?>>Vereinsvorstände + höher</option>
                                <option value="5"<?php if (isset($prefill['minright']) && $prefill['minright'] == "5"){echo " selected=\"selected\"";}?>>Administratoren</option>
                              </select>
                          </p>
                          <p>
                              <input type="submit" name="addnews" id="addnews" value="Hinzufügen" title="Hinzufügen" class="button medium" />
                          </p>
                      </form>
                  </div>
        		  <div class="rightbox">
                      <h2>Aktionen</h2>
                      <ul class="nolistimg">
                        <li><img src="images/list.png" alt="" title="Zur Übersicht" /> <a href="news.php" title="Zurück zur Übersicht aller Nachrichten">Nachrichten / Meldungen</a></li>
                      </ul>
        		  </div>
        		</div>
    		<div class="clearit">&nbsp;</div>
        </div>
<?php
// HTML-Fußbereich einbinden
require_once("inc/footer.inc.php");
?>