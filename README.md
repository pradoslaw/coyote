# Coyote

[![StyleCI](https://styleci.io/repos/30256872/shield)](https://styleci.io/repos/30256872)
[![Build Status](https://travis-ci.org/adam-boduch/coyote.svg?branch=master)](https://travis-ci.org/adam-boduch/coyote)

Coyote to nazwa systemu obslugujacego serwis 4programmers.net. Obecnie w obludze jest wersja 1.x ktora mamy nadzieje zastapic wersja 2.0 ktora jest w trakcie pisania i bedzie dostepna na githubie jako open source.

Uwaga! To repozytorium zawiera wersje 2.0-dev ktora absolutnie nie jest wersja koncowa i stabilna.

## Wymagania

* PHP >= 5.6
    * Moduł GD
    * Moduł mongodb
    * Moduł mcrypt
* PostgreSQL >= 9.3
* MongoDB >= 2.7
* composer
* node.js
* npm
* git

### Zalecane

* Redis
* Elasticsearch 1.7
* Supervisor

## Instalacja

Moduły takie jak mcrypt czy gd na większości serwerów są domyślnie zainstalowane. Jeżeli jednak
moduły te nie są zainstalowane na serwerze, możesz je zainstalować poprzez:  `apt-get install php5-mcrypt`
oraz `apt-get install php5-gd`.

* `sudo pecl install mongodb`
* `git clone https://github.com/adam-boduch/coyote.git .`
* `psql -c 'create database coyote;' -U postgres`
* `cp .env.default .env` (plik .env zawiera konfiguracje bazy danych PostgreSQL oraz MongoDB)
* `make install` (na produkcji) lub `make install-dev` (bez minifikacji JS oraz CSS)
* `php artisan key:generate` (generowanie unikalnego klucza, który posłuży do szyfrowania danych)

### Problemy podczas instalacji
#### Class 'MongoClient' not found

Czy biblioteka mongo jest zainstalowana? Jeżeli tak to `service php5-fpm restart` (zakładając, że używasz PHP5 a nie PHP7)

#### Use of undefined constant MCRYPT_RIJNDAEL_128 - assumed 'MCRYPT_RIJNDAEL_128'

Czy biblioteka mcrypt jest zainstalowana? Jeżeli nie to `apt-get install php5-mcrypt`. Jeżeli tak to `service php5-fpm restart` (zakładając, że używasz PHP5 a nie PHP7)

## Uruchomienie

Działanie projektu wymaga zainstalowania serwera HTTP takiego jak Apache czy Nginx. PHP udostępnia jednak prosty serwer HTTP, który można wykorzystać, aby sprawdzić działanie aplikacji. Aby go uruchomić należy wykonać polecenie: `php -S localhost:8000 -t public`

## Testowanie

W pisaniu testów, pomaga nam framework [Codeception](http://codeception.com/). Testy znajdują się w katalogu `tests`, który zawiera testy jednostkowe, funkcjonalne oraz akceptacyjne. Aby uruchomić testy trzeba wejść do katalogu z projektem i wykonać polecenia:

1. `vendor/bin/codecept build` (tylko jednorazowo)
2. `vendor/bin/codecept run`

Jeżeli chcesz uruchomić testy akceptacyjne, to będziesz potrzebował narzędzia takiego jak Selenium czy PhantomJS. W katalogu `tests` znajduje się plik `acceptance.suite.yml.travis` który zawiera konfigurację testów akceptacyjnych i jest wykorzystywany przez Travis CI. Należy zmienić nazwę tego pliku na `acceptance.suite.yml` i zmienić wartość `url` na prawidłowy adres pod którym uruchomiony jest Coyote (np. `http://localhost/`). Zakładamy, że PhantomJS jest uruchomiony (`phantomjs --webdriver=4444`).

## Aktualizacja projektu

`make update` (na produkcji) lub `make update-dev` (na serwerze deweloperskim)

## Konfiguracja

Konfiguracja projektu znajduje się w pliku `.env`. **Szczególnie** zaleca się zmianę sterownika cache na **redis**:

`CACHE_DRIVER=redis`

### Konfiguracja supervisor

Supervisor jest narzędziem monitorującym procesy, działającym w środowisku Linux. W Laravel dostępny jest
mechanizm kolejkowania zadań (np. indeksowanie treści w Elasticsearch), który można uruchomić przy pomocy

`artisan queue:listen --sleep=10`

Supervisor ma na celu automatyczne uruchamianie tego procesu po starcie systemu i pilnownie, aby zawsze był uruchomiony.
Konfigurację supervisor możesz znaleźć w pliku `supervisor.conf`. Więcej informacji: https://laravel.com/docs/5.2/queues

## Jak mozesz pomoc?

Zachecamy do aktywnego udzialu w rozwoju projektu. Zajrzyj na zakladke *Issues* i zobacz jakie zadanie mozesz zrealizowac. Realizujemy tylko te zadanie ktore jest zaakceptowane i przypisane do wersji 2.0..

1. Utworz fork repozytorium
2. Wprowadz zmiany
3. Dodaj pull request
