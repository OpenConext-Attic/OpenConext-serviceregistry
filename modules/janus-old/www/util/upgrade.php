<?php
/**
 * Upgrade script fra JANUS 1.5 to JANUS 1.6
 *
 * This script should be deleted after use or if its use is not required.
 *
 * PHP version 5
 *
 * @category   SimpleSAMLphp
 * @package    JANUS
 * @subpackage Upgrade
 * @author     Jacob Christiansen <jach@wayf.dk>
 * @copyright  2010 Jacob Christiansen
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @version    SVN: $Id: upgrade.php 999 2012-04-03 10:07:18Z jach@wayf.dk $
 * @link       http://code.google.com/p/janus-ssp/
 * @since      File available since Release 1.6.0
 */

// Uncomment line below to grant access to the script
exit('No access');

// Modify variables below to fit your DA
$type = null;
$host = null;
$name = null;
$prefix = null;
$user = null;
$pass = null;

// Modify variable below to fit your DB
$dsn = $type .':host='. $host . ';dbname='. $name;

// Metadata fields to be updated
$fields = array(
    'SingleSignOnService' => 'SingleSignOnService:0:Location',                
    'SingleLogoutService' => 'SingleLogoutService:0:Location',
    'certFingerprint' => 'certFingerprint:0',
    'AssertionConsumerService' => 'AssertionConsumerService:0:Location',
    'SingleLogoutService' => 'SingleLogoutService:0:Location',
    'contacts:contactType' => 'contacts:0:contactType',
    'contactS:name' => 'contact:0:name',
    'contactS:surName' => 'contact:0:surName',
    'contactS:givenName' => 'contact:0:givenName',
    'contactS:telephoneNumber' => 'contact:0:telephoneNumber',
    'contactS:company' => 'contact:0:company',
    'contactS:emailAddress' => 'contact:0:emailAddress',
    'entity:description:da' => 'description:da',
    'entity:description:en' => 'description:en',
    'entity:description:es' => 'description:es',
    'entity:name:da' => 'name:da',
    'entity:name:en' => 'name:en',
    'entity:name:es' => 'name:es',
    'entity:url:da' => 'url:da',
    'entity:url:en' => 'url:en',
    'entity:url:es' => 'url:es',
);

$error = false;

//--------------------------- Script start -----------------------------------
echo '<h1>DB update for JANUS 1.5 to 1.6</h1>';
echo '<p>The entity table will be updated to include a column for the user ID.</p>';
echo '<p>The following metadata fields will be updated</p>';
echo '<pre>';
print_r($fields);
echo '</pre>';
echo '<hr><h2>Updating started</h2>';

$dbh = new PDO($dsn, $user, $pass);

$dbh->beginTransaction();

// Update entity table 
$st = $dbh->exec('
    ALTER TABLE `' . $prefix  . 'entity` 
    ADD `user` INT NOT NULL AFTER `arp`;'
);

if($st === false) {
    echo '<p><b style="color: #FF0000;">ERROR:</b> entity table not updated</p>';
    echo '<p>Error information</p>';
    echo '<pre>';
    print_r($dbh->errorInfo());
    echo '</pre>';
    $error = true;
} else {
    echo '<p><b style="color: #00FF00">OK:</b> entity table updated</p>';
}

// Update the metadata fields
foreach($fields AS $old => $new) {
    $st = $dbh->prepare('
        UPDATE `' . $prefix  . 'metadata`
        SET `key` = ?
        WHERE `key` = ?'            
    );
    
    $st->execute(array($new, $old));

    if($st === false) {
        echo '<p><b style="color: #FF0000;">ERROR:</b> ' . $old . ' not updated</p>';
        echo '<p>Error information</p>';
        echo '<pre>';
        print_r($dbh->errorInfo());
        echo '</pre>';
        $error = true;
    } else {
        echo '<p><b style="color: #00FF00">OK:</b> ' . $old . ' changed to ' . $new . '</p>';
    }
}

if(!$error) {
    $dbh->commit();
    echo '<h2 style="color: 00FF00">Update successful</h2>';
    echo '<p>Remeber to manually upgrade your config file, to accomodate for the changes in the metadata field names.</p>';
} else {
    $dbh->rollBack();
    echo '<h2 style="color: #FF0000">Update was not succesfull. Changes have been rolled back</h2>';
}
