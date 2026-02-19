##################
# Install
##################

export APP_ENV ?= dev

CURRENT_COMMAND := $(firstword $(MAKECMDGOALS))
ARGS := $(filter-out $(CURRENT_COMMAND), $(MAKECMDGOALS))

DOCKER_COMPOSE = docker compose -f ./docker/docker-compose.yaml
DOCKER_EXEC_APP = docker exec -it ${PROJECT_NAME}_php
DOCKER_EXEC_NGINX = docker exec -it ${PROJECT_NAME}_nginx
DOCKER_EXEC_REDIS = docker exec -it ${PROJECT_NAME}_redis
PHP_EXECUTOR=php
PHP_RUNNER=$(PHP_EXECUTOR)
DOCKER_EXEC_APP_PHP = $(DOCKER_EXEC_APP) bash -c
COMPOSER = ${DOCKER_EXEC_APP} composer
NPM = docker exec -it ${PROJECT_NAME}_nodejs npm
NPX = docker exec -it ${PROJECT_NAME}_nodejs npx
NODE = docker exec -it ${PROJECT_NAME}_nodejs node

ifeq ("$(wildcard ./docker/.env)","")
  $(info docker .env is not exist, trying create it)
  ifeq ($(OS),Windows_NT)
    $(shell copy docker\.env.dist docker\.env)
  else
    $(shell cp ./docker/.env.dist ./docker/.env)
  endif
endif

include ./docker/.env

ifeq ($(OS),Windows_NT)
  export $(shell powershell -NoProfile -Command "Get-Content docker/.env -Raw | Select-String '^[A-Za-z_][A-Za-z0-9_]*=' | ForEach-Object { 'export ' + ($_ -replace '^([^=]+)=(.*)$','$$1 := $$2').Trim() }")
else
  export $(shell sed 's/=.*//' ./docker/.env)
endif

ifeq ($(OS),Windows_NT)
  export USER_ID=1000
  export GROUP_ID=1000
  export LOCALHOST_IP_ADDRESS=$(shell powershell -NoProfile -Command "docker network inspect bridge --format '{{(index .IPAM.Config 0).Gateway}}'")
else
  export USER_ID=$(shell id -u)
  export GROUP_ID=$(shell id -g)
  export LOCALHOST_IP_ADDRESS=$(shell ip addr show | grep "\binet\b.*\bdocker0\b" | awk '{print $$2}' | cut -d '/' -f 1)
endif

ifndef LOCALHOST_IP_ADDRESS
  $(error It seems like docker is not started yet, because docker IP is empty, please try restart docker with: sudo systemctl restart docker)
endif

SYMFONY_CONSOLE = $(PHP_RUNNER) -dxdebug.client_host=$(LOCALHOST_IP_ADDRESS) -dxdebug.mode=debug -dxdebug.start_with_request=yes bin/console

export COMPOSE_PROJECT_NAME=${PROJECT_NAME}

help: ## Shows this help.
ifeq ($(OS),Windows_NT)
	@powershell -NoProfile -Command "Get-Content Makefile -Encoding UTF8 | ForEach-Object { if ($$_ -match '^[A-Za-z_-]+:') { $$n = ($$_ -split ':')[0]; if ($$_ -match '##\s*(.*)') { '{0,-25} {1}' -f $$n, $$Matches[1] } else { '{0,-25}' -f $$n } } elseif ($$_ -match '^##\s*(.*)') { $$Matches[1] } }"
else
	@grep -E '^([a-zA-Z_-]+:.*?##)|(^##)[^#]*$$' Makefile \
       | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-25s\033[0m %s\n", $$1, $$2}' \
       | sed -e 's/\[32m##\(.*\)$/\1/\[33m\n/g' \
       | sed -e 's/:.*\##//'
endif

install: pre-install down build-no-cache up post-install ## Create and start docker hub and install all requires for app. Should be used only once.

pre-install: ## Before-install routines
	@echo --- Pre-install ---
ifeq ($(OS),Windows_NT)
	@del /F /Q .env.local.php 2>NUL
else
	@rm -f ./.env.local.php
endif

post-install: composer-install composer-dump-env-dev migrate-up npm-install css-build codeception-build ## After-install routines

rebase: rebuild composer-install composer-dump-env-dev migrate-up npm-install css-build codeception-build sf-cc ## For use after merge pull-request to master from your colleagues.

## —— Docker compose ———————————————————————————————————————————————————————————————————————————————————————————————————
build: ##
	${DOCKER_COMPOSE} build

build-no-cache: ## Build containers without cache, e.g.: make build-no-cache / make build-no-cache nginx
	@${DOCKER_COMPOSE} build --no-cache

restart: down up ##

ifeq ($(OS),Windows_NT)
up:
	set APP_ENV=$(APP_ENV)
	${DOCKER_COMPOSE} up -d $(ARGS:=)
else
up:
	APP_ENV=$(APP_ENV) ${DOCKER_COMPOSE} up -d $(ARGS:=)
endif

ps: ##
	${DOCKER_COMPOSE} ps

logs: ##
	${DOCKER_COMPOSE} logs -f

down: ##
	${DOCKER_COMPOSE} down -v --remove-orphans $(ARGS:=)

down-hard: ## Stops all containers (even out of compose file)
	@docker ps -q | xargs -r docker stop

rebuild: down build up composer-clear-cache composer-dump-env-dev ##


## —— App ——————————————————————————————————————————————————————————————————————————————————————————————————————————————
bash: ##
	${DOCKER_EXEC_APP} bash

tunnel: ## Starts localtunnel session (lt required https://github.com/localtunnel/localtunnel)
	@lt --port $(NGINX_HOST_HTTP_PORT)


## —— Composer —————————————————————————————————————————————————————————————————————————————————————————————————————————
composer: ## Run composer, pass the parameter "c=" to run a given command, example: make composer c='req symfony/orm-pack'
	$(if $(c:=), , $(error Command is not set, example: "make composer c='require ***/***'"))
	@$(COMPOSER) $(c)

composer-install: ## Install composer to project
	@$(COMPOSER) install --ignore-platform-reqs --no-interaction --no-scripts

composer-version: ## Current version of composer
	@$(COMPOSER) --version

composer-clear-cache: ## Clear composer cache
	@$(COMPOSER) clear-cache

composer-dump-autoload: ## Update the composer autoloader because of new classes in a classmap package
	@$(COMPOSER) dump-autoload

composer-dump-env-dev: ## Recreate env php files
	@$(COMPOSER) dump-env dev

composer-dump-env-test: ## Recreate env php files
	@$(COMPOSER) dump-env test

composer-update: ## Update dependencies
	@$(COMPOSER) update

composer-update-full: ## Upgrades, downgrades and removals for packages currently locked to specific versions.
	@$(COMPOSER) update --with-all-dependencies

## —— Migrations ———————————————————————————————————————————————————————————————————————————————————————————————————————
migrate-up: ## Use additional option for upping certain migration, e.g.: make migrate-up 20230425182152'
	@$(eval version=$(if $(ARGS:=), 'DoctrineMigrations\Version$(ARGS)',))
	@$(eval env?=dev)
	@${DOCKER_EXEC_APP_PHP} "${SYMFONY_CONSOLE} doctrine:migrations:migrate --no-interaction --env=$(env) $(version)"

migrate-down: ## Use additional option for upping certain migration, e.g.: make migrate-down 20230425182152
	@$(eval version=$(if $(ARGS:=), 'DoctrineMigrations\Version$(ARGS)', $(error Migrations number is not set, example: make migrate-down 20230425182152)))
	@${DOCKER_EXEC_APP_PHP} "${SYMFONY_CONSOLE} doctrine:migrations:execute --down --no-interaction $(version)"

migration: ## Generate empty migration
	@${DOCKER_EXEC_APP_PHP} "${SYMFONY_CONSOLE} doctrine:migrations:generate"

migrate-drop: ## Drop database
	@$(eval env?=dev)
	@${DOCKER_EXEC_APP_PHP} "${SYMFONY_CONSOLE} doctrine:schema:drop --full-database --force --env=$(env)"

migrate-make: ## Generate migration if you have changed something in mapping (Entities)
	@${DOCKER_EXEC_APP_PHP} "${SYMFONY_CONSOLE} make:migration"

migrate-status: ## Show migrations status
	@${DOCKER_EXEC_APP_PHP} "${SYMFONY_CONSOLE} doctrine:migrations:status"

## —— Symfony ——————————————————————————————————————————————————————————————————————————————————————————————————————————
sf: ## List all Symfony commands or pass the parameter "c=" to run a given command, example: make sf c="app:logWriter 'NEXT ARG!!!'"
	@$(eval c?=)
	${DOCKER_EXEC_APP_PHP} "${SYMFONY_CONSOLE} $(c)"

sf-cc: ## Clear cache
	@${DOCKER_EXEC_APP_PHP} "${SYMFONY_CONSOLE} cache:clear"

sf-command: ## Create Symfony command, example: make sf-command app:logWriter
	$(if $(ARGS:=),,$(error command name is not set, example: 'make sf-command app:logWriter'))
	@${DOCKER_EXEC_APP_PHP} "${SYMFONY_CONSOLE} make:command $(ARGS)"

sf-entity: ## Create Symfony entity, example: make sf-entity Address
	$(if $(ARGS:=),,$(error Entity name is not set, example: "make sf-entity Address"))
	@${DOCKER_EXEC_APP_PHP} "${SYMFONY_CONSOLE} make:entity $(ARGS)"

sf-form: ## Create Symfony form
	@${DOCKER_EXEC_APP_PHP} "${SYMFONY_CONSOLE} make:form"

sf-assets-install: ## install`s/build`s assets from all required composer symfony bundles
	@${DOCKER_EXEC_APP_PHP} "${SYMFONY_CONSOLE} assets:install"

sf-messenger: ## Start messenger consumers, example: "make sf-messenger 'telemetry_sync_queue -vvv'"
	${DOCKER_EXEC_APP_PHP} "${SYMFONY_CONSOLE} messenger:consume $(ARGS) --memory-limit=256M --time-limit=1800 -vv"

sf-lint-containers: ## Run linters
	@${DOCKER_EXEC_APP_PHP} "${SYMFONY_CONSOLE} lint:container"

## —— CSS ——————————————————————————————————————————————————————————————————————————————————————————————————————————————
css-build: sf-assets-install npm-run-build-dev ##

## —— Tests ————————————————————————————————————————————————————————————————————————————————————————————————————————————
test-generate: ## e.g. mage test-generate Acceptance Auth
	${DOCKER_EXEC_APP_PHP} "vendor/bin/codecept generate:cest $(ARGS:=)"
test: ## Run all tests, or "make test Acceptance AuthCest", or "make test Acceptance AuthCest::openLoginPage"
ifeq ($(OS),Windows_NT)
	@make pre-test
	$(DOCKER_EXEC_APP_PHP) "vendor/bin/codecept run --steps $(ARGS)" || set STATUS=1
	@make post-test
	@if defined STATUS exit /b 1
else
	make pre-test; \
	${DOCKER_EXEC_APP_PHP} "vendor/bin/codecept run --steps $(ARGS:=)"; \
	STATUS=$$?; \
	make post-test; \
	exit $$STATUS
endif
pre-test: ## Pre-Tests routines
	make composer-dump-env-test
	@${DOCKER_EXEC_APP_PHP} "${SYMFONY_CONSOLE} doctrine:database:create --env=test --if-not-exists"
	make restart php APP_ENV=test
post-test: ## Post-Tests routines
	make composer-dump-env-dev
	make restart php
codeception-build: ## build codeception suites
	${DOCKER_EXEC_APP_PHP} "vendor/bin/codecept build"

## —— 🏳️‍🌈 NodeJS 🏳️‍🌈 ————————————————————————————————————————————————————————————————————————————
node: ## Run node, pass the parameter "c=" to run a given command, example: make node c='--version'
	$(if $(c:=), , $(error Command is not set, example: "make node c='--version'"))
	@$(NODE) $(c)
npm: ## Run npm, pass the parameter "c=" to run a given command, example: make npm c='ls stable'
	$(if $(c:=), , $(error Command is not set, example: "make npm c='ls stable'"))
	@$(NPM) $(c)
npx: ## Run npx, pass the parameter "c=" to run a given command, example: make npx c='npm-check-updates'
	$(if $(c:=), , $(error Command is not set, example: "make npx c='npm-check-updates'"))
	@$(NPX) $(c)
npm-install: ## Install nodeJs packages
	@$(NPM) install
npm-install-package: ## Install nodeJs package "n=" to set a package name, example: make npm-install-package n='core-js'
	$(if $(n:=), , $(error Command is not set, example: "make npm-install-package n='core-js'"))
	@$(NPM) i $(n)
npm-version: ## Current npm version
	@$(NPM) --version
npm-run-build-dev: ## Recreate frontend files (bigger size, human readable)
	@$(NPM) run build-dev
npm-run-build-dev-watch: ## Watch for difference is assets and builds frontend files if something changes
	@$(NPM) run build-dev-watch
npm-run-build-prod: ## Recreate frontend files for prod env (less size, not readable)
	@$(NPM) run build-prod
npm-cache-clean: ## Clean npm cache
	@$(NPM) cache clean
npm-cache-verify: ## Verify npm cache
	@$(NPM) cache verify

## —— 🍠 Redis 🍠 ——————————————————————————————————————————————————————————————————————————————————————————————————————
redis-flush-db-dev: ## flush dev cache databases
	@${DOCKER_EXEC_REDIS} redis-cli -n 0 FLUSHDB > /dev/null

ifeq ($(OS),Windows_NT)
.DEFAULT:
	@exit 0
else
.DEFAULT:
	@true
endif
