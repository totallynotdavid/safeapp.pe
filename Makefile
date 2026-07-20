.DEFAULT_GOAL := help
.PHONY: help setup dev fmt fmt-check lint analyze test db-up db-down db-dump db-reset db-legacy db-status

MISE := mise exec --
DB := ./scripts/db.sh
FILE ?=

help: ## Show this help
	@grep -h -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-12s\033[0m %s\n", $$1, $$2}'

# .env has to exist before db-up: scripts/db.sh reads the DB name and
# credentials from it to create the database and its account.
setup: ## Install the Laravel app's deps, prepare .env, build a fresh dev DB
	$(MISE) composer install
	@[ -f .env ] || cp .env.example .env
	$(MISE) php artisan key:generate --ansi
	$(MAKE) db-up
	$(MISE) php artisan migrate --seed --ansi

dev: db-up ## Start the local DB and the Laravel dev server
	$(MISE) php artisan serve

fmt: ## Format the app's PHP code with Mago
	$(MISE) mago format

fmt-check: ## Check PHP formatting without modifying files (for CI)
	$(MISE) mago format --check

lint: ## Lint the app's PHP code with Mago
	$(MISE) mago lint

analyze: ## Run Mago's static analysis (type errors, bugs)
	$(MISE) mago analyze

test: ## Run the Laravel app's test suite
	$(MISE) php artisan test

db-up: ## Start the local MariaDB instance (installs/initializes on first run)
	$(DB) start

db-down: ## Stop the local MariaDB instance
	$(DB) stop

db-dump: ## Snapshot the local DB to a .sql file (FILE=path, default dev-dump.sql)
	$(DB) dump $(FILE)

db-reset: db-up ## Wipe the dev DB and rebuild it from migrations + seeders
	$(MISE) php artisan migrate:fresh --seed --ansi

db-legacy: db-up ## Restore a production export into the cutover source DB (FILE=path)
	$(DB) restore-legacy $(FILE)

db-status: ## Show whether the local MariaDB instance is running
	$(DB) status
