# Coyote

[![StyleCI](https://styleci.io/repos/30256872/shield)](https://styleci.io/repos/30256872)
[![Build Status](https://travis-ci.org/adam-boduch/coyote.svg?branch=master)](https://travis-ci.org/adam-boduch/coyote)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/adam-boduch/coyote/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/adam-boduch/coyote/?branch=master)

Coyote to nazwa systemu obsługującego serwis 4programmers.net.

## Wymagania

* Docker
* docker-compose

## Instalacja

1. `cp .env.default .env`
6. `sudo docker-compose build`
7. `sudo docker-compose up -d`
8. `sudo docker-compose exec php make install-dev`

Strona jest dostępna w przeglądarce pod adresem: `localhost:8880`

## Konfiguracja xdebug

W pliku `.env` należy dodać ustawienia xdebug:

```
ENABLE_XDEBUG=1
XDEBUG_HOST=
XDEBUG_PORT=
```

## Praca z kodem CSS/JS

W projekcie korzystamy z yarn oraz webpack. Aby "skompilować" pliki źródłowe do postaci finalnej należy wykonać
polecenie `sudo docker-compose exec php yarn run dev` lub `sudo docker-compose exec php yarn run prod` (na produkcji).

Polecenie `sudo docker-compose exec php yarn run watch` powoduje stałe monitorowanie zmian w plikach źródłowych. Jakiekolwiek zmiany w tych plikach
spowodują wygenerowanie nowych plików wynikowych CSS oraz JS.

## Testowanie

Testy pisane są w dwóch frameworkach: codeception (legacy code) oraz laravel.

Aby uruchomić testy napisane w codeception należy wykonać polecenie:

`docker-compose exec php php vendor/bin/codecept run`

Aby uruchomić testy w laravel:

`docker-compose exec php php vendor/bin/phpunit`

## Zadania uruchomiane w tle

Na serwerze produkcyjnym niektóre zadanie wykonywane są w tle. Dodawane są one do kolejki oraz wykonywane przez proces działający w tle.
Domyślnie, na serwerze lokalnym zadania nie są dodawane do kolejki (w pliku `.env` ustawienie `QUEUE_DRIVER=sync`).

Jeżeli jednak chciałbyś przetestować działanie mechanizmu kolejek, ustaw wartość zmiennej środowiskowej `QUEUE_DRIVER` na `redis`.

Aby uruchomić mechanizm kolejek skorzystaj z następującego polecenia:

`sudo docker-compose exec php php artisan queue:listen --sleep=10`

## Ustawienia crona

W przypadku ustawienia środowiska na `production` w pliku `.env`, konieczne będzie ustawienie crona aby wykonywać
pewne czynności cykliczne.

1. W konsoli wpisz `crontab -e`
2. Dodaj linię: `* * * * * php /var/www/path-to-app/artisan schedule:run >> /dev/null 2>&1`


## Jak możesz pomóc?

Zachęcamy do aktywnego udziału w rozwoju projektu. Zajrzyj na zakładkę *Issues* i zobacz jakie zadanie możesz zrealizować. Realizujemy tylko te zadania, które są zaakceptowane i przypisane do wersji 2.0.

1. Utwórz fork repozytorium
2. Wprowadź zmiany
3. Dodaj pull request
