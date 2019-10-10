.PHONY: dev composer env test

.DEFAULT_GOAL := env

dev:
	@mkdir -p env

composer: dev
	@scripts/install-composer.sh

env: composer

vendor: composer
	env/composer install

test: vendor
	./vendor/phpunit/phpunit/phpunit test

builddir:
	@mkdir -p build

clean:
	rm -rf build/

build: builddir
	mkdir -p build/cep-venues-assets
	cp cep-venues-assets.php build/cep-venues-assets/
	cp -r src build/cep-venues-assets/
	cp -r static build/cep-venues-assets/
	cp -r vendor build/cep-venues-assets/
	cd build && tar -czvf cep-venues-assets.tar.gz cep-venues-assets

build-docker:
	scripts/build-docker.sh
