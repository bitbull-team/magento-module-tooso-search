#!/usr/bin/env bash

set -e
trap '>&2 echo Error: Command \`$BASH_COMMAND\` on line $LINENO failed with exit code $?' ERR

# Create document root
sudo mkdir -p /var/www/html
sudo chown -R www-data:www-data /var/www/html

# disable xdebug and adjust memory limit
phpenv config-rm xdebug.ini
echo 'memory_limit = -1' >> ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/travis.ini
phpenv rehash

# Install apache
sudo apt-get update
sudo apt-get install apache2 libapache2-mod-fastcgi

# Enable php-fpm -- www.conf.default is PHP 7 only, so we dev/null any copy problems
sudo cp ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.d/www.conf.default ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.d/www.conf 2>/dev/null || true
sudo cp ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf.default ~/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf
sudo a2enmod rewrite actions fastcgi alias
echo "cgi.fix_pathinfo = 1" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
~/.phpenv/versions/$(phpenv version-name)/sbin/php-fpm

# Configure apache virtual hosts
sudo cp -f ${TRAVIS_BUILD_DIR}/dev/travis/config/apache_virtual_host /etc/apache2/sites-available/000-default.conf
#sudo sed -e "s?%TRAVIS_BUILD_DIR%?$(pwd)?g" --in-place /etc/apache2/sites-available/000-default.conf

sudo usermod -a -G www-data travis
sudo usermod -a -G travis www-data

sudo service apache2 restart

# Download and launch localtunnel
npm install -g localtunnel

lt --port 80 > localtunnel.txt &

sleep 5

# Get random localtunnel generated url
EXTERNAL_URL=`egrep -o 'https?://[^ ]+' localtunnel.txt`

echo "The base url is $EXTERNAL_URL"