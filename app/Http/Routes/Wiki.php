<?php

Route::group(['namespace' => 'Wiki', 'prefix' => '', 'as' => 'wiki.'], function () {
    Route::get('Edit/{wiki?}', ['as' => 'submit', 'uses' => 'SubmitController@index', 'middleware' => ['auth', 'wiki.lock']]);
    Route::post('Edit/{wiki?}', ['uses' => 'SubmitController@save', 'middleware' => ['auth', 'wiki.lock']]);

    Route::post('Delete/{wiki}', ['as' => 'delete', 'uses' => 'DeleteController@index', 'middleware' => ['auth', 'can:wiki-admin']]);
    Route::post('Restore/{id}', ['as' => 'restore', 'uses' => 'RestoreController@index', 'middleware' => ['auth', 'can:wiki-admin']]);

    Route::get('Comment/{wiki}/{id?}', ['as' => 'comment.edit', 'CommentController@edit', 'middleware' => 'auth']);
    Route::post('Comment/{wiki}/{id?}', ['as' => 'comment.save', 'CommentController@save', 'middleware' => 'auth']);

    Route::post('Wiki/Subscribe/{wiki}', ['uses' => 'SubscribeController@index', 'as' => 'subscribe', 'middleware' => 'auth']);

    // deleted pages are visible only for users with privilege
    Route::get('{path}', ['as' => 'show', 'uses' => 'ShowController@index', 'middleware' => 'wiki.access:wiki-admin']);
});
