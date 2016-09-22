<?php

/** @var $this \Illuminate\Routing\Router */
$this->get('Profile/{user}/{tab?}', ['uses' => 'Profile\HomeController@index', 'as' => 'profile']);
