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
$title = "Nachricht / Meldung löschen";

// HTML-Kopf einbinden
require_once("inc/head.inc.php");
?>
        <div id="content">
        <?php
        if (isset($_GET['id'])){
            $sql = "SELECT * FROM `news` LEFT JOIN `mitglieder` ON `news`.`author` = `mitglieder`.`mitglieder_id` WHERE `news_id` = ".intval($_GET['id'])." AND `news`.`status` = '1' ";
            $nachrichten_result = mysql_query($sql);
            //echo mysql_error();
            if (mysql_num_rows($nachrichten_result) == 1){
                $nachricht = mysql_fetch_assoc($nachrichten_result);
                // Hat der Nutzer die erforderlichen Rechte?
                if ($user['rights'] >= 4 || $nachricht['author'] == $user['mitglieder_id']){
                ?>
            		<div class="boxsystem33">
            		  <div class="leftbox">
                          <h2>Nachricht / Meldung löschen</h2>
                          <p>Entfernen Sie eine Nachricht bzw. Meldung aus dem System.</p>
                          <?php
                          if(isset($_GET['removal']) && $_GET['removal'] == "fail"){
                            echo "<div class=\"message error\"><p>Beim Entfernen der Nachricht ist ein Fehler aufgetreten.</p></div>";
                          }elseif(isset($_GET['deletion']) && $_GET['deletion'] == "fail"){
                            echo "<div class=\"message error\"><p>Beim Löschen der Nachricht aus der Datenbank ist ein Fehler aufgetreten.</p></div>";
                          }
                          ?>
                          <form id="deleteform" class="formular" method="post" action="news_process.php" accept-charset="utf-8">
                              <p>
                                  Möchten Sie folgende Nachricht wirklich löschen?
                              </p>
                              <p class="textcenter">
                                  <?php
                                  if (empty($nachricht['title'])){
                                    echo "<i>Kein Titel</i>";
                                  }else{
                                    echo "<i>".htmlspecialchars($nachricht['title'])."</i>";
                                  }
                                  echo "<br />Erstellt am ".strftime("%d. %B %Y um %H:%M Uhr",$nachricht['lastchange'])." von ";
                                  if (empty($nachricht['vorname']) && empty($nachricht['nachname'])){
                                    echo "<i>Unbekannt</i>";
                                  }else{
                                    echo htmlspecialchars($nachricht['vorname']." ".$nachricht['nachname']);
                                  }
                                  ?>
                              </p>
                              <p>
                                  <label for="sure">Nachricht löschen</label>
                                  <select name="sure" size="1" class="medium" id="sure">
                                    <option value="1">Nein</option>
                                    <option value="2">Ja</option>
                                  </select>
                              </p>
                              <?php
                              if ($user['rights'] >= 5){
                                // Ist der aktuelle Nutzer ein Administrator
                              ?>
                                  <p>
                                      <label for="dbdelete">Aus der DB löschen</label>
                                      <select name="dbdelete" size="1" class="medium" id="dbdelete">
                                        <option value="1">Nein</option>
                                        <option value="2">Ja</option>
                                      </select>
                                  </p>
                              <?php
                              }
                              ?>
                              <p>
                                  <input type="hidden" name="news_id" value="<?php echo $nachricht['news_id'];?>" />
                                  <input type="submit" name="deletenews" id="deletenews" value="Löschen" title="Löschen" class="button medium" />
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