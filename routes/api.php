<?php

// @deprecated
$this->any('token/verify', ['uses' => 'User\SessionTokenController@verifyToken']);
$this->get('users/{user}', ['uses' => 'User\UserApiController@get']);

$this->prefix('v1')->group(function () {
    $this->get('microblogs', ['uses' => 'Api\Microblog\HomeController@index']);
    $this->get('jobs', ['uses' => 'Api\Job\HomeController@index']);
    $this->get('jobs/{id}', ['uses' => 'Api\Job\OfferController@index']);
    $this->post('login', ['uses' => 'Api\LoginController@login']);
    $this->get('user', ['uses' => 'Api\UserController@index', 'middleware' => 'auth:api']);
});

if (config('services.api.host') && env('APP_ENV') !== 'testing') {
    // catch all url's and redirect to correct URL (like from api.4programmers.net/Forum to 4programmers.net/Forum)
    $this->get('{any}', ['uses' => 'Api\PermanentRedirectController@redirect'])->where('any', '.*');
}

