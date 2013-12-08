<?php
// Verarbeitet Speicherprozesse der Einstellungen

// Datenbankverbindung & Kodierung einbinden
require_once("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once("inc/checkuser.inc.php");

// Rechte Check
if ($user['rights'] < 5){
  // Rechtelevel geringer als 5 = Kein Zugang
  if (isset($_SERVER["HTTP_REFERER"])){
    $referer = "?before=".base64_encode($_SERVER["HTTP_REFERER"]);
  }else{
    $referer = "";
  }
  header("Location: norights.php$referer");
  exit();
}

function verify_einstellungen($input){
  $errors = array();
  if (preg_match("/^[a-zäöüß0-9 \.-]{6,}$/i", $input['vereinsname']) != 1){
    // Vereinsnamen dürfen nur aus Buchstaben, Ziffern Bindestrichen, Leerzeichen und Punkten bestehen und müssen mindestens 6 Zeichen lang sein
    $errors[] = "Der eingegebene Vereinsname ist ungültig";
  }
  if (preg_match("/^[a-zäöüß0-9 \.-]{10,}$/i", $input['vereinsname_lang']) != 1){
    // Vereinsnamen dürfen nur aus Buchstaben, Ziffern Bindestrichen, Leerzeichen und Punkten bestehen und müssen mindestens 6 Zeichen lang sein
    $errors[] = "Der eingegebene offizielle Vereinsname ist ungültig";
  }
  if (preg_match("/^[0-9]{0,3}$/i", $input['beitrag_kindereuro']) != 1){
    // Eurobetrag des Migliedsbeitrags für Kinder darf nur aus Ziffern bestehen und muss 0-3 Zeichen lang sein
    $errors[] = "Der eingegebene Mitgliedsbeitrag für Kinder ist ungültig";
  }else{
    $input['beitrag_kindereuro'] = intval($input['beitrag_kindereuro']);
  }
  if (preg_match("/^[0-9]{0,2}$/i", $input['beitrag_kindercent']) != 1){
    // Centbetrag des Migliedsbeitrags für Kinder darf nur aus Ziffern bestehen und muss 2 Zeichen lang sein
    $errors[] = "Der eingegebene Mitgliedsbeitrag für Kinder ist ungültig";
  }else{
    $input['beitrag_kindercent'] = intval($input['beitrag_kindercent']);
  }
  if (preg_match("/^[0-9]{0,3}$/i", $input['beitrag_erwachsenereuro']) != 1){
    // Eurobetrag des Migliedsbeitrags für Erwachsene darf nur aus Ziffern bestehen und muss 0-3 Zeichen lang sein
    $errors[] = "Der eingegebene Mitgliedsbeitrag für Erwachsene ist ungültig";
  }else{
    $input['beitrag_erwachsenereuro'] = intval($input['beitrag_erwachsenereuro']);
  }
  if (preg_match("/^[0-9]{0,2}$/i", $input['beitrag_erwachsenercent']) != 1){
    // Centbetrag des Migliedsbeitrags für Erwachsene darf nur aus Ziffern bestehen und muss 2 Zeichen lang sein
    $errors[] = "Der eingegebene Mitgliedsbeitrag für Erwachsene ist ungültig";
  }else{
    $input['beitrag_erwachsenercent'] = intval($input['beitrag_erwachsenercent']);
  }
  // E-Mail Adresse sanitizen/reinigen
  $input['webmastermail'] = filter_var($input['webmastermail'], FILTER_SANITIZE_EMAIL);
  if (!filter_var($input['webmastermail'], FILTER_VALIDATE_EMAIL) && !empty($input['webmastermail'])) {
    // Prüfen, ob es eine gültige E-Mail-Adresse und nicht leer ist (kleine Fehler werden automatisch verbessert; leer wäre auch erlaubt)
    $errors[] = "Die eingegebene E-Mail Adresse des Webmasters ist ungültig";
  }
  // E-Mail Adresse sanitizen/reinigen
  $input['leitermail'] = filter_var($input['leitermail'], FILTER_SANITIZE_EMAIL);
  if (!filter_var($input['leitermail'], FILTER_VALIDATE_EMAIL) && !empty($input['leitermail'])) {
    // Prüfen, ob es eine gültige E-Mail-Adresse und nicht leer ist (kleine Fehler werden automatisch verbessert; leer wäre auch erlaubt)
    $errors[] = "Die eingegebene E-Mail Adresse des Leiters ist ungültig";
  }

  if (preg_match("/^[a-zäöüß \.-]{4,}$/i", $input['strasse']) != 1){
    // Straßennamen dürfen nur aus Buchstaben, Bindestrichen, Leerzeichen und Punkten bestehen und müssen mindestens 4 Zeichen lang sein
    $errors[] = "Der eingegebene Straßenname ist ungültig";
  }
  if (preg_match("/^[0-9]+[a-z0-9 -]*$/i", $input['hausnummer']) != 1 && !empty($input['hausnummer'])){
    // Hausnummern dürfen nur aus Buchstaben, Ziffern, Bindestrichen, Leerzeichen und Punkten bestehen, müssen jedoch min. mit einer Ziffer starten
    $errors[] = "Die eingegebene Hausnummer ist ungültig";
  }
  if (preg_match("/^[0-9]{5}$/", $input['plz']) != 1){
    // PLZ dürfen nur aus Ziffern bestehen und müssen 5 Zeichen lang sein
    $errors[] = "Die eingegebene PLZ ist ungültig";
  }elseif($input['ort'] == ""){
    // PLZ ist gültig und Ortsname ist nicht vorhanden
    // Versuchen, falls kein Ortsname angegeben ist, anhand der bekannten Orte den Ortsnamen mittels PLZ herauszufinden
    $sql_test = "SELECT `plz`,`ort` FROM `orte` WHERE `plz` = '".mysql_real_escape_string($input['plz'])."' GROUP BY `ort`";
    $sql_result = mysql_query($sql_test);
    if (mysql_num_rows($sql_result) == 1){
      // Es gibt nur einen Ortsnamen, der zu dieser PLZ gefunden wurde (bei mehreren wird nichts übernommen, da es evtl. falsch sein könnte)
      $sql_test_data = mysql_fetch_assoc($sql_result);
      $input['ort'] = $sql_test_data['ort'];
    }
  }
  if (preg_match("/^[a-zäöüß -]{3,}$/i", $input['ort']) != 1){
    // Ortsnamen dürfen nur aus Buchstaben, Bindestrichen, Leerzeichen und Punkten bestehen und müssen mindestens 3 Zeichen lang sein
    $errors[] = "Die eingegebene Ortsname ist ungültig";
  }elseif($input['plz'] == ""){
    // Ortsname ist gültig und PLZ ist nicht vorhanden
    // Versuchen, falls kein PLZ angegeben ist, anhand der bekannten Orte die PLZ mittels Ortsnamen herauszufinden
    $sql_test = "SELECT `plz`,`ort` FROM `orte` WHERE `ort` = '".mysql_real_escape_string($input['ort'])."' GROUP BY `plz`";
    $sql_result = mysql_query($sql_test);
    if (mysql_num_rows($sql_result) == 1){
      // Es gibt nur eine PLZ zu diesem Ortsnamen (bei mehreren wird nichts übernommen, da es evtl. falsch sein könnte)
      $sql_test_data = mysql_fetch_assoc($sql_result);
      $input['plz'] = $sql_test_data['plz'];
      // Da die fehlende PLZ eine Fehlermeldung erzeugt hat und dieses Problem nun gelöst wurde, muss die Fehlermeldung wieder aus dem $errors-Array entfernt werden
      // (Entfernt den letzten Eintrag des Arrays)
      unset($errors[count($errors)-1]);
    }
  }
  if (!empty($input['telvorwahl']) && !empty($input['telnr']) && (preg_match("/^[0-9\+]{3,}$/", $input['telvorwahl']) != 1 && preg_match("/^[0-9]{4,}$/", $input['telnr']) != 1 )){
    // Telefonnummern dürfen nur aus Ziffern und einem eventuellen Plus bestehen (Alternativ sind beide String empty/leer, wenn keine Telefonnr. angegeben wird
    $errors[] = "Die eingegebene Telefonnummer ist ungültig";
  }
  if (preg_match("/^[0-9]{1,}$/i", $input['annualwork']) != 1){
    // Jährliche Diensteanzahl darf nur aus Ziffern bestehen und mindestens 1 Zeichen lang sein
    $errors[] = "Die eingegebene Anzahl jährlicher Dienste ist ungültig";
  }else{
    $input['annualwork'] = intval($input['annualwork']);
  }
  if (preg_match("/^[0-9]{1,}$/i", $input['cakeworkcount']) != 1){
    // cakeworkcount darf nur aus Ziffern bestehen und mindestens 1 Zeichen lang sein
    $errors[] = "Die eingegebene Verhältnis von Standdienst zu Kuchen-mitbringen ist ungültig";
  }else{
    $input['cakeworkcount'] = intval($input['cakeworkcount']);
  }
if (count($errors) == 0){
       // Gab es keine Fehler bisher, Prüfen, ob ein Ort mit den angegebenen Daten vorhanden ist
       if ($input['telvorwahl'] > 0 && $input['telnr'] > 0){
         $searchtelefon = $input['telvorwahl']."/".$input['telnr'];
       }else{
         $searchtelefon = "";
       }
       $sql_ort = "SELECT orts_id FROM `orte` WHERE `strasse` = '".mysql_real_escape_string($input['strasse'])."' AND `hausnummer` = '".mysql_real_escape_string($input['hausnummer'])."' AND `plz` = '".mysql_real_escape_string($input['plz'])."' AND `ort` = '".mysql_real_escape_string($input['ort'])."' AND `telefon` = '".mysql_real_escape_string($searchtelefon)."' AND `status` = 1";
       $orte = mysql_query($sql_ort);
       if (mysql_num_rows($orte) == 1){
         // Es wurde ein Ort gefunden, der den Angaben entspricht
         // Werte dieses Eintrags in $ort einlesen (ist hier nur die orts_id)
         $ort = mysql_fetch_assoc($orte);
         $input['orts_id'] = $ort['orts_id'];
       }elseif(mysql_num_rows($orte) == 0){
         // Der Ort ist neu, bzw es gibt ihn noch nicht in der Datenbank = Ort neu Anlegen
         $sql_ort = "INSERT INTO `orte` SET
            `strasse`       = '".mysql_real_escape_string(filter_var($input['strasse'],FILTER_SANITIZE_STRING))."',
            `hausnummer`    = '".mysql_real_escape_string(filter_var($input['hausnummer'],FILTER_SANITIZE_STRING))."',
            `plz`           = '".mysql_real_escape_string(filter_var($input['plz'],FILTER_SANITIZE_STRING))."',
            `ort`           = '".mysql_real_escape_string(filter_var($input['ort'],FILTER_SANITIZE_STRING))."',";
             if ($input['telvorwahl'] > 0 && $input['telnr'] > 0){
                $sql_ort .= "`telefon`      = '".mysql_real_escape_string(filter_var($input['telvorwahl']."/".$input['telnr'],FILTER_SANITIZE_STRING))."',";
             }else{
                $sql_ort .= "`telefon`      = '',";
             }
             $sql_ort .= "`typ`        = '1',
             `status`       = '1'";
             if (mysql_query($sql_ort)){
               // Neuer Ort wurde erfolgreich in der Datenbank angelegt
               // ID des letzten Insert-Vorgangs (anlegen der neuen Anschrift in der Orts-Tabelle) erhalten
               $input['orts_id'] = mysql_insert_id();
             }else{
               $errors[] = "Adressänderung konnte nicht übernommen werden.";
             }
       }else{
         // Unmöglicher Fehler! Ein Ort kann nur einmal oder gar nicht in der DB sein.
         exit("Schwerwiegender Fehler. Bitte wenden Sie sich umgehend an den Systemadministrator!");
       }

}
  return array($errors,$input);
}

if (isset($_POST['savesettings'])){
  // Einstellungen müssen in die DB gespeichert werden
  // Prüfung der Eingaben
  $input = $_POST;
  // Folgendes wendet trim auf jedes Element des Arrays an
  foreach ($input as $key=>$value) {
      $input[$key] = trim($value);
  }
  // Daten an die Prüfungsfunktion übergeben
  list($errors,$input) = verify_einstellungen($input);
  if (count($errors) == 0){
      // Gab es keine Fehler, SQL Befehle ausführen
      //
      $sqls = array(
      "UPDATE `einstellungen` SET `value` = '".mysql_real_escape_string($input['orts_id'])."' WHERE `einstellungen`.`name` = 'vereinsanschrift'",
      "UPDATE `einstellungen` SET `value` = '".mysql_real_escape_string($input['vereinsname_lang'])."' WHERE `einstellungen`.`name` = 'vereinsname_lang'",
      "UPDATE `einstellungen` SET `value` = '".mysql_real_escape_string($input['vereinsname'])."' WHERE `einstellungen`.`name` = 'vereinsname'",
      "UPDATE `einstellungen` SET `value` = '".intval(($input['beitrag_kindereuro']*100 + $input['beitrag_kindercent']))."' WHERE `einstellungen`.`name` = 'beitrag_kinder'",
      "UPDATE `einstellungen` SET `value` = '".intval(($input['beitrag_erwachsenereuro']*100 + $input['beitrag_erwachsenercent']))."' WHERE `einstellungen`.`name` = 'beitrag_erwachsener'",
      "UPDATE `einstellungen` SET `value` = '".mysql_real_escape_string($input['webmastermail'])."' WHERE `einstellungen`.`name` = 'webmastermail'",
      "UPDATE `einstellungen` SET `value` = '".mysql_real_escape_string($input['leitermail'])."' WHERE `einstellungen`.`name` = 'leitermail'",
      "UPDATE `einstellungen` SET `value` = '".mysql_real_escape_string($input['usecaptchalogin'])."' WHERE `einstellungen`.`name` = 'usecaptchalogin'",
      "UPDATE `einstellungen` SET `value` = '".mysql_real_escape_string($input['annualwork'])."' WHERE `einstellungen`.`name` = 'annualwork'",
      "UPDATE `einstellungen` SET `value` = '".mysql_real_escape_string($input['cakeworkcount'])."' WHERE `einstellungen`.`name` = 'cakeworkcount'"
      );
      $befehle = count($sqls);
      for ($i=1; $i<=$befehle; $i++)  {
         if (!mysql_query($sqls[$i-1])){
            // Bei einem Fehler die Mysql-Fehlernummer zur Fehleruntersuchung mitsenden
            header("Location: einstellungen.php?save=fail&when=$i&why=".mysql_errno());
            exit();
         }
      }
      // Bei Erfolg entsprechendes als GET-Parameter übergeben
      header("Location: einstellungen.php?id=&save=success");
      exit();
  }else{
    // Bei der Prüfung der Daten sind Fehler aufgetreten
    header("Location: einstellungen.php?&save=fail&why=data&errors=".base64_encode(json_encode($errors)).'&data='.base64_encode(json_encode($input)));
    exit();
  }
}

// Weiterleitung, falls aus irgendwelchen Gründen diese Datei aufgerufen wird, ohne, dass etwas übergeben wird
header('Location: einstellungen.php');
?>