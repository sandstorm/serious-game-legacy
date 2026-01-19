 #!/usr/bin/env bash
cd app

composer remove archilex/filament-filter-sets --no-scripts

composer config repositories.advancedtables composer https://filament-filter-sets.composer.sh
echo -e "${YELLOW}Now, go to **Filament Advanced Tables TEAM Lizenz** in Bitwarden and open the checkout.anystack.sh URL. Enter Username + Password (Project Specific)${RESET}"
composer require archilex/filament-filter-sets --no-scripts
composer show -- archilex/filament-filter-sets > DistributionPackages/archilex-filament-filter-sets.md

rm -Rf DistributionPackages/archilex-filament-filter-sets

cp -R vendor/archilex/filament-filter-sets DistributionPackages/archilex-filament-filter-sets
composer config --unset repositories.advancedtables
composer require archilex/filament-filter-sets @dev --no-scripts
