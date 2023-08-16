#!/bin/bash

echo "sending email"
docker exec -it fgta4server php /var/www/html/fgta4/kalista/cli fgta/framework/msg/sendmail




