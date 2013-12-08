<?php
// Cronjob-Datei; Wird regelmäßig mittels Cron ausgeführt
// Versendet personalisierte Geburtstags- und Jubiläumsgrüße für die Mitglieder

// Datenbankverbindung & Kodierung einbinden
require_once ("inc/connect.inc.php");

// Output-Buffer starten
ob_start();
echo "Ausführung startet ".date("r")."<br />\n";
// Soll der Cronjob zur Durchführung gezwungen werden oder eine Ausgabe getätigt werden
if (isset($_GET['force']) || isset($_GET['output'])){
  // Dann prüfen ob jemand eingeloggt ist und ob dieser Admin ist
  // Login-Status-Prüfung einbinden
  require_once("inc/checkuser.inc.php");
}
// Prüfen, ob die letzte Ausführung knapp 24h her ist
// Nicht Sekundengenau rechnen, da aufgrund von Serverlast der Cronjob nicht immer Sekundengenau gestartet und ausgeführt wird
if (time() - 86300 >= $settings['lastcronrun'] || (isset($_GET['force']) && $user['rights'] >= 5)){
    // Letzte ausführung knapp 24h zurück, also jetzt erneut ausführen ODER es soll gezwungen werden und ein Admin ist eingeloggt

    // Brief-Array erstellen. In ihm werden alle Mitglieder gespeichert, die von der Vereinsleitung einen Brief per Post zugestellt bekommen müssen,
    // da sie keine E-Mail-Adresse haben
    $brief = array();

    // SQL-Anfrage für Geburtstage
 	$sql = "SELECT  `mitglieder`.`vorname`, `mitglieder`.`nachname`, `mitglieder`.`geschlecht`, `mitglieder`.`email`, `mitglieder`.`mitglieder_id`,
                    `orte`.* , DATE_FORMAT(NOW(),'%Y' ) - DATE_FORMAT( geburtstag,  '%Y' ) + IF( DATE_FORMAT( geburtstag,  '%m%d' ) < DATE_FORMAT( NOW( ) ,  '%m%d' ) , 1, 0 ) AS new_age,
                    DATEDIFF( geburtstag + INTERVAL YEAR( NOW( ) ) - YEAR( geburtstag ) + IF( DATE_FORMAT( NOW( ) ,  '%m%d' ) > DATE_FORMAT( geburtstag,  '%m%d' ) , 1, 0 ) YEAR, NOW( ) ) AS days_to_birthday
            FROM `mitglieder`
            LEFT JOIN `orte` ON `mitglieder`.`anschrift` = `orte`.`orts_id`
            WHERE `lastbirthdaymail` < UNIX_TIMESTAMP()-28927182
            HAVING (days_to_birthday = 0 AND `email` != '') OR (days_to_birthday <= 3 AND `email` = '')
            ORDER BY days_to_birthday ASC";
    // Es werden alle Mitglieder zurückgegeben, die
    //                      - innerhalb der letzten 11 Monate keine birthday-mail erhalten haben
    //                      - keine E-Mail-Adresse haben und in 3 Tagen oder weniger Geburtstag haben
    //                      - eine E-Mail-Adresse haben und am heutigen Tag Geburtstag haben
    $members = mysql_query($sql);
    echo mysql_error();
    if (mysql_num_rows($members) == 0){
      echo "Keine Geburtstage anstehend";
    }else{
      // Ergebnisse der DB Abfrage durchlaufen
      while ($member = mysql_fetch_assoc($members)){
        if (empty($member['email']) || filter_var($member['email'], FILTER_VALIDATE_EMAIL) === false) {
          // Aktuelles Mitglied hat keine E-Mail oder die E-Mail ist ungültig = Meldung an Vereinsleitung, einen Brief zu senden
          // Array mit dem Mitgliedsdaten der Datenbankanfrage und dem zusatz des Grundes in das Brief-Array schreiben
          $member["why"] = "geburtstag";
          $brief[] = $member;
          // In die DB schreiben, dass dieses Mitglied eine E-Mail bekommen hat
          mysql_query("UPDATE `mitglieder` SET `lastbirthdaymail`  = '".time()."' WHERE `mitglieder`.`mitglieder_id` = '".$member['mitglieder_id']."' ");
        }else{
            // Das Mitglied hat eine eigene, gültige E-Mail
    		$betreff = "Alles Gute zum Geburtstag";
            $mailtext = "<html>
            <head>
            <title>Alles Gute zum Geburtstag</title>
            </head>
            <p>";
            if ($member['new_age'] >= 18){
                if ($member['geschlecht'] == 1){
                  $mailtext .= "Sehr geehrter Herr ".htmlspecialchars($member['nachname']);
                }elseif($member['geschlecht'] == 2){
                  $mailtext .= "Sehr geehrte Frau ".htmlspecialchars($member['nachname']);
                }else{
                  $mailtext .= "Hallo ".htmlspecialchars($member['vorname']." ".$member['nachname']);
                }
                $mailtext .= ",<br />\n<br />\nzu Ihrem heutigen Geburtstag gratulieren wir Ihnen recht herzlich und wünschen alles Gute im nächsten Lebensjahr.</p>";
            }else{
                if ($member['geschlecht'] == 1){
                  $mailtext .= "Lieber ".htmlspecialchars($member['vorname']);
                }elseif($member['geschlecht'] == 2){
                  $mailtext .= "Liebe ".htmlspecialchars($member['vorname']);
                }else{
                  $mailtext .= "Hallo ".htmlspecialchars($member['vorname']);
                }
                $mailtext .= ",<br />\n<br />\nzu deinem ".$member['new_age'].". Geburtstag gratulieren wir dir recht herzlich und wünschen alles Gute.</p>";
            }
            $mailtext .= "<p>Mit besten Grüßen<br />Die Vereinsleitung ".$settings['vereinsname']."</p>
            </html>";
            $header = "MIME-Version: 1.0\n";
            $header .= "Content-type: text/html; charset=utf-8\n";
            $header .= "From: '".$settings['vereinsname']."' <".$settings['leitermail'].">\n";
            if (mail($member['email'], $betreff, $mailtext, $header)){
              echo "Erfolgreich Geburtstagsgrüße versendet an ".$member['vorname']." ".$member['nachname']."<br />\n";
              // In die DB schreiben, dass dieses Mitglied eine E-Mail bekommen hat
              mysql_query("UPDATE `mitglieder` SET `lastbirthdaymail`  = '".time()."' WHERE `mitglieder`.`mitglieder_id` = '".$member['mitglieder_id']."' ");
            }else{
              echo "Fehler beim Senden der Geburtstagsgrüße an ".$member['vorname']." ".$member['nachname']."<br />\n";
            }
       }
      }
    }

    // SQL-Anfrage für Jubiläen
 	$sql = "SELECT  `mitglieder`.`vorname`, `mitglieder`.`nachname`, `mitglieder`.`geschlecht`, `mitglieder`.`email`, `mitglieder`.`mitglieder_id`, `mitglieder`.`geburtstag`,
                    `orte`.* , DATE_FORMAT(NOW(),'%Y' ) - DATE_FORMAT( `beitritt`,  '%Y' ) + IF( DATE_FORMAT( `beitritt`,  '%m%d' ) < DATE_FORMAT( NOW( ) ,  '%m%d' ) , 1, 0 ) AS jahre,
                    DATEDIFF( `beitritt`+ INTERVAL YEAR( NOW( ) ) - YEAR( `beitritt`) + IF( DATE_FORMAT( NOW( ) ,  '%m%d' ) > DATE_FORMAT( `beitritt`,  '%m%d' ) , 1, 0 ) YEAR, NOW( ) ) AS days_to_jubilee
            FROM `mitglieder`
            LEFT JOIN `orte` ON `mitglieder`.`anschrift` = `orte`.`orts_id`
            WHERE `lastjubileemail` < UNIX_TIMESTAMP()-28927182
            HAVING (days_to_jubilee= 0 AND `email` != '') OR (days_to_jubilee<= 3 AND `email` = '') AND MOD(jahre,5) = 0
            ORDER BY days_to_jubilee ASC";
    // Es werden alle Mitglieder zurückgegeben, die
    //                      - innerhalb der letzten 11 Monate keine Jubiläums-Mail erhalten haben
    //                      - keine E-Mail-Adresse haben und in 3 Tagen oder weniger Jubiläum haben
    //                      - eine E-Mail-Adresse haben und am heutigen Tag Jubiläum haben
    //                      - ein "rundes" Jubiläum haben, also 5 Jahre, 10 Jahre, 15 Jahre, ...
    $members = mysql_query($sql);
    echo mysql_error();
    if (mysql_num_rows($members) == 0){
      echo "Keine Jubiläen anstehend<br />\n";
    }else{
      // Ergebnisse der DB Abfrage durchlaufen
      while ($member = mysql_fetch_assoc($members)){
        if (empty($member['email']) || filter_var($member['email'], FILTER_VALIDATE_EMAIL) === false) {
          // Aktuelles Mitglied hat keine E-Mail oder die E-Mail ist ungültig = Meldung an Vereinsleitung, einen Brief zu senden
          // Array mit dem Mitgliedsdaten der Datenbankanfrage und dem zusatz des Grundes in das Brief-Array schreiben
          $member["why"] = "jubilaeum";
          $brief[] = $member;
          // In die DB schreiben, dass dieses Mitglied eine E-Mail bekommen hat
          mysql_query("UPDATE `mitglieder` SET `lastjubileemail`  = '".time()."' WHERE `mitglieder`.`mitglieder_id` = '".$member['mitglieder_id']."' ");
        }else{
            // Das Mitglied hat eine eigene, gültige E-Mail
    		$betreff = $member['jahre']." Jahre Vereinsmitgliedschaft";
            $mailtext = "<html>
            <head>
            <title>".$member['jahre']." Jahre Vereinsmitgliedschaft</title>
            </head>
            <p>";
            if ($member['geburtstag'] <= time()- 568024668){
                if ($member['geschlecht'] == 1){
                  $mailtext .= "Sehr geehrter Herr ".htmlspecialchars($member['nachname']);
                }elseif($member['geschlecht'] == 2){
                  $mailtext .= "Sehr geehrte Frau ".htmlspecialchars($member['nachname']);
                }else{
                  $mailtext .= "Hallo ".htmlspecialchars($member['vorname']." ".$member['nachname']);
                }
                $mailtext .= ",<br />\n<br />\nzu Ihrem ".$member['jahre']."jährigen Vereinsjubiläum gratulieren wir Ihnen recht herzlich.</p>";
            }else{
                if ($member['geschlecht'] == 1){
                  $mailtext .= "Lieber ".htmlspecialchars($member['vorname']);
                }elseif($member['geschlecht'] == 2){
                  $mailtext .= "Liebe ".htmlspecialchars($member['vorname']);
                }else{
                  $mailtext .= "Hallo ".htmlspecialchars($member['vorname']);
                }
                $mailtext .= ",<br />\n<br />\nzu deinem ".$member['jahre']."jährigen Vereinsjubiläum gratulieren wir dir recht herzlich.</p>";
            }
            $mailtext .= "<p>Mit besten Grüßen<br />Die Vereinsleitung ".$settings['vereinsname']."</p>
            </html>";
            $header = "MIME-Version: 1.0\n";
            $header .= "Content-type: text/html; charset=utf-8\n";
            $header .= "From: '".$settings['vereinsname']."' <".$settings['leitermail'].">\n";
            if (mail($member['email'], $betreff, $mailtext, $header)){
              echo "Jubiläumgsrüße Erfolgreich versendet an ".$member['vorname']." ".$member['nachname']."<br />\n";
              // In die DB schreiben, dass dieses Mitglied eine E-Mail bekommen hat
              mysql_query("UPDATE `mitglieder` SET `lastjubileemail`  = '".time()."' WHERE `mitglieder`.`mitglieder_id` = '".$member['mitglieder_id']."' ");
            }else{
              echo "Fehler beim Senden der Jubiläumgsrüße an ".$member['vorname']." ".$member['nachname']."<br />\n";
            }
       }
      }
    }

    // Muss die Vereinsleitung informiert werden?
    if (count($brief) > 0){
      // Es müssen postalische Briefe versendet werden
      $betreff = "Gruesse per Post";
      $mailtext = "<html>
      <head>
      <title>Gruesse per Post</title>
      </head>
      <p>Guten Tag,<br />\n
      folgende Mitglieder haben innerhalb der nächsten 3 Tage Geburtstag oder Mitgliedschaftsjubiläum und keine E-Mail Adresse im Vereinssystem hinterlegt.</p>";
      foreach ($brief as &$eintrag) {
        if ($eintrag['why'] == "geburtstag"){
          $mailtext .= "<p>".htmlspecialchars($eintrag['vorname']." ".$eintrag['nachname'])." wird in ".$eintrag['days_to_birthday']." Tagen ".$eintrag['new_age']." Jahre alt.<br />Adresse:<br />\n".$eintrag['strasse']." ".$eintrag['hausnummer']."<br />\n".$eintrag['plz']." ".$eintrag['ort']."</p>";
        }elseif($eintrag['why'] == "jubilaeum"){
          $mailtext .= "<p>".htmlspecialchars($eintrag['vorname']." ".$eintrag['nachname'])." hat in ".$eintrag['days_to_jubilee']." Tagen ".$eintrag['jahre']."jähriges Vereinsmitgliedschaftsjubiläum.<br />Adresse:<br />\n".$eintrag['strasse']." ".$eintrag['hausnummer']."<br />\n".$eintrag['plz']." ".$eintrag['ort']."</p>";
        }
      }
      $mailtext .= "<small>Diese E-Mail wurde automatisch gesendet.</small>
      </html>";
      $header = "MIME-Version: 1.0\n";
      $header .= "Content-type: text/html; charset=utf-8\n";
      $header .= "From: '".$settings['vereinsname']."' <".$settings['leitermail'].">\n";
      if (mail($settings['leitermail'], $betreff, $mailtext, $header)){
        echo "Erfolgreich versendet an Vereinsleitung mit ".count($brief)." Hinweisen<br />\n";
      }else{
        echo "Fehler beim Senden an Vereinsleitung<br />\n\n";
      }
    }else{
      echo "Keine Nachricht musste an die Vereinsleitung gesendet werden<br />\n";
    }
    // Jährliche Ausführung, jeweils an Silvester jeden Jahres und wenn die letzte Dienstabrechnung bereits mindestens 364 Tage her ist
    if (date("j") == "31" && date("n") == "12" && $settings['last_diensteabrechnung'] < time()-31449600){
      echo "Endabrechnung Dienste erfolgt<br />\n";
      $sql = "SELECT `mitglieder`.`mitglieder_id`,`mitglieder`.`vorname`,`mitglieder`.`nachname`,`mitglieder`.`geburtstag`,`mitglieder`.`email`,`mitglieder`.`dienste_lastyears`,`mitglieder`.`beitritt`, COUNT(`dienste`.`person`) as dienstzahl, SUM(`dienste`.`dienstart`) as kuchenzahl, COUNT(DISTINCT `dienste`.`endzeit`) - IF( SUM(`dienste`.`dienstart`) > 0, 1, 0) as standdienste
              FROM `mitglieder`
              LEFT JOIN `dienste` ON `dienste`.`person` = `mitglieder`.`mitglieder_id`
              WHERE `status` = 1 AND `startzeit` > ".mktime(0,0,0,1,1,date("Y"))." AND `startzeit` < ".mktime(23,59,59,12,31,date("Y"))."
              GROUP BY `mitglieder_id`";
      $dienst_ergebnisse = mysql_query($sql);
      while ($member = mysql_fetch_assoc($dienst_ergebnisse)){
        // Alle Mitglieder durchlaufen
        $geleisteter_dienst = $member['standdienste'];
        if ($member['kuchenzahl'] > 0){
          // Um Probleme mit der MySQL Rückgabe NULL zu vermeiden
          $geleisteter_dienst = $geleisteter_dienst + floor($dienste_result['kuchenzahl']/$settings['cakeworkcount']);
        }
        if (time()- strtotime($member['geburtstag']) < 568024668){
              // Kinder (unter 18) brauchen keine Mindestmenge an Diensten
              $zuleistende_arbeit = 0;
        }else{
            if (strtotime($member['beitritt']) < mktime(0,0,0,1,1,date("Y"))){
              // Das Mitglied ist bereits seit Beginn des Jahres oder eher noch länger Mitglied
              $zuleistende_arbeit = $settings['annualwork'] + $member['dienste_lastyears'];
            }else{
              // Das Mitglied kam im Laufe des Jahres zum Verein. Die zuleistende Arbeit wird entsprechend prozentual angepasst
              $zuleistende_arbeit = ceil($settings['annualwork'] * (abs(365 - date("z",strtotime($member['beitritt'])))/365));
              // $member['dienste_lastyears'] kann vernachlässigt werden, da er ja eh nicht vorher dabei war
            }
        }
        if ($geleisteter_dienst < $zuleistende_arbeit){
            // Mitglied hat nicht genug gearbeitet
            echo $member['vorname']." ".$member['nachname']." hat ".($zuleistende_arbeit-$geleisteter_dienst)." Dienste zu wenig gemacht<br />\n";
            $sql_dienstelastyears = mysql_query("UPDATE `mitglieder` SET `dienste_lastyears` = ".intval($zuleistende_arbeit-$geleisteter_dienst)." WHERE `mitglieder`.`mitglieder_id` = '".$member['mitglieder_id']."'");
        }elseif($member['dienste_lastyears'] > 0){
            // Das Mitglied hat genug gearbeitet, in der DB stehen jedoch noch Dienste vom letzten Jahr, dann diese jetzt entfernen (sie wurden ja abgeleistet)
            echo $member['vorname']." ".$member['nachname']." hat den Übertrag der Vorjahre komplett abgearbeitet<br />\n";
            $sql_dienstelastyears = mysql_query("UPDATE `mitglieder` SET `dienste_lastyears` = 0 WHERE `mitglieder`.`mitglieder_id` = '".$member['mitglieder_id']."'");
        }
      }
      mysql_query("UPDATE `einstellungen` SET `value`  = '".time()."' WHERE `einstellungen`.`name` = 'last_diensteabrechnung'");
    }
    echo "Ausführung Ende ".date("r")."<br />\n";
    // Output-Buffer Inhalt in Variable schreiben
    $ausgabe = ob_get_contents();
    // In die DB schreiben, dass der Cronjob ausgeführt wurde
    mysql_query("UPDATE `einstellungen` SET `value`  = '".time()."' WHERE `einstellungen`.`name` = 'lastcronrun'");
    mysql_query("UPDATE `einstellungen` SET `value`  = '".mysql_real_escape_string($ausgabe)."' WHERE `einstellungen`.`name` = 'lastcronoutput'");

}else{
  echo "Cronjob wurde heute bereits ausgeführt";
}
if (isset($_GET['output']) || isset($_GET['force'])){
    // Wird die Ausführung erzwungen oder eine Ausgabe gewünscht?
    if ($user['rights'] >= 4){
        // Rechte sind vorhanden, Output-Buffer leeren (Inhalt ausgeben)
        ob_end_flush();
    }else{
      // Rechte sind nicht vorhanden, Output löschen (nichts ausgeben)
      ob_end_clean();
    }
}else{
  // Output löschen (nichts ausgeben, außer es wurde oben bereits etwas ausgegeben)
  ob_end_clean();
}
?>