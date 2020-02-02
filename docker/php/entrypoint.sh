#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'bin/console' ]; then
	# Permissions hack because setfacl does not work on Mac and Windows
	mkdir -p var
#	chown -R www-data var
fi

exec "$@"
