# Coyote

[![StyleCI](https://styleci.io/repos/30256872/shield)](https://styleci.io/repos/30256872)
[![Build Status](https://travis-ci.org/adam-boduch/coyote.svg?branch=master)](https://travis-ci.org/adam-boduch/coyote)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/adam-boduch/coyote/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/adam-boduch/coyote/?branch=master)

Coyote to nazwa systemu obsługującego serwis [4programmers.net].

[4programmers.net]: https://4programmers.net/

## Instalacja

1. Stwórz lokalny plik `.env`, bazując na `.env.default`
   ```bash
   cp .env.default .env
   ```
2. Uruchom kontenery lokalnie, posługująć się jedną z komend:
   - ```
     make up
     ```
   - ```
     docker compose up
     ```
   Jeśli uruchomienie aplikacji nie udaje się na systemie Windows, usuń `-u nginx` z pliku `Makefile`
   z każdej komendy, i spróbuj ponownie.
3. Zainstaluj zależności:
   - Wersja developerska (source mapy):
     ```
     make install-dev 
     ```
   - Wersja produkcyjna (minifikacja zasobów):
     ```
     make install
     ```
4. Aplikacja jest gotowa
   - Odwiedź `localhost:8880`
   - Na użytkownika `admin` można zalogować się hasłem `123`.
5. Zatrzymaj aplikację, posługująć się jedną z komend:
   - ```
     make stop
     ```
   - ```
     docker compose stop
     ```

### Konfiguracja debuggera

By skorzystać z xdebug, w pliku `.env` należy dodać ustawienia xdebug:

```
ENABLE_XDEBUG=1
XDEBUG_HOST=
XDEBUG_PORT=
```

Nastepnie należy zrestartować wszystkie kontenery.

### Praca z CSS oraz JS

W projekcie korzystamy z `yarn` oraz `webpack`. Aby skompilować pliki źródłowe do postaci finalnej, należy wykonać
polecenie:
 - Wersja developerska 
   ```
   docker-compose exec php yarn run dev
   ```
   lub
   ```
   docker-compose exec php yarn run watch
   ```

   Polecenie `docker-compose exec php yarn run watch` powoduje stałe monitorowanie zmian w plikach źródłowych. Jakiekolwiek zmiany w tych plikach
   spowodują wygenerowanie nowych plików wynikowych CSS oraz JS.

 - Wersja produkcyjna:
   ```
   docker-compose exec php yarn run prod
   ```

### Testowanie

Aby uruchomić testy w laravel:

```
docker-compose exec php php vendor/bin/phpunit
```

### Troubleshooting

1. Running tests causes a lot of failed tests, with CSRF token fail.
   - Can be fixed with running this command in `php` container:
     ```
     php artisan config:clear
     ```

### Zadania uruchomiane w tle

Na serwerze produkcyjnym niektóre zadanie wykonywane są w tle. Dodawane są one do kolejki oraz wykonywane przez proces działający w tle.
Domyślnie, na serwerze lokalnym zadania nie są dodawane do kolejki (w pliku `.env` ustawienie `QUEUE_DRIVER=sync`).

Jeżeli jednak chciałbyś przetestować działanie mechanizmu kolejek, ustaw wartość zmiennej środowiskowej `QUEUE_DRIVER` na `redis`.

Aby uruchomić mechanizm kolejek skorzystaj z następującego polecenia:

```
docker-compose exec php php artisan queue:listen --sleep=10
```

## Jak zgłaszać błędy bezpieczeństwa?

W przypadku znalezienia błędów prosimy o zgłaszanie ich bezpośrednio na e-mail: support@4programmers.net

## Jak możesz pomóc?

Zachęcamy do aktywnego udziału w rozwoju projektu. Zajrzyj na zakładkę *Issues* i zobacz jakie zadanie możesz zrealizować. Realizujemy tylko te zadania, które są zaakceptowane i przypisane do wersji 2.0.

1. Utwórz fork repozytorium
2. Wprowadź zmiany
3. Dodaj pull request
