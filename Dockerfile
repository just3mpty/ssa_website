FROM php:8.3-apache

# 1. Dépendances système pour pdo_sqlite
RUN apt-get update \
    && apt-get install -y --no-install-recommends libsqlite3-dev sqlite3 nano less ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# 2. Modules Apache
RUN a2enmod rewrite

# 3. Extensions PHP
RUN docker-php-ext-install pdo pdo_sqlite

# 4. Vhost Apache custom (optionnel)
COPY docker/vhost.conf /etc/apache2/sites-available/000-default.conf

# 5. Entrypoint (optionnel)
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
