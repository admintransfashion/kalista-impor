#!/bin/bash


INPUT=$1
if [[ $INPUT == "" ]]
then 


	echo "backup: nama database belum didefinisikan"
	echo "Perintah: ./backup.sh <databasename>"
	echo

else

	DATABASENAME=$INPUT
	DBWORKDIR=/var/database
	TIMESTAMP=$(date -d "+7 hour" "+%F_%H%M")

	BACKUPDIR=$DBWORKDIR/backup
	BACKUPFILENAME=$DATABASENAME-backup-$TIMESTAMP.sql
	BACKUPPATH=$BACKUPDIR/$BACKUPFILENAME

	ARCHIEVEDIR=$DBWORKDIR/backup
	ARCHIEVEFILENAME=$DATABASENAME-backup-$TIMESTAMP.tar.gz
	ARCHIEVEPATH=$ARCHIEVEDIR/$ARCHIEVEFILENAME

	echo "backup $DATABASENAME to $BACKUPPATH"
	mysqldump -u root -p --routines $DATABASENAME > $BACKUPPATH
	chmod 666 $BACKUPPATH

	# compres dan copy ke 
	cd $BACKUPDIR
	tar -czvf $ARCHIEVEPATH $BACKUPFILENAME 
	chmod 666 $ARCHIEVEPATH

	rm -f $BACKUPPATH

fi







