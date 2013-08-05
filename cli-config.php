<?php
// cli-config.php
require_once 'vendor/simplesamlphp/simplesamlphp/lib/_autoload.php';
require_once 'vendor/simplesamlphp/simplesamlphp/modules/janus/lib/DiContainer.php';

// Any way to access the EntityManager from  your application
$em = sspmod_janus_DiContainer::getInstance()->getEntityManager();

$helperSet = new \Symfony\Component\Console\Helper\HelperSet(array(
'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em)
));