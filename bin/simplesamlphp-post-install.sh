#!/bin/sh
# Applies various changes to simplesamlphp, this should be ran when composer has installed it in vendor

ROOT_DIR=$(pwd)

#!/bin/sh

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

# Add/override config
cp config/* vendor/simplesamlphp/simplesamlphp/config/

# Add/override metadata
cp metadata/* vendor/simplesamlphp/simplesamlphp/metadata/

# Workaround: move modules back to correct location, this happpens due to incorrect installation order (janus before ssp)
if [-d mv vendor/simplesamlphp/simplesamlphp/modules/modules/* vendor/simplesamlphp/simplesamlphp/modules/]; then
    mv vendor/simplesamlphp/simplesamlphp/modules/modules/* vendor/simplesamlphp/simplesamlphp/modules/
    rm -r vendor/simplesamlphp/simplesamlphp/modules/modules
fi

# Enable cron module
touch vendor/simplesamlphp/simplesamlphp/modules/cron/enable

# Delete unused config, metadata and modules
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
