<?php
// Datenbankverbindung & Kodierung einbinden
require_once("inc/connect.inc.php");

// HTML-Kopf einbinden
require_once("inc/head.inc.php");
?>
        <div id="content">
                  <h2>Rechtesystem</h2>
                  <table>
                  <!-- Richtwerte für die Spaltenbreite --->
                  <colgroup>
                    <col width="25%">
                    <col width="15%">
                    <col width="15%">
                    <col width="15%">
                    <col width="15%">
                    <col width="15%">
                  </colgroup>
                  <thead>
                    <tr>
                        <th>Aktion</th>
                        <th>Normaler Nutzer (Level 1)</th>
                        <th>Moderator (Level 2)</th>
                        <th>Abteilungsleiter (Level 3)</th>
                        <th>Vereinsvorstand (Level 4)</th>
                        <th>Administrator (Level 5)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                        <td class="firstcolumn">Mitglieder sehen</td>
                        <td colspan="3"><img src="images/success.png" /> (nur sich selber + eigene Kinder)</td>
                        <td colspan="2"><img src="images/success.png" /> (alle)</td>
                    </tr>
                    <tr>
                        <td class="firstcolumn">Mitglieder bearbeiten</td>
                        <td colspan="3"><img src="images/success.png" /> (nur sich selber + eigene Kinder, bis diese 18 sind)</td>
                        <td colspan="2"><img src="images/success.png" /> (alle)</td>
                       </tr>
                    <tr>
                        <td class="firstcolumn">Systemeinstellungen von Mitglieder bearbeiten</td>
                        <td colspan="3"><img src="images/error.png" /></td>
                        <td><img src="images/success.png" /> (alle, außer von Admins)</td>
                        <td><img src="images/success.png" /> (alle)</td>
                    </tr>
                    <tr>
                        <td class="firstcolumn">Mitglieder erstellen</td>
                        <td colspan="3"><img src="images/error.png" /></td>
                        <td colspan="2"><img src="images/success.png" /></td>
                    </tr>
                    <tr>
                        <td class="firstcolumn">Orte sehen</td>
                        <td colspan="2"><img src="images/success.png" /> (nur Veranstaltungsorte, eigenen und Geschäftsstelle)</td>
                        <td colspan="3"><img src="images/success.png" /> (alle)</td>
                    </tr>
                    <tr>
                        <td class="firstcolumn">Orte bearbeiten</td>
                        <td colspan="3"><img src="images/error.png" /> (nur eigener über Mitglied-Bearbeiten änderbar)</td>
                        <td colspan="2"><img src="images/success.png" /> (alle)</td>
                    </tr>
                    <tr>
                        <td class="firstcolumn">Orte erstellen</td>
                        <td colspan="3"><img src="images/error.png" /> (nur indirekt über Mitglied-Bearbeiten)</td>
                        <td colspan="2"><img src="images/success.png" /></td>
                    </tr>
                    <tr>
                        <td class="firstcolumn">Orte löschen</td>
                        <td colspan="3"><img src="images/error.png" /></td>
                        <td><img src="images/success.png" /> (nur aus dem System)</td>
                        <td><img src="images/success.png" /> (auch aus der Datenbank)</td>
                    </tr>
                    <tr>
                        <td class="firstcolumn">Veranstaltungen sehen</td>
                        <td colspan="5"><img src="images/success.png" /> (alle)</td>
                    </tr>
                    <tr>
                        <td class="firstcolumn">Veranstaltungen bearbeiten</td>
                        <td colspan="2"><img src="images/error.png" /> (nur eigene, falls Verantwortlicher)</td>
                        <td colspan="3"><img src="images/success.png" /> (alle)</td>
                    </tr>
                    <tr>
                        <td class="firstcolumn">Veranstaltungen erstellen</td>
                        <td colspan="2"><img src="images/error.png" /></td>
                        <td colspan="3"><img src="images/success.png" /></td>
                    </tr>
                    <tr>
                        <td class="firstcolumn">Sich selber zu Diensten ansetzen</td>
                        <td colspan="3"><img src="images/success.png" /> (bis 24h vorher)</td>
                        <td colspan="2"><img src="images/success.png" /> (jederzeit)</td>
                    </tr>
                    <tr>
                        <td class="firstcolumn">Andere zu Diensten ansetzen</td>
                        <td colspan="2"><img src="images/error.png" /> (außgenommen eigene Kinder oder der Verantwortlicher (letzterer jederzeit))</td>
                        <td colspan="1"><img src="images/success.png" /> (bis 24h vorher)</td>
                        <td colspan="2"><img src="images/success.png" /> (jederzeit)</td>
                    </tr>
                    <tr>
                        <td class="firstcolumn">Abteilungen sehen</td>
                        <td colspan="5"><img src="images/success.png" /></td>
                    </tr>
                    <tr>
                        <td class="firstcolumn">Abteilungen bearbeiten</td>
                        <td colspan="2"><img src="images/error.png" /></td>
                        <td colspan="1"><img src="images/error.png" /> (außgenommen er ist Abteilungsleiters)</td>
                        <td colspan="2"><img src="images/success.png" /></td>
                    </tr>
                    <tr>
                        <td class="firstcolumn">Abteilungen erstellen</td>
                        <td colspan="3"><img src="images/error.png" /></td>
                        <td colspan="2"><img src="images/success.png" /></td>
                    </tr>
                    <tr>
                        <td class="firstcolumn">Nachrichten sehen</td>
                        <td colspan="5"><img src="images/success.png" /></td>
                    </tr>
                    <tr>
                        <td class="firstcolumn">Nachrichten bearbeiten</td>
                        <td colspan="1"><img src="images/error.png" /></td>
                        <td colspan="2"><img src="images/success.png" /> (nur eigene)</td>
                        <td colspan="2"><img src="images/success.png" /></td>
                    </tr>
                    <tr>
                        <td class="firstcolumn">Nachrichten erstellen</td>
                        <td colspan="1"><img src="images/error.png" /></td>
                        <td colspan="4"><img src="images/success.png" /></td>
                    </tr>
                    <tr>
                        <td class="firstcolumn">Nachrichten löschen</td>
                        <td colspan="1"><img src="images/error.png" /></td>
                        <td colspan="2"><img src="images/error.png" /> (nur eigene)</td>
                        <td colspan="2"><img src="images/success.png" /></td>
                    </tr>
                    <tr>
                        <td class="firstcolumn">Rechtelevel der Mitglieder ändern</td>
                        <td colspan="3"><img src="images/error.png" /></td>
                        <td colspan="1"><img src="images/success.png" /> (Aber nicht von Admins)</td>
                        <td colspan="1"><img src="images/success.png" /></td>
                    </tr>
                    <tr>
                        <td class="firstcolumn">Einstellungen ändern + Einblick in Systemwerte</td>
                        <td colspan="4"><img src="images/error.png" /></td>
                        <td colspan="1"><img src="images/success.png" /></td>
                    </tr>
                  </tbody>
                  </table>
    		<div class="clearit">&nbsp;</div>
        </div>
<?php
// HTML-Fußbereich einbinden
require_once("inc/footer.inc.php");
?>