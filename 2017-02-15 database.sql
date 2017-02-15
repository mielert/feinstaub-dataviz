-- phpMyAdmin SQL Dump
-- version 4.6.6
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Erstellungszeit: 15. Feb 2017 um 15:59
-- Server-Version: 5.7.17-0ubuntu0.16.04.1
-- PHP-Version: 5.6.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `luftdaten`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `alerts`
--

CREATE TABLE `alerts` (
  `id` int(11) NOT NULL,
  `city_id` int(11) NOT NULL,
  `start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `end` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cities`
--

CREATE TABLE `cities` (
  `city_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cities_mean`
--

CREATE TABLE `cities_mean` (
  `id` int(11) NOT NULL,
  `city_id` int(11) NOT NULL,
  `timestamp` timestamp NULL DEFAULT NULL,
  `P1h` decimal(10,1) NOT NULL,
  `P2h` decimal(10,1) NOT NULL,
  `P1d` decimal(10,1) NOT NULL,
  `P2d` decimal(10,1) NOT NULL,
  `P1h_min` decimal(10,1) NOT NULL,
  `P1h_max` decimal(10,1) NOT NULL,
  `P2h_min` decimal(10,1) NOT NULL,
  `P2h_max` decimal(10,1) NOT NULL,
  `P1h_50_min` decimal(10,1) NOT NULL,
  `P1h_50_max` decimal(10,1) NOT NULL,
  `P2h_50_min` decimal(10,1) NOT NULL,
  `P2h_50_max` decimal(10,1) NOT NULL,
  `P1min_sensor_id` int(11) NOT NULL,
  `P1max_sensor_id` int(11) NOT NULL,
  `P2min_sensor_id` int(11) NOT NULL,
  `P2max_sensor_id` int(11) NOT NULL,
  `num_sensors` int(11) DEFAULT NULL,
  `num_values` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cron_jobs`
--

CREATE TABLE `cron_jobs` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `script` varchar(512) NOT NULL,
  `interval` int(11) NOT NULL,
  `last_execution` timestamp NULL DEFAULT NULL,
  `activated` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `districts`
--

CREATE TABLE `districts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `city_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `districts_mean`
--

CREATE TABLE `districts_mean` (
  `id` int(11) NOT NULL,
  `district_id` int(11) NOT NULL,
  `timestamp` timestamp NULL DEFAULT NULL,
  `P1h` decimal(10,1) NOT NULL,
  `P2h` decimal(10,1) NOT NULL,
  `P1d` decimal(10,1) NOT NULL,
  `P2d` decimal(10,1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `sensors`
--

CREATE TABLE `sensors` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `sensors_hourly_mean`
--

CREATE TABLE `sensors_hourly_mean` (
  `id` int(11) NOT NULL,
  `sensor_id` int(11) NOT NULL,
  `lon` decimal(6,3) NOT NULL,
  `lat` decimal(6,3) NOT NULL,
  `timestamp` timestamp NULL DEFAULT NULL,
  `P1` decimal(10,1) DEFAULT NULL,
  `P2` decimal(10,1) DEFAULT NULL,
  `P1d` decimal(10,1) DEFAULT NULL,
  `P2d` decimal(10,1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `sensor_data`
--

CREATE TABLE `sensor_data` (
  `id` int(11) NOT NULL,
  `sensor_id` int(11) NOT NULL,
  `lon` decimal(6,3) NOT NULL,
  `lat` decimal(6,3) NOT NULL,
  `timestamp` timestamp NULL DEFAULT NULL,
  `P1` float NOT NULL,
  `P2` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `sensor_types`
--

CREATE TABLE `sensor_types` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `x_coordinates_districts`
--

CREATE TABLE `x_coordinates_districts` (
  `lon` decimal(6,3) NOT NULL,
  `lat` decimal(6,3) NOT NULL,
  `district_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `alerts`
--
ALTER TABLE `alerts`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`city_id`);

--
-- Indizes für die Tabelle `cities_mean`
--
ALTER TABLE `cities_mean`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `district_id` (`city_id`,`timestamp`);

--
-- Indizes für die Tabelle `cron_jobs`
--
ALTER TABLE `cron_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `districts`
--
ALTER TABLE `districts`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `districts_mean`
--
ALTER TABLE `districts_mean`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `district_id` (`district_id`,`timestamp`);

--
-- Indizes für die Tabelle `sensors`
--
ALTER TABLE `sensors`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `sensors_hourly_mean`
--
ALTER TABLE `sensors_hourly_mean`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `sensor_data`
--
ALTER TABLE `sensor_data`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sensor_id` (`sensor_id`,`timestamp`);

--
-- Indizes für die Tabelle `sensor_types`
--
ALTER TABLE `sensor_types`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `x_coordinates_districts`
--
ALTER TABLE `x_coordinates_districts`
  ADD UNIQUE KEY `lon` (`lon`,`lat`,`district_id`),
  ADD KEY `district_id` (`district_id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `alerts`
--
ALTER TABLE `alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT für Tabelle `cities`
--
ALTER TABLE `cities`
  MODIFY `city_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT für Tabelle `cities_mean`
--
ALTER TABLE `cities_mean`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3956;
--
-- AUTO_INCREMENT für Tabelle `cron_jobs`
--
ALTER TABLE `cron_jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT für Tabelle `districts`
--
ALTER TABLE `districts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
--
-- AUTO_INCREMENT für Tabelle `districts_mean`
--
ALTER TABLE `districts_mean`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=129306;
--
-- AUTO_INCREMENT für Tabelle `sensors`
--
ALTER TABLE `sensors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=645;
--
-- AUTO_INCREMENT für Tabelle `sensors_hourly_mean`
--
ALTER TABLE `sensors_hourly_mean`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=190093;
--
-- AUTO_INCREMENT für Tabelle `sensor_data`
--
ALTER TABLE `sensor_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17120770;
--
-- AUTO_INCREMENT für Tabelle `sensor_types`
--
ALTER TABLE `sensor_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
