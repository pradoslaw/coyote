<?php

// logowanie uzytkownika
Route::get('Login', ['uses' => 'Auth\LoginController@index', 'as' => 'login']);
Route::post('Login', 'Auth\LoginController@signin');
// wylogowanie
Route::get('Logout', ['uses' => 'Auth\LoginController@signout', 'as' => 'logout']);

// rejestracja uzytkownika
Route::get('Register', ['uses' => 'Auth\RegisterController@index', 'as' => 'register']);
Route::post('Register', 'Auth\RegisterController@signup');

// przypominanie hasla
Route::controller('Password', 'Auth\PasswordController');
Route::controller('Confirm', 'Auth\ConfirmController');

Route::get('OAuth/{provider}/Login', ['uses' => 'Auth\OAuthController@login', 'as' => 'oauth']);
Route::get('OAuth/{provider}/Callback', 'Auth\OAuthController@callback');

Route::group(['namespace' => 'User', 'prefix' => 'User', 'middleware' => 'auth', 'as' => 'user.'], function () {

    // strona glowna panelu uzytkownika
    Route::get('/', ['uses' => 'HomeController@index', 'as' => 'home']);
    // dodawanie i usuwanie zdjecia uzytkownika
    Route::post('Photo/Upload', ['uses' => 'HomeController@upload', 'as' => 'photo.upload']);
    Route::post('Photo/Delete', ['uses' => 'HomeController@delete', 'as' => 'photo.delete']);

    // ustawienia uzytkownika
    Route::get('Settings', ['uses' => 'SettingsController@index', 'as' => 'settings']);
    Route::post('Settings', 'SettingsController@save');

    Route::get('Visits', ['uses' => 'VisitsController@index', 'as' => 'visits']);
    Route::post('Visits', 'VisitsController@save');

    Route::get('Alerts', ['uses' => 'AlertsController@index', 'as' => 'alerts']);
    Route::get('Alerts/Settings', ['uses' => 'AlertsController@settings', 'as' => 'alerts.settings']);
    Route::post('Alerts/Settings', 'AlertsController@save');
    Route::get('Alerts/Ajax', ['uses' => 'AlertsController@ajax', 'as' => 'alerts.ajax']);
    Route::post('Alerts/Mark/{id?}', ['uses' => 'AlertsController@markAsRead', 'as' => 'alerts.mark']);
    Route::post('Alerts/Delete/{id}', ['uses' => 'AlertsController@delete', 'as' => 'alerts.delete']);

    Route::get('Pm', ['uses' => 'PmController@index', 'as' => 'pm']);
    Route::get('Pm/Show/{id}', ['uses' => 'PmController@show', 'as' => 'pm.show']);
    Route::get('Pm/Submit', ['uses' => 'PmController@submit', 'as' => 'pm.submit']);
    Route::post('Pm/Submit', 'PmController@save');
    Route::post('Pm/Delete/{id}', ['uses' => 'PmController@delete', 'as' => 'pm.delete']);
    Route::post('Pm/Preview', ['uses' => 'PmController@preview', 'as' => 'pm.preview']);
    Route::get('Pm/Ajax', ['uses' => 'PmController@ajax', 'as' => 'pm.ajax']);
    Route::post('Pm/Paste', ['uses' => 'PmController@paste', 'as' => 'pm.paste']);

    Route::get('Favorites', ['uses' => 'FavoritesController@index', 'as' => 'favorites']);
    Route::post('Favorites', 'FavoritesController@save');

    Route::get('Profiles', ['uses' => 'ProfilesController@index', 'as' => 'profiles']);
    Route::post('Profiles', 'ProfilesController@save');

    Route::get('Rates', ['uses' => 'RatesController@index', 'as' => 'rates']);
    Route::post('Rates', 'RatesController@save');

    Route::get('Stats', ['uses' => 'StatsController@index', 'as' => 'stats']);
    Route::post('Stats', 'StatsController@save');

    Route::get('Accepts', ['uses' => 'AcceptsController@index', 'as' => 'accepts']);
    Route::post('Accepts', 'AcceptsController@save');

    Route::get('Skills', ['uses' => 'SkillsController@index', 'as' => 'skills']);
    Route::post('Skills', 'SkillsController@save');
    Route::post('Skills/Order', ['uses' => 'SkillsController@order', 'as' => 'skills.order']);
    Route::post('Skills/{id}', ['uses' => 'SkillsController@delete', 'as' => 'skills.delete']);

    Route::get('Security', ['uses' => 'SecurityController@index', 'as' => 'security']);
    Route::post('Security', 'SecurityController@save');

    Route::get('Password', ['uses' => 'PasswordController@index', 'as' => 'password']);
    Route::post('Password', 'PasswordController@save');

    Route::get('Forum', ['uses' => 'ForumController@index', 'as' => 'forum']);
    Route::post('Forum', 'ForumController@save');
    Route::post('Forum/Restore', ['uses' => 'ForumController@restore', 'as' => 'forum.restore']);
});

// wizytowka usera. komponent ktory pojawia sie po naprowadzenia kursora nad login usera
Route::get('User/Vcard/{id}', ['uses' => 'User\VcardController@index', 'as' => 'user.vcard']);
// zadanie AJAX z lista loginow (podpowiedzi)
Route::get('User/Prompt', ['uses' => 'User\PromptController@index', 'as' => 'user.prompt']);
// zapis ustawien do tabeli settings. moga to byc np. niestandardowe ustawienia takie jak
// np. domyslna zakladka na stronie glownej
Route::post('User/Settings/Ajax', ['uses' => 'User\SettingsController@ajax', 'as' => 'user.settings.ajax']);

Route::get('Profile/{user}', ['uses' => 'Profile\HomeController@index', 'as' => 'profile']);