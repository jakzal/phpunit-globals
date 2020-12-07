IS_PHP8:=$(shell php -r 'echo (int)version_compare(PHP_VERSION, "8.0", ">=");')

default: build

build: install test
.PHONY: build

install:
	composer install
.PHONY: install

update:
	composer update
.PHONY: update

update-min:
	composer update --prefer-stable --prefer-lowest
.PHONY: update-min

update-no-dev:
	composer update --prefer-stable --no-dev
.PHONY: update-no-dev

test: vendor cs phpunit
.PHONY: test

test-min: update-min cs phpunit
.PHONY: test-min

test-package: test-package-tools
	cd tests/phar && ./tools/phpunit
.PHONY: test-package

ifeq ($(IS_PHP8),1)
cs:
else
cs: tools/php-cs-fixer
	tools/php-cs-fixer --dry-run --allow-risky=yes --no-interaction --ansi fix
endif
.PHONY: cs

ifeq ($(IS_PHP8),1)
cs-fix:
else
cs-fix: tools/php-cs-fixer
	tools/php-cs-fixer --allow-risky=yes --no-interaction --ansi fix
endif
.PHONY: cs-fix

phpunit: tools/phpunit
	tools/phpunit
.PHONY: phpunit

clean:
	rm -rf build
	rm -rf vendor
	find tools -not -path '*/\.*' -type f -delete
	find tests/phar/tools -not -path '*/\.*' -type f -delete
.PHONY: clean

build/zalas-phpunit-globals-extension.phar: tools/box
	$(eval VERSION=$(shell git describe --abbrev=0 --tags 2> /dev/null | sed -e 's/^v//' || echo 'dev'))
	@rm -rf build/phar && mkdir -p build/phar

	cp -r src LICENSE composer.json build/phar
	sed -e 's/@@version@@/$(VERSION)/g' manifest.xml.in > build/phar/manifest.xml

	cd build/phar && \
	  composer remove phpunit/phpunit --no-update && \
	  composer config platform.php 7.3 && \
	  composer update --no-dev -o -a

	tools/box compile

	@rm -rf build/phar

package: build/zalas-phpunit-globals-extension.phar
.PHONY: package

vendor: install

vendor/bin/phpunit: install

tools: tools/php-cs-fixer tools/phpunit tools/box
.PHONY: tools

tools/phpunit: vendor/bin/phpunit
	ln -sf ../vendor/bin/phpunit tools/phpunit

tools/php-cs-fixer:
	curl -Ls http://cs.sensiolabs.org/download/php-cs-fixer-v2.phar -o tools/php-cs-fixer && chmod +x tools/php-cs-fixer

tools/box:
	curl -Ls https://github.com/humbug/box/releases/download/3.10.0/box.phar -o tools/box && chmod +x tools/box

test-package-tools: tests/phar/tools/phpunit tests/phar/tools/phpunit.d/zalas-phpunit-globals-extension.phar
.PHONY: test-package-tools

tests/phar/tools/phpunit:
	curl -Ls https://phar.phpunit.de/phpunit-9.4.4.phar -o tests/phar/tools/phpunit && chmod +x tests/phar/tools/phpunit

tests/phar/tools/phpunit.d/zalas-phpunit-globals-extension.phar: build/zalas-phpunit-globals-extension.phar
	cp build/zalas-phpunit-globals-extension.phar tests/phar/tools/phpunit.d/zalas-phpunit-globals-extension.phar
