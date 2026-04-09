-- ═══════════════════════════════════════════════════════════════════
-- Ethiomark Bingo — MySQL Database Schema
-- ───────────────────────────────────────────────────────────────────
-- Import via phpMyAdmin:
--   1. Open phpMyAdmin → click "Import" tab
--   2. Choose this file → click "Go"
--   Done. All tables are created and ready.
--
-- The database is created automatically.
-- No manual setup needed after import.
-- ═══════════════════════════════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS `ethiomark_bingo`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `ethiomark_bingo`;

-- ── Game State ────────────────────────────────────────────────────
-- Stores the current round, active cards, pattern, daily reset date.
-- Always a single row (id = 1).

CREATE TABLE IF NOT EXISTS `em_game_state` (
  `id`         INT          NOT NULL DEFAULT 1,
  `state_json` LONGTEXT,
  `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
               ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── App Settings ──────────────────────────────────────────────────
-- Theme, sound preference, speed, and other UI settings.
-- Always a single row (id = 1).

CREATE TABLE IF NOT EXISTS `em_app_settings` (
  `id`            INT       NOT NULL DEFAULT 1,
  `settings_json` LONGTEXT,
  `updated_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                  ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Game History ──────────────────────────────────────────────────
-- One row per completed bingo game/round.

CREATE TABLE IF NOT EXISTS `em_game_history` (
  `id`           INT           NOT NULL AUTO_INCREMENT,
  `cashier_id`   VARCHAR(64)   DEFAULT '',
  `round`        INT           NOT NULL DEFAULT 1,
  `date`         VARCHAR(50)   DEFAULT '',
  `cards`        TEXT,
  `price`        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `pattern`      INT           NOT NULL DEFAULT 1,
  `sound`        INT           NOT NULL DEFAULT 1,
  `randomstring` VARCHAR(255)  DEFAULT '',
  `status`       VARCHAR(20)   DEFAULT 'played',
  `created_at`   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Cashiers ──────────────────────────────────────────────────────
-- Login accounts. password_hash is MD5 of the plain password.
-- Seed data: default cashier @temp1 / password @temp1

CREATE TABLE IF NOT EXISTS `em_cashiers` (
  `id`            VARCHAR(64)  NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `settings_json` LONGTEXT,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default cashier (@temp1 / @temp1)
-- MD5('@temp1') = a01610228fe998f515a72dd730294d87
INSERT IGNORE INTO `em_cashiers` (`id`, `password_hash`) VALUES
  ('@temp1', 'a01610228fe998f515a72dd730294d87');

-- ── License ───────────────────────────────────────────────────────
-- Stores machine ID, activated packages, and balance as JSON blobs.
-- key_name 'license_data' → JSON: { machine_id, packages[], total_deposited, total_revenue }

CREATE TABLE IF NOT EXISTS `em_license` (
  `key_name`   VARCHAR(100) NOT NULL,
  `value`      LONGTEXT,
  `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
               ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
