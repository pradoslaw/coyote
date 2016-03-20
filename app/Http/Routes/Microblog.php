<?php

// Obsluga mikroblogow
Route::group(['namespace' => 'Microblog', 'prefix' => 'Mikroblogi', 'as' => 'microblog.'], function () {
    Route::get('/', ['uses' => 'HomeController@index', 'as' => 'home']);
    Route::post('Edit/{id?}', ['uses' => 'SubmitController@save', 'as' => 'save', 'middleware' => 'auth']);
    Route::get('Edit/{id}', ['uses' => 'SubmitController@edit', 'middleware' => 'auth']);

    Route::get('Upload', 'SubmitController@thumbnail');
    Route::post('Upload', ['uses' => 'SubmitController@upload', 'as' => 'upload', 'middleware' => 'auth']);
    Route::post('Paste', ['uses' => 'SubmitController@paste', 'as' => 'paste', 'middleware' => 'auth']);
    Route::get('View/{id}', ['uses' => 'ViewController@index', 'as' => 'view']);
    Route::post('Vote/{id}', ['uses' => 'VoteController@post', 'as' => 'vote']);
    Route::get('Vote/{id}', ['uses' => 'VoteController@voters', 'as' => 'voters']);
    Route::post('Subscribe/{id}', ['uses' => 'SubscribeController@post', 'as' => 'subscribe', 'middleware' => 'auth']);
    Route::post('Delete/{id}', ['uses' => 'SubmitController@delete', 'as' => 'delete', 'middleware' => 'auth']);

    // edycja/publikacja komentarza oraz jego usuniecie
    Route::post('Comment/{id?}', ['uses' => 'CommentController@save', 'as' => 'comment.save', 'middleware' => 'auth']);
    Route::get('Comment/{id}', ['uses' => 'CommentController@edit', 'middleware' => 'auth']);
    Route::post('Comment/Delete/{id}', ['uses' => 'CommentController@delete', 'as' => 'comment.delete', 'middleware' => 'auth']);
    // pokaz reszte komentarzy...
    Route::get('Comment/Show/{id}', ['uses' => 'CommentController@show', 'as' => 'comment.show']);

    Route::get('Mine', ['uses' => 'HomeController@mine', 'as' => 'mine', 'middleware' => 'auth']);
    Route::get('{tag}', ['uses' => 'HomeController@tag', 'as' => 'tag']);
});
