CREATE DATABASE  IF NOT EXISTS `php_test_version3`;
USE `php_test_version3`;

DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
    `id` int NOT NULL,
    `username` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `validts` int NOT NULL DEFAULT '0',
    `confirmed` tinyint NOT NULL DEFAULT '0',
    `checked` tinyint NOT NULL,
    `valid` tinyint NOT NULL DEFAULT '0',
    `last_sent` int DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_user_validts_valid` (`valid`,`validts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
