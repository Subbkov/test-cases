CREATE DATABASE  IF NOT EXISTS `php_test_version1`;
USE `php_test_version1`;

DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
  `id` int NOT NULL,
  `username` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `validts` int NOT NULL DEFAULT '0',
  `confirmed` tinyint NOT NULL DEFAULT '0',
  `checked` tinyint NOT NULL,
  `valid` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_user_validts_valid` (`valid`,`validts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

