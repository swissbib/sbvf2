#!/bin/sh

EPFL_DIR=/usr/local/vufind/epfl
EPFL_CONFIG=$EPFL_DIR/local/config/vufind
EPFL_CACHE=$EPFL_DIR/local/cache
EPFL_DOKU=$EPFL_DIR/sbDocumentation
EPFL_INSTALLATION=$EPFL_DOKU/installation
WEB_SERVER_CONF=/etc/apache2/conf.d

localuser=guenter
localgroup=guenter


#install local database

cd $EPFL_DOKU


echo "installing the local database"
read tonull

mysql --user=root --password=listefoo1 < sb.vufind2.schema.only.sql

echo "local database installed"

echo "change permissions on some directories"
read tonull

chmod 777 $EPFL_DIR/log
chmod 777 $EPFL_CACHE

echo "copy necessary configuration files"
read tonull


cp $EPFL_INSTALLATION/searchspecs.nofilter.yaml $EPFL_CONFIG/searchspecs.yaml
cp $EPFL_INSTALLATION/http-vufind.conf $EPFL_CONFIG/local
cp $EPFL_INSTALLATION/config.ini $EPFL_CONFIG

cd $EPFL_DIR

echo "fetch the standard institutions from http://admin.swissbib.ch/libadmintest"
read tonull


./cli/sync.ubuntu.sh -v


echo "configure the webserver and restart the webservice"
read tonull

cd $WEB_SERVER_CONF
unlink http-epfl-vufind.conf
ln -s $EPFL_DIR/local/http-vufind.conf http-epfl-vufind.conf

service apache2 restart


echo "change user:group application files"
read tonull


cd $EPFL_DIR
find $EPFL_DIR -name '*' | xargs chown $localuser:$localgroup



echo "finished - sart the application with http://localhost/epfl in the browser"





