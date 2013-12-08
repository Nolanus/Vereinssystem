                          <h2>Aktionen Abteilung</h2>
                          <ul class="nolistimg">
                            <li><img src="images/show.png" alt="" title="Anzeigen" /> <a href="abteilung_show.php?id=<?php echo $abteilung['abteilungs_id'];?>" title="Das Profil dieser Abteilung anzeigen">Abteilung anzeigen</a></li>
                            <?php
                            if (($user['rights'] == 3 && $abteilung['abteilungsleiter'] == $user['mitglieder_id']) || $user['rights'] >= 4){
                              // Entweder man ist Abteilungsleiter mit Rechtelevel 3 oder hat Rechtelevel 4 oder höher
                            ?>
                                <li><img src="images/edit.png" alt="" title="Bearbeiten" /> <a href="abteilung_edit.php?id=<?php echo $abteilung['abteilungs_id'];?>" title="Abteilung bearbeiten">Abteilung bearbeiten</a></li>
                                <li><img src="images/trash_can.png" alt="" title="Löschen" /> <a href="abteilung_delete.php?id=<?php echo $abteilung['abteilungs_id'];?>" title="Abteilung löschen">Abteilung löschen</a></li>
                            <?php
                            }
                            ?>
                            <li><img src="images/list.png" alt="" title="Zur Übersicht" /> <a href="abteilungen.php" title="Zurück zur Übersicht aller Abteilungen">Abteilungsverwaltung</a></li>
                          </ul>
                           <?php if (($user['rights'] == 3 && $abteilung['abteilungsleiter'] == $user['mitglieder_id']) || $user['rights'] >= 4){
                              // Entweder man ist Abteilungsleiter mit Rechtelevel 3 oder hat Rechtelevel 4 oder höher
                            ?>
                          <h2>Aktionen Abteilungsmitglieder</h2>
                          <ul class="nolistimg">
                            <li><img src="images/add.png" alt="" title="Abteilungsmitgliedschaft hinzufügen" /> <a href="abteilung_mitgliedschaft_add.php?id=<?php echo $abteilung['abteilungs_id'];?>" title="Mitglied zur Abteilung hinzufügen">Abteilungsmitgliedschaft hinzufügen</a></li>
                          <?php //Wenn aktuelle eine Abteilungsmitgliedschaft angesehen wird, die entsprechenden Menupunkte anzeigen
                          if (isset($abteilung['mitglieder_id'])){ ?>
                              <li><img src="images/show.png" alt="" title="Abteilungsmitgliedschaft anzeigen" /> <a href="abteilung_mitgliedschaft_show.php?id=<?php echo $abteilung['abteilungs_id']."&amp;who=".$abteilung['mitglieder_id'];?>" title="Abteilungsmitgliedschaft anzeigen">Abteilungsmitgliedschaft anzeigen</a></li>
                              <li><img src="images/edit.png" alt="" title="Mitglieder der Abteilung verwalten" /> <a href="abteilung_mitgliedschaft_edit.php?id=<?php echo $abteilung['abteilungs_id']."&amp;who=".$abteilung['mitglieder_id'];?>" title="Abteilungsmitgliedschaft bearbeiten">Abteilungsmitgliedschaft bearbeiten</a></li>
                              <li><img src="images/trash_can.png" alt="" title="Mitglieder der Abteilung verwalten" /> <a href="abteilung_mitgliedschaft_delete.php?id=<?php echo $abteilung['abteilungs_id']."&amp;who=".$abteilung['mitglieder_id'];?>" title="Abteilungsmitgliedschaft löschen">Abteilungsmitgliedschaft löschen</a></li>
                          <?php
                          }
                          ?>
                          <li><img src="images/group.png" alt="" title="Mitglieder der Abteilung verwalten" /> <a href="abteilung_mitglieder.php?id=<?php echo $abteilung['abteilungs_id'];?>" title="Mitglieder der Abteilung verwalten">Abteilungsmitgliedschaften verwalten</a></li>
                          </ul>
                        <?php
                        }
                        ?>
