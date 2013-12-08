<?php
// Datenbankverbindung & Kodierung einbinden
require_once("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once("inc/checkuser.inc.php");

// Titel festlegen
$title = "Ort / Anschrift anzeigen";

// HTML-Kopf einbinden
require_once("inc/head.inc.php");
?>
        <div id="content">
        <?php
        if (isset($_GET['id'])){
            $sql = "SELECT `orte`.*";
            if ($user['rights'] >= 5){
              // Hat man die nötigen Rechte, auch den Ersteller des Ortes mit auslesen
              $sql .= ", `mitglieder`.`vorname`, `mitglieder`.`nachname` FROM `orte`
                        LEFT JOIN `mitglieder` ON `orte`.`createdby` = `mitglieder`.`mitglieder_id` ";
            }else{
              $sql .= " FROM `orte` ";
            }
            $sql .= "WHERE `orts_id` = ".intval($_GET['id'])." AND `orte`.`status` = '1' ";
            $ort_result = mysql_query($sql);
            //echo mysql_error();
            if (mysql_num_rows($ort_result) == 1){
                $ort = mysql_fetch_assoc($ort_result);
                // Hat der Nutzer die erforderlichen Rechte?
                if ($user['rights'] <= 2 && $ort['typ'] == 2 || $user['anschrift'] == $ort['orts_id'] || $settings['vereinsanschrift'] == $ort['orts_id'] || $user['rights'] >= 3){
                ?>
            		<div class="boxsystem33">
            		  <div class="leftbox">
                          <h2>Ort / Anschrift anzeigen</h2>
                          <?php
                          if (isset($_GET['created'])){
                              echo "<div class=\"message success\"><p>Der neue Ort/Die neue Anschrift wurde erfolgreich erstellt.</p></div>";
                          }
                          if ($settings['vereinsanschrift'] == $ort['orts_id']){
                            echo "<div class=\"message information\"><p>Dies ist die offizielle Anschrift der Vereinsgeschäftsstelle.</p></div>";
                          }
                          ?>
                          <table>
                          <!-- Richtwerte für die Spaltenbreite -->
                          <colgroup>
                            <col width="40%" />
                            <col width="60%" />
                          </colgroup>
                          <tbody>
                            <?php
                            if ($ort['typ'] == 2){
                              // Nur Veranstaltungsorte haben Namen, also auch nur dann die entsprechende Zeile anzeigen
                            ?>
                              <tr>
                                  <td class="firstcolumn">Name / Bezeichnung</td>
                                  <td><?php echo htmlspecialchars($ort['name']);?></td>
                              </tr>
                            <?php
                            }
                            ?>
                            <tr>
                                <td class="firstcolumn">Adresse</td>
                                <td><?php echo htmlspecialchars($ort['strasse']." ".$ort['hausnummer'])."<br />\n".htmlspecialchars($ort['plz']." ".$ort['ort']);?></td>
                            </tr>
                            <tr>
                                <td class="firstcolumn">Telefonnr.</td>
                                <td><?php
                                if (empty($ort['telefon'])){
                                  echo "<i>Keine Telefonnummer</i>";
                                }else{
                                  echo htmlspecialchars($ort['telefon']);
                                }?></td>
                            </tr>
                            <tr>
                                <td class="firstcolumn">Ortstyp</td>
                                <td>
                                    <?php
                                    if ($ort['typ'] == 1){
                                      echo "Wohnort";
                                    }elseif($ort['typ'] == 2){
                                      echo "Veranstaltungsort";
                                    }else{
                                        echo "<i>Nicht festgelegt</i>";
                                    }?></td>
                            </tr>
                            <tr>
                                <td class="firstcolumn">Verknüpfungen</td>
                                <td>
                                    <?php
                                      $connections_members_result = mysql_query("SELECT COUNT(*) as count FROM `mitglieder` WHERE `anschrift` = '".$ort['orts_id']."'");
                                      $connections_members = mysql_fetch_assoc($connections_members_result);
                                      echo "Mitglieder, die hier wohnen: ".$connections_members['count']."<br />";
                                      $connections_events_result = mysql_query("SELECT COUNT(*) as count FROM `veranstaltungen` WHERE `ort` = '".$ort['orts_id']."'");
                                      $connections_events = mysql_fetch_assoc($connections_events_result);
                                      echo "Veranstaltungen, die hier stattfanden/finden: ".$connections_events['count'];
                                      ?></td>
                            </tr>
                            <?php
                            if ($user['rights'] >= 5){
                            ?>
                            <tr>
                                <td class="firstcolumn">Erstellt von</td>
                                <td><?php
                                if ($ort['createdby'] == 0){
                                  echo "<i>unbekannt</i>";
                                }else{
                                  echo "<a href=\"member_show.php?id=".$ort['createdby']."\" title=\"Mitgliedsprofil anzeigen\">".$ort['vorname']." ".$ort['nachname']."</a>";
                                }
                                ?></td>
                            </tr>
                            <?php
                            } // Ende User-Rights 5 oder höher only
                            ?>
                          </tbody>
                          </table>
                          <p class="tcenter"><a href="http://maps.google.com/?q=<?php echo rawurlencode($ort['strasse']." ".$ort['hausnummer']." ".$ort['plz']." ".$ort['ort'].", Germany");?>" target="_blank" title="Adresse bei Google Maps anzeigen"><img src="http://maps.googleapis.com/maps/api/staticmap?markers=color:red%7C<?php echo rawurlencode($ort['strasse']." ".$ort['hausnummer'].",".$ort['plz']." ".$ort['ort'].",Germany");?>&amp;zoom=14&amp;size=550x400&amp;sensor=false" alt="" title="" /></a></p>
                          <p><img src="images/arrow_left.png" alt="" class="imageinline" /> <a href="orte.php" title="Zur Übersicht aller Orte">Zurück zur Übersicht</a></p>
                      </div>
            		  <div class="rightbox">
                      <?php include("inc/action_leiste_orte.inc.php"); ?>
            		  </div>
            		</div>
            <?php }else{ ?>
                <h2>Ort / Anschrift anzeigen</h2>
                <div class="message error"><p>Für diese Aktion haben Sie nicht die erforderlichen Rechte.<br />
                  Wenden Sie sich an den Systemadministrator, wenn Sie der Meinung sind, diese Funktion zu benötigen.<br /><a href="orte.php" title="Übersicht aller Orte anzeigen">Zur Ortsverwaltung</a></p></div>
        <?php
            }
             }else{ ?>
                    <h2>Ort / Anschrift anzeigen</h2>
                    <div class="message error"><p>Es wurde kein Ort mit dieser ID gefunden!<br /><a href="orte.php" title="Übersicht aller Orte anzeigen">Zur Orts- und Anschriftsverwaltung</a></p></div>

        <?php     }
        }else{ ?>
        <h2>Ort / Anschrift anzeigen</h2>
        <div class="message error"><p>Es wurde keine Orts-ID übergeben!<br /><a href="orte.php" title="Übersicht aller Orte anzeigen">Zur Orts- und Anschriftsverwaltung</a></p></div>

            <?php } ?>
    		<div class="clearit">&nbsp;</div>
        </div>
<?php
// HTML-Fußbereich einbinden
require_once("inc/footer.inc.php");
?>