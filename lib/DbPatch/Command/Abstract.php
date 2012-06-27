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
 * @subpackage Command
 * @author Sandy Pleyte
 * @author Martijn De Letter
 * @copyright 2011 Sandy Pleyte
 * @copyright 2010-2011 Martijn De Letter
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @link http://www.github.com/dbpatch/DbPatch
 * @since File available since Release 1.0.0
 */

/**
 * Abstract command class
 *
 * @package DbPatch
 * @subpackage Command
 * @author Sandy Pleyte
 * @author Martijn De Letter
 * @copyright 2011 Sandy Pleyte
 * @copyright 2010-2011 Martijn De Letter
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @link http://www.github.com/dbpatch/DbPatch
 * @since File available since Release 1.0.0
 */
abstract class DbPatch_Command_Abstract
{
    /**
     * @var string
     */
    const DEFAULT_BRANCH = 'default';

    /**
     * @var string
     */
    const TABLE = 'db_changelog';

    /**
     * @var string
     */
    const PATCH_DIRECTORY = './database/patch';

    /**
     * @var string
     */
    const PATCH_PREFIX = 'patch';

    /**
     * @var \DbPatch_Core_Db
     */
    protected $db = null;

    /**
     * @var \Zend_Config|\Zend_Config_Ini|\Zend_Config_Xml
     */
    protected $config = null;

    /**
     * @var \DbPatch_Core_Console
     */
    protected $console = null;

    /**
     * @var \DbPatch_Core_Writer
     */
    protected $writer = null;

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @abstract
     * @return void
     */
    abstract public function execute();

    /**
     * @throws DbPatch_Exception
     * @return DbPatch_Command_Abstract
     */
    public function init()
    {
        if (!$this->validateChangelog()) {
            throw new DbPatch_Exception('Can\'t create changelog table');
        }
        return $this;
    }

    /**
     * @param DbPatch_Core_Db $db
     * @return DbPatch_Command_Abstract
     */
    public function setDb(DbPatch_Core_Db $db)
    {
        $this->db = $db;
        return $this;
    }

    /**
     * @param DbPatch_Core_Console $console
     * @return DbPatch_Command_Abstract
     */
    public function setConsole(DbPatch_Core_Console $console)
    {
        $this->console = $console;
        return $this;
    }

    /**
     * @param Zend_Config|Zend_Config_Ini|Zend_Config_Xml $config
     * @return DbPatch_Command_Abstract
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @param DbPatch_Core_Writer $writer
     * @return DbPatch_Command_Abstract
     */
    public function setWriter(DbPatch_Core_Writer $writer)
    {
        $this->writer = $writer;
        return $this;
    }

    /**
     * @return null|Zend_Config|Zend_Config_Ini|Zend_Config_Xml
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return \DbPatch_Core_Writer|null
     */
    public function getWriter()
    {
        return $this->writer;
    }

    /**
     * @return null|\Zend_Db_Adapter_Abstract
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @param $options
     * @return void
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Validate if the changelog is present in the database
     * if not try to create the table
     * @return bool
     */
    protected function validateChangelog()
    {
        if ($this->changelogExists()) {
            $this->updateColumnType();
            return true;
        }

        $this->getWriter()
                ->line("no changelog database found, try to create one");

        if (!$this->createChangelog()) {
            $this->getWriter()
                    ->line("couldn't create a changelog table");
            return false;
        }
        return true;
    }

    /**
     * @return DbPatch_Core_Console|null
     */
    public function getConsole()
    {
        return $this->console;
    }

    /**
     * @return string
     */
    public function getBranch()
    {
        if ($this->console instanceof DbPatch_Core_Console &&
            $this->console->issetOption('branch')
        ) {
            return $this->console->getOptionValue('branch');
        } else if (isset($this->config->default_branch)) {
            return $this->config->default_branch;
        } else {
            return self::DEFAULT_BRANCH;
        }
    }

    /**
     * @return string
     */
    public function getPatchPrefix()
    {
        if (isset($this->config->patch_prefix)) {
            $prefix = $this->config->patch_prefix;
        } else {
            $prefix = self::PATCH_PREFIX;
        }
        return $prefix;
    }

    /**
     * @return string|bool
     */
    public function getPatchDirectory()
    {
        if (isset($this->config->patch_directory)) {
            $dir = $this->trimTrailingSlashes($this->config->patch_directory);
        } else {
            $dir = self::PATCH_DIRECTORY;
        }

        return $dir;
    }

    /**
     * Check if the passed patch number can be found in the changelog table
     * for the specified branch
     *
     * @param int $patchNumber
     * @param string $branch
     * @return boolean $result true if patch already applied; false if not
     */
    protected function isPatchApplied($patchNumber, $branch)
    {
        $db = $this->getDb()->getAdapter();
        $query = sprintf("SELECT COUNT(patch_number) as applied
                          FROM %s
                          WHERE patch_number = %d
                          AND branch = %s",
                         $db->quoteIdentifier(self::TABLE),
                         $patchNumber,
                         $db->quote($branch));

        $patchRecords = $db->fetchAll($query);

        if ((int)$patchRecords[0]['applied'] == 0) {
            return false;
        }

        return true;
    }

    /**
     * @param string $branch
     * @param null|int $searchPatchNumber
     * @return array
     */
    public function getPatches($branch, $searchPatchNumber = null)
    {
        $patchDirectory = $this->getPatchDirectory();

        if (!is_dir($patchDirectory)) {
            $this->writer->error('path ' . $patchDirectory . ' doesn\'t exists');
            return array();
        }

        try {
            $iterator = new DirectoryIterator($patchDirectory);
        } catch (Exception $e) {
            $this->writer->line('Error: ' . $e->getMessage());
            return array();
        }

        $branch = $branch == '' ? $this->getBranch() : $branch;
        $patchPrefix = $this->getPatchPrefix();

        if ($branch <> self::DEFAULT_BRANCH) {
            $patchPrefix .= '-' . $branch;
        }

        $patches = array();
        $pattern = '/^' . preg_quote($patchPrefix) . '-(\d{3,4})\.(sql|php)$/';

        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isDot() || substr($fileinfo->getFilename(), 0, 1) == '.') {
                continue;
            }
            if (preg_match($pattern, $fileinfo->getFilename(), $matches)) {
                $patchNumber = (int)$matches[1];

                if ((!is_null($searchPatchNumber) && $searchPatchNumber != '*' && $patchNumber != $searchPatchNumber) ||
                    is_null($searchPatchNumber) && $this->isPatchApplied($patchNumber, $branch)
                ) {
                    continue;
                }

                $filename = $patchDirectory . '/' . $fileinfo->getFilename();
                $type = $matches[2];

                $patch = DbPatch_Command_Patch::factory($type);

                $patch->loadFromArray(
                    array(
                         'filename' => $filename,
                         'basename' => $matches[0],
                         'patchNumber' => $patchNumber,
                         'branch' => $branch
                    )
                );

                $patches[$patchNumber] = $patch;
            }
        }
        ksort($patches);
        return $patches;
    }


    /**
     * Detect the different branches that are used in the patch dir
     * the default branch is always returned
     *
     * @return array
     */
    protected function detectBranches()
    {
        $branches = array(self::DEFAULT_BRANCH);

        $patchDirectory = $this->getPatchDirectory();

        if (!is_dir($patchDirectory)) {
            $this->writer->error('path ' . $patchDirectory . ' doesn\'t exists');
            return array();
        }

        try {
            $iterator = new DirectoryIterator($patchDirectory);
        } catch (Exception $e) {
            $this->writer->line('Error: ' . $e->getMessage());
            return array();
        }


        $patchPrefix = $this->getPatchPrefix();
        $pattern = '/^' . preg_quote($patchPrefix) . '-(.*)?-\d{3,4}\.(sql|php)$/';

        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isDot() || substr($fileinfo->getFilename(), 0, 1) == '.') {
                continue;
            }

            if (preg_match($pattern, $fileinfo->getFilename(), $matches)) {

                $branch = $matches[1];
                if (!in_array($branch, $branches)) {
                    $branches[] = $branch;
                }
            }
        }
        return $branches;
    }


    /**
     * @param int $patchNumber
     * @param string $branch
     * @return bool|DbPatch_Command_Patch_Abstract
     */
    public function getPatch($patchNumber, $branch)
    {
        $patches = $this->getPatches($branch, $patchNumber);
        if (array_key_exists($patchNumber, $patches)) {
            return $patches[$patchNumber];
        }
        return false;
    }

    /**
     * Checks if the changelog table is present in the database
     * @return bool
     */
    protected function changelogExists()
    {
        try {
            return in_array(
                self::TABLE, $this->getDb()->getAdapter()->listTables()
            );
        } catch (Zend_Db_Adapter_Exception $e) {
            throw new DbPatch_Exception('Database error: ' . $e->getMessage());
        } catch (Exception $e) {
            return false;
        }

    }

    /**
     * Check column types
     * return void
     */
    protected function updateColumnType()
    {
        $adapter = strtolower($this->config->db->adapter);
        if (in_array($adapter, array('mysql', 'mysqli', 'pdo_mysql'))) {
            $columns = $this->getDb()->getAdapter()->describeTable(self::TABLE);
            foreach($columns as $columnName => $meta) {
                if ($columnName == 'completed' && strtolower($meta['DATA_TYPE']) == 'timestamp') {
                    $db = $this->getDb()->getAdapter();
                    $db->query(sprintf("ALTER TABLE %s ADD completed2 int(11) NOT NULL DEFAULT 0 AFTER completed", $db->quoteIdentifier(self::TABLE)));
                    $db->query(sprintf("UPDATE %s SET completed2 = UNIX_TIMESTAMP(completed)", $db->quoteIdentifier(self::TABLE)));
                    $db->query(sprintf("ALTER TABLE %s DROP COLUMN completed, CHANGE completed2 completed INT(11) NOT NULL", $db->quoteIdentifier(self::TABLE)));
                    $this->writer->line('Updated column type');
                }
            }
        }
    }

    /**
     * Try to create the changelog table
     *
     * @return bool
     */
    protected function createChangelog()
    {
        if ($this->changelogExists()) {
            return true;
        }

        $db = $this->getDb()->getAdapter();

        $db->query(
            sprintf("
             CREATE TABLE %s (
             patch_number int NOT NULL,
             branch varchar(50) NOT NULL,
             completed int,
             filename varchar(100) NOT NULL,
             hash varchar(32) NOT NULL,
             description varchar(200) default NULL,
             PRIMARY KEY  (patch_number, branch)
        )", $db->quoteIdentifier(self::TABLE)
            ));


        if (!$this->changelogExists()) {
            return false;
        }

        $this->getWriter()->line(sprintf("changelog table '%s' created", self::TABLE));
        $this->getWriter()->line("use 'dbpatch sync' to sync your patches");

        return true;
    }

    /**
     * Store patchfile entry to the changelog table
     *
     * @param array $patchFile
     * @param string $description
     * @return void
     */
    protected function addToChangelog($patchFile, $description = null)
    {
        if ($description == null) {
            $description = $patchFile->description;
        }

        if($this->isPatchApplied($patchFile->patch_number, $patchFile->branch)) {
             $this->writer->warning(
                 sprintf(
                     'Skip %s, already exists in the changelog',
                     $patchFile->basename
                 )
             );
         } else {
            $db = $this->getDb()->getAdapter();

            $sql = sprintf("
                INSERT INTO %s (patch_number, branch, completed, filename, description, hash)
                VALUES(%d, %s, %d, %s, %s, %s)",
                           $db->quoteIdentifier(self::TABLE),
                           $patchFile->patch_number,
                           $db->quote($patchFile->branch),
                           time(),
                           $db->quote($patchFile->basename),
                           $db->quote($description),
                           $db->quote($patchFile->hash)
            );

            $db->query($sql);
            $this->writer->line(
                sprintf(
                    'added %s to the changelog ',
                    $patchFile->basename
                )
            );
        }
    }

    /**
     * Dump database
     *
     * @param string $filename
     * @return bool
     */
    protected function dumpDatabase($filename, $noData = false)
    {
        try {
            $db = $this->getDb();
            $db->dump($filename, $noData);
        } catch (Exception $e) {
            $this->writer->error($e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * Create dump filename
     * @return string
     */
    protected function getDumpFilename()
    {
        $filename = null;
        $config = $this->getDb()->getAdapter()->getConfig();
        $database = $config['dbname'];

        if ($this->console->issetOption('file')) {
            $filename = $this->console->getOptionValue('file', null);
        }

        if (is_null($filename)) {
            // split by slash, database name can be a path (in case of SQLite)
            $parts    = explode(DIRECTORY_SEPARATOR, $database);
            $filename = array_pop($parts) . '_' . date('Ymd_His') . '.sql';
        }

        if (isset($this->config->dump_directory)) {
            $filename = $this->trimTrailingSlashes($this->config->dump_directory) . '/' . $filename;
        } else {
            $filename = './' . $filename;
        }

        return $filename;
    }

    /**
     * Show help options for a given command
     *
     * @param string $command
     * @return void
     */
    protected function showHelp($command = null)
    {
        $writer = $this->getWriter();
        $writer->line('usage: dbpatch ' . $command . ' [<args>]')
                ->line()
                ->line('The args are:')
                ->indent(2)->line('--config=<string>  Filename of the config file')
                ->indent(2)->line('--branch=<string>  Branch name')
                ->indent(2)->line('--color            Show colored output');

    }

    /**
     * Remove trailing slash from string
     * @param string $str String
     * @return string
     */
    function trimTrailingSlashes( $str )
    {
        $str = trim($str);
        return $str == '/' ? $str : rtrim($str, '/');
    }


}
