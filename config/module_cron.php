<?php
/* 
 * Configuration for the Cron module.
 * 
 * $Id: $
 */

$config = array (
    'key' => '',
    'allowed_tags' => array('daily', 'hourly', 'frequent'),
    'debug_message' => FALSE,
    'sendemail' => FALSE,
    'enabled' => FALSE
);

$localConfig = '/etc/surfconext/serviceregistry.module_cron.php';
if (file_exists($localConfig)) {
    require $localConfig;
}