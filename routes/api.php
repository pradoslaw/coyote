<?php

$this->domain('api.' . ltrim(config('session.domain'), '.'))->group(function () {
    $this->get('token', ['uses' => 'User\SessionTokenController@generate_token']);
    $this->post('token/verify', ['uses' => 'User\SessionTokenController@verify_token']);

    $this->get('user/{user}', ['uses' => 'User\UserApiController@get']);
});
