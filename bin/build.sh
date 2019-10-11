#!/bin/sh

Fail() {
  echo "ERROR: $@" 1>&2
  exit 1
}

Run() {
  test -f "$1" || Fail "$1 missing"
  php "$@" || Fail "$1 failed"
}

which realpath >/dev/null || Fail "realpath not found"
which php      >/dev/null || Fail "php not found"

cd "$(realpath "$(dirname "$0")"/..)"

if which composer >/dev/null; then
  composer install --prefer-dist --no-interaction
  composer dumpautoload -o
else
  test -e "composer.phar" || php -r "readfile('https://getcomposer.org/installer');" | php
  php composer.phar install --prefer-dist --no-interaction
  php composer.phar dumpautoload -o
fi

php bin/console doctrine:schema:update --force

php bin/console cache:clear