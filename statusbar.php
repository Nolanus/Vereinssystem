<?php
// Header Senden
header("Content-type: image/png");
// Bild erstellen
$bild = imagecreatetruecolor(204, 30);
// Farben festlegen
$weiss = imagecolorallocate($bild, 255, 255, 255);
//$gruen = imagecolorallocate($bild, 0, 204, 0);
$gruen = imagecolorallocate($bild, 2, 167, 65);
$orange = imagecolorallocate($bild, 255, 128, 0);
//$orange = imagecolorallocate($bild, 255, 136, 0);

if(isset($_GET['value'])){
      // Orange füllen
      imagefilledrectangle($bild, 2, 2, 201, 27, $orange);
      // Es wurde ein Value übergeben; ungültige Werte filtern
      if($_GET['value'] > 0){
      	if($_GET['value'] > 100){
      		$value = 100;
      	}else{
      		$value = $_GET['value'];
      	}
        // Grünen Balkenteil einfügen
      	imagefilledrectangle($bild, 2, 2, 1+(2*$value), 27, $gruen);
      }else{
      	$value = 0;
      }
      // Prozentwert ausgeben
      ImageTTFText($bild, 10, 0, 92, 20, $weiss, "inc/ARIAL.TTF", $value." %");
}else{
    ImageTTFText($bild, 10, 0, 47, 20, $weiss, "inc/ARIAL.TTF", "Parameter falsch");
}
// Bild absenden
imagepng($bild);
?>