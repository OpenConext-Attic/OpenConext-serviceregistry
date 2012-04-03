<?php
/*
 * Generate metadata
 *
 * @author Jacob Christiansen, <jach@wayf.dk>
 * @package SimpleSAMLphp
 * @subpackeage JANUS
 * @version $Id: exportentity.php 999 2012-04-03 10:07:18Z jach@wayf.dk $
 */

/* Load simpleSAMLphp, configuration and metadata */
$session = SimpleSAML_Session::getInstance();
$config = SimpleSAML_Configuration::getInstance();
$janus_config = SimpleSAML_Configuration::getConfig('module_janus.php');

$authsource = $janus_config->getValue('auth', 'login-admin');
$useridattr = $janus_config->getValue('useridattr', 'eduPersonPrincipalName');

if ($session->isValid($authsource)) {
    $attributes = $session->getAttributes();
    // Check if userid exists
    if (!isset($attributes[$useridattr]))
        throw new Exception('User ID is missing');
    $userid = $attributes[$useridattr][0];
} else {
    $session->setData('string', 'refURL', SimpleSAML_Utilities::selfURL());
    SimpleSAML_Utilities::redirect(SimpleSAML_Module::getModuleURL('janus/index.php'));
}

if(isset($_GET['eid'])) {
    $eid = $_GET['eid'];
} else {
    throw new SimpleSAML_Error_Exception('Eid must be set');
}
if(isset($_GET['revisionid'])) {
    $revisionid = $_GET['revisionid'];
} else {
    throw new SimpleSAML_Error_Exception('Revisionid must be set');
}

$metaxml = sspmod_janus_MetaExport::getReadableXMLMetadata(
    $eid, 
    $revisionid,
    array(
        'maxCache' => $janus_config->getValue('maxCache', NULL),
        'maxDuration' => $janus_config->getValue('maxDuration', NULL),
    )
);

$metaflat = sspmod_janus_MetaExport::getFlatMetadata($eid, $revisionid);

$metaarray = sspmod_janus_MetaExport::getPHPArrayMetadata($eid, $revisionid);
                                                     
// Error generating som of the metadata
if(empty($metaflat) || empty($metaxml)) {
    $t = new SimpleSAML_XHTML_Template($config, 'janus:error.php', 'janus:error');
    $t->data['header'] = 'JANUS';
    $t->data['title'] = 'error_required_metadata_missing_header';
    $t->data['error'] = 'error_required_metadata_missing';
    $t->data['extra_data'] = '<ul><li>' .implode("</li>\n<li>", sspmod_janus_MetaExport::getError()) . '</li></ul>';
    $t->show();
    exit(0);
} elseif (array_key_exists('output', $_GET) && $_GET['output'] == 'xhtml') {
    $t = new SimpleSAML_XHTML_Template($config, 'janus:metadata.php', 'janus:metadata');

    $t->data['header'] = 'Metadata export';
    $t->data['metaurl'] = SimpleSAML_Utilities::selfURLNoQuery();
    $t->data['metadata'] = htmlentities($metaxml);
    $t->data['metadataflat'] = htmlentities($metaflat, ENT_COMPAT, 'UTF-8');
    $t->data['metadatajson'] = json_encode($metaarray);
    $t->data['revision'] = $revisionid;
    $t->data['eid'] = $eid;

    // Send metadata to admin
    if(isset($_GET['send_mail'])) {
        $t->data['send_mail'] = TRUE;
        $t->data['mail'] = $userid;
    }
    $t->show();
} else {
    header('Content-Type: application/xml');
    echo $metaxml;
    exit(0);
}
?>
