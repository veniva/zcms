-- --------------------------------------------------------
-- Host:                         localhost
-- Server version:               5.6.20 - MySQL Community Server (GPL)
-- Server OS:                    Win32
-- HeidiSQL Version:             9.3.0.5009
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sort` int(11) NOT NULL DEFAULT '0',
  `parent_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `category_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned DEFAULT NULL,
  `lang_id` int(10) unsigned DEFAULT NULL,
  `alias` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_391D70D712469DE2` (`category_id`),
  KEY `IDX_391D70D7B213FA4` (`lang_id`),
  CONSTRAINT `FK_391D70D712469DE2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `FK_391D70D7B213FA4` FOREIGN KEY (`lang_id`) REFERENCES `lang` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `category_parents` (
  `category_id` int(10) unsigned NOT NULL,
  `parent_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`category_id`,`parent_id`),
  KEY `IDX_3A885D1412469DE2` (`category_id`),
  KEY `IDX_3A885D14727ACA70` (`parent_id`),
  CONSTRAINT `FK_3A885D1412469DE2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `FK_3A885D14727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `lang` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `iso_code` char(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1' COMMENT '2- default; 1-active;',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `listings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sort` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `listings_content` (
  `listing_id` INT(10) UNSIGNED NOT NULL,
  `lang_id` INT(10) UNSIGNED NOT NULL,
  `alias` VARCHAR(255) NOT NULL,
  `link` VARCHAR(255) NOT NULL,
  `title` VARCHAR(255) NULL DEFAULT NULL,
  `text` TEXT NULL,
  `meta_title` VARCHAR(128) NULL DEFAULT NULL,
  `meta_description` TINYTEXT NULL,
  `meta_keywords` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`listing_id`, `lang_id`),
  INDEX `FK_listings_content_lang` (`lang_id`),
  INDEX `FK_listings_content_listings` (`listing_id`),
  CONSTRAINT `FK_listings_content_lang` FOREIGN KEY (`lang_id`) REFERENCES `lang` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FK_listings_content_listings` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
)
  COLLATE='utf8_general_ci'
  ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS `listing_category` (
  `listing_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`listing_id`,`category_id`),
  KEY `IDX_E0307BBBD4619D1A` (`listing_id`),
  KEY `IDX_E0307BBB12469DE2` (`category_id`),
  CONSTRAINT `FK_E0307BBB12469DE2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_E0307BBBD4619D1A` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `listing_images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `listing_id` int(10) unsigned DEFAULT NULL,
  `image_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_4E79FB9D4619D1A` (`listing_id`),
  CONSTRAINT `FK_4E79FB9D4619D1A` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_resets` (
  `email` VARCHAR(255) NOT NULL COLLATE 'utf8_unicode_ci',
  `token` VARCHAR(10) NOT NULL COLLATE 'utf8_unicode_ci',
  `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`email`, `token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `upass` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `role` int(11) NOT NULL DEFAULT '4',
  `reg_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
