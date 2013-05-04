#!/bin/bash
#
#

VUFIND_CACHE=/usr/local/vufind/httpd/local/cache
#VUFIND_DEPLOY_LOG=$VUFIND_BASE/log
#VUFIND_DEPLOY=$VUFIND_BASE/httpd

TIMESTAMP=`date +%Y%m%d%H%M%S`

#LOGFILE=$VUFIND_DEPLOY_LOG/remove.local.cache.$TIMESTAMP.log


TIMESTAMP=`date +%Y%m%d%H%M%S`	# seconds


#CURRENTHOST=sb-vf1.swissbib.unibas.ch


function setTimestamp()
{
    CURRENT_TIMESTAMP=`date +%Y%m%d%H%M%S`
}



if [ "$UID"  -eq 0 ]; then


    echo "tryinig to remove local cache in $VUFIND_CACHE"
    rm -rf $VUFIND_CACHE/*

    echo "now restart apache ..."

    service httpd restart



else
        echo "you have to be root to start this script ..."
        exit 1
fi