<?php

/** @var $this \Illuminate\Routing\Router */
$this->get('Login', ['uses' => 'Auth\LoginController@index', 'as' => 'login']);
$this->post('Login', 'Auth\LoginController@signin');
// wylogowanie
$this->get('Logout', ['uses' => 'Auth\LoginController@signout', 'as' => 'logout']);

// rejestracja uzytkownika
$this->get('Register', ['uses' => 'Auth\RegisterController@index', 'as' => 'register']);
$this->post('Register', 'Auth\RegisterController@signup');

// potwierdzenie adresu e-mail
$this->get('Confirm', 'Auth\ConfirmController@index')->name('confirm');
$this->post('Confirm', 'Auth\ConfirmController@generateLink');
$this->get('Confirm/Email', 'Auth\ConfirmController@email');

// Password Reset Routes...
$this->get('Password', 'Auth\PasswordController@showLinkRequestForm');
$this->post('Password', 'Auth\PasswordController@sendResetLinkEmail');
$this->get('Password/reset/{token}', 'Auth\PasswordController@showResetForm');
$this->post('Password/reset/', 'Auth\PasswordController@reset');
//$this->get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm');
//$this->post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
//$this->get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm');
//$this->post('password/reset', 'Auth\ResetPasswordController@reset');

$this->get('OAuth/{provider}/Login', ['uses' => 'Auth\OAuthController@login', 'as' => 'oauth']);
$this->get('OAuth/{provider}/Callback', 'Auth\OAuthController@callback');
