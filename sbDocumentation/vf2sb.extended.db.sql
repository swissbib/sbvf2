-- Additional/modified db structures
-- to VuFind2 database for Swissbib specific features
-- ------------------------------------------------------
ALTER TABLE `user` ADD `favorite_institutions` TEXT NOT NULL;
ALTER TABLE `user` ADD `language` VARCHAR( 2 ) NOT NULL DEFAULT '';
ALTER TABLE `user` ADD `max_hits` SMALLINT NOT NULL DEFAULT '0';
alter table user modify username VARCHAR(200);
