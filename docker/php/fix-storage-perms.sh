#!/bin/sh
set -e

mkdir -p storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
