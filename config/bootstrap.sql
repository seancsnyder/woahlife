CREATE TABLE `entries` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `entry_text` text,
  `entry_date` date DEFAULT NULL,
  `message_id` varchar(256) DEFAULT NULL,
  `message_url` varchar(256) DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_date` (`user_id`,`entry_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) DEFAULT '',
  `email` varchar(256) DEFAULT NULL,
  `active` int(10) DEFAULT '1',
  `message_id` varchar(256) DEFAULT NULL,
  `message_url` varchar(256) DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;