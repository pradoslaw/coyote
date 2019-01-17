.PHONY: all update-repo dependency-install file-permission migration seed assets-dev assets-production

install: dependency-install dump-autoload file-permission migration seed install-assets assets-production cache-config
install-dev: dependency-install dump-autoload file-permission migration seed install-assets assets-dev
install-vagrant: dependency-install dump-autoload file-permission migration seed
update: update-repo dependency-install dump-autoload migration assets-production cache-config
update-dev: update-repo dependency-install dump-autoload migration assets-dev

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
	chmod -R 777 storage/
	chmod 777 bootstrap/cache/
	chmod 777 public/build

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

dump-autoload:
	php artisan clear-compiled

cache-config:
	php artisan config:cache
	php artisan route:cache
