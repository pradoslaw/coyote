<?php

// Obsluga mikroblogow
/** @var $this \Illuminate\Routing\Router */
$this->group(['namespace' => 'Microblog', 'prefix' => 'Mikroblogi', 'as' => 'microblog.'], function () {
    $this->get('/', ['uses' => 'HomeController@index', 'as' => 'home']);
    $this->post('Edit/{microblog?}', ['uses' => 'SubmitController@save', 'as' => 'save', 'middleware' => 'auth']);
    $this->post('Preview', ['uses' => 'SubmitController@preview', 'middleware' => 'auth']);

    $this->get('View/{id}', ['uses' => 'HomeController@show', 'as' => 'view']);
    $this->post('Vote/{microblog}', ['uses' => 'VoteController@post', 'as' => 'vote']);
    $this->get('Voters/{microblog}', ['uses' => 'VoteController@voters']);
    $this->post('Subscribe/{microblog}', ['uses' => 'SubscribeController@post', 'as' => 'subscribe', 'middleware' => 'auth']);
    $this->delete('Delete/{microblog}', ['uses' => 'SubmitController@delete', 'as' => 'delete', 'middleware' => 'auth']);
    $this->post('Sponsored/{microblog}', ['uses' => 'SubmitController@toggleSponsored', 'middleware' => 'auth']);

    // edycja/publikacja komentarza oraz jego usuniecie
    $this->post('Comment/{microblog?}', ['uses' => 'CommentController@save', 'as' => 'comment.save', 'middleware' => 'auth']);
    $this->delete('Comment/Delete/{microblog}', ['uses' => 'CommentController@delete', 'as' => 'comment.delete', 'middleware' => 'auth']);
    // pokaz reszte komentarzy...
    $this->get('Comment/Show/{id}', ['uses' => 'CommentController@show', 'as' => 'comment.show']);

    $this->get('Mine', ['uses' => 'HomeController@mine', 'as' => 'mine', 'middleware' => 'auth']);
    $this->get('{tag_name}', ['uses' => 'HomeController@tag', 'as' => 'tag']);
});
