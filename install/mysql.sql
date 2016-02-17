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
  `type` int(11) NOT NULL COMMENT 'Category type. 1 - pages, 2 - listings, 3 ...',
  `sort` int(11) NOT NULL DEFAULT '0',
  `parent_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `category_children` (
  `category_id` int(10) unsigned NOT NULL,
  `child_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`category_id`,`child_id`),
  KEY `IDX_16ED35C112469DE2` (`category_id`),
  KEY `IDX_16ED35C1DD62C21B` (`child_id`),
  CONSTRAINT `FK_16ED35C112469DE2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `FK_16ED35C1DD62C21B` FOREIGN KEY (`child_id`) REFERENCES `categories` (`id`)
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


CREATE TABLE IF NOT EXISTS `listings_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `listing_id` int(10) unsigned DEFAULT NULL,
  `lang_id` int(10) unsigned DEFAULT NULL,
  `alias` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `text` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_834C219D4619D1A` (`listing_id`),
  KEY `IDX_834C219B213FA4` (`lang_id`),
  CONSTRAINT `FK_834C219B213FA4` FOREIGN KEY (`lang_id`) REFERENCES `lang` (`id`),
  CONSTRAINT `FK_834C219D4619D1A` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


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


CREATE TABLE IF NOT EXISTS `metadata` (
  `listing_id` int(10) unsigned NOT NULL,
  `lang_id` int(10) unsigned NOT NULL,
  `meta_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `meta_description` tinytext COLLATE utf8_unicode_ci,
  `meta_keywords` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`listing_id`,`lang_id`),
  KEY `IDX_4F143414D4619D1A` (`listing_id`),
  KEY `IDX_4F143414B213FA4` (`lang_id`),
  CONSTRAINT `FK_4F143414B213FA4` FOREIGN KEY (`lang_id`) REFERENCES `lang` (`id`),
  CONSTRAINT `FK_4F143414D4619D1A` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`)
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
