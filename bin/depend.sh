#!/bin/sh

if [ ! -f bin/composer.phar ]
then
  curl -s https://getcomposer.org/installer | php -- --install-dir=bin
fi

bin/composer.phar $*
