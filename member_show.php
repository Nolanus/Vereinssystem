<?php
// Datenbankverbindung & Kodierung einbinden
require_once("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once("inc/checkuser.inc.php");

// Titel festlegen
$title = "Mitgliedsprofil anzeigen";

// HTML-Kopf einbinden
require_once("inc/head.inc.php");
?>
        <div id="content">
        <?php
        if (isset($_GET['id'])){
            $sql = "SELECT * FROM `mitglieder` LEFT JOIN `orte` ON `mitglieder`.`anschrift` = `orte`.`orts_id` WHERE `mitglieder_id` = ".intval($_GET['id'])." AND `mitglieder`.`status` = '1' ";
            $member_result = mysql_query($sql);
            if (mysql_num_rows($member_result) == 1){
                $member = mysql_fetch_assoc($member_result);
                // Ansehen können Eltern das Profil ihrer Kinder auch nach deren 18. Lebensjahr noch
                if ($member['parent2'] == $user['mitglieder_id']){
                    $isfather = true;
                }else{
                    $isfather = false;
                }
                if ($member['parent1'] == $user['mitglieder_id']){
                    $ismother = true;
                }else{
                    $ismother = false;
                }
                // Ist das Mitglied vll. irgendwo Abteilungsleiter? (Dann soll auch das Profil eingeschränkt betrachtet werden können)
                $test_sql_abteilungsleiter = "SELECT `abteilungs_id` FROM `abteilungen` WHERE `abteilungsleiter` = '".$member['mitglieder_id']."'";
                $test_abteilungsleiter = mysql_query($test_sql_abteilungsleiter);
                $abteilungsleiter = mysql_num_rows($test_abteilungsleiter);
                if ($user['rights'] >= 4 || $member['mitglieder_id'] == $user['mitglieder_id'] || $isfather || $ismother || $abteilungsleiter > 0){
                  // Kann das aktuelle Profil nur angesehen werden, da das Mitglied Abteilungsleiter ist?
                  if ($user['rights'] < 4 && $member['mitglieder_id'] != $user['mitglieder_id'] && !$isfather && !$ismother && $abteilungsleiter > 0){
                    $just_because_abteilungsleiter = true;
                  }else{
                    $just_because_abteilungsleiter = false;
                  }
                ?>
            		<div class="boxsystem33">
            		  <div class="leftbox">
                        <?php
                        if (isset($_GET['created'])){
                            echo "<div class=\"message success\"><p>Das Mitglied wurde erfolgreich erstellt.<br />Klicken Sie rechts auf \"Einstellungen\", um ein Passwort zu vergeben.</p></div>";
                        }
                        ?>
                          <h2>Mitgliedsprofil "<?php echo htmlspecialchars($member['vorname']." ".$member['nachname'])?>"</h2>
                          <table>
                          <!-- Richtwerte für die Spaltenbreite -->
                          <colgroup>
                            <col width="40%" />
                            <col width="60%" />
                          </colgroup>
                          <tbody>
                            <tr>
                                <td class="firstcolumn" colspan="2">Persönliches</td>
                            </tr>
                            <tr>
                                <td class="firstcolumn">Vorname</td>
                                <td><?php echo htmlspecialchars($member['vorname']);?></td>
                            </tr>
                            <tr>
                                <td class="firstcolumn">Nachname</td>
                                <td><?php echo htmlspecialchars($member['nachname']);?></td>
                            </tr>
                            <?php
                            if ($just_because_abteilungsleiter == false){
                            ?>
                                <tr>
                                    <td class="firstcolumn">Geschlecht</td>
                                    <td><?php
                                    switch ($member['geschlecht']) {
                                      case 1:
                                        echo "männlich";
                                      break;
                                      case 2:
                                        echo "weiblich";
                                      break;
                                      default:
                                        echo "<i>Nicht gesetzt</i>";
                                    }?></td>
                                </tr>
                                <tr>
                                    <td class="firstcolumn">Geburtstag</td>
                                    <td><?php echo strftime("%d. %B %Y",strtotime($member['geburtstag']));
                                    echo " (".floor((time()-strtotime($member['geburtstag']))/31556926)." Jahre)";?></td>
                                </tr>
                                <tr>
                                    <td class="firstcolumn">Eltern / Kinder</td>
                                    <td>
                                    <?php
                                    $sql = "SELECT `mitglieder_id`,`vorname`,`nachname`,`geschlecht` FROM `mitglieder` WHERE `mitglieder_id` = '".$member['parent1']."' OR `mitglieder_id` = '".$member['parent2']."' OR `parent1` = '".$member['mitglieder_id']."' OR `parent2` = '".$member['mitglieder_id']."' ORDER BY `geburtstag`";
                                    $relations = mysql_query($sql);
                                    if (mysql_num_rows($relations) == 0){
                                      echo "<i>Keine Beziehungen vorhanden</i>";
                                    }else{
                                      while ($relation = mysql_fetch_assoc($relations)){
                                        switch ($relation['mitglieder_id']) {
                                          case $member['parent1']:
                                            echo "Mutter: <a href=\"member_show.php?id=".$relation['mitglieder_id']."\" title=\"Mitgliedsprofil anzeigen\">".$relation['vorname']." ".$relation['nachname']."</a><br />";
                                          break;
                                          case $member['parent2']:
                                            echo "Vater: <a href=\"member_show.php?id=".$relation['mitglieder_id']."\" title=\"Mitgliedsprofil anzeigen\">".$relation['vorname']." ".$relation['nachname']."</a><br />";
                                          break;
                                          default:
                                            switch ($relation['geschlecht']) {
                                              case 1:
                                                  echo "Sohn";
                                              break;
                                              case 2:
                                                  echo "Tochter";
                                              break;
                                              default:
                                                  echo "Kind";
                                            }
                                            echo ": <a href=\"member_show.php?id=".$relation['mitglieder_id']."\" title=\"Mitgliedsprofil anzeigen\">".$relation['vorname']." ".$relation['nachname']."</a><br />";
                                        }
                                      }
                                    }
                                    ?>
                                    </td>
                                </tr>
                            <?php
                            } // Ende $just_because_abteilungsleiter == false
                            ?>
                            <tr>
                                <td class="firstcolumn" colspan="2">Kontaktdaten</td>
                            </tr>
                            <tr>
                                <td class="firstcolumn">Wohnort</td>
                                <td><?php echo $member['strasse']." ".$member['hausnummer']."<br />".$member['plz']." ".$member['ort'];
                                if ($user['rights'] >= 3){
                                  // Link nur ab rechtelevel 3 anzeigen, da man ab diesem alle Orte sehen kann und der Link somit nicht zu einer "Permission Denied" Nachricht führt
                                  echo "<br /><a href=\"ort_show.php?id=".$member['anschrift']."\" title=\"Wohnort anzeigen\">Wohnort anzeigen</a>";
                                }
                                ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="firstcolumn">Telefonnummer</td>
                                <td><?php
                                if (empty($member['telefon'])){
                                  echo "<i>Keine Telefonnummer</i>";
                                }else{
                                  echo $member['telefon'];
                                }?></td>
                            </tr>
                            <tr>
                                <td class="firstcolumn">Handynummer</td>
                                <td><?php
                                if (empty($member['handy'])){
                                  echo "<i>Keine Handynummer</i>";
                                }else{
                                  echo $member['handy'];
                                }?></td>
                            </tr>
                            <tr>
                                <td class="firstcolumn">E-Mail</td>
                                <td><?php
                                if (empty($member['email'])){
                                  echo "<i>Keine E-Mail-Adresse</i>";
                                }else{
                                  echo $member['email'];
                                }?></td>
                            </tr>
                            <?php
                            if ($just_because_abteilungsleiter == false){
                            ?>
                                <tr>
                                    <td class="firstcolumn" colspan="2">Mitgliedschaft</td>
                                </tr>
                                <tr>
                                    <td class="firstcolumn">Mitgliedschaft</td>
                                    <td><?php
                                    switch ($member['mitgliedschaft']) {
                                      case 1:
                                        echo "Unterstützend / Passiv";
                                      break;
                                      case 2:
                                        echo "Ruhend";
                                      break;
                                      default:
                                        echo "Normal / Aktiv";
                                    }?></td>
                                </tr>
                                <tr>
                                    <td class="firstcolumn">Beitrittsdatum</td>
                                    <td><?php echo strftime("%d. %B %Y",strtotime($member['beitritt']));?></td>
                                </tr>
                                <tr>
                                    <td class="firstcolumn">Mitgliedsnummer</td>
                                    <td><?php echo $member['mitgliedsnummer'];?></td>
                                </tr>
                                <tr>
                                    <td class="firstcolumn">Abteilungen</td>
                                    <td><?php
                                    // Ist das Mitglied ein Abteilungsleiter?
                                    $sql = "SELECT * FROM `abteilungen` WHERE `abteilungsleiter` = ".intval($member['mitglieder_id'])." AND `abteilungen`.`status` = 1";
                                    $zugehoerigkeiten = mysql_query($sql);
                                    $leitungen = mysql_num_rows($zugehoerigkeiten);
                                    if ($leitungen > 0){
                                      while ($zugehoerigkeit = mysql_fetch_assoc($zugehoerigkeiten)){
                                        echo "Abteilungsleiter";
                                        if ($member['geschlecht'] == 2){
                                          // Weiblichkeitsform
                                          echo "in";
                                        }
                                        echo " <a href=\"abteilung_show.php?id=".intval($zugehoerigkeit['abteilungs_id'])."\" title=\"Abteilung anzeigen\">".$zugehoerigkeit['name']."</a><br />\n";
                                      }
                                    }
                                    // In welchen Abteilungen ist das Mitglied normales Mitglied
                                    $sql = "SELECT * FROM `abteilungszugehoerigkeit` LEFT JOIN `abteilungen` ON `abteilungszugehoerigkeit`.`abteilung` = `abteilungen`.`abteilungs_id` WHERE `mitglied` = ".intval($member['mitglieder_id'])." AND `abteilungen`.`status` = 1";
                                    $zugehoerigkeiten = mysql_query($sql);
                                    if (mysql_num_rows($zugehoerigkeiten) > 0){
                                      while ($zugehoerigkeit = mysql_fetch_assoc($zugehoerigkeiten)){
                                        echo "<a href=\"abteilung_show.php?id=".intval($zugehoerigkeit['abteilungs_id'])."\" title=\"Abteilung anzeigen\">".$zugehoerigkeit['name']."</a> (";
                                        if (($user['rights'] == 3 && $zugehoerigkeit['abteilungsleiter'] == $user['mitglieder_id']) || $user['rights'] >= 4){
                                          // Link nur anzeigen, wenn die Seite dahinter auch rechtemäßig angeshcaut werden darf (also Entweder man ist Abteilungsleiter mit Rechtelevel 3 oder hat Rechtelevel 4 oder höher)
                                          echo "<a href=\"abteilung_mitgliedschaft_show.php?id=".intval($zugehoerigkeit['abteilungs_id'])."&amp;who=".intval($member['mitglieder_id'])."\" title=\"Abteilungsmitgliedschaft anzeigen\">";
                                          if ($zugehoerigkeit['aktiv'] == 1){
                                            echo "aktive Zugehörigkeit";
                                          }else{
                                            echo "passive Zugehörigkeit";
                                          }
                                          echo "</a>";
                                        }else{
                                          if ($zugehoerigkeit['aktiv'] == 1){
                                            echo "aktive Zugehörigkeit";
                                          }else{
                                            echo "passive Zugehörigkeit";
                                          }
                                        }
                                        echo ")<br />\n";
                                      }
                                    }elseif($leitungen == 0){
                                      echo "<i>(In keiner Abteilung Mitglied)</i>";
                                    }
                                    ?></td>
                                </tr>
                                <?php
                                if ($member['mitglieder_id'] == $user['mitglieder_id'] || $user['rights'] >= 4 || (($isfather || $ismother) && time()- strtotime($member['geburtstag']) < 568024668)){
                                  // Wenn die Kinder älter als 18 sind, dürfen Eltern deren Bankverbindung, deren Finanzen und deren Diensttätigkeiten nicht mehr sehen
                                  $sql_dienste = "SELECT COUNT(`dienste`.`person`) as dienstzahl, SUM(`dienste`.`dienstart`) as kuchenzahl, COUNT(DISTINCT `dienste`.`endzeit`) - IF( SUM(`dienste`.`dienstart`) > 0, 1, 0) as standdienste
                                    FROM `mitglieder`
                                    LEFT JOIN `dienste` ON `dienste`.`person` = `mitglieder`.`mitglieder_id`
                                    WHERE `status` = 1 AND `startzeit` > ".mktime(0,0,0,1,1,date("Y"))." AND `startzeit` < ".mktime(23,59,59,12,31,date("Y"))." AND `mitglieder_id` = '".$member['mitglieder_id']."'
                                    GROUP BY `mitglieder_id`";
                                  $dienste_result = mysql_query($sql_dienste);
                                  $dienst_daten = mysql_fetch_assoc($dienste_result);
                                  $geleisteter_dienst = intval($dienst_daten['standdienste']);
                                  if ($dienst_daten['kuchenzahl'] > 0){
                                    // Um Probleme mit der MySQL Rückgabe NULL zu vermeiden
                                    $geleisteter_dienst = $geleisteter_dienst + floor($dienst_daten['kuchenzahl']/$settings['cakeworkcount']);
                                  }
                                  if (time()- strtotime($member['geburtstag']) < 568024668){
                                        // Kinder (unter 18) brauchen keine Mindestmenge an Diensten
                                        $zuleistende_arbeit = 0;
                                  }else{
                                        if (strtotime($member['beitritt']) < mktime(0,0,0,1,1,date("Y"))){
                                          // Das Mitglied ist bereits seit Beginn des Jahres oder eher noch länger Mitglied
                                          $zuleistende_arbeit = $settings['annualwork'] + $member['dienste_lastyears'];
                                        }else{
                                          // Das Mitglied kam im Laufe des Jahres zum Verein. Die zuleistende Arbeit wird entsprechend prozentual angepasst
                                          $zuleistende_arbeit = ceil($settings['annualwork'] * (abs(365 - date("z",strtotime($member['beitritt'])))/365));
                                          // $member['dienste_lastyears'] kann vernachlässigt werden, da er ja eh nicht vorher dabei war
                                        }
                                  }
                                  ?>
                                  <tr>
                                      <td class="firstcolumn">Zu leistende Arbeit <?php echo date("Y");?></td>
                                      <td><?php echo $zuleistende_arbeit." Einheiten";
                                      if ($member['dienste_lastyears'] > 0){
                                        echo " (davon ".$member['dienste_lastyears']." aus Vorjahren)";
                                      } ?>
                                      </td>
                                  </tr>
                                  <tr>
                                      <td class="firstcolumn">Geleistete Dienste <?php echo date("Y");?></td>
                                      <td><?php
                                        echo "Standdienst: ".intval($dienst_daten['standdienste'])." Einheit";
                                        if (intval($dienst_daten['standdienste']) != 1){
                                          echo "en";
                                        }
                                        echo "<br />\nKuchen: ".intval($dienst_daten['kuchenzahl'])." Stück";
                                        echo "<br />\n<b>Gesamt:</b> ".$geleisteter_dienst."";
                                        // Prozentwert des bereits geleisteten Dienstes berechnen
                                        if ($zuleistende_arbeit > 0){
                                          $prozent = round($geleisteter_dienst/$zuleistende_arbeit*100,0);
                                          if ($prozent > 100){
                                            $prozent = 100;
                                          }
                                          echo "<br />\n<br />\n<img src=\"statusbar.php?value=".$prozent."\" title=\"{$prozent}% der nötigen Arbeit wurde absolviert\" alt=\"\" /> ";
                                        }
                                      ?>
                                      </td>
                                  </tr>
                                  <tr>
                                      <td class="firstcolumn">Abrechnung</td>
                                      <td><?php if ($member['abrechnung'] == 0){
                                        echo "Bankeinzug";
                                      }else{
                                        echo "Überweisung";
                                      }?>
                                      </td>
                                  </tr>
                                  <tr>
                                      <td class="firstcolumn">Bankverbindung</td>
                                      <td><?php if ($member['abrechnung'] == 0){
                                        echo $member['kontoinhaber']."<br />".$member['kontonummer']."<br />".$member['blz']." ".$member['bankname'];
                                      }else{
                                        echo "<i>Nicht relevant</i>";
                                      }?>
                                      </td>
                                  </tr>
                                  <tr><td colspan="2" class="firstcolumn"><a href="member_finanzen.php?id=<?php echo $member['mitglieder_id'];?>" title="Finanzübersicht anzeigen">Finanzen</a></td></tr>
                                  <tr>
                                      <td class="firstcolumn">Vereins-Mitgliedsbeitrag</td>
                                      <td>
                                      <?php
                                      // Gesamtbetrag für den aktuellen Nutzer auf 0 setzen
                                      $gesamt = 0;
                                      if (time()-strtotime($member['geburtstag']) < 568024668){
                                          // Es ist ein Kind
                                          echo number_format($settings['beitrag_kinder']/100,2)." €";
                                          $gesamt += $settings['beitrag_kinder'];
                                      }else{
                                          // Mitglied wird als Erwachsener behandelt
                                          echo number_format($settings['beitrag_erwachsener']/100,2)." €";
                                          $gesamt += $settings['beitrag_erwachsener'];
                                      }
                                      ?>
                                      </td>
                                  </tr>
                                  <?php
                                  // Alle Abteilungsmitgliedschaften der Person abrufen, bei denen er "aktives Mitglied" ist
                                  $sql = "SELECT `aktivenumlage`,`name`,`abteilungs_id` FROM `abteilungszugehoerigkeit`
                                          LEFT JOIN `abteilungen` ON `abteilungszugehoerigkeit`.`abteilung` = `abteilungen`.`abteilungs_id`
                                          WHERE `mitglied` = '".$member['mitglieder_id']."' AND `aktiv` = 1";
                                  $result = mysql_query($sql);
                                  if (mysql_num_rows($result) == 0){
                                    // Mitglied ist in keiner Abteilung
                                    echo "<tr>
                                      <td class=\"firstcolumn\">Aktivenumlagen</td>
                                      <td>0.00 €</td>
                                    </tr>";
                                  }else{
                                    $abteilungen_gesamt = 0;
                                    while ($eintrag = mysql_fetch_assoc($result)){
                                      $abteilungen_gesamt += $eintrag['aktivenumlage'];
                                      echo "<tr>
                                        <td class=\"firstcolumn\">Aktivenumlage <a href=\"abteilung_show.php?id=".$eintrag['abteilungs_id']."\" title=\"Abeilung anzeigen\">".$eintrag['name']."</a></td>
                                        <td>".number_format($eintrag['aktivenumlage']/100,2)." €"."</td>
                                      </tr>";
                                    }
                                    $gesamt += $abteilungen_gesamt;
                                  }
                                  ?>
                                  <tr>
                                      <td class="firstcolumn">Gesamt (monatlich)</td>
                                      <td><?php echo number_format($gesamt/100,2);?> €</td>
                                  </tr>
                                <?php
                                }
                                // Admin only
                                if ($user['rights'] >= 5){
                                ?>
                                    <tr>
                                        <td class="firstcolumn" colspan="2">System</td>
                                    </tr>
                                    <tr>
                                        <td class="firstcolumn">Username / Nutzername</td>
                                        <td><?php
                                            echo htmlspecialchars($member['username']);
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="firstcolumn">Rechtelevel</td>
                                        <td><?php
                                        switch ($member['rights']) {
                                          case 0:
                                            echo "<span class=\"akzentrot\">gesperrt</span>";
                                          break;
                                          case 1:
                                            echo "Normaler Nutzer (Stufe 1)";
                                          break;
                                          case 2:
                                            echo "Moderator (Stufe 2)";
                                          break;
                                          case 3:
                                            echo "Abteilungsleiter (Stufe 3)";
                                          break;
                                          case 4:
                                            echo "Vereinsvorstand (Stufe 4)";
                                          break;
                                          case 5:
                                            echo "Administrator (Stufe 5)";
                                          break;
                                          default:
                                            echo "<i>unbekannt</i> (Stufe ".intval($member['rights']).")";
                                        }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="firstcolumn">Notizen</td>
                                        <td><?php
                                            if (strlen($member['notizen']) > 0){
                                                echo htmlspecialchars($member['notizen']);
                                            }else{
                                                echo "<i>Keine Notizen</i>";
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="firstcolumn">Letzter Login</td>
                                        <td><?php
                                        if ($member['currentlogintime'] == 0){
                                          echo "<i>Noch kein Login bisher</i>";
                                        }else{
                                          echo strftime("%A, %d. %B %Y, %H:%M",$member['currentlogintime'])." Uhr";
                                        }
                                            ?>
                                        </td>
                                    </tr>
                                <?php
                                } // ENde Admin-Only
                                ?>
                            <?php
                            } // Ende $just_because_abteilungsleiter == false
                            ?>
                            </tbody>
                            </table>
                      </div>
            		  <div class="rightbox">
                      <?php
                        include("inc/action_leiste_members.inc.php");
                      ?>
            		  </div>
            		</div>
            <?php }else{
                  echo "<div class=\"message error\"><p>Für diese Aktion haben Sie nicht die erforderlichen Rechte. Bitte beachten Sie, dass Sie Ihre Kinder nur bis zu dessen Alter von 18 Jahren bearbeiten können.<br />
                  Wenden Sie sich an den Systemadministrator, wenn Sie der Meinung sind, diese Funktion zu benötigen.</p></div>";
                }
            }else{ ?>
                    <h2>Mitglied anzeigen</h2>
                    <div class="message error"><p>Es wurde kein Mitglied mit dieser ID gefunden!<br /><a href="members.php" title="Übersicht aller Mitglieder anzeigen">Zur Mitgliederverwaltung</a></p></div>

        <?php     }
        }else{ ?>
        <h2>Mitglied anzeigen</h2>
        <div class="message error"><p>Es wurde keine Mitglieder-ID übergeben!<br /><a href="members.php" title="Übersicht aller Mitglieder anzeigen">Zur Mitgliederverwaltung</a></p></div>

            <?php } ?>
    		<div class="clearit">&nbsp;</div>
        </div>
<?php
// HTML-Fußbereich einbinden
require_once("inc/footer.inc.php");
?>