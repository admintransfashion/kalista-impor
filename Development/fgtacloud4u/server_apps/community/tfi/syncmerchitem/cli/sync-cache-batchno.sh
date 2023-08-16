#!/bin/bash


# generate kode batchno
if [[ $STEP -eq 1 ]]; then
	date=$(date '+%Y%m%d-%H%M')
	export BATCHNO=$date
else
	export BATCHNO="last" #$date  # batchno bisa diisi dengan last
fi




lastbachnocache="/mnt/ramdisk/$REGIONID-lastbatchno.txt"
#lastBATCHNO = "$(cat $lastbachnocache)"

# Jika file cache batch belum ada, buat dulu
if ! test -f "$lastbachnocache" ; then
    echo "$lastbachnocache not exists, create new one"
	touch $lastbachnocache
fi


if [[ $BATCHNO == "last" ]]; then
	# ambil nilai dari /mnt/ramdisk/xxxxx-lastbatchno.txt
	echo "get batchno dari cache"
	export BATCHNO="$(cat $lastbachnocache)"
else 
	# simpan data batchno ke file /mnt/ramdisk/xxxxx-lastbatchno.txt
	echo $BATCHNO > $lastbachnocache
fi



#### load and ovveride config dari /home/kalista/crons/config
if [[ $ovverideConfig == "" ]]; then
	ovverideConfig="/home/kalista/crons/config/mercharticle-sync-config.sh"
fi

echo "cek $ovverideConfig"
if test -f $ovverideConfig ; then
	echo "ovveride config"
	source $ovverideConfig
fi


#export SYNCBATCHCMD="curl -s https://beta.transfashionindonesia.com/setbatchno.php?data=$BATCHNO@$REGIONID"
