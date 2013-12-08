<?php
// Datenbankverbindung & Kodierung einbinden
require_once("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once("inc/checkuser.inc.php");

// Titel festlegen
$title = "Nachricht / Meldung anzeigen";

// HTML-Kopf einbinden
require_once("inc/head.inc.php");
?>
        <div id="content">
        <?php
        if (isset($_GET['id'])){
            $sql = "SELECT * FROM `news` LEFT JOIN `mitglieder` ON `news`.`author` = `mitglieder`.`mitglieder_id` WHERE `news_id` = ".intval($_GET['id'])." AND `news`.`status` = '1' ";
            $nachrichten_result = mysql_query($sql);
            //echo mysql_error();
            if (mysql_num_rows($nachrichten_result) == 1){
                $nachricht = mysql_fetch_assoc($nachrichten_result);
                // Hat der Nutzer die erforderlichen Rechte?
                if ($user['rights'] >= $nachricht['minright'] || $nachricht['author'] == $user['mitglieder_id']){
                  // Er hat das nötige Rechtelevel (gleich oder größer minright) oder ist der Autor der Nachricht
                ?>
            		<div class="boxsystem33">
            		  <div class="leftbox">
                          <h2><?php echo $nachricht['title'];?></h2>
                          <?php
                          if (isset($_GET['created'])){
                              echo "<div class=\"message success\"><p>Die neue Nachricht wurde erfolgreich erstellt.</p></div>";
                          }
                          ?>
                          <?php
                          echo nl2br($nachricht['content']);
                          echo "<div class=\"afternews\">
                          <p>Diese Nachricht wurde am ".strftime("%A, %d. %B %Y, um %H:%M Uhr",$nachricht['created'])." von ".htmlspecialchars($nachricht['vorname']." ".$nachricht['nachname'])." geschrieben";
                          if ($nachricht['lastchange']-60 > ($nachricht['created'])){
                            // Wurde die letzte Änderung mehr als 60 Sekunden nach dem erstellen gemacht
                            echo ", zuletzt am ".strftime("%A, %d. %B %Y, um %H:%M Uhr",$nachricht['lastchange'])." bearbeitet";
                          }
                          echo " und ist für ";
                          switch ($nachricht['minright']) {
                            case 2:
                                echo " Moderatoren, Abteilungsleiter, Vereinsvorstände und Administratoren";
                            break;
                            case 3:
                                echo " Abteilungsleiter, Vereinsvorstände und Administratoren";
                            break;
                            case 4:
                                echo " Vereinsvorstände und Administratoren";
                            break;
                            case 5:
                                echo " Administratoren alleine";
                            break;
                            default:
                                echo " jedes Mitglied";
                          }
                          echo " sichtbar.</p></div>
                          <p><img src=\"images/arrow_left.png\" alt=\"\" class=\"imageinline\" /> <a href=\"news.php\" title=\"Zurück zur Nachrichtenübersicht\">Zurück zur Übersicht</a></p>";
                          ?>

                      </div>
            		  <div class="rightbox">
                        <?php include("inc/action_leiste_news.inc.php"); ?>
            		  </div>
            		</div>
            <?php
                }else{
                  echo "<div class=\"message error\"><p>Für diese Aktion haben Sie nicht die erforderlichen Rechte.<br />
                  Wenden Sie sich an den Systemadministrator, wenn Sie der Meinung sind, diese Funktion zu benötigen.<br /><a href=\"news.php\" title=\"Übersicht aller Nachrichten anzeigen\">Zur Nachrichtenübersicht</a></p></div>";
                }
            }else{ ?>
                    <h2>Nachricht / Meldung bearbeiten</h2>
                    <div class="message error"><p>Es wurde keine Nachricht mit dieser ID gefunden!<br /><a href="news.php" title="Übersicht aller Nachrichten anzeigen">Zur Nachrichtenübersicht</a></p></div>

        <?php     }
        }else{ ?>
        <h2>Nachricht / Meldung bearbeiten</h2>
        <div class="message error"><p>Es wurde keine Nachrichten-ID übergeben!<br /><a href="news.php" title="Übersicht aller Orte anzeigen">Zur Nachrichtenübersicht</a></p></div>

            <?php } ?>
    		<div class="clearit">&nbsp;</div>
        </div>
<?php
// HTML-Fußbereich einbinden
require_once("inc/footer.inc.php");
?>