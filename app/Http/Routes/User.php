<?php

// logowanie uzytkownika
/** @var $this \Illuminate\Routing\Router */
$this->get('Login', ['uses' => 'Auth\LoginController@index', 'as' => 'login']);
$this->post('Login', 'Auth\LoginController@signin');
// wylogowanie
$this->get('Logout', ['uses' => 'Auth\LoginController@signout', 'as' => 'logout']);

// rejestracja uzytkownika
$this->get('Register', ['uses' => 'Auth\RegisterController@index', 'as' => 'register']);
$this->post('Register', 'Auth\RegisterController@signup');

// przypominanie hasla
// @todo do zmiany metoda controller() na post() oraz get()
$this->controller('Password', 'Auth\PasswordController');

// potwierdzenie adresu e-mail
$this->get('Confirm', 'Auth\ConfirmController@index')->name('confirm');
$this->post('Confirm', 'Auth\ConfirmController@generateLink');
$this->get('Confirm/Email', 'Auth\ConfirmController@email');

$this->get('OAuth/{provider}/Login', ['uses' => 'Auth\OAuthController@login', 'as' => 'oauth']);
$this->get('OAuth/{provider}/Callback', 'Auth\OAuthController@callback');

$this->group(['namespace' => 'User', 'prefix' => 'User', 'middleware' => 'auth', 'as' => 'user.'], function () {
    /** @var $this \Illuminate\Routing\Router */
    // strona glowna panelu uzytkownika
    $this->get('/', ['uses' => 'HomeController@index', 'as' => 'home']);
    // dodawanie i usuwanie zdjecia uzytkownika
    $this->post('Photo/Upload', ['uses' => 'HomeController@upload', 'as' => 'photo.upload']);
    $this->post('Photo/Delete', ['uses' => 'HomeController@delete', 'as' => 'photo.delete']);

    // ustawienia uzytkownika
    $this->get('Settings', ['uses' => 'SettingsController@index', 'as' => 'settings']);
    $this->post('Settings', 'SettingsController@save');

    $this->get('Visits', ['uses' => 'VisitsController@index', 'as' => 'visits']);
    $this->post('Visits', 'VisitsController@save');

    $this->get('Alerts', ['uses' => 'AlertsController@index', 'as' => 'alerts']);
    $this->get('Alerts/Settings', ['uses' => 'AlertsController@settings', 'as' => 'alerts.settings']);
    $this->post('Alerts/Settings', 'AlertsController@save');
    $this->get('Alerts/Ajax', ['uses' => 'AlertsController@ajax', 'as' => 'alerts.ajax']);
    $this->post('Alerts/Mark/{id?}', ['uses' => 'AlertsController@markAsRead', 'as' => 'alerts.mark']);
    $this->post('Alerts/Delete/{id}', ['uses' => 'AlertsController@delete', 'as' => 'alerts.delete']);

    $this->get('Pm', ['uses' => 'PmController@index', 'as' => 'pm']);
    $this->get('Pm/Show/{id}', ['uses' => 'PmController@show', 'as' => 'pm.show']);
    $this->get('Pm/Submit', ['uses' => 'PmController@submit', 'as' => 'pm.submit']);
    $this->post('Pm/Submit', 'PmController@save');
    $this->post('Pm/Delete/{id}', ['uses' => 'PmController@delete', 'as' => 'pm.delete']);
    $this->post('Pm/Preview', ['uses' => 'PmController@preview', 'as' => 'pm.preview']);
    $this->get('Pm/Ajax', ['uses' => 'PmController@ajax', 'as' => 'pm.ajax']);
    $this->post('Pm/Paste', ['uses' => 'PmController@paste', 'as' => 'pm.paste']);

    $this->get('Favorites', ['uses' => 'FavoritesController@index', 'as' => 'favorites']);
    $this->get('Favorites/Forum', ['uses' => 'FavoritesController@forum', 'as' => 'favorites.forum']);
    $this->get('Favorites/Job', ['uses' => 'FavoritesController@job', 'as' => 'favorites.job']);
    $this->get('Favorites/Microblog', ['uses' => 'FavoritesController@microblog', 'as' => 'favorites.microblog']);
    $this->get('Favorites/Wiki', ['uses' => 'FavoritesController@wiki', 'as' => 'favorites.wiki']);
    $this->post('Favorites', 'FavoritesController@save');

    $this->get('Profiles', ['uses' => 'ProfilesController@index', 'as' => 'profiles']);
    $this->post('Profiles', 'ProfilesController@save');

    $this->get('Rates', ['uses' => 'RatesController@index', 'as' => 'rates']);
    $this->post('Rates', 'RatesController@save');

    $this->get('Stats', ['uses' => 'StatsController@index', 'as' => 'stats']);
    $this->post('Stats', 'StatsController@save');

    $this->get('Accepts', ['uses' => 'AcceptsController@index', 'as' => 'accepts']);
    $this->post('Accepts', 'AcceptsController@save');

    $this->get('Skills', ['uses' => 'SkillsController@index', 'as' => 'skills']);
    $this->post('Skills', 'SkillsController@save');
    $this->post('Skills/Order', ['uses' => 'SkillsController@order', 'as' => 'skills.order']);
    $this->post('Skills/{id}', ['uses' => 'SkillsController@delete', 'as' => 'skills.delete']);

    $this->get('Security', ['uses' => 'SecurityController@index', 'as' => 'security']);
    $this->post('Security', 'SecurityController@save');

    $this->get('Password', ['uses' => 'PasswordController@index', 'as' => 'password']);
    $this->post('Password', 'PasswordController@save');

    $this->get('Forum', ['uses' => 'ForumController@index', 'as' => 'forum']);
    $this->post('Forum', 'ForumController@save');
    $this->post('Forum/Restore', ['uses' => 'ForumController@restore', 'as' => 'forum.restore']);
});

// wizytowka usera. komponent ktory pojawia sie po naprowadzenia kursora nad login usera
$this->get('User/Vcard/{id}', ['uses' => 'User\VcardController@index', 'as' => 'user.vcard']);
// zadanie AJAX z lista loginow (podpowiedzi)
$this->get('User/Prompt', ['uses' => 'User\PromptController@index', 'as' => 'user.prompt']);
// zapis ustawien do tabeli settings. moga to byc np. niestandardowe ustawienia takie jak
// np. domyslna zakladka na stronie glownej
$this->post('User/Settings/Ajax', ['uses' => 'User\SettingsController@ajax', 'as' => 'user.settings.ajax']);


