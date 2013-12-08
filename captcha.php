<?
header("Content-type: image/png");

$bild = imagecreatetruecolor(220, 50);

$weiss = imagecolorallocate($bild, 255, 255, 255);
$rot = imagecolorallocate($bild, 255, 0, 0);
$gruen = imagecolorallocate($bild, 0, 204, 0);
$blau = imagecolorallocate($bild, 0, 102, 204);
$orange = imagecolorallocate($bild, 255, 128, 0);

imagefilledrectangle($bild, 10, 10, 210, 20, $weiss);
imagefilledrectangle($bild, 10, 30, 210, 40, $weiss);

// Blowfish Klasse einbingen
include("inc/blowfish.class.php");
$blowfish = new Blowfish("schluessel");

// Per GET wurde der anzuzeigende Text verschlüsselt übergeben
$encoded_catpcha = base64_decode($_GET['s']);
$captcha = $blowfish -> Decrypt($encoded_catpcha);

$winkel1 = rand(-25, 25);
$winkel2 = rand(-25, 25);
$winkel3 = rand(-25, 25);
$winkel4 = rand(-25, 25);

ImageTTFText($bild, 30, $winkel1, 20, 40, $rot, "inc/ARIAL.TTF", $captcha[0]);
ImageTTFText($bild, 30, $winkel2, 60, 40, $gruen, "inc/ARIAL.TTF", $captcha[1]);
ImageTTFText($bild, 30, $winkel3, 130, 40, $blau, "inc/ARIAL.TTF", $captcha[2]);
ImageTTFText($bild, 30, $winkel4, 180, 40, $orange, "inc/ARIAL.TTF", $captcha[3]);

imagepng($bild);
?>