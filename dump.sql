CREATE TABLE IF NOT EXISTS `prefix_plugin_facebook_topic_list` (
  `topic_id` int(11) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `publish_id` char(32) NOT NULL, 
  PRIMARY KEY (`topic_id`),
  CONSTRAINT `FK_plugin_facebook_topic` FOREIGN KEY (`topic_id`) REFERENCES `prefix_topic` (`topic_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `prefix_plugin_facebook_topic_list`  ADD COLUMN `status` ENUM('published','blocked') NOT NULL AFTER `publish_id`;

CREATE TABLE IF NOT EXISTS `prefix_plugin_facebook_settings` (
	`id` INT(10) NULL DEFAULT NULL,
	`appId` CHAR(20) NULL DEFAULT NULL,
	`appKey` CHAR(48) NULL DEFAULT NULL,
	`appSecret` CHAR(48) NULL DEFAULT NULL,
	`pageId` CHAR(20) NULL DEFAULT NULL,
	`pageUrl` VARCHAR(1024) NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
