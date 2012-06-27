<?php
/**
 * DbPatch
 *
 * Copyright (c) 2011, Sandy Pleyte.
 * Copyright (c) 2010-2011, Martijn De Letter.
 *
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *  * Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 *
 *  * Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in
 *    the documentation and/or other materials provided with the
 *    distribution.
 *
 *  * Neither the name of the authors nor the names of his
 *    contributors may be used to endorse or promote products derived
 *    from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package DbPatch
 * @subpackage Core
 * @author Sandy Pleyte
 * @author Martijn De Letter
 * @copyright 2011 Sandy Pleyte
 * @copyright 2010-2011 Martijn De Letter
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @link http://www.github.com/dbpatch/DbPatch
 * @since File available since Release 1.0.0
 */

/**
 * Config object
 *
 * @package DbPatch
 * @subpackage Core
 * @author Sandy Pleyte
 * @author Martijn De Letter
 * @copyright 2011 Sandy Pleyte
 * @copyright 2010-2011 Martijn De Letter
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @link http://www.github.com/dbpatch/DbPatch
 * @since File available since Release 1.0.0
 */
class DbPatch_Core_Config
{
    /**
     * @var null|\Zend_Config|\Zend_Config_Ini|\Zend_Config_Xml
     */
    protected $config = null;

    /**
     * @param string $filename optional config file
     */
    public function __construct($filename = null)
    {
        if (is_null($filename) || !file_exists($filename)) {
            $filename = $this->searchConfigFile();
        }

        if (is_null($filename)) {
            throw new DbPatch_Exception('No config file found');
        }

        $type = $this->detectConfigType($filename);

        switch ($type) {
            case 'php' :
                $this->config = $this->loadPhpConfig($filename, 'dbpatch', false);
                break;
            case 'ini' :
                $this->config = $this->loadIniConfig($filename, 'dbpatch', true);
                break;
            case 'xml' :
                $this->config = $this->loadXmlConfig($filename, 'dbpatch', true);
                break;
            default:
                throw new DbPatch_Exception('Not a valid config file');
        }
    }

    /**
     * Load a PHP config file
     *
     * @param string $filename Path to config file to load
     * @param bool $allowOverride
     */
    protected function loadPhpConfig($filename, $allowOverride)
    {
        $returnedConfig = require_once $filename;

        if (isset($dbPatchConfig)) {
            return new Zend_Config($dbPatchConfig, $allowOverride);
        }

        if ($returnedConfig instanceof Zend_Config) {
            return $returnedConfig;
        }

        if (is_array($returnedConfig)) {
            return new Zend_Config($returnedConfig, $allowOverride);
        }

        throw new DbPatch_Exception(
            'Could not determine database configuration'
        );
    }

    /**
     * Load a Ini config file
     *
     * @param string $filename Path to config file to load
     * @param string $section Config section to load from file
     * @param bool $allowOverride
     */
    protected function loadIniConfig($filename, $section, $allowOverride)
    {
        return new Zend_Config_Ini($filename, $section, $allowOverride);
    }

    /**
     * Load a XML config file
     *
     * @param string $filename Path to config file to load
     * @param string $section Config section to load from file
     * @param bool $allowOverride
     */
    protected function loadXmlConfig($filename, $section, $allowOverride)
    {
        return new Zend_Config_Xml($filename, $section, $allowOverride);
    }

    /**
     * @return null|string
     */
    protected function searchConfigFile()
    {
        $supportedConfigExtentsions = array('php', 'ini', 'xml');

        foreach ($supportedConfigExtentsions as $ext) {
            $filename = './dbpatch.' . $ext;
            if (
                (file_exists($filename)) &&
                (realpath($filename) != realpath($_SERVER['argv'][0]))
            ) {
                return $filename;
            }
        }
        return null;
    }

    /**
     * Detect config type based on file extension
     *
     * @param string $filename
     * @return string
     */
    protected function detectConfigType($filename)
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * @return null|Zend_Config|Zend_Config_Ini|Zend_Config_Xml
     */
    public function getConfig()
    {
        return $this->config;
    }
}
