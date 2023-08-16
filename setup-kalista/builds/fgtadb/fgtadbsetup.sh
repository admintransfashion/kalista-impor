#!/bin/bash
DATABASE="fgtadb"
BACKUP_DIR=$(pwd)

unset -v latest
currentlocation=$(pwd)
cd $BACKUP_DIR
for file in "$(ls -t $DATABASE-backup-*.sql | head -1)"; do
  [[ $file -nt $latest ]] && latest=$file
done

if [[ $file == "" ]]
then
	echo "File backup $DATABASE tidak ditemukan"
	exit 0
fi	


INITCOMMAND="CREATE DATABASE IF NOT EXISTS $DATABASE CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE $DATABASE;
SET SESSION FOREIGN_KEY_CHECKS=0; 
"


echo
echo -e "Data $DATABASE akan di restore dari"
echo -e "$BACKUP_DIR/\033[1:33m$latest\033[0m"

# don dangerous stuff
echo
echo mulai restore database ...

result=$(mysql -u root -p --init-command="$INITCOMMAND" < $BACKUP_DIR/$latest 2>&1)

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
	echo "# sed -i 's/`ossdb`./`fgtadb`./g' fgtadb-backup-2022-01-04_1532.sql"
	echo "mengganti prefix `ossdb`. dengan `fgtadb`, di file fgtadb-backup-2022-01-04_1532.sql"
	echo
	echo
fi

