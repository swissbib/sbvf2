#!/bin/bash
#
# Update current project from github repository.
# Clear cache

if [ "$UID"  -eq 0 ]; then

        echo "Good morning!"
        echo  "git repository update as root user not allowed!"
        echo "feel free to try it again"
        exit 1
fi


TIME=`date +%Y-%m-%d_%H.%M.%S`
LOG=cli/log/update.${TIME}.log

# Write message with timestamp into log file
function log {
	local TIMESTAMP=`date +%Y-%m-%d_%H.%M.%S`
	local MESSAGE="${TIMESTAMP}: $1"
	echo $MESSAGE >> $LOG
	echo "Log: ${MESSAGE}"
}

# Update from git

log "start update VuFind"

git stash
git checkout development
git pull origin development
git stash pop

log "finish update VuFind"




# Clear cache

#log "Clear local cache"

#rm -rf local/cache/*

log "Local cache not cleared - please use root account"