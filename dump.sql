CREATE TABLE IF NOT EXISTS `prefix_plugin_facebook_topic_list` (
  `topic_id` int(11) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `publish_id` CHAR(32) NULL DEFAULT NULL,
  `status` ENUM('published','blocked') NOT NULL,
  PRIMARY KEY (`topic_id`),
  CONSTRAINT `FK_plugin_facebook_topic` FOREIGN KEY (`topic_id`) REFERENCES `prefix_topic` (`topic_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `prefix_plugin_facebook_settings` (
	`id` INT(10) NULL DEFAULT NULL,
	`app_id` CHAR(20) NULL DEFAULT NULL,
	`app_secret` CHAR(48) NULL DEFAULT NULL,
	`access_token` TEXT NULL,
	`page_id` CHAR(20) NULL DEFAULT NULL,
	`page_url` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
