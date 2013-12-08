-- phpMyAdmin SQL Dump
-- version 4.0.6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 08. Dez 2013 um 21:08
-- Server Version: 5.5.33
-- PHP-Version: 5.5.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `vereinssystem`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `abteilungen`
--

CREATE TABLE IF NOT EXISTS `abteilungen` (
  `abteilungs_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text COLLATE utf8_unicode_ci NOT NULL,
  `beschreibung` text COLLATE utf8_unicode_ci NOT NULL,
  `homepage` text COLLATE utf8_unicode_ci NOT NULL,
  `abteilungsleiter` int(11) NOT NULL COMMENT 'Mitglieds-ID des Abteilungsleiters',
  `aktivenumlage` int(11) NOT NULL COMMENT 'monatlicher Betrag in Cent',
  `status` int(11) NOT NULL DEFAULT '1' COMMENT '1=Aktiv;2=Papierkorb',
  PRIMARY KEY (`abteilungs_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Abteilungen des Vereins' AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `abteilungszugehoerigkeit`
--

CREATE TABLE IF NOT EXISTS `abteilungszugehoerigkeit` (
  `mitglied` int(11) NOT NULL COMMENT 'Mitglieds-ID des Mitgleids',
  `abteilung` int(11) NOT NULL COMMENT 'Abteilungs-ID der Abteilung',
  `aktiv` int(11) NOT NULL DEFAULT '1' COMMENT '0=Inaktiv;1=Aktiv (wichtig für Aktivenumlage)',
  `beitrittdate` date NOT NULL COMMENT 'Datum, wann der Abteilung beigetreten wurde',
  PRIMARY KEY (`mitglied`,`abteilung`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Zugehörigkeit der Vereinsmitglieder zu den Abteilungen';

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `dienste`
--

CREATE TABLE IF NOT EXISTS `dienste` (
  `dienst_id` int(11) NOT NULL AUTO_INCREMENT,
  `event` int(11) NOT NULL COMMENT 'Veranstaltungs-ID des Events',
  `dienstart` int(11) NOT NULL COMMENT '0= Standdienst; Alternativ Anzahl der Kuchen',
  `person` int(11) NOT NULL COMMENT 'Mitglieder-ID der ausführenden/diensthabenden Person',
  `startzeit` int(11) NOT NULL COMMENT 'Unix-Timestamp, wann der Dienst startet',
  `endzeit` int(11) NOT NULL COMMENT 'Unix-Timestamp, wann der Dienst endet',
  `stand` int(11) NOT NULL COMMENT 'Bei mehreren gleichzeitig anwesenden Person die jeweilige nummer',
  `erstellt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`dienst_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Diensteinteilungen' AUTO_INCREMENT=75 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `einstellungen`
--

CREATE TABLE IF NOT EXISTS `einstellungen` (
  `name` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Einstellungen';

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `mitglieder`
--

CREATE TABLE IF NOT EXISTS `mitglieder` (
  `mitglieder_id` int(11) NOT NULL AUTO_INCREMENT,
  `mitgliedsnummer` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `nachname` text COLLATE utf8_unicode_ci NOT NULL,
  `vorname` text COLLATE utf8_unicode_ci NOT NULL,
  `geschlecht` int(11) NOT NULL COMMENT '0=nicht gesetzt;1=männlich;2=weiblich',
  `geburtstag` date NOT NULL COMMENT 'Geburtstag als MySQL Date-Element',
  `beitritt` date NOT NULL COMMENT 'Beitrittstag als MySQL-Date Element',
  `anschrift` int(11) NOT NULL COMMENT 'Orts-ID des Wohnortes',
  `handy` text COLLATE utf8_unicode_ci NOT NULL,
  `email` text COLLATE utf8_unicode_ci NOT NULL,
  `mitgliedschaft` int(11) NOT NULL COMMENT '0=Normal; 1=Unterstützende; 2=Ruhende',
  `abrechnung` int(11) NOT NULL COMMENT '0=Bankeinzug;1=Überweisung;etc...',
  `kontoinhaber` text COLLATE utf8_unicode_ci NOT NULL,
  `kontonummer` int(11) NOT NULL,
  `bankname` text COLLATE utf8_unicode_ci NOT NULL,
  `blz` int(11) NOT NULL,
  `dienste_lastyears` int(11) NOT NULL COMMENT 'Enthält die Anzahl der Dienste, die in den letzten Jahren nicht getätigt wurden',
  `parent1` int(11) NOT NULL COMMENT 'Mutter',
  `parent2` int(11) NOT NULL COMMENT 'Vater',
  `notizen` text COLLATE utf8_unicode_ci NOT NULL,
  `username` varchar(150) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Neben der E-Mail alternativer Name zum Einloggen',
  `passwort` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'gespeichert als MD5-Hash',
  `rights` int(11) NOT NULL DEFAULT '1' COMMENT 'Rechtelevel 0=gesperrt 1=normal 5=admin',
  `lastbirthdaymail` int(11) NOT NULL COMMENT 'Unix-Timestamp des letzten Sendens der Geburtstagsmail',
  `lastjubileemail` int(11) NOT NULL COMMENT 'Unix-Timestamp des letzten Sendens der Jubiläumsmail',
  `lastlogintime` int(11) NOT NULL COMMENT 'Unix-Timestamp des (vor-)letzten Logins',
  `lastloginip` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'IP-Adresse des (vor-)letzten Logins',
  `currentlogintime` int(11) NOT NULL COMMENT 'Unix-Timestamp des aktuellen/letzten Logins',
  `currentloginip` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'IP-Adresse des aktuellen/letzten Logins',
  `status` int(11) NOT NULL DEFAULT '1' COMMENT '1=Aktiv;2=Papierkorb(+Gesperrt)',
  PRIMARY KEY (`mitglieder_id`),
  UNIQUE KEY `mitgliedsnummer` (`mitgliedsnummer`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Vereinsmitglieder' AUTO_INCREMENT=23 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `news`
--

CREATE TABLE IF NOT EXISTS `news` (
  `news_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `minright` int(11) NOT NULL COMMENT 'Mindesterechtelevel, um den Eintrag zu sehen',
  `author` int(11) NOT NULL COMMENT 'Mitglieder-ID des Erstellers',
  `created` int(11) NOT NULL,
  `lastchange` int(11) NOT NULL,
  `status` int(11) NOT NULL COMMENT '1=Aktiv;2=Papierkorb',
  PRIMARY KEY (`news_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Nachrichtenbeiträge' AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `orte`
--

CREATE TABLE IF NOT EXISTS `orte` (
  `orts_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text COLLATE utf8_unicode_ci NOT NULL,
  `strasse` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `hausnummer` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `plz` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `ort` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `telefon` varchar(250) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Telefonnummer (Format: XXX/XXXX)',
  `typ` int(11) NOT NULL COMMENT '0 = Nicht gesetzt, 1 = Wohnort, 2 = Veranstaltungsort',
  `createdby` int(11) NOT NULL COMMENT 'Nutzer-ID des Erstellers',
  `status` int(11) NOT NULL DEFAULT '1' COMMENT '1=Aktiv;2=Papierkorb',
  PRIMARY KEY (`orts_id`),
  UNIQUE KEY `strasse` (`strasse`,`hausnummer`,`plz`,`ort`,`telefon`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Wohn, Veranstaltungsorte, etc.' AUTO_INCREMENT=48 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `veranstaltungen`
--

CREATE TABLE IF NOT EXISTS `veranstaltungen` (
  `veranstaltungs_id` int(11) NOT NULL AUTO_INCREMENT,
  `veranstaltungsname` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `beschreibung` text COLLATE utf8_unicode_ci NOT NULL,
  `ort` int(11) NOT NULL COMMENT 'Orts-ID des Veranstaltungsortes',
  `ansprechpartner` int(11) NOT NULL COMMENT 'Mitglieder-ID des Hauptverantwortlichen',
  `startzeit` int(11) NOT NULL COMMENT 'Unix-Timestamp des Veranstaltungsbegins',
  `endzeit` int(11) NOT NULL COMMENT 'Unix-Timestamp des Veranstaltungsendes',
  `mindienste` int(11) NOT NULL DEFAULT '0',
  `minkuchen` int(11) NOT NULL DEFAULT '0',
  `dienstbeschreibung` text COLLATE utf8_unicode_ci NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`veranstaltungs_id`),
  UNIQUE KEY `name` (`veranstaltungsname`,`ort`,`startzeit`,`endzeit`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Events, Veranstaltungen' AUTO_INCREMENT=4 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
