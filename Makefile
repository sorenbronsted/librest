.PHONY:	dist clean test checkout coverage depend update-depend

SHELL=/bin/bash

all: checkout depend clean coverage
	echo "Up-to-date"

clean:
	bin/clean.sh

test:
	bin/phpunit.phar test

checkout:
	git pull

coverage:
	bin/phpunit.phar --coverage-html doc/coverage test

depend:
	bin/depend.sh install

update-depend:
	bin/depend.sh update
