<?php
// Added persistent, transient and unspecified to all entities as valid NameIDFormats

/**
 * DbPatch makes the following variables available to PHP patches:
 *
 * @var $this       DbPatch_Command_Patch_PHP
 * @var $writer     DbPatch_Core_Writer
 * @var $db         Zend_Db_Adapter_Abstract
 * @var $phpFile    string
 */

define('SAML2_NAME_ID_FORMAT_UNSPECIFIED', 'urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified');
define('SAML2_NAME_ID_FORMAT_TRANSIENT'  , 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient');
define('SAML2_NAME_ID_FORMAT_PERSISTENT' , 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent');

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

$janusConfig = SimpleSAML_Configuration::getConfig('module_janus.php');

$userController = new sspmod_janus_UserController($janusConfig);
$userController->setUser('engine');
$entities = $userController->getEntities();
/** @var sspmod_janus_Entity $entity */
foreach ($entities as $entity) {

    if ($entity->getType() != 'saml20-sp') {
        continue;
    }

    $entity->setRevisionnote('patch-0015.php: Added persistent, transient and unspecified to all entities as valid NameIDFormats');

    $entityController = new sspmod_janus_EntityController($janusConfig);
    $entityController->setEntity($entity);
    $entityController->addMetadata('NameIDFormats:0', SAML2_NAME_ID_FORMAT_PERSISTENT);
    $entityController->addMetadata('NameIDFormats:1', SAML2_NAME_ID_FORMAT_TRANSIENT);
    $entityController->addMetadata('NameIDFormats:2', SAML2_NAME_ID_FORMAT_UNSPECIFIED);
    $entityController->saveEntity();
}