<?php
// Verarbeitet Speicher- und Erstellungsprozesse von Abteilungen

// Datenbankverbindung & Kodierung einbinden
require_once("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once("inc/checkuser.inc.php");

function verify_abteilungsdaten($input){
  $errors = array();
  if (preg_match("/^[a-zäöüß0-9 \.-]{4,}$/i", $input['name']) != 1){
    // Abteilungsnamen dürfen nur aus Buchstaben, Ziffern, Bindestrichen und Leerzeichen und müssen mindestens 4 Zeichen lang sein
    $errors[] = "Der eingegebene Abteilungsname ist ungültig";
  }
  if (preg_match("/^[a-zäöüß0-9 \.-]{4,}$/i", $input['name']) != 1){
    // Abteilungsnamen dürfen nur aus Buchstaben, Ziffern, Bindestrichen und Leerzeichen und müssen mindestens 4 Zeichen lang sein
    $errors[] = "Der eingegebene Abteilungsname ist ungültig";
  }
  // Homepage Adresse sanitizen/reinigen
  $input['homepage'] = filter_var($input['homepage'], FILTER_SANITIZE_URL);
  if (substr($input['homepage'],0,4) != "http"){
    $input['homepage'] = "http://".$input['homepage'];
  }
  if ($input['homepage'] == "http://"){
    $input['homepage'] = "";
  }
  if (!filter_var($input['homepage'], FILTER_VALIDATE_URL) && !empty($input['homepage'])) {
    // Prüfen, ob es eine gültige URL und nicht leer ist (kleine Fehler werden automatisch verbessert; leer wäre auch erlaubt)
    $errors[] = "Homepage Adresse ungültig";
  }
  if (isset($input['abteilungsleiterdrop']) && $input['abteilungsleiterdrop'] != "new" && empty($input['abteilungsleiter'])){
    // Dem Nutzer wurde ein Auswahlfeld angezeigt und das Textfeld ist leer (= Keine Eingabe über "Anderer Nutzer", es wurde also jemand aus der Liste gewählt)
    // Prüfung: Ist der Nutzer vorhanden und aktiv?
    $abteilungsleiter_test = mysql_query("SELECT * FROM `mitglieder` WHERE `mitglieder_id` = ".intval($input['abteilungsleiterdrop'])." AND `status` = 1");
    if (mysql_num_rows($abteilungsleiter_test) == 0){
      $errors[] = "Es wurde kein Mitglied mit dem angegebenen Namen des Abteilungsleiters gefunden";
    }else{
      // Es ist ein Mitglied vorhanden
      $abteilungsleiter_daten = mysql_fetch_assoc($abteilungsleiter_test);
      $input['abteilungsleiterid'] = $abteilungsleiter_daten['mitglieder_id'];
    }
  }elseif(!empty($input['abteilungsleiter'])){
    // Es wurde ein Name als String übergeben, anhand des Namens das Mitglied finden
    $namensteile = explode(" ",$input['abteilungsleiter']);
    $suchteile = array(); // Suchteile Array erstellen, welches alle Suchkriterien (Textteile des Namens) enthält
    foreach ($namensteile as &$suchteil) {
      $suchteile[] = "(`nachname` LIKE '%".$suchteil."%' OR `vorname` LIKE '%".$suchteil."%')";
    }
    $suchteile[] = "`status` = 1"; // Nur aktive Mitglieder verwenden
    $abteilungsleiter_test = mysql_query("SELECT * FROM `mitglieder` WHERE ".implode(" AND ",$suchteile));
    if (mysql_num_rows($abteilungsleiter_test) == 1){
        // Es gibt genau einen Nutzer mit dem Namen
        $abteilungsleiter_daten = mysql_fetch_assoc($abteilungsleiter_test);
        $input['abteilungsleiterid'] = $abteilungsleiter_daten['mitglieder_id'];
    }elseif (mysql_num_rows($abteilungsleiter_test) == 0){
      $errors[] = "Es wurde kein Mitglied mit dem angegebenen Namen des Abteilungsleiters gefunden";
    }else{
      $errors[] = "Es wurden mehrere Mitglieder mit dem angegebenen Namen des Abteilungsleiters gefunden";
      if (!isset($input['abteilungsleiterdrop'])){
        // Es wird nur eine Selectbox bei der Datenübergaben mit Prefil angezeigt, wenn das Feld "abteilungsleiterdrop" ebenfalls übergeben wird. Daher hier dieses erzeugen, wenn es nicht vorhanden ist
        $input['abteilungsleiterdrop'] = "";
      }
    }
  }else{
    // Kein Auswahlfeld und Textfeld ist leer oder sonstige fehlerhafte Wertübergabe = Kein Abteilungsleiter angegeben
    $errors[] = "Es wurden kein Abteilungsleiters eingegeben";
  }
  if (isset($abteilungsleiter_daten)){
    // Wurde ein Datensatz eines Möglichen Abteilungsleiters gefunden? Dann auf plausibilität prüfen
    if (abs(time() - strtotime($abteilungsleiter_daten['geburtstag'])) < 568024668 ){
      // Die Person ist keine 18 Jahre alt
      $errors[] = "Der angegebene Abteilungsleiter ist jünger als 18 Jahre und damit zu jung";
    }
  }


  if (preg_match("/^[0-9]{0,3}$/i", $input['aktumleuro']) != 1){
    // Eurobetrag der Aktivenumlage darf nur aus Ziffern bestehen und muss 0-3 Zeichen lang sein
    $errors[] = "Der eingegebene Aktivenumlagebetrag ist ungültig";
  }else{
    $input['aktumleuro'] = intval($input['aktumleuro']);
  }
  if (preg_match("/^[0-9]{0,2}$/i", $input['aktumlcent']) != 1){
    // Centbetrag der Aktivenumlage darf nur aus Ziffern bestehen und muss 2 Zeichen lang sein
    $errors[] = "Der eingegebene Aktivenumlagebetrag ist ungültig";
  }else{
    $input['aktumlcent'] = intval($input['aktumlcent']);
  }
  return array($errors,$input);
}

if (isset($_POST['saveabteilung'])){
  // Ein Abteilung wurde verändert und muss in die DB gespeichert werden
  // Hat der aktuelle Nutzer die Rechte dazu?
  if ($user['rights'] < 4){
    // Man hat schonmal ein Rechtelevel von kleiner 4
    $sql_test_premission = "SELECT `abteilungsleiter` FROM `abteilungen` WHERE
                  `abteilungen`.`abteilungs_id` = ".intval($_POST['abteilungs_id'])."";
    $test_premission = mysql_query($sql_test_premission);
    $test_premission_result = mysql_fetch_assoc($test_premission);
    // Abteilungsleiter-ID auslesen und mit der aktuellen Nutzer-ID vergleichen
    if ($test_premission_result['abteilungsleiter'] != $user['mitglieder_id']){
        // Der aktuelle Nutzer ist auch nicht der Abteilungsleiter = Keine Erlaubnis
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
  list($errors,$input) = verify_abteilungsdaten($input);
  if (count($errors) == 0){
      // Gab es keine Fehler, dann SQL-Befehl erstellen
      $sql =
          "UPDATE `abteilungen` SET
           `name`            = '".mysql_real_escape_string(filter_var($input['name'],FILTER_SANITIZE_STRING))."',
           `beschreibung`    = '".mysql_real_escape_string(filter_var($input['beschreibung'],FILTER_SANITIZE_STRING))."',
           `homepage`        = '".mysql_real_escape_string($input['homepage'])."',";
           if ($input['abteilungsleiterid'] > 0){
             $sql .= "`abteilungsleiter`= '".mysql_real_escape_string(intval($input['abteilungsleiterid']))."',";
           }
           $sql .= "`aktivenumlage`   = '".intval(($input['aktumleuro']*100 + $input['aktumlcent']))."'
         WHERE
           `abteilungen`.`abteilungs_id` = '".intval($input['abteilungs_id'])."'";
         // MySQL Code ausführen (und somit Änderungen speichern)
         if (mysql_query($sql)){
            // Bei Erfolg entsprechendes als GET-Parameter übergeben
            header('Location: abteilung_edit.php?id='.intval($input['abteilungs_id'])."&save=success");
            exit();
         }else{
            // Bei einem Fehler die Mysql-Fehlernummer zur Fehleruntersuchung mitsenden
            header('Location: abteilung_edit.php?id='.intval($input['abteilungs_id'])."&save=fail&why=".mysql_errno());
            exit();
         }
  }else{
    // Bei der Prüfung der Daten sind Fehler aufgetreten
    header('Location: abteilung_edit.php?id='.intval($input['abteilungs_id']).'&save=fail&why=data&errors='.base64_encode(json_encode($errors)).'&data='.base64_encode(json_encode($input)));
    exit();
  }
}elseif (isset($_POST['addabteilung'])){
  // Ein neuen Abteilung in die DB aufnehmen
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
  list($errors,$input) = verify_abteilungsdaten($_POST);
  if (count($errors) == 0){
    // Es gab keine Fehler bei der Prüfung der Eingaben
      $sql =
          "INSERT INTO `abteilungen` SET
           `name`            = '".mysql_real_escape_string(filter_var($input['name'],FILTER_SANITIZE_STRING))."',
           `homepage`        = '".mysql_real_escape_string($input['homepage'])."',
           `beschreibung`    = '".mysql_real_escape_string(filter_var($input['beschreibung'],FILTER_SANITIZE_STRING))."',
           `abteilungsleiter`= '".mysql_real_escape_string(intval($input['abteilungsleiterid']))."',
           `aktivenumlage`   = '".intval(($input['aktumleuro']*100 + $input['aktumlcent']))."',
           `status`          = '1'";
         // MySQL Code ausführen (und somit Änderungen speichern)
         if (mysql_query($sql)){
            // Bei Erfolg entsprechendes als GET-Parameter übergeben
            header('Location: abteilung_show.php?id='.mysql_insert_id()."&created");
            exit();
         }else{
            // Bei einem Fehler die Mysql-Fehlernummer zur Fehleruntersuchung mitsenden
            header('Location: abteilung_add.php?created=fail&why='.mysql_errno()."&data=".base64_encode(json_encode($input)));
            exit();
         }
  }else{
    // Bei der Prüfung der Daten sind Fehler aufgetreten
    header('Location: abteilung_add.php?created=fail&why=data&errors='.base64_encode(json_encode($errors)).'&data='.base64_encode(json_encode($input)));
    exit();
  }
}elseif (isset($_POST['deleteabteilung'])){
  // Vorhandenen Abteilung Löschen bzw. als im Papierkorb markieren
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
    $zugehoerigkeiten_sql = "DELETE FROM `abteilungszugehoerigkeit`
                    WHERE `abteilungszugehoerigkeit`.`abteilung` = ".intval($_POST['abteilungs_id']);
    if (mysql_query($zugehoerigkeiten_sql)){
        if ($_POST['dbdelete'] == 2 && $user['rights'] >= 5){
          // Abteilung soll auch aus der DB gelöscht werden und der Nutzer hat die Berechtigung dazu
          $sql = "DELETE FROM `abteilungen`
                WHERE
               `abteilungen`.`abteilungs_id` = '".intval($_POST['abteilungs_id'])."' AND `abteilungen`.`status` = 1";
          if (mysql_query($sql)){
             // Bei Erfolg entsprechendes als GET-Parameter übergeben
             header('Location: abteilungen.php?deletion=success&who='.intval($_POST['abteilungs_id']));
             exit();
          }else{
             // Bei einem Fehler die Mysql-Fehlernummer zur Fehleruntersuchung mitsenden
             header('Location: abteilung_delete.php?id='.intval($_POST['abteilungs_id'])."&deletion=fail&why=".mysql_errno());
             exit();
          }
        }else{
          // Ort normal als "im Papierkorb" markieren
          $sql = "UPDATE `abteilungen` SET
               `status`        = '2'
             WHERE
               `abteilungen`.`abteilungs_id` = '".intval($_POST['abteilungs_id'])."' AND `abteilungen`.`status` = 1";
          if (mysql_query($sql)){
             // Bei Erfolg entsprechendes als GET-Parameter übergeben
             header('Location: abteilungen.php?removal=success&who='.intval($_POST['abteilungs_id']));
             exit();
          }else{
             // Bei einem Fehler die Mysql-Fehlernummer zur Fehleruntersuchung mitsenden
             header('Location: abteilung_delete.php?id='.intval($_POST['abteilungs_id'])."&removal=fail&why=".mysql_errno());
             exit();
          }
        }
    }else{
         // Bei einem Fehler die Mysql-Fehlernummer zur Fehleruntersuchung mitsenden
         header('Location: abteilung_delete.php?id='.intval($_POST['abteilungs_id'])."&memberdelete=fail&why=".mysql_errno());
         exit();
    }
  }else{
    // Wirklich löschen wurde mit Nein beantwortet
    header('Location: abteilung_show.php?id='.intval($_POST['abteilungs_id']));
    exit();
  }
}elseif (isset($_POST['addabteilungsmember'])){
  // Mitglied soll zu einer Abteilung zugeordnet werden
  // Hat der aktuelle Nutzer die Rechte dazu?
  if ($user['rights'] < 4){
    // Man hat schonmal ein Rechtelevel von kleiner 4
    $sql_test_premission = "SELECT `abteilungsleiter` FROM `abteilungen` WHERE
                  `abteilungen`.`abteilungs_id` = ".intval($_POST['abteilungs_id'])."";
    $test_premission = mysql_query($sql_test_premission);
    $test_premission_result = mysql_fetch_assoc($test_premission);
    // Abteilungsleiter-ID auslesen und mit der aktuellen Nutzer-ID vergleichen
    if ($test_premission_result['abteilungsleiter'] != $user['mitglieder_id']){
        // Der aktuelle Nutzer ist auch nicht der Abteilungsleiter = Keine Erlaubnis
        if (isset($_SERVER["HTTP_REFERER"])){
          $referer = "?before=".base64_encode($_SERVER["HTTP_REFERER"]);
        }else{
          $referer = "";
        }
        header("Location: norights.php$referer");
        exit();
    }
  }
  // Fehlerarray erstellen
  $errors = array();
  // Zunächst versuchen die mitglieder_id des Mitglieds zu bekommen
  if (!empty($_POST['mitgliedsnummer'])){
        // Es wurde eine Mitgliedsnummer eingegeben
        $sql = "SELECT `mitglieder_id` FROM `mitglieder` WHERE `mitgliedsnummer` = '".mysql_real_escape_string($_POST['mitgliedsnummer'])."' AND `status` = 1";
        $mitglieder = mysql_query($sql);
        if (mysql_num_rows($mitglieder) == 1){
          // Es wurde eine Übereinstimmung gefunden
          $mitglied = mysql_fetch_assoc($mitglieder);
        }elseif(mysql_num_rows($mitglieder) == 0){
          $errors[] = "Es wurde kein Mitglied mit der angegebenen Mitgliedsnummer gefunden";
        }else{
          // Eigentlich unmöglicher Fehler, da Mitgliedsnummern eindeutig sind
          $errors[] = "Es wurde mehrere Mitglied mit der angegebenen Mitgliedsnummer gefunden";
        }
  }elseif (!empty($_POST['mitgliedsname']) || (isset($_POST['mitgliedsnamedrop']) && $_POST['mitgliedsnamedrop'] != "new")){
          // Es wurde ein Mitgliedsname eingegeben
        if (isset($_POST['mitgliedsnamedrop']) && $_POST['mitgliedsnamedrop'] != "new" && empty($_POST['mitgliedsname'])){
          // Dem Nutzer wurde ein Auswahlfeld angezeigt und das Textfeld ist leer (= Keine Eingabe über "Anderer Nutzer", es wurde also jemand aus der Liste gewählt)
          // Prüfung: Ist der Nutzer vorhanden und aktiv?
          $mitglieds_test = mysql_query("SELECT * FROM `mitglieder` WHERE `mitglieder_id` = ".intval($_POST['mitgliedsnamedrop'])." AND `status` = 1");
          if (mysql_num_rows($mitglieds_test) == 0){
            $errors[] = "Es wurde kein Mitglied mit dem angegebenen Namen gefunden";
          }else{
            // Es ist ein Mitglied vorhanden
            $mitglied = mysql_fetch_assoc($mitglieds_test);
          }
        }elseif(!empty($_POST['mitgliedsname'])){
          // Es wurde ein Name als String übergeben, anhand des Namens das Mitglied finden
          $namensteile = explode(" ",$_POST['mitgliedsname']);
          $suchteile = array(); // Suchteile Array erstellen, welches alle Suchkriterien (Textteile des Namens) enthält
          foreach ($namensteile as &$suchteil) {
            $suchteile[] = "(`nachname` LIKE '%".$suchteil."%' OR `vorname` LIKE '%".$suchteil."%')";
          }
          $suchteile[] = "`status` = 1"; // Nur aktive Mitglieder verwenden
          $mitglieds_test = mysql_query("SELECT * FROM `mitglieder` WHERE ".implode(" AND ",$suchteile));
          if (mysql_num_rows($mitglieds_test) == 1){
              // Es gibt genau einen Nutzer mit dem Namen
              $mitglied = mysql_fetch_assoc($mitglieds_test);
          }elseif (mysql_num_rows($mitglieds_test) == 0){
            $errors[] = "Es wurde kein Mitglied mit dem angegebenen Namen gefunden";
          }else{
            $errors[] = "Es wurden mehrere Mitglieder mit dem angegebenen Namen gefunden";
            if (!isset($_POST['mitgliedsnamedrop'])){
              // Es wird nur eine Selectbox bei der Datenübergaben mit Prefil angezeigt, wenn das Feld "mitgliedsnamedrop" ebenfalls übergeben wird. Daher hier dieses erzeugen, wenn es nicht vorhanden ist
              $_POST['mitgliedsnamedrop'] = "";
            }
          }
        }else{
          // Kein Auswahlfeld und Textfeld ist leer oder sonstige fehlerhafte Wertübergabe = Kein Abteilungsleiter angegeben
          $errors[] = "Es wurden kein Mitgliedsname eingegeben";
        }
  }else{
    $errors[] = "Es wurde weder ein Mitgliedsname noch eine Mitgliedsnummer eingegeben";
  }
  if (isset($mitglied) && count($errors) == 0){
      if ($_POST['type'] == 0){
        $aktiv = 0;
      }else{
        $aktiv = 1;
      }
      $sql = "INSERT INTO `abteilungszugehoerigkeit` SET
           `abteilung`       = '".mysql_real_escape_string(intval($_POST['abteilungs_id']))."',
           `mitglied`        = '".mysql_real_escape_string($mitglied['mitglieder_id'])."',
           `aktiv`           = '".mysql_real_escape_string($aktiv)."',
           `beitrittdate`    = '".mysql_real_escape_string(date("Y-m-d"))."'";
      if (mysql_query($sql)){
        // Hinzufügen war erfolgreich
        if ($_POST['afteradd'] == 1){
             // Nach dem Hinzufügen weitere Mitgliedschaft hinzufügen
             header('Location: abteilung_mitgliedschaft_add.php?id='.intval($_POST['abteilungs_id'])."&add=success&who=".intval($mitglied['mitglieder_id'])."&return");
             exit();
         }elseif ($_POST['afteradd'] == 2){
             // Nach dem Hinzufügen zurück zur Übersicht
             header('Location: abteilung_mitglieder.php?id='.intval($_POST['abteilungs_id'])."&add=success&who=".intval($mitglied['mitglieder_id']));
             exit();
        }else{
             // Nach dem Hinzufügen zur Detailseite
             header('Location: abteilung_mitgliedschaft_show.php?id='.intval($_POST['abteilungs_id'])."&add=success&who=".intval($mitglied['mitglieder_id']));
             exit();
        }
      }else{
         // Bei einem Fehler die Mysql-Fehlernummer zur Fehleruntersuchung mitsenden
         header('Location: abteilung_mitgliedschaft_add.php?id='.intval($_POST['abteilungs_id'])."&add=fail&why=".mysql_errno());
         exit();
      }

    }else{
        // Bei der Prüfung der Daten sind Fehler aufgetreten oder es wurde  kein Mitglied gefunden
        header('Location: abteilung_mitgliedschaft_add.php?id='.intval($_POST['abteilungs_id']).'&add=fail&why=data&errors='.base64_encode(json_encode($errors)).'&data='.base64_encode(json_encode($_POST)));
        exit();
    }
}elseif (isset($_POST['saveabteilungsmember'])){
  // Abteilungsmitgliedschaft wurde geändert
  // Hat der aktuelle Nutzer die Rechte dazu?
  if ($user['rights'] < 4){
    // Man hat schonmal ein Rechtelevel von kleiner 4
    $sql_test_premission = "SELECT `abteilungsleiter` FROM `abteilungen` WHERE
                  `abteilungen`.`abteilungs_id` = ".intval($_POST['abteilungs_id'])."";
    $test_premission = mysql_query($sql_test_premission);
    $test_premission_result = mysql_fetch_assoc($test_premission);
    // Abteilungsleiter-ID auslesen und mit der aktuellen Nutzer-ID vergleichen
    if ($test_premission_result['abteilungsleiter'] != $user['mitglieder_id']){
        // Der aktuelle Nutzer ist auch nicht der Abteilungsleiter = Keine Erlaubnis
        if (isset($_SERVER["HTTP_REFERER"])){
          $referer = "?before=".base64_encode($_SERVER["HTTP_REFERER"]);
        }else{
          $referer = "";
        }
        header("Location: norights.php$referer");
        exit();
    }
  }
  if ($_POST['type'] == 0){
    $aktiv = 0;
  }else{
    $aktiv = 1;
  }
  $sql =
      "UPDATE `abteilungszugehoerigkeit` SET
       `aktiv`            = '".mysql_real_escape_string($aktiv)."'
     WHERE
       `abteilungszugehoerigkeit`.`abteilung` = '".intval($_POST['abteilungs_id'])."' AND `abteilungszugehoerigkeit`.`mitglied` = '".intval($_POST['mitglieder_id'])."'";
     // MySQL Code ausführen (und somit Änderungen speichern)
     if (mysql_query($sql)){
        // Bei Erfolg entsprechendes als GET-Parameter übergeben
        header('Location: abteilung_mitgliedschaft_edit.php?id='.intval($_POST['abteilungs_id'])."&who=".intval($_POST['mitglieder_id'])."&save=success");
        exit();
     }else{
        // Bei einem Fehler die Mysql-Fehlernummer zur Fehleruntersuchung mitsenden
        header('Location: abteilung_mitgliedschaft_edit.php?id='.intval($_POST['abteilungs_id'])."&who=".intval($_POST['mitglieder_id'])."&save=fail&why=".mysql_errno());
        exit();
     }
}elseif (isset($_POST['deleteabteilungsmember'])){
  // Abteilungsmitgliedschaft wurde geändert
  // Hat der aktuelle Nutzer die Rechte dazu?
  if ($user['rights'] < 4){
    // Man hat schonmal ein Rechtelevel von kleiner 4
    $sql_test_premission = "SELECT `abteilungsleiter` FROM `abteilungen` WHERE
                  `abteilungen`.`abteilungs_id` = ".intval($_POST['abteilungs_id'])."";
    $test_premission = mysql_query($sql_test_premission);
    $test_premission_result = mysql_fetch_assoc($test_premission);
    // Abteilungsleiter-ID auslesen und mit der aktuellen Nutzer-ID vergleichen
    if ($test_premission_result['abteilungsleiter'] != $user['mitglieder_id']){
        // Der aktuelle Nutzer ist auch nicht der Abteilungsleiter = Keine Erlaubnis
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
       $sql = "DELETE FROM `abteilungszugehoerigkeit`
              WHERE
             `abteilungszugehoerigkeit`.`abteilung` = '".intval($_POST['abteilungs_id'])."' AND `abteilungszugehoerigkeit`.`mitglied` = ".intval($_POST['mitglieder_id']);
        if (mysql_query($sql)){
           // Bei Erfolg entsprechendes als GET-Parameter übergeben
           header('Location: abteilung_mitglieder.php?deletion=success&id='.intval($_POST['abteilungs_id'])."&who=".intval($_POST['mitglieder_id']));
           exit();
        }else{
           // Bei einem Fehler die Mysql-Fehlernummer zur Fehleruntersuchung mitsenden
           header('Location: abteilung_mitgliedschaft_delete.php?id='.intval($_POST['abteilungs_id'])."&who=".intval($_POST['mitglieder_id'])."&deletion=fail&why=".mysql_errno());
           exit();
        }
  }else{
    // Wirklich löschen wurde mit Nein beantwortet
    header('Location: abteilung_mitgliedschaft_show.php?id='.intval($_POST['abteilungs_id'])."&who=".intval($_POST['mitglieder_id']));
    exit();
  }

}


// Weiterleitung, falls aus irgendwelchen Gründen diese Datei aufgerufen wird, ohne, dass etwas übergeben wird
header('Location: abteilungen.php');
?>