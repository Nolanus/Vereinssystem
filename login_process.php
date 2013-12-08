<?php
// Datenbankverbindung & Kodierung einbinden
require_once("inc/connect.inc.php");

// Diese Datei wird aufgerufen, wenn sich ein Besucher anmeldet.
// Die Anmeldedaten bestehen aus dem username oder der E-Mail Adresse,
// dem Passwort und dem Captcha-Code und werden per POST übergeben

// Test-Cookie auslesen
if (!isset($_COOKIE['keks']) || $_COOKIE['keks'] != "funktioniert"){
    // Cookies werden nicht unterstützt
    header("location:login.php?fail=cookie");
    exit();
}

if ($settings['usecaptchalogin'] == 1){
  // Blowfish Klasse einbingen, wenn Captchas verwendet werden sollen
  include ("inc/blowfish.class.php");
  $blowfish = new Blowfish("schluessel");
}
// Prüfen, ob der Captcha-Code richtig war
// $_POST['captcha'] enthält die Nutzereingabe, $_POST['astresult'] den MD5-Hash des richtigen Ergebnissen
// Alternativ ist $settings['usecaptchalogin'] gleich 0 (kein Captcha beim Login Nutzen)
if ($settings['usecaptchalogin'] == 1 && (md5($_POST['captcha']) == $_POST['astresult']) || $settings['usecaptchalogin'] == 0){
    // Captcha-Test bestanden, Login weiter fortsetzen
    // Mitglieder aus der DB auslesen, die den eingegebenen Nutzernmane/Mitgliedsnummer und das eingegebene Passwort haben
    $sql = "SELECT * FROM `mitglieder` WHERE (`username` = '".mysql_real_escape_string(stripslashes($_POST['username']))."' OR `mitgliedsnummer` = '".mysql_real_escape_string(stripslashes($_POST['username']))."') AND `passwort` = '".md5(stripslashes($_POST['psword']))."'";
    $nutzer = mysql_query($sql);

    if (mysql_num_rows($nutzer) == 1){
        // Gibt es genau eine Übereinstimmung, dann Login erfolgreich
        // Session starten
        session_start();

        // Session-ID erneuern, um das Sicherheitsproblem der Session Fixation zu verhindern
        session_regenerate_id();

        // Nutzerdaten des aktuell angemeldet Mitglieds
        $user = mysql_fetch_assoc($nutzer);

        // Prüfen, ob der Nutzer berechtigt ist, sich anzumelden
        if ($user['status'] != 1 || $user['rights'] == 0){
          // Der Nutzer ist gesperrt (entweder keine Recht (rights=0) und/oder der Status ist nicht 1 (vll. Nutzer gelöscht, was Status auf 2 setzt)
          // Session zerstören und zurück zum Login-Formular
          session_destroy();
          header ("Location: login.php?forbidden");
          exit();
        }

        // Last-Login Daten aktualisieren
        mysql_query("UPDATE `mitglieder` SET `lastlogintime` = `currentlogintime`, `lastloginip` = `currentloginip`, `currentlogintime` = ".time().", `currentloginip` = '".$_SERVER["REMOTE_ADDR"]."' WHERE `mitglieder_id` = ".$user['mitglieder_id']);

        // SESSION-Variable setzen
        $_SESSION["uid"] = $user['mitglieder_id'];
        $_SESSION["loginip"] = $_SERVER["REMOTE_ADDR"];
        $_SESSION["freshlogin"] = true;

        // Login Erfolgreich; Wurde einer after-URL übergeben und verweist diese auf den aktuellen Server?
        if (isset($_POST['after'])){
            // Weiterleiten zur after-URL
            header("location: ".base64_decode($_POST['after']));
            exit();
        }else{
            // Weiterleiten zur Startseite
            header("location:index.php");
            exit();
        }
    }else{
        // Keine oder mehrere Übereinstimmungen, Login fehlgeschlagen
        header("location:login.php?fail=data");
        exit();
    }
}else{
  // Captcha-Test fehlgeschlagen
  header("location:login.php?fail=captcha");
  exit();
}
// Keine Weiterleitung bisher, dann zurück zur Login (sollte nie vorkommen)
header("location:login.php");
?>