#!/usr/bin/env bash

booting_apache_ssa() {
    local CONF_NAME="ssa-web.conf"
    local VHOST_DIR="/etc/httpd/conf.d"

    echo "🔍 Vérification du système..."
    if ! command -v dnf &> /dev/null || ! command -v systemctl &> /dev/null; then
        echo "❌ Ce script requiert Rocky Linux (ou un dérivé RHEL) avec systemd + dnf."
        exit 1
    fi

    echo "🔧 Installation de Apache (httpd) si nécessaire..."
    if ! command -v httpd &> /dev/null; then
        sudo dnf install -y httpd
    fi

    echo "🚀 Activation et démarrage du service Apache..."
    sudo systemctl enable --now httpd

    # 🚫 Supprime la page d'accueil par défaut de Rocky (welcome page)
    if [ -f /etc/httpd/conf.d/welcome.conf ]; then
        echo "⚠️ Suppression du welcome.conf Apache par défaut (Rocky)"
        sudo mv /etc/httpd/conf.d/welcome.conf /etc/httpd/conf.d/welcome.conf.disabled
    fi

    # 🔍 Chemin projet
    PROJECT_PATH="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)/public"
    if [ ! -d "$PROJECT_PATH" ]; then
        echo "❌ Projet introuvable à $PROJECT_PATH"
        exit 1
    fi

    # 🔐 Permissions (lecture + traverse)
    echo "🔐 Configuration des permissions d’accès pour Apache..."
    path="$PROJECT_PATH"
    while [ "$path" != "/" ]; do
        sudo chmod o+x "$path"
        path="$(dirname "$path")"
    done
    sudo chmod -R o+rX "$PROJECT_PATH"

    # 🔐 SELinux : autoriser Apache à lire le contenu utilisateur
    if command -v getenforce &> /dev/null && [ "$(getenforce)" = "Enforcing" ]; then
        echo "🔒 SELinux détecté : activation de httpd_read_user_content"
        sudo setsebool -P httpd_read_user_content on
    fi

    # 🛠️ Création du VirtualHost
    echo "🛠️ Génération de la configuration Apache..."
    cat > "/tmp/$CONF_NAME" <<EOF
<VirtualHost *:80>
    ServerName ssapays.local
    DocumentRoot ${PROJECT_PATH}
    <Directory ${PROJECT_PATH}>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
EOF

    sudo cp "/tmp/$CONF_NAME" "$VHOST_DIR/$CONF_NAME"
    sudo systemctl reload httpd

    # 🧭 Ajout dans /etc/hosts
    if ! grep -q "ssapays.local" /etc/hosts; then
        echo "127.0.0.1 ssapays.local" | sudo tee -a /etc/hosts > /dev/null
        echo "📌 Entrée ajoutée dans /etc/hosts"
    fi

    echo "✅ VirtualHost accessible via : http://ssapays.local"
}

booting_apache_ssa
