#!/usr/bin/env bash

# disable xdebug and adjust memory limit
echo '' > ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/xdebug.ini
echo 'memory_limit = -1' >> ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/travis.ini
phpenv rehash;

# this is needed b/c in Debian systems the node binary is "nodejs", and this break localtunnel start script
ln -s /usr/bin/nodejs /usr/bin/node

# Download and launch localtunnel
npm install -g localtunnel

lt --port 80 > localtunnel.txt &

sleep 5

# Get random localtunnel generated url
EXTERNAL_URL=`egrep -o 'https?://[^ ]+' localtunnel.txt`

echo "The base url is $EXTERNAL_URL"