#!/bin/bash
#
# sync with libadmin and clear cache

VUFIND_BASE=/usr/local/vufind/httpd


BASEDIR=$(dirname $0)
INDEX="$BASEDIR/../public/index.php"
VUFIND_LOCAL_DIR="$BASEDIR/../local"

export VUFIND_LOCAL_MODULES=Swissbib
export VUFIND_LOCAL_DIR
#export APPLICATION_ENV=development

php $INDEX libadmin syncMapPortal $@

#please do not delete a directory with options -rf as root based on a relative directory! GH
