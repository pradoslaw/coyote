.PHONY: all update-repo dependency-install file-permission migration seed assets-dev assets-production install-es install-passport

install: dependency-install file-permission migration seed install-assets assets-production cache-config key-generate install-es install-passport
install-dev: dependency-install file-permission migration seed install-assets assets-dev key-generate install-es install-passport
update: update-repo dependency-install migration assets-production cache-config
update-dev: update-repo dependency-install migration assets-dev

help:
	@echo 'make install -- download dependencies and install'
	@echo 'make install-dev -- download dependencies and install without minifing assets'
	@echo 'make update-dev -- pull repo and rebuild assets'
	@echo 'make update -- pull repo and rebuild assets without minifing'

update-repo:
	git reset --hard
	git pull origin master

dependency-install:
	composer install

file-permission:
	chmod -R 775 storage/
	chmod 775 bootstrap/cache/

migration:
	php artisan migrate --force

seed:
	php artisan db:seed

install-assets:
	yarn install

assets-production:
	yarn run prod

assets-dev:
	yarn run dev

cache-config:
	php artisan config:cache
	php artisan route:cache

key-generate:
	php artisan key:generate

install-es:
	php artisan es:create --force
	php artisan es:index --force

install-passport:
	php artisan passport:install
