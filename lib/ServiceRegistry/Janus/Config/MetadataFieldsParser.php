<?php
namespace ServiceRegistry\Janus\Config;

/**
 * Fill out some defaults and apply ordering
 */
class MetadataFieldsParser {
    const JANUS_FIELDS_TYPE_ALL = '*';
    const JANUS_FIELDS_TYPE_IDP = 'saml20-idp';
    const JANUS_FIELDS_TYPE_SP = 'saml20-sp';

    protected $_templates;

    public function __construct($templates)
    {
        $this->_templates = $templates;
    }

    public function getSpFields()
    {
        return $this->_getFields(self::JANUS_FIELDS_TYPE_SP);
    }

    public function getIdpFields()
    {
        return $this->_getFields(self::JANUS_FIELDS_TYPE_IDP);
    }

    protected function _getFields($entityType)
    {
        $fields = array();
        foreach ($this->_templates[self::JANUS_FIELDS_TYPE_ALL] as $fieldName => $fieldTemplate) {
            $field = $this->_applyDefaults($fieldTemplate);
            $fields[$fieldName] = $field;
        }

        $templates = $this->_templates[$entityType];
        $entityFields = array();
        foreach ($templates as $fieldName => $fieldTemplate) {
            $field = $this->_applyDefaults($fieldTemplate);
            $entityFields[$fieldName] = $field;
        }
        $fields = static::_merge($fields, $entityFields);

        return $fields;
    }

    protected function _applyDefaults($fieldTemplate)
    {
        $field = $fieldTemplate;
        if (!isset($field['type'])) {
            $field['type'] = 'text';
        }
        if (isset($field['default']) && !isset($field['default_allow'])) {
            $field['default_allow'] = TRUE;
        }
        if (!isset($field['default'])) {
            $field['default'] = '';
            $field['default_allow'] = FALSE;
        }
        if (!isset($field['required'])) {
            $field['required'] = FALSE;
        }
        return $field;
    }

    protected static function _merge($array1, $array2)
    {
        foreach($array2 as $key => $Value)
        {
            if (array_key_exists($key, $array1) && is_array($Value)) {
                $array1[$key] = static::_merge($array1[$key], $array2[$key]);
            }
            else {
                $array1[$key] = $Value;
            }
        }
        return $array1;
    }
}
