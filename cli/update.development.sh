#!/bin/bash
#
# Update current project on the branch development from github repository.
# Clear cache

BASE_DIR=/usr/local/vufind/httpd

if [ "$UID"  -ne 0 ]; then

        echo "you have to be root to use the git update script because cache will be cleared"
        exit 1
fi

cd $BASE_DIR


TIME=`date +%Y-%m-%d_%H.%M.%S`
LOG=cli/log/update.development.${TIME}.log

# Write message with timestamp into log file
function log {
	local TIMESTAMP=`date +%Y-%m-%d_%H.%M.%S`
	local MESSAGE="${TIMESTAMP}: $1"
	echo $MESSAGE >> $LOG
	echo "Log: ${MESSAGE}"
}

# Update from git

log "start update VuFind development branch"

su -c "git stash" vfsb >> $LOG 2>&1
su -c "git checkout development" vfsb >> $LOG 2>&1
su -c "git pull origin development" vfsb >> $LOG 2>&1
su -c "git stash pop" vfsb >> $LOG 2>&1

log "finish update VuFind release branch"

# set access rights of local cache to full

log "set full access rights to cache"


# Clear cache

log "Clear local cache"

rm -rf $BASE_DIR/local/cache/*


chmod 777 $BASE_DIR/local/cache/

log "full access rights to cache set"


log "restarting the httpd service..."
service httpd restart

chown vfsb:vf $LOG


log "git update process for development branch has finished"

