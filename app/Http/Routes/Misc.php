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

Route::get('Flag', ['uses' => 'FlagController@index', 'as' => 'flag', 'middleware' => 'auth']);
Route::post('Flag', ['uses' => 'FlagController@save', 'middleware' => 'auth']);
Route::post('Flag/Delete/{id}', ['uses' => 'FlagController@delete', 'middleware' => 'auth', 'as' => 'flag.delete']);
Route::get('Flag/Delete/{id}', ['uses' => 'FlagController@modal', 'middleware' => 'auth', 'as' => 'flag.modal']);

Route::get('Sitemap', ['uses' => 'SitemapController@index', 'as' => 'sitemap']);
