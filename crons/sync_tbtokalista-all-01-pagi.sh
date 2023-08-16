#!/bin/bash


# Syncronsisasi Find Kapoor Pagi, tanpa update inventory
./sync_tbtokalista-fkp.sh -n -f

# Update batchno ke Web
./sync_tbtokalista-fkp.sh -s 3 -f




