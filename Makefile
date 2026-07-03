.DEFAULT_GOAL := help
.PHONY: help setup dev db-up db-down db-restore db-dump db-reset db-status

MISE := mise exec --
DB := ./scripts/db.sh
FILE ?=

help: ## Show this help
	@grep -h -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-12s\033[0m %s\n", $$1, $$2}'

setup: ## Install PHP deps, start the local DB, and seed it from 127_0_0_1.sql
	$(MISE) composer install --working-dir=public_html/SAFEAPP
	$(DB) restore $(FILE)

dev: db-up ## Start the local DB and PHP's built-in server on 127.0.0.1:8000
	$(MISE) php -S 127.0.0.1:8000 -t public_html/SAFEAPP

db-up: ## Start the local MariaDB instance (installs/initializes on first run)
	$(DB) start

db-down: ## Stop the local MariaDB instance
	$(DB) stop

db-restore: ## Import a .sql file into the local DB (FILE=path, default 127_0_0_1.sql)
	$(DB) restore $(FILE)

db-dump: ## Snapshot the local DB to a .sql file (FILE=path, default dev-dump.sql)
	$(DB) dump $(FILE)

db-reset: ## Wipe the local DB and reseed from 127_0_0_1.sql (FILE=path to override)
	$(DB) reset $(FILE)

db-status: ## Show whether the local MariaDB instance is running
	$(DB) status
