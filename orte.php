<?php
// Datenbankverbindung & Kodierung einbinden
require_once("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once("inc/checkuser.inc.php");

// Titel festlegen
$title = "Orts- und Anschriftsverwaltung";

// JavaScript einfügen
$appendjs = "$(\"#resetsearch\").click(function(){
$(\"#searchfor\").val(\"\");
});";

// HTML-Kopf einbinden
require_once("inc/head.inc.php");
?>
        <div id="content">
           <h2>Orts- und Anschriftsverwaltung</h2>
           <form id="searchform" class="formular" accept-charset="utf-8" method="get" action="orte.php" enctype="">
             <p class="actionline">
                <span class="actionlinesearchform"><img src="images/magnifier.png" alt="" title="Suchen"  /> <input type="text" name="searchfor" id="searchfor" value="<?php if (!empty($_GET['searchfor'])){echo htmlspecialchars($_GET['searchfor']);}?>" class="actionlinesearch" title="Nach Straße, Ort oder PLZ suchen" /> <input type="submit" value="Suchen" class="actionlinesearch" /> <input type="submit" id="resetsearch" value="Zurücksetzen" class="actionlinesearch" /></span>
                <?php if ($user['rights'] >= 4){?>
                    <span><img src="images/add.png" alt="" title="Hinzufügen/Erstellen"  /> <a href="ort_add.php" title="Neuen Ort erstellen" >Ort/Anschrift hinzufügen</a></span>
                <?php } ?>
             </p>
             </form>
             <div class="clearit">&nbsp;</div>
             <?php
             // Meldungen, die per GET übergeben wurden anzeigen (Löschen bzw. Entfernen von Orten)
             if (isset($_GET['removal']) && $_GET['removal'] == "success"){
                 echo "<div class=\"message success\"><p>Der Ort wurde erfolgreich aus dem System entfernt.</p></div>";
             }elseif(isset($_GET['deletion']) && $_GET['deletion'] == "success"){
                 echo "<div class=\"message success\"><p>Der Ort wurde erfolgreich aus der Datenbank gelöscht.</p></div>";
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
                <col width="20%" />
                <col width="15%" />
                <col width="15%" />
                <col width="15%" />
                <col width="15%" />
              </colgroup>
              <thead>
                <tr>
                    <th>Name
                    <?php
                          if (isset($_GET['orderby']) && $_GET['orderby'] == "name" && $_GET['sort'] == "desc"){
                            echo "<a href=\"orte.php?orderby=name&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Namen sortieren\"><img src=\"images/arrow_down.png\" alt=\"\" /></a>";
                            $order4url = "&amp;orderby=name&amp;sort=desc";
                            $sqlorder = "ORDER BY `name` DESC";
                          }elseif(isset($_GET['orderby']) && $_GET['orderby'] == "name" && $_GET['sort'] == "asc"){
                            echo "<a href=\"orte.php?orderby=name&amp;sort=desc{$search4url}{$page4url}\" title=\"Absteigend nach Namen sortieren\"><img src=\"images/arrow_up.png\" alt=\"\" /></a>";
                            $order4url = "&amp;orderby=name&amp;sort=asc";
                            $sqlorder = "ORDER BY `name` ASC";
                          }else{
                            echo "<a href=\"orte.php?orderby=name&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Namen sortieren\"><img src=\"images/arrow_updown.png\" alt=\"\" /></a>";
                          }
                          ?></th>
                    <th>Straße
                    <?php
                          if (isset($_GET['orderby']) && $_GET['orderby'] == "street" && $_GET['sort'] == "desc"){
                            echo "<a href=\"orte.php?orderby=street&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Straße sortieren\"><img src=\"images/arrow_down.png\" alt=\"\" /></a>";
                            $order4url = "&amp;orderby=street&amp;sort=desc";
                            $sqlorder = "ORDER BY `strasse` DESC";
                          }elseif(isset($_GET['orderby']) && $_GET['orderby'] == "street" && $_GET['sort'] == "asc"){
                            echo "<a href=\"orte.php?orderby=street&amp;sort=desc{$search4url}{$page4url}\" title=\"Absteigend nach Straße sortieren\"><img src=\"images/arrow_up.png\" alt=\"\" /></a>";
                            $order4url = "&amp;orderby=street&amp;sort=asc";
                            $sqlorder = "ORDER BY `strasse` ASC";
                          }else{
                            echo "<a href=\"orte.php?orderby=street&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Straße sortieren\"><img src=\"images/arrow_updown.png\" alt=\"\" /></a>";
                          }
                          ?></th>
                    <th>Ort
                    <?php
                          if (isset($_GET['orderby']) && $_GET['orderby'] == "ort" && $_GET['sort'] == "desc"){
                            echo "<a href=\"orte.php?orderby=ort&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Ort sortieren\"><img src=\"images/arrow_down.png\" alt=\"\" /></a>";
                            $order4url = "&amp;orderby=ort&amp;sort=desc";
                            $sqlorder = "ORDER BY `ort` DESC";
                          }elseif(isset($_GET['orderby']) && $_GET['orderby'] == "ort" && $_GET['sort'] == "asc"){
                            echo "<a href=\"orte.php?orderby=ort&amp;sort=desc{$search4url}{$page4url}\" title=\"Absteigend nach Ort sortieren\"><img src=\"images/arrow_up.png\" alt=\"\" /></a>";
                            $order4url = "&amp;orderby=ort&amp;sort=asc";
                            $sqlorder = "ORDER BY `ort` ASC";
                          }else{
                            echo "<a href=\"orte.php?orderby=ort&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Ort sortieren\"><img src=\"images/arrow_updown.png\" alt=\"\" /></a>";
                          }
                          ?></th>
                    <th>Telefonnr.
                    <?php
                          if (isset($_GET['orderby']) && $_GET['orderby'] == "telnr" && $_GET['sort'] == "desc"){
                            echo "<a href=\"orte.php?orderby=telnr&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Nachname sortieren\"><img src=\"images/arrow_down.png\" alt=\"\" /></a>";
                            $order4url = "&amp;orderby=telnr&amp;sort=desc";
                            $sqlorder = "ORDER BY `telefon` DESC";
                          }elseif(isset($_GET['orderby']) && $_GET['orderby'] == "telnr" && $_GET['sort'] == "asc"){
                            echo "<a href=\"orte.php?orderby=telnr&amp;sort=desc{$search4url}{$page4url}\" title=\"Absteigend nach Nachname sortieren\"><img src=\"images/arrow_up.png\" alt=\"\" /></a>";
                            $order4url = "&amp;orderby=telnr&amp;sort=asc";
                            $sqlorder = "ORDER BY `telefon` ASC";
                          }else{
                            echo "<a href=\"orte.php?orderby=telnr&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Nachname sortieren\"><img src=\"images/arrow_updown.png\" alt=\"\" /></a>";
                          }
                          ?></th>
                    <th>Art
                    <?php
                          if (isset($_GET['orderby']) && $_GET['orderby'] == "art" && $_GET['sort'] == "desc"){
                            echo "<a href=\"orte.php?orderby=art&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Nachname sortieren\"><img src=\"images/arrow_down.png\" alt=\"\" /></a>";
                            $order4url = "&amp;orderby=art&amp;sort=desc";
                            $sqlorder = "ORDER BY `typ` DESC";
                          }elseif(isset($_GET['orderby']) && $_GET['orderby'] == "art" && $_GET['sort'] == "asc"){
                            echo "<a href=\"orte.php?orderby=art&amp;sort=desc{$search4url}{$page4url}\" title=\"Absteigend nach Nachname sortieren\"><img src=\"images/arrow_up.png\" alt=\"\" /></a>";
                            $order4url = "&amp;orderby=art&amp;sort=asc";
                            $sqlorder = "ORDER BY `typ` ASC";
                          }else{
                            echo "<a href=\"orte.php?orderby=art&amp;sort=asc{$search4url}{$page4url}\" title=\"Aufsteigend nach Nachname sortieren\"><img src=\"images/arrow_updown.png\" alt=\"\" /></a>";
                          }
                          ?></th>
                    <th>Aktionen</th>
                </tr>
              </thead>
              <tbody>
              <?php
              // Gesamtanzahl aller Orte ermitteln
              $anzahl_sql = "SELECT count(*) as count FROM `orte` WHERE status = '1'";
              if (!empty($_GET['searchfor']) && strlen($_GET['searchfor']) > 2){
                $anzahl_sql .= " AND (`strasse` LIKE '%".mysql_real_escape_string(str_replace(" ","%",$_GET['searchfor']))."%' OR `plz` LIKE '%".mysql_real_escape_string(str_replace(" ","%",$_GET['searchfor']))."%' OR `ort` LIKE '%".mysql_real_escape_string(str_replace(" ","%",$_GET['searchfor']))."%') ";
              }
              if ($user['rights'] < 3){
                // Geringeres Rechtelevel als 3 (darf nur Veranstaltungsorte sehen)
                $anzahl_sql .= " AND (`typ` = 2 OR `orts_id` IN (".$user['anschrift'].",".$settings['vereinsanschrift']."))";
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
              $sql = "SELECT * FROM `orte` WHERE status = 1";
              if (!empty($_GET['searchfor']) && strlen($_GET['searchfor']) > 2){
                // Haben wir eine Suche? Dann das Suchwort in die Abfrage einbauen (eigentliche Filterung findet damit statt)
                $sql .= " AND (`strasse` LIKE '%".mysql_real_escape_string(str_replace(" ","%",$_GET['searchfor']))."%' OR `plz` LIKE '%".mysql_real_escape_string(str_replace(" ","%",$_GET['searchfor']))."%' OR `ort` LIKE '%".mysql_real_escape_string(str_replace(" ","%",$_GET['searchfor']))."%') ";
              }
              if ($user['rights'] < 3){
                // Geringeres Rechtelevel als 3 (darf nur Veranstaltungsorte + eigenen Ort sehen)
                $sql .= " AND (`typ` = 2 OR `orts_id` IN (".$user['anschrift'].",".$settings['vereinsanschrift']."))";
              }
              if (isset($sqlorder)){
                // Haben wir eine manuelle Sortierung? Dann diese hier in die Abfrage einbauen
                $sql .= " ".$sqlorder.", `plz` ASC, `strasse` ASC";
              }else{
                // Keine manuelle Sortierung, Standardsortierung einbauen
                $sql .= " ORDER BY `plz` ASC, `strasse` ASC";
              }
              // Ergebnismenge limitieren, evtl. entsprechend der Seitenzahl bereits nur hintere Ergebnisse ausgeben
              $sql .= " LIMIT ".(abs($page-1)*$proseite).",$proseite";
              //echo "<tr><td colspan=\"6\">$sql</td></tr>";
              $orte = mysql_query($sql);
              if (mysql_num_rows($orte) == 0){
                echo "<tr><td colspan=\"6\" class=\"tcenter\">Es wurden keine Orte oder Anschriften gefunden!</td></tr>";
              }else{
                while ($ort = mysql_fetch_assoc($orte)){
                  echo "<tr>
                          <td>";
                  if ($settings['vereinsanschrift'] == $ort['orts_id']){
                    echo "<img src=\"images/star.png\" alt=\"&#9733;\" title=\"Geschäftsstelle\" class=\"imageinline\" /> ";
                  }
                  echo $ort['name']."</td>
                          <td>".$ort['strasse']." ".$ort['hausnummer']."</td>
                          <td>".$ort['plz']." ".$ort['ort']."</td>
                          <td>".$ort['telefon']."</td>
                          <td>";
                      if ($ort['typ'] == 1){
                        echo "Wohnort";
                      }elseif ($ort['typ'] == 2){
                        echo "Veranstaltungsort";
                      }else{
                        echo "<i>Nicht festgelegt</i>";
                      }
                      echo "</td>
                      <td class=\"actionrow\"><a href=\"ort_show.php?id=".$ort['orts_id']."\" title=\"Ortsprofil anzeigen\"><img src=\"images/show.png\" alt=\"\" title=\"Anzeigen\" /></a>";
                      if ($user['rights'] >= 4 ){
                        echo "<a href=\"ort_edit.php?id=".$ort['orts_id']."\" title=\"Ort bearbeiten\"><img src=\"images/edit.png\" alt=\"\" title=\"Bearbeiten\" /></a>
                        <a href=\"ort_delete.php?id=".$ort['orts_id']."\" title=\"Ort löschen\"><img src=\"images/trash_can.png\" alt=\"\" title=\"Löschen\" /></a>";
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
                      <a href=\"orte.php?page=".($page-1)."{$order4url}{$search4url}\" title=\"Seite zurück\" class=\"naviicon";
                      if ($page == 1){
                        // Auf der ersten Seite "zurück" Pfeil verstecken
                        echo " hidden";
                      }
                      echo "\" ><img src=\"images/arrow_left.png\" alt=\"&laquo;\" /></a> \n";
                      for ($i = 1; ($i-1)*$proseite < $anzahl['count']; $i++)  {
                        echo "<a href=\"orte.php?page={$i}{$order4url}{$search4url}\"";
                        if ($i == $page){
                          echo " class=\"currentpage\" title=\"Aktuelle Seite $i\" ";
                        }else{
                          echo " title=\"Seite $i anzeigen\" ";
                        }
                        echo ">$i</a> \n";
                      }
                      echo "
                      <a href=\"orte.php?page=".($page+1)."{$order4url}{$search4url}\" title=\"Seite nach vorne\" class=\"naviicon";
                      if ($page*$proseite >= $anzahl['count']){
                        // Auf der letzten Seite "vorwärts" Pfeil verstecken
                        echo " hidden";
                      }
                      echo "\"><img src=\"images/arrow_right.png\" alt=\"&raquo;\" /></a>\n
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