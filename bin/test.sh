#!/bin/bash

# Tests must be run within the container
if [ -f /.dockerenv ]; then
  vendor/bin/phpunit --colors=always
  exit 0
else
  docker exec -it php-app vendor/bin/phpunit --colors=always
  exit 0
fi
