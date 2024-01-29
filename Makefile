install:     file-permission composer-install migration es-create seed es-index install-assets assets-production install-passport install-push
install-dev: file-permission composer-install migration es-create seed es-index install-assets assets-dev        install-passport install-push
update:      update-repo composer-install migration assets-production cache-config
update-dev:  update-repo composer-install migration assets-dev

up:
	docker-compose up -d

stop:
	docker-compose stop

up-ci:
	docker-compose -f docker-compose.yaml -f docker-compose.testing.yaml up -d

stop-ci:
	docker-compose -f docker-compose.yaml -f docker-compose.testing.yaml stop

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

es-create:
	docker-compose exec -T -u nginx php php artisan es:create --force

es-index:
	docker-compose exec -T -u nginx php php artisan es:index --force

install-passport:
	docker-compose exec -T -u nginx php php artisan passport:install

install-push:
	docker-compose exec -T -u nginx php php artisan webpush:vapid
