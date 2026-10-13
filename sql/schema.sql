-- Gestione Finanziamenti - schema del database (solo struttura, nessun dato)
-- Compatibile con MySQL 5.7+ / MariaDB 10.3+

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `rate`;
DROP TABLE IF EXISTS `clienti`;
DROP TABLE IF EXISTS `lead`;
DROP TABLE IF EXISTS `uscite`;
DROP TABLE IF EXISTS `utenti`;

CREATE TABLE `clienti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) DEFAULT NULL,
  `citta` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `indirizzo` text DEFAULT NULL,
  `codice_fiscale` varchar(30) DEFAULT NULL,
  `data_registrazione` date DEFAULT NULL,
  `data_nascita` date DEFAULT NULL,
  `acquisto` varchar(255) DEFAULT NULL,
  `importo` decimal(10,2) DEFAULT NULL,
  `pagamento` enum('immediato','rateale_compass','finanziamento_interno') DEFAULT NULL,
  `note` text DEFAULT NULL,
  `acconto` decimal(10,2) DEFAULT 0.00,
  `interessi` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `lead` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) DEFAULT NULL,
  `citta` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `data_ricontatto` date DEFAULT NULL,
  `esito` enum('non interessato','ricontattare','acquistato') DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `rate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) DEFAULT NULL,
  `numero_rata` int(11) DEFAULT NULL,
  `importo` decimal(10,2) DEFAULT NULL,
  `data_scadenza` date DEFAULT NULL,
  `pagata` tinyint(1) DEFAULT 0,
  `data_pagamento` date DEFAULT NULL,
  `note` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cliente_id` (`cliente_id`),
  CONSTRAINT `rate_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clienti` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `uscite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) DEFAULT NULL,
  `importo` decimal(10,2) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `tipo` enum('una_tantum','ricorrente') DEFAULT NULL,
  `data_uscita` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `utenti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `ruolo` enum('admin','responsabile','dipendente') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
