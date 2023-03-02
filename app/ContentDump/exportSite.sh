#!/usr/bin/env bash

# IMPORTANT: will run in Container
echo "Starting Export In Container"

tar fcz /app/ContentDump/Resources.tar.gz -C /app/Data/Persistent/Resources/ .

# First we create the structure of the database
mysqldump \
    --host=$DB_NEOS_HOST \
    --user=$DB_NEOS_USER \
    --password=$DB_NEOS_PASSWORD \
    --no-data \
    $DB_NEOS_DATABASE \
     > ./temp.sql

# Then we create insert statements except for the ignored tables. Some table data ignored here
# will be added further down, but it will be filtered.
mysqldump \
    --host=$DB_NEOS_HOST \
    --user=$DB_NEOS_USER \
    --password=$DB_NEOS_PASSWORD \
    $DB_NEOS_DATABASE \
    --no-create-info \
    --ignore-table="$DB_NEOS_DATABASE.neos_contentrepository_domain_model_workspace" \
    --ignore-table="$DB_NEOS_DATABASE.neos_contentrepository_domain_model_nodedata" \
    --ignore-table="$DB_NEOS_DATABASE.neos_flow_security_account" \
    --ignore-table="$DB_NEOS_DATABASE.neos_neos_domain_model_user" \
    --ignore-table="$DB_NEOS_DATABASE.neos_neos_domain_model_userpreferences" \
    --ignore-table="$DB_NEOS_DATABASE.neos_party_domain_model_abstractparty_accounts_join" \
    --ignore-table="$DB_NEOS_DATABASE.neos_party_domain_model_abstractparty" \
    --ignore-table="$DB_NEOS_DATABASE.neos_party_domain_model_electronicaddress" \
    --ignore-table="$DB_NEOS_DATABASE.neos_party_domain_model_person" \
    --ignore-table="$DB_NEOS_DATABASE.neos_party_domain_model_person_electronicaddresses_join" \
    --ignore-table="$DB_NEOS_DATABASE.neos_party_domain_model_personname" \
    --ignore-table="$DB_NEOS_DATABASE.fulltext_index" \
    --ignore-table="$DB_NEOS_DATABASE.fulltext_objects" \
     >> ./temp.sql

# Adding filtered data for previously ignored tables. For some strange reason we have to do it
# separately.
mysqldump \
    --host=$DB_NEOS_HOST \
    --user=$DB_NEOS_USER \
    --password=$DB_NEOS_PASSWORD \
    $DB_NEOS_DATABASE \
    neos_contentrepository_domain_model_workspace --where 'name="live"' \
    --no-create-info \
     >> ./temp.sql
mysqldump \
    --host=$DB_NEOS_HOST \
    --user=$DB_NEOS_USER \
    --password=$DB_NEOS_PASSWORD \
    $DB_NEOS_DATABASE \
    neos_contentrepository_domain_model_nodedata --where 'workspace="live"' \
    --no-create-info \
     >> ./temp.sql

gzip ./temp.sql
mv ./temp.sql.gz /app/ContentDump/Database.sql.gz
