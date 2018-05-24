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

test: vendor cs phpunit
.PHONY: test

test-min: update-min cs phpunit
.PHONY: test-min

cs: vendor/bin/php-cs-fixer
	vendor/bin/php-cs-fixer --dry-run --allow-risky=yes --no-interaction --ansi fix
.PHONY: cs

cs-fix: vendor/bin/php-cs-fixer
	vendor/bin/php-cs-fixer --allow-risky=yes --no-interaction --ansi fix
.PHONY: cs-fix

phpunit: vendor/bin/phpunit
	vendor/bin/phpunit
.PHONY: phpunit

vendor: install

vendor/bin/phpunit: install

vendor/bin/php-cs-fixer:
	curl -Ls http://cs.sensiolabs.org/download/php-cs-fixer-v2.phar -o vendor/bin/php-cs-fixer && chmod +x vendor/bin/php-cs-fixer

