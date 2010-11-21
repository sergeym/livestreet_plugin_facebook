CREATE TABLE IF NOT EXISTS `prefix_plugin_facebook_topic_list` (
  `topic_id` int(11) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `publish_id` char(32) NOT NULL, 
  PRIMARY KEY (`topic_id`),
  CONSTRAINT `FK_plugin_facebook_topic` FOREIGN KEY (`topic_id`) REFERENCES `prefix_topic` (`topic_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;