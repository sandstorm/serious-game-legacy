#!/bin/bash
set -ex

mysql --password="$MYSQL_ROOT_PASSWORD" --execute="CREATE DATABASE IF NOT EXISTS $DB_DATABASE_SCRAMBLED;"
mysql --password="$MYSQL_ROOT_PASSWORD" --execute="GRANT ALL PRIVILEGES ON *.* TO '$MYSQL_USER'@'%';"
