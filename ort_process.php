<?php
// Verarbeitet Speicher- und Erstellungsprozesse von Orten

// Datenbankverbindung & Kodierung einbinden
require_once("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once("inc/checkuser.inc.php");

function verify_ortsdaten($input){
  $errors = array();
  if (strlen($input['name']) < 3 && $input['typ'] == 2){
    // Mindestlänge für Namen von Veranstaltungsorten ist 3 Zeichen
    $errors[] = "Der eingegebene Name des Veranstaltungsortes ist zu kurz";
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
  if ($input['typ'] > 2 || $input['typ'] < 1){
    // Eingegebene Typennummer ist ungültig. Vermutlich Formular-Manipulation
    $errors[] = "Der eingegebene Ortstyp ist ungültig";
  }
  return array($errors,$input);
}
if (isset($_POST['saveort'])){
  // Ein Ort bzw. eine Anschrift wurde verändert und muss in die DB gespeichert werden
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
  // Prüfung der Eingaben
  $input = $_POST;
  // Folgendes wendet trim auf jedes Element des Arrays an
  foreach ($input as $key=>$value) {
      $input[$key] = trim($value);
  }
  // Daten an die Prüfungsfunktion übergeben
  list($errors,$input) = verify_ortsdaten($input);
  if (count($errors) == 0){
      // Gab es keine Fehler, dann SQL-Befehl erstellen
      $sql =
          "UPDATE `orte` SET
           `name`        = '".mysql_real_escape_string(filter_var($input['name'],FILTER_SANITIZE_STRING))."',
           `strasse`     = '".mysql_real_escape_string(filter_var($input['strasse'],FILTER_SANITIZE_STRING))."',
           `hausnummer`  = '".mysql_real_escape_string(filter_var($input['hausnummer'],FILTER_SANITIZE_STRING))."',
           `plz`         = '".mysql_real_escape_string(filter_var($input['plz'],FILTER_SANITIZE_STRING))."',
           `ort`         = '".mysql_real_escape_string(filter_var($input['ort'],FILTER_SANITIZE_STRING))."',";
           if ($input['telvorwahl'] > 0 && $input['telvorwahl'] > 0){
             $sql .= "`telefon`     = '".mysql_real_escape_string(filter_var($input['telvorwahl'],FILTER_SANITIZE_STRING))."/".mysql_real_escape_string(filter_var($input['telnr'],FILTER_SANITIZE_STRING))."',";
           }else{
             $sql .= "`telefon`     = '',";
           }
           $sql .= "`typ`         = '".(intval($input['typ']))."'
         WHERE
           `orte`.`orts_id` = '".intval($input['orts_id'])."'";
         // MySQL Code ausführen (und somit Änderungen speichern)
         if (mysql_query($sql)){
            // Bei Erfolg entsprechendes als GET-Parameter übergeben
            header('Location: ort_edit.php?id='.intval($input['orts_id'])."&save=success");
            exit();
         }else{
            // Bei einem Fehler die Mysql-Fehlernummer zur Fehleruntersuchung mitsenden
            header('Location: ort_edit.php?id='.intval($input['orts_id'])."&save=fail&why=".mysql_errno());
            exit();
         }
  }else{
    // Bei der Prüfung der Daten sind Fehler aufgetreten
    header('Location: ort_edit.php?id='.intval($input['orts_id']).'&save=fail&why=data&errors='.base64_encode(json_encode($errors)).'&data='.base64_encode(json_encode($input)));
    exit();
  }
}elseif (isset($_POST['addort'])){
  // Ein neuen Ort bzw. eine neue Anschrift in die DB aufnehmen
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
  // Prüfung der Eingaben; Fehlerarray erstellen
  list($errors,$input) = verify_ortsdaten($_POST);
  if (count($errors) == 0){
    // Es gab keine Fehler bei der Prüfung der Eingaben
      $sql =
          "INSERT INTO `orte` SET
           `name`        = '".mysql_real_escape_string(filter_var($input['name'],FILTER_SANITIZE_STRING))."',
           `strasse`     = '".mysql_real_escape_string(filter_var($input['strasse'],FILTER_SANITIZE_STRING))."',
           `hausnummer`  = '".mysql_real_escape_string(filter_var($input['hausnummer'],FILTER_SANITIZE_STRING))."',
           `plz`         = '".mysql_real_escape_string(filter_var($input['plz'],FILTER_SANITIZE_STRING))."',
           `ort`         = '".mysql_real_escape_string(filter_var($input['ort'],FILTER_SANITIZE_STRING))."',";
           if ($input['telvorwahl'] > 0 && $input['telvorwahl'] > 0){
             $sql .= "`telefon`     = '".mysql_real_escape_string(filter_var($input['telvorwahl'],FILTER_SANITIZE_STRING))."/".mysql_real_escape_string(filter_var($input['telnr'],FILTER_SANITIZE_STRING))."',";
           }else{
             $sql .= "`telefon`     = '',";
           }
           $sql .= "`typ`         = '".(intval($input['typ']))."',
           `createdby`         = '".(intval($user['mitglieder_id']))."'
           ";
         // MySQL Code ausführen (und somit Änderungen speichern)
         if (mysql_query($sql)){
            // Bei Erfolg entsprechendes als GET-Parameter übergeben
            header('Location: ort_show.php?id='.mysql_insert_id()."&created");
            exit();
         }else{
            // Bei einem Fehler die Mysql-Fehlernummer zur Fehleruntersuchung mitsenden
            header('Location: ort_add.php?created=fail&why='.mysql_errno()."&data=".base64_encode(json_encode($input)));
            exit();
         }
  }else{
    // Bei der Prüfung der Daten sind Fehler aufgetreten
    header('Location: ort_add.php?created=fail&why=data&errors='.base64_encode(json_encode($errors)).'&data='.base64_encode(json_encode($input)));
    exit();
  }
}elseif (isset($_POST['deleteort'])){
  // Vorhandenen Ort Löschen bzw. als im Papierkorb markieren
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
  if ($_POST['sure'] == 2){
    // Prüfen, ob der Ort nicht noch irgendwo eingebunden ist
    $sql_test1 = mysql_query("SELECT COUNT(*) as count FROM `mitglieder` WHERE `anschrift` = ".intval($_POST['orts_id']));
    $sql_result1 = mysql_fetch_assoc($sql_test1);
    $sql_test2 = mysql_query("SELECT COUNT(*) as count FROM `veranstaltungen` WHERE `ort` = ".intval($_POST['orts_id']));
    $sql_result2 = mysql_fetch_assoc($sql_test2);
    if ($sql_result1['count'] == 0 && $sql_result2['count'] == 0){
        // Ort nicht mehr in Verwendung
        if ($_POST['dbdelete'] == 2 && $user['rights'] >= 5){
          // Ort soll auch aus der DB gelöscht werden und der Nutzer hat die Berechtigung dazu
          $sql = "DELETE FROM `orte`
                WHERE
               `orte`.`orts_id` = '".intval($_POST['orts_id'])."' AND `orte`.`status` = 1";
          if (mysql_query($sql)){
             // Bei Erfolg entsprechendes als GET-Parameter übergeben
             header('Location: orte.php?deletion=success&who='.intval($_POST['orts_id']));
             exit();
          }else{
             // Bei einem Fehler die Mysql-Fehlernummer zur Fehleruntersuchung mitsenden
             header('Location: ort_delete.php?id='.intval($_POST['orts_id']).'&deletion=fail&why='.mysql_errno());
             exit();
          }
        }else{
          // Ort normal als "im Papierkorb" markieren
          $sql = "UPDATE `orte` SET
               `status`        = '2'
             WHERE
               `orte`.`orts_id` = '".intval($_POST['orts_id'])."' AND `orte`.`status` = 1";
          if (mysql_query($sql)){
             // Bei Erfolg entsprechendes als GET-Parameter übergeben
             header('Location: orte.php?removal=success&who='.intval($_POST['orts_id']));
             exit();
          }else{
             // Bei einem Fehler die Mysql-Fehlernummer zur Fehleruntersuchung mitsenden
             header('Location: ort_delete.php?id='.intval($_POST['orts_id']).'&removal=fail&why='.mysql_errno());
             exit();
          }
        }
    }else{
      // Der Ort wird noch verwendet und kann daher nicht gelöscht werden
      header('Location: ort_delete.php?id='.intval($_POST['orts_id'])."&fail=inuse&m=".$sql_result1['count']."&v=".$sql_result2['count']);
      exit();
    }
  }else{
    // Wirklich löschen wurde mit Nein beantwortet
    header('Location: ort_show.php?id='.intval($_POST['orts_id']));
    exit();
  }
}

// Weiterleitung, falls aus irgendwelchen Gründen diese Datei aufgerufen wird, ohne, dass etwas übergeben wird
header('Location: orte.php');
?>