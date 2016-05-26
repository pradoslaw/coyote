<?php

// logowanie do panelu administracyjnego (ponowne wpisanie hasla)
Route::match(['get', 'post'], 'Adm', ['uses' => 'Adm\HomeController@index', 'as' => 'adm.home', 'middleware' => ['auth', 'adm:0']]);

// dostep do panelu administracyjnego
Route::group(['namespace' => 'Adm', 'middleware' => ['auth', 'adm:1'], 'prefix' => 'Adm', 'as' => 'adm.'], function () {
    Route::get('Dashboard', 'DashboardController@index')->name('dashboard');

    Route::get('Forum/Category', 'Forum\CategoryController@index')->name('forum.category');
    Route::get('Forum/Access', 'Forum\AccessController@index')->name('forum.access');

    Route::get('User', 'UserController@index')->name('user');
    Route::get('Stream', 'StreamController@index')->name('stream');
    Route::get('Cache', 'CacheController@index')->name('cache');
    Route::get('Firewall', 'FirewallController@index')->name('firewall');
    Route::get('Stream', 'StreamController@index')->name('stream');
    Route::get('Log', 'LogController@index')->name('log');
});
