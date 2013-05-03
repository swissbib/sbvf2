#!/bin/bash
#
# Update current project on the branch master from github repository.
# Clear cache

if [ "$UID"  -eq 0 ]; then

        echo "Good morning!"
        echo  "git repository update as root user not allowed!"
        echo "feel free to try it again"
        exit 1
fi


TIME=`date +%Y-%m-%d_%H.%M.%S`
LOG=cli/log/update.master.${TIME}.log

# Write message with timestamp into log file
function log {
	local TIMESTAMP=`date +%Y-%m-%d_%H.%M.%S`
	local MESSAGE="${TIMESTAMP}: $1"
	echo $MESSAGE >> $LOG
	echo "Log: ${MESSAGE}"
}

# Update branch master from git

log "start update VuFind master branch"

git stash
git checkout master
git pull origin master
git stash pop

log "finish update VuFind master branch"

# set access rights of local cache to full

log "set full access rights to cache"

chmod 777 ../local/cache

log "full access rights to cache set"

# Clear cache

#log "Clear local cache"

#rm -rf local/cache/*

log "Local cache not cleared - please use root account and script removeLocalCache.sh"