<?php
// Datenbankverbindung & Kodierung einbinden
require_once("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once("inc/checkuser.inc.php");

// Titel festlegen
$title = "Permission Denied";

// HTML-Kopf einbinden
require_once("inc/head.inc.php");
?>
        <div id="content">
                  <h2>Permission Denied</h2>
                  <div class="message error"><p>Für diese Aktion haben Sie nicht die erforderlichen Rechte. Bitte wenden Sie sich an den Systemadministrator, wenn Sie der Meinung sind, diese Funktion zu benötigen.</p></div>
                  <p class="tcenter"><img src="images/close.png" alt="" /></p>
                  <?php
                  if (isset($_GET['before']) && strpos(base64_decode($_GET['before']), $_SERVER["SERVER_NAME"]) !== false && strpos(base64_decode($_GET['before']),"norights.php") === false){
                    // War die Referer Seite, über die man auf die verbotene kam von diesem Server und sie war nicht die norights.php, dann einen Zurücklink anbieten
                    echo "<p><img src=\"images/arrow_left.png\" alt=\"\" class=\"imageinline\" /> <a href=\"".base64_decode($_GET['before'])."\" title=\"Zurück zur vorherigen Seite\">Zurück</a></p>";
                  }
                  ?>
    		<div class="clearit">&nbsp;</div>
        </div>
<?php
// HTML-Fußbereich einbinden
require_once("inc/footer.inc.php");
?>