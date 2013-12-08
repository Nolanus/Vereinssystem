# Vereinssystem

PHP/MySQL basierendes System zur Verwaltung von Mitgliedern, Kontaktdaten, Abteilungszugehörigkeiten, Orten, Veranstaltungen und Mitgliederarbeiten in einem Verein. Es können automatisch E-Mails zum Geburtstag oder Mitgliedsjubiläum versendet werden.

### Installallation
Erstellen Sie mittels der Datei table_structure_vereinssystem.sql die MySQL Datenbank, indem Sie etwa in phpMyAdmin die Import Funktion verwenden. Erstellen Sie anschließend manuell einen Eintrag in der _mitglieder_ Tabelle. Wichtig ist insbesondere der Nutzername und das Passwort. Setzen Sie die Eintrag _rights_ auf 5 (Administrator-Rechte).

Beispiel SQL-Befehl:

```SQL
INSERT INTO `mitglieder`
(`mitglieder_id`, `mitgliedsnummer`, `nachname`, `vorname`, `geschlecht`, `geburtstag`, `beitritt`, `anschrift`, `handy`, `email`, `mitgliedschaft`, `abrechnung`, `parent1`, `parent2`, `notizen`, `username`, `passwort`, `rights`, `status`) VALUES
(1, '0000000001', 'Nachname', 'Vorname', 1, '2000-01-01', '2001-01-01', 0, '0123/45678910', 'mail@adresse.com', 1, 0, 4, 0, '', 'Nutzer.Name', 'PasswortMD5Hash', 5, 1);
```

Anschließend können Sie sich mit dem gewählten Nutzernamen und Passwort im System anmelden, um von dort alle weiteren Administrationsaufgaben durchzuführen.

### Über das Projekt
Das Vereinssystem ist eine Abitursarbeit von Nicolas Jourdan und Sebastian Fuss. Es darf kostenlos für nichtkommerzielle Zwecke verwendet und erweitert werden.
