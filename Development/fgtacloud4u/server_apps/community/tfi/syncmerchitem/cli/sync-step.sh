#!/bin/bash

export ERR_BATCH=9999


source sync-cache-batchno.sh
source trycatch.sh


if [[ $KALISTACLI == "" ]]; then
	KALISTACLI="/var/www/html/fgta4/kalista/cli"
fi

if [[ $KALISTASYNMOD == "" ]]; then
	KALISTASYNMOD="community/tfi/syncmerchitem"
fi



echo "## SYNC CONFIGURATION #########################"
echo "  BATCHNO       $BATCHNO"
echo "  REGIONID      $REGIONID"
echo "  WEBPRICING    $WEBPRICING"
echo "  SYNCBATCHCMD  $SYNCBATCHCMD"
echo "  UPDATE INV TB $TBUPDATE"

echo ""
echo "## DATABASE CONFIGURATION #####################"
echo "  KALISTADB     $KALISTADB"
echo "  KALISTAFS     $KALISTAFS"
echo "  TFIWEBDB      $TFIWEBDB"
echo "  FRMDB         $FRMDB"

echo ""
echo "## DIRECTORIES ###############################"
echo "  TEMPIMAGELOCATION   $TEMPIMAGELOCATION";
echo "  TEMPDIR             $TEMPDIR";
echo "  IMAGELOCATION       $IMAGELOCATION"
echo "  TARGETIMAGELOCATION $TARGETIMAGELOCATION"
echo "  KALISTACLI          $KALISTACLI"
echo "  KALISTASYNMOD       $KALISTASYNMOD"

echo ""
echo "## WEBCONFIGURATIONS #########################"
echo "  KALISTAUSER         $KALISTAUSER"
echo "  TFIWEB_BASE_URL     $TFIWEB_BASE_URL"
echo "  TFIWEB_POSTAUTHOR   $TFIWEB_POSTAUTHOR"

echo ""
echo ""

echo "Executing Step $STEP"
if [[ $ASKCONFIRM -eq 1 ]]; then
	while true; do
		read -p "Do you want to continue ? [y/n] " yn
		case $yn in
			[Yy]* ) break;;
			[Nn]* ) echo "CANCELED by USER"; exit;;
			* ) echo "Please answer yes or no.";;
		esac
	done
fi

cli=$KALISTACLI
mod=$KALISTASYNMOD


try 
(
	if [[ $STEP -eq 1 ]]; then
		echo "get item TB, sync to kalista"
		php $cli $mod/01_sync_get_items_tb || throw $ERR_BATCH
		php $cli $mod/02_sync_kalista_mark_updating || throw $ERR_BATCH
		php $cli $mod/03_sync_put_item_to_kalista || throw $ERR_BATCH
		php $cli $mod/04_sync_kalista_set_updated || throw $ERR_BATCH


	elif [[ $STEP -eq 2 ]]; then
		echo "put image to kalista"
		php $cli $mod/05_sync_image_to_kalista || throw $ERR_BATCH


	elif [[ $STEP -eq 3 ]]; then
		echo "Sync Batchno to Web"
		if [[ $SYNCBATCHCMD != "" ]]; then
			echo $SYNCBATCHCMD
			result=$($SYNCBATCHCMD || throw $ERR_BATCH)
			echo $result
		else
			echo "perintah belum didefinisi di SYNCBATCHCMD"	
		fi

	elif [[ $STEP -eq 4 ]]; then
		echo "get item kalista, sync to web"
		php $cli $mod/06_sync_get_items_kalista || throw $ERR_BATCH
		php $cli $mod/07_sync_web_mark_updating || throw $ERR_BATCH 
		php $cli $mod/08_sync_put_items_web || throw $ERR_BATCH
		php $cli $mod/09_sync_web_set_updated || throw $ERR_BATCH

	else 
		echo "plase choose:"
		echo "1: get item TB, sync to kalista"
		echo "2: put image to kalista"
		echo "3: sync batchno to web"
		echo "4: get item kalista, sync to web"
		echo ""

	fi

)
catch || {
	echo "Batch Process ERROR."
}


