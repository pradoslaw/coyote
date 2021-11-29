.PHONY: all update-repo file-permission composer-install migration seed assets-dev assets-production install-es install-passport

install: file-permission composer-install migration seed install-assets assets-production install-es install-passport install-push
install-dev: file-permission composer-install migration seed install-assets assets-dev install-es install-passport install-push
update: update-repo composer-install migration assets-production cache-config
update-dev: update-repo composer-install migration assets-dev

help:
	@echo 'make install -- download dependencies and install'
	@echo 'make install-dev -- download dependencies and install without minifing assets'
	@echo 'make update-dev -- pull repo and rebuild assets'
	@echo 'make update -- pull repo and rebuild assets without minifing'

up:
	docker-compose up -d

stop:
	docker-compose stop

up-ci:
	docker-compose -f docker-compose.yaml -f docker-compose.testing.yaml up -d

stop-ci:
	docker-compose -f docker-compose.yaml -f docker-compose.testing.yaml stop

php:
	docker-compose exec -u nginx php bash

update-repo:
	git reset --hard
	git pull origin master

composer-install:
	docker-compose exec -T -u nginx php composer install

file-permission:
	docker-compose exec -T -u nginx php chmod -R 777 storage/
	docker-compose exec -T -u nginx php chmod 777 bootstrap/cache/

migration:
	docker-compose exec -T -u nginx php php artisan migrate --force

seed:
	docker-compose exec -T -u nginx php php artisan db:seed

install-assets:
	docker-compose exec -T -u nginx php yarn install

assets-production:
	docker-compose exec -T -u nginx php yarn run prod

assets-dev:
	docker-compose exec -T -u nginx php yarn run dev

cache-config:
	docker-compose exec -T -u nginx php php artisan config:cache
	docker-compose exec -T -u nginx php php artisan route:cache

install-es:
	docker-compose exec -T -u nginx php php artisan es:create --force
	docker-compose exec -T -u nginx php php artisan es:index --force

install-passport:
	docker-compose exec -T -u nginx php php artisan passport:install

install-push:
	docker-compose exec -T -u nginx php php artisan webpush:vapid


