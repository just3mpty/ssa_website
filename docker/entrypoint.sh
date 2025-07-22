#!/bin/bash
set -e
chmod -R a+rX lib/
chmod -R a+rX templates/
chmod -R a+rX src/

# Init DB si absente
if [ ! -f /var/www/html/data/database.sqlite ]; then
    if [ -f /var/www/html/migrations/tables.sql ]; then
        echo "Création base SQLite…"
        sqlite3 /var/www/html/data/database.sqlite < /var/www/html/migrations/tables.sql
        chown www-data:www-data /var/www/html/data/database.sqlite
    fi
fi

# Démarre Apache au premier plan
exec apache2-foreground
