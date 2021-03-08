<?php

/** @var $this \Illuminate\Routing\Router */
$this->group(['namespace' => 'Questions', 'prefix' => 'Question', 'as' => 'question.'], function () {
    $this->get('{question}-{slug}', ['uses' => 'ShowController@index']);
});
