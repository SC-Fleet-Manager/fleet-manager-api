#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [[ "${1#-}" != "$1" ]]; then
	set -- php-fpm "$@"
fi

if [[ "$1" = 'php-fpm' ]] || [[ "$1" = 'bin/console' ]]; then
    # The first time volumes are mounted, the project needs to be recreated
    if [[ "$BUILD_ENV" != 'prod' ]]; then
        # Always try to reinstall deps when not in prod
        if [[ -x "$(command -v composer)" ]]; then
            composer install --prefer-dist --no-progress --no-suggest --no-interaction
        else
            >&2 echo "Error : composer is not installed."
            exit 1
        fi
    fi

	# Permissions hack because setfacl does not work on Mac and Windows
	chown -R www-data var
fi

exec "$@"
