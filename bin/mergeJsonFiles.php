<?php
/**
 * Merges two json files to one, this can be used to combine a default and custom Janus config file
 *
 */

if (count($argv) != 3) {
    die ('Please specify path to file and custom file');
}

$filePath = $argv[1];
$customFilePath = $argv[2];

// Read data
$orgJson = file_get_contents($filePath);
$orgData = json_decode($orgJson, true);
$customJson = file_get_contents($customFilePath);
$customData = json_decode($customJson, true);

// Merge and store
$mergeData = array_merge($orgData, $customData);
file_put_contents($filePath, json_encode($mergeData));
