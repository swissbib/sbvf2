#!/bin/bash

CACHE_DIR=/usr/local/vufind/httpd/local/cache




BASEDIR=$(dirname $0)
INDEX="$BASEDIR/../public/index.php"
VUFIND_LOCAL_DIR="$BASEDIR/../local"

export VUFIND_LOCAL_MODULES=Swissbib
export VUFIND_LOCAL_DIR
#export APPLICATION_ENV=development

php $INDEX libadmin sync $@

#please do not delete a directory with options -rf as root based on a relative directory! GH


