<?php
// Datenbankverbindung & Kodierung einbinden
require_once("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once("inc/checkuser.inc.php");

// Titel festlegen
$title = "Abteilung löschen";

// HTML-Kopf einbinden
require_once("inc/head.inc.php");
?>
        <div id="content">
        <?php
        if (isset($_GET['id'])){
            $sql = "SELECT * FROM `abteilungen` WHERE `abteilungs_id` = ".intval($_GET['id'])." AND `status` = '1' ";
            $ort_result = mysql_query($sql);
            //echo mysql_error();
            if (mysql_num_rows($ort_result) == 1){
                $abteilung = mysql_fetch_assoc($ort_result);
                ?>
            		<div class="boxsystem33">
            		  <div class="leftbox">
                          <h2>Abteilung löschen</h2>
                          <p>Entfernen Sie über dieses Formular eine Abteilung der Vereins.</p>
                          <?php
                          if (isset($_GET['removal']) && $_GET['removal'] == "fail"){
                            echo "<div class=\"message error\"><p>Beim Entfernen der Abteilung ist ein Fehler aufgetreten.</p></div>";
                          }elseif (isset($_GET['deletion']) && $_GET['deletion'] == "fail"){
                            echo "<div class=\"message error\"><p>Beim Löschen der Abteilung aus der Datenbank ist ein Fehler aufgetreten.</p></div>";
                          }elseif (isset($_GET['memberdelete']) && $_GET['memberdelete'] == "fail"){
                            echo "<div class=\"message error\"><p>Beim Löschen der Zugehörigkeiten der Mitglieder zu dieser Abteilung ist ein Fehler aufgetreten.</p></div>";
                          }
                          ?>
                          <form id="deleteform" class="formular" method="post" action="abteilung_process.php" accept-charset="utf-8">
                              <p>
                                  Möchten Sie die Abteilung "<?php echo htmlspecialchars($abteilung['name']);?>" wirklich löschen?<br />
                                  Sämtliche Zugehörigkeiten zu dieser Abteilung werden ebenfalls gelöscht!
                              </p>
                              <p>
                                  <label for="sure">Abteilung löschen</label>
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
                                  <input type="hidden" name="abteilungs_id" value="<?php echo $abteilung['abteilungs_id'];?>" />
                                  <input type="submit" name="deleteabteilung" id="deleteabteilung" value="Löschen" title="Löschen" class="button medium" />
                              </p>
                          </form>
                      </div>
            		  <div class="rightbox">
                        <?php include("inc/action_leiste_abteilungen.inc.php"); ?>
            		  </div>
            		</div>
            <?php }else{ ?>
                    <h2>Abteilung löschen</h2>
                    <div class="error"><p>Es wurde keine Abteilung mit dieser ID gefunden!<br /><a href="abteilungen.php" title="Übersicht aller Abteilungen anzeigen">Zur Abteilungsverwaltung</a></p></div>

        <?php     }
        }else{ ?>
        <h2>Abteilung löschen</h2>
        <div class="error"><p>Es wurde keine Abteilungs-ID übergeben!<br /><a href="abteilungen.php" title="Übersicht aller Abteilungen anzeigen">Zur Abteilungsverwaltung</a></p></div>

            <?php } ?>
    		<div class="clearit">&nbsp;</div>
        </div>
<?php
// HTML-Fußbereich einbinden
require_once("inc/footer.inc.php");
?>