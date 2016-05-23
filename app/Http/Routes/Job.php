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
    Route::get('Submit/Firm/Partial/{id?}', ['uses' => 'SubmitController@getFirmPartial', 'as' => 'submit.firm.partial', 'middleware' => 'auth']);

    Route::get('Submit/Preview', ['uses' => 'SubmitController@getPreview', 'as' => 'submit.preview', 'middleware' => 'auth']);
    Route::post('Submit/Save', ['uses' => 'SubmitController@save', 'as' => 'submit.save', 'middleware' => 'auth']);

    Route::post('Tag/Submit', ['uses' => 'TagController@submit', 'as' => 'submit.tag']);
    Route::get('Tag/Prompt', ['uses' => 'TagController@prompt', 'as' => 'tag.prompt']);
    Route::get('Tag/Validate', ['uses' => 'TagController@valid', 'as' => 'tag.validate']);

    Route::post('Delete/{job}', ['uses' => 'DeleteController@index', 'as' => 'delete']);

    Route::get('Technologia/{name}', ['uses' => 'HomeController@tag', 'as' => 'tag']);
    Route::get('Zdalna', ['uses' => 'HomeController@remote', 'as' => 'remote']);
    Route::get('Miasto/{name}', ['uses' => 'HomeController@city', 'as' => 'city']);
    Route::get('Firma/{name}', ['uses' => 'HomeController@firm', 'as' => 'firm']);

    Route::get('{id}-{slug}', ['uses' => 'OfferController@index', 'as' => 'offer']);

    Route::post('Subscribe/{job}', ['uses' => 'SubscribeController@index', 'as' => 'subscribe', 'middleware' => 'auth']);
    Route::post('Preferences', ['uses' => 'PreferencesController@index', 'as' => 'preferences']);

    Route::get('Application/{job}', ['uses' => 'ApplicationController@submit', 'as' => 'candidate']);
    Route::post('Application/{job}', ['uses' => 'ApplicationController@save', 'as' => 'candidate']);

    Route::get('Ad', ['uses' => 'AdController@index', 'as' => 'ad']);
});

Route::group(['namespace' => 'Firm', 'prefix' => 'Firma', 'as' => 'firm.'], function () {
    Route::post('Logo', ['uses' => 'SubmitController@logo', 'as' => 'logo']);
});
