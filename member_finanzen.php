<?php
// Datenbankverbindung & Kodierung einbinden
require_once("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once("inc/checkuser.inc.php");

// HTML-Kopf einbinden
require_once("inc/head.inc.php");
?>
        <div id="content">
    		<div class="boxsystem33">
    		  <div class="leftbox">
              <?php
              if (isset($_GET['id']) && $user['rights'] >= 4){
                // Wurde eine ID übergeben und man hat die nötigen Rechte, dann die Finanzen eines anderen Mitglieds anzeigen
                $member_data = mysql_query("SELECT * FROM `mitglieder` WHERE `mitglieder_id` = '".intval($_GET['id'])."' AND `status` = 1");
                if (mysql_num_rows($member_data) == 1){
                  $member = mysql_fetch_assoc($member_data);
                  echo "<h2>Finanzen von \"".htmlspecialchars($member['vorname']." ".$member['nachname'])."\"</h2>";
                }else{
                  // Es wurde kein Mitglied gefunden
                  echo "<h2>Meine Finanzen</h2>
                  <div class=\"message error\"><p>Es wurde kein Mitglied mit dieser ID gefunden. Es werden Ihre Finanzen angezeigt.</p></div>";
                  $member = $user;
                }
              }else{
                // Ansonsten die eigenen Finanzen
                $member = $user;
                echo "<h2>Meine Finanzen</h2>";
              }
              ?>
              <table class="">
              <!-- Richtwerte für die Spaltenbreite -->
              <colgroup>
                <col width="50%" />
                <col width="50%" />
              </colgroup>
              <tr>
                  <td class="firstcolumn">Vereins-Mitgliedsbeitrag</td>
                  <td class="textright">
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
                  <td class=\"textright\">0.00 €</td>
                </tr>";
              }else{
                $abteilungen_gesamt = 0;
                while ($eintrag = mysql_fetch_assoc($result)){
                  $abteilungen_gesamt += $eintrag['aktivenumlage'];
                  echo "<tr>
                    <td class=\"firstcolumn\">Aktivenumlage <a href=\"abteilung_show.php?id=".$eintrag['abteilungs_id']."\" title=\"Abeilung anzeigen\">".$eintrag['name']."</a></td>
                    <td class=\"textright\">".number_format($eintrag['aktivenumlage']/100,2)." €"."</td>
                  </tr>";
                }
                $gesamt += $abteilungen_gesamt;
              }
              ?>
              <tr class="concluderow">
                  <td class="firstcolumn">Gesamt (monatlich)</td>
                  <td class="textright"><?php echo number_format($gesamt/100,2);?> €</td>
              </tr>
              </table>

              <?php
                  // Die Geburtsdaten aller Kinder dieses Mitglieds abrufen
                  $sql = "SELECT `geburtstag` FROM `mitglieder` WHERE (`parent1` = '".$member['mitglieder_id']."' OR `parent2` = '".$member['mitglieder_id']."') AND `status` = 1";
                  $result = mysql_query($sql);
                  if (mysql_num_rows($result) == 0){
                        // Keine Kinder gefunden
                        // echo "<div class=\"message information\"><p>Es sind keine Kinder mit Ihrer Mitgliedschaft verknüpft.</p></div>";
                  }else{
              ?>
            <h2>Kinder</h2>
              <table class="">
              <!-- Richtwerte für die Spaltenbreite -->
              <colgroup>
                <col width="50%" />
                <col width="50%" />
              </colgroup>
              <tr>
                  <td class="firstcolumn">Vereins-Mitgliedsbeitrag</td>
                  <td class="textright">
                  <?php

                    $kinder_gesamt = 0;
                    $count_minderjahrig = 0;
                    $count_volljahrig = 0;
                    while ($eintrag = mysql_fetch_assoc($result)){
                      if (time()-strtotime($eintrag['geburtstag']) < 568024668){
                        $count_minderjahrig++;
                        $kinder_gesamt += $settings['beitrag_kinder'];
                      }else{
                        $count_volljahrig++;
                        $kinder_gesamt += $settings['beitrag_erwachsener'];
                      }
                    }
                    echo $count_minderjahrig."x ".number_format($settings['beitrag_kinder']/100,2)." €<br />\n";
                    echo $count_volljahrig."x ".number_format($settings['beitrag_erwachsener']/100,2)." €";

                  ?>
                  </td>
              </tr>
              <?php
              // Alle Abteilungsmitgliedschaften der Person abrufen, bei denen er "aktives Mitglied" ist
              $sql = "SELECT `aktivenumlage`,`name`,`abteilungs_id`,`aktiv`,`vorname` FROM `abteilungszugehoerigkeit`
                      LEFT JOIN `abteilungen` ON `abteilungszugehoerigkeit`.`abteilung` = `abteilungen`.`abteilungs_id`
                      LEFT JOIN `mitglieder` ON `abteilungszugehoerigkeit`.`mitglied` = `mitglieder`.`mitglieder_id`
                      WHERE (`mitglieder`.`parent1` = '".$member['mitglieder_id']."' OR `mitglieder`.`parent2` = '".$member['mitglieder_id']."') AND `mitglieder`.`status` = 1";
              $result = mysql_query($sql);
              if (mysql_num_rows($result) == 0){
                // Mitglied ist in keiner Abteilung
                echo "<tr>
                  <td class=\"firstcolumn\">Aktivenumlagen</td>
                  <td class=\"textright\">0.00 €</td>
                </tr>";
              }else{
                $abteilungen_kinder_gesamt = 0;
                while ($eintrag = mysql_fetch_assoc($result)){
                  if ($eintrag['aktiv'] == 1){
                      $abteilungen_kinder_gesamt += $eintrag['aktivenumlage'];
                      echo "<tr>
                        <td class=\"firstcolumn\">Aktivenumlage <a href=\"abteilung_show.php?id=".$eintrag['abteilungs_id']."\" title=\"Abeilung anzeigen\">".$eintrag['name']."</a> von ".$eintrag['vorname']."</td>
                        <td class=\"textright\">".number_format($eintrag['aktivenumlage']/100,2)." €"."</td>
                      </tr>";
                  }else{
                      echo "<tr>
                        <td class=\"firstcolumn\">Aktivenumlage <a href=\"abteilung_show.php?id=".$eintrag['abteilungs_id']."\" title=\"Abeilung anzeigen\">".$eintrag['name']."</a> von ".$eintrag['vorname']."</td>
                        <td class=\"textright\"><i>passive Abteilungszugehötigkeit</i></td>
                      </tr>";
                  }
                }
                $kinder_gesamt += $abteilungen_kinder_gesamt;
              }
              ?>
              <tr class="concluderow">
                  <td class="firstcolumn">Gesamt (monatlich)</td>
                  <td class="textright"><?php echo number_format($kinder_gesamt/100,2);?> €</td>
              </tr>
              </table>
              <?php
              } // Ende "Es gibt Kinder"
              ?>
              <?php
              echo "<p><img src=\"images/arrow_left.png\" alt=\"\" class=\"imageinline\" /> ";
                  if (isset($_SERVER["HTTP_REFERER"]) && strpos($_SERVER["HTTP_REFERER"], "members.php") !== false){
                    // Kommt man von der Übersichtsseite?
                    echo "<a href=\"".$_SERVER["HTTP_REFERER"]."\" title=\"Zurück zur vorherigen Seite\">Zurück zur Übersicht</a>";
                  }elseif (isset($_SERVER["HTTP_REFERER"]) && strpos($_SERVER["HTTP_REFERER"], "index.php") !== false){
                    echo "<a href=\"".$_SERVER["HTTP_REFERER"]."\" title=\"Zurück zur vorherigen Seite\">Zurück zur Startseite</a>";
                  }else{
                    echo "<a href=\"member_show.php?id=".$member['mitglieder_id']."\" title=\"Mitgliedsprofil anzeigen\">Mitgliedsprofil anzeigen</a>";
                  }
                  echo "</p>";
              ?>
              </div>
    		  <div class="rightbox">
              <?php
                include("inc/action_leiste_members.inc.php");
              ?>
    		  </div>
    		</div>
    		<div class="clearit">&nbsp;</div>
        </div>
<?php
// HTML-Fußbereich einbinden
require_once("inc/footer.inc.php");
?>