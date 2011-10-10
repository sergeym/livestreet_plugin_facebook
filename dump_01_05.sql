ALTER TABLE `prefix_plugin_facebook_topic_list`  CHANGE COLUMN `publish_id` `publish_id` CHAR(32) NULL DEFAULT NULL AFTER `date`,  ADD COLUMN `status` ENUM('published','blocked') NOT NULL AFTER `publish_id`;

CREATE TABLE IF NOT EXISTS `prefix_plugin_facebook_settings` (
	`id` INT(10) NULL DEFAULT NULL,
	`app_id` CHAR(20) NULL DEFAULT NULL,
	`app_secret` CHAR(48) NULL DEFAULT NULL,
	`access_token` TEXT NULL,
	`page_id` CHAR(20) NULL DEFAULT NULL,
	`page_url` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;