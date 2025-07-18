.PHONY: init-db run-local run-dev

init-db:
	@echo "Init db"
	bash ./init_db.sh

run-dev:
	@echo "Init db"
	bash ./boot_apache.sh

run-local:
	@echo "Initialise website"
	php -S localhost:8000

desac-apache:
	@echo "Init db"
	bash .desactived_read.sh
