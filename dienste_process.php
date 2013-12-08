<?php
// Verarbeitet Speicher- und Erstellungsprozesse von Mitgliedern

// Datenbankverbindung & Kodierung einbinden
require_once ("inc/connect.inc.php");
//echo "stop";
// Login-Status-Pr�fung einbinden
require_once ("inc/checkuser.inc.php");
if (isset($_GET['action']) and isset($_GET['event'])) {
	//Error Counter auf False setzen
	$errors = FALSE;
	//Betroffene Veranstaltung aus der DB abrufen
	$sql_veranstaltung = "SELECT * FROM `veranstaltungen` WHERE `veranstaltungs_id` = " . intval($_GET['event']);
	$veranstaltung_result = mysql_query($sql_veranstaltung);
	//Nur fortfahren wenn es genau ein Ergebniss gibt
	if (mysql_num_rows($veranstaltung_result) == 1) {

		$veranstaltung = mysql_fetch_assoc($veranstaltung_result);
		//Soll eine Veranstaltung erstellt oder gelöscht werden?
		if ($_GET['action'] == "add") {
			
											if(isset($_GET['person']) and $_GET['person'] == "other"){
												if(empty($_GET['ansprechpartner_id'])){
												//Variable die angibt ob es mehrere Personen desselben Namens gibt
												$more_persons = FALSE;

										
													//Textfelder für Ansprechpartner sind ausgefüllt
													if (!empty($_GET['vorname']) and !empty($_GET['nachname'])) {
														//Test ob Ansprechpartner in der DB vorhanden
										
														$sql_test_person = "SELECT * FROM `mitglieder` WHERE `vorname` = '" . mysql_real_escape_string(filter_var($_GET['vorname'], FILTER_SANITIZE_STRING)) . "' AND `nachname` = '" . mysql_real_escape_string(filter_var($_GET['nachname'], FILTER_SANITIZE_STRING)) . "'";
															$test_person_result = mysql_query($sql_test_person);
															
															$personen = mysql_fetch_assoc($test_person_result);
											
															//echo mysql_error();
															//Wenn es nur eine Person mit diesem Namen gibt oder diese Person bereits in der DB steht
															if (mysql_num_rows($test_person_result) == 1) {
																$person = $personen['mitglieder_id'];
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
											
													if ($more_persons == TRUE) {
														$data = $_GET;
														$data = array_merge($data, array("more_persons" => "true"));
													}
													if(isset($_GET['kuchen'])){
														$get = "";
													}else{
														
													}
													if($errors != FALSE){
														header('Location: dienste_add.php?action=fail&event=' . intval($_GET['event']) . '&data=' . base64_encode(json_encode($data)) . '&errors=' . base64_encode(json_encode($errors)));
														exit();		
													}
												  }else{
												  	$person = $_GET['ansprechpartner_id'];
												  }
												}elseif(isset($_GET['person']) and $_GET['person'] == "self"){
													$person = $user['mitglieder_id'];
												}

			


			
			//Personen die entweder die berechtigung besitzen oder ansprechpartner sind und auch eine entsprechende Person eingeben dürfen die Veranstaltung bearbeiten
			if ($user['rights'] > 3 or ($user['rights'] == 3 and ($veranstaltung['startzeit'] - time()) > 259200) or $user['mitglieder_id'] == $veranstaltung['ansprechpartner']) {
				$admin = TRUE;
			} else {
				$person = $user['mitglieder_id'];
				$admin = FALSE;
			}
			//Geht es um Kuchen
			if (isset($_GET['kuchen'])) {
				$sql_dienste = "SELECT * FROM `dienste` WHERE `dienstart` > 0 AND `event` = " . intval($_GET['event']);
				$dienste_result = mysql_query($sql_dienste);

				$belegt = FALSE;
				//Überprüfen ob es bereits einen Kuchendienst für diese Person gibt
				while ($dienst = mysql_fetch_assoc($dienste_result)) {
					if ($dienst['person'] == $person) {
						$belegt = $dienst['dienst_id'];
						break;
					}
				}
				if (($veranstaltung['startzeit'] - time()) > 259200 or $admin == TRUE) {
					//Wurde die Kuchenanzahl auf 0 gesetzt?
					if ($_GET['kuchen'] != 0) {
						if (!$belegt) {
							//neuen Dienst erzeugen
							$sql = "INSERT INTO `dienste` SET
		        	 				`event` = '" . mysql_real_escape_string(filter_var($_GET['event'], FILTER_SANITIZE_STRING)) . "',
		        	 				`dienstart` = '" . mysql_real_escape_string(filter_var($_GET['kuchen'], FILTER_SANITIZE_STRING)) . "',
		        	 				`startzeit` = '" . mysql_real_escape_string(filter_var($veranstaltung['startzeit'], FILTER_SANITIZE_STRING)) . "',
		        	 				`person` = '" . $person . "'";
						} elseif ($belegt != $_GET['kuchen']) {
							//Bereits bestehenden Dienst updaten
							$sql = "UPDATE `dienste` SET
		        	 				`dienstart` = '" . mysql_real_escape_string(filter_var($_GET['kuchen'], FILTER_SANITIZE_STRING)) . "'
		        	 			     WHERE `dienst_id` = '" . intval($belegt) . "'";
						}
						if (isset($sql) and mysql_query($sql)) {
							// Bei Erfolg entsprechendes als GET-Parameter übergeben
							header('Location: dienste_show.php?action=success&id=' . intval($_GET['event']));
							exit();
						} else {
							// Bei einem Fehler die Mysql-Fehlernummer zur Fehleruntersuchung mitsenden
							header('Location: dienste_show.php?action=fail&id=' . intval($_GET['event']));
							exit();
						}
					} else {
						//Wenn es 0 Kuchen geben soll, wird der entweder der vorhandene Dienst gelöscht oder gar keiner angelegt
						if (!$belegt) {
							//An Dienstseite ohne Aktion leiten
							header('Location: dienste_show.php?id=' . intval($_GET['event']));
							exit();
						} else {
							//An diese Seite leiten mit Löschparameter
							header('Location: dienste_process.php?action=remove&dienst_id=' . intval($belegt) . '&event=' . intval($_GET['event']));
							exit();
						}
					}
				} else {
					header('Location: dienste_show.php?action=fail&id=' . intval($_GET['event']));
					exit();
				}
			} else {
				$sql_dienste = "SELECT * FROM `dienste` WHERE `event` = " . intval($_GET['event']);
				$dienste_result = mysql_query($sql_dienste);

				$belegt = FALSE;
				while ($dienst = mysql_fetch_assoc($dienste_result)) {
					if ($dienst['startzeit'] <= $_GET['anfang'] and $dienst['endzeit'] >= $_GET['ende'] and $dienst['person'] == $person and $dienst['dienstart'] == 0) {
						$belegt = TRUE;
						break;
					}
				}
				//Dienstzeit muss innerhalb der Veranstaltung sein und nicht länger als 45 Minuten
				if ((!$belegt and isset($_GET['stand']) and isset($_GET['anfang']) and isset($_GET['ende']) and $_GET['anfang'] < $_GET['ende'] and $_GET['anfang'] >= ($veranstaltung['startzeit'] - 30 * 60) and $_GET['ende'] <= ($veranstaltung['endzeit'] + 30 * 60) and ($_GET['ende'] - $_GET['anfang']) <= (45 * 60)) and (($veranstaltung['startzeit'] - time()) > 259200 or $admin == TRUE)) {

					$sql = "INSERT INTO `dienste` SET
	        	 				`event` = '" . mysql_real_escape_string(filter_var($_GET['event'], FILTER_SANITIZE_STRING)) . "',
	        	 				`startzeit` = '" . mysql_real_escape_string(filter_var($_GET['anfang'], FILTER_SANITIZE_STRING)) . "',
	        	 				`endzeit` = '" . mysql_real_escape_string(filter_var($_GET['ende'], FILTER_SANITIZE_STRING)) . "',
	        	 				`stand` = '" . mysql_real_escape_string(filter_var($_GET['stand'], FILTER_SANITIZE_STRING)) . "',
	        	 				`dienstart` = '0',
	        	 				`person` = '" . $person . "'";
					if (mysql_query($sql)) {
						// Bei Erfolg entsprechendes als GET-Parameter übergeben
						header('Location: dienste_show.php?action=success&id=' . intval($_GET['event']));
						exit();
					} else {
						// Bei einem Fehler die Mysql-Fehlernummer zur Fehleruntersuchung mitsenden
						header('Location: dienste_show.php?action=fail&id=' . intval($_GET['event']) . '&why=' . mysql_errno());
						exit();
					}

				} else {
					header('Location: dienste_show.php?action=fail&id=' . intval($_GET['event']));
					exit();
				}
			}

		} elseif ($_GET['action'] == "remove" and ($user['rights'] > 3 or ($user['rights'] <= 3 and ($veranstaltung['startzeit'] - time()) > 259200) or $user['mitglieder_id'] == $veranstaltung['ansprechpartner'])) {
			if (isset($_GET['dienst_id'])) {
				$sql_dienste = "SELECT * FROM `dienste` WHERE `dienst_id` = " . intval($_GET['dienst_id']);
				$dienste_result = mysql_query($sql_dienste);
				$dienst = mysql_fetch_assoc($dienste_result);
				
					$sql = "DELETE FROM `dienste`
					    WHERE
					   `dienste`.`dienst_id` = '" . intval($_GET['dienst_id']) . "'";
					if (mysql_query($sql)) {
						// Bei Erfolg entsprechendes als GET-Parameter übergeben
						header('Location: dienste_show.php?action=success&id=' . intval($_GET['event']));
						exit();
					} else {
						// Bei einem Fehler die Mysql-Fehlernummer zur Fehleruntersuchung mitsenden
						header('Location: dienste_show.php?action=fail&id=' . intval($_GET['event']) . '&why=' . mysql_errno());
						exit();
					}

			}
		} else {
			header('Location: dienste_show.php?action=fail&id=' . intval($_GET['event']));
			exit();
		}
	} else {
		// Weiterleitung, falls aus irgendwelchen Gr�nden diese Datei aufgerufen wird, ohne, dass etwas �bergeben wird
		header('Location: veranstaltungen.php');
	}
} else {
	// Weiterleitung, falls aus irgendwelchen Gr�nden diese Datei aufgerufen wird, ohne, dass etwas �bergeben wird
	header('Location: veranstaltungen.php');
}
?>
