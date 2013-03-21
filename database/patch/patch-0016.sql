-- Add index for allowed and blocked entities to improve performance (created by Geert vd Ploeg)
ALTER TABLE janus__blockedEntity ADD index `eid_revision` (eid, revisionid);
ALTER TABLE janus__allowedEntity ADD index `eid_revision` (eid, revisionid);