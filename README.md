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

## Testowanie

W pisaniu testów, pomaga nam framework [Codeception](http://codeception.com/). Testy znajdują się w katalogu `tests`, który zawiera testy jednostkowe, funkcjonalne oraz akceptacyjne. Aby uruchomić testy trzeba wejść do katalogu z projektem i wykonać polecenia:

1. `vendor/bin/codecept build` (tylko jednorazowo)
2. `vendor/bin/codecept run`

## Aktualizacja projektu

`make update` (na produkcji) lub `make update-dev` (na serwerze deweloperskim)

### Konfiguracja supervisor

Supervisor jest narzędziem monitorującym procesy, działającym w środowisku Linux. W Laravel dostępny jest
mechanizm kolejkowania zadań (np. indeksowanie treści w Elasticsearch), który można uruchomić przy pomocy

`php artisan queue:listen --sleep=10`

Supervisor ma na celu automatyczne uruchamianie tego procesu po starcie systemu i pilnownie, aby zawsze był uruchomiony.
Konfigurację supervisor możesz znaleźć w pliku `supervisor.conf`. Więcej informacji: https://laravel.com/docs/5.2/queues

### Ustawienia crona

W przypadku ustawienia środowiska na `production` w pliku `.env`, konieczne będzie ustawienie crona aby wykonywać
pewne czynności cykliczne.

1. W konsoli wpisz `crontab -e`
2. Dodaj linię: `* * * * * php /var/www/path-to-app/artisan schedule:run >> /dev/null 2>&1`


## Jak możesz pomóc?

Zachęcamy do aktywnego udziału w rozwoju projektu. Zajrzyj na zakładkę *Issues* i zobacz jakie zadanie możesz zrealizować. Realizujemy tylko te zadania, które są zaakceptowane i przypisane do wersji 2.0.

1. Utwórz fork repozytorium
2. Wprowadź zmiany
3. Dodaj pull request
