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

    /**
     * Loads Janus database parameters
     *
     * @return array
     * @throws RuntimeException
     */
    private function _getDatabaseConfig()
    {
        $pathToParameters = realpath(__DIR__ . '/../../../../simplesamlphp/modules/janus/app/config/parameters.yml');
        if (!is_readable($pathToParameters)) {
            throw new \RuntimeException("Parameters file cannot be read");
        }

        $parametersYaml = file_get_contents($pathToParameters);
        $yamlParser = new \Symfony\Component\Yaml\Parser();
        $parameters = $yamlParser->parse($parametersYaml);


        $databaseParameters = array();
        $prefix = 'database_';
        $prefixLength = strlen($prefix);
        foreach($parameters['parameters'] as $name => $value) {
            if(substr($name, 0, $prefixLength) === $prefix) {
                $databaseParameters[substr($name, $prefixLength)] = $value;
            }
        }

        return $databaseParameters;
    }

    protected function getConfig($filename = null)
    {
        $dbConfig = $this->_getDatabaseConfig();

        $config = array(
            'db' => array(
                'adapter'   => $this->_convertPdoDriverToZendDbAdapter('mysql'),
                'params' => array(
                    'host'      => $dbConfig['host'],
                    'username'  => $dbConfig['user'],
                    'password'  => $dbConfig['password'],
                    'dbname'    => $dbConfig['name'],
                    'charset'   => 'utf8',
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
