<?php
// This is only required when loading config files directly from Janus (classes aren't autoloaded)
require_once __DIR__ . "/../lib/ServiceRegistry/Janus/Config/GlobalPermissionTableParser.php";
require_once __DIR__ . "/../lib/ServiceRegistry/Janus/Config/EntityPermissionTableParser.php";

use ServiceRegistry\Janus\Config\GlobalPermissionTableParser;
use ServiceRegistry\Janus\Config\EntityPermissionTableParser;

$access = array();
$parser = new GlobalPermissionTableParser(__DIR__ . '/permissions.table');
$access = $parser->parse();

$parser = new EntityPermissionTableParser(__DIR__ . '/permissions.entity.table');
$access = array_merge_recursive($access, $parser->parse());