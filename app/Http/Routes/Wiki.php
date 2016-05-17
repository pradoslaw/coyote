<?php

/*
 * Tymczasowe reguly
 */
Route::get('/Delphi', ['as' => 'page', 'uses' => 'Wiki\WikiController@category']);
Route::get('/Delphi/Lorem_ipsum', ['as' => 'article', 'uses' => 'Wiki\WikiController@article']);

Route::group(['namespace' => 'Wiki', 'prefix' => '', 'as' => 'wiki.'], function () {
    Route::get('Edit/{wiki?}', ['as' => 'submit', 'uses' => 'SubmitController@index', 'middleware' => ['auth', 'wiki.lock']]);
    Route::post('Edit/{wiki?}', ['uses' => 'SubmitController@save', 'middleware' => ['auth', 'wiki.lock']]);

    // deleted pages are visible only for users with privilege
    Route::get('{path}', ['as' => 'show', 'uses' => 'ShowController@index', 'middleware' => 'wiki.access:wiki-admin']);
});
