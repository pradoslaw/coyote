<?php

/*
 * Tymczasowe reguly
 */
Route::get('/Delphi', ['as' => 'page', 'uses' => 'Wiki\WikiController@category']);
Route::get('/Delphi/Lorem_ipsum', ['as' => 'article', 'uses' => 'Wiki\WikiController@article']);

Route::get('/{slug}', function ($slug) {
    return view('errors/404');

})->where('slug', '.*');