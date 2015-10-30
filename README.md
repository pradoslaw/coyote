# Coyote

[![StyleCI](https://styleci.io/repos/30256872/shield)](https://styleci.io/repos/30256872)
[![Build Status](https://travis-ci.org/adam-boduch/coyote.svg?branch=master)](https://travis-ci.org/adam-boduch/coyote)

4programmers.net

# Instalacja

* `git clone https://github.com/adam-boduch/coyote.git .`
* `composer install`
* `npm install`
* `cp .env.production .env` (plik .env zawiera konfiguracje bazy danych)
* `psql -c 'create database coyote;' -U postgres`
* `php artisan migrate --seed`
