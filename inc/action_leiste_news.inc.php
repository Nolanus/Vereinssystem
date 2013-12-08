                          <h2>Aktionen</h2>
                          <ul class="nolistimg">
                            <li><img src="images/show.png" alt="" title="Anzeigen" /> <a href="news_show.php?id=<?php echo $nachricht['news_id'];?>" title="Nachricht anzeigen">Nachricht anzeigen</a></li>
                            <?php
                            if ($user['rights'] >= 4 || $nachricht['author'] == $user['mitglieder_id']){
                            ?>
                                <li><img src="images/edit.png" alt="" title="Bearbeiten" /> <a href="news_edit.php?id=<?php echo $nachricht['news_id'];?>" title="Nachricht bearbeiten">Nachricht bearbeiten</a></li>
                                <li><img src="images/trash_can.png" alt="" title="Löschen" /> <a href="news_delete.php?id=<?php echo $nachricht['news_id'];?>" title="Nachricht löschen">Nachricht löschen</a></li>
                            <?php
                            }
                            ?>
                            <li><img src="images/list.png" alt="" title="Zur Übersicht" /> <a href="news.php" title="Zurück zur Übersicht aller Nachrichten">Nachrichtenübersicht</a></li>
                          </ul>