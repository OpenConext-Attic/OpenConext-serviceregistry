<?php
/*
 * Generate metadata
 *
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @package SimpleSAMLphp
 * @subpackeage JANUS
 * @version $Id: MetaExport.php 946 2011-11-17 10:09:56Z vanlieroplucas@gmail.com $
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class sspmod_janus_MetaExport
{
    const FLATFILE = '__FLAT_FILE_METADATA__';
    
    const XML = '__XML_METADATA__';
    
    const XMLREADABLE = '__XML_READABLE_METADATA__';

    const PHPARRAY = '__PHP_ARRAY_METADATA__';

    private static $_error;

    public static function getError()
    {
        return self::$_error;
    }

    public static function getPHPArrayMetadata($eid, $revision, array $option = null)
    {
        return self::getMetadata($eid, $revision, self::PHPARRAY, $option);
    }

    public static function getFlatMetadata($eid, $revision, array $option = null)
    {   
        return self::getMetadata($eid, $revision, self::FLATFILE, $option);
    }
    
    public static function getXMLMetadata($eid, $revision, array $option = null)
    {   
        return self::getMetadata($eid, $revision, self::XML, $option);
    }

    public static function getReadableXMLMetadata($eid, $revision, array $option = null)
    {   
        return self::getMetadata($eid, $revision, self::XMLREADABLE, $option);
    }

    private static function getMetadata($eid, $revision, $type = null, array $option = null)
    {
        assert('ctype_digit($eid)');
        assert('ctype_digit($revision)');

        $janus_config = SimpleSAML_Configuration::getConfig('module_janus.php');
        $econtroller = new sspmod_janus_EntityController($janus_config);

        if(!$entity = $econtroller->setEntity($eid, $revision)) {
            self::$_error = array('Entity could not be loaded - Eid: ' . $eid . ' Revisionid: ' . $revisionid);
            return false;
        }

        $metadata_raw = $econtroller->getMetadata();

        // Get metadata fields
        $nm_mb = new sspmod_janus_MetadatafieldBuilder(
            $janus_config->getArray('metadatafields.' . $entity->getType())
        );
        $metadatafields_required = $nm_mb->getMetadatafields();

        // Get required metadata fields
        $required = array();
        foreach($metadatafields_required AS $mf) {
            if(isset($mf->required) && $mf->required === true) {
                $required[] = $mf->name;
            }
        }

        // Get metadata to me tested
        $metadata = array();
        foreach($metadata_raw AS $k => $v) {
            // Metadata field not defined
            if (!isset($metadatafields_required[$v->getKey()])) {
                continue;
            }
            // Value not set for metadata
            if (is_string($v->getValue()) && $v->getValue() == '') {
                continue;
            }

            // Compute is the default values is allowed
            $default_allow = false;
            if(isset($metadatafield_required[$v->getKey()]->default_allow) && is_bool($metadata_required[$v->getKey()]->default_allow)) {
                $default_allow = $metadata_required[$v->getKey()]->default_allow;
            }

            /*
             * Do not include metadata if value is set to default and default
             * is not allowed.
             */ 
            if (!$default_allow && (isset($metadata_required[$v->getKey()]->default) && ($v->getValue() == $metadata_required[$v->getKey()]->default))) {
                continue;
            }

            $metadata[] = $v->getKey();
        }

        // Compute missing metadata that is required
        $missing_required = array_diff($required, $metadata);
        
        $entityid = $entity->getEntityid();
        
        if (empty($missing_required)) {
            try {
                $metaArray = $econtroller->getMetaArray();
                $metaArray['eid'] = $eid;

                $blocked_entities = $econtroller->getBlockedEntities();
                $allowed_entities = $econtroller->getAllowedEntities();
                $disable_consent = $econtroller->getDisableConsent();

                $metaflat = '// Revision: '. $entity->getRevisionid() ."\n";
                $metaflat .= var_export($entityid, TRUE) . ' => ' . var_export($metaArray, TRUE) . ',';

                // Add authproc filter to block blocked entities
                if(!empty($blocked_entities) || !empty($allowed_entities)) {
                    $metaflat = substr($metaflat, 0, -2);
                    if(!empty($blocked_entities)) {
                        $metaflat .= "  'blocked' => array(\n";
                        foreach($blocked_entities AS $bentity => $value) {
                            $metaflat .= "    '". $bentity ."',\n";
                        }
                        $metaflat .= "  ),\n";
                    }
                    if(!empty($allowed_entities)) {
                        $metaflat .= "  'allowed' => array(\n";
                        foreach($allowed_entities AS $aentity => $value) {
                            $metaflat .= "      '". $aentity ."',\n";
                        }
                        $metaflat .= "  ),\n";
                    }
                    $metaflat .= '),';
                }

                // Add disable consent
                if(!empty($disable_consent)) {
                    $metaflat = substr($metaflat, 0, -2);
                    $metaflat .= "  'consent.disable' => array(\n";

                    foreach($disable_consent AS $key => $value) {
                        $metaflat .= "    '". $key ."',\n";
                    }

                    $metaflat .= "  ),\n";
                    $metaflat .= '),';
                }

                $maxCache = isset($option['maxCache']) ? $option['maxCache'] : null;
                $maxDuration = isset($option['maxDuration']) ? $option['maxDuration'] : null;
                
                try {
                    $metaBuilder = new SimpleSAML_Metadata_SAMLBuilder($entityid, $maxCache, $maxDuration);
                    $metaBuilder->addMetadata($metaArray['metadata-set'], $metaArray);
                } catch (Exception $e) {
                    SimpleSAML_Logger::error('JANUS - Entity_id:' . $entityid . ' - Error generating XML metadata - ' . var_export($e, true));
                    self::$_error = array('Error generating XML metadata - ' . $e->getMessage());
                    return false;
                }

                // Add organization info
                if(    !empty($metaArray['OrganizationName'])
                    && !empty($metaArray['OrganizationDisplayName'])
                    && !empty($metaArray['OrganizationURL'])
                ) {
                    $metaBuilder->addOrganizationInfo(
                        array(
                            'OrganizationName' => $metaArray['OrganizationName'],
                            'OrganizationDisplayName' => $metaArray['OrganizationDisplayName'],
                            'OrganizationURL' => $metaArray['OrganizationURL']
                        )
                    );
                }

                // Add contact info
                if(!empty($metaArray['contact'])) {
                    $metaBuilder->addContact('technical', $metaArray['contact']);
                }

                switch($type) {
                    case self::XML:
                        return $metaBuilder->getEntityDescriptor();
                    case self::XMLREADABLE:
                        return $metaBuilder->getEntityDescriptorText();
                    case self::PHPARRAY:
                        return $metaArray;
                    case self::FLATFILE:
                    default:
                        return $metaflat;
                }
            } catch(Exception $exception) {
                $session = SimpleSAML_Session::getInstance();
                SimpleSAML_Utilities::fatalError($session->getTrackID(), 'JANUS - Metadatageneration', $exception);
            }
        } else {
            SimpleSAML_Logger::error('JANUS - Missing required metadata fields. Entity_id:' . $entityid);
            self::$_error = $missing_required;
            return false;
        }
    }
}
