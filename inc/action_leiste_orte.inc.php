                          <h2>Aktionen</h2>
                          <ul class="nolistimg">
                            <li><img src="images/show.png" alt="" title="Anzeigen" /> <a href="ort_show.php?id=<?php echo $ort['orts_id'];?>" title="Das Profil dieses Ortes anzeigen">Ort anzeigen</a></li>
                            <?php
                            if ($user['rights'] >= 4 || $ort['orts_id'] == $user['anschrift']){
                            ?>
                                <li><img src="images/edit.png" alt="" title="Bearbeiten" /> <a href="ort_edit.php?id=<?php echo $ort['orts_id'];?>" title="Ort bearbeiten">Ort bearbeiten</a></li>
                            <?php
                            }
                            if ($user['rights'] >= 4){
                            ?>
                            <li><img src="images/trash_can.png" alt="" title="Löschen" /> <a href="ort_delete.php?id=<?php echo $ort['orts_id'];?>" title="Ort löschen">Ort löschen</a></li>
                            <?php
                            }
                            ?>
                            <li><img src="images/list.png" alt="" title="Zur Übersicht" /> <a href="orte.php" title="Zurück zur Übersicht aller Orte">Ortsverwaltung</a></li>
                          </ul>