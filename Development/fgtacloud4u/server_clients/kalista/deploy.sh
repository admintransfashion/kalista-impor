#/bin/bash



cd $(pwd)/..
SOURCE_DIR=$(pwd)


cd $(pwd)/..
TARGET_DIR=$(pwd)/kalista


#sementara
# rm -rf $TARGET_DIR



echo "KALISTA DEPLOY"
echo "==============="
echo ""
echo "Target direcory: $TARGET_DIR"
if [ -d "$TARGET_DIR" ]
then
	# direktori sudah ada, biarkan
	echo "Directory already exists"
else
	# direktory var belum ada, buat
	echo "Preparing directory ... "
	mkdir "$TARGET_DIR"

	cd $TARGET_DIR
	ln -s $SOURCE_DIR/apps apps
	ln -s $SOURCE_DIR/core core
	ln -s $SOURCE_DIR/rootdir rootdir
	ln -s $SOURCE_DIR/index.html index.html

	mkdir public
	cd $TARGET_DIR/public
	ln -s $SOURCE_DIR/public/assets assets
	ln -s $SOURCE_DIR/public/cron cron
	ln -s $SOURCE_DIR/public/customprintedform customprintedform
	ln -s $SOURCE_DIR/public/images images
	ln -s $SOURCE_DIR/public/jslibs jslibs
	ln -s $SOURCE_DIR/public/manuals manuals
	ln -s $SOURCE_DIR/public/templates templates
	ln -s $SOURCE_DIR/public/favicon.ico favicon.ico
	ln -s $SOURCE_DIR/public/getcfs.php getcfs.php
	ln -s $SOURCE_DIR/public/getotp.php getotp.php
	ln -s $SOURCE_DIR/public/index.php index.php
	ln -s $SOURCE_DIR/public/info.php info.php
	ln -s $SOURCE_DIR/public/login-kalista.phtml login-kalista.phtml
	ln -s $SOURCE_DIR/public/style.css style.css
	ln -s $SOURCE_DIR/public/manifest.json manifest.json
	

	echo "setup htaccess ... "
	touch $TARGET_DIR/public/.htaccess
	echo "SetEnv FGTA_APP_NAME \"kalista\"" >> $TARGET_DIR/public/.htaccess
	echo "SetEnv FGTA_APP_TITLE \"Kalista\""  >> $TARGET_DIR/public/.htaccess
	echo "SetEnv FGTA_DBCONF_PATH \"$TARGET_DIR/public/dbconfig.php\""  >> $TARGET_DIR/public/.htaccess
	echo "SetEnv FGTA_LOCALDB_DIR \"$TARGET_DIR/public/data\""  >> $TARGET_DIR/public/.htaccess
	echo "#Mailer Format \"host:port:username:password:fromname:fromemail\"" >> $TARGET_DIR/public/.htaccess
	echo "SetEnv FGTA_MAILER \"mail.transfashionindonesia.com:587:agung:0necupofm1lk:AgungNugroho:agung@transfashionindonesia.com\"" >> $TARGET_DIR/public/.htaccess


	echo "copy dbconfig ... "
	cp $SOURCE_DIR/public/dbconfig.php $TARGET_DIR/public


	echo "preparing data directory ... "
	cd $TARGET_DIR/public
	mkdir data
	mkdir data/debug
	mkdir data/menus
	mkdir data/output
	mkdir data/progaccess
	mkdir data/settings
	mkdir data/userprofiles
	mkdir data/grouppriv



fi


#update menu data
echo "Updating local data ..."
cp $SOURCE_DIR/core/database/menus/* $TARGET_DIR/public/data/menus
cp $SOURCE_DIR/core/database/grouppriv/* $TARGET_DIR/public/data/grouppriv





echo ""
echo ""


