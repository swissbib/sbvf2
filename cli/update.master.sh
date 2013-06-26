#!/bin/bash
#
# Update current project on the branch master from github repository.
# Clear cache
# Set access rights for httpd service
# Pass corresponding application directory on the command line (the directory you are usually in at the moment)

getopts ":d:" opt;
APP_DIR=$OPTARG
echo $APP_DIR

if [ "$APP_DIR" = "" ]; then

    echo "you have to pass an application directory on the command line"
    echo "example: > cli/update.development.sh -dhttpd"
    exit 1
fi

BASE_DIR=/usr/local/vufind/$APP_DIR
echo "$BASE_DIR"

if [ "$UID"  -ne 0 ]; then

        echo "you have to be root to use the git update script because cache will be cleared"
        exit 1
fi

cd $BASE_DIR


TIME=`date +%Y-%m-%d_%H.%M.%S`
LOG=cli/log/update.master.${TIME}.log

# Write message with timestamp into log file
function log {
	local TIMESTAMP=`date +%Y-%m-%d_%H.%M.%S`
	local MESSAGE="${TIMESTAMP}: $1"
	echo $MESSAGE >> $LOG
	echo "Log: ${MESSAGE}"
}

# Update from git

log "start update VuFind master branch"

su -c "git stash" vfsb >> $LOG 2>&1
su -c "git checkout master" vfsb >> $LOG 2>&1
su -c "git pull origin master" vfsb >> $LOG 2>&1
su -c "git stash pop" vfsb >> $LOG 2>&1

log "finish update VuFind development branch"

# set access rights of local cache to full

log "set full access rights to cache"


# Clear cache

log "Clear local cache"

rm -rf $BASE_DIR/local/cache/*


chmod 777 $BASE_DIR/local/cache/
chmod 777 $BASE_DIR/log/

log "full access rights to cache set"
log "full access rights to log directory set
log "restarting the httpd service..."

service httpd restart

log "httpd service restarted"

chown vfsb:vf $LOG

log "git update process for master branch has finished"

