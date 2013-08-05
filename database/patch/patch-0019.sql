-- Remove obsolete remoteentityid columns
ALTER TABLE janus__allowedEntity
    DROP COLUMN remoteentityid;

ALTER TABLE janus__blockedEntity
    DROP COLUMN remoteentityid;