<?php
// Verarbeitet Speicher- und Erstellungsprozesse von Nachrichten

// Datenbankverbindung & Kodierung einbinden
require_once("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once("inc/checkuser.inc.php");

// Genereller Rechte Check
if ($user['rights'] < 2){
  // Rechtelevel geringer als 2 = Kein Zugang zu allen Funktionen der news_process.php
  if (isset($_SERVER["HTTP_REFERER"])){
    $referer = "?before=".base64_encode($_SERVER["HTTP_REFERER"]);
  }else{
    $referer = "";
  }
  header("Location: norights.php$referer");
  exit();
}

function verify_newsdaten($input){
  $errors = array();
  if (preg_match("/^[a-zäöüß0-9 \.-]{6,}$/i", $input['title']) != 1){
    // Titel dürfen nur aus Buchstaben, Ziffern, Bindestrichen, Leerzeichen und Punkten bestehen und müssen mindestens 6 Zeichen lang sein
    $errors[] = "Der eingegebene Titel ist ungültig";
  }
  // Verbotene Tags aus dem Inhalt entfernen
  $input['content'] = strip_tags($input['content'],"<a><u><i><b><strong><em><img>");
  if (strlen($input['content']) < 50){
    // PLZ dürfen nur aus Ziffern bestehen und müssen 5 Zeichen lang sein
    $errors[] = "Die eingegebene Nachricht ist zu kurz";
  }
  if (intval($input['minright']) > 5 || intval($input['minright']) < 0){
    // Mindestrechtelevel ist außerhalb des erlaubten Rahmens
    $errors[] = "Das eingegebene Sichtbarkeitslevel ist ungültig";
  }
  return array($errors,$input);
}
if (isset($_POST['savenews'])){
  // Eine Nachricht bzw. Meldung wurde verändert und muss in die DB gespeichert werden
  // Rechte Check
  if ($user['rights'] < 4){
    // Man hat schonmal ein Rechtelevel von kleiner 4 (darf also nicht automatisch alles bearbeiten)
    $sql_test_premission = "SELECT `author` FROM `news` WHERE `news`.`news_id` = ".intval($_POST['news_id'])."";
    $test_premission = mysql_query($sql_test_premission);
    $test_premission_result = mysql_fetch_assoc($test_premission);
    // Autor-ID auslesen und mit der aktuellen Nutzer-ID vergleichen
    if ($test_premission_result['author'] != $user['mitglieder_id']){
        // Der aktuelle Nutzer ist auch nicht der Autor der Nachricht = Keine Erlaubnis
        if (isset($_SERVER["HTTP_REFERER"])){
          $referer = "?before=".base64_encode($_SERVER["HTTP_REFERER"]);
        }else{
          $referer = "";
        }
        header("Location: norights.php$referer");
        exit();
    }
  }

  // Prüfung der Eingaben
  $input = $_POST;
  // Folgendes wendet trim auf jedes Element des Arrays an
  foreach ($input as $key=>$value) {
      $input[$key] = trim($value);
  }
  // Daten an die Prüfungsfunktion übergeben
  list($errors,$input) = verify_newsdaten($input);
  if (count($errors) == 0){
      // Gab es keine Fehler, dann SQL-Befehl erstellen
      $sql =
          "UPDATE `news` SET
           `title`        = '".mysql_real_escape_string(filter_var(stripslashes($input['title']),FILTER_SANITIZE_STRING))."',
           `content`      = '".mysql_real_escape_string((stripslashes($input['content'])))."',";
          if (!isset($input['silentchange']) || (isset($input['silentchange']) && $input['silentchange'] == 1 && $user['rights'] >= 5)){
            // Möglichkeit bieten für Nutzer mit Rechtelevel 5 oder höher, einen Nachrichtenbeitrag zu ändern ohne "lastchange" zu verändern
              $sql .= "`lastchange`   = '".mysql_real_escape_string(time())."',";
          }
           $sql .= "`minright`     = '".mysql_real_escape_string(intval($input['minright']))."'
         WHERE
           `news`.`news_id` = '".intval($input['news_id'])."'";
         // MySQL Code ausführen (und somit Änderungen speichern)
         if (mysql_query($sql)){
            // Bei Erfolg entsprechendes als GET-Parameter übergeben
            header('Location: news_edit.php?id='.intval($input['news_id'])."&save=success");
            exit();
         }else{
            // Bei einem Fehler die Mysql-Fehlernummer zur Fehleruntersuchung mitsenden
            header('Location: news_edit.php?id='.intval($input['news_id'])."&save=fail&why=".mysql_errno());
            exit();
         }
  }else{
    // Bei der Prüfung der Daten sind Fehler aufgetreten
    header('Location: news_edit.php?id='.intval($input['news_id']).'&save=fail&why=data&errors='.base64_encode(json_encode($errors)).'&data='.base64_encode(json_encode($input)));
    exit();
  }
}elseif (isset($_POST['addnews'])){
  // Ein neuen Nachricht bzw. Meldung in die DB aufnehmen
  // Rechte Check
  if ($user['rights'] < 2){
    // Rechtelevel geringer als 2 = Kein Zugang
    if (isset($_SERVER["HTTP_REFERER"])){
      $referer = "?before=".base64_encode($_SERVER["HTTP_REFERER"]);
    }else{
      $referer = "";
    }
    header("Location: norights.php$referer");
    exit();
  }
  // Prüfung der Eingaben; Fehlerarray erstellen
  list($errors,$input) = verify_newsdaten($_POST);
  if (count($errors) == 0){
    // Es gab keine Fehler bei der Prüfung der Eingaben
      $sql =
          "INSERT INTO `news` SET
           `title`        = '".mysql_real_escape_string(filter_var($input['title'],FILTER_SANITIZE_STRING))."',
           `content`      = '".mysql_real_escape_string(nl2br($input['content']))."',
           `minright`     = '".mysql_real_escape_string(intval($input['minright']))."',
           `author`       = '".mysql_real_escape_string($user['mitglieder_id'])."',
           `created`      = '".mysql_real_escape_string(time())."',
           `lastchange`   = '".mysql_real_escape_string(time())."',
           `status`       = '1'
           ";
         // MySQL Code ausführen (und somit Änderungen speichern)
         if (mysql_query($sql)){
            // Bei Erfolg entsprechendes als GET-Parameter übergeben
            header('Location: news_show.php?id='.mysql_insert_id()."&created");
            exit();
         }else{
            // Bei einem Fehler die Mysql-Fehlernummer zur Fehleruntersuchung mitsenden
            header('Location: news_add.php?created=fail&why='.mysql_errno()."&data=".base64_encode(json_encode($input)));
            exit();
         }
  }else{
    // Bei der Prüfung der Daten sind Fehler aufgetreten
    header('Location: news_add.php?created=fail&why=data&errors='.base64_encode(json_encode($errors)).'&data='.base64_encode(json_encode($input)));
    exit();
  }
}elseif (isset($_POST['deletenews'])){
  // Vorhandene Nachricht Löschen bzw. als im Papierkorb markieren
  // Rechte Check
  if ($user['rights'] < 4){
    // Man hat schonmal ein Rechtelevel von kleiner 4 (darf also nicht automatisch alles bearbeiten)
    $sql_test_premission = "SELECT `author` FROM `news` WHERE `news`.`news_id` = ".intval($_POST['news_id'])."";
    $test_premission = mysql_query($sql_test_premission);
    $test_premission_result = mysql_fetch_assoc($test_premission);
    // Autor-ID auslesen und mit der aktuellen Nutzer-ID vergleichen
    if ($test_premission_result['author'] != $user['mitglieder_id']){
        // Der aktuelle Nutzer ist auch nicht der Autor der Nachricht = Keine Erlaubnis
        if (isset($_SERVER["HTTP_REFERER"])){
          $referer = "?before=".base64_encode($_SERVER["HTTP_REFERER"]);
        }else{
          $referer = "";
        }
        header("Location: norights.php$referer");
        exit();
    }
  }

  if ($_POST['sure'] == 2){
     if ($_POST['dbdelete'] == 2 && $user['rights'] >= 5){
      // Nachricht soll auch aus der DB gelöscht werden und der Nutzer hat die Berechtigung dazu
      $sql = "DELETE FROM `news`
            WHERE
           `news`.`news_id` = '".intval($_POST['news_id'])."' AND `news`.`status` = 1";
      if (mysql_query($sql)){
         // Bei Erfolg entsprechendes als GET-Parameter übergeben
         header('Location: news.php?deletion=success&who='.intval($_POST['news_id']));
         exit();
      }else{
         // Bei einem Fehler die Mysql-Fehlernummer zur Fehleruntersuchung mitsenden
         header('Location: news_delete.php?id='.intval($_POST['news_id']).'&deletion=fail&why='.mysql_errno());
         exit();
      }
    }else{
      // Nachricht normal als "im Papierkorb" markieren
      $sql = "UPDATE `news` SET
           `status`        = '2'
         WHERE
           `news`.`news_id` = '".intval($_POST['news_id'])."' AND `news`.`status` = 1";
      if (mysql_query($sql)){
         // Bei Erfolg entsprechendes als GET-Parameter übergeben
         header('Location: news.php?removal=success&who='.intval($_POST['news_id']));
         exit();
      }else{
         // Bei einem Fehler die Mysql-Fehlernummer zur Fehleruntersuchung mitsenden
         header('Location: news_delete.php?id='.intval($_POST['news_id']).'&removal=fail&why='.mysql_errno());
         exit();
      }
    }
  }else{
    // Wirklich löschen wurde mit Nein beantwortet
    header('Location: news_show.php?id='.intval($_POST['news_id']));
    exit();
  }
}

// Weiterleitung, falls aus irgendwelchen Gründen diese Datei aufgerufen wird, ohne, dass etwas übergeben wird
header('Location: news.php');
?>