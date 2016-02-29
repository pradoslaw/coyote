.PHONY: all update-repo dependency-install file-permission migration seed assets_dev assets_production

install: dependency-install dump-autoload file-permission migration seed install_gulp assets_production cache-config
install-dev: dependency-install dump-autoload file-permission migration seed install_gulp assets_dev
update: update-repo dump-autoload migration assets_production cache-config
update-dev: update-repo dump-autoload migration assets_dev

help:
	@echo 'make install -- download dependencies and install'
	@echo 'make install-dev -- download dependencies and install without minifing assets'
	@echo 'make update-dev -- pull repo and rebuild assets'
	@echo 'make update -- pull repo and rebuild assets without minifing'

update-repo:
	git reset --hard
	git pull origin master

dependency-install:
	composer update

file-permission:
	chmod -R 777 storage/
	chmod -R 777 public/storage/
	chmod 777 bootstrap/cache/

migration:
	php artisan migrate --force
	
seed:
	php artisan db:seed

install_gulp:
	npm install --g gulp
	npm install

assets_production:
	gulp --production

assets_dev:
	gulp
    
dump-autoload:
	php artisan clear-compiled
    
cache-config:
	php artisan config:clear
	php artisan config:cache
	php artisan optimize
