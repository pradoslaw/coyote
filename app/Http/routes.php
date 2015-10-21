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

/*
 * Tymczasowe reguly
 */
Route::get('/Delphi', ['as' => 'page', 'uses' => 'Wiki\WikiController@category']);
Route::get('/Delphi/Lorem_ipsum', ['as' => 'article', 'uses' => 'Wiki\WikiController@article']);
Route::get('Forum/Python/Test', ['uses' => 'Forum\TopicController@index']);
Route::get('Forum/Python', ['uses' => 'Forum\CategoryController@index']);

// Obsluga mikroblogow
Route::get('Mikroblogi', ['uses' => 'Microblog\HomeController@index', 'as' => 'microblog.home']);

Route::group(['namespace' => 'User'], function() {

    // strona glowna panelu uzytkownika
    Route::get('User', ['uses' => 'HomeController@index', 'as' => 'user.home']);

    // ustawienia uzytkownika
    Route::get('User/Settings', ['uses' => 'SettingsController@index', 'as' => 'user.settings']);
    Route::post('User/Settings', 'SettingsController@save');
});

Route::get('/{slug}', function($slug) {
    echo "404 $slug";

})->where('slug', '.*');



