JANUS patches
===================

In this folder are the patches for JANUS <http://code.google.com/p/janus-ssp/>.
While both SimpleSAMLphp and the JANUS module (what the ServiceRegistry is all about) have been forked, we keep the JANUS module as clean as possible, 
this has been done for these reasons:
1. Automatic integration of patches.
2. (more important) clean separation of custom patches / hacks and JANUS, for easy upgrades and to avoid forking.

Unfortunately we do have to tweak JANUS for our needs, we do this using the UNIX diff and patch tools.


Guidelines for patches
----------------------
ALL patches MUST end with .patch

ALL patches MUST be made with the Git diff binding (git diff).

ALL patches MUST have a meaningful filename.

ALL patches MUST start with either:
- 'hack_' a change specific to the serviceregistry, will never be picked up by JANUS
- 'feature_' a change that adds a feature, may be picked up by JANUS eventually
- 'bug_' a change that fixes a bug that should be picked up by JANUS in the next version

ALL patches MUST be made from the root of the serviceregistry project, example:
  cd serviceregistry &&
  git diff modules/janus/ > janus_patches/$MEANINGFUL_PATCH_NAME.patch

ALL patches in janus_patches MUST be executable on the version of JANUS that is included.

Patches for future versions of JANUS (patches on trunk) CAN be stored in janus_patches/future/ but SHOULD be comitted by someone with commit rights.

OBSOLETE patches MUST be removed


Creating patches
-----------------

cd serviceregistry
git diff $FILE > janus_patches/$MEANINGFUL_PATCHNAME.patch


Applying patches
-----------------

cd serviceregistery
./bin/apply_janus_patches.sh
