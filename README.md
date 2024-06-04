# Coyote

[![Build Status](https://travis-ci.org/adam-boduch/coyote.svg?branch=master)](https://travis-ci.org/adam-boduch/coyote)

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
XDEBUG_HOST=host.docker.internal
XDEBUG_PORT=9003
```

Nastepnie należy zrestartować wszystkie kontenery (a dokładniej kontener `php`, żeby `./entrypoint.sh` został uruchomiony ponownie).

Skonfiguruj IDE by łączyło się do istniejącego kontenera, a nie tworzyło nowy.

#### IDE od JetBrains

Jeśli korzystasz z PhpStorm, konieczne może być ustawienie zmiennej środowiskowej:

```bash
# from /bin/bash
export PHP_IDE_CONFIG="serverName=your_server_name"
```
```cmd
REM from cmd.exe
set PHP_IDE_CONFIG="serverName=your_server_name"
```
```ps
# from powershell.exe
$env:PHP_IDE_CONFIG = 'serverName=your_server_name'
```

Nazwa `your_server_name` powinna odpowiadać nazwie servera w sekcji "path mappings".

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

#### Powolna praca z Vue

Zmiany w szablonach `.twig` są widoczne od razu po przeładowaniu, ale zmiany w plikach `.vue` są
widoczne dopiero po przebudowaniu `yarn run dev`.

Dla szybkiego developowania, warto stworzyć osobną aplikację Vue, zbudować w niej komponenty, i potem
po prostu przekopiować do projektu coyote.

```
# Stwórz nowy folder poza projektem coyote/
cd ..
mkdir spike/

# Stwórz pustą aplikację vue
cd spike/
yarn init --yes --private
yarn add @vue/cli
yarn run vue create app    # Select preset "Default Vue 2"

# Dodaj SCSS oraz uruchom aplikację
cd app
yarn add sass-loader sass
yarn serve
```

Możesz teraz po prostu edytować plik `.vue` i pracować na błyskawicznej aplikacji. Po skończeniu, folder `spike/`
może być usunięty.

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

2. Niektóre widoki powodują błąd związany z ElasticSearch i brakującymi polami.
   - Prawdopodobnie początkowe tworzenie indexów się nie powiodło.
     ```
     php artisan es:drop
     ```
     A następnie stwórz indexy od nowa
     ```
     php artisan es:create --force
     php artisan es:index --force
     ```
   - ElasticSearch przełącza się w tryb readonly, kiedy dysk ma zajęte 95% miejsca.
     Spowoduje to błędy w dodawaniu nowych wartości. Rozwiązaniem na to jest
     oczywiście zwolnienie miejsca na dysku.
   - ElasticSearch możliwe że jest trybie readonly, i sam z niego nie wyjdzie.
     Wtedy należy się przełączyć na kontener, i wykonać
     ```
     curl -XPUT -H "Content-Type: application/json" http://localhost:9200/_all/_settings -d '{"index.blocks.read_only_allow_delete": null}'
     ```

3. Running containers listening on high ports fails.

   If you're running Windows, it's possible that random dynamic ports for IANA are
   blocking docker containers.

   To verify:
   ```
   netsh int ipv4 show dynamicport tcp
   ```
   If the starting port is low-ish (between 1000-2000), then it's possible that the
   dynamic ports are blocking the docker containers.

   To change:
   1. ```
      netsh int ipv4 set dynamic tcp start=49152 num=16384
      netsh int ipv6 set dynamic tcp start=49152 num=16384
      ```
   2. Reboot.
4. Random unrelated tests start to fail.
   
   Many Dusk tests aren't properly constructed to expect debug features like Debugbar.

   To fix:
   ```
   APP_DEBUG=false
   ```

5. Problem z uruchomieniem `docker compose` na Windows:
   ```
   Error response from daemon: Ports are not available: exposing port TCP 0.0.0.0:8025 -> 0.0.0.0:0: listen tcp 0.0.0.0:8025: bind: Została podjęta próba uzyskania dostępu do gniazda w sposób zabroniony przez przypisane do niego uprawnienia dostępu.
   ```
   Rozwiązanie:
   1. Otwórz PowerShell jako administrator
   2. ```
      net stop winnat
      ```

6. Brak połączenia z internetem z WSL:
   1. `wsl --shutdown`
   2. ```
      netsh winsock reset 
      netsh int ip reset all
      netsh winhttp reset proxy
      ipconfig /flushdns
      ```
   3. Reboot
   4. Start `wsl`
   
   If the steps didn't work, repeat the steps, but also run `netsh winsock reset` after `ipconfig /flushdns`.

7. Różnice w środowiskach

   Pamiętaj, że uruchomienie `docker compose up` (bez przekazania `-f`) domyślnie
   skorzysta z plików `docker-compose.yaml` **oraz** `docker-compose.override.yaml`.

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
