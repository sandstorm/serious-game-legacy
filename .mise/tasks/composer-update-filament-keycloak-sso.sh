 #!/usr/bin/env bash
 cd app

  composer remove heloufir/filament-keycloak-sso --no-scripts

  composer config repositories.filamentkeycloaksso composer https://filament-keycloak-sso.composer.sh

  echo -e "${YELLOW}Now, go to **Filament Advanced Tables TEAM Lizenz, Filament Keycloak TEAM Lizenz** in Bitwarden - enter Username + Password from notes KEYCLOAK if prompted (always same for all projects)${RESET}"
  composer require heloufir/filament-keycloak-sso --no-scripts
  composer show -- heloufir/filament-keycloak-sso > DistributionPackages/heloufir-filament-keycloak-sso.md

  rm -Rf DistributionPackages/heloufir-filament-keycloak-sso

  cp -R vendor/heloufir/filament-keycloak-sso DistributionPackages/heloufir-filament-keycloak-sso
  composer config --unset repositories.filamentkeycloaksso
  composer require heloufir/filament-keycloak-sso @dev --no-scripts
  composer update heloufir/filament-keycloak-sso
