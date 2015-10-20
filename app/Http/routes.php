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
Route::get('/Forum/Python/Test', ['uses' => 'Forum\TopicController@getIndex']);
Route::get('/Forum/Python', ['uses' => 'Forum\CategoryController@getIndex']);
Route::get('/Delphi', ['as' => 'page', 'uses' => 'Wiki\WikiController@category']);
Route::get('/Delphi/Lorem_ipsum', ['as' => 'article', 'uses' => 'Wiki\WikiController@article']);

Route::controllers([
    'login'      => 'Auth\LoginController',
    'password'   => 'Auth\PasswordController',
    'register'   => 'Auth\RegisterController',
    'Mikroblogi' => 'Microblog\HomeController',
    'Forum'      => 'Forum\HomeController'
]);

Route::group(['namespace' => 'User'], function() {
    Route::controllers([
       'User/Settings'           => 'SettingsController',
       'User'                    => 'HomeController',
    ]);
});



