#!/bin/bash
#
#


VUFIND_BASE=/usr/local/vufind
VUFIND_DEPLOY_LOG=$VUFIND_BASE/log
VUFIND_DEPLOY=$VUFIND_BASE/httpd

TIMESTAMP=`date +%Y%m%d%H%M%S`

LOGFILE=$VUFIND_DEPLOY_LOG/git.update.repository.$TIMESTAMP.log


TIMESTAMP=`date +%Y%m%d%H%M%S`	# seconds


HOST=sb-vf1.swissbib.unibas.ch


function setTimestamp()
{
    CURRENT_TIMESTAMP=`date +%Y%m%d%H%M%S`
}

printf "starting update process for VuFind $HOST at  <%s> ...\n" ${CURRENT_TIMESTAMP}  >> $LOGFILE


cd $VUFIND_DEPLOY

git checkout development


setTimestamp
printf "update process for VuFind $HOST finished at  <%s> ...\n" ${CURRENT_TIMESTAMP}  >> $LOGFILE










