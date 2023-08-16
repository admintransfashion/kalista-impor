Panduan CRONTAB

# menampilkan isi crontab
  $ sudo crontab -l

# edit isi crontab
  $ sudo crontab -e


# Struktur
  * * * * * command(s)
  - - - - -
  | | | | |
  | | | | ----- Hari dalam satu minggu (0 - 7) (Minggu=0 atau 7)
  | | | ------- Bulan (1 - 12)
  | | --------- Tanggal (1 - 31)
  | ----------- Jam (0 - 23)
  ------------- Menit (0 - 59)


##########
# Contoh #
##########
## generate data sales setiap hari setiap jam 6:30
30 6 * * * /home/kalista/fgtacloud4u/server_clients/kalista/cron/dailysales.sh



