<?php
//Prüft, ob ein Nutzer angemeldet und somit zum Besuchen der Seite berechtigt ist und leitet gegebenenfalls zum Login-Formular weiter

session_set_cookie_params(0,"/",session_save_path(),false,true);
session_start();

if (!isset ($_SESSION["uid"])){ //Keine Session-ID vorhanden?
    // Dann ist keine gültiger Login vorhanden, eventuelle Session-Daten zerstören
    @session_destroy();
    // Weiterleiten zum Login-Formular
    header ("Location: login.php?after=".base64_encode($_SERVER["REQUEST_URI"]));
    exit(); //Ausführung des Scripts abbrechen, rein zur Sicherheit
}

$user_data = mysql_query("SELECT * FROM `mitglieder` WHERE `mitglieder_id` = '".$_SESSION['uid']."' AND `currentloginip` = '".$_SESSION["loginip"]."'");
if (mysql_num_rows($user_data) != 1){
    // Kein gültiges Mitglied vorhanden. Entweder gefälschte Session-ID oder IP hat sich geändert
    @session_destroy();
    header ("Location: login.php?old&after=".base64_encode($_SERVER["REQUEST_URI"]));
    exit();
}else{
  $user = mysql_fetch_assoc($user_data);
  if ($user['status'] != 1 || $user['rights'] == 0){
    // Der Nutzer ist gesperrt (entweder keine Recht (rights=0) und/oder der Status ist nicht 1 (vll. Nutzer gelöscht, was Status auf 2 setzt)
    @session_destroy();
    header ("Location: login.php?forbidden");
    exit();
  }
}
?>