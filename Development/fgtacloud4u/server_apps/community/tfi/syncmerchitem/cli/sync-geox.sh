#!/bin/bash

echo "Sync Geox"
cd /var/www/fgtacloud4u/server/apps/community/tfi/syncmerchitem/cli
source sync-config.sh


# get options (p) WEBPRICING (f) No Confirm Question (s) STEP
in_force=0
in_step=1
in_noupdate=0
while getopts p:f:n:s: flag
do
    case "${flag}" in
        p) in_pricing=${OPTARG};;
        f) in_force=1;;
		n) in_noupdate=1;;
        s) in_step=${OPTARG};;
    esac
done


export TBUPDATE=0
export SYNCBATCHCMD=""
export STEP=$in_step
export WEBPRICING=$in_pricing
export REGIONID="03400"
export IMAGELOCATION="/var/www/html/others/geximages"
export TARGETIMAGELOCATION="/var/www/html/others/webimages/gex"



# cek apakah sebelum update, di cli ada prompt tanya dulu
if [[ $in_force -eq 1 ]]; then
	export ASKCONFIRM=0
else
	export ASKCONFIRM=1
fi


if [[ $in_noupdate -eq 1 ]]; then
	export TBUPDATE=0
else
	export TBUPDATE=1
fi


# ambil batchno dari cache, apabila BATCHNO=="last" (untuk proses2 selanjutnya)
# lakukan sync sesuai step yang dipilih
source sync-step.sh


