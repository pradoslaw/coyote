<?php

// logowanie do panelu administracyjnego (ponowne wpisanie hasla)
Route::match(['get', 'post'], 'Adm', ['uses' => 'Adm\HomeController@index', 'as' => 'adm.home', 'middleware' => ['auth', 'adm:0']]);

// dostep do panelu administracyjnego
Route::group(['namespace' => 'Adm', 'middleware' => ['auth', 'adm:1'], 'prefix' => 'Adm', 'as' => 'adm.'], function () {
    Route::get('Dashboard', 'DashboardController@index')->name('dashboard');

    Route::get('Forum/Category', 'Forum\CategoryController@index')->name('forum.category');
    Route::get('Forum/Access', 'Forum\AccessController@index')->name('forum.access');

    Route::get('User', 'UserController@index')->name('user');
    Route::get('User/Save/{user}', 'UserController@edit')->name('user.save');
    Route::post('User/Save/{user}', 'UserController@save');

    Route::get('Firewall', 'FirewallController@index')->name('firewall');
    Route::get('Firewall/Save/{firewall?}', 'FirewallController@edit')->name('firewall.save');
    Route::post('Firewall/Save/{firewall?}', 'FirewallController@save');

    Route::get('Stream', 'StreamController@index')->name('stream');
    Route::get('Cache', 'CacheController@index')->name('cache');

    Route::get('Stream', 'StreamController@index')->name('stream');
    Route::get('Log', 'LogController@index')->name('log');
});
