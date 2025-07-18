PROJECT_NAME := ssa_website
SRC_DIR := $(CURDIR)
DEST_DIR := /var/www/$(PROJECT_NAME)
BOOT_SCRIPT := $(DEST_DIR)/deploy_apache.sh

# Liste des fichiers ignorés
RSYNC_EXCLUDES := --exclude '.git/' \
                  --exclude 'node_modules/' \
                  --exclude '*.sqlite*~' \
                  --exclude '*.swp' \
                  --exclude '*.log' \
                  --exclude '.DS_Store'

.PHONY: all deploy run-dev init-db 

all: deploy

## 📦 Copie le projet vers /var/www/
deploy:
	@echo "📦 Synchronisation du projet vers $(DEST_DIR)..."
	sudo mkdir -p $(DEST_DIR)
	sudo rsync -av --delete $(RSYNC_EXCLUDES) $(SRC_DIR)/ $(DEST_DIR)/

	@echo "🔑 Rend le script exécutable..."
	@if [ -f $(BOOT_SCRIPT) ]; then sudo chmod +x $(BOOT_SCRIPT); fi

	@echo "🚀 Configuration Apache..."
	@if [ -f $(BOOT_SCRIPT) ]; then sudo $(BOOT_SCRIPT); fi


## 👁️ Surveille les changements avec inotifywait et redeploy automatiquement
run-dev:
	@echo "👀 Surveillance active (Ctrl+C pour quitter)..."
	@command -v inotifywait >/dev/null || (echo "⛔ inotify-tools requis. Install avec : sudo dnf install inotify-tools" && exit 1)
	while inotifywait -r -e modify,create,delete $(SRC_DIR); do \
		$(MAKE) deploy; \
	done

## 🧪 Init DB locale
deploy-db:
	@echo "🧪 Init base SQLite..."
	@bash ./init_db.sh
