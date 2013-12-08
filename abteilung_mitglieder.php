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
$title = "Abteilungsmitglieder verwalten";

// HTML-Kopf einbinden
require_once("inc/head.inc.php");
?>
        <div id="content">

        <?php
        if (isset($_GET['id'])){
            $sql = "SELECT `abteilungen`.* FROM `abteilungen` WHERE `abteilungs_id` = ".intval($_GET['id'])." AND `abteilungen`.`status` = '1' ";
            $abteilungs_result = mysql_query($sql);
            //echo mysql_error();
            if (mysql_num_rows($abteilungs_result) == 1){
                $abteilung = mysql_fetch_assoc($abteilungs_result);
                if ($abteilung['abteilungsleiter'] == $user['mitglieder_id'] || $user['rights'] >= 4){
                ?>
            		<div class="boxsystem33">
            		  <div class="leftbox">
                      <h2>Abteilungsmitglieder verwalten</h2>
                      <p>Fügen Sie Mitglieder zur Abteilung <i><?php echo htmlspecialchars($abteilung['name']);?></i> hinzu, ändern die Art der Zugehörigkeit oder beenden sie.</p>
                          <?php
                          if (isset($_GET['add']) && $_GET['add'] == "success"){
                              echo "<p class=\"message success\">Die Zugehörigkeit wurde erfolgreich eingerichtet.</p>";
                          }elseif(isset($_GET['deletion']) && $_GET['deletion'] == "success"){
                            echo "<p class=\"message success\">Die Zugehörigkeit wurde erfolgreich entfernt.</p>";
                          }
                           if (!empty($_GET['searchfor'])){
                             // Haben wir eine Suche? Dann muss das Suchwort auch bei URLs übergeben werden, daher bauen wir einen entsprechenden String, der das Suchwort enthält
                             if (strlen($_GET['searchfor']) > 2){
                              $search4url = "&amp;searchfor=".urlencode($_GET['searchfor']);
                             }else{
                               $search4url = "";
                               echo "<div class=\"message warning\"><p>Das eingegebene Suchwort ist zu kurz. Es muss mindestens 3 Zeichen lang sein.</p></div>";
                             }
                           }else{
                             // Haben wir keine Suche, ist der String leer und hat somit keine Auswirkungen auf die URL
                             $search4url = "";
                           }
                           if (isset($_GET['page']) && intval($_GET['page']) >= 1){
                             // Sind wir auf einer bestimmten Seite Suche? Dann muss die Seitenzahl auch bei URLs übergeben werden, daher bauen wir einen entsprechenden String, der diese enthält
                             $page4url = "&amp;page=".intval($_GET['page']);
                           }else{
                             // Haben wir auf keiner bestimmten Seite, ist der String leer und hat somit keine Auswirkungen auf die URL
                             $page4url = "";
                           }
                           $order4url = "";
                          ?>
                       <table>
                        <!-- Richtwerte für die Spaltenbreite -->
                        <colgroup>
                          <col width="35%" />
                          <col width="35%" />
                          <col width="10%" />
                          <col width="20%" />
                        </colgroup>
                        <thead>
                          <tr>
                              <th>Name
                              <?php
                                    if (isset($_GET['orderby']) && $_GET['orderby'] == "name" && $_GET['sort'] == "desc"){
                                      echo "<a href=\"abteilung_mitglieder.php?id=".$abteilung['abteilungs_id']."&amp;orderby=name&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Name sortieren\"><img src=\"images/arrow_down.png\" alt=\"\" /></a>";
                                      $order4url = "&amp;orderby=name&amp;sort=desc";
                                      $sqlorder = "ORDER BY `nachname` DESC, `vorname` DESC";
                                    }elseif(isset($_GET['orderby']) && $_GET['orderby'] == "name" && $_GET['sort'] == "asc"){
                                      echo "<a href=\"abteilung_mitglieder.php?id=".$abteilung['abteilungs_id']."&amp;orderby=name&amp;sort=desc{$search4url}{$page4url}\" title=\"Absteigend nach Name sortieren\"><img src=\"images/arrow_up.png\" alt=\"\" /></a>";
                                      $order4url = "&amp;orderby=name&amp;sort=asc";
                                      $sqlorder = "ORDER BY `nachname` ASC, `vorname` ASC";
                                    }else{
                                      echo "<a href=\"abteilung_mitglieder.php?id=".$abteilung['abteilungs_id']."&amp;orderby=name&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Name sortieren\"><img src=\"images/arrow_updown.png\" alt=\"\" /></a>";
                                    }
                                    ?></th>
                                    <th>Geburtstag
                                    <?php
                                    if (isset($_GET['orderby']) && $_GET['orderby'] == "geburtstag" && $_GET['sort'] == "desc"){
                                      echo "<a href=\"abteilung_mitglieder.php?id=".$abteilung['abteilungs_id']."&amp;orderby=geburtstag&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Geburtstag sortieren\"><img src=\"images/arrow_down.png\" alt=\"\" /></a>";
                                      $order4url = "&amp;orderby=geburtstag&amp;sort=desc";
                                      $sqlorder = "ORDER BY `geburtstag` DESC";
                                    }elseif(isset($_GET['orderby']) && $_GET['orderby'] == "geburtstag" && $_GET['sort'] == "asc"){
                                      echo "<a href=\"abteilung_mitglieder.php?id=".$abteilung['abteilungs_id']."&amp;orderby=geburtstag&amp;sort=desc{$search4url}{$page4url}\" title=\"Absteigend nach Geburtstag sortieren\"><img src=\"images/arrow_up.png\" alt=\"\" /></a>";
                                      $order4url = "&amp;orderby=geburtstag&amp;sort=asc";
                                      $sqlorder = "ORDER BY `geburtstag` ASC";
                                    }else{
                                      echo "<a href=\"abteilung_mitglieder.php?id=".$abteilung['abteilungs_id']."&amp;orderby=geburtstag&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Geburtstag sortieren\"><img src=\"images/arrow_updown.png\" alt=\"\" /></a>";
                                    }
                                    ?>
                                  </th>
                                  <th>Typ</th>
                              <th>Aktionen</th>
                          </tr>
                        </thead>
                        <tbody>
                        <?php
                        // Gesamtanzahl aller Abteilungsmitglieder ermitteln
                        $anzahl_sql = "SELECT count(*) as count FROM `abteilungszugehoerigkeit`
                                LEFT JOIN `mitglieder` ON `abteilungszugehoerigkeit`.`mitglied` = `mitglieder`.`mitglieder_id`
                                WHERE `abteilung` = ".intval($abteilung['abteilungs_id']);
                        if (!empty($_GET['searchfor']) && strlen($_GET['searchfor']) > 2){
                          $anzahl_sql .= " AND (`mitglieder`.`vorname` LIKE '%".mysql_real_escape_string(str_replace(" ","%",$_GET['searchfor']))."%' OR `mitglieder`.`nachname` LIKE '%".mysql_real_escape_string(str_replace(" ","%",$_GET['searchfor']))."%') ";
                        }
                        $anzahl_result = mysql_query($anzahl_sql);
                        $anzahl = mysql_fetch_assoc($anzahl_result);
                        // Festlegen, wie viele Einträge pro Seite angezeigt werden sollen
                        $proseite = 10;
                        if (!isset($_GET['page'])){
                          // Keine Seite übermittelt, dann erste Seite anzeigen
                          $page = 1;
                        }elseif($anzahl['count'] > $proseite){
                              // Seite übermittelt und mehrere Seiten werden auch benötigt
                              $page = abs(intval($_GET['page'])); //Absolutwert, um negative Seitenzahlen zu verhindern
                              // Seite übermittelt und mehrere Seiten werden auch benötigt
                              if (($page-1)*$proseite > $anzahl['count']){
                                // Übermittelte Seite ist zu hoch (so viele Seiten werden doch nicht benötigt)
                                $page = 1;
                              }
                            }
                        $sql = "SELECT * FROM `abteilungszugehoerigkeit`
                                LEFT JOIN `mitglieder` ON `abteilungszugehoerigkeit`.`mitglied` = `mitglieder`.`mitglieder_id`
                                WHERE `abteilung` = ".intval($abteilung['abteilungs_id']);
                        if (!empty($_GET['searchfor']) && strlen($_GET['searchfor']) > 2){
                          // Haben wir eine Suche? Dann das Suchwort in die Abfrage einbauen (eigentliche Filterung findet damit statt)
                          $sql .= " AND (`mitglieder`.`vorname` LIKE '%".mysql_real_escape_string(str_replace(" ","%",$_GET['searchfor']))."%' OR `mitglieder`.`nachname` LIKE '%".mysql_real_escape_string(str_replace(" ","%",$_GET['searchfor']))."%') ";
                        }
                        if (isset($sqlorder)){
                          // Haben wir eine manuelle Sortierung? Dann diese hier in die Abfrage einbauen
                          $sql .= " ".$sqlorder;
                        }else{
                          // Keine manuelle Sortierung, Standardsortierung einbauen
                          $sql .= " ORDER BY `nachname` DESC, `vorname` DESC";
                        }
                        // Ergebnismenge limitieren, evtl. entsprechend der Seitenzahl bereits nur hintere Ergebnisse ausgeben
                        $sql .= " LIMIT ".(abs($page-1)*$proseite).",$proseite";
                        //echo "<tr><td colspan=\"4\">$sql</td></tr>";
                        $mitglieder = mysql_query($sql);
                        if (mysql_num_rows($mitglieder) == 0){
                          echo "<tr><td colspan=\"4\" class=\"tcenter\">Es wurden keine Mitglieder der Abteilung gefunden!</td></tr>";
                        }else{
                          while ($mitglied = mysql_fetch_assoc($mitglieder)){
                            echo "<tr>
                                    <td>".htmlspecialchars($mitglied['vorname']." ".$mitglied['nachname'])."</td>
                                    <td>".strftime("%d. %B %Y",strtotime($mitglied['geburtstag']))."</td>";
                                    if ($mitglied['aktiv'] == 1){
                                      echo "<td>Aktiv</td>";
                                    }else{
                                      echo "<td>Passiv</td>";
                                    }
                                    echo "<td class=\"actionrow\"><a href=\"abteilung_mitgliedschaft_show.php?id=".$abteilung['abteilungs_id']."&amp;who=".$mitglied['mitglieder_id']."\" title=\"Abteilungsmitgliedschaft anzeigen\"><img src=\"images/show.png\" alt=\"\" title=\"Abteilungsmitgliedschaft anzeigen\" /></a>
                                    <a href=\"abteilung_mitgliedschaft_edit.php?id=".$abteilung['abteilungs_id']."&amp;who=".$mitglied['mitglieder_id']."\" title=\"Abteilungsmitgliedschaft bearbeiten\"><img src=\"images/edit.png\" alt=\"\" title=\"Abteilungsmitgliedschaft bearbeiten\" /></a>
                                    <a href=\"abteilung_mitgliedschaft_delete.php?id=".$abteilung['abteilungs_id']."&amp;who=".$mitglied['mitglieder_id']."\" title=\"Abteilungsmitgliedschaft entfernen/beenden\"><img src=\"images/trash_can.png\" alt=\"\" title=\"Abteilungsmitgliedschaft entfernen/beenden\" /></a></td>
                                  </tr>";
                          }
                          }
                        ?>
                        </tbody>
                       </table>
                             <?php
                             if ($anzahl['count'] > $proseite){
                                // Gibt es mehr Einträge, als auf einer Seite anzeigt werden können, also muss eine Seitennavigation angezeigt werden
                                echo "<div class=\"pagenavi\">
                                <p class=\"tcenter\">
                                <a href=\"abteilung_mitglieder.php?id=".$abteilung['abteilungs_id']."&amp;page=".($page-1)."{$order4url}{$search4url}\" title=\"Seite zurück\" class=\"naviicon";
                                if ($page == 1){
                                  // Auf der ersten Seite "zurück" Pfeil verstecken
                                  echo " hidden";
                                }
                                echo "\" ><img src=\"images/arrow_left.png\" alt=\"&laquo;\" /></a> \n";
                                for ($i = 1; ($i-1)*$proseite < $anzahl['count']; $i++)  {
                                  echo "<a href=\"abteilung_mitglieder.php?id=".$abteilung['abteilungs_id']."&amp;page={$i}{$order4url}{$search4url}\"";
                                  if ($i == $page){
                                    echo " class=\"currentpage\" title=\"Aktuelle Seite $i\" ";
                                  }else{
                                    echo " title=\"Seite $i anzeigen\" ";
                                  }
                                  echo ">$i</a> \n";
                                }
                                echo "
                                <a href=\"abteilung_mitglieder.php?id=".$abteilung['abteilungs_id']."&amp;page=".($page+1)."{$order4url}{$search4url}\" title=\"Seite nach vorne\" class=\"naviicon";
                                if ($page*$proseite >= $anzahl['count']){
                                  // Auf der letzten Seite "vorwärts" Pfeil verstecken
                                  echo " hidden";
                                }
                                echo "\"><img src=\"images/arrow_right.png\" alt=\"&raquo;\" /></a> \n
                                </p>
                                </div>";
                             }
                             ?>
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
                    <h2>Abteilungsmitglieder verwalten</h2>
                    <div class="message error"><p>Es wurde kein Abteilung mit dieser ID gefunden!<br /><a href="abteilungen.php" title="Übersicht aller Abteilungen anzeigen">Zur Abteilungsverwaltung</a></p></div>

        <?php     }
        }else{ ?>
            <h2>Abteilungsmitglieder verwalten</h2>
            <div class="message error"><p>Es wurde keine Abteilungs-ID übergeben!<br /><a href="abteilungen.php" title="Übersicht aller Abteilungen anzeigen">Zur Abteilungsverwaltung</a></p></div>
  <?php } ?>
    		<div class="clearit">&nbsp;</div>
        </div>
<?php
// HTML-Fußbereich einbinden
require_once("inc/footer.inc.php");
?>