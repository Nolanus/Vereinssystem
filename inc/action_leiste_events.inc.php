                          <h2>Aktionen</h2>
                          <ul class="nolistimg">                       
                           <li><img src="images/show.png" alt="" title="Anzeigen" /> <a href="veranstaltung_show.php?id=<?php echo $veranstaltung['veranstaltungs_id'];?>" title="Die Veranstaltungsdetails von <?php echo htmlspecialchars($veranstaltung['veranstaltungsname'])?> anzeigen">Veranstaltungsdetails anzeigen</a></li>
                           <?php if($user['rights'] == 5){?>
                            <li><img src="images/edit.png" alt="" title="Bearbeiten" /> <a href="veranstaltung_edit.php?id=<?php echo ($veranstaltung['veranstaltungs_id']);?>" title="Die Veranstaltungsdetails von <?php echo htmlspecialchars($veranstaltung['veranstaltungsname'])?> bearbeiten">Veranstaltungsdetails bearbeiten</a></li>
                            <li><img src="images/trash_can.png" alt="" title="Löschen" /> <a href="veranstaltung_delete.php?id=<?php echo ($veranstaltung['veranstaltungs_id']);?>" title="<?php echo htmlspecialchars($veranstaltung['veranstaltungsname'])?> löschen">Veranstaltung löschen</a></li>
                          <?php }?>
                           <li><img src="images/history.png" alt="" title="Dienste" /> <a href="dienste_show.php?id=<?php echo ($veranstaltung['veranstaltungs_id']);?>" title="Dienste für <?php echo htmlspecialchars($veranstaltung['veranstaltungsname'])?> im Detail anzeigen">Dienstdetails anzeigen</a></li>
                            <li><img src="images/list.png" alt="" title="Zur Übersicht" /> <a href="veranstaltungen.php" title="Zurück zur Übersicht aller Veranstaltungen">Veranstaltungsverwaltung</a></li>
                          </ul>