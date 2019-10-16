.PHONY: dev composer env test

.DEFAULT_GOAL := env

dev:
	@mkdir -p env

composer: dev
	@scripts/install-composer.sh

env: composer

vendor: composer
	env/composer install

test: test-unit lint

test-unit: vendor
	./vendor/bin/phpunit test


lint: vendor
	./vendor/bin/phplint

builddir:
	@mkdir -p build

clean:
	rm -rf build/

build: builddir
	mkdir -p build/cep-bookings
	cp cep-bookings.php bootstrap.php build/cep-bookings/
	cp -r src build/cep-bookings/
	cp -r static build/cep-bookings/
	cp -r vendor build/cep-bookings/
	cd build && tar -czvf cep-bookings.tar.gz cep-bookings

build-docker:
	scripts/build-docker.sh
