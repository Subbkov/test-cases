CREATE DATABASE  IF NOT EXISTS `php_test_version5`;
USE `php_test_version5`;

DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
    `id` int NOT NULL,
    `username` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `validts` int NOT NULL DEFAULT '0',
    `confirmed` tinyint NOT NULL DEFAULT '0',
    `checked` tinyint NOT NULL,
    `valid` tinyint NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `idx_user_validts_valid` (`valid`,`validts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `queue_user_subscription_expiration`;

CREATE TABLE `queue_user_subscription_expiration` (
    `id` int NOT NULL AUTO_INCREMENT,
    `user_id` int NOT NULL,
    `group_id` smallint NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `user_id_UNIQUE` (`user_id`),
    KEY `idx_queue_user_subscription_expiration_1` (`group_id`),
    CONSTRAINT `fk_queue_user_subscription_expiration_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
