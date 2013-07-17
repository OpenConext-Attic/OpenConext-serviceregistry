-- Remove obsolete indexes
ALTER TABLE janus__entity
  DROP INDEX eid,
  DROP INDEX janus__entity__eid_revisionid;