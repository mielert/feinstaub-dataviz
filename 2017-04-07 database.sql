-- phpMyAdmin SQL Dump
-- version 4.6.6
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Erstellungszeit: 07. Apr 2017 um 17:05
-- Server-Version: 5.7.17-0ubuntu0.16.04.2
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
  `region_id` int(11) NOT NULL,
  `start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
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
  `name` varchar(255) NOT NULL,
  `script` varchar(512) NOT NULL,
  `interval` int(11) NOT NULL,
  `last_execution` timestamp NULL DEFAULT NULL,
  `done_at` timestamp NULL DEFAULT NULL,
  `activated` int(11) NOT NULL,
  `last_result` int(11) DEFAULT NULL,
  `message` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `cron_jobs`
--

INSERT INTO `cron_jobs` (`id`, `name`, `script`, `interval`, `last_execution`, `done_at`, `activated`, `last_result`, `message`) VALUES
(1, 'Collecting and filtering data from api.luftdaten.info', 'crawler_luftdaten_api.php', 300, '2017-04-07 15:03:01', '2017-04-07 15:03:29', 1, 0, 'Ok | 2522 datasets (first: 2017-04-07 14:58:01, last: 2017-04-07 15:03:01) '),
(2, 'Collecting data from LUBW', 'crawler_lubw.php', 1800, '2017-04-07 14:37:01', '2017-04-07 14:37:02', 1, 0, 'Ok | 2017-04-07 16:00:00: DEBW013: 31 DEBW118: 39 '),
(3, 'Generating backup of archive.luftdaten.info', 'crawler_luftdaten_archive.php', 86400, '2017-04-07 02:46:01', '2017-04-07 02:46:08', 1, 0, 'Ok | '),
(4, 'Calculating hourly means for the sensors', 'crawler_db_hourly_mean.php', 1200, '2017-04-07 15:04:01', '2017-04-07 15:04:54', 1, 0, 'Ok | 2017-04-07 15:00:00: 515 sensors'),
(5, '', 'crawler_db_chronological_data_of_districts.php', 1200, '2017-04-07 14:46:01', '2017-04-07 14:46:03', 1, 0, 'Ok | 2017-04-07 14:00:00 crawled'),
(6, 'Database dump of district data to tsv', 'dump_db_districts_past.php', 1200, '2017-04-07 14:47:01', '2017-04-07 14:47:05', 1, 0, 'Ok | nothing to dump'),
(7, '', 'dump_db_districts_recent.php', 1200, '2017-04-07 14:52:02', '2017-04-07 14:52:02', 1, 0, 'Ok | 2017-04-07 14:00:00 dumped Array\n(\n    [P1h] => 0\n    [P2h] => 0\n    [P1d] => 0\n    [0] => P2d\n)\n'),
(9, 'Database dump of LUBW data to tsv', 'dump_db_lubw.php', 1800, '2017-04-07 14:43:01', '2017-04-07 14:43:01', 1, 0, 'Ok | dumped until 2017-04-07 16:00:00'),
(10, '', 'crawler_db_city_mean.php', 1800, '2017-04-07 14:35:02', '2017-04-07 14:35:19', 1, 0, 'Ok | start time of city_id = 1 = 2017-04-07 14:00:01 2017-04-07 14:00:01 > 2017-04-07 13:33:01 (DATE_ADD(MAX(timestamp), INTERVAL -1 HOUR) FROM `sensor_data`) nothing to do '),
(11, 'Database dump of city data to tsv', 'dump_db_cities_past.php', 1200, '2017-04-07 14:53:01', '2017-04-07 14:53:01', 1, 0, 'Ok | nothing to dump'),
(12, '', 'crawler_db_daily_mean.php', 600, '2017-03-01 17:03:01', '2017-03-24 08:05:37', 0, 1, '');

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
-- Tabellenstruktur für Tabelle `regions`
--

CREATE TABLE `regions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `region_type_id` int(11) NOT NULL,
  `geometry` geometry DEFAULT NULL,
  `parent_region_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `regions_mean`
--

CREATE TABLE `regions_mean` (
  `id` int(11) NOT NULL,
  `region_id` int(11) NOT NULL,
  `timestamp` timestamp NULL DEFAULT NULL,
  `P1h` decimal(10,1) NOT NULL,
  `P2h` decimal(10,1) NOT NULL,
  `P1d` decimal(10,1) NOT NULL,
  `P2d` decimal(10,1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `region_types`
--

CREATE TABLE `region_types` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `sensors`
--

CREATE TABLE `sensors` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type_id` int(11) DEFAULT NULL,
  `first_seen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
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
-- Tabellenstruktur für Tabelle `x_coordinates_regions`
--

CREATE TABLE `x_coordinates_regions` (
  `lon` decimal(6,3) NOT NULL,
  `lat` decimal(6,3) NOT NULL,
  `region_id` int(11) NOT NULL
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
-- Indizes für die Tabelle `regions`
--
ALTER TABLE `regions`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `regions_mean`
--
ALTER TABLE `regions_mean`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `district_id` (`region_id`,`timestamp`);

--
-- Indizes für die Tabelle `region_types`
--
ALTER TABLE `region_types`
  ADD UNIQUE KEY `id` (`id`);

--
-- Indizes für die Tabelle `sensors`
--
ALTER TABLE `sensors`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `sensors_hourly_mean`
--
ALTER TABLE `sensors_hourly_mean`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sensor_id_2` (`sensor_id`,`timestamp`),
  ADD KEY `sensor_id` (`sensor_id`,`timestamp`);

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
-- Indizes für die Tabelle `x_coordinates_regions`
--
ALTER TABLE `x_coordinates_regions`
  ADD UNIQUE KEY `lon` (`lon`,`lat`,`region_id`),
  ADD UNIQUE KEY `lon_2` (`lon`,`lat`),
  ADD KEY `district_id` (`region_id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `alerts`
--
ALTER TABLE `alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT für Tabelle `cities`
--
ALTER TABLE `cities`
  MODIFY `city_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT für Tabelle `cities_mean`
--
ALTER TABLE `cities_mean`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5278;
--
-- AUTO_INCREMENT für Tabelle `cron_jobs`
--
ALTER TABLE `cron_jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT für Tabelle `districts`
--
ALTER TABLE `districts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
--
-- AUTO_INCREMENT für Tabelle `regions`
--
ALTER TABLE `regions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;
--
-- AUTO_INCREMENT für Tabelle `regions_mean`
--
ALTER TABLE `regions_mean`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=242231;
--
-- AUTO_INCREMENT für Tabelle `region_types`
--
ALTER TABLE `region_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT für Tabelle `sensors`
--
ALTER TABLE `sensors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1041;
--
-- AUTO_INCREMENT für Tabelle `sensors_hourly_mean`
--
ALTER TABLE `sensors_hourly_mean`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=812157;
--
-- AUTO_INCREMENT für Tabelle `sensor_data`
--
ALTER TABLE `sensor_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42464296;
--
-- AUTO_INCREMENT für Tabelle `sensor_types`
--
ALTER TABLE `sensor_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
