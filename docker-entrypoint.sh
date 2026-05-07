#!/bin/sh
set -e

if [ -n "$PORT" ] && [ "$PORT" != "80" ]; then
  sed -ri -e "s|Listen 80|Listen $PORT|g" /etc/apache2/ports.conf
  sed -ri -e "s|<VirtualHost \*:80>|<VirtualHost *:$PORT>|g" /etc/apache2/sites-available/*.conf
fi

exec apache2-foreground
