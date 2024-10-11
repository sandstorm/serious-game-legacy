# User and account migration from one Neos to another

During the relaunch of our sandstorm.de public website we wanted to enable everyone to just log in with their old accounts.

Luckily this is pretty straight forward in Neos. You just have to export the content of a few database tables and import
that into the target Neos database. That's it, everyone can log in again.

## 1. Export the content of the following tables
    - neos_flow_security_account
    - neos_neos_domain_model_user
    - neos_neos_domain_model_userpreferences
    - neos_party_domain_model_abstractparty
    - neos_party_domain_model_abstractparty_accounts_join
    - neos_party_domain_model_electronicaddress
    - neos_party_domain_model_person
    - neos_party_domain_model_person_electronicaddresses_join
    - neos_party_domain_model_personname

**Attention**: Do not add `DROP TABLE` syntax if you want to merge the data with already existing data on the target Neos

## 2. Import the resulting SQL
Just import it into the target database. You should disable foregin key checks temporarily, so the import finishes without
complaints.

Example for mariadb first run the QUERY

`SET FOREIGN_KEY_CHECKS=0;`

and after the import finished 

`SET FOREIGN_KEY_CHECKS=1;`
