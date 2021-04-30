PROJECT_DIR := $(shell dirname $(realpath $(lastword $(MAKEFILE_LIST))))
USER_ID ?= $(shell id -u)
GROUP_ID ?= $(shell id -g)
DOCKER_COMPOSE?=docker-compose
CONSOLE=bin/console
PHPUNIT=bin/phpunit
PHP_CS_FIXER=vendor/bin/php-cs-fixer
EXEC_PHP=$(DOCKER_COMPOSE) exec -u ${USER_ID}:${GROUP_ID} php
EXEC_PHP_NOTTY=$(DOCKER_COMPOSE) exec -T -u ${USER_ID}:${GROUP_ID} php
EXEC_PHP_ROOT=$(DOCKER_COMPOSE) exec -u 0 php
EXEC_DB=$(DOCKER_COMPOSE) exec -T postgres
EXEC_COMPOSER=$(EXEC_PHP) composer
EXEC_CONSOLE=$(EXEC_PHP) $(CONSOLE)
EXEC_CONSOLE_NOTTY=$(EXEC_PHP_NOTTY) $(CONSOLE)

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
	$(EXEC_CONSOLE) server:dump

##
##Setups
##---------------------------------------------------------------------------
.PHONY: start stop up down tty tty-root clear
install:									## run the stack + init fixtures
	$(MAKE) up
	$(MAKE) stop c=supervisor
	$(MAKE) ci
	$(MAKE) -j2 db-reset db-reset-tests
	$(MAKE) fixtures
	$(MAKE) start c=supervisor
start:										## start stopped containers
	$(DOCKER_COMPOSE) start $(c)
stop:										## stop
	$(DOCKER_COMPOSE) stop $(c)
up: 										## launch all containers
	$(DOCKER_COMPOSE) up -d
down: 										## destroy all containers (without volumes)
	$(DOCKER_COMPOSE) down
tty: 										## get a shell
	$(EXEC_PHP) bash
tty-root:									## get a root shell
	$(EXEC_PHP_ROOT) bash
clear:										## remove all the cache and the logs
	-$(EXEC_PHP_ROOT) rm -rf var/cache/* var/log/* .php_cs.cache

##
##Databases
##---------------------------------------------------------------------------
.PHONY: db-migrate db-dump db-reset-tests fixtures
db-migrate:									## execute all database migrations
	$(EXEC_CONSOLE_NOTTY) doctrine:migrations:migrate -n
db-reset:									## recreate the database without data
	-$(EXEC_CONSOLE_NOTTY) doctrine:database:drop --if-exists --force
	$(EXEC_CONSOLE_NOTTY) doctrine:database:create
	$(MAKE) db-migrate
	$(EXEC_CONSOLE_NOTTY) messenger:setup-transports
db-reset-tests:									## recreate the database without data for testing
	-$(EXEC_CONSOLE_NOTTY) --env=test doctrine:database:drop --if-exists --force
	$(EXEC_CONSOLE_NOTTY) --env=test doctrine:database:create
	$(EXEC_CONSOLE_NOTTY) --env=test doctrine:migrations:migrate -n
	$(EXEC_CONSOLE_NOTTY) --env=test messenger:setup-transports
fixtures:									## executes all fixtures
	$(EXEC_CONSOLE_NOTTY) hautelook:fixtures:load -n

##
##QA
##---------------------------------------------------------------------------
.PHONY: qa tests phpunit-tests functional-tests phpcsfix lint-twig lint-yaml
qa: phpcsfix lint-twig lint-yaml tests									## launch tests + syntax checks

tests: unit-tests acceptance-tests integration-tests end2end-tests					## launch all tests
unit-tests:												## launch unit tests
	$(EXEC_PHP_NOTTY) $(PHPUNIT) --testsuite=unit $(c)
acceptance-tests:											## launch acceptance tests
	$(EXEC_PHP_NOTTY) $(PHPUNIT) --testsuite=acceptance $(c)
integration-tests:											## launch integration tests
	$(EXEC_PHP_NOTTY) $(PHPUNIT) --testsuite=integration $(c)
end2end-tests:												## launch functional tests
	$(EXEC_PHP_NOTTY) $(PHPUNIT) --testsuite=e2e $(c)

phpcsfix:												## fix syntax of all PHP sources
	$(EXEC_PHP_NOTTY) $(PHP_CS_FIXER) --allow-risky=yes fix
lint-twig:												## check syntax of templates
	$(EXEC_CONSOLE_NOTTY) lint:twig templates
lint-yaml:												## check syntax of yaml files
	$(EXEC_CONSOLE_NOTTY) lint:yaml --parse-tags *.yml fixtures/*.yaml

# Files generation
vendor: composer.lock
	$(MAKE) ci
composer.lock: composer.json
	$(MAKE) cu
