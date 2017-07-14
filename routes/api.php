<?php

$this->domain('api.' . ltrim(config('session.domain'), '.'))->group(function () {
    $this->get('token', ['uses' => 'User\SessionTokenController@index'])->name('session');
});
