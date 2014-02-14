<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace ServiceRegistry\Janus\Config;


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
            $access[$right] = array( 'role' => $roles );
        }

        return $access;
    }
}