#!/usr/bin/env bash

#MISE description="Initial project setup"
source "$MISE_PROJECT_ROOT/.mise/tasks/_colors.sh"

echo -e "${GREEN}Installing mise dependencies${RESET}"
mise install

echo -e "${GREEN}Setting up git lfs${RESET}"
git lfs install || true
echo -e "${YELLOW}Touch your Yubikey...${RESET}"
git lfs pull || true

# Adding folders -> when using `docker compose` instead of `docker-compose`
# folders must already be present
echo -e "${GREEN}Creating cache folders${RESET}"
mkdir -p ./tmp/composer_cache
mkdir -p ./tmp/.yarn-cache

echo -e "${GREEN}Running initial build${RESET}"
mise build

# Running composer to install dependencies locally so you have autocompletion
# in your IDE
pushd app
composer install --ignore-platform-reqs
popd

echo -e "${GREEN}Setup complete${RESET}"
