#!/bin/bash
#
#

VUFIND_BASE=/usr/local/vufind
VUFIND_DEPLOY_LOG=$VUFIND_BASE/log
VUFIND_DEPLOY=$VUFIND_BASE/httpd

TIMESTAMP=`date +%Y%m%d%H%M%S`

LOGFILE=$VUFIND_DEPLOY_LOG/remove.local.cache.$TIMESTAMP.log


TIMESTAMP=`date +%Y%m%d%H%M%S`	# seconds


CURRENTHOST=sb-vf1.swissbib.unibas.ch


function setTimestamp()
{
    CURRENT_TIMESTAMP=`date +%Y%m%d%H%M%S`
}

printf "starting process remove local cache on VuFind $CURRENTHOST at  $TIMESTAMP \n"  >> $LOGFILE


if [ "$UID"  -eq 0 ]; then


    printf "cache will be deleted ...\n"   >> $LOGFILE
    cd $VUFIND_DEPLOY/local/cache
    rm -rf *

    printf "now restart apache ...\n"   >> $LOGFILE

    service httpd restart



else
        printf "you have to be root to start this script ...\n"  >> $LOGFILE
        exit 1
fi


setTimestamp
printf "process remove local cache on VuFind $CURRENTHOST finished at  $CURRENT_TIMESTAMP ...\n"  >> $LOGFILE












