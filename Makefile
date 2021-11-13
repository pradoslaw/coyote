.PHONY: all update-repo composer-install file-permission migration seed assets-dev assets-production install-es install-passport

install: composer-install file-permission migration seed install-assets assets-production install-es install-passport install-push
install-dev: composer-install file-permission migration seed install-assets assets-dev install-es install-passport install-push
update: update-repo composer-install migration assets-production cache-config
update-dev: update-repo composer-install migration assets-dev

up:
	docker-compose up -d

stop:
	docker-compose stop

up-ci:
	docker-compose -f docker-compose.yaml -f docker-compose.testing.yaml up -d

stop-ci:
	docker-compose -f docker-compose.yaml -f docker-compose.testing.yaml stop

php:
	docker-compose exec php bash

help:
	@echo 'make install -- download dependencies and install'
	@echo 'make install-dev -- download dependencies and install without minifing assets'
	@echo 'make update-dev -- pull repo and rebuild assets'
	@echo 'make update -- pull repo and rebuild assets without minifing'

update-repo:
	git reset --hard
	git pull origin master

composer-install:
	docker-compose exec -T php composer install

file-permission:
	chmod -R 777 storage/
	chmod 777 bootstrap/cache/

migration:
	docker-compose exec -T php php artisan migrate --force

seed:
	docker-compose exec -T php php artisan db:seed

install-assets:
	docker-compose exec -T php yarn install

assets-production:
	docker-compose exec -T php yarn run prod

assets-dev:
	docker-compose exec -T php yarn run dev

cache-config:
	docker-compose exec -T php php artisan config:cache
	docker-compose exec -T php php artisan route:cache

install-es:
	docker-compose exec -T php php artisan es:create --force
	docker-compose exec -T php php artisan es:index --force

install-passport:
	docker-compose exec -T php php artisan passport:install

install-push:
	docker-compose exec -T php php artisan webpush:vapid


