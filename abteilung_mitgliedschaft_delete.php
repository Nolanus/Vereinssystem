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
$title = "Abteilungsmitgliedschaft entfernen/beenden";

// JavaScript einfügen
$appendjs = "";

// HTML-Kopf einbinden
require_once("inc/head.inc.php");
?>
        <div id="content">
 <?php
        if (isset($_GET['id']) && isset($_GET['who'])){
            $sql = "SELECT * FROM `abteilungszugehoerigkeit`
                    LEFT JOIN `mitglieder` ON `abteilungszugehoerigkeit`.`mitglied` = `mitglieder`.`mitglieder_id`
                    LEFT JOIN `abteilungen` ON `abteilungszugehoerigkeit`.`abteilung` = `abteilungen`.`abteilungs_id`
            WHERE `abteilungszugehoerigkeit`.`abteilung` = ".intval($_GET['id'])." AND `abteilungszugehoerigkeit`.`mitglied` = ".intval($_GET['who'])." AND `abteilungen`.`status` = '1' AND `mitglieder`.`status` = 1";
            $abteilungs_result = mysql_query($sql);
            //echo mysql_error();
            if (mysql_num_rows($abteilungs_result) == 1){
                $abteilung = mysql_fetch_assoc($abteilungs_result);
                if ($abteilung['abteilungsleiter'] == $user['mitglieder_id'] || $user['rights'] >= 4){
                ?>
            		<div class="boxsystem33">
            		  <div class="leftbox">
                          <h2>Abteilungsmitgliedschaft entfernen/beenden</h2>
                          <?php
                          if (isset($_GET['deletion']) && $_GET['deletion'] == "fail"){
                              echo "<div class=\"message error\"><p>Beim Versuch, die Abteilungsmitgliedschaft zu löschen, ist leider ein Fehler aufgetreten. Bitte versuchen Sie es erneut oder wenden sich an den Systemadministrator.</p></div>";
                          }
                          ?>
                          <p>Über dieses Formular kann die die Zugehörigkeit eines Mitglieds bei einer Abteilung beendet werden.</p>
                          <form id="deleteform" class="formular" method="post" action="abteilung_process.php" accept-charset="utf-8">
                              <p>
                                  Möchten Sie folgenden Abteilungsmitgliedschaft wirklich entfernen?
                              </p>
                              <p class="textcenter">
                                  <?php
                                  echo "Mitglied: <i>".htmlspecialchars($abteilung['vorname']." ".$abteilung['nachname'])."</i><br />";
                                  echo "Abteilung: <i>".htmlspecialchars($abteilung['name'])."</i>";
                                  ?>
                              </p>
                              <p>
                                  <label for="sure">Abteilungsmitgliedschaft entfernen/beenden</label>
                                  <select name="sure" size="1" class="medium" id="sure">
                                    <option value="1">Nein</option>
                                    <option value="2">Ja</option>
                                  </select>
                              </p>
                              <p>
                                  <input type="hidden" name="abteilungs_id" value="<?php echo $abteilung['abteilungs_id'];?>" />
                                  <input type="hidden" name="mitglieder_id" value="<?php echo $abteilung['mitglieder_id'];?>" />
                                  <input type="submit" name="deleteabteilungsmember" id="deleteabteilungsmember" value="Entfernen/Beenden" title="Entfernen/Beenden" class="button medium" />
                              </p>
                          </form>
                          <p><img src="images/arrow_left.png" alt="" class="imageinline" /> <a href="abteilung_mitglieder.php?id=<?php echo $abteilung['abteilungs_id'];?>" title="Zurück zur Abteilungsmitgliedschaftsverwaltung">Zurück zur Übersicht</a></p>
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
                    <h2>Abteilungsmitgliedschaft entfernen/beenden</h2>
                    <div class="message error"><p>Es wurde keine Zugehörigkeit entsprechend der Angaben gefunden!<br /><a href="abteilung_mitglieder.php?id=<?php echo intval($_GET['id']);?>" title="Übersicht aller Abteilungsmitglieder anzeigen">Zur Abteilungsmitgliederverwaltung</a></p></div>

        <?php    }
        }else{ ?>
            <h2>Abteilungsmitgliedschaft entfernen/beenden</h2>
            <div class="message error"><p>Es wurde keine Abteilungs-ID und/oder Mitglieder-ID übergeben!<br /><a href="abteilungen.php" title="Übersicht aller Abteilungen anzeigen">Zur Abteilungsverwaltung</a></p></div>
  <?php } ?>
    		<div class="clearit">&nbsp;</div>
        </div>
<?php
// HTML-Fußbereich einbinden
require_once("inc/footer.inc.php");
?>