#!/usr/bin/env bash

set -euo pipefail

# === CONFIGURATION ===
PROJECT_NAME="ssa_website"
LOCAL_SOURCE_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && cd .. && pwd)"
DEPLOY_DIR="/var/www/${PROJECT_NAME}"
VHOST_CONF_NAME="${PROJECT_NAME}.conf"
VHOST_DEST="/etc/httpd/conf.d/${VHOST_CONF_NAME}"
DOMAIN_NAME="ssapays.local"
PUBLIC_DIR="${DEPLOY_DIR}/public"
DB_FILE_RELATIVE="data/database.sqlite"
APACHE_USER="apache"

echo "🔍 Vérification des prérequis..."
command -v httpd &> /dev/null || sudo dnf install -y httpd
command -v rsync &> /dev/null || sudo dnf install -y rsync

echo "📁 Déploiement du projet dans /var/www..."
sudo mkdir -p "$DEPLOY_DIR"

if [[ "$DEPLOY_DIR" == "$LOCAL_SOURCE_DIR" ]]; then
    echo "❌ Erreur : LOCAL_SOURCE_DIR et DEPLOY_DIR identiques. Abandon." >&2
    exit 1
fi
echo "🔐 Configuration des permissions..."
# Traverse pour Apache
sudo find "$DEPLOY_DIR" -type d -exec chmod 755 {} +
# Lecture pour les fichiers publics
sudo find "$DEPLOY_DIR" -type f -exec chmod 644 {} +

# Propriété Apache sur les fichiers modifiables
DB_PATH="$DEPLOY_DIR/$DB_FILE_RELATIVE"
if [ -f "$DB_PATH" ]; then
    echo "🎯 Configuration SQLite : $DB_PATH"
    sudo chown $APACHE_USER:$APACHE_USER "$DB_PATH"
    sudo chmod 640 "$DB_PATH"
    # SELinux : autoriser lecture/écriture
    sudo chcon -t httpd_sys_rw_content_t "$DB_PATH"

    # Nouveau : droits sur le dossier parent data/
    DATA_DIR="$(dirname "$DB_PATH")"
    sudo chown $APACHE_USER:$APACHE_USER "$DATA_DIR"
    sudo chmod 770 "$DATA_DIR"
    sudo chcon -t httpd_sys_rw_content_t "$DATA_DIR"
fi

echo "🔒 Vérification SELinux..."
if command -v getenforce &> /dev/null && [ "$(getenforce)" = "Enforcing" ]; then
    echo "✅ SELinux actif : configuration spécifique pour SQLite"
    sudo setsebool -P httpd_read_user_content on
fi

echo "🛠️ Création du VirtualHost..."
sudo tee "$VHOST_DEST" > /dev/null <<EOF
<VirtualHost *:80>
    ServerName $DOMAIN_NAME
    DocumentRoot $PUBLIC_DIR

    <Directory $PUBLIC_DIR>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog logs/${PROJECT_NAME}_error.log
    CustomLog logs/${PROJECT_NAME}_access.log combined
</VirtualHost>
EOF

echo "📌 Ajout dans /etc/hosts si nécessaire..."
if ! grep -q "$DOMAIN_NAME" /etc/hosts; then
    echo "127.0.0.1 $DOMAIN_NAME" | sudo tee -a /etc/hosts > /dev/null
fi

echo "🚀 Redémarrage Apache..."
sudo systemctl enable --now httpd
sudo systemctl reload httpd

echo "✅ Projet déployé : http://$DOMAIN_NAME"
