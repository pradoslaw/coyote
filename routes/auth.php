<?php

/** @var $this \Illuminate\Routing\Router */
$this->get('Login', ['uses' => 'Auth\LoginController@index', 'as' => 'login']);
$this->post('Login', 'Auth\LoginController@login');
$this->post('Logout', ['uses' => 'Auth\LoginController@logout', 'as' => 'logout']);

// rejestracja uzytkownika
$this->get('Register', ['uses' => 'Auth\RegisterController@index', 'as' => 'register']);
$this->post('Register', 'Auth\RegisterController@signup')->middleware('throttle.submission:registration,1,1440');

// potwierdzenie adresu e-mail
$this->get('Confirm', 'Auth\ConfirmController@index')->name('confirm');
$this->post('Confirm', 'Auth\ConfirmController@generateLink');
$this->get('Confirm/Email', 'Auth\ConfirmController@email');

// Password Reset Routes...
$this->get('Password', 'Auth\ForgotPasswordController@showLinkRequestForm');
$this->post('Password', 'Auth\ForgotPasswordController@sendResetLinkEmail');
$this->get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm');
$this->post('password/reset', 'Auth\ResetPasswordController@reset');

$this->get('OAuth/{provider}/Login', ['uses' => 'Auth\OAuthController@login'])->name('oauth');
$this->get('OAuth/{provider}/Callback', 'Auth\OAuthController@callback');

$this->get('token', ['uses' => 'User\SessionTokenController@generateToken']);

