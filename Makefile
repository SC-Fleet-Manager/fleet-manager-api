PROJECT_DIR := $(shell dirname $(realpath $(lastword $(MAKEFILE_LIST))))
UID := $(shell id -u)
GID := $(shell id -g)
DOCKER_COMPOSE?=docker-compose
CONSOLE=bin/console
PHPUNIT=bin/phpunit
PHP_CS_FIXER=vendor/bin/php-cs-fixer
EXEC_PHP=$(DOCKER_COMPOSE) exec -u ${UID}:${GID} php
EXEC_PHP_ROOT=$(DOCKER_COMPOSE) exec php
EXEC_COMPOSER=$(EXEC_PHP) composer
EXEC_CONSOLE=$(EXEC_PHP) $(CONSOLE)
EXEC_YARN=docker container run --rm -it -u ${UID}:${GID} -v ${PROJECT_DIR}:/app -w /app node:10-alpine yarn

.PHONY: help
help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

##
##Utilities
##---------------------------------------------------------------------------
.PHONY: yarn yi yu composer ci cu console cc
yarn: 									## launch an ephemeral node container for executing yarn with arbitrary args c="<args>"
	$(EXEC_YARN) $(c)
yi:										## yarn install
	$(EXEC_YARN) install
yu:										## yarn upgrade
	$(EXEC_YARN) upgrade
composer: 								## exec PHP composer with arbitrary args c="<args>"
	$(EXEC_COMPOSER) $(c)
ci:										## composer install
	$(EXEC_COMPOSER) install --no-interaction
cu:										## composer update
	$(EXEC_COMPOSER) update --lock --no-scripts --no-interaction
console:								## exec SF console with arbitrary args c="<args>"
	$(EXEC_CONSOLE) $(c)
cc:										## clear and rebuild the cache
	$(EXEC_CONSOLE) cache:clear --no-warmup
	$(EXEC_CONSOLE) cache:warmup

##
##Setups
##---------------------------------------------------------------------------
.PHONY: install start stop up down tty tty-root clear clean deps assets
install: up clean deps						## launch containers and install dependencies
start:										## start stopped containers
	$(DOCKER_COMPOSE) start
stop:										## stop
	$(DOCKER_COMPOSE) stop
up: 										## launch all containers
	$(DOCKER_COMPOSE) up -d
down: 										## destroy all containers (without volumes)
	$(DOCKER_COMPOSE) down
tty: 										## get a shell
	$(EXEC_PHP) sh
tty-root:									## get a root shell
	$(EXEC_PHP_ROOT) sh

clear:										## remove all the cache, the logs, the sessions and the built assets
	-$(EXEC_PHP_ROOT) rm -rf var/cache/* var/sessions/* var/log/*
	-$(EXEC_PHP_ROOT) rm -rf .php_cs.cache npm-debug.log yarn-error.log
	-$(EXEC_PHP_ROOT) rm -rf public/build/
clean: clear								## clear and remove dependencies
	-$(EXEC_PHP_ROOT) rm -rf vendor node_modules

deps: vendor assets							## build dependencies
assets: public/build						## shortcut for building assets public/build

##
##Databases
##---------------------------------------------------------------------------
.PHONY: db-migrate db-reset db-reset-tests fixtures
db-migrate: vendor								## execute all database migrations
	$(EXEC_CONSOLE) doctrine:migrations:migrate -n
db-reset: vendor								## recreate the database without data
	-$(EXEC_CONSOLE) doctrine:database:drop --if-exists --force
	$(EXEC_CONSOLE) doctrine:database:create
	$(MAKE) db-migrate
db-reset-tests: vendor							## recreate the database without data for testing
	-$(EXEC_CONSOLE) --env=test doctrine:database:drop --if-exists --force
	$(EXEC_CONSOLE) --env=test doctrine:database:create
	$(EXEC_CONSOLE) --env=test doctrine:migrations:migrate -n
fixtures: vendor								## executes all fixtures
	$(EXEC_CONSOLE) hautelook:fixtures:load -n

##
##QA
##---------------------------------------------------------------------------
.PHONY: qa tests phpunit-tests phpcsfix lint-twig lint-yaml
qa: phpcsfix lint-twig lint-yaml tests			## launch tests + syntax checks

tests: phpunit-tests							## launch all tests
phpunit-tests: vendor							## launch unit + functional tests (PHPUnit /w Panther)
	$(EXEC_PHP) $(PHPUNIT)

phpcsfix: vendor								## fix syntax of all PHP sources
	$(EXEC_PHP) $(PHP_CS_FIXER) fix
lint-twig: vendor								## check syntax of templates
	$(EXEC_CONSOLE) lint:twig templates
lint-yaml: vendor								## check syntax of yaml files
	$(EXEC_CONSOLE) lint:yaml --parse-tags *.yml fixtures/*.yaml k8s/*.yaml

# Files generation
vendor: composer.lock
	$(MAKE) ci
composer.lock: composer.json
	$(MAKE) cu

node_modules: yarn.lock
	$(MAKE) yi
	@touch -c node_modules
yarn.lock: package.json
	$(MAKE) yu
public/build: node_modules
	$(EXEC_YARN) dev
