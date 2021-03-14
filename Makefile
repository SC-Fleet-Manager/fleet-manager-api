PROJECT_DIR := $(shell dirname $(realpath $(lastword $(MAKEFILE_LIST))))
UID := $(shell id -u)
GID := $(shell id -g)
DOCKER_COMPOSE?=docker-compose
CONSOLE=bin/console
PHPUNIT=bin/phpunit
PHP_CS_FIXER=vendor/bin/php-cs-fixer
EXEC_PHP=$(DOCKER_COMPOSE) exec -u ${UID}:${GID} php
EXEC_PHP_NOTTY=$(DOCKER_COMPOSE) exec -T -u ${UID}:${GID} php
EXEC_PHP_ROOT=$(DOCKER_COMPOSE) exec -u 0 php
EXEC_MYSQL=$(DOCKER_COMPOSE) exec -T mysql
EXEC_COMPOSER=$(EXEC_PHP) composer
EXEC_CONSOLE=$(EXEC_PHP) $(CONSOLE)

.PHONY: help
help:
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

##
##Utilities
##---------------------------------------------------------------------------
.PHONY: composer ci cu console cc
composer: 								## exec PHP composer with arbitrary args c="<args>"
	$(EXEC_COMPOSER) $(c)
ci:										## composer install
	$(EXEC_COMPOSER) install -o
cu:										## composer update
	$(EXEC_COMPOSER) update -o
console:								## exec SF console with arbitrary args c="<args>"
	$(EXEC_CONSOLE) $(c)
cc:										## clear and rebuild the cache
	$(EXEC_CONSOLE) cache:clear --no-warmup
	$(EXEC_CONSOLE) cache:warmup
server-dump:							## launch the dump server for test env
	$(EXEC_CONSOLE) server:dump -e test

##
##Setups
##---------------------------------------------------------------------------
.PHONY: install start stop up down tty tty-root clear clean
install: up clean						## launch containers and install dependencies
start:										## start stopped containers
	$(DOCKER_COMPOSE) start
stop:										## stop
	$(DOCKER_COMPOSE) stop
up: 										## launch all containers
	$(DOCKER_COMPOSE) up -d
down: 										## destroy all containers (without volumes)
	$(DOCKER_COMPOSE) down
tty: 										## get a shell
	$(EXEC_PHP) bash
tty-root:									## get a root shell
	$(EXEC_PHP_ROOT) bash

clear:										## remove all the cache, the logs, the sessions and the built assets
	-$(EXEC_PHP_ROOT) rm -rf var/cache/* var/sessions/* var/log/*
	-$(EXEC_PHP_ROOT) rm -rf .php_cs.cache npm-debug.log yarn-error.log
clean: clear								## clear and remove dependencies
	-$(EXEC_PHP_ROOT) rm -rf vendor

##
##Databases
##---------------------------------------------------------------------------
.PHONY: db-migrate db-dump db-reset-tests fixtures
db-migrate: vendor								## execute all database migrations
	$(EXEC_CONSOLE) doctrine:migrations:migrate -n
db-dump:									## create a database dump to ./dumps/
	$(EXEC_MYSQL) sh -c 'exec mysqldump -uroot -p"$${MYSQL_ROOT_PASSWORD}" "$${MYSQL_DATABASE}"' > ./dumps/dump-$(shell date -Iminutes).sql
db-reset: vendor								## recreate the database without data
	-$(EXEC_CONSOLE) doctrine:database:drop --if-exists --force
	$(EXEC_CONSOLE) doctrine:database:create
	$(MAKE) db-migrate
db-reset-tests: vendor								## recreate the database without data for testing
	-$(EXEC_CONSOLE) --env=test doctrine:database:drop --if-exists --force
	$(EXEC_CONSOLE) --env=test doctrine:database:create
	$(EXEC_CONSOLE) --env=test doctrine:migrations:migrate -n
fixtures: vendor								## executes all fixtures
	$(EXEC_CONSOLE) hautelook:fixtures:load -n

##
##QA
##---------------------------------------------------------------------------
.PHONY: qa tests phpunit-tests functional-tests end2end-tests phpcsfix lint-twig lint-yaml
qa: phpcsfix lint-twig lint-yaml tests					## launch tests + syntax checks

tests:													## reset db tests + launch all tests
	$(MAKE) db-reset-tests
	$(MAKE) para-tests
para-tests: unit-tests functional-tests end2end-tests	## launch all tests parallelisable
unit-tests:												## launch unit tests
	$(EXEC_PHP_NOTTY) $(PHPUNIT) --group=unit $(c)
functional-tests:										## launch functional tests
	$(EXEC_PHP_NOTTY) $(PHPUNIT) --group=functional $(c)
end2end-tests:											## launch end2end tests
	$(EXEC_PHP_NOTTY) $(PHPUNIT) --group=end2end $(c)

phpcsfix:												## fix syntax of all PHP sources
	$(EXEC_PHP) $(PHP_CS_FIXER) --allow-risky=yes fix
lint-twig:												## check syntax of templates
	$(EXEC_CONSOLE) lint:twig templates
lint-yaml:												## check syntax of yaml files
	$(EXEC_CONSOLE) lint:yaml --parse-tags *.yml fixtures/*.yaml

# Files generation
vendor: composer.lock
	$(MAKE) ci
composer.lock: composer.json
	$(MAKE) cu
