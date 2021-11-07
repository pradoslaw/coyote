<?php

/** @var $this \Illuminate\Routing\Router */
$this->group(['namespace' => 'Guide', 'prefix' => 'Guide', 'as' => 'guide.'], function () {
    $this->get('/', ['uses' => 'HomeController@index', 'as' => 'home']);

    // editing, saving post
    $this->get('Submit', ['uses' => 'SubmitController@form', 'middleware' => ['auth'], 'as' => 'submit']);
    $this->post('Submit/{guide?}', ['uses' => 'SubmitController@save', 'middleware' => ['auth']]);

    $this->get('{guide}-{slug}', ['uses' => 'ShowController@index', 'middleware' => ['page.hit'], 'as' => 'show']);
    $this->post('Vote/{guide?}', ['uses' => 'VoteController@vote', 'middleware' => ['auth']]);
    $this->post('Role/{guide?}', ['uses' => 'RoleController@update', 'middleware' => ['auth']]);

    $this->get('{name}', ['uses' => 'HomeController@filterByTags', 'as' => 'tag']);
});

// this route refers to a different namespace than above ones
$this->post('Guide/Subscribe/{guide}', ['uses' => 'SubscribeController@guide', 'middleware' => 'auth']);
