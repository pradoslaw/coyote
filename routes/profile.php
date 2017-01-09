<?php

/** @var $this \Illuminate\Routing\Router */
$this->get('Profile/{user}/History', ['uses' => 'Profile\HomeController@history', 'as' => 'profile.history']);
// ta regula musi znalezc sie na samym koncu
$this->get('Profile/{user}/{tab?}', ['uses' => 'Profile\HomeController@index', 'as' => 'profile']);
