<?php
// Datenbankverbindung & Kodierung einbinden
require_once("inc/connect.inc.php");

// HTML-Kopf einbinden
require_once("inc/head.inc.php");
?>
        <div id="content">
                  <h2>Demo-Tabelle</h2>
                  <table>
                  <!-- Richtwerte für die Spaltenbreite --->
                  <colgroup>
                    <col width="10%">
                    <col width="20%">
                    <col width="20%">
                    <col width="25%">
                    <col width="25%">
                  </colgroup>
                  <thead>
                    <tr>
                        <th>Kopf1</th>
                        <th>Kopf2</th>
                        <th>Kopf3</th>
                        <th>Kopf4 (breite Spalte)</th>
                        <th>Kopf5 (breite Spalte)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                        <td class="firstcolumn">Daten</td>
                        <td>Daten</td>
                        <td>Daten</td>
                        <td>Daten</td>
                        <td>Daten</td>
                    </tr>
                    <tr>
                        <td class="firstcolumn">Daten</td>
                        <td>Daten</td>
                        <td>Daten</td>
                        <td>Daten</td>
                        <td>Daten</td>
                    </tr>
                    <tr>
                        <td class="firstcolumn">Daten</td>
                        <td>Daten</td>
                        <td>Daten</td>
                        <td>Daten</td>
                        <td>Daten</td>
                    </tr>
                    <tr>
                        <td class="firstcolumn">Daten</td>
                        <td>Daten</td>
                        <td>Daten</td>
                        <td>Daten</td>
                        <td>äöü ÄÖÜ ß</td>
                    </tr>
                    <tr>
                        <td class="firstcolumn">Daten</td>
                        <td>Daten</td>
                        <td>Daten</td>
                        <td>Daten</td>
                        <td>Daten</td>
                    </tr>
                  </tbody>
                  </table>
    		<div class="clearit">&nbsp;</div>
        </div>
<?php
// HTML-Fußbereich einbinden
require_once("inc/footer.inc.php");
?>