<?php
// Datenbankverbindung & Kodierung einbinden
require_once("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once("inc/checkuser.inc.php");

// Titel festlegen
$title = "Abteilung anzeigen";

// HTML-Kopf einbinden
require_once("inc/head.inc.php");
?>
        <div id="content">

        <?php
        if (isset($_GET['id'])){
            $sql = "SELECT `abteilungen`.*, `mitglieder`.`vorname`, `mitglieder`.`nachname` FROM `abteilungen`
                        LEFT JOIN `mitglieder` ON `abteilungen`.`abteilungsleiter` = `mitglieder`.`mitglieder_id`
                        WHERE `abteilungs_id` = ".intval($_GET['id'])." AND `abteilungen`.`status` = '1' ";
            $abteilungs_result = mysql_query($sql);
            //echo mysql_error();
            if (mysql_num_rows($abteilungs_result) == 1){
                $abteilung = mysql_fetch_assoc($abteilungs_result);
                ?>
            		<div class="boxsystem33">
            		  <div class="leftbox">
                      <h2>Abteilung anzeigen</h2>
                          <?php
                          if (isset($_GET['created'])){
                              echo "<p class=\"message success\">Die neue Abteilung wurde erfolgreich erstellt.</p>";
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
                                <td class="firstcolumn">Name / Bezeichnung</td>
                                <td><?php echo htmlspecialchars($abteilung['name']);?></td>
                            </tr>
                            <tr>
                                <td class="firstcolumn">Beschreibung</td>
                                <td><?php
                                if (empty($abteilung['beschreibung'])){
                                  echo "<i>Keine Beschreibung</i>";
                                }else{
                                  echo htmlspecialchars($abteilung['beschreibung']);
                                }?></td>
                            </tr>
                            <tr>
                                <td class="firstcolumn">Homepage</td>
                                <td><?php
                                if ($abteilung['homepage'] == ""){
                                  echo "<i>Keine Homepage</i>";
                                }else{
                                  echo "<a href=\"".$abteilung['homepage']."\" target=\"_blank\" title=\"Homepage besuchen\" >".htmlspecialchars($abteilung['homepage'])."</a>";
                                }?></td>
                            </tr>
                            <tr>
                                <td class="firstcolumn">Abteilungsleiter</td>
                                <td><a href="member_show.php?id=<?php echo $abteilung['abteilungsleiter'];?>" title="Mitgliedsprofil anzeigen"><?php echo htmlspecialchars($abteilung['vorname']." ".$abteilung['nachname']);?></a></td>
                            </tr>
                            <tr>
                                <td class="firstcolumn">Aktivenumlage</td>
                                <td>
                                <?php
                                if ($abteilung['aktivenumlage'] == 0){
                                  echo "<i>Keine Aktivenumlage</i>";
                                }else{
                                  echo "Monatlich ".number_format($abteilung['aktivenumlage']/100,2)." €";
                                }
                                ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="firstcolumn">Mitglieder</td>
                                <td>
                                <?php
                                $sql_mitglieder = mysql_query("SELECT
                                    (SELECT COUNT(1) FROM `abteilungszugehoerigkeit` WHERE `aktiv` = 1 AND `abteilung` = ".intval($abteilung['abteilungs_id']).") AS aktive,
                                    (SELECT COUNT(1) FROM `abteilungszugehoerigkeit` WHERE `aktiv` = 0 AND`abteilung` = ".intval($abteilung['abteilungs_id']).") AS passive");
                                mysql_error();
                                $sql_mitglieder_data = mysql_fetch_assoc($sql_mitglieder);
                                echo "Aktive Mitglieder: ".intval($sql_mitglieder_data['aktive'])."<br />
                                     Passive Mitglieder: ".intval($sql_mitglieder_data['passive'])."<br />
                                     Mitglieder gesamt: ".(intval($sql_mitglieder_data['aktive'])+intval($sql_mitglieder_data['passive']))."<br />";
                                ?>
                                </td>
                            </tr>
                          </tbody>
                          </table>
                          <p><img src="images/arrow_left.png" alt="" class="imageinline" /> <a href="abteilungen.php" title="Zur Übersicht aller Abteilungen">Zurück zur Übersicht</a></p>
                      </div>
            		  <div class="rightbox">
                      <?php include("inc/action_leiste_abteilungen.inc.php"); ?>
            		  </div>
            		</div>
            <?php }else{ ?>
                    <h2>Abteilung anzeigen</h2>
                    <div class="message error"><p>Es wurde kein Abteilung mit dieser ID gefunden!<br /><a href="abteilungen.php" title="Übersicht aller Abteilungen anzeigen">Zur Abteilungsverwaltung</a></p></div>

        <?php     }
        }else{ ?>
            <h2>Abteilung anzeigen</h2>
            <div class="message error"><p>Es wurde keine Abteilungs-ID übergeben!<br /><a href="abteilungen.php" title="Übersicht aller Abteilungen anzeigen">Zur Abteilungsverwaltung</a></p></div>
  <?php } ?>
    		<div class="clearit">&nbsp;</div>
        </div>
<?php
// HTML-Fußbereich einbinden
require_once("inc/footer.inc.php");
?>