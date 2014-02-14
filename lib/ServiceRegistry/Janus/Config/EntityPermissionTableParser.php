<?php
/**
 * @author Lucas van Lierop <lucas@vanlierop.org>
 */

namespace ServiceRegistry\Janus\Config;


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