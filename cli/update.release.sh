#!/bin/bash
#
# Update current project on the branch release from github repository.
# Clear cache

if [ "$UID"  -eq 0 ]; then

        echo "Good morning!"
        echo  "git repository update as root user not allowed!"
        echo "feel free to try it again"
        exit 1
fi


TIME=`date +%Y-%m-%d_%H.%M.%S`
LOG=cli/log/update.release.${TIME}.log

# Write message with timestamp into log file
function log {
	local TIMESTAMP=`date +%Y-%m-%d_%H.%M.%S`
	local MESSAGE="${TIMESTAMP}: $1"
	echo $MESSAGE >> $LOG
	echo "Log: ${MESSAGE}"
}

# Update branch release from git

log "start update VuFind release branch"

git stash
git checkout release
git pull origin release
git stash pop

log "finish update VuFind release branch"

# set access rights of local cache to full

log "set full access rights to cache"

chmod 777 ../local/cache

log "full access rights to cache set"

# Clear cache

#log "Clear local cache"

#rm -rf local/cache/*

log "Local cache not cleared - please use root account and script removeLocalCache.sh"