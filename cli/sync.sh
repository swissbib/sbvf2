BASEDIR=$(dirname $0)
INDEX="$BASEDIR/../public/index.php"

export VUFIND_LOCAL_MODULES=Swissbib
php $INDEX libadmin sync $@