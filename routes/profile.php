<?php

/** @var $this \Illuminate\Routing\Router */
$this->get('Profile/{user_trashed}/History', ['uses' => 'Profile\HomeController@history', 'as' => 'profile.history']);
// ta regula musi znalezc sie na samym koncu
$this->get('Profile/{user_trashed}/{tab?}', ['uses' => 'Profile\HomeController@index', 'as' => 'profile']);
