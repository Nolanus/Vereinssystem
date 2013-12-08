<?php
if ($user['rights'] >= 4){
  // Nur Ab Rechtelevel 4 hier anzeigen, wer zunächst Geburtstag hat
?>
    <h2>Nächste Geburtstage</h2>
    <ul>
    <?php
    // http://www.tsc-web.net/archive/2007/12/mysql-query-howto-select-upcoming-birthdays/
    $sql = "SELECT  `mitglieder`.*,
                    DATE_FORMAT(NOW(),'%Y' ) - DATE_FORMAT( geburtstag,  '%Y' ) + IF( DATE_FORMAT( geburtstag,  '%m%d' ) < DATE_FORMAT( NOW( ) ,  '%m%d' ) , 1, 0 ) AS new_age,
                    DATEDIFF( geburtstag + INTERVAL YEAR( NOW( ) ) - YEAR( geburtstag ) + IF( DATE_FORMAT( NOW( ) ,  '%m%d' ) > DATE_FORMAT( geburtstag,  '%m%d' ) , 1, 0 ) YEAR, NOW( ) ) AS days_to_birthday
              FROM mitglieder
              HAVING days_to_birthday < 30
              ORDER BY days_to_birthday ASC
              LIMIT 0 , 15";
    $upcoming = mysql_query($sql);
    while ($member = mysql_fetch_assoc($upcoming)){
      echo "<li>";
      switch ($member['days_to_birthday']) {
        case 0:
          echo "<span class=\"akzentgruen\">Heute</span>";
        break;
        case 1:
          echo "<span class=\"akzentgruen\">Morgen</span>";
        break;
        default:
          echo "In ".$member['days_to_birthday']." Tagen";
      }
      echo ": <a href=\"member_show.php?id=".$member['mitglieder_id']."\" title=\"Zum Mitgliedsprofil\">".$member['vorname']." ".$member['nachname']."</a> (".$member['new_age'].")</li>";
    }
    echo "</ul>";
    }

    //Gesamtgeleistete Dienste sind Zeitdienste + Kuchendienste mal Konstante
          $sql_dienste = "SELECT COUNT(`dienste`.`person`) as dienstzahl, SUM(`dienste`.`dienstart`) as kuchenzahl, COUNT(DISTINCT `dienste`.`endzeit`) - IF( SUM(`dienste`.`dienstart`) > 0, 1, 0) as standdienste
            FROM `mitglieder`
            LEFT JOIN `dienste` ON `dienste`.`person` = `mitglieder`.`mitglieder_id`
            WHERE `status` = 1 AND `startzeit` > ".mktime(0,0,0,1,1,date("Y"))." AND `startzeit` < ".mktime(23,59,59,12,31,date("Y"))." AND `mitglieder_id` = '".$user['mitglieder_id']."'
            GROUP BY `mitglieder_id`";
          $dienste_result = mysql_query($sql_dienste);
          $dienst_daten = mysql_fetch_assoc($dienste_result);
          $geleisteter_dienst = intval($dienst_daten['standdienste']);
          if ($dienst_daten['kuchenzahl'] > 0){
            // Um Probleme mit der MySQL Rückgabe NULL zu vermeiden
            $geleisteter_dienst = $geleisteter_dienst + floor($dienst_daten['kuchenzahl']/$settings['cakeworkcount']);
          }
          if (time()- strtotime($user['geburtstag']) < 568024668){
                // Kinder (unter 18) brauchen keine Mindestmenge an Diensten
                $zuleistende_arbeit = 0;
          }else{
                if (strtotime($user['beitritt']) < mktime(0,0,0,1,1,date("Y"))){
                  // Das Mitglied ist bereits seit Beginn des Jahres oder eher noch länger Mitglied
                  $zuleistende_arbeit = $settings['annualwork'] + $user['dienste_lastyears'];
                }else{
                  // Das Mitglied kam im Laufe des Jahres zum Verein. Die zuleistende Arbeit wird entsprechend prozentual angepasst
                  $zuleistende_arbeit = ceil($settings['annualwork'] * (abs(365 - date("z",strtotime($user['beitritt'])))/365));
                  // $user['dienste_lastyears'] kann vernachlässigt werden, da er ja eh nicht vorher dabei war
                }
          }
          if ($zuleistende_arbeit > 0){
            $prozent = round($geleisteter_dienst/$zuleistende_arbeit*100,0);
            if ($prozent > 100){
              $prozent = 100;
            }
            //Geleistete Dienste dieses Jahr werden angezeigt
            echo "<h2>Geleistete Vereinsdienste</h2>\n<p>Geleistete Dienste (dieses Jahr): </p><p><img src=\"statusbar.php?value=".$prozent."\" alt=\"".$prozent." % geleiset\" class=\"imageinline\" /></p>";
          }

      //Wenn der User bereits über länger als Jahresbeginn Mitglied ist wird auch der Balken für die Gesamtmitgliedschaft ausgegeben
      if (date("Y",strtotime($user['beitritt'])) < date("Y")){
          $sql_dienste = "SELECT COUNT(`dienste`.`person`) as dienstzahl, SUM(`dienste`.`dienstart`) as kuchenzahl, COUNT(DISTINCT `dienste`.`endzeit`) - IF( SUM(`dienste`.`dienstart`) > 0, 1, 0) as standdienste
            FROM `mitglieder`
            LEFT JOIN `dienste` ON `dienste`.`person` = `mitglieder`.`mitglieder_id`
            WHERE `status` = 1 AND `mitglieder_id` = '".$user['mitglieder_id']."'
            GROUP BY `mitglieder_id`";
          $dienste_result = mysql_query($sql_dienste);
          $dienst_daten = mysql_fetch_assoc($dienste_result);
          $geleisteter_dienst = intval($dienst_daten['standdienste']);
          if ($dienst_daten['kuchenzahl'] > 0){
            // Um Probleme mit der MySQL Rückgabe NULL zu vermeiden
            $geleisteter_dienst = $geleisteter_dienst + floor($dienst_daten['kuchenzahl']/$settings['cakeworkcount']);
          }
          if (time()- strtotime($user['geburtstag']) < 568024668){
                // Kinder (unter 18) brauchen keine Mindestmenge an Diensten
                $zuleistende_arbeit = 0;
          }else{
                // Es ist ein Erwachsener
                if ((strtotime($user['geburtstag'])+568024668) > strtotime($user['beitritt'])){
                  // Das Mitglied ist vor seinem 18. Geburtstag dem Verein beigetreten, ist nun aber 18
                  $bday18_timestamp = strtotime($user['geburtstag']);
                  $jahre_dabei = ceil((time() - mktime(0,0,0,date("n",$bday18_timestamp),date("d",$bday18_timestamp),date("Y",$bday18_timestamp)+18))/31556926);
                  $zuleistende_arbeit = $jahre_dabei*$settings['annualwork'];
                }else{
                  // Als das Mitglied dem Verein beitratt, war es bereits 18 Jahre alt
                  $jahre_dabei = ceil((time() - mktime(0,0,0,1,1,date("Y",strtotime($user['beitritt']))+1))/31556926);
                  // Komplette Jahre, die das Mitglied bereits dabei ist und die Einheiten, die er im Jahr seiner Mitgliedschaft bekam (vermutlich kam er nicht am 1.1., also prozentuallen Anteil an jährlicher Leistung dafür vergeben) berechnen
                  $zuleistende_arbeit = ceil($settings['annualwork'] * (abs(365 - date("z",strtotime($user['beitritt'])))/365)) + $jahre_dabei*$settings['annualwork'];
                }
          }
          if ($zuleistende_arbeit > 0){
            $prozent = round($geleisteter_dienst/$zuleistende_arbeit*100,0);
            if ($prozent > 100){
              $prozent = 100;
            }
            echo "<p>Geleistete Dienste (gesamte Mitgliedschaft): </p><p><img src=\"statusbar.php?value=".$prozent."\" alt=\"".$prozent." % geleiset\" class=\"imageinline\" /></p>";
          }
      }
    //echo "<table><tbody>";
    // echo '<td class="firscolumn">Geleistete Dienste</td>'
    //echo "</tbody></table>";
    ?>
    <?php

    // Gesamtbetrag setzen
    $gesamt = 0;
    ?>
    <h2>Meine Finanzen</h2>
    <table class="">
    <!-- Richtwerte für die Spaltenbreite -->
    <colgroup>
      <col width="50%" />
      <col width="50%" />
    </colgroup>
    <tr>
        <td class="firstcolumn">Mitgliedsbeitrag</td>
        <td class="textright">
        <?php
        if (time()-strtotime($user['geburtstag']) < 568024668){
            // Es ist ein Kind
            echo number_format($settings['beitrag_kinder']/100,2)." €";
            $gesamt += $settings['beitrag_kinder'];
        }else{
            // Mitglied wird als Erwachsener behandelt
            echo number_format($settings['beitrag_erwachsener']/100,2)." €";
            $gesamt += $settings['beitrag_erwachsener'];
        }
        ?>
        </td>
    </tr>
    <tr>
        <td class="firstcolumn">Aktivenumlagen</td>
        <td class="textright">
        <?php
        // Alle Abteilungsmitgliedschaften der Person abrufen, bei denen er "aktives Mitglied" ist
        $sql = "SELECT `aktivenumlage` FROM `abteilungszugehoerigkeit`
                JOIN `abteilungen` ON `abteilungszugehoerigkeit`.`abteilung` = `abteilungen`.`abteilungs_id`
                WHERE `mitglied` = '".$user['mitglieder_id']."' AND `aktiv` = 1";
        $result = mysql_query($sql);
        if (mysql_num_rows($result) == 0){
          // Mitglied ist in keiner Abteilung
          echo "0.00 €";
        }else{
          $abteilungen_gesamt = 0;
          while ($eintrag = mysql_fetch_assoc($result)){
            $abteilungen_gesamt += $eintrag['aktivenumlage'];
          }
          $gesamt += $abteilungen_gesamt;
          echo number_format($abteilungen_gesamt/100,2)." €";
        }
        ?>
        </td>
        </tr>
    <tr>
        <td class="firstcolumn">Gesamt (monatl.)</td>
        <td class="textright"><?php echo number_format($gesamt/100,2);?> €</td>
    </tr>
    </table>
    <p><a href="member_finanzen.php" title="Finanzdetails anzeigen" class="floatright">Details</a></p>

    <?php
    $sql_lastdienste = "SELECT `veranstaltungen`.`veranstaltungsname`,`veranstaltungen`.`veranstaltungs_id`,`dienste`.`startzeit`,`dienste`.`endzeit`, `dienste`.`dienstart`,`mitglieder`.`vorname`,`mitglieder`.`nachname`,`mitglieder`.`mitglieder_id`
                FROM `dienste` LEFT JOIN `veranstaltungen` ON `veranstaltungen`.`veranstaltungs_id` = `dienste`.`event` LEFT JOIN `mitglieder` ON `dienste`.`person` = `mitglieder`.`mitglieder_id` ORDER BY  `dienste`.`erstellt` DESC LIMIT 0,2";
                // GROUP BY `person` sollte verhindern, dass nur Einträge einer Person angezeigt werden, dann geht aber aufgrund einer MySQL Bugs die Sortierung nach `erstellt` verloren
    $lastdienste_result = mysql_query($sql_lastdienste);
    echo mysql_error();
    if (mysql_num_rows($lastdienste_result) > 0){
      echo "<h2>Letzte Eintragungen</h2>\n<ul>";
      while ($lastdienst = mysql_fetch_assoc($lastdienste_result)){
        echo "<li>";
        if ($user['rights'] >= 4){
          echo "<a href=\"member_show.php?id=".$lastdienst['mitglieder_id']."\" title=\"Mitgliederprofil anzeigen\">".htmlspecialchars($lastdienst['vorname']." ".$lastdienst['nachname'])."</a>";
        }else{
          echo htmlspecialchars($lastdienst['vorname']." ".$lastdienst['nachname']);
        }
        if ($lastdienst['dienstart'] == 0){
          // Standdienst
          echo " hat sich für Standdienst von ".date("H:i", $lastdienst["startzeit"])." bis ".date("H:i", $lastdienst["endzeit"])." Uhr bei <a href=\"veranstaltung_show.php?id=".$lastdienst['veranstaltungs_id']."\" title=\"Veranstaltung anzeigen\">".$lastdienst["veranstaltungsname"]."</a> eingetragen.";
        }else{
          echo " hat sich für das Mitbringen von ".$lastdienst['dienstart']." Kuchen bei <a href=\"veranstaltung_show.php?id=".$lastdienst['veranstaltungs_id']."\" title=\"Veranstaltung anzeigen\">".$lastdienst["veranstaltungsname"]."</a> eingetragen.";
        }
        echo "</li>";
      }
      echo "</ul>";
    }
    ?>
    <p><a href="rechtesystem.php">Überblick über das Rechtesystem!</a></p>