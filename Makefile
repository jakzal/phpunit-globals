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

cs: tools/php-cs-fixer
	tools/php-cs-fixer --dry-run --allow-risky=yes --no-interaction --ansi fix
.PHONY: cs

cs-fix: tools/php-cs-fixer
	tools/php-cs-fixer --allow-risky=yes --no-interaction --ansi fix
.PHONY: cs-fix

phpunit: tools/phpunit
	tools/phpunit
.PHONY: phpunit

clean:
	rm -rf build
.PHONY: clean

package: tools/box
	$(eval VERSION=$(shell git describe --abbrev=0 --tags 2> /dev/null | sed -e 's/^v//' || echo 'dev'))
	@rm -rf build/phar && mkdir -p build/phar

	cp -r src LICENSE composer.json build/phar
	sed -e 's/@@version@@/$(VERSION)/g' manifest.xml.in > build/phar/manifest.xml

	cd build/phar && \
	  composer remove phpunit/phpunit --no-update && \
	  composer config platform.php 7.1 && \
	  composer update --no-dev -o -a

	tools/box compile

	@rm -rf build/phar
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
	curl -Ls https://github.com/humbug/box/releases/download/3.0.0-beta.0/box.phar -o tools/box && chmod +x tools/box
