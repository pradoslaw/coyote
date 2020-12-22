.PHONY: all update-repo dependency-install file-permission migration seed assets-dev assets-production install-es install-passport

install: dependency-install file-permission migration seed install-assets assets-production install-es install-passport
install-dev: dependency-install file-permission migration seed install-assets assets-dev install-es install-passport
update: update-repo dependency-install migration assets-production cache-config
update-dev: update-repo dependency-install migration assets-dev

up:
	docker-compose up -d

stop:
	docker-compose stop

up-ci:
	docker-compose -f docker-compose.yaml -f docker-compose-testing.yaml up -d

stop-ci:
 	docker-compose -f docker-compose.yaml -f docker-compose-testing.yaml stop

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

install-es:
	php artisan es:create --force
	php artisan es:index --force

install-passport:
	php artisan passport:install
