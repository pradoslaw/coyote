<?php

/** @var $this Router */

use Illuminate\Routing\Router;

$this->group(['namespace' => 'Survey', 'prefix' => 'survey', 'as' => 'survey.'], function () {
    $this->post('/', ['uses' => 'HomeController@store']);
});
