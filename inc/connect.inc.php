<?php
// Zum Testbetrieb: Alle Fehler ausgeben
//error_reporting(E_ALL);
// Zum Nutzbetrieb: Keine Fehler ausgeben
error_reporting(0);

// Header manuell senden, um UTF-8-Kodierung zu erreichen, falls der Server dies nicht automatisch als Standard hat
@header("Content-Type: text/html; charset=utf-8");

// Stellt Verbindung mit dem Datenbankserver her und liest seitenweit geltende, allgemeine Einstellungen aus
if (!@mysql_connect("localhost","root","root")){
  // Verbindung ist fehlgeschlagen, Ausführung abbrechen
  die('Keine Verbindung zu Datenbank möglich: ' . mysql_error());
}

// PHP mitteilen, dass die Verbindung mit der Datenbank UTF-8-kodiert ist
mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET utf8");

// Weitere Kodierungseinstellungen, um eventuell unpassende Standardwerte zu überschreiben
mb_internal_encoding("UTF-8");

// Lokalität setzen, um etwa Monatsnamen auf Deutsch auszugeben
//setlocale(LC_ALL, 'de_DE@euro.UTF8', 'de_DE.UTF8', 'de.UTF8', 'deu.UTF8', 'deu_deu.UTF8', 'ge.UTF8');
setlocale(LC_ALL, 'deu_deu');

// Tabelle auswählen
if (!@mysql_select_db("vereinssystem")){
  // Tabelle konnte nicht ausgewählt werden, Ausführung abbrechen
  die('Benötigte Tabelle nicht gefunden: ' . mysql_error());
}

// Einstellungen auslesen
$settings_result = mysql_query("SELECT * FROM `einstellungen`");
if (mysql_num_rows($settings_result) > 0){
  // Einstellungen in einer Array $settings lesen
  while ($settings_entry = mysql_fetch_assoc($settings_result)){
    $settings[$settings_entry['name']] = $settings_entry['value'];
  }
}
?>