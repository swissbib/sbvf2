-- Additional/modified db structures
-- to VuFind2 database for Swissbib specific features
-- ------------------------------------------------------

DROP TABLE IF EXISTS `user_localdata`;

CREATE TABLE `user_localdata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `language` text,
  `max_hits` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `user` ADD `favorite_institutions` TEXT NOT NULL;