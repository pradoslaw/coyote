# Coyote

[![StyleCI](https://styleci.io/repos/30256872/shield)](https://styleci.io/repos/30256872)
[![Build Status](https://travis-ci.org/adam-boduch/coyote.svg?branch=master)](https://travis-ci.org/adam-boduch/coyote)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/adam-boduch/coyote/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/adam-boduch/coyote/?branch=master)

Coyote to nazwa systemu obsługującego serwis 4programmers.net.

## Wymagania

* Docker >= 19.x
* docker-compose >= 1.29

## Instalacja

1. `cp .env.default .env`
2. `make up`
3. `make install-dev`

Strona jest dostępna w przeglądarce pod adresem: `localhost:8880`. Na użytkownika `admin` można zalogować się hasłem `123`.

## Zatrzymanie projektu

`make stop`

## Konfiguracja xdebug

W pliku `.env` należy dodać ustawienia xdebug:

```
ENABLE_XDEBUG=1
XDEBUG_HOST=
XDEBUG_PORT=
```

Nastepnie należy zrestartować usługę: `make stop && make up`.

## Praca z kodem CSS/JS

W projekcie korzystamy z yarn oraz webpack. Aby "skompilować" pliki źródłowe do postaci finalnej należy wykonać
polecenie `docker-compose exec php yarn run dev` lub `docker-compose exec php yarn run prod` (na produkcji).

Polecenie `docker-compose exec php yarn run watch` powoduje stałe monitorowanie zmian w plikach źródłowych. Jakiekolwiek zmiany w tych plikach
spowodują wygenerowanie nowych plików wynikowych CSS oraz JS.

## Testowanie

Aby uruchomić testy w laravel:

`docker-compose exec php php vendor/bin/phpunit`

## Jak zgłaszać błędy bezpieczeństwa?

W przypadku znalezienia błędów prosimy o zgłaszanie ich bezpośrednio na e-mail: support@4programmers.net

## Zadania uruchomiane w tle

Na serwerze produkcyjnym niektóre zadanie wykonywane są w tle. Dodawane są one do kolejki oraz wykonywane przez proces działający w tle.
Domyślnie, na serwerze lokalnym zadania nie są dodawane do kolejki (w pliku `.env` ustawienie `QUEUE_DRIVER=sync`).

Jeżeli jednak chciałbyś przetestować działanie mechanizmu kolejek, ustaw wartość zmiennej środowiskowej `QUEUE_DRIVER` na `redis`.

Aby uruchomić mechanizm kolejek skorzystaj z następującego polecenia:

`docker-compose exec php php artisan queue:listen --sleep=10`

## Jak możesz pomóc?

Zachęcamy do aktywnego udziału w rozwoju projektu. Zajrzyj na zakładkę *Issues* i zobacz jakie zadanie możesz zrealizować. Realizujemy tylko te zadania, które są zaakceptowane i przypisane do wersji 2.0.

1. Utwórz fork repozytorium
2. Wprowadź zmiany
3. Dodaj pull request
