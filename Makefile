.PHONY: dev build env test

.DEFAULT_GOAL := build

dev:
	@mkdir -p env

env/composer: dev
	@scripts/install-composer.sh

env/php-cs-fixer: dev
	@wget https://cs.symfony.com/download/php-cs-fixer-v2.phar -O env/php-cs-fixer
	@chmod +x env/php-cs-fixer

env: env/composer env/php-cs-fixer
	env/composer install

test: test-unit lint

test-unit: env
	./vendor/bin/phpunit test

cs-fixer: env/php-cs-fixer
	env/php-cs-fixer fix --verbose --dry-run src

lint: vendor
	./vendor/bin/phplint

builddir:
	@mkdir -p build

clean:
	rm -rf build/

build: test builddir
	env/composer install -o
	rm -rf build/cep-bookings && mkdir -p build/cep-bookings
	cp cep-bookings.php bootstrap.php build/cep-bookings/
	cp -r src build/cep-bookings/
	cp -r static build/cep-bookings/
	cp -r vendor build/cep-bookings/
	cd build && tar -czvf cep-bookings.tar.gz cep-bookings

build-docker:
	scripts/build-docker.sh
