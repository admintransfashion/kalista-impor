#!/bin/bash

################################################################################
# Syncronisasi data transbrowser ke kalista
# Find Kapoor

# Parameter
# -p NOMORPRICING  ==> set kode pricing khusus harga barand di web
# -f               ==> diproses langsung tanpa konfirmasi dahulu
# -n               ==> diproses tanpa melakukan update inventory di cache TB
# -s STEP          ==> step fase yang akan di proses
#                      STEP:
#                      1: get item TB, sync to kalista
#                      2: put image to kalista
#                      3: syn batchno to web
#                      4: get item kalista, sync to web (lakukan di server web)
#
################################################################################

in_force=""
in_step=""
in_noupdate=""
while getopts p:fns: flag
do
    case "${flag}" in
        p) in_pricing="-p ${OPTARG}";;
        f) in_force="-f 1";;
		n) in_noupdate="-n 1";;
        s) in_step="-s ${OPTARG}";;
    esac
done

command="/var/www/fgtacloud4u/server_apps/community/tfi/syncmerchitem/cli/sync-fkp.sh"
opt="$in_step $in_pricing $in_force $in_noupdate"

# Jalankan perintahnya
docker exec -it fgta4server bash $command $opt

