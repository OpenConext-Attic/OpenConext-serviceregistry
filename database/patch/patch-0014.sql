-- BACKLOG-675: Add manipulation field to entity
ALTER TABLE `janus__entity` ADD COLUMN `manipulation` MEDIUMTEXT NULL DEFAULT NULL AFTER `arp` ;