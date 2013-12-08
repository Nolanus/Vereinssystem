<?php
// Verarbeitet Speicher- und Erstellungsprozesse von Mitgliedern

// Datenbankverbindung & Kodierung einbinden
require_once ("inc/connect.inc.php");

// Login-Status-Pr�fung einbinden
require_once ("inc/checkuser.inc.php");

function verify_ortsdaten($input){
  $errors = array();
  if (strlen($input['ort_name']) < 3 && $input['ort_typ'] == 2){
    // Mindestlänge für Namen von Veranstaltungsorten ist 3 Zeichen
    $errors[] = "Der eingegebene Name des Veranstaltungsortes ist zu kurz";
  }
  if (preg_match("/^[a-zäöüß \.-]{4,}$/i", $input['ort_strasse']) != 1){
    // Straßennamen dürfen nur aus Buchstaben, Bindestrichen, Leerzeichen und Punkten bestehen und müssen mindestens 4 Zeichen lang sein
    $errors[] = "Der eingegebene Straßenname ist ungültig";
  }
  if (preg_match("/^[0-9]+[a-z0-9 -]*$/i", $input['ort_hausnummer']) != 1 && !empty($input['ort_hausnummer'])){
    // Hausnummern dürfen nur aus Buchstaben, Ziffern, Bindestrichen, Leerzeichen und Punkten bestehen, müssen jedoch min. mit einer Ziffer starten
    $errors[] = "Die eingegebene Hausnummer ist ungültig";
  }
  if (preg_match("/^[0-9]{5}$/", $input['ort_plz']) != 1){
    // PLZ dürfen nur aus Ziffern bestehen und müssen 5 Zeichen lang sein
    $errors[] = "Die eingegebene PLZ ist ungültig";
  }elseif($input['ort_ort'] == ""){
    // PLZ ist gültig und Ortsname ist nicht vorhanden
    // Versuchen, falls kein Ortsname angegeben ist, anhand der bekannten Orte den Ortsnamen mittels PLZ herauszufinden
    $sql_test = "SELECT `plz`,`ort` FROM `orte` WHERE `plz` = '".mysql_real_escape_string($input['ort_plz'])."' GROUP BY `ort`";
    $sql_result = mysql_query($sql_test);
    if (mysql_num_rows($sql_result) == 1){
      // Es gibt nur einen Ortsnamen, der zu dieser PLZ gefunden wurde (bei mehreren wird nichts übernommen, da es evtl. falsch sein könnte)
      $sql_test_data = mysql_fetch_assoc($sql_result);
      $input['ort_ort'] = $sql_test_data['ort'];
    }
  }
  if (preg_match("/^[a-zäöüß -]{3,}$/i", $input['ort_ort']) != 1){
    // Ortsnamen dürfen nur aus Buchstaben, Bindestrichen, Leerzeichen und Punkten bestehen und müssen mindestens 3 Zeichen lang sein
    $errors[] = "Die eingegebene Ortsname ist ungültig";
  }elseif($input['ort_plz'] == ""){
    // Ortsname ist gültig und PLZ ist nicht vorhanden
    // Versuchen, falls kein PLZ angegeben ist, anhand der bekannten Orte die PLZ mittels Ortsnamen herauszufinden
    $sql_test = "SELECT `plz`,`ort` FROM `orte` WHERE `ort` = '".mysql_real_escape_string($input['ort_ort'])."' GROUP BY `plz`";
    $sql_result = mysql_query($sql_test);
    if (mysql_num_rows($sql_result) == 1){
      // Es gibt nur eine PLZ zu diesem Ortsnamen (bei mehreren wird nichts übernommen, da es evtl. falsch sein könnte)
      $sql_test_data = mysql_fetch_assoc($sql_result);
      $input['ort_plz'] = $sql_test_data['plz'];
      // Da die fehlende PLZ eine Fehlermeldung erzeugt hat und dieses Problem nun gelöst wurde, muss die Fehlermeldung wieder aus dem $errors-Array entfernt werden
      // (Entfernt den letzten Eintrag des Arrays)
      unset($errors[count($errors)-1]);
    }
  }
  if (!empty($input['ort_telvorwahl']) && !empty($input['ort_telnr']) && (preg_match("/^[0-9\+]{3,}$/", $input['ort_telvorwahl']) != 1 && preg_match("/^[0-9]{4,}$/", $input['telnr']) != 1 )){
    // Telefonnummern dürfen nur aus Ziffern und einem eventuellen Plus bestehen (Alternativ sind beide String empty/leer, wenn keine Telefonnr. angegeben wird
    $errors[] = "Die eingegebene Telefonnummer ist ungültig";
  }
  if ($input['ort_typ'] > 2 || $input['ort_typ'] < 1){
    // Eingegebene Typennummer ist ungültig. Vermutlich Formular-Manipulation
    $errors[] = "Der eingegebene Ortstyp ist ungültig";
  }
  return array($errors,$input);
}

$input = $_POST;

//Soll Änderung gespeichert werden oder eine neue Veranstaltung angelegt werden?
if (isset($input['saveevent'])) {
	// Eine Veranstaltung wurde ver�ndert und muss in die DB gespeichert werden

	
	$errors = array();
	// Fehlerarray anlegen

	//Per Post übergebene Veranstaltung aus Datenbank abrufen
	$sql = "SELECT * FROM `veranstaltungen` WHERE `veranstaltungs_id` = " . intval($input['veranstaltungs_id']);

	$veranstaltung_result = mysql_query($sql);

	//echo mysql_error();
	//Nur fortfahren wenn es genau ein Ergebniss gibt
	if (mysql_num_rows($veranstaltung_result) == 1) {
		$veranstaltung = mysql_fetch_assoc($veranstaltung_result);
		
		//Dienstbeschreibung muss auf Form überprüft werden
		 $dienstbeschreibung = explode("\n",$input['dienstbeschreibung']);
		 //Jede zeile durchgehen
		 for($i = 0; $i < $input['mindienste']; $i++){
			if(isset($dienstbeschreibung[$i])){
				if(substr($dienstbeschreibung[$i],0,2) == ($i+1).":"){
					$dienstbeschreibung[$i] = substr($dienstbeschreibung[$i],0,20)."\n";
				}else{
					$dienstbeschreibung[$i] = ($i+1).":\n";
				}
			}else{
				$dienstbeschreibung[$i] = ($i+1).":\n";
		 	}
		 }
		 $dienstbeschreibung = array_slice($dienstbeschreibung,0,$input['mindienste']);
		 $dienstbeschreibung = implode($dienstbeschreibung);
		
		//Veranstaltungsname muss ausgefüllt sein
		if (!empty($input['veranstaltungsname'])) {
			$sql = "UPDATE `veranstaltungen` SET
        	 `veranstaltungsname` = '" . mysql_real_escape_string(filter_var($input['veranstaltungsname'], FILTER_SANITIZE_STRING)) . "',
        	 `beschreibung` = '" . mysql_real_escape_string(filter_var($input['beschreibung'], FILTER_SANITIZE_STRING)) . "',
        	 `mindienste` = '" . mysql_real_escape_string(filter_var($input['mindienste'], FILTER_SANITIZE_STRING)) . "',
        	 `dienstbeschreibung` = '" . mysql_real_escape_string(filter_var($dienstbeschreibung, FILTER_SANITIZE_STRING)) . "',";
		} else {
			//Ansonsten Error Array füllen
			$errors[] = "Veranstaltungsname muss vorhanden sein";
			$sql = "UPDATE `veranstaltungen` SET
        	 `beschreibung` = '" . mysql_real_escape_string(filter_var($input['beschreibung'], FILTER_SANITIZE_STRING)) . "',";
		}
	   if(is_numeric($input['minkuchen']) and $input['minkuchen'] >= 0){
		 	$sql .= "`minkuchen` = '" . mysql_real_escape_string(filter_var($input['minkuchen'], FILTER_SANITIZE_STRING)) . "',";
		 }else{
		   $errors[] = "Kuchenanzahl muss eine Zahl größer 0 sein";
		}
		//Falls im Formular ein neuer Ort angelegt werden soll
		if ($input['ort'] == "new") {
			//Ortsdaten überprüfen
			list($errors_ort, $input) = verify_ortsdaten($input);
			//Wenn keine Fehler festgestellt wurden, fortfahren
			if (empty($errors_ort)) {
				$sql_new_ort = "INSERT INTO `orte` SET
		       `name`        = '" . mysql_real_escape_string(filter_var($input['ort_name'], FILTER_SANITIZE_STRING)) . "',
		       `strasse`     = '" . mysql_real_escape_string(filter_var($input['ort_strasse'], FILTER_SANITIZE_STRING)) . "',
		       `hausnummer`  = '" . mysql_real_escape_string(filter_var($input['ort_hausnummer'], FILTER_SANITIZE_STRING)) . "',
		       `plz`         = '" . mysql_real_escape_string(filter_var($input['ort_plz'], FILTER_SANITIZE_STRING)) . "',
		       `ort`         = '" . mysql_real_escape_string(filter_var($input['ort_ort'], FILTER_SANITIZE_STRING)) . "',
			   `telefon`    = '" . mysql_real_escape_string(filter_var($input['ort_telvorwahl'], FILTER_SANITIZE_STRING)) . "/" . mysql_real_escape_string(filter_var($input['ort_telnr'], FILTER_SANITIZE_STRING)) . "',
			   `typ`        = '2',
               `createdby`  = '" . mysql_real_escape_string(intval($user['mitglieder_id'])) . "'";
				// MySQL Code ausf�hren (und somit �nderungen speichern)
				if (mysql_query($sql_new_ort)) {
					// Bei Erfolg entsprechendes als GET-Parameter �bergeben
					//header('Location: ort_show.php?id='.mysql_insert_id()."&created");
					//exit();

					//ID des neuen Ortes in die Veranstaltung schreiben
					$sql .= "`ort` = '" . mysql_insert_id() . "',";
				} else {
					// Bei einem Fehler die Mysql-Fehlernummer zur Fehleruntersuchung mitsenden
					// header('Location: ort_add.php?created=fail&why='.mysql_errno());
					// exit();
					$errors[] = "Datenbank-Fehler bei Erstellung des neuen Ortes";
				}
			} else {
				//Ortsfehler an allgemeines Fehlerarray anfügen
				$errors = array_merge($errors, $errors_ort);
			}

		} else {
			//Veranstaltungseintrag mit gegebeneer Orts ID füllen
			$sql .= "`ort` = '" . mysql_real_escape_string(intval($input['ort'])) . "',";
		}

		//Variable die angibt ob es mehrere Personen desselben Namens gibt
		$more_persons = FALSE;

		//Falls keine eindeutige Ansprechpartner ID übergeben wurde
		if (empty($input['ansprechpartner_id']) or $input['ansprechpartner_id'] == "other") {

			//Textfelder für Ansprechpartner sind ausgefüllt
			if (!empty($input['ansprechpartner_vorname']) and !empty($input['ansprechpartner_nachname'])) {
				//Test ob Ansprechpartner in der DB vorhanden

				$sql_test_person = "SELECT * FROM `mitglieder` WHERE `vorname` = '" . mysql_real_escape_string(filter_var($input['ansprechpartner_vorname'], FILTER_SANITIZE_STRING)) . "' AND `nachname` = '" . mysql_real_escape_string(filter_var($input['ansprechpartner_nachname'], FILTER_SANITIZE_STRING)) . "'";
				$test_person_result = mysql_query($sql_test_person);
				
				$person = mysql_fetch_assoc($test_person_result);

				//echo mysql_error();
				//Wenn es nur eine Person mit diesem Namen gibt oder diese Person bereits in der DB steht
				if (mysql_num_rows($test_person_result) == 1 or $person['mitglieder_id'] == $veranstaltung['ansprechpartner']) {
					$sql .= "`ansprechpartner` = '" . $person['mitglieder_id'] . "',";
				} elseif (mysql_num_rows($test_person_result) > 1) {
					//Wenn es mehrere Personen gibt muss dies im nächsten Schritt ausgewählt werden
					$more_persons = TRUE;
					$errors[] = "Es wurden mehrere Personen mit diesem Namen gefunden, bitte w&auml;hlen sie die richtige aus!";
				} else {
					$errors[] = "Keine Person mit diesem Namen in der Datenbank gefunden";
				}
			} else {
				$errors[] = "Ansprechpartner muss vorhanden sein";
			}

		} else {
			$sql .= "`ansprechpartner` = '" . $input['ansprechpartner_id'] . "',";
		}

		//Zeit Timestamps aus übergebenen Daten generieren
		$startzeit = mysql_real_escape_string(mktime(intval($input['startzeit_stunde']), intval($input['startzeit_minute']), 0, intval($input['startzeit_monat']), intval($input['startzeit_tag']), intval($input['startzeit_jahr'])));
		$endzeit = mysql_real_escape_string(mktime(intval($input['endzeit_stunde']), intval($input['endzeit_minute']), 0, intval($input['endzeit_monat']), intval($input['endzeit_tag']), intval($input['endzeit_jahr'])));

		//Startzeit muss vor Endzeit sein
		if ($endzeit > $startzeit) {
			$sql .= "`startzeit` = '" . $startzeit . "',";
			$sql .= "`endzeit` = '" . $endzeit . "'";
		} else {
			$errors[] = "Startzeitpunkt muss vor Endzeitpunkt sein";
		}

		//Überprüfen ob SQL anfrage noch ein überschüssiges Komma aufweißt
		if (substr($sql, -1, 1) == ",") {
			$sql = substr($sql, 0, strlen($sql) - 1);
		}

		//Veranstaltungseintrag bearbeiten
		$sql .= "   WHERE `veranstaltungs_id` = '" . intval($input['veranstaltungs_id']) . "'";
		if (mysql_query($sql)) {
				
				//Bei Veränderungen an der Veranstaltung müssen evtl vorhandene Dienste geändert oder gelöscht werden
				if($startzeit != $veranstaltung['startzeit'] or $endzeit != $veranstaltung['endzeit']){	
					$sql_dienste = "DELETE FROM `dienste`
		                WHERE
		               `dienste`.`event` = '".intval($input['veranstaltungs_id'])."'";
					mysql_query($sql_dienste);
				}elseif($input['mindienste'] != $veranstaltung['mindienste']){
					$sql_dienste = "DELETE FROM `dienste`
		                WHERE
		               `dienste`.`stand` > '".intval($input['mindienste'])."'";
					mysql_query($sql_dienste);
				}
			//Wenn es nicht mehrere Personen gab, keinen Namen übergeben
			if ($more_persons == FALSE) {
				// Bei Erfolg entsprechendes als GET-Parameter �bergeben
				header('Location: veranstaltung_edit.php?id=' . intval($input['veranstaltungs_id']) . "&save=success&errors=" . base64_encode(json_encode($errors)));
				exit();

			} else {

				header('Location: veranstaltung_edit.php?id=' . intval($input['veranstaltungs_id']) . "&save=success&vorname=" . $input['ansprechpartner_vorname'] . "&nachname=" . $input['ansprechpartner_nachname'] . "&errors=" . base64_encode(json_encode($errors)));
				exit();

			}

		} else {
			// Bei einem Fehler die Mysql-Fehlernummer zur Fehleruntersuchung mitsenden
			header('Location: veranstaltung_edit.php?id=' . intval($input['veranstaltungs_id']) . "&save=fail&why=" . mysql_errno() . "&errors=" . base64_encode(json_encode($errors)));
			exit();
		}

	} else {
		//SQL Fehler
		$errors[] = "Veranstaltung mit gegebener ID nicht gefunden";
	}

	//Veranstaltung wird neu angelegt
} elseif (isset($input['addevent'])) {
	// Eine Veranstaltung wurde ver�ndert und muss in die DB gespeichert werden

	$errors = array();
	// Fehlerarray anlegen

	//Veranstaltungsname muss vorhanden sein
	if (!empty($input['veranstaltungsname'])) {
		$sql = "INSERT INTO `veranstaltungen` SET
        	 `veranstaltungsname` = '" . mysql_real_escape_string(filter_var($input['veranstaltungsname'], FILTER_SANITIZE_STRING)) . "',
        	 `beschreibung` = '" . stripslashes(mysql_real_escape_string(filter_var($input['beschreibung'], FILTER_SANITIZE_STRING))) . "',
        	 `mindienste` = '" . mysql_real_escape_string(filter_var($input['mindienste'], FILTER_SANITIZE_STRING)) . "',
        	 `dienstbeschreibung` = '" . stripslashes(mysql_real_escape_string(filter_var($input['dienstbeschreibung'], FILTER_SANITIZE_STRING))) . "',";
	} else {
		$sql = "";
		$errors[] = "Veranstaltungsname muss vorhanden sein";
	}

	//Soll auch ein neuer Ort angelegt werden?
	if ($input['ort'] == "new") {
		
		list($errors_ort, $input) = verify_ortsdaten($input);
		
		if (empty($errors_ort)) {
			$sql_new_ort = "INSERT INTO `orte` SET
		       `name`        = '" . mysql_real_escape_string(filter_var($input['ort_name'], FILTER_SANITIZE_STRING)) . "',
		       `strasse`     = '" . mysql_real_escape_string(filter_var($input['ort_strasse'], FILTER_SANITIZE_STRING)) . "',
		       `hausnummer`  = '" . mysql_real_escape_string(filter_var($input['ort_hausnummer'], FILTER_SANITIZE_STRING)) . "',
		       `plz`         = '" . mysql_real_escape_string(filter_var($input['ort_plz'], FILTER_SANITIZE_STRING)) . "',
		       `ort`         = '" . mysql_real_escape_string(filter_var($input['ort_ort'], FILTER_SANITIZE_STRING)) . "',
			   `telefon`     = '" . mysql_real_escape_string(filter_var($input['ort_telvorwahl'], FILTER_SANITIZE_STRING)) . "/" . mysql_real_escape_string(filter_var($input['ort_telnr'], FILTER_SANITIZE_STRING)) . "',
			   `typ`         = '2',
               `createdby`   = '" . mysql_real_escape_string(intval($user['mitglieder_id'])) . "'";
			// MySQL Code ausf�hren (und somit �nderungen speichern)
			if (mysql_query($sql_new_ort)) {
				// Bei Erfolg entsprechendes als GET-Parameter �bergeben
				//header('Location: ort_show.php?id='.mysql_insert_id()."&created");
				//exit();

				//ID des neuen Ortes in die Veranstaltung schreiben
				$sql .= "`ort` = '" . mysql_insert_id() . "',";
			} else {
				// Bei einem Fehler die Mysql-Fehlernummer zur Fehleruntersuchung mitsenden
				// header('Location: ort_add.php?created=fail&why='.mysql_errno());
				// exit();
				$errors[] = "Datenbank-Fehler bei Erstellung des neuen Ortes";
			}
		} else {
			//Orsfehler an allgemeine Fehler anfügen
			$errors = array_merge($errors, $errors_ort);
		}

	} else {
		//Ausgewählten Ort einfügen
		$sql .= "`ort` = '" . mysql_real_escape_string(intval($input['ort'])) . "',";
	}
	
	$more_persons = FALSE;
	
	//Wenn kein eindeutiger Ansprechpartner übergeben wurde
	if (empty($input['ansprechpartner_id'])) {

		//Wenn kein Name in der Textbox stand
		if (!empty($input['ansprechpartner_vorname']) and !empty($input['ansprechpartner_nachname'])) {
			//Test ob Ansprechpartner in der DB vorhanden

			$sql_test_person = "SELECT * FROM `mitglieder` WHERE `vorname` = '" . mysql_real_escape_string(filter_var($input['ansprechpartner_vorname'], FILTER_SANITIZE_STRING)) . "' AND `nachname` = '" . mysql_real_escape_string(filter_var($input['ansprechpartner_nachname'], FILTER_SANITIZE_STRING)) . "'";
			$test_person_result = mysql_query($sql_test_person);

			$more_persons = FALSE;
			$person = mysql_fetch_assoc($test_person_result);

			//echo mysql_error();
			//Gibt es mehrere Personen?
			if (mysql_num_rows($test_person_result) == 1) {
				$sql .= "`ansprechpartner` = '" . $person['mitglieder_id'] . "',";
			} elseif (mysql_num_rows($test_person_result) > 1) {
				$more_persons = TRUE;
				$errors[] = "Es gibt mehrere Personen mit diesem Namen";
			} else {
				$errors[] = "Keine Person mit diesem Namen in der Datenbank gefunden";
			}
		} else {
			$errors[] = "Ansprechpartner muss vorhanden sein";
		}

	} else {
		$sql .= "`ansprechpartner` = '" . $input['ansprechpartner_id'] . "',";
	}

	//Zeiten als Timestamp generieren
	$startzeit = mysql_real_escape_string(mktime(intval($input['startzeit_stunde']), intval($input['startzeit_minute']), 0, intval($input['startzeit_monat']), intval($input['startzeit_tag']), intval($input['startzeit_jahr'])));
	$endzeit = mysql_real_escape_string(mktime(intval($input['endzeit_stunde']), intval($input['endzeit_minute']), 0, intval($input['endzeit_monat']), intval($input['endzeit_tag']), intval($input['endzeit_jahr'])));

	//Startzeit muss vor Endzeit sein
	if ($endzeit > $startzeit) {
		$sql .= "`startzeit` = '" . $startzeit . "',";
		$sql .= "`endzeit` = '" . $endzeit . "'";
	} else {
		$errors[] = "Startzeitpunkt muss vor Endzeitpunkt sein";
	}

	//Komma am Ende der Anfrage ggf. entfernen
	if (substr($sql, -1, 1) == ",") {
		$sql = substr($sql, 0, strlen($sql) - 1);
	}

	$sql .= ";";

	if (empty($errors)) {
		if (mysql_query($sql)) {
			// Bei Erfolg entsprechendes als GET-Parameter �bergeben
			header('Location: veranstaltung_show.php?id=' . mysql_insert_id() . "&save=success");
		} else {
			// Bei einem Fehler die Mysql-Fehlernummer zur Fehleruntersuchung mitsenden
			header('Location: veranstaltung_add.php?save=fail&why=' . mysql_errno());
		}
		exit();

	} else {
		if ($more_persons == TRUE) {
			$data = array("minkuchen" => $input['minkuchen'],"mindienste" => $input['mindienste'], "ort" => $input['ort'], "ansprechpartner_vorname" => $input['ansprechpartner_vorname'], "ansprechpartner_nachname" => $input['ansprechpartner_nachname'], "more_persons" => "TRUE", $input['ansprechpartner_vorname'], "veranstaltungsname" => $input['veranstaltungsname'], "beschreibung" => $input['beschreibung'], "ort_name" => $input['ort_name'], "ort_strasse" => $input['ort_strasse'], "ort_hausnummer" => $input['ort_hausnummer'], "ort_plz" => $input['ort_plz'], "ort_ort" => $input['ort_ort'], "ort_telvorwahl" => $input['ort_telvorwahl'], "ort_telnr" => $input['ort_telnr'], "startzeit" => $startzeit, "endzeit" => $endzeit);
		} else {
			$data = array("minkuchen" => $input['minkuchen'],"mindienste" => $input['mindienste'], "ort" => $input['ort'], "ansprechpartner_vorname" => $input['ansprechpartner_vorname'], "ansprechpartner_nachname" => $input['ansprechpartner_nachname'], "veranstaltungsname" => $input['veranstaltungsname'], "beschreibung" => $input['beschreibung'], "ort_name" => $input['ort_name'], "ort_strasse" => $input['ort_strasse'], "ort_hausnummer" => $input['ort_hausnummer'], "ort_plz" => $input['ort_plz'], "ort_ort" => $input['ort_ort'], "ort_telvorwahl" => $input['ort_telvorwahl'], "ort_telnr" => $input['ort_telnr'], "startzeit" => $startzeit, "endzeit" => $endzeit);
		}
		header('Location: veranstaltung_add.php?save=fail&data=' . base64_encode(json_encode($data)) . '&errors=' . base64_encode(json_encode($errors)));
		exit();
	}

	
}elseif (isset($input['deleteevent'])){
  // Vorhandene Veranstaltung Löschen bzw. als im Papierkorb markieren
  if ($input['sure'] == 2){
        if ($input['dbdelete'] == 2 && $user['rights'] >= 5){
          // Ort soll auch aus der DB gelöscht werden und der Nutzer hat die Berechtigung dazu
          $sql = "DELETE FROM `veranstaltungen`
                WHERE
               `veranstaltungen`.`veranstaltungs_id` = '".intval($input['veranstaltungs_id'])."'";
		 $sql_dienste = "DELETE FROM `dienste`
                WHERE
               `dienste`.`event` = '".intval($input['veranstaltungs_id'])."'";
          if (mysql_query($sql) and mysql_query($sql_dienste)){
             // Bei Erfolg entsprechendes als GET-Parameter übergeben
           header('Location: veranstaltungen.php?deletion=success&who='.intval($input['veranstaltungs_id']));
             exit();
          }else{
             // Bei einem Fehler die Mysql-Fehlernummer zur Fehleruntersuchung mitsenden
           header('Location: veranstaltungen.php?deletion=fail&who='.intval($input['veranstaltungs_id'])."&why=".mysql_errno());
             exit();
          }
        }else{
          // Ort normal als "im Papierkorb" markieren
          $sql = "UPDATE `veranstaltungen` SET
               `status`        = '2'
             WHERE
               `veranstaltungen`.`veranstaltungs_id` = '".intval($input['veranstaltungs_id'])."'";
           $sql_dienste = "DELETE FROM `dienste`
            WHERE
           `dienste`.`event` = '".intval($input['veranstaltungs_id'])."'";
          if (mysql_query($sql) and mysql_query($sql_dienste)){
             // Bei Erfolg entsprechendes als GET-Parameter übergeben
             header('Location: veranstaltungen.php?removal=success&who='.intval($input['veranstaltungs_id']));
             exit();
          }else{
             // Bei einem Fehler die Mysql-Fehlernummer zur Fehleruntersuchung mitsenden
            header('Location: veranstaltungen.php?removal=fail&who='.intval($input['veranstaltungs_id'])."&why=".mysql_errno());
             exit();
          }
        }

  }else{
    // Wirklich löschen wurde mit Nein beantwortet
    header('Location: veranstaltung_show.php?id='.intval($input['veranstaltungs_id']));
    exit();
  }
} else {
	// Weiterleitung, falls aus irgendwelchen Gr�nden diese Datei aufgerufen wird, ohne, dass etwas �bergeben wird
	header('Location: veranstaltungen.php');
}
?>