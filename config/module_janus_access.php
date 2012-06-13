<?php

$access = array();
$parser = new GlobalPermissionTableParser('permissions.table');
$access = $parser->parse();

$parser = new EntityPermissionTableParser('permissions.entity.table');
$access = array_merge_recursive($access, $parser->parse());

class GlobalPermissionTableParser
{
    private $_input = "";

    public function __construct($filePath)
    {
        $this->_input = file($filePath);
    }

    public function parse()
    {
        // Shift off the header
        array_shift($this->_input);
        array_shift($this->_input);

        $access = array();
        while ($line = array_shift($this->_input)) {
            list ($right, $roles) = explode('|', $line);
            $right = strtolower(preg_replace('/\s+/', '', $right));
            $roles = array_map('trim', explode(',', $roles));
            $access[$right] = array( 'roles' => $roles );
        }

        return $access;
    }
}

class EntityPermissionTableParser
{
    private $_input = "";

    public function __construct($filePath)
    {
        $this->_input = file($filePath);
    }

    public function parse()
    {
        $headers = explode('|', array_shift($this->_input));
        $states = array_map('trim', array_slice($headers, 2));
        array_shift($this->_input);

        $access = array();
        foreach ($this->_input as $line) {
            $values = array_map('trim', explode('|', $line));

            $right = strtolower(preg_replace('/\s+/', '', array_shift($values)));
            $default = array_shift($values) === 'Y' ? true : false;

            $access[$right] = array(
                'default' => $default,
            );

            for ($i = 0; $i < count($states); $i++) {
                if ($values[$i]) {
                    $access[$right][$states[$i]]['role'] = explode(',', $values[$i]);
                }
            }
        }

        return $access;
    }
}