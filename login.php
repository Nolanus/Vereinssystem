<?php
// Datenbankverbindung & Kodierung einbinden
require_once("inc/connect.inc.php");

// Test-Cookie setzen
setcookie("keks","funktioniert");

// HTML-Kopf einbinden
require_once("inc/head.inc.php");

if ($settings['usecaptchalogin'] == 1){
  // Captcha soll beim Login verwendet werden
    function randomstring($n = 0) {
        // Funktion zum Erstellen eines Zufallsstrings der Länge n
    	if ($n < 1) {
    		$n = mt_rand(16, 32);
    	}
    	$a = 'abcdefghijkmnopqrstuvwxyz1029384756';
    	$l = strlen($a) - 1;
        $s = "";
    	for ($i = 0; $i < $n; $i++) {
    		$s .= substr($a, mt_rand(0, $l), 1);
    	}
    	return $s;
    }

    // Blowfish Klasse einbingen
    include ("inc/blowfish.class.php");
    $blowfish = new Blowfish("schluessel");

    // 4-Zeichen langen String generieren, der auf dem Captcha angezeigt werden soll
    $captcha = randomstring(4);
    // Captcha-String verschlüsseln
    $encoded_catpcha = $blowfish -> Encrypt($captcha);
}
?>
        <div id="content">
    		<div class="boxsystem33">
    		  <div class="leftbox">
                  <h2>Anmeldung</h2>
                  <?php
                  if (isset($_GET['fail'])){
                    if ($_GET['fail'] == "captcha"){
                      echo "<div class=\"message error\"><p>Der eingegebene Anti-Spam Code war nicht korrekt.</p></div>";
                    }elseif ($_GET['fail'] == "cookie"){
                      echo "<div class=\"message warning\"><p>Ihr Browser akzeptiert keine Cookies, welche zum Login erforderlich sind.</p></div>";
                    }else{
                      echo "<div class=\"message error\"><p>Login fehlgeschlagen. Bitte überprüfen Sie die eingegebenen Anmelde-Daten!</p></div>";
                    }
                  }elseif(isset($_GET['loggedout'])){
                    echo "<div class=\"message success\"><p>Sie haben sich erfolgreich abgemeldet.</p></div>";
                  }elseif(isset($_GET['old'])){
                    echo "<div class=\"message information\"><p>Sie wurden automatisch abgemeldet, da Sie zu lange inaktiv waren oder sich Ihre IP-Adresse geändert hat.</p></div>";
                  }elseif(isset($_GET['forbidden'])){
                    echo "<div class=\"message error\"><p>Ihr Nutzeraccount wurde gesperrt oder ist noch nicht freigschaltet. Bitte wenden Sie sich an den System-Administrator.</p></div>";
                  }
                  ?>
                    <form id="loginform" class="formular" method="post" action="login_process.php" accept-charset="utf-8">
                        <p>
                            <label for="username">Nutzername/Mitgliedsnr.</label>
                            <input type="text" name="username" id="username" title="Ihr Benutzername oder Ihre Mitgliedsnummer" class="medium" />
                        </p>
                        <p>
                            <label for="psword">Passwort</label>
                            <input type="password" name="psword" id="psword" title="Ihr Passwort" class="medium"/>
                        </p>
                        <?php
                        if ($settings['usecaptchalogin'] == 1){
                        ?>
                        <p class="captchaholder"><img src="captcha.php?s=<?php echo base64_encode($encoded_catpcha);?>" alt="Anti-Spam Test" title="Anti-Spam Test"  /></p>
                        <p class="captchaline">
                            <label for="captcha">Anti-Spam Test</label>
                            <input type="text" name="captcha" id="captcha" title="Die 4 Zeichen auf dem Bild" class="medium" maxlength="4"/>
                            <input type="hidden" name="astresult" value="<?php echo md5($captcha);?>" />
                        </p>
                        <?php
                        }
                        ?>
                        <p>
                            <?php
                            if (isset($_GET['after'])){
                              // Ist eine after-URL übergeben und diese ist eine URL dieses Servers
                              echo "<input type=\"hidden\" name=\"after\" value=\"".($_GET['after'])."\" />";
                            }
                            ?>
                            <input type="submit" name="Login" value="Login" title="Einloggen" class="button medium" />
                        </p>
                    </form>
              </div>
    		  <div class="rightbox">
                  <h2>Warum Anmelden?</h2>
                  <p>Da die Daten im Vereinssystem vetraulich sind, ist es nur ausgewiesenen Mitgliedern erlaubt, diese einzusehen. <br />Nach Ihrer Anmeldung können Sie sich für Dienste bei Veranstaltungen eintragen, ihre bisherigen Arbeiten einsehen und Ihre Mitgliedschaft bearbeiten.</p>
    		  </div>
    		</div>
    		<div class="clearit">&nbsp;</div>
        </div>
<?php
// HTML-Fußbereich einbinden
require_once("inc/footer.inc.php");
?>