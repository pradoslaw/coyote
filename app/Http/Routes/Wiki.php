<?php

/*
 * Tymczasowe reguly
 */
Route::get('/Delphi', ['as' => 'page', 'uses' => 'Wiki\WikiController@category']);
Route::get('/Delphi/Lorem_ipsum', ['as' => 'article', 'uses' => 'Wiki\WikiController@article']);

Route::group(['namespace' => 'Wiki', 'prefix' => '', 'as' => 'wiki.'], function () {
    Route::get('Edit/{slug}', ['as' => 'submit', 'uses' => 'SubmitController@index', 'middleware' => ['auth', 'wiki.exists']])->where('slug', '.*');
    Route::get('{slug}', ['as' => 'show', 'uses' => 'ShowController@index', 'middleware' => 'wiki.exist'])->where('slug', '.*');
});
