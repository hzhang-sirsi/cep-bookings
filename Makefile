.PHONY: dev clean build test test-unit cs-fixer lint buildir build-docker phpstan

.DEFAULT_GOAL := build

env:
	@mkdir -p env

env/composer: env
	@scripts/install-composer.sh

env/php-cs-fixer: env
	@wget https://cs.symfony.com/download/php-cs-fixer-v2.phar -O env/php-cs-fixer && touch env/php-cs-fixer
	@chmod +x env/php-cs-fixer

env/wp: env
	@rm -rf env/wp
	@wget https://wordpress.org/latest.tar.gz -O env/wp-latest.tar.gz
	@cd env && tar -xzvf wp-latest.tar.gz && mv wordpress wp && touch wp

dev: env/composer
	env/composer install

test: test-unit lint

test-unit: dev
	./vendor/bin/phpunit test

cs-fixer: dev env/php-cs-fixer
	env/php-cs-fixer fix --verbose --dry-run src

phpstan: dev env/wp
	./vendor/bin/phpstan analyze src -l 1

lint: vendor
	./vendor/bin/phplint

builddir:
	@mkdir -p build

clean:
	rm -rf build/

build: test builddir
	rm -rf build/cep-bookings && mkdir -p build/cep-bookings
	cp composer.json composer.lock cep-bookings.php bootstrap.php build/cep-bookings/
	cp -r src build/cep-bookings/
	cp -r static build/cep-bookings/
	env/composer install --no-dev -o --working-dir=build/cep-bookings/
	rm build/cep-bookings/composer.json build/cep-bookings/composer.lock
	cd build && tar -czvf cep-bookings.tar.gz cep-bookings

build-docker:
	scripts/build-docker.sh
