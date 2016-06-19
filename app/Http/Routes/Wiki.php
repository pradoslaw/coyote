<?php

Route::group(['namespace' => 'Wiki', 'prefix' => '', 'as' => 'wiki.'], function () {
    Route::get('Edit/{wiki?}', ['as' => 'submit', 'uses' => 'SubmitController@index', 'middleware' => ['auth', 'wiki.lock']]);
    Route::post('Edit/{wiki?}', ['uses' => 'SubmitController@save', 'middleware' => ['auth', 'wiki.lock']]);

    Route::get('Clone/{wiki}', ['as' => 'clone', 'uses' => 'CloneController@index', 'middleware' => ['auth', 'can:wiki-admin']]);
    Route::post('Clone/{wiki}', ['uses' => 'CloneController@save', 'middleware' => ['auth', 'can:wiki-admin']]);

    Route::post('Delete/{wiki}', ['as' => 'delete', 'uses' => 'DeleteController@delete', 'middleware' => ['auth', 'can:wiki-admin']]);
    Route::post('Unlink/{wiki}', ['as' => 'unlink', 'uses' => 'DeleteController@unlink', 'middleware' => ['auth', 'can:wiki-admin']]);
    Route::post('Restore/{id}', ['as' => 'restore', 'uses' => 'RestoreController@index', 'middleware' => ['auth', 'can:wiki-admin']]);

    Route::post('Comment/Save/{wiki}/{id?}', ['as' => 'comment.save', 'uses' => 'CommentController@save', 'middleware' => 'auth']);
    Route::post('Comment/Delete/{wiki}/{id?}', ['as' => 'comment.delete', 'uses' => 'CommentController@delete', 'middleware' => 'auth']);

    Route::post('Wiki/Subscribe/{wiki}', ['uses' => 'SubscribeController@index', 'as' => 'subscribe', 'middleware' => 'auth']);

    // deleted pages are visible only for users with privilege
    Route::get('{path}', ['as' => 'show', 'uses' => 'ShowController@index', 'middleware' => ['wiki.access:wiki-admin', 'page.hit']]);
});
