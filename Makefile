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

cs: tools/php-cs-fixer
	PHP_CS_FIXER_IGNORE_ENV=1 tools/php-cs-fixer --dry-run --allow-risky=yes --no-interaction --ansi fix

cs-fix: tools/php-cs-fixer
	PHP_CS_FIXER_IGNORE_ENV=1 tools/php-cs-fixer --allow-risky=yes --no-interaction --ansi fix

phpunit: vendor
	vendor/bin/phpunit
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
	  composer config platform.php 8.1 && \
	  composer update --no-dev -o -a

	tools/box compile

	@rm -rf build/phar

package: build/zalas-phpunit-globals-extension.phar
.PHONY: package

vendor: install

vendor/bin/phpunit: install

tools: tools/php-cs-fixer tools/box
.PHONY: tools

tools/php-cs-fixer:
	curl -Ls http://cs.symfony.com/download/php-cs-fixer-v3.phar -o tools/php-cs-fixer && chmod +x tools/php-cs-fixer

tools/box:
	curl -Ls https://github.com/humbug/box/releases/download/4.2.0/box.phar -o tools/box && chmod +x tools/box

test-package-tools: tests/phar/tools/phpunit tests/phar/tools/phpunit.d/zalas-phpunit-globals-extension.phar
.PHONY: test-package-tools

tests/phar/tools/phpunit: vendor
	ln -sf $(CURDIR)/vendor/bin/phpunit tests/phar/tools/phpunit

tests/phar/tools/phpunit.d/zalas-phpunit-globals-extension.phar: build/zalas-phpunit-globals-extension.phar
	cp build/zalas-phpunit-globals-extension.phar tests/phar/tools/phpunit.d/zalas-phpunit-globals-extension.phar
