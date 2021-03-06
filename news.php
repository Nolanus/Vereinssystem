<?php
// Datenbankverbindung & Kodierung einbinden
require_once("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once("inc/checkuser.inc.php");

// Titel festlegen
$title = "Nachrichten und Meldungen";

// JavaScript einfügen
$appendjs = "$(\"#resetsearch\").click(function(){
$(\"#searchfor\").val(\"\");
});";

// HTML-Kopf einbinden
require_once("inc/head.inc.php");
?>
        <div id="content">
           <h2>Nachrichten und Meldungen</h2>
           <form id="searchform" class="formular" accept-charset="utf-8" method="get" action="news.php" enctype="">
             <p class="actionline">
                <span class="actionlinesearchform"><img src="images/magnifier.png" alt="" title="Suchen"  /> <input type="text" name="searchfor" id="searchfor" value="<?php if (!empty($_GET['searchfor'])){echo htmlspecialchars($_GET['searchfor']);}?>" class="actionlinesearch" title="Nach Titel oder Inhalt suchen" /> <input type="submit" value="Suchen" class="actionlinesearch" /> <input type="submit" id="resetsearch" value="Zurücksetzen" class="actionlinesearch" /></span>
                <?php if ($user['rights'] >= 2){?>
                    <span><img src="images/add.png" alt="" title="Hinzufügen/Erstellen"  /> <a href="news_add.php" title="Neuen Meldung erstellen" >Nachricht/Meldung hinzufügen</a></span>
                <?php } ?>
             </p>
             </form>
             <div class="clearit">&nbsp;</div>
             <?php
             // Meldungen, die per GET übergeben wurden anzeigen (Löschen bzw. Entfernen von Nachrichten)
             if (isset($_GET['removal']) && $_GET['removal'] == "success"){
                 echo "<div class=\"message success\"><p>Die Nachricht wurde erfolgreich aus dem System entfernt.</p></div>";
             }elseif(isset($_GET['deletion']) && $_GET['deletion'] == "success"){
                 echo "<div class=\"message success\"><p>Die Nachricht wurde erfolgreich aus der Datenbank gelöscht.</p></div>";
             }
             // Suchwort
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
             // Das gleiche Prinzip für die Sortierung. Wird unten der String überschrieben, kann die Sortierung beim Seitenzahlwechsel erhalten bleiben
             $order4url = "";
             ?>
             <table>
              <!-- Richtwerte für die Spaltenbreite -->
              <colgroup>
                <col width="20%" />
                <col width="30%" />
                <col width="15%" />
                <col width="15%" />
                <col width="15%" />
              </colgroup>
              <thead>
                <tr>
                    <th>Titel
                    <?php
                          if (isset($_GET['orderby']) && $_GET['orderby'] == "titel" && $_GET['sort'] == "desc"){
                            echo "<a href=\"news.php?orderby=titel&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Titel sortieren\"><img src=\"images/arrow_down.png\" alt=\"\" /></a>";
                            $order4url = "&amp;orderby=titel&amp;sort=desc";
                            $sqlorder = "ORDER BY `title` DESC";
                          }elseif(isset($_GET['orderby']) && $_GET['orderby'] == "titel" && $_GET['sort'] == "asc"){
                            echo "<a href=\"news.php?orderby=titel&amp;sort=desc{$search4url}{$page4url}\" title=\"Absteigend nach Titel sortieren\"><img src=\"images/arrow_up.png\" alt=\"\" /></a>";
                            $order4url = "&amp;orderby=titel&amp;sort=asc";
                            $sqlorder = "ORDER BY `title` ASC";
                          }else{
                            echo "<a href=\"news.php?orderby=titel&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Titel sortieren\"><img src=\"images/arrow_updown.png\" alt=\"\" /></a>";
                          }
                          ?></th>
                    <th>Inhalt</th>
                    <th>Autor
                    <?php
                          if (isset($_GET['orderby']) && $_GET['orderby'] == "author" && $_GET['sort'] == "desc"){
                            echo "<a href=\"news.php?orderby=author&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Autor sortieren\"><img src=\"images/arrow_down.png\" alt=\"\" /></a>";
                            $order4url = "&amp;orderby=author&amp;sort=desc";
                            $sqlorder = "ORDER BY `nachname` DESC, `vorname` DESC";
                          }elseif(isset($_GET['orderby']) && $_GET['orderby'] == "author" && $_GET['sort'] == "asc"){
                            echo "<a href=\"news.php?orderby=author&amp;sort=desc{$search4url}{$page4url}\" title=\"Absteigend nach Autor sortieren\"><img src=\"images/arrow_up.png\" alt=\"\" /></a>";
                            $order4url = "&amp;orderby=author&amp;sort=asc";
                            $sqlorder = "ORDER BY `nachname` ASC, `vorname` ASC ";
                          }else{
                            echo "<a href=\"news.php?orderby=author&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Autor sortieren\"><img src=\"images/arrow_updown.png\" alt=\"\" /></a>";
                          }
                          ?></th>
                    <th>Datum
                    <?php
                          if (isset($_GET['orderby']) && $_GET['orderby'] == "date" && $_GET['sort'] == "desc"){
                            echo "<a href=\"news.php?orderby=date&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Datum sortieren\"><img src=\"images/arrow_down.png\" alt=\"\" /></a>";
                            $order4url = "&amp;orderby=date&amp;sort=desc";
                            $sqlorder = "ORDER BY `lastchange` DESC";
                          }elseif(isset($_GET['orderby']) && $_GET['orderby'] == "date" && $_GET['sort'] == "asc"){
                            echo "<a href=\"news.php?orderby=date&amp;sort=desc{$search4url}{$page4url}\" title=\"Absteigend nach Datum sortieren\"><img src=\"images/arrow_up.png\" alt=\"\" /></a>";
                            $order4url = "&amp;orderby=date&amp;sort=asc";
                            $sqlorder = "ORDER BY `lastchange` ASC";
                          }else{
                            echo "<a href=\"news.php?orderby=date&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Datum sortieren\"><img src=\"images/arrow_updown.png\" alt=\"\" /></a>";
                          }
                          ?></th>
                    <th>Aktionen</th>
                </tr>
              </thead>
              <tbody>
              <?php
              // Gesamtanzahl aller Nachrichten ermitteln
              $anzahl_sql = "SELECT count(*) as count FROM `news` WHERE status = '1'";
              if (!empty($_GET['searchfor']) && strlen($_GET['searchfor']) > 2){
                $anzahl_sql .= " AND (`title` LIKE '%".mysql_real_escape_string(str_replace(" ","%",$_GET['searchfor']))."%' OR `content` LIKE '%".mysql_real_escape_string(str_replace(" ","%",$_GET['searchfor']))."%') ";
              }
              // Nur Beiträge, die für das eigene Rechtelevel oder niedriger bestimmt sind
              $anzahl_sql .= " AND (`minright` <= '".$user['rights']."' OR `author` = '".$user['mitglieder_id']."')";
              $anzahl_result = mysql_query($anzahl_sql);
              $anzahl = mysql_fetch_assoc($anzahl_result);
              // Festlegen, wie viele Einträge pro Seite angezeigt werden sollen
              $proseite = 10;
              if (!isset($_GET['page'])){
                // Keine Seite übermittelt, dann erste Seite anzeigen
                $page = 1;
              }elseif($anzahl['count'] > $proseite){
                $page = abs(intval($_GET['page'])); //Absolutwert, um negative Seitenzahlen zu verhindern
                // Seite übermittelt und mehrere Seiten werden auch benötigt
                if (($page-1)*$proseite > $anzahl['count']){
                  // Übermittelte Seite ist zu hoch (so viele Seiten werden doch nicht benötigt)
                  $page = 1;
                }
              }
              $sql = "SELECT * FROM `news` LEFT JOIN `mitglieder` ON `news`.`author` = `mitglieder`.`mitglieder_id` WHERE `news`.`status` = 1";
              if (!empty($_GET['searchfor']) && strlen($_GET['searchfor']) > 2){
                // Haben wir eine Suche? Dann das Suchwort in die Abfrage einbauen (eigentliche Filterung findet damit statt)
                $sql .= " AND (`title` LIKE '%".mysql_real_escape_string(str_replace(" ","%",$_GET['searchfor']))."%' OR `content` LIKE '%".mysql_real_escape_string(str_replace(" ","%",$_GET['searchfor']))."%') ";
              }
              $sql .= " AND (`minright` <= '".$user['rights']."' OR `author` = '".$user['mitglieder_id']."')";

              if (isset($sqlorder)){
                // Haben wir eine manuelle Sortierung? Dann diese hier in die Abfrage einbauen
                $sql .= " ".$sqlorder.", `lastchange` DESC, `created` DESC";
              }else{
                // Keine manuelle Sortierung, Standardsortierung einbauen
                $sql .= " ORDER BY `lastchange` DESC, `created` DESC";
              }
              // Ergebnismenge limitieren, evtl. entsprechend der Seitenzahl bereits nur hintere Ergebnisse ausgeben
              $sql .= " LIMIT ".(abs($page-1)*$proseite).",$proseite";
              echo "<tr><td colspan=\"5\">$sql</td></tr>";
              $nachrichten = mysql_query($sql);
              if (mysql_num_rows($nachrichten) == 0){
                echo "<tr><td colspan=\"5\" class=\"tcenter\">Es wurden keine Nachrichten gefunden!</td></tr>";
              }else{
                while ($nachricht = mysql_fetch_assoc($nachrichten)){
                  echo "<tr>
                          <td><a href=\"news_show.php?id=".$nachricht['news_id']."\" title=\"Nachricht lesen\">".htmlspecialchars($nachricht['title'])."</a></td>
                          <td>".substr(strip_tags($nachricht['content']),0,75)."...</td>
                          <td>".$nachricht['vorname']." ".$nachricht['nachname']."</td>
                          <td>".strftime("%d. %B %Y, %H:%M",$nachricht['lastchange'])."</td>
                      <td class=\"actionrow\"><a href=\"news_show.php?id=".$nachricht['news_id']."\" title=\"Nachricht anzeigen\"><img src=\"images/show.png\" alt=\"\" title=\"Anzeigen\" /></a>";
                      if ($user['rights'] >= 2 ){
                        if ($nachricht['author'] == $user['mitglieder_id'] || $user['rights'] >= 4){
                        echo "<a href=\"news_edit.php?id=".$nachricht['news_id']."\" title=\"Nachricht bearbeiten\"><img src=\"images/edit.png\" alt=\"\" title=\"Bearbeiten\" /></a>
                              <a href=\"news_delete.php?id=".$nachricht['news_id']."\" title=\"Nachricht löschen\"><img src=\"images/trash_can.png\" alt=\"\" title=\"Löschen\" /></a>";
                        }else{
                          echo "<img src=\"images/edit_light.png\" alt=\"\" title=\"Nicht genug Rechte für diese Aktion\" />
                                <img src=\"images/trash_can_light.png\" alt=\"\" title=\"Nicht genug Rechte für diese Aktion\" />";
                        }
                      }
                      echo "</td>
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
                      <a href=\"news.php?page=".($page-1)."{$order4url}{$search4url}\" title=\"Seite zurück\" class=\"naviicon";
                      if ($page == 1){
                        // Auf der ersten Seite "zurück" Pfeil verstecken
                        echo " hidden";
                      }
                      echo "\" ><img src=\"images/arrow_left.png\" alt=\"&laquo;\" /></a> \n";
                      for ($i = 1; ($i-1)*$proseite <= $anzahl['count']; $i++)  {
                        echo "<a href=\"news.php?page={$i}{$order4url}{$search4url}\"";
                        if ($i == $page){
                          echo " class=\"currentpage\" title=\"Aktuelle Seite $i\" ";
                        }else{
                          echo " title=\"Seite $i anzeigen\" ";
                        }
                        echo ">$i</a> \n";
                      }
                      echo "
                      <a href=\"news.php?page=".($page+1)."{$order4url}{$search4url}\" title=\"Seite nach vorne\" class=\"naviicon";
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