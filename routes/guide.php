<?php

/** @var $this \Illuminate\Routing\Router */
$this->group(['namespace' => 'Guide', 'prefix' => 'Guide', 'as' => 'guide.'], function () {
    $this->get('/', ['uses' => 'HomeController@index']);
    $this->get('{guide}-{slug}', ['uses' => 'ShowController@index', 'middleware' => ['page.hit'], 'as' => 'show']);
    $this->post('Submit/{guide?}', ['uses' => 'SubmitController@save', 'middleware' => ['auth']]);
    $this->post('Vote/{guide?}', ['uses' => 'VoteController@vote', 'middleware' => ['auth']]);
    $this->post('Sub/{guide?}', ['uses' => 'VoteController@vote', 'middleware' => ['auth']]);

    $this->get('Submit', ['uses' => 'SubmitController@form', 'middleware' => ['auth']]);
});

// this route refers to a different namespace than above ones
$this->post('Guide/Subscribe/{guide}', ['uses' => 'SubscribeController@guide', 'middleware' => 'auth']);
