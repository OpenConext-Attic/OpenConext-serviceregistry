<?php

/**
 * Override of DbPatch Application class to inject configuration, read from the EngineBlock configuration file
 *
 * @throws EngineBlock_Exception
 *
 */
class ServiceRegistry_DbPatch_Core_Application extends DbPatch_Core_Application
{
    const PATCH_DIR_RELATIVE = '/../../../../database/patch';

    private function _getDatabaseConfig()
    {
        return new Zend_Config(SimpleSAML_Configuration::getConfig('module_janus.php')->getArray('store'));
    }

    protected function getConfig($filename = null)
    {
        $dbConfig = $this->_getDatabaseConfig();

        $dsnParsed = parse_url($dbConfig->dsn);
        $dsnPathParts = explode(';', $dsnParsed['path']);
        $dsnProperties = array();
        foreach ($dsnPathParts as $dsnPathPart) {
            $dsnPathPart = explode('=', $dsnPathPart);
            $dsnProperties[array_shift($dsnPathPart)] = implode($dsnPathPart, '=');
        }

        $config = array(
            'db' => array(
                'adapter'   => $this->_convertPdoDriverToZendDbAdapter($dsnParsed['scheme']),
                'params' => array(
                    'host'      => isset($dsnProperties['host'])    ? $dsnProperties['host']    : 'localhost',
                    'username'  => isset($dbConfig->username)       ? $dbConfig->username   : 'root',
                    'password'  => isset($dbConfig->password)       ? $dbConfig->password       : '',
                    'dbname'    => isset($dsnProperties['dbname'])  ? $dsnProperties['dbname']  : 'serviceregistry',
                    'charset'   => isset($dsnProperties['charset']) ? $dsnProperties['charset'] : 'utf8',
                ),
            ),
            'patch_directory' => realpath(__DIR__ . self::PATCH_DIR_RELATIVE),
            'color' => true,
        );
        return new Zend_Config($config, true);
    }

    private function _convertPdoDriverToZendDbAdapter($pdoDriver)
    {
        switch ($pdoDriver) {
            case 'mysql':
                return 'Mysqli';
            default:
                throw new Exception("Unsupported PDO driver '$pdoDriver'");
        }
    }
}
