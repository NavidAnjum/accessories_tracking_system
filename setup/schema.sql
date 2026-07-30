-- ED Module Database Schema
-- Run via /ed_module/setup/install.php

CREATE DATABASE IF NOT EXISTS `ed_module`
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `ed_module`;

-- ── Customers ─────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `customers` (
    `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `company_name`        VARCHAR(255) NOT NULL,
    `address_head_office` TEXT,
    `factory_address`     TEXT,
    `chairman_name`       VARCHAR(255),
    `chairman_mobile`     VARCHAR(50),
    `customer_type`       VARCHAR(20) DEFAULT 'Regular',
    `date_form`           DATE,
    `politics_yes`        TINYINT(1) DEFAULT 0,
    `politics_party`      VARCHAR(100),
    `extra_data`          JSON,
    `created_at`          DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_company_name` (`company_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Buyers ────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `buyers` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `buyer_code`    VARCHAR(50)  NOT NULL,
    `buyer_name`    VARCHAR(255) NOT NULL,
    `customer_name` VARCHAR(255),
    `address`       TEXT,
    `created_at`    DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_buyer_code` (`buyer_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Orders ────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `orders` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id`        VARCHAR(20)  NOT NULL,
    `customer_name`   VARCHAR(255),
    `salesperson`     VARCHAR(255),
    `intake_date`     DATE,
    `po_number`       VARCHAR(100),
    `trims_ipo`       VARCHAR(100),
    `without_arl`     TINYINT(1) DEFAULT 0,
    `to_buyer`        VARCHAR(255),
    `sub_description` TEXT,
    `paper_quality`   VARCHAR(255),
    `buyer_end_buyer` VARCHAR(255),
    `design`          VARCHAR(255),
    `order_no`        VARCHAR(100),
    `order_type`      VARCHAR(100),
    `delivery_date`   DATE,
    `notes`           TEXT,
    `current_step`    VARCHAR(50) DEFAULT 'marketing-intake',
    `created_at`      DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Order Items ───────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `order_items` (
    `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id`          VARCHAR(20)  NOT NULL,
    `sl_no`             INT          NOT NULL DEFAULT 1,
    `product_line`      VARCHAR(100),
    `item_name`         VARCHAR(255),
    `art_size`          VARCHAR(100),
    `grade`             VARCHAR(100),
    `paper_combination` VARCHAR(255),
    `qty`               DECIMAL(12,3) DEFAULT 0,
    `unit`              VARCHAR(20),
    `unit_price`        DECIMAL(12,4) DEFAULT 0,
    `amount`            DECIMAL(14,2) DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_order_items_order_id` (`order_id`),
    CONSTRAINT `fk_order_items_order_id`
        FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Page Data (per-order per-page JSON blob) ──────────────────────────────────
CREATE TABLE IF NOT EXISTS `page_data` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id`   VARCHAR(20)  NOT NULL,
    `page_name`  VARCHAR(50)  NOT NULL,
    `data`       JSON,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_page_data` (`order_id`, `page_name`),
    CONSTRAINT `fk_page_data_order_id`
        FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
