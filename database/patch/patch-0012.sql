ALTER TABLE `janus__allowedEntity` ADD `remoteeid` INT( 11 ) NOT NULL AFTER `remoteentityid` ,
    ADD INDEX ( `remoteeid` );

ALTER TABLE `janus__blockedEntity` ADD `remoteeid` INT( 11 ) NOT NULL AFTER `remoteentityid` ,
    ADD INDEX ( `remoteeid` );