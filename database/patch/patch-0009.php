<?php
// Update all NameIDFormat keys to urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified

/**
 * DbPatch makes the following variables available to PHP patches:
 *
 * @var $this       DbPatch_Command_Patch_PHP
 * @var $writer     DbPatch_Core_Writer
 * @var $db         Zend_Db_Adapter_Abstract
 * @var $phpFile    string
 */

$nameIdValue = 'urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified';
$nameIdKey = 'NameIDFormat';
$ip = '127.0.0.1';
date_default_timezone_set('Europe/Amsterdam');
$now = date('c');
//first we do a set-update for NameIDFormat entries
$statement = $db->query("
  UPDATE janus__metadata janus__metadata SET janus__metadata.value = ?
  WHERE janus__metadata.key = ?",
                        array($nameIdValue, $nameIdKey)
);
$writer->info('Updated #' . $statement->rowCount() . ' records in the janus_metadate table');
//then get all sp's who did not have a NameIDFormat and the highest revision number
$sps = $db->fetchAll(
    "SELECT janus__metadata.eid, MAX(janus__metadata.revisionid) as revisionid, janus__entity.entityid
   from janus__metadata janus__metadata, janus__entity janus__entity
   where janus__entity.eid = janus__metadata.eid AND janus__entity.type = 'saml20-sp' AND janus__metadata.eid not IN
   (SELECT janus__metadata.eid FROM janus__metadata janus__metadata WHERE janus__metadata.key = 'NameIDFormat')
   GROUP BY janus__metadata.eid
  ");
foreach ($sps as $sp) {
    $eid = $sp['eid'];
    $revision = $sp['revisionid'];
    $statement = $db->query("
      INSERT INTO `janus__metadata` (`eid`, `revisionid`, `key`, `value`, `created`, `ip`)
      VALUES (?, ?, ?, ?, ?, ?)",
                            array($eid, $revision, $nameIdKey, $nameIdValue, $now, $ip)
    );
    $writer->info('Inserting NameIDFormat for ' . $sp['entityid'] . ' for revision: ' . $revision);
}