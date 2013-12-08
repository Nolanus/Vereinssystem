<?php
// Datenbankverbindung & Kodierung einbinden
require_once("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once("inc/checkuser.inc.php");

// Rechte Check
if ($user['rights'] < 4){
  // Rechtelevel geringer als 4 = Kein Zugang
  if (isset($_SERVER["HTTP_REFERER"])){
    $referer = "?before=".base64_encode($_SERVER["HTTP_REFERER"]);
  }else{
    $referer = "";
  }
  header("Location: norights.php$referer");
  exit();
}

// Titel festlegen
$title = "Mitglieds-Systemeinstellungen bearbeiten";

// JavaScript einfügen
$appendjs = "$(\"#strasse,#hausnummer,#plz,#ort,#telnr,#telvorwahl\").keydown(function(){
    $(\"#locationchangep\").slideDown();
});
$(\"#mitgliedschaft\").change(function(){
if ($(this).val() == 1){
    $(\"#fammitgliedschaftp\").slideDown();
}else{
    $(\"#fammitgliedschaftp\").slideUp();
}
});
$(\"#abrechnung\").change(function(){
if ($(this).val() == 0){
    $(\"#kontoverb\").slideDown();
}else{
    $(\"#kontoverb\").slideUp();
}
});";
// HTML-Kopf einbinden
require_once("inc/head.inc.php");
?>
        <div id="content">
        <?php
        if (isset($_GET['id'])){
            $sql = "SELECT * FROM `mitglieder` WHERE `mitglieder`.`mitglieder_id` = ".intval($_GET['id'])." AND `mitglieder`.`status` = '1' ";
            $member_result = mysql_query($sql);
            echo mysql_error();
            if (mysql_num_rows($member_result) == 1){
                $member = mysql_fetch_assoc($member_result);
                if ($member['rights'] == 5 && $user['rights'] == 4){
                  echo "<h2>Mitglieds-Systemeinstellungen bearbeiten</h2>
                    <div class=\"message error\">Für diese Aktion haben Sie nicht die erforderlichen Rechte. Nur andere Administratoren können die Systemeinstellungen von Administrator-Accounts bearbeiten.
                      Wenden Sie sich an den Systemadministrator, wenn Sie der Meinung sind, diese Funktion zu benötigen.<br /><a href=\"members.php\" title=\"Übersicht aller Mitglieder anzeigen\">Zur Mitgliederverwaltung</a></p></div>";
                }else{
                ?>
            		<div class="boxsystem33">
            		  <div class="leftbox">
                          <h2>Mitglieds-Systemeinstellungen bearbeiten</h2>
                          <p>Passen Sie die Systemwerte des Mitglieds an, etwa wenn das Passwort vergessen wurde.</p>
                          <?php
                          if (isset($_GET['save'])){
                            if ($_GET['save'] == "success"){
                                echo "<div class=\"message success\"><p>Die Änderungen wurden erfolgreich gespeichert.";
                                if ($_GET['pword'] == true){
                                  echo "<br />Das Passwort wurde erfolgreich geändert.";
                                }
                                echo "</p></div>";
                            }elseif($_GET['save'] == "fail"){
                              if ($_GET['why'] == "data"){
                                echo "<div class=\"message error\"><p>Die Änderungen wurden aufgrund ungültiger Eingaben nicht übernommen.</p><ul>";
                                $fehlerarray = json_decode(base64_decode($_GET['errors']),true);
                                foreach ($fehlerarray as &$fehler) {
                                  echo "<li>$fehler</li>\n";
                                }
                                echo "</ul></div>";
                              }elseif($_GET['why'] == 1062){
                                // MySQL Error für doppelten Tabelleneintrag
                                echo "<div class=\"message error\"><p>Es ist bereits ein Mitglied mit diesem Nutzernamen vorhanden. Bitte verwenden einen alternativen Nutzernamen oder die Mitgliedsnummer zum Einloggen.</p></div>";
                              }else{
                                echo "<div class=\"message error\"><p>Beim Versuch, die Änderungen zu speichern, ist leider ein Fehler aufgetreten. Bitte versuchen Sie es erneut oder wenden sich an den Systemadministrator.</p></div>";
                              }
                              if (empty($_GET['data']) == false){
                                // Vorgaben für die Felder wurden übergeben; diese Werte in das Array $prefill schreiben
                                $prefill = json_decode(base64_decode($_GET['data']), true);
                                // Folgendes wendet stripslashes auf jedes Element des Arrays an
                                foreach ($prefill as $key=>$value) {
                                    $prefill[$key] = stripslashes($value);
                                }
                              }
                            }
                          }
                          ?>
                          <form id="editform" class="formular" method="post" action="member_process.php" accept-charset="utf-8">
                              <p>
                                  <label for="usernamechange">Username</label>
                                  <input type="text" autocomplete="off" value="<?php
                                  if (isset($prefill['usernamechange'])){
                                    echo htmlspecialchars($prefill['usernamechange']);
                                  }else{
                                    echo htmlspecialchars($member['username']);
                                  }?>" name="usernamechange" id="usernamechange" title="Nutzername" class="medium" />
                              </p>
                              <p>
                                  <label for="passwortchange">Passwort</label>
                                  <input type="password" value="" name="passwortchange" id="passwortchange" autocomplete="off" title="Passwort" class="medium" />
                              </p>
                              <p>
                                  <label for="rights">Rechtelevel</label>
                                  <select name="rights" size="1" class="medium" id="rights">
                                    <?php
                                    echo "<option value=\"0\"";
                                    if ((!isset($prefill['rights']) && $member['rights'] == 0) || (isset($prefill['rights']) && $prefill['rights'] == 0)){
                                      echo " selected=\"selected\" ";
                                    }
                                    echo ">Gesperrt</option>";
                                    echo "<option value=\"1\"";
                                    if ((!isset($prefill['rights']) && $member['rights'] == 1) || (isset($prefill['rights']) && $prefill['rights'] == 1)){
                                      echo " selected=\"selected\" ";
                                    }
                                    echo ">Normaler Nutzer</option>";
                                    echo "<option value=\"2\"";
                                    if ((!isset($prefill['rights']) && $member['rights'] == 2) || (isset($prefill['rights']) && $prefill['rights'] == 2)){
                                      echo " selected=\"selected\" ";
                                    }
                                    echo ">Moderator</option>";
                                    echo "<option value=\"3\"";
                                    if ((!isset($prefill['rights']) && $member['rights'] == 3) || (isset($prefill['rights']) && $prefill['rights'] == 3)){
                                      echo " selected=\"selected\" ";
                                    }
                                    echo ">Abteilungsleiter</option>";
                                    echo "<option value=\"4\"";
                                    if ((!isset($prefill['rights']) && $member['rights'] == 4) || (isset($prefill['rights']) && $prefill['rights'] == 4)){
                                      echo " selected=\"selected\" ";
                                    }
                                    echo ">Vereinsvorstand</option>";
                                    if ($user['rights'] == 5){
                                      // Nur Admins die Option anzeigen, andere ebenfalls zu Admins zu machen
                                      echo "<option value=\"5\"";
                                      if ((!isset($prefill['rights']) && $member['rights'] == 5) || (isset($prefill['rights']) && $prefill['rights'] == 5)){
                                        echo " selected=\"selected\" ";
                                      }
                                      echo ">Administrator</option>";
                                    }
                                    ?>
                                  </select>
                              </p>
                              <p>
                                  <label for="notizen">Notizen</label>
                                  <textarea name="notizen" id="notizen" class="xlarge" rows="5" cols="20"><?php
                                  if (isset($prefill['notizen'])){
                                    echo ($prefill['notizen']);
                                  }else{
                                    echo ($member['notizen']);
                                  }
                                  ?></textarea>
                              </p>
                              <p>
                                  <input type="hidden" name="mitglieder_id" value="<?php echo $member['mitglieder_id'];?>" />
                                  <input type="submit" name="savesystemmember" value="Speichern" title="Speichern" class="button medium" />
                              </p>
                          </form>
                      </div>
            		  <div class="rightbox">
                      <?php
                        include("inc/action_leiste_members.inc.php");
                      ?>
                      </div>
            		</div>
            <?php }
             }else{ ?>
                    <h2>Mitglieds-Systemeinstellungen bearbeiten</h2>
                    <div class="message error"><p>Es wurde kein Mitglied mit dieser ID gefunden!<br /><a href="members.php" title="Übersicht aller Mitglieder anzeigen">Zur Mitgliederverwaltung</a></p></div>

        <?php     }
        }else{ ?>
        <h2>Mitglieds-Systemeinstellungen bearbeiten</h2>
        <div class="message error"><p>Es wurde keine Mitglieder-ID übergeben!<br /><a href="members.php" title="Übersicht aller Mitglieder anzeigen">Zur Mitgliederverwaltung</a></p></div>

            <?php } ?>
    		<div class="clearit">&nbsp;</div>
        </div>
<?php
// HTML-Fußbereich einbinden
require_once("inc/footer.inc.php");
?>