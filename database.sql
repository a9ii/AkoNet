-- =============================================
-- AkoNet Web Monitor - Database Schema
-- Version: 1.0
-- =============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `akonet_monitor` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `akonet_monitor`;

-- =============================================
-- Table: providers
-- =============================================
CREATE TABLE `providers` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `host` VARCHAR(255) NOT NULL COMMENT 'IP address or hostname to ping',
    `status` ENUM('up','down','unknown') NOT NULL DEFAULT 'unknown',
    `ping` DECIMAL(8,2) DEFAULT NULL COMMENT 'Latest ping in ms',
    `packet_loss` DECIMAL(5,2) DEFAULT NULL COMMENT 'Latest packet loss %',
    `logo` VARCHAR(255) DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: monitoring_logs
-- =============================================
CREATE TABLE `monitoring_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `provider_id` INT UNSIGNED NOT NULL,
    `status` ENUM('up','down') NOT NULL,
    `ping` DECIMAL(8,2) DEFAULT NULL,
    `packet_loss` DECIMAL(5,2) DEFAULT NULL,
    `checked_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_provider_checked` (`provider_id`, `checked_at`),
    INDEX `idx_checked_at` (`checked_at`),
    CONSTRAINT `fk_monitoring_provider` FOREIGN KEY (`provider_id`) 
        REFERENCES `providers`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: downtime_logs
-- =============================================
CREATE TABLE `downtime_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `provider_id` INT UNSIGNED NOT NULL,
    `started_at` DATETIME NOT NULL,
    `ended_at` DATETIME DEFAULT NULL,
    `duration_minutes` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_provider_started` (`provider_id`, `started_at`),
    INDEX `idx_started_at` (`started_at`),
    CONSTRAINT `fk_downtime_provider` FOREIGN KEY (`provider_id`) 
        REFERENCES `providers`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: admins
-- =============================================
CREATE TABLE `admins` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Seed Data: Admin (password: admin123)
-- =============================================
INSERT INTO `admins` (`username`, `password`) VALUES
('admin', '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQkLgRJkFus5.mRH0RkvAm.WNtW0X2');

-- =============================================
-- Seed Data: Providers
-- =============================================
INSERT INTO `providers` (`name`, `host`, `status`, `ping`, `packet_loss`, `logo`) VALUES
('Cloudflare DNS',       '1.1.1.1',           'up',   12.45, 0.00, NULL),
('Google DNS',           '8.8.8.8',           'up',   18.32, 0.00, NULL),
('Amazon AWS',           '52.94.236.248',     'up',   45.78, 0.50, NULL),
('Microsoft Azure',      '13.107.42.14',      'up',   38.12, 0.00, NULL),
('OpenDNS',              '208.67.222.222',    'down',  0.00, 100.00, NULL),
('Quad9 DNS',            '9.9.9.9',           'up',   22.67, 1.20, NULL);

-- =============================================
-- Seed Data: Monitoring Logs (last 24 hours)
-- =============================================
INSERT INTO `monitoring_logs` (`provider_id`, `status`, `ping`, `packet_loss`, `checked_at`) VALUES
-- Cloudflare
(1, 'up', 11.20, 0.00, DATE_SUB(NOW(), INTERVAL 24 HOUR)),
(1, 'up', 12.80, 0.00, DATE_SUB(NOW(), INTERVAL 23 HOUR)),
(1, 'up', 13.10, 0.00, DATE_SUB(NOW(), INTERVAL 22 HOUR)),
(1, 'up', 10.50, 0.00, DATE_SUB(NOW(), INTERVAL 21 HOUR)),
(1, 'up', 14.20, 0.00, DATE_SUB(NOW(), INTERVAL 20 HOUR)),
(1, 'up', 11.90, 0.50, DATE_SUB(NOW(), INTERVAL 19 HOUR)),
(1, 'up', 12.40, 0.00, DATE_SUB(NOW(), INTERVAL 18 HOUR)),
(1, 'up', 15.30, 0.00, DATE_SUB(NOW(), INTERVAL 17 HOUR)),
(1, 'up', 11.70, 0.00, DATE_SUB(NOW(), INTERVAL 16 HOUR)),
(1, 'up', 13.50, 0.00, DATE_SUB(NOW(), INTERVAL 15 HOUR)),
(1, 'up', 12.10, 0.00, DATE_SUB(NOW(), INTERVAL 14 HOUR)),
(1, 'up', 10.80, 0.00, DATE_SUB(NOW(), INTERVAL 13 HOUR)),
(1, 'down', 0.00, 100.00, DATE_SUB(NOW(), INTERVAL 12 HOUR)),
(1, 'up', 14.60, 2.00, DATE_SUB(NOW(), INTERVAL 11 HOUR)),
(1, 'up', 12.30, 0.00, DATE_SUB(NOW(), INTERVAL 10 HOUR)),
(1, 'up', 11.40, 0.00, DATE_SUB(NOW(), INTERVAL 9 HOUR)),
(1, 'up', 13.20, 0.00, DATE_SUB(NOW(), INTERVAL 8 HOUR)),
(1, 'up', 12.90, 0.00, DATE_SUB(NOW(), INTERVAL 7 HOUR)),
(1, 'up', 11.60, 0.00, DATE_SUB(NOW(), INTERVAL 6 HOUR)),
(1, 'up', 14.10, 0.50, DATE_SUB(NOW(), INTERVAL 5 HOUR)),
(1, 'up', 12.50, 0.00, DATE_SUB(NOW(), INTERVAL 4 HOUR)),
(1, 'up', 13.80, 0.00, DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(1, 'up', 11.30, 0.00, DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(1, 'up', 12.45, 0.00, DATE_SUB(NOW(), INTERVAL 1 HOUR)),

-- Google DNS
(2, 'up', 17.40, 0.00, DATE_SUB(NOW(), INTERVAL 24 HOUR)),
(2, 'up', 19.20, 0.00, DATE_SUB(NOW(), INTERVAL 23 HOUR)),
(2, 'up', 18.50, 0.00, DATE_SUB(NOW(), INTERVAL 22 HOUR)),
(2, 'up', 16.80, 0.50, DATE_SUB(NOW(), INTERVAL 21 HOUR)),
(2, 'up', 20.10, 0.00, DATE_SUB(NOW(), INTERVAL 20 HOUR)),
(2, 'up', 18.70, 0.00, DATE_SUB(NOW(), INTERVAL 19 HOUR)),
(2, 'up', 17.90, 0.00, DATE_SUB(NOW(), INTERVAL 18 HOUR)),
(2, 'up', 21.30, 0.00, DATE_SUB(NOW(), INTERVAL 17 HOUR)),
(2, 'up', 18.10, 0.00, DATE_SUB(NOW(), INTERVAL 16 HOUR)),
(2, 'up', 19.50, 0.00, DATE_SUB(NOW(), INTERVAL 15 HOUR)),
(2, 'up', 17.60, 0.00, DATE_SUB(NOW(), INTERVAL 14 HOUR)),
(2, 'down', 0.00, 100.00, DATE_SUB(NOW(), INTERVAL 13 HOUR)),
(2, 'down', 0.00, 100.00, DATE_SUB(NOW(), INTERVAL 12 HOUR)),
(2, 'up', 22.40, 5.00, DATE_SUB(NOW(), INTERVAL 11 HOUR)),
(2, 'up', 18.90, 0.00, DATE_SUB(NOW(), INTERVAL 10 HOUR)),
(2, 'up', 17.30, 0.00, DATE_SUB(NOW(), INTERVAL 9 HOUR)),
(2, 'up', 19.80, 0.00, DATE_SUB(NOW(), INTERVAL 8 HOUR)),
(2, 'up', 18.20, 0.00, DATE_SUB(NOW(), INTERVAL 7 HOUR)),
(2, 'up', 17.70, 0.00, DATE_SUB(NOW(), INTERVAL 6 HOUR)),
(2, 'up', 20.60, 1.00, DATE_SUB(NOW(), INTERVAL 5 HOUR)),
(2, 'up', 18.40, 0.00, DATE_SUB(NOW(), INTERVAL 4 HOUR)),
(2, 'up', 19.10, 0.00, DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(2, 'up', 17.50, 0.00, DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(2, 'up', 18.32, 0.00, DATE_SUB(NOW(), INTERVAL 1 HOUR)),

-- Amazon AWS
(3, 'up', 44.20, 0.00, DATE_SUB(NOW(), INTERVAL 24 HOUR)),
(3, 'up', 46.80, 0.00, DATE_SUB(NOW(), INTERVAL 22 HOUR)),
(3, 'up', 43.10, 0.50, DATE_SUB(NOW(), INTERVAL 20 HOUR)),
(3, 'up', 47.50, 0.00, DATE_SUB(NOW(), INTERVAL 18 HOUR)),
(3, 'up', 45.30, 0.00, DATE_SUB(NOW(), INTERVAL 16 HOUR)),
(3, 'up', 42.90, 1.00, DATE_SUB(NOW(), INTERVAL 14 HOUR)),
(3, 'up', 48.10, 0.00, DATE_SUB(NOW(), INTERVAL 12 HOUR)),
(3, 'up', 44.70, 0.00, DATE_SUB(NOW(), INTERVAL 10 HOUR)),
(3, 'up', 46.20, 0.50, DATE_SUB(NOW(), INTERVAL 8 HOUR)),
(3, 'up', 43.80, 0.00, DATE_SUB(NOW(), INTERVAL 6 HOUR)),
(3, 'up', 47.90, 0.00, DATE_SUB(NOW(), INTERVAL 4 HOUR)),
(3, 'up', 45.78, 0.50, DATE_SUB(NOW(), INTERVAL 2 HOUR)),

-- OpenDNS (has downtime)
(5, 'up', 35.20, 0.00, DATE_SUB(NOW(), INTERVAL 24 HOUR)),
(5, 'up', 36.80, 0.00, DATE_SUB(NOW(), INTERVAL 22 HOUR)),
(5, 'up', 34.50, 1.00, DATE_SUB(NOW(), INTERVAL 20 HOUR)),
(5, 'down', 0.00, 100.00, DATE_SUB(NOW(), INTERVAL 18 HOUR)),
(5, 'down', 0.00, 100.00, DATE_SUB(NOW(), INTERVAL 16 HOUR)),
(5, 'down', 0.00, 100.00, DATE_SUB(NOW(), INTERVAL 14 HOUR)),
(5, 'down', 0.00, 100.00, DATE_SUB(NOW(), INTERVAL 12 HOUR)),
(5, 'down', 0.00, 100.00, DATE_SUB(NOW(), INTERVAL 10 HOUR)),
(5, 'down', 0.00, 100.00, DATE_SUB(NOW(), INTERVAL 8 HOUR)),
(5, 'down', 0.00, 100.00, DATE_SUB(NOW(), INTERVAL 6 HOUR)),
(5, 'down', 0.00, 100.00, DATE_SUB(NOW(), INTERVAL 4 HOUR)),
(5, 'down', 0.00, 100.00, DATE_SUB(NOW(), INTERVAL 2 HOUR));

-- =============================================
-- Seed Data: Downtime Logs
-- =============================================
INSERT INTO `downtime_logs` (`provider_id`, `started_at`, `ended_at`, `duration_minutes`) VALUES
-- Cloudflare had 1 hour downtime
(1, DATE_SUB(NOW(), INTERVAL 12 HOUR), DATE_SUB(NOW(), INTERVAL 11 HOUR), 60),
-- Google DNS had 2 hours downtime
(2, DATE_SUB(NOW(), INTERVAL 13 HOUR), DATE_SUB(NOW(), INTERVAL 11 HOUR), 120),
-- OpenDNS ongoing downtime (no end time)
(5, DATE_SUB(NOW(), INTERVAL 18 HOUR), NULL, NULL),
-- Historical downtimes for daily chart
(1, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(DATE_SUB(NOW(), INTERVAL 2 DAY), INTERVAL -45 MINUTE), 45),
(2, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(DATE_SUB(NOW(), INTERVAL 3 DAY), INTERVAL -30 MINUTE), 30),
(5, DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(DATE_SUB(NOW(), INTERVAL 1 DAY), INTERVAL -180 MINUTE), 180),
(5, DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(DATE_SUB(NOW(), INTERVAL 4 DAY), INTERVAL -90 MINUTE), 90),
(3, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(DATE_SUB(NOW(), INTERVAL 5 DAY), INTERVAL -15 MINUTE), 15);

COMMIT;
