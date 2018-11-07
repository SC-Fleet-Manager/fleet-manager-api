PROJECT_DIR := $(shell dirname $(realpath $(lastword $(MAKEFILE_LIST))))
UID := $(shell id -u)
GID := $(shell id -g)

yarn:
	docker container run --rm -it -u ${UID}:${GID} -v ${PROJECT_DIR}:/app -w /app node:10-alpine yarn $(c)

composer:
	docker-compose exec -u ${UID}:${GID} php composer $(c)

console:
	docker-compose exec -u ${UID}:${GID} php bin/console $(c)
