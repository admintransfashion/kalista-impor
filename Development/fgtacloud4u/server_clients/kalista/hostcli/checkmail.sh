#!/bin/bash

echo "checking email"
docker exec -it fgta4server php /var/www/html/fgta4/kalista/cli fgta/framework/msg/checkmail

