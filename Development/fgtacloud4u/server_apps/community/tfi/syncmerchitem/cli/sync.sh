#!/bin/bash

clear

echo "Sync FindKapoor"
cd /var/www/fgtacloud4u/server/apps/retail/tbsync/syncmerchitem/cli
source sync-config.sh

date=$(date '+%Y%m%d-%H%M')

export BATCHNO="last" #$date
export BATCHNO=$date
export REGIONID="03700"
export IMAGELOCATION="/var/www/html/others/fkpimages"
export TARGETIMAGELOCATION="/var/www/html/others/webimages/fkp"
export WEBPRICING=$1


source sync-cache-batchno.sh

 # ovveride no batch
 #export BATCHNO="20221218-1516"

#php /var/www/html/fgta4/kalista/cli retail/tbsync/syncmerchitem/01_sync_get_items_tb
#php /var/www/html/fgta4/kalista/cli retail/tbsync/syncmerchitem/02_sync_kalista_mark_updating
#php /var/www/html/fgta4/kalista/cli retail/tbsync/syncmerchitem/03_sync_put_item_to_kalista
#php /var/www/html/fgta4/kalista/cli retail/tbsync/syncmerchitem/04_sync_kalista_set_updated

#php /var/www/html/fgta4/kalista/cli retail/tbsync/syncmerchitem/05_sync_image_to_kalista

#php /var/www/html/fgta4/kalista/cli retail/tbsync/syncmerchitem/06_sync_get_items_kalista 
php /var/www/html/fgta4/kalista/cli retail/tbsync/syncmerchitem/07_sync_web_mark_updating 
php /var/www/html/fgta4/kalista/cli retail/tbsync/syncmerchitem/08_sync_put_items_web 
php /var/www/html/fgta4/kalista/cli retail/tbsync/syncmerchitem/09_sync_web_set_updated 






