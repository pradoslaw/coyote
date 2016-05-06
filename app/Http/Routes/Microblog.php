<?php

// Obsluga mikroblogow
Route::group(['namespace' => 'Microblog', 'prefix' => 'Mikroblogi', 'as' => 'microblog.'], function () {
    Route::get('/', ['uses' => 'HomeController@index', 'as' => 'home']);
    Route::post('Edit/{microblog?}', ['uses' => 'SubmitController@save', 'as' => 'save', 'middleware' => 'auth']);
    Route::get('Edit/{microblog}', ['uses' => 'SubmitController@edit', 'middleware' => 'auth']);

    Route::get('Upload', 'SubmitController@thumbnail');
    Route::post('Upload', ['uses' => 'SubmitController@upload', 'as' => 'upload', 'middleware' => 'auth']);
    Route::post('Paste', ['uses' => 'SubmitController@paste', 'as' => 'paste', 'middleware' => 'auth']);
    Route::get('View/{id}', ['uses' => 'ViewController@index', 'as' => 'view']);
    Route::post('Vote/{microblog}', ['uses' => 'VoteController@post', 'as' => 'vote']);
    Route::get('Vote/{microblog}', ['uses' => 'VoteController@voters', 'as' => 'voters']);
    Route::post('Subscribe/{microblog}', ['uses' => 'SubscribeController@post', 'as' => 'subscribe', 'middleware' => 'auth']);
    Route::post('Delete/{microblog}', ['uses' => 'SubmitController@delete', 'as' => 'delete', 'middleware' => 'auth']);

    // edycja/publikacja komentarza oraz jego usuniecie
    Route::post('Comment/{microblog?}', ['uses' => 'CommentController@save', 'as' => 'comment.save', 'middleware' => 'auth']);
    Route::get('Comment/{microblog}', ['uses' => 'CommentController@edit', 'middleware' => 'auth']);
    Route::post('Comment/Delete/{microblog}', ['uses' => 'CommentController@delete', 'as' => 'comment.delete', 'middleware' => 'auth']);
    // pokaz reszte komentarzy...
    Route::get('Comment/Show/{id}', ['uses' => 'CommentController@show', 'as' => 'comment.show']);

    Route::get('Mine', ['uses' => 'HomeController@mine', 'as' => 'mine', 'middleware' => 'auth']);
    Route::get('{tag}', ['uses' => 'HomeController@tag', 'as' => 'tag']);
});
