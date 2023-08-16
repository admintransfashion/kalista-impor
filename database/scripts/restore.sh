#!/bin/bash


#echo "Restore DB di production harus dengan autorisasi"
#exit 0


INPUT=$1
if [[ $INPUT == "" ]]
then 

	echo "restore: nama database belum didefinisikan"
	echo "Perintah: ./restore.sh <databasename>"
	echo

else

	DATABASE=$INPUT
	DBWORKDIR=/var/database

	BACKUPDIR=$DBWORKDIR/backup
	ARCHIEVEDIR=$DBWORKDIR/backup


	# Cek data dari archieve
	cd $ARCHIEVEDIR
	for file in "$(ls -t $DATABASE-backup-*.tar.gz | head -1)"; do
	[[ $file -nt $latest ]] && latest=$file
	done


	if [[ $file == "" ]]
	then
		echo "File backup $DATABASE tidak ditemukan"
		exit 0
	fi	



	clear
	echo -e "\033[1;31m=========\033[0m"
	echo -e "\033[1;31mPERHATIAN\033[0m"
	echo -e "\033[1;31m=========\033[0m"
	echo
	echo -e "Data $DATABASE akan di restore dari"
	echo -e "$ARCHIEVEDIR/\033[1:33m$latest\033[0m"
	echo "Anda akan melakukan perintah beresiko tinggi terhadap data"
	echo -e "setelah eksekusi perintah, proses ini \033[1:37mtidak bisa dibatalkan\033[0m"
	read -p "Yakin [Y/N] ? " -n 1 -r

	if [[ $REPLY =~ ^[Yy]$ ]]
	then
		echo
		echo extract file backup ...
		ARCHIEVEPATH=$ARCHIEVEDIR/$latest

		tar -xvf $ARCHIEVEPATH -C $BACKUPDIR

		FILEINFO=$(basename -- "$latest")
		BACKUPFILENAME="${FILEINFO%%.*}.sql"
		BACKUPPATH=$BACKUPDIR/$BACKUPFILENAME

		INITCOMMAND="
			DROP DATABASE IF EXISTS $DATABASE;
			CREATE DATABASE $DATABASE CHARACTER SET latin1 COLLATE latin1_swedish_ci;
			USE $DATABASE;
			SET SESSION FOREIGN_KEY_CHECKS=0; 
		"
		echo
		echo "mulai restore database dari $BACKUPPATH ..."
		result=$(mysql -u root -p --init-command="$INITCOMMAND" $DATABASE < $BACKUPPATH 2>&1)
		rm -f $BACKUPPATH

		if [[ $result == "" ]]
		then
			echo
			echo restore data selesai
			echo
		else
			echo
			echo -e "\033[1;31mError Saat Restore Data\033[0m"
			echo $result
			echo
			echo "Jika ada referensi ke db yg salah, ada dapat melakukan replace dengan sed"
			echo "# sed -i 's/`xxxxxx`./`$DATABASE`./g' $BACKUPFILENAME"
			echo "mengganti prefix `xxxxxx`. dengan `$DATABASE`, di file $BACKUPFILENAME"
			echo
			echo
		fi


	else
		echo 
		echo restore di batalkan
		echo
	fi


	echo
	echo

fi
