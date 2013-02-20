#!/bin/bash
#
#



VUFIND_BASE=/usr/local/vufind
VUFIND_DEPLOY=$VUFIND_BASE/httpd

VUFIND_DEPLOY_1TARGET=$VUFIND_BASE/httpd1target

VUFIND_DEPLOY_ARCHIVE=$VUFIND_BASE/deployed.archive
VUFIND_TEMP=/usr/local/vufind/tmp

TIMESTAMP=`date +%Y%m%d%H%M%S`	# seconds

singlehost=sb-vf1.swissbib.unibas.ch


function copyDirsToDeploy {

    echo "copy dirs to deploy"

    rm -rf $VUFIND_TEMP/*
    cp -r $VUFIND_DEPLOY/config $VUFIND_TEMP/
    cp -r $VUFIND_DEPLOY/data $VUFIND_TEMP/
    cp -r $VUFIND_DEPLOY/languages $VUFIND_TEMP/
    cp -r $VUFIND_DEPLOY/local $VUFIND_TEMP/
    cp -r $VUFIND_DEPLOY/module $VUFIND_TEMP/
    cp -r $VUFIND_DEPLOY/public $VUFIND_TEMP/
    cp -r $VUFIND_DEPLOY/vendor $VUFIND_TEMP/
    cp -r $VUFIND_DEPLOY/util $VUFIND_TEMP/
    cp -r $VUFIND_DEPLOY/tests $VUFIND_TEMP/
    cp -r $VUFIND_DEPLOY/themes $VUFIND_TEMP/


}

echo "reminder: delete local cache in VUFIND_DEPLOY/local/cache before start of the script "

copyDirsToDeploy
cd $VUFIND_TEMP

echo "creating tar - file to deploy"
tar cfz $VUFIND_DEPLOY_ARCHIVE/vufind.deploy.$TIMESTAMP.tar.gz ./*



cd $VUFIND_DEPLOY

for hosttoprocess in $singlehost
do

    echo "deploying $hosttoprocess..."
    ssh root@$hosttoprocess "mkdir $VUFIND_BASE/$TIMESTAMP; cd $VUFIND_DEPLOY; cp -rp * $VUFIND_BASE/$TIMESTAMP"
    ssh root@$hosttoprocess "chown vfsb:vf $VUFIND_BASE/$TIMESTAMP"
    ssh root@$hosttoprocess "cd $VUFIND_BASE; tar cfz  $TIMESTAMP.tar.gz --remove-files $TIMESTAMP; chown vfsb:vf $TIMESTAMP.tar.gz; cp -p $TIMESTAMP.tar.gz $VUFIND_DEPLOY_ARCHIVE; rm $TIMESTAMP.tar.gz"

    ssh root@$hosttoprocess "cd $VUFIND_DEPLOY; rm -rf * "

    scp   $VUFIND_DEPLOY_ARCHIVE/vufind.deploy.$TIMESTAMP.tar.gz  vfsb@$hosttoprocess:$VUFIND_DEPLOY

    echo "extracting vufind.deploy.$TIMESTAMP.tar.gz on $hosttoprocess"
    ssh vfsb@$hosttoprocess "cd $VUFIND_DEPLOY; tar xfz vufind.deploy.$TIMESTAMP.tar.gz; rm vufind.deploy.$TIMESTAMP.tar.gz"

    echo "changing permissions on $VUFIND_DEPLOY/local/config and $VUFIND_DEPLOY/local/cache"
    ssh root@$hosttoprocess "chmod 777 $VUFIND_DEPLOY/local/config $VUFIND_DEPLOY/local/cache"

    scp   $VUFIND_DEPLOY/sbDocumentation/gh.local/httpd-vufind.vfsb.conf  vfsb@$hosttoprocess:$VUFIND_DEPLOY/local/httpd-vufind.conf
    scp   $VUFIND_DEPLOY/sbDocumentation/gh.local/config.vfsb.ini  vfsb@$hosttoprocess:$VUFIND_DEPLOY/local/config/vufind/config.ini


    echo "now configure single target domain"
    ssh root@$hosttoprocess "cd $VUFIND_DEPLOY_1TARGET; rm -rf * "
    scp   $VUFIND_DEPLOY_ARCHIVE/vufind.deploy.$TIMESTAMP.tar.gz  vfsb@$hosttoprocess:$VUFIND_DEPLOY_1TARGET

    echo "extracting vufind.deploy.$TIMESTAMP.tar.gz on $hosttoprocess single target"
    ssh vfsb@$hosttoprocess "cd $VUFIND_DEPLOY_1TARGET; tar xfz vufind.deploy.$TIMESTAMP.tar.gz; rm vufind.deploy.$TIMESTAMP.tar.gz"
    echo "changing permissions on $VUFIND_DEPLOY_1TARGET/local/config and $VUFIND_DEPLOY_1TARGET/local/cache"
    ssh root@$hosttoprocess "chmod 777 $VUFIND_DEPLOY_1TARGET/local/config $VUFIND_DEPLOY_1TARGET/local/cache"

    scp   $VUFIND_DEPLOY/sbDocumentation/gh.local/httpd-vufind.vfsbsingle.conf  vfsb@$hosttoprocess:$VUFIND_DEPLOY_1TARGET/local/httpd-vufind.conf
    scp   $VUFIND_DEPLOY/sbDocumentation/gh.local/config.vfsbsingle.ini  vfsb@$hosttoprocess:$VUFIND_DEPLOY_1TARGET/local/config/vufind/config.ini



    echo "echo restart httpd"
    ssh root@$hosttoprocess "service httpd restart"

done
