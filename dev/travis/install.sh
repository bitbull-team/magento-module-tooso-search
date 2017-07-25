#!/usr/bin/env bash

echo "Installing Magento and adding the module..."

cp ${TRAVIS_BUILD_DIR}/dev/travis/config/composer.json /var/www/html/

sudo sed -e "s?%TRAVIS_BUILD_DIR%?$(pwd)?g" --in-place /var/www/html/composer.json

composer require -d /var/www/html/ bitbull/magento-core ${MAGENTO_CE_VERSION}

composer require -d /var/www/html/ ${COMPOSER_MODULE_NAME}