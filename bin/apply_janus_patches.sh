#!/bin/sh

echo -e "\nPatching files\n"
REJFILE="/tmp/janus_patches-rej-$(date +%s)"
touch $REJFILE
for FILENAME in janus_patches/*.patch
do
  echo "- Executing patch: $FILENAME\n"
  patch -Np1 -r $REJFILE < $FILENAME
  echo "\n"
done
rm $REJFILE
echo "Removed $REJFILE"

echo -e "\nFinished"
