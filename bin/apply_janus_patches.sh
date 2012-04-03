#!/bin/sh

# Get janus dir
scriptPath=$(readlink -f "$0")
binDir=$(dirname $scriptPath)
serviceRegistryRootDir=$(dirname $binDir)
janusDir=${serviceRegistryRootDir}"/modules/janus/"

echo -e "\nCreating enable file"
touch ${janusDir}"enable"

echo -e "\nReverting versioned files"
git checkout HEAD ${janusDir}

echo -e "\nRemoving unversioned files"
svn status --no-ignore ${janusDir} | grep '^\?' | sed 's/^\?      //'
svn status --no-ignore ${janusDir} | grep '^\?' | sed 's/^\?      //'  | xargs rm -rf

echo -e "\nPatching files"
REJFILE="/tmp/janus_patches-rej-$(date +%s)"
touch $REJFILE
for FILENAME in janus_patches/*.patch
do
  patch -Np0 -r $REJFILE < $FILENAME
done
rm $REJFILE
echo "Removed $REJFILE"

echo -e "\nFinished"
