<?php
// Datenbankverbindung & Kodierung einbinden
require_once ("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once ("inc/checkuser.inc.php");

// Titel festlegen
$title = "Veranstaltung löschen";

// HTML-Kopf einbinden
require_once ("inc/head.inc.php");
?>
        <div id="content">
        <?php
        if (isset($_GET['id'])){
            $sql = "SELECT * FROM `veranstaltungen` WHERE `veranstaltungs_id` = ".intval($_GET['id']);
            $veranstaltung_result = mysql_query($sql);
            //echo mysql_error();
            if (mysql_num_rows($veranstaltung_result) == 1){
                $veranstaltung = mysql_fetch_assoc($veranstaltung_result);
			if (date("d.n.Y", $veranstaltung["startzeit"]) == date("d.n.Y", $veranstaltung["endzeit"])) {
				$termin = "Am " . date("d.n.Y", $veranstaltung["startzeit"]) . "<br />Von " . date("H:i", $veranstaltung["startzeit"]) . " bis " . date("H:i", $veranstaltung["endzeit"]);
			} else {
				$termin = date("d.n.Y", $veranstaltung["startzeit"]) . ", " . date("H:i", $veranstaltung["startzeit"]) . " bis <br />" . date("d.n.Y", $veranstaltung["endzeit"]) . ", " . date("H:i", $veranstaltung["endzeit"]);
			}
                ?>
            		<div class="boxsystem33">
            		  <div class="leftbox">
                          <h2>Veranstaltung löschen</h2>
                          <form id="deleteform" class="formular" method="post" action="veranstaltungen_process.php" accept-charset="utf-8">
                              <p>
                                  Möchten Sie folgende Veranstaltung wirklich löschen?
                              </p>
                              <p class="textcenter">
                                  <?php
								if (empty($veranstaltung['veranstaltungsname'])) {
									echo "<i>Kein Veranstaltungsname</i>";
								} else {
									echo $veranstaltung['veranstaltungsname'];
								}
								echo "<br />";
								if (empty($termin)) {
									echo "<i>Kein Termin</i>";
								} else {
									echo $termin;
								}
                                  ?>
                              </p>
                              <p>
                                  <label for="sure">Veranstaltung löschen</label>
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
                                  <input type="hidden" name="veranstaltungs_id" value="<?php echo $veranstaltung['veranstaltungs_id']; ?>" />
                                  <input type="submit" name="deleteevent" id="deleteort" value="Löschen" title="Löschen" class="button medium" />
                              </p>
                          </form>
                      </div>
    		  <div class="rightbox">
              <?php
			include ("inc/action_leiste_events.inc.php");
              ?>
    		  </div>
            		</div>
            <?php }else{ ?>
                    <h2>Veranstaltung löschen</h2>
                    <p class="error">Es wurde keine Veranstaltung mit dieser ID gefunden!<br /><a href="veranstaltungen.php" title="Übersicht aller Veranstaltungen anzeigen">Zur Veranstaltungsverwaltung</a></p>

        <?php     }
	}else{
 ?>
        <h2>Veranstaltung löschen</h2>
        <p class="error">Es wurde keine Veranstaltungs-ID übergeben!<br /><a href="veranstaltungen.php" title="Übersicht aller Veranstaltungen anzeigen">Zur Veranstaltungsverwaltung</a></p>

            <?php } ?>
    		<div class="clearit">&nbsp;</div>
        </div>
<?php
// HTML-Fußbereich einbinden
require_once ("inc/footer.inc.php");
?>