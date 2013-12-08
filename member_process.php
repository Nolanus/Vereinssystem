<?php
// Verarbeitet Speicher- und Erstellungsprozesse von Mitgliedern

// Datenbankverbindung & Kodierung einbinden
require_once("inc/connect.inc.php");

// Login-Status-Prüfung einbinden
require_once("inc/checkuser.inc.php");

function verify_mitgliedsdaten($input, $user = array("rights" => 1,"mitglieder_id"=>0)){
  // Prüft die übergebenen Mitgliedsaten auf Fehler und Plausibilität
  // Gibt eine Array bestehend aus einem array errors mit den Fehlern sowie die verbesserten input Daten zurück (mehrdimensionales Array)
  $errors = array();
  if (preg_match("/^[a-zäöüß \.-]{2,}$/i", $input['vorname']) != 1){
    // Vornamen dürfen nur aus Buchstaben, Bindestrichen, Leerzeichen und Punkten bestehen und müssen mindestens 2 Zeichen lang sein
    $errors[] = "Der eingegebene Vorname ist ungültig";
  }
  if (preg_match("/^[a-zäöüß \.-]{2,}$/i", $input['nachname']) != 1){
    // Nachnamen dürfen nur aus Buchstaben, Bindestrichen, Leerzeichen und Punkten bestehen und müssen mindestens 2 Zeichen lang sein
    $errors[] = "Der eingegebene Nachname ist ungültig";
  }
  if ($input['geschlecht'] < 0 && $input['geschlecht'] > 2){
    // Geschlechts-Nummer ist außerhalb des gültigen Rahmens von 0 bis 2
    $errors[] = "Das ausgewählte Geschlecht ist ungültig";
  }
  $timestampbday = mktime(0,0,0,intval($input['geburtstagmonth']),intval($input['geburtstagday']),intval($input['geburtstagyear']));
  if (checkdate(intval($input['geburtstagmonth']),intval($input['geburtstagday']),intval($input['geburtstagyear'])) == false || $timestampbday > time() || intval($input['geburtstagyear']) < 1900 ){
    // Geburtstag auf Gültigkeit prüfen (30. Februar z.B. zurückweisen) und auf plausibilität (in der Vergangenheit und nicht vor 1900)
    $errors[] = "Das eingegebene Geburtsdatum ist ungültig";
  }
  // Prüfung der Eltern
  if (isset($input['mutter'])){
      // Nur weiteres Prüfen, falls auch die Felder übermittelt wurden. Normale Nutzer senden diese Felder nicht mit, dass darf dann nicht als "Feld leer" = Keine Eltern speichern gewertet werden
      if (isset($input['mutterdrop']) && $input['mutterdrop'] != "new" && empty($input['mutter'])){
        // Dem Nutzer wurde ein Auswahlfeld angezeigt und das Textfeld ist leer (= Keine Eingabe über "Anderer Nutzer", es wurde also jemand aus der Liste gewählt)
        // Prüfung: Ist der Nutzer vorhanden und aktiv?
        $mutter_test = mysql_query("SELECT * FROM `mitglieder` WHERE `mitglieder_id` = ".intval($input['mutterdrop'])." AND `status` = 1");
        if (mysql_num_rows($mutter_test) == 0){
          $errors[] = "Es wurde kein Mitglied mit dem angegebenen Namen der Mutter gefunden";
        }else{
          // Es ist ein Mitglied vorhanden
          $mutter_daten = mysql_fetch_assoc($mutter_test);
          $input['parent1'] = $mutter_daten['mitglieder_id'];
        }
      }elseif(!empty($input['mutter'])){
        // Es wurde ein Name als String übergeben, anhand des Namens das Mitglied finden
        $namensteile = explode(" ",$input['mutter']);
        $suchteile = array(); // Suchteile Array erstellen, welches alle Suchkriterien (Textteile des Namens) enthält
        foreach ($namensteile as &$suchteil) {
          $suchteile[] = "(`nachname` LIKE '%".$suchteil."%' OR `vorname` LIKE '%".$suchteil."%')";
        }
        $suchteile[] = "`status` = 1"; // Nur aktive Mitglieder verwenden
        $mutter_test = mysql_query("SELECT * FROM `mitglieder` WHERE ".implode(" AND ",$suchteile));
        if (mysql_num_rows($mutter_test) == 1){
            // Es gibt genau einen Nutzer mit dem Namen
            $mutter_daten = mysql_fetch_assoc($mutter_test);
            $input['parent1'] = $mutter_daten['mitglieder_id'];
        }elseif (mysql_num_rows($mutter_test) == 0){
          $errors[] = "Es wurde kein Mitglied mit dem angegebenen Namen der Mutter gefunden";
        }else{
          $errors[] = "Es wurden mehrere Mitglieder mit dem angegebenen Namen der Mutter gefunden";
          if (!isset($input['mutterdrop'])){
            // Es wird nur eine Selectbox bei der Datenübergaben mit Prefil angezeigt, wenn das Feld "mutterdrop" ebenfalls übergeben wird. Daher hier dieses erzeugen, wenn es nicht vorhanden ist
            $input['mutterdrop'] = "";
          }
        }
      }else{
        // Kein Auswahlfeld und Textfeld ist leer oder sonstige fehlerhafte Wertübergabe = Keine Mutter soll gespeichert werden
        $input['parent1'] = 0;
      }
      if (isset($mutter_daten)){
        // Wurde ein Datensatz einer Möglichen Mutter gefunden? Dann aus plausibilität prüfen
        if (strtotime($mutter_daten['geburtstag']) > $timestampbday ){
          // Die Mutter ist jünger als das Kind = Fehler
          $errors[] = "Die angegebene Mutter ist jünger als das Kind";
        }
        if (abs(strtotime($mutter_daten['geburtstag']) - $timestampbday) < 315569260 ){
          // Die Mutter ist keine 10 Jahre älter als das Kind = Fehler
          $errors[] = "Die angegebene Mutter ist weniger als 10 Jahre älter und damit zu jung";
        }
        if ($mutter_daten['geschlecht'] == 1){
          // Die Mutter ist ein Mann
          $errors[] = "Die angegebene Mutter ist männlich. Bitte diese Person als Vater eintragen";
        }
      }
  }
  if (isset($input['vater'])){
      // Nur weiteres Prüfen, falls auch die Felder übermittelt wurden. Normale Nutzer senden diese Felder nicht mit, dass darf dann nicht als "Feld leer" = Keine Eltern speichern gewertet werden
      if (isset($input['vaterdrop']) && $input['vaterdrop'] != "new" && empty($input['vaterdrop'])){
        // Dem Nutzer wurde ein Auswahlfeld angezeigt und das Textfeld ist leer (= Keine Eingabe über "Anderer Nutzer", es wurde also jemand aus der Liste gewählt)
        // Prüfung: Ist der Nutzer vorhanden und aktiv?
        $vater_test = mysql_query("SELECT * FROM `mitglieder` WHERE `mitglieder_id` = ".intval($input['vaterdrop'])." AND `status` = 1");
        if (mysql_num_rows($vater_test) == 0){
          $errors[] = "Es wurde kein Mitglied mit dem angegebenen Namen des Vaters gefunden";
        }else{
          // Es ist ein Mitglied vorhanden
          $vater_daten = mysql_fetch_assoc($vater_test);
          $input['parent2'] = $vater_daten['mitglieder_id'];
        }
      }elseif(!empty($input['vater'])){
        // Es wurde ein Name als String übergeben, anhand des Namens das Mitglied finden
        $namensteile = explode(" ",$input['vater']);
        $suchteile = array(); // Suchteile Array erstellen, welches alle Suchkriterien (Textteile des Namens) enthält
        foreach ($namensteile as &$suchteil) {
          $suchteile[] = "(`nachname` LIKE '%".$suchteil."%' OR `vorname` LIKE '%".$suchteil."%')";
        }
        $suchteile[] = "`status` = 1"; // Nur aktive Mitglieder verwenden
        $vater_test = mysql_query("SELECT * FROM `mitglieder` WHERE ".implode(" AND ",$suchteile));
        if (mysql_num_rows($vater_test) == 1){
            // Es gibt genau einen Nutzer mit dem Namen
            $vater_daten = mysql_fetch_assoc($vater_test);
            $input['parent2'] = $vater_daten['mitglieder_id'];
        }elseif (mysql_num_rows($vater_test) == 0){
          $errors[] = "Es wurde kein Mitglied mit dem angegebenen Namen des Vaters gefunden";
        }else{
          $errors[] = "Es wurden mehrere Mitglieder mit dem angegebenen Namen des Vaters gefunden";
          if (!isset($input['vaterdrop'])){
            // Es wird nur eine Selectbox bei der Datenübergaben mit Prefil angezeigt, wenn das Feld "vaterdrop" ebenfalls übergeben wird. Daher hier dieses erzeugen, wenn es nicht vorhanden ist
            $input['vaterdrop'] = "";
          }
        }
      }else{
        // Kein Auswahlfeld und Textfeld ist leer oder sonstige fehlerhafte Wertübergabe = Kein Vater soll gespeichert werden
        $input['parent2'] = 0;
      }
      if (isset($vater_daten)){
        // Wurde ein Datensatz eines Möglichen Vaters gefunden? Dann aus plausibilität prüfen
        if (strtotime($vater_daten['geburtstag']) > $timestampbday ){
          // Der Vater ist jünger als das Kind = Fehler
          $errors[] = "Der angegebene Vater ist jünger als das Kind";
        }
        if (abs(strtotime($vater_daten['geburtstag']) - $timestampbday) < 315569260 ){
          // Der Vater ist keine 10 Jahre älter als das Kind = Fehler
          $errors[] = "Der angegebene Vater ist weniger als 10 Jahre älter und damit zu jung";
        }
        if ($vater_daten['geschlecht'] == 2){
          // Der Vater ist eine Frau
          $errors[] = "Der angegebene Vater ist weiblich. Bitte diese Person als Mutter eintragen";
        }
      }
      if (!empty($input['parent1']) && !empty($input['parent2']) && $input['parent1'] == $input['parent2']){
        $errors[] = "Die angegebenen Elternteile sind ein und die selbe Person";
      }
  }
  // Prüfung der Ortsdaten
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
  if (!empty($input['hdyvorwahl']) && !empty($input['handynr']) && (preg_match("/^[0-9\+]{3,}$/", $input['hdyvorwahl']) != 1 && preg_match("/^[0-9]{4,}$/", $input['handynr']) != 1 )){
    // Handynummern dürfen nur aus Ziffern und einem eventuellen Plus bestehen (Alternativ sind beide String empty/leer, wenn keine Handynr. angegeben wird
    $errors[] = "Die eingegebene Handynummer ist ungültig";
  }else{
    // Handynummer ist gültig, String für den MySQL Befehl erstellen
    if (empty($input['hdyvorwahl']) && empty($input['handynr'])){
      $input['handynummer'] = "";
    }else{
      $input['handynummer'] = $input['hdyvorwahl']."/".$input['handynr'];
    }
  }
  // E-Mail Adresse sanitizen/reinigen
  $input['email'] = filter_var($input['email'], FILTER_SANITIZE_EMAIL);
  if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL) && !empty($input['email'])) {
    // Prüfen, ob es eine gültige E-Mail-Adresse und nicht leer ist (kleine Fehler werden automatisch verbessert; leer wäre auch erlaubt)
    $errors[] = "E-Mail Adresse ungültig";
  }
  if ($input['mitgliedschaft'] < 0 && $input['mitgliedschaft'] > 2){
    // Mitgliedsart-Nummer ist außerhalb des gültigen Rahmens von 0 bis 2
    $errors[] = "Die ausgewählte Mitgliedsart ist ungültig";
  }
  $timestampbeitritt = mktime(0,0,0,intval($input['beitrittmonth']),intval($input['beitrittday']),intval($input['beitrittyear']));
  if (checkdate(intval($input['beitrittmonth']),intval($input['beitrittday']),intval($input['beitrittyear'])) == false || $timestampbeitritt > time() || intval($input['beitrittyear']) < 1900 || $timestampbday > $timestampbeitritt ){
    // Beitrittsdatum auf Gültigkeit prüfen (30. Februar z.B. zurückweisen) und auf plausibilität (in der Vergangenheit, nicht vor 1900 und nach dem Geburtstag)
    $errors[] = "Das eingegebene Beitrittsdatum ist ungültig";
  }
  if ($input['abrechnung'] < 0 && $input['abrechnung'] > 1){
    // Abrechnungsart-Nummer ist außerhalb des gültigen Rahmens von 0 bis 1
    $errors[] = "Die ausgewählte Abrechnungsart ist ungültig";
  }
  if (preg_match("/^[a-zäöüß \.-]{5,}$/i", $input['kontoinhaber']) != 1 && $input['abrechnung'] == 0){
    // Kontoinhaber dürfen nur aus Buchstaben, Bindestrichen, Leerzeichen und Punkten bestehen und müssen mindestens 5 Zeichen lang sein
    $errors[] = "Der eingegebene Kontoinhaber ist ungültig";
  }
  if (preg_match("/^[0-9]{5,10}$/i", $input['kontonummer']) != 1 && $input['abrechnung'] == 0){
    // Kontonummern dürfen nur aus Ziffern bestehen und müssen 5-10 Zeichen lang sein,
    $errors[] = "Die eingegebene Kontonummer ist ungültig";
  }
  if (preg_match("/^[0-9]{8}$/i", $input['blz']) != 1 && $input['abrechnung'] == 0){
    // Bankleitzahlen dürfen nur aus Ziffern bestehen und müssen 8 Zeichen lang sein,
    $errors[] = "Die eingegebene Bankleitzahl ist ungültig";
  }elseif($input['bankname'] == "" && $input['abrechnung'] == 0){
    // BLZ ist gültig und Bankname ist nicht vorhanden
    // Versuchen, falls kein Bankname angegeben ist, anhand der bekannten Kontoverbindungen den Banknamen mittels BLZ herauszufinden
    $sql_test = "SELECT `blz`,`bankname` FROM `mitglieder` WHERE `blz` = '".mysql_real_escape_string($input['blz'])."' GROUP BY `bankname`";
    $sql_result = mysql_query($sql_test);
    if (mysql_num_rows($sql_result) == 1){
      // Es gibt nur einen Ortsnamen, der zu dieser PLZ gefunden wurde (bei mehreren wird nichts übernommen, da es evtl. falsch sein könnte)
      $sql_test_data = mysql_fetch_assoc($sql_result);
      $input['bankname'] = $sql_test_data['bankname'];
    }
  }
  if (preg_match("/^[a-zäöüß -]{5,}$/i", $input['bankname']) != 1 && $input['abrechnung'] == 0){
    // Banknamen dürfen nur aus Buchstaben, Bindestrichen und Leerzeichen bestehen und müssen mindestens 5 Zeichen lang sein
    $errors[] = "Die eingegebene Bankname ist ungültig";
  }
  if (isset($input['rights']) && $input['rights'] < 0 && $input['rights'] > 5){
    // Abrechnungsart-Nummer ist außerhalb des gültigen Rahmens von 0 bis 5
    $errors[] = "Das ausgewählte Rechtelevel ist ungültig";
  }
  if (isset($input['username'])){
    if (preg_match("/^[a-z0-9\._-]{4,}$/i", $input['username']) != 1){
    // Username. Mindestens 4 Zeichen lang und aus buchstaben, zahlen, punkten und binde- und unterstrichen
    $errors[] = "Der eingegebene Username ist ungültig";
  }else{
    $sql_username_test = mysql_query("SELECT mitglieder_id FROM `mitglieder` WHERE `username` = '".$input['username']."'");
    if (mysql_num_rows($sql_username_test) != 0){
      // Nutzername wird bereits verwendet
      $errors[] = "Der eingegebene Username wird bereits verwendet";
    }
  }
  }
  // Wohnort
  if (count($errors) == 0){
        // Um unnötige Datenbankaktivität zu verhindern, prüfen wir den Wohnort nur, wenn alle anderen Eingaben korrekt sind
        // Die Daten ansich müssen wir nicht prüfen, da dies bereits weiter oben geschehen ist
        if ($input['locationchange'] == 4 && $user['rights'] >= 4){
           // Ortsänderung gilt für dieses Mitglied und alle Mitglieder, die ebenfalls dort wohnten = Orts-Eintrag verändern (+ entsprechende Rechte des Nutzers)
              $sql_ort =
                "UPDATE `orte` SET
                 `strasse`        = '".mysql_real_escape_string(filter_var($input['strasse'],FILTER_SANITIZE_STRING))."',
                 `hausnummer`      = '".mysql_real_escape_string(filter_var($input['hausnummer'],FILTER_SANITIZE_STRING))."',
                 `plz`      = '".mysql_real_escape_string(filter_var($input['plz'],FILTER_SANITIZE_STRING))."',
                 `ort`      = '".mysql_real_escape_string(filter_var($input['ort'],FILTER_SANITIZE_STRING))."',";
                 if ($input['telvorwahl'] > 0 && $input['telnr'] > 0){
                    $sql_ort .= "`telefon`      = '".mysql_real_escape_string(filter_var($input['telvorwahl']."/".$input['telnr'],FILTER_SANITIZE_STRING))."',";
                 }else{
                    $sql_ort .= "`telefon`      = '',";
                 }
              $sql_ort .= "
                `typ`        = '1'
              WHERE
               `orte`.`orts_id` = '".intval($input['orts_id'])."'";
              if (mysql_query($sql_ort)){
                // Orts-Eintrag erfolgreich geändert, am Eintrag in der Mitglieder-Tabelle muss nichts geändert werden, da die ID die gleiche bleibt
              }else{
                $errors[] = "Adressänderung konnte nicht übernommen werden";
              }
       }else{
         // Die anderen beiden Fälle ("Ortsänderung gilt nur für dieses Mitglied" und "Ortsänderung gilt für dieses Mitglied + Beziehungen")
         // Prüfen, ob ein Ort mit den angegebenen Daten vorhanden ist
         if ($input['telvorwahl'] > 0 && $input['telnr'] > 0){
           $searchtelefon = $input['telvorwahl']."/".$input['telnr'];
         }else{
           $searchtelefon = "";
         }
         $sql_ort = "SELECT `orts_id`,`status` FROM `orte` WHERE `strasse` = '".mysql_real_escape_string($input['strasse'])."' AND `hausnummer` = '".mysql_real_escape_string($input['hausnummer'])."' AND `plz` = '".mysql_real_escape_string($input['plz'])."' AND `ort` = '".mysql_real_escape_string($input['ort'])."' AND `telefon` = '".mysql_real_escape_string($searchtelefon)."'";
         $orte = mysql_query($sql_ort);
         if (mysql_num_rows($orte) == 1){
           // Es wurde ein Ort gefunden, der den Angaben entspricht
           // Werte dieses Eintrags in $ort einlesen (ist hier nur die orts_id)
           $ort = mysql_fetch_assoc($orte);
           if ($ort['status'] == 2){
                // Ist der Ort aus dem System entfernt worden, ihn wieder aktivieren und "createdby" neu setzen
                mysql_query("UPDATE `orte` SET `status` = '1', `createdby`='".$user['mitglieder_id']."' WHERE `orte`.`orts_id` = ".$ort['orts_id']);
           }
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
               `createdby`    = '".intval($user['mitglieder_id'])."',
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
         //****** Hier erfogt die eigentliche Entscheidung über die Behandlung der Adressänderung! ******
         // Der Fall 3 wird oben mittels Änderung der Ortseintrags behandelt
         // Die Änderung der Anschrift für das aktuelle bearbeitet-werdende Mitglied erfolgt unten. Hier eventuelle zusätzliche Mitglieder bearbeiten
         if ($input['locationchange'] == 1 && $user['rights'] >= 4){
           // Fall: "Ortsänderung gilt für dieses Mitglied + Eltern" (+ entsprechende Rechte des Nutzers)
           $sql_changeloc = mysql_query("UPDATE `mitglieder` SET
                       `anschrift` = '".$input['orts_id']."'
                     WHERE
                       `mitglieder`.`mitglieder_id` = '".$input['parent1']."' OR `mitglieder`.`mitglieder_id` = '".$input['parent2']."'");
           if (!$sql_changeloc){
             // Ist die MySQL Änderung fehlgeschlagen, eine Fehlermeldung ausgeben
             $errors[] = "Die Adressänderung konnte für die Eltern nicht übernommen werden (Error #".mysql_errno().")";
           }
         }elseif ($input['locationchange'] == 2){
           // Fall: "Ortsänderung gilt für dieses Mitglied + Kinder"
           $sql_changeloc = "UPDATE `mitglieder` SET
                       `anschrift` = '".$input['orts_id']."'
                     WHERE
                       (`mitglieder`.`parent1` = '".$input['mitglieder_id']."' OR `mitglieder`.`parent2` = '".$input['mitglieder_id']."')";
           if ($user['rights'] < 4){
             // Hat der aktuelle Nutzer ein Rechtelevel von kleiner als 4 = dann nur bei Kindern, die noch nicht 18 sind ändern!
             $sql_changeloc .= " AND `mitglieder`.`geburtstag` > '".date("Y-m-d",time()-568024668)."'";
           }
           $sql_changeloc = mysql_query($sql_changeloc);
           if (!$sql_changeloc){
             // Ist die MySQL Änderung fehlgeschlagen, eine Fehlermeldung ausgeben
             $errors[] = "Die Adressänderung konnte für die Kinder nicht übernommen werden (Error #".mysql_errno().")";
           }
         }elseif ($input['locationchange'] == 3 && $user['rights'] >= 4){
           // Fall: "Ortsänderung gilt für dieses Mitglied + Kinder + Eltern" (+ entsprechende Rechte des Nutzers)
           $sql_changeloc = mysql_query("UPDATE `mitglieder` SET
                       `anschrift` = '".$input['orts_id']."'
                     WHERE
                       `mitglieder`.`parent1` = '".$input['mitglieder_id']."' OR `mitglieder`.`parent2` = '".$input['mitglieder_id']."' OR `mitglieder`.`mitglieder_id` = '".$input['parent1']."' OR `mitglieder`.`mitglieder_id` = '".$input['parent2']."'");
           if (!$sql_changeloc){
             // Ist die MySQL Änderung fehlgeschlagen, eine Fehlermeldung ausgeben
             $errors[] = "Die Adressänderung konnte für die Kinder und Eltern nicht übernommen werden (Error #".mysql_errno().")";
           }
         }else{
           // Fall: "Ortsänderung gilt nur für dieses Mitglied" und bei ungültigen Werten für locationchange
           // Es muss hier nichts gemacht werden. Das speichern der Anschrift erfolgt unten
           // Ebenso wird der Ort nur beim aktuellen Nutzer geändert, wenn der übergebene Wert von locationchange manipuliert wurde
         }
       }
  }else{
    // Es sind ohnehin Fehler aufgetreten, also wird der Ort noch nicht geprüft. Damit $input['orts_id'] nicht unbestimmt ist, geben wir der Variable den Wert 0
    $input['orts_id'] = 0;
  }
  return array($errors,$input);
} // Ende der Funktion verify_mitgliedsdaten()


if (isset($_POST['savemember'])){
    // Ein Mitglied wurde verändert und muss in die DB gespeichert werden
    // Hat der aktuelle Nutzer die Rechte dazu?
    if ($user['rights'] < 4 && $_POST['mitglieder_id'] != $user['mitglieder_id']){
      // Man hat schonmal ein Rechtelevel von kleiner 4 und ist es nicht selber
      $sql_test_premission = "SELECT `mitglieder_id` FROM `mitglieder` WHERE
                    `mitglieder`.`mitglieder_id` = ".intval($_POST['mitglieder_id'])."
                    AND (`parent1` = ".$user['mitglieder_id']." OR `parent2` = ".$user['mitglieder_id'].")
                    AND `geburtstag` > '".date("Y-m-d",time()-568024668)."'";
      $test_premission = mysql_query($sql_test_premission);
      // Alle Nutzer auslesen, die die bestimmte Mitglieder_id haben, deren einer Elternteil der aktuelle Nutzer ist und die jünger als 18 sind
      if (mysql_num_rows($test_premission) != 1){
          // Der aktuelle Nutzer ist auch nicht der Vater oder die Mutter eines unter 18-jährigen = Keine Erlaubnis
          if (isset($_SERVER["HTTP_REFERER"])){
            $referer = "?before=".base64_encode($_SERVER["HTTP_REFERER"]);
          }else{
            $referer = "";
          }
          header("Location: norights.php$referer");
          exit();
      }
    }

    $input = $_POST;
    // Folgendes wendet trim auf jedes Element des Arrays an
    foreach ($input as $key=>$value) {
        $input[$key] = trim($value);
    }
    // Daten an die Prüfungsfunktion übergeben
    list($errors,$input) = verify_mitgliedsdaten($input, $user);
    if (count($errors) == 0){
        // Gab es keine Fehler, dann SQL-Befehl erstellen
        $bdaytimestamp = mktime(0,0,0,intval($input['geburtstagmonth']),intval($input['geburtstagday']),intval($input['geburtstagyear']));
        // Bisheriges Geburtsdatum aus der DB lesen
        $bdaychange_test = mysql_query("SELECT `geburtstag`,`mitgliedsnummer` FROM `mitglieder` WHERE `mitglieder_id` != '".intval($input['mitglieder_id'])."'");
        $bdaychange_data = mysql_fetch_assoc($bdaychange_test);
        // Prüfen, ob der Geburtstag geändert wurde, falls ja die Mitgliedsnummer neu berechnen
        if (strtotime($bdaychange_data['geburtstag']) != $bdaytimestamp){
            // Das aktuell übermittelte Geburtsdatum weicht vom bisherigen in der Datenbank ab = Mitgliedsnummer neu berechnen
            // dafür zählen, wie viele Personen am selben Tag Geburtstag haben, um die fortlaufende Nummer ermitteln zu können
            $sql_mtglnr = "SELECT count(*) as count FROM `mitglieder` WHERE `geburtstag` = '".date("Y-m-d",$bdaytimestamp)."'";
            $sql_mtglnr_data = mysql_query($sql_mtglnr);
            $sql_mtglnr_count = mysql_fetch_assoc($sql_mtglnr_data);
            $mitgliedsnummer = date("dmy",$bdaytimestamp).str_pad(intval($sql_mtglnr_count['count'])+1, 3, "0", STR_PAD_LEFT);
        }else{
            // Geburtsdatum wurde nicht geändert, Mitgliedsnummer nicht ändern (bisherigen Wert mit gleichem "überschreiben")
            $mitgliedsnummer = $bdaychange_data['mitgliedsnummer'];
        }

        $sql =
            "UPDATE `mitglieder` SET
            `vorname`           = '".mysql_real_escape_string(filter_var(stripslashes($input['vorname']),FILTER_SANITIZE_STRING))."',
            `nachname`          = '".mysql_real_escape_string(filter_var(stripslashes($input['nachname']),FILTER_SANITIZE_STRING))."',
            `geschlecht`        = '".mysql_real_escape_string(intval($input['geschlecht']))."',
            `geburtstag`        = '".mysql_real_escape_string(date('Y-m-d',$bdaytimestamp))."',
            `mitgliedsnummer`   = '".mysql_real_escape_string($mitgliedsnummer)."',";
            if (isset($input['parent1']) && intval($input['parent1']) >= 0 && $user['rights'] >= 4){
              // Es wurde eine Mutter definiert, sie ist nicht kleiner null und der aktuelle Nutzer hat die nötigen Rechte
              $sql .= "`parent1`  = '".intval($input['parent1'])."',";
            }
            if (isset($input['parent2']) && intval($input['parent2']) >= 0 && $user['rights'] >= 4){
              // Es wurde ein Vater definiert, der ist nicht kleiner null und der aktuelle Nutzer hat die nötigen Rechte
              $sql .= "`parent2`  = '".intval($input['parent2'])."',";
            }
            if ($input['orts_id'] > 0){
              // Sicherheitshalber nochmal prüfen, nicht damit ein Mitglied ohne Ort entstehen kann
              $sql .= "`anschrift`  = '".intval($input['orts_id'])."',";
            }
        $sql .= "
            `handy`             = '".mysql_real_escape_string($input['handynummer'])."',
            `email`             = '".mysql_real_escape_string($input['email'])."',";
        if (isset($input['mitgliedschaft']) && $user['rights'] >= 4){
            // Wert für Mitgliedschaft wurde mitgesendet und der Nutzer hat das Recht, diese Eigenschaft zu bearbeiten
            $sql .= "`mitgliedschaft`    = '".mysql_real_escape_string(intval($input['mitgliedschaft']))."',";
        }
        if (isset($input['beitrittmonth']) && isset($input['beitrittday']) &&  isset($input['beitrittyear']) && $user['rights'] >= 4){
            // Wert für Beitrittstag wurde mitgesendet und der Nutzer hat das Recht, diese Eigenschaft zu bearbeiten
            $sql .= "`beitritt`          = '".mysql_real_escape_string(date('Y-m-d',mktime(0,0,0,intval($input['beitrittmonth']),intval($input['beitrittday']),intval($input['beitrittyear']))))."',";
        }
        $sql .= "`abrechnung`        = '".intval($input['abrechnung'])."',
            `kontoinhaber`      = '".mysql_real_escape_string(filter_var(stripslashes($input['kontoinhaber']),FILTER_SANITIZE_STRING))."',
            `kontonummer`       = '".mysql_real_escape_string(filter_var($input['kontonummer'],FILTER_SANITIZE_STRING))."',
            `bankname`          = '".mysql_real_escape_string(filter_var($input['bankname'],FILTER_SANITIZE_STRING))."',
            `blz`               = '".mysql_real_escape_string(filter_var($input['blz'],FILTER_SANITIZE_STRING))."'
            WHERE
            `mitglieder`.`mitglieder_id` = '".intval($input['mitglieder_id'])."'";
            // MySQL Code ausführen (und somit Änderungen speichern)
            if (mysql_query($sql)){
                // Bei Erfolg entsprechendes als GET-Parameter übergeben
                header("Location: member_edit.php?id=".intval($input['mitglieder_id'])."&save=success");
                exit();
            }else{
                // Bei einem Fehler die Mysql-Fehlernummer zur Fehleruntersuchung mitsenden
                $goto = 'member_edit.php?id='.intval($input['mitglieder_id'])."&save=fail&why=".mysql_errno();
                if (count($errors) > 0){
                    // Gibt es im $errors-Array Einträge? Nur wenn ja, anhängen
                    $goto .= "&errors=".base64_encode(json_encode($errors));
                }
                $goto .= '&data='.base64_encode(json_encode($input));
                header("Location: $goto");
                exit();
            }
    }else{
        // Bei der Prüfung der Daten sind Fehler aufgetreten
        header('Location: member_edit.php?id='.intval($input['mitglieder_id']).'&save=fail&why=data&errors='.base64_encode(json_encode($errors)).'&data='.base64_encode(json_encode($input)));
        exit();
    }
}elseif (isset($_POST['savesystemmember'])){
    // Die Systemeinstellungen eines Mitglieds wurden verändert
    // Hat der aktuelle Nutzer die Rechte dazu?
    if ($user['rights'] < 4){
      // Man hat ein Rechtelevel von kleiner 4 = Keine Erlaubnis
      if (isset($_SERVER["HTTP_REFERER"])){
        $referer = "?before=".base64_encode($_SERVER["HTTP_REFERER"]);
      }else{
        $referer = "";
      }
      header("Location: norights.php$referer");
      exit();
    }
    $errors = array();
    if (preg_match("/^[a-zäöüß\.-]{5,}$/i", $_POST['usernamechange']) != 1){
      // Nutzernamen dürfen nur aus Buchstaben, Bindestrichen und Punkten bestehen und müssen mindestens 5 Zeichen lang sein
      $errors[] = "Der eingegebene Nutzername ist ungültig";
    }
    if ($_POST['rights'] > 5 || $_POST['rights'] < 0 ){
      // Ist das übergebene Rechtelevel außerhalb des Rahmens von 0-5
      $errors[] = "Das angegebene Rechtelevel ist ungültig";
    }
    if (count($errors) == 0){
        // $pword enthält den Boolean, ob das Passwort geändert wurde
        $pword = false;
        $sql =
            "UPDATE `mitglieder` SET
            `username`            = '".mysql_real_escape_string(filter_var($_POST['usernamechange'],FILTER_SANITIZE_STRING))."',";
            if ($_POST['passwortchange'] != ""){
              $sql .= "`passwort` = '".mysql_real_escape_string(md5($_POST['passwortchange']))."',";
              $pword = true;
            }
            $sql .= "`notizen`    = '".mysql_real_escape_string(filter_var(stripslashes($_POST['notizen']),FILTER_SANITIZE_STRING))."' ";
            if ($user['rights'] >= 5 || ($user['rights'] == 4 && intval($_POST['rights']) <= 4)){
              // Nur Admins dürfen die Rechtelevel von Nutzern ändern
              // oder der Nutzer hat level 4 und vergibt auch maximal dieses Rechtelevel
              $sql .= ", `rights`        = '".intval($_POST['rights'])."' ";
            }
            $sql .= "WHERE
            `mitglieder`.`mitglieder_id` = '".intval($_POST['mitglieder_id'])."'";
            if ($user['rights'] == 4){
              // Leute mit Rechtelevel 4 dürfen nichts von Admins ändern! Daher hier eine Enschränkung machen
              $sql .= " AND `mitglieder`.`rights` != 5";
            }

            // MySQL Code ausführen (und somit Änderungen speichern)
            if (mysql_query($sql)){
                // Bei Erfolg entsprechendes als GET-Parameter übergeben
                header("Location: member_systemedit.php?id=".intval($_POST['mitglieder_id'])."&save=success&pword=".strval($pword));
                exit();
            }else{
                // Bei einem Fehler die Mysql-Fehlernummer zur Fehleruntersuchung mitsenden
                $goto = 'member_systemedit.php?id='.intval($_POST['mitglieder_id'])."&save=fail&why=".mysql_errno();
                if (count($errors) > 0){
                    // Gibt es im $errors-Array Einträge? Nur wenn ja, anhängen
                    $goto .= "&errors=".base64_encode(json_encode($errors));
                }
                $goto .= '&data='.base64_encode(json_encode($_POST));
                header("Location: $goto");
                exit();
            }
    }else{
        // Bei der Prüfung der Daten sind Fehler aufgetreten
        header('Location: member_systemedit.php?id='.intval($_POST['mitglieder_id']).'&save=fail&why=data&errors='.base64_encode(json_encode($errors)).'&data='.base64_encode(json_encode($_POST)));
        exit();
    }
}elseif (isset($_POST['addmember'])){
    // Ein Mitglied wurde erstellt und muss in die DB aufgenommen werden
    // Hat der aktuelle Nutzer die Rechte dazu?
    if ($user['rights'] < 4){
      // Man hat ein Rechtelevel von kleiner 4 = Keine Erlaubnis
      if (isset($_SERVER["HTTP_REFERER"])){
        $referer = "?before=".base64_encode($_SERVER["HTTP_REFERER"]);
      }else{
        $referer = "";
      }
      header("Location: norights.php$referer");
      exit();
    }
    $input = $_POST;
    // Locationchange immitieren, da diese Option beim Neuanlegen irrelevant ist, von der Prüfungsfkt. jedoch benötigt wird
    $input['locationchange'] = 0;
    // Folgendes wendet trim auf jedes Element des Arrays an
    foreach ($input as $key=>$value) {
        $input[$key] = trim($value);
    }
    // Daten an die Prüfungsfunktion übergeben
    list($errors,$input) = verify_mitgliedsdaten($input, $user);
    if (count($errors) == 0){
        // Gab es keine Fehler, dann SQL-Befehl erstellen
        // Mitgliedsnummer erzeugen. Dazu zählen, wie viele Leute bereits an diesem Tag Geburtstag haben, um die fortlaufende Nummer zu erhalten (Status ist irrelevant!!!)
        $bdaytimestamp = mktime(0,0,0,intval($input['geburtstagmonth']),intval($input['geburtstagday']),intval($input['geburtstagyear']));
        $sql_mtglnr = "SELECT count(*) as count FROM `mitglieder` WHERE `geburtstag` = '".date("Y-m-d",$bdaytimestamp)."'";
        $sql_mtglnr_data = mysql_query($sql_mtglnr);
        $sql_mtglnr_count = mysql_fetch_assoc($sql_mtglnr_data);
        $mitgliedsnummer = date("dmy",$bdaytimestamp).str_pad(intval($sql_mtglnr_count['count'])+1, 3, "0", STR_PAD_LEFT);


        $sql =
            "INSERT INTO `mitglieder` SET
            `mitgliedsnummer`   = '$mitgliedsnummer',
            `vorname`           = '".mysql_real_escape_string(filter_var(stripslashes($input['vorname']),FILTER_SANITIZE_STRING))."',
            `nachname`          = '".mysql_real_escape_string(filter_var(stripslashes($input['nachname']),FILTER_SANITIZE_STRING))."',
            `geschlecht`        = '".mysql_real_escape_string(intval($input['geschlecht']))."',
            `geburtstag`        = '".mysql_real_escape_string(date('Y-m-d',$bdaytimestamp))."',";
            if (intval($input['parent1']) >= 0){
              $sql .= "`parent1`  = '".intval($input['parent1'])."',";
            }
            if (intval($input['parent2']) >= 0){
              $sql .= "`parent2`  = '".intval($input['parent2'])."',";
            }
            if ($input['orts_id'] > 0){
              // Sicherheitshalber nochmal prüfen, nicht damit ein Mitglied ohne Ort entstehen kann
              $sql .= "`anschrift`  = '".intval($input['orts_id'])."',";
            }
        $sql .= "
            `handy`             = '".mysql_real_escape_string($input['handynummer'])."',
            `email`             = '".mysql_real_escape_string($input['email'])."',
            `mitgliedschaft`    = '".mysql_real_escape_string(intval($input['mitgliedschaft']))."',
            `beitritt`          = '".mysql_real_escape_string(date('Y-m-d',mktime(0,0,0,intval($input['beitrittmonth']),intval($input['beitrittday']),intval($input['beitrittyear']))))."',
            `abrechnung`        = '".intval($input['abrechnung'])."',
            `kontoinhaber`      = '".mysql_real_escape_string(filter_var($input['kontoinhaber'],FILTER_SANITIZE_STRING))."',
            `kontonummer`       = '".mysql_real_escape_string(filter_var($input['kontonummer'],FILTER_SANITIZE_STRING))."',
            `bankname`          = '".mysql_real_escape_string(filter_var(stripslashes($input['bankname']),FILTER_SANITIZE_STRING))."',
            `blz`               = '".mysql_real_escape_string(filter_var($input['blz'],FILTER_SANITIZE_STRING))."',
            `username`          = '".mysql_real_escape_string(filter_var(stripslashes($input['username']),FILTER_SANITIZE_STRING))."',
            `rights`            = '0'
            ";
            // MySQL Code ausführen (und somit Änderungen speichern)
            if (mysql_query($sql)){
                // Bei Erfolg entsprechendes als GET-Parameter übergeben
                header("Location: member_show.php?id=".intval(mysql_insert_id())."&created");
                exit();
            }else{
                // Bei einem Fehler die Mysql-Fehlernummer zur Fehleruntersuchung mitsenden
                $goto = "member_add.php?created=fail&why=".mysql_errno();
                if (count($errors) > 0){
                    // Gibt es im $errors-Array Einträge? Nur wenn ja, anhängen
                    $goto .= "&errors=".base64_encode(json_encode($errors));
                }
                $goto .= '&data='.base64_encode(json_encode($input));
                header("Location: $goto");
                exit();
            }
    }else{
        // Bei der Prüfung der Daten sind Fehler aufgetreten
        header('Location: member_add.php?created=fail&why=data&errors='.base64_encode(json_encode($errors)).'&data='.base64_encode(json_encode($_POST)));
        exit();
    }
}elseif (isset($_POST['changemypword'])){
    // Das Passwort des aktuellen Nutzers soll geändert werden

    $input = $_POST;
    $errors = array();
    // Daten prüfen
    if ($user['passwort'] != md5($_POST['oldpword'])){
      $errors[] = "Das eingegebene bisheriges Passwort ist nicht korrekt";
    }
    if ($_POST['newpword'] != $_POST['newpword2']){
      $errors[] = "Die eingegebenen Passwörter sind nicht identisch";
    }
    if (strlen($_POST['newpword']) < 5){
      $errors[] = "Das neue Passwort ist zu kurz";
    }
    if (md5($_POST['newpword']) == $user['passwort']){
      $errors[] = "Das neue Passwort ist identisch zum bisherigen Passwort";
    }
    if (count($errors) == 0){
        // Gab es keine Fehler, dann SQL-Befehl erstellen
        $sql = "UPDATE `mitglieder` SET
        `passwort`  = '".mysql_real_escape_string(md5($input['newpword']))."'
        WHERE
        `mitglieder`.`mitglieder_id` = '".$user['mitglieder_id']."'
        ";
        // MySQL Code ausführen (und somit Änderungen speichern)
         if (mysql_query($sql)){
             // Bei Erfolg entsprechendes als GET-Parameter übergeben
             header("Location: member_changepword.php?save=success");
             exit();
         }else{
             // Bei einem Fehler die Mysql-Fehlernummer zur Fehleruntersuchung mitsenden
             $goto = "member_changepword.php?save=fail&why=".mysql_errno();
             if (count($errors) > 0){
                 // Gibt es im $errors-Array Einträge? Nur wenn ja, anhängen
                 $goto .= "&errors=".base64_encode(json_encode($errors));
             }
             $goto .= '&data='.base64_encode(json_encode($input));
             header("Location: $goto");
             exit();
         }
    }else{
        // Bei der Prüfung der Daten sind Fehler aufgetreten
        header('Location: member_changepword.php?save=fail&why=data&errors='.base64_encode(json_encode($errors)).'&data='.base64_encode(json_encode($_POST)));
        exit();
    }
}

// Weiterleitung, falls aus irgendwelchen Gründen diese Datei aufgerufen wird, ohne, dass etwas übergeben wird
header('Location: members.php');
?>