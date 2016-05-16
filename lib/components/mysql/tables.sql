CREATE DATABASE IF NOT EXISTS `mainoi_service` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `mainoi_service`;

CREATE TABLE IF NOT EXISTS `users` (
	`user_id` int(11) NOT NULL AUTO_INCREMENT,
	`user_unique_tag` varchar(255) NOT NULL COLLATE utf8_unicode_ci,
	`user_first_name` varchar(255) NOT NULL COLLATE utf8_unicode_ci,
	`user_last_name` varchar(255) NOT NULL COLLATE utf8_unicode_ci,
	`user_mail` varchar(255) NOT NULL COLLATE utf8_unicode_ci,
	`user_phone` varchar(255) NOT NULL COLLATE utf8_unicode_ci,
	`user_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
	`user_password_hash` varchar(255) NOT NULL COLLATE utf8_unicode_ci,
	`user_role` int(11) COMMENT 'Participant = 0, Volunteer = 1, Admin = 2' DEFAULT 0,
	`user_hero_level` int(11) COMMENT 'None = 0, Legend = 1, Hero = 2, Guardian = 3' DEFAULT 0, 
	`user_created_timestamp` DATETIME DEFAULT '0000-00-00 00:00:00',
	`user_updated_timestamp` DATETIME DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`user_id`),
	UNIQUE KEY `user_unique_tag` (`user_unique_tag`),
	UNIQUE KEY `user_phone` (`user_phone`),
	UNIQUE KEY `user_mail` (`user_mail`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
