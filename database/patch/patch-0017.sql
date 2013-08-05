-- Change ip columns so that ipv6 addresses will fit
ALTER TABLE janus__allowedEntity
	CHANGE ip ip char(39) NOT NULL;
ALTER TABLE janus__arp
	CHANGE ip ip char(39) NOT NULL;
ALTER TABLE janus__attribute
	CHANGE ip ip char(39) NOT NULL;
ALTER TABLE janus__blockedEntity
	CHANGE ip ip char(39) NOT NULL;
ALTER TABLE janus__disableConsent
	CHANGE ip ip char(39) NOT NULL;
ALTER TABLE janus__entity
	CHANGE ip ip char(39) DEFAULT NULL;
ALTER TABLE janus__hasEntity
	CHANGE ip ip char(39) DEFAULT NULL;
ALTER TABLE janus__message
	CHANGE ip ip char(39) DEFAULT NULL;
ALTER TABLE janus__metadata
	CHANGE ip ip char(39) NOT NULL;
ALTER TABLE janus__subscription
	CHANGE ip ip char(39) DEFAULT NULL;
ALTER TABLE janus__user
	CHANGE ip ip char(39) DEFAULT NULL;
ALTER TABLE janus__userData
	CHANGE ip ip char(39) NOT NULL;
