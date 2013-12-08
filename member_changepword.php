<?php
// Datenbankverbindung & Kodierung einbinden
require_once("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once("inc/checkuser.inc.php");

// Titel festlegen
$title = "Passwort ändern";

// JavaScript einfügen
$appendjs = "";
// HTML-Kopf einbinden
require_once("inc/head.inc.php");
?>
        <div id="content">
            		<div class="boxsystem33">
            		  <div class="leftbox">
                          <h2>Passwort ändern</h2>
                          <p>Passen Sie Ihr Nutzerpasswort an.</p>
                          <?php
                          // Die aktuelle Person ist der aktuelle Nutzer. Daher die Variable $member mit den Daten der aktuellen Nutzers füllen, wie als wenn $_GET['id'] die Mitglieder-ID des aktuellen Users übergeben würde
                          $member = $user;

                          if (isset($_GET['save'])){
                            if ($_GET['save'] == "success"){
                                echo "<div class=\"message success\"><p>Das Password wurde erfolgreich geändert. Bitte melden Sie sich nun erneut an.</p></div>";
                                // Erfolgreich Passwort geändert. Session beenden um Login zu erzwingen
                                session_destroy();
                            }elseif($_GET['save'] == "fail"){
                              if ($_GET['why'] == "data"){
                                echo "<div class=\"message error\"><p>Das neue Passwort wurden aufgrund ungültiger Eingaben nicht übernommen.</p><ul>";
                                $fehlerarray = json_decode(base64_decode($_GET['errors']),true);
                                foreach ($fehlerarray as &$fehler) {
                                  echo "<li>$fehler</li>\n";
                                }
                                echo "</ul></div>";
                              }else{
                                echo "<div class=\"message error\"><p>Beim Versuch, das neue Passwort zu speichern, ist leider ein Fehler aufgetreten. Bitte versuchen Sie es erneut oder wenden sich an den Systemadministrator.</p></div>";
                              }
                            }
                          }
                          ?>
                          <form id="editform" class="formular" method="post" action="member_process.php" accept-charset="utf-8">
                              <p>
                                  <label for="oldpword">Bisheriges Passwort</label>
                                  <input type="password" autocomplete="off" value="" name="oldpword" id="oldpword" title="Ihr bisheriges Passwort" class="medium" />
                              </p>
                              <p>
                                  <label for="newpword">Neues Passwort</label>
                                  <input type="password" value="" name="newpword" id="newpword" autocomplete="off" title="Ihr neues Passwort" class="medium" />
                              </p>
                              <p>
                                  <label for="newpword2">Neues Passwort Wdhl.</label>
                                  <input type="password" value="" name="newpword2" id="newpword2" autocomplete="off" title="Neues Passwort wiederholen" class="medium" />
                              </p>
                              <p>
                                  <input type="submit" name="changemypword" value="Passwort ändern" title="Passwort ändern" class="button medium" />
                              </p>
                          </form>
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