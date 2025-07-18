read -p "Désactiver httpd_read_user_content (y/N)? " confirm
if [[ "$confirm" =~ ^[Yy]$ ]]; then
    sudo setsebool -P httpd_read_user_content off
fi
