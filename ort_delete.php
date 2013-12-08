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
$title = "Ort / Anschrift löschen";

// HTML-Kopf einbinden
require_once("inc/head.inc.php");
?>
        <div id="content">
        <?php
        if (isset($_GET['id'])){
            $sql = "SELECT * FROM `orte` WHERE `orts_id` = ".intval($_GET['id'])." AND `status` = '1' ";
            $ort_result = mysql_query($sql);
            //echo mysql_error();
            if (mysql_num_rows($ort_result) == 1){
                $ort = mysql_fetch_assoc($ort_result);
                ?>
            		<div class="boxsystem33">
            		  <div class="leftbox">
                          <h2>Ort / Anschrift löschen</h2>
                          <p>Entfernen Sie einen Ort aus dem System. Beachten Sie, dass der Ort nicht in Verwendung sein darf, damit er gelöscht werden kann.</p>
                          <?php
                          if (isset($_GET['fail'])){
                            if ($_GET['fail'] == "inuse"){
                              echo "<div class=\"message error\"><p>Der Ort kann nicht gelöscht werden, da er momentan als";
                              if ($_GET['m'] == 1){
                                echo " Wohnort für 1 Mitglied";
                              }elseif ($_GET['m'] > 1){
                                echo " Wohnort für ".intval($_GET['m'])." Mitglieder";
                              }
                              if ($_GET['m'] > 0 && $_GET['v'] > 0){
                                echo " und als";
                              }
                              if ($_GET['v'] == 1){
                                echo " Veranstaltungsort für 1 Veranstaltung";
                              }elseif ($_GET['v'] > 1){
                                echo " Veranstaltungsort für ".intval($_GET['v'])." Veranstaltungen";
                              }
                              echo " verwendet wird.</p></div>";
                            }
                          }elseif(isset($_GET['removal']) && $_GET['removal'] == "fail"){
                            echo "<div class=\"message error\"><p>Beim Entfernen des Ortes ist ein Fehler aufgetreten.</p></div>";
                          }elseif(isset($_GET['deletion']) && $_GET['deletion'] == "fail"){
                            echo "<div class=\"message error\"><p>Beim Löschen des Ortes aus der Datenbank ist ein Fehler aufgetreten.</p></div>";
                          }
                          ?>
                          <form id="deleteform" class="formular" method="post" action="ort_process.php" accept-charset="utf-8">
                              <p>
                                  Möchten Sie folgenden Ort wirklich löschen?
                              </p>
                              <p class="textcenter">
                                  <?php
                                  if (empty($ort['strasse'])){
                                    echo "<i>Kein Straßenname</i>";
                                  }else{
                                    echo $ort['strasse'];
                                  }
                                  echo " ";
                                  if (empty($ort['hausnummer'])){
                                    echo "<i>Keine Hausnummer</i>";
                                  }else{
                                    echo $ort['hausnummer'];
                                  }
                                  echo "<br />";
                                  if (empty($ort['plz'])){
                                    echo "<i>Keine PLZ</i>";
                                  }else{
                                    echo $ort['plz'];
                                  }
                                  echo " ";
                                  if (empty($ort['ort'])){
                                    echo "<i>Kein Ortsname</i>";
                                  }else{
                                    echo $ort['ort'];
                                  }
                                  echo "<br />";
                                  if (empty($ort['telefon']) || $ort['telefon'] == "/"){
                                    echo "<i>Keine Telefonnummer</i>";
                                  }else{
                                    echo $ort['telefon'];
                                  }
                                  ?>
                              </p>
                              <p>
                                  <label for="sure">Ort löschen</label>
                                  <select name="sure" size="1" class="medium" id="sure">
                                    <option value="1">Nein</option>
                                    <option value="2">Ja</option>
                                  </select>
                              </p>
                              <?php
                              if ($user['rights'] >= 5){
                                // Ist der aktuelle Nutzer ein Administrator
                              ?>
                                  <p>
                                      <label for="dbdelete">Aus der DB löschen</label>
                                      <select name="dbdelete" size="1" class="medium" id="dbdelete">
                                        <option value="1">Nein</option>
                                        <option value="2">Ja</option>
                                      </select>
                                  </p>
                              <?php
                              }
                              ?>
                              <p>
                                  <input type="hidden" name="orts_id" value="<?php echo $ort['orts_id'];?>" />
                                  <input type="submit" name="deleteort" id="deleteort" value="Löschen" title="Löschen" class="button medium" />
                              </p>
                          </form>
                      </div>
            		  <div class="rightbox">
                        <?php include("inc/action_leiste_orte.inc.php"); ?>
            		  </div>
            		</div>
            <?php }else{ ?>
                    <h2>Ort löschen</h2>
                    <div class="error"><p>Es wurde kein Ort mit dieser ID gefunden!<br /><a href="orte.php" title="Übersicht aller Orte anzeigen">Zur Orts- und Anschriftsverwaltung</a></p></div>

        <?php     }
        }else{ ?>
        <h2>Ort löschen</h2>
        <div class="error"><p>Es wurde keine Orts-ID übergeben!<br /><a href="orte.php" title="Übersicht aller Orte anzeigen">Zur Orts- und Anschriftsverwaltung</a></p></div>

            <?php } ?>
    		<div class="clearit">&nbsp;</div>
        </div>
<?php
// HTML-Fußbereich einbinden
require_once("inc/footer.inc.php");
?>