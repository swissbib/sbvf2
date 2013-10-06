#!/bin/sh

EPFL_DIR=/usr/local/vufind/epfl
EPFL_CONFIG=$EPFL_DIR/local/config/vufind
EPFL_CACHE=$EPFL_DIR/local/cache
EPFL_DOKU=$EPFL_DIR/sbDocumentation
EPFL_INSTALLATION=$EPFL_DOKU/installation

#install local database

cd $EPFL_DOKU


echo "installing the local database" | read

mysql --user=root --password=listefoo1 < sb.vufind2.schema.only.sql


