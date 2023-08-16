#/bin/bash

# directori setting
# menunjuk ke alamat absolute direktori
SOURCE_DIR="/home/agung/Development/fgtacloud4u"
SOURCE_CLIENT_DIR="/home/agung/Development/fgtacloud4u/server_clients/kalista"

TARGET_DIR="/home/agung/Development/fgtacloud4u/server_release_test/fgta_for_kalista"



echo "KALISTA RELEASE"
echo "==============="
echo ""
echo "Target direcory: $TARGET_DIR"
if [ -d "$TARGET_DIR" ]
then
	if [ "$(ls -A $TARGET_DIR)" ]; then
		echo "cleaning directory ..."
		cd $TARGET_DIR
		rm -r *
	fi
else
	mkdir "$TARGET_DIR"
fi


echo "Preparing target directory ... "
cd $TARGET_DIR
echo "Preparing core directory ... "
cp  -rf "$SOURCE_DIR/server/core" "$TARGET_DIR"
echo "Preparing public directory ... "
cp  -rf "$SOURCE_DIR/server/public" "$TARGET_DIR"


echo "Preparing required files ... "
cp  -r "$SOURCE_DIR/server/cli.php" "$TARGET_DIR"
cp  -r "$SOURCE_DIR/server/fgtacloud4u.inc.php" "$TARGET_DIR"
cp  -r "$SOURCE_DIR/server/genapp.js" "$TARGET_DIR"
cp  -r "$SOURCE_DIR/server/index.html" "$TARGET_DIR"
cp  -r "$SOURCE_DIR/server/LICENSE.md" "$TARGET_DIR"
cp  -r "$SOURCE_DIR/server/README.md" "$TARGET_DIR"
cp  -r "$SOURCE_DIR/server/minify.js" "$TARGET_DIR"
cp  -r "$SOURCE_DIR/server/package.json" "$TARGET_DIR"
cp  -r "$SOURCE_DIR/server/package-lock.json" "$TARGET_DIR"


mkdir apps
mkdir rootdir
mkdir rootdir/backupdb
mkdir public/data
mkdir public/data/debug
mkdir public/data/menus
mkdir public/data/output
mkdir public/data/progaccess
mkdir public/data/settings
mkdir public/data/userprofiles


echo "Copying rootdir ..."
ROOTDIR_EXCLUDE=("backupdb" "cek" "dokument" "mpctest" "pkey_ferragamo" "git.txt" "mpc.zip" "style.css") 
ROOTDIR_PATH="$SOURCE_DIR/server/rootdir"

for dir in $ROOTDIR_PATH/*; do
	prefix="$ROOTDIR_PATH/";
	entryname=${dir#"$prefix"}
	if [[ ! " ${ROOTDIR_EXCLUDE[*]} " =~ " ${entryname} " ]]; then
    	# whatever you want to do when array doesn't contain value
		echo "$dir"
		cp  -r $dir "$TARGET_DIR/rootdir"
	fi
done

# echo ""
# echo "Backup current data"
# mysqldump -u root -p --routines ossdb >  $TARGET_DIR/rootdir/backupdb/kalistadb-backup-$(date +%F).sql

echo ""
echo "Copy Default FGTA apps ..."
cp -rf $SOURCE_DIR/server/apps/fgta $TARGET_DIR/apps/

echo ""
echo "Deploy apps ..."
APPS_INCLUDE=("crm" "ent" "finact" "hrms" "pg" "retail" "media")
APPS_PATH="$SOURCE_DIR/server_apps"
for dir in $APPS_PATH/*; do
	prefix="$APPS_PATH/";
	entryname=${dir#"$prefix"}
	if [[ " ${APPS_INCLUDE[*]} " =~ " ${entryname} " ]]; then
    	# whatever you want to do when array doesn't contain value
		echo "$dir"
		cp  -r $dir "$TARGET_DIR/apps"
		rm -rf $TARGET_DIR/apps/$entryname/.git
		rm -rf $TARGET_DIR/apps/$entryname/.vscode
	fi
done


echo ""
echo "Deploy public Kalista ..."
PUBLIC_INCLUDE=("assets" "cron" "customprintedform" "dbconfig.php" "login-kalista.phtml" "manifest.json" "style.css" "deploy.sh")
for dir in $SOURCE_CLIENT_DIR/*; do
	prefix="$SOURCE_CLIENT_DIR/";
	entryname=${dir#"$prefix"}
	if [[ " ${PUBLIC_INCLUDE[*]} " =~ " ${entryname} " ]]; then
		echo "$dir"
		cp  -rf $dir "$TARGET_DIR/public"
	fi
done

echo ""
echo ""