# @todo Convert this to a php script that can use parts of the Janus make release tool

#!/bin/sh

ROOT_DIR=$(pwd)
SSP_VERSION="1.9.0"
JANUS_VERSION="1.17.4"
SSP_DIR="$ROOT_DIR/simplesamlphp"
SSP_MODULES_DIR="$SSP_DIR/modules"
JANUS_DIR="$SSP_MODULES_DIR/janus"

if [ ! -d simplesamlphp ]; then
    # @todo make version variable
    echo "installing SSP"
    tarFile="simplesamlphp-$SSP_VERSION.tar.gz"
    wget http://simplesamlphp.googlecode.com/files/$tarFile
    tar -xzf $tarFile
    rm $tarFile
    mv simplesamlphp-$SSP_VERSION simplesamlphp

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
fi

if [ ! -d $JANUS_DIR ]; then
    echo "cloning janus"
    git clone https://github.com/janus-ssp/janus.git $JANUS_DIR && \
    cd $JANUS_DIR && \
    git checkout $JANUS_VERSION
fi

# echo checking out branch/tag
cd $JANUS_DIR
# Note that this pulls the current branch so if you want to work on a feature branch of Janus, no problem!
git pull
composer install
rm -rf $JANUS_DIR/vendor/simplesamlphp
composer dump-autoload
rm -rf /tmp/janus/*
rm -rf /var/log/janus/*

# Applies various changes to simplesamlphp

cd $ROOT_DIR

# Add/override SimpleSamlPhp config
for FILENAME in config/*
do
    cd $SSP_DIR/config/
    ln -sf ../../$FILENAME
done

# Add/override SimpleSamlPhp metadata
for FILENAME in metadata/*
do
    cd $SSP_DIR/metadata/
    ln -sf ../../$FILENAME
done

# Set custom janus dictionaries
cd $JANUS_DIR/dictionaries

# Make a backup of the original metadatafields definitions
if [ ! -f metadatafields.definition.json.org ]; then
    cp metadatafields.definition.json metadatafields.definition.json.org
fi

# Reset the metadatafields definitions
cp metadatafields.definition.json.org metadatafields.definition.json

# Merge metadatafields definitions with overrides
cd $ROOT_DIR
php bin/mergeJsonFiles.php \
$JANUS_DIR/dictionaries/metadatafields.definition.json \
$ROOT_DIR/janus-dictionaries/metadatafields.definition.json

# Delete unused config, metadata and modules
cd $ROOT_DIR
rm -rf $SSP_DIR/config/acl.php   \
    $SSP_DIR/config/authmemcookie.php \
    $SSP_DIR/config/cas-ldap.php \
    $SSP_DIR/config/config-login-auto.php \
    $SSP_DIR/config/config-login-feide.php \
    $SSP_DIR/config/ldapmulti.php \
    $SSP_DIR/config/ldap.php \
    $SSP_DIR/config/translation.php \
    $SSP_DIR/metadata/adfs-idp-hosted.php \
    $SSP_DIR/metadata/adfs-sp-remote.php \
    $SSP_DIR/metadata/saml20-idp-hosted.php \
    $SSP_DIR/metadata/saml20-sp-remote.php \
    $SSP_DIR/metadata/shib13-idp-hosted.php \
    $SSP_DIR/metadata/shib13-idp-remote.php \
    $SSP_DIR/metadata/shib13-sp-hosted.php \
    $SSP_DIR/metadata/shib13-sp-remote.php \
    $SSP_DIR/metadata/wsfed-idp-remote.php \
    $SSP_DIR/metadata/wsfed-sp-hosted.php \
    $SSP_MODULES_DIR/adfs \
    $SSP_MODULES_DIR/aggregator \
    $SSP_MODULES_DIR/aggregator2 \
    $SSP_MODULES_DIR/aselect \
    $SSP_MODULES_DIR/authcrypt \
    $SSP_MODULES_DIR/authfacebook \
    $SSP_MODULES_DIR/authlinkedin \
    $SSP_MODULES_DIR/authmyspace \
    $SSP_MODULES_DIR/authtwitter \
    $SSP_MODULES_DIR/authwindowslive \
    $SSP_MODULES_DIR/authX509 \
    $SSP_MODULES_DIR/authYubiKey \
    $SSP_MODULES_DIR/autotest \
    $SSP_MODULES_DIR/cas \
    $SSP_MODULES_DIR/casserver \
    $SSP_MODULES_DIR/cdc \
    $SSP_MODULES_DIR/consentAdmin \
    $SSP_MODULES_DIR/consentSimpleAdmin \
    $SSP_MODULES_DIR/discopower \
    $SSP_MODULES_DIR/exampleattributeserver \
    $SSP_MODULES_DIR/exampleauth \
    $SSP_MODULES_DIR/expirycheck \
    $SSP_MODULES_DIR/InfoCard \
    $SSP_MODULES_DIR/logpeek \
    $SSP_MODULES_DIR/memcacheMonitor \
    $SSP_MODULES_DIR/metaedit \
    $SSP_MODULES_DIR/metarefresh \
    $SSP_MODULES_DIR/negotiate \
    $SSP_MODULES_DIR/oauth \
    $SSP_MODULES_DIR/openid \
    $SSP_MODULES_DIR/openidProvider \
    $SSP_MODULES_DIR/preprodwarning \
    $SSP_MODULES_DIR/radius \
    $SSP_MODULES_DIR/saml2debug \
    $SSP_MODULES_DIR/smartnameattribute \
    $SSP_MODULES_DIR/statistics \
    $SSP_MODULES_DIR/themefeidernd