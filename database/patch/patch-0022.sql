-- Reverts surfnet patch 0005, 0021 to start with a database comparable to wat janus expects before changing to doctrine migrations
ALTER TABLE janus__entity
  DROP PRIMARY KEY,
  ADD UNIQUE KEY `eid` (`eid`,`revisionid`),
  ADD UNIQUE KEY `janus__entity__eid_revisionid` (`eid`,`revisionid`);