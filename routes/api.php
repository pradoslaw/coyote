<?php

// @deprecated
$this->any('token/verify', ['uses' => 'User\SessionTokenController@verifyToken']);
$this->get('users/{user}', ['uses' => 'User\UserApiController@get']);

$this->prefix('v1')->group(function () {
    $this->get('microblogs', ['uses' => 'Api\MicroblogsController@index']);
    $this->get('microblogs/{microblog}', ['uses' => 'Api\MicroblogsController@show']);

    $this->get('jobs', ['uses' => 'Api\JobsController@index']);
    $this->get('jobs/{job}', ['uses' => 'Api\JobsController@show']);
    $this->put('jobs/{job}', ['uses' => 'Api\JobsController@save', 'middleware' => 'auth:api']);
    $this->post('jobs', ['uses' => 'Api\JobsController@save', 'middleware' => 'auth:api']);
    $this->post('login', ['uses' => 'Api\LoginController@login']);
    $this->get('user', ['uses' => 'Api\UserController@index', 'middleware' => 'auth:api']);
    $this->get('topics', ['uses' => 'Api\TopicsController@index']);
    $this->get('topics/{topic}', ['uses' => 'Api\TopicsController@show']);
    $this->get('posts', ['uses' => 'Api\PostsController@index']);
    $this->get('posts/{post}', ['uses' => 'Api\PostsController@show']);
});

$this->get('/', ['uses' => 'Api\HomeController@index']);

if (config('services.api.host') && env('APP_ENV') !== 'testing') {
    // catch all url's and redirect to correct URL (like from api.4programmers.net/Forum to 4programmers.net/Forum)
    $this->get('{path}', ['uses' => 'Api\PermanentRedirectController@redirect'])->where('path', '.*');
}

