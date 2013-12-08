<?php
// Datenbankverbindung & Kodierung einbinden
require_once("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once("inc/checkuser.inc.php");

// Titel festlegen
$title = "Abteilungsverwaltung";

// JavaScript einfügen
$appendjs = "$(\"#resetsearch\").click(function(){
$(\"#searchfor\").val(\"\");
});";

// HTML-Kopf einbinden
require_once("inc/head.inc.php");
?>
        <div id="content">
           <h2>Abteilungsverwaltung</h2>
           <form id="searchform" class="formular" accept-charset="utf-8" method="get" action="abteilungen.php" enctype="">
             <p class="actionline">
                <span class="actionlinesearchform"><img src="images/magnifier.png" alt="" title="Suchen"  /> <input type="text" name="searchfor" id="searchfor" value="<?php if (!empty($_GET['searchfor'])){echo htmlspecialchars($_GET['searchfor']);}?>" class="actionlinesearch" title="Nach Abteilungsname oder Beschreibung suchen" /> <input type="submit" value="Suchen" class="actionlinesearch" /> <input type="submit" id="resetsearch" value="Zurücksetzen" class="actionlinesearch" /></span>
                <?php if ($user['rights'] >= 4){ //Mindestens Rechtelevel 4 zum Erstellen von Abteilungen ?>
                <span><img src="images/add.png" alt="" title="Hinzufügen/Erstellen"  /> <a href="abteilung_add.php" title="Neuen Abteilung erstellen" >Abteilung hinzufügen</a></span>
                <?php } ?>
             </p>
             </form>
             <div class="clearit">&nbsp;</div>
             <?php
             // Meldungen, die per GET übergeben wurden anzeigen
             if (isset($_GET['removal'])){
               if ($_GET['removal'] == "success"){
                 echo "<div class=\"message success\"><p>Die Abteilung wurde erfolgreich aus dem System entfernt.</p></div>";
               }else{
                 echo "<div class=\"message error\"><p>Beim Entfernen der Abteilung ist ein Fehler aufgetreten.</p></div>";
               }
             }elseif(isset($_GET['deletion'])){
               if ($_GET['deletion'] == "success"){
                 echo "<div class=\"message success\"><p>Der Abteilung wurde erfolgreich aus der Datenbank gelöscht.</p></div>";
               }else{
                 echo "<div class=\"message error\"><p>Beim Löschen der Abteilung aus der Datenbank ist ein Fehler aufgetreten.</p></div>";
               }
             }
             if (!empty($_GET['searchfor'])){
               // Haben wir eine Suche? Dann muss das Suchwort auch bei URLs übergeben werden, daher bauen wir einen entsprechenden String, der das Suchwort enthält
               if (strlen($_GET['searchfor']) > 2){
                $search4url = "&amp;searchfor=".urlencode($_GET['searchfor']);
               }else{
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
             // Das gleiche Prinzip für die Sortierung. Wird unten der String überschrieben, kann die Sortierung beim Seitenzahlwechsel erhalten bleiben
             $order4url = "";
             ?>
             <table>
              <!-- Richtwerte für die Spaltenbreite -->
              <colgroup>
                <col width="30%" />
                <col width="30%" />
                <col width="25%" />
                <col width="15%" />
              </colgroup>
              <thead>
                <tr>
                    <th>Name
                    <?php
                          if (isset($_GET['orderby']) && $_GET['orderby'] == "name" && $_GET['sort'] == "desc"){
                            echo "<a href=\"abteilungen.php?orderby=name&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Abteilungsname sortieren\"><img src=\"images/arrow_down.png\" alt=\"\" /></a>";
                            $order4url = "&amp;orderby=name&amp;sort=desc";
                            $sqlorder = "ORDER BY `name` DESC";
                          }elseif(isset($_GET['orderby']) && $_GET['orderby'] == "name" && $_GET['sort'] == "asc"){
                            echo "<a href=\"abteilungen.php?orderby=name&amp;sort=desc{$search4url}{$page4url}\" title=\"Absteigend nach Abteilungsname sortieren\"><img src=\"images/arrow_up.png\" alt=\"\" /></a>";
                            $order4url = "&amp;orderby=name&amp;sort=asc";
                            $sqlorder = "ORDER BY `name` ASC";
                          }else{
                            echo "<a href=\"abteilungen.php?orderby=name&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Abteilungsname sortieren\"><img src=\"images/arrow_updown.png\" alt=\"\" /></a>";
                          }
                          ?></th>
                    <th>Abteilungsleiter
                    <?php
                          if (isset($_GET['orderby']) && $_GET['orderby'] == "leiter" && $_GET['sort'] == "desc"){
                            echo "<a href=\"abteilungen.php?orderby=leiter&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Abteilungsleiter sortieren\"><img src=\"images/arrow_down.png\" alt=\"\" /></a>";
                            $order4url = "&amp;orderby=leiter&amp;sort=desc";
                            $sqlorder = "ORDER BY `nachname` DESC, `vorname` DESC";
                          }elseif(isset($_GET['orderby']) && $_GET['orderby'] == "leiter" && $_GET['sort'] == "asc"){
                            echo "<a href=\"abteilungen.php?orderby=leiter&amp;sort=desc{$search4url}{$page4url}\" title=\"Absteigend nach Abteilungsleiter sortieren\"><img src=\"images/arrow_up.png\" alt=\"\" /></a>";
                            $order4url = "&amp;orderby=leiter&amp;sort=asc";
                            $sqlorder = "ORDER BY `nachname` ASC, `vorname` ASC";
                          }else{
                            echo "<a href=\"abteilungen.php?orderby=leiter&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Abteilungsleiter sortieren\"><img src=\"images/arrow_updown.png\" alt=\"\" /></a>";
                          }
                          ?></th>
                    <th>Mitglieder
                    <?php
                          if (isset($_GET['orderby']) && $_GET['orderby'] == "mitglieder" && $_GET['sort'] == "desc"){
                            echo "<a href=\"abteilungen.php?orderby=mitglieder&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Mitgliederzahl sortieren\"><img src=\"images/arrow_down.png\" alt=\"\" /></a>";
                            $order4url = "&amp;orderby=mitglieder&amp;sort=desc";
                            $sqlorder = "ORDER BY `members` DESC";
                          }elseif(isset($_GET['orderby']) && $_GET['orderby'] == "mitglieder" && $_GET['sort'] == "asc"){
                            echo "<a href=\"abteilungen.php?orderby=mitglieder&amp;sort=desc{$search4url}{$page4url}\" title=\"Absteigend nach Mitgliederzahl sortieren\"><img src=\"images/arrow_up.png\" alt=\"\" /></a>";
                            $order4url = "&amp;orderby=mitglieder&amp;sort=asc";
                            $sqlorder = "ORDER BY `members` ASC";
                          }else{
                            echo "<a href=\"abteilungen.php?orderby=mitglieder&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Mitgliederzahl sortieren\"><img src=\"images/arrow_updown.png\" alt=\"\" /></a>";
                          }
                          ?></th>
                    <th>Aktionen</th>
                </tr>
              </thead>
              <tbody>
              <?php
              // Gesamtanzahl aller Orte ermitteln
              $anzahl_sql = "SELECT count(*) as count FROM `abteilungen` WHERE status = '1'";
              if (!empty($_GET['searchfor']) && strlen($_GET['searchfor']) > 2){
                $anzahl_sql .= " AND (`name` LIKE '%".mysql_real_escape_string(str_replace(" ","%",$_GET['searchfor']))."%' OR `beschreibung` LIKE '%".mysql_real_escape_string(str_replace(" ","%",$_GET['searchfor']))."%') ";
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
                    if ($anzahl['count'] < ($page+1)*$proseite){
                      // Übermittelte Seite ist zu hoch (so viele Seiten werden nicht benötigt)
                      $page = 1;
                    }else{
                      $page = intval($_GET['page']);
                    }
                  }
              $sql = "SELECT `abteilungen`.* , `mitglieder`.`vorname` , `mitglieder`.`nachname`,  COUNT(`abteilungszugehoerigkeit`.`mitglied` ) AS members
                      FROM `abteilungen`
                      LEFT JOIN `abteilungszugehoerigkeit` ON `abteilungszugehoerigkeit`.`abteilung` =  `abteilungen`.`abteilungs_id`
                      LEFT JOIN `mitglieder` ON `abteilungen`.`abteilungsleiter` = `mitglieder`.`mitglieder_id`
                      WHERE `abteilungen`.`status` = 1
                       ";
              if (!empty($_GET['searchfor']) && strlen($_GET['searchfor']) > 2){
                // Haben wir eine Suche? Dann das Suchwort in die Abfrage einbauen (eigentliche Filterung findet damit statt)
                $sql .= " AND (`name` LIKE '%".mysql_real_escape_string(str_replace(" ","%",$_GET['searchfor']))."%' OR `beschreibung` LIKE '%".mysql_real_escape_string(str_replace(" ","%",$_GET['searchfor']))."%') ";
              }
              // Ergebnisse gruppieren (ansonsten wäre jede Abteilungszugehötigkeiten ein Eintrag
              $sql .= " GROUP BY `abteilungen`.`abteilungs_id` ";
              if (isset($sqlorder)){
                // Haben wir eine manuelle Sortierung? Dann diese hier in die Abfrage einbauen
                $sql .= " ".$sqlorder.", `name` ASC, `abteilungs_id` ASC";
              }else{
                // Keine manuelle Sortierung, Standardsortierung einbauen
                $sql .= " ORDER BY `name` ASC, `abteilungs_id` ASC";
              }
              // Ergebnismenge limitieren, evtl. entsprechend der Seitenzahl bereits nur hintere Ergebnisse ausgeben
              $sql .= " LIMIT ".(abs($page-1)*$proseite).",$proseite";
              //echo "<tr><td colspan=\"6\">$sql</td></tr>";
              $abteilungen = mysql_query($sql);
              if (mysql_num_rows($abteilungen) == 0){
                echo "<tr><td colspan=\"6\" class=\"tcenter\">Es wurden keine Abteilungen gefunden!</td></tr>";
              }else{
                while ($abteilung = mysql_fetch_assoc($abteilungen)){
                  echo "<tr>
                          <td>".htmlspecialchars($abteilung['name'])."</td>
                          <td>".htmlspecialchars($abteilung['vorname']." ".$abteilung['nachname'])."</td>
                          <td>".intval($abteilung['members'])."</td>
                          <td class=\"actionrow\"><a href=\"abteilung_show.php?id=".$abteilung['abteilungs_id']."\" title=\"Abteilungsprofil anzeigen\"><img src=\"images/show.png\" alt=\"\" title=\"Anzeigen\" /></a>";
                          if ($user['rights'] >= 3){
                            if ($abteilung['abteilungsleiter'] == $user['mitglieder_id'] || $user['rights'] >= 4){
                              // Der aktuelle Nutzer ist Abteilungsleiter oder hat mindestens Rechtelevel 4 oder höher
                              echo "<a href=\"abteilung_mitglieder.php?id=".$abteilung['abteilungs_id']."\" title=\"Mitglieder der Abteilung verwalten\"><img src=\"images/group.png\" alt=\"\" title=\"Mitglieder der Abteilung verwalten\" /></a>
                              <a href=\"abteilung_edit.php?id=".$abteilung['abteilungs_id']."\" title=\"Abteilung bearbeiten\"><img src=\"images/edit.png\" alt=\"\" title=\"Bearbeiten\" /></a>";
                            }else{
                              echo "<img src=\"images/group_light.png\" alt=\"\" title=\"Nicht genug Rechte für diese Aktion\" />
                              <img src=\"images/edit_light.png\" alt=\"\" title=\"Nicht genug Rechte für diese Aktion\" />";
                            }
                          }
                        echo "</td></tr>";
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
                      <a href=\"abteilungen.php?page=".($page-1)."{$order4url}{$search4url}\" title=\"Seite zurück\" class=\"naviicon";
                      if ($page == 1){
                        // Auf der ersten Seite "zurück" Pfeil verstecken
                        echo " hidden";
                      }
                      echo "\" ><img src=\"images/arrow_left.png\" alt=\"&laquo;\" /></a> \n";
                      for ($i = 1; ($i-1)*$proseite <= $anzahl['count']; $i++)  {
                        echo "<a href=\"abteilungen.php?page={$i}{$order4url}{$search4url}\"";
                        if ($i == $page){
                          echo " class=\"currentpage\" title=\"Aktuelle Seite $i\" ";
                        }else{
                          echo " title=\"Seite $i anzeigen\" ";
                        }
                        echo ">$i</a> \n";
                      }
                      echo "
                      <a href=\"abteilungen.php?page=".($page+1)."{$order4url}{$search4url}\" title=\"Seite nach vorne\" class=\"naviicon";
                      if ($page*$proseite >= $anzahl['count']){
                        // Auf der letzten Seite "vorwärts" Pfeil verstecken
                        echo " hidden";
                      }
                      echo "\"><img src=\"images/arrow_right.png\" alt=\"&raquo;\" /></a> \n
                      </ul>
                      </div>";
                   }
                   ?>
    		<div class="clearit">&nbsp;</div>
        </div>
<?php
// HTML-Fußbereich einbinden
require_once("inc/footer.inc.php");
?>