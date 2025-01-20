#!/bin/bash
set -ex

mysql --user=root --password="$MYSQL_ROOT_PASSWORD" --execute="CREATE DATABASE IF NOT EXISTS $DB_DATABASE_TESTING;"
mysql --user=root --password="$MYSQL_ROOT_PASSWORD" --execute="GRANT ALL PRIVILEGES ON *.* TO '$MYSQL_USER'@'%';"
