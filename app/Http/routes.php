<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', ['as' => 'home', 'uses' => 'HomeController@index']);

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

// strona glowna forum
Route::get('Forum', ['uses' => 'Forum\HomeController@index', 'as' => 'forum.home']);

// formularz dodawania nowego watku na forum
Route::get('Forum/Submit/{forum}', ['uses' => 'Forum\HomeController@getSubmit', 'as' => 'forum.submit']);
Route::post('Forum/Submit/{forum}', 'Forum\HomeController@getSubmit');

Route::get('Praca', ['uses' => 'Job\HomeController@index', 'as' => 'job.home']);

/*
 * Tymczasowe reguly
 */
Route::get('/Delphi', ['as' => 'page', 'uses' => 'Wiki\WikiController@category']);
Route::get('/Delphi/Lorem_ipsum', ['as' => 'article', 'uses' => 'Wiki\WikiController@article']);
Route::get('Forum/Python/Test', ['uses' => 'Forum\TopicController@index']);
Route::get('Forum/Python', ['uses' => 'Forum\CategoryController@index']);
Route::get('Praca/Lorem_ipsum', ['uses' => 'Job\OfferController@index', 'as' => 'job.offer']);

// Obsluga mikroblogow
Route::group(['namespace' => 'Microblog', 'prefix' => 'Mikroblogi'], function () {
    Route::get('/', ['uses' => 'HomeController@index', 'as' => 'microblog.home']);
    Route::post('Edit/{id?}', ['uses' => 'SubmitController@save', 'as' => 'microblog.save', 'middleware' => 'auth']);
    Route::get('Edit/{id}', ['uses' => 'SubmitController@edit', 'middleware' => 'auth']);

    Route::get('/{tag}', ['as' => 'microblog.tag']);

    Route::post('Upload', ['uses' => 'SubmitController@upload', 'as' => 'microblog.upload', 'middleware' => 'auth']);
    Route::get('View/{id}', ['uses' => 'ViewController@index', 'as' => 'microblog.view']);
    Route::post('Vote/{id}', ['uses' => 'VoteController@post', 'as' => 'microblog.vote', 'middleware' => 'auth']);
    Route::get('Vote/{id}', ['uses' => 'VoteController@voters', 'as' => 'microblog.voters']);
    Route::post('Watch/{id}', ['uses' => 'WatchController@post', 'as' => 'microblog.watch', 'middleware' => 'auth']);
    Route::post('Delete/{id}', ['uses' => 'SubmitController@delete', 'as' => 'microblog.delete', 'middleware' => 'auth']);

    // edycja/publikacja komentarza oraz jego usuniecie
    Route::post('Comment/{id?}', ['uses' => 'CommentController@save', 'as' => 'microblog.comment.save', 'middleware' => 'auth']);
    Route::get('Comment/{id}', ['uses' => 'CommentController@edit', 'middleware' => 'auth']);
    Route::post('Comment/Delete/{id}', ['uses' => 'CommentController@delete', 'as' => 'microblog.comment.delete', 'middleware' => 'auth']);
    // pokaz reszte komentarzy...
    Route::get('Comment/Show/{id}', ['uses' => 'CommentController@show', 'as' => 'microblog.comment.show']);
});

// Obsluga modulu pastebin
Route::get('Pastebin', ['uses' => 'Pastebin\HomeController@index', 'as' => 'pastebin.home']);
Route::get('Pastebin/{id}', ['uses' => 'Pastebin\HomeController@show', 'as' => 'pastebin.show'])->where('id', '\d+');
Route::post('Pastebin', ['uses' => 'Pastebin\HomeController@save']);

Route::group(['namespace' => 'User', 'middleware' => 'auth'], function () {

    // strona glowna panelu uzytkownika
    Route::get('User', ['uses' => 'HomeController@index', 'as' => 'user.home']);
    // dodawanie i usuwanie zdjecia uzytkownika
    Route::post('User/Photo/Upload', ['uses' => 'HomeController@upload', 'as' => 'user.photo.upload']);
    Route::post('User/Photo/Delete', ['uses' => 'HomeController@delete', 'as' => 'user.photo.delete']);

    // ustawienia uzytkownika
    Route::get('User/Settings', ['uses' => 'SettingsController@index', 'as' => 'user.settings']);
    Route::post('User/Settings', 'SettingsController@save');

    Route::get('User/Visits', ['uses' => 'VisitsController@index', 'as' => 'user.visits']);
    Route::post('User/Visits', 'VisitsController@save');

    Route::get('User/Alerts', ['uses' => 'AlertsController@index', 'as' => 'user.alerts']);
    Route::post('User/Alerts', 'AlertsController@save');

    Route::get('User/Pm', ['uses' => 'PmController@index', 'as' => 'user.pm']);
    Route::post('User/Pm', 'PmController@save');

    Route::get('User/Favorites', ['uses' => 'FavoritesController@index', 'as' => 'user.favorites']);
    Route::post('User/Favorites', 'FavoritesController@save');

    Route::get('User/Profiles', ['uses' => 'ProfilesController@index', 'as' => 'user.profiles']);
    Route::post('User/Profiles', 'ProfilesController@save');

    Route::get('User/Rates', ['uses' => 'RatesController@index', 'as' => 'user.rates']);
    Route::post('User/Rates', 'RatesController@save');

    Route::get('User/Stats', ['uses' => 'StatsController@index', 'as' => 'user.stats']);
    Route::post('User/Stats', 'StatsController@save');

    Route::get('User/Accepts', ['uses' => 'AcceptsController@index', 'as' => 'user.accepts']);
    Route::post('User/Accepts', 'AcceptsController@save');

    Route::get('User/Skills', ['uses' => 'SkillsController@index', 'as' => 'user.skills']);
    Route::post('User/Skills', 'SkillsController@save');

    Route::get('User/Security', ['uses' => 'SecurityController@index', 'as' => 'user.security']);
    Route::post('User/Security', 'SecurityController@save');

    Route::get('User/Password', ['uses' => 'PasswordController@index', 'as' => 'user.password']);
    Route::post('User/Password', 'PasswordController@save');

    Route::get('User/Alerts/Settings', ['uses' => 'AlertsController@settings', 'as' => 'user.alerts.settings']);
//    Route::post('User/Alerts/Settings', 'AlertsController@save');

    Route::get('User/Forum', ['uses' => 'ForumController@index', 'as' => 'user.forum']);
    Route::post('User/Forum', 'ForumController@save');

    // Generowanie linka potwierdzajacego autentycznosc adresu e-mail
    Route::get('User/Confirm', ['uses' => 'ConfirmController@index', 'as' => 'user.confirm']);
    Route::post('User/Confirm', ['uses' => 'ConfirmController@send']);
});

// ta regula nie moze sprawdzac czy user jest zalogowany, czy nie. user moze potwierdzic adres e-mail
// niekoniecznie bedac zalogowanym
Route::get('User/Confirm/Email', ['uses' => 'User\ConfirmController@email', 'as' => 'user.email']);
// wizytowka usera. komponent ktory pojawia sie po naprowadzenia kursora nad login usera
Route::get('User/Vcard/{id}', ['uses' => 'User\VcardController@index', 'as' => 'user.vcard'])->where('id', '\d+');
// zadanie AJAX z lista loginow (podpowiedzi)
Route::get('User/Prompt', ['uses' => 'User\PromptController@index', 'as' => 'user.prompt']);

// dostep do panelu administracyjnego
Route::group(['namespace' => 'Adm', 'middleware' => ['auth', 'adm'], 'prefix' => 'Adm'], function () {
    Route::get('/', 'HomeController@index');
});

Route::get('Profile/{user}', ['uses' => 'Profile\HomeController@index', 'as' => 'profile']);

Route::get('/{slug}', function ($slug) {
    echo "404 $slug";

})->where('slug', '.*');