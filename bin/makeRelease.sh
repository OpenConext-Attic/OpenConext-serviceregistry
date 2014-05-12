# @todo Convert this to a php script that can use parts of the Janus make release tool

#!/bin/sh
RELEASE_DIR=${HOME}/Releases
GITHUB_USER=OpenConext
PROJECT_NAME=OpenConext-serviceregistry

if [ -z "$1" ]
then

cat << EOF
Please specify the tag or branch to make a release of.

Examples:

    sh makeRelease.sh 0.1.0
    sh makeRelease.sh master
    sh makeRelease.sh develop

If you want to GPG sign the release, you can specify the "sign" parameter, this will
invoke the gpg command line tool to sign it.

   sh makeRelease 0.1.0 sign

EOF
exit 1
else
    TAG=$1
fi

PROJECT_DIR_NAME=$(echo "${PROJECT_NAME}-${TAG}"| sed -e "s/\//-/g")
PROJECT_DIR=${RELEASE_DIR}/${PROJECT_DIR_NAME}
JANUS_DIR="${PROJECT_DIR}/simplesamlphp/modules/janus/"

# Create empty dir
mkdir -p ${RELEASE_DIR}
rm -rf ${PROJECT_DIR}

# clone the tag
cd ${RELEASE_DIR}
git clone -b ${TAG} https://github.com/${GITHUB_USER}/${PROJECT_NAME}.git ${PROJECT_DIR_NAME}

# run Composer
cd ${PROJECT_DIR}
composer install --no-dev
bin/install.sh

# Tag it
COMMITHASH=`git rev-parse HEAD`
echo "Tag: ${TAG}" > ${PROJECT_DIR}/RELEASE
echo "Commit: ${COMMITHASH}" >> ${PROJECT_DIR}/RELEASE

# remove files that are not required for production
rm -rf ${PROJECT_DIR}/.idea
rm -rf ${PROJECT_DIR}/.git
rm -f ${PROJECT_DIR}/.gitignore
rm -f ${PROJECT_DIR}/makeRelease.sh
rm -f ${PROJECT_DIR}/bin/mergeJsonFiles.php
rm -rf ${PROJECT_DIR}/janus-dictionaries
rm -rf ${PROJECT_DIR}/simplesamlphp_patches
rm -rf ${JANUS_DIR}/www/install

# create tarball
RELEASE_TARBALL_NAME=${PROJECT_DIR_NAME}.tar.gz
RELEASE_TARBALL_FILE=${RELEASE_DIR}/${RELEASE_TARBALL_NAME}
cd ${RELEASE_DIR}
tar -czf ${RELEASE_TARBALL_FILE} ${PROJECT_DIR_NAME}
