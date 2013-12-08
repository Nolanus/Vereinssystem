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
$title = "Abteilungsmitgliedschaft anzeigen";

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
                          <h2>Abteilungsmitgliedschaft anzeigen</h2>
                          <?php
                          if (isset($_GET['add']) && $_GET['add'] == "success"){
                              echo "<div class=\"message success\"><p>Die Zugehörigkeit wurde erfolgreich eingerichtet.</p></div>";
                          }
                          ?>
                          <table>
                          <!-- Richtwerte für die Spaltenbreite -->
                          <colgroup>
                            <col width="40%" />
                            <col width="60%" />
                          </colgroup>
                          <tbody>
                            <tr>
                                <td class="firstcolumn">Mitglied</td>
                                <td><?php
                                if ($user['rights'] >= 4){
                                    // Den Namen des Mitglieds nur verlinken, wenn rechtemäßig die member_show Seite über ihn auch angezeigt werden darf (also ab Rechtelevel 4, ab dem jedes Mitglied gesehen werden darf)
                                    echo "<a title=\"Mitgliedsprofil anzeigen\" href=\"member_show.php?id=".intval($abteilung['mitglieder_id'])."\">";
                                }
                                echo htmlspecialchars($abteilung['vorname']." ".$abteilung['nachname']);
                                if ($user['rights'] >= 4){
                                    echo "</a>";
                                }
                                ?></td>
                            </tr>
                            <tr>
                                <td class="firstcolumn">Abteilung</td>
                                <td><a href="abteilung_show.php?id=<?php echo $abteilung['abteilungs_id'];?>" title="Abteilungsdetails anzeigen"><?php
                                  echo htmlspecialchars($abteilung['name']);
                                ?></a></td>
                            </tr>
                            <tr>
                                <td class="firstcolumn">Art der Zugehörigkeit</td>
                                <td>
                                    <?php
                                    if ($abteilung['aktiv'] == 1){
                                      echo "Aktive Zugehörigkeit";
                                    }elseif($abteilung['aktiv'] == 0){
                                      echo "Passive Zugehörigkeit";
                                    }else{
                                        echo "Ungültiger Wert";
                                    }?></td>
                            </tr>
                            <tr>
                                <td class="firstcolumn">Beitrittsdatum</td>
                                <td>
                                    <?php
                                    echo strftime("%d. %B %Y",strtotime($abteilung['beitrittdate']))
                                    ?></td>
                            </tr>
                          </tbody>
                          </table>
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
                    <h2>Abteilungsmitgliedschaft anzeigen</h2>
                    <div class="message error"><p>Es wurde keine Zugehörigkeit entsprechend der Angaben gefunden!<br /><a href="abteilung_mitglieder.php?id=<?php echo intval($_GET['id']);?>" title="Übersicht aller Abteilungsmitglieder anzeigen">Zur Abteilungsmitgliederverwaltung</a></p></div>

        <?php     }
        }else{ ?>
            <h2>Abteilungsmitgliedschaft anzeigen</h2>
            <div class="message error"><p>Es wurde keine Abteilungs-ID und/oder Mitglieder-ID übergeben!<br /><a href="abteilungen.php" title="Übersicht aller Abteilungen anzeigen">Zur Abteilungsverwaltung</a></p></div>
  <?php } ?>
    		<div class="clearit">&nbsp;</div>
        </div>
<?php
// HTML-Fußbereich einbinden
require_once("inc/footer.inc.php");
?>