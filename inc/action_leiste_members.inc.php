                          <h2>Aktionen</h2>
                          <ul class="nolistimg">
                            <li><img src="images/show.png" alt="" title="Anzeigen" /> <a href="member_show.php?id=<?php echo $member['mitglieder_id'];?>" title="Das Mitgliedsprofil von <?php echo htmlspecialchars($member['vorname']." ".$member['nachname'])?> anzeigen">Mitgliedsprofil anzeigen</a></li>
                            <?php
                            if ($user['rights'] >= 4 || $member['mitglieder_id'] == $user['mitglieder_id'] || ($member['parent1'] == $user['mitglieder_id'] && time()-strtotime($member['geburtstag']) < 568024668) || ($member['parent2'] == $user['mitglieder_id'] && time()-strtotime($member['geburtstag']) < 568024668)){
                              // Um folgenden Link sehen zu können, muss man entweder Rechtelevel 4 haben, das Mitglied selber oder ein Elternteil des Mitglieds
                            ?>
                                <li><img src="images/edit.png" alt="" title="Bearbeiten" /> <a href="member_edit.php?id=<?php echo ($member['mitglieder_id']);?>" title="Das Mitgliedsprofil von <?php echo htmlspecialchars($member['vorname']." ".$member['nachname'])?> bearbeiten">Mitgliedsprofil bearbeiten</a></li>
                            <?php
                            }
                            if (($user['rights'] == 4 && $member['rights'] <= 4) || $user['rights'] == 5){
                              // Nur anzeigen wenn entweder der aktuelle Nutzer hat Level 4 und die zu bearbeitende Person hat 4 oder weniger (also ist kein Admin) ODER der aktuelle Nutzer ist Admin
                            ?>
                                <li><img src="images/gear.png" alt="" title="Einstellungen bearbeiten" /> <a href="member_systemedit.php?id=<?php echo ($member['mitglieder_id']);?>" title="Einstellungen für <?php echo htmlspecialchars($member['vorname']." ".$member['nachname'])?> bearbeiten">Einstellungen bearbeiten</a></li>
                            <?php
                            }elseif($member['mitglieder_id'] == $user['mitglieder_id'] && $user['rights'] < 4){
                              // Aktueller Nutzer ist das aktuelle Mitglied und er hat ein Rechtlevel von kleiner 4 = Option zum passwortändern über member_pwordchange.php anbieten
                            ?>
                                <li><img src="images/password.png" alt="" title="Passwort ändern" /> <a href="member_changepword.php" title="Eigenes Passwort ändern">Passwort ändern</a></li>
                            <?php
                            }
                            ?>
                            <li><img src="images/list.png" alt="" title="Zur Übersicht" /> <a href="members.php" title="Zurück zur Übersicht aller Mitglieder">Mitgliederverwaltung</a></li>
                          </ul>