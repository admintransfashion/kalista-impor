#!/bin/bash


# Syncronsisasi Find Kapoor Siang, update inventory dahulu
./sync_tbtokalista-fkp.sh -f

# Update batchno ke Web
./sync_tbtokalista-fkp.sh -s 3 -f




