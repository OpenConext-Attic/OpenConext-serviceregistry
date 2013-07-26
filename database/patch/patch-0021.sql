-- Reverts surfnet patch 0016, removes index which causes trouble when introducting foreign keys using doctrine migrations
ALTER TABLE janus__blockedEntity DROP index `eid_revision`;
ALTER TABLE janus__allowedEntity DROP index `eid_revision`;