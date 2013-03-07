-- Additional/modified db structures
-- to VuFind2 database for Swissbib specific features
-- ------------------------------------------------------

ALTER TABLE user ADD `sb_nickname` text NOT NULL;
