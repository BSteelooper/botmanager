-- phpMyAdmin SQL Dump
-- version 5.2.1deb1+deb12u1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 02, 2025 at 09:51 PM
-- Server version: 10.11.11-MariaDB-0+deb12u1
-- PHP Version: 8.1.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `c54nirn2bot`
--

-- --------------------------------------------------------

--
-- Table structure for table `berichten`
--

CREATE TABLE `berichten` (
  `id` int(11) NOT NULL,
  `chat_id` bigint(20) DEFAULT NULL,
  `from_name` varchar(100) DEFAULT NULL,
  `gebruikersnaam` varchar(100) DEFAULT NULL,
  `message_text` text DEFAULT NULL,
  `publiceerbaar` tinyint(1) DEFAULT 0,
  `publiceerbaar_gemarkeerd_door_chat_id` bigint(20) DEFAULT NULL,
  `ontvangen_op` datetime DEFAULT current_timestamp(),
  `beantwoord_door_chat_id` bigint(20) DEFAULT NULL,
  `beantwoord_op` datetime DEFAULT NULL,
  `antwoord` text DEFAULT NULL,
  `categorie_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categorieen`
--

CREATE TABLE `categorieen` (
  `id` int(11) NOT NULL,
  `naam` varchar(100) NOT NULL,
  `kleur` varchar(7) DEFAULT '#228be6',
  `actief` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categorieen`
--

INSERT INTO `categorieen` (`id`, `naam`, `kleur`, `actief`) VALUES
(1, 'Evenement', '#001eff', 1),
(2, 'NIA', '#b505f5', 1),
(3, 'SITREP', '#ff0000', 1);

-- --------------------------------------------------------

--
-- Table structure for table `login_tokens`
--

CREATE TABLE `login_tokens` (
  `id` int(11) NOT NULL,
  `gebruiker_chat_id` bigint(20) DEFAULT NULL,
  `token` varchar(100) DEFAULT NULL,
  `geldig_tot` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log_acties`
--

CREATE TABLE `log_acties` (
  `id` int(11) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `omschrijving` text DEFAULT NULL,
  `aanmaker_chat_id` bigint(20) DEFAULT NULL,
  `aangemaakt_op` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------

--
-- Table structure for table `notificatie_ontvangers`
--

CREATE TABLE `notificatie_ontvangers` (
  `chat_id` bigint(20) NOT NULL,
  `naam` varchar(100) DEFAULT NULL,
  `gebruikersnaam` varchar(100) DEFAULT NULL,
  `goedgekeurd` tinyint(1) DEFAULT 0,
  `rol` enum('gebruiker','admin','superadmin') DEFAULT 'gebruiker',
  `aangemeld_op` datetime DEFAULT current_timestamp(),
  `laatste_ip` varchar(45) DEFAULT NULL,
  `laatste_login` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------

--
-- Table structure for table `publicatie_log`
--

CREATE TABLE `publicatie_log` (
  `id` int(11) NOT NULL,
  `bericht_id` int(11) DEFAULT NULL,
  `kanaal_id` int(11) DEFAULT NULL,
  `verzonden_door_chat_id` bigint(20) DEFAULT NULL,
  `verzonden_door_naam` varchar(100) DEFAULT NULL,
  `verzonden_op` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `systeeminstellingen`
--

CREATE TABLE `systeeminstellingen` (
  `sleutel` varchar(50) NOT NULL,
  `waarde` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `systeeminstellingen`
--

INSERT INTO `systeeminstellingen` (`sleutel`, `waarde`) VALUES
('max_admins', '5'),
('max_sessie_duur', '3600'),
('standaard_categorie_kleur', '#000000'),
('token_verlooptijd', '3600');

-- --------------------------------------------------------

--
-- Table structure for table `telegram_bots`
--

CREATE TABLE `telegram_bots` (
  `id` int(11) NOT NULL,
  `naam` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `uniek_sleutel` varchar(50) DEFAULT NULL,
  `actief` tinyint(1) DEFAULT 1,
  `toegevoegd_op` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `telegram_kanalen`
--

CREATE TABLE `telegram_kanalen` (
  `id` int(11) NOT NULL,
  `naam` varchar(100) DEFAULT NULL,
  `chat_id` bigint(20) DEFAULT NULL,
  `actief` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Indexes for dumped tables
--

--
-- Indexes for table `berichten`
--
ALTER TABLE `berichten`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categorieen`
--
ALTER TABLE `categorieen`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_tokens`
--
ALTER TABLE `login_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `gebruiker_chat_id` (`gebruiker_chat_id`);

--
-- Indexes for table `log_acties`
--
ALTER TABLE `log_acties`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notificatie_ontvangers`
--
ALTER TABLE `notificatie_ontvangers`
  ADD PRIMARY KEY (`chat_id`);

--
-- Indexes for table `publicatie_log`
--
ALTER TABLE `publicatie_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bericht_id` (`bericht_id`),
  ADD KEY `kanaal_id` (`kanaal_id`);

--
-- Indexes for table `systeeminstellingen`
--
ALTER TABLE `systeeminstellingen`
  ADD PRIMARY KEY (`sleutel`);

--
-- Indexes for table `telegram_bots`
--
ALTER TABLE `telegram_bots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniek_sleutel` (`uniek_sleutel`);

--
-- Indexes for table `telegram_kanalen`
--
ALTER TABLE `telegram_kanalen`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `berichten`
--
ALTER TABLE `berichten`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `categorieen`
--
ALTER TABLE `categorieen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `login_tokens`
--
ALTER TABLE `login_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `log_acties`
--
ALTER TABLE `log_acties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `publicatie_log`
--
ALTER TABLE `publicatie_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `telegram_bots`
--
ALTER TABLE `telegram_bots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `telegram_kanalen`
--
ALTER TABLE `telegram_kanalen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `login_tokens`
--
ALTER TABLE `login_tokens`
  ADD CONSTRAINT `login_tokens_ibfk_1` FOREIGN KEY (`gebruiker_chat_id`) REFERENCES `notificatie_ontvangers` (`chat_id`) ON DELETE CASCADE;

--
-- Constraints for table `publicatie_log`
--
ALTER TABLE `publicatie_log`
  ADD CONSTRAINT `publicatie_log_ibfk_1` FOREIGN KEY (`bericht_id`) REFERENCES `berichten` (`id`),
  ADD CONSTRAINT `publicatie_log_ibfk_2` FOREIGN KEY (`kanaal_id`) REFERENCES `telegram_kanalen` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
