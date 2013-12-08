<?php
// Datenbankverbindung & Kodierung einbinden
require_once("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once("inc/checkuser.inc.php");

// Titel festlegen
$title = "Mitgliederverwaltung";

// JavaScript einfügen
$appendjs = "$(\"#resetsearch\").click(function(){
$(\"#searchfor\").val(\"\");
});";
// HTML-Kopf einbinden
require_once("inc/head.inc.php");
?>
        <div id="content">
           <h2>Mitgliederverwaltung</h2>
           <form id="searchform" class="formular" accept-charset="utf-8" method="get" action="members.php">
             <p class="actionline">
                <span class="actionlinesearchform"><img src="images/magnifier.png" alt="" title="Suchen"  /> <input type="text" name="searchfor" id="searchfor" value="<?php if (!empty($_GET['searchfor'])){echo htmlspecialchars($_GET['searchfor']);}?>" class="actionlinesearch" title="Nach Name, E-Mail oder Nutzername suchen" /> <input type="submit" value="Suchen" class="actionlinesearch" /> <input type="submit" id="resetsearch" value="Zurücksetzen" class="actionlinesearch" /></span>
                <?php if ($user['rights'] >= 4){ //Mindestens Rechtelevel 4 zum Erstellen von Mitgliedern ?>
                    <span><img src="images/add.png" alt="" title="Hinzufügen/Erstellen"  /> <a href="member_add.php" title="Neues Mitgliedsprofil erstellen" >Mitglied hinzufügen</a></span>
                <?php } ?>
             </p>
             </form>
             <div class="clearit">&nbsp;</div>
             <?php
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
                    <col width="22%" />
                    <col width="22%" />
                    <col width="20%" />
                    <col width="20%" />
                    <col width="16%" />
                  </colgroup>
                  <thead>
                    <tr>
                        <th>Nachname
                          <?php
                          if (isset($_GET['orderby']) && $_GET['orderby'] == "nachname" && $_GET['sort'] == "desc"){
                            echo "<a href=\"members.php?orderby=nachname&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Nachname sortieren\"><img src=\"images/arrow_down.png\" alt=\"\" /></a>";
                            $order4url = "&amp;orderby=nachname&amp;sort=desc";
                            $sqlorder = "ORDER BY `nachname` DESC";
                          }elseif(isset($_GET['orderby']) && $_GET['orderby'] == "nachname" && $_GET['sort'] == "asc"){
                            echo "<a href=\"members.php?orderby=nachname&amp;sort=desc{$search4url}{$page4url}\" title=\"Absteigend nach Nachname sortieren\"><img src=\"images/arrow_up.png\" alt=\"\" /></a>";
                            $order4url = "&amp;orderby=nachname&amp;sort=asc";
                            $sqlorder = "ORDER BY `nachname` ASC";
                          }else{
                            echo "<a href=\"members.php?orderby=nachname&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Nachname sortieren\"><img src=\"images/arrow_updown.png\" alt=\"\" /></a>";
                          }
                          ?>
                        </th>
                        <th>Vorname
                          <?php
                          if (isset($_GET['orderby']) && $_GET['orderby'] == "vorname" && $_GET['sort'] == "desc"){
                            echo "<a href=\"members.php?orderby=vorname&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Vorname sortieren\"><img src=\"images/arrow_down.png\" alt=\"\" /></a>";
                            $order4url = "&amp;orderby=vorname&amp;sort=desc";
                            $sqlorder = "ORDER BY `vorname` DESC";
                          }elseif(isset($_GET['orderby']) && $_GET['orderby'] == "vorname" && $_GET['sort'] == "asc"){
                            echo "<a href=\"members.php?orderby=vorname&amp;sort=desc{$search4url}{$page4url}\" title=\"Absteigend nach Vorname sortieren\"><img src=\"images/arrow_up.png\" alt=\"\" /></a>";
                            $order4url = "&amp;orderby=vorname&amp;sort=asc";
                            $sqlorder = "ORDER BY `vorname` ASC";
                          }else{
                            echo "<a href=\"members.php?orderby=vorname&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Vorname sortieren\"><img src=\"images/arrow_updown.png\" alt=\"\" /></a>";
                          }
                          ?>
                        </th>
                          <th>Geburtstag
                          <?php
                          if (isset($_GET['orderby']) && $_GET['orderby'] == "geburtstag" && $_GET['sort'] == "desc"){
                            echo "<a href=\"members.php?orderby=geburtstag&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Geburtstag sortieren\"><img src=\"images/arrow_down.png\" alt=\"\" /></a>";
                            $order4url = "&amp;orderby=geburtstag&amp;sort=desc";
                            $sqlorder = "ORDER BY `geburtstag` DESC";
                          }elseif(isset($_GET['orderby']) && $_GET['orderby'] == "geburtstag" && $_GET['sort'] == "asc"){
                            echo "<a href=\"members.php?orderby=geburtstag&amp;sort=desc{$search4url}{$page4url}\" title=\"Absteigend nach Geburtstag sortieren\"><img src=\"images/arrow_up.png\" alt=\"\" /></a>";
                            $order4url = "&amp;orderby=geburtstag&amp;sort=asc";
                            $sqlorder = "ORDER BY `geburtstag` ASC";
                          }else{
                            echo "<a href=\"members.php?orderby=geburtstag&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Geburtstag sortieren\"><img src=\"images/arrow_updown.png\" alt=\"\" /></a>";
                          }
                          ?>
                        </th>
                          <th>Beitritt
                          <?php
                          if (isset($_GET['orderby']) && $_GET['orderby'] == "beitritt" && $_GET['sort'] == "desc"){
                            echo "<a href=\"members.php?orderby=beitritt&amp;sort=asc$search4url\" title=\"Aufsteigend nach Beitrittsdatum sortieren\"><img src=\"images/arrow_down.png\" alt=\"\" /></a>";
                            $order4url = "&amp;orderby=beitritt&amp;sort=desc";
                            $sqlorder = "ORDER BY `beitritt` DESC";
                          }elseif(isset($_GET['orderby']) && $_GET['orderby'] == "beitritt" && $_GET['sort'] == "asc"){
                            echo "<a href=\"members.php?orderby=beitritt&amp;sort=desc$search4url\" title=\"Absteigend nach Beitrittsdatum sortieren\"><img src=\"images/arrow_up.png\" alt=\"\" /></a>";
                            $order4url = "&amp;orderby=beitritt&amp;sort=asc";
                            $sqlorder = "ORDER BY `beitritt` ASC";
                          }else{
                            echo "<a href=\"members.php?orderby=beitritt&amp;sort=asc$search4url\" title=\"Aufsteigend nach Beitrittsdatum sortieren\"><img src=\"images/arrow_updown.png\" alt=\"\" /></a>";
                          }
                          ?>
                        </th>
                        <th>Aktionen</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php
                  // Gesamtanzahl aller Mitglieder ermitteln
                  $anzahl_sql = "SELECT count(*) as count FROM `mitglieder` WHERE status = '1'";
                  if (!empty($_GET['searchfor']) && strlen($_GET['searchfor']) > 2){
                    $anzahl_sql .= " AND (`vorname` LIKE '%".mysql_real_escape_string(str_replace(" ","%",$_GET['searchfor']))."%' OR `nachname` LIKE '%".mysql_real_escape_string(str_replace(" ","%",$_GET['searchfor']))."%' OR `email` LIKE '%".mysql_real_escape_string(str_replace(" ","%",$_GET['searchfor']))."%' OR `username` LIKE '%".mysql_real_escape_string(str_replace(" ","%",$_GET['searchfor']))."%') ";
                  }
                  if ($user['rights'] < 4){
                    // Rechtelevel geringer als 4 = Man darf nur den eigenen Eintrag + die eigenen Kinder sehen
                    $anzahl_sql .= " AND (`mitglieder_id` = ".$user['mitglieder_id']." OR `parent1` = ".$user['mitglieder_id']." OR `parent2` = ".$user['mitglieder_id'].")";
                  }
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
                  // SQL-Anfrage erstellen, um die Daten zu erhalten
                  $sql = "SELECT * FROM `mitglieder` WHERE status = 1 ";
                  if (!empty($_GET['searchfor']) && strlen($_GET['searchfor']) > 2){
                    $sql .= " AND (`vorname` LIKE '%".mysql_real_escape_string(str_replace(" ","%",$_GET['searchfor']))."%' OR `nachname` LIKE '%".mysql_real_escape_string(str_replace(" ","%",$_GET['searchfor']))."%' OR `email` LIKE '%".mysql_real_escape_string(str_replace(" ","%",$_GET['searchfor']))."%' OR `username` LIKE '%".mysql_real_escape_string(str_replace(" ","%",$_GET['searchfor']))."%') ";
                  }
                  if ($user['rights'] < 4){
                    // Rechtelevel geringer als 4 = Man darf nur den eigenen Eintrag + die eigenen Kinder sehen
                    $sql .= " AND (`mitglieder_id` = ".$user['mitglieder_id']." OR `parent1` = ".$user['mitglieder_id']." OR `parent2` = ".$user['mitglieder_id'].")";
                  }
                  if (isset($sqlorder)){
                    $sql .= $sqlorder.", `nachname` ASC, `vorname` ASC ";
                  }else{
                    $sql .= "ORDER BY `nachname` ASC, `vorname` ASC";
                  }
                  $sql .= " LIMIT ".(($page-1)*$proseite).",$proseite";
                  $members = mysql_query($sql);
                  if (mysql_num_rows($members) == 0){
                    echo "<tr><td colspan=\"5\" class=\"tcenter\">Es wurden keine Mitglieder gefunden!</td></tr>";
                  }else{
                    while ($member = mysql_fetch_assoc($members)){
                      echo "<tr>
                              <td class=\"firstcolumn\">".$member['nachname']."</td>
                              <td>".$member['vorname']."</td>
                              <td>".strftime("%d. %B %Y",strtotime($member['geburtstag']))."</td>
                              <td>".strftime("%d. %B %Y",strtotime($member['beitritt']))."</td>
                              <td class=\"actionrow\"><a href=\"member_show.php?id=".$member['mitglieder_id']."\"><img src=\"images/show.png\" alt=\"\" title=\"Anzeigen\" /></a>";
                              if ($user['rights'] >= 4 || $member['mitglieder_id'] == $user['mitglieder_id'] || ($member['parent1'] == $user['mitglieder_id'] && time()-strtotime($member['geburtstag']) < 568024668) || ($member['parent2'] == $user['mitglieder_id'] && time()-strtotime($member['geburtstag']) < 568024668)){
                                // Um folgenden Link sehen zu können, muss man entweder Rechtelevel 4 oder höher haben, das Mitglied selber oder ein Elternteil des Mitglieds sein
                                echo "<a href=\"member_edit.php?id=".$member['mitglieder_id']."\"><img src=\"images/edit.png\" alt=\"\" title=\"Profil Bearbeiten\" /></a>";
                              }else{
                                // Ist das Kind älter als 18 oder die aktuelle Person darf aus anderen Gründen nicht vom aktuellen Nutzer bearbeitet werden, einen durchsichtigen Stift ohne Link anzeigen
                                echo "<img src=\"images/edit_light.png\" alt=\"\" title=\"Nicht genug Rechte für diese Aktion\" />";
                              }
                              if ($user['rights'] >= 4){
                                if (($user['rights'] == 4 && $member['rights'] <= 4) || $user['rights'] == 5){
                                  // Aktueller Nutzer hat Rechtelevel 4 und der akutell durchlaufende Person hat 4 oder kleiner ODER AKtueller Nutzer hat Rechtelevel 5
                                  echo "<a href=\"member_systemedit.php?id=".$member['mitglieder_id']."\"><img src=\"images/gear.png\" alt=\"\" title=\"Einstellungen Bearbeiten\" /></a>";
                                }else{
                                  echo "<img src=\"images/gear_light.png\" alt=\"\" title=\"Nicht genug Rechte für diese Aktion\" />";
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
                      <a href=\"members.php?page=".($page-1)."{$order4url}{$search4url}\" title=\"Seite zurück\" class=\"naviicon";
                      if ($page == 1){
                        // Auf der ersten Seite "zurück" Pfeil verstecken
                        echo " hidden";
                      }
                      echo "\"><img src=\"images/arrow_left.png\" alt=\"&laquo;\" /></a>";
                      for ($i = 1; ($i-1)*$proseite < $anzahl['count']; $i++)  {
                        echo "<a href=\"members.php?page={$i}{$order4url}{$search4url}\"";
                        if ($i == $page){
                          echo " class=\"currentpage\" title=\"Aktuelle Seite $i\" ";
                        }else{
                          echo " title=\"Seite $i anzeigen\" ";
                        }
                        echo ">$i</a>\n";
                      }
                      echo "
                      <a href=\"members.php?page=".($page+1)."{$order4url}{$search4url}\" title=\"Seite nach vorne\" class=\"naviicon";
                      if ($page*$proseite >= $anzahl['count']){
                        // Auf der letzten Seite "vorwärts" Pfeil verstecken
                        echo " hidden ";
                      }
                      echo "\"><img src=\"images/arrow_right.png\" alt=\"&raquo;\" /></a>
                      </p>
                      </div>";
                   }
                   ?>
    		<div class="clearit">&nbsp;</div>
        </div>
<?php
// HTML-Fußbereich einbinden
require_once("inc/footer.inc.php");
?>