<?php

/*
 * Modul "Praca"
 */
Route::group(['namespace' => 'Job', 'prefix' => 'Praca', 'as' => 'job.'], function () {
    Route::get('/', ['uses' => 'HomeController@index', 'as' => 'home']);

    Route::get('Submit/{id?}', ['uses' => 'SubmitController@getIndex', 'as' => 'submit', 'middleware' => 'auth']);
    Route::post('Submit', ['uses' => 'SubmitController@postIndex', 'middleware' => 'auth']);

    Route::get('Submit/Firm', ['uses' => 'SubmitController@getFirm', 'as' => 'submit.firm', 'middleware' => 'auth']);
    Route::post('Submit/Firm', ['uses' => 'SubmitController@postFirm', 'middleware' => 'auth']);

    Route::get('Submit/Preview', ['uses' => 'SubmitController@getPreview', 'as' => 'submit.preview', 'middleware' => 'auth']);
    Route::post('Submit/Save', ['uses' => 'SubmitController@save', 'as' => 'submit.save', 'middleware' => 'auth']);

    Route::get('Technologia/{name}', ['uses' => 'HomeController@index', 'as' => 'tag']);
    Route::get('Zdalna', ['uses' => 'HomeController@remote', 'as' => 'remote']);
    Route::get('Miasto/{name}', ['uses' => 'HomeController@city', 'as' => 'city']);
    Route::get('Firma/{name}', ['uses' => 'HomeController@firm', 'as' => 'firm']);

    Route::get('{id}-{slug}', ['uses' => 'OfferController@index', 'as' => 'offer']);
});

Route::group(['namespace' => 'Firm', 'prefix' => 'Firma', 'as' => 'firm.'], function () {
    Route::post('Logo', ['uses' => 'SubmitController@logo', 'as' => 'logo']);
});
