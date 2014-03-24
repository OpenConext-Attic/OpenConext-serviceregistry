<?php
// This is only required when loading config files directly from Janus (classes aren't autoloaded)
require_once __DIR__ . "/../lib/ServiceRegistry/Janus/Config/MetadataFieldsParser.php";

use ServiceRegistry\Janus\Config\MetadataFieldsParser;

/**
 * Defaults:
 *      type = 'text'
 *      required = FALSE
 *      default = '' (default value)
 *      default_allow = FALSE (unless you set a default, in which case this is TRUE by default)
 */

$template = array(
    // Fields for ALL entities (both Service Provider and Identity Provider)
    MetadataFieldsParser::JANUS_FIELDS_TYPE_ALL => array(
        'name:#'                    => array('required'=>TRUE, 'supported' => array('en', 'nl')),
        'displayName:#'             => array(                  'supported' => array('en', 'nl')),
        'description:#'             => array('required'=>TRUE, 'supported' => array('en', 'nl')),

        'certData'                  => array(),
        'certData2'                 => array(),
        'certData3'                 => array(),

        'SingleLogoutService_Binding' => array(
            'type' => 'select',
            'select_values' => array(
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                'urn:oasis:names:tc:SAML:2.0:bindings:SOAP',
                'urn:oasis:names:tc:SAML:2.0:bindings:PAOS',
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact',
                'urn:oasis:names:tc:SAML:2.0:bindings:URI'
            ),
            'default' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'required'=>FALSE,
        ),
        'SingleLogoutService_Location' => array(
            'required'=>FALSE,
            'validate' => 'isurl'
        ),
        'NameIDFormat' => array(
            'type' => 'select',
            'required'=>FALSE,
            'select_values' => array(
                'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
                'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
                'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
                /**
                 * @deprecated This is an incorrect name id format since unspecified does no longer exist in SAML 2.0, only use this for backwards compatibility
                 */
                'urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified'
            ),
            'default' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
        ),
        'contacts:#:contactType'    => array(
            'type' => 'select',
            'required' => TRUE,
            'supported' => array(0,1,2),
            'select_values' => array('technical', 'support', 'administrative', 'billing', 'other')
        ),
        'contacts:#:givenName'      => array('required' => TRUE, 'supported' => array(0,1,2)),
        'contacts:#:surName'        => array('required' => TRUE, 'supported' => array(0,1,2)),
        'contacts:#:emailAddress'   => array('required' => TRUE, 'supported' => array(0,1,2), 'validate'=>'isemail'),
        'contacts:#:telephoneNumber'=> array(                    'supported' => array(0,1,2)),

        'OrganizationName:#'        => array(                    'supported' => array('en', 'nl')),
        'OrganizationDisplayName:#' => array(                    'supported' => array('en', 'nl')),
        'OrganizationURL:#'         => array('validate' => 'isurl', 'supported' => array('en', 'nl')),
        'logo:0:url'    => array('required' => TRUE, 'default' => 'https://.png', 'default_allow' => FALSE),
        'logo:0:width'  => array('required' => TRUE, 'default' => '120'),
        'logo:0:height' => array('required' => TRUE, 'default' => '60'),

        'redirect.sign' => array('type' => 'boolean', 'required' => TRUE, 'default' => FALSE),

        // publish SP/IDP metadata to edugain
        'coin:publish_in_edugain' => array('type' => 'boolean'),
        'coin:publish_in_edugain_date' => array('validate' => 'isdatetime'),

        'coin:additional_logging' => array('type' => 'boolean')

        // Institution
        'coin:institution_id' => array(),

    ),

    // Fields only for Identity Providers
    MetadataFieldsParser::JANUS_FIELDS_TYPE_IDP => array(
        // Endpoint fields
        'SingleSignOnService:0:Binding' => array(
            'type' => 'select',
            'select_values' => array(
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                'urn:oasis:names:tc:SAML:2.0:bindings:SOAP',
                'urn:oasis:names:tc:SAML:2.0:bindings:PAOS',
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact',
                'urn:oasis:names:tc:SAML:2.0:bindings:URI'
            ),
            'default' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'required' => TRUE,
        ),
        'SingleSignOnService:0:Location' => array('required' => TRUE, 'validate' => 'isurl'),
        'SingleSignOnService:1:Binding' => array(
            'type' => 'select',
            'select_values' => array(
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                'urn:oasis:names:tc:SAML:2.0:bindings:SOAP',
                'urn:oasis:names:tc:SAML:2.0:bindings:PAOS',
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact',
                'urn:oasis:names:tc:SAML:2.0:bindings:URI'
            ),
            'default' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'required' => FALSE,
        ),
        'SingleSignOnService:1:Location' => array('required' => FALSE, 'validate' => 'isurl'),
        'certData'                  => array('required'=>TRUE),

        'coin:guest_qualifier' => array(
            'type' => 'select',
            'required' => TRUE,
            'select_values' => array('All', 'Some', 'None'),
            'default' => 'All',
        ),
        'coin:schachomeorganization' => array(),

        // MDUI stuff
        'keywords:#'    => array('required' => TRUE, 'supported'=>array('en','nl')),

        // disable SAML scoping
        'coin:disable_scoping' => array('type' => 'boolean'),

        // Hide idp from wayf and metadata
        'coin:hidden' => array('type' => 'boolean'),

        // shibmd:Scope element
        'shibmd:scope:#:allowed' => array('supported' => array(0,1,2,3,4,5)),
        'shibmd:scope:#:regexp' => array('type' => 'boolean', 'supported' => array(0,1,2,3,4,5))
    ),

    // Fields only for Service Providers
    MetadataFieldsParser::JANUS_FIELDS_TYPE_SP => array(
        # Must have at least 1 binding
        'AssertionConsumerService:0:Binding' => array(
            'type' => 'select',
            'select_values' => array(
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                'urn:oasis:names:tc:SAML:2.0:bindings:SOAP',
                'urn:oasis:names:tc:SAML:2.0:bindings:PAOS',
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact',
                'urn:oasis:names:tc:SAML:2.0:bindings:URI'
            ),
            'default' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            'required' => TRUE,
        ),
        'AssertionConsumerService:#:Binding' => array(
            'type' => 'select',
            'select_values' => array(
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                'urn:oasis:names:tc:SAML:2.0:bindings:SOAP',
                'urn:oasis:names:tc:SAML:2.0:bindings:PAOS',
                'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact',
                'urn:oasis:names:tc:SAML:2.0:bindings:URI'

            ),
            'default' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            'required' => FALSE,
            'supported' => array(1,2,3,4,5,6,7,8,9),
        ),
        # Must have at least 1 location
        'AssertionConsumerService:0:Location' => array('required' => TRUE, 'validate' => 'isurl'),
        'AssertionConsumerService:#:Location' => array('required' => FALSE, 'validate' => 'isurl', 'supported' => array(1,2,3,4,5,6,7,8,9)),

        'AssertionConsumerService:0:index' => array('required' => FALSE),
        'AssertionConsumerService:#:index' => array('required' => FALSE, 'supported' => array(1,2,3,4,5,6,7,8,9)),

        'NameIDFormats:#' => array(
            'supported' => array(0,1,2),
            'type' => 'select',
            'required'=>FALSE,
            'select_values' => array(
                'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
                'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
                'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
                /**
                 * @deprecated This is an incorrect name id format since unspecified does no longer exist in SAML 2.0, only use this for backwards compatibility
                 */
                'urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified'
            ),
            'default' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
        ),

        'coin:no_consent_required'      => array('type' => 'boolean', 'default' => FALSE),

        'coin:eula'                     => array('validate' => 'isurl'),

        'url:#'                    		=> array('required'=>TRUE, 'supported' => array('en', 'nl'), 'validate' =>'isurl'),

        'coin:alternate_public_key'     => array(),
        'coin:alternate_private_key'    => array(),

        // OAuth
        'coin:gadgetbaseurl'            => array('validate' => 'isurl'),
        'coin:oauth:secret'             => array('validate' => 'lengteq20'),
        'coin:oauth:two_legged_allowed' => array('type' => 'boolean'),
        'coin:oauth:consumer_key'       => array(),
        'coin:oauth:consumer_secret'    => array('validate' => 'lengteq20'),
        'coin:oauth:key_type'           => array(
            'type'=>'select',
            'select_values'=>array('HMAC_SHA1', 'RSA_PRIVATE'),
            'default' => 'HMAC_SHA1',
        ),
        'coin:oauth:public_key'         => array(),
        'coin:oauth:app_title'          => array('default' => 'Application Title','default_allow' => FALSE),
        'coin:oauth:app_description'    => array(),
        'coin:oauth:app_thumbnail'      => array('validate' => 'isurl', 'default' => 'https://www.surfnet.nl/thumb.png', 'default_allow' => FALSE),
        'coin:oauth:app_icon'           => array('validate' => 'isurl', 'default' => 'https://www.surfnet.nl/icon.gif' ,'default_allow' => FALSE),
        'coin:oauth:callback_url'       => array('validate' => 'isurl'),
        'coin:oauth:consent_not_required'   => array('type' => 'boolean'),

        // Provisioning
        'coin:is_provision_sp'          => array('type' => 'boolean'),
        'coin:is_provision_sp_groups'   => array('type' => 'boolean'),
        'coin:provision_domain'         => array(),
        'coin:provision_admin'          => array(),
        'coin:provision_password'       => array(),
        'coin:provision_type'           => array(
            'type' => 'select',
            'select_values' => array("none", "google"),
            'default' => 'google'
        ),
		//Self Service
		'coin:ss:idp_visible_only'      => array('type' => 'boolean', 'default' => FALSE),
        'coin:application_url'          => array('default' => 'Application URL','default_allow' => FALSE),

        // Other
        'coin:provide_is_member_of'     => array('type' => 'boolean', 'default' => FALSE),
        'coin:implicit_vo_id'           => array(),

        'coin:transparant_issuer'       => array('type' => 'boolean'),
        'coin:do_not_add_attribute_aliases' => array('type' => 'boolean', 'default' => FALSE),

        // Do we need to show all Idp's in the WAYF with the unconnected ones grey-out
        'coin:display_unconnected_idps_wayf' => array('type' => 'boolean', 'default' => FALSE)
    ),
);

$fieldTemplates = new MetadataFieldsParser($template);
$fields = array(
    'metadatafields.saml20-idp' => $fieldTemplates->getIdpFields(),
    'metadatafields.saml20-sp'  => $fieldTemplates->getSpFields(),
);


