#!/bin/sh
# Applies various changes to simplesamlphp, this should be ran when composer has installed it in vendor

ROOT_DIR=$(pwd)
VENDOR_DIR="$ROOT_DIR/vendor"
SSP_MODULES_DIR="$VENDOR_DIR/simplesamlphp/simplesamlphp/modules"
JANUS_DIR="$VENDOR_DIR/janus-ssp/janus"

# Apply patch files
echo -e "\nPatching files\n"
REJFILE="/tmp/simplesamlphp_patches-rej-$(date +%s)"
touch $REJFILE
for FILENAME in simplesamlphp_patches/*.patch
do
  echo "- Executing patch: $FILENAME\n"
  patch -Np0 -r $REJFILE < $FILENAME
  echo "\n"
done
rm $REJFILE
echo "Removed $REJFILE"

echo -e "\nFinished"

cd $ROOT_DIR;

# Add/override SimpleSamlPhp config
cp config/* vendor/simplesamlphp/simplesamlphp/config/

# Add/override SimpleSamlPhp metadata
cp metadata/* vendor/simplesamlphp/simplesamlphp/metadata/

# Enable SimpleSamlPhp cron module
touch vendor/simplesamlphp/simplesamlphp/modules/cron/enable

# Link janus module into SimpleSamlPhp
cd $SSP_MODULES_DIR
ln -sf ../../../janus-ssp/janus

# Correct link to jquery since Janus itself is installed in the vendor dir instead of in simplesamlphp
cd $JANUS_DIR/www/resources
ln -sf ../../../../../components

# Set custom janus dictionaries
cd $JANUS_DIR
git checkout dictionaries/metadatafields.definition.json
cd $ROOT_DIR
php bin/mergeJsonFiles.php \
$JANUS_DIR/dictionaries/metadatafields.definition.json \
$ROOT_DIR/janus-dictionaries/metadatafields.definition.json

# Delete unused config, metadata and modules
cd $ROOT_DIR
rm -rf vendor/simplesamlphp/simplesamlphp/config/acl.php   \
    vendor/simplesamlphp/simplesamlphp/config/authmemcookie.php \
    vendor/simplesamlphp/simplesamlphp/config/cas-ldap.php \
    vendor/simplesamlphp/simplesamlphp/config/config-login-auto.php \
    vendor/simplesamlphp/simplesamlphp/config/config-login-feide.php \
    vendor/simplesamlphp/simplesamlphp/config/ldapmulti.php \
    vendor/simplesamlphp/simplesamlphp/config/ldap.php \
    vendor/simplesamlphp/simplesamlphp/config/translation.php \
    vendor/simplesamlphp/simplesamlphp/metadata/adfs-idp-hosted.php \
    vendor/simplesamlphp/simplesamlphp/metadata/adfs-sp-remote.php \
    vendor/simplesamlphp/simplesamlphp/metadata/saml20-idp-hosted.php \
    vendor/simplesamlphp/simplesamlphp/metadata/saml20-sp-remote.php \
    vendor/simplesamlphp/simplesamlphp/metadata/shib13-idp-hosted.php \
    vendor/simplesamlphp/simplesamlphp/metadata/shib13-idp-remote.php \
    vendor/simplesamlphp/simplesamlphp/metadata/shib13-sp-hosted.php \
    vendor/simplesamlphp/simplesamlphp/metadata/shib13-sp-remote.php \
    vendor/simplesamlphp/simplesamlphp/metadata/wsfed-idp-remote.php \
    vendor/simplesamlphp/simplesamlphp/metadata/wsfed-sp-hosted.php \
    vendor/simplesamlphp/simplesamlphp/modules/adfs \
    vendor/simplesamlphp/simplesamlphp/modules/aggregator \
    vendor/simplesamlphp/simplesamlphp/modules/aggregator2 \
    vendor/simplesamlphp/simplesamlphp/modules/aselect \
    vendor/simplesamlphp/simplesamlphp/modules/authcrypt \
    vendor/simplesamlphp/simplesamlphp/modules/authfacebook \
    vendor/simplesamlphp/simplesamlphp/modules/authlinkedin \
    vendor/simplesamlphp/simplesamlphp/modules/authmyspace \
    vendor/simplesamlphp/simplesamlphp/modules/authtwitter \
    vendor/simplesamlphp/simplesamlphp/modules/authwindowslive \
    vendor/simplesamlphp/simplesamlphp/modules/authX509 \
    vendor/simplesamlphp/simplesamlphp/modules/authYubiKey \
    vendor/simplesamlphp/simplesamlphp/modules/autotest \
    vendor/simplesamlphp/simplesamlphp/modules/cas \
    vendor/simplesamlphp/simplesamlphp/modules/casserver \
    vendor/simplesamlphp/simplesamlphp/modules/cdc \
    vendor/simplesamlphp/simplesamlphp/modules/consentAdmin \
    vendor/simplesamlphp/simplesamlphp/modules/consentSimpleAdmin \
    vendor/simplesamlphp/simplesamlphp/modules/discopower \
    vendor/simplesamlphp/simplesamlphp/modules/exampleattributeserver \
    vendor/simplesamlphp/simplesamlphp/modules/exampleauth \
    vendor/simplesamlphp/simplesamlphp/modules/expirycheck \
    vendor/simplesamlphp/simplesamlphp/modules/InfoCard \
    vendor/simplesamlphp/simplesamlphp/modules/logpeek \
    vendor/simplesamlphp/simplesamlphp/modules/memcacheMonitor \
    vendor/simplesamlphp/simplesamlphp/modules/metaedit \
    vendor/simplesamlphp/simplesamlphp/modules/metarefresh \
    vendor/simplesamlphp/simplesamlphp/modules/negotiate \
    vendor/simplesamlphp/simplesamlphp/modules/oauth \
    vendor/simplesamlphp/simplesamlphp/modules/openid \
    vendor/simplesamlphp/simplesamlphp/modules/openidProvider \
    vendor/simplesamlphp/simplesamlphp/modules/preprodwarning \
    vendor/simplesamlphp/simplesamlphp/modules/radius \
    vendor/simplesamlphp/simplesamlphp/modules/saml2debug \
    vendor/simplesamlphp/simplesamlphp/modules/smartnameattribute \
    vendor/simplesamlphp/simplesamlphp/modules/statistics \
    vendor/simplesamlphp/simplesamlphp/modules/themefeidernd