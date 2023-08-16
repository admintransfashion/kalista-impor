#!/bin/bash


WORKINGDIR="/home/agung/Development/fgtacloud4u/server_clients/kalista"

if [[ $(date +%u) -eq 1 ]]; then
	# eksekusi program tiap hari senin
	echo "generate laporan setiap senin"

fi


echo "generate laporan harian"
cd $WORKINGDIR



echo "generate sales statistic"
php cli retail/slrpt/salesstat/generate




