#!/bin/sh
# @todo remove hardcoded user name

if [ -z "$1" ]
then

cat << EOF
Please specify the tag or branch to make a release of.

Examples:
    
    sh bin/deploy/deployToSurfconextTest.sh 0.1.0
    sh bin/deploy/deployToSurfconextTest.sh master
    sh bin/deploy/deployToSurfconextTest.sh develop
EOF
exit 1
else
    TAG=$1
fi

# Make a new release
./bin/makeRelease.sh ${TAG}

#@todo get these variables from makeRelease script
PROJECT_NAME=OpenConext-serviceregistry
RELEASE_DIR=${HOME}/Releases
PROJECT_DIR_NAME=$(echo "${PROJECT_NAME}-${TAG}"| sed -e "s/\//-/g")
PROJECT_DIR=${RELEASE_DIR}/${PROJECT_DIR_NAME}
RELEASE_TARBALL_NAME=${PROJECT_DIR_NAME}.tar.gz
RELEASE_TARBALL_FILE=${RELEASE_DIR}/${RELEASE_TARBALL_NAME}
TARGET_DIR_NAME="OpenConext-serviceregistry"

# Copy release to test server
scp ${RELEASE_TARBALL_FILE} lucas@surf-test:/opt/data/test/

# @todo add error handling
# Replace current version with new version and run migrations
ssh lucas@surf-test <<COMMANDS
    cd /opt/data/test

    # Unpack and remove tar
    tar -xzf ${RELEASE_TARBALL_NAME}
    rm ${RELEASE_TARBALL_NAME}
    rm -rf ${TARGET_DIR_NAME}

    # Copy unpacked tar
    mv ${PROJECT_DIR_NAME} ${TARGET_DIR_NAME}
    cd ${TARGET_DIR_NAME}/simplesamlphp/modules/janus/app/config
    ln -sf /etc/surfconext/serviceregistry.module_janus.parameters.yml parameters.yml

    cd ${TARGET_DIR_NAME}
    bin/migrate
COMMANDS
