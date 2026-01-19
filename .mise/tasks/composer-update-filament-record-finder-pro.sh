 #!/usr/bin/env bash
cd app
composer remove ralphjsmit/laravel-filament-record-finder --no-scripts

composer config repositories.laravelrecordfinder composer https://satis.ralphjsmit.com
echo -e "${YELLOW}Now, go to **Filament Record Finder Pro TEAM Lizenz** in Bitwarden - enter Username + Password if prompted (always the same for all projects)${RESET}"
composer require ralphjsmit/laravel-filament-record-finder --no-scripts
composer show -- ralphjsmit/laravel-filament-record-finder > DistributionPackages/ralphjsmit-laravel-filament-record-finder.md
composer show -- ralphjsmit/packages > DistributionPackages/ralphjsmit-packages.md

rm -Rf DistributionPackages/ralphjsmit-laravel-filament-record-finder
rm -Rf DistributionPackages/ralphjsmit-packages

cp -R vendor/ralphjsmit/laravel-filament-record-finder DistributionPackages/ralphjsmit-laravel-filament-record-finder
cp -R vendor/ralphjsmit/packages DistributionPackages/ralphjsmit-packages
composer config --unset repositories.laravelrecordfinder
composer require ralphjsmit/packages "dev-main as 1.4.2" ralphjsmit/laravel-filament-record-finder @dev --no-scripts
