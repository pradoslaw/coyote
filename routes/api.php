<?php

// @deprecated
$this->domain('api.' . ltrim(config('session.domain'), '.'))->group(function () {
    $this->any('token/verify', ['uses' => 'User\SessionTokenController@verifyToken']);

    $this->get('users/{user}', ['uses' => 'User\UserApiController@get']);
});

$this->domain(config('services.api.host'))->group(function () {
    $this->get('microblog', ['uses' => 'Api\Microblog\HomeController@index']);
});

