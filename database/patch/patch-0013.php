<?php
// Convert allowed / blocked entities from remoteentityid to remoteeid (see BACKLOG-505)

/**
 * DbPatch makes the following variables available to PHP patches:
 *
 * @var $this       DbPatch_Command_Patch_PHP
 * @var $writer     DbPatch_Core_Writer
 * @var $db         Zend_Db_Adapter_Abstract
 * @var $phpFile    string
 */

$entityTable        = 'janus__entity';

// First: allowed entities

echo "Converting whitelists" . PHP_EOL;

$blockedEntityTable = 'janus__allowedEntity';

$statement = $db->query("
SELECT be.*, entities.eid AS newremoteeid
FROM (
    SELECT *
    FROM $blockedEntityTable fbe
    WHERE revisionid=(
        SELECT MAX(revisionid)
        FROM $blockedEntityTable
        WHERE fbe.eid = eid
)) be
JOIN (
    SELECT *
    FROM $entityTable e
    WHERE revisionid = (
        SELECT MAX(revisionid)
        FROM $entityTable
        WHERE eid = e.eid
    )) entities ON be.remoteentityid = entities.entityid
");
$rows = $statement->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $row) {
    echo "ALLOW ({$row['eid']}): {$row['remoteentityid']} => {$row['newremoteeid']} ";

    $query = "
        UPDATE $blockedEntityTable
        SET remoteeid = ?
        WHERE eid = ?
          AND revisionid = ?
          AND remoteentityid = ?";
    $params = array(
        $row['newremoteeid'],
        $row['eid'],
        $row['revisionid'],
        $row['remoteentityid']
    );

    $update = $db->prepare($query);
    $update->execute($params);

    if ($update) {
        if ($update->rowCount() > 0) {
            echo "[SUCCESS]";
        }
        else {
            echo "[NO CHANGE REQUIRED]";
        }
    }
    else {
        echo "[FAILURE]";
    }
    echo PHP_EOL;
}

// Next blocked entities

echo "Converting blacklists" . PHP_EOL;

$blockedEntityTable = 'janus__blockedEntity';

$statement = $db->query("
SELECT be.*, entities.eid AS newremoteeid
FROM (
    SELECT *
    FROM $blockedEntityTable fbe
    WHERE revisionid=(
        SELECT MAX(revisionid)
        FROM $blockedEntityTable
        WHERE fbe.eid = eid
)) be
JOIN (
    SELECT *
    FROM $entityTable e
    WHERE revisionid = (
        SELECT MAX(revisionid)
        FROM $entityTable
        WHERE eid = e.eid
    )) entities ON be.remoteentityid = entities.entityid
");
$rows = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    echo "BLOCK ({$row['eid']}): {$row['remoteentityid']} => {$row['newremoteeid']} ";

    $query = "
        UPDATE $blockedEntityTable
        SET remoteeid = ?
        WHERE eid = ?
          AND revisionid = ?
          AND remoteentityid = ?";
    $params = array(
        $row['newremoteeid'],
        $row['eid'],
        $row['revisionid'],
        $row['remoteentityid']
    );

    $update = $db->prepare($query);
    $update->execute($params);

    if ($update && $update->rowCount() > 0) {
        if ($update->rowCount() > 0) {
            echo "[SUCCESS]";
        }
        else {
            echo "[NO CHANGE REQUIRED]";
        }
    }
    else {
        echo "[FAILURE]";
    }
    echo PHP_EOL;
}

echo "All done, enjoy!" . PHP_EOL;