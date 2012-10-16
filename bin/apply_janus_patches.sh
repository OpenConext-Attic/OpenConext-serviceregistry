#!/bin/sh

echo -e "\nPatching files"
REJFILE="/tmp/janus_patches-rej-$(date +%s)"
touch $REJFILE
for FILENAME in janus_patches/*.patch
do
  patch -Np1 -r $REJFILE < $FILENAME
done
rm $REJFILE
echo "Removed $REJFILE"

echo -e "\nFinished"
