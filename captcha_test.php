<?php
// Datenbankverbindung & Kodierung einbinden
require_once ("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once ("inc/checkuser.inc.php");

// Titel festlegen
$title = "Veranstaltung Detailansicht";

// HTML-Kopf einbinden
require_once ("inc/head.inc.php");
?>

<?php
function randomstring($n = 0) {
	if ($n < 1) {
		$n = mt_rand(16, 32);
	}
	$a = 'abcdefghijklmnopqrstuvwxyz1029384756';
	$l = strlen($a) - 1;
	for ($i = 0; $i < $n; $i++) {
		$s .= substr($a, mt_rand(0, $l), 1);
	}
	return $s;
}

include ("blowfish.class.php");

$blowfish = new Blowfish("schluessel");

if (isset($_POST['captcha']) and isset($_POST['cipher'])) {
	$cipher = base64_decode($_POST['cipher']);
	$plain = $blowfish -> Decrypt($cipher);
	$plain = substr($plain,0,4);
	if ($_POST['captcha'] == $plain) {
		echo "eingabe ok";
	} else {
		echo "eingabe falsch";
	}
} else {
	$captcha = randomstring(4);
	$cipher = $blowfish -> Encrypt($captcha);
	$cipher = base64_encode($cipher);
	echo '
	<img src="captcha.php?cipher='.$cipher.'" alt="" title="captcha"  />
<form id="captchaform" class="formular" method="post" action="captcha_test.php" accept-charset="utf-8">
	 <input type="text" name="captcha" />
	 <input class="nodisplay" type="text" name="cipher" value = "'.$cipher.'"/>
	 <input type="submit" name="search" value="abschicken" class="actionlinesearch" />
</form>';
}
?>



<?php
// HTML-Fußbereich einbinden
require_once ("inc/footer.inc.php");
?>